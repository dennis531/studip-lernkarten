<?php

class CreateLlmConfigs extends Migration
{
    public function description()
    {
        return 'Creates the configurations for the large language model client.';
    }

    public function up()
    {
        $cfg = Config::get();

        // Config for global api key
        $cfg->create('LERNKARTEN_API_KEY',
            [
                'type' => 'string',
                'range' => 'global',
                'section' => 'Lernkarten',
                'description' => 'Der globale OpenAI-API-Key.'
            ]
        );

        // Config for model
        $cfg->create('LERNKARTEN_CHAT_MODEL',
            [
                'value' => 'gpt-4o-mini',
                'type' => 'string',
                'range' => 'global',
                'section' => 'Lernkarten',
                'description' => "Das Chat Model von OpenAI. Das Model muss mit der Chat Completions API von OpenAI kompatibel sein."

            ]
        );
    }

    public function down()
    {
        $cfg = Config::get();

        $cfg->delete('LERNKARTEN_API_KEY');
        $cfg->delete('LERNKARTEN_CHAT_MODEL');
    }
}
