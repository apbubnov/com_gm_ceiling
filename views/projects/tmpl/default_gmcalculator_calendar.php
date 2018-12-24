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

//if (empty($app->getUserState($this->context . '.list')['ordering'])) $this->setState('list.ordering', 'project_calculation_date');

?>
<?=parent::getButtonBack();?>
<h2 class="center">График замеров</h2>
<form action="<?php echo JRoute::_('index.php?option=com_gm_ceiling&view=projects&type=gmcalculator&subtype=calendar'); ?>" method="post"
      name="adminForm" id="adminForm">
	  <div class="row-fluid toolbar">
		<div class="span3">
			<a href="<?php echo JRoute::_('index.php?option=com_gm_ceiling&view=addproject&type=gmcalculator', false, 2); ?>" class="btn btn-success">
			   <i class="icon-plus"></i> Добавить замер
			</a>
		</div>
		<div class="span9">
			<?php echo JLayoutHelper::render('default_filter', array('view' => $this), dirname(__FILE__)); ?>
		</div>
	  </div>
	<table class="table table-striped one-touch-view" id="projectList">
		<thead>
			<tr>
				<th class='center'>
					<?php echo JHtml::_('grid.sort',  'Номер договора', 'p.id', $listDirn, $listOrder); ?>
				</th>
				<th class='center'>
					<?php echo JHtml::_('grid.sort',  'Дата замера', 'p.project_calculation_date', $listDirn, $listOrder); ?>
				</th>
				<th class='center'>
					<?php echo JHtml::_('grid.sort',  'Время замера', 'p.calculation_time', $listDirn, $listOrder); ?>
				</th>
				<th class='center'>
					<?php echo JHtml::_('grid.sort',  'COM_GM_CEILING_PROJECTS_PROJECT_INFO', 'p.project_info', $listDirn, $listOrder); ?>
                </th>
				<th class='center'>
					<?php echo JHtml::_('grid.sort',  'Телефоны', 'p.client_contacts', $listDirn, $listOrder); ?>
				</th>
                <th>
                    <?php echo JHtml::_('grid.sort',  'Примечание менеджера', 'p.gm_manager_note', $listDirn, $listOrder); ?>
                </th>
				<th class='center'>
					<?php echo JHtml::_('grid.sort',  'COM_GM_CEILING_PROJECTS_CLIENT_ID', 'p.client_id', $listDirn, $listOrder); ?>
				</th>
				<?php
					$user  = JFactory::getUser();
					$groups = $user->get('groups');
					//Если менеджер дилера, то показывать дилерских клиентов
					if(in_array("16",$groups)){
				?>
					<th class="center">
						Дилер
					</th>
				<?php } ?>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($this->items as $i => $item) : ?>
			<?
			if (in_array("22", $groups) && $item->project_calculator != $userId) continue;
			else if (in_array("17", $groups) && $item->dealer_id != 1) continue;
			?>
				<?php $canEdit = $user->authorise('core.edit', 'com_gm_ceiling'); ?>

				<?php if (!$canEdit && $user->authorise('core.edit.own', 'com_gm_ceiling')): ?>
					<?php $canEdit = JFactory::getUser()->id == $item->created_by; ?>
				<?php endif; ?>
				<?php if($user->dealer_id == $item->dealer_id || $item->dealer_id == 1) {?>
				<tr data-href="<?php echo JRoute::_('index.php?option=com_gm_ceiling&view=project&type=gmcalculator&subtype=calendar&id='.(int) $item->id); ?>">
					<td class="center one-touch">
						<?php echo $item->id; ?>
					</td>
                    <td class="center one-touch">
                        <? if ($item->project_calculation_date == '0000-00-00'): ?>-
                        <? else: ?><?= $item->project_calculation_date; ?>
                        <? endif; ?>
                    </td>
                    <td class="center one-touch">
                        <? if ($item->calculation_time == '00:00-01:00' || $item->calculation_time == ''): ?>-
                        <? else: ?><?= $item->calculation_time; ?>
                        <? endif; ?>
                    </td>
					<td class="center one-touch">
						<?php echo $this->escape($item->project_info); ?>
					</td>
					<td class="center one-touch">
						<?php echo $item->client_contacts; ?>
					</td>
                    <td>
                    <?php echo $item->gm_manager_note; ?>
                    </td>
					<td class="center one-touch">
						<?php echo $item->client_name; ?>
					</td>
					<?php
						$user  = JFactory::getUser();
						$groups = $user->get('groups');
						//Если менеджер дилера, то показывать дилерских клиентов
						if(in_array("16",$groups)){
					?>
						<td class="center one-touch">
							<?php echo $item->dealer_id; ?>
						</td>
					<?php } ?>
				</tr>
				<?php } ?>
				
			<?php endforeach; ?>
		</tbody>
	</table>

    <table class="table table-striped one-touch-view" id="projectListMobil" style="display: none;">
        <thead>
        <tr>
            <th class='center'>
                <?= JHtml::_('grid.sort', '№', 'a.id', $listDirn, $listOrder); ?>
            </th>
            <th class='center'>
                <?= JHtml::_('grid.sort', 'Дата/Время замера', 'a.calculation_date', $listDirn, $listOrder); ?>
            </th>
            <th class='center'>
                <?= JHtml::_('grid.sort', 'Адрес', 'address', $listDirn, $listOrder); ?>
            </th>
            <th class='center'>
                <?php echo JHtml::_('grid.sort',  'Примечание менеджера', 'a.gm_manager_note', $listDirn, $listOrder); ?>
            </th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($this->items as $i => $item) : ?>
            <?
            if (in_array("22", $groups) && $item->project_calculator != $userId) continue;
            else if (in_array("17", $groups) && $item->dealer_id != 1) continue;
            ?>
            <?php $canEdit = $user->authorise('core.edit', 'com_gm_ceiling'); ?>

            <?php if (!$canEdit && $user->authorise('core.edit.own', 'com_gm_ceiling')): ?>
                <?php $canEdit = JFactory::getUser()->id == $item->created_by; ?>
            <?php endif; ?>
            <?php if($user->dealer_id == $item->dealer_id || $item->dealer_id == 1) {?>
                <tr data-href="<?php echo JRoute::_('index.php?option=com_gm_ceiling&view=project&type=gmcalculator&subtype=calendar&id='.(int) $item->id); ?>">
                    <td class="center one-touch">
                        <?php echo $item->id; ?>
                    </td>
                    <td class="center one-touch">
                        <? if ($item->calculation_date == "00.00.0000"): ?>-
                        <? else: ?><?= $item->calculation_date; ?>
                        <? endif; ?>
                        <? if ($item->calculation_time == "00:00-01:00" || $item->calculation_time == ""): ?>-
                        <? else: ?><?= $item->calculation_time; ?>
                        <? endif; ?>
                    </td>
                    <td class="center one-touch">
                        <?php echo $this->escape($item->project_info); ?>
                    </td>
                    <td>
                        <?php echo $item->gm_manager_note; ?>
                    </td>
                </tr>
            <?php } ?>

        <?php endforeach; ?>
        </tbody>
    </table>

	<input type="hidden" name="task" value=""/>
	<input type="hidden" name="boxchecked" value="0"/>
	<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>"/>
	<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>"/>
	<?php echo JHtml::_('form.token'); ?>
</form>


<script type="text/javascript">

    var $ = jQuery;

	jQuery(document).ready(function () {
		jQuery('.delete-button').click(deleteItem);
		Resize();
	});

	function deleteItem() {

		if (!confirm("<?php echo JText::_('COM_GM_CEILING_DELETE_MESSAGE'); ?>")) {
			return false;
		}
	}
    $(window).resize(Resize);

    function Resize() {
        if ($(window).width() <= '1024') {
            jQuery('#projectList').hide();
            jQuery('#projectListMobil').show();
            jQuery('#projectListMobil').css('font-size', '10px');
            jQuery('.container').css('padding-left', '0');
        }
        else {
            jQuery('#projectList').show();
            jQuery('#projectListMobil').hide();
            jQuery('#projectListMobil').css('display', 'none');
        }

    }

    // вызовем событие resize
    $(window).resize();
</script>
