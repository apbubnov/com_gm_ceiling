<?php
/**
 * @version    CVS: 1.0.0
 * @package    Com_Gm_ceiling
 * @author     Mikhail  <vms@itctl.ru>
 * @copyright  2016 Mikhail 
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;

JHtml::_('behavior.keepalive');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
/* JHtml::_('formbehavior.chosen', 'select'); */
$user    = JFactory::getUser();
$userId     = $user->get('id');

$model = Gm_ceilingHelpersGm_ceiling::getModel('Projectfinished');
$projects = $model->GetData();

// id для аякса изменения статуса прочтения
$masID = [];
foreach ($projects as $value) {
	if ($value->read_by_chief == 0) {
		array_push($masID, $value->id);
	}
}
$server_name = $_SERVER['SERVER_NAME'];
?>

<link rel="stylesheet" href="components/com_gm_ceiling/views/projectfinished/tmpl/css/style.css" type="text/css" />

<h2 class="center" style="padding-bottom: 1em;">Завершенные монтажи</h2>

<div id="project_container">
	<table id="finished_mounting" class="table1">
		<tr>
			<th>№ Проекта</th>
			<th>Дата завершения монтажа</th>
			<th>Монтажная бригада</th>
			<th>Примечание</th>
			<th>Адрес</th>
		</tr>
		<?php foreach ($projects as $value) {
				if ($user->dealer_id == 1) { ?>
					<tr onclick="location.replace('/index.php?option=com_gm_ceiling&view=projectform&type=gmchief&id=<?php echo $value->id; ?>');">
				<?php } else {?>
					<tr onclick="location.replace('/index.php?option=com_gm_ceiling&view=projectform&type=chief&id=<?php echo $value->id; ?>');">
				<?php }?>
					<td><?php echo $value->id; ?></td>
					<td><?php echo $value->project_mounting_end; ?></td>
					<td><?php echo $value->name; ?></td>
					<td><?php
							if ($user->dealer_id == 1) {
								echo $value->gm_mounter_note;
							} else {
								echo $value->mounter_note;
							}
						?>
					</td>
					<td><?php echo $value->project_info; ?></td>
				</tr>

		<?php } ?>
	</table>
</div>

<script type='text/javascript'>

	jQuery(document).ready(function () {
		// изменение статусов прочтения
		jQuery.ajax({
			type: 'POST',
			url: "/index.php?option=com_gm_ceiling&task=projectfinished.ChangeStatusOfRead",
			dataType: 'json',
			data: {
				masID: <?php echo json_encode($masID); ?>,
			},
			success: function(data) {
			},
			error: function(data) {
				console.log(data);
				console.log("ошибка");
			}
		});
	});	

</script>