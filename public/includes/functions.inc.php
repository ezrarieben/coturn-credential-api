<?php
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
