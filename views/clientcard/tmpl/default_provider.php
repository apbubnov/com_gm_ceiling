<?php

JHtml::_('formbehavior.chosen', 'select');

$user       = JFactory::getUser();
$userId     = $user->get('id');
$user_group = $user->groups;

$clientcardModel = Gm_ceilingHelpersGm_ceiling::getModel('clientcard');
$historyModel = Gm_ceilingHelpersGm_ceiling::getModel('client_history');
$history = $historyModel->getDataByClientId($this->item->id);
$projects = $clientcardModel->getProjects($this->item->id);
$app = JFactory::getApplication();
$jinput = $app->input;
$phoneto = $jinput->get('phoneto', '', 'STRING');
$phonefrom = $jinput->get('phonefrom', '', 'STRING');
$call_id = $jinput->get('call_id', 0, 'INT');
$client_model = Gm_ceilingHelpersGm_ceiling::getModel('client');
$dealer_info_model = Gm_ceilingHelpersGm_ceiling::getModel('dealer_info');
$dealer_city =  $dealer_info_model->getDataById($this->item->dealer_id)->city;
$client = $client_model->getClientById($this->item->id);
$clients_model = Gm_ceilingHelpersGm_ceiling::getModel('clients');
$clients_items = $clients_model->getDealersClientsListQuery($client->dealer_id, $this->item->id);
$dealer = JFactory::getUser($client->dealer_id);
if ($dealer->associated_client != $this->item->id)
{
    throw new Exception("this is not dealer", 403);
}


if(!empty($client->manager_id)){
    $manager_name = JFactory::getUser($client->manager_id)->name;
}
else{
    $manager_name = "-";
}
$client_phones_model = Gm_ceilingHelpersGm_ceiling::getModel('client_phones');
$client_phones = $client_phones_model->getItemsByClientId($this->item->id);
$client_dop_contacts_model = Gm_ceilingHelpersGm_ceiling::getModel('clients_dop_contacts');
$dop_contacts = $client_dop_contacts_model->getContact($this->item->id);

$status_model = Gm_ceilingHelpersGm_ceiling::getModel('statuses');
$status = $status_model->getData();


/*Dealer history*/
$recoil_map_project_model = Gm_ceilingHelpersGm_ceiling::getModel('recoil_map_project');
$dealer_history = $recoil_map_project_model->getData($client->dealer_id);
$dealer_history_sum = 0;
foreach ($dealer_history as $key => $item) {
    $dealer_history[$key]->data = date("d.m.Y H:i", strtotime($item->date_time));
    $dealer_history_sum += $item->sum;
}
$usersModel = Gm_ceilingHelpersGm_ceiling::getModel('users');
$managers = $usersModel->getUserByGroup(47);
?>

<button id="back_btn" class="btn btn-primary"><i class="fa fa-arrow-left" aria-hidden="true"></i> Назад</button>
<div class="row" id="FIO-container-tar">
    <div class="col-md-4"></div>
    <div class="col-md-4">
        <div class="row">
            <div class="col-md-10 col-xs-10">
                <label id = "FIO"><?php echo $this->item->client_name; ?></label>
            </div>
            <div class="col-md-2 col-xs-2">
                <button type="button" id="edit" value="" class = "btn btn-primary">
                    <i class="fas fa-pen" aria-hidden="true"></i>
                </button>
            </div>
        </div>
        <div class="row">
            <div class="col-md-10 col-xs-10">
                <label>Менеджер: <span id="manager_name"><?php echo $manager_name;?></span></label>
            </div>
            <div class="col-md-2 col-xs-2">
                <button type="button" id="edit_manager" value="" class = "btn btn-primary">
                    <i class="fas fa-pen" aria-hidden="true"></i>
                </button>
            </div>
        </div>
        <div class="row">
            <div class="col-md-10 col-xs-10">
                <label>Город: <?php echo $dealer_city;?></label>
            </div>
            <div class="col-md-2 col-xs-2">
                <button class="btn btn-primary" type="button" id="btn_city">
                    <i class="fas fa-pen" aria-hidden="true"></i>
                </button>
            </div>
        </div>
    </div>
    <div class="col-md-4"></div>
