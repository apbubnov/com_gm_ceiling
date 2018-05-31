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
<h2 class="center">График замеров</h2>
<form action="<?= JRoute::_('index.php?option=com_gm_ceiling&view=projects&type=calculator&subtype=calendar'); ?>" method="post" name="adminForm" id="adminForm">
    <div class="row-fluid toolbar">
        <div class="span3">
            <a href="<?= JRoute::_('index.php?option=com_gm_ceiling&view=addproject&type=calculator', false, 2); ?>" class="btn btn-success">
                <i class="icon-plus"></i> Добавить замер
            </a>
            <a class="btn btn-large btn-primary" href="/index.php?option=com_gm_ceiling&amp;view=gaugers&amp;type=chief"><i class="fa fa-user" aria-hidden="true"></i> Замерщики</a>
        </div>
        <?php if (false): ?>
            <div class="span9">
                <?= JLayoutHelper::render('default_filter', array('view' => $this), dirname(__FILE__)); ?>
            </div>
        <?php endif; ?>
    </div>
    <table class="rwd-table" id="projectList">
        <thead>
            <tr class="row">
                <th class='center'>
                    №
                </th>
                <th class='center'>
                    Дата и время замера
                </th>
                <th class='center'>
                    Адрес
                </th>
                <th class='center'>
                    Примечание
                </th>
                <?php if (in_array("16", $groups)):?>
                    <th class="center">
                        Дилер
                    </th>
                <?php endif;?>
                <?php if (in_array("14", $groups)):?>
                    <th class="center">
                        <i class="fa fa-trash-o" aria-hidden="true"></i>
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
                <tr class="row" style = "cursor: pointer;" data-href="<?= JRoute::_('index.php?option=com_gm_ceiling&view=project&type=calculator&subtype=calendar&id=' . $item->id); ?>">
                    <td data-th = "Номер договора" class="center one-touch"><?= $item->id; ?></td>
                    <td data-th = "Дата/время замера" class="center one-touch">
                        <? if ($item->calculation_date == "00.00.0000"): ?>-
                        <? else: ?><?= $item->calculation_date; ?>
                        <? endif; ?>
                        <br>
                        <? if ($item->calculation_time == "00:00-01:00" || $item->calculation_time == ""): ?>-
                        <? else: ?><?= $item->calculation_time; ?>
                        <? endif; ?>
                    </td>
                    <td data-th = "Адрес" class="center one-touch"><?= $item->address; ?></td>
                    <td data-th = "Примечание" class="center one-touch"><?= $item->dealer_manager_note; ?></td>
                    <?if (in_array("16", $groups)):?>
                        <td data-th = "Дилер" class="center one-touch"><?= $item->dealer_name; ?></td>
                    <?endif;?>
                    <?php if(in_array(14, $groups)){ ?>
                        <td data-th = "Удалить" class="center one-touch delete"><button class = "btn btn-danger" data-id = "<?php echo $item->id;?>" type = "button"><i class="fa fa-trash-o" aria-hidden="true"></i></button></td>
                    <?php } ?>
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

    jQuery("#projectList tr").click(function(){

        location.href = jQuery(this).data('href');
    })
</script>
