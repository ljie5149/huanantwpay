<?php
	function getUtcDateTime()
	{
		date_default_timezone_set('UTC');
		return date("Y-m-d\TH:i:s\Z");
	}
	function getVariant($obj, $field_name)
	{
		return isset($obj[$field_name]) ? $obj[$field_name] : "";
	}
	function protectSqlValue($link, $input) {
    	return (strlen($input) > 0) ? mysqli_real_escape_string($link, $input) : "";
	}
	// 訊息中心 public
	function result_message($status, $code, $responseMessage, $json)
	{
		$data = array();
		$data["status"			] = $status;
		$data["code"			] = $code;
		$data["responseMessage"	] = $responseMessage;
		$data["data"			] = $json;

		// $data["status_code"		] = $code;
		// $data["status_message"	] = $responseMessage;
		// var_dump($data);
		return $data;
	}
	function ocpi_result_message($code, $responseMessage)
	{
		$data = array();
		$data["status_code"		] = $code;
		$data["status_message"	] = $responseMessage;
		// var_dump($data);
		return $data;
	}
	function result_connect_error($link)
	{
		$data = array();
		if (!$link || is_null($link))
		{
			try
			{
				$data = result_message("false", "0x0206", "連接錯誤：".mysqli_connect_error(), "");
			}
			catch (Exception $e)
			{
				$data = result_message("false", "0x0206", "連接錯誤 Exception error :".$e->getMessage(), "");
			}
		}
		else
		{
			$data = result_message("true", "0x0200", "連接成功", "");
		}
		return $data;
	}
	// 取得訊息符號
	function get_error_symbol($val)
	{
		/*
		0x0200	data parse succeed
		0x0201	data parse error					(X)
		0x0202	API parameter is required!			(!)
		0x0203	data exists							(!)
		0x0204	data not exists						(!)
		0x0205	dog err								(X)
		0x0206	other message - condiction			(!)
		0x0207	Exception error: disconnect!		(!)
		0x0208	SQL fail! please check query str	(!)
		0x0209	Exception error!					(X)
		*/
		$ret = "";
		
		if ($val == "0x0202" || $val == "0x0203" || $val == "0x0204" ||
			$val == "0x0206" || $val == "0x0207" || $val == "0x0208")
			$ret = "(!) ";
		else if ($val == "0x0201" || $val == "0x0205" || $val == "0x0209")
			$ret = "(X) ";
		return $ret;
	}
	function get_role_name($val)
	{
		$ret = "";
		switch ($val) {
			case "proposer":
				$ret = "要保人";
				break;
			case "insured":
				$ret = "被保人";
				break;
			case "legalRepresentative":
				$ret = "法定代理人";
				break;
			default:
				$ret = "";
		}
		return $ret;
	}
	// encrypt-加密  public
	function encrypt_string_if_not_empty($flag, $val)
	{
		global $key;
		
		$ret = $val;
		if ($val == "") return $ret;
		if ($flag)
			$ret = encrypt($key, $val);
		return $ret;
	}
	// decrypt-解密  public
	function decrypt_string_if_not_empty($flag, $val)
	{
		global $key;
		
		$ret = $val;
		if ($val == "") return $ret;
		if ($flag)
			$ret = decrypt($key, $val);
		return $ret;
	}
	// 組裝sql語法-非空白字  public
	function merge_sql_string_if_not_empty($column_name, $val, $method_flag="=", $is_value=false, $default_str="")
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
	function merge_sql_string_anyway($column_name, $val, $method_flag="=")
	{
		return " AND ".$column_name.$method_flag."'".$val."'";
	}
	// 組裝sql語法-非空白字  public
	function merge_sql_string_set_value($column_name, $val, $method_flag="=", $is_value=false, $is_first=false)
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
	function merge_with_comma(&$values, $val, $value_symbol="'")
	{
		$values.= (strlen($values) > 0) ? "," : "";
		$values.=  $value_symbol.$val.$value_symbol;
	}
	function merge_invalid_message_with_comma(&$fields, $val, $field_name, $value_symbol="'")
	{
		if (!empty($val)) {
			$fields.= (strlen($fields) > 0) ? ", " : "";
			$fields.=  $value_symbol.$field_name.$value_symbol;
		}
	}
	function uploadImage($path, $_GRAPH_FILE)
	{
		// 上傳圖檔
		$data = array();
		$dst_path = array(); $value = "";
		
		$data = result_message("false", "0x0206", $path, "");
		if (isset($_GRAPH_FILE['file'])) {
			$data = result_message("false", "0x0206", "step01", "");
			$file_name = $_GRAPH_FILE['file']['name'];
			$file_size = $_GRAPH_FILE['file']['size'];
			$file_tmp  = $_GRAPH_FILE['file']['tmp_name'];
			$file_type = $_GRAPH_FILE['file']['type'];
			$myfile_name = explode('.', $_GRAPH_FILE['file']['name']);
			$ret_str = ""; $err_str = ""; $succeed_flag = true; $dst_path = '';
			if (!is_null($myfile_name)) $file_ext = strtolower(end($myfile_name));
			$data = result_message("false", "0x0206", "step02", "");
			
			$extensions = array("xlsx","xls");
			if ($succeed_flag && in_array($file_ext, $extensions)=== false) {
				$err_str = "Extension not allowed, please choose an Excel file.";
				$data = result_message("false", "0x0206", $ret_str, "");
				$succeed_flag = false;
			}
			
			if ($succeed_flag && $file_size > 30971520) {
				$err_str = 'File size must be less than 30 MB';
				$data = result_message("false", "0x0206", $ret_str, "");
				$succeed_flag = false;
			}
			
			if ($succeed_flag && empty($errors) == true) {
				$dst_path = $path.$file_name;
				move_uploaded_file($file_tmp, $dst_path);
				// echo "Excel file uploaded successfully.";
				$data = result_message("true", "0x0200", $ret_str, "");
			}
			$ret_str = ($succeed_flag) ? '上傳圖片 ['.$file_name.'] 成功'.$dst_path : '上傳圖片 ['.$file_name.",".$dst_path.'] 異常<br>'.$err_str;
			$data['responseMessage'] = $ret_str;
		}
		return $data;
	}
	// 照片儲入Nas事先工作 public
	function will_save2nas_prepare($remote_ip, $Person_id, $front)
	{
		$data = array();
		$data["status"]			 = "true";
		$data["code"]			 = "0x0200";
		$data["responseMessage"] = "Create NAS Folder Success";
		$data["filename"] 		 = "";
		//$date = date("Ymd");
		$date = date("Y")."/".date("Ym")."/".date("Ymd");
		//$foldername ="/dis_app/dis_idphoto/".$date; 
		$foldername = NASDir().$date; 
		if (create_folder($foldername) == false)
		{
			$data["status"]			= "false";
			$data["code"]			= "0x0205";
			$data["responseMessage"]= "NAS fail!";
			$filename = "";
		}
		if ($data["status"] == "true")
		{
			$filename = $foldername."/".$Person_id."_".$front;
			$data["filename"] = $filename;
		}
		wh_log($remote_ip, $data["responseMessage"], $Person_id);
		return $data;
	}
	// 照片儲入Nas public
	function save_image2nas($remote_ip, $Person_id, $filename, $image)
	{
		try
		{
			$fp = fopen($filename, "w");
			$orgLen = strlen($image);
			if($orgLen<=0)
			{
				fclose($fp);
				return -1;
			}
			
			$len = fwrite($fp, $image, strlen($image));
			if($orgLen!=$len)
			{
				fclose($fp);
				return -2;
			}
			
			fclose($fp);
		/*	
			//Verify
			$fp = fopen($filename, "r");
			$rImg = fread($fp, filesize($filename));
			if($orgLen!=strlen($rImg))
			{
				fclose($fp);
				return -3;		
			}

			fclose($fp);
		*/
		}
		catch (Exception $e)
		{
			wh_log($remote_ip, "saveImagetoNas failed:".$e->getMessage(), $Person_id);
			return -4;
		}
		return 1;
	}
	function getFirstDateOfMonth($cur_date)
	{
		$yyyyMM=date("Y-m", $cur_date);
		return date("Y-m-d", strtotime("first day of {$yyyyMM}"));
	}
	function getLastDateOfMonth($cur_date)
	{
		$yyyyMM=date("Y-m", $cur_date);
		return date("Y-m-d", strtotime("{$yyyyMM} +1 month -1 day"));
	}
	function randomkeys4UserCode($length)
	{
		//$pattern = "1234567890abcdefghijklmnopqrstuvwxyz";
		$pattern = "1234567890";
		$key = "";
		for ($i=0;$i<$length;$i++) {
			$key .= $pattern[rand(0,9)];
		}
		return $key;
	}
	
	function getRandomLetter() {
		$isUppercase = mt_rand(0, 1); // Randomly decide if the letter should be uppercase or lowercase
		if ($isUppercase) {
			return chr(mt_rand(65, 90)); // ASCII values for 'A' to 'Z'
		} else {
			return chr(mt_rand(97, 122)); // ASCII values for 'a' to 'z'
		}
	}
	function getUniqueId() {
		return sprintf('%02x%04x%04x',
		// 32 bits for the time_low
		mt_rand(0xaa, 0xff), mt_rand(0, 0x9999), mt_rand(0, 0x9999)
		);
	}
	function getUniqueId2() {
		return sprintf('%s%s%08d',getRandomLetter(), getRandomLetter(), mt_rand(0, 99999999)
		);
	}
	function getUniqueId4Simple($head, $idx) {
		return $head.sprintf('%05d', $idx);
	}
	function getUUID() {
		return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
		// 32 bits for the time_low
		mt_rand(0, 0xffff), mt_rand(0, 0xffff),
		// 16 bits for the time_mid
		mt_rand(0, 0xffff),
		// 16 bits for the time_hi,
		mt_rand(0, 0x0fff) | 0x4000,
	
		// 8 bits and 16 bits for the clk_seq_hi_res,
		// 8 bits for the clk_seq_low,
		mt_rand(0, 0x3fff) | 0x8000,
		// 48 bits for the node
		mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
		);
	}
	function saveBase64Image($base64, $root_path, $path, $uid, $file_name, $file_title="undef")
	{
		$ret = "";
		try {
			// Extract the data part from the base64 string (this is the part after "data:image/png;base64,")
			$base64_image = explode(',', $base64)[1];

			// Decode the base64-encoded image data to binary data
			$image_data = base64_decode($base64_image);

			// Set a filename for the image (you can use any name you like)
			$array_filename = explode('.', $file_name);
			$filename_ext = $array_filename[count($array_filename) - 1];
			$filename = $file_title.$uid.'.'.$filename_ext;

			// Save the binary data to a file on your server
			file_put_contents($root_path.$path.$filename, $image_data);

			$ret = $path.$filename;
		} catch (Exception $e) { }
		return $ret;
	}
	function saveBase64File($base64, $path, $uid, $file_name, &$err)
	{
		$ret = "";
		try {
			// Remove the MIME type prefix
			$base64_content = substr($base64, strpos($base64, ',') + 1);

			// Decode the base64 string to binary data
			$binary_data = base64_decode($base64_content);
			$err = $binary_data;
			
			$array_filename = explode('.', $file_name);
			$file_title = $array_filename[0];
			$filename_ext = $array_filename[count($array_filename) - 1];
			$filename = $file_title.$uid.'.csv';//.$filename_ext;
			
			// Write the binary data to a file
			$file_handle = fopen($path.$filename, 'wb');
			fwrite($file_handle, $binary_data);
			fclose($file_handle);

			// file_put_contents($path.$filename, $binary_data);

			$ret = $filename;
		} catch (Exception $e) {
			$err .= $e->getMessage();
		}
		return $ret;
	}
	function strStartWith($src, $search)
	{
		return (substr($src, 0, strlen($search)) === $search);
	}
	function strEndWith($src, $search)
	{
		return (substr($src, -strlen($search)) === $search);
	}
	function get24HourFormat($val)
	{
		$ret = $val;
		$dateTime = new DateTime($val);
		$ret = $dateTime->format("Y-m-d H:i:s");
		return $ret;
	}
	function getDateFormat($val)
	{
		$ret = $val;
		$dateTime = new DateTime($val);
		$ret = $dateTime->format("Y-m-d");
		return $ret;
	}
	function getDateFormat2($val, $srcformat = "d/m/Y")
	{
		$ret = $val;
		$dateTime = DateTime::createFromFormat($srcformat, $val);
		$ret = $dateTime->format("Y-m-d");
		return $ret;
	}
	function getDateFormat3($val, $dstformat = "d/m/Y")
	{
		$ret = $val;
		$dateTime = new DateTime($val);
		$ret = $dateTime->format($dstformat);
		return $ret;
	}
	function getDateFormat4($val, $srcformat = "Ymd")
	{
		if (strlen($val) == 0) {
			$now = new DateTime();
			$val = $now->format('Y-m-d H:i:s');
		}
		$ret = $val;
		$dateTime = DateTime::createFromFormat("Y-m-d H:i:s", $val);
		$ret = $dateTime->format($srcformat);
		return $ret;
	}
	function getDateFormat5($val, $srcformat = "Ymd")
	{
		if (strlen($val) == 0) {
			$now = new DateTime();
			$val = $now->format('Y/m/d H:i:s');
		}
		$ret = $val;
		$dateTime = DateTime::createFromFormat("Y/m/d H:i:s", $val);
		$ret = $dateTime->format($srcformat);
		return $ret;
	}
	function getDateFormat6($val, $dstformat = "Y-m-d H:i:s")
	{
		if (strlen($val) == 0) {
			$now = new DateTime();
			$val = $now->format('Y/m/d H:i:s');
		}
		$ret = $val;
		$dateTime = DateTime::createFromFormat("Y/m/d H:i:s", $val);
		$ret = $dateTime->format($dstformat);
		return $ret;
	}
	function getDate4Nseconds($nSec = 0, $val = '', $format_str = 'Y-m-d H:i:s')
	{
		// 如果沒傳基準時間，使用現在時間
		if (empty($val)) {
			$baseTime = time();
		} else {
			$baseTime = strtotime($val);
			if ($baseTime === false) {
				return null; // 時間格式錯誤
			}
		}

		return date($format_str, $baseTime + $nSec);
	}
	function getDateTimeFormat($val, $dtformat = "Ymd")
	{
		if (strlen($val) == 0) {
			$now = new DateTime();
			$val = $now->format('Y-m-d H:i:s');
		}
		$ret = $val;
		$dateTime = DateTime::createFromFormat("Y-m-d H:i:s", $val);
		$ret = $dateTime->format($dtformat);
		return $ret;
	}
	function getTwoTimeDiff($tm1, $tm2)
	{
		$dst_tm1 = (is_string($tm1)) ? new DateTime($tm1) : $tm1;
		$dst_tm2 = (is_string($tm2)) ? new DateTime($tm2) : $tm2;
		$interval = $dst_tm1->diff($dst_tm2);
		$seconds = $interval->s
				+ $interval->i * 60
				+ $interval->h * 60 * 60
				+ $interval->d * 24 * 60 * 60
				+ $interval->m * 30 * 24 * 60 * 60
				+ $interval->y * 365 * 24 * 60 * 60;
		return $seconds;
	}
	function findStrInArray($array_val, $search) {
		$ret = -1;
		for ($i = 0; $i < count($array_val); $i++) {
			if ($array_val[$i] == $search) {
				$ret = $i;
				break;
			}
		}
		return $ret;
	}
	function emptyStrInArray($array_val) {
		$ret = true;
		for ($i = 0; $i < count($array_val); $i++) {
			if ($array_val[$i] != '') {
				$ret = false;
				break;
			}
		}
		return $ret;
	}
	function str2array($val, $seperate_str = ',') {
		$ret_array = array();
		
		if (stripos($val, $seperate_str) === false) {
			array_push($ret_array, $val);
		} else {
			$ret_array = explode($seperate_str, $val);
		}
		return $ret_array;
	}
	function calculateHHmm($startTime, $endTime, $containSec = false) {
		$display_time = "";
		// 將時間格式轉換為時間戳記
		$startTimestamp = strtotime($startTime	);
		$endTimestamp 	= strtotime($endTime	);
	
		// 檢查時間是否有效
		if ($startTimestamp === false || $endTimestamp === false || $startTimestamp >= $endTimestamp) {
			return "無效的時間範圍";
		}
	
		// 計算總秒數
		$offsetSeconds = $endTimestamp - $startTimestamp;
		$remainSeconds = $offsetSeconds % 60;
		$totalSeconds = $offsetSeconds - $remainSeconds;
	

		// 計算總秒數與分鐘數
		$durationInSeconds = $offsetSeconds;
		$durationInMinutes = ceil($durationInSeconds / 60); // 每分鐘收費（不滿一分鐘以一分鐘計）

		// 計算總小時（可顯示用）
		$myhours = floor($durationInSeconds / 3600);
		$myminutes = ($containSec) ? floor(($durationInSeconds % 3600) / 60) : ceil(($durationInSeconds % 3600) / 60);
		$myseconds = $durationInSeconds % 60;
		//if (!$containSec && $myseconds > 0) $myminutes++;

		$display_time = ($containSec) ? "{$myhours}時{$myminutes}分{$myseconds}秒" : "{$myhours}時{$myminutes}分";
		return $display_time;
	}
	function calculateCost(&$totalHours, $startTime, $endTime, $ratePerHour = 20) {
		$res = 0;

		// 將時間格式轉換為時間戳記
		$startTimestamp = strtotime($startTime	);
		$endTimestamp 	= strtotime($endTime	);
	
		// 檢查時間是否有效
		if ($startTimestamp === false || $endTimestamp === false || $startTimestamp >= $endTimestamp) {
			return $res;
		}
		JTG_wh_log("", "calculateCost 檢查時間是否有效 startTime :$startTime, endTime :$endTime", "JTG_CHARGE");
		JTG_wh_log("", "calculateCost 檢查時間是否有效 startTimestamp :$startTimestamp, endTimestamp :$endTimestamp", "JTG_CHARGE");
	
		// 計算總秒數
		$offsetSeconds = $endTimestamp - $startTimestamp;
		$remainSeconds = $offsetSeconds % 60;
		$totalSeconds = $offsetSeconds - $remainSeconds;
		// echo "totalSeconds :$totalSeconds\n\n";
		JTG_wh_log("", "calculateCost 計算總秒數 totalSeconds :$totalSeconds", "JTG_CHARGE");
	
		// 計算總分鐘數
		$totalMinutes = $totalSeconds / 60;
		// echo "totalMinutes :$totalMinutes\n\n";
	
		// 計算已充電小時
		$remainMinutes = $totalMinutes % 60;
		$totalHours = ($totalMinutes - $remainMinutes) / 60;
		JTG_wh_log("", "calculateCost 計算已充電小時01 totalHours :$totalHours", "JTG_CHARGE");

		if ($remainSeconds > 0) $totalHours++;
		if ($remainSeconds == 0 && $totalSeconds > 0) $totalHours++;
		JTG_wh_log("", "calculateCost 計算已充電小時02 totalHours :$totalHours", "JTG_CHARGE");

		// 計算總金額
		$res = $totalHours * $ratePerHour; // 每分鐘費率
		// echo "totalHours :$totalHours, ratePerHour :$ratePerHour\n\n";
	
		// 返回結果
		return $res;
	}
	// 20250502 慢充一小時20元 - 竹東
	function calculateCost2(&$totalHours, $startTime, $endTime, $ratePerHour = 20, $kwh = "0") {
		$res = 0;
		$PerKwhAmount = 10;

		// 將時間格式轉換為時間戳記
		$startTimestamp = strtotime($startTime	);
		$endTimestamp 	= strtotime($endTime	);
	
		// 檢查時間是否有效
		if ($startTimestamp === false || $endTimestamp === false || $startTimestamp >= $endTimestamp) {
			return $res;
		}
		JTG_wh_log("", "calculateCost2 檢查時間是否有效 startTime :$startTime, endTime :$endTime", "JTG_CHARGE");
		JTG_wh_log("", "calculateCost2 檢查時間是否有效 startTimestamp :$startTimestamp, endTimestamp :$endTimestamp", "JTG_CHARGE");
	
		// 計算總秒數
		$offsetSeconds = $endTimestamp - $startTimestamp;
		$remainSeconds = $offsetSeconds % 60;
		$totalSeconds = $offsetSeconds - $remainSeconds;
		// echo "totalSeconds :$totalSeconds\n\n";
		JTG_wh_log("", "calculateCost2 計算總秒數 totalSeconds :$totalSeconds", "JTG_CHARGE");
	
		// 計算總分鐘數
		$totalMinutes = $totalSeconds / 60;
		// echo "totalMinutes :$totalMinutes\n\n";
	
		// 計算已充電小時
		$remainMinutes = $totalMinutes % 60;
		$totalHours = ($totalMinutes - $remainMinutes) / 60;
		JTG_wh_log("", "calculateCost2 計算已充電小時01 totalHours :$totalHours", "JTG_CHARGE");

		if ($remainSeconds > 0) $totalHours++;
		if ($remainSeconds == 0 && $totalSeconds > 0) $totalHours++;
		JTG_wh_log("", "calculateCost2 計算已充電小時02 totalHours :$totalHours", "JTG_CHARGE");

		// 計算總金額
		$res = $totalHours * $ratePerHour; // 每分鐘費率
		// echo "totalHours :$totalHours, ratePerHour :$ratePerHour\n\n";
		
		// 收費切換時間點 - 以度計費
		$switchTimestamp2 = strtotime("2026-01-05 00:00:00");
		// 計算費用 - 以度計費
		$kwh_value = floatval($kwh);
		if ($endTimestamp >= $switchTimestamp2) {
			$fee = 0;
			if ($kwh_value > 0) {
				$fee = round($kwh_value * $PerKwhAmount); // 四捨五入進位到整數
			}
			$res = $fee;
		}
	
		// 返回結果
		return $res;
	}
	// 20250806 快充每分鐘15元
	function calculateCost4miaoliDC(&$totalHours, $startTime, $endTime, $is_value = true) {
		$fee = 0;

		// 將時間格式轉換為時間戳記
		$startTimestamp = strtotime($startTime	);
		$endTimestamp 	= strtotime($endTime	);
	
		// 檢查時間是否有效
		if ($startTimestamp === false || $endTimestamp === false || $startTimestamp >= $endTimestamp) {
			return $fee;
		}
		JTG_wh_log("", "calculateCost4miaoli4DC 檢查時間是否有效 startTime :$startTime, endTime :$endTime", "JTG_CHARGE");
		JTG_wh_log("", "calculateCost4miaoli4DC 檢查時間是否有效 startTimestamp :$startTimestamp, endTimestamp :$endTimestamp", "JTG_CHARGE");
	
		// 計算總秒數
		$offsetSeconds = $endTimestamp - $startTimestamp;
		$remainSeconds = $offsetSeconds % 60;
		$totalSeconds = $offsetSeconds - $remainSeconds;
		// echo "totalSeconds :$totalSeconds\n\n";
		JTG_wh_log("", "calculateCost4miaoli4DC 計算總秒數 totalSeconds :$totalSeconds", "JTG_CHARGE");
	

		// 計算總秒數與分鐘數
		$durationInSeconds = $offsetSeconds;
		$durationInMinutes = ceil($durationInSeconds / 60); // 每分鐘收費（不滿一分鐘以一分鐘計）

		// 收費切換時間點
		$switchTimestamp = strtotime("2025-10-01 00:00:00");

		// 判斷費率
		if ($endTimestamp >= $switchTimestamp) {
			$rate = 10; // 2025-10-01 起 10元/分鐘
		} else {
			$rate = 15; // 2025-09-19 前 15元/分鐘
		}
		
		// 計算費用
		$fee = $durationInMinutes * $rate;

		// 計算費用
		//$fee = $durationInMinutes * 15;

		// 計算總小時（可顯示用）
		$myhours = floor($durationInSeconds / 3600);
		$myminutes = floor(($durationInSeconds % 3600) / 60);
		$myseconds = $durationInSeconds % 60;

		$totalHours = round($durationInSeconds / 3600, 2);
		if (!$is_value) $totalHours .= "({$myhours}:{$myminutes}:{$myseconds})";

		return $fee;
	}
	// 20250502 慢充一小時50元 後面每半小時25元 緩衝五分鐘 電壓設定7KW
	function calculateCost4miaoli(&$totalHours, $startTime, $endTime, $is_value = true) {
		$fee = 0;

		// 將時間格式轉換為時間戳記
		$startTimestamp = strtotime($startTime	);
		$endTimestamp 	= strtotime($endTime	);
	
		// 檢查時間是否有效
		if ($startTimestamp === false || $endTimestamp === false || $startTimestamp >= $endTimestamp) {
			return $fee;
		}
		JTG_wh_log("", "calculateCost 檢查時間是否有效 startTime :$startTime, endTime :$endTime", "JTG_CHARGE");
		JTG_wh_log("", "calculateCost 檢查時間是否有效 startTimestamp :$startTimestamp, endTimestamp :$endTimestamp", "JTG_CHARGE");
	
		// 計算總秒數
		$offsetSeconds = $endTimestamp - $startTimestamp;
		$remainSeconds = $offsetSeconds % 60;
		$totalSeconds = $offsetSeconds - $remainSeconds;
		// echo "totalSeconds :$totalSeconds\n\n";
		JTG_wh_log("", "calculateCost 計算總秒數 totalSeconds :$totalSeconds", "JTG_CHARGE");
	

		// 計算總秒數與分鐘數
		$durationInSeconds = $offsetSeconds;
		$durationInMinutes = ceil($durationInSeconds / 60);
	
		// 免費時間（4 分 59 秒）＝ 299 秒
		if ($durationInSeconds <= 299) {
			$myhours = floor($durationInSeconds / 3600);
			$myminutes = floor(($durationInSeconds % 3600) / 60);
			$myseconds = $durationInSeconds % 60;

			$totalHours = round($durationInSeconds / 3600, 2);
			if (!$is_value) $totalHours.= "({$myhours}:{$myminutes}:{$myseconds})";
			return 0;
		}
	
		// 滿 5 分鐘後開始收費
		// $chargeableSeconds = $durationInSeconds - 299;
		$chargeableSeconds = $durationInSeconds;
	
		// 第 1 小時內直接收 50 元
		if ($chargeableSeconds <= 3600) {
			$myhours = floor($durationInSeconds / 3600);
			$myminutes = floor(($durationInSeconds % 3600) / 60);
			$myseconds = $durationInSeconds % 60;
			
			$totalHours = round($chargeableSeconds / 3600, 2);
			if (!$is_value) $totalHours.= "({$myhours}:{$myminutes}:{$myseconds})";
			return 50;
		}
	
		// echo "chargeableSeconds :$chargeableSeconds\n";
		// 扣掉第 1 小時（3600 秒）
		$remainingSeconds = $chargeableSeconds - 3600;
	
		// 半小時為 1800 秒，4 分 59 秒為 299 秒
		// 若剩餘時間 > 299 秒，則加一個半小時的費用
		$extraFee = 0;
	
		// 是否要算下一個半小時
		if ($remainingSeconds > 299) {
			// 移除緩衝時間後的秒數，以半小時為單位進位
			$remainingSeconds -= 299;
			$halfHours 	= ceil($remainingSeconds / 1800);
			$extraFee 	= $halfHours * 25;
		}

		$remainSeconds = $durationInSeconds % 60;
		$totalHours = round(($durationInSeconds - $remainSeconds) / 3600 + ($remainSeconds / 3600), 2);
		JTG_wh_log("", "calculateCost4miaoli 計算已充電小時02 totalHours :$totalHours", "JTG_CHARGE");
		
		$myhours = floor($durationInSeconds / 3600);
		$myminutes = floor(($durationInSeconds % 3600) / 60);
		$myseconds = $durationInSeconds % 60;
		
		if (!$is_value) $totalHours.= "({$myhours}:{$myminutes}:{$myseconds})";

		// echo "totalHours :$totalHours, ratePerHour :$ratePerHour\n\n";
	
		// 返回結果
		return 50 + $extraFee;
	}
	
	// 20250806 快充每分鐘15元
	function calculateCost4miaoliDC2(&$totalHours, $startTime, $endTime, $kwh = "0", $is_value = true) {
		$fee = 0;
		$PerKwhAmount = 13;

		// 將時間格式轉換為時間戳記
		$startTimestamp = strtotime($startTime	);
		$endTimestamp 	= strtotime($endTime	);
	
		// 收費切換時間點 - 以度計費
		$switchTimestamp2 = strtotime("2026-01-16 00:00:00");
		$switchTimestamp3 = strtotime("2026-03-01 00:00:00");

		// 檢查時間是否有效
		if ($startTimestamp === false || $endTimestamp === false || $startTimestamp >= $endTimestamp) {
			try {
				if ($endTimestamp < $switchTimestamp2) {
					return $fee;
				}
			} catch (Exception $e) {
			}
		}
		
		// 收費切換時間點 - 以度計費
		if ($endTimestamp >= $switchTimestamp2) $PerKwhAmount = 10;
		if ($endTimestamp >= $switchTimestamp3) $PerKwhAmount = 13;

		JTG_wh_log("", "calculateCost4miaoli4DC 檢查時間是否有效 startTime :$startTime, endTime :$endTime", "JTG_CHARGE");
		JTG_wh_log("", "calculateCost4miaoli4DC 檢查時間是否有效 startTimestamp :$startTimestamp, endTimestamp :$endTimestamp", "JTG_CHARGE");
	
		// 計算總秒數
		$offsetSeconds = $endTimestamp - $startTimestamp;
		$remainSeconds = $offsetSeconds % 60;
		$totalSeconds = $offsetSeconds - $remainSeconds;
		// echo "totalSeconds :$totalSeconds\n\n";
		JTG_wh_log("", "calculateCost4miaoli4DC 計算總秒數 totalSeconds :$totalSeconds", "JTG_CHARGE");
	

		// 計算總秒數與分鐘數
		$durationInSeconds = $offsetSeconds;
		$durationInMinutes = ceil($durationInSeconds / 60); // 每分鐘收費（不滿一分鐘以一分鐘計）

		// 收費切換時間點
		$switchTimestamp = strtotime("2025-10-01 00:00:00");

		// 判斷費率
		if ($endTimestamp >= $switchTimestamp) {
			$rate = 10; // 2025-10-01 起 10元/分鐘
		} else {
			$rate = 15; // 2025-09-19 前 15元/分鐘
		}
		
		// 計算費用 - 以度計費
		$kwh_value = floatval($kwh);
		if ($endTimestamp >= $switchTimestamp2) {
			$fee = 0;
			if ($kwh_value > 0) {
				$fee = round($kwh_value * $PerKwhAmount); // 無條件進位到整數
			}
		} else {
			$fee = $durationInMinutes * $rate;
		}

		// 計算總小時（可顯示用）
		$myhours = floor($durationInSeconds / 3600);
		$myminutes = floor(($durationInSeconds % 3600) / 60);
		$myseconds = $durationInSeconds % 60;

		$totalHours = round($durationInSeconds / 3600, 2);
		if (!$is_value) $totalHours .= "({$myhours}:{$myminutes}:{$myseconds})";

		return $fee;
	}
	// 20250502 慢充一小時50元 後面每半小時25元 緩衝五分鐘 電壓設定7KW
	function calculateCost4miaoli2(&$totalHours, $startTime, $endTime, $kwh = "0", $is_value = true) {
		$fee = 0;
		$PerKwhAmount = 10;

		// 將時間格式轉換為時間戳記
		$startTimestamp = strtotime($startTime	);
		$endTimestamp 	= strtotime($endTime	);
	
		// 收費切換時間點 - 以度計費
		$switchTimestamp2 = strtotime("2026-01-16 00:00:00");
		$switchTimestamp3 = strtotime("2026-03-01 00:00:00");

		// 檢查時間是否有效
		if ($startTimestamp === false || $endTimestamp === false || $startTimestamp >= $endTimestamp) {
			try {
				if ($endTimestamp < $switchTimestamp2) {
					return $fee;
				}
			} catch (Exception $e) {
			}
		}
		// 收費切換時間點 - 以度計費
		if ($endTimestamp >= $switchTimestamp2) $PerKwhAmount = 7;
		if ($endTimestamp >= $switchTimestamp3) $PerKwhAmount = 10;

		JTG_wh_log("", "calculateCost 檢查時間是否有效 startTime :$startTime, endTime :$endTime", "JTG_CHARGE");
		JTG_wh_log("", "calculateCost 檢查時間是否有效 startTimestamp :$startTimestamp, endTimestamp :$endTimestamp", "JTG_CHARGE");
	
		// 計算總秒數
		$offsetSeconds = $endTimestamp - $startTimestamp;
		$remainSeconds = $offsetSeconds % 60;
		$totalSeconds = $offsetSeconds - $remainSeconds;
		// echo "totalSeconds :$totalSeconds\n\n";
		JTG_wh_log("", "calculateCost 計算總秒數 totalSeconds :$totalSeconds", "JTG_CHARGE");
	
		// 計算總秒數與分鐘數
		$durationInSeconds = $offsetSeconds;
		$durationInMinutes = ceil($durationInSeconds / 60);

		// 計算費用 - 以度計費
		$kwh_value = floatval($kwh);
		if ($endTimestamp >= $switchTimestamp2) {
			$fee = 0;
			if ($kwh_value > 0) {
				$fee = round($kwh_value * $PerKwhAmount); // 無條件進位到整數
			}
			$myhours = floor($durationInSeconds / 3600);
			$myminutes = floor(($durationInSeconds % 3600) / 60);
			$myseconds = $durationInSeconds % 60;

			$totalHours = round($durationInSeconds / 3600, 2);
			if (!$is_value) $totalHours.= "({$myhours}:{$myminutes}:{$myseconds})";
			return $fee;
		}
	
		// 免費時間（4 分 59 秒）＝ 299 秒
		if ($durationInSeconds <= 299) {
			$myhours = floor($durationInSeconds / 3600);
			$myminutes = floor(($durationInSeconds % 3600) / 60);
			$myseconds = $durationInSeconds % 60;

			$totalHours = round($durationInSeconds / 3600, 2);
			if (!$is_value) $totalHours.= "({$myhours}:{$myminutes}:{$myseconds})";
			return 0;
		}
	
		// 滿 5 分鐘後開始收費
		// $chargeableSeconds = $durationInSeconds - 299;
		$chargeableSeconds = $durationInSeconds;
	
		// 第 1 小時內直接收 50 元
		if ($chargeableSeconds <= 3600) {
			$myhours = floor($durationInSeconds / 3600);
			$myminutes = floor(($durationInSeconds % 3600) / 60);
			$myseconds = $durationInSeconds % 60;
			
			$totalHours = round($chargeableSeconds / 3600, 2);
			if (!$is_value) $totalHours.= "({$myhours}:{$myminutes}:{$myseconds})";
			return 50;
		}
	
		// echo "chargeableSeconds :$chargeableSeconds\n";
		// 扣掉第 1 小時（3600 秒）
		$remainingSeconds = $chargeableSeconds - 3600;
	
		// 半小時為 1800 秒，4 分 59 秒為 299 秒
		// 若剩餘時間 > 299 秒，則加一個半小時的費用
		$extraFee = 0;
	
		// 是否要算下一個半小時
		if ($remainingSeconds > 299) {
			// 移除緩衝時間後的秒數，以半小時為單位進位
			$remainingSeconds -= 299;
			$halfHours 	= ceil($remainingSeconds / 1800);
			$extraFee 	= $halfHours * 25;
		}

		$remainSeconds = $durationInSeconds % 60;
		$totalHours = round(($durationInSeconds - $remainSeconds) / 3600 + ($remainSeconds / 3600), 2);
		JTG_wh_log("", "calculateCost4miaoli 計算已充電小時02 totalHours :$totalHours", "JTG_CHARGE");
		
		$myhours = floor($durationInSeconds / 3600);
		$myminutes = floor(($durationInSeconds % 3600) / 60);
		$myseconds = $durationInSeconds % 60;
		
		if (!$is_value) $totalHours.= "({$myhours}:{$myminutes}:{$myseconds})";

		// echo "totalHours :$totalHours, ratePerHour :$ratePerHour\n\n";
	
		// 返回結果
		return 50 + $extraFee;
	}

	// 20250806 快充每分鐘15元
	function calculateCost4nantouDC(&$totalHours, $startTime, $endTime, $is_value = true) {
		$fee = 0;

		// 將時間格式轉換為時間戳記
		$startTimestamp = strtotime($startTime	);
		$endTimestamp 	= strtotime($endTime	);
	
		// 檢查時間是否有效
		if ($startTimestamp === false || $endTimestamp === false || $startTimestamp >= $endTimestamp) {
			return $fee;
		}
		JTG_wh_log("", "calculateCost4miaoli4DC 檢查時間是否有效 startTime :$startTime, endTime :$endTime", "JTG_CHARGE");
		JTG_wh_log("", "calculateCost4miaoli4DC 檢查時間是否有效 startTimestamp :$startTimestamp, endTimestamp :$endTimestamp", "JTG_CHARGE");
	
		// 計算總秒數
		$offsetSeconds = $endTimestamp - $startTimestamp;
		$remainSeconds = $offsetSeconds % 60;
		$totalSeconds = $offsetSeconds - $remainSeconds;
		// echo "totalSeconds :$totalSeconds\n\n";
		JTG_wh_log("", "calculateCost4miaoli4DC 計算總秒數 totalSeconds :$totalSeconds", "JTG_CHARGE");
	

		// 計算總秒數與分鐘數
		$durationInSeconds = $offsetSeconds;
		$durationInMinutes = ceil($durationInSeconds / 60); // 每分鐘收費（不滿一分鐘以一分鐘計）

		// 收費切換時間點
		$switchTimestamp = strtotime("2025-09-20 00:00:00");

		// 判斷費率
		if ($endTimestamp >= $switchTimestamp) {
			$rate = 10; // 2025-09-20 起 10元/分鐘
		} else {
			$rate = 15; // 2025-09-19 前 15元/分鐘
		}
		
	    	// 計算費用
    		$fee = $durationInMinutes * $rate;

		// 計算總小時（可顯示用）
		$myhours = floor($durationInSeconds / 3600);
		$myminutes = floor(($durationInSeconds % 3600) / 60);
		$myseconds = $durationInSeconds % 60;

		$totalHours = round($durationInSeconds / 3600, 2);
		if (!$is_value) $totalHours .= "({$myhours}:{$myminutes}:{$myseconds})";

		return $fee;
	}
	function calculateCost4nantou(&$totalHours, $startTime, $endTime, $is_value = true) {
		return calculateCost4miaoli($totalHours, $startTime, $endTime, $is_value);

		// // 將時間字串轉為 timestamp
		// $startTimestamp = strtotime($startTime);
		// $endTimestamp   = strtotime($endTime);

		// if ($startTimestamp === false || $endTimestamp === false || $startTimestamp >= $endTimestamp) {
		// 	return "無效的時間範圍";
		// }
		// JTG_wh_log("", "calculateCost 檢查時間是否有效 startTime :$startTime, endTime :$endTime", "JTG_CHARGE");
		// JTG_wh_log("", "calculateCost 檢查時間是否有效 startTimestamp :$startTimestamp, endTimestamp :$endTimestamp", "JTG_CHARGE");
	
		// // 計算總秒數
		// $offsetSeconds = $endTimestamp - $startTimestamp;
		// $remainSeconds = $offsetSeconds % 60;
		// $totalSeconds = $offsetSeconds - $remainSeconds;
		// // echo "totalSeconds :$totalSeconds\n\n";
		// JTG_wh_log("", "calculateCost 計算總秒數 totalSeconds :$totalSeconds", "JTG_CHARGE");
	

		// // 計算總秒數與分鐘數
		// $durationInSeconds = $offsetSeconds;
		// $durationInMinutes = ceil($durationInSeconds / 60);
	
		// // 免費時間（4 分 59 秒）＝ 299 秒
		// if ($durationInSeconds <= 299) {
		// 	$myhours = floor($durationInSeconds / 3600);
		// 	$myminutes = floor(($durationInSeconds % 3600) / 60);
		// 	$myseconds = $durationInSeconds % 60;

		// 	$totalHours = round($durationInSeconds / 3600, 2);
		// 	if (!$is_value) $totalHours.= "({$myhours}:{$myminutes}:{$myseconds})";
		// 	return 0;
		// }
	
		// // 滿 5 分鐘後開始收費
		// // $chargeableSeconds = $durationInSeconds - 299;
		// $chargeableSeconds = $durationInSeconds;
	
		// // 第 1 小時內直接收 50 元
		// if ($chargeableSeconds <= 3600) {
		// 	$myhours = floor($durationInSeconds / 3600);
		// 	$myminutes = floor(($durationInSeconds % 3600) / 60);
		// 	$myseconds = $durationInSeconds % 60;
			
		// 	$totalHours = round($chargeableSeconds / 3600, 2);
		// 	if (!$is_value) $totalHours.= "({$myhours}:{$myminutes}:{$myseconds})";
		// 	return 50;
		// }
	
		// // echo "chargeableSeconds :$chargeableSeconds\n";
		// // 扣掉第 1 小時（3600 秒）
		// $remainingSeconds = $chargeableSeconds - 3600;
	
		// // 半小時為 1800 秒，4 分 59 秒為 299 秒
		// // 若剩餘時間 > 299 秒，則加一個半小時的費用
		// $extraFee = 0;
	
		// // 是否要算下一個小時
		// if ($remainingSeconds > 299) {
		// 	// 移除緩衝時間後的秒數，以半小時為單位進位
		// 	$remainingSeconds -= 299;
		// 	$anHours 	= ceil($remainingSeconds / 3600);
		// 	$extraFee 	= $anHours * 50;
		// }

		// $remainSeconds = $durationInSeconds % 60;
		// $totalHours = round(($durationInSeconds - $remainSeconds) / 3600 + ($remainSeconds / 3600), 2);
		// JTG_wh_log("", "calculateCost4miaoli 計算已充電小時02 totalHours :$totalHours", "JTG_CHARGE");
		
		// $myhours = floor($durationInSeconds / 3600);
		// $myminutes = floor(($durationInSeconds % 3600) / 60);
		// $myseconds = $durationInSeconds % 60;
		
		// if (!$is_value) $totalHours.= "({$myhours}:{$myminutes}:{$myseconds})";

		// // echo "totalHours :$totalHours, ratePerHour :$ratePerHour\n\n";
	
		// // 返回結果
		// return 50 + $extraFee;
	}
	function calculateCost4nantouDC2(&$totalHours, $startTime, $endTime, $kwh = "0", $is_value = true) {
		$fee = 0;
		$PerKwhAmount = 13;

		// 將時間格式轉換為時間戳記
		$startTimestamp = strtotime($startTime	);
		$endTimestamp 	= strtotime($endTime	);
	
		// 檢查時間是否有效
		if ($startTimestamp === false || $endTimestamp === false || $startTimestamp >= $endTimestamp) {
			return $fee;
		}
		JTG_wh_log("", "calculateCost4miaoli4DC 檢查時間是否有效 startTime :$startTime, endTime :$endTime", "JTG_CHARGE");
		JTG_wh_log("", "calculateCost4miaoli4DC 檢查時間是否有效 startTimestamp :$startTimestamp, endTimestamp :$endTimestamp", "JTG_CHARGE");
	
		// 計算總秒數
		$offsetSeconds = $endTimestamp - $startTimestamp;
		$remainSeconds = $offsetSeconds % 60;
		$totalSeconds = $offsetSeconds - $remainSeconds;
		// echo "totalSeconds :$totalSeconds\n\n";
		JTG_wh_log("", "calculateCost4miaoli4DC 計算總秒數 totalSeconds :$totalSeconds", "JTG_CHARGE");
	

		// 計算總秒數與分鐘數
		$durationInSeconds = $offsetSeconds;
		$durationInMinutes = ceil($durationInSeconds / 60); // 每分鐘收費（不滿一分鐘以一分鐘計）

		// 收費切換時間點
		$switchTimestamp = strtotime("2025-09-20 00:00:00");

		// 判斷費率
		if ($endTimestamp >= $switchTimestamp) {
			$rate = 10; // 2025-09-20 起 10元/分鐘
		} else {
			$rate = 15; // 2025-09-19 前 15元/分鐘
		}

		// 收費切換時間點 - 以度計費
		$switchTimestamp2 = strtotime("2025-12-12 00:00:00");

		// 計算費用 - 以度計費
		$kwh_value = floatval($kwh);
		if ($endTimestamp >= $switchTimestamp2) {
			$fee = 0;
			if ($kwh_value > 0) {
				$fee = round($kwh_value * $PerKwhAmount); // 四捨五入進位到整數
			}
		} else {
			$fee = $durationInMinutes * $rate;
		}

		// 計算總小時（可顯示用）
		$myhours = floor($durationInSeconds / 3600);
		$myminutes = floor(($durationInSeconds % 3600) / 60);
		$myseconds = $durationInSeconds % 60;

		$totalHours = round($durationInSeconds / 3600, 2);
		if (!$is_value) $totalHours .= "({$myhours}:{$myminutes}:{$myseconds})";

		return $fee;
	}
	function calculateCost4nantou2(&$totalHours, $startTime, $endTime, $kwh = "0", $is_value = true) {
		$fee = 0;
		$PerKwhAmount = 10;

		// 將時間格式轉換為時間戳記
		$startTimestamp = strtotime($startTime	);
		$endTimestamp 	= strtotime($endTime	);
	
		// 檢查時間是否有效
		if ($startTimestamp === false || $endTimestamp === false || $startTimestamp >= $endTimestamp) {
			return $fee;
		}
		JTG_wh_log("", "calculateCost 檢查時間是否有效 startTime :$startTime, endTime :$endTime", "JTG_CHARGE");
		JTG_wh_log("", "calculateCost 檢查時間是否有效 startTimestamp :$startTimestamp, endTimestamp :$endTimestamp", "JTG_CHARGE");
	
		// 計算總秒數
		$offsetSeconds = $endTimestamp - $startTimestamp;
		$remainSeconds = $offsetSeconds % 60;
		$totalSeconds = $offsetSeconds - $remainSeconds;
		// echo "totalSeconds :$totalSeconds\n\n";
		JTG_wh_log("", "calculateCost 計算總秒數 totalSeconds :$totalSeconds", "JTG_CHARGE");
	

		// 收費切換時間點 - 以度計費
		$switchTimestamp_tmp = strtotime("2025-12-12 00:00:00");

		// 計算總秒數與分鐘數
		$durationInSeconds = $offsetSeconds;
		$durationInMinutes = ceil($durationInSeconds / 60);
	
		if ($endTimestamp < $switchTimestamp_tmp) {
			// 免費時間（4 分 59 秒）＝ 299 秒
			if ($durationInSeconds <= 299) {
				$myhours = floor($durationInSeconds / 3600);
				$myminutes = floor(($durationInSeconds % 3600) / 60);
				$myseconds = $durationInSeconds % 60;

				$totalHours = round($durationInSeconds / 3600, 2);
				if (!$is_value) $totalHours.= "({$myhours}:{$myminutes}:{$myseconds})";
				return 0;
			}
		
			// 滿 5 分鐘後開始收費
			// $chargeableSeconds = $durationInSeconds - 299;
			$chargeableSeconds = $durationInSeconds;
		
			// 第 1 小時內直接收 50 元
			if ($chargeableSeconds <= 3600) {
				$myhours = floor($durationInSeconds / 3600);
				$myminutes = floor(($durationInSeconds % 3600) / 60);
				$myseconds = $durationInSeconds % 60;
				
				$totalHours = round($chargeableSeconds / 3600, 2);
				if (!$is_value) $totalHours.= "({$myhours}:{$myminutes}:{$myseconds})";
				return 50;
			}
		}
	
		// echo "chargeableSeconds :$chargeableSeconds\n";
		// 扣掉第 1 小時（3600 秒）
		$remainingSeconds = $chargeableSeconds - 3600;
	
		// 半小時為 1800 秒，4 分 59 秒為 299 秒
		// 若剩餘時間 > 299 秒，則加一個半小時的費用
		$extraFee = 0;
	
		// 是否要算下一個半小時
		if ($remainingSeconds > 299) {
			// 移除緩衝時間後的秒數，以半小時為單位進位
			$remainingSeconds -= 299;
			$halfHours 	= ceil($remainingSeconds / 1800);
			$extraFee 	= $halfHours * 25;
		}

		// 收費切換時間點 - 以度計費
		$switchTimestamp2 = strtotime("2025-12-12 00:00:00");
		// 計算費用 - 以度計費
		$kwh_value = floatval($kwh);
		if ($endTimestamp >= $switchTimestamp2) {
			$fee = 0;
			if ($kwh_value > 0) {
				$fee = round($kwh_value * $PerKwhAmount); // 四捨五入進位到整數
			}
		} else {
			$fee = 50 + $extraFee;
		}

		$remainSeconds = $durationInSeconds % 60;
		$totalHours = round(($durationInSeconds - $remainSeconds) / 3600 + ($remainSeconds / 3600), 2);
		JTG_wh_log("", "calculateCost4miaoli 計算已充電小時02 totalHours :$totalHours", "JTG_CHARGE");
		
		$myhours = floor($durationInSeconds / 3600);
		$myminutes = floor(($durationInSeconds % 3600) / 60);
		$myseconds = $durationInSeconds % 60;
		
		if (!$is_value) $totalHours.= "({$myhours}:{$myminutes}:{$myseconds})";

		// echo "totalHours :$totalHours, ratePerHour :$ratePerHour\n\n";
	
		// 返回結果
		return $fee;
	}
	function getTwoDateTimeSeconds($startTime, $endTime) {
		$res = 0;

		// 將時間格式轉換為時間戳記
		$startTimestamp = strtotime($startTime	);
		$endTimestamp 	= strtotime($endTime	);
	
		// 檢查時間是否有效
		if ($startTimestamp === false || $endTimestamp === false || $startTimestamp >= $endTimestamp) {
			return $res;
		}
		
		// 計算總秒數
		$res = $endTimestamp - $startTimestamp;
	
		// 返回結果
		return $res;
	}
	function secondsToTime($seconds) {
		$hours 	 = floor($seconds / 3600); // 計算小時
		$minutes = floor(($seconds % 3600) / 60); // 計算分鐘
		$seconds = $seconds % 60; // 剩餘的秒數
	
		// 格式化為 HH:MM:SS
		return sprintf("%02d 時 %02d 分 %02d 秒", $hours, $minutes, $seconds);
	}
	function generateSign(array $data, string $verifyCode): string
	{
		// sign 不可參與運算
		unset($data['sign']);

		// 1. 依欄位名稱排序
		ksort($data);

		// 2. key=value&key=value
		$queryString = urldecode(http_build_query($data));

		// 3. Base64
		$base64 = base64_encode($queryString);

		// 4. SHA256
		return hash('sha256', $base64 . $verifyCode);
	}
?>
