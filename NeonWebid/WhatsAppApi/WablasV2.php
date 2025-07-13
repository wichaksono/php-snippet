<?php

namespace NeonWebid\WhatsAppApi;

/**
 * Class WablasV2
 *
 * A simple wrapper for sending WhatsApp messages using Wablas API v2.0.
 * Supports sending plain text messages to one or multiple recipients via HTTP POST (form-data).
 * 
 * example usage:
 * ```php
 * use NeonWebid\WhatsAppApi\WablasV2;
 * require_once __DIR__ . '/WablasV2.php';
 * 
 * $wa = WablasV2::setup(
 *     'https://xxx.wablas.com',
 *     'xxxxxxxxxxx',
 *     'xxx'
 * );
 * 
 * // Send a message to a single recipient
 * $response = $wa->sendMessage('6281234563090', 'Hello 👋 world! 🌍');
 * 
 * // Send a message to multiple recipients
 * $response = $wa->sendMessage('6281234563090,6281234563091', 'Hello 👋 everyone! 🌍');
 *
 * // Send bulk messages using JSON payload
 * $messages = [
 *    ['phone' => '6281234563090', 'message' => 'Hello 👋 world! 🌍'],
 *    ['phone' => '6281234563091', 'message' => 'Hello 👋 everyone! 🌍'],
 * ];
 * 
 * $response = $wa->sendBulkMessage($messages);
 * 
 * // Print the response
 * print_r($response);
 * ```
 *
 * @package NeonWebid\WhatsAppApi
 */
final class WablasV2
{
    /**
     * @var string $server
     * The base URL of the Wablas API server (e.g. https://xxx.wablas.com)
     */
    private string $server;

    /**
     * @var string $token
     * The Wablas API token assigned to your account.
     */
    private string $token;

    /**
     * @var string $secretKey
     * The secret key associated with your Wablas token.
     */
    private string $secretKey;

    /**
     * Private constructor. Use static setup() method to initialize the class.
     *
     * @param string $server     Base URL of the Wablas server
     * @param string $token      API token
     * @param string $secretKey  API secret key
     */
    private function __construct(string $server, string $token, string $secretKey)
    {
        $this->server     = rtrim($server, '/');
        $this->token      = $token;
        $this->secretKey  = $secretKey;
    }

    /**
     * Initialize a new instance of WablasV2.
     *
     * @param string $server     Wablas base URL
     * @param string $token      Your Wablas API token
     * @param string $secretKey  Your Wablas secret key
     * @return self              Instance of WablasV2
     */
    public static function setup(string $server, string $token, string $secretKey): self
    {
        return new self($server, $token, $secretKey);
    }

    /**
     * Send a plain text WhatsApp message.
     *
     * @param string $phone     Destination phone number(s). Separate with commas if multiple.
     * @param string $message   The message body to send.
     * @return array            The response from the Wablas API.
     */
    public function sendMessage(string $phone, string $message): array
    {
        $endpoint = $this->server . '/api/send-message';

        $headers = [
            'Authorization: ' . $this->token . '.' . $this->secretKey
        ];

        $postFields = [
            'phone'   => $phone,
            'message' => $message,
        ];

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL            => $endpoint,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $postFields,
            CURLOPT_HTTPHEADER     => $headers,
        ]);

        $response = curl_exec($curl);
        $error    = curl_error($curl);
        curl_close($curl);

        if ($error) {
            return [
                'success' => false,
                'error'   => $error,
            ];
        }

        return json_decode($response, true);
    }

    /**
     * Send bulk text messages using JSON payload (Wablas v2).
     *
     * @param array $messages Array of messages. Each item must contain 'phone', 'message', and optionally 'source'.
     * @return array API response as associative array
     */
    public function sendBulkMessage(array $messages): array
    {
        $endpoint = $this->server . '/api/v2/send-message';

        $payload = json_encode(['data' => $messages]);

        $headers = [
            'Authorization: ' . $this->token . '.' . $this->secretKey,
            'Content-Type: application/json'
        ];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $endpoint,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_HTTPHEADER     => $headers,
        ]);

        $response = curl_exec($ch);
        $error    = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return [
                'success' => false,
                'error'   => $error,
            ];
        }

        return json_decode($response, true);
    }

}
