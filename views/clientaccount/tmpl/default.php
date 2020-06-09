<?php
$jinput = JFactory::getApplication()->input;
$id = $jinput->getInt('id');
$today = date('Y-m-d');
$model = Gm_ceilingHelpersGm_ceiling::getModel('client_state_of_account');
$data = $model->getData($id);
$sum = $model->getStateOfAccount($id,null,$today)->sum;
$debtModel = Gm_ceilingHelpersGm_ceiling::getModel('mountersdebt');
$operations = $debtModel->getTypes();
$total_sum = 0;
?>
<style>
    fieldset {
        margin: 10px;
        border: 2px solid #414099;
        padding: 4px;
        border-radius: 4px;
    }
    legend{
        width: auto;
    }
</style>
<form>
    <div class="container">
        <div class="row center">
            <div class="col-md-12">
                <label style="font-size: 18pt;color:#414099"><?=$data[0]->client_name;?></label>
            </div>
        </div>
        <div class="row right">
            <div class="col-md-12">
                <div style="color:#414099;font-size:13pt;">Состояние счета: <b><span ><?=$sum;?></span></b></div>
            </div>
        </div>
        <fieldset>
            <legend align="left"><label style="padding-left: auto">Внесение оплаты</label></legend>
            <div class="row">
                <div class="col-md-3">
                    <div class="col-md-4">
                        <label for="pay_sum">Сумма</label>
                    </div>
                    <div class="col-md-8">
                        <input  class="inputactive" type="text" id="pay_sum"  name="pay_sum" placeholder="Сумма" pattern="\-\d+|\-\d+\.{1,1}\d+"
                                title="Введите сумму, которую вносит дилер" required>
                    </div>
                    <input hidden type="number" name="client_id" id="client_id" value="<?=$id;?>">
                </div>
                <div class="col-md-3">
                    <div class="col-md-5">
                        <label for="select_operation_type"></label>
                    </div>
                    <div class="col-md-7">
                        <select class="inputactive" id="select_operation_type">
                            <?php foreach($operations as $operation){?>
                                <option value="<?=$operation->id?>"><?=$operation->title;?></option>
                            <?php }?>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="col-md-5">
                        <label for="pay_comment">Комментарий</label>
                    </div>
                    <div class="col-md-7">
                        <input type="text" id="pay_comment"class="inputactive" name="pay_comment" placeholder="Комментарий"
                               title="Введите комментарий об внесении средств" required>
                    </div>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary" type="button" id="savePayment"><i class="fa fa-paper-plane"></i></button>
                </div>
            </div>
        </fieldset>
        <div class="row" style="margin-bottom: 15px;">
            <div class="col-md-6">
                <span id="client_account_state_before"></span>
            </div>
            <div class="col-md-3">
                <input type="date" id="date_from" class="form-control date">
            </div>
            <div class="col-md-3">
                <input type="date" id="date_to" class="form-control date" value="<?= $today;?>">
            </div>
        </div>
        <div class="row">
            <table class="table table-striped table_cashbox" id="detailed_score">
                <thead class="caption-style-tar">
                <tr>
                    <th class="center">Дата</th>
                    <th class="center">Тип</th>
                    <th class="center">Сумма</th>
                    <th class="center">Проект</th>
                    <th class="center">Комментарий</th>
                </tr>
                </thead>
                <tbody>
                <?foreach ($data as $item):?>
                    <tr class="<?=($item->project_id != "-")?"project":"";?>" data-project="<?=$item->project_id;?>">
                        <td><?=$item->date;?></td>
                        <td><?=$item->operation;?></td>
                        <td>
                            <?php
                                if($item->operation_type == 1){
                                    $total_sum+=$item->sum;

                                }
                                else{
                                    $total_sum-=$item->sum;
                                }
                            echo $item->sum;
                            ?>
                        </td>
                        <td><?=$item->project_id;?></td>
                        <td><?=$item->comment;?></td>
                    </tr>
                <?endforeach;?>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan = "2">Итого:</th>
                        <th class="total_sum"><?=$total_sum?></th>
                        <th colspan="2"></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</form>

<script type="text/javascript">
    jQuery(document).ready(function () {
        jQuery('#savePayment').click(function(){
            var id = jQuery('#client_id').val(),
                operation = jQuery('#select_operation_type').val(),
                sum = jQuery('#pay_sum').val(),
                comment = jQuery('#pay_comment').val();
            console.log(id,operation,sum,comment);
            jQuery.ajax({
                url: "index.php?option=com_gm_ceiling&task=client.addRecToStateOfAccount",
                data: {
                    id: id,
                    operation: operation,
                    sum: sum,
                    comment: comment
                },
                dataType: "json",
                async: true,
                success: function (data) {
                   location.reload();
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
        });

        jQuery('.date').change(function(){
            var start_date = jQuery('#date_from').val(),
                end_date = jQuery('#date_to').val();
            jQuery.ajax({
                url: "index.php?option=com_gm_ceiling&task=client.getAccountStateDataByPeriod",
                data: {
                    id: jQuery('#client_id').val(),
                    date_from: start_date,
                    date_to: end_date
                },
                dataType: "json",
                async: true,
                success: function (data) {
                    console.log(data);
                    jQuery("#detailed_score > tbody").empty();
                    jQuery('#client_account_state_before').text('Состояние счета на начало выбранного периода '+data.sum);
                    var totalByPeriod = 0;
                    jQuery.each(data.detailed_data,function(index,elem){
                        var project = (!empty(elem.project_id)) ? elem.project_id : '-',
                            comment = (!empty(elem.comment)) ? elem.comment : '-';
                        if(elem.operation_type == 1){
                            totalByPeriod += +elem.sum;
                        }
                        else{
                            totalByPeriod -= elem.sum;
                        }
                        jQuery("#detailed_score > tbody").append(
                            '<tr>' +
                                '<td>'+elem.date+'</td>'+
                                '<td>'+elem.operation+'</td>'+
                                '<td>'+elem.sum+'</td>'+
                                '<td>'+project+'</td>'+
                                '<td>'+comment+'</td>'+
                            '</tr>');
                    });
                    jQuery('.total_sum').text(totalByPeriod);
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
        });
    });
</script>
