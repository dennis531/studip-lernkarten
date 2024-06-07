<?php

namespace Lernkarten\JsonApi\Routes;

use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use Lernkarten\JsonApi\Schemas\Deck as DeckSchema;
use Lernkarten\JsonApi\Schemas\SharedDeck as SharedDeckSchema;
use Lernkarten\Models\SharedDeck;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use User;

/**
 * Displays all SharedDecks of a User.
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class SharedDecksOfUsersIndex extends JsonApiController
{
    protected $allowedIncludePaths = [
        SharedDeckSchema::REL_COLEARNING_DECK,
        SharedDeckSchema::REL_COLEARNING_DECK . '.' . DeckSchema::REL_OWNER,
        SharedDeckSchema::REL_COPIED_DECKS,
        SharedDeckSchema::REL_DECK,
        SharedDeckSchema::REL_RECIPIENT,
        SharedDeckSchema::REL_SHARER,
    ];
    protected $allowedPagingParameters = ['offset', 'limit'];

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @param array $args
     *
     * @return Response
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        $resource = \User::find($args['id']);
        if (!$resource) {
            throw new RecordNotFoundException();
        }

        if ($this->cannot($request, 'viewAnyOfUser', SharedDeck::class, $resource)) {
            throw new AuthorizationFailedException();
        }

        $resources = SharedDeck::findBySql(
            '(recipient_id = ? AND recipient_type = ?) OR sharer_id = ?',
            [$resource->id, \User::class, $resource->id]
        );

        return $this->getPaginatedContentResponse(
            array_slice($resources, ...$this->getOffsetAndLimit()),
            count($resources)
        );
    }
}
