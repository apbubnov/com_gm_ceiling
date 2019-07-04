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

<h4 class="center" style="margin-bottom: 1em;">Не назначенные на монтаж или не запущенные в производство</h4>
<form action="<?php echo JRoute::_('index.php?option=com_gm_ceiling&view=projects&type=chiefprojects'); ?>" method="post" name="adminForm" id="adminForm">
	<? if (count($this->items) > 0 && empty($this->items->project_mounter)): ?>
        <table class="table table-striped one-touch-view g_table" id="projectList">
            <thead>
                <tr>
                    <th class='center'>
                        <i class="fa fa-check-circle"></i>
                    </th>
                    <th class='center'>
                        №
                    </th>
                    <th class='center'>
                        Дата / время замера
                    </th>
                    <th class='center'>
                        Адрес
                    </th>
                    <th class='center'>
                        Клиент
                    </th>
                    <?php if (in_array("14", $groups)):?>
                        <th class="center">
                            <i class="fas fa-trash-alt" aria-hidden="true"></i>
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
                        <tr data-href="<?= JRoute::_('index.php?option=com_gm_ceiling&view=projectform&type=chief&id=' . (int)$item->id); ?>">
                            <td><button class="btn btn-primary btn-sm" type="button" data-id="<?=$item->id;?>" name="btn_done"><i class="fa fa-check-circle"></i></button> </td>
                            <td class="center one-touch"><?= $item->id; ?></td>
                            <td class="center one-touch">
                                <? if ($item->project_calculation_date == '0000-00-00 00:00:00'): ?> -
                                <? else: echo $item->project_calculation_date;
                                endif;?>
                            </td>
                            <td class="center one-touch"><?= $item->project_info; ?><br><?= $item->client_contacts; ?></td>
                            <td class="center one-touch"><?= $item->client_name; ?></td>
                            <?php if(in_array(14, $groups)){ ?>
                                <td class="center one-touch delete"><button class = "btn btn-danger btn-sm" data-id = "<?php echo $item->id;?>" type = "button"><i class="fas fa-trash-alt" aria-hidden="true"></i></button></td>
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
        <h3 class="center">У вас нет заказов неназначенных на монтаж!</h3>
  <? endif; ?>
</form>

<script type="text/javascript">

  jQuery(document).ready(function () {

      jQuery("[name='btn_done']").click(function(){
          var td = jQuery( this ),
              tr = td.closest("tr");

          var input = jQuery( this ),
              input = input.closest("input"),
              project_sum = input.find(".project_sum");
          var button = jQuery( this );
          var type = "info";
          var subject = "Отметка стоимости договора №" + td.data("id");
          var text = "";

          text += "<div class='dop_info_block' style='font-size:15px;'><div class='center'>Укажите новую стоимость договора</div><div class='center'><input id='input_check' class='noty_input' style='margin-top: 5px;' value=''/></div></br>";
          text += "<div class='center'>Укажите новую стоимость расходных материалов</div><div class='center'><input id='input_material' class='noty_input' style='margin-top: 5px;' value=''/></div></br>";
          text += "<div class='center'>Укажите новую стоимость монтажных работ</div><div class='center'><input id='input_mounting' class='noty_input' style='margin-top: 5px;' value=''/></div>";
          text += "</div>";

          modal({
              type: 'primary',
              title: subject,
              text: text,
              size: 'small',
              buttons: [{
                  text: 'Выполнено', //Button Text
                  val: 0, //Button Value
                  eKey: true, //Enter Keypress
                  onClick: function(dialog) {
                      var input_value = jQuery("#input_check").val();
                      var input_mounting = jQuery("#input_mounting").val();
                      var input_material = jQuery("#input_material").val();
                      var check = jQuery("input[name='check_mount']:checked").val();
                      //Просчет прибыли

                      var profit = parseFloat(input_value) - (parseFloat(input_mounting)+parseFloat(input_mounting));
                      if (check == undefined) {
                          check = 1;
                      }
                      else check = 0;


                      jQuery.ajax({
                          type: 'POST',
                          url: "index.php?option=com_gm_ceiling&task=project.done",
                          data: {
                              project_id : td.data("id"),
                              new_value : input_value,
                              mouting_sum : input_mounting,
                              material_sum : input_material,
                              check: check
                          },
                          success: function(data){
                              if(check == 1) button.closest("td").html("<i class='fa fa-check' aria-hidden='true'></i> Выполнено");
                              var n = noty({
                                  theme: 'relax',
                                  layout: 'center',
                                  maxVisible: 5,
                                  type: "success",
                                  text: data
                              });
                              if(check == 0) setInterval(function() { location.reload();}, 1500);

                          },
                          dataType: "text",
                          timeout: 10000,
                          error: function(data){
                              console.log(data);
                              var n = noty({
                                  theme: 'relax',
                                  layout: 'center',
                                  maxVisible: 5,
                                  type: "error",
                                  text: "Ошибка при попытке сохранить отметку. Сервер не отвечает"
                              });
                          }
                      });
                      return 1;
                  }
              },
                  {
                      addClass: 'btn', text: 'Отмена', onClick: function($noty) {
                          $noty.close();
                      }
                  }
              ],
              callback: null,
              autoclose: false,
              center: true,
              closeClick: true,
              closable: true,
              theme: 'xenon',
              animate: true,
              background: 'rgba(0,0,0,0.35)',
              zIndex: 1050,
              buttonText: {
                  ok: 'Поставить',
                  cancel: 'Снять'
              },
              template: '<div class="modal-box"><div class="modal-inner"><div class="modal-title"><a class="modal-close-btn"></a></div><div class="modal-text"></div><div class="modal-buttons"></div></div></div>',
              _classes: {
                  box: '.modal-box',
                  boxInner: ".modal-inner",
                  title: '.modal-title',
                  content: '.modal-text',
                  buttons: '.modal-buttons',
                  closebtn: '.modal-close-btn'
              }

          });

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

</script>
