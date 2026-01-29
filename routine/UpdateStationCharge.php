<?php
	global $g_root_url;

	$g_is_remote 		= true;
	$g_skip_linenotify 	= false;
	if ($g_is_remote) {
    	include_once "/var/www/html/jtgcharge/common/entry.php";
	} else {
    	include_once "./../common/entry.php";
	}
	// 55 23 * * * sudo php /var/www/html/jtgcharge/routine/RecallHistory4User.php
	function private_wh_log($log_msg)
	{
		global $g_is_remote;
		if ($g_is_remote) {
			// remote
			$log_filename = "/var/www/html/jtgcharge/log/";
		} else {
			// localhost
			$log_filename = $_SERVER["DOCUMENT_ROOT"]."/jtgcharge/locallog/routine";
		}
		
		if (!file_exists($log_filename)) {
			// create directory/folder uploads.
			mkdir($log_filename, 0777, true);
		}
		$log_file_data = $log_filename.'RecallHistory4User_'. date('Ymd').'.log';

		// if you don't add `FILE_APPEND`, the file will be erased each time you add a log
		if ($g_is_remote) {
			// remote
			echo date("Y-m-d H:i:s")."  ------  ".$log_msg . "\n";
		} else {
			// localhost
			echo date("Y-m-d H:i:s")."  ------  ".$log_msg . "<br>";
		}
	}


// Entry
	$message  = "";
	$had_msg  = false;
	
	try {
		$url = $g_root_url.'api/JTG_get_station4save.php';
		$member_id = "JTG_CHARGE";
		$remote_ip = "localhost";
		// 要傳送的參數
		$data = [
			"user_id" => "824dcc55-ccbf-11ef-9565-fa163ef46fbc",
			"count_one_time" => "9999"
		];
		$resJson = jCallAPI($error, $url, $data, "POST", "application/form-data");
	} catch (Exception $e) {
		private_wh_log("Exception error! error detail:".$e->getMessage());
	} finally {
		private_wh_log("finally!");
	}
?>