<?php
    include_once "../common/entry.php";
    global $g_app_title, $g_history_array_avalible4zhtw, $array_paymethod4zhtw, $g_dst_cell_plate_path;
    session_start();
    if ($_SESSION['accname'] == "") {
        header("Location: ../logout.php");
    }
    $accname = strtolower($_SESSION['accname']);
    $showAdv = ($accname == "emma" || $accname == "nancy" || $accname == "administrator") ? true : false;

    $member_id  = 'JTG_CHARGE UI';
    $day        = strftime("%Y-%m-%d", time());
    $input_date = date('Y-m-d', strtotime($day." 0 days"));
    $area       = '竹東';

    $act     = isset($_POST['act']) ? $_POST['act'] : '';
    $SDate   = isset($_POST['txtSDate']) ? $_POST['txtSDate'] : '';
    $EDate   = isset($_POST['txtEDate']) ? $_POST['txtEDate'] : '';
    $avalible= isset($_POST['avalible']) ? $_POST['avalible'] : '';
    $station_name   = isset($_POST['station_name']) ? $_POST['station_name'] : '';
    
    $station_list = [];
    try {
        $remote_ip = get_remote_ip();
        $db = new CXDB($remote_ip);
        $conn_res = $db->connect($link, $member_id, "");
        if ($conn_res["status"] == "true") {
            $sql_station = "SELECT DISTINCT station_name 
                            FROM order_chargecell 
                            WHERE station_name LIKE '$area%'
                            ORDER BY station_name ASC";
            $res_station = mysqli_query($link, $sql_station);
            while ($row_s = mysqli_fetch_array($res_station)) {
                $station_list[] = $row_s['station_name'];
            }
        }
    } catch (Exception $e) {
    }
?>
<!DOCTYPE html>
<>
<head>
    <meta charset="utf-8" />
    <title><?php echo $g_app_title; ?></title>
    <link href="../css/bootstrap.min.css" rel="stylesheet" />
    <link href="../css/font-awesome.css" rel="stylesheet" />
    <link href="../css/style.css" rel="stylesheet" />
    <link href="../css/parkingoverview.css" rel="stylesheet" />

    <!-- jQuery (必須最先載入) -->
    <script src="../js/jquery.min.js"></script>

    <!-- Bootstrap (依賴 jQuery) -->
     <script src="../js/bootstrap.min.js"></script> <!-- 不是 bootstrap.bundle.min.js -->
    <!-- <script src="../js/bootstrap.bundle.min.js"></script> -->


    <style>
        .rotate-icon { transition: transform 0.3s ease; }
        .rotate-icon.rotate { transform: rotate(180deg); }
        .hiddenRow { padding: 0 !important; }
        
  /* Modal 滿版 */
#imageModal .modal-dialog {
    max-width: 100%;      /* 滿寬 */
    width: 100%;
    height: 100%;
    margin: 0;
    padding: 0;
}

#imageModal .modal-content {
    width: 100%;
    height: 100%;
    border: none;
    border-radius: 0;
    background-color: transparent; /* 可選，去掉背景 */
}

#imageModal .modal-body {
    width: 100%;
    height: 100%;
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 0;
}

#modalImage {
    max-width: 100%;
    max-height: 100%;
    width: auto;
    height: auto;
}

  /* 移除 footer 以避免占空間（可選） */
  #imageModal .modal-footer {
    display: flex;
    justify-content: center;
  }
    </style>
