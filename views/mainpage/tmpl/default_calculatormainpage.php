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

$user       = JFactory::getUser();
$userId     = $user->get('id');

/* циферки на кнопки */
$model = Gm_ceilingHelpersGm_ceiling::getModel('projects');
// график замеров
$answer1 = $model->getDataByStatus("GaugingsGraph");
//--------------------------------------

?>
<style>
    .row{
        margin-bottom: 1em !important;
    }
</style>
<h2 class="center">Замерщик</h2>
<div class="start_page">
	<?php if ($user->dealer_type == 1){?>
		<p class="center">
            <a class="btn btn-large btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=addproject&type=calculator', false); ?>"><i class="fa fa-plus" aria-hidden="true"></i> Добавить замер</a>
        </p>
	<?php }?>
	<div class="row center">
			<a class="btn btn-large btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=projects&type=calculator&subtype=calendar', false); ?>">
                <div style="position: relative">
                    <div>
                        <i class="fa fa-calendar" aria-hidden="true"></i> График замеров
                    </div>
                    <?php if ($answer1[0]->count != 0) { ?>
                        <div class="circl-digits"><?php echo $answer1[0]->count; ?></div>
                    <?php } ?>
                </div>
            </a>
	</div>
	<div class="row center">
		<a class="btn btn-large btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=projects&type=calculator&subtype=projects', false); ?>"><i class="far fa-calendar-check" aria-hidden="true"></i> Запущенные</a>
	</div>
    <div class="row center">
        <a class="btn btn-large btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=projects&type=calculator&subtype=refused', false); ?>"><i class="far fa-window-close"></i> Отказы</a>
    </div>
	<div class="row center">
		<a class="btn btn-large btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=gaugers&type=chief', false); ?>"><i class="fa fa-user" aria-hidden="true"></i> Замерщики</a>
	</div>
    <div class="row center">
        <a class="btn btn-large btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=callback', false); ?>"><i class="fa fa-phone" aria-hidden="true"></i> Перезвоны</a>
    </div>
</div>