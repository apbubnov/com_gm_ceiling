<?php
/**
 * Created by PhpStorm.
 * User: bubnov
 * Date: 30.08.2019
 * Time: 11:48
 */
defined('_JEXEC') or die;
JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');

$user = JFactory::getUser();
$model = $this->getModel();

$userId = $user->get('id');
$groups = $user->get('groups');


$app = JFactory::getApplication();
$projectId = $app->input->get('id', 0, 'int');
$projectModel = Gm_ceilingHelpersGm_ceiling::getModel('Project');
$calculationformModel = Gm_ceilingHelpersGm_ceiling::getModel('calculationForm');
$data = $projectModel->getProjectForStock($projectId);
$project = $projectModel->getData($projectId);
$stocks = $model->getStocks();

$dealer = JFactory::getUser($project->dealer_id);
$goods = $data->goods;

$customer = $data->customer;

$status = floatval($customer->Status);
$statusNumber = $status;

$goodsInCategories = $model->getGoodsInCategories($dealer->id);
$goodsInCategories_json = quotemeta(json_encode($goodsInCategories, JSON_HEX_QUOT));

echo parent::getPreloaderNotJS();
?>
<style>
    .Actions {
        line-height: 42px;
    }

    .Actions .ButInp {
        height: 38px;
        position: relative;
        display: inline-block;
        margin-bottom: -15px;
    }

    .Actions .ButInp .InputButInp {
        width: 100%;
        height: 100%;
        float: left;
        border: none;
        background: transparent;
        border-radius: 25%;
    }

    .Actions .ButInp .ButtonButInp {
        position: absolute;
        left: 0;
        top: 0;
    }

    .Actions .Stock {
        color: #fff;
        background-color: #414099;
        border: 1px solid #414099;
        width: auto;
        padding: 0 5px;
        border-radius: 4px;
        height: 38px;
        position: relative;
        display: inline-block;
        margin-bottom: -15px;
        bottom: -2px;
        cursor: pointer;
    }

    .CustomerInfo {
        display: inline-block;
        width: calc(100% - 2px);
        margin-top: 10px;
        float: left;
        border-radius: 5px;
        overflow: hidden;
        box-shadow: 0 0 0 1px #414099;
    }

    .CustomerInfo .CustomerInfoTable {
        width: 100%;
        position: relative;
        border-collapse: collapse;
    }

    .CustomerInfo .CustomerInfoTable tr {
        height: 30px;
        line-height: 30px;
    }

    .CustomerInfo .CustomerInfoTable tr td {
        padding: 0 15px;
        min-width: 102px;
    }

    .CustomerInfo .CustomerInfoTable tr .Name {
        background-color: #414099;
        color: #ffffff;
        max-width: 80px;
        text-align: center;
    }

    .CustomerInfo .CustomerInfoTable tr td label {
        height: 8px;
        margin-right: -17px;
    }

    .CustomerInfo .CustomerInfoTable tr:not(:first-child) td:not(.Name) {
        border-top: 1px solid #414099;
    }

    .CustomerInfo .CustomerInfoTable tr:not(:first-child) td.Name {
        border-top: 1px solid #ffffff;
    }

    .CustomerInfo .CustomerInfoTable tr.Pay td.Value div{
        display: inline-block;
        float: left;
    }
    .CustomerInfo .CustomerInfoTable tr.Pay td.Value div.Radio {
        margin-right: 50px;
        line-height: 30px;
    }
    .CustomerInfo .CustomerInfoTable tr.Pay td.Value div.Radio div.RUB {
        margin-right: 15px;
    }
    .CustomerInfo .CustomerInfoTable tr.Pay td.Value div.Radio * {
        cursor: pointer;
    }
    .CustomerInfo .CustomerInfoTable tr.Pay td.Value span {
        font-weight: bold;
    }

    .Realization {
        display: inline-block;
        width: 100%;
        float: left;
    }
    .Realization .Table {
        margin-top: 10px;
        width: 100%;
        display: inline-block;
        border-radius: 5px 5px 0 0;
        overflow: hidden;
        overflow-x: auto;
    }
    .Realization .Elements {
        min-width: 100%;
        position: relative;
        border-collapse: collapse;
        float: left;
    }

    .Realization .Elements tr {
        border: 1px solid #414099;
        background-color: #E6E6FA;
        color: #000000;
    }

    .Realization .Elements tr td {
        border: 0;
        border-right: 1px solid #414099;
        width: auto;
        height: 30px;
        line-height: 20px;
        padding: 0 5px;
    }

    .Realization .Elements tr td button {
        display: inline-block;
        float: left;
        border: none;

        background-color: #414099;
        color: #FFFFFF;
        cursor: pointer;
    }

    .Realization .Elements thead {
        position: relative;
        top: 0;
        left: 0;
    }

    .Realization .Elements thead tr td {
        background-color: #414099;
        color: #ffffff;
        border-color: #ffffff;
        padding: 5px 10px;
        text-align: center;
        min-width: 102px;
    }

    .Realization .Elements tbody tr {
        cursor: pointer;
    }

    .Realization .Elements tbody tr:hover {
        background-color: #97d8ee;
    }

    .Realization .Elements thead tr .ButtonTD {
        min-width: 0;
    }

    .Realization .Elements tbody tr .ButtonTD {
        width: 30px;
    }

    .Realization .Elements tr td:last-child {
        border-right: 0;
    }

    .Realization .Elements .CloneElementsHead {
        position: fixed;
        top: 0;
        left: 0;
    }

    .Realization .Elements .CloneElementsHeadTr {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        z-index: 1;
    }

    .Realization .Show {
        display: inline-block !important;
    }


    .Input:hover + .Message,
    .Input:focus + .Message {
        display: inline-block;
    }



    .ModalDoc {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background-color: rgba(0, 0, 0, .8);
        z-index: 1;
        display: inline-block;
    }

    .ModalDoc .Document {
        position: absolute;
        left: 25px;
        top: 25px;
        width: calc(100vw - 50px);
        height: calc(100vh - 50px);
        overflow: hidden;
        background-color: rgb(54, 53, 127);
        color: rgb(255,255,255);
        z-index: 1;
        display: inline-block;
    }

    .ModalDoc .Document .iFrame {
        display: inline-block;
        width: 100%;
        height: calc(100vh - 90px);
        float: left;
    }

    .ModalDoc .Document .Actions {
        display: inline-block;
        width: 100%;
        height: 40px;
        line-height: 40px;
        float: left;
        padding: 0 10px;
    }

    .ModalDoc .Document .Actions .CheckBox {
        display: inline-block;
        width: auto;
        height: 40px;
        float: left;
    }

    .ModalDoc .Document .Actions .CheckBox .Name {
        display: inline-block;
        float: left;
        height: 40px;
        width: auto;
        margin-left: 10px;
    }

    .ModalDoc .Document .Actions .CheckBox input[type="checkbox"] {
        display: inline-block;
        float: left;
        width: 20px;
        height: 20px;
        margin: 10px;
        cursor: pointer;
    }

    .ModalDoc .Document .Actions .Right {
        display: inline-block;
        width: auto;
        height: 40px;
        float: right;
    }

    .ModalDoc .Document .Actions .Right button[type="button"] {
        position: relative;
        display: inline-block;
        width: 30px;
        height: 30px;
        margin: 5px;
        float: left;
        border: none;
        background-color: rgba(0,0,0,0);
        color: rgb(255,255,255);
        cursor: pointer;
        box-shadow: inset 0 0 0 1px rgb(255,255,255);
        border-radius: 3px;
    }

    .ModalDoc .Document .Actions .Right button[type="button"] i:before {
        position: absolute;
        left: 0;
        top: 0;
        width: 30px;
        height: 30px;
        line-height: 30px;
        text-align: center;
    }
