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
<link rel="stylesheet" href="templates/gantry/js/chosen/chosen.min.css">
<script src="templates/gantry/js/chosen/chosen.jquery.min.js"></script>
<style>
    .row{
        margin-bottom: 15px;
    }
</style>
<?=parent::getButtonBack();?>

<form id = "mounter_form" enctype="multipart/form-data" action="/index.php?option=com_gm_ceiling&task=teamform.RegisterBrigade" method="post">
    <h3> Добавление бригады</h3>
    <div class="container">
        <div class="row center">
            <div class="col-md-12">
                Название бригады
            </div>
        </div>
        <div class="row center">
            <div class="col-md-12">
                <input type="text" name="name" id="name" class="input-tar">
            </div>
        </div>
        <div class="row center">
            <div class="col-md-12">
                Телефон (логин)
            </div>
        </div>
        <div class="row center">
            <div class="col-md-12">
                <input type="text" name="phone" id="phone" class="input-tar">
            </div>
        </div>
        <div class="row center">
            <div class="col-md-12">
                Адрес электронной почты
            </div>
        </div>
        <div class="row center">
            <div class="col-md-12">
                <input type="text" name="email" id="email" class="input-tar">
            </div>
        </div>
        <div class="row center">
            <div class="col-md-12">
                Город
            </div>
        </div>
        <div class="row center">
            <div class="col-md-12">
                <input type="hidden" id="chosen_city" name="chosen_city">
                <select type="text" id="select_city" class="input-tar"></select>
            </div>
        </div>
    </div>
    <div id="content-tar">

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

	// функция валидации емайла
	function isValidEmailAddress(emailAddress) {
		var pattern = new RegExp(/^(("[\w-\s]+")|([\w-]+(?:\.[\w-]+)*)|("[\w-\s]+")([\w-]+(?:\.[\w-]+)*))(@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$)|(@\[?((25[0-5]\.|2[0-4][0-9]\.|1[0-9]{2}\.|[0-9]{1,2}\.))((25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\.){2}(25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\]?$)/i);
		return pattern.test(emailAddress);
	}

	jQuery(document).ready( function() {
        jQuery.ajax({
            url: "index.php?option=com_gm_ceiling&task=city.getData",
            data:{
            },
            type: "POST",
            dataType: 'json',
            async: false,
            success: function (data) {
               jQuery.each(data,function(index,elem){
                   jQuery("#select_city").append('<option value="'+elem.id+'">'+elem.name+' ('+elem.region_name+')</option>')
                });
            },
            error: function (data) {
                var n = noty({
                    theme: 'relax',
                    layout: 'center',
                    maxVisible: 5,
                    type: "error",
                    text: "Ошибка при попытке удалить!"
                });
            }
        });
        jQuery( "#select_city" ).chosen();
		// проверка на пустые поля
		jQuery("#add-brigade").click(function() {
		    jQuery("#chosen_city").val(jQuery("#select_city").val())
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
			} else if (isValidEmailAddress(jQuery("#email").val()) == false) {
				jQuery("#wrong").text("Введите корректный E-mail");
				jQuery("#email").css({"border" : "1px solid #ff3d3d"});
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