<?php

namespace Lernkarten\JsonApi;

trait Routes
{
    public function registerAuthenticatedRoutes(\Slim\Routing\RouteCollectorProxy $group)
    {
        $group->get('/lernkarten-folders', Routes\FoldersIndex::class);
        $group->post('/lernkarten-folders', Routes\FoldersCreate::class);
        $group->patch('/lernkarten-folders/{id}', Routes\FoldersUpdate::class);
        $group->get('/lernkarten-folders/{id}', Routes\FoldersShow::class);
        $group->delete('/lernkarten-folders/{id}', Routes\FoldersDelete::class);

        $group->get('/lernkarten-decks', Routes\DecksIndex::class);
        $group->post('/lernkarten-decks', Routes\DecksCreate::class);
        $group->get('/lernkarten-decks/{id}', Routes\DecksShow::class);
        $group->patch('/lernkarten-decks/{id}', Routes\DecksUpdate::class);
        $group->delete('/lernkarten-decks/{id}', Routes\DecksDelete::class);

        // not a real JSONAPI route
        $group->post('/lernkarten-decks/{id}/copy', Routes\DecksCopy::class);

        $group->post('/lernkarten-cards', Routes\CardsCreate::class);
        $group->get('/lernkarten-cards/{id}', Routes\CardsShow::class);
        $group->patch('/lernkarten-cards/{id}', Routes\CardsUpdate::class);
        $group->delete('/lernkarten-cards/{id}', Routes\CardsDelete::class);

        $group->get('/lernkarten-shared-decks', Routes\SharedDecksIndex::class);
        $group->post('/lernkarten-shared-decks', Routes\SharedDecksCreate::class);
        $group->get('/lernkarten-shared-decks/{id}', Routes\SharedDecksShow::class);
        $group->delete('/lernkarten-shared-decks/{id}', Routes\SharedDecksDelete::class);

        // not real JSONAPI routes
        $group->post('/lernkarten-shared-decks/{id}/colearn', Routes\SharedDecksColearn::class);
        $group->post('/lernkarten-shared-decks/{id}/copy', Routes\SharedDecksCopy::class);

        $group->get('/lernkarten-folders/{id}/decks', Routes\DecksOfFoldersIndex::class);
        $group->get('/lernkarten-decks/{id}/cards', Routes\CardsOfDecksIndex::class);

        $group->get('/courses/{id}/lernkarten-decks', Routes\DecksOfCoursesIndex::class);
        $group->get('/users/{id}/lernkarten-decks', Routes\DecksOfUsersIndex::class);

        $group->get('/users/{id}/lernkarten-instances', Routes\InstancesOfUsersIndex::class);

        $group->get('/users/{id}/lernkarten-shared-decks', Routes\SharedDecksOfUsersIndex::class);
        $group->get('/courses/{id}/lernkarten-shared-decks', Routes\SharedDecksOfCoursesIndex::class);
    }

    public function registerUnauthenticatedRoutes(\Slim\Routing\RouteCollectorProxy $group)
    {
    }
}
