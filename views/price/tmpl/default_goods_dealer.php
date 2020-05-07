<?php
/**
 * Created by PhpStorm.
 * User: bubnov
 * Date: 03.07.2019
 * Time: 14:52
 */
$jinput = JFactory::getApplication()->input;
$dealerId = $jinput->get('dealer_id',null,'INT');

$user = JFactory::getUser();
$user->groups = $user->get('groups');

$managerGM = in_array(16, $user->groups);
$dealer = JFactory::getUser($dealerId);
$pricesModel = Gm_ceilingHelpersGm_ceiling::getModel('prices');
$stockModel = Gm_ceilingHelpersGm_ceiling::getModel('stock');
$operations = json_encode($stockModel->getOperations());
$goodsPrices = json_encode($pricesModel->getGoodsPriceForDealer($dealerId));

?>
<link rel="stylesheet" type="text/css"
      href="/components/com_gm_ceiling/views/price/css/style.css?date=<?= date("H.i.s"); ?>">
<div class="Page">
    <div class="Title">
        Прайс товаров<?= (isset($dealer)) ? " для $dealer->name #$dealer->id" : ""; ?>
    </div><div class="Scroll">
        <form action="<?= JRoute::_('index.php?option=com_gm_ceiling&view=canvases' . (!empty($dealer) ? "&dealer=$dealer->id" : "")); ?>"
              method="post"
              name="adminForm" id="adminForm" hidden>
            <?= JHtml::_('form.token'); ?>
        </form>
        <table class="Body">
            <thead>
            <tr class="THead">
                <td><i class="fa fa-bars" aria-hidden="true"></i></td>
                <td><i class="fa fa-hashtag" aria-hidden="true"></i></td>
                <td>Наименование</td>
                <td>Цвет</td>
                <? if (!empty($dealer) && !$managerGM): ?>
                    <td>Цена</td>
                    <td>Цена для клиента</td>
                <? elseif ($managerGM): ?>
                    <td>Цена</td>
                    <td>Изменение</td>
                    <td>Цена для дилера</td>
                    <td>Изменить</td>
                <? endif; ?>
            </tr>
            </thead>
            <tbody id="goods_tbody">
            </tbody>
            <tfoot>
            <tr>
                <td colspan="12"></td>
            </tr>
            </tfoot>
        </table>
    </div>
</div>
<table>

