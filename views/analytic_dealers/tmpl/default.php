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
$model = Gm_ceilingHelpersGm_ceiling::getModel('Analytic_Dealers');
$data = json_encode($model->getData());
?>
<table id = "analytic" class="analitic-table">
	<thead class = "caption-style-tar">
		<th>
			Дилер
		</th>
		<th>
			Кол-во проектов
		</th>
		<th>
			Кол-во потолков
		</th>
		<th>
			Квадратура
		</th>
		<th>
			Стоимость
		</th>
		<th>
			Себестоимость
		</th>
		<th>
			Стоимость комплектуюших
		</th>
		<th>
			Себестоимость комплектующих
		</th>
	</thead>
	<tbody>
	</tbody>
</table>
<script type="text/javascript">
	var data = JSON.parse('<?php echo $data;?>');
	console.log(data);
	jQuery('#analytic tbody').empty();
	for(let i = 0;i<data.length;i++){
		jQuery('#analytic').append('<tr></tr>');
		for(let j=0;j<Object.keys(data[i]).length;j++){
			if(Object.keys(data[i])[j] != 'projects' && Object.keys(data[i])[j] != 'id')
			jQuery('#analytic > tbody > tr:last').append('<td>'+data[i][Object.keys(data[i])[j]] +'</td>');
		}
	}
	

</script>