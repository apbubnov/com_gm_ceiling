<?php
    $user       = JFactory::getUser();
    $userId     = $user->get('id');
    $user_group = $user->groups;
    $dop_num_model = Gm_ceilingHelpersGm_ceiling::getModel('dop_numbers_of_users');
    $dop_num = $dop_num_model->getData($userId)->dop_number;
    $_SESSION['user_group'] = $user_group;
    $_SESSION['dop_num'] = $dop_num;

    $clientcardModel = Gm_ceilingHelpersGm_ceiling::getModel('clientcard');
    $historyModel = Gm_ceilingHelpersGm_ceiling::getModel('client_history');
    $history = $historyModel->getDataByClientId($this->item->id);
    $projects = $clientcardModel->getProjects($this->item->id);
    $jinput = JFactory::getApplication()->input;
    $phoneto = $jinput->get('phoneto', '', 'STRING');
    $phonefrom = $jinput->get('phonefrom', '', 'STRING');
    $call_id = $jinput->get('call_id', 0, 'INT');
    $client_model = Gm_ceilingHelpersGm_ceiling::getModel('client');
    $client = $client_model->getClientById($this->item->id);
    if (!empty($client->manager_id)) {
        $manager_name = JFactory::getUser($client->manager_id)->name;
    }
    else {
        $manager_name = "-";
    }
    if ($client->dealer_id == 1)
    {
        $subtype = 'calendar';
    }
    else
    {
        if(JFactory::getUser($client->dealer_id)->dealer_type == 3 || JFactory::getUser($client->dealer_id)->dealer_type == 5)
        {
            $subtype = 'designer';
        }
        else
        {
            $subtype = 'production';
        }
    }
    // контакты клиента
    $client_phones_model = Gm_ceilingHelpersGm_ceiling::getModel('client_phones');
    $client_phones = $client_phones_model->getItemsByClientId($this->item->id);
    $client_dop_contacts_model = Gm_ceilingHelpersGm_ceiling::getModel('clients_dop_contacts'); 
    $dop_contacts = $client_dop_contacts_model->getContact($this->item->id);
    //-----------------
?>

<style>
    body {
        color: #414099;
    }
    .col-sm-6 {
        padding: 0;
    }
    .contact_container1, .contact_container2 {
        display: inline-block;
        width: 100%;
        padding: 0;
    }
    @media screen and (min-width: 768px) {
        .contact_container1 {
            padding-right: 10px;
        }
        .contact_container2 {
            padding-left: 10px;
        }
    }
</style>

<button id="back_btn" class="btn btn-primary" style="margin-bottom: 1em;"><i class="fa fa-arrow-left" aria-hidden="true"></i> Назад</button>
<h3 class="center">Карточка клиента</h3>
<div id="FIO-container-tar">
    <label>Имя клиента:</label>
    
        <label id = "FIO" ><?php echo $this->item->client_name; ?></label>
        <button type="button" id="edit" value="" class = "btn btn-primary"><i class="fa fa-pencil" aria-hidden="true"></i></button>
        <?php if ($user->dealer_type != 1):?>
            <button class = "btn btn-primary" type = "button" id="but_call"><i class="fa fa-phone" aria-hidden="true"></i></button>
            <?php if ($call_id != 0) { ?>
                <button id = "broke" type = "button" class = "btn btn-primary">Звонок сорвался, перенести время</button>
            <?php } ?>
            <br>
            <label>Менеджер: <?php echo $manager_name;?></label>
        <?php endif;?>
   
