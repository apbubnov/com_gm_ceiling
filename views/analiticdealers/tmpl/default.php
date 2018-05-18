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
try {

    $today = date('Y-m-d');
    $jinput = JFactory::getApplication()->input;
    $api = $jinput->get('api', 0, 'INT');
    $user_id = $jinput->get('user_id',null,'INT');
   	$user = JFactory::getUser($user_id);
    $common_analitic_model = Gm_ceilingHelpersGm_ceiling::getModel('Analiticcommon'); 
    $det_analitic_model  = Gm_ceilingHelpersGm_ceiling::getModel('AnaliticDetailed');
    $c_items = $common_analitic_model->getData($user->dealer_id);
    $d_items = $det_analitic_model->getData(null,null,$user->dealer_id);
    $phones_model = Gm_ceilingHelpersGm_ceiling::getModel('api_phones');
	
}
catch (Exception $e) {
	$date = date("d.m.Y H:i:s");
	$files = "components/com_gm_ceiling/";
	file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
	throw new Exception('Ошибка!', 500);
}
if(!$api){
	echo parent::getButtonBack();
}


?>
<?php if ($api): ?>
    <style type="text/css">
        header
        {
            display: none;
        }
        footer
        {
            display: none;
        }
    </style>
<?php endif ?>

<h2> Общая аналитика</h2>
<form action="" method="post" name="adminForm" id="analiticForm">

	<div class = "analitic-actions">
		Выбрать с <input type = "date" id = "c_date1"> по <input type ="date" id = "c_date2"> <button type="button" class = "btn btn-primary" id = "c_show_all">Показать всё</button>
	</div>
	<table id = "c_analitic-table" class = "rwd-table">
		<thead class = "caption-style-tar">
			<th>Реклама</th>
            <th data-value = "all">Всего</th>
            <th data-value = "(0,2,3)">В работе</th>
            <th data-value = "(1)">Замеры</th>
            <th data-value = "(4,5,6,7,8,10,11,12,16,17,19)">Договоры</th>
            <th data-value = "(12)">Завершенные</th>
            <th data-value = "(12)">Сумма</th>
            <th data-value = "(12)">Прибыль</th>
            <th></th>
			</thead> 

		<?php foreach ($c_items as $item) {?>
			<tr data-value = "<?php echo $item->name;?>" >
				<td data-th = "Реклама">
					<?php echo $item->name;?>
				</td>
				<td data-th = "Всего">
					<?php 
						$c_all_common +=$item->common;
						echo $item->common;
					?>
				</td>
				<td data-th = "В работе">
					<?php
						$c_all_inwork+=$item->inwork;
						echo $item->inwork;
					?>
				</td>
				<td data-th = "Замеры" >
					<?php 
						$c_all_measure+=$item->measure;
						echo $item->measure;
					?>
				</td>
				<td data-th = "Договоры">
					<?php
						$c_all_deals+= $item->deals;
					 	echo $item->deals;
					 ?>
				</td>
                <td data-th = "Завершенные">
                    <?php
                        $c_all_done+=$item->done;
                        echo $item->done; 
                    ?>
                </td>
                <td data-th = "Сумма">
                    <?php
                        $c_all_sum +=round($item->sum,2);
                        echo floor($item->sum);
                    ?>
                </td>
                <td data-th = "Прибыль">
                    <?php
                        $c_all_profit +=round($item->profit,2);
                        echo floor($item->profit); 
                    ?>
                </td>
				<td data-th = "Скрыть">
					<button type="button" class='clear_form_group btn btn-primary'> <i class="fa fa-eye-slash" aria-hidden="true"></i> </button>
				</td>
			</tr>
			
		<?php  }?>
		<tr data-value = "total" >
				<td><b>Итого</b></td>
				<td data-th = "Всего"><b><?php echo $c_all_common;?></b></td>
				<td data-th = "В работе"><b><?php echo $c_all_inwork;?></b></td>
				<td data-th = "Замеры"><b><?php echo $c_all_measure;?></b></td>
				<td data-th = "Договоры"><b><?php echo $c_all_deals;?></b></td>
                <td data-th = "Завершенные"><b><?php echo $c_all_done;?></b></td>
                <td data-th = "Сумма"><b><?php echo $c_all_sum;?></b></td>
                <td data-th = "Прибыль"><b><?php echo floor($c_all_profit); ?></b></td>
			</tr>
	</table>
