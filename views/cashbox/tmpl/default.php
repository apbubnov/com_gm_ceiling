<?php
/**
 * @version    CVS: 0.1.7
 * @package    Com_Gm_ceiling
 * @author     bubnov <2017 al.p.bubnov@gmail.com>
 * @copyright  2017 al.p.bubnov@gmail.com
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;

$user       = JFactory::getUser();
$userId     = $user->get('id');
$month = date("n");
$year = date("Y");
?>
<form>
    <div style="display:block; right:0px">
        <a class="btn btn-large btn-primary"
        href="/index.php?option=com_gm_ceiling&view=mainpage&type=gmmanagermainpage"
        id="back"><i class="fa fa-arrow-left" aria-hidden="true"></i> Назад</a>
        <button type = "button" id = "add" class = "btn btn-primary">Инкассация</button>
        <button type= "button" class = "btn btn-primary" id="prev"><i class="fa fa-arrow-left" aria-hidden="true"></i></button>
        <select id = "month">
            <option value = "1">Январь</option>
            <option value = "2">Февраль</option>
            <option value = "3">Март</option>
            <option value = "4">Апрель</option>
            <option value = "5">Май</option>
            <option value = "6">Июнь</option>
            <option value = "7">Июль</option>
            <option value = "8">Август</option>
            <option value = "9">Сентябрь</option>
            <option value = "10">Октябрь</option>
            <option value = "11">Ноябрь</option>
            <option value = "12">Декабрь</option>
        </select><label id = "year"><?php echo $year?></label>
        <button  type= "button"\ class = "btn btn-primary" id = "next"><i class="fa fa-arrow-right" aria-hidden="true"></i></button>
    </div>
    <div id="modal_window_container" class = "modal_window_container">
		<button type="button" id="close" class = "close_btn"><i class="fa fa-times fa-times-tar" aria-hidden="true"></i></button>
		<div id="modal_window_sum" class = "modal_window">
			<h6 style = "margin-top:10px">Введите сумму</h6>
			<p><input type="text" id="sum" placeholder="Сумма" required></p>
			<p><button type="button" id="save" class="btn btn-primary">Сохранить</button>  <button type="button" id="cancel" class="btn btn-primary">Отмена</button></p>
	    </div>
    </div>
    <table class="table table-striped table_cashbox" id="cashbox_table">
        <thead id = "cashbox_head">
        <tr>
            <th>
              Дата
            </th>
            <th>
               № дог-ра
            </th>
            <th>
                Статус
            </th>
            <th>
               Бригада
            </th>
            <th>
                Сумма дог-ра
            </th>
            <th>
                З\п монт-м
            </th>
            <th>
                Не выдано монт-м
            </th>
            <th>
                Расходные мат-лы
            </th>
            <th>
                Остаток
            </th>
            <th>
                Касса
            </th>
            <th>
                Инкассация
            </th>
        </tr>
        </thead>

        <tbody id="cashbox_body">
            <?php foreach ($this->item as $item) {
               ?>
                <tr>
                    <td>
                        <?php
                            if(isset($item->id)){
                                echo date_format(date_create($item->closed),"d.m.Y");
                            }
                            else{
                                echo date_format(date_create($item->closed),"d.m.Y H:i:s");
                            }
                        ?>
                    </td>
                    <td>
                        <?php echo $item->id;?>        
                    </td>
                    <td>
                        <?php echo $item->status ?>
                    </td>
                    <td width = 15%>
                        <?php echo $item->name;?>
                    
                    </td>
                    <td>
                        <?php echo $item->new_project_sum;?>        
                    </td>
                    <td>
                        <?php echo $item->new_mount_sum;?>        
                    </td>
                    <td>
                        <?php
        
                            echo $item->not_issued;
                        ?>
                    </td>
                    <td>
                        <?php echo $item->new_material_sum;?>        
                    </td>
                    <td>
                        <?php
                            if(!isset($item->sum)){
                                $residue = $item->new_project_sum - $item->new_mount_sum -$item->new_material_sum;
                                echo $residue;
                            }
                        ?>        
                    </td>
                    <td>
                        <?php
                            $cashbox += $residue - $encash;
                            $encash = 0;
                            echo $cashbox;
                        ?>
                    </td>
                    <td>
                        <?php 
                            if(isset($item->sum)) {
                                $encash = $item->sum;
                                echo $item->sum; 
                            }
                             ?>
                    </td>
                </tr>
            <?php }?>
            <tr>
                <td style = "text-align:right" colspan = "10">
                    <b>Итого в кассе:</b>
                </td>
                <td>
                    <b><?php echo $cashbox;?></b>
                </td>
            </tr>
            <tr>
                <td style = "text-align:right" colspan = "10">
                    <b>Недовыдано</b>
                </td>
                <td>
                    <b><?php echo $all_not_issued ?></b>
                </td>
            </tr>
            <tr>
                <td style = "text-align:right" colspan = "10">
                   <b>Остаток</b>
                </td>
                <td>
                    <b><?php echo $cashbox-$all_not_issued;?></b>
                </td>
            </tr>
        </tbody>
    </table>
</form>

<script>
    jQuery(document).mouseup(function (e){ // событие клика по веб-документу
        var div = jQuery("#modal_window_sum"); // тут указываем ID элемента
        if (!div.is(e.target) // если клик был не по нашему блоку
            && div.has(e.target).length === 0) { // и не по его дочерним элементам
            jQuery("#close").hide();
            jQuery("#modal_window_container").hide();
            jQuery("#modal_window_sum").hide();
        }
    });
    jQuery(document).ready(function (){
        jQuery("#cancel").click(function(){
            jQuery("#close").hide();
            jQuery("#modal_window_container").hide();
            jQuery("#modal_window_sum").hide();
        });
        jQuery("#add").click(function (){
            jQuery("#modal_window_container").show();
            jQuery("#modal_window_sum").show("slow");
            jQuery("#close").show();
        });
        jQuery("#save").click(function(){
            id = <?php echo $userId;?>;
            jQuery.ajax({
                type: 'POST',
                url: "index.php?option=com_gm_ceiling&task=saveEncashment",
                async: false,
                data: {
                    sum: jQuery("#sum").val(),
                    id: id
                },
                success: function (data) {
                    window.location = window.location;
                },
                dataType: "text",
                timeout: 10000,
                error: function () {
                    var n = noty({
                        timeout: 2000,
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "error",
                        text: "Ошибка cервер не отвечает"
                    });
                }
            });
        });
        
        month_old = 0;
        year_old = 0;
        jQuery("#next").click(function () {
            month = <?php echo $month; ?>;
            year = <?php echo $year; ?>;
            if (month_old != 0) {
                month = month_old;
                year = year_old;
            }
            if (month == 12) {
                month = 1;
                year++;
            } else {
                month++;
            }
            month_old = month;
            year_old = year;
            update_month_year(month,year);  
            console.log(month,year);          
        });
        jQuery("#prev").click(function () {
            month = <?php echo $month; ?>;
            year = <?php echo $year; ?>;
            if (month_old != 0) {
                month = month_old;
                year = year_old;
            }
            if (month == 1) {
                month = 12;
                year--;
            } else {
                month--;
            }
            month_old = month;
            year_old = year;
            update_month_year(month,year);
        });
        jQuery("#month").change(function() {
            var month = this.value;
            var year = jQuery("#year").text();
            jQuery.ajax({
                type: 'POST',
                url: "index.php?option=com_gm_ceiling&task=getCashboxByMonth",
                async: false,
                data: {
                    month: month,
                    year: year
                },
                success: function (data) {
                   fill_table(JSON.parse(data));
                },
                dataType: "text",
                timeout: 10000,
                error: function () {
                    var n = noty({
                        timeout: 2000,
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "error",
                        text: "Ошибка cервер не отвечает"
                    });
                }
            });

        });
        
    });
    function update_month_year(month,year){
        jQuery("#month option").each(function()
        {
            if(jQuery(this).val()==month){
                jQuery(this).attr("selected",true);
            }
        });
        jQuery("#year").text(year); 
    }
    function fill_table(data){
        jQuery('#cashbox_table tbody').empty();
        for(var i=0;i<data.length;i++) {
            jQuery("#cashbox_table").append('<tr></tr>');
            for(var j=0;j<Object.keys(data[i]).length;j++){ 
                if(Object.keys(data[i])[j]!="done"&&Object.keys(data[i])[j]!="project_status"&&Object.keys(data[i])[j]!="new_project_mounting")
                jQuery('#cashbox_table > tbody > tr:last').append('<td>'+data[i][Object.keys(data[i])[j]] +'</td>');
                
            }
        }
    }
</script>