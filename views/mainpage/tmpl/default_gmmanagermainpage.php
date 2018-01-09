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

$dop_num_model = Gm_ceilingHelpersGm_ceiling::getModel('dop_numbers_of_users');
$dop_num = $dop_num_model->getData($userId)->dop_number;

/* циферки на кнопки */
$model = Gm_ceilingHelpersGm_ceiling::getModel('projects');
// в производстве
$answer1 = $model->getDataByStatus("InProduction", $userId, null);
// запущенные
$answer2 = $model->getDataByStatus("Zapushennie", $userId, null);
// заявки с сайта
$answer3 = $model->getDataByStatus("ZayavkiSSaita", $userId, null);
// звонки
$date = date("Y")."-".date("n")."-".date("d");
$answer4 = $model->getDataByStatus("Zvonki", $userId, $date);
// пропущенные
$answer5 = Gm_ceilingController::missedCalls($date, "missed", 1);
//--------------------------------------
?>

<style>
.columns-tar {
	display: inline-block;
	float: left;
	width: 100%;
	text-align: center;
}

@media screen and (min-width: 992px) {
	.columns-tar{
		width: calc(100% / 3 - 5px);
	}
}
</style>

<h2 class="center">Менеджер ГМ</h2>

<div class="start_page">
	<div class="columns-tar">
		<div style="margin-left: calc(50% - 100px); padding-bottom: 1em;">
			<div class="container-for-circl">
				<a class="btn btn-large btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=projects&type=gmmanager', false); ?>"><i class="fa fa-clock-o" aria-hidden="true"></i> В производстве </a>
				<?php if ($answer1[0]->count != 0) { ?>
					<div class="circl-digits"><?php echo $answer1[0]->count; ?></div>
				<?php } ?>
			</div>
		</div>
		<div style="margin-left: calc(50% - 100px); padding-bottom: 1em;">
			<div class="container-for-circl">
				<a class="btn btn-large btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=projects&type=gmmanager&subtype=runprojects', false); ?>"><i class="fa fa-cogs" aria-hidden="true"></i> Запущенные </a>
				<?php if ($answer2[0]->count != 0) { ?>
					<div class="circl-digits"><?php echo $answer2[0]->count; ?></div>
				<?php } ?>
			</div>
		</div>
		<p>
			<a class="btn btn-large btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=projects&type=gmmanager&subtype=archive', false); ?>"><i class="fa fa-archive" aria-hidden="true"></i> Архив</a>
		</p>
		<p>
			<a class="btn btn-large btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=projects&type=gmmanager&subtype=clientorders', false); ?>"><i class="fa fa-shopping-cart" aria-hidden="true"></i> Клиентские заказы</a>
		</p>
	</div>
	<div class="columns-tar">
		<div style="margin-left: calc(50% - 100px); padding-bottom: 1em;">
			<div class="container-for-circl">
				<a class="btn btn-large btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=requestfrompromo', false); ?>"><i class="fa fa-bookmark" aria-hidden="true"></i></i> Заявки с сайта </a>
				<?php if ($answer3[0]->count != 0) { ?>
					<div class="circl-digits"><?php echo $answer3[0]->count; ?></div>
				<?php } ?>
			</div>
		</div>
		<div style="margin-left: calc(50% - 100px); padding-bottom: 1em;">
			<div class="container-for-circl">
				<a class="btn btn-large btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=missed_calls', false); ?>"><i class="fa fa-exclamation-circle" aria-hidden="true"></i> Пропущенные</a>
				<?php if ($answer5 != 0) { ?>
					<div class="circl-digits"><?php echo $answer5; ?></div>
				<?php } ?>
			</div>
		</div>
		<p>
			<a class="btn btn-large btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=clients&type=manager', false); ?>"><i class="fa fa-user" aria-hidden="true"></i> Клиенты</a>
		</p>
		<p>
			<a class="btn btn-large btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=recoil', false); ?>"><i class="fa fa-user" aria-hidden="true"></i> Откатники</a>
		</p>
		<p>
			<a class="btn btn-large btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=projects&type=gmmanager&subtype=refused', false); ?>"><i class="fa fa-times" aria-hidden="true"></i> Отказы</a>
		</p>
	</div>
	<div class="columns-tar">
		<div style="margin-left: calc(50% - 100px); padding-bottom: 1em;">
			<div class="container-for-circl">
				<a class="btn btn-large btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=callback', false); ?>"><i class="fa fa-phone-square" aria-hidden="true"></i> Звонки </a>
				<?php if ($answer4[0]->count != 0) { ?>
					<div class="circl-digits"><?php echo $answer4[0]->count; ?></div>
				<?php } ?>
			</div>
		</div>
		<p>
			<a class="btn btn-large btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=reservecalculation&type=gmmanager&subtype=activatedprojects', false); ?>"><i class="fa fa-pencil" aria-hidden="true"></i> Запись на замер</a>
		</p>
		<p>
			<a class="btn btn-large btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=prices', false); ?>"><i class="fa fa-rub" aria-hidden="true"></i> Прайсы</a>
		</p>
		<p>
			<a class="btn btn-large btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=colors', false); ?>"><i class="fa fa-eyedropper" aria-hidden="true"></i> Цвета полотен</a>
		</p>
	</div>
</div>

<?php
    $user       = JFactory::getUser();
    $userId     = $user->get('id');
    $user_group = $user->groups;
    $dop_num_model = Gm_ceilingHelpersGm_ceiling::getModel('dop_numbers_of_users');
    $dop_num = $dop_num_model->getData($userId)->dop_number;
    $_SESSION['user_group'] = $user_group;
    $_SESSION['dop_num'] = $dop_num;
?>

