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
$month_begin_date = date('Y-m-01');
$today = date('Y-m-d');
$jinput = JFactory::getApplication()->input;
$new_analytic = $jinput->get('new',0,'INT');
?>
<style>
    .container{
        width:auto !important;
        margin-left:0px !important;
        margin-right: 0px !important;
    }
</style>
<div class="container center">
    <div class="row" >
        <h4>Данные по запущенным проектам</h4>
        <div class="col-md-6" align="left">
            <input type="radio" name="selection_type" value="5" class="radio" id ="run" checked="true"><label for="run">Выборка по запущенным в производство</label><br>
            <input type="radio" name="selection_type" value="10" class="radio" id ="in_work"><label for="in_work">Выборка по запущенным в цех</label>
        </div>
        <div class="col-md-3">
            <button class="btn btn-primary" id="go_to_old">Просмотреть cтарую аналитику</button>
        </div>
        <div class="col-md-3" align="right">
            <div>
                <label for="date_from">Выбрать с: </label>
                <input type="date" name="date_from" id = "date_from" class="input-gm" value="<?php echo $date_from;?>">
            </div>
            <div>
                <label for ="date_to">по:</label>
                <input type="date" name="date_to" id = "date_to" class="input-gm" value="<?php echo $date_to;?>">
            </div>
        </div>
    </div>
    <div class="row" align="right">
        <div class="col-md-12">
            <label style="color: #414099;font-size: 14pt">Общее количество дилеров: <b><span id="dealers_count">0</span></b></label>
        </div>
    </div>
    <div class="row center" style="margin-bottom: 15px;">
        <table id = "analytic" class="analitic-table">
            <thead id = "thead" class = "caption-style-tar">
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
    <hr style="border: none; color: #414099; background-color: #414099; height: 2px;">
    <div class="row">
        <h4>Данные по выданным проектам</h4>
        <div class="col-md-6"></div>
        <div class="col-md-6" align="right">
            <div>
                <label for="date_from">Выбрать с: </label>
                <input type="date" name="issued_date_from" id = "issued_date_from" class="input-gm" value="<?php echo $date_from;?>">
            </div>
            <div>
                <label for ="date_to">по:</label>
                <input type="date" name="issued_date_to" id = "issued_date_to" class="input-gm" value="<?php echo $date_to;?>">
            </div>
        </div>

        <table id="table_issued" class="analitic-table">
            <thead class = "caption-style-tar">
            <th>
                Дилер
            </th>
            <th>
                Стоимость
            </th>
            <th>
                Себетоимость
            </th>
            <th>
                Разница
            </th>
            <th>
                Стоимость полотен
            </th>
            <th>
                Себестоимость полотен
            </th>
            <th>
                Стоимость компонентов
            </th>
            <th>
                Себестоимость компонентов
            </th>
            </thead>
            <tbody>

            </tbody>
        </table>
    </div>
    <hr style="border: none; color: #414099; background-color: #414099; height: 2px;">
    <div class="row center">
        <div class="col-md-6" style="border: 1px #414099 solid;border-radius: 15px;">
            <div class="row">
                <input type="radio" name="interval_radio" id="month" class="radio"  value ="0"><label for="month">Выборка по месяцам</label>
            </div>
            <div class="row"  id = "months_div" style="display: none;">
                <div class="col-md-12 right">
                    <label for="year1">
                        Выбрать с
                    </label>
                    <select class="input-gm" id = "month1">
                        <option value="00">месяц</option>
                        <option value="01">Январь</option>
                        <option value="02">Февраль</option>
                        <option value="03">Март</option>
                        <option value="04">Апрель</option>
                        <option value="05">Май</option>
                        <option value="06">Июнь</option>
                        <option value="07">Июль</option>
                        <option value="08">Август</option>
                        <option value="09">Сентябрь</option>
                        <option value="10">Октябрь</option>
                        <option value="11">Ноябрь</option>
                        <option value="12">Декабрь</option>
                    </select>
                    <input type = "text" class="input-gm" style="width: 60px;" id = "year1" value="<?php echo date(Y);?>"> г.
                </div>

                <div class="col-md-12 right">
                    <label for="year2">
                        по
                    </label>
                    <select class="input-gm"  id = "month2">
                        <option value="00">месяц</option>
                        <option value="01">Январь</option>
                        <option value="02">Февраль</option>
                        <option value="03">Март</option>
                        <option value="04">Апрель</option>
                        <option value="05">Май</option>
                        <option value="06">Июнь</option>
                        <option value="07">Июль</option>
                        <option value="08">Август</option>
                        <option value="09">Сентябрь</option>
                        <option value="10">Октябрь</option>
                        <option value="11">Ноябрь</option>
                        <option value="12">Декабрь</option>
                    </select>
                    <input type = "text" class="input-gm" style="width: 60px;" id = "year2" value="<?php echo date(Y);?>"> г.
                </div>
                <div class="col-md-12" style="margin-bottom: 15px;">
                    <button class="btn btn-primary show" type="button"> Показать</button>
                </div>
            </div>
        </div>
        <div class="col-md-6" style="border: 1px #414099 solid;border-radius: 15px;">
            <div class="row">
                <input type="radio" name="interval_radio" id = "dates" class="radio" checked="true" value ="1"><label for="dates">Выборка по интревалу</label>
            </div>
            <div class="row" id = "dates_div" >
                <div class="col-md-12 right">
                    <label for="date1">
                        Выбрать с
                    </label>
                    <input type = "date" class="input-gm" id = "date1" value="<?php echo $month_begin_date?>">
                </div>

                <div class="col-md-12 right">
                    <label for="date2">
                        по
                    </label>
                    <input type ="date" class="input-gm" id = "date2" value="<?php echo $today?>">
                </div>
                <div class="col-md-12" style="margin-bottom: 15px">
                    <button class="btn btn-primary show" type="button"> Показать</button>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div id="chart_div"></div>
    </div>
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
</div>
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script type="text/javascript">
    var data = JSON.parse('<?php echo $data;?>');
    var month_translate = [];
    month_translate["January"] = ['Январь',0];
    month_translate["February"] =['Февраль',1];
    month_translate["March"] = ['Март',2];
    month_translate["April"] = ['Апрель',3];
    month_translate["May"] = ['Май',4];
    month_translate["June"] = ['Июнь',5];
    month_translate["July"] = ['Июль',6];
    month_translate["August"] = ['Август',7];
    month_translate["September"] = ['Сентябрь',8];
    month_translate["October"] = ['Октябрь',9];
    month_translate["November"] = ['Ноябрь',10];
    month_translate["December"] = ['Декабрь',11];

    function getDataChart(){
        var select_type = jQuery('input[name="interval_radio"]:checked').val(),
            date1,date2;

        if(select_type == 1){
            date1 = jQuery("#date1").val();
            date2 = jQuery("#date2").val();
        }
        else
        {
            date1 = jQuery("#year1").val()+'-'+jQuery("#month1").val()+'-01';
            date2 = jQuery("#year2").val()+'-'+jQuery("#month2").val()+'-'+ new Date(jQuery("#year2").val(), jQuery("#month2").val(), 0).getDate();
        }
        if(date1<=date2){
            jQuery.ajax({
                type: 'POST',
                url: "index.php?option=com_gm_ceiling&task=calculations.getQuadrature",
                data: {
                    date1:date1,
                    date2:date2,
                    type:select_type
                },
                success: function(data){
                    console.log(data);
                    if(select_type == 1){
                        jQuery.each(data,function(index,elem){
                            data[index][1]=parseFloat(elem[1]);
                        });
                        if(data.length)
                            drawGraphic(data);
                    }
                    else{
                        var new_data = [];
                        jQuery.each(data,function(index,elem){
                            var ym = elem[0].split(' ',2);
                            data[index][2] = month_translate[ym[0]][0]+' '+ym[1];
                            var new_index = month_translate[ym[0]][1];
                            if(new_data[new_index]){
                                if(parseFloat(elem[1])){
                                    new_data[new_index][1]+=parseFloat(elem[1]);
                                }
                            }
                            else{
                                new_data[new_index] = [];
                                if(parseFloat(elem[1])){
                                    new_data[new_index][1] = parseFloat(elem[1]);
                                }
                                else{
                                    new_data[new_index][1] = 0;
                                }
                                new_data[new_index][0] = elem[2];
                            }

                        });
                        data = [];
                        for (var i = 0; i < new_data.length; i++) {
                            data.push(new_data[i]);
                        }
                        console.log(data);
                        if(data.length)
                            drawGraphic(data);
                    }

                },
                dataType: "json",
                async: false,
                timeout: 10000,
                error: function(data){
                    var n = noty({
                        timeout: 2000,
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "error",
                        text: "Ошибка. Сервер не отвечает"
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
                text: "Начальная дата не может быть больше конечной!"
            });
        }
    }

    function drawGraphic(quadr_data){
        google.charts.load('current', {'packages':['line']});
        google.charts.setOnLoadCallback(drawChart);

        function drawChart() {
            var data = new google.visualization.DataTable();
            data.addColumn('string', 'Даты');
            data.addColumn('number', 'Квадратура');
            data.addRows(quadr_data);
            var options = {
                chart: {
                    title: 'Квадратура за выбранный период времени',
                    subtitle: 'в квадратных метрах'
                },
                width: window.width,
                height: 500
            };
            var chart = new google.charts.Line(document.getElementById('chart_div'));
            chart.draw(data, google.charts.Line.convertOptions(options));
        }
    }
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
        jQuery("#year1").mask("9999");
        jQuery("#year2").mask("9999");
        getIssuedData(jQuery("#issued_date_from").val(),jQuery("#issued_date_to").val());
        getDataChart();
        getData(jQuery('#date_from').val(),jQuery("#date_to").val());
        jQuery('input[name="interval_radio"]').click(function(){
            if(this.id == "dates"){
                console.log("in dates");
                jQuery("#dates_div").show();
                jQuery("#months_div").hide();
            }
            if(this.id == "month"){
                console.log("in_months");
                jQuery("#dates_div").hide();
                jQuery("#months_div").show();
            }
        });

        jQuery("#go_to_old").click(function(){
            location.href='/index.php?option=com_gm_ceiling&view=analytic_dealers';
        });

        jQuery('.show').click(function(){
            getDataChart();
        });

        jQuery('[name = "selection_type"]').change(function(){
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

    jQuery("#issued_date_from").change(function(){
        var date_from = jQuery('#issued_date_from').val(),
            date_to = jQuery("#issued_date_to").val()

        if(date_from <= date_to){
            getIssuedData(date_from,date_to);
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

    jQuery("#issued_date_to").change(function(){
        var date_from = jQuery('#issued_date_from').val(),
            date_to = jQuery("#issued_date_to").val()

        if(date_from <= date_to){
            getIssuedData(date_from,date_to);
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
        jQuery.ajax({
            url: "index.php?option=com_gm_ceiling&task=getDealersAnalyticData",
            data: {
                date_from:date_from,
                date_to:date_to,
                status:jQuery('[name = "selection_type"]:checked').val(),
                new:1
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

    function getIssuedData(date_from,date_to){
        jQuery.ajax({
            url: "index.php?option=com_gm_ceiling&task=analytic.getIssuedData",
            data: {
                date_from:date_from,
                date_to:date_to
            },
            dataType: "json",
            async: true,
            success: function (data) {
                console.log(data);
                if(!empty(data)){
                    jQuery("#table_issued > tbody").empty();
                    var total_arr = {name:'Итого',sum:0,cost_sum:0,diff:0,canv_sum:0,canv_cost:0,comp_sum:0,comp_cost:0};
                    jQuery.each(data,function(index,elem){
                        total_arr.sum += +elem.sum;
                        total_arr.cost_sum += +elem.cost_sum;
                        total_arr.diff += (elem.sum-elem.cost_sum);
                        total_arr.canv_sum += +elem.canvases_sum;
                        total_arr.canv_cost += +elem.canvases_cost_sum;
                        total_arr.comp_sum += +elem.components_sum;
                        total_arr.comp_cost += +elem.components_cost_sum;

                        jQuery("#table_issued > tbody").append('<tr data-dealer_id = "'+elem.id+'"></tr>');
                        jQuery("#table_issued > tbody > tr:last").append(   '<td>'+elem.name+'</td>'+
                            '<td>'+parseFloat(elem.sum).toFixed(2)+'</td>'+
                            '<td>'+parseFloat(elem.cost_sum).toFixed(2)+'</td>'+
                            '<td>'+((elem.sum-elem.cost_sum)).toFixed(2)+'</td>'+
                            '<td>'+parseFloat(elem.canvases_sum).toFixed(2)+'</td>'+
                            '<td>'+parseFloat(elem.canvases_cost_sum).toFixed(2)+'</td>'+
                            '<td>'+parseFloat(elem.components_sum).toFixed(2)+'</td>'+
                            '<td>'+parseFloat(elem.components_cost_sum).toFixed(2)+'</td>'
                        );

                    });
                    jQuery("#table_issued > tbody").append('<tr>' +
                        '<td><b>'+total_arr.name+'</b></td>'+
                        '<td><b>'+total_arr.sum.toFixed(2)+'</b></td>'+
                        '<td><b>'+total_arr.cost_sum.toFixed(2)+'</b></td>'+
                        '<td><b>'+total_arr.diff.toFixed(2)+'</b></td>'+
                        '<td><b>'+total_arr.canv_sum.toFixed(2)+'</b></td>'+
                        '<td><b>'+total_arr.canv_cost.toFixed(2)+'</b></td>'+
                        '<td><b>'+total_arr.comp_sum.toFixed(2)+'</b></td>'+
                        '<td><b>'+total_arr.comp_cost.toFixed(2)+'</b></td>'+
                        '</tr>');
                }
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
        jQuery("#dealers_count").text(data.length);
        var ths = jQuery("#analytic > thead  th"),key ="",total = [],total_manf = {};
        jQuery('#analytic tbody').empty();
        for(let i = 0;i<data.length;i++){
            jQuery('#analytic').append('<tr data-dealer_id = "'+data[i].dealer_id+'"></tr>');
            jQuery.each(ths,function(index,item){
                key = jQuery(item).data('value');
                let val = (key!='name' && key!='squares_manf' && key!='project_count' && key!='calcs_count') ? parseFloat(data[i][key]).toFixed(2) : data[i][key];

                if (key == 'squares_manf' || key == 'name') {
                    jQuery('#analytic > tbody > tr:last').append('<td style="min-width: 200px; text-align: left;">'+ val +'</td>');
                } else {
                    jQuery('#analytic > tbody > tr:last').append('<td style="text-align: right;">'+ val +'</td>');
                }

                if(key == 'rest') {
                    console.log('rest');
                    total[key] = '-';
                    console.log(total[key]);
                } else if (key == 'name') {
                    total[key] = '<b>Итого</b>';
                } else if (key == 'squares_manf') {
                    if (!empty(val)) {
                        var temp = val.split('<br>'), k;
                        for (var j = temp.length; j--;) {
                            temp[j] = temp[j].split(': ');
                            k = temp[j][0];
                            if (total_manf[k] === undefined) {
                                total_manf[k] = temp[j][1]-0;
                            } else {
                                total_manf[k] += temp[j][1]-0;
                            }
                        }
                    }

                } else {
                    total[key] = (total[key]) ? +total[key] + +val : val;
                }
            });

        }

        for (var k in total_manf) {
            if(empty(total['squares_manf'])){
                total['squares_manf'] = k+': '+total_manf[k].toFixed(2)+'<br>';
            }
            else{
                total['squares_manf'] += k+': '+total_manf[k].toFixed(2)+'<br>';
            }
        }

        if(Object.keys(total).length){
            console.log('total',total);
            jQuery('#analytic').append('<tr></tr>');
            jQuery.each(ths,function(index,item){
                key = jQuery(item).data('value');

                var value = (key!='name' && key!='squares_manf' && key!='project_count' && key!='calcs_count' && key!= "rest") ? parseFloat(total[key]).toFixed(2) : total[key];
                //console.log(value);

                if (key == 'squares_manf' || key == 'name') {
                    jQuery('#analytic > tbody > tr:last').append('<td style="min-width: 200px; text-align: left;"><b>'+ value +'</b></td>');
                } else {
                    jQuery('#analytic > tbody > tr:last').append('<td style="text-align: right;"><b>'+ value +'</b></td>');
                }

            });
        }

        jQuery("#analytic tr").click(function(){
            var dealer_id = jQuery(this).data('dealer_id'), projects = '';

            data.forEach(function(elem){
                if(elem.dealer_id == dealer_id){
                    projects = elem.projects;
                }

            });
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
                    jQuery("#detailed_analytic > tbody > tr").click(function(){
                        document.location.href = "/index.php?option=com_gm_ceiling&view=project&type=gmcalculator&subtype=calendar&id="+jQuery(this).data('id');
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
                        text: "Произошла ошибка, попробуйте позднее!"
                    });
                }
            });
            console.log(projects);
        });

        function create_detailed_table(data){
            jQuery('#detailed_analytic tbody').empty();
            for(let i = 0;i<data.length;i++){
                jQuery('#detailed_analytic').append('<tr data-id = '+data[i].id+'></tr>');
                jQuery('#detailed_analytic > tbody > tr:last').append('<td>'+ data[i].id+'</td>');
                jQuery('#detailed_analytic > tbody > tr:last').append('<td>'+ data[i].project_info+'</td>');
                jQuery('#detailed_analytic > tbody > tr:last').append('<td>'+ data[i].quadr+'</td>');
            }
        }
    }


</script>