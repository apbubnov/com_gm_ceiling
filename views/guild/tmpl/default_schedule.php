<?php
echo parent::getPreloaderNotJS();
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
?>
<link type="text/css" rel="stylesheet" href="/components/com_gm_ceiling/views/guild/styles/calendar.css">
<link type="text/css" rel="stylesheet" href="/components/com_gm_ceiling/views/guild/styles/schedule.css">

<?if (!($chief || $employee)): ?>
    <h1>К сожалению данный кабинет вам не доступен!</h1>
    <p>Что бы получить доступ, обратитесь к IT отделу. Через <span>5</span> секунды вы вернетесь на предыдущую страницу!</p>
    <div style="display: none;"><?=parent::getButtonBack(); ?></div>
    <script type="text/javascript">
        var $ = jQuery;
        $(function () {
            $(".PRELOADER_GM").hide();
            setTimeout(function () {
                $("#BackPage").click();
            }, 5000);
            setInterval(function () {
                var span = $("p span"),
                    text = span.text();
                span.text(parseInt(text) - 1);
            }, 1000);
        });
    </script>
<?else:?>

<h1><?=($chief)?"Начальник":"Работник";?> цеха: <?= $user->name; ?></h1>
<div class="Actions">
    <?=parent::getButtonBack(); ?>
</div>
<div class="Page">
    <div class="Main MainCalendar">
        <div class="MainName">Календарь работ</div>
        <div class="MainBlock">
            <div class="Arrow Left">
                <i class="fa fa-chevron-circle-left" aria-hidden="true"></i>
            </div>
            <div class="Calendars"></div>
            <div class="Arrow Right">
                <i class="fa fa-chevron-circle-right" aria-hidden="true"></i>
            </div>
        </div>
    </div>
    <div class="Main MainWork">
        <div class="MainName">Работы за <span class="Date"></span> г.</div>
        <div class="MainBlock"></div>
    </div>
</div>
<div class="Modal">
    <div class="ModalDark" onclick="DATA.MODAL.MODAL.hide();"></div>
    <div class="ModalDay">
        <div class="Title"></div>
        <div class="ActionsDay">

        </div>
        <div class="Schedule"></div>
    </div>
</div>

