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
	$msg_title = "\n偵測車位充電資訊";

	$jtg_parking_chage_group_id = "c8cfc424b5";
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
	$dt_now = get24HourFormat('');
	
	try {
		$member_id = "JTG_PKCHARGE";
		$remote_ip = "localhost";
		$db = new CXDB($remote_ip);
		$conn_res = $db->connect($link, $member_id, "");
		$section = "南投"; // 竹東：縣民廣場平面停車場	苗栗：自治路充電站	南投：停五停車場
		if ($conn_res["status"] == "true") {
			$sql = "SELECT * FROM `v_parking_charge_today` WHERE station_name LIKE '%$section%' ORDER BY park_code ASC, park_cell ASC;";
			private_wh_log("sql :$sql");
			$msg_title .= "\n偵測時間：$dt_now\n";
			if ($result = mysqli_query($link, $sql)) {
				if (mysqli_num_rows($result) > 0) {
					while($row = mysqli_fetch_array($result)) {
						$station_name = $row['station_name'];
						$park_code = $row['park_code'];
						$park_cell = $row['park_cell'];
						$enterPlateNum = $row['enterPlateNum'];
						$enterTime = $row['enterTime'];
						$parkSpaceCode = sprintf("%05d", $park_cell);
						
						$order_no = getChargecellOrderNo('N', 'T', $enterTime, $park_code, $parkSpaceCode, $ret_msg);
						echo "order_no :$order_no, enterTime :$enterTime\n";
						
						$in_use_exitingImage = 0;
            			$sql_moc = "SELECT * FROM order_chargecell WHERE order_no='$order_no'";
						if ($result_moc = mysqli_query($link, $sql_moc)) {
							if (mysqli_num_rows($result_moc) > 0) {
								if ($row_moc = mysqli_fetch_array($result_moc)) {
									$in_use_exitingImage = $row_moc['in_use_exitingImage'];
								}
							}
						}

						if ($row['occupy'] == 1 || (strlen($row['exitingImage']) > 0 && strlen($in_use_exitingImage) == 0)) {
							$message .= "\n[$station_name] $park_code - $park_cell ($enterPlateNum) 車子在位";

							if (condictionCore($message, $enterPlateNum, $row)) {
								// 佔位處理
								$res = "";
								$enteredImage = copyCarPlateImage($res, $row['enteredImage']);
								private_wh_log("[進位] copyCarPlateImage :$res");
								$res = "";
								$exitingImage = copyCarPlateImage($res, $row['exitingImage']);
								private_wh_log("[離位] copyCarPlateImage :$res");
								echo "enteredImage :$enteredImage, exitingImage :$exitingImage\n";
								$input = refreshChargeCellInfo($row, $enteredImage, $exitingImage);
								
								$effect_row = $db->modifyOrderChargeCell($link, $remote_ip, $member_id, $order_no, $input, $func, $ret_msg);
							}
							$message .= "\n---------------------------------------------------";
						} else {
							echo "\n車位目前無車";
						}
					}
				}
			}
			private_wh_log("message :$msg_title $message");

			if (!$g_skip_linenotify) {
				if (strlen($message) > 0) {
					// 判斷現在分鐘是否為 5 的倍數
					$cur_min = intval(date('i')); // 取得分鐘 (00 ~ 59)
					if ($cur_min % 5 == 0) {
						sendJtgLineNotify($msg_title.$message, $jtg_parking_chage_group_id);
					} else {
						// private_wh_log("Skip LINE Notify (not 5-min interval)");
					}
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