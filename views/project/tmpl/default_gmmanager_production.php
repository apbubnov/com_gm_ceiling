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
$dop_num = $dop_num_model->getData($userId)->dop_number;
$_SESSION['user_group'] = $user_group;
$_SESSION['dop_num'] = $dop_num;
$project_id = $this->item->id;

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

// календарь
$month = date("n");
$year = date("Y");
$FlagCalendar = [3, $user->dealer_id];
$calendar = Gm_ceilingHelpersGm_ceiling::DrawCalendarTar($userId, $month, $year, $FlagCalendar);
//----------------------------------------------------------------------------------

// все замерщики
$AllGauger = $calculationsModel->FindAllGauger($user->dealer_id, 22);
//----------------------------------------------------------------------------------

$project_card = '';
$phones = [];
if (!empty($_SESSION["project_card_$project_id"]))
{
    $project_card = $_SESSION["project_card_$project_id"];
    $phones = json_decode($project_card)->phones;
}
?>
<script src="https://api-maps.yandex.ru/2.1/?lang=ru_RU" type="text/javascript"></script>
<link rel="stylesheet" href="/components/com_gm_ceiling/views/project/css/style.css" type="text/css" />
<style>
    .center-left {
        width: 100%;
        text-align: center;
        margin-bottom: 15px;
    }
    .calculation_sum {
        width: 100%;
        margin-bottom: 25px;
    }
    .calculation_sum td {
        padding: 0 5px;
    }
    #table1 {
        width: 100%;
        max-width: 300px;
        font-size: 13px;
    }
    #table1 button, #table1 a, #table1 input {
        font-size: 13px;
        max-width: 150px;
    }
    #table1 td, #table1 th {
        padding: 10px 5px;
    }
    .wtf_padding {
        padding: 0;
    }
    .no_yes_padding {
        padding: 0;
    }
    #calendar1, #calendar2 {
        display: inline-block;
        width: 100%;
        padding: 0;
    }
    #container_calendars {
        width: 100%;
    }
    #button-prev, #button-next {
        padding: 0;
    }
    #calcs_total_border {
        display: inline-block;
        width: auto;
        padding: 3px 7px;
        border: 2px solid #414099;
    }
    @media screen and (min-width: 768px) {
        .center-left {
            text-align: left;
        }
        #table1 {
            width: 100%;
            max-width: 3000px;
            font-size: 1em;
        }
        #table1 td, #table1 th {
            padding: 15px;
        }
        #table1 button, #table1 a, #table1 input {
            font-size: 1em;
            width: auto;
            max-width: 200px;
        }
        .wtf_padding {
            padding: 15px;
        }
        .no_yes_padding {
            padding: 15px;
        }
        #calendar1, #calendar2 {
            width: calc(50% - 25px);
        }
        #calendar2 {
            margin-left: 30px;
        }
    }
</style>
<button id = "back_btn" class = "btn btn-primary"><i class="fa fa-arrow-left" aria-hidden="true"></i> Назад</button>

