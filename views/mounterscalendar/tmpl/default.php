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
    //JHtml::_('formbehavior.chosen', 'select');

    $user       = JFactory::getUser();
    $userId     = $user->get('id');
    $dealerId   = $user->dealer_id;

    // календарь
    $month = date("n");
    $year = date("Y");
    $FlagCalendar = [5, $dealerId];
    $calendar = Gm_ceilingHelpersGm_ceiling::DrawCalendarTar($userId, $month, $year, $FlagCalendar);

?>

<?=parent::getButtonBack();?>

<link rel="stylesheet" href="components/com_gm_ceiling/views/mounterscalendar/tmpl/CSS/style.css" type="text/css" />

<div id="content-tar">
    <h2 class="center tar-color-414099">Календарь работ</h2>
    <div id="prev-button-container">
        <button id="button-prev"><i class="fa fa-arrow-left" aria-hidden="true"></i></button>
    </div>
    <div id="calendar-container">
        <?php echo $calendar; ?>
    </div>
    <div id="next-button-container">
        <button id="button-next"><i class="fa fa-arrow-right" aria-hidden="true"></i></button>
    </div>
    <div id="legenda-container" class="tar-color-414099">
        <table id="legenda">
            <tr>
                <td class="right">
                    <img class="legenda_modal" src="components/com_gm_ceiling/views/mounterscalendar/tmpl/images/ff3d3d.png" alt="Красный">
                </td>
                <td class="left">Новый монтаж. Не просмотрен</td>
                <td class="right">
                    <img class="legenda_modal" src="components/com_gm_ceiling/views/mounterscalendar/tmpl/images/414099.png" alt="Синий">
                </td>
                <td class="left">Монтаж в работе</td>
            </tr>
            <tr>
                <td class="right">
                    <img class="legenda_modal" src="components/com_gm_ceiling/views/mounterscalendar/tmpl/images/fff23d.png" alt="Желтый">
                </td>
                <td class="left">Новый монтаж. Просмотрен</td>
                <td class="right">
                    <img class="legenda_modal" src="components/com_gm_ceiling/views/mounterscalendar/tmpl/images/461f08.png" alt="Коричневый">
                </td>
                <td class="left">Монтаж недовыполнен</td>
            </tr>
            <tr>
                <td class="right">
                    <img class="legenda_modal" src="components/com_gm_ceiling/views/mounterscalendar/tmpl/images/d3d3f9.png" alt="Голубой">
                </td>
                <td class="left">Выходные часы</td>
                <td class="right">
                    <img class="legenda_modal" src="components/com_gm_ceiling/views/mounterscalendar/tmpl/images/1ffe4e.png" alt="Зеленый">
                </td>
                <td class="left">Монтаж выполнен</td>
            </tr>
            <tr>
                <td class="right"></td>
                <td class="left"></td>
                <td class="right">
                    <img class="legenda_modal" src="components/com_gm_ceiling/views/mounterscalendar/tmpl/images/9e9e9e.png" alt="Серый">
                </td>
                <td class="left">Заказ закрыт</td>
            </tr>
        </table>
    </div>
    <div id="modal-window-with-table">
        <button type="button" id="close-modal-window"><i class="fa fa-times fa-times-tar" aria-hidden="true"></i></button>
        <div id="window-with-table">
            <table id="table-mounting"></table>
        </div>
    </div>
