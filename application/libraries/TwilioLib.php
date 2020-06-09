<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');
require_once 'vendor\autoload.php';

use Twilio\Rest\Client;
use Twilio\TwiML\VoiceResponse;

class TwilioLib
{
	public $twilio;
	public $response;

	public function __construct()
	{
		$sid = ACCOUNT_SID;
		$token = AUTH_TOKEN;
		try {
			$twilio = new Client($sid, $token);
		} catch (\Twilio\Exceptions\ConfigurationException $e) {
		}
		$response = new VoiceResponse();
	}

	public function xml($speech)
	{
		$response = new VoiceResponse();
		$response->say($speech);
		$response->gather(array('numDigits' => 1, 'input' => 'dtmf', 'timeout' => 5, 'action' => 'google.com'));
		$response->say(TIME_OUT_MSG);
	}
}

