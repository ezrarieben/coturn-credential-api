<?php
/**
 * PHP script to generate CoTURN server credentials
 * Script acts as an API endpoint and therefore responds with a JSON Array
 * 
 * @author Ezra Rieben (@ezrarieben)
 */

 require_once './config.inc.php';

// Set response headers to JSON
header('Content-Type: application/json; charset=utf-8');

/**
 * Function used to return JSON object as API response
 *
 * @param bool $success Used to identify wether action was successfull or not
 * @param string $message Message to send (used mainly for errors)
 * @param array $data Data to return in JSON response
 *
 * @return JSON [return description]
 */
function _response(bool $success, string $message, array $data = [])
{
    $response = array(
        'success' => $success,
        'message' => $message,
        'data' => $data
    );

    die(json_encode($response));
}


$requiredParams = array(
    'key',
    'username',
);
foreach ($requiredParams as $requiredParam) {
    if (!isset($_REQUEST[$requiredParam])) {
        _response(false, "Required parameter '{$requiredParam}' missing. Please specify.");
    }
}

// Check to make sure that provided API key is in allowed list
$key = htmlspecialchars($_REQUEST['key']);
if (!in_array($key, ALLOWED_API_KEYS)) {
    _response(false, 'API key provided is invalid.');
}

$username = htmlspecialchars($_REQUEST['username']);

$credentialEndTime = time() + CREDENTIAL_TTL;
$username = $credentialEndTime . ':' . $username;
$passwordHash = hash_hmac('sha1', $username, TURN_AUTH_SECRET, true);
$password = base64_encode($passwordHash);

$credentialEndTimeFormatted = date('Y-m-d h:i:s', $credentialEndTime);

$message = "Credentials are valid until: " . date('Y-m-d h:i:s', $credentialEndTime) . " (UTC)";
$data = array(
    'username' => $username,
    'password' => $password,
    'credentialEndTime' => $credentialEndTime
);
_response(true, $message, $data);
