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

$model_calls = Gm_ceilingHelpersGm_ceiling::getModel('callback');

$user = JFactory::getUser();
$dealerId = $user->dealer_id;
$outcoming_bad = json_encode($model_calls->selectCallHistoryByStatus(1, $user->dealer_id));
$outcoming_good = json_encode($model_calls->selectCallHistoryByStatus(2, $user->dealer_id));
$incoming = json_encode($model_calls->selectCallHistoryByStatus(3, $user->dealer_id));
$presentation = json_encode($model_calls->selectCallHistoryByStatus(4, $user->dealer_id));
$lid = json_encode($model_calls->selectCallHistoryByStatus(5, $user->dealer_id));

echo parent::getButtonBack();

?>
<style type="text/css">
	.small_table {
		cursor: pointer;
	}
	.small_table tbody tr:hover {
		background: #ddeeff;
	}
</style>
<h2>Аналитика звонков</h2>
<button type="button" class="btn btn-primary" id="show_all">Показать за всё время</button>
<div class="analitic-actions">
	Выбрать с <input type="date"  class="choose_date" id="date1" value="<?= date('Y-m-d'); ?>"> по <input type="date"  class="choose_date" id="date2"  value="<?= date('Y-m-d'); ?>">
</div>
<table class="small_table table-striped table_cashbox one-touch-view" id="common_table">
    <thead>
        <th>
            Статус
        </th>
        <th>
            Кол-во по менеджерам
        </th>
        <th>
            Общеее кол-во
        </th>
    </thead>
	<tbody>
	</tbody>
</table>
<hr>
<table class="small_table table-striped table_cashbox one-touch-view">
	<thead>
		<th>Дата</th>
		<th>Клиент</th>
		<th>Менеджер</th>
	</thead>
	<tbody id="info">
	</tbody>
</table>
<script type="text/javascript">
    jQuery(document).ready(function(){
        getData();
        jQuery(".click_tr").click(function(){
            var status_id = jQuery(this).data('id');
            jQuery.ajax({
                type: 'POST',
                url: "index.php?option=com_gm_ceiling&task=callback.getCallsHistory",
                data: {
                    dealerId: '<?php echo $dealerId;?>',
                    dateFrom: jQuery('#date1').val(),
                    dateTo: jQuery('#date2').val(),
                    statusId: status_id
                },
                success: function(data){
                    console.log(data);
                    fillDetailedTable(data);
                },
                dataType:"json",
                async: false,
                timeout: 10000,
                error: function(data){
                    console.log(data)
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
        });

        jQuery('.choose_date').change(function(){
            getData();
        });
    });
    function getData(){
        jQuery.ajax({
            type: 'POST',
            url: "index.php?option=com_gm_ceiling&task=callback.getCallsAnalytic",
            data: {
                dateFrom: jQuery('#date1').val(),
                dateTo: jQuery('#date2').val(),
                dealerId: '<?php echo $dealerId;?>'
            },
            success: function(data){
                console.log(data);
                showTableData(data);
            },
            dataType:"json",
            async: false,
            timeout: 10000,
            error: function(data){
                console.log(data)
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
        var table = jQuery('#common_table > tbody'),
            common_count = 0;
        table.empty();
        for(var i=0;i<data.length;i++){
            table.append('<tr class="click_tr" data-id="'+data[i].id+'"></tr>');
            var tr = jQuery('#common_table > tbody > tr:last');
            var manager_info = JSON.parse(data[i].manager_count);

            var common_count_by_status = 0,
                count_str = '';

            for(var j=0;j<manager_info.length;j++){
                count_str += '<div class="row" ><div class="col-md-6">'+manager_info[j].manager+':</div><div class="col-md-3">Звонков-'+manager_info[j].count+'</div><div class="col-md-3">Замеров-'+manager_info[j].measures_count+'</div> </div>';
                common_count_by_status += +manager_info[j].count;
            }
            common_count += common_count_by_status;
            tr.append('<td>'+data[i].title+'</td><td>'+count_str+'</td><td>'+common_count_by_status+'</td>');
        }
        table.append('<tr><td colspan=2><b>Итого</b></td><td>'+common_count+'</td></tr>')
    }

    function fillDetailedTable(data) {
        jQuery("#info").empty();
        for(var i=0;i<data.length;i++){
            jQuery("#info").append('<tr></tr>');
            jQuery("#info > tr:last").append('<td>'+data[i].change_time+'</td><td>'+data[i].client_name+'</td><td>'+data[i].manager_name+'</td>')
        }
    }





</script>
