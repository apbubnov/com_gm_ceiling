<?php
/**
 * Created by PhpStorm.
 * User: popovaa
 * Date: 02.02.2018
 * Time: 12:20
 */

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');

$user       = JFactory::getUser();
$userId     = $user->get('id');
$stateOfAccountModel =  Gm_ceilingHelpersGm_ceiling::getModel('client_state_of_account');
$beginOfMonth = date('Y-m-01');
$today = date('Y-m-d');
$data = $stateOfAccountModel->getData($user->associated_client,$beginOfMonth,$today);

$rest = $stateOfAccountModel->getStateOfAccount($user->associated_client)->sum;
$total_sum = 0;
?>

<style>
    input {
        border: 1px solid #414099;
        border-radius: 5px;
    }
    .small_table {
        font-size: 13px;
    }
    @media screen and (min-width: 768px) {
        .small_table {
            font-size: 1em !important;
        }
	}
</style>

<input hidden type="number" name="client_id" id="client_id" value="<?=$user->associated_client;?>">
<h2 class = "center">Детализация счета</h2>
<div class = "center">
    <strong><?php echo $rest; ?></strong>
</div>
<div class="row" style="margin-bottom: 15px">
    <div class="col-md-6">
        <span id="client_account_state_before"></span>
    </div>
    <div class="col-md-3">
        <div class="col-md-2">
            <span style="vertical-align: bottom">C</span>
        </div>
        <div class="col-md-10">
            <input type = "date" class="form-control date" id = "date_from" value="<?=$beginOfMonth?>">
        </div>
    </div>
    <div class="col-md-3">
        <div class="col-md-2">
            <span style="vertical-align: bottom">по</span>
        </div>
        <div class="col-md-10">
            <input type ="date" id = "date_to" class="form-control date" value="<?=$today?>">
        </div>
    </div>
</div>
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
    <?php foreach ($data as $item):?>
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



<script type="text/javascript">
   jQuery(document).ready(function () {
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