</form>
<h2>Дневная аналитика</h2>
<form action="" method="post" name="adminForm" id="d_analiticForm">

	<div class = "analitic-actions">
		Выбрать с <input type = "date" id = "d_date1"> по <input type ="date" id = "d_date2"> <button type="button" class = "btn btn-primary" id = "d_show_all">Показать всё</button>
	</div>
	<table id = "d_analitic-table" class="rwd-table">
		<thead class = "caption-style-analitic">
            <tr>
                <th rowspan = "2">Реклама</th>
                <th data-value = "all" rowspan = "2">Всего</th>
                <th colspan = "3">
                    Замеры
                </th>
                <th colspan = "3">
                    Договоры
                </th>
				<th data-value = "mounts" rowspan="2">Монтажи</th>
                <th colspan = "2">
                    Закрытые
                </th>
				<th rowspan = "2">Скрыть</th>
            </tr>
            <tr>
                <th data-value = "(2)">Отказ</th>
                <th data-value = "(1)">Запись</th>
                <th data-value = "current">Текущие</th>
                <th data-value = "(3)">Отказ</th>
                <th data-value = "(4,5)">Договоры</th>
                <th data-value = "(4,5)">Сумма</th>
                <th data-value = "(12)">Кол-во</th>
                <th data-value = "(12)">Сумма</th>
            </tr>
		</thead> 
         
		<?php foreach ($d_items as $item) {?>
			<tr data-value = "<?php echo $item->name;?>">
				<td data-th = "Реклама">
					<?php echo $item->name;?>
				</td>
				<td data-th = "Всего">
					<?php 
						$d_all_common +=$item->common;
						echo $item->common;
					?>
				</td>
				<td data-th = "Замеры - Отказ">
					<?php 
						$d_all_ref_measure+=$item->ref_measure;
						echo $item->ref_measure;
					?>
				</td>
				<td data-th = "Замеры - Запись">
					<?php
						$d_all_measure+= $item->measure; 
						echo $item->measure;
					?>
				</td>
                <td data-th = "Замеры - Текущие">
					<?php
						$d_all_current_measure+= $item->current_measure; 
						echo $item->current_measure;
					?>
				</td>
				<td data-th = "Договоры - Отказ">
					<?php
						$d_all_ref_deals+= $item->ref_deals;
					 	echo $item->ref_deals;
					 ?>
				</td>
				<td data-th = "Договоры">
					<?php
						$d_all_deals+= $item->deals;
					 	echo $item->deals;
					 ?>
				</td>
				<td data-th = "Договоры - Сумма">
                    <?php
						$d_all_sum_deals+=$item->sum_deals;
						if(!empty($item->sum_deals)){
							echo $item->sum_deals; 
						}
						else echo 0;
                    ?>
                </td>
				<td data-th = "Монтажи">
					<?php 
						$d_all_mounts+=$item->mounts;
						echo $item->mounts;
					?>
				</td>
				<td data-th = "Закрытые">
					<?php 
						$d_all_closed+=$item->closed;
						echo $item->closed;
					?>
				</td>
                <td data-th = "Сумма закрытых">
                    <?php
						$d_all_sum_done+=$item->sum_done;
						if(!empty($item->sum_done)){
							echo $item->sum_done;
						}
						else echo 0;
                    ?>
                </td>
				<td data-th = "Скрыть">
					<button type="button" class='clear_form_group btn btn-primary'> <i class="fa fa-eye-slash" aria-hidden="true"></i> </button>
				</td>
			</tr>
			
		<?php  }?>
		<tr data-value = "total">
				<td><b>Итого</b></td>
				<td data-th = "Всего"><b><?php echo $d_all_common;?></b></td>
				<td data-th = "Замеры - Отказ"><b><?php echo $d_all_ref_measure;?></b></td>
				<td data-th = "Замеры - Запись"><b><?php echo $d_all_measure;?></b></td>
                <td data-th = "Замеры - Текущие"><b><?php echo $d_all_current_measure;?></b></td>
				<td data-th = "Договоры - Отказ"><b><?php echo $d_all_ref_deals;?></b></td>
				<td data-th = "Договоры"><b><?php echo $d_all_deals;?></b></td>
				<td data-th = "Договоры - Сумма"><b><?php echo $d_all_sum_deals;?></b></td>
				<td data-th = "Монтажи"><b><?php echo $d_all_mounts;?></b></td>
				<td data-th = "Закрытые"><b><?php echo $d_all_closed;?></b></td>
                <td data-th = "Сумма закрытых"><b><?php echo $d_all_sum_done;?></b></td>
			</tr>
	</table>
	<div id="modal-window-with-table" class="modal_window_analitic">
        <button type="button" id="close-modal-window" class="close_modal_analic"><i class="fa fa-times fa-times-tar" aria-hidden="true"></i></button>
        <div id="window-with-table" class ="window-with-table-analitic">
            <table id="table_projects" class = "table_project_analitic"></table>
        </div>
    </div>
