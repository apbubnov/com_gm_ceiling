<?php
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
?>
<button id="back_btn" class="btn btn-primary"><i class="fa fa-arrow-left" aria-hidden="true"></i> Назад</button>
<div id="FIO-container-tar">
    <label id = "FIO"><?php echo $this->item->client_name; ?></label>
    <button type="button" id="edit" value="" class = "btn btn-primary"><i class="fa fa-pencil" aria-hidden="true"></i></button>
    <button class = "btn btn-primary" type = "button" id="but_call"><i class="fa fa-phone" aria-hidden="true"></i></button>
    <?php if ($call_id != 0) { ?>
        <button id = "broke" type = "button" class = "btn btn-primary">Звонок сорвался, перенести время</button>
    <?php } ?>
   
    <br><label>Менеджер: <?php echo $manager_name;?></label>
</div>
<table class = "actions">
    <tr>
        <td>
            <button class="btn btn-primary" type="button" id="btn_refuse">Отказ от сотрудничества</button>
        </td>
    </tr>
    <tr>
        <td class = "td-left">
            <button class="btn btn-primary" type="button" id="but_comm">Отправить КП</button>
        </td>
    </tr>
    <tr>
        <td class = "td-left">
            <button class="btn btn-primary" type="button" id="but_msk_kp">Московский застройщик КП</button>
        </td>
    </tr>
    <tr>
        <td class = "td-left">
            <button class="btn btn-primary" type="button" id="but_callback">Добавить перезвон</button>
        </td>
    </tr>
    
</table>
<?php include_once('components/com_gm_ceiling/views/clientcard/buttons_calls_hisory.php'); ?>
<?php
        $client_phones_model = Gm_ceilingHelpersGm_ceiling::getModel('client_phones');
        $client_phones = $client_phones_model->getItemsByClientId($this->item->id);
        $client_dop_contacts_model = Gm_ceilingHelpersGm_ceiling::getModel('clients_dop_contacts'); 
        $dop_contacts = $client_dop_contacts_model->getContact($this->item->id);?>
<div style="width: 98%;">
<div style="display: inline-block; width: 48%;">
    <div>
    <p class = "caption-tar" style="font-size: 26px; color: #414099; text-align: left; margin-bottom: 0px;">Почта застройщика: </p>
    </div>
    <? if (!empty($dop_contacts)) { ?>
    <div>
    <? foreach ($dop_contacts AS $contact) {?>
        <p  style="font-size: 20px; color: #414099; text-align: left; margin-bottom: 0px;"><? echo $contact->contact; echo "<br>";?></p> <? }?>
    </div>
    <? } ?>
    <div>
        <input type="text" id="new_email" placeholder="Почта" required>
        <button type="button" id="add_email" class="btn btn-primary">Добавить</button>
    </div>
</div>
<div style="display: inline-block; width: 48%;">
    <div>
    <p class = "caption-tar" style="font-size: 26px; color: #414099; margin-bottom: 0px;">Телефоны застройщика: </p>
    </div>
    <div>
    <?php foreach($client_phones as $item) { ?>
            <p  style="font-size: 20px; color: #414099; margin-bottom: 0px;"><? echo $item->phone; ?></p>
        <?php } ?>
    </div>
    <div>
        <input type="text" id="new_phone" placeholder="Телефон" required>
        <button type="button" id="add_phone" class="btn btn-primary">Добавить</button>
    </div>
</div>
</div>
<div class="row">
    <div class="col-sm-12" id = "calls">
        <p class="caption-tar">История застройщика</p>
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
        <label for="comments">Добавить комментарий:</label>
        <br>
        <input id="new_comment" type="text" class="input-text-tar input2" placeholder ="Введите новый комментарий">
        <button class = "btn btn-primary" type = "button" id="add_comment"><i class="fa fa-paper-plane" aria-hidden="true"></i></button>
    </div>
</div>

<br>
<div style="width: 100%; text-align: center;">
    <button type="button" id="new_client" class="btn btn-primary">Создать клиента</button>
</div>
<br>

<div class="row">
    <div class="col-sm-12" id = "cliens_of_dealer">
        <p class="caption-tar">Клиенты застройщика</p>
        <div id="cliens_of_dealer_2">
            <table id="cliens_of_dealer_table" class="table table-striped table_cashbox one-touch-view" cellspacing="0">
                <tbody>
                <?php foreach ($clients_items as $i => $item) : ?>
                    <tr class="row<?php echo $i % 2; ?>" data-href="<?php echo JRoute::_('index.php?option=com_gm_ceiling&view=clientcard&id='.(int) $item->id); ?>">
                        <td class="one-touch">
                            <?php
                                if($item->created == "0000-00-00") {
                                    echo "-";
                                } else {
                                    $jdate = new JDate($item->created);
                                    $created = $jdate->format("d.m.Y");
                                    echo $created;
                                }
                            ?>
                            
                        </td>
                        <td class="one-touch"><?php echo $item->client_name; ?></td>
                        <td class="one-touch"><?php echo $item->client_contacts; ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="orders-container-tar">
    <p class="caption-tar">Заказы</p>
    <table id="table-orders-tar" class="table table-striped one-touch-view">
        <tr>
            <td>Номер</td>
            <td>Дата</td>
            <td>Сумма</td>
            <td>Примечание</td>
            <td>Статус</td>
        </tr>
     
        <?php foreach($projects as $item):?>

            <tr class = "row_project" data-href="<?php echo JRoute::_('index.php?option=com_gm_ceiling&view=project&type=gmmanager&subtype=designer&id='.(int) $item->id.'&call_id='.(int) $call_id); ?>">
                <td><?php echo $item->id;?></td>
                <td>
                    <?php 
                        $date = new DateTime($item->created);
                        echo $date->Format('d.m.Y');
                    ?>
                </td>
                <td><?php echo $item->project_sum;?></td>
                <td><?php echo $item->gm_manager_note; ?></td>
                <td><?php echo $item->status; ?></td>
            </tr>

        <?php endforeach;?>
   
    </table>
    <div id="add-gauging-container-tar">
        <input type="button" id="add_new_project" class="input-button-tar" value="Добавить замер">
    </div>
