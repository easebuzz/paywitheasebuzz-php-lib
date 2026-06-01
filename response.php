<?php
/**
 * Payment Response Handler
 *
 * Handles POST callback from Easebuzz after payment completion.
 * Verifies response hash to ensure authenticity.
 */

include_once('easebuzz-lib/utils.php');

_loadEnvFile(__DIR__ . '/.env');
$config = _getConfig();
$salt = isset($config['salt']) ? $config['salt'] : '';

$responseData = '';
$errorMessage = '';
$isSuccess = false;

if (!empty($_POST) && count($_POST) > 0) {
    $reverseHash = _getReverseHashKey($_POST, $salt);
    if (isset($_POST['hash']) && hash_equals($reverseHash, $_POST['hash'])) {
        $responseData = json_encode($_POST, JSON_PRETTY_PRINT);
        $isSuccess = (isset($_POST['status']) && $_POST['status'] === 'success');
    } else {
        $errorMessage = 'Hash verification failed - response may be tampered';
    }
} else {
    $errorMessage = 'No response data received';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Response</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .response-container{margin:20px auto;max-width:900px;padding:20px}
        .error-block{background:#ffebee;border:1px solid #f44336;color:#c62828;padding:15px;border-radius:5px;margin:10px 0}
        .success-block{background:#e8f5e8;border:1px solid #4caf50;padding:15px;border-radius:5px;margin:10px 0}
        .failure-block{background:#ffebee;border:1px solid #f44336;color:#c62828;padding:15px;border-radius:5px;margin:10px 0}
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
        <h3>Transaction Details</h3>
        <table>
            <?php
            $displayFields = array('txnid', 'amount', 'firstname', 'email', 'phone', 'productinfo', 'status');
            foreach ($displayFields as $field) {
                if (isset($_POST[$field])) {
                    echo '<tr><td>' . htmlspecialchars($field, ENT_QUOTES, 'UTF-8') . '</td><td>' . htmlspecialchars($_POST[$field], ENT_QUOTES, 'UTF-8') . '</td></tr>';
                }
            }
            ?>
        </table>
    </div>
    <h2>Payment Response</h2>
    <?php if (!empty($errorMessage)): ?>
        <div class="error-block">
            <h3>Error</h3>
            <p><?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?></p>
        </div>
    <?php elseif (!empty($responseData)): ?>
        <div class="<?php echo $isSuccess ? 'success-block' : 'failure-block'; ?>">
            <h3>Payment <?php echo $isSuccess ? 'Success' : 'Failed'; ?></h3>
            <pre class="json-display"><?php echo htmlspecialchars($responseData, ENT_QUOTES, 'UTF-8'); ?></pre>
        </div>
    <?php else: ?>
        <div class="error-block">
            <h3>No Response</h3>
            <p>No response data was received.</p>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
