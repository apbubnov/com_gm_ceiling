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
?>
    <a class="btn btn-large btn-primary"
       href="/index.php?option=com_gm_ceiling&view=mainpage&type=gmmanagermainpage"
       id="back"><i class="fa fa-arrow-left" aria-hidden="true"></i> Назад</a>
    <h2 class="center">Застройщики</h2>
    <div class="row" style="margin-bottom: 15px;">
        <div class="col-md-4">
            <button type="button" id="new_builder" class="btn btn-primary">Добавить застройщика</button>
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
               Дата регистрации
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
                        <tr class="row"
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
                        </tr>
                        <?php
                    }
                }
            ?>
        </tbody>
    </table>
    <div id="modal-window-container">
        <button type="button" id="close4-tar"><i class="fa fa-times fa-times-tar" aria-hidden="true"></i></button>
        <div id="modal-window-1-tar">
                <p><strong>Создание нового застройщика</strong></p>
                <p>Название:</p>
                <p><input type="text" id="fio_builder"></p>
                <p>Номер телефона:</p>
                <p><input type="text" id="builder_contacts"></p>
                <p><button type="submit" id="save_builder" class="btn btn-primary">ОК</button></p>
        </div>
    </div>

<script>
    jQuery(document).mouseup(function (e){ // событие клика по веб-документу
        var div3 = jQuery("#modal-window-1-tar"); // тут указываем ID элемента
        if (!div3.is(e.target) // если клик был не по нашему блоку
            && div3.has(e.target).length === 0) { // и не по его дочерним элементам
            jQuery("#close4-tar").hide();
            jQuery("#modal-window-container").hide();
            jQuery("#modal-window-1-tar").hide();
        }
    });

    jQuery(document).ready(function()
    {
        var builders = JSON.parse('<?=json_encode($result_clients)?>');
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
            jQuery("#close4-tar").show();
            jQuery("#modal-window-container").show();
            jQuery("#modal-window-1-tar").show("slow");
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