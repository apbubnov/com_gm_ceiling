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
$user_group = $user->groups;
$dop_num_model = Gm_ceilingHelpersGm_ceiling::getModel('dop_numbers_of_users');
$dop_num = $dop_num_model->getData($userId)->dop_number;
$_SESSION['user_group'] = $user_group;
$_SESSION['dop_num'] = $dop_num;
$listOrder  = $this->state->get('list.ordering');
$listDirn   = $this->state->get('list.direction');
$canCreate  = $user->authorise('core.create', 'com_gm_ceiling') && file_exists(JPATH_COMPONENT . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . 'forms' . DIRECTORY_SEPARATOR . 'clientform.xml');
$canEdit    = $user->authorise('core.edit', 'com_gm_ceiling') && file_exists(JPATH_COMPONENT . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . 'forms' . DIRECTORY_SEPARATOR . 'clientform.xml');
$canCheckin = $user->authorise('core.manage', 'com_gm_ceiling');
$canChange  = $user->authorise('core.edit.state', 'com_gm_ceiling');
$canDelete  = $user->authorise('core.delete', 'com_gm_ceiling');

$jinput = JFactory::getApplication()->input;
$type = $jinput->getString('type', NULL);

?>

<?=parent::getButtonBack();?>

<h2 class = "center">Клиенты</h2>

<form action="<?php echo JRoute::_('index.php?option=com_gm_ceiling&view=clients&type='.$type); ?>" method="post" name="adminForm" id="adminForm">
	<div class="row-fluid toolbar">
		<div class="span3">
			<?php if ($canCreate) : ?>
				<a href="<?php echo JRoute::_('index.php?option=com_gm_ceiling&view=clientform&id=0&type='.$type, false, 2); ?>" class="btn btn-success"><i class="icon-plus"></i>
					Добавить клиента
				</a>
			<?php endif; ?>
		</div>
		<div class="span9">
			<?php echo JLayoutHelper::render('default_filter', array('view' => $this), dirname(__FILE__)); ?>
		</div>
	</div>
	<table class="table table-striped table_cashbox one-touch-view" id="clientList">
		<thead>
			<tr>
				<th class=''>
					<?php echo JHtml::_('grid.sort',  'Создан', 'a.created', $listDirn, $listOrder); ?>
				</th>
				<th class=''>
					<?php echo JHtml::_('grid.sort',  'COM_GM_CEILING_CLIENTS_CLIENT_NAME', 'a.client_name', $listDirn, $listOrder); ?>
				</th>
				<th class=''>
					<?php echo JHtml::_('grid.sort',  'COM_GM_CEILING_CLIENTS_CLIENT_CONTACTS', 'a.client_contacts', $listDirn, $listOrder); ?>
				</th>
			</tr>
		</thead>

		<tbody>
		<?php foreach ($this->items as $i => $item) : ?>
			<?php $canEdit = $user->authorise('core.edit', 'com_gm_ceiling'); ?>
			<?php if (!$canEdit && $user->authorise('core.edit.own', 'com_gm_ceiling')): ?>
				<?php $canEdit = JFactory::getUser()->id == $item->created_by; ?>
			<?php endif; ?>
			<?php if($item->id !== $user->associated_client): ?>
			<tr class="row<?php echo $i % 2; ?>" data-href="<?php echo JRoute::_('index.php?option=com_gm_ceiling&view=clientcard&id='.(int) $item->id); ?>">
				<td class="one-touch">
					<?php
						if($item->created == "0000-00-00") {
							echo "-";
						} else {
							$jdate = new JDate($item->created);
							$created = $jdate->format("d.m.Y");
							echo $created;
						}
					?>
                    
				</td>
				<td class="one-touch"><?php echo $this->escape($item->client_name); ?></td>
				<td class="one-touch"><?php echo $item->client_contacts; ?></td>
			</tr>
			<?php endif; endforeach; ?>
		</tbody>
	</table>

	<input type="hidden" name="task" value=""/>
	<input type="hidden" name="boxchecked" value="0"/>
	<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>"/>
	<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>"/>
	<?php echo JHtml::_('form.token'); ?>
</form>

<script type="text/javascript">

	jQuery(document).ready(function () {
		jQuery('.delete-button').click(deleteItem);
	});

	function deleteItem() {

		if (!confirm("<?php echo JText::_('COM_GM_CEILING_DELETE_MESSAGE'); ?>")) {
			return false;
		}
	}

    var $ = jQuery;
    $(window).resize(function(){
        if (screen.width <= '1024') {
            jQuery('#clientList').css('font-size', '10px');
        }
        else {
        }
    });

    // вызовем событие resize
    $(window).resize();
</script>

