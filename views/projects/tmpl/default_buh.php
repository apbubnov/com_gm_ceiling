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
    td {
        vertical-align: middle;
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
        width: calc(16% - 1px);
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
    }
</style>

С <input type="date" class="" id="calendarFrom" value="<?php echo '0000-00-00';?>">
По <input type="date" class="" id="calendarTo" value="<?php echo date('Y-m-d');?>">
<button type="button" class="btn btn-primary" id="btn_show">Показать</button>
<hr>
<table class="table" id="projectList">
	<thead>
		<tr>
			<th>№</th>
			<th>ID проекта</th>
			<th>Дата</th>
			<th>ID потолка</th>
			<th>Сумма</th>
			<th>Дилер</th>
		</tr>
	</thead>
	<tbody id="projectList_tbody"></tbody>
</table>
<h4 id="h4_common_sum"></h4>


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
            td.innerHTML = data[i].date;
            td = tr.insertCell();
            td.innerHTML = data[i].calc_id;
            td = tr.insertCell();
            td.innerHTML = data[i].sum;
            td = tr.insertCell();
            td.innerHTML = data[i].dealer_name;
            td = tr.insertCell();
            if (!empty(data[i].sum)) {
                com_sum += data[i].sum-0;
            }
        }
        document.getElementById('h4_common_sum').innerHTML = 'Общая сумма: '+com_sum;
    }
</script>
