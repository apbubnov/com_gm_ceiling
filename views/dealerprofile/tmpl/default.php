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

?>

<form id="dealer_form" action="/index.php?option=com_gm_ceiling&task=dealer.updatedata" method="post"  class="form-validate form-horizontal" enctype="multipart/form-data">
	<h2>
		Редактирование маржинальности и прайса монтажа
	</h2>

	<div class = "col-md-4"></div>
	<div class = "col-md-4">

	<div class="control-group">
		<div class="control-label">
			<label id="jform_dealer_canvases_margin-lbl" for="jform_dealer_canvases_margin" class="hasTooltip required" >Маржинальность на полотна</label>
		</div>
		<div class="controls">
			<input type="text" name="jform[dealer_canvases_margin]" id="jform_dealer_canvases_margin" value=<?php echo $user->dealer_canvases_margin ?>  class="required" style="width:100%;" size="3" required aria-required="true" />
		</div>
	</div>
		<div class="control-group">
			<div class="control-label">
				<label id="jform_dealer_components_margin-lbl" for="jform_dealer_components_margin">Маржинальность на комплектующие</label>
			</div>
			<div class="controls">
				<input type="text" name="jform[dealer_components_margin]" id="jform_dealer_components_margin" value=<?php echo $user->dealer_components_margin ?> class="required"style="width:100%;" size="3" required aria-required="true" />	
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<label id="jform_dealer_mounting_margin-lbl" for="jform_dealer_mounting_margin">Маржинальность на монтаж></label>
			</div>
			<div class="controls">
				<input type="text" name="jform[dealer_mounting_margin]" id="jform_dealer_mounting_margin" value=<?php echo $user->dealer_mounting_margin ?> class="required" style="width:100%;"size="3" required aria-required="true" />				
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<label id="jform_mp1-lbl" for="jform_mp1" >	Монтаж</label>
			</div>
			<div class="controls">
				<input type="text" name="jform[mp1]" id="jform_mp1" value=<?php echo $user->mp1 ?> class="required" style="width:100%;" size="3" required aria-required="true" />				
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<label id="jform_mp2-lbl" for="jform_mp2">Люстра планочная</label>
			</div>
			<div class="controls">
				<input type="text" name="jform[mp2]" id="jform_mp2" value=<?php echo $user->mp2 ?> class="required" style="width:100%;" size="3" required aria-required="true" />				
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<label id="jform_mp3-lbl" for="jform_mp3" >	Люстра большая</label>
			</div>
			<div class="controls">
				<input type="text" name="jform[mp3]" id="jform_mp3" value=<?php echo $user->mp3 ?> class="required" style="width:100%;" size="3" required aria-required="true" />				
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<label id="jform_mp4-lbl" for="jform_mp4" class="hasTooltip required">	Установка светильников</label>
			</div>
			<div class="controls">
				<input type="text" name="jform[mp4]" id="jform_mp4" value=<?php echo $user->mp4 ?> class="required" style="width:100%;" size="3" required aria-required="true" />				
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<label id="jform_mp5-lbl" for="jform_mp5" >	Светильники квадратные</label>
			</div>
			<div class="controls">
				<input type="text" name="jform[mp5]" id="jform_mp5" value=<?php echo $user->mp5 ?> class="required" style="width:100%;" size="3" required aria-required="true" />				
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<label id="jform_mp6-lbl" for="jform_mp6" >Пожарная сигнализация</label>
			</div>
			<div class="controls">
				<input type="text" name="jform[mp6]" id="jform_mp6" value=<?php echo $user->mp6 ?> class="required" style="width:100%;" size="3" required aria-required="true" />			
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<label id="jform_mp7-lbl" for="jform_mp7">Обвод трубы D > 120мм</label>
			</div>
			<div class="controls">
				<input type="text" name="jform[mp7]" id="jform_mp7" value=<?php echo $user->mp7 ?> class="required" style="width:100%;" size="3" required aria-required="true" />				
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<label id="jform_mp8-lbl" for="jform_mp8" >	Обвод трубы D < 120мм</label>
			</div>
			<div class="controls">
				<input type="text" name="jform[mp8]" id="jform_mp8" value=<?php echo $user->mp8 ?> class="required" style="width:100%;" size="3" required aria-required="true" />			
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<label id="jform_mp9-lbl" for="jform_mp9" >Брус-разделитель, брус-отбойник</label>
			</div>
			<div class="controls">
				<input type="text" name="jform[mp9]" id="jform_mp9" value=<?php echo $user->mp9 ?> class="required" style="width:100%;" size="3" required aria-required="true" />			
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<label id="jform_mp10-lbl" for="jform_mp10" >Вставка</label>
			</div>
			<div class="controls">
				<input type="text" name="jform[mp10]" id="jform_mp10" value=<?php echo $user->mp10 ?> class="required" style="width:100%;" size="3" required aria-required="true" />						
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<label id="jform_mp11-lbl" for="jform_mp11" >Шторный карниз на полотно</label>
			</div>
			<div class="controls">
				<input type="text" name="jform[mp11]" id="jform_mp11" value=<?php echo $user->mp11 ?> class="required" style="width:100%;" size="3" required aria-required="true" />						
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<label id="jform_mp12-lbl" for="jform_mp12" >Установка вытяжки</label>
			</div>
			<div class="controls">
				<input type="text" name="jform[mp12]" id="jform_mp12" value=<?php echo $user->mp12 ?> class="required" style="width:100%;" size="3" required aria-required="true" />						
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<label id="jform_mp13-lbl" for="jform_mp13" >Крепление в плитку</label>
			</div>
			<div class="controls">
				<input type="text" name="jform[mp13]" id="jform_mp13" value=<?php echo $user->mp13 ?> class="required" style="width:100%;" size="3" required aria-required="true" />						
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<label id="jform_mp14-lbl" for="jform_mp14" >Крепление в керамогранит</label>
			</div>
			<div class="controls">
				<input type="text" name="jform[mp14]" id="jform_mp14" value=<?php echo $user->mp14 ?> class="required"  style="width:100%;" size="3" required aria-required="true" />						
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<label id="jform_mp15-lbl" for="jform_mp15" >Усиление стен</label>
			</div>
			<div class="controls">
				<input type="text" name="jform[mp15]" id="jform_mp15" value=<?php echo $user->mp15 ?> class="required" style="width:100%;" size="3" required aria-required="true" />					
				</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<label id="jform_mp16-lbl" for="jform_mp16" >Установка вентиляции</label>
			</div>
			<div class="controls">
				<input type="text" name="jform[mp16]" id="jform_mp16" value=<?php echo $user->mp16 ?> class="required" style="width:100%;" size="3" required aria-required="true" />						
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<label id="jform_mp17-lbl" for="jform_mp17" >Сложность доступа</label>
			</div>
			<div class="controls">
				<input type="text" name="jform[mp17]" id="jform_mp17" value=<?php echo $user->mp17 ?> class="required" style="width:100%;" size="3" required aria-required="true" />						
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<label id="jform_mp18-lbl" for="jform_mp18" >Дополнительный монтаж</label>
			</div>
			<div class="controls">
				<input type="text" name="jform[mp18]" id="jform_mp18" value=<?php echo $user->mp18 ?> class="required" style="width:100%;" size="3" required aria-required="true" />						
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<label id="jform_mp19-lbl" for="jform_mp19" >??? </label>
			</div>
			<div class="controls">
				<input type="text" name="jform[mp19]" id="jform_mp19" value=<?php echo $user->mp19 ?> class="required" style="width:100%;" size="3" required aria-required="true" />						
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<label id="jform_transport-lbl" for="jform_transport" >Транспортные расходы</label>
			</div>
			<div class="controls">
				<input type="text" name="jform[transport]" id="jform_transport" value=<?php echo $user->transport ?> class="required" style="width:100%;" size="3" required aria-required="true" />						
			</div>
		</div>
		<br>
		<button class="btn btn-primary" style="width:100%;"> Сохранить </button>
	</div>
	
</form>
