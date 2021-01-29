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

$jinput = JFactory::getApplication()->input;
$user = JFactory::getUser();
$userId = (empty($jinput->getInt('id'))) ? $user->get('id') : $jinput->getInt('id');
$userType = JFactory::getUser($userId)->dealer_type;
//$model_dealer_info = Gm_ceilingHelpersGm_ceiling::getModel('dealer_info');
//$margin = $model_dealer_info->getData();
$model_mount = Gm_ceilingHelpersGm_ceiling::getModel('mount');
$mount = $model_mount->getDataAll($userId);
$gm_mount = json_encode($model_mount->getDataAll(1));

if (!$user->getDealerInfo()->update_check) {
    $user->setDealerInfo(["update_check" => true]);
}

$model_prices = Gm_ceilingHelpersGm_ceiling::getModel('prices');
$dealer_jobs = $model_prices->getJobsDealer($userId);
$gm_price = json_encode($model_prices->getJobsDealer(1));

$grouppedJobs = $model_prices->getJobsGroupedByType($userId);

$model_dealer_info = Gm_ceilingHelpersGm_ceiling::getModel('dealer_info');
$dealer_info = $model_dealer_info->getDealerInfo($userId);
$style = "";
if ($userType == 7) {
    $style = "style=\"display:none;\"";
}

$modelUsers = Gm_ceilingHelpersGm_ceiling::getModel('users');
$mountService = $modelUsers->getUsersByGroupAndDealer(26,$user->dealer_id);
$mountService = (!empty($mountService)) ? JFactory::getUser($mountService[0]->id) : '';
$disable = !empty($mountService) && in_array('26',$mountService->groups) ? 'disabled' : '';
?>


<style>
    body {
        color: #414099;
    }

    .caption1 {
        text-align: center;
        padding: 15px 0;
        margin-bottom: 0;
        color: #414099;
    }

    .caption2 {
        text-align: center;
        height: auto;
        padding: 10px 0;
        border: 0;
        margin-bottom: 0;
        color: #414099;
    }

    input[type="text"] {
        padding: .5rem .75rem;
        border: 1px solid rgba(0, 0, 0, .15);
        border-radius: .25rem;
    }

    .control-label {
        margin-top: 7px;
        margin-bottom: 0;
    }
    .left{
        text-align: left !important;
    }

    .act_btn{
        width: 230px;
        margin-bottom: 0.5em;
        text-align: center;
    }

</style>


<?php if ($userType != 7) { ?>
    <div class="row">
        <div class="col-md-6 col-xs-0">
            <button class="btn btn-primary" id="btn_new_user">Создать пользователя</button>
        </div>
        <div class="col-md-3 col-xs-12" style="text-align:right;margin-bottom: 1em !important;">
            <a href="/index.php?option=com_users&view=profile&layout=edit" class="btn btn-large btn-primary">Изменить
                личные данные</a>
        </div>
        <div class="col-md-3 col-xs-12" style="text-align:right;margin-bottom: 1em !important;">
            <a href="/index.php?option=com_gm_ceiling&view=dealerprofile&type=integration"
               class="btn btn-large btn-primary">Интеграция</a>
        </div>
    </div>
<?php } ?>

