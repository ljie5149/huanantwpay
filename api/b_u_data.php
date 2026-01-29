<?php
    /*************************************************/
    /*                                               */
    /* b_u_data : back-end_update_data               */
    /*                                               */
    /* 給後台通用，不需每個頁面都要更新資料，就要新增API */
    /*                                               */
    /*************************************************/

    header('Content-Type: application/json');
    include("./../common/entry.php");
    global $g_root_dir;
    global $g_fldidx_name, $g_fldidx_comment, $g_fldidx_show, $g_fldidx_showbuthide, $g_fldidx_lockedit, $g_fldidx_srch;

	$data = array();
	$remote_ip = get_remote_ip();

	$table           = isset($_POST['table'          ]) ? $_POST['table'          ] : '';
	$caption         = isset($_POST['caption'        ]) ? $_POST['caption'        ] : '';
	$member_id       = isset($_POST['member_id'      ]) ? $_POST['member_id'      ] : '';
    $without_columns = isset($_POST['without_columns']) ? $_POST['without_columns'] : '';
    $image_path      = isset($_POST['image_path'     ]) ? $_POST['image_path'     ] : '';
    $base64image     = isset($_POST['file_content'   ]) ? $_POST['file_content'   ] : '';
    $mode            = isset($_POST['mode'           ]) ? $_POST['mode'           ] : '';
	$nid             = isset($_POST['rcd_id'         ]) ? $_POST['rcd_id'         ] : '';
    $prev_data       = isset($_POST['prev_data'      ]) ? $_POST['prev_data'      ] : '';
    $prev_data_array = json_decode($prev_data);

    
	$necessary_fields = ""; $is_base64_image = false;
    $necessary_fields = $g_fields_msgcenterneed;
    switch ($mode) {
        case "member"       : $is_base64_image = true; break;
        case "news"         : $is_base64_image = true; break;
        case "banner"       : $is_base64_image = true; break;
        case "sightseeing"  : $is_base64_image = true; break;
        case "store"        : $is_base64_image = true; break;
    }

    $db = new CXDB($remote_ip);
    try {
        $sql_param = ""; $new_value = "";
        $data = $db->connect($link, $member_id, "");
        if ($data["status"] == "true") {
            $column_info = $db->getTableColumnComments($link, $table, $without_columns);
            $uid = getSid($db, $link, $table, $member_id);

            $dst_filename_array = array(); // 上傳圖片
            if ($is_base64_image) {
                $tmp_image_info_base64 = isset($_POST['image_info_base64']) ? $_POST['image_info_base64'] : '';
                if (!empty($tmp_image_info_base64)) {
                    $image_info_base64    = json_decode($tmp_image_info_base64);
                    // var_dump($image_names);
                    $n = 1;
                    for ($i = 0; $i < count($column_info); $i++) {
                        $com = $column_info[$i];
                        
                        $field    = $com[$g_fldidx_name];
                        $name     = $com[$g_fldidx_comment];
                        $show     = ($com[$g_fldidx_show]         == "true");
                        $hidden   = ($com[$g_fldidx_showbuthide]  == "true");
                        $search   = ($com[$g_fldidx_srch]         == "true");
                        $lockedit = ($com[$g_fldidx_lockedit]     == "true");
                        if ($show) {
                            if (strEndWith($field, '_img')) {
                                for ($j = 0; $j < count($image_info_base64); $j++) {
                                    $com_base64 = $image_info_base64[$j];
                                    if ($com_base64->field == $field) {
                                        if (!empty($com_base64->filename) && !empty($com_base64->base64))
                                            $dst_filename_array[$field] = saveBase64Image($com_base64->base64, $g_root_dir, $image_path, $uid.'_'.$n++
                                                                                        , $com_base64->filename,  $mode);
                                        else
                                            $dst_filename_array[$field] = "";
                                    }
                                }
                            }
                        }
                    }
                }
            } else {
                $tmp_filename = isset($_POST['file_path']) ? $_POST['file_path'] : '';

                $dst_filename = ""; // 上傳圖片
                if (!empty($base64image) && !empty($tmp_filename))
                    $dst_filename = saveBase64Image($base64image, $g_root_dir, $image_path, $uid, $tmp_filename,  $mode);
            }

            for ($i = 0; $i < count($column_info); $i++) {
                $com = $column_info[$i];

                $field    = $com[$g_fldidx_name];
                $name     = $com[$g_fldidx_comment];
                $show     = ($com[$g_fldidx_show]         == "true");
                $hidden   = ($com[$g_fldidx_showbuthide]  == "true");
                $search   = ($com[$g_fldidx_srch]         == "true");
                $lockedit = ($com[$g_fldidx_lockedit]     == "true");
                if ($show) {
                    $val = isset($_POST[$field]) ? $_POST[$field] : '';
                    if ($field == "file_path" && !empty($dst_filename)) $val = $dst_filename;
                    if ($is_base64_image && 
                        strEndWith($field, '_img')) {
                        if (count($dst_filename_array) > 0)
                            $val = $dst_filename_array[$field];
                    }
                    $new_value = $val;
                    if (strEndWith($field, '_date')) {
                        $new_value = get24HourFormat($new_value);
                    }
                    // echo $field.":".$new_value. " val:".$val."<BR>";
                    foreach ($prev_data_array as $prev_key => $prev_value ) {
                        if ($prev_key == $field && !empty($val) && $new_value != $prev_value) {
                            $sql_param.= (strlen($sql_param) > 0) ? "," : "";
                            $sql_param.= $field.'="'.$new_value.'"';
                        }
                    }
                }
            }
            
            // 更新資料
            if (!empty($sql_param)) {
                $sql = 'UPDATE '.$table.' SET '.$sql_param.' WHERE nid='.$nid.';';
                // $sql = "UPDATE $table SET $sql_param WHERE nid=$nid;";
                // echo $sql."\n";
                $ret_msg = "";
                if ($db->execute($link, $sql, $ret_msg) > 0) {
                    $ret_str = '變更 '.$caption.' 資料成功 !';
                    $data = result_message("true", "0x0200", $ret_str, "");
                    $db->saveLog($link, $member_id, 'back-end 呼叫api', $caption, '變更資料', $data['responseMessage'], $sql);
                } else {
                    $ret_str = '變更 '.$caption.' 紀錄編號'.$nid.'資料無效!';
                    $data = result_message("false", "0x0206", $ret_str, "");
                    $db->saveLog($link, $member_id, 'back-end 呼叫api', $caption, '變更資料', $data['responseMessage'], $sql);
                }
            } else {
                $ret_str = $caption.' 沒有資料待變更！';
                $data = result_message("true", "0x0200", $ret_str, "");
            }
        }
    } catch (Exception $e) {
        $ret_str = '變更 '.$caption.' 資料失敗 !';
        $data = result_message("false", "0x0207", $ret_str." Except error:".$e->getMessage(), "");
        $db->saveLog($link, $member_id, 'back-end 呼叫api', $caption, $data['responseMessage'], "Except error:".$e->getMessage());
    } finally {
        $data_close_conn = close_connection_finally($link, $remote_ip, $member_id);
        if ($data_close_conn["status"] == "false") $data = $data_close_conn;
    }
	echo (json_encode($data, JSON_UNESCAPED_UNICODE));
?>