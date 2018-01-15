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
$schedule = Gm_ceilingHelpersGm_ceiling::getModel('Guild')->getSchedule();
$calendars = [];
$calendars[] = Gm_ceilingHelpersGm_ceiling::LiteCalendar(-1);
$calendars[] = Gm_ceilingHelpersGm_ceiling::LiteCalendar(0);
$calendars[] = Gm_ceilingHelpersGm_ceiling::LiteCalendar(1);
?>

<?if ($chief || true):?>
    <h1>Начальник цеха: <?=$user->name;?></h1>
    <div class="Calendars">
        <div class="CalName">Календарь работ</div>
        <div class="Block">
            <div class="Left">
                <div
            </div>
            <div class="CalBlock">
                <?=$calendars[0];?>
                <?=$calendars[1];?>
                <?=$calendars[2];?>
            </div>
            <div class="Right">

            </div>
        </div>
    </div>
<?elseif($employee):?>
<h1>Работник цеха: <?=$user->name;?></h1>
<?else:?>
<h1>К сожалению данный кабинет вам не доступен!</h1>
Что бы получить доступ, обратитесь к IT отделу.
<?endif;?>

<link type="text/css" rel="stylesheet" href="/components/com_gm_ceiling/views/guild/styles/calendar.css">
<link type="text/css" rel="stylesheet" href="/components/com_gm_ceiling/views/guild/styles/schedule.css">
