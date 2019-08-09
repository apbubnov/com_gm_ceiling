<?php

?>

<h1 class="center">Приём товаров</h1>
<br>
<div class="container">
	<div class="row justify-content-md-center">
		<div class="col-md-4 center">
			<label>Штрихкод</label><br>
			<input type="text" id="goods_id" class="form-control" style="width: 70%; display: inline-block;">
			<button class="btn btn-large btn-primary" id="btn_show" style="margin-bottom: 4px;">ОК</button>
		</div>
		<div class="col-md-4 center"> 
			<label>Поставщик</label><br>
			<select id="selectCounterparty" class="form-control">
			</select>
		</div>
		<div class="col-md-4 center">
			<label>Склад</label><br>
			<select id="selectStocks" class="form-control">
			</select>
		</div>
	</div>
</div>

<br>
<div>
	<table class="table"  id="tgoods" cellspacing="0" width="100%">
		<thead>
			<th>Штрихкод</th>
			<th>Наименование</th>
			<th>Кол-во</th>
			<th>Себестоимость</th>
			<th></th>
		</thead>
		<tbody id="tbody_goods"></tbody>
	</table>

	<div class="right">
		<button class="btn btn-large btn-primary" id="save_btn">Сохранить</button>
	</div>
</div>

<script type="text/javascript">

	var INPUT_COUNT='<input class="count center"/>',
	INPUT_COST='<input class="cost center"/>',
	BUTTON_DELETE='<button type="button" class="btn btn-danger delete"> <i class="fas fa-trash-alt"></i> </button>';

	jQuery(document).mouseup(function (e){
		var div = jQuery("#mw_add_good");
		if (!div.is(e.target) && div.has(e.target).length === 0) { 
			jQuery("#btn_close").hide();
			jQuery("#mw_container").hide();
			div.hide();
		}
	});
	
	jQuery(document).ready(function(){
		jQuery("#add_position").click(function(){
			jQuery("#btn_close").show();
			jQuery("#mw_container").show();
			jQuery("#mw_add_good").show('slow');
		});
		showCounterparty();
		showStocks();
	});

	document.getElementById('btn_show').onclick = function() {
		getData();
	};

	function getData() {
		jQuery.ajax({
			type: 'POST',
			url: "index.php?option=com_gm_ceiling&task=stock.getStockGoods",
			data: {
				goods_id: jQuery('#goods_id').val()
			},
			success: function(data){
				console.log(data);
				if (empty(data)) {
					var n = noty({
						timeout: 2000,
						theme: 'relax',
						layout: 'center',
						maxVisible: 5,
						type: "warning",
						text: "Товар с данным кодом не найден!"
					});
				} else {
					showTableData(data);
				}
			},
			dataType:"json",
			async: false,
			timeout: 10000,
			error: function(data){
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

	function showTableData(data) {
		var tr, td, com_sum = 0,
		table_body_elem = document.getElementById('tbody_goods');
		for (var i = 0; i < data.length; i++) {
			tr = table_body_elem.insertRow();
			tr.setAttribute('data-id', data[i].id);
			td = tr.insertCell();
			td.innerHTML = data[i].id;
			td = tr.insertCell();
			td.innerHTML = data[i].name;
			td = tr.insertCell();
			td.innerHTML = INPUT_COUNT;
			td = tr.insertCell();
			td.innerHTML = INPUT_COST;
			td = tr.insertCell();
			td.innerHTML = BUTTON_DELETE;
		}
	}

	jQuery('body').on('click','.delete', function () {
		jQuery(this).closest('tr').remove();
	});

	function showCounterparty() {
		jQuery.ajax({
			type: 'POST',
			url: "index.php?option=com_gm_ceiling&task=stock.getCounterparty",
			success: function(data){
				console.log(data);
				fillSelect(data, "#selectCounterparty");
			},
			dataType:"json",
			async: false,
			timeout: 10000,
			error: function(data){
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

	function showStocks() {
		jQuery.ajax({
			type: 'POST',
			url: "index.php?option=com_gm_ceiling&task=stock.getStocks",
			success: function(data){
				console.log(data);
				fillSelect(data, "#selectStocks");
			},
			dataType:"json",
			async: false,
			timeout: 10000,
			error: function(data){
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
			selectCounterparty.append( jQuery('<option>', {
				value: data[i].id,
				text: data[i].name
			}));
		}
	}

	document.getElementById('save_btn').onclick = function() {
		collectDataTable();
	};

	var array = [];
	function collectDataTable(){
		var rows = jQuery('#tbody_goods > tr');
		array = [];
		for (var i = 0; i < rows.length; i++) {
			var inpCount = jQuery(rows[i]).find('.count').val();
			var inpCost = jQuery(rows[i]).find('.cost').val();

			var id = rows[i].getAttribute("data-id");
			array.push({
				id: id,
				count: inpCount,
				cost: inpCost
			});
		}

		saveData();
	}

	function saveData() {
		jQuery.ajax({
			type: 'POST',
			url: "index.php?option=com_gm_ceiling&task=stock.saveInventory",
			data: {
				array: array,
				id_stock: jQuery('#selectStocks').val(),
				id_counterparty: jQuery('#selectCounterparty').val()
			},
			success: function(data){
				console.log(data);
				var n = noty({
					timeout: 2000,
					theme: 'relax',
					layout: 'center',
					maxVisible: 5,
					type: "success",
					text: "Успешно!"
				});
				document.getElementById('tbody_goods').innerHTML = '';
			},
			dataType:"json",
			async: false,
			timeout: 10000,
			error: function(data){
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


</script>