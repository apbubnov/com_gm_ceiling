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
    if(!empty($client->manager_id)){
        $manager_name = JFactory::getUser($client->manager_id)->name;
    }
    else{
        $manager_name = "-";
    }

    if ($client->dealer_id == 1)
    {
        $subtype = 'calendar';
    }
    else
    {
        if(JFactory::getUser($client->dealer_id)->dealer_type == 3)
        {
            $subtype = 'designer';
        }
        else
        {
            $subtype = 'production';
        }
    }
?>
<button id="back_btn" class="btn btn-primary"><i class="fa fa-arrow-left" aria-hidden="true"></i> Назад</button>
<div id="FIO-container-tar">
    <p>
    <label id = "FIO"><?php echo $this->item->client_name; ?></label>
    <button type="button" id="edit" value="" class = "btn btn-primary"><i class="fa fa-pencil" aria-hidden="true"></i></button>
     <? if ($user->dealer_type != 1):?>
    <button class = "btn btn-primary" type = "button" id="but_call"><i class="fa fa-phone" aria-hidden="true"></i></button>
    <?php if ($call_id != 0) { ?>
        <button id = "broke" type = "button" class = "btn btn-primary">Звонок сорвался, перенести время</button>
    <?php } ?>
    <br><label>Менеджер: <?php echo $manager_name;?></label>
    <?endif;?>
</div>


<?php
        $client_phones_model = Gm_ceilingHelpersGm_ceiling::getModel('client_phones');
        $client_phones = $client_phones_model->getItemsByClientId($this->item->id);
    ?>
    <select id="select_phones" style="display:none;"><option value='0' disabled selected>Выберите номер</option>
        <?php foreach($client_phones as $item): ?>
            <option value="<?php echo $item->phone; ?>"><?php echo $item->phone; ?></option>
        <?php endforeach;?>
    </select></p>
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
<? $client_dop_contacts_model = Gm_ceilingHelpersGm_ceiling::getModel('clients_dop_contacts'); 
        $dop_contacts = $client_dop_contacts_model->getContact($this->item->id);?>
<div class="container">
    <div class="row">
        <div class="col-sm-6">
            <div style="display: inline-block;">
                <div>
                    <p class="caption-tar"
                       style="font-size: 26px; color: #414099; text-align: left; margin-bottom: 0px;">Почта
                        клиента: </p>
                </div>
                <? if (!empty($dop_contacts)) { ?>
                    <div>
                        <? foreach ($dop_contacts AS $contact) { ?>
                            <p style="font-size: 20px; color: #414099; text-align: left; margin-bottom: 0px;"><? echo $contact->contact;
                                echo "<br>"; ?></p> <? } ?>
                    </div>
                <? } ?>
                <div>
                    <input type="text" id="new_email" placeholder="Почта" required>
                    <button type="button" id="add_email" class="btn btn-primary">Добавить</button>
                </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div style="display: inline-block;">
                <div>
                    <p class="caption-tar" style="font-size: 26px; color: #414099; margin-bottom: 0px;">Телефоны
                        клиента: </p>
                </div>
                <div>
                    <?php foreach ($client_phones as $item) { ?>
                        <a href="tel:<? echo $item->phone; ?>" style="font-size: 20px; color: #414099; margin-bottom: 0px;"><? echo $item->phone; ?></a><br>
                    <?php } ?>
                </div>
                <div>
                    <input type="text" id="new_phone" placeholder="Телефон" required>
                    <button type="button" id="add_phone" class="btn btn-primary">Добавить</button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if($user->dealer_type != 1) { ?>
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
<? } ?>
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

            <tr class = "row_project" data-href="<?php if($user->dealer_type == 1) {
                if($item->status == "Отказ от договора" || $item->status == "Ждет замера" || $item->status == "Договор" ) echo JRoute::_('index.php?option=com_gm_ceiling&view=project&type=calculator&subtype=calendar&id='.(int) $item->id);
                elseif($item->status == "В производстве" || $item->status == "Ожидание монтажа" || $item->status == "Заказ закрыт") echo JRoute::_('index.php?option=com_gm_ceiling&view=project&type=calculator&subtype=calendar&id='.(int) $item->id);
                else echo JRoute::_('index.php?option=com_gm_ceiling&view=project&type=manager&subtype=calendar&id='.(int) $item->id); }
                else {  echo JRoute::_('index.php?option=com_gm_ceiling&view=project&type=gmmanager&subtype='.$subtype.'&id='.(int) $item->id.'&call_id='.(int) $call_id); }?>">
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
<div id="modal-window-container-tar">
		<button type="button" id="close-tar"><i class="fa fa-times fa-times-tar" aria-hidden="true"></i></button>
		<div id="modal-window-call-tar">
			<h6>Введите новое ФИО клиента</h6>
			<p><input type="text" id="new_fio" placeholder="ФИО" required></p>
			<p><button type="button" id="update_fio" class="btn btn-primary">Сохранить</button>  <button type="button" id="cancel" class="btn btn-primary">Отмена</button></p>
		</div>
	</div>

<script>
    	jQuery(document).mouseup(function (e){ // событие клика по веб-документу
		var div = jQuery("#modal-window-call-tar"); // тут указываем ID элемента
		if (!div.is(e.target) // если клик был не по нашему блоку
		    && div.has(e.target).length === 0) { // и не по его дочерним элементам
			jQuery("#close-tar").hide();
			jQuery("#modal-window-container-tar").hide();
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
    jQuery("#cancel").click(function(){
        jQuery("#close-tar").hide();
		jQuery("#modal-window-container-tar").hide();
		jQuery("#modal-window-call-tar").hide();
    })
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
    })
    jQuery("#edit").click(function() {
			jQuery("#modal-window-container-tar").show();
			jQuery("#modal-window-call-tar").show("slow");
			jQuery("#close-tar").show();
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
                <?}?>
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

        document.getElementById('calls-tar').scrollTop = 9999;

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
