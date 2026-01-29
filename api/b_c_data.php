<?php
    /*************************************************/
    /*                                               */
    /* b_c_data : back-end_create_data               */
    /*                                               */
    /* 給後台通用，不需每個頁面都要新增資料，就要新增API */
    /*                                               */
    /*************************************************/
    header('Content-Type: application/json');

    include("./../common/entry.php");
    global $g_images_dir, $g_root_dir;
    global $g_fldidx_name, $g_fldidx_comment, $g_fldidx_show, $g_fldidx_showbuthide, $g_fldidx_lockedit, $g_fldidx_srch;
    global $g_fields_memneed, $g_fields_pdctdetlneed;
    global $g_fields_msgcenterneed;
	$data = array();
	$remote_ip = get_remote_ip();

	$table           = isset($_POST['table'          ]) ? $_POST['table'          ] : '';
	$caption         = isset($_POST['caption'        ]) ? $_POST['caption'        ] : '';
	$member_id       = isset($_POST['member_id'      ]) ? $_POST['member_id'      ] : '';
    $without_columns = isset($_POST['without_columns']) ? $_POST['without_columns'] : '';
    $image_path      = isset($_POST['image_path'     ]) ? $_POST['image_path'     ] : '';
    $base64image     = isset($_POST['file_content'   ]) ? $_POST['file_content'   ] : '';
    $mode            = isset($_POST['mode'           ]) ? $_POST['mode'           ] : '';
    $unique_sid      = isset($_POST['unique_sid'     ]) ? $_POST['unique_sid'     ] : ''; // 如果是匯入的方式，且有定義 唯一sid則透過此參數傳入
    
	$necessary_fields = ""; $head_tag = ""; $is_base64_image = false;
    $necessary_fields = $g_fields_msgcenterneed;
    switch ($mode) {
        case "member":
            $role = isset($_POST['role']) ? $_POST['role'] : 'Stf';
            $necessary_fields = $g_fields_memneed[$role];
            $head_tag = 'MM';
            $is_base64_image = true; 
            break;
        case "news"         : $head_tag = 'NW'; $is_base64_image = true; break;
        case "banner"       : $head_tag = 'BN'; $is_base64_image = true;  break;
        case "sightseeing"  : $head_tag = 'SS'; $is_base64_image = true;  break;
        case "store"        : $head_tag = 'ST'; $is_base64_image = true;  break;
    }
    // echo $necessary_fields;
    $necessary_array = str2array($necessary_fields);
    $empty_fields = "null";
    $skip = false;
    if ($mode == "conferenceroom") {
        $title = isset($_POST['title']) ? $_POST['title'] : '';
        if (empty($member_id) || empty($table) || empty($mode) || empty($caption) || empty($title)) {
            $skip = true;
        }
    } else {
        if (empty($member_id) || empty($table) || empty($mode) || empty($caption)) {
            $skip = true;
        }
        for ($i = 0; $i < count($necessary_array); $i++) {
            $post_val = isset($_POST[$necessary_array[$i]]) ? $_POST[$necessary_array[$i]] : '';
            if (empty($post_val)) {
                $empty_fields.= (empty($empty_fields)) ? "" : ",";
                $empty_fields.= $necessary_array[$i];
                if(!$skip) $skip = true;
            }
        }
    }
    
    $db = new CXDB($remote_ip);
    try {

        $data = $db->connect($link, $member_id, "");
        if ($data["status"] == "true") {
            $uid = $unique_sid;
            if (empty($unique_sid))
                $uid = getSidSimple($table, $member_id, '');
            
            if ($mode == "member") {
                $fields = "sid,create_date";
                $values = '"'.$uid.'",NOW()';
            } else {
                $fields = "sid,edit_sid,create_date";
                $values = '"'.$uid.'","'.$member_id.'",NOW()';
            }
            $column_info = $db->getTableColumnComments($link, $table, $without_columns);

            if ($skip) {
                $empty_fields_zhtw = "";
                for ($i = 0; $i < count($column_info); $i++) {
                    $com = $column_info[$i];
                    if ($com[$g_fldidx_show] == "true") {
                        if (stripos($empty_fields, $com[$g_fldidx_name]) != false) {
                            $empty_fields_zhtw.= (empty($empty_fields_zhtw)) ? "" : ",";
                            $empty_fields_zhtw.= $com[$g_fldidx_comment];
                        }
                    }
                }
                if (!empty($empty_fields_zhtw)) {
                    $ret_str= "新增 [ $caption ] 資料異常，API 參數不全!<br>「 $empty_fields_zhtw 」不可為空值";
                    $data = result_message("false", "0x0206", $ret_str, '');
                    echo (json_encode($data, JSON_UNESCAPED_UNICODE));
                    return;
                }
            }

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
                    if ($field == "file_path") $val = $dst_filename;

                    if ($is_base64_image && 
                        strEndWith($field, '_img')) {
                        if (count($dst_filename_array) > 0)
                            $val = $dst_filename_array[$field];
                    }

                    if (!empty($val)) {
                        if (strEndWith($field, '_date')) $val = get24HourFormat($val);
                        
                        $fields .= (strlen($fields) > 0) ? "," : "";
                        $fields .= $field;
                        $values .= (strlen($values) > 0) ? "," : "";
                        $values .= '"'.$val.'"';
                    } else {
                        if ($field == "priority") {
                            $fields .= (strlen($fields) > 0) ? "," : ""; $fields .= $field;
                            $values .= (strlen($values) > 0) ? "," : ""; $values .= "2";
                        } else if ($field == "start_date" || $field == "modify_date") {
                            $fields .= (strlen($fields) > 0) ? "," : "";
                            $fields .= $field;
                            $values .= (strlen($values) > 0) ? "," : "";
                            $values .= "NOW()";
                        }
                    }
                }
            }

            // 新增資料
            $sql = 'INSERT INTO '.$table.' ('.$fields.') VALUES ('.$values.');';
            // echo $sql;
            if (strlen($fields) == 0) {
                $ret_str = '語法有誤 :'.$sql;
                $data = result_message("false", "0x0206", $ret_str, "");
                $db->saveLog($link, $member_id, 'back-end 呼叫api', $caption, '新增資料', $ret_str);
            } else {
                $ret_msg = "";
                if ($db->execute($link, $sql, $ret_msg) > 0) {
                    $ret_str = '新增 '.$caption.' 成功 !';
                    $data = result_message("true", "0x0200", $ret_str, "");
                    $db->saveLog($link, $member_id, 'back-end 呼叫api', $caption, '新增資料', $data['responseMessage'], $sql);
                }
            }
        }
    } catch (Exception $e) {
        $ret_str = '新增 '.$caption.' 異常 !';
        $data = result_message("true", "0x0207", $ret_str."Except error:".$e->getMessage(), "");
        $db->saveLog($link, $member_id, 'back-end 呼叫api', $caption, '新增資料', $data['responseMessage']);
    } finally {
        $data_close_conn = close_connection_finally($link, $remote_ip, $member_id);
        if ($data_close_conn["status"] == "false") $data = $data_close_conn;
    }
    
	echo (json_encode($data, JSON_UNESCAPED_UNICODE));
?>