<div id="dealer_form" action="/index.php?option=com_gm_ceiling&task=dealer.updatedata" method="post"
     class="form-validate form-horizontal" enctype="multipart/form-data">
    <input type="hidden" name="jform[dealer_id]" id="jform_dealer_id" value="<?php echo $userId ?>">

    <div <?= $style ?>>
        <h3>Маржинальность</h3>
        <h5 class="caption2">Укажите, какой процент прибыли от заказа Вы желаете получать</h5>
        <div class="row">
            <div class="col-md-4">
                <div class="control-group">
                    <div class="control-label">
                        <label id="-lbl" for="jform_dealer_canvases_margin" class="hasTooltip required">от
                            полотен</label>
                    </div>
                    <div class="controls">
                        <input type="text" name="jform[dejform_dealer_canvases_marginaler_canvases_margin]"
                               id="jform_dealer_canvases_margin" class="required" style="width:100%;" size="3" required
                               aria-required="true" value="<?= $dealer_info->dealer_canvases_margin; ?>"/>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="control-group">
                    <div class="control-label">
                        <label id="jform_dealer_components_margin-lbl" for="jform_dealer_components_margin">от
                            комплектующих</label>
                    </div>
                    <div class="controls">
                        <input type="text" name="jform[dealer_components_margin]" id="jform_dealer_components_margin"
                               value="<?= $dealer_info->dealer_components_margin; ?>" class="required"
                               style="width:100%;" size="3" required aria-required="true"/>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="control-group">
                    <div class="control-label">
                        <label id="jform_dealer_mounting_margin-lbl" for="jform_dealer_mounting_margin">от
                            монтажа</label>
                    </div>
                    <div class="controls">
                        <input type="text" name="jform[dealer_mounting_margin]" id="jform_dealer_mounting_margin"
                               value="<?= $dealer_info->dealer_mounting_margin; ?>" class="required" style="width:100%;"
                               size="3" required aria-required="true"/>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row" style="margin-bottom: 1em !important;">
    <div class="col-md-1 col-xs-12" style="margin-top: 1em !important;">
        <?= parent::getButtonBack();?>
    </div>
    <div class="col-md-3 col-xs-12" style="margin-top: 1em !important;text-align: center;">
        <h4> Прайс монтажа</h4>
    </div>
    <div class="col-md-3 col-xs-12" style="margin-top: 1em !important;">
        <button id="fill_default" class="btn btn-primary" type="button">Заполнить по умолчанию</button>
    </div>
    <div class="col-md-2 col-xs-12" style="margin-top: 1em !important;text-align:center">
        <button id="reset_ap" class="btn btn-primary" type="button">Сбросить</button>
    </div>
    <?php if($userType !=7){ ?>
        <div class="col-md-3 col-xs-12" style="margin-top: 1em !important;text-align:center">
            <button id="create_ms" class="btn btn-primary" type="button" <?=$disable;?>> Создать Монтажную службу</button>
            <?php if(!empty($mountService)&&in_array('26',$mountService->groups)){?>
                <div class="row">
                    <div class="col-md-8">
                        <span>МС уже создана</span>
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-danger btn-sm" id="remove_service" type="button">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                </div>
            <?php }?>
        </div>
    <?php }?>
</div>


<div class="row" style="margin-bottom: 1em !important;">
    <?php foreach ($grouppedJobs as $mountType) { ?>
        <div class="row mount_type">
            <div class="col-md-12 col-xs-12" style="margin-bottom: 1em !important;">
                <h4>
                    <div class="col-md-4 col-xs-10">
                        <?= $mountType->title; ?>
                    </div>
                    <div class="col-md-2 col-xs-2" style="text-align: left">
                        <i class="fas fa-angle-down"></i>
                    </div>
                </h4>
            </div>
            <div class="jobs" style="display:none;">
                <?php foreach ($mountType->jobs as $value) { ?>
                    <!--<div class="row" style="margin-top: 5px;">-->
                    <div class="col-md-6 col-xs-12" style="margin-bottom: 5px; !important;">
                        <div class="col-md-6 control-label"><label><?= $value->name; ?></label></div>
                        <div class="col-md-6 left">
                            <input type="text" class="required input" style="width:100%;" size="3" required
                                   aria-required="true" value="<?= $value->price; ?>" data-id="<?= $value->dp_id; ?>"
                                   id="<?= $value->id; ?>"/>
                        </div>
                    </div>
                    <!--</div>-->
                <?php } ?>
            </div>
        </div>

    <?php } ?>

