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
$user_group = $user->groups;

$project_id = $this->item->id;

/*_____________блок для всех моделей/models block________________*/
$client_model = Gm_ceilingHelpersGm_ceiling::getModel('client');

$project_card = '';
$phones = [];
if (!empty($_SESSION["project_card_$project_id"])) {
    $project_card = $_SESSION["project_card_$project_id"];
    $phones = json_decode($project_card)->phones;
}
$dealer = JFactory::getUser($this->item->dealer_id);
$dealerType = $dealer->dealer_type;
?>
<style>
    .row {
        margin-bottom: 15px;
    }
</style>
<link rel="stylesheet" href="/components/com_gm_ceiling/views/project/css/style.css" type="text/css"/>

<button id="back_btn" class="btn btn-primary"><i class="fa fa-arrow-left" aria-hidden="true"></i> Назад</button>
<a class="btn btn-primary"
   href="/index.php?option=com_gm_ceiling&view=clientcard&id=<?php echo $this->item->id_client; ?>">В карточку</a>
<?php if ($dealerType == 7) { ?>
    <a class="btn btn-primary"
       href="/index.php?option=com_gm_ceiling&view=clientcard&type=builder&id=<?php echo $dealer->associated_client; ?>">В
        застройщика</a>
<?php } ?>
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
    if ($this->item->manager_id == 1 || empty($model_client->getClientById($this->item->id_client)->manager_id)) {
        $model_client->updateClientManager($this->item->id_client, $userId);
    }
    $projects_model = Gm_ceilingHelpersGm_ceiling::getModel('projects');
    $projects_model->updateManagerId($userId, $this->item->id_client);
    $request_model = Gm_ceilingHelpersGm_ceiling::getModel('requestfrompromo');
    $request_model->delete($this->item->id_client);
    $client_sex = $model_client->getClientById($this->item->id_client)->sex;
    if ($this->item->id_client != 1) {
        $dop_contacts = Gm_ceilingHelpersGm_ceiling::getModel('Clients_dop_contacts');
        $email = $dop_contacts->getEmailByClientID($this->item->id_client);
    }
    $client_dealer = JFactory::getUser($model_client->getClientById($this->item->id_client)->dealer_id);
    $skidka = 0;
    if (!empty($calculation_total)) {
        $skidka = ($calculation_total - $project_total_1) / $calculation_total * 100;
    }
    ?>
    <h5 class="center">
        Дилер/клиент дилера (<?= $client_dealer->name ?>)
    </h5>
    <button id="show_cl_block" class="btn btn-primary" type="button">Раскрыть блок информации о клиенте</button>
    <div class="container" id="cl_block" style="display:none;">
        <div class="row">
            <div class="item_fields">
                <h4>Информация по проекту № <?php echo $this->item->id ?></h4>
                <form id="form-client"
                      action="/index.php?option=com_gm_ceiling&task=project.run_in_production&type=gmmanager&subtype=calendar"
                      method="post" class="form-validate form-horizontal" enctype="multipart/form-data">
                    <input name="project_id" id="project_id" value="<?php echo $this->item->id; ?>" type="hidden">
                    <input name="client_id" id="client_id" value="<?php echo $this->item->id_client; ?>" type="hidden">
                    <input name="comments_id" id="comments_id" type="hidden">
                    <input name="status" id="project_status" value="" type="hidden">
                    <input name="call_id" value="<?php echo $call_id; ?>" type="hidden">
                    <input name="type" value="gmmanager" type="hidden">
                    <input name="subtype" value="calendar" type="hidden">
                    <input name="data_change" id="data_change" value="0" type="hidden">
                    <input name="data_delete" value="0" type="hidden">
                    <input id="project_sum" name="project_sum" value="<?php echo $project_total_discount ?>"
                           type="hidden">
                    <input id="project_sum_transport" name="project_sum_transport"
                           value="<?php echo $project_total_discount_transport ?>" type="hidden">
                    <input id="mount" name="mount" type='hidden' value='<?php echo $this->item->mount_data; ?>'>
                    <input id="emails" name="emails" value="" type="hidden">
                    <div class="row">
                        <div class="col-xs-6 col-sm-6 col-md-6 col-lg-6">
                            <div class="container">
                                <div class="row">
                                    <div class="col-md-4">
                                        <b>
                                            <?php echo JText::_('COM_GM_CEILING_FORM_LBL_PROJECT_CLIENT_ID'); ?>
                                        </b>
                                    </div>
                                    <div class="col-md-8">
                                        <input name="new_client_name"
                                               class="form-control"
                                               id="jform_client_name" value="<?php echo $this->item->client_id; ?>"
                                               placeholder="ФИО клиента" type="text"></div>
                                    <?php if ($this->item->id_client == "1") { ?>
                                        <div class="col-md-2">
                                            <button id="find_old_client" type="button" class="btn btn-primary"><i
                                                        class="fa fa-search" aria-hidden="true"></i></button>
                                        </div>
                                    <?php } ?>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <b>Пол клиента</b>
                                    </div>
                                    <div class="col-md-6">
                                        <input id='male' type='radio' class="radio" name='slider-sex'
                                               value='0' <?php if ($client_sex == "0") echo "checked"; ?>>
                                        <label for='male'>Mужской</label>
                                        <input id='female' type='radio' class="radio" name='slider-sex'
                                               value='1' <?php if ($client_sex == "1") echo "checked"; ?> >
                                        <label for='female'>Женский</label>
                                    </div>
                                </div>
                                <div class="row" id="search" style="display : none;">
                                    <div class="col-md-6">
                                        <b>Выберите клиента из списка:</b>
                                    </div>
                                    <div class="col-md-6">
                                        <select id="found_clients" class="form-control">
                                        </select>
                                    </div>
                                </div>
                                <?php $client_model = Gm_ceilingHelpersGm_ceiling::getModel('client');
                                $birthday = $client_model->getClientBirthday($this->item->id_client); ?>
                                <div class="row">
                                    <div class="col-md-4">
                                        <b>
                                            Дата рождения
                                        </b>
                                    </div>
                                    <div class="col-md-6">
                                        <input name="new_birthday" id="jform_birthday" class="form-control"
                                               value="<?php if ($birthday->birthday != 0000 - 00 - 00) echo $birthday->birthday; ?>"
                                               placeholder="Дата рождения" type="date">
                                    </div>
                                    <div class="col-md-2">
                                        <button type="button" class="btn btn-primary" id="add_birthday">Ок</button>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <?php echo JText::_('COM_GM_CEILING_CLIENTS_CLIENT_CONTACTS'); ?>
                                        <button type="button" class="btn btn-primary" id="add_phone"><i
                                                    class="fa fa-plus-square" aria-hidden="true"></i></button>
                                    </div>
                                    <div class="col-md-6">
                                        <?php if ($this->item->id_client == 1) { ?>
                                            <input name="new_client_contacts[]" id="jform_client_contacts"
                                                   class="form-control" value="<?php echo $phonefrom; ?>"
                                                   placeholder="Телефон клиента" type="text">
                                        <?php } elseif (count($cl_phones) == 1) { ?>
                                            <input name="new_client_contacts[<?php echo '\'' . $cl_phones[0]->phone . '\'' ?>]"
                                                   id="jform_client_contacts"
                                                   class="form-control" value="<?php echo $cl_phones[0]->phone; ?>"
                                                   type="text">

                                        <?php } elseif (count($cl_phones) > 1) {
                                            foreach ($cl_phones as $value) { ?>
                                                <input name="new_client_contacts[<?php echo '\'' . $value->phone . '\'' ?>]"
                                                       id="jform_client_contacts"
                                                       class="form-control" value="<?php echo strval($value->phone); ?>"
                                                       type="text">
                                            <?php }
                                        } ?>
                                    </div>
                                    <?php if (count($cl_phones) == 1): ?>
                                        <div class="col-md-2">
                                            <button id="make_call" type="button" class="btn btn-primary"><i
                                                        class="fa fa-phone" aria-hidden="true"></i></button>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div id="phones-block"></div>
                                    </div>
                                </div>
                                <?php if (count($cl_phones) > 1): ?>
                                    <div class="row">
                                        <div class="col-md-6">
                                            Сделать звонок:
                                        </div>
                                        <div class="col-md-6">
                                            <select id="select_phones" class="inputactive">
                                                <option value='0' disabled selected>Выберите номер для звонка:</option>
                                                <?php foreach ($cl_phones as $item): ?>
                                                    <option value="<?php echo $item->phone; ?>"><?php echo $item->phone; ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                <?php if ($call_id != 0): ?>
                                    <div class="row" style="margin-bottom: 1em;">
                                        <div class="col-md-12">
                                            <button id="broke" type="button" class="btn btn-primary"
                                                    style="width: 100%;">Звонок сорвался,
                                                перенести время
                                            </button>
                                        </div>
                                        <div id="call_up" class="col-md-12" style="display:none;">
                                            <div class="col-md-12">
                                                <label for="call">Добавить звонок</label>
                                            </div>
                                            <div class="col-md-5">
                                                <input name="call_date" id="call_date_up" type="datetime-local"
                                                       class="form-control" placeholder="Дата звонка">
                                            </div>
                                            <div class="col-md-5">
                                                <input name="call_comment" id="call_comment_up" class="form-control"
                                                       placeholder="Введите примечание">
                                            </div>
                                            <div class="col-md-2">
                                                <button class="btn btn-primary" id="add_call_and_submit_up"
                                                        type="button"><i
                                                            class="fas fa-save" aria-hidden="true"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                <?php if (isset($_SESSION['phones']) && count($_SESSION['phones'] > 1)) {
                                    for ($i = 1; $i < count($_SESSION['phones']); $i++) { ?>
                                        <div class="row dop-phone">
                                            <div class="col-md-10">
                                                <input name='new_client_contacts[<?php echo $i; ?>]'
                                                       id='jform_client_contacts'
                                                       class='form-control'
                                                       value="<?php echo $_SESSION['phones'][$i]; ?>">
                                            </div>
                                            <div class="col-md-2">
                                                <button class='clear_form_group btn btn-danger' type='button'>
                                                    <i class='fa fa-trash' aria-hidden='true'></i>
                                                </button>
                                            </div>
                                        </div>
                                    <?php }
                                } ?>
                                <?php if (count($email) > 0) {
                                    foreach ($email as $value) { ?>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <b>
                                                    e-mail
                                                </b>
                                            </div>
                                            <div class="col-md-8">
                                                <input name="email[]" id="email" class="form-control" readonly
                                                       value="<?php echo $value->contact; ?>" placeholder="e-mail"
                                                       type="text">
                                            </div>
                                        </div>
                                    <?php }
                                } ?>
                                <div class="row">
                                    <div class="col-md-4">
                                        <b>
                                            Добавить адрес эл.почты
                                        </b>
                                    </div>
                                    <div class="col-md-6">
                                        <input name="new_email" id="jform_email" class="form-control"
                                               value="" placeholder="e-mail" type="text">
                                    </div>
                                    <div class="col-md-2">
                                        <button type="button" class="btn btn-primary" id="add_email">Ок</button>
                                    </div>
                                </div>
                                <?php

                                $adress = Gm_ceilingHelpersGm_ceiling::parseProjectInfo($this->item->project_info);
                                ?>
                                <div class="row">
                                    <div class="col-xs-4 col-md-4">
                                        <b><?php echo JText::_('COM_GM_CEILING_FORM_LBL_PROJECT_PROJECT_INFO'); ?></b>
                                    </div>
                                    <div class="col-xs-8 col-md-8">
                                        <input name="new_address" id="jform_address" class="form-control"
                                               value="<?php echo $adress->street ?>" placeholder="Адрес" type="text">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-xs-4 col-md-4">
                                        <b>Дом / Корпус</b>
                                    </div>
                                    <div class="col-xs-4 col-md-4">
                                        <input name="new_house" id="jform_house" value="<?php echo $adress->house ?>"
                                               class="form-control" placeholder="Дом" aria-required="true" type="text">
                                    </div>
                                    <div class="col-xs-4 col-md-4">
                                        <input name="new_bdq" id="jform_bdq" value="<?php echo $adress->bdq ?>"
                                               class="form-control" placeholder="Корпус" aria-required="true"
                                               type="text">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-xs-4 col-md-4">
                                        <b>Квартира / Подъезд</b>
                                    </div>
                                    <div class="col-xs-4 col-md-4">
                                        <input name="new_apartment" id="jform_apartment"
                                               value="<?php echo $adress->apartment ?>" class="form-control"
                                               placeholder="Квартира" aria-required="true" type="text">
                                    </div>
                                    <div class="col-xs-4 col-md-4">
                                        <input name="new_porch" id="jform_porch" value="<?php echo $adress->porch ?>"
                                               class="form-control" placeholder="Подъезд" aria-required="true"
                                               type="text">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-xs-4 col-md-4">
                                        <b>Этаж / Код домофона</b>
                                    </div>
                                    <div class="col-xs-4 col-md-4">
                                        <input name="new_floor" id="jform_floor" value="<?php echo $adress->floor ?>"
                                               class="form-control" placeholder="Этаж" aria-required="true" type="text">
                                    </div>
                                    <div class="col-xs-4 col-md-4">
                                        <input name="new_code" id="jform_code" value="<?php echo $adress->code ?>"
                                               class="form-control" placeholder="Код" aria-required="true" type="text">
                                    </div>
                                </div>
                                <div class="row center">
                                    <div class="col-md-12">
                                        <button class="btn btn-primary" id="save_project_info" type="button">Сохранить
                                            адрес
                                        </button>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <b>
                                            Менеджер
                                        </b>
                                    </div>
                                    <div class="col-md-8">
                                        <input name="Manager_name" id="manager_name" class="inputhidden"
                                               value="<?php if (isset($this->item->read_by_manager) && $this->item->read_by_manager != 1) {
                                                   echo JFactory::getUser($this->item->read_by_manager)->name;
                                               } ?>" readonly>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <b>
                                            % скидки
                                        </b>
                                    </div>
                                    <div class="col-md-6">
                                        <?= empty($this->item->project_discount) ? '-' : $this->item->project_discount; ?>
                                    </div>
                                    <?php if (!in_array($this->item->project_status, VERDICT_STATUSES)) { ?>
                                        <div class="col-md-2">
                                            <button class="btn btn-primary" type="button" id="change_discount">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </div>
                                    <?php } ?>
                                </div>
                                <div class="row new_discount" style="display: none;">
                                    <div class="col-md-4">
                                        <label id="jform_discoint-lbl" for="jform_new_discount">Новый процент
                                            скидки:</label>
                                    </div>
                                    <div class="col-md-6">
                                        <input name="new_discount" id="jform_new_discount" value=""
                                               placeholder="Новый % скидки"
                                               max='<?= round($skidka, 0); ?>' type="number" class="form-control">
                                    </div>
                                    <div class="col-md-2">
                                        <button type="button" id="update_discount" class="btn btn-primary">Ок</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-6 col-lg-6">
                            <div class="comment">
                                <label> История клиента: </label>
                                <textarea id="comments" class="form-control" rows=11 readonly> </textarea>
                                <table>
                                    <tr>
                                        <td><label> Добавить комментарий: </label></td>
                                    </tr>
                                    <tr>
                                        <td width=100%>
                                            <textarea class="form-control" id="new_comment"></textarea>
                                        </td>
                                        <td>
                                            <button class="btn btn-primary" type="button" id="add_comment">
                                                <i class="fa fa-paper-plane" aria-hidden="true"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <?php include_once('components/com_gm_ceiling/views/project/project_notes.php'); ?>
                        </div>
                    </div>
            </div>
        </div>

    </div>
    <?php include_once('components/com_gm_ceiling/views/project/common_table.php'); ?>
    <?php if (!in_array($this->item->project_status, VERDICT_STATUSES)) { ?>
        <div class="row">
            <div class="col-md-12">
                <button class="btn  btn-primary" id="run_in_production" type="button">
                    Запустить в производство
                </button>
            </div>
        </div>
        <div id="choose_mount" style="display: none;">
            <div class="row">
                <div class="col-md-6 col-xs-12">
                    <div class="row">
                        <h4>Назначить дату готовности полотен</h4>
                        <div class="row" style="padding-bottom: 5px;">
                            <div class="col-md-3 text-left">
                                <b>Все потолки</b>
                            </div>
                            <div class="col-md-3">
                                <input type="checkbox" id="all_calcs" name="runByCallAll" class="inp-cbx"
                                       style="display: none">
                                <label for="all_calcs" class="cbx">
                                            <span>
                                                <svg width="12px" height="10px" viewBox="0 0 12 10">
                                                    <polyline points="1.5 6 4.5 9 10.5 1"></polyline>
                                                </svg>
                                            </span>
                                    <span>По звонку</span>
                                </label>
                            </div>
                            <div class="col-md-6 left">
                                <input type="datetime-local" pattern="[0-9]{4}-[0-9]{2}-[0-9]{2}T[0-9]{2}:[0-9]{2}"
                                       name="date_all_canvas_ready" class="form-control">
                            </div>
                        </div>
                        <?php
                        if (empty($calculations)) {
                            $calculationsModel = Gm_ceilingHelpersGm_ceiling::getModel('calculations');
                            $calculations = $calculationsModel->new_getProjectItems($this->item->id);
                        }
                        foreach ($calculations as $calculation) {
                            $byCall = '';
                            $runDate = '';
                            if ($calculation->run_by_call) {
                                $byCall = 'checked';
                            }
                            if (!empty($calculation->run_date)) {
                                $date = date_create($calculation->run_date);
                                $runDate = date_format($date, "Y-m-d") . 'T' . date_format($date, "h:i");
                            }
                            ?>
                            <div class="row" style="padding-bottom: 5px;">
                                <div class="col-md-3 text-left">
                                    <?php echo $calculation->calculation_title; ?>
                                </div>
                                <div class="col-md-3">
                                    <input type="checkbox" data-calc_id="<?php echo $calculation->id ?>"
                                           id="<?php echo "cid" . $calculation->id ?>" name="runByCall" class="inp-cbx"
                                           style="display: none" <?= $byCall; ?>>
                                    <label for="<?php echo "cid" . $calculation->id ?>" class="cbx">
                                        <span>
                                            <svg width="12px" height="10px" viewBox="0 0 12 10">
                                                <polyline points="1.5 6 4.5 9 10.5 1"></polyline>
                                            </svg>
                                        </span>
                                        <span>По звонку</span>
                                    </label>
                                </div>
                                <div class="col-md-6 left">
                                    <input type="datetime-local"
                                           data-calc_id="<?php echo $calculation->id ?>" name="date_canvas_ready"
                                           class="form-control" value="<?= $runDate ?>">
                                </div>

                            </div>
                        <?php } ?>
                        <div class="row center">
                            <div class="col-md-12">
                                <button class="btn btn-primary" id="btn_ready_date_вave" type="button">Сохранить
                                    готовность
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xs-12">
                    <h4>Назначить дату монтажа, если требуется</h4>
                    <div id="calendar_mount" align="center"></div>
                </div>
            </div>
            <div class="row center">
                <div class="col-md-12">
                    <button id="to_production" class="btn btn-primary">Запустить</button>
                </div>
            </div>
        </div>

        <div class="modal_window_container" id="mw_container">
            <button type="button" class="close_btn" id="close_mw"><i class="fa fa-times fa-times-tar"
                                                                     aria-hidden="true"></i></button>
            <div id="mw_mounts_calendar" class="modal_window">
            </div>
        </div>

    <?php } ?>
