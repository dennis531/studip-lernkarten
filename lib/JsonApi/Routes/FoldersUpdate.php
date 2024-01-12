<?php

namespace Lernkarten\JsonApi\Routes;

use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\Routes\ValidationTrait;
use Lernkarten\JsonApi\Schemas\Deck as DeckSchema;
use Lernkarten\Models\Folder;
use Lernkarten\JsonApi\Schemas\Folder as FolderSchema;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Updates one Deck.
 */
class FoldersUpdate extends JsonApiController
{
    use ValidationTrait;

    /**
     * @param array $args
     *
     * @return Response
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        /** @var ?\Lernkarten\Models\Folder $resource */
        $resource = Folder::find($args['id']);
        if (!$resource) {
            throw new RecordNotFoundException();
        }

        if ($this->cannot($request, 'update', $resource)) {
            throw new AuthorizationFailedException();
        }

        $json = $this->validate($request, $resource);
        $card = $this->update($resource, $json);

        return $this->getContentResponse($card);
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
        if (!self::arrayHas($json, 'data')) {
            return 'Missing `data` member at document´s top level.';
        }
        if (FolderSchema::TYPE !== self::arrayGet($json, 'data.type')) {
            return 'Invalid `type` of document´s `data`.';
        }

        if (!self::arrayHas($json, 'data.attributes.name')) {
            return 'Missing `name` attribute.';
        }
        if (!mb_strlen(trim(self::arrayGet($json, 'data.attributes.name')))) {
            return 'Empty `name` attribute.';
        }
    }

    private function update(Folder $resource, array $json): Folder
    {
        foreach (['name'] as $attr) {
            if (self::arrayHas($json, 'data.attributes.' . $attr)) {
                $resource->$attr = trim(self::arrayGet($json, 'data.attributes.' . $attr));
            }
        }
        $resource->store();

        return $resource;
    }
}
