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
    $model_api_phones = Gm_ceilingHelpersGm_ceiling::getModel('api_phones');
    $repeat_model = Gm_ceilingHelpersGm_ceiling::getModel('repeatrequest');
    $model_client_phones = Gm_ceilingHelpersGm_ceiling::getModel('client_phones');
    $model_client = Gm_ceilingHelpersGm_ceiling::getModel('client');
    $projects_model = Gm_ceilingHelpersGm_ceiling::getModel('projects');
    $request_model = Gm_ceilingHelpersGm_ceiling::getModel('requestfrompromo');
    $dop_contacts = Gm_ceilingHelpersGm_ceiling::getModel('Clients_dop_contacts');
    $recoil_model = Gm_ceilingHelpersGm_ceiling::getModel('recoil');
    $projects_mounts_model = Gm_ceilingHelpersGm_ceiling::getModel('projects_mounts');

    $project_id = $this->item->id;
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
    $address = Gm_ceilingHelpersGm_ceiling::parseProjectInfo($this->item->project_info);
    $json_mount = $this->item->mount_data;
    $this->item->mount_data = json_decode(htmlspecialchars_decode($this->item->mount_data));
    if(!empty($this->item->mount_data)) {
        $mount_types = $projects_mounts_model->get_mount_types();
        foreach ($this->item->mount_data as $value) {
            $value->stage_name = $mount_types[$value->stage];
            if (!array_key_exists($value->mounter, $stages)) {
                $stages[$value->mounter] = array((object)array("stage" => $value->stage, "time" => $value->time));
            } else {
                array_push($stages[$value->mounter], (object)array("stage" => $value->stage, "time" => $value->time));
            }
        }
    }
    $partialFIO = false;
    $fioArr = explode(' ',$this->item->client_id);
    if(count($fioArr) === 3){
        $clientSurname = $fioArr[0];
        $clientName = $fioArr[1];
        $clientPatronymic = $fioArr[2];
    }
    else{
        $partialFIO = true;
    }
?>

<script src="https://api-maps.yandex.ru/2.1/?lang=ru_RU" type="text/javascript"></script>
<link rel="stylesheet" href="/components/com_gm_ceiling/views/project/css/style.css" type="text/css" />
<style>
.act_btn{
    width:210px;
    margin-bottom: 10px;
}
.save_bnt{
    width:250px;
}
.btn_edit{
    position: absolute;
    right:0;
}

</style>
<?= parent::getButtonBack(); ?>

