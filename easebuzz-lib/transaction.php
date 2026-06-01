<?php
/**
 * Transaction V2 API
 *
 * Retrieves transaction details by transaction ID.
 *
 * REQUIRED PARAMS: txnid
 * ENDPOINT: POST {dashboardBaseUrl}/transaction/v2/retrieve
 * HASH SEQUENCE: key|txnid|SALT
 * CONTENT-TYPE: application/x-www-form-urlencoded
 */

include_once(__DIR__ . '/utils.php');

$params = _sanitizeParams($postData);

// Validate
$error = _validateMandatoryFields($params, array('txnid'));
if ($error !== null) {
    displayResponse(array('status' => 0, 'data' => $error), $params);
    return;
}

// Add key and generate hash
$params['key'] = $merchantKey;
$params['hash'] = generateHashValue($params, $salt, 'transaction');
$params = _removeEmptyParams($params);

// Call API
$url = fetchBaseUrl($env) . 'transaction/v2/retrieve';
$response = _curlCall($url, http_build_query($params));

// Return complete response
$status = (isset($response->status)) ? (int)$response->status : 0;
displayResponse(array('status' => $status, 'data' => $response), $postData);
