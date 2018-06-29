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

    $user = JFactory::getUser();
    $userId = $user->get('id');

    $userId = $user->get('id');
    $userName = $user->get('username');
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

    //----------------------------------------------------------------------------------
    $server_name = $_SERVER['SERVER_NAME'];
?>

<style>
    .calculation_sum {
        width: 100%;
        margin-bottom: 25px;
    }
    .calculation_sum td {
        padding: 0 5px;
    }
</style>

<link rel="stylesheet" href="/components/com_gm_ceiling/views/project/css/style.css" type="text/css" />
<script src="https://api-maps.yandex.ru/2.1/?lang=ru_RU" type="text/javascript"></script>

<?=parent::getButtonBack();?>

<h2 class="center">Просмотр проекта</h2>

<?php if ($this->item) : ?>
<?php    
    $phones = $phones_model->getItemsByClientId($this->item->id_client);
?>
<form id="form-client" action="/index.php?option=com_gm_ceiling&task=project.activate&type=gmcalculator&subtype=calendar" method="post" class="form-validate form-horizontal" enctype="multipart/form-data" >
    <div class="container">
        <div class="row">
            <div class="col-12 item_fields">
                <h4>Информация по проекту № <?php echo $this->item->id ?></h4>
                    <?php if ($this->type === "gmcalculator" && $this->subtype === "calendar") { ?>
                        <?php if ($this->item->project_verdict == 0) { ?>
                            <?php if ($user->dealer_type != 2) { ?>
                                <table>
                                    <tr>
                                        <td>
                                            <a class="btn btn-primary" id="change_data">
                                                <?php if ($this->item->client_id == 1) { echo "Заполнить данные о клиенте"; } else { echo "Изменить данные"; } ?>
                                            </a>
                                        </td>
                                    </tr>
                                </table>
                            <?php } ?>
                        <?php } ?>
                        <div class="project_activation" style="display: none;">
                            <input name="project_id" value="<?php echo $this->item->id; ?>" type="hidden">
                            <input name="client_id" id="client_id" value="<?php echo $this->item->id_client; ?>" type="hidden">
                            <input name="type" value="gmcalculator" type="hidden">
                            <input name="subtype" value="calendar" type="hidden">
                            <input id="project_verdict" name="project_verdict" value="0" type="hidden">
                            <input name="data_change" value="0" type="hidden">
                            <input name="data_delete" value="0" type="hidden">
                            <input id="mounting_date" name="mounting_date" type='hidden'>
                            <input id = "jform_project_mounting_date" name="jform_project_mounting_date" value="<?php echo $this->item->project_mounting_date; ?>" type='hidden'>
                            <input name="project_mounter" id = "project_mounter" value="<?php echo $this->item->project_mounter; ?>" type='hidden'>
                            <input id="project_sum" name="project_sum" value="<?php echo $project_total_discount ?>" type="hidden">
                            <input id="project_sum_transport" name="project_sum_transport" value="<?php echo $project_total_discount_transport ?>" type="hidden">
                            <input name="comments_id" id="comments_id" value="<?php if (isset($_SESSION['comments'])) echo $_SESSION['comments']; ?>" type="hidden">
                            <input id="jform_new_project_calculation_daypart" name="new_project_calculation_daypart" value="" type='hidden'> 
                            <input name = "project_new_calc_date" id = "jform_project_new_calc_date"  value="" type='hidden'>
                            <input id="jform_project_gauger" name="project_gauger" value="" type='hidden'>  
                        </div>
                        <?php if ($user->dealer_type != 2) { ?>
                            <div class="row">
                                <div class="col-12 col-md-6">
                                    <table class="table" >
                                        <tr>
                                            <th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_PROJECT_CLIENT_ID'); ?></th>
                                            <td><a href="http://<?php echo $server_name ?>/index.php?option=com_gm_ceiling&view=clientcard&id=<?=$this->item->id_client;?>"><?php echo $this->item->client_id; ?></a></td>
                                            <td>
                                                <div class="FIO" style="display: none;">
                                                    <input class = "inputactive" name="new_client_name" id="jform_client_name" value="<?php echo $this->item->client_id; ?>" placeholder="Новое ФИО клиента" type="text">
                                                </div>
                                            </td>
                                        </tr>
                                        <?php  
                                            $birthday = $client_model->getClientBirthday($this->item->id_client);
                                        ?>
                                        <tr>
                                            <th>Дата рождения</th>
                                            <td>
                                                <input name="new_birthday" id="jform_birthday" class="inputactivenotsingle" value="<?php if ($birthday->birthday != 0000-00-00)  echo $birthday->birthday ;?>" placeholder="Дата рождения" type="date">
                                                <button type="button" class = "btn btn-primary" id = "add_birthday">Ок</button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?php echo JText::_('COM_GM_CEILING_CLIENTS_CLIENT_CONTACTS'); ?></th>
                                            <td><?foreach ($phones as $contact):?><a href="tel:+<?=$contact->phone;?>"><?=$contact->phone;?></a><br><?endforeach;?></td>
                                        </tr>
                                        <tr>
                                            <th>Почта</th>
                                            <td>
                                                <?php
                                                    $contact_email = $clients_dop_contacts_model->getContact($this->item->id_client);
                                                    foreach ($contact_email AS $contact):?>
                                                        <a href="mailto:<?=$contact->contact;?>"><?=$contact->contact;?></a>
                                                <?endforeach;?>
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
                                            <td><a target="_blank" href="https://yandex.ru/maps/?mode=search&text=<?=$this->item->project_info;?>"><?=$this->item->project_info;?></a></td>
                                            <td>
                                                <div class="Address" style="display: none; position:relative;">
                                                    <label id="jform_address_lbl" for="jform_address">
                                                        Адрес<span class="star">&nbsp;*</span>
                                                    </label>
                                                    <input name="new_address" class="inputactive" id="jform_address" value="<?=$street?>" placeholder="Улица" type="text">
                                                </div>
                                            </td>
                                        </tr>
                                        <tr class="Address" style="display: none;">
                                            <td>Дом</td>
                                            <td>Корпус</td>
                                            <td>
                                                <input name="new_house" id="jform_house" value="<?php if (isset($_SESSION['house'])) {echo $_SESSION['house']; } else echo $house ?>"class="inputactive"  style="width: 50%; margin-bottom: 1em; float: left; margin: 0 5px 0 0;" placeholder="Дом" aria-required="true" type="text">
                                                <input name="new_bdq" id="jform_bdq"  value="<?php if (isset($_SESSION['bdq'])) {echo $_SESSION['bdq']; } else echo $bdq ?>" class="inputactive" style="width: calc(50% - 5px); margin-bottom: 1em;" placeholder="Корпус" aria-required="true" type="text">
                                            </td>
                                        </tr>
                                        <tr class="Address" style="display: none;">
                                            <td>Квартира</td>
                                            <td>Подъезд</td>
                                            <td>
                                                <input name="new_apartment" id="jform_apartment" value="<?php if (isset($_SESSION['apartment'])) {echo $_SESSION['apartment']; } else echo $apartment ?>" class="inputactive" style="width:50%;margin-bottom:1em;margin-right: 5px;float: left;" placeholder="Квартира"  aria-required="true" type="text">
                                                <input name="new_porch" id="jform_porch"  value="<?php if (isset($_SESSION['porch'])) {echo $_SESSION['porch']; } else echo $porch ?>" class="inputactive" style="width: calc(50% - 5px); margin-bottom: 1em;" placeholder="Подъезд"  aria-required="true" type="text">
                                            </td>
                                        </tr>
                                        <tr class="Address" style="display: none;">
                                            <td>Этаж</td>
                                            <td>Код домофона</td>
                                            <td>
                                                <input name="new_floor" id="jform_floor"  value="<?php if (isset($_SESSION['floor'])) {echo $_SESSION['floor']; } else echo $floor ?>" class="inputactive" style="width:50%; margin-bottom:1em;  margin: 0 5px  0 0; float: left;" placeholder="Этаж" aria-required="true" type="text">
                                                <input name="new_code" id="jform_code"  value="<?php echo $code;?>" class="inputactive" style="width: calc(50% - 5px); margin-bottom: 1em;" placeholder="Код" aria-required="true" type="text">
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?php echo JText::_('COM_GM_CEILING_PROJECTS_PROJECT_CALCULATION_DATE'); ?></th>
                                            <td>
                                                <?php if ($this->item->project_calculation_date == "0000-00-00 00:00:00") { ?>
                                                    -
                                                <?php } else { ?>
                                                    <?php $jdate = new JDate(JFactory::getDate($this->item->project_calculation_date)); ?>
                                                    <?php echo $jdate->format('d.m.Y H:i'); ?>
                                                <?php } ?>
                                            </td>
                                            <td>
                                                <div class="Date" style="display: none;">
                                                    <label id="jform_project_mounting_date-lbl" for="jform_project_new_calc_date">
                                                        Новая дата<span class="star">&nbsp;*</span>
                                                    </label>
                                                    <div id="calendar-container">
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Замерщик</th>
                                            <td>
                                                <?php if ($this->item->project_calculator == null) { ?>
                                                    - 
                                                <?php } else { ?>
                                                    <?php echo JFactory::getUser($this->item->project_calculator)->name; ?>
                                                <?php } ?>
                                            </td>
                                            <td class="Gauger" style="display: none;">
                                                <p>Новый замерщик:</p>
                                                <p id="new_gauger"></p>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Дилер</th>
                                            <td><?php
                                                    $dealer = $client_model->getDealer($this->item->id_client);
                                                    echo $dealer;
                                                ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan=3 style="text-align: center;">
                                                <button type="submit" id="accept_changes" class="btn btn btn-success" style="display: none;">Сохранить изменения</button>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                              <!--   <?php //if ($user->dealer_type == 0) { ?> -->
                                    <div  class="col-12 col-md-6">
                                        <div class="comment" >
                                            <label>История клиента:</label>
                                            <textarea id="comments" class="input-comment" rows=11 readonly></textarea>
                                            <table>
                                                <tr>
                                                    <td>
                                                        <label>Добавить комментарий:</label>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td width = 100%>
                                                        <textarea  class = "inputactive" id="new_comment" placeholder="Введите новое примечание"></textarea>
                                                    </td>
                                                    <td>
                                                        <button class="btn btn-primary" type="button" id="add_comment"><i class="fa fa-paper-plane" aria-hidden="true"></i></button>
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                <!-- <?php //} ?> -->
                            </div>
                        <?php } ?>
                    <?php } ?>
                
            </div>
        </div>
    </div>
    <!-- скидка -->
        <div class="center-left">
            <a class="btn btn-primary" id="change_discount">Изменить величину скидки</a>
        </div>
        <table class="calculation_sum">
            <?php
                if (!empty($calculation_total)) {
                    $skidka = ($calculation_total - $project_total_1) / $calculation_total * 100;
                } else {
                    $skidka = 0;
                }
                
            ?>
            <tbody class="new_discount" style="display: none">
                <tr>
                    <td>
                        <label id="jform_discoint-lbl" for="jform_new_discount">Новый процент скидки:<span class="star">&nbsp;*</span></label>
                    </td>
                    <td></td>
                </tr>
                <tr>
                    <td>
                        <input name="new_discount" id="jform_new_discount" value="" onkeypress="PressEnter(this.value, event)" placeholder="Новый % скидки" max='<?= round($skidka, 0); ?>' type="number" style="width: 100%;">
                        <input name="isDiscountChange" value="0" type="hidden">
                    </td>
                    <td>
                        <button type="button" id="update_discount" class="btn btn btn-primary">Ок</button>
                    </td>
                </tr>
            </tbody>
        </table>
    <!-- конец скидки -->
    
    <?php include_once('components/com_gm_ceiling/views/project/common_table.php'); ?>

    <?php if ($this->item->project_verdict == 0) { ?>
            <?php if ($user->dealer_type != 2) { ?>
                <table>
                    <tr>
                        <td>
                            <a class="btn  btn-success" id="accept_project">Договор</a>
                        </td>
                        <td>
                            <a class="btn  btn-primary" id="refuse_project">Сохранить</a>
                        </td>
                    </tr>
                </table>
            <?php } ?>
        <?php } ?>
        <div class="project_activation" style="display: none;" id="project_activation">
            <?php if ($user->dealer_type != 2) { ?>
                <label id="jform_gm_calculator_note-lbl" for="jform_gm_calculator_note" class="">Примечание к договору</label>
                <div class="controls">
                    <textarea name="gm_calculator_note" id="jform_gm_calculator_note" placeholder="Примечание к договору" aria-invalid="false"></textarea>
                </div>
                <button id="refuse" class="btn btn-success" type="submit" style="display: none;">Переместить в отказы</button>
                <table id="mounter_wraper" style="display: none;">
                    <tr>
                        <td colspan=4>
                            <h4 id="title" style="display: none;">Назначить монтажную бригаду</h4>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <button id="button-prev" type="button" class="btn btn-primary"><i class="fa fa-arrow-left" aria-hidden="true"></i></button>
                        </td>
                        <td>
                            <div id="calendar1">
                               
                            </div>
                        </td>
                        <td>
                            <div id="calendar2">
                              
                            </div>
                        </td>
                        <td>
                            <button id="button-next" type="button" class="btn btn-primary"><i class="fa fa-arrow-right" aria-hidden="true"></i></button>
                        </td>
                    </tr>
                    <tr>
                        <td colspan=4>
                            <label id="jform_chief_note-lbl" for="jform_chief_note" class="">Примечание к монтажу</label>
                            <textarea name="chief_note" id="jform_chief_note" placeholder="Примечание к монтажу" aria-invalid="false">
                                <?php echo $this->item->gm_chief_note; ?>
                            </textarea>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <button class="validate btn btn-primary" id="save" type="button">Сохранить и запустить <br> в производство</button>
                        </td>
                        <td colspan=3 style="text-align: left;">
                            <a class="btn btn-primary" href="<?php echo JRoute::_('index.php?option=com_gm_ceiling&view=projects&type=chief'); ?>">Перейти к монтажам</a>
                        </td>
                    </tr>
                    <tr>
                        <td colspan = 3 id = "new_call" style = "display:none;" >
                            <label>Введите дату и время звонка</label>
                            <input type  = "date" class = "" name ="calldate_without_mounter" id  = "calldate_without_mounter" >
                            <select name = "calltime_without_mounter" class = "" id = "calltime_without_mounter" >
                                <option value="9:00">9:00</option>
                                <option value="10:00">10:00</option>
                                <option value="11:00">11:00</option>
                                <option value="12:00">12:00</option>
                                <option value="13:00">13:00</option>
                                <option value="14:00">14:00</option>
                                <option value="15:00">15:00</option>
                                <option value="16:00">16:00</option>
                                <option value="17:00">17:00</option>
                            </select>
                            <button class = "btn btn-primary" id = "ok_btn" type = "button"><i class="fa fa-check" aria-hidden="true"></i></button>
                        </td>
                    </tr>
                </table>
            <?php } ?>
        </div>
    </div>
    <div id="modal-window-container-tar">
        <button id="close-tar" type="button"><i class="fa fa-times fa-times-tar" aria-hidden="true"></i></button>
        <div id="modal-window-choose-tar">
            <p id="date-modal"></p>
            <p><strong>Выберите монтажника:</strong></p>
            <p>
                <select name="mounters" id="mounters"></select>
            </p>
            <p style="margin-bottom: 0;"><strong>Монтажники:</strong></p>
            <div id="mounters_names"></div>
            <div id="projects_brigade_container"></div>
            <p style="margin-top: 1em;"><strong>Выберите время начала монтажа:</strong></p>
            <p>
                <select name="hours" id='hours'></select>
            </p>
            <p><button type="button" id="save-choise-tar" class="btn btn-primary">Ок</button></p>
        </div>
    </div>
    <div id="modal-window-container2-tar">
        <button id="close2-tar" type="button"><i class="fa fa-times fa-times-tar" aria-hidden="true"></i></button>
        <div id="modal-window-2-tar">
            <p id="date-modal"></p>
            <p><strong>Выберите время замера:</strong></p>
            <p>
                <table id="projects_gaugers"></table>
            </p>
        </div>
    </div>
    <input name="idCalcDelete" id="idCalcDelete" value="<?=$calculation->id;?>" type="hidden">
    </div>
    <div id="modal_window_container" class = "modal_window_container">
        <button type="button" id="close" class = "close_btn"><i class="fa fa-times fa-times-tar" aria-hidden="true"></i></button>
        <div id="modal_window_del" class = "modal_window">
            <h6 style = "margin-top:10px">Вы действительно хотите удалить?</h6>
            <p>
                <button type="button" id="ok" class="btn btn-primary">Да</button>
                <button type="button" id="cancel" onclick="click_cancel();" class="btn btn-primary">Отмена</button>
            </p>
        </div>
    </div>
