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
?>

<h2 class="center">Аналитик</h2>

<div class="start_page">
	<p class="center">
		<a class="btn btn-large btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=analytics', false); ?>"><i class="fa fa-line-chart" aria-hidden="true"></i> Аналитика</a>
	</p>
	<p class="center">
		<a class="btn btn-large btn-primary" href="<?php echo JRoute::_('/index.php?option=com_gm_ceiling&view=callback', false); ?>"><i class="fa fa-phone-square" aria-hidden="true"></i> Перезвоны</a>
	</p>
</div>
