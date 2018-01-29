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
<h2 class="center">Запущенные в производство</h2>
<form action="<?php echo JRoute::_('index.php?option=com_gm_ceiling&view=projects&type=gmcalculator&subtype=projects'); ?>" method="post"
      name="adminForm" id="adminForm">
	  <div class="toolbar">
		<?php echo JLayoutHelper::render('default_filter', array('view' => $this), dirname(__FILE__)); ?>
	  </div>
	<table class="table table-striped one-touch-view" id="projectList">
		<thead>
			<tr>
				<th class='center'>
					<?php echo JHtml::_('grid.sort',  'Номер договора', 'a.id', $listDirn, $listOrder); ?>
				</th>
				<th class='center'>
					<?php echo JHtml::_('grid.sort',  'Дата замера', 'a.project_mounting_date', $listDirn, $listOrder); ?>
				</th>
				<th class='center'>
					<?php echo JHtml::_('grid.sort',  'Время замера', 'a.project_mounting_daypart', $listDirn, $listOrder); ?>
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
					Статус
				</th>
				<th class="center">
					Дилер
				</th>
			</tr>
		</thead>
	
		<tbody>
			<?php foreach ($this->items as $i => $item) :
                if (in_array("22", $groups) && $item->project_calculator != $userId) continue;
                else if (in_array("17", $groups) && $item->who_calculate != 1) continue;
			if($item->project_status!=2) {?>
				<?php $canEdit = $user->authorise('core.edit', 'com_gm_ceiling'); ?>
				<?php $dealer = JFactory::getUser($item->dealer_id); ?>
				<?php if (!$canEdit && $user->authorise('core.edit.own', 'com_gm_ceiling')): ?>
					<?php $canEdit = JFactory::getUser()->id == $item->created_by; ?>
				<?php endif; ?>

				<tr data-href="<?php echo JRoute::_('index.php?option=com_gm_ceiling&view=project&type=gmcalculator&subtype=project&id='.(int) $item->id); ?>">
					<td class="center one-touch">
						<?php echo $item->id; ?>
					</td>
                    <td class="center one-touch">
                        <? if ($item->calculation_date == "00.00.0000"): ?>-
                        <? else: ?><?= $item->calculation_date; ?>
                        <? endif; ?>
                    </td>
                    <td class="center one-touch">
                        <? if ($item->calculation_time == "00:00-01:00" || $item->calculation_time == ""): ?>-
                        <? else: ?><?= $item->calculation_time; ?>
                        <? endif; ?>
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
                    <td class="center one-touch"><?= $item->status; ?></td>
                    <td class="center one-touch"><?= $item->dealer_name; ?></td>
				</tr>
			<?php } endforeach; ?>
		</tbody>
	</table>

    <table class="table table-striped one-touch-view" id="projectListMobil">
        <thead>
        <tr>
            <th class='center'>
                <?php echo JHtml::_('grid.sort',  '№', 'a.id', $listDirn, $listOrder); ?>
            </th>
            <th class='center'>
                <?php echo JHtml::_('grid.sort',  'Дата(время) замера', 'a.project_mounting_date', $listDirn, $listOrder); ?>
            </th>
            <th class='center'>
                <?php echo JHtml::_('grid.sort',  'COM_GM_CEILING_PROJECTS_PROJECT_INFO', 'a.project_info', $listDirn, $listOrder); ?>
            </th>
            <th class='center'>
                Статус
            </th>
        </tr>
        </thead>

        <tbody>
        <?php foreach ($this->items as $i => $item) :
            if (in_array("22", $groups) && $item->project_calculator != $userId) continue;
            else if (in_array("17", $groups) && $item->who_calculate != 1) continue;
            if($item->project_status!=2) {?>
                <?php $canEdit = $user->authorise('core.edit', 'com_gm_ceiling'); ?>
                <?php $dealer = JFactory::getUser($item->dealer_id); ?>
                <?php if (!$canEdit && $user->authorise('core.edit.own', 'com_gm_ceiling')): ?>
                    <?php $canEdit = JFactory::getUser()->id == $item->created_by; ?>
                <?php endif; ?>

                <tr data-href="<?php echo JRoute::_('index.php?option=com_gm_ceiling&view=project&type=gmcalculator&subtype=project&id='.(int) $item->id); ?>">
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
                    <td class="center one-touch"><?= $item->status; ?></td>
                </tr>
            <?php } endforeach; ?>
        </tbody>
    </table>

	<input type="hidden" name="task" value=""/>
	<input type="hidden" name="boxchecked" value="0"/>
	<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>"/>
	<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>"/>
	<?php echo JHtml::_('form.token'); ?>
</form>

<?php if($canDelete) : ?>
<script type="text/javascript">

	jQuery(document).ready(function () {
		jQuery('.delete-button').click(deleteItem);
	});

	function deleteItem() {

		if (!confirm("<?php echo JText::_('COM_GM_CEILING_DELETE_MESSAGE'); ?>")) {
			return false;
		}
	}
</script>
<?php endif; ?>
<script type="text/javascript">
    var $ = jQuery;
    $(window).resize(function(){
        if (screen.width <= '1024') {
            jQuery('#projectList').hide();
            jQuery('#projectListMobil').show();
            jQuery('#projectListMobil').css('font-size', '10px');
            jQuery('.container').css('padding-left', '0');
        }
        else {
            jQuery('#projectList').show();
            jQuery('#projectListMobil').hide();
        }
    });

    // вызовем событие resize
    $(window).resize();

</script>