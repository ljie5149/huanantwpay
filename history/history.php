<?php
    include_once "../common/entry.php";
	global $g_app_title, $g_history_array_avalible4zhtw;
	session_start();
	if ($_SESSION['accname'] == "") {
		header("Location: ../logout.php");
	}
	$accname = $_SESSION['accname'];
	$accname = strtolower($accname);
	$showAdv = ($accname == "emma" || $accname == "nancy" || $accname == "administrator") ? true : false; // 竹東

	$member_id  = 'JTG_CHARGE UI';
	$day	  	= strftime("%Y-%m-%d", time());
	$input_date = date('Y-m-d', strtotime($day." 0 days"));
	$area 		= '竹東';
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
		<link href="css/plugins/dataTables/dataTables.bootstrap.css?" rel="stylesheet">
		
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
				    <form action="history.php" method="Post" name='frm1' id='frm1'>
					<input type="hidden" name="act" id="act"  value=""/>
					<input type="hidden" name="id" id="id" value="">
					
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
							$sql = "SELECT * FROM `log_history4user` WHERE DATE(start_time)>='$sd' AND DATE(start_time)<='$ed' AND station_name LIKE '$area%' ";
							
							
							$sql .= " ORDER BY start_time DESC;";
							
							// 充電資訊
							$charge_info = getDailyInfo($add_visible, $sd, $ed, $area);
							echo $charge_info;

							$sqldownload = $sql;
							$idx = 0;
							try {
								$remote_ip = get_remote_ip();
								$db = new CXDB($remote_ip);
								$conn_res = $db->connect($link, $member_id, "");
								if ($conn_res["status"] == "true") {
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
											echo "	  <th>站點名稱</th>";
											// echo "	  <th>單號</th>";
											echo "	  <th>第三方單號</th>";
											echo "	  <th>用電度數</th>";
											echo "	  <th>金額</th>";
											echo "	  <th>付款狀態</th>";
											echo "	  <th>充電開始時間</th>";
											echo "	  <th>充電開始時間</th>";
											echo "	  <th>已充電時間</th>";
											echo "	  <th>充電桩ID</th>";
											echo "	  <th>充電桩名稱</th>";
											echo "	  <th>年月</th>";
											echo "	  <th>最後更新日期</th>";
											echo "	  <th>槍號</th>";
											echo "	  <th>槍名</th>";
											echo "	  <th>槍頭類型</th>";
											echo "	  <th>停止充電原因</th>";
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
												$avalible_str  = $g_history_array_avalible4zhtw[$row['avalible']];
												// $paystatus_str = getMonthlyPaystatusZhtw($row['pay_status']);
												// $paymethod_str = getMonthlyPaymethodZhtw($row['pay_method']);
												// $paysource_str = getMonthlyPaysourceZhtw($row['pay_source']);
												// $fares_str 	   = getMonthlyFareZhtw($fares_select, $row['fares_sid']);
												// $park_str 	   = getMonthlyParkZhtw($park_select, $row['parkCode']);
												$jtg_pay_status = "待付款";
												$status_color = "";
												if ($row['jtg_pay_status'] == 1) { // 已付款
													$jtg_pay_status = "已付款";
													$status_color = " style='color:green'";
												} else if ($row['jtg_pay_status'] == 2) { // 付款失敗
													$jtg_pay_status = "付款失敗";
												}
												$color = "gray";
												if ($row['avalible'] == 0) $color = "lightgray";
												echo "  <tr style='color :$color'>";
												echo "    <td>".$idx."</td>";
												echo "    <td>".$row['create_date']."</td>";
												echo "    <td>".$row['station_name']."</td>";
												// echo "    <td>".$row['order_id']."</td>";
												echo "    <td>".$row['order_serial_number']."</td>";
												echo "    <td>".$row['kwh']."</td>";
												echo "    <td>".$row['price']."</td>";
												echo "    <td".$status_color.">".$jtg_pay_status."</td>";
												echo "    <td>".$row['start_time']."</td>";
												echo "    <td>".$row['stop_time']."</td>";
												echo "    <td>".$row['charge_time']."</td>";
												echo "    <td>".$row['charge_point_id']."</td>";
												echo "    <td>".$row['charger_name']."</td>";
												echo "    <td>".$row['month']."</td>";
												echo "    <td>".$row['modify_date']."</td>";
												echo "    <td>".$row['gun_id']."</td>";
												echo "    <td>".$row['gun_name']."</td>";
												echo "    <td>".$row['header_type']."</td>";
												echo "    <td>".$row['stop_reason']."</td>";
												echo "    <td>".$avalible_str."</td>";
												echo "    <td>";
												if ($showAdv) {
													echo "      <a href='javascript:GoEdit(".$id.")'><i class='fa fa-edit'></i></a>";
													// echo "      <a href='javascript:GoEditAll(".$id.")'><i class='fa fa-edit'></i></a>";
													if ($row['avalible'] == "1") {
														echo "      <a href='javascript:GoDel(".$id.")'><i class='fa fa-trash'></i></a>";
													}
												} else {
													if ($row['avalible'] == "1") {
														echo "      <a href='javascript:GoDel(".$id.")'><i class='fa fa-trash'></i></a>";
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
							
						//}
// 						mysqli_close($link);
						?>
					
					
					</div>
				</div>

				<?php
					// if (1) {
					// 	if ($havedata == 1) {
				   	// 		echo '<div class="col-md-12">
					// 				<br/>
					// 				</div>';
					// 	} else {
					// 		echo "沒有符合條件的資料!";
					// 	}
					// }
				?>
			
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

	// //外部js调用
	// laydate.render({
	// 	elem: '#txtSDate', //目标元素。由于laydate.js封装了一个轻量级的选择器引擎，因此elem还允许你传入class、tag但必须按照这种方式 '#id .class'
	// 	type: 'date',
	// 	format: 'yyyy-MM-dd',
	// 	event: 'focus' //响应事件。如果没有传入event，则按照默认的click
	// });
	// laydate.render({
	// 	elem: '#txtEDate', //目标元素。由于laydate.js封装了一个轻量级的选择器引擎，因此elem还允许你传入class、tag但必须按照这种方式 '#id .class'
	// 	type: 'date',
	// 	format: 'yyyy-MM-dd',
	// 	event: 'focus' //响应事件。如果没有传入event，则按照默认的click
	// });

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
	
	function Add()
	{
		document.frm1.action="historyadd_page.php";
		document.frm1.submit();	
	}
	function GoEdit(id)
	{
		document.frm1.action="historyedit_page.php";
		document.getElementById("id").value =id;
		document.frm1.submit();	
	}
	function GoEditAll(id)
	{
		document.frm1.action="historyditall_page.php";
		document.getElementById("id").value =id;
		document.frm1.submit();	
	}
	function GoDel(id)
	{
		if(confirm("確定刪除嗎?") == true)
		{
			document.frm1.action="historydel.php";
			document.getElementById("id").value =id;
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
