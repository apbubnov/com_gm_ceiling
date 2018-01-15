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
    </div>
</div>

<link type="text/css" rel="stylesheet" href="/components/com_gm_ceiling/views/guild/styles/calendar.css">
<link type="text/css" rel="stylesheet" href="/components/com_gm_ceiling/views/guild/styles/schedule.css">

<script type="text/javascript">
    var $ = jQuery;

    $(document).ready(Init);

    function Init() {
        var calendars = $(".Calendars"),
            button = calendars.find(".button");
        button.attr("onclick", "ButtomCalendarClick(this);");

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
            ModalDay = Modal.find(".ModalDay");

        Modal.show();
    }

    function hideModalCalendar() {
        $(".ModalCalendar").hide();
        $(".ModalCalendar div:not(.Dark)").hide();
    }

</script>