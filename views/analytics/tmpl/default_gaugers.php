<?php
/**
 * Created by PhpStorm.
 * User: bubnov
 * Date: 30.10.2019
 * Time: 9:26
 */
$analyticModel = Gm_ceilingHelpersGm_ceiling::getModel('analytic_new');
$today = date('Y-m-d');
$data = $analyticModel->getGaugersAnalytic($today,$today);
?>
<h4>Аналитика по замерщикам</h4>
<div class="row" style="margin-bottom: 15px;">
    <div class="col-md-6"></div>
    <div class="col-md-3">
        <input type="date" id="date_from" value="<?=$today?>" class="form-control date_selector">
    </div>
    <div class="col-md-3">
        <input type="date" id="date_to" value="<?=$today?>" class="form-control date_selector" >
    </div>

</div>
<table class="table table_cashbox" id="gaugers_analytic">
    <thead>
        <th>
            Замерщик
        </th>
        <th>
            Кол-во замеров
        </th>
        <th>
            Кол-во договоров
        </th>
        <th>
            Конверсия
        </th>
        <th>
            Кол-во отказов
        </th>
        <th>
            Сумма договоров
        </th>
        <th>
            Прибыль
        </th>
    </thead>
    <tbody>
        <?php foreach($data as $item){
            if(!empty($item->deals_count)&&!empty($item->measures_count)){
                $conversion = round(($item->deals_count*100)/$item->measures_count,2);
            }
            else{
                $conversion = '-';
            }
           ?>
            <tr data-id = "<?= $item->id?>" class="tr_click">
                <td>
                    <?=$item->name?>
                </td>
                <td data-pr_type="measures_projects">
                    <?= !empty($item->measures_count) ? $item->measures_count : '-'?>
                </td>
                <td data-pr_type="deals_projects">
                    <?= !empty($item->deals_count) ? $item->deals_count : '-'?>
                </td>
                <td >
                    <?=$conversion;?>
                </td>
                <td data-pr_type="refused_projects">
                    <?=!empty($item->refused_count) ? $item->refused_count : '-';?>
                </td>
                <td>
                    <?=!empty($item->total_sum) ? $item->total_sum : '-';?>
                </td>
                <td>
                    <?=$item->total_sum - $item->total_cost;?>
                </td>
            </tr>
        <?php } ?>
    </tbody>
</table>
<div id="mw_container" class="modal_window_container">
    <button type="button" id="close" class="close_btn"><i class="fa fa-times fa-times-tar" aria-hidden="true"></i></button>
    <div id="mw_projects" class ="modal_window">
        <table id="table_projects" class = "table_project_analitic"></table>
    </div>
</div>

<script type="text/javascript">
    jQuery(document).mouseup(function (e){ // событие клика по веб-документу
        var div = jQuery("#mw_projects"); // тут указываем ID элемента
        if (!div.is(e.target) // если клик был не по нашему блоку
            && div.has(e.target).length === 0) { // и не по его дочерним элементам
            jQuery("#mw_projects").hide();
            jQuery("#close").hide();
            jQuery("#mw_container").hide();
        }
    });

    jQuery(document).ready(function(){
        var data = JSON.parse('<?php echo json_encode($data);?>');
        jQuery('body').on('click','.tr_click td',function() {

            var pr_type = jQuery(this).data("pr_type"),
                gauger_id = jQuery(this).closest('tr').data('id'),
                projects = data.find(function (elem) {
                    return elem.id == gauger_id;
                })[pr_type];
            if (!empty(projects)) {
                jQuery.ajax({
                    url: "index.php?option=com_gm_ceiling&task=getAnaliticProjects",
                    data: {
                        ids: projects
                    },
                    dataType: "json",
                    async: true,
                    success: function (data) {
                        console.log(data);
                        //localStorage.setItem('projectsData',JSON.stringify(data));
                        fillModalTable(data);
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
        });

        jQuery('.date_selector').change(function(){
            var date1 = jQuery("#date_from").val(),
                date2 = jQuery("#date_to").val();
            if(date1<date2){
                jQuery.ajax({
                    url: "index.php?option=com_gm_ceiling&task=analytic.getGaugersAnalytic",
                    data: {
                        date_from: date1,
                        date_to: date2
                    },
                    dataType: "json",
                    async: true,
                    success: function (result) {
                        data = result;
                        jQuery("#gaugers_analytic >tbody").empty();
                        jQuery.each(data,function(index,elem){
                            var measures_count = !empty(elem.measures_count) ? elem.measures_count : '-',
                                deals_count = !empty(elem.deals_count) ? elem.deals_count : '-',
                                refused_count = !empty(elem.refused_count) ? elem.refused_count : '-',
                                conversion = !empty(elem.measures_count) && !empty(elem.deals_count) ? ((elem.deals_count*100)/elem.measures_count).toFixed(2) : '-',
                                total_sum = !empty(elem.total_sum) ? elem.total_sum : '-';
                            jQuery("#gaugers_analytic > tbody").append('<tr data-id="'+elem.id+'" class="tr_click"></tr>');
                            jQuery("#gaugers_analytic > tbody > tr:last").append('<td>'+ elem.name +'</td>' +
                                                                                 '<td data-pr_type="measures_projects">'+ measures_count +'</td>' +
                                                                                 '<td data-pr_type="deals_projects">'+ deals_count +'</td>' +
                                                                                 '<td>'+ conversion +'</td>' +
                                                                                 '<td data-pr_type="refused_projects">'+ refused_count +'</td>' +
                                                                                 '<td>'+ total_sum +'</td>' +
                                                                                 '<td>'+ (elem.total_sum - elem.total_cost).toFixed(2) +'</td>');
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
    function fillModalTable(data) {
        var profit = 0, sum = 0, totalSum = 0, totalProfit = 0;
        jQuery("#mw_projects").show('slow');
        jQuery("#close").show();
        jQuery("#mw_container").show();
        jQuery("#table_projects").empty();
        if (data.length == 0) {
            TrOrders = '<tr id="caption-data"></tr><tr><td>Проектов нет</td></tr>';
            jQuery("#table_projects").append(TrOrders);
        } else {
            TrOrders = '<tr id="caption-tr"><td>Id</td><td>Адрес</td><td>Статус</td><td>Сумма</td><td>Прибыль</td></tr>';
            for (var i = 0; i < data.length; i++) {
                sum = data[i].sum;
                profit = data[i].profit;
                totalSum += +sum;
                totalProfit += +profit;
                TrOrders += '<tr class="link_row" data-href = \'/index.php?option=com_gm_ceiling&view=clientcard&id=' + data[i].client_id + '\'>' +
                    '<td>' + data[i].id + '</td><td>' + data[i].project_info + '</td><td>' + data[i].status + '</td><<td>' + data[i].sum + '</td>' +
                    '<td>' + parseFloat(data[i].profit).toFixed(2) + '</td></tr>';

            }

            jQuery("#table_projects").append(TrOrders);
            jQuery("#table_projects").append("<tr><td colspan=3><b>Итого</b></td><td>" + totalSum + "</td><td>" + totalProfit.toFixed(2) + "</td><td></td></tr>")
        }
        jQuery(".link_row").click(function () {
            //localStorage.setItem('projectsData',JSON.stringify(data));
            window.location = jQuery(this).data("href");
        });
    }
</script>