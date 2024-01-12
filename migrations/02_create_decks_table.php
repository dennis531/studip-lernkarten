<?php

class CreateDecksTable extends Migration
{
    public function description()
    {
        return 'Creates the decks table in the database.';
    }

    public function up()
    {
        $dbm = \DBManager::get();
        $dbm->exec("
            CREATE TABLE `lernkarten_decks` (
              `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
              `name` varchar(300) COLLATE utf8mb4_unicode_ci NOT NULL,
              `description` text COLLATE utf8mb4_unicode_ci,
              `metadata` text COLLATE utf8mb4_unicode_ci,
              `owner_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
              `folder_id` int(11) UNSIGNED NULL,
              `context_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
              `context_type` varchar(300) NOT NULL,
              `shared_deck_id` int(11) UNSIGNED NULL,
              `template_id` int(11) UNSIGNED,
              `colearning` tinyint UNSIGNED NOT NULL DEFAULT '0',
              `mkdate` int(11) NOT NULL,
              `chdate` int(11) NOT NULL,
              PRIMARY KEY (`id`),
              INDEX `index_owner_id` (`owner_id`))");
    }

    public function down()
    {
        $dbm = \DBManager::get();
        $dbm->exec('DROP TABLE IF EXISTS `lernkarten_decks`');
    }
}