<form id="form-client" action="eindex.php?option=com_gm_ceiling&task=project.recToMeasurement&type=gmmanager&subtype=calendar" method="post"  enctype="multipart/form-data">
    <div>
        <input name="project_id" id = "project_id"  value="<?php echo $project_id; ?>" type="hidden">
        <input name="client_id" id="client_id" value="<?php echo $this->item->id_client; ?>" type="hidden">
        <input name="advt_id" id="advt_id" value="<?php echo $reklama->id; ?>" type="hidden">
        <input name="status" id="project_status" value="" type="hidden">
        <input name="call_id" value="<?php echo $call_id; ?>" type="hidden">
        <input name="type" value="gmmanager" type="hidden">
        <input name="subtype" value="calendar" type="hidden">
        <input name="data_change" value="0" type="hidden">
        <input name="data_delete" value="0" type="hidden">
        <input id="mount" name="mount" type='hidden' value='<?php echo $json_mount ?>'>
        <input name="selected_advt" id="selected_advt" value="<?php echo (!empty($this->item->api_phone_id))? $this->item->api_phone_id: '0' ?>" type="hidden">
        <input name = "recoil" id = "recoil" value = "" type = "hidden">
        <input id="jform_new_project_calculation_daypart" name="new_project_calculation_daypart" value = "<?php if ($this->item->project_calculation_date != null && $this->item->project_calculation_date != "0000-00-00 00:00:00") { echo substr($this->item->project_calculation_date, 11); }?>"class="inputactive" type="hidden">
        <input name = "project_new_calc_date" id = "jform_project_new_calc_date" class ="inputactive" value="<?php if (isset($this->item->project_calculation_date)) { echo $this->item->project_calculation_date;}?>" type="hidden">
        <input name = "project_gauger" id = "jform_project_gauger" class ="inputactive" value="<?php if ($this->item->project_calculator != null) { echo $this->item->project_calculator; } else {echo "0";}?>" type="hidden">
        <input id="project_sum" name="project_sum" value="<?php echo $project_total_discount ?>" type="hidden">
        <input id="project_sum_transport" name="project_sum_transport" value="<?php echo $project_total_discount_transport ?>" type="hidden">
        <input type="text" id="measure_info" class="inputactive" readonly style="display: none;">
        <input type="hidden" id="new_client_name" name="new_client_name" value=''> 
    </div>
    <h2 class="center" style="margin-top: 15px; margin-bottom: 15px;">Проект № <?php echo $this->item->id ?></h2>
    <div class="container">
        <div class="row center">
            <div class="col-xs-12 col-md-12">
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
                                <option value="<?php echo $item['id'] ?>"><?php echo $item['advt_title'] ?></option>
                            <?php } ?>
                        </select>
                        <button type="button" id="add_new_dvt" class="btn btn-primary"><i class="far fa-plus-square"></i></button>
                        <select id="recoil_choose" name ="recoil_choose" style="display:none;">
                            <option value="0">-Выберите откатника-</option>
                            <?php foreach ($all_recoil as $item) { ?>
                                <option value="<?php echo $item->id ?>"><?php echo $item->name ?></option>
                            <?php } ?>
                        </select>
                        <button type="button" id = "show_window" style = "display:none;" class="btn btn-primary"><i class="fa fa-plus-square" aria-hidden="true"></i></button>

                    <?php } ?>
                </h5>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12 col-md-6">
                <div class="row" style="margin-bottom:15px;">
                    <div class="col-xs-4 col-md-4">
                         <b><?php echo JText::_('COM_GM_CEILING_FORM_LBL_PROJECT_CLIENT_ID'); ?></b>
                    </div>
                    <div class="col-xs-8 col-md-8" style="text-align: right;">
                        <button id="find_old_client" type="button" class="btn btn-primary"><i class="fa fa-search" aria-hidden="true"></i> Найти и объеденить</button>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xs-12 col-md-12">
                        <div class="row" style="margin-bottom: 5px">
                            <div class="col-md-3">
                                <label for="new_surname"> Фамилия </label>
                            </div>
                            <div class="col-md-9">
                                <input class="form-control" type="text" id='new_surname' value='<?=$clientSurname;?>'>
                            </div>
                        </div>
                        <div class="row" style="margin-bottom: 5px">
                            <div class="col-md-3">
                                <label for="new_name" > Имя </label>
                            </div>
                            <div class="col-md-9">
                                <input class="form-control" type="text" id='new_name' value='<?=$clientName;?>'>
                            </div>
                        </div>
                        <div class="row" style="margin-bottom: 5px">
                            <div class="col-md-3">
                                <label for="new_patronymic"> Отчество </label>
                            </div>
                            <div class="col-md-9">
                                <input class="form-control" type="text" id='new_patronymic' value='<?=$clientPatronymic;?>'>
                            </div>
                        </div>
                        
                    </div>
                </div>
                <?php if($partialFIO){?>
                    <div class="row">
                        <div class="col-md-12">
                            <input type='text' class="form-control" disabled="true" value='<?=$this->item->client_id;?>'>
                        </div>
                        <div class="col-md-12">
                            <label style="font-size:10pt;color: red;" >ФИО клиента неполное. <br>Пожалуйста введите Фамилию Имя и Отчество Клиента</label>
                        </div>
                    </div>
                <?php }?>
                <div class="row" style="margin-bottom:15px;">
                    <div class="col-xs-4 col-md-4">
                         <b>Пол клиента</b>
                    </div>
                    <div class="col-xs-8 col-md-8">
                        <input id='male' type='radio' class = "radio" name='slider-sex' value='0' <?php if($client_sex == "0") echo "checked";?>>
                            <label  for='male'>Mужской</label>
                            <input id='female' type='radio' class = "radio" name='slider-sex' value='1'  <?php if($client_sex == "1") echo "checked";?> >
                            <label for='female'>Женский</label>
                    </div>
                </div>
                <div class="row" style="margin-bottom:15px;">
                    <div class="col-xs-10 col-md-10">
                        <b><?php echo JText::_('COM_GM_CEILING_CLIENTS_CLIENT_CONTACTS'); ?></b>
                    </div>
                    <div class="col-xs-2 col-md-2" align="right">
                        <button type="button" class="btn btn-primary" id="add_phone"><i class="fa fa-plus-square" aria-hidden="true"></i></button>
                    </div>
                </div>
                <div id = "phones_block">
                    <?php foreach ($cl_phones as $value) {  ?>
                        <div class="row" style="margin-bottom:15px;">
                            <div class="col-xs-10 col-md-10">
                                <input name="new_client_contacts[<?php echo '\''.$value->phone.'\''?>]" id="jform_client_contacts" class="inputactive client_phones" value="<?php echo  strval($value->phone); ?>" type="text">
                            </div>
                            <div class="col-xs-2 col-md-2" align="right">
                                <button id="make_call" type="button" class="btn btn-primary make_call"><i class="fa fa-phone" aria-hidden="true"></i></button>
                            </div>
                         </div>
                    <?php } ?>
                </div>
                <?php if ($call_id != 0): ?>
                    <div class="row center" style="margin-bottom:15px;">
                        <div class="col-xs-12 col-md-12">
                            <button id="broke" type="button" class="btn btn-primary">Перенести дату звонка</button>
                        </div>
                    </div>
                <?php endif; ?>
                 <div class="row" style="margin-bottom:15px;">
                    <div class="col-xs-12 col-md-12">
                        <b>Эл.почта</b>
                    </div>
                </div>
                <div id = "emails_block">
                    <?php if(count($email)>0){
                        foreach ($email as $value) {?>
                            <div class="row" style="margin-bottom:15px;">
                                <div class="col-xs-10 col-md-10 col-lg-10">
                                    <input name="email[<?php echo $value->contact?>]" id="email" class="inputactive" value="<?php echo $value->contact;?>" placeholder="e-mail" type="text">
                                </div>
                                <div class="col-xs-2 col-md-2 col-lg-2" align="right">
                                    <button class="btn btn-danger remove_email" type="button"><i class="fas fa-trash-alt" aria-hidden="true"></i></button>
                                </div>
                            </div>
                        <?php } 
                     }?>
                </div>
                <div class="row" style="margin-bottom:15px;">
                     <div class="col-xs-10 col-md-10 col-lg-10">
                            <input name="new_email" id="new_email" class="inputactive" placeholder=" Новый e-mail" type="text">
                        </div>
                        <div class="col-xs-2 col-md-2" align="right">
                            <button type="button" class="btn btn-primary" id="add_email"><i class="fa fa-plus-square" aria-hidden="true"></i></button>
                        </div>
                </div>
                <div class="row" style="margin-bottom:15px;">
                     <div class="col-xs-12 col-md-12" align="center"><b>Дата и время замера</b></div>
                </div>
                <div class="row" style="margin-bottom:15px;">
                     <div class="col-xs-12 col-md-12">
                        <div id="measures_calendar" align="center"></div>
                    </div>
                </div>
                <?php if(!empty($this->item->mount_data)):?>
                    <div class="row"  style="margin-bottom:15px;">
                       <div class="col-md-12">
                           <b>Монтаж</b>
                       </div>
                    </div>
                    <div class="row"  style="margin-bottom:15px;">
                        <?php foreach ($this->item->mount_data as $value) { ?>
                                <div class="col-md-4">
                                    <?php
                                        $date = date_create($value->time);
                                        echo date_format($date,"d.m.Y H:i:s");
                                    ?>
                                </div>
                                <div class="col-md-4"><?php echo $value->stage_name;?></div>
                                <div class="col-md-4"><?php echo JFactory::getUser($value->mounter)->name;?></div>

                        <?php }?>
                    </div>

                <?php endif;?>
                <div class="row" style="margin-bottom:15px;">
                    <div class="col-xs-4 col-md-4">
                        <b><?php echo JText::_('COM_GM_CEILING_FORM_LBL_PROJECT_PROJECT_INFO'); ?></b>
                    </div>
                    <div class="col-xs-8 col-md-8">
                        <input name="new_address" id="jform_address" class="inputactive" value="<?php echo $address->street ?>" placeholder="Адрес" type="text" >
                    </div>
                </div>
                <div class="row" style="margin-bottom:15px;">
                    <div class="col-xs-4 col-md-4">
                        <b>Дом / Корпус</b>
                    </div>
                    <div class="col-xs-4 col-md-4">
                        <input name="new_house" id="jform_house" value="<?php echo $address->house ?>" class="inputactive" placeholder="Дом"  aria-required="true" type="text">
                    </div>
                    <div class="col-xs-4 col-md-4">
                        <input name="new_bdq" id="jform_bdq"  value="<?php echo $address->bdq ?>" class="inputactive"   placeholder="Корпус" aria-required="true" type="text">
                    </div>
                </div>
                <div class="row" style="margin-bottom:15px;">
                    <div class="col-xs-4 col-md-4">
                        <b>Квартира / Подъезд</b>
                    </div>
                    <div class="col-xs-4 col-md-4">
                        <input name="new_apartment" id="jform_apartment" value="<?php echo $address->apartment ?>" class="inputactive" placeholder="Квартира"  aria-required="true" type="text">
                    </div>
                    <div class="col-xs-4 col-md-4">
                        <input name="new_porch" id="jform_porch"  value="<?php echo $address->porch ?>" class="inputactive"    placeholder="Подъезд"  aria-required="true" type="text">
                    </div>
                </div>
                <div class="row" style="margin-bottom:15px;">
                    <div class="col-xs-4 col-md-4">
                        <b>Этаж / Код домофона</b>
                    </div>
                    <div class="col-xs-4 col-md-4">
                        <input name="new_floor" id="jform_floor"  value="<?php echo $address->floor ?>" class="inputactive"  placeholder="Этаж" aria-required="true" type="text">
                    </div>
                    <div class="col-xs-4 col-md-4">
                        <input name="new_code" id="jform_code"  value="<?php echo $address->code ?>" class="inputactive"   placeholder="Код" aria-required="true" type="text">
                    </div>
                </div>
                <div class="row" style="margin-bottom:15px;">
                    <div class="col-xs-6 col-md-6"><b>Примечание к замеру</b></div>
                    <div class="col-xs-6 col-md-6">
                        <input name="measure_note" id="measure_note" class="inputactive"
                               value="">
                    </div>
                </div>
                <div class="row" style="margin-bottom:15px;">
                    <div class="col-xs-4 col-md-4"><b>Менеджер</b></div>
                    <div class="col-xs-8 col-md-8">
                        <input name="Manager_name" id="manager_name" class="inputhidden"
                            value="<?php if (isset($this->item->read_by_manager)&&$this->item->read_by_manager!=1) {
                            echo JFactory::getUser($this->item->read_by_manager)->name;
                            } ?>" readonly>
                    </div>
                </div>
               <div class="row" style="margin-bottom:15px;">
                    <div class="col-xs-4 col-md-4"><b>Замерщик</b></div>
                    <div class="col-xs-8 col-md-8">
                        <input name="calculator_name" id="calculator_name" class="inputhidden"
                            value="<?php if (isset($this->item->project_calculator)) {
                            echo JFactory::getUser($this->item->project_calculator)->name;
                            }?>" readonly>
                    </div>
                </div>

                <?php if (!in_array($this->item->project_status,VERDICT_STATUSES)) {
                    $skidka = 0;
                    if (!empty($calculation_total)) {
                        $skidka = ($calculation_total - $project_total_1) / $calculation_total * 100;
                    }
                ?>
                <div class="row" style="margin-bottom:15px;">
                    <div class="col-xs-4 col-md-4">
                        <label id="jform_discoint-lbl" for="jform_new_discount"><b>Процент скидки:</b></label>
                    </div>
                    <div class="col-xs-6 col-md-6">
                         <input name="new_discount" id="jform_new_discount" value="" placeholder="Новый % скидки"
                                   min="0" max="<?= round($skidka, 0); ?>" class="inputactive" type="number">
                    </div>
                    <div class="col-xs-2 col-md-2">
                         <button type="button" id="update_discount" class="btn btn-primary">Ок</button>
                    </div>
                </div>
                <?php } ?>
                <?php include_once('components/com_gm_ceiling/views/project/project_notes.php'); ?>
            </div>
            <div class="col-xs-12 col-md-6">
                <div class="comment">
                    <label> <b>История клиента:</b> </label>
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
                <?php if(in_array(16,$user_group)){?>
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
                <?php }?>
                <?php if (in_array($this->item->project_status,VERDICT_STATUSES)) { ?>
                    <div class="row center">
                        <div class="col-md-12">
                            <h4>Перенести дату монтажа</h4>
                            <div id="calendar_mount" align="center"></div>
                        </div>
                    </div>
                    <div class="row center"  style="margin-bottom:15px;">
                        <div class="col-md-12">
                            <button type="button" class="btn btn-primary" id="update_mount">Сохранить изменения</button>
                        </div>
                    </div>
                <?php }?>
            </div>
        </div>
    </div>  
    <?php include_once('components/com_gm_ceiling/views/project/common_table.php'); ?>

    <?php if (!in_array($this->item->project_status,VERDICT_STATUSES)) { ?>
        <div class="container">
            <div class="row">
                <div class="col-xs-12 col-xm-12 col-md-4 col-lg-4">
                    <button class="btn  btn-primary act_btn" id="rec_to_measurement" type ="button">
                        Записать на замер
                    </button>
                </div>
                <div class="col-xs-12 col-xm-12 col-md-4 col-lg-4">
                    <button class="btn  btn-danger act_btn" id="refuse_project" type ="button">
                        Отказ от замера
                    </button>
                </div>
                <div class="col-xs-12 col-xm-12 col-md-4 col-lg-4">
                    <button class="btn  btn-danger act_btn" id="refuse_partnership" type ="button">
                        Отказ от сотрудничества
                    </button>
                </div>
            </div>
            <div class="row" id="call" class="call col" style="display:none;">
                <div class="col-md-6">
                    <div class="row center">
                        <div class="col-md-12">
                            <b>
                                <label for="call">Если нужно добавьте звонок по <span class="for_span"></span></label>
                            </b>
                        </div>
                    </div>
                    <div class="row" style="margin-bottom: 15px;">
                        <div class="col-md-6">
                            <input name="call_date" id="call_date" class="form-control" type="datetime-local" placeholder="Дата звонка">
                        </div>
                        <div class="col-md-6">
                            <input name="call_comment" id="call_comment" class="form-control" placeholder="Введите примечание">
                        </div>
                    </div>
                    <div class="row center" >
                        <div class="col-md-12">
                            <button class="btn btn-primary" id="add_call_and_submit" type="button">
                                <i class="fas fa-save" aria-hidden="true"></i> Записать
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php }?>
    </div>
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
    <div id="mw_mounts_calendar" class="modal_window"></div>
    <div class="modal_window" id="mw_find_client">
        <div class="col-md-4"></div>
        <div class="col-md-4">
            <h4>Поиск для объединения</h4>
            <div class="row center">
                <label>Введите Имя, номер телефона или адрес</label>
                <input class="form-control" id="search_text">
            </div>
            <div class="row" style="text-align: left;">
                <label> Ищем:</label><br>
                <input id='radio_clients' type='radio' class = "radio" name='slider-search' value='clients'>
                <label for='radio_clients'>Клиентов</label><br>
                
                <input id='radio_dealers' type='radio' class = "radio" name='slider-search' value='dealers'>
                <label for='radio_dealers'>Дилеров</label><br>
                    
                <input id='radio_designers' type='radio' class = "radio" name='slider-search' value='designers'>
                <label for='radio_designers'>Отделочников</label><br>
                <div id="search" style="display : none;">
                    <label>
                        <b>Выберите из списка:</b>
                    </label>
                    <br>
                    <select id="found_clients" class="input-gm"></select>
                </div>
            </div>
        </div>
        <div class="col-md-4"></div>
    </div>
    <div class="modal_window" id="mw_call_up">
        <h4>Перенос звонка</h4>
        <label for="call_date_up">Новая дата звонка</label><br>                            
        <input name="call_date_up" id="call_date_up"  class = "act_btn" type="datetime-local" placeholder="Дата звонка"><br>
        <label for="call_comment_up">Комментарий</label><br>
        <input name="call_comment_up" id="call_comment_up" class = "act_btn" placeholder="Введите примечание"><br>
        <br>
        <button class="btn btn-primary act_btn" id="add_call_and_submit_up" type="button"><i class="fas fa-save" aria-hidden="true"></i> Сохранить</button>
    </div>
    <div class="modal_window" id="mw_new_adwt">
        <h4>Добавление нового вида рекламы</h4>
        <div class="row">
            <div class="col-md-3">

            </div>
            <div class="col-md-6">
                <div class="col-md-10">
                    <input id="new_advt_name" class="form-control"  placeholder="Название рекламы">
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-primary" id="save_advt">Ok</button>
                </div>
            </div>
            <div class="col-md-3">

            </div>
        </div>
    </div>