</table>
<script type="text/javascript">
    var goodsPrices = JSON.parse('<?php echo $goodsPrices;?>'),
        operations = JSON.parse('<?php echo $operations?>'),
        CHANGE_FIELDS = '<div class="row">'+
                            '<div class="col-md-4 col-xs-4">'+
                                createOperationSelect(operations)[0].outerHTML+
                            '</div>'+
                            '<div class="col-md-4 col-xs-4" style="padding:1px">'+
                                '<input type="text" name="operation_value" class="form-control non_click" placeholder="0" size="5" required>' +
                            '</div>'+
                            '<div class="col-md-2 col-xs-2">' +
                                '<button type="button" class="btn btn-primary change_price non_click"><i class="fa fa-paper-plane" aria-hidden="true"></i></button>'+
                            '</div>'+
                            '<div class="col-md-2 col-xs-2" >' +
                                '<button type="button" class="btn btn-primary clear_price non_click"><i class="fas fa-eraser"></i></button>'+
                            '</div>'+
                        '</div>',
        isGMManager = '<?=$managerGM?>';

    jQuery(document).ready(function () {
       console.log(goodsPrices);
       console.log(operations);
       jQuery.each(goodsPrices,function(index,good){
           var tr;
           if(good.category_id == 1){
               tr = '<tr class="TBody Level1 Action" data-level="1"> <td><i class="fa fa-caret-down" aria-hidden="true"></i></td><td colspan="6">'+good.category+'</td>';
               if(isGMManager){
                   tr += '<td>'+CHANGE_FIELDS+'</td>';
               }
               tr += '</tr>';
               jQuery("#goods_tbody").append(tr);
               addCanvasesToTable(good.textures);
           }
           else{
               tr = '<tr class="TBody Level1 Action" data-level="1"> <td><i class="fa fa-caret-down" aria-hidden="true"></i></td><td colspan="6">'+good.category+'</td>';
               if(isGMManager){
                   tr +='<td>'+CHANGE_FIELDS+'</td>';
               }
               tr+= '</tr>';
               jQuery("#goods_tbody").append(tr);
               addComponentsToTable(good.goods);
           }
       });

       jQuery("#goods_tbody > tr").click(function () {
           var TR = jQuery(this),
               level = parseInt(jQuery(this).data("level"));
           var TRN = TR.next();
           if (TR.hasClass("Active")) {
               console.log("has");
               TR.removeClass("Active");
               TR.find("td:first-child i").removeClass("fa-caret-up").addClass("fa-caret-down");

               while (TRN.length !== 0 && TRN.data("level") > level) {
                   TRN.removeClass("Active");

                   if (TRN.hasClass("Action"))
                       TRN.find("td:first-child i").removeClass("fa-caret-up").addClass("fa-caret-down");

                   TRN.hide();
                   TRN = TRN.next();
               }
           } else {
               console.log("notas ");
               TR.addClass("Active");
               TR.find("td:first-child i").removeClass("fa-caret-down").addClass("fa-caret-up");

              while (TRN.length !== 0 && TRN.data("level") > level) {
                   if (TRN.hasClass("Level" + (level + 1)))
                       TRN.show();
                   TRN = TRN.next();
               }
           }
       });

       jQuery('.non_click').click(function () {
          return false;
       });

       jQuery('.change_price').click(function () {
           var goods = collectGoods(this),
               thisBtn = jQuery(this),
               change = thisBtn.closest('tr').find('[name="operation_value"]').val();
           if(goods.length && !empty(change)) {
               jQuery.ajax({
                   type: 'POST',
                   url: '/index.php?option=com_gm_ceiling&task=prices.dealerPriceGoods',
                   data: {
                       dealer_id: '<?php echo $dealerId;?>',
                       dealer_prices: goods
                   },
                   dataType: "json",
                   timeout: 5000,
                   success: function (data) {
                       noty({
                           theme: 'relax',
                           layout: 'center',
                           maxVisible: 5,
                           type: "success",
                           text: "Успешно изменено!"
                       });
                       var tr,oldPrice,operation = thisBtn.closest('tr').find('[name="operation_select"]').val(),
                           newPrice;
                       console.log(operation,change);
                       jQuery.each(goods,function(index,elem){
                          tr = jQuery('#goods_tbody').find('[data-good_id='+elem.goods_id+']');
                          oldPrice = tr.find('.old_price').text();
                          newPrice = getNewPrice(operation,oldPrice,change);
                          tr.find('.operation').text(newPrice.operation);
                          tr.find('.dealer_price').text(newPrice.final_price);
                       });
                   },
                   error: function (error) {
                       noty({
                           theme: 'relax',
                           layout: 'center',
                           maxVisible: 5,
                           type: "error",
                           text: "Ошибка при попытке изменить"
                       });
                   }
               });
           }
           else{
               noty({
                   theme: 'relax',
                   layout: 'center',
                   maxVisible: 5,
                   type: "error",
                   text: "Пустое значение!"
               });
           }
       });

       jQuery('.clear_price').click(function () {
           var goods = collectGoods(this);
           console.log(goods);
           if(goods.length) {
               jQuery.ajax({
                   type: 'POST',
                   url: '/index.php?option=com_gm_ceiling&task=prices.dealerPriceGoods',
                   data: {
                       dealer_id: '<?php echo $dealerId;?>',
                       dealer_prices: goods,
                       reset_flag:1
                   },
                   dataType: "json",
                   timeout: 5000,
                   success: function (data) {
                       noty({
                           theme: 'relax',
                           layout: 'center',
                           maxVisible: 5,
                           type: "success",
                           text: "Успешно изменено!"
                       });
                       var tr,oldPrice;
                      jQuery.each(goods,function(index,elem){
                          tr = jQuery('#goods_tbody').find('[data-good_id='+elem.goods_id+']');
                          oldPrice = tr.find('.old_price').text();
                          tr.find('.operation').text('');
                          tr.find('.dealer_price').text(oldPrice);
                      });
                   },
                   error: function (error) {
                       noty({
                           theme: 'relax',
                           layout: 'center',
                           maxVisible: 5,
                           type: "error",
                           text: "Ошибка при попытке изменить"
                       });
                   }
               });
           }
       });
    });

    function getNewPrice(operation,oldPrice,change) {
        var newPrice = {operation:null,final_price:null};
        switch(operation){
            case '1':
                newPrice.operation = change;
                newPrice.final_price = change;
                break;
            case '2':
                newPrice.operation = '+'+change;
                newPrice.final_price = +oldPrice + +change;
                break;
            case '3':
                newPrice.operation = '-'+change;
                newPrice.final_price = +oldPrice - +change;
                break;
            case '4':
                newPrice.operation = '+'+change+'%';
                newPrice.final_price = +oldPrice + (oldPrice*change*0.01);
                break;
            case '5':
                newPrice.operation = '-'+change+'%';
                newPrice.final_price = +oldPrice - (oldPrice*change*0.01);
                break;
        }
        return newPrice;
    }
    function collectGoods(elem) {
        var row = jQuery(elem).closest('.row'),
            operation_type = row.find('[name="operation_select"]').val(),
            operation_value = row.find('[name="operation_value"]').val(),
            tr = row.closest('tr'),
            trn = tr.next(),
            goods = [];
        if (tr.hasClass('goods')) {
            goods.push({
                goods_id: tr.data('good_id'),
                operation_id: operation_type,
                value: operation_value
            });
        } else {
            while (trn.length !== 0 && trn.data("level") > tr.data('level')) {
                console.log(trn.length !== 0);
                console.log(trn.data("level") > tr.data('level'));
                if (trn.hasClass('goods') && !empty(trn.data('good_id'))) {
                    goods.push({
                        goods_id: trn.data('good_id'),
                        operation_id: operation_type,
                        value: operation_value
                    });
                }
                trn = trn.next();
            }
        }
        return goods;
    }
    function addCanvasesToTable(canvasesData){
        jQuery.each(canvasesData,function(index,texture){
            if(isGMManager){
                jQuery("#goods_tbody").append('<tr class="TBody Level2 Action" style="display:none;" data-level="2" ><td><i class="fa fa-caret-down" aria-hidden="true"></i></td><td colspan="6">'+texture.texture+'</td><td>'+CHANGE_FIELDS+'</td></tr>');
            }
            else{
                jQuery("#goods_tbody").append('<tr class="TBody Level2 Action" style="display:none;" data-level="2" ><td><i class="fa fa-caret-down" aria-hidden="true"></i></td><td colspan="6">'+texture.texture+'</td></tr>');
            }
            jQuery.each(texture.manufacturers,function(index1,manufacturer){
                if(isGMManager) {
                    jQuery("#goods_tbody").append('<tr class="TBody Level3 Action" style="display:none;" data-level="3"><td><i class="fa fa-caret-down" aria-hidden="true"></i></td><td colspan="6">' + manufacturer.manufacturer + '</td><td>' + CHANGE_FIELDS + '</td></tr>');
                }
                else{
                    jQuery("#goods_tbody").append('<tr class="TBody Level3 Action" style="display:none;" data-level="3"><td><i class="fa fa-caret-down" aria-hidden="true"></i></td><td colspan="6">' + manufacturer.manufacturer + '</td></tr>');
                }
                jQuery.each(manufacturer.goods,function(index2,goods){
                    jQuery("#goods_tbody").append('<tr class="TBody Level4 goods"  data-level="4" data-good_id ='+goods.id+' style="display:none;"></tr>');
                    if(isGMManager){
                        jQuery("#goods_tbody > tr:last").append('<td>#</td><td>'+goods.id+'</td><td>'+goods.name+'</td><td>'+goods.color+'</td><td class="old_price">'+goods.price+'</td><td class="operation">'+goods.operation+'</td><td class="dealer_price">'+goods.final_price+'</td><td>'+CHANGE_FIELDS+'</td>');
                    }
                    else{
                        jQuery("#goods_tbody > tr:last").append('<td>#</td><td>'+goods.id+'</td><td>'+goods.name+'</td><td>'+goods.color+'</td><td>'+goods.price+'</td><td>'+goods.final_price+'</td>');
                    }
                });
            });
        });
    }

    function addComponentsToTable(goods) {
        jQuery.each(goods,function (index,elem) {
            var color = !empty(elem.color) ? elem.color : '-';
            jQuery("#goods_tbody").append('<tr class="TBody Level2 goods" data-level="2" data-good_id ='+elem.id+' style="display:none;"></tr>');
            if(isGMManager) {
                jQuery("#goods_tbody > tr:last").append('<td></td><td>' + elem.id + '</td><td>' + elem.name + '</td><td>' + color + '</td><td class="old_price">' + elem.price + '</td><td class="operation">' + elem.operation + '</td><td class="dealer_price">' + parseFloat(elem.final_price).toFixed(2) + '</td><td>' + CHANGE_FIELDS + '</td>')
            }
            else{
                if(empty(elem.color)){
                    elem.color = '-';
                }
                jQuery("#goods_tbody > tr:last").append('<td></td><td>' + elem.id + '</td><td>' + elem.name + '</td><td>' + elem.color + '</td><td>' + elem.price + '</td><td>' + parseFloat(elem.final_price).toFixed(2) + '</td>');
            }
        });

    }

    function createOperationSelect(optionData){
        var select = jQuery(document.createElement('select'));
        select.prop('name','operation_select');
        select.addClass('form-control non_click');
        jQuery.each(optionData,function (index,elem) {
           var option = jQuery(document.createElement('option'));
           option.prop('value',elem.id);
           option.prop('text',elem.title);
           select.append(option);
        });
        return select;
    }

</script>
