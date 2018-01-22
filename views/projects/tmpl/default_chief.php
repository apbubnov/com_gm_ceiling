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
<?=parent::getButtonBack();?>
<? if ($user->dealer_type != 2): ?><h2 class="center">Монтажи</h2><? else: ?><h2 class="center">Заказы</h2><? endif; ?>

<form action="<?= JRoute::_('index.php?option=com_gm_ceiling&view=projects&type=chief'); ?>" method="post"
      name="adminForm" id="adminForm">

    <? if (false): ?>
        <div class="toolbar">
            <?= JLayoutHelper::render('default_filter', array('view' => $this), dirname(__FILE__)); ?>
        </div>
    <? endif; ?>

    <? if (count($this->items) > 0): ?>
        <table class="table table-striped one-touch-view" id="projectList">
            <? if ($user->dealer_type != 2): ?>
                <thead>
                <tr>
                    <th class='center'>
                        <?//= //JHtml::_('grid.sort', 'Статус', 'status', $listDirn, $listOrder); ?>
                    </th>
                    <th class='center'>
                        <?= JHtml::_('grid.sort', 'Номер договора', 'id', $listDirn, $listOrder); ?>
                    </th>
                    <th class='center'>
                        <?= JHtml::_('grid.sort', 'Дата монтажа', 'mounting_date', $listDirn, $listOrder); ?>
                    </th>
                    <th class='center'>
                        <?= JHtml::_('grid.sort', 'Время монтажа', 'mounting_time', $listDirn, $listOrder); ?>
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
                    <th class="center">
                        <?= JHtml::_('grid.sort', 'Бригада', 'group_name', $listDirn, $listOrder); ?>
                    </th>
                </tr>
                </thead>
                <tbody>

                <? foreach ($this->items as $i => $item) :
                    $canEdit = $user->authorise('core.edit', 'com_gm_ceiling');
                    if (!$canEdit && $user->authorise('core.edit.own', 'com_gm_ceiling'))
                        $canEdit = JFactory::getUser()->id == $item->created_by;
                        if($user->dealer_type == 1 && empty($item->project_mounter)) continue;
                    ?>

                    <? if ($userId == $item->dealer_id || $user->dealer_id == $item->dealer_id) { ?>
                        <tr data-href="<?= JRoute::_('index.php?option=com_gm_ceiling&view=projectform&type=chief&id=' . (int)$item->id); ?>">

                            <td>
                                <? if ($item->project_status == 10 || ($user->dealer_type == 1 && $item->project_status >= 5 && $item->project_status <= 11)){  ?>
                                        <button class="btn btn-primary btn-done" data-project_id="<?= $item->id; ?>"
                                                type="button">Выполнено
                                        </button>
                                        
                            </td>
                            <td class="center one-touch">
                                <?= $item->id; ?>
                            </td>
                            <?php $jdate = new JDate(JFactory::getDate($item->mounting_date)); ?>
                            <td class="center one-touch">
                                <? if ($item->mounting_date == "00.00.0000 00:00"): ?> -
                                <? else: ?><?= $jdate->format('d.m.Y'); ?>
                                <? endif; ?>
                            </td>
                            <td class="center one-touch">
                                <? if ($item->mounting_date == "00.00.0000 00:00" || $item->calculation_time == ""): ?>-
                                <? else: ?>
                                    <?php echo $jdate->format('H:i'); ?>
                                <? endif; ?>
                            </td>
                            <td class="center one-touch"><?= $item->address; ?></td>
                            <td class="center one-touch"><?= $item->client_contacts; ?></td>
                            <td class="center one-touch"><?= $item->client_name; ?></td>
                            <td class="center one-touch"><?= $item->dealer_name; ?></td>
                            <td class="center one-touch"><?= round($item->quadrature, 2); ?></td>
                            <? if ($item->project_mounter) {$mounters_model = Gm_ceilingHelpersGm_ceiling::getModel('mounters');
                            $mounter = $mounters_model->getEmailMount($item->project_mounter);}?>
                            <td class="center one-touch"><?= $mounter->name; ?></td>
                        </tr>
                            <?php } } ?>
                <? endforeach; ?>
                </tbody>
            <? else: ?>
                <thead>
                <tr>
                    <th class='center'>
                        <?= JHtml::_('grid.sort', 'Номер договора', 'id', $listDirn, $listOrder); ?>
                    </th>
                    <th class='center'>
                        <?= JHtml::_('grid.sort', 'Сумма заказа', 'project_margin_sum', $listDirn, $listOrder); ?>
                    </th>
                    <th class='center'>
                        <?= JHtml::_('grid.sort', 'Кол-во потолков', 'count_ceilings', $listDirn, $listOrder); ?>
                    </th>
                    <th class='center'>
                        <?= JHtml::_('grid.sort', 'Статус', 'status', $listDirn, $listOrder); ?>
                    </th>
                    <th class='center'>
                        <?= JHtml::_('grid.sort', 'Информация', 'project_status', $listDirn, $listOrder); ?>
                    </th>

                </tr>
                </thead>
                <tbody>
                <? foreach ($this->items as $i => $item) : ?>
                    <? if ($userId == $item->dealer_id || $user->dealer_id == $item->dealer_id): ?>
                        <tr data-href="<?= JRoute::_('index.php?option=com_gm_ceiling&view=project&type=calculator&subtype=project&id=' . $item->id); ?>">
                            <td class="center one-touch"><?= $item->id; ?></td>
                            <td class="center one-touch"><?=round($item->project_margin_sum,2);?></td>
                            <td class="center one-touch"><?=$item->count_ceilings;?></td>
                            <td class="center one-touch"><?=$item->status;?></td>
                            <td class="center one-touch">
                                <? if ($item->project_status == 0 || $item->project_status == 1 || $item->status == 4 )   { ?>
                                    <a class="btn btn-large btn-primary"
                                       href="<?= JRoute::_('/index.php?option=com_gm_ceiling&view=project&type=calculator&subtype=calendar&id=' . $item->id, false); ?>">Дооформить</a>
                                <? } elseif ($item->project_status == 13) {
                                    ?>
                                    Для оплаты кликните по заказу, на открывшейся странице нажмите "Оплатить"
                                <? } ?>
                            </td>
                        </tr>
                    <? endif ?>
                <? endforeach; ?>
                </tbody>
            <? endif; ?>
        </table>

        <input type="hidden" name="task" value=""/>
        <input type="hidden" name="boxchecked" value="0"/>
        <input type="hidden" name="filter_order" value="<?= $listOrder; ?>"/>
        <input type="hidden" name="filter_order_Dir" value="<?= $listDirn; ?>"/>
        <?= JHtml::_('form.token'); ?>
    <? else: ?>
        <p class="center">
        <h3>У вас еще нет заказов!</h3>
        </p>
        <button id="new_order_btn" class="btn btn-primary" type="button">Сделайте заказ прямо сейчас</button>
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
