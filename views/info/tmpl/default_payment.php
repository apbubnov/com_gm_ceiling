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
	<h1> Способы оплаты: </h1>
	<ul>
		<li>
			Наличный расчёт<br>
				Оплата осуществляется наличными в офисе.
		</li>
		<li>
			Банковской картой<br> 
				Для выбора оплаты товара с помощью банковской карты на соответствующей странице необходимо нажать кнопку «Оплата заказа ». 
				Оплата происходит через ПАО СБЕРБАНК  с использованием Банковских карт следующих платежных систем: 
			<br>
			<table style="margin-left:20px">
				<tr>
					<td>
						✔ МИР
					</td>
					<td>
						<img src ="/images/mir.png" width = "80px" height = "30px"></img>
					</td>
				</tr>
				<tr>
					<td>
						✔ VISA International
					</td>
					<td>
						<img src ="/images/visa.png" width = "80px" height = "30px"></img>
					</td>
				</tr>
				<tr>
					<td>
						✔ Mastercard Worldwide
					</td>
					<td>
						<img src ="/images/mastercard.png" width = "80px" height = "60px"></img>
					</td>
				</tr>
			</table>
	</ul>

</form>