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

$model = Gm_ceilingHelpersGm_ceiling::getModel('calculations');
$calculations = $model->getProjectItems($this->item->id);

foreach ($calculations as $calculation) {
    $calculation->dealer_canvases_sum = double_margin($calculation->canvases_sum, $this->item->gm_canvases_margin, $this->item->dealer_canvases_margin);
    $calculation->dealer_components_sum = double_margin($calculation->components_sum, $this->item->gm_components_margin, $this->item->dealer_components_margin);
    $calculation->dealer_gm_mounting_sum = double_margin($calculation->gm_mounting_sum, $this->item->gm_mounting_margin, $this->item->dealer_mounting_margin);

    $calculation->calculation_total = round($calculation->dealer_canvases_sum + $calculation->dealer_components_sum + $calculation->dealer_gm_mounting_sum, 2);
    $calculation->calculation_total_discount = round($calculation->calculation_total * ((100 - $this->item->project_discount) / 100), 2);
    $project_total += $calculation->calculation_total;
    $project_total_discount += $calculation->calculation_total_discount;

}

$project_total = round($project_total, 2);
$project_total_discount = round($project_total_discount, 2);
$client_model = Gm_ceilingHelpersGm_ceiling::getModel('client_phones');
$phones = $client_model->getItemsByClientId($this->item->id_client);
?>
<?= parent::getButtonBack(); ?>

<h2 class="center">Просмотр проекта</h2>
<?php if ($this->item) : ?>

    <form id="form-client" action="/index.php?option=com_gm_ceiling&task=project.activate&type=gmcalculator&subtype=calendar" method="post" class="form-validate form-horizontal" enctype="multipart/form-data">
        <div class="container">
            <div class="row">
                <div class="col-xl-6 item_fields">
                    <h4>Информация по проекту № <?= $this->item->id;?></h4>
                    <table class="table">
                        <tr>
                            <th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_PROJECT_CLIENT_ID'); ?></th>
                            <td><a href="http://test1.gm-vrn.ru/index.php?option=com_gm_ceiling&view=clientcard&id=<?=$this->item->id_client;?>"><?php echo $this->item->client_id; ?></a></td>
                        </tr>
                        <tr>
                            <th><?php echo JText::_('COM_GM_CEILING_CLIENTS_CLIENT_CONTACTS'); ?></th>
                            <td><?foreach ($phones as $contact):?><a href="tel:+<?=$contact->phone;?>"><?=$contact->phone;?></a><?endforeach;?></td>
                        </tr>
                        <tr>
                            <th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_PROJECT_PROJECT_INFO'); ?></th>
                            <td><a target="_blank" href="https://yandex.ru/maps/?mode=search&text=<?=$this->item->project_info;?>"><?=$this->item->project_info;?></a></td>
                        </tr>
                        <tr>
                            <th><?php echo JText::_('COM_GM_CEILING_PROJECTS_PROJECT_CALCULATION_DATE'); ?></th>
                            <td><?php if ($this->item->project_mounting_date == '0000-00-00 00:00:00') echo "-"; else echo $this->item->project_mounting_date; ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <?php include_once('components/com_gm_ceiling/views/project/common_table.php'); ?>

    </form>

    <script type="text/javascript" src="/components/com_gm_ceiling/create_calculation.js"></script>

    <script>
        jQuery(document).ready(function () {

            document.getElementById('add_calc').onclick = function()
            {
                create_calculation(<?php echo $this->item->id; ?>);
            };

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

            jQuery("#refuse_project").click(function () {
                jQuery("input[name='project_verdict']").val(0);
                jQuery(".project_activation").show();
                jQuery("#mounting_date_control").hide();
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
    </script>

<?php
    else:
        echo JText::_('COM_GM_CEILING_ITEM_NOT_LOADED');
    endif;
?>
