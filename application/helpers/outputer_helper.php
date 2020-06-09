<?php


function send_response(array $resp)
{
	header('Content-Type: application/json');
	echo json_encode($resp, JSON_PRETTY_PRINT);
	exit(0);
}

function validate_json(string $json)
{
	$encode = json_decode($json, true);
	if ($encode) {
		return $encode;
	}
	send_response(INVALID_PARAMS);
}

function validate_phone(string $phone)
{
	if (filter_var($phone, FILTER_SANITIZE_STRING)) {
		return $phone;
	}
	send_response(INVALID_PARAMS);
}

function validate_string(string $msg)
{
	if (filter_var($msg, FILTER_SANITIZE_STRING)) {
		return $msg;
	}
	send_response(INVALID_PARAMS);
}

function valid_int($value)
{
	$value = (int)$value;
	if (isset($value) && $value !== NULL && ($value > -1 || $value === 0)) {
		return $value;
	}
	if ($value === 0) {
		return 0;
	}
	return false;
}


function validate_integer(...$value)
{
	foreach ($value as $v) {
		if (valid_int($v) !== false) {
			// do nothing for now
		} else {
			send_response(INVALID_PARAMS);
		}
	}
}
