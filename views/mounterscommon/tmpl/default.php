<?php
/**
 * Created by PhpStorm.
 * User: bubnov
 * Date: 24.05.2019
 * Time: 11:46
 */
$model = Gm_ceilingHelpersGm_ceiling::getModel('mounterscommon');
$items = $model->getData();
$mountersdebtModel = Gm_ceilingHelpersGm_ceiling::getModel('mountersdebt');
$types = $mountersdebtModel->getTypes();
?>
<style>
    .debt_type{
        width: 100%;
        vertical-align: middle;
    }
</style>
<h3>Сводная таблица по монтажным бригадам </h3>
<table class="table table_cashbox">
    <thead>
        <tr>
            <th class="center">
                Монтажник
            </th>
            <th class="center">
                Объект
            </th>
            <th class="center">
                В работе
            </th>
            <th class="center">
                Закрыто
            </th>
            <th class="center">
                Выплачено
            </th>
            <th class="center">
                Остаток
            </th>
            <th class="center">
                Закрыть
            </th>
            <th class="center">
                Долг
            </th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($items as $mounter_id => $item) {
        $total_taken = 0;$total_closed = 0;$total_payed = 0;$total_rest = 0;
        ?>
        <tr data-mounter_id="<?php echo $mounter_id;?>">
            <td rowspan="<?php echo count($item['builder_data'])?>">
                <?php echo $item['mounter_name'];?>
            </td>
            <?php foreach ($item['builder_data'] as $key=>$value){?>
                <?php if($rowspan) echo '<tr data-mounter_id="'.$mounter_id.'">'?>
                <td  class = "builder builder_name" data-builder_id = "<?php echo $value->builder_id?>" data-builder_client_id = "<?php echo JFactory::getUser($value->builder_id)->associated_client;?>">
                    <?php echo !empty($value->builder_name) ? $value->builder_name : "-";?>
                </td>
                <td class = "builder">
                    <?php if(!empty($value->taken)){
                        $total_taken += $value->taken;
                        echo round($value->taken,2);
                    }
                    else{
                        echo "0";
                    }
                    ?>
                </td>
                <td class = "builder">
                    <?php if(!empty($value->closed)){
                        $total_closed += $value->closed;
                        echo round($value->closed,2);
                    }
                    else{
                        echo "0";
                    }
                    ?>
                </td>
                <td class="payed builder">
                    <?php if(!empty($value->payed)){
                        $total_payed += $value->payed;
                        echo round($value->payed,2);

                    }
                    else{
                        echo "0";
                    }
                    ?>
                </td>
                <td class="rest builder">
                    <?php if (!empty($value->rest)){
                        $total_rest += $value->rest;
                        echo round($value->rest,2);
                    }
                    ?>
                </td>
                <td>
                    <div class="row">
                        <div class="col-xs-10 col-md-10">
                            <input class="input-gm close_sum" style="max-width: 100%;">
                        </div>
                        <div class="col-xs-2 col-md-2" style="padding-left: 0;">
                            <button class="btn btn-primary btn-sm save_sum"><i class="far fa-save"></i></button>
                        </div>
                    </div>
                    <?php if(!empty($item['mounter_debt'])){ ?>
                    <div class="row">
                        <div class="col-xs-12 col-md-12">
                            <input type="checkbox" id="auto_<?=$value->builder_id?>" class="inp-cbx auto_debt_relief" checked style="display: none">
                            <label for="auto_<?=$value->builder_id?>" class="cbx">
                                                    <span>
                                                        <svg width="12px" height="10px" viewBox="0 0 12 10">
                                                            <polyline points="1.5 6 4.5 9 10.5 1"></polyline>
                                                        </svg>
                                                    </span>
                                <span>Автосписание долга</span>
                            </label>
                        </div>
                    </div>
                    <?php }?>

                </td>
                <?php if($key == 0){?>
                    <td rowspan="<?php echo count($item['builder_data'])+1?>">

                        <div class="row" style="margin-bottom: 5px;">
                            <div class="col-md-12">
                                <input class="input-gm debt_sum">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xs-9 col-md-9" style="padding-right: 2px;">
                                <select class="input-gm debt_type">
                                    <?php foreach ($types as $type){
                                        echo "<option value='$type->id'>$type->title</option>";
                                    }?>
                                </select>
                            </div>
                            <div class="col-xs-3 col-md-3" style="padding-left: 0;">
                                <button class="btn btn-primary btn-sm save_debt_sum"><i class="far fa-save"></i></button>
                            </div>
                        </div>
                        <?php if(!empty($item['mounter_debt'])) {?>
                            <div class="row">
                                <b>Остаток:</b>
                                <span class="debt_rest"><?php echo (!empty($item['mounter_debt'])) ? $item['mounter_debt'] : 0;?></span>
                            </div>
                            <div class="row">
                                <button class="btn btn-primary btn-sm debt_detailed">Детализация</button>
                            </div>
                        <?php }?>
                    </td>
                <?php }?>
            <?php if(count($item > 1)){
                $rowspan = true;
                echo '</tr>';
             } ?>
            <?php }?>
            <?php $rowspan = false;?>

        </tr>
        <tr>
            <td colspan="2">
                <b>Итого</b>
            </td>
            <td><?=round($total_taken,2);?></td>
            <td><?=round($total_closed,2);?></td>
            <td><?=round($total_payed,2);?></td>
            <td><?=round($total_rest,2);?></td>
            <td>-</td>
        </tr>
    <?php } ?>
    </tbody>
