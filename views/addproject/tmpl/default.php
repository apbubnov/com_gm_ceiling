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

$apiPhonesModel = Gm_ceilingHelpersGm_ceiling::getModel('api_phones');
$advt = $apiPhonesModel->getDealersAdvt($user->dealer_id);
?>

<style>
    body {
        color: #414099;
    }

</style>

<link rel="stylesheet" href="/components/com_gm_ceiling/views/addproject/tmpl/css/style.css" type="text/css" />
<?php if (!in_array("14", $user->groups)) { ?>
    <?=parent::getButtonBack();?>
<?php } ?>

<form id="calculate_form" action="/index.php?option=com_gm_ceiling&task=addproject.save" method="post" class="form-validate form-horizontal" enctype="multipart/form-data">
	<!-- Скрытые поля -->
	<input name="jform[project_calculation_date]" id="jform_project_calculation_date" value="" type="hidden">
	<input name="jform[project_calculator]" id="jform_project_calculator" type="hidden" value="">
    <input name="jform[client_id]" id="client_id" type="hidden" value="">
	<!-- - - - - - - - - - - - - - - - - - - - - - -->
	<h2 class ="center" style="margin-bottom: 15px;"> Запись на замер </h2>

	<div class="col-md-3"></div>
	<div class="col-md-6">
        <h4>Данные о клиенте</h4>
	<!-- Регистрация клиента -->
        <div class="row">
            <div class="col-md-12">
                <label id="jform_client_name-lbl" for="jform_client_name" class="required">ФИО клиента</label>
            </div>
            <div class="col-md-12">
                <input name="jform[client_name]" id="jform_client_name" value="" class="form-control required" style="width:100%; margin-bottom:1em;" placeholder="Фамилия Имя Отчество" aria-required="true" type="text">
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <label id="jform_client_contacts-lbl" for="jform_client_contacts" class="required">Номер телефона</label>
            </div>
            <div class="col-md-12">
                <input name="jform[client_contacts]" id="jform_client_contacts" value="" class="form-control required" style="width:100%; margin-bottom:1em;" placeholder="Телефон клиента" required="required" type="text">
            </div>
        </div>
        <div class="row" id="found_clients" style="display: none;">
            <div class="col-md-12">
                <span style="color:red;">Внимание! Клиент с данным номером уже существует, замер будет добавлен клиенту: </span>
                <span id="found_client_info"></span>
            </div>

        </div>
	<!-- Создание проекта -->
        <div class="row" style="margin-bottom:15px;">
            <div class="col-xs-4 col-md-4">
                Улица
            </div>
            <div class="col-xs-8 col-md-8">
                <input name="jform[new_address]" id="jform_address" class="form-control" value="" placeholder="Улица" type="text" >
            </div>
        </div>
        <div class="row" style="margin-bottom:15px;">
            <div class="col-xs-4 col-md-4">
                Дом / Корпус
            </div>
            <div class="col-xs-4 col-md-4">
                <input name="jform[new_house]" id="jform_house" value="" class="form-control" placeholder="Дом"  aria-required="true" type="text">
            </div>
            <div class="col-xs-4 col-md-4">
                <input name="jform[new_bdq]" id="jform_bdq"  value="" class="form-control"   placeholder="Корпус" aria-required="true" type="text">
            </div>
        </div>
        <div class="row" style="margin-bottom:15px;">
            <div class="col-xs-4 col-md-4">
                Квартира / Подъезд
            </div>
            <div class="col-xs-4 col-md-4">
                <input name="jform[new_apartment]" id="jform_apartment" value="" class="form-control" placeholder="Квартира"  aria-required="true" type="text">
            </div>
            <div class="col-xs-4 col-md-4">
                <input name="jform[new_porch]" id="jform_porch"  value="" class="form-control"    placeholder="Подъезд"  aria-required="true" type="text">
            </div>
        </div>
        <div class="row" style="margin-bottom:15px;">
            <div class="col-xs-4 col-md-4">
                Этаж / Код домофона
            </div>
            <div class="col-xs-4 col-md-4">
                <input name="jform[new_floor]" id="jform_floor"  value="" class="form-control"  placeholder="Этаж" aria-required="true" type="text">
            </div>
            <div class="col-xs-4 col-md-4">
                <input name="jform[new_code]" id="jform_code"  value="" class="form-control"   placeholder="Код" aria-required="true" type="text">
            </div>
        </div>
        <div class="row" style="margin-bottom:15px;">
            <div class="col-xs-4 col-md-4">
                Примечание к замеру
            </div>
            <div class="col-xs-8 col-md-8">
                <input name="jform[measure_note]" id="measure_note" class="form-control"
                       value="">
            </div>
        </div>
        <div class="row" style="margin-bottom: 1em;">
            <h4>Выберите рекламу</h4>
            <div class="col-md-12">
                <select class="form-control"  name= "jform[advt]" id="advt_select">
                    <option value="">Выберите рекламу</option>
                    <?php foreach ($advt as $i){ ?>
                        <option value="<?=$i->id?>"><?=$i->advt_title?></option>
                    <?php }?>
                </select>
            </div>
        </div>
        <div class="row" style="margin-bottom: 1em;">
            <h4>Выберите дату и время замера</h4>
            <div id="measures_calendar" align="center"></div>
        </div>
        <div class="row" style="margin-bottom: 15px;">
            <div class="col-md-12">
                Вы выбрали
            </div>
            <div class="col-md-12">
                <input type="text" id="measure_info" class="form-control" readonly>
            </div>
        </div>
	<div class="row center">
		<button id="rec_to_measure" class="btn btn-primary" type="button">Записать</button>
	</div>
    </div>
    <div class="col-md-3"></div>
