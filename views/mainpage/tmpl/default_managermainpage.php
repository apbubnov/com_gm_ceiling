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

?>
<?=parent::getButtonBack();?>
<h2 class="center">Менеджер</h2>

<div class="start_page">
	<p class="center">
		<a class="btn btn-large btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=projects&type=manager&subtype=refused', false); ?>"><i class="fa fa-times" aria-hidden="true"></i> Отказы</a>
	</p>	
	<p class="center">
		<a class="btn btn-large btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=clients&type=manager', false); ?>"><i class="fa fa-user" aria-hidden="true"></i> Клиенты</a>
	</p>
	<p class="center">
		<a class="btn btn-large btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=callback', false); ?>"><i class="fa fa-phone-square" aria-hidden="true"></i> Перезвоны</a>
	</p>
	<p class="center">
		<a class="btn btn-large btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=reservecalculation&type=manager', false); ?>"><i class="fa fa-pencil" aria-hidden="true"></i> Запись на замер</a>
	</p>
	<p class="center">
		<a class="btn btn-large btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=prices', false); ?>"><i class="fa fa-rub" aria-hidden="true"></i> Прайсы</a>
	</p>
</div>