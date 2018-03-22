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
$groups = $user->get('groups');
$listOrder = $this->state->get('list.ordering');
$listDirn = $this->state->get('list.direction');
$canCreate = $user->authorise('core.create', 'com_gm_ceiling') && file_exists(JPATH_COMPONENT . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . 'forms' . DIRECTORY_SEPARATOR . 'projectform.xml');
$canEdit = $user->authorise('core.edit', 'com_gm_ceiling') && file_exists(JPATH_COMPONENT . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . 'forms' . DIRECTORY_SEPARATOR . 'projectform.xml');
$canCheckin = $user->authorise('core.manage', 'com_gm_ceiling');
$canChange = $user->authorise('core.edit.state', 'com_gm_ceiling');
$canDelete = $user->authorise('core.delete', 'com_gm_ceiling');

?>

<style>
    #projectList th:nth-child(1) {
        width: 1%;
    }
    #projectList th:nth-child(3) {
        width: 15%;
    }
    #projectListMobil {
        font-size: 12px;
        padding: 6px;
    }
    #projectListMobil td, #projectListMobil th {
        padding: 6px;
        vertical-align: middle !important;
        text-align: center !important;
    }
</style>

<?=parent::getButtonBack();?>
<h2 class="center">График замеров</h2>
<form action="<?= JRoute::_('index.php?option=com_gm_ceiling&view=projects&type=calculator&subtype=calendar'); ?>" method="post" name="adminForm" id="adminForm">
    <div class="row-fluid toolbar">
        <div class="span3">
            <a href="<?= JRoute::_('index.php?option=com_gm_ceiling&view=addproject&type=calculator', false, 2); ?>" class="btn btn-success">
                <i class="icon-plus"></i> Добавить замер
            </a>
        </div>
        <?php if (false): ?>
            <div class="span9">
                <?= JLayoutHelper::render('default_filter', array('view' => $this), dirname(__FILE__)); ?>
            </div>
        <?php endif; ?>
    </div>
    <table class="table table-striped one-touch-view" id="projectList">
        <thead>
            <tr>
                <th class='center'>
                    <?php //JHtml::_('grid.sort', 'Номер договора', 'a.id', $listDirn, $listOrder); ?>
                    Номер договора
                </th>
                <th class='center'>
                    <?php //JHtml::_('grid.sort', 'Дата и время замера', 'a.calculation_date', $listDirn, $listOrder); ?>
                    Дата и время замера
                </th>
                <th class='center'>
                    <?php //JHtml::_('grid.sort', 'Адрес', 'address', $listDirn, $listOrder); ?>
                    Адрес
                </th>
                <th class='center'>
                    <?php //JHtml::_('grid.sort', 'Клиент', 'client_name', $listDirn, $listOrder); ?>
                    Примечание
                </th>
                <?php if (in_array("16", $groups)):?>
                    <th class="center">
                        <?php //JHtml::_('grid.sort', 'Дилер', 'dealer_name', $listDirn, $listOrder); ?>
                        Дилер
                    </th>
                <?php endif;?>
            </tr>
        </thead>
        <tbody>
            <?php
                foreach ($this->items as $i => $item): 
                    if (in_array("21", $groups) && $item->project_calculator != $userId) continue;
                    //else if (in_array("14", $groups) && $item->dealer_id != $userId ) continue;
                    else if (in_array("12", $groups) && $item->who_calculate != 0) continue;
            ?>
                <tr data-href="<?= JRoute::_('index.php?option=com_gm_ceiling&view=project&type=calculator&subtype=calendar&id=' . $item->id); ?>">
                    <td class="center one-touch"><?= $item->id; ?></td>
                    <td class="center one-touch">
                        <? if ($item->calculation_date == "00.00.0000"): ?>-
                        <? else: ?><?= $item->calculation_date; ?>
                        <? endif; ?>
                        <br>
                        <? if ($item->calculation_time == "00:00-01:00" || $item->calculation_time == ""): ?>-
                        <? else: ?><?= $item->calculation_time; ?>
                        <? endif; ?>
                    </td>
                    <td class="center one-touch"><?= $item->address; ?></td>
                    <td class="center one-touch"><?= $item->dealer_manager_note; ?></td>
                    <?if (in_array("16", $groups)):?>
                        <td class="center one-touch"><?= $item->dealer_name; ?></td>
                    <?endif;?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <table class="table table-striped one-touch-view" id="projectListMobil" style="display: none;">
        <thead>
            <tr>
                <th class='center'>
                    <?php //JHtml::_('grid.sort', '№', 'a.id', $listDirn, $listOrder); ?>
                    №
                </th>
                <th class='center'>
                    <?php //JHtml::_('grid.sort', 'Дата/Время замера', 'a.calculation_date', $listDirn, $listOrder); ?>
                    Дата/Время замера
                </th>
                <th class='center'>
                    <?php //JHtml::_('grid.sort', 'Адрес', 'address', $listDirn, $listOrder); ?>
                    Адрес
                </th>
                <th class='center'>
                    <?php //JHtml::_('grid.sort', 'Телефоны', 'client_contacts', $listDirn, $listOrder); ?>
                    Примечание
                </th>
            </tr>
        </thead>
        <tbody>
            <?php
                foreach ($this->items as $i => $item):
                    if (in_array("21", $groups) && $item->project_calculator != $userId) continue;
                    //else if (in_array("14", $groups) && $item->dealer_id != $userId ) continue;
                    else if (in_array("12", $groups) && $item->who_calculate != 0) continue;
            ?>
                <tr data-href="<?= JRoute::_('index.php?option=com_gm_ceiling&view=project&type=calculator&subtype=calendar&id=' . $item->id); ?>">
                    <td class="center one-touch"><?= $item->id; ?></td>
                    <td class="center one-touch">
                        <? if ($item->calculation_date == "00.00.0000"): ?>-
                        <? else: ?><?= $item->calculation_date; ?>
                        <? endif; ?>
                        <? if ($item->calculation_time == "00:00-01:00" || $item->calculation_time == ""): ?>-
                        <? else: ?><?= $item->calculation_time; ?>
                        <? endif; ?>
                    </td>
                    <td class="center one-touch"><?= $item->address; ?></td>
                    <td class="center one-touch"><?= $item->dealer_manager_note; ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <input type="hidden" name="task" value=""/>
    <input type="hidden" name="boxchecked" value="0"/>
    <input type="hidden" name="filter_order" value="<?= $listOrder; ?>"/>
    <input type="hidden" name="filter_order_Dir" value="<?= $listDirn; ?>"/>
    <?= JHtml::_('form.token'); ?>
</form>

<? if ($canDelete) : ?>
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

<script type="text/javascript">
    var $ = jQuery;
    $(window).resize(function(){
        if (screen.width <= '1024') {
            jQuery('#projectList').hide();
            jQuery('#projectListMobil').show();
        }
        else {
            jQuery('#projectList').show();
            jQuery('#projectListMobil').hide();
        }
    });

    // вызовем событие resize
    $(window).resize();

</script>
