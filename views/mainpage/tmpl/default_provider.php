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
$userGroup = $user->groups;
//if (!(array_search('19', $userGroup) || array_search('18', $userGroup))) header('Location: '.$_SERVER['REDIRECT_URL']);
?>


<style>
    .row{
        margin-bottom: 1em !important;
    }
    .btn-width{
        width:300px !important;
    }
</style>

<?= parent::getPreloader(); ?>
<h2><?=$user->name;?></h2>

<div class="start_page">
    <h3>Склад</h3>
    <div class="row center">
        <a class="btn btn-width btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=stock&type=reception', false, 2); ?>">
            <i class="fas fa-file-invoice"></i> Добавить новый товар
        </a>
    </div>
    <div class="row center">
        <a class="btn btn-width btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=stock&type=reception', false, 2); ?>">
            <i class="fas fa-file-invoice"></i> Прием
        </a>
    </div>
    <div class="row center">
        <a class="btn btn-width btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=stock&type=received', false, 2); ?>">
            <i class="far fa-clock"></i> История приемки
        </a>
    </div>
    <div class="row center">
        <a class="btn btn-width btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=stock&type=goods', false, 2); ?>">
            <i class="fas fa-list"></i> Список товаров
        </a>
    </div>
    <div class="row center">
        <a class="btn btn-width btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=stock&type=realization&subtype=projects', false, 2); ?>">
            <i class="fas fa-ellipsis-v"></i> Проекты на реализацию(дилеры)
        </a>
    </div>

    <div class="row center">
        <a class="btn btn-width btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=stock&type=issued&subtype=projects', false, 2); ?>">
            <i class="fa fa-bars" aria-hidden="true"></i> Выданные заказы
        </a>
    </div>
    <div class="row center">
        <a class="btn btn-width btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=stock&type=rest', false, 2); ?>">
            <i class="fa fa-bars" aria-hidden="true"></i> Количество оставшихся товаров
        </a>
    </div>


</div>