<?php

namespace Lernkarten\JsonApi\Routes;

use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\BadRequestException;
use Lernkarten\JsonApi\Schemas\Folder as FolderSchema;
use Lernkarten\Models\Folder;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Displays all Folders.
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class FoldersIndex extends JsonApiController
{
    protected $allowedIncludePaths = [
        FolderSchema::REL_CHILDREN,
        FolderSchema::REL_CONTEXT,
        FolderSchema::REL_DECKS,
        FolderSchema::REL_PARENT,
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
        if ($this->cannot($request, 'viewAny', Folder::class)) {
            throw new AuthorizationFailedException();
        }

        $resources = Folder::findByUser($this->getUser($request));

        return $this->getPaginatedContentResponse(
            array_slice($resources, ...$this->getOffsetAndLimit()),
            count($resources)
        );
    }
}
