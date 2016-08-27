        $appStoreReceipt = $this->receiptService->getFullReceipt($receipt->getReceiptData());
        $lastReceipt = $this->receiptService->filterLastReceipt($appStoreReceipt);