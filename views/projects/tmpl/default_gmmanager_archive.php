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
<h2 class = "center">Архив проектов</h2>
<form action="<?php echo JRoute::_('index.php?option=com_gm_ceiling&view=projects&type=gmmanager&subtype=archive'); ?>" method="post"
      name="adminForm" id="adminForm">
    <?php if (count($this->items) > 0): ?>
        <div class="toolbar">
            <?php echo JLayoutHelper::render('default_filter', array('view' => $this), dirname(__FILE__)); ?>
        </div>

        <table class="table table-striped one-touch-view" id="projectList">
            <thead>
            <tr>
                <th class='center'>
                   
                </th>
                <th class='center'>
                    <?php echo JHtml::_('grid.sort',  'Номер договора', 'a.id', $listDirn, $listOrder); ?>
                </th>
                <th class='center'>
                    <?php echo JHtml::_('grid.sort',  'Статус', 'a.project_status', $listDirn, $listOrder); ?>
                </th>
                <th class='center'>
                    <?php echo JHtml::_('grid.sort',  'Дата закрытия проекта', 'a.project_mounting_from', $listDirn, $listOrder); ?>
                </th>
                <th class='center'>
                    <?php echo JHtml::_('grid.sort',  'COM_GM_CEILING_PROJECTS_PROJECT_INFO', 'a.project_info', $listDirn, $listOrder); ?>
                </th>
                <th class='center'>
                    <?php echo JHtml::_('grid.sort',  'Телефоны', 'a.client_contacts', $listDirn, $listOrder); ?>
                </th>
                <th class='center'>
                    <?php echo JHtml::_('grid.sort',  'COM_GM_CEILING_PROJECTS_CLIENT_ID', 'a.client_id', $listDirn, $listOrder); ?>
                </th>
                <th class="center">
                    Дилер
                </th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($this->items as $i => $item) : ?>
                <?php $canEdit = $user->authorise('core.edit', 'com_gm_ceiling'); ?>
                <?php $dealer = JFactory::getUser($item->dealer_id); ?>
                <?php if (!$canEdit && $user->authorise('core.edit.own', 'com_gm_ceiling')): ?>
                    <?php $canEdit = JFactory::getUser()->id == $item->created_by; ?>
                <?php endif; ?>

                <tr data-href="<?php echo JRoute::_('index.php?option=com_gm_ceiling&view=project&type=gmmanager&subtype=run&id='.(int) $item->id); ?>">
                    <td>
                    <?php if ($item->project_status == 12) { ?>
                         <i class='fa fa-check' aria-hidden='true'></i> Выполнено
                    <?php } ?>
                    </td>
                    <td class="center one-touch">
                        <?php echo $item->id; ?>
                         <input id="project_sum" value="<?php echo ($item->new_project_sum)?$item->new_project_sum:$item->project_sum; ?>"  hidden>
                    </td>
                    <td class="center one-touch">
                        <?php echo $item->status; ?>
                    </td>
                    <td class="center one-touch">
                        <?php if($item->closed == "0000-00-00" || empty($item->closed)) { ?>
                            -
                        <?php } else { ?>
                            <?php $jdate = new JDate($item->closed); ?>
                            <?php echo $jdate->format('d.m.Y'); ?>
                        <?php } ?>
                    </td>
                    <td class="center one-touch">
                        <?php echo $this->escape($item->project_info); ?>
                    </td>
                    <td class="center one-touch">
                        <?php echo $item->client_contacts; ?>
                    </td>
                    <td class="center one-touch">
                        <?php echo $item->client_name; ?>
                    </td>
                    <td class="center one-touch">
                        <?php echo $dealer->name; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
           
        </table>
    <?php else: ?>
        <p class="center">
        <h3>У вас еще нет завершенных проектов!</h3>
        </p>
    <?php endif; ?>

    <input type="hidden" name="task" value=""/>
    <input type="hidden" name="boxchecked" value="0"/>
    <input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>"/>
    <input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>"/>
    <?php echo JHtml::_('form.token'); ?>
</form>

    <script type="text/javascript">
        function deleteItem() {

            if (!confirm("<?php echo JText::_('COM_GM_CEILING_DELETE_MESSAGE'); ?>")) {
                return false;
            }
        }
    </script>
