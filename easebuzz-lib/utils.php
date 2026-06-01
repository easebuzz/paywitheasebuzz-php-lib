<?php

/**
 * Load .env file into environment variables.
 */
function _loadEnvFile($filePath)
{
    if (!is_file($filePath) || !is_readable($filePath)) {
        return;
    }

    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        return;
    }

    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line) || $line[0] === '#') {
            continue;
        }
        $pos = strpos($line, '=');
        if ($pos === false) {
            continue;
        }
        $key = trim(substr($line, 0, $pos));
        $value = trim(substr($line, $pos + 1));

        if (strlen($value) >= 2) {
            $first = $value[0];
            $last = $value[strlen($value) - 1];
            if (($first === '"' && $last === '"') || ($first === "'" && $last === "'")) {
                $value = substr($value, 1, -1);
            }
        }

        if (getenv($key) === false) {
            putenv($key . '=' . $value);
        }
    }
}

/**
 * Load merchant credentials from environment.
 */
function _getConfig()
{
    $merchant_key = getenv('EASEBUZZ_MERCHANT_KEY');
    $salt = getenv('EASEBUZZ_SALT');
    $env = getenv('ENV');

    if (empty($merchant_key) || empty($salt)) {
        return array('status' => 0, 'data' => '[utils.php] EASEBUZZ_MERCHANT_KEY and EASEBUZZ_SALT environment variables are required. Please set them in your .env file.');
    }

    if (empty($env) || !in_array($env, array('test', 'prod'))) {
        $env = 'test';
    }

    return array('merchant_key' => trim($merchant_key), 'salt' => trim($salt), 'env' => trim($env));
}

/**
 * Get API base URL based on environment.
 *
 * @param string $env - 'test' or 'prod'
 * @param string $api_name - 'initiate_api' for pay domain, 'dashboard' for dashboard domain
 * @return string Base URL
 */
function fetchBaseUrl($env, $api_name = 'dashboard')
{
    if ($api_name === 'initiate_api') {
        return ($env === 'prod') ? 'https://pay.easebuzz.in/' : 'https://testpay.easebuzz.in/';
    }
    return ($env === 'prod') ? 'https://dashboard.easebuzz.in/' : 'https://testdashboard.easebuzz.in/';
}

/**
 * cURL POST request (form-encoded by default, JSON if specified).
 */
function _curlCall($url, $params_array, $content_type = 'application/x-www-form-urlencoded')
{
    // Ensure only HTTPS URLs are called
    if (strpos($url, 'https://') !== 0) {
        return (object) array('status' => 0, 'data' => 'Only HTTPS URLs are allowed');
    }

    $ch = curl_init();

    curl_setopt_array($ch, array(
        CURLOPT_URL            => $url,
        CURLOPT_POSTFIELDS     => $params_array,
        CURLOPT_POST           => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTPHEADER     => array('Content-Type: ' . $content_type),
    ));

    $result = curl_exec($ch);

    if (curl_errno($ch)) {
        $error = curl_error($ch);
        curl_close($ch);
        return (object) array('status' => 0, 'data' => empty($error) ? 'Server Error' : $error);
    }

    curl_close($ch);

    if ($result === false || $result === '') {
        return (object) array('status' => 0, 'data' => 'Empty response from server');
    }

    $trimmed = trim($result);
    if ($trimmed === '') {
        return (object) array('status' => 0, 'data' => 'Empty response from server');
    }

    $decoded = json_decode($trimmed);

    if ($decoded === null && json_last_error() !== JSON_ERROR_NONE) {
        return $result; // Raw HTML (bank redirects)
    }

    return $decoded;
}

/**
 * cURL POST with JSON body.
 */
function _curlCallJson($url, $params)
{
    return _curlCall($url, json_encode($params), 'application/json');
}

/**
 * Generate SHA-512 hash for API request signing.
 *
 * How it works:
 *   - Takes the hash sequence (pipe-separated field names)
 *   - For each field: appends the value (or empty string if not set) followed by "|"
 *   - Appends salt at the end (no trailing pipe before salt)
 *   - Returns lowercase SHA-512 hex hash
 *
 * Example for initiate_payment:
 *   hashString = "keyValue|txnidValue|amountValue|...|udf10Value|saltValue"
 *
 * NOTE: Even if a field (like udf1) is empty, the pipe separator is still included.
 *       This is required for hash to match on Easebuzz side.
 */
function generateHashValue($posted, $salt_key, $api_name)
{
    $sequences = array(
        'initiate_payment'    => 'key|txnid|amount|productinfo|firstname|email|udf1|udf2|udf3|udf4|udf5|udf6|udf7|udf8|udf9|udf10',
        'transaction'      => 'key|txnid',
        'transaction_date' => 'key|merchant_email|start_date|end_date',
        'refund'           => 'key|merchant_refund_id|easebuzz_id|refund_amount',
        'refund_status'       => 'key|easebuzz_id',
        'payout'              => 'merchant_key|start_date|end_date',
        'easy_collect'        => 'key|merchant_txn|name|email|phone|amount|udf1|udf2|udf3|udf4|udf5|message',
    );

    if (!isset($sequences[$api_name])) {
        return null;
    }

    $hash_string = '';
    foreach (explode('|', $sequences[$api_name]) as $field) {
        // Each field value is appended (empty string if not set) followed by pipe
        $hash_string .= isset($posted[$field]) ? $posted[$field] : '';
        $hash_string .= '|';
    }
    // Salt is appended at the end (after the last pipe)
    $hash_string .= $salt_key;

    return strtolower(hash('sha512', $hash_string));
}

/**
 * Generate reverse hash for response verification.
 */
