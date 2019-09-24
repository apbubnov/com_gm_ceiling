<?php
/**
 * Created by PhpStorm.
 * User: bubnov
 * Date: 11.09.2019
 * Time: 10:28
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

$dealer = JFactory::getUser($project->dealer_id);
$goods = $data->goods;

$customer = $data->customer;
$stocks = $model->getStocks();
$status = floatval($customer->Status);
$statusNumber = $status;

$goodsInCategories = $model->getGoodsInCategories($dealer->id);
$goodsInCategories_json = quotemeta(json_encode($goodsInCategories, JSON_HEX_QUOT));
?>
<style>
    body {
        background-color: #E6E6FA;
    }

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
        width: 30px;
        height: 30px;
        background-color: inherit;
        color: rgb(54, 53, 127);
        border-radius: 5px;
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

    .Modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background-color: rgba(0, 0, 0, .8);
        z-index: 1;
        display: inline-block;
    }

    .Modal .Form {
        position: absolute;
        width: 320px;
        background-color: rgb(229, 229, 229);
        display: inline-block;
    }

    .Modal .Form.Provider {
        width: 640px;
    }

    .Modal .Form .Area {
        display: inline-block;
        float: left;
        width: 310px;
        height: auto;
        overflow: visible;
        margin: 0 5px;
        margin-bottom: 10px;
        position: relative;
    }

    .Modal .Form .Title {
        display: inline-block;
        width: 100%;
        height: 45px;
        line-height: 45px;
        background-color: rgb(54, 53, 127);
        color: rgb(255, 255, 255);
        text-align: center;
        font-weight: bold;
        font-size: 18px;
        margin-bottom: 10px;
    }

    .Modal .Form .Area .Input {
        display: inline-block;
        float: left;
        width: 100%;
        height: 30px;
        padding-left: 5px;
        border: none;
    }

    .Modal .Form .Area .Selects {
        display: inline-block;
        position: relative;
        float: left;
        width: 100%;
        height: 0;
        z-index: 2;
    }

    .Modal .Form .Area .Selects .Select {
        position: absolute;
        top: -1px;
        left: 0;
        display: inline-block;
        float: left;
        width: 100%;
        border: 1px solid rgb(169, 169, 169);
        border-top: 0;
        height: auto;
        background-color: rgb(54, 53, 127);
        color: rgb(255, 255, 255);
        max-height: 90px;
        overflow-y: scroll;
        overflow-x: hidden;
    }

    .Modal .Form .Area .Selects .Select .Item {
        display: inline-block;
        float: left;
        width: 100%;
        padding-left: 5px;
        height: auto;
        line-height: 25px;
        font-size: 14px;
        border-top: 1px solid rgb(255, 255, 255);
        cursor: pointer;
    }

    .Modal .Form .Area .Selects .Select .Item:hover {
        background-color: rgb(31, 30, 70);
    }

    .Modal .Form .Action {
        display: inline-block;
        float: left;
        width: calc(100% - 10px);
        height: auto;
        overflow: visible;
        margin: 0 5px;
        margin-bottom: 5px;
        position: relative;
    }

    .Modal .Form .Action .Button {
        display: inline-block;
        float: left;
        width: calc(40% - 5px);
        height: 30px;
        overflow: visible;
        margin-right: 10px;
        position: relative;
        cursor: pointer;
        background-color: rgb(54, 53, 127);
        color: #ffffff;
        border: none;
    }

    .Modal .Form .Action .Button.Reset {
        width: calc(30% - 10px);
    }

    .Modal .Form .Action .Button.Cancel {
        width: calc(30% - 5px);
        margin-right: 0;
    }

    .Modal .Form .Line {
        display: inline-block;
        float: left;
        width: 100%;
        height: 5px;
        background-color: rgb(54, 53, 127);
    }

    .Input:hover + .Message,
    .Input:focus + .Message {
        display: inline-block;
    }

    .Message {
        position: absolute;
        top: -25px;
        left: 0;
        width: calc(100% - 2px);
        margin-left: 1px;
        height: 20px;
        line-height: 20px;
        font-size: 14px;
        font-weight: bold;
        text-align: center;
        background-color: #36357f;
        color: #ffffff;
        border-radius: 3px;
        box-shadow: 0 0 0 1px rgb(0, 0, 0);
        display: none;
    }

    .Message:before {
        content: '▼';
        position: absolute;
        left: calc(50% - 3px);
        top: 12px;
        color: #36357f;
        text-shadow: 0 1px 0 rgb(0, 0, 0);
        font-size: 8px;
    }

    .OGRN .Message {
        top: -45px;
        height: 40px;
    }

    .OGRN .Message:before {
        top: 32px;
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

    .result_row{
        border:1px solid #414099;
        color: black;
        padding: 12px 16px;
        margin-bottom: 5px;
        text-decoration: none;
        display: block;
    }
    .result_row:hover {
        background-color: #f1f1f1
    }

</style>
<h2 class="center">Создание новой реализации</h2>
<div class="Realization">
    <div class="Actions">
        <?= parent::getButtonBack(); ?>
        <div class="Action ButInp Customer">
            <input type="text" name="customer" class="InputButInp" required>
            <button type="button" class="btn btn-primary" id="choose_customer_btn">
                <i class="fa fa-user" aria-hidden="true"></i> Покупатель
            </button>
        </div>
        <button type="button" class="btn btn-primary add_goods">
            <i class="fa fa-plus" aria-hidden="true"></i> Товар
        </button>
        <select class="Action Stock" name="stock">
            <?foreach ($stocks as $s):?>
                <option value="<?=$s->id;?>"><?=$s->name;?></option>
            <?endforeach;?>
        </select>
        <button type="button" class="btn btn-primary" id="make_realisation">
            <i class="fa fa-shopping-cart" aria-hidden="true"></i> Реализовать
        </button>
    </div>
    <div class="CustomerInfo">
        <table class="CustomerInfoTable">
            <tbody>
            <tr class="Pay">
                <td class="Name">Оплата:</td>
                <td class="Value">
                    <span class="RV">0</span><i class="fas fa-ruble-sign"></i>
                </td>
            </tr>
            <tr class="Dealer" style="">
                <td class="Name">Дилер:</td>
                <td class="Value"></td>
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
                <td colspan="2" class="ButtonTD">Функции</td>
            </tr>
            </thead>
            <tbody>

            </tbody>
        </table>
    </div>
    <div class="modal_window_container" id="mw_container">
        <button type="button" class="close_btn" id="close"><i class="fa fa-times fa-times-tar" aria-hidden="true"></i></button>
        <div class="modal_window" id="mw_add_goods">
            <div class="row">
                <div class="col-md-6 center" style="border:2px solid #414099;border-radius: 5px;">
                    <label>Штрихкод</label><br>
                    <div class="col-md-10">
                        <input type="text" id="goods_id" class="form-control" style="margin-bottom: 10px;">
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-large btn-primary" id="add_by_id" style="margin-bottom: 10px;">ОК</button>
                    </div>
                </div>
                <div class="col-md-6 center" style="border:2px solid #414099;border-radius: 5px;">
                    <label>Категория</label><br>
                    <div class="col-md-12">
                        <select class="form-control" id="goods_category_select" style="margin-bottom: 10px;">
                            <option>Выберите категорию</option>
                            <?php foreach ($goodsInCategories as $category){?>
                                <option value="<?=$category->category_id?>"><?=$category->category_name?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-md-12" id="div_goods_select" style="display:none">
                        <select class="form-control" id="goods_select" style="margin-bottom: 10px;">
                            <option>Выберите компонент</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">
                <table id="added_goods" class="table table-stripped table_cashbox">
                    <thead>
                    <td>
                        Наименование
                    </td>
                    <td>
                        Количество
                    </td>
                    <td>
                        <i class="far fa-trash-alt"></i>
                    </td>
                    </thead>
                    <tbody>

                    </tbody>
                </table>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <button class="btn btn-primary" id="add_dop_goods">Добавить</button>
                </div>
            </div>
        </div>

        <div class="modal_window" id="mw_customer">
            <div class="row">
                <div class="col-md-4"></div>
                <div class="col-md-4">
                    <label>Введите данные для поиска</label>
                    <input class="form-control" id="search_inp">
                    <div id="search_result" style="height: 300px; overflow-y: scroll;">
                    </div>
                </div>
                <div class="col-md-4"></div>
            </div>

        </div>

        <div class="modal_window" id="mw_edit">
            <div class="row">
                <div class="col-md-4">
                    <input type="hidden" class="edit_goods_id">
                    <label><b>Наименование</b></label><br>
                    <span class="goods_name"></span>
                </div>
                <div class="col-md-2">
                    <label><b>Стоимость</b></label><br>
                    <span class="goods_price"></span>
                </div>
                <div class="col-md-2">
                    <label><b>Количество</b></label><br>
                    <div class="col-md-12"><input id="new_count" class="form-control"></div>
                </div>
                <div class="col-md-2">
                    <label><b>Цена</b></label><br>
                   <span class="sum"></span>
                </div>
            </div>
        </div>
    </div>
    <div class="ModalDoc" id="mw_doc" style="display: none;">
        <div class="Document">
            <iframe class="iFrame" >

            </iframe>
            <div class="Actions">
                <div class="CheckBox">
                    <div class="Name">Товарная накладная</div>
                    <input type="checkbox" id="PackingList" name="page">
                </div>
                <div class="CheckBox">
                    <div class="Name">Приходный кассовый ордер</div>
                    <input type="checkbox" id="RetailCashOrder" name="page">
                </div>
                <div class="CheckBox">
                    <div class="Name">Расходная накладная</div>
                    <input type="checkbox" id="SalesInvoice" name="page">
                </div>
                <div class="Right">
                    <button type="button" id="print_doc" <!--onclick="print_frame();-->"><i class="fa fa-print" aria-hidden="true"></i></button>
                    <button type="button" id="save_doc" <!--onclick="save_frame();-->"><i class="fas fa-save" aria-hidden="true"></i></button>
                    <button type="button" id="close_doc" <!--onclick="close_frame();-->"><i class="fa fa-times" aria-hidden="true"></i></button>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    var EDIT_BUTTON = '<button type="button" class="Clone">' +
        '<i class="fa fa-edit" aria-hidden="true"></i>' +
        '</button>',
        DELETE_BUTTON = '<button type="button" class="Remove">' +
            '<i class="fas fa-trash-alt" aria-hidden="true"></i>' +
            '</button>',
        INPUT_COUNT='<input class="count center"/>',
        BUTTON_DELETE_MW='<button type="button" class="btn btn-danger delete"> <i class="fas fa-trash-alt"></i> </button>',
        found_users,
        customer ={},
        Goods = {},
        goodsInCategories = JSON.parse('<?= $goodsInCategories_json?>'),
        goodsToAdd = {};
    console.log(goodsInCategories);
    jQuery(document).mouseup(function (e) {
        var div = jQuery("#mw_add_goods"),
            div1 = jQuery("#mw_customer"),
            div2 = jQuery("#mw_edit");
        if (!div.is(e.target)
            && div.has(e.target).length === 0 &&
            !div1.is(e.target)
            && div1.has(e.target).length === 0 &&
            !div2.is(e.target)
            && div2.has(e.target).length === 0) {
            jQuery("#close").hide();
            jQuery("#mw_container").hide();
            div.hide();
            div1.hide();
            div2.hide();
        }
    });

    jQuery('#mw_customer').on('click','.result_row',function(){
        var dealer_id = jQuery(this).data('id');
        customer = found_users[dealer_id];
        console.log(customer);
        jQuery('.Dealer').find('.Value').text(customer.name);
        jQuery("#mw_container").hide();
        jQuery("#mw_customer").hide();
        jQuery("#close_btn").hide();
    });

    jQuery(document).ready(function () {
        jQuery('.add_goods').click(function () {
            jQuery("#mw_container").show();
            jQuery("#close").show();
            jQuery("#mw_add_goods").show('slow');
        });

        jQuery("#choose_customer_btn").click(function () {
            jQuery("#mw_container").show();
            jQuery("#close").show();
            jQuery("#mw_customer").show('slow');
        });

        jQuery("#goods_category_select").change(function(){
            var selected_category = jQuery(this).val(),
                goodsByCategory = goodsInCategories.filter(function(category){
                    return category.category_id == selected_category;
                })[0].goods;
            jQuery("#goods_select").empty();
            jQuery("#goods_select").append('<option>Выберите компонент</option>');
            jQuery.each(goodsByCategory,function(index,elem){
                jQuery("#goods_select").append('<option value = "'+elem.goods_id+'">'+elem.name+'</option>');
            });
            jQuery("#div_goods_select").show();
        });

        jQuery("#goods_select").change(function() {
            var selectedId = jQuery(this).val(),
                goodsByCategory = goodsInCategories.filter(function(category){
                    return category.category_id == jQuery("#goods_category_select").val();
                })[0].goods,
                selected_goods = goodsByCategory.filter(function (elem) {
                    console.log(elem);
                    return elem.goods_id == selectedId;
                })[0];
            console.log(goodsByCategory);
            addGoodsInTable(selected_goods);
            Goods[selected_goods.goods_id] = selected_goods;
        });

        jQuery("#add_by_id").click(function () {
            var found_goods = findGoods(jQuery('#goods_id').val());
            console.log(found_goods);
            addGoodsInTable(found_goods);
            Goods[found_goods.goods_id] = found_goods;

       })

        jQuery("#add_dop_goods").click(function(){
            var addTotalSum = 0;
            jQuery.each(jQuery("#added_goods > tbody > tr"),function (index,elem) {
                elem = jQuery(elem);
                Goods[elem.data('id')].final_count = elem.find('.count').val();
                Goods[elem.data('id')].price_sum = Goods[elem.data('id')].dealer_price* Goods[elem.data('id')].final_count;
                addTotalSum += Goods[elem.data('id')].price_sum;
            });
            fillData(Goods);
            jQuery("#close").hide();
            jQuery("#mw_container").hide();
            jQuery("#mw_add_goods").hide();
        });

        jQuery("#search_inp").keyup(function(){
            jQuery.ajax({
                url: "/index.php?option=com_gm_ceiling&task=stock.getCustomer",
                async: false,
                data: {
                   filter:jQuery("#search_inp").val()
                },
                type: "POST",
                success: function (data) {
                    if(!empty(data)){
                        found_users = JSON.parse(data);
                        console.log(found_users);
                        var row;
                        jQuery("#search_result").empty();
                        for(var i  in found_users){
                            row = '<div class="result_row" data-id="'+found_users[i].id+'">'+found_users[i].name+'</div>';
                            jQuery("#search_result").append(row);
                        }
                    }
                    else{
                        noty({
                            theme: 'relax',
                            layout: 'center',
                            timeout: 5000,
                            type: "success",
                            text: "Ничего не найдено!"
                        });
                    }
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

        jQuery("#make_realisation").click(function () {
            console.log(customer.associated_client);
            jQuery.ajax({
                url: "index.php?option=com_gm_ceiling&task=create_empty_project",
                data: {
                    client_id: customer.associated_client

                },
                async:false,
                type: "POST",
                success: function (data) {
                    console.log(data);
                    data = JSON.parse(data);
                    makeRealisation(parseInt(data));
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

        jQuery('body').on('click','.Remove',function(){
           var btn = jQuery(this);
           noty({
               theme: 'relax',
               layout: 'center',
               timeout: false,
               type: "info",
               text: "Вы действитльно хотите удалить выбранный компонент?",
               buttons:[
                   {
                       addClass: 'btn btn-primary', text: 'Да', onClick: function($noty) {
                           var row = btn.closest('tr'),
                               id = row.data('id'),
                               sum = row.find('.goods_sum').text();
                           /*удалить из массива реализации*/
                           delete Goods[id];
                           /*удалить стороку из табицы и обновить суммы*/
                           row.remove();
                           updateTotalSum(sum);
                           $noty.close();
                       }
                   },
                   {
                       addClass: 'btn btn-primary', text: 'Отмена', onClick: function($noty) {
                           $noty.close();
                       }
                   }
               ]
           });
        });

        jQuery('body').on('click','.delete',function() {
           jQuery(this).closest('tr').remove();
        });

        jQuery('.add_goods').click(function () {
            jQuery("#mw_container").show();
            jQuery("#close").show();
            jQuery("#mw_add_goods").show('slow');
        });

        jQuery("#print_doc").click(function() {
            (jQuery(".iFrame")[0].contentWindow || jQuery('.iFrame')[0]).print();
        });

        jQuery("#save_doc").click(function () {
            var now = new Date();
            var link = document.createElement('a');
            link.setAttribute('href',$(".iFrame").attr("src"));
            link.setAttribute('download',"Реализация " + now.getDay() + "/" + now.getMonth() + "/" + now.getFullYear() + " " + now.getHours() + ":" + now.getMinutes() + ".pdf");
            onload=link.click();
        });

        jQuery("#close_doc").click(function () {
            jQuery("#mw_doc").hide();
        });

        jQuery('body').on('click','.Clone',function() {
            jQuery("#mw_container").show();
            jQuery("#close").show();
            jQuery("#mw_edit").show('slow');

            var goods_id = jQuery(this).closest('tr').data('id'),
                goods = Goods[goods_id];
            jQuery('.edit_goods_id').val(goods.goods_id);
            jQuery('.goods_name').text(goods.name);
            jQuery('.goods_price').text(goods.dealer_price);
            jQuery('#new_count').val(goods.final_count);
            jQuery('.sum').text(goods.price_sum);
        });

        jQuery("#new_count").keyup(function(){
           var id = jQuery(this).closest('.row').find('.edit_goods_id').val(),
               new_count = jQuery(this).val(),
               price = jQuery(this).closest('.row').find('.goods_price').text(),
               old_sum = jQuery(this).closest('.row').find('.sum').text(),
               new_sum = (price*new_count).toFixed(2),
               diff_sum = old_sum - new_sum;
           console.log(diff_sum);
           jQuery(this).closest('.row').find('.sum').text(new_sum);
           updateGoodsInfoInList(id,new_sum,new_count);
           updateTotalSum(diff_sum);
           updateGoodsInArray(id,new_count,new_sum);
        });
    });
    function addGoodsInTable(goods){
        console.log(goods);
        jQuery("#added_goods > tbody").append('<tr data-id="'+goods.goods_id+'"></tr>');
        jQuery("#added_goods > tbody > tr:last").append('<td>'+goods.name+'</td><td>'+INPUT_COUNT+'</td><td>'+BUTTON_DELETE_MW+'</td>');

    }
    function fillData(goods){
        jQuery('#goods_table > tbody').empty();
        var total_sum = 0,
            goods_name,
            td_edit = '<td>'+EDIT_BUTTON+'</td>',
            td_delete = '<td>'+DELETE_BUTTON+'</td>';
        jQuery.each(goods,function(eindex,elem){
            jQuery('#goods_table > tbody').append('<tr data-id="' + elem.goods_id + '"></tr>');
            if (elem.category_id == 1) {
                goods_name = 'Полотно: ' + elem.name;
            }
            else {
                goods_name = 'Компонент: ' + elem.name;
            }
            jQuery('#goods_table > tbody > tr:last').append('<td>' + goods_name + '</td>' +
                '<td>' + elem.dealer_price + '</td>' +
                '<td class="goods_count">' + parseFloat(elem.final_count).toFixed(2) + '</td>' +
                '<td class="goods_sum">' + parseFloat(elem.price_sum).toFixed(2) + '</td>' +
                td_edit +
                td_delete)
            total_sum += +elem.price_sum;
        });
        jQuery('#goods_table > tbody').append('<tr><td colspan="3" style="text-align:right;"><b>Итого:</b></td><td colspan="3" class="total_sum_td"><b>'+total_sum+'</b></td></tr>');
        jQuery('.RV').text(total_sum);
    }

    function findGoods(goods_id){
        var result;
        console.log(goods_id);
        jQuery.each(goodsInCategories,function (index,category) {
            for(var i = category.goods.length;i--;){

                if(category.goods[i].goods_id == goods_id){
                    console.log(category.goods[i]);
                    result = category.goods[i];
                    return;
                }
            }
        });
        return result;
    }
    function updateTotalSum(sum){
        var old_total_sum = jQuery('.RV').text();
        jQuery('.RV').text(old_total_sum-sum);
        jQuery('.total_sum_td').text(old_total_sum-sum);
    }

    function updateGoodsInfoInList(goods_id,new_sum,new_count){
        var tr = jQuery('#goods_table > tbody > tr[data-id = "'+goods_id+'"]');
        tr.find('.goods_count').text(new_count);
        tr.find('.goods_sum').text(new_sum);
    }

    function updateGoodsInArray(goods_id,new_count,new_sum){

        Goods[goods_id].final_count = new_count;
        Goods[goods_id].price_sum = new_sum;
    }

    function makeRealisation(project_id){
        var goodsToRealisation = {ids:Object.keys(Goods).join(','),goods:[],goods_count:Object.keys(Goods).length};
            jQuery.each(Goods,function(index,elem){
                goodsToRealisation.goods.push({goods_id:elem.goods_id,category_id:elem.category_id,name:elem.name,dealer_price:elem.dealer_price,unit_id:elem.unit_id,count:elem.final_count});
            });
            console.log(goodsToRealisation);
            jQuery.ajax({
                url: "/index.php?option=com_gm_ceiling&task=stock.makeRealisation",
                async: false,
                data: {
                    goods:JSON.stringify(goodsToRealisation),
                    project_id:project_id,
                    stock: jQuery('[name="stock"]').val()
                },
                type: "POST",
                success: function (data) {
                   data = JSON.parse(data);
                   if(data.type == 'error'){
                       noty({
                           theme: 'relax',
                           layout: 'center',
                           timeout: 5000,
                           type: "error",
                           text: data.text,
                           buttons:[
                               {
                                   addClass: 'btn btn-primary', text: 'Посмотреть детали', onClick: function($noty) {
                                       jQuery("#shortage_goods > tbody").empty();
                                       jQuery.each(data.goods,function(index,goods){
                                           jQuery("#shortage_goods > tbody").append('<tr><td>'+goods.name+'</td><td>'+goods.count+'</td></tr>')
                                       });
                                       jQuery("#mw_container").show();
                                       jQuery("#mw_shortage").show('slow');
                                       jQuery("#close").show();
                                       $noty.close();
                                   }
                               },
                               {
                                   addClass: 'btn btn-primary', text: 'Отмена', onClick: function($noty) {
                                       $noty.close();
                                   }
                               }
                           ]
                       });
                   }
                   else{
                       console.log(data);
                       if (data.href != null)
                       {
                           jQuery.each(data.href, function (i, t) {
                               console.log(i,t)
                               jQuery("#"+i).val(t); jQuery("#"+i).attr("checked",true);
                           });
                           jQuery(".ModalDoc .Document .iFrame").attr("src", data.href.MergeFiles);
                           jQuery("#mw_doc").show('slow');
                           jQuery(".ModalDoc .Document .Actions .CheckBox input[type=\"checkbox\"]").change(LoadPDF);
                       }
                   }
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
    }

    function LoadPDF() {
        var checkbox = jQuery(".ModalDoc .Document .Actions .CheckBox input[type=\"checkbox\"]:checked"),
            values = [];

        jQuery.each(checkbox, function (i, t) { values.push(jQuery(t).val());});

        if (values.length > 0) jQuery.ajax({
            type: 'POST',
            url: "/index.php?option=com_gm_ceiling&task=stock.MergeFiles",
            data: {files: values},
            success: function (data) {
                jQuery(".ModalDoc .Document .iFrame").attr("src", data);
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
    }
</script>