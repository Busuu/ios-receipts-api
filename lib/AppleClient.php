<?php

namespace IosReceiptValidatorBundle\Services;

use GuzzleHttp\Client;

class AppleClient
{
    /** @var  Client $client */
    private $client;
    /** @var  string */
    private $password;

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
     * @return array
     */
    public function fetchReceipt($receiptData, $endpoint)
    {
        try {
            $data = [
                'password' => $this->password,
                'receipt-data' => $receiptData
            ];

            $response = $this->client->post($endpoint, ['body' => json_encode($data)]);

            return json_decode($response->getBody(), true);
        } catch (\Exception $e) {
            throw new \InvalidArgumentException('Error in the communication with Apple');
        }
    }
}