function _getReverseHashKey($response_array, $salt)
{
    $fields = explode('|', 'udf10|udf9|udf8|udf7|udf6|udf5|udf4|udf3|udf2|udf1|email|firstname|productinfo|amount|txnid|key');

    $hash_string = $salt . '|' . (isset($response_array['status']) ? $response_array['status'] : '');
    foreach ($fields as $field) {
        $hash_string .= '|' . (isset($response_array[$field]) ? $response_array[$field] : '');
    }

    return strtolower(hash('sha512', $hash_string));
}

/**
 * Encrypt card details (AES-256-CBC).
 */
function _encryptCardDetail($data, $merchant_key, $salt)
{
    $key = substr(hash('sha256', $merchant_key), 0, 32);
    $iv = substr(hash('sha256', $salt), 0, 16);
    return base64_encode(openssl_encrypt($data, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv));
}

/**
 * Trim all values in an array.
 */
function _sanitizeParams($params)
{
    $result = array();
    foreach ($params as $key => $value) {
        $result[$key] = is_array($value) ? $value : trim($value);
    }
    return $result;
}

/**
 * Remove parameters with empty string values from array.
 *
 * Used AFTER hash generation to clean the POST body before sending to Easebuzz.
 * We don't want to send empty fields in the actual API request.
 *
 * NOTE: This is called AFTER generateHashValue() because the hash calculation
 * needs empty fields to be present (as empty strings with pipe separators).
 * But the actual HTTP request should only contain non-empty values.
 */
function _removeEmptyParams($params)
{
    return array_filter($params, function ($value) {
        return is_array($value) || (isset($value) && trim($value) !== '');
    });
}

/**
 * Validate email format.
 */
function _validateEmail($email)
{
    $pattern = '/^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/';
    if (!preg_match($pattern, $email)) {
        return 'Email invalid, Please enter valid email.';
    }
    return null;
}

/**
 * Validate initiate payment mandatory params and formats.
 */
function _validateInitiatePaymentParams($params)
{
    $mandatory = array('txnid', 'amount', 'firstname', 'email', 'phone', 'productinfo', 'surl', 'furl');
    foreach ($mandatory as $field) {
        if (!isset($params[$field]) || trim($params[$field]) === '') {
            return "Mandatory parameter '" . $field . "' cannot be empty";
        }
    }

    $patterns = array(
        'txnid'       => '/^[a-zA-Z0-9_|\-\/]{1,40}$/',
        'productinfo' => '/^[a-zA-Z0-9\-\s|\-]{1,45}$/',
        'firstname'   => '/^[a-zA-Z0-9&\'\-._ ()\/,@]{1,150}$/',
        'phone'       => '/^(\+\d{1,4}[-]?)?\d{5,20}$/',
    );

    foreach ($patterns as $field => $pattern) {
        if (isset($params[$field]) && $params[$field] !== '' && !preg_match($pattern, $params[$field])) {
            return "Invalid value for '" . $field . "'";
        }
    }

    if (strpos($params['amount'], '.') === false) {
        return "Amount must contain a decimal point (e.g., 125.0)";
    }

    $amt = floatval($params['amount']);
    if ($amt < 1) {
        return "Amount must be greater than or equal to 1";
    }

    $email_error = _validateEmail($params['email']);
    if ($email_error !== null) {
        return $email_error;
    }

    // If payment_category is TPV, account_no and ifsc become mandatory
    if (isset($params['payment_category']) && strtoupper(trim($params['payment_category'])) === 'TPV') {
        if (!isset($params['account_no']) || trim($params['account_no']) === '') {
            return "Mandatory parameter 'account_no' cannot be empty when payment_category is TPV";
        }
        if (!isset($params['ifsc']) || trim($params['ifsc']) === '') {
            return "Mandatory parameter 'ifsc' cannot be empty when payment_category is TPV";
        }
    }

    return null;
}

/**
 * Validate mandatory fields exist and are non-empty.
 */
function _validateMandatoryFields($params, $fields)
{
    foreach ($fields as $field) {
        if (!isset($params[$field]) || trim($params[$field]) === '') {
            return "Mandatory parameter '" . $field . "' cannot be empty";
        }
    }
    return null;
}

/**
 * Call Initiate Payment API and return access_key.
 *
 * Validates params, generates hash, calls Easebuzz, returns access_key on success.
 * Used by: initiate_payment, initiate_payment_iframe, initiate_seamless_payment.
 *
 * @param array $params - payment params (txnid, amount, firstname, email, phone, productinfo, surl, furl, etc.)
 * @param string $merchantKey
 * @param string $salt
 * @param string $env
 * @return array ['status' => 0|1, 'data' => access_key string on success | error on failure]
 */
function _callInitiatePaymentAPI($params, $merchantKey, $salt, $env)
{
    // Ensure credentials are not empty
    if (empty($merchantKey) || empty($salt) || empty($env)) {
        return array('status' => 0, 'data' => 'Merchant credentials are not configured');
    }

    // Validate
    $error = _validateInitiatePaymentParams($params);
    if ($error !== null) {
        return array('status' => 0, 'data' => $error);
    }

    // Add key and generate hash
    $params['key'] = $merchantKey;
    $params['hash'] = generateHashValue($params, $salt, 'initiate_payment');

    // udf8-10 are part of hash but NOT sent in POST body
    unset($params['udf8'], $params['udf9'], $params['udf10']);
    $params = _removeEmptyParams($params);

    // Call API
    $url = fetchBaseUrl($env, 'initiate_api') . 'payment/initiateLink';
    $response = _curlCall($url, http_build_query($params));

    // Check response
    if (isset($response->status) && $response->status == 1) {
        return array('status' => 1, 'data' => $response->data);
    }

    // Return complete error
    return array('status' => 0, 'data' => $response);
}

?>
