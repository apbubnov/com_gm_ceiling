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

$project = $_GET['project'];

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
        <?php if (!empty($calculation_ids)) { ?>
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
                    <?php if (!empty($DataOfProject)) { ?>
                        <?php foreach ($DataOfProject as $value) { ?>
                            
                        <?php } ?>
                    <?php } ?>
                    <tr id="before-insert" class="caption">
                        <td colspan=3 style="text-align: right;">Итого, ₽:</td>
                        <td id="sum-all"></td>
                    </tr>
                </table>
            </div>
        </div>
        <?php foreach ($calculation_ids as $value) { ?>
            <div id="ceiling<?php echo $value->id; ?>" class="content-tab tab-pane" role="tabpanel">
                <div class="ceiling">
                    <img src="/calculation_images/<?php echo md5("calculation_sketch".$value->id); ?>.png" class="image-ceiling">
                </div>
                <div class = "overflow">
                    <table id="table-order-<?php echo $value->id; ?>" cols=4 class="table-order">
                        <tr class="caption">
                            <td>Наименование</td>
                            <td>Цена, ₽</td>
                            <td>Количество</td>
                            <td>Стоимость, ₽</td>
                        </tr>
                        <?php $DataOfProject = Gm_ceilingHelpersGm_ceiling::calculate_mount(0, 1, $value->id, null); ?>
                        <?php var_dump($DataOfProject["mounting_data"]) ?>
                        <?php if (isset($DataOfProject["mounting_data"]) { ?>
                            <?php $calculate_sum = 0;?>
                            <?php foreach ($DataOfProject["mounting_data"] as $val) { ?>
                                <tr>
                                    <td class="left">
                                        <?php echo $val->title; ?>
                                    </td>
                                    <?php if ($user->dealer_id == 1) { ?>
                                        <td>
                                            <?php echo $val->gm_salary; ?>
                                        </td>
                                    <?php } else { ?>
                                        <td>
                                            <?php echo $val->dealer_salary; ?>
                                        </td>
                                    <?php } ?>
                                    <td>
                                        <?php echo $val->quantity; ?>
                                    </td>
                                    <?php if ($user->dealer_id == 1) { ?>
                                        <td>
                                            <?php echo $val->gm_salary_total; ?>
                                            <?php $calculate_sum += $val->gm_salary_total; ?>
                                        </td>
                                    <?php } else { ?>
                                        <td>
                                            <?php echo $val->dealer_salary_total; ?>
                                            <?php $calculate_sum += $val->gm_salary_total; ?>
                                        </td>
                                    <?php } ?>
                                </tr>
                            <?php } ?> 
                        <?php } ?>
                        <tr class="caption">
                            <td colspan=3 style="text-align: right;">Итого, ₽:</td>
                            <td>
                                <?php echo $calculate_sum; ?>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        <?php } ?>
    </div>
    <div id="buttons-cantainer">
        <button id="begin" class="btn"><i class="fa fa-play fa-tar" aria-hidden="true"></i> Монтаж начат</button>
        <button id="complited" class="btn modal"><i class="fa fa-check" aria-hidden="true"></i> Монтаж выполнен</button>
        <button id="underfulfilled" class="btn modal"><i class="fa fa-pause fa-tar" aria-hidden="true"></i> Монтаж недовыполнен</button>
    </div>
    <div id="modal-window-container-tar">
        <button id="close-tar" type="button"><i class="fa fa-times fa-times-tar" aria-hidden="true"></i></button>
        <div id="modal-window-1-tar">
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

    var url_proj = <?php echo $project; ?>;

    function AddTab(DataOfMount) {
        /*        
        // вкладка общее
        tabAll = '';
        if (n5 !=0) {
            tabAll += '<tr><td>Периметр</td>';
            tabAll += '<td>'+n5price+'</td>';
            tabAll += '<td>'+(+n5).toFixed(2)+'</td>';
            tabAll += '<td>'+(+n5 * +n5price).toFixed(2)+'</td></tr>';
        }
        if (n6 !=0) {
            tabAll += '<tr><td>Вставка</td>';
            tabAll += '<td>'+n6price+'</td>';
            tabAll += '<td>'+(+n6).toFixed(2)+'</td>';
            tabAll += '<td>'+(+n6 * +n6price).toFixed(2)+'</td></tr>';
        }
        if (n7 !=0) {
            tabAll += '<tr><td>Крепление в плитку</td>';
            tabAll += '<td>'+n7price+'</td>';
            tabAll += '<td>'+n7+'</td>';
            tabAll += '<td>'+(+n7 * +n7price).toFixed(2)+'</td></tr>';
        }
        if (n8 !=0) {
            tabAll += '<tr><td>Крепление в керамогранит</td>';
            tabAll += '<td>'+n8price+'</td>';
            tabAll += '<td>'+n8+'</td>';
            tabAll += '<td>'+(+n8 * +n8price).toFixed(2)+'</td></tr>';
        }
        if (n9 !=0) {
            tabAll += '<tr><td>Обработка угла</td>';
            tabAll += '<td>'+n9price+'</td>';
            tabAll += '<td>'+n9+'</td>';
            tabAll += '<td>'+(+n9 * +n9price).toFixed(2)+'</td></tr>';
        }
        if (n11 !=0) {
            tabAll += '<tr><td>Крепление в керамогранит</td>';
            tabAll += '<td>'+n11price+'</td>';
            tabAll += '<td>'+n11+'</td>';
            tabAll += '<td>'+(+n11 * +n11price).toFixed(2)+'</td></tr>';
        }
        if (n12 !=0) {
            tabAll += '<tr><td>Установка люстр</td>';
            tabAll += '<td>'+n12price+'</td>';
            tabAll += '<td>'+n12+'</td>';
            tabAll += '<td>'+(+n12 * +n12price).toFixed(2)+'</td></tr>';
        }
        if (n13 !=0) {
            tabAll += '<tr><td>Установка светильников</td>';
            tabAll += '<td>'+n13price+'</td>';
            tabAll += '<td>'+n13+'</td>';
            tabAll += '<td>'+(+n13 * +n13price).toFixed(2)+'</td></tr>';
        }
        if (n14 !=0) {
            tabAll += '<tr><td>Обвод труб</td>';
            tabAll += '<td>'+n14price+'</td>';
            tabAll += '<td>'+n14+'</td>';
            tabAll += '<td>'+(+n14 * +n14price).toFixed(2)+'</td></tr>';
        }
        if (n17 !=0) {
            tabAll += '<tr><td>Закладная брусом</td>';
            tabAll += '<td>'+n17price+'</td>';
            tabAll += '<td>'+(+n17).toFixed(2)+'</td>';
            tabAll += '<td>'+(+n17 * +n17price).toFixed(2)+'</td></tr>';
        }
        if (n18 !=0) {
            tabAll += '<tr><td>Укрепление стен</td>';
            tabAll += '<td>'+n18price+'</td>';
            tabAll += '<td>'+n18+'</td>';
            tabAll += '<td>'+(+n18 * +n18price).toFixed(2)+'</td></tr>';
        }
        if (n20 !=0) {
            tabAll += '<tr><td>Разделитель стен</td>';
            tabAll += '<td>'+n20price+'</td>';
            tabAll += '<td>'+n20+'</td>';
            tabAll += '<td>'+(+n20 * +n20price).toFixed(2)+'</td></tr>';
        }
        if (n21 !=0) {
            tabAll += '<tr><td>Пожарные сигнализации</td>';
            tabAll += '<td>'+n21price+'</td>';
            tabAll += '<td>'+n21+'</td>';
            tabAll += '<td>'+(+n21 * +n21price).toFixed(2)+'</td></tr>';
        }
        if (n22_56 !=0) {
            tabAll += '<tr><td>Вентиляции</td>';
            tabAll += '<td>'+n22_56price+'</td>';
            tabAll += '<td>'+n22_56+'</td>';
            tabAll += '<td>'+(+n22_56 * +n22_56price).toFixed(2)+'</td></tr>';
        }
        if (n22_78 !=0) {
            tabAll += '<tr><td>Электровытяжки</td>';
            tabAll += '<td>'+n22_78price+'</td>';
            tabAll += '<td>'+n22_78+'</td>';
            tabAll += '<td>'+(+n22_78 * +n22_78price).toFixed(2)+'</td></tr>';
        }
        if (n23 !=0) {
            tabAll += '<tr><td>Установка диффузоров</td>';
            tabAll += '<td>'+n23price+'</td>';
            tabAll += '<td>'+n23+'</td>';
            tabAll += '<td>'+(+n23 * +n23price).toFixed(2)+'</td></tr>';
        }
        if (n24 !=0) {
            tabAll += '<tr><td>Сложность доступа</td>';
            tabAll += '<td>'+n24price+'</td>';
            tabAll += '<td>'+n24+'</td>';
            tabAll += '<td>'+(+n24 * +n24price).toFixed(2)+'</td></tr>';
        }
        if (n27 !=0) {
            tabAll += '<tr><td>Шторных карнизов</td>';
            tabAll += '<td>'+n27price+'</td>';
            tabAll += '<td>'+(+n27).toFixed(2)+'</td>';
            tabAll += '<td>'+(+n27 * +n27price).toFixed(2)+'</td></tr>';
        }
        if (n29_23 !=0) {
            tabAll += '<tr><td>Переход уровня по прямой</td>';
            tabAll += '<td>'+n29price_23+'</td>';
            tabAll += '<td>'+(+n29_23)+'</td>';
            tabAll += '<td>'+(+n29_23 * +n29price_23).toFixed(2)+'</td></tr>';
        }
        if (n29_24 !=0) {
            tabAll += '<tr><td>Переход уровня по кривой</td>';
            tabAll += '<td>'+n29price_24+'</td>';
            tabAll += '<td>'+(+n29_24)+'</td>';
            tabAll += '<td>'+(+n29_24 * +n29price_24).toFixed(2)+'</td></tr>';
        }
        if (n29_25 !=0) {
            tabAll += '<tr><td>Переход уровня по прямой с нишей</td>';
            tabAll += '<td>'+n29price_25+'</td>';
            tabAll += '<td>'+(+n29_25)+'</td>';
            tabAll += '<td>'+(+n29_25 * +n29price_25).toFixed(2)+'</td></tr>';
        }
        if (n29_26 !=0) {
            tabAll += '<tr><td>Переход уровня по кривой с нишей</td>';
            tabAll += '<td>'+n29price_26+'</td>';
            tabAll += '<td>'+(+n29_26)+'</td>';
            tabAll += '<td>'+(+n29_26 * +n29price_26).toFixed(2)+'</td></tr>';
        }
        if (n30 !=0) {
            tabAll += '<tr><td>Парящий потолок</td>';
            tabAll += '<td>'+n30price+'</td>';
            tabAll += '<td>'+(+n30).toFixed(2)+'</td>';
            tabAll += '<td>'+(+n30 * +n30price).toFixed(2)+'</td></tr>';
        }
        if (dop_krepezh !=0) {
            tabAll += '<tr><td>Дополнительный крепеж</td>';
            tabAll += '<td>'+dop_krepezhprice+'</td>';
            tabAll += '<td>'+dop_krepezh+'</td>';
            tabAll += '<td>'+(+dop_krepezh * +dop_krepezhprice).toFixed(2)+'</td></tr>';
        }
        if (mas_extra_mounting != undefined) {
            for (var i=0; i<mas_extra_mounting.length; i++) {
                tabAll += '<tr><td>'+mas_extra_mounting[i].title+'</td>';
                tabAll += '<td>'+mas_extra_mounting[i].value+'</td>';
                tabAll += '<td>1</td>';
                tabAll += '<td>'+mas_extra_mounting[i].value+'</td></tr>';       
            }
        }
        if (arr["transport"] !=0) {
            tabAll += '<tr id="caption" class="caption"><td colspan="4" style="text-align: center;">Транспорт</td></tr>';
            tabAll += '<tr><td></td>';
            tabAll += '<td>'+arr["transport_price"]+'</td>';
            tabAll += '<td>'+arr["transport_count"]+'</td>';
            tabAll += '<td>'+arr["transport_sum"]+'</td></tr>';
            sumAll += arr["transport_sum"];
        }
        jQuery("#before-insert").before(tabAll);
        if (sumAll < 1500) {
            jQuery("#sum-all").text("1500");
        } else {
            jQuery("#sum-all").text(sumAll);
        } */
    }


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
            url_proj : url_proj,
        },
        success: function(msg) {
            start = msg[0].project_mounting_start;
            end = msg[0].project_mounting_end;
            status_mount = msg[0].project_status;
            if (status_mount == 17 ) {
                jQuery("#begin").attr("disabled", "disabled");
                jQuery("#complited").attr("disabled", false);
                jQuery("#underfulfilled").attr("disabled", false);
            } else if (status_mount == 10) {
                jQuery("#begin").attr("disabled", false);
                jQuery("#complited").attr("disabled", "disabled");
                jQuery("#underfulfilled").attr("disabled", "disabled");
            } else if (status_mount == 16) {
                jQuery("#begin").attr("disabled", "disabled");
                jQuery("#complited").attr("disabled", false);
                jQuery("#underfulfilled").attr("disabled", false);
            } else {
                jQuery("#begin").attr("disabled", "disabled");
                jQuery("#complited").attr("disabled", "disabled");
                jQuery("#underfulfilled").attr("disabled", "disabled");
            }
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
        if (whatBtn == "complited") {
            // кнопка "монтаж выполнен"
            if (note.length != 0) {
                CurrentDateTime();
                jQuery.ajax( {
                    type: "POST",
                    url: "index.php?option=com_gm_ceiling&task=mountersorder.MountingComplited",
                    dataType: 'json',
                    data: {
                        date : date,
                        url_proj : url_proj,
                        note : note,
                    },
                    success: function(msg) {
                        if (msg[0].project_status == 11) {
                            window.location.href = "/index.php?option=com_gm_ceiling&&view=mounterscalendar"
                        }
                    }
                });
            } else {
                 jQuery("#warning").show();
            }
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
