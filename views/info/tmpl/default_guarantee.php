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
	<h1> Гарантии </h1>
	«Гильдия Мастеров» осуществляет производство и реализацию натяжных потолков из пленок производства MSD (Китай), LongWei (Китай). На все натяжные потолки, а также комплектующие, приобретенные у нас, распространяется гарантия. Гарантийный срок - 5 лет.
	<br>Гарантируется:
	<ul>
		<li>отсутствие заводских и других дефектов;</li>
		<li>механическая долговечность материала и прочность сварных швов; </li>
		<li>бесплатные работы по устранению возникших дефектов, если не были нарушены правила эксплуатации потолка.</li>
	</ul>
	<br>Вернуть потолок по гарантии возможно приехав к нам в офис или обратившись в офис по телефону и согласовав условия возврата.
</form>