</div>
<div class="row" <?= $style; ?>>
    <h3 class="caption1">Минимальная сумма заказа</h3>
    <div class="col-md-4">
        <div class="control-group">
            <div class="control-label">
                <label id="jform_min_sum-lbl" for="jform_min_sum">Минимальная сумма</label>
            </div>
            <div class="controls">
                <input type="text" name="jform[min_sum]" id="jform_min_sum" value="<?= $dealer_info->min_sum; ?>"
                       style="width:100%;" size="3" required aria-required="true"/>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="control-label">
            <label id="jform_min_sum-lbl" for="jform_min_sum">Сумма транспорта по городу</label>
        </div>
        <div class="controls">
            <input type="text" name="jform[transport]" id="jform_transport" value="<?= $dealer_info->transport; ?>"
                   style="width:100%;" size="3" required aria-required="true"/>
        </div>
    </div>
    <div class="col-md-4">
        <div class="control-label">
            <label id="jform_min_sum-lbl" for="jform_min_sum">Сумма транспорта вне города (за 1 км.)</label>
        </div>
        <div class="controls">
            <input type="text" name="jform[distance]" id="jform_distance" value="<?= $dealer_info->distance; ?>"
                   style="width:100%;" size="3" required aria-required="true"/>
        </div>
    </div>
</div>
<div class="col-md-12" style="margin-top:15px;">
    <div class="col-md-4"></div>
    <div class="col-md-4">
        <button type="button" id="btn_save" class="btn btn-primary" style="width:100%;"> Сохранить</button>
    </div>
    <div class="col-md-4"></div>
</div>
<div class="modal_window_container" id="mw_container">
    <button type="button" id="btn_close" class="btn-close">
        <i class="fa fa-times fa-times-tar" aria-hidden="true"></i>
    </button>
    <div class="modal_window" id="mw_add_user">
        <h4>Добавление сотрудника</h4>
        <div class="row">
            <div class="col-md-6 col-xs-12">
                <div class="row" style="margin-bottom: 1em !important;">
                    <div class="col-md-6 col-xs-12">
                        <label for="user_name">ФИО пользователя</label>
                    </div>
                    <div class="col-md-6 col-xs-12">
                        <input class="form-control" id="user_name">
                    </div>
                </div>
                <div class="row" style="margin-bottom: 1em !important;">
                    <div class="col-md-6 col-xs-12">
                        <label for="user_phone">Номер телефона</label>
                    </div>
                    <div class="col-md-6 col-xs-12">
                        <input class="form-control" id="user_phone">
                    </div>
                </div>
                <div class="row" style="margin-bottom: 1em !important;">
                    <div class="col-md-6 col-xs-12">
                        <label for="user_email">Email</label>
                    </div>
                    <div class="col-md-6 col-xs-12">
                        <input class="form-control" id="user_email">
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xs-12">
                <label>Выберите тип пользователя:</label>
                <div class="row left">
                    <div class="col-md-12">
                        <input type="checkbox" id="manager" data-group="13" class="inp-cbx groups"  style="display: none">
                        <label for="manager" class="cbx">
                            <span>
                                <svg width="12px" height="10px" viewBox="0 0 12 10">
                                    <polyline points="1.5 6 4.5 9 10.5 1"></polyline>
                                </svg>
                            </span>
                            <span>Менеджер</span>
                        </label>
                    </div>
                </div>
                <div class="row left">
                    <div class="col-md-12">
                        <input type="checkbox" id="gauger" data-group="21" class="inp-cbx groups"  style="display: none">
                        <label for="gauger" class="cbx">
                            <span>
                                <svg width="12px" height="10px" viewBox="0 0 12 10">
                                    <polyline points="1.5 6 4.5 9 10.5 1"></polyline>
                                </svg>
                            </span>
                            <span>Замерщик</span>
                        </label>
                    </div>
                </div>
                <div class="row left">
                    <div class="col-md-12">
                        <input type="checkbox" id="nms" data-group="12" class="inp-cbx groups"  style="display: none">
                        <label for="nms" class="cbx">
                            <span>
                                <svg width="12px" height="10px" viewBox="0 0 12 10">
                                    <polyline points="1.5 6 4.5 9 10.5 1"></polyline>
                                </svg>
                            </span>
                            <span>Начальник МС</span>
                        </label>
                    </div>
                </div>

            </div>
        </div>
        <div class="row center">
            <div class="col=md-12">
                <span style="color:red;display: none;" id="error_text"></span>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <button class="btn btn-primary" id="create_user"> Создать </button>
            </div>
        </div>
    </div>
