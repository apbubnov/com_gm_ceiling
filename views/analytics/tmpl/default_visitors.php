<?php
/**
 * Created by PhpStorm.
 * User: bubnov
 * Date: 27.01.2020
 * Time: 15:17
 */
$usersModel = Gm_ceilingHelpersGm_ceiling::getModel('users');
$date_from = date('Y-m-d');
$date_to = date('Y-m-d');
$visitors = $usersModel->getVisitors($date_from,$date_to);
?>
<div class="containder">
    <div class="row">
        <div class="col-md-8"></div>
        <div class="col-md-2">
            Начальная дата <input class="input-gm change_date" id="date_from" type="date" value="<?=$date_from;?>">
        </div>
        <div class="col-md-2">
            Конечная дата <input class="input-gm change_date" id="date_to" type="date" value="<?=$date_to;?>">
        </div>
    </div>
    <div class="row">
        <table class="analitic-table">
            <thead class="caption-style-analitic">
                <tr>
                    <th>Имя</th>
                    <th>Телефон(логин)</th>
                    <th>Город</th>
                    <th>Группы</th>
                    <th>Дата входа</th>
                </tr>
            </thead>
            <tbody id="tbody_visitors">
                <?php foreach ($visitors as $visitor) { ?>
                    <tr class="href_tr" data-id="<?=$visitor->associated_client?>">
                        <td><?=$visitor->name?></td>
                        <td><?=$visitor->username?></td>
                        <td><?=(!empty($visitor->city)?$visitor->city:(!empty($visitor->city1)? $visitor->city1 : '-'))?></td>
                        <td><?=$visitor->groups?></td>
                        <td><?=$visitor->visit_date?></td>
                    </tr>
                <?php } ?>

            </tbody>
        </table>
    </div>
</div>
<script type="text/javascript">
    jQuery(document).ready(function(){
        jQuery('.change_date').change(function(){
            var date_from = jQuery('#date_from').val(),
                date_to = jQuery('#date_to').val();
            if(date_from <= date_to){
                jQuery.ajax({
                    url: "index.php?option=com_gm_ceiling&task=users.getVisitors",
                    data: {
                        date_from: date_from,
                        date_to: date_to
                    },
                    dataType: "json",
                    async: true,
                    success: function(data) {
                        console.log(data);
                        jQuery('#tbody_visitors').empty();
                        jQuery.each(data,function(index,elem){
                            var city = !empty(elem.city) ? elem.city : (!empty(elem.city1) ? elem.city1 : '');
                            jQuery('#tbody_visitors').append('<tr class="href_tr" data-id="'+elem.associated_client+'"></tr>');
                            jQuery('#tbody_visitors > tr:last').append('<td>'+elem.name+'</td>' +
                                                                        '<td>'+elem.username+'</td>' +
                                                                        '<td>'+city+'</td>'+
                                                                        '<td>'+elem.groups+'</td>'+
                                                                        '<td>'+elem.visit_date+'</td>');
                        });
                    },
                    error: function(data) {
                        console.log(data);
                        var n = noty({
                            timeout: 2000,
                            theme: 'relax',
                            layout: 'topCenter',
                            maxVisible: 5,
                            type: "error",
                            text: "Ошибка получения данных!"
                        });
                    }
                });
            }
            else{
                noty({
                    timeout: 2000,
                    theme: 'relax',
                    layout: 'topCenter',
                    maxVisible: 5,
                    type: "error",
                    text: 'Начальная дата не может быть больше конечной!'
                });
            }
        });

        jQuery('body').on('click','.href_tr',function () {
            var id = jQuery(this).data('id');
            if(!empty(id)){
                location.href='/index.php?option=com_gm_ceiling&view=clientcard&type=dealer&id='+id;
            }
            else{
                noty({
                    timeout: 2000,
                    theme: 'relax',
                    layout: 'topCenter',
                    maxVisible: 5,
                    type: "error",
                    text: 'Данный пользователь не является дилером!'
                });
            }
        });
    });
</script>