</div>
<!-- стиль исправить не могу, пока не увижу где селект показывается -->
    <select id="select_phones" style="display:none;">
        <option value='0' disabled selected>Выберите номер</option>
        <?php foreach($client_phones as $item): ?>
            <option value="<?php echo $item->phone; ?>"><?php echo $item->phone; ?></option>
        <?php endforeach;?>
    </select>
    <div id="call" class="call" style="display:none;">
        <label for="call">Перенести звонок</label>
        <br>
        <table>
            <tr>
                <td> <input name="call_date" id="call_date" type="datetime-local" placeholder="Дата звонка"></td>
                <td> <input name="call_comment" id="call_comment" placeholder="Введите примечание"></td>
                <td><button class="btn btn-primary" id="add_call_and_submit" type="button"><i class="fa fa-floppy-o" aria-hidden="true"></i></button></td>
            </tr>
        </table>  
    </div>
<?php include_once('components/com_gm_ceiling/views/clientcard/buttons_calls_hisory.php'); ?>
<!-- конец -->
<!-- контакты -->
<div class="row">
    <div class="col-sm-6">
        <div class="contact_container1">
            <div>
                <h4 style="text-align: center;">Почты клиента:</h4>
            </div>
            <?php if (!empty($dop_contacts)) { ?>
                <div>
                    <?php foreach ($dop_contacts AS $contact) { ?>
                        <p style="font-size: 20px; margin-bottom: 0px;">
                            <?php echo $contact->contact; echo "<br>" ?>
                        </p>
                    <?php } ?>
                </div>
            <?php } ?>
            <div>
                <input type="text" id="new_email" placeholder="Почта" required style="width: calc(100% - 130px);">
                <button type="button" id="add_email" class="btn btn-primary" style="margin-left: 15px;">Добавить</button>
            </div>
        </div>
    </div>
    <div class="col-sm-6">
        <div class="contact_container2">
            <div>
                <h4 style="text-align: center;">Телефоны клиента:</h4>
            </div>
            <div>
                <?php foreach ($client_phones as $item) { ?>
                    <a href="tel:<?php echo $item->phone; ?>" style="font-size: 20px; color: #414099; margin-bottom: 0px;">
                        <?php echo $item->phone; ?>
                    </a>
                    <br>
                <?php } ?>
            </div>
            <div>
                <input type="text" id="new_phone" placeholder="Телефон" required style="width: calc(100% - 130px);">
                <button type="button" id="add_phone" class="btn btn-primary" style="margin-left: 15px;">Добавить</button>
            </div>
        </div>
    </div>
</div>
<!-- заказы -->
<!-- стиль исправить не могу, пока не увижу где селект показывается -->
        <div class="row">
            <div class="col-sm-12" id = "calls">
                <p class="caption-tar">История клиента</p>
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
        <p class="caption-tar">Добавить перезвон</p>
         <div class="row center">
            <input name="call_date" id="call_date" type="datetime-local" placeholder="Дата звонка">
            <input name="call_comment" id="call_comment" placeholder="Введите примечание">
            <button class="btn btn-primary" id="add_call" type="button"><i class="fa fa-floppy-o" aria-hidden="true"></i></button>

        </div>