<script type="text/javascript">
    var $ = jQuery,
        DATA = {};

    $(document).ready(Init);
    $(window).resize(Resize);


    function Init() {
        DATA.PRELOADER = $(".PRELOADER_GM");

        DATA.MODAL = {};
        DATA.MODAL.MODAL = $(".Modal");
        DATA.MODAL.ModalDay = $(".Modal .ModalDay");
        DATA.MODAL.ModalSchedule = $(".Modal .ModalDay .Schedule");

        DATA.MODAL.MODAL.show = modalShow;
        DATA.MODAL.MODAL.hide = modalHide;
        DATA.MODAL.ModalDay.show = modalShow;

        DATA.HTML = {};
        DATA.HTML.Employee = $("<div class=\"Employee\"><div class=\"time\"></div><div class=\"name\"></div></div>");

        DATA.DATE = {};
        DATA.DATE.DAY = "<?=date("d");?>";
        DATA.DATE.MONTH = "<?=date("m");?>";
        DATA.DATE.YEAR = "<?=date("Y");?>";
        DATA.DATE.DATE = "<?=date("Y-m-d H:i:s");?>";

        DATA.MainCalendar = $(".Page .Main.MainCalendar");
        DATA.MainWork = $(".Page .Main.MainWork");

        DATA.CALENDARS = $(".Page .Main .MainBlock .Calendars");

        DATA.CALENDARS.siblings(".Arrow").attr({"onclick":"getNextCalendar(this);"});

        $('.chosen-container').remove();
        $('select').removeAttr("style");

        for (var i = -1; i < 2; ++i)
            getCalendar({MONTH: parseInt(DATA.DATE.MONTH) + i, YEAR: DATA.DATE.YEAR});

        selectDay(DATA.DATE);

        DATA.PRELOADER.hide();
    }

    function Resize() {

    }

    function getCalendar(Date) {
        var MONTH = Date.MONTH,
            YEAR = Date.YEAR;

        jQuery.ajax({
            type: 'POST',
            url: "/index.php?option=com_gm_ceiling&task=guild.getCalendar",
            data: {month: MONTH, year: YEAR},
            cache: false,
            async: false,
            success: function (data) {
                data = JSON.parse(data);

                if (data.status === "success")
                {
                    data.calendar = $(data.calendar);

                    data.calendar.find(".IssetDay").click(getDay);

                    if (DATA.CALENDARS.find(".Calendar").length < 3)
                        DATA.CALENDARS.append(data.calendar);
                    else
                    {
                        var First = DATA.CALENDARS.find(".Calendar").filter(":first-child"),
                            Last = DATA.CALENDARS.find(".Calendar").filter(":last-child"),
                            Month = First.attr("month"),
                            Year = First.attr("year"),
                            Logic1 = (parseInt(YEAR) > parseInt(Year)),
                            Logic2 = (parseInt(YEAR) === parseInt(Year)),
                            Logic3 = (parseInt(MONTH) > parseInt(Month));

                        if (Logic1 || (Logic2 && Logic3))
                        {
                            DATA.CALENDARS.append(data.calendar);
                            First.remove();
                        }
                        else
                        {
                            DATA.CALENDARS.prepend(data.calendar);
                            Last.remove();
                        }
                    }
                }
                else
                {
                    noty({
                        theme: 'relax',
                        layout: 'center',
                        timeout: 1500,
                        type: "error",
                        text: "Что то пошло не так! Попробуйте снова!"
                    });
                }
            },
            dataType: "text",
            timeout: 1000,
            error: function () {
                noty({
                    theme: 'relax',
                    layout: 'center',
                    timeout: 1500,
                    type: "error",
                    text: "Сервер не отвечает! Попробуйте снова!"
                });
            }
        });
    }
    function getNextCalendar(value) {
        if (typeof value === "object")
            value = ($(value).hasClass("Left"))?-1:1;

        var First = DATA.CALENDARS.find(".Calendar").filter(":first-child"),
            Last = DATA.CALENDARS.find(".Calendar").filter(":last-child"),
            Month = (value < 0)
                ?parseInt(First.attr("month")) - 1
                :parseInt(Last.attr("month")) + 1,
            Year = (value < 0)?First.attr("year"):Last.attr("year");

        getCalendar({MONTH: Month, YEAR: Year});
    }
    function selectDay(Date) {
        var Month = DATA.CALENDARS.find("[month='"+Date.MONTH+"']"),
            Day = Month.find("[day='"+Date.DAY+"']");

        DATA.MainWork.find(".MainName .Date")
            .text(Date.DAY + " " + Month.attr("modalname") + " " + Month.attr("year"));
    }
    function getDay() {
        var _this = $(this),
            month = _this.closest(".Month"),
            Day = _this.attr("day"),
            Month = month.attr("month"),
            Year = month.attr("year");

        showModalDay({DAY: Day, MONTH: Month, YEAR: Year});
    }
    function showModalDay(Date = null) {
        if (Date === null)
            Date = DATA.DATE;

        var DAY = Date.DAY,
            MONTH = Date.MONTH,
            YEAR = Date.YEAR,

            Month = DATA.CALENDARS.find("[month='"+MONTH+"']"),
            Day = Month.find("[day='"+DAY+"']"),
            Workings = null;

        jQuery.ajax({
            type: 'POST',
            url: "/index.php?option=com_gm_ceiling&task=guild.getData",
            data: {Day: DAY, Month: MONTH, Year: YEAR, Type: "Working"},
            cache: false,
            async: false,
            success: function (data) {
                Workings = JSON.parse(data);
            },
            dataType: "text",
            timeout: 5000,
            error: function () {
                noty({
                    theme: 'relax',
                    layout: 'center',
                    timeout: 1500,
                    type: "error",
                    text: "Сервер не отвечает! Попробуйте позже!"
                });
            }
        });

        DATA.MODAL.ModalDay.find(".Title")
            .text(DAY + " " + Month.attr("modalname") + " " + YEAR + " г.");

        DATA.MODAL.ModalSchedule.empty();
        $.each(Workings, function (key, value) {
            var Employee = DATA.HTML.Employee.clone();

            Employee.find(".time").text(value.time);
            Employee.find(".name").text(value.user.name);
            Employee.attr("id", value.id);
            if (value.action === "0")
                Employee.addClass("Out"); else Employee.addClass("In");

            DATA.MODAL.ModalSchedule.prepend(Employee);
        });

        DATA.MODAL.MODAL.show();
        DATA.MODAL.ModalDay.show();
    }

    function modalShow() {
        var _this = $(this);

        if (_this.hasClass("Modal"))
        {
            $("body").css({"overflow":"hidden"});
            _this.css({"display":"inline-block"});
        }
        else
        {
            _this.css({"display":"inline-block"});
        }
    }
    function modalHide() {
        var _this = $(this);

        if (_this.hasClass("Modal"))
        {
            $("body").css({"overflow":"auto"});
            _this.css({"display":"none"});
            _this.find("> div:not(.ModalDark)").hide();
        }
        else
        {
            _this.css({"display":"none"});
        }
    }
</script>

