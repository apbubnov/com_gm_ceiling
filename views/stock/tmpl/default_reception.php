<?php

?>
<style>
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
</style>

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
<div class="ModalDoc" style="display: none;" id="mw_doc">
    <div class="Document">
        <iframe class="iFrame" >

        </iframe>
        <div class="Actions">
            <div class="CheckBox">
                <div class="Name">Оприходование товаров</div>
                <input type="checkbox" id="InventoryOfGoods" name="page">
            </div>
            <div class="CheckBox">
                <div class="Name">Приходный кассовый ордер</div>
                <input type="checkbox" id="RetailCashOrder" name="page">
            </div>
            <div class="Right">
                <button type="button" onclick="print_frame();"><i class="fa fa-print" aria-hidden="true"></i></button>
                <button type="button" onclick="save_frame();"><i class="fas fa-save" aria-hidden="true"></i></button>
                <button type="button" onclick="close_frame();"><i class="fa fa-times" aria-hidden="true"></i></button>
            </div>
        </div>
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
				//document.getElementById('tbody_goods').innerHTML = '';
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

    function LoadPDF() {
        var checkbox = jQuery(".ModalDoc .Document .Actions .CheckBox input[type=\"checkbox\"]:checked"),
            values = [];
        console.log("cbx",checkbox);
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