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
$userId  = $user->id;

Gm_ceilingHelpersGm_ceiling::create_client_common_estimate($this->item->id);
Gm_ceilingHelpersGm_ceiling::create_common_estimate_mounters($this->item->id);
Gm_ceilingHelpersGm_ceiling::create_estimate_of_consumables($this->item->id);

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

//address
$street = preg_split("/,.дом([\S\s]*)/", $this->item->project_info)[0];
preg_match("/,.дом:.([\d\w\/\s]{1,4})/", $this->item->project_info,$house);
$house = $house[1];
preg_match("/.корпус:.([\d\W\s]{1,4}),|.корпус:.([\d\W\s]{1,4}),{0}/", $this->item->project_info,$bdq);
$bdq = $bdq[1];
preg_match("/,.квартира:.([\d\s]{1,4}),/", $this->item->project_info,$apartment);
$apartment = $apartment[1];
preg_match("/,.подъезд:.([\d\s]{1,4}),/", $this->item->project_info,$porch);
$porch = $porch[1];
preg_match("/,.этаж:.([\d\s]{1,4})/", $this->item->project_info,$floor);
$floor = $floor[1];
preg_match("/,.код:.([\d\S\s]{1,10})/", $this->item->project_info,$code);
$code = $code[1];

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
if ($user->dealer_id == 1) {
    $dealer_for_calendar = $userId;
} else {
    $dealer_for_calendar = $user->dealer_id;
}
if ($this->item->project_status == 1) {
    $whatCalendar = 0;
    $FlagCalendar = [3, $dealer_for_calendar];
} elseif ($this->item->project_status != 11 && $this->item->project_status != 12) {
    $whatCalendar = 1;
    if ($user->dealer_type == 1 && $user->dealer_mounters == 1) {
        $dealer_for_calendar = 1;
    }
    $FlagCalendar = [2, $dealer_for_calendar];
}
$calendar1 = Gm_ceilingHelpersGm_ceiling::DrawCalendarTar($userId, $month1, $year1, $FlagCalendar);
$calendar2 = Gm_ceilingHelpersGm_ceiling::DrawCalendarTar($userId, $month2, $year2, $FlagCalendar);
//----------------------------------------------------------------------------------

// все бригады
$Allbrigades = $calculationsModel->FindAllbrigades($dealer_for_calendar);
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
if (count($AllGauger) == 0) {
    $AllGauger = ["id" => $userId, "name" => $user->name];
}
//----------------------------------------------------------------------------------

$mount_sum = 0;

echo parent::getPreloader();

?>

<?=parent::getButtonBack();?>
<?php if ($whatCalendar == 0) { ?>
    <a class="btn btn-primary" href="<?php echo JRoute::_('index.php?option=com_gm_ceiling&view=projects&type=chief&subtype=gaugings'); ?>" title="">Вернуться к замерам</a>
<?php } else { ?>
    <a class="btn btn-primary" href="<?php if ($userId == $user->dealer_id) echo JRoute::_('index.php?option=com_gm_ceiling&view=mainpage&type=chiefmainpage'); else echo JRoute::_('index.php?option=com_gm_ceiling&view=projects&type=gmchief'); ?>" title="">Вернуться к монтажам</a>
<?php } ?>

<link rel="stylesheet" href="/components/com_gm_ceiling/views/projectform/tmpl/css/style.css" type="text/css" />

