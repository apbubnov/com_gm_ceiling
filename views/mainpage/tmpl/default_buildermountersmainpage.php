<?php
/**
 * Created by PhpStorm.
 * User: bubnov
 * Date: 18.03.2019
 * Time: 14:56
 */
$clients_model = Gm_ceilingHelpersGm_ceiling::getModel('users');
$result_clients = $clients_model->getBuilders();
?>
<h2 class="center">Застройщики</h2>
<div class="row" style="margin-bottom: 15px;">
    <div class="col-md-4">
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
<h4 class="center">Объекты</h4>
<table class="table table-striped one-touch-view" id="callbacksList">
    <thead>
    <tr>
        <th class="center">
            Название
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

            <tr class="row center" data-href="<?php echo JRoute::_('index.php?option=com_gm_ceiling&view=clientcard&type=builder&subtype=mounter&id='.(int)$value->associated_client); ?>">
                <td class="center">
                    <?php echo $value->name; ?>
                </td>
            </tr>
            <?php
        }
    }
    ?>
    </tbody>
</table>
<script>
    jQuery(document).ready(function() {
        var builders = JSON.parse('<?=json_encode($result_clients)?>');
        console.log(builders);
        jQuery('body').on('click', 'tr', function (e) {
            if (jQuery(this).data('href') != "") {
                document.location.href = jQuery(this).data('href');
            }
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

                        html += '<tr  data-href="/index.php?option=com_gm_ceiling&view=clientcard&type=builder&subtype=mounter&id=' + data[i].id + '">';
                        html += '<td>' + data[i].client_name + '</td>';
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
                jQuery('#tbody_builders > tr:last').append('<td>'+builder.name+'</td>');
            });
        }

    });
</script>