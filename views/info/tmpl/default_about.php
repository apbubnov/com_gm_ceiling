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
	<h1> О компании </h1>
	«Гильдия мастеров» - молодая, быстроразвивающаяся компания-производитель натяжных потолков. За короткое время «Гильдия мастеров» стала одной из крупнейших компаний в Центральном Черноземье. Мы специализируемся на:<br>
	<ul>
		<li>производстве и реализации натяжных потолков;
		<li>производстве и реализации комплектующих;
		<li>реализации «натяжных потолков под ключ»;
	</ul>

</form>