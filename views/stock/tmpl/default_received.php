<?php
    $today = date('Y-m-d');
    $modelStock = Gm_ceilingHelpersGm_ceiling::getModel('stock');
    $data = $modelStock->getReceivedGoods(null,null,$today);
    $stocks = $modelStock->getStocks();
?>
<h4>Список принятых товаров </h4>
<div class="row" style="margin-bottom: 15px;">
    <div class="col-md-6">
        <dov class="col-md-3"></dov>
        <div class="col-md-6">
            <input class="form-control" id="search">
        </div>
        <div class="col-md-3">
            <button class="btn btn-primary" id="find">Найти</button>
        </div>
    </div>
    <div class="col-md-6">
        <div class="col-md-6">
            <input type="date" id="date_from" class="date form-control">
        </div>
        <div class="col-md-6">
            <input type="date" id="date_to" class="date form-control">
        </div>
    </div>
</div>
<div class="row">
    <table class="table table_cashbox" id="reception_table">
        <thead>
            <th>
                Наименование
            </th>
            <th>
                Цена закупки
            </th>
            <th>
                Количество
            </th>
            <th>
                Склад
            </th>
            <th>
                Дата
            </th>
        </thead>
        <tbody>
            <?php foreach ($data as $item) {?>
                <tr data-id="<?=$item->id?>">
                    <td class="name">
                        <?=$item->name;?>
                    </td>
                    <td class="cost_price">
                        <?=$item->cost_price;?>
                    </td>
                    <td class="received_count">
                        <?=$item->received_count;?>
                    </td>
                    <td class="stock">
                        <?=$item->stock_name;?>
                    </td>
                    <td>
                        <?=date('d.m.Y H:i:s',strtotime($item->date_time));?>
                    </td>
                </tr>
            <?php }?>
        </tbody>
    </table>
</div>
<div class="modal_window_container" id="mw_container">
    <button type="button" class="close_btn" id="close_mw"><i class="fa fa-times fa-times-tar" aria-hidden="true"></i></button>
    <div class="modal_window" id="mw_change">
        <input type="hidden" id="reception_id">
        <div class="row" style="margin-bottom: 15px;">
            <div class="col-md-3">
                <div class="col-md-12">
                    <label><b>Наименование</b></label>
                </div>
                <div class="col-md-12">
                    <select class="form-control" id="goods"></select>
                </div>
            </div>
            <div class="col-md-3">
                <div class="col-md-12">
                    <label><b>Цена закупки</b></label>
                </div>
                <div class="col-md-12">
                    <input class="form-control" id="cost_price">
                </div>
            </div>
            <div class="col-md-3">
                <div class="col-md-12">
                    <label><b>Количество</b></label>
                </div>
                <div class="col-md-12">
                    <input class="form-control" id="count">
                </div>
            </div>
            <div class="col-md-3">
                <div class="col-md-12">
                    <label><b>Склад</b></label>
                </div>
                <div class="col-md-12">
                    <select class="form-control" id="stock">
                        <?php foreach ($stocks as $stock){?>
                            <option value="<?=$stock->id?>"><?=$stock->name?></option>
                        <?php }?>
                    </select>
                </div>

            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <button class="btn btn-primary" id="save_changes">Сохранить изменения</button>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    var received_data = JSON.parse('<?= json_encode($data);?>');
    jQuery(document).ready(function(){
        jQuery('.date').change(function(){
            var date_from = jQuery('#date_from').val(),
                date_to = jQuery('#date_to').val(),
                search = jQuery('#search').val();
            jQuery.ajax({
                url: "index.php?option=com_gm_ceiling&task=stock.getReceivedGoods",
                data: {
                    date_from: date_from,
                    date_to: date_to,
                    search: search
                },
                dataType: "json",
                async: true,
                success: function (data) {
                   received_data = data;
                   jQuery('#reception_table > tbody').empty();
                   jQuery.each(data,function (n,e) {
                       jQuery('#reception_table > tbody').append(
                           '<tr data-id="'+e.id+'">' +
                               '<td class="name">'+e.name+'</td>'+
                               '<td class="cost_price">'+e.cost_price+'</td>'+
                               '<td class="received_count">'+e.received_count+'</td>'+
                               '<td class="stock">'+e.stock_name+'</td>'+
                               '<td>'+e.date_time+'</td>'+
                            '</tr>'
                       );

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
                        text: "Ошибка отправки"
                    });
                }
            });
        });

        jQuery('#close_mw').click(function(){
            jQuery('.modal_window_container').hide();
        });
        jQuery('#reception_table > tbody').on('click','tr',function(){
            var id = jQuery(this).data('id'),
                received_item = received_data[id],
                sameCategoryGoods = getGoodsByCategory(received_item.category_id);
            if(!empty(sameCategoryGoods)){
                jQuery.each(sameCategoryGoods,function (n,e) {
                    var selected = '';
                    if(received_item.goods_id == e.id){
                        selected = 'selected=true';
                    }
                    jQuery('#goods').append('<option '+selected+'  value="'+e.id+'">'+e.name+'</option>');
               });
            }
            jQuery('#cost_price').val(received_item.cost_price);
            jQuery('#count').val(received_item.received_count);
            jQuery('#stock option[value="'+received_item.stock_id+'"] ').attr('selected',true);
            jQuery('#reception_id').val(received_item.id);
            jQuery('#mw_container').show();
            jQuery('#close_mw').show();
            jQuery('#mw_change').show('slow');

        });

        jQuery('#save_changes').click(function () {
            var oldData = received_data[jQuery('#reception_id').val()],
                newGoods = jQuery('#goods').val(),
                newCount = jQuery('#count').val(),
                newCost = jQuery('#cost_price').val(),
                newStock = jQuery('#stock').val(),
                sendData = {};
                if(newGoods != oldData.goods_id){
                    sendData['goods_id'] = newGoods;
                }
                if(newCost != oldData.cost_price){
                    sendData['cost'] = newCost;
                }
                if(newCount != oldData.count){
                    sendData['count'] = newCount;
                }
                if(newStock != oldData.stock_id){
                    sendData['stock'] = newStock;
                }
                console.log(sendData);
                if(!empty(sendData)){
                    sendData['id'] = oldData.id;
                    sendData['inventory'] = oldData.inventory_id;
                    jQuery.ajax({
                        url: "index.php?option=com_gm_ceiling&task=stock.updateReceived",
                        data: sendData,
                        dataType: "json",
                        async: false,
                        success: function (data) {
                            jQuery('#mw_container').hide();
                            jQuery('#mw_change').hide();
                            var tr = jQuery('tr[data-id="'+data.id+'"]'),
                                name_td = tr.find('.name'),
                                cost_td = tr.find('.cost_price'),
                                count_td = tr.find('.received_count'),
                                stock_td = tr.find('.stock');
                            name_td.text(data.name);
                            cost_td.text(data.cost_price);
                            count_td.text(data.received_count);
                            stock_td.text(data.stock_name);
                            received_data[data.id] = data;
                        },
                        error: function (data) {
                        }
                    });
                }
        });
        function getGoodsByCategory(category){
            var result = [];
            jQuery.ajax({
                url: "index.php?option=com_gm_ceiling&task=stock.getGoodsByCategory",
                data: {
                    category: category
                },
                dataType: "json",
                async: false,
                success: function (data) {
                    result = data;
                },
                error: function (data) {
                }
            });

            return result;
        }

    });
</script>