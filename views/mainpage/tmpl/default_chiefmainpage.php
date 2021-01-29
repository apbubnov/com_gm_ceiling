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
// договоры
$answer2 = $model->getDataByStatus("UnComplitedMountings");
// незапущенные монтажи
$answer3 =  $model->getDataByStatus("Mountings");
//запросы на монтаж
/*$answer4 = $model->getDataByStatus("MountService");*/
//--------------------------------------
?>
<style>
    .row{
        margin-bottom: 1em !important;
    }
</style>
<!-- <?=parent::getButtonBack();?> -->
<h2 class="center">Начальник МС</h2>
	<div class="start_page">
		<div class="row center">
            <a class="btn btn-large btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=projects&type=chiefprojects', false); ?>">
                <div style="position:relative;">
                    <div>
                        <i class="far fa-handshake"></i> Договоры
                    </div>
                    <?php if ($answer2[0]->count != 0) { ?>
                        <div class="circl-digits"><?php echo $answer2[0]->count; ?></div>
                    <?php } ?>
                </div>
            </a>
		</div>
		<div class="row center">
            <a class="btn btn-large btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=projects&type=chief', false); ?>">
                <div style="position:relative;">
                    <div>
                        <i class="fas fa-hammer"></i> Монтажи
                    </div>
                    <?php if ($answer3[0]->count != 0) { ?>
                        <div class="circl-digits"><?php echo $answer3[0]->count; ?></div>
                    <?php } ?>
                </div>
            </a>
		</div>
        <div class="row center">
            <a class="btn btn-large btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=projects&type=gmchief&subtype=service', false); ?>">
            <div style="position:relative;">
                    <div>
                        <i class="fas fa-hammer"></i> Запросы на монтаж
                    </div>
                  <!--  <?php /*if ($answer4[0]->count != 0) { */?>
                        <div class="circl-digits"><?php /*echo $answer4[0]->count; */?></div>
                    --><?php /*} */?>
                </div>
            </a>
        </div>
		<div class="row center">
			<a class="btn btn-large btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=teams&type=chief', false); ?>"><i class="fa fa-user" aria-hidden="true"></i> Бригады</a>
		</div>
        <div class="row center">
            <a class="btn btn-large btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=price&type=mount&subtype=service', false); ?>"><i class="fas fa-dollar-sign"></i> Прайс МС</a>
        </div>
	</div>

