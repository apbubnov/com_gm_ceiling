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
$mountModel = Gm_ceilingHelpersGm_ceiling::getModel('mount');
$calculationform_model = Gm_ceilingHelpersGm_ceiling::getModel('calculationform');
$reserve_model = Gm_ceilingHelpersGm_ceiling::getModel('reservecalculation');
$client_model = Gm_ceilingHelpersGm_ceiling::getModel('client');
$clients_dop_contacts_model = Gm_ceilingHelpersGm_ceiling::getModel('clients_dop_contacts');
$components_model = Gm_ceilingHelpersGm_ceiling::getModel('components');
$canvas_model = Gm_ceilingHelpersGm_ceiling::getModel('canvases');

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
foreach ($calculations as $calculation) {
    $calculation->dealer_canvases_sum = double_margin($calculation->canvases_sum, 0/*$this->item->gm_canvases_margin*/, $this->item->dealer_canvases_margin);
    $calculation->dealer_components_sum = double_margin($calculation->components_sum, 0 /*$this->item->gm_components_margin*/, $this->item->dealer_components_margin);
    $calculation->dealer_gm_mounting_sum = double_margin($calculation->mounting_sum, 0 /*$this->item->gm_mounting_margin*/, $this->item->dealer_mounting_margin);
    $calculation->dealer_self_canvases_sum = margin($calculation->canvases_sum, 0/*$this->item->gm_canvases_margin*/);
    $self_canvases_sum +=$calculation->dealer_self_canvases_sum;
    $calculation->dealer_self_components_sum = margin($calculation->components_sum, 0/* $this->item->gm_components_margin*/);
    $self_components_sum += $calculation->dealer_self_components_sum;
    $calculation->dealer_self_gm_mounting_sum = margin($calculation->mounting_sum, 0/* $this->item->gm_mounting_margin*/);
    $self_mounting_sum += $calculation->dealer_self_gm_mounting_sum;
    $calculation->calculation_total = $calculation->dealer_canvases_sum + $calculation->dealer_components_sum + $calculation->dealer_gm_mounting_sum;
    $calculation->calculation_total_discount = $calculation->calculation_total * ((100 - $calculation->discount) / 100);
    $calculation->n13 = $calculationform_model->n13_load($calculation->id);
    $calculation->n14 = $calculationform_model->n14_load($calculation->id);
    $calculation->n15 = $calculationform_model->n15_load($calculation->id);
    $calculation->n22 = $calculationform_model->n22_load($calculation->id);
    $calculation->n23 = $calculationform_model->n23_load($calculation->id);
    $calculation->n26 = $calculationform_model->n26_load($calculation->id);
    $calculation->n29 = $calculationform_model->n29_load($calculation->id);
    $total_square +=  $calculation->n4;
    $total_perimeter += $calculation->n5;
    $project_total += $calculation->calculation_total;
    $project_total_discount += $calculation->calculation_total_discount;
    $self_calc_data[$calculation->id] = array(
        "canv_data" => $calculation->dealer_self_canvases_sum,
        "comp_data" => $calculation->dealer_self_components_sum,
        "mount_data" => $calculation->dealer_self_gm_mounting_sum,
        "square" => $calculation->n4,
        "perimeter" => $calculation->n5,
        "sum" => $calculation->calculation_total,
        "sum_discount" => $calculation->calculation_total_discount
    );
    $calculation_total = $calculation->calculation_total;
    $calculation_total_discount =  $calculation->calculation_total_discount;
}
$self_calc_data = json_encode($self_calc_data);//массив с себестоимотью по каждой калькуляции
$project_self_total = $self_sum_transport + $self_components_sum + $self_canvases_sum + $self_mounting_sum; //общая себестоимость проекта

$mount_transport = $mountModel->getDataAll($this->item->dealer_id);
$min_project_sum = (empty($mount_transport->min_sum)) ? 0 : $mount_transport->min_sum;
$min_components_sum = (empty($mount_transport->min_components_sum)) ? 0 : $mount_transport->min_components_sum;

$project_total_discount_transport = $project_total_discount + $client_sum_transportt;

$del_flag = 0;
$project_total = $project_total + $client_sum_transport;
$project_total_discount = $project_total_discount  + $client_sum_transport;
    if (!empty($this->item->sb_order_id))
        $sb_project_id = $this->item->sb_order_id;
    else  $sb_project_id = 0;

    $recoil_map_project_model = Gm_ceilingHelpersGm_ceiling::getModel('recoil_map_project');
    $recoil_map_project = $recoil_map_project_model->getDataForProject($project_id);

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
    </div>
</div>

<?php if ($this->item) : ?>
    <?php include_once('components/com_gm_ceiling/views/project/common_table.php'); ?>
    <!-- чтото для клиенткого кабинета, потом стили применю, если не уберется это вообще -->
        <?php if ($user->dealer_type == 2) { ?>
            <button type="button" class="btn btn-primary" id="btn_pay">Оплатить с помощью карты</button>
        <?php } ?>
    <!-- конец -->
    <? if ($this->item->project_status >= 5 && $this->item->project_status != 12): ?>
        <button class="btn btn-primary btn-done" data-project_id="<?= $this->item->id; ?>" type="button">Выполнено</button>
    <? endif; ?>

    <script type="text/javascript" src="/components/com_gm_ceiling/create_calculation.js"></script>
    <script type="text/javascript" src="/components/com_gm_ceiling/views/project/common_table.js"></script>
    <script type="text/javascript">
        var $ = jQuery;
        var project_id = "<?php echo $this->item->id; ?>";
        jQuery(document).ready(function () {

            document.getElementById('add_calc').onclick = function()
            {
                create_calculation(<?php echo $this->item->id; ?>);
            };
            
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
                       
            jQuery("#btn_pay").click(function () {
                var id = "<?php echo $sb_project_id ?>";
                var number = <?php echo $project_id ?>;
                jQuery.ajax({
                    type: 'POST',
                    url: "index.php?option=com_gm_ceiling&task=get_paymanet_form&",
                    data: {
                        amount: <?php echo $project_total_discount * 100 ?>,
                        orderNumber: number.toString() + Date.now(),
                        description: "Количество потолков: "+<?php echo sizeof($calculations) ?>+
                        " на сумму " +<?php echo $project_total_discount ?>,
                        id: number
                    },
                    dataType: "json",
                    success: function (data) {
                        if (data.errorCode) {
                            var n = noty({
                                theme: 'relax',
                                layout: 'center',
                                maxVisible: 5,
                                type: "error",
                                text: data.ErrorMessage
                            });
                        }
                        if (data.formUrl) {
                            location.href = data.formUrl;
                        }
                    },
                    timeout: 10000,
                    error: function (data) {
                        var n = noty({
                            theme: 'relax',
                            layout: 'center',
                            maxVisible: 5,
                            type: "error",
                            text: "Ошибка при попытке оплаты, попробуйте позднее"
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