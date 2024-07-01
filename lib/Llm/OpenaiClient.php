<?php

namespace Lernkarten\Llm;

class OpenaiClient extends LlmClient {

    protected $api_url = 'https://api.openai.com/v1/chat/completions';

    protected $temperature = 0.9;
    protected $top_p = 1;
    protected $frequency_penalty = 0;
    protected $presence_penalty = 0;

    public function request(string $system_prompt, string $user_prompt)
    {
        // Send request to OpenAI
        $ch = curl_init($this->api_url);

        $api_key = self::getGlobalApiKey();

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json",
            "Authorization: Bearer {$api_key}"
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $post_body = json_encode([
            'model' => self::getGlobalChatModel(),
            'messages' => [
                [
                    'role' => 'system',
                    'content' => $system_prompt,
                ],
                [
                    'role' => 'user',
                    'content' => $user_prompt,
                ],
            ],
            'temperature' => $this->temperature,
            'top_p' => $this->top_p,
            'frequency_penalty' => $this->frequency_penalty,
            'presence_penalty' => $this->presence_penalty,
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_body);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        $error = curl_error($ch);
        curl_close($ch);
        if ($error || $http_code >= 400) {
            $error_response = json_decode($response, true);

            $error_msg = dgettext('lernkarten-plugin', "OpenAI-API-Fehler: {$error_response['error']['message']}.");

            throw new \Trails_Exception(500, $error_msg);
        }

        return json_decode($response, true);
    }
}