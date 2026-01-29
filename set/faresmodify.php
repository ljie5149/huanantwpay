<?php
    include_once "../common/entry.php";
	include "chargecellcore.php";
	
	session_start();
	if ($_SESSION['accname'] == "") {
		header("Location: ../logout.php"); 
	}

	$member_id = "";
	$edit_member_sid = $_SESSION['accname'];
	$act = isset($_POST['act']) ? $_POST['act'] : '';
	$ret_page = isset($_POST['ret_page']) ? $_POST['ret_page'] : '';
	// var_dump($_POST);
	// echo "<br> act :".$act;
	$ret_msg = ""; $sql_msg = ""; $func = "";
	if ($act == 'Add') {
		try {
			$remote_ip = get_remote_ip();
			$sid = getSidSimple('data_fares', $member_id, 'F');
			// echo "<br> sid :".$sid;
			$db = new CXDB($remote_ip);
			$conn_res = $db->connect($link, $member_id, "");
			// var_dump($conn_res);
			if ($conn_res["status"] == "true") {
				$effectRows = modifyFares($link, $sid, $remote_ip, $_POST, $func, $ret_msg, $sql_msg);
				$_SESSION['saveresult']="新增成功!";
					
				// once saved, redirect back to the view page
				header("Location: $ret_page.php?act=Qry");
			}
		} catch (Exception $e) {
		} finally {
			$data_close_conn = close_connection_finally($link, $remote_ip, $member_id);
		}
	} else if ($act == 'Edit') {
		try {
			$remote_ip = get_remote_ip();
			$sid = getSidSimple('data_fares', $member_id, 'F');
			$db = new CXDB($remote_ip);
			$conn_res = $db->connect($link, $member_id, "");
			if ($conn_res["status"] == "true") {
				$effectRows = modifyFares($link, $sid, $remote_ip, $_POST, $func, $ret_msg, $sql_msg);
				$_SESSION['saveresult']="更新成功!";
					
				// once saved, redirect back to the view page
				header("Location: $ret_page.php?act=Qry");
			}
		} catch (Exception $e) {
		} finally {
			$data_close_conn = close_connection_finally($link, $remote_ip, $member_id);
		}
	} else {
		header("Location: ../logout.php");
	}
?>
