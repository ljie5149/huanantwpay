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
	global $g_3party_url, $g_jtg_key_id, $g_verify_code, $g_terminalId, $g_txnCurrency, $g_channelCode, $g_show_request;
	global $g_storeId4debitcard, $g_endpointCode4debitcard, $g_storeId4credit, $g_endpointCode4credit;
	$table = 'data_order';
	$reuqire_fields 	= ['orderNo','amount','payment_order_no','bill_no','PosArea','parking_id','device_id'];
	$input_fields 		= ['order_no', 'api','pay_time','pay_method','pay_status','amount','avalible','json_str','remark'];

    $who_call			= isset($_POST['who_call'   		]) ? $_POST['who_call'   		] : 'app'			; // 誰呼叫
    $method	    		= isset($_POST['method'     		]) ? $_POST['method'     		] : ''				; // GET, POST, PUT, DELETE
    $operateSrc			= isset($_POST['operateSrc'			]) ? $_POST['operateSrc' 		] : ''				; // 車號或會員
    $txnDir				= isset($_POST['txnDir'  			]) ? $_POST['txnDir' 	 		] : 'RQ'			; // 
    $amount				= isset($_POST['amount'  			]) ? $_POST['amount' 	 		] : '0' 			; // 
    $orderNo			= isset($_POST['orderNo'  			]) ? $_POST['orderNo' 	 		] : '' 				; // 
    $txnCurrency		= isset($_POST['txnCurrency'		]) ? $_POST['txnCurrency'		] : $g_txnCurrency	; // 
    $channelCode		= isset($_POST['channelCode'		]) ? $_POST['channelCode'		] : $g_channelCode	; // 
    $expirySeconds		= isset($_POST['expirySeconds'		]) ? $_POST['expirySeconds'		] : "300"	; //

    $payment_order_no	= isset($_POST['payment_order_no'  	]) ? $_POST['payment_order_no' 	] : '' 				; // 繳費機訂單號碼
    $pay_log			= isset($_POST['pay_log'  			]) ? $_POST['pay_log' 	 		] : 'TwPay主掃'	 	; // 支付log
    $realpay			= isset($_POST['realpay'  			]) ? $_POST['realpay' 	 		] : '' 				; // 實繳金額
    $discount			= isset($_POST['discount'  			]) ? $_POST['discount' 	 		] : '0' 			; // 折扣金額
    $bill_no			= isset($_POST['bill_no'  			]) ? $_POST['bill_no' 	 		] : '' 				; // 帳務序號
    $PosArea			= isset($_POST['PosArea'  			]) ? $_POST['PosArea' 	 		] : '' 				; // 場域代碼
    $parking_id			= isset($_POST['parking_id'			]) ? $_POST['parking_id' 		] : '' 				; // 停車場代碼
    $device_id			= isset($_POST['device_id'			]) ? $_POST['device_id'  		] : '' 				; // 設備Id

    $carrierId1			= isset($_POST['carrierId1'  		]) ? $_POST['carrierId1' 	 	] : '' 				; // 悠遊卡：(免填) 共通用載具：手機條碼 自然人憑證：憑證號碼
    $carrierId2			= isset($_POST['carrierId2'  		]) ? $_POST['carrierId2' 	 	] : '' 				; // 悠遊卡：(免填) 共通用載具：手機條碼 自然人憑證：憑證號碼
    $email				= isset($_POST['email'  			]) ? $_POST['email' 	 		] : '' 				; // 電子信箱
    $companyNo			= isset($_POST['companyNo'  		]) ? $_POST['companyNo' 	 	] : '' 				; // 統一編號
    $carrierType		= isset($_POST['carrierType'  		]) ? $_POST['carrierType' 	 	] : '' 				; // 載具類別0: 悠遊卡 1: 通用載具(手機) 2: 自然人憑證
    $loveId				= isset($_POST['loveId'  			]) ? $_POST['loveId' 	 		] : '' 				; // 愛心碼
    $parking_url		= isset($_POST['parking_url'		]) ? $_POST['parking_url'		] : '' 				; // 停管url(設定訂單狀態與開發票)
	
    $skip_mode			= isset($_POST['skip_mode'			]) ? $_POST['skip_mode'			] : '0'				; // 0:支援金融、信用卡; 1:僅支援金融卡; 2:僅支援信用卡; 3:都不支援
    $display_req_res	= isset($_POST['display_req_res'	]) ? $_POST['display_req_res'	] : '0'				;

    $require_param['orderNo'			] = $orderNo;
    $require_param['amount' 			] = $amount;
    $require_param['payment_order_no' 	] = $payment_order_no;
    $require_param['bill_no' 			] = $bill_no;
    $require_param['PosArea' 			] = $PosArea;
    $require_param['parking_id' 		] = $parking_id;
    $require_param['device_id' 			] = $device_id;

	$api 		= $func 	= "JTG_preorder";
	$api_name 	= $caption 	= "主掃(預下單)";
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
			JTG_wh_log($remote_ip, "$func API return :".$res['responseMessage'], $member_id);
			echo (json_encode($res, JSON_UNESCAPED_UNICODE));
			exit;
		}
		// ------------------------------------------------------------------------
		// $storeId 		= getStoreId($mode);
		// $endpointCode 	= getEndpointCode($mode);
		// $modezhtw 		= getPaymodeZhtw($mode);
		// JTG_wh_log($remote_ip, "$func API Entry :mode = ($mode)$modezhtw, storeId = $storeId, endpointCode =$endpointCode", $member_id);
		// echo "mode :$mode\n";
		// echo "storeId :$storeId\n";
		// echo "endpointCode :$endpointCode\n";
		// return;

		// entry
		$error = ""; $ret_msg = ""; $found_data = false;
		$db = new CXDB($remote_ip);
		$conn_res = $db->connect($link, $member_id, "");
		if ($conn_res["status"] == "true") {
			$process_year = intval(getDateTimeFormat("", "Y"));
			createTWpayTable($link, $process_year);

			for ($iyear = $process_year; $iyear >= $g_start_year; $iyear--) {
				$table = "data_order_$iyear";

				$sql = "SELECT * FROM $table WHERE 1=1";
				$sql.= merge_sql_string_if_not_empty("order_no", $orderNo);
				// $sql.= merge_sql_string_if_not_empty("pay_status", "1");
				if ($result = mysqli_query($link, $sql)) {
					if (mysqli_num_rows($result) > 0) {
						if ($row = mysqli_fetch_assoc($result)) {
							$pay_status = $row['pay_status'];
							if ($pay_status == "1") {
								$res = result_message("false", "0x0205", "訂單 $orderNo 已存在且已付款!", []);
								JTG_wh_log($remote_ip, "$func search data return :".$res['responseMessage'], $member_id);
								echo (json_encode($res, JSON_UNESCAPED_UNICODE));
								exit;
							}
							$found_data = true;
							$process_year = $iyear;
							break;
						}
					}
				}
			}
			$table = "data_order_$process_year";

			$url = $g_3party_url."preOrder";

			// 先初始化一個空陣列
			$requestData = [];

			// 一個一個加入欄位
			$requestData['txnDir'					]	= $txnDir;
			$requestData['channelCode'				]	= $channelCode;
			if ($skip_mode == "0" || $skip_mode == "1") {
				$requestData['storeId'				]	= $g_storeId4debitcard;
				$requestData['endpointCode'			]	= $g_endpointCode4debitcard;
			}
			if ($skip_mode == "0" || $skip_mode == "2") {
				$requestData['creditStoreId'		]	= $g_storeId4credit;
				$requestData['creditEndpointCode'	] 	= $g_endpointCode4credit;
			}
			$requestData['terminalId'				]	= $g_terminalId;
			$requestData['txnOrderNumber'			]	= $orderNo;
			$requestData['txnAmt'					]	= $amount . "00"; // 注意這裡做了字串拼接
			$requestData['txnCurrency'				]	= $txnCurrency;

			// 加入帶有函數運算的欄位
			$requestData['expiryDate'				]	= getDate4Nseconds(intval($expirySeconds), "", 'YmdHis');

			// 產生 sign
			// echo generateSign($requestData, $g_verify_code)."\n";
			$requestData['sign'] = generateSign($requestData, $g_verify_code);
			$post_data = json_encode($requestData, JSON_UNESCAPED_UNICODE);
			JTG_wh_log($remote_ip, "$func API Request :$post_data", $member_id);
			if ($display_req_res == "1") {
				echo "Request\n".$post_data."\n"."RESPONSE\n";
			}

			$result = callAPI($error, $url, $post_data, "POST", 30, false, getHuananHeader());
			if ($display_req_res == "1") {
				echo "$result\n---------------------------------\n";
			}
			
			// 初始化參數
			try {
				$input_param['order_no'				] = $orderNo;
				$input_param['scanType'				] = "A"; // A：主掃，B：被掃
				$input_param['storeId'				] = $g_storeId4debitcard;
				$input_param['endpointCode'			] = $g_endpointCode4debitcard;
				$input_param['credit_storeId'		] = $g_storeId4credit;
				$input_param['credit_endpointCode'	] = $g_endpointCode4credit;
				$input_param['amount'				] = $amount;
				$input_param['operate_src'			] = $operateSrc;
				
				$input_param['payment_order_no'		] = $payment_order_no;
				$input_param['pay_log'				] = $pay_log;
				$input_param['realpay'				] = $realpay;
				$input_param['discount'				] = $discount;
				$input_param['bill_no'				] = $bill_no;
				$input_param['PosArea'				] = $PosArea;
				$input_param['parking_id'			] = $parking_id;
				$input_param['device_id'			] = $device_id;
				
				$input_param['carrierId1'			] = $carrierId1;
				$input_param['carrierId2'			] = $carrierId2;
				$input_param['email'				] = $email;
				$input_param['companyNo'			] = $companyNo;
				$input_param['carrierType'			] = $carrierType;
				$input_param['loveId'				] = $loveId;
				$input_param['parking_url'			] = $parking_url;

				$input_param['api'					] = $api;
				$input_param['api_zhtw'				] = $api_name;
				$input_param['avalible'				] = "1";
				$input_param['json_str'				] = protectSqlValue($link, $result);
				// saveLog用
				$input_param['request'				] = protectSqlValue($link, $post_data);
				$input_param['response'				] = protectSqlValue($link, $result);
			} catch(Exception $e) {}

			// echo getHuananHeader()."\n";
			// echo $result."\n";
			// exit;
			if (!empty($error)) {
				$input_param['remark'] = protectSqlValue($link, "ERROR :".$error);
				$effect_row = $db->saveLog($link, $input_param, $ret_msg);
				$res = result_message("false", "0x020E", "API return :$error", []);
				JTG_wh_log($remote_ip, "$func API response :$error", $member_id);
				echo (json_encode($res, JSON_UNESCAPED_UNICODE));
				exit;
			}

			// ------------------------------------------------------------------------
			JTG_wh_log($remote_ip, "$func $caption Response :$result", $member_id);
			$obj 		= json_decode($result, true);
			$txnOrderNumber = isset($obj["txnOrderNumber"	]) ? $obj["txnOrderNumber"	] : "";
			$txnDir 		= isset($obj["txnDir"			]) ? $obj["txnDir"	 		] : '';
			$respCode 		= isset($obj["respCode"			]) ? $obj["respCode"	 	] : '';
			$respDesc 		= isset($obj["respDesc"			]) ? $obj["respDesc"	 	] : '';
			$restCodeZhtw = hncbRespCodeMessage($respCode);

			// 預設停管設定訂單狀態與發票
			$SetPayStatus_json = ""; $GetInvoice_json = "";
			$input_param['res_set_pay_status'] = $SetPayStatus_json;
			$input_param['res_invoice_status'] = $GetInvoice_json;

			// 初始化參數
			try {
				// saveLog用
				$input_param['resp_code'	] = $respCode;
				$input_param['resp_msg'		] = $restCodeZhtw;
				$input_param['twqr_resp_msg'] = protectSqlValue($link, $respDesc);
			} catch(Exception $e) {}

			$effect_row = $db->modifyDataOrder($link, $process_year, $input_param, $ret_msg);
			// echo $effect_row."\n";
			// return;

			// 紀錄至資料庫
			// $effect_row = $db->saveLog($link, $input_param, $ret_msg);
			if (strlen($respCode) > 0) { // 成功
				if ($respCode == "0000") { // 成功
					// Do 建立或更新訂單資訊
					// $effect_row = $db->modifyDataOrder($link, $input_param, $ret_msg);
					
					// echo "effect_row :".$effect_row."\n";
					// if ($effect_row > 0) {
						// echo "modify success\n";
						$res = result_message("true", "0x0200", "succeed", $obj);
					// } else {
					// 	$res = result_message("false", "0x0205", "sql error :".$ret_msg, []);
					// }
					// $res = result_message("true", "0x0200", $message, $obj);
				} else {
					$res = result_message("false", "0x0202", "($respCode)$restCodeZhtw", $obj);
				}
			} else { // 異常
				$res = result_message("false", "0x020E", "API return :未知錯誤$respCode", $obj);
			}
		} else {
			$res = result_message("false", "0x0205", "connect to mysql error :".json_encode($conn_res), []);
		}
	} catch (Exception $e) {
		$res = result_message("false", "0xE209", "Exception error! error detail:".$e->getMessage(), []);
		JTG_wh_log_Exception($remote_ip, $func ." ".get_error_symbol($res["status_code"]).$res["status_code"]." ".$res["responseMessage"], $member_id);
	} finally {
		$data_close_conn = close_connection_finally($link, $remote_ip, $member_id);
		if ($data_close_conn["status"] == "false") $data = $data_close_conn;
	}
	
	JTG_wh_log($remote_ip, $func ." ".get_error_symbol($res["status_code"])." result :".$res["responseMessage"]."\r\n".$g_exit_symbol.$caption." exit ->"."\r\n", $member_id);
	echo (json_encode($res, JSON_UNESCAPED_UNICODE));
?>