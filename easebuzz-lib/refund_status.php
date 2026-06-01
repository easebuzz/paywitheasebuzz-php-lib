<?php
/**
 * Refund Status API
 *
 * Checks the status of a previously initiated refund.
 *
 * REQUIRED PARAMS: easebuzz_id
 * OPTIONAL PARAMS: merchant_refund_id (filter refunds by unique refund ID)
 * ENDPOINT: POST {dashboardBaseUrl}/refund/v1/retrieve
 * HASH SEQUENCE: key|easebuzz_id|SALT
 * CONTENT-TYPE: application/x-www-form-urlencoded
 */

include_once(__DIR__ . '/utils.php');

$params = _sanitizeParams($postData);

// Validate
$error = _validateMandatoryFields($params, array('easebuzz_id'));
if ($error !== null) {
    displayResponse(array('status' => 0, 'data' => $error), $params);
    return;
}

// Add key and generate hash
$params['key'] = $merchantKey;
$params['hash'] = generateHashValue($params, $salt, 'refund_status');
$params = _removeEmptyParams($params);

// Call API
$url = fetchBaseUrl($env) . 'refund/v1/retrieve';
$response = _curlCall($url, http_build_query($params));

// Return complete response
$status = (isset($response->status)) ? (int)$response->status : 0;
displayResponse(array('status' => $status, 'data' => $response), $postData);
