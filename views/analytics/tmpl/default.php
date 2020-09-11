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
//$analytic_model = Gm_ceilingHelpersGm_ceiling::getModel('analytic_new');
//$data = json_encode($analytic_model->getData($user->dealer_id));
//$det_acnalytic_model = Gm_ceilingHelpersGm_ceiling::getModel('Analytic_detailed_new');
//$det_data = json_encode($det_acnalytic_model->getData($user->dealer_id));
$today = date('Y-m-d');
echo parent::getButtonBack();

?>
<style>
    .additional_analytic{
        width: 100%;
    }
</style>
<div id="preloader" style="display: none;" class="PRELOADER_GM PRELOADER_GM_OPACITY">
    <div class="PRELOADER_BLOCK"></div>
    <img src="/images/GM_R_HD.png" class="PRELOADER_IMG">
</div>

<div class="row right" style="margin-top: 15px;">
    <div class="col-md-3">
        <div class="row" style="margin-bottom: 15px;">
            <div class="col-md-12">
                <button class="btn btn-primary additional_analytic" id="show_other_analytic"> <i class="fas fa-list"></i> Дополнительная аналитика</button>
            </div>
        </div>
        <div id="additional_analytic_container" style="display: none;">
            <?php if ($user->dealer_id == 1 && ($user->dealer_type == 0 || $user->dealer_type == 1)) { ?>
            <div class="row" style="margin-bottom: 15px;">
                <div class="col-md-12">
                    <a class="btn btn-primary additional_analytic" href="/index.php?option=com_gm_ceiling&view=calls_analitic">
                        Звонки
                    </a>
                </div>
            </div>
            <div class="row" style="margin-bottom: 15px;">
                <div class="col-md-12">
                    <a class="btn btn-primary additional_analytic" href="/index.php?option=com_gm_ceiling&view=analytic_dealers">
                        Дилеры
                    </a>
                </div>
            </div>
            <div class="row" style="margin-bottom: 15px;">
                <div class="col-md-12">
                    <a class="btn btn-primary additional_analytic" href="/index.php?option=com_gm_ceiling&view=analytics&type=gaugers">
                        Замерщики
                    </a>
                </div>
            </div>
            <div class="row" style="margin-bottom: 15px;">
                <div class="col-md-12">
                    <a class="btn btn-primary additional_analytic" href="/index.php?option=com_gm_ceiling&view=analytics&type=visitors">
                        Посетители
                    </a>
                </div>
            </div>
            <?php } else { ?>
                <div class="row" style="margin-bottom: 15px;">
                    <div class="col-md-12">
                        <a class="btn btn-primary additional_analytic" href="/index.php?option=com_gm_ceiling&view=calls_analitic">
                            Менеджеры
                        </a>
                    </div>
                </div>
                <div class="row" style="margin-bottom: 15px;">
                    <div class="col-md-12">
                        <a class="btn btn-primary additional_analytic" href="/index.php?option=com_gm_ceiling&view=analytics&type=gaugers">
                            Замерщики
                        </a>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>

    <div class="col-md-9">
        <div class="col-md-3">
            <label for="c_date_from">Выбрать с:</label>
        </div>
        <div class="col-md-3">
            <input type="date" name="c_date_from" id="c_date_from" class="form-control">
        </div>
        <div class="col-md-1">
            <label for="c_date_to">до:</label>
        </div>
        <div class="col-md-3">
            <input type="date" name="c_date_to" id="c_date_to" class="form-control">
        </div>
        <div class="col-md-2">
            <button type="button" class="btn btn-primary" id="c_show_all">Показать всё</button>
        </div>
    </div>
</div>
<div class="row center" style="margin-top: 10px;margin-bottom: 10px">
    <div class="col-md-12">
        <table id="analytic_common" class="analitic-table">
            <thead id="thead" class="caption-style-tar">
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
</div>

<div class="row right" style="margin-bottom: 15px;">
    <div class="col-md-3"></div>
    <div class="col-md-9">
        <div class="col-md-3">
            <label for="d_date_from">Выбрать с:</label>
        </div>
        <div class="col-md-3">
            <input type="date" name="d_date_from" id="d_date_from" class="form-control" value="<?php echo $today ?>">
        </div>
        <div class="col-md-1">
            <label for="d_date_to">до:</label>
        </div>
        <div class="col-md-3">
            <input type="date" name="d_date_to" id="d_date_to" class="form-control" value="<?php echo $today ?>">
        </div>
        <div class="col-md-2">
            <button type="button" class="btn btn-primary" id="d_show_all">Показать всё</button>
        </div>
    </div>
</div>
<div class="row" style="margin-top: 10px">
    <div class="col-md-12">
        <table id="analytic_detailed" class="analitic-table">
            <thead id="thead_det" class="caption-style-analitic">
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
</div>
<div id="mw_container" class="modal_window_analitic">
    <button type="button" id="close-modal-window" class="close_modal_analic"><i class="fa fa-times fa-times-tar"
                                                                                aria-hidden="true"></i></button>
    <div id="mw_projects" class="window-with-table-analitic">
        <div class="row right">
            <div class="col-md-6"></div>
            <div class="col-md-6">
                <span style="color: #414099" id="count_of_projects"></span>
            </div>
        </div>
        <table id="table_projects" class="table_project_analitic"></table>
    </div>
</div>

<script type="text/javascript">
    var data = [], total = [];
    var ths = [];
    var det_ths = [];
    var det_data = [];

    jQuery(document).mouseup(function (e) { // событие клика по веб-документу
        var div = jQuery("#mw_projects"); // тут указываем ID элемента
        if (!div.is(e.target) // если клик был не по нашему блоку
            && div.has(e.target).length === 0) { // и не по его дочерним элементам
            jQuery("#mw_projects").hide();
            jQuery("#close-modal-window").hide();
            jQuery("#mw_container").hide();
        }
    });

    jQuery(document).ready(function () {

        jQuery("#show_other_analytic").click(function () {
            jQuery('#additional_analytic_container').toggle();
        });

        jQuery.ajax({
            beforeSend: function () {
                jQuery("#preloader").show();
            },
            url: "index.php?option=com_gm_ceiling&task=analytic.getData",
            data: {
                c_date_from: jQuery("#c_date_from").val(),
                c_date_to: jQuery("#c_date_to").val(),
                d_date_from: jQuery("#d_date_from").val(),
                d_date_to: jQuery("#d_date_to").val()
            },
            dataType: "json",
            async: true,
            success: function (successData) {
                data = successData.commonData;
                det_data = successData.detailedData;
                console.log("common", data);
                console.log("detailed", det_data);
                makeTh(jQuery("#analytic_common > thead"), data[0]);
                makeTh(jQuery("#analytic_detailed > thead"), det_data[0]);
                ths = jQuery("#analytic_common > thead  th");
                det_ths = jQuery("#analytic_detailed > thead  th").filter(":not([colspan]),[colspan='1']")
                data.shift();
                det_data.shift();
                fill_table("#analytic_common", data, ths);
                fill_table("#analytic_detailed", det_data, det_ths);
                hideEmptyTr("#analytic_common");
                hideEmptyTr("#analytic_detailed");
                jQuery("#c_show_all").click(function () {
                    jQuery('#analytic_common > tbody > tr').show();
                    jQuery('#analytic_common > tbody > tr:last').remove();
                    jQuery('#analytic_common').append('<tr></tr>');
                    fill_total_string("#analytic_common", ths);
                });

                jQuery("#d_show_all").click(function () {
                    jQuery('#analytic_detailed > tbody > tr').show();
                    jQuery('#analytic_detailed > tbody > tr:last').remove();
                    jQuery('#analytic_detailed').append('<tr></tr>');
                    fill_total_string("#analytic_detailed", det_ths);
                });

                jQuery(".clear_form_group").click(function (event) {
                    jQuery(this).closest("tr").hide();
                    table_name = "#" + jQuery(this).closest("tr")[0].parentNode.parentElement.id;
                    var tr = jQuery(this).closest("tr");
                    tr = tr[0];
                    var arr = [];
                    for (var i = 1; i < tr.children.length; i++) {
                        arr.push(+tr.children[i].childNodes[0].data);
                    }
                    console.log(table_name);
                    update_total(arr, table_name);
                });
                jQuery("#preloader").hide();
            },
            error: function (data) {
                jQuery("#preloader").hide();
                var n = noty({
                    timeout: 2000,
                    theme: 'relax',
                    layout: 'center',
                    maxVisible: 5,
                    type: "error",
                    text: "Ошибка получения данных!"
                });
            }
        });

        var savedProjects = localStorage.getItem('projectsData');
        localStorage.removeItem("projectsData");
        if (!empty(savedProjects)) {
            savedProjects = JSON.parse(savedProjects);
            fillModalTable(savedProjects);
        }

        jQuery("#c_date_to").change(function () {
            var date1 = jQuery("#c_date_from").val(),
                date2 = jQuery("#c_date_to").val();
            getDataByPeriod(date1, date2, 1);
        });

        jQuery("#c_date_from").change(function () {
            var date1 = jQuery("#c_date_from").val(),
                date2 = jQuery("#c_date_to").val();
            getDataByPeriod(date1, date2, 1);
        });

        jQuery("#d_date_to").change(function () {
            var date1 = jQuery("#d_date_from").val(),
                date2 = jQuery("#d_date_to").val();
            getDataByPeriod(date1, date2, 0);
        });

        jQuery("#d_date_from").change(function () {
            var date1 = jQuery("#d_date_from").val(),
                date2 = jQuery("#d_date_to").val();
            getDataByPeriod(date1, date2, 0);
        });
    });
    jQuery(document).on("click", "#analytic_common tbody tr", function (event) {
        var target = event.target;
        projects = "";
        if (target.tagName == 'TD' || target.tagName == 'B') {
            target = (target.tagName == 'B') ? jQuery(target.closest("td")) : target;
            var rek_name = jQuery(target.closest("tr")).data('value');
            var index = jQuery(target)[0].cellIndex;
            var statuses = jQuery(jQuery('#analytic_common > thead > tr')[0].children[index]).data('value');
            var date1 = jQuery("#c_date_from").val();
            var date2 = jQuery("#c_date_to").val();
            console.log(statuses);
            if (rek_name != undefined) {
                if (statuses != "expenses") {
                    for (let i = 0; i < data.length; i++) {
                        if (data[i]['id'] == rek_name) {
                            projects = data[i]['projects'][statuses];
                        }
                    }
                } else {
                    var td = jQuery(target);
                    var oldExpenses = td.text();
                    td.empty();
                    td.append("<div class='row'><div class='col-md-5 left' style='padding:0 0 0 0'>" +
                        "<input type='text' class='inputactive' value='" + oldExpenses + "' name='newExpenses'></div>" +
                        "<div class='col-md-7 left' style='padding:0 0 0 0'>" +
                        "<button class='btn btn-primary' name='saveExpenses'><i class=\"fas fa-save\" aria-hidden=\"true\"></i></button></div></div>");
                    jQuery("[name = 'saveExpenses']").click(function () {
                        var newExpense = jQuery(this.closest('td')).find("[name = newExpenses]").val();
                        jQuery.ajax({
                            url: "index.php?option=com_gm_ceiling&task=api_phones.saveExpense",
                            data: {
                                api_phone_id: rek_name,
                                newExpense: newExpense
                            },
                            dataType: "json",
                            async: true,
                            success: function (data) {
                                td.empty();
                                td.text(newExpense);
                                var totalTd = jQuery('#analytic_common > tbody > tr:last > td:last');
                                var totalExpenses = totalTd.text();
                                totalTd.text(totalExpenses - oldExpenses + +newExpense);

                            },
                            error: function (data) {
                                var n = noty({
                                    timeout: 2000,
                                    theme: 'relax',
                                    layout: 'center',
                                    maxVisible: 5,
                                    type: "error",
                                    text: "Ошибка сохранения!"
                                });
                            }
                        });
                    })
                    return;
                }
            } else {
                for (let i = 0; i < data.length; i++) {
                    if (data[i]['projects'][statuses])
                        projects += data[i]['projects'][statuses];
                }
            }
            console.log(projects);
            if (projects) {
                getProjects(projects);
            } else {
                var n = noty({
                    timeout: 2000,
                    theme: 'relax',
                    layout: 'center',
                    maxVisible: 5,
                    type: "error",
                    text: "Проекты отсутствуют!"
                });
            }
        }
    });


    jQuery(document).on("click", "#analytic_detailed tbody tr", function (event) {
        var target = event.target;
        projects = "";
        if (target.tagName == 'TD' || target.tagName == 'B') {
            target = (target.tagName == 'B') ? jQuery(target.closest("td")) : target;
            var rek_name = jQuery(target.closest("tr")).data('value');
            let click_indexes = [];
            jQuery.each(det_ths, function (index, item) {
                key = jQuery(item).data('value');
                let cell_index = item.cellIndex;
                let bias = jQuery(item).data('bias');
                if (typeof bias !== typeof undefined && bias !== false) {
                    cell_index += bias;
                }
                click_indexes[cell_index] = key;
            });
            console.log(click_indexes);
            var index = jQuery(target)[0].cellIndex;
            var statuses = click_indexes[index];
            var date1 = jQuery("#d_date_from").val();
            var date2 = jQuery("#d_date_to").val();
            console.log(rek_name, statuses);
            if (rek_name != undefined) {
                for (let i = 0; i < det_data.length; i++) {
                    if (det_data[i]['id'] == rek_name) {
                        projects = det_data[i]['projects'][statuses];
                    }
                }
            } else {
                for (let i = 0; i < det_data.length; i++) {
                    if (det_data[i]['projects'][statuses])
                        projects += det_data[i]['projects'][statuses] + ';';
                }
            }
            if (projects) {
                console.log(projects);
                getProjects(projects);
            } else {
                var n = noty({
                    timeout: 2000,
                    theme: 'relax',
                    layout: 'center',
                    maxVisible: 5,
                    type: "error",
                    text: "Проекты отсутствуют!"
                });
            }
        }
    });

    function makeTh(container, data) {
        var row = jQuery("<tr/>"), row1 = jQuery("<tr/>");

        container.empty();
        jQuery.each(data, function (key, value) {
            if (typeof value == 'string') {
                row.append(jQuery("<th/ data-value = '" + key + "'>").text(value));
                row1 = "";
            }
            if (typeof value == 'object') {
                let bias = (value.bias ? value.bias : 0);
                if (!value.columns) {
                    row.append(jQuery("<th/ rowspan =" + value.rowspan + " data-bias='" + bias + "' data-value = '" + key + "'>").text(value.head_name));
                } else {

                    row.append(jQuery("<th/ colspan =" + Object.keys(value.columns).length + " data-value = '" + key + "'>").text(value.head_name));
                    jQuery.each(value.columns, function (key_c, value_c) {

                        row1.append(jQuery("<th/ data-bias='" + bias + "' data-value = '" + key_c + "'>").text(value_c));
                    });
                }

            }
        });
        container.append(row);

        if (row1) {
            container.append(row1);
        }
    }

    function getDataByPeriod(date1, date2, type) {
        var table_name = "", table_ths = [];
        if (type) {
            url = "index.php?option=com_gm_ceiling&task=getAnaliticByPeriod";
            table_name = "#analytic_common";
            table_ths = ths;

        } else {
            url = "index.php?option=com_gm_ceiling&task=getDetailedAnaliticByPeriod";
            table_name = "#analytic_detailed";
            table_ths = det_ths;
        }
        jQuery.ajax({
            beforeSend: function () {
                jQuery("#preloader").show();
            },
            url: url,
            data: {
                date1: date1,
                date2: date2,
            },
            dataType: "json",
            async: true,
            success: function (result) {
                total = [];
                result.shift();
                if (type) {
                    data = result;
                } else {
                    det_data = result;
                }
                fill_table(table_name, result, table_ths);
                hideEmptyTr(table_name);
                console.log(result);
                jQuery("#preloader").hide();
            },
            error: function (data) {
                console.log(data.responseText);
                jQuery("#preloader").hide();
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

    function hideEmptyTr(table_name) {
        jQuery(table_name + " tbody tr").each(function () {
            var tds = jQuery("td", this);
            var empty = true;
            for (var i = 1; i < tds.length - 1; i++) {

                if (tds[i].innerHTML.trim() != "0") {
                    empty = false;
                }
            }
            if (empty) {
                this.style.display = "none";
            }
        });
    }

    function fill_table(container, data, ths) {
        var key = "";
        let tds = [], result = [];
        jQuery(container + ' tbody').empty();
        if(jQuery(container + '> thead > tr:last .hide_cont').length == 0) {
            jQuery(container + '> thead > tr:last').append("<th class=\"hide_cont\"></th>");
        }
        for (let i = 0; i < data.length; i++) {
            jQuery(container).append('<tr></tr>');
            jQuery(container + ' > tbody > tr:last').attr('data-value', data[i]['id'] ? data[i]['id'] : 0);
            for (var j = ths.length - 1; j >= 0; j--) {
                jQuery(container + ' > tbody > tr:last').append('<td></td>');
            }
            jQuery.each(ths, function (index, item) {
                key = jQuery(item).data('value');
                let val = (data[i][key]) ? data[i][key] : 0;
                tds = jQuery(container + " > tbody >tr:last td");
                let cell_index = item.cellIndex;
                let bias = jQuery(item).data('bias');
                if (typeof bias !== typeof undefined && bias !== false) {
                    cell_index += bias;
                }
                tds[cell_index].innerText = val;
                if (key == 'advt_title') {
                    result[key] = '<b>Итого</b>';
                } else {
                    result[key] = (result[key]) ? result[key] - (-val) : val;
                }
            });
            total[container] = result;
            jQuery(container + ' > tbody > tr:last').append("<td><button class='clear_form_group btn btn-primary' type='button'><i class=\"fa fa-eye-slash\" aria-hidden=\"true\"></i></button></td> ");
        }

        fill_total_string(container, ths);
    }


    function fill_total_string(container, ths) {
        if (Object.keys(total).length) {
            jQuery(container).append('<tr></tr>');
            for (var j = ths.length - 1; j >= 0; j--) {
                jQuery(container + ' > tbody > tr:last').append('<td></td>');
            }
            let tds = jQuery(container + " > tbody >tr:last td"),
                outputVal;
            jQuery.each(ths, function (index, item) {
                key = jQuery(item).data('value');
                let cell_index = item.cellIndex,
                    bias = jQuery(item).data('bias');
                if (typeof bias !== typeof undefined && bias !== false) {
                    cell_index += bias;
                }
                outputVal = !isNaN(parseFloat(total[container][key])) ? parseFloat(total[container][key]).toFixed(2) : total[container][key];
                tds[cell_index].innerHTML = '<b>' + outputVal + '</b>';
            });
        }
    }


    function update_total(arr, table_name) {
        var tr = jQuery(table_name + ' > tbody > tr:last');
        tr = tr[0];
        for (var i = 1; i < tr.children.length; i++) {
            tr.children[i].children[0].childNodes[0].data = tr.children[i].children[0].childNodes[0].data - arr[i - 1];
        }
    }

    function fillModalTable(data) {
        var profit = 0, sum = 0, totalSum = 0, totalProfit = 0;

        jQuery("#mw_projects").show('slow');
        jQuery("#close-modal-window").show();
        jQuery("#mw_container").show();
        jQuery("#table_projects").empty();
        if (data.length == 0) {
            TrOrders = '<tr id="caption-data"></tr><tr><td>Проектов нет</td></tr>';
            jQuery("#table_projects").append(TrOrders);
        } else {
            jQuery('#count_of_projects').html('<b>Количество проектов:</b> '+ data.length);
            TrOrders = '<tr id="caption-tr"><td>Id</td><td>Адрес</td><td>Статус</td><td>Сумма</td><td>Прибыль</td><td>Ярлык</td></tr>';
            for (var i = 0; i < data.length; i++) {
                totalSum += +data[i].project_sum;
                totalProfit += +data[i].profit;
                TrOrders += '<tr class="link_row" data-href = \'/index.php?option=com_gm_ceiling&view=clientcard&id=' + data[i].client_id + '\'>' +
                    '<td>' + data[i].id + '</td><td>' + data[i].project_info + '</td><td>' + data[i].status + '</td><<td>' + data[i].project_sum + '</td>' +
                    '<td>' + parseFloat(data[i].profit).toFixed(2) + '</td><td style = "background:linear-gradient(90deg, white 70%' + data[i].color_code + ' 100%);">' + data[i].title + '</td></tr>';

            }

            jQuery("#table_projects").append(TrOrders);
            jQuery("#table_projects").append("<tr><td colspan=3><b>Итого</b></td><td>" + parseFloat(totalSum).toFixed(2) + "</td><td>" + parseFloat(totalProfit).toFixed(2) + "</td><td></td></tr>")
        }
        jQuery(".link_row").click(function () {
            localStorage.setItem('projectsData', JSON.stringify(data));
            window.location = jQuery(this).data("href");
        });
    }

    function getProjects(ids) {
        jQuery.ajax({
            url: "index.php?option=com_gm_ceiling&task=getAnaliticProjects",
            data: {
                ids: ids
            },
            dataType: "json",
            async: true,
            success: function (data) {
                console.log(data);
                //localStorage.setItem('projectsData', JSON.stringify(data));
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

</script>