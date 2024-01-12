<?php

namespace Lernkarten\JsonApi\Schemas;

use Lernkarten\Models\Instance as InstanceModel;
use Neomerx\JsonApi\Contracts\Schema\ContextInterface;
use Neomerx\JsonApi\Schema\Link;

/**
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class Instance extends SchemaProvider
{
    public const TYPE = 'lernkarten-instances';
    public const REL_RANGE = 'range';

    /**
     * {@inheritdoc}
     * @param FolderModel $resource
     */
    public function getId($resource): ?string
    {
        $range = $resource->getRange();

        return join('_', [$range->getRangeType(), $range->getRangeId()]);
    }

    /**
     * {@inheritdoc}
     * @param FolderModel $resource
     */
    public function getAttributes($resource, ContextInterface $context): iterable
    {
        return [
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getRelationships($resource, ContextInterface $context): iterable
    {
        $relationships = [];

        $range = $resource->getRange();
        $relationships[self::REL_RANGE] = [
            self::RELATIONSHIP_LINKS => [
                Link::RELATED => $this->createLinkToResource($range),
            ],
            self::RELATIONSHIP_DATA => $range,
        ];

        return $relationships;
    }
}
