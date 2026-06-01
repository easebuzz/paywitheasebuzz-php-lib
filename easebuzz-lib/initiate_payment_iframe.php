<?php
/**
 * Initiate Payment API (iframe / Ease Checkout)
 *
 * Same as Initiate Payment but returns JSON with access_key instead of redirecting.
 * Frontend uses access_key with Easebuzz Checkout SDK to open payment popup.
 *
 * REQUIRED PARAMS: txnid, amount, firstname, email, phone, productinfo, surl, furl
 * RETURNS JSON: { status: 1, data: { key, access_key, baseUrl, env } }
 *
 * ENDPOINT: POST {payBaseUrl}/payment/initiateLink
 * HASH SEQUENCE: key|txnid|amount|productinfo|firstname|email|udf1|udf2|udf3|udf4|udf5|udf6|udf7|udf8|udf9|udf10|SALT
 */

include_once(__DIR__ . '/utils.php');

$params = _sanitizeParams($postData);

// Call Initiate Payment API to get access_key
$result = _callInitiatePaymentAPI($params, $merchantKey, $salt, $env);

header('Content-Type: application/json');

if ($result['status'] == 1) {
    // Return access_key for frontend SDK
    echo json_encode(array(
        'status' => 1,
        'data' => array(
            'key' => $merchantKey,
            'access_key' => $result['data'],
            'baseUrl' => fetchBaseUrl($env, 'initiate_api'),
            'env' => $env
        )
    ));
} else {
    // Return complete error response
    echo json_encode($result);
}
exit;
