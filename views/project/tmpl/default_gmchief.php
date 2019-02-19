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


/*_____________блок для всех моделей/models block________________*/
$calculationsModel = Gm_ceilingHelpersGm_ceiling::getModel('calculations');
$mountModel = Gm_ceilingHelpersGm_ceiling::getModel('mount');
$calculationform_model = Gm_ceilingHelpersGm_ceiling::getModel('calculationform');
$reserve_model = Gm_ceilingHelpersGm_ceiling::getModel('reservecalculation');
$client_model = Gm_ceilingHelpersGm_ceiling::getModel('client');
$phones_model = Gm_ceilingHelpersGm_ceiling::getModel('client_phones');
$clients_dop_contacts_model = Gm_ceilingHelpersGm_ceiling::getModel('clients_dop_contacts');
$components_model = Gm_ceilingHelpersGm_ceiling::getModel('components');
$canvas_model = Gm_ceilingHelpersGm_ceiling::getModel('canvases');
$projects_mounts_model = Gm_ceilingHelpersGm_ceiling::getModel('projects_mounts');

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
?>
<?= parent::getButtonBack(); ?>

    <div class="container">
        <div class="row">
            <h1>Отключено из-за переизбытка плохого кода</h1>
            <!--<div class="col-xl item_fields">
                <h4>Информация по проекту № <?php /*echo $this->item->id */?></h4>
                <form id="form-client"
                      action="/index.php?option=com_gm_ceiling&task=project.save_mount"
                      method="post" class="form-validate form-horizontal" enctype="multipart/form-data">
                    <table class="table">
                        <tr>
                            <th><?php /*echo JText::_('COM_GM_CEILING_FORM_LBL_PROJECT_CLIENT_ID'); */?></th>
                            <td><?php /*echo $this->item->client_id; */?></td>
                        </tr>
                        <tr>
                            <th><?php /*echo JText::_('COM_GM_CEILING_CLIENTS_CLIENT_CONTACTS'); */?></th>

                            <td><?php /*foreach ($contacts as $phone) echo $phone->client_contacts; */?></td>
                        </tr>
                        <tr>
                            <th><?php /*echo JText::_('COM_GM_CEILING_FORM_LBL_PROJECT_PROJECT_INFO'); */?></th>
                            <td><?php /*echo $this->item->project_info; */?></td>
                        </tr>

                        <tr>
                            <th><?php /*echo JText::_('COM_GM_CEILING_PROJECTS_PROJECT_CALCULATION_DATE'); */?></th>
                            <td> <?php /*$jdate = new JDate(JFactory::getDate($this->item->project_calculation_date)); */?>
                                <?php /*if ($jdate->format('d.m.Y') == "00.00.0000" || $jdate->format('d.m.Y') == '30.11.-0001') { */?>
                                    -
                                <?php /*} else { */?>
                                    <?php /*echo $jdate->format('d.m.Y'); */?>
                                <?php /*} */?></td>
                        </tr>
                        <tr>
                            <th><?php /*echo JText::_('COM_GM_CEILING_PROJECTS_PROJECT_CALCULATION_DAYPART'); */?></th>
                            <td><?php /*$jdate = new JDate(JFactory::getDate($this->item->project_calculation_date)); */?>
                                <?php /*if ($jdate->format('H:i') == "00:00") { */?>
                                    -
                                <?php /*} else { */?>
                                    <?php /*echo $jdate->format('H:i'); */?>
                                <?php /*} */?>
                            </td>
                        </tr>
                        <tr>
                            <th>Дата и время монтажа</th>
                            <td><?php /*$jdate = new JDate(JFactory::getDate($this->item->project_mounting_date)); */?>
                                <?php /*if ($this->item->project_mounting_date == "0000-00-00 00:00:00") { */?>
                                    -
                                <?php /*} else { */?>
                                    <?php /*echo $jdate->format('d.m.Y H:i'); */?>
                                <?php /*} */?>
                            </td>
                        </tr>
                        <tr>
                            <th>Монтажная бригада</th>
                            <?php /*$mount_model = Gm_ceilingHelpersGm_ceiling::getModel('project'); */?>
                            <?php /*$mount = $mount_model->getMount($this->item->id); */?>
                            <td><?php /*echo $mount->name; */?></td>
                        </tr>
                    </table>
                    <?php /*$jdate = new JDate(JFactory::getDate($this->item->project_mounting_date)); */?>
                    <input name="id" value="<?php /*echo $this->item->id; */?>" type="hidden">
                    <input name="type" value="gmchief" type="hidden">
                    <input id="jform_project_mounting_from" type="hidden" name="jform[project_mounting_from]"
                           value="<?php /*echo $jdate->format('H:i'); */?>"/>
                    <input id="jform_project_mounting_date" type="hidden" name="jform[project_mounting_date]"
                           value="<?php /*echo $jdate->format('d.m.Y H:i'); */?>"/>
                    <input id="jform_project_mounter" type="hidden" name="jform[project_mounting]"
                           value="<?php /*echo ($mount->project_mounter) ? $mount->project_mounter : '1'; */?>"/>
                    <?php /*if ($this->item->project_status >= 10 ) { */?>
                        <a class="btn btn btn-primary"
                           id="change_data">Изменить дату и время монтажа
                        </a>
                        <?php
/*                    } */?>
                    <div class="calendar_wrapper" style="display: none;">
                        <table>
                            <tr>
                                <td>
                                    <button id="calendar_prev" type="button" class="btn btn-secondary"> <<</button>
                                </td>
                                <td>
                                    <div id="calendar">
                                        <?php /*echo $calendar; */?>
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

                <?php /*include_once('components/com_gm_ceiling/views/project/common_table.php'); */?>
            </div>-->
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


            var calendar_toggle = 0,
                month = <?php echo date("n"); ?>,
                year = <?php echo date("Y"); ?>;

            jQuery("#jform_project_mounting_date").mask("99.99.9999");

            jQuery("#jform_project_mounter").change(function () {
                
            });

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
               
            });
            jQuery("#calendar_next").click(function () {
                if (month == 12) {
                    month = 1;
                    year = year + 1;
                } else {
                    month = month + 1;
                }
              
            });
           


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
