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
	<thead id = "thead" class = "caption-style-tar">
	</thead>
	<tbody>
	</tbody>
</table>
 <div class="modal_window_container" id="mw_container">
    <button type="button" class="close_btn" id="close_mw"><i class="fa fa-times fa-times-tar" aria-hidden="true"></i></button>
    <div id="mw_detailed" class="modal_window">
    	<table id="detailed_analytic" class = "table_project_analitic">
    		<thead>
	    		<tr id="caption-tr">
	    			<th>
	    				№ проекта
	    			</th>
	    			<th>
	    				Адрес
	    			</th>
	    			<th>
	    				Квадратура
	    			</th>
	    		</tr>
    		</thead>
    		<tbody>
    		</tbody>
    	</table>
	</div>
</div>

<script type="text/javascript">
	var data = JSON.parse('<?php echo $data;?>');

	jQuery(document).mouseup(function (e){ // событие клика по веб-документу
        var div = jQuery("#mw_detailed"); // тут указываем ID элемента
        if (!div.is(e.target) // если клик был не по нашему блоку
            && div.has(e.target).length === 0) { // и не по его дочерним элементам
            jQuery("#mw_detailed").hide();
            jQuery("#close-modal-window").hide();
            jQuery("#mw_container").hide();
        }
    });

	jQuery(document).ready(function(){
		makeTh(jQuery("#thead"),data[0]);
		data.shift();
		fill_table(data);
	});

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
            	makeTh(jQuery("#thead"),data[0]);
				data.shift();
                fill_table(data);
            },
            error: function (data) {
                console.log(data.responseText);
                var n = noty({
                timeout: 2000,
                theme: 'relax',
                layout: 'center',
                maxVisible: 5,
                type: "error",
                text: "Произошла ошибка, попробуйте позднее!"
            });
            }
        });
	}

	function makeTh(container, data) {
        var row = jQuery("<tr/>");
		container.empty();
        jQuery.each(data, function(key, value) { 
            row.append(jQuery("<th/ data-value = '"+key+"'>").text(value));
        });
		container.append(row);
	}

	function fill_table(data){
		var ths = jQuery("#analytic > thead  th"),key ="",total = [];
		jQuery('#analytic tbody').empty();
		for(let i = 0;i<data.length;i++){
			jQuery('#analytic').append('<tr data-dealer_id = "'+data[i].id+'"></tr>');
			jQuery.each(ths,function(index,item){
				key = jQuery(item).data('value');
				let val = (data[i][key] ? data[i][key] : 0); 
				jQuery('#analytic > tbody > tr:last').append('<td>'+ val +'</td>');
				if(key == 'name'){
					total[key] = '<b>Итого</b>';
				}
				else{
					total[key] = (total[key]) ? total[key] + val : val;
				}
				
			});
			
		}
		if(Object.keys(total).length){
			jQuery('#analytic').append('<tr></tr>');
			jQuery.each(ths,function(index,item){
				key = jQuery(item).data('value');
				jQuery('#analytic > tbody > tr:last').append('<td><b>'+ ((key!='name') ? total[key].toFixed(2) : total[key]) +'</b></td>');
			});
		}

		jQuery("#analytic tr").click(function(){
			var dealer_id = jQuery(this).data('dealer_id'),projects = [];
			console.log(dealer_id);
			data.forEach(function(elem){
				if(elem.id == dealer_id){
					projects = Object.keys(elem.projects);
				}
				
			});
			console.log(projects);
			jQuery.ajax({
	            url: "index.php?option=com_gm_ceiling&task=projects.getProjectsInfo",
	            data: {
	               projects:projects
	            },
	            dataType: "json",
	            async: true,
	            success: function (data) {
	            	console.log(data);
	            	create_detailed_table(data);
	            	jQuery("#close_mw").show();
	                jQuery("#mw_container").show();
	                jQuery("#mw_detailed").show('slow');
	            },
	            error: function (data) {
	                console.log(data.responseText);
	                var n = noty({
	                timeout: 2000,
	                theme: 'relax',
	                layout: 'center',
	                maxVisible: 5,
	                type: "error",
	                text: "Произошла ошибка, попробуйте позднее!"
	            });
	            }
	        });
			console.log(projects);
		});

		function create_detailed_table(data){
			jQuery('#detailed_analytic tbody').empty();
			for(let i = 0;i<data.length;i++){
				console.log(data[i]);
				jQuery('#detailed_analytic').append('<tr></tr>');
				jQuery('#detailed_analytic > tbody > tr:last').append('<td>'+ data[i].id+'</td>');
				jQuery('#detailed_analytic > tbody > tr:last').append('<td>'+ data[i].project_info+'</td>');
				jQuery('#detailed_analytic > tbody > tr:last').append('<td>'+ data[i].quadr+'</td>');
			}	
		}
	}

	
</script>