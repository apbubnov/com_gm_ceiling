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

?>

<style type="text/css">
    /*td {
        vertical-align: middle;
        width: calc(14% - 1px);
    }
    th {
        width: calc(14% - 2px);
    }
    table {
        border-collapse: collapse;
        border: 1px solid black;
    }
    tbody, thead, tfoot, tr {
        display: block;
    }
    td, th {
        padding: 5px 10px;
        display: inline-block;
        vertical-align: top;
        border-top: none !important;
        border-bottom: none !important; 
    }
    thead {
        border-bottom: 1px solid black;
    }
    tbody {
        max-height: 600px;
        overflow-x: hidden;
        overflow-y: auto;
    }
    tbody tr {
        border-bottom: 1px dashed darkgray;
    }*/
</style>

С <input type="date" class="" id="calendarFrom" value="<?php echo '0000-00-00';?>">
По <input type="date" class="" id="calendarTo" value="<?php echo date('Y-m-d');?>">
<button type="button" class="btn btn-primary" id="btn_show">Показать</button>
<hr>
<table class="table table-striped table-bordered table-sm" cellspacing="0" width="100%" id="projectList">
	<thead>
		<tr>
			<th>№</th>
			<th>ID проекта</th>
			<th>Дата производства</th>
			<th>ID потолка</th>
			<th>Сумма по полотнам</th>
			<th>Дилер</th>
            <th>Дата создания</th>
		</tr>
	</thead>
	<tbody id="projectList_tbody"></tbody>
</table>
<h4 id="h4_common_sum"></h4>

<link href="/libraries/MDB-Free_4.7.1/css/addons/datatables.min.css" rel="stylesheet">
<script type="text/javascript" src="/libraries/MDB-Free_4.7.1/js/mdb.min.js"></script>
<script type="text/javascript" src="/libraries/MDB-Free_4.7.1/js/addons/datatables.min.js"></script>

<script type="text/javascript">
    jQuery(document).ready(function() {
        getData();
    });

    document.getElementById('btn_show').onclick = function() {
        getData();
    };
    
	function getData() {
        jQuery.ajax({
            type: 'POST',
            url: "index.php?option=com_gm_ceiling&task=projects.getProjectsForBuh",
            data: {
                dateFrom: jQuery('#calendarFrom').val(),
                dateTo: jQuery('#calendarTo').val()
            },
            success: function(data){
                //console.log(data);
                showTableData(data);
            },
            dataType:"json",
            async: false,
            timeout: 10000,
            error: function(data){
                var n = noty({
                    timeout: 2000,
                    theme: 'relax',
                    layout: 'center',
                    maxVisible: 5,
                    type: "error",
                    text: "Ошибка!"
                });
            }
        });
    }

    function showTableData(data) {
        var tr, td, com_sum = 0,
            table_body_elem = document.getElementById('projectList_tbody');
        table_body_elem.innerHTML = '';
        for (var i = 0; i < data.length; i++) {
            tr = table_body_elem.insertRow();
            td = tr.insertCell();
            td.innerHTML = i + 1;
            td = tr.insertCell();
            td.innerHTML = data[i].project_id;
            td = tr.insertCell();
            td.innerHTML = data[i].date_production;
            td = tr.insertCell();
            td.innerHTML = data[i].calc_id;
            td = tr.insertCell();
            td.innerHTML = data[i].canvases_sum;
            td = tr.insertCell();
            td.innerHTML = data[i].dealer_name;
            td = tr.insertCell();
            td.innerHTML = data[i].date_created;
            if (!empty(data[i].canvases_sum)) {
                com_sum += data[i].canvases_sum-0;
            }
        }
        document.getElementById('h4_common_sum').innerHTML = 'Общая сумма: '+com_sum.toFixed(2);

        jQuery('#projectList').DataTable({
            "scrollY": "50vh",
            "scrollCollapse": true,
            "paging": false,
            "ordering": false
        });
        jQuery('.dataTables_length').addClass('bs-select');
    }
</script>