</div>
<script type='text/javascript'>
    // листание календаря
    month_old = 0;
    year_old = 0;
    jQuery("#button-next").click(function () {
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
    jQuery("#button-prev").click(function () {
        month = <?php echo $month; ?>;
        year = <?php echo $year; ?>;
        if (month_old != 0) {
            month = month_old;
            year = year_old;
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
                id_dealer: <?php echo $dealerId; ?>,
                flag: 5,
                month: month,
                year: year,
            },
            success: function (msg) {
                jQuery("#calendar-container").empty();
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

    // функция подсвета сегоднешней даты
    var Today = function (day, month, year) {
        month++;
        jQuery("#current-monthD"+day+"DM"+month+"MY"+year+"YI"+<?php echo $userId; ?>+"I").addClass("today");
    }

    // подсвет сегоднешней даты
    window.today = new Date();
    window.NowYear = today.getFullYear();
    window.NowMonth = today.getMonth();
    window.day = today.getDate();
    Today(day, NowMonth, NowYear);

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

    // закрытие модального окна, при нажатии вне модального окна
    jQuery(document).mouseup(function (e){ // событие клика по веб-документу
        var div = jQuery("#window-with-table"); // тут указываем ID элемента
        if (!div.is(e.target) // если клик был не по нашему блоку
            && div.has(e.target).length === 0) { // и не по его дочерним элементам
            jQuery("#window-with-table").hide();
            jQuery("#close-modal-window").hide();
            jQuery("#modal-window-with-table").hide();
        }
    });

    // открытие модального окна и узнаем какой день нажат
    jQuery("#calendar-container").on("click", ".current-month, .day-off2", function () {
        kind = "empty";        
        var id = jQuery(this).attr("id");
        WhatDay(id);
        ListOfWork(kind, ChooseDay, ChooseMonth, ChooseYear);
        jQuery("#window-with-table").show('slow');
        jQuery("#close-modal-window").show();
        jQuery("#modal-window-with-table").show();
    });
    jQuery("#calendar-container").on("click", ".day-not-read, .day-read, .day-in-work, .day-underfulfilled, .day-complite, .old-project", function () {
        kind = "no-empty";
        id = jQuery(this).attr("id");
        WhatDay(id);
        ListOfWork(kind, ChooseDay, ChooseMonth, ChooseYear);
        jQuery("#window-with-table").show('slow');
        jQuery("#close-modal-window").show();
        jQuery("#modal-window-with-table").show();
    });

    // функция узнать выбранный день, месяц, год
    function WhatDay(id) {
        var nov_reg1 = "D(.*)D";
        ChooseDay = id.match(nov_reg1)[1];
        var nov_reg2 = "M(.*)M";
        ChooseMonth = id.match(nov_reg2)[1];
        var nov_reg3 = "Y(.*)Y";
        ChooseYear = id.match(nov_reg3)[1];
    }
    // функция вывода работ (таблицы) дня при нажатии на день
    function ListOfWork(kind, day, month, year) {
        td = day;
        tm = month;
        ty = year;
        if (day.length == 1) {
            day = 0+day;
        }
        if (month.length == 1) {
            month = 0+month;
        }
        date = year+"-"+month+"-"+day;
        jQuery("#table-mounting").empty();
        if (kind == "empty") {
            TrOrders = '<tr id="caption-data"><td colspan=2>'+day+'-'+month+'-'+year+'</td></tr><tr><td colspan=2>В данный момент на этот день монтажей нет</td></tr>';  
             jQuery.ajax({
                type: 'POST',
                url: "/index.php?option=com_gm_ceiling&task=mounterscalendar.GetDataOfMounting",
                dataType: 'json',
                data: {
                    date: date,
                    id: <?php echo $userId?>,
                },
                success: function(data) {
                    if (data.length > 0) { 
                        Array.from(data).forEach(function(element) {
                            TrOrders += '<tr><td style="width: 25%;">'+element.project_mounting_date+'</td><td style="width: 75%;">'+element.project_info+'</td></tr>';
                        }); 
                    }
                    jQuery("#table-mounting").append(TrOrders);
                }
            });
        } else if (kind == "no-empty") {
            TrOrders2 = '<tr id="caption-data"><td colspan="7">'+day+'-'+month+'-'+year+'</td></tr><tr id="caption-tr"><td>Время</td><td>Адрес</td><td>P</td><td>З/П</td><td>Примечание</td><td>Статус</td><td>Этап</td></tr>';
            jQuery("#table-mounting").append(TrOrders2);
             jQuery.ajax( {
                type: "POST",
                url: "index.php?option=com_gm_ceiling&task=mounterscalendar.GetDataOfMounting",
                dataType: 'json',
                data: {
                    date: date,
                    id : <?php echo $userId; ?>
                },
                success: function(msg) {
                    console.log(msg);
                    var status, type, note, note2, comment_calc, project, adress, perimeter;
                    msg.forEach(function(element) {
                        if (element.project_mounting_date.length < 6) {
                            project = element.id;
                            adress = element.project_info;
                            perimeter = element.n5;
                            // комменты
                            if (<?php echo $dealerId; ?> == 1) {
                                if(element.gm_chief_note == null || element.gm_chief_note == undefined || element.gm_chief_note == "null" || element.gm_chief_note == "") {
                                    note = "";
                                } else {
                                    note = "Примечание НМС: "+element.gm_chief_note;
                                }
                                if(element.gm_calculator_note == null || element.gm_calculator_note == undefined || element.gm_calculator_note == "null" || element.gm_calculator_note == "") {
                                    note2 = "";
                                } else {
                                    note2 = "<br>Примечание замерщика: "+element.gm_calculator_note;
                                }
                            } else {
                                if(element.dealer_chief_note == null || element.dealer_chief_note == undefined || element.dealer_chief_note == "null" || element.dealer_chief_note == "") {
                                    note = "";
                                } else {
                                    note = "Примечание НМС: "+element.dealer_chief_note;
                                }
                                if(element.dealer_calculator_note == null || element.dealer_calculator_note == undefined || element.dealer_calculator_note == "null" || element.dealer_calculator_note == "") {
                                    note2 = "";
                                } else {
                                    note2 = "<br>Примечание замерщика: "+element.dealer_calculator_note;
                                }
                            }
                            if (element.details == 1) {
                                comment_calc = "<br>Есть примечание к потолку";
                            } else {
                                comment_calc = "";
                            }
                            // статусы
                            status = element.project_status;
                            
                            /*if (element.project_status == 5 ) {
                                status = "В производстве";
                            }
                            if (element.project_status == 6 ) {
                                status = "На раскрое";
                            }
                            if (element.project_status == 7 ) {
                                status = "Укомплектован";
                            }
                            if (element.project_status == 8 ) {
                                status = "Выдан";
                            }
                            if (element.project_status == 10 ) {
                                status = "Ожидание монтажа";
                            }
                            if (element.project_status == 12 ) {
                                status = "Заказ закрыт";
                            }
                            if (element.project_status == 16 ) {
                                status = "Монтаж";
                            }
                            if()
                            if (element.project_status == 11 ) {
                                status = "Монтаж выполнен";
                            }
                            if (element.project_status == 17 ) {
                                status = "Монтаж недовыполнен";
                            }*/
                            if (element.read_by_mounter == 0) {
                                status += " <strong>/ Не прочитан</strong>";
                            }
                            salary = element.mounting_sum;
                            if (salary < 1500) {
                                salary = 1500;
                            }

                            switch (element.type) {
                                case '1': type = 'Полный монт.';
                                break;
                                case '2': type = 'Обагечивание';
                                break;
                                case '3': type = 'Натяжка';
                                break;
                                case '4': type = 'Вставка';
                                break;
                            }
                            // рисовка таблицы
                            TrOrders2 = `<tr class="clickabel" onclick="ReplaceToOrder(${element.id}, tm, ${element.read_by_mounter}, ${element.type});"><td>${element.project_mounting_date}</td><td>${adress}</td><td>${perimeter}</td><td>${salary}</td><td id="comment_calc${element.id}">${note}${note2}${comment_calc}</td><td>${status}</td><td>${type}</td></tr>`;
                            jQuery("#table-mounting").append(TrOrders2);
                        } else {
                            TrOrders2 = '<tr><td>'+element.project_mounting_date+'</td><td colspan=5>'+element.project_info+'</td></tr>';
                            jQuery("#table-mounting").append(TrOrders2);
                        }                  
                    });
                }
            });
        }
    }

    function ReplaceToOrder(project, month, ReadOrNot, stage) {
        month--;
        if (ReadOrNot == 0) {
            jQuery.ajax({
                type: "POST",
                url: "index.php?option=com_gm_ceiling&task=mounterscalendar.ChangeStatus",
                dataType: 'json',
                data: {
                    id_calculation: project
                },
                success: function(msg) {
                    if (msg.read_by_mounter == 1) {
                        location.href="/index.php?option=com_gm_ceiling&view=mountersorder&project="+project+"&stage="+stage;
                    }
                },
                error: function(msg) {
                }
            });
        } else {
            location.href="/index.php?option=com_gm_ceiling&view=mountersorder&project="+project+"&stage="+stage;
        }
    }

    jQuery(document).ready(function () {
        // кнопки на телефон маленькие
        if (screen.width < 768) {
            jQuery(".perimeter").empty();
			jQuery("#button-prev").css({"width":"25px"});
			jQuery("#button-next").css({"width":"25px"});
		}
        // --------------------------------
    });

</script>
