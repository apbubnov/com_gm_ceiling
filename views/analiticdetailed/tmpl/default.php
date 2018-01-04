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

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');

$user       = JFactory::getUser();
$userId     = $user->get('id');
$today = date('Y-m-d');
?>
<h2>Аналитика</h2>
<form action="" method="post" name="adminForm" id="analiticForm">

	<div class = "analitic-actions">
		Выбрать с <input type = "date" id = "date1"> по <input type ="date" id = "date2"> <button type="button" class = "btn btn-primary" id = "show_all">Показать всё</button>
	</div>
	<table id = "analitic-table">
		<thead class = "caption-style-tar">
			<th>Реклама</th>
			<th>Всего</th>
            <th>Дилеры</th>
			<th>Реклама</th>
            <th>Отказ от сотрудничества</th>
            <th>Отказ от замера</th>
			<th>Замеры</th>
			<th>Отказ от договора</th>
			<th>Договоры</th>
			<th>Монтажи</th>
			<th>Закрытые</th>
			<th></th>
			</thead> 

		<?php foreach ($this->item as $item) {?>
			<tr>
				<td>
					<?php echo $item->name;?>
				</td>
				<td>
					<?php 
						$all_common +=$item->common;
						echo $item->common;
					?>
				</td>
				<td>
					<?php
						$all_dealers+= $item->dealers;
					 	echo $item->dealers;
					 ?>
				</td>
				<td>
					<?php
						$all_advt+=$item->advt;
						echo $item->advt;
					?>
				</td>
				<td>
					<?php 
						$all_refused+=$item->refused;
						echo $item->refused;
					?>
				</td>
				<td>
					<?php 
						$all_ref_measure+=$item->ref_measure;
						echo $item->ref_measure;
					?>
				</td>
				<td>
					<?php
						$all_measure+= $item->measure; 
						echo $item->measure;
					?>
				</td>
				<td>
					<?php
						$all_ref_deals+= $item->ref_deals;
					 	echo $item->ref_deals;
					 ?>
				</td>
				<td>
					<?php
						$all_deals+= $item->deals;
					 	echo $item->deals;
					 ?>
				</td>
				<td>
					<?php 
						$all_mounts+=$item->mounts;
						echo $item->mounts;
					?>
				</td>
				<td>
					<?php 
						$all_closed+=$item->closed;
						echo $item->closed;
					?>
				</td>
				<td>
					<button type="button" class='clear_form_group btn btn-primary'> <i class="fa fa-eye-slash" aria-hidden="true"></i> </button>
				</td>
			</tr>
			
		<?php  }?>
		<tr  >
				<td><b>Итого</b></td>
				<td><b><?php echo $all_common;?></b></td>
				<td><b><?php echo $all_dealers;?></b></td>
				<td><b><?php echo $all_advt;?></b></td>
				<td><b><?php echo $all_refused;?></b></td>
				<td><b><?php echo $all_ref_measure;?></b></td>
				<td><b><?php echo $all_measure;?></b></td>
				<td><b><?php echo $all_ref_deals;?></b></td>
				<td><b><?php echo $all_deals;?></b></td>
				<td><b><?php echo $all_mounts;?></b></td>
				<td><b><?php echo $all_closed;?></b></td>
			</tr>
	</table>
</form>

