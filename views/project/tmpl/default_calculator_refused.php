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

$canEdit = JFactory::getUser()->authorise('core.edit', 'com_gm_ceiling');
if (!$canEdit && JFactory::getUser()->authorise('core.edit.own', 'com_gm_ceiling')) {
	$canEdit = JFactory::getUser()->id == $this->item->created_by;
}

Gm_ceilingHelpersGm_ceiling::create_client_common_estimate($this->item->id);
Gm_ceilingHelpersGm_ceiling::create_common_estimate_mounters($this->item->id);
Gm_ceilingHelpersGm_ceiling::create_estimate_of_consumables($this->item->id);

/*_____________блок для всех моделей/models block________________*/ 
$calculationsModel = Gm_ceilingHelpersGm_ceiling::getModel('calculations');
$mountModel = Gm_ceilingHelpersGm_ceiling::getModel('mount');
$calculationform_model = Gm_ceilingHelpersGm_ceiling::getModel('calculationform');
$reserve_model = Gm_ceilingHelpersGm_ceiling::getModel('reservecalculation');
$client_model = Gm_ceilingHelpersGm_ceiling::getModel('client');
$clients_dop_contacts_model = Gm_ceilingHelpersGm_ceiling::getModel('clients_dop_contacts');
$components_model = Gm_ceilingHelpersGm_ceiling::getModel('components');
$canvas_model = Gm_ceilingHelpersGm_ceiling::getModel('canvases');

/*________________________________________________________________*/
$transport = Gm_ceilingHelpersGm_ceiling::calculate_transport($this->item->id);
$client_sum_transport = $transport['client_sum'];
$self_sum_transport = $transport['mounter_sum'];//идет в монтаж
$self_calc_data = [];
$self_canvases_sum = 0;
$self_components_sum = 0;
$self_mounting_sum = 0;
$project_self_total = 0;
$project_total = 0;
$project_total_discount = 0;
$total_square = 0;
$total_perimeter = 0;
$calculation_total_discount = 0;
$calculations = $calculationsModel->new_getProjectItems($this->item->id);

$del_flag = 0;
$project_total = $project_total + $client_sum_transport;
$project_total_discount = $project_total_discount  + $client_sum_transport;

?>
<link rel="stylesheet" href="/components/com_gm_ceiling/views/project/css/style.css" type="text/css" />
<script src="https://api-maps.yandex.ru/2.1/?lang=ru_RU" type="text/javascript"></script>

<?=parent::getButtonBack();?>
<h2 class="center">Просмотр проекта</h2>
<?php if ($this->item) : ?>

	<div class="container">
	  <div class="row">
		<div class="col-xl-6 item_fields">
			<h4>Информация по проекту</h4>
			<form id="form-client" action="/index.php?option=com_gm_ceiling&task=project.activate&type=calculator&subtype=refused" method="post" class="form-validate form-horizontal" enctype="multipart/form-data">
				<table class="table">
					<tr>
						<th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_PROJECT_CLIENT_ID'); ?></th>
						<td><?php echo $this->item->client_id; ?></td>
					</tr>
					<tr>
						<th><?php echo JText::_('COM_GM_CEILING_CLIENTS_CLIENT_CONTACTS'); ?></th>
						<td><?php echo $this->item->client_contacts; ?></td>
					</tr>	
					<tr>
						<th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_PROJECT_PROJECT_INFO'); ?></th>
						<td><?php echo $this->item->project_info; ?></td>
					</tr>
					<tr>
						<th><?php echo JText::_('COM_GM_CEILING_PROJECTS_PROJECT_CALCULATION_DATE'); ?></th>
						<td>
							<?php if($this->item->project_calculation_date == "0000-00-00 00:00:00") { ?>
								-
							<?php } else { ?>
								<?php $jdate = new JDate($this->item->project_calculation_date); ?>
								<?php echo $jdate->format('d.m.Y'); ?>
							<?php } ?>
						</td>
					</tr>
					<tr>
						<th><?php echo JText::_('COM_GM_CEILING_PROJECTS_PROJECT_CALCULATION_DAYPART'); ?></th>
						<td>
							<?php if($this->item->project_calculation_date == "0000-00-00 00:00:00") { ?>
								-
							<?php } else { ?>
								<?php $jdate = new JDate($this->item->project_calculation_date); ?>
								<?php echo $jdate->format('H:i'); ?>
							<?php } ?>
						</td>
					</tr>
					<?php if($this->type === "calculator" && $this->subtype === "refused"){ ?>
						<button type="submit" id="return_project" class="btn btn btn-success">
							Вернуть замерщику
						</button>
						<div class="project_activation" style="display: none;">
							<input name="project_id" value="<?php echo $this->item->id; ?>" type="hidden">
							<input name="type" value="calculator" type="hidden">
							<input name="subtype" value="refused" type="hidden">
							<input name="project_verdict" value="0" type="hidden">
							<input name="project_status" value="1" type="hidden">
						</div>
					<?php } ?>
				</table>
				
			</form>
            <?php include_once('components/com_gm_ceiling/views/project/project_notes.php'); ?>
		</div>
      </div>
	
<?php include_once('components/com_gm_ceiling/views/project/common_table.php'); ?>
	
<script type="text/javascript" src="/components/com_gm_ceiling/create_calculation.js"></script>
<script type="text/javascript" src="/components/com_gm_ceiling/views/project/common_table.js"></script>
<script type="text/javascript">
    var project_id = "<?php echo $this->item->id; ?>";
</script>
	
	<?php
else:
	echo JText::_('COM_GM_CEILING_ITEM_NOT_LOADED');
endif;
