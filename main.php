<?php
    include_once "common/entry.php";
	session_start();
	global $g_app_title;
	if ($_SESSION['accname']=="") {
		header("Location: logout.php");
	}
	$adv_display = (intval($_SESSION['authority']) == 0) ? "" : "display: none";
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<meta name="renderer" content="webkit" />

		<title><?php echo $g_app_title; ?></title>


		<!--[if lt IE 9]>
			<meta http-equiv="refresh" content="0;ie.html" />
		<![endif]-->

		<link rel="shortcut icon" href="favicon.ico?" />
		<link href="css/bootstrap.min.css?v=3.3.7" rel="stylesheet" />
		<link href="css/font-awesome.min.css?v=4.4.0" rel="stylesheet" />
		<link href="css/animate.css" rel="stylesheet" />
		<link href="css/style.css?v=4.1.0" rel="stylesheet" />
		<link href="css/jquery.contextMenu.min.css" rel="stylesheet"/>
	</head>

	<body class="fixed-sidebar full-height-layout gray-bg" style="overflow: hidden;">
		<div id="wrapper">
			<!--左侧导航开始-->
			<nav class="navbar-default navbar-static-side" role="navigation">
				<div class="nav-close"><i class="fa fa-times-circle"></i></div>
				<div class="sidebar-collapse">
					<ul class="nav" id="side-menu">
						<li class="nav-header">
							<div class="dropdown profile-element">
								<!--<span><img alt="image" class="img-circle" src="img/profile_small.jpg" /></span>-->
								<a data-toggle="dropdown" class="dropdown-toggle" href="#">
									<span class="clear">
										<span class="block m-t-xs"><strong class="font-bold"><?php echo $_SESSION['accname'];?></strong></span>
										<span class="text-muted text-xs block">管理員<b class="caret"></b></span>
									</span>
								</a>
								<ul class="dropdown-menu animated fadeInRight m-t-xs">
									<!--<li><a class="J_menuItem" href="form_avatar.html">修改头像</a></li>
									<li><a class="J_menuItem" href="profile.html">个人资料</a></li>
									<li><a class="J_menuItem" href="contacts.html">联系我们</a></li>
									<li><a class="J_menuItem" href="mailbox.html">信箱</a></li>
									<li class="divider"></li>-->
									<li><a href="login.php">登出</a></li>
								</ul>
							</div>
							<div class="logo-element">JTG</div>
						</li>
						<li>
							<a href="#">
								<i class="fa fa-home"></i>
								<span class="nav-label">主控台</span>
							</a>
						</li>
						<li style="<?=$adv_display?>">
							<a href="#"><i class="fa fa-edit"></i> <span class="nav-label">設定</span><span class="fa arrow"></span></a>
							<ul class="nav nav-second-level">
								<li><a class="J_menuItem tabReload" href="set/chargecell4zhudong.php">竹東車柱與車格設定</a></li>
								<li><a class="J_menuItem tabReload" href="set/chargecell4miaoli.php">苗栗車柱與車格設定</a></li>
								<li><a class="J_menuItem tabReload" href="set/chargecell4nantou.php">南投車柱與車格設定</a></li>
								<li><a class="J_menuItem tabReload" href="set/fares.php">費率設定</a></li>
							</ul>
						</li>
						<li>
							<a href="#"><i class="fa fa-edit"></i> <span class="nav-label">充電紀錄</span><span class="fa arrow"></span></a>
							<ul class="nav nav-second-level">
								<li><a class="J_menuItem tabReload" href="history/history4zhudong.php" style="<?=$adv_display?>">竹東歷史紀錄</a></li>
								<li><a class="J_menuItem tabReload" href="history/history4miaoli.php">苗栗歷史紀錄</a></li>
								<li><a class="J_menuItem tabReload" href="history/history4nantou.php" style="<?=$adv_display?>">南投歷史紀錄</a></li>
							</ul>
						</li>
						<li style="<?=$adv_display?>">
							<a href="#"><i class="fa fa-edit"></i> <span class="nav-label">佔位與充電訂單</span><span class="fa arrow"></span></a>
							<ul class="nav nav-second-level">
								<li><a class="J_menuItem tabReload" href="parkingoverview/zhudong.php">竹東佔位與充電訂單</a></li>
								<li><a class="J_menuItem tabReload" href="parkingoverview/miaoli.php">苗栗佔位與充電訂單</a></li>
								<li><a class="J_menuItem tabReload" href="parkingoverview/nantou.php">南投佔位與充電訂單</a></li>
							</ul>
						</li>
						<li>
							<a href="#"><i class="fa fa-edit"></i> <span class="nav-label">統計資料</span><span class="fa arrow"></span></a>
							<ul class="nav nav-second-level">
								<li style="<?=$adv_display?>"><a class="J_menuItem tabReload" href="report_gov4zhudong.php">竹東統計資料</a></li>
								<li><a class="J_menuItem tabReload" href="report_gov4miaoli.php">苗栗統計資料</a></li>
								<li style="<?=$adv_display?>"><a class="J_menuItem tabReload" href="report_gov4nantou.php">南投統計資料</a></li>
							</ul>
						</li>
					</ul>
				</div>
			</nav>
			<!--左侧导航结束-->
			<!--右侧部分开始-->
			
			<div id="page-wrapper" class="gray-bg dashbard-1">
				<div class="row border-bottom">
					<!--<nav class="navbar navbar-static-top" role="navigation" style="margin-bottom: 0;">
						<div class="navbar-header">
							<a class="navbar-minimalize minimalize-styl-2 btn btn-primary" href="#"><i class="fa fa-bars"></i> </a>
						</div>
						<ul class="nav navbar-top-links navbar-right">
							<li class="dropdown">
								<a class="dropdown-toggle count-info" data-toggle="dropdown" href="#"> <i class="fa fa-envelope"></i> <span class="label label-warning" id="notread1"></span> </a>
								<ul class="dropdown-menu dropdown-messages">
									<li class="m-t-xs">
										<div class="dropdown-messages-box">
											<a href="profile.html" class="pull-left">
												<img alt="image" class="img-circle" src="" />
											</a>
											<div class="media-body">
												<small class="pull-right">一小時前</small>
												<strong>未審核</strong> 南陽路 006500
												<br />
												<small class="text-muted">2024-04-11 15:00:22</small>
											</div>
										</div>
									</li>
									<li class="divider"></li>
									<li>
										<div class="dropdown-messages-box">
											<a href="profile.html" class="pull-left">
												<img alt="image" class="img-circle" src="" />
											</a>
											<div class="media-body">
												<small class="pull-right text-navy">兩小時前</small>
												<strong>未審核</strong> 南陽路 006600
												<br />
												<small class="text-muted">2024-04-11 14:00:22</small>
											</div>
										</div>
									</li>
									<li class="divider"></li>
									<li>
										<div class="text-center link-block">
											<a class="J_menuItem" href="parkingAudit.php"> <i class="fa fa-envelope"></i> <strong> 詳細</strong> </a>
										</div>
									</li>
								</ul>
							</li>
						</ul>
					</nav>-->
				</div>
				<div class="row content-tabs">
					<button class="roll-nav roll-left J_tabLeft"><i class="fa fa-backward"></i></button>
					<nav class="page-tabs J_menuTabs">
						<div class="page-tabs-content">
							<a href="javascript:;" class="active J_menuTab" data-id="">首頁</a>
						</div>
					</nav>
					<button class="roll-nav roll-right J_tabRight"><i class="fa fa-forward"></i></button>
					<div class="btn-group roll-nav roll-right">
						<button class="dropdown" data-toggle="dropdown">頁籤操作<span class="caret"></span></button>
						<ul role="menu" class="dropdown-menu dropdown-menu-right">
							<li class="tabCloseCurrent"><a>關閉當前</a></li>
							<li class="J_tabCloseOther"><a>關閉其他</a></li>
							<li class="J_tabCloseAll"><a>全部關閉</a></li>
						</ul>
					</div>
					<a href="#" class="roll-nav roll-right tabReload"><i class="fa fa-refresh"></i>刷新</a>
				</div>
				<div class="row J_mainContent" id="content-main">
					<iframe class="J_iframe" name="iframe0" width="100%" height="100%" src="" frameborder="0" data-id="" seamless></iframe>
				</div>
				<div class="footer">
					<div class="pull-right">&copy; 2025 </div>
				</div>
			</div>
			<!--右侧部分结束-->
			<!--右侧边栏开始-->
			<div id="right-sidebar">
				<div class="sidebar-container">
					<ul class="nav nav-tabs navs-3">
						<li  class="active">
							<a data-toggle="tab" href="#tab-3">
								審核進度
							</a>
						</li>
					</ul>

					<div class="tab-content">
						<div id="tab-3" class="tab-pane">
							<div class="sidebar-title">
								<h3><i class="fa fa-cube"></i> 最新任務</h3>
								<small><i class="fa fa-tim"></i> 您当前有14个任务，10个已完成</small>
							</div>

							<ul class="sidebar-list">
								<li>
									<a href="#">
										<div class="small pull-right m-t-xs">9小时以后</div>
										<h4>市场调研</h4>
										按要求接收教材；

										<div class="small">已完成： 22%</div>
										<div class="progress progress-mini">
											<div style="width: 22%;" class="progress-bar progress-bar-warning"></div>
										</div>
										<div class="small text-muted m-t-xs">项目截止： 4:00 - 2015.10.01</div>
									</a>
								</li>
								<li>
									<a href="#">
										<div class="small pull-right m-t-xs">9小时以后</div>
										<h4>可行性报告研究报上级批准</h4>
										编写目的编写本项目进度报告的目的在于更好的控制软件开发的时间,对团队成员的 开发进度作出一个合理的比对

										<div class="small">已完成： 48%</div>
										<div class="progress progress-mini">
											<div style="width: 48%;" class="progress-bar"></div>
										</div>
									</a>
								</li>
								<li>
									<a href="#">
										<div class="small pull-right m-t-xs">9小时以后</div>
										<h4>立项阶段</h4>
										东风商用车公司 采购综合综合查询分析系统项目进度阶段性报告武汉斯迪克科技有限公司

										<div class="small">已完成： 14%</div>
										<div class="progress progress-mini">
											<div style="width: 14%;" class="progress-bar progress-bar-info"></div>
										</div>
									</a>
								</li>
								<li>
									<a href="#">
										<span class="label label-primary pull-right">NEW</span>
										<h4>设计阶段</h4>
										<!--<div class="small pull-right m-t-xs">9小时以后</div>-->
										项目进度报告(Project Progress Report)
										<div class="small">已完成： 22%</div>
										<div class="small text-muted m-t-xs">项目截止： 4:00 - 2015.10.01</div>
									</a>
								</li>
								<li>
									<a href="#">
										<div class="small pull-right m-t-xs">9小时以后</div>
										<h4>拆迁阶段</h4>
										科研项目研究进展报告 项目编号: 项目名称: 项目负责人:

										<div class="small">已完成： 22%</div>
										<div class="progress progress-mini">
											<div style="width: 22%;" class="progress-bar progress-bar-warning"></div>
										</div>
										<div class="small text-muted m-t-xs">项目截止： 4:00 - 2015.10.01</div>
									</a>
								</li>
								<li>
									<a href="#">
										<div class="small pull-right m-t-xs">9小时以后</div>
										<h4>建设阶段</h4>
										编写目的编写本项目进度报告的目的在于更好的控制软件开发的时间,对团队成员的 开发进度作出一个合理的比对

										<div class="small">已完成： 48%</div>
										<div class="progress progress-mini">
											<div style="width: 48%;" class="progress-bar"></div>
										</div>
									</a>
								</li>
								<li>
									<a href="#">
										<div class="small pull-right m-t-xs">9小时以后</div>
										<h4>获证开盘</h4>
										编写目的编写本项目进度报告的目的在于更好的控制软件开发的时间,对团队成员的 开发进度作出一个合理的比对

										<div class="small">已完成： 14%</div>
										<div class="progress progress-mini">
											<div style="width: 14%;" class="progress-bar progress-bar-info"></div>
										</div>
									</a>
								</li>
							</ul>
						</div>
					</div>
				</div>
			</div>
			<!--右侧边栏结束-->
			
		</div>

		<!-- 全局js -->
		<script src="js/jquery.min.js?v=2.1.4"></script>
		<script src="js/bootstrap.min.js?v=3.3.7"></script>
		<script src="js/plugins/metisMenu/jquery.metisMenu.js"></script>
		<script src="js/plugins/slimscroll/jquery.slimscroll.min.js"></script>
		<script src="js/plugins/contextMenu/jquery.contextMenu.min.js"></script>
		<script src="js/plugins/layer/layer.min.js"></script>

		<!-- 自定义js -->
		<script src="js/hplus.js?v=4.1.0"></script>
		<script type="text/javascript" src="js/contabs.js"></script>

		<!-- 第三方插件 -->
		<script src="js/plugins/pace/pace.min.js"></script>
	</body>
</html>
<script>


var intervalID = window.setInterval(
  auditR_notread,
  1000*300
);
var intervalID = window.setInterval(
  audit_notread,
  1000*300
);
function auditR_notread() {
	$.ajax({
    url: "auditR_notread.php",
    type: "GET",
    success: function(response) {
		document.getElementById("notreadR").innerHTML = response;
		//document.getElementById("notreadR1").innerHTML = response;
    },
    error: function() {
      console.log("ajax error!");
    }
	});
}	
auditR_notread();

function audit_notread() {
	$.ajax({
    url: "audit_notread.php",
    type: "GET",
    success: function(response) {
		document.getElementById("notread").innerHTML = response;
		//document.getElementById("notread1").innerHTML = response;
    },
    error: function() {
      console.log("ajax error!");
    }
	});
}	
audit_notread();
</script>