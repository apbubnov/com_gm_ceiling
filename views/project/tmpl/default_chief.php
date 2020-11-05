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

?>
<style>
    .row{
        margin-bottom: 15px;
    }
</style>
<?= parent::getButtonBack(); ?>
<h2 class="center">Просмотр проекта</h2>
<?php if ($this->item) : ?>
    <?php $model = Gm_ceilingHelpersGm_ceiling::getModel('calculations'); ?>
    <?php $calculations = $model->getProjectItems($this->item->id); ?>
    <h4>Проект № <?php echo $this->item->id ?></h4>
    <div class="container">
        <div class="row">
            <div class="col-xlьв-6 item_fields">
                <div class="container">
                    <div class="row">
                        <div class="col-md-6">
                            <b>
                                <?php echo JText::_('COM_GM_CEILING_FORM_LBL_PROJECT_CLIENT_ID'); ?>
                            </b>
                        </div>
                        <div class="col-md-6">
                            <?php echo $this->item->client_id; ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <b>
                                <?php echo JText::_('COM_GM_CEILING_CLIENTS_CLIENT_CONTACTS'); ?>
                            </b>
                        </div>
                        <?php $contacts = $model->getClientPhone($this->item->client_id); ?>
                        <div class="col-md-6">
                            <?php foreach ($contacts as $phone){?>
                                 <div class="row">
                                     <div class="col-md-12">
                                         <?php echo $phone->client_contacts; ?>
                                     </div>
                                 </div>
                            <?php }?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <b>
                                <?php echo JText::_('COM_GM_CEILING_FORM_LBL_PROJECT_PROJECT_INFO'); ?>
                            </b>
                        </div>
                        <div class="col-md-6">
                            <?php echo $this->item->project_info; ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <b>
                                <?php echo JText::_('COM_GM_CEILING_PROJECTS_PROJECT_CALCULATION_DATE'); ?>
                            </b>
                        </div>
                        <div class="col-md-6">
                            <?php if(!empty($this->item->project_calculation_date) && $this->item->project_calculation_date != '0000-00-00 00:00:00'){
                                $jdate = new JDate(JFactory::getDate($this->item->project_calculation_date));
                                echo $jdate->format('d.m.Y H:i');
                            }
                            else{
                                echo '-';
                            }
                             ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <b>
                                Замерщик
                            </b>
                        </div>
                        <div class="col-md-6">
                            <?php if(!empty($this->item->project_calculator)){
                                echo JFactory::getUser($this->item->project_calculator)->name;
                            }
                            else{
                                echo '-';
                            }?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <b>
                                Монтаж
                            </b>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">

                        </div>
                        <div class="col-md-4">

                        </div>
                        <div class="col-md-4">

                        </div>
                    </div>

                </div>

                <input name="id" value="<?php echo $this->item->id; ?>" type="hidden">
                <input name="type" value="chief" type="hidden">

                <?php if ($this->item->project_status == 10) { ?>
                    <div class="row">
                        <div class="col-md-12">
                            <button class="btn btn-primary" id="change_mount">
                                    Изменить дату и время монтажа
                            </button>
                        </div>
                    </div>
                    <div class="row center" id="mount_container">
                        <div class="col-md-12">
                            <div id="mw_mount_calendar">

                            </div>
                        </div>
                    </div>
                    <a class="btn btn btn-primary"
                       id="change_data">Изменить дату и время монтажа
                    </a>
                    <?php
                } ?>
                <?php include_once('components/com_gm_ceiling/views/project/common_table.php'); ?>
        </div>
    </div>
    </div>

    <script type="text/javascript" src="/components/com_gm_ceiling/create_calculation.js"></script>
    <script type="text/javascript" src="/components/com_gm_ceiling/views/project/common_table.js?t=<?php echo time(); ?>"></script>
    <script type="text/javascript">
        var project_id = "<?php echo $this->item->id; ?>";
        jQuery(document).ready(function () {

            jQuery("#change_data").click(function () {
                jQuery("#mounter_wraper").toggle();
                jQuery("#title").toggle();
                jQuery(".calendar_wrapper").toggle();
                jQuery(".buttons_wrapper").toggle();
                jQuery("#mounting_date_control").show();
                jQuery("#calendar_wrapper").show();
            });

            var calendar_toggle = 0,
                month = <?php echo date("n"); ?>,
                year = <?php echo date("Y"); ?>;
            //jQuery("#jform_project_mounting_daypart").val(jQuery('#hours_list').val());
            jQuery("#jform_project_mounting_date").mask("99.99.9999");

            jQuery("#jform_project_mounter").change(function () {
                //update_calendar();
            });




            jQuery("#mounter_prev").click(function () {
                jQuery("#jform_project_mounter option:selected").prop("selected", false).prev("option").prop("selected", true);
                jQuery("#jform_project_mounter").change();
            });
            jQuery("#mounter_next").click(function () {
                jQuery("#jform_project_mounter option:selected").prop("selected", false).next("option").prop("selected", true);
                jQuery("#jform_project_mounter").change();
            });

            jQuery("#calendar_prev").click(function () {
                if (month == 1) {
                    month = 12;
                    year = year - 1;
                } else {
                    month = month - 1;
                }
                //update_calendar();
            });
            jQuery("#calendar_next").click(function () {
                if (month == 12) {
                    month = 1;
                    year = year + 1;
                } else {
                    month = month + 1;
                }
               // update_calendar();
            });
            //update_calendar();


        });


        var mountArray = {};

        function selectTimeF(obj) {
            obj = jQuery(obj);
            var sel = obj.val();
            var mountObj = jQuery('#selectMount').html('');
            jQuery.each(mountArray[sel], function (key, val) {
                var option = jQuery('<option>').html(val.mount).val(val.id);
                if (val.id == jQuery('#jform_project_mounting').val()) option.attr('selected', '');
                mountObj.append(option);
            });
        }

        function selectMountF(obj) {
            obj = jQuery(obj);
            var input = jQuery('input[name="project_mounter"]');
            input.val(obj.val());
        }

    </script>

    <?php
else:
    echo JText::_('COM_GM_CEILING_ITEM_NOT_LOADED');
endif;
?>
<script language="JavaScript">
    function PressEnter(your_text, your_event) {
        if (your_text != "" && your_event.keyCode == 13)
            jQuery("#update_discount").click();
    }


</script>