</div>
<script>
    var user = JSON.parse('<?= quotemeta(json_encode($user)); ?>'),
        serviceId = '<?=$mountService->id;?>';
    jQuery(document).mouseup(function (e) { // событие клика по веб-документу
        var div = jQuery("#mw_add_user");
        if (!div.is(e.target) // если клик был не по нашему блоку
            && div.has(e.target).length === 0) { // и не по его дочерним элементам
            jQuery("#btn_close").hide();
            jQuery("#mw_container").hide();
            div.hide();
        }
    });

    jQuery(document).ready(function () {
        jQuery('#user_phone').mask('+7(999)999-99-99');
        var gm_price = JSON.parse('<?= $gm_price;?>');

        jQuery("#fill_default").click(function () {
            fill_inputs("fill");
        });

        jQuery("#reset_ap").click(function () {
            fill_inputs("reset");
        });

        function fill_inputs(type) {
            switch (type) {
                case 'reset':
                    jQuery.each(jQuery('.input'), function (index, value) {
                        value.value = 0;
                    });
                    break;
                case 'fill':
                    var i = 0;
                    jQuery.each(jQuery('.input'), function (index, value) {
                        value.value = gm_price[i].price;
                        i++;
                    });
                    break;
            }
        }

        document.getElementById('btn_save').onclick = function () {
            if (document.getElementById('jform_dealer_canvases_margin') && document.getElementById('jform_dealer_components_margin')
                && document.getElementById('jform_dealer_mounting_margin')) {
                if (!(/^\d+$/g).test(document.getElementById('jform_dealer_canvases_margin').value) ||
                    document.getElementById('jform_dealer_canvases_margin').value < 0 ||
                    document.getElementById('jform_dealer_canvases_margin').value > 99) {
                    noty({
                        theme: 'relax',
                        timeout: 3000,
                        layout: 'topCenter',
                        maxVisible: 5,
                        type: "warning",
                        text: "Значение маржинальности должно быть в пределе [0..99]"
                    });
                    document.getElementById('jform_dealer_canvases_margin').focus();
                    return;
                }
                if (!(/^\d+$/g).test(document.getElementById('jform_dealer_components_margin').value) ||
                    document.getElementById('jform_dealer_components_margin').value < 0 ||
                    document.getElementById('jform_dealer_components_margin').value > 99) {
                    noty({
                        theme: 'relax',
                        timeout: 3000,
                        layout: 'topCenter',
                        maxVisible: 5,
                        type: "warning",
                        text: "Значение маржинальности должно быть в пределе [0..99]"
                    });
                    document.getElementById('jform_dealer_components_margin').focus();
                    return;
                }
                if (!(/^\d+$/g).test(document.getElementById('jform_dealer_mounting_margin').value) ||
                    document.getElementById('jform_dealer_mounting_margin').value < 0 ||
                    document.getElementById('jform_dealer_mounting_margin').value > 99) {
                    noty({
                        theme: 'relax',
                        timeout: 3000,
                        layout: 'topCenter',
                        maxVisible: 5,
                        type: "warning",
                        text: "Значение маржинальности должно быть в пределе [0..99]"
                    });
                    document.getElementById('jform_dealer_mounting_margin').focus();
                    return;
                }
            }
            collectDataTable();
        };

        jQuery('.mount_type').click(function () {
            var divJobs = jQuery(this).find('.jobs'),
                arrow = jQuery(this).find('i');
            divJobs.toggle()
            if (divJobs.is(':visible')) {
                arrow.removeClass('fa-angle-down');
                arrow.addClass('fa-angle-up');
            } else {
                arrow.removeClass('fa-angle-up');
                arrow.addClass('fa-angle-down');
            }
        });

        jQuery('#create_ms').click(function () {
            var dealer_id = user.dealer_id,
                groups = (dealer_id == 1) ? 17 : 12,
                nmsExist = checkExistNMS(groups,dealer_id);
            if(!nmsExist) {
                noty({
                    theme: 'relax',
                    layout: 'center',
                    timeout: false,
                    type: "info",
                    text: "У вас отсутсвует начальник монтажной службы. Вам автоматически будет добавлен данный тип пользователя. Продолжить?",
                    buttons: [
                        {
                            addClass: 'btn btn-primary act_btn', text: 'Продолжить', onClick: function ($noty) {
                                $noty.close();
                                addNmsGroup(dealer_id,groups);
                                createMountServiceUser();
                            }
                        },
                        {
                            addClass: 'btn btn-primary act_btn', text: 'Создать начальника МС', onClick: function ($noty) {
                                jQuery('#mw_container').show();
                                jQuery('#nms').attr('checked', true);
                                jQuery('#btn_close').show();
                                jQuery('#mw_add_user').show();
                                $noty.close();
                            }
                        },
                        {
                            addClass: 'btn btn-primary act_btn', text: 'Отмена', onClick: function ($noty) {
                                $noty.close();
                            }
                        }
                    ]
                });
            }
            else{
                createMountServiceUser();
            }
        });

        jQuery('#btn_new_user').click(function () {
            jQuery('#mw_container').show();
            jQuery('#btn_close').show();
            jQuery('#mw_add_user').show();
        });

        jQuery('#create_user').click(function () {
            var checkboxes = jQuery('.groups:checked'),
                groups = [],
                name = jQuery('#user_name').val(),
                phone = jQuery('#user_phone').val(),
                email = jQuery('#user_email').val();
            if(!empty(checkboxes)){
                jQuery.each(checkboxes,function(i,c){
                    groups.push(jQuery(c).data('group'));
                });
            }

            if(empty(name)){
                jQuery('#user_name').css('border-color','red');
                jQuery('#error_text').text('Не заполнено поле ФИО!');
                jQuery('#error_text').show();
                return;
            }
            jQuery('#brigade_name').css('border-color','');
            if(empty(phone)){
                jQuery('#phone').css('border-color','red');
                jQuery('#error_text').text('Не заполнено поле Номер телефона!');
                jQuery('#error_text').show();
                return;
            }
            jQuery('#phone').css('border-color','');
            if(groups.length == 0){
                jQuery('#error_text').text('Не выбран тип пользоавтеля!');
                jQuery('#error_text').show();
                return;
            }
            jQuery('#error_text').text('');
            jQuery('#error_text').hide();

            jQuery.ajax({
                type: 'POST',
                url: "index.php?option=com_gm_ceiling&task=users.createUser",
                data: {
                    name: name,
                    phone: phone,
                    email: email,
                    groups: groups

                },
                success: function(data) {
                    noty({
                        theme: 'relax',
                        timeout: 2000,
                        layout: 'center',
                        maxVisible: 5,
                        type: "success",
                        text: "Пользоаватель создан!"
                    });
                    setTimeout(function(){location.reload();},2000);

                },
                dataType: "json",
                timeout: 10000,
                error: function(data) {
                    noty({
                        theme: 'relax',
                        timeout: 2000,
                        layout: 'center',
                        maxVisible: 5,
                        type: "error",
                        text: "Ошибка при создании!"
                    });
                }
            });
        });

        jQuery(document).on('click','#remove_service',function(){
            var deaelr_id = '';
            jQuery.ajax({
                type: 'POST',
                url: "index.php?option=com_gm_ceiling&task=users.removeUserGroup",
                data: {
                    id: serviceId,
                    group: 26
                },
                success: function(data) {
                    location.reload();
                },
                dataType: "json",
                timeout: 10000,
                error: function(data) {
                    noty({
                        theme: 'relax',
                        timeout: 2000,
                        layout: 'center',
                        maxVisible: 5,
                        type: "error",
                        text: "Ошибка при создании!"
                    });
                }
            });
        });
    });

    var array = [];
    var data = [];

    function collectDataTable() {
        array = [];
        jQuery.each(jQuery('.input'), function (index, value) {
            array.push({
                job_id: value.id,
                price: value.value.replace(',', '.').replace(/[^\d\.]/g, '')
            });
        });

        data = [];
        data = {
            canvases_margin: jform_dealer_canvases_margin.value.replace(',', '.').replace(/[^\d\.]/g, ''),
            components_margin: jform_dealer_components_margin.value.replace(',', '.').replace(/[^\d\.]/g, ''),
            mounting_margin: jform_dealer_mounting_margin.value.replace(',', '.').replace(/[^\d\.]/g, ''),
            min_sum: jform_min_sum.value.replace(',', '.').replace(/[^\d\.]/g, ''),
            transport: jform_transport.value.replace(',', '.').replace(/[^\d\.]/g, ''),
            distance: jform_distance.value.replace(',', '.').replace(/[^\d\.]/g, '')
        };
        console.log(data);
        saveData();
    }

    function saveData() {
        jQuery.ajax({
            type: 'POST',
            url: "index.php?option=com_gm_ceiling&task=dealer.updatedata",
            data: {
                array: array,
                dealer_id: <?= $userId ?>,
                data: data
            },
            success: function (data) {
                console.log(data);
                noty({
                    timeout: 2000,
                    theme: 'relax',
                    layout: 'center',
                    maxVisible: 5,
                    type: "success",
                    text: "Данные сохранены!"
                });
            },
            dataType: "json",
            async: false,
            timeout: 10000,
            error: function (data) {
                var n = noty({
                    timeout: 2000,
                    theme: 'relax',
                    layout: 'center',
                    maxVisible: 5,
                    type: "error",
                    text: "Ошибка!"
                });
            }
        });
    }

    function createMountServiceUser() {
        jQuery.ajax({
            url: "index.php?option=com_gm_ceiling&task=users.createMountServiceUser",
            data: {},
            dataType: "json",
            async: true,
            success: function (data) {
                location.reload();
            },
            error: function (data) {
                console.log(data);
                var n = noty({
                    timeout: 2000,
                    theme: 'relax',
                    layout: 'topCenter',
                    maxVisible: 5,
                    type: "error",
                    text: "Ошибка сервера"
                });
            }
        });
    }

    function checkExistNMS(groups,dealer_id){
        var result = false;
        jQuery.ajax({
            url: "index.php?option=com_gm_ceiling&task=users.getUsersByGroupAndDealer",
            data: {
                groups: groups ,
                dealer_id: dealer_id
            },
            dataType: "json",
            async: false,
            success: function (data) {
                if(data.length > 0){
                    result = true;
                }
            },
            error: function (data) {
                console.log(data);
                var n = noty({
                    timeout: 2000,
                    theme: 'relax',
                    layout: 'topCenter',
                    maxVisible: 5,
                    type: "error",
                    text: "Ошибка сервера"
                });
            }
        });

        return result;
    }

    function addNmsGroup(dealer_id,groups){
        jQuery.ajax({
            url: "index.php?option=com_gm_ceiling&task=users.addGroupToExistUser",
            data: {
                user_id: dealer_id ,
                group_id: groups
            },
            dataType: "json",
            async: false,
            success: function (data) {
            },
            error: function (data) {
                console.log(data);
                var n = noty({
                    timeout: 2000,
                    theme: 'relax',
                    layout: 'topCenter',
                    maxVisible: 5,
                    type: "error",
                    text: "Ошибка сервера"
                });
            }
        });
    }


</script>