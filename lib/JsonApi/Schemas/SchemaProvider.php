<?php

namespace Lernkarten\JsonApi\Schemas;

use JsonApi\Schemas\SchemaProvider as StudipSchemaProvider;
use User;

abstract class SchemaProvider extends StudipSchemaProvider
{
    public function userCan(string $ability, ...$arguments): bool
    {
        $policyObject = current($arguments);
        $class = get_class($policyObject);

        return $class::getPolicy()->$ability($this->currentUser, ...$arguments);
    }
}
