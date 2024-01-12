<?php

namespace Lernkarten\JsonApi\Routes;

use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use Lernkarten\JsonApi\Schemas\Deck as DeckSchema;
use Lernkarten\JsonApi\Schemas\SharedDeck as SharedDeckSchema;
use Lernkarten\Models\SharedDeck;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Display one SharedDeck.
 */
class SharedDecksShow extends JsonApiController
{
    protected $allowedIncludePaths = [
        SharedDeckSchema::REL_COLEARNING_DECK,
        SharedDeckSchema::REL_COLEARNING_DECK . '.' . DeckSchema::REL_OWNER,
        SharedDeckSchema::REL_DECK,
        SharedDeckSchema::REL_RECIPIENT,
        SharedDeckSchema::REL_SHARER,
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
        $resource = SharedDeck::find($args['id']);
        if (!$resource) {
            throw new RecordNotFoundException();
        }

        if ($this->cannot($request, 'view', $resource)) {
            throw new AuthorizationFailedException();
        }

        return $this->getContentResponse($resource);
    }
}