</table>
<div id="mw_container" class="modal_window_container">
    <button type="button" id="close" class="close_btn"><i class="fa fa-times fa-times-tar" aria-hidden="true"></i></button>
    <div id="one_mounter_salary" class="modal_window">
        <table id="detailed_salary" class="table_project_analitic">
            <thead>
            <tr class="caption_table">
                <td>
                    Сумма
                </td>
                <td>
                    Объект
                </td>
                <td>
                    Время
                </td>
            </tr>
            </thead>
            <tbody>

            </tbody>
        </table>
    </div>
    <div id="detailed_debt" class="modal_window">
        <table id="mounter_debt_detailed" class="table_project_analitic">
            <thead>
                <tr class="caption_table">
                    <th class="center">
                        Сумма
                    </th>
                    <th class="center">
                        Тип
                    </th>
                    <th class="center">
                        Дата
                    </th>
                </tr>
            </thead>
            <tbody>

            </tbody>
        </table>
    </div>
</div>
<script>
    var data = JSON.parse('<?php echo json_encode($items)?>');
    console.log(data);
    jQuery(document).mouseup(function (e){ // событие клика по веб-документу
        var div = jQuery("#one_mounter_salary"),
            div1 = jQuery("#detailed_debt"); // тут указываем ID элемента
        if (!div.is(e.target) && div.has(e.target).length === 0 &&
            !div1.is(e.target) && div1.has(e.target).length === 0) {
            jQuery("#close").hide();
            jQuery("#mw_container").hide();
            div.hide();
            div1.hide();
        }
    });

    jQuery(document).ready(function () {
        console.log(data);
        jQuery(".save_sum").click(function () {
            var tr = jQuery(this).closest('tr'),
                mounter_id = tr.data('mounter_id'),
                builder_id = tr.find('.builder').data('builder_id'),
                rest = parseFloat(tr.find('.rest')[0].innerText),
                close_sum = parseFloat(tr.find('.close_sum').val()),
                debt_auto_relief = jQuery(this).closest('td').find('.auto_debt_relief').is(':checked');
            if(close_sum>0){
                close_sum = -close_sum;
            }
            make_pay(mounter_id,builder_id,close_sum,tr.find('.payed'),tr.find('.rest'),tr.find('.close_sum'),debt_auto_relief);
        });

        jQuery('.builder').click(function () {
            var tr = jQuery(this).closest('tr'),
                builder_id = tr.find('.builder_name').data('builder_id'),
                mounter_id = tr.data('mounter_id');
            console.log(builder_id,mounter_id);
            jQuery.ajax({
                url: "index.php?option=com_gm_ceiling&task=MountersSalary.getMounterSalaryByBuilder",
                data: {
                    mounterId: mounter_id,
                    builder_id: builder_id
                },
                dataType: "json",
                async: false,
                success: function(data) {
                    var total = 0,note = "";
                    console.log(data);
                    jQuery("#detailed_salary > tbody").empty();
                    jQuery.each(data,function (index,el){
                        total += +el.sum;
                        note = (!empty(el.note)) ? el.note : "Выплата";
                        jQuery("#detailed_salary > tbody").append('<tr/>');
                        jQuery("#detailed_salary > tbody > tr:last").append('<td>'+el.sum+'</td><td>'+note+'</td><td>'+el.datetime+'</td>')
                    });
                    jQuery("#detailed_salary > tbody").append('<tr/>');
                    jQuery("#detailed_salary > tbody > tr:last").append('<td align="right"><b>Итого:<b></td><td>'+total+'</td><td></td>');
                },
                error: function(data) {
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
            jQuery("#mw_container").show();
            jQuery("#one_mounter_salary").show();
            jQuery("#close").show();
        });

        jQuery('body').on('click','.debt_detailed',function(){
            var tr = jQuery(this).closest('tr'),
                mounter_id = tr.data('mounter_id');
            jQuery.ajax({
                url: "index.php?option=com_gm_ceiling&task=users.getMounterDebtData",
                data: {
                    mounterId: mounter_id
                },
                dataType: "json",
                async: false,
                success: function (responseData) {
                    jQuery("#mounter_debt_detailed > tbody").empty();
                    jQuery.each(responseData,function(index,elem){
                        jQuery("#mounter_debt_detailed > tbody").append('<tr>' +
                                                                            '<td>'+elem.sum+'</td>'+
                                                                            '<td>'+elem.title+'</td>'+
                                                                            '<td>'+elem.date_time+'</td>'+
                                                                        '</tr>');
                    });
                    console.log(responseData);
                    jQuery("#mw_container").show();
                    jQuery("#detailed_debt").show('slow');
                    jQuery("#close").show();
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
        jQuery('.save_debt_sum').click(function () {
            var tr = jQuery(this).closest('tr'),
                mounter_id = tr.data('mounter_id'),
                mounter_debt = data[mounter_id]['mounter_debt'],
                sum = parseFloat(tr.find('.debt_sum').val()),
                type = tr.find('.debt_type').val();
            console.log(empty(mounter_debt)&&type == 2);
            if(empty(mounter_debt)&&type == 2){
                noty({
                    timeout: 2000,
                    theme: 'relax',
                    layout: 'center',
                    maxVisible: 5,
                    type: "error",
                    text: "Долг отсутствует, нельзя провести его списание!"
                });
                return false;
            }

            saveDebtSum(mounter_id, sum, tr,type);
        });
    });

    function check_pay_possibility(current_builder,close_sum){
        var unclosed_sum = current_builder.taken - current_builder.closed,
            available_sum = unclosed_sum + current_builder.rest;
        /*console.log('unclosed',unclosed_sum);
        console.log('available',available_sum);
        console.log(available_sum >= Math.abs(close_sum));*/
        if(available_sum >= Math.abs(close_sum)) {
            return true;
        }
        else return false;
    }
    function make_pay(mounter_id,builder_id,close_sum,payed_td,rest_td,input,debt_auto_relied) {
        var mounter_data = data[mounter_id],
            percent_sum = 0,
            tr = rest_td.closest('tr'),
            debt_relief_sum = 0;
        if(!empty(mounter_data['mounter_debt']) && debt_auto_relied){
            debt_relief_sum = Math.abs(close_sum)*0.25;
            if(mounter_data['mounter_debt'] >= debt_relief_sum){
                percent_sum = Math.abs(close_sum)*0.25;
            }
            else{
                percent_sum = mounter_data['mounter_debt'];
            }
        }
        close_sum -= percent_sum;
        if(mounter_data['builder_data'].length == 1){
            if(check_pay_possibility(mounter_data['builder_data'][0],close_sum)){
                savePay(mounter_id,close_sum,mounter_data['builder_data'][0].builder_id,payed_td,rest_td,mounter_data['builder_data'][0]);
                input.val("");
                var n = noty({
                    timeout: 2000,
                    theme: 'relax',
                    layout: 'center',
                    maxVisible: 5,
                    type: "success",
                    text: "Успешно!"
                });
                if(!empty(percent_sum)){
                    saveDebtSum(mounter_id, percent_sum, tr,2);
                }
            }
            else{
                var n = noty({
                    timeout: 2000,
                    theme: 'relax',
                    layout: 'center',
                    maxVisible: 5,
                    type: "error",
                    text: "Введенная сумма больше чем остаток и сумма взятого,но не закрытого объема!"
                });
            }
        }
        else{
            var current_builder = mounter_data['builder_data'].find(function(element,index){
                var result = null;
                if(element.builder_id == builder_id){
                    result = element;
                }
                return result;
            });
            if(check_pay_possibility(current_builder,close_sum)){
                savePay(mounter_id,close_sum,current_builder.builder_id,payed_td,rest_td,current_builder);
                var n = noty({
                    timeout: 2000,
                    theme: 'relax',
                    layout: 'center',
                    maxVisible: 5,
                    type: "success",
                    text: "Успешно!"
                });
                input.val("");
                if(!empty(percent_sum)){
                    saveDebtSum(mounter_id, percent_sum, tr,2);
                }
            }
            else{
                /*проверить больше ли общая доступная сумма по всем объектам суммы выплаты*/
                var total_available_sum = 0;
                for(var i=mounter_data['builder_data'].length;i--;){
                    total_available_sum += mounter_data['builder_data'][i].taken - mounter_data['builder_data'][i].closed + mounter_data['builder_data'][i].rest;
                }
                if(total_available_sum>Math.abs(close_sum)) {
                    /*списать часть*/
                    var available_sum = current_builder.taken-current_builder.closed + current_builder.rest;
                    savePay(mounter_id,0-available_sum,current_builder.builder_id,payed_td,rest_td,current_builder);
                    close_sum += available_sum;
                    jQuery.each(mounter_data['builder_data'], function (index, builder) {
                        if (builder.builder_id != current_builder.builder_id && close_sum != 0) {
                            if(check_pay_possibility(builder,close_sum)){
                                savePay(mounter_id,close_sum,builder.builder_id,payed_td,rest_td,builder);
                            }
                            else{
                                available_sum = builder.taken-builder.closed + builder.rest;
                                savePay(mounter_id,0-available_sum,builder.builder_id,payed_td,rest_td,current_builder);
                            }
                        }
                    });
                    var n = noty({
                        timeout: 2000,
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "success",
                        text: "Успешно!"
                    });
                    input.val("");
                    if(!empty(percent_sum)){
                        saveDebtSum(mounter_id, percent_sum, tr,2);
                    }
                }
                else {
                    var n = noty({
                        timeout: 3000,
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "error",
                        text: "Введенная сумма больше чем остаток и сумма взятого,но не закрытого объема на всех объектах!"
                    });
                }
                /*savePay(mounter_id,0-unclosed_summ,current_builder.builder_id,payed_td,rest_td,current_builder);
                close_sum +=unclosed_sum+current_builder.rest;
                console.log(close_sum);*/
            }
        }
    }

    function savePay(mounter_id, paid_sum, builder_id,payed_td,rest_td,data) {
        jQuery.ajax({
            url: "index.php?option=com_gm_ceiling&task=MountersSalary.savePay",
            data: {
                mounter_id: mounter_id,
                paid_sum: paid_sum,
                builder_id: builder_id
            },
            dataType: "json",
            async: false,
            success: function (responseData) {
               /* data[0].payed = +data.payed + paid_sum-0;
                data[0].rest += +paid_sum;
                /!*payed_td[0].innerText = data.payed;
                rest_td[0].innerText = data.rest;*!/*/
                console.log(data);
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
    }

    function saveDebtSum(mounter_id, sum, tr, type) {
        jQuery.ajax({
            url: "index.php?option=com_gm_ceiling&task=users.saveMounterDebt",
            data: {
                mounterId: mounter_id,
                sum: sum,
                type: type
            },
            dataType: "json",
            async: false,
            success: function (data) {
                var n = noty({
                    timeout: 2000,
                    theme: 'relax',
                    layout: 'center',
                    maxVisible: 5,
                    type: "success",
                    text: "Сохранено!"
                });
                tr.find('.debt_sum').val('');
                var old_sum = tr.find('.debt_rest').text();
                if(type == 2){
                    tr.find('.debt_rest').text((+old_sum - sum).toFixed(2));

                }
                else{
                    tr.find('.debt_rest').text((+old_sum + sum).toFixed(2));
                }
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
    }
</script>