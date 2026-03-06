<?php
/*
	1.1 測試環境連線參數 TEST
		◼ 介接網址URL：https://qrtest.hncb.com.tw/eWalletWebapp/api/POSApp/{服務名稱} 
		◼ VerifyCode：店家端POS系統身份驗證簽章驗MAC值，由銀行端提供會依各店家參數設
			定不同產生key值。 
		◼ Http head 設定： 
			content-type：application/json;charset=utf-8 
			User-Agent：POSAPI 
			X-KeyID:62cade5c-f841-432d-ae75-7da8b6348d4c 
		◼ HTTP body 格式：Json 


		20260121
		--------------------------------------------------------------------------
		測試資訊如下，目前信用卡的資料還未建檔，可以先測金融卡跟電支

		test verify code：0DA970575BB4EFEDA23ED301C4F4E36B

		特店代號 / 端末代號
		008537589950001 / 00000001 (金融卡、跨電支交易)
		008537589955001 / 50010001 (信用卡主掃交易)
		008537589956001 / 60010001 (信用卡被掃交易)

	1.2 正式環境連線參數 PROD
		◼ 介接網址URL：https://store.hncb.com.tw/eWalletWebapp/api/POSApp/{服務名稱} 
		◼ VerifyCode：店家端POS系統身份驗證簽章驗MAC值，由銀行端提供會依各店家參數設
			定不同產生key值。 
		◼ Http head設定： 
			content-type：application/json;charset=utf-8 
			User-Agent：POSAPI 
			X-KeyID:<<正式環境由銀行端提供，依各店家不同產生key值>> 
		◼ Http body 格式：Json 

		20260128
		--------------------------------------------------------------------------
		合歡山暗空公園鳶峰遊客中心停車場 
		刷卡機MID: 008537589951001  TID:00005501
		QRCODE主掃MID: 008537589955001  TID: 50010001
		QRCODE被掃MID: 008537589956001  TID: 60010001

		頭份親子館公有停車場
		刷卡機MID: 008537589951002  TID:00005501
		QRCODE主掃MID: 008537589955002  TID: 50020001
		QRCODE被掃MID: 008537589956002  TID: 60020001
*/
	$g_is_remote 			= true;
	$g_setpaystatus_notify	= true;
	$g_getinvoice_notify	= true;
	$g_setpaystatus_inquery	= false;
	$g_getinvoice_inquery	= false;
	$g_setpaystatus_order	= false;
	$g_getinvoice_order		= false;
	$g_show_request			= false;
	$g_test_mode 			= true;
	$g_start_year			= 2026;

	// 華南TWpay參數
	$g_3party_url 		= ($g_test_mode) ? "https://qrtest.hncb.com.tw/eWalletWebapp/api/POSApp/" :
										   "https://store.hncb.com.tw/eWalletWebapp/api/POSApp/";
	$g_jtg_key_id 		= ($g_test_mode) ? "62cade5c-f841-432d-ae75-7da8b6348d4c" :				// X-KeyID
										   "待提供";
	$g_verify_code 		= ($g_test_mode) ? "0DA970575BB4EFEDA23ED301C4F4E36B" : 				// 銀行給你的 VerifyCode
										   "待提供";

	$g_terminalId		= ($g_test_mode) ? "0012345" :
										   "待提供";

	/* (金融卡、跨電支交易) */
	$g_storeId4debitcard			= ($g_test_mode) ? "008537589950001" :									// 特店代號
										   "待提供";
	$g_endpointCode4debitcard		= ($g_test_mode) ? "00000001" :											// 端末代號
										   "待提供";
	
	/* (信用卡主掃交易) */
	$g_storeId4credit				= ($g_test_mode) ? "008537589955001" :									// 特店代號
										   "待提供";
	$g_endpointCode4credit			= ($g_test_mode) ? "50010001" :											// 端末代號
										   "待提供";
	
	/* (信用卡被掃交易) */
	$g_storeId4creditbyscan			= ($g_test_mode) ? "008537589956001" :									// 特店代號
										   "待提供";
	$g_endpointCode4creditbyscan	= ($g_test_mode) ? "60010001" :											// 端末代號
										   "待提供";

	$g_txnCurrency		= ($g_test_mode) ? "901" :												// 目前支援台幣，幣別碼為901
										   "901";
	$g_channelCode		= "TWP";
				

	// 資料庫
	// -----------------------------------------------------------------------------------------------------------------
	$g_db_ip		= '127.0.0.1'; // 當地 mysql ip，不用改
	$g_db_user		= "root";
	$g_db_pwd		= ($g_is_remote) ? "JTG@1qaz@WSX" : ""; // 

	$g_db_name		= "jtg_twpay";
	$g_proj_name 	= "huanantwpay";
	$g_proj_url 	= ($g_is_remote) ? 'http://43.200.219.248/' : 'http://localhost/華南TWpay/huanantwpay/';

	// 系統編解碼參數
	// -----------------------------------------------------------------------------------------------------------------
	$g_key 	= "YcL+NyCRl5FYMWhozdV5V8eu6qv3cLDL";	//uat
	$g_iv  	= "77215989@jotangi";
	$g_token_expire_sec = 3 * 24 * 60 * 60;

	// 系統參數
	// -----------------------------------------------------------------------------------------------------------------
	$g_exit_symbol = "---------------------------  ";

	$g_save2db = true;
	
	$g_trace_log   = [
					'JTG_wh_log'       			=> true,
					'JTG_wh_log_Exception'      => true,
					'wh_log'   					=> true,
					'wh_log_watch_dog'    		=> true,
					'wh_log_Exception'    		=> true,
					'wtask_log'    				=> true,
					'wtask_log_Exception'    	=> true
				   ];
    $g_base_avalible         			= ["1", "0"];
    $g_base_avalible4zhtw     			= ["啟用", "刪除"];
    $g_base_avalible4show     			= ["1" => "啟用", "0" => "刪除"];
	$g_history_array_avalible 			= ["0", "1"];
	$g_history_array_avalible4zhtw 		= ["廢單", "正常單"];
	$g_history_array_paystatus			= ["0", "1", "2"];
	$g_history_array_paystatus4zhtw		= ["待付款", "已付款", "付款失敗"];
	$array_paymethod					= ["0" => "現金", "1" => "刷卡", "2" => "linepay", "3" => "街口", "4" => "悠遊卡", "5" => "一卡通", "6" => "匯款", "7" => "ATM"];
	$array_paymethod4zhtw				= ["現金", "刷卡", "linepay", "街口", "悠遊卡", "一卡通", "匯款", "ATM"];

	// 路徑
	// -----------------------------------------------------------------------------------------------------------------
	$g_root_url			 				= $g_proj_url.$g_proj_name."/"					;
	$g_root_dir			 				= $_SERVER["DOCUMENT_ROOT"]."/".$g_proj_name.'/'	; // 網站根目錄	"/var/www/html/jtgOCPI/"
	$g_log_path		  	 				= $g_root_dir."log/"								; // log directory
	$g_src_cell_plate_path				= "/var/www/html/parkingpostV2/api/"				; // 來源車輛圖片(佔位時使用)
	$g_dst_cell_plate_path				= "/var/www/html/".$g_proj_name."/log/imgcell/"		; // 目的車輛圖片(佔位時使用)
	$g_xlsx_out_path		  	 		= $g_root_dir."excel/export/"						; // excel directory
	$g_xlsx_in_path		  	 			= $g_root_dir."excel/import/"						; // excel directory
	$g_json_path	  	 				= $g_root_dir."json/"								; // json directory
	$g_pdf_path		  	 				= $g_root_dir."pdf/"								; // pdf directory
	$g_images_dir 						= $g_root_dir."images/"								; // 照片 directory
	$g_live_dir 						= $g_root_dir."live/"								; // 照片 directory
	$g_attachment_dir 					= $g_root_dir."attachment/"							; // 附件照片 directory
	$g_watermark_src_url 				= $g_root_url."watermark.png"						; // 浮水印來源
	
	$g_newsimg_path 					= "images/upload/news/"								; // 最新消息上傳照片路徑
	$g_bannerimg_path 					= "images/upload/banner/"							; // 最新消息上傳照片路徑
	$g_fcmimg_path 						= "images/upload/fcm/"								; // 最新消息上傳照片路徑
	$g_productimg_path 					= "images/upload/pdct/"								; // 最新消息上傳照片路徑
	$g_memberimg_path 					= "images/upload/member/"							; // 最新消息上傳照片路徑
	$g_verifyimg_path 					= "images/verify/"									; // 最新消息上傳照片路徑
	
?>
