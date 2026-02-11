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

	global $g_start_year;
	global $g_3party_url, $g_jtg_key_id, $g_verify_code, $g_merchantNo, $g_terminalId, $g_txnCurrency, $g_channelCode, $g_setpaystatus_inquery, $g_getinvoice_inquery, $g_show_request;
	$table = 'data_order'.$g_start_year;
	$reuqire_fields 	= ['orderNo'];
	$input_fields 		= ['order_no', 'api','pay_time','pay_method','pay_status','amount','avalible','json_str','remark'];

    $who_call		= isset($_POST['who_call'   ]) ? $_POST['who_call'  	] : 'app'; // 誰呼叫
    $method	    	= isset($_POST['method'     ]) ? $_POST['method'    	] : ''	; // GET, POST, PUT, DELETE
    $operateSrc		= isset($_POST['operateSrc'	]) ? $_POST['operateSrc'	] : ''	; // 車號或會員
    $txnDir			= isset($_POST['txnDir'  	]) ? $_POST['txnDir' 		] : 'RQ'; // 
    $orderNo		= isset($_POST['orderNo'  	]) ? $_POST['orderNo' 		] : '' 	; // 
    $txnCurrency	= isset($_POST['txnCurrency']) ? $_POST['txnCurrency'	] : $g_txnCurrency; // 
	
    $require_param['orderNo'] = $orderNo;
	

	$api 		= $func 	= "JTG_inquery";
	$api_name 	= $caption 	= "查詢訂單";
	$member_id 	= "JTG_TWPAY";
    
	$remote_ip		= get_remote_ip();
	$error 			= "";
	$res 			= array();
	$token_base64 	= "";
	$ret_msg 		= "";
	$user_token		= "";

	$test02 = 0;
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
		$error = ""; $ret_msg = ""; $mode = ""; $storeId = ""; $endpointCode = ""; $amount = ""; $found_data = false;

		$input_param['order_no'		] = $orderNo;
		$input_param['api'			] = $api;
		$input_param['api_zhtw'		] = $api_name;
		$input_param['avalible'		] = "1";
		$process_year = 0;
		$db = new CXDB($remote_ip);
		$conn_res = $db->connect($link, $member_id, "");
		if ($conn_res["status"] == "true") {
			$process_year = intval(getDateTimeFormat("", "Y"));
			createTWpayTable($link, $process_year);

			for ($iyear = $process_year; $iyear >= $g_start_year; $iyear--) {
				$table = "data_order_$iyear";
				$sql = "SELECT * FROM $table WHERE 1=1";
				$sql.= merge_sql_string_if_not_empty("order_no", $orderNo);
				// echo $sql."\n";
				try {
					if ($result = mysqli_query($link, $sql)) {
						if (mysqli_num_rows($result) > 0) {
							if ($row = mysqli_fetch_assoc($result)) {
								$operate_src		= $row['operate_src'];
								$mode 				= $row['mode'];
								$amount 			= $row['amount'];
								$storeId 			= $row['storeId'];
								$endpointCode 		= $row['endpointCode'];
								$parking_url 		= $row['parking_url'];
								$parking_id 		= $row['parking_id'];
								$discount 			= $row['discount'];
								$pay_log 			= $row['pay_log'];
								$bill_no 			= $row['bill_no'];
								$realpay 			= $row['realpay'] ?? "";
								$payment_order_no	= $row['payment_order_no'] ?? "";
								$pay_method			= $row['pay_method'] ?? "";

								$carrierId1			= $row['carrierId1'] ?? "";
								$carrierId2			= $row['carrierId2'] ?? "";
								$email				= $row['email'] ?? "";
								$carrierType		= $row['carrierType'] ?? "";
								$loveId				= $row['loveId'] ?? "";
								
								$found_data = true;
								$process_year = $iyear;
								break;
							}
						}
					}
					JTG_wh_log($remote_ip, "$func API Entry :mode = ($mode)$modezhtw, storeId = $storeId, endpointCode =$endpointCode", $member_id);
				} catch (Exception $e) { }
			}
			if (!$found_data) {
				$res = result_message("false", "0x0204", "訂單 $orderNo 不存在，請確認是否使用TWQR繳費!", []);
				JTG_wh_log($remote_ip, "$func search data return :".$res['responseMessage'], $member_id);
				echo (json_encode($res, JSON_UNESCAPED_UNICODE));
				$input_param['resp_code'	] = $res['code'];
				$input_param['resp_msg'		] = $res['responseMessage'];
				$effect_row = $db->saveLog($link, $input_param, $ret_msg);
				exit;
			}
			$table = "data_order_$process_year";

			$url = $g_3party_url."inquery";
			$requestData = [
				'txnDir'          => $txnDir,
				'storeId'         => $storeId,
				'endpointCode'    => $endpointCode,
				'terminalId'      => $g_terminalId,
				'txnCurrency'     => $txnCurrency,
				'orderNumber'  	  => $orderNo,
				'inqDateTime'	  => getDateFormat6('', 'Ymd')
			];

			// 產生 sign
			$requestData['sign'] = generateSign($requestData, $g_verify_code);
			$post_data = json_encode($requestData, JSON_UNESCAPED_UNICODE);
			JTG_wh_log($remote_ip, "$func API Request :$post_data", $member_id);
			if ($g_show_request) {
				echo "Request\n".$post_data."\n"."RESPONSE\n";
			}
			
			$result = callAPI($error, $url, $post_data, "POST", 30, false, getHuananHeader());
			if ($g_show_request) {
				echo "$result\n---------------------------------\n";
			}
			// ------------------------------------------------------------------------

			// 初始化參數
			try {
				// saveLog用
				$input_param['request'		] = protectSqlValue($link, $post_data);
				$input_param['response'		] = protectSqlValue($link, $result);
			} catch(Exception $e) {}

			if (!empty($error)) {
				$input_param['remark'] = protectSqlValue($link, "ERROR :".$error);
				$effect_row = $db->saveLog($link, $input_param, $ret_msg);
				$res = result_message("false", "0x020E", "API return :$error", []);
				JTG_wh_log($remote_ip, "$func API return :$error", $member_id);
				echo (json_encode($res, JSON_UNESCAPED_UNICODE));
				exit;
			}

			// ------------------------------------------------------------------------
			JTG_wh_log($remote_ip, "$func $caption :$result", $member_id);
			$obj 		= json_decode($result, true);
			$txnDir 		= isset($obj["txnDir"			]) ? $obj["txnDir"	 		] : '';
			$txnType 		= isset($obj["txnType"			]) ? $obj["txnType"	 		] : '';
			$respCode 		= isset($obj["respCode"			]) ? $obj["respCode"	 	] : '';
			$respDesc 		= isset($obj["respDesc"			]) ? $obj["respDesc"	 	] : '';
			$restCodeZhtw = hncbRespCodeMessage($respCode);
			$txnTypeZhtw  = hncbTxnTypeMessage($respCode, $txnType);
			// 初始化參數
			try {
				$obj['txnTypeDesc'		 ] = $txnTypeZhtw;

				// saveLog用
				$input_param['resp_msg'		] = protectSqlValue($link, $respDesc);
				$input_param['resp_code'	] = $respCode;
				$input_param['resp_msg'		] .= "，".$restCodeZhtw;
				$input_param['txnType_code'	] = $txnType;
				$input_param['txnType_msg'	] = protectSqlValue($link, $txnTypeZhtw);
			} catch(Exception $e) {}

			$SetPayStatus_json = ""; $GetInvoice_json = "";
			$input_param['res_set_pay_status'] = $SetPayStatus_json;
			$input_param['res_invoice_status'] = $GetInvoice_json;

			// 紀錄至資料庫
			$restCodeZhtw = hncbRespCodeMessage($respCode);
			$effect_row = $db->saveLog($link, $input_param, $ret_msg);
			if (strlen($respCode) > 0) { // 成功
				if ($respCode == "0000" || $respCode == "4001") { // 成功
					$Reference_No = isset($obj["txnSeqno"]) ? $obj["txnSeqno"] : '';
					// echo "Reference_No :$Reference_No\n";
					// Do 更新訂單狀態
					$input_param['Reference_No'] = $Reference_No;
					$input_param['pay_status'] = "1";
					$input_param['pay_time'	 ] = getDateFormat6("");
					$input_param['pay_method'] = "8";
					$input_param['pay_source'] = "3";
					$effect_row = $db->modifyDataOrder($link, $process_year, $input_param, $ret_msg);

					if ($g_setpaystatus_inquery) {
						// 寫回停管
						if (strlen($realpay) == 0) $relpay = $amount;
						$pay_method = "8"; $pay_source = "3";
						if ($found_data) {
							$req_statusjson = "";
							$SetPayStatus_json 	= setPayStatus($req_statusjson, $parking_url, $parking_id, $orderNo, $realpay, $discount, "1", $pay_log, $bill_no, $payment_order_no, $pay_method, "2");
							$obj4SetPayStatus	= json_decode($SetPayStatus_json, true);
							JTG_wh_log($remote_ip, "$func - [setPayStatus] API return :$SetPayStatus_json", $member_id);
							$input_param['api_zhtw'		] = $api_name."[設定停管訂單狀態]";
							$input_param['request'		] = protectSqlValue($link, $req_statusjson);
							$input_param['response'		] = protectSqlValue($link, $SetPayStatus_json);
							$input_param['resp_code'	] = isset($obj4SetPayStatus['code']) ? $obj4SetPayStatus['code'] : "";
							if ($obj4SetPayStatus['code'] == "0x0200") { // 設定訂單狀態成功
								$input_param['resp_msg'		] .= "，設定停管訂單狀態成功";
								$input_param['set_pay_status'] = "1";
								if ($g_getinvoice_notify) {
									$req_invoicejson = "";
									$GetInvoice_json 	= getInvoice($req_invoicejson, $url, $parking_id, $orderNo, $operate_src, $amount, $carrierId1, $carrierId2, $email, $carrierType, $loveId);
									$obj4GetInvoice		= json_decode($SetPayStatus_json, true);
									JTG_wh_log($remote_ip, "$func - [getInvoice] API return :$GetInvoice_json", $member_id);
									$input_param['api_zhtw'		] = $api_name."[取得發票]";
									$input_param['invoice_status'] = "0";
									$input_param['request'		] = protectSqlValue($link, $req_invoicejson);
									$input_param['response'		] = protectSqlValue($link, $GetInvoice_json);
									$input_param['resp_code'	] = isset($obj4GetInvoice['code']) ? $obj4GetInvoice['code'] : "";
									if ($obj4GetInvoice['code'] == "0x0200") { // 設定訂單狀態成功
										$input_param['invoice_status'] = "1";
										$input_param['resp_msg'		] .= "，設定發票成功";
									} else {
										$input_param['invoice_status'] = "0";
										$input_param['resp_msg'		] .= "，設定發票失敗";
									}
									$effect_row = $db->saveLog($link, $input_param, $ret_msg);
								}
							} else { // 設定訂單狀態異常
								$input_param['resp_msg'		] .= "，設定停管訂單狀態失敗";
								$input_param['set_pay_status'] = "0";
								if ($g_getinvoice_inquery) {
									$input_param['invoice_status'] = "0";
									$input_param['api_zhtw'		] = $api_name."[設定停管訂單狀態]";
									$input_param['response'		] = protectSqlValue($link, $SetPayStatus_json);
								}
							}
							$input_param['res_set_pay_status'] = $SetPayStatus_json;
							$input_param['res_invoice_status'] = $GetInvoice_json;
							$effect_row = $db->modifyDataOrder($link, $process_year, $input_param, $ret_msg, true);

							$data['SetPayStatus_json'] = $SetPayStatus_json;
							$data['GetInvoice_json'  ] = $GetInvoice_json;
							$res = result_message("true", "0x0200", $input_param['resp_msg'], $data);
							$effect_row = $db->saveLog($link, $input_param, $ret_msg);
						} else {
							$res = result_message("false", "0x0204", "資料不存在，無法進行變更訂單狀態與發票作業!", []);
							$effect_row = $db->saveLog($link, $input_param, $ret_msg);
						}
					} else {
						$res = result_message("true", "0x0200", "成功(未觸發停管)", $data);
						$input_param['resp_msg'		] .= "，成功(未觸發停管)";
						$effect_row = $db->saveLog($link, $input_param, $ret_msg);
					}
					// // echo "effect_row :".$effect_row."\n";
					// if ($effect_row > 0) {
					// 	// echo "modify success\n";
					// 	$res = result_message("true", "0x0200", "succeed", []);
					// } else {
					// 	$res = result_message("false", "0x0205", "sql error :".$ret_msg, []);
					// }
					$res = result_message("true", "0x0200", $message, $obj);
				} else {
					$res = result_message("false", "0x020E", "$restCodeZhtw($respCode)", $obj);
				}
			} else { // 異常
				$res = result_message("false", "0x020E", "API return :未知錯誤$respCode", $obj);
			}
		} else {
			$res = result_message("false", "0x0205", "connect to mysql error :".$result, []);
		}
	} catch (Exception $e) {
		$res = result_message("false", "0xE209", "Exception error! error detail:".$e->getMessage(), []);
		JTG_wh_log_Exception($remote_ip, $func ." ".get_error_symbol($data["status_code"]).$data["status_code"]." ".$data["responseMessage"], $member_id);
	} finally {
		$data_close_conn = close_connection_finally($link, $remote_ip, $member_id);
		if ($data_close_conn["status"] == "false") $data = $data_close_conn;
	}
	
	JTG_wh_log($remote_ip, $func ." ".get_error_symbol($res["status_code"])." result :".$res["responseMessage"]."\r\n".$g_exit_symbol.$caption." exit ->"."\r\n", $member_id);
	echo (json_encode($res, JSON_UNESCAPED_UNICODE));
?>