</style>
<h1>Возврат по проекту №<?=$projectId;?></h1>
<form class="Realization">
    <input type="number" name="project" value="<?=$projectId;?>" hidden>
    <input type="number" name="status" value="<?=$statusNumber;?>" hidden>
    <div class="Actions">
        <?= parent::getButtonBack(); ?>
        <?php if($statusNumber !=8) {?>
            <button type="button" class="btn btn-primary add_goods"">
            <i class="fa fa-plus" aria-hidden="true"></i> Компонент
            </button>
            <!--    <select class="Action Stock" name="stock">
        <?/*foreach ($stocks as $s):*/?>
            <option value="<?/*=$s->id;*/?>"><?/*=$s->name;*/?></option>
        <?/*endforeach;*/?>
    </select>-->
            <button type="button" class="btn btn-primary make_realisation" <?php if ($data->customer->status == 8) echo "style='display:none;'"?>>
                <i class="fa fa-shopping-cart" aria-hidden="true"></i> Реализовать
            </button>
        <?php }?>
    </div>

    <div class="CustomerInfo">
        <table class="CustomerInfoTable">
            <tbody>
            <tr class="Project">
                <td class="Name">Проект №:</td>
                <td class="Value"><?=$project->id;?></td>
            </tr>
            <tr class="Project_info">
                <td class="Name">Адрес:</td>
                <td class="Value"><?=$project->project_info?></td>
            </tr>
            <tr class="Client">
                <td class="Name">Клиент:</td>
                <td class="Value"><?=$project->client_id?></td>
            </tr>
            <tr class="Dealer">
                <td class="Name">Дилер:</td>
                <td class="Value"><?=$dealer->name?></td>
            </tr>
            <tr class="Pay">
                <td class="Name">Оплата:</td>
                <td class="Value">
                    <div class="Radio">
                        <div class="RUB"><span class="RV">-</span> <i class="fas fa-ruble-sign"></i></div>
                    </div>
                </td>
            </tr>
            <tr class="Pay">
                <td class="Name">Сумма возврата:</td>
                <td class="Value">
                    <div class="Radio">
                        <div class="RUB"><span class="return_sum">-</span> <i class="fas fa-ruble-sign"></i></div>
                    </div>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
    <div class="Table">
        <table class="Elements" id="goods_table">
            <thead class="ElementsHead">
            <tr class="ElementsHeadTr">
                <td>Название</td>
                <td>Стоимость</td>
                <td>Значение</td>
                <td>Цена</td>
                <td>Кол-во возврата</td>
            </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
