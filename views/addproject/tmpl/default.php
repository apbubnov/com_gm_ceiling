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

$user       = JFactory::getUser();
$userId     = $user->get('id');

// календарь
$month = date("n");
$year = date("Y");
if ($user->dealer_id == 1 && in_array("14", $user->groups)) {
	$FlagCalendar = [3, $userId];
} else {
	$FlagCalendar = [3, $user->dealer_id];
}
$calendar = Gm_ceilingHelpersGm_ceiling::DrawCalendarTar($userId, $month, $year, $FlagCalendar);
//----------------------------------------------------------------------------------

// все замерщики
$model = Gm_ceilingHelpersGm_ceiling::getModel('reservecalculation');
if ($user->dealer_id == 1 && in_array("14", $user->groups)) {
	$AllGauger = $model->FindAllGauger($userId);
} else {
	$AllGauger = $model->FindAllGauger($user->dealer_id);
}
if (count($AllGauger) == 0) {
    array_push($AllGauger, ["id" => $userId, "name" => $user->name]);
}
//----------------------------------------------------------------------------------


?>

<script src="https://api-maps.yandex.ru/2.1/?lang=ru_RU" type="text/javascript"></script>
<link rel="stylesheet" href="/components/com_gm_ceiling/views/addproject/tmpl/css/style.css" type="text/css" />

<?=parent::getButtonBack();?>
<form id="calculate_form" action="/index.php?option=com_gm_ceiling&task=addproject.save" method="post" class="form-validate form-horizontal" enctype="multipart/form-data">
	<!-- Скрытые поля -->
	<input name="jform[project_calculation_date]" id="jform_project_calculation_date" value="" type="hidden">
	<input name="jform[project_calculation_daypart]" id="jform_project_calculation_daypart" value="" type="hidden">
	<input name="jform[project_calculator]" id="jform_project_calculator" type="hidden" value="">
	<!-- - - - - - - - - - - - - - - - - - - - - - -->
	<h2 class ="center" style="margin-bottom: 15px;"> Добавить замер </h2>

	<div class="col-md-4"></div>
	<div class="col-md-4">
		<p>
			<button id="choose_cleint_btn" class="btn btn-primary" type="button" style="width:100%;">Выбрать существующего клиента</button>
		</p>
		<div id=choose_fields style="display: none">
		<div class="control-label">
			<label id="jform_client_contacts-lbl-find" for="jform_client_fio-find" style="width:100%;">ФИО клиента<span class="star">&nbsp;*</span></label>
		</div>
		<div class="controls">
			<input name="jform[client_fio-find]" id="jform_client_fio-find" value="" style="width:100%;" placeholder="ФИО клиента"  aria-required="true" type="text">
		</div>
		<div class="controls">
			<button id="find_client_btn" class="btn btn-primary" style="width:100%; margin-top: 10px;" type="button">Найти</button>
		</div>
		<div class="controls" id="select_clients" style="display:none">
			<select  style="width:100%; margin-top: 5px;"  name="jform[clients]"  id="clients"></select>
		</div>
	</div>
	<!-- Регистрация клиента -->
	<div class="control-group" id="register_client">
		<div class="control-label">
			<label id="jform_client_name-lbl" for="jform_client_name" class="required">ФИО клиента<span class="star">&nbsp;*</span></label>
		</div>
		<div class="controls">
			<input name="jform[client_name]" id="jform_client_name" value="" class="required" style="width:100%; margin-bottom:1em;" placeholder="ФИО клиента" aria-required="true" type="text">
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<label id="jform_client_contacts-lbl" for="jform_client_contacts" class="required">Телефоны клиента<span class="star">&nbsp;*</span></label>
		</div>
		<div class="controls">
			<input name="jform[client_contacts]" id="jform_client_contacts" value="" class="required" style="width:100%; margin-bottom:1em;" placeholder="Телефоны клиента" required="required" type="text">
		</div>
	</div>
	<input name="jform[client_id]" id="client_id" type="hidden" value="0">
	<!-- Создание проекта -->
	<div class="control-group">
		<div class="control-label">
			<label id="jform_project_info-lbl" for="jform_project_info" class="required">Адрес клиента<span class="star">&nbsp;*</span></label>
		</div>
		<div class="controls">
			<input name="jform[project_info]" id="jform_project_info" value="" class="required" style="width:100%; margin-bottom:1em; float: left;" placeholder="Улица" required="required" aria-required="true" type="text">
		</div>
		<div class="controls">
			<input name="jform[project_info_house]" id="jform_project_info_house" value="" class="required" style="width: 50%; margin-bottom: 1em; float: left; margin: 0 5px 0 0;" placeholder="Дом" required="required" aria-required="true" type="text">
		</div>
		<div class="controls">
			<input name="jform[project_info_bdq]" id="jform_project_info_bdq" value=""  style="width: calc(50% - 5px); margin-bottom: 1em;" placeholder="Корпус" aria-required="true" type="text">
		</div>
		<div class="controls">
			<input name="jform[project_apartment]" id="jform_project_apartment" value=""  style="width:50%;margin-bottom:1em;margin-right: 5px;float: left;" placeholder="Квартира"  aria-required="true" type="text">
		</div>
		<div class="controls">
			<input name="jform[project_info_porch]" id="jform_project_info_porch" value=""  style="width: calc(50% - 5px); margin-bottom: 1em;" placeholder="Подъезд"  aria-required="true" type="text">
		</div>
		<div class="controls">
			<input name="jform[project_info_floor]" id="jform_project_info_floor" value=""  style="width:50%; margin-bottom:1em;  margin: 0 5px  0 0; float: left;" placeholder="Этаж" aria-required="true" type="text">
		</div>
		<div class="controls">
			<input name="jform[project_info_code]" id="jform_project_info_code" value=""  style="width: calc(50% - 5px); margin-bottom: 1em;" placeholder="Код" aria-required="true" type="text">
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<label id="jform_project_note-lbl" for="jform_project_note" class="required">Примечание</label>
		</div>
		<div class="controls">
			<input name="jform[project_note]" id="jform_project_note" value="" style="width:100%; margin-bottom:1em;" placeholder="Примечание" type="text">
		</div>
	</div>
	<div class="control-group" style="margin-bottom: 1em;">
		<p>Выберите удобные дату и время замера</p>
		<div id="calendar-container" style="position: relative;">
			<div class="btn-small-l">
				<button id="button-prev" class="button-prev-small" type="button" class="btn btn-primary"><i class="fa fa-arrow-left" aria-hidden="true"></i></button>
			</div>
			<?php echo $calendar; ?>
			<div class="btn-small-r">
				<button id="button-next" class="button-next-small" type="button" class="btn btn-primary"><i class="fa fa-arrow-right" aria-hidden="true"></i></button>
			</div>
		</div>
	</div>
	<div>
		<button id="calculate_button" class="btn btn-primary" style="width:100%;margin-bottom: 10px;" type="button">Записать</button>
	</div>
	<div id="modal-window-container-tar">
		<button id="close-tar" type="button"><i class="fa fa-times fa-times-tar" aria-hidden="true"></i></button>
		<div id="modal-window-choose-tar">
				<p id="date-modal"></p>
				<p><strong>Выберите время замера (и замерщика):</strong></p>
				<p>
					<table id="projects_gaugers"></table>
				</p>
		</div>
	</div>
