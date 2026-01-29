<?php
    include_once "../common/entry.php";
	include "historycore.php";
	
	session_start();
	if ($_SESSION['accname'] == "") {
		header("Location: ../logout.php"); 
	}

	$member_id = "";
	$edit_member_sid = $_SESSION['accname'];
	$act = isset($_POST['act']) ? $_POST['act'] : '';
	$ret_page = isset($_POST['ret_page']) ? $_POST['ret_page'] : '';
	echo "<br> act :".$act;
	if ($act == 'Edit') {

		
		try {
			$remote_ip = get_remote_ip();
			$db = new CXDB($remote_ip);
			$conn_res = $db->connect($link, $member_id, "");
			if ($conn_res["status"] == "true") {
				$nid				= getPostParam($link, 'nid'				);
				$avalible			= getPostParam($link, 'avalible'		, '1');
				if ($nid != '') {
					$sql = "SELECT * FROM log_history4user WHERE nid='$nid'";
					if ($result = mysqli_query($link, $sql)) {
						if (mysqli_num_rows($result) > 0) {
							while ($row = mysqli_fetch_array($result)) {
								$order_serial_number = $row['order_serial_number'];
							}
						}
					}
				}
				$table = "log_history4user";
				$sql = "UPDATE $table SET modify_date=NOW(), avalible=$avalible";
				$sql.= " WHERE 1=1";
				$sql.= " AND order_serial_number='$order_serial_number'";
				$sql.= ";";
				// echo $sql;
				mysqli_query($link, $sql);
				$ret_msg = mysqli_error($link);
		
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
