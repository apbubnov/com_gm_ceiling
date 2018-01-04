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
$canCreate  = $user->authorise('core.create', 'com_gm_ceiling') && file_exists(JPATH_COMPONENT . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . 'forms' . DIRECTORY_SEPARATOR . 'calculationform.xml');
$canEdit    = $user->authorise('core.edit', 'com_gm_ceiling') && file_exists(JPATH_COMPONENT . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . 'forms' . DIRECTORY_SEPARATOR . 'calculationform.xml');
$canCheckin = $user->authorise('core.manage', 'com_gm_ceiling');
$canChange  = $user->authorise('core.edit.state', 'com_gm_ceiling');
$canDelete  = $user->authorise('core.delete', 'com_gm_ceiling');
?>
<h2>Расчеты, не назначенные на проекты</h2>
<form action="<?php echo JRoute::_('index.php?option=com_gm_ceiling&view=calculations'); ?>" method="post"
      name="adminForm" id="adminForm">
	<div class="row-fluid toolbar">
		<div class="span3">
			<?php if ($canCreate) : ?>
				<a href="<?php echo JRoute::_('index.php?option=com_gm_ceiling&task=calculationform.edit&id=0', false, 2); ?>" class="btn btn-success">
					<i class="icon-plus"></i> Рассчитать потолок
				</a>
			<?php endif; ?>
		</div>
		<div class="span9">
			<?php echo JLayoutHelper::render('default_filter', array('view' => $this), dirname(__FILE__)); ?>
		</div>
	</div>
	<table class="table table-striped" id="calculationList">
		<thead>
			<tr>
				<th class=''>
					<?php echo JHtml::_('grid.sort',  'COM_GM_CEILING_CALCULATIONS_CALCULATION_TITLE', 'a.calculation_title', $listDirn, $listOrder); ?>
				</th>
				<th class=''>
					<?php echo JHtml::_('grid.sort',  'COM_GM_CEILING_CALCULATIONS_N1', 'a.n1', $listDirn, $listOrder); ?>
				</th>
				<th class=''>
					<?php echo JHtml::_('grid.sort',  'COM_GM_CEILING_CALCULATIONS_N2', 'a.n2', $listDirn, $listOrder); ?>
				</th>
				<th class=''>
					<?php echo JHtml::_('grid.sort',  'COM_GM_CEILING_CALCULATIONS_N3', 'a.n3', $listDirn, $listOrder); ?>
				</th>
				<th class='center'>
					<?php echo JHtml::_('grid.sort',  'COM_GM_CEILING_CALCULATIONS_N4', 'a.n4', $listDirn, $listOrder); ?>
				</th>
				<th class='center'>
					<?php echo JHtml::_('grid.sort',  'COM_GM_CEILING_CALCULATIONS_N5', 'a.n5', $listDirn, $listOrder); ?>
				</th>
				<?php if ($canEdit || $canDelete): ?>
					<th class="center">
						
					</th>
				<?php endif; ?>
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
			<tr class="row<?php echo $i % 2; ?>">
				<td>
					<?php if (isset($item->checked_out) && $item->checked_out) : ?>
						<?php echo JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'calculations.', $canCheckin); ?>
					<?php endif; ?>
					<a href="<?php echo JRoute::_('index.php?option=com_gm_ceiling&view=calculation&id='.(int) $item->id); ?>">
						<?php echo $this->escape($item->calculation_title); ?>
					</a>
				</td>
				<td>
					<?php echo $item->n1; ?>
				</td>
				<td>
					<?php echo $item->n2; ?>
				</td>
				<td>
					<?php echo $item->n3; ?>
				</td>
				<td class="center">
					<?php echo $item->n4; ?>
				</td>
				<td class="center">
					<?php echo $item->n5; ?>
				</td>
				<?php if ($canEdit || $canDelete): ?>
					<td class="right">
						<?php if ($canEdit): ?>
							<a href="<?php echo JRoute::_('index.php?option=com_gm_ceiling&task=calculationform.edit&id=' . $item->id, false, 2); ?>" class="btn" type="button"><i class="icon-edit" ></i> Изменить</a>
						<?php endif; ?>
						<?php if ($canDelete): ?>
							<a href="<?php echo JRoute::_('index.php?option=com_gm_ceiling&task=calculationform.remove&id=' . $item->id, false, 2); ?>" class="btn delete-button" type="button"><i class="icon-trash" ></i> Удалить</a>
						<?php endif; ?>
					</td>
				<?php endif; ?>
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
