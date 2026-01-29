<?php
    include_once "common/entry.php";
	global $g_app_title;
	session_start();
	
	$_SESSION['saveresult']	= "";
	$Error_login			="0";
	$member_id 				= "JTG_CHARGE";
	$func 					= "login";
    
	$remote_ip				= get_remote_ip();

	$act 					= isset($_POST['act']) ? $_POST['act'] : '';
	if ($act == "Login") {
		try {
			$db = new CXDB($remote_ip);
			$conn_res = $db->connect($link, $member_id, "");
			if ($conn_res["status"] == "true") {
				$userid = isset($_POST['UID']) ? $_POST['UID'] : '';
				$userid  = mysqli_real_escape_string($link,$userid);
				$userpwd = isset($_POST['UPWD']) ? $_POST['UPWD'] : '';
				$userpwd  = mysqli_real_escape_string($link,$userpwd);
				
				if (($userid != "") && ($userpwd != "")) {
					$sql = "SELECT * FROM sysuser where sid>0 ";
					if ($userid != "") {
						$sql = $sql." and user_id = '".$userid."'";
					}
					if ($userpwd != "") {
						$sql = $sql." and user_pwd='".$userpwd."'";
					}
					
					// echo $sql;
					//exit;
					//$result = mysql_query($sql) or die('MySQL query error');
					//$rowCount = mysql_num_rows($result);
					//echo $rowCount;
					//echo $sql;
					if ($result = mysqli_query($link, $sql)) {
						if (mysqli_num_rows($result) > 0) {
							$accname	= "";
							$authority	= 0;
							while($row = mysqli_fetch_array($result)) {
								$accname	= $row['user_name'];
								$authority	= $row['group_id' ];
							}
							$Error_login 			  = "0";
							$_SESSION['loginsid'	] = "0"			;
							$_SESSION['store_id'	] = "0"			;
							$_SESSION['userid'		] = $userid		;
							$_SESSION['accname'		] = $accname	;
							$_SESSION['authority'	] = $authority	;
							$_SESSION['downloadlog'	] = ""			;
							
							//Save_Log($link,$userid,$accname,0,'Login',$authority);
							header("Location: main.php");
							exit;

						} else {
							header("Location: login.php");
							exit;
							
							$_SESSION['loginsid'	] = "";
							$_SESSION['userid'		] = "";
							$_SESSION['accname'		] = "";
							$_SESSION['authority'	] = "";
							$_SESSION['downloadlog'	] = "";
							$_SESSION['saveresult'	] = "";
							$Error_login="l";
						}
						mysqli_close($link);
					}
				} else {
					$Error_login="l";
				}
			}
		} catch (Exception $e) {
			$res = result_message("false", "0xE209", "Exception error! error detail:".$e->getMessage(), []);
			JTG_wh_log_Exception($remote_ip, $func." ".get_error_symbol($data["status_code"])." ".$data["status_message"], $member_id, 'UI');
		} finally {
			$data_close_conn = close_connection_finally($link, $remote_ip, $member_id);
			JTG_wh_log_Exception($remote_ip, $func." ".get_error_symbol($data_close_conn["status_code"])." ".$data_close_conn["status_message"], $member_id, 'UI');
		}
	}

?>
<!DOCTYPE html>
<html>

<head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">


	<title><?php echo $g_app_title; ?></title>
    <meta name="description" content="">

    <link rel="shortcut icon" href="favicon.ico"> <link href="css/bootstrap.min.css?v=3.3.7" rel="stylesheet">
    <link href="css/font-awesome.css?v=4.4.0" rel="stylesheet">

    <link href="css/animate.css" rel="stylesheet">
    <link href="css/style.css?v=4.1.0" rel="stylesheet">
    <!--[if lt IE 9]>
    <meta http-equiv="refresh" content="0;ie.html" />
    <![endif]-->
    <script>if(window.top !== window.self){ window.top.location = window.location;}</script>
</head>

<body class="gray-bg">

    <div class="middle-box text-center loginscreen  animated fadeInDown">
        <div>
            <div>

                <h1 class="logo-name">JTG</h1>

            </div>
            <h3><?php echo $g_app_title; ?></h3>

            <form class="m-t" role="form" action="login.php" method="post">
				<input type="hidden" name="act" id="act"  value="Login"/>
                <div class="form-group">
                    <input type="text" name="UID" class="form-control" placeholder="帳號" required="">
                </div>
                <div class="form-group">
                    <input type="password" name="UPWD" class="form-control" placeholder="密碼" required="">
                </div>
                <button type="submit" class="btn btn-primary block full-width m-b">登入</button>

<!--
                <p class="text-muted text-center"> <a href="login.html#"><small>忘记密码了？</small></a> | <a href="register.html">注册一个新账号</a>
                </p>
-->
            </form>
        </div>
    </div>

    <!-- 全局js -->
    <script src="js/jquery.min.js?v=2.1.4"></script>
    <script src="js/bootstrap.min.js?v=3.3.7"></script>

</body>

</html>
