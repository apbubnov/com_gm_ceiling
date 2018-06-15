<?php
/**
 * @version    CVS: 0.1.7
 * @package    Com_Gm_ceiling
 * @author     SpectralEye <Xander@spectraleye.ru>
 * @copyright  2016 SpectralEye
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');

$user       = JFactory::getUser();
$userId     = $user->get('id');

$jinput = JFactory::getApplication()->input;
$project = $jinput->get('project',null,'INT');
$model = Gm_ceilingHelpersGm_ceiling::getModel('mountersorder');

$calculation_ids = $model->GetCalculation($project);

if (!empty($calculation_ids)) {
    $DataOfTransport = Gm_ceilingHelpersGm_ceiling::calculate_transport($project);
}

?>

<?=parent::getButtonBack();?>

<link rel="stylesheet" href="components/com_gm_ceiling/views/mountersorder/tmpl/CSS/style.css" type="text/css" />

<div id="content-tar">
    <h2 class="center tar-color-414099">Просмотр проекта №<?php echo $project; ?></h2>
    <ul class="nav nav-tabs" role="tablist" id="tabs">
        <li class="nav-item">
            <a class="nav-link active" data-toggle="tab" href="#summary" role="tab">Общее</a>
        </li>
        <?php if (!empty($calculation_ids)) { 
            ?>
            <?php foreach ($calculation_ids as $value) { ?>
                <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#ceiling<?php echo $value->id; ?>" role="tab"><?php echo $value->calculation_title; ?></a>
                </li>
            <?php } ?>
        <?php } ?>
    </ul>
    <div class="tab-content" id="content-tabs">
        <div id="summary" class="content-tab tab-pane active" role="tabpanel">
            <div class = "overflow">
                <table id="table-all-orders" cols=4>
                    <tr class="caption">
                        <td>Наименование</td>
                        <td>Цена, ₽</td>
                        <td>Количество</td>
                        <td>Стоимость, ₽</td>
                    </tr>
                    <?php if (!empty($calculation_ids)) { ?>
                        <?php $AllCalc = [];?>
                        <?php foreach ($calculation_ids as $value) { ?>
                            <?php $DataOfProject = Gm_ceilingHelpersGm_ceiling::calculate_mount(0, $value->id, null);?>
                            <?php foreach ($DataOfProject["mounting_data"] as $val) { ?>
                                <?php
                                    if (!array_key_exists($val["title"], $AllCalc)) {
                                        $AllCalc[$val["title"]] = ["title"=>$val["title"], "gm_salary"=>$val["gm_salary"], "dealer_salary"=>$val["dealer_salary"], "quantity"=>$val["quantity"], "gm_salary_total"=>$val["gm_salary_total"], "dealer_salary_total"=>$val["dealer_salary_total"]];
                                    } else {
                                        $AllCalc[$val["title"]]["quantity"] += $val["quantity"];
                                        $AllCalc[$val["title"]]["gm_salary_total"] += $val["gm_salary_total"];
                                        $AllCalc[$val["title"]]["dealer_salary_total"] += $val["dealer_salary_total"];
                                    }
                                ?>
                            <?php } ?>
                        <?php } ?>
                    <?php } ?>
                    <?php $AllSum = 0;?>
                    <?php foreach ($AllCalc as $val) { ?>
                        <tr>
                            <td class="left"><?php echo $val["title"]; ?></td>
                            <?php if ($user->dealer_id == 1) { ?>
                                <td><?php echo $val["gm_salary"]; ?></td>
                            <?php } else { ?>
                                <td><?php echo $val["dealer_salary"]; ?></td>
                            <?php } ?>
                            <td><?php echo $val["quantity"]; ?></td>
                            <?php if ($user->dealer_id == 1) { ?>
                                <td><?php echo $val["gm_salary_total"]; ?></td>
                                <?php $AllSum += $val["gm_salary_total"]; ?>
                            <?php } else { ?>
                                <td><?php echo $val["dealer_salary_total"]; ?></td>
                                <?php $AllSum += $val["gm_salary_total"]; ?>
                            <?php } ?>
                        </tr>
                    <?php } ?>
                    <tr class="caption">
                        <td colspan=3 style="text-align: right;">Итого, ₽:</td>
                        <td id="sum-all"><?php echo $AllSum; ?></td>
                    </tr>
                    <?php if (!empty($DataOfTransport)) { ?>
                        <tr class="caption">
                            <td colspan="4" style="text-align: center; background-color: #ffffff;">Транспортные расходы</td>
                        </tr>
                        <tr class="caption">
                            <td>Вид транспорта</td>
                            <td>Кол-во км</td>
                            <td>Кол-во выездов</td>
                            <td>Стоимость, ₽</td>
                        </tr>
                        <tr>
                            <td>
                                <?php echo $DataOfTransport["transport"]; ?>
                            </td>
                            <td>
                                <?php echo $DataOfTransport["distance"]; ?>
                            </td>
                            <td>
                                <?php echo $DataOfTransport["distance_col"]; ?>
                            </td>
                            <td>
                                <?php echo $DataOfTransport["mounter_sum"]; ?>
                            </td>
                        </tr>
                    <?php } ?>
                </table>
            </div>
        </div>
        <?php foreach ($calculation_ids as $value) { ?>
            <div id="ceiling<?php echo $value->id; ?>" class="content-tab tab-pane" role="tabpanel">
                <?php if (!empty($value->details)) { ?>
                    <div>
                        Примечание к потолку: <?php echo $value->details; ?>
                    </div>
                <?php } ?>
                <div class="ceiling">
                    <img src="/calculation_images/<?php echo md5("calculation_sketch".$value->id); ?>.svg" class="image-ceiling">
                </div>
                <div class = "overflow">
                    <table id="table-order-<?php echo $value->id; ?>" cols=4 class="table-order">
                        <tr class="caption">
                            <td>Наименование</td>
                            <td>Цена, ₽</td>
                            <td>Количество</td>
                            <td>Стоимость, ₽</td>
                        </tr>
                        <?php $DataOfProject = Gm_ceilingHelpersGm_ceiling::calculate_mount(0, $value->id, null); ?>
                        <?php if (!empty($DataOfProject)) { ?>
                            <?php foreach ($DataOfProject["mounting_data"] as $val) { ?>
                                <tr>
                                    <td class="left">
                                        <?php echo $val["title"]; ?>
                                    </td>
                                    <?php if ($user->dealer_id == 1) { ?>
                                        <td>
                                            <?php echo $val["gm_salary"]; ?>
                                        </td>
                                    <?php } else { ?>
                                        <td>
                                            <?php echo $val["dealer_salary"]; ?>
                                        </td>
                                    <?php } ?>
                                    <td>
                                        <?php echo $val["quantity"]; ?>
                                    </td>
                                    <?php if ($user->dealer_id == 1) { ?>
                                        <td>
                                            <?php echo $val["gm_salary_total"]; ?>
                                        </td>
                                    <?php } else { ?>
                                        <td>
                                            <?php echo $val["dealer_salary_total"]; ?>
                                        </td>
                                    <?php } ?>
                                </tr>
                            <?php } ?> 
                            <tr class="caption">
                                <td colspan=3 style="text-align: right;">Итого, ₽:</td>
                                <?php if ($user->dealer_id == 1) { ?>
                                    <td>
                                        <?php echo $DataOfProject["total_gm_mounting"]; ?>
                                    </td>
                                <?php } else { ?>
                                    <td>
                                        <?php echo $DataOfProject["total_dealer_mounting"]; ?>
                                    </td>
                                <?php } ?>
                            </tr>
                        <?php } ?>
                    </table>
                </div>
            </div>
        <?php } ?>
    </div>
    <div id="buttons-cantainer">
        <button id="begin" class="btn" disabled><i class="fa fa-play fa-tar" aria-hidden="true" ></i> Монтаж начат</button>
        <button id="complited" class="btn modal" disabled><i class="fa fa-check" aria-hidden="true" ></i> Монтаж выполнен</button>
        <button id="underfulfilled" class="btn modal" disabled><i class="fa fa-pause fa-tar" aria-hidden="true" ></i> Монтаж недовыполнен</button>
    </div>
    <div id="modal-window-container-tar">
        <button id="close-tar" type="button"><i class="fa fa-times fa-times-tar" aria-hidden="true"></i></button>
        <div id="modal-window-1-tar">
            <div align=center>
                <p>Выполнено:</p>
                <p>
                    <input type="checkbox" id="obag" class="inp-cbx" data-status = "24" style="display: none">
                    <label for="obag" class="cbx">
                      <span>
                        <svg width="12px" height="10px" viewBox="0 0 12 10">
                          <polyline points="1.5 6 4.5 9 10.5 1"></polyline>
                        </svg>
                      </span>
                      <span>обагечивание;</span>
                    </label>
                </p>
                <p>
                    <input type="checkbox" id="natyazhka" class="inp-cbx" data-status = "25" style="display: none">
                    <label for="natyazhka" class="cbx">
                      <span>
                        <svg width="12px" height="10px" viewBox="0 0 12 10">
                          <polyline points="1.5 6 4.5 9 10.5 1"></polyline>
                        </svg>
                      </span>
                      <span>натяжка;</span>
                    </label>
                </p>
                <p>
                    <input type="checkbox" id="vstavka" class="inp-cbx" data-status = "26" style="display: none">
                    <label for="vstavka" class="cbx">
                      <span>
                        <svg width="12px" height="10px" viewBox="0 0 12 10">
                          <polyline points="1.5 6 4.5 9 10.5 1"></polyline>
                        </svg>
                      </span>
                      <span>установка вставки.</span>
                    </label>
                </p>
            </div>
            <p>Введите примечание:</p>
            <p>
                <textarea id="note"></textarea>
            </p>
            <div id="warning">
                <p>Введите примечание</p>
            </div>
            <p><button type="button" id="save" class="btn btn-primary">Ок</button></p>
        </div>
    </div>
</div>

<script>

    var url_proj = '<?php echo $project; ?>';
    var statuses = [];
    // функция получения текущего времени
    var date;
    function CurrentDateTime() {
        var now = new Date();
        var year = String(now.getFullYear());
        var month = String(now.getMonth());
        month++;
        if (month.length == 1) {
            month = "0"+month;
        }
        var day = String(now.getDate());
        if (day.length == 1) {
            day = "0"+day;
        }
        var hour = String(now.getHours());
        if (hour.length == 1) {
            hour = "0"+hour;
        }
        var minute = String(now.getMinutes());
        if (minute.length == 1) {
            minute = "0"+minute;
        }
        var second = String(now.getSeconds());
        if (second.length == 1) {
            second = "0"+second;
        }
        date = year+"-"+month+"-"+day+" "+hour+":"+minute+":"+second;
        return date;
    }

    jQuery(document).ready( function() {

        //отправка ajax для проверки дат начала конца монтажа и статуса
        jQuery.ajax( {
            type: "POST",
            url: "index.php?option=com_gm_ceiling&task=mountersorder.GetDates",
            dataType: 'json',
            data: {
                url_proj : url_proj
            },
            success: function(msg) {
                console.log(msg);
                start = msg[0].project_mounting_start;
                end = msg[0].project_mounting_end;
                status_mount = msg[0].project_status;
                console.log(status_mount);
                if (status_mount == 17 ) {
                    jQuery("#begin").attr("disabled", "disabled");
                    jQuery("#complited").attr("disabled", false);
                    jQuery("#underfulfilled").attr("disabled", false);
                } else if (status_mount == 10 || status_mount == 19) {
                    jQuery("#begin").attr("disabled", false);
                    jQuery("#complited").attr("disabled", "disabled");
                    jQuery("#underfulfilled").attr("disabled", "disabled");
                } else if (status_mount == 16 || status_mount == 24 || status_mount == 25 || status_mount == 26 ) {
                    jQuery("#complited").attr("disabled", false);
                    jQuery("#underfulfilled").attr("disabled", false);
                } 
            },
            error: function(data){
                console.log(data);
            }
        });

        //скрыть модальное окно
        jQuery(document).mouseup(function (e) {
            var div = jQuery("#modal-window-1-tar");
            if (!div.is(e.target)
                && div.has(e.target).length === 0) {
                jQuery("#close-tar").hide();
                jQuery("#modal-window-container-tar").hide();
                jQuery("#modal-window-1-tar").hide();
            }
        });
        jQuery('.inp-cbx').change(function(){
            let status = jQuery(this).data('status');
            console.log(this.checked);
            if(this.checked){
                if(statuses.indexOf(status)== -1 ){
                    statuses.push(status);
                }
            }
            else
            {
                 if(statuses.indexOf(status) != -1 ){
                    statuses.splice(statuses.indexOf(status),1);
                }
            }
            console.log(statuses);
        });
        //  кнопка "монтаж начат"
        jQuery("#begin").click( function() {
            CurrentDateTime();
            jQuery.ajax( {
                type: "POST",
                url: "index.php?option=com_gm_ceiling&task=mountersorder.MountingStart",
                dataType: 'json',
                data: {
                    date : date,
                    url_proj : url_proj,
                },
                success: function(msg) {
                    if (msg[0].project_status == 16) {
                        window.location.href = "/index.php?option=com_gm_ceiling&&view=mounterscalendar"
                    }
                }
            });
        });

        // узнаем какая кнопка, открываем модальное окно
        jQuery("#buttons-cantainer").on("click", ".modal", function() {
            whatBtn = this.id;
            jQuery("#close-tar").show();
            jQuery("#modal-window-container-tar").show();
            jQuery("#modal-window-1-tar").show("slow");
        });

        // получение значений из селектов
        jQuery("#modal-window-container-tar").on("click", "#save", function() {
            var note = jQuery("#note").val();
            let status;
            if (whatBtn == "complited") {
                // кнопка "монтаж выполнен"
                
                    CurrentDateTime();
                    if(statuses.length == 3){
                        status = 11;
                    }
                    else{
                        statuses.sort(function (a, b) {
                                          if (a > b) return 1;
                                          if (a < b) return -1;
                                        });
                        status = statuses[statuses.length-1];
                    }
                    console.log(status);
                    jQuery.ajax( {
                        type: "POST",
                        url: "index.php?option=com_gm_ceiling&task=mountersorder.MountingComplited",
                        dataType: 'json',
                        data: {
                            date : date,
                            url_proj : url_proj,
                            note : note,
                            status: status
                        },
                        success: function(msg) {
                            if (msg[0].project_status == 11) {
                                window.location.href = "/index.php?option=com_gm_ceiling&&view=mounterscalendar"
                            }
                        },
                        error: function(data){
                            console.log(data);
                        }
                    });
            } else if (whatBtn == "underfulfilled") {
                // кнопка "монтаж недовыполнен"
                if (note.length != 0) {
                    CurrentDateTime();
                    jQuery.ajax( {
                        type: "POST",
                        url: "index.php?option=com_gm_ceiling&task=mountersorder.MountingUnderfulfilled",
                        dataType: 'json',
                        data: {
                            date : date,
                            url_proj : url_proj,
                            note : note,
                        },
                        success: function(msg) {
                            if (msg[0].project_status == 17) {
                                window.location.href = "/index.php?option=com_gm_ceiling&&view=mounterscalendar"
                            }
                        }
                    });
                } else {
                    jQuery("#warning").show();
                }
            }
        });

        // проверка, если пустое то убирать подсказку
        jQuery("#modal-window-container-tar").on("keydown", "#note", function() {
            jQuery("#warning").hide();
        });

    });

</script>
