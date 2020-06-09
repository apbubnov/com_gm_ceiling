<?php
$modelStock = Gm_ceilingHelpersGm_ceiling::getModel('stock');
$data = $modelStock->getRests();
$today = date('Y-m-d');
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
                <tr>
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
                        <?=$item->received_count - $item->sale_count;?>
                    </td>
                </tr>
            <?php }?>
        </tbody>
    </table>
</div>
<script type="text/javascript">
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
                        '<tr data-id="'+e.id+'">' +
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