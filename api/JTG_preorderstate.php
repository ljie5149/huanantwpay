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
	global $g_txnCurrency, $g_channelCode;
	$table = 'data_order';
	$reuqire_fields 	= ['orderNo'];

    $who_call		= isset($_POST['who_call'   ]) ? $_POST['who_call'  ] : 'app'; // 誰呼叫
    $method	    	= isset($_POST['method'     ]) ? $_POST['method'    ] : ''; // GET, POST, PUT, DELETE
    $operateSrc		= isset($_POST['operateSrc'	]) ? $_POST['operateSrc'] : ''; // 車號或會員
    $amount			= isset($_POST['amount'  	]) ? $_POST['amount' 	] : '0' ; // 
    $orderNo		= isset($_POST['orderNo'  	]) ? $_POST['orderNo' 	] : '' ; // 
    $txnCurrency	= isset($_POST['txnCurrency'  	]) ? $_POST['txnCurrency' 	] : $g_txnCurrency; // 
    $channelCode	= isset($_POST['channelCode'  	]) ? $_POST['channelCode' 	] : $g_channelCode; //

	$require_param['orderNo'] = $orderNo;
	
	$api 		= $func 	= "JTG_preorderstete";
	$api_name 	= $caption 	= "JTG 取得觸發主掃(預下單)後狀態[檢查訂單狀態]";
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
			JTG_wh_log($remote_ip, "$func API return :$error", $member_id);
			echo (json_encode($res, JSON_UNESCAPED_UNICODE));
			exit;
		}
		// ------------------------------------------------------------------------

		// entry
		$error = ""; $ret_msg = ""; $found_data = false;
		$orders = [];

		$input_param['api'					] = $api;
		$input_param['api_zhtw'				] = $api_name;
		$input_param['operate_src'			] = '';
		$input_param['resp_code'			] = '';
		$input_param['resp_msg'				] = '';
		$input_param['twqr_resp_msg'		] = '';

		$input_param['storeId'				] = '';
		$input_param['endpointCode'			] = '';
		$input_param['credit_storeId'		] = '';
		$input_param['credit_endpointCode'	] = '';
		$input_param['scanType'				] = '';
		$input_param['Reference_No'			] = '';
		$input_param['pay_status'			] = '';
		$input_param['pay_time'	 			] = '';
		$input_param['pay_method'			] = '';
		$input_param['pay_source'			] = '';
			
		// 紀錄至資料庫
		$db = new CXDB($remote_ip);
		$conn_res = $db->connect($link, $member_id, "");
		if ($conn_res["status"] == "true") {
			$process_year = intval(getDateTimeFormat("", "Y"));
			createTWpayTable($link, $process_year);

			for ($iyear = $process_year; $iyear >= $g_start_year; $iyear--) {
				$table = "data_order_$iyear";

				$sql = "SELECT * FROM $table WHERE 1=1";
				$sql.= merge_sql_string_if_not_empty("order_no", $orderNo);
				$sql.= merge_sql_string_if_not_empty("amount", $amount);
				try {
					if ($result = mysqli_query($link, $sql)) {
						if (mysqli_num_rows($result) > 0) {
							while ($row = mysqli_fetch_assoc($result)) {
								$input_param['operate_src'			] = $row['operate_src'			];
								$input_param['storeId'				] = $row['storeId'				];
								$input_param['endpointCode'			] = $row['endpointCode'			];
								$input_param['credit_storeId'		] = $row['credit_storeId'		];
								$input_param['credit_endpointCode'	] = $row['credit_endpointCode'	];
								$input_param['scanType'				] = $row['scanType'				];
								$input_param['Reference_No'			] = $row['Reference_No'			];
								
								$input_param['resp_code'			] = $row['resp_code'			];
								$input_param['resp_msg'				] = $row['resp_msg'				];
								$input_param['twqr_resp_msg'		] = $row['twqr_resp_msg'		];

								$input_param['pay_status'			] = $row['pay_status'			];
								$input_param['pay_time'	 			] = $row['pay_time'	 			];
								$input_param['pay_method'			] = $row['pay_method'			];
								$input_param['pay_source'			] = $row['pay_source'			];

								array_push($orders, $row);
							}
							$found_data = true;
							$process_year = $iyear;
							break;
						}
					}
				} catch (Exception $e) { }
			}
			$table = "data_order_$process_year";
			// echo "effect_row :".$effect_row."\n";
			if ($found_data) {
				// echo "modify success\n";
				$res = result_message("true", "0x0200", "succeed", $orders);
				$input_param['resp_code'	] = protectSqlValue($link, $res['code']);
				$input_param['resp_msg'		] = protectSqlValue($link, $res['responseMessage']);
				$input_param['remark'] = "成功";
				$effect_row = $db->saveLog($link, $input_param, $ret_msg);
			} else {
				$res = result_message("false", "0x0204", "訂單 $orderNo 不存在!", []);
				$input_param['resp_code'	] = protectSqlValue($link, $res['code']);
				$input_param['resp_msg'		] = protectSqlValue($link, $res['responseMessage']);
				$input_param['remark'] = "失敗";
				$effect_row = $db->saveLog($link, $input_param, $ret_msg);
			}
		} else {
			$res = result_message("false", "0x0205", "connect to mysql error :".json_encode($conn_res), $orders);
			$input_param['remark'] = "異常"."connect to mysql error :".json_encode($conn_res);
			$effect_row = $db->saveLog($link, $input_param, $ret_msg);
		}
	} catch (Exception $e) {
		$res = result_message("false", "0xE209", "Exception error! error detail:".$e->getMessage(), []);
		JTG_wh_log_Exception($remote_ip, $func ." ".get_error_symbol($res["status_code"]).$res["status_code"]." ".$res["responseMessage"], $member_id);
		$error_msg = "Exception error! error detail:".$func ." ".get_error_symbol($res["status_code"]).$res["status_code"]." ".$res["responseMessage"];
		$input_param['remark'] = $error_msg;
		$effect_row = $db->saveLog($link, $input_param, $ret_msg);
	} finally {
		$data_close_conn = close_connection_finally($link, $remote_ip, $member_id);
		if ($data_close_conn["status"] == "false") $data = $data_close_conn;
	}
	
	JTG_wh_log($remote_ip, $func ." ".get_error_symbol($res["status_code"])." result :".$res["responseMessage"]."\r\n".$g_exit_symbol.$caption." exit ->"."\r\n", $member_id);
	echo (json_encode($res, JSON_UNESCAPED_UNICODE));
?>