<!-- конец -->
<!-- заказы -->
<div id="orders-container-tar">
    <h3>Заказы</h3>
    <table class="small_table table-striped table_cashbox one-touch-view" id="table_projects">
        <thead>
            <tr>
                <th>Номер</th>
                <th>Дата</th>
                <th>Сумма</th>
                <th>Примечание</th>
                <th>Статус</th>
            </tr>
        </thead>
        <?php foreach($projects as $item):?>
            <tr class = "row_project" data-href="
                <?php
                    if($user->dealer_type == 1) {
                        if ($item->status == "Просчет" || $item->status == "Отказ от договора" || $item->status == "Ждет замера" || $item->status == "Договор" ) {
                            echo JRoute::_('index.php?option=com_gm_ceiling&view=project&type=calculator&subtype=calendar&id='.(int) $item->id);
                        } else {
                            echo JRoute::_('index.php?option=com_gm_ceiling&view=project&type=calculator&subtype=project&id='.(int) $item->id);
                        } 
                        /* elseif ($item->status == "В производстве" || $item->status == "Ожидание монтажа" || $item->status == "Заказ закрыт") {
                            echo JRoute::_('index.php?option=com_gm_ceiling&view=project&type=calculator&subtype=project&id='.(int) $item->id);
                        } else {
                            echo JRoute::_('index.php?option=com_gm_ceiling&view=project&type=manager&subtype=calendar&id='.(int) $item->id);
                        } */
                    } else {
                        echo JRoute::_('index.php?option=com_gm_ceiling&view=project&type=gmmanager&subtype='.$subtype.'&id='.(int) $item->id.'&call_id='.(int) $call_id);
                    }
                ?>
            ">
                <td>
                    <?php echo $item->id;?>
                </td>
                <td>
                    <?php 
                        $date = new DateTime($item->created);
                        echo $date->Format('d.m.Y');
                    ?>
                </td>
                <td>
                    <?php echo $item->project_sum;?>
                </td>
                <td>
                    <?php
                        $note = '';
                        if ($item->project_status < 3 || $item->project_status == 15) {
                            if (!empty($item->gm_manager_note)) {
                                $note .= $item->gm_manager_note.'<br>';
                            }
                            if (!empty($item->dealer_manager_note)) {
                                $note .= $item->dealer_manager_note.'<br>';
                            }
                            if (!empty($item->project_note)) {
                                $note .= $item->project_note.'<br>';
                            }
                        } elseif ($item->project_status == 3 || $item->project_status == 4) {
                            if (!empty($item->gm_calculator_note)) {
                                $note .= $item->gm_calculator_note.'<br>';
                            }
                            if (!empty($item->dealer_calculator_note)) {
                                $note .= $item->dealer_calculator_note.'<br>';
                            }
                        } elseif ($item->project_status > 4 || $item->project_status < 11) {
                            if (!empty($item->gm_chief_note)) {
                                $note .= $item->gm_chief_note.'<br>';
                            }
                            if (!empty($item->dealer_chief_note)) {
                                $note .= $item->dealer_chief_note.'<br>';
                            }
                        } else {
                            if (!empty($item->gm_mount_note)) {
                                $note .= $item->gm_mount_note.'<br>';
                            }
                            if (!empty($item->mount_note)) {
                                $note .= $item->mount_note.'<br>';
                            }
                        }
                        echo $note;
                    ?>
                </td>
                <td>
                    <?php echo $item->status; ?>
                </td>
            </tr>
        <?php endforeach;?>
    </table>
    <div id="add-gauging-container-tar">
        <input type="button" id="add_new_project" class="btn btn-primary" value="Добавить замер">
    </div>
</div>
<!-- модальное окно -->
<div class="modal-window-container">
    <button type="button" class="btn-close"><i class="fa fa-times fa-times-tar" aria-hidden="true"></i></button>
    <div id="modal-window-call-tar" class="modal-window-tar">
        <p style="margin-top: 1em !important;">Введите новое ФИО клиента</p>
        <p><input type="text" id="new_fio" placeholder="ФИО" required></p>
        <p>
            <button type="button" id="update_fio" class="btn btn-primary">Сохранить</button>
            <button type="button" id="cancel" class="btn btn-primary">Отмена</button>
        </p>
    </div>
</div>

