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

JHtml::_('behavior.keepalive');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('formbehavior.chosen', 'select');

// Load admin language file
$lang = JFactory::getLanguage();
$lang->load('com_gm_ceiling', JPATH_SITE);
$doc = JFactory::getDocument();
$doc->addScript(JUri::base() . '/media/com_gm_ceiling/js/form.js');

$user    = JFactory::getUser();
$userId     = $user->get('id');
$dealerId   = $user->dealer_id;

?>

<link rel="stylesheet" href="components/com_gm_ceiling/views/teamform/tmpl/css/style.css" type="text/css" />
<script type='text/javascript' src="components/com_gm_ceiling/views/teamform/tmpl/js/js.js"></script>

<form id = "mounter_form" enctype="multipart/form-data" action="/index.php?option=com_gm_ceiling&task=teamform.RegisterBrigade" method="post">
	<div id="content-tar">
		<p><h3> Добавление бригады</h3></p>
		<div id="text-container">
			<p class="margin-bottom-tar">Название бригады:</p>
			<p class="margin-top-tar"><input type="text" name="name" id="name" class="input-tar"></p>
			<p class="margin-bottom-tar">Телефон (логин):</p>
			<p class="margin-top-tar"><input type="text" name="phone" id="phone" class="input-tar"></p>
			<p class="margin-bottom-tar">Адрес электронной почты:</p>
			<p class="margin-top-tar"><input type="text" name="email" id="email" class="input-tar"></p>
		</div>
		<div id="add-mounter-container">
			<div id="add-mounter">
				<p><button type ="button" id="add-mounter-btn" class="btn btn-primary">Добавить монтажника</button></p>
			</div>
		</div>
		<p><button type ="button" id="add-brigade" class="btn btn-primary">Добавить бригаду</button></p>
		<div id="label-container">
			<div id="wrong"></div>
		</div>
	</div>
</form>