</div>

<table class = "actions">
    <tr>
        <td class = "td-left">
            <button class="btn btn-primary" type="button" id="but_login">Предоставить доступ</button>
        </td>
    </tr>
    <tr>
        <td class = "td-right">
            <button id = "new_send_email" type = "button" class = "btn btn-primary">Написать e-mail</button>
        </td>
    </tr>

</table>
<?php include_once('components/com_gm_ceiling/views/clientcard/buttons_calls_history.php'); ?>
<?php include_once('components/com_gm_ceiling/views/clientcard/labels.php'); ?>
<div class="row" style="margin-top: 10px;">
    <div class="col-md-6 col-xs-12">
        <div class="row">
            <div class="col-md-5 caption-tar" style="font-size: 20px; color: #414099; text-align: left; margin-bottom: 0px;">
                Адреса эл.почты:
            </div>
            <div class="col-md-5">
                <input type="text" id="new_email" class="form-control" placeholder="Почта" required>
            </div>
            <div class="col-md-2">
                <button type="button" id="add_email" class="btn btn-primary">Добавить</button>
            </div>
        </div>
        <?php if (!empty($dop_contacts)) { ?>
            <? foreach ($dop_contacts AS $contact) {?>
                <div class="row">
                    <div class="col-md-10" style="font-size: 20px; color: #414099; text-align: left; margin-bottom: 0px;">
                        <? echo $contact->contact;?>
                    </div>
                    <div class="col-md-2">
                        <button name ="rm_email" class = "btn btn-danger" email="<? echo $contact->contact;?>"><i class="fa fa-trash" aria-hidden="true"></i></button>
                    </div>
                </div>
            <?php }?>
        <? } ?>
    </div>
    <div class="col-md-6 col-xs-12">
        <div class="row">
            <div class="col-md-5 caption-tar" style="font-size: 20px; color: #414099; margin-bottom: 0px;">
                Номера телефона:
            </div>
            <div class="col-md-5">
                <input type="text" id="new_phone" placeholder="Телефон" class="form-control" required>
            </div>
            <div class="col-md-2">
                <button type="button" id="add_phone" class="btn btn-primary">Добавить</button>
            </div>
        </div>
        <?php foreach($client_phones as $item) { ?>
            <div class="row center"  style="font-size: 20px; color: #414099; margin-bottom: 0px;">
                <div class="col-md-12">
                    <? echo $item->phone; ?>
                </div>
            </div>
        <?php } ?>
    </div>
</div>
<div class="row">
    <div class="col-sm-12" id = "calls">
        <p class="caption-tar">История дилера</p>
        <div id="calls-tar">
            <table id="table-calls-tar" class="table table-striped one-touch-view" cellspacing="0">
                <?php foreach($history as $item): ?>
                    <tr>
                        <td>
                            <?php
                            $date = new DateTime($item->date_time);
                            echo $date->Format('d.m.Y H:i');
                            ?>
                        </td>
                        <td><?php echo $item->text;?></td>
                    </tr>

                <?php endforeach;?>

            </table>
        </div>
    </div>

    <div class="col-xs-12" id="add-note-container-tar">
        <div class="col-md-4 col-xs-12">
            <label for="comments">Добавить комментарий:</label>
        </div>
        <div class="col-md-6 col-xs-10">
            <input id="new_comment" type="text" class="form-control" placeholder ="Введите новый комментарий">
        </div>
        <div class="col-md-2 col-xs-2">
            <button class = "btn btn-primary" type = "button" id="add_comment"><i class="fa fa-paper-plane" aria-hidden="true"></i></button>
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
<div id="orders-container-tar">
    <p class="caption-tar">Товары </p>
    <table id="table-orders-tar" class="table table-striped one-touch-view g_table">
        <tr>
            <td>Артикул</td>
            <td>Название</td>
            <td>Дата создания</td>
        </tr>

        <?php foreach($goods as $item):?>

            <tr class = "row_project" data-href="<?php echo JRoute::_('index.php?option=com_gm_ceiling&view=project&type=gmmanager&subtype=production&id='.(int) $item->id.'&call_id='.(int) $call_id); ?>">
                <td><?php echo $item->id;?></td>
                <td>
                    <?php
                    $date = new DateTime($item->created);
                    echo $date->Format('d.m.Y');
                    ?>
                </td>
                <td><?php echo $item->name;?></td>
            </tr>

        <?php endforeach;?>

    </table>
