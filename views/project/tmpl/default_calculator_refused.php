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
							Вернуть а стадию замера
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