<script>
	 jQuery(document).ready(function(){
        hideEmptyTr();
		jQuery("#date1").val("<?php echo $today?>");
		jQuery("#date2").val("<?php echo $today?>");
		all_count = []; 
		all_count['name'] = "Итого:"; 
		all_count['common'] = <?php echo $all_common;?>;
		all_count['all_measure'] = <?php echo $all_measure;?>;
		all_count['all_ref_measure'] = <?php echo $all_ref_measure;?>;
		all_count['all_deals'] = <?php echo $all_deals;?>;
		all_count['all_ref_deals'] = <?php echo $all_ref_deals;?>;
		all_count['all_dealers'] = <?php echo $all_dealers;?>;
		all_count['all_advt'] = <?php echo $all_advt;?>;
		all_count['all_closed'] = <?php echo $all_closed;?>;
		all_count['all_mounts'] = <?php echo $all_mounts;?>;
		all_count['all_refused'] = <?php echo $all_refused;?>;
		// функция получения сведения о браузере
		function GetNameBrowser(){
			var ua = navigator.userAgent;    
			if (ua.search(/Chrome/) > 0) return 'Google Chrome';
			if (ua.search(/Firefox/) > 0) return 'Firefox';
			if (ua.search(/Safari/) > 0) return 'Safari';
			if (ua.search(/MSIE/) > 0) return 'Internet Explorer';
			return 'Не определен';
		}

		// узнаем браузер
		var browser = GetNameBrowser();
		

		//наложение маски на время в мозиле
		/*if (browser == "Firefox") {
			var options = {
					year: 'numeric',
					month: 'numeric',
					day: 'numeric',
					timezone: 'UTC'
				};

//alert(new Date().toLocaleString("ru", options));
			jQuery("#date1").mask("99.99.9999");
			jQuery("#date2").mask("99.99.9999");
			jQuery("#date1").val(new Date("<? echo $today;?>").toLocaleString("ru", options));
			jQuery("#date2").val(new Date("<? echo $today;?>").toLocaleString("ru", options));
			jQuery("#date2").keypress(function(){
				var date1 = transform_date(jQuery("#date1").val());
				var date2 = transform_date(this.value);
				if(test_date(date2,date1))
					fill_table(date1,date2);
			})
		}
		else{*/
		jQuery("#date2").change(function(){
			
			var date1 = jQuery("#date1").val();
			if(test_date(this.value,date1))
				fill_table(date1,this.value);
		})
		//}
	})
    function hideEmptyTr(){
        jQuery("#analitic-table tbody tr").each(function(){
            var tds = jQuery("td",this);
            var empty = true;
            for(var i = 1;i<tds.length-1;i++){
               console.log(tds[i].innerHTML.length)
                if(tds[i].innerHTML.trim() != "0"){
                    empty = false;
                }
                console.log(i+"->"+tds[i].innerHTML);

            }
            if(empty){
                this.style.display = "none";
            }
        });
    }
	function test_date(date1,date2){
		var reg = /^\d{4}\-\d{2}\-\d{2}$/;
		return reg.test(date1)&&reg.test(date2) ? true : false;
	}
	function transform_date(date){
		var year = date.substr(6);
		var month = date.substr(3, 2);
		var day = date.substr(0,2);
		var result = year+'-'+month+'-'+day;
		return result;
	}

	jQuery("#show_all").click(function(){
		jQuery('#analitic-table > tbody > tr').show();
		jQuery('#analitic-table > tbody > tr:last').remove();
		jQuery('#analitic-table').append('<tr></tr>');
		console.log(all_count);
		for(var i in all_count){
			jQuery('#analitic-table > tbody > tr:last').append('<td> <b>'+all_count[i]+'<b></td>');
		}
	})
	jQuery(".clear_form_group").click(function () {
        jQuery(this).closest("tr").hide();
		var tr = jQuery(this).closest("tr");
		tr = tr[0];
		var arr = [];
		for(var i = 1;i<tr.children.length;i++){
			arr.push(+tr.children[i].childNodes[0].data);
		}
		update_total(arr);
    });

	function update_total(arr){
		var tr = jQuery('#analitic-table > tbody > tr:last');
		tr = tr[0];
		for(var i = 1;i<tr.children.length;i++){
			tr.children[i].children[0].childNodes[0].data = tr.children[i].children[0].childNodes[0].data - arr[i-1];
		}
	}
	function fill_table(date1,date2){
		if(date1<=date2){
			all_count = [];
			jQuery.ajax({
                    url: "index.php?option=com_gm_ceiling&task=getDetailedAnaliticByPeriod",
                    data: {
                        date1: date1,
                        date2: date2
                    },
                    dataType: "json",
                    async: true,
                    success: function (data) {
						console.log(data);
						for(var i = 0;i<data.length;i++){
							delete data[i].id;
						}
						jQuery('#analitic-table tbody').empty();
						for(var i=0;i<data.length;i++) {
							jQuery('#analitic-table').append('<tr></tr>');
							for(var j=0;j<Object.keys(data[i]).length;j++){
								all_count['name'] = "Итого:";
								if(Object.keys(data[i])[j]!='name'){
									if(all_count[Object.keys(data[i])[j]]==undefined){
										all_count[Object.keys(data[i])[j]] = 0;
									}
									all_count[Object.keys(data[i])[j]]+=data[i][Object.keys(data[i])[j]]-0;
								}
								jQuery('#analitic-table > tbody > tr:last').append('<td>'+data[i][Object.keys(data[i])[j]]+'</td>');
							}
							jQuery('#analitic-table > tbody > tr:last').append("<td><button class='clear_form_group btn btn-primary' type='button'><i class=\"fa fa-eye-slash\" aria-hidden=\"true\"></i></button></td> ");
						}
						
						jQuery('#analitic-table').append('<tr></tr>');
						for(var i in all_count){
						
							jQuery('#analitic-table > tbody > tr:last').append('<td> <b>'+all_count[i]+'<b></td>');
						}
						jQuery(".clear_form_group").click(function () {
							jQuery(this).closest("tr").hide();
							var tr = jQuery(this).closest("tr");
							tr = tr[0];
							var arr = [];
							for(var i = 1;i<tr.children.length;i++){
								arr.push(+tr.children[i].childNodes[0].data);
							}
							update_total(arr);
						});
						hideEmptyTr();
                    },
                    error: function (data) {
                        var n = noty({
                            timeout: 2000,
                            theme: 'relax',
                            layout: 'center',
                            maxVisible: 5,
                            type: "error",
                            text: "Ошибка получения данных"
                        });
                    }
                });
		}
		else{
			var n = noty({
						timeout: 2000,
						theme: 'relax',
						layout: 'center',
						maxVisible: 5,
						type: "error",
						text: "Начальная дата не может быть больше конечной"
                    });
		
		}

	}
//https://javascript.ru/forum/jquery/15210-zapolnit-tablicu.html	
</script>
