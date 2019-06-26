<?php
    $stockModel = Gm_ceilingHelpersGm_ceiling::getModel('stock');
    $categories = json_encode($stockModel->getGoodsCategories());
    $textures = json_encode($stockModel->getPropTextures());
    $widths = json_encode($stockModel->getPropCanvasWidths());
    $colors = json_encode($stockModel->getPropColors());
    $manufacturers = json_encode($stockModel->getPropManufacturers());
?>
<div class="container">
	<div class="row">
		<h1>Создание нового товара</h1>
	</div>
	<div class="row" style="border: 1px solid #414099;border-radius: 5px;">
		<div class="col-md-6" style="margin-bottom: 5px;margin-top: 5px;">
			<select id = "goods_categories" class="input-gm">
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
	<table class="table table-striped table-bordered  table_cashbox" cellspacing="0" width="100%" id="projectList">
	<thead>
		<tr>
			<th>Штрихкод</th>
			<th>Наименование</th>
			<th>Наличие</th>
		</tr>
	</thead>
	<tbody></tbody>
</table>
<h4 id="h4_common_sum"></h4>

<link href="/libraries/MDB-Free_4.7.1/css/addons/datatables.min.css" rel="stylesheet">
<script type="text/javascript" src="/libraries/MDB-Free_4.7.1/js/mdb.min.js"></script>
<script type="text/javascript" src="/libraries/MDB-Free_4.7.1/js/addons/datatables.min.js"></script>
</div>

<script type="text/javascript">
    var goodsCategories = JSON.parse('<?php echo $categories;?>'),
        propCanvasWidths = JSON.parse('<?php echo $widths;?>'),
        propTextures = JSON.parse('<?php echo $textures;?>'),
        propColors = JSON.parse('<?php echo $colors;?>'),
        propManufacturers = JSON.parse('<?php echo $manufacturers;?>');

    jQuery(document).ready(function(){
        console.log(goodsCategories);
        console.log(propCanvasWidths);
        console.log(propTextures);
        console.log(propColors);
        console.log(propManufacturers);
        fillSelect("#goods_categories",goodsCategories);
        jQuery("#goods_categories").change(function () {
            console.log(this.value);
            switch(this.value){
                case "1":
                    jQuery("#props").empty();
                    addPropSelect("canvases_textures",propTextures);
                    addPropSelect("canvases_manufacturers",propManufacturers);
                    addPropSelect("canvases_widths",propCanvasWidths);
                    addPropSelect("color",propColors);
                    break;
            }
        });
    });

    function addPropSelect(selectId,propValueArray) {
        var select = document.createElement('select');
        select.setAttribute('id',selectId);
        select.classList.add('input-gm');
        jQuery("#props")[0].append(select);
        for(var i = 0;i<propValueArray.length;i++){
            jQuery("#"+selectId)
                .append('<option value = "'+propValueArray[i].id+'">'+propValueArray[i].value+'</option>');
        }
    }

    function fillSelect(selectId,data){
        jQuery(selectId).empty();
        for(var i = 0; i < data.length; i++){
            jQuery(selectId).append('<option value = "'+data[i].id+'">'+data[i].value+'</option>');
        }
    }

</script>