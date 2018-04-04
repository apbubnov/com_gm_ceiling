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
    
    $dop_num_model = Gm_ceilingHelpersGm_ceiling::getModel('dop_numbers_of_users');
    $model_calculations = Gm_ceilingHelpersGm_ceiling::getModel('calculations');
    $mountModel = Gm_ceilingHelpersGm_ceiling::getModel('mount');
    $model_api_phones = Gm_ceilingHelpersGm_ceiling::getModel('api_phones');
    $repeat_model = Gm_ceilingHelpersGm_ceiling::getModel('repeatrequest');
    $model_client_phones = Gm_ceilingHelpersGm_ceiling::getModel('client_phones');
    $model_client = Gm_ceilingHelpersGm_ceiling::getModel('client');
    $projects_model = Gm_ceilingHelpersGm_ceiling::getModel('projects');
    $request_model = Gm_ceilingHelpersGm_ceiling::getModel('requestfrompromo');
    $dop_contacts = Gm_ceilingHelpersGm_ceiling::getModel('Clients_dop_contacts');
    $recoil_model = Gm_ceilingHelpersGm_ceiling::getModel('recoil');
    $canvas_model = Gm_ceilingHelpersGm_ceiling::getModel('canvases');
    $model_components = Gm_ceilingHelpersGm_ceiling::getModel('components');

    $dop_num = $dop_num_model->getData($userId)->dop_number;
    $_SESSION['user_group'] = $user_group;
    $_SESSION['dop_num'] = $dop_num;

    Gm_ceilingHelpersGm_ceiling::create_client_common_estimate($this->item->id);
    Gm_ceilingHelpersGm_ceiling::create_common_estimate_mounters($this->item->id);
    Gm_ceilingHelpersGm_ceiling::create_estimate_of_consumables($this->item->id);
    //транспорт
    $transport = Gm_ceilingHelpersGm_ceiling::calculate_transport($this->item->id);
    $sum_transport = $transport['client_sum'];
    $sum_transport_1 = $transport['mounter_sum'];
    /* минимальная сумма заказа */
    $mount_transport = $mountModel->getDataAll($this->item->dealer_id);
    $min_project_sum = (empty($mount_transport->min_sum)) ? 0 : $mount_transport->min_sum;
    $min_components_sum = (empty($mount_transport->min_components_sum)) ? 0 : $mount_transport->min_components_sum;
    $project_total = 0;
    $project_total_discount = 0;
    $total_square = 0;
    $total_perimeter = 0;
    $calculations = $model_calculations->new_getProjectItems($this->item->id);

    foreach ($calculations as $calculation) {
        $calculation->dealer_canvases_sum = double_margin($calculation->canvases_sum, 0/*$this->item->gm_canvases_margin*/, $this->item->dealer_canvases_margin);
        $calculation->dealer_components_sum = double_margin($calculation->components_sum, 0 /*$this->item->gm_components_margin*/, $this->item->dealer_components_margin);
        $calculation->dealer_gm_mounting_sum = double_margin($calculation->mounting_sum, 0 /*$this->item->gm_mounting_margin*/, $this->item->dealer_mounting_margin);
        $calculation->dealer_canvases_sum_1 = margin($calculation->canvases_sum, 0/*$this->item->gm_canvases_margin*/);
        $calculation->dealer_components_sum_1 = margin($calculation->components_sum, 0/* $this->item->gm_components_margin*/);
        $calculation->dealer_gm_mounting_sum_1 = margin($calculation->mounting_sum, 0/* $this->item->gm_mounting_margin*/);
        $calculation->calculation_total = $calculation->dealer_canvases_sum + $calculation->dealer_components_sum + $calculation->dealer_gm_mounting_sum;
        //$calculation->calculation_total_discount = $calculation->calculation_total * ((100 - $this->item->project_discount) / 100);
        $calculation->calculation_total_discount = $calculation->calculation_total * ((100 - $calculation->discount) / 100);
        $project_total += $calculation->calculation_total;
        $project_total_discount += $calculation->calculation_total_discount;
        if ($user->dealer_type != 2) {
            $dealer_canvases_sum_1 = margin($calculation->canvases_sum, 0/*$this->item->gm_canvases_margin*/);
            $dealer_components_sum_1 = margin($calculation->components_sum, 0/*$this->item->gm_components_margin*/);
            $dealer_gm_mounting_sum_1 = margin($calculation->mounting_sum, 0/*$this->item->gm_mounting_margin*/);
            $calculation_total_1 = $dealer_canvases_sum_1 + $dealer_components_sum_1;
            $dealer_gm_mounting_sum_11 += $dealer_gm_mounting_sum_1;
            $calculation_total_11 += $calculation_total_1;
            $project_total_1 = $calculation_total_1 + $dealer_gm_mounting_sum_1;
        }
        $project_total_11 += $project_total_1;
        $calculation_total = $calculation->calculation_total;
    }

    $project_total_discount_transport = $project_total_discount + $sum_transport;

    $project_total = $project_total  + $sum_transport;
    $project_total_discount = $project_total_discount  + $sum_transport;

    // календарь
    $month = date("n");
    $year = date("Y");
    $FlagCalendar = [3, $user->dealer_id];
    $calendar = Gm_ceilingHelpersGm_ceiling::DrawCalendarTar($userId, $month, $year, $FlagCalendar);
    //----------------------------------------------------------------------------------

    // все замерщики
    $AllGauger = $model_calculations->FindAllGauger($user->dealer_id, 22);
    //----------------------------------------------------------------------------------

?>

<script src="https://api-maps.yandex.ru/2.1/?lang=ru_RU" type="text/javascript"></script>
<link rel="stylesheet" href="/components/com_gm_ceiling/views/project/css/style.css" type="text/css" />

<button id = "back_btn" class = "btn btn-primary"><i class="fa fa-arrow-left" aria-hidden="true"></i> Назад</button>

