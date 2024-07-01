<?php

namespace Lernkarten\JsonApi\Routes;

use JsonApi\NonJsonApiController as StudipNonJsonApiController;

class NonJsonApiController extends StudipNonJsonApiController
{
    use JsonApiTrait;
}
