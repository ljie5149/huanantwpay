<?php
	global $g_root_url;

	$g_is_remote 		= true;
	$g_skip_linenotify 	= false;
	if ($g_is_remote) {
    	include_once "/var/www/html/jtgcharge/common/entry.php";
	} else {
    	include_once "./../common/entry.php";
	}
	
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
	$day	  = strftime("%Y-%m-%d", time());
	$pre_date = date('Y-m-d', strtotime($day." 0 days"));
	
	try {
		$url = $g_root_url.'api/JTG_get_history4user2.php';
		$member_id = "JTG_CHARGE";
		$remote_ip = "localhost";
		$db = new CXDB($remote_ip);
		$conn_res = $db->connect($link, $member_id, "");
		if ($conn_res["status"] == "true") {
			$sql_user = "SELECT DISTINCT user_id FROM `log_charge_event` WHERE DATE(create_date)='$pre_date';";
			echo "sql_user :$sql_user\n\n";
			if ($result_user = mysqli_query($link, $sql_user)) {
				if (mysqli_num_rows($result_user) > 0) {
					while($row_user = mysqli_fetch_array($result_user)) {
						$user_id = $row_user['user_id'];
						$sql = "SELECT * FROM `log_history4user` WHERE user_id = '$user_id' AND DATE(stop_time)='$pre_date' ORDER BY create_date ASC;";
						
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
		} else {
			private_wh_log("connect to mysql error ");
		}
	} catch (Exception $e) {
		private_wh_log("Exception error! error detail:".$e->getMessage());
	} finally {
		$data_close_conn = close_connection_finally($link, $remote_ip, $member_id);
		if ($data_close_conn["status"] == "false") $data = $data_close_conn;
		private_wh_log("finally close database!");
	}
?>
