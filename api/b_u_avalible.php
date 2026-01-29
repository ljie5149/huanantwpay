<?php
    /*************************************************/
    /*                                               */
    /* b_u_avalible : back-end_update_avalible       */
    /*                                               */
    /* 給後台通用，不需每個頁面都要更新狀態，就要新增API */
    /*                                               */
    /*************************************************/

    include("./../common/entry.php");
	$data = array();
	$remote_ip = get_remote_ip();

	$table           = isset($_POST['table'          ]) ? $_POST['table'          ] : '';
	$caption         = isset($_POST['caption'        ]) ? $_POST['caption'        ] : '';
	$member_id       = isset($_POST['member_id'      ]) ? $_POST['member_id'      ] : '';
    $mode            = isset($_POST['mode'           ]) ? $_POST['mode'           ] : '';
	$nid             = isset($_POST['rcd_id'         ]) ? $_POST['rcd_id'         ] : '';
    $avalible        = isset($_POST['avalible'       ]) ? $_POST['avalible'       ] : '';
    $avalible_str    = isset($_POST['avalible_array' ]) ? $_POST['avalible_array' ] : '';
    $avalible_array = json_decode($avalible_str);
    $avalible_key = '';
    $db = new CXDB($remote_ip);
    try {
        $ret_msg = "";
        foreach ($avalible_array as $key => $value ) {
            if ($value == $avalible) $avalible_key = $key;
        }
        $data = $db->connect($link, $member_id, "");
        if ($data["status"] == "true") {
            // 新增資料
            $sql = 'UPDATE '.$table.' SET avalible="'.$avalible.'" WHERE nid='.$nid.' AND avalible<>"'.$avalible.'";';
            if ($db->execute($link, $sql, $ret_msg) > 0) {
                $ret_str = '變更 '.$caption.' 狀態 '.$avalible_key.' 成功 !';
                $data = result_message("true", "0x0200", $ret_str, "");
                $db->saveLog($link, $member_id, 'back-end 呼叫api', $caption, '變更狀態', $data['responseMessage'], $sql);
            } else {
                $ret_str = '變更 '.$caption.' 紀錄編號'.$nid.'狀態為 '.$avalible_key.' 無效，已是該狀態 !';
                $data = result_message("false", "0x0206", $ret_str, "");
                $db->saveLog($link, $member_id, 'back-end 呼叫api', $caption, '變更狀態', $data['responseMessage'], $sql);
            }
        }
    } catch (Exception $e) {
        $ret_str = '變更 '.$caption.' 資料狀態 '.$avalible_key.' 失敗 !';
        $data = result_message("false", "0x0207", $ret_str." Except error:".$e->getMessage(), "");
        $db->saveLog($link, $member_id, 'back-end 呼叫api', $caption, $data['responseMessage'], "Except error:".$e->getMessage());
    } finally {
        $data_close_conn = close_connection_finally($link, $remote_ip, $member_id);
        if ($data_close_conn["status"] == "false") $data = $data_close_conn;
    }
    header('Content-Type: application/json');
	echo (json_encode($data, JSON_UNESCAPED_UNICODE));
?>