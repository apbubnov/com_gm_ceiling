<?php
$user = JFactory::getUser();
$userId = $user->get('id');
$user_group = $user->groups;

$clientcardModel = Gm_ceilingHelpersGm_ceiling::getModel('clientcard');
$historyModel = Gm_ceilingHelpersGm_ceiling::getModel('client_history');
$projectModel = Gm_ceilingHelpersGm_ceiling::getModel('project');
$usersModel = Gm_ceilingHelpersGm_ceiling::getModel('users');
$client_model = Gm_ceilingHelpersGm_ceiling::getModel('client');
$callbackModel = Gm_ceilingHelpersGm_ceiling::getModel('callback');
$client_phones_model = Gm_ceilingHelpersGm_ceiling::getModel('client_phones');
$client_dop_contacts_model = Gm_ceilingHelpersGm_ceiling::getModel('clients_dop_contacts');

$history = $historyModel->getDataByClientId($this->item->id);
$projects = $clientcardModel->getProjects($this->item->id);
$jinput = JFactory::getApplication()->input;
$phoneto = $jinput->get('phoneto', '', 'STRING');
$phonefrom = $jinput->get('phonefrom', '', 'STRING');
$call_id = $jinput->get('call_id', 0, 'INT');

$client = $client_model->getClientById($this->item->id);
$dealer_type = JFactory::getUser($client->dealer_id)->dealer_type;

$userClient = $usersModel->getUserByAssociatedClient($client->id);
if (!empty($userClient)) {
    $clientDealerId = $userClient->dealer_id;
} else {
    $clientDealerId = $client->dealer_id;
}
if (!empty($client->manager_id)) {
    $manager_name = JFactory::getUser($client->manager_id)->name;
} else {
    $manager_name = "-";
}
if ($clientDealerId == 1) {
    $subtype = 'calendar';
} else {
    if ($dealer_type == 3 || $dealer_type == 5) {
        $subtype = 'designer';
    } else {
        $subtype = 'production';
    }
}
$isBuilder = $dealer_type == 7; // Застройщик или нет
// контакты клиента
$client_phones = $client_phones_model->getItemsByClientId($this->item->id);
$dop_contacts = $client_dop_contacts_model->getContact($this->item->id);
//-----------------
$group_id = ($user->dealer_id == 1) ? 16 : 13;
$gaugers_group = ($user->dealer_id == 1) ? 22 : 21;

$managers = $usersModel->getUsersByGroupAndDealer($group_id, $user->dealer_id);
$gaugers = $usersModel->getUsersByGroupAndDealer($gaugers_group, $user->dealer_id);
$managers = array_merge($managers, $gaugers);

$closestCallBack = $callbackModel->getClosestCallback($this->item->id,date('Y-m-d'));
$objMaster = in_array('46',$user_group);
$disabled = $objMaster ? 'disabled' : '';
?>

<style>
    body {
        color: #414099;
    }

    .margin{
        margin-bottom: 1em !important;
    }
    .wide_btn{
        width:250px;
    }
</style>

<button id="back_btn" class="btn btn-primary" style="margin-bottom: 1em;"><i class="fa fa-arrow-left"
                                                                             aria-hidden="true"></i> Назад
</button>
<h3 class="center">Карточка клиента</h3>
<div class="row">
    <div class="col-md-2"></div>
    <div class="col-md-6">
        <div class="row">
            <div class="col-md-10 col-xs-10">
                <label id="FIO" style="font-size: 24px;"><?php echo $this->item->client_name; ?></label>
            </div>
            <div class="col-md-2 col-xs-2">
                <button type="button" id="edit" value="" class="btn btn-primary" <?=$disabled?>>
                    <i class="fas fa-pen" aria-hidden="true"></i>
                </button>
            </div>
        </div>
        <div class="row">
            <div class="col-md-10 col-xs-10">
                <label style="font-size: 24px;">Менеджер: <span id="manager_name"><?php echo $manager_name; ?></span></label>
            </div>
            <div class="col-md-2 col-xs-2">
                <?php if (count($managers)) { ?>
                    <button type="button" id="edit_manager" value="" class="btn btn-primary" <?=$disabled?>>
                        <i class="fas fa-edit" aria-hidden="true"></i>
                    </button>
                <?php } ?>
            </div>
        </div>
        <?php if(!empty($closestCallBack->date)){?>
            <div class="row">
                <div class="col-md-12 col-xs-12">
                    <label>
                        Звонок назначен на <?= $closestCallBack->date;?>. Примечание : <?= !empty($closestCallBack->comment) ? $closestCallBack->comment : '-';?>
                    </label>
                </div>

            </div>
        <?php }?>
    </div>
    <div class="col-md-4">
        <?php if ($call_id != 0) { ?>
            <div class="row margin">
                <div class="col-md-12">
                    <button id="broke" type="button" class="btn btn-primary wide_btn">Звонок сорвался,<br> перенести время</button>
                </div>
                <div id="call" class="row call" style="display:none;">
                    <div class="row center">
                        <div class="col-md-12">
                            <label for="call">Перенести звонок</label>
                        </div>
                    </div>
                   <div class="row margin">
                       <div class="col-md-6 col-xs-6">
                           <input name="call_date" id="call_date" type="datetime-local" class="form-control" placeholder="Дата звонка">
                       </div>
                       <div class="col-md-5 col-xs-6">
                           <input name="call_comment" id="call_comment"  class="form-control" placeholder="Примечание">
                       </div>

                   </div>
                    <div class="row center">
                        <div class="col-md-12 col-xs-12">
                            <button class="btn btn-primary" id="add_call_and_submit" type="button">
                                <i class="fas fa-save" aria-hidden="true"></i> Перенести
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        <?php } ?>
        <div class="row margin">
            <div class="col-md-12">
                <button class="btn btn-primary wide_btn" id="grant_access" <?=$disabled?>>
                    Предоставить доступ <br>для онлайн просчета
                </button>
            </div>
        </div>
    </div>


