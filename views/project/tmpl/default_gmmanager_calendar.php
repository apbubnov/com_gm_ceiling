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

    $user       = JFactory::getUser();
    $userId     = $user->get('id');
    $user_group = $user->groups;
    $dealer_id = $user->dealer_id;
    $dealer_type = JFactory::getUser($dealer_id)->dealer_type;
    $model_calculations = Gm_ceilingHelpersGm_ceiling::getModel('calculations');
    $mountModel = Gm_ceilingHelpersGm_ceiling::getModel('mount');
    $calculationform_model = Gm_ceilingHelpersGm_ceiling::getModel('calculationform');
    $model_api_phones = Gm_ceilingHelpersGm_ceiling::getModel('api_phones');
    $repeat_model = Gm_ceilingHelpersGm_ceiling::getModel('repeatrequest');
    $model_client_phones = Gm_ceilingHelpersGm_ceiling::getModel('client_phones');
    $model_client = Gm_ceilingHelpersGm_ceiling::getModel('client');
    $projects_model = Gm_ceilingHelpersGm_ceiling::getModel('projects');
    $request_model = Gm_ceilingHelpersGm_ceiling::getModel('requestfrompromo');
    $dop_contacts = Gm_ceilingHelpersGm_ceiling::getModel('Clients_dop_contacts');
    $recoil_model = Gm_ceilingHelpersGm_ceiling::getModel('recoil');
    $canvas_model = Gm_ceilingHelpersGm_ceiling::getModel('canvases');
    $components_model = Gm_ceilingHelpersGm_ceiling::getModel('components');

    Gm_ceilingHelpersGm_ceiling::create_client_common_estimate($this->item->id);
    Gm_ceilingHelpersGm_ceiling::create_common_estimate_mounters($this->item->id);
    Gm_ceilingHelpersGm_ceiling::create_estimate_of_consumables($this->item->id);

    $project_id = $this->item->id;

    //транспорт
    $transport = Gm_ceilingHelpersGm_ceiling::calculate_transport($this->item->id);
    /* минимальная сумма заказа */
    $mount_transport = $mountModel->getDataAll($this->item->dealer_id);
    $min_project_sum = (empty($mount_transport->min_sum)) ? 0 : $mount_transport->min_sum;
    $min_components_sum = (empty($mount_transport->min_components_sum)) ? 0 : $mount_transport->min_components_sum;
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
    $calculations = $model_calculations->new_getProjectItems($this->item->id);
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

    $project_card = '';
    $phones = [];
    if (!empty($_SESSION["project_card_$project_id"]))
    {
        $project_card = $_SESSION["project_card_$project_id"];
        $phones = json_decode($project_card)->phones;
    }

    $need_choose = false;
    $jinput = JFactory::getApplication()->input;
    //$phoneto = $jinput->get('phoneto', '0', 'STRING');
    //$phonefrom = $jinput->get('phonefrom', '0', 'STRING');
    $call_id = $jinput->get('call_id', 0, 'INT');
    
    if(!empty($this->item->api_phone_id)){
        if ($this->item->api_phone_id == 10) {
            $repeat_advt = $repeat_model->getDataByProjectId($this->item->id);
            if (!empty($repeat_advt->advt_id)) {
                $reklama = $model_api_phones->getDataById($repeat_advt->advt_id);
            } else {
                $need_choose = true;
            }
        }
        $reklama = $model_api_phones->getDataById($this->item->api_phone_id);
        $write = $reklama->number .' '.$reklama->name . ' ' . $reklama->description;
    }
    else{
        $need_choose = true;
        $all_advt = $model_api_phones->getDealersAdvt($dealer_id);
    }

    /*
    if (!empty($phoneto) && !empty($phonefrom)) {
        $reklama = $model_api_phones->getNumberInfo($phoneto);
        $write = $reklama->number .' '.$reklama->name . ' ' . $reklama->description;
    } elseif (!empty($this->item->api_phone_id)) {
        
        
        if ($this->item->api_phone_id == 10) {
            if (!empty($repeat_advt->advt_id)) {
                $reklama = $model_api_phones->getDataById($repeat_advt->advt_id);
            } else {
                $need_choose = true;
            }
        }
        else {
            $reklama = $model_api_phones->getDataById($this->item->api_phone_id);
        }
        $write = $reklama->number . ' ' .$reklama->name . ' ' . $reklama->description;
    } else {
        $need_choose = true;
    }*/
    
    $cl_phones = $model_client_phones->getItemsByClientId($this->item->id_client);
    $date_time = $this->item->project_calculation_date;
    $date_arr = date_parse($date_time);
    $date = $date_arr['year'] . '-' . $date_arr['month'] . '-' . $date_arr['day'];
    $time = $date_arr['hour'] . ':00';
    //обновляем менеджера для клиента
    
    if($this->item->manager_id==1||empty($model_client->getClientById($this->item->id_client)->manager_id)){
        $model_client->updateClientManager($this->item->id_client, $userId);
    }
    
    $projects_model->updateManagerId($userId, $this->item->id_client);
    
    $request_model->delete($this->item->id_client);
    $client_sex  = $model_client->getClientById($this->item->id_client)->sex;
    //$client_dealer_id = $model_client->getClientById($this->item->id_client)->dealer_id;
    if($this->item->id_client!=1){
        
        $email = $dop_contacts->getEmailByClientID($this->item->id_client);
    }
    $client_dealer = JFactory::getUser($model_client->getClientById($this->item->id_client)->dealer_id);
    if($client_dealer->name == $this->item->client_id){
        $lk = true;
    }
    
    $all_recoil = $recoil_model->getData();

