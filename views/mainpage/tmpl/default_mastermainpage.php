<?php
/**
 * Created by PhpStorm.
 * User: bubnov
 * Date: 05.12.2018
 * Time: 15:45
 */
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

?>
<?=parent::getButtonBack();?>
<h2 class="center">Мастер</h2>
<style>
    .row{
        margin-bottom: 1em !important;
    }
</style>
<div class="start_page">
    <div class="row">
        <div class="col-md-4"></div>
        <div class="col-md-4">
            <a class="btn btn-large btn-primary" style="width: 85% !important;" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=builders', false); ?>">
                <i class="fa fa-building" aria-hidden="true"></i>   Застройщики
            </a>
        </div>
        <div class="col-md-4"></div>
    </div>
    <div class="row">
        <div class="col-md-4"></div>
        <div class="col-md-4">
            <a class="btn btn-large btn-primary" style="width: 85% !important;" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=mounterscommon', false); ?>">
                <i class="fas fa-table" style="text-align: left;"></i>   Сводная таблица
            </a>
        </div>
        <div class="col-md-4"></div>
    </div>
    <div class="row">
        <div class="col-md-4"></div>
        <div class="col-md-4">
            <a class="btn btn-large btn-primary" style="width: 85% !important;" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=teams&type=builders', false); ?>">
                <i class="fas fa-users" style="text-align: left"></i> Управление бригадами
            </a>
        </div>
        <div class="col-md-4"></div>
    </div>

</div>