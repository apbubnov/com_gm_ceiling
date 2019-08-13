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

<?= parent::getButtonBack(); ?>
<h2 class="center">Просмотр проекта</h2>
<?php if ($this->item) : ?>
    <?php $model = Gm_ceilingHelpersGm_ceiling::getModel('calculations'); ?>
    <?php $calculations = $model->getProjectItems($this->item->id); ?>

    <div class="container">
        <div class="row">
            <div class="col-xl item_fields">
                <h4>Информация по проекту № <?php echo $this->item->id ?></h4>
                <form id="form-client"
                      action="/index.php?option=com_gm_ceiling&task=project.save_mount"
                      method="post" class="form-validate form-horizontal" enctype="multipart/form-data">
                    <table class="table">
                        <tr>
                            <th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_PROJECT_CLIENT_ID'); ?></th>
                            <td><?php echo $this->item->client_id; ?></td>
                        </tr>
                        <tr>
                            <th><?php echo JText::_('COM_GM_CEILING_CLIENTS_CLIENT_CONTACTS'); ?></th>
                            <? $contacts = $model->getClientPhone($this->item->client_id); ?>
                            <td><?php foreach ($contacts as $phone) echo $phone->client_contacts; ?></td>
                        </tr>
                        <tr>
                            <th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_PROJECT_PROJECT_INFO'); ?></th>
                            <td><?php echo $this->item->project_info; ?></td>
                        </tr>

                        <tr>
                            <th><?php echo JText::_('COM_GM_CEILING_PROJECTS_PROJECT_CALCULATION_DATE'); ?></th>
                            <td> <?php $jdate = new JDate(JFactory::getDate($this->item->project_calculation_date)); ?>
                                <?php if ($jdate->format('d.m.Y') == "00.00.0000" || $jdate->format('d.m.Y') == '30.11.-0001') { ?>
                                    -
                                <?php } else { ?>
                                    <?php echo $jdate->format('d.m.Y'); ?>
                                <?php } ?></td>
                        </tr>
                        <tr>
                            <th><?php echo JText::_('COM_GM_CEILING_PROJECTS_PROJECT_CALCULATION_DAYPART'); ?></th>
                            <td><?php $jdate = new JDate(JFactory::getDate($this->item->project_calculation_date)); ?>
                                <?php if ($jdate->format('H:i') == "00:00") { ?>
                                    -
                                <?php } else { ?>
                                    <?php echo $jdate->format('H:i'); ?>
                                <?php } ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Дата и время монтажа</th>
                            <td><?php $jdate = new JDate(JFactory::getDate($this->item->project_mounting_date)); ?>
                                <?php if ($this->item->project_mounting_date == "0000-00-00 00:00:00") { ?>
                                    -
                                <?php } else { ?>
                                    <?php echo $jdate->format('d.m.Y H:i'); ?>
                                <?php } ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Монтажная бригада</th>
                            <?php $mount_model = Gm_ceilingHelpersGm_ceiling::getModel('project'); ?>
                            <?php $mount = $mount_model->getMount($this->item->id); ?>
                            <td><?php echo $mount->name; ?></td>
                        </tr>
                    </table>
                    <?php $jdate = new JDate(JFactory::getDate($this->item->project_mounting_date)); ?>
                    <input name="id" value="<?php echo $this->item->id; ?>" type="hidden">
                    <input name="type" value="chief" type="hidden">
                    <input id="jform_project_mounting_from" type="hidden" name="jform[project_mounting_from]"
                           value="<?php echo $jdate->format('H:i'); ?>"/>
                    <input id="jform_project_mounting_date" type="hidden" name="jform[project_mounting_date]"
                           value="<?php echo $jdate->format('d.m.Y H:i'); ?>"/>
                    <input id="jform_project_mounter" type="hidden" name="jform[project_mounting]"
                           value="<?php echo ($mount->project_mounter) ? $mount->project_mounter : '1'; ?>"/>
                    <?php if ($this->item->project_status == 10) { ?>
                        <a class="btn btn btn-primary"
                           id="change_data">Изменить дату и время монтажа
                        </a>
                        <?php
                    } ?>
                    <div class="calendar_wrapper" style="display: none;">
                        <table>
                            <tr>
                                <td>
                                    <button id="calendar_prev" type="button" class="btn btn-secondary"> <<</button>
                                </td>
                                <td>
                                    <div id="calendar">
                                        <?php echo $calendar; ?>
                                    </div>
                                </td>
                                <td>
                                    <button id="calendar_next" type="button" class="btn btn-secondary"> >></button>
                                </td>
                            </tr>

                        </table>
                        <div class="control-group" id="save">
                            <div class="controls">
                                <button type="submit" class="validate btn btn-primary">
                                    Сохранить
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php include_once('components/com_gm_ceiling/views/project/common_table.php'); ?>
            </form>
        </div>
    </div>
    </div>

    <script type="text/javascript" src="/components/com_gm_ceiling/create_calculation.js"></script>
    <script type="text/javascript" src="/components/com_gm_ceiling/views/project/common_table.js"></script>
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

            document.getElementById('add_calc').onclick = function()
            {
                create_calculation(<?php echo $this->item->id; ?>);
            };



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
