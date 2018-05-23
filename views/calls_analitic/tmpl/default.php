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

$outcoming_bad = json_encode($model_calls->selectCallHistoryByStatus(1));
$outcoming_good = json_encode($model_calls->selectCallHistoryByStatus(2));
$incoming = json_encode($model_calls->selectCallHistoryByStatus(3));

echo parent::getButtonBack();

?>
<h2>Аналитика звонков</h2>
<div class="analitic-actions">
	Выбрать с <input type="date" id="date1"> по <input type="date" id="date2"> <button type="button" class="btn btn-primary" id="show_all">Показать всё</button>
</div>
<table class="small_table table-striped table_cashbox one-touch-view">
	<tbody>
		<tr id="s1"><th>Исходящие недозвоны</th><td id="outcoming_bad"></td></tr>
		<tr id="s2"><th>Исходящие дозвоны</th><td id="outcoming_good"></td></tr>
		<tr id="s3"><th>Входящие звонки</th><td id="incoming"></td></tr>
	</tbody>
</table>
<table class="small_table table-striped table_cashbox one-touch-view">
	<tbody>
		<tr id="s1"><th>Исходящие недозвоны</th><td id="outcoming_bad"></td></tr>
		<tr id="s2"><th>Исходящие дозвоны</th><td id="outcoming_good"></td></tr>
		<tr id="s3"><th>Входящие звонки</th><td id="incoming"></td></tr>
	</tbody>
</table>
<script type="text/javascript">
	var outcoming_bad = JSON.parse('<?php echo $outcoming_bad; ?>');
	var outcoming_good = JSON.parse('<?php echo $outcoming_good; ?>');
	var incoming = JSON.parse('<?php echo $incoming; ?>');

	var td_outcoming_bad = document.getElementById('outcoming_bad');
	var td_outcoming_good = document.getElementById('outcoming_good');
	var td_incoming = document.getElementById('incoming');

	td_outcoming_bad.innerHTML = outcoming_bad.length;
	td_outcoming_good.innerHTML = outcoming_good.length;
	td_incoming.innerHTML = incoming.length;

	var arr_s1 = [], arr_s2 = [], arr_s3 = [];

	document.getElementById('date1').onchange = show;
	document.getElementById('date2').onchange = show;

	function show() {
		arr_s1 = [];
		arr_s2 = [];
		arr_s3 = [];
		var date1 = document.getElementById('date1').value;
		var date2 = document.getElementById('date2').value;
		if (date2 == '')
		{
			date2 = '<?php echo date("Y-m-d H-i-s"); ?>' + ' 23:59:59';
		}
		else
		{
			date2 += ' 23:59:59';
		}
		if (date1 == '')
		{
			date1 = '00-00-00 00:00:00';
		}
		else
		{
			date1 += ' 00:00:00';
		}

		for (var i = outcoming_bad.length; i--;)
		{
			if (outcoming_bad[i].date_time >= date1 && outcoming_bad[i].date_time <= date2)
			{
				arr_s1.push(outcoming_bad[i]);
			}
		}
		for (var i = outcoming_good.length; i--;)
		{
			if (outcoming_good[i].date_time >= date1 && outcoming_good[i].date_time <= date2)
			{
				arr_s2.push(outcoming_good[i]);
			}
		}
		for (var i = incoming.length; i--;)
		{
			if (incoming[i].date_time >= date1 && incoming[i].date_time <= date2)
			{
				arr_s3.push(incoming[i]);
			}
		}
		td_outcoming_bad.innerHTML = arr_s1.length;
		td_outcoming_good.innerHTML = arr_s2.length;
		td_incoming.innerHTML = arr_s3.length;
	}


</script>
