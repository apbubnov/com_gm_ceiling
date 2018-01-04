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

<link rel="stylesheet" href="components/com_gm_ceiling/views/gaugerform/tmpl/css/style.css" type="text/css" />

<form id = "gauger_form" enctype="multipart/form-data" action="/index.php?option=com_gm_ceiling&task=gaugerform.RegisterGauger" method="post">
	<div id="content-tar">
		<p><h3> Добавление замерщика</h3></p>
		<div id="text-container">
			<p class="margin-bottom-tar">ФИО:</p>
			<p class="margin-top-tar"><input type="text" name="name" id="name" class="input-tar"></p>
			<p class="margin-bottom-tar">Телефон (логин):</p>
			<p class="margin-top-tar"><input type="text" name="phone" id="phone" class="input-tar"></p>
			<p class="margin-bottom-tar">Адрес электронной почты:</p>
			<p class="margin-top-tar"><input type="text" name="email" id="email" class="input-tar"></p>
			<p class="margin-bottom-tar">Загрузите ксерокопию паспорта:</p>
			<p class="margin-top-tar"><input type="file" accept="image/*" name="passport[]" class="passport input-tar"></p>
		</div>
		<p><button type ="button" id="add-gauger" class="btn btn-primary">Добавить</button></p>
		<div id="label-container">
			<div id="wrong"></div>
		</div>
	</div>
</form>

<script type='text/javascript'>

	jQuery(document).ready( function() {
		// проверка на пустые поля
		jQuery("#add-gauger").click(function() {
			if (jQuery("#name").val() == "" || jQuery("#phone").val() == "" || jQuery("#email").val() == "") {
				jQuery("#wrong").text("Все поля должны быть заполнены");
				if (jQuery("#name").val() == "") {
					jQuery("#name").css({"border" : "1px solid #ff3d3d"});
				}
				if (jQuery("#phone").val() == "") {
					jQuery("#phone").css({"border" : "1px solid #ff3d3d"});
				}
				if (jQuery("#email").val() == "") {
					jQuery("#email").css({"border" : "1px solid #ff3d3d"});
				}
			} else {
				jQuery("#gauger_form").submit();
			}
		});

		// если поле изменилось и заполнено, удалить класс empty или вернуть прежний цвет
		jQuery("#name").keydown(function() {
			jQuery("#name").css({"border" : "1px solid #a9a9a9"});
		});
		jQuery("#phone").keydown(function() {
			jQuery("#phone").css({"border" : "1px solid #a9a9a9"});
		}); 
		jQuery("#email").keydown(function() {
			jQuery("#email").css({"border" : "1px solid #a9a9a9"});
		}); 
		
		// Маски для телефонов
		jQuery("#phone").mask("+7(999)999-99-99");
	});

</script>