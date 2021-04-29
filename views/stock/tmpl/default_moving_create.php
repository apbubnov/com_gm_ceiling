<?php
$modelStock = Gm_ceilingHelpersGm_ceiling::getModel('stock');
$goodsInCategories = $modelStock->getGoodsInCategories($dealer->id);
$goodsInCategories_json = quotemeta(json_encode($goodsInCategories, JSON_HEX_QUOT));
?>
<style>
    .row {
        margin-bottom: 1em;
    }
</style>
<h3 class="center">Перемещение товаров</h3>
<div class="row">
    <div class="col-md-2">Откуда:</div>
    <div class="col-md-4">
        <select class="form-control" id="from_stock">
            <option>Выберите склад списания</option>
        </select>
    </div>
    <div class="col-md-2">куда:</div>
    <div class="col-md-4">
        <select class="form-control" id="to_stock">
            <option>Выберите склад приема</option>
        </select>
    </div>
</div>
<h4>Выберите товар</h4>
<div class="row" style="margin-bottom: 5px;">
    <div class="col-md-4 style=" margin-bottom: 5px;
    ">
    <label for="choose_category">Категория</label>
    <select class="form-control" id="choose_category">
        <option>Выберите категорию</option>
        <?php foreach ($goodsInCategories as $category) { ?>
            <option value="<?= $category->category_id ?>"><?= $category->category_name ?></option>
        <?php } ?>
    </select>
</div>
<div class="col-md-4" style="margin-bottom: 5px;">
    <label for="choose_category">Товар</label>
    <select class="form-control" id="choose_goods">
    </select>
</div>
</div>
<table class="table table-stripped">
    <thead>
        <th>
            #
        </th>
        <th>
            Наименование
        </th>
        <th>
            Кол-во на складе
        </th>
        <th>
            Сколько переместить
        </th>
        <th>

        </th>
    </thead>
</table>

<script type="text/javascript">
    var INPUT_COUNT='<input class="count center"/>',
        BUTTON_DELETE='<button type="button" class="btn btn-danger delete"> <i class="fas fa-trash-alt"></i> </button>',
        goodsInCategories = JSON.parse('<?= $goodsInCategories_json?>');
    jQuery(document).ready(function () {
        showStocks();

        jQuery("#choose_category").change(function(){
            var selected_category = jQuery(this).val(),
                goodsByCategory = goodsInCategories.filter(function(category){
                    return category.category_id == selected_category;
                })[0].goods;
            jQuery("#choose_goods").empty();
            jQuery("#choose_goods").append('<option>Выберите компонент</option>');
            jQuery.each(goodsByCategory,function(index,elem){
                jQuery("#choose_goods").append('<option value = "'+elem.goods_id+'">'+elem.name+'</option>');
            });
            jQuery("#div_goods_select").show();
        });
        jQuery("#choose_goods").change(function() {
            var selectedId = jQuery(this).val(),
                goodsByCategory = goodsInCategories.filter(function(category){
                    return category.category_id == jQuery("#choose_category").val();
                })[0].goods,
                selected_goods = goodsByCategory.filter(function (elem) {
                    console.log(elem);
                    return elem.goods_id == selectedId;
                })[0];
            jQuery.ajax({
                type: 'POST',
                url: "index.php?option=com_gm_ceiling&task=stock.getStocks",
                data:{
                    goods_id: selected_goods.id
                },
                success: function (data) {

                },
                dataType: "json",
                async: false,
                timeout: 10000,
                error: function (data) {
                    var n = noty({
                        timeout: 2000,
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "error",
                        text: "Ошибка!"
                    });
                }
            });
            addGoodsInTable(selected_goods);

        });
    });

    function addGoodsInTable(goods){
        console.log(goods);
        jQuery("#tgoods > tbody").append('<tr data-id="'+goods.goods_id+'"></tr>');
        jQuery("#tgoods > tbody > tr:last").append('<td>'+goods.goods_id+'</td><td>'+goods.name+'</td><td>'+INPUT_COUNT+'</td><td>'+INPUT_COST+'</td><td>'+BUTTON_DELETE+'</td>');
    }




    function showStocks() {
        jQuery.ajax({
            type: 'POST',
            url: "index.php?option=com_gm_ceiling&task=stock.getStocks",
            success: function (data) {
                console.log(data);
                fillSelect(data, "#from_stock");
                fillSelect(data, "#to_stock");
            },
            dataType: "json",
            async: false,
            timeout: 10000,
            error: function (data) {
                var n = noty({
                    timeout: 2000,
                    theme: 'relax',
                    layout: 'center',
                    maxVisible: 5,
                    type: "error",
                    text: "Ошибка!"
                });
            }
        });
    }

    function fillSelect(data, id) {
        var selectCounterparty = jQuery(id);
        for (var i = 0; i < data.length; i++) {
            selectCounterparty.append(jQuery('<option>', {
                value: data[i].id,
                text: data[i].name
            }));
        }
    }
</script>