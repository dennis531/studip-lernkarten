<?php

namespace Lernkarten\JsonApi\Routes;

use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\BadRequestException;
use Lernkarten\JsonApi\Schemas\Deck as DeckSchema;
use Lernkarten\JsonApi\Schemas\SharedDeck as SharedDeckSchema;
use Lernkarten\Models\SharedDeck;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Displays all SharedDecks.
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class SharedDecksIndex extends JsonApiController
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
        if ($this->cannot($request, 'viewAny', SharedDeck::class)) {
            throw new AuthorizationFailedException();
        }

        $resources = SharedDeck::findByUser($this->getUser($request));

        return $this->getPaginatedContentResponse(
            array_slice($resources, ...$this->getOffsetAndLimit()),
            count($resources)
        );
    }
}
