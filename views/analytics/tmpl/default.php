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

$user = JFactory::getUser();
$analytic_model = Gm_ceilingHelpersGm_ceiling::getModel('analytic_new');
$data = json_encode($analytic_model->getData($user->dealer_id));
$det_acnalytic_model = Gm_ceilingHelpersGm_ceiling::getModel('Analytic_detailed_new');
$det_data = json_encode($det_acnalytic_model->getData($user->dealer_id));
$today = date('Y-m-d');
echo parent::getButtonBack();

?>
<div class="container">
    <div class="row right">
        <label for="c_date_from">Выбрать с:</label>
        <input type="date" name="c_date_from" id = "c_date_from" class="input-gm">
        <label for="c_date_to">до:</label>
        <input type="date" name="c_date_to" id = "c_date_to" class="input-gm">
        <button type="button" class = "btn btn-primary" id = "c_show_all">Показать всё</button>
    </div>
    <div class="row" style="margin-top: 10px">  
        <div class="col-md-12">
            <table id = "analytic_common" class="analitic-table">
                <thead id = "thead" class = "caption-style-tar">
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>        
    </div>

    <div class="row right">
        <label for="d_date_from">Выбрать с:</label>
        <input type="date" name="d_date_from" id = "d_date_from" class="input-gm" value="<?php echo $today?>">
        <label for="d_date_to">до:</label>
        <input type="date" name="d_date_to" id = "d_date_to" class="input-gm" value="<?php echo $today?>">
        <button type="button" class = "btn btn-primary" id = "d_show_all">Показать всё</button>
    </div>
    <div class="row" style="margin-top: 10px">  
        <div class="col-md-12">
            <table id = "analytic_detailed" class="analitic-table">
                <thead id = "thead_det" class = "caption-style-tar">
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>        
    </div>

</div>
<div id="mw_container" class="modal_window_analitic">
        <button type="button" id="close-modal-window" class="close_modal_analic"><i class="fa fa-times fa-times-tar" aria-hidden="true"></i></button>
        <div id="mw_projects" class ="window-with-table-analitic">
            <table id="table_projects" class = "table_project_analitic"></table>
        </div>
    </div>

