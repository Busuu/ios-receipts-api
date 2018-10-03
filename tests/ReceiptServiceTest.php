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

    public function testGetLastPurchaseFromFullReceipt_emptyFullReceipt_shouldReturnNull()
    {
        // GIVEN
        $helper = new AppleHelper();
        $fullReceipt = $helper->getStoreReceiptDataNoPurchase();

        // WHEN / THEN
        $this->assertNull($this->receiptService->getLastPurchaseFromFullReceipt($fullReceipt));
    }

    public function testGetLastPurchaseFromFullReceipt_shouldReturnLastPurchaseAsAppStoreReceipt()
    {
        // GIVEN
        $helper = new AppleHelper();
        $fullReceipt = $helper->getStoreReceiptMultipleSubscriptions();

        // WHEN / THEN
        $result = $this->receiptService->getLastPurchaseFromFullReceipt($fullReceipt);

        $this->assertInstanceOf(AppStoreReceipt::class, $result);

        //Checking for the latest receipt details as within $fullReceipt.
        $this->assertEquals('com.busuu.app.subs1monthoptionC.switzerland', $result->getProductId());
        $this->assertEquals('140000164971107', $result->getOriginalTransactionId());
        $this->assertEquals('1450006697000', $result->getExpiresDateMs());
        $this->assertEquals('1447414697000', $result->getPurchaseDateMs());
    }

    public function testGetLastPurchaseFromFullReceiptWithCancelledPurchase_shouldReturnLastUncancelledPurchaseAsAppStoreReceipt()
    {
        // GIVEN
        $helper = new AppleHelper();
        $fullReceipt = $helper->getStoreReceiptLatestSubscriptionIsCancelled();

        // WHEN / THEN
        $result = $this->receiptService->getLastPurchaseFromFullReceipt($fullReceipt);

        $this->assertInstanceOf(AppStoreReceipt::class, $result);

        //Checking for the latest receipt details as within $fullReceipt.
        $this->assertEquals('com.busuu.app.subs12month.50_discount_sep_16', $result->getProductId());
        $this->assertEquals('710000330793142', $result->getOriginalTransactionId());
        $this->assertEquals('1568800595000', $result->getExpiresDateMs());
        $this->assertEquals('1537264595000', $result->getPurchaseDateMs());
        $this->assertNull($result->getCancellationDateMs());
    }
}
