<?php
    include_once "../common/entry.php";
	global $g_app_title, $g_history_array_avalible, $g_history_array_avalible4zhtw;
	session_start();
	if ($_SESSION['accname'] == "") {
		header("Location: ../logout.php");
	}

	$member_id  = 'JTG_CHARGE UI';
	$nid = isset($_POST['id']) ? $_POST['id'] : '';
	$ret_page = isset($_POST['ret_page']) ? $_POST['ret_page'] : '';
	if ($nid != '') {
		try {
			$remote_ip = get_remote_ip();
			$db = new CXDB($remote_ip);
			$conn_res = $db->connect($link, $member_id, "");
			
			$sql = "SELECT mc.*, ds.station_name FROM map_chargecell mc 
					LEFT JOIN data_charge dc ON dc.id = mc.charge_id
					LEFT JOIN data_station ds ON ds.marker_id = dc.marker_id
					WHERE mc.nid='$nid' ";
			if ($result = mysqli_query($link, $sql)) {
				if (mysqli_num_rows($result) > 0) {
					while ($row = mysqli_fetch_array($result)) {
						$nid 					= $row['nid'				];
						$create_date 			= $row['create_date'		];
						$modify_date			= $row['modify_date'		];
						$charge_id				= $row['charge_id'			];
						$park_code 				= $row['park_code'			];
						$park_cell				= $row['park_cell'			];
						$plate_no 				= $row['plate_no'			];
						$placeholder			= $row['placeholder'		];
						$avalible 				= $row['avalible'			];
						$json_str 				= $row['json_str'			];
						$remark 				= $row['remark'				];
					}
				}
			}
		} catch (Exception $e) {
		} finally {
			$data_close_conn = close_connection_finally($link, $remote_ip, $member_id);
		}
	}
	

	$member_id  = 'JTG_CHARGE UI';
	$area 		= isset($_POST['area']) ? $_POST['area'] : '';
	$charges = [];
	try {
		$remote_ip = get_remote_ip();
		$db = new CXDB($remote_ip);
		$conn_res = $db->connect($link, $member_id, "");
		$sql = "SELECT dc.id, dc.position, ds.station_name FROM data_charge dc LEFT JOIN data_station ds ON ds.marker_id = dc.marker_id WHERE dc.avalible = 1 AND ds.station_name IS NOT NULL AND ds.station_name LIKE '%$area%';";
		if ($result = mysqli_query($link, $sql)) {
			if (mysqli_num_rows($result) > 0) {
				while ($row = mysqli_fetch_array($result)) {
					$q[  'id'] = $row['id'				];
					$q['name'] = $row['station_name']."-".$row['position'];
					array_push($charges, $q);
				}
			}
		}
	} catch (Exception $e) {
	} finally {
		$data_close_conn = close_connection_finally($link, $remote_ip, $member_id);
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
						<h5>編輯月租車輛資料</h5>
                </div>
				  <div>
					<form action="chargecellmodify.php" method="Post" name='frm1' id='frm1'>
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
									<label class="col-sm-2 control-label">更新日期</label>
									<div class="col-sm-4">
										<input class="text-input small-input" type="text" name="modify_date_show" id="modify_date_show"  value="<?php echo $modify_date;?>" disabled />
									</div>
								</div>

								<br/>
								<div class="form-group">
									<label class="col-sm-2 control-label">充電槍</label>
									<!--<input type="text" name="field-name2" class="form-control" data-mask="0000/00/00" data-mask-clearifnotmatch="true" placeholder="yyyy/mm/dd" />-->
									<div class="col-sm-2">
										<select name="charge_id" id="charge_id" class="form-control custom-width">
											<?php
												for ($i = 0; $i < count($charges); $i++) {
													$cur_charge = $charges[$i];
													$opt_value 		= $cur_charge['id'];
													$opt_value_zhtw = $cur_charge['name'];
													$option_str 	= "<option value='$opt_value'";
													$option_str .= ($charge_id == $opt_value) ? " selected" : "";
													$option_str .= ">$opt_value_zhtw</option>";
													echo $option_str;
												}
											?>
										</select>
									</div>
								</div>

								<br/>
								<br/>
								<div class="form-group">
									<label class="col-sm-2 control-label">路段代碼</label>
									<div class="col-sm-4">
										<input class="text-input small-input" type="text" name="phone_show" id="phone_show"  value="<?php echo $park_code;?>" />
									</div>
								</div>

								<br/>
								<div class="form-group">
									<label class="col-sm-2 control-label">車格</label>
									<div class="col-sm-4">
										<input class="text-input small-input" type="text" name="park_cell" id="park_cell"  value="<?php echo $park_cell;?>" />
									</div>
								</div>
								
								<br/>
								<br/>
								<div class="form-group" hidden>
									<label class="col-sm-2 control-label">車柱設備id</label>
									<div class="col-sm-4">
										<input class="text-input small-input" type="text" name="device_id" id="device_id"  value="" />
									</div>
								</div>

								<br/>
								<div class="hr-line-dashed"></div>
								<div class="form-group">
									<label class="col-sm-2 control-label">狀態</label>
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