<?php
else:
    echo JText::_('COM_GM_CEILING_ITEM_NOT_LOADED');
endif;
?>

<script type="text/javascript" src="/components/com_gm_ceiling/create_calculation.js"></script>
<script type="text/javascript"
        src="/components/com_gm_ceiling/views/project/common_table.js?t=<?php echo time(); ?>"></script>
<script type="text/javascript" src="/components/com_gm_ceiling/date_picker/mounts_calendar.js"></script>
<script type="text/javascript">
    init_mount_calendar('calendar_mount', 'mount', 'mw_mounts_calendar', ['close_mw', 'mw_container']);
    var project_id = "<?php echo $this->item->id; ?>";
    var $ = jQuery;
    var min_project_sum = <?php echo $min_project_sum;?>;
    var min_components_sum = <?php echo $min_components_sum;?>;
    var self_data = JSON.parse('<?php echo $self_calc_data;?>');

    jQuery(document).mouseup(function (e) {// событие клика по веб-документу
        var div1 = jQuery("#mw_mounts_calendar");

        if (!div1.is(e.target) // если клик был не по нашему блоку
            && div1.has(e.target).length === 0) { // и не по его дочерним элементам
            jQuery("#close_mw").hide();
            jQuery("#mw_container").hide();
            div1.hide();

        }
    });

    jQuery(document).ready(function () {

        jQuery("#show_cl_block").click(function () {
            jQuery("#cl_block").toggle();
        });

        var project_card = '<?php echo $project_card; ?>';
        if (project_card != '') {
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
            for (let i = slider_sex.length; i--;) {
                if (slider_sex[i].value == project_card.sex) {
                    slider_sex[i].checked = 'checked';
                }
            }
            let slider_radio = document.getElementsByName('slider-radio');
            for (let i = slider_radio.length; i--;) {
                if (slider_radio[i].value == project_card.type) {
                    slider_radio[i].checked = 'checked';
                }
            }
        }
        //console.log(project_card);

        var hrefs = document.getElementsByTagName("a");
        var regexp = /index\.php\?option=com_gm_ceiling\&task=mainpage/;
        for (var i = 0; i < hrefs.length; i++) {
            if (regexp.test(hrefs[i].href)) {

                hrefs[i].onclick = function () {
                    return false;
                };
                break;
            }
        }
        jQuery("#back_btn").click(function () {
            var client_id = jQuery("#client_id").val();
            if (client_id == 1) {
                if (jQuery("#jform_client_name").val() == "") {
                    jQuery("#jform_client_name").val("Безымянный");
                }
                jQuery("#form-client").submit();
            } else {
                history.back();

            }
        });

        document.onkeydown = function (e) {
            if (e.keyCode === 13) {
                return false;
            }
        };

        document.getElementById('new_comment').onkeydown = function (e) {
            if (e.keyCode === 13) {
                document.getElementById('add_comment').click();
            }
        };

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

        jQuery("#save_project_info").click(function () {
            var street = jQuery('#jform_address').val(),
                house = jQuery('#jform_house').val(),
                bdq = jQuery('#jform_bdq').val(),
                apartment = jQuery('#jform_apartment').val(),
                porch = jQuery('#jform_porch').val(),
                floor = jQuery('#jform_floor').val(),
                code = jQuery('#jform_code').val(),
                address = '';
            if (!empty(house)) address = street + ", дом: " + house;
            if (!empty(bdq)) address += ", корпус: " + bdq;
            if (!empty(apartment)) address += ", квартира: " + apartment;
            if (!empty(porch)) address += ", подъезд: " + porch;
            if (!empty(floor)) address += ", этаж: " + floor;
            if (!empty(code)) address += ", код: " + code;
            var new_data = {id: project_id, project_info: address};
            jQuery.ajax({
                url: "index.php?option=com_gm_ceiling&task=project.change_project_data",
                data: {
                    new_data: JSON.stringify(new_data)
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
                        text: "Адрес сохранен!"
                    });
                },
                error: function (data) {
                    console.log(data);
                    var n = noty({
                        timeout: 2000,
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "error",
                        text: "Ошибка сохранения адреса!"
                    });
                }
            });
        });

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

        function getClientDealerID() {
            var client_id = jQuery("#client_id").val();
            jQuery.ajax({
                type: 'POST',
                url: "index.php?option=com_gm_ceiling&task=getClientDealerId",
                data: {
                    client_id: client_id,
                },
                success: function (data) {
                    result = data;
                },
                async: false,
                dataType: "json",
                timeout: 10000,
                error: function (data) {

                }
            });
            return result;
        }

        jQuery("#add_email").click(function () {
            if (jQuery("#jform_email").val() != "") {
                jQuery.ajax({
                    type: 'POST',
                    url: "index.php?option=com_gm_ceiling&task=addemailtoclient",
                    data: {
                        email: jQuery("#jform_email").val(),
                        client_id: jQuery("#client_id").val()
                    },
                    success: function (data) {
                        console.log(data);
                        jQuery("#emails").val(jQuery("#emails").val() + data + ";");
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

        jQuery("#client_order").click(function () {
            jQuery("input[name='project_verdict']").val(1);
            jQuery("#project_sum").val(<?php echo $project_total_discount?>);
        });

        jQuery("#run_in_production").click(function () {
            jQuery("#project_status").val(5);
            jQuery("#data_change").val(1);
            jQuery("#choose_mount").toggle();
            //jQuery("#form-client").submit();
        });
        jQuery("#change_discount").click(function () {
            jQuery(".new_discount").toggle();

        });

        jQuery("#update_discount").click(function () {
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
                    location.href = '/index.php?option=com_gm_ceiling&view=clientcard&id=' + jQuery("#found_clients").val();
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

        jQuery("#broke").click(function () {
            jQuery("#call_up").toggle();

        });
        jQuery("#add_call_and_submit_up").click(function () {
            client_id = <?php echo $this->item->id_client;?>;
            if (jQuery("#call_date_up").val() == '') {
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
                    add_history(client_id, "Звонок перенесен");
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

        jQuery("#all_calcs").change(function () {
            if (jQuery(this).prop('checked')) {
                jQuery('[name="runByCall"]').prop('checked', true);
                jQuery('[name = "date_all_canvas_ready"]').val("");
                jQuery('[name = "date_canvas_ready"]').val("");
            } else {
                jQuery('[name="runByCall"]').prop('checked', false);
            }
        });
        jQuery('[name = "date_all_canvas_ready"]').focus(function () {
            var date = new Date,
                month = (date.getMonth() < 10) ? "0" + (date.getMonth() + 1) : (date.getMonth() + 1),
                day = (date.getDate() < 10) ? "0" + date.getDate() : date.getDate(),
                value = date.getFullYear() + "-" + month + "-" + day + "T09:00";
            this.value = value;
            jQuery('[name = "date_canvas_ready"]').val(value);
            jQuery("#all_calcs").prop('checked', false);
            jQuery('[name = "runByCall"]').prop('checked', false);
        });
        jQuery('[name = "date_all_canvas_ready"]').change(function () {
            var date_time = this;
            jQuery('[name = "date_canvas_ready"]').val(this.value);
        });

        jQuery('[name = "runByCall"]').change(function () {
            var checkBox = this;
            if (checkBox.checked) {
                jQuery('[name = "date_canvas_ready"]').filter(function () {
                    if (jQuery(this).data("calc_id") == jQuery(checkBox).data("calc_id")) {
                        this.value = "";
                    }
                    ;
                });
                jQuery('[name = "date_all_canvas_ready"]').val("");
            } else {
                jQuery("#all_calcs").prop('checked', false);
            }

        });

        jQuery('[name = "date_canvas_ready"]').focus(function () {
            var date = new Date,
                month = (date.getMonth() < 10) ? "0" + (date.getMonth() + 1) : (date.getMonth() + 1),
                day = (date.getDate() < 10) ? "0" + date.getDate() : date.getDate();
            this.value = date.getFullYear() + "-" + month + "-" + day + "T09:00";
            jQuery('[name = "date_all_canvas_ready"]').val("");
            jQuery('#all_calcs').prop('checked', false);
        });
        jQuery('[name = "date_canvas_ready"]').change(function () {
            var date_time = this;
            jQuery('[name = "runByCall"]').filter(function () {
                if (jQuery(this).data("calc_id") == jQuery(date_time).data("calc_id")) {
                    jQuery(this).attr("checked", false);
                }
                ;
            });
        });

        jQuery("#btn_ready_date_вave").click(function () {
            var readyDates = jQuery('[name = "date_canvas_ready"]').filter(function () {
                    if (this.value) {
                        return this;
                    }
                    ;
                }),
                byCall = jQuery('[name = "runByCall"]:checked'),
                result = [];
            jQuery.each(readyDates, function (index, elem) {
                result.push({calc_id: jQuery(elem).data("calc_id"), ready_time: jQuery(elem).val()});
            });
            jQuery.each(byCall, function (index, elem) {
                result.push({calc_id: jQuery(elem).data("calc_id"), ready_time: "by_call"});
            });
            jQuery.ajax({
                /*index.php?option=com_gm_ceiling&task=project.update_ready_time*/
                url: "index.php?option=com_gm_ceiling&task=calculation.set_ready_time",
                data: {
                    data: JSON.stringify(result)
                },
                dataType: "json",
                async: true,
                success: function (data) {
                    noty({
                        timeout: 2000,
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "success",
                        text: "Время готовности полотен назначено!"
                    });
                },
                error: function (data) {
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
    });

    jQuery('.change_calc').click(function () {
        let id = jQuery(this).data('calc_id');
        save_data_to_session(2, id, jQuery(this));
    });

    jQuery("#add_phone").click(function () {
        var html = "";
        html += "<div class = 'row dop-phone'>";

        html += "<div class='col-md-10'><input name='new_client_contacts[]' id='jform_client_contacts' class='form-control' value=''> </div>";
        html += "<div class='col-md-2'><button class='clear_form_group btn btn-danger' type='button'><i class='fa fa-trash' aria-hidden='true'></i></button></div> ";
        html += "</div>";
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
        } else {
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


</script>
