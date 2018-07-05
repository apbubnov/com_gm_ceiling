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
foreach ($this->items as $i => $item){
    if(!empty($item->project_mounter)){
        $item->project_mounter = explode(',',$item->project_mounter);
    }
}

?>

<?= parent::getButtonBack(); ?>
<? if ($user->dealer_type != 2): ?>
    <h4 class="center" style="margin-bottom: 1em;">Назначенные на монтаж и запущенные в производство</h4>
<? else: ?>
    <h4 class="center" style="margin-bottom: 1em;">Заказы</h4>
<? endif; ?>
<form action="<?= JRoute::_('index.php?option=com_gm_ceiling&view=projects&type=chief'); ?>" method="post" name="adminForm" id="adminForm">
    <? if (false): ?>
        <div class="toolbar">
            <?= JLayoutHelper::render('default_filter', array('view' => $this), dirname(__FILE__)); ?>
        </div>
    <? endif; ?>
    <? if (count($this->items) > 0): ?>
        <table class="table table-striped one-touch-view g_table" id="projectList">
            <? if ($user->dealer_type != 2): ?>
                <thead>
                    <tr>
                        <th class='center'>№</th>
                        <th class='center'></th>
                        <th class='center'>Дата / время монтажа</th>
                        <th class='center'>Адрес</th>
                        <th class='center'>Клиент</th>
                        <th class="center">Бригада</th>
                        <?php if (in_array("14", $groups)):?>
                            <th class="center">
                                <i class="fa fa-trash-o" aria-hidden="true"></i>
                            </th>
                        <?php endif;?>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                        foreach ($this->items as $i => $item) :
                            $canEdit = $user->authorise('core.edit', 'com_gm_ceiling');
                            if (!$canEdit && $user->authorise('core.edit.own', 'com_gm_ceiling'))
                                $canEdit = JFactory::getUser()->id == $item->created_by;
                            if ($user->dealer_type == 1 && empty($item->project_mounter)) continue;
                    ?>
                        <? if ($userId == $item->dealer_id || $user->dealer_id == $item->dealer_id): ?>
                            <tr data-href="<?= JRoute::_('index.php?option=com_gm_ceiling&view=projectform&type=chief&id=' . (int)$item->id); ?>">
                                <td class="center one-touch"><?= $item->id; ?></td>
                                <td>
                                    <? if ($item->project_status >= 8): ?>
                                        <button class="btn btn-primary btn-sm btn-done" data-project_id="<?= $item->id; ?>" type="button"><i class="fa fa-check-circle"></i></button>
                                    <? endif; ?>
                                </td>
                                <?php $jdate = new JDate(JFactory::getDate($item->mounting_date)); ?>
                                <td class="center one-touch">
                                    <? if ($item->mounting_date == "00.00.0000 00:00"): ?> -
                                    <? else: ?><?= $jdate->format('d.m.Y'); ?>
                                    <? endif; ?>
                                    <? if ($item->mounting_date == "00.00.0000 00:00" || $item->calculation_time == ""): ?>-
                                    <? else: ?>
                                        <?php echo $jdate->format('H:i'); ?>
                                    <? endif; ?>
                                </td>
                                <td class="center one-touch"><?= $item->address; ?></td>
                                <td class="center one-touch"><?= $item->client_contacts; ?> <br> <?= $item->client_name; ?></td>
                                <?php if ($item->project_mounter) {
                                    $mounter = "";
                                    foreach ($item->project_mounter as $value) {
                                        $mounter  .= JFactory::getUser($value)->name."; ";
                                    }
                                } ?>
                                <td class="center one-touch"><?php echo $mounter; ?></td>
                                <?php if(in_array(14, $groups)){ ?>
                                    <td class="center one-touch delete"><button class="btn btn-danger btn-sm" data-id = "<?php echo $item->id;?>" type="button"><i class="fa fa-trash-o" aria-hidden="true"></i></button></td>
                                <?php } ?>
                            </tr>
                        <? endif; ?>
                    <? endforeach; ?>
                </tbody>
            <? else: ?>
                <thead>
                    <tr>
                        <th class='center'>
                            <? //echo JHtml::_('grid.sort', 'Номер договора', 'id', $listDirn, $listOrder); ?>
                            №
                        </th>
                        <th class='center'>
                            <? //echo JHtml::_('grid.sort', 'Сумма заказа', 'project_margin_sum', $listDirn, $listOrder); ?>
                            Сумма заказа
                        </th>
                        <th class='center'>
                            <? //echo JHtml::_('grid.sort', 'Кол-во потолков', 'count_ceilings', $listDirn, $listOrder); ?>
                            Кол-во потолков
                        </th>
                        <th class='center'>
                            <? //echo JHtml::_('grid.sort', 'Статус', 'status', $listDirn, $listOrder); ?>
                            Статус
                        </th>
                        <th class='center'>
                            <? //echo JHtml::_('grid.sort', 'Информация', 'project_status', $listDirn, $listOrder); ?>
                            Информация
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <? foreach ($this->items as $i => $item) : ?>
                        <? if ($userId == $item->dealer_id || $user->dealer_id == $item->dealer_id): ?>
                            <tr data-href="<?= JRoute::_('index.php?option=com_gm_ceiling&view=project&type=calculator&subtype=project&id=' . $item->id); ?>">
                                <td class="center one-touch"><?= $item->id; ?></td>
                                <td class="center one-touch"><?= round($item->project_margin_sum, 2); ?></td>
                                <td class="center one-touch"><?= $item->count_ceilings; ?></td>
                                <td class="center one-touch"><?= $item->status; ?></td>
                                <td class="center one-touch">
                                    <? if ($item->project_status == 0 || $item->project_status == 1 || $item->status == 4) { ?>
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
    <? endif; ?>
</form>

<script type="text/javascript">

    jQuery(document).ready(function () {

        jQuery(".btn-done").click(function () {
            var button = jQuery(this);
            noty({
                layout: 'center',
                type: 'warning',
                modal: true,
                text: 'Вы уверены, что хотите отметить договор выполненным?',
                killer: true,
                buttons: [
                    {
                        addClass: 'btn btn-success', text: 'Выполнен', onClick: function ($noty) {
                            jQuery.get(
                                "/index.php?option=com_gm_ceiling&task=project.done",
                                {
                                    project_id: button.data("project_id"),
                                    check: 1
                                },
                                function (data) {
                                    if (data == "Договор закрыт!") {
                                        button.closest("td").html("<i class='fa fa-check' aria-hidden='true'></i> Выполнено");
                                    }
                                }
                            );
                            $noty.close();
                        }
                    },
                    {
                        addClass: 'btn', text: 'Отмена', onClick: function ($noty) {
                            $noty.close();
                        }
                    }
                ]
            });

        });

        jQuery('.delete-button').click(deleteItem);

        jQuery("#new_order_btn").click(function () {
            location.href = "<?=JRoute::_('/index.php?option=com_gm_ceiling&view=calculationform&type=calculator', false); ?>";
        });
    });

    jQuery(".btn-danger").click(function(){
        var project_id = jQuery(this).data('id');
        console.log(project_id);
        jQuery.ajax({
            url: "index.php?option=com_gm_ceiling&task=project.delete_by_user",
            data: {
                project_id: project_id
            },
            dataType: "json",
            async: true,
            success: function(data) {
               jQuery('.btn-danger[data-id ='+project_id+']').closest('.row').remove();
            },
            error: function(data) {
                console.log(data);
                var n = noty({
                    timeout: 2000,
                    theme: 'relax',
                    layout: 'center',
                    maxVisible: 5,
                    type: "error",
                    text: "Ошибка сервера"
                });
            }
        });
        return false;
        
    });

    function deleteItem() {

        if (!confirm("<?=JText::_('COM_GM_CEILING_DELETE_MESSAGE'); ?>")) {
            return false;
        }
    }
</script>
