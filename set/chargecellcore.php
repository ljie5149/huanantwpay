<?php
	function str2array4Zhudong($val, $seperate_str = ',') {
		$ret_array = array();
		
		if (stripos($val, $seperate_str) === false) {
			array_push($ret_array, $val);
		} else {
			$ret_array = explode($seperate_str, $val);
		}
		return $ret_array;
	}
	// 組裝sql語法-非空白字  public
	function merge_sql_string_if_not_empty4Zhudong($column_name, $val, $method_flag="=", $is_value=false, $default_str="")
	{
		if ($is_value) {
			$ret = ($val > -1) ? " AND ".$column_name.$method_flag."".$val."" : "";
			if (empty($ret)) $ret = (!empty($default_str)) ? $default_str : "";
		} else {
			$ret = ($val != "") ? " AND ".$column_name.$method_flag."'".$val."'" : "";
			if (empty($ret)) $ret = (!empty($default_str)) ? "'".$default_str."'" : "";
		}
		return $ret;
	}
	function merge_sql_string_set_value4Zhudong($column_name, $val, $method_flag="=", $is_value=false, $is_first=false)
	{
		$ret = "";
		if ($is_value) {
			if ($val > -1) {
				$ret = ($is_first) ? "": ", ";
				$ret.= $column_name.$method_flag."".$val."";
			}
		} else {
			if ($val != "") {
				$ret = ($is_first) ? "": ", ";
				$ret.= $column_name.$method_flag."'".$val."'";
			}
		}
		return $ret;
	}
	function funMultiExecute($link, $sql)
	{
		$ret_msg = "";
		if (mysqli_multi_query($link, $sql)) {
			while(mysqli_more_results($link)) {
				mysqli_next_result($link);
			}
		} else {
			$ret_msg = "執行錯誤: ".mysqli_error($link);
		}
		return $ret_msg;
		// return mysqli_affected_rows($link);
	}
	

	function getMonthlyAvalibleZhtw($input)
	{
		global $array_avalible, $array_avalible4zhtw;
		$ret = "";
		for ($i = 0; $i < count($array_avalible); $i++) {
			if ($array_avalible[$i] == $input) {
				$ret = $array_avalible4zhtw[$i];
			}
		}
		return $ret;
	}
	function getRentMonthlyAvalibleZhtw($input)
	{
		global $rent_array_avalible, $rent_array_avalible4zhtw;
		$ret = "";
		for ($i = 0; $i < count($rent_array_avalible); $i++) {
			if ($rent_array_avalible[$i] == $input) {
				$ret = $rent_array_avalible4zhtw[$i];
			}
		}
		return $ret;
	}
	function getMonthlyPaystatusZhtw($input)
	{
		global $array_paystatus, $array_paystatus4zhtw;
		$ret = "";
		for ($i = 0; $i < count($array_paystatus); $i++) {
			if ($array_paystatus[$i] == $input) {
				$ret = $array_paystatus4zhtw[$i];
			}
		}
		return $ret;
	}
	function getMonthlyPaysourceZhtw($input)
	{
		global $array_paysource, $array_paysource4zhtw;
		$ret = "";
		for ($i = 0; $i < count($array_paysource); $i++) {
			if ($array_paysource[$i] == $input) {
				$ret = $array_paysource4zhtw[$i];
			}
		}
		return $ret;
	}
	function getMonthlyPaymethodZhtw($input)
	{
		global $array_paymethod, $array_paymethod4zhtw;
		$ret = "";
		for ($i = 0; $i < count($array_paymethod); $i++) {
			if ($array_paymethod[$i] == $input) {
				$ret = $array_paymethod4zhtw[$i];
			}
		}
		return $ret;
	}
	function getDateByNdays($input_date, $val = 0, $format_str = 'Y-m-d') {
		if (strlen($input_date) == 0) {
			$now = new DateTime();
			$input_date = $now->format($format_str);
		}
		// Create a DateTime object from the input date
		$date = new DateTime($input_date);
	
		// Add one month to the date
		$date->modify("$val day");
	
		// Format the resulting date in the desired format
		$ret_date = $date->format($format_str);
		return $ret_date;
	}
	function getDateByNmonth($input_date, $val = 0, $format_str = 'Y-m-d') {
		if (strlen($input_date) == 0) {
			$now = new DateTime();
			$input_date = $now->format($format_str);
		}
		// Create a DateTime object from the input date
		$date = new DateTime($input_date);
	
		// Add one month to the date
		$date->modify("$val month");
	
		// Format the resulting date in the desired format
		$ret_date = $date->format($format_str);
		return $ret_date;
	}
	function getDateByNyears($input_date, $val = 0, $format_str = 'Y-m-d') {
		if (strlen($input_date) == 0) {
			$now = new DateTime();
			$input_date = $now->format($format_str);
		}
	
		// Create a DateTime object from the input date
		$date = new DateTime($input_date);
	
		// Add one month to the date
		$date->modify("$val years");
	
		// Format the resulting date in the desired format
		$ret_date = $date->format($format_str);
		return $ret_date;
	}
	function existMonthlyTable($link, $table) {
		global $database;

		$ret = false;
		$sql = "SELECT 1 FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = '$database' AND TABLE_NAME = '$table'";
		if ($result = mysqli_query($link, $sql)) {
			if (mysqli_num_rows($result) > 0) {
				// echo "Table '$table' exists.";
				$ret = true;
			} else {
				// echo "Table '$table' does not exist.";
				$ret = false;
			}
		}
		return $ret;
	}


	function modifyEntry($link, $table, $year, $other_year, $array_table
						, $member_sid, $edit_member_sid, $plate_no, $name, $phone				
						, $fares_sid, $parkCode, $rent_start_date, $rent_end_date, $rent_hint_date		
						, $pay_time, $pay_status, $pay_source, $pay_method, $amount, $b_amount
						, $discount, $avalible)
	{
			$sql = "";
			$sql = "SELECT * FROM $table WHERE avalible IN ('S', 'Y') ";
			$sql.= " AND plate_no = '$plate_no'";
			$sql.= merge_sql_string_if_not_empty4Zhudong("parkCode", $parkCode);
			$sql.= ";";
			$data_id = -1; $i_rent_count = 0; $i_back_rent_count = 0;
			// echo "\nplate_no :".$plate_no;
			if ($result = mysqli_query($link, $sql)) {
				if (mysqli_num_rows($result) > 0) {
					if ($row = mysqli_fetch_array($result)) {
						$data_id 			= intval($row['nid'				]);
						$i_rent_count 		= intval($row['rent_count'		]);
						$i_back_rent_count 	= intval($row['back_rent_count'	]);
					}
				}
			}
			// echo $sql;
			if ($pay_status == "2" || $avalible == "B" || $avalible == "W") { // 退款
				$i_back_rent_count++;
			}
			if ($pay_status == "1") { // 已付款
				$i_rent_count++;
			}
			$sql = "";
			// echo "data_id :".$data_id;
			if ($data_id > -1) {
				// edit
				$sql = "";
				$avalible = "Y";
				// echo "<br>edit";
				$table = "data_monthly_$year";
				$sql_tmp = getUpdateSqlString($table, $sql, $data_id, $member_sid, $edit_member_sid, $plate_no, $name, $phone				
										, $fares_sid, $parkCode, $rent_start_date, $rent_end_date, $rent_hint_date		
										, $pay_time, $pay_status, $pay_source, $pay_method, $amount, $b_amount
										, $discount, $i_rent_count, $i_back_rent_count, $avalible);
				$sql .= $sql_tmp;
				if ($year != $other_year) {
					$table = "data_monthly_$other_year";
					$sql_tmp = getUpdateSqlString($table, $sql, $data_id, $member_sid, $edit_member_sid, $plate_no, $name, $phone				
											, $fares_sid, $parkCode, $rent_start_date, $rent_end_date, $rent_hint_date		
											, $pay_time, $pay_status, $pay_source, $pay_method, $amount, $b_amount
											, $discount, $i_rent_count, $i_back_rent_count, $avalible);
					$sql .= $sql_tmp;
				}
				
				$data_type = "U";
				$table = "log_monthly_$year";
				$sql_tmp = getInsertSqlString($table, $sql, $member_sid, $edit_member_sid, $plate_no, $name, $phone				
										, $fares_sid, $parkCode, $rent_start_date, $rent_end_date, $rent_hint_date		
										, $pay_time, $pay_status, $pay_source, $pay_method, $amount, $b_amount
										, $discount, $i_rent_count, $i_back_rent_count, $avalible, $data_type);
				$sql .= $sql_tmp;
				if ($year != $other_year) {
					$table = "log_monthly_$other_year";
					$sql_tmp = getInsertSqlString($table, $sql, $member_sid, $edit_member_sid, $plate_no, $name, $phone				
											, $fares_sid, $parkCode, $rent_start_date, $rent_end_date, $rent_hint_date		
											, $pay_time, $pay_status, $pay_source, $pay_method, $amount, $b_amount
											, $discount, $i_rent_count, $i_back_rent_count, $avalible, $data_type);
					$sql .= $sql_tmp;
				}
			} else {
				// echo "<br>add";
				$sql = "";
				$avalible = "S";
				for ($i = 0; $i < count($array_table); $i++) {
					$table = $array_table[$i];
					$data_type = (strpos($table, "log_monthly_") !== false) ? "I" : "";
					$sql_tmp = getInsertSqlString($table, $sql, $member_sid, $edit_member_sid, $plate_no, $name, $phone				
											, $fares_sid, $parkCode, $rent_start_date, $rent_end_date, $rent_hint_date		
											, $pay_time, $pay_status, $pay_source, $pay_method, $amount, $b_amount
											, $discount, $i_rent_count, $i_back_rent_count, $avalible, $data_type);
					$sql .= $sql_tmp;
					// if ($i == 0) break;
				}
			}
			// echo "<br>".$sql;
			$ret_msg = funMultiExecute($link, $sql);
			return $ret_msg;
	}

	function getInsertSqlString($table, $sql, $member_sid, $edit_member_sid, $plate_no, $name, $phone				
								, $fares_sid, $parkCode, $rent_start_date, $rent_end_date, $rent_hint_date		
								, $pay_time, $pay_status, $pay_source, $pay_method, $amount, $b_amount			
								, $discount, $rent_count, $back_rent_count, $avalible, $data_type, $isBackend = false)
	{
		$rent_start = (strpos($rent_start_date, ":") === false) ? $rent_start_date." 00:00:00" : $rent_start_date;
		$rent_end = (strpos($rent_end_date, ":") === false) ? $rent_end_date." 23:59:59" : $rent_end_date;
		$sql = "";
		$sql.= "INSERT INTO $table (create_date";
		$sql.= (strlen($member_sid			) > 0) ? ", member_sid"				: "";
		$sql.= (strlen($edit_member_sid		) > 0) ? ", edit_member_sid"		: "";
		$sql.= (strlen($plate_no			) > 0) ? ", plate_no"				: "";
		$sql.= (strlen($name				) > 0) ? ", name"					: "";
		$sql.= (strlen($phone				) > 0) ? ", phone"					: "";
		$sql.= (strlen($fares_sid			) > 0) ? ", fares_sid" 				: "";
		$sql.= (strlen($parkCode			) > 0) ? ", parkCode" 				: "";
		$sql.= (strlen($rent_start_date		) > 0) ? ", rent_start_date"		: "";
		$sql.= (strlen($rent_end_date		) > 0) ? ", rent_end_date"			: "";
		$sql.= (strlen($rent_hint_date		) > 0) ? ", rent_hint_date"			: "";
		$sql.= (strlen($pay_time			) > 0) ? ", pay_time"				: "";
		$sql.= (strlen($pay_status			) > 0) ? ", pay_status"				: "";
		$sql.= (strlen($pay_source			) > 0) ? ", pay_source"				: "";
		$sql.= (strlen($pay_method			) > 0) ? ", pay_method"				: "";
		$sql.= (strlen($amount				) > 0) ? ", amount"					: "";
		$sql.= (strlen($b_amount			) > 0) ? ", b_amount"				: "";
		$sql.= (strlen($discount			) > 0) ? ", discount"				: "";
		if (strpos($table, "log_monthly_") === false) {
			$sql.= (strlen($rent_count			) > 0) ? ", rent_count"				: "";
			$sql.= (strlen($back_rent_count		) > 0) ? ", back_rent_count"		: "";
		}
		$sql.= (strlen($avalible			) > 0) ? ", avalible" 				: "";
		$sql.= (strlen($data_type			) > 0) ? ", data_type" 				: "";
		$sql.= ") VALUES (NOW() ";
		$sql.= (strlen($member_sid			) > 0) ? ", '$member_sid'"			: "";
		$sql.= (strlen($edit_member_sid		) > 0) ? ", '$edit_member_sid'"		: "";
		$sql.= (strlen($plate_no			) > 0) ? ", '$plate_no'"			: "";
		$sql.= (strlen($name				) > 0) ? ", '$name'"				: "";
		$sql.= (strlen($phone				) > 0) ? ", '$phone'"				: "";
		$sql.= (strlen($fares_sid			) > 0) ? ", '$fares_sid'"			: "";
		$sql.= (strlen($parkCode			) > 0) ? ", '$parkCode'"			: "";
		$sql.= (strlen($rent_start_date		) > 0) ? ", '$rent_start'"			: "";
		$sql.= (strlen($rent_end_date		) > 0) ? ", '$rent_end'"			: "";
		$sql.= (strlen($rent_hint_date		) > 0) ? ", '$rent_hint_date'"		: "";
		if ($isBackend)
			$sql.= (strlen($pay_time		) > 0) ? ",  '$pay_time'"			: "";
		else
			$sql.= (strlen($pay_time		) > 0) ? ",  $pay_time"				: "";
		$sql.= (strlen($pay_status			) > 0) ? ", '$pay_status'"			: "";
		$sql.= (strlen($pay_source			) > 0) ? ", '$pay_source'"			: "";
		$sql.= (strlen($pay_method			) > 0) ? ", '$pay_method'"			: "";
		$sql.= (strlen($amount				) > 0) ? ", '$amount'"				: "";
		$sql.= (strlen($b_amount			) > 0) ? ", '$b_amount'"			: "";
		$sql.= (strlen($discount			) > 0) ? ", '$discount'"			: "";
		if (strpos($table, "log_monthly_") === false) {
			$sql.= (strlen($rent_count			) > 0) ? ", '$rent_count'"			: "";
			$sql.= (strlen($back_rent_count		) > 0) ? ", '$back_rent_count'"		: "";
		}
		$sql.= (strlen($avalible			) > 0) ? ", '$avalible'" 			: "";
		$sql.= (strlen($data_type			) > 0) ? ", '$data_type'" 			: "";
		$sql.= ");";
		return $sql;
	}

	function getUpdateSqlString($table, $sql, $data_id, $member_sid, $edit_member_sid, $plate_no, $name, $phone				
								, $fares_sid, $parkCode, $rent_start_date, $rent_end_date, $rent_hint_date		
								, $pay_time, $pay_status, $pay_source, $pay_method, $amount, $b_amount				
								, $discount, $i_rent_count, $i_back_rent_count, $avalible, $isBackend = false)
	{
		$sql = "";
		$sql.= "UPDATE $table SET modify_date=NOW()";
		$sql.= merge_sql_string_set_value4Zhudong("member_sid"			, $member_sid		);
		$sql.= merge_sql_string_set_value4Zhudong("edit_member_sid"		, $edit_member_sid	);
		$sql.= merge_sql_string_set_value4Zhudong("plate_no"			, $plate_no			);
		$sql.= merge_sql_string_set_value4Zhudong("name"				, $name				);
		$sql.= merge_sql_string_set_value4Zhudong("phone"				, $phone			);
		$sql.= merge_sql_string_set_value4Zhudong("fares_sid"			, $fares_sid		);
		$sql.= merge_sql_string_set_value4Zhudong("parkCode"			, $parkCode			);
		$sql.= merge_sql_string_set_value4Zhudong("rent_start_date"		, (strpos($rent_start_date, ":") === false) ? $rent_start_date." 00:00:00" : $rent_start_date);
		$sql.= merge_sql_string_set_value4Zhudong("rent_end_date"		, (strpos($rent_end_date, ":") === false) ? $rent_end_date." 23:59:59" : $rent_end_date);
		$sql.= merge_sql_string_set_value4Zhudong("rent_hint_date"		, $rent_hint_date  );
		if ($isBackend)
			$sql.= merge_sql_string_set_value4Zhudong("pay_time"			, $pay_time);
		else
			$sql.= merge_sql_string_set_value4Zhudong("pay_time"			, $pay_time			, '=', true);
		$sql.= merge_sql_string_set_value4Zhudong("pay_status"			, $pay_status		);
		$sql.= merge_sql_string_set_value4Zhudong("pay_source"			, $pay_source		);
		$sql.= merge_sql_string_set_value4Zhudong("pay_method"			, $pay_method		);
		$sql.= merge_sql_string_set_value4Zhudong("amount"				, $amount			);
		$sql.= merge_sql_string_set_value4Zhudong("b_amount"			, $b_amount			);
		$sql.= merge_sql_string_set_value4Zhudong("discount"			, $discount			);
		$sql.= merge_sql_string_set_value4Zhudong("rent_count"			, $i_rent_count		, '=', true);
		$sql.= merge_sql_string_set_value4Zhudong("back_rent_count"		, $i_back_rent_count, '=', true);
		$sql.= merge_sql_string_set_value4Zhudong("avalible"			, $avalible			);
		$sql.= " WHERE 1=1";
		$sql.= merge_sql_string_if_not_empty4Zhudong("nid", $data_id);
		$sql.= ";";
		return $sql;
	}

	// 費率選單
	function getMonthlyFares($link, &$dft_amount, &$discount_amount, &$array_fields) {
		$fields = "nid,sid,name,edit_member_sid,fares_type,amount,discount_percent,discount_amount,avalible";
		$array_fields = str2array4Zhudong($fields);
		$sql = "SELECT * FROM data_monthly_fares WHERE avalible='Y';";
		$fares_select = array();
		$data_idx = 0;
		if ($result = mysqli_query($link, $sql)) {
			if (mysqli_num_rows($result) > 0) {
				while ($row = mysqli_fetch_array($result)) {
					for ($i = 0; $i < count($array_fields); $i++) {
						$field_name = $array_fields[$i];
						$cur_data[$field_name] = $row[$field_name];
					}
					if ($data_idx++ == 0) {
						$dft_amount 		= $row['amount'];
						$discount_amount 	= $row['discount_amount'];
					}
					array_push($fares_select, $cur_data);
				}
			}
		}
		return $fares_select;
	}
	function getPostParam($link, $param_name, $default = '')
	{
		$ret = "";

		$ret = isset($_POST[$param_name]) ? $_POST[$param_name] : $default;
		$ret = mysqli_real_escape_string($link, $ret);
		$ret = trim($ret);
		return $ret;
	}
	function ret_json_message($status, $code, $responseMessage, $json)
	{
		$data = array();
		$data["status"			] = $status;
		$data["status_code"		] = $code;
		$data["status_message"	] = $responseMessage;

		// $data["code"			] = $code;
		// $data["responseMessage"	] = $responseMessage;
		$data["data"			] = $json;
		// var_dump($data);
		return $data;
	}
	function isMonthlyCar($link, $year, $parkCode, $parkSpaceCode, $plate_no, $parking_date, $skip = false)
	{
		$ret = false; $gate01 = false;
		if ($skip) return false;

		// 是否為月租車有效期限內
		$table = "data_monthly_$year";
		$sql = "SELECT * FROM $table WHERE plate_no = '$plate_no' AND parkCode = '$parkCode' AND DATE('$parking_date') >= DATE(rent_start_date) AND DATE('$parking_date') <= DATE(rent_end_date) AND avalible IN ('Y', 'S')";
		if ($result = mysqli_query($link, $sql)) {
			if (mysqli_num_rows($result) > 0) {
				$gate01 = true;
			}
		}
		// echo $sql;

		if ($gate01) {
			// 路段是否在月租車格
			$sql = "SELECT * FROM `view_enable_monthly_parking` WHERE `parkCode`='$parkCode' AND `parkSpaceCode`='$parkSpaceCode'";
			if ($result = mysqli_query($link, $sql)) {
				if (mysqli_num_rows($result) > 0) {
					$ret = true;
				}
			}
			// echo $sql;
		}
		return $ret;
	}

	function getDataViaField($link, $sql, $field = "sid")
	{
		$ret = "";
		try {
			if ($result = mysqli_query($link, $sql)) {
				if (mysqli_num_rows($result) > 0) {
					if ($row = mysqli_fetch_array($result)) {
						$ret = strval($row[$field]);
					}
				}
			}
		} catch (Exception $e) { }
		return $ret;
	}
	function modifyChargecell($link, $sid, $remote_ip, $input, &$func, &$ret_msg, &$sql_msg)
	{
		$func = "modifyChargecell";
		$table = 'map_chargecell';
		$array_field_src = ['charge_id', 'park_code', 'park_cell', 'device_id', 'plate_no', 'entry_time', 'exit_time', 'start_time', 'end_time', 'placeholder', 'avalible', 'json_str'];
		$fields = "";
		$values = "";

		$charge_id = getVariant($input, 'charge_id');
		// echo "getVariant ok";
		// Q1 我只留 marker_id 作為更新基礎
		$sql = "SELECT * FROM $table WHERE 1=1";
		$sql.= merge_sql_string_if_not_empty("charge_id", $charge_id);
		// echo "sql :$sql<BR>";

		$ori_sid= getDataViaField($link, $sql, 'nid');
		// echo "ori_sid :$ori_sid<BR>";
		if (empty($ori_sid)) {
			for ($i = 0; $i < count($array_field_src); $i++) {
				$cur_field_name = $array_field_src[$i];
				$cur_data = getVariant($input, $cur_field_name);
				if (!empty($cur_data)) {
					$fields .= strlen($fields) ? "," : "";
					$fields .= $array_field_src[$i];
					$values .= strlen($values) ? "," : "";
					$values .= "'$cur_data'";
				}
			}
			$sql = "INSERT INTO $table (sid, create_date, $fields
					) VALUES (
						'$sid', NOW(), $values
					);";
			// echo "insert sql :$sql<BR>";
			$ret_msg = "新增成功";
		} else {
			$sql = "UPDATE $table SET ";
			$sql.= merge_sql_string_set_value("modify_date", 'NOW()', "=", true, true);
			
			for ($i = 0; $i < count($array_field_src); $i++) {
				$cur_field_name = $array_field_src[$i];
				$cur_data = getVariant($input, $cur_field_name);
				if (!empty([$cur_data])) {
					$sql.= merge_sql_string_set_value($cur_field_name, $cur_data, "=");
				}
			}
			$sql.= " WHERE 1=1";
			$sql.= merge_sql_string_if_not_empty("nid", $ori_sid);
			$ret_msg = "資料已存在，更新成功";
		}
		$result = execute($link, $sql, $sql_msg);
		return $result;
	}
	function modifyFares($link, $sid, $remote_ip, $input, &$func, &$ret_msg, &$sql_msg)
	{
		$func = "modifyFares";
		$table = 'data_fares';
		$array_field_src = ['station_area', 'charge_type', 'fare_start_date', 'fare_end_date', 'rule_name', 'rule_descript', 'amount', 'open', 'avalible', 'json_str'];
		$fields = "";
		$values = "";

		$ori_sid = "";
		$charge_id = getVariant($input, 'nid');
		if (strlen($charge_id) > 0) {
			// echo "getVariant ok";
			// Q1 我只留 marker_id 作為更新基礎
			$sql = "SELECT * FROM $table WHERE 1=1";
			$sql.= merge_sql_string_if_not_empty("nid", $charge_id);
			// echo "sql :$sql<BR>";

			$ori_sid= getDataViaField($link, $sql, 'nid');
		}
		// echo "ori_sid :$ori_sid<BR>";
		if (empty($ori_sid)) {
			for ($i = 0; $i < count($array_field_src); $i++) {
				$cur_field_name = $array_field_src[$i];
				$cur_data = getVariant($input, $cur_field_name);
				$cur_data = trim($cur_data);
				if (!empty($cur_data)) {
					$fields .= strlen($fields) ? "," : "";
					$fields .= $array_field_src[$i];
					$values .= strlen($values) ? "," : "";
					$values .= "'$cur_data'";
				}
			}
			$sql = "INSERT INTO $table (sid, create_date, $fields
					) VALUES (
						'$sid', NOW(), $values
					);";
			// echo "insert sql :$sql<BR>";
			$ret_msg = "新增成功";
		} else {
			$sql = "UPDATE $table SET ";
			$sql.= merge_sql_string_set_value("modify_date", 'NOW()', "=", true, true);
			
			for ($i = 0; $i < count($array_field_src); $i++) {
				$cur_field_name = $array_field_src[$i];
				$cur_data = getVariant($input, $cur_field_name);
				if (!empty([$cur_data])) {
					$sql.= merge_sql_string_set_value($cur_field_name, $cur_data, "=");
				}
			}
			$sql.= " WHERE 1=1";
			$sql.= merge_sql_string_if_not_empty("nid", $ori_sid);
			$ret_msg = "資料已存在，更新成功";
		}
		$result = execute($link, $sql, $sql_msg);
		return $result;
	}
	function execute($link, $sql, &$ret_msg)
	{
		mysqli_query($link, $sql);// or die(mysqli_error($link));
		$ret_msg = mysqli_error($link);
		return mysqli_affected_rows($link);
	}
?>