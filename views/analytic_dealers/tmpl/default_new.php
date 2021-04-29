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
$data = json_encode($model->getData($date_from, $date_to, 5, true));
$month_begin_date = date('Y-m-01');
$today = date('Y-m-d');
$jinput = JFactory::getApplication()->input;
$new_analytic = $jinput->get('new', 0, 'INT');
$goods = Gm_ceilingHelpersGm_ceiling::getModel('stock')->getArrayOfGoods();
?>
<style>
    .container {
        width: auto !important;
        margin-left: 0px !important;
        margin-right: 0px !important;
    }

    .selectize-control.contacts .selectize-input > div {
        padding: 1px 10px;
        font-size: 13px;
        font-weight: normal;
        -webkit-font-smoothing: auto;
        color: #f7fbff;
        text-shadow: 0 1px 0 rgba(8, 32, 65, 0.2);
        background: #2183f5;
        background: -moz-linear-gradient(top, #2183f5 0%, #1d77f3 100%);
        background: -webkit-gradient(linear, left top, left bottom, color-stop(0%, #2183f5), color-stop(100%, #1d77f3));
        background: -webkit-linear-gradient(top, #2183f5 0%, #1d77f3 100%);
        background: -o-linear-gradient(top, #2183f5 0%, #1d77f3 100%);
        background: -ms-linear-gradient(top, #2183f5 0%, #1d77f3 100%);
        background: linear-gradient(to bottom, #2183f5 0%, #1d77f3 100%);
        filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#2183f5', endColorstr='#1d77f3', GradientType=0);
        border: 1px solid #0f65d2;
        -webkit-border-radius: 999px;
        -moz-border-radius: 999px;
        border-radius: 999px;
        -webkit-box-shadow: 0 1px 1px rgba(0, 0, 0, 0.15);
        -moz-box-shadow: 0 1px 1px rgba(0, 0, 0, 0.15);
        box-shadow: 0 1px 1px rgba(0, 0, 0, 0.15);
    }

    .selectize-control.contacts .selectize-input > div.active {
        background: #0059c7;
        background: -moz-linear-gradient(top, #0059c7 0%, #0051c1 100%);
        background: -webkit-gradient(linear, left top, left bottom, color-stop(0%, #0059c7), color-stop(100%, #0051c1));
        background: -webkit-linear-gradient(top, #0059c7 0%, #0051c1 100%);
        background: -o-linear-gradient(top, #0059c7 0%, #0051c1 100%);
        background: -ms-linear-gradient(top, #0059c7 0%, #0051c1 100%);
        background: linear-gradient(to bottom, #0059c7 0%, #0051c1 100%);
        filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#0059c7', endColorstr='#0051c1', GradientType=0);
        border-color: #0051c1;
    }

    .selectize-control.contacts .selectize-input > div .email {
        opacity: 0.8;
    }

    .selectize-control.contacts .selectize-input > div .name + .email {
        margin-left: 5px;
    }

    .selectize-control.contacts .selectize-input > div .email:before {
        content: '<';
    }

    .selectize-control.contacts .selectize-input > div .email:after {
        content: '>';
    }

    .selectize-control.contacts .selectize-dropdown .caption {
        font-size: 12px;
        display: block;
        color: #a0a0a0;
    }
</style>
<script src="/templates/gantry/js/selectize.js"></script>
<script src="/templates/gantry/js/index.js"></script>
<div class="container center">
    <div class="row caption_div" data-container="run_container">
        <div class="col-md-4" style="text-align: left">
            <h4 id="issued_projects_title">Данные по запущенным проектам</h4>
        </div>
        <div class="col-md-2">
            <i class="fas fa-angle-up" style="font-size: 20pt;"></i>
        </div>
    </div>
    <div id="run_container">
        <div class="row">
            <div class="col-md-4" align="left">
                <input type="radio" name="selection_type" value="5" class="radio" id="run" checked="true"><label
                        for="run">Выборка по запущенным в производство</label><br>
                <input type="radio" name="selection_type" value="10" class="radio" id="in_work"><label for="in_work">Выборка
                    по запущенным в цех</label>
            </div>
            <div class="col-md-8" align="right">
                <div class="col-md-3">
                    <label for="date_from">Выбрать с: </label>
                </div>
                <div class="col-md-3 col-sm-6">
                    <input type="date" name="date_from" id="date_from" class="form-control"
                           value="<?php echo $date_from; ?>">
                </div>
                <div class="col-md-3 col-sm-6">
                    <label for="date_to">по:</label>
                </div>
                <div class="col-md-3 col-sm-6">
                    <input type="date" name="date_to" id="date_to" class="form-control" value="<?php echo $date_to; ?>">
                </div>
            </div>
        </div>
        <div class="row" align="right">
            <div class="col-md-12">
                <label style="color: #414099;font-size: 14pt">Общее количество дилеров: <b><span
                                id="dealers_count">0</span></b></label>
            </div>
        </div>
        <div class="row center" style="margin-bottom: 15px;">
            <table id="analytic" class="analitic-table">
                <thead id="thead" class="caption-style-tar">
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
    <hr style="border: none; color: #414099; background-color: #414099; height: 2px;">
    <div class="row caption_div" data-container="issued_container">
        <div class="col-md-4" style="text-align: left">
            <h4 id="issued_projects_title">Данные по выданным проектам</h4>
        </div>
        <div class="col-md-2">
            <i class="fas fa-angle-up" style="font-size: 20pt;"></i>
        </div>
    </div>
    <div id="issued_container">
        <div class="row" style="text-align:right;margin-bottom: 1em;">
            <div class="col-md-4 col-sm-6"></div>
            <div class="col-md-2">
                <label for="date_from" style="vertical-align: middle;">Выбрать с: </label>
            </div>
            <div class="col-md-2 col-sm-6">
                <input type="date" name="issued_date_from" id="issued_date_from" class="form-control" value="<?php echo $date_from; ?>">
            </div>
            <div class="col-md-2 col-sm-6">
                <label for="date_to" style="vertical-align: middle;">по:</label>
            </div>
            <div class="col-md-2 col-sm-6">
                <input type="date" name="issued_date_to" id="issued_date_to" class="form-control" value="<?php echo $date_to; ?>">
            </div>
        </div>
        <table id="table_issued" class="analitic-table">
            <thead class="caption-style-tar">
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
                Разница
            </th>
            <th>
                Стоимость компонентов
            </th>
            <th>
                Себестоимость компонентов
            </th>
            <th>
                Разница
            </th>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
    <hr style="border: none; color: #414099; background-color: #414099; height: 2px;">
    <div class="row caption_div" data-container="sold_goods_container">
        <div class="col-md-4" style="text-align: left">
            <h4 id="goods_sold_title">Проданные товары</h4>
        </div>
        <div class="col-md-2">
            <i class="fas fa-angle-down" style="font-size: 20pt;"></i>
        </div>
    </div>
    <div id="sold_goods_container" class="row" style="display: none;">
        <div class="row right" style="margin-bottom: 1em;">
            <div class="col-md-4">
                <div class="control-group">
                    <select id="goods_filter" placeholder="Начните вводить название товара"></select>
                </div>
            </div>
            <div class="col-md-2">
                <b><span>Выбрать с:</span></b>
            </div>
            <div class="col-md-2">
                <input type="date" class="form-control goods_date" id="g_date_from" value="<?= $month_begin_date ?>">
            </div>
            <div class="col-md-2">
                <b><span>до:</span></b>
            </div>
            <div class="col-md-2">
                <input type="date" class="form-control goods_date" id="g_date_to" value="<?= $today; ?>">
            </div>
        </div>
        <table id="goods_analytic_table" class="analitic-table">
            <thead class="caption-style-tar">
            <tr>
                <th class="center">
                    Наименование
                </th>
                <th class="center">
                    Кол-во
                </th>
                <th class="center">
                    Стоимость
                </th class="center">
                <th class="center">
                    Себестоимость
                </th>
                <th class="center">
                    Разница
                </th>
            </tr>
            </thead>
            <tbody>
            </tbody>
            <tfoot>
            <tr>
                <td colspan="2" style="text-align: right;">
                    <b>Итого:</b>
                </td>
                <td>
                    <b><span id="total_sum">-</span></b>
                </td>
                <td>
                    <b><span id="total_cost">-</span></b>
                </td>
                <td>
                    <b><span id="total_profit">-</span></b>
                </td>
            </tr>
            </tfoot>
        </table>
    </div>
    <hr style="border: none; color: #414099; background-color: #414099; height: 2px;">
    <!--График по квадратуре-->
    <div class="row caption_div" data-container="chart_container">
        <div class="col-md-4" style="text-align: left">
            <h4 id="chart_title">График по квадратуре</h4>
        </div>
        <div class="col-md-2">
            <i class="fas fa-angle-down" style="font-size: 20pt;"></i>
        </div>
    </div>
    <div id="chart_container" style="display: none;">
        <div class="row center">
            <div class="col-md-6" style="border: 1px #414099 solid;border-radius: 15px;">
                <div class="row">
                    <input type="radio" name="interval_radio" id="month" class="radio" value="0"><label for="month">Выборка
                        по месяцам</label>
                </div>
                <div class="row" id="months_div" style="display: none;">
                    <div class="col-md-12 right" style="margin-bottom: 1em;">
                        <div class="col-md-4">
                            <label for="year1">
                                Выбрать с
                            </label>
                        </div>
                        <div class="col-md-4">
                            <select class="form-control" id="month1">
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
                        </div>
                        <div class="col-md-4">
                            <div class="col-md-10">
                                <input type="text" class="form-control" style="width: 60px;" id="year1"
                                       value="<?php echo date(Y); ?>">
                            </div>
                            <div class="col-md-2">
                                <span>г.</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12 right">
                        <div class="col-md-4">
                            <label for="year2">
                                по
                            </label>
                        </div>
                        <div class="col-md-4">
                            <select class="form-control" id="month2">
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
                        </div>
                        <div class="col-md-4">
                            <div class="col-md-10">
                                <input type="text" class="form-control" style="width: 60px;" id="year2"
                                       value="<?php echo date(Y); ?>">
                            </div>
                            <div class="col-md-2">
                                <span>г.</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12" style="margin-bottom: 15px;">
                        <button class="btn btn-primary show" type="button"> Показать</button>
                    </div>
                </div>
            </div>
            <div class="col-md-6" style="border: 1px #414099 solid;border-radius: 15px;">
                <div class="row">
                    <input type="radio" name="interval_radio" id="dates" class="radio" checked="true" value="1"><label
                            for="dates">Выборка по интревалу</label>
                </div>
                <div class="row" id="dates_div">
                    <div class="col-md-12 right">
                        <div class="col-md-6">
                            <label for="date1">
                                Выбрать с
                            </label>
                        </div>
                        <div class="col-md-6">
                            <input type="date" class="form-control" id="date1" value="<?php echo $month_begin_date ?>">
                        </div>
                    </div>
                    <div class="col-md-12 right">
                        <div class="col-md-6">
                            <label for="date2">
                                по
                            </label>
                        </div>
                        <div class="col-md-6">
                            <input type="date" class="form-control" id="date2" value="<?php echo $today ?>">
                        </div>
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
    </div>
    <!--end-->
    <div class="modal_window_container" id="mw_container">
        <button type="button" class="close_btn" id="close_mw">
            <i class="fa fa-times fa-times-tar" aria-hidden="true"></i>
        </button>
        <div id="mw_detailed" class="modal_window">
            <table id="detailed_analytic" class="table_project_analitic">
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
        <div class="modal_window" id="mw_detailes_issued">
            <table id="det_issued" class="analitic-table">
                <thead class="caption-style-tar">
                <th class="center">#</th>
                <th class="center">Адрес</th>
                <th class="center">Стоимость</th>
                <th class="center">Себестоимость</th>
                <th class="center">Разница</th>
                <th class="center">Стоимость полотен</th>
                <th class="center">Себестоимость полотен</th>
                <th class="center">Разница</th>
                <th class="center">Стоимость компонентов</th>
                <th class="center">Себестоимость компонентов</th>
                <th class="center">Разница</th>
                </thead>
                <tbody>
                </tbody>
                <tfoot>
                <th class="center" colspan="2">Итого</th>
                <th class="center project_sum"></th>
                <th class="center cost_sum"></th>
                <th class="center project_diff"></th>
                <th class="center canvases_sum"></th>
                <th class="center canvases_cost_sum"></th>
                <th class="center canvases_diff"></th>
                <th class="center components_sum"></th>
                <th class="center components_cost_sum"></th>
                <th class="center components_diff"></th>
                </tfoot>

            </table>
        </div>
    </div>
</div>
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script type="text/javascript">
    var data = JSON.parse('<?php echo $data;?>'),
        issuedData = data,
        projectsDetData;
    var month_translate = [];
    month_translate["January"] = ['Январь', 0];
    month_translate["February"] = ['Февраль', 1];
    month_translate["March"] = ['Март', 2];
    month_translate["April"] = ['Апрель', 3];
    month_translate["May"] = ['Май', 4];
    month_translate["June"] = ['Июнь', 5];
    month_translate["July"] = ['Июль', 6];
    month_translate["August"] = ['Август', 7];
    month_translate["September"] = ['Сентябрь', 8];
    month_translate["October"] = ['Октябрь', 9];
    month_translate["November"] = ['Ноябрь', 10];
    month_translate["December"] = ['Декабрь', 11];

    function getDataChart() {
        var select_type = jQuery('input[name="interval_radio"]:checked').val(),
            date1, date2;

        if (select_type == 1) {
            date1 = jQuery("#date1").val();
            date2 = jQuery("#date2").val();
        } else {
            date1 = jQuery("#year1").val() + '-' + jQuery("#month1").val() + '-01';
            date2 = jQuery("#year2").val() + '-' + jQuery("#month2").val() + '-' + new Date(jQuery("#year2").val(), jQuery("#month2").val(), 0).getDate();
        }
        if (date1 <= date2) {
            jQuery.ajax({
                type: 'POST',
                url: "index.php?option=com_gm_ceiling&task=calculations.getQuadrature",
                data: {
                    date1: date1,
                    date2: date2,
                    type: select_type
                },
                success: function (data) {
                    console.log(data);
                    if (select_type == 1) {
                        jQuery.each(data, function (index, elem) {
                            data[index][1] = parseFloat(elem[1]);
                        });
                        if (data.length)
                            drawGraphic(data);
                    } else {
                        var new_data = [];
                        jQuery.each(data, function (index, elem) {
                            var ym = elem[0].split(' ', 2);
                            data[index][2] = month_translate[ym[0]][0] + ' ' + ym[1];
                            var new_index = month_translate[ym[0]][1];
                            if (new_data[new_index]) {
                                if (parseFloat(elem[1])) {
                                    new_data[new_index][1] += parseFloat(elem[1]);
                                }
                            } else {
                                new_data[new_index] = [];
                                if (parseFloat(elem[1])) {
                                    new_data[new_index][1] = parseFloat(elem[1]);
                                } else {
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
                        if (data.length)
                            drawGraphic(data);
                    }

                },
                dataType: "json",
                async: false,
                timeout: 10000,
                error: function (data) {
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
        } else {
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

    function drawGraphic(quadr_data) {
        google.charts.load('current', {'packages': ['line']});
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

    jQuery(document).mouseup(function (e) { // событие клика по веб-документу
        var div = jQuery("#mw_detailed"), // тут указываем ID элемента
            div1 = jQuery('#mw_detailes_issued');
        if (!div.is(e.target)
            && div.has(e.target).length === 0
            && !div1.is(e.target)
            && div1.has(e.target).length === 0) {
            div.hide();
            div1.hide();
            jQuery("#close-modal-window").hide();
            jQuery("#mw_container").hide();
        }
    });

    jQuery(document).ready(function () {
        goods = JSON.parse('<?= json_encode($goods)?>');
        console.log(goods);
        makeTh(jQuery("#thead"), data[0]);
        data.shift();
        fill_table(data);
        jQuery("#year1").mask("9999");
        jQuery("#year2").mask("9999");

        getIssuedData(jQuery("#issued_date_from").val(), jQuery("#issued_date_to").val());

        getGoodsAnalytic();

        getDataChart();
        getData(jQuery('#date_from').val(), jQuery("#date_to").val());
        jQuery('input[name="interval_radio"]').click(function () {
            if (this.id == "dates") {
                console.log("in dates");
                jQuery("#dates_div").show();
                jQuery("#months_div").hide();
            }
            if (this.id == "month") {
                console.log("in_months");
                jQuery("#dates_div").hide();
                jQuery("#months_div").show();
            }
        });

        jQuery("#go_to_old").click(function () {
            location.href = '/index.php?option=com_gm_ceiling&view=analytic_dealers';
        });

        jQuery('.show').click(function () {
            getDataChart();
        });

        jQuery('[name = "selection_type"]').change(function () {
            var date_from = jQuery('#date_from').val(),
                date_to = jQuery("#date_to").val()

            if (date_from <= date_to) {
                getData(date_from, date_to);
            } else {
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


        jQuery('.caption_div').click(function () {
            var containerId = jQuery(this).data('container'),
                container = jQuery('#' + containerId),
                i = jQuery(this).find('i');
            container.toggle();
            if (container.is(':visible')) {
                i.removeClass('fa-angle-down');
                i.addClass('fa-angle-up');
            } else {
                i.removeClass('fa-angle-up');
                i.addClass('fa-angle-down');
            }
        });


        jQuery('.goods_date').change(function () {
            getGoodsAnalytic();
        });

        jQuery('#goods_filter').change(function () {
            getGoodsAnalytic();
        });

        var goodsData = jQuery('#goods_filter').selectize({
            plugins: ['remove_button'],
            //persist: true,
            maxItems: null,
            valueField: 'id',
            labelField: 'name',
            sortField: [
                {field: 'id', direction: 'asc'}
            ],
            searchField: [
                'name'
            ],
            options: goods,
            render: {
                item: function (item, escape) {
                    return '<div><span class="name">' + item.name + '</span></div>';
                },
                option: function (item, escape) {
                    return '<div>' +
                        '<span class="label">' + item.name + '</span></div>';
                }
            },
            createFilter: function (input) {
                return jQuery.grep(Object.values(goodsData.options), function (value) {
                    return value.name.indexOf(jQuery('#goods_filter-selectized').val()) != -1;
                }).length == 0;
            },
            onDelete: function (values) {
                return true;
            },
            create: false
        })[0].selectize;


        function getGoodsAnalytic() {
            var dateFrom = jQuery('#g_date_from').val(),
                dateTo = jQuery('#g_date_to').val(),
                goodsId = (!empty(jQuery('#goods_filter').val())) ? jQuery('#goods_filter').val().join(',') : '';
            if (dateFrom <= dateTo) {
                jQuery.ajax({
                    url: "/index.php?option=com_gm_ceiling&task=analytic.getGoodsAnalytic",
                    async: false,
                    data: {
                        date_from: dateFrom,
                        dateTo: dateTo,
                        goods_id: goodsId
                    },
                    type: "POST",
                    dataType: "json",
                    success: function (data) {
                        jQuery('#goods_analytic_table > tbody').empty();
                        if (!empty(data)) {
                            data = JSON.parse(data);
                            var total_sum = 0,
                                total_cost = 0,
                                total_profit = 0;
                            jQuery.each(data, function (i, el) {
                                total_sum += +el.total_sum;
                                total_cost += +el.total_cost;
                                total_profit += +parseFloat(el.total_sum - el.total_cost).toFixed(2);
                                jQuery('#goods_analytic_table > tbody').append('<tr>' +
                                    '<td>' + el.name + '</td>' +
                                    '<td>' + el.total_count + ' ' + el.unit + '</td>' +
                                    '<td>' + el.total_sum + '</td>' +
                                    '<td>' + el.total_cost + '</td>' +
                                    '<td>' + parseFloat(el.total_sum - el.total_cost).toFixed(2) + '</td>' +
                                    '</tr>');
                            });
                            jQuery('#total_sum').text(parseFloat(total_sum).toFixed(2));
                            jQuery('#total_cost').text(parseFloat(total_cost).toFixed(2));
                            jQuery('#total_profit').text(parseFloat(total_profit).toFixed(2));
                        } else {
                            noty({
                                theme: 'relax',
                                layout: 'center',
                                timeout: 5000,
                                type: "warning",
                                text: "Ничего не найдено!"
                            });
                        }
                    },
                    dataType: "text",
                    timeout: 10000,
                    error: function () {
                        noty({
                            theme: 'relax',
                            layout: 'center',
                            timeout: 5000,
                            type: "error",
                            text: "Сервер не отвечает!"
                        });
                    }
                });
            } else {
                noty({
                    timeout: 2000,
                    theme: 'relax',
                    layout: 'center',
                    maxVisible: 5,
                    type: "error",
                    text: "Начальная дата не может быть больше конечной!"
                });
            }
        }

        jQuery('#table_issued').on('click', 'tr', function () {
            var dealerId = jQuery(this).data('dealer_id'),
                projectIds;
            if (!empty(issuedData)) {
                jQuery.each(issuedData, function (i, el) {
                    if (el.id == dealerId) {
                        projectIds = el.pr_ids;
                        return;
                    }
                });
            }
            if (!empty(projectIds)) {
                jQuery.ajax({
                    url: "index.php?option=com_gm_ceiling&task=projects.getProjectsWithRealisedGoodsByIds",
                    data: {
                        ids: projectIds
                    },
                    dataType: "json",
                    async: true,
                    type: 'POST',
                    success: function (data) {
                        projectsDetData = data;
                        var totalSums = {
                            project_sum: 0,
                            cost_sum: 0,
                            project_diff: 0,
                            canvases_sum: 0,
                            canvases_cost_sum: 0,
                            canvases_diff: 0,
                            components_sum: 0,
                            components_cost_sum: 0,
                            components_diff: 0
                        };
                        jQuery('#det_issued > tbody').empty();
                        jQuery.each(data, function (n, el) {
                            totalSums.project_sum += +parseFloat(el.project_sum).toFixed(2);
                            totalSums.cost_sum += +parseFloat(el.cost_sum).toFixed(2);
                            totalSums.project_diff += +parseFloat(el.project_sum - el.cost_sum).toFixed(2);
                            totalSums.canvases_sum += +parseFloat(el.canvases_sum).toFixed(2);
                            totalSums.canvases_cost_sum += +parseFloat(el.canvases_cost_sum).toFixed(2);
                            totalSums.canvases_diff += +parseFloat(el.canvases_sum - el.canvases_cost_sum).toFixed(2);
                            totalSums.components_sum += +parseFloat(el.components_sum).toFixed(2);
                            totalSums.components_cost_sum += +parseFloat(el.components_cost_sum).toFixed(2);
                            totalSums.components_diff += +parseFloat(el.components_sum - el.components_cost_sum).toFixed(2);
                            jQuery('#det_issued > tbody').append(
                                '<tr data-id="' + el.id + '"><div class="row">' +
                                '<td>' + el.id + '</td>' +
                                '<td>' + el.project_info + '</td>' +
                                '<td>' + parseFloat(el.project_sum).toFixed(2) + '</td>' +
                                '<td>' + parseFloat(el.cost_sum).toFixed(2) + '</td>' +
                                '<td>' + (el.project_sum - el.cost_sum).toFixed(2) + '</td>' +
                                '<td>' + parseFloat(el.canvases_sum).toFixed(2) + '</td>' +
                                '<td>' + parseFloat(el.canvases_cost_sum).toFixed(2) + '</td>' +
                                '<td>' + (el.canvases_sum - el.canvases_cost_sum).toFixed(2) + '</td>' +
                                '<td>' + parseFloat(el.components_sum).toFixed(2) + '</td>' +
                                '<td>' + parseFloat(el.components_cost_sum).toFixed(2) + '</td>' +
                                '<td>' + (el.components_sum - el.components_cost_sum).toFixed(2) + '</td>' +
                                '</div></tr>'
                            );
                        });

                        jQuery('#mw_detailes_issued').show();
                        jQuery('#mw_container').show();
                        jQuery('#close_mw').show();
                        jQuery.each(Object.keys(totalSums), function (n, el) {
                            jQuery('.' + el).text(parseFloat(totalSums[el]).toFixed(2));
                        });
                    },
                    error: function (data) {
                        n = noty({
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
        });

        jQuery('#det_issued').on('click', 'tr', function () {
            var projectId = jQuery(this).data('id'),
                prGoods = JSON.parse(projectsDetData[projectId].goods),
                trGoods = jQuery('.project_goods[data-project_id="' + projectId + '"]'),
                totalSum = 0,
                totalCost = 0,
                goodsHtml = '<div class="row center">' +
                    '<div class="col-md-3"><b>Наименование</b></div>' +
                    '<div class="col-md-1"><b>Кол-во</b></div>' +
                    '<div class="col-md-2"><b>Цена за ед.</b></div>' +
                    '<div class="col-md-2"><b>Цена закупки</b></div>' +
                    '<div class="col-md-1"><b>Сумма</b></div>' +
                    '<div class="col-md-2"><b>Себестоимость</b></div>' +
                    '<div class="col-md-1"><b>Разница</b></div>' +
                    '</div>';
            if (trGoods.length == 0) {
                jQuery.each(prGoods, function (i, g) {
                    totalSum += +parseFloat(g.count * g.sale_price).toFixed(2);
                    totalCost += +parseFloat(g.count * g.cost_price).toFixed(2);
                    goodsHtml += '<div class="row center">' +
                        '<div class="col-md-3">' + g.name + '</div>' +
                        '<div class="col-md-1">' + g.count + '</div>' +
                        '<div class="col-md-2">' + g.sale_price + '</div>' +
                        '<div class="col-md-2">' + g.cost_price + '</div>' +
                        '<div class="col-md-1">' + parseFloat(g.count * g.sale_price).toFixed(2) + '</div>' +
                        '<div class="col-md-2">' + parseFloat(g.count * g.cost_price).toFixed(2) + '</div>' +
                        '<div class="col-md-1">' + parseFloat(g.count * g.sale_price - g.count * g.cost_price).toFixed(2) + '</div>' +
                        '</div>';
                });
                goodsHtml += '<div class="row center">' +
                    '<div class="col-md-8"><b>Итого</b></div>' +
                    '<div class="col-md-1"><b>' + parseFloat(totalSum).toFixed(2) + '</b></div>' +
                    '<div class="col-md-2"><b>' + parseFloat(totalCost).toFixed(2) + '</b></div>' +
                    '<div class="col-md-1"><b>' + parseFloat(totalSum - totalCost).toFixed(2) + '</b></div>' +
                    '</div>';
                jQuery(this).after(
                    '<tr class="project_goods" data-project_id="' + projectId + '">' +
                    '<td colspan="11">' +
                    goodsHtml +
                    '</td>' +
                    '</tr>'
                );
            } else {
                trGoods.remove();
            }

        });
    });

    jQuery("#date_from").change(function () {
        var date_from = jQuery('#date_from').val(),
            date_to = jQuery("#date_to").val()

        if (date_from <= date_to) {
            getData(date_from, date_to);
        } else {
            noty({
                timeout: 2000,
                theme: 'relax',
                layout: 'center',
                maxVisible: 5,
                type: "error",
                text: "Начальная дата не может быть больше конечной!"
            });
        }
    });

    jQuery("#date_to").change(function () {
        var date_from = jQuery('#date_from').val(),
            date_to = jQuery("#date_to").val()

        if (date_from <= date_to) {
            getData(date_from, date_to);
        } else {
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

    jQuery("#issued_date_from").change(function () {
        var date_from = jQuery('#issued_date_from').val(),
            date_to = jQuery("#issued_date_to").val()

        if (date_from <= date_to) {
            getIssuedData(date_from, date_to);
        } else {
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

    jQuery("#issued_date_to").change(function () {
        var date_from = jQuery('#issued_date_from').val(),
            date_to = jQuery("#issued_date_to").val()

        if (date_from <= date_to) {
            getIssuedData(date_from, date_to);
        } else {
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

    function getData(date_from, date_to) {
        jQuery.ajax({
            url: "index.php?option=com_gm_ceiling&task=getDealersAnalyticData",
            data: {
                date_from: date_from,
                date_to: date_to,
                status: jQuery('[name = "selection_type"]:checked').val(),
                new: 1
            },
            dataType: "json",
            async: true,
            success: function (data) {
                console.log(data);
                makeTh(jQuery("#thead"), data[0]);
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

    function getIssuedData(date_from, date_to) {
        jQuery.ajax({
            url: "index.php?option=com_gm_ceiling&task=analytic.getIssuedData",
            data: {
                date_from: date_from,
                date_to: date_to
            },
            dataType: "json",
            async: true,
            success: function (data) {
                issuedData = data;
                if (!empty(data)) {
                    jQuery("#table_issued > tbody").empty();
                    var total_arr = {
                        name: 'Итого',
                        sum: 0,
                        cost_sum: 0,
                        diff: 0,
                        canv_sum: 0,
                        canv_cost: 0,
                        diff_canv: 0,
                        comp_sum: 0,
                        comp_cost: 0,
                        diff_comp: 0
                    };
                    jQuery.each(data, function (index, elem) {
                        total_arr.sum += +elem.sum;
                        total_arr.cost_sum += +elem.cost_sum;
                        total_arr.diff += (elem.sum - elem.cost_sum);
                        total_arr.canv_sum += +elem.canvases_sum;
                        total_arr.canv_cost += +elem.canvases_cost_sum;
                        total_arr.comp_sum += +elem.components_sum;
                        total_arr.comp_cost += +elem.components_cost_sum;
                        total_arr.diff_canv += (elem.canvases_sum - elem.canvases_cost_sum);
                        total_arr.diff_comp += (elem.components_sum - elem.components_cost_sum);

                        jQuery("#table_issued > tbody").append('<tr data-dealer_id = "' + elem.id + '"></tr>');
                        jQuery("#table_issued > tbody > tr:last").append('<td>' + elem.name + '</td>' +
                            '<td>' + parseFloat(elem.sum).toFixed(2) + '</td>' +
                            '<td>' + parseFloat(elem.cost_sum).toFixed(2) + '</td>' +
                            '<td>' + ((elem.sum - elem.cost_sum)).toFixed(2) + '</td>' +
                            '<td>' + parseFloat(elem.canvases_sum).toFixed(2) + '</td>' +
                            '<td>' + parseFloat(elem.canvases_cost_sum).toFixed(2) + '</td>' +
                            '<td>' + ((elem.canvases_sum - elem.canvases_cost_sum)).toFixed(2) + '</td>' +
                            '<td>' + parseFloat(elem.components_sum).toFixed(2) + '</td>' +
                            '<td>' + parseFloat(elem.components_cost_sum).toFixed(2) + '</td>' +
                            '<td>' + ((elem.components_sum - elem.components_cost_sum)).toFixed(2) + '</td>'
                        );

                    });
                    jQuery("#table_issued > tbody").append('<tr>' +
                        '<td><b>' + total_arr.name + '</b></td>' +
                        '<td><b>' + total_arr.sum.toFixed(2) + '</b></td>' +
                        '<td><b>' + total_arr.cost_sum.toFixed(2) + '</b></td>' +
                        '<td><b>' + total_arr.diff.toFixed(2) + '</b></td>' +
                        '<td><b>' + total_arr.canv_sum.toFixed(2) + '</b></td>' +
                        '<td><b>' + total_arr.canv_cost.toFixed(2) + '</b></td>' +
                        '<td><b>' + total_arr.diff_canv.toFixed(2) + '</b></td>' +
                        '<td><b>' + total_arr.comp_sum.toFixed(2) + '</b></td>' +
                        '<td><b>' + total_arr.comp_cost.toFixed(2) + '</b></td>' +
                        '<td><b>' + total_arr.diff_comp.toFixed(2) + '</b></td>' +
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
        jQuery.each(data, function (key, value) {
            row.append(jQuery("<th/ data-value = '" + key + "'>").text(value));
        });
        container.append(row);
    }

    function fill_table(data) {
        jQuery("#dealers_count").text(data.length);
        var ths = jQuery("#analytic > thead  th"), key = "", total = [], total_manf = {};
        jQuery('#analytic tbody').empty();
        for (let i = 0; i < data.length; i++) {
            jQuery('#analytic').append('<tr data-dealer_id = "' + data[i].dealer_id + '"></tr>');
            jQuery.each(ths, function (index, item) {
                key = jQuery(item).data('value');
                let val = (key != 'name' && key != 'squares_manf' && key != 'project_count' && key != 'calcs_count') ? parseFloat(data[i][key]).toFixed(2) : data[i][key];

                if (key == 'squares_manf' || key == 'name') {
                    jQuery('#analytic > tbody > tr:last').append('<td style="min-width: 200px; text-align: left;">' + val + '</td>');
                } else {
                    jQuery('#analytic > tbody > tr:last').append('<td style="text-align: right;">' + val + '</td>');
                }

                if (key == 'rest') {
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
                                total_manf[k] = temp[j][1] - 0;
                            } else {
                                total_manf[k] += temp[j][1] - 0;
                            }
                        }
                    }

                } else {
                    total[key] = (total[key]) ? +total[key] + +val : val;
                }
            });

        }

        for (var k in total_manf) {
            if (empty(total['squares_manf'])) {
                total['squares_manf'] = k + ': ' + total_manf[k].toFixed(2) + '<br>';
            } else {
                total['squares_manf'] += k + ': ' + total_manf[k].toFixed(2) + '<br>';
            }
        }

        if (Object.keys(total).length) {
            console.log('total', total);
            jQuery('#analytic').append('<tr></tr>');
            jQuery.each(ths, function (index, item) {
                key = jQuery(item).data('value');

                var value = (key != 'name' && key != 'squares_manf' && key != 'project_count' && key != 'calcs_count' && key != "rest") ? parseFloat(total[key]).toFixed(2) : total[key];
                //console.log(value);

                if (key == 'squares_manf' || key == 'name') {
                    jQuery('#analytic > tbody > tr:last').append('<td style="min-width: 200px; text-align: left;"><b>' + value + '</b></td>');
                } else {
                    jQuery('#analytic > tbody > tr:last').append('<td style="text-align: right;"><b>' + value + '</b></td>');
                }

            });
        }

        jQuery("#analytic tr").click(function () {
            var dealer_id = jQuery(this).data('dealer_id'), projects = '';

            data.forEach(function (elem) {
                if (elem.dealer_id == dealer_id) {
                    projects = elem.projects;
                }

            });
            jQuery.ajax({
                url: "index.php?option=com_gm_ceiling&task=projects.getProjectsInfo",
                data: {
                    projects: projects
                },
                dataType: "json",
                async: true,
                success: function (data) {
                    console.log(data);
                    create_detailed_table(data);
                    jQuery("#close_mw").show();
                    jQuery("#mw_container").show();
                    jQuery("#mw_detailed").show('slow');
                    jQuery("#detailed_analytic > tbody > tr").click(function () {
                        document.location.href = "/index.php?option=com_gm_ceiling&view=project&type=gmcalculator&subtype=calendar&id=" + jQuery(this).data('id');
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

        function create_detailed_table(data) {
            jQuery('#detailed_analytic tbody').empty();
            for (let i = 0; i < data.length; i++) {
                jQuery('#detailed_analytic').append('<tr data-id = ' + data[i].id + '></tr>');
                jQuery('#detailed_analytic > tbody > tr:last').append('<td>' + data[i].id + '</td>');
                jQuery('#detailed_analytic > tbody > tr:last').append('<td>' + data[i].project_info + '</td>');
                jQuery('#detailed_analytic > tbody > tr:last').append('<td>' + data[i].quadr + '</td>');
            }
        }
    }


</script>