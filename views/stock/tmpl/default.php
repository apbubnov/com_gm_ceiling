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
    body {
        background-color: #E6E6FA;
    }
</style>

<?= parent::getPreloader(); ?>
<h2><?=$user->name;?></h2>

<div class="start_page">
    <h3>Склад</h3>
    <p class="center">
        <a class="btn btn-large btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=stock&type=reception', false, 2); ?>"><i class="fa fa-bars" aria-hidden="true"></i> Прием</a>
    </p>
    <p class="center">
        <a class="btn btn-large btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=stock&type=inventory', false, 2); ?>"><i class="fa fa-bars" aria-hidden="true"></i> Инвентаризация</a>
    </p>
    <p class="center">
        <a class="btn btn-large btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=stock&type=goods', false, 2); ?>"><i class="fa fa-bars" aria-hidden="true"></i> На складе</a>
    </p>
    <p class="center">
        <a class="btn btn-large btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=stock&type=realization&subtype=projects', false, 2); ?>"><i class="fa fa-bars" aria-hidden="true"></i> Реализация</a>
    </p>
    <p class="center">
        <?= parent::getButtonBack(); ?>
    </p>
</div>