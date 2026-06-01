<?php
/**
 * Seamless Payment API (Merchant-Hosted Checkout)
 *
 * Allows merchants to collect payment details (Card/UPI/Net Banking) on their own page
 * and process the payment without redirecting to Easebuzz hosted page.
 *
 * FLOW:
 *   1. Separate payment params from seamless params (card/UPI/NB details)
 *   2. Encrypt card details if payment mode is CC/DC/EMI
 *   3. Call Initiate Payment API to get access_key
 *   4. Call Seamless API with access_key + payment mode details
 *   5. Handle response (bank page HTML or JSON result)
 *
 * SUPPORTED PAYMENT MODES:
 *   CC  - Credit Card       → requires: card_number, card_holder_name, card_cvv, card_expiry_date
 *   DC  - Debit Card        → requires: card_number, card_holder_name, card_cvv, card_expiry_date
 *   NB  - Net Banking       → requires: bank_code (e.g., "HDFCB", "SBOI")
 *   UPI - UPI               → requires: upi_va (e.g., "success@easebuzz")
 *   MW  - Mobile Wallet     → requires: bank_code (e.g., "SGMW")
 *   OM  - Ola Money         → requires: bank_code
 *   PL  - Pay Later         → no additional params
 *   EMI - EMI               → requires: card details + emi_object
 *
 * ENDPOINTS:
 *   Step 1: POST {payBaseUrl}/payment/initiateLink
 *   Step 2: POST {payBaseUrl}/initiate_seamless_payment/
 */

include_once(__DIR__ . '/utils.php');

$params = _sanitizeParams($postData);

// ─── STEP 1: Separate payment params from seamless-specific params ───

$seamlessKeys = array(
    'payment_mode',       // CC, DC, NB, UPI, MW, OM, PL, EMI
    'bank_code',          // For NB, MW, OM
    'card_number',        // For CC, DC, EMI
    'card_holder_name',   // For CC, DC, EMI
    'card_cvv',           // For CC, DC, EMI
    'card_expiry_date',   // For CC, DC, EMI (format: MM/YY)
    'upi_va',             // For UPI
    'upi_qr',             // For UPI QR
    'request_mode',       // For UPI (set as SUVA in backend)
    'emi_object',         // For EMI (JSON string)
);

$seamlessParams = array();
foreach ($seamlessKeys as $key) {
    if (isset($params[$key]) && trim($params[$key]) !== '') {
        $seamlessParams[$key] = $params[$key];
    }
    unset($params[$key]);
}
unset($params['pay_later_app']); // Frontend-only, never sent to API

// ─── STEP 2: Encrypt card details (only for CC, DC, EMI) ───

if (isset($seamlessParams['card_number'])) {
    $seamlessParams['card_number'] = str_replace(' ', '', $seamlessParams['card_number']);
}

if (isset($seamlessParams['payment_mode']) && in_array($seamlessParams['payment_mode'], array('DC', 'CC', 'EMI'))) {
    $cardFields = array('card_number', 'card_holder_name', 'card_cvv', 'card_expiry_date');
    foreach ($cardFields as $field) {
        if (isset($seamlessParams[$field]) && trim($seamlessParams[$field]) !== '') {
            $seamlessParams[$field] = _encryptCardDetail($seamlessParams[$field], $merchantKey, $salt);
        }
    }
}

// For UPI mode: set request_mode as SUVA
if (isset($seamlessParams['payment_mode']) && $seamlessParams['payment_mode'] === 'UPI') {
    $seamlessParams['request_mode'] = 'SUVA';
}

// ─── STEP 3: Call Initiate Payment API to get access_key ───

$result = _callInitiatePaymentAPI($params, $merchantKey, $salt, $env);

if ($result['status'] != 1) {
    displayResponse($result, $postData);
    return;
}

// ─── STEP 4: Call Seamless Payment API ───

$seamlessParams['access_key'] = $result['data'];
$seamlessParams = _removeEmptyParams($seamlessParams);

$seamlessUrl = fetchBaseUrl($env, 'initiate_api') . 'initiate_seamless_payment/';
$seamlessResponse = _curlCall($seamlessUrl, http_build_query($seamlessParams));

// ─── STEP 5: Handle response ───

// Bank/3DS page — render directly so customer can complete authentication
// The HTML comes from Easebuzz trusted payment server via SSL-verified cURL call
if (is_string($seamlessResponse)) {
    // Ensure response is actually HTML (contains doctype or html tag)
    $trimmedResponse = trim($seamlessResponse);
    if (stripos($trimmedResponse, '<!DOCTYPE') === 0 || stripos($trimmedResponse, '<html') !== false) {
        header('Content-Type: text/html; charset=UTF-8');
        echo $trimmedResponse;
        exit;
    }
    // Not valid HTML — treat as error
    displayResponse(array('status' => 0, 'data' => 'Unexpected response format from payment server'), $postData);
    return;
}

// JSON response — show success or error
if (isset($seamlessResponse->status)) {
    displayResponse(array('status' => $seamlessResponse->status, 'data' => $seamlessResponse), $postData);
} else {
    displayResponse(array('status' => 0, 'data' => $seamlessResponse), $postData);
}
