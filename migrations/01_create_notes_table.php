<?php

class CreateNotesTable extends Migration
{
    public function description()
    {
        return 'Creates the notes table in the database.';
    }

    public function up()
    {
        $dbm = \DBManager::get();
        $dbm->exec("
            CREATE TABLE `lernkarten_notes` (
              `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
              `guid` char(40) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
              `model` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
              `fields` json NOT NULL,
              `mkdate` int(11) NOT NULL,
              `chdate` int(11) NOT NULL,
              PRIMARY KEY (`id`),
              UNIQUE `index_guid` (`guid`))");
    }

    public function down()
    {
        $dbm = \DBManager::get();
        $dbm->exec('DROP TABLE IF EXISTS `lernkarten_notes`');
    }
}