<?if(false):?>

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
<div class="InfoBlock" day="<?=date("d");?>" month="<?=date("m");?>" year="<?=date("Y");?>">
    <div class="InfoBName">Подробная информация - <span class="InfoBDate"><?=date("d");?>.<?=date("m");?>.<?=date("Y");?></span></div>
    <div class="Block">
        <div class="Schedule">

        </div>
        <div class="BigData">
            <div class="Employee">
                <div class="EmployeeName">Петя</div>
                <div class="EmployeeTime">
                    <div class="TimeName">Время работы ~ 8 часов</div>
                    <div class="TimeList">
                        <div class="TimeItem">11:30-19:30</div>
                    </div>
                </div>
                <div class="EmployeeSalaries">
                    <div class="SalariesName">Заработок за день ~ 80000 руб.</div>
                    <div class="SalariesList">
                        <div class="SalariesItem">Охуенный потолок 25000 руб.</div>
                        <div class="SalariesItem">Хуевый потолок 55000 руб.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="ModalCalendar">
    <div class="Dark" onclick="hideModalCalendar();"></div>
    <div class="ModalDay">
        <div class="Title"></div>
        <div class="Actions">
            <button type="button" class="ShowBigInfo" onclick="setBigDataEmployees();">
                <i class="fa fa-info-circle" aria-hidden="true"></i> Подробнее
            </button>
        </div>
        <div class="Add">
            <div class="ButtomAdd">
                <i class="fa fa-plus-circle" aria-hidden="true"></i>  Добавить
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
        <div class="Employees"></div>
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
        getBigDataEmployees();
    }

    function getBigDataEmployees() {
        var InfoBlock = $(".InfoBlock"),
            Schedule = InfoBlock.find(".Schedule"),
            day = InfoBlock.attr("day"),
            month = InfoBlock.attr("month"),
            year = InfoBlock.attr("year");

        jQuery.ajax({
            type: 'POST',
            url: "/index.php?option=com_gm_ceiling&task=guild.getData",
            data: {Day: day, Month: month, Year: year, Type: ["Employee", "Working"]},
            cache: false,
            async: false,
            success: function (data) {
                data = JSON.parse(data);

                Schedule.empty();
                $.each(data.Working, function (key, value) {
                    var Employee = Data.Employee.clone();

                    Employee.find(".time").text(value.time);
                    Employee.find(".name").text(value.user.name);
                    Employee.attr("id", value.id);
                    if (value.action === "0") Employee.addClass("Out"); else Employee.addClass("In");

                    Schedule.prepend(Employee);
                });

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

        hideModalCalendar();
    }

    function setBigDataEmployees() {
        Data.InfoBlock = $(".InfoBlock");

        var day = Data.ModalDay.attr("day"),
            month = Data.ModalDay.attr("month"),
            year = Data.ModalDay.attr("year");

        Data.InfoBlock.attr({"day":day, "month":month, "year":year});
        Data.InfoBlock.find(".InfoBDate").text(day+"."+month+"."+year);
        getBigDataEmployees();
    }

    function InitCalendarFunction() {
        var calendars = $(".Calendars"),
            calendar = $(".Calendar"),
            days = calendar.find(".Day.IssetDay");

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
            url: "/index.php?option=com_gm_ceiling&task=guild.getData",
            data: {Day: day, Month: month, Year: year, Type: "Working"},
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

        ModalDay.find(".Employees").empty();
        $.each(Workings, function (key, value) {
            var Employee = Data.Employee.clone();

            Employee.find(".time").text(value.time);
            Employee.find(".name").text(value.user.name);
            Employee.attr("id", value.id);
            if (value.action === "0") Employee.addClass("Out"); else Employee.addClass("In");

            ModalDay.find(".Employees").prepend(Employee);
        });

        Modal.show();
        ModalDay.show();

        UpdateModalModalTop();
    }

    function UpdateModalModalTop() {
        var Modal = $(".ModalCalendar"),
            ModalDay = Modal.find(".ModalDay");

        var ModalDayHeight = ModalDay.height(),
            ModalHeight = Modal.height(),
            Top = (ModalHeight - ModalDayHeight) / 2;
        Top = (Top/3*2 < 0)?0:Top/3*2;

        ModalDay.css("top", Top + "px");
    }

    function showAddForm(button) {
        button = $(button);
        var form = button.siblings(".AddEmployeeForm");

        button.hide();
        form.css("display","inline-block");

        UpdateModalModalTop();
    }

    function hideAddForm(button = null) {
        if (button !== null) button = $(button);
        else button = Data.ModalDay.find(".Cancel");

        var form = button.closest(".AddEmployeeForm")
        button = form.siblings(".ButtomAdd");

        button.css("display","inline-block");
        form.hide();

        UpdateModalModalTop();
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
            action = Form.find("[name='action']").val(),
            hour = Form.find("[name='hour']").val(),
            minute = Form.find("[name='minute']").val(),
            day = Data.ModalDay.attr("day"),
            month = Data.ModalDay.attr("month"),
            year = Data.ModalDay.attr("year"),
            status = null;

        jQuery.ajax({
            type: 'POST',
            url: "/index.php?option=com_gm_ceiling&task=guild.setWorking",
            data: {user_id: user_id, date: year+"-"+month+"-"+day+" "+hour+":"+minute+":00", action: action},
            cache: false,
            async: false,
            success: function (data) {
                data = JSON.parse(data);
                status = data.status;

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
        if (status !== "error") hideAddForm();
    }

</script>
        <?endif;?>
<?endif;?>