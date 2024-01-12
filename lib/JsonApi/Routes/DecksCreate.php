<?php

namespace Lernkarten\JsonApi\Routes;

use Course;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\BadRequestException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\Routes\ValidationTrait;
use JsonApi\Schemas\Course as CourseSchema;
use JsonApi\Schemas\User as UserSchema;
use Lernkarten\JsonApi\Schemas\Deck as DeckSchema;
use Lernkarten\JsonApi\Schemas\Folder as FolderSchema;
use Lernkarten\Models\Deck;
use Lernkarten\Models\Folder;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use RuntimeException;
use User;

/**
 * Creates a Deck.
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class DecksCreate extends JsonApiController
{
    use ValidationTrait;

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @param array $args
     *
     * @return Response
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        if ($this->cannot($request, 'create', Deck::class)) {
            throw new AuthorizationFailedException();
        }

        $json = $this->validate($request);

        $resource = $this->create($this->getUser($request), $json);

        return $this->getCreatedResponse($resource);
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
        if (DeckSchema::TYPE !== self::arrayGet($json, 'data.type')) {
            return 'Invalid `type` of document´s `data`.';
        }
        if (self::arrayHas($json, 'data.id')) {
            return 'New document must not have an `id`.';
        }

        if (!self::arrayHas($json, 'data.attributes.name')) {
            return 'Missing `name` attribute.';
        }
        if (!mb_strlen(trim(self::arrayGet($json, 'data.attributes.name')))) {
            return 'Empty `name` attribute.';
        }
        if (!self::arrayHas($json, 'data.attributes.description')) {
            return 'Missing `description` attribute.';
        }
        if (!mb_strlen(trim(self::arrayGet($json, 'data.attributes.description')))) {
            return 'Empty `description` attribute.';
        }

        // rel context
        if (!self::arrayHas($json, 'data.relationships.context')) {
            return 'Missing `context` relationship.';
        }
        if (!$this->getContextFromJson($json)) {
            return 'Invalid `context` relationship.';
        }

        // rel folder
        if (self::arrayHas($json, 'data.relationships.folder')) {
            try {
                $this->getFolderFromJson($json);
            } catch (RecordNotFoundException $exception) {
                return 'Invalid `folder` relationship.';
            }
        }
    }

    private function create(User $user, array $json): Deck
    {
        /** @var Course|User */
        $context = $this->getContextFromJson($json);
        $folder = $this->getFolderFromJson($json);
        $name = trim(self::arrayGet($json, 'data.attributes.name'));
        $description = trim(self::arrayGet($json, 'data.attributes.description'));
        $metadata = trim(self::arrayGet($json, 'data.attributes.metadata', ''));

        $resource = Deck::create([
            'folder_id' => $folder ? $folder->id : null,
            'context_id' => $context->getId(),
            'context_type' => get_class($context),
            'name' => $name,
            'description' => $description,
            'metadata' => $metadata,
            'owner_id' => $user->id,
        ]);

        if (!$resource) {
            throw new RuntimeException('Could not create deck.');
        }

        return $resource;
    }

    /**
     * @return null|Course|User
     */
    private function getContextFromJson(array $json)
    {
        $relation = 'data.relationships.' . DeckSchema::REL_CONTEXT . '.data';
        $resourceId = self::arrayGet($json, $relation . '.id');
        if ($this->validateResourceObject($json, $relation, CourseSchema::TYPE)) {
            return Course::find($resourceId);
        }
        if ($this->validateResourceObject($json, $relation, UserSchema::TYPE)) {
            return User::find($resourceId);
        }

        return null;
    }

    /**
     * @return null|Folder
     * @throws RecordNotFoundException
     */
    private function getFolderFromJson(array $json)
    {
        $relation = 'data.relationships.' . DeckSchema::REL_FOLDER . '.data';
        if (self::arrayGet($json, $relation) === null) {
            return null;
        }

        $resourceId = self::arrayGet($json, $relation . '.id');
        if (
            !$this->validateResourceObject($json, $relation, FolderSchema::TYPE) ||
            !Folder::exists($resourceId)
        ) {
            throw new RecordNotFoundException('Invalid `parent` relationship.');
        }

        return Folder::find($resourceId);
    }
}
