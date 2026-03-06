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
	global $g_3party_url, $g_terminalNo;
	$table = 'data_order';
    
    header('Content-Type: application/json');
    // 假設前端送 JSON 格式
    $json = file_get_contents("php://input");
	JTG_wh_log("notify", "json :$json", "", "JTG_notify");

    // // 看門狗
    // $role = ""; $order_limit = 0;
    // // if (!empty($json_token)) {
    //     $data = validToken($json_token, $member_id, $role, $order_limit);
    //     if ($data["status"] == "false") {
    //         echo (json_encode($data, JSON_UNESCAPED_UNICODE));
    //         return;
    //     }
    // // }
	/*
	json :{
		"orderNumber":"C20260204001",
		"txnSalesAcct":"",
		"txnRefundSeqno":"",
		"txnCharge":"2600000",
		"txnCurrency":"901",
		"sign":"48b19790d994e26120da4186e399581fd7bd88787fb666180a110bca7a18f1f4",
		"txnType":"2541",
		"terminalId":"",
		"carrierId1":"",
		"carrierId2":"",
		"txnDateTime":"20260204110319",
		"txnAccNO":"30061",
		"isPartRefund":"",
		"txnFeeInfo":"",
		"respDesc":"交易成功",
		"endpointCode":"00000001",
		"carrierType":"",
		"orgOrderNumber":"",
		"storeId":"008537589950001",
		"storeMemo":"",
		"txnAcctDate":"20260204",
		"txnFeeName":"",
		"scanType":"A",
		"txnSeqno":"20260204110318297204",
		"txnBillRefID":"",
		"txnAmt":"1",
		"respCode":"4001"
		}
	*/
	
	try {
		$error = ""; $ret_msg = ""; $mode = ""; $storeId = ""; $endpointCode = ""; $amount = "";
		$found_data = false;
		
		$api 		= $func 	= "JTG_notify";
		$api_name 	= $caption 	= "銀行轉址回傳訊息";
		$member_id 	= "JTG_TWPAY";
		
		$obj 		= json_decode($json, true);
		if ($g_show_request) {
			echo "$json\n---------------------------------\n";
		}

		$pay_method01 = 0;
		$db = new CXDB($remote_ip);
		$conn_res = $db->connect($link, $member_id, "");
		if ($conn_res["status"] == "true") {
			$process_year = intval(getDateTimeFormat("", "Y"));
			createTWpayTable($link, $process_year);

			// 初始化參數
			try {
				$orderNo 		= isset($obj["orderNumber"		]) ? $obj["orderNumber"		] : '';
				$r_storeId 		= isset($obj["storeId"			]) ? $obj["storeId"			] : '';
				$r_endpointCode	= isset($obj["endpointCode"		]) ? $obj["endpointCode"	] : '';
				$scanType 		= isset($obj["scanType"			]) ? $obj["scanType"	 	] : '';
				$txnDir 		= isset($obj["txnDir"			]) ? $obj["txnDir"	 		] : '';
				$respCode 		= isset($obj["respCode"			]) ? $obj["respCode"	 	] : '';
				$respDesc 		= isset($obj["respDesc"			]) ? $obj["respDesc"	 	] : '';
				$restCodeZhtw = hncbRespCodeMessage($respCode);

				$input_param['order_no'		] = $orderNo;
				$input_param['scanType'		] = $scanType;
				$input_param['api'			] = $api;
				$input_param['api_zhtw'		] = $api_name;
				$input_param['resp_code'	] = $respCode;
				$input_param['resp_msg'		] = $restCodeZhtw;
				$input_param['twqr_resp_msg'] = protectSqlValue($link, $respDesc);
				
				// saveLog用
				$input_param['request'		] = protectSqlValue($link, "銀行端觸發回傳訊息");
				$input_param['response'		] = protectSqlValue($link, $json);
				
				$SetPayStatus_json = ""; $GetInvoice_json = "";
				$input_param['res_set_pay_status'] = $SetPayStatus_json;
				$input_param['res_invoice_status'] = $GetInvoice_json;
			} catch(Exception $e) {}

			if (empty($orderNo)) {
				$error = "ERROR :訂單號碼為空的";
				$input_param['remark'] = protectSqlValue($link, "ERROR :$error");
				$effect_row = $db->saveLog($link, $input_param, $ret_msg);
				$res = result_message("false", "0x020E", "API return :$error", []);
				JTG_wh_log($remote_ip, "$func API return :$error", $member_id);
				echo (json_encode($res, JSON_UNESCAPED_UNICODE));
				exit;
			}

			for ($iyear = $process_year; $iyear >= $g_start_year; $iyear--) {
				$table = "data_order_$iyear";
				$sql = "SELECT * FROM $table WHERE 1=1";
				$sql.= merge_sql_string_if_not_empty("order_no", $orderNo);
				// echo $sql."\n";
				try {
					if ($result = mysqli_query($link, $sql)) {
						if (mysqli_num_rows($result) > 0) {
							if ($row = mysqli_fetch_assoc($result)) {
								$operate_src			= $row['operate_src'];
								$amount 				= $row['amount'];
								$storeId 				= $row['storeId'];
								$endpointCode 			= $row['endpointCode'];
								$credit_storeId			= $row['credit_storeId'];
								$credit_endpointCode	= $row['credit_endpointCode'];
								$parking_url 			= $row['parking_url'];
								$parking_id 			= $row['parking_id'];
								$discount 				= $row['discount'];
								$pay_log 				= $row['pay_log'];
								$bill_no 				= $row['bill_no'];
								$realpay 				= $row['realpay'] ?? "";
								$payment_order_no		= $row['payment_order_no'] ?? "";
								$pay_method				= $row['pay_method'] ?? "";

								$carrierId1				= $row['carrierId1'] ?? "";
								$carrierId2				= $row['carrierId2'] ?? "";
								$email					= $row['email'] ?? "";
								$carrierType			= $row['carrierType'] ?? "";
								$loveId					= $row['loveId'] ?? "";
								if ($credit_storeId == $r_storeId && $credit_endpointCode == $r_endpointCode) $pay_method01 = 1;
								if ($storeId == $r_storeId && $endpointCode == $r_endpointCode) $pay_method01 = 2;
								
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
				$res = result_message("false", "0x0204", "銀行端notify轉址失敗!! 訂單 $orderNo 不存在，請確認是否使用TWQR繳費!", $json);
				JTG_wh_log($remote_ip, "$func search data return :".$res['responseMessage'], $member_id);
				echo (json_encode($res, JSON_UNESCAPED_UNICODE));
				$input_param['resp_code'	] = protectSqlValue($link, $res['code']);
				$input_param['resp_msg'		] = protectSqlValue($link, $res['responseMessage']);
				$effect_row = $db->saveLog($link, $input_param, $ret_msg);
				exit;
			}
			$table = "data_order_$process_year";


			// ------------------------------------------------------------------------
			// 紀錄至資料庫
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
					$input_param['pay_method01'] = $pay_method01;
					$input_param['pay_source'] = "3";
					$effect_row = $db->modifyDataOrder($link, $process_year, $input_param, $ret_msg, true);

					if ($g_setpaystatus_notify) {
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