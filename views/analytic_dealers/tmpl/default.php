<?php
/**
 * @version    CVS: 0.1.7
 * @package    Com_Gm_ceiling
 * @author     SpectralEye <Xander@spectraleye.ru>
 * @copyright  2016 SpectralEye
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;
$date_from = date('Y-m-d');
$date_to = date('Y-m-d');
$model = Gm_ceilingHelpersGm_ceiling::getModel('Analytic_Dealers');
$data = json_encode($model->getData($date_from,$date_to));
?>
<div class="row" align="right">
	<div>
		<label for="date_from">Выбрать с: </label>
		<input type="date" name="date_from" id = "date_from" class="input-gm" value="<?php echo $date_from;?>">
	</div>
	<div>
		<label for ="date_to">по:</label>
		<input type="date" name="date_to" id = "date_to" class="input-gm" value="<?php echo $date_to;?>">
	</div>
</div>
<table id = "analytic" class="analitic-table">
	<thead class = "caption-style-tar">
		<th>
			Дилер
		</th>
		<th>
			Кол-во проектов
		</th>
		<th>
			Кол-во потолков
		</th>
		<th>
			Квадратура
		</th>
		<?php if(!empty($proizv)){
			foreach($proizv as $key=>$value){?>
				<th data-id="<?php echo $key;?>"><?php echo $value;?></th>
		<?php }
		}?>
		<th>
			Стоимость
		</th>
		<th>
			Себестоимость
		</th>
		<th>
			Стоимость комплектуюших
		</th>
		<th>
			Себестоимость комплектующих
		</th>
	</thead>
	<tbody>
	</tbody>
</table>
<table id = "analytic2" class="analitic-table">
	<thead id = "thead" class = "caption-style-tar">
		
	</thead>
	<tbody>
	</tbody>
</table>
<script type="text/javascript">
	var data = JSON.parse('<?php echo $data;?>');
	console.log(data);
	jQuery(document).ready(function(){
		fill_data(data);
		makeTh(jQuery("#thead"),data[0]);
	});
	function fill_data(data){
		jQuery('#analytic tbody').empty();
		for(let i = 0;i<data.length;i++){
			jQuery('#analytic').append('<tr></tr>');
			for(let j=0;j<Object.keys(data[i]).length;j++){
				if(Object.keys(data[i])[j] != 'projects' && Object.keys(data[i])[j] != 'proizvs' && Object.keys(data[i])[j] != 'id'){

					jQuery('#analytic > tbody > tr:last').append('<td>'+data[i][Object.keys(data[i])[j]] +'</td>');
				}
			}
		}
	}
	jQuery("#date_from").change(function(){
		var date_from = jQuery('#date_from').val(),
		date_to = jQuery("#date_to").val()

		if(date_from <= date_to){
			getData(date_from,date_to);
		}
		else{
			var n = noty({
                timeout: 2000,
                theme: 'relax',
                layout: 'center',
                maxVisible: 5,
                type: "error",
                text: "Начальная дата не может быть больше конечной!"
            });
		}
	});

	jQuery("#date_to").change(function(){
		var date_from = jQuery('#date_from').val(),
		date_to = jQuery("#date_to").val()

		if(date_from <= date_to){
			getData(date_from,date_to);
		}
		else{
			var n = noty({
                timeout: 2000,
                theme: 'relax',
                layout: 'center',
                maxVisible: 5,
                type: "error",
                text: "Начальная дата не может быть больше конечной!"
            });
		}
	});

	function getData(date_from,date_to){
		console.log("123");
		jQuery.ajax({
            url: "index.php?option=com_gm_ceiling&task=getDealersAnalyticData",
            data: {
                date_from:date_from,
                date_to:date_to
            },
            dataType: "json",
            async: true,
            success: function (data) {
            	console.log(data);
                fill_data(data);
            },
            error: function (data) {
                console.log(data.responseText);
            }
        });
	}

	function makeTh(container, data) {
        var row = jQuery("<tr/>");
        jQuery.each(data, function(key, value) { 
            row.append(jQuery("<th/ data-value = "+key+">").text(value));
        });
		container.append(row);
	}
</script>