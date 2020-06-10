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
$doc = JFactory::getDocument();
/*$doc->addScript(JUri::base() . '/media/com_gm_ceiling/js/form.js');*/

$user = JFactory::getUser();
$userId = $user->get('id');

?>
<?= parent::getButtonBack(); ?>
<link rel="stylesheet" href="/components/com_gm_ceiling/views/reservecalculation/tmpl/css/style.css" type="text/css" />

<style>
    .center input {
        min-width: 280px;
    }
    #find_client_btn {
        min-width: 280px;
    }
</style>
<form id="calculate_form" onsubmit="check(this); return false;" action="/index.php?option=com_gm_ceiling&task=addproject.save" method="post" class="form-validate form-horizontal" enctype="multipart/form-data">
    <h2 class="center">Запись на замер</h2>
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <div class="row" style="margin-bottom: 2px;">
                    <div class="col-md-12 center">
                        <button id="new_cleint_btn" class="btn btn-primary" type="button" style=" min-width: 280px;">
                            <i class="fa fa-plus-square" aria-hidden="true"></i> Создать нового клиента
                        </button>
                    </div>
                </div>
                <div id="new_client" style="display: none;margin-bottom: 2px;" class="row">
                    <!-- Регистрация клиента -->
                    <div class="col-md-12">
                        <label id="jform_client_name-lbl" for="jform_client_name" class="required">ФИО клиента<span class="star">&nbsp;*</span></label>
                        <input name="jform[client_name]" id="jform_client_name" value="" class=" inputactive required" placeholder="ФИО клиента" aria-required="true" type="text">
                    </div>
                    <div class="col-md-12">
                        <label id="jform_client_contacts-lbl" for="jform_client_contacts" class="required">Телефон клиента<span class="star">&nbsp;*</span></label>
                        <input name="jform[client_contacts]" id="jform_client_contacts" value="" class=" inputactive required" placeholder="Телефон клиента" aria-required="true" type="text">
                    </div>
                </div>
                <div class="row" style="margin-bottom: 2px;">
                    <div class="col-md-12 center">
                        <button id="choose_cleint_btn" class="btn btn-primary" type="button" style=" min-width: 280px;">
                           <i class="fa fa-users" aria-hidden="true"></i> Выбрать существующего клиента
                        </button>
                    </div>
                </div>
                <div id=choose_fields style="display: none;margin-bottom: 2px;" class="row">
                    <div class="col-md-12">
                        <label id="jform_client_fio-lbl-find" for="jform_client_fio-find">ФИО клиента<span class="star">&nbsp;*</span></label>
                        <input name="jform[client_fio-find]" id="jform_client_fio-find" value="" class="inputactive" placeholder="ФИО клиента" aria-required="true" type="text">
                        <div class="center">
                            <button align="center" id="find_client_btn" class="btn btn-primary" type="button" style="margin-top: 10px;margin-bottom: 10px;">
                                 <i class="fa fa-search" aria-hidden="true"></i> Найти
                            </button>
                        </div>
                    </div>
                    <div class="col-md-12" id="select_clients" style="display:none">
                        <select class="inputactive" name="jform[clients]" size="1" id="clients"></select>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="row" style="margin-bottom: 2px;">
                    <div class="col-sm-12">
                        <input name="jform[project_info]" id="jform_project_info" value=""  class="inputactive" placeholder="Адрес замера" aria-required="true" type="text">
                    </div>
                </div>
                <div class="row" style="margin-bottom: 2px;">
                    <div class="col-md-6">
                        <input name="jform[project_info_house]" id="jform_project_info_house" value="" class="inputactive" placeholder="Дом" required="required" aria-required="true" type="text">
                    </div>
                    <div class="col-md-6">
                        <input name="jform[project_info_bdq]" id="jform_project_info_bdq" value="" class="inputactive" placeholder="Корпус" aria-required="true" type="text">
                    </div>
                </div>
                <div class="row" style="margin-bottom: 2px;">
                    <div class="col-md-6">
                         <input name="jform[project_apartment]" id="jform_project_apartment" value=""  class="inputactive" placeholder="Квартира"  aria-required="true" type="text">
                    </div>
                    <div class="col-md-6">
                        <input name="jform[project_info_porch]" id="jform_project_info_porch" value="" class="inputactive" placeholder="Подъезд"  aria-required="true" type="text">
                    </div>
                </div>
                <div class="row" style="margin-bottom: 2px;">
                    <div class="col-md-6">
                        <input name="jform[project_info_floor]" id="jform_project_info_floor" value=""  class="inputactive" placeholder="Этаж" aria-required="true" type="text">
                    </div>
                    <div class="col-md-6">
                        <input name="jform[project_info_code]" id="jform_project_info_code" value=""  class="inputactive" placeholder="Код" aria-required="true" type="text">
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Для начальника монтажной службы -->
    <div class="row">
        <div class="control-label">
            <label id="jform_project_note-lbl" style="display: none" for="jform_project_note" class="required">Примечание</label>
        </div>
        <div class="controls">
            <input name="jform[project_note]" id="jform_project_note" style="display: none" value="" placeholder="Примечание" type="text">
        </div>
    </div>
    <div class="row" style="text-align: center;">
        <h4><p>Назначить дату и время замера</p></h4>
        <input name="jform[project_calculation_date]" id="jform_project_calculation_date" type="hidden" value="">
        <input name="jform[project_calculation_daypart]" id="jform_project_calculation_daypart" type="hidden" value="">
        <input name="jform[project_calculator]" id="jform_project_calculator" type="hidden" value="">
        <div id = "measures_calendar" align="center"></div>
        <label for="measure_info">Вы выбрали:</label>
        <input id="measure_info" readonly>
    </div>
    <input name="jform[client_id]" id="client_id" type="hidden" value="0">
    <div class="center">
        <button id="calculate_button" class="btn btn-primary" type="submit" style=" min-width: 280px;">Записать</button>
    </div>
    <div class="modal_window_container" id="mw_container">
        <button type="button" class="close_btn" id="close_mw"><i class="fa fa-times fa-times-tar" aria-hidden="true"></i></button>
        <div id="mw_measures_calendar" class="modal_window"></div>
    </div>
