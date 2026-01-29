<?php
	$g_is_remote 		= true;
	$g_skip_linenotify 	= false;
	if ($g_is_remote) {
    	include_once "/var/www/html/jtgcharge/common/entry.php";
	} else {
    	include_once "./../common/entry.php";
	}
	
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

	function sendLineNotify($message, $token) {
		$url = 'https://notify-api.line.me/api/notify';
		$data = array('message' => $message);
		$headers = array(
			'Content-Type: application/x-www-form-urlencoded',
			'Authorization: Bearer ' . $token
		);

		$options = array(
			'http' => array(
				'method' => 'POST',
				'header' => implode("\r\n", $headers),
				'content' => http_build_query($data)
			)
		);

		$context = stream_context_create($options);
		$result = file_get_contents($url, false, $context);

		if ($result === FALSE) {
			echo "Failed to send message.";
		} else {
			echo "Message sent successfully.";
		}
	}
	function sendJtgLineNotify($message, $group_id) {
		$sso_token = "zsv8v+67K1wEnpkmkSHl0KpipsP7eWIgCAMcTHNf5QzuxGztD3ceoCXNtZKes3Dtndlf29gmZFUfXo95YkL5S9asYyeFdrt2fRzXvFm0o+6qTowRObD7hKxiQkAMDjCx";
		$url = 'https://miaoparking.jotangi.net/jtgmsgnotify/api/JTG_notify.php';
		
		// 替換所有的換行符號
		$new_message = str_replace(array("\r\n", "\n"), "<br>", $message);
		// echo "new_message :$new_message\n";
		$error = "";
		$param["sso_token"			] = $sso_token;
		$param["conferenceroom_sid"	] = $group_id;
		$param["content"			] = $new_message;
		$msg_ret    = jackyCallAPI($error, $url, $param, "POST");
		// echo $msg_ret;
		$json_msg = json_decode($msg_ret);
		if ($json_msg->status == "true") {
			if ($json_msg->code == "0x0200") {
				echo "Message sent successfully.";
			} else {
				echo "Failed to send message.";
			}
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
	
	try {
		$member_id = "JTG_CHARGE";
		$remote_ip = "localhost";
		$db = new CXDB($remote_ip);
		$conn_res = $db->connect($link, $member_id, "");
		$section = "竹東"; // 竹東：縣民廣場平面停車場	苗栗：自治路充電站	南投：停五停車場
		if ($conn_res["status"] == "true") {
			$sql = "SELECT * FROM `log_history4user` WHERE DATE(stop_time)='$pre_date' AND station_name LIKE '$section%' AND stop_reason != '---' AND stop_reason != '' ORDER BY create_date ASC;";
			private_wh_log("sql :$sql");
			$charge_count = 0; $total_amount = 0; $total_kwh = 0.0; $calc_seconds = 0; $paied_count = 0; $paied = 0; $unPay_count = 0; $unPay = 0;
			$nid = 0;
			$message .= "\n統計日期：$pre_date\n";
			if ($result = mysqli_query($link, $sql)) {
				if (mysqli_num_rows($result) > 0) {
					while($row = mysqli_fetch_array($result)) {
						$charge_count++; // 充電車次
						$calc_seconds += getTwoDateTimeSeconds($row['start_time'], $row['stop_time']); // 總充電時間
						$total_kwh 	  += doubleval($row['kwh']); // 度數
						$total_amount += intval($row['price']); // 營業額
						if ($row['jtg_pay_status'] == "1") {
							$paied_count++;
							$paied += intval($row['price']);
						} else {
							$unPay_count++;
							$unPay += intval($row['price']);
						}
						
						$area = $row['station_name'];
						if (!$had_msg) {
							$message .= "\n場域 :$area\n";
							$had_msg = true;
						}
					}
					$dest_time = secondsToTime($calc_seconds);
					$message .= "\n充電車次 :$charge_count";
					$message .= "\n總充電時間 :$dest_time";
					$message .= "\n使用度數 :$total_kwh";
					$message .= "\n\n營業額 :$total_amount 元";
					$message .= "\n\n已收款項 :$paied_count 筆, 合計 $paied 元";
					$message .= "\n待收款項 :$unPay_count 筆, 合計 $unPay 元";
				}
			}
			private_wh_log("message :$message");

			if (!$g_skip_linenotify) {
				if ($had_msg) {
					sendLineNotify($msg_title.$message, $line_bills_token);
					sendJtgLineNotify($msg_title.$message, $jtg_bills_token_group_id);
				}
				if ($had_msg) {
					sendLineNotify($msg_title.$message, $line_charge_bills_token);
					sendJtgLineNotify($msg_title.$message, $jtg_charge_bills_token_group_id);
				}
			}

			
			$g_sso_token = "9Dcl8uXVFt/vSYaizaE+KkAgXtYO0807";
			$input_param = []; $error = ""; $query_rows = [];
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
					WHERE start_time IS NOT NULL AND stop_time IS NOT NULL AND DATE(stop_time)='$pre_date' AND station_name LIKE '%$section%' AND stop_reason != '---' AND stop_reason != '' 
					GROUP BY DATE(create_date), jtg_pay_status, station_name, charger_name, gun_name
					ORDER BY bill_date ASC;";
				// echo $sql."\n";
			if ($result = mysqli_query($link, $sql)) {
				// echo "into\n";
				if (mysqli_num_rows($result) > 0) {
					$input_param['sso_token'] = $g_sso_token;
					while($row = mysqli_fetch_assoc($result)) {
                        array_push($query_rows, $row);
					}
					$input_param['charge_data'] = $query_rows;
					$json_data = json_encode($input_param);
					// echo $json_data."\n";
					$msg_ret   = jackyCallAPI($error, "https://miaoparking.jotangi.net/jtgmonitor4zhudong/api/JTG_chargeAccount.php", $json_data, "POST");
				} else {
					$query_rows[0]['bill_date'				] = $pre_date;
					$query_rows[0]['jtg_pay_status'			] = "1";
					$query_rows[0]['station_name'			] = $section;
					$query_rows[0]['charger_name'			] = '充電車專用車位1';
					$query_rows[0]['gun_name'				] = "自動";
					$query_rows[0]['total_charge_duration'	] = '00:00:00';
					$query_rows[0]['total_kwh'				] = '0';
					$query_rows[0]['total_price'			] = '0';
					$query_rows[0]['record_count'			] = '0';
					$input_param['sso_token'] = $g_sso_token;
					$input_param['charge_data'] = $query_rows;
					$json_data = json_encode($input_param);
					// echo "json_data: $json_data\n";
					$msg_ret   = jackyCallAPI($error, "https://miaoparking.jotangi.net/jtgmonitor4zhudong/api/JTG_chargeAccount.php", $json_data, "POST");
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