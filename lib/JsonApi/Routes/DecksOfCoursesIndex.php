<?php

namespace Lernkarten\JsonApi\Routes;

use Course;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\BadRequestException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\Schemas\Course as CourseSchema;
use Lernkarten\JsonApi\Schemas\Deck as DeckSchema;
use Lernkarten\JsonApi\Schemas\SharedDeck as SharedDeckSchema;
use Lernkarten\Models\Deck;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Displays all Decks of a Course.
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class DecksOfCoursesIndex extends JsonApiController
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
        $resource = Course::find($args['id']);
        if (!$resource) {
            throw new RecordNotFoundException();
        }

        if ($this->cannot($request, 'viewAnyOfCourse', Deck::class, $resource)) {
            throw new AuthorizationFailedException();
        }

        $resources = Deck::findBySql("context_id = ? AND context_type = ?", [$resource->id, Course::class]);
        return $this->getPaginatedContentResponse(
            array_slice($resources, ...$this->getOffsetAndLimit()),
            count($resources)
        );
    }
}
