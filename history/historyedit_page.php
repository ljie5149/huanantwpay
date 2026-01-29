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
			$sql = "SELECT * FROM log_history4user WHERE nid='$nid'";
			if ($result = mysqli_query($link, $sql)) {
				if (mysqli_num_rows($result) > 0) {
					while ($row = mysqli_fetch_array($result)) {
						$nid 					= $row['nid'				];
						$order_serial_number 	= $row['order_serial_number'];
						$create_date 			= $row['create_date'		];
						$modify_date			= $row['modify_date'		];
						$station_sid			= $row['station_sid'		];
						$station_id 			= $row['station_id'			];
						$charge_point_id		= $row['charge_point_id'	];
						$charger_name 			= $row['charger_name'		];
						$gun_id 				= $row['gun_id'				];
						$gun_name 				= $row['gun_name'			];
						$charge_time 			= $row['charge_time'		];
						$header_type 			= $row['header_type'		];
						$kwh 					= $row['kwh'				];
						$month 					= $row['month'				];
						$pay_status 			= $row['pay_status'			];
						$price 					= $row['price'				];
						$start_soc 				= $row['start_soc'			];
						$stop_soc 				= $row['stop_soc'			];
						$start_time 			= $row['start_time'			];
						$stop_time 				= $row['stop_time'			];
						$station_name 			= $row['station_name'		];
						$avalible 				= $row['avalible'			];
						$stop_reason 			= $row['stop_reason'		];
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
					<form action="historymodify.php" method="Post" name='frm1' id='frm1'>
						<input type="hidden" name="act" id="act"  value="Edit"/>
						<input type="hidden" name="nid" 				id="nid"  					value="<?php echo $nid; ?>"/>
						<input type="hidden" name="order_serial_number"	id="order_serial_number"	value="<?php echo $order_serial_number;?>"/>
						<input type="hidden" name="create_date"			id="create_date"			value="<?php echo $create_date;?>"/>
						<input type="hidden" name="modify_date"			id="modify_date"  			value="<?php echo $modify_date;?>"/>
						<input type="hidden" name="station_sid"			id="station_sid"  			value="<?php echo $station_sid;?>"/>
						<input type="hidden" name="station_id" 			id="station_id"  			value="<?php echo $station_id;?>"/>
						<input type="hidden" name="charge_point_id" 	id="charge_point_id"  		value="<?php echo $charge_point_id;?>"/>
						<input type="hidden" name="charger_name" 		id="charger_name"  			value="<?php echo $charger_name;?>"/>
						<input type="hidden" name="gun_id" 				id="gun_id"					value="<?php echo $gun_id;?>"/>
						<input type="hidden" name="gun_name" 			id="gun_name"  				value="<?php echo $gun_name;?>"/>
						<input type="hidden" name="charge_time"			id="charge_time"  			value="<?php echo $charge_time;?>"/>
						<input type="hidden" name="header_type"			id="header_type"  			value="<?php echo $header_type;?>"/>
						<input type="hidden" name="kwh" 				id="kwh"  					value="<?php echo $kwh;?>"/>
						<input type="hidden" name="month" 				id="month"					value="<?php echo $month;?>"/>
						<input type="hidden" name="pay_status"			id="pay_status"				value="<?php echo $pay_status;?>"/>
						<input type="hidden" name="price" 				id="price"					value="<?php echo $price;?>"/>
						<input type="hidden" name="start_soc"			id="start_soc"				value="<?php echo $start_soc;?>"/>
						<input type="hidden" name="stop_soc"			id="stop_soc"				value="<?php echo $stop_soc;?>"/>
						<input type="hidden" name="start_time"			id="start_time"				value="<?php echo $start_time;?>"/>
						<input type="hidden" name="stop_time"			id="stop_time"				value="<?php echo $stop_time;?>"/>
						<input type="hidden" name="station_name"		id="station_name"			value="<?php echo $station_name;?>"/>
						<input type="hidden" name="stop_reason"			id="stop_reason"			value="<?php echo $stop_reason;?>"/>
						<input type="hidden" name="json_str"			id="json_str"				value="<?php echo $json_str;?>"/>
						<input type="hidden" name="remark" 				id="remark"					value="<?php echo $remark;?>"/>

						<div>
							<div class="ibox float-e-margins">
								<br/>
								<div class="form-group">
									<label class="col-sm-2 control-label">最後更新日期</label>
									<div class="col-sm-4">
										<input class="text-input small-input" type="text" name="modify_date_show" id="modify_date_show"  value="<?php echo $modify_date;?>" disabled />
									</div>
								</div>

								<br/>
								<div class="form-group">
									<label class="col-sm-2 control-label">充電桩名稱</label>
									<div class="col-sm-4">
										<input class="text-input small-input" type="text" name="charger_name_show" id="charger_name_show"  value="<?php echo $charger_name;?>" disabled />
									</div>
								</div>

								<br/>
								<div class="form-group">
									<label class="col-sm-2 control-label">槍號/槍名</label>
									<div class="col-sm-4">
										<input class="text-input small-input" type="text" name="phone_show" id="phone_show"  value="<?php echo $gun_id."/".$gun_name;?>" disabled />
									</div>
								</div>

								<br/>
								<div class="form-group">
									<label class="col-sm-2 control-label">槍頭類型</label>
									<div class="col-sm-4">
										<input class="text-input small-input" type="text" name="header_type_show" id="header_type_show"  value="<?php echo $header_type;?>" disabled />
									</div>
								</div>

								<br/>
								<div class="form-group">
									<label class="col-sm-2 control-label">用電度數</label>
									<div class="col-sm-4">
										<input class="text-input small-input" type="text" name="kwh_show" id="kwh_show"  value="<?php echo $kwh;?>" disabled />
									</div>
								</div>

								<br/>
								<div class="form-group">
									<label class="col-sm-2 control-label">金額</label>
									<div class="col-sm-4">
										<input class="text-input small-input" type="text" name="price_show" id="price_show"  value="<?php echo $price;?>" disabled />
									</div>
								</div>

								<br/>
								<div class="form-group">
									<label class="col-sm-2 control-label">付款狀態</label>
									<div class="col-sm-4">
										<input class="text-input small-input" type="text" name="pay_status_show" id="pay_status_show"  value="<?php echo $pay_status;?>" disabled />
									</div>
								</div>

								<br/>
								<div class="hr-line-dashed"></div>
								<div class="form-group">
									<label class="col-md-2 control-label">充電開始時間</label>
									<!--<input type="text" name="field-name2" class="form-control" data-mask="0000/00/00" data-mask-clearifnotmatch="true" placeholder="yyyy/mm/dd" />-->
									<div class="col-sm-2">
										<input placeholder="西元年-月-日" class="form-control custom-width" id="start_time_show" name="start_time_show" value="<?php echo $start_time; ?>" disabled>
									</div>
								</div>

								<br/>
								<div class="hr-line-dashed"></div>
								<div class="form-group">
									<label class="col-md-2 control-label">充電開始時間</label>
									<!--<input type="text" name="field-name2" class="form-control" data-mask="0000/00/00" data-mask-clearifnotmatch="true" placeholder="yyyy/mm/dd" />-->
									<div class="col-md-2">
										<input placeholder="西元年-月-日" class="form-control custom-width" id="stop_time_show" name="stop_time_show" value="<?php echo $stop_time; ?>" disabled>
									</div>
								</div>

								<br/>
								<div class="hr-line-dashed"></div>
								<div class="form-group">
									<label class="col-sm-2 control-label">繳費狀態</label>
									<div class="col-sm-4">
										<input class="text-input small-input" type="text" name="pay_status_show" id="pay_status_show"  value="<?php echo $pay_status;?>" disabled />
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
												for ($i = 0; $i < count($g_history_array_avalible); $i++) {
													$opt_value 		= $g_history_array_avalible[$i];
													$opt_value_zhtw = $g_history_array_avalible4zhtw[$i];
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