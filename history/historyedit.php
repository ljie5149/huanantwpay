<?php
include "../db_tools.php";
include "monthlycore.php";
session_start();
if ($_SESSION['accname'] == "") {
	header("Location: ../logout.php"); 
}
	$edit_member_sid = $_SESSION['accname'];
	$act = isset($_POST['act']) ? $_POST['act'] : '';
	if ($act == 'Edit') {
		$id = isset($_POST['id']) ? $_POST['id'] : '';
		$id  = mysqli_real_escape_string($link,$id);
		
		$sid 				= isset($_POST['sid']) ? $_POST['sid'] : '';
		$sid  				= mysqli_real_escape_string($link,$sid);
		
		$name 				= isset($_POST['name']) ? $_POST['name'] : '';
		$name  				= mysqli_real_escape_string($link,$name);

		$fares_type 		= isset($_POST['fares_type']) ? $_POST['fares_type'] : '0';
		$fares_type			= mysqli_real_escape_string($link,$fares_type);

		$amount 			= isset($_POST['amount']) ? $_POST['amount'] : '0';
		$amount				= mysqli_real_escape_string($link,$amount);

		$discount_amount	= isset($_POST['discount_amount']) ? $_POST['discount_amount'] : '0';
		$discount_amount	= mysqli_real_escape_string($link,$discount_amount);

		$discount_percent 	= isset($_POST['discount_percent']) ? $_POST['discount_percent'] : '0';
		$discount_percent  	= mysqli_real_escape_string($link,$discount_percent);

		$avalible 			= isset($_POST['avalible']) ? $_POST['avalible'] : 'Y';
		$avalible  			= mysqli_real_escape_string($link,$avalible);
		
		$table = "data_monthly_fares";
		$sql = "UPDATE $table SET modify_date=NOW()";
		$sql.= merge_sql_string_set_value4Zhudong("name"			, $name, "="		);
		$sql.= merge_sql_string_set_value4Zhudong("edit_member_sid"	, $edit_member_sid	);
		$sql.= merge_sql_string_set_value4Zhudong("fares_type"		, $fares_type		);
		$sql.= merge_sql_string_set_value4Zhudong("amount"			, $amount			);
		$sql.= merge_sql_string_set_value4Zhudong("discount_amount"	, $discount_amount	);
		$sql.= merge_sql_string_set_value4Zhudong("discount_percent", $discount_percent	);
		$sql.= merge_sql_string_set_value4Zhudong("avalible"		, $avalible			);
		$sql.= " WHERE 1=1";
		$sql.= merge_sql_string_if_not_empty4Zhudong("nid", $id);
		$sql.= ";";
		
		$data_type = "U";
		$table = "log_monthly_fares";
		$sql.= "INSERT INTO $table (create_date, sid, edit_member_sid, name, data_type";
		$sql.= (!empty($fares_type		)) ? ", fares_type"			: "";
		$sql.= (!empty($amount			)) ? ", amount" 			: "";
		$sql.= (!empty($discount_amount	)) ? ", discount_amount"	: "";
		$sql.= (!empty($discount_percent)) ? ", discount_percent"	: "";
		$sql.= (!empty($avalible		)) ? ", avalible" 			: "";
		$sql.= ") VALUES (NOW(), '$sid', '$edit_member_sid', '$name', '$data_type' ";
		$sql.= (!empty($fares_type		)) ? ", '$fares_type'"		: "";
		$sql.= (!empty($amount			)) ? ", '$amount'" 			: "";
		$sql.= (!empty($discount_amount	)) ? ", '$discount_amount'"	: "";
		$sql.= (!empty($discount_percent)) ? ", '$discount_percent'": "";
		$sql.= (!empty($avalible		)) ? ", '$avalible'" 		: "";
		$sql.= ");";
		// echo $sql;
		funMultiExecute($link,$sql);
		
		$_SESSION['saveresult']="編輯成功!";
			
		// once saved, redirect back to the view page
		header("Location: monthlyfares.php?act=Qry");
	}else{
		header("Location: logout.php");
	}

?>
