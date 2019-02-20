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
?>
<?=parent::getButtonBack();?>
<h2 class="center">График замеров</h2>
<form action="<?php echo JRoute::_('index.php?option=com_gm_ceiling&view=projects&type=gmcalculator&subtype=calendar'); ?>" method="post" name="adminForm" id="adminForm">
    <div class="row-fluid toolbar">
        <div class="span3">
            <a href="<?php echo JRoute::_('index.php?option=com_gm_ceiling&view=addproject&type=gmcalculator', false, 2); ?>" class="btn btn-success"><i class="icon-plus"></i> Добавить замер
            </a>
        </div>
    </div>
    <table class="table table-striped one-touch-view g_table" id="projectList">
        <thead>
        <tr>
            <th class='center'>
                №
            </th>
            <th class='center'>
                Дата замера
            </th>
            <th class='center'>
                Адрес
            </th>
            <th>
                Примечание
            </th>
            <th class='center'>
                Клиент
            </th>
            <th class="center">
                Дилер
            </th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($this->items as $i => $item) : ?>
            <tr data-href="<?php echo JRoute::_('index.php?option=com_gm_ceiling&view=project&type=gmcalculator&subtype=calendar&id='.(int) $item->id); ?>">
                <td class="center one-touch">
                    <?php echo $item->id; ?>
                </td>
                <td class="center one-touch">
                    <? if (empty($item->project_calculation_date) || $item->project_calculation_date == '0000-00-00'): ?>-
                    <? else: ?><?= $item->project_calculation_date; ?>
                    <? endif; ?>
                </td>
                <td class="center one-touch">
                    <?php echo $this->escape($item->project_info); ?>
                </td>
                <td>
                    <?php
                        $project_notes = Gm_ceilingHelpersGm_ceiling::getProjectNotes($item->id,2);
                        foreach ($project_notes as $note){
                            echo $note->description.$note->value."<br>";
                        }
                    ?>
                </td>
                <td class="center one-touch">
                    <?php echo $item->client_name; ?><br><?php echo $item->client_contacts; ?>
                </td>
                <td class="center one-touch">
                    <?php echo $item->dealer_name; ?>
                </td>
            </tr>

        <?php endforeach; ?>
        </tbody>
    </table>
    <?php echo JHtml::_('form.token'); ?>
</form>

<script type="text/javascript">

    var $ = jQuery;

    jQuery(document).ready(function () {
        $(window).resize(Resize);
        jQuery('.delete-button').click(deleteItem);
        Resize();
    });

    function deleteItem() {

        if (!confirm("<?php echo JText::_('COM_GM_CEILING_DELETE_MESSAGE'); ?>")) {
            return false;
        }
    }
    
    function Resize() {
        reduceGTable();
    }

    // вызовем событие resize
    $(window).resize();
</script>
