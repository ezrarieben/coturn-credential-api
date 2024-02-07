<?php
/**
 * PHP script to generate CoTURN server credentials
 * Script acts as an API endpoint and therefore responds with a JSON Array
 * 
 * @author Ezra Rieben (@ezrarieben)
 */

 require_once './config.inc.php';
 require_once './includes/functions.inc.php';

// Set content type to JSON
header('Content-Type: application/json; charset=utf-8');

// Allow CORS from any source (allows usage of API in front end from any domain)
header("Access-Control-Allow-Origin: *");

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
    'ttl' => $credentialEndTime
);
_response(true, $message, $data);
