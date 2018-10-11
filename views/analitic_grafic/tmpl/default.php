<?php
/**
 * @version    CVS: 0.1.7
 * @package    Com_Gm_ceiling
 * @author     bubnov <2018 al.p.bubnov@gmail.com>
 * @copyright  2018 al.p.bubnov@gmail.com
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
    $model = Gm_ceilingHelpersGm_ceiling::getModel('Analytic_Dealers');
    $data = json_encode($model->calculateQuadratureByPeriod('2018-01-01',date('Y-m-d')));
?>
Выбрать с <input type = "date" class="input-gm" id = "date1"> по <input type ="date" class="input-gm" id = "date2">
<div id="chart_div"></div>
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script type="text/javascript">
    jQuery(document).ready(function(){
        var quadr_data = JSON.parse('<?php echo $data?>');
        jQuery.each(quadr_data,function(index,elem){
            //quadr_data[index][0]=new Date(elem[0]);
            quadr_data[index][1]=parseFloat(elem[1]);
        })
        console.log(quadr_data);
        google.charts.load('current', {'packages':['line']});
      google.charts.setOnLoadCallback(drawChart);

    function drawChart() {

      var data = new google.visualization.DataTable();
      data.addColumn('string', 'Day');
        data.addColumn('number', 'Quadr');

      data.addRows(quadr_data);

      var options = {
        chart: {
          title: 'Box Office Earnings in First Two Weeks of Opening',
          subtitle: 'in millions of dollars (USD)'
        },
        width: 900,
        height: 500
      };

      var chart = new google.charts.Line(document.getElementById('chart_div'));

      chart.draw(data, google.charts.Line.convertOptions(options));
    }
    });
    
</script>