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
                    <select id="goods_categories" class="form-control">
                        <option value="0">Выберите категорию</option>
                        <?php
                        foreach ($categories as $item) {
                            echo "<option value=\"$item->id\">$item->value</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-6" style="margin-bottom: 5px;margin-top: 5px;">
                    <button type="button" class="btn btn-primary" id="new_category">
                        <i class="fas fa-plus-square"></i> Добавить категорию
                    </button>
                    <div class="row" id="new_category_info" style="display:none;margin-top: 5px;">
                        <div class="col-md-8">
                            <input type="text" class="form-control" id="new_category_name">
                        </div>
                        <div class="col-md-4">
                            <button type="button" class="btn btn-primary" id="create_new_category">
                                <i class="far fa-save"></i> Сохранить
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div id="common_props">
                    <div class="col-md-4">
                        <label for="name">Наименование</label><br>
                        <input type="text" class="form-control" id="name"><br>
                    </div>
                    <div class="col-md-3">
                        <label for="unit">Единицы измерения</label><br>
                        <select id="unit" class="form-control">
                            <?php foreach ($goodsUnits as $unit){ ?>
                                <option value="<?= $unit->id;?>"><?= $unit->unit;?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="multiplicity">Кратность</label><br>
                        <input type="text" id="multiplicity" class="form-control formatted"><br>
                    </div>
                    <div class="col-md-2">
                        <label for="price">Цена</label><br>
                        <input type="text" id="price" class="form-control formatted"><br>
                    </div>
                </div>
            </div>
            <div class="row">
                <div id="special_props" class="col-md-12">

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
        jQuery('body').on('input','.formatted',function () {
            jQuery(this).val(jQuery(this).val().replace(/\,/g, '.'));
            jQuery(this).val(jQuery(this).val().replace(/(?=(\d+\.\d{2})).+|(\.(?=\.))|([^\.\d])|(^\D)/gi, '$1'));
        });
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
                    var div_col1 = jQuery(document.createElement('div')),
                        div_col2 = jQuery(document.createElement('div'));
                    div_col1.addClass('col-md-6');
                    div_col2.addClass('col-md-6');
                    div_col1.append(addPropSelect('canvases_textures', propTextures, 'Текстура'));
                    div_col1.append(addPropSelect('canvases_manufacturers', propManufacturers, 'Производитель'));
                    div_col2.append(addPropSelect('canvases_widths', propCanvasWidths, 'Ширина'));
                    div_col2.append(addPropSelect('color', propColors, 'Цвет'));
                    jQuery("#special_props").append(div_col1);
                    jQuery("#special_props").append(div_col2);
                    break;
                case '4':
                    var div_col1 = jQuery(document.createElement('div'));
                    div_col1.addClass('col-md-6');
                    div_col1.append(addPropSelect('color', propColors, 'Цвет'));
                    jQuery("#special_props").append(div_col1);
                    break;
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
                color = jQuery("#color").length > 0 ? jQuery("#color").val() : "",
                empty_flag = false,
                warning_text = "";
            console.log("category",category);
            console.log("goodsName",goodsName);
            console.log("goodsUnit",goodsUnit);
            console.log("goodsMultiplicity",goodsMultiplicity);
            console.log("goodsPrice",goodsPrice);
            console.log("texture",texture);
            console.log("manufacturer",manufacturer);
            console.log("width",width);
            console.log("color",color);
            if(empty(category)){
                empty_flag = true;
                warning_text = "Не выбрана категория!";
            }
            if(empty(goodsName)){
                empty_flag = true;
                warning_text = "Пустое наименование!";
            }
            if(empty(goodsUnit)){
                empty_flag = true;
                warning_text = "Не выбрана едница измерения!";
            }
            if(empty(goodsMultiplicity)){
                empty_flag = true;
                warning_text = "Пустая кратность!";
            }
            if(empty(goodsPrice)){
                empty_flag = true;
                warning_text = "Пустая цена!";
            }

            if(!empty_flag) {
                jQuery.ajax({
                    url: "index.php?option=com_gm_ceiling&task=stock.addGoods",
                    type: "post",
                    data: {
                        category: category,
                        goodsName: goodsName,
                        goodsUnit: goodsUnit,
                        goodsMultiplicity: goodsMultiplicity,
                        goodsPrice: goodsPrice,
                        texture: texture,
                        manufacturer: manufacturer,
                        width: width,
                        color: color
                    },
                    dataType: "json",
                    async: false,
                    success: function (data) {
                        location.reload();
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
            else{
                noty({
                    timeout: 2000,
                    theme: 'relax',
                    layout: 'center',
                    maxVisible: 5,
                    type: "error",
                    text: warning_text
                });
            }
        });

        jQuery('#new_category').click(function(){
            jQuery('#new_category_info').toggle();
        });

        jQuery('#create_new_category').click(function () {
            var name = jQuery('#new_category_name').val();
            if(!empty(name)){
                jQuery.ajax({
                    url: "index.php?option=com_gm_ceiling&task=stock.createCategory",
                    type: "post",
                    data: {
                        name: name
                    },
                    dataType: "json",
                    async: false,
                    success: function (data) {
                        noty({
                            timeout: 2000,
                            theme: 'relax',
                            layout: 'center',
                            maxVisible: 5,
                            type: "success",
                            text: "Категория добавлена!"
                        });
                        setTimeout(function(){location.reload()},2000);
                    },
                    error: function (data) {
                        noty({
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
            else{
                noty({
                    timeout: 2000,
                    theme: 'relax',
                    layout: 'center',
                    maxVisible: 5,
                    type: "error",
                    text: "Название категории не может быть пустым!"
                });
            }
        });
    });

    function addPropSelect(selectId, propValueArray, labelText) {
        var select = jQuery(document.createElement('select')),
            rowDiv = jQuery(document.createElement(('div')));
        rowDiv.addClass('row');
        select.attr('id', selectId);
        select.addClass('form-control');
        for(var i = 0; i < propValueArray.length; i++){
            select.append('<option value = "'+propValueArray[i].id+'">'+propValueArray[i].value+'</option>');
        }
        var label = document.createElement('label');
        label.innerHTML = labelText;
        rowDiv.append(label);
        rowDiv.append(document.createElement('br'));
        rowDiv.append(select);
        rowDiv.append(document.createElement('br'));

        return rowDiv;
    }



    /*function fillSelect(selectId, data) {
        jQuery(selectId).empty();
        for(var i = 0; i < data.length; i++){
            jQuery(selectId).append('<option value = "'+data[i].id+'">'+data[i].value+'</option>');
        }
    }*/

    function allGoods(goods){
        var tr, td, tbody_goods = document.getElementById('tbody_goods');
        tbody_goods.innerHTML = '';
        console.log(goods.length);
        for (var i = 0; i < goods.length; i++) {
            console.log(goods[i]);
            tr = tbody_goods.insertRow();
            td = tr.insertCell();
            td.innerHTML = goods[i].id;
            td = tr.insertCell();
            td.innerHTML = goods[i].name;

            var stocks = "";
            if (!empty(goods[i].stocks_count)) {
                for (var j = 0; j < goods[i].stocks_count.length; j++) {
                    stocks += goods[i].stocks_count[j].name + ': ' + goods[i].stocks_count[j].count + "<html> <br> </html>";
                }
            }
            td = tr.insertCell();
            td.innerHTML = stocks;
        }
    }

</script>