</div>
<div id="mv_container" class="modal_window_container">
    <button type="button" id="close" class="close_btn"><i class="fa fa-times fa-times-tar" aria-hidden="true"></i></button>
    <div id="modal_window_fio" class="modal_window">
        <h4>Изменение ФИО/названия</h4>
        <h6>Введите новое ФИО/название поставщика</h6>
        <div class="row" style="margin-bottom: 10px;">
            <div class="col-md-3"></div>
            <div class="col-md-6">
                <input type="text" id="new_fio" class="form-control" placeholder="ФИО" required>
            </div>
            <div class="col-md-3"></div>
        </div>
        <div class="row">
            <div class="col-md-4"></div>
            <div class="col-md-4">
                <div class="col-md-2"></div>
                <div class="col-md-4">
                    <button type="button" id="update_fio" class="btn btn-primary">Сохранить</button>
                </div>
                <div class="col-md-4">
                    <button type="button" id="cancel" class="btn btn-primary btn_cancel">Отмена</button>
                </div>
                <div class="col-md-2"></div>
            </div>
            <div class="col-md-4"></div>
        </div>
    </div>
    <div id="modal_window_comm" class="modal_window">
        <? if (!empty($dop_contacts)) { ?>
            <div style="margin-top: 10px;">
                <? foreach ($dop_contacts AS $contact) {?>
                    <input type="radio" name='rb_email' value='<? echo $contact->contact; ?>' onclick='rb_email_click(this)'><? echo $contact->contact; ?><br>
                <? }?>
            </div>
        <? } ?>
        <h6 style = "margin-top:10px">Введите почту</h6>
        <p><input type="text" id="email_comm" placeholder="Почта" required></p>
        <p><button type="button" id="send_comm" data-quick = 0 class="btn btn-primary">Отправить</button>  <button type="button" id="cancel2" class="btn btn-primary">Отмена</button></p>
    </div>
    <div id="modal_window_login" class="modal_window">
        <? if (!empty($dop_contacts)) { ?>
            <div style="margin-top: 10px;">
                <? foreach ($dop_contacts AS $contact) {?>
                    <input type="radio" name='rb_email_l' value='<? echo $contact->contact; ?>' onclick='rb_email_l_click(this)'><? echo $contact->contact; ?><br>
                <? }?>
            </div>
        <? } ?>
        <h6 style = "margin-top:10px">Введите почту</h6>
        <p><input type="text" id="email_login" placeholder="Почта" required></p>
        <p><button type="button" id="send_login" class="btn btn-primary">Отправить</button>  <button type="button" id="cancel3" class="btn btn-primary">Отмена</button></p>
    </div>
    <div id="modal_window_call" class="modal_window">
        <h4>Добавить звонок</h4>
        <div class="row center" style="margin-bottom: 15px;">
            <div class="col-md-4"></div>
            <div class="col-md-4">
                <input id="call_date_m" type="datetime-local" placeholder="Дата звонка" class="form-control">
            </div>
            <div class="col-md-4"></div>
        </div>
        <div class="row center" style="margin-bottom: 15px;">
            <div class="col-md-4"></div>
            <div class="col-md-4">
                <input id="call_comment_m" placeholder="Введите примечание" class="form-control">
            </div>
            <div class="col-md-4"></div>
        </div>
        <div class="row center" style="margin-bottom: 15px;">
            <div class="col-md-12">
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
        </div>
        <div class="row center" style="margin-bottom: 15px;">
            <div class="col-md-12">
                <button class="btn btn-primary" id="add_call" type="button">
                    <i class="fas fa-save" aria-hidden="true"></i> Сохранить
                </button>
            </div>
        </div>

    </div>
    <div class="modal_window" id="modal_window_select_number">
        <p>Выберите номер для звонка:</p>
        <select id="select_phones" class = "select_phones"><option value='0' disabled selected>Выберите номер</option>
            <?php foreach($client_phones as $item): ?>
                <option value="<?php echo $item->phone; ?>"><?php echo $item->phone; ?></option>
            <?php endforeach;?>
        </select>
    </div>
    <div id="call" class="modal_window">
        <p>Перенести звонок</p>
        <p>Дата звонка:</p>
        <p><input name="call_date" id="call_date" type="datetime-local" placeholder="Дата звонка"></p>
        <p>Примечание</p>
        <p><input name="call_comment" id="call_comment" placeholder="Введите примечание"></p>
        <p><button class="btn btn-primary" id="add_call_and_submit" type="button"><i class="fas fa-save" aria-hidden="true"></i></button></p>
    </div>
    <div class = "modal_window" id="modal_window_send_email">
        <p>Новое сообщение</p>
        <? if (!empty($dop_contacts)) {
            $i=0; ?>
            <div style="margin-top: 10px;">
                <? foreach ($dop_contacts AS $contact) {

                    $id = "cnt".$i;?>
                    <input type="radio" name='send_email' class = "radio" id="<?php echo $id;?>" value='<? echo $contact->contact; ?>'><label for ="<?php echo $id;?>" ><? echo $contact->contact; ?></label><br>
                    <?
                    $i++;
                }?>
            </div>
        <? } ?>
        <h6 style = "margin-top:10px">Введите почту</h6>
        <p><input type="text" id="email" class="input-gm" placeholder="Почта" required></p>
        <p>Тема письма</p>
        <p><input type="text" id="email_subj" class="input-gm" placeholder="Тема" required></p>
        <p><textarea class="textarea-gm" rows="10" id="email_text" placeholder="Введите текст письма"></textarea></p>
        <p><button type = "button" id = "send_email" class = "btn btn-primary">Отправить</button></p>
    </div>
    <div class = "modal_window" id="modal_window_city">
        <h4>Изменение города</h4>
        <h6 style = "margin-top:10px">Введите город</h6>
        <div class="row" style="margin-bottom: 10px;">
            <div class="col-md-3"></div>
            <div class="col-md-6">
                <input type="text" id="city" class="form-control" placeholder="Город" required>
            </div>
            <div class="col-md-3"></div>
        </div>
        <div class="row">
            <div class="col-md-4"></div>
            <div class="col-md-4">
                <div class="col-md-2"></div>
                <div class="col-md-4">
                    <button type = "button" id = "change_city" class = "btn btn-primary">Сохранить</button>
                </div>
                <div class="col-md-4">
                    <button type = "button"  class = "btn btn-primary btn_cancel">Отмена</button>
                </div>
                <div class="col-md-2"></div>

            </div>
            <div class="col-md-4"></div>
        </div>
        <p></p>
        <p></p>
    </div>
    <div class = "modal_window" id="mw_manager">
        <h4>Изменение менеджера</h4>
        <h6 style = "margin-top:10px">Выберите менеджера</h6>
        <div class="row" style="margin-bottom: 10px;">
            <div class="col-md-3"></div>
            <div class="col-md-6">
                <select id="managers" class="form-control">
                    <option value="0">Выберите менеджера</option>
                    <?php foreach($managers as $manager){ ?>
                        <option value="<?=$manager->id?>"><?=$manager->name?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="col-md-3"></div>
        </div>
        <div class="row">
            <div class="col-md-4"></div>
            <div class="col-md-4">
                <div class="col-md-2"></div>
                <div class="col-md-4">
                    <button type = "button" id = "update_manager" class = "btn btn-primary">Сохранить</button>
                </div>
                <div class="col-md-4">
                    <button type = "button"  class = "btn btn-primary btn_cancel">Отмена</button>
                </div>
                <div class="col-md-2"></div>

            </div>
            <div class="col-md-4"></div>
        </div>
        <p></p>
        <p></p>
    </div>
</div>

<script>
    var $ = jQuery,
        client_id = <?php echo $this->item->id;?>;
    jQuery(document).mouseup(function (e){ // событие клика по веб-документу
        var div = jQuery("#modal_window_fio"),
            div2 = jQuery('#mw_manager'),
            div3 = jQuery("#modal_window_comm"),
            div4 = jQuery("#modal_window_login"),
            div5 = jQuery("#modal_window_call"),
            div6 = jQuery("#modal_window_select_number"),
            div8 = jQuery("#call"),
            div9 = jQuery("#modal_window_send_email"),
            div10 = jQuery("#modal_window_city");
        if (!div.is(e.target) &&
            !div2.is(e.target) &&
            !div3.is(e.target) &&
            !div4.is(e.target) &&
            !div5.is(e.target) &&
            !div6.is(e.target) &&
            !div8.is(e.target) &&
            !div9.is(e.target) &&
            !div10.is(e.target)
            && div.has(e.target).length === 0 && div2.has(e.target).length === 0
            && div3.has(e.target).length === 0 && div4.has(e.target).length === 0
            && div5.has(e.target).length === 0 &&  div6.has(e.target).length === 0
            && div8.has(e.target).length === 0 && div9.has(e.target).length === 0
            && div10.has(e.target).length === 0) {
            jQuery("#close").hide();
            jQuery("#mv_container").hide();
            div.hide();
            div2.hide();
            div3.hide();
            div4.hide();
            div5.hide();
            div6.hide();
            div8.hide();
            div9.hide();
            div10.hide();
        }
    });

    function add_history(id_client, comment)
    {
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
                var pt = "<?php echo $phoneto; ?>";
                var pf = "<?php echo $phonefrom; ?>";
                var call_id = <?php echo $call_id; ?>;
                setTimeout(function(){location.href = location.href;}, 1000);
            },
            error: function (data) {
                console.log(data);
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

    function rb_email_click(elem)
    {
        jQuery("#email_comm").val(elem.value);
    }

    function rb_email_l_click(elem)
    {
        jQuery("#email_login").val(elem.value);
    }

    function OpenPage() {
        var e = jQuery("[data-href]");
        jQuery.each(e, function (i, v) {
            jQuery(v).click(function () {
                document.location.href = this.dataset.href;
            });
        });
    }
    //-----------------------------------------------

    document.getElementById('btn_open_callback').onclick = function () {
        var cont = document.getElementById('callback_cont');
        if (cont.style.display == 'block') {
            cont.style.display = 'none';
        } else {
            cont.style.display = 'block';
        }
    }

    jQuery(document).ready(function () {
        jQuery('body').on('click', '.row_project', function(e){
            if (jQuery(this).data('href') !== undefined)
            {
                document.location.href = jQuery(this).data('href');
            }
        });
        // фильтр по статусу
        jQuery("#select_status").change(function () {
            var status = jQuery("#select_status").val();
            var search = jQuery("#filter_search").val();
            jQuery.ajax({
                type: "POST",
                url: "/index.php?option=com_gm_ceiling&task=filterProjectForStatus",
                data: {
                    status: status,
                    search: search,
                    dealer_id: <?php echo $client->dealer_id; ?>
                },
                dataType: "json",
                async: true,
                cache: false,
                success: function (data) {
                    console.log(data);
                    fill_dealer_clients(data);
                },
                timeout: 50000,
                error: function (data) {
                    console.log(data);
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
        jQuery("#but_comm").click(function (){
            jQuery("#mv_container").show();
            jQuery("#modal_window_comm").show("slow");
            jQuery("#close").show();
        });

        jQuery("#but_login").click(function (){
            jQuery("#mv_container").show();
            jQuery("#modal_window_login").show("slow");
            jQuery("#close").show();
        });

        jQuery("#start_search").click(function(){
            var search_string = jQuery("#search_string").val();
            jQuery.ajax({
                url: "index.php?option=com_gm_ceiling&task=clients.searchClients",
                data: {
                    search_text: search_string,
                    dealer_id:<?php echo $client->dealer_id; ?>
                },
                dataType: "json",
                async: true,
                success: function (data) {
                    console.log(data);
                    fill_dealer_clients(data);
                },
                error: function (data) {
                    console.log(data);
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

        jQuery("#add_call").click(function(){
            jQuery.ajax({
                url: "index.php?option=com_gm_ceiling&task=addCall",
                data: {
                    id_client: client_id,
                    date: jQuery("#call_date_m").val(),
                    comment: jQuery("#call_comment_m").val()
                },
                dataType: "json",
                async: true,
                success: function (data) {
                    add_history(client_id,"Добавлен звонок");
                    var n = noty({
                        timeout: 2000,
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "success",
                        text: "Добавлен звонок"
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
                        text: "Ошибка сервера"
                    });
                }
            });
        });


        jQuery("[name = rm_email]").click(function(){
            var email = jQuery(this).attr("email");

            jQuery.ajax({
                url: "index.php?option=com_gm_ceiling&task=client.removeEmail",
                data: {
                    email: email,
                    client_id : client_id
                },
                dataType: "json",
                async: true,
                success: function (data) {
                    add_history(client_id,"Удален email: "+email);
                    var n = noty({
                        timeout: 2000,
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "success",
                        text: "Письмо отправлено!"
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

        jQuery("#btn_city").click(function(){
            jQuery("#close").show();
            jQuery("#mv_container").show();
            jQuery("#modal_window_city").show("slow");
        });

        jQuery("#edit_manager").click(function(){
            jQuery("#close").show();
            jQuery("#mv_container").show();
            jQuery("#mw_manager").show("slow");
        });

        jQuery("#change_city").click(function(){
            var dealer_id = <?php echo $this->item->dealer_id;?>;
            jQuery.ajax({
                url: "index.php?option=com_gm_ceiling&task=dealer.change_city",
                data: {
                    dealer_id : dealer_id,
                    city: jQuery("#city").val()
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
                        text: "Город изменен"
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

        jQuery("#new_send_email").click(function(){
            jQuery("#close").show();
            jQuery("#mv_container").show();
            jQuery("#modal_window_send_email").show("slow");
        });

        jQuery("#edit").click(function() {
            jQuery("#mv_container").show();
            jQuery("#modal_window_fio").show("slow");
            jQuery("#close").show();
        });

        jQuery("[name = send_email]").change(function(){
            jQuery("#email").val(jQuery(this).val());
        });

        jQuery("#send_email").click(function(){
            var email = jQuery("#email").val();
            var subj = jQuery("#email_subj").val();
            var text = jQuery("#email_text").val();
            jQuery.ajax({
                url: "index.php?option=com_gm_ceiling&task=dealer.sendEmail",
                data: {
                    email: email,
                    subj: subj,
                    text: text,
                    client_id : client_id
                },
                dataType: "json",
                async: true,
                success: function (data) {
                    add_history(client_id,"Отправлен email");
                    var n = noty({
                        timeout: 2000,
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "success",
                        text: "Письмо отправлено!"
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

        jQuery("#update_fio").click(function(){
            jQuery.ajax({
                type: 'POST',
                url: "index.php?option=com_gm_ceiling&task=updateClientFIO",
                data: {
                    client_id: client_id,
                    fio: jQuery("#new_fio").val()
                },
                success: function(data){
                    jQuery("#FIO").text(data);
                    jQuery("#new_fio").val("");
                    jQuery("#close-tar").hide();
                    jQuery("#modal-window-container-tar").hide();
                    jQuery("#modal-window-call-tar").hide();
                    var n = noty({
                        theme: 'relax',
                        timeout: 2000,
                        layout: 'center',
                        maxVisible: 5,
                        type: "success",
                        text: "ФИО обновлено!"
                    });
                },
                dataType: "text",
                timeout: 10000,
                error: function(data){
                    console.log(data);
                    var n = noty({
                        theme: 'relax',
                        timeout: 2000,
                        layout: 'center',
                        maxVisible: 5,
                        type: "error",
                        text: "Ошибка!"
                    });
                }
            });
        });

        jQuery("#add_new_project").click(function(){
            jQuery.ajax({
                type: 'POST',
                url: "index.php?option=com_gm_ceiling&task=create_empty_project",
                data: {
                    client_id: client_id

                },
                success: function(data){
                    data = JSON.parse(data);
                    var call_id = <?php echo $call_id; ?>;
                    url = '/index.php?option=com_gm_ceiling&view=project&type=gmmanager&subtype=production&id=' + data + '&call_id=' + call_id;
                    location.href =url;
                },
                dataType: "text",
                timeout: 10000,
                error: function(data){
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

        /*Переход к проекту в воде денег*/
        $(".modal_window_pay .dealer_history tbody tr.project").click(function () {
            $id = this.dataset.project;
            location.href = "/index.php?option=com_gm_ceiling&view=project&type=calculator&subtype=project&id=" + $id;
        });

        jQuery("#savePayment").click(function(){
            var project_id = jQuery("#slct_project").val(),
                dealer_id = jQuery("#jform_dealer_id").val(),
                comment = jQuery("#pay_comment").val(),
                sum = jQuery("#pay_sum").val();
            jQuery.ajax({
                url: '/index.php?option=com_gm_ceiling&task=clientform.pay',
                data: {
                    dealer_id: dealer_id,
                    pay_sum: sum,
                    pay_comment: comment,
                    project_id: project_id
                },
                dataType: "json",
                async: true,
                success: function(data) {
                    var n = noty({
                        timeout: 2000,
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "success",
                        text: "Сумма успешно внесена!"
                    });
                    jQuery("#close").hide();
                    jQuery("#mv_container").hide();
                    jQuery("#modal_window_comm").hide();
                    location.reload();
                },
                error: function(data) {
                    console.log(data);
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
        // фильтр по статусу
        jQuery("#select_status").change();
        //-----------------------------------------------

        document.getElementById('calls-tar').scrollTop = 9999;
        jQuery('#jform_client_contacts').mask('+7(999) 999-9999');
        jQuery('#new_phone').mask('+7(999) 999-9999');

        jQuery("#send_comm").click(function(){
            let url = "index.php?option=com_gm_ceiling&task=sendCommercialOffer";
            if(jQuery(this).data('quick') == 1){
                url = "index.php?option=com_gm_ceiling&task=sendCommercialQuickWay"
            }
            var user_id = <?php echo $client->dealer_id; ?>;
            jQuery.ajax({
                url: url,
                data: {
                    user_id: user_id,
                    email: jQuery("#email_comm").val(),
                    dealer_type: 1
                },
                dataType: "json",
                async: false,
                success: function(data) {
                    var n = noty({
                        timeout: 2000,
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "success",
                        text: "Коммерческое предложение отправленно"
                    });
                    jQuery("#close").hide();
                    jQuery("#mv_container").hide();
                    jQuery("#modal_window_comm").hide();
                },
                error: function(data) {
                    console.log(data);
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
        jQuery("#send_quick_way").click(function(){
            jQuery("#send_comm").attr('data-quick',1);
            jQuery("#mv_container").show();
            jQuery("#modal_window_comm").show("slow");
            jQuery("#close").show();
        });
        jQuery("#btn_made_mnfct").click(function(){
            var user_id = <?php echo $client->dealer_id; ?>;
            jQuery.ajax({
                url: "index.php?option=com_gm_ceiling&task=big_smeta.changeDealerType",
                data: {
                    user_id: user_id,
                    type : 6
                },
                dataType: "json",
                async: false,
                success: function(data) {
                    var n = noty({
                        timeout: 2000,
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "success",
                        text: "Дилер переведен в производителя"
                    });
                },
                error: function(data) {
                    console.log(data);
                    var n = noty({
                        timeout: 2000,
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "error",
                        text: "Произошла ошибка"
                    });
                }
            });
        });
        jQuery("#send_login").click(function(){
            var user_id = <?php echo $client->dealer_id; ?>;
            jQuery.ajax({
                url: "index.php?option=com_gm_ceiling&task=sendLogin",
                data: {
                    user_id: user_id,
                    email: jQuery("#email_login").val()
                },
                dataType: "json",
                async: false,
                success: function(data) {
                    var n = noty({
                        timeout: 2000,
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "success",
                        text: "Логин и пароль отправлен"
                    });
                    jQuery("#close").hide();
                    jQuery("#mv_container").hide();
                    jQuery("#modal_window_login").hide();
                },
                error: function(data) {
                    console.log(data);
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

        jQuery('#update_manager').click(function () {
            var client_id = <?php echo $client->id; ?>;
            jQuery.ajax({
                type: 'POST',
                url: "index.php?option=com_gm_ceiling&task=client.updateManager",
                data: {
                    client_id: client_id,
                    manager_id: jQuery("#managers").val()
                },
                success: function (data) {
                    jQuery("#close").hide();
                    jQuery("#mv_container").hide();
                    jQuery("#mw_manager").hide;
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

        document.getElementById('add_email').onclick = function()
        {
            if (document.getElementById('new_email').value == "")
            {
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
                success: function(data) {
                    location.reload();
                },
                error: function(data) {
                    console.log(data);
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

        document.getElementById('add_phone').onclick = function()
        {
            jQuery.ajax({
                url: "index.php?option=com_gm_ceiling&task=client.addPhone",
                data: {
                    client_id: client_id,
                    phone: document.getElementById('new_phone').value
                },
                dataType: "json",
                async: false,
                success: function(data) {
                    location.reload();
                },
                error: function(data) {
                    console.log(data);
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

        jQuery("#back_btn").click(function (){
            history.go(-1);
        });

        jQuery("#add_comment").click(function ()
        {
            var comment = jQuery("#new_comment").val();
            var reg_comment = /[\\\<\>\/\'\"\#]/;


            if (reg_comment.test(comment) || comment === "")
            {
                alert('Неверный формат примечания!');
                return;
            }

            add_history(client_id, comment);
        });

        jQuery("#but_call").click(function ()
        {
            jQuery("#close").show();
            jQuery("#mv_container").show();
            jQuery("#modal_window_select_number").show("slow");
        });
        jQuery("#add_pay").click(function(){
            location.href = '/index.php?option=com_gm_ceiling&view=clientaccount&id='+client_id
        });
        jQuery("#select_phones").change(function ()
        {
            call(jQuery("#select_phones").val());
            add_history(client_id, "Исходящий звонок на " + jQuery("#select_phones").val().replace('+',''));
        });
        jQuery("#but_callback").click(function (){
            jQuery("#mv_container").show();
            jQuery("#modal_window_call").show("slow");
            jQuery("#close").show();
        });
        jQuery("#broke").click(function(){
            jQuery("#mv_container").show();
            jQuery("#call").show("slow");
            jQuery("#close").show();

        })
        jQuery("#add_call_and_submit").click(function(){
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
                    add_history(client_id,"Звонок перенесен");
                    var n = noty({
                        timeout: 2000,
                        theme: 'relax',
                        layout: 'center',
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
                        layout: 'center',
                        maxVisible: 5,
                        type: "error",
                        text: "Ошибка сервера"
                    });
                }
            });
        });

        jQuery('.btn_cancel').click(function(){
            jQuery(this).closest('.modal_window').hide();
            jQuery("#close").hide();
            jQuery("#mv_container").hide();
        });
    });
</script>