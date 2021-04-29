<?php
/**
 * @version    CVS: 0.1.7
 * @package    Com_Gm_ceiling
 * @author     bubnov <2017 al.p.bubnov@gmail.com>
 * @copyright  2017 al.p.bubnov@gmail.com
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;

$user       = JFactory::getUser();
$userId     = $user->get('id');
$today = date("Y-m-d");
$modelCashBox = Gm_ceilingHelpersGm_ceiling::getModel('cashbox');
$types = $modelCashBox->getCashBoxTypes();
$usersModel = Gm_ceilingHelpersGm_ceiling::getModel('users');
$users = $usersModel->getUsersByGroupsAndDealer('16,19',$user->dealer_id);
$cashboxTypes = $modelCashBox->getCashBoxTypes();
$isManager = in_array('16',$user->groups);
$isStorekeeper = in_array('19',$user->groups);
$isBuh = in_array('14',$user->groups);
$cashboxsSum = $modelCashBox->getCashboxSum();
$cashboxType = '';
if($isManager){
    $cashboxType = 1;
    unset($cashboxTypes[3]);
}
elseif($isStorekeeper){
    $cashboxType = 2;
    unset($cashboxTypes[1]);
    unset($cashboxTypes[3]);
}
elseif($isBuh){
    $cashboxType = 3;
}

?>
<style>
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
<div class="row">
    <?= parent::getButtonBack();?>
</div>
<div class="row" style="margin-bottom: 1em;">
    <div class="col-md-4">
        <div class="col-md-6">
            <b>Период с</b>
        </div>
        <div class="col-md-6">
            <b>по</b>
        </div>
        <div class="col-md-6" style="padding-left:0px;padding-right: 1px;">
            <input class="form-control date_choose" type="date" id="date_from" value="<?=$today?>">
        </div>

        <div class="col-md-6" style="padding-left:1px;padding-right: 0px;">
            <input class="form-control date_choose" type="date" id="date_to" value="<?=$today?>">
        </div>
    </div>
    <div class="col-md-2" style="padding-left:1px;padding-right: 1px;" >
        <div class="col-md-12">
            <b>Тип кассы</b>
        </div>
        <div class="col-md-12">
            <select class="form-control" id="cashbox_type">
                <option value="">Выберите кассу</option>
                <?php foreach ($cashboxTypes as $type){
                    echo "<option value='$type->id'>$type->name</option>";
                }?>
            </select>
        </div>
    </div>
    <div class="col-md-3">
        <div class="col-md-12">
            <b>Контрагент</b>
        </div>
        <div class="col-md-12">
            <input class="form-control" id="search_inp">
            <div class="search_result" style="height: 300px; overflow-y: scroll;display: none;">
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="col-md-12">
            <b>Ответственный</b>
        </div>
        <div class="col-md-12">
            <select class="form-control" id="user_select">
                <option>Выберите</option>
                <?php foreach ($users as $userObj) {
                    echo "<option value='$userObj->id'>$userObj->name</option>";
                }?>
            </select>
        </div>
    </div>
</div>

<div class="row" style="margin-bottom: 1em;">
    <div class="col-md-6">
        <?php if($isBuh){?>
            <div class="col-md-3">
                <button class="btn btn-primary" id="create_collection">Инкассация</button>
            </div>
        <?php }?>
        <div class="col-md-3">
            <button class="btn btn-primary" id="create_payment">Внести\списать</button>
        </div>
    </div>
    <div class="col-md-6" style="text-align: right;">
        <?php if($isBuh) {
            foreach ($cashboxsSum as $value) {
                $cSum = $value['incoming'] - $value['outcoming']; ?>
                <div class="row">
                    <div class="col-md-6"></div>
                    <div class="col-md-4">
                        <b><?= $value['name'] ?>:</b>
                    </div>
                    <div class="col-md-2">
                        <b><?= $cSum; ?></b>
                    </div>
                </div>
                <?php
            }
        }
        else{
            $cSum = $cashboxsSum[$cashboxType]['incoming'] - $cashboxsSum[$cashboxType]['outcoming'];
            echo "<b>В кассе: $cSum</b>";
        }?>
    </div>
</div>
<div class="row">
    <table class="table table-stripped table_cashbox" id="cashbox_table">
        <thead>
            <tr>
                <th>Дата</th>
                <th>Контрагент</th>
                <th>Тип</th>
                <th>Сумма</th>
                <th>Касса</th>
                <th>Ответственный</th>
                <th>Комментарий</th>
                <th>Ордер</th>
            </tr>
        </thead>
        <tbody>

        </tbody>
        <tfoot></tfoot>
    </table>
</div>
<div class="modal_window_container" id="mw_container">
    <button type="button" id="btn_close" class="btn-close">
        <i class="fa fa-times fa-times-tar" aria-hidden="true"></i>
    </button>
    <div class="modal_window" id="mw_payment">
        <h4>Внесение оплаты</h4>
        <div class="col-md-3"></div>
        <div class="col-md-6">
            <div class="row" style="margin-bottom: 1em;">
                <div class="col-md-6" style="text-align:left;">
                    <span><b>Выберите контрагента:</b></span>
                </div>
                <div class="col-md-6">
                    <input type="hidden" id="selected_dealer" >
                    <input class="form-control" id="payment_search_dealer">
                    <div class="search_result" id="found_dealers" style="height: 300px; overflow-y: scroll;display: none;"></div>
                </div>
            </div>
            <div class="row" style="margin-bottom: 1em;">
                <div class="col-md-3" style="text-align:right;">
                    <span><b>Введите сумму</b></span>
                </div>
                <div class="col-md-3">
                    <input class="form-control" id="payment_sum">
                </div>
                <div class="col-md-3" style="text-align:right;">
                    <span><b>Выберите тип:</b></span>
                </div>
                <div class="col-md-3">
                    <select class="form-control" id="payment_type">
                        <option value="1">Внесение</option>
                        <option value="2">Списание</option>
                    </select>
                </div>
            </div>
            <div class="row" style="margin-bottom: 1em;">
                <div class="col-md-4" style="text-align:left;">
                    <span><b>Введите комментарий:</b></span>
                </div>
                <div class="col-md-8">
                    <input class="form-control" id="payment_comment">
                </div>
            </div>
            <div class="row" style="margin-bottom: 1em;">
                <div class="col-md-12">
                    <button class="btn btn-primary" id="save_payment">Сохранить</button>
                </div>
            </div>
        </div>
        <div class="col-md-3"></div>
    </div>
    <div class="modal_window" id="mw_collection">
        <h4>Инкассация</h4>
        <div class="row">
            <div class="col-md-3"></div>
            <div class="col-md-6">
                <div class="row" style="margin-bottom: 1em;">
                    <div class="col-md-6">
                        <span><b>Введите сумму:</b></span>
                    </div>
                    <div class="col-md-6">
                        <input class="form-control" id="collection_sum">
                    </div>
                </div>
                <div class="row" style="margin-bottom: 1em;">
                    <div class="col-md-6">
                        <span><b>Введите комментарий:</b></span>
                    </div>
                    <div class="col-md-6">
                        <input class="form-control" id="collection_comment">
                    </div>
                </div>
                <div class="row" style="margin-bottom: 1em;">
                    <div class="col-md-6">
                        <span><b>Выберите кассу инкассации:</b></span>
                    </div>
                    <div class="col-md-6">
                        <select class="form-control" id="collection_box">
                            <?php foreach ($cashboxTypes as $type){
                                echo "<option value='$type->id'>$type->name</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="row" style="margin-bottom: 1em;">
                    <div class="col-md-12">
                        <button class="btn btn-primary" id="save_collection">Сохранить</button>
                    </div>
                </div>
            </div>
            <div class="col-md-3"></div>
        </div>
    </div>
</div>
<script>

    jQuery(document).mouseup(function (e){ // событие клика по веб-документу
        var div1 = jQuery("#mw_collection"),
            div2 = jQuery('#mw_payment');
        if (!div1.is(e.target) &&
            !div2.is(e.target) &&
            div1.has(e.target).length === 0 &&
            div2.has(e.target).length === 0) {
            jQuery("#btn_close").hide();
            jQuery("#mw_container").hide();
            div1.hide();
            div2.hide();
        }
    });
    jQuery(document).ready(function (){
        var counterparty,
            isBuh = '<?=$isBuh?>',
            isManager= '<?=$isManager?>',
            isStorekeeper = '<?=$isStorekeeper?>';
        if(isManager){
            jQuery('#cashbox_type option[value="1"]').attr('selected',true);
        }
        if(isStorekeeper){
            jQuery('#cashbox_type option[value="2"]').attr('selected',true);
        }
        getFilteredData();
        jQuery("#search_inp").keyup(function(){
            var searchVal = jQuery("#search_inp").val();
            if(!empty(searchVal)){
                findDealer(searchVal,this);
            }
            else{
                jQuery("#search_result").empty();
                jQuery("#search_result").hide();
                counterparty = null;
                getFilteredData();
            }
        });

        jQuery('.date_choose').change(function () {
            getFilteredData();
        });

        jQuery('#user_select').change(function () {
            getFilteredData();
        });

        jQuery('.search_result').on('click','.result_row',function(){
            var dealer_id = jQuery(this).data('id'),
            parent = jQuery(this).closest('.search_result');
            if (parent.attr('id') === 'found_dealers'){
                jQuery('#selected_dealer').val(dealer_id);
                jQuery('#payment_search_dealer').val(jQuery(this).text());
            }
            else{
                counterparty = found_users[dealer_id];
                jQuery('#search_inp').val(counterparty.name);
                getFilteredData();
            }
            parent.hide();

        });

        jQuery('#search_inp').blur(function(){
            getFilteredData();
        });

        jQuery('#cashbox_type').change(function () {
            getFilteredData();
        })

        jQuery('#create_collection').click(function(){
            jQuery('#btn_close').show();
            jQuery('#mw_container').show();
            jQuery('#mw_collection').show();
        });

        jQuery('#create_payment').click(function(){
            jQuery('#btn_close').show();
            jQuery('#mw_container').show();
            jQuery('#mw_payment').show();
            jQuery('#selected_dealer').val('');
        });

        jQuery('#save_payment').click(function (){
            var dealer_id = jQuery('#selected_dealer').val(),
                sum = jQuery('#payment_sum').val(),
                paynent_type = jQuery('#payment_type').val(),
                comment = jQuery('#payment_comment').val(),
                user_id = '<?=$userId;?>',
                cashbox_type = '<?=$cashboxType;?>',
                data = {};
            if(!empty(dealer_id)){
                data.dealer_id = dealer_id;
            }
            if(!empty(sum)){
                data.sum = sum;
            }
            if(!empty(paynent_type)){
                data.operation_type = paynent_type;
            }
            if(!empty(comment)){
                data.comment = comment;
            }
            if(!empty(user_id)){
                data.user_id = user_id;
            }
            if(!empty(cashbox_type)){
                data.cashbox_type = cashbox_type;
            }
            saveSum(data);
        });

        jQuery('#save_collection').click(function () {
            var sum = jQuery('#collection_sum').val(),
                cashbox = jQuery('#collection_box').val(),
                comment = jQuery('#collection_comment').val(),
                data = {
                    operation_type: 3,
                    user_id: '<?=$userId;?>',
                    sum: sum,
                    comment: comment,
                    cashbox_type: cashbox
                };
            saveSum(data);
        });

        jQuery('#payment_search_dealer').keyup(function(){
            findDealer(this.value,this);
        });

        jQuery('#cashbox_table').on('click','.create_check',function () {
            var id = jQuery(this).closest('tr').data('id');
            jQuery.ajax({
                url: "/index.php?option=com_gm_ceiling&task=cashbox.createCashOrder",
                async: false,
                data: {
                    id: id
                },
                type: "POST",
                success: function (data) {
                    window.open(data, '_blank');
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

        function getFilteredData() {
            var dateFrom = jQuery('#date_from').val(),
                dateTo = jQuery('#date_to').val(),
                cashboxType = jQuery('#cashbox_type').val(),
                counterpartyId = !empty(counterparty) ? counterparty.id :'',
                userId = jQuery('#user_select').val();
            console.log(jQuery('#cashbox_type').val());
            jQuery.ajax({
                url: "/index.php?option=com_gm_ceiling&task=cashbox.getData",
                async: false,
                data: {
                    date_from: dateFrom,
                    dateTo: dateTo,
                    counterparty: counterpartyId,
                    user_id: userId,
                    cashbox_type: cashboxType
                },
                type: "POST",
                success: function (data) {
                    jQuery('#cashbox_table > tbody').empty();
                    if(!empty(data)){
                        data = JSON.parse(data);
                        jQuery.each(data,function (n,el) {
                            console.log(el);
                            var tr = '<tr data-id="'+el.id+'">' +
                                '<td>'+el.datetime+'</td>'+
                                '<td>'+el.counterparty+'</td>'+
                                '<td>'+el.operation+'</td>'+
                                '<td>'+el.sum+'</td>'+
                                '<td>'+el.cashbox+'</td>'+
                                '<td>'+el.user_name+'</td>'+
                                '<td>'+el.comment+'</td>';
                            if(el.operation_type != 3){
                                tr += '<td><button class="btn btn-primary create_check"><i class="fas fa-money-check"></i></button></td>'+
                            '</tr>';
                            }
                            else{
                                tr+= '<td>-</td></tr>'
                            }
                            jQuery('#cashbox_table > tbody').append(tr);
                       });
                    }
                    else{
                        jQuery('#search_result').hide();
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
        }

        function findDealer(searchVal,input){
            jQuery.ajax({
                url: "/index.php?option=com_gm_ceiling&task=stock.getCustomer",
                async: false,
                data: {
                    filter: searchVal
                },
                type: "POST",
                success: function (data) {
                    var searchResult = jQuery(input).closest('div').find('.search_result');
                    if(!empty(data)){
                        searchResult.show();
                        found_users = JSON.parse(data);
                        var row;
                        searchResult.empty();
                        for(var i  in found_users){
                            row = '<div class="result_row" data-id="'+found_users[i].id+'">'+found_users[i].name+'</div>';
                            searchResult.append(row);
                        }
                    }
                    else{
                        searchResult.hide();
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
        }

        function saveSum(data) {
            jQuery.ajax({
                url: "/index.php?option=com_gm_ceiling&task=cashbox.save",
                async: false,
                data: {
                    data_to_save: data
                },
                type: "POST",
                success: function (data) {
                    noty({
                        theme: 'relax',
                        layout: 'center',
                        timeout: 5000,
                        type: "sucess",
                        text: "Сохранено!"
                    });
                    setTimeout(function (){location.reload();},5000);
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
    });
</script>