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
$projects_mounts_model = Gm_ceilingHelpersGm_ceiling::getModel('projects_mounts');
$model_api_phones = Gm_ceilingHelpersGm_ceiling::getModel('api_phones');
/*________________________________________________________________*/
$transport = Gm_ceilingHelpersGm_ceiling::calculate_transport($this->item->id);
$client_sum_transport = $transport['client_sum'];
$self_sum_transport = $transport['mounter_sum'];//идет в монтаж
if(!empty($service_mount)){
    $self_sum_transport = Gm_ceilingHelpersGm_ceiling::calculate_transport($this->item->id,"service")['mounter_sum'];
}
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
    $calculation->n19 = $calculationform_model->n19_load($calculation->id);
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

$mount_sum = 0;
if(!empty($this->item->api_phone_id))
    $reklama = $model_api_phones->getDataById($this->item->api_phone_id)->name;
else
    $reklama = "";
$all_advt = $model_api_phones->getAdvt();
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

if ($this->item->project_status == 1) {
    $whatCalendar = 0;
} elseif ($this->item->project_status != 11 || $this->item->project_status != 12 || $this->item->project_status == 17) {
    $whatCalendar = 1;
}

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
                <input id="mount" type="hidden" name="mount" value="<?php echo $json_mount; ?>"/>
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
                        <?php } ?>
                    </tr>
                    <tr>
                        <th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_PROJECT_CLIENT_ID'); ?></th>
                        <td><a href="/index.php?option=com_gm_ceiling&view=clientcard&id=<?php echo $this->item->_client_id?>"><?php echo $this->item->client_id; ?></a></td>
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
                        <th>
                            Реклама
                        </th>
                        <td>
                            <?php echo $reklama;?>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <button class="btn btn-primary" type="button" id="change_rek">Изменить рекламу</button>
                        </td>
                    </tr>
                    <tr>
                        <th>Примечание к монтажу</th>
                        <td><textarea name="jform[mount_note]" id="jform_mount_note" placeholder="Примечание начальника МС" aria-invalid="false"><?php //вывести по-другому ?></textarea></td>
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
                <?php if ($userId == $user->dealer_id) { ?>
                    <input name="type" value="chief" type="hidden">
                <?php } else { ?>
                    <input name="type" value="gmchief" type="hidden">
                <?php } ?>
            </div>
            <div class="col-md-6">
                <h4 class="center"> Примечания</h4>
                <?php include_once('components/com_gm_ceiling/views/project/project_notes.php'); ?>
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
            <?php 
                if ($this->item->project_status == 1) { 
                    echo "<h4 class='center'>Изменить замерщика, время и дату замера</h4>";
                    echo '<div id="calendar_measures"></div>
                        <div id="calendar_mount" style="display: none"></div>';
                } elseif ($this->item->project_status != 11 && $this->item->project_status != 12) {
                    echo "<h4 class='center'>Назначить/изменить монтажную бригаду, время и дату</h4>";
                    echo '<div id="calendar_measures" style="display: none"></div>
                        <div id="calendar_mount"></div>';
                }
            ?>
            </div>
        </div>
        <div class="control-group">
            <div class="controls">
                <?php if($this->item->project_status == 4) { ?>
                    <button id="btn_submit" type="button" class="validate btn btn-primary">Сохранить и запустить в производство</button>
                <?php } else{ ?>
                    <button id="btn_submit" type="button" class="validate btn btn-primary">Сохранить</button>
                <?php } ?>
            </div>
        </div>
    <?php } ?>
</form>

