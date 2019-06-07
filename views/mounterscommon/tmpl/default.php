<?php
/**
 * Created by PhpStorm.
 * User: bubnov
 * Date: 24.05.2019
 * Time: 11:46
 */
$model = Gm_ceilingHelpersGm_ceiling::getModel('mounterscommon');
$items = $model->getData();
?>
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
        </tr>
    </thead>
    <tbody>
    <?php foreach ($items as $mounter_id => $item) { ?>
        <tr data-mounter_id="<?php echo $mounter_id;?>">
            <td rowspan="<?php echo count($item['builder_data'])?>">
                <?php echo $item['mounter_name'];?>
            </td>
            <?php foreach ($item['builder_data'] as $key=>$value){?>
                <?php if($rowspan) echo '<tr data-mounter_id="'.$mounter_id.'">'?>
                <td  class = "builder" data-builder_id = "<?php echo $value->builder_id?>">
                    <?php echo !empty($value->builder_name) ? $value->builder_name : "-";?>
                </td>
                <td>
                    <?php echo !empty($value->taken) ? $value->taken : "0";;?>
                </td>
                <td>
                    <?php echo !empty($value->closed) ? $value->closed : "0" ;?>
                </td>
                <td class="payed">
                    <?php echo !empty($value->payed) ? $value->payed : "0";?>
                </td>
                <td class="rest">
                    <?php echo $value->rest;?>
                </td>
                <td>
                    <input class="input-gm close_sum">
                    <button class="btn btn-primary btn-sm save_sum">Save</button>
                </td>
            <?php if(count($item > 1)){
                $rowspan = true;
                echo '</tr>';
             } ?>
            <?php }?>
            <?php $rowspan = false;?>
        </tr>
    <?php } ?>
    </tbody>
</table>
<script>
    var data = JSON.parse('<?php echo json_encode($items)?>');
    jQuery(document).ready(function () {
        jQuery(".save_sum").click(function () {
            var tr = jQuery(this).closest('tr'),
                mounter_id = tr.data('mounter_id'),
                builder_id = tr.find('.builder').data('builder_id'),
                rest = parseFloat(tr.find('.rest')[0].innerText),
                close_sum = parseFloat(tr.find('.close_sum').val());
            if(close_sum>0){
                close_sum = -close_sum;
            }
            make_pay(mounter_id,builder_id,close_sum,tr.find('.payed'),tr.find('.rest'));
        });
    });

    function check_pay_possibility(current_builder,close_sum){
        var unclosed_sum = current_builder.taken - current_builder.closed,
            available_sum = unclosed_sum + current_builder.rest;
        console.log(unclosed_sum);
        if(available_sum >= Math.abs(close_sum) ||
            available_sum >= Math.abs(close_sum)) {
            return true;
        }
        else return false;
    }
    function make_pay(mounter_id,builder_id,close_sum,payed_td,rest_td) {
        var mounter_data = data[mounter_id];
        if(mounter_data['builder_data'].length == 1){
            console.log(mounter_data['builder_data'][0]);
            if(check_pay_possibility(mounter_data['builder_data'][0]),close_sum){
                savePay(mounter_id,close_sum,mounter_data['builder_data'][0].builder_id,payed_td,rest_td,mounter_data['builder_data'][0]);
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
            console.log(check_pay_possibility(current_builder,close_sum));
            if(check_pay_possibility(current_builder,close_sum)){
                savePay(mounter_id,close_sum,current_builder.builder_id,payed_td,rest_td,current_builder);
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
</script>