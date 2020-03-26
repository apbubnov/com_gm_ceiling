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
// замеры
$answer1 = $model->getDataByStatus("GaugingsGraph");
// монтажи
$answer2 = $model->getDataByStatus("Mountings");
// незапущенные монтажи
$answer3 = $model->getDataByStatus("UnComplitedMountings");
//--------------------------------------

?>
<!-- <?=parent::getButtonBack();?> -->
<h2 class="center">Монтажи</h2>
<?php if ($user->dealer_type == 0) { ?>
	<div class="start_page">
		<div style="width: 100%; margin-left: calc(50% - 100px); padding-bottom: 1em;">
			<div class="container-for-circl">
				<a class="btn btn-large btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=projects&type=chief&subtype=gaugings', false); ?>"><i class="fa fa-calculator" aria-hidden="true"></i> График замеров</a>
				<?php if ($answer1[0]->count != 0) { ?>
					<div class="circl-digits"><? echo $answer1[0]->count; ?></div>
				<?php } ?>
			</div>
		</div>
		<div style="width: 100%; margin-left: calc(50% - 100px); padding-bottom: 1em;">
			<div class="container-for-circl">
				<a class="btn btn-large btn-danger" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=projects&type=chief', false); ?>"><i class="fa fa-calculator" aria-hidden="true"></i> Монтажи</a>
				<?php if ($answer2[0]->count != 0) { ?>
					<div class="circl-digits"><?php echo $answer2[0]->count; ?></div>
				<?php } ?>
			</div>
		</div>
		<p class="center">
			<a class="btn btn-large btn-danger" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=projects&type=chief&subtype=run', false); ?>"><i class="fa fa-lock" aria-hidden="true"></i> Завершенные заказы</a>
		</p>
		<p class="center">
			<a class="btn btn-large btn-success" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=teams&type=chief', false); ?>"><i class="fa fa-user" aria-hidden="true"></i> Бригады</a>
		</p>
		<p class="center">
			<a class="btn btn-large btn-success" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=gaugers&type=chief', false); ?>"><i class="fa fa-user" aria-hidden="true"></i> Замерщики</a>
		</p>
	</div>
<? } else if ($user->dealer_type == 1) { ?>
	<div class="start_page">
		<div style="width: 100%; margin-left: calc(50% - 100px); padding-bottom: 1em;">
			<div class="container-for-circl">
				<a class="btn btn-large btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=projects&type=chief', false); ?>"><i class="fa fa-calculator" aria-hidden="true"></i> Назначенные <br>на монтаж и<br>запущенные<br>в производство</a>
				<?php if ($answer2[0]->count != 0) { ?>
					<div class="circl-digits"><?php echo $answer2[0]->count; ?></div>
				<?php } ?>
			</div>
		</div>
		<div style="width: 100%; margin-left: calc(50% - 100px); padding-bottom: 1em;">
			<div class="container-for-circl">
				<a class="btn btn-large btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=projects&type=chiefprojects', false); ?>"><i class="fa fa-calculator" aria-hidden="true"></i> Не назначенные<br>на монтаж или<br>не запущенные<br>в производство</a>
				<?php if ($answer3[0]->count != 0) { ?>
					<div class="circl-digits"><?php echo $answer3[0]->count; ?></div>
				<?php } ?>
			</div>
		</div>
		<p class="center">
			<a class="btn btn-large btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=teams&type=chief', false); ?>"><i class="fa fa-user" aria-hidden="true"></i> Бригады</a>
		</p>
	</div>
<? } ?>
