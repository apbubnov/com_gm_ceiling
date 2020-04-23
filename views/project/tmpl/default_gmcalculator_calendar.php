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
    $userName = $user->get('username');

    /*_____________блок для всех моделей/models block________________*/ 
    $calculationsModel = Gm_ceilingHelpersGm_ceiling::getModel('calculations');
    $phones_model = Gm_ceilingHelpersGm_ceiling::getModel('client_phones');
    $projects_mounts_model = Gm_ceilingHelpersGm_ceiling::getModel('projects_mounts');

    /*________________________________________________________________*/


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

<style>
    .calculation_sum {
        width: 100%;
        margin-bottom: 25px;
    }
    .calculation_sum td {
        padding: 0 5px;
    }
    .act_btn{
        width:210px;
        margin-bottom: 10px;
    }
    .save_bnt{
        width:250px;
        height: 52px;
    }
    .btn_edit{
        position: absolute;
        right:0;
    }
    .row{
        margin-bottom: 5px;
    }
</style>

<link rel="stylesheet" href="/components/com_gm_ceiling/views/project/css/style.css" type="text/css" />

<?=parent::getButtonBack();?>
<?php if ($this->item) : ?>
<?php    
    $phones = $phones_model->getItemsByClientId($this->item->id_client);
?>
<form id="form-client" action="/index.php?option=com_gm_ceiling&task=project.activate&type=gmcalculator&subtype=calendar" method="post" class="form-validate form-horizontal" enctype="multipart/form-data" >
    <div class="project_activation" style="display: none;">
        <input name="project_id" id="project_id" value="<?php echo $this->item->id; ?>" type="hidden">
        <input name="client_id" id="client_id" value="<?php echo $this->item->id_client; ?>" type="hidden">
        <input name="type" value="calculator" type="hidden">
        <input name="subtype" value="calendar" type="hidden">
        <input id="project_verdict" name="project_verdict" value="0" type="hidden">
        <input id="project_status" name="project_status" value="<?php echo $this->item->project_status;?>" type="hidden">
        <input name="data_change" value="0" type="hidden">
        <input name="data_delete" value="0" type="hidden">
        <input id="mounting_date" name="mounting_date" type='hidden'>
        <input id="mount" name="mount" type='hidden' value='<?php echo $json_mount ?>'>
        <input id="jform_project_mounting_date" name="jform_project_mounting_date" value="<?php echo $this->item->project_mounting_date; ?>" type='hidden'>
        <input id="project_mounter" name="project_mounter" value="<?php echo $this->item->project_mounter; ?>" type='hidden'>
        <input id="project_sum" name="project_sum" value="<?php echo $project_total_discount; ?>" type="hidden">
        <input id="project_sum_transport" name="project_sum_transport" value="<?php echo $project_total_discount_transport; ?>" type="hidden">
        <input name="comments_id" id="comments_id" value="<?php if (isset($_SESSION['comments'])) echo $_SESSION['comments']; ?>" type="hidden">
        <input name = "project_new_calc_date" id = "jform_project_new_calc_date"  value="" type='hidden'>
        <input id="jform_project_gauger" name="project_gauger" value="" type='hidden'> 
    </div>
    <h2 class="center" style="margin-top: 15px; margin-bottom: 15px;">Проект № <?php echo $this->item->id ?></h2>
    <div class="container">
        <div class="row">
            <div class="col-xs-12 col-md-6 no_padding">
                <div class="container" style="border: 1px solid #414099;border-radius: 5px;margin-bottom: 15px;">
                    <div class="row">
                        <div class="col-md-4 col-xs-4">
                            <b>
                                <?php echo JText::_('COM_GM_CEILING_FORM_LBL_PROJECT_CLIENT_ID'); ?>
                            </b>
                        </div>
                        <div class="col-md-6 col-xs-6">
                            <a href="/index.php?option=com_gm_ceiling&view=clientcard&id=<?=$this->item->id_client;?>">
                                <?php echo $this->item->client_id; ?>
                            </a>
                        </div>
                        <div class="col-md-2 col-xs-2">
                            <button class="btn btn-sm btn-primary btn_edit" type = "button" id="change_data"><i class="fas fa-pen" aria-hidden="true"></i></button>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <b>
                                <?php echo JText::_('COM_GM_CEILING_CLIENTS_CLIENT_CONTACTS'); ?>
                            </b>
                        </div>
                        <div class="col-md-8">
                            <?php
                                if ($this->item->id_client!=1) {
                                    $phone = $calculationsModel->getClientPhones($this->item->id_client);
                                } else  {
                                    $phone = [];
                                }
                                foreach ($phone AS $contact) {
                                    echo "<a href='tel:+$contact->client_contacts'>$contact->client_contacts</a>";
                                    echo "<br>";
                                }
                            ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <b>
                                Почта
                            </b>
                        </div>
                        <div class="col-md-8">
                            <?php
                            if(!empty($contact_email)){
                                foreach ($contact_email AS $contact) {
                                    echo "<a href='mailto:$contact->contact'>$contact->contact</a>";
                                    echo "<br>";
                                }
                            }
                            ?>
                        </div>
                    </div>

                </div>

                <div class="container" style="border: 1px solid #414099;border-radius: 5px;margin-bottom: 15px;">
                    <div class="row">
                        <div class="col-md-4 col-xs-4">
                            <b>
                                <?php echo JText::_('COM_GM_CEILING_FORM_LBL_PROJECT_PROJECT_INFO'); ?>
                            </b>
                        </div>
                        <div class="col-md-6 col-xs-6">
                            <a target="_blank" href="https://yandex.ru/maps/?mode=search&text=<?=$this->item->project_info;?>">
                                <?=$this->item->project_info;?>
                            </a>
                        </div>
                        <div class="col-md-2 col-xs-2">
                            <button class="btn btn-sm btn-primary btn_edit" type = "button" id="edit_address"><i class="fas fa-pen" aria-hidden="true"></i></button>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <b>
                                <?php echo JText::_('COM_GM_CEILING_PROJECTS_PROJECT_CALCULATION_DATE'); ?>
                            </b>
                        </div>
                        <div class="col-md-8">
                            <?php if ($this->item->project_calculation_date == "0000-00-00 00:00:00") { ?>
                                -
                            <?php } else { ?>
                                <?php $jdate = new JDate(JFactory::getDate($this->item->project_calculation_date)); ?>
                                <?php echo $jdate->format('d.m.Y H:i'); ?>
                            <?php } ?>
                        </div>
                    </div>

                    <?php if(!empty($this->item->project_calculator)):?>
                        <div class="row">
                            <div class="col-md-4">
                                <b>
                                    Замерщик
                                </b>
                            </div>
                            <div class="col-md-8">
                                <?php echo JFactory::getUser($this->item->project_calculator)->name;?>
                            </div>
                        </div>
                    <?php endif;?>
                </div>

                <div class="container">
                    <?php if(!empty($this->item->mount_data)):?>
                        <div class="row center">
                            <div class="col-md-12">
                                <b>
                                    Монтаж
                                </b>
                            </div>
                        </div>
                        <?php foreach ($this->item->mount_data as $value) { ?>
                            <div class="row">
                                <div class="col-md-4">
                                    <b>
                                        <?php echo $value->time;?>
                                    </b>
                                </div>
                                <div class="col-md-4">
                                    <?php echo $value->stage_name;?>
                                </div>
                                <div class="col-md-4">
                                    <?php echo JFactory::getUser($value->mounter)->name;?>
                                </div>
                            </div>
                        <?php }?>
                    <?php endif;?>
                    <div class="row">
                        <div class="col-md-4">
                            <b>
                                Скидка
                            </b>
                        </div>
                        <div class="col-md-6">
                            <?php echo (!empty($this->item->project_discount))?  $this->item->project_discount : " - ";?>
                        </div>
                        <div class="col-md-2" style="text-align: right;">
                             <button class="btn btn-sm btn-primary" type = "button" id="edit_discount"><i class="fas fa-pen" aria-hidden="true"></i></button>
                        </div>
                    </div>
                </div>

                <?php include_once('components/com_gm_ceiling/views/project/project_notes.php')?>

            </div>
            <div class="col-xs-12 col-md-6 comment">
                <label> История клиента: </label>
                <textarea id="comments" class="input-comment" rows=11 readonly> </textarea>
                <table>
                    <tr>
                        <td><label> Добавить комментарий: </label></td>
                    </tr>
                    <tr>
                        <td width = 100%><textarea  class = "inputactive" id="new_comment"></textarea></td>
                        <td><button class="btn btn-primary" type="button" id="add_comment"><i class="fa fa-paper-plane" aria-hidden="true"></i>
                        </button></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>      
    
    <?php include_once('components/com_gm_ceiling/views/project/common_table.php'); ?>

    <!-- активация проекта (назначение на монтаж, заключение договора) -->
        <div class="container" <?php if (!empty($_GET['precalculation'])) {echo "style='display:none'";} ?> >
            <?php if (!in_array($this->item->project_status,VERDICT_STATUSES)) { ?>
                <div class="row center">
                    <div class="col-md-6">
                        <div class="row">
                            <button class="btn btn-success act_btn" <?php echo $status_attr;?> id="accept_project" type="button">Договор</button>
                        </div>
                        <div class="row">
                            <button id="simple_save" class="btn btn-primary act_btn">Сохранить</button>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="row">
                            <button id="refuse" class="btn btn-danger act_btn" type="button">Отказ от договора</button>
                            <div class="row center" id="ref_comment" style="display:none;">
                                <div class="col-md-4">
                                    <label for= "ref_note" >Примечание:</label>
                                </div>
                                <div class="col-md-6">
                                    <textarea name="ref_note" class="input-gm" id="ref_note" placeholder="Примечание" aria-invalid="false"></textarea><br>
                                </div>
                                <div class="col-md-2">
                                    <button class="btn btn-primary" id="refuse_submit" type="button">Ок</button>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <button id="refuse_cooperate" class="btn btn-danger act_btn" type="button">Отказ от сотрудничества</button>
                        </div>
                    </div>
                </div>
            <?php } ?>
            
        <div class="project_activation center" style="display: none;" id="project_activation">
            <?php if ($user->dealer_type != 2) { ?>
            <div class="row">
                <div class = "col-xs-12 col-md-6" id="mounter_wraper">
                    <h6 id="title" style="display: none;">Назначить монтажную бригаду</h6>
                    <div id="calendar_mount" align="center"></div>
                </div>
                <div class = "col-xs-12 col-md-6">
                    <div class="row">
                        <div class="col-md-6">
                            <b>
                                <label id="jform_production_note-lbl" for="jform_production_note">Примечание в производство</label>
                            </b>
                        </div>
                        <div class="col-md-6">
                            <textarea name="production_note" id="jform_production_note" class="input-gm" placeholder="Примечание в производство" aria-invalid="false"></textarea>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <b><label id="jform_mount_note-lbl" for="jform_mount_note" class="">Примечание к монтажу</label></b><br>
                        </div>
                        <div class="col-md-6">
                            <textarea name="mount_note" id="jform_mount_note" class="input-gm" placeholder="Примечание к монтажу" aria-invalid="false"><?php echo $project_notes->gm_chief_note->value; ?></textarea>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <b>
                                <label id="jform_refuse_note-lbl" for="jform_refuse_note" class="">Примечание к незапускаемым потолкам(если есть)</label>
                            </b>
                        </div>
                        <div class="col-md-6">
                            <textarea name="refuse_note" id="jform_refuse_note" class="input-gm" placeholder="Примечание к незапускаемым потолкам" aria-invalid="false"><?php echo $project_notes->gm_chief_note->value; ?></textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row center">
               <!-- <div class="col-xs-12 col-md-3" style="margin-top: 15px">
                    <button class="validate btn btn-primary save_bnt" id="sign_project" type="button">Подписать договор</button>
                </div>-->
                <div class="col-xs-12 col-md-4" style="margin-top: 15px">
                    <button class="validate btn btn-primary save_bnt" id="save" type="button">Сохранить и запустить <br> в производство</button>
                </div>
                <div class="col-xs-12 col-md-4" style="margin-top: 15px">
                    <button class="validate btn btn-primary save_bnt" id="save_by_call_btn" type="button">Сохранить и запустить <br> монтаж по звонку</button>
                </div>
                <div class="col-xs-12 col-md-4" style="margin-top: 15px">
                    <a class="btn btn-primary save_bnt" href="<?php echo JRoute::_('index.php?option=com_gm_ceiling&view=projects&type=chief'); ?>">Перейти к монтажам</a>
                </div>
            </div>

            <?php } ?>
        </div>

    </div>
    </div>