</form>
<div class="row center">
    <div class="col-md-12">
        <button type="button" class="btn btn-primary" id="save_btn">Сохранить</button>
    </div>
</div>
<script type="text/javascript">
    var INPUT_COUNT='<input class="count center form-control"/>',
        OK_BTN = '<button type="button" class="btn btn-primary add_return">Ок</button>',
        Customer = <?=json_encode($customer);?>,
        Goods = <?=json_encode($goods);?>,
        goodsToReturn = [],realisationUpdate = [],returnArray = [],
        project_id = '<?=$projectId?>',
        status = '<?= $statusNumber;?>',
        total_return_sum = 0;
    jQuery(document).ready(function () {
        jQuery(".PRELOADER_GM").hide();
        fillData(Goods);
        jQuery(".add_return").click(function () {
            var tr = jQuery(this).closest('tr'),
                goods_id = tr.data('goods_id'),
                inventories = JSON.parse(Goods[goods_id].inventories),
                return_count = parseFloat(tr.find('.count').val()),
                count_td = tr.find('.goods_count'),
                sum_td = tr.find('.goods_sum'),
                return_sum = Goods[goods_id].dealer_price*return_count,
                totalSumSpan = jQuery('.RV');
            totalSumSpan.text((totalSumSpan.text() - return_sum).toFixed(2));
            jQuery('.total_sum_td').text(totalSumSpan.text());
            total_return_sum += return_sum;
            count_td.text((count_td.text() - return_count).toFixed(2));
            sum_td.text((sum_td.text()- return_sum).toFixed(2));

            jQuery.each(inventories,function(index,elem){
                if(elem.r_count >= return_count){
                    returnArray.push({inventory_id:elem.inventory_id,count: return_count});
                    goodsToReturn.push({inventory_id:elem.inventory_id,count:(+elem.i_count+ +return_count).toFixed(2)});
                    realisationUpdate.push({inventory_id:elem.inventory_id,count:(elem.r_count-return_count).toFixed(2)});
                    return_count = 0;
                }
                else{
                    if(return_count > 0 && elem.r_count > 0) {
                        var part_of_return = elem.r_count;
                        return_count -= part_of_return;
                        returnArray.push({inventory_id:elem.inventory_id,count: part_of_return});
                        goodsToReturn.push({inventory_id: elem.inventory_id, count: (+elem.i_count + +part_of_return).toFixed(2)});
                        realisationUpdate.push({inventory_id:elem.inventory_id,count:(elem.r_count-return_count).toFixed(2)});
                    }
                }
                if(return_count == 0){
                    return;
                }
            });
            console.log('gTR',goodsToReturn);
            console.log('rU',realisationUpdate);
            console.log('rA',returnArray);
            jQuery('.return_sum').text(total_return_sum);
        });

        jQuery("#save_btn").click(function () {
            var send_data = {return_array:returnArray,realisation_update:realisationUpdate,inventory_update:goodsToReturn};
            jQuery.ajax({
                url: "/index.php?option=com_gm_ceiling&task=stock.makeReturn",
                async: false,
                data: {
                    data:JSON.stringify(send_data),
                    project_id:project_id
                },
                type: "POST",
                success: function (data) {


                },
                dataType: "text",
                timeout: 10000,
                error: function () {
                    noty({
                        theme: 'relax',
                        layout: 'center',
                        timeout: 5000,
                        type: "error",
                        text: "Сервер не отвечает!"
                    });
                }
            });
        });

    });

    function fillData(goods){
        jQuery('#goods_table > tbody').empty();
        var total_sum = 0,
            goods_name,
            td_edit = '<td><div class="row" style="margin-top: 5px;margin-bottom:5px;"> <div class="col-md-8"> '+INPUT_COUNT+'</div><div class="col-md-4">'+OK_BTN+'</div></div></td>';
        jQuery.each(goods,function(eindex,elem){
            jQuery('#goods_table > tbody').append('<tr data-goods_id="'+elem.goods_id+'"></tr>');
            if(elem.category_id == 1){
                goods_name = 'Полотно: '+elem.name;
            }
            else{
                goods_name = 'Компонент: '+elem.name;
            }
            jQuery('#goods_table > tbody > tr:last').append('<td>'+goods_name+'</td>' +
                '<td>'+elem.dealer_price+'</td>' +
                '<td class="goods_count">'+parseFloat(elem.final_count).toFixed(2)+'</td>' +
                '<td class="goods_sum">'+parseFloat(elem.price_sum).toFixed(2)+'</td>' +
                td_edit)
            total_sum += +elem.price_sum;
        });
        jQuery('#goods_table > tbody').append('<tr><td colspan="3" style="text-align:right;"><b>Итого:</b></td><td colspan="3" class="total_sum_td"><b>'+total_sum+'</b></td></tr>');
        jQuery('.RV').text(total_sum);
    }

</script>