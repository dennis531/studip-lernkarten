<?php

namespace Lernkarten\JsonApi\Routes;

use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\Routes\TimestampTrait;
use JsonApi\Routes\ValidationTrait;
use Lernkarten\JsonApi\Schemas\Card as CardSchema;
use Lernkarten\Models\Card;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Updates one Card.
 */
class CardsUpdate extends JsonApiController
{
    use TimestampTrait;
    use ValidationTrait;

    /**
     * @param array $args
     * @return Response
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        /** @var ?\Lernkarten\Models\Card $resource */
        $resource = Card::find($args['id']);
        if (!$resource) {
            throw new RecordNotFoundException();
        }

        if ($this->cannot($request, 'update', $resource)) {
            throw new AuthorizationFailedException();
        }

        $json = $this->validate($request, $resource);
        $card = $this->update($resource, $json);

        return $this->getContentResponse($card);
    }

    /**
     * @param array $json
     * @param Card $data
     *
     * @return string|void
     */
    protected function validateResourceDocument($json, $data)
    {
        if (!self::arrayHas($json, 'data')) {
            return 'Missing `data` member at document´s top level.';
        }
        if (CardSchema::TYPE !== self::arrayGet($json, 'data.type')) {
            return 'Invalid `type` of document´s `data`.';
        }
        if (!self::arrayHas($json, 'data.id')) {
            return 'Document must have an `id`.';
        }
        if (self::arrayGet($json, 'data.id') !== $data->id) {
            return 'Differing `id`';
        }

        // attr guid
        if (self::arrayHas($json, 'data.attributes.guid')) {
            if (self::arrayGet($json, 'data.attributes.guid') !== $data->note->guid) {
                return 'Differing `guid` attribute';
            }
        }

        // attr fields
        if (self::arrayHas($json, 'data.attributes.fields')) {
            if (!$this->validFields($json)) {
                return 'Invalid attribute `model`.';
            }
        }

        // attrs für Lernstand
        $floatAttrs = $this->getOptionalFloatAttrs();
        $timestampAttrs = $this->getOptionalTimestampAttrs();
        $unsignedAttrs = $this->getOptionalUnsignedAttrs();
        foreach ($floatAttrs as $attr) {
            $result = $this->validateOptionalFloat($json, $attr);
            if ($result) {
                return $result;
            }
        }
        foreach ($timestampAttrs as $attr) {
            $result = $this->validateOptionalTimestamp($json, $attr);
            if ($result) {
                return $result;
            }
        }
        foreach ($unsignedAttrs as $attr) {
            $result = $this->validateOptionalUnsigned($json, $attr);
            if ($result) {
                return $result;
            }
        }
    }

    private function getOptionalFloatAttrs(): array
    {
        return ['stability', 'difficulty'];
    }

    private function getOptionalTimestampAttrs(): array
    {
        return ['due', 'last-review'];
    }

    private function getOptionalUnsignedAttrs(): array
    {
        return [
            'elapsed-days',
            'scheduled-days',
            'reps',
            'lapses',
            'state',
            'again-count',
            'hard-count',
            'good-count',
            'easy-count',
        ];
    }

    private function update(Card $resource, array $json): Card
    {
        if (self::arrayHas($json, 'data.attributes.fields')) {
            $fields = self::arrayGet($json, 'data.attributes.fields');
            $resource->updateFields($fields);
        }

        $this->updateOptionalNumberAttrs($resource, $json);
        $this->updateOptionalTimestampAttrs($resource, $json);
        $resource->store();

        return $resource;
    }

    private function updateOptionalNumberAttrs(Card $resource, array $json): void
    {
        $numbers = array_merge($this->getOptionalFloatAttrs(), $this->getOptionalUnsignedAttrs());
        foreach ($numbers as $attr) {
            $value = self::arrayGet($json, 'data.attributes.' . $attr, false);
            if ($value !== false) {
                $resource[strtr($attr, ['-' => '_'])] = $value;
            }
        }
    }

    private function updateOptionalTimestampAttrs(Card $resource, array $json): void
    {
        $timestamps = $this->getOptionalTimestampAttrs();
        foreach ($timestamps as $attr) {
            $value = self::arrayGet($json, 'data.attributes.' . $attr, false);
            if ($value !== false) {
                $date = self::fromISO8601($value);
                $resource[strtr($attr, ['-' => '_'])] = $date->getTimestamp();
            }
        }
    }

    private function validFields(array $json): bool
    {
        $fields = self::arrayGet($json, 'data.attributes.fields');

        return is_array($fields);
    }

    private function validateOptionalFloat(array $json, string $attr): ?string
    {
        $path = 'data.attributes.' . $attr;
        if (self::arrayHas($json, $path)) {
            $value = self::arrayGet($json, $path);
            if (!is_float($value) && !is_int($value)) {
                return "Invalid float for attribute `$attr`";
            }
        }
        return null;
    }

    private function validateOptionalTimestamp(array $json, string $attr): ?string
    {
        $path = 'data.attributes.' . $attr;
        if (self::arrayHas($json, $path)) {
            $value = self::arrayGet($json, $path);
            if (!self::isValidTimestamp($value)) {
                return "Invalid timestamp for attribute `$attr`";
            }
        }
        return null;
    }

    private function validateOptionalUnsigned(array $json, string $attr): ?string
    {
        $path = 'data.attributes.' . $attr;
        if (self::arrayHas($json, $path)) {
            $value = self::arrayGet($json, $path);
            if (!(is_int($value) && $value >= 0)) {
                return "Invalid unsigned integer for attribute `$attr`";
            }
        }
        return null;
    }
}
