<?php
	/* PHP Excel section - start */
	// error_reporting(E_ALL); /** Error reporting */
	// ini_set('display_errors', TRUE);
	// ini_set('display_startup_errors', TRUE);
	// define('EOL',(PHP_SAPI == 'cli') ? PHP_EOL : '<br />');
	/*********************************************************/
	/*                                                       */
	/* 		PHPExcel component: php read excel file 		 */
	/*                                                       */
	/*********************************************************/
	// if (!empty($dst_filename)) {
	// 	try {
	// 		$objPHPExcel = PHPExcel_IOFactory::load($g_xlsx_in_path.$dst_filename);
	// 		$worksheet = $objPHPExcel->getActiveSheet();
	// 		foreach ($worksheet->getRowIterator() as $row) {
	// 			$cellIterator = $row->getCellIterator();
	// 			$cellIterator->setIterateOnlyExistingCells(false); // Loop through all cells, even if they're empty
	// 			foreach ($cellIterator as $cell) {
	// 				$value .= ','.$cell->getCalculatedValue(); // Get the value of the cell
	// 			}
	// 			$value .= '\n';
	// 		}
	// 		$data = result_message("true", "0x0200", $ret_str.'<br>解析Excel檔案 ['.$file_name.'] 成功', $value);
	// 	} catch(Exception $ex) {
	// 		$succeed_flag = false;
	// 		$data = result_message("false", "0x0207", $ret_str.'<br>解析Excel檔案 ['.$file_name.'] 異常<br>'." :", $ex->getMessage());
	// 	}
	// }
	/* PHP Excel section - end */
	// require 'utils/vendor/autoload.php';
    include("define.php");
	include("log.php");
	include("db_tools.php");
	include("funcCore.php");
	include("api_core.php");
    include("accessdb.php");
    include("StatusCode.php");
    include("bearer_token.php");
    include("ocpi_base.php");
    
	function getDailyInfo(&$visible, $date_s, $date_e, $area = '竹東', $member_id = "JTG_CHARGE UI") {
		$ret = '';
		try {
			$remote_ip = get_remote_ip();
			$db = new CXDB($remote_ip);
			$conn_res = $db->connect($link, $member_id, "");
			if ($conn_res["status"] == "true") {
				$sql = "SELECT * FROM `log_history4user` WHERE DATE(start_time)>='$date_s' AND DATE(start_time)<='$date_e' AND station_name LIKE '$area%' AND avalible = 1 ORDER BY start_time ASC;";
				$charge_count = 0; $total_amount = 0; $total_kwh = 0.0; $calc_seconds = 0; $paied_count = 0; $paied = 0; $unPay_count = 0; $unPay = 0;
				$nid = 0;
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
							
							$area_name = $row['station_name'];
						}
						$dest_time = secondsToTime($calc_seconds);
						
						$flagZero = true;
						$ret = "<table>";
						// if (intval($cur_info['remain_rent_count']) > 0) $flagZero = false;
						$ret .= "<tr>";
						$ret .= '<td><span style="color:black; font-size:18px">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;場域：'.$area_name.'</span></td>';
						$ret .= '<td><span style="color:black; font-size:18px">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;統計日期：'.$date_s." ~ ".$date_e.'</span></td>';
						$ret .= '<td><span style="color:red; font-size:16px">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;充電車次 : <span style="color:red; font-size:24px">'.$charge_count.'</span></span></td>';
						$ret .= "</tr>";
						$ret .= "</table><br>";
						$ret .= "<table>";
						$ret .= "<tr>";
						$ret .= '<td><span style="color:blue; font-size:16px">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;總充電時間 : '.$dest_time.'</span></td>';
						$ret .= '<td><span style="color:green; font-size:16px">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;使用度數 : '.$total_kwh.'</span></td>';
						$ret .= '<td><span style="color:red; font-size:16px">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;營業額 : '.$total_amount.'</span></td>';
						$ret .= '<td><span style="color:green; font-size:16px">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;已付款 : '.$paied_count.'筆, 金額'.$paied.'元</span></td>';
						$ret .= '<td><span style="color:gray; font-size:16px">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;待付款 : '.$unPay_count.'筆, 金額'.$unPay.'元</span></td>';
						$ret .= "</tr>";
						$ret .= "</table><br><br>";
						$visible = ($flagZero) ? "display:none;" : "";
					}
				}
			} else {
			}
		} catch (Exception $e) {
		} finally {
			$data_close_conn = close_connection_finally($link, $remote_ip, $member_id);
		}
		return $ret;
	}
	function getOrderInStop($func, $remote_ip, $member_id, $token, $link, $db)
	{
		global $g_3party_url, $default_3party_history;
		global $g_save2db;
		
		// entry
		$error = "";
		$array_json_main = ['charge_history','charge_point_id','charge_time','charger_name','gun_name','header_type','kwh','month','order_id','order_serial_number','pay_status','price','start_soc','start_time','station_id','station_name','stop_reason','stop_soc','stop_time'];
		$array_json_sub  = ['start_time','end_time','fee','kwh','price'];
		
		$url = $g_3party_url."/get_charge_history";
		$today = getDateFormat4("", 'Y-m-d');
		$data = array(
			'count_one_time' => 9999,
			'skip_docs' => 0,
			'start_date' => $today,
			'end_date' => $today,
			'select_all' => false
		);
		$post_data = json_encode($data);
		
		// echo $url." -> ".$post_data;
		$result = callAPI($error, $url, $post_data, "POST", false, getZDBearerToken($token));
		// echo $result;
		if (!empty($error)) {
			$res = result_message("false", "0x020E", "API return :$error", $result);
			JTG_wh_log($remote_ip, "$func API return :$error", $member_id);
			echo (json_encode($res, JSON_UNESCAPED_UNICODE));
			exit;
		}

		// ------------------------------------------------------------------------
		JTG_wh_log($remote_ip, "$func 取得使用者充電歷史紀錄 :$result", $member_id);
		$obj = json_decode($result, true);
		$obj_data 	= isset($obj["data"		]) ? $obj["data"	 ] : "";
		$status 	= isset($obj["status"	]) ? $obj["status"	 ] : 0;
		$code 		= isset($obj_data["code"]) ? $obj_data["code"] : 400;

		if ($status == 1 && intval($code) == 200) { // 成功
			// {
			// 		"code": 200,
			// 		"doc_num": 200,
			// 		"history_list": [
			// 		  {
			// 			"charge_history": [
			// 			  {
			// 				"end_time": "2024-01-01 00:00:00",
			// 				"fee": 10,
			// 				"kwh": 20,
			// 				"price": 10,
			// 				"start_time": "2024-01-01 00:00:00"
			// 			  }
			// 			],
			// 			"charge_time": "3小時21分",
			// 			"charger_name": "充電樁",
			// 			"gun_name": "槍位1",
			// 			"header_type": "CCS1",
			// 			"kwh": 10,
			// 			"month": "2024-01",
			// 			"order_serial_number": "TP_0001_20240329_0001",
			// 			"price": 100,
			// 			"start_soc": 20,
			// 			"start_time": "2024-01-01 00:00:00",
			// 			"station_name": "充電站",
			// 			"stop_reason": "APP結束交易",
			// 			"stop_soc": 80,
			// 			"stop_time": "2024-01-01 00:00:00"
			// 		  }
			// 		]
			// }
			$history_list = isset($obj_data["history_list"]) ? $obj_data["history_list"] : [];
			if (is_null($history_list)) {
				$res = result_message("true", "0x0204", "(Null)查無資料", []);
				echo (json_encode($res, JSON_UNESCAPED_UNICODE));
				exit;
			}

			$history_count = count($history_list);
			if ($history_count == 0) {
				$res = result_message("true", "0x0204", "查無資料", []);
				echo (json_encode($res, JSON_UNESCAPED_UNICODE));
				exit;
			}

			$ret_msg = "";
			$msg = "$func 該 站點 共 $history_count 筆充電槍資料";
			// echo $msg."\n";
			for ($i = 0; $i < $history_count; $i++) {
				$curdata 		= $history_list[$i];
				$data_input 	= [];
				$data_input['json_str'] = json_encode($result, true);
				$order_serial_number  	= $curdata['order_serial_number'];

				if ($g_save2db) {
					$charge_history = [];
					// 紀錄至資料庫
					// 使用者充電子紀錄-主檔
					$table = 'log_history4user';
					$sid = getSid($table, $member_id);
					
					for ($m = 0; $m < count($array_json_main); $m++) {
						$field_name = $array_json_main[$m];
						if ($field_name != "charge_history")
							$data_input[$field_name] = $curdata[$field_name];
						else
							$charge_history = isset($curdata["charge_history"]) ? $curdata["charge_history"] : null;
						// echo "field_name :".$field_name.", data_input :".$data_input[$field_name]."\n";
					}
					if ($db->modifyLogHistory4User($link, $sid, $remote_ip, $member_id, $data_input, $func, $ret_msg) > 0) {

						// echo "!is_null(modifyLogHistory4User)\n";
						// 使用者充電子紀錄-副檔
						$table = 'log_subhistory4user';
						if (!is_null($charge_history)) {
							// echo "!is_null(charge_history)\n";
							for ($n = 0; $n < count($charge_history); $n++) {
								$curdata2 = $charge_history[$n];

								$sub_input = [];
								$sub_input["parent_sid"] = $sid;
								$sub_input["order_serial_number"] = $curdata['order_serial_number'];
								$sub_input["order_id"			] = $curdata['order_id'			  ];
								$sub_input['json_str'] = json_encode($result, true);
								for ($t = 0; $t < count($array_json_sub); $t++) {
									$sub_sid = getSid($table, $member_id);

									$field_name = $array_json_sub[$t];
									$sub_input[$field_name] = $curdata2[$field_name];
									// echo "field_name :".$field_name.", data_input :".$sub_input[$field_name]."\n";

									if ($db->modifySub4LogHistory4User($link, $sub_sid, $remote_ip, $member_id, $sub_input, $func, $ret_msg) > 0) {
									}
								}
							}
						}
						
						$res = result_message("true", "0x0200", "succeed", []);
					} else {
						$res = result_message("false", "0x0205", "sql error :".$ret_msg, []);
					}
					
				}
			}
			JTG_wh_log($remote_ip, $msg, $member_id);
			if ($res['status_code'] == "0x0200") {
				$res = result_message("true", "0x0200", "succeed", $history_list);
			}

		} else { // 異常
			// {
			// 		"data": {
			// 		  "code": 401,
			// 		  "message": "Not Authorization."
			// 		},
			// 		"status": true
			// }
			$data 	= isset($obj["data"	 ]) ? $obj["data"  ] : "";
			$status = isset($obj["status"]) ? $obj["status"] : "";
			
			if (!empty($data)) {
				$code = isset($data["code"]) ? $data["code"] : "";
				$message = isset($data["message"]) ? $data["message"] : "";

				$res = result_message("false", "0x020E", "API return error json data :($code)$message", $obj);
			} else if (!empty($status)) {
				$res = result_message("false", "0x020E", "API return status :$status", $obj);
			} else {
				$res = result_message("false", "0x020E", "API return :未知錯誤", $obj);
			}
		}
		return $history_list;
	}
	function chkChargeState($func, $control_id, $token, $remote_ip, $member_id)
	{
		global $g_3party_url;
		
		// entry
		$error = "";
		$iRet = 0; // (0:未處理，1:已處理,成功，2:已處理, 失敗, 3:異常).

		$url = $g_3party_url."/check_control_response";
		$data = array(
			"control_id" => $control_id
		);
		$post_data = json_encode($data);

		$result = callAPI($error, $url, $post_data, "POST", false, getZDBearerToken($token));
		// echo "result :".$result."\n";
		
		if (!empty($error)) {
			$res = result_message("false", "0x020E", "API return :$error", "");
			JTG_wh_log($remote_ip, "API return :$error", $member_id);
			echo (json_encode($res, JSON_UNESCAPED_UNICODE));
			exit;
		}

		// ------------------------------------------------------------------------
		JTG_wh_log($remote_ip, "$func 檢查充電桩指令結果 :$result", $member_id);
		$obj 		= json_decode($result, true);
		$obj_data 	= isset($obj["data"]) ? $obj["data"] : "";
		$status 	= isset($obj["status"]) ? $obj["status"] : 0;
		$code 		= isset($obj_data["code"]) ? $obj_data["code"] : 400;

		if (!empty($code) && intval($code) == 200) { // 成功
			// {
			// 	"code": 200,
			// 	"control_result": {
			// 	  "check": 0,
			// 	  "error_log": "下達失敗",
			// 	  "order_flag": 0,
			// 	  "order_id": "661c8a2b9c8a94322f5c36d9"
			// }
			$control_result = isset($obj_data["control_result"]) ? $obj_data["control_result"] : "";

			$check 		= isset($control_result["check"		]) ? $control_result["check"	 ] : ""; // 處理結果 (0:未處理，1:已處理,成功，2:已處理, 失敗).
			$error_log 	= isset($control_result["error_log"	]) ? $control_result["error_log" ] : "";
			$order_flag = isset($control_result["order_flag"]) ? $control_result["order_flag"] : ""; // 訂單狀態 (-1:不需要處理，0:未處理，1:已處理,成功，2:已處理, 失敗)
			$order_id 	= isset($control_result["order_id"	]) ? $control_result["order_id"	 ] : "";
			$iRet = $check;
		} else { // 異常
			$iRet = 3;
			// {
			// 		"data": {
			// 		  "code": 401,
			// 		  "message": "Not Authorization."
			// 		},
			// 		"status": true
			// }
			$data 	= isset($obj["data"	 ]) ? $obj["data"  ] : "";
			$status = isset($obj["status"]) ? $obj["status"] : "";
			
			if (!empty($data)) {
				$code = isset($data["code"]) ? $data["code"] : "";
				$message = isset($data["message"]) ? $data["message"] : "";

				$res = result_message("false", "0x020E", "API return error json data :($code)$message", []);
			} else if (!empty($status)) {
				$res = result_message("false", "0x020E", "API return status :$status", []);
			} else {
				$res = result_message("false", "0x020E", "API return :未知錯誤", []);
			}
		}
		$ret_str = [0 => "未處理", 1 => "已處理成功", 2 => "失敗", 3 => "異常"];
		JTG_wh_log($remote_ip, "$func 檢查充電桩指令結果 :$iRet(".$ret_str[$iRet].")", $member_id);
		// echo "$func 檢查充電桩指令結果 :$iRet(".$ret_str[$iRet].")\n";
		return $iRet;
	}
	// 取得 postman 輸入格式為 json 資訊
	function getJsonInput(&$rawInput) {
		$input_data = array();
		try {
			// Read the raw input
			$rawInput = file_get_contents('php://input');

			// Decode the JSON input
			$input_data = json_decode($rawInput, true);
		} catch (Exception $e) {
			$input_data = null;
		}
		return $input_data;
	}

	// 帳密變成token
	function generateSSOtoken($uid, $upwd)
	{
		global $g_key, $g_jtg_OCPI_root;
		$SSO_info["www"] 		= $g_jtg_OCPI_root;
		$SSO_info["uid"] 		= $uid;
		$SSO_info["upwd"] 		= $upwd;
		$SSO_info["expire"] 	= date("Y-m-d H:i:s");
		$SSO_json 				= json_encode($SSO_info);
		$SSO_token["sso_token"]	= encrypt($g_key, $SSO_json);
		return ($SSO_token);
	}
	function validToken($val, &$member_id, &$role, &$order_limit, $ori_pwd="", $skip_expire=true)
	{
		global $g_key, $g_jtg_OCPI_root, $g_token_expire_sec;
		$ret = false;
		// $token = json_decode($val);
		// $content = $token->sso_token;
		$content_decry = decrypt($g_key, $val);
		// echo $content_decry;
		$SSO_info = json_decode($content_decry);
		// var_dump($SSO_info);
		if ($SSO_info->www != $g_jtg_OCPI_root) {
			$data = result_message("false", "0x0205", "token identity error", array());
			return $data;
		}
		if (empty($SSO_info->uid)) {
			$data = result_message("false", "0x0205", "token user id is required without empty", array());
			return $data;
		}
		if (empty($SSO_info->upwd)) {
			$data = result_message("false", "0x0205", "token user pwd is required without empty", array());
			return $data;
		}

		if ($skip_expire == false) {
			$dt_now = date('Y-m-d H:i:s', strtotime('now'));
			$dateTime = new DateTime($SSO_info->expire);
			if (getTwoTimeDiff($dt_now, $dateTime) > $g_token_expire_sec) {
				$data = result_message("false", "0x0205", "token over expire (".$g_token_expire_sec.")", array());
				return $data;
			}
		}

		$remote_ip = get_remote_ip();
		$db= new CXDB($remote_ip);
		try {
			$data = $db->connect($link, $SSO_info->uid, "");
			if ($data["status"] == "true") {
				$upwd = (empty($ori_pwd)) ? $SSO_info->upwd : $ori_pwd;
				// echo $upwd;
				$result = $db->existsMember($link, $SSO_info->uid, $upwd);
				if (!is_null($result) && mysqli_num_rows($result) > 0) {
					$member_id = $SSO_info->uid;
					// if ($row = mysqli_fetch_array($result)) {
					// 	$role =$row['role'];
					// 	$order_limit = isset($row['order_limit']) ? $row['order_limit']: 0;
					// }
					$data = result_message("true", "0x0200", "token is validate.", array());
				} else {
					$data = result_message("false", "0x0206", "請重新輸入密碼", array());
				}
			}
		} catch (Exception $e) {
			$data = result_message("false", "0x0205", "token sql Exception :".$e->getMessage(), array());
		} finally {
			$data_close_conn = close_connection_finally($link, $remote_ip, $SSO_info->uid);
			if ($data_close_conn["status"] == "false") $data = $data_close_conn;
		}
		return $data;
	}
	function sendFCM($fcm_token, $FCM_title, $FCM_content, $FCM_extra)
	{
		global $g_notify_apiurl;

		$FCM_url = $g_notify_apiurl;
		$fcmresult = "";
		if (!empty($FCM_extra)) {								
			$fields = array(
				'to' 		   => $fcm_token,
				"notification" => [
									"body"  	   => $FCM_content,
									"title" 	   => $FCM_title,
									"icon"  	   => "ic_launcher",
									"sound" 	   => "default",
									"click_action" => $FCM_extra,
				],
			);
		} else {
			$fields = array(
				'to' 		   => $fcm_token,
				"notification" => [
									"body" 	=> $FCM_content,
									"title" => $FCM_title,
									"icon"  => "ic_launcher",
									"sound" => "default",
				],
			);
		}
		
		$headers = array(
			'Authorization: key=AAAAADrsV1M:APA91bEH_dSnFD_CtG2z4UyJo8kQSG5fziwYmyxQJeftr-PcLOPe_xoMhWxLIa-B9wn078EDTl-A3S8eZcExy49xdXxAGSMGA3QNbPZKBAI73jcstgdT77b8DspUmeFR59JD8QaABO1C',
			'Content-Type: application/json',
		);
		for ($i = 0; $i < 3; $i++) {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL			, $FCM_url				);
			curl_setopt($ch, CURLOPT_POST			, true					);
			curl_setopt($ch, CURLOPT_HTTPHEADER		, $headers				);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER	, true					);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER	, false					);
			curl_setopt($ch, CURLOPT_POSTFIELDS		, json_encode($fields)	);
			$fcmresult = curl_exec($ch);
			
			curl_close($ch);	
			if (strlen($fcmresult) > 2) break;							
		}
		return (strlen($fcmresult) > 2);
	}
