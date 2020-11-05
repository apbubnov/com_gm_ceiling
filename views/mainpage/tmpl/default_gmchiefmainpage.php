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
<style>
    .row{
        margin-bottom: 1em !important;
    }
</style>
<?=parent::getButtonBack();?>

<h2 class="center">Начальник монтажной службы ГМ</h2>

<div class="start_page">
    <div class="row center">
            <a class="btn btn-large btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=projects&type=gmchief&subtype=gaugings', false); ?>">
                <div style="position: relative">
                    <div>
                        <i class="fa fa-calendar" aria-hidden="true"></i> График замеров
                    </div>
                    <?php if ($answer1[0]->count != 0) { ?>
                        <div class="circl-digits" >
                            <?= $answer1[0]->count; ?>
                        </div>
                    <?php } ?>
                </div>
            </a>
    </div>
	<div class="row center" >
        <a class="btn btn-large btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=projects&type=gmchief', false); ?>">
            <div style="position: relative">
                <div>
                    <i class="fa fa-calendar" aria-hidden="true"></i> Монтажи
                </div>
                <?php if ($answer2[0]->count != 0) { ?>
                    <div class="circl-digits"><?php echo $answer2[0]->count; ?></div>
                <?php } ?>
            </div>
        </a>
	</div>
	<div class="row center" >
		<div class="container-for-circl">
			<a class="btn btn-large btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=projects&type=gmchief&subtype=service', false); ?>">
                <div style="position: relative">
                    <div>
                        <i class="fa fa-gavel" aria-hidden="true"></i> Запросы на монтаж
                    </div>
                    <?php if ($answer5[0]->count != 0) { ?>
                        <div class="circl-digits"><?php echo $answer5[0]->count; ?></div>
                    <?php } ?>
                </div>
            </a>

		</div>
	</div>
	<div class="row center" >
		<div class="container-for-circl">
			<a class="btn btn-large btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=projectfinished', false); ?>">
                <div style="position: relative">
                    <div>
                        <i class="fa fa-lock" aria-hidden="true"></i> Завершенные монтажи
                    </div>
                    <?php if ($answer3[0]->count != 0) { ?>
                        <div class="circl-digits"><?php echo $answer3[0]->count; ?></div>
                    <?php } ?>
                </div>
            </a>

		</div>
	</div>
    <div class="row center">
        <a class="btn btn-large btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=projects&type=gmchief&subtype=run', false); ?>"><i class="fa fa-lock" aria-hidden="true"></i> Завершенные заказы</a>
    </div>
	<div class="row center">
		<a class="btn btn-large btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=teams&type=gmchief', false); ?>"><i class="fa fa-user" aria-hidden="true"></i> Бригады</a>
	</div>
	<div  class="row center">
		<a class="btn btn-large btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=gaugers&type=gmchief', false); ?>"><i class="fa fa-user" aria-hidden="true"></i> Замерщики</a>
	</div>
		<div class="row center">
		<div class="container-for-circl">
            <a class="btn btn-large btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=mainpage&type=gmcalculatormainpage', false); ?>">
                <div style="position: relative">
                    <div>
                        <i class="fas fa-sign-in-alt"></i> Войти как замерщик
                    </div>
                    <?php if ($answer4[0]->count != 0) { ?>
                        <div class="circl-digits"><? echo $answer4[0]->count; ?></div>
                    <?php } ?>
                </div>
            </a>
		</div>
	</div>
    <div class="row center">
        <a class="btn btn-large btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=price&type=mount&subtype=service', false); ?>"><i class="fas fa-dollar-sign"></i> Прайс МС</a>
    </div>
</div>