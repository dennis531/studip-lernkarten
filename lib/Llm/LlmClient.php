<?php

namespace Lernkarten\Llm;

abstract class LlmClient
{
    private static $instance;

    protected $api_url;

    /**
     * Get singleton instance of client
     *
     * @return static
     */
    public static function getInstance(): OpenaiClient
    {
        if (empty(self::$instance)) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    protected function __construct() {}

    /**
     * Loads global api key
     *
     * @return string OpenAI API Key
     */
    protected static function getGlobalApiKey(): ?string
    {
        return \Config::get()->getValue('LERNKARTEN_API_KEY');
    }

    /**
     * Checks if global api key is set
     *
     * @return bool global api key provided
     */
    public static function hasGlobalApiKey(): bool
    {
        return !empty(static::getGlobalApiKey());
    }

    /**
     * Loads name of global chat model
     *
     * @return string global chat model
     */
    public static function getGlobalChatModel(): ?string
    {
        return \Config::get()->getValue('LERNKARTEN_CHAT_MODEL');
    }

    /**
     * Sends a request to the LLM
     *
     * @param string $system_prompt
     * @param string $user_prompt
     * @return mixed json decoded response
     */
    public abstract function request(string $system_prompt, string $user_prompt);
}