// 顯示過長資料，則使用縮略文字
	function getShortText4Show($str, $max_len = 20)
	{
		return (strlen($str) > $max_len * 3) ? mb_substr($str, 0, $max_len, "UTF-8")."..." : $str; //substr($str, 0, $max_len * 3).".." : $str;
	}

// 取得 html 空白字元
	function getHtmlSpaceChar($mylength = 0)
	{
		$ret = "";
		for ($i = 0; $i < $mylength; $i++)
			$ret.="&nbsp;";
		return $ret;
	}

// 取得序號且判斷是否重覆
	function getSid($table, $member_id)
	{
		$sid 		= "";
		$data 		= array();
		$remote_ip 	= get_remote_ip();
		$db			= new CXDB($remote_ip);
		try {
			$data = $db->connect($link, $member_id, "");
			if ($data["status"] == "true") {
				$sid = getUniqueId2();
				while ($db->existsSid($link, $table, $sid)) {
					$sid = getUniqueId2();
				}
			}
		} catch (Exception $e) {
		} finally {
			$data_close_conn = close_connection_finally($link, $remote_ip, $member_id);
			if ($data_close_conn["status"] == "false") $data = $data_close_conn;
		}
		return $sid;
	}

	// 取得序號且判斷是否重覆
	function getSidSimple($table, $member_id, $head)
	{
		$idx 		= 0;
		$sid 		= "";
		$data 		= array();
		$remote_ip 	= get_remote_ip();
		$db			= new CXDB($remote_ip);
    	try {
			$data = $db->connect($link, $member_id, "");
			if ($data["status"] == "true") {
				$idx++;
				$sid = getUniqueId4Simple($head.getDateTimeFormat(""), $idx);
				while ($db->existsSid($link, $table, $sid)) {
					$idx++;
					$sid = getUniqueId4Simple($head.getDateTimeFormat(""), $idx);
				}
			}
		} catch (Exception $e) {
		} finally {
			$data_close_conn = close_connection_finally($link, $remote_ip, $member_id);
			if ($data_close_conn["status"] == "false") $data = $data_close_conn;
		}
		return $sid;
	}
	
