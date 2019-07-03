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

<h2 class="center">Производитель</h2>

<div class="start_page">
	<p class="center">
		<a class="btn btn-large btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=dealers&type=manufacturer', false); ?>"><i class="fa fa-user" aria-hidden="true"></i> Дилеры</a>
	</p>	
	<p class="center">
		<a class="btn btn-large btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=projects&type=manager', false); ?>"><i class="far fa-clock" aria-hidden="true"></i> В производстве</a>
	</p>
	<p class="center">
		<a class="btn btn-large btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=projects&type=manager&subtype=runprojects', false); ?>"><i class="fa fa-cogs" aria-hidden="true"></i> Запущенные</a>
	</p>
	<p class="center">
		<a class="btn btn-large btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=prices', false); ?>"><i class="fa fa-rub" aria-hidden="true"></i> Прайсы</a>
	</p>
</div>