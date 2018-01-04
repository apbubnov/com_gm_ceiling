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
	<h1>Ограничения</h1>
	<ol>
		<li>
			Ограничения на использование информации
			<ol>
				<li>Обращаем Ваше внимание что база данных test.gm-vrn.ru (далее Cервис) является исключительно коммерческим проектом и ориентирована на продажу натяжных потолков , а не на предоставление какой-либо информации посетителям сайта (например статистической).</li>
				<li>Используя Сервис, Вы соглашаетесь с тем, что:
					<ol>
						<li>будете использовать полученные данные только в законных целях</li>
						<li>не будете производить массовых выборок информации, превышающих разрешенные нормы</li>
					</ol>
				</li>
				<li>Запрещается использовать полученную информацию с целью ее дальнейшего распространения в коммерческих целях.</li>
				
			</ol>	
		</li>
	</ol>
</form>