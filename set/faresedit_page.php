<?php
    include_once "../common/entry.php";
	global $g_app_title, $g_history_array_avalible, $g_history_array_avalible4zhtw;
	session_start();
	if ($_SESSION['accname'] == "") {
		header("Location: ../logout.php");
	}

	$member_id  = 'JTG_CHARGE UI';
	$nid = isset($_POST['id']) ? $_POST['id'] : '';
	// echo "nid :$nid\n";
	$ret_page = isset($_POST['ret_page']) ? $_POST['ret_page'] : '';
	$charge_types = ['DC', 'AC'];
	if ($nid != '') {
		try {
			$remote_ip = get_remote_ip();
			$db = new CXDB($remote_ip);
			$conn_res = $db->connect($link, $member_id, "");
			$sql = "SELECT * FROM data_fares WHERE 1 = 1";
			$sql.= ($nid != '') ? " AND nid = $nid" : "";
			// echo "$sql\n";
			if ($result = mysqli_query($link, $sql)) {
				if (mysqli_num_rows($result) > 0) {
					while ($row = mysqli_fetch_array($result)) {
						$nid 					= isset($row['nid'				]) ? trim($row['nid'				]) : "";
						$create_date 			= isset($row['create_date'		]) ? trim($row['create_date'		]) : "";
						$modify_date			= isset($row['modify_date'		]) ? trim($row['modify_date'		]) : "";
						$station_area			= isset($row['station_area'		]) ? trim($row['station_area'		]) : "";
						$charge_type			= isset($row['charge_type'		]) ? trim($row['charge_type'		]) : "";
						$rule_name				= isset($row['rule_name'		]) ? trim($row['rule_name'			]) : "";
						$rule_descript			= isset($row['rule_descript'	]) ? trim($row['rule_descript'		]) : "";
						$amount					= isset($row['amount'			]) ? trim($row['amount'				]) : "";
						$open					= isset($row['open'				]) ? trim($row['open'				]) : "";
						$fare_start_date		= isset($row['fare_start_date'	]) ? trim($row['fare_start_date'	]) : "";
						$fare_end_date			= isset($row['fare_end_date'	]) ? trim($row['fare_end_date'		]) : "";
						$avalible 				= isset($row['avalible'			]) ? trim($row['avalible'			]) : "";
						$json_str 				= isset($row['json_str'			]) ? trim($row['json_str'			]) : "";
						$remark 				= isset($row['remark'			]) ? trim($row['remark'				]) : "";
						echo "$nid, $create_date, $modify_date, $station_area, $charge_type, $rule_name, $rule_descript, $amount, $fare_start_date, $fare_end_date, $avalible, $json_str, $remark\n";
					}
				}
			}
		} catch (Exception $e) {
		} finally {
			$data_close_conn = close_connection_finally($link, $remote_ip, $member_id);
		}
	}
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />

		<!--360浏览器优先以webkit内核解析-->
		<title><?php echo $g_app_title; ?></title>

		<link rel="shortcut icon" href="../favicon.ico" />
		<link href="../css/bootstrap.min.css?v=3.3.7" rel="stylesheet" />
		<link href="../css/font-awesome.css?v=4.4.0" rel="stylesheet" />

		<link href="../css/animate.css" rel="stylesheet" />
		<link href="../css/style.css?v=4.1.0" rel="stylesheet" />
	   <!-- Data Tables -->
		<link href="../css/plugins/dataTables/dataTables.bootstrap.css?" rel="stylesheet">

		<link href="../js/plugins/fancybox/jquery.fancybox.css" rel="stylesheet">

		<style>
			.custom-width {
				width: 300px; /* 設定寬度為200px */
			}
		</style>
	</head>
	<body class="gray-bg">
       <!-- Begin Page Content -->
        <div class="container-fluid">
          <!-- Page Heading -->

          <!-- Content Row -->
          <div class="row">

            <div class="wrapper wrapper-content animated fadeInRight">

              <!-- Illustrations -->
			   <div class="col-sm-12">
                <div class="ibox float-e-margins">
                    <div class="ibox-title">
						<h5>編輯 費率 資料</h5>
                </div>
				  <div>
					<form action="faresmodify.php" method="Post" name='frm1' id='frm1' class="form-horizontal">
						<input type="hidden" name="act" id="act"  value="Edit"/>
						<input type="hidden" name="ret_page" id="ret_page" value="<?=$ret_page?>">
						<input type="hidden" name="nid" 				id="nid"  					value="<?php echo $nid; ?>"/>
						<input type="hidden" name="create_date"			id="create_date"			value="<?php echo $create_date;?>"/>
						<input type="hidden" name="modify_date"			id="modify_date"  			value="<?php echo $modify_date;?>"/>
						<!-- <input type="hidden" name="charge_id"			id="charge_id"  			value="<?php echo $charge_id;?>"/> -->
						<!-- <input type="hidden" name="park_code" 			id="park_code"  			value="<?php echo $park_code;?>"/>
						<input type="hidden" name="park_cell" 			id="park_cell"  			value="<?php echo $park_cell;?>"/> -->
						<!-- <input type="hidden" name="avalible" 			id="avalible"  				value="<?php echo $avalible;?>"/> -->
						<input type="hidden" name="json_str"			id="json_str"				value="<?php echo $json_str;?>"/>
						<input type="hidden" name="remark" 				id="remark"					value="<?php echo $remark;?>"/>

						<div>
							<div class="ibox float-e-margins">
								<br/>
								<div class="form-group">
									<label class="col-sm-2 form-label">更新日期</label>
									<div class="col-sm-4">
										<input class="text-input small-input" type="text" name="modify_date_show" id="modify_date_show"  value="<?php echo $modify_date;?>" disabled />
									</div>
								</div>
								<br/>
								<br/>
								<div class="form-group">
									<label class="col-sm-2 form-label">區域</label>
									<div class="col-sm-4">
										<input class="text-input small-input" type="text" name="station_area" id="station_area"  value="<?php echo $station_area;?>" />
									</div>
								</div>

								<br/>
								<div class="form-group">
									<label class="col-sm-2 form-label">充電類型</label>
									<!--<input type="text" name="field-name2" class="form-control" data-mask="0000/00/00" data-mask-clearifnotmatch="true" placeholder="yyyy/mm/dd" />-->
									<div class="col-sm-2">
										<select name="charge_type" id="charge_type" class="form-control custom-width">
											<?php
												for ($i = 0; $i < count($charge_types); $i++) {
													$cur_charge 	 = $charge_types[$i];
													$opt_value 		 = $cur_charge;
													$opt_value_zhtw  = $cur_charge;
													$option_str 	 = "<option value='$opt_value'";
													$option_str 	.= ($charge_type == $opt_value) ? " selected" : "";
													$option_str 	.= ">$opt_value_zhtw</option>";
													echo $option_str;
												}
											?>
										</select>
									</div>
								</div>

								<br/>
								<br/>
								<div class="form-group">
									<label class="col-sm-2 form-label">規則名稱</label>
									<div class="col-sm-4">
										<input class="text-input small-input" type="text" name="rule_name" id="rule_name"  value="<?php echo $rule_name ?>" />
									</div>
								</div>

								<br/>
								<br/>
								<div class="form-group">
									<label class="col-sm-2 form-label">規則內容</label>
									<div class="col-sm-4">
										<textarea
											class="form-control"
											name="rule_descript"
											id="rule_descript"
											rows="4"
											placeholder="請輸入規則內容"><?php echo htmlspecialchars(trim($rule_descript)); ?></textarea>
									</div>
								</div>

								<br/>
								<br/>
								<div class="form-group">
									<label class="col-sm-2 form-label">金額</label>
									<div class="col-sm-4">
										<input class="text-input small-input" type="text" name="amount" id="amount"  value="<?php echo $amount ?>" />
									</div>
								</div>

								<br/>
								<br/>
								<div class="form-group">
									<label class="col-sm-2 form-label">營業時間</label>
									<div class="col-sm-4">
										<input class="text-input small-input" type="text" name="open" id="open"  value="<?php echo $open ?>" />
									</div>
								</div>

								<br/>
								<br/>
								<div class="form-group">
									<label class="col-sm-2 form-label">費率使用日期(起)</label>
									<div class="col-sm-4">
										<input class="text-input small-input" type="date" name="fare_start_date" id="fare_start_date" value="<?php echo $fare_start_date ?>" />
									</div>
								</div>

								<br/>
								<br/>
								<div class="form-group">
									<label class="col-sm-2 form-label">費率使用日期(迄)</label>
									<div class="col-sm-4">
										<input class="text-input small-input" type="date" name="fare_end_date" id="fare_end_date" value="<?php echo $fare_end_date ?>" />
									</div>
								</div>

								<br/>
								<div class="hr-line-dashed"></div>
								<div class="form-group">
									<label class="col-sm-2 form-label">狀態</label>
									<!--<input type="text" name="field-name2" class="form-control" data-mask="0000/00/00" data-mask-clearifnotmatch="true" placeholder="yyyy/mm/dd" />-->
									<div class="col-sm-2">
										<select name="avalible" id="avalible" class="form-control custom-width">
											<?php
											// var_dump($g_history_array_avalible);
												for ($i = 0; $i < count($g_base_avalible); $i++) {
													$opt_value 		= $g_base_avalible[$i];
													$opt_value_zhtw = $g_base_avalible4zhtw[$i];
													$option_str 	= "<option value='$opt_value'";
													$option_str .= ($avalible == $opt_value) ? " selected" : "";
													$option_str .= ">$opt_value_zhtw</option>";
													echo $option_str;
												}
											?>
										</select>
									</div>
								</div>

								<br/>
								<div class="hr-line-dashed"></div>
								<div class="form-group">
									<div class="text-center">
										<button type="button" class='btn btn-info ml-auto' onclick='submit();'>確認</button> &nbsp;<button type="button" class='btn btn-success ml-auto' onclick='Cancel();'>取消</button> &nbsp;
									</div>
								</div>

							</div>
						</div>
					</form>
				  </div>
              </div>
			</div>

              <!-- Approach -->

        </div>
        <!-- /.container-fluid -->


		<!-- 全局js -->
		<script src="../js/jquery.min.js?v=2.1.4"></script>
		<script src="../js/bootstrap.min.js?v=3.3.7"></script>
		<script src="../js/plugins/layer/layer.min.js"></script>

    <!-- Data Tables -->
    <script src="../js/plugins/dataTables/jquery.dataTables.js?"></script>
    <script src="../js/plugins/dataTables/dataTables.bootstrap.js?"></script>
    <script src="../js/plugins/jeditable/jquery.jeditable.js"></script>

		<!-- 自定义js -->
		<script src="../js/content.js"></script>

		<!-- 欢迎信息 -->
		<script src="../js/welcome.js"></script>

		<!-- Fancy box -->
		<script src="../js/plugins/fancybox/jquery.fancybox.js"></script>

		<!-- layerDate plugin javascript -->
		<script src="../js/plugins/layer/laydate-master/laydate.js"></script>

	</body>
</html>
<script>
       $(document).ready(function () {
            $('.dataTables-example').dataTable();

            /* Init DataTables */
            var oTable = $('#editable').dataTable();
        });

        $(document).ready(function () {
            $('.fancy-photo').fancybox({
                openEffect: 'none',
                closeEffect: 'none'
            });
        });
       //外部js调用


function Cancel()
{
	window.location.replace('<?=$ret_page?>.php');
}


</script>