<?php

namespace Lernkarten\JsonApi\Routes;

use Course;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\BadRequestException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\Routes\ValidationTrait;
use JsonApi\Schemas\Course as CourseSchema;
use JsonApi\Schemas\User as UserSchema;
use Lernkarten\JsonApi\Schemas\Card as CardSchema;
use Lernkarten\JsonApi\Schemas\Deck as DeckSchema;
use Lernkarten\Models\Card;
use Lernkarten\Models\Deck;
use Lernkarten\Models\Note;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use RuntimeException;
use Studip\Markup;
use User;

/**
 * Creates a Card.
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class CardsCreate extends JsonApiController
{
    use ValidationTrait;

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @param array $args
     *
     * @return Response
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        if ($this->cannot($request, 'create', Card::class)) {
            throw new AuthorizationFailedException();
        }

        $json = $this->validate($request);

        $resource = $this->create($json);

        return $this->getCreatedResponse($resource);
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameters)
     *
     * @param array $json
     * @param mixed $data
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
        if (self::arrayHas($json, 'data.id')) {
            return 'New document must not have an `id`.';
        }

        // rel deck
        if (!self::arrayHas($json, 'data.relationships.deck')) {
            return 'Missing `deck` relationship.';
        }
        if (!$this->getDeckFromJson($json)) {
            return 'Invalid `deck` relationship.';
        }

        // attr guid
        if (self::arrayHas($json, 'data.attributes.guid')) {
            return 'Attribute `guid` is read-only.';
        }

        // attr model
        if (!self::arrayHas($json, 'data.attributes.model')) {
            return 'Missing attribute `model`.';
        }
        if (!$this->validModel($json)) {
            return 'Invalid attribute `model`.';
        }

        // attr fields
        if (!self::arrayHas($json, 'data.attributes.fields')) {
            return 'Missing attribute `fields`.';
        }
        if (!$this->validFields($json)) {
            return 'Invalid attribute `fields`.';
        }
    }

    private function create(array $json): Card
    {
        /** @var Course|User */
        $deck = $this->getDeckFromJson($json);
        $model = self::arrayGet($json, 'data.attributes.model');
        $fields = json_encode($this->purifyHTML(self::arrayGet($json, 'data.attributes.fields')));

        $note = Note::create(['model' => $model, 'fields' => $fields]);

        $resource = Card::create([
            'deck_id' => $deck->id,
            'note_id' => $note->id,
        ]);

        if (!$resource) {
            throw new RuntimeException('Could not create card.');
        }

        return $resource;
    }

    /**
     * @return null|Deck
     */
    private function getDeckFromJson(array $json)
    {
        $relation = 'data.relationships.' . CardSchema::REL_DECK . '.data';
        $resourceId = self::arrayGet($json, $relation . '.id');
        if ($this->validateResourceObject($json, $relation, DeckSchema::TYPE)) {
            return Deck::find($resourceId);
        }

        return null;
    }

    private function purifyHTML(array $fields): iterable
    {
        $purified = [];
        foreach ($fields as $key => $value) {
            $purified[$key] = is_string($value) && Markup::hasHtmlMarker($value) ? Markup::purifyHtml($value) : $value;
        }

        return $purified;
    }

    private function validModel(array $json): bool
    {
        $model = self::arrayGet($json, 'data.attributes.model');

        return $model === 'basic' || $model === 'image';
    }

    private function validFields(array $json): bool
    {
        $fields = self::arrayGet($json, 'data.attributes.fields');

        return is_array($fields);
    }
}