</div>

<!--<div id="FIO-container-tar">

 Кнопка для звонка через Яндекс телефонию, в новой версии у них пока нет API
    <?php /*if ($user->dealer_type != 1) { */?>
        <button class="btn btn-primary btn-sm" type="button" id="but_call"><i class="fa fa-phone"
                                                                              aria-hidden="true"></i></button>
    <?php /*} */?>
    <select id="select_phones" style="display:none;">
        <option value='0' disabled selected>Выберите номер</option>
        <?php /*foreach ($client_phones as $item):*/ ?>
            <option value="<?php /*echo $item->phone;*/ ?>"><?php /* echo $item->phone;*/ ?></option>
        <?php/* endforeach;*/ ?>
    </select>
    <br>


</div>-->




<?php include_once('components/com_gm_ceiling/views/clientcard/buttons_calls_history.php'); ?>
<?php if(!$objMaster) include_once('components/com_gm_ceiling/views/clientcard/labels.php'); ?>
<hr>

<!-- конец -->
<!-- контакты -->
<div class="row">
    <div class="col-md-6 col-xs-12">
        <div class="row">
            <h4 style="text-align: center;">Адреса эл.почты:</h4>
        </div>
        <div class="row">
            <div class="col-md-10 col-xs-10">
                <input type="text" id="new_email" class="form-control" placeholder="Почта">
            </div>
            <div class="col-md-2 col-xs-2">
                <button type="button" id="add_email" class="btn btn-primary">
                    <i class="fas fa-save" aria-hidden="true"></i>
                </button>
            </div>
        </div>

        <?php if (!empty($dop_contacts)) {
            foreach ($dop_contacts AS $contact) { ?>
                <div class="row center">
                    <div class="col-md-12">
                        <?= $contact->contact; ?>
                    </div>
                </div>

            <?php } ?>
        <?php } ?>

    </div>
    <div class="col-md-6 col-xs-12">
        <div class="row">
            <h4 style="text-align: center;">Телефоны клиента:</h4>
        </div>
        <div class="contact_container2">
            <div class="row" style="margin-bottom: 1em;">
                <div class="col-md-10 col-xs-10">
                    <input type="text" id="new_phone" class="form-control" placeholder="Телефон">
                </div>
                <div class="col-md-2 col-xs-2">
                    <button type="button" id="add_phone" class="btn btn-primary" >
                        <i class="fas fa-save" aria-hidden="true"></i>
                    </button>
                </div>
            </div>

            <?php if (!empty($client_phones)) {
                foreach ($client_phones as $item) { ?>
                    <div class="row" style="margin-bottom: 1em;">
                        <div class="col-md-8" style="text-align: right;">
                            <a href="tel:<?php echo '+' . $item->phone; ?>" data-phone="<?=$item->phone;?>"
                               style="font-size: 20px; color: #414099; margin-bottom: 0px;">
                                <?php echo $item->phone; ?>
                            </a>
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-primary make_call"><i class="fas fa-phone-square"></i></button>
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-danger del_phone"><i class="fas fa-trash-alt"></i></button>
                        </div>
                    </div>
                <?php }
            } ?>
        </div>
    </div>