</div>
</form>
<script type="text/javascript" src="/components/com_gm_ceiling/create_calculation.js"></script>
<script type="text/javascript" src="/components/com_gm_ceiling/views/project/common_table.js"></script>

<script type="text/javascript" src="/components/com_gm_ceiling/date_picker/measures_calendar.js"></script>
<script type="text/javascript" src="/components/com_gm_ceiling/date_picker/mounts_calendar.js"></script>
<script type="text/javascript">
    init_measure_calendar('measures_calendar','jform_project_new_calc_date','jform_project_gauger','mw_measures_calendar',['close_mw','mw_container'], 'measure_info');
    init_mount_calendar('calendar_mount','mount','mw_mounts_calendar',['close_mw','mw_container']);
    var project_id = "<?php echo $this->item->id; ?>",
        $ = jQuery,
        min_project_sum = <?php echo  $min_project_sum;?>,
        min_components_sum = <?php echo $min_components_sum;?>,
        self_data = JSON.parse('<?php echo $self_calc_data;?>');
    jQuery(document).mouseup(function (e){ // событие клика по веб-документу
        var div1 = jQuery("#mw_recoil"),
            div2 = jQuery("#mw_measures_calendar"),
            div3 = jQuery("#mw_call_up"),
            div4 = jQuery("#mw_find_client"),
            div5 = jQuery("#mw_mounts_calendar"),
            div6 = jQuery("#mw_new_adwt");
        if (!div1.is(e.target)
            && div1.has(e.target).length === 0
            && !div2.is(e.target)
            && div2.has(e.target).length === 0
            && !div3.is(e.target)
            && div3.has(e.target).length === 0
            && !div4.is(e.target)
            && div4.has(e.target).length === 0
            && !div5.is(e.target)
            && div5.has(e.target).length === 0
            && !div6.is(e.target)
            && div6.has(e.target).length === 0) {
            jQuery("#close_mw").hide();
            jQuery("#mw_container").hide();
            div1.hide();
            div2.hide();
            div3.hide();
            div4.hide();
            div5.hide();
            div6.hide();
        }
    });

    jQuery(document).ready(function() {
        jQuery(".client_phones").each(function(index,element){
            jQuery(element).mask("+7 (999) 999-99-99");
        });
        jQuery(document).on("click", "#add_calc", function(){
            create_calculation(<?php echo $this->item->id; ?>);
        });
        jQuery(".remove_email").click(remove_email);
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
        jQuery('body').on('click','#update_mount',function () {
            jQuery.ajax({
                type: 'POST',
                url: "index.php?option=com_gm_ceiling&task=project.updateMountDate",
                data: {
                    project_id : jQuery("#project_id").val(),
                    mount_data : jQuery("#mount").val()
                },
                success: function(data){
                    location.reload();
                },
                dataType: "json",
                timeout: 10000,
                error: function(data){
                    console.log(data);
                    var n = noty({
                        timeout: 2000,
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "error",
                        text: "Ошибка обновления!"
                    });
                }
            });
        });
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
            if(jQuery("#new_email").val()!=""){
                jQuery.ajax({
                    type: 'POST',
                    url: "index.php?option=com_gm_ceiling&task=addemailtoclient",
                    data: {
                        email:jQuery("#new_email").val(),
                        client_id: jQuery("#client_id").val()
                    },
                    success: function (data) {
                        var html = "<div class=\"row\" style=\"margin-bottom:15px;\">";
                        html += "<div class=\"col-xs-10 col-md-10\">";
                        html += "<input name=\"email["+jQuery("#new_email").val()+"]\" id=\"email\" class=\"inputactive\" value=\""+jQuery("#new_email").val()+"\" type=\"text\">";
                        html +="</div>";
                        html += "<div class=\"col-xs-2 col-md-2\" align=\"right\">";
                        html += "<button class='btn btn-danger remove_email' type='button'><i class='fa fa-trash' aria-hidden='true' type='button'></i></button>";
                        html += "</div>";
                        html += "</div>";
                        jQuery(html).appendTo("#emails_block");
                        //jQuery(".remove_email").click(remove_email(jQuery(this).closest('.row').find('input').val()));
                        jQuery("#emails").val(jQuery("#emails").val()+data+";");
                        var n = noty({
                            timeout: 2000,
                            theme: 'relax',
                            layout: 'center',
                            maxVisible: 5,
                            type: "success",
                            text: "Почта добавлена!"
                        });
                        jQuery("#new_email").val("");
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
        
        function remove_email(){
            var row = jQuery(this).closest('.row'),email = jQuery(this).closest('.row').find('input').val()
            jQuery.ajax({
                type: 'POST',
                url: "index.php?option=com_gm_ceiling&task=client.removeEmail",
                data: {
                    email:email,
                    client_id: jQuery("#client_id").val()
                },
                success: function (data) {
                    row.remove();
                    var n = noty({
                        timeout: 2000,
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "success",
                        text: "Почта удалена!"
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
            if (jQuery("#call").is(':hidden')){
                jQuery("#call").show();

            }
            else{
                if(jQuery("#project_status").val() == 1){
                    jQuery("#call").hide();
                }
            }
            jQuery(".for_span").text('замеру');
            jQuery("#add_call_and_submit").html("<i class=\"fas fa-save\" aria-hidden=\"true\"></i>  Записать");
            jQuery("#project_status").val(1);
        });

        jQuery("#refuse_partnership").click(function () {
            writeClientName();
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
                console.log(jQuery("#form-client"))
                jQuery("#form-client").submit();
            }
        });

        jQuery("#refuse_project").click(function () {
            if (jQuery("#call").is(':hidden')){
                jQuery("#call").show();

            }
            else{
                if(jQuery("#project_status").val() == 2){
                    jQuery("#call").hide();
                }
            }
            jQuery(".for_span").text('отказу');
            jQuery("#add_call_and_submit").html("<i class=\"fas fa-save\" aria-hidden=\"true\"></i> Отказ");
            jQuery("#project_status").val(2);
        });

        jQuery("#accept_changes").click(function () {
            jQuery("input[name='data_change']").val(1);
        });
        function writeClientName(){
            var new_surname = jQuery('#new_surname').val(),
                new_name = jQuery('#new_name').val(),
                new_patronymic = jQuery('#new_patronymic').val(),
                fio = '';
                if(!empty(new_surname)){
                fio += new_surname;
                }
                if(!empty(new_name)){
                    if(!empty(fio)){
                        fio += ' ' + new_name;
                    }
                    else{
                        fio += new_name;
                    }
                }
                if(!empty(new_patronymic)){
                    if(!empty(fio)){
                        fio += ' ' + new_patronymic;
                    }
                    else{
                        fio += new_patronymic;
                    }
                }

            jQuery('#new_client_name').val(fio);
        }
        jQuery("#add_call_and_submit").click(function () {
            writeClientName();
            if (jQuery("#project_status").val() == 1) {
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
                    project_total: jQuery("#project_total span.sum")[0].innerText,
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

        jQuery("#find_old_client").click(function(){
            jQuery("#mw_container").show();
            jQuery("#mw_find_client").show("slow");
            jQuery("#close_mw").show();
        });
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
                    fio: jQuery("#search_text").val(),
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
            jQuery.ajax({
                url: "index.php?option=com_gm_ceiling&task=addPhoneToClient",
                data: {
                    id: jQuery("#found_clients").val(),
                    old_id:jQuery("#client_id").val(),
                    p_id: "<?php echo $this->item->id; ?>"
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

        jQuery(".make_call").click(function () {
            phone = jQuery(this).closest('.row').find('input').val();
            client_id = jQuery("#client_id").val();
            call(phone);
            add_history(client_id, "Исходящий звонок на " + phone);
        });

        jQuery("#broke").click(function(){
            jQuery("#mw_container").show();
            jQuery("#mw_call_up").show("slow");
            jQuery("#close_mw").show();
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
        var html = "<div class=\"row\" style=\"margin-bottom:15px;\">";
        html += "<div class=\"col-xs-10 col-md-10\">";
        html += "<input name='new_client_contacts[]' id='jform_client_contacts' class='inputactive' value=''>";
        html +="</div>";
        html += "<div class=\"col-xs-2 col-md-2\" align=\"right\">";
        html += "<button class='btn btn-danger remove_phone' type='button'><i class='fa fa-trash' aria-hidden='true'></i></button>";
        html += "</div>";
        html += "</div>";
        jQuery(html).appendTo("#phones_block");
        var classname = jQuery("input[name='new_client_contacts[]']");
        classname.mask("+7 (999) 999-99-99");
        jQuery(".remove_phone").click(function () {
            jQuery(this).closest(".row").remove();

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
        jQuery("#mw_container").show();
        jQuery("#mw_new_adwt").show('slow');
        jQuery("#close").show();
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
                jQuery('#mw_container').hide();
                jQuery('#mw_new_adwt').hide();
                jQuery('#close').hide();
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