<h2 class="center">Просмотр проекта</h2>
<?php if ($this->item) : ?>
    <?php
        $jinput = JFactory::getApplication()->input;
        $call_id = $jinput->get('call_id', 0, 'INT');
        $client_model = Gm_ceilingHelpersGm_ceiling::getModel('client_phones');
        $cl_phones = $client_model->getItemsByClientId($this->item->id_client);
        $date_time = $this->item->project_calculation_date;
        $date_arr = date_parse($date_time);
        $date = $date_arr['year'] . '-' . $date_arr['month'] . '-' . $date_arr['day'];
        $time = $date_arr['hour'] . ':00';

        //обновляем менеджера для клиента
        $model_client = Gm_ceilingHelpersGm_ceiling::getModel('client');
        if($this->item->manager_id==1||empty($model_client->getClientById($this->item->id_client)->manager_id)){
            $model_client->updateClientManager($this->item->id_client, $userId);
        }
        $projects_model = Gm_ceilingHelpersGm_ceiling::getModel('projects');
        $projects_model->updateManagerId($userId, $this->item->id_client);
        $request_model = Gm_ceilingHelpersGm_ceiling::getModel('requestfrompromo');
        $request_model->delete($this->item->id_client);
        $client_sex  = $model_client->getClientById($this->item->id_client)->sex;
        if($this->item->id_client!=1){
            $dop_contacts = Gm_ceilingHelpersGm_ceiling::getModel('Clients_dop_contacts');
            $email = $dop_contacts->getEmailByClientID($this->item->id_client);
        }
        $client_dealer = JFactory::getUser($model_client->getClientById($this->item->id_client)->dealer_id);
    ?>
    <h5 class="center">
       Дилер/клиент дилера
    </h5>
    <button id = "show_cl_block" class="btn btn-primary" type = "button">Раскрыть блок информации о клиенте</button>
    <div class="container" id = "cl_block" style="display:none;">
        <div class="row">
            <div class="item_fields">
                <h4>Информация по проекту № <?php echo $this->item->id ?></h4>
                <form id="form-client"
                      action="/index.php?option=com_gm_ceiling&task=project.run_in_production&type=gmmanager&subtype=calendar"
                      method="post" class="form-validate form-horizontal" enctype="multipart/form-data">
                    <input name="project_id" id = "project_id"  value="<?php echo $this->item->id; ?>" type="hidden">
                    <input name="client_id" id="client_id" value="<?php echo $this->item->id_client; ?>" type="hidden">
                    <input name="comments_id" id="comments_id" type="hidden">
                    <input name="status" id="project_status" value="" type="hidden">
                    <input name="call_id" value="<?php echo $call_id; ?>" type="hidden">
                    <input name="type" value="gmmanager" type="hidden">
                    <input name="subtype" value="calendar" type="hidden">
                    <input name="data_change" id = "data_change" value="0" type="hidden">
                    <input name="data_delete" value="0" type="hidden">
                    <input id="project_sum" name="project_sum" value="<?php echo $project_total_discount ?>" type="hidden">
                    <input id="project_sum_transport" name="project_sum_transport" value="<?php echo $project_total_discount_transport ?>" type="hidden">
                    <input id = "emails" name = "emails" value = "" type = "hidden"> 
                    <div class="row">
                        <div class="col-xs-6 col-sm-6 col-md-6 col-lg-6">
                            <table class="table">
                                <tr>
                                    <th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_PROJECT_CLIENT_ID'); ?></th>
                                    <td><input name="new_client_name"
                                               class="<?php if ($this->item->id_client != "1") echo "inputactive"; else echo "inputactive"; ?>"
                                               id="jform_client_name" value="<?php echo $this->item->client_id; ?>"
                                               placeholder="ФИО клиента" type="text"></td>
                                    <?php if($this->item->id_client == "1"){?>
                                        <td>
                                            <button id="find_old_client" type="button" class="btn btn-primary"><i
                                                        class="fa fa-search" aria-hidden="true"></i></button>
                                        </td>
                                    <?php  }?>
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
                                <tr id="search" style="display : none;">
                                    <th>
                                        Выберите клиента из списка:
                                    </th>
                                    <td>
                                        <select id="found_clients" class="inputactive">
                                        </select>
                                    </td>
                                </tr>
                                <?php  $client_model = Gm_ceilingHelpersGm_ceiling::getModel('client');  
                                 $birthday = $client_model->getClientBirthday($this->item->id_client); ?>
                                <tr>
                                    <th>Дата рождения</th>
                                    <td><input name="new_birthday" id="jform_birthday" class="inputactive"
                                                value="<?php if ($birthday->birthday != 0000-00-00)  echo $birthday->birthday ;?>" placeholder="Дата рождения" type="date"></td>
                                    <td><button type="button" class = "btn btn-primary" id = "add_birthday">Ок</button></td>
                                </tr>
                                <tr>
                                    <th><?php echo JText::_('COM_GM_CEILING_CLIENTS_CLIENT_CONTACTS'); ?>
                                        <button type="button" class="btn btn-primary" id="add_phone"><i
                                                    class="fa fa-plus-square" aria-hidden="true"></i></button>
                                    </th>
                                    <td>
                                        <?php if($this->item->id_client == 1){?>
                                            <input name="new_client_contacts[]" id="jform_client_contacts"
                                               class="inputactive" value="<?php echo $phonefrom;?>" placeholder="Телефон клиента" type="text">
                                        <?php } elseif(count($cl_phones)==1) { ?>
                                            <input name="new_client_contacts[<?php echo  '\''.$cl_phones[0]->phone.'\''?>]" id="jform_client_contacts"
                                               class="inputactive" value="<?php echo $cl_phones[0]->phone; ?>"
                                                type="text">

                                        <?php } elseif(count($cl_phones)>1) {
                                            foreach ($cl_phones as $value) {  ?>
                                         <input name="new_client_contacts[<?php echo '\''.$value->phone.'\''?>]" id="jform_client_contacts"
                                               class="inputactive" value="<?php echo  strval($value->phone); ?>"
                                                type="text">
                                        <?php }
                                            } ?>
                                    </td>
                                    <?php if (count($cl_phones) == 1): ?>
                                        <td>
                                            <button id="make_call" type="button" class="btn btn-primary"><i
                                                        class="fa fa-phone" aria-hidden="true"></i></button>
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
                                            <button id="broke" type="button" class="btn btn-primary">Звонок сорвался,
                                                перенести время
                                            </button>
                                            <div id="call_up" class="call" style="display:none;">
                                                <label for="call">Добавить звонок</label>
                                                <br>
                                                <input name="call_date" id="call_date_up" type="datetime-local" placeholder="Дата звонка">
                                                <input name="call_comment" id="call_comment_up" placeholder="Введите примечание">
                                                <button class="btn btn-primary" id="add_call_and_submit_up" type="button"><i
                                                            class="fa fa-floppy-o" aria-hidden="true"></i></button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                                <?php if (isset($_SESSION['phones']) && count($_SESSION['phones'] > 1)) {
                                    for ($i = 1; $i < count($_SESSION['phones']); $i++) { ?>
                                        <tr class='dop-phone'>
                                            <th></th>
                                            <td>
                                                <input name='new_client_contacts[<?php echo $i; ?>]' id='jform_client_contacts'
                                                       class='inputactive'
                                                       value="<?php echo $_SESSION['phones'][$i]; ?>">
                                            </td>
                                            <td>
                                                <button class='clear_form_group btn btn-danger' type='button'><i
                                                            class='fa fa-trash' aria-hidden='true'></i></button>
                                            </td>
                                        </tr>
                                    <?php }
                                } ?>
                                <?php if(count($email)>0){
                                         foreach ($email as $value) {?>
                                    <tr>
                                        <th>e-mail</th>
                                        <td><input name="email[]" id="email" class="inputhidden"
                                                value="<?php echo $value->contact;?>" placeholder="e-mail"
                                                type="text">
                                        </td>
                                    </tr>
                                    <?php } 
                                 }?>
                                <tr>
                                    <th>Добавить адрес эл.почты</th>
                                    <td><input name="new_email" id="jform_email" class="inputactive"
                                               value="" placeholder="e-mail" type="text"></td>
                                    <td><button type="button" class = "btn btn-primary" id = "add_email">Ок</button></td>
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
                                    <td><input name="new_address" id="jform_address" class="inputactive"
                                               value="<?php if (isset($_SESSION['address'])) {
                                                   echo $_SESSION['address'];
                                               } else echo $street ?>" placeholder="Адрес"
                                               type="text" required="required"></td>
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
                                    <th>Менеджер</th>
                                    <td>
                                        <input name="Manager_name" id="manager_name" class="inputhidden"
                                               value="<?php if (isset($this->item->read_by_manager)&&$this->item->read_by_manager!=1) {
                                                   echo JFactory::getUser($this->item->read_by_manager)->name;
                                               } ?>">
                                    </td>
                                </tr>
                            </div>
                        </table>
                    </div>
                    <div class="col-sm-6 col-md-6 col-lg-6">
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
        </div>
    </div>
    </div>
    <?php include_once('components/com_gm_ceiling/views/project/common_table.php'); ?>