</div>
<!-- стиль исправить не могу, пока не увижу где селект показывается -->
<div class="row margin">
    <div class="col-xs-12" id="calls">
        <p class="caption-tar">История клиента</p>
        <div id="calls-tar">
            <table id="table-calls-tar" class="table table-striped small_table" cellspacing="0">

                <?php foreach ($history as $item): ?>

                    <tr>
                        <td>
                            <?php
                            $date = new DateTime($item->date_time);
                            echo $date->Format('d.m.Y H:i');
                            ?>
                        </td>
                        <td><?php echo $item->text; ?></td>
                    </tr>

                <?php endforeach; ?>

            </table>
        </div>
    </div>
    <div class="col-xs-12" id="add-note-container-tar">
        <div class="col-md-12">
            <label for="comments">Добавить комментарий:</label>
        </div>
        <div class="row center">
            <div class="col-md-11 col-xs-10">
                <input id="new_comment" type="text" class="form-control" placeholder="Введите комментарий">
            </div>
            <div class="col-md-1 col-xs-2">
                <button class="btn btn-primary" type="button" id="add_comment">
                    <i class="fa fa-paper-plane" aria-hidden="true"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<div class="row center">
    <h4>Добавить звонок
        <button class="btn btn-sm btn-primary" type="button" id="btn_open_callback"><i class="fa fa-angle-down"
                                                                                       aria-hidden="true"></i></button>
    </h4>
    <div class="row" id="callback_cont"
         style="display: none; border: 1px solid #414099; border-radius: 4px; padding: 4px;">
        <link rel="stylesheet" href="/components/com_gm_ceiling/date_picker/nice-date-picker.css">
        <script src="/components/com_gm_ceiling/date_picker/nice-date-picker.js"></script>
        <div class="col-md-2 col-xs-0"></div>
        <div class="col-md-4 col-xs-12" style="padding-left: 0px; padding-right: 0px;">
            <label><b>Дата: </b></label><br>
            <div id="calendar-wrapper" style="margin: 0px auto; width: 274px;"></div>
            <script>
                new niceDatePicker({
                    dom: document.getElementById('calendar-wrapper'),
                    mode: 'en',
                    onClickDate: function (date) {
                        document.getElementById('create_call_date').value = date;
                    }
                });
            </script>
        </div>
        <div class="col-md-4 col-xs-12">
            <input type="hidden" id="create_call_date">
            <div class="col-md-12">
                <label><b>Время:</b></label>
            </div>
            <div class="col-md-12">
                <input type="time" class="form-control" id="create_call_time">
            </div>
            <div class="col-md-12">
                <label><b>Примечание:</b></label>
            </div>
            <div class="col-md-12">
                <input type="text" class="form-control" id="create_call_comment" placeholder="Введите примечание">
            </div>
            <?php if (in_array('17', $user_group) || in_array('21', $user_group) || in_array('22', $user_group)) { ?>
                <div class="col-md-12 col-xs-12">
                    <label><b>Менеджер:</b></label>
                </div>
                <div class="col-md-12 col-xs-12">
                    <select class="form-control" id="select_manager_for_call">
                        <option value="0">Выберите менеджера</option>
                        <?php foreach ($managers as $manager) { ?>
                            <option value="<?= $manager->id; ?>"><?= $manager->name; ?></option>
                        <?php } ?>
                    </select>
                </div>
            <?php } ?>
            <div class="col-md-12 col-xs-12" style="margin-top: 10px;">
                <input type="checkbox" id="important_call" class="inp-cbx" style="display: none">
                <label for="important_call" class="cbx">
                <span>
                    <svg width="12px" height="10px" viewBox="0 0 12 10">
                        <polyline points="1.5 6 4.5 9 10.5 1"></polyline>
                    </svg>
                </span>
                    <span>Важный звонок</span>
                </label>
            </div>
            <div class="col-md-12 col-xs-12" style="margin-top: 10px;">
                <button class="btn btn-primary" id="new_add_call" type="button">
                    <i class="fas fa-save" aria-hidden="true"></i> Сохранить
                </button>
            </div>
        </div>
    </div>
