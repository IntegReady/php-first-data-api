<?php

namespace integready\firstdataapi;

use GuzzleHttp\Ring\Client\CurlHandler;
use stdClass;

/**
 * Class FirstData
 */
class FirstData
{
    public const LIVE_API_URL = 'https://api.globalgatewaye4.firstdata.com/transaction/';
    public const TEST_API_URL = 'https://api.demo.globalgatewaye4.firstdata.com/transaction/';

    /**
     * @var string - the API username
     */
    protected $username;

    /**
     * @var string - the API password
     */
    protected $password;

    /**
     * @var int - API transaction type
     */
    protected $transactionType = '00';

    /**
     * The error code if one exists
     * @var int
     */
    protected $errorCode = 0;

    /**
     * The error message if one exists
     * @var string
     */
    protected $errorMessage = '';

    /**
     * The response message
     * @var string
     */
    protected $response = '';

    /**
     * The headers returned from the call made
     * @var array
     */
    protected $headers = '';

    /**
     * The response represented as an array
     * @var array
     */
    protected $arrayResponse = [];

    /**
     * All the post fields we will add to the call
     * @var array
     */
    protected $postFields = [];

    /**
     * The API type we are about to call
     * @var string
     */
    protected $apiVersion = 'v12';

    /**
     * The API key id needed for hmac headers
     * @var string
     */
    protected $apiId = '';

    /**
     * The API key needed for hmac headers
     * @var string
     */
    protected $apiKey = '';

    /**
     * @var bool - set whether we are in a test mode or not
     */
    public static $testMode = false;

    /**
     * Default options for CURL.
     */
    public static $CURL_OPTS = [
        CURLOPT_CONNECTTIMEOUT => 30,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 60,
        CURLOPT_FRESH_CONNECT  => 1,
        CURLOPT_PORT           => 443,
        CURLOPT_USERAGENT      => 'curl-php',
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_CUSTOMREQUEST  => 'POST',
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json; charset=UTF-8;', 'Accept: application/json'],
    ];

    /**
     * Transaction types
     */
    public const TRAN_PURCHASE              = '00';
    public const TRAN_PREAUTH               = '01';
    public const TRAN_PREAUTHCOMPLETE       = '02';
    public const TRAN_FORCEDPOST            = '03';
    public const TRAN_REFUND                = '04';
    public const TRAN_PREAUTHONLY           = '05';
    public const TRAN_PAYPALORDER           = '07';
    public const TRAN_VOID                  = '13';
    public const TRAN_TAGGEDPREAUTHCOMPLETE = '32';
    public const TRAN_TAGGEDVOID            = '33';
    public const TRAN_TAGGEDREFUND          = '34';
    public const TRAN_CASHOUT               = '83';
    public const TRAN_ACTIVATION            = '85';
    public const TRAN_BALANCEINQUIRY        = '86';
    public const TRAN_RELOAD                = '88';
    public const TRAN_DEACTIVATION          = '89';

    /**
     * FirstData constructor.
     *
     * @param string $username - username
     * @param string $password - password
     * @param string $hmacID - HMAC ID
     * @param string $hmacKey - HMAC Key
     * @param bool $debug - debug mode
     */
    public function __construct(string $username, string $password, $hmacID = '', $hmacKey = '', $debug = false)
    {
        $this->username = $username;
        $this->password = $password;
        $this->apiId    = $hmacID;
        $this->apiKey   = $hmacKey;

        $this->setTestMode((bool)$debug);
    }

