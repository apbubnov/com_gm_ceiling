<?php
$monthBegin = date('Y-m-01');
$today = date('Y-m-d');
$analyticModel = Gm_ceilingHelpersGm_ceiling::getModel('analytic_new');
/*$data = $analyticModel->getMountServiceAnalytic($monthBegin,$today);
$projectCount = count($data);*/

?>
<h4>Данные по заказам монтажной службы</h4>
<div class="row">
    <div class="col-md-6">
        Общее кол-во проектов за период <span id="project_count"></span>
    </div>
    <div class="col-md-3">
        <input type="date" id="date_from" class="form-control date" value="<?=$monthBegin?>">
    </div>
    <div class="col-md-3">
        <input type="date" id="date_to" class="form-control date" value="<?=$today?>">
    </div>
</div>
<table class="table table-stripped" id="service_analytic">
    <thead>
        <th>
            Проект
        </th>
        <th>
            Дилер
        </th>

        <th>
            Сумма з\п бригаде
        </th>
        <th>
            Сумма по МС
        </th>
        <th>
            Процент
        </th>
    </thead>
    <tbody>
       <!-- <?php /*foreach ($data as $item){*/?>
            <tr data-id="<?/*=$item->id;*/?>">
                <td>
                    <?/*=$item->name;*/?>
                </td>
                <td>
                    <?/*=$item->id;*/?>
                </td>
                <td>
                    <?/*=$item->mounting_sum;*/?>
                </td>
                <td>
                    <?/*=$item->serviceSum;*/?>
                </td>
                <td>
                    <?/*=(($item->serviceSum-$item->mounting_sum)/$item->mounting_sum)*100;*/?>
                </td>
            </tr>
        --><?php /*}*/?>
    </tbody>
</table>

<script>
    jQuery(document).ready(function () {
        var savedDates = localStorage.getItem('dates');
        localStorage.removeItem('dates');
        if(!empty(savedDates)){
            savedDates = JSON.parse(savedDates);
            jQuery('#date_from').val(savedDates.date_from);
            jQuery('#date_to').val(savedDates.date_to);
        }
        getData();
        jQuery('.date').change(function () {
           getData();
        });

        jQuery('#service_analytic > tbody').on('click','tr',function(){
            var id = jQuery(this).data('id');
            location.href = '/index.php?option=com_gm_ceiling&view=project&type=calculator&subtype=project&id='+id;
            localStorage.setItem('dates',JSON.stringify({date_from:jQuery('#date_from').val(),date_to:jQuery('#date_to').val()}));
        });

        function getData() {
            var dateFrom = jQuery('#date_from').val(),
                dateTo = jQuery('#date_to').val();
            if(dateFrom>dateTo){

            }
            else{
                jQuery.ajax({
                    url: "index.php?option=com_gm_ceiling&task=analytic.getMountServiceAnalytic",
                    data: {
                        date1: dateFrom,
                        date2: dateTo
                    },
                    dataType: "json",
                    async: true,
                    success: function (data) {
                        var total_br = 0,
                            total_ms = 0;
                        jQuery('#project_count').text(data.length);
                        jQuery('#service_analytic > tbody').empty();
                        jQuery.each(data,function (n,el) {
                            total_br += +el.mounting_sum;
                            total_ms += +el.serviceSum;
                            jQuery('#service_analytic > tbody').append('<tr data-id="'+el.id+'">' +
                                '<td>'+el.name+'</td>'+
                                '<td>'+el.id+'</td>'+
                                '<td>'+el.mounting_sum+'</td>'+
                                '<td>'+el.serviceSum+'</td>'+
                                '<td>'+((el.serviceSum-el.mounting_sum)/el.mounting_sum)*100+'</td>'+
                                '</tr>');
                        });
                        total_br = parseFloat(total_br).toFixed(2);
                        total_ms = parseFloat(total_ms).toFixed(2);
                        jQuery('#service_analytic > tbody').append('<tr><td colspan="2" style="text-align:right;"><b>Итого</b></td><td>'+ total_br+'</td><td>'+total_ms+'</td><td>'+((total_ms-total_br)/total_br)*100+'</td></tr>');
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
        }
    });
</script>