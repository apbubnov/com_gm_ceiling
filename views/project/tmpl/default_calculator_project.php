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
    $model = Gm_ceilingHelpersGm_ceiling::getModel('calculations');
    $calculations = $model->getProjectItems($this->item->id);
    $project_id = $this->item->id;
    foreach ($calculations as $calculation) {
        $calculation->dealer_gm_mounting_sum = double_margin($calculation->gm_mounting_sum, $this->item->gm_mounting_margin, $this->item->dealer_mounting_margin);
        $calculation->calculation_total = round($calculation->dealer_canvases_sum + $calculation->dealer_components_sum + $calculation->dealer_gm_mounting_sum, 2);
        $calculation->calculation_total_discount = round($calculation->calculation_total * ((100 - $this->item->project_discount) / 100), 2);
        $project_total += $calculation->calculation_total;
        $project_total_discount += $calculation->calculation_total_discount;
    }
    $client_model = Gm_ceilingHelpersGm_ceiling::getModel('client_phones');
    $phones = $client_model->getItemsByClientId($this->item->id_client);
    $project_total = round($project_total, 2);
    $project_total_discount = round($project_total_discount, 2);
    if (!empty($this->item->sb_order_id))
        $sb_project_id = $this->item->sb_order_id;
    else  $sb_project_id = 0;

    $recoil_map_project_model = Gm_ceilingHelpersGm_ceiling::getModel('recoil_map_project');
    $recoil_map_project = $recoil_map_project_model->getDataForProject($project_id);

?>

<style>
    .container {
        padding: 0;
    }
    #table1 {
        width: 100%;
        max-width: 330px;
        font-size: 13px;
    }
    #table1 button, #table1 a, #table1 input {
        font-size: 13px;
        max-width: 150px;
    }
    #table1 td, #table1 th {
        padding: 10px 5px;
    }
    .small_complectuushie a {
        padding: 6px 12px;
    }
    .wtf_padding {
        padding: 0;
    }
    @media screen and (min-width: 768px) {
        #table1 {
            width: 100%;
            max-width: 3000px;
            font-size: 1em;
        }
        #table1 td, #table1 th {
            padding: 15px;
        }
        #table1 button, #table1 a, #table1 input {
            font-size: 1em;
            width: auto;
            max-width: 200px;
        }
        .small_complectuushie a {
            padding: 8px 16px;
        }
        .wtf_padding {
            padding: 15px;
        }
    }
</style>

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

    <script type="text/javascript" src="/components/com_gm_ceiling/create_calculation.js"></script>

    <script>
        var $ = jQuery;
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
            jQuery("input[name^='include_calculation']").click(function () {
                if (jQuery(this).prop("checked")) {
                    jQuery(this).closest("tr").removeClass("not-checked");
                } else {
                    jQuery(this).closest("tr").addClass("not-checked");
                }
                calculate_total();
            });

            jQuery("#accept_project").click(function () {
                jQuery("input[name='project_verdict']").val(1);
                jQuery(".project_activation").show();
                jQuery("#mounting_date_control").show();
            });
            var flag = 0;
            jQuery("#sh_ceilings").click(function () {
                if (flag) {
                    jQuery(".section_ceilings").hide();
                    flag = 0;
                }
                else {
                    jQuery(".section_ceilings").show();
                    flag = 1;
                }
            });
            var flag1 = 0;
            jQuery("#sh_estimate").click(function () {
                if (flag1) {
                    jQuery(".section_estimate").hide();
                    flag1 = 0;
                }
                else {
                    jQuery(".section_estimate").show();
                    flag1 = 1;
                }
                jQuery(".section_estimate").each(function () {
                    var el = jQuery(this);
                    if (el.attr("vis") == "hide") el.hide();
                })
            });

            var flag2 = 0;
            jQuery("#sh_mount").click(function () {
                if (flag2) {
                    jQuery(".section_mount").hide();
                    flag2 = 0;
                }
                else {
                    jQuery(".section_mount").show();
                    flag2 = 1;
                }
                jQuery(".section_mount").each(function () {
                    var el = jQuery(this);
                    if (el.attr("vis") == "hide") el.hide();
                })
            });

            jQuery("#refuse_project").click(function () {
                jQuery("input[name='project_verdict']").val(0);
                jQuery(".project_activation").show();
                jQuery("#mounting_date_control").hide();
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

        function calculate_total() {
            var components_total = 0;
            gm_total = 0;
            dealer_total = 0;

            jQuery("input[name^='include_calculation']:checked").each(function () {
                var parent = jQuery(this).closest(".include_calculation"),
                    components_sum = parent.find("input[name^='components_sum']").val(),
                    gm_mounting_sum = parent.find("input[name^='gm_mounting_sum']").val(),
                    dealer_mounting_sum = parent.find("input[name^='dealer_mounting_sum']").val();

                components_total += parseFloat(components_sum);
                gm_total += parseFloat(gm_mounting_sum);
                dealer_total += parseFloat(dealer_mounting_sum);
            });

            jQuery("#components_total").text(components_total.toFixed(2));
            jQuery("#gm_total").text(gm_total.toFixed(2));
            jQuery("#dealer_total").text(dealer_total.toFixed(2));
        }

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