<script type="text/javascript">
    var data = JSON.parse('<?php echo $data?>'),total = [];
    var ths = [];
    var det_ths =[];
    var det_data = JSON.parse('<?php echo $det_data?>');
    console.log(det_data);
    jQuery(document).mouseup(function (e){ // событие клика по веб-документу
        var div = jQuery("#mw_projects"); // тут указываем ID элемента
        if (!div.is(e.target) // если клик был не по нашему блоку
            && div.has(e.target).length === 0) { // и не по его дочерним элементам
            jQuery("#mw_projects").hide();
            jQuery("#close-modal-window").hide();
            jQuery("#mw_container").hide();
        }
    });
    jQuery(document).ready(function(){
        makeTh(jQuery("#analytic_common > thead"),data[0]);
        makeTh(jQuery("#analytic_detailed > thead"),det_data[0]);
        ths = jQuery("#analytic_common > thead  th");
        det_ths = jQuery("#analytic_detailed > thead  th");
        data.shift();
        det_data.shift();
        fill_table("#analytic_common",data,ths);
        fill_table("#analytic_detailed",det_data,det_ths);   
        hideEmptyTr("#analytic_common");
        //hideEmptyTr("#analytic_detailed");
        jQuery("#c_show_all").click(function(){
            jQuery('#analytic_common > tbody > tr').show();
            jQuery('#analytic_common > tbody > tr:last').remove();
            jQuery('#analytic_common').append('<tr></tr>');
            fill_total_string();
        });

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

        jQuery("#c_date_to").change(function(){
            var date1 = jQuery("#c_date_from").val(),
            date2 = jQuery("#c_date_to").val();
            getDataByPeriod(date1,date2);
        });

        jQuery("#c_date_from").change(function(){
            var date1 = jQuery("#c_date_from").val(),
            date2 = jQuery("#c_date_to").val();
            getDataByPeriod(date1,date2);
        });
    });
    jQuery(document).on("click", "#analytic_common tbody tr", function(event) {
        var target = event.target;
        projects = "";
        if (target.tagName == 'TD' || target.tagName == 'B'){
            target = (target.tagName == 'B') ?  jQuery(target.closest("td")) : target;
            var rek_name = jQuery(target.closest("tr")).data('value');
            var index = jQuery(target)[0].cellIndex;
            var statuses = jQuery(jQuery('#analytic_common > thead > tr')[0].children[index]).data('value');
            var date1 = jQuery("#c_date_from").val();
            var date2 = jQuery("#c_date_to").val();
            if(rek_name != undefined){
                for(let i = 0;i<data.length;i++){
                    if(data[i]['id'] == rek_name){
                        projects = data[i]['projects'][statuses];
                    }
                 }
            }
            else{
                for(let i = 0;i<data.length;i++){
                    if(data[i]['projects'][statuses])
                        projects += data[i]['projects'][statuses];
                }   
            }
            getProjects(projects,date1,date2,0);
        }         
    });

    function makeTh(container, data) {
        var row = jQuery("<tr/>"),row1 = jQuery("<tr/>");

        container.empty();
        jQuery.each(data, function(key, value) {
            if(typeof value == 'string'){ 
                row.append(jQuery("<th/ data-value = '"+key+"'>").text(value));
                row1 = "";        
            }
            if(typeof value == 'object'){
                if(!value.columns){
                    row.append(jQuery("<th/ rowspan ="+value.rowspan+" data-value = '"+key+"'>").text(value.head_name));    
                }
                else{
                    
                    row.append(jQuery("<th/ colspan ="+Object.keys(value.columns).length+" data-value = '"+key+"'>").text(value.head_name));
                    jQuery.each(value.columns, function(key_c, value_c) {
                        
                        row1.append(jQuery("<th/ data-value = '"+key_c+"'>").text(value_c));
                    });
                }
               
            }
        });
       container.append(row);

       if(row1){
            container.append(row1);
       }
    }
    
    function getDataByPeriod(date1,date2){
        jQuery.ajax({
            url: "index.php?option=com_gm_ceiling&task=getAnaliticByPeriod",
            data: {
                date1: date1,
                date2: date2,
                type : 0
            },
            dataType: "json",
            async: true,
            success: function (result) {
                total = [];
                result.shift();
                data = result;
                fill_table("#analytic_common",result,ths);
                hideEmptyTr("#analytic_common");
                console.log(result);
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

    function fill_table(container,data,ths){
        var key ="";
        jQuery(container + ' tbody').empty();
        jQuery(container + '> thead > tr:last').append("<th/>");
        for(let i = 0;i<data.length;i++){
            jQuery(container).append('<tr></tr>');
            jQuery.each(ths,function(index,item){
                if(item.colSpan == 1){
                    key = jQuery(item).data('value');
                    let val = (data[i][key] ? data[i][key] : 0);
                     jQuery(container +' > tbody > tr:last').attr('data-value',data[i]['id'] ? data[i]['id'] : 0); 
                    jQuery(container + ' > tbody > tr:last').append('<td>'+ val +'</td>');
                    if(key == 'advt_title'){
                        total[key] = '<b>Итого</b>';
                    }
                    else{
                        total[key] = (total[key]) ? total[key] + val : val;
                    }
                }
            });

            jQuery(container + ' > tbody > tr:last').append("<td><button class='clear_form_group btn btn-primary' type='button'><i class=\"fa fa-eye-slash\" aria-hidden=\"true\"></i></button></td> ");
        }

        fill_total_string();
    }

    function fill_total_string(){
        if(Object.keys(total).length){
            jQuery('#analytic_common').append('<tr></tr>');
                jQuery.each(ths,function(index,item){
                    key = jQuery(item).data('value');
                    jQuery('#analytic_common > tbody > tr:last').append('<td><b>'+  total[key] +'</b></td>');
                });
            }
    }


    function update_total(arr,table_name){
        var tr = jQuery(table_name+' > tbody > tr:last');
        tr = tr[0];
        for(var i = 1;i<tr.children.length;i++){
            tr.children[i].children[0].childNodes[0].data = tr.children[i].children[0].childNodes[0].data - arr[i-1];
        }
    }

    function getProjects(ids,date1,date2,type){
        jQuery.ajax({
                    url: "index.php?option=com_gm_ceiling&task=getAnaliticProjects",
                    data: {
                        ids : ids,
                        date1: date1,
                        date2: date2,
                        type : type
                    },
                    dataType: "json",
                    async: true,
                    success: function (data) {
                        console.log(data);
                        var profit = 0,sum=0;
                        jQuery("#mw_projects").show('slow');
                        jQuery("#close-modal-window").show();
                        jQuery("#mw_container").show();
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
    
</script>