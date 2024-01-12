<?php

namespace Lernkarten\JsonApi\Routes;

use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\BadRequestException;
use JsonApi\Errors\RecordNotFoundException;
use Lernkarten\Models\Deck;
use Lernkarten\Models\SharedDeck;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * @SuppressWarnings(PHPMD.StaticAccess)
 *
 */
// TODO: Möglicherweise genügt eigentlich DecksCopy?
class SharedDecksCopy extends JsonApiController
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameters)
     */
    public function __invoke(Request $request, Response $response, array $args)
    {
        /** @var ?\Lernkarten\Models\SharedDeck $sharedDeck */
        $sharedDeck = SharedDeck::find($args['id']);
        if (!$sharedDeck) {
            throw new RecordNotFoundException('Could not find SharedDeck.');
        }

        if ($this->cannot($request, 'copy', $sharedDeck)) {
            throw new AuthorizationFailedException();
        }

        $user = $this->getUser($request);
        $deck = $sharedDeck->copyToWorkPlace($user);
        if (!$deck) {
            throw new BadRequestException('Could not copy SharedDeck to Work Place.');
        }

        return $this->getCreatedResponse($deck);
    }
}