</div>

<div id="mv_container" class="modal_window_container">
    <button type="button" id="close" class="close_btn"><i class="fa fa-times fa-times-tar" aria-hidden="true"></i></button>
    <div id="modal_window_fio" class="modal_window">
        <h6>Введите новое ФИО клиента</h6>
        <p><input type="text" id="new_fio" placeholder="ФИО" required></p>
        <p><button type="button" id="update_fio" class="btn btn-primary">Сохранить</button>  <button type="button" id="cancel" class="btn btn-primary">Отмена</button></p>
    </div>
    <div id="modal_window_client" class="modal_window">
        <form action="/index.php?option=com_gm_ceiling&task=clientform.save" method="post" enctype="multipart/form-data">
            <p><strong>Создание нового клиента</strong></p>
            <p>ФИО:</p>
            <p><input type="text" id="fio_client" name="jform[client_name]"></p>
            <p>Номер телефона:</p>
            <p><input type="text" id="jform_client_contacts" name="jform[client_contacts]" required></p>
            <input type="hidden" id="jform_dealer_id" name="jform[dealer_id]" value="<?php echo $client->dealer_id; ?>">
            <p><button type="submit" id="save_client" class="btn btn-primary">ОК</button></p>
        </form>
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
        <p><button type="button" id="send_comm" class="btn btn-primary">Отправить</button>  <button type="button" id="cancel2" class="btn btn-primary">Отмена</button></p>
    </div>
    <div id="modal_window_call" class="modal_window">
            <label>Добавить звонок</label><br>
            <input id="call_date_m" type="datetime-local" placeholder="Дата звонка"><br>
            <input id="call_comment_m" placeholder="Введите примечание"><br>
            <button class="btn btn-primary" id="add_call" type="button"><i class="fa fa-floppy-o" aria-hidden="true"></i></button>
    </div>
    <div id="modal_window_select_number" class="modal_window">
        <p>Выберите номер для звонка:</p>
        <select id="select_phones" class = "select_phones">
            <option value='0' disabled selected>Выберите номер</option>
            <?php foreach($client_phones as $item): ?>
                <option value="<?php echo $item->phone; ?>"><?php echo $item->phone; ?></option>
            <?php endforeach;?>
        </select>
    </div>
    <div id="call" class="modal_window">
            <p>Перенести звонок</p>
            <p>Дата звонка</p>
            <p><input name="call_date" id="call_date" type="datetime-local" placeholder="Дата звонка"></p>
            <p>Примечание</p>
            <p><input name="call_comment" id="call_comment" placeholder="Введите примечание"></p>
            <p><button class="btn btn-primary" id="add_call_and_submit" type="button"><i class="fa fa-floppy-o" aria-hidden="true"></i></button></p>

    </div>
