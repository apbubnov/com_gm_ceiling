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

$user       = JFactory::getUser();
$userId     = $user->get('id');
$listOrder  = $this->state->get('list.ordering');
$listDirn   = $this->state->get('list.direction');
$canCreate  = $user->authorise('core.create', 'com_gm_ceiling') && file_exists(JPATH_COMPONENT . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . 'forms' . DIRECTORY_SEPARATOR . 'projectform.xml');
$canEdit    = $user->authorise('core.edit', 'com_gm_ceiling') && file_exists(JPATH_COMPONENT . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . 'forms' . DIRECTORY_SEPARATOR . 'projectform.xml');
$canCheckin = $user->authorise('core.manage', 'com_gm_ceiling');
$canChange  = $user->authorise('core.edit.state', 'com_gm_ceiling');
$canDelete  = $user->authorise('core.delete', 'com_gm_ceiling');
		$app  = JFactory::getApplication();
		echo $app->input->getString('type', NULL);
?>
<h2>Проекты</h2>
<form action="<?php echo JRoute::_('index.php?option=com_gm_ceiling&view=projects'); ?>" method="post"
      name="adminForm" id="adminForm">
	  <div class="row-fluid toolbar">
		<div class="span3">
			<?php if ($canCreate) : ?>
				<a href="<?php echo JRoute::_('index.php?option=com_gm_ceiling&task=projectform.edit&id=0', false, 2); ?>"
				   class="btn btn-success"><i
						class="icon-plus"></i>
					Добавить проект
				</a>
			<?php endif; ?>
		</div>
		<div class="span9">
			<?php echo JLayoutHelper::render('default_filter', array('view' => $this), dirname(__FILE__)); ?>
		</div>
	  </div>
	<table class="table table-striped one-touch-view" id="projectList">
		<thead>
			<tr>
				<th class='center'>
					<?php echo JHtml::_('grid.sort',  'COM_GM_CEILING_PROJECTS_ID', 'a.id', $listDirn, $listOrder); ?>
				</th>
				<th class='center'>
					<?php echo JHtml::_('grid.sort',  'COM_GM_CEILING_PROJECTS_PROJECT_STATUS', 'a.project_status', $listDirn, $listOrder); ?>
				</th>
				<th class='center'>
					<?php echo JHtml::_('grid.sort',  'COM_GM_CEILING_PROJECTS_PROJECT_MOUNTING_DATE', 'a.project_mounting_date', $listDirn, $listOrder); ?>
				</th>
				<th class='center'>
					<?php echo JHtml::_('grid.sort',  'COM_GM_CEILING_PROJECTS_PROJECT_MOUNTING_DAYPART', 'a.project_mounting_daypart', $listDirn, $listOrder); ?>
				</th>
				<th class='center'>
					<?php echo JHtml::_('grid.sort',  'COM_GM_CEILING_PROJECTS_PROJECT_INFO', 'a.project_info', $listDirn, $listOrder); ?>
				</th>
				<th class='center'>
					<?php echo JHtml::_('grid.sort',  'Потолки', 'a.project_calculations', $listDirn, $listOrder); ?>
				</th>
				<th class='center'>
					<?php echo JHtml::_('grid.sort',  'COM_GM_CEILING_PROJECTS_CLIENT_ID', 'a.client_id', $listDirn, $listOrder); ?>
				</th>
				<th class='center'>
					<?php echo JHtml::_('grid.sort',  'Телефоны', 'a.client_contacts', $listDirn, $listOrder); ?>
				</th>
				<th class='center'>
					<?php echo JHtml::_('grid.sort',  'COM_GM_CEILING_PROJECTS_PROJECT_MOUNTER', 'a.project_mounter', $listDirn, $listOrder); ?>
				</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="<?php echo isset($this->items[0]) ? count(get_object_vars($this->items[0])) : 10; ?>">
					<?php echo $this->pagination->getListFooter(); ?>
				</td>
			</tr>
		</tfoot>
		<tbody>
			<?php foreach ($this->items as $i => $item) : ?>
				<?php $canEdit = $user->authorise('core.edit', 'com_gm_ceiling'); ?>

				<?php if (!$canEdit && $user->authorise('core.edit.own', 'com_gm_ceiling')): ?>
					<?php $canEdit = JFactory::getUser()->id == $item->created_by; ?>
				<?php endif; ?>

				<tr data-href="<?php echo JRoute::_('index.php?option=com_gm_ceiling&view=project&id='.(int) $item->id); ?>">
					<td class="center one-touch">
						<?php echo $item->id; ?>
					</td>
					<td class="center project_status<?php echo $item->project_status_id; ?> one-touch">
						<i class="fa fa-circle" aria-hidden="true" title="<?php echo $item->project_status; ?>"></i>
					</td>
					<td class="center one-touch">
						<?php if($item->project_mounting_date == "0000-00-00") { ?>
							-
						<?php } else { ?>
							<?php echo $item->project_mounting_date; ?>
						<?php } ?>
					</td>
					<td class="center one-touch">
						<?php echo $item->project_mounting_daypart; ?>
					</td>
					<td class="center one-touch">
						<?php echo $this->escape($item->project_info); ?>
					</td>
					<td class="center one-touch">
						<?php echo $item->project_calculations; ?>
					</td>
					<td class="center one-touch">
						<?php echo $item->client_id; ?>
					</td>
					<td class="center one-touch">
						<?php echo $item->client_contacts; ?>
					</td>
					<?php if($item->project_mounter) { ?>
						<td class="center one-touch">
							<?php echo $item->project_mounter; ?>
						</td>						
					<?php } else { ?>
						<td class="center one-touch">
							-
						</td>
					<?php } ?>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

	<input type="hidden" name="task" value=""/>
	<input type="hidden" name="boxchecked" value="0"/>
	<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>"/>
	<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>"/>
	<?php echo JHtml::_('form.token'); ?>
</form>

<?php if($canDelete) : ?>
<script type="text/javascript">

	jQuery(document).ready(function () {
		jQuery('.delete-button').click(deleteItem);
	});

	function deleteItem() {

		if (!confirm("<?php echo JText::_('COM_GM_CEILING_DELETE_MESSAGE'); ?>")) {
			return false;
		}
	}
</script>
<?php endif; ?>
