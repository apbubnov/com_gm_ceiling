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

<script>
	jQuery(document).ready( function() {
		
		// проверка на пустые поля
		jQuery("#add-brigade").click(function() {
			if (jQuery("#name").val() == "" || jQuery("#phone").val() == "" || jQuery("#email").val() == "") {
				jQuery("#wrong").text("Все поля монтажной бригады должны быть заполнены");
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
				var names = jQuery(".name-mount");
				var phones = jQuery(".phone-mount");
				Array.from(names).forEach(function(element) {
					if (element.value == "") {
						element.classList.add("empty");
					}
				});
				Array.from(phones).forEach(function(element) {
					if (element.value == "") {
						element.classList.add("empty");
					}
				});
				var count_empty = Array.from(jQuery(".empty")).length;
				if (count_empty == 0) {
					jQuery("#mounter_form").submit();
				} else {
					jQuery("#wrong").text("Поля имя и телефон монтажника должны быть заполнены");
				}          
			}
		});

		// если поле изменилось и заполнено, удалить класс empty или вернуть прежний цвет
		jQuery(document).on("keydown", ".name-mount, .phone-mount", function() {
			if (this != "") {
				this.classList.remove("empty");            
			}
		});
		jQuery("#name").keydown(function() {
			jQuery("#name").css({"border" : "1px solid #a9a9a9"});
		});
		jQuery("#phone").keydown(function() {
			jQuery("#phone").css({"border" : "1px solid #a9a9a9"});
		}); 
		jQuery("#email").keydown(function() {
			jQuery("#email").css({"border" : "1px solid #a9a9a9"});
		}); 
		
		// добавление полей для монтажников
		var serialNumber = 1;
		jQuery("#add-mounter-btn").click(function() {
			mounter = '<div class="mounter">';
			mounter += '<p class="margin-button-tar">Монтажник '+serialNumber+'</p>';
			mounter += '<p class="margin-button-tar" style="margin-top: 1em;">ФИО:</p>';
			mounter += '<p class="margin-top-tar"><input type="text" name="name-mount[]" class="name-mount input-tar"></p>';
			mounter += '<p class="margin-button-tar">Номер телефона:</p>';
			mounter += '<p class="margin-top-tar"><input type="text" name="phone-mount[]" class="phone-mount input-tar"></p>';
			mounter += '<p class="margin-button-tar">Загрузите ксерокопию паспорта:</p>';
			mounter += '<p class="margin-top-tar"><input type="file" accept="image/*" name="pasport[]" class="pasport input-tar"></p>';
			mounter += '<p class="margin-top-tar"><button type="button" class="button-del btn btn-danger">Удалить</button></p>'
			mounter += '</div>'
			jQuery("#add-mounter").before(mounter);
			jQuery(".phone-mount").mask("+7(999)999-99-99");
			serialNumber++;
		});

		// Маски для телефонов
		jQuery("#phone").mask("+7(999)999-99-99");

		// удаление монтажника
		jQuery(document).on("click", ".button-del", function() {
			jQuery(this).closest("div").remove();
			serialNumber--;
		});

	});
</script>