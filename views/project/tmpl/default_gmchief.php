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

if ($month1 == 12) {
    $month2 = 1;
    $year2 = $year1 + 1;
} else {
    $month2 = $month1 + 1;
    $year2 = $year1;
}

$jdate = new JDate($this->item->project_mounting_from);
$current_from = $jdate->format('Y-m-d H:i:s');

$jdate = new JDate($this->item->project_mounting_to);
$current_to = $jdate->format('Y-m-d H:i:s');

$calendar = Gm_ceilingHelpersGm_ceiling::draw_calendar($this->item->id, $this->item->project_mounter, $month1, $year1, $current_from, $current_to);
$calendar .= Gm_ceilingHelpersGm_ceiling::draw_calendar($this->item->id, $this->item->project_mounter, $month2, $year2, $current_from, $current_to);


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
                            <?php $contacts = $model->getClientPhone($this->item->client_id); ?>
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
                    <input name="type" value="gmchief" type="hidden">
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

            </div>
            </form>
        </div>
    </div>
    </div>

    <script type="text/javascript" src="/components/com_gm_ceiling/create_calculation.js"></script>
    <script type="text/javascript" src="/components/com_gm_ceiling/views/project/common_table.js"></script>
    <script type="text/javascript">
        var project_id = "<?php echo $this->item->id; ?>";
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


            var preloader = '<?=parent::getPreloaderNotJS();?>';
            var calendar_toggle = 0,
                month = <?php echo date("n"); ?>,
                year = <?php echo date("Y"); ?>;
            jQuery('body').append(preloader);
            //jQuery("#jform_project_mounting_daypart").val(jQuery('#hours_list').val());
            jQuery("#jform_project_mounting_date").mask("99.99.9999");

            jQuery("#jform_project_mounter").change(function () {
                update_calendar();
            });



            var hours_list = "<select id='hours_list'>";
            hours_list += "<option value='09:00:00'>09:00</option>";
            hours_list += "<option value='10:00:00'>10:00</option>";
            hours_list += "<option value='11:00:00'>11:00</option>";
            hours_list += "<option value='12:00:00'>12:00</option>";
            hours_list += "<option value='13:00:00'>13:00</option>";
            hours_list += "<option value='14:00:00'>14:00</option>";
            hours_list += "<option value='15:00:00'>15:00</option>";
            hours_list += "<option value='16:00:00'>16:00</option>";
            hours_list += "<option value='17:00:00'>17:00</option>";
            hours_list += "<option value='18:00:00'>18:00</option>";
            hours_list += "<option value='19:00:00'>19:00</option>";
            hours_list += "<option value='20:00:00'>20:00</option>";
            hours_list += "<option value='21:00:00'>21:00</option>";
            hours_list += "</select>";

            listening();



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
                update_calendar();
            });
            jQuery("#calendar_next").click(function () {
                if (month == 12) {
                    month = 1;
                    year = year + 1;
                } else {
                    month = month + 1;
                }
                update_calendar();
            });
            update_calendar();


        });

        function update_calendar() {
            jQuery(".PRELOADER_GM").addClass('PRELOADER_GM_OPACITY');
            jQuery.ajax({
                type: 'POST',
                url: "index.php?option=com_gm_ceiling&task=update_calendar",
                data: {
                    project_id: <?php echo $this->item->id; ?>,
                    project_mounter: jQuery("#jform_project_mounter").val(),
                    current_from: jQuery("#jform_project_mounting_date").val()

                    //current_to: jQuery("#jform_project_mounting_to").val()
                },
                success: function (data) {
                    jQuery("#calendar").html(data);
                    jQuery(".PRELOADER_GM").remove();
                    listening();
                },
                dataType: "text",
                timeout: 10000,
                error: function () {
                    var n = noty({
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "error",
                        text: "Ошибка при попытке обновить календарь. Сервер не отвечает"
                    });
                }
            });
        }

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

        function listening() {
            jQuery(".b-calendar__day").click(function () {
                var this_td = jQuery(this),
                    date = this_td.data('date'),
                    project_mounter = jQuery("#selectMount").val();
                // console.log(project_mounter);
                //Задаем дату начала
                //if(calendar_toggle == 0) {

                jQuery.ajax({
                    type: 'POST',
                    url: "index.php?option=com_gm_ceiling&task=get_calendar",
                    data: {
                        date: date
                        //project_mounter: project_mounter
                    },
                    success: function (data) {

                        data = JSON.parse(data);
                        var result = null;
                        if (data.message == 0) {
                            var selectTime = jQuery('<select class="form-control" >').attr({
                                'id': 'selectTime',
                                'onchange': 'selectTimeF(this);'
                            });
                            var selectMount = jQuery('<select class="form-control">').attr({
                                'id': 'selectMount',
                                'onchange': 'selectMountF(this);'
                            });
                            jQuery.each(data.info, function (key, val) {
                                mountArray[key] = val;
                                var option = jQuery('<option>').html(key).val(key);
                                var currentDate = (jQuery("#jform_project_mounting_date").val()).replace(/(\d+)\.(\d+)\.(\d+)/, '$3-$2-$1');
                                if (key == jQuery('#jform_project_mounting_from').val() && date == currentDate) option.attr('selected', '');
                                selectTime.append(option);
                            });
                            var select = jQuery('<div>').append(selectTime);
                            var mount = jQuery('<div>').append(selectMount);

                            result = select.html() + mount.html();
                        }
                        else if (data.message == 1) result = data.info;

                        noty({
                            layout: 'center',
                            modal: true,
                            text: '<br>Выберите время <strong>начала</strong> монтажа:<br>' + result,
                            buttons: [
                                {
                                    addClass: 'btn btn-danger', text: 'Отмена', onClick: function ($noty) {
                                    $noty.close();
                                }
                                },
                                {
                                    addClass: 'btn btn-primary', text: 'ОК', onClick: function ($noty) {
                                    jQuery(".b-calendar__day").removeClass("current_project");
                                    jQuery("input[name='jform[project_mounting_date]']").val(date);
                                    jQuery("input[name='jform[project_mounting_from]']").val(date + " " + jQuery('#selectTime').val());
                                    jQuery("input[name='jform[project_mounting]']").val(jQuery('#selectMount').val());
                                    //jQuery("input[name ='jform_project_mounting_to']").val(date + " " + jQuery('#hours_list').val());
                                    calendar_toggle = 1;
                                    this_td.addClass("current_project");
                                    $noty.close();
                                }
                                }
                            ]
                        });
                        jQuery('#selectTime').change();
                    },
                    dataType: "text",
                    timeout: 10000,
                    error: function () {
                        var n = noty({
                            theme: 'relax',
                            layout: 'center',
                            maxVisible: 5,
                            type: "error",
                            text: "Ошибка при попытке получить список занятых в этот день монтажников. Сервер не отвечает"
                        });
                    }
                });

            })
        };
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
