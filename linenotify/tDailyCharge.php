<?php
	$g_is_remote 		= true;
	$g_skip_linenotify 	= false;
	if ($g_is_remote) {
    	include_once "/var/www/html/jtgcharge/common/entry.php";
	} else {
    	include_once "./../common/entry.php";
	}
	$g_sso_token = "9Dcl8uXVFt/vSYaizaE+KkAgXtYO0807";

	// 01 00 * * * sudo php /var/www/html/jtgcharge/linenotify/dailyBills.php
	$parkingId = 0;
	$msg_title = "\n充電樁帳務資訊";
	$line_bills_token 		 = "WJXAB2cFhnjaguszBarsAIlsGPYbpGqpKaaP8xah9aE";
	$line_charge_bills_token = "bhwFxclmpD1YkzE1uFm3hgJZgprUkkLwvht3cMzg4vr";

	$jtg_bills_token_group_id = "96190eb97e";
	$jtg_charge_bills_token_group_id = "341632e8c6";
	function private_wh_log($log_msg)
	{
		global $g_is_remote;
		if ($g_is_remote) {
			// remote
			$log_filename = "/var/www/html/jtgcharge/log/";
		} else {
			// localhost
			$log_filename = $_SERVER["DOCUMENT_ROOT"]."/jtgcharge/locallog/routine";
		}
		
		if (!file_exists($log_filename)) {
			// create directory/folder uploads.
			mkdir($log_filename, 0777, true);
		}
		$log_file_data = $log_filename.'dailyBills_'. date('Ymd').'.log';

		// if you don't add `FILE_APPEND`, the file will be erased each time you add a log
		if ($g_is_remote) {
			// remote
			echo date("Y-m-d H:i:s")."  ------  ".$log_msg . "\n";
		} else {
			// localhost
			echo date("Y-m-d H:i:s")."  ------  ".$log_msg . "<br>";
		}
	}
	function jackyCallAPI(&$error, $url, $data, $method="GET", $content_type ='application/json', $usedefault_header=false, $header=null)
	{
		// echo $content_type."\n";
		$curl = curl_init();
		switch ($method)
		{
			case "POST":
				curl_setopt($curl, CURLOPT_POST, true);

				if (is_array($data)) {
					// echo "CURLOPT_POSTFIELDS 01\n";
					curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
				} else {
					// echo "CURLOPT_POSTFIELDS 02\n";
					curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
				}

				if ($header != null)
					curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
				break;

			case "GET":
				if (is_array($data))
					$url = sprintf("%s?%s", $url, http_build_query($data));
				else
					$url = sprintf("%s?%s", $url, $data);
				if($header != null) {
					// echo "header != null\n";
					curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
				}
				break;

			case "PUT":
				curl_setopt($curl, CURLOPT_PUT, true);
				break;

			default:
				if ($data)
					$url = sprintf("%s?%s", $url, http_build_query($data));
		}
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		if ($usedefault_header) {
			if ($content_type == "text/xml") {
				$header = array('Content-Type: '.$content_type);
			} else {
				$header = array(
					'Content-Type: '.$content_type,
					'Content-Length: '.strlen($data));
			}
			curl_setopt($curl, CURLINFO_HEADER_OUT, true);
			curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
		}

		$response = curl_exec($curl);
		$error = curl_error($curl);
		curl_close($curl);

		// echo "error :";
		// echo $error."\n";
		return $response;
	}


// Entry
	$message  = "";
	$had_msg  = false;
	$day	  = strftime("%Y-%m-%d", time());
	$pre_date = date('Y-m-d', strtotime($day." -1 days"));
	$query_rows = [];
	try {
		$member_id = "JTG_CHARGE";
		$remote_ip = "localhost";
		$db = new CXDB($remote_ip);
		$conn_res = $db->connect($link, $member_id, "");
		if ($conn_res["status"] == "true") {
			$input_param = []; $error = "";
			$sql = "SELECT 
						DATE(create_date) AS bill_date, 
						jtg_pay_status, 
						station_name, 
						charger_name, 
						gun_name, 
						SEC_TO_TIME(SUM(TIMESTAMPDIFF(SECOND, start_time, stop_time))) AS total_charge_duration,
						SUM(kwh) AS total_kwh, 
						SUM(price) AS total_price, 
						COUNT(*) AS record_count
					FROM log_history4user
					WHERE start_time IS NOT NULL AND stop_time IS NOT NULL AND DATE(stop_time)<='$pre_date' AND station_name LIKE '%南投%' 
					GROUP BY DATE(create_date), jtg_pay_status, station_name, charger_name, gun_name
					ORDER BY bill_date ASC;";
				// echo $sql."\n";
			if ($result = mysqli_query($link, $sql)) {
				if (mysqli_num_rows($result) > 0) {
					$input_param['sso_token'] = $g_sso_token;
					while($row = mysqli_fetch_assoc($result)) {
                        array_push($query_rows, $row);
					}
					$input_param['charge_data'] = $query_rows;
					$json_data = json_encode($input_param);
					// echo $json_data."\n";
					$msg_ret   = jackyCallAPI($error, "https://miaoparking.jotangi.net/jtgmonitor4nantou/api/JTG_chargeAccount.php", $json_data, "POST");
					echo $msg_ret."\n";
				}
			}
		} else {
			private_wh_log("connect to mysql error ");
		}
	} catch (Exception $e) {
		private_wh_log("Exception error! error detail:".$e->getMessage());
	} finally {
		$data_close_conn = close_connection_finally($link, $remote_ip, $member_id);
		if ($data_close_conn["status"] == "false") $data = $data_close_conn;
		private_wh_log("finally close database!");
	}
?>