</div>
<!-- конец -->
<!-- заказы -->
<div id="orders-container-tar">
    <h4>Заказы</h4>
    <table class="small_table table-striped table_cashbox one-touch-view g_table" id="table_projects">
        <thead>
        <tr>
            <th>Номер</th>
            <th>Дата</th>
            <th>Сумма</th>
            <th>Примечание</th>
            <th>Статус</th>
            <th></th>
        </tr>
        </thead>
        <?php foreach ($projects as $item):
            if ($isBuilder) {
                $item->notes = $projectModel->getProjectNotes($item->id, 1);
            }
            ?>
            <tr class="row_project" data-proj_id="<?php echo $item->id; ?>" data-href="
                <?php
            if ($user->dealer_type == 1) {
                if ($item->project_status == 0) {
                    echo JRoute::_('index.php?option=com_gm_ceiling&view=project&type=calculator&subtype=precalc&id=' . (int)$item->id);
                } elseif ($item->project_status == 1 || $item->project_status == 4) {
                    echo JRoute::_('index.php?option=com_gm_ceiling&view=project&type=calculator&subtype=calendar&id=' . (int)$item->id);
                } elseif ($item->project_status == 3 || $item->project_status == 2 || $item->project_status == 15) {
                    echo JRoute::_('index.php?option=com_gm_ceiling&view=project&type=calculator&subtype=refused&id=' . (int)$item->id);
                } else {
                    echo JRoute::_('index.php?option=com_gm_ceiling&view=project&type=calculator&subtype=project&id=' . (int)$item->id);
                }
            } else {
                if ($user->dealer_type == 8) {
                    echo JRoute::_('index.php?option=com_gm_ceiling&view=project&type=calculator&subtype=calendar&id=' . (int)$item->id);
                } else {
                    echo JRoute::_('index.php?option=com_gm_ceiling&view=project&type=gmmanager&subtype=' . $subtype . '&id=' . (int)$item->id . '&call_id=' . (int)$call_id);
                }
            }
            ?>
            ">
                <td>
                    <?php echo ($isBuilder) ? $item->project_info : $item->id; ?>
                </td>
                <td>
                    <?php
                    $date = new DateTime($item->created);
                    echo $date->Format('d.m.Y');
                    ?>
                </td>
                <td>
                    <?php
                    echo $item->project_sum; ?>
                </td>
                <td>
                    <?php
                    if ($isBuilder) {
                        echo $item->notes[0]->note;
                    } else {
                        $note = '';
                        if ($item->project_status < 3 || $item->project_status == 15) {
                            if (!empty($item->gm_manager_note)) {
                                $note .= $item->gm_manager_note . '<br>';
                            }
                            if (!empty($item->dealer_manager_note)) {
                                $note .= $item->dealer_manager_note . '<br>';
                            }
                            if (!empty($item->project_note)) {
                                $note .= $item->project_note . '<br>';
                            }
                        } elseif ($item->project_status == 3 || $item->project_status == 4) {
                            if (!empty($item->gm_calculator_note)) {
                                $note .= $item->gm_calculator_note . '<br>';
                            }
                            if (!empty($item->dealer_calculator_note)) {
                                $note .= $item->dealer_calculator_note . '<br>';
                            }
                        } elseif ($item->project_status > 4 || $item->project_status < 11) {
                            if (!empty($item->gm_chief_note)) {
                                $note .= $item->gm_chief_note . '<br>';
                            }
                            if (!empty($item->dealer_chief_note)) {
                                $note .= $item->dealer_chief_note . '<br>';
                            }
                        } else {
                            if (!empty($item->gm_mount_note)) {
                                $note .= $item->gm_mount_note . '<br>';
                            }
                            if (!empty($item->mount_note)) {
                                $note .= $item->mount_note . '<br>';
                            }
                        }
                    }
                    echo $note;
                    ?>
                </td>
                <td>
                    <?php echo $item->status; ?>
                </td>
                <td>
                    <button class="btn btn-danger btn-sm btn_del_proj" type="button" data-id="<?php echo $item->id; ?>" <?=$disabled;?>>
                        <i class="fas fa-trash-alt" aria-hidden="true"></i></button>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
    <div id="add-gauging-container-tar">
        <button type="button" id="add_new_project" class="btn btn-primary"><i class="fa fa-plus"></i> Заказ</button>
        <button type="button" id="add_new_calc"
                class="btn btn-primary" <?php if ($user->dealer_type == 8) echo "hidden"; ?>><i class="fa fa-plus"></i>
            Просчет
        </button>
    </div>
