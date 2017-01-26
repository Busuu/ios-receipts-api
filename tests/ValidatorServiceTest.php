<?php
namespace Busuu\IosReceiptsApi\Tests;

use Busuu\IosReceiptsApi\Exception\InvalidReceiptException;
use Busuu\IosReceiptsApi\ValidatorService;

class ValidatorServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \Busuu\IosReceiptsApi\Exception\InvalidReceiptException
     * @expectedExceptionCode 0
     */
    public function testValidateReceiptMissingField()
    {
        $data = [];

        $validator = new ValidatorService();

        $validator->validateReceipt($data);
    }

    /**
     * @dataProvider validCodesProvider
     *
     * @param int $statusCode
     * @param int $returnCode
     */
    public function testValidateReceiptSuccessCode($statusCode, $returnCode)
    {
        $data = [
            'status' => $statusCode,
            'receipt' => []
        ];

        $validator = new ValidatorService();

        $this->assertEquals($returnCode, $validator->validateReceipt($data));
    }

    /**
     * @dataProvider invalidRequestCodesProvider
     *
     * @param int $statusCode
     *
     */
    public function testValidateReceiptInvalidCode($statusCode)
    {
        $data = [
            'status' => $statusCode,
            'receipt' => []
        ];

        $exceptionThrown = false;

        try {
            $validator = new ValidatorService();
            $validator->validateReceipt($data);
        } catch(\Exception $e) {
            $this->assertInstanceOf(InvalidReceiptException::class, $e);
            $this->assertEquals($statusCode, $e->getCode());
            $exceptionThrown = true;
        } finally {
            if (!$exceptionThrown) {
                throw new \RuntimeException('No exception was thrown');
            }
        }
    }


    /**
     * ["store status code", "validator response code"]
     *
     * @return array
     */
    public function validCodesProvider()
    {
        return [
            [0 , 1],
            [21006 , 1],
            [21007 , 2],
            [21008 , 3],
        ];
    }

    /**
     * ["store status code"]
     *
     * @return array
     */
    public function invalidRequestCodesProvider()
    {
        return [
            [21000],
            [21002],
            [21003],
            [21004],
            [21005],
            [12345],
        ];
    }
}
