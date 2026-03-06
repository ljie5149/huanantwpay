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
       
function getAreaInfo(&$query_rows, $area = '竹東', $member_id = "JTG_CHARGE UI") {
        $ret = ''; $area_name = '';
        try {
            $remote_ip = get_remote_ip();
            $db = new CXDB($remote_ip);
            $conn_res = $db->connect($link, $member_id, "");
            if ($conn_res["status"] == "true") {
                $sql = "SELECT sid, station_name FROM `data_station` WHERE station_name LIKE '$area%' AND avalible = '1' ORDER BY nid ASC;";
                if ($result = mysqli_query($link, $sql)) {
                    if (mysqli_num_rows($result) > 0) {
                        while($row = mysqli_fetch_assoc($result)) {
                            $area_name .= (strlen($area_name) > 0) ? ", " : "";
                            $area_name .= $row['station_name'];
                            array_push($query_rows, $row);
                        }
                        
                        $ret = "<table>";
                        // if (intval($cur_info['remain_rent_count']) > 0) $flagZero = false;
                        $ret .= "<tr>";
                        $ret .= '<td><span style="color:black; font-size:18px">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;場域：'.$area_name.'</span></td>';
                        $ret .= "</tr>";
                        $ret .= "</table><br><br>";
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
	function getDailyInfo(&$visible, $date_s, $date_e, $area = '竹東', $member_id = "JTG_CHARGE UI") {
		$ret = '';
		try {
			$area_name = $area;
			$remote_ip = get_remote_ip();
			$db = new CXDB($remote_ip);
			$conn_res = $db->connect($link, $member_id, "");
			if ($conn_res["status"] == "true") {
				$sql = "SELECT * FROM `log_history4user` WHERE DATE(start_time)>='$date_s' AND DATE(start_time)<='$date_e' AND station_name LIKE '%$area%' AND avalible = 1 ORDER BY start_time ASC;";
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
			$ip = isset($_SERVER["REMOTE_ADDR"]) ? $_SERVER["REMOTE_ADDR"]:'';
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
	
	// 判斷充電車格是否佔位
	function condictionCore(&$message, $enterPlateNum, $row)
	{
		$is_use = false;

		$status = $row['status'];
		$status_zhtw = $row['status_zhtw'];
		if ($status == "-1") $status_zhtw = "無操作";
		$message .= "\n充電狀態 :$status_zhtw($status)";

		// 充電中判斷
		if (!empty($row['order_serial_number']) && $row['stop_reason'] == '---') {
			$message .= "\n$enterPlateNum 狀態：車子充電中";
		}
		// 有充電，但已結束
		elseif (!empty($row['order_serial_number']) && ($row['stop_reason'] == '直接拔槍結束' || $row['stop_reason'] == 'APP結束交易(APP 使用者)')) {
			$message .= "\n$enterPlateNum 狀態：車子已結束充電，開始判斷是否佔位中";

			// 計算 enterTime 與 start_time 差異
			if (!empty($row['enterTime']) && !empty($row['start_time'])) {
				$_now = (empty($row['start_time'])) ? get24HourFormat('') : $row['start_time'];
				$enterTime = strtotime($row['enterTime']);
				$startTime = strtotime($_now);
				$diff1 = $startTime - $enterTime; // 秒數
				$message .= "\n進場 → 開始充電 差異：" . gmdate("H:i:s", $diff1);
			}

			// 計算 exitTime 與 stop_time 差異
			if (!empty($row['stop_time'])) {
				$_now = (empty($row['exitTime'])) ? get24HourFormat('') : $row['exitTime'];
				$exitTime = strtotime($_now);
				$stopTime = strtotime($row['stop_time']);
				$diff2 = $stopTime - $exitTime; // 秒數
				$message .= "\n結束 → 離場充電 差異：" . gmdate("H:i:s", $diff2);
				$is_use = true;
			}
		}
		// 佔位判斷
		elseif (empty($row['order_serial_number']) && $row['stop_reason'] == '') {
			$message .= "\n$enterPlateNum 狀態：車子佔位";
			$is_use = true;

			// 計算 enterTime 與 start_time 差異
			if (!empty($row['enterTime']) && !empty($row['start_time'])) {
				$enterTime = strtotime($row['enterTime']);
				$startTime = strtotime($row['start_time']);
				$diff1 = $startTime - $enterTime; // 秒數
				$message .= "\n進場 → 開始充電 差異：" . gmdate("H:i:s", $diff1);
			}

			// 計算 exitTime 與 stop_time 差異
			if (!empty($row['exitTime']) && !empty($row['stop_time'])) {
				$exitTime = strtotime($row['exitTime']);
				$stopTime = strtotime($row['stop_time']);
				$diff2 = $stopTime - $exitTime; // 秒數
				$message .= "\n離場 → 結束充電 差異：" . gmdate("H:i:s", $diff2);
			}
		}
		else {
			$message .= "\n狀態：未知(可能充電紀錄不足)";
			$is_use = true;
		}
		return $is_use;
	}
	
	function condictionCore4miaoli(&$message, $enterPlateNum, $row) {
		return condictionCore($message, $enterPlateNum, $row);
	}
	
	function condictionCore4nantou(&$message, $enterPlateNum, $row) {
		return condictionCore($message, $enterPlateNum, $row);
	}

	function refreshChargeCellInfo($row, $enteredImage, $exitingImage) {
		// 佔位處理
		$input = [];
		$input['user_id'			] = $row['user_id'				];
		$input['map_chargecell_sid'	] = $row['map_chargecell_sid'	];
		$input['station_name'		] = $row['station_name'			];
		$input['charger_name'		] = $row['charger_name'			];
		$input['gun_name'			] = $row['gun_name'				];
		$input['charge_id'			] = $row['charge_id'			];
		$input['park_code'			] = $row['park_code'			];
		$input['park_cell'			] = $row['park_cell'			];
		$input['status'				] = $row['status'				];
		$input['status_zhtw'		] = $row['status_zhtw'			];
		// $input['pay_status'			] = '';
		// $input['pay_method'			] = '';
		// $input['pay_time'			] = '';

		$input['in_use_fee'			] = '';
		$input['enterPlateNum'		] = $row['enterPlateNum'		];
		$input['exitPlateNum'		] = $row['exitPlateNum'			];
		$input['enter_time'			] = $row['enterTime'			];
		$input['exit_time'			] = $row['exitTime'				];
		$input['in_use_flag'		] = "1";
		$input['in_use_start_time'	] = (strlen($input['enter_time']) > 0) ? '' : get24HourFormat('');
		$input['in_use_end_time'	] = $input['exit_time'			];
		$input['in_use_time'		] = (strlen($input['exit_time']) > 0) ? secondsToTime(getTwoDateTimeSeconds($input['enter_time'], $input['exit_time'])) : '';
		// $input['in_use_pay_status'	] = '';
		// $input['in_use_pay_method'	] = '';
		// $input['in_use_pay_time'	] = '';
		
		$input['charge_fee'					] = $row['price'				];
		$input['charge_flag'				] = (strlen($row['order_serial_number']) > 0) ? '1':'0';
		$input['charge_order_id'			] = $row['order_id'				];
		$input['charge_order_serial_number'	] = $row['order_serial_number'	];
		$input['charge_start_time'			] = $row['start_time'			];
		$input['charge_stop_time'			] = $row['stop_time'			];
		$input['charge_time'				] = (strlen($row['stop_time']) > 0) ? secondsToTime(getTwoDateTimeSeconds($row['start_time'], $row['stop_time'])):'';
		$input['charge_stop_reason'			] = $row['stop_reason'			];
		$input['charge_pay_status'			] = $row['jtg_pay_status'		];
		$input['charge_pay_method'			] = $row['pay_method'			];
		$input['charge_pay_time'			] = $row['pay_time'			];
		
		$input['in_use_enteredImage'		] = (strlen($enteredImage) > 0) ? $enteredImage : '';
		$input['in_use_exitingImage'		] = (strlen($exitingImage) > 0) ? $exitingImage : '';
		// $input['avalible'					] = '1';
		return $input;
	}
	// 第１碼：場別代碼 
	// 第２碼：計時計次之收費代碼 
	// 第３，４，５，６，７，８碼：開單時間經轉譯後的結果 
	// 第９，１０，１１：路段代號 
	// 第１２，１３，１４碼：車格格號 
	// 第１５碼：檢查碼 
	// 所有英文字元一律以大寫方式呈現，不得為小寫。
	function getChargecellOrderNo($f1, $f2, $start_time, $parkCode, $parkSpaceCode, &$msg)
	{
		global $link;

		$msg .= "start_time :$start_time, parkCode :$parkCode, parkSpaceCode :$parkSpaceCode, f2 :$f2\n\n";
		$od = "";
		try {
			$n2c = array( 0=>'0',  1=>'1',  2=>'2',  3=>'3', 4=>'4',  5=>'5',  6=>'6',  7=>'7',  8=>'8', 9=>'9',
						10=>'A', 11=>'B', 12=>'C', 13=>'D',14=>'E', 15=>'F', 16=>'G', 17=>'H',18=>'I', 19=>'J', 20=>'K', 21=>'L',
						22=>'M', 23=>'N', 24=>'O', 25=>'P',26=>'Q', 27=>'R', 28=>'S', 29=>'T',30=>'U', 31=>'V', 32=>'W', 33=>'X',
						34=>'Y', 35=>'Z');
			$f01 = $f1; 											// 第１碼：場別代碼，例:Q
			$f02 = $f2; 											// 第２碼：計時計次之收費代碼
																		// Ｈ：計時收費。
																		// Ｋ：卸貨區次計收費。
																		// Ｌ：一般路段計次收費。
																		// Ｍ：機車計次收費。
																		// N：機車卸貨收費。
																		// P：計時身障（優惠４小時）收費。
																		// Q：計次身障（優惠半價）收費。
																		// Ｒ：計時最多３小時收費。
			$f030405060708 = getDatetimeCode($start_time, $n2c);	// 第３，４，５，６，７，８碼：開單時間經轉譯後的結果
																		// (開單年－2012) X 35942400
																		// + 月 X 2764800
																		// + 日 X 86400
																		// + 時 X 3600
																		// + 分 X 60
																		// + 秒
			$f09101112 = $parkCode;									// 第９，１０，１１，１２：路段代號
			$f1314151617 = $parkSpaceCode; 							// 第１３，１４，１５，１６，１７碼：車格格號

			// echo "17碼 :".$f01.$f02.$f030405060708.$f091011.$f121314."\n";
			// echo "<br>test check sum :".checksum("AK6NGPF0A01099")."<br>";
			$f18 = checksum($f01.$f02.$f030405060708.$f09101112.$f1314151617);
			// echo "f18:".$f15."\n";
			
			$od = $f01.$f02.$f030405060708.$f09101112.$f1314151617.$f18;
			$msg .= "18碼 :".$od."\n";
			$sql = "select * from order_chargecell where order_no='$od'";
			if ($ret1 = mysqli_query($link, $sql)){
				if (mysqli_num_rows($ret1) > 0) {
					$msg .= "單號已存在($od)";
					return $od;
				}
			}
		} catch (Exception $e) {
			$msg = "getChargecellOrderNo Exception :".$e->getMessage();
		}
		return $od;
	}
	function getDatetimeCode($date_time, $map_char)
	{
		$sRet = "";
		$iCalc = 0;
		$dt_src = new DateTime($date_time);
		
		// 規則加總計算
		$currentYear  = (int) $dt_src->format("Y");
		$iCalc += ($currentYear - 2012) * 35942400;
		$currentMonth = (int) $dt_src->format("m");
		$iCalc += $currentMonth * 2764800;
		$currentDay   = (int) $dt_src->format("d");
		$iCalc += $currentDay * 86400;
		$currentHour  = (int) $dt_src->format("H");
		$iCalc += $currentHour * 3600;
		$currentHour  = (int) $dt_src->format("i");
		$iCalc += $currentHour * 60;
		$currentSec   = (int) $dt_src->format("s");
		$iCalc += $currentSec;
		// echo "iCalc :$iCalc <br>";

		$f345678 = ""; // 第３，４，５，６，７，８碼：開單時間經轉譯後的結果
							// (開單年－2012) X 35942400
							// + 月 X 2764800
							// + 日 X 86400
							// + 時 X 3600
							// + 分 X 60
							// + 秒
		$iRemain   = 0;
		$iQuotient = 0;

		// 第一段
		$iRemain = $iCalc % 36;
		$iQuotient = ($iCalc - $iRemain) / 36;
		$sRet = $map_char[$iRemain];

		// 第二段
		$iRemain = $iQuotient % 36;
		$iQuotient = ($iQuotient - $iRemain) / 36;
		$sRet .= $map_char[$iRemain];
		
		// 第三段
		$iRemain = $iQuotient % 36;
		$iQuotient = ($iQuotient - $iRemain) / 36;
		$sRet .= $map_char[$iRemain];
		
		// 第四段
		$iRemain = $iQuotient % 36;
		$iQuotient = ($iQuotient - $iRemain) / 36;
		$sRet .= $map_char[$iRemain];
		
		// 第五段
		$iRemain = $iQuotient % 36;
		$iQuotient = ($iQuotient - $iRemain) / 36;
		$sRet .= $map_char[$iRemain];
		
		// 第六段
		$iRemain = $iQuotient % 36;
		$iQuotient = ($iQuotient - $iRemain) / 36;
		$sRet .= $map_char[$iRemain];
		
		return $sRet;
	}
	
	/**
	* 搬移車輛圖片，依日期自動建立子資料夾
	* @param string $srcFullPath 來源檔案完整路徑
	* @return bool 搬移成功或失敗
	*/
	function copyCarPlateImage(&$res, $srcFile)
	{
		global $g_src_cell_plate_path, $g_dst_cell_plate_path;

		$src = $g_src_cell_plate_path.$srcFile;
		if (!file_exists($src)) {
			$res = "來源檔案不存在: $src";
			return '';
		}

		// 從路徑取出日期資料夾 (假設固定格式: .../mntevent/YYYYMMDD/filename.jpg)
		$parts = explode("/", $src);
		$dateFolder = getDateTimeFormat(''); // 倒數第二層就是日期資料夾
		$filename   = basename($src);

		// 目的資料夾
		$dstFolder = $g_dst_cell_plate_path . $dateFolder . "/";
		$dstFile   = $dstFolder . $filename;

		// 如果目的日期資料夾不存在就建立
		echo "dstFolder: $dstFolder\n";
		if (!is_dir($dstFolder)) {
			mkdir($dstFolder, 0777, true);
		}

		// 複製檔案 (保留來源)
		// echo "來源: $src\n";
		// echo "目的: $dstFile\n";
		// echo "filename: $filename\n";
		if (strlen($filename) > 0 && $filename != 'api') {
			if (copy($src, $dstFile)) {
				$res = "複製成功: $dstFile";
				return "/$dateFolder/$filename";
			} else {
				$res = "複製失敗: $src -> $dstFile";
				return '';
			}
		}
		return '';
	}
	
	function checksum($code)
	{
		$c2n = array('0'=>0, '1'=>1, '2'=>2, '3'=>3, '4'=>4, '5'=>5, '6'=>6, '7'=>7, '8'=>8, '9'=>9,
					 'A'=>10, 'B'=>11, 'C'=>12, 'D'=>13,'E'=>14, 'F'=>15, 'G'=>16, 'H'=>17,'I'=>18, 'J'=>19, 'K'=>20, 'L'=>21,
					 'M'=>22, 'N'=>23, 'O'=>24, 'P'=>25,'Q'=>26, 'R'=>27, 'S'=>28, 'T'=>29,'U'=>30, 'V'=>31, 'W'=>32, 'X'=>33,
					 'Y'=>34, 'Z'=>35);
		$n2c = array(0=>'0', 1=>'1', 2=>'2', 3=>'3', 4=>'4', 5=>'5', 6=>'6', 7=>'7', 8=>'8', 9=>'9',
					 10=>'A', 11=>'B', 12=>'C', 13=>'D',14=>'E', 15=>'F', 16=>'G', 17=>'H',18=>'I', 19=>'J', 20=>'K', 21=>'L',
					 22=>'M', 23=>'N', 24=>'O', 25=>'P',26=>'Q', 27=>'R', 28=>'S', 29=>'T',30=>'U', 31=>'V', 32=>'W', 33=>'X',
					 34=>'Y', 35=>'Z');
		$base = array(3,5,9,11,13,17,19,3,5,9,11,13,17,19,23,29,31);
		$sum=0;
		//for($i=0;$i<count($code);$i++)
			//$sum += $c2n[$code[$n2c[$i]]]*$base[$i];
		
		for($i=0;$i<strlen($code);$i++)
			$sum += $c2n[$code[$i]]*$base[$i];
		$val = $sum%7;
		return $val;
	}

	function autoGetHistory($link, $db, $member_id) {
		global $g_root_url;

		$url = $g_root_url.'api/JTG_get_history4user2.php';
		$sql_user = "SELECT DISTINCT user_id FROM `log_charge_event` WHERE DATE(create_date)=DATE(NOW());";
		echo "sql_user :$sql_user\n\n";
		if ($result_user = mysqli_query($link, $sql_user)) {
			if (mysqli_num_rows($result_user) > 0) {
				while($row_user = mysqli_fetch_array($result_user)) {
					$user_id = $row_user['user_id'];
					$sql = "SELECT * FROM `log_history4user` WHERE user_id = '$user_id' AND DATE(stop_time)=DATE(NOW()) ORDER BY create_date ASC;";
					
					echo "sql :$sql\n\n";
					if ($result = mysqli_query($link, $sql)) {
						// if ($user_id == '6d8bad8a-bd39-11ef-9565-fa163ef46fbc') {
							echo "user_id :$user_id\n\n";
							if (mysqli_num_rows($result) > 0) {
							} else {
								// 要傳送的參數
								$data = [
									"user_id" => "$user_id",
									"count_one_time" => "9999",
									"skip_docs" => "0"
								];
								$resJson = jCallAPI($error, $url, $data, "POST", "application/form-data");
								// echo "resJson :$reJson\n";
							}
						// }
					}
				}
			}
		}
	}
	function getPaymodeZhtw($mode)
	{
		$map = [
			'credit' => '信用卡',
			'debit'  => '金融卡'
			];
		return $map[$mode] ?? '未知的交易';
	}
	function getStoreId($mode)
	{
		global $g_storeId4debitcard, $g_storeId4credit, $g_storeId4creditbyscan;
		$ret = "";
		switch($mode) {
			case "credit":
				$ret = $g_storeId4credit;
				break;
			case "debit":
				$ret = $g_storeId4debitcard;
				break;
		}
		return $ret;
	}
	function getEndpointCode($mode)
	{
		global $g_endpointCode4debitcard, $g_endpointCode4credit, $g_endpointCode4creditbyscan;
		$ret = "";
		switch($mode) {
			case "credit":
				$ret = $g_endpointCode4credit;
				break;
			case "debit":
				$ret = $g_endpointCode4debitcard;
				break;
		}
		return $ret;
	}
	function getStoreIdByScan($mode)
	{
		global $g_storeId4debitcard, $g_storeId4credit, $g_storeId4creditbyscan;
		$ret = "";
		switch($mode) {
			case "credit":
				$ret = $g_storeId4creditbyscan;
				break;
			case "debit":
				$ret = $g_storeId4debitcard;
				break;
		}
		return $ret;
	}
	function getEndpointCodeByScan($mode)
	{
		global $g_endpointCode4debitcard, $g_endpointCode4credit, $g_endpointCode4creditbyscan;
		$ret = "";
		switch($mode) {
			case "credit":
				$ret = $g_endpointCode4creditbyscan;
				break;
			case "debit":
				$ret = $g_endpointCode4debitcard;
				break;
		}
		return $ret;
	}
	function setPayStatus(&$req_json, $url, $parking_id, $order_no, $realpay, $discount, $pay_status, $pay_log, $bill_no, $payment_order_no, $pay_method, $pay_source, $jko_coin = "0") {
		/*
		[api] <<<<< JTG_Set_PayStatus request :{
			"order_no": "176897159057055",
			"realpay": "2000",
			"discount": "000",
			"pay_status": "1",
			"pay_log": "Cash",
			"m_id": "ZP04",
			"balance_sn": "",
			"m_order_id": "ZP04-ldvNCr",
			"pay_method": "0",
			"pay_source": "0",
			"jko_coin": "0",
			"parking_id": "4"
			}
		*/

		// 要傳送的參數
		$resJson = ""; $req_json = "";
		if (strlen($url) > 0) {
			$data = [
				"order_no" 		=> $order_no,
				"realpay" 		=> $realpay."00",
				"discount" 		=> $discount,
				"pay_status" 	=> $pay_status,
				"pay_log" 		=> $pay_log,
				"m_id" 			=> $pay_status,
				"balance_sn" 	=> $bill_no,
				"m_order_id" 	=> $payment_order_no,
				"pay_method" 	=> $pay_method,
				"pay_source" 	=> $pay_source,
				"jko_coin" 		=> $jko_coin,
				"parking_id" 	=> $parking_id
			];
			$req_json = json_encode($data, true);
			$resJson = jCallAPI($error, $url."JTG_Set_PayStatus.php", $data, "POST", "application/form-data");
		}
		return $resJson;
	}
	function getInvoice(&$req_json, $url, $parking_id, $order_no, $plate_no, $amount, $carrierId1, $carrierId2, $email, $carrierType, $loveId) {
		/*
			<<<<< getInvoice request :{
			"name": "RFP-0039",
			"amount": "20",
			"orderno": "176897159057055",
			"carrierId1": "",
			"carrierId2": "",
			"email": "",
			"companyNo": "",
			"carrierType": "",
			"loveId": "6888",
			"parking_id": "4"
			}
		*/
		// 要傳送的參數
		$resJson = "";
		if (strlen($url) > 0) {
			$data = [
				"name" 			=> $plate_no,
				"amount" 		=> $amount,
				"orderno" 		=> $order_no,
				"carrierId1" 	=> $carrierId1,
				"carrierId2" 	=> $carrierId2,
				"email" 		=> $email,
				"carrierType"	=> $carrierType,
				"loveId" 		=> $loveId,
				"parking_id" 	=> $parking_id
			];
			$req_json = json_encode($data, true);
			$resJson = jCallAPI($error, $url."JTG_Get_Invoice.php", $data, "POST", "application/form-data");
		}
		return $resJson;
	}
	function createTWpayTable($link, $year) {
		$table = "data_order_$year";
		$sql = "";
		if (!existTable($link, $table)) {
			$sql.= "CREATE TABLE IF NOT EXISTS $table (
						nid 				        INT(20) 		                UNSIGNED AUTO_INCREMENT PRIMARY KEY COMMENT '流水號'    ,
						create_date 		        DATETIME		                NOT NULL COMMENT '建立日期'                             ,
						modify_date	 		        DATETIME 			                NULL COMMENT '最後更新日期'                           ,
						scanType                    VARCHAR(2) 	                  		NULL COMMENT '回傳解析模式 - A：主掃，B：被掃'          ,
						order_no                    VARCHAR(100)                  		NULL COMMENT '訂單號碼'                               ,
						storeId                     VARCHAR(100) 	                	NULL COMMENT '特店代號'                               ,
						endpointCode                VARCHAR(100) 	                	NULL COMMENT '端末代號'                               ,
						credit_storeId              VARCHAR(100) 	                	NULL COMMENT '信用卡特店代號'                         ,
						credit_endpointCode         VARCHAR(100) 	                	NULL COMMENT '信用卡端末代號'                         ,
						operate_src                 VARCHAR(200) 		              	NULL COMMENT '操作來源'                              ,
						api                         VARCHAR(100) 	                	NULL COMMENT 'api'		                              ,
						api_zhtw                    VARCHAR(500) 	                	NULL COMMENT 'api名稱'	                            ,
						resp_code 			        VARCHAR(10) 		              	NULL COMMENT '回應代碼'                             ,
						resp_msg 			        TEXT 		                      	NULL COMMENT '回應訊息'                             ,
    					twqr_resp_msg 				VARCHAR(2000) 						NULL COMMENT 'TWQR回應訊息'   						,

						pay_time                    DATETIME                      		NULL COMMENT '付款時間'                             ,
						pay_method                  INT(1)                        		NULL COMMENT '繳費方式  0:現金、1:刷卡、2:linepay、3:街口、4:悠遊卡、5:一卡通 6:匯款 7:ATM  8:其他多元支付TWpay 9:試營運',
						pay_method01                INT(1)              				NULL COMMENT '繳費方式  0:未知、1:信用卡、2:金融卡'         ,
						pay_status                  VARCHAR(100)                  		NULL COMMENT '繳費狀態  0:待付款; 1:已付款; 2:失敗; 3:退款'   ,
						amount                      VARCHAR(15)                   		NULL COMMENT '應繳金額'                             ,

						payment_order_no            VARCHAR(100)                  		NULL COMMENT '繳費機訂單號碼'                       ,
						pay_log                     VARCHAR(2000)                 		NULL COMMENT '支付log'                             ,
						realpay                     VARCHAR(15)                   		NULL COMMENT '實繳金額'                             ,
						discount                    VARCHAR(15)   	DEFAULT '0' 	NOT NULL COMMENT '折扣金額'                             ,
						bill_no                     VARCHAR(10) 		              	NULL COMMENT '帳務序號'                             ,
						PosArea                     VARCHAR(2) 		                	NULL COMMENT '場域代碼'                             ,
						parking_id                  VARCHAR(2) 		                	NULL COMMENT '停車場代碼'                           ,
						device_id                   VARCHAR(10) 		              	NULL COMMENT '設備Id'                               ,
						
						carrierId1                  VARCHAR(200) 		              	NULL COMMENT '悠遊卡：(免填) 共通用載具：手機條碼 自然人憑證：憑證號碼'                               ,
						carrierId2                  VARCHAR(200) 		              	NULL COMMENT '悠遊卡：(免填) 共通用載具：手機條碼 自然人憑證：憑證號碼'                               ,
						email                       VARCHAR(200) 		              	NULL COMMENT '電子信箱'                               ,
						companyNo                   VARCHAR(200) 		              	NULL COMMENT '統一編號'                               ,
						carrierType                 VARCHAR(200) 		              	NULL COMMENT '載具類別0: 悠遊卡 1: 通用載具(手機) 2: 自然人憑證'                               ,
						loveId                      VARCHAR(200) 		              	NULL COMMENT '愛心碼'                               ,

						set_pay_status              VARCHAR(2) 		DEFAULT '-1'    	NULL COMMENT '停管後台更新訂單狀態結果'              ,
						invoice_status              VARCHAR(2) 		DEFAULT '-1'    	NULL COMMENT '停管後台取得發票結果'                 ,
						res_set_pay_status          TEXT 		                      	NULL COMMENT 'json 停管後台更新訂單狀態結果'	        ,
						res_invoice_status          TEXT 				                NULL COMMENT 'json 停管後台取得發票結果'			        ,

						QRcode                      VARCHAR(2000) 		            	NULL COMMENT '條碼(退款用)'                          ,
						Reference_No                VARCHAR(200) 		              	NULL COMMENT '銀行交易單號'                          ,
						parking_url                 VARCHAR(500) 		              	NULL COMMENT '停管url(設定訂單狀態與開發票)'          ,

						request 			        TEXT 		                      	NULL COMMENT '要求'	                                ,
						response 			        TEXT 				                NULL COMMENT '回應'			                        ,
						txnType_code                VARCHAR(10) 		              	NULL COMMENT '交易類型代碼'                         ,
						txnType_msg 			    TEXT 		                      	NULL COMMENT '交易類型'                             ,

						avalible			        VARCHAR(2)   	DEFAULT 1    	NOT	NULL COMMENT '狀態'                                 ,
						remark				        TEXT	 			                NULL COMMENT '備註'
					) COMMENT '訂單紀錄';
				";
		}

		$table = "log_order_$year";
		if (!existTable($link, $table)) {
				$sql.= "CREATE TABLE IF NOT EXISTS $table (
							nid							INT(20) 		            UNSIGNED AUTO_INCREMENT PRIMARY KEY COMMENT '流水號',
							create_date 		        DATETIME		            NOT NULL COMMENT '建立日期'                         ,
							type	 		            VARCHAR(50)                 	NULL COMMENT 'INSERT:新增,UPDATE:更新'          ,
							order_no                    VARCHAR(100)                	NULL COMMENT '訂單號碼'                         ,
							scanType                    VARCHAR(2) 	                	NULL COMMENT '回傳解析模式 - A：主掃，B：被掃'    ,
							storeId                     VARCHAR(100) 	              	NULL COMMENT '特店代號'                         ,
							endpointCode                VARCHAR(100) 	              	NULL COMMENT '端末代號'                         ,
							credit_storeId              VARCHAR(100) 	              	NULL COMMENT '信用卡特店代號'                   ,
							credit_endpointCode         VARCHAR(100) 	              	NULL COMMENT '信用卡端末代號'                   ,
							operate_src                 VARCHAR(200) 		            NULL COMMENT '操作來源'                         ,
							api                         VARCHAR(100) 	              	NULL COMMENT 'api'		                          ,
							api_zhtw                    VARCHAR(500) 	              	NULL COMMENT 'api名稱'	                        ,
							resp_code 			        VARCHAR(10) 		            NULL COMMENT '回應代碼'                         ,
							resp_msg 			        TEXT 		                    NULL COMMENT '回應訊息'                         ,
    						twqr_resp_msg 				VARCHAR(2000) 					NULL COMMENT 'TWQR回應訊息'   					,

							pay_time                    DATETIME                    	NULL COMMENT '付款時間'                             ,
							pay_method                  INT(1)                      	NULL COMMENT '繳費方式  0:現金、1:刷卡、2:linepay、3:街口、4:悠遊卡、5:一卡通 6:匯款 7:ATM  8:其他多元支付TWpay 9:試營運',
    						pay_method01                INT(1)              			NULL COMMENT '繳費方式  0:未知、1:信用卡、2:金融卡'         ,
							pay_status                  VARCHAR(100)                	NULL COMMENT '繳費狀態  0:待付款; 1:已付款; 2:失敗; 3:退款'   ,
							amount                      VARCHAR(15)                 	NULL COMMENT '金額'                                 ,

							payment_order_no            VARCHAR(100)                	NULL COMMENT '繳費機訂單號碼'                       ,
							pay_log                     VARCHAR(2000)               	NULL COMMENT '支付log'                             ,
							realpay                     VARCHAR(15)                 	NULL COMMENT '實繳金額'                             ,
							discount                    VARCHAR(15)                 	NULL COMMENT '折扣金額'                             ,
							bill_no                     VARCHAR(10) 		            NULL COMMENT '帳務序號'                             ,
							PosArea                     VARCHAR(2) 		              	NULL COMMENT '場域代碼'                             ,
							parking_id                  VARCHAR(2) 		              	NULL COMMENT '停車場代碼'                           ,
							device_id                   VARCHAR(10) 		            NULL COMMENT '設備Id'                               ,

							carrierId1                  VARCHAR(200) 		            NULL COMMENT '悠遊卡：(免填) 共通用載具：手機條碼 自然人憑證：憑證號碼',
							carrierId2                  VARCHAR(200) 		            NULL COMMENT '悠遊卡：(免填) 共通用載具：手機條碼 自然人憑證：憑證號碼',
							email                       VARCHAR(200) 		            NULL COMMENT '電子信箱'                               ,
							companyNo                   VARCHAR(200) 		            NULL COMMENT '統一編號'                               ,
							carrierType                 VARCHAR(200) 		            NULL COMMENT '載具類別0: 悠遊卡 1: 通用載具(手機) 2: 自然人憑證',
							loveId                      VARCHAR(200) 		            NULL COMMENT '愛心碼'                                 ,
							
							set_pay_status              VARCHAR(2) 		DEFAULT '-1'  	NULL COMMENT '停管後台更新訂單狀態果'                 ,
							invoice_status              VARCHAR(2) 		DEFAULT '-1'  	NULL COMMENT '停管後台取得發票結果'                   ,
							res_set_pay_status          TEXT 		                    NULL COMMENT '要求'	                                ,
							res_invoice_status          TEXT 				            NULL COMMENT '回應'			                            ,

							QRcode                      VARCHAR(2000) 		          	NULL COMMENT '條碼(退款用)'                          ,
							Reference_No                VARCHAR(200) 		            NULL COMMENT '銀行交易單號'                          ,
							parking_url                 VARCHAR(500) 		            NULL COMMENT '停管url(設定訂單狀態與開發票)'          ,

							request 			        TEXT 		                    NULL COMMENT '要求'	                                ,
							response 			        TEXT 				            NULL COMMENT '回應'			                            ,
							txnType_code                VARCHAR(10)                 	NULL COMMENT '交易類型代碼'                         ,
							txnType_msg 			    TEXT 		                    NULL COMMENT '交易類型'                             ,
							notify_resp_msg 			TEXT 		                    NULL COMMENT 'TWQR回應訊息'                         ,

							avalible			        VARCHAR(2)   DEFAULT 1  	NOT	NULL COMMENT '狀態'                                 ,
							remark				        TEXT	 			            NULL COMMENT '備註'
						) COMMENT '訂單歷史紀錄';
				";
		}
		$table = "log_message";
		if (!existTable($link, $table)) {
				$sql.= "CREATE TABLE IF NOT EXISTS $table (
						nid						INT(20) 		    UNSIGNED AUTO_INCREMENT PRIMARY KEY COMMENT '流水號',
						create_date				DATETIME		    NOT NULL COMMENT '建立日期'		                      ,
						api                   	VARCHAR(100) 	  	NOT NULL COMMENT 'api'		                          ,
						api_zhtw              	VARCHAR(500) 	      	NULL COMMENT 'api名稱'	                        ,
						scanType              	VARCHAR(2) 	        	NULL COMMENT '回傳解析模式 - A：主掃，B：被掃'    ,
						storeId               	VARCHAR(100) 	      	NULL COMMENT '特店代號'                         ,
						endpointCode          	VARCHAR(100) 	      	NULL COMMENT '端末代號'                         ,
						credit_storeId        	VARCHAR(100) 	      	NULL COMMENT '信用卡特店代號'                    ,
						credit_endpointCode   	VARCHAR(100) 	      	NULL COMMENT '信用卡端末代號'                    ,
						order_no              	VARCHAR(100)        	NULL COMMENT '訂單號碼'                         ,
						operate_src           	VARCHAR(200) 	  	  	NULL COMMENT '操作來源'                         ,
						resp_code				VARCHAR(10) 	  	  	NULL COMMENT '回應代碼'                         ,
						resp_msg				TEXT 		            NULL COMMENT '回應訊息'                         ,
    					twqr_resp_msg 			VARCHAR(2000) 			NULL COMMENT 'TWQR回應訊息'   					,
						txnType_code          	VARCHAR(10) 	  	  	NULL COMMENT '交易類型代碼'                     ,
						txnType_msg 			TEXT 		            NULL COMMENT '交易類型'                         ,
						request 			    TEXT                	NULL COMMENT '要求'	                            ,
						response 			    TEXT 				    NULL COMMENT '回應'			                    ,
						remark				    TEXT	 			    NULL COMMENT '備註'
					) COMMENT '訊息紀錄資料表';
				";
		}
		// echo "$sql\n";
		if (strlen($sql) > 0) funMultiExecute($link, $sql);
	}
?>
