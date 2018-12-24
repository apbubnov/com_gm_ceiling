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
<form action="">
    <input id="jform_project_id" type="hidden" value="jform[project_id]" />
    <input id="jform_project_status" type="hidden" value="jform[project_status]" />
</form>
<?php if ($user->dealer_type != 2): ?><h2 class="center">Завершенные монтажи</h2><?php else: ?><h2 class="center">Заказы</h2><?php endif; ?>
<form action="<?php echo JRoute::_('index.php?option=com_gm_ceiling&view=projects&type=gmchief&&subtype=run'); ?>" method="post"
      name="adminForm" id="adminForm">
    <?php if (false): ?>
        <div class="toolbar">
            <?php echo JLayoutHelper::render('default_filter', array('view' => $this), dirname(__FILE__)); ?>
        </div>
    <?php endif; ?>

    <?php if (count($this->items) > 0): ?>
        <table class="table table-striped one-touch-view" id="projectList">
            <thead>
            <tr>
                <th class='center'>
                    <?php echo JHtml::_('grid.sort',  'Номер договора', 'a.id', $listDirn, $listOrder); ?>
                </th>
                <th class='center'>
                    <?php echo JHtml::_('grid.sort',  'Дата закрытия', 'a.closed', $listDirn, $listOrder); ?>
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
                <th class='center'>
                    Имя дилера
                </th>
                <th class='center'>
                    Квадратура
                </th>
                <th class='center'>
                    Бригада
                </th>
            </tr>
            </thead>
            <tbody>
            <?php   foreach ($this->items as $i => $item) : ?>
                <?php $canEdit = $user->authorise('core.edit', 'com_gm_ceiling'); ?>

                <?php if (!$canEdit && $user->authorise('core.edit.own', 'com_gm_ceiling')): ?>
                    <?php $canEdit = JFactory::getUser()->id == $item->created_by; ?>
                <?php endif; ?>
                    <tr data-href="<?php echo JRoute::_('index.php?option=com_gm_ceiling&view=projectform&type=gmchief&subtype=run&id='.(int) $item->id); ?>">
                        <td class="center one-touch">
                            <?php echo $item->id; ?>
                        </td>

                        <?php $jdate = new JDate(JFactory::getDate($item->closed)); ?>
                        <td class="center one-touch">
                            <?php if ( $item->closed == "0000-00-00"): ?> -
                            <?php else: ?><?= $jdate->format('d.m.Y'); ?>
                            <?php endif; ?>
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
                            <?php //$dealer = JFactory::getUser($item->dealer_id); ?>
                            <?php echo $item->dealer_name; ?>
                        </td>
                        <td class="center one-touch">
                            <?php $calculations_model = Gm_ceilingHelpersGm_ceiling::getModel('calculations'); ?>
                            <?php echo $calculations_model->getProjectQuadrature($item->id); ?>
                        </td>
                        <?php if ($item->project_mounter) {$mounters_model = Gm_ceilingHelpersGm_ceiling::getModel('mounters');
                            $mounter = $mounters_model->getEmailMount($item->project_mounter);?>
                        <td class="center one-touch"><?= $mounter->name; ?></td> <?} else { ?>
                        <td class="center one-touch"><?= "-"; ?></td><?php } ?>
                    </tr>
            <?php endforeach; ?>
            </tbody>

        </table>

        <input type="hidden" name="task" value=""/>
        <input type="hidden" name="boxchecked" value="0"/>
        <input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>"/>
        <input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>"/>

        <?php echo JHtml::_('form.token'); ?>
    <?php else: ?>
        <p class="center">
        <h3>У вас еще нет завершенных заказов!</h3>
        </p>

    <?php endif; ?>
</form>

<?php if($canDelete) : ?>

<?php endif; ?>