</form>

<div class="modal_window_container" id="mw_container">
    <button type="button" class="close_btn" id="close_mw"><i class="fa fa-times fa-times-tar" aria-hidden="true"></i></button>
    <div class="modal_window" id="modal_window_measures_calendar" style="border: 2px solid black; border-radius: 4px;"></div>
    <div class="modal_window" id="modal_window_mounts_calendar"></div>
    <div id="modal_window_by_email" class = "modal_window">
        <p><strong>Введите адрес эл.почты:</strong></p>
        <p>
            <input id = "email_to_send" name = "email_to_send" class = "input-gm">
        </p>
        <p><button class = "btn btn-primary">Запустить</button></p>
    </div>
    <div id="mw_cl_info" class="modal_window">
        <h4>Изменение данных клиента</h4>
        <form id = "new_cl_info">
            <div class="row">
                <div class="col-md-6">
                    <div class="row">
                        <div class="col-md-3">
                            <label for="new_surname"> Фамилия </label>
                        </div>
                        <div class="col-md-9">
                            <input class="form-control" type="text" id='new_surname'>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <label for="new_name" > Имя </label>
                        </div>
                        <div class="col-md-9">
                            <input class="form-control" type="text" id='new_name'>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <label for="new_patronymic"> Отчество </label>
                        </div>
                        <div class="col-md-9">
                            <input class="form-control" type="text" id='new_patronymic'>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <table align="center" id="client_phones">
                        <thead>
                            <th>
                                Телефоны клиента
                            </th>
                            <th>
                                <button id="add_phone" class="btn btn-primary" type="button"><i class="fa fa-plus-square" aria-hidden="true"></i></button>
                            </th>
                        </thead>
                        <tbody>
                            <?php foreach ($phone as $value) { ?>
                                <tr>
                                    <td>
                                         <input name="new_client_contacts[]" id="jform_client_contacts[]" data-old = "<?php echo $value->client_contacts;?>" placeholder="Телефон клиента" type="text" value=<?php echo $value->client_contacts;?>>
                                    </td>
                                    <td>
                                        <button class="btn btn-danger phone" type="button"><i class="fas fa-trash-alt" aria-hidden="true"></i></button>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                    <table align="center" id="client_emails">
                        <thead>
                            <th>
                                Эл.почта клиента
                            </th>
                            <th>
                                <button id="add_email" class="btn btn-primary" type="button"><i class="fa fa-plus-square" aria-hidden="true"></i></button>
                            </th>
                        </thead>
                        <tbody>
                            <?php   if(!empty($contact_email)){
                                foreach ($contact_email as $value) { ?>
                                    <tr>
                                        <td>
                                             <input name="new_client_emails[]" id="jform_client_emails[]" placeholder="Email клиента" type="text" data-old="<?php echo $value->contact;?>" value=<?php echo $value->contact;?>>
                                        </td>
                                        <td>
                                            <button class="btn btn-danger email"><i class="fas fa-trash-alt" aria-hidden="true"></i></button>
                                        </td>
                                    </tr>
                            <?php }
                                }?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <input name="new_client_name" id="jform_client_name" value="" type="hidden">
            
        </form>
        <button id = "update_cl_info" class="btn btn-primary" type="button">Сохранить</button>
    </div>
    <div id="mw_rec_to_msr" class="modal_window">
        <div class="row">
            <div class="col-md-6">
                <label><strong>Адрес замера</strong></label>
                <table align="center">
                    <tr>
                        <td>Улица:</td>
                        <td style="padding-bottom: 10px;">
                            <input name="rec_address" id="jform_rec_address"  placeholder="Улица" type="text">
                        </td>
                    </tr>
                    <tr>
                        <td>Дом:</td>
                        <td style="padding-bottom: 10px;">
                            <input name="rec_house" id="jform_rec_house"  placeholder="Дом"  aria-required="true" type="text">
                        </td>
                    </tr>
                    <tr>
                        <td>Корпус:</td>
                        <td style="padding-bottom: 10px;">
                            <input name="rec_bdq" id="jform_rec_bdq"  placeholder="Корпус" aria-required="true" type="text">
                        </td>
                    </tr>
                    <tr>
                        <td>Квартира:</td>
                        <td style="padding-bottom: 10px;">
                            <input name="rec_apartment" id="rec_apartment" placeholder="Квартира"  aria-required="true" type="text">
                        </td>
                    </tr>
                    <tr>
                        <td>Подъезд:</td>
                        <td style="padding-bottom: 10px;">
                            <input name="rec_porch" id="jform_rec_porch" placeholder="Подъезд"  aria-required="true" type="text">
                        </td>
                    </tr>
                    <tr>
                        <td>Этаж:</td>
                        <td style="padding-bottom: 10px;">
                            <input name="rec_floor" id="jform_rec_floor" placeholder="Этаж" aria-required="true" type="text">
                        </td>
                    </tr>
                    <tr>
                        <td>Код:</td>
                        <td style="padding-bottom: 10px;">
                            <input name="rec_code" id="jform_rec_code" placeholder="Код" aria-required="true" type="text">
                        </td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <label><strong>Время замера</strong></label>
                <div id = "measures_calendar" align="center"></div>
                <input  id="measure_info" readonly>
            </div>
        </div>
        <button  id = "save_rec" class="btn btn-primary" type="button">Сохранить</button>
    </div>
    <div id="mw_measures_calendar" class="modal-window1"></div>
    <div id="mw_mounts_calendar" class="modal_window"></div>
    <div id="mw_add_call" class="modal_window" >
        <h4>Добавить звонок</h4>
        <link rel="stylesheet" href="/components/com_gm_ceiling/date_picker/nice-date-picker.css">
        <script src="/components/com_gm_ceiling/date_picker/nice-date-picker.js"></script>
        <label><b>Дата: </b></label><br>
        <div id="calendar-wrapper" align="center"></div>
        <script>
            new niceDatePicker({
                dom:document.getElementById('calendar-wrapper'),
                mode:'en',
                onClickDate:function(date){
                    document.getElementById('call_date').value = date;
                }
            });
        </script>
        <p><label><b>Время: </b></label><br><input type="time" id="call_time"></p>
        <p><input name="call_date" id="call_date" type="hidden"></p>
        <p><input name="call_comment" id="call_comment" placeholder="Введите примечание"></p>
        <P><button class="btn btn-primary" id="add_call" type="button">Сохранить</button></p>
    </div>
    <div id="mw_discount" class="modal_window">
        <p>
            <label id="jform_discoint-lbl" for="jform_new_discount">Новый процент скидки:</label>
            <input name="new_discount" id="jform_new_discount" placeholder="%" min="0" max='<?= round($skidka, 0); ?>' type="number" style="width: 100%;">
        </p>
        <p>
            <button type="button" id="update_discount" class="btn btn-primary">Сохранить</button>
        </p>
    </div>
    <div id ="mw_signature" class="modal_window">
        <div class="row" style="width: 100%;height:40%;">
            <div class="col-md-6">
                <canvas id="signCanvas" style="border: #414099 2px solid;"></canvas>
            </div>
            <div class="col-md-6">
                <div class="col-md-6">
                    Дата рождения
                    <input type="date" id="client_birthday" class="form-control">
                </div>
                <div class="col-md-6">
                    Документ
                    <textarea id="client_doc" class="input-gm" rows="3" ></textarea>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-3"></div>
            <div class="col-md-3">
                <button class="btn btn-primary" id="reset_sign">Очистить подпись</button>
            </div>
            <div class="col-md-3">
                <button class="btn btn-primary" id="save_sign">Сохранить</button>
            </div>
            <div class="col-md-3"></div>

        </div>
    </div>
</div>
<script type="text/javascript" src="/components/com_gm_ceiling/create_calculation.js"></script>
<script type="text/javascript" src="/components/com_gm_ceiling/views/project/common_table.js"></script>

    <script type="text/javascript" src="/components/com_gm_ceiling/date_picker/measures_calendar.js"></script>
    <script type="text/javascript" src="/components/com_gm_ceiling/date_picker/mounts_calendar.js"></script>
    <script type="text/javascript" src="/sketch/libs/paper-full.js"></script>
    <script type="text/javascript" src="/signature/signature.js"></script>

<script type="text/javascript">
    init_measure_calendar('measures_calendar','jform_project_new_calc_date','jform_project_gauger','mw_measures_calendar',['close_mw','mw_container'], 'measure_info');
    init_mount_calendar('calendar_mount','mount','mw_mounts_calendar',['close_mw','mw_container']);

    var $ = jQuery;
    var min_project_sum = <?php echo  $min_project_sum;?>;
    var min_components_sum = <?php echo $min_components_sum;?>;
    var self_data = JSON.parse('<?php echo $self_calc_data;?>');
    var project_id = "<?php echo $this->item->id; ?>";
    var precalculation = <?php if (!empty($_GET['precalculation'])) { echo $_GET['precalculation']; } else { echo 0; } ?>;
    var deleted_phones = [], deleted_emails = [];

        // закрытие окон модальных
        jQuery(document).mouseup(function (e){ // событие клика по веб-документу
            var div1 = jQuery("#modal_window_by_email"),
                div2 = jQuery("#mw_rec_to_msr"),
                div3 = jQuery("#mw_discount"),
                div4 = jQuery("#mw_add_call"),
                div5 = jQuery("#mw_address"),
                div6 = jQuery("#mw_cl_info"),
                div7 = jQuery("#mw_mounts_calendar"),
                div8 = jQuery("#mw_measures_calendar"),
                div9 = jQuery('#mw_signature');
            if (!div1.is(e.target) // если клик был не по нашему блоку
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
                && div6.has(e.target).length === 0
                && !div7.is(e.target)
                && div7.has(e.target).length === 0
                && !div8.is(e.target)
                && div8.has(e.target).length === 0
                && !div9.is(e.target)
                && div9.has(e.target).length === 0){ // и не по его дочерним элементам
                jQuery("#close_mw").hide();
                jQuery("#mw_container").hide();
                div1.hide();
                div2.hide();
                div3.hide();
                div4.hide();
                div5.hide();
                div6.hide();
                div7.hide();
                div8.hide();
                div9.hide()
            }
        });
    //--------------------------------------------------

    jQuery(document).ready(function () {
        var client_id = "<?php echo $this->item->id_client;?>";
        var client_name = "<?php echo $this->item->client_id;?>";
        jQuery("[name = 'new_client_contacts[]']").mask('+7(999) 999-9999');


        /*jQuery('#sign_project').click(function(){
            jQuery("#close_mw").show();
            jQuery('#mw_container').show();
            jQuery('#mw_signature').show();
        });*/

        jQuery("#change_data").click(function(){
            jQuery("#close_mw").show();
            jQuery("#mw_container").show();
            jQuery("#mw_cl_info").show();
        });

        jQuery("#edit_discount").click(function(){
            jQuery("#close_mw").show();
            jQuery("#mw_container").show();
            jQuery("#mw_discount").show();
        });

        jQuery("#edit_address").click(function(){
            jQuery("#close_mw").show();
            jQuery("#mw_container").show();
            jQuery("#mw_rec_to_msr").show();
        });

        jQuery("#add_phone").click(function(){
                jQuery('#client_phones tr:last').after('<tr><td><input name="new_client_contacts[]" id="jform_client_contacts[]" placeholder="Телефон клиента"></td><td><button class="btn btn-danger phone" type="button"><i class="fas fa-trash-alt" aria-hidden="true"></i></button></td></tr>');
                jQuery(".phone").click(function(){
                     var tr = jQuery(this).closest('tr');
                     remove_tr(tr,deleted_phones);
                });
                jQuery("[name = 'new_client_contacts[]']").mask('+7(999) 999-9999');
            });

        jQuery(".phone").click(function(){
             var tr = jQuery(this).closest('tr');
             remove_tr(tr,deleted_phones);
        });


        jQuery("#add_email").click(function(){
            jQuery('#client_emails tr:last').after('<tr><td><input name="new_client_emails[]" id="jform_client_emails[]" placeholder="Email клиента"></td><td><button class="btn btn-danger phone" type="button"><i class="fas fa-trash-alt" aria-hidden="true"></i></button></td></tr>');
            jQuery(".phone").click(function(){
                 var tr = jQuery(this).closest('tr');
                 remove_tr(tr,deleted_phones);
            });
        });

        jQuery(".email").click(function(){
             var tr = jQuery(this).closest('tr');
             remove_tr(tr,deleted_emails);
        });

        function remove_tr (tr,arr){
            if(tr.find("input").val()){
                arr.push(tr.find("input").val());
            }
            tr.remove();
        }


        jQuery("#update_cl_info").click(function(){
            var phones = jQuery.map(jQuery('[name = "new_client_contacts[]"]'),function(value){
                if(value.value != jQuery(value).data("old"))
                    return {phone: value.value,old_phone:jQuery(value).data("old")};
                }),
                emails = jQuery.map(jQuery('[name = "new_client_emails[]'),function(value){
                    if(value.value != jQuery(value).data("old"))
                        return {email: value.value,old_email:jQuery(value).data("old")};
                }),
                new_surname = jQuery('#new_surname').val(),
                new_cl_name = jQuery('#new_name').val(),
                new_patronymic = jQuery('#new_patronymic').val(),
                fio = '',
                new_name = ''; 
            if(!empty(new_surname)){
                fio += new_surname;
            }
            if(!empty(new_cl_name)){
                if(!empty(fio)){
                    fio += ' ' + new_cl_name;
                }
                else{
                    fio += new_cl_name;
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
        
            if(!empty(fio) && fio != client_name){
                new_name = fio;
            }
            
            jQuery.ajax({
                url: "index.php?option=com_gm_ceiling&task=client.update_info",
                data: {
                    phones: phones,
                    emails: emails,
                    deleted_emails: deleted_emails,
                    deleted_phones: deleted_phones,
                    client_name: new_name,
                    client_id: client_id
                },
                dataType: "json",
                async: true,
                success: function (data) {
                    var n = noty({
                        timeout: 2000,
                        theme: 'relax',
                        layout: 'topCenter',
                        maxVisible: 5,
                        type: "success",
                        text: "Данные успешно изменены!"
                    });
                    location.reload();
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

        jQuery("#save_rec").click(function(){

            var address = "",
                street = jQuery("#jform_rec_address").val(),
                house = jQuery("#jform_rec_house").val(),
                bdq = jQuery("#jform_rec_bdq").val(),
                apartment = jQuery("#jform_rec_apartment").val(),
                porch = jQuery("#jform_rec_porch").val(),
                floor =jQuery("#jform_rec_floor").val(),
                code = jQuery("#jform_rec_code").val();
            if(house) address = street + ", дом: " + house;
            if(bdq) address += ", корпус: " + bdq;
            if(apartment) address += ", квартира: "+ apartment;
            if(porch) address += ", подъезд: " + porch;
            if(floor) address += ", этаж: " + floor;
            if(code) address += ", код: " + code;

            var data = {id:project_id,project_calculator:jQuery("#jform_project_gauger").val(), project_calculation_date:jQuery("#jform_project_new_calc_date").val(),project_info:address};

            data = JSON.stringify(data)

            jQuery.ajax({
                url: "index.php?option=com_gm_ceiling&task=project.change_project_data",
                data: {
                    new_data: data
                },
                dataType: "json",
                async: true,
                success: function (data) {
                    var n = noty({
                        timeout: 2000,
                        theme: 'relax',
                            layout: 'topCenter',
                            maxVisible: 5,
                            type: "success",
                            text: "Данные успешно изменены!"
                        });
                        location.reload();
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

        show_comments();

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

        jQuery("#save").click(function() {
            var prepayment_taken = jQuery("#prepayment_taken").val(),
                prepayment_sum = jQuery('#prepayment').val();
            if((prepayment_sum != "" && prepayment_sum >= 0) || prepayment_taken != 0){
                if (empty(jQuery("#mount").val())) {
                    noty({
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "alert",
                        text: "Не указана дата монтажа. Продолжить?",
                        buttons: [
                            {
                                addClass: 'btn btn-primary', text: 'Да', onClick: function (modal) {
                                    jQuery("input[name='project_status']").val(5);
                                    jQuery("input[name='project_verdict']").val(1);
                                    jQuery('#form-client').submit();
                                    modal.close();
                                }
                            },
                            {
                                addClass: 'btn btn-primary', text: 'Нет', onClick: function (modal) {
                                    modal.close();
                                }
                            }
                        ]
                    });
                }
                else {
                    jQuery("input[name='project_status']").val(5);
                    jQuery("input[name='project_verdict']").val(1);
                    jQuery('#form-client').submit();
                }
            }
            else {
                noty({
                    timeout: 2000,
                    theme: 'relax',
                    layout: 'center',
                    maxVisible: 5,
                    type: "error",
                    text: "Не введена предоплата!"
                });
                jQuery('html,body').animate({ scrollTop: jQuery('#prepayment').offset().top }, 1000);

            }

        });

        jQuery("#save_by_call_btn").click(function(){
            jQuery('#project_status').val(30)
            jQuery("#form-client").submit();
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
                //jQuery("#refuse").hide();
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
            jQuery("#accept_changes").toggle();
            jQuery("#FIO_static").toggle();
            jQuery("#Address_static").toggle();
            jQuery("#Date_static").toggle();
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
        if (document.getElementById('refuse_cooperate')){
                document.getElementById('refuse_cooperate').onclick = click_on_refuse_cooperate;
            }


            function click_on_refuse_cooperate()
            {
                noty({
                    layout: 'topCenter',
                    type: 'default',
                    modal: true,
                    text: 'Перевести проект в статус "отказ от сотрудничества"?',
                    killer: true,
                    buttons: [
                        {
                            addClass: 'btn btn-success', text: 'Ок', onClick: function ($noty) {
                                jQuery.ajax({
                                    url: "index.php?option=com_gm_ceiling&task=project.updateProjectStatus",
                                    data: {
                                        project_id: project_id,
                                        status: 15
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
                                            text: "Проект переведен в отказ от сотрудничества"
                                        });
                                        setTimeout(function(){location.href = '/index.php?option=com_gm_ceiling&task=mainpage'}, 2000);
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
                                $noty.close();
                            }
                        },
                        {
                            addClass: 'btn', text: 'Отмена', onClick: function ($noty) {
                                $noty.close();
                            }
                        }
                    ]
                });
            };

            jQuery("#refuse").click(function(){

                jQuery("#ref_comment").toggle();
                //jQuery("#jform_gm_calculator_note").val(jQuery("#ref_note").val());
                jQuery('#project_status').val(3);
                jQuery("#project_verdict").val(0);
                //document.getElementById('form-client').submit();
            });

        jQuery("#refuse_submit").click(function(){
            document.getElementById('form-client').submit();
        });    
        jQuery("#update_discount").click(function() {
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
    });


    jQuery("#send_all").click(function () {
        jQuery(".email-all").toggle();
    });

    /*function PressEnter(your_text, your_event) {
        if (your_text != "" && your_event.keyCode == 13)
            jQuery("#update_discount").click();
    }*/

</script>

<?php
else:
    echo JText::_('COM_GM_CEILING_ITEM_NOT_LOADED');
endif;
?>