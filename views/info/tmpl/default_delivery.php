<?php
/**
 * @version    CVS: 1.0.0
 * @package    Com_Gm_ceiling
 * @author     Mikhail  <vms@itctl.ru>
 * @copyright  2016 Mikhail 
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');
?>

<form >
	<h1> Доставка </h1>
	<ul>
		<li>
			Самовывоз(только для жителей Воронежской области);
		</li>
		<li>
			Доставка курьером(только для жителей Воронежа);
		</li>
		<li>
			Отправка Почтой России или ТК(для всей России);
		</li>
	</ul>
	<br>
		Отправка осуществляется по готовности заказа или в день согласованный с клиентом. Клиенту сообщается номер для отслеживания отправления.
</form>