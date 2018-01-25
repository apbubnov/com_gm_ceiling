<?php
/**
 * @version    CVS: 1.0.0
 * @package    Com_Gm_ceiling
 * @author     Mikhail "Spectral Eye" Vinyukov <vms@itctl.ru>
 * @copyright  2016 Mikhail "Spectral Eye" Vinyukov
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
$doc = JFactory::getDocument();
$doc->addScript(JUri::base() . '/media/com_gm_ceiling/js/form.js');

$user = JFactory::getUser();
$userId = $user->get('id');

// календарь
$month = date("n");
$year = date("Y");
$FlagCalendar = [3, $user->dealer_id];
$calendar = Gm_ceilingHelpersGm_ceiling::DrawCalendarTar($userId, $month, $year, $FlagCalendar);
//----------------------------------------------------------------------------------

// все замерщики
$model = Gm_ceilingHelpersGm_ceiling::getModel('reservecalculation');
$AllGauger = $model->FindAllGauger($user->dealer_id);
if (count($AllGauger) == 0) {
    array_push($AllGauger, ["id" => $userId, "name" => $user->name]);
}
//----------------------------------------------------------------------------------


?>
<?= parent::getButtonBack(); ?>
<script src="https://api-maps.yandex.ru/2.1/?lang=ru_RU" type="text/javascript"></script>
<link rel="stylesheet" href="/components/com_gm_ceiling/views/reservecalculation/tmpl/css/style.css" type="text/css" />

<style>
    .center input {
        min-width: 280px;
    }
    #find_client_btn {
        min-width: 280px;
    }
</style>
<form id="calculate_form" onsubmit="check(this); return false;" action="/index.php?option=com_gm_ceiling&task=reservecalculation.save&type=<?php echo $type; ?>" method="post" class="form-validate form-horizontal" enctype="multipart/form-data">
    <h2 class="center">Запись на замер</h2>
    <div class="center">
        <p>
            <button id="new_cleint_btn" class="btn btn-primary" type="button" style=" min-width: 280px;">Создать нового клиента</button>
        </p>
        <p>
            <button id="choose_cleint_btn" class="btn btn-primary" type="button" style=" min-width: 280px;">Выбрать существующего клиента</button>
        </p>
    </div>
    <div id="new_client" style="display: none" class="center">
        <!-- Регистрация клиента -->
        <div class="control-group">
            <div class="control-label">
                <label id="jform_client_name-lbl" for="jform_client_name" class="required">ФИО клиента<span class="star">&nbsp;*</span></label>
            </div>
            <div class="controls">
                <input name="jform[client_name]" id="jform_client_name" value="" class="required" placeholder="ФИО клиента" aria-required="true" type="text">
            </div>
        </div>
        <div class="control-group">
            <div class="control-label">
                <label id="jform_client_contacts-lbl" for="jform_client_contacts" class="required">Телефон клиента<span class="star">&nbsp;*</span></label>
            </div>
            <div class="controls">
                <input name="jform[client_contacts]" id="jform_client_contacts" value="" class="required" placeholder="Телефоны клиента" aria-required="true" type="text">
            </div>
        </div>
    </div>
    <!-- Создание проекта -->
    <div id=choose_fields style="display: none" class="center">
        <div class="control-label">
            <label id="jform_client_fio-lbl-find" for="jform_client_fio-find">ФИО клиента<span class="star">&nbsp;*</span></label>
        </div>
        <div class="controls">
            <input name="jform[client_fio-find]" id="jform_client_fio-find" value="" placeholder="ФИО клиента" aria-required="true" type="text">
        </div>
        <div class="controls">
            <button id="find_client_btn" class="btn btn-primary" type="button" style="margin-top: 10px;">Найти</button>
        </div>
        <div class="controls" id="select_clients" style="display:none">
            <select style="width: 168px" name="jform[clients]" size="1" id="clients"></select>
        </div>
    </div>
    <div class="center">
        <div class="control-label">
            <label id="jform_project_info-lbl" for="jform_project_info" style="display: none" class="required">Адрес замера <span class="star">&nbsp;*</span></label>
        </div>
        <div class="controls">
            <input name="jform[project_info]" id="jform_project_info" value="" style="display: none" class="required" placeholder="Адрес замера" aria-required="true" type="text">
        </div>
        <div style="width:100%; margin-top: 10px; ">
            <div  class="center" style="display: inline-block;width:280px;">
                <div class="controls">
                    <input name="jform[project_info_house]" id="jform_project_info_house" value="" class="required" style="width: 133px;min-width: 10px;float: left;margin: 0px 5px 0px 0px;display: none;" placeholder="Дом" required="required" aria-required="true" type="text">
                </div>
                <div class="controls">
                    <input name="jform[project_info_bdq]" id="jform_project_info_bdq" value=""  style="width: 140px; min-width: 50px; display: none;" placeholder="Корпус" aria-required="true" type="text">
                </div>
            </div>
        </div>
        <div style="width:100%; margin-top: 10px;">
            <div  class="center" style="display: inline-block; width:280px;">
                <div class="controls">
                    <input name="jform[project_apartment]" id="jform_project_apartment" value=""  style="width: 133px;min-width: 10px;float: left;margin: 0px 5px 0px 0px;display: none;" placeholder="Квартира"  aria-required="true" type="text">
                </div>
                <div class="controls">
                    <input name="jform[project_info_porch]" id="jform_project_info_porch" value=""  style="width: 140px; min-width: 50px; display: none;" placeholder="Подъезд"  aria-required="true" type="text">
                </div>
            </div>
        </div>
        <div style="width:100%; margin-top: 10px;">
            <div  class="center" style="display: inline-block; width:280px;">
                <div class="controls">
                    <input name="jform[project_info_floor]" id="jform_project_info_floor" value=""  style="width: 133px;min-width: 10px;float: left;margin: 0px 5px 0px 0px;display: none;" placeholder="Этаж" aria-required="true" type="text">
                </div>
                <div class="controls">
                    <input name="jform[project_info_code]" id="jform_project_info_code" value=""  style="width: 140px; min-width: 50px; display: none;" placeholder="Код" aria-required="true" type="text">
                </div>
            </div> 
        </div>
    </div>
    <!-- Для начальника монтажной службы -->
    <div class="center">
        <div class="control-label">
            <label id="jform_project_note-lbl" style="display: none" for="jform_project_note" class="required">Примечание</label>
        </div>
        <div class="controls">
            <input name="jform[project_note]" id="jform_project_note" style="display: none" value="" placeholder="Примечание" type="text">
        </div>
    </div>
    <div class="center" style="text-align: center;">
        <h4><p>Назначить дату и время замера</p></h4>
        <input name="jform[project_calculation_date]" id="jform_project_calculation_date" type="hidden" value="">
        <input name="jform[project_calculation_daypart]" id="jform_project_calculation_daypart" type="hidden" value="">
        <input name="jform[project_calculator]" id="jform_project_calculator" type="hidden" value="">
        <div id="calendar" style="position: relative; max-width: 280px; margin-left: calc(50% - 140px)">
            <div id="calendar-container">
                <div class="btn-small-l">
                    <button id="button-prev" class="button-prev-small" type="button" class="btn btn-primary"><i class="fa fa-arrow-left" aria-hidden="true"></i></button>
                </div>
                <?php echo $calendar; ?>
                <div class="btn-small-r">
                    <button id="button-next" class="button-next-small" type="button" class="btn btn-primary"><i class="fa fa-arrow-right" aria-hidden="true"></i></button>
                </div>
            </div>
        </div>
    </div>
    <input name="jform[client_id]" id="client_id" type="hidden" value="0">
    <br>
    <div class="center">
        <button id="calculate_button" class="btn btn-primary" type="submit" style=" min-width: 280px;">Записать</button>
    </div>
    <div id="modal-window-container-tar">
        <button id="close-tar" type="button"><i class="fa fa-times fa-times-tar" aria-hidden="true"></i></button>
        <div id="modal-window-choose-tar">
            <p id="date-modal"></p>
            <p><strong>Выберите время замера (и замерщика):</strong></p>
            <p>
                <table id="projects_gaugers"></table>
            </p>
            <p><button type="button" id="save-choise-tar" class="btn btn-primary">Ок</button></p>
        </div>
    </div>
</form>
<script>

    var zamerArray = {};

    function check(e) {
        if (jQuery("#jform_client_name").val() == "" && jQuery("#jform_client_fio-find").val() == "") {
            var n = noty({
                theme: 'relax',
                layout: 'center',
                maxVisible: 5,
                type: "error",
                text: "Введите все данные"
            });
        }
        else e.submit();
    }

    // листание календаря
    month_old = 0;
    year_old = 0;
    jQuery("#calendar-container").on("click", "#button-next", function () {
        month = <?php echo $month; ?>;
        year = <?php echo $year; ?>;
        if (month_old != 0) {
            month = month_old;
            year = year_old;
        }
        if (month == 12) {
            month = 1;
            year++;
        } else {
            month++;
        }
        month_old = month;
        year_old = year;
        update_calendar(month, year);
    });
    jQuery("#calendar-container").on("click", "#button-prev", function () {
        month = <?php echo $month; ?>;
        year = <?php echo $year; ?>;
        if (month_old != 0) {
            month = month_old;
            year = year_old;
        }
        if (month == 1) {
            month = 12;
            year--;
        } else {
            month--;
        }
        month_old = month;
        year_old = year;
        update_calendar(month, year);
    });
    function update_calendar(month, year) {
        jQuery.ajax({
            type: 'POST',
            url: "index.php?option=com_gm_ceiling&task=UpdateCalendarTar",
            data: {
                id: <?php echo $userId; ?>,
                id_dealer: <?php echo $user->dealer_id; ?>,
                flag: 3,
                month: month,
                year: year,
            },
            success: function (msg) {
                jQuery("#calendar-container").empty();
                msg += '<div class="btn-small-l"><button id="button-prev" class="button-prev-small" type="button" class="btn btn-primary"><i class="fa fa-arrow-left" aria-hidden="true"></i></button></div><div class="btn-small-r"><button id="button-next" class="button-next-small" type="button" class="btn btn-primary"><i class="fa fa-arrow-right" aria-hidden="true"></i></button></div>';
                jQuery("#calendar-container").append(msg);
                Today(day, NowMonth, NowYear);
            },
            dataType: "text",
            timeout: 10000,
            error: function () {
                var n = noty({
                    theme: 'relax',
                    layout: 'center',
                    maxVisible: 5,
                    type: "error",
                    text: "Ошибка при попытке обновить календарь. Сервер не отвечает"
                });
            }
        });
    }
    //-----------------------------------------------------------------

    //скрыть модальное окно
    jQuery(document).mouseup(function (e) {
		var div = jQuery("#modal-window-choose-tar");
		if (!div.is(e.target)
		    && div.has(e.target).length === 0) {
			jQuery("#close-tar").hide();
			jQuery("#modal-window-container-tar").hide();
			jQuery("#modal-window-choose-tar").hide();
		}
    });
    //-------------------------------------------------------------------

    // функция подсвета сегоднешней даты
    var Today = function (day, month, year) {
        month++;
        jQuery("#current-monthD"+day+"DM"+month+"MY"+year+"YI"+<?php echo $userId; ?>+"I").addClass("today");
    }   
    //------------------------------------------

    // функция чтобы другая функция выполнилась позже чем document ready
    Function.prototype.process= function(state){
        var process= function(){
            var args= arguments;
            var self= arguments.callee;
            setTimeout(function(){
                self.handler.apply(self, args);
            }, 0 )
        }
        for(var i in state) process[i]= state[i];
        process.handler= this;
        return process;
    }
    //------------------------------------------

    jQuery(document).ready(function () {

        // открытие модального окна с календаря и получение даты и вывода свободных монтажников
        jQuery("#calendar-container").on("click", ".current-month, .not-full-day, .change", function() {
            window.idDay = jQuery(this).attr("id");
            reg1 = "D(.*)D";
            reg2 = "M(.*)M";
            reg3 = "Y(.*)Y";
            if (idDay.match(reg1)[1].length == 1) {
                d = "0"+idDay.match(reg1)[1];
            } else {
                d = idDay.match(reg1)[1];
            }
            if (idDay.match(reg2)[1].length == 1) {
                m = "0"+idDay.match(reg2)[1];
            } else {
                m = idDay.match(reg2)[1];
            }
            window.date = idDay.match(reg3)[1]+"-"+m+"-"+d;
            jQuery("#modal-window-container-tar").show();
			jQuery("#modal-window-choose-tar").show("slow");
            jQuery("#close-tar").show();
            if (jQuery("#jform_who_calculate1").attr("checked") == "checked") {
				var dealer = 1;
			} else {
				var dealer = <?php echo $user->dealer_id; ?>;
			}
            jQuery.ajax({
                type: 'POST',
                url: "/index.php?option=com_gm_ceiling&task=calculations.GetBusyGauger",
                data: {
                    date: date,
                    dealer: dealer,
                },
                success: function(data) {
                    Array.prototype.diff = function(a) {
                        return this.filter(function(i) {return a.indexOf(i) < 0;});
                    };
                    AllGauger = <?php echo json_encode($AllGauger); ?>;
                    data = JSON.parse(data); // замеры
                    AllTime = ["09:00:00", "10:00:00", "11:00:00", "12:00:00", "13:00:00", '14:00:00', "15:00:00", "16:00:00", "17:00:00", "18:00:00", "19:00:00", "20:00:00"];
                    var TableForSelect = '<tr><th class="caption"></th><th class="caption">Время</th><th class="caption">Адрес</th><th class="caption">Замерщик</th></tr>';
                    AllTime.forEach( elementTime => {
                        var t = elementTime.substr(0, 2);
                        t++;
                        Array.from(AllGauger).forEach(function(elementGauger) {
                            TableForSelect += '<tr><td><input type="radio" name="choose_time_gauger" value="'+elementTime+'"></td>';
                            TableForSelect += '<td>'+elementTime.substr(0, 5)+'-'+t+':00</td>';
                            var emptytd = 0;
                            Array.from(data).forEach(function(elementProject) {
                                /* if (elementProject.project_calculator == elementGauger.id && elementProject.project_calculation_date.substr(11) == elementTime) {
                                    TableForSelect += '<td>'+elementProject.project_info+'</td>';
                                    emptytd = 1;
                                } */
                                if (elementProject.project_calculator == elementGauger.id && elementProject.project_calculation_date.substr(11) == elementTime) {
                                    var timesession = jQuery("#jform_new_project_calculation_daypart").val();
                                    var gaugersession = jQuery("#jform_project_gauger").val();
                                    if (elementProject.project_calculator == gaugersession && elementProject.project_calculation_date.substr(11) == timesession) {
                                        TableForSelect += '<tr><td><input type="radio" name="choose_time_gauger" value="'+elementTime+'"></td>';
                                    } else {
                                        TableForSelect += '<tr><td></td>';
                                    }
                                    TableForSelect += '<td>'+elementTime.substr(0, 5)+'-'+t+':00</td>';
                                    TableForSelect += '<td>'+elementProject.project_info+'</td>';
                                    emptytd = 1;
                                }
                            });
                            if (emptytd == 0) {
                                TableForSelect += '<td></td>';
                            }
                            TableForSelect += '<td>'+elementGauger.name+'<input type="hidden" name="gauger" value="'+elementGauger.id+'"></td></tr>';
                        });
                    });
                    jQuery("#projects_gaugers").empty();
                    jQuery("#projects_gaugers").append(TableForSelect);
                    jQuery("#date-modal").html("<strong>Выбранный день: "+d+"."+m+"."+idDay.match(reg3)[1]+"</strong>");
                }
            });
        });
        //--------------------------------------------------------------------------------------------------

        // получение значений из селектов
		jQuery("#projects_gaugers").on("change", "input:radio[name='choose_time_gauger']", function() {
            var times = jQuery("input[name='choose_time_gauger']");
            var time = "";
            gauger = "";
            times.each(function(element) {
                if (jQuery(this).prop("checked") == true) {
                    time = jQuery(this).val();
                    gauger = jQuery(this).closest('tr').find("input[name='gauger']").val();
                }
            });
            jQuery("#jform_project_calculation_daypart").val(time);
            jQuery("#jform_project_calculation_date").val(date);
            jQuery("#jform_project_calculator").val(gauger);
            if (jQuery(".change").length == 0) {
                jQuery("#"+idDay).addClass("change");
            } else {
                jQuery(".change").removeClass("change");
                jQuery("#"+idDay).addClass("change");
            }
            jQuery("#close-tar").hide();
            jQuery("#modal-window-container-tar").hide();
            jQuery("#modal-window-choose-tar").hide();
        });
        //------------------------------------------

        // подсвет сегоднешней даты
        window.today = new Date();
        window.NowYear = today.getFullYear();
        window.NowMonth = today.getMonth();
        window.day = today.getDate();
        Today(day, NowMonth, NowYear);
        //------------------------------------------

        jQuery("#jform_client_contacts").mask("+7 (999) 999-99-99");

        jQuery("#new_cleint_btn").click(function () {
            if (jQuery('#new_client').css('display') == 'none') {
                jQuery('#new_client').show();
                jQuery('#jform_project_info-lbl').show();
                jQuery('#jform_project_info').show();
                jQuery('#jform_project_info_house').show();
                jQuery('#jform_project_info_bdq').show();
                jQuery('#jform_project_apartment').show();
                jQuery('#jform_project_info_porch').show();
                jQuery('#jform_project_info_floor').show();
                jQuery('#jform_project_info_code').show();
                jQuery('#jform_project_note-lbl').show();
                jQuery('#jform_project_note').show();
            }
            else {
                jQuery('#new_client').hide();
                jQuery('#jform_project_info-lbl').hide();
                jQuery('#jform_project_info').hide();
                jQuery('#jform_project_info_house').hide();
                jQuery('#jform_project_info_bdq').hide();
                jQuery('#jform_project_apartment').hide();
                jQuery('#jform_project_info_porch').hide();
                jQuery('#jform_project_info_floor').hide();
                jQuery('#jform_project_info_code').hide();
                jQuery('#jform_project_note-lbl').hide();
                jQuery('#jform_project_note').hide();
            }
            if (jQuery("#choose_fields").css('display') != 'none')
                jQuery("#choose_fields").hide();
        });
        jQuery("#choose_cleint_btn").click(function () {
            if (jQuery('#choose_fields').css('display') == 'none') {
                jQuery('#choose_fields').show();
                jQuery('#jform_project_info-lbl').show();
                jQuery('#jform_project_info').show();
                jQuery('#jform_project_info_house').show();
                jQuery('#jform_project_info_bdq').show();
                jQuery('#jform_project_apartment').show();
                jQuery('#jform_project_info_porch').show();
                jQuery('#jform_project_info_floor').show();
                jQuery('#jform_project_info_code').show();
                jQuery('#jform_project_note-lbl').show();
                jQuery('#jform_project_note').show();
            }
            else {
                jQuery('#choose_fields').hide();
                jQuery('#jform_project_info-lbl').hide();
                jQuery('#jform_project_info').hide();
                jQuery('#jform_project_info_house').hide();
                jQuery('#jform_project_info_bdq').hide();
                jQuery('#jform_project_apartment').hide();
                jQuery('#jform_project_info_porch').hide();
                jQuery('#jform_project_info_floor').hide();
                jQuery('#jform_project_info_code').hide();
                jQuery('#jform_project_note-lbl').hide();
                jQuery('#jform_project_note').hide();
            }
            if (jQuery('#new_client').css('display') != 'none')
                jQuery('#new_client').hide();
        });

        jQuery('#find_client_btn').click(function () {
            var fio = jQuery("#jform_client_fio-find").val();
            jQuery.ajax({
                url: "index.php?option=com_gm_ceiling&task=findOldClients",
                data: {
                    fio: fio
                },
                dataType: "json",
                async: true,
                success: function (data) {
                    jQuery('#clients').find('option').remove();
                    for (var i = 0; i < data.length; i++) {
                        jQuery('<option>').val(data[i].id).text(data[i].client_name).appendTo('#clients');
                    }
                    jQuery('#client_id').val(jQuery('#clients option:selected').val());
                },
                error: function (data) {
                    var n = noty({
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "error",
                        text: "Ошибка"
                    });
                }
            });
            jQuery('#select_clients').show();
        });

    });
        ymaps.ready(init);

    function init() {
		var provider
        // Подключаем поисковые подсказки к полю ввода.
        var suggestView = new ymaps.SuggestView('jform_project_info');
		input = jQuery('#jform_project_info');

		suggestView.events.add('select', function (e) {
		var s = e.get('item').value.replace('Россия, ','');
		input.val(s);
		});

    }
</script>