</div>
<script>
    jQuery(document).mouseup(function (e){ // событие клика по веб-документу
        var div = jQuery("#modal_window_fio"); // тут указываем ID элемента
        var div2 = jQuery("#modal_window_client");
        var div3 = jQuery("#modal_window_comm");
        var div4 = jQuery("#modal_window_call");
        var div5 = jQuery("#call");
        var div6 = jQuery("#modal_window_select_number");
        if (!div.is(e.target) && !div2.is(e.target) && !div3.is(e.target) 
            && !div4.is(e.target) && !div5.is(e.target) && !div6.is(e.target)
            && div.has(e.target).length === 0 && div2.has(e.target).length === 0 && div3.has(e.target).length === 0 
            && div4.has(e.target).length === 0 && div5.has(e.target).length === 0 && div6.has(e.target).length === 0) {
            jQuery("#close").hide();
            jQuery("#mv_container").hide();
            jQuery("#modal_window_fio").hide();
            jQuery("#modal_window_client").hide();
            jQuery("#modal_window_comm").hide();
            jQuery("#modal_window_call").hide();
            jQuery("#call").hide();
            jQuery("#modal_window_select_number").hide();
        }
    });

    jQuery("#new_client").click(function(){
        jQuery("#close").show();
		jQuery("#mv_container").show();
		jQuery("#modal_window_client").show("slow");
    });

    jQuery("#edit").click(function() {
        jQuery("#mv_container").show();
        jQuery("#modal_window_fio").show("slow");
        jQuery("#close").show();
    });

    jQuery("#but_comm").click(function (){
        jQuery("#mv_container").show();
        jQuery("#modal_window_comm").show("slow");
        jQuery("#close").show();
    });

    jQuery("#but_msk_kp").click(function (){
        jQuery("#send_comm").attr("c_type","msk");
        jQuery("#mv_container").show();
        jQuery("#modal_window_comm").show("slow");
        jQuery("#close").show();
    });

    jQuery("#but_callback").click(function (){
        jQuery("#mv_container").show();
        jQuery("#modal_window_call").show("slow");
        jQuery("#close").show();
    });

    jQuery("#cancel").click(function(){
        jQuery("#close").hide();
        jQuery("#mv_container").hide();
        jQuery("#modal_window_fio").hide();
    });

    jQuery("#cancel2").click(function(){
        jQuery("#close").hide();
        jQuery("#modal_window_container").hide();
        jQuery("#modal_window_comm").hide();
    });

    jQuery("#update_fio").click(function(){
        jQuery.ajax({
            type: 'POST',
            url: "index.php?option=com_gm_ceiling&task=updateClientFIO",
            data: {	
                client_id: "<?php echo $this->item->id;?>",
                fio: jQuery("#new_fio").val()
            },
            success: function(data){
                jQuery("#FIO").text(data);
                jQuery("#new_fio").val("");
                jQuery("#close").hide();
		        jQuery("#mv_container").hide();
		        jQuery("#modal_window_fio").hide();
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
    })
    
    jQuery('body').on('click', '.row_project', function(e)
    {
        if (jQuery(this).data('href') !== undefined)
        {
            document.location.href = jQuery(this).data('href');
        }
    });

    jQuery("#add_new_project").click(function(){
        jQuery.ajax({
            type: 'POST',
            url: "index.php?option=com_gm_ceiling&task=create_empty_project",
            data: {
                client_id:<?php echo $this->item->id;?>
            
            },
            success: function(data){
                data = JSON.parse(data);
                var call_id = <?php echo $call_id; ?>;
                url = '/index.php?option=com_gm_ceiling&view=project&type=gmmanager&subtype=designer&id=' + data + '&call_id=' + call_id;
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

    function rb_email_click(elem)
    {
        jQuery("#email_comm").val(elem.value);
    }

    jQuery(document).ready(function ()
    {
        document.getElementById('calls-tar').scrollTop = 9999;
        jQuery('#jform_client_contacts').mask('+7(999) 999-9999');
        jQuery('#new_phone').mask('+7(999) 999-9999');

        jQuery("#send_comm").click(function(){
            var user_id = <?php echo $client->dealer_id; ?>;
            var type = null;
            if(jQuery(this).attr("c_type") == "msk"){
                type = 2;
            }
            jQuery.ajax({
                url: "index.php?option=com_gm_ceiling&task=sendCommercialOffer",
                data: {
                    user_id: user_id,
                    email: jQuery("#email_comm").val(),
                    dealer_type: 7,
                    type:type
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

        document.getElementById('add_email').onclick = function()
        {
            var client_id = <?php echo $client->id; ?>;
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
            var client_id = <?php echo $client->id; ?>;
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

        document.getElementById('btn_refuse').onclick = function()
        {
            var user_id = <?php echo $dealer->id; ?>;
            jQuery.ajax({
                url: "index.php?option=com_gm_ceiling&task=userRefuseToCooperate",
                data: {
                    user_id: user_id,
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
                        text: "Переведен в отказ от сотрудничества"
                    });
                    setTimeout(function(){location.href = '/index.php?option=com_gm_ceiling&view=mainpage&type=gmmanagermainpage';}, 1000);
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
    });


    jQuery("#back_btn").click(function (){
        history.go(-1);
    });

    jQuery("#add_comment").click(function ()
    {
        var comment = jQuery("#new_comment").val();
        var reg_comment = /[\\\<\>\/\'\"\#]/;
        var id_client = <?php echo $this->item->id; ?>;

        if (reg_comment.test(comment) || comment === "")
        {
            alert('Неверный формат примечания!');
            return;
        }

        add_history(id_client, comment);
    });

    jQuery("#but_call").click(function ()
    {
        jQuery("#close").show();
        jQuery("#mv_container").show();
        jQuery("#modal_window_select_number").show("slow");
    });

    jQuery("#select_phones").change(function ()
    {
        var id_client = <?php echo $this->item->id; ?>;
        call(jQuery("#select_phones").val());
        add_history(id_client, "Исходящий звонок на " + jQuery("#select_phones").val().replace('+',''));
    });

    jQuery("#broke").click(function(){
        jQuery("#mv_container").show();
        jQuery("#call").show("slow");
        jQuery("#close").show(); 
    });

    jQuery("#add_call_and_submit").click(function(){
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
    jQuery("#add_call").click(function(){
        client_id = <?php echo $this->item->id;?>;
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
                setTimeout(function(){location.reload();}, 1000);
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

</script>