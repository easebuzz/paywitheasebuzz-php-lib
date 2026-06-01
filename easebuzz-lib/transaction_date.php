<?php
/**
 * Transaction Date V2 API
 *
 * Retrieves all transactions within a date range.
 *
 * REQUIRED PARAMS: start_date (DD-MM-YYYY), end_date (DD-MM-YYYY)
 * OPTIONAL PARAMS: merchant_email
 * ENDPOINT: POST {dashboardBaseUrl}/transaction/v2/retrieve/date
 * HASH SEQUENCE: key|merchant_email|start_date|end_date|SALT
 * CONTENT-TYPE: application/json
 * NOTE: Dates are nested inside "date_range" object in request body
 */

include_once(__DIR__ . '/utils.php');

$params = _sanitizeParams($postData);

// Validate
$error = _validateMandatoryFields($params, array('start_date', 'end_date'));
if ($error !== null) {
    displayResponse(array('status' => 0, 'data' => $error), $params);
    return;
}

// Generate hash
$params['key'] = $merchantKey;
$params['hash'] = generateHashValue($params, $salt, 'transaction_date');

// Build JSON body — dates go inside "date_range" object
$requestBody = array(
    'key' => $merchantKey,
    'hash' => $params['hash'],
    'date_range' => array(
        'start_date' => $params['start_date'],
        'end_date' => $params['end_date']
    )
);

// merchant_email is optional
if (isset($params['merchant_email']) && trim($params['merchant_email']) !== '') {
    $requestBody['merchant_email'] = $params['merchant_email'];
}

// Call API (JSON body)
$url = fetchBaseUrl($env) . 'transaction/v2/retrieve/date';
$response = _curlCallJson($url, $requestBody);

// Return complete response
$status = (isset($response->status)) ? (int)$response->status : 0;
displayResponse(array('status' => $status, 'data' => $response), $postData);
