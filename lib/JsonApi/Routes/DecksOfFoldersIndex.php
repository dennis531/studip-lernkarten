<?php

namespace Lernkarten\JsonApi\Routes;

use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\BadRequestException;
use JsonApi\Errors\RecordNotFoundException;
use Lernkarten\JsonApi\Schemas\Deck as DeckSchema;
use Lernkarten\Models\Deck;
use Lernkarten\Models\Folder;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Displays all Decks of a folder.
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class DecksOfFoldersIndex extends JsonApiController
{
    protected $allowedIncludePaths = [
        DeckSchema::REL_CONTEXT,
        DeckSchema::REL_FOLDER,
        DeckSchema::REL_OWNER,
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
        $resource = Folder::find($args['id']);
        if (!$resource) {
            throw new RecordNotFoundException();
        }

        if ($this->cannot($request, 'viewAnyOfFolder', Deck::class, $resource)) {
            throw new AuthorizationFailedException();
        }

        $resources = Deck::findBySql("folder_id = ?", [$resource->id]);
        return $this->getPaginatedContentResponse(
            array_slice($resources, ...$this->getOffsetAndLimit()),
            count($resources)
        );
    }
}
