<?php
/**
 * Initiate Payment API (Hosted Checkout)
 *
 * Initiates a payment and redirects the customer to Easebuzz hosted payment page.
 * After payment, Easebuzz POSTs result to your surl (success) or furl (failure).
 *
 * REQUIRED PARAMS: txnid, amount, firstname, email, phone, productinfo, surl, furl
 * OPTIONAL PARAMS: udf1-udf7, address1, address2, city, state, country, zipcode,
 *                  sub_merchant_id, unique_id, show_payment_mode, split_payments
 *
 * ENDPOINT: POST {payBaseUrl}/payment/initiateLink
 * HASH SEQUENCE: key|txnid|amount|productinfo|firstname|email|udf1|udf2|udf3|udf4|udf5|udf6|udf7|udf8|udf9|udf10|SALT
 */

include_once(__DIR__ . '/utils.php');

$params = _sanitizeParams($postData);

// Call Initiate Payment API to get access_key
$result = _callInitiatePaymentAPI($params, $merchantKey, $salt, $env);

if ($result['status'] != 1) {
    displayResponse($result, $postData);
    return;
}

// On success: redirect customer to Easebuzz payment page
$accessKey = $result['data'];

// Validate access_key format — must be exactly 64 hex chars
if (empty($accessKey) || !preg_match('/^[a-f0-9]{64}$/', $accessKey)) {
    displayResponse(array('status' => 0, 'data' => 'Invalid access key received'), $postData);
    return;
}

// Build redirect URL from hardcoded trusted domains only
if ($env === 'prod') {
    $redirectUrl = 'https://pay.easebuzz.in/pay/' . $accessKey;
} else {
    $redirectUrl = 'https://testpay.easebuzz.in/pay/' . $accessKey;
}

header('Location: ' . $redirectUrl);
exit;
