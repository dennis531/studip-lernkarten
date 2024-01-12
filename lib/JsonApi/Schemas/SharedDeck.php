<?php

namespace Lernkarten\JsonApi\Schemas;

use Lernkarten\Models\Deck as DeckModel;
use Lernkarten\Models\SharedDeck as SharedDeckModel;
use Neomerx\JsonApi\Contracts\Schema\ContextInterface;
use Neomerx\JsonApi\Schema\Link;

class SharedDeck extends SchemaProvider
{
    public const TYPE = 'lernkarten-shared-decks';
    public const REL_COLEARNING_DECK = 'colearning-deck';
    public const REL_DECK = 'deck';
    public const REL_RECIPIENT = 'recipient';
    public const REL_SHARER = 'sharer';

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

        $colearningDeck = $resource->getColearningDeck($this->currentUser);
        $relationships[self::REL_COLEARNING_DECK] = [
            self::RELATIONSHIP_LINKS => $colearningDeck
                ? [
                    Link::RELATED => $this->createLinkToResource($colearningDeck),
                ]
                : [],
            self::RELATIONSHIP_DATA => $colearningDeck,
        ];

        $deck = $resource->deck;
        $relationships[self::REL_DECK] = [
            self::RELATIONSHIP_LINKS => $deck
                ? [
                    Link::RELATED => $this->createLinkToResource($deck),
                ]
                : [],
            self::RELATIONSHIP_DATA => $deck,
        ];

        $recipient = $resource->getRecipient();
        $relationships[self::REL_RECIPIENT] = [
            self::RELATIONSHIP_LINKS => [
                Link::RELATED => $this->createLinkToResource($recipient),
            ],
            self::RELATIONSHIP_DATA => $recipient,
        ];

        $sharer = $resource->sharer;
        $relationships[self::REL_SHARER] = [
            self::RELATIONSHIP_LINKS => [
                Link::RELATED => $this->createLinkToResource($sharer),
            ],
            self::RELATIONSHIP_DATA => $sharer,
        ];

        return $relationships;
    }
}
