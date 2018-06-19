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
$dealer = JFactory::getUser($user->dealer_id);
$canCreate  = $user->dealer_id == 1;
$canEdit    = $user->dealer_id == 1;
$canCheckin = $user->dealer_id == 1;
$canChange  = $user->dealer_id == 1;
$canDelete  = $user->dealer_id == 1;
$mount = array(
			"mp1" => "Монтаж",
			"mp2" => "Люстра планочная",
			"mp3" => "Люстра большая",
			"mp4" => "Светильники",
			"mp5" => "Светильники квадратные",
			"mp6" => "Пожарная сигнализация",
			"mp7" => "Обвод трубы D > 120мм",
			"mp8" => "Обвод трубы D < 120мм",
			"mp9"=> "брус\разделит; брус\отбойник",
			"mp10" => "Вставка",
			"mp11" => "Шторный карниз на полотно",
			"mp12" => "Установка вытяжки",
			"mp13" => "Крепление в плитку",
			"mp14" => "Крепление в керамогранит",
			"mp15" => "Усиление стен",
			"mp16" => "Установка вентиляции",
			"mp17" => "Сложность доступа",
			"mp18" => "Дополнительный крепеж",
			"mp19" => "Установка диффузора",
            "distance" => "Выезд за город (1км)",
			"transport" => "Транспорт"
		);

		
		
?>

<form action="<?php echo JRoute::_('index.php?option=com_gm_ceiling&view=mount'); ?>" method="post"
      name="adminForm" id="adminForm">

	<?php echo JLayoutHelper::render('default_filter', array('view' => $this), dirname(__FILE__)); ?>
    <?=parent::getButtonBack();?>

		<table class="table calculation_sum">
		<?php if($user->dealer_type !=2) {?>
			<tr>
				<td><a id = "change_margin" class="btn btn-primary" style="float:right;">Изменить маржинальность</a></td>
			</tr>
				<?php } ?>
			<tbody class  = "new_margin" style="display: none; float:right;" >
				<tr>
					<td>
					<label id="jform_margin-lbl" for="jform_new_margin" >Новый процент маржинальности:<span class="star">&nbsp;*</span></label>
					<input name="new_margin" id="jform_new_margin" onkeypress="PressEnter(this.value, event)" value=""  placeholder="Новый % маржинальности"  type="text" style = "width:200px;">
					<input name="ismarginChange" value="0" type="hidden">
					</td>
				<td>
				<button id="update_margin" type="button" class="btn btn btn-success">Ок</button>
				</td>
				</tr>
			</tbody>
	</table>
	<table class="table table-striped g_table" id="mountList">
		<thead>
		<tr>
			<th class='center'>ID</th>
			<th class='center'>Наименование</th>
			<th class='center'>Себестоимость</th>
			<th class='center'>Цена для клиента</th>
			<?php if ($canEdit || $canDelete): ?>
				<th class="center">
				</th>
				<th class="center">
				</th>
			<?php endif; ?>
		</tr>
		</thead>
		<tfoot>
		
		</tfoot>
		<tbody>
		<?php
			$j = 1; 

	        
			$model = Gm_ceilingHelpersGm_ceiling::getModel('dealer_info');
			$margin = $model->getMargin('dealer_mounting_margin',$user->dealer_id);
			
		?>
		<?php foreach ($this->item as $key => $value) :  ?>
			<?php if(($key != 'id')&&($key != 'mp18')&&($key != 'user_id')) {?>
				<tr class="row<?php echo $i % 2; ?>">
					<td class="center">
						<?php echo $j++; ?>
					</td>
					<td class="center">
						<?php  echo $mount[$key]; ?>
					</td>
					<td class="center">        
						<?php echo $value; ?>
					</td>
					<td class="center">
						<?php echo round(($this->item->$key * 100 / (100 - $margin)), 2); ?>
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

<script>
    jQuery(document).ready(function(){
        jQuery("#change_margin").click(function(){
            jQuery(".new_margin").show();
        });

        jQuery("#update_margin").click(function(){
            jQuery("input[name='ismarginChange']").val(1);
            jQuery(".new_margin").hide();
        });
    });
	
	jQuery("#update_margin").click(function(){
		jQuery.ajax({
			type: 'POST',
			url: "index.php?option=com_gm_ceiling&task=update_margin",
			data: {
				new_margin: jQuery("#jform_new_margin").val(),
				type: 3
			},
			
			success: function(data){

                var answer = "Данные успешно изменены";

                if (data[0] === '{') {
                    var info = jQuery.parseJSON(data);
                    answer = info.answer_error;
                } else {
                    data = jQuery("<div/>", {"html":data}).find('#mountList').html()
                    jQuery("#mountList").html(data);
                }

                var n = noty({
                	timeout: 2000,
                    theme: 'relax',
                    modal: true,
                    layout: 'center',
                    text: answer
                });
			},
			dataType: "text",
			timeout: 10000,
			error: function(){
				var n = noty({
					theme: 'relax',
					layout: 'center',
					maxVisible: 5,
					type: "error",
					text: "Ошибка при попытке обновить процент маржинальности. Сервер не отвечает"
				});
			}					
		});
	});
</script>

<script language="JavaScript">
		function PressEnter(your_text, your_event) {
		  if(your_text != "" && your_event.keyCode == 13)
			jQuery("#update_margin").click();
		}
</script>