</form>
<script type="text/javascript" src="/components/com_gm_ceiling/date_picker/measures_calendar.js"></script>
<script>
    init_measure_calendar('measures_calendar','jform_project_calculation_date','jform_project_calculator','mw_measures_calendar',['close_mw','mw_container'], 'measure_info');
    function check(e) {
        if (jQuery("#jform_client_name").val() == "" && jQuery("#jform_client_fio-find").val() == "") {
            var n = noty({
                theme: 'relax',
                layout: 'center',
                maxVisible: 5,
                type: "error",
                text: "Введите все данные"
            });
        }
        else e.submit();
    }

    //скрыть модальное окно
    jQuery(document).mouseup(function (e) {
		var div = jQuery("#mw_measures_calendar");
		if (!div.is(e.target)
		    && div.has(e.target).length === 0) {
			jQuery("#close_mw").hide();
			jQuery("#mw_container").hide();
			jQuery("#mw_measures_calendar").hide();
		}
    });
    jQuery(document).ready(function () {

        jQuery("#jform_client_contacts").mask("+7 (999) 999-99-99");

        jQuery("#new_cleint_btn").click(function () {
            if (jQuery('#new_client').css('display') == 'none') {
                jQuery('#new_client').show();
            }
            else {
                jQuery('#new_client').hide();
                
            }
            if (jQuery("#choose_fields").css('display') != 'none')
                jQuery("#choose_fields").hide();
        });
        jQuery("#choose_cleint_btn").click(function () {
            if (jQuery('#choose_fields').css('display') == 'none') {
                jQuery('#choose_fields').show();
               
            }
            else {
                jQuery('#choose_fields').hide();
                
            }
            if (jQuery('#new_client').css('display') != 'none')
                jQuery('#new_client').hide();
        });

        jQuery('#find_client_btn').click(function () {
            var fio = jQuery("#jform_client_fio-find").val();
            jQuery.ajax({
                url: "index.php?option=com_gm_ceiling&task=findOldClients",
                data: {
                    fio: fio
                },
                dataType: "json",
                async: true,
                success: function (data) {
                    console.log(data);
                    jQuery('#clients').find('option').remove();
                    for (var i = 0; i < data.length; i++) {
                        jQuery('<option>').val(data[i].id).text(data[i].client_name+"/"+data[i].client_contacts).appendTo('#clients');
                    }
                    jQuery('#client_id').val(jQuery('#clients option:selected').val());
                },
                error: function (data) {
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

        jQuery('#clients').change(function () {
            console.log(jQuery('#clients option:selected').val());
            jQuery('#jform_client_fio-find').val(jQuery('#clients option:selected').text().split('/')[0]);
        });
    });
</script>