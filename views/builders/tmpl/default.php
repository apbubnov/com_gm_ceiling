<?php
/**
 * @version    CVS: 0.1.7
 * @package    Com_Gm_ceiling
 * @author     bubnov <2017 al.p.bubnov@gmail.com>
 * @copyright  2017 al.p.bubnov@gmail.com
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;

$user       = JFactory::getUser();
$userId     = $user->get('id');

$clients_model = Gm_ceilingHelpersGm_ceiling::getModel('users');
$result_clients = $clients_model->getBuilders();

$isObjectMaster = in_array('46',$user->groups);
$disabled = $isObjectMaster ? 'disabled' : '';

$objMasters = $clients_model->getUsersByGroupAndDealer(46,$user->dealer_id);
?>
    <?= parent::getButtonBack();?>
    <h2 class="center">Застройщики</h2>
    <div class="row" style="margin-bottom: 15px;">
        <div class="col-md-4">
            <button type="button" id="new_builder" class="btn btn-primary" <?= $disabled;?>>Добавить застройщика</button>
        </div>
        <div class="col-md-4">
            <select id="show_type" class="form-control">
                <option value="0">Показать всё</option>
                <option value="1" selected>Только в работе</option>
                <option value="2">Только закрытые</option>
            </select>
        </div>
        <div class="col-md-4 right">
            <div class="col-md-10">
                <input type="text" id="name_find_builder" class="form-control">
            </div>
            <div class="col-md-2">
                <button type="button" id="find_builder" class="btn btn-primary"><i class="fa fa-search" aria-hidden="true"></i></button>
            </div>
        </div>
    </div>
    <div class="row" id="legend" style="display:none;margin-bottom: 15px;">
            <div class="col-md-4">
                <div style="height:35px;background:linear-gradient(135deg, white, green 150%);">
                    Объект закрыт
                </div>

            </div>
            <div class="col-md-4">
                <div style="height:35px;background:linear-gradient(135deg, white, #414099 150%);">
                    Объект в работе
                </div>
            </div>
    </div>
    <table class="table table-striped one-touch-view " id="buildersList">
        <thead>
        <tr>
            <th>
               Название
            </th>
            <th>
               Телефоны
            </th>
            <th>
               Дата создания
            </th>
            <th>
                Мастер
            </th>
        </tr>
        </thead>
        <tbody id="tbody_builders">
            <?php
                foreach ($result_clients as $key => $value)
                {
                    $groups = explode(';',$value->groups);
                    if(!in_array(36,$groups)) {
                        ?>
                        <tr class="row" data-id="<?=$value->id;?>" data-cl_id="<?=$value->associated_client?>"
                            data-href="<?php echo JRoute::_('index.php?option=com_gm_ceiling&view=clientcard&type=builder&id=' . (int)$value->associated_client); ?>">
                            <td>
                                <?php echo $value->name; ?>
                            </td>
                            <td>
                                <?php echo $value->client_contacts; ?>
                            </td>
                            <td>
                                <?php
                                if ($value->created == "0000-00-00 00:00:00") {
                                    echo "-";
                                } else {
                                    $jdate = new JDate($value->created);
                                    $created = $jdate->format("d.m.Y H:i");
                                    echo $created;
                                }
                                ?>
                            </td>
                            <td>
                                <div class="row">
                                    <div class="col-md-10">
                                        <?= empty($value->manager) ? '-' : $value->manager;?>
                                    </div>
                                    <div class="col-md-2">
                                        <button class="btn-sm btn-primary edit_master" <?=$disabled;?>>
                                            <i class="fas fa-user-edit"></i>
                                        </button>
                                    </div>
                                </div>

                            </td>
                        </tr>
                        <?php
                    }
                }
            ?>
        </tbody>
    </table>
    <div class="modal_window_container" id="mw_container">
        <button type="button" class="btn-close" id="close"><i class="fa fa-times fa-times-tar" aria-hidden="true"></i></button>
        <div class="modal_window" id="mw_create">
                <p><strong>Создание нового застройщика</strong></p>
                <p>Название:</p>
                <p><input type="text" id="fio_builder"></p>
                <p>Номер телефона:</p>
                <p><input type="text" id="builder_contacts"></p>
                <p><button type="submit" id="save_builder" class="btn btn-primary">ОК</button></p>
        </div>
        <div class="modal_window" id="mw_objMaster">
            <input type="hidden" id="selected_object" value="">
            <div class="row">
                <h4>Назначение/изменение мастера на объекте <span id="selected_name"></span></h4>
            </div>
            <div class="row" style="margin-bottom: 1em !important;">
                <div class="col-md-3"></div>
                <div class="col-md-6">
                    <select class="form-control" id="masters_select">
                        <option value="">Выберите мастера</option>
                        <?php foreach ($objMasters as $master){?>
                            <option value="<?=$master->id?>"><?=$master->name?></option>
                        <?php }?>
                    </select>
                </div>
                <div class="col-md-3"></div>
            </div>
            <div class="row center">
                <div class="col-md-12">
                    <button class="btn btn-primary" id="save_obj_master">
                        <i class="far fa-save"></i> Сохранить
                    </button>
                </div>
            </div>
        </div>
    </div>

<script>
    jQuery(document).mouseup(function (e){ // событие клика по веб-документу
        var div1 = jQuery("#mw_create"),
            div2 = jQuery('#mw_objMaster');
        if (!div1.is(e.target)
            && div1.has(e.target).length === 0
            && !div2.is(e.target)
            && div2.has(e.target).length === 0) {
            jQuery("#close").hide();
            jQuery("#mw_container").hide();
            div1.hide();
            div2.hide();
        }
    });

    jQuery(document).ready(function()
    {
        var builders = JSON.parse('<?=quotemeta(json_encode($result_clients))?>');
        console.log(builders);
        jQuery('#builder_contacts').mask('+7(999) 999-9999');
        jQuery('body').on('click', 'tr', function(e)
        {
            if(jQuery(this).data('href')!=""){
                document.location.href = jQuery(this).data('href');
            } 
        });

        jQuery("#show_type").change(function(){
            var builders_to_show = [];
            if(this.value == 0){
                jQuery("#legend").show();
                fillTable(builders);
            }
            if(this.value == 1){
                jQuery.each(builders,function(index,elem){
                    var groups = elem.groups.split(';'),
                        check = groups.find(function(g){
                            return g == 36;
                        });
                    console.log(check);
                    if(empty(check)){
                        builders_to_show.push(elem);
                    }
                });
                jQuery("#legend").hide();
                fillTable(builders_to_show);
            }
            if(this.value == 2){
                jQuery.each(builders,function(index,elem){
                    var groups = elem.groups.split(';'),
                        check = groups.find(function(g){
                            return g == 36;
                        });
                    console.log(check);
                    if(!empty(check)){
                        builders_to_show.push(elem);
                    }
                });
                jQuery("#legend").hide();
                fillTable(builders_to_show);
            }
        });

        jQuery("#new_builder").click(function(){
            jQuery("#close").show();
            jQuery("#mw_container").show();
            jQuery("#mw_create").show("slow");
        });

        jQuery("#save_builder").click(function(){
            jQuery.ajax({
                type: 'POST',
                url: "index.php?option=com_gm_ceiling&task=dealer.create_designer",
                data: {
                    fio: document.getElementById('fio_builder').value,
                    phone: document.getElementById('builder_contacts').value,
                    designer_type: 7
                },
                success: function(data){
                    if (data == 'client_found')
                    {
                        var n = noty({
                            timeout: 2000,
                            theme: 'relax',
                            layout: 'center',
                            maxVisible: 5,
                            type: "error",
                            text: "Клиент с таким номером существует!"
                        });
                    }
                    else
                    {
                        location.reload();
                    }
                },
                dataType: "text",
                async: false,
                timeout: 10000,
                error: function(data){
                    var n = noty({
                        timeout: 2000,
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "error",
                        text: "Ошибка. Сервер не отвечает"
                    });
                }                   
            });
        });

        jQuery("#find_builder").click(function(){
            jQuery.ajax({
                type: 'POST',
                url: "index.php?option=com_gm_ceiling&task=findOldClients",
                data: {
                    fio: document.getElementById('name_find_builder').value,
                    flag: 'builders'
                },
                success: function(data){
                    console.log(data);
                    var tbody = document.getElementById('tbody_builders');
                    tbody.innerHTML = '';
                    var html = '';
                    for(var i in data)
                    {
                        html += '<tr data-href="/index.php?option=com_gm_ceiling&view=clientcard&type=builder&id=' + data[i].id + '">';
                        html += '<td>' + data[i].client_name + '</td>';
                        html += '<td>' + data[i].client_contacts + '</td>';
                        html += '<td>' + data[i].created + '</td></tr>';
                    }
                    tbody.innerHTML = html;
                    html = '';
                },
                dataType: "json",
                async: false,
                timeout: 20000,
                error: function(data){
                    var n = noty({
                        timeout: 2000,
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "error",
                        text: "Ошибка. Сервер не отвечает"
                    });
                }                   
            });
        });

        jQuery('.edit_master').click(function () {
            jQuery("#close").show();
            jQuery("#mw_container").show();
            jQuery("#mw_objMaster").show();
            var selectedObjId = jQuery(this).closest('tr').data('id'),
                selectedName = builders.find(function(e){if(e.id == selectedObjId){return e}}).name,
                selectedClientId = jQuery(this).closest('tr').data('cl_id');
            console.log(selectedObjId,selectedName);
            jQuery('#selected_object').val(selectedClientId);
            jQuery('#selected_name').text(selectedName);
            return false;
        });

        jQuery('#save_obj_master').click(function(){
            var objMasterId = jQuery('#masters_select').val(),
                clientId = jQuery('#selected_object').val();

            console.log(objMasterId);
            if(!empty(objMasterId)){
                updateManager(clientId,objMasterId);
            }
            else{
                noty({
                    theme: 'relax',
                    layout: 'center',
                    timeout: false,
                    type: "info",
                    text: "Мастер будет снят с данного объекта.Продолжить?",
                    buttons: [
                        {
                            addClass: 'btn btn-primary', text: 'Продолжить', onClick: function ($noty) {
                                updateManager(clientId,null);
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
            }
        });

        function updateManager(clientId,managerId){
            jQuery.ajax({
                url: "index.php?option=com_gm_ceiling&task=client.updateManager",
                data: {
                    client_id: clientId,
                    manager_id: managerId
                },
                dataType: "json",
                async: true,
                success: function (data) {
                    noty({
                        timeout: 2000,
                        theme: 'relax',
                        layout: 'topCenter',
                        maxVisible: 5,
                        type: "success",
                        text: "Сохранено!"
                    });
                    setTimeout(function(){location.reload();},2000);
                },
                error: function (data) {
                    console.log(data);
                    noty({
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
    });
    function fillTable(builders){
        jQuery("#tbody_builders").empty();
        var showAll = jQuery("#show_type").val() == 0,
            style = '';
        jQuery.each(builders,function (index,builder) {
            if(showAll){
                var groups = builder.groups.split(';'),
                    check = groups.find(function(g){
                        return g == 36;
                    });
                if(!empty(check)){
                    style = 'background:linear-gradient(135deg, white, green 150%);'
                }
                else{
                    style = 'background:linear-gradient(135deg, white, #414099 150%);'

                }
            }
           jQuery("#tbody_builders").append('<tr style="'+style+'" data-href="/index.php?option=com_gm_ceiling&view=clientcard&type=builder&id='+builder.associated_client+'"></tr>');
           jQuery('#tbody_builders > tr:last').append('<td>'+builder.name+'</td><td>'+builder.client_contacts+'</td><td>'+builder.created+'</td>');
        });
    }
</script>