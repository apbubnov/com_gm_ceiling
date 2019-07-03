<?php
/**
 * @version    CVS: 1.0.0
 * @package    Com_Gm_ceiling
 * @author     Mikhail "Spectral Eye" Vinyukov <vms@itctl.ru>
 * @copyright  2016 Mikhail "Spectral Eye" Vinyukov
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');

$user       = JFactory::getUser();
$userId     = $user->get('id');

/* циферки на кнопки */
$model = Gm_ceilingHelpersGm_ceiling::getModel('projects');
// график замеров
$answer1 = $model->getDataByStatus("GaugingsGraphNMS");
//--------------------------------------

?>
<?=parent::getButtonBack();?>
<h2 class="center">Замерщик ГМ</h2>

<div class="start_page">
	<div style="width: 100%; margin-left: calc(50% - 100px); padding-bottom: 1em;">
		<div class="container-for-circl">
		<a class="btn btn-large btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=projects&type=gmcalculator&subtype=calendar', false); ?>"><i class="fa fa-calendar" aria-hidden="true"></i> График замеров</a>
			<?php if ($answer1[0]->count != 0) { ?>
				<div class="circl-digits"><?php echo $answer1[0]->count; ?></div>
			<?php } ?>
		</div>
	</div>
	<p class="center">
		<a class="btn btn-large btn-danger" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=projects&type=gmcalculator&subtype=refused', false); ?>"><i class="fa fa-calculator" aria-hidden="true"></i> Отказники</a>
	</p>
	<p class="center">
		<a class="btn btn-large btn-success" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=projects&type=gmcalculator&subtype=projects', false); ?>"><i class="far fa-calendar-check" aria-hidden="true"></i> Запущенные</a>
	</p>
	<p class="center">
		<a class="btn btn-large btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=canvases', false); ?>"><i class="fa fa-rub" aria-hidden="true"></i> Прайс полотен</a>
	</p>
	<p class="center">
		<a class="btn btn-large btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=callback', false); ?>"><i class="fa fa-phone" aria-hidden="true"></i> Перезвоны</a>
	</p>
</div>