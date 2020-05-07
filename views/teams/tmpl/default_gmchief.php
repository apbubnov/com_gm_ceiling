<?php
/**
 * @version    CVS: 1.0.0
 * @package    Com_Gm_ceiling
 * @author     Mikhail  <vms@itctl.ru>
 * @copyright  2016 Mikhail 
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
//JHtml::_('formbehavior.chosen', 'select');

$user       = JFactory::getUser();
$userId     = $user->get('id');
$dealerId   = $user->dealer_id;

$teams_model = Gm_ceilingHelpersGm_ceiling::getModel('teams');
$brigadesData = $teams_model->getDatas($dealerId);

// календарь
$month1 = date("n");
$year1 = date("Y");
if ($month1 == 12) {
    $month2 = 1;
    $year2 = $year1;
    $year2++;
} else {
    $month2 = $month1;
    $month2++;
    $year2 = $year1;
}
$FlagCalendar = [1, $dealerId];
$allBrigades = [];
foreach ($brigadesData as $value){
    $brigades = json_decode($value->mounters);
    $allBrigades = array_merge($allBrigades,$brigades);
    $city = (!empty($value->name) ? $value->name : 'Город не указан');
    $calendars .= "<div class='row brigade_city' data-id='$value->city_id' style='text-align: left'>";
    $calendars .= "<div class='col-md-10' style='font-size: 22pt'>$city <i class=\"fas fa-angle-down\"></i></div>";
    $calendars .= '</div>';
    $calendars .= '<div class="city_brigades" data-city_id="'.$value->city_id.'" style="display:none;">';
    for($i=0;$i<count($brigades);$i++){
        if($i % 3 == 0){
            $calendars.= '<div class="row" style="margin-bottom: 15px;">';
        }
        $calendars .= '<div class="col-md-4" >';
        $calendars .= '<div class="row center" style="width: 98%">';
        $calendars .= '<div class="col-md-10"><a href="/index.php?option=com_gm_ceiling&view=team&id='.$brigades[$i]->id.'class="site-tar">'.$brigades[$i]->name.'</a></div><div class="col-md-2"><button class="btn btn-danger btn-sm btn_del_brigade" type="button" data-id="'.$brigades[$i]->id.'"><i class="fas fa-trash-alt" aria-hidden="true"></i></button></div>';
        $calendars .= '</div>';
        $calendars .= '<div class="row" style="width: 98%">';
        $calendars .= '<div class="col-md-12">' . $brigades[$i]->include_mounters . '</div>';
        $calendars .= '</div>';
        $calendars .= '<div class="row center firstMonth" data-brigade_id="'.$brigades[$i]->id.'" style="width: 98%">';
        $calendars .= Gm_ceilingHelpersGm_ceiling::DrawCalendarTar($brigades[$i]->id, $month1, $year1, $FlagCalendar);
        $calendars .= '</div>';
        $calendars .= '<div class="row center secondMonth" data-brigade_id="'.$brigades[$i]->id.'" style="width: 98%">';
        $calendars .= Gm_ceilingHelpersGm_ceiling::DrawCalendarTar($brigades[$i]->id, $month2, $year2, $FlagCalendar);
        $calendars .= '</div>';
        $calendars .= '</div>';
        if(($i+1) % 3 == 0 || $i+1 == count($brigades)){
            $calendars .= '</div>';
        }

    }
    $calendars .= '</div>';
}

?>
<?=parent::getButtonBack();?>
<div id="preloader" style="display: none;" class="PRELOADER_GM PRELOADER_GM_OPACITY">
    <div class="PRELOADER_BLOCK"></div>
    <img src="/images/GM_R_HD.png" class="PRELOADER_IMG">
</div>
<link rel="stylesheet" href="components/com_gm_ceiling/views/teams/tmpl/css/style.css" type="text/css" />
<style>
    #legenda-container img{
        max-height: 35px;
    }
    #legenda-container{
        font-size: 10pt;
        text-align: left;
    }
</style>
<div id="content-tar">
	<h2>Бригады</h2>
	<div id="btn-container">
		<button id="add-brigade" class="btn btn-success btn-small" onClick='location.href="/index.php?option=com_gm_ceiling&view=teamform"'>Добавить бригаду</button>
	</div>
	<div  id="legenda-container">
        <div class="row">
            <div class="col-md-3">
                <div class="row">
                    <div class="col-md-4">
                        <img src="components/com_gm_ceiling/views/teams/tmpl/images/ff3d3d.png" alt="Красный">
                    </div>
                    <div class="col-md-8">
                        Новый монтаж. Не просмотрен монтажником
                    </div>

                </div>
            </div>
            <div class="col-md-3">
                <div class="row">
                    <div class="col-md-4">
                        <img src="components/com_gm_ceiling/views/teams/tmpl/images/fff23d.png" alt="Желтый">
                    </div>
                    <div class="col-md-8">
                        Новый монтаж. Просмотрен монтажником
                    </div>

                </div>
            </div>
            <div class="col-md-3">
                <div class="row">
                    <div class="col-md-4">
                        <img src="components/com_gm_ceiling/views/teams/tmpl/images/414099.png" alt="Синий">
                    </div>
                    <div class="col-md-8">
                        Монтаж в работе
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="row">
                    <div class="col-md-4">
                        <img src="components/com_gm_ceiling/views/teams/tmpl/images/461f08.png" alt="Коричневый">
                    </div>
                    <div class="col-md-8">
                        Монтаж недовыполнен
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-3">
                <div class="row">
                    <div class="col-md-4">
                        <img src="components/com_gm_ceiling/views/teams/tmpl/images/1ffe4e.png" alt="Зеленый">
                    </div>

                    <div class="col-md-8">
                        Монтаж выполнен
                    </div>

                </div>
            </div>
            <div class="col-md-3">
                <div class="row">
                    <div class="col-md-4">
                        <img src="components/com_gm_ceiling/views/teams/tmpl/images/9e9e9e.png" alt="Серый">
                    </div>
                    <div class="col-md-8">
                        Заказ закрыт
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="row">
                    <div class="col-md-4">
                        <img src="components/com_gm_ceiling/views/teams/tmpl/images/digits.png" alt="Выходной">
                    </div>
                    <div class="col-md-8">
                        Выходные часы
                    </div>

                </div>
            </div>
        </div>
	</div>
	<div id="prev-button-container">
		<button id="button-prev"><i class="fa fa-arrow-left" aria-hidden="true"></i></button>
	</div>
	<div class="container" id="calendars-container">
		<?php echo $calendars; ?>
	</div>
	<div id="next-button-container">
		<button id="button-next"><i class="fa fa-arrow-right" aria-hidden="true"></i></button>
	</div>
	<div id="modal-window-with-table">
		<button type="button" id="close-modal-window"><i class="fa fa-times fa-times-tar" aria-hidden="true"></i></button>
        <div id="window-with-table">
            <table id="table-mounting"></table>
			<div id="free_day_container">
			<button type="button" id="add_free_day" class="btn btn-primary"></button>
			</div>
        </div>
	</div>
	<div id="modal-window-container-tar">
		<button type="button" id="close-tar"><i class="fa fa-times fa-times-tar" aria-hidden="true"></i></button>
		<div id="modal-window-1-tar">
			<p id="date-modal"></p>
			<p><strong>Выберите время выходного дня:</strong></p>
			<p>
				<table id = "hours">
					<tr>
						<td>С: </td>
						<td>
							<select name="hours1" id='hours1'>
								<option value='09:00:00'>09:00</option>
								<option value='10:00:00'>10:00</option>
								<option value='11:00:00'>11:00</option>
								<option value='12:00:00'>12:00</option>
								<option value='13:00:00'>13:00</option>
								<option value='14:00:00'>14:00</option>
								<option value='15:00:00'>15:00</option>
								<option value='16:00:00'>16:00</option>
								<option value='17:00:00'>17:00</option>
								<option value='18:00:00'>18:00</option>
								<option value='19:00:00'>19:00</option>
								<option value='20:00:00'>20:00</option>
								<option value='21:00:00'>21:00</option>
							</select>
						</td>
					</tr>
					<tr>
						<td>До: </td>
						<td>
							<select name="hours2" id='hours2'>
								<option value='09:00:00'>09:00</option>
								<option value='10:00:00'>10:00</option>
								<option value='11:00:00'>11:00</option>
								<option value='12:00:00'>12:00</option>
								<option value='13:00:00'>13:00</option>
								<option value='14:00:00'>14:00</option>
								<option value='15:00:00'>15:00</option>
								<option value='16:00:00'>16:00</option>
								<option value='17:00:00'>17:00</option>
								<option value='18:00:00'>18:00</option>
								<option value='19:00:00'>19:00</option>
								<option value='20:00:00'>20:00</option>
								<option value='21:00:00'>21:00</option>
							</select>
						</td>
					</tr>
				</table>
			</p>
			<div id="wrong-window2"></div>
			<p><button type="button" id="save_choise_tar" class="btn btn-primary">Сохранить</button></p>
			<p id="delete_container"><button type="button" id="delete_day_off" class="btn btn-danger">Удалить</button></p>
		</div>
	</div>
</div>

<script>
    var notes=[],
        month_old1 = 0,
        year_old1 = 0,
        month_old2 = 0,
        year_old2 = 0,
        brigades = JSON.parse('<?= json_encode($allBrigades)?>'),
        calendar,
        today = new Date(),
        NowYear = today.getFullYear(),
        NowMonth = today.getMonth(),
        day = today.getDate();

    jQuery(document).mouseup(function (e) { // событие клика по веб-документу
        var div = jQuery("#window-with-table"); // тут указываем ID элемента
        if (!div.is(e.target) && div.has(e.target).length == 0) { // не по элементу и не по его дочерним элементам
            jQuery("#window-with-table").hide();
            jQuery("#close-modal-window").hide();
            jQuery("#modal-window-with-table").hide();
			jQuery("#table-mounting").empty();
        }
		var div2 = jQuery("#modal-window-1-tar"); // тут указываем ID элемента
        if (!div2.is(e.target) && div2.has(e.target).length == 0) { // и не по его дочерним элементам
            jQuery("#modal-window-1-tar").hide();
            jQuery("#close-tar").hide();
            jQuery("#modal-window-container-tar").hide();
        }
    });

	jQuery(document).ready(function () {
	    jQuery('.brigade_city').click(function(){
	        var city_id = jQuery(this).data('id');
            jQuery('.city_brigades[data-city_id="'+city_id+'"]').toggle();
            var i = jQuery(this).find('i');

            if(i.hasClass('fa-angle-down')){
                i.removeClass("fa-angle-down").addClass("fa-angle-up");
            }
            else if(i.hasClass('fa-angle-up')){
                i.removeClass("fa-angle-up").addClass("fa-angle-down");
            }
        });

		if (screen.width < 768) {
            jQuery("#button-prev").css({"width":"25px"});
			jQuery("#prev-button-container").css({"left":"0px"});
			jQuery("#button-next").css({"width":"25px"});
			jQuery("#next-button-container").css({"right":"0px"});
		}

		jQuery.each(brigades,function (index,elem) {
            Today(day, NowMonth, NowYear, elem.id);
        });

        jQuery("#button-next").click(function () {
            var month1 = <?php echo $month1; ?>,
                year1 = <?php echo $year1; ?>,
                month2 = <?php echo $month2; ?>,
                year2 = <?php echo $year2; ?>;

            if (month_old1 != 0) {
                month1 = month_old1;
                year1 = year_old1;
                month2 = month_old2;
                year2 = year_old2;
            }
            if (month1 == 12) {
                month1 = 1;
                year1++;
            } else {
                month1++;
            }
            if (month2 == 12) {
                month2 = 1;
                year2++;
            } else {
                month2++;
            }
            month_old1 = month1;
            year_old1 = year1;
            month_old2 = month2;
            year_old2 = year2;

            jQuery.each(brigades,function (index,elem) {
                updateCalendar(elem.id,month1,year1);
                jQuery('.firstMonth[data-brigade_id='+elem.id+']').empty();
                jQuery('.firstMonth[data-brigade_id='+elem.id+']').append(calendar);
                updateCalendar(elem.id,month2,year2);
                jQuery('.secondMonth[data-brigade_id='+elem.id+']').empty();
                jQuery('.secondMonth[data-brigade_id='+elem.id+']').append(calendar);
                Today(day, NowMonth, NowYear, elem.id);
            });
        });

        jQuery("#button-prev").click(function () {
            var month1 = <?php echo $month1; ?>,
                year1 = <?php echo $year1; ?>,
                month2 = <?php echo $month2; ?>,
                year2 = <?php echo $year2; ?>;
            if (month_old1 != 0) {
                month1 = month_old1;
                year1 = year_old1;
                month2 = month_old2;
                year2 = year_old2;
            }
            if (month1 == 1) {
                month1 = 12;
                year1--;
            } else {
                month1--;
            }
            if (month2 == 1) {
                month2 = 12;
                year2--;
            } else {
                month2--;
            }
            month_old1 = month1;
            year_old1 = year1;
            month_old2 = month2;
            year_old2 = year2;
            jQuery("#preloader").show();
            jQuery.each(brigades,function (index,elem) {
                updateCalendar(elem.id,month1,year1);
                jQuery('.firstMonth[data-brigade_id='+elem.id+']').empty();
                jQuery('.firstMonth[data-brigade_id='+elem.id+']').append(calendar);
                updateCalendar(elem.id,month2,year2);
                jQuery('.secondMonth[data-brigade_id='+elem.id+']').empty();
                jQuery('.secondMonth[data-brigade_id='+elem.id+']').append(calendar);
                Today(day, NowMonth, NowYear, elem.id);
            });
        });
        //------------------------------------------

		// нажатие на день, чтобы посмотреть проекты на день
		jQuery("#calendars-container").on("click", ".day-not-read, .day-read, .day-in-work, .day-underfulfilled, .day-complite, .old-project", function() {
			ChoosenDay = this.id;
			kind = "no-empty";
			WhatDay(ChoosenDay);
			ListOfWork(kind, d, m, y);
			jQuery.ajax({
				type: 'POST',
				url: "/index.php?option=com_gm_ceiling&task=teams.FindFreeDay",
				dataType: 'json',
				data: {
					date: date,
					id: idBrigade,
				},
				success: function(data) {
					if (data.length == 0) {
						jQuery("#add_free_day").text("Добавить выходной");
						window.dataFree1 = 0;
						window.dataFree2 = 0;
						jQuery("#delete_container").hide();
					} else {
						jQuery("#add_free_day").text("Изменить выходной");
						window.dataFree1 = data[0].date_from;
						window.dataFree2 = data[0].date_to;
						jQuery("#delete_container").show();
					}
				},
				error: function (data) {
					var n = noty({
						theme: 'relax',
						layout: 'center',
						maxVisible: 5,
						type: "error",
						text: "Ошибка при попытке загрузки инфомации. Сервер не отвечает"
					});
				}
			});
			jQuery("#window-with-table").show('slow');
			jQuery("#close-modal-window").show();
			jQuery("#modal-window-with-table").show();
		});

		jQuery("#calendars-container").on("click", ".current-month, .day-off", function() {
			ChoosenDay = this.id;
			kind = "empty";
			ChoosenDay = jQuery(this).attr("id");
			WhatDay(ChoosenDay);
			ListOfWork(kind, d, m, y);
			jQuery.ajax({
				type: 'POST',
				url: "/index.php?option=com_gm_ceiling&task=teams.FindFreeDay",
				dataType: 'json',
				data: {
					date: date,
					id: idBrigade,
				},
				success: function(data) {
					if (data.length == 0) {
						jQuery("#add_free_day").text("Добавить выходной");
						window.dataFree1 = 0;
						window.dataFree2 = 0;
						jQuery("#delete_container").hide();
					} else {
						jQuery("#add_free_day").text("Изменить выходной");
						window.dataFree1 = data[0].date_from;
						window.dataFree2 = data[0].date_to;
						jQuery("#delete_container").show();
					}
				},
				error: function (data) {
					var n = noty({
						theme: 'relax',
						layout: 'center',
						maxVisible: 5,
						type: "error",
						text: "Ошибка при попытке загрузки инфомации. Сервер не отвечает"
					});
				}
			});
			jQuery("#window-with-table").show('slow');
			jQuery("#close-modal-window").show();
			jQuery("#modal-window-with-table").show();
		});
		// -----------------------------------------

		// функция узнать выбранный день, месяц, год
		function WhatDay(id) {
			var nov_reg1 = "D(.*)D";
			d = id.match(nov_reg1)[1];
			var nov_reg2 = "M(.*)M";
			m = id.match(nov_reg2)[1];
			var nov_reg3 = "Y(.*)Y";
			y = id.match(nov_reg3)[1];
			if (d.length == 1) {
                d = "0"+d;
            }
            if (m.length == 1) {
                m = "0"+m;
            }
		}
		// ----------------------------------------

		// функция вывода работ (таблицы) дня при нажатии на день
		function ListOfWork(kind, d, m, y) {
			date = y+"-"+m+"-"+d;
			date_to_modal_window = d+"."+m+"."+y;
			idBrigade = ChoosenDay.match("I(.*)I")[1];
			jQuery("#table-mounting").empty();
			var table = "";
			if (kind == "empty") {
				table = '<tr id="caption-data"><td colspan=2>'+d+'.'+m+'.'+y+'</td></tr><tr><td colspan=2>В данный момент на этот день монтажей нет</td></tr>';        
				jQuery.ajax({
					type: 'POST',
					url: "/index.php?option=com_gm_ceiling&task=teams.GetMounting",
					dataType: 'json',
					data: {
						date: date,
						id: idBrigade,
					},
					success: function(data) {
					    console.log(data);
						Array.from(data).forEach(function(element) {
							table += '<tr><td style="width: 25%;">'+element.project_mounting_date+'</td><td style="width: 75%;">'+element.project_info+'</td></tr>';
						});
						jQuery("#table-mounting").append(table);
					}
				});
			} else {
				table += '<tr id="caption-data"><td colspan="8">'+d+'.'+m+'.'+y+'</td></tr><tr id="caption-tr"><td>№</td><td>Время</td><td>Адрес</td><td>Периметр</td><td>З/П</td><td>Остаток</td><td>Примечание</td><td>Статус</td></tr>';
				jQuery.ajax({
					type: 'POST',
					url: "/index.php?option=com_gm_ceiling&task=teams.GetMounting",
					dataType: 'json',
					data: {
						date: date,
						id: idBrigade,
					},
					success: function(data) {
						Array.from(data).forEach(function(element) {
							if (element.project_mounting_date.length < 6) {
								if (element.project_status == 5) {
									status = "В производстве";
								} else if (element.project_status == 6) {
									status = "На раскрое";
								} else if (element.project_status == 7) {
									status = "Укомплектован";
								} else if (element.project_status == 8) {
									status = "Выдан";
								} else if (element.project_status == 10) {
									status = "Ожидание монтажа";
								} else if (element.project_status == 16) {
									status = "Монтаж";
								} else if (element.project_status == 11) {
									status = "Монтаж выполнен";
								} else if (element.project_status == 17) {
									status = "Монтаж недовыполнен";
								} else if (element.project_status == 12) {
									status = "Заказ закрыт";
								} else if (element.project_status == 19) {
									status = "Заказ собран";
								}
                                else if (element.project_status == 24) {
                                    status = "Выполнено обагечивание";
                                }
                                else if (element.project_status == 25) {
                                    status = "Выполнена натяжка";
                                }
                                else if (element.project_status == 26) {
                                    status = "Установлена вставка";
                                }
                                else if (element.project_status == 27) {
                                    status = "Монтаж багета";
                                }
                                else if (element.project_status == 28) {
                                    status = "Натяжка";
                                }
                                else if (element.project_status == 29) {
                                    status = "Установка вставки";
                                }

								if (element.read_by_mounter == 0) {
									status += " / Не прочитан";
								}
								console.log(element);
								var note = "";
                                getProjectNotes(element.id);
                                jQuery.each(notes,function (index,elem) {
                                    note +=elem.description+": "+elem.value;
                                });
								perimeter = +element.perimeter;
								table += '<tr class="clickabel" onclick="ReplaceToOrder('+element.id+')"><td>'+element.id+'</td><td>'+element.project_mounting_date+'</td><td>'+element.project_info+'</td><td>'+perimeter.toFixed(2)+'</td><td>'+element.salary+'</td><td>'+element.project_rest+'</td><td>'+note+'</td><td>'+status+'</td></tr>';
							} else {
								table += '<tr><td>'+element.project_mounting_date+'</td><td colspan=5>'+element.project_info+'</td></tr>';
							}
						});
						jQuery("#table-mounting").append(table);
					}
				});
			}
		}
		// -----------------------------------------
        function getProjectNotes(project_id){
            jQuery.ajax({
                type: "POST",
                url: "index.php?option=com_gm_ceiling&task=project.getProjectNotes",
                dataType: 'json',
                data: {
                    project_id: project_id
                },
                async:false,
                success: function(msg) {
                    notes = msg;
                },
                error: function(msg) {
                    var n = noty({
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "error",
                        text: "Ошибка получения примечаний"
                    });
                }
            });
        }
		// нажатие на "добавить выходной" или "изменить выходной"
		jQuery("#add_free_day").click (function () {
			jQuery("#window-with-table").hide();
			jQuery("#close-modal-window").hide();
			jQuery("#modal-window-with-table").hide();
			jQuery("#date-modal").html("<strong>Выбранный день: "+date_to_modal_window+"</strong>");
			if (dataFree1 != 0 && dataFree2 != 0) {
                setTimeout(function() {
                    var hours1 = document.getElementById('hours1').options;
					var hours2 = document.getElementById('hours2').options;
					for (var i = 0; i < hours1.length; i++) {
						if (hours1[i].value == dataFree1.substr(11)) {
							document.getElementById('hours1').disabled = false;
							hours1[i].selected = true;
						}
					}
					for (var i = 0; i < hours2.length; i++) {
						if (hours2[i].value == dataFree2.substr(11)) {
							document.getElementById('hours2').disabled = false;
							hours2[i].selected = true;
						}
					}
                }, 200);
			}
			jQuery("#modal-window-container-tar").show();
			jQuery("#close-tar").show();
			jQuery("#modal-window-1-tar").show();
		});
		// -----------------------------------------

		// сохранение выходного времени
		jQuery("#save_choise_tar").click (function () {
			var time1 = jQuery("#hours1").val();
			var time2 = jQuery("#hours2").val();
			if (time1.substr(0,2) < time2.substr(0,2)) {
				jQuery.ajax({
					type: 'POST',
					url: "/index.php?option=com_gm_ceiling&task=teams.GetMountingForSaveDayOff",
					dataType: 'json',
					data: {
						date: date,
						time1: time1,
						time2: time2,
						id: idBrigade,
					},
					success: function(data) {
						if (data == "ok") {
							jQuery.ajax({
								type: 'POST',
								url: "/index.php?option=com_gm_ceiling&task=teams.SaveFreeDay",
								dataType: 'json',
								data: {
									date: date,
									time1: time1,
									time2: time2,
									id: idBrigade,
								},
								success: function(data) {
									if (data == "no") {
										jQuery("#wrong-window2").text("Не удалось сохранить время. Повторите попытку позже.");
									} else {
										if (jQuery("#"+ChoosenDay).attr("class") == "current-month") {
											jQuery("#"+ChoosenDay).attr("class", "day-off");
										}
										jQuery("#modal-window-container-tar").hide();
										jQuery("#close-tar").hide();
										jQuery("#modal-window-1-tar").hide();
										var n = noty({
											theme: 'relax',
											layout: 'center',
											maxVisible: 5,
											type: "success",
											text: "Выходной день (время) сохранено успешно."
										});
									}
								},
								dataType: "text",
								timeout: 10000,
								error: function (data) {
									var n = noty({
										theme: 'relax',
										layout: 'center',
										maxVisible: 5,
										type: "error",
										text: "Ошибка при попытке сохранить выходные часы. Сервер не отвечает"
									});
								}
							});
						} else {
							jQuery("#wrong-window2").text("В данный промежуток времени у бригады есть монтаж");
						}
					},
					dataType: "text",
					timeout: 10000,
					error: function (data) {
						var n = noty({
							theme: 'relax',
							layout: 'center',
							maxVisible: 5,
							type: "error",
							text: "Ошибка при попытке проверить выходные часы. Сервер не отвечает"
						});
					}
				});
			} else {
				jQuery("#wrong-window2").text("Введите корректный промежуток времени");
			}
		});
		//------------------------------------------

		// убрать красный текст ошибки
		jQuery("#modal-window-1-tar").on("change", "#hours1, #hours2", function() {
			jQuery("#wrong-window2").empty();
		});

		// удалить выходной день
		jQuery("#delete_day_off").click( function() {
			jQuery.ajax({
				type: 'POST',
				url: "/index.php?option=com_gm_ceiling&task=teams.DeleteFreeDay",
				dataType: 'json',
				data: {
					date: date,
					id: idBrigade,
				},
				success: function(data) {
					if (data == "no") {
						jQuery("#wrong-window2").text("Не удалось удалить время. Повторите попытку позже.");
					} else {
						if (jQuery("#"+ChoosenDay).attr("class") == "day-off") {
							jQuery("#"+ChoosenDay).attr("class", "current-month");
						}
						jQuery("#modal-window-container-tar").hide();
						jQuery("#close-tar").hide();
						jQuery("#modal-window-1-tar").hide();
						var n = noty({
							theme: 'relax',
							layout: 'center',
							maxVisible: 5,
							type: "success",
							text: "Выходной день (время) удалено успешно."
						});
					}
				},
				dataType: "text",
				timeout: 10000,
				error: function (data) {
					var n = noty({
						theme: 'relax',
						layout: 'center',
						maxVisible: 5,
						type: "error",
						text: "Ошибка при попытке удалить выходные часы. Сервер не отвечает"
					});
				}
			});
		});
		// -----------------------------------------
		jQuery('.btn_del_brigade').click(function()
        {
            var user_id = jQuery(this).data('id');
            alert(user_id);
            noty({
                layout: 'topCenter',
                type: 'default',
                modal: true,
                text: 'Вы действительно хотите удалить бригаду?',
                killer: true,
                buttons: [
                    {
                        addClass: 'btn btn-primary', text: 'Да', onClick: function ($noty) {
                            jQuery.ajax({
                                url: "index.php?option=com_gm_ceiling&task=users.deleteUser",
                                data: {
                                    user_id: user_id
                                },
                                dataType: "json",
                                async: true,
                                success: function(data) {
                                    location.reload();
                                },
                                error: function(data) {
                                    console.log(data);
                                    var n = noty({
                                        timeout: 2000,
                                        theme: 'relax',
                                        layout: 'center',
                                        maxVisible: 5,
                                        type: "error",
                                        text: "Ошибка сервера"
                                    });
                                }
                            });
                            $noty.close();
                        }
                    },
                    {
                        addClass: 'btn', text: 'Нет', onClick: function ($noty) {
                            $noty.close();
                        }
                    }
                ]
            });
        });
	});

    var Today = function (day, month, year, id) {
        month++;
        jQuery("#current-monthD"+day+"DM"+month+"MY"+year+"YI"+id+"I").addClass("today");
    }

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

    function ReplaceToOrder(project) {
        location.replace("/index.php?option=com_gm_ceiling&view=projectform&type=gmchief&id="+project);
    }

    function updateCalendar(id,month,year){
        jQuery.ajax({
            async: false,
            type: 'POST',
            url: "index.php?option=com_gm_ceiling&task=UpdateCalendarTar",
            data: {
                id: id,
                id_dealer: '<?php echo $dealerId; ?>',
                flag: 1,
                month: month,
                year: year,
            },
            success: function (msg) {
                jQuery("#preloader").hide();
                calendar = msg;
            },
            dataType: "text",
            timeout: 10000,
            error: function () {
                jQuery("#preloader").hide();
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

</script>