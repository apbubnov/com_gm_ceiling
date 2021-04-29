<?php
$modelStock = Gm_ceilingHelpersGm_ceiling::getModel('stock');

$today = date('Y-m-d');
$data = $modelStock->getRests($today);
?>
<h4>Остатки товаров на <span id="date_span"><?= date('d.m.Y')?></span></h4>
<div class="row" style="margin-bottom: 15px;">
    <div class="col-md-6">
        <div class="col-md-3">Поиск</div>
        <div class="col-md-6">
            <input class="form-control" id="search">
        </div>
        <div class="col-md-3">
            <button class="btn btn-primary" id="find">Найти</button>
        </div>
    </div>
    <div class="col-md-6">
        <div class="col-md-6">
            <!-- <input type="date" id="date_from" class="date form-control">-->
        </div>
        <div class="col-md-6">
            <input type="date" id="date_to" class="date form-control" value="<?=$today;?>">
        </div>
    </div>
</div>
<div class="row">
    <table class="table table_cashbox" id="rests_table">
        <thead>
        <th>
            Наименование
        </th>
        <th>
            Всего принято
        </th>
        <th>
            Списано
        </th>
        <th>
            Остаток
        </th>
        </thead>
        <tbody>
        <?php foreach ($data as $item) {?>
            <tr data-id = "<?=$item->id?>" class="goods_row">
                <td>
                    <?=$item->name;?>
                </td>
                <td>
                    <?=$item->received_count;?>
                </td>
                <td>
                    <?=$item->sale_count;?>
                </td>
                <td>
                    <?=$item->rest_count;?>
                </td>
            </tr>
        <?php }?>
        </tbody>
    </table>
</div>
<script type="text/javascript">
    function formatDate(date) {
        var dd = date.getDate();
        if (dd < 10) dd = '0' + dd;

        var mm = date.getMonth() + 1;
        if (mm < 10) mm = '0' + mm;
        var yy = date.getFullYear();
        if (yy < 10) yy = '0' + yy;

        var hh = date.getHours();
        if (hh < 10) hh = '0' + hh;

        var ii = date.getMinutes();
        if (ii < 10) ii = '0' + ii;

        var ss = date.getSeconds();
        if (ss < 10) ss = '0' + ss;

        return dd + '.' + mm + '.' + yy + ' ' + hh + ':' + ii + ':' + ss;
    }
    jQuery(document).ready(function () {
        jQuery('.date').change(function () {
            var date = jQuery('#date_to').val(),
                formattedDate = new Date(date),
                search = jQuery('#search').val();
            jQuery('#date_span').text(formatDate(formattedDate));
            getRestData(date,search);
        });

        jQuery('#find').click(function(){
            var date = jQuery('#date_to').val(),
                search = jQuery('#search').val();
            getRestData(date,search);
        });

        jQuery('#rests_table').on('click','.goods_row',function(){
            var thisTr = jQuery(this),
                goodsId = thisTr.data('id'),
                date = jQuery('#date_to').val(),
                actionsInfoRow = jQuery('.goods_actions_info[data-goods_id="'+goodsId+'"]');
            if(actionsInfoRow.length == 0){
                jQuery.ajax({
                    url: "index.php?option=com_gm_ceiling&task=goods.getActionsOnGoodsInfo",
                    data: {
                        date: date,
                        goods_id: goodsId
                    },
                    dataType: "json",
                    async: true,
                    success: function (data) {
                        if(!empty(data)){
                            var trHtml = '<tr class="goods_actions_info" data-goods_id="'+goodsId+'">' +
                                '<td colspan="4">' +
                                '<div class="row">' +
                                '<div class="col-md-3"><b>Тип</b></div>' +
                                '<div class="col-md-3"><b>Количество</b></div>' +
                                '<div class="col-md-3"><b>Информация</b></div>' +
                                '<div class="col-md-3"><b>Дата</b></div>' +
                                '</div>';
                            jQuery.each(data,function (n,el) {
                                trHtml += '<div class="row">' +
                                    '<div class="col-md-3">'+el.type+'</div>' +
                                    '<div class="col-md-3">'+el.count+'</div>' +
                                    '<div class="col-md-3">'+el.info+'</div>' +
                                    '<div class="col-md-3">'+el.date_time+'</div>' +
                                    '</div>';
                            });
                            trHtml += '</td></tr>';
                            thisTr.after(trHtml);
                        }
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
            }
            else{
                actionsInfoRow.remove();
            }
        })
    });

    function getRestData(date,search) {
        jQuery.ajax({
            url: "index.php?option=com_gm_ceiling&task=stock.getRests",
            data: {
                date_to: date,
                search: search
            },
            dataType: "json",
            async: true,
            success: function (data) {
                console.log(data);
                jQuery('#rests_table > tbody').empty();
                jQuery.each(data,function (n,e) {
                    jQuery('#rests_table > tbody').append(
                        '<tr data-id="'+e.id+'" class="goods_row">' +
                        '<td>'+e.name+'</td>'+
                        '<td>'+e.received_count+'</td>'+
                        '<td>'+e.sale_count+'</td>'+
                        '<td>'+(e.received_count - e.sale_count).toFixed(2)+'</td>'+
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

    }
</script>