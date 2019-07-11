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