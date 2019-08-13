<?php
 /**
     * @version    CVS: 0.1.7
     * @package    Com_Gm_ceiling
     * @author     SpectralEye <Xander@spectraleye.ru>
     * @copyright  2016 SpectralEye
     * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
 // No direct access
    defined('_JEXEC') or die;

    $canEdit = JFactory::getUser()->authorise('core.edit', 'com_gm_ceiling');
    if (!$canEdit && JFactory::getUser()->authorise('core.edit.own', 'com_gm_ceiling')) {
        $canEdit = JFactory::getUser()->id == $this->item->created_by;
    }

    Gm_ceilingHelpersGm_ceiling::create_client_common_estimate($this->item->id);
    Gm_ceilingHelpersGm_ceiling::create_common_estimate_mounters($this->item->id);
    Gm_ceilingHelpersGm_ceiling::create_estimate_of_consumables($this->item->id);

    $user = JFactory::getUser();
    $dealer = JFactory::getUser($user->dealer_id);
    $project_id = $this->item->id;

/*_____________блок для всех моделей/models block________________*/ 
$calculationsModel = Gm_ceilingHelpersGm_ceiling::getModel('calculations');
$model_api_phones = Gm_ceilingHelpersGm_ceiling::getModel('api_phones');
/*________________________________________________________________*/
$transport = Gm_ceilingHelpersGm_ceiling::calculate_transport($this->item->id);
$client_sum_transport = $transport['client_sum'];
$self_sum_transport = $transport['mounter_sum'];//идет в монтаж
$self_calc_data = [];
$self_canvases_sum = 0;
$self_components_sum = 0;
$self_mounting_sum = 0;
$project_self_total = 0;
$project_total = 0;
$project_total_discount = 0;
$total_square = 0;
$total_perimeter = 0;
$calculation_total_discount = 0;
$calculations = $calculationsModel->new_getProjectItems($this->item->id);
if (!empty($this->item->calcs_mounting_sum)) {
    $service_mount = get_object_vars(json_decode($this->item->calcs_mounting_sum));
}
if(!empty($service_mount)){
    $self_sum_transport = Gm_ceilingHelpersGm_ceiling::calculate_transport($this->item->id,"service")['mounter_sum'];
}

$del_flag = 0;
$project_total = $project_total + $client_sum_transport;
$project_total_discount = $project_total_discount  + $client_sum_transport;
    if (!empty($this->item->sb_order_id))
        $sb_project_id = $this->item->sb_order_id;
    else  $sb_project_id = 0;

    $recoil_map_project_model = Gm_ceilingHelpersGm_ceiling::getModel('recoil_map_project');
    $recoil_map_project = $recoil_map_project_model->getDataForProject($project_id);
if(!empty($this->item->api_phone_id))
    $reklama = $model_api_phones->getDataById($this->item->api_phone_id)->name;
else
    $reklama = "";
$all_advt = $model_api_phones->getAdvt();
?>

