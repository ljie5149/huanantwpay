<?php
    include_once "../common/entry.php";
	session_start();
	if ($_SESSION['accname'] == "") {
		header("Location: ../logout.php"); 
	}
	$edit_member_sid = $_SESSION['accname'];
	$act = isset($_POST['act']) ? $_POST['act'] : '';
	$id  = isset($_POST['id' ]) ? $_POST['id' ] : '';
	$ret_page = isset($_POST['ret_page']) ? $_POST['ret_page'] : '';
	
	// if ($act == 'DEL') {
	try {
		$remote_ip = get_remote_ip();
		$db = new CXDB($remote_ip);
		$conn_res = $db->connect($link, $member_id, "");
		if ($conn_res["status"] == "true") {
			$table = "data_fares";
			$sql = "UPDATE $table SET modify_date=NOW(), avalible=0";
			$sql.= " WHERE 1=1";
			$sql.= " AND  nid='$id'";
			$sql.= ";";
			// echo $sql;
			mysqli_query($link, $sql);
			$ret_msg = mysqli_error($link);

			$_SESSION['saveresult'] = "刪除成功!";
					
			// once saved, redirect back to the view page
		}
	} catch (Exception $e) {
	} finally {
		$data_close_conn = close_connection_finally($link, $remote_ip, $member_id);
	}
	header("Location: $ret_page.php?act=Qry");
?>
