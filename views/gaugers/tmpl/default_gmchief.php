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
	$gaugers_id = $model->getDealerGaugers($user->dealer_id);

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

<?=parent::getButtonBack();?>

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
				<!-- <td><img src="components/com_gm_ceiling/views/gaugers/tmpl/images/9e9e9e.png" alt="Серый"></td>
				<td>Замер был выполнен</td> -->
				<td><img src="components/com_gm_ceiling/views/gaugers/tmpl/images/digits.png" alt="Выходной"></td>
				<td>Выходные часы</td>
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
	<div id="modal-window-container-tar">
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
			<p id="delete_container"><button type="button" id="delete_day_off" class="btn btn-danger">Удалить</button></p>
		</div>
	</div>
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
		var div2 = jQuery("#modal-window-choose-tar"); // тут указываем ID элемента
        if (!div2.is(e.target) // если клик был не по нашему блоку
            && div2.has(e.target).length === 0) { // и не по его дочерним элементам
            jQuery("#modal-window-choose-tar").hide();
            jQuery("#close-tar").hide();
            jQuery("#modal-window-container-tar").hide();
        }
    });
    //--------------------------------------------------

	// перенаправление на страницу заказа
	function ReplaceToOrder(project) {
		location.replace("/index.php?option=com_gm_ceiling&view=projectform&type=gmchief&id="+project);
	}
	// ------------------------------------------------


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
			jQuery.ajax({
				type: 'POST',
				url: "/index.php?option=com_gm_ceiling&task=gaugers.FindFreeDay",
				dataType: 'json',
				data: {
					date: date,
					id: id_gauger,
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
			jQuery("#modal-window-with-table").show();
			jQuery("#window-with-table").show('slow');
			jQuery("#close-modal-window").show();
		});
		jQuery("#calendars-container").on("click", ".current-month, .day-off", function() {
			ChoosenDay = this.id;
			kind = "empty";
			ChoosenDay = jQuery(this).attr("id");
			WhatDay(ChoosenDay);
			ListOfWork(kind, d, m, y);
			jQuery.ajax({
				type: 'POST',
				url: "/index.php?option=com_gm_ceiling&task=gaugers.FindFreeDay",
				dataType: 'json',
				data: {
					date: date,
					id: id_gauger,
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
			window.date = y+"-"+m+"-"+d;
			date_to_modal_window = d+"."+m+"."+y;
			window.id_gauger = ChoosenDay.match("I(.*)I")[1];
			jQuery("#table-gauging").empty();
			var table = "";
			if (kind == "empty") {
				table = '<tr id="caption-data"><td colspan=2>'+date_to_modal_window+'</td></tr><tr><td colspan=2>В данный момент на этот день замеров нет</td></tr>';        
				jQuery.ajax({
					type: 'POST',
					url: "/index.php?option=com_gm_ceiling&task=gaugers.GetGaugersWorkDayOff",
					dataType: 'json',
					data: {
						date: date,
						id: id_gauger,
					},
					success: function(data) {
						Array.from(data).forEach(function(element) {
							table += '<tr><td style="width: 25%;">'+element.project_calculation_date.substr(11, 5)+" - "+element.project_calculation_day_off.substr(11, 5)+'</td><td style="width: 75%;">Выходной</td></tr>';
						});
						jQuery("#table-gauging").append(table);
					}
				});
			} else {
				table += '<tr id="caption-data"><td colspan="6">'+date_to_modal_window+'</td></tr><tr id="caption-tr"><td>Время</td><td>Адрес</td></tr>';
				jQuery.ajax({
					type: 'POST',
					url: "/index.php?option=com_gm_ceiling&task=gaugers.GetGaugersWorkDayOff",
					data: {
						date: date,
						id: id_gauger,
					},
					success: function(data) {
						data = JSON.parse(data); // замеры и выходные
						console.log(data);
						Array.from(data).forEach(function(element) {
							if (element.project_info == null) {
								table += '<tr><td style="width: 25%;">'+element.project_calculation_date.substr(11, 5)+" - "+element.project_calculation_day_off.substr(11, 5)+'</td><td style="width: 75%;">Выходной</td></tr>';
							}  else {
								if (element.project_status != 3) {
									timegauging2 = element.project_calculation_date.substr(11, 2);
									if (element.project_calculation_date.substr(11, 1) == "0") {
										timegauging2 = element.project_calculation_date.substr(12, 1);
										if (timegauging2 == 9) {
											timegauging2 = "10";
										} else {
											timegauging2++;
										}
									} else {
										timegauging2++;
									}
									timegauging = element.project_calculation_date.substr(11, 5)+" - "+timegauging2+":00";
									table += '<tr class="clickabel" onclick="ReplaceToOrder('+element.id+')">';
									table += '<td style="width: 25%;">'+timegauging+'</td><td style="width: 75%;">'+element.project_info+'</td></tr>';
								}
							}
						});
						jQuery("#table-gauging").append(table);
					}
				});
			}
		}
		// -----------------------------------------

		// нажатие на "добавить выходной"
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
			jQuery("#modal-window-choose-tar").show();
		});
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
					url: "/index.php?option=com_gm_ceiling&task=gaugers.GetGaugingForSaveDayOff",
					dataType: 'json',
					data: {
						datetime1: datetime1,
						datetime2: datetime2,
						id: id_gauger,
					},
					success: function(data) {
						if (data == "ok") {
							jQuery.ajax({
								type: 'POST',
								url: "/index.php?option=com_gm_ceiling&task=gaugers.SaveDayOff",
								data: {
									datetime1: datetime1,
									datetime2: datetime2,
									id_gauger: id_gauger
								},
								success: function(data) {
									if (data == "no") {
										jQuery("#wrong-window").text("Не удалось сохранить время. Повторите попытку позже.");
									} else {
										if (jQuery("#"+ChoosenDay).attr("class") == "current-month") {
											jQuery("#"+ChoosenDay).attr("class", "day-off");
										}
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
									}
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
							jQuery("#wrong-window").text("В данный промежуток времени у бригады есть монтаж");
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
				jQuery("#wrong-window").text("Введите корректный промежуток времени");
			}
        });
        //------------------------------------------

		// убрать красный текст ошибки
		jQuery("#modal-window-1-tar").on("change", "#hours1, #hours2", function() {
			jQuery("#wrong-window").empty();
		});

		// удалить выходной день
		jQuery("#delete_day_off").click( function() {
			jQuery.ajax({
				type: 'POST',
				url: "/index.php?option=com_gm_ceiling&task=gaugers.DeleteFreeDay",
				dataType: 'json',
				data: {
					date: date,
					id: id_gauger,
				},
				success: function(data) {
					if (data == "no") {
						jQuery("#wrong-window").text("Не удалось удалить время. Повторите попытку позже.");
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

	});

</script>