<?php

namespace Busuu\IosReceiptsApi\Model;

class AppStoreReceipt
{
    private $quantity;
    private $productId;
    private $transactionId;
    private $originalTransactionId;
    private $purchaseDate;
    private $purchaseDateMs;
    private $purchaseDatePst;
    private $originalPurchaseDate;
    private $originalPurchaseDateMs;
    private $originalPurchaseDatePst;
    private $expiresDate;
    private $expiresDateMs;
    private $expiresDatePst;
    private $webOrderLineItemId;
    private $isTrialPeriod;
    private $promotionalOfferId;

    /**
     * An array of elements that refers to auto-renewable subscription renewals that are open or failed in the past.
     * https://developer.apple.com/documentation/appstorereceipts/responsebody/pending_renewal_info
     *
     * @var array|null
     */
    private ?array $pendingRenewalInfos;

    /**
     * @var int The cancellation date is set when Apple's customer service refunds the user purchase. In that case, consider that the purchase never happened.
     * There is no way to know if and when the user requested that their subscription should not renew at the end of the term, except from checking the expiration time afterward.
     */
    private int $cancellationDateMs;

    /**
     * @return mixed
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @param mixed $quantity
     * @return $this
     */
    public function setQuantity($quantity): AppStoreReceipt
    {
        $this->quantity = $quantity;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getProductId()
    {
        return $this->productId;
    }

    /**
     * @param mixed $productId
     * @return $this
     */
    public function setProductId($productId): AppStoreReceipt
    {
        $this->productId = $productId;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTransactionId()
    {
        return $this->transactionId;
    }

    /**
     * @param mixed $transactionId
     * @return $this
     */
    public function setTransactionId($transactionId): AppStoreReceipt
    {
        $this->transactionId = $transactionId;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getOriginalTransactionId()
    {
        return $this->originalTransactionId;
    }

    /**
     * @param mixed $originalTransactionId
     * @return $this
     */
    public function setOriginalTransactionId($originalTransactionId): AppStoreReceipt
    {
        $this->originalTransactionId = $originalTransactionId;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPurchaseDate()
    {
        return $this->purchaseDate;
    }

    /**
     * @param mixed $purchaseDate
     * @return $this
     */
    public function setPurchaseDate($purchaseDate): AppStoreReceipt
    {
        $this->purchaseDate = $purchaseDate;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPurchaseDateMs()
    {
        return $this->purchaseDateMs;
    }

    /**
     * @param mixed $purchaseDateMs
     * @return $this
     */
    public function setPurchaseDateMs($purchaseDateMs): AppStoreReceipt
    {
        $this->purchaseDateMs = $purchaseDateMs;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPurchaseDatePst()
    {
        return $this->purchaseDatePst;
    }

    /**
     * @param mixed $purchaseDatePst
     * @return $this
     */
    public function setPurchaseDatePst($purchaseDatePst): AppStoreReceipt
    {
        $this->purchaseDatePst = $purchaseDatePst;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getOriginalPurchaseDate()
    {
        return $this->originalPurchaseDate;
    }

    /**
     * @param mixed $originalPurchaseDate
     * @return $this
     */
    public function setOriginalPurchaseDate($originalPurchaseDate): AppStoreReceipt
    {
        $this->originalPurchaseDate = $originalPurchaseDate;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getOriginalPurchaseDateMs()
    {
        return $this->originalPurchaseDateMs;
    }

    /**
     * @param mixed $originalPurchaseDateMs
     * @return $this
     */
    public function setOriginalPurchaseDateMs($originalPurchaseDateMs): AppStoreReceipt
    {
        $this->originalPurchaseDateMs = $originalPurchaseDateMs;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getOriginalPurchaseDatePst()
    {
        return $this->originalPurchaseDatePst;
    }

    /**
     * @param mixed $originalPurchaseDatePst
     * @return $this
     */
    public function setOriginalPurchaseDatePst($originalPurchaseDatePst): AppStoreReceipt
    {
        $this->originalPurchaseDatePst = $originalPurchaseDatePst;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getExpiresDate()
    {
        return $this->expiresDate;
    }

    /**
     * @param mixed $expiresDate
     * @return $this
     */
    public function setExpiresDate($expiresDate): AppStoreReceipt
    {
        $this->expiresDate = $expiresDate;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getExpiresDateMs()
    {
        return $this->expiresDateMs;
    }

    /**
     * @param mixed $expiresDateMs
     * @return $this
     */
    public function setExpiresDateMs($expiresDateMs): AppStoreReceipt
    {
        $this->expiresDateMs = $expiresDateMs;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getExpiresDatePst()
    {
        return $this->expiresDatePst;
    }

    /**
     * @param mixed $expiresDatePst
     * @return $this
     */
    public function setExpiresDatePst($expiresDatePst): AppStoreReceipt
    {
        $this->expiresDatePst = $expiresDatePst;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getWebOrderLineItemId()
    {
        return $this->webOrderLineItemId;
    }

    /**
     * @param mixed $webOrderLineItemId
     * @return $this
     */
    public function setWebOrderLineItemId($webOrderLineItemId): AppStoreReceipt
    {
        $this->webOrderLineItemId = $webOrderLineItemId;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getIsTrialPeriod()
    {
        return $this->isTrialPeriod;
    }

    /**
     * @param mixed $isTrialPeriod
     * @return $this
     */
    public function setIsTrialPeriod($isTrialPeriod): AppStoreReceipt
    {
        $this->isTrialPeriod = $isTrialPeriod;
        return $this;
    }

    /**
     * @return int
     */
    public function getCancellationDateMs(): ?int
    {
        return $this->cancellationDateMs;
    }

    /**
     * @param mixed $cancellationDateMs
     * @return $this
     */
    public function setCancellationDateMs($cancellationDateMs): AppStoreReceipt
    {
        $this->cancellationDateMs = $cancellationDateMs;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPromotionalOfferId(): ?string
    {
        return $this->promotionalOfferId;
    }

    /**
     * @param string|null $promotionalOfferId
     * @return AppStoreReceipt
     */
    public function setPromotionalOfferId(?string $promotionalOfferId): AppStoreReceipt
    {
        $this->promotionalOfferId = $promotionalOfferId;

        return $this;
    }

    /**
     * @return array|null
     */
    public function getPendingRenewalInfos(): ?array
    {
        return $this->pendingRenewalInfos;
    }

    /**
     * @param array|null $pendingRenewalInfos
     */
    public function setPendingRenewalInfos(array $pendingRenewalInfos = null): AppStoreReceipt
    {
        $this->pendingRenewalInfos = $pendingRenewalInfos;

        return $this;
    }

    public function toArray(): array
    {
        return [
            'quantity' => $this->getQuantity(),
            'productId' => $this->getProductId(),
            'transactionId' => $this->getTransactionId(),
            'originalTransactionId' => $this->getOriginalTransactionId(),
            'purchaseDate' => $this->getPurchaseDate(),
            'purchaseDateMs' => $this->getPurchaseDateMs(),
            'purchaseDatePst' => $this->getPurchaseDatePst(),
            'originalPurchaseDate' => $this->getOriginalPurchaseDate(),
            'originalPurchaseDateMs' => $this->getOriginalPurchaseDateMs(),
            'originalPurchaseDatePst' => $this->getOriginalPurchaseDatePst(),
            'expiresDate' => $this->getExpiresDate(),
            'expiresDateMs' => $this->getExpiresDateMs(),
            'expiresDatePst' => $this->getExpiresDatePst(),
            'webOrderLineItemId' => $this->getWebOrderLineItemId(),
            'isTrialPeriod' => $this->getIsTrialPeriod(),
            'cancellationDateMs' => $this->getCancellationDateMs()
        ];
    }
}
