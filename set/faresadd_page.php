<?php
    include_once "../common/entry.php";
	global $g_app_title, $g_base_avalible, $g_base_avalible_zhtw;
	session_start();
	if ($_SESSION['accname'] == "") {
		header("Location: ../logout.php");
	}

	$member_id  = 'JTG_CHARGE UI';
	$area 		= isset($_POST['area']) ? $_POST['area'] : '';
	$ret_page 	= isset($_POST['ret_page']) ? $_POST['ret_page'] : '';
	$charge_type = ['DC', 'AC'];
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
						<h5>新增 費率 資料</h5>
                </div>
				  <div>
					<form action="faresmodify.php" method="Post" name='frm1' id='frm1' class="form-horizontal">
						<input type="hidden" name="act" id="act"  value="Add"/>
						<input type="hidden" name="ret_page" id="ret_page" value="<?=$ret_page?>">
						<div>
							<div class="ibox float-e-margins">
								<br/>
								<div class="form-group">
									<label class="col-sm-2 form-label">區域</label>
									<div class="col-sm-4">
										<input class="text-input small-input" type="text" name="station_area" id="station_area"  value="" />
									</div>
								</div>

								<br/>
								<br/>
								<div class="form-group">
									<label class="col-sm-2 form-label">充電類型</label>
									<!--<input type="text" name="field-name2" class="form-control" data-mask="0000/00/00" data-mask-clearifnotmatch="true" placeholder="yyyy/mm/dd" />-->
									<div class="col-sm-2">
										<select name="charge_type" id="charge_type" class="form-control custom-width">
											<?php
											// var_dump($g_history_array_avalible);
												for ($i = 0; $i < count($charge_type); $i++) {
													$cur_charge = $charge_type[$i];
													$opt_value 		= $cur_charge;
													$opt_value_zhtw = $cur_charge;
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
								<br/>
								<div class="form-group">
									<label class="col-sm-2 form-label">規則名稱</label>
									<div class="col-sm-4">
										<input class="text-input small-input" type="text" name="rule_name" id="rule_name"  value="" />
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
											placeholder="請輸入規則內容"></textarea>
									</div>
								</div>

								<br/>
								<br/>
								<div class="form-group">
									<label class="col-sm-2 form-label">金額</label>
									<div class="col-sm-4">
										<input class="text-input small-input" type="text" name="amount" id="amount"  value="" />
									</div>
								</div>

								<br/>
								<br/>
								<div class="form-group">
									<label class="col-sm-2 form-label">營業時間</label>
									<div class="col-sm-4">
										<input class="text-input small-input" type="text" name="open" id="open"  value="" />
									</div>
								</div>

								<br/>
								<br/>
								<div class="form-group">
									<label class="col-sm-2 form-label">費率使用日期(起)</label>
									<div class="col-sm-4">
										<input class="text-input small-input" type="date" name="fare_start_date" id="fare_start_date" value="" />
									</div>
								</div>

								<br/>
								<br/>
								<div class="form-group">
									<label class="col-sm-2 form-label">費率使用日期(迄)</label>
									<div class="col-sm-4">
										<input class="text-input small-input" type="date" name="fare_end_date" id="fare_end_date" value="" />
									</div>
								</div>

								<br/>
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

	function Cancel()
	{
		window.location.replace('<?=$ret_page?>.php');
	}
</script>