    /**
     * Set the API username we are going to user
     *
     * @param string $username - the API username
     *
     * @return self
     */
    public function setUsername($username): self
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Set the API password we are going to user
     *
     * @param string $password - the API password
     *
     * @return self
     */
    public function setPassword($password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Return the post data fields as an array
     * @return array
     */
    public function getPostData(): array
    {
        return $this->postFields;
    }

    /**
     * Set post fields
     *
     * @param mixed $key
     * @param mixed $value
     *
     * @return self
     */
    public function setPostData($key, $value = null): self
    {
        if (is_array($key) && !$value) {
            foreach ($key as $k => $v) {
                $this->postFields[$k] = $v;
            }
        } else {
            $this->postFields[$key] = $value;
        }

        return $this;
    }

    /**
     * Set the API version we are going to use
     *
     * @param string $version the new API version
     *
     * @return self
     */
    public function setApiVersion($version): self
    {
        $this->apiVersion = $version;

        return $this;
    }

    /**
     * Set the API id we are going to use for hmac hash
     *
     * @param string $id the new API id
     *
     * @return self
     */
    public function setApiId($id): self
    {
        $this->apiId = $id;

        return $this;
    }

    /**
     * Set the API key we are going to use for hmac hash
     *
     * @param string $key the new API key
     *
     * @return self
     */
    public function setApiKey($key): self
    {
        $this->apiKey = $key;

        return $this;
    }

    /**
     * Set whether we are in a test mode or not
     *
     * @param bool $value
     *
     * @return void
     */
    public function setTestMode($value): void
    {
        self::$testMode = (bool)$value;
    }

    /**
     * Set transaction type
     *
     * @param int $transactionType
     *
     * @return self
     */
    public function setTransactionType($transactionType): self
    {
        $this->transactionType = $transactionType;

        return $this;
    }

    /**
     * Return transaction type
     * @return int
     */
    public function getTransactionType()
    {
        return $this->transactionType;
    }

    /**
     * Set credit card number
     *
     * @param int $number
     *
     * @return self
     */
    public function setCreditCardNumber($number): self
    {
        $this->setPostData('cc_number', (string)$number);

        return $this;
    }

    /**
     * Set credit card type
     *
     * @param int $type
     *
     * @return self
     */
    public function setCreditCardType($type): self
    {
        $this->setPostData('credit_card_type', $type);

        return $this;
    }

    /**
     * =====================================================================================
     * Setting Track 1 and Track 2 data allows input from USB credit card swiper
     * For format of track data see: http://www.gae.ucm.es/~padilla/extrawork/magexam1.html
     * =====================================================================================
     */

    /**
     * Set Track1 data
     *
     * @param string $track
     *
     * @return self
     */
    public function setTrack1($track): self
    {
        $this->setPostData('track1', $track);

        return $this;
    }

    /**
     * Set Track2 data
     *
     * @param string $track
     *
     * @return self
     */
    public function setTrack2($track): self
    {
        $this->setPostData('track2', $track);

        return $this;
    }

    /**
     * Set credit card holder name
     *
     * @param string $name
     *
     * @return self
     */
    public function setCreditCardName($name): self
    {
        $this->setPostData('cardholder_name', $name);

        return $this;
    }

    /**
     * Set credit card expiration date
     *
     * @param int $date
     *
     * @return self
     */
    public function setCreditCardExpiration($date): self
    {
        $this->setPostData('cc_expiry', $date);

        return $this;
    }

    /**
     * Set amount
     *
     * @param double $amount
     *
     * @return self
     */
    public function setAmount($amount): self
    {
        $this->setPostData('amount', $amount);

        return $this;
    }

    /**
     * Set trans armor token
     *
     * @param string $token
     *
     * @return self
     */
    public function setTransArmorToken($token): self
    {
        $this->setPostData('transarmor_token', $token);

        return $this;
    }

    /**
     * Set auth number
     *
     * @param string $number
     *
     * @return self
     */
    public function setAuthNumber($number): self
    {
        $this->setPostData('authorization_num', $number);

        return $this;
    }

    /**
     * Set credit card address
     * VerificationStr1 is comprised of the following constituent address values: Street, Zip/Postal Code, City, State/Provence, Country.
     * They are separted by the Pipe Character "|". Street Address|Zip/Postal|City|State/Prov|Country
     * Used for verification
     *
     * @param string $address
     *
     * @return self
     */
    public function setCreditCardAddress($address): self
    {
        $this->setPostData('cc_verification_str1', $address);

        return $this;
    }

    /**
     * Set credit card address
     * Used for verification, replaces the old cc_verification_str1 with the new address type
     *
     * @param array $address
     *
     * @return self
     */
    public function setCreditCardAddressNew($address): self
    {
        $this->setPostData('address', $address);

        return $this;
    }

    /**
     * Set credit card cvv code
     * This is the 0, 3, or 4-digit code on the back of the credit card sometimes called the CVV2 or CVD value.
     *
     * Used for verification
     *
     * @param int $cvv
     *
     * @return self
     */
    public function setCreditCardVerification($cvv): self
    {
        $this->setPostData('cc_verification_str2', $cvv);
        $this->setPostData('cvd_presence_ind', 1);

        return $this;
    }

    /**
     * Set credit card cavv code
     * This is the 0, 3, or 4-digit code on the back of the credit card sometimes called the CVV2 or CVD value.
     *
     * Used for verification
     *
     * @param int $cavv
     *
     * @return self
     */
    public function setCreditCardCAVV($cavv): self
    {
        $this->setPostData('cavv', $cavv);

        return $this;
    }

    /**
     * Set credit card zip code
     *
     * Used for verification
     *
     * @param int $zip
     *
     * @return self
     */
    public function setCreditCardZipCode($zip): self
    {
        $this->setPostData('zip_code', $zip);

        return $this;
    }

    /**
     * Set currency code
     *
     * @param string $code
     *
     * @return self
     */
    public function setCurrency($code): self
    {
        $this->setPostData('currency_code', $code);

        return $this;
    }

    /**
     * Set client IP
     *
     * @param string $ip
     *
     * @return self
     */
    public function setClientIp($ip): self
    {
        $this->setPostData('client_ip', $ip);

        return $this;
    }

    /**
     * Set client email
     *
     * @param string $email
     *
     * @return self
     */
    public function setClientEmail($email): self
    {
        $this->setPostData('client_email', $email);

        return $this;
    }

    /**
     * Set reference number
     *
     * @param int $number
     *
     * @return self
     */
    public function setReferenceNumber($number): self
    {
        $this->setPostData('reference_no', $number);

        return $this;
    }

    /**
     * Set transaction tag
     *
     * @param int $number
     *
     * @return self
     */
    public function setTransactionTag($number): self
    {
        $this->setPostData('transaction_tag', $number);

        return $this;
    }

    /**
     * Set customerNumber
     *
     * @param string $number
     *
     * @return self
     */
    public function setCustomerReferenceNumber($number): self
    {
        $this->setPostData('customer_ref', $number);

        return $this;
    }

    /**
     * Perform the API call
     * @return string
     * @throws \JsonException
     */
    public function process(): string
    {
        return $this->doRequest();
    }

    /**
     * Makes an HTTP request. This method can be overriden by subclasses if
     * developers want to do fancier things or use something other than curl to
     * make the request.
     *
     * @param CurlHandler optional initialized curl handle
     *
     * @return string the response text
     * @throws \JsonException
     */
    protected function doRequest($ch = null): string
    {
        if (!$ch) {
            $ch = curl_init();
        }
        $opts                     = self::$CURL_OPTS;
        $content                  = json_encode(array_merge($this->getPostData(), ['gateway_id' => $this->username, 'password' => $this->password, 'transaction_type' => $this->transactionType]), JSON_THROW_ON_ERROR);
        $opts[CURLOPT_POSTFIELDS] = $content;
        $opts[CURLOPT_URL]        = self::$testMode ? self::TEST_API_URL . $this->apiVersion : self::LIVE_API_URL . $this->apiVersion;
        if ($this->apiVersion >= 'v12') {
            $gge4_date                = gmdate('Y-m-d\TH:i:s') . 'Z'; // ISO8601
            $content_digest           = sha1($content);
            $hmac                     = base64_encode(hash_hmac('sha1',
                "POST\n" .
                "application/json; charset=UTF-8;\n" .
                $content_digest . "\n" .
                $gge4_date . "\n" .
                "/transaction/" . $this->apiVersion,
                $this->apiKey, true));
            $headers                  = [
                'X-GGe4-Content-SHA1: ' . $content_digest,
                'X-GGe4-Date: ' . $gge4_date,
                'Authorization: GGE4_API ' . $this->apiId . ':' . $hmac,
                'Content-Length: ' . strlen($content),
            ];
            $opts[CURLOPT_HTTPHEADER] = array_merge($opts[CURLOPT_HTTPHEADER], $headers);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // set options
        curl_setopt_array($ch, $opts);

        // execute
        $this->setResponse(curl_exec($ch));
        $this->setHeaders(curl_getinfo($ch));

        // fetch errors
        $this->setErrorCode(curl_errno($ch));
        $this->setErrorMessage(curl_error($ch));

        // Convert response to array
        $this->convertResponseToArray();

        // We need to make sure we do not have any errors
        if (!$this->getArrayResponse()) {
            // We have an error
            $returnedMessage = $this->getResponse();

            // Pull out the error code from the message
            preg_match('/\(\d+\)/', $returnedMessage, $matches);

            $errorCodes = $this->getBankResponseCodes();

            if (isset($matches[1])) {
                // If it's not 00 then there was an error
                $this->setErrorCode(isset($errorCodes[$matches[1]]) ? $matches[1] : 42);
                $this->setErrorMessage($errorCodes[$matches[1]] ?? $errorCodes[42]);
            } else {
                $headers = $this->getHeaders();
                $this->setErrorCode($headers['http_code']);
                $this->setErrorMessage($returnedMessage);
            }
        } elseif ($this->isError()) {
            $code  = $this->getBankResponseCode();
            $codes = $this->getBankResponseCodes();
            $error = isset($codes[$code]) ? $codes[$code]['name'] : null;

            $this->setErrorMessage($error);
            $this->setErrorCode(42);
        } else {
            // We have a json string, empty error message
            $this->setErrorMessage('');
            $this->setErrorCode(0);
        }

        // close
        curl_close($ch);

        // Reset
        $this->postFields = [];

        return $this->getResponse();
    }

    /**
     * Did we encounter an error?
     * @return bool
     */
    public function isError(): bool
    {
        $headers  = $this->getHeaders();
        $response = $this->getArrayResponse();
        // First make sure we got a valid response
        if (!in_array($headers['http_code'], [200, 201, 202], true)) {
            return true;
        }

        // Make sure the response does not have error in it
        if (!$response || empty($response)) {
            return true;
        }

        // Do we have an error code
        if ($this->getErrorCode() > 0) {
            return true;
        }

        // Bank response type
        if ($this->getBankResponseType() && $this->getBankResponseType() !== 'S') {
            return true;
        }

        // Exact response type
        if ($this->getExactResponseCode() > 0) {
            return true;
        }

        // No error
        return false;
    }

    /**
     * Was the last call successful
     * @return bool
     */
    public function isSuccess(): bool
    {
        return !$this->isError();
    }

    /**
     * Check if transaction was approved
     * @return null|int
     */
    public function isApproved(): ?int
    {
        return $this->getValueByKey($this->getArrayResponse(), 'transaction_approved');
    }

    /**
     * Get Transaction Tag
     * @return null|int
     */
    public function getTransactionTag(): ?int
    {
        return $this->getValueByKey($this->getArrayResponse(), 'transaction_tag');
    }

    /**
     * Get transaction record/receipt
     * @return null|string
     */
    public function getTransactionRecord(): ?string
    {
        return $this->getValueByKey($this->getArrayResponse(), 'ctr');
    }

    /**
     * Get transaction auth number
     * @return null|string
     */
    public function getAuthNumber(): ?string
    {
        return $this->getValueByKey($this->getArrayResponse(), 'authorization_num');
    }

    /**
     * Get transaction transarmor token
     * @return null|string
     */
    public function getTransArmorToken(): ?string
    {
        return $this->getValueByKey($this->getArrayResponse(), 'transarmor_token');
    }

    /**
     * Get transaction bank response code
     * @return null|int
     */
    public function getBankResponseCode(): ?int
    {
        return $this->getValueByKey($this->getArrayResponse(), 'bank_resp_code');
    }

    /**
     * Get transaction bank response message
     * @return null|string
     */
    public function getBankResponseMessage(): ?string
    {
        return $this->getValueByKey($this->getArrayResponse(), 'bank_message');
    }

    /**
     * Get transaction Exact response code
     * @return null|int
     */
    public function getExactResponseCode(): ?int
    {
        return $this->getValueByKey($this->getArrayResponse(), 'exact_resp_code');
    }

    /**
     * Get transaction Exact response message
     * @return null|string
     */
    public function getExactResponseMessage(): ?string
    {
        return $this->getValueByKey($this->getArrayResponse(), 'exact_message');
    }

    /**
     * Get the Address Verification System Response.
     * @return null|string
     */
    public function getAvs(): ?string
    {
        return $this->getValueByKey($this->getArrayResponse(), 'avs');
    }

    /**
     * Get transaction bank response comment
     * @return null|string
     */
    public function getBankResponseComments(): ?string
    {
        $code  = $this->getBankResponseCode();
        $codes = $this->getBankResponseCodes();

        return isset($codes[$code]) ? $codes[$code]['comments'] : null;
    }

    /**
     * Get transaction bank response type
     *  S = Successful Response Codes
     *  R = Reject Response Codes
     *  D = Decline Response Codes
     *
     * @return string
     */
    public function getBankResponseType(): ?string
    {
        $code  = $this->getBankResponseCode();
        $codes = $this->getBankResponseCodes();

        return isset($codes[$code]) ? $codes[$code]['response'] : null;
    }

    /**
     * Set the array response value
     *
     * @param array $value
     *
     * @return void
     */
    public function setArrayResponse($value): void
    {
        $this->arrayResponse = $value;
    }

    /**
     * Return the array representation of the last response
     * @return array
     */
    public function getArrayResponse(): array
    {
        return $this->arrayResponse;
    }

    /**
     * Return the response represented as string
     * @return array
     * @throws \JsonException
     */
    protected function convertResponseToArray(): array
    {
        if ($this->getResponse()) {
            $this->setArrayResponse(json_decode($this->getResponse(), true, 512, JSON_THROW_ON_ERROR));
        }

        return $this->getArrayResponse();
    }

    /**
     * Set the response
     *
     * @param string $response the response returned from the call
     *
     * @return self
     */
    protected function setResponse($response = ''): self
    {
        $this->response = $response;

        return $this;
    }

    /**
     * Get the response data
     *
     * @return mixed the response data
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Set the headers
     *
     * @param array the headers array
     *
     * @return self
     */
    protected function setHeaders($headers = []): self
    {
        $this->headers = $headers;

        return $this;
    }

    /**
     * Get the headers
     *
     * @return array the headers returned from the call
     */
    protected function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Set the error code number
     *
     * @param int the error code number
     *
     * @return self
     */
    public function setErrorCode($code = 0): self
    {
        $this->errorCode = $code;

        return $this;
    }

    /**
     * Get the error code number
     *
     * @return int error code number
     */
    public function getErrorCode(): int
    {
        return $this->errorCode;
    }

    /**
     * Set the error message
     *
     * @param string the error message
     *
     * @return self
     */
    public function setErrorMessage($message = ''): self
    {
        $this->errorMessage = $message;

        return $this;
    }

    /**
     * Get the error code message
     *
     * @return string error code message
     */
    public function getErrorMessage(): string
    {
        return $this->errorMessage;
    }

    /**
     * Find a key inside a multi dim. array
     *
     * @param array $data
     * @param string $key
     *
     * @return mixed
     */
    protected function getValueByKey($data, $key)
    {
        if (is_countable($data) && count($data) >= 1) {
            foreach ($data as $k => $each) {
                if ($k === $key) {
                    return $each;
                }

                if (is_array($each) && $return = $this->getValueByKey($each, $key)) {
                    return $return;
                }
            }
        }

        return null;
    }

    /**
     * Return API response codes
     * @return array
     */
    protected function getBankResponseCodes(): array
    {
        return [
            0   => [
                'response' => 'D',
                'code'     => '000',
                'name'     => 'No Answer',
                'action'   => 'Resend',
                'comments' => 'First Data received no answer from auth network',
            ],
            100 => [
                'response' => 'S',
                'code'     => '100',
                'name'     => 'Approved',
                'action'   => 'N/A',
                'comments' => 'Successfully approved',
            ],
            101 => [
                'response' => 'S',
                'code'     => '101',
                'name'     => 'Validated',
                'action'   => 'N/A',
                'comments' => 'Account Passed edit checks',
            ],
            102 => [
                'response' => 'S',
                'code'     => '102',
                'name'     => 'Verified',
                'action'   => 'N/A',
                'comments' => 'Account Passed external negative file',
            ],
            103 => [
                'response' => 'S',
                'code'     => '103',
                'name'     => 'Pre-Noted',
                'action'   => 'N/A',
                'comments' => 'Passed Pre-Note',
            ],
            104 => [
                'response' => 'S',
                'code'     => '104',
                'name'     => 'No Reason to Decline',
                'action'   => 'N/A',
                'comments' => 'Successfully approved',
            ],
            105 => [
                'response' => 'S',
                'code'     => '105',
                'name'     => 'Received and Stored',
                'action'   => 'N/A',
                'comments' => 'Successfully approved',
            ],
            106 => [
                'response' => 'S',
                'code'     => '106',
                'name'     => 'Provided Auth',
                'action'   => 'N/A',
                'comments' => 'Successfully approved Note: Indicates customized code was used in processing',
            ],
            107 => [
                'response' => 'S',
                'code'     => '107',
                'name'     => 'Request Received',
                'action'   => 'N/A',
                'comments' => 'Successfully approved Note: Indicates customized code was used in processing',
            ],
            108 => [
                'response' => 'S',
                'code'     => '108',
                'name'     => 'Approved for Activation',
                'action'   => 'N/A',
                'comments' => 'Successfully Activated',
            ],
            109 => [
                'response' => 'S',
                'code'     => '109',
                'name'     => 'Previously&nbsp;Processed Transaction',
                'action'   => 'N/A',
                'comments' => 'Transaction was not re-authorized with the Debit Network because it was previously processed',
            ],
            110 => [
                'response' => 'S',
                'code'     => '110',
                'name'     => 'BIN Alert',
                'action'   => 'N/A',
                'comments' => 'Successfully approved Note: Indicates customized code was used in processing',
            ],
            111 => [
                'response' => 'S',
                'code'     => '111',
                'name'     => 'Approved for Partial',
                'action'   => 'N/A',
                'comments' => 'Successfully approved Note: Indicates customized code was used in processing',
            ],
            164 => [
                'response' => 'S',
                'code'     => '164',
                'name'     => 'Conditional Approval',
                'action'   => 'Wait',
                'comments' => 'Conditional Approval - Hold shipping for 24 hours',
            ],
            201 => [
                'response' => 'R',
                'code'     => '201',
                'name'     => 'Invalid CC Number',
                'action'   => 'Cust',
                'comments' => 'Bad check digit, length, or other credit card problem',
            ],
            202 => [
                'response' => 'R',
                'code'     => '202',
                'name'     => 'Bad Amount Nonnumeric Amount',
                'action'   => 'If',
                'comments' => 'Amount sent was zero, unreadable, over ceiling limit, or exceeds maximum allowable amount.',
            ],
            203 => [
                'response' => 'R',
                'code'     => '203',
                'name'     => 'Zero Amount',
                'action'   => 'Fix',
                'comments' => 'Amount sent was zero',
            ],
            204 => [
                'response' => 'R',
                'code'     => '204',
                'name'     => 'Other Error',
                'action'   => 'Fix',
                'comments' => 'Unidentifiable error',
            ],
            205 => [
                'response' => 'R',
                'code'     => '205',
                'name'     => 'Bad Total Auth Amount',
                'action'   => 'Fix',
                'comments' => 'The sum of the authorization amount from extended data information does not equal detail record authorization Amount. Amount sent was zero, unreadable, over ceiling limit, or exceeds Maximum allowable amount.',
            ],
            218 => [
                'response' => 'R',
                'code'     => '218',
                'name'     => 'Invalid SKU Number',
                'action'   => 'Fix',
                'comments' => 'Non‐numeric value was sent',
            ],
            219 => [
                'response' => 'R',
                'code'     => '219',
                'name'     => 'Invalid Credit Plan',
                'action'   => 'Fix',
                'comments' => 'Non‐numeric value was sent',
            ],
            220 => [
                'response' => 'R',
                'code'     => '220',
                'name'     => 'Invalid Store Number',
                'action'   => 'Fix',
                'comments' => 'Non‐numeric value was sent',
            ],
            225 => [
                'response' => 'R',
                'code'     => '225',
                'name'     => 'Invalid Field Data',
                'action'   => 'Fix',
                'comments' => 'Data within transaction is incorrect',
            ],
            227 => [
                'response' => 'R',
                'code'     => '227',
                'name'     => 'Missing Companion Data',
                'action'   => 'Fix',
                'comments' => 'Specific and relevant data within transaction is absent',
            ],
            229 => [
                'response' => 'R',
                'code'     => '229',
                'name'     => 'Percents do not total 100',
                'action'   => 'Fix',
                'comments' => 'FPO monthly payments do not total 100 Note: FPO only',
            ],
            230 => [
                'response' => 'R',
                'code'     => '230',
                'name'     => 'Payments do not total 100',
                'action'   => 'Fix',
                'comments' => 'FPO monthly payments do not total 100 Note: FPO only',
            ],
            231 => [
                'response' => 'R',
                'code'     => '231',
                'name'     => 'Invalid Division Number',
                'action'   => 'Fix',
                'comments' => 'Division number incorrect',
            ],
            233 => [
                'response' => 'R',
                'code'     => '233',
                'name'     => 'Does not match MOP',
                'action'   => 'Fix',
                'comments' => 'Credit card number does not match method of payment type or invalid BIN',
            ],
            234 => [
                'response' => 'R',
                'code'     => '234',
                'name'     => 'Duplicate Order Number',
                'action'   => 'Fix',
                'comments' => 'Unique to authorization recycle transactions. Order number already exists in system Note: Auth Recycle only',
            ],
            235 => [
                'response' => 'R',
                'code'     => '235',
                'name'     => 'FPO Locked',
                'action'   => 'Resend',
                'comments' => 'FPO change not allowed Note: FPO only',
            ],
            236 => [
                'response' => 'R',
                'code'     => '236',
                'name'     => 'Auth Recycle Host System Down',
                'action'   => 'Resend',
                'comments' => 'Authorization recycle host system temporarily unavailable Note: Auth Recycle only',
            ],
            237 => [
                'response' => 'R',
                'code'     => '237',
                'name'     => 'FPO Not Approved',
                'action'   => 'Call',
                'comments' => 'Division does not participate in FPO. Contact your First Data Representative for information on getting set up for FPO Note: FPO only',
            ],
            238 => [
                'response' => 'R',
                'code'     => '238',
                'name'     => 'Invalid Currency',
                'action'   => 'Fix',
                'comments' => 'Currency does not match First Data merchant setup for division',
            ],
            239 => [
                'response' => 'R',
                'code'     => '239',
                'name'     => 'Invalid MOP for Division',
                'action'   => 'Fix',
                'comments' => 'Method of payment is invalid for the division',
            ],
            240 => [
                'response' => 'R',
                'code'     => '240',
                'name'     => 'Auth Amount for Division',
                'action'   => 'Fix',
                'comments' => 'Used by FPO',
            ],
            241 => [
                'response' => 'R',
                'code'     => '241',
                'name'     => 'Illegal Action',
                'action'   => 'Fix',
                'comments' => 'Invalid action attempted',
            ],
            243 => [
                'response' => 'R',
                'code'     => '243',
                'name'     => 'Invalid Purchase Level 3',
                'action'   => 'Fix',
                'comments' => 'Data is inaccurate or missing, or the BIN is ineligible for P‐card',
            ],
            244 => [
                'response' => 'R',
                'code'     => '244',
                'name'     => 'Invalid Encryption Format',
                'action'   => 'Fix',
                'comments' => 'Invalid encryption flag. Data is Inaccurate.',
            ],
            245 => [
                'response' => 'R',
                'code'     => '245',
                'name'     => 'Missing or Invalid Secure Payment Data',
                'action'   => 'Fix',
                'comments' => 'Visa or MasterCard authentication data not in appropriate Base 64 encoding format or data provided on A non‐e‐Commerce transaction.',
            ],
            246 => [
                'response' => 'R',
                'code'     => '246',
                'name'     => 'Merchant not MasterCard Secure code Enabled',
                'action'   => 'Call',
                'comments' => 'Division does not participate in MasterCard Secure Code. Contact your First Data Representative for information on getting setup for MasterCard SecureCode.',
            ],
            247 => [
                'response' => 'R',
                'code'     => '247',
                'name'     => 'Check conversion Data Error',
                'action'   => 'Fix',
                'comments' => 'Proper data elements were not sent',
            ],
            248 => [
                'response' => 'R',
                'code'     => '248',
                'name'     => 'Blanks not passed in reserved field',
                'action'   => 'Fix',
                'comments' => 'Blanks not passed in Reserved Field',
            ],
            249 => [
                'response' => 'R',
                'code'     => '249',
                'name'     => 'Invalid (MCC)',
                'action'   => 'Fix',
                'comments' => 'Invalid Merchant Category (MCC) sent',
            ],
            251 => [
                'response' => 'R',
                'code'     => '251',
                'name'     => 'Invalid Start Date',
                'action'   => 'Fix',
                'comments' => 'Incorrect start date or card may require an issue number, but a start date was submitted.',
            ],
            252 => [
                'response' => 'R',
                'code'     => '252',
                'name'     => 'Invalid Issue Number',
                'action'   => 'Fix',
                'comments' => 'Issue number invalid for this BIN.',
            ],
            253 => [
                'response' => 'R',
                'code'     => '253',
                'name'     => 'Invalid Tran. Type',
                'action'   => 'Fix',
                'comments' => 'If an “R” (Retail Indicator) is sent for a transaction with a MOTO Merchant Category Code (MCC)',
            ],
            257 => [
                'response' => 'R',
                'code'     => '257',
                'name'     => 'Missing Cust Service Phone',
                'action'   => 'Fix',
                'comments' => 'Card was authorized, but AVS did not match. The 100 was overwritten with a 260 per the merchant’s request Note: Conditional deposits only',
            ],
            258 => [
                'response' => 'R',
                'code'     => '258',
                'name'     => 'Not Authorized to Send Record',
                'action'   => 'Call',
                'comments' => 'Division does not participate in Soft Merchant Descriptor. Contact your First Data Representative for information on getting set up for Soft Merchant Descriptor.',
            ],
            260 => [
                'response' => 'D',
                'code'     => '260',
                'name'     => 'Soft AVS',
                'action'   => 'Cust.',
                'comments' => 'Authorization network could not reach the bank which issued the card',
            ],
            261 => [
                'response' => 'R',
                'code'     => '261',
                'name'     => 'Account Not Eligible For Division’s Setup',
                'action'   => 'N/A',
                'comments' => 'Account number not eligible for division’s Account Updater program setup',
            ],
            262 => [
                'response' => 'R',
                'code'     => '262',
                'name'     => 'Authorization Code Response Date Invalid',
                'action'   => 'Fix',
                'comments' => 'Authorization code and/or response date are invalid. Note: MOP = MC, MD, VI only',
            ],
            263 => [
                'response' => 'R',
                'code'     => '263',
                'name'     => 'Partial Authorization Not Allowed or Partial Authorization Request Note Valid',
                'action'   => 'Fix',
                'comments' => 'Action code or division does not allow partial authorizations or partial authorization request is not valid.',
            ],
            264 => [
                'response' => 'R',
                'code'     => '264',
                'name'     => 'Duplicate Deposit Transaction',
                'action'   => 'N/A',
                'comments' => 'Transaction is a duplicate of a previously deposited transaction. Transaction will not be processed.',
            ],
            265 => [
                'response' => 'R',
                'code'     => '265',
                'name'     => 'Missing QHP Amount',
                'action'   => 'Fix',
                'comments' => 'Missing QHP Amount',
            ],
            266 => [
                'response' => 'R',
                'code'     => '266',
                'name'     => 'Invalid QHP Amount',
                'action'   => 'Fix',
                'comments' => 'QHP amount greater than transaction amount',
            ],
            274 => [
                'response' => 'R',
                'code'     => '274',
                'name'     => 'Transaction Not Supported',
                'action'   => 'N/A',
                'comments' => 'The requested transaction type is blocked from being used with this card. Note:&nbsp; This may be the result of either an association rule, or a merchant boarding option.',
            ],
            301 => [
                'response' => 'D',
                'code'     => '301',
                'name'     => 'Issuer unavailable',
                'action'   => 'Resend',
                'comments' => 'Authorization network could not reach the bank which issued the card',
            ],
            302 => [
                'response' => 'D',
                'code'     => '302',
                'name'     => 'Credit Floor',
                'action'   => 'Wait',
                'comments' => 'Insufficient funds',
            ],
            303 => [
                'response' => 'D',
                'code'     => '303',
                'name'     => 'Processor Decline',
                'action'   => 'Cust.',
                'comments' => 'Generic decline – No other information is being provided by the Issuer',
            ],
            304 => [
                'response' => 'D',
                'code'     => '304',
                'name'     => 'Not On File',
                'action'   => 'Cust.',
                'comments' => 'No card record, or invalid/nonexistent to account specified',
            ],
            305 => [
                'response' => 'D',
                'code'     => '305',
                'name'     => 'Already Reversed',
                'action'   => 'N/A',
                'comments' => 'Transaction previously reversed. Note: MOP = any Debit MOP, SV, MC, MD, VI only',
            ],
            306 => [
                'response' => 'D',
                'code'     => '306',
                'name'     => 'Amount Mismatch',
                'action'   => 'Fix',
                'comments' => 'Requested reversal amount does not match original approved authorization amount. Note: MOP = MC, MD, VI only',
            ],
            307 => [
                'response' => 'D',
                'code'     => '307',
                'name'     => 'Authorization Not Found',
                'action'   => 'Fix',
                'comments' => 'Transaction cannot be matched to an authorization that was stored in the database. Note: MOP = MC, MD, VI only',
            ],
            351 => [
                'response' => 'R',
                'code'     => '351',
                'name'     => 'TransArmor Service Unavailable',
                'action'   => 'Resend',
                'comments' => 'TransArmor Service temporarily unavailable.',
            ],
            352 => [
                'response' => 'D',
                'code'     => '352',
                'name'     => 'Expired Lock',
                'action'   => 'Cust.',
                'comments' => 'ValueLink - Lock on funds has expired.',
            ],
            353 => [
                'response' => 'R',
                'code'     => '353',
                'name'     => 'TransArmor Invalid Token or PAN',
                'action'   => 'Fix',
                'comments' => 'TransArmor Service encountered a problem converting the given Token or PAN with the given Token Type.',
            ],
            354 => [
                'response' => 'R',
                'code'     => '354',
                'name'     => 'TransArmor Invalid Result',
                'action'   => 'Cust',
                'comments' => 'TransArmor Service encountered a problem with the resulting Token/PAN.',
            ],
            401 => [
                'response' => 'D',
                'code'     => '401',
                'name'     => 'Call',
                'action'   => 'Voice',
                'comments' => 'Issuer wants voice contact with cardholder',
            ],
            402 => [
                'response' => 'D',
                'code'     => '402',
                'name'     => 'Default Call',
                'action'   => 'Voice',
                'comments' => 'Decline',
            ],
            501 => [
                'response' => 'D',
                'code'     => '501',
                'name'     => 'Pickup',
                'action'   => 'Cust',
                'comments' => 'Card Issuer wants card returned',
            ],
            502 => [
                'response' => 'D',
                'code'     => '502',
                'name'     => 'Lost/Stolen',
                'action'   => 'Cust',
                'comments' => 'Card reported as lost/stolen Note: Does not apply to American Express',
            ],
            503 => [
                'response' => 'D',
                'code'     => '503',
                'name'     => 'Fraud/ Security Violation',
                'action'   => 'Cust',
                'comments' => 'CID did not match Note: Discover only',
            ],
            505 => [
                'response' => 'D',
                'code'     => '505',
                'name'     => 'Negative File',
                'action'   => 'Cust',
                'comments' => 'On negative file',
            ],
            508 => [
                'response' => 'D',
                'code'     => '508',
                'name'     => 'Excessive PIN try',
                'action'   => 'Cust',
                'comments' => 'Allowable number of PIN tries exceeded',
            ],
            509 => [
                'response' => 'D',
                'code'     => '509',
                'name'     => 'Over the limit',
                'action'   => 'Cust',
                'comments' => 'Exceeds withdrawal or activity amount limit',
            ],
            510 => [
                'response' => 'D',
                'code'     => '510',
                'name'     => 'Over Limit Frequency',
                'action'   => 'Cust',
                'comments' => 'Exceeds withdrawal or activity count limit',
            ],
            519 => [
                'response' => 'D',
                'code'     => '519',
                'name'     => 'On negative file',
                'action'   => 'Cust',
                'comments' => 'Account number appears on negative file',
            ],
            521 => [
                'response' => 'D',
                'code'     => '521',
                'name'     => 'Insufficient funds',
                'action'   => 'Cust',
                'comments' => 'Insufficient funds/over credit limit',
            ],
            522 => [
                'response' => 'D',
                'code'     => '522',
                'name'     => 'Card is expired',
                'action'   => 'Cust',
                'comments' => 'Card has expired',
            ],
            524 => [
                'response' => 'D',
                'code'     => '524',
                'name'     => 'Altered Data',
                'action'   => 'Fix',
                'comments' => 'Altered Data\\Magnetic stripe incorrect',
            ],
            530 => [
                'response' => 'D',
                'code'     => '530',
                'name'     => 'Do Not Honor',
                'action'   => 'Cust',
                'comments' => 'Generic Decline – No other information is being provided by the issuer. Note: This is a hard decline for BML (will never pass with recycle attempts)',
            ],
            531 => [
                'response' => 'D',
                'code'     => '531',
                'name'     => 'CVV2/VAK Failure',
                'action'   => 'Cust',
                'comments' => 'Issuer has declined auth request because CVV2 or VAK failed',
            ],
            534 => [
                'response' => 'D',
                'code'     => '534',
                'name'     => 'Do Not Honor - High Fraud',
                'action'   => 'Cust',
                'comments' => 'The transaction has failed PayPal or Google Checkout risk models',
            ],
            570 => [
                'response' => 'D ',
                'code'     => '570 ',
                'name'     => 'Stop payment order one time recurring/ installment',
                'action'   => 'Fix',
                'comments' => 'Cardholder has requested this one recurring/installment payment be stopped.',
            ],
            571 => [
                'response' => 'D',
                'code'     => '571',
                'name'     => 'Revocation of Authorization for All Recurring / Installments',
                'action'   => 'Cust',
                'comments' => 'Cardholder has requested all recurring/installment payments be stopped',
            ],
            572 => [
                'response' => 'D',
                'code'     => '572',
                'name'     => 'Revocation of All Authorizations – Closed Account',
                'action'   => 'Cust',
                'comments' => 'Cardholder has requested that all authorizations be stopped for this account due to closed account. Note: Visa only',
            ],
            580 => [
                'response' => 'D',
                'code'     => '580',
                'name'     => 'Account previously activated',
                'action'   => 'Cust',
                'comments' => 'Account previously activated',
            ],
            581 => [
                'response' => 'D',
                'code'     => '581',
                'name'     => 'Unable to void',
                'action'   => 'Fix',
                'comments' => 'Unable to void',
            ],
            582 => [
                'response' => 'D',
                'code'     => '582',
                'name'     => 'Block activation failed',
                'action'   => 'Fix',
                'comments' => 'Reserved for Future Use',
            ],
            583 => [
                'response' => 'D',
                'code'     => '583',
                'name'     => 'Block Activation Failed',
                'action'   => 'Fix',
                'comments' => 'Reserved for Future Use',
            ],
            584 => [
                'response' => 'D',
                'code'     => '584',
                'name'     => 'Issuance Does Not Meet Minimum Amount',
                'action'   => 'Fix',
                'comments' => 'Issuance does not meet minimum amount',
            ],
            585 => [
                'response' => 'D',
                'code'     => '585',
                'name'     => 'No Original Authorization Found',
                'action'   => 'N/A',
                'comments' => 'No original authorization found',
            ],
            586 => [
                'response' => 'D',
                'code'     => '586',
                'name'     => 'Outstanding Authorization, Funds on Hold',
                'action'   => 'N/A',
                'comments' => 'Outstanding Authorization, funds on hold',
            ],
            587 => [
                'response' => 'D',
                'code'     => '587',
                'name'     => 'Activation Amount Incorrect',
                'action'   => 'Fix',
                'comments' => 'Activation amount incorrect',
            ],
            588 => [
                'response' => 'D',
                'code'     => '588',
                'name'     => 'Block Activation Failed',
                'action'   => 'Fix',
                'comments' => 'Reserved for Future Use',
            ],
            589 => [
                'response' => 'D',
                'code'     => '589',
                'name'     => 'CVD Value Failure',
                'action'   => 'Cust',
                'comments' => 'Magnetic stripe CVD value failure',
            ],
            590 => [
                'response' => 'D',
                'code'     => '590',
                'name'     => 'Maximum Redemption Limit Met',
                'action'   => 'Cust',
                'comments' => 'Maximum redemption limit met',
            ],
            591 => [
                'response' => 'D',
                'code'     => '591',
                'name'     => 'Invalid CC Number',
                'action'   => 'Cust',
                'comments' => 'Bad check digit, length or other credit card problem. Issuer generated',
            ],
            592 => [
                'response' => 'D',
                'code'     => '592',
                'name'     => 'Bad Amount',
                'action'   => 'Fix',
                'comments' => 'Amount sent was zero or unreadable. Issuer generated',
            ],
            594 => [
                'response' => 'D',
                'code'     => '594',
                'name'     => 'Other Error',
                'action'   => 'Fix',
                'comments' => 'Unidentifiable error. Issuer generated',
            ],
            595 => [
                'response' => 'D',
                'code'     => '595',
                'name'     => 'New Card Issued',
                'action'   => 'Cust',
                'comments' => 'New Card Issued',
            ],
            596 => [
                'response' => 'D',
                'code'     => '596',
                'name'     => 'Suspected Fraud',
                'action'   => 'Cust',
                'comments' => 'Issuer has flagged account as suspected fraud',
            ],
            599 => [
                'response' => 'D',
                'code'     => '599',
                'name'     => 'Refund Not Allowed',
                'action'   => 'N/A',
                'comments' => 'Refund Not Allowed',
            ],
            602 => [
                'response' => 'D',
                'code'     => '602',
                'name'     => 'Invalid Institution Code',
                'action'   => 'Fix',
                'comments' => 'Card is bad, but passes MOD 10 check digit routine, wrong BIN',
            ],
            603 => [
                'response' => 'D',
                'code'     => '603',
                'name'     => 'Invalid Institution',
                'action'   => 'Cust',
                'comments' => 'Institution not valid (i.e. possible merger)',
            ],
            605 => [
                'response' => 'D',
                'code'     => '605',
                'name'     => 'Invalid Expiration Date',
                'action'   => 'Cust',
                'comments' => 'Card has expired or bad date sent. Confirm proper date',
            ],
            606 => [
                'response' => 'D',
                'code'     => '606',
                'name'     => 'Invalid Transaction Type',
                'action'   => 'Cust',
                'comments' => 'Issuer does not allow this type of transaction',
            ],
            607 => [
                'response' => 'D',
                'code'     => '607',
                'name'     => 'Invalid Amount',
                'action'   => 'Fix',
                'comments' => 'Amount not accepted by network',
            ],
            610 => [
                'response' => 'D',
                'code'     => '610',
                'name'     => 'BIN Block',
                'action'   => 'Cust',
                'comments' => 'Merchant has requested First Data not process credit cards with this BIN',
            ],
            704 => [
                'response' => 'S',
                'code'     => '704',
                'name'     => 'FPO Accepted',
                'action'   => 'N/A',
                'comments' => 'Stored in FPO database',
            ],
            740 => [
                'response' => 'R',
                'code'     => '740',
                'name'     => 'Match Failed',
                'action'   => 'Fix',
                'comments' => 'Unable to validate the debit. Authorization Record - based on amount, action code, and MOP (Batch response reason code for Debit Only)',
            ],
            741 => [
                'response' => 'R/D',
                'code'     => '741',
                'name'     => 'Validation Failed',
                'action'   => 'Fix',
                'comments' => 'Unable to validate the Debit Authorization Record - based on amount, action code, and MOP (Batch response reason code for Debit Only)',
            ],
            750 => [
                'response' => 'R/D',
                'code'     => '750',
                'name'     => 'Invalid Transit Routing Number',
                'action'   => 'Fix',
                'comments' => 'EC - ABA transit routing number is invalid, failed check digit',
            ],
            751 => [
                'response' => 'R/D',
                'code'     => '751',
                'name'     => 'Transit Routing Number Unknown',
                'action'   => 'Fix',
                'comments' => 'Transit routing number not on list of current acceptable numbers.',
            ],
            752 => [
                'response' => 'R',
                'code'     => '752',
                'name'     => 'Missing Name',
                'action'   => 'Fix',
                'comments' => 'Pertains to deposit transactions only',
            ],
            753 => [
                'response' => 'R',
                'code'     => '753',
                'name'     => 'Invalid Account Type',
                'action'   => 'Fix',
                'comments' => 'Pertains to deposit transactions only',
            ],
            754 => [
                'response' => 'R/D',
                'code'     => '754',
                'name'     => 'Account Closed',
                'action'   => 'Cust',
                'comments' => 'Bank account has been closed For PayPal and GoogleCheckout – the customer’s account was closed / restricted',
            ],
            802 => [
                'response' => 'D',
                'code'     => '802',
                'name'     => 'Positive ID',
                'action'   => 'Voice',
                'comments' => 'Issuer requires further information',
            ],
            806 => [
                'response' => 'D',
                'code'     => '806',
                'name'     => 'Restraint',
                'action'   => 'Cust',
                'comments' => 'Card has been restricted',
            ],
            811 => [
                'response' => 'D',
                'code'     => '811',
                'name'     => 'Invalid Security Code',
                'action'   => 'Fix',
                'comments' => 'American Express CID is incorrect',
            ],
            813 => [
                'response' => 'D',
                'code'     => '813',
                'name'     => 'Invalid PIN',
                'action'   => 'Cust',
                'comments' => 'PIN for online debit transactions is incorrect',
            ],
            825 => [
                'response' => 'D',
                'code'     => '825',
                'name'     => 'No Account',
                'action'   => 'Cust',
                'comments' => 'Account does not exist',
            ],
            833 => [
                'response' => 'D',
                'code'     => '833',
                'name'     => 'Invalid Merchant',
                'action'   => 'Fix',
                'comments' => 'Service Established (SE) number is incorrect, closed or Issuer does not allow this type of transaction',
            ],
            834 => [
                'response' => 'R',
                'code'     => '834',
                'name'     => 'Unauthorized User',
                'action'   => 'Fix',
                'comments' => 'Method of payment is invalid for the division',
            ],
            902 => [
                'response' => 'D',
                'code'     => '902',
                'name'     => 'Process Unavailable',
                'action'   => 'Resend/ Call/ Cust.',
                'comments' => 'System error/malfunction with Issuer For Debit – The link is down or setup issue; contact your First Data Representative.',
            ],
            903 => [
                'response' => 'D',
                'code'     => '903',
                'name'     => 'Invalid Expiration',
                'action'   => 'Cust',
                'comments' => 'Invalid or expired expiration date',
            ],
            904 => [
                'response' => 'D',
                'code'     => '904',
                'name'     => 'Invalid Effective',
                'action'   => 'Cust./ Resend',
                'comments' => 'Card not active',
            ],
        ];
    }
}