<?= parent::getButtonBack(); ?>
<input name="url" value="" type="hidden">
<h2 class="center" style="margin-top: 15px; margin-bottom: 15px;">Проект № <?php echo $this->item->id ?></h2>
<div class="row">
    <div class="col-xs-12 col-md-6 no_padding">
        <form id="form-client" action="/index.php?option=com_gm_ceiling&task=project.activate&type=calculator&subtype=calendar" method="post" class="form-validate form-horizontal" enctype="multipart/form-data">
            <h4>Информация о клиенте</h4>
            <table class="table_info" style="margin-bottom: 25px;">
                <tr>
                    <th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_PROJECT_CLIENT_ID'); ?></th>
                    <td><?php echo $this->item->client_id; ?></td>
                </tr>
                <tr>
                    <th><?php echo JText::_('COM_GM_CEILING_CLIENTS_CLIENT_CONTACTS'); ?></th>
                    <td>
                        <?php
                            foreach ($phones AS $contact) {
                                echo $contact->phone;
                                echo "<br>";
                            } 
                        ?>
                    </td>
                </tr>
                <tr>
                    <th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_PROJECT_PROJECT_INFO'); ?></th>
                    <td><?php echo $this->item->project_info; ?></td>
                </tr>
                <tr>
                    <th><?php echo JText::_('COM_GM_CEILING_PROJECTS_PROJECT_CALCULATION_DATE'); ?></th>
                    <td><?php if ($this->item->project_mounting_date == '0000-00-00 00:00:00') echo "-"; else echo $this->item->project_mounting_date; ?></td>
                </tr>
                <tr>
                    <th>
                        Реклама
                    </th>
                    <td>
                        <?php echo $reklama;?>
                    </td>
                </tr>
                <tr>
                    <td>
                        <button class="btn btn-primary" type="button" id="change_rek">Изменить рекламу</button>
                    </td>
                </tr>
                <?php if(!empty($this->item->project_calculator)):?>
                    <tr>
                        <th>Замерщик</th>
                        <td><?php echo JFactory::getUser($this->item->project_calculator)->name;?></td>
                    </tr>
                <?php endif;?>
                <?php if(!empty($this->item->project_mounter)):?>
                    <tr>
                        <th>Монтажная бригада</th>
                        <td><?php echo JFactory::getUser($this->item->project_mounter)->name;?></td>
                    </tr>
                <?php endif;?>
            </table>
        </form>
        <?php include_once('components/com_gm_ceiling/views/project/project_notes.php'); ?>

    </div>
