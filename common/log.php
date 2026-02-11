<?php
	date_default_timezone_set("Asia/Taipei");
	ini_set('memory_limit','-1');
	$glogfile = "";
	$log_path = $g_log_path;
	
	function create_folder($name)
	{
		if (!file_exists($name)) 
			return mkdir($name, 0777, true);
		else
			return true;
	}
	function wtask_log($Task, $remote_ip, $log_msg)
	{
		global $g_trace_log;
		
		if ($g_trace_log["wtask_log"] == false) return;
		wh_log_core($remote_ip, $log_msg, $Task);
	}
	function wtask_log_Exception($Task, $remote_ip, $log_msg)
	{
		global $g_trace_log;
		if ($g_trace_log["wtask_log_Exception"] == false) return;
		wh_log_core($remote_ip, $log_msg, $Task);
	}
	
	// write log for JTG_API
	function JTG_wh_log($remote_ip, $log_msg, $Person_id = "", $file_header="api")
	{
		global $g_trace_log;
		if ($g_trace_log["JTG_wh_log"] == false) return;
		wh_log_core($remote_ip, $log_msg, $Person_id, $file_header);
	}
	
	// write log for JTG_API exception
	function JTG_wh_log_Exception($remote_ip, $log_msg, $Person_id = "", $file_header="api")
	{
		global $g_trace_log;
		if ($g_trace_log["JTG_wh_log_Exception"] == false) return;
		wh_log_core($remote_ip, $log_msg, $Person_id, $file_header);
	}
	
	// write log for func
	function wh_log($remote_ip, $log_msg, $Person_id = "", $file_header="api")
	{
		global $g_trace_log;
		if ($g_trace_log["wh_log"] == false) return;
		wh_log_core($remote_ip, $log_msg, $Person_id, $file_header);
	}
	
	// write log for func watch dog
	function wh_log_watch_dog($remote_ip, $log_msg, $Person_id = "", $file_header="api")
	{
		global $g_trace_log;
		if ($g_trace_log["wh_log_watch_dog"] == false) return;
		wh_log_core($remote_ip, $log_msg, $Person_id, $file_header);
	}
	
	// write log for func exception
	function wh_log_Exception($remote_ip, $log_msg, $Person_id = "", $file_header="api")
	{
		global $g_trace_log;
		if ($g_trace_log["wh_log_Exception"] == false) return;
		wh_log_core($remote_ip, $log_msg, $Person_id, $file_header);
	}
	
	// write log core
	function wh_log_core($remote_ip, $log_msg, $Person_id = "", $file_header)
	{
		global $log_path;
		global $glogfile;
		
		if (strlen($Person_id) > 0) $Person_id = "_".$Person_id;
		set_log_name($log_path, $Person_id, $file_header);
		create_folder($log_path);
		// echo "log version >>>>>>>".$glogfile;
		// echo $glogfile;
		file_put_contents($glogfile, date("Y-m-d H:i:s")."[".$remote_ip.", ".$Person_id."]  ------  ".$log_msg."\n", FILE_APPEND);
	}
	function set_log_name($dir, $Person_id, $file_header)
	{
		global $glogfile;
		
		// if (strlen($remote_ip) 			> 0) $remote_ip 			= "_".$remote_ip;
		// if (strlen($Remote_insurance_no) 	> 0) $Remote_insurance_no 	= "_".$Remote_insurance_no;
		$glogfile = $dir.$file_header.'_'.date('Ymd').$Person_id.'.log';
	}
?>
