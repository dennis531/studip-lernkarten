<?php

namespace Lernkarten\JsonApi\Routes;

use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Routes\ValidationTrait;
use Lernkarten\Models\Card;
use Lernkarten\Models\Deck;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Generates cards using a LLM
 *
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class CardsGenerate extends NonJsonApiController
{
    use ValidationTrait;

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameters)
     */
    public function __invoke(Request $request, Response $response, array $args)
    {
        if ($this->cannot($request, 'create', Card::class)) {
            throw new AuthorizationFailedException();
        }

        $json = $this->validate($request);

        $this->generate($json);

        return $response->withStatus(201);
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
        if (!self::arrayHas($json, 'deck_id')) {
            return 'Missing `deck id` field.';
        }

        if (!self::getDeckFromJson($json)) {
            return 'Invalid `deck` relationship.';
        }

        if (!self::arrayHas($json, 'content')) {
            return 'Missing `content` field.';
        }

        if (!self::validContent($json)) {
            return 'Invalid number of words.';
        }

        if (!self::arrayHas($json, 'number')) {
            return 'Missing `number` field.';
        }

        if (!self::validNumber($json)) {
            return 'Invalid field `number`.';
        }
    }

    private function validNumber(array $json): bool
    {
        $number = self::arrayGet($json, 'number');

        return $number > 0 && $number <= 100;
    }

    public function validContent(array $json): bool
    {
        $content = self::arrayGet($json, 'content');
        $words = preg_split('/\s+/', $content);
        $limit = \Config::get()->getValue('LERNKARTEN_WORD_LIMIT');

        return count($words) <= $limit;
    }

    /**
     * @param array $json
     * @return null|Deck
     */
    private function getDeckFromJson(array $json): ?Deck
    {
        $deck_id = self::arrayGet($json, 'deck_id');
        return Deck::find($deck_id);
    }

    /**
     * Generate new cards
     *
     * @param array $json
     * @return Card[]
     */
    private function generate(array $json): array
    {
        $deck = $this->getDeckFromJson($json);
        $content = self::arrayGet($json, 'content');
        $number = self::arrayGet($json, 'number');

        return Card::generate($deck, $content, $number);
    }
}