<div class="modal_window_container" id="mw_container">
    <button type="button" class="close_btn" id="close_mw"><i class="fa fa-times fa-times-tar" aria-hidden="true"></i></button>
    <div class="modal_window" id="mw_measures_calendar"></div>
    <div class="modal_window" id="mw_mounts_calendar"></div>
    <div id="mw_advt" class="modal_window">
                <h4>Изменение/добавление рекламы</h4>
                <label>Выберите или добавьте новую рекламу</label>
                <div class="row">  
                    <div class="col-xs-6 col-md-6">
                        <p>
                            <label><strong>Выбрать:</strong></label>
                        </p>
                        <select id="advt_choose">
                            <option value="0">Выберите рекламу</option>
                            <?php if (!empty($all_advt)) foreach ($all_advt as $item) { ?>
                                <option value="<?php echo $item->id ?>"><?php echo $item->name ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-xs-6 col-md-6">
                         <p>
                            <label><strong>Добавить:</strong></label>
                        </p>
                         <div id="new_advt_div">
                            <p><input id="new_advt_name" placeholder="Название рекламы"></p>
                            <button type="button" class="btn btn-primary" id="add_new_advt">Добавить</button>
                        </div>
                    </div>
                </div>
                <br>
                <button class="btn btn-primary" id="save_advt" type="button">Сохранить </button>
            </div>
</div>

<script type="text/javascript" src="/components/com_gm_ceiling/views/project/common_table.js"></script>

<script type="text/javascript" src="/components/com_gm_ceiling/date_picker/measures_calendar.js"></script>
<script type="text/javascript" src="/components/com_gm_ceiling/date_picker/mounts_calendar.js"></script>
<script type="text/javascript">
    init_measure_calendar('measures_calendar','jform_project_new_calc_date','jform_project_gauger','mw_measures_calendar',['close_mw','mw_container'], 'measure_info');
    init_mount_calendar('calendar_mount','mount','mw_mounts_calendar',['close_mw','mw_container']);

    var min_project_sum = <?php echo  $min_project_sum;?>;
    var min_components_sum = <?php echo $min_components_sum;?>;
    var self_data = JSON.parse('<?php echo $self_calc_data;?>');
    var project_id = "<?php echo $this->item->id; ?>";
    var preloader = '<?=parent::getPreloaderNotJS();?>';
    var client_id = "<?php echo $this->item->id_client;?>";
    jQuery('#mw_container').click(function(e) { // событие клика по веб-документу
        var div = jQuery("#mw_measures_calendar"); // тут указываем ID элемента
        var div1 = jQuery("#mw_mounts_calendar");
        var div2 = jQuery("#mw_advt");
        if (!div.is(e.target) // если клик был не по нашему блоку
            && div.has(e.target).length === 0
            && !div1.is(e.target)
            && div1.has(e.target).length === 0
            && !div2.is(e.target)
            && div2.has(e.target).length === 0) { // и не по его дочерним элементам
            jQuery("#close_mw").hide();
            jQuery("#mw_container").hide();
            jQuery(".modal_window").hide();
        }
    });

    jQuery('#btn_submit').click(function(){
        var project_status = <?= $this->item->project_status; ?>;
        if (document.getElementById('mount').value == ''
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
        // показать историю
        if (document.getElementById('comments')) {
            show_comments();
        }

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

        jQuery("#change_rek").click(function(){
            jQuery("#close_mw").show();
            jQuery("#mw_container").show();
            jQuery("#mw_advt").show('slow');
        });


        jQuery("#save_advt").click(function() {
            if (jQuery("#advt_choose").val() == '0' || jQuery("#advt_choose").val() == '') {
                noty({
                    timeout: 2000,
                    theme: 'relax',
                    layout: 'center',
                    maxVisible: 5,
                    type: "warning",
                    text: "Укажите рекламу"
                });
                jQuery("#advt_choose").focus();
                return;
            }
            jQuery.ajax({
                url: "index.php?option=com_gm_ceiling&task=project.save_advt",
                data: {
                    project_id: project_id,
                    api_phone_id: jQuery("#advt_choose").val(),
                    client_id: client_id
                },
                dataType: "json",
                async: true,
                success: function(data) {
                    document.getElementById('save_advt').style.display = 'none';
                    document.getElementById('advt_choose').disabled = 'disabled';
                    noty({
                        timeout: 2000,
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "success",
                        text: "Реклама сохранена"
                    });
                    location.reload();
                },
                error: function(data) {
                    console.log(data);
                    noty({
                        timeout: 2000,
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "error",
                        text: "Ошибка"
                    });
                }
            });
        });

        jQuery("#add_new_advt").click(function() {
            jQuery.ajax({
                url: "index.php?option=com_gm_ceiling&task=addNewAdvt",
                data: {
                    name: jQuery("#new_advt_name").val()
                },
                dataType: "json",
                async: true,
                success: function (data) {
                    select = document.getElementById('advt_choose');
                    var opt = document.createElement('option');
                    opt.selected = true;
                    opt.value = data.id;
                    opt.innerHTML = data.name;
                    select.appendChild(opt);
                    jQuery("#new_advt_name").val('');
                },
                error: function (data) {
                    console.log(data);
                    var n = noty({
                        timeout: 2000,
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "error",
                        text: "ошибка"
                    });
                }
            });
        });
    });

</script>