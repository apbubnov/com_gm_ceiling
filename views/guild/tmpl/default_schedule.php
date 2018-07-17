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

$employees = Gm_ceilingHelpersGm_ceiling::getModel('Guild')->getEmployees();
?>
    <link type="text/css" rel="stylesheet" href="/components/com_gm_ceiling/views/guild/styles/calendar.css">
    <link type="text/css" rel="stylesheet" href="/components/com_gm_ceiling/views/guild/styles/schedule.css">

<? if (!$chief): ?>
    <h1>К сожалению данный кабинет вам не доступен!</h1>
    <p>Что бы получить доступ, обратитесь к IT отделу. Через <span>5</span> секунды вы вернетесь на предыдущую страницу!
    </p>
    <div style="display: none;"><?= parent::getButtonBack(); ?></div>
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
<? else: ?>

    <h1><?= ($chief) ? "Начальник" : "Работник"; ?> цеха: <?= $user->name; ?></h1>
    <div class="Actions">
        <?= parent::getButtonBack(); ?>
    </div>
    <div class="Page">
        <div class="Main MainCalendar" id="MainCalendar">
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
        <div class="Main MainWork" id="MainWork">
            <div class="MainName">
                Работы за <span class="Date"></span> г.
                <button type="button" class="Update" onclick="selectDay();">
                    <i class="fa fa-refresh" aria-hidden="true"></i>
                </button>
            </div>
            <div class="MainBlock">
                <div class="Employee">
                    <div class="Name">
                        <span class="EName"></span> -
                        <span class="Hour"></span> ч. -
                        <span class="Salaries"></span> р.
                    </div>
                    <div class="Info">
                        <div class="PreList">
                        <table class="List Schedule">
                            <thead>
                            <tr>
                                <td>Время работы</td>
                            </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                        </div>
                        <div class="PreList">
                        <table class="List Working">
                            <thead>
                            <tr>
                                <td>Время</td>
                                <td>Работа</td>
                                <td>Потолок</td>
                                <td>Цена</td>
                            </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="Modal">
        <div class="ModalDark" onclick="DATA.MODAL.MODAL.hide();"></div>
        <div class="ModalDay ModalBlock">
            <div class="Title"></div>
            <div class="ActionsDay">
                <button type="button" class="showInfoDay" onclick="showInfoDay();">
                    <i class="fa fa-info-circle" aria-hidden="true"></i> Подробнее
                </button>
                <div class="Action">
                    <button type="button" class="showAddEmployee" onclick="showAddEmployee();">
                        <i class="fa fa-plus-circle" aria-hidden="true"></i> Добавить
                </div>
                <form class="AddEmployeeForm" action="javascript:setWorking();">
                    <div class="Line">
                        <div class="Name">Рабочий:</div>
                        <select name="employee">
                            <? foreach ($employees as $employee): ?>
                                <option value="<?= $employee->id; ?>"><?= $employee->name; ?></option>
                            <? endforeach; ?>
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
                                <? for ($i = 0; $i < 24; $i++): $H = date("H", mktime($i, 0, 0, 1, 1, 1)); ?>
                                    <option value="<?= $H; ?>"><?= $H; ?></option>
                                <? endfor; ?>
                            </select>
                            <select name="minute">
                                <? for ($i = 0; $i <= 59; $i += 5): $I = date("i", mktime(0, $i, 0, 1, 1, 1)); ?>
                                    <option value="<?= $I; ?>"><?= $I; ?></option>
                                <? endfor; ?>
                            </select>
                        </div>
                    </div>
                    <div class="Line">
                        <button type="submit" class="Send"> Добавить</button>
                        <button type="button" class="Cancel" onclick="hideAddEmployee();"> Отмена</button>
                    </div>
                </form>
            </div>
            <div class="Schedule"></div>
        </div>
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

            DATA.MainCalendar = $(".Page .Main.MainCalendar");
            DATA.MainWork = $(".Page .Main.MainWork");
            DATA.MainWork.MainBlock = DATA.MainWork.find(".MainBlock");

            DATA.MODAL.MODAL.show = modalShow;
            DATA.MODAL.MODAL.hide = modalHide;
            DATA.MODAL.ModalDay.show = modalShow;

            DATA.HTML = {};
            DATA.HTML.Employee = $("<div class=\"Employee\"><span class='Time'></span><span class='Name'></span></div>");

            DATA.HTML.BigEmployee = DATA.MainWork.find(".Employee").clone();
            DATA.MainWork.find(".Employee").remove();
            DATA.HTML.Time = $("<tr><td class='Line'><span class='Time'></span><span class='Hour'></span></td></tr>");
            DATA.HTML.Salaries = $("<tr><td class=\"Time\"></td><td class=\"Work\"></td><td class=\"Name\"></td><td class=\"Price\"></td></tr>");

            DATA.DATE = {};
            DATA.DATE.DAY = "<?=date("d");?>";
            DATA.DATE.MONTH = "<?=date("m");?>";
            DATA.DATE.YEAR = "<?=date("Y");?>";
            DATA.DATE.DATE = "<?=date("Y-m-d H:i:s");?>";

            DATA.CALENDARS = $(".Page .Main .MainBlock .Calendars");

            DATA.CALENDARS.siblings(".Arrow").attr({"onclick": "getNextCalendar(this);"});

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

                    if (data.status === "success") {
                        data.calendar = $(data.calendar);

                        data.calendar.find(".IssetDay").click(getDay);

                        if (DATA.CALENDARS.find(".Calendar").length < 3)
                            DATA.CALENDARS.append(data.calendar);
                        else {
                            var First = DATA.CALENDARS.find(".Calendar").filter(":first-child"),
                                Last = DATA.CALENDARS.find(".Calendar").filter(":last-child"),
                                Month = First.attr("month"),
                                Year = First.attr("year"),
                                Logic1 = (parseInt(YEAR) > parseInt(Year)),
                                Logic2 = (parseInt(YEAR) === parseInt(Year)),
                                Logic3 = (parseInt(MONTH) > parseInt(Month));

                            if (Logic1 || (Logic2 && Logic3)) {
                                DATA.CALENDARS.append(data.calendar);
                                First.remove();
                            }
                            else {
                                DATA.CALENDARS.prepend(data.calendar);
                                Last.remove();
                            }
                        }
                    }
                    else {
                        noty({
                            theme: 'relax',
                            layout: 'center',
                            timeout: 5000,
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
                        timeout: 5000,
                        type: "error",
                        text: "Сервер не отвечает! Попробуйте снова!"
                    });
                }
            });
        }

        function getNextCalendar(value) {
            if (typeof value === "object")
                value = ($(value).hasClass("Left")) ? -1 : 1;

            var First = DATA.CALENDARS.find(".Calendar").filter(":first-child"),
                Last = DATA.CALENDARS.find(".Calendar").filter(":last-child"),
                Month = (value < 0)
                    ? parseInt(First.attr("month")) - 1
                    : parseInt(Last.attr("month")) + 1,
                Year = (value < 0) ? First.attr("year") : Last.attr("year");

            getCalendar({MONTH: Month, YEAR: Year});
        }

        function selectDay(Date = null) {
            if (Date === null) {
                Date = {};
                Date.DAY = DATA.MainWork.attr("day");
                Date.MONTH = DATA.MainWork.attr("month");
                Date.YEAR = DATA.MainWork.attr("year");
            }

            var Month = DATA.CALENDARS.find("[month='" + Date.MONTH + "']"),
                Day = Month.find("[day='" + Date.DAY + "']"),
                Info = null;

            DATA.MainWork.attr({"day": Date.DAY, "month": Date.MONTH, "year": Date.YEAR});

            jQuery.ajax({
                type: 'POST',
                url: "/index.php?option=com_gm_ceiling&task=guild.getData",
                data: {Day: Date.DAY, Month: Date.MONTH, Year: Date.YEAR, Type: "Employee"},
                cache: false,
                async: false,
                success: function (data) {
                    Info = JSON.parse(data);
                },
                dataType: "text",
                timeout: 15000,
                error: function () {
                    noty({
                        theme: 'relax',
                        layout: 'center',
                        timeout: 5000,
                        type: "error",
                        text: "Сервер не отвечает!"
                    });
                }
            });

            DATA.MainWork.MainBlock.empty();
            $.each(Info, function (Index, Value) {
                var Block = DATA.HTML.BigEmployee.clone(),
                    Schedule = Block.find(".Schedule tbody"),
                    Working = Block.find(".Working tbody");

                Block.addClass((Value.Work === 1)?"In":"Out");
                var Title = Block.find(".Name");

                Title.find(".EName").text(Value.name);
                Title.find(".Hour").text(Value.Working.Time);
                Title.find(".Salaries").text(Value.Salaries.Price);

                $.each(Value.Working.Times, function (I, V) {
                    var Time = DATA.HTML.Time.clone();
                    Time.find(".Time").text(V.TimeLine);
                    Time.find(".Hour").text(V.Time + " ч.");
                    Schedule.append(Time);
                });

                $.each(Value.Salaries.List, function (I, V) {
                    var Salar = DATA.HTML.Salaries.clone();
                    Salar.find(".Time").text(V.Time);
                    Salar.find(".Work").text(V.Work);
                    Salar.find(".Name").text(V.Ceiling);
                    Salar.find(".Price").text(V.Price);
                    Working.append(Salar);
                });

                DATA.MainWork.MainBlock.append(Block);
            });

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

        function showInfoDay() {
            var DAY = DATA.MODAL.ModalDay.attr("day"),
                MONTH = DATA.MODAL.ModalDay.attr("month"),
                YEAR = DATA.MODAL.ModalDay.attr("year");
            selectDay({DAY: DAY, MONTH: MONTH, YEAR: YEAR});
            DATA.MODAL.MODAL.hide();
            window.location.hash = 'MainWork';
        }

        function showModalDay(Date = null) {
            if (Date === null)
                Date = DATA.DATE;

            var DAY = Date.DAY,
                MONTH = Date.MONTH,
                YEAR = Date.YEAR,

                Month = DATA.CALENDARS.find("[month='" + MONTH + "']"),
                Day = Month.find("[day='" + DAY + "']"),
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
                        timeout: 5000,
                        type: "error",
                        text: "Сервер не отвечает! Попробуйте позже!"
                    });
                }
            });

            DATA.MODAL.ModalDay
                .attr({"day": DAY, "month": MONTH, "year": YEAR});

            DATA.MODAL.ModalDay.find(".Title")
                .text(DAY + " " + Month.attr("modalname") + " " + YEAR + " г.");

            DATA.MODAL.ModalSchedule.empty();
            $.each(Workings, function (key, value) {
                var Employee = DATA.HTML.Employee.clone();

                Employee.find(".Time").text(value.time);
                Employee.find(".Name").text(value.user.name);
                Employee.attr("id", value.id);
                if (value.action === "0")
                    Employee.addClass("Out"); else Employee.addClass("In");

                DATA.MODAL.ModalSchedule.prepend(Employee);
            });

            DATA.MODAL.MODAL.show();
            DATA.MODAL.ModalDay.show();
        }

        function showAddEmployee() {
            var B = DATA.MODAL.ModalDay.find(".showAddEmployee"),
                F = DATA.MODAL.ModalDay.find(".AddEmployeeForm");

            B.hide();
            F.show();
            console.log(B);
            console.log(F);
        }

        function hideAddEmployee() {
            var B = DATA.MODAL.ModalDay.find(".showAddEmployee"),
                F = DATA.MODAL.ModalDay.find(".AddEmployeeForm");

            B.show();
            F.hide();
        }

        function modalShow() {
            var _this = $(this);

            if (_this.hasClass("Modal")) {
                $("body").css({"overflow": "hidden"});
                _this.css({"display": "inline-block"});
            }
            else {
                _this.css({"display": "inline-block"});
            }
        }

        function modalHide() {
            var _this = $(this);

            if (_this.hasClass("Modal")) {
                $("body").css({"overflow": "auto"});
                _this.css({"display": "none"});
                _this.find(".ModalBlock").hide();
            }
            else {
                _this.css({"display": "none"});
            }
        }

        function setWorking() {
            var Form = DATA.MODAL.ModalDay.find(".AddEmployeeForm"),
                user_id = Form.find("[name='employee']").val(),
                action = Form.find("[name='action']").val(),
                hour = Form.find("[name='hour']").val(),
                minute = Form.find("[name='minute']").val(),
                day = DATA.MODAL.ModalDay.attr("day"),
                month = DATA.MODAL.ModalDay.attr("month"),
                year = DATA.MODAL.ModalDay.attr("year"),
                status = null;

            jQuery.ajax({
                type: 'POST',
                url: "/index.php?option=com_gm_ceiling&task=guild.setWorking",
                data: {
                    user_id: user_id,
                    date: year + "-" + month + "-" + day + " " + hour + ":" + minute + ":00",
                    action: action
                },
                cache: false,
                async: false,
                success: function (data) {
                    data = JSON.parse(data);
                    status = data.status;

                    noty({
                        theme: 'relax',
                        layout: 'center',
                        timeout: 5000,
                        type: data.status,
                        text: data.message
                    });
                },
                dataType: "text",
                timeout: 1500,
                error: function () {
                    noty({
                        theme: 'relax',
                        layout: 'center',
                        timeout: 5000,
                        type: "error",
                        text: "Сервер не отвечает!"
                    });
                }
            });


            showModalDay({DAY: day, MONTH: month, YEAR: year});
            if (status !== "error") hideAddEmployee();
        }

    </script>
<? endif; ?>