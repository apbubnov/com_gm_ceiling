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
<h2 class="center">График замеров</h2>

<form action="<?php echo JRoute::_('index.php?option=com_gm_ceiling&view=projects&type=gmchief'); ?>" method="post" name="adminForm" id="adminForm">
    <? if (false): ?>
	  <div class="toolbar">
		<?php echo JLayoutHelper::render('default_filter', array('view' => $this), dirname(__FILE__)); ?>
		</div>
    <? endif; ?>
    <? if (count($this->items) > 0): ?>
        <table class="table table-striped one-touch-view" id="projectList">
            <thead>
                <tr>
                    <th class='center'>
                        <?php echo JHtml::_('grid.sort',  'Номер договора', 'a.id', $listDirn, $listOrder); ?>
                    </th>
                    <th class='center'>
                        <?php echo JHtml::_('grid.sort',  'COM_GM_CEILING_PROJECTS_PROJECT_CALCULATION_DATE', 'a.project_calculation_date', $listDirn, $listOrder); ?>
                    </th>
                    <th class='center'>
                        <?php echo JHtml::_('grid.sort',  'Замерщик', 'a.project_calculator', $listDirn, $listOrder); ?>
                    </th>
                    <th class='center'>
                        <?php echo JHtml::_('grid.sort',  'COM_GM_CEILING_PROJECTS_PROJECT_INFO', 'a.project_info', $listDirn, $listOrder); ?>
                    </th>
                    <th class='center'>
                        <?php echo JHtml::_('grid.sort',  'Телефоны', 'a.client_contacts', $listDirn, $listOrder); ?>
                    </th>
                    <th class='center'>
                        <?php echo JHtml::_('grid.sort',  'Примечание менеджера', 'a.gm_manager_note', $listDirn, $listOrder); ?>
                    </th>
                    <th class='center'>
                        <?php echo JHtml::_('grid.sort',  'COM_GM_CEILING_PROJECTS_CLIENT_ID', 'a.client_id', $listDirn, $listOrder); ?>
                    </th>
                </tr>
            </thead>
            <tbody>
                <?php   foreach ($this->items as $i => $item) : ?>
                    <tr data-href="<?php echo JRoute::_('index.php?option=com_gm_ceiling&view=projectform&type=gmchief&id='.(int) $item->id); ?>">
                        <td class="center one-touch">
                            <?php echo $item->id; ?>
                        </td>
                        <?php $jdate = new JDate(JFactory::getDate($item->project_calculation_date)); ?>
                        <td class="center one-touch">
                            <? if ($item->project_calculation_date == '0000-00-00 00:00'): ?> -
                            <? else: ?><?= $jdate->format('d.m.Y H:i'); ?>
                            <? endif; ?>
                        </td>
                        <? if ($item->project_calculator) {
                            $mounters_model = Gm_ceilingHelpersGm_ceiling::getModel('mounters');
                            $mounter = $mounters_model->getEmailMount($item->project_calculator);
                        } ?>
                            <td class="center one-touch">
                                <?= $mounter->name; ?>
                            </td>
                        <td class="center one-touch">
                            <?php echo $this->escape($item->project_info); ?>
                        </td>
                        <td class="center one-touch">
                            <?php echo $item->client_contacts; ?>
                        </td>
                        <td class="center one-touch">
                            <?php echo $item->gm_manager_note; ?>
                        </td>
                        <td class="center one-touch">
                            <?php echo $item->client_name; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <table class="table table-striped one-touch-view" id="projectListMobil">
            <thead>
                <tr>
                    <th class='center'>
                        <?php echo JHtml::_('grid.sort',  '№', 'a.id', $listDirn, $listOrder); ?>
                    </th>
                    <th class='center'>
                        <?php echo JHtml::_('grid.sort',  'COM_GM_CEILING_PROJECTS_PROJECT_CALCULATION_DATE', 'a.project_calculation_date', $listDirn, $listOrder); ?>
                    </th>
                    <th class='center'>
                        <?php echo JHtml::_('grid.sort',  'COM_GM_CEILING_PROJECTS_PROJECT_INFO', 'a.project_info', $listDirn, $listOrder); ?>
                    </th>
                    <th class='center'>
                        <?php echo JHtml::_('grid.sort',  'COM_GM_CEILING_PROJECTS_CLIENT_ID', 'a.client_id', $listDirn, $listOrder); ?>
                    </th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($this->items as $i => $item) : ?>
                    <tr data-href="<?php echo JRoute::_('index.php?option=com_gm_ceiling&view=projectform&type=gmchief&id='.(int) $item->id); ?>">
                        <td class="center one-touch">
                            <?php echo $item->id; ?>
                        </td>
                        <?php $jdate = new JDate(JFactory::getDate($item->calculation_date)); ?>
                        <td class="center one-touch">
                            <? if ($item->calculation_date == "00.00.0000 00:00"): ?> -
                            <? else: ?><?= $jdate->format('d.m.Y H:i'); ?>
                            <? endif; ?>
                        </td>
                        <td class="center one-touch">
                            <?php echo $this->escape($item->project_info); ?>
                        </td>
                        <td class="center one-touch">
                            <?php echo $item->client_name; ?>
                            <?php echo $item->client_contacts; ?><br>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <input type="hidden" name="task" value=""/>
        <input type="hidden" name="boxchecked" value="0"/>
        <input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>"/>
        <input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>"/>
        <?php echo JHtml::_('form.token'); ?>
    <? else: ?>
        <p class="center">
        <h3>У вас еще нет заказов!</h3>
        </p>
        <button id="new_order_btn" class="btn btn-primary" type="button">Сделайте заказ прямо сейчас</button>
    <? endif; ?>
</form>

<script type="text/javascript">

    var $ = jQuery;
    $(window).resize(function(){
        if (screen.width <= '1024') {
            jQuery('#projectList').hide();
            jQuery('#projectListMobil').show();
            jQuery('#projectListMobil').css('font-size', '10px');
            jQuery('.container').css('padding-left', '0');
            jQuery('.btn-done').css('font-size', '10px');
            jQuery('.btn-done').css('padding', '5px');
        }
        else {
            jQuery('#projectList').show();
            jQuery('#projectListMobil').hide();
        }
    });
    // вызовем событие resize
    $(window).resize();

    jQuery(document).ready(function () {

    });

</script>