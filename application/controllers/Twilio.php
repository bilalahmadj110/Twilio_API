<?php
defined('BASEPATH') or exit('No direct script access allowed');
require_once 'vendor\autoload.php';

use Twilio\Rest\Client;
use Twilio\TwiML\VoiceResponse;


class Twilio extends CI_Controller
{

	public function __construct()
	{
		parent::__construct();
		$this->load->model('twilio_model');
	}

	final public function send()
	{
		/**
		 * Required parameters (GET method)
		 *  1. from phone number  (from twilio)
		 *  2. to number (client number) --  may contain list ["Phone1", "Phone2", "Phone3", ..., "Phone n"]
		 *  3. speech msg
		 */
		$from = $this->input->get("from", true);
		$to = $this->input->get("to", true);
		$msg = $this->input->get("msg", true);

		if (isset($from, $to, $msg)) {
			$phone_list = validate_json($to);
			$total = count($phone_list);
			if ($total === 0) {
				send_response(INVALID_PARAMS);
			}
			validate_phone($to);
			validate_string($msg);
			$resp = SUCCESS_WITH_RESPONSE;
			foreach ($phone_list as $ph) {
				validate_phone($ph);
				try {
					$call_sid = $this->call($from, $ph, $this->xml($msg));
				} catch (Exception $e) {
					$call_sid = "";
				}
				if ($call_sid) {
					$return_ids = $this->twilio_model->add($from, $ph, $msg, $call_sid, 'PENDING');
					$resp['ids'][] = array('id' => $return_ids, 'sid' => $call_sid);
				} else {
					$return_ids = $this->twilio_model->add($from, $ph, $msg, $call_sid, 'FAILED');
					$resp['ids'][] = array('id' => $return_ids, 'sid' => $call_sid);
				}
			}

			$resp['response'] = "Total $total number(s) have been added for calling";

			send_response($resp);
		} else {
			send_response(INVALID_PARAMS);
		}
	}

	final public function get()
	{
		/**
		 * Required parameters (GET method)
		 *  1. Start value (int)
		 *  2. End value (int)
		 */
		$start = $this->input->get('start');
		$end = $this->input->get('end');
		if (isset($start, $end)) {
			validate_integer($start, $end);
			$resp = SUCCESS_WITH_DATA;
			$resp = array_merge($this->twilio_model->get((int)$start, (int)$end), $resp);
			send_response($resp);

		} else {
			send_response(INVALID_PARAMS);
		}
	}

	final private function call($from, $to, $xml)
	{
		$full_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . '://' . $_SERVER['SERVER_NAME']
			. '/asad/code/index.php/twilio/complete';
		$sid = ACCOUNT_SID;
		$token = AUTH_TOKEN;
		$twilio = new Client($sid, $token);
		$call = $twilio->calls->create($to, // to
			$from, // from
			[
				"statusCallback" => $full_url,
				"statusCallbackEvent" => ["completed"],
				"statusCallbackMethod" => "GET",
				"twiml" => $xml
			]
		);
		return $call->sid;
	}

	final private function xml(string $msg)
	{
		$full_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . '://' . $_SERVER['SERVER_NAME']
			. '/asad/code/index.php/twilio/callback';
		$response = new VoiceResponse();
		$gather = $response->gather(
			array('numDigits' => 1,
				'input' => 'dtmf',
				'action' => $full_url,
				'method' => 'GET',
				'actionOnEmptyResult' => true,
				'timeout' => 5));
		$gather->say($msg,
			['voice' => 'man', 'loop' => 1]);
		$response->say('Sorry, we didn\'t receive any response, Good bye!', ['voice' => 'man']);
		return $response;
	}

	final public function callback()
	{
		$callSid = $this->input->get('CallSid', true);
		$from = $this->input->get('From', true);
		$to = $this->input->get('To', true);
		$digits = $this->input->get('Digits', true);

		if (isset($callSid, $from, $to, $digits)) {
			$response = new VoiceResponse();
			if ($digits == '1') {
				$response->say('Thank you, we received you response. you pressed ' . ($digits ?: 'nothing.') . 'Good bye!',
					['voice' => 'man']);
			} else {
				$response->say('You denied this confirmation. Good bye!', ['voice' => 'man']);
			}
			$this->twilio_model->put_response($callSid, $digits);

			header('Content-Type: text/xml');
			echo $response;
		} else {
			echo "NOT VALID. It might have been spoofed!";
		}
	}

	final public function complete()
	{
		$callSid = $this->input->get('CallSid', true);
		$callStatus = $this->input->get('CallStatus', true);
		if (isset($callSid, $callStatus)) {
			$this->twilio_model->put_complete($callSid, strtoupper($callStatus));
		} else {
			echo "NOT VALID. It might have been spoofed!";
		}
	}

}
