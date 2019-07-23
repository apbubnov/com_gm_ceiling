<?php
    $stockModel = Gm_ceilingHelpersGm_ceiling::getModel('stock');
    $categories = $stockModel->getGoodsCategories();
    $textures = json_encode($stockModel->getPropTextures());
    $widths = json_encode($stockModel->getPropCanvasWidths());
    $colors = json_encode($stockModel->getPropColors());
    $manufacturers = json_encode($stockModel->getPropManufacturers());

    $goods_json = json_encode($stockModel->getGoods());
    $goodsUnits = $stockModel->getGoodsUnits();
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
<div class="container">
    <form>
        <fieldset style="border: 1px solid #414099;border-radius: 5px;">
            <legend>Создание нового товара</legend>
            <div class="row" >
                <div class="col-md-6" style="margin-bottom: 5px;margin-top: 5px;">
                    <select id="goods_categories" class="input-gm">
                        <option value="0">Выберите категорию</option>
                        <?php
                        foreach ($categories as $item) {
                            echo "<option value=\"$item->id\">$item->value</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="row">
                    <div id="special_props" class="col-md-6">

                    </div>
                    <div id="common_props" class="col-md-12">
                        <label for="name">Наименование</label><br>
                        <input type="text" class="form-control" id="name"><br>
                        <label for="unit">Единицы измерения</label><br>
                        <select id="unit" class="form-control">
                            <?php foreach ($goodsUnits as $unit){ ?>
                                <option value="<?= $unit->id;?>"><?= $unit->unit;?></option>
                            <?php } ?>
                        </select><br>
                        <label for="multiplicity">Кратность продажи</label><br>
                        <input type="text" id="multiplicity" class="form-control"><br>
                        <label for="price">Цена</label><br>
                        <input type="text" id="price" class="form-control"><br>
                    </div>
            </div>
            <div class="row">
                <div class="col-md-12 center">
                    <button class="btn btn-primary" type="button" id="create_new_goods">Создать</button>
                </div>
            </div>

        </fieldset>


    </form>

	<div class="row">
		<h1>Список существующих товаров</h1>
		<table class="table"  id="tgoods" cellspacing="0" width="100%">
			<thead>
				<th>Штрихкод</th>
				<th>Наименование</th>
				<th>Наличие</th>
			</thead>
			<tbody id="tbody_goods">
			</tbody>
		</table>
	</div>

<h4 id="h4_common_sum"></h4>
</div>

<link href="/libraries/MDB-Free_4.7.1/css/addons/datatables.min.css" rel="stylesheet">
<script type="text/javascript" src="/libraries/MDB-Free_4.7.1/js/mdb.min.js"></script>
<script type="text/javascript" src="/libraries/MDB-Free_4.7.1/js/addons/datatables.min.js"></script>

<script type="text/javascript">

    var propCanvasWidths = JSON.parse('<?php echo $widths;?>'),
        propTextures = JSON.parse('<?php echo $textures;?>'),
        propColors = JSON.parse('<?php echo $colors;?>'),
        propManufacturers = JSON.parse('<?php echo $manufacturers;?>'),
        goods = JSON.parse('<?php echo $goods_json;?>');

    jQuery(document).ready(function(){
        allGoods(goods);
        jQuery('#tgoods').DataTable({
            "paging": true
        });
        jQuery('.dataTables_length').addClass('bs-select');

        console.log(goods);
        console.log(propCanvasWidths);
        console.log(propTextures);
        console.log(propColors);
        console.log(propManufacturers);



        jQuery('#goods_categories').change(function () {
            console.log(this.value);
            jQuery('#special_props').empty();
            switch(this.value) {
                case '1':
                    addPropSelect('canvases_textures', propTextures, 'Текстура');
                    addPropSelect('canvases_manufacturers', propManufacturers, 'Производитель');
                    addPropSelect('canvases_widths', propCanvasWidths, 'Ширина');
                    addPropSelect('color', propColors, 'Цвет');
                    jQuery("#common_props").removeClass('col-md-12');
                    jQuery("#common_props").addClass('col-md-6');
                    break;
                case '4':
                    addPropSelect('color', propColors, 'Цвет');
                    break;
                default:
                    jQuery("#common_props").removeClass('col-md-6');
                    jQuery("#common_props").addClass('col-md-12');
            }
        });

        jQuery("#create_new_goods").click(function () {
            var category = jQuery("#goods_categories").val(),
                goodsName = jQuery("#name").val(),
                goodsUnit = jQuery("#unit").val(),
                goodsMultiplicity = jQuery("#multiplicity").val(),
                goodsPrice = jQuery("#price").val(),
                texture = jQuery("#canvases_textures").length > 0 ? jQuery("#canvases_textures").val() : "",
                manufacturer = jQuery("#canvases_manufacturers").length > 0 ? jQuery("#canvases_manufacturers").val() : "",
                width =  jQuery("#canvases_widths").length > 0 ? jQuery("#canvases_widths").val() : "",
                color = jQuery("#color").length > 0 ? jQuery("#color").val() : "";
            console.log("category",category);
            console.log("goodsName",goodsName);
            console.log("goodsUnit",goodsUnit);
            console.log("goodsMultiplicity",goodsMultiplicity);
            console.log("goodsPrice",goodsPrice);
            console.log("texture",texture);
            console.log("manufacturer",manufacturer);
            console.log("width",width);
            console.log("color",color);

            jQuery.ajax({
                url: "index.php?option=com_gm_ceiling&task=stock.addGoods",
                type: "post",
                data: {
                    category:category,
                    goodsName:goodsName,
                    goodsUnit:goodsUnit,
                    goodsMultiplicity:goodsMultiplicity,
                    goodsPrice:goodsPrice,
                    texture:texture,
                    manufacturer:manufacturer,
                    width:width,
                    color:color
                },
                dataType: "json",
                async: false,
                success: function (data) {

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
    });

    function addPropSelect(selectId, propValueArray, labelText) {
        var select = document.createElement('select');
        select.setAttribute('id', selectId);
        select.classList.add('form-control');
        var label = document.createElement('label');
        label.innerHTML = labelText;
        jQuery('#special_props')[0].append(label);
        jQuery('#special_props')[0].append(document.createElement('br'));
        jQuery('#special_props')[0].append(select);
        jQuery('#special_props')[0].append(document.createElement('br'));
        fillSelect('#'+selectId, propValueArray);
    }



    function fillSelect(selectId, data) {
        jQuery(selectId).empty();
        for(var i = 0; i < data.length; i++){
            jQuery(selectId).append('<option value = "'+data[i].id+'">'+data[i].value+'</option>');
        }
    }

    function allGoods(goods){
        var tr, td, tbody_goods = document.getElementById('tbody_goods');
        tbody_goods.innerHTML = '';
        for (var i = 0; i < goods.length; i++) {
            tr = tbody_goods.insertRow();
            td = tr.insertCell();
            td.innerHTML = goods[i].id;
            td = tr.insertCell();
            td.innerHTML = goods[i].name;

            var stocks = "";
            if (goods[i].stocks_count != null) {
                for (var j = 0; j < goods[i].stocks_count.length; j++) {
                    stocks += goods[i].stocks_count[j].name + ': ' + goods[i].stocks_count[j].count + "<html> <br> </html>";
                }
            }
            td = tr.insertCell();
            td.innerHTML = stocks;
        }
    }

</script>