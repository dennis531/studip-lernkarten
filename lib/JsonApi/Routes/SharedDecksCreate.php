<?php

namespace Lernkarten\JsonApi\Routes;

use Course;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\BadRequestException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\Routes\ValidationTrait;
use JsonApi\Schemas\Course as CourseSchema;
use JsonApi\Schemas\User as UserSchema;
use Lernkarten\JsonApi\Schemas\Deck as DeckSchema;
use Lernkarten\JsonApi\Schemas\SharedDeck as SharedDeckSchema;
use Lernkarten\Models\Deck;
use Lernkarten\Models\SharedDeck;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use RuntimeException;
use User;

/**
 * Creates a SharedDeck.
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class SharedDecksCreate extends JsonApiController
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
        if ($this->cannot($request, 'create', SharedDeck::class)) {
            throw new AuthorizationFailedException();
        }

        $json = $this->validate($request);

        $resource = $this->create($this->getUser($request), $json);

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
        if (SharedDeckSchema::TYPE !== self::arrayGet($json, 'data.type')) {
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

        // rel recipient
        if (!self::arrayHas($json, 'data.relationships.recipient')) {
            return 'Missing `recipient` relationship.';
        }
        if (!$this->getRecipientFromJson($json)) {
            return 'Invalid `recipient` relationship.';
        }
    }

    private function create(User $user, array $json): SharedDeck
    {
        /** @var Course|User */
        $recipient = $this->getRecipientFromJson($json);
        $deck = $this->getDeckFromJson($json);

        if (SharedDeck::isShared($deck, $recipient)) {
            throw new RuntimeException('Deck already shared.');
        }

        $resource = SharedDeck::create([
            'deck_id' => $deck->id,
            'recipient_id' => $recipient->getId(),
            'recipient_type' => get_class($recipient),
            'sharer_id' => $user->id,
        ]);

        if (!$resource) {
            throw new RuntimeException('Could not create deck.');
        }

        return $resource;
    }

    /**
     * @return null|Course|User
     */
    private function getRecipientFromJson(array $json)
    {
        $relation = 'data.relationships.' . SharedDeckSchema::REL_RECIPIENT . '.data';
        $resourceId = self::arrayGet($json, $relation . '.id');
        if ($this->validateResourceObject($json, $relation, CourseSchema::TYPE)) {
            return Course::find($resourceId);
        }
        if ($this->validateResourceObject($json, $relation, UserSchema::TYPE)) {
            return User::find($resourceId);
        }

        return null;
    }

    /**
     * @return null|Deck
     */
    private function getDeckFromJson(array $json)
    {
        $relation = 'data.relationships.' . SharedDeckSchema::REL_DECK . '.data';
        $resourceId = self::arrayGet($json, $relation . '.id');
        if ($this->validateResourceObject($json, $relation, DeckSchema::TYPE)) {
            return Deck::find($resourceId);
        }

        return null;
    }
}
