<?php
/**
 * @version    CVS: 0.1.7
 * @package    Com_Gm_ceiling
 * @author     bubnov <2017 al.p.bubnov@gmail.com>
 * @copyright  2017 al.p.bubnov@gmail.com
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
    $api_model = Gm_ceilingHelpersGm_ceiling::getModel('api_phones');
    $result = $api_model->getData();
?>
Выбрать с <input type = "date" id = "date1"> по <input type ="date" id = "date2">
<div>
Реклама:<br>
<?php
    foreach ($result as $key => $value)
    {
?>
        <input type="checkbox" id="<?php echo 'cb'.$result[$key]->id; ?>" value="<?php echo $result[$key]->name; ?>"><?php echo $result[$key]->name; ?><br>
<?php
    }
?>
</div>

<div>
    <input type="checkbox" id="cb_metrics" value="Новые посетители">Новые посетители<br>
    <input type="checkbox" id="cb_common" value="Обращения">Обращения<br>
    <input type="checkbox" id="cb_mounts" value="Монтажи">Монтажи<br>
    <input type="checkbox" id="cb_measure" value="Замеры">Замеры<br>
    <input type="checkbox" id="cb_deals" value="Договоры">Договоры<br>
    <input type="checkbox" id="cb_closed" value="Закрытые">Закрытые<br>
    <input type="checkbox" id="cb_refused" value="Отказы от сотрудничества">Отказы от сотрудничества<br>
</div>
<input type="button" id="btn_show" value="Показать график">
<div id="curve_chart" style="height: 500px; width: 98%;"></div>
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script type="text/javascript">
    jQuery(document).ready(function(){
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
        if (browser == "Firefox")
        {
            jQuery("#date1").mask("99.99.9999");
            jQuery("#date2").mask("99.99.9999");
            jQuery("#btn_show").click(function(){
                var date1 = transform_date(jQuery("#date1").val());
                var date2 = transform_date(jQuery("#date2").val());
                if(test_date(date2,date1))
                {
                    fill_table(date1,date2);
                }
            });
        }
        else
        {
            jQuery("#btn_show").click(function(){
                var date1 = jQuery("#date1").val();
                var date2 = jQuery("#date2").val();
                if(test_date(date2, date1))
                {
                    fill_grafic(date1, date2);
                }
            });
        }
    });

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

    function formatDate(date)
    {

      var dd = date.getDate();
      if (dd < 10) dd = '0' + dd;

      var mm = date.getMonth() + 1;
      if (mm < 10) mm = '0' + mm;

      var yy = date.getFullYear();
      if (yy < 10) yy = '0' + yy;

      return yy + '-' + mm + '-' + dd;
    }

    function fill_grafic(date1,date2)
    {
        if(date1<=date2)
        {
            var d1 = new Date(date1);
            var d2 = new Date(date2);
            var arr_graf = [];
            var arr_graf_data = [['Дата']];
            var cb_metrics_elem_check = document.getElementById('cb_metrics').checked;
            var cb_common_elem_check = document.getElementById('cb_common').checked;
            var cb_mounts_elem_check = document.getElementById('cb_mounts').checked;
            var cb_measure_elem_check = document.getElementById('cb_measure').checked;
            var cb_deals_elem_check = document.getElementById('cb_deals').checked;
            var cb_closed_elem_check = document.getElementById('cb_closed').checked;
            var cb_refused_elem_check = document.getElementById('cb_refused').checked;
            if (cb_metrics_elem_check)
            {
                arr_graf_data[0].push('Новые посетители');
            }
            if (cb_common_elem_check)
            {
                arr_graf_data[0].push('Обращения');
            }
            if (cb_mounts_elem_check)
            {
                arr_graf_data[0].push('Монтажи');
            }
            if (cb_measure_elem_check)
            {
                arr_graf_data[0].push('Замеры');
            }
            if (cb_deals_elem_check)
            {
                arr_graf_data[0].push('Договоры');
            }
            if (cb_closed_elem_check)
            {
                arr_graf_data[0].push('Закрытые');
            }
            if (cb_refused_elem_check)
            {
                arr_graf_data[0].push('Отказы от сотрудничества');
            }
            while(d1 <= d2)
            {
                date1 = formatDate(d1);
                //console.log(date1);
                arr_graf[date1] = {metrics: 0, common: 0, mounts: 0, measure: 0, deals: 0, closed: 0, refused: 0};
                jQuery.ajax({
                    url: "/index.php?option=com_gm_ceiling&task=getDetailedAnaliticByPeriod",
                    data: {
                        date1: date1,
                        date2: date1
                    },
                    dataType: "json",
                    async: false,
                    success: function (data)
                    {
                        //console.log(data);
                        for(var i = data.length; i--;)
                        {
                            if (!document.getElementById('cb'+data[i].id).checked)
                            {
                                continue;
                            }
                            arr_graf[date1].common += data[i].common - 0;
                            arr_graf[date1].mounts += data[i].mounts - 0;
                            arr_graf[date1].measure += data[i].measure - 0;
                            arr_graf[date1].deals += data[i].deals - 0;
                            arr_graf[date1].closed += data[i].closed - 0;
                            arr_graf[date1].refused += data[i].refused - 0;
                        }
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
                
                jQuery.ajax({
                    url: "/index.php?option=com_gm_ceiling&task=get_yandex_metric",
                    data: {
                        date1: date1,
                    },
                    dataType: "json",
                    async: false,
                    success: function (data)
                    {
                        data = JSON.parse(data).data;
                        console.log(data);
                        for(var i = data.length; i--;)
                        {
                            if (document.getElementById('cb1').checked && data[i].dimensions[0].name == "http://promo.gm-vrn.ru/"
                                || document.getElementById('cb2').checked && data[i].dimensions[0].name == "http://promo.gm-vrn.ru/1"
                                || document.getElementById('cb3').checked && data[i].dimensions[0].name == "http://promo.gm-vrn.ru/2"
                                || document.getElementById('cb4').checked && data[i].dimensions[0].name == "http://promo.gm-vrn.ru/3")
                            {
                                arr_graf[date1].metrics += data[i].metrics[0] - 0;
                            }
                        }
                    },
                    error: function (data) {
                        console.log(data);
                        var n = noty({
                            timeout: 2000,
                            theme: 'relax',
                            layout: 'center',
                            maxVisible: 5,
                            type: "error",
                            text: "Ошибка получения данных с яндекс метрики"
                        });
                    }
                });
                var agdp = [d1.getDate() + "." + (d1.getMonth() + 1)];
                if (cb_metrics_elem_check)
                {
                    agdp.push(arr_graf[date1].metrics);
                }
                if (cb_common_elem_check)
                {
                    agdp.push(arr_graf[date1].common);
                }
                if (cb_mounts_elem_check)
                {
                    agdp.push(arr_graf[date1].mounts);
                }
                if (cb_measure_elem_check)
                {
                    agdp.push(arr_graf[date1].measure);
                }
                if (cb_deals_elem_check)
                {
                    agdp.push(arr_graf[date1].deals);
                }
                if (cb_closed_elem_check)
                {
                    agdp.push(arr_graf[date1].closed);
                }
                if (cb_refused_elem_check)
                {
                    agdp.push(arr_graf[date1].refused);
                }

                arr_graf_data.push(agdp);
                d1.setDate(d1.getDate() + 1);
            }
            google.charts.load('current', {'packages':['corechart']});
            google.charts.setOnLoadCallback(drawChart);
            //console.log(arr_graf_data);
            function drawChart()
            {
                console.log(arr_graf_data);
                var data = google.visualization.arrayToDataTable(arr_graf_data);

                var options = {
                    hAxis:  { title: 'Дата' },
                    vAxis:  { title: 'Кол-во' },
                    legend: { position: 'bottom' },
                    //colors: ['#a52714', '#097138']
                };

                var chart = new google.visualization.LineChart(document.getElementById('curve_chart'));

                chart.draw(data, options);
            }
        }
        else
        {
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