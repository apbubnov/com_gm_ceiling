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
$FlagCalendar = [3, 1];
$calendar = Gm_ceilingHelpersGm_ceiling::DrawCalendarTar($userId, $month, $year, $FlagCalendar);
//----------------------------------------------------------------------------------

// все замерщики
$model = Gm_ceilingHelpersGm_ceiling::getModel('reservecalculation');
$AllGaugerGM = $model->FindAllGauger(1);
$AllGaugerDealer = $model->FindAllGauger($user->dealer_id);
if (count($AllGaugerDealer) == 0) {
    array_push($AllGaugerDealer, ["id" => $userId, "name" => $user->name]);
}
//----------------------------------------------------------------------------------


?>

<script src="https://api-maps.yandex.ru/2.1/?lang=ru_RU" type="text/javascript"></script>

<link rel="stylesheet" href="/components/com_gm_ceiling/views/addproject/tmpl/css/style.css" type="text/css" />

<form id="calculate_form" action="/index.php?option=com_gm_ceiling&task=addproject.save" method="post" class="form-validate form-horizontal" enctype="multipart/form-data">
	<!-- Скрытые поля -->
	<input name="jform[project_calculation_date]" id="jform_project_calculation_date" value="" type="hidden">
	<input name="jform[project_calculation_daypart]" id="jform_project_calculation_daypart" value="" type="hidden">
	<input name="jform[project_calculator]" id="jform_project_calculator" type="hidden" value="">
	<!-- - - - - - - - - - - - - - - - - - - - - - -->
	<h2> Добавить замер </h2>
	
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
			<input name="jform[client_contacts]" id="jform_client_contacts" value="" class="required" style="width:100%; margin-bottom:1em;" placeholder="Телефоны клиента" required="required" aria-required="true" type="text">
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
	<div class="control-group">
		<?php if ($user->dealer_id != 1) { ?>
			<div class="control-label">
				<label id="jform_who_calculate-lbl" for="jform_who_calculate" class="required">Выберите замерщика<span class="star">&nbsp;*</span></label>
			</div>
			<div class="controls">
				<p><input name="jform[who_calculate]" id="jform_who_calculate1" type="radio" value="1" checked><label id="jform_who_calculate-lbl" for="jform_who_calculate1" class="required">Замерщик ГМ</label></p>
				<p><input name="jform[who_calculate]" id="jform_who_calculate2" type="radio" value="0"><label id="jform_who_calculate-lbl" for="jform_who_calculate2" class="required">Замерщик Дилера</label></p>
			</div>
		<?php } else { ?>
			<input name="jform[who_calculate]" id="jform_who_calculate1" type="hidden" value="1" checked>
		<?php } ?>
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
	<button id="calculate_button" class="btn btn-primary" style="width:100%;" type="submit">Записать</button>
	</div>
	<div id="modal-window-container-tar">
		<button id="close-tar" type="button"><i class="fa fa-times fa-times-tar" aria-hidden="true"></i></button>
		<div id="modal-window-choose-tar">
			<div id="div1">
				<p id="date-modal"></p>
				<p><strong>Выберите время замера (и замерщика):</strong></p>
			</div>
			<div id="table_wraper">
				<p>
					<table id="projects_gaugers"></table>
				</p>
			</div>
			<div id="div2">
				<p><button type="button" id="save-choise-tar" class="btn btn-primary">Ок</button></p>
			</div>
		</div>
	</div>
</form>

<script>

	jQuery(window).resize(function() {
		heightAll = jQuery("#modal-window-choose-tar").css("height");
		height1 = jQuery("#div1").css("height");
		height2 = jQuery("#div2").css("height");
		height = heightAll - height1 - height2;
		jQuery("#table_wraper").css("height", height);
	});

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
		if (jQuery("#jform_who_calculate1").attr("checked") == "checked") {
			console.log("dealer 1");
			dealer = 1;
		}
		if (jQuery("#jform_who_calculate2").attr("checked") == "checked") {
			console.log("dealer 123");
			dealer = <?php echo $user->dealer_id; ?>;
		}
		update_calendar(month, year, dealer);
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
        if (jQuery("#jform_who_calculate1").attr("checked") == "checked") {
			console.log("dealer 1");
			update_calendar(month, year, 1);
		}
		if (jQuery("#jform_who_calculate2").attr("checked") == "checked") {
			console.log("dealer 123");
			update_calendar(month, year, <?php echo $user->dealer_id; ?>);
		}
    });
    function update_calendar(month, year, dealer) {
        jQuery.ajax({
            type: 'POST',
            url: "index.php?option=com_gm_ceiling&task=UpdateCalendarTar",
            data: {
                id: <?php echo $userId; ?>,
                id_dealer: dealer,
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

	jQuery( document ).ready(function(){
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
			setTimeout(function () {
				heightAll = jQuery("#modal-window-choose-tar").css("height");
				height1 = jQuery("#div1").css("height");
				height2 = jQuery("#div2").css("height");
				height = heightAll - height1 - height2;
				console.log(heightAll);
				console.log(height);
				jQuery("#table_wraper").css("height", height);
				console.log(jQuery("#table_wraper").css("height"));}, 1500);
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
					if (jQuery("#jform_who_calculate1").attr("checked") == "checked") {
						AllGauger = <?php echo json_encode($AllGaugerGM); ?>;
					} else {
						AllGauger = <?php echo json_encode($AllGaugerDealer); ?>;
					}
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
                                if (elementProject.project_calculator == elementGauger.id && elementProject.project_calculation_date.substr(11) == elementTime) {
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
        jQuery("#save-choise-tar").click(function() {
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
        //------------------------------------------

		// если изменился радиобаттон, менять календарь
		jQuery("input[name=\"jform[who_calculate]\"]").click( function () {
			month = 0;
			if (month == 0) {
				if (month_old != 0) {
					month = month_old;
					year = year_old;
				} else {
					month = <?php echo $month; ?>;
					year = <?php echo $year; ?>;
				}
			}
			if (jQuery("#jform_who_calculate1").attr("checked") == "checked") {
				update_calendar(month, year, 1);
			}
			if (jQuery("#jform_who_calculate2").attr("checked") == "checked") {
				update_calendar(month, year, <?php echo $user->dealer_id; ?>);
			}
		});
		//-------------------------------------------------

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
			}
			else{ 
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
						console.log(data);
						jQuery('#clients').find('option').remove();
						for(var i = 0; i < data.length; i++)
						{
							jQuery('<option>').val(data[i].id).text(data[i].client_name).appendTo('#clients');
						}
						jQuery('#client_id').val(jQuery('#clients option:selected').val());
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
		
		/*,
           map,
            placemark;
        /*function geocode() {
            // Забираем запрос из поля ввода.
            var request = $('#jform_project_info').val();
            // Геокодируем введённые данные.
            ymaps.geocode(request).then(function (res) {
                var obj = res.geoObjects.get(0),
                    error, hint;

            }, function (e) {
                console.log(e)
            })

        }*/
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