<h2 class="center" style="margin-bottom: 1em;">Просмотр проекта № <?php echo $this->item->id; ?></h2>
<form id="form-project" action="<?php echo JRoute::_('index.php?option=com_gm_ceiling&task=project.approve'); ?>" method="post" class="form-validate form-horizontal" enctype="multipart/form-data">
    <?php if ($this->item) { ?>
        <?php include_once('components/com_gm_ceiling/views/project/common_table.php'); ?>
        <div class="row">
            <div class="col-xs-12 col-md-6 no_padding">
                <h4>Информация по проекту</h4>
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
                        <?php } else if ($this->item->project_status == 11 || $this->item->project_status == 12 || $this->item->project_status != 17) { ?>
                            <th>Дата монтажа</th>
                            <td>
                                <?php
                                    if ($this->item->project_mounting_date == "0000-00-00 00:00:00")
                                    {
                                        echo '-';
                                    }
                                    else
                                    {
                                        $jdate = new JDate(JFactory::getDate($this->item->project_mounting_date));
                                        echo $jdate->format('d.m.Y H:i');
                                    }
                                ?>
                            </td>
                        <?php } else { ?>
                            <th>Удобная дата монтажа для клиента</th>
                            <td>
                                <?php
                                    if ($this->item->project_mounting_date == "0000-00-00 00:00:00")
                                    {
                                        echo '-';
                                    }
                                    else
                                    {
                                        $jdate = new JDate(JFactory::getDate($this->item->project_mounting_date));
                                        echo $jdate->format('d.m.Y H:i');
                                    }
                                ?>
                            </td>
                        <?php } ?>
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
                        <th>Примечание менеджера</th>
                        <td><?php echo $this->item->dealer_manager_note; ?></td>
                    </tr>
                    <tr>
                        <th>Примечание замерщика</th>
                        <td><?php echo $this->item->dealer_calculator_note; ?></td>
                    </tr>
                    <tr>
                        <th>Примечание начальника МС</th>
                        <td><textarea name="jform[dealer_chief_note]" id="jform_dealer_chief_note" placeholder="Примечание начальника МС" aria-invalid="false"><?php echo $this->item->dealer_chief_note; ?></textarea></td>
                    </tr>
                    <tr>
                        <th>Замерщик</th>
                        <?php 
                            $gauger_model = Gm_ceilingHelpersGm_ceiling::getModel('project');
                            $gauger = $gauger_model->getGauger($this->item->id); 
                        ?>
                        <td><?php echo $gauger->name; ?></td>
                    </tr>
                    <tr>
                        <th>Монтажная бригада</th>
                        <?php 
                            $mount_model = Gm_ceilingHelpersGm_ceiling::getModel('project');
                            $mount = $mount_model->getMount($this->item->id); 
                        ?>
                        <td><?php echo $mount->name; ?></td>
                    </tr>
                </table>
                <?php if ($userId == $user->dealer_id) { ?>
                    <input name="type" value="chief" type="hidden">
                <?php } else { ?>
                    <input name="type" value="gmchief" type="hidden">
                <?php } ?>
            </div>
            <!-- стиль не правила,  у нас нет расширенного дилера -->
            <?php if($user->dealer_type == 0) { ?>
                <div class="col-xs-12 col-md-6 no_padding">
                    <div class="comment">
                        <label style="font-weight: bold;"> История клиента: </label>
                        <textarea id="comments" class="input-comment" rows=11 readonly style="resize: none; outline: none;"></textarea>
                        <table>
                            <tr>
                                <td><label style="font-weight: bold;"> Добавить комментарий: </label></td>
                            </tr>
                            <tr>
                                <td width = 100%>
                                    <textarea  style="resize: none;" class = "inputactive" id="new_comment" placeholder="Введите новое примечание"></textarea>
                                </td>
                                <td>
                                    <button class="btn btn-primary" type="button" id="add_comment"><i class="fa fa-paper-plane" aria-hidden="true"></i></button>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            <?php } ?>
            <!-- конец -->
        </div>

        <div class="row">
            <div class="col-xs-12 no_padding">
                <table id="container_calendars">
                    <tr>
                        <td colspan="3">
                            <?php 
                                if ($this->item->project_status == 1) { 
                                    echo "<h4 class='center'>Изменить замерщика, время и дату замера</h4>";
                                } elseif ($this->item->project_status != 11 && $this->item->project_status != 12) {
                                    echo "<h4 class='center'>Назначить/изменить монтажную бригаду, время и дату</h4>";
                                }
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="no_yes_padding">
                            <button id="button-prev" type="button" class="btn btn-primary"><i class="fa fa-arrow-left" aria-hidden="true"></i></button>
                        </td>
                        <td>
                            <div style="display: inline-block; width: 100%;">
                                <div id="calendar1">
                                    <?php echo $calendar1; ?>
                                </div>
                                <div id="calendar2">
                                    <?php echo $calendar2; ?>
                                </div>
                            </div>
                        </td>
                        <td class="no_yes_padding">
                            <button id="button-next" type="button" class="btn btn-primary"><i class="fa fa-arrow-right" aria-hidden="true"></i></button>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        <div class="control-group">
            <div class="controls">
                <?php if($this->item->project_status == 4) { ?>
                    <button id="btn_submit" type="button" class="validate btn btn-primary">Сохранить и запустить в производство</button>
                <?php } else if($this->item->project_status == 5) { ?>
                    <button id="btn_submit" type="button" class="validate btn btn-primary">Сохранить</button>
                <?php } ?>
            </div>
        </div>
        <div id="modal-window-container-tar">
            <button id="close-tar" type="button"><i class="fa fa-times fa-times-tar" aria-hidden="true"></i></button>
            <div id="modal-window-choose-tar">
                <p id="date-modal"></p>
                <?php if ($whatCalendar == 0) { ?>
                    <p><strong>Выберите время замера:</strong></p>
                    <p><table id="projects_gaugers"></table></p>
                <?php } else { ?>
                    <p><strong>Выберите монтажника:</strong></p>
                    <p><select name="mounters" id="mounters"></select></p>
                    <p style="margin-bottom: 0;"><strong>Монтажники:</strong></p>
                    <div id="mounters_names"></div>
                    <div id="projects_brigade_container"></div>
                    <p style="margin-top: 1em;"><strong>Выберите время начала монтажа:</strong></p>
                    <p><select name="hours" id='hours'></select></p>
                    <p><button type="button" id="save-choise-tar" class="btn btn-primary">Ок</button></p>
                <?php } ?>
            </div>
        </div>
    <?php } ?>
