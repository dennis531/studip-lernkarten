<?php

namespace Lernkarten\JsonApi;

trait Schemas
{
    public function registerSchemas(): array
    {
        return [
            \Lernkarten\Models\Card::class => Schemas\Card::class,
            \Lernkarten\Models\Deck::class => Schemas\Deck::class,
            \Lernkarten\Models\Folder::class => Schemas\Folder::class,
            \Lernkarten\Models\Instance::class => Schemas\Instance::class,
            \Lernkarten\Models\SharedDeck::class => Schemas\SharedDeck::class,
        ];
    }
}