</div>
<!-- модальное окно -->
<div class="modal_window_container" id="mw_container">
    <button type="button" id="btn_close" class="btn-close">
        <i class="fa fa-times fa-times-tar" aria-hidden="true"></i>
    </button>
    <div id="mw_call" class="modal_window">
        <?php
            $clientNameArr = explode(' ',$client->client_name);
            $surname = '';
            $name = '';
            $patr = '';
            if(count($clientNameArr) == 3){
                $surname = $clientNameArr[0];
                $name = $clientNameArr[1];
                $patr = $clientNameArr[2];
            }
            else{ $surname = $client->client_name;}

        ?>
        <div class="row center" style="margin-bottom: 15px;">
            Изменение ФИО клиента
        </div>
        <div class="row" style="margin-bottom: 15px;">
            <div class="col-md-4">
                <div class="col-md-3">
                    <label for="new_surname">Фамилия</label>
                </div>
                <div class="col-md-9">
                    <input type="text" class="form-control" id="new_surname" value="<?=$surname;?>">
                </div>
            </div>
            <div class="col-md-4">
                <div class="col-md-3">
                    <label for="new_name">Имя</label>
                </div>
                <div class="col-md-9">
                    <input type="text" class="form-control" id="new_name" value="<?=$name;?>">
                </div>
            </div>
            <div class="col-md-4">
                <div class="col-md-3">
                    <label for="new_patronymic">Отчество</label>
                </div>
                <div class="col-md-9">
                    <input type="text" class="form-control" id="new_patronymic" value="<?=$patr;?>">
                </div>
            </div>

        </div>

        <p>
            <button type="button" id="update_fio" class="btn btn-primary">Сохранить</button>
            <button type="button" id="cancel_fio" class="btn btn-primary">Отмена</button>
        </p>
    </div>
    <div id="mw_change_manager" class="modal_window">
        <div class="row margin">
            <span style="font-size: 20px;">Изменение менеджера</span>
        </div>
        <div class="row margin">
            <div class="col-md-4"></div>
            <div class="col-md-4">
                <select id="new_manager" class="form-control">
                    <?php foreach ($managers as $manager) { ?>
                        <option value= <?php echo $manager->id; ?>><?php echo $manager->name; ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="col-md-4"></div>
        </div>
        <div class="row">
            <div class="col-md-4"></div>
            <div class="col-md-4">
                <div class="col-md-6 col-xs-6">
                    <button type="button" id="save_new_manager" class="btn btn-primary">Сохранить</button>
                </div>
                <div class="col-md-6 col-xs-6">
                    <button type="button" class="btn btn-primary cancel" id="cancel_manager">Отмена</button>
                </div>
            </div>
            <div class="col-md-4"></div>
        </div>
    </div>
</div>

<script>
    var client_id = '<?php echo $this->item->id;?>';
    jQuery(document).mouseup(function (e) { // событие клика по веб-документу
        var div = jQuery("#mw_call"), // тут указываем ID элемента
            div1 = jQuery("#mw_change_manager");
        if (!div.is(e.target) // если клик был не по нашему блоку
            && div.has(e.target).length === 0
            && !div1.is(e.target) // если клик был не по нашему блоку
            && div1.has(e.target).length === 0) { // и не по его дочерним элементам
            jQuery("#btn_close").hide();
            jQuery("#mw_container").hide();
            div.hide();
            div1.hide();
        }
    });

    jQuery("#edit_manager").click(function () {
        jQuery("#btn_close").show();
        jQuery("#mw_container").show();
        jQuery("#mw_change_manager").show("slow");
    });

    jQuery("#cancel_fio").click(function () {
        jQuery("#btn_close").hide();
        jQuery("#mw_container").hide();
        jQuery("#mw_call").hide();
    });

    jQuery("#cancel_manager").click(function () {
        jQuery("#btn_close").hide();
        jQuery("#mw_container").hide();
        jQuery("#mw_change_manager").hide();
    });


    jQuery("#update_fio").click(function () {
        var surname = jQuery('#new_surname').val(),
            name = jQuery('#new_name').val(),
            patronymic = jQuery('#new_patronymic').val(),
            fio = '';
        if (!empty(new_surname)) {
            fio += surname;
        }
        if (!empty(name)) {
            if (!empty(fio)) {
                fio += ' ' + name;
            } else {
                fio += name;
            }
        }
        if (!empty(patronymic)) {
            if (!empty(fio)) {
                fio += ' ' + patronymic;
            } else {
                fio += patronymic;
            }
        }
        if (!empty(fio)) {
            jQuery.ajax({
                type: 'POST',
                url: "index.php?option=com_gm_ceiling&task=updateClientFIO",
                data: {
                    client_id: client_id,
                    fio: fio
                },
                success: function (data) {
                    jQuery("#FIO").text(data);
                    jQuery('#new_surname').val('');
                    jQuery('#new_patronymic').val('');
                    jQuery('#new_name').val('');
                    jQuery("#btn_close").hide();
                    jQuery("#mw_container").hide();
                    jQuery("#mw_call").hide();
                    var n = noty({
                        theme: 'relax',
                        timeout: 2000,
                        layout: 'topCenter',
                        maxVisible: 5,
                        type: "success",
                        text: "ФИО обновлено!"
                    });
                },
                dataType: "text",
                timeout: 10000,
                error: function (data) {
                    console.log(data);
                    var n = noty({
                        theme: 'relax',
                        timeout: 2000,
                        layout: 'topCenter',
                        maxVisible: 5,
                        type: "error",
                        text: "Ошибка!"
                    });
                }
            });
        } else {
            noty({
                theme: 'relax',
                timeout: 2000,
                layout: 'topCenter',
                maxVisible: 5,
                type: "error",
                text: "Не введено имя клиента!"
            });
        }

    });
    jQuery("#save_new_manager").click(function () {
        console.log(jQuery("#new_manager").val());
        jQuery.ajax({
            type: 'POST',
            url: "index.php?option=com_gm_ceiling&task=client.updateManager",
            data: {
                client_id: client_id,
                manager_id: jQuery("#new_manager").val()
            },
            success: function (data) {
                jQuery("#btn_close").hide();
                jQuery("#mw_container").hide();
                jQuery("#mw_change_manager").hide;
                jQuery("#manager_name")[0].innerHTML = data;
            },
            dataType: "json",
            timeout: 10000,
            error: function (data) {
                console.log(data);
                var n = noty({
                    theme: 'relax',
                    timeout: 2000,
                    layout: 'topCenter',
                    maxVisible: 5,
                    type: "error",
                    text: "Ошибка!"
                });
            }
        });
    });
    document.getElementById('btn_open_callback').onclick = function () {
        var cont = document.getElementById('callback_cont');
        if (cont.style.display == 'block') {
            cont.style.display = 'none';
        } else {
            cont.style.display = 'block';
        }
    }

    jQuery("#new_add_call").click(function () {
        if (jQuery("#create_call_date").val() == '') {
            var n = noty({
                timeout: 2000,
                theme: 'relax',
                layout: 'topCenter',
                maxVisible: 5,
                type: "warning",
                text: "Укажите дату перезвона"
            });
            return;
        }
        var date = jQuery("#create_call_date").val().replace(/(-)([\d]+)/g, function (str, p1, p2) {
            if (p2.length === 1) {
                return '-0' + p2;
            } else {
                return str;
            }
        }),
            time = jQuery("#create_call_time").val(),
            important = jQuery('#important_call').is(':checked') ? 1 : 0;
        if (time == '') {
            time = '00:00';
        }
        jQuery.ajax({
            url: "index.php?option=com_gm_ceiling&task=addCall",
            data: {
                id_client: client_id,
                date: date + ' ' + time,
                comment: jQuery("#create_call_comment").val(),
                old_call: '<?php echo $call_id;?>',
                manager_id: jQuery("#select_manager_for_call").val(),
                important: important
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
                    text: "Звонок добавлен"
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

    jQuery("#edit").click(function () {
        jQuery("#btn_close").show();
        jQuery("#mw_container").show();
        jQuery("#mw_call").show("slow");
    });
    jQuery('body').on('click', '.row_project', function (e) {
        if (jQuery(this).data('href') !== undefined) {
            document.location.href = jQuery(this).data('href');
        }
    });

    jQuery("#add_new_project").click(function () {
        jQuery.ajax({
            type: 'POST',
            url: "index.php?option=com_gm_ceiling&task=create_empty_project",
            data: {
                client_id:<?php echo $this->item->id;?>

            },
            success: function (data) {
                data = JSON.parse(data);
                var pt = "<?php echo $phoneto; ?>";
                var pf = "<?php echo $phonefrom; ?>";
                var call_id = <?php echo $call_id; ?>;
                var subtype = "<?php echo $subtype; ?>";
                if (pt === "" || pf === "") {
                    if (call_id === 0) {
                        url = '/index.php?option=com_gm_ceiling&view=project&type=gmmanager&subtype=' + subtype + '&id=' + data + '&call_id=0';
                    } else {
                        url = '/index.php?option=com_gm_ceiling&view=project&type=gmmanager&subtype=' + subtype + '&id=' + data + '&call_id=' + call_id;
                    }
                } else {
                    url = '/index.php?option=com_gm_ceiling&view=project&type=gmmanager&subtype=' + subtype + '&id=' + data + '&phoneto=' + pt + '&phonefrom=' + pf;
                }
                <?php if($user->dealer_type == 1 || $user->dealer_type == 8) {?>
                url = '/index.php?option=com_gm_ceiling&view=project&type=manager&subtype=calendar' + '&id=' + data;
                <?php }?>
                location.href = url;
            },
            dataType: "text",
            timeout: 10000,
            error: function (data) {
                var n = noty({
                    timeout: 2000,
                    theme: 'relax',
                    layout: 'center',
                    maxVisible: 5,
                    type: "error",
                    text: "Ошибка при создании заказа. Сервер не отвечает"
                });
            }
        });
    });


    jQuery(document).ready(function () {
        jQuery('#new_phone').mask('+7(999) 999-9999');
        document.getElementById('calls-tar').scrollTop = 9999;
        var client_id = <?php echo $client->id; ?>;

        document.getElementById('add_email').onclick = function () {
            if (!(/^[A-Za-z\d\-\_\.]+\@{1}[A-Za-z\d\-\_]+\.[A-Za-z\d]+$/).test(document.getElementById('new_email').value)) {
                noty({
                    timeout: 2000,
                    theme: 'relax',
                    layout: 'topCenter',
                    maxVisible: 5,
                    type: "warning",
                    text: "Email не заполнен или имеет неверный формат"
                });
                document.getElementById('new_email').focus();
                return;
            }
            jQuery.ajax({
                url: "index.php?option=com_gm_ceiling&task=addemailtoclient",
                data: {
                    client_id: client_id,
                    email: document.getElementById('new_email').value
                },
                dataType: "json",
                async: false,
                success: function (data) {
                    location.reload();
                },
                error: function (data) {
                    console.log(data);
                    var n = noty({
                        timeout: 2000,
                        theme: 'relax',
                        layout: 'topCenter',
                        maxVisible: 5,
                        type: "error",
                        text: "Ошибка сервера"
                    });
                }
            });
        };

        document.getElementById('add_new_calc').onclick = function () {
            jQuery.ajax({
                url: "index.php?option=com_gm_ceiling&task=create_project_and_calc",
                data: {
                    client_id: client_id
                },
                dataType: "json",
                async: false,
                success: function (data) {
                    console.log(data);
                    <?php
                    if (in_array("16", $user_group)) {
                        echo "var url = '/index.php?option=com_gm_ceiling&view=calculationform&type=gmmanager&subtype=calendar&calc_id=';";
                    } else {
                        echo "var url = '/index.php?option=com_gm_ceiling&view=calculationform&type=calculator&subtype=precalc&calc_id=';";
                    }
                    ?>
                    location.href = url + data;
                },
                error: function (data) {
                    console.log(data);
                    var n = noty({
                        timeout: 2000,
                        theme: 'relax',
                        layout: 'topCenter',
                        maxVisible: 5,
                        type: "error",
                        text: "Ошибка сервера"
                    });
                }
            });
        };

        jQuery('.btn_del_proj').click(function () {
            var project_id = jQuery(this).data('id');
            noty({
                theme: 'relax',
                layout: 'center',
                timeout: false,
                type: "info",
                text: "Вы действительно хотите удалить проект?",
                buttons: [
                    {
                        addClass: 'btn btn-primary', text: 'Удалить', onClick: function ($noty) {
                            jQuery.ajax({
                                url: "index.php?option=com_gm_ceiling&task=project.delete_by_user",
                                data: {
                                    project_id: project_id
                                },
                                dataType: "json",
                                async: true,
                                success: function (data) {
                                    jQuery('.row_project[data-proj_id="' + project_id + '"]')[0].remove();
                                },
                                error: function (data) {
                                    console.log(data);
                                    var n = noty({
                                        timeout: 2000,
                                        theme: 'relax',
                                        layout: 'topCenter',
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
                        addClass: 'btn btn-primary', text: 'Отмена', onClick: function ($noty) {
                            $noty.close();
                        }
                    }
                ]
            });

            return false;
        });

        jQuery('#grant_access').click(function () {
            var client_id = '<?= $this->item->id; ?>',
                phones = JSON.parse('<?=json_encode($client_phones); ?>'),
                dop_contacts = JSON.parse('<?= json_encode($dop_contacts); ?>'),
                phone, email;
            if (!empty(phones)) {
                phone = phones[0].phone;
            } else {
                var n = noty({
                    timeout: 2000,
                    theme: 'relax',
                    layout: 'topCenter',
                    maxVisible: 5,
                    type: "error",
                    text: "У клиента отсутствует номер телефона!"
                });
                return;
            }
            if (!empty(dop_contacts)) {
                email = dop_contacts[0].contact;
            } else {
                email = client_id + "@" + client_id;
            }
            jQuery.ajax({
                url: "index.php?option=com_gm_ceiling&task=users.registerClient",
                data: {
                    client_id: client_id,
                    phone: phone,
                    email: email
                },
                dataType: "json",
                async: true,
                success: function (data) {
                    if (data.type != 'error') {
                        var n = noty({
                            timeout: 2000,
                            theme: 'relax',
                            layout: 'topCenter',
                            maxVisible: 5,
                            type: "success",
                            text: "Доступ предоставлен!Логин и пароль совпадают с первым номером телефона клиента!"
                        });
                    } else {
                        var n = noty({
                            timeout: 2000,
                            theme: 'relax',
                            layout: 'topCenter',
                            maxVisible: 5,
                            type: "error",
                            text: data.text
                        });
                    }
                },
                error: function (data) {
                    console.log(data);
                    var n = noty({
                        timeout: 2000,
                        theme: 'relax',
                        layout: 'topCenter',
                        maxVisible: 5,
                        type: "error",
                        text: "Ошибка сервера"
                    });
                }
            });
        });

        jQuery('.del_phone').click(function(){
            var row = jQuery(this).closest('.row'),
                phone = row.find('a').data('phone'),
                client_id = '<?php echo $this->item->id; ?>';
            noty({
                theme: 'relax',
                layout: 'center',
                timeout: false,
                type: "info",
                text: "Вы действительно хотите удалить номер телефона?",
                buttons: [
                    {
                        addClass: 'btn btn-primary', text: 'Удалить', onClick: function ($noty) {
                            jQuery.ajax({
                                url: "index.php?option=com_gm_ceiling&task=client.deletePhone",
                                data: {
                                    client_id: client_id,
                                    phone: phone
                                },
                                dataType: "json",
                                async: true,
                                success: function (data) {
                                    row.remove();
                                },
                                error: function (data) {
                                    console.log(data);
                                    var n = noty({
                                        timeout: 2000,
                                        theme: 'relax',
                                        layout: 'topCenter',
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
                        addClass: 'btn btn-primary', text: 'Отмена', onClick: function ($noty) {
                            $noty.close();
                        }
                    }
                ]
            });
        });

        jQuery('.del_phone').click(function(){
            var row = jQuery(this).closest('.row'),
                phone = row.find('a').data('phone'),
                client_id = '<?php echo $this->item->id; ?>';
            jQuery.ajax({
                url: "index.php?option=com_gm_ceiling&task=client.deletePhone",
                data: {
                    client_id: client_id,
                    phone: phone
                },
                dataType: "json",
                async: true,
                success: function (data) {
                    row.remove();
                },
                error: function (data) {
                    console.log(data);
                    var n = noty({
                        timeout: 2000,
                        theme: 'relax',
                        layout: 'topCenter',
                        maxVisible: 5,
                        type: "error",
                        text: "Ошибка сервера"
                    });
                }
            });
        });

        jQuery('.make_call').click(function(){
            var row = jQuery(this).closest('.row'),
                phone = row.find('a').data('phone'),
                client_id = '<?php echo $this->item->id; ?>';
            jQuery.ajax({
                url: "/telephony_zadarma.php",
                data: {
                    dataForCall: {phone: phone,clientId: client_id}
                },
                dataType: "json",
                async: true,
                success: function (data) {
                    console.log(data);
                },
                error: function (data) {
                    console.log(data);
                    var n = noty({
                        timeout: 2000,
                        theme: 'relax',
                        layout: 'topCenter',
                        maxVisible: 5,
                        type: "error",
                        text: "Ошибка сервера"
                    });
                }
            });
        });
    });

    jQuery("#back_btn").click(function () {
        history.go(-1);
    });

    jQuery("#add_comment").click(function () {
        var comment = jQuery("#new_comment").val();
        var reg_comment = /[\\\<\>\/\'\"\#]/;
        var id_client = <?php echo $this->item->id; ?>;

        if (reg_comment.test(comment) || comment === "") {
            alert('Неверный формат примечания!');
            return;
        }

        add_history(id_client, comment);
    });

    jQuery("#but_call").click(function () {
        document.getElementById('select_phones').style.display = 'block';
    });

    jQuery("#select_phones").change(function () {
        var id_client = <?php echo $this->item->id; ?>;
        call(jQuery("#select_phones").val());
        add_history(id_client, "Исходящий звонок на " + jQuery("#select_phones").val().replace('+', ''));
    });
    jQuery("#broke").click(function () {
        jQuery("#call").toggle();

    })
    jQuery("#add_call_and_submit").click(function () {
        client_id = <?php echo $this->item->id;?>;
        jQuery.ajax({
            url: "index.php?option=com_gm_ceiling&task=changeCallTime",
            data: {
                id:<?php echo $call_id;?>,
                date: jQuery("#call_date").val(),
                comment: jQuery("#call_comment").val()
            },
            dataType: "json",
            async: true,
            success: function (data) {
                if (jQuery("#call_date").val() == '') {
                    add_history(client_id, 'Звонок перенесен на 30 мин.');
                } else {
                    add_history(client_id, 'Звонок перенесен на ' + jQuery("#call_date").val().replace('T', ' ') + ':00');
                }
                var n = noty({
                    timeout: 2000,
                    theme: 'relax',
                    layout: 'topCenter',
                    maxVisible: 5,
                    type: "success",
                    text: "Звонок сдвинут"
                });

            },
            error: function (data) {
                console.log(data);
                var n = noty({
                    timeout: 2000,
                    theme: 'relax',
                    layout: 'topCenter',
                    maxVisible: 5,
                    type: "error",
                    text: "Ошибка сервера"
                });
            }
        });
    })

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
                    layout: 'topCenter',
                    maxVisible: 5,
                    type: "success",
                    text: "Добавленна запись в историю клиента!"
                });
                jQuery('#table-calls-tar').append('<tr><td>'+formatDate(new Date(),1)+'</td><td>'+comment+'</td></tr>');
                jQuery('#new_comment').val('');

            },
            error: function (data) {
                console.log(data);
                var n = noty({
                    timeout: 2000,
                    theme: 'relax',
                    layout: 'topCenter',
                    maxVisible: 5,
                    type: "error",
                    text: "Ошибка отправки"
                });
            }
        });
    }

    document.getElementById('add_phone').onclick = function () {
        var client_id = <?php echo $client->id; ?>;
        if (document.getElementById('new_phone').value == '') {
            noty({
                timeout: 2000,
                theme: 'relax',
                layout: 'topCenter',
                maxVisible: 5,
                type: "warning",
                text: "Заполните номер"
            });
            document.getElementById('new_phone').focus();
            return;
        }
        jQuery.ajax({
            url: "index.php?option=com_gm_ceiling&task=client.addPhone",
            data: {
                client_id: client_id,
                phone: document.getElementById('new_phone').value
            },
            dataType: "json",
            async: false,
            success: function (data) {
                location.reload();
            },
            error: function (data) {
                console.log(data);
                var n = noty({
                    timeout: 2000,
                    theme: 'relax',
                    layout: 'topCenter',
                    maxVisible: 5,
                    type: "error",
                    text: "Ошибка сервера"
                });
            }
        });
    }
</script>
