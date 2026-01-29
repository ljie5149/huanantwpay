<?php
    /*************************************************/
    /*                                               */
    /*       主掃(預下單)                         	  */
    /*		店家收款設備顯示QR Code，				   */
	/*		客戶使用電子錢包對店家所展示的 				*/
	/*		QR Code掃描後進行支付		       		  */
    /*                                               */
    /*************************************************/
    include_once "./../common/entry.php";
	header('Content-Type: application/json');
	
	global $g_3party_url, $g_terminalNo;
	$table = 'log_message';
    
    header('Content-Type: application/json');
    // 假設前端送 JSON 格式
    $json = file_get_contents("php://input");
	JTG_wh_log("notify", "json :$json", "JTG_notify");

	exit;
    // // 看門狗
    // $role = ""; $order_limit = 0;
    // // if (!empty($json_token)) {
    //     $data = validToken($json_token, $member_id, $role, $order_limit);
    //     if ($data["status"] == "false") {
    //         echo (json_encode($data, JSON_UNESCAPED_UNICODE));
    //         return;
    //     }
    // // }
	
	try {
		// 判斷必要參數
		$invalidate_param = "";
		for ($i = 0; $i < count($reuqire_fields); $i++) {
			$re_field = $reuqire_fields[$i];
			if (empty($require_param[$re_field])) {
				$invalidate_param .= (!empty($invalidate_param)) ? "," : "";
				$invalidate_param .= $re_field;
			}
		}
		if (!empty($invalidate_param)) {
			$res = result_message("false", "0x0202", "API parameter is required! $invalidate_param 為空值", []);
			JTG_wh_log($remote_ip, "$func API return :$error", $member_id);
			echo (json_encode($res, JSON_UNESCAPED_UNICODE));
			exit;
		}
		// ------------------------------------------------------------------------

		// entry
		$error = ""; $ret_msg = "";

		$url = $g_3party_url."preOrder";
		$body = [
			"merchantNo"  => $g_merchantNo,
			"terminalNo"  => $g_terminalNo,
			"orderNo"     => "ORD".date("YmdHis"),
			"orderAmount" => intval($amount),
			"currency"    => "TWD",
			"orderTime"   => date("YmdHis"),
			"orderDesc"   => $caption,
			"notifyUrl"   => "http://43.200.219.248/huanantwpay/api/notify.php",
			"txnDir"      => $txnDir
		];
		$post_data = $body;
		$result = callAPI($error, $url, $post_data, "POST", false, getHuananHeader());

		if (!empty($error)) {
			$res = result_message("false", "0x020E", "API return :$error", []);
			JTG_wh_log($remote_ip, "$func API return :$error", $member_id);
			echo (json_encode($res, JSON_UNESCAPED_UNICODE));
			exit;
		}

		// ------------------------------------------------------------------------
		JTG_wh_log($remote_ip, "$func $caption :$result", $member_id);
		$obj 		= json_decode($result, true);
		$obj_data 	= isset($obj["data"		]) ? $obj["data"	 ] : "";
		$status 	= isset($obj["status"	]) ? $obj["status"	 ] : 0;
		$code 		= isset($obj_data["code"]) ? $obj_data["code"] : 400;

		if ($status == 1 && intval($code) == 200) { // 成功
			// {
			// 		"code": 200,
			// 		"message": "success"
			// }
			$token 	 = $obj_data['token'];
			$message = isset($obj["message"]) ? $obj["message"] : "";
			$data_input['id'	  ] = $user_id;
			$data_input['password'] = '';
			$data_input['token'	  ] = $token;
			$data_input['json_str'] = json_encode($result);
			
			// 紀錄至資料庫
			$db = new CXDB($remote_ip);
			$conn_res = $db->connect($link, $member_id, "");
			if ($conn_res["status"] == "true") {
				$effect_row = $db->saveLog($link, $plateNo, $api, $api_name, $req, $res, "", $ret_msg);
				// echo "effect_row :".$effect_row."\n";
				if ($effect_row > 0) {
					// echo "modify success\n";
					$res = result_message("true", "0x0200", "succeed", []);
				} else {
					$res = result_message("false", "0x0205", "sql error :".$ret_msg, []);
				}
			} else {
				$res = result_message("false", "0x0205", "connect to mysql error :".$result, []);
			}
			
			$res 	 = result_message("true", "0x0200", $message, $obj);
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
	} catch (Exception $e) {
		$res = result_message("false", "0xE209", "Exception error! error detail:".$e->getMessage(), []);
		JTG_wh_log_Exception($remote_ip, $func ." ".get_error_symbol($data["status_code"]).$data["status_code"]." ".$data["status_message"], $member_id);
	} finally {
		$data_close_conn = close_connection_finally($link, $remote_ip, $member_id);
		if ($data_close_conn["status"] == "false") $data = $data_close_conn;
	}
	
	JTG_wh_log($remote_ip, $func ." ".get_error_symbol($res["status_code"])." result :".$res["status_message"]."\r\n".$g_exit_symbol.$caption." exit ->"."\r\n", $member_id);
	echo (json_encode($res, JSON_UNESCAPED_UNICODE));
?>