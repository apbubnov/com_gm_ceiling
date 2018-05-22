<?php
/**
 * @version    CVS: 0.1.7
 * @package    Com_Gm_ceiling
 * @author     SpectralEye <Xander@spectraleye.ru>
 * @copyright  2016 SpectralEye
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');

echo parent::getButtonBack();

?>
<h2>Аналитика звонков</h2>
	<div class="analitic-actions">
		Выбрать с <input type="date" id="c_date1"> по <input type="date" id="c_date2"> <button type="button" class="btn btn-primary" id="c_show_all">Показать всё</button>
	</div>
	<table>
		<tbody>
			<tr><th></th><td></td></tr>
			<tr><th></th><td></td></tr>
			<tr><th></th><td></td></tr>
		</tbody>
	</table>
