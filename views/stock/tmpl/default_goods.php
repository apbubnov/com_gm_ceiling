<?php
    $stockModel = Gm_ceilingHelpersGm_ceiling::getModel('stock');
    $categories = $stockModel->getGoodsCategories();
    $textures = json_encode($stockModel->getPropTextures());
    $widths = json_encode($stockModel->getPropCanvasWidths());
    $colors = json_encode($stockModel->getPropColors());
    $manufacturers = json_encode($stockModel->getPropManufacturers());

    $goods_json = json_encode($stockModel->getGoods());
?>
<div class="container">
	<div class="row">
		<h1>Создание нового товара</h1>
	</div>
	<div class="row" style="border: 1px solid #414099;border-radius: 5px;">
		<div class="col-md-6" style="margin-bottom: 5px;margin-top: 5px;">
			<select id="goods_categories" class="input-gm">
                <option value="0">Все категории</option>
                <?php
                    foreach ($categories as $item) {
                        echo "<option value=\"$item->id\">$item->value</option>";
                    }
                ?>
			</select>
            <div id="props"></div>
		</div>
	</div>
	<div class="row">
		<h1>Список существующих товаров</h1>
		<table class="table table_cashbox">
			<thead>
				<th>Штрихкод</th>
				<th>Наименование</th>
				<th>Наличие</th>
			</thead>
			<tbody>
				<tr>
					<td>0000001</td>
					<td>Компонент1</td>
					<td>
						Склад1: 2 шт<br>
						Склад1: 999 шт<br>
					</td>
				</tr>
			</tbody>
		</table>
	</div>

<h4 id="h4_common_sum"></h4>

<link href="/libraries/MDB-Free_4.7.1/css/addons/datatables.min.css" rel="stylesheet">
<script type="text/javascript" src="/libraries/MDB-Free_4.7.1/js/mdb.min.js"></script>
<script type="text/javascript" src="/libraries/MDB-Free_4.7.1/js/addons/datatables.min.js"></script>
</div>

<script type="text/javascript">
    var propCanvasWidths = JSON.parse('<?php echo $widths;?>'),
        propTextures = JSON.parse('<?php echo $textures;?>'),
        propColors = JSON.parse('<?php echo $colors;?>'),
        propManufacturers = JSON.parse('<?php echo $manufacturers;?>'),
        goods = JSON.parse('<?php echo $goods_json;?>');

    jQuery(document).ready(function(){
        console.log(goods);
        console.log(propCanvasWidths);
        console.log(propTextures);
        console.log(propColors);
        console.log(propManufacturers);

        jQuery('#goods_categories').change(function () {
            console.log(this.value);
            jQuery('#props').empty();
            switch(this.value) {
                case '1':
                    addPropSelect('canvases_textures', propTextures, 'Текстура');
                    addPropSelect('canvases_manufacturers', propManufacturers, 'Производитель');
                    addPropSelect('canvases_widths', propCanvasWidths, 'Ширина');
                    addPropSelect('color', propColors, 'Цвет');
                    break;
                case '4':
                    addPropSelect('color', propColors, 'Цвет');
                    break;
            }
        });
    });

    function addPropSelect(selectId, propValueArray, labelText) {
        var select = document.createElement('select');
        select.setAttribute('id', selectId);
        select.classList.add('input-gm');
        var label = document.createElement('label');
        label.innerHTML = labelText;
        jQuery('#props')[0].append(label);
        jQuery('#props')[0].append(document.createElement('br'));
        jQuery('#props')[0].append(select);
        jQuery('#props')[0].append(document.createElement('br'));
        fillSelect('#'+selectId, propValueArray);
    }

    function fillSelect(selectId, data) {
        jQuery(selectId).empty();
        for(var i = 0; i < data.length; i++){
            jQuery(selectId).append('<option value = "'+data[i].id+'">'+data[i].value+'</option>');
        }
    }

</script>