<?php

namespace Lernkarten\JsonApi\Routes;

use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\BadRequestException;
use JsonApi\Errors\RecordNotFoundException;
use Lernkarten\JsonApi\Schemas\Card as CardSchema;
use Lernkarten\Models\Card;
use Lernkarten\Models\Deck;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Displays all Cards of a Deck.
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class CardsOfDecksIndex extends JsonApiController
{
    protected $allowedIncludePaths = [
        CardSchema::REL_DECK,
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
        $resource = Deck::find($args['id']);
        if (!$resource) {
            throw new RecordNotFoundException();
        }

        if ($this->cannot($request, 'viewAnyOfDeck', Card::class, $resource)) {
            throw new AuthorizationFailedException();
        }

        $resources = Card::findBySql("deck_id = ? ORDER BY mkdate", [$resource->id]);
        return $this->getPaginatedContentResponse(
            array_slice($resources, ...$this->getOffsetAndLimit()),
            count($resources)
        );
    }
}
