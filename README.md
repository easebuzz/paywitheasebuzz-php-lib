# Easebuzz PHP Integration Kit v2.0

PHP SDK for integrating with Easebuzz Payment Gateway (pay.easebuzz.in).

## Requirements

- PHP 5.5 or above
- php-curl extension
- php-openssl extension
- Apache, Nginx, WAMP, XAMPP, or PHP built-in server

## Quick Start

### 1. Setup

```bash
git clone <repository-url>
cd easebuzz-php-kit
cp .env.example .env
# Edit .env with your merchant credentials
```

### 2. Configure Environment

Edit `.env` file:
```
EASEBUZZ_MERCHANT_KEY=your_merchant_key
EASEBUZZ_SALT=your_salt
EASEBUZZ_ENV=test
EASEBUZZ_LOG_FILE=logs/easebuzz.log
```

### 3. Run Development Server

```bash
php -S localhost:3000
```

Open `http://localhost:3000` in your browser to access the sample forms.

## Integration Guide

### Basic Usage

```php
// 1. Include the SDK
include_once('easebuzz-lib/Easebuzz.php');

// 2. Load environment (optional - if using .env file)
EasebuzzConfig::loadEnvFile(__DIR__ . '/.env');
$config = EasebuzzConfig::loadFromEnv();

// 3. Create instance
$easebuzz = new Easebuzz(
    $config['merchant_key'],
    $config['salt'],
    $config['env'],
    'logs/easebuzz.log'  // Optional: log file path
);
```

### Initiate Payment (Hosted Checkout)

```php
$params = array(
    'txnid' => 'TXN' . time(),
    'amount' => '100.0',
    'firstname' => 'John Doe',
    'email' => 'john@example.com',
    'phone' => '9876543210',
    'productinfo' => 'Test Product',
    'surl' => 'http://localhost:3000/response.php',
    'furl' => 'http://localhost:3000/response.php'
);

// This will redirect the customer to Easebuzz payment page
$result = $easebuzz->initiatePayment($params);

// If validation fails, $result contains error:
// ['status' => 0, 'data' => 'Error message']
```

### Initiate Payment (iframe / Ease Checkout)

```php
$result = $easebuzz->initiatePaymentIframe($params);
// Returns: ['status' => 1, 'data' => ['key' => ..., 'access_key' => ..., 'baseUrl' => ...]]
```

### Seamless Payment (Merchant-Hosted)

```php
$params = array(
    // Standard payment params...
    'txnid' => 'TXN' . time(),
    'amount' => '100.0',
    'firstname' => 'John',
    'email' => 'john@example.com',
    'phone' => '9876543210',
    'productinfo' => 'Product',
    'surl' => 'http://localhost:3000/response.php',
    'furl' => 'http://localhost:3000/response.php',
    // Seamless-specific params:
    'payment_mode' => 'CC',
    'card_number' => '4111111111111111',
    'card_holder_name' => 'John Doe',
    'card_cvv' => '123',
    'card_expiry_date' => '12/25'
);

$result = $easebuzz->initiateSeamlessPayment($params);
```

### Transaction Status (V2)

```php
$result = $easebuzz->getTransaction(array('txnid' => 'TXN123'));
```

### Transactions by Date Range (V2)

```php
$result = $easebuzz->getTransactionsByDate(array(
    'start_date' => '01-01-2025',
    'end_date' => '31-01-2025',
    'merchant_email' => 'merchant@example.com'  // optional
));
```

### Refund (V2)

```php
$result = $easebuzz->refund(array(
    'easebuzz_id' => 'E2308172536',
    'refund_amount' => '50.0',
    'merchant_refund_id' => 'REF001'
));
```

### Refund Status

```php
$result = $easebuzz->getRefundStatus(array('easebuzz_id' => 'E2308172536'));
```

### Payout / Settlement

```php
$result = $easebuzz->getPayout(array(
    'start_date' => '01-01-2025',
    'end_date' => '31-01-2025',
    'merchant_email' => 'merchant@example.com'  // optional
));
```

### Easy Collect (Payment Link)

```php
$result = $easebuzz->createEasyCollect(array(
    'name' => 'John Doe',
    'phone' => '9876543210',
    'amount' => '500.0',
    'email' => 'john@example.com',
    'merchant_txn' => 'EC001',
    'message' => 'Payment for order #123'
));
```

### Verify Payment Response (Callback)

```php
// In your response.php (surl/furl endpoint):
include_once('easebuzz-lib/Easebuzz.php');
EasebuzzConfig::loadEnvFile(__DIR__ . '/.env');
$config = EasebuzzConfig::loadFromEnv();

$easebuzz = new Easebuzz($config['merchant_key'], $config['salt'], $config['env']);
$result = $easebuzz->verifyPaymentResponse($_POST);

if ($result['status'] === 1) {
    // Payment response is authentic
    $paymentData = $result['data'];
    // Process based on $paymentData['status'] (success/failure)
} else {
    // Hash mismatch - response may be tampered
}
```

## Response Hash Verification

All payment callbacks include a hash for verification:

```
Reverse Hash = SHA-512(salt|status|udf10|udf9|...|udf1|email|firstname|productinfo|amount|txnid|key)
```

The `verifyPaymentResponse()` method handles this automatically.

## File Structure

```
easebuzz-lib/
  Easebuzz.php          - Main SDK class (include this one file)
  EasebuzzHelper.php    - Hash, HTTP, validation utilities
  EasebuzzConfig.php    - Environment & URL configuration
  EasebuzzLogger.php    - Optional file-based logger

easebuzz.php            - Sample router (demo usage)
response.php            - Sample callback handler
csrf_token.php          - CSRF token endpoint for forms
.env.example            - Environment variable template
index.html              - Sample form navigation
view/                   - Sample HTML forms for each API
```

## Security Features

- SHA-512 request signing on all API calls
- Reverse hash verification on payment callbacks
- AES-256-CBC card encryption for seamless payments
- SSL/TLS verification enabled on all cURL requests
- CSRF protection on all form submissions
- HTTP security headers (X-Content-Type-Options, X-Frame-Options, HSTS, Referrer-Policy)
- Environment variable-based credential management
- Input validation with regex patterns and length limits
- XSS prevention via htmlspecialchars output encoding
- File-based error logging (sensitive data never logged)

## Documentation

Full API documentation: https://docs.easebuzz.in/

## Advanced Features

### Split Payments
```php
$params['split_payments'] = '{"label_bank1": 100, "label_bank2": 100}';
```

### Sub-Merchant
```php
$params['sub_merchant_id'] = 'SUB123';
```

### Saved Cards (Unique ID)
```php
$params['unique_id'] = 'CUSTOMER_001';
```

### Restrict Payment Modes
```php
$params['show_payment_mode'] = 'NB,DC,CC,UPI';
```
