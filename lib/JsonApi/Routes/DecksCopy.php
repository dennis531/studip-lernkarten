<?php

namespace Lernkarten\JsonApi\Routes;

use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\BadRequestException;
use JsonApi\Errors\RecordNotFoundException;
use Lernkarten\Models\Deck;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class DecksCopy extends JsonApiController
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameters)
     */
    public function __invoke(Request $request, Response $response, array $args)
    {
        /** @var ?\Lernkarten\Models\Deck $deck */
        $deck = Deck::find($args['id']);
        if (!$deck) {
            throw new RecordNotFoundException('Could not find Deck.');
        }

        if ($this->cannot($request, 'copy', $deck)) {
            throw new AuthorizationFailedException();
        }

        $user = $this->getUser($request);
        $deck = $deck->copy($user);
        if (!$deck) {
            throw new BadRequestException('Could not copy Deck to Work Place.');
        }

        return $this->getCreatedResponse($deck);
    }
}
