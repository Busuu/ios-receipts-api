<?php


class ValidatorServiceTest extends \PHPUnit_Framework_TestCase
{
    public function testValidateReceiptMissingField()
    {
        $data = [];

        $validator = new \IosReceiptValidatorBundle\Services\ValidatorService();

        $this->expectException(\InvalidArgumentException::class);
        $validator->validateReceipt($data);
    }

    /**
     * @dataProvider validCodesProvider
     *
     * @param int $statusCode
     * @param int $returnCode
     */
    public function testValidateReceiptSuccessCode(int $statusCode, int $returnCode)
    {
        $data = [
            'status' => $statusCode,
            'receipt' => []
        ];

        $validator = new \IosReceiptValidatorBundle\Services\ValidatorService();

        $this->assertEquals($returnCode, $validator->validateReceipt($data));
    }

    /**
     * @dataProvider invalidRequestCodesProvider
     *
     * @param int $statusCode
     */
    public function testValidateReceiptInvalidCode(int $statusCode)
    {
        $data = [
            'status' => $statusCode,
            'receipt' => []
        ];

        $validator = new \IosReceiptValidatorBundle\Services\ValidatorService();
        $this->expectException(\InvalidArgumentException::class);
        $validator->validateReceipt($data);
    }

    /**
     * @dataProvider panicRequestCodesProvider
     *
     * @param int $statusCode
     */
    public function testValidateReceiptPanicCode(int $statusCode)
    {
        $data = [
            'status' => $statusCode,
            'receipt' => []
        ];

        $validator = new \IosReceiptValidatorBundle\Services\ValidatorService();
        $this->expectException(\Exception::class);
        $validator->validateReceipt($data);
    }

    /**
     * ["store status code", "validator response code"]
     *
     * @return array
     */
    public function validCodesProvider() :array
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
    public function invalidRequestCodesProvider() :array
    {
        return [
            [21000],
            [21002],
            [21003],
            [21004],
        ];
    }

    /**
     * ["store status code"]
     *
     * @return array
     */
    public function panicRequestCodesProvider() :array
    {
        return [
            [21005],
            [12345],
        ];
    }
}