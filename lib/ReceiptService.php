<?php

namespace IosReceiptValidatorBundle\Services;

use IosReceiptValidatorBundle\Model\AppStoreReceipt;

class ReceiptService
{
    const PRODUCTION_ENVIRONMENT = 'production';
    const SANDBOX_ENVIRONMENT = 'sandbox';

    private $environment;
    private $productionEndpoint = 'https://buy.itunes.apple.com/verifyReceipt';
    private $sandboxEndpoint = 'https://sandbox.itunes.apple.com/verifyReceipt';
    /** @var  AppleClient $appleClient */
    private $appleClient;
    /** @var  ValidatorService $validatorService */
    private $validatorService;

    /**
     * ReceiptService constructor.
     * @param AppleClient $appleClient
     * @param ValidatorService $validatorService
     * @param string $environment
     */
    public function __construct(AppleClient $appleClient, ValidatorService $validatorService, $environment = self::PRODUCTION_ENVIRONMENT)
    {
        $this->appleClient = $appleClient;
        $this->validatorService = $validatorService;
        $this->environment = $environment;
    }

    /**
     * @param string $receiptData
     * @return array
     */
    public function getFullReceipt(string $receiptData) :array
    {
        return $this->getReceipt($receiptData);
    }
    /**
     * Method to choose the apple environment that we have to call
     *
     * @return string
     */
    private function getAppleEndpoint()
    {
        if ($this->environment === self::PRODUCTION_ENVIRONMENT) {
            $endpoint = $this->productionEndpoint;
        } elseif ($this->environment === self::SANDBOX_ENVIRONMENT) {
            $endpoint = $this->sandboxEndpoint;
        } else {
            throw new \InvalidArgumentException('Invalid environment');
        }

        return $endpoint;
    }

    /**
     * Get receipt data from store
     *
     * @param $receiptData
     * @return array
     */
    private function getReceipt($receiptData)
    {
        // Fetch the receipt from production store
        $appleEndpoint = $this->getAppleEndpoint();
        $result = $this->appleClient->fetchReceipt($receiptData, $appleEndpoint);
        $status = $this->validatorService->validateReceipt($result);

        // If Apple returns this code, it means that the receipt should be fetched from the sandbox store instead
        if ($status === ValidatorService::SANDBOX_REQUEST_RESPONSE) {
            $this->environment = self::SANDBOX_ENVIRONMENT;
            $appleEndpoint = $this->getAppleEndpoint();
            $result = $this->appleClient->fetchReceipt($receiptData, $appleEndpoint);
            $this->validatorService->validateReceipt($result);
            // Set the environment to production again
            $this->environment = self::PRODUCTION_ENVIRONMENT;
        // If the receipt is for production but was sent to sandbox, resend it to production
        } elseif ($status === ValidatorService::PRODUCTION_ENVIRONMENT_ERROR_CODE) {
            $this->environment = self::PRODUCTION_ENVIRONMENT;
            $appleEndpoint = $this->getAppleEndpoint();
            $result = $this->appleClient->fetchReceipt($receiptData, $appleEndpoint);
            $this->validatorService->validateReceipt($result);
        }

        return $result;
    }

    /**
     * Get the latest receipt from the store response and wrap it in a StoreReceipt object
     *
     * @param array $userReceipt
     * @return AppStoreReceipt|null
     */
    public function filterLastReceipt(array $userReceipt)
    {
        // The user does not have any purchase
        if (empty($userReceipt['receipt']['in_app']) && empty($userReceipt['latest_receipt_info'])) {
            return null;
        }

        $latestReceiptData = [];
        // The amazing apple is sending to us the information in different ways in every subscription and we should check both paces
        $purchasesLists = array_merge($userReceipt['receipt']['in_app'], $userReceipt['latest_receipt_info']);
        $latestReceiptData = $this->searchLatestPurchase($purchasesLists, $latestReceiptData);

        if (empty($latestReceiptData)) {
            return null;
        }

        $latestReceipt = new AppStoreReceipt();

        $cancellationTime = !empty($latestReceiptData['cancellation_date_ms']) ? $latestReceiptData['cancellation_date_ms'] : null;
        $latestReceipt->setQuantity($latestReceiptData['quantity'])
            ->setProductId($latestReceiptData['product_id'])
            ->setTransactionId($latestReceiptData['transaction_id'])
            ->setOriginalTransactionId($latestReceiptData['original_transaction_id'])
            ->setPurchaseDate($latestReceiptData['purchase_date'])
            ->setPurchaseDateMs($latestReceiptData['purchase_date_ms'])
            ->setPurchaseDatePst($latestReceiptData['purchase_date_pst'])
            ->setOriginalPurchaseDate($latestReceiptData['original_purchase_date'])
            ->setOriginalPurchaseDateMs($latestReceiptData['original_purchase_date_ms'])
            ->setOriginalPurchaseDatePst($latestReceiptData['original_purchase_date_pst'])
            ->setExpiresDate($latestReceiptData['expires_date'])
            ->setExpiresDateMs($latestReceiptData['expires_date_ms'])
            ->setExpiresDatePst($latestReceiptData['expires_date_pst'])
            ->setWebOrderLineItemId($latestReceiptData['web_order_line_item_id'])
            ->setIsTrialPeriod($latestReceiptData['is_trial_period'])
            ->setCancellationDateMs($cancellationTime)
        ;

        return $latestReceipt;
    }

    /**
     * check if the user cancel the subscription
     * 
     * @param AppStoreReceipt $receipt
     * @return bool
     */
    public function isCancelled(AppStoreReceipt $receipt)
    {
        return !empty($receipt->getCancellationDateMs()) ? true : false;
    }

    /**
     * @param array $purchasesList
     * @param array $latestReceiptData
     * @return array|mixed
     */
    private function searchLatestPurchase(array $purchasesList, array $latestReceiptData = [])
    {
        if (!empty($purchasesList)) {
            // Loop in all the users receipt to get the latest receipt
            foreach ($purchasesList as $key => $value) {
                if (empty($latestReceiptData['original_purchase_date_ms']) || $latestReceiptData['original_purchase_date_ms'] < $value['original_purchase_date_ms']) {
                    $latestReceiptData = $value;
                }
            }
        } else {
            $latestReceiptData = null;
        }

        return $latestReceiptData;
    }
}