</form>
<script type="text/javascript" src="/components/com_gm_ceiling/create_calculation.js"></script>
<script type="text/javascript" src="/components/com_gm_ceiling/views/project/common_table.js"></script>
<script type="text/javascript">
    var project_id = "<?php echo $this->item->id; ?>";
    var $ = jQuery;
    var min_project_sum = <?php echo  $min_project_sum;?>;
    var min_components_sum = <?php echo $min_components_sum;?>;
    var self_data = JSON.parse('<?php echo $self_calc_data;?>');
    console.log(self_data);
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

    //скрыть модальное окно
    jQuery(document).mouseup(function (e) {
		var div1 = jQuery("#modal-window-choose-tar");
		if (!div1.is(e.target)
		    && div1.has(e.target).length === 0) {
			jQuery("#close-tar").hide();
			jQuery("#modal-window-container-tar").hide();
			jQuery("#modal-window-choose-tar").hide();
		}
        var div = jQuery("#modal_window_del"); // тут указываем ID элемента
        if (!div.is(e.target) // если клик был не по нашему блоку
            && div.has(e.target).length === 0) { // и не по его дочерним элементам
            jQuery("#close").hide();
            jQuery("#modal_window_container").hide();
            jQuery("#modal_window_del").hide();
        }
        var div2 = jQuery("#modal-window-2-tar");
		if (!div2.is(e.target)
		    && div2.has(e.target).length === 0) {
			jQuery("#close2-tar").hide();
			jQuery("#modal-window-container2-tar").hide();
			jQuery("#modal-window-2-tar").hide();
		}
    });
    //--------------------------------------------------

    jQuery(document).ready(function () {

        document.getElementById('add_calc').onclick = function()
        {
            create_calculation(<?php echo $this->item->id; ?>);
        };

        // для истории и добавления комментария
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
        // ----------------------------------------

        $("#modal_window_container #ok").click(function() { click_ok(this); });
        show_comments();
        //trans();

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

        jQuery("#save").click(function(){
            if(jQuery("#project_mounter").val()==0 && jQuery("#jform_project_mounting_date").val()==0 ){
                jQuery("#new_call").show();
            }
            else {
                jQuery("#form-client").submit();
            }
        });

        jQuery("#ok_btn").click(function(){
            if(jQuery("#calldate_without_mounter").val()&&jQuery("#calltime_without_mounter").val()){
                jQuery("#form-client").submit();
            }
            else{
                var n = noty({
                    theme: 'relax',
                    layout: 'center',
                    maxVisible: 5,
                    type: "error",
                    text: "Введите дату и время звонка!"
                });
            }
        });

        jQuery("#jform_client_contacts").mask("+7 (999) 999-99-99");

        jQuery("#jform_project_new_calc_date").on("keyup", function () {
            jQuery("#jform_new_project_calculation_daypart").prop("disabled",false);
        });
        
        /*jQuery("input[name^='smeta']").click(function () {
            let old_self_comp = jQuery("#calcs_self_components_total span.sum").data('oldval');
                let self_component = jQuery("#calcs_self_components_total span.sum").text();
                let calcs_total = jQuery("#calcs_total_border").text();
                if(jQuery(this).prop("checked") == true){
                    jQuery("input[name='smeta']").val(1);
                    jQuery("#calcs_self_components_total span.sum").text(0);
                    jQuery("#calcs_total_border").text(calcs_total - self_component);
                }
                else{
                    jQuery("input[name='smeta']").val(0);
              
                    jQuery("#calcs_self_components_total span.sum").text(old_self_comp);
                    jQuery("#calcs_total_border").text(parseInt(calcs_total) + parseInt(old_self_comp));
                } 
        });*/

        jQuery("#client_order").click(function () {
            jQuery("input[name='project_verdict']").val(1);
            jQuery("#project_sum").val( jQuery("#project_total_discount").val());
            //jQuery("#project_sum").val(<?php //echo $project_total_discount?>);
        });

        $tmp_accept = 0; $tmp_refuse = 0;
        jQuery("#accept_project").click(function () {
            jQuery("input[name='project_verdict']").val(1);
            
            if($tmp_accept == 0) {
                
                jQuery("#mounter_wraper").show();
                jQuery("#title").show();
                jQuery(".calendar_wrapper").show();
                jQuery(".buttons_wrapper").show();
                jQuery(".project_activation").hide();
                jQuery("#refuse").hide();
                jQuery("#project_activation").show();
                $tmp_accept = 1;
                $tmp_refuse = 0;
            } else {
                jQuery(".project_activation").hide();
                jQuery("#mounter_wraper").hide();
                jQuery("#title").hide();
                jQuery(".calendar_wrapper").hide();
                jQuery(".buttons_wrapper").hide();
                jQuery("#project_activation").hide();
                $tmp_accept = 0;
                $tmp_refuse = 0;
            }
            
            setTimeout(() => {
                window.location = "#project_activation";
            }, 100); 
        });
        jQuery("#refuse_project").click(function () {
            jQuery("input[name='project_verdict']").val(0);
            if($tmp_refuse == 0) {
                jQuery(".project_activation").show();
                jQuery("#refuse").show();
                jQuery("#mounter_wraper").hide();
                jQuery("#title").hide();
                jQuery(".calendar_wrapper").hide();
                jQuery(".buttons_wrapper").hide();
                jQuery("#mounting_date_control").hide();
                $tmp_refuse = 1;
                $tmp_accept = 0;
            } else {
                jQuery(".project_activation").hide();
                jQuery("#refuse").hide();
                $tmp_refuse = 0;
                $tmp_accept = 0;
            }
            setTimeout(() => {
                window.location = "#refuse";
            }, 100); 
            //jQuery(".project_activation").toggle();
            //jQuery("#refuse").toggle();
           // 
        });

        jQuery("#accept_changes").click(function () {
            jQuery("input[name='data_change']").val(1);
        });
        jQuery("#change_data").click(function () {
            jQuery(".FIO").toggle();
            jQuery(".Contacts").toggle();
            jQuery(".Address").toggle();
            jQuery(".Date").toggle();
            jQuery(".Gauger").toggle();
            jQuery("#accept_changes").toggle();
        });

        var temp = 0;
        jQuery("#change_discount").click(function () {
            if (!temp) {
                jQuery(".new_discount").show();
                temp = 1;
            }
            else {
                jQuery(".new_discount").hide();
                temp = 0;
            }
        });

        jQuery("#update_discount").click(function() {
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
    });


    jQuery("#send_all").click(function () {
        jQuery(".email-all").toggle();
    });


    jQuery("#jform_project_new_calc_date").change(function(){
        jQuery("#jform_new_project_calculation_daypart").prop("disabled",false);
    })

   
    jQuery("#spend-form input").on("keyup", function () {
        jQuery('#extra_spend_submit').fadeIn();
    });

    jQuery("#penalty-form input").on("keyup", function () {
        jQuery('#penalty_submit').fadeIn();
    });

    jQuery("#bonus-form input").on("keyup", function () {
        jQuery('#bonus_submit').fadeIn();
    });

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

    function PressEnter(your_text, your_event) {
        if (your_text != "" && your_event.keyCode == 13)
            jQuery("#update_discount").click();
    }

</script>

<?php
else:
    echo JText::_('COM_GM_CEILING_ITEM_NOT_LOADED');
endif;
?>