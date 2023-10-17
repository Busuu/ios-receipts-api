<?php

namespace Busuu\IosReceiptsApi;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class AppleClient
{
    /** @var  Client $client */
    private Client $client;
    /** @var  string */
    private string $password;

    public function __construct($password)
    {
        $this->client = new Client();
        $this->password = $password;
    }

    /**
     * Fetch the receipt from apple
     *
     * @param $receiptData
     * @param $endpoint
     *
     * @return array
     * @throws Exception|GuzzleException
     */
    public function fetchReceipt($receiptData, $endpoint): array
    {
        $data = [
            'password' => $this->password,
            'receipt-data' => $receiptData
        ];

        $response = $this->client->post($endpoint, [
            'body' => json_encode($data),
            'timeout' => 20
        ]);

        $jsonResponse = json_decode($response->getBody(), true);

        if (null !== $jsonResponse) {
            return $jsonResponse;
        }

        throw new Exception(sprintf('Invalid Response from Apple Server: %s', $response->getBody()));
    }
}
