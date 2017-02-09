<?php


namespace Busuu\IosReceiptsApi\Tests;

use Busuu\IosReceiptsApi\AppleClient;
use Busuu\IosReceiptsApi\Model\AppStoreReceipt;
use Busuu\IosReceiptsApi\ReceiptService;
use Busuu\IosReceiptsApi\ValidatorService;
use Mockery\MockInterface;
use Tests\Helper\AppleHelper;

class ReceiptServiceTest extends \PHPUnit_Framework_TestCase
{
    /** @var  ReceiptService */
    private $receiptService;

    /** @var  MockInterface */
    private $appleClient;
    /** @var  MockInterface */
    private $validatorService;

    public function setup()
    {
        $this->appleClient = \Mockery::mock(AppleClient::class);
        $this->validatorService = \Mockery::mock(ValidatorService::class);

        $this->receiptService = new ReceiptService($this->appleClient, $this->validatorService, ReceiptService::PRODUCTION_ENVIRONMENT);
    }

    public function testGetLastReceiptSuccess()
    {
        $helper = new AppleHelper();

        $receiptData = $helper->getReceiptData();
        $storeReceiptData = $helper->getStoreReceiptDataValidSubscription();

        $this->appleClient
            ->shouldReceive('fetchReceipt')
            ->once()
            ->with($receiptData, 'https://buy.itunes.apple.com/verifyReceipt')
            ->andReturn($storeReceiptData)
        ;

        $this->validatorService
            ->shouldReceive('validateReceipt')
            ->once()
            ->andReturn(ValidatorService::SUCCESS_VALIDATION_RESPONSE);

        $this->receiptService->setReceiptData($receiptData);
        $storeReceipt = $this->receiptService->getLastPurchase();

        $this->assertInstanceOf(AppStoreReceipt::class, $storeReceipt);
        $this->assertEquals($storeReceiptData['receipt']['in_app'][0]['original_transaction_id'], $storeReceipt->getOriginalTransactionId());
        $this->assertEquals($storeReceiptData['receipt']['in_app'][0]['expires_date_ms'], $storeReceipt->getExpiresDateMs());
        $this->assertEquals($storeReceiptData['receipt']['in_app'][0]['purchase_date_ms'], $storeReceipt->getPurchaseDateMs());
        $this->assertEquals($storeReceiptData['receipt']['in_app'][0]['product_id'], $storeReceipt->getProductId());
    }

    public function testGetLastReceiptNoPurchase()
    {
        $helper = new AppleHelper();

        $receiptData = $helper->getReceiptData();
        $storeReceiptData = $helper->getStoreReceiptDataNoPurchase();

        $this->appleClient
            ->shouldReceive('fetchReceipt')
            ->once()
            ->with($receiptData, 'https://buy.itunes.apple.com/verifyReceipt')
            ->andReturn($storeReceiptData)
        ;

        $this->validatorService
            ->shouldReceive('validateReceipt')
            ->once()
            ->andReturn(ValidatorService::SUCCESS_VALIDATION_RESPONSE);

        $this->receiptService->setReceiptData($receiptData);
        $storeReceipt = $this->receiptService->getLastPurchase();

        $this->assertNull($storeReceipt);
    }
}
