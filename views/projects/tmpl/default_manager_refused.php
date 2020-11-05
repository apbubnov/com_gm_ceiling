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
<?= parent::getButtonBack(); ?>
<h2 class="center">Отказы</h2>
<form action="<?= JRoute::_('index.php?option=com_gm_ceiling&view=projects&type=' . $type . '&subtype=' . $subtype); ?>"
      method="post" name="adminForm" id="adminForm">
    <table class="table table-striped one-touch-view" id="projectList">
        <thead>
        <tr>
            <th class='center'>
                Номер договора
            </th>
            <th class='center'>
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
        </tr>
        </thead>

        <tbody>
        <?php foreach ($this->items as $i => $item): ?>

            <tr data-href="<?= JRoute::_('index.php?option=com_gm_ceiling&view=project&type=calculator&subtype=refused&id=' . (int)$item->id); ?>">
                <td class="center one-touch"><?= $item->id; ?></td>
                <td class="center one-touch"><?= $item->status; ?></td>
                <td class="center one-touch">
                    <?php
                        if ($item->project_calculation_date == '00.00.0000' || empty($item->project_calculation_date)):
                            echo '-';
                        else:
                            echo date('d.m.Y H:i', strtotime($item->project_calculation_date));
                        endif;
                    ?>
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