</form>
<script type="text/javascript" src="/components/com_gm_ceiling/views/project/common_table.js"></script>
<script>
    var min_project_sum = <?php echo  $min_project_sum;?>;
    var min_components_sum = <?php echo $min_components_sum;?>;
    var self_data = JSON.parse('<?php echo $self_calc_data;?>');
    var project_id = "<?php echo $this->item->id; ?>";
    var preloader = '<?=parent::getPreloaderNotJS();?>';
    

    whatCalendar = <?php echo $whatCalendar; ?>
    // листание календаря
    month_old1 = 0;
    year_old1 = 0;
    month_old2 = 0;
    year_old2 = 0;
    jQuery("#button-next").click(function () {
        month1 = <?php echo $month1; ?>;
        year1 = <?php echo $year1; ?>;
        month2 = <?php echo $month2; ?>;
        year2 = <?php echo $year2; ?>;
        if (month_old1 != 0) {
            month1 = month_old1;
            year1 = year_old1;
            month2 = month_old2;
            year2 = year_old2;
        }
        if (month1 == 12) {
            month1 = 1;
            year1++;
        } else {
            month1++;
        }
        if (month2 == 12) {
            month2 = 1;
            year2++;
        } else {
            month2++;
        }
        month_old1 = month1;
        year_old1 = year1;
        month_old2 = month2;
        year_old2 = year2;
        update_calendar(month1, year1);
        update_calendar2(month2, year2);
    });
    jQuery("#button-prev").click(function () {
        month1 = <?php echo $month1; ?>;
        year1 = <?php echo $year1; ?>;
        month2 = <?php echo $month2; ?>;
        year2 = <?php echo $year2; ?>;
        if (month_old1 != 0) {
            month1 = month_old1;
            year1 = year_old1;
            month2 = month_old2;
            year2 = year_old2;
        }
        if (month1 == 1) {
            month1 = 12;
            year1--;
        } else {
            month1--;
        }
        if (month2 == 1) {
            month2 = 12;
            year2--;
        } else {
            month2--;
        }
        month_old1 = month1;
        year_old1 = year1;
        month_old2 = month2;
        year_old2 = year2;
        update_calendar(month1, year1);
        update_calendar2(month2, year2);
    });

    function update_calendar(month, year) {
        jQuery.ajax({
            type: 'POST',
            url: "index.php?option=com_gm_ceiling&task=UpdateCalendarTar",
            data: {
                id: <?php echo $userId; ?>,
                id_dealer: <?php echo $dealer_for_calendar; ?>,
                flag: <?php if ($whatCalendar == 0) { echo 3; } else { echo 2; } ?>,
                month: month,
                year: year,
            },
            success: function (msg) {
                jQuery("#calendar1").empty();
                jQuery("#calendar1").append(msg);
                Today(day, NowMonth, NowYear);
                var datesession = jQuery("#jform_project_mounting_date").val();  
                if (datesession != undefined) {
                    if (whatCalendar == 0) {
                        jQuery("#current-monthD"+datesession.substr(8, 2)+"DM"+datesession.substr(5, 2)+"MY"+datesession.substr(0, 4)+"YI"+<?php echo $userId; ?>+"IC0C").addClass("change");
                    } else {
                        jQuery("#current-monthD"+datesession.substr(8, 2)+"DM"+datesession.substr(5, 2)+"MY"+datesession.substr(0, 4)+"YI"+<?php echo $userId; ?>+"IC1C").addClass("change");
                    }
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
                    text: "Ошибка при попытке обновить календарь. Сервер не отвечает"
                });
            }
        });
    }
    function update_calendar2(month, year) {
        jQuery.ajax({
            type: 'POST',
            url: "index.php?option=com_gm_ceiling&task=UpdateCalendarTar",
            data: {
                id: <?php echo $userId; ?>,
                month: month,
                year: year,
                id_dealer: <?php echo $dealer_for_calendar; ?>,
                flag: <?php if ($whatCalendar == 0) { echo 3; } else { echo 2; } ?>,
            },
            success: function (msg) {
                jQuery("#calendar2").empty();
                jQuery("#calendar2").append(msg);
                Today(day, NowMonth, NowYear);
                var datesession = jQuery("#jform_project_mounting_date").val();  
                if (datesession != undefined) {
                    if (whatCalendar == 0) {
                        jQuery("#current-monthD"+datesession.substr(8, 2)+"DM"+datesession.substr(5, 2)+"MY"+datesession.substr(0, 4)+"YI"+<?php echo $userId; ?>+"IC0C").addClass("change");
                    } else {
                        jQuery("#current-monthD"+datesession.substr(8, 2)+"DM"+datesession.substr(5, 2)+"MY"+datesession.substr(0, 4)+"YI"+<?php echo $userId; ?>+"IC1C").addClass("change");
                    }
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
                    text: "Ошибка при попытке обновить календарь. Сервер не отвечает"
                });
            }
        });
    }
    //----------------------------------------

    //скрыть модальное окно
    jQuery(document).mouseup(function (e) {
        var div = jQuery("#modal-window-choose-tar");
        if (!div.is(e.target)
            && div.has(e.target).length === 0) {
            jQuery("#close-tar").hide();
            jQuery("#modal-window-container-tar").hide();
            jQuery("#modal-window-choose-tar").hide();
        }
    });
    //--------------------------------------------------

    // функция подсвета сегоднешней даты
    var Today = function (day, month, year) {
        month++;
        if (whatCalendar == 0) {
            jQuery("#current-monthD"+day+"DM"+month+"MY"+year+"YI"+<?php echo $userId; ?>+"IC0C").addClass("today");
        } else {
            jQuery("#current-monthD"+day+"DM"+month+"MY"+year+"YI"+<?php echo $userId; ?>+"IC1C").addClass("today");
        }
    }   
    //------------------------------------------

    // функция чтобы другая функция выполнилась позже чем document ready
    Function.prototype.process= function(state){
        var process= function(){
            var args= arguments;
            var self= arguments.callee;
            setTimeout(function(){
                self.handler.apply(self, args);
            }, 0 )
        }
        for(var i in state) process[i]= state[i];
        process.handler= this;
        return process;
    }
    //------------------------------------------

    // показать историю
    function show_comments() {
        <?php if (isset($this->item->id_client)) { ?>
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
                    comments_area.innerHTML = "";
                    var date_t;
                    for (var i = 0; i < data.length; i++) {
                        date_t = new Date(data[i].date_time);
                        comments_area.innerHTML += formatDate(date_t) + "\n" + data[i].text + "\n----------\n";
                    }
                    comments_area.scrollTop = comments_area.scrollHeight;
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
        <?php } ?>        
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
    <?php if ($user->dealer_type != 1) { ?>
        document.getElementById('new_comment').onkeydown = function (e) {
            if (e.keyCode === 13) {
                document.getElementById('add_comment').click();
            }
        }
    <?php } ?>
    // ----------------------------------------------------------------------

    jQuery(document).ready(function () {

        //trans();

        window.time_gauger = undefined;
        window.gauger = undefined;
        window.datetime_gauger = undefined;
        window.time = undefined;
        window.mounter = undefined;
        window.datatime = undefined;

        // показать историю
        if (document.getElementById('comments')) {
            show_comments();
        }
        //---------------------------------------------------------

        jQuery('#btn_submit').click(function(){
            var project_status = <?= $this->item->project_status; ?>;
            if (document.getElementById('jform_project_mounting_date_old').value == '0000-00-00 00:00:00'
                && document.getElementById('jform_project_mounting_date').value == '0000-00-00 00:00:00'
                && project_status != 1 && project_status != 17)
            {
                var n = noty({
                    timeout: 2000,
                    theme: 'relax',
                    layout: 'center',
                    maxVisible: 5,
                    type: "error",
                    text: "Дата монтажа пустая!"
                });
            }
            else
            {
                document.getElementById('form-project').submit();
            }
        });

        // открытие модального окна с календаря и получение даты и вывода свободных монтажников или замерщиков
        jQuery("#calendar1, #calendar2").on("click", ".current-month, .not-full-day, .change, .full-day", function() {
            window.idDay = jQuery(this).attr("id");
            reg1 = "D(.*)D";
            reg2 = "M(.*)M";
            reg3 = "Y(.*)Y";
            var d = idDay.match(reg1)[1];
            var m = idDay.match(reg2)[1];
            if (d.length == 1) {
                d = "0"+d;
            }
            if (m.length == 1) {
                m = "0"+m;
            }
            window.date = idDay.match(reg3)[1]+"-"+m+"-"+d;
            jQuery("#modal-window-container-tar").show();
            jQuery("#modal-window-choose-tar").show("slow");
            jQuery("#close-tar").show();
            if (whatCalendar == 0) {
                jQuery.ajax({
                    type: 'POST',
                    url: "/index.php?option=com_gm_ceiling&task=calculations.GetBusyGauger",
                    data: {
                        date: date,
                        dealer: <?php echo $dealer_for_calendar; ?>,
                    },
                    success: function(data) {
                        Array.prototype.diff = function(a) {
                            return this.filter(function(i) {return a.indexOf(i) < 0;});
                        };
                        AllGauger = <?php echo json_encode($AllGauger); ?>;
                        data = JSON.parse(data); // замеры
                        console.log(data);
                        AllTime = ["09:00:00", "10:00:00", "11:00:00", "12:00:00", "13:00:00", '14:00:00', "15:00:00", "16:00:00", "17:00:00", "18:00:00", "19:00:00", "20:00:00"];
                        var TableForSelect = '<tr><th class="caption"></th><th class="caption">Время</th><th class="caption">Адрес</th><th class="caption">Замерщик</th></tr>';
                        AllTime.forEach( elementTime => {
                            var t = elementTime.substr(0, 2);
                            t++;
                            Array.from(AllGauger).forEach(function(elementGauger) {
                                var emptytd = 0;
                                Array.from(data).forEach(function(elementProject) {
                                    if (elementProject.project_calculator == elementGauger.id && elementProject.project_calculation_date.substr(11) == elementTime) {
                                        var timesession_gauger = jQuery("#jform_project_new_calc_date").val();
                                        var gaugersession = jQuery("#jform_project_gauger").val();
                                        if (elementProject.project_calculator == gaugersession && elementProject.project_calculation_date.substr(11) == timesession_gauger.substr(11)) {
                                            TableForSelect += '<tr><td><input type="radio" name="choose_time_gauger" value="'+elementTime+'"></td>';
                                        } else {
                                            TableForSelect += '<tr><td></td>';
                                        }
                                        TableForSelect += '<td>'+elementTime.substr(0, 5)+'-'+t+':00</td>';
                                        TableForSelect += '<td>'+elementProject.project_info+'</td>';
                                        emptytd = 1;
                                    }
                                });
                                if (emptytd == 0) {
                                    TableForSelect += '<tr><td><input type="radio" name="choose_time_gauger" value="'+elementTime+'"></td>';
                                    TableForSelect += '<td>'+elementTime.substr(0, 5)+'-'+t+':00</td>';
                                    TableForSelect += '<td></td>';
                                }
                                TableForSelect += '<td>'+elementGauger.name+'<input type="hidden" name="gauger" value="'+elementGauger.id+'"></td></tr>';
                            });
                        });
                        jQuery("#projects_gaugers").empty();
                        jQuery("#projects_gaugers").append(TableForSelect);
                        jQuery("#date-modal").html("<strong>Выбранный день: "+d+"."+m+"."+idDay.match(reg3)[1]+"</strong>");
                    }
                });
                //если сессия есть, то выдать время, которое записано в сессии
                if (date == datesession_gauger.substr(0, 10)) {
                    var timesession_gauger = jQuery("#jform_project_new_calc_date").val();
                    var gaugersession = jQuery("#jform_project_gauger").val();
                    setTimeout(function() { 
                        var times = jQuery("input[name='choose_time_gauger']");
                        if (timesession_gauger != undefined) {
                            times.each(function(element) {
                                if (timesession_gauger.substr(11) == jQuery(this).val() && gaugersession == jQuery(this).closest('tr').find("input[name='gauger']").val()) {
                                    jQuery(this).prop("checked", true);
                                }
                            });
                        }
                    }, 200);
                }
                if (datetime_gauger != undefined) {
                    if (date == datetime_gauger.substr(0, 10)) {
                        if (time_gauger != undefined) {
                            setTimeout(function() { 
                                var times = jQuery("input[name='choose_time_gauger']");
                                times.each(function(element) {
                                    if (time_gauger == jQuery(this).val() && gauger == jQuery(this).closest('tr').find("input[name='gauger']").val()) {
                                        jQuery(this).prop("checked", true);
                                    }
                                });
                            }, 200);
                        }
                    }
                }
            } else {
                jQuery.ajax({
                    type: 'POST',
                    url: "/index.php?option=com_gm_ceiling&task=calculations.GetBusyMounters",
                    data: {
                        date: date,
                        dealer: <?php echo $dealer_for_calendar; ?>,
                    },
                    success: function(data) {
                        Array.prototype.diff = function(a) {
                            return this.filter(function(i) {return a.indexOf(i) < 0;});
                        };
                        window.DataOfProject = JSON.parse(data);
                        window.AllTime = ["09:00:00", "10:00:00", "11:00:00", "12:00:00", "13:00:00", '14:00:00', "15:00:00", "16:00:00", "17:00:00", "18:00:00", "19:00:00", "20:00:00"];
                        data = JSON.parse(data);
                        jQuery("#date-modal").text("Выбранный день: "+d+"."+m+"."+idDay.match(reg3)[1]);
                        // заполнение бригад в селекте
                        jQuery("#mounters").empty();
                        Allbrigades = <?php echo json_encode($Allbrigades); ?>;
                        select_brigade = "";
                        Array.from(Allbrigades).forEach(function(elem) {
                            select_brigade += '<option value="'+elem.id+'">'+elem.name+'</option>';
                        });
                        jQuery("#mounters").append(select_brigade);
                        // вывод имен монтажников
                        var selectedBrigade = jQuery("#mounters").val();
                        jQuery("#mounters_names").empty();
                        AllMounters = <?php echo json_encode($AllMounters) ?>;
                        AllMounters.forEach(elem => {
                            if (selectedBrigade == elem.id_brigade) {
                                jQuery("#mounters_names").append("<p style=\"margin-top: 0; margin-bottom: 0;\">"+elem.name+"</p>");
                            }
                        });
                        // вывод работ бригады
                        jQuery("#projects_brigade_container").empty();
                        var table_projects = '<p style="margin-top: 1em; margin-bottom: 0;"><strong>Монтажи бригады:</strong></p><table id="projects_brigade">';
                        table_projects += '<tr class="caption"><td>Время</td><td>Адрес</td><td>Периметр</td></tr>';
                        Array.from(data).forEach(function(element) {
                            if (element.project_mounter == selectedBrigade) {
                                if (element.project_mounting_day_off != "") {
                                    table_projects += '<tr><td>'+element.project_mounting_date.substr(11, 5)+' - '+element.project_mounting_day_off.substr(11, 5)+'</td><td colspan="2">'+element.project_info+'</td></tr>';
                                } else {
                                    table_projects += '<tr><td>'+element.project_mounting_date.substr(11, 5)+'</td><td>'+element.project_info+'</td><td>'+element.n5+'</td></tr>';
                                }
                            }
                        });
                        table_projects += "</table>";
                        jQuery("#projects_brigade_container").append(table_projects);
                        // вывод времени бригады
                        var BusyTimes = [];
                        Array.from(data).forEach(function(elem) {
                            if (selectedBrigade == elem.project_mounter && elem.project_mounting_day_off == "" ) {
                                BusyTimes.push(elem.project_mounting_date.substr(11));
                            } else if (selectedBrigade == elem.project_mounter && elem.project_mounting_day_off != "") {
                                AllTime.forEach(element => {
                                    if (element >= elem.project_mounting_date.substr(11) && element <= elem.project_mounting_day_off.substr(11)) {
                                        BusyTimes.push(element);
                                    }
                                }); 
                            }
                        });
                        FreeTimes = AllTime.diff(BusyTimes);
                        var select_hours;
                        FreeTimes.forEach(element => {
                            select_hours += '<option value="'+element+'">'+element.substr(0, 5)+'</option>';
                        });
                        jQuery("#hours").empty();
                        jQuery("#hours").append(select_hours);
                    }
                });
                //если монтаж есть, то выдать время, монтажную бригаду и инфу о ней, которые записаны
                if (date == datesession.substr(0, 10)) {
                    var timesession = jQuery("#jform_project_mounting_date").val().substr(11);
                    var mountersession = jQuery("#jform_project_mounter").val();
                    setTimeout(function() {
                        // время
                        var timeall = document.getElementById('hours').options;
                        for (var i = 0; i < timeall.length; i++) {
                            if (timesession != undefined) {
                                if (timeall[i].value == timesession) {
                                    document.getElementById('hours').disabled = false;
                                    timeall[i].selected = true;
                                }
                            }
                        }
                        // бригада
                        var mounterall = document.getElementById('mounters').options;
                        for (var i = 0; i < mounterall.length; i++) {
                            if (mountersession != undefined) {
                                if (mounterall[i].value == mountersession) {
                                    document.getElementById('mounters').disabled = false;
                                    mounterall[i].selected = true;
                                }
                            }
                        }
                        // инфа о бригаде
                        jQuery("#mounters_names").empty();
                        AllMounters = <?php echo json_encode($AllMounters) ?>;
                        AllMounters.forEach(elem => {
                            if (mountersession == elem.id_brigade) {
                                jQuery("#mounters_names").append("<p style=\"margin-top: 0; margin-bottom: 0;\">"+elem.name+"</p>");
                            }
                        });
                        // монтажи
                        jQuery("#projects_brigade_container").empty();
                        var table_projects3 = '<p style="margin-top: 1em; margin-bottom: 0;"><strong>Монтажи бригады:</strong></p><table id="projects_brigade">';
                        table_projects3 += '<tr class="caption"><td>Время</td><td>Адрес</td><td>Периметр</td></tr>';
                        Array.from(DataOfProject).forEach(function(element) {
                            if (element.project_mounter == mountersession) {
                                table_projects3 += '<tr><td>'+element.project_mounting_date+'</td><td>'+element.project_info+'</td><td>'+element.n5+'</td></tr>';
                            }
                        });
                        table_projects3 += "</table>";
                        jQuery("#projects_brigade_container").append(table_projects3);
                    }, 200);
                }
                // Если перевыбрана дата монтажа, показать ее
                if (datatime != undefined) {
                    if (date == datatime.substr(0, 10)) {
                        setTimeout(function() {
                            // время
                            var timeall = document.getElementById('hours').options;
                            for (var i = 0; i < timeall.length; i++) {
                                if (time != undefined) {
                                    if (timeall[i].value == time) {
                                        document.getElementById('hours').disabled = false;
                                        timeall[i].selected = true;
                                    }
                                }
                            }
                            // бригада
                            var mounterall = document.getElementById('mounters').options;
                            for (var i = 0; i < mounterall.length; i++) {
                                if (mounter != undefined) {
                                    if (mounterall[i].value == mounter) {
                                        document.getElementById('mounters').disabled = false;
                                        mounterall[i].selected = true;
                                    }
                                }
                            }
                            // инфа о бригаде
                            jQuery("#mounters_names").empty();
                            AllMounters = <?php echo json_encode($AllMounters) ?>;
                            AllMounters.forEach(elem => {
                                if (mounter == elem.id_brigade) {
                                    jQuery("#mounters_names").append("<p style=\"margin-top: 0; margin-bottom: 0;\">"+elem.name+"</p>");
                                }
                            });
                            // монтажи
                            jQuery("#projects_brigade_container").empty();
                            var table_projects3 = '<p style="margin-top: 1em; margin-bottom: 0;"><strong>Монтажи бригады:</strong></p><table id="projects_brigade">';
                            table_projects3 += '<tr class="caption"><td>Время</td><td>Адрес</td><td>Периметр</td></tr>';
                            Array.from(DataOfProject).forEach(function(element) {
                                if (element.project_mounter == mounter) {
                                    table_projects3 += '<tr><td>'+element.project_mounting_date+'</td><td>'+element.project_info+'</td><td>'+element.n5+'</td></tr>';
                                }
                            });
                            table_projects3 += "</table>";
                            jQuery("#projects_brigade_container").append(table_projects3);
                        }, 200);
                    }
                }
                // запрет выбора монтажника, если монтаж в статусе недовыполнен
                if (<?php echo $this->item->project_status ?> == 17) {
                    setTimeout(function() {
                        var mounter = document.getElementById('mounters').options;
                        for (var i = 0; i < mounter.length; i++) {
                            document.getElementById('mounters').disabled = true;
                        }
                        console.log(mounter);
                    }, 200);
                }
            }
        });
        //--------------------------------------------

        // заполнение данных о выбранной бригаде при изменении селекта
        jQuery("#mounters").change(function () {
            // имена бригад
            jQuery("#mounters_names").empty();
            var id = jQuery("#mounters").val();
            AllMounters = <?php echo json_encode($AllMounters) ?>;
            AllMounters.forEach(elem => {
                if (id == elem.id_brigade) {
                    jQuery("#mounters_names").append("<p style=\"margin-top: 0; margin-bottom: 0;\">"+elem.name+"</p>");
                }
            });
            // монтажи
            jQuery("#projects_brigade_container").empty();
            var table_projects2 = '<p style="margin-top: 1em; margin-bottom: 0;"><strong>Монтажи бригады:</strong></p><table id="projects_brigade">';
            table_projects2 += '<tr class="caption"><td>Время</td><td>Адрес</td><td>Периметр</td></tr>';
            Array.from(DataOfProject).forEach(function(element) {
                if (element.project_mounter == id) {
                    if (element.project_mounting_day_off != "") {
                        table_projects2 += '<tr><td>'+element.project_mounting_date.substr(11, 5)+' - '+element.project_mounting_day_off.substr(11, 5)+'</td><td colspan="2">'+element.project_info+'</td></tr>';
                    } else {
                        table_projects2 += '<tr><td>'+element.project_mounting_date.substr(11, 5)+'</td><td>'+element.project_info+'</td><td>'+element.n5+'</td></tr>';
                    }
                }
            });
            table_projects2 += "</table>";
            jQuery("#projects_brigade_container").append(table_projects2);
            // времена
            jQuery("#hours").empty();
            var BusyTimes = [];
            Array.from(DataOfProject).forEach(function(elem) {
                if (id == elem.project_mounter && elem.project_mounting_day_off == "" ) {
                    BusyTimes.push(elem.project_mounting_date.substr(11));
                } else if (id == elem.project_mounter && elem.project_mounting_day_off != "") {
                    AllTime.forEach(element => {
                        if (element >= elem.project_mounting_date.substr(11) && element <= elem.project_mounting_day_off.substr(11)) {
                            BusyTimes.push(element);
                        }
                    }); 
                }
            });
            FreeTimes = AllTime.diff(BusyTimes);
            var select_hours2;
            FreeTimes.forEach(element => {
                select_hours2 += '<option value="'+element+'">'+element.substr(0, 5)+'</option>';
            });
            jQuery("#hours").append(select_hours2);
        });
        //-------------------------------------------

        // получение значений из селектов монтажников
        jQuery("#save-choise-tar").click(function() {
            mounter = jQuery("#mounters").val();
            time = jQuery("#hours").val();
            datatime = date+" "+time;
            jQuery("#jform_project_mounter").val(mounter);
            jQuery("#jform_project_mounting_date").val(datatime);
            if (jQuery(".change").length == 0) {
                jQuery("#"+idDay).addClass("change");
            } else {
                jQuery(".change").removeClass("change");
                jQuery("#"+idDay).addClass("change");
            }
            jQuery("#close-tar").hide();
            jQuery("#modal-window-container-tar").hide();
            jQuery("#modal-window-choose-tar").hide();
        });
        //------------------------------------------

        // получение значений из селектов замерщиков
        jQuery("#projects_gaugers").on("change", "input:radio[name='choose_time_gauger']", function() {
            var times = jQuery("input[name='choose_time_gauger']");
            time_gauger = "";
            gauger = "";
            times.each(function(element) {
                if (jQuery(this).prop("checked") == true) {
                    time_gauger = jQuery(this).val();
                    gauger = jQuery(this).closest('tr').find("input[name='gauger']").val();
                }
            });
            datetime_gauger = date+" "+time_gauger;
            jQuery("#jform_project_new_calc_date").val(datetime_gauger);
            jQuery("#jform_project_gauger").val(gauger);
            if (jQuery(".change").length == 0) {
                jQuery("#"+idDay).addClass("change");
            } else {
                jQuery(".change").removeClass("change");
                jQuery("#"+idDay).addClass("change");
            }
            jQuery("#close-tar").hide();
            jQuery("#modal-window-container-tar").hide();
            jQuery("#modal-window-choose-tar").hide();
        });
        jQuery("#projects_gaugers").on("click", "td", function(){
            var times = jQuery(this).closest('tr').find("input:radio[name='choose_time_gauger']");
            times.prop("checked",true);
            times.change();
        });
        //------------------------------------------

        // подсвет сегоднешней даты
        window.today = new Date();
        window.NowYear = today.getFullYear();
        window.NowMonth = today.getMonth();
        window.day = today.getDate();
        Today(day, NowMonth, NowYear);
        //------------------------------------------

        //если сессия есть, то выдать дату, которая записана в сессии
        var datesession = jQuery("#jform_project_mounting_date").val();
        if (datesession != undefined) {
            if (datesession.substr(8, 1) == "0") {
                daytocalendar = datesession.substr(9, 1);
            } else {
                daytocalendar = datesession.substr(8, 2);
            }
            if (datesession.substr(5, 1) == "0") {
                monthtocalendar = datesession.substr(6, 1);
            } else {
                monthtocalendar = datesession.substr(5, 2);
            }
            jQuery("#current-monthD"+daytocalendar+"DM"+monthtocalendar+"MY"+datesession.substr(0, 4)+"YI"+<?php echo $userId; ?>+"IC1C").addClass("change");
        }
        var datesession_gauger = jQuery("#jform_project_new_calc_date").val();
        if (datesession_gauger != undefined) {
            if (datesession_gauger.substr(8, 1) == "0") {
                daytocalendar_gauger = datesession_gauger.substr(9, 1);
            } else {
                daytocalendar_gauger = datesession_gauger.substr(8, 2);
            }
            if (datesession_gauger.substr(5, 1) == "0") {
                monthtocalendar_gauger = datesession_gauger.substr(6, 1);
            } else {
                monthtocalendar_gauger = datesession_gauger.substr(5, 2);
            }
            jQuery("#current-monthD"+daytocalendar_gauger+"DM"+monthtocalendar_gauger+"MY"+datesession_gauger.substr(0, 4)+"YI"+<?php echo $userId; ?>+"IC0C").addClass("change");
        }
        //-----------------------------------------------------------

        // добавление коммента и обновление истории
        jQuery("#add_comment").click(function () {
            var comment = jQuery("#new_comment").val();
            var reg_comment = /[\\\<\>\/\'\"\#]/;
            <?php if (isset($this->item->id_client)) { ?>
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
            <?php } ?>
        });
        //----------------------------------------------------------------------------------

            
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