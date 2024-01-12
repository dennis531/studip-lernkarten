<?php

class CreateFoldersTable extends Migration
{
    public function description()
    {
        return 'Creates the folders table in the database.';
    }

    public function up()
    {
        $dbm = \DBManager::get();
        $dbm->exec("
            CREATE TABLE `lernkarten_folders` (
              `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
              `parent_id` int(11) UNSIGNED NULL,
              `context_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
              `context_type` varchar(300) NOT NULL,
              `name` varchar(300) NOT NULL,
              `mkdate` int(11) NOT NULL,
              `chdate` int(11) NOT NULL,
              PRIMARY KEY (`id`),
              INDEX `index_parent_id` (`parent_id`))");
    }

    public function down()
    {
        $dbm = \DBManager::get();
        $dbm->exec('DROP TABLE IF EXISTS `lernkarten_folders`');
    }
}
