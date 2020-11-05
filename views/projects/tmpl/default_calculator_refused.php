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
<h2 class="center">Отказы</h2>
	<table class="table table-striped one-touch-view" id="projectList">
		<thead>
			<tr>
				<th class='center'>
					Номер договора
				</th>
                <th>
                    Статус
                </th>
				<th class='center'>
					Дата замера
				</th>
				<th class='center'>
					Адрес
				</th>
				<th class='center'>
					Телефоны
				</th>
				<th class='center'>
					Клиент
				</th>
				<?php
					$user  = JFactory::getUser();
					$groups = $user->get('groups');
					//Если менеджер дилера, то показывать дилерских клиентов
					if(in_array("16",$groups)){
				?>
					<th class="center">
						Дилер
					</th>
				<?php } ?>
			</tr>
		</thead>

		<tbody>
			<?php foreach ($this->items as $i => $item) : ?>
				<?php $canEdit = $user->authorise('core.edit', 'com_gm_ceiling'); ?>

				<?php if (!$canEdit && $user->authorise('core.edit.own', 'com_gm_ceiling')): ?>
					<?php $canEdit = JFactory::getUser()->id == $item->created_by; ?>
				<?php endif; ?>

				<tr data-href="<?php echo JRoute::_('index.php?option=com_gm_ceiling&view=project&type=calculator&subtype=refused&id='.(int) $item->id); ?>">
					<td class="center one-touch">
						<?php echo $item->id; ?>
					</td>
                    <td>
                        <?= $item->status;?>
                    </td>
					<td class="center one-touch">
							<?php if(!empty($item->project_calculation_date)) {
							    echo date('d.m.Y H:i',strtotime($item->project_calculation_date));
							}
							else {
                                echo '-';
                            }?>
					</td>
					<td class="center one-touch">
						<?php echo $this->escape($item->project_info); ?>
					</td>
					<td class="center one-touch">
						<?php echo $item->client_contacts; ?>
					</td>
					<td class="center one-touch">
						<?php echo $item->client_id; ?>
					</td>
					<?php
						$user  = JFactory::getUser();
						$groups = $user->get('groups');
						//Если менеджер дилера, то показывать дилерских клиентов
						if(in_array("16",$groups)){
					?>
						<td class="center one-touch">
							<?php echo $item->dealer_name; ?>
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