</head>
<body class="gray-bg">
<div class="container-fluid">

    <!-- 搜尋條件 -->
    <div class="ibox">
        <div class="ibox-title"><h5>搜尋條件</h5></div>
        <div class="ibox-content">
            <form action="zhudong.php" method="Post" name='frm1' id='frm1' class="form-inline">
                <input type="hidden" name="act" id="act" value=""/>
                <input type="hidden" name="id" id="id" value="">
                <input type="hidden" name="ret_page" id="ret_page" value="">
                <div class="form-group">
                    <label>日期(起)</label>
                    <input type="date" name="txtSDate" id="txtSDate" value="<?=$SDate;?>" class="form-control" />
                </div>
                <div class="form-group mx-sm-3">
                    <label>日期(迄)</label>
                    <input type="date" name="txtEDate" id="txtEDate" value="<?=$EDate;?>" class="form-control" />
                </div>

                <div class="form-group mx-sm-3">
                    <label>站點名稱</label>
                    <select name="station_name" id="station_name" class="form-control">
                        <option value="">全部</option>
                        <?php foreach ($station_list as $sname) { ?>
                            <option value="<?= htmlspecialchars($sname) ?>"
                                <?= ($station_name == $sname ? 'selected' : '') ?>>
                                <?= htmlspecialchars($sname) ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
                <button type="submit" class='btn btn-info'>搜尋</button>
            </form>
        </div>
    </div>

    <!-- 資料列表 -->
    <div class="ibox">
        <div class="ibox-title">
            <table>
                <tr>
                    <td><h3>資料列表</h3></td><td>&nbsp;&nbsp;</td>
                    <td><input type="text" id="tableFilter" class="form-control" placeholder="輸入關鍵字搜尋資料"></td>
                </tr>
            </table>
        </div>
        <div class="ibox-content">
            <table class="table myDataTables table-hover">
                <thead class="thead-light">
                    <tr>
                        <th>#</th>
                        <th>建立日期</th>
                        <th>訂單編號</th>
                        <th>站點名稱</th>
                        <th>充電樁名稱</th>
                        <th>充電槍ID</th>
                        <th>車格</th>
                        <th>車號</th>
                        <th>充電槍使用狀態</th>
                        <th>佔用費</th>
                        <th>佔位付款狀態</th>
                        <th>充電費</th>
                        <th>充電付款狀態</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $sd = ($SDate!="") ? $SDate." 00:00:00" : $input_date." 00:00:00";
                $ed = ($EDate!="") ? $EDate." 23:59:59" : $input_date." 23:59:59";

                if ($station_name != "") {
                    $station_name_safe = mysqli_real_escape_string($link, $station_name);
                    $station_condiction = " AND station_name LIKE '%$station_name_safe%'";
                } else {
                    $station_condiction =  " AND station_name LIKE '$area%'";

                }

                $sql = "SELECT * FROM order_chargecell
                        WHERE ((enter_time >= '$sd' AND enter_time <= '$ed')
                        OR (charge_start_time >= '$sd' AND charge_start_time <= '$ed'))
                        $station_condiction
                        ORDER BY create_date DESC";
				// echo $sql."<br>";
                $idx = 0;

                try {
                    $remote_ip = get_remote_ip();
                    $db = new CXDB($remote_ip);
                    $conn_res = $db->connect($link, $member_id, "");
                    if ($conn_res["status"] == "true") {
                        if ($result = mysqli_query($link, $sql)) {
                            if (mysqli_num_rows($result) > 0) {
                                while ($row = mysqli_fetch_array($result)) {
                                    $idx++;
                                    $id = $row['nid'];
                                    $avalible_str = $g_history_array_avalible4zhtw[$row['avalible']];
                                    $paymethod_str = (strlen($row['pay_method']) > 0) ? $array_paymethod4zhtw[$row['pay_method']] : '';
                                    $in_use_str = (strlen($row['charge_order_serial_number']) > 0) ? '' : '(佔位)';


                                    $in_use_pay_status = "待付款";
                                    $in_use_status_color = "";
                                    if ($row['in_use_pay_status'] == 1) {
                                        $in_use_pay_status = "已付款";
                                        $in_use_status_color = "alert alert-success";
                                    } else if ($row['pay_status'] == 2) {
                                        $in_use_pay_status = "付款失敗";
                                        $in_use_status_color = "alert alert-danger";
                                    }

                                    $charge_pay_status = "待付款";
                                    $charge_status_color = "";
                                    if ($row['charge_pay_status'] == 1) {
                                        $charge_pay_status = "已付款";
                                        $charge_status_color = "alert alert-success";
                                    } else if ($row['pay_status'] == 2) {
                                        $charge_pay_status = "付款失敗";
                                        $charge_status_color = "alert alert-danger";
                                    }

                                    $status = "無操作$in_use_str";
                                    $status_color = "";
                                    if ($row['status'] == 0) {
                                        $status = "充電槍就緒(待機)";
                                        $status_color = "alert alert-info";
                                    } else if ($row['status'] == 1) {
                                        $status = "充電準備中(充電槍已連接電動車，尚未啟動充電)";
                                        $status_color = "alert alert-info";
                                    } else if ($row['status'] == 2) {
                                        $status = "充電中(充電槍已連接電動車，並啟動充電)";
                                        $status_color = "alert alert-warning";
                                    } else if ($row['status'] == 3) {
                                        $status = "充電樁暫停輸電中，提醒您前往查看車輛是否達到充電上限，車端是否暫停充電，或使用APP暫停充電";
                                        $status_color = "alert alert-danger";
                                    } else if ($row['status'] == 4) {
                                        $status = "電動車不接受充電服務$in_use_str";
                                        $status_color = (strlen($in_use_str) > 0) ? "" : "alert alert-danger";
                                    } else if ($row['status'] == 5) {
                                        $status = "RFID$in_use_str";
                                        $status_color = (strlen($in_use_str) > 0) ? "" : "alert alert-success";
                                    } else if ($row['status'] == 6) {
                                        $status = "充電完成，尚未拔槍";
                                        $status_color = "alert alert-primary";
                                    } else if ($row['status'] == 7) {
                                        $status = "[其他]$in_use_str";
                                        $status_color = "alert alert-warning";
                                    }
									?>
                                    <!-- 外層 row -->
                                    <tr class="accordion-toggle" data-toggle="collapse" data-target="#detail<?= $idx ?>" aria-expanded="false">
                                        <td><?= $idx ?></td>
                                        <td><?= $row['create_date'] ?></td>
                                        <td><?= $row['order_no'] ?></td>
                                        <td><?= $row['station_name'] ?></td>
                                        <td><?= $row['charger_name'] ?></td>
                                        <td><?= $row['charge_id'] ?></td>
                                        <td><?= $row['park_cell'] ?></td>
                                        <td><?= $row['enterPlateNum'] ?></td>
                                        <td class="<?= $status_color ?>"><?= $status ?></td>
                                        <td><?= "$".number_format($row['in_use_fee'], 0) ?></td>
                                        <td class="<?= $in_use_status_color ?>"><?= $in_use_pay_status ?></td>
                                        <td><?= "$".number_format($row['charge_fee'], 0) ?></td>
                                        <td class="<?= $charge_status_color ?>"><?= $charge_pay_status ?></td>
                                        <td class="text-end"><i class="fa fa-chevron-down rotate-icon"></i></td>
                                    </tr>

                                    <!-- 內層展開 -->
                                    <tr>
                                        <td colspan="14" class="hiddenRow">
                                            <div class="collapse" id="detail<?= $idx ?>">
                                                <table class="table mb-0 table-bordered bg-light">
                                                    <thead>
                                                    <tr>
                                                        <th>進位車號</th>
                                                        <th>離位車號</th>
                                                        <th>進位時間</th>
                                                        <th>離位時間</th>
                                                        <th>佔位開始</th>
                                                        <th>佔位結束</th>
                                                        <th>佔位時間</th>
                                                        <th>佔位繳費方式</th>
                                                        <th>佔位付款時間</th>
                                                        <th>充電單號</th>
                                                        <th>充電開始</th>
                                                        <th>充電結束</th>
                                                        <th>充電時間</th>
                                                        <th>停止原因</th>
                                                        <th>充電繳費方式</th>
                                                        <th>充電付款時間</th>
                                                        <th>最後更新</th>
                                                        <th>進位圖片</th>
                                                        <th>離位圖片</th>
                                                        <!-- <th>操作</th> -->
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    <tr>
                                                        <td><?= $row['enterPlateNum'] ?></td>
                                                        <td><?= $row['exitPlateNum'] ?></td>
                                                        <td><?= $row['enter_time'] ?></td>
                                                        <td><?= $row['exit_time'] ?></td>
                                                        <td><?= $row['in_use_start_time'] ?></td>
                                                        <td><?= $row['in_use_end_time'] ?></td>
                                                        <td><?= $row['in_use_time'] ?></td>
                                                        <td><?= $array_paymethod4zhtw[$row['in_use_pay_method']] ?? '' ?></td>
                                                        <td><?= $row['in_use_pay_time'] ?></td>
                                                        <td><?= $row['charge_order_id'] ?></td>
                                                        <td><?= $row['charge_start_time'] ?></td>
                                                        <td><?= $row['charge_stop_time'] ?></td>
                                                        <td><?= $row['charge_time'] ?></td>
                                                        <td><?= $row['charge_stop_reason'] ?></td>
                                                        <td><?= $array_paymethod4zhtw[$row['charge_pay_method']] ?? '' ?></td>
                                                        <td><?= $row['charge_pay_time'] ?></td>
                                                        <td><?= $row['modify_date'] ?></td>
														<td>
															<?php if (!empty($row['in_use_enteredImage'])) { ?>
																<img src="<?= "../log/imgcell".$row['in_use_enteredImage'] ?>"
                                                                    alt="進位圖片"
                                                                    style="width:320px; cursor:pointer;"
                                                                    class="preview-img"
                                                                    data-img="<?= "../log/imgcell".$row['in_use_enteredImage'] ?>" />
															<?php } ?>
														</td>
														<td>
															<?php if (!empty($row['in_use_exitingImage'])) { ?>
																<img src="<?= "../log/imgcell".$row['in_use_exitingImage'] ?>"
                                                                    alt="離位圖片"
                                                                    style="width:320px; cursor:pointer;"
                                                                    class="preview-img"
                                                                    data-img="<?= "../log/imgcell".$row['in_use_exitingImage'] ?>" />
															<?php } ?>
														</td>
                                                        <!-- <td>
                                                            <?php if ($showAdv) { ?>
                                                                <a href="javascript:GoEdit(<?= $id ?>)"><i class="fa fa-edit"></i></a>
                                                                <?php if ($row['avalible'] == "1") { ?>
                                                                    <a href="javascript:GoDel(<?= $id ?>)"><i class="fa fa-trash"></i></a>
                                                                <?php } ?>
                                                            <?php } else {
                                                                if ($row['avalible'] == "1") { ?>
                                                                    <a href="javascript:GoDel(<?= $id ?>)"><i class="fa fa-trash"></i></a>
                                                                <?php }
                                                            } ?>
                                                        </td> -->
                                                    </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </td>
                                    </tr>

                                    <?php
                                }
                            } else {
                                echo "<tr><td colspan='14'><div class='alert alert-warning'>查無紀錄</div></td></tr>";
                            }
                        }
                    }
                } catch (Exception $e) {
                } finally {
                    $data_close_conn = close_connection_finally($link, $remote_ip, $member_id);
                }
                ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- 圖片預覽 Modal -->
<div class="modal fade" id="imageModal" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-body text-center">
        <img id="modalImage" src="" alt="圖片" class="img-fluid" />
        <!-- <button type="button" class="btn btn-secondary" data-dismiss="modal">關閉</button> -->
      </div>
    </div>
  </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function(){
        document.querySelectorAll('tr.accordion-toggle').forEach(function(row){
            row.addEventListener('click', function(){
                var targetSelector = row.dataset.target;
                var icon = row.querySelector('.rotate-icon');
                var targetEl = document.querySelector(targetSelector);

                // 收起其他 collapse
                document.querySelectorAll('div.collapse').forEach(function(c){
                    if(c !== targetEl){
                        var bs = bootstrap.Collapse.getOrCreateInstance(c, {toggle:false});
                        bs.hide();
                        var otherIcon = c.closest('tr.accordion-toggle')?.querySelector('.rotate-icon');
                        if(otherIcon) otherIcon.classList.remove('rotate');
                    }
                });

                // 切換目標 collapse
                var bsTarget = bootstrap.Collapse.getOrCreateInstance(targetEl);
                bsTarget.toggle();

                // 處理箭頭
                targetEl.addEventListener('shown.bs.collapse', function(){ icon.classList.add('rotate'); });
                targetEl.addEventListener('hidden.bs.collapse', function(){ icon.classList.remove('rotate'); });
            });
        });
    });
    $(document).ready(function() {
        $("#tableFilter").on("keyup", function() {
            var value = $(this).val().toLowerCase();
            $(".myDataTables tbody tr.accordion-toggle").filter(function() {
                // 判斷每一行是否包含關鍵字
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);

                // 同步控制展開內容行顯示/隱藏
                var targetSelector = $(this).data('target');
                if(targetSelector) {
                    $(targetSelector).closest('tr').toggle($(this).is(":visible"));
                }
            });
        });

        // 點擊圖片，開啟 Modal
        $(document).on("click", ".preview-img", function() {
            var imgSrc = $(this).data("img");
            $("#modalImage").attr("src", imgSrc);

            var modalEl = $("#imageModal");
            modalEl.modal("show");

            // 點擊圖片以外的區域關閉
            modalEl.off("click.closeModal").on("click.closeModal", function(e) {
                // if (!$(e.target).is("#modalImage")) { // 如果點擊目標不是圖片
                    modalEl.modal("hide");
                // }
            });
        });
    });
</script>
</body>
</html>