</form>

<script>
	var dealer_id = '<?php echo $user->dealer_id?>';
	var columns_array = [];
	 jQuery(document).ready(function(){
	 	columns_array["name"] = "Реклама";
	 	columns_array["common"] = "Всего";
 		columns_array["inwork"] = "В работе";
 		columns_array["measure"] = "Замеры";
 		columns_array["deals"] = "Договоры";
 		columns_array["done"] = "Завершенные";
 		columns_array["sum"] = "Сумма";
 		columns_array["profit"] = "Прибыль";
		columns_array["ref_measure"] = "Замеры - Отказ";
		columns_array["current_measure"] = "Замеры - Текущие";
		columns_array["ref_deals"] = "Договоры - Отказ";
		columns_array["sum_deals"] = "Договоры - Сумма";
		columns_array["mounts"] = "Монтажи";
		columns_array["closed"] = "Закрытые";
		columns_array["sum_done"] = "Сумма закрытых";
        hideEmptyTr("#c_analitic-table");
		c_all_count = []; 
		c_all_count['name'] = "Итого:"; 
		c_all_count['common'] = <?php echo $c_all_common;?>;
		c_all_count['inwork'] = <?php echo $c_all_inwork;?>;
		c_all_count['measure'] = <?php echo $c_all_measure;?>;
        c_all_count['deals'] = <?php echo $c_all_deals;?>;
        c_all_count['done'] = <?php echo $c_all_done;?>;
        c_all_count['sum'] = <?php echo $c_all_sum;?>;
        c_all_count['profit'] = <?php echo $c_all_profit;?>;

        hideEmptyTr("#d_analitic-table");
		jQuery("#d_date1").val("<?php echo $today?>");
		jQuery("#d_date2").val("<?php echo $today?>");
		d_all_count = []; 
		d_all_count['name'] = "Итого:"; 
		d_all_count['common'] = <?php echo $d_all_common;?>;
		d_all_count['measure'] = <?php echo $d_all_measure;?>;
		d_all_count['ref_measure'] = <?php echo $d_all_ref_measure;?>;
        d_all_count['current_measure'] = <?php echo $d_all_current_measure;?>;
		d_all_count['deals'] = <?php echo $d_all_deals;?>;
		d_all_count['ref_deals'] = <?php echo $d_all_ref_deals;?>;
		d_all_count['closed'] = <?php echo $d_all_closed;?>;
		d_all_count['mounts'] = <?php echo $d_all_mounts;?>;
		d_all_count['sum_deals'] = <?php echo $d_all_sum_deals;?>;
		d_all_count['sum_done'] = <?php echo $d_all_sum_done;?>;
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
		if (browser == "Firefox") {
			jQuery("#c_date1").mask("99.99.9999");
			jQuery("#c_date2").mask("99.99.9999");
			jQuery("#c_date2").keypress(function(){
				var date1 = transform_date(jQuery("#c_date1").val());
				var date2 = transform_date(this.value);
				if(test_date(date2,date1))
					fill_table(date1,date2,dealer_id);
			})
		}
		else{
		jQuery("#c_date2").change(function(){
			
			var date1 = jQuery("#c_date1").val();
			if(test_date(this.value,date1))
				fill_table(date1,this.value,"#c_analitic-table",dealer_id);
		})
        jQuery("#c_date1").change(function(){
			
			var date2 = jQuery("#c_date2").val();
			if(test_date(this.value,date2))
				fill_table(this.value,date2,"#c_analitic-table",dealer_id);
        })
        jQuery("#d_date2").change(function(){
			
			var date1 = jQuery("#d_date1").val();
			if(test_date(this.value,date1))
				fill_table(date1,this.value,"#d_analitic-table",dealer_id);
		})
        jQuery("#d_date1").change(function(){
			
			var date2 = jQuery("#d_date2").val();
			if(test_date(this.value,date2))
				fill_table(this.value,date2,"#d_analitic-table",dealer_id);
		})
		}
	})
    function hideEmptyTr(table_name){
        jQuery(table_name+" tbody tr").each(function(){
            var tds = jQuery("td",this);
            var empty = true;
            for(var i = 1;i<tds.length-1;i++){
               
                if(tds[i].innerHTML.trim() != "0"){
                    empty = false;
                }
            }
            if(empty){
                this.style.display = "none";
            }
        });
    }
    jQuery(document).mouseup(function (e){ // событие клика по веб-документу
        var div = jQuery("#window-with-table"); // тут указываем ID элемента
        if (!div.is(e.target) // если клик был не по нашему блоку
            && div.has(e.target).length === 0) { // и не по его дочерним элементам
            jQuery("#window-with-table").hide();
            jQuery("#close-modal-window").hide();
            jQuery("#modal-window-with-table").hide();
        }
    });

    /* jQuery("#c_analitic-table tbody tr").click(function(event){
        var target = event.target;
        console.log('click');
        if (target.tagName == 'TD'){
            var rek_name = jQuery(target.closest("tr")).data('value');
            var index = jQuery(target)[0].cellIndex;
            var statuses = jQuery(jQuery('#c_analitic-table > thead > tr')[0].children[index]).data('value');
            var date1 = jQuery("#c_date1").val();
            var date2 = jQuery("#c_date2").val();
            getProjects(rek_name,statuses,date1,date2);
        }
    }) */
    jQuery(document).on("click", "#c_analitic-table tbody tr", function(event) {
        var target = event.target;
        if (target.tagName == 'TD'){
            var rek_name = jQuery(target.closest("tr")).data('value');
            var index = jQuery(target)[0].cellIndex;
            var statuses = jQuery(jQuery('#c_analitic-table > thead > tr')[0].children[index]).data('value');
            var date1 = jQuery("#c_date1").val();
            var date2 = jQuery("#c_date2").val();
            getProjects(rek_name,statuses,date1,date2,0);
        }         
    });
    jQuery(document).on("click", "#d_analitic-table tbody tr", function(event) {
		
        var target = event.target;
		console.log(target.tagName);
        if (target.tagName == 'TD' || target.tagName == 'B'){
			
            var rek_name = jQuery(target.closest("tr")).data('value');
            var index = jQuery(target)[0].cellIndex;
			if(target.tagName == 'B'){
				index = jQuery(target)[0].parentNode.cellIndex;
			}
            var strindex = 0;
			var diff = 0;
			console.log(rek_name,index);
			switch(true){
				case index>=2 && index<=7:
					strindex = 1;
					diff = 2;
					break;
				case index == 8:
					diff = 4;
					break;
				case index>=9 && index<=10:
					strindex = 1;
					diff = 3;
					break;
				default:
					diff = 0;

			}
            var statuses = jQuery(jQuery('#d_analitic-table > thead > tr')[strindex].children[index-diff]).data('value');
            var date1 = jQuery("#d_date1").val();
            var date2 = jQuery("#d_date2").val();
            console.log(rek_name,statuses,date1,date2,1);
            getProjects(rek_name,statuses,date1,date2,1);
        }               
    });
   /*  jQuery("#d_analitic-table tbody tr").click(function(event){
        var target = event.target;
        if (target.tagName == 'TD'){
            var rek_name = jQuery(target.closest("tr")).data('value');
            var index = jQuery(target)[0].cellIndex;
            var statuses = jQuery(jQuery('#d_analitic-table > thead > tr')[0].children[index]).data('value');
            var date1 = jQuery("#d_date1").val();
            var date2 = jQuery("#d_date2").val();
            getProjects(rek_name,statuses,date1,date2);
        }
    }) */
    function getProjects(rek_name,statuses,date1,date2,type){
        jQuery.ajax({
                    url: "index.php?option=com_gm_ceiling&task=getAnaliticProjects",
                    data: {
                        advt:rek_name,
                        statuses:statuses,
                        date1: date1,
                        date2: date2,
                        type : type,
                        dealer_id : dealer_id
                    },
                    dataType: "json",
                    async: true,
                    success: function (data) {
                        console.log(data);
                        var profit = 0,sum=0;
                        jQuery("#window-with-table").show('slow');
				        jQuery("#close-modal-window").show();
				        jQuery("#modal-window-with-table").show();
				        jQuery("#table_projects").empty();
				        if (data.length==0) {
				            TrOrders = '<tr id="caption-data"></tr><tr><td>Проектов нет</td></tr>';        
				            jQuery("#table_projects").append(TrOrders);
				        } else {
				            TrOrders = '<tr id="caption-tr"><td>Id</td><td>Адрес</td><td>Статус</td><td>Сумма</td><td>Прибыль</td></tr>';
				            for(var i = 0;i<data.length;i++){
                                profit = 0,sum = 0;
                                if(data[i].new_project_sum!=0){
                                    sum = data[i].new_project_sum;
                                    if(data[i].new_mount_sum!=0&&data[i].new_material_sum!=0){
                                        profit = data[i].new_project_sum -data[i].new_mount_sum-data[i].new_material_sum;
                                    }
                                }
                                else{
                                    if(data[i].project_sum){
                                        sum = data[i].project_sum;
                                        if(data[i].cost!=0){
                                            profit = data[i].project_sum - data[i].cost;
                                        }
                                        
                                    }
                                }

                                TrOrders += '<tr class="link_row" data-href = \'/index.php?option=com_gm_ceiling&view=clientcard&id='+data[i].client_id+'\'><td>'+data[i].id+'</td><td>'+data[i].project_info+'</td><td>'+data[i].status+'</td><<td>'+sum+'</td><td>'+profit.toFixed(2)+'</td>/tr>';

				            }

				            jQuery("#table_projects").append(TrOrders);
				        }
                        jQuery(".link_row").click(function(){
                            window.location = jQuery(this).data("href");
                        });
                    },
                    error: function (data) {
                    	console.log(data.responseText);
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

	jQuery("#c_show_all").click(function(){
		jQuery('#c_analitic-table > tbody > tr').show();
		jQuery('#c_analitic-table > tbody > tr:last').remove();
		jQuery('#c_analitic-table').append('<tr></tr>');
		for(var i in c_all_count){
			jQuery('#c_analitic-table > tbody > tr:last').append('<td data-th = "'+columns_array[i]+'"> <b>'+c_all_count[i]+'<b></td>');
		}
		jQuery('#c_analitic-table > tbody > tr:last').attr("data-value","total");
    })
    jQuery("#d_show_all").click(function(){
		jQuery('#d_analitic-table > tbody > tr').show();
		jQuery('#d_analitic-table > tbody > tr:last').remove();
		jQuery('#d_analitic-table').append('<tr></tr>');
		for(var i in d_all_count){
			jQuery('#d_analitic-table > tbody > tr:last').append('<td data-th = "'+columns_array[i]+'"> <b>'+d_all_count[i]+'<b></td>');
		}
		jQuery('#d_analitic-table > tbody > tr:last').attr("data-value","total");
	})
	jQuery(".clear_form_group").click(function (event) {
    
        jQuery(this).closest("tr").hide();
        table_name = "#"+jQuery(this).closest("tr")[0].parentNode.parentElement.id;
		var tr = jQuery(this).closest("tr");
		tr = tr[0];
		var arr = [];
		for(var i = 1;i<tr.children.length;i++){
			arr.push(+tr.children[i].childNodes[0].data);
		}
		update_total(arr,table_name);
    });

	function update_total(arr,table_name){
		var tr = jQuery(table_name+' > tbody > tr:last');
		tr = tr[0];
		for(var i = 1;i<tr.children.length;i++){
			tr.children[i].children[0].childNodes[0].data = tr.children[i].children[0].childNodes[0].data - arr[i-1];
		}
	}
	function fill_table(date1,date2,table_name,dealer_id){
        console.log(table_name);
        if(table_name == "#c_analitic-table"){
            url = "index.php?option=com_gm_ceiling&task=getAnaliticByPeriod";
        }
        else{
            url = "index.php?option=com_gm_ceiling&task=getDetailedAnaliticByPeriod";
        }
		if(date1<=date2){
			all_count = [];
			jQuery.ajax({
                    url: url,
                    data: {
                        date1: date1,
                        date2: date2,
                        dealer_id: dealer_id
                    },
                    dataType: "json",
                    async: true,
                    success: function (data) {
						console.log(data);
                        if(table_name == "#d_analitic-table"){
                            for(var i = 0;i<data.length;i++){
                                delete data[i].id;
                            }
                        }
						jQuery(table_name+' tbody').empty();
						for(var i=0;i<data.length;i++) {
							jQuery(table_name).append('<tr data-value="'+data[i]['name']+'"></tr>');
							for(var j=0;j<Object.keys(data[i]).length;j++){
								all_count['name'] = "Итого:";
								if(Object.keys(data[i])[j]!='name'){
									if(all_count[Object.keys(data[i])[j]]==undefined ){
										all_count[Object.keys(data[i])[j]] = 0;
									}
									all_count[Object.keys(data[i])[j]]+=data[i][Object.keys(data[i])[j]]-0;
								}
								jQuery(table_name+' > tbody > tr:last').append('<td data-th="'+columns_array[Object.keys(data[i])[j]]+'">'+data[i][Object.keys(data[i])[j]] +'</td>');
							}
							jQuery(table_name+' > tbody > tr:last').append("<td data-th = \"Скрыть\"><button class='clear_form_group btn btn-primary' type='button'><i class=\"fa fa-eye-slash\" aria-hidden=\"true\"></i></button></td> ");
						}
						if(table_name == "#c_analitic-table"){
                            c_all_count = all_count;
                        }
                        if(table_name == "#d_analitic-table"){
                            d_all_count = all_count;
                        }
						jQuery(table_name).append('<tr></tr>');
						for(var i in all_count){
							jQuery(table_name+' > tbody > tr:last').append('<td data-th = "'+columns_array[i]+'"> <b>'+all_count[i]+'<b></td>');
							jQuery(table_name+' > tbody > tr:last').attr("data-value", "total");
                        }
                        
						jQuery(".clear_form_group").click(function () {
							jQuery(this).closest("tr").hide();
							var tr = jQuery(this).closest("tr");
							tr = tr[0];
							var arr = [];
							for(var i = 1;i<tr.children.length;i++){
								arr.push(+tr.children[i].childNodes[0].data);
							}
							update_total(arr,table_name);
						});
						hideEmptyTr(table_name);
                        
                        
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
</script>