<h2 class="center">Просмотр проекта</h2>
<?php if ($this->item) : ?>
    <?php
        $need_choose = false;
        $jinput = JFactory::getApplication()->input;
        $phoneto = $jinput->get('phoneto', '0', 'STRING');
        $phonefrom = $jinput->get('phonefrom', '0', 'STRING');
        $call_id = $jinput->get('call_id', 0, 'INT');
        
        $all_advt = $model_api_phones->getAdvt();
        if (!empty($phoneto) && !empty($phonefrom)) {
            $reklama = $model_api_phones->getNumberInfo($phoneto);
            $write = $reklama->number .' '.$reklama->name . ' ' . $reklama->description;
        } elseif (!empty($this->item->api_phone_id)) {
            
            $repeat_advt = $repeat_model->getDataByProjectId($this->item->id);
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
        }
        
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
    <div id="modal-window-container">
		<button type="button" id="close-tar"><i class="fa fa-times fa-times-tar" aria-hidden="true"></i></button>
		<div id="modal-window-call-tar">
			<h6>Введите ФИО</h6>
			<p><input type="text" id="new_fio" placeholder="ФИО" required></p>
            <h6>Введите номер телефона</h6>
			<p><input type="text" id="new_phone" placeholder="ФИО" required></p>
			<p><button type="button" id="add_recoil" class="btn btn-primary">Сохранить</button>  <button type="button" id="cancel" class="btn btn-primary">Отмена</button></p>
	    </div>
    </div>
    <div class="container">
        <div class="row">
            <div class="item_fields">
                <h4>Информация по проекту № <?php echo $this->item->id ?></h4>
                <form id="form-client" action="/index.php?option=com_gm_ceiling&task=project.recToMeasurement&type=gmmanager&subtype=calendar" method="post" class="form-validate form-horizontal" enctype="multipart/form-data">
                    <input name="project_id" id = "project_id"  value="<?php echo $this->item->id; ?>" type="hidden">
                    <input name="client_id" id="client_id" value="<?php echo $this->item->id_client; ?>" type="hidden">
                    <input name="advt_id" id="advt_id" value="<?php echo $reklama->id; ?>" type="hidden">
                    <input name="comments_id" id="comments_id" value="<?php if (isset($_SESSION['comments'])) echo $_SESSION['comments']; ?>" type="hidden">
                    <input name="status" id="project_status" value="" type="hidden">
                    <input name="call_id" value="<?php echo $call_id; ?>" type="hidden">
                    <input name="type" value="gmmanager" type="hidden">
                    <input name="subtype" value="calendar" type="hidden">
                    <input name="data_change" value="0" type="hidden">
                    <input name="data_delete" value="0" type="hidden">
                    <input name="selected_advt" id="selected_advt" value="<?php echo (!empty($this->item->api_phone_id))? $this->item->api_phone_id: '0' ?>" type="hidden">
                    <input name = "recoil" id = "recoil" value = "" type = "hidden">
                    <input id="jform_new_project_calculation_daypart" name="new_project_calculation_daypart" value = "<?php if(isset($_SESSION['time'])){ echo $_SESSION['time']; } else if ($this->item->project_calculation_date != null && $this->item->project_calculation_date != "0000-00-00 00:00:00") { echo substr($this->item->project_calculation_date, 11); }?>"class="inputactive" type="hidden">
                    <input name = "project_new_calc_date" id = "jform_project_new_calc_date" class ="inputactive" value="<?php if(isset($_SESSION['date'])){ echo $_SESSION['date']; } else if (isset($this->item->project_calculation_date)) { echo $this->item->project_calculation_date;}?>" type="hidden">
                    <input name = "project_gauger" id = "jform_project_gauger" class ="inputactive" value="<?php if(isset($_SESSION['gauger'])){ echo $_SESSION['gauger']; } else if ($this->item->project_calculator != null) { echo $this->item->project_calculator; } else {echo "0";}?>" type="hidden">
                    <input id="project_sum" name="project_sum" value="<?php echo $project_total_discount ?>" type="hidden">
                    <input id="project_sum_transport" name="project_sum_transport" value="<?php echo $project_total_discount_transport ?>" type="hidden">
                    <input id = "emails" name = "emails" value = "" type = "hidden"> 
                    <div class="row">
                        <div class="col-xs-6 col-sm-6 col-md-6 col-lg-6">
                            <table class="table">
                                <tr>
                                    <th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_PROJECT_CLIENT_ID'); ?></th>
                                    <td>
                                        <input name="new_client_name" class="<?php if ($this->item->id_client != "1") echo "inputactive"; else echo "inputactive"; ?>"
                                        id="jform_client_name" value="<?php if (isset($_SESSION['FIO'])) {
                                        echo $_SESSION['FIO'];
                                        } else echo $this->item->client_id; ?>" placeholder="ФИО клиента" type="text">
                                    </td>
                                    <?php if($this->item->id_client == "1"){?>
                                        <td>
                                            <button id="find_old_client" type="button" class="btn btn-primary"><i class="fa fa-search" aria-hidden="true"></i></button><br>
                                        </td>
                                    <?php  }?>
                                </tr>
                                <?php if($this->item->id_client == "1"){ ?>
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
                                <?php }?>
                                <tr id="search" style="display : none;">
                                    <th>
                                        Выберите клиента из списка:
                                    </th>
                                    <td>
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
                                    <td>
                                        <?php if($this->item->id_client == 1){?>
                                            <input name="new_client_contacts[]" id="jform_client_contacts" class="inputactive" value="<?php echo $phonefrom;?>" placeholder="Телефон клиента" type="text">
                                        <?php } elseif(count($cl_phones)==1) { ?>
                                            <input name="new_client_contacts[<?php echo  '\''.$cl_phones[0]->phone.'\''?>]" id="jform_client_contacts" class="inputactive" value="<?php echo $cl_phones[0]->phone; ?>" type="text">
                                        <?php } elseif(count($cl_phones)>1) {
                                            foreach ($cl_phones as $value) {  ?>
                                         <input name="new_client_contacts[<?php echo '\''.$value->phone.'\''?>]" id="jform_client_contacts" class="inputactive" value="<?php echo  strval($value->phone); ?>" type="text">
                                        <?php }
                                        } ?>
                                    </td>
                                    <?php if (count($cl_phones) == 1): ?>
                                        <td>
                                            <button id="make_call" type="button" class="btn btn-primary"><i class="fa fa-phone" aria-hidden="true"></i></button>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                                <tr>
                                    <th></th>
                                    <td colspan=2>
                                        <div id="phones-block"></div>
                                    </td>
                                </tr>
                                <?php if (count($cl_phones) > 1): ?>
                                    <tr>
                                        <th>
                                            Сделать звонок:
                                        </th>
                                        <td>
                                            <select id="select_phones" class="inputactive">
                                                <option value='0' disabled selected>Выберите номер для звонка:</option>
                                                <?php foreach ($cl_phones as $item): ?>
                                                    <option value="<?php echo $item->phone; ?>"><?php echo $item->phone; ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </td>
                                    </tr>
                                <?php endif; ?>
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
                                <?php if (isset($_SESSION['phones']) && count($_SESSION['phones'] > 1)) {
                                    for ($i = 1; $i < count($_SESSION['phones']); $i++) { ?>
                                        <tr class='dop-phone'>
                                            <th></th>
                                            <td>
                                                <input name='new_client_contacts[<?php echo $i; ?>]' id='jform_client_contacts' class='inputactive' value="<?php echo $_SESSION['phones'][$i]; ?>">
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
                                        <input name="new_email" id="jform_email" class="inputactive" value="" placeholder="e-mail" type="text">
                                    </td>
                                    <td>
                                        <button type="button" class = "btn btn-primary" id = "add_email">Ок</button>
                                    </td>
                                </tr>
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
                                    <td><input name="new_address" id="jform_address" class="inputactive" value="<?php if (isset($_SESSION['address'])) {
                                        echo $_SESSION['address'];
                                        } else echo $street ?>" placeholder="Адрес" type="text" required="required"></td>
                                </tr>
                                <tr class="controls">
                                    <td>Дом / Корпус</td>
                                    <td>
                                        <input name="new_house" id="jform_house" value="<?php if (isset($_SESSION['house'])) {echo $_SESSION['house'];
                                            } else echo $house ?>" class="inputactive" style="width: 50%; margin-bottom: 1em; float: left; margin: 0 5px 0 0;" placeholder="Дом" required="required" aria-required="true" type="text">
                                        <input name="new_bdq" id="jform_bdq"  value="<?php if (isset($_SESSION['bdq'])) {echo $_SESSION['bdq'];
                                            } else echo $bdq ?>" class="inputactive"  style="width: calc(50% - 5px); margin-bottom: 1em;" placeholder="Корпус" aria-required="true" type="text">
                                    </td>
                                </tr>
                                <tr class="controls">
                                    <td> Квартира / Подъезд</td>
                                    <td>
                                        <input name="new_apartment" id="jform_apartment" value="<?php if (isset($_SESSION['apartment'])) {echo $_SESSION['apartment'];
                                            } else echo $apartment ?>" class="inputactive" style="width:50%;margin-bottom:1em;margin-right: 5px;float: left;" placeholder="Квартира"  aria-required="true" type="text">
                                
                                        <input name="new_porch" id="jform_porch"  value="<?php if (isset($_SESSION['porch'])) {echo $_SESSION['porch'];
                                            } else echo $porch ?>" class="inputactive"   style="width: calc(50% - 5px); margin-bottom: 1em;" placeholder="Подъезд"  aria-required="true" type="text">
                                    </td>
                                </tr>
                                <tr class="controls">
                                    <td> Этаж / Код домофона</td>
                                    <td>
                                        <input name="new_floor" id="jform_floor"  value="<?php if (isset($_SESSION['floor'])) {echo $_SESSION['floor'];
                                            } else echo $floor ?>" class="inputactive"  style="width:50%; margin-bottom:1em;  margin: 0 5px  0 0; float: left;" placeholder="Этаж" aria-required="true" type="text">
                                
                                        <input name="new_code" id="jform_code"  value="<?php if (isset($_SESSION['code'])) {echo $_SESSION['code'];
                                            } else echo $code ?>" class="inputactive"  style="width: calc(50% - 5px); margin-bottom: 1em;" placeholder="Код" aria-required="true" type="text">
                                    </td>
                                </tr>
                                <tr>
                                    <th>Дата и время замера</th>
                                    <td>
                                        <div id="calendar-container">
                                            <div class="btn-small-l">
                                                <button id="button-prev" class="button-prev-small" type="button" class="btn btn-primary"><i class="fa fa-arrow-left" aria-hidden="true"></i></button>
                                            </div>
                                            <?php echo $calendar; ?>
                                            <div class="btn-small-r">
                                                <button id="button-next" class="button-next-small" type="button" class="btn btn-primary"><i class="fa fa-arrow-right" aria-hidden="true"></i></button>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Примечание менеджера</th>
                                    <td>
                                        <input name="gmmanager_note" id="gmmanager_note" class="inputactive"
                                            value="<?php if (isset($_SESSION['manager_comment'])) {
                                            echo $_SESSION['manager_comment'];
                                            } else echo $this->item->gm_manager_note; ?>">
                                    </td>
                                </tr>
                                <tr>
                                    <th>Менеджер</th>
                                    <td>
                                        <input name="Manager_name" id="manager_name" class="inputhidden"
                                            value="<?php if (isset($this->item->read_by_manager)&&$this->item->read_by_manager!=1) {
                                            echo JFactory::getUser($this->item->read_by_manager)->name;
                                            } ?>">
                                    </td>
                                </tr>
                                <tr>
                                    <th>Замерщик</th>
                                    <td>
                                        <input name="calculator_name" id="calculator_name" class="inputhidden"
                                            value="<?php if (isset($this->item->project_calculator)) {
                                            echo JFactory::getUser($this->item->project_calculator)->name;
                                            }?>">
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-sm-6 col-md-6 col-lg-6">
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
            <table class="table calculation_sum">
                <?php if ($this->item->project_verdict == 0) { ?>
                    <tr>
                        <td style=" padding-left:0;"><a class="btn btn-primary" id="change_discount">Изменить величину
                                скидки</a></td>
                    </tr>
                <?php } ?>
                <?php $skidka = ($calculation_total - $project_total_1) / $calculation_total * 100; ?>
                <tbody class="new_discount" style="display: none">
                <tr>

                    <td>
                        <label id="jform_discoint-lbl" for="jform_new_discount">Новый процент скидки:<span class="star">&nbsp;*</span></label>
                        <input name="new_discount" id="jform_new_discount" value="" placeholder="Новый % скидки"
                               max='<?= round($skidka, 0); ?>' type="number">
                        <input name="isDiscountChange" value="0" type="hidden">
                    </td>
                    <td>
                        <button type="button" id="update_discount" class="btn btn btn-primary">
                            Ок
                        </button>
                    </td>

                </tr>
                </tbody>
            </table>
            <!--</form>-->
        </div>
    </div>
    </div>
    <?php echo "<h3>Расчеты для проекта</h3>"; ?>
    <!-- Nav tabs -->
    <ul class="nav nav-tabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" data-toggle="tab" href="#summary" role="tab">Общее</a>
        </li>
        <?php foreach ($calculations as $k => $calculation) { ?>
            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#calculation<?php echo $calculation->id; ?>"
                   role="tab"><?php echo $calculation->calculation_title; ?></a>
            </li>
        <?php } ?>
        <li class="nav-item">
            <button class="nav-link" id="add_calc" type="button">
                <i class="fa fa-plus-square-o" aria-hidden="true"></i>
            </button>
        </li>
    </ul>
    <!-- Tab panes -->
    <div class="tab-content">
        <div class="tab-pane active" id="summary" role="tabpanel">
            <table>
                <tr>
                    <th class="section_header" id="sh_ceilings">Потолки <i class="fa fa-sort-desc"
                                                                           aria-hidden="true"></i></th>
                    <th></th>
                </tr>
                <?php $project_total = 0;
                $project_total_discount = 0;
                $dealer_gm_mounting_sum_1 = 0;
                $calculation_total_1 = 0;
                $project_total_1 = 0;
                $dealer_gm_mounting_sum_11 = 0;
                $calculation_total_11 = 0;
                $project_total_11 = 0;
                $kol = 0;
                $sum_transport_discount_total = 0;
                $sum_transport_total = 0;

                foreach ($calculations as $calculation) {
                    $dealer_canvases_sum = double_margin($calculation->canvases_sum, 0 /*$this->item->gm_canvases_margin*/, $this->item->dealer_canvases_margin);
                    $dealer_components_sum = double_margin($calculation->components_sum, 0/* $this->item->gm_components_margin*/, $this->item->dealer_components_margin);
                    $dealer_gm_mounting_sum = double_margin($calculation->mounting_sum, 0/*$this->item->gm_mounting_margin*/, $this->item->dealer_mounting_margin);

                    //$calculation_total_discount = $calculation_total * ((100 - $this->item->project_discount) / 100);

                    if ($user->dealer_type != 2) {
                        $dealer_canvases_sum_1 = margin($calculation->canvases_sum, 0/*$this->item->gm_canvases_margin*/);
                        $dealer_components_sum_1 = margin($calculation->components_sum, 0/*$this->item->gm_components_margin*/);
                        $dealer_gm_mounting_sum_1 = margin($calculation->mounting_sum, 0/*$this->item->gm_mounting_margin*/);
                        $dealer_gm_mounting_sum_11 += $dealer_gm_mounting_sum_1;
                        $calculation_total_1 = $dealer_canvases_sum_1 + $dealer_components_sum_1;
                        $calculation_total_11 += $calculation_total_1;
                        $project_total_1 = $calculation_total_1 + $dealer_gm_mounting_sum_1;
                        $project_total_11 += $project_total_1;
                    }
                    $calculation->calculation_title;
                    $total_square += $calculation->n4;
                    $total_perimeter += $calculation->n5;
                    $canvas = $dealer_canvases_sum;
                    // --------------------------Высчитываем транспорт в отдельную строчку -----------------------------------------------------
                    //$sum_transport = 0;  $sum_transport_discount = 0;
                    //$mount_transport = $mountModel->getDataAll();

                    $calculation_total = $dealer_canvases_sum + $dealer_components_sum + $dealer_gm_mounting_sum ;
                    $calculation_total_discount = $calculation_total * ((100 - $calculation->discount) / 100);
                    $project_total += $calculation_total;
                    $project_total_discount += $calculation_total_discount;

                    ?>

                    <tr class="section_ceilings">
                        <td class="include_calculation">
                            <input name='include_calculation[]' value='<?php echo $calculation->id; ?>' type='checkbox'
                                   checked="checked">
                            <input name='calculation_total[<?php echo $calculation->id; ?>]'
                                   value='<?php echo $calculation_total; ?>' type='hidden'>
                            <input name='calculation_total_discount[<?php echo $calculation->id; ?>]'
                                   value='<?php echo $calculation_total_discount; ?>' type='hidden'>
                            <input name='total_square[<?php echo $calculation->id; ?>]'
                                   value='<?php echo $calculation->n4; ?>' type='hidden'>
                            <input name='total_perimeter[<?php echo $calculation->id; ?>]'
                                   value='<?php echo $calculation->n5; ?>' type='hidden'>
                            <input name='calculation_total1[<?php echo $calculation->id; ?>]'
                                   value='<?php echo $calculation_total_1; ?>' type='hidden'>
                            <input name='calculation_total2[<?php echo $calculation->id; ?>]'
                                   value='<?php echo $dealer_gm_mounting_sum_1; ?>' type='hidden'>
                            <input name='calculation_total3[<?php echo $calculation->id; ?>]'
                                   value='<?php echo $project_total_1; ?>' type='hidden'>
                            <input name='canvas[<?php echo $calculation->id; ?>]'
                                value='<?php echo $canvas; ?>' type='hidden'>
                            <?php echo $calculation->calculation_title; ?>
                        </td>
                    </tr>
                    <tr class="section_ceilings">
                        <?php if ($calculation->discount != 0) { ?>
                            <td>Цена / -<?php echo $calculation->discount ?>% :</td>
                            <td id="calculation_total"> <?php echo round($calculation_total, 0); ?> руб. /</td>
                            <td id="calculation_total_discount"> <?php echo round($calculation_total_discount ,0); ?>
                                руб.
                            </td>
                        <?php } else { ?>
                            <td>Итого</td>
                            <td id="calculation_total"> <?php echo round($calculation_total , 0); ?> руб.</td>
                            <td></td>
                        <?php } ?>

                    </tr>

                    <?php if($calculation->discount > 0) $kol++;
                } ?>
                <tr>
                    <th>Общая S/общий P :</th>
                    <th id="total_square">
                        <?php echo $total_square; ?>м<sup>2</sup> /
                    </th>
                    <th id="total_perimeter">
                        <?php echo $total_perimeter; ?> м
                    </th>
                </tr>
                <tr>
                    <th>
                        Транспортные расходы
                    </th>
                    <th></th>
                    <th></th>
                </tr>
                <tr>
                    <td style="width: 45%;">

                        <p><input name="transport"  class="radio" id ="transport" value="1"  type="radio"  <?php if($this->item->transport == 1 ) echo "checked"?>><label for = "transport">Транспорт по городу</label></p>
                        <div class="row sm-margin-bottom" style="width: 45%; display:none;" id="transport_dist_col" >
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <div class="advanced_col1" style="width: 35%;">
                                        <label>Кол-во выездов</label>
                                    </div>
                                    <div class="advanced_col2" style="width: 20%;"></div>
                                </div>

                                <div class="form-group">

                                    <div class="advanced_col1" style="width: 35%;">
                                        <input name="jform[distance_col_1]" id="distance_col_1" style="width: 100%;" value="<?php echo $this->item->distance_col; ?>" class="form-control" placeholder="раз" type="tel">
                                    </div>
                                    <div class="advanced_col2" style="width: 20%;">
                                        <button type="button" name="click_transport" class="btn btn-primary">Ок</button>
                                    </div>
                                </div>

                            </div>

                        </div>
                        <p><input name="transport" class="radio" id = "distanceId" value="2" type="radio" <?php if( $this->item->transport == 2) echo "checked"?>><label for = "distanceId">Выезд за город</label></p>

                        <div class="row sm-margin-bottom" style="width: 45%; display:none;" id="transport_dist" >
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <div class="advanced_col1" style="width: 35%;">
                                        <label>Кол-во,км</label>
                                    </div>
                                    <div class="advanced_col2" style="width: 35%;">
                                        <label>Кол-во выездов</label>
                                    </div>
                                    <div class="advanced_col3" style="width: 20%;"></div>
                                </div>

                                <div class="form-group">
                                    <div class="advanced_col1" style="width: 35%;">
                                        <input name="jform[distance]" id="distance" style="width: 100%;" value="<?php echo $this->item->distance; ?>" class="form-control" placeholder="км." type="tel">
                                    </div>
                                    <div class="advanced_col2" style="width: 35%;">
                                        <input name="jform[distance_col]" id="distance_col" style="width: 100%;" value="<?php echo $this->item->distance_col; ?>" class="form-control" placeholder="раз" type="tel">
                                    </div>
                                    <div class="advanced_col3" style="width: 20%;">
                                        <button type="button" name="click_transport" class="btn btn-primary">Ок</button>
                                    </div>
                                </div>

                            </div>

                        </div>
                        <p><input name="transport" class="radio" id ="no_transport" value="0" type="radio" <?php if($this->item->transport == 0 ) echo "checked"?>> <label for="no_transport">Без транспорта</label></p>
                    </td>
                    <th></th>
                    <th></th>
                </tr>
                <tr>
                    <?php

                    //-------------------------Себестоимость транспорта-------------------------------------
                    $project_total_11 = $project_total_11 + $sum_transport_1;
                    $project_total = $project_total + $sum_transport;
                    $project_total_discount = $project_total_discount + $sum_transport;
                    ?>
                    <th> Транспорт</th>
                    <td id="transport_sum"> <?=$sum_transport;?> руб. </td>
                    <input id="transport_suma" value='<?php echo $sum_transport; ?>' type='hidden'>
                    <td></td>
                </tr>
                <tr>

                <?php if ($kol > 0) { ?>
                    <th>Итого/ - %:
                    </th>
                    <th id="project_total"><span class="sum"><?php echo round($project_total, 0); ?></span> руб. /</th>
                    <th id="project_total_discount">
                        <span class="sum">
                     <?php //---------------  Если сумма проекта меньше 3500, то делаем сумму проекта 3500  -----------------------
                    if($dealer_canvases_sum == 0 && $project_total_discount < $min_components_sum){
                        if($min_components_sum>0){
                            $project_total_discount = $min_components_sum;
                        }
                    } 
                    elseif ($dealer_gm_mounting_sum_11 == 0 && $project_total_discount < $min_components_sum) {
                        if($min_components_sum>0){
                            $project_total_discount = $min_components_sum;
                        }
                         echo round($project_total_discount, 0);  ?> руб.</th> <?}
                    elseif($project_total_discount < $min_project_sum && $project_total_discount > 0) {
                        if($min_project_sum>0){
                            $project_total_discount = $min_project_sum;
                        }
                        echo round($project_total_discount, 0);  ?> руб.</th>
                        </span> <span class="dop" style="font-size: 9px;" > * минимальная сумма заказа <?php echo $min_project_sum?>р. </span>
                    <?php } else echo round($project_total_discount, 0);  ?> руб.</span> <span class="dop" style="font-size: 9px;" ></span></th>

                <?php }
                else { ?>
                <th>Итого</th>
                <th id="project_total">
                <span class="sum">
                    <?php
                    if ($this->item->new_project_sum == 0) {
                        if($project_total < $min_components_sum && $project_total > 0 && $dealer_canvases_sum == 0)  {
                            if($min_components_sum>0){
                                $project_total = $min_components_sum;
                            } 
                        } 
                        elseif($project_total < $min_project_sum && $project_total > 0 && $dealer_gm_mounting_sum_11 != 0)  {
                            if($min_project_sum>0){
                                $project_total = $min_project_sum;
                            } 
                        }
                        
                        echo round($project_total, 2);
                    } else {
                        echo round($this->item->new_project_sum, 2);
                    }
                } ?>
            </span>
            <span class="dop" style="font-size: 9px;">
            <?php if ($project_total <= $min_components_sum && $project_total_discount > 0 && $dealer_canvases_sum == 0):?>
                     * минимальная сумма заказа <?php echo $min_components_sum?>р.
                <?php elseif ($project_total <= $min_project_sum && $project_total_discount > 0 && $dealer_gm_mounting_sum_11 != 0): ?>
                     * минимальная сумма заказа <?php echo $min_project_sum?>р.<?php endif;?>
                     
                      </span>
                </th>
            </tr>
                <tr>
                    <td></td>
                    <td id="calculation_total1"><?php echo round($calculation_total_11, 0) ?></td>
                    <td id="calculation_total2"><?php echo round($dealer_gm_mounting_sum_11, 0) ?></td>
                    <td id="calculation_total3"><?php echo round($project_total_11, 0); ?></td>
                </tr>
                <tr>
                    <th class="section_header" id="sh_estimate"> Сметы <i class="fa fa-sort-desc"
                                                                          aria-hidden="true"></i></th>
                </tr>
                <?php foreach ($calculations as $calculation) { ?>
                <tr class="section_estimate" id="section_estimate_<?= $calculation->id; ?>" style="display:none;">
                    <td><?php echo $calculation->calculation_title; ?></td>
                    <td>
                        <?php
                        $path = "/costsheets/".md5($calculation->id."client_single").".pdf";

                        $pdf_names[] = array("name" => $calculation->calculation_title, "filename" => md5($calculation->id . "client_single") . ".pdf", "id" => $calculation->id);
                        ?>
                        <?php if (file_exists($_SERVER['DOCUMENT_ROOT'] . $path)) { ?>
                            <a href="<?php echo $path; ?>" class="btn btn-secondary" target="_blank">Посмотреть</a>
                        <?php } else { ?>
                            -
                        <?php } ?>
                    </td>
                    <?php }
                    $json = json_encode($pdf_names); ?>
                </but>
                <?php if (count($calculations) > 0) { ?>
                    <tr class="section_estimate" style="display:none;">
                        <td><b>Отправить все сметы <b></td>
                    </tr>
                    <tr class="section_estimate" style="display:none;">
                        <td>
                            <div class="email-all" style="float: left;">
                                <input list="email" name="all-email" id="all-email1" class="form-control" style="width:200px;"
                                placeholder="Адрес эл.почты" type="text">
                                <datalist id="email">
                                    <?php foreach ($contact_email AS $em) {?>
                                    <option value="<?=$em->contact;?>"> <?php }?>
                                </datalist>
                            </div>
                            <div class="file_data">
                                <div class="file_upload">
                                    <input type="file" class="dopfile" name="dopfile" id="dopfile">
                                </div>
                                <div class="file_name"></div>
                                <script>jQuery(function () {
                                        jQuery("div.file_name").html("Файл не выбран");
                                        jQuery("div.file_upload input.dopfile").change(function () {
                                            var filename = jQuery(this).val().replace(/.*\\/, "");
                                            jQuery("div.file_name").html((filename != "") ? filename : "Файл не выбран");
                                        });
                                    });</script>
                            </div>
                        </td>
                        <td>
                            <button class="btn btn-primary" id="send_all_to_email1" type="button">Отправить</button>
                        </td>
                    </tr>
                <?php } ?>

                <?php if ($this->item->project_mounter != 'Монтажная бригада ГМ') { ?>
                    <tr>
                        <th id="sh_mount"> Наряд на монтаж <i class="fa fa-sort-desc" aria-hidden="true"></i></th>
                    </tr>

                    <?php foreach ($calculations as $calculation) { ?>
                        <tr class="section_mount" id="section_mount_<?= $calculation->id; ?>" style="display:none;">
                        <td><?php echo $calculation->calculation_title; ?></td>
                        <td>
                            <?php $path = "/costsheets/" . md5($calculation->id . "mount_single") . ".pdf"; ?>
                            <?php $pdf_names_mount[] = array("name" => $calculation->calculation_title, "filename" => md5($calculation->id . "mount_single") . ".pdf", "id" => $calculation->id); ?>
                            <?php if (file_exists($_SERVER['DOCUMENT_ROOT'] . $path)) { ?>
                                <a href="<?php echo $path; ?>" class="btn btn-secondary" target="_blank">Посмотреть</a>
                            <?php } else { ?>
                                После договора
                            <?php } ?>
                        </td>

                    <?php }
                    $json1 = json_encode($pdf_names_mount); ?>
                    </tr>
                    <?php if (count($calculations) > 1) { ?>
                        <tr class="section_mount" style="display:none;">
                            <td><b>Отправить все наряды на монтаж<b></td>
                        </tr>
                        <tr class="section_mount" style="display:none;">
                            <td>
                                <div class="email-all" style="float: left;">
                                    <input name="all-email" id="all-email2" class="form-control" style="width:200px;"
                                           value="" placeholder="Адрес эл.почты" type="text">

                                </div>
                                <div class="file_data">
                                    <div class="file_upload">
                                        <input type="file" class="dopfile1" name="dopfile1" id="dopfile1">
                                    </div>
                                    <div class="file_name1"></div>
                                    <script>jQuery(function () {
                                            jQuery("div.file_name1").html("Файл не выбран");
                                            jQuery("div.file_upload input.dopfile1").change(function () {
                                                var filename = jQuery(this).val().replace(/.*\\/, "");
                                                jQuery("div.file_name1").html((filename != "") ? filename : "Файл не выбран");
                                            });
                                        });</script>
                                </div>
                            </td>
                            <td>
                                <button class="btn btn-primary" id="send_all_to_email2" type="button">Отправить</button>
                            </td>
                        </tr>
                    <?php } ?>
                <?php } ?>
                <!-- Общая смета для клиента -->

                <tr>
                    <td><b>Отправить общую смету <b></td>
                    <td>
                        <?php
                        $path = "/costsheets/" . md5($this->item->id . "client_common") . ".pdf"; if (file_exists($_SERVER['DOCUMENT_ROOT'] . $path)) { ?>
                            <a href="<?php echo $path; ?>" class="btn btn-secondary" target="_blank">Посмотреть</a>
                        <?php } else { ?>
                            -
                        <?php }
                        $pdf_names[] = array("name" => "Подробная смета", "filename" => md5($this->item->id . "client_common") . ".pdf", "id" => $this->item->id);
                        $json2 = json_encode($pdf_names); ?>
                    </td>
                    <td></td>
                </tr>
                <?php  if (file_exists($_SERVER['DOCUMENT_ROOT'] . $path)) { ?>
                    <tr >
                        <td>
                            <div class="email-all" style="float: left;">
                               <input list="email" name="all-email" id="all-email3" class="form-control" style="width:200px;"
                                placeholder="Адрес эл.почты" type="text">
                                <datalist id="email">
                                    <?php foreach ($contact_email AS $em) {?>
                                    <option value="<?=$em->contact;?>"> <?php }?>
                                </datalist>
                            </div>
                            <div class="file_data">
                                <div class="file_upload">
                                    <input type="file" class="dopfile2" name="dopfile2" id="dopfile2">
                                </div>
                                <div class="file_name2"></div>
                                <script>jQuery(function () {
                                        jQuery("div.file_name2").html("Файл не выбран");
                                        jQuery("div.file_upload input.dopfile2").change(function () {
                                            var filename = jQuery(this).val().replace(/.*\\/, "");
                                            jQuery("div.file_name2").html((filename != "") ? filename : "Файл не выбран");
                                        });
                                    });</script>
                            </div>
                        </td>
                        <td>

                            <button class="btn btn-primary" id="send_all_to_email3" type="button">Отправить</button>
                        </td>
                        <td>

                        </td>
                    </tr>
                    <!-- общий наряд на монтаж-->
                    <tr>
                        <td><b>Общий наряд на монтаж <b></td>
                        <td>
                            <?php
                            $path = "/costsheets/" . md5($this->item->id . "mount_common") . ".pdf"; if (file_exists($_SERVER['DOCUMENT_ROOT'] . $path)) { ?>
                                <a href="<?php echo $path; ?>" class="btn btn-secondary" target="_blank">Посмотреть</a>
                            <?php } else { ?>
                                -
                            <?php }
                            $pdf_names[] = array("name" => "Подробная смета", "filename" => md5($this->item->id . "mount_common") . ".pdf", "id" => $this->item->id);
                            $json2 = json_encode($pdf_names); ?>
                        </td>
                        <td></td>
                    </tr>
                <?php }?>
            </table>


        </div>
        <?php foreach ($calculations as $k => $calculation) { ?>
            <?php $mounters = json_decode($calculation->mounting_sum); ?>
            <?php if(!empty($calculation->n2)) $filename = "/calculation_images/".md5("calculation_sketch".$calculation->id).".svg";
           ?>

            <div class="tab-pane" id="calculation<?php echo $calculation->id; ?>" role="tabpanel">
                <h3><?php echo $calculation->calculation_title; ?></h3>

                <a class="btn btn-primary" onclick='send_and_redirect(<?php echo $calculation->id; ?>)'
                   href='javascript:'>Изменить
                    расчет</a>
                <?php if (!empty($filename)):?>
                    <div class="sketch_image_block">
                        <h3 class="section_header">
                            Чертеж <i class="fa fa-sort-desc" aria-hidden="true"></i>
                        </h3>
                        <div class="section_content">
                            <img class="sketch_image" src="<?php echo $filename.'?t='.time(); ?>" style="width:80vw;"/>
                        </div>
                    </div>
                <?php endif; ?>
                <div class="row-fluid">
                    <div class="span6">
                        <?php 
                            if (!empty($calculation->n3)){
                            $canvas = $canvas_model->getFilteredItemsCanvas("`a`.`id` = $calculation->n3");
                        ?>
                            <h4>Материал</h4>
                            <table class="table_info2">
                                <tr>
                                    <td>Тип фактуры:</td>
                                    <td><?php echo $canvas[0]->texture_title; ?></td>
                                </tr>
                                <tr>
                                    <td>Производитель, ширина:</td>
                                    <td><?php echo $canvas[0]->name.' '.$canvas[0]->width; ?></td>
                                </tr>
                                <?php
                                    if (!empty($canvas[0]->color_id)) {
                                ?>
                                    <tr>
                                        <td>Цвет:</td>
                                        <td>
                                            <?php echo $canvas[0]->color_title; ?>
                                            <img src="/<?php echo $canvas[0]->color_file; ?>" alt=""/>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </table>
                            <h4>Размеры помещения</h4>
                            <div>
                                Площадь, м<sup>2</sup>: <?php echo $calculation->n4; ?>
                            </div>
                            <div>
                                Периметр, м: <?php echo $calculation->n5; ?>
                            </div>
                            <?php if ($calculation->n6 > 0) {?>
                                <div>
                                    <h4> Вставка</h4>
                                </div>
                                <?php if ($calculation->n6 == 314) {?>
                                    <div> Белая </div>
                                <?php } else  { ?>
                                    <?php $color_1 = $model_components->getColorId($calculation->n6); ?>
                                    <div>
                                        Цветная : <?php echo $color_1[0]->title; ?> <img style='width: 50px; height: 30px;' src="/<?php echo $color_1[0]->file; ?>"
                                                                                         alt=""/>
                                    </div>
                                <?php }?>
                            <?php } } ?>
                        <?php if ($calculation->n16) { ?>
                            <div>
                                Скрытый карниз: <?php echo $calculation->n16; ?>
                            </div>
                        <?php } ?>

                        <?php if ($calculation->n12) { ?>
                            <h4>Установка люстры</h4>
                            <?php echo $calculation->n12; ?> шт.
                        <?php } ?>

                        <?php if ($calculation->n13) { ?>
                            <h4>Установка светильников</h4>
                            <?php foreach ($calculation->n13 as $key => $n13_item) {
                                echo "<b>Количество:</b> " . $n13_item->n13_count . " шт - <b>Тип:</b>  " . $n13_item->type_title . " - <b>Размер:</b> " . $n13_item->component_title . "<br>";
                                ?>
                            <?php }
                        } ?>

                        <?php if ($calculation->n14) { ?>
                            <h4>Обвод трубы</h4>
                            <?php foreach ($calculation->n14 as $key => $n14_item) {
                                echo "<b>Количество:</b> " . $n14_item->n14_count . " шт  -  <b>Диаметр:</b>  " . $n14_item->component_title . "<br>";
                                ?>
                            <?php }
                        } ?>

                        <?php if ($calculation->n15) { ?>
                            <h4>Шторный карниз Гильдии мастеров</h4>
                            <?php foreach ($calculation->n15 as $key => $n15_item) {
                                echo "<b>Количество:</b> " . $n15_item->n15_count . " шт - <b>Тип:</b>   " . $n15_item->type_title . " <b>Длина:</b> " . $n15_item->component_title . "<br>";
                                ?>
                            <?php }
                        } ?>
                        <?php if ($calculation->n27> 0) { ?>
                            <h4>Шторный карниз</h4>
                            <?php if ($calculation->n16) echo "Скрытый карниз"; ?>
                            <?php if (!$calculation->n16) echo "Обычный карниз"; ?>
                            <?php echo $calculation->n27; ?> м.
                        <?php } ?>

                        <?php if ($calculation->n26) { ?>
                            <h4>Светильники Эcola</h4>
                            <?php foreach ($calculation->n26 as $key => $n26_item) {
                                echo "<b>Количество:</b> " . $n26_item->n26_count . " шт - <b>Тип:</b>  " . $n26_item->component_title_illuminator . " -  <b>Лампа:</b> " . $n26_item->component_title_lamp . "<br>";
                                ?>
                            <?php }
                        } ?>

                        <?php if ($calculation->n22) { ?>
                            <h4>Вентиляция</h4>
                            <?php foreach ($calculation->n22 as $key => $n22_item) {
                                echo "<b>Количество:</b> " . $n22_item->n22_count . " шт - <b>Тип:</b>   " . $n22_item->type_title . " - <b>Размер:</b> " . $n22_item->component_title . "<br>";
                                ?>
                            <?php }
                        } ?>

                        <?php if ($calculation->n23) { ?>
                            <h4>Диффузор</h4>
                            <?php foreach ($calculation->n23 as $key => $n23_item) {
                                echo "<b>Количество:</b> " . $n23_item->n23_count . " шт - <b>Размер:</b>  " . $n23_item->component_title . "<br>";
                                ?>
                            <?php }
                        } ?>

                        <?php if ($calculation->n29) { ?>
                            <h4>Переход уровня</h4>
                            <?php foreach ($calculation->n29 as $key => $n29_item) {
                                echo "<b>Количество:</b> " . $n29_item->n29_count . " м - <b>Тип:</b>  " . $n29_item->type_title . " <br>";
                                ?>
                            <?php }
                        } ?>
                        <h4>Прочее</h4>
                        <?php if ($calculation->n9> 0) { ?>
                            <div>
                                Углы, шт.: <?php echo $calculation->n9; ?>
                            </div>
                        <?php } ?>
                        <?php if ($calculation->n10> 0) { ?>
                            <div>
                                Криволинейный вырез, м: <?php echo $calculation->n10; ?>
                            </div>
                        <?php } ?>
                        <?php if ($calculation->n11> 0) { ?>
                            <div>
                                Внутренний вырез, м: <?php echo $calculation->n11; ?>
                            </div>
                        <?php } ?>
                        <?php if ($calculation->n7> 0) { ?>
                            <div>
                                Крепление в плитку, м: <?php echo $calculation->n7; ?>
                            </div>
                        <?php } ?>
                        <?php if ($calculation->n8> 0) { ?>
                            <div>
                                Крепление в керамогранит, м: <?php echo $calculation->n8; ?>
                            </div>
                        <?php } ?>
                        <?php if ($calculation->n17> 0) { ?>
                            <div>
                                Закладная брусом, м: <?php echo $calculation->n17; ?>
                            </div>
                        <?php } ?>
                        <?php if ($calculation->n19> 0) { ?>
                            <div>
                                Провод, м: <?php echo $calculation->n19; ?>
                            </div>
                        <?php } ?>
                        <?php if ($calculation->n20> 0) { ?>
                            <div>
                                Разделитель, м: <?php echo $calculation->n20; ?>
                            </div>
                        <?php } ?>
                        <?php if ($calculation->n21> 0) { ?>
                            <div>
                                Пожарная сигнализация, м: <?php echo $calculation->n21; ?>
                            </div>
                        <?php } ?>

                        <?php if ($calculation->dop_krepezh> 0) { ?>
                            <div>
                                Дополнительный крепеж: <?php echo $calculation->dop_krepezh; ?>
                            </div>
                        <?php } ?>

                        <?php if ($calculation->n24> 0) { ?>
                            <div>
                                Сложность доступа к месту монтажа, м: <?php echo $calculation->n24; ?>
                            </div>
                        <?php } ?>

                        <?php if ($calculation->n30> 0) { ?>
                            <div>
                                Парящий потолок, м: <?php echo $calculation->n30; ?>
                            </div>
                        <?php } ?>
                        <?php if ($calculation->n32> 0) { ?>
                            <div>
                                Слив воды, кол-во комнат: <?php echo $calculation->n32; ?>
                            </div>
                        <?php } ?>
                        <?php $extra_mounting = (array) json_decode($calculation->extra_mounting);?>
                        <?php if (!empty($extra_mounting) ) { ?>
                            <div>
                                <h4>Дополнительные работы</h4>
                                <?php foreach($extra_mounting as $dop) {
                                    echo "<b>Название:</b> " . $dop->title .  "<br>";
                                }?>
                            </div>
                        <?php } ?>
                    </div>
                    <button class="btn  btn-danger"  id="delete" style="margin:10px;" type="button" onclick="submit_form(this);"> Удалить потолок </button>
                    <input id="idCalcDeleteSelect" value="<?=$calculation->id;?>" type="hidden" disabled>
                    <div class="span6">

                    </div>
                </div>
            </div>
        <?php } ?>
        <?php if ($this->item->project_verdict == 0) { ?>
            <table>
                <tr>
                    <td>
                        <a class="btn  btn-primary" id="rec_to_measurement">
                            Записать на замер
                        </a>
                    </td>
                    <td>
                        <a class="btn  btn-danger" id="refuse_project">
                            Отказ от замера
                        </a>
                    </td>
                    <td>
                        <a class="btn  btn-primary" id="refuse_partnership">
                            Отказ от сотрудничества с ГМ
                        </a>
                    </td>
                </tr>
                <tr>
                    <td colspan=3>
                        <div id="call" class="call" style="display:none;">
                            <label for="call">Добавить звонок</label>
                            <br>
                            <input name="call_date" id="call_date" type="datetime-local" placeholder="Дата звонка">
                            <input name="call_comment" id="call_comment" placeholder="Введите примечание">
                            <button class="btn btn-primary" id="add_call_and_submit" type="button"><i
                                        class="fa fa-floppy-o" aria-hidden="true"></i></button>
                        </div>
                    <td>
                </tr>
            </table>
        <?php } ?>

    </div>
    <div id="modal-window-container-tar">
        <button id="close-tar" type="button"><i class="fa fa-times fa-times-tar" aria-hidden="true"></i></button>
        <div id="modal-window-choose-tar">
            <p id="date-modal"></p>
            <p><strong>Выберите время замера:</strong></p>
            <p>
                <table id="projects_gaugers"></table>
            </p>
        </div>
    </div>
    <input name="idCalcDelete" id="idCalcDelete" value="<?=$calculation->id;?>" type="hidden">
    </form>
    </div>
    <div id="modal_window_container" class="modal_window_container">
        <button type="button" id="close" class="close_btn"><i class="fa fa-times fa-times-tar" aria-hidden="true"></i>
        </button>
        <div id="modal_window_del" class="modal_window">
            <h6 style="margin-top:10px">Вы действительно хотите удалить?</h6>
            <p>
                <button type="button" id="ok" class="btn btn-primary">Да</button>
                <button type="button" id="cancel" onclick="click_cancel();" class="btn btn-primary">Отмена</button>
            </p>
        </div>
    </div>
<?php
    else:
        echo JText::_('COM_GM_CEILING_ITEM_NOT_LOADED');
    endif;
?>

<script language="JavaScript">

    var $ = jQuery;
    var min_project_sum = <?php echo  $min_project_sum;?>;
    var min_components_sum = <?php echo $min_components_sum;?>;

    jQuery(document).mouseup(function (e){ // событие клика по веб-документу
        var div = jQuery("#modal_window_del"); // тут указываем ID элемента
        if (!div.is(e.target) // если клик был не по нашему блоку
            && div.has(e.target).length === 0) { // и не по его дочерним элементам
            jQuery("#close").hide();
            jQuery("#modal_window_container").hide();
            jQuery("#modal_window_del").hide();
        }
        var div1 = jQuery("#modal-window-call-tar");
        if (!div1.is(e.target)
            && div1.has(e.target).length === 0) {
            jQuery("#close-tar").hide();
            jQuery("#modal-window-container").hide();
            jQuery("#modal-window-call-tar").hide();
        }
    });

    // листание календаря
    month_old = 0;
    year_old = 0;
    jQuery("#calendar-container").on("click", "#button-next", function () {
        month = <?php echo $month; ?>;
        year = <?php echo $year; ?>;
        if (month_old != 0) {
            month = month_old;
            year = year_old;
        }
        if (month == 12) {
            month = 1;
            year++;
        } else {
            month++;
        }
        month_old = month;
        year_old = year;
        update_calendar(month, year);
    });
    jQuery("#calendar-container").on("click", "#button-prev", function () {
        month = <?php echo $month; ?>;
        year = <?php echo $year; ?>;
        if (month_old != 0) {
            month = month_old;
            year = year_old;
        }
        if (month == 1) {
            month = 12;
            year--;
        } else {
            month--;
        }
        month_old = month;
        year_old = year;
        update_calendar(month, year);
    });
    function update_calendar(month, year) {
        jQuery.ajax({
            type: 'POST',
            url: "index.php?option=com_gm_ceiling&task=UpdateCalendarTar",
            data: {
                id: <?php echo $userId; ?>,
                id_dealer: <?php echo $user->dealer_id; ?>,
                flag: 3,
                month: month,
                year: year,
            },
            success: function (msg) {
                jQuery("#calendar-container").empty();
                msg += '<div class="btn-small-l"><button id="button-prev" class="button-prev-small" type="button" class="btn btn-primary"><i class="fa fa-arrow-left" aria-hidden="true"></i></button></div><div class="btn-small-r"><button id="button-next" class="button-next-small" type="button" class="btn btn-primary"><i class="fa fa-arrow-right" aria-hidden="true"></i></button></div>';
                jQuery("#calendar-container").append(msg);
                Today(day, NowMonth, NowYear);
                var datesession = jQuery("#jform_project_new_calc_date").val();
                if (datesession != undefined) {
                    jQuery("#current-monthD"+datesession.substr(8, 2)+"DM"+datesession.substr(5, 2)+"MY"+datesession.substr(0, 4)+"YI"+<?php echo $userId; ?>+"IC0C").addClass("class", "change");
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
    //-----------------------------------------------------------------

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
    //-------------------------------------------------------------------

    // функция подсвета сегоднешней даты
    var Today = function (day, month, year) {
        month++;
        jQuery("#current-monthD"+day+"DM"+month+"MY"+year+"YI"+<?php echo $userId; ?>+"IC0C").addClass("today");
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

    jQuery(document).ready(function() {
        if (jQuery("#selected_advt"))
        {
            jQuery("#selected_advt").val(jQuery("#advt_choose").val());
        }

        $("#modal_window_container #ok").click(function() { click_ok(this); });
        
        //trans();

        window.time = undefined;
        window.gauger = undefined;

        // открытие модального окна с календаря и получение даты и вывода свободных замерщиков
        jQuery("#calendar-container").on("click", ".current-month, .not-full-day, .change", function() {
            window.idDay = jQuery(this).attr("id");
            reg1 = "D(.*)D";
            reg2 = "M(.*)M";
            reg3 = "Y(.*)Y";
            if (idDay.match(reg1)[1].length == 1) {
                d = "0"+idDay.match(reg1)[1];
            } else {
                d = idDay.match(reg1)[1];
            }
            if (idDay.match(reg2)[1].length == 1) {
                m = "0"+idDay.match(reg2)[1];
            } else {
                m = idDay.match(reg2)[1];
            }
            window.date = idDay.match(reg3)[1]+"-"+m+"-"+d;
            jQuery("#modal-window-container-tar").show();
			jQuery("#modal-window-choose-tar").show("slow");
            jQuery("#close-tar").show();
            jQuery.ajax({
                type: 'POST',
                url: "/index.php?option=com_gm_ceiling&task=calculations.GetBusyGauger",
                data: {
                    date: date,
                    dealer: <?php echo $user->dealer_id; ?>,
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
                                    var timesession = jQuery("#jform_new_project_calculation_daypart").val();
                                    var gaugersession = jQuery("#jform_project_gauger").val();
                                    if (elementProject.project_calculator == gaugersession && elementProject.project_calculation_date.substr(11) == timesession) {
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
            if (date == datesession.substr(0, 10)) {
                var timesession = jQuery("#jform_new_project_calculation_daypart").val();
                var gaugersession = jQuery("#jform_project_gauger").val();
                setTimeout(function() { 
                    var times = jQuery("input[name='choose_time_gauger']");
                    if (timesession != undefined) {
                        times.each(function(element) {
                            if (timesession == jQuery(this).val() && gaugersession == jQuery(this).closest('tr').find("input[name='gauger']").val()) {
                                jQuery(this).prop("checked", true);
                            }
                        });
                    }
                }, 200);
            } else if (time != undefined) {
                setTimeout(function() { 
                    var times = jQuery("input[name='choose_time_gauger']");
                    times.each(function(element) {
                        if (time == jQuery(this).val() && gauger == jQuery(this).closest('tr').find("input[name='gauger']").val()) {
                            jQuery(this).prop("checked", true);
                        }
                    });
                }, 200);
            }
        });
        //--------------------------------------------------------------------------------------------------

        // получение значений из селектов
		jQuery("#projects_gaugers").on("change", "input:radio[name='choose_time_gauger']", function() {
            var times = jQuery("input[name='choose_time_gauger']");
            time = "";
            gauger = "";
            times.each(function(element) {
                if (jQuery(this).prop("checked") == true) {
                    time = jQuery(this).val();
                    gauger = jQuery(this).closest('tr').find("input[name='gauger']").val();
                }
            });
            jQuery("#jform_new_project_calculation_daypart").val(time);
            jQuery("#jform_project_new_calc_date").val(date);
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
        var datesession = jQuery("#jform_project_new_calc_date").val();
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
            jQuery("#current-monthD"+daytocalendar+"DM"+monthtocalendar+"MY"+datesession.substr(0, 4)+"YI"+<?php echo $userId; ?>+"IC0C").addClass("change");
        }
        //-----------------------------------------------------------


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

        if (jQuery("#comments_id").val() == "" && jQuery("#client_id").val() == 1 && <?php echo $phonefrom; ?> != "0") {
            var comment = "Входящий звонок c " +<?php echo $phonefrom;?>;
            var reg_comment = /[\\\<\>\/\'\"\#]/;
            var id_client = <?php echo $this->item->id_client;?>;
            if (reg_comment.test(comment)) {
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
                        text: "Добавлена запись в историю клиента"
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

        }

        var time = <?php if (isset($_SESSION['time'])) {
            echo "\"" . $_SESSION['time'] . "\"";
        } else echo "\"" . $time . "\"";?>;

        var ne = <?php unset($_SESSION['FIO'], $_SESSION['address'],$_SESSION['house'],$_SESSION['bdq'],$_SESSION['apartment'],$_SESSION['porch'],$_SESSION['floor'],$_SESSION['code'], $_SESSION['date'], $_SESSION['time'], $_SESSION['phones'], $_SESSION['manager_comment'], $_SESSION['comments'], $_SESSION['url'], $_SESSION['gauger']); echo 1;?>;
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

        jQuery("#jform_project_new_calc_date").on("keyup", function () {
            jQuery("#jform_new_project_calculation_daypart").prop("disabled", false);
        });

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

        jQuery("input[name^='include_calculation']").click(function () {
            var _this = jQuery(this);
            var id = _this.val();
            var estimate = jQuery("#section_estimate_" + id);
            var mount = jQuery("#section_mount_" + id);
            if (jQuery(this).prop("checked")) {
                jQuery(this).closest("tr").removeClass("not-checked");
                estimate.attr("vis", "");
                if (flag1 == 1) estimate.show();
                mount.attr("vis", "");
                if (flag2 == 1) mount.show();
            } else {
                jQuery(this).closest("tr").addClass("not-checked");
                estimate.attr("vis", "hide").hide();
                mount.attr("vis", "hide").hide();
            }
            square_total();
            calculate_total();
            calculate_total1();
            trans();
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
            jQuery("#modal-window-container").show();
            jQuery("#modal-window-call-tar").show("slow");
            jQuery("#close-tar").show();
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
                    jQuery("#close-tar").hide();
                    jQuery("#modal-window-container").hide();
                    jQuery("#modal-window-call-tar").hide();
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
                else {
                    jQuery("#form-client").submit();
                }
            }
        });

        jQuery("#change_discount").click(function () {
            jQuery(".new_discount").toggle();

        });

        jQuery("#update_discount").click(function () {
            console.log(<?php echo $skidka; ?>);
            console.log(jQuery("#jform_new_discount").val());
            var phones = [];
            var s = window.location.href;
            var classname = jQuery("input[name='new_client_contacts[]']");
            Array.from(classname).forEach(function (element) {
                phones.push(element.value);
            });
            jQuery("input[name='isDiscountChange']").val(1);
            if (jQuery("#jform_new_discount").is("valid")) jQuery(".new_discount").hide();
            jQuery.ajax({
                type: 'POST',
                url: "index.php?option=com_gm_ceiling&task=save_data_to_session",
                data: {
                    fio: jQuery("#jform_client_name").val(),
                    address: jQuery("#jform_address").val(),
                    house: jQuery("#jform_house").val(),
                    bdq: jQuery("#jform_bdq").val(),
                    apartment: jQuery("#jform_apartment").val(),
                    porch: jQuery("#jform_porch").val(),
                    floor: jQuery("#jform_floor").val(),
                    code: jQuery("#jform_code").val(),
                    date: jQuery("#jform_project_new_calc_date").val(),
                    time: jQuery("#jform_new_project_calculation_daypart").val(),
                    manager_comment: jQuery("#gmmanager_note").val(),
                    client_name: jQuery("#jform_client_name").val(),
                    phones: phones,
                    comments: jQuery("#comments_id").val(),
                    s: s,
                    gauger: jQuery("#jform_project_gauger").val()
                },
                success: function (data) {
                    jQuery("#form-client").submit();
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

        });

        jQuery("#ok").click(function () {
            var phones = [];
            var s = window.location.href;
            var classname = jQuery("input[name='new_client_contacts[]']");
            Array.from(classname).forEach(function (element) {
                phones.push(element.value);
            });
            jQuery("input[name='data_delete']").val(1);
            jQuery.ajax({
                type: 'POST',
                url: "index.php?option=com_gm_ceiling&task=save_data_to_session",
                data: {
                    fio: jQuery("#jform_client_name").val(),
                    address: jQuery("#jform_address").val(),
                    house: jQuery("#jform_house").val(),
                    bdq: jQuery("#jform_bdq").val(),
                    apartment: jQuery("#jform_apartment").val(),
                    porch: jQuery("#jform_porch").val(),
                    floor: jQuery("#jform_floor").val(),
                    code: jQuery("#jform_code").val(),
                    date: jQuery("#jform_project_new_calc_date").val(),
                    time: jQuery("#jform_new_project_calculation_daypart").val(),
                    manager_comment: jQuery("#gmmanager_note").val(),
                    client_name: jQuery("#jform_client_name").val(),
                    phones: phones,
                    comments: jQuery("#comments_id").val(),
                    s: s,
                    gauger: jQuery("#jform_project_gauger").val()
                },
                success: function (data) {
                    jQuery("#form-client").submit();
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
                            var n = noty({
                                timeout: 2000,
                                theme: 'relax',
                                layout: 'center',
                                maxVisible: 5,
                                type: "success",
                                text: "Звонок сдвинут на 30 минут"
                            });

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

        jQuery("input[name='transport']").click(function () {
            var transport = jQuery("input[name='transport']:checked").val();

            if (transport == '2') {
                jQuery("#transport_dist").show();
                jQuery("#transport_dist_col").hide();
                jQuery("#distance").val('');
                jQuery("#distance_col").val('');
            }
            else if(transport == '1') {
                jQuery("#transport_dist").hide();
                jQuery("#transport_dist_col").show();
                jQuery("#distance_col").val('');
                jQuery("#distance").val('');
            }
            else {
                jQuery("#transport_dist").hide();
                jQuery("#transport_dist_col").hide();
                jQuery("#distance").val('');
                jQuery("#distance_col").val('');
            }
            if(transport == 0){
                trans();
            }
            
        });

        jQuery("[name = click_transport]").click(function () {
            trans();
        });

        if (jQuery("input[name='transport']:checked").val() == '2') {
            jQuery("#transport_dist").show();
        }

        if (jQuery("input[name='transport']:checked").val() == '1') {
            jQuery("#transport_dist_col").show();
        }

    });

    function submit_form(e) {
        jQuery("#modal_window_container, #modal_window_container *").show();
        jQuery('#modal_window_container').addClass("submit");
    }

    function click_ok(e) {
        var modal = $(e).closest("#modal_window_container");
        if (modal.hasClass("submit"))
        {
            var select_tab = $(".tab-pane.active").find("#idCalcDeleteSelect").val();

            $("#idCalcDelete").val(select_tab);
            modal.removeClass("submit");
            jQuery("input[name='data_delete']").val(1);
            document.getElementById("form-client").submit();
        }
    }

    function click_cancel(e) {
        jQuery("#modal_window_container, #modal_window_container *").hide();
    }

    jQuery("#cancel").click(function(){
        jQuery("#close-tar").hide();
        jQuery("#modal-window-container").hide();
        jQuery("#modal-window-call-tar").hide();
    })

    function send_and_redirect(id) {
        var phones = [];
        var s = window.location.href;
        var classname = jQuery("input[name='new_client_contacts[]']");
        Array.from(classname).forEach(function (element) {
            phones.push(element.value);
        });
        jQuery.ajax({
            type: 'POST',
            url: "index.php?option=com_gm_ceiling&task=save_data_to_session",
            data: {
                fio: jQuery("#jform_client_name").val(),
                address: jQuery("#jform_address").val(),
                house: jQuery("#jform_house").val(),
                bdq: jQuery("#jform_bdq").val(),
                apartment: jQuery("#jform_apartment").val(),
                porch: jQuery("#jform_porch").val(),
                floor: jQuery("#jform_floor").val(),
                code: jQuery("#jform_code").val(),
                date: jQuery("#jform_project_new_calc_date").val(),
                time: jQuery("#jform_new_project_calculation_daypart").val(),
                manager_comment: jQuery("#gmmanager_note").val(),
                client_name: jQuery("#jform_client_name").val(),
                phones: phones,
                comments: jQuery("#comments_id").val(),
                s: s,
                gauger: jQuery("#jform_project_gauger").val()
            },
            success: function (data) {
                window.location = "index.php?option=com_gm_ceiling&view=calculationform&type=gmmanager&subtype=calendar&id=" + id;
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

    jQuery("#send_all").click(function () {
        jQuery(".email-all").toggle();
    });

    jQuery("#send_all_to_email1").click(function () {

        var email = jQuery("#all-email1").val();
        var client_id = jQuery("#client_id").val();
        var dop_file = jQuery("#dop_file").serialize();
        var testfilename = <?php echo $json;?>;
        var filenames = [];
        for (var i = 0; i < testfilename.length; i++) {
            var id = testfilename[i].id;
            var el = jQuery("#section_estimate_" + id);
            if (el.attr("vis") != "hide") filenames.push(testfilename[i]);
        }
        var formData = new FormData();
        jQuery.each(jQuery('#dopfile')[0].files, function (i, file) {
            formData.append('dopfile', file)
        });
        formData.append('filenames', JSON.stringify(filenames));
        formData.append('email', email);
        formData.append('type', 0);
        formData.append('client_id', client_id);


        jQuery.ajax({
            url: "index.php?option=com_gm_ceiling&task=send_estimate",
            data: formData, /*{
                filenames: JSON.stringify(filenames),
                email: email,
                type: 0,
                client_id: client_id,
                dop_file : serialize
            },*/
            type: "POST",
            dataType: 'json',
            processData: false,
            contentType: false,
            cache: false,
            success: function (data) {
                var n = noty({
                    theme: 'relax',
                    layout: 'center',
                    maxVisible: 5,
                    type: "success",
                    text: "Сметы отправлены!"
                });

            },
            error: function (data) {
                var n = noty({
                    theme: 'relax',
                    layout: 'center',
                    maxVisible: 5,
                    type: "error",
                    text: "ошибка отправки"
                });
            }
        });
    });

    jQuery("#add_new_dvt").click(function () {
        jQuery("#new_advt_div").toggle();
    })

    jQuery("#save_advt").click(function () {
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
    })

    jQuery("#send_all_to_email2").click(function () {
        var email = jQuery("#all-email2").val();
        var testfilename = <?php echo $json1;?>;
        var filenames = [];
        for (var i = 0; i < testfilename.length; i++) {
            var id = testfilename[i].id;
            var el = jQuery("#section_mount_" + id);
            if (el.attr("vis") != "hide") filenames.push(testfilename[i]);
        }
        var formData = new FormData();
        jQuery.each(jQuery('#dopfile1')[0].files, function (i, file) {
            formData.append('dopfile1', file)
        });
        formData.append('filenames', JSON.stringify(filenames));
        formData.append('email', email);
        formData.append('type', 1);
        //formData.append('client_id', client_id);
        jQuery.ajax({
            url: "index.php?option=com_gm_ceiling&task=send_estimate",
            data: formData,/* {
                filenames: JSON.stringify(filenames),
                email: email,
                type: 1
            },*/
            type: "POST",
            dataType: 'json',
            processData: false,
            contentType: false,
            cache: false,
            success: function (data) {
                var n = noty({
                    theme: 'relax',
                    layout: 'center',
                    maxVisible: 5,
                    type: "success",
                    text: "Наряды на монтаж отправлены!"
                });

            },
            error: function (data) {
                var n = noty({
                    theme: 'relax',
                    layout: 'center',
                    maxVisible: 5,
                    type: "error",
                    text: "ошибка отправки"
                });
            }
        });

    });

    jQuery("#send_all_to_email3").click(function () {
        var email = jQuery("#all-email3").val();
        var id  = jQuery("#project_id").val();
        var client_id = jQuery("#client_id").val();
        var testfilename = <?php echo $json2;?>;
        //        for (var i = 0; i < testfilename.length; i++) {
        //            var id = testfilename[i].id;
        //            var el = jQuery("#section_mount_" + id);
        //            if (el.attr("vis") != "hide") filenames.push(testfilename[i]);
        //        }
        var filenames = [];
        var formData = new FormData();
        jQuery.each(jQuery('#dopfile2')[0].files, function (i, file) {
            formData.append('dopfile2', file)
        });
        formData.append('filenames', JSON.stringify(filenames));
        formData.append('email', email);
        formData.append('id', id);
        formData.append('type', 2);
        formData.append('client_id', client_id);
        jQuery.ajax({
            url: "index.php?option=com_gm_ceiling&task=send_estimate",
            data: formData,
            type: "POST",
            dataType: 'json',
            processData: false,
            contentType: false,
            cache: false,
            success: function (data) {
                var n = noty({
                    theme: 'relax',
                    layout: 'center',
                    maxVisible: 5,
                    type: "success",
                    text: "Общая смета отправлена!"
                });

            },
            error: function (data) {
                var n = noty({
                    theme: 'relax',
                    layout: 'center',
                    maxVisible: 5,
                    type: "error",
                    text: "ошибка отправки"
                });
            }
        });

    });

    jQuery("#jform_project_new_calc_date").change(function () {
        jQuery("#jform_new_project_calculation_daypart").prop("disabled", false);
    });

    function isDate(txtDate) {
        var currVal = txtDate;
        if (currVal == '')
            return false;
        //Declare Regex
        var rxDatePattern = /^(\d{1,2})(\/|.)(\d{1,2})(\/|.)(\d{4})$/;
        var dtArray = currVal.match(rxDatePattern); // is format OK?
        if (dtArray == null)
            return false;

        //Checks for mm/dd/yyyy format.
        dtMonth = dtArray[3];
        dtDay = dtArray[1];
        dtYear = dtArray[5];

        if (dtMonth < 1 || dtMonth > 12)
            return false;
        else if (dtDay < 1 || dtDay > 31)
            return false;
        else if ((dtMonth == 4 || dtMonth == 6 || dtMonth == 9 || dtMonth == 11) && dtDay == 31)
            return false;
        else if (dtMonth == 2) {
            var isleap = (dtYear % 4 == 0 && (dtYear % 100 != 0 || dtYear % 400 == 0));
            if (dtDay > 29 || (dtDay == 29 && !isleap))
                return false;
        }
        return true;
    }

    function trans()
    {
        var id = <?php echo $this->item->id; ?>;
        var calcul = jQuery("input[name='transport']:checked").val();
        var transport = jQuery("input[name='transport']:checked").val();
        var distance = jQuery("#distance").val();
        var distance_col = jQuery("#distance_col").val();
        var distance_col_1 = jQuery("#distance_col_1").val();
        var send_data = [];
        send_data["id"] = id; 
        send_data["transport"] = transport;
        switch(transport){
            case "0" :
                send_data["distance_col"] = 0;
                send_data["distance"] = 0;
                break;
            case "1":
                send_data["distance_col"] = distance_col_1;
                send_data["distance"] = 0;
                break;
            case "2" :
                send_data["distance_col"] = distance_col;
                send_data["distance"] = distance;
                break;
        }
        jQuery.ajax({
            type: 'POST',
            url: "index.php?option=com_gm_ceiling&task=project.update_transport",
            data:{
                id : send_data["id"],
                transport : send_data["transport"],
                distance : send_data["distance"] ,
                distance_col :send_data["distance_col"]
            },
            success: function(data){
                calc_transport(data);
            },
            dataType: "json",
            timeout: 10000,
            error: function(data){
                var n = noty({
                    theme: 'relax',
                    timeout: 2000,
                    layout: 'center',
                    maxVisible: 5,
                    type: "error",
                    text: "Ошибка при попытке рассчитать транспорт. Сервер не отвечает"
                });
            }
        }); 
    }
    function calc_transport(data){
        data = JSON.parse(data);
                var html = "",
                    transport_sum = parseFloat(data);
                var calc_sum = 0, calc_total = 0; canvas = 0;
                jQuery.each(jQuery("[name='include_calculation[]']:checked"), function (i,e) {
                    calc_sum += parseFloat(jQuery("[name='calculation_total_discount["+jQuery(e).val()+"]']").val());
                    calc_total += parseFloat(jQuery("[name='calculation_total["+jQuery(e).val()+"]']").val());
                    canvas += parseFloat(jQuery("[name='canvas["+jQuery(e).val()+"]']").val());
                });

                var sum = Float(calc_sum + transport_sum);
                var sum_total = Float(calc_total + transport_sum);

                if (canvas == 0) {
                    sum = (sum <= min_components_sum)?min_components_sum:sum;
                    sum_total = (sum_total <= min_components_sum)?min_components_sum:sum_total;
                } else {
                    sum = (sum <= min_project_sum)?min_project_sum:sum;
                    sum_total = (sum_total <= min_project_sum)?min_project_sum:sum_total;
                }
                sum = Math.round(sum * 100) / 100;
                sum_total = Math.round(sum_total * 100) / 100;

                jQuery("#transport_sum").text(transport_sum.toFixed(0) + " руб.");
                jQuery("#project_total span.sum").text(sum_total);
                jQuery("#project_total_discount span.sum").text(sum  + " руб.");
                jQuery("#project_sum_transport").val(sum);
                jQuery(" #project_sum").val(sum);

                if(canvas == 0) {
                    jQuery("#project_total span.dop").html((sum_total == min_components_sum)?" * минимальная сумма заказа "+min_components_sum+"р.":"");
                    jQuery("#project_total_discount span.dop").html((sum == min_components_sum)?" * минимальная сумма заказа"+min_components_sum+"р.":"");
                }
                else {
                    jQuery("#project_total span.dop").html((sum_total == min_project_sum)?" * минимальная сумма заказа"+min_project_sum+"р.":"");
                    jQuery("#project_total_discount span.dop").html((sum == min_project_sum)?" * минимальная сумма заказа"+min_project_sum+".":"");
                }
    }

    // @return {number}
    function Float(x, y = 2) {
        return Math.round(parseFloat(""+x) * Math.pow(10,y)) / Math.pow(10,y);
    }

    function calculate_total() {
        var project_total = 0,
            project_total_discount = 0,
            pr_total2 = 0,
            canvas = 0;
            

        jQuery("input[name^='include_calculation']:checked").each(function () {
            var parent = jQuery(this).closest(".include_calculation"),
                calculation_total = parent.find("input[name^='calculation_total']").val(),
                calculation_total_discount = parent.find("input[name^='calculation_total_discount']").val(),
                project_total2 = parent.find("input[name^='calculation_total2']").val(),
                canv = parent.find("input[name^='canvas']").val();

            project_total += parseFloat(calculation_total);
            project_total_discount += parseFloat(calculation_total_discount);
            pr_total2 += parseFloat(project_total2);
            canvas += parseFloat(canv);
        });
        //var canvas = jQuery("#canvas").val();
        
        if(canvas == 0) {
            if(project_total < min_components_sum && pr_total2 !== 0){
                if(min_components_sum>0){
                    project_total = min_components_sum;
                }
            } 
            if(project_total_discount < min_components_sum && pr_total2 !== 0)
            {
                if(min_components_sum>0){
                    project_total_discount = min_components_sum;
                }
            }
            jQuery("#project_total span.sum").text(project_total.toFixed(2));
            if (project_total > min_components_sum) { jQuery("#project_total span.dop").html(" "); }
            if (project_total <= min_components_sum && pr_total2 != 0) { jQuery("#project_total span.dop").html(" * минимальная сумма заказа "+min_components_sum+"р."); }
            if (project_total <= min_components_sum && pr_total2 == 0) { jQuery("#project_total span.dop").html(""); }
            jQuery("#project_total_discount span.sum").text(project_total_discount.toFixed(2));
            if (project_total_discount > min_components_sum) { jQuery("#project_total_discount span.dop").html(" "); }
            if (project_total_discount <= min_components_sum && pr_total2 != 0) { jQuery("#project_total_discount span.dop").html(" * минимальная сумма заказа "+min_components_sum+"р."); }
            if (project_total_discount <= min_components_sum && pr_total2 == 0) { jQuery("#project_total_discount span.dop").html(""); }
            //jQuery("#project_total_discount").text(project_total_discount.toFixed(2) );
            jQuery("#project_sum").val(project_total_discount);
        }
        else {
            if(project_total < min_project_sum && pr_total2 !== 0){
                if(min_project_sum>0){
                    project_total = min_project_sum;
                }
            }  
            if(project_total_discount < min_project_sum && pr_total2 !== 0){
                if(min_project_sum>0){
                    project_total_discount = min_project_sum;
                }
            }

            jQuery("#project_total span.sum").text(project_total.toFixed(2));
            if (project_total > min_project_sum) { jQuery("#project_total span.dop").html(" "); }
            if (project_total <= min_project_sum && pr_total2 != 0) { jQuery("#project_total span.dop").html(" * минимальная сумма заказа "+min_project_sum+"р."); }
            if (project_total <= min_project_sum && pr_total2 == 0) { jQuery("#project_total span.dop").html(""); }
            jQuery("#project_total_discount span.sum").text(project_total_discount.toFixed(2));
            if (project_total_discount > min_project_sum) { jQuery("#project_total_discount span.dop").html(" "); }
            if (project_total_discount <= min_project_sum && pr_total2 != 0) { jQuery("#project_total_discount span.dop").html(" * минимальная сумма заказа"+min_project_sum+"р."); }
            if (project_total_discount <= min_project_sum && pr_total2 == 0) { jQuery("#project_total_discount span.dop").html(""); }
            //jQuery("#project_total_discount").text(project_total_discount.toFixed(2) );
            jQuery("#project_sum").val(project_total_discount);
        }
        
    }

    function square_total() {
        var square = 0,
            perimeter = 0;
        var total_sq = 0, total_p = 0;
        jQuery("input[name^='include_calculation']:checked").each(function () {
            var parent = jQuery(this).closest(".include_calculation"),
                square = parent.find("input[name^='total_square']").val(),
                perimeter = parent.find("input[name^='total_perimeter']").val();
            total_sq += parseFloat(square);
            total_p += parseFloat(perimeter);
        });

        jQuery("#total_square").text(total_sq.toFixed(2));
        jQuery("#total_perimeter").text(total_p.toFixed(2));
    }

    function calculate_total1() {
        var project_total1 = 0,
            project_total2 = 0,
            project_total3 = 0,
            pr_total1 = 0,
            pr_total2 = 0,
            pr_total3 = 0;

        jQuery("input[name^='include_calculation']:checked").each(function () {
            var parent = jQuery(this).closest(".include_calculation"),
                project_total1 = parent.find("input[name^='calculation_total1']").val(),
                project_total2 = parent.find("input[name^='calculation_total2']").val(),
                project_total3 = parent.find("input[name^='calculation_total3']").val();

            pr_total1 += parseFloat(project_total1);
            pr_total2 += parseFloat(project_total2);
            pr_total3 += parseFloat(project_total3);
        });
        jQuery("#calculation_total1").text(pr_total1.toFixed(0));
        jQuery("#calculation_total2").text(pr_total2.toFixed(0));
        jQuery("#calculation_total3").text(pr_total3.toFixed(0));
    }

    jQuery("#add_calc").click(function () {
        var phones = [];
        var s = window.location.href;
        var classname = jQuery("input[name='new_client_contacts[]']");
        Array.from(classname).forEach(function (element) {
            phones.push(element.value);
        });
        jQuery.ajax({
            type: 'POST',
            url: "index.php?option=com_gm_ceiling&task=save_data_to_session",
            data: {
                fio: jQuery("#jform_client_name").val(),
                address: jQuery("#jform_address").val(),
                house: jQuery("#jform_house").val(),
                bdq: jQuery("#jform_bdq").val(),
                apartment: jQuery("#jform_apartment").val(),
                porch: jQuery("#jform_porch").val(),
                floor: jQuery("#jform_floor").val(),
                code: jQuery("#jform_code").val(),
                date: jQuery("#jform_project_new_calc_date").val(),
                time: jQuery("#jform_new_project_calculation_daypart").val(),
                manager_comment: jQuery("#gmmanager_note").val(),
                client_name: jQuery("#jform_client_name").val(),
                phones: phones,
                comments: jQuery("#comments_id").val(),
                s: s,
                gauger: jQuery("#jform_project_gauger").val()
            },
            success: function (data) {
                window.location = "index.php?option=com_gm_ceiling&view=calculationform&type=gmmanager&subtype=calendar&id=0&project_id=" +<?php echo $this->item->id; ?>;
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
