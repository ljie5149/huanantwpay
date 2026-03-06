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

	global $g_3party_url, $g_jtg_key_id, $g_verify_code, $g_terminalId, $g_txnCurrency, $g_channelCode, $g_show_request;
	$table = 'data_order';
	$reuqire_fields 	= ['orderNo','amount'];
	$input_fields 		= ['order_no', 'api','pay_time','pay_method','pay_status','amount','avalible','json_str','remark'];

    $who_call		= isset($_POST['who_call'   	]) ? $_POST['who_call'  	] : 'app'; // 誰呼叫
    $method	    	= isset($_POST['method'     	]) ? $_POST['method'    	] : ''	; // GET, POST, PUT, DELETE
    $operateSrc		= isset($_POST['operateSrc'		]) ? $_POST['operateSrc'	] : ''	; // 車號或會員
    $txnDir			= isset($_POST['txnDir'  		]) ? $_POST['txnDir' 		] : 'RQ'; // 
    $amount			= isset($_POST['amount'  		]) ? $_POST['amount' 		] : '0' ; // 
    $orderNo		= isset($_POST['orderNo'  		]) ? $_POST['orderNo' 		] : '' 	; // 
    $refundOrderNo	= isset($_POST['refundOrderNo'	]) ? $_POST['refundOrderNo'	] : '' 	; // 
    $txnCurrency	= isset($_POST['txnCurrency'  	]) ? $_POST['txnCurrency' 	] : $g_txnCurrency; // 
    $QRcode			= isset($_POST['QRcode'			]) ? $_POST['QRcode' 	 	] : ''	; //  
    $bankNo			= isset($_POST['bankNo'			]) ? $_POST['bankNo' 	 	] : ''	; // 
    $isOrderRefund	= isset($_POST['isOrderRefund'	]) ? $_POST['isOrderRefund'	] : 'Y'	; // 
	
    $require_param['orderNo'] = $orderNo;
	
	$api 		= $func 	= "JTG_refund";
	$api_name 	= $caption 	= "退款/取消訂單";
	$member_id 	= "JTG_TWPAY";
    
	$remote_ip		= get_remote_ip();
	$error 			= "";
	$res 			= array();
	$token_base64 	= "";
	$ret_msg 		= "";
	$user_token		= "";

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
		// ------------------------------------------------------------------------

		// entry
		$input_param['api'			] = $api;
		$input_param['api_zhtw'		] = $api_name;
		$input_param['avalible'		] = "1";

		$error = ""; $ret_msg = ""; $found_data = false;
		$db = new CXDB($remote_ip);
		$conn_res = $db->connect($link, $member_id, "");
		if ($conn_res["status"] == "true") {
			$process_year = intval(getDateTimeFormat("", "Y"));
			createTWpayTable($link, $process_year);
			
			// 取得訂單資料
            try {
				for ($iyear = $process_year; $iyear >= $g_start_year; $iyear--) {
					$table = "data_order_$iyear";
					
					$sql = "SELECT * FROM $table WHERE 1=1";
					$sql.= merge_sql_string_if_not_empty("order_no", $orderNo);
					// echo $sql."\n";
					if ($result = mysqli_query($link, $sql)) {
						if (mysqli_num_rows($result) > 0) {
							if ($row = mysqli_fetch_assoc($result)) {
								$scanType		= $row['scanType'	 ];
								$amount 		= $row['amount'		 ];
								$payMethod01	= $row['pay_method01'];
								if ($payMethod01 == "1") {
									$storeId		= $row['credit_storeId'];
									$endpointCode	= $row['credit_endpointCode'];
								} else {
									$storeId 		= $row['storeId'	 ];
									$endpointCode 	= $row['endpointCode'];
								}
								$txnSeqno 		= $row['Reference_No'];
								$found_data = true;
								$process_year = $iyear;
								break;
							}
						}
					}
				}
				JTG_wh_log($remote_ip, "$func API Entry : storeId = $storeId, endpointCode =$endpointCode", $member_id);
            } catch (Exception $e) { }
			if (!$found_data) {
				$res = result_message("false", "0x0204", "訂單 $orderNo 不存在，無法進行退款作業!", []);
				JTG_wh_log($remote_ip, "$func search data return :".$res['responseMessage'], $member_id);
				echo (json_encode($res, JSON_UNESCAPED_UNICODE));
				$input_param['resp_code'	] = protectSqlValue($link, $res['code']);
				$input_param['resp_msg'		] = protectSqlValue($link, $res['responseMessage']);
				$effect_row = $db->saveLog($link, $input_param, $ret_msg);
				exit;
			}
			$table = "data_order_$process_year";
    		$require_param['amount' ] = $amount;
			
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

			$url = $g_3party_url."refund";
			if ($isOrderRefund == "Y") {
				$requestData = [
					'txnDir'          => $txnDir,
					'storeId'         => $storeId,
					'orgEndpointCode' => $endpointCode,
					'terminalId'      => $g_terminalId,
					'txnDateTime'	  => date('YmdHis'),
					'orgOrderNumber'  => $orderNo,
					'orgCurrency'     => $txnCurrency,
					'orgAmt'		  => $amount."00",
					// 'txnSeqno'   	  => $bankNo,
					// 'txnFISCTac'	  => $QRcode,
					'isOrderRefund'	  => 'Y'
				];
			} else {
				$requestData = [
					'txnDir'          => $txnDir,
					'storeId'         => $storeId,
					'orgEndpointCode' => $endpointCode,
					'terminalId'      => $g_terminalId,
					'txnDateTime'	  => date('YmdHis'),
					'orgOrderNumber'  => $orderNo,
					'orgCurrency'     => $txnCurrency,
					'orgAmt'		  => $amount."00",
					'txnSeqno'   	  => $txnSeqno,
					// 'txnFISCTac'	  => $QRcode,
					'isOrderRefund'	  => $isOrderRefund
				];
			}

			// 產生 sign
			$requestData['sign'] = generateSign($requestData, $g_verify_code);
			$post_data = json_encode($requestData, JSON_UNESCAPED_UNICODE);
			JTG_wh_log($remote_ip, "$func API Request :$post_data", $member_id);
			if ($g_show_request) {
				echo "Request\n".$post_data."\n"."RESPONSE\n";
			}
			
			$result = callAPI($error, $url, $post_data, "POST", 25, false, getHuananHeader());
			if ($g_show_request) {
				echo "$result\n---------------------------------\n";
			}

			// 初始化參數
			try {
				$input_param['order_no'		] = $orderNo;
				$input_param['mode'			] = $mode;
				$input_param['storeId'		] = $storeId;
				$input_param['endpointCode'	] = $endpointCode;
				$input_param['amount'		] = $amount;
				$input_param['operate_src'	] = $operateSrc;
				$input_param['json_str'		] = protectSqlValue($link, $result);
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
			$respCode 		= isset($obj["respCode"			]) ? $obj["respCode"	 	] : '';
			$respDesc 		= isset($obj["respDesc"			]) ? $obj["respDesc"	 	] : '';
			$restCodeZhtw = hncbRespCodeMessage($respCode);
			// 初始化參數
			try {
				// saveLog用
				$input_param['resp_code'	] = $respCode;
				$input_param['resp_msg'		] = $restCodeZhtw;
				$input_param['twqr_resp_msg'] = protectSqlValue($link, $respDesc);
			} catch(Exception $e) {}

			// 紀錄至資料庫
			$effect_row = $db->saveLog($link, $input_param, $ret_msg);
			if (strlen($respCode) > 0) { // 成功
				if ($respCode == "0000" || $respCode == "4001") { // 成功
					// Do 更新訂單狀態
					$input_param['pay_status'] = "3";
					$input_param['pay_time'	 ] = getDateFormat6("");
					$input_param['pay_method'] = "8";
					$effect_row = $db->modifyDataOrder($link, $process_year, $input_param, $ret_msg);
					
					// echo "effect_row :".$effect_row."\n";
					if ($effect_row > 0) {
						// echo "modify success\n";
						$res = result_message("true", "0x0200", "succeed", []);
					} else {
						$res = result_message("false", "0x0205", "sql error :".$ret_msg, []);
					}
					$res = result_message("true", "0x0200", $message, $obj);
				} else {
					$res = result_message("false", "0x0202", "($respCode)$restCodeZhtw", $obj);
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