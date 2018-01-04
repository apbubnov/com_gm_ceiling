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

$jinput = JFactory::getApplication()->input;
$type = $jinput->getString('type', NULL);

?>
<?php if ($this->item) : ?>
	<?php $model = Gm_ceilingHelpersGm_ceiling::getModel('projects'); ?>
	<?php $projects = $model->getClientItems($this->item->id); ?>
	<h2>Клиент: <?php echo $this->item->client_name; ?></h2>
		<div class="toolbar">
			<?php if($canEdit && $this->item->checked_out == 0): ?>
				<a class="btn btn-primary" href="<?php echo JRoute::_('index.php?option=com_gm_ceiling&view=clientform&type='.$type.'&id='.$this->item->id); ?>">Изменить клиента</a>
			<?php endif; ?>
		</div>
		<div class="row-fluid">
			<div class="span6 item_fields">
				<table class="table">
					<tr>
						<th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_CLIENT_CLIENT_NAME'); ?></th>
						<td><?php echo $this->item->client_name; ?></td>
					</tr>
					<tr>
						<th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_CLIENT_CLIENT_CONTACTS'); ?></th>
						<td><?php echo $this->item->client_contacts; ?></td>
					</tr>
				</table>
			</div>
		</div>
		<div id="ajax_add_project">
			<form id="form-project"
				  action="<?php echo JRoute::_('index.php?option=com_gm_ceiling&type='.$type.'&task=project.save'); ?>"
				  method="post" class="form-validate form-horizontal" enctype="multipart/form-data">
				
				<input type="hidden" name="jform[ajax]" value="1" />
				<input type="hidden" name="jform[client_id]" value="<?php echo $this->item->id; ?>" />
				<input type="hidden" name="jform[project_status]" value="0" />
				<input type="hidden" name="jform[state]" value="1" />
				<input type="hidden" name="jform[project_mounting_daypart]" value="1" />
				<h3 class="section_header">
					Создать проект <i class="fa fa-sort-desc"></i>
				</h3>
				<div class="section_content" style="">
					<div>
						<div><label id="jform_project_info-lbl" for="jform_project_info" class="">Адрес</label></div>
						<div><textarea aria-invalid="false" name="jform[project_info]" id="jform_project_info" placeholder="Адрес"></textarea></div>
					</div>
					<br>
					<div>
						<!--<button type="submit" class="validate btn btn-primary">
							Создать новый проект
						</button>-->
					</div>
				</div>

				<input type="hidden" name="option" value="com_gm_ceiling"/>
				<input type="hidden" name="task"
					   value="projectform.save"/>
				<?php echo JHtml::_('form.token'); ?>
			</form>
		</div>
		<?php if(count($projects)) { ?>
			<div class="client_projects">
				<h3 class="section_header">
					Проекты клиента
				</h3>
				<table class="table table-stripped">
					<thead>
						<tr>
							<th class='center'>
								<?php echo JText::_('COM_GM_CEILING_PROJECTS_ID'); ?>
							</th>
							<th class='center'>
								<?php echo JText::_('COM_GM_CEILING_PROJECTS_PROJECT_STATUS'); ?>
							</th>
							<th class='center'>
								<?php echo JText::_('COM_GM_CEILING_PROJECTS_PROJECT_MOUNTING_DATE'); ?>
							</th>
							<th class='center'>
								<?php echo JText::_('COM_GM_CEILING_PROJECTS_PROJECT_INFO'); ?>
							</th>
							<th class='center'>
								<?php echo JText::_('Потолки', 'a.project_calculations'); ?>
							</th>
							<th class='center'>
								<?php echo JText::_('COM_GM_CEILING_PROJECTS_CLIENT_ID'); ?>
							</th>
							<th class='center'>
								<?php echo JText::_('Телефоны'); ?>
							</th>
							<th class='center'>
								<?php echo JText::_('COM_GM_CEILING_PROJECTS_PROJECT_MOUNTER'); ?>
							</th>
							<?php if ($canEdit || $canDelete): ?>
								<th class="center">

								</th>
							<?php endif; ?>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($projects as $i => $item) : ?>
							<tr>
								<td class="center">
									<?php echo $item->id; ?>
								</td>
								<td class="center project_status<?php echo $item->project_status_id; ?>">
									<i class="fa fa-circle" aria-hidden="true" title="<?php echo $item->project_status; ?>"></i>
								</td>
								<td class="center">
									<?php if($item->project_mounting_date == "0000-00-00") { ?>
										-
									<?php } else { ?>
										<?php echo $item->project_mounting_date; ?>
									<?php } ?>
								</td>
								<td class="center">
									<a href="<?php echo JRoute::_('index.php?option=com_gm_ceiling&view=project&type='.$type.'&id='.(int) $item->id); ?>">
										<?php echo $this->escape($item->project_info); ?>
									</a>
								</td>
								<td class="center">
									<?php echo $item->project_calculations; ?>
								</td>
								<td class="center">
									<?php echo $item->client_id; ?>
								</td>
								<td class="center">
									<?php echo $item->client_contacts; ?>
								</td>
								<?php if($item->project_mounter) { ?>
									<td class="center">
										<?php echo $item->project_mounter; ?>
									</td>						
								<?php } else { ?>
									<td class="center">
										-
									</td>
								<?php } ?>
								<td class="right">
									<a href="<?php echo JRoute::_('index.php?option=com_gm_ceiling&view=project&type='.$type.'&id='.(int) $item->id); ?>" class="btn" type="button"><i class="icon-eye" ></i> Посмотреть</i></a>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		<?php } ?>
	<?php
else:
	echo JText::_('COM_GM_CEILING_ITEM_NOT_LOADED');
endif;
