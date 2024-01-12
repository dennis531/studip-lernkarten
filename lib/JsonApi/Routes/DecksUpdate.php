<?php

namespace Lernkarten\JsonApi\Routes;

use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\Routes\ValidationTrait;
use Lernkarten\JsonApi\Schemas\Deck as DeckSchema;
use Lernkarten\Models\Deck;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Updates one Deck.
 */
class DecksUpdate extends JsonApiController
{
    use ValidationTrait;

    /**
     * @param array $args
     *
     * @return Response
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        /** @var ?\Lernkarten\Models\Deck $resource */
        $resource = Deck::find($args['id']);
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
     * @param Deck  $data
     *
     * @return string|void
     */
    protected function validateResourceDocument($json, $data)
    {
        if (!self::arrayHas($json, 'data')) {
            return 'Missing `data` member at document´s top level.';
        }
        if (DeckSchema::TYPE !== self::arrayGet($json, 'data.type')) {
            return 'Invalid `type` of document´s `data`.';
        }
        if (!self::arrayHas($json, 'data.id')) {
            return 'Document must have an `id`.';
        }
        if (self::arrayGet($json, 'data.id') !== $data->id) {
            return 'Differing `id`';
        }

        if (self::arrayHas($json, 'data.attributes.name')) {
            if (!mb_strlen(trim(self::arrayGet($json, 'data.attributes.name')))) {
                return 'Empty `name` attribute.';
            }
        }
        if (self::arrayHas($json, 'data.attributes.description')) {
            if (!mb_strlen(trim(self::arrayGet($json, 'data.attributes.description')))) {
                return 'Empty `description` attribute.';
            }
        }
    }

    private function update(Deck $resource, array $json): Deck
    {
        foreach (['name', 'description', 'metadata', 'folder_id'] as $attr) {
            if (self::arrayHas($json, 'data.attributes.' . $attr)) {
                $resource->$attr = trim(self::arrayGet($json, 'data.attributes.' . $attr));
            }
        }
        $resource->store();

        return $resource;
    }
}
