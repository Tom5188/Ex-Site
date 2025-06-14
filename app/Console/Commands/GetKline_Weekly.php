<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
defined('ACCOUNT_ID') || define('ACCOUNT_ID', '50154012');
defined('ACCESS_KEY') || define('ACCESS_KEY', 'c96392eb-b7c57373-f646c2ef-25a14');
defined('SECRET_KEY') || define('SECRET_KEY', '');
class GetKline_Weekly extends Command
{
	protected $signature = "get_kline_data_weekly";
	protected $description = "获取K线图数据";
	private $url = "https://api.huobi.br.com";
	private $api = "";
	public $api_method = "";
	public $req_method = "";
	public function __construct()
	{
		parent::__construct();
	}
	public function handle()
	{
		$all = DB::table('currency')->where('is_display', '1')->get();
		$all_arr = $this->object2array($all);
		$legal = DB::table('currency')->where('is_display', '1')->where('is_legal', '1')->get();
		$legal_arr = $this->object2array($legal);
		$ar = [];
		foreach ($legal_arr as $legal) {
			foreach ($all_arr as $item) {
				if ($legal['id'] != $item['id']) {
					$ar_a = [];
					$ar_a['name'] = strtolower($item['name']) . strtolower($legal['name']);
					$ar_a['currency_id'] = $item['id'];
					$ar_a['legal_id'] = $legal['id'];
					$ar[] = $ar_a;
				}
			}
		}
		$kko = json_decode($this->curl('https://api.huobi.br.com/v1/common/symbols'), TRUE);
		if ($kko['status'] == 'ok') {
			$trade = [];
			foreach ($kko['data'] as $key => $value) {
				$trade[] = $value['symbol'];
			}
			foreach ($ar as $it) {
				if (in_array($it['name'], $trade)) {
					$data = array();
					$data = $this->get_history_kline($it['name'], '1week', 1);
					if ($data['status'] == 'ok') {
						$info = $data['data'][0];
						$insert_instance = DB::table('market_hour')->where('currency_id', $it['currency_id'])->where('legal_id', $it['legal_id'])->where('day_time', '=', $info['id'])->where('period', '1week')->where('sign', 2)->where('type', 8)->first();
						if (!empty($insert_instance)) {
							continue 1;
						}
						$insert_Data = array();
						$insert_Data['currency_id'] = $it['currency_id'];
						$insert_Data['legal_id'] = $it['legal_id'];
						$insert_Data['start_price'] = $this->sctonum($info['open']);
						$insert_Data['end_price'] = $this->sctonum($info['close']);
						$insert_Data['mminimum'] = $this->sctonum($info['low']);
						$insert_Data['highest'] = $this->sctonum($info['high']);
						$insert_Data['type'] = 8;
						$insert_Data['sign'] = 2;
						$insert_Data['day_time'] = $info['id'];
						$insert_Data['period'] = '1week';
						$insert_Data['number'] = bcmul($info['amount'], 1, 5);
						$insert_Data['mar_id'] = $info['id'];
						DB::table('market_hour')->insert($insert_Data);
					}
				}
			}
		}
	}
	public function object2array($obj)
	{
		return json_decode(json_encode($obj), true);
	}
	public function sctonum($num, $double = 8)
	{
		if (false !== stripos($num, "e")) {
			$a = explode("e", strtolower($num));
			return bcmul($a[0], bcpow(10, $a[1], $double), $double);
		} else {
			return $num;
		}
	}
	public function get_history_kline($symbol = '', $period = '', $size = 0)
	{
		$this->api_method = "/market/history/kline";
		$this->req_method = 'GET';
		$param = ['symbol' => $symbol, 'period' => $period];
		if ($size) {
			$param['size'] = $size;
		}
		$url = $this->create_sign_url($param);
		return json_decode($this->curl($url), TRUE);
	}
	public function create_sign_url($append_param = [])
	{
		$param = ['AccessKeyId' => ACCESS_KEY, 'SignatureMethod' => 'HmacSHA256', 'SignatureVersion' => 2, 'Timestamp' => date('Y-m-d\\TH:i:s', time())];
		if ($append_param) {
			foreach ($append_param as $k => $ap) {
				$param[$k] = $ap;
			}
		}
		return $this->url . $this->api_method . '?' . $this->bind_param($param);
	}
	function bind_param($param)
	{
		$u = [];
		$sort_rank = [];
		foreach ($param as $k => $v) {
			$u[] = $k . "=" . urlencode($v);
			$sort_rank[] = ord($k);
		}
		asort($u);
		$u[] = "Signature=" . urlencode($this->create_sig($u));
		return implode('&', $u);
	}
	function create_sig($param)
	{
		$sign_param_1 = $this->req_method . "\r\n" . $this->api . "\r\n" . $this->api_method . "\r\n" . implode('&', $param);
		$signature = hash_hmac('sha256', $sign_param_1, SECRET_KEY, true);
		return base64_encode($signature);
	}
	public function curl($url, $postdata = [])
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		if ($this->req_method == 'POST') {
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postdata));
		}
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_TIMEOUT, 60);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
		curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
		$output = curl_exec($ch);
		$info = curl_getinfo($ch);
		curl_close($ch);
		return $output;
	}
}