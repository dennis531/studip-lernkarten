<?php

namespace Lernkarten\JsonApi\Routes;

use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\BadRequestException;
use Lernkarten\JsonApi\Schemas\Deck as DeckSchema;
use Lernkarten\JsonApi\Schemas\SharedDeck as SharedDeckSchema;
use Lernkarten\Models\Deck;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use User;

/**
 * Displays all Decks.
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class DecksIndex extends JsonApiController
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
        if ($this->cannot($request, 'viewAny', Deck::class)) {
            throw new AuthorizationFailedException();
        }

        $resources = $this->findAll($this->getUser($request));
        return $this->getPaginatedContentResponse(
            array_slice($resources, ...$this->getOffsetAndLimit()),
            count($resources)
        );
    }

    private function findAll(User $user): iterable
    {
        $ownedDecks = Deck::findBySql('owner_id = ?', [$user->id]);

        return $ownedDecks;
    }
}
