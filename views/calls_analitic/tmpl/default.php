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
	Выбрать с <input type="date" id="date1" value="<?= date('Y-m-d'); ?>"> по <input type="date" id="date2"  value="<?= date('Y-m-d'); ?>">
</div>
<table class="small_table">
	<tbody>
		<tr id="s1"><td>Исходящие недозвоны</td><td id="outcoming_bad"></td></tr>
		<tr id="s2"><td>Исходящие дозвоны</td><td id="outcoming_good"></td></tr>
		<tr id="s3"><td>Входящие звонки</td><td id="incoming"></td></tr>
		<tr id="s4"><td>Презентация</td><td id="presentation"></td></tr>
		<tr id="s5"><td>Лид</td><td id="lid"></td></tr>
	</tbody>
	<tfoot>
		<tr><td>Итого</td><td id="sum"></td></tr>
	</tfoot>
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
	var outcoming_bad = JSON.parse('<?php echo $outcoming_bad; ?>');
	var outcoming_good = JSON.parse('<?php echo $outcoming_good; ?>');
	var incoming = JSON.parse('<?php echo $incoming; ?>');
	var presentation = JSON.parse('<?php echo $presentation; ?>');
	var lid = JSON.parse('<?php echo $lid; ?>');

	var td_outcoming_bad = document.getElementById('outcoming_bad');
	var td_outcoming_good = document.getElementById('outcoming_good');
	var td_incoming = document.getElementById('incoming');
	var td_presentation = document.getElementById('presentation');
	var td_lid = document.getElementById('lid');
	var td_sum = document.getElementById('sum');

	var info = document.getElementById('info');

	var arr_s1 = [], arr_s2 = [], arr_s3 = [], arr_s4 = [], arr_s5 = [];

	document.getElementById('date1').onchange = show;
	document.getElementById('date2').onchange = show;
	document.getElementById('show_all').onclick = function() {
		document.getElementById('date1').value = '';
		document.getElementById('date2').value = '';
		show();
	};

	show();

	document.getElementById('s1').onclick = sClick;
	document.getElementById('s2').onclick = sClick;
	document.getElementById('s3').onclick = sClick;
	document.getElementById('s4').onclick = sClick;
	document.getElementById('s5').onclick = sClick;

	function sClick() {
		var tr, td, arr = window[String('arr_'+this.id)];
		info.innerHTML = '';
		jQuery('.small_table>tbody>tr').css('background', '');
		jQuery('.small_table>tbody>tr').css('color', '');
		jQuery(this).css('background', '#414099');
		jQuery(this).css('color', '#ffffff');
		for (var i = arr.length; i--;) {
			tr = info.insertRow();
			tr.setAttribute('data-clientId', arr[i].client_id-0);
			td = tr.insertCell();
            td.innerHTML = arr[i].change_time;
            td = tr.insertCell();
            td.innerHTML = arr[i].client_name;
            td = tr.insertCell();
            td.innerHTML = arr[i].manager_name;
		}
		var trs_calls = info.getElementsByTagName('tr');
		for (var i = trs_calls.length; i--;) {
			trs_calls[i].onclick = function() {
				location.href = 'index.php?option=com_gm_ceiling&view=clientcard&id='+this.getAttribute('data-clientId');
			}
		}
	}

	function show() {
		arr_s1 = [];
		arr_s2 = [];
		arr_s3 = [];
		var date1 = document.getElementById('date1').value;
		var date2 = document.getElementById('date2').value;
		if (date2 == '') {
			date2 = '<?php echo date("Y-m-d"); ?>' + ' 23:59:59';
		} else {
			date2 += ' 23:59:59';
		}
		if (date1 == '') {
			date1 = '00-00-00 00:00:00';
		} else {
			date1 += ' 00:00:00';
		}

		for (var i = outcoming_bad.length; i--;) {
			if (outcoming_bad[i].change_time >= date1 && outcoming_bad[i].change_time <= date2) {
				arr_s1.push(outcoming_bad[i]);
			}
		}
		for (var i = outcoming_good.length; i--;) {
			if (outcoming_good[i].change_time >= date1 && outcoming_good[i].change_time <= date2) {
				arr_s2.push(outcoming_good[i]);
			}
		}
		for (var i = incoming.length; i--;) {
			if (incoming[i].change_time >= date1 && incoming[i].change_time <= date2) {
				arr_s3.push(incoming[i]);
			}
		}
		for (var i = presentation.length; i--;) {
			if (presentation[i].change_time >= date1 && presentation[i].change_time <= date2) {
				arr_s4.push(presentation[i]);
			}
		}
		for (var i = lid.length; i--;) {
			if (lid[i].change_time >= date1 && lid[i].change_time <= date2) {
				arr_s5.push(lid[i]);
			}
		}
		td_outcoming_bad.innerHTML = arr_s1.length;
		td_outcoming_good.innerHTML = arr_s2.length;
		td_incoming.innerHTML = arr_s3.length;
		td_presentation.innerHTML = arr_s4.length;
		td_lid.innerHTML = arr_s5.length;
		td_sum.innerHTML = arr_s1.length + arr_s2.length + arr_s3.length + arr_s4.length + arr_s5.length;
	}


</script>
