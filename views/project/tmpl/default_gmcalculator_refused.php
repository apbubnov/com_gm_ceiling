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

$projects_mounts_model = Gm_ceilingHelpersGm_ceiling::getModel('projects_mounts');
$client_model = Gm_ceilingHelpersGm_ceiling::getModel('client_phones');


$json_mount = $this->item->mount_data;
$stages = [];
if(!empty($this->item->mount_data)){
    $mount_types = $projects_mounts_model->get_mount_types();
    $this->item->mount_data = json_decode(htmlspecialchars_decode($this->item->mount_data));
    foreach ($this->item->mount_data as $value) {
        $value->stage_name = $mount_types[$value->stage];
        if(!array_key_exists($value->mounter,$stages)){
            $stages[$value->mounter] = array((object)array("stage"=>$value->stage,"time"=>$value->time));
        }
        else{
            array_push($stages[$value->mounter],(object)array("stage"=>$value->stage,"time"=>$value->time));
        }
    }
}
//----------------------------------------------------------------------------------
$server_name = $_SERVER['SERVER_NAME'];
$project_notes = Gm_ceilingHelpersGm_ceiling::getProjectNotes($this->item->id);

$phones = $client_model->getItemsByClientId($this->item->id_client);

?>
<?=parent::getButtonBack();?>
<?php if ($this->item) : ?>

    <div class="container">
        <div class="row">
            <div class="col-xl-6 item_fields">
                <h4>Информация по проекту</h4>
                <form id="form-client" action="/index.php?option=com_gm_ceiling&task=project.activate&type=gmcalculator&subtype=refused" method="post" class="form-validate form-horizontal" enctype="multipart/form-data">
                    <input type="hidden" name="project_id" value="<?php echo $this->item->id; ?>">
                    <table class="table">
                        <tr>
                            <th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_PROJECT_CLIENT_ID'); ?></th>
                            <td><a href="http://<?php echo $server_name;?>/index.php?option=com_gm_ceiling&view=clientcard&id=<?=$this->item->id_client;?>"><?php echo $this->item->client_id; ?></a></td>
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
                            <td>
                                <?php if($this->item->project_calculation_date == "0000-00-00 00:00:00") { ?>
                                    -
                                <?php } else { ?>
                                    <?php $jdate = new JDate($this->item->project_calculation_date); ?>
                                    <?php echo $jdate->format('d.m.Y'); ?>
                                <?php } ?>
                            </td>
                        </tr>
                        <tr>
                            <th><?php echo JText::_('COM_GM_CEILING_PROJECTS_PROJECT_CALCULATION_DAYPART'); ?></th>
                            <td><?php if($this->item->project_calculation_date == "0000-00-00 00:00:00") { ?>
                                    -
                                <?php } else { ?>
                                    <?php $jdate = new JDate($this->item->project_calculation_date); ?>
                                    <?php echo $jdate->format('H:i'); ?>
                                <?php } ?>
                            </td>
                        </tr>
                        <?php if($this->type === "gmcalculator" && $this->subtype === "refused"){ ?>
                            <button type="submit" id="return_project" class="btn btn btn-success">
                                Вернуть на стадию замера
                            </button>
                        <?php } ?>
                    </table>
                </form>
                <?php include_once('components/com_gm_ceiling/views/project/project_notes.php'); ?>

            </div>
        </div>
    </div>

    <?php include_once('components/com_gm_ceiling/views/project/common_table.php'); ?>

    <script type="text/javascript" src="/components/com_gm_ceiling/create_calculation.js"></script>
    <script type="text/javascript" src="/components/com_gm_ceiling/views/project/common_table.js"></script>
    <script type="text/javascript">
        var project_id = "<?php echo $this->item->id; ?>";
        jQuery(document).ready(function(){

            document.getElementById('add_calc').onclick = function()
            {
                create_calculation(<?php echo $this->item->id; ?>);
            };

            jQuery("#jform_project_mounting_date").mask("99.99.9999");

            jQuery("input[name^='include_calculation']").click(function(){
                if( jQuery( this ).prop("checked") ) {
                    jQuery( this ).closest("tr").removeClass("not-checked");
                } else {
                    jQuery( this ).closest("tr").addClass("not-checked");
                }
                calculate_total();
            });

        });

        function calculate_total(){
            var project_total = 0,
                project_total_discount = 0;

            jQuery("input[name^='include_calculation']:checked").each(function(){
                var parent = jQuery( this ).closest(".include_calculation"),
                    calculation_total = parent.find("input[name^='calculation_total']").val(),
                    calculation_total_discount = parent.find("input[name^='calculation_total_discount']").val();

                project_total += parseFloat(calculation_total);
                project_total_discount += parseFloat(calculation_total_discount);
            });

            jQuery("#project_total").text(project_total.toFixed(2));
            jQuery("#project_total_discount").text(project_total_discount.toFixed(2));
        }
    </script>

<?php
else:
    echo JText::_('COM_GM_CEILING_ITEM_NOT_LOADED');
endif;