</div>
<div class="modal_window_container" id="mw_container">
    <button type="button" class="close_btn" id="close_mw"><i class="fa fa-times fa-times-tar" aria-hidden="true"></i></button>
    <div id="mw_advt" class="modal_window">
        <h4>Изменение/добавление рекламы</h4>
        <label>Выберите или добавьте новую рекламу</label>
        <div class="row">  
            <div class="col-xs-6 col-md-6">
                <p>
                    <label><strong>Выбрать:</strong></label>
                </p>
                <select id="advt_choose">
                    <option value="0">Выберите рекламу</option>
                    <?php if (!empty($all_advt)) foreach ($all_advt as $item) { ?>
                        <option value="<?php echo $item->id ?>"><?php echo $item->name ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="col-xs-6 col-md-6">
                 <p>
                    <label><strong>Добавить:</strong></label>
                </p>
                 <div id="new_advt_div">
                    <p><input id="new_advt_name" placeholder="Название рекламы"></p>
                    <button type="button" class="btn btn-primary" id="add_new_advt">Добавить</button>
                </div>
            </div>
        </div>
        <br>
        <button class="btn btn-primary" id="save_advt" type="button">Сохранить </button>
    </div>
</div>
<?php if ($this->item) : ?>
    <?php include_once('components/com_gm_ceiling/views/project/common_table.php'); ?>
    <? if ($this->item->project_status >= 5 && $this->item->project_status != 12): ?>
        <button class="btn btn-primary btn-done" data-project_id="<?= $this->item->id; ?>" type="button">Выполнено</button>
    <? endif; ?>

    <script type="text/javascript" src="/components/com_gm_ceiling/create_calculation.js"></script>
    <script type="text/javascript" src="/components/com_gm_ceiling/views/project/common_table.js"></script>
    <script type="text/javascript">
        var $ = jQuery;
        var project_id = "<?php echo $this->item->id; ?>";
        var client_id = "<?php echo $this->item->id_client;?>";
        jQuery('#mw_container').click(function(e) { // событие клика по веб-документу
            var div = jQuery("#mw_advt");
            if (!div.is(e.target) // если клик был не по нашему блоку
                && div.has(e.target).length === 0) { // и не по его дочерним элементам
                jQuery("#close_mw").hide();
                jQuery("#mw_container").hide();
                jQuery(".modal_window").hide();
            }
        });

        jQuery(document).ready(function () {
            $(".head_comsumables").click(function () {
                e = $(this);
                if (e.val() === "") e.val(true);
                if (e.val() === false) {
                    e.find("i").removeClass("fa-sort-desc").addClass("fa-sort-asc");
                    $(".section_comsumables").show();
                } else {
                    e.find("i").removeClass("fa-sort-asc").addClass("fa-sort-desc");
                    $(".section_comsumables").hide();
                }
                e.val(!e.val());
            });

            var id = "<?php echo $sb_project_id; ?>";
            orderId = id != 0 ? id : "";
            jQuery.ajax({
                type: 'POST',
                url: "index.php?option=com_gm_ceiling&task=get_paymanet_status&",
                data: {
                    orderId: orderId
                },
                dataType: "json",
                success: function (data) {
                    if (data.OrderStatus == 2 && data.ErrorMessage == "Успешно") {
                        change_project_status(<?php echo $project_id;?>, 14);
                    }
                },
                timeout: 10000,
                error: function (data) {
                    console.log("error", data);
                }
            });

            jQuery(".btn-done").click(function () {
                var button = jQuery(this);
                noty({
                    layout: 'center',
                    type: 'warning',
                    modal: true,
                    text: 'Вы уверены, что хотите отметить договор выполненным?',
                    killer: true,
                    buttons: [
                        {
                            addClass: 'btn btn-success', text: 'Выполнен', onClick: function ($noty) {
                                jQuery.get(
                                    "/index.php?option=com_gm_ceiling&task=project.done",
                                    {
                                        project_id: button.data("project_id"),
                                        check: 1
                                    },
                                    function(data) {
                                        location.reload();
                                    }
                                );
                                $noty.close();
                            }
                        },
                        {
                            addClass: 'btn', text: 'Отмена', onClick: function ($noty) {
                                $noty.close();
                            }
                        }
                    ]
                });

            });
            jQuery("#change_rek").click(function(){
            jQuery("#close_mw").show();
            jQuery("#mw_container").show();
            jQuery("#mw_advt").show('slow');
        });


        jQuery("#save_advt").click(function() {
            if (jQuery("#advt_choose").val() == '0' || jQuery("#advt_choose").val() == '') {
                noty({
                    timeout: 2000,
                    theme: 'relax',
                    layout: 'center',
                    maxVisible: 5,
                    type: "warning",
                    text: "Укажите рекламу"
                });
                jQuery("#advt_choose").focus();
                return;
            }
            jQuery.ajax({
                url: "index.php?option=com_gm_ceiling&task=project.save_advt",
                data: {
                    project_id: project_id,
                    api_phone_id: jQuery("#advt_choose").val(),
                    client_id: client_id
                },
                dataType: "json",
                async: true,
                success: function(data) {
                    document.getElementById('save_advt').style.display = 'none';
                    document.getElementById('advt_choose').disabled = 'disabled';
                    noty({
                        timeout: 2000,
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "success",
                        text: "Реклама сохранена"
                    });
                    location.reload();
                },
                error: function(data) {
                    console.log(data);
                    noty({
                        timeout: 2000,
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "error",
                        text: "Ошибка"
                    });
                }
            });
        });

        jQuery("#add_new_advt").click(function() {
            jQuery.ajax({
                url: "index.php?option=com_gm_ceiling&task=addNewAdvt",
                data: {
                    name: jQuery("#new_advt_name").val()
                },
                dataType: "json",
                async: true,
                success: function (data) {
                    select = document.getElementById('advt_choose');
                    var opt = document.createElement('option');
                    opt.selected = true;
                    opt.value = data.id;
                    opt.innerHTML = data.name;
                    select.appendChild(opt);
                    jQuery("#new_advt_name").val('');
                },
                error: function (data) {
                    console.log(data);
                    var n = noty({
                        timeout: 2000,
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "error",
                        text: "ошибка"
                    });
                }
            });
        });
        });

        function change_project_status(project_id, project_status) {
            jQuery.ajax({
                type: 'POST',
                url: "index.php?option=com_gm_ceiling&task=change_status&",
                data: {
                    id: project_id,
                    project_status: project_status
                },
                dataType: "json",
                success: function (data) {
                },
                timeout: 10000,
                error: function (data) {
                    var n = noty({
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "error",
                        text: "Ошибка!"
                    });
                }
            });
        }
    </script>

<?php
    else:
        echo JText::_('COM_GM_CEILING_ITEM_NOT_LOADED');
    endif;
?>