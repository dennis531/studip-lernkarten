<?php

namespace Lernkarten\JsonApi\Routes;

use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use Lernkarten\Models\Folder;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Delete one Folder.
 */
class FoldersDelete extends JsonApiController
{
    /**
     * @param array $args
     * @return Response
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        $resource = Folder::find($args['id']);
        if (!$resource) {
            throw new RecordNotFoundException();
        }

        if ($this->cannot($request, 'delete', $resource)) {
            throw new AuthorizationFailedException();
        }

        $resource->delete();

        return $this->getCodeResponse(204);
    }
}
