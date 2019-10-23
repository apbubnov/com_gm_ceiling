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
    <div id="calendar-container" class="row center" style="padding-bottom: 10px">
        <?php echo $calendar; ?>
    </div>
    <div class="col-md-6 col-sm-6 col-xs-6">
        <div class="row">
            <div class="col-md-6">
                <div class="col-md-4">
                    <img class="legenda_modal" src="components/com_gm_ceiling/views/mounterscalendar/tmpl/images/ff3d3d.png" alt="Красный">
                </div>
                <div class="col-md-8 legend_text">Не просмотреный монтаж</div>
            </div>
            <div class="col-md-6">
                <div class="col-md-4">
                    <img class="legenda_modal" src="components/com_gm_ceiling/views/mounterscalendar/tmpl/images/fff23d.png" alt="Желтый">
                </div>
                <div class="col-md-8 legend_text">Просмотреный монтаж</div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="col-md-4">
                    <img class="legenda_modal" src="components/com_gm_ceiling/views/mounterscalendar/tmpl/images/1ffe4e.png" alt="Зеленый">
                </div>
                <div class="col-md-8 legend_text" legend_tezt>Монтаж выполнен</div>
            </div>
            <div class="col-md-6">
                <div class="col-md-4">
                    <img class="legenda_modal" src="components/com_gm_ceiling/views/mounterscalendar/tmpl/images/d3d3f9.png" alt="Голубой">
                </div>
                <div class="col-md-8 legend_text">Выходные часы</div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-sm-6 col-xs-6">
        <div class="row">
            <div class="col-md-6">
                <div class="col-md-4">
                    <img class="legenda_modal" src="components/com_gm_ceiling/views/mounterscalendar/tmpl/images/414099.png" alt="Синий">
                </div>
                <div  class="col-md-8 legend_text">Монтаж в работе</div>
            </div>
            <div class="col-md-6">
                <div class="col-md-4">
                    <img class="legenda_modal" src="components/com_gm_ceiling/views/mounterscalendar/tmpl/images/461f08.png" alt="Коричневый">
                </div>
                <div class="col-md-8 legend_text">Монтаж недовыполнен</div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="col-md-4">
                    <img class="legenda_modal" src="components/com_gm_ceiling/views/mounterscalendar/tmpl/images/9e9e9e.png" alt="Серый">
                </div>
                <div  class="col-md-8 legend_text">Заказ закрыт</div>
            </div>
        </div>
    </div>


    <div class="modal_window_container" id="mw_container">
        <button type="button" class="close_btn" id="close_mw"><i class="fa fa-times fa-times-tar" aria-hidden="true"></i></button>

        <div class="modal_window" id="window-with-table">
            <table id="table-mounting" class="rwd-table">
                <thead>
                <tr>
                    <th id="selected_data" colspan="8"></th>
                </tr>
                <tr id="caption-tr" style="font-size: 9pt;">
                    <th>Время</th>
                    <th>Адрес</th>
                    <th>Телефоны</th>
                    <th>Периметр</th>
                    <th>З/П</th>
                    <th>Примечание</th>
                    <th>Статус</th>
                    <th>Этап</th>
                </tr>
                </thead>
                <tbody>

                </tbody>
            </table>
        </div>
    </div>
</div>

