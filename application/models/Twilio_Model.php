<?php

class Twilio_Model extends CI_Model
{

	public function __construct()
	{
		$this->load->database();

	}

	final public function add(string $from, string $to, string $msg, string $sid, string $status): int
	{
		$data = array(
			TABLE_REQUEST_FROM_PHONE => $from,
			TABLE_REQUEST_TO_NUM => $to,
			TABLE_REQUEST_MSG => $msg,
			TABLE_REQUEST_SID => $sid,
			TABLE_REQUEST_STATUS => $status
		);
		$this->db->insert(TABLE_REQUEST, $data);
		// Returning insert ID for later reference/request
		return $this->db->insert_id();
	}

	final public function put_response(string $sid, string $resp)
	{
		$whr = array(TABLE_REQUEST_SID => $sid);
		$this->db->where($whr);
		$this->db->update(TABLE_REQUEST, array(TABLE_REQUEST_RESPONSE => $resp));
	}

	final public function put_complete(string $sid, string $status)
	{
		$whr = array(TABLE_REQUEST_SID => $sid);
		$this->db->where($whr);
		$this->db->update(TABLE_REQUEST, array(TABLE_REQUEST_STATUS => $status));
	}

	final public function get(int $start, int $end): array
	{
		$this->db->order_by(TABLE_REQUEST_ID, 'DESC');
		$this->db->limit($end, $start);
		$results = $this->db->get(TABLE_REQUEST)->result();
		foreach ($results as $r) {
			$r->id = (int)$r->id;
			$r->response = $r->response ?: "";
			$r->status = $r->status ?: "";
			$r->sid = $r->sid ?: "";
		}
		$this->db->reset_query();
		$total = $this->db->count_all_results(TABLE_REQUEST);
		return array('total' => $total, 'results' => $results);
	}

}