?>

<script src="https://api-maps.yandex.ru/2.1/?lang=ru_RU" type="text/javascript"></script>
<link rel="stylesheet" href="/components/com_gm_ceiling/views/project/css/style.css" type="text/css" />

<button id = "back_btn" class = "btn btn-primary"><i class="fa fa-arrow-left" aria-hidden="true"></i> Назад</button>

<h2 class="center">Просмотр проекта</h2>
<?php if ($this->item) : ?>
    <h5 class="center">
        <?php if (!$need_choose) { ?>
            <input id="advt_info" class="h5-input" readonly value= <?php echo '"' . $write . '"'; ?>>
            <?php if($reklama->id == 17) { ?>
                <select id="recoil_choose" name ="recoil_choose">
                    <option value="0">-Выберите откатника-</option>
                    <?php foreach ($all_recoil as $item) { ?>
                        <option value="<?php echo $item->id ?>"><?php echo $item->name ?></option>
                    <?php } ?>
                </select>
                <button type="button" id = "show_window" class="btn btn-primary"><i class="fa fa-plus-square" aria-hidden="true"></i></button>
            <?php }?>
        <?php } elseif ($need_choose) { ?>
            <select id="advt_choose">
                <option value="0">Выберите рекламу</option>
                <?php foreach ($all_advt as $item) { ?>
                    <option value="<?php echo $item->id ?>"><?php echo $item->name ?></option>
                <?php } ?>
            </select>
            <button type="button" id="add_new_dvt" class="btn btn-primary"><i class="fa fa-plus-square-o" aria-hidden="true"></i></button>
            <select id="recoil_choose" name ="recoil_choose" style="display:none;">
                <option value="0">-Выберите откатника-</option>
                <?php foreach ($all_recoil as $item) { ?>
                    <option value="<?php echo $item->id ?>"><?php echo $item->name ?></option>
                <?php } ?>
            </select>
            <button type="button" id = "show_window" style = "display:none;" class="btn btn-primary"><i class="fa fa-plus-square" aria-hidden="true"></i></button>
            <div id="new_advt_div" style="display:none;"><input id="new_advt_name" placeholder="Название рекламы"><br>
                <button type="button" class="btn btn-primary" id="save_advt">Ok</button>
            </div>
        <?php } ?>
    </h5>
    <div class="container">
        <div class="row">
            <div class="item_fields">
                <h4>Информация по проекту № <?php echo $this->item->id ?></h4>
                <form id="form-client" action="/index.php?option=com_gm_ceiling&task=project.recToMeasurement&type=gmmanager&subtype=calendar" method="post" class="form-validate form-horizontal" enctype="multipart/form-data">
                    <input name="project_id" id = "project_id"  value="<?php echo $project_id; ?>" type="hidden">
                    <input name="client_id" id="client_id" value="<?php echo $this->item->id_client; ?>" type="hidden">
                    <input name="advt_id" id="advt_id" value="<?php echo $reklama->id; ?>" type="hidden">
                    <input name="comments_id" id="comments_id" type="hidden">
                    <input name="status" id="project_status" value="" type="hidden">
                    <input name="call_id" value="<?php echo $call_id; ?>" type="hidden">
                    <input name="type" value="gmmanager" type="hidden">
                    <input name="subtype" value="calendar" type="hidden">
                    <input name="data_change" value="0" type="hidden">
                    <input name="data_delete" value="0" type="hidden">
                    <input name="selected_advt" id="selected_advt" value="<?php echo (!empty($this->item->api_phone_id))? $this->item->api_phone_id: '0' ?>" type="hidden">
                    <input name = "recoil" id = "recoil" value = "" type = "hidden">
                    <input id="jform_new_project_calculation_daypart" name="new_project_calculation_daypart" value = "<?php if ($this->item->project_calculation_date != null && $this->item->project_calculation_date != "0000-00-00 00:00:00") { echo substr($this->item->project_calculation_date, 11); }?>"class="inputactive" type="hidden">
                    <input name = "project_new_calc_date" id = "jform_project_new_calc_date" class ="inputactive" value="<?php if (isset($this->item->project_calculation_date)) { echo $this->item->project_calculation_date;}?>" type="hidden">
                    <input name = "project_gauger" id = "jform_project_gauger" class ="inputactive" value="<?php if ($this->item->project_calculator != null) { echo $this->item->project_calculator; } else {echo "0";}?>" type="hidden">
                    <input id="project_sum" name="project_sum" value="<?php echo $project_total_discount ?>" type="hidden">
                    <input id="project_sum_transport" name="project_sum_transport" value="<?php echo $project_total_discount_transport ?>" type="hidden">
                    <input id = "emails" name = "emails" value = "" type = "hidden"> 
                    <div class="row">
                        <div class="col-xs-12 col-xm-12 col-md-6 col-lg-6">
                            <table>
                                <tr>
                                    <th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_PROJECT_CLIENT_ID'); ?></th>
                                    <td>
                                        <input name="new_client_name" class="inputactive"
                                        id="jform_client_name" value="<?php echo $this->item->client_id; ?>" placeholder="ФИО клиента" type="text">
                                    </td>
                                    <td>
                                        <button id="find_old_client" type="button" class="btn btn-primary"><i class="fa fa-search" aria-hidden="true"></i></button><br>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="3">
                                        <label><b>Искать:</b></label><br>
                                        <input id='radio_clients' type='radio' class = "radio" name='slider-search' value='clients'>
                                        <label for='radio_clients'>Клиентов</label>&nbsp;&nbsp;&nbsp;
                                        <input id='radio_dealers' type='radio' class = "radio" name='slider-search' value='dealers'>
                                        <label for='radio_dealers'>Дилеров</label>&nbsp;&nbsp;&nbsp;
                                        <input id='radio_designers' type='radio' class = "radio" name='slider-search' value='designers'>
                                        <label for='radio_designers'>Отделочников</label>
                                    </td>
                                </tr>
                                <tr id="search" style="display : none;">
                                    <th>
                                        Выберите клиента из списка:
                                    </th>
                                    <td colspan="2">
                                        <select id="found_clients" class="inputactive"></select>
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        Пол клиента
                                    </th>
                                    <td>
                                        <input id='male' type='radio' class = "radio" name='slider-sex' value='0' <?php if($client_sex == "0") echo "checked";?>>
                                        <label  for='male'>Mужской</label>
                                        <input id='female' type='radio' class = "radio" name='slider-sex' value='1'  <?php if($client_sex == "1") echo "checked";?> >
                                        <label for='female'>Женский</label>
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        <?php echo JText::_('COM_GM_CEILING_CLIENTS_CLIENT_CONTACTS'); ?>
                                        <button type="button" class="btn btn-primary" id="add_phone"><i class="fa fa-plus-square" aria-hidden="true"></i></button>
                                    </th>
                                    
                                    <?php foreach ($cl_phones as $value) {  ?>
                                        <td>
                                            <input name="new_client_contacts[<?php echo '\''.$value->phone.'\''?>]" id="jform_client_contacts" class="inputactive" value="<?php echo  strval($value->phone); ?>" type="text">
                                        </td>
                                        <td>
                                            <button id="make_call" type="button" class="btn btn-primary"><i class="fa fa-phone" aria-hidden="true"></i></button>
                                        </td>
                                    <?php } ?>
                                </tr>
                                <tr>
                                    <th></th>
                                    <td colspan=2>
                                        <div id="phones-block"></div>
                                    </td>
                                </tr>
                                <?php if ($call_id != 0): ?>
                                    <tr>
                                        <td colspan=3>
                                            <button id="broke" type="button" class="btn btn-primary">Звонок сорвался, перенести время</button>
                                            <div id="call_up" class="call" style="display:none;">
                                                <label for="call">Добавить звонок</label>
                                                <br>
                                                <input name="call_date" id="call_date_up" type="datetime-local" placeholder="Дата звонка">
                                                <input name="call_comment" id="call_comment_up" placeholder="Введите примечание">
                                                <button class="btn btn-primary" id="add_call_and_submit_up" type="button"><i class="fa fa-floppy-o" aria-hidden="true"></i></button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                                <?php if (!empty($phones) && count($phones > 1)) {
                                    for ($i = 1; $i < count($phones); $i++) { ?>
                                        <tr class='dop-phone'>
                                            <th></th>
                                            <td>
                                                <input name='new_client_contacts[<?php echo $i; ?>]' id='jform_client_contacts' class='inputactive' value="<?php echo $phones[$i]; ?>">
                                            </td>
                                            <td>
                                                <button class='clear_form_group btn btn-danger' type='button'><i class='fa fa-trash' aria-hidden='true'></i></button>
                                            </td>
                                        </tr>
                                    <?php }
                                } ?>
                                <?php if(count($email)>0){
                                    foreach ($email as $value) {?>
                                        <tr>
                                            <th>e-mail</th>
                                            <td>
                                                <input name="email[]" id="email" class="inputhidden" value="<?php echo $value->contact;?>" placeholder="e-mail" type="text">
                                            </td>
                                        </tr>
                                    <?php } 
                                 }?>
                                <tr>
                                    <th>Добавить адрес эл.почты</th>
                                    <td>
                                        <input name="new_email" id="jform_email" class="inputactivenotsingle" value="" placeholder="e-mail" type="text">
                                       
                                     </td>
                                     <td>
                                         <button type="button" class = "btn btn-primary" id = "add_email">Ок</button>
                                     </td>
                                <?php 
                                    $street = preg_split("/,.дом:.([\d\w\/\s]{1,4}),/", $this->item->project_info)[0];
                                    preg_match("/,.дом:.([\d\w\/\s]{1,4}),/", $this->item->project_info,$house);
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
                                ?>
                                <tr>
                                    <th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_PROJECT_PROJECT_INFO'); ?></th>
                                    <td><input name="new_address" id="jform_address" class="inputactive" value="<?php echo $street ?>" placeholder="Адрес" type="text" required="required"></td>
                                </tr>
                                <tr class="controls">
                                    <td>Дом / Корпус</td>
                                    <td>
                                        <input name="new_house" id="jform_house" value="<?php echo $house ?>" class="inputactive" style="width: 50%; margin-bottom: 1em; float: left; margin: 0 5px 0 0;" placeholder="Дом" required="required" aria-required="true" type="text">
                                        <input name="new_bdq" id="jform_bdq"  value="<?php echo $bdq ?>" class="inputactive"  style="width: calc(50% - 5px); margin-bottom: 1em;" placeholder="Корпус" aria-required="true" type="text">
                                    </td>
                                </tr>
                                <tr class="controls">
                                    <td> Квартира / Подъезд</td>
                                    <td>
                                        <input name="new_apartment" id="jform_apartment" value="<?php echo $apartment ?>" class="inputactive" style="width:50%;margin-bottom:1em;margin-right: 5px;float: left;" placeholder="Квартира"  aria-required="true" type="text">
                                
                                        <input name="new_porch" id="jform_porch"  value="<?php echo $porch ?>" class="inputactive"   style="width: calc(50% - 5px); margin-bottom: 1em;" placeholder="Подъезд"  aria-required="true" type="text">
                                    </td>
                                </tr>
                                <tr class="controls">
                                    <td> Этаж / Код домофона</td>
                                    <td>
                                        <input name="new_floor" id="jform_floor"  value="<?php echo $floor ?>" class="inputactive"  style="width:50%; margin-bottom:1em;  margin: 0 5px  0 0; float: left;" placeholder="Этаж" aria-required="true" type="text">
                                
                                        <input name="new_code" id="jform_code"  value="<?php echo $code ?>" class="inputactive"  style="width: calc(50% - 5px); margin-bottom: 1em;" placeholder="Код" aria-required="true" type="text">
                                    </td>
                                </tr>
                                <tr>
                                    <th>Дата и время замера</th>
                                    <td>
                                        <input type="text" id="measure_info" class="inputactive" readonly>
                                        <div id="measures_calendar"></div>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Примечание менеджера</th>
                                    <td>
                                        <input name="gmmanager_note" id="gmmanager_note" class="inputactive"
                                            value="<?php echo $this->item->gm_manager_note; ?>">
                                    </td>
                                </tr>
                                <tr>
                                    <th>Менеджер</th>
                                    <td>
                                        <input name="Manager_name" id="manager_name" class="inputhidden"
                                            value="<?php if (isset($this->item->read_by_manager)&&$this->item->read_by_manager!=1) {
                                            echo JFactory::getUser($this->item->read_by_manager)->name;
                                            } ?>" readonly>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Замерщик</th>
                                    <td>
                                        <input name="calculator_name" id="calculator_name" class="inputhidden"
                                            value="<?php if (isset($this->item->project_calculator)) {
                                            echo JFactory::getUser($this->item->project_calculator)->name;
                                            }?>" readonly>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Клиентский просчет?</th>
                                    <td>
                                        <input id='no_client' type='radio' class = "radio" name='slider_which_calc' value='0' checked= "checked">
                                        <label  for='no_client'>Нет</label>
                                        <input id='client' type='radio' class = "radio" name='slider_which_calc' value='1'>
                                        <label for='client'>Да</label>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-xs-12 col-xm-12 col-md-6 col-lg-6">
                            <label for="slider-table"><b>Тип:</b></label>
                            <table class="slider-table">
                                <tr>
                                    <td></td>
                                    <td>Клиент</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td>Диллер</td>
                                    <td>
                                        <div class='switcher'>
                                            <label class='switcher-label switcher-state1' for='state1'>Дилер</label>
                                            <input id='state1' class='switcher-radio-state1' type='radio'
                                                name='slider-radio' value='dealer'<?php if($this->item->project_status == 20) echo "checked"?>>
                                            <label class='switcher-label switcher-state2' for='state2'>Клиент</label>
                                            <input id='state2' class='switcher-radio-state2' type='radio'
                                                name='slider-radio' value='client' <?php if($this->item->project_status != 20 && $this->item->project_status !=21 ) echo "checked"?>>
                                            <label class='switcher-label switcher-state3' for='state3'>Реклама</label>
                                            <input id='state3' class='switcher-radio-state3' type='radio'
                                                name='slider-radio' value='promo' <?php if($this->item->project_status == 21) echo "checked"?>>
                                            <div class='switcher-slider'></div>
                                        </div>
                                    </td>
                                    <td>Реклама</td>
                                </tr>
                            </table>
                            <div class="comment">
                                <label> История клиента: </label>
                                <textarea id="comments" class="input-comment" rows=11 readonly> </textarea>
                                <table>
                                    <tr>
                                        <td><label> Добавить комментарий: </label></td>
                                    </tr>
                                    <tr>
                                        <td width = 100%><textarea  class = "inputactive" id="new_comment" placeholder="Введите новое примечание"></textarea></td>
                                        <td><button class="btn btn-primary" type="button" id="add_comment"><i class="fa fa-paper-plane" aria-hidden="true"></i>
                                        </button></td>
                                    </tr>
                                </table>
                            </div>
                            <label ><b>Предоставление личного кабиента</b></label>
                            <br>
                            <div class="radio-group">
                                <input id='no' class='' type='radio' name='client_lk' value='0' <?php if(!$lk) echo checked ?>>
                                <label for='no'>Убрать</label>
                                <input id='yes' class='' type='radio' name='client_lk' value='1' <?php if($lk) echo checked ?>>
                                <label class='' for='yes'>Предоставить</label>
                            </div>
                        </div>
                       
                    </div>
            </div>
            <table class="table calculation_sum">
                <?php if ($this->item->project_verdict == 0) { ?>
                    <tr>
                        <td style=" padding-left:0;"><a class="btn btn-primary" id="change_discount">Изменить величину
                                скидки</a></td>
                    </tr>
                <?php } ?>
                <?php
                    $skidka = 0;
                    if (!empty($calculation_total)) {
                        $skidka = ($calculation_total - $project_total_1) / $calculation_total * 100;
                    }
                ?>
                <tbody class="new_discount" style="display: none">
                <tr>
                    <td>
                        <label id="jform_discoint-lbl" for="jform_new_discount">Новый процент скидки:</label>
                        <input name="new_discount" id="jform_new_discount" value="" placeholder="Новый % скидки"
                               min="0" max="<?= round($skidka, 0); ?>" type="number">
                    </td>
                    <td>
                        <button type="button" id="update_discount" class="btn btn-primary">Ок</button>
                    </td>
                </tr>
                </tbody>
            </table>
            <!--</form>-->
        </div>
    </div>
    </div>
    
        <?php include_once('components/com_gm_ceiling/views/project/common_table.php'); ?>

        <?php if ($this->item->project_verdict == 0) { ?>
            <divclass="container">
                <div class="row">
                    <div class="col-xs-12 col-xm-12 col-md-4 col-lg-4">
                        <button class="btn  btn-primary" id="rec_to_measurement">
                            Записать на замер
                        </button>
                    </div>
                    <div class="col-xs-12 col-xm-12 col-md-4 col-lg-4">
                        <button class="btn  btn-danger" id="refuse_project">
                            Отказ от замера
                        </button>
                    </div>
                    <div class="col-xs-12 col-xm-12 col-md-4 col-lg-4">
                        <button class="btn  btn-primary" id="refuse_partnership">
                            Отказ от сотрудничества с ГМ
                        </button>
                    </div>
                </div>
                <div class="row">
                    <div id="call" class="call col" style="display:none;">
                        <label for="call">Добавить звонок</label>
                        <br>
                        <input name="call_date" id="call_date" type="datetime-local" placeholder="Дата звонка">
                        <input name="call_comment" id="call_comment" placeholder="Введите примечание">
                        <button class="btn btn-primary" id="add_call_and_submit" type="button"><i
                                    class="fa fa-floppy-o" aria-hidden="true"></i></button>
                    </div>
                </div>
            </div>
        <?php } ?>

    </div>
