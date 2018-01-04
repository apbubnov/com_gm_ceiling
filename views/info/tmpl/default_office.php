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
	<h1> Адрес офиса </h1>
	<table>
		<tr>
			<td>
				Наш офис расположен по адресу г.Воронеж проспект Труда 48, территория станкостроительного завода. Чтобы попасть к нам необходимо зайти на территорию, повернуть в первый поворот налево, 5 подъезд в здании справа. 
			</td>
			<td>
				<script type="text/javascript" charset="utf-8" async src="https://api-maps.yandex.ru/services/constructor/1.0/js/?um=constructor%3A9e544eb4b376f3fa31e5322f97c35ee13289cc4a69525460fc296c9926976319&amp;width=500&amp;height=400&amp;lang=ru_RU&amp;scroll=true"></script>
			</td>
		</tr>
	</table>
</form>
