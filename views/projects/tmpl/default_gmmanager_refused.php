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

$user = JFactory::getUser();
$userId = $user->get('id');

$listOrder = $this->state->get('list.ordering');
$listDirn = $this->state->get('list.direction');

$canCreate = $user->authorise('core.create', 'com_gm_ceiling') && file_exists(JPATH_COMPONENT . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . 'forms' . DIRECTORY_SEPARATOR . 'projectform.xml');
$canEdit = $user->authorise('core.edit', 'com_gm_ceiling') && file_exists(JPATH_COMPONENT . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . 'forms' . DIRECTORY_SEPARATOR . 'projectform.xml');
$canCheckin = $user->authorise('core.manage', 'com_gm_ceiling');
$canChange = $user->authorise('core.edit.state', 'com_gm_ceiling');
$canDelete = $user->authorise('core.delete', 'com_gm_ceiling');

$jinput = JFactory::getApplication()->input;
$type = $jinput->getString('type', '', 'STRING');
$subtype = $jinput->getString('subtype', '', 'STRING');

?>
<?=parent::getButtonBack();?>
<h2 class = "center">Отказы</h2>
<form action="<?= JRoute::_('index.php?option=com_gm_ceiling&view=projects&type=' . $type . '&subtype=' . $subtype); ?>"
      method="post" name="adminForm" id="adminForm">
    <? if (false): ?>
        <div class="toolbar"><?= JLayoutHelper::render('default_filter', array('view' => $this), dirname(__FILE__)); ?></div>
    <? endif; ?>
    <table class="table table-striped one-touch-view" id="projectList">
        <thead>
        <tr>
            <th class='center'>
                <?= JHtml::_('grid.sort', 'Номер договора', 'id', $listDirn, $listOrder); ?>
            </th>
            <th class='center'>
                <?= JHtml::_('grid.sort', 'Статус', 'status', $listDirn, $listOrder); ?>
            </th>
            <th class='center'>
                <?= JHtml::_('grid.sort', 'Дата замера', 'calculation_date', $listDirn, $listOrder); ?>
            </th>
            <th class='center'>
                <?= JHtml::_('grid.sort', 'Время замера', 'calculation_time', $listDirn, $listOrder); ?>
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
        </tr>
        </thead>

        <tbody>
        <? foreach ($this->items as $i => $item): ?>
            <?
            $canEdit = $user->authorise('core.edit', 'com_gm_ceiling');
            $dealer = JFactory::getUser($item->dealer_id);
            if (!$canEdit && $user->authorise('core.edit.own', 'com_gm_ceiling'))
                $canEdit = JFactory::getUser()->id == $item->created_by;
            ?>

            <tr data-href="<?= JRoute::_('index.php?option=com_gm_ceiling&view=project&type=gmmanager&id=' . (int)$item->id); ?>">
                <td class="center one-touch"><?= $item->id; ?></td>
                <td class="center one-touch"><?= $item->status; ?></td>
                <td class="center one-touch">
                    <? if ($item->calculation_date == "00.00.0000"): ?>-
                    <? else: ?><?= $item->calculation_date; ?>
                    <? endif; ?>
                </td>
                <td class="center one-touch">
                    <? if ($item->calculation_time == "00:00-01:00" || $item->calculation_time == ""): ?>-
                    <? else: ?><?= $item->calculation_time; ?>
                    <? endif; ?>
                </td>
                <td class="center one-touch"><?= $item->project_info; ?></td>
                <td class="center one-touch"><?= $item->client_contacts; ?></td>
                <td class="center one-touch"><?= $item->client_name; ?></td>
            </tr>
        <? endforeach; ?>
        </tbody>
    </table>

    <input type="hidden" name="task" value=""/>
    <input type="hidden" name="boxchecked" value="0"/>
    <input type="hidden" name="filter_order" value="<?= $listOrder; ?>"/>
    <input type="hidden" name="filter_order_Dir" value="<?= $listDirn; ?>"/>
    <?= JHtml::_('form.token'); ?>
</form>

<? if ($canDelete): ?>
    <script type="text/javascript">

        jQuery(document).ready(function () {
            jQuery('.delete-button').click(deleteItem);
        });

        function deleteItem() {
            if (!confirm("<?=JText::_('COM_GM_CEILING_DELETE_MESSAGE');?>")) {
                return false;
            }
        }

    </script>
<? endif; ?>
