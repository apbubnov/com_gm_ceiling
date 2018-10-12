<?php
/**
 * @version    CVS: 0.1.7
 * @package    Com_Gm_ceiling
 * @author     bubnov <2018 al.p.bubnov@gmail.com>
 * @copyright  2018 al.p.bubnov@gmail.com
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
    $model = Gm_ceilingHelpersGm_ceiling::getModel('Analytic_Dealers');
    $month_begin_date = date('Y-m-01');
    $today = date('Y-m-d');
    //$data = json_encode($model->calculateQuadratureByPeriod($month_begin_date,$today));
?>
<div class="container">
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
</div>
   
<div id="chart_div"></div>
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script type="text/javascript">
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

    console.log(month_translate);
    function getData(){

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
        if(date1<date2){
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
    jQuery(document).ready(function(){
        jQuery("#year1").mask("9999");
        jQuery("#year2").mask("9999");

        getData();

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

        jQuery('.show').click(function(){
            getData();
        });
    });
    
</script>