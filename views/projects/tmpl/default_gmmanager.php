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

?>
<button class="btn btn-primary" id="btn_back"><i class="fa fa-arrow-left" aria-hidden="true"></i>Назад</button>
<h2 class = "center">Запущенные в производство</h2>
<form action="<?php echo JRoute::_('index.php?option=com_gm_ceiling&view=projects&type=gmmanager'); ?>" method="post"
      name="adminForm" id="adminForm">
    <? if (count($this->items) > 0): ?>
	  <div class="toolbar">
		<?php echo JLayoutHelper::render('default_filter', array('view' => $this), dirname(__FILE__)); ?>
	  </div>

	<table class="table table-striped one-touch-view" id="projectList">
		<thead>
			<tr>
				<th class='center'>
					<?php //echo JHtml::_('grid.sort',  'Номер договора', 'a.id', $listDirn, $listOrder); ?>
					Номер договора
				</th>
<!--				<th class='center'>-->
<!--					--><?php //echo JHtml::_('grid.sort',  'Статус', 'a.project_status', $listDirn, $listOrder); ?>
<!--				</th>-->
				<th class='center'>
					<?php //echo JHtml::_('grid.sort',  'Дата и время начала монтажа', 'a.project_mounting_from', $listDirn, $listOrder); ?>
					Дата и время начала монтажа
				</th>
				<th class='center'>
					<?php //echo JHtml::_('grid.sort',  'COM_GM_CEILING_PROJECTS_PROJECT_INFO', 'a.project_info', $listDirn, $listOrder); ?>
					Адрес
				</th>
				<th class='center'>
					<?php //echo JHtml::_('grid.sort',  'Телефоны', 'a.client_contacts', $listDirn, $listOrder); ?>
					Телефоны
				</th>
				<th class='center'>
					<?php //echo JHtml::_('grid.sort',  'COM_GM_CEILING_PROJECTS_CLIENT_ID', 'a.client_id', $listDirn, $listOrder); ?>
					Клиент
				</th>
				<th class="center">
					Дилер
				</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($this->items as $i => $item):
				$canEdit = $user->authorise('core.edit', 'com_gm_ceiling');
				if (!$canEdit && $user->authorise('core.edit.own', 'com_gm_ceiling')):
					$canEdit = JFactory::getUser()->id == $item->created_by;
				endif;
				?>

				<tr data-href="<?php echo JRoute::_('index.php?option=com_gm_ceiling&view=project&type=gmmanager&id='.(int) $item->id); ?>">
					<td class="center one-touch">
						<?php echo $item->id; ?>
					</td>
					<td class="center one-touch">
						<?php if(empty($item->project_mounting_date)) { ?>
							-
						<?php } else { ?>
							<?php echo str_replace(',', '<br>', $item->project_mounting_date)   ?>
							
						<?php } ?>
					</td>
					<td class="center one-touch">
						<?php echo $this->escape($item->project_info); ?>
					</td>
					<td class="center one-touch">
						<?php echo $item->client_contacts; ?>
					</td>
					<td class="center one-touch">
						<?php echo $item->client_name; ?>
					</td>
					<td class="center one-touch">
						<?php echo $item->dealer_name; ?>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
    <? else: ?>
        <p class="center">
        <h3>У вас еще нет проектов, запущенных в производство!</h3>
        </p>
    <? endif; ?>

	<input type="hidden" name="task" value=""/>
	<input type="hidden" name="boxchecked" value="0"/>
	<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>"/>
	<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>"/>
	<?php echo JHtml::_('form.token'); ?>
</form>

<?php
    $user       = JFactory::getUser();
    $userId     = $user->get('id');
    $user_group = $user->groups;
    $dop_num_model = Gm_ceilingHelpersGm_ceiling::getModel('dop_numbers_of_users');
    $dop_num = $dop_num_model->getData($userId)->dop_number;
    $_SESSION['user_group'] = $user_group;
    $_SESSION['dop_num'] = $dop_num;
?>

<?php if($canDelete) { ?>
<script type="text/javascript">

	jQuery(document).ready(function () {
		jQuery('#btn_back').click(function(){
                location.href = "/index.php?option=com_gm_ceiling&task=mainpage";
            });
		jQuery('.delete-button').click(deleteItem);
	});

	function deleteItem() {

		if (!confirm("<?php echo JText::_('COM_GM_CEILING_DELETE_MESSAGE'); ?>")) {
			return false;
		}
	}
</script>
<?php } ?>
