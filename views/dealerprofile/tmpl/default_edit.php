<?php
/**
 * @version    CVS: 1.0.0
 * @package    Com_Gm_ceiling
 * @author     Mikhail "Spectral Eye" Vinyukov <vms@itctl.ru>
 * @copyright  2016 Mikhail "Spectral Eye" Vinyukov
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');

$user       = JFactory::getUser();
$userId     = $user->get('id');
$model_dealer_info = Gm_ceilingHelpersGm_ceiling::getModel('dealer_info');
$margin = $model_dealer_info->getData();
$model_mount = Gm_ceilingHelpersGm_ceiling::getModel('mount');
$mount = $model_mount->getDataAll();
$gm_mount = $model_mount->getDataAll(1);

?>
<?php if(!$user->getDealerInfo()->update_check) {
	$user->setDealerInfo(["update_check" => true]);
}?>

<style>
	body {
		color: #414099;
	}
	.caption1 {
		text-align: center;
		padding: 15px 0;
		margin-bottom: 0;
		color: #414099;
	}
	.caption2 {
		text-align: center;
		height: auto;
		padding: 10px 0;
		border: 0;
		margin-bottom: 0;
		color: #414099;
	}
	input[type="text"] {
		padding: .5rem .75rem;
		border: 1px solid rgba(0,0,0,.15);
		border-radius: .25rem;
	}
	.control-label {
		margin-top: 7px;
		margin-bottom: 0;
	}
</style>

<?=parent::getButtonBack();?>

<div style="width: 100%; text-align: right; margin-top: 15px;">
	<a href="/index.php?option=com_users&view=profile&layout=edit" class="btn btn-large btn-primary">Изменить личные данные</a>
</div>
<form id="dealer_form" action="/index.php?option=com_gm_ceiling&task=dealer.updatedata" method="post"  class="form-validate form-horizontal" enctype="multipart/form-data">
	<h3 class="caption1">Редактирование маржинальности</h3>
	<h5 class="caption2">Укажите, какой процент прибыли от заказа Вы желаете получать</h5>
	<div class="row">
		<div class="col-md-4">
			<div class="control-group">
				<div class="control-label">
					<label id="jform_dealer_canvases_margin-lbl" for="jform_dealer_canvases_margin" class="hasTooltip required" >от полотен</label>
				</div>
				<div class="controls">
					<input type="text" name="jform[dealer_canvases_margin]" id="jform_dealer_canvases_margin" value=<?php echo ($margin->dealer_canvases_margin)?$margin->dealer_canvases_margin:0 ?>  class="required" style="width:100%;" size="3" required aria-required="true" />
				</div>
			</div>
		</div>
		<div class="col-md-4">
			<div class="control-group">
				<div class="control-label">
					<label id="jform_dealer_components_margin-lbl" for="jform_dealer_components_margin">от комплектующих</label>
				</div>
				<div class="controls">
					<input type="text" name="jform[dealer_components_margin]" id="jform_dealer_components_margin" value=<?php echo ($margin->dealer_components_margin)?$margin->dealer_components_margin:0 ?> class="required"style="width:100%;" size="3" required aria-required="true" />	
				</div>
			</div>
		</div>
		<div class="col-md-4">
			<div class="control-group">
				<div class="control-label">
					<label id="jform_dealer_mounting_margin-lbl" for="jform_dealer_mounting_margin">от монтажа</label>
				</div>
				<div class="controls">
					<input type="text" name="jform[dealer_mounting_margin]" id="jform_dealer_mounting_margin" value=<?php echo ($margin->dealer_mounting_margin)?$margin->dealer_mounting_margin:0 ?> class="required" style="width:100%;"size="3" required aria-required="true" />				
				</div>
			</div>
		</div>
	</div>
	<?php if ($user->dealer_type == 1 && $user->dealer_mounters == 0): ?>
		<h3 class="caption1">Редактирование прайса монтажа</h3>
		<button id = "fill_default" class="btn btn-primary" style="display:inline-block">Заполнить по умолчанию</button>
		<div class="row">
			<div class="col-md-4">
				<div class="control-group">
					<div class="control-label">
						<label id="jform_mp1-lbl" for="jform_mp1" >Монтаж</label>
					</div>
					<div class="controls">
						<input type="text" name="jform[mp1]" id="jform_mp1" value=<?php echo ($mount->mp1)?$mount->mp1:0 ?> class="required" style="width:100%;" size="3" required aria-required="true" />				
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<label id="jform_mp31-lbl" for="jform_mp31" >Монтаж (потолочный багет)</label>
					</div>
					<div class="controls">
						<input type="text" name="jform[mp31]" id="jform_mp31" value=<?php echo ($mount->mp31)?$mount->mp31:0 ?> class="required" style="width:100%;" size="3" required aria-required="true" />				
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<label id="jform_mp32-lbl" for="jform_mp32" >Монтаж (алюминиевый багет)</label>
					</div>
					<div class="controls">
						<input type="text" name="jform[mp32]" id="jform_mp32" value=<?php echo ($mount->mp32)?$mount->mp32:0 ?> class="required" style="width:100%;" size="3" required aria-required="true" />				
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<label id="jform_mp2-lbl" for="jform_mp2">Люстра планочная</label>
					</div>
					<div class="controls">
						<input type="text" name="jform[mp2]" id="jform_mp2" value=<?php echo ($mount->mp2)?$mount->mp2:0 ?> class="required" style="width:100%;" size="3" required aria-required="true" />				
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<label id="jform_mp3-lbl" for="jform_mp3" >Люстра большая</label>
					</div>
					<div class="controls">
						<input type="text" name="jform[mp3]" id="jform_mp3" value=<?php echo ($mount->mp3)?$mount->mp3:0 ?> class="required" style="width:100%;" size="3" required aria-required="true" />				
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<label id="jform_mp4-lbl" for="jform_mp4" class="hasTooltip required">Установка светильников</label>
					</div>
					<div class="controls">
						<input type="text" name="jform[mp4]" id="jform_mp4" value=<?php echo ($mount->mp4)?$mount->mp4:0 ?> class="required" style="width:100%;" size="3" required aria-required="true" />				
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<label id="jform_mp5-lbl" for="jform_mp5" >Светильники квадратные</label>
					</div>
					<div class="controls">
						<input type="text" name="jform[mp5]" id="jform_mp5" value=<?php echo ($mount->mp5)?$mount->mp5:0 ?> class="required" style="width:100%;" size="3" required aria-required="true" />				
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<label id="jform_mp6-lbl" for="jform_mp6" >Пожарная сигнализация</label>
					</div>
					<div class="controls">
						<input type="text" name="jform[mp6]" id="jform_mp6" value=<?php echo ($mount->mp6)?$mount->mp6:0 ?> class="required" style="width:100%;" size="3" required aria-required="true" />			
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<label id="jform_mp7-lbl" for="jform_mp7">Обвод трубы D > 120мм</label>
					</div>
					<div class="controls">
						<input type="text" name="jform[mp7]" id="jform_mp7" value=<?php echo ($mount->mp7)?$mount->mp7:0 ?> class="required" style="width:100%;" size="3" required aria-required="true" />				
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<label id="jform_mp8-lbl" for="jform_mp8" >Обвод трубы D < 120мм</label>
					</div>
					<div class="controls">
						<input type="text" name="jform[mp8]" id="jform_mp8" value=<?php echo ($mount->mp8)?$mount->mp8:0 ?> class="required" style="width:100%;" size="3" required aria-required="true" />			
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<label id="jform_mp9-lbl" for="jform_mp9" >Брус-разделитель, брус-отбойник</label>
					</div>
					<div class="controls">
						<input type="text" name="jform[mp9]" id="jform_mp9" value=<?php echo ($mount->mp9)?$mount->mp9:0 ?> class="required" style="width:100%;" size="3" required aria-required="true" />			
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<label id="jform_mp10-lbl" for="jform_mp10" >Вставка</label>
					</div>
					<div class="controls">
						<input type="text" name="jform[mp10]" id="jform_mp10" value=<?php echo ($mount->mp10)?$mount->mp10:0 ?> class="required" style="width:100%;" size="3" required aria-required="true" />						
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<label id="jform_mp11-lbl" for="jform_mp11" >Шторный карниз на полотно</label>
					</div>
					<div class="controls">
						<input type="text" name="jform[mp11]" id="jform_mp11" value=<?php echo ($mount->mp11)?$mount->mp11:0 ?> class="required" style="width:100%;" size="3" required aria-required="true" />						
					</div>
				</div>
			</div>
			<div class = "col-md-4">
				<div class="control-group">
					<div class="control-label">
						<label id="jform_mp12-lbl" for="jform_mp12" >Установка электровытяжки</label>
					</div>
					<div class="controls">
						<input type="text" name="jform[mp12]" id="jform_mp12" value=<?php echo $mount->mp12?$mount->mp12:0 ?> class="required" style="width:100%;" size="3" required aria-required="true" />						
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<label id="jform_mp13-lbl" for="jform_mp13" >Крепление в плитку</label>
					</div>
					<div class="controls">
						<input type="text" name="jform[mp13]" id="jform_mp13" value=<?php echo $mount->mp13?$mount->mp13:0 ?> class="required" style="width:100%;" size="3" required aria-required="true" />						
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<label id="jform_mp14-lbl" for="jform_mp14" >Крепление в керамогранит</label>
					</div>
					<div class="controls">
						<input type="text" name="jform[mp14]" id="jform_mp14" value=<?php echo $mount->mp14?$mount->mp14:0 ?> class="required"  style="width:100%;" size="3" required aria-required="true" />						
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<label id="jform_mp15-lbl" for="jform_mp15" >Усиление стен</label>
					</div>
					<div class="controls">
						<input type="text" name="jform[mp15]" id="jform_mp15" value=<?php echo $mount->mp15?$mount->mp15:0 ?> class="required" style="width:100%;" size="3" required aria-required="true" />					
						</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<label id="jform_mp16-lbl" for="jform_mp16" >Установка вентиляции</label>
					</div>
					<div class="controls">
						<input type="text" name="jform[mp16]" id="jform_mp16" value=<?php echo $mount->mp16?$mount->mp16:0 ?> class="required" style="width:100%;" size="3" required aria-required="true" />						
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<label id="jform_mp17-lbl" for="jform_mp17" >Сложность доступа</label>
					</div>
					<div class="controls">
						<input type="text" name="jform[mp17]" id="jform_mp17" value=<?php echo $mount->mp17?$mount->mp17:0 ?> class="required" style="width:100%;" size="3" required aria-required="true" />						
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<label id="jform_mp18-lbl" for="jform_mp18" >Дополнительный монтаж</label>
					</div>
					<div class="controls">
						<input type="text" name="jform[mp18]" id="jform_mp18" value=<?php echo $mount->mp18?$mount->mp18:0 ?> class="required" style="width:100%;" size="3" required aria-required="true" />						
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<label id="jform_mp19-lbl" for="jform_mp19" >Установка диффузора </label>
					</div>
					<div class="controls">
						<input type="text" name="jform[mp19]" id="jform_mp19" value=<?php echo $mount->mp19?$mount->mp19:0 ?> class="required" style="width:100%;" size="3" required aria-required="true" />						
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<label id="jform_mp22-lbl" for="jform_mp22" >Внутренний вырез для ПВХ </label>
					</div>
					<div class="controls">
						<input type="text" name="jform[mp22]" id="jform_mp22" value=<?php echo $mount->mp22?$mount->mp22:0 ?> class="required" style="width:100%;" size="3" required aria-required="true" />						
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<label id="jform_mp23-lbl" for="jform_mp23" >Переход уровня по прямой </label>
					</div>
					<div class="controls">
						<input type="text" name="jform[mp23]" id="jform_mp23" value=<?php echo $mount->mp23?$mount->mp23:0 ?> class="required" style="width:100%;" size="3" required aria-required="true" />						
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<label id="jform_mp24-lbl" for="jform_mp24" >Переход уровня по кривой </label>
					</div>
					<div class="controls">
						<input type="text" name="jform[mp24]" id="jform_mp24" value=<?php echo $mount->mp24?$mount->mp24:0 ?> class="required" style="width:100%;" size="3" required aria-required="true" />						
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<label id="jform_mp25-lbl" for="jform_mp25" >Переход уровня по прямой с нишей </label>
					</div>
					<div class="controls">
						<input type="text" name="jform[mp25]" id="jform_mp25" value=<?php echo $mount->mp25?$mount->mp25:0 ?> class="required" style="width:100%;" size="3" required aria-required="true" />						
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<label id="jform_mp26-lbl" for="jform_mp26" >Переход уровня по кривой с нишей </label>
					</div>
					<div class="controls">
						<input type="text" name="jform[mp26]" id="jform_mp26" value=<?php echo $mount->mp26?$mount->mp26:0 ?> class="required" style="width:100%;" size="3" required aria-required="true" />						
					</div>
				</div>
			</div>
			<div class = "col-md-4">
				<div class="control-group">
					<div class="control-label">
						<label id="jform_mp27-lbl" for="jform_mp27" >Слив воды </label>
					</div>
					<div class="controls">
						<input type="text" name="jform[mp27]" id="jform_mp27" value=<?php echo $mount->mp27?$mount->mp27:0 ?> class="required" style="width:100%;" size="3" required aria-required="true" />						
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<label id="jform_mp30-lbl" for="jform_mp30" >Парящий потолок</label>
					</div>
					<div class="controls">
						<input type="text" name="jform[mp30]" id="jform_mp30" value=<?php echo $mount->mp30?$mount->mp30:0 ?> class="required" style="width:100%;" size="3" required aria-required="true" />						
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<label id="jform_mp33-lbl" for="jform_mp33" >Монтаж (ткань) </label>
					</div>
					<div class="controls">
						<input type="text" name="jform[mp33]" id="jform_mp33" value=<?php echo $mount->mp33?$mount->mp33:0 ?> class="required" style="width:100%;" size="3" required aria-required="true" />						
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<label id="jform_mp34-lbl" for="jform_mp34" >Установка люстры (ткань)</label>
					</div>
					<div class="controls">
						<input type="text" name="jform[mp34]" id="jform_mp34" value=<?php echo $mount->mp34?$mount->mp34:0 ?> class="required" style="width:100%;" size="3" required aria-required="true" />						
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<label id="jform_mp36-lbl" for="jform_mp36" >Установка светильников (ткань)</label>
					</div>
					<div class="controls">
						<input type="text" name="jform[mp36]" id="jform_mp36" value=<?php echo $mount->mp36?$mount->mp36:0 ?> class="required" style="width:100%;" size="3" required aria-required="true" />						
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<label id="jform_mp37-lbl" for="jform_mp37" >Светильники квадратные (ткань)</label>
					</div>
					<div class="controls">
						<input type="text" name="jform[mp37]" id="jform_mp37" value=<?php echo $mount->mp37?$mount->mp37:0 ?> class="required" style="width:100%;" size="3" required aria-required="true" />						
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<label id="jform_mp38-lbl" for="jform_mp38" >Пожарная сигнализация (ткань)</label>
					</div>
					<div class="controls">
						<input type="text" name="jform[mp38]" id="jform_mp38" value=<?php echo $mount->mp38?$mount->mp38:0 ?> class="required" style="width:100%;" size="3" required aria-required="true" />						
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<label id="jform_mp40-lbl" for="jform_mp40" >Обвод трубы D < 120мм (ткань)</label>
					</div>
					<div class="controls">
						<input type="text" name="jform[mp40]" id="jform_mp40" value=<?php echo $mount->mp40?$mount->mp40:0 ?> class="required" style="width:100%;" size="3" required aria-required="true" />						
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<label id="jform_mp41-lbl" for="jform_mp41" >Шторный карниз на полотно (ткань)</label>
					</div>
					<div class="controls">
						<input type="text" name="jform[mp41]" id="jform_mp41" value=<?php echo $mount->mp41?$mount->mp41:0 ?> class="required" style="width:100%;" size="3" required aria-required="true" />						
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<label id="jform_mp42-lbl" for="jform_mp42" >Установка вентиляции (ткань)</label>
					</div>
					<div class="controls">
						<input type="text" name="jform[mp42]" id="jform_mp42" value=<?php echo $mount->mp42?$mount->mp42:0 ?> class="required" style="width:100%;" size="3" required aria-required="true" />						
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<label id="jform_mp43-lbl" for="jform_mp43" >Обработка каждого угла (ткань)</label>
					</div>
					<div class="controls">
						<input type="text" name="jform[mp43]" id="jform_mp43" value=<?php echo $mount->mp43?$mount->mp43:0 ?> class="required" style="width:100%;" size="3" required aria-required="true" />						
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<label id="jform_transport-lbl" for="jform_transport" >Транспортные расходы</label>
					</div>
					<div class="controls">
						<input type="text" name="jform[transport]" id="jform_transport" value=<?php echo $mount->transport?$mount->transport:0 ?> class="required" style="width:100%;" size="3" required aria-required="true" />						
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<label id="jform_distance-lbl" for="jform_distance" >Выезд за город</label>
					</div>
					<div class="controls">
						<input type="text" name="jform[distance]" id="jform_distance" value=<?php echo $mount->distance?$mount->distance:0 ?> class="required" style="width:100%;" size="3" required aria-required="true" />						
					</div>
				</div>
			</div>
		</div>
		<h3 class="caption1">Минимальная сумма заказа</h3>
		<div class="row">
			<div class="col-md-4">
				<div class="control-group">
					<div class="control-label">
						<label id="jform_min_sum-lbl" for="jform_min_sum" >Минимальная сумма</label>
					</div>
					<div class="controls">
						<input type="text" name="jform[min_sum]" id="jform_min_sum" value=<?php echo ($mount->min_sum)?$mount->min_sum:0 ?> class="required" style="width:100%;" size="3" required aria-required="true" />				
					</div>
				</div>
			</div>
		</div>
	<?php endif ?>
	<div  class = "col-md-12" style="margin-top:15px;">
		<div  class = "col-md-4">
		</div>
		<div  class = "col-md-4">
			<button class="btn btn-primary" style="width:100%;"> Сохранить </button>
		</div>
	</div>	
</form>
<!-- <script>
	jQuery(document).ready(function(){
		//var gm_count = <?php //echo $gm_mount;?>;
		console.log(gm_count);
	});
</script> -->