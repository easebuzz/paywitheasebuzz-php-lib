<?php
/**
 * Payout / Settlement API
 *
 * Retrieves settlement/payout details for a date range.
 *
 * REQUIRED PARAMS: start_date (DD-MM-YYYY), end_date (DD-MM-YYYY)
 * OPTIONAL PARAMS: merchant_email, sub_merchant_id
 * ENDPOINT: POST {dashboardBaseUrl}/settlements/v1/retrieve
 * HASH SEQUENCE: merchant_key|start_date|end_date|SALT
 * CONTENT-TYPE: application/json
 *
 * NOTE: Uses "merchant_key" (not "key") in hash and request body.
 *       Dates are nested inside "payout_date" object.
 *       merchant_email is NOT part of the hash.
 */

include_once(__DIR__ . '/utils.php');

$params = _sanitizeParams($postData);

// Validate
$error = _validateMandatoryFields($params, array('start_date', 'end_date'));
if ($error !== null) {
    displayResponse(array('status' => 0, 'data' => $error), $params);
    return;
}

// Generate hash (uses "merchant_key" not "key")
$params['merchant_key'] = $merchantKey;
$params['hash'] = generateHashValue($params, $salt, 'payout');

// Build JSON body — dates go inside "payout_date" object
$requestBody = array(
    'merchant_key' => $merchantKey,
    'hash' => $params['hash'],
    'payout_date' => array(
        'start_date' => $params['start_date'],
        'end_date' => $params['end_date']
    )
);

// merchant_email is optional (NOT part of hash)
if (isset($params['merchant_email']) && trim($params['merchant_email']) !== '') {
    $requestBody['merchant_email'] = $params['merchant_email'];
}

// sub_merchant_id is optional (NOT part of hash)
if (isset($params['sub_merchant_id']) && trim($params['sub_merchant_id']) !== '') {
    $requestBody['sub_merchant_id'] = $params['sub_merchant_id'];
}

// Call API (JSON body)
$url = fetchBaseUrl($env) . 'settlements/v1/retrieve';
$response = _curlCallJson($url, $requestBody);

// Return complete response
$status = (isset($response->status)) ? (int)$response->status : 0;
displayResponse(array('status' => $status, 'data' => $response), $postData);
