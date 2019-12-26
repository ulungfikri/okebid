<?php

function needAuth() {
    $CI =& get_instance();
    $headers = $CI->input->request_headers();
    if(!isset($headers['Authorization'])) {
        return false;
    }
    $token = $headers['Authorization'];
    return validateTimestamp($token);
}

function validateTimestamp($token)
{
    $CI =& get_instance();
    $token = validateToken($token);
    if ($token != false && (time() - $token->timestamp < ($CI->config->item('token_timeout') * 60))) {
        return $token;
    }
    return false;
}

function validateToken($token)
{
    $CI =& get_instance();
    return JWT::decode($token, $CI->config->item('jwt_key'));
}

 function generateToken($data)
{
    $CI =& get_instance();
    return JWT::encode($data, $CI->config->item('jwt_key'));
}