<?php if ($this->item->project_verdict == 0) { ?>
            <table>
                <tr>
                    <td>
                        <a class="btn  btn-primary" id="run_in_production">
                            Запустить в производство
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
            <p><button type="button" id="save-choise-tar" class="btn btn-primary">Ок</button></p>
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
<script type="text/javascript" src="/components/com_gm_ceiling/create_calculation.js"></script>
<script type="text/javascript" src="/components/com_gm_ceiling/views/project/common_table.js"></script>
<script type="text/javascript">
    var project_id = "<?php echo $this->item->id; ?>";
    var $ = jQuery;
    var min_project_sum = <?php echo  $min_project_sum;?>;
    var min_components_sum = <?php echo $min_components_sum;?>;
    var self_data = JSON.parse('<?php echo $self_calc_data;?>');
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

    jQuery(document).ready(function () {


        jQuery("#show_cl_block").click(function(){
            jQuery("#cl_block").toggle();
        });

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
        }
        console.log(project_card);

        $("#modal_window_container #ok").click(function() { click_ok(this); });
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
        })

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

        if (jQuery("#comments_id").val() == "" && jQuery("#client_id").val() == 1) {
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
        var time = <?php echo "\"" . $time . "\"";?>;

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

        })

        jQuery("[name = 'include_calculation[]']").change(function(){
            let canv_data = (self_data[jQuery(this).val()].canv_data).toFixed(0);
            let comp_data = (self_data[jQuery(this).val()].comp_data).toFixed(0);
            let mount_data = (self_data[jQuery(this).val()].mount_data).toFixed(0);
            let calc_sum = (self_data[jQuery(this).val()].sum).toFixed(0);
            let calc_sum_discount = (self_data[jQuery(this).val()].sum_discount).toFixed(0);
            let n4 = self_data[jQuery(this).val()].square;
            let n5 = self_data[jQuery(this).val()].perimeter;
            let old_canv = jQuery("#calcs_self_canvases_total span.sum").text();
            let old_comp = jQuery("#calcs_self_components_total span.sum").text();
            let old_mount = jQuery("#calcs_self_mount_total span.sum" ).text();
            let old_all = jQuery("#calcs_total_border").text();
            let old_total = jQuery("#project_total span.sum").text();
            let old_total_discount = jQuery("#project_total_discount span.sum").text();
            let old_n4 = jQuery("#total_square span.sum").text();
            let old_n5 = jQuery("#total_perimeter span.sum").text();
            if(jQuery(this).prop("checked") == true){
               jQuery("#calcs_self_canvases_total span.sum").text(parseInt(old_canv) + parseInt(canv_data));
               if(jQuery("input[name='smeta']").val()!=1){
                   jQuery("#calcs_self_components_total span.sum").text(parseInt(old_comp) + parseInt(comp_data));
               }
               jQuery("#calcs_self_mount_total span.sum").text(parseInt(old_mount) + parseInt(mount_data));
               jQuery("#calcs_total_border").text(parseInt(old_all) + parseInt(canv_data) +  parseInt(comp_data) + parseInt(mount_data));
               jQuery("#project_total span.sum").text(parseInt(old_total)+ parseInt(calc_sum));
               jQuery("#project_total_discount span.sum").text(parseInt(old_total_discount)+ parseInt(calc_sum_discount));
               jQuery("#total_square span.sum").text(parseFloat(old_n4) + parseFloat(n4));
               jQuery("#total_perimeter span.sum").text(parseFloat(old_n5) + parseFloat(n5));
              
            }
            else{
                jQuery("#calcs_self_canvases_total span.sum").text(old_canv-canv_data);
                if(jQuery("input[name='smeta']").val()!=1){
                    jQuery("#calcs_self_components_total span.sum").text(old_comp-comp_data);
                }
                jQuery("#calcs_self_mount_total span.sum").text(old_mount-mount_data);
                jQuery("#calcs_total_border").text(old_all - canv_data - comp_data - mount_data);
                jQuery("#project_total span.sum").text(old_total - calc_sum);
                jQuery("#project_total_discount span.sum").text(old_total_discount - calc_sum_discount);
                jQuery("#total_square span.sum").text((old_n4 - n4).toFixed(2));
                jQuery("#total_perimeter span.sum").text((old_n5 - n5).toFixed(2));
                let more_one = check_selected();
                if(!more_one){
                    jQuery("#project_total_discount span.sum").text(jQuery("#transport_sum span.sum").text());
                }
                
            }
            
            jQuery("#calcs_self_components_total span.sum").data('oldval',jQuery("#calcs_self_components_total span.sum").text());
            check_min_sum(jQuery("#calcs_self_canvases_total span.sum").text());
        });
      
        function check_min_sum(canv_sum){
            let min_sum = 0;
            if(canv_sum == 0) {
                if(min_components_sum>0){
                    min_sum = min_components_sum;
                }
            }
            else{
                if(min_project_sum>0){
                    min_sum = min_project_sum;
                }
            }            
            let project_total = jQuery("#project_total span.sum").text();
            if(jQuery("#project_total_discount span.dop").length == 0){
                jQuery("#project_total_discount").append('<span class = \"dop\" style = \"font-size: 9px\";></span>');
            }
            if(project_total < min_sum){
                jQuery("#project_total_discount span.dop").html(` * минимальная сумма заказа ${min_sum} р.`);
                jQuery("#project_total_discount span.sum").text(min_sum);

            }
            else{
                jQuery("#project_total_discount span.dop").html(" ");
            }
            jQuery("#project_sum").val(jQuery("#project_total_discount span.sum").text());
        }
        function check_selected(){
            let result = false;
            jQuery("[name = 'include_calculation[]']").each(function(){
                if(jQuery(this).prop("checked") == true ){
                    result = true;
                }
            });
            return result;
        }

        jQuery("#show_window").click(function(){
            jQuery("#modal-window-container").show();
            jQuery("#modal-window-call-tar").show("slow");
            jQuery("#close-tar").show();
        })
       
        jQuery("#client_order").click(function () {
            jQuery("input[name='project_verdict']").val(1);
            jQuery("#project_sum").val(<?php echo $project_total_discount?>);
        });

        jQuery("#run_in_production").click(function () {
            jQuery("#project_status").val(5);
            jQuery("#data_change").val(1);
            jQuery("#form-client").submit();
        });
        jQuery("#change_discount").click(function () {
            jQuery(".new_discount").toggle();

        });

        jQuery("#update_discount").click(function () {
            var phones = [];
            var s = window.location.href;
            var classname = jQuery("input[name='new_client_contacts[]']");
            Array.from(classname).forEach(function (element) {
                phones.push(element.value);
            });
            jQuery("input[name='isDiscountChange']").val(1);
            if (jQuery("#jform_new_discount").is("valid")) jQuery(".new_discount").hide();
            save_data_to_session(3);
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

        jQuery("#find_old_client").click(function () {
            jQuery('#found_clients').find('option').remove();
            var opt = document.createElement('option');
            opt.value = 0;
            opt.innerHTML = "Выберите клиента";
            document.getElementById("found_clients").appendChild(opt);
            jQuery.ajax({
                url: "index.php?option=com_gm_ceiling&task=findOldDealers",
                data: {
                    fio: jQuery("#jform_client_name").val()
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
        });

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
                    location.href = '/index.php?option=com_gm_ceiling&view=clientcard&id='+jQuery("#found_clients").val();
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
                jQuery("#distance_col_1").val('');
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
                calculate_transport();
            }
        });
        jQuery("[name = click_transport]").click(function () {
            calculate_transport();
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

    jQuery("#send_all").click(function () {
        jQuery(".email-all").toggle();
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

    jQuery("input[name='transport']").click(function () {
            var transport = jQuery("input[name='transport']:checked").val();
            if (transport == '2') {
                jQuery("#transport_dist").show();
                jQuery("#transport_dist_col").hide();
                jQuery("#distance").val('');
                jQuery("#distance_col_1").val('');
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
                calculate_transport();
            }
        });
        function change_transport(sum){
            let old_transport = jQuery("#transport_sum span.sum").text();
            let new_transport = sum.client_sum;
            let new_self_transport = sum.mounter_sum;
            let old_self_transport = jQuery("#transport_sum span.sum").data('selfval');
            jQuery("#project_sum_transport").val(new_transport);
            jQuery("#transport_sum span.sum").text(new_transport);
            let old_self_mount = jQuery("#calcs_self_mount_total span.sum").text();
            let old_self_total = jQuery("#calcs_total_border").text();
            let old_total = jQuery("#project_total span.sum").text();
            let old_total_discount = jQuery("#project_total_discount span.sum").text();
            jQuery("#project_total span.sum").text(parseInt(old_total) - old_transport + parseInt(new_transport));
            jQuery("#project_total_discount span.sum").text(old_total_discount - old_transport + new_transport);
            jQuery("#calcs_self_mount_total span.sum").text(old_self_mount - old_self_transport + new_self_transport);
            jQuery("#calcs_total_border").text(old_self_total - old_self_transport + new_self_transport);
            jQuery("#transport_sum span.sum").data('selfval',new_self_transport);
            jQuery("#project_sum").val(jQuery("#project_total_discount span.sum").text());
        }
       
        function update_transport(id,transport,distance,distance_col){
            jQuery.ajax({
                type: 'POST',
                url: "index.php?option=com_gm_ceiling&task=project.update_transport",
                data:{
                    id : id,
                    transport : transport,
                    distance : distance,
                    distance_col : distance_col
                },
                success: function(data){
                    change_transport(data);
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

        function calculate_transport(){
            var id = <?php echo $this->item->id; ?>;
            var transport = jQuery("input[name='transport']:checked").val();
            var distance = jQuery("#distance").val();
            var distance_col = jQuery("#distance_col").val();
            var distance_col_1 = jQuery("#distance_col_1").val();
            console.log(distance,distance_col,distance_col_1);
            switch(transport){
                case "0" :
                    update_transport(id,0,0,0);
                    break;
                case "1":
                   
                    update_transport(id,transport,distance,distance_col_1);
                    break;
                case "2" :
                                       
                    update_transport(id,transport,distance,distance_col);
                    break;
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
            if(project_total < 2500 && pr_total2 !== 0)  project_total = 2500;
            if(project_total_discount < 2500 && pr_total2 !== 0)  project_total_discount = 2500;

            jQuery("#project_total span.sum").text(project_total.toFixed(2));
            if (project_total > 2500) { jQuery("#project_total span.dop").html(" "); }
            if (project_total <= 2500 && pr_total2 != 0) { jQuery("#project_total span.dop").html(" * минимальная сумма заказа 2500р."); }
            if (project_total <= 2500 && pr_total2 == 0) { jQuery("#project_total span.dop").html(""); }
            jQuery("#project_total_discount span.sum").text(project_total_discount.toFixed(2));
            if (project_total_discount > 2500) { jQuery("#project_total_discount span.dop").html(" "); }
            if (project_total_discount <= 2500 && pr_total2 != 0) { jQuery("#project_total_discount span.dop").html(" * минимальная сумма заказа 2500р."); }
            if (project_total_discount <= 2500 && pr_total2 == 0) { jQuery("#project_total_discount span.dop").html(""); }
            //jQuery("#project_total_discount").text(project_total_discount.toFixed(2) );
            jQuery("#project_sum").val(project_total_discount);
        }
        else {
            if(project_total < 3500 && pr_total2 !== 0)  project_total = 3500;
            if(project_total_discount < 3500 && pr_total2 !== 0)  project_total_discount = 3500;

            jQuery("#project_total span.sum").text(project_total.toFixed(2));
            if (project_total > 3500) { jQuery("#project_total span.dop").html(" "); }
            if (project_total <= 3500 && pr_total2 != 0) { jQuery("#project_total span.dop").html(" * минимальная сумма заказа 3500р."); }
            if (project_total <= 3500 && pr_total2 == 0) { jQuery("#project_total span.dop").html(""); }
            jQuery("#project_total_discount span.sum").text(project_total_discount.toFixed(2));
            if (project_total_discount > 3500) { jQuery("#project_total_discount span.dop").html(" "); }
            if (project_total_discount <= 3500 && pr_total2 != 0) { jQuery("#project_total_discount span.dop").html(" * минимальная сумма заказа 3500р."); }
            if (project_total_discount <= 3500 && pr_total2 == 0) { jQuery("#project_total_discount span.dop").html(""); }
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
        save_data_to_session(1);
    });
    
    function save_data_to_session(action_type,id=null){
        var phones = [];
            var s = window.location.href;
            var classname = jQuery("input[name='new_client_contacts[]']");
            Array.from(classname).forEach(function (element) {
                phones.push(element.value);
            });
        console.log(phones);
        var data = {
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
                phones: phones,
                comments: jQuery("#comments_id").val(),
                gauger: jQuery("#jform_project_gauger").val(),
                sex: jQuery('[name = "slider-sex"]:checked').val(),
                type : jQuery('[name = "slider-radio"]:checked').val(),
                recoil: jQuery("#recoil_choose").val(),
                advt: jQuery("#advt_choose").val()
            };
        var object = {proj_id : jQuery("#project_id").val(), data:JSON.stringify(data)};
        jQuery.ajax({
            type: 'POST',
            url: "index.php?option=com_gm_ceiling&task=save_data_to_session",
            data: object,
            success: function (data) {
                console.log(data);
                if(action_type == 1){
                    create_calculation(<?php echo $this->item->id; ?>);
                }
                if(action_type == 2){
                   window.location = "index.php?option=com_gm_ceiling&view=calculationform2&type=gmmanager&subtype=production&calc_id=" + id;
                }
                if(action_type == 3){
                    jQuery("#form-client").submit();
                }
                
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