<script type='text/javascript'>
    var notes;
    // листание календаря
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
                jQuery("#prev").click(function () {
                    scrollCalendar(0);
                });
                jQuery("#next").click(function () {
                    scrollCalendar(1);
                });
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
            jQuery("#close_mw").hide();
            jQuery("#mw_container").hide();
        }
    });

    // открытие модального окна и узнаем какой день нажат
    jQuery("#calendar-container").on("click", ".current-month, .day-off2", function () {
        console.log(1);
        kind = "empty";
        var id = jQuery(this).attr("id");
        WhatDay(id);
        ListOfWork(kind, ChooseDay, ChooseMonth, ChooseYear);
        jQuery("#window-with-table").show('slow');
        jQuery("#close_mw").show();
        jQuery("#mw_container").show();
    });
    jQuery("#calendar-container").on("click", ".day-not-read, .day-read, .day-in-work, .day-underfulfilled, .day-complite, .old-project", function () {
        console.log(2);
        kind = "no-empty";
        id = jQuery(this).attr("id");
        WhatDay(id);
        ListOfWork(kind, ChooseDay, ChooseMonth, ChooseYear);
        jQuery("#window-with-table").show('slow');
        jQuery("#close_mw").show();
        jQuery("#mw_container").show();
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
        var outputDate = day+"."+month+"."+year;
        jQuery("#table-mounting > tbody").empty();
        jQuery("#selected_data").text(outputDate);
        if (kind == "empty") {
            jQuery("#caption-tr").hide();
            TrOrders = '<tr><td data-th="монтажей нет" colspan=8>В данный момент на этот день монтажей нет</td></tr>';
            jQuery.ajax({
                type: 'POST',
                url: "/index.php?option=com_gm_ceiling&task=mounterscalendar.GetDataOfMounting",
                dataType: 'json',
                data: {
                    date: date,
                    id: <?php echo $userId?>,
                },
                success: function(data) {
                    console.log(data);
                    if (data.length > 0) {
                        Array.from(data).forEach(function(element) {
                            TrOrders += '<tr><td data-th="дата монтажа" style="width: 25%;">'+element.project_mounting_date+'</td><td data-th="адрес" style="width: 75%;">'+element.project_info+'</td></tr>';
                        });
                    }
                    jQuery("#table-mounting > tbody").append(TrOrders);
                },
                error: function(msg) {
                    console.log(msg);
                    var n = noty({
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "error",
                        text: "Ошибка!"
                    });
                }
            });
        } else if (kind == "no-empty") {
            jQuery("#caption-tr").show();
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
                    var status, type = "", note = "", comment_calc, project, adress, perimeter;
                    msg.forEach(function(element) {
                        if (element.project_mounting_date.length < 6) {
                            project = element.id;
                            adress = element.project_info;
                            perimeter = element.n5;
                            getProjectNotes(project);
                            // комменты
                            jQuery.each(notes,function (index,elem) {
                                if(index.indexOf("mount") >=0){
                                    note += elem.description + elem.value+"<br>";
                                }
                                if(index.indexOf("common") >=0){
                                    note += elem.description + elem.value+"<br>";
                                }
                            });


                            // статусы
                            status = element.project_status;

                            if (element.read_by_mounter == 0) {
                                status += " <strong>/ Не прочитан</strong>";
                            }
                            salary = element.m_sum;

                            for(var k=0;k<element.type.length;k++){
                                switch (element.type[k]) {
                                    case '1': type += 'Полный монт.\n';
                                        break;
                                    case '2': type += 'Обагечивание\n';
                                        break;
                                    case '3': type += 'Натяжка\n';
                                        break;
                                    case '4': type += 'Вставка\n';
                                        break;
                                }
                            }
                            var typeParam;
                            if(Array.isArray(element.type)){
                                typeParam = element.type.join('_');
                            }
                            else{
                                typeParam = element.type;
                            }
                            console.log(typeParam);
                            // рисовка таблицы
                            TrOrders2 = '<tr class="clickabel" onclick="ReplaceToOrder('+element.id+', tm, '+element.read_by_mounter+',\''+typeParam+'\');"><td data-th="Дата мотажа">'+outputDate+' '+element.project_mounting_date+'</td><td data-th="Адрес">'+adress+'</td><td data-th="Телефоны"><a href="tel:+'+element.client_phones+'">'+element.client_phones+'</a></td><td data-th="Периметр">'+perimeter+'</td><td data-th="Зарплата">'+salary+'</td><td data-th="Комментарий" id="comment_calc'+element.id+'">'+note+'</td><td data-th="Статус">'+status+'</td><td data-th="Этап">'+type+'</td></tr>';
                            jQuery("#table-mounting > tbody").append(TrOrders2);
                        } else {
                            TrOrders2 = '<tr><td>'+element.project_mounting_date+'</td><td colspan=5>'+element.project_info+'</td></tr>';
                            jQuery("#table-mounting > tbody").append(TrOrders2);
                        }
                    });
                },
                error: function(msg) {
                    console.log(msg);
                    var n = noty({
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "error",
                        text: "Ошибка!"
                    });
                }
            });
        }
    }
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
    function ReplaceToOrder(project, month, ReadOrNot, stage) {
        month--;
        console.log(project);
        console.log(stage);
        stage = stage.split('_');
        if (ReadOrNot == 0) {
            jQuery.ajax({
                type: "POST",
                url: "index.php?option=com_gm_ceiling&task=mounterscalendar.ChangeStatus",
                dataType: 'json',
                data: {
                    id_calculation: project
                },
                success: function(msg) {
                    if (msg[0].read_by_mounter == 1) {
                        location.href="/index.php?option=com_gm_ceiling&view=mountersorder&project="+project+"&stage="+JSON.stringify(stage);
                    }
                },
                error: function(msg) {
                }
            });
        } else {
            location.href="/index.php?option=com_gm_ceiling&view=mountersorder&project="+project+"&stage="+JSON.stringify(stage);
        }
    }
    /*
    *type == 0 листание назад
    *type == 1 листание вперед
    */
    function scrollCalendar(type){
        month = <?php echo $month; ?>;
        year = <?php echo $year; ?>;
        if (month_old != 0) {
            month = month_old;
            year = year_old;
            month = month_old;
            year = year_old;
        }
        if(type == 0) {
            if (month == 1) {
                month = 12;
                year--;
            } else {
                month--;
            }
        }
        if(type == 1){
            if (month == 12) {
                month = 1;
                year++;
            } else {
                month++;
            }
        }
        month_old = month;
        year_old = year;
        update_calendar(month, year);
    }
    jQuery(document).ready(function () {
        month_old = 0;
        year_old = 0;
        jQuery("#prev").click(function () {
            scrollCalendar(0);
        });
        jQuery("#next").click(function () {
            scrollCalendar(1);
        });
    });

</script>
