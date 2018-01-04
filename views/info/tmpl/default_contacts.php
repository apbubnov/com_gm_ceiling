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
	<h1> Контакты </h1>
	<table>
		<tr>
			<td>
				Полное наименование
			</td>
			<td>
				Индивидуальный предприниматель Руденко Игорь Александрович
			</td>
		</tr>
		<tr>
			<td>
				ОГРНИП
			</td>
			<td>
				309361002600011
			</td>
		</tr>	
		<tr>
			<td>
				ИНН
			</td>
			<td>
				362200996371
			</td>
		</tr>	
		<tr>
			<td>
				Паспорт
			</td>
			<td>
				2009 № 188576 выдан: ТП УФМС России по Воронежской области в Петропавлоском районе. Дата выдачи 19.04.2010
			</td>
		</tr>	
		<tr>
			<td>
				Юридический адрес
			</td>
			<td>
				394074, Воронежская область, город Воронеж,ул. Новикова д.9
			</td>
		</tr>	
		<tr>
			<td>
				Почтовый адрес 
			</td>
			<td>
				394026,г. Воронеж, ул. Проспект Труда, д.48,офис 55
			</td>
		</tr>
		<tr>
			<td>
				Фактический адрес 
			</td>
			<td>
				394026,г. Воронеж, ул. Проспект Труда, д.48,офис 55
			</td>
		</tr>		
		<tr>
			<td>
				Расчетный счет
			</td>
			<td>
				40802810508200005046
			</td>
		</tr>	
		<tr>
			<td>
				Корреспондентский счет
			</td>
			<td>
				30101810000000000201
			</td>
		</tr>	
		<tr>
			<td>
				БИК
			</td>
			<td>
				044525201
			</td>
		</tr>	
		<tr>
			<td>
				Наименование банка
			</td>
			<td>
				ОАО АКБ "АВАНГАРД" г. Москва
			</td>
		</tr>	
		<tr>
			<td>
				Контактные данные
			</td>
			<td>
				Тел. (473) 229-61-34
				<br>
				E-mail: gm-partner@mail.ru
			</td>
		</tr>	
	</table>
</form>