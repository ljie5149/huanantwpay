<?php
include "../db_tools.php";
include "monthlycore.php";
global $g_app_title;
session_start();
if ($_SESSION['accname'] == "") {
	header("Location: ../logout.php"); 
}
	
	// 費率選單
	$dft_amount = 2400; $discount_amount = 0; $data_idx = 0;
	$fares_select = getMonthlyFares($link, $dft_amount, $discount_amount, $array_fields);
	$fares_json = json_encode($fares_select);
	$extern_obj = "display:none";

	// 月租路段選單
	$park_select = getMonthlyParkInfo($link);

	$id = isset($_POST['id']) ? $_POST['id'] : '';
	if ($id != '') {
		$year = getDateTimeFormat4Zhudong("", "Y");
		$sql = "SELECT * FROM data_monthly_$year WHERE nid='$id'";
		if ($result = mysqli_query($link, $sql)) {
			if (mysqli_num_rows($result) > 0) {
				while ($row = mysqli_fetch_array($result)) {
					$plate_no 			= $row['plate_no'		];
					$name 				= $row['name'			];	
					$phone 				= $row['phone'			];
					$fares_sid 			= $row['fares_sid'		];
					$parkCode 			= $row['parkCode'		];
					$rent_start_date 	= $row['rent_start_date'];
					$rent_end_date 		= $row['rent_end_date'	];
					$rent_hint_date 	= $row['rent_hint_date'	];
					$pay_time 			= $row['pay_time'		];
					$pay_status 		= $row['pay_status'		];
					$pay_source 		= $row['pay_source'		];
					$pay_method 		= $row['pay_method'		];
					$amount 			= $row['amount'			];
					$discount 			= $row['discount'		];
					$b_amount 			= $row['b_amount'		];
					$avalible 			= $row['avalible'		];
					$remark 			= $row['remark'			];
				}
			}
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
					<form action="monthlymodify.php" method="Post" name='frm1' id='frm1'>
						<input type="hidden" name="act" id="act"  value="Edit"/>
						<input type="hidden" name="nid" id="nid"  value="<?php echo $id; ?>"/>
						
						<div>
							<div class="ibox float-e-margins">
								<br/>
								<div class="form-group">
									<label class="col-sm-2 control-label">車牌號碼</label>
									<div class="col-sm-4">
										<input class="text-input small-input" type="text" name="plate_no" id="plate_no"  value="<?php echo $plate_no;?>" />
									</div>
								</div>

								<br/>
								<div class="form-group">
									<label class="col-sm-2 control-label">繳款人</label>
									<div class="col-sm-4">
										<input class="text-input small-input" type="text" name="name" id="name"  value="<?php echo $name;?>" />
									</div>
								</div>

								<br/>
								<div class="form-group">
									<label class="col-sm-2 control-label">手機號碼</label>
									<div class="col-sm-4">
										<input class="text-input small-input" type="text" name="phone" id="phone"  value="<?php echo $phone;?>" />
									</div>
								</div>

								<br/>
								<div class="hr-line-dashed"></div>
								<div class="form-group">
									<label class="col-sm-2 control-label">費率sid</label>
									<!--<input type="text" name="field-name2" class="form-control" data-mask="0000/00/00" data-mask-clearifnotmatch="true" placeholder="yyyy/mm/dd" />-->
									<div class="col-sm-2">
										<select name="fares_sid" id="fares_sid" class="form-control custom-width">
											<?php
												for ($i = 0; $i < count($fares_select); $i++) {
													$cur_data = $fares_select[$i];
													$opt_value = $cur_data["nid"];
													$opt_value_zhtw = $cur_data["name"];
													$option_str = "<option value='$opt_value'";
													if ($opt_value == $fares_sid) $option_str .= " selected";
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
									<label class="col-md-2 control-label">場域選擇</label>
									<!--<input type="text" name="field-name2" class="form-control" data-mask="0000/00/00" data-mask-clearifnotmatch="true" placeholder="yyyy/mm/dd" />-->
									<div class="col-md-2">
										<select name="parkCode" id="parkCode" class="form-control custom-width">
												<?php
													for ($i = 0; $i < count($park_select); $i++) {
														$cur_data = $park_select[$i];
														$opt_value 		= $cur_data["parkCode"];
														$opt_value_zhtw = $cur_data["parkName"];
														$option_str = "<option value='$opt_value'";
														if ($opt_value == $parkCode) $option_str .= " selected";
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
									<label class="col-md-2 control-label">起租日期</label>
									<!--<input type="text" name="field-name2" class="form-control" data-mask="0000/00/00" data-mask-clearifnotmatch="true" placeholder="yyyy/mm/dd" />-->
									<div class="col-sm-2">
										<input placeholder="西元年-月-日" class="form-control custom-width" id="rent_start_date" name="rent_start_date" value="<?php echo $rent_start_date; ?>">
									</div>
								</div>

								<br/>
								<div class="hr-line-dashed"></div>
								<div class="form-group">
									<label class="col-md-2 control-label">結束日期</label>
									<!--<input type="text" name="field-name2" class="form-control" data-mask="0000/00/00" data-mask-clearifnotmatch="true" placeholder="yyyy/mm/dd" />-->
									<div class="col-md-2">
										<input placeholder="西元年-月-日" class="form-control custom-width" id="rent_end_date" name="rent_end_date" value="<?php echo $rent_end_date; ?>">
									</div>
								</div>

								<br/>
								<div class="hr-line-dashed"></div>
								<div class="form-group">
									<label class="col-md-2 control-label">提醒日期</label>
									<!--<input type="text" name="field-name2" class="form-control" data-mask="0000/00/00" data-mask-clearifnotmatch="true" placeholder="yyyy/mm/dd" />-->
									<div class="col-md-2">
										<input placeholder="西元年-月-日" class="form-control custom-width" id="rent_hint_date" name="rent_hint_date" value="<?php echo $rent_hint_date; ?>">
									</div>
								</div>

								<br/>
								<div class="hr-line-dashed"></div>
								<div class="form-group">
									<label class="col-md-2 control-label">繳費日期</label>
									<!--<input type="text" name="field-name2" class="form-control" data-mask="0000/00/00" data-mask-clearifnotmatch="true" placeholder="yyyy/mm/dd" />-->
									<div class="col-md-2">
										<input placeholder="西元年-月-日 時:分:秒" class="form-control custom-width" id="pay_time" name="pay_time" value="<?php echo $pay_time; ?>">
									</div>
								</div>

								<br/>
								<div class="hr-line-dashed"></div>
								<div class="form-group">
									<label class="col-sm-2 control-label">繳費狀態</label>
									<div class="col-sm-2">
										<select name="pay_status" id="pay_status" class="form-control custom-width">
											<?php
												for ($i = 0; $i < count($array_paystatus); $i++) {
													$opt_value = $array_paystatus[$i];
													$opt_value_zhtw = $array_paystatus4zhtw[$i];
													$option_str = "<option value='$opt_value'";
													if ($i == $pay_status) $option_str .= " selected";
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
									<label class="col-sm-2 control-label">繳費來源</label>
									<!--<input type="text" name="field-name2" class="form-control" data-mask="0000/00/00" data-mask-clearifnotmatch="true" placeholder="yyyy/mm/dd" />-->
									
									<div class="col-sm-2">
										<select name="pay_source" id="pay_source" class="form-control custom-width">
											<?php
												for ($i = 0; $i < count($array_paysource); $i++) {
													$opt_value = $array_paysource[$i];
													$opt_value_zhtw = $array_paysource4zhtw[$i];
													$option_str = "<option value='$opt_value'";
													if ($i == $pay_source) $option_str .= " selected";
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
									<label class="col-sm-2 control-label">繳費方式</label>
									<!--<input type="text" name="field-name2" class="form-control" data-mask="0000/00/00" data-mask-clearifnotmatch="true" placeholder="yyyy/mm/dd" />-->
									<div class="col-sm-2">
										<select name="pay_method" id="pay_method" class="form-control custom-width">
											<?php
												for ($i = 0; $i < count($array_paymethod); $i++) {
													$opt_value = $array_paymethod[$i];
													$opt_value_zhtw = $array_paymethod4zhtw[$i];
													$option_str = "<option value='$opt_value'";
													if ($i == $pay_method) $option_str .= " selected";
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
									<label class="col-sm-2 control-label">繳費金額</label>
									<!--<input type="text" name="field-name2" class="form-control" data-mask="0000/00/00" data-mask-clearifnotmatch="true" placeholder="yyyy/mm/dd" />-->
									<div class="col-sm-2">
										<input placeholder="2400" class="form-control custom-width" id="amount" name="amount" value="<?php echo $amount; ?>">
									</div>
								</div>

								<br/>
								<div class="hr-line-dashed"></div>
								<div class="form-group">
									<label class="col-sm-2 control-label">退款金額</label>
									<!--<input type="text" name="field-name2" class="form-control" data-mask="0000/00/00" data-mask-clearifnotmatch="true" placeholder="yyyy/mm/dd" />-->
									<div class="col-sm-2">
										<input placeholder="2400" class="form-control custom-width" id="b_amount" name="b_amount" value="<?php echo $b_amount; ?>">
									</div>
								</div>

								<br/>
								<div style="<?php echo $extern_obj; ?>">
									<br/>
									<div class="hr-line-dashed"></div>
									<div class="form-group">
										<label class="col-sm-2 control-label">折扣金額</label>
										<!--<input type="text" name="field-name2" class="form-control" data-mask="0000/00/00" data-mask-clearifnotmatch="true" placeholder="yyyy/mm/dd" />-->
										<div class="col-sm-2">
											<input placeholder="0" class="form-control custom-width" id="discount" name="discount" value="<?php echo $discount_amount; ?>">
										</div>
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
												for ($i = 0; $i < count($rent_array_avalible); $i++) {
													$opt_value 		= $rent_array_avalible[$i];
													$opt_value_zhtw = $rent_array_avalible4zhtw[$i];
													$option_str 	= "<option value='$opt_value'";
													if ($opt_value == $avalible) $option_str .= " selected";
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
	window.location.replace('monthly.php');
}

 
</script>