<script>
    	jQuery(document).mouseup(function (e){ // событие клика по веб-документу
		var div = jQuery("#modal-window-call-tar"); // тут указываем ID элемента
		if (!div.is(e.target) // если клик был не по нашему блоку
		    && div.has(e.target).length === 0) { // и не по его дочерним элементам
			jQuery(".btn-close").hide();
			jQuery(".modal-window-container").hide();
			jQuery("#modal-window-call-tar").hide();
		}
		var div1 = jQuery("#modal-window-enroll-tar"); // тут указываем ID элемента
		if (!div1.is(e.target) // если клик был не по нашему блоку
		    && div1.has(e.target).length === 0) { // и не по его дочерним элементам
			jQuery("#close2-tar").hide();
			jQuery("#modal-window-container2-tar").hide();
			jQuery("#modal-window-enroll-tar").hide();
		}
		var div2 = jQuery("#modal-window-registration-tar"); // тут указываем ID элемента
		if (!div2.is(e.target) // если клик был не по нашему блоку
		    && div2.has(e.target).length === 0) { // и не по его дочерним элементам
			jQuery("#close3-tar").hide();
			jQuery("#modal-window-container3-tar").hide();
			jQuery("#modal-window-registration-tar").hide();
		}
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
                jQuery(".btn-close").hide();
		        jQuery(".modal-window-container").hide();
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

    jQuery("#add_call").click(function(){
        if (jQuery("#call_date").val() == '')
        {
            var n = noty({
                    timeout: 2000,
                    theme: 'relax',
                    layout: 'center',
                    maxVisible: 5,
                    type: "warning",
                    text: "Укажите время перезвона"
                });
            jQuery("#call_date").focus();
            return;
        }
        jQuery.ajax({
            url: "index.php?option=com_gm_ceiling&task=addCall",
            data: {
                id_client: client_id,
                date: jQuery("#call_date").val(),
                comment: jQuery("#call_comment").val()
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
                    text: "Звонок добавлен"
                });
                add_history(client_id, 'Добавлен звонок на ' + jQuery("#call_date").val().replace('T', ' ') + ':00');
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
    
    jQuery("#edit").click(function() {
			jQuery(".modal-window-container").show();
			jQuery("#modal-window-call-tar").show("slow");
			jQuery(".btn-close").show();
	    });
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
                var pt = "<?php echo $phoneto; ?>";
                var pf = "<?php echo $phonefrom; ?>";
                var call_id = <?php echo $call_id; ?>;
                var subtype = "<?php echo $subtype; ?>";
               if (pt === "" || pf === "")
                {
                    if (call_id === 0)
                    {
                        url = '/index.php?option=com_gm_ceiling&view=project&type=gmmanager&subtype=' + subtype +'&id=' + data+ '&call_id=0';
                    }
                    else
                    {
                        url = '/index.php?option=com_gm_ceiling&view=project&type=gmmanager&subtype=' + subtype + '&id=' + data + '&call_id=' + call_id;
                    }
                }
                else
                {
                    url = '/index.php?option=com_gm_ceiling&view=project&type=gmmanager&subtype=' + subtype + '&id=' + data + '&phoneto=' + pt + '&phonefrom=' + pf;
                }
                <?php if($user->dealer_type == 1) {?>
                url = '/index.php?option=com_gm_ceiling&view=project&type=manager&subtype=calendar'+'&id=' + data;
                <?php }?>
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


    jQuery(document).ready(function ()
    {
        jQuery('#new_phone').mask('+7(999) 999-9999');
        <?php if($user->dealer_type != 1) { ?>
        document.getElementById('calls-tar').scrollTop = 9999;
        <?php } ?>

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
        document.getElementById('select_phones').style.display = 'block';
    });

    jQuery("#select_phones").change(function ()
    {
        var id_client = <?php echo $this->item->id; ?>;
        call(jQuery("#select_phones").val());
        add_history(id_client, "Исходящий звонок на " + jQuery("#select_phones").val().replace('+',''));
    });
    jQuery("#broke").click(function(){
        jQuery("#call").show();
            
    })
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
    })
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
                if (pt === "" || pf === "")
                {
                    if (call_id === 0)
                    {
                        url = '/index.php?option=com_gm_ceiling&view=clientcard&id=' + id_client;
                    }
                    else
                    {
                        url = '/index.php?option=com_gm_ceiling&view=clientcard&id=' + id_client + '&call_id=' + call_id;
                    }
                }
                else
                {
                    url = '/index.php?option=com_gm_ceiling&view=clientcard&id=' + id_client + '&phoneto=' + pt + '&phonefrom=' + pf;
                }
                setTimeout(function(){location.href = url;}, 1000);
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
</script>
