<?php

class CreateWordLimitConfig extends Migration
{
    public function description()
    {
        return 'Create a configuration for the maximum supported word count in PDF files.';
    }

    public function up()
    {
        $cfg = Config::get();

        // Config for word limit
        $cfg->create('LERNKARTEN_WORD_LIMIT',
            [
                'value' => 10000,
                'type' => 'integer',
                'range' => 'global',
                'section' => 'Lernkarten',
                'description' => 'Die maximale Wortanzahl in einer Datei zur Generierung von Lernkarten. Das Limit sollte abhängig vom Context Window des konfigurierten Chat Models festgelegt werden.'
            ]
        );
    }

    public function down()
    {
        $cfg = Config::get();

        $cfg->delete('LERNKARTEN_WORD_LIMIT');
    }
}
