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
//$schedule = Gm_ceilingHelpersGm_ceiling::getModel('Guild')->getSchedule();
$calendars = [];
$calendars[] = Gm_ceilingHelpersGm_ceiling::LiteCalendar(-1);
$calendars[] = Gm_ceilingHelpersGm_ceiling::LiteCalendar(0);
$calendars[] = Gm_ceilingHelpersGm_ceiling::LiteCalendar(1);
?>

<? if ($chief || true): ?>
    <h1>Начальник цеха: <?= $user->name; ?></h1>
    <div class="Calendars">
        <div class="CalName">Календарь работ</div>
        <div class="Block">
            <button type="button" class="Left" value="-1">
                <i class="fa fa-chevron-circle-left" aria-hidden="true"></i>
            </button>
            <div class="CalBlock">
                <?= $calendars[0]; ?>
                <?= $calendars[1]; ?>
                <?= $calendars[2]; ?>
            </div>
            <button type="button" class="Right" value="1">
                <i class="fa fa-chevron-circle-right" aria-hidden="true"></i>
            </button>
        </div>
    </div>
<? elseif ($employee): ?>
    <h1>Работник цеха: <?= $user->name; ?></h1>
<? else: ?>
    <h1>К сожалению данный кабинет вам не доступен!</h1>
    Что бы получить доступ, обратитесь к IT отделу.
<? endif; ?>

<link type="text/css" rel="stylesheet" href="/components/com_gm_ceiling/views/guild/styles/calendar.css">
<link type="text/css" rel="stylesheet" href="/components/com_gm_ceiling/views/guild/styles/schedule.css">

<script type="text/javascript">
    var $ = jQuery;

    $(document).ready(Init);

    function Init() {
        var calendars = $(".Calendars"),
            button = calendars.find("button");
        button.click(function () {ButtomCalendarClick(this);});
    }

    function ButtomCalendarClick(element = null) {
        element = $(element);

        var val = parseInt(element.val()),
            calendars = $(".Calendars .block .CalBlock"),
            calendarFirst = calendars.find(".Calendar:first-child"),
            calendarLast = calendars.find(".Calendar:last-child"),
            diff = (parseInt(((val < 0)?calendarFirst:calendarLast).attr("diff"))) + val;

        console.log(val);
        console.log(calendarFirst);
        console.log(calendarLast);

        jQuery.ajax({
            type: 'POST',
            url: "/index.php?option=com_gm_ceiling&task=guild.getCalendar",
            data: {month: diff},
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
    }

</script>