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

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');

$user       = JFactory::getUser();
$userId     = $user->get('id');
$listOrder  = $this->state->get('list.ordering');
$listDirn   = $this->state->get('list.direction');
$canCreate  = $user->authorise('core.create', 'com_gm_ceiling') && file_exists(JPATH_COMPONENT . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . 'forms' . DIRECTORY_SEPARATOR . 'colorform.xml');
$canEdit    = $user->authorise('core.edit', 'com_gm_ceiling') && file_exists(JPATH_COMPONENT . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . 'forms' . DIRECTORY_SEPARATOR . 'colorform.xml');
$canCheckin = $user->authorise('core.manage', 'com_gm_ceiling');
$canChange  = $user->authorise('core.edit.state', 'com_gm_ceiling');
$canDelete  = $user->authorise('core.delete', 'com_gm_ceiling');


?>
<?=parent::getButtonBack();?>

<form action="<?php echo JRoute::_('index.php?option=com_gm_ceiling&view=colors'); ?>" method="post"
      name="adminForm" id="adminForm">

	<?php echo JLayoutHelper::render('default_filter', array('view' => $this), dirname(__FILE__)); ?>
	<table class="table table-striped" id="colorList">
		<thead>
		<tr>
<!--			--><?php //if (isset($this->items[0]->count)): ?>
<!--				<th width="5%">-->
<!--	--><?php //echo JHtml::_('grid.sort', 'JPUBLISHED', 'a.state', $listDirn, $listOrder); ?>
<!--</th>-->
<!--			--><?php //endif; ?>

							<th class=''>
				<?php echo JHtml::_('grid.sort',  'COM_GM_CEILING_COLORS_ID', 'a.id', $listDirn, $listOrder); ?>
				</th>
				<th class=''>
				<?php echo JHtml::_('grid.sort',  'COM_GM_CEILING_COLORS_COLOR_TITLE', 'a.color_title', $listDirn, $listOrder); ?>
				</th>
				<th class=''>
				<?php echo JHtml::_('grid.sort',  'COM_GM_CEILING_COLORS_COLOR_CANVAS', 'a.color_canvas', $listDirn, $listOrder); ?>
				</th>
				<th class=''>
				<?php echo JHtml::_('grid.sort',  'Фактура', 'a.canvas_texture', $listDirn, $listOrder); ?>
				</th>
				<th class=''>
				<?php echo JHtml::_('grid.sort',  'COM_GM_CEILING_COLORS_COLOR_FILE', 'a.color_file', $listDirn, $listOrder); ?>
				</th>
				<?php if ($canEdit || $canDelete): ?>
					<th class="center">
					
					</th>
				<?php endif; ?>

		</tr>
		</thead>

		<tbody>
		<?php foreach ($this->items as $i => $item) : ?>
			<?php $canEdit = $user->authorise('core.edit', 'com_gm_ceiling'); ?>

					<?php if (!$canEdit && $user->authorise('core.edit.own', 'com_gm_ceiling')): ?>
					<?php $canEdit = JFactory::getUser()->id == $item->created_by; ?>
				<?php endif; ?>

			<tr class="row<?php echo $i % 2; ?>">

<!--				--><?php //if (isset($this->items[0]->count)) : ?>
<!--					--><?php //$class = ($canChange) ? 'active' : 'disabled'; ?>
<!--					<td class="center">-->
<!--					<a class="btn btn-micro --><?php //echo $class; ?><!--" href="--><?php //echo ($canChange) ? JRoute::_('index.php?option=com_gm_ceiling&task=color.publish&id=' . $item->id . '&state=' . (($item->count + 1) % 2), false, 2) : '#'; ?><!--">-->
<!--					--><?php //if ($item->count > 0): ?>
<!--						<i class="fa fa-check-circle" aria-hidden="true"></i>-->
<!--					--><?php //else: ?>
<!--						<i class="fa fa-times-circle-o" aria-hidden="true"></i>-->
<!--					--><?php //endif; ?>
<!--					</a>-->
<!--				</td>-->
<!--				--><?php //endif; ?>

								<td>

					<?php echo $item->id; ?>
				</td>
				<td>
					<a href="<?php echo JRoute::_('index.php?option=com_gm_ceiling&view=color&id='.(int) $item->id); ?>">
						<?php echo $this->escape($item->colors_title); ?>
					</a>
				</td>
				<td>

					<?php echo $item->full_name; ?>
				</td>
				<td>
					<?php echo $item->texture_title; ?>
				</td>
				<td>

					<img src="/<?php echo $item->file; ?>" alt="" style="max-width: 64px;"/>
				</td>


								<?php if ($canEdit || $canDelete): ?>
					<td class="center">
						<?php if ($canEdit): ?>
							<a href="<?php echo JRoute::_('index.php?option=com_gm_ceiling&task=colorform.edit&id=' . $item->id, false, 2); ?>" class="btn btn-mini" type="button"><i class="fa fa-pencil" aria-hidden="true"></i></a>
						<?php endif; ?>
<!--						--><?php //if ($canDelete): ?>
<!--							<a href="--><?php //echo JRoute::_('index.php?option=com_gm_ceiling&task=colorform.remove&id=' . $item->id, false, 2); ?><!--" class="btn btn-mini delete-button" type="button"><i class="fa fa-trash" aria-hidden="true"></i></a>-->
<!--						--><?php //endif; ?>
					</td>
				<?php endif; ?>

			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>

	<?php if ($canCreate) : ?>
		<a href="<?php echo JRoute::_('index.php?option=com_gm_ceiling&task=colorform.edit&id=0', false, 2); ?>"
		   class="btn btn-success btn-small"><i
				class="icon-plus"></i>
			Добавить
		</a>
	<?php endif; ?>

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
