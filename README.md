# Easebuzz PHP Integration Kit v2.0

PHP SDK for integrating with Easebuzz Payment Gateway (pay.easebuzz.in).

## Requirements

PHP 5.5 or above
php-curl extension
php-openssl extension (for seamless payment card encryption)
Apache, Nginx, WAMP, XAMPP, or PHP built-in server

## Quick Start

### 1. Setup

bash
git clone https://github.com/easebuzz/paywitheasebuzz-php-lib.git
cd paywitheasebuzz-php-lib

### 2. Configure Environment

Edit .env file with your merchant credentials:
EASEBUZZ_MERCHANT_KEY=your_merchant_key
EASEBUZZ_SALT=your_salt
ENV=test

### 3. Run Development Server

bash
php -S localhost:3000

Open http://localhost:3000 in your browser to access the sample forms.

## Available APIs

| API | Description |
|-----|-------------|
| Initiate Payment | Redirect customer to Easebuzz hosted payment page |
| Initiate Payment (iframe) | Open payment in Ease Checkout popup |
| Seamless Payment | Merchant-hosted card/UPI payment flow |
| Transaction API | Query single transaction status by txnid |
| Transaction Date API | Query transactions by date range |
| Refund API | Initiate refund on a transaction |
| Refund Status API | Check refund status by easebuzz_id |
| Payout API | Get settlement/payout details by date range |
| Easy Collect API | Create payment link |

## Integration Guide

### How It Works

1. HTML forms in view/ collect payment parameters
2. Forms POST to easebuzz.php?api_name=<api> which routes to the correct handler
3. Each handler in easebuzz-lib/ validates params, generates hash, calls Easebuzz API
4. Response is displayed on a styled response page

### Initiate Payment (Hosted Checkout)

php
// Form POSTs to: easebuzz.php?api_name=initiate_payment
// Required params: txnid, amount, firstname, email, phone, productinfo, surl, furl
// On success: redirects customer to Easebuzz payment page

### Initiate Payment (iframe / Ease Checkout)

php
// Form POSTs via AJAX to: easebuzz.php?api_name=initiate_payment_iframe
// Returns JSON: { status: 1, key: '...', access_key: '...', env: '...' }
// Use access_key with EasebuzzCheckout JS SDK

### Seamless Payment

php
// Form POSTs to: easebuzz.php?api_name=initiate_seamless_payment
// Additional params: payment_mode, card_number, card_holder_name, card_cvv, card_expiry_date
// Card details are AES-256-CBC encrypted before sending

### Transaction API

php
// Form POSTs to: easebuzz.php?api_name=transaction
// Required params: txnid
// Hash sequence: key|txnid

### Transaction Date API

php
// Form POSTs to: easebuzz.php?api_name=transaction_date
// Required params: merchant_email, start_date, end_date
// Hash sequence: key|merchant_email|start_date|end_date

### Refund API

php
// Form POSTs to: easebuzz.php?api_name=refund
// Required params: easebuzz_id, refund_amount, merchant_refund_id
// Hash sequence: key|merchant_refund_id|easebuzz_id|refund_amount

### Refund Status API

php
// Form POSTs to: easebuzz.php?api_name=refund_status
// Required params: easebuzz_id
// Hash sequence: key|easebuzz_id

### Payout API

php
// Form POSTs to: easebuzz.php?api_name=payout
// Required params: start_date, end_date
// Hash sequence: merchant_key|start_date|end_date

### Easy Collect (Payment Link)

php
// Form POSTs to: easebuzz.php?api_name=easy_collect
// Required params: merchant_txn, name, email, phone, amount, message
// Hash sequence: key|merchant_txn|name|email|phone|amount|udf1|udf2|udf3|udf4|udf5|message

## Hash Generation

All API calls are signed with SHA-512 hash:

Hash String = field1Value|field2Value|...|fieldNValue|salt
Hash = SHA-512(Hash String)

For initiate payment:
key|txnid|amount|productinfo|firstname|email|udf1|udf2|udf3|udf4|udf5|udf6|udf7|udf8|udf9|udf10|salt

**Note:** udf8, udf9, udf10 are included in hash calculation but removed from the POST body before sending to API.

## Response Hash Verification

Payment callbacks include a reverse hash for verification:

Reverse Hash = SHA-512(salt|status|udf10|udf9|udf8|...|udf1|email|firstname|productinfo|amount|txnid|key)

The response.php handles this verification automatically using _getReverseHashKey().

## File Structure

easebuzz-lib/
  utils.php                      - Core utilities (hash, cURL, validation, config)
  initiate_payment.php           - Initiate Payment handler
  initiate_payment_iframe.php    - Initiate Payment (iframe) handler
  initiate_seamless_payment.php  - Seamless Payment handler
  transaction.php                - Transaction API handler
  transaction_date.php           - Transaction Date API handler
  refund.php                     - Refund API handler
  refund_status.php              - Refund Status API handler
  payout.php                     - Payout API handler
  easy_collect.php               - Easy Collect API handler

assets/
  css/style.css                  - Stylesheet
  js/form-validation.js          - Client-side form validation
  images/                        - Logo assets

view/
  initiate_payment.html          - Initiate Payment form
  initiate_payment_iframe.html   - Initiate Payment (iframe) form
  seamless_payment.html          - Seamless Payment form
  transaction.html               - Transaction API form
  transaction_date.html          - Transaction Date API form
  refund.html                    - Refund API form
  refund_status.html             - Refund Status API form
  payout.html                    - Payout API form
  easy_collect.html              - Easy Collect API form

easebuzz.php                     - API router (routes to handlers)
response.php                     - Payment callback handler
index.html                       - Main navigation page
.env                             - Environment configuration (credentials)
logs/                            - Log files directory

## Client-Side Form Validation

The form-validation.js provides:
Auto-detection of required fields via input[required] selector
Auto-detection of pattern fields via input[pattern] selector
Live validation on keystroke with red highlight (#ff0000 border, #ffebee background)
On submit: inline error messages below invalid fields
Amount range check (must be > 0 and ≤ 9999999)
Bullet-point alert listing all missing required fields

## Security Features

SHA-512 request signing on all API calls
Reverse hash verification on payment callbacks (timing-safe comparison)
AES-256-CBC card encryption for seamless payments
SSL/TLS verification enabled on all cURL requests (HTTPS only)
Input validation with regex patterns and length limits
XSS prevention via htmlspecialchars output encoding
Environment variable-based credential management (.env file)
No sensitive data in logs

## Advanced Features

### Split Payments
split_payments = {"label_bank1": 100, "label_bank2": 100}

### TPV (Third Party Validation)
payment_category = TPV
account_no = 0000000000000000
ifsc = IFSCCODE

### Sub-Merchant
sub_merchant_id = SUB123

### Saved Cards (Unique ID)
unique_id = CUSTOMER_001

### Restrict Payment Modes
show_payment_mode = NB,DC,CC,UPI

## Documentation

Full API documentation: https://docs.easebuzz.in/
