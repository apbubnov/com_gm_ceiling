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
$answer1 = $model->getDataByStatus("GaugingsGraph");
// монтажи
$answer2 = $model->getDataByStatus("Mountings");
// завершенные монтажи
$answer3 = $model->getDataByStatus("ComplitedMountings");
// войти как замерщик
$answer4 = $model->getDataByStatus("GaugingsGraphNMS");
//запросы на монтаж
$answer5 = $model->getDataByStatus("MountService");
//--------------------------------------

?>

<?=parent::getButtonBack();?>

<h2 class="center">Начальник монтажной службы ГМ</h2>

<div class="start_page">
	<div style="width: 100%; margin-left: calc(50% - 100px); padding-bottom: 1em;">
		<div class="container-for-circl">
			<a class="btn btn-large btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=projects&type=gmchief&subtype=gaugings', false); ?>"><i class="fa fa-calendar" aria-hidden="true"></i> График замеров</a>
			<?php if ($answer1[0]->count != 0) { ?>
				<div class="circl-digits"><? echo $answer1[0]->count; ?></div>
			<?php } ?>
		</div>
	</div>
	<div style="width: 100%; margin-left: calc(50% - 100px); padding-bottom: 1em;">
		<div class="container-for-circl">
			<a class="btn btn-large btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=projects&type=gmchief', false); ?>"><i class="fa fa-calendar" aria-hidden="true"></i> Монтажи</a>
			<?php if ($answer2[0]->count != 0) { ?>
				<div class="circl-digits"><?php echo $answer2[0]->count; ?></div>
			<?php } ?>
		</div>
	</div>
	<div style="width: 100%; margin-left: calc(50% - 100px); padding-bottom: 1em;">
		<div class="container-for-circl">
			<a class="btn btn-large btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=projects&type=gmchief&subtype=service', false); ?>"><i class="fa fa-gavel" aria-hidden="true"></i> Запросы на монтаж </a>
			<?php if ($answer5[0]->count != 0) { ?>
				<div class="circl-digits"><?php echo $answer5[0]->count; ?></div>
			<?php } ?>
		</div>
	</div>
	<div style="width: 100%; margin-left: calc(50% - 100px); padding-bottom: 1em;">
		<div class="container-for-circl">
			<a class="btn btn-large btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=projectfinished', false); ?>"><i class="fa fa-lock" aria-hidden="true"></i> Завершенные монтажи</a>
			<?php if ($answer3[0]->count != 0) { ?>
				<div class="circl-digits"><?php echo $answer3[0]->count; ?></div>
			<?php } ?>
		</div>
	</div>
    <p class="center">
        <a class="btn btn-large btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=projects&type=gmchief&subtype=run', false); ?>"><i class="fa fa-lock" aria-hidden="true"></i> Завершенные заказы</a>
    </p>
	<p class="center">
		<a class="btn btn-large btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=teams&type=gmchief', false); ?>"><i class="fa fa-user" aria-hidden="true"></i> Бригады</a>
	</p>
	<p class="center">
		<a class="btn btn-large btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=gaugers&type=gmchief', false); ?>"><i class="fa fa-user" aria-hidden="true"></i> Замерщики</a>
	</p>
		<div style="width: 100%; margin-left: calc(50% - 100px); padding-bottom: 1em;">
		<div class="container-for-circl">
			<a class="btn btn-large btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=mainpage&type=gmcalculatormainpage', false); ?>"><i class="fa fa-sign-in" aria-hidden="true"></i> Войти как замерщик</a>
			<?php if ($answer4[0]->count != 0) { ?>
				<div class="circl-digits"><? echo $answer4[0]->count; ?></div>
			<?php } ?>
		</div>
	</div> 
</div>