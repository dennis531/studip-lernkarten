<?php

namespace Lernkarten\JsonApi\Routes;

use InvalidArgumentException;
use Psr\Http\Message\ServerRequestInterface as Request;

trait JsonApiTrait
{
    public function can(Request $request, string $ability, $objectOrClass, ...$arguments): bool
    {
        $user = $this->getUser($request);
        if (is_object($objectOrClass)) {
            $class = get_class($objectOrClass);
            return $class::getPolicy()->$ability($user, $objectOrClass, ...$arguments);
        }

        if (class_exists($objectOrClass)) {
            return $objectOrClass::getPolicy()->$ability($user, ...$arguments);
        }

        throw new InvalidArgumentException();
    }

    public function cannot(Request $request, string $ability, $objectOrClass, ...$arguments): bool
    {
        return !$this->can($request, $ability, $objectOrClass, ...$arguments);
    }
}