</form>

<div id="mw_container" class="modal_window_container">
    <button type="button" class="close_btn" id="close_mw"><i class="fa fa-times fa-times-tar" aria-hidden="true"></i></button>
    <div class="modal_window" id="mw_measures_calendar"></div>
</div>

<script type="text/javascript" src="/components/com_gm_ceiling/date_picker/measures_calendar.js"></script>
<script type="text/javascript">
    init_measure_calendar('measures_calendar','jform_project_calculation_date','jform_project_calculator','mw_measures_calendar',['close_mw','mw_container'], 'measure_info');
    var $ = jQuery,
        Data = {},
        dealer_id = '<?=$user->dealer_id;?>';

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

		jQuery("#rec_to_measure").click(function(){
		    if(
                empty(jQuery('#jform_client_name').val()) &&
                empty(jQuery('#jform_client_contacts').val())&&
                empty(jQuery('#client_id').val())
            ){
                noty({
                    theme: 'relax',
                    layout: 'center',
                    maxVisible: 5,
                    type: "error",
                    text: "Не введена информация о клиенте!"
                });
                return;
            }
		    if(
		        empty(jQuery('#jform_address').val()) &&
                empty(jQuery('#jform_house').val())
            ){
                noty({
                    theme: 'relax',
                    layout: 'center',
                    maxVisible: 5,
                    type: "error",
                    text: "Не введен адрес и дом замера!"
                });
                return;
            }
		    if(
		        empty(jQuery("#jform_project_calculation_date").val()) &&
                empty(jQuery("#jform_project_calculator").val())
            ){
                noty({
                    theme: 'relax',
                    layout: 'center',
                    maxVisible: 5,
                    type: "error",
                    text: "Не выбрана дата замера!"
                });
                return;
            }
		    if(empty(jQuery('#advt_select').val())){
                noty({
                    theme: 'relax',
                    layout: 'center',
                    timeout: false,
                    type: "info",
                    text: "Не выбрана реклама, продолжить?",
                    buttons:[
                        {
                            addClass: 'btn btn-primary', text: 'Записать без реклама', onClick: function($noty) {
                                jQuery("#calculate_form").submit();
                            }
                        },
                        {
                            addClass: 'btn btn-primary', text: 'Отмена', onClick: function($noty) {
                                $noty.close();
                            }
                        }
                    ]
                });
                return;
            }
            jQuery("#calculate_form").submit();



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
		

		jQuery('#jform_client_contacts').blur(function () {
		    var client_phone = jQuery(this).val();
		    if(!empty(client_phone)) {
                jQuery.ajax({
                    url: "index.php?option=com_gm_ceiling&task=client.getClientByPhoneNumber",
                    data: {
                        phone: client_phone,
                        dealer_id: dealer_id
                    },
                    dataType: "json",
                    async: true,
                    success: function (data) {
                        console.log(data);
                        if (!empty(data)) {
                            jQuery('#found_client_info').append(data.client_name + '<br> <a href="/index.php?option=com_gm_ceiling&view=clientcard&id=' + data.id + '" class="btn btn-primary">В карточку</a>');
                            jQuery('#found_clients').show();
                            jQuery('#client_id').val(data.id);
                        } else {
                            jQuery('#found_clients').hide();
                            jQuery('#client_id').val('');
                        }
                    },
                    error: function (data) {
                        console.log(data);
                        noty({
                            theme: 'relax',
                            layout: 'center',
                            maxVisible: 5,
                            type: "error",
                            text: "Ошибка"
                        });
                    }
                });
            }
        });
	});

</script>
