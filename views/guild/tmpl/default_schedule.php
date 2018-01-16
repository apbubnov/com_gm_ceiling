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
JHtml::_('formbehavior.chosen', 'select');

$user = JFactory::getUser();
$model = $this->getModel();

$userId = $user->get('id');
$groups = $user->get('groups');

$chief = (in_array(23, $groups));
$employee = (in_array(18, $groups));

//$app = JFactory::getApplication();
$calendars = [];
$calendars[] = Gm_ceilingHelpersGm_ceiling::LiteCalendar(intval(date("m")) - 1);
$calendars[] = Gm_ceilingHelpersGm_ceiling::LiteCalendar(date("m"));
$calendars[] = Gm_ceilingHelpersGm_ceiling::LiteCalendar(intval(date("m")) + 1);

$employees = Gm_ceilingHelpersGm_ceiling::getModel('Guild')->getEmployees();
//$schedule = Gm_ceilingHelpersGm_ceiling::getModel('Guild')->getSchedule();
?>

<? if ($chief || true): ?>
    <h1>Начальник цеха: <?= $user->name; ?></h1>
<? elseif ($employee): ?>
    <h1>Работник цеха: <?= $user->name; ?></h1>
<? else: ?>
    <h1>К сожалению данный кабинет вам не доступен!</h1>
    Что бы получить доступ, обратитесь к IT отделу.
<? endif; ?>

<div class="Calendars">
    <div class="CalName">Календарь работ</div>
    <div class="Block">
        <div class="button Left">
            <i class="fa fa-chevron-circle-left" aria-hidden="true"></i>
        </div>
        <div class="CalBlock">
            <?= $calendars[0]; ?>
            <?= $calendars[1]; ?>
            <?= $calendars[2]; ?>
        </div>
        <div class="button Right">
            <i class="fa fa-chevron-circle-right" aria-hidden="true"></i>
        </div>
    </div>
</div>
<div class="ModalCalendar">
    <div class="Dark" onclick="hideModalCalendar();"></div>
    <div class="ModalDay">
        <div class="Title"></div>
        <div class="Employees"></div>
        <div class="Add">
            <div class="ButtomAdd">
                <i class="fa fa-plus-circle" aria-hidden="true"></i>
            </div>
            <form class="AddEmployeeForm" action="javascript:setWorking();">
                <div class="Line">
                    <div class="Name">Рабочий:</div>
                    <select name="employee">
                        <?foreach ($employees as $employee):?>
                            <option value="<?=$employee->id;?>"><?=$employee->name;?></option>
                        <?endforeach;?>
                    </select>
                </div>
                <div class="Line">
                    <div class="Name">Событие:</div>
                    <select name="action">
                        <option value="1">Пришел</option>
                        <option value="0">Ушел</option>
                    </select>
                </div>
                <div class="Line">
                    <div class="Name">Время:</div>
                    <div class="Time">
                        <select name="hour">
                            <?for($i = 0; $i < 24; $i++): $H = date("H", mktime($i, 0, 0, 1, 1, 1));?>
                                <option value="<?=$H;?>"><?=$H;?></option>
                            <?endfor;?>
                        </select>
                        <select name="minute">
                            <?for($i = 0; $i <= 59; $i+=5): $I = date("i", mktime(0, $i, 0, 1, 1, 1));?>
                                <option value="<?=$I;?>"><?=$I;?></option>
                            <?endfor;?>
                        </select>
                    </div>
                </div>
                <div class="Line">
                    <button type="submit" class="Send"> Добавить </button>
                    <button type="button" class="Cancel"> Отмена </button>
                </div>
            </form>
        </div>
    </div>
</div>

<link type="text/css" rel="stylesheet" href="/components/com_gm_ceiling/views/guild/styles/calendar.css">
<link type="text/css" rel="stylesheet" href="/components/com_gm_ceiling/views/guild/styles/schedule.css">

