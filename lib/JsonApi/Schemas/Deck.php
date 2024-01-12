<?php

namespace Lernkarten\JsonApi\Schemas;

use Lernkarten\Models\Deck as DeckModel;
use Neomerx\JsonApi\Contracts\Schema\ContextInterface;
use Neomerx\JsonApi\Schema\Link;

class Deck extends SchemaProvider
{
    public const TYPE = 'lernkarten-decks';
    public const REL_CARDS = 'cards';
    public const REL_CONTEXT = 'context';
    public const REL_FOLDER = 'folder';
    public const REL_OWNER = 'owner';
    public const REL_SHARED_DECK = 'shared-deck';
    public const REL_SHARED_DECKS = 'shared-decks';
    public const REL_SHARED_WITH = 'shared-with';
    public const REL_TEMPLATE = 'template';

    /**
     * {@inheritdoc}
     * @param DeckModel $resource
     */
    public function getId($resource): ?string
    {
        return (string) $resource->id;
    }

    /**
     * {@inheritdoc}
     * @param DeckModel $resource
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function getAttributes($resource, ContextInterface $context): iterable
    {
        return [
            'name' => (string) $resource->name,
            'description' => (string) $resource->description,
            'metadata' => (string) $resource->metadata,
            'colearning' => (bool) $resource->colearning,
            'is-editable' => $this->userCan('update', $resource),
            'progress' => $resource->getProgress(),
            'mkdate' => date('c', $resource->mkdate),
            'chdate' => date('c', $resource->chdate),
        ];
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function getRelationships($resource, ContextInterface $context): iterable
    {
        $relationships = [];

        $cards = $resource->cards;
        $relationships[self::REL_CARDS] = [
            self::RELATIONSHIP_LINKS => [
                Link::RELATED => $this->getRelationshipRelatedLink($resource, self::REL_CARDS),
            ],
            self::RELATIONSHIP_DATA => $cards,
        ];

        $context = $resource->getContext();
        $relationships[self::REL_CONTEXT] = [
            self::RELATIONSHIP_LINKS => [
                Link::RELATED => $this->createLinkToResource($context),
            ],
            self::RELATIONSHIP_DATA => $context,
        ];

        $folder = $resource->folder;
        $relationships[self::REL_FOLDER] = [
            self::RELATIONSHIP_LINKS => $folder
                ? [
                    Link::RELATED => $this->createLinkToResource($folder),
                ]
                : [],
            self::RELATIONSHIP_DATA => $folder,
        ];

        $owner = $resource->owner;
        $relationships[self::REL_OWNER] = [
            self::RELATIONSHIP_LINKS => [
                Link::RELATED => $this->createLinkToResource($owner),
            ],
            self::RELATIONSHIP_DATA => $owner,
        ];

        $sharedDeck = $resource->shared_deck;
        $relationships[self::REL_SHARED_DECK] = [
            self::RELATIONSHIP_LINKS => $sharedDeck
                ? [
                    Link::RELATED => $this->createLinkToResource($sharedDeck),
                ]
                : [],
            self::RELATIONSHIP_DATA => $sharedDeck,
        ];

        $relationships[self::REL_SHARED_DECKS] = [
            self::RELATIONSHIP_LINKS => [
                Link::RELATED => $this->getRelationshipRelatedLink(
                    $resource,
                    self::REL_SHARED_DECKS
                ),
            ],
            self::RELATIONSHIP_DATA => $resource->shared_decks,
        ];

        $relationships[self::REL_SHARED_WITH] = [
            self::RELATIONSHIP_LINKS => [
                Link::RELATED => $this->getRelationshipRelatedLink(
                    $resource,
                    self::REL_SHARED_WITH
                ),
            ],
            self::RELATIONSHIP_DATA => $resource->getSharedWith(),
        ];

        $template = $resource->template;
        $relationships[self::REL_TEMPLATE] = [
            self::RELATIONSHIP_LINKS => $template
                ? [
                    Link::RELATED => $this->createLinkToResource($template),
                ]
                : [],
            self::RELATIONSHIP_DATA => $template,
        ];

        return $relationships;
    }

    public function hasResourceMeta($resource): bool
    {
        return true;
    }

    public function getResourceMeta($resource)
    {
        return [
            'cards-count' => $resource->getNumberOfCards(),
        ];
    }
}
