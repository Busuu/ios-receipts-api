<?php

namespace Busuu\IosReceiptsApi;

use Busuu\IosReceiptsApi\Model\AppStoreReceipt;
use IosReceiptValidatorBundle\Services\AppleClient;

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
    /** @var  string base64-encoded receipt data */
    private $receiptData;
    /** @var array raw receipt received from the App store */
    private $receipt;

    /**
     * ReceiptService constructor.
     * @param string $password
     * @param string $environment
     */
    public function __construct($password, $environment = self::PRODUCTION_ENVIRONMENT)
    {
        $this->appleClient = new AppleClient($password);
        $this->validatorService = new ValidatorService();
        $this->environment = $environment;
    }

    /**
     * @param string $receiptData
     * @return self
     */
    public function setReceiptData($receiptData)
    {
        $this->receiptData = $receiptData;

        return $this;
    }

    /**
     * Get array containing the full response from the App store
     *
     * @return array
     */
    public function getFullReceipt()
    {
        if (!$this->receipt) {
            $this->receipt =  $this->getReceipt();
        }

        return $this->receipt;
    }

    /**
     * Get AppStoreReceipt object with the last purchase made for the user
     *
     * @return AppStoreReceipt|null
     * @throws \Exception
     */
    public function getLastPurchase()
    {
        if (!$this->receipt) {
            $this->receipt =  $this->getReceipt();
        }

        return $this->filterLastReceipt($this->receipt);
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
     * @return array
     * @throws \Exception
     */
    private function getReceipt()
    {
        if (!$this->receiptData) {
            throw new \Exception('Receipt data not initialized on receipt service');
        }

        // Fetch the receipt from production store
        $appleEndpoint = $this->getAppleEndpoint();
        $result = $this->appleClient->fetchReceipt($this->receiptData, $appleEndpoint);
        $status = $this->validatorService->validateReceipt($result);

        /**
         * As per Apple's advice, receipts should be first send to production environment, and if the "sandbox" response code is received, they should then sent to sandbox.
         * This means that no configuration change is necessary for working in either environment.
         */
        if ($status === ValidatorService::SANDBOX_REQUEST_RESPONSE) {
            $this->environment = self::SANDBOX_ENVIRONMENT;
            $appleEndpoint = $this->getAppleEndpoint();
            $result = $this->appleClient->fetchReceipt($this->receiptData, $appleEndpoint);
            $this->validatorService->validateReceipt($result);
            // Set the environment to production again
            $this->environment = self::PRODUCTION_ENVIRONMENT;
        // If the receipt is for production but was sent to sandbox, resend it to production
        } elseif ($status === ValidatorService::PRODUCTION_ENVIRONMENT_ERROR_CODE) {
            $this->environment = self::PRODUCTION_ENVIRONMENT;
            $appleEndpoint = $this->getAppleEndpoint();
            $result = $this->appleClient->fetchReceipt($this->receiptData, $appleEndpoint);
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
    private function filterLastReceipt(array $userReceipt)
    {
        // The user does not have any purchase
        if (empty($userReceipt['receipt']['in_app']) && empty($userReceipt['latest_receipt_info'])) {
            return null;
        }

        $latestReceiptData = [];
        /**
         * The App store is sending back the purchases in two different fields, "in_app" and "latest_receipt_info". They usually have the same content.
         * "latest_receipt_info" is deprecated but on some occasions returns purchases more recent than "in_app".
         * For the time being we merge the 2 arrays and parse everything.
         */
        $purchasesLists = array_merge($userReceipt['receipt']['in_app'], $userReceipt['latest_receipt_info']);
        $latestReceiptData = $this->searchLatestPurchase($purchasesLists, $latestReceiptData);

        if (empty($latestReceiptData)) {
            return null;
        }

        return $this->createAppStoreReceipt($latestReceiptData);
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

    /**
     * Create an AppStoreReceipt object from a single purchase returned by the App store
     *
     * @param array $storePurchase
     * @return AppStoreReceipt|null
     */
    private function createAppStoreReceipt(array $storePurchase)
    {
        if (empty($storePurchase)) {
            return null;
        }

        $receipt = new AppStoreReceipt();

        $cancellationTime = !empty($storePurchase['cancellation_date_ms']) ? $storePurchase['cancellation_date_ms'] : null;
        $receipt->setQuantity($storePurchase['quantity'])
            ->setProductId($storePurchase['product_id'])
            ->setTransactionId($storePurchase['transaction_id'])
            ->setOriginalTransactionId($storePurchase['original_transaction_id'])
            ->setPurchaseDate($storePurchase['purchase_date'])
            ->setPurchaseDateMs($storePurchase['purchase_date_ms'])
            ->setPurchaseDatePst($storePurchase['purchase_date_pst'])
            ->setOriginalPurchaseDate($storePurchase['original_purchase_date'])
            ->setOriginalPurchaseDateMs($storePurchase['original_purchase_date_ms'])
            ->setOriginalPurchaseDatePst($storePurchase['original_purchase_date_pst'])
            ->setExpiresDate($storePurchase['expires_date'])
            ->setExpiresDateMs($storePurchase['expires_date_ms'])
            ->setExpiresDatePst($storePurchase['expires_date_pst'])
            ->setWebOrderLineItemId($storePurchase['web_order_line_item_id'])
            ->setIsTrialPeriod($storePurchase['is_trial_period'])
            ->setCancellationDateMs($cancellationTime)
        ;

        return $receipt;
    }
}