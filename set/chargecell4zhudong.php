<?php
    include_once "../common/entry.php";
	global $g_app_title, $g_base_avalible, $g_base_avalible_zhtw;
	session_start();
	if ($_SESSION['accname'] == "") {
		header("Location: ../logout.php");
	}
	$member_id  = 'JTG_CHARGE UI';
	$day	  	= strftime("%Y-%m-%d", time());
	$input_date = date('Y-m-d', strtotime($day." 0 days"));
	$area 		= '竹東';
	$showAdv = true;

	$member_id  = 'JTG_CHARGE UI';
	$remote_ip = get_remote_ip();
	$charges = [];
	try {
		$db = new CXDB($remote_ip);
		$conn_res = $db->connect($link_chargeInfo, $member_id, "");
		$sql_chargeInfo = "SELECT dc.id, dc.position, ds.station_name FROM data_charge dc LEFT JOIN data_station ds ON ds.marker_id = dc.marker_id WHERE dc.avalible = 1 AND ds.station_name IS NOT NULL AND ds.station_name LIKE '%$area%';";
		if ($result_chargeInfo = mysqli_query($link_chargeInfo, $sql_chargeInfo)) {
			if (mysqli_num_rows($result_chargeInfo) > 0) {
				while ($row_chargeInfo = mysqli_fetch_array($result_chargeInfo)) {
					$q[  'id'] = $row_chargeInfo['id'				];
					$q['name'] = $row_chargeInfo['station_name']."-".$row_chargeInfo['position'];
					array_push($charges, $q);
				}
			}
		}
	} catch (Exception $e) {
	} finally {
		$data_close_conn_chargeInfo = close_connection_finally($link_chargeInfo, $remote_ip, $member_id);
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
		
		<link href="../js/plugins/fancyboxv3/jquery.fancybox.css" rel="stylesheet">

		
	</head>
	<body class="gray-bg">
       <!-- Begin Page Content -->
        <div class="container-fluid">
          <!-- Page Heading -->
		<?php
			$act 		= isset($_POST['act'		]) ? $_POST['act'		] : '';
			$SDate		= isset($_POST['txtSDate'	]) ? $_POST['txtSDate'	] : '';
			$EDate		= isset($_POST['txtEDate'	]) ? $_POST['txtEDate'	] : '';
			$avalible	= isset($_POST['avalible'	]) ? $_POST['avalible'	] : '';
			// $parkCode 	= mysqli_real_escape_string($link,$parkCode);
		?>
 
          <!-- Content Row -->
          <div class="row">

            <div class="wrapper wrapper-content animated fadeInRight">

              <!-- Illustrations -->
			   <div class="col-sm-12">
                <div class="ibox float-e-margins">
                    <div class="ibox-title">
						<h5>搜尋條件</h5>
                </div>
				  <div>
				    <form action="chargecell4zhudong.php" method="Post" name='frm1' id='frm1'>
						<input type="hidden" name="act" id="act"  value=""/>
						<input type="hidden" name="id" id="id" value="">
						<input type="hidden" name="area" id="area" value="<?=$area?>">
						<input type="hidden" name="ret_page" id="ret_page" value="">
						
						<div>
							<div class="ibox float-e-margins">
								<div class="ibox-content">
									<div class="form-inline">

										<div class="form-group">
											<label class="form-label">日期(起)</label>
											<!--<input type="text" name="field-name1" class="form-control" data-mask="0000/00/00" data-mask-clearifnotmatch="true" placeholder="yyyy/mm/dd" />-->
											<input class="text-input small-input" type="date" name="txtSDate" id="txtSDate" value="<?=$SDate;?>" />
											<!-- <input placeholder="西元年-月-日" class="form-control custom-width" id="txtSDate" name="txtSDate" value="<?php echo $SDate; ?>"> -->
										</div>
										
										<div class="form-group">
											<label class="form-label">日期(迄)</label>
											<!--<input type="text" name="field-name2" class="form-control" data-mask="0000/00/00" data-mask-clearifnotmatch="true" placeholder="yyyy/mm/dd" />-->
											<input class="text-input small-input" type="date" name="txtEDate" id="txtEDate" value="<?=$EDate;?>" />
											<!-- <input placeholder="西元年-月-日" class="form-control custom-width" id="txtEDate" name="txtEDate" value="<?php echo $EDate; ?>"> -->
										</div>

										<div class="form-group">
											<div class="text-center">
												<button type="button" class='btn btn-info ml-auto' onclick='submit();'>搜尋</button> &nbsp;
											</div>
										</div>

										<div class="form-group">
											<div class="text-center">
												<button type="button" class='btn btn-success ml-auto' onclick="Add('<?=$area?>');">新增</button> &nbsp;
											</div>
										</div>

									</div>
								</div>
							</div>
						</div>
					</form>
				  </div>
              </div>
			</div>
			<div class="col-sm-12">
                <div class="ibox float-e-margins">
                    <div class="ibox-content">
						<h3>資料列表</h3>
						<?php
							$act = isset($_POST['act']) ? $_POST['act'] : '';
							$sd = $input_date;
							if ($SDate != "") {
								$sd = $SDate." 00:00:00";
							}
							$ed = $input_date;
							if ($EDate != "") {
								$ed = $EDate." 23:59:59";
							}
							$havedata = 0;
							$sql = "SELECT DISTINCT mc.nid, mc.*, ds.station_name FROM map_chargecell mc 
									LEFT JOIN data_charge dc ON dc.id = mc.charge_id
									LEFT JOIN data_station ds ON ds.marker_id = dc.marker_id
									WHERE dc.avalible = 1 AND ds.station_name IS NOT NULL AND ds.station_name LIKE '%$area%' ";
							
							
							$sql .= " ORDER BY nid ASC;";
							
							// 充電資訊
							$stations = [];
							$charge_info = getAreaInfo($stations, $area);
							echo $charge_info;
							// var_dump($stations);

							$sqldownload = $sql;
							$idx = 0;
							try {
								$db = new CXDB($remote_ip);
								$conn_res = $db->connect($link, $member_id, "");
								if ($conn_res["status"] == "true") {
									// echo $sql;
									$msg = "";
									if ($result = mysqli_query($link, $sql)) {
										// echo "rowcount =".mysqli_num_rows($result);
										if (mysqli_num_rows($result) > 0) {
											$havedata = 1;
											echo "<table class='table myDataTables' >";
											echo "  <thead>";
											echo "    <tr>";
											echo "	  <th>#</th>";
											echo "	  <th>建立日期</th>";
											echo "	  <th>充電槍序號</th>";
											echo "	  <th>路段代碼</th>";
											echo "	  <th>車格</th>";
											echo "	  <th>在位</th>";
											echo "	  <th>更新日期</th>";
											echo "	  <th>狀態</th>";
											echo "	  <th>操作</th>";	
											echo "    </tr>";
											echo "  </thead>";
											echo "  <tbody>";
											$auditsum = 0;
											$auditarray = array();

											while ($row = mysqli_fetch_array($result)) {
												$idx = $idx + 1;
												$id = $row['nid'];
												// echo $row['avalible'];
												$avalible_str  = $g_base_avalible4show[$row['avalible']];
												$charge_str = "";
    											foreach ($charges as $cur_charge) {
													if ($cur_charge['id'] == $row['charge_id']) {
														$charge_str = $cur_charge['name'];
														break;
													}
												}
												$status_color = "";
												$color = "gray";
												if ($row['avalible'] == 0) $color = "lightgray";
												echo "  <tr style='color :$color'>";
												echo "    <td>".$idx."</td>";
												echo "    <td>".$row['create_date']."</td>";
												echo "    <td>".$charge_str."</td>";
												echo "    <td>".$row['park_code']."</td>";
												echo "    <td>".$row['park_cell']."</td>";
												echo "    <td>".$row['placeholder']."</td>";
												echo "    <td>".$row['modify_date']."</td>";
												echo "    <td>".$avalible_str."</td>";
												echo "    <td>";
												if ($showAdv) {
													echo "      <a href='javascript:GoEdit(".$id.", \"".$area."\")'><i class='fa fa-edit'></i></a>";
													// echo "      <a href='javascript:GoEditAll(".$id.")'><i class='fa fa-edit'></i></a>";
													if ($row['avalible'] == "1") {
														echo "      <a href='javascript:GoDel(".$id.", \"".$area."\")'><i class='fa fa-trash'></i></a>";
													}
												} else {
													if ($row['avalible'] == "1") {
														echo "      <a href='javascript:GoDel(".$id.", \"".$area."\")'><i class='fa fa-trash'></i></a>";
													}
													// if (!($row['pay_status'] == "2" || $row['avalible'] == 0)) {
													// 	echo "      <a href='javascript:GoEdit(".$id.")'><i class='fa fa-edit'></i></a>";
													// }
												}
												
	//											echo " <button type='button' class='btn btn-info ml-auto' onclick=Edit('".$fid."')>編輯</button>";
												echo "    </td>";
												echo "  </tr>";
											}
											echo "  </tbody>";
											echo "</table>";
											echo "<br/>";
											$havedata = 1;
										}else{
											$page = 0;
											$pages = 0;
											$havedata = 0;
										}
									}
								}
							} catch (Exception $e) {
							} finally {
								$data_close_conn = close_connection_finally($link, $remote_ip, $member_id);
							}
						?>
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
		<script src="../js/plugins/fancyboxv3/jquery.fancybox.js"></script>
		
		<!-- layerDate plugin javascript -->
		<script src="../js/plugins/layer/laydate-master/laydate.js"></script>

		<script src="../js/jackyDate.js"></script>
	</body>
</html>
<script>
	$(document).ready(function () {
		$('.myDataTables').dataTable();

		/* Init DataTables */
		var oTable = $('#editable').dataTable();
	});

	// txtSDate 綁定 change 事件
	document.getElementById('txtSDate').addEventListener('change', function() {
		const selectedValue = this.value;
		console.log('Selected value:', selectedValue);
		
		const EDateInput = document.getElementById("txtEDate");
		const offsetDays = 90 - 1;
		let rent_end  = jsgetDateByNdays(selectedValue, offsetDays, 'YYYY-MM-DD', true);
		console.log('rent_end :', rent_end);


		const new_rentEnd = new Date(rent_end + ' 23:59:59');
		let cur_date = EDateInput.value;
		console.log('cur_date value:', cur_date);
		const targetDate = new Date(cur_date + ' 00:00:00');
		if (targetDate > new_rentEnd) {
			EDateInput.value = rent_end;
			console.log('rent_end 比 targetDate 晚');
		} else if (targetDate < new_rentEnd) {
			console.log('rent_end 比 targetDate 早');
		} else {
			console.log('兩個時間相同');
		}
	});
	document.getElementById('txtEDate').addEventListener('change', function() {
		const selectedValue = this.value;
		console.log('Selected value:', selectedValue);
		
		const SDateInput = document.getElementById("txtSDate");
		const offsetDays = -90;
		let rent_start  = jsgetDateByNdays(selectedValue, offsetDays, 'YYYY-MM-DD', true);
		console.log('rent_start :', rent_start);

		
		const new_rent_start = new Date(rent_start + ' 23:59:59');
		let cur_date = SDateInput.value;
		console.log('cur_date value:', cur_date);
		const targetDate = new Date(cur_date + ' 00:00:00');
		if (targetDate < new_rent_start) {
			SDateInput.value = rent_start;
			console.log('rent_start 比 targetDate 晚');
		} else if (targetDate > new_rent_start) {
			console.log('rent_start 比 targetDate 早');
		} else {
			console.log('兩個時間相同');
		}
	});

	function Add(area)
	{
		document.frm1.action="chargecelladd_page.php";
		document.getElementById("area").value =area;
		document.getElementById("ret_page").value ='chargecell4zhudong';
		document.frm1.submit();
	}
	function GoEdit(id, area)
	{
		console.log("Edit ID:", id, "Area:", area);
		document.frm1.action="chargecelledit_page.php";
		document.getElementById("id").value =id;
		document.getElementById("area").value =area;
		document.getElementById("ret_page").value ='chargecell4zhudong';
		document.frm1.submit();
	}
	function GoDel(id, area)
	{
		if (confirm("確定刪除嗎?") == true) {
			document.frm1.action="chargecelldel.php";
			document.getElementById("id").value =id;
			document.getElementById("area").value =area;
			document.getElementById("ret_page").value ='chargecell4zhudong';
			document.frm1.submit();
		}
	}
	function imgDisplay()
	{
		//alert("OK");
		var board = document.getElementById("imgshow");
		board.style.display = 'none';
	}
</script>
