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
<style>
    #projectList th:nth-child(1) {
        width: 1%;
    }
    #projectList th:nth-child(3) {
        width: 15%;
    }
    #projectList th:nth-child(5) {
        width: 5%;
    }
    #projectListMobil {
        font-size: 12px;
        padding: 6px;
    }
    #projectListMobil td, #projectListMobil th {
        padding: 6px;
        vertical-align: middle !important;
        text-align: center !important;
    }
    .btn-done {
        font-size: 12px;
        padding: 6px;
    }
    @media screen and (min-width: 768px) {
        .btn-done {
            font-size: 1em;
            padding: 8px 12px;
        }
    }
</style>

<h2 class="center" style="margin-bottom: 1em;">Не назначенные на монтаж или не запущенные в производство</h2>
<form action="<?php echo JRoute::_('index.php?option=com_gm_ceiling&view=projects&type=chiefprojects'); ?>" method="post" name="adminForm" id="adminForm">
	<? if (count($this->items) > 0 && empty($this->items->project_mounter)): ?>
        <table class="table table-striped one-touch-view" id="projectList">
            <thead>
                <tr>
                    <th class='center'>
                        <?//= JHtml::_('grid.sort', '№', 'id', $listDirn, $listOrder); ?>
                        №
                    </th>
                    <th class='center'>
                        <?//= JHtml::_('grid.sort', 'Дата/Время замера', 'a.calculation_date', $listDirn, $listOrder); ?>
                        Дата / время замера
                    </th>
                    <th class='center'>
                        <?//= JHtml::_('grid.sort', 'Адрес', 'address', $listDirn, $listOrder); ?>
                        Адрес
                    </th>
                    <th class='center'>
                        <?//= JHtml::_('grid.sort', 'Клиент', 'client_name', $listDirn, $listOrder); ?>
                        Клиент
                    </th>
                    <th class="center">
                        <?//= JHtml::_('grid.sort', 'Квадратура', 'quadrature', $listDirn, $listOrder); ?>
                        Квадратура
                    </th>
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
                        if ($userId == $item->dealer_id || $user->dealer_id == $item->dealer_id):
                ?>
                        <tr class = "row" data-href="<?= JRoute::_('index.php?option=com_gm_ceiling&view=projectform&type=chief&id=' . (int)$item->id); ?>">
                            <td class="center one-touch"><?= $item->id; ?></td>
                            <td class="center one-touch">
                                <? if ($item->calculation_date == "00.00.0000"): ?> -
                                <? else: echo $item->calculation_date;
                                endif;?><br><?
                                if ($item->calculation_time == "00:00-01:00" || $item->calculation_time == ""): ?> -
                                <? else: echo $item->calculation_time;
                                endif; ?>
                            </td>
                            <td class="center one-touch"><?= $item->address; ?><br><?= $item->client_contacts; ?></td>
                            <td class="center one-touch"><?= $item->client_name; ?></td>
                            <td class="center one-touch"><?= round($item->quadrature, 2); ?></td>
                            <?php if(in_array(14, $groups)){ ?>
                                <td class="center one-touch delete"><button class = "btn btn-danger" data-id = "<?php echo $item->id;?>" type = "button"><i class="fa fa-trash-o" aria-hidden="true"></i></button></td>
                            <?php } ?>
                        </tr>
                    <? endif; ?>
                <? endforeach; ?>
            </tbody>
        </table>
        <table class="table table-striped one-touch-view" id="projectListMobil">
            <thead>
                <tr>
                    <th class='center'>
                        <?//= JHtml::_('grid.sort', '№', 'id', $listDirn, $listOrder); ?>
                        №
                    </th>
                    <th class='center'>
                        <?//= JHtml::_('grid.sort', 'Дата/Время замера', 'a.calculation_date', $listDirn, $listOrder); ?>
                        Дата / время замера
                    </th>
                    <th class='center'>
                        <?//= JHtml::_('grid.sort', 'Адрес', 'address', $listDirn, $listOrder); ?>
                        Адрес
                    </th>
                    <th class="center">
                        Примечание
                    </th>
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
                        if ($userId == $item->dealer_id || $user->dealer_id == $item->dealer_id):
                ?>
                        <tr class = "row" data-href="<?= JRoute::_('index.php?option=com_gm_ceiling&view=projectform&type=chief&id=' . (int)$item->id); ?>">
                            <td class="center one-touch">
                                <?= $item->id; ?>
                            </td>
                            <td class="center one-touch">
                                <? if ($item->calculation_date == "00.00.0000"): ?> -
                                <? else: echo $item->calculation_date;
                                endif;?><br><?
                                if ($item->calculation_time == "00:00-01:00" || $item->calculation_time == ""): ?> -
                                <? else: echo $item->calculation_time;
                                endif; ?>
                            </td>
                            <td class="center one-touch"><?= $item->address; ?></td>
                            <td><?= ($item->dealer_chief_note)?$item->dealer_chief_note:$item->gm_chief_note ;  ?></td>
                            <?php if(in_array(14, $groups)){ ?>
                                <td class="center one-touch delete"><button class = "btn btn-danger" data-id = "<?php echo $item->id;?>" type = "button"><i class="fa fa-trash-o" aria-hidden="true"></i></button></td>
                            <?php } ?>
                        </tr>
                    <? endif; ?>
                <? endforeach; ?>
            </tbody>
        </table>
        <input type="hidden" name="task" value=""/>
        <input type="hidden" name="boxchecked" value="0"/>
        <input type="hidden" name="filter_order" value="<?= $listOrder; ?>"/>
        <input type="hidden" name="filter_order_Dir" value="<?= $listDirn; ?>"/>
        <?= JHtml::_('form.token'); ?>
  <? else: ?>
        <h3 class="center">У вас нет заказов, не назначенных на монтаж!</h3>
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

  var $ = jQuery;
  $(window).resize(function(){
      if (screen.width <= '1024') {
          jQuery('#projectList').hide();
          jQuery('#projectListMobil').show();
      }
      else {
          jQuery('#projectList').show();
          jQuery('#projectListMobil').hide();
      }
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

  // вызовем событие resize
  $(window).resize();
</script>
