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

$jinput = JFactory::getApplication()->input;

?>
<?=parent::getButtonBack();?>
<h2 class="center">Клиентские заказы</h2>
<form action="<?php echo JRoute::_('index.php?option=com_gm_ceiling&view=projects&type=gmmanagerclients'); ?>" method="post"
      name="adminForm" id="adminForm">
	  <div class="toolbar">
		<?php echo JLayoutHelper::render('default_filter', array('view' => $this), dirname(__FILE__)); ?>
	  </div>
	<table class="table table-striped one-touch-view" id="projectList">
		<thead>
			<tr>
				<th class='center'>
					<?php echo JHtml::_('grid.sort',  '', 'a.project_status', $listDirn, $listOrder); ?>
				</th>
				<th class='center'>
					<?php echo JHtml::_('grid.sort',  'Номер договора', 'a.id', $listDirn, $listOrder); ?>
				</th>

				<th class='center'>
					<?php echo JHtml::_('grid.sort',  'Телефоны', 'a.client_contacts', $listDirn, $listOrder); ?>
				</th>
				<th class='center'>
					<?php echo JHtml::_('grid.sort',  'COM_GM_CEILING_PROJECTS_CLIENT_ID', 'a.client_id', $listDirn, $listOrder); ?>
				</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($this->items as $i => $item) : ?>
				
				<tr data-href="<?php echo JRoute::_('index.php?option=com_gm_ceiling&view=project&type=manager&id='.(int) $item->id); ?>">
					
						 <td>
						<?php if($item->project_status == 7) { ?>
							Ожидается прочтение
						<?php } elseif($item->project_status == 4) { ?>
							<button class="btn btn-primary btn-done" data-project_id="<?php echo $item->id; ?>" type="button">Выполнено</button>
							<?php } elseif ($item->project_status == 5){?>
								<i class="fa fa-check" aria-hidden="true"></i>Выполнено
						<?php } ?>
					</td>
					<td class="center one-touch">
						<?php echo $item->id; ?>
					</td>
					<td class="center one-touch">
						<?php echo $item->client_contacts; ?>
					</td>
					<td class="center one-touch">
						<?php echo $item->client_id; ?>
					</td>
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
	jQuery(document).ready(function () {
	
		jQuery(".btn-done").click(function(){
			var button = jQuery( this );
			
			noty({
				layout	: 'center',
				type	: 'warning',
				modal	: true,
				text	: 'Вы уверены, что хотите отметить договор выполненным?',
				killer	: true,
				buttons	: [
					{addClass: 'btn btn-success', text: 'Выполнен', onClick: function($noty) {
							jQuery.get(
							  "/index.php?option=com_gm_ceiling&task=project.done",
							  {
								project_id: button.data("project_id")
							  },
							  function(data){
								  if(data == "1") {
									  button.closest("td").html("<i class='fa fa-check' aria-hidden='true'></i> Выполнено");
								  }
							  }
							);
							$noty.close();
						}
					},
					{addClass: 'btn', text: 'Отмена', onClick: function($noty) {
							$noty.close();
						}
					}
				]
			});

		});

	});
</script>
<?php endif; ?>