<script type="text/javascript">
    var $ = jQuery,
        Data = {};

    $(document).ready(Init);

    function Init() {
        Data.Employee = $("<div class=\"Employee\"><div class=\"time\"></div><div class=\"name\"></div></div>");

        $('.chosen-container').remove();
        $('select').removeAttr("style");

        var calendars = $(".Calendars"),
            button = calendars.find(".button");
        button.attr("onclick", "ButtomCalendarClick(this);");

        Data.Modal = $(".ModalCalendar");
        Data.ModalDay = Data.Modal.find(".ModalDay");

        Data.ModalDay.find(".ButtomAdd").attr("onclick", "showAddForm(this);");
        Data.ModalDay.find(".Cancel").attr("onclick", "hideAddForm(this);");

        InitCalendarFunction();
    }

    function InitCalendarFunction() {
        var calendars = $(".Calendars"),
            calendar = $(".Calendar"),
            days = calendar.find(".Day");

        days.attr("onclick", "getWorkingDay(this);");
    }

    function ButtomCalendarClick(element = null) {
        element = $(element);

        var val = ((element.hasClass("Left"))?-1:1),
            calendars = $(".Calendars .Block .CalBlock"),
            calendarFirst = calendars.find(".Calendar:first-child"),
            calendarLast = calendars.find(".Calendar:last-child"),
            month = (parseInt(((val < 0)?calendarFirst:calendarLast).attr("month"))) + val,
            year = parseInt(((val < 0)?calendarFirst:calendarLast).attr("year"));

        jQuery.ajax({
            type: 'POST',
            url: "/index.php?option=com_gm_ceiling&task=guild.getCalendar",
            data: {month: month, year: year},
            cache: false,
            async: false,
            success: function (data) {
                data = JSON.parse(data);

                if (data.status === "success")
                {
                    var newCalendar = $(data.calendar);
                    if (val < 0)
                    {
                        calendars.prepend(newCalendar);
                        calendarLast.remove();
                    }
                    else
                    {
                        calendars.append(newCalendar);
                        calendarFirst.remove();
                    }
                }
            },
            dataType: "text",
            timeout: 15000,
            error: function () {
                noty({
                    theme: 'relax',
                    layout: 'center',
                    timeout: 1500,
                    type: "error",
                    text: "Сервер не отвечает!"
                });
            }
        });

        InitCalendarFunction();
    }

    function getWorkingDay(day) {
        var Modal = $(".ModalCalendar"),
            ModalDay = Modal.find(".ModalDay"),

            Day = $(day),
            Month = Day.closest(".Month");
      
        ModalDay.find(".Title").text(Day.attr("day") + " " + Month.attr("modalname") + " " + Month.attr("year") + "г.");

        var day = Day.attr("day"),
            month = Month.attr("month"),
            year = Month.attr("year"),
            Workings;

        ModalDay.attr({"day":day, "month":month, "year":year, "dayid": "#" + Month.attr("id") + " #" + Day.attr("id")});

        jQuery.ajax({
            type: 'POST',
            url: "/index.php?option=com_gm_ceiling&task=guild.getWorking",
            data: {Day: day, Month: month, Year: year},
            cache: false,
            async: false,
            success: function (data) {
                Workings = JSON.parse(data);
            },
            dataType: "text",
            timeout: 15000,
            error: function () {
                noty({
                    theme: 'relax',
                    layout: 'center',
                    timeout: 1500,
                    type: "error",
                    text: "Сервер не отвечает!"
                });
            }
        });


        $.each(Workings, function (key, value) {
            var Employee = Data.Employee.clone();

            Employee.find(".time").text(value.time);
            Employee.find(".name").text(value.user.name);
            Employee.attr("id", value.id);

            ModalDay.find(".Employees").append(Employee);
        });

        Modal.show();
        ModalDay.show();
    }

    function showAddForm(button) {
        button = $(button);
        var form = button.siblings(".AddEmployeeForm");

        button.hide();
        form.css("display","inline-block");
    }

    function hideAddForm(button = null) {
        if (button !== null) button = $(button);
        else button = Data.ModalDay.find(".Cancel");

        var form = button.closest(".AddEmployeeForm")
        button = form.siblings(".ButtomAdd");

        button.css("display","inline-block");
        form.hide();
    }

    function hideModalCalendar() {
        Data.Modal.hide();

        Data.ModalDay.hide();
        Data.ModalDay.find(".ButtomAdd").css("display","inline-block");
        Data.ModalDay.find(".AddEmployeeForm").hide();
    }

    function setWorking() {
        var Form = Data.ModalDay.find(".AddEmployeeForm"),
            user_id = Form.find("[name='employee']").val(),
            action = Form.find("[name='action]"),
            hour = Form.find("[name='hour']"),
            minute = Form.find("[name='minute']"),
            day = Data.ModalDay.attr("day"),
            month = Data.ModalDay.attr("month"),
            year = Data.ModalDay.attr("year");

        jQuery.ajax({
            type: 'POST',
            url: "/index.php?option=com_gm_ceiling&task=guild.setWorking",
            data: {user_id: user_id, date: "${year}.${month}.${day} ${hour}:${minute}:00", action: action},
            cache: false,
            async: false,
            success: function (data) {
                data = JSON.parse(data);

                noty({
                    theme: 'relax',
                    layout: 'center',
                    timeout: 1500,
                    type: data.status,
                    text: data.message
                });
            },
            dataType: "text",
            timeout: 15000,
            error: function () {
                noty({
                    theme: 'relax',
                    layout: 'center',
                    timeout: 1500,
                    type: "error",
                    text: "Сервер не отвечает!"
                });
            }
        });


        getWorkingDay(Data.ModalDay.attr("dayid"));
        hideAddForm();
    }

</script>