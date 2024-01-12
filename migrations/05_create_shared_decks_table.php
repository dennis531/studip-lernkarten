<?php

class CreateSharedDecksTable extends Migration
{
    public function description()
    {
        return 'Creates the shared_decks table in the database.';
    }

    public function up()
    {
        $dbm = \DBManager::get();
        $dbm->exec("
            CREATE TABLE `lernkarten_shared_decks` (
              `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
              `deck_id` int(11) UNSIGNED NULL,
              `sharer_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
              `recipient_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
              `recipient_type` varchar(300) NOT NULL,
              `mkdate` int(11) NOT NULL,
              `chdate` int(11) NOT NULL,
              PRIMARY KEY (`id`),
              INDEX `index_sharer_id` (`sharer_id`),
              INDEX `index_recipient_id` (`recipient_id`))");
    }

    public function down()
    {
        $dbm = \DBManager::get();
        $dbm->exec('DROP TABLE IF EXISTS `lernkarten_shared_decks`');
    }
}
