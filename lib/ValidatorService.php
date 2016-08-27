<?php

namespace IosReceiptValidatorBundle\Services;
use CommonBundle\Exception\ApiHttpException;
use CommonBundle\Exception\ApiInvalidArgumentException;

/**
 * You can see the apple response documentation here: https://developer.apple.com/library/ios/releasenotes/General/ValidateAppStoreReceipt/Chapters/ValidateRemotely.html#//apple_ref/doc/uid/TP40010573-CH104-SW1
 *
 * Class ValidatorService
 * @package IosReceiptValidatorBundle\Services
 */
class ValidatorService
{
    /*
     * Error returned by apple to indicates that the receipt is a sandbox receipt. This is happening when
     * apple is testing the app and is calling to our backend in production mode with a sandbox receipt-data
     *
     * This receipt is from the test environment, but it was sent to the production environment for verification. Send it to the test environment instead.
     */
    const SANDBOX_ENVIRONMENT_ERROR_CODE = 21007;
    // This receipt is from the production environment, but it was sent to the test environment for verification. Send it to the production environment instead.
    const PRODUCTION_ENVIRONMENT_ERROR_CODE = 21008;
    // The App Store could not read the JSON object you provided.
    const INVALID_REQUEST_JSON_ERROR_CODE = 21000;
    // The data in the receipt-data property was malformed or missing.
    const MALFORMED_RECEIPT_DATA_ERROR_CODE = 21002;
    // The receipt could not be authenticated.
    const RECEIPT_NOT_AUTHENTICATE_ERROR_CODE = 21003;
    /*
     * The shared secret you provided does not match the shared secret on file for your account.
     * Only returned for iOS 6 style transaction receipts for auto-renewable subscriptions.
     */
    const INVALID_CREDENTIALS_ERROR_CODE = 21004;
    // The receipt server is not currently available.
    const RECEIPT_SERVER_DOWN_ERROR_CODE = 21005;
    /*
     * This receipt is valid but the subscription has expired. When this status code is returned to your server, the receipt data is also decoded and returned as part of the response.
     * Only returned for iOS 6 style transaction receipts for auto-renewable subscriptions.
     */
    const EXPIRED_SUBSCRIPTION_CODE = 21006;
    // 0 if the receipt is valid
    const SUCCESS_RECEIPT_CODE = 0;

    /**
     * Busuu internal status codes
     */
    const SANDBOX_REQUEST_RESPONSE = 2;
    const PRODUCTION_REQUEST_RESPONSE = 3;
    const SUCCESS_VALIDATION_RESPONSE = 1;

    /**
     * @param $receiptInfo
     * @return int
     * @throws \Exception
     */
    public function validateReceipt(array $receiptInfo) :int
    {
        // Status is the only mandatory field
        if (!isset($receiptInfo['status'])) {
            throw new \InvalidArgumentException('The receipt sent by Apple is incorrect');
        }

        switch ($receiptInfo['status']) {
            case self::SANDBOX_ENVIRONMENT_ERROR_CODE:
                $return = self::SANDBOX_REQUEST_RESPONSE;
                break;
            case self::PRODUCTION_ENVIRONMENT_ERROR_CODE:
                $return = self::PRODUCTION_REQUEST_RESPONSE;
                break;
            case self::EXPIRED_SUBSCRIPTION_CODE:
            case self::SUCCESS_RECEIPT_CODE:
                $return = self::SUCCESS_VALIDATION_RESPONSE;
                break;
            case self::INVALID_REQUEST_JSON_ERROR_CODE:
                throw new \InvalidArgumentException('The App Store could not read the JSON object you provided.');
                break;
            case self::MALFORMED_RECEIPT_DATA_ERROR_CODE:
                throw new \InvalidArgumentException('The data in the receipt-data property was malformed or missing.');
                break;
            case self::RECEIPT_NOT_AUTHENTICATE_ERROR_CODE:
                throw new \InvalidArgumentException('The receipt could not be authenticated.');
                break;
            case self::INVALID_CREDENTIALS_ERROR_CODE:
                throw new \InvalidArgumentException('The shared secret you provided does not match the shared secret on file for your account.');
                break;
            case self::RECEIPT_SERVER_DOWN_ERROR_CODE:
                throw new \Exception('The receipt server is not currently available.');
                break;
            default:
                throw new \Exception('The receipt does not contain a valid status.');
                break;
        }

        return $return;
    }
}