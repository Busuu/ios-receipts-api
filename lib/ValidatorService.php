<?php

namespace Busuu\IosReceiptsApi;

use Busuu\IosReceiptsApi\Exception\InvalidReceiptException;
use Busuu\IosReceiptsApi\Exception\UnauthorizedReceiptException;

/**
 * You can see the apple response documentation here: https://developer.apple.com/library/ios/releasenotes/General/ValidateAppStoreReceipt/Chapters/ValidateRemotely.html#//apple_ref/doc/uid/TP40010573-CH104-SW1
 *
 * Class ValidatorService
 * @package Busuu\IosReceiptsApi
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
     * This code is not shown in the documentation but users reported it in the forum without any official reply
     * We will treat it as a receipt server error
     * https://forums.developer.apple.com/thread/42678
     */
    const RECEIPT_SERVER_UNKNOWN_ERROR_CODE = 21009;
    /*
     * This receipt is valid but the subscription has expired. When this status code is returned to your server, the receipt data is also decoded and returned as part of the response.
     * Only returned for iOS 6 style transaction receipts for auto-renewable subscriptions.
     */
    const EXPIRED_SUBSCRIPTION_CODE = 21006;

    /*
     * "This receipt could not be authorized. Treat this the same as if a purchase was never made."
     * Unclear what causes this, but we're seeing it on some receipts.
     */
    const UNAUTHORIZED_RECEIPT = 21010;

    // 0 if the receipt is valid
    const SUCCESS_RECEIPT_CODE = 0;

    /**
     * Busuu internal status codes
     */
    const SANDBOX_REQUEST_RESPONSE = 2;
    const PRODUCTION_REQUEST_RESPONSE = 3;
    const SUCCESS_VALIDATION_RESPONSE = 1;

    /**
     * @param array $receiptInfo
     * @return int
     * @throws InvalidReceiptException
     * @throws UnauthorizedReceiptException
     */
    public function validateReceipt(array $receiptInfo): int
    {
        // Status is the only mandatory field
        if (!isset($receiptInfo['status'])) {
            throw new InvalidReceiptException('The receipt sent by Apple is incorrect');
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
            case self::UNAUTHORIZED_RECEIPT:
                throw new UnauthorizedReceiptException(
                    'This receipt could not be authorized. Treat this the same as if a purchase was never made.',
                    $receiptInfo['status']
                );
            case self::INVALID_REQUEST_JSON_ERROR_CODE:
                throw new InvalidReceiptException(
                    'The App Store could not read the JSON object you provided.',
                    $receiptInfo['status']
                );
            case self::MALFORMED_RECEIPT_DATA_ERROR_CODE:
                throw new InvalidReceiptException(
                    'The data in the receipt-data property was malformed or missing.',
                    $receiptInfo['status']
                );
            case self::RECEIPT_NOT_AUTHENTICATE_ERROR_CODE:
                throw new InvalidReceiptException('The receipt could not be authenticated.', $receiptInfo['status']);
            case self::INVALID_CREDENTIALS_ERROR_CODE:
                throw new InvalidReceiptException(
                    'The shared secret you provided does not match the shared secret on file for your account.',
                    $receiptInfo['status']
                );
            case self::RECEIPT_SERVER_DOWN_ERROR_CODE:
            case self::RECEIPT_SERVER_UNKNOWN_ERROR_CODE:
                throw new InvalidReceiptException(
                    'The receipt server is not currently available.',
                    $receiptInfo['status']
                );
            default:
                throw new InvalidReceiptException(
                    'The receipt does not contain a valid status.', $receiptInfo['status']
                );
        }

        return $return;
    }
}
