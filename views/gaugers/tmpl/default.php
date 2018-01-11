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

$model = Gm_ceilingHelpersGm_ceiling::getModel('gaugers');
$gaugers_id = $model->getData($dealerId);

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
$FlagCalendar = [4, $dealerId];
foreach ($gaugers_id as $value) {
	$calendars .= '<div class="calendars-gaugers"><p class="gaugers-name">';
	$calendars .= $value->name;
	$calendars .= "</p>";
	$calendars .= Gm_ceilingHelpersGm_ceiling::DrawCalendarTar($value->id, $month1, $year1, $FlagCalendar);
	$calendars .= Gm_ceilingHelpersGm_ceiling::DrawCalendarTar($value->id, $month2, $year2, $FlagCalendar);
	$calendars .= "</div>";
}
//----------------------------------------------------------------------------------------------------------


?>

<link rel="stylesheet" href="components/com_gm_ceiling/views/gaugers/tmpl/css/style.css" type="text/css" />

<div id="content-tar">
	<h2>Замерщики</h2>
	<div id="btn-container">
		<button id="add-brigade" class="btn btn-success btn-small" onClick='location.href="/index.php?option=com_gm_ceiling&view=gaugerform"'>Добавить замерщика</button>
	</div>
	<div id="legenda-container">
		<table id="legenda">
			<tr>
				<td><img src="components/com_gm_ceiling/views/gaugers/tmpl/images/414099.png" alt="Синий"></td>
				<td>День занят полностью</td>
				<td><img src="components/com_gm_ceiling/views/gaugers/tmpl/images/d3d3f9.png" alt="Голубой"></td>
				<td>Есть замеры в этот день</td>
				<td><img src="components/com_gm_ceiling/views/gaugers/tmpl/images/9e9e9e.png" alt="Серый"></td>
				<td>Есть замеры в этот день</td>
			</tr>
		</table>
	</div>
	<div id="prev-button-container">
		<button id="button-prev"><i class="fa fa-arrow-left" aria-hidden="true"></i></button>
	</div>
	<div id="calendars-container">
		<?php echo $calendars; ?>
	</div>
	<div id="next-button-container">
		<button id="button-next"><i class="fa fa-arrow-right" aria-hidden="true"></i></button>
	</div>
	<div id="modal-window-with-table">
		<button type="button" id="close-modal-window"><i class="fa fa-times fa-times-tar" aria-hidden="true"></i></button>
        <div id="window-with-table">
            <table id="table-gauging"></table>
			<div id="free_day_container">
			<button type="button" id="add_free_day" class="btn btn-primary"></button>
			</div>
        </div>
	</div>
	<!-- <div id="modal-window-container-tar">
		<button id="close-tar" type="button"><i class="fa fa-times fa-times-tar" aria-hidden="true"></i></button>
		<div id="modal-window-choose-tar">
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
			<div id="wrong-window"></div>
			<p><button type="button" id="save-choise-tar" class="btn btn-primary">Ок</button></p>
		</div>
	</div> -->
</div>