<?php
    else:
        echo JText::_('COM_GM_CEILING_ITEM_NOT_LOADED');
    endif;
?>

<div id="mw_container" class="modal_window_container">
    <button type="button" class="close_btn" id="close_mw"><i class="fa fa-times fa-times-tar" aria-hidden="true"></i></button>
    <div class="modal_window" id="mw_recoil">
        <h6>Введите ФИО</h6>
        <p><input type="text" id="new_fio" placeholder="ФИО" required></p>
        <h6>Введите номер телефона</h6>
        <p><input type="text" id="new_phone" placeholder="ФИО" required></p>
        <p><button type="button" id="add_recoil" class="btn btn-primary">Сохранить</button>  <button type="button" id="cancel" class="btn btn-primary">Отмена</button></p>
    </div>
    <div class="modal_window" id="mw_measures_calendar"></div>
</div>

<script type="text/javascript" src="/components/com_gm_ceiling/create_calculation.js"></script>
<script type="text/javascript" src="/components/com_gm_ceiling/views/project/common_table.js"></script>

<script type="text/javascript" src="/components/com_gm_ceiling/date_picker/measures_calendar.js"></script>
<script type="text/javascript">
    init_measure_calendar('measures_calendar','jform_project_new_calc_date','jform_project_gauger','mw_measures_calendar',['close_mw','mw_container'], 'measure_info');
    var project_id = "<?php echo $this->item->id; ?>";
    var $ = jQuery;
    var min_project_sum = <?php echo  $min_project_sum;?>;
    var min_components_sum = <?php echo $min_components_sum;?>;
    var self_data = JSON.parse('<?php echo $self_calc_data;?>');
    jQuery(document).mouseup(function (e){ // событие клика по веб-документу
        var div1 = jQuery("#mw_recoil");
        var div2 = jQuery("#mw_measures_calendar");
        if (!div1.is(e.target)
            && div1.has(e.target).length === 0
            && !div2.is(e.target)
            && div2.has(e.target).length === 0) {
            jQuery("#close_mw").hide();
            jQuery("#mw_container").hide();
            jQuery("#mw_recoil").hide();
            jQuery("#mw_measures_calendar").hide();
        }
    });

    jQuery(document).ready(function() {

        document.getElementById('add_calc').onclick = function()
        {
            create_calculation(<?php echo $this->item->id; ?>);
        };

        var project_card = '<?php echo $project_card; ?>';
        if (project_card != '')
        {
            project_card = JSON.parse(project_card);
            jQuery("#jform_client_name").val(project_card.fio);
            jQuery("#jform_address").val(project_card.address);
            jQuery("#jform_house").val(project_card.house);
            jQuery("#jform_bdq").val(project_card.bdq);
            jQuery("#jform_apartment").val(project_card.apartment);
            jQuery("#jform_porch").val(project_card.porch);
            jQuery("#jform_floor").val(project_card.floor);
            jQuery("#jform_code").val(project_card.code);
            jQuery("#jform_project_new_calc_date").val(project_card.date);
            jQuery("#jform_new_project_calculation_daypart").val(project_card.time);
            jQuery("#gmmanager_note").val(project_card.manager_comment);
            jQuery("#comments_id").val(project_card.comments);
            jQuery("#jform_project_gauger").val(project_card.gauger);
            let slider_sex = document.getElementsByName('slider-sex');
            for (let i = slider_sex.length; i--;)
            {
                if (slider_sex[i].value == project_card.sex)
                {
                    slider_sex[i].checked = 'checked';
                }
            }
            let slider_radio = document.getElementsByName('slider-radio');
            for (let i = slider_radio.length; i--;)
            {
                if (slider_radio[i].value == project_card.type)
                {
                    slider_radio[i].checked = 'checked';
                }
            }
            jQuery("#recoil_choose").val(project_card.recool);
            jQuery("#advt_choose").val(project_card.advt);
        }
        console.log(project_card);
    
        if (jQuery("#selected_advt"))
        {
            jQuery("#selected_advt").val(jQuery("#advt_choose").val());
        }       

        var hrefs = document.getElementsByTagName("a");
        var regexp = /index\.php\?option=com_gm_ceiling\&task=mainpage/;
        for(var i = 0; i < hrefs.length;i++){
            if(regexp.test(hrefs[i].href))
            {

                hrefs[i].onclick = function(){
                    return false;
                };
                break;
            }
        }

        jQuery("#back_btn").click(function(){
            var client_id = jQuery("#client_id").val();
            if(client_id == 1){
                if(jQuery("#jform_client_name").val() == ""){
                    jQuery("#jform_client_name").val("Безымянный");
                }
                jQuery("#form-client").submit();
            }
            else{
                history.back();

            }
        });

        document.onkeydown = function (e) {
            if (e.keyCode === 13) {
                return false;
            }
        }

        document.getElementById('new_comment').onkeydown = function (e) {
            if (e.keyCode === 13) {
                document.getElementById('add_comment').click();
            }
        }

        var time = <?php echo '"'.$time.'"';?>;

        if (project_card.time != '')
        {
            time = project_card.time;
        }

        show_comments();

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
        }

        jQuery("#jform_client_contacts").mask("+7 (999) 999-99-99");

        jQuery("input[name=client_lk]:radio").change(function(){
            if(this.value == 1){
                var name = jQuery("#jform_client_name").val();
                var login = jQuery("#jform_client_contacts").val().replace( /[-,(,),+, ]/g, "" );
                var pass = login.substr(4);
                var email = jQuery("#jform_email").val();
                var client_id = jQuery("#client_id").val();

                if(email.length == 0){
                    email = client_id+"@"+client_id;
                }
                jQuery.ajax({
                        type: 'POST',
                        url: "index.php?option=com_gm_ceiling&task=register_user",
                        data: {
                            FIO : name,
                            login : login,
                            email : email,
                            pass : pass,
                            pass2 : pass,
                            client_id : client_id,
                            project_id : jQuery("#project_id").val()
                        },
                        success: function(data){
                            if(data.error){
                                var n = noty({
                                    theme: 'relax',
                                    timeout: 2000,
                                    layout: 'center',
                                    maxVisible: 5,
                                    type: "error",
                                    text:data.error.msg
                                });
                            }
                            else{
                                var n = noty({
                                    theme: 'relax',
                                    timeout: 2000,
                                    layout: 'center',
                                    maxVisible: 5,
                                    type: "success",
                                    text:"Кабинет создан!"
                                });
                            }
                        },
                        dataType: "json",
                        timeout: 10000,
                        error: function(data){

                        }
                    });

            }
            else{
                var client_id = jQuery("#client_id").val(),
                    user_id = getClientDealerID();
                jQuery.ajax({
                        type: 'POST',
                        url: "index.php?option=com_gm_ceiling&task=deleteUser",
                        data: {
                            client_id : client_id,
                            user_id : user_id
                        },
                        success: function(data){
                            if(data.error){
                                var n = noty({
                                    theme: 'relax',
                                    timeout: 2000,
                                    layout: 'center',
                                    maxVisible: 5,
                                    type: "error",
                                    text:data.error.msg
                                });
                            }
                            else{
                                var n = noty({
                                    theme: 'relax',
                                    timeout: 2000,
                                    layout: 'center',
                                    maxVisible: 5,
                                    type: "success",
                                    text:"Кабинет удален!"
                                });
                            }
                        },
                        dataType: "json",
                        timeout: 10000,
                        error: function(data){

                        }
                    });
            }
        })

        function getClientDealerID(){
            var client_id = jQuery("#client_id").val();
                jQuery.ajax({
                        type: 'POST',
                        url: "index.php?option=com_gm_ceiling&task=getClientDealerId",
                        data: {
                            client_id : client_id,
                        },
                        success: function(data){
                            result = data;
                        },
                        async:false,
                        dataType: "json",
                        timeout: 10000,
                        error: function(data){

                        }
                    });
                return result;
        }

        jQuery("#add_email").click(function(){
            if(jQuery("#jform_email").val()!=""){
                jQuery.ajax({
                type: 'POST',
                url: "index.php?option=com_gm_ceiling&task=addemailtoclient",
                data: {
                    email:jQuery("#jform_email").val(),
                    client_id: jQuery("#client_id").val()
                },
                success: function (data) {
                    console.log(data);
                    jQuery("#emails").val(jQuery("#emails").val()+data+";");
                    var n = noty({
                        timeout: 2000,
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "success",
                        text: "Почта добавлена!"
                    });
                },
                dataType: "text",
                timeout: 10000,
                error: function () {
                    var n = noty({
                        timeout: 2000,
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "error",
                        text: "Ошибка cервер не отвечает"
                    });
                }
            });
            }
        });

        jQuery("#advt_choose").change(function () {
            jQuery("#selected_advt").val(jQuery("#advt_choose").val());
            if(jQuery("#advt_choose").val()==17){
                jQuery("#recoil_choose").show();
                jQuery("#show_window").show();
            }
            else{
                jQuery("#recoil_choose").hide();
                jQuery("#show_window").hide();
            }
        });

        jQuery("#show_window").click(function(){
            jQuery("#mw_container").show();
            jQuery("#mw_recoil").show("slow");
            jQuery("#close_mw").show();
        });

        jQuery("#recoil_choose").change(function(){
            jQuery("#recoil").val(jQuery("#recoil_choose").val());
        });

        jQuery("#add_recoil").click(function(){
            jQuery.ajax({
                type: 'POST',
                url: "index.php?option=com_gm_ceiling&task=saveRecoil",
                data: {
                    fio:jQuery("#new_fio").val(),
                    phone:jQuery("#new_phone").val()
                },
                success: function (data) {
                    option = "<option value = "+data+" selected >"+jQuery("#new_fio").val()+"</opton>";
                    jQuery("#recoil_choose").append(option);
                    jQuery("#close_mw").hide();
                    jQuery("#mw_container").hide();
                    jQuery("#mw_recoil").hide();
                    jQuery("#recoil").val(data);
                    var n = noty({
                        timeout: 2000,
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "success",
                        text: "Отканик добавлен!"
                    });
                },
                dataType: "text",
                timeout: 10000,
                error: function () {
                    var n = noty({
                        timeout: 2000,
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "error",
                        text: "Ошибка при сохранении"
                    });
                }
            });
        });
        function get_selected_calcs(){
            let ids = [];
            jQuery.each(jQuery("[name = 'include_calculation[]']:checked"),function(){
                ids.push(jQuery(this).val());
            });
            return ids;
        }
       
        jQuery("#client_order").click(function () {
            jQuery("input[name='project_verdict']").val(1);
            jQuery("#project_sum").val(<?php echo $project_total_discount?>);
        });

        jQuery("#rec_to_measurement").click(function () {
            jQuery("#project_status").val(1);
            jQuery("#call").toggle();
        });

        jQuery("#refuse_partnership").click(function () {
            jQuery("#project_status").val(15);
            if(jQuery("#selected_advt").val() == 0 && jQuery("#advt_id").val() == ""){
                var n = noty({
                    timeout: 2000,
                    theme: 'relax',
                    layout: 'center',
                    maxVisible: 5,
                    type: "error",
                    text: "Укажите рекламу"
                });
            }
            else {
                jQuery("#form-client").submit();
            }
        });

        jQuery("#refuse_project").click(function () {
            jQuery("#project_status").val(2);
            jQuery("#call").toggle();
        });

        jQuery("#accept_changes").click(function () {
            jQuery("input[name='data_change']").val(1);
        });

        jQuery("#add_call_and_submit").click(function () {
            if (jQuery("#project_status").val() == 1) {
                if (jQuery("#jform_project_gauger").val() == 0) {
                    var n = noty({
                        timeout: 2000,
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "error",
                        text: "Укажите время замера"
                    });
                } else if(jQuery("#selected_advt").val() == 0 && jQuery("#advt_id").val() == ""){
                    var n = noty({
                        timeout: 2000,
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "error",
                        text: "Укажите рекламу"
                    });
                }
                else {
                    jQuery("#form-client").submit();
                }
            } else if (jQuery("#project_status").val() == 2) {
                if(jQuery("#selected_advt").val() == 0 && jQuery("#advt_id").val() == ""){
                    var n = noty({
                        timeout: 2000,
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "error",
                        text: "Укажите рекламу"
                    });
                }
                else if (document.getElementById('call_date').value == '')
                {
                    var n = noty({
                        timeout: 2000,
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "error",
                        text: "Укажите время перезвона"
                    });
                }
                else {
                    jQuery("#form-client").submit();
                }
            }
        });

        jQuery("#change_discount").click(function() {
            jQuery(".new_discount").toggle();
        });

        jQuery("#update_discount").click(function() {
            //if (jQuery("#jform_new_discount").is("valid")) jQuery(".new_discount").hide();
            save_data_to_session(4);
            jQuery.ajax({
                url: "index.php?option=com_gm_ceiling&task=project.changeDiscount",
                data: {
                    project_id: project_id,
                    new_discount: jQuery("#jform_new_discount").val()
                },
                dataType: "json",
                async: true,
                success: function (data) {
                    //console.log(data);
                    location.reload();
                },
                error: function (data) {
                    console.log(data);
                    var n = noty({
                        timeout: 2000,
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "error",
                        text: "Ошибка изменения скидки"
                    });
                }
            });
        });

        jQuery("#ok").click(function () {
            jQuery("input[name='data_delete']").val(1);
            save_data_to_session(3);
        });

        function add_history(id_client, comment) {
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
                        text: "Добавленна запись в историю клиента"
                    });
                    if (jQuery("#client_id").val() == 1) {
                        
                        jQuery("#comments_id").val(jQuery("#comments_id").val() + data + ";");
                    }
                    show_comments();
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
        }

        jQuery("#find_old_client").click(find_old_client);
        jQuery("#radio_clients").click(find_old_client);
        jQuery("#radio_dealers").click(find_old_client);
        jQuery("#radio_designers").click(find_old_client);

        function find_old_client()
        {
            jQuery('#found_clients').find('option').remove();
            var opt = document.createElement('option');
            opt.value = 0;
            opt.innerHTML = "Выберите клиента";
            document.getElementById("found_clients").appendChild(opt);
            jQuery.ajax({
                url: "index.php?option=com_gm_ceiling&task=findOldClients",
                data: {
                    fio: jQuery("#jform_client_name").val(),
                    flag: jQuery('input:radio[name=slider-search]:checked').val()
                },
                dataType: "json",
                async: true,
                success: function (data) {
                    jQuery("#search").show();
                    for (var i = 0; i < data.length; i++) {
                        var opt = document.createElement('option');
                        opt.value = data[i].id;
                        opt.innerHTML = data[i].client_name + ' ' + data[i].client_contacts;
                        document.getElementById("found_clients").appendChild(opt);
                    }
                },
                error: function (data) {
                    var n = noty({
                        timeout: 2000,
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "error",
                        text: "Ошибка сервера"
                    });
                }
            });
        }

        jQuery("#found_clients").change(function () {
            var arr = [<?php echo $phonefrom;?>];
            jQuery.ajax({
                url: "index.php?option=com_gm_ceiling&task=addPhoneToClient",
                data: {
                    id: jQuery("#found_clients").val(),
                    phones: arr,
                    p_id: "<?php echo $this->item->id; ?>",
                    comments: jQuery("#comments_id").val()
                },
                dataType: "json",
                async: true,
                success: function (data) {
                    var who = jQuery('input:radio[name=slider-search]:checked').val();
                    var type;
                    if (who == 'clients')
                    {  
                        location.href = '/index.php?option=com_gm_ceiling&view=clientcard&id='+jQuery("#found_clients").val();
                    }
                    else if (who == 'dealers')
                    {
                        location.href = '/index.php?option=com_gm_ceiling&view=clientcard&type=production&id='+jQuery("#found_clients").val();
                    }
                    else if (who == 'designers')
                    {
                        location.href = '/index.php?option=com_gm_ceiling&view=clientcard&type=designer&id='+jQuery("#found_clients").val();
                    }
                },
                error: function (data) {
                    var n = noty({
                        timeout: 2000,
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "error",
                        text: "Ошибка сервера"
                    });
                }
            });

        });

        jQuery("#select_phones").change(function () {
            var id_client = <?php echo $this->item->id_client; ?>;
            call(jQuery("#select_phones").val());
            add_history(id_client, "Исходящий звонок на " + jQuery("#select_phones").val().replace('+', ''));
        });

        jQuery("#make_call").click(function () {
            phone = jQuery("#jform_client_contacts").val();
            client_id = jQuery("#client_id").val();
            call(phone);
            add_history(client_id, "Исходящий звонок на " + phone);
        });

        jQuery("#broke").click(function(){
            jQuery("#call_up").show();

        });

        jQuery("#add_call_and_submit_up").click(function(){
            client_id = <?php echo $this->item->id_client;?>;
            if (jQuery("#call_date_up").val() == '')
            {
                var n = noty({
                        timeout: 2000,
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "error",
                        text: "Укажите время перезвона"
                    });
                return;
            }
                    jQuery.ajax({
                        url: "index.php?option=com_gm_ceiling&task=changeCallTime",
                        data: {
                            id:<?php echo $call_id;?>,
                            date: jQuery("#call_date_up").val(),
                            comment: jQuery("#call_comment_up").val()
                        },
                        dataType: "json",
                        async: true,
                        success: function (data) {
                            add_history(client_id,"Звонок перенесен");
                        },
                        error: function (data) {
                            var n = noty({
                                timeout: 2000,
                                theme: 'relax',
                                layout: 'center',
                                maxVisible: 5,
                                type: "error",
                                text: "Ошибка сервера"
                            });
                        }
                    });
        });

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
                    //new_comments_id.push(data);
                    //document.getElementById("comments_id").value +=data+";";
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
    });

    jQuery("#cancel").click(function(){
        jQuery("#close_mw").hide();
        jQuery("#mw_container").hide();
        jQuery("#mw_recoil").hide();
    });

    jQuery('.change_calc').click(function() {
        let id = jQuery(this).data('calc_id');
        save_data_to_session(2, id);
    });

    jQuery("#add_phone").click(function () {
        var html = "";
        html += "<tr class = 'dop-phone'>";

        html += "<td><input name='new_client_contacts[]' id='jform_client_contacts' class='inputactive' value=''> </td>";
        html += "<td><button class='clear_form_group btn btn-danger' type='button'><i class='fa fa-trash' aria-hidden='true'></i></button></td> ";
        html += "</tr>";
        jQuery(html).appendTo("#phones-block");
        var classname = jQuery("input[name='new_client_contacts[]']");
        classname.mask("+7 (999) 999-99-99");
        jQuery(".clear_form_group").click(function () {
            jQuery(this).closest(".dop-phone").remove();

        });
        //num_counts++;
    });
    
    jQuery(".clear_form_group").click(function () {
        jQuery(this).closest(".dop-phone").remove();
       // num_counts--;
    });
  
    jQuery("#send_all").click(function () {
        jQuery(".email-all").toggle();
    });

    jQuery("#add_new_dvt").click(function () {
        jQuery("#new_advt_div").toggle();
    })

    jQuery("#save_advt").click(function() {
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
                jQuery("#new_advt_div").hide();
                jQuery("#selected_advt").val(data.id);
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

    jQuery("#add_birthday").click(function () {
        var birthday = jQuery("#jform_birthday").val();
        var id_client = <?php echo $this->item->id_client;?>;
        jQuery.ajax({
            url: "index.php?option=com_gm_ceiling&task=client.addBirthday",
            data: {
                birthday: birthday,
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
                    text: "Дата рождения добавлена"
                });
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

    // Подсказки по городам
    ymaps.ready(init);
    var Data = {};
    function init() {
        // Подключаем поисковые подсказки к полю ввода.
        var suggestView = new ymaps.SuggestView('jform_address');
        input = jQuery('#jform_address');

        suggestView.events.add('select', function (e) {
            var s = e.get('item').value.replace('Россия, ','');
            input.val(s);
        });

        Data.ProjectInfoYMaps = $("#jform_address").siblings("ymaps");
        Data.ProjectInfoYMaps.click(hideYMaps);
    }

    function hideYMaps() {
        setTimeout(function () {
            Data.ProjectInfoYMaps.hide();
            $("#jform_house").focus();
        }, 75);
    }

</script>