</form>

<script>
    var $ = jQuery;
    $(window).resize(function(){
        if (screen.width <= '1024') {
            jQuery('#calculate_form').css('font-size', '13px');
            jQuery('#choose_cleint_btn').css('font-size', '13px');
            jQuery('#find_client_btn').css('font-size', '13px');
            jQuery('#choose_cleint_btn').css('padding', '10px 5px');
        }
        else {
        }
    });

    // вызовем событие resize
    $(window).resize();
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
                id_dealer: <?php if ($user->dealer_id == 1 && in_array("14", $user->groups)) { echo $userId; } else { echo $user->dealer_id; } ?>,
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
        jQuery("#current-monthD"+day+"DM"+month+"MY"+year+"YI"+<?php echo $userId; ?>+"IC0C").addClass("today");
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

	jQuery(document).ready(function() {

		window.time = undefined;
        window.gauger = undefined;
		
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
            jQuery.ajax({
                type: 'POST',
                url: "/index.php?option=com_gm_ceiling&task=calculations.GetBusyGauger",
                data: {
                    date: date,
					dealer: <?php if ($user->dealer_id == 1 && in_array("14", $user->groups)) { echo $userId; } else { echo $user->dealer_id; } ?>,
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
                            var emptytd = 0;
                            Array.from(data).forEach(function(elementProject) {
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
								TableForSelect += '<tr><td><input type="radio" name="choose_time_gauger" value="'+elementTime+'"></td>';
                                TableForSelect += '<td>'+elementTime.substr(0, 5)+'-'+t+':00</td>';
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
			//если было выбрано время, то выдать его
            if (time != undefined) {
                setTimeout(function() { 
                    var times = jQuery("input[name='choose_time_gauger']");
                    times.each(function(element) {
                        if (time == jQuery(this).val() && gauger == jQuery(this).closest('tr').find("input[name='gauger']").val()) {
                            jQuery(this).prop("checked", true);
                        }
                    });
                }, 200);
            }
        });
        //--------------------------------------------------------------------------------------------------

        // получение значений из селектов
		jQuery("#projects_gaugers").on("change", "input:radio[name='choose_time_gauger']", function() {
			var times = jQuery("input[name='choose_time_gauger']");
            time = "";
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
                jQuery("#"+idDay).attr("class", "change");
            } else {
                jQuery(".change").attr("class", "current-month");
                jQuery("#"+idDay).attr("class", "change");
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

		jQuery("#calculate_button").click(function(){
			if(jQuery("#jform_project_calculation_date").val()!=""
                && jQuery("#jform_project_calculation_daypart").val()!=""
                && jQuery("#jform_project_calculator").val()!=""
                && jQuery("#jform_project_info").val()!=""
                && jQuery("#jform_project_info_house").val()!=""
                && (jQuery("#client_id").val()!="0" || jQuery("#jform_client_name").val()!=""))
            {
				jQuery("#calculate_form").submit();
			}
			else{
				var n = noty({
					theme: 'relax',
					layout: 'center',
					maxVisible: 5,
					type: "error",
					text: "Не выбрана дата замера и замерщик!"
				});
			}
		});

		jQuery("#jform_client_contacts").mask("+7 (999) 999-99-99");
		
		jQuery('#clients').change(function(){
			jQuery('#client_id').val(jQuery('#clients option:selected').val());
		});
		
		jQuery('#jform_client_fio-find').change(function(){
			jQuery('#jform_client_contacts').val(jQuery('#jform_client_fio-find').val());
		});

		jQuery( "#choose_cleint_btn" ).click(function(){
			if(jQuery('#choose_fields').css('display') == 'none'){
				document.getElementById("choose_cleint_btn").innerHTML = 'Добавить нового клиента';
				jQuery('#choose_fields').show();
				jQuery('#jform_client_name-lbl').hide();
				jQuery('#jform_client_name').hide();
				jQuery('#jform_client_contacts-lbl').hide();
				jQuery('#jform_client_contacts').hide();
                jQuery('#jform_client_contacts').removeAttr("required");
			}
			else {
				document.getElementById("choose_cleint_btn").innerHTML = 'Выбрать существующего клиента';
				jQuery('#choose_fields').hide();
				jQuery('#jform_client_name-lbl').show();
				jQuery('#jform_client_name').show();
				jQuery('#jform_client_contacts-lbl').show();
				jQuery('#jform_client_contacts').show();

			}
			if(jQuery('#new_client').css('display') != 'none')
				jQuery('#new_client').hide();
		});
		
		jQuery('#find_client_btn').click(function(){
			document.getElementById('clients').innerHTML='';
			var fio = jQuery("#jform_client_fio-find").val();
			jQuery.ajax({
					url: "index.php?option=com_gm_ceiling&task=findOldClients",
					data: {
						fio: fio
					},
					dataType: "json",
					async:true,
					success: function(data){
					    //data = JSON.parse(date);
						console.log(data);
						jQuery('#clients').find('option').remove();
						for(var i = 0; i < data.length; i++)
						{
							jQuery('<option>').val(data[i].id).text(data[i].client_name).appendTo('#clients');
                            jQuery('<option>').attr("phone",data[i].client_contacts);
						}
						jQuery('#client_id').val(jQuery('#clients option:selected').val());
                        jQuery('#jform_client_contacts').val(jQuery('#clients option:selected').attr("phone"));
						console.log(jQuery('#client_id').val());
					},
					error: function(data){
						console.log(data);
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

    jQuery(function(){
        jQuery('#clients').change(function(){
            var client_id = jQuery('#clients :selected').text();
            var client_contacts = jQuery('#clients :selected').attr("phone");
            jQuery('#jform_client_fio-find').val(client_id);
            jQuery('#jform_client_contacts').val(client_contacts);
        })
    });

	// Подсказки по городам
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
	// ---------------------------------

	var $ = jQuery;
	$(document).ready(function() {
		$(window).keydown(function(event){
			if(event.keyCode == 13) {
			event.preventDefault();
			return false;
			}
		});
	});

</script>
