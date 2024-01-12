<?php

class CreateCardsTable extends Migration
{
    public function description()
    {
        return 'Creates the cards table in the database.';
    }

    public function up()
    {
        $dbm = \DBManager::get();
        $dbm->exec("
            CREATE TABLE `lernkarten_cards` (
              `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
              `note_id` int(11) UNSIGNED NOT NULL,
              `original_card_id` int(11) UNSIGNED NOT NULL,
              `deck_id` int(11) UNSIGNED NOT NULL,
              `due` int(11) NULL,
              `stability` float NULL,
              `difficulty` float NULL,
              `elapsed_days` int UNSIGNED NULL,
              `scheduled_days` int UNSIGNED NULL,
              `reps` int UNSIGNED NULL,
              `lapses` int UNSIGNED NULL,
              `state` tinyint UNSIGNED NULL,
              `last_review` int(11) UNSIGNED NULL,
              `again_count` int UNSIGNED NULL,
              `hard_count` int UNSIGNED NULL,
              `good_count` int UNSIGNED NULL,
              `easy_count` int UNSIGNED NULL,
              `mkdate` int(11) NOT NULL,
              `chdate` int(11) NOT NULL,
              PRIMARY KEY (`id`),
              INDEX `index_note_id` (`note_id`),
              INDEX `index_deck_id` (`deck_id`))");
    }

    public function down()
    {
        $dbm = \DBManager::get();
        $dbm->exec('DROP TABLE IF EXISTS `lernkarten_cards`');
    }
}
