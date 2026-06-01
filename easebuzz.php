<?php
/**
 * Easebuzz Sample Router
 *
 * Routes API requests to individual handler files.
 * Each API has its own file — merchants can pick only what they need.
 */

include_once('easebuzz-lib/utils.php');

// Load environment
_loadEnvFile(__DIR__ . '/.env');

// Load configuration
$config = _getConfig();
if (isset($config['status']) && $config['status'] === 0) {
    displayError($config['data']);
    exit;
}

$merchantKey = $config['merchant_key'];
$salt = $config['salt'];
$env = $config['env'];

// Validate request
if (empty($_POST) || count($_POST) === 0) {
    displayError('Please fill all mandatory fields.');
    exit;
}

// Route to API handler using strict whitelist
$apiName = isset($_GET['api_name']) ? $_GET['api_name'] : '';

// Static mapping — no user input touches file paths
switch ($apiName) {
    case 'initiate_payment':
        $postData = $_POST;
        include(__DIR__ . '/easebuzz-lib/initiate_payment.php');
        break;
    case 'initiate_payment_iframe':
        $postData = $_POST;
        include(__DIR__ . '/easebuzz-lib/initiate_payment_iframe.php');
        break;
    case 'initiate_seamless_payment':
        $postData = $_POST;
        include(__DIR__ . '/easebuzz-lib/initiate_seamless_payment.php');
        break;
    case 'transaction':
        $postData = $_POST;
        include(__DIR__ . '/easebuzz-lib/transaction.php');
        break;
    case 'transaction_date':
        $postData = $_POST;
        include(__DIR__ . '/easebuzz-lib/transaction_date.php');
        break;
    case 'refund':
        $postData = $_POST;
        include(__DIR__ . '/easebuzz-lib/refund.php');
        break;
    case 'refund_status':
        $postData = $_POST;
        include(__DIR__ . '/easebuzz-lib/refund_status.php');
        break;
    case 'payout':
        $postData = $_POST;
        include(__DIR__ . '/easebuzz-lib/payout.php');
        break;
    case 'easy_collect':
        $postData = $_POST;
        include(__DIR__ . '/easebuzz-lib/easy_collect.php');
        break;
    default:
        displayError('Unknown API. Please try again.');
        exit;
}

// --- Helper Functions ---

function displayError($message)
{
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div style="margin:20px auto;max-width:900px;padding:20px;">
    <h2><a href="index.html">&larr; Back</a></h2>
    <div style="background:#ffebee;border:1px solid #f44336;color:#c62828;padding:15px;border-radius:5px;">
        <h3>Error</h3>
        <p><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></p>
    </div>
</div>
</body>
</html>
    <?php
}

function displayResponse($result, $postData)
{
    $status = isset($result['status']) ? $result['status'] : 0;
    $data = isset($result['data']) ? $result['data'] : null;
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Response</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .response-container{margin:20px auto;max-width:900px;padding:20px}
        .error-block{background:#ffebee;border:1px solid #f44336;color:#c62828;padding:15px;border-radius:5px;margin:10px 0}
        .success-block{background:#e8f5e8;border:1px solid #4caf50;padding:15px;border-radius:5px;margin:10px 0}
        .json-display{background:#f5f5f5;padding:15px;font-family:'Courier New',monospace;font-size:14px;overflow-x:auto;border-radius:4px;white-space:pre-wrap;word-wrap:break-word}
        .request-details{margin-top:15px}
        .request-details table{width:100%;border-collapse:collapse}
        .request-details td{padding:5px 10px;border-bottom:1px solid #ddd}
        .request-details td:first-child{font-weight:bold;width:150px}
    </style>
</head>
<body>
<div class="response-container">
    <h2><a href="index.html">&larr; Back</a></h2>
    <div class="request-details">
        <h3>Request Details</h3>
        <table>
            <?php
            $displayFields = array('txnid', 'amount', 'firstname', 'email', 'phone', 'productinfo');
            foreach ($displayFields as $field) {
                if (!empty($postData[$field])) {
                    echo '<tr><td>' . htmlspecialchars($field, ENT_QUOTES, 'UTF-8') . '</td><td>' . htmlspecialchars($postData[$field], ENT_QUOTES, 'UTF-8') . '</td></tr>';
                }
            }
            ?>
        </table>
    </div>
    <h2>API Response</h2>
    <?php if ($status == 1): ?>
        <div class="success-block">
            <h3>Success</h3>
            <pre class="json-display"><?php echo htmlspecialchars(is_array($data) || is_object($data) ? json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : $data, ENT_QUOTES, 'UTF-8'); ?></pre>
        </div>
    <?php else: ?>
        <div class="error-block">
            <h3>Error</h3>
            <pre class="json-display"><?php echo htmlspecialchars(is_array($data) || is_object($data) ? json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : $data, ENT_QUOTES, 'UTF-8'); ?></pre>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
    <?php
}