<script>
	// листание календаря
    month_old1 = 0;
    year_old1 = 0;
    month_old2 = 0;
    year_old2 = 0;
    jQuery("#button-next").click(function () {
        month1 = <?php echo $month1; ?>;
        year1 = <?php echo $year1; ?>;
        month2 = <?php echo $month2; ?>;
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
        jQuery("#calendars-container").empty();
		<?php foreach ($gaugers_id as $value) { ?>
			calendars = "";
			jQuery.ajax({
				async: false,
				type: 'POST',
				url: "index.php?option=com_gm_ceiling&task=UpdateCalendarTar",
				data: {
					id: <?php echo $value->id; ?>,
					id_dealer: <?php echo $dealerId; ?>,
					flag: 4,
					month: month1,
					year: year1,
				},
				success: function (msg) {
					calendars = '<div class="calendars-gaugers"><p class="gaugers-name"><?php echo $value->name; ?></p>';
					calendars += msg;
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
			jQuery.ajax({
				async: false,
				type: 'POST',
				url: "index.php?option=com_gm_ceiling&task=UpdateCalendarTar",
				data: {
					id: <?php echo $value->id; ?>,
					id_dealer: <?php echo $dealerId; ?>,
					flag: 4,
					month: month2,
					year: year2,
				},
				success: function (msg) {
					calendars += msg;
					calendars += '</div>';
					jQuery("#calendars-container").append(calendars);
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
			Today(day, NowMonth, NowYear, <?php echo $value->id; ?>);
		<?php } ?>
    });
    jQuery("#button-prev").click(function () {
        month1 = <?php echo $month1; ?>;
        year1 = <?php echo $year1; ?>;
        month2 = <?php echo $month2; ?>;
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
		jQuery("#calendars-container").empty();
		<?php foreach ($gaugers_id as $value) { ?>
			calendars = "";
			jQuery.ajax({
				async: false,
				type: 'POST',
				url: "index.php?option=com_gm_ceiling&task=UpdateCalendarTar",
				data: {
					id: <?php echo $value->id; ?>,
					id_dealer: <?php echo $dealerId; ?>,
					flag: 4,
					month: month1,
					year: year1,
				},
				success: function (msg) {
					calendars = '<div class="calendars-gaugers"><p class="gaugers-name"><?php echo $value->name; ?></p>';
					calendars += msg;
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
			jQuery.ajax({
				async: false,
				type: 'POST',
				url: "index.php?option=com_gm_ceiling&task=UpdateCalendarTar",
				data: {
					id: <?php echo $value->id; ?>,
					id_dealer: <?php echo $dealerId; ?>,
					flag: 4,
					month: month2,
					year: year2,
				},
				success: function (msg) {
					calendars += msg;
					calendars += '</div>';
					jQuery("#calendars-container").append(calendars);
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
			Today(day, NowMonth, NowYear, <?php echo $value->id; ?>);
		<?php } ?>
	});
	//---------------------------------------------

	// функция подсвета сегоднешней даты
	var Today = function (day, month, year, id) {
        month++;
        jQuery("#current-monthD"+day+"DM"+month+"MY"+year+"YI"+id+"I").addClass("today");
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

	//скрыть модальное окно
    jQuery(document).mouseup(function (e) {
		var div = jQuery("#window-with-table");
		if (!div.is(e.target)
		    && div.has(e.target).length === 0) {
			jQuery("#close-modal-window").hide();
			jQuery("#modal-window-with-table").hide();
			jQuery("#window-with-table").hide();
		}
    });
    //--------------------------------------------------

	jQuery(document).ready(function () {
		// подсвет сегоднешней даты
        window.today = new Date();
        window.NowYear = today.getFullYear();
        window.NowMonth = today.getMonth();
		window.day = today.getDate();
		<?php foreach ($gaugers_id as $value) { ?>
			Today(day, NowMonth, NowYear, <?php echo $value->id; ?>);
		<?php } ?>
        //------------------------------------------

		// нажатие на день, чтобы посмотреть проекты на день
		jQuery("#calendars-container").on("click", ".not-full-day, .full-day", function() {
			ChoosenDay = this.id;
			kind = "no-empty";
			WhatDay(ChoosenDay);
			ListOfWork(kind, d, m, y);
			/* 
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
						} else {
							jQuery("#add_free_day").text("Изменить выходной");
							window.dataFree1 = data[0].date_from;
							window.dataFree2 = data[0].date_to;
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
			*/
			jQuery("#modal-window-with-table").show();
			jQuery("#window-with-table").show('slow');
			jQuery("#close-modal-window").show();
		});
		/* jQuery("#calendars-container").on("click", ".current-month, .day-off", function() {
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
					} else {
						jQuery("#add_free_day").text("Изменить выходной");
						window.dataFree1 = data[0].date_from;
						window.dataFree2 = data[0].date_to;
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
		}); */
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
			window.date = y+"-"+m+"-"+d;
			date_to_modal_window = d+"."+m+"."+y;
			window.id_gauger = ChoosenDay.match("I(.*)I")[1];
			var table = "";
			if (kind == "empty") {
				/* table = '<tr id="caption-data"><td colspan=2>'+d+'.'+m+'.'+y+'</td></tr><tr><td colspan=2>В данный момент на этот день монтажей нет</td></tr>';        
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
							table += '<tr><td style="width: 25%;">'+element.project_mounting_date+'</td><td style="width: 75%;">'+element.project_info+'</td></tr>';
						});
						jQuery("#table-mounting").append(table);
					}
				}); */
			} else {
				table += '<tr id="caption-data"><td colspan="6">'+date_to_modal_window+'</td></tr><tr id="caption-tr"><td>Время</td><td>Адрес</td><td>Замерщик</td></tr>';
				jQuery.ajax({
					type: 'POST',
					url: "/index.php?option=com_gm_ceiling&task=gaugers.GetGaugersWorkDayOff",
					data: {
						date: date,
						id: id_gauger,
					},
					success: function(data) {
						//Вывод замеров у НМС у замерщиков 14
						Array.prototype.diff = function(a) {
							return this.filter(function(i) {return a.indexOf(i) < 0;});
						};
						data = JSON.parse(data); // замеры и выходные
						console.log(data);
						/* 
							var table = '<tr><th class="caption"></th><th class="caption">Время</th><th class="caption">Адрес</th><th class="caption">Замерщик</th></tr>';
							AllTime.forEach( elementTime => {
								var t = elementTime.substr(0, 2);
								t++;
								Array.from(AllGauger).forEach(function(elementGauger) {
									table += '<tr><td><input type="radio" name="choose_time_gauger" value="'+elementTime+'"></td>';
									table += '<td>'+elementTime.substr(0, 5)+'-'+t+':00</td>';
									var emptytd = 0;
									Array.from(data).forEach(function(elementProject) {
										if (elementProject.project_calculator == elementGauger.id && elementProject.project_calculation_date.substr(11) == elementTime) {
											table += '<td>'+elementProject.project_info+'</td>';
											emptytd = 1;
										}
									});
									if (emptytd == 0) {
										table += '<td></td>';
									}
									table += '<td>'+elementGauger.name+'<input type="hidden" name="gauger" value="'+elementGauger.id+'"></td></tr>';
								});
							}); 
						*/
						jQuery("#projects_gaugers").empty();
						jQuery("#projects_gaugers").append(table);
					}
				});
			}
		}
		// -----------------------------------------

        // получение значений из селектов
        jQuery("#modal-window-container-tar").on("click", "#save-choise-tar", function() {
            var time1 = jQuery("#hours1").val();
            var time2 = jQuery("#hours2").val();
            var datetime1 = date+" "+time1;
			var datetime2 = date+" "+time2;
			if (time1.substr(0,2) < time2.substr(0,2)) {
				jQuery.ajax({
					type: 'POST',
					url: "/index.php?option=com_gm_ceiling&task=gaugers.SaveDayOff",
					data: {
						datetime1: datetime1,
						datetime2: datetime2,
						id_gauger: id_gauger
					},
					success: function(data) {
						jQuery("#"+idDay).attr("class", "day-off");
						jQuery("#close-tar").hide();
						jQuery("#modal-window-container-tar").hide();
						jQuery("#modal-window-choose-tar").hide();
						var n = noty({
								theme: 'relax',
								layout: 'center',
								maxVisible: 5,
								type: "success",
								text: "Выходной день (время) сохранено успешно."
							});
					},
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
				jQuery("#wrong-window").text("Введите корректный промежуток времени");
			}
        });
        //------------------------------------------

	});


</script>