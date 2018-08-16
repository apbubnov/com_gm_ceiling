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

    JHtml::_('behavior.keepalive');
    //JHtml::_('behavior.tooltip');
    JHtml::_('behavior.formvalidation');

    // Load admin language file
    $lang = JFactory::getLanguage();
    $lang->load('com_gm_ceiling', JPATH_SITE);
    $doc = JFactory::getDocument();
    $doc->addScript(JUri::base() . '/media/com_gm_ceiling/js/form.js');

    $user = JFactory::getUser();
    $userId = $user->get('id');

    /*_____________блок для всех моделей/models block________________*/ 
    $calculationsModel = Gm_ceilingHelpersGm_ceiling::getModel('calculations');
    $mountModel = Gm_ceilingHelpersGm_ceiling::getModel('mount');
    $calculationform_model = Gm_ceilingHelpersGm_ceiling::getModel('calculationform');
    $reserve_model = Gm_ceilingHelpersGm_ceiling::getModel('reservecalculation');
    $client_model = Gm_ceilingHelpersGm_ceiling::getModel('client');
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
    $extra_spend_array = Gm_ceilingHelpersGm_ceiling::decode_extra($this->item->extra_spend);
    $penalty_array = Gm_ceilingHelpersGm_ceiling::decode_extra($this->item->penalty);
    $bonus_array = Gm_ceilingHelpersGm_ceiling::decode_extra($this->item->bonus);

    // календарь
    $month1 = date("n");
    $year1 = date("Y");
    if ($month1 == 12) {
        $month2 = 1;
        $year2 = $year1;
        $year2++;
    } else {
        $month2 = $month1;
        $month2++;
        $year2 = $year1;
    }
    if ($this->item->project_status == 1) {
        $whatCalendar = 0;
        $FlagCalendar = [3, $user->dealer_id];
    } elseif ($this->item->project_status != 11 || $this->item->project_status != 12 || $this->item->project_status == 17) {
        $whatCalendar = 1;
        $FlagCalendar = [2, $user->dealer_id];
    }
    $calendar1 = Gm_ceilingHelpersGm_ceiling::DrawCalendarTar($userId, $month1, $year1, $FlagCalendar);
    $calendar2 = Gm_ceilingHelpersGm_ceiling::DrawCalendarTar($userId, $month2, $year2, $FlagCalendar);
    //----------------------------------------------------------------------------------

    // все бригады
    $Allbrigades = $calculationsModel->FindAllbrigades($user->dealer_id);
    $AllMounters = [];
    if (count($Allbrigades) == 0) {
        array_push($Allbrigades, ["id"=>$userId, "name"=>$user->get('name')]);
        array_push($AllMounters, ["id"=>$userId, "name"=>$user->get('name')]);
    } else {
        // все монтажники
        $masid = [];
        foreach ($Allbrigades as $value) {
            array_push($masid, $value->id);
        }
        foreach ($masid as $value) {
            if (strlen($where) == 0) {
                $where = "'".$value."'";
            } else {
                $where .= ", '".$value."'";                
            }
        }
        $AllMounters = $calculationsModel->FindAllMounters($where);
    }
    //----------------------------------------------------------------------------------

    // все замерщики
    if ($user->dealer_id == 1 && !in_array("14", $user->groups)) {
        $AllGauger = $calculationsModel->FindAllGauger($user->dealer_id, 22);
    } else {
        $AllGauger = $calculationsModel->FindAllGauger($user->dealer_id, 21);
    }
    //----------------------------------------------------------------------------------

    $mount_sum = 0;

    echo parent::getPreloader();

?>

<?=parent::getButtonBack();?>

<link rel="stylesheet" href="/components/com_gm_ceiling/views/projectform/tmpl/css/style.css" type="text/css" />

<style>
    #jform_project_mounter-lbl {
        display: none;
    }
</style>

<h2>Просмотр проекта</h2>
<form id="form-client">
<?php if ($this->item) { ?>
    <?php include_once('components/com_gm_ceiling/views/project/common_table.php'); ?>
    <input name="project_id" id="project_id" value="<?php echo $this->item->id; ?>" type="hidden">
    <div class="container">
        <div class="row" style="padding-top: 1em;">
            <div class="col-xl-6 item_fields project-edit front-end-edit">
                <form id="form-project" action="<?php echo JRoute::_('index.php?option=com_gm_ceiling&task=project.approve'); ?>" method="post" class="form-validate form-horizontal" enctype="multipart/form-data">
                    <h4>Информация по проекту № <?php echo $this->item->id ?></h4>
                    <input type="hidden" name="jform[id]" value="<?php echo $this->item->id; ?>"/>
                    <input type="hidden" name="jform[ordering]" value="<?php echo $this->item->ordering; ?>"/>
                    <input type="hidden" name="jform[state]" value="<?php echo $this->item->state; ?>"/>
                    <input type="hidden" name="jform[checked_out]" value="<?php echo $this->item->checked_out; ?>"/>
                    <input type="hidden" name="jform[checked_out_time]" value="<?php echo $this->item->checked_out_time; ?>"/>
                    <?php if ($this->item->project_status == 3) { ?>
                        <input type="hidden" name="jform[project_status]" value="4"/>
                    <?php } ?>
                    <?php if (empty($this->item->created_by)): ?>
                        <input type="hidden" name="jform[created_by]" value="<?php echo JFactory::getUser()->id; ?>"/>
                    <?php else: ?>
                        <input type="hidden" name="jform[created_by]" value="<?php echo $this->item->created_by; ?>"/>
                    <?php endif; ?>
                    <?php if (empty($this->item->modified_by)): ?>
                        <input type="hidden" name="jform[modified_by]" value="<?php echo JFactory::getUser()->id; ?>"/>
                    <?php else: ?>
                        <input type="hidden" name="jform[modified_by]" value="<?php echo $this->item->modified_by; ?>"/>
                    <?php endif; ?>
                    <?php $jdate = new JDate(JFactory::getDate($this->item->project_mounting_date)); ?>
                    <input name = "jform[project_new_calc_date]" id = "jform_project_new_calc_date" value="<?php if (isset($this->item->project_calculation_date)) { echo $this->item->project_calculation_date; } ?>" type="hidden">
                    <input name = "jform[project_gauger]" id = "jform_project_gauger" value="<?php if ($this->item->project_calculator != null) { echo $this->item->project_calculator; } ?>" type="hidden">
                    <input id="jform_project_gauger_old" type="hidden" name="jform_project_gauger_old" value="<?php if ($this->item->project_calculator != null) { echo $this->item->project_calculator; } else { echo "0"; } ?>"/>
                    <input id="jform_project_calculation_date_old" type="hidden" name="jform_project_calculation_date_old" value="<?php if (isset($this->item->project_calculation_date)) { echo $this->item->project_calculation_date;} ?>"/>
                    <input id="jform_project_mounting_date" type="hidden" name="jform[project_mounting_date]" value="<?php if (isset($this->item->project_mounting_date)) { echo $this->item->project_mounting_date; } ?>"/>
                    <input id="jform_project_mounter" type="hidden" name="jform[project_mounting]" value="<?php if (isset($this->item->project_mounter)) { echo $this->item->project_mounter; } ?>"/>
                    <input id="jform_project_mounter_old" type="hidden" name="jform_project_mounting_old" value="<?php if (isset($this->item->project_mounter)) { echo $this->item->project_mounter; } ?>"/>
                    <input id="jform_project_mounting_date_old" type="hidden" name="jform_project_mounting_date_old" value="<?php if (isset($this->item->project_mounting_date)) { echo $this->item->project_mounting_date; } ?>"/>
                    <input type="hidden" name="option" value="com_gm_ceiling"/>
                    <input type="hidden" name="task" value="project.approve"/>
                    <input id="mount" name="mount" type='hidden' value='<?php echo $json_mount ?>'>
                    <?php echo JHtml::_('form.token'); ?>
                    <table class="table">
                        <tr>
                            <th>Номер договора</th>
                            <td><?php echo $this->item->id; ?></td>
                        </tr>
                        <tr>
                            <th>Статус проекта</th>
                            <td>
                                <?php 
                                    if ($this->item->project_status == 1) {
                                        $status = "Ждет замера";
                                    } else if ($this->item->project_status == 5) {
                                        $status = "В производстве";
                                    } else if ($this->item->project_status == 6) {
                                        $status = "На раскрое";
                                    } else if ($this->item->project_status == 7) {
                                        $status = "Укомплектован";
                                    } else if ($this->item->project_status == 8) {
                                        $status = "Выдан";
                                    } else if ($this->item->project_status == 9) {
                                        $status = "Деактевирован";
                                    } else if ($this->item->project_status == 10) {
                                        $status = "Ожидает монтаж";
                                    } else if ($this->item->project_status == 11) {
                                        $status = "Монтаж выполнен";
                                    } else if ($this->item->project_status == 12) {
                                        $status = "Закрыт";
                                    } else if ($this->item->project_status == 13) {
                                        $status = "Ожидает оплаты";
                                    } else if ($this->item->project_status == 14) {
                                        $status = "Оплачен";
                                    } else if ($this->item->project_status == 15) {
                                        $status = "Отказ от сотруднечества";
                                    } else if ($this->item->project_status == 16) {
                                        $status = "Монтаж";
                                    } else if ($this->item->project_status == 17) {
                                        $status = "Монтаж недовыполнен";
                                    } else if ($this->item->project_status == 19) {
                                        $status = "Собран";
                                    } else if ($this->item->project_status == 22) {
                                        $status = "Отказ от производства";
                                    } else if ($this->item->project_status == 4) {
                                        $status = "Не назначен на монтаж";
                                    }
                                    echo $status; 
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <?php if ($this->item->project_status == 1) { ?>
                                <th>Дата замера</th>
                                <td>
                                    <?php if ($this->item->project_calculation_date == "0000-00-00 00:00:00") { ?>
                                        -
                                    <?php } else { ?>
                                        <?php $jdate = new JDate(JFactory::getDate($this->item->project_calculation_date)); ?>
                                        <?php echo $jdate->format('d.m.Y H:i'); ?>
                                    <?php } ?>
                                </td>
                            <?php } /*else if ($this->item->project_status == 11 || $this->item->project_status == 12 || $this->item->project_status != 17) { ?>
                                <th>Дата монтажа</th>
                                <td>
                                    <?php $jdate = new JDate(JFactory::getDate($this->item->project_mounting_date)); ?>
                                    <?php echo $jdate->format('d.m.Y H:i'); ?>
                                </td>
                            <?php } else { ?>
                                <th>Удобная дата монтажа для клиента</th>
                                <td>
                                    <?php if ($this->item->project_mounting_date == "0000-00-00 00:00:00") { ?>
                                        -
                                    <?php } else { ?>
                                        <?php $jdate = new JDate(JFactory::getDate($this->item->project_mounting_date)); ?>
                                        <?php echo $jdate->format('d.m.Y H:i'); ?>
                                    <?php } ?>
                                </td>
                            <?php }*/ ?>
                        </tr>
                        <tr>
                            <th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_PROJECT_CLIENT_ID'); ?></th>
                            <td><?php echo $this->item->client_id; ?></td>
                        </tr>
                        <tr>
                            <th><?php echo JText::_('COM_GM_CEILING_CLIENTS_CLIENT_CONTACTS'); ?></th>
                            <?php 
                                $mod = Gm_ceilingHelpersGm_ceiling::getModel('projectform');
                                $contact = $mod->getData($this->item->id);
                            ?>
                            <td><?php echo $contact->client_contacts; ?></td>
                        </tr>
                        <tr>
                            <th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_PROJECT_PROJECT_INFO'); ?></th>
                            <td><?php echo $this->item->project_info; ?></td>
                        </tr>
                        <tr>
                            <th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_PROJECT_GM_MANAGER_NOTE'); ?></th>
                            <td><?php echo $this->item->gm_manager_note; ?></td>
                        </tr>
                        <tr>
                            <th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_PROJECT_GM_CALCULATOR_NOTE'); ?></th>
                            <td><?php echo $this->item->gm_calculator_note; ?></td>
                        </tr>
                        <tr>
                            <th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_PROJECT_GM_CHIEF_NOTE'); ?></th>
                            <td><textarea name="jform[gm_chief_note]" id="jform_gm_chief_note" placeholder="Примечание начальника МС ГМ" aria-invalid="false"><?php echo $this->item->gm_chief_note; ?></textarea></td>
                        </tr>
                        <tr>
                            <th>Замерщик</th>
                            <?php 
                                $gauger_model = Gm_ceilingHelpersGm_ceiling::getModel('project');
                                $gauger = $gauger_model->getGauger($this->item->id); 
                            ?>
                            <td><?php echo $gauger->name; ?></td>
                        </tr>
                       <?php if(!empty($this->item->mount_data)):?>
                            <tr>
                                <th colspan="3" style="text-align: center;">Монтаж</th>
                            </tr>
                            <?php foreach ($this->item->mount_data as $value) { ?>                          
                                <tr>
                                    <th><?php echo $value->time;?></th>
                                    <td><?php echo $value->stage_name;?></td>
                                    <td><?php echo JFactory::getUser($value->mounter)->name;?></td>
                                </tr>
                            <?php }?>
                        <?php endif;?>
                    </table>
                    <?php if ($this->item->project_status == 1) { ?>
                        <h4 style="text-align:center;">Изменить замерщика, время и дату замера</h4>
                        <div class="calendar_wrapper" style="background: #ffffff">
                            <table>
                                <tr>
                                    <td>
                                        <button id="button-prev" type="button" class="btn btn-primary"><i class="fa fa-arrow-left" aria-hidden="true"></i></button>
                                    </td>
                                    <td style="width: 100%">
                                        <div id="calendar1" style="padding: 1em">
                                            <?php echo $calendar1; ?>
                                        </div>
                                        <div id="calendar2" style="padding: 1em">
                                            <?php echo $calendar2; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <button id="button-next" type="button" class="btn btn-primary"><i class="fa fa-arrow-right" aria-hidden="true"></i></button>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    <?php } else if ($this->item->project_status != 11 || $this->item->project_status != 12 || $this->item->project_status == 17) { ?>
                        <h4 style="text-align:center;">Назначить/изменить монтажную бригаду, время и дату</h4>
                        <div id="calendar_mount" align="center"></div>
                    <?php } ?>
                    <?php if ($userId == $user->dealer_id) { ?>
                        <input name="type" value="chief" type="hidden">
                    <?php } else { ?>
                        <input name="type" value="gmchief" type="hidden">
                    <?php } ?>
                    <div class="control-group">
                        <div class="controls">
                            <button type="submit" class="validate btn btn-primary">Сохранить</button>
                            <?php if ($whatCalendar == 0) { ?>
                                <a class="btn btn-success"
                                    href="<?php echo JRoute::_('index.php?option=com_gm_ceiling&view=projects&type=gmchief&subtype=gaugings'); ?>"
                                    title="">Вернуться к замерам
                                </a>
                            <?php } else { ?>
                                <a class="btn btn-success"
                                    href="<?php if ($this->item->project_status == 4)  echo JRoute::_('index.php?option=com_gm_ceiling&view=projects&type=chiefprojects');
                                    elseif ($userId == $user->dealer_id)  echo JRoute::_('index.php?option=com_gm_ceiling&view=projects&type=chief');
                                    else echo JRoute::_('index.php?option=com_gm_ceiling&view=projects&type=gmchief'); ?>"
                                    title="">Вернуться к монтажам
                                </a>
                            <?php } ?>
                        </div>
                    </div>
                </form>
            </div>
            <?php if($user->dealer_type == 0) { ?>
                <div class="col-xl-6">
                    <div class="comment">
                        <label style="font-weight: bold;"> История клиента: </label>
                        <textarea id="comments" class="input-comment" rows=11 readonly style="resize: none; outline: none;"></textarea>
                        <table>
                            <tr>
                                <td><label style="font-weight: bold;"> Добавить комментарий: </label></td>
                            </tr>
                            <tr>
                                <td width = 100%><textarea  style="resize: none;" class = "inputactive" id="new_comment" placeholder="Введите новое примечание"></textarea></td>
                                <td><button class="btn btn-primary" type="button" id="add_comment"><i class="fa fa-paper-plane" aria-hidden="true"></i>
                                </button></td>
                            </tr>
                        </table>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>
    <div class="modal_window_container" id="mw_container">
        <button type="button" class="close_btn" id="close_mw"><i class="fa fa-times fa-times-tar" aria-hidden="true"></i></button>
       <div id="mw_mounts_calendar" class="modal_window"></div>
    </div>
<?php } ?>
<script type="text/javascript" src="/components/com_gm_ceiling/views/project/common_table.js"></script>
<script type="text/javascript" src="/components/com_gm_ceiling/date_picker/mounts_calendar.js"></script>
<script type="text/javascript">
    init_mount_calendar('calendar_mount','mount','mw_mounts_calendar',['close_mw','mw_container']);
    var min_project_sum = <?php echo  $min_project_sum;?>;
    var min_components_sum = <?php echo $min_components_sum;?>;
    var self_data = JSON.parse('<?php echo $self_calc_data;?>');
    var project_id = "<?php echo $this->item->id; ?>";
    var preloader = '<?=parent::getPreloaderNotJS();?>';


    //скрыть модальное окно
    jQuery(document).mouseup(function (e) {
        var div = jQuery("#mw_mounts_calendar");
        if (!div.is(e.target)
            && div.has(e.target).length === 0) {
            jQuery("#close_mw").hide();
            jQuery("#mw_container").hide();
            div.hide();
        }
    });
    //--------------------------------------------------

    // функция подсвета сегоднешней даты
    

    // показать историю
    function show_comments() {
        var id_client = <?php echo $this->item->id_client;?>;
        jQuery.ajax({
            url: "index.php?option=com_gm_ceiling&task=selectComments",
            data: {
                id_client: id_client
            },
            dataType: "json",
            async: true,
            success: function (data) {
                var comments_area = document.getElementById('comments');
                if(comments_area){
                    comments_area.innerHTML = "";
                    var date_t;
                    for (var i = 0; i < data.length; i++) {
                        date_t = new Date(data[i].date_time);
                        comments_area.innerHTML += formatDate(date_t) + "\n" + data[i].text + "\n----------\n";
                    }
                    comments_area.scrollTop = comments_area.scrollHeight;
                }
                
            },
            error: function (data) {
                var n = noty({
                    timeout: 2000,
                    theme: 'relax',
                    layout: 'center',
                    maxVisible: 5,
                    type: "error",
                    text: "Ошибка вывода примечаний"
                });
            }
        });
    }
    //------------------------------------------------------

    // форматирование даты для вывода
    function formatDate(date) {
        var dd = date.getDate();
        if (dd < 10) dd = '0' + dd;
        var mm = date.getMonth() + 1;
        if (mm < 10) mm = '0' + mm;
        var yy = date.getFullYear();
        if (yy < 10) yy = '0' + yy;
        var hh = date.getHours();
        if (hh < 10) hh = '0' + hh;
        var ii = date.getMinutes();
        if (ii < 10) ii = '0' + ii;
        var ss = date.getSeconds();
        if (ss < 10) ss = '0' + ss;
        return dd + '.' + mm + '.' + yy + ' ' + hh + ':' + ii + ':' + ss;
    }
    // ------------------------------------------------------------------------

    // при нажатии на энтер добавляется коммент
    var new_comment_btn = document.getElementById('new_comment');
    if(new_comment_btn){
        document.getElementById('new_comment').onkeydown = function (e) {
        if (e.keyCode === 13) {
            document.getElementById('add_comment').click();
        }
    }
    }
    
    // ----------------------------------------------------------------------

    jQuery(document).ready(function () {

        window.time_gauger = undefined;
        window.gauger = undefined;
        window.datetime_gauger = undefined;
        window.time = undefined;
        window.mounter = undefined;
        window.datatime = undefined;

        
        // показать историю
        show_comments();
        //---------------------------------------------------------

        // добавление коммента и обновление истории
        jQuery("#add_comment").click(function () {
            var comment = jQuery("#new_comment").val();
            var reg_comment = /[\\\<\>\/\'\"\#]/;
            var id_client = <?php echo $this->item->id_client;?>;
            if (reg_comment.test(comment) || comment === "") {
                alert('Неверный формат примечания!');
                return;
            }
            jQuery.ajax({
                url: "index.php?option=com_gm_ceiling&task=addComment",
                data: {
                    comment: comment,
                    id_client: id_client
                },
                dataType: "json",
                async: true,
                success: function (data) {
                    var n = noty({
                        timeout: 2000,
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "success",
                        text: "Комментарий добавлен"
                    });
                    jQuery("#comments_id").val(jQuery("#comments_id").val() + data + ";");
                    show_comments();
                    jQuery("#new_comment").val("");
                },
                error: function (data) {
                    var n = noty({
                        timeout: 2000,
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "error",
                        text: "Ошибка отправки"
                    });
                }
            });
        });
        //----------------------------------------------------------------------------------

        // с вкладкой общее связано
            
        function Float(x, y = 2) {
            return Math.round(parseFloat(""+x) * Math.pow(10,y)) / Math.pow(10,y);
        }
        

        jQuery("#spend-form input").on("keyup", function () {
            jQuery('#extra_spend_submit').fadeIn();
        });

        jQuery("#penalty-form input").on("keyup", function () {
            jQuery('#penalty_submit').fadeIn();
        });

        jQuery("#bonus-form input").on("keyup", function () {
            jQuery('#bonus_submit').fadeIn();
        });

        jQuery("#extra_spend_button").click(function () {
            var extra_spend_title_container = jQuery("#extra_spend_title_container"),
                extra_spend_value_container = jQuery("#extra_spend_value_container");
            jQuery("<div class='form-group'><input name='extra_spend_title[]' value='' class='form-control' type='text'></div>").appendTo(extra_spend_title_container);
            jQuery("<div class='form-group'><input name='extra_spend_value[]' value='' class='form-control' type='tel'></div>").appendTo(extra_spend_value_container);
            jQuery('#extra_spend_submit').fadeIn();
            jQuery("#spend-form input").on("keyup", function () {
                jQuery('#extra_spend_submit').fadeIn();
            });
        });

        jQuery("#penalty_button").click(function () {
            var extra_spend_title_container = jQuery("#penalty_title_container"),
                extra_spend_value_container = jQuery("#penalty_value_container");
            jQuery("<div class='form-group'><input name='penalty_title[]' value='' class='form-control' type='text'></div>").appendTo(penalty_title_container);
            jQuery("<div class='form-group'><input name='penalty_value[]' value='' class='form-control' type='tel'></div>").appendTo(penalty_value_container);
            jQuery('#penalty_submit').fadeIn();
            jQuery("#penalty-form input").on("keyup", function () {
                jQuery('#penalty_submit').fadeIn();
            });
        });

        jQuery("#bonus_button").click(function () {
            var extra_spend_title_container = jQuery("#bonus_title_container"),
                extra_spend_value_container = jQuery("#bonus_value_container");
            jQuery("<div class='form-group'><input name='bonus_title[]' value='' class='form-control' type='text'></div>").appendTo(bonus_title_container);
            jQuery("<div class='form-group'><input name='bonus_value[]' value='' class='form-control' type='tel'></div>").appendTo(bonus_value_container);
            jQuery('#bonus_submit').fadeIn();
            jQuery("#bonus-form input").on("keyup", function () {
                jQuery('#bonus_submit').fadeIn();
            });
        });

        jQuery("#spend-form").submit(function (e) {
            e.preventDefault();
            data = jQuery(this).serialize();
            jQuery.ajax({
                type: 'POST',
                url: "index.php?option=com_gm_ceiling&task=add_spend&id=<?php echo $this->item->id; ?>",
                data: data,
                success: function (data) {
                    if (data == 1) {
                        jQuery('#extra_spend_submit').fadeOut();
                    } else {
                        var n = noty({
                            theme: 'relax',
                            layout: 'center',
                            maxVisible: 5,
                            type: "error",
                            text: "Ошибка при попытке сохранить доп. затраты."
                        });
                    }

                },
                dataType: "text",
                timeout: 10000,
                error: function () {
                    var n = noty({
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "error",
                        text: "Ошибка при попытке сохранить доп. затраты. Сервер не отвечает"
                    });
                }
            });
        });

        jQuery("#penalty-form").submit(function (e) {
            e.preventDefault();
            data = jQuery(this).serialize();
            jQuery.ajax({
                type: 'POST',
                url: "index.php?option=com_gm_ceiling&task=add_penalty&id=<?php echo $this->item->id; ?>",
                data: data,
                success: function (data) {
                    if (data == 1) {
                        jQuery('#penalty_submit').fadeOut();
                    } else {
                        var n = noty({
                            theme: 'relax',
                            layout: 'center',
                            maxVisible: 5,
                            type: "error",
                            text: "Ошибка при попытке сохранить штрафы."
                        });
                    }

                },
                dataType: "text",
                timeout: 10000,
                error: function () {
                    var n = noty({
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "error",
                        text: "Ошибка при попытке сохранить штрафы. Сервер не отвечает"
                    });
                }
            });
        });

        jQuery("#bonus-form").submit(function (e) {
            e.preventDefault();
            data = jQuery(this).serialize();
            jQuery.ajax({
                type: 'POST',
                url: "index.php?option=com_gm_ceiling&task=add_bonus&id=<?php echo $this->item->id; ?>",
                data: data,
                success: function (data) {
                    if (data == 1) {
                        jQuery('#bonus_submit').fadeOut();
                    } else {
                        var n = noty({
                            theme: 'relax',
                            layout: 'center',
                            maxVisible: 5,
                            type: "error",
                            text: "Ошибка при попытке сохранить штрафы."
                        });
                    }

                },
                dataType: "text",
                timeout: 10000,
                error: function () {
                    var n = noty({
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "error",
                        text: "Ошибка при попытке сохранить штрафы. Сервер не отвечает"
                    });
                }
            });
        });

    });

</script>