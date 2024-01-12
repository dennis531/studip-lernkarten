<?php

namespace Lernkarten\JsonApi\Routes;

use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\BadRequestException;
use JsonApi\Errors\RecordNotFoundException;
use Lernkarten\JsonApi\Schemas\Instance as InstanceSchema;
use Lernkarten\Models\Instance;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use User;

/**
 * Displays all instances of LernkartenPlugin of a User.
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class InstancesOfUsersIndex extends JsonApiController
{
    protected $allowedIncludePaths = [InstanceSchema::REL_RANGE];
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
        $resource = User::find($args['id']);
        if (!$resource) {
            throw new RecordNotFoundException();
        }

        if ($this->cannot($request, 'viewAnyOfUsers', Instance::class, $resource)) {
            throw new AuthorizationFailedException();
        }

        $instances = array_filter(
            $resource->course_memberships->map(function ($membership) {
                return Instance::findForRange($membership->course);
            })
        );

        return $this->getPaginatedContentResponse(
            array_slice($instances, ...$this->getOffsetAndLimit()),
            count($instances)
        );
    }
}
