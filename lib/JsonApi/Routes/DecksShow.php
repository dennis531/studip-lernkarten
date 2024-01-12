<?php

namespace Lernkarten\JsonApi\Routes;

use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use Lernkarten\JsonApi\Schemas\Deck as DeckSchema;
use Lernkarten\JsonApi\Schemas\SharedDeck as SharedDeckSchema;
use Lernkarten\Models\Deck;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Display one Deck.
 */
class DecksShow extends JsonApiController
{
    protected $allowedIncludePaths = [
        DeckSchema::REL_CARDS,
        DeckSchema::REL_CONTEXT,
        DeckSchema::REL_FOLDER,
        DeckSchema::REL_OWNER,
        DeckSchema::REL_SHARED_DECK,
        DeckSchema::REL_SHARED_DECKS,
        DeckSchema::REL_SHARED_DECKS . '.' . SharedDeckSchema::REL_RECIPIENT,
        DeckSchema::REL_SHARED_WITH,
        DeckSchema::REL_TEMPLATE,
        DeckSchema::REL_TEMPLATE . '.' . DeckSchema::REL_OWNER,
    ];

    /**
     * @param array $args
     * @return Response
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        $resource = Deck::find($args['id']);
        if (!$resource) {
            throw new RecordNotFoundException();
        }

        if ($this->cannot($request, 'view', $resource)) {
            throw new AuthorizationFailedException();
        }

        return $this->getContentResponse($resource);
    }
}
