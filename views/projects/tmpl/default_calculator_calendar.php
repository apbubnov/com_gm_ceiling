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

<?= parent::getButtonBack(); ?>
<h2 class="center">График замеров</h2>
<form action="<?= JRoute::_('index.php?option=com_gm_ceiling&view=projects&type=calculator&subtype=calendar'); ?>"
      method="post" name="adminForm" id="adminForm">
    <div class="row">
        <div class="col-md-2 col-xs-6">
            <a href="<?= JRoute::_('index.php?option=com_gm_ceiling&view=addproject&type=calculator', false, 2); ?>"
               class="btn btn-primary">
                <i class="icon-plus"></i> Добавить замер
            </a>
        </div>
        <div class="col-md-2 col-xs-6">
            <a class="btn btn-large btn-primary"
               href="/index.php?option=com_gm_ceiling&amp;view=gaugers&amp;type=chief">
                <i class="fa fa-user" aria-hidden="true"></i> Замерщики
            </a>
        </div>
        <div class="span3">


        </div>
        <?php if (false): ?>
            <div class="span9">
                <?= JLayoutHelper::render('default_filter', array('view' => $this), dirname(__FILE__)); ?>
            </div>
        <?php endif; ?>
    </div>
    <div class="row">
        <table class="rwd-table" id="projectList">
            <thead>
            <tr class="row">
                <th class='center'>
                    №
                </th>
                <th class='center'>
                    Дата замера
                </th>
                <th class='center'>
                    Время
                </th>
                <th class='center'>
                    Адрес
                </th>
                <th class='center'>
                    Примечание
                </th>
                <th class="center">
                    Клиент
                </th>
                <th class="center">
                    <i class="fas fa-ban"></i> Перевсти в отказ
                </th>
            </tr>
            </thead>
            <tbody>
            <?php
            foreach ($this->items as $i => $item):
                ?>
                <tr class="row" style="cursor: pointer;"
                    data-href="<?= JRoute::_('index.php?option=com_gm_ceiling&view=project&type=calculator&subtype=calendar&id=' . $item->id); ?>">
                    <td data-th="Номер договора" class="center one-touch"><?= $item->id; ?></td>
                    <td data-th="Дата замера" class="center one-touch">
                        <? if (empty($item->calculation_date) || $item->calculation_date == '0000-00-00'): ?>-
                        <? else: ?><?= date('d.m.Y', strtotime($item->calculation_date)); ?>
                        <? endif; ?>
                    </td>
                    <td data-th="Время замера" class="center one-touch">
                        <? if (empty($item->calculation_time) || $item->calculation_time == '00:00'): ?>-
                        <? else: ?><?= $item->calculation_time; ?>
                        <? endif; ?>
                    </td>

                    <td data-th="Адрес" class="center one-touch"><?= $item->project_info; ?></td>
                    <td data-th="Примечание" class="center one-touch">
                        <?php
                        $project_notes = Gm_ceilingHelpersGm_ceiling::getProjectNotes($item->id, 2);
                        foreach ($project_notes as $note) {
                            echo $note->description . $note->value . "<br>";
                        }
                        ?>
                    </td>
                    <td data-th="Клиент" class="center one-touch"><?= $item->client_name; ?><br><?= $item->client_contacts; ?></td>
                    <td data-th="Отказ от замера" class="center one-touch delete">
                        <button class="btn btn-danger" data-id="<?php echo $item->id; ?>" type="button">
                            <i  class="fas fa-trash-alt" aria-hidden="true"></i> Перевести
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <input type="hidden" name="task" value=""/>
        <input type="hidden" name="boxchecked" value="0"/>
    </div>


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
    jQuery(".btn-danger").click(function () {
        var project_id = jQuery(this).data('id');
        noty({
            theme: 'relax',
            layout: 'center',
            timeout: false,
            type: "info",
            text: "Вы действительно хотите перевести проект в статус отказ от замера?",
            buttons: [
                {
                    addClass: 'btn btn-primary', text: 'Перевести в отказы', onClick: function ($noty) {
                        jQuery.ajax({
                            url: "index.php?option=com_gm_ceiling&task=project.updateProjectStatus",
                            data: {
                                project_id: project_id,
                                status: 3
                            },
                            dataType: "json",
                            async: true,
                            success: function (data) {
                                jQuery('.btn-danger[data-id =' + project_id + ']').closest('.row').remove();
                            },
                            error: function (data) {
                                console.log(data);
                                var n = noty({
                                    timeout: 2000,
                                    theme: 'relax',
                                    layout: 'topCenter',
                                    maxVisible: 5,
                                    type: "error",
                                    text: "Ошибка сервера"
                                });
                            }
                        });
                        $noty.close();
                    }
                },
                {
                    addClass: 'btn btn-primary', text: 'Отмена', onClick: function ($noty) {
                        $noty.close();
                    }
                }
            ]
        });
        return false;
    });

    jQuery("#projectList tr").click(function () {

        location.href = jQuery(this).data('href');
    })
</script>
