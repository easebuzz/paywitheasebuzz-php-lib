<?php
/**
 * Easy Collect API
 *
 * Creates a payment link that can be sent to customers via SMS, Email, or WhatsApp.
 *
 * REQUIRED PARAMS: name, phone, amount
 * OPTIONAL PARAMS: email, merchant_txn, message, udf1-udf5, expiry_date,
 *                  update, active, accept_partial_payment, split_payments, sub_merchant_id
 * NOTIFICATION: op_sms, op_email, op_whatsapp (checkboxes → operation array)
 *
 * ENDPOINT: POST {dashboardBaseUrl}/easycollect/v1/create
 * HASH SEQUENCE: key|merchant_txn|name|email|phone|amount|udf1|udf2|udf3|udf4|udf5|message|SALT
 * CONTENT-TYPE: application/json
 *
 * NOTE: Boolean fields (update, active, accept_partial_payment) must be actual
 *       booleans in JSON (true/false), not strings.
 */

include_once(__DIR__ . '/utils.php');

$params = _sanitizeParams($postData);

// Validate
$error = _validateMandatoryFields($params, array('name', 'phone', 'amount'));
if ($error !== null) {
    displayResponse(array('status' => 0, 'data' => $error), $params);
    return;
}

// Add key and generate hash
$params['key'] = $merchantKey;
$params['hash'] = generateHashValue($params, $salt, 'easy_collect');

// Build JSON request body
$requestBody = array(
    'key' => $merchantKey,
    'hash' => $params['hash'],
    'name' => $params['name'],
    'phone' => $params['phone'],
    'amount' => $params['amount']
);

// Optional string fields — add only if non-empty
$optional = array('email', 'merchant_txn', 'message', 'udf1', 'udf2', 'udf3', 'udf4', 'udf5',
    'expiry_date', 'split_percentage', 'split_payments', 'sub_merchant_id');
foreach ($optional as $field) {
    if (isset($params[$field]) && trim($params[$field]) !== '') {
        $requestBody[$field] = $params[$field];
    }
}

// Boolean fields — pass as string
foreach (array('update', 'accept_partial_payment') as $field) {
    if (isset($params[$field]) && trim($params[$field]) !== '') {
        $requestBody[$field] = $params[$field];
    }
}

// Notification channels — build operation array from checkboxes
$operation = array();
if (isset($params['op_sms']) && $params['op_sms'] === 'on') {
    $operation[] = array('type' => 'sms', 'template' => 'Default sms template');
}
if (isset($params['op_email']) && $params['op_email'] === 'on') {
    $operation[] = array('type' => 'email', 'template' => 'Default email template');
}
if (isset($params['op_whatsapp']) && $params['op_whatsapp'] === 'on') {
    $operation[] = array('type' => 'whatsapp', 'template' => 'Default whatsapp template');
}
if (!empty($operation)) {
    $requestBody['operation'] = $operation;
}

// Call API (JSON body)
$url = fetchBaseUrl($env) . 'easycollect/v1/create';
$response = _curlCallJson($url, $requestBody);

// Return complete response
$status = (isset($response->status)) ? (int)$response->status : 0;
displayResponse(array('status' => $status, 'data' => $response), $postData);
