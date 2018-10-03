<?php
/**
 * @version    CVS: 1.0.0
 * @package    Com_Gm_ceiling
 * @author     Mikhail "Spectral Eye" Vinyukov <vms@itctl.ru>
 * @copyright  2016 Mikhail "Spectral Eye" Vinyukov
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');

$user       = JFactory::getUser();
$userId     = $user->get('id');
?>

<style>
    body {
        color: #414099;
    }
</style>

<script src="https://api-maps.yandex.ru/2.1/?lang=ru_RU" type="text/javascript"></script>
<link rel="stylesheet" href="/components/com_gm_ceiling/views/addproject/tmpl/css/style.css" type="text/css" />
<?php if (!in_array("14", $user->groups)) { ?>
    <?=parent::getButtonBack();?>
<?php } ?>

<form id="calculate_form" action="/index.php?option=com_gm_ceiling&task=addproject.save" method="post" class="form-validate form-horizontal" enctype="multipart/form-data">
	<!-- Скрытые поля -->
	<input name="jform[project_calculation_date]" id="jform_project_calculation_date" value="" type="hidden">
	<input name="jform[project_calculator]" id="jform_project_calculator" type="hidden" value="">
	<!-- - - - - - - - - - - - - - - - - - - - - - -->
	<h2 class ="center" style="margin-bottom: 15px;"> Добавить замер </h2>

	<div class="col-md-4"></div>
	<div class="col-md-4">
		<p>
			<button id="choose_cleint_btn" class="btn btn-primary" type="button" style="width:100%;">Выбрать существующего клиента</button>
		</p>
		<div id=choose_fields style="display: none">
		<div class="control-label">
			<label id="jform_client_contacts-lbl-find" for="jform_client_fio-find" style="width:100%;">ФИО клиента<span class="star">&nbsp;*</span></label>
		</div>
		<div class="controls">
			<input name="jform[client_fio-find]" id="jform_client_fio-find" value="" style="width:100%;" placeholder="ФИО клиента"  aria-required="true" type="text">
		</div>
		<div class="controls">
			<button id="find_client_btn" class="btn btn-primary" style="width:100%; margin-top: 15px; margin-bottom: 15px;" type="button">Найти</button>
		</div>
		<div class="controls" id="select_clients" style="display:none">
			<select  style="width: 100%; margin-bottom: 15px;"  name="jform[clients]"  id="clients"></select>
		</div>
	</div>
	<!-- Регистрация клиента -->
	<div class="control-group" id="register_client">
		<div class="control-label">
			<label id="jform_client_name-lbl" for="jform_client_name" class="required">ФИО клиента<span class="star">&nbsp;*</span></label>
		</div>
		<div class="controls">
			<input name="jform[client_name]" id="jform_client_name" value="" class="required" style="width:100%; margin-bottom:1em;" placeholder="ФИО клиента" aria-required="true" type="text">
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<label id="jform_client_contacts-lbl" for="jform_client_contacts" class="required">Телефоны клиента<span class="star">&nbsp;*</span></label>
		</div>
		<div class="controls">
			<input name="jform[client_contacts]" id="jform_client_contacts" value="" class="required" style="width:100%; margin-bottom:1em;" placeholder="Телефоны клиента" required="required" type="text">
		</div>
	</div>
	<input name="jform[client_id]" id="client_id" type="hidden" value="0">
	<!-- Создание проекта -->
	<div class="control-group">
		<div class="control-label">
			<label id="jform_project_info-lbl" for="jform_project_info" class="required">Адрес клиента<span class="star">&nbsp;*</span></label>
		</div>
		<div class="controls">
			<input name="jform[project_info]" id="jform_project_info" value="" class="required" style="width:100%; margin-bottom:1em; float: left;" placeholder="Улица" required="required" aria-required="true" type="text">
		</div>
		<div class="controls">
			<input name="jform[project_info_house]" id="jform_project_info_house" value="" class="required" style="width: 50%; margin-bottom: 1em; float: left; margin: 0 5px 0 0;" placeholder="Дом" required="required" aria-required="true" type="text">
		</div>
		<div class="controls">
			<input name="jform[project_info_bdq]" id="jform_project_info_bdq" value=""  style="width: calc(50% - 5px); margin-bottom: 1em;" placeholder="Корпус" aria-required="true" type="text">
		</div>
		<div class="controls">
			<input name="jform[project_apartment]" id="jform_project_apartment" value=""  style="width:50%;margin-bottom:1em;margin-right: 5px;float: left;" placeholder="Квартира"  aria-required="true" type="text">
		</div>
		<div class="controls">
			<input name="jform[project_info_porch]" id="jform_project_info_porch" value=""  style="width: calc(50% - 5px); margin-bottom: 1em;" placeholder="Подъезд"  aria-required="true" type="text">
		</div>
		<div class="controls">
			<input name="jform[project_info_floor]" id="jform_project_info_floor" value=""  style="width:50%; margin-bottom:1em;  margin: 0 5px  0 0; float: left;" placeholder="Этаж" aria-required="true" type="text">
		</div>
		<div class="controls">
			<input name="jform[project_info_code]" id="jform_project_info_code" value=""  style="width: calc(50% - 5px); margin-bottom: 1em;" placeholder="Код" aria-required="true" type="text">
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<label id="jform_dealer_manager_note-lbl" for="jform_dealer_manager_note" class="required">Примечание</label>
		</div>
		<div class="controls">
			<input name="jform[dealer_manager_note]" id="jform_dealer_manager_note" value="" style="width:100%; margin-bottom:1em;" placeholder="Примечание" type="text">
		</div>
	</div>
	<div class="control-group" style="margin-bottom: 1em;">
		<p>Выберите дату и время замера</p>
        <input type="text" id="measure_info" class="inputactive" readonly>
        <div id="measures_calendar" align="center"></div>
	</div>
	<div>
		<button id="calculate_button" class="btn btn-primary" style="width:100%;margin-bottom: 10px;" type="button">Записать</button>
	</div>
</form>

<div id="mw_container" class="modal_window_container">
    <button type="button" class="close_btn" id="close_mw"><i class="fa fa-times fa-times-tar" aria-hidden="true"></i></button>
    <div class="modal_window" id="mw_measures_calendar"></div>
</div>

<script type="text/javascript" src="/components/com_gm_ceiling/date_picker/measures_calendar.js"></script>
<script type="text/javascript">
    init_measure_calendar('measures_calendar','jform_project_calculation_date','jform_project_calculator','mw_measures_calendar',['close_mw','mw_container'], 'measure_info');
    var $ = jQuery, Data = {};
    
    $(window).resize(function(){
        if (screen.width <= '1024') {
            jQuery('#calculate_form').css('font-size', '13px');
            jQuery('#choose_cleint_btn').css('font-size', '13px');
            jQuery('#find_client_btn').css('font-size', '13px');
            jQuery('#choose_cleint_btn').css('padding', '10px 5px');
        }
    });

    // вызовем событие resize
    $(window).resize();

    jQuery(document).mouseup(function(e) {
        var div = jQuery("#mw_measures_calendar");
        if (!div.is(e.target)
            && div.has(e.target).length === 0) {
            jQuery("#close_mw").hide();
            jQuery("#mw_container").hide();
            jQuery("#mw_measures_calendar").hide();
        }
    });

	jQuery(document).ready(function() {

        $(window).keydown(function(event){
            if(event.keyCode == 13) {
            event.preventDefault();
            return false;
            }
        });

		jQuery("#calculate_button").click(function(){
			if(jQuery("#jform_project_calculation_date").val()!=""
                && jQuery("#jform_project_calculator").val()!=""
                && jQuery("#jform_project_info").val()!=""
                && jQuery("#jform_project_info_house").val()!=""
                && (jQuery("#client_id").val()!="0" || jQuery("#jform_client_name").val()!=""))
            {
				jQuery("#calculate_form").submit();
			}
			else{
				var n = noty({
					theme: 'relax',
                    timeout: 2000,
					layout: 'center',
					maxVisible: 5,
					type: "error",
					text: "Введены не все данные!"
				});
			}
		});

		jQuery("#jform_client_contacts").mask("+7 (999) 999-99-99");
		
		jQuery('#clients').change(function(){
			jQuery('#client_id').val(jQuery('#clients option:selected').val());
		});
		
		jQuery('#jform_client_fio-find').change(function(){
			jQuery('#jform_client_contacts').val(jQuery('#jform_client_fio-find').val());
		});

		jQuery( "#choose_cleint_btn" ).click(function(){
			if(jQuery('#choose_fields').css('display') == 'none'){
				document.getElementById("choose_cleint_btn").innerHTML = 'Добавить нового клиента';
				jQuery('#choose_fields').show();
				jQuery('#jform_client_name-lbl').hide();
				jQuery('#jform_client_name').hide();
				jQuery('#jform_client_contacts-lbl').hide();
				jQuery('#jform_client_contacts').hide();
                jQuery('#jform_client_contacts').removeAttr("required");
			}
			else {
				document.getElementById("choose_cleint_btn").innerHTML = 'Выбрать существующего клиента';
				jQuery('#choose_fields').hide();
				jQuery('#jform_client_name-lbl').show();
				jQuery('#jform_client_name').show();
				jQuery('#jform_client_contacts-lbl').show();
				jQuery('#jform_client_contacts').show();

			}
			if(jQuery('#new_client').css('display') != 'none')
				jQuery('#new_client').hide();
		});
		
		jQuery('#find_client_btn').click(function(){
			document.getElementById('clients').innerHTML='';
			var fio = jQuery("#jform_client_fio-find").val();
			jQuery.ajax({
					url: "index.php?option=com_gm_ceiling&task=findOldClients",
					data: {
						fio: fio
					},
					dataType: "json",
					async:true,
					success: function(data){
					    //data = JSON.parse(date);
						console.log(data);
						jQuery('#clients').find('option').remove();
						for(var i = 0; i < data.length; i++)
						{
							jQuery('<option>').val(data[i].id).text(data[i].client_name).appendTo('#clients');
                            jQuery('<option>').attr("phone",data[i].client_contacts);
						}
						jQuery('#client_id').val(jQuery('#clients option:selected').val());
                        jQuery('#jform_client_contacts').val(jQuery('#clients option:selected').attr("phone"));
						console.log(jQuery('#client_id').val());
					},
					error: function(data){
						console.log(data);
						var n = noty({
							theme: 'relax',
							layout: 'center',
							maxVisible: 5,
							type: "error",
							text: "Ошибка"
						});
					}					
				});
			jQuery('#select_clients').show();
		});

        Data.ProjectInfoYMaps = $("#jform_project_info").siblings("ymaps");
        Data.ProjectInfoYMaps.click(hideYMaps);
	});

	function hideYMaps() {
        setTimeout(function () {
            Data.ProjectInfoYMaps.hide();
            $("#jform_project_info_house").focus();
        }, 75);
    }

    jQuery(function(){
        jQuery('#clients').change(function(){
            var client_id = jQuery('#clients :selected').text();
            var client_contacts = jQuery('#clients :selected').attr("phone");
            jQuery('#jform_client_fio-find').val(client_id);
            jQuery('#jform_client_contacts').val(client_contacts);
        })
    });

	// Подсказки по городам
    ymaps.ready(init);

    function init() {
		var provider
        // Подключаем поисковые подсказки к полю ввода.
        var suggestView = new ymaps.SuggestView('jform_project_info');
		input = jQuery('#jform_project_info');

		suggestView.events.add('select', function (e) {
            var s = e.get('item').value.replace('Россия, ','');
            input.val(s);
		});

        Data.ProjectInfoYMaps = $("#jform_project_info").siblings("ymaps");
        Data.ProjectInfoYMaps.click(hideYMaps);
    }
</script>
