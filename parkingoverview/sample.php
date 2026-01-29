<?php
	include_once "../common/entry.php";
	global $g_app_title, $g_history_array_avalible4zhtw, $array_paymethod4zhtw;
	session_start();
	if ($_SESSION['accname'] == "") {
		header("Location: logout.php");
	}
	$accname = strtolower($_SESSION['accname']);
	$showAdv = ($accname == "emma" || $accname == "nancy" || $accname == "administrator") ? true : false;

	$member_id  = 'JTG_CHARGE UI';
	$day        = strftime("%Y-%m-%d", time());
	$input_date = date('Y-m-d', strtotime($day." 0 days"));
	$area       = '苗栗';

	$act     = isset($_POST['act']) ? $_POST['act'] : '';
	$SDate   = isset($_POST['txtSDate']) ? $_POST['txtSDate'] : '';
	$EDate   = isset($_POST['txtEDate']) ? $_POST['txtEDate'] : '';
	$avalible= isset($_POST['avalible']) ? $_POST['avalible'] : '';
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8" />
		<title><?php echo $g_app_title; ?></title>
		<link href="../css/bootstrap.min.css" rel="stylesheet" />
		<link href="../css/font-awesome.css" rel="stylesheet" />
		<link href="../css/style.css" rel="stylesheet" />
		<link href="../css/parkingoverview.css" rel="stylesheet" />
		<style>
			.rotate-icon { transition: transform 0.3s ease; }
			.rotate-icon.rotate { transform: rotate(180deg); }
			.hiddenRow { padding: 0 !important; }
		</style>
	</head>
	<body class="gray-bg">
		<div class="container-fluid">

			<!-- 搜尋條件 -->
			<div class="ibox">
				<div class="ibox-title"><h5>搜尋條件</h5></div>
				<div class="ibox-content">
					<form action="miaoli.php" method="Post" name='frm1' id='frm1' class="form-inline">
						<input type="hidden" name="act" id="act" value=""/>
						<input type="hidden" name="id" id="id" value="">
						<input type="hidden" name="ret_page" id="ret_page" value="">
						<div class="form-group">
							<label>日期(起)</label>
							<input type="date" name="txtSDate" id="txtSDate" value="<?=$SDate;?>" class="form-control" />
						</div>
						<div class="form-group mx-sm-3">
							<label>日期(迄)</label>
							<input type="date" name="txtEDate" id="txtEDate" value="<?=$EDate;?>" class="form-control" />
						</div>
						<button type="submit" class='btn btn-info'>搜尋</button>
					</form>
				</div>
			</div>

			<!-- 資料列表 -->
			<div class="ibox">
				<div class="ibox-title"><h3>資料列表</h3></div>
				<div class="ibox-content">
					<table class="table table-hover">
						<thead class="thead-light">
							<tr>
								<th>#</th>
								<th>建立日期</th>
								<th>站點名稱</th>
								<th>單號</th>
								<th>第三方單號</th>
								<th>用電度數</th>
								<th>金額</th>
								<th>付款方式</th>
								<th>付款狀態</th>
								<th></th>
							</tr>
						</thead>
						<tbody>
						<?php
						$sd = ($SDate!="") ? $SDate." 00:00:00" : $input_date;
						$ed = ($EDate!="") ? $EDate." 23:59:59" : $input_date;

						$sql = "SELECT * FROM `log_history4user` 
								WHERE DATE(start_time)>='$sd' AND DATE(start_time)<='$ed' 
								AND station_name LIKE '$area%' 
								ORDER BY start_time DESC;";
						$idx = 0;

						try {
							$remote_ip = get_remote_ip();
							$db = new CXDB($remote_ip);
							$conn_res = $db->connect($link, $member_id, "");
							if ($conn_res["status"] == "true") {
								if ($result = mysqli_query($link, $sql)) {
									if (mysqli_num_rows($result) > 0) {
										while ($row = mysqli_fetch_array($result)) {
											$idx++;
											$id = $row['nid'];
											$avalible_str = $g_history_array_avalible4zhtw[$row['avalible']];
											$paymethod_str = (strlen($row['pay_method']) > 0) ? $array_paymethod4zhtw[$row['pay_method']] : '';
											$jtg_pay_status = "待付款";
											$status_color = "";
											if ($row['jtg_pay_status'] == 1) {
												$jtg_pay_status = "已付款";
												$status_color = "text-success";
											} else if ($row['jtg_pay_status'] == 2) {
												$jtg_pay_status = "付款失敗";
												$status_color = "text-danger";
											}
						?>
							<!-- 外層 row -->
							<tr class="accordion-toggle" data-toggle="collapse" data-target="#detail<?= $idx ?>" aria-expanded="false">
								<td><?= $idx ?></td>
								<td><?= $row['create_date'] ?></td>
								<td><?= $row['station_name'] ?></td>
								<td><?= $row['order_id'] ?></td>
								<td><?= $row['order_serial_number'] ?></td>
								<td><?= $row['kwh'] ?> 度</td>
								<td class="text-success">$<?= $row['price'] ?></td>
								<td><?= $paymethod_str ?></td>
								<td class="<?= $status_color ?>"><?= $jtg_pay_status ?></td>
								<td class="text-end"><i class="fa fa-chevron-down rotate-icon"></i></td>
							</tr>

							<!-- 內層展開 -->
							<!-- 內層展開 -->
							<tr>
								<td colspan="10" class="hiddenRow">
									<div class="collapse" id="detail<?= $idx ?>">
										<table class="table mb-0 table-bordered bg-light">
											<thead>
												<tr>
													<th>充電開始</th>
													<th>充電結束</th>
													<th>已充電時間</th>
													<th>充電樁ID</th>
													<th>充電樁名稱</th>
													<th>年月</th>
													<th>最後更新日期</th>
													<th>槍號</th>
													<th>槍名</th>
													<th>槍頭類型</th>
													<th>停止原因</th>
													<th>狀態</th>
													<th>操作</th>
												</tr>
											</thead>
											<tbody>
												<tr>
													<td><?= $row['start_time'] ?></td>
													<td><?= $row['stop_time'] ?></td>
													<td><?= $row['charge_time'] ?></td>
													<td><?= $row['charge_point_id'] ?></td>
													<td><?= $row['charger_name'] ?></td>
													<td><?= $row['month'] ?></td>
													<td><?= $row['modify_date'] ?></td>
													<td><?= $row['gun_id'] ?></td>
													<td><?= $row['gun_name'] ?></td>
													<td><?= $row['header_type'] ?></td>
													<td><?= $row['stop_reason'] ?></td>
													<td><?= $avalible_str ?></td>
													<td>
														<?php if ($showAdv) { ?>
															<a href="javascript:GoEdit(<?= $id ?>)"><i class="fa fa-edit"></i></a>
															<?php if ($row['avalible'] == "1") { ?>
																<a href="javascript:GoDel(<?= $id ?>)"><i class="fa fa-trash"></i></a>
															<?php } ?>
														<?php } else { 
															if ($row['avalible'] == "1") { ?>
																<a href="javascript:GoDel(<?= $id ?>)"><i class="fa fa-trash"></i></a>
															<?php } 
														} ?>
													</td>
												</tr>
											</tbody>
										</table>
									</div>
								</td>
							</tr>

						<?php
										}
									} else {
										echo "<tr><td colspan='10'><div class='alert alert-warning'>查無紀錄</div></td></tr>";
									}
								}
							}
						} catch (Exception $e) {
						} finally {
							$data_close_conn = close_connection_finally($link, $remote_ip, $member_id);
						}
						?>
						</tbody>
					</table>
				</div>
			</div>
		</div>

		<script src="../js/jquery.min.js"></script>
		<script src="../js/bootstrap.bundle.min.js"></script>
		<script>
			document.addEventListener("DOMContentLoaded", function(){
				document.querySelectorAll('tr.accordion-toggle').forEach(function(row){
					row.addEventListener('click', function(){
						var targetSelector = row.dataset.target;
						var icon = row.querySelector('.rotate-icon');
						var targetEl = document.querySelector(targetSelector);

						// 收起其他 collapse
						document.querySelectorAll('div.collapse').forEach(function(c){
							if(c !== targetEl){
								var bs = bootstrap.Collapse.getOrCreateInstance(c, {toggle:false});
								bs.hide();
								var otherIcon = c.closest('tr.accordion-toggle')?.querySelector('.rotate-icon');
								if(otherIcon) otherIcon.classList.remove('rotate');
							}
						});

						// 切換目標 collapse
						var bsTarget = bootstrap.Collapse.getOrCreateInstance(targetEl);
						bsTarget.toggle();

						// 處理箭頭
						targetEl.addEventListener('shown.bs.collapse', function(){ icon.classList.add('rotate'); });
						targetEl.addEventListener('hidden.bs.collapse', function(){ icon.classList.remove('rotate'); });
					});
				});
			});
		</script>

	</body>
</html>
