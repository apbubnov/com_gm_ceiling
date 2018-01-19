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
<?=parent::getButtonBack();?>
<h2 class="center">Не назначенные на монтаж</h2>
<form action="<?php echo JRoute::_('index.php?option=com_gm_ceiling&view=projects&type=chiefprojects'); ?>" method="post"
      name="adminForm" id="adminForm">
	  <? if (count($this->items) > 0 && empty($this->items->project_mounter)): ?>
	
	  <table class="table table-striped one-touch-view" id="projectList">
			  <thead>
			  <tr>
				  <th class='center'>
					  <?= JHtml::_('grid.sort', 'Номер договора', 'id', $listDirn, $listOrder); ?>
				  </th>
				  <th class='center'>
					  <?= JHtml::_('grid.sort', 'Адрес', 'address', $listDirn, $listOrder); ?>
				  </th>
				  <th class='center'>
					  <?= JHtml::_('grid.sort', 'Телефоны', 'client_contacts', $listDirn, $listOrder); ?>
				  </th>
				  <th class='center'>
					  <?= JHtml::_('grid.sort', 'Клиент', 'client_name', $listDirn, $listOrder); ?>
				  </th>
				  <th class="center">
					  <?= JHtml::_('grid.sort', 'Дилер', 'dealer_name', $listDirn, $listOrder); ?>
				  </th>
				  <th class="center">
					  <?= JHtml::_('grid.sort', 'Квадратура', 'quadrature', $listDirn, $listOrder); ?>
				  </th>
			  </tr>
			  </thead>
			  <tbody>

			  <? foreach ($this->items as $i => $item) :
				  $canEdit = $user->authorise('core.edit', 'com_gm_ceiling');
				  if (!$canEdit && $user->authorise('core.edit.own', 'com_gm_ceiling'))
					  $canEdit = JFactory::getUser()->id == $item->created_by;
				  ?>

				  <? if ($userId == $item->dealer_id || $user->dealer_id == $item->dealer_id): ?>
					  <tr data-href="<?= JRoute::_('index.php?option=com_gm_ceiling&view=projectform&type=chief&id=' . (int)$item->id); ?>">
						  <td class="center one-touch">
							  <?= $item->id; ?>
						  </td>
						  <td class="center one-touch"><?= $item->address; ?></td>
						  <td class="center one-touch"><?= $item->client_contacts; ?></td>
						  <td class="center one-touch"><?= $item->client_name; ?></td>
						  <td class="center one-touch"><?= $item->dealer_name; ?></td>
						  <td class="center one-touch"><?= round($item->quadrature, 2); ?></td>
					  </tr>
				  <? endif; ?>
			  <? endforeach; ?>
			  </tbody>
	  </table>

	  <input type="hidden" name="task" value=""/>
	  <input type="hidden" name="boxchecked" value="0"/>
	  <input type="hidden" name="filter_order" value="<?= $listOrder; ?>"/>
	  <input type="hidden" name="filter_order_Dir" value="<?= $listDirn; ?>"/>
	  <?= JHtml::_('form.token'); ?>
  <? else: ?>
	  <p class="center">
	  <h3>У вас нет заказов, не назначенных на монтаж!</h3>
	  </p>
  <? endif; ?>
</form>

<script type="text/javascript">

  jQuery(document).ready(function () {

	  jQuery(".btn-done").click(function () {
		  var button = jQuery(this);
		  jQuery.get(
			  "/index.php?option=com_gm_ceiling&task=project.done",
			  {
				  project_id: button.data("project_id")
			  },
			  function (data) {
				  if (data == "1") {
					  button.closest("td").html("<i class='fa fa-check' aria-hidden='true'></i> Выполнено");
				  }
			  }
		  );

	  });

	  jQuery('.delete-button').click(deleteItem);

	  jQuery("#new_order_btn").click(function () {
		  location.href = "<?=JRoute::_('/index.php?option=com_gm_ceiling&view=calculationform&type=calculator', false); ?>";
	  });
  });

  function deleteItem() {

	  if (!confirm("<?=JText::_('COM_GM_CEILING_DELETE_MESSAGE'); ?>")) {
		  return false;
	  }
  }
</script>