// 取得遠端用戶的ip public
	function get_remote_ip()
	{
		if (!empty($_SERVER["HTTP_CLIENT_IP"])) {
			$ip = $_SERVER["HTTP_CLIENT_IP"];
		} elseif (!empty($_SERVER["HTTP_X_FORWARDED_FOR"])) {
			$ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
		} else {
			$ip = isset($_SERVER["REMOTE_ADDR"]) ? $_SERVER["REMOTE_ADDR"] : '';
		}
		return $ip;
	}
	function get_remote_ip_underline()
	{
		$ip = get_remote_ip();
		$ip = str_replace('.', '_', $ip);
		$ip = str_replace(':', '_', $ip);
		return $ip;
	}

// close connection at finally
	function close_connection_finally(&$link, $remote_ip, $Person_id,
									$log_title = "", $log_subtitle = "", $file_header="Api")
	{
		$data 			= array();
		$data_status 	= array();
		
		$dst_Person_id 	= "";
		$dst_title 		= $log_title	;
		$dst_subtitle 	= $log_subtitle	;
		$dst_Person_id 	= ($log_title 	 == "") ? $Person_id 			: "";
		$data = result_message("true", "0x0200", "close connection Succeed!", "");
		// wh_log($remote_ip, $data["responseMessage"], $dst_Person_id, $file_header);
		try {
			if ($link != null)
			{
				mysqli_close($link);
				$link = null;
			}
		} catch (Exception $e) {
			$data = result_message("false", "0x0207", "Exception error: disconnect!", "");
			wh_log_Exception($remote_ip, get_error_symbol($data["code"]).$data["code"]." ".$data["responseMessage"]." error :".$e->getMessage(), $dst_Person_id, $file_header);
		}
		return $data;
	}
	function getHairserviceParameter($db, $link, $sid, $service_item, $Mode="get_name")
	{
		$ret = "";
		try {
			$result2 = $db->getHairservice($link, $sid, -1, 0, -1, -1, $service_item
									, "*" , "");
			if (!is_null($result2) && mysqli_num_rows($result2) > 0) {
				while ($row2 = mysqli_fetch_array($result2)) {
					if ($Mode == "get_name")
						$ret.=$row2['service_name'].",";
					else
						$ret.=$row2['service_price'].",";
				}
				$ret = substr($ret, 0, strlen($ret) - 1);
			} else {
				$ret = "";
			}
		} catch (Exception $e) {
			$ret = "";
		}
		return $ret;	
	}
	function getMemberCount($db, $link, $sid, $item)
	{
		$member_count = 0;
		try {
			$date2 = new DateTime(date("Y-m-d"));
			$edate = $date2->format('Y-m-d');
			switch ($item) {
				case "d":
					$sdate = $date2->format('Y-m-d');
					break;
				case "w":
					$date1 = $date2->modify('-7 day');
					$sdate = $date1->format('Y-m-d');
					break;
				case "m":
					$date1 = $date2->modify('-30 day');
					$sdate = $date1->format('Y-m-d');
					break;
			}
			$result = $db->getMyMembercardCount($link, $sid, 0, $sdate, $edate);
			if (!is_null($result) && mysqli_num_rows($result) > 0) {
				while ($row = mysqli_fetch_array($result)) {
					$member_count = $row['member_count'];
				}
			}
		} catch (Exception $e) { }	
		return $member_count;	
	}
	function getMemberToken($db, $link, $uid, &$rcd_count)
	{
		$token = "";
		try {
			$where_str = "AND id = '$uid'";
			$result = $db->getData($link, "data_member", "*", $where_str);
			if (!is_null($result) && mysqli_num_rows($result) > 0) {
				$rcd_count = mysqli_num_rows($result);
				while ($row = mysqli_fetch_array($result)) {
					$token = $row['token'];
					break;
				}
			}
		} catch (Exception $e) { }	
		return $token;	
	}
	function postChangePwd($memberid, $memberpwd)
	{
		$data = array(
			'mobile' 	=> $memberid,
			'password' 	=> $memberpwd,
		);

		$post_data = json_encode($data);
		$result = callAPI($error, 'https://ml-api.jotangi.com.tw/api/auth/rewritepwd', $post_data, "POST", true);
		$obj = json_decode($result, true);

		// handle curl error
		return ($obj["status"] == "error") ? 0 : 1; //die();
	}
	function postRegisterMember($membername, $memberid, $memberpwd)
	{
		$data = array(
		'name' 		=> $membername,
		'mobile' 		=> $memberid,
		'password' 	=> $memberpwd,
		);
		$error=null;
		$post_data = json_encode($data);
		$result = callAPI($error, 'https://ml-api.jotangi.com.tw/api/auth/register', $post_data, "POST", true);
		$obj = json_decode($result, true);

		// handle curl error
		return ($obj["status"] == "error") ? $error : 1; //die();
	}
	function postResetPwd($memberid, $memberpwd)
	{
		$data = array(
			'mobile' 	=> $memberid,
			'password' 	=> $memberpwd,
		);

		$post_data = json_encode($data);
		$result = callAPI($error, 'https://ml-api.jotangi.com.tw/api/auth/rewritepwd', $post_data, "POST", true);
		$obj = json_decode($result, true);

		// handle curl error
		return ($obj["status"] == "error") ? 0 : 1; //die();
	}
	function uiLocationPage($onlyChk4LogoutPage=false)
	{
		session_start();
		if ($_SESSION['accname'	 ] ==  "") header("Location: logout.php");
		if (!$onlyChk4LogoutPage && (is_null($_SESSION['priority']) || strval($_SESSION['priority']) == 0)) header("Location: ./");
	}
	function getFullMenuString($idx, $subidx)
	{
		$ret = "";
		$ret = getMenuString($idx);
		$retsub = getSubMenuString($idx, $subidx);
		return (empty($retsub)) ? $ret : $ret." --> ".$retsub;
	}
	function getMenuString($idx)
	{
		global $g_sidemenu;
		$root = $g_sidemenu['root'];
		return $root[$idx];
	}
	function getSubMenuString($idx, $subidx)
	{
		global $g_sidemenu;
		$root = $g_sidemenu['root'];
		$subMenu = $g_sidemenu[$root[$idx]];
		return $subMenu[$subidx];
	}
	function getMenuIcon($idx)
	{
		global $g_sidemenu;
		$root_icon  = $g_sidemenu['root_icon'];
		return $root_icon[$idx];
	}
?>