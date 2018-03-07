<?php
/**
 * @version    CVS: 0.1.2
 * @package    Com_Gm_ceiling
 * @author     SpectralEye <Xander@spectraleye.ru>
 * @copyright  2016 SpectralEye
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
	defined('_JEXEC') or die;
	JHtml::_('behavior.keepalive');
	//JHtml::_('behavior.tooltip');
	JHtml::_('behavior.formvalidation');
	$lang = JFactory::getLanguage();
	$lang->load('com_gm_ceiling', JPATH_SITE);
	$doc = JFactory::getDocument();
	$doc->addScript(JUri::base() . '/media/com_gm_ceiling/js/form.js');
	$new = 1;
	if($this->item->id > 0) {
		$new = 0;
	}
	$jinput = JFactory::getApplication()->input;
	$project_id = $jinput->getString('project_id', NULL);
	$type = $jinput->getString('type', NULL);
	$subtype = $jinput->getString('subtype', NULL);
	$user = JFactory::getUser();
	if($user->guest) {
		$login_link = JRoute::_("index.php?option=com_users&view=login", false);
	} else {
		$login_link = JRoute::_("index.php?option=com_gm_ceiling&task=mainpage", false);
	}
	$project_model = Gm_ceilingHelpersGm_ceiling::getModel('project');
	$project = $project_model->getData($project_id);
	$extra_components_array = Gm_ceilingHelpersGm_ceiling::decode_extra($this->item->extra_components);
    $components_stock_array = Gm_ceilingHelpersGm_ceiling::decode_stock($this->item->components_stock);
	$extra_mounting_array = Gm_ceilingHelpersGm_ceiling::decode_extra($this->item->extra_mounting);
	$calc_id = $jinput->get('id','','INT');
	$calc_id = empty($calc_id)?0:$calc_id;
	$del_flag = 1;
	$rek = $jinput->getInt('rek', 8);
	$user_group = $user->groups;
?>

<form method="POST" action="/sketch/index.php" style="display: none" id="form_url">
	<input name="url" id="url" value="" type="hidden">
	<input name="user_id" id="user_id" value=<?php echo "\"".$user->id."\"";?> type="hidden">
	<input name = "width" id = "width" value = "" type = "hidden">
	<input name = "texture" id = "texture" value = "" type = "hidden">
	<input name = "color" id = "color" value = "" type = "hidden">
	<input name = "manufacturer" id = "manufacturer" value = "" type = "hidden">
	<input name = "calc_title" id = "calc_title" value="" type = "hidden">
    <input name = "auto" id = "auto" value="" type = "hidden">
    <input name = "walls" id = "walls" value="" type= "hidden">
    <input name = "n4" id = "n4" value ="" type ="hidden">
    <input name = "n5" id = "n5" value ="" type ="hidden">
    <input name = "n9" id = "n9" value ="" type ="hidden">
</form>

<?php
	if($type === "calculator" || $type === "gmcalculator" || $type === "gmmanager" || $type === "manager" )
	{
		echo ('<div style="margin: 0 0 30px 30px;">'.parent::getButtonBack().'</div>');
	}
	if ($_SERVER['HTTP_REFERER'] == 'http://test1.gm-vrn.ru/sketch/index.php'){
		echo "<script>BackPage = function() { window.history.go(-3); }</script>";
	}
?>
<div class="calculation-edit front-end-edit">
	<form id="form-calculation" action="<?php echo JRoute::_('index.php?option=com_gm_ceiling&task=calculation.save'); ?>" method="post" class="form-validate form-horizontal" enctype="multipart/form-data">
		<?php if ($this->type === "guest") { ?>
			<div style="display: inline-block; width: 100%;">
				<!--<a href="<?php //echo $login_link; ?>" class="btn btn-secondary" style="float: right; margin: 0px 30px 0 0;"><i class="fa fa-lock" aria-hidden="true"></i></a>-->
			</div>
			<div class="show_before_calculate" style="margin-bottom: 1em;">
				<h1>Натяжные потолки от производителя без посредников дешевле на 30%</h1>
			</div>
			<input type="hidden" name="jform[rek]" value="<?php echo  $rek; ?>" />
		<?php } else { ?>
			<!--<a href="<?php //echo $login_link; ?>" class="btn btn-secondary" style="float: right; margin: -67px 30px 0 0;"><i class="fa fa-lock" aria-hidden="true"></i></a>-->
		<?php } ?>
		<input id="jform_id" type="hidden" name="jform[id]" value="<?php echo $this->item->id; ?>" />
		<input id="flag_auto" type="hidden" value="0"/>
		<input type="hidden" name="jform[public]" value="1" />
		<input type="hidden" name="jform[ordering]" value="<?php echo $this->item->ordering; ?>" />
		<input type="hidden" name="jform[state]" value="<?php echo $this->item->state; ?>" />
		<input type="hidden" name="jform[checked_out]" value="<?php echo $this->item->checked_out; ?>" />
		<input type="hidden" name="jform[checked_out_time]" value="<?php echo $this->item->checked_out_time; ?>" />
		<?php if($new) { 
			if($type === "calculator" || $type === "manager") {?>
				<input type="hidden" name="jform[dealer_id]" value="<?php echo $user->dealer_id; ?>" />
			<?php }?>
			<?php if($user->guest) { ?>
				<input type="hidden" name="jform[dealer_id]" value="2" />
			<?php } else { ?>
				<input type="hidden" name="jform[dealer_id]" value= "<?php echo $user->dealer_id; ?> "/>
			<?php } ?>
		<?php } else { ?>
			<?php if($user->guest) { ?>
				<input type="hidden" name="jform[dealer_id]" value="2" />
			<?php } else { ?>
				<input type="hidden" name="jform[dealer_id]" value= "<?php echo $user->dealer_id; ?>" />
			<?php } ?>
		<?php } ?>
		<input type="hidden" name="jform[type]" value="<?php echo $type; ?>" />
		<?php if($new) { ?>
			<input id="jform_project_id" type="hidden" name="jform[project_id]" value="<?php echo $project_id; ?>" />
		<?php } else { ?>
			<input id="jform_project_id" type="hidden" name="jform[project_id]" value="<?php echo $this->item->project_id; ?>" />
		<?php } ?>
		<input id="jform_sketch_name" type="hidden" name="jform[sketch_name]" value="" />
		<input id="jform_cut_name" type="hidden" name="jform[cut_name]" value="" />
		<input id="jform_original_name" type="hidden" name="jform[original_name]" value="" />
		<input id="jform_cuts" type="hidden" name="jform[cuts]" value="" />
		<input id="jform_components_sum" type="hidden" name="jform[components_sum]" value="" />
		<input id="jform_canvases_sum" type="hidden" name="jform[canvases_sum]" value="" />
		<input id="jform_gm_mounting_sum" type="hidden" name="jform[gm_mounting_sum]" value="" />
		<input id="jform_dealer_mounting_sum" type="hidden" name="jform[dealer_mounting_sum]" value="" />
		<input name="jform[created_by]" value="<?php echo $this->item->created_by; ?>" type="hidden">
		<input name="jform[modified_by]" value="<?php echo $this->item->modified_by; ?>" type="hidden">
		<input name="jform[transport]" value="<?php echo $this->item->transport; ?>" type="hidden">
		<input id="jform_n1" class="n1" name="jform[n1]" value="28" type="hidden">
		<?php if ($user->dealer_type !=2 ){
			$del_flag = 1;
		} ?>
		<?php if(!$new || $type === "gmcalculator" || $type === "calculator"||$type === "gmmanager"  ) { ?>
			<div class="form-group"> 
				<div class="container">
					<div class="col-sm-4"></div>
					<div class="row sm-margin-bottom">
						<div class="col-sm-4">
							<table>	
								<tr>
									<td>
										<label id="jform_calculation_title-lbl" for="jform_calculation_title" class="">Название расчета:</label>
									</td>
									<td>
										<a class="help"><img src="/images/hint.png" alt="подсказка" style="margin-bottom: 16px" /><span class="airhelp">Назовите чертеж, по названию комнаты, в которой производится замер, что бы легче было потом ориентироваться. Например: "Спальня" </span></a>
									</td>
								</tr>
							</table>
							<input id="jform_calculation_title" name="jform[calculation_title]" value="<?php echo $this->item->calculation_title; ?>" class="form-control" type="text">
						</div>
					</div>
					<div class="col-sm-4"></div>
				</div>
			</div>
		<?php } ?>
		<!-- характеристики полотна -->
		<div class="container">
			<div class="col-sm-4"></div>
			<div class="row sm-margin-bottom">
				<div class="col-sm-4">
					<h3>Характеристики полотна</h3>		
				</div>
			</div>
			<div class="col-sm-4"></div>
		</div>
		<!-- Фактура -->
		<div class="container">
			<div class="col-sm-4"></div>
			<div class="row sm-margin-bottom">
				<div class="col-sm-4">
					<table>
						<tr>
							<td>
								<label id="jform_n2-lbl" for="jform_n2">Выберите фактуру полотна</label>
							</td>
							<td> 
								<a class="help" ><img src="/images/hint.png" alt="подсказка" style="margin-bottom: 16px" />
									<span class="airhelp"><strong>Выберите фактуру для Вашего будущего потолка</strong>
										<ul>
											<li>Матовый больше похож на побелку.</li>
											<li>Сатин – на, крашенный потолок.</li>
											<li>Глянец – имеет легкий отблеск.</li>
										</ul>
									</span>
								</a>
							</td>
						</tr>
					</table>
					<select id="jform_n2" name="jform[n2]" class="form-control inputbox ">
						<option value="" selected="">- Выберите фактуру -</option>
					</select>
					<input id="jform_n2_hidden" class="n2" name="jform[n2_hidden]" value="<?php echo $this->item->n2; ?>" type="hidden">
				</div>
			</div>
			<div class="col-sm-4"></div>
		</div>
		<!-- Ширина -->
		<div class="container" style ="display:none;">
			<div class="col-sm-4"></div>
			<div class="row sm-margin-bottom">
				<div class="col-sm-4">
					<div class="form-group">
						<select id="jform_n3" name="jform[n3]" class="form-control inputbox " disabled=""><option value="<?php echo $this->item->n2; ?>" selected="">- Выберите ширину материала -</option></select>
					</div>
				</div>
			</div>
			<div class="col-sm-4"></div>
		</div>
		<!-- Цвет -->
		<div class="container">
			<div class="col-sm-4"></div>
			<div class="col-sm-4">
				<?php  if ($this->item->color > 0) {  ?> 
					<?php $color_model = Gm_ceilingHelpersGm_ceiling::getModel('color'); ?>
					<?php $color = $color_model->getData($this->item->color); ?>
					<?php $imgurl = $color->file;?>
				<?php } ?>
				<div style="width: 100%; text-align: left;">
					<label id="jform_color_switch-lbl" for="color_switch" style="display: none; text-align: left !important;">Выберите цвет:</label>
				</div>
				<button id="color_switch" class="btn btn-primary btn-width" type="button" style="display: none; margin-bottom: 1.5em;">Цвет <img id="color_img" class="calculation_color_img" style='width: 50px; height: 30px;' src="/<?php if(isset($imgurl)){ echo $imgurl; } ?>" alt="" /></button>
				<input id="jform_color" name="jform[color]" value="<?php echo $this->item->color; ?>" type="hidden">
			</div>
			<div class="col-sm-4">
			</div>
		</div>
		<!-- Производитель -->
		<div class="container">
			<div class="col-sm-4"></div>
			<div class="row sm-margin-bottom">
				<div class="col-sm-4">
					<div class="form-group">
						<table>
							<tr>
								<td>
									<label id="jform_proizv-lbl" for="jform_proizv">Выберите производителя</label>
								</td>
								<td>
									<a class="help" ><img src="/images/hint.png" alt="подсказка" style="margin-bottom: 16px" /><span class="airhelp">От производителя материала зависит качество потолка и его цена!</span></a>
								</td>
							</tr>
						</table>
						<select id="jform_proizv" name="jform[proizv]" class="form-control inputbox " disabled="">
							<option value="<?=($this->item->n3)?($this->item->n3):"";?>" selected=""><?=($this->item->n3)?($this->item->n3):"- Выберите производителя материала -";?></option>
						</select>
						<input id="jform_proizv_hidden" class="n3" name="jform[proizv_hidden]" value="" type="hidden">
						<input id="jform_n3_hidden" class="n3" name="jform[n3]" value="<?php echo $this->item->n3;?>" type="hidden">
					</div>
				</div>
			</div>
			<div class="col-sm-4"></div>
		</div>
		<!-- размеры -->
		<div class="container">
			<div class="col-sm-4"></div>
			<div class="row sm-margin-bottom">
				<div class="col-sm-4">
					<h3>Размеры помещения</h3>
				</div>
			</div>
			<div class="col-sm-4"></div>
		</div>
		<!-- начертить -->
		<div class="container">
			<div class="row sm-margin-bottom">
				<div class="col-sm-4"></div>
				<div class="col-sm-4 ">
					<button id="sketch_switch" class="btn btn-primary btn-big" type="button">Начертить потолок</button>
					<div id="sketch_image_block">
						<?php
							if ($this->item->id > 0)
							{
								$filename = "/calculation_images/" . md5("calculation_sketch" . $this->item->id) . ".svg";
						?>
							<img id="sketch_image" src="<?php echo $filename.'?t='.time(); ?>">
						<?php 		
							}
							else
							{
						?>
							<img id="sketch_image" hidden = true src="/">
						<?php 	
							}
						?>
					</div>
				</div>
				<div class="col-sm-4"></div>
			</div>
		</div>
		<!-- S,P,углы -->
		<div class="container">
			<div id="data-wrapper">
				<div class="row sm-margin-bottom">
					<div class="col-sm-4"></div>
					<div class="col-sm-4 xs-center">
						<table style="width: 100%;">
							<tr>
								<td width=35%>
									<label id="jform_n4-lbl" for="jform_n4" class="center" > S = </label>
								</td>
								<td width=55%>
									<input name="jform[n4]" class="form-control-input" id="jform_n4" data-next="#jform_n5" value="<?php echo $this->item->n4;  ?>" placeholder="Площадь комнаты"  readonly  type="tel"> 
								</td>
								<td width=10%>
									<label for="jform_n4" class="control-label"> м<sup>2 </sup></label>
								</td>
							</tr>
							<tr>
								<td width=35%>
									<label id="jform_n5-lbl" for="jform_n5" class="center" > P = </label>
								</td>
								<td width=55%>
									<input name="jform[n5]" class="form-control-input" id="jform_n5" data-next="#jform_n9" value="<?php echo $this->item->n5; ?>" placeholder="Периметр комнаты" readonly  type="tel"> 
								</td>
								<td width=10%>
									<label for="jform_n5" class="control-label"> м </label>
								</td>
							</tr>
							<tr>
								<td width=35%>
									<label id="jform_n9-lbl" for="jform_n9" class="center"> Кол-во углов = </label>
								</td>
								<td width=55%>
									<input name="jform[n9]" id="jform_n9" data-next="#jform_n27" value="<?php echo $this->item->n9; ?>" class="form-control-input" placeholder="Кол-во углов"  readonly  type="tel"> 
								</td>
								<td width=10%>
									<label for="jform_n9" class="control-label">шт.</label>
								</td>
							</tr>
						</table>
						<input name = "jform[offcut_square]" id = "jform_offcut_square" value = "<?php echo $this->item->offcut_square; ?>" type="hidden">
					</div>
					<div class="col-sm-4"></div>
				</div>
			</div>
		</div>
		<!-- Багет -->
		<div class="container" id="block_n28">
			<div class="row">
				<div class="col-sm-4" style="padding-left: 0px;">
					<div class="form-group" style="margin-bottom: 30px;">
						<h3>Выберите багет</h3>
					</div>
				</div>
				<div class="col-sm-4" style="padding-right: 0px;">
					<div class="form-group" style="text-align: left; margin-left: calc(50% - 81px);">
                        <div style="display: inline-block;"><input name="jform[n28]" id="jform_n28" class="radio" value="0" type="radio" <?php if ($this->item->n28 == 0) echo "checked='checked'" ?>>
                            <label for="jform_n28"> Обычный багет</label>
                        </div>
                        <div style="display: inline-block;"><input name="jform[n28]" id="jform_n28_1" class="radio" value="1" type="radio" <?php if ($this->item->n28 == 1) echo "checked='checked'" ?>>
                            <label for="jform_n28_1"> Потолочный багет</label>
                        </div>
                        <div style="display: inline-block;"><input name="jform[n28]" id="jform_n28_2" class="radio" value="2" type="radio" <?php if ($this->item->n28 == 2) echo "checked='checked'" ?>>
                            <label for="jform_n28_2"> Алюминиевый багет</label>
                        </div>
					</div>
				</div>
			</div>
		</div>
		<!-- вставка -->
		<div class="container" id="block_n6">
			<div class="row sm-margin-bottom">
				<div class="col-sm-4"></div>
				<div class="col-sm-4" style="text-align:-webkit-center">
					<table style="margin-bottom: 30px;">
						<tr>
							<td>
								<h3>Декоративная вставка</h3>
							</td>
							<td>
								<a class="help" ><img src="/images/hint.png" alt="подсказка" style="margin-bottom: 16px" /><span class="airhelp"><img src="/images/vstavka.png" width="380" height="250"/>
									<br>Между стеной и натяжным потолком после монтажа остается технологический зазор 5мм, который закрывается декоративной вставкой 
								</span></a>
							</td>
						</tr>
					</table>
					<div class="form-group" style="text-align: left; margin-left: calc(50% - 72px);">
						<?php
							if ($this->item->n6 > 0) {
								$color_model_1 = Gm_ceilingHelpersGm_ceiling::getModel('components');
								$color_1 = $color_model_1->getColorId($this->item->n6);
								$color_image_1 = $color_1[0]->file;
								$color_id_1 = $color_1->id;
							}
						?>
                        <div style="display: inline-block;">
                            <input name="radio" id="jform_n6" class="radio" value="<?=($this->item->n6)?$this->item->n6:'314'?>" type="radio" <?php if($this->item->n6 == 314) echo "checked=\"checked\""?>>
                            <label for="jform_n6">Белая вставка</label>
                        </div>
                        <br>
                        <div style="display: inline-block;">
                            <input name="radio" id="jform_n6_1" class="radio" value="<?=($this->item->n6)?$this->item->n6:''?>" type="radio" <?php if(!empty($this->item->n6) && $this->item->n6 != 314) echo "checked=\"checked\""?>>
                            <label for="jform_n6_1">Цветная вставка</label>
                        </div>
                        <br>
                        <div style="display: inline-block;">
                            <input name="radio" id="jform_n6_2" class="radio" value="<?=($this->item->n6)?$this->item->n6:'0'?>" type="radio" <?php if(empty($this->item->n6)) echo "checked=\"checked\""?>>
                            <label for="jform_n6_2">Вставка не нужна</label>
                        </div>
                    </div>
                	<div class="col-sm-4"></div>
				</div>
				<?php if(empty($this->item->n6) || $this->item->n6 == 0 ||$this->item->n6 ==314) { ?>
					<div class="container">
						<div class="col-sm-4"></div>
						<div class="col-sm-4">
							<div style="width: 100%; text-align: left;">
								<label id="jform_color_switch-lbl_1" for="color_switch_1" style="display: none; text-align: center;">Выберите цвет:</label>
							</div>
							<button id="color_switch_1" class="btn btn-primary btn-width" type="button" style="display: none;">Цвет <img id="color_img_1" class="calculation_color_img" style='width: 50px; height: 30px;'src="<?php if(isset($color_image_1)){ echo $color_image_1; } ?>" alt="" /></button>
							<input id="jform_color_1" name="jform[n6]" value="<?php echo $this->item->n6;?>" type="hidden">
						</div>
						<div class="col-sm-4"></div>
					</div>
				<?php } else { ?>
					<div class="container">
						<div class="col-sm-4"></div>
						<div class="col-sm-4">
							<div style="width: 100%; text-align: left;">
								<label id="jform_color_switch-lbl_1" for="color_switch_1" style="text-align: center;">Выберите цвет:</label>
							</div>
							<button id="color_switch_1" class="btn btn-primary btn-width" type="button" style="">Цвет <img id="color_img_1" class="calculation_color_img" style='width: 50px; height: 30px;'src="<?php if(isset($color_image_1)){ echo $color_image_1; } ?>" alt="" /></button>
							<input id="jform_color_1" name="jform[n6]" value="<?php echo $this->item->n6;?>" type="hidden">
						</div>
						<div class="col-sm-4"></div>
					</div>
				<?php } ?>
				<div class="col-sm-4"></div>
			</div>
		</div>
		<!-- освещение -->
		<div class="container">
			<div class="row sm-margin-bottom">
				<div class="col-sm-4"></div>
				<div class="col-sm-4">
					<table style="margin-left: calc(50% - 67px);">
						<tr>
							<td>
								<h3>Освещение</h3>
							</td>
							<td>
								<a class="help"><img src="/images/hint.png" alt="подсказка" style="margin-bottom: 16px" /><span class="airhelp">
								Если на потолке будут люстры или светильники укажите их количество и характеристики. Если их не будет просто пропустите этот пункт
								</span></a>
							</td>
						</tr>
					</table>
				</div>
				<div class="col-sm-4"></div>
			</div>
		</div>
		<!-- Люстры -->
		<div class="container">
			<div class="row sm-margin-bottom">
				<div class="col-sm-4"></div>
				<div class="col-sm-4">
					<table class="table_calcform" style="margin-bottom: 30px;">
						<tr>
							<td class="td_calcform1">
								<button type="button" id="btn_chandelier" class="btn add_fields">
									<label id="jform_n12-lbl" for="jform_n12" class="no_margin">Добавить люстры</label>
								</button>
							</td>
							<td class="td_calcform2">
								<a class="help"><img src="/images/hint.png" alt="подсказка" style="margin-bottom: 16px"; /><span class="airhelp">В паспорте на люстру есть описание системы крепежа и диаметр технологического отверстия</span></a>
							</td>
						</tr>
					</table>
					<table id="chandelier" style="display: none; width: 100%;">
						<tr>
							<td>Введите кол-во люстр:</td>
						</tr>
						<tr>
							<td>
								<input id="jform_n12" data-next="#jform_n13" name="jform[n12]" placeholder ="шт." value="<?php echo $this->item->n12; ?>" class="form-control" type="tel">
							</td>
						</tr>
					</table>
				</div>
				<div class="col-sm-4"></div>
			</div>
		</div>
		<!-- Светильники -->
		<div class="container">
			<div class="row sm">
				<div class="col-sm-4"></div>
				<div class="col-sm-4">
					<table class="table_calcform">
						<tr>
							<td class="td_calcform1">
								<button type="button" id="btn_fixtures" class="btn add_fields">
									<label id="jform_n13-lbl" for="jform_n13" class="no_margin">Добавить светильники</label>
								</button>
							</td>
							<td class="td_calcform2">
								<a class="help" ><img src="/images/hint.png" alt="подсказка" style="margin-bottom: 16px" /><span class="airhelp">На упаковке светильника указан диаметр технологического отверстия</span></a>
							</td>
						</tr>
					</table>
				</div>
				<div class="col-sm-4" style="margin-bottom: 30px;"></div>
			</div>
			<div class="row sm-margin-bottom fixtures" style="display: none; width: 100%;">
				<div class="col-sm-4"></div>
				<div class="col-sm-4">
					<div id="jform_n13_block" >
						<div class="form-group" style="margin-bottom: 0em;">
							<div class="advanced_col1">
								<label>Кол-во</label>
							</div>
							<div class="advanced_col2">
								<label>Вид</label>
							</div>
							<div class="advanced_col3">
								<label>Диаметр</label>
							</div>
							<div class="advanced_col4 center">
								<label><i class="fa fa-trash" aria-hidden="true"></i></label>
							</div>
							<div class="clr"></div>
						</div>
						<div id="jform_n13_block_html" class="hide_label">
							<?php $n13 = $this->item->n13; ?>
							<?php if(count($n13) > 0) { ?>
								<?php foreach($n13 as $lamp) {?>
									<div class="form-group">
										<div class="advanced_col1">
											<input id="n13_count" name="n13_count[]" class="form-control" value="<?php echo $lamp->n13_count; ?>" placeholder="шт." >
										</div>
										<div class="advanced_col2">
											<select name="n13_type[]" id="n13" class="form-control n13_control" placeholder="Вид">
												<?foreach ($this->item->types[1]->id AS $ring):?>
													<option value="<?=$ring->id;?>" <?=($ring->id == $lamp->n13_type)?'selected':'';?>><?=$ring->title;?></option>
												<?endforeach;?>
											</select>
										</div>
										<div class="advanced_col3">
											<select name="n13_ring[]" id="n13_1" class="form-control" placeholder="Диаметр">
												<?foreach ($this->item->types[1]->id[$lamp->n13_type]->options[0]->components_option AS $n13_item):?>
													<option value="<?=$n13_item->id;?>" <?=($n13_item->id == $lamp->n13_size)?'selected':'';?>><?=$n13_item->title;?></option>
												<?endforeach;?>
											</select>
										</div>
										<div class="advanced_col4 center">
											<button class="clear_form_group btn btn-danger" type="button"><i class="fa fa-trash" aria-hidden="true"></i></button>
										</div>
										<div class="clr"></div>
									</div>
								<?php } ?>
							<?php } ?>
						</div>
						<button id="add_n13" class="btn btn-primary" type="button">Добавить</button>
					</div>
				</div>
				<div class="col-sm-4"></div>
			</div>
		</div>
		<!-- Экола -->
		<div class="container fixtures" style="display: none; width: 100%;">
			<div class="row sm-margin-bottom">
				<div class="col-sm-4"></div>
				<div class="col-sm-4">
					<h4>Можете приобрести светильники у нас:</h4>
				</div>
				<div class="col-sm-4"></div>
			</div>
		</div>
		<div class="container fixtures" style="display: none; width: 100%;">
			<div class="row sm-margin-bottom">
				<div class="col-sm-4"></div>
				<div class="col-sm-4">
					<div class="form-group" style="margin-bottom: 0em;">
						<div class="advanced_col1">
							<label>Кол-во,шт</label>
						</div>
						<div class="advanced_col2">
							<label>Цвет</label>
						</div>
						<div class="advanced_col3">
							<label>Лампа</label>
						</div>
						<div class="advanced_col4 center">
							<label><i class="fa fa-trash" aria-hidden="true"></i></label>
						</div>
						<div class="clr"></div>
					</div>
					<div id="ecola_block_html" class="hide_label">
						<?php $n26 = $this->item->n26;?>
						<?php if(count($n26) > 0) { ?>
							<?php foreach($n26 as $ecola) {?>
								<div class="form-group">
									<div class="advanced_col1">
										<input id="ecola_count" name="ecola_count[]"  value="<?=/*$tmp[$item]*/ $ecola->n26_count; ?>" class="form-control" placeholder="шт." type="tel">
									</div>
									<div class="advanced_col2">
										<select class="form-control" name="light_color[]" placeholder="Светильник">
											<?foreach ($this->item->n26_all AS $ecola_item):?>
												<option value="<?=$ecola_item->id;?>" <?=($ecola_item->id == $ecola->n26_illuminator)?'selected':'';?>><?=$ecola_item->title;?></option>
											<?endforeach;?>
										</select>
									</div>
									<div class="advanced_col3">
										<select class="form-control"  name="light_lamp_color[]" placeholder="Лампа">
											<?foreach ( $this->item->n26_lamp AS $ecola_lamps):?>
												<option value="<?=$ecola_lamps->id;?>" <?=($ecola_lamps->id == $ecola->n26_lamp)?'selected':'';?>><?=$ecola_lamps->title;?></option>
											<?endforeach;?>
										</select>
									</div>
									<div class="advanced_col4 center">
										<button class="clear_form_group btn btn-danger" type="button"><i class="fa fa-trash" aria-hidden="true"></i></button>
									</div>
									<div class="clr"></div>
								</div>
							<?php } ?>
						<?php } ?>
					</div>
					<button id="add_ecola" class="btn btn-primary" type="button">Добавить </button>
				</div>
				<div class="col-sm-4" style="margin-bottom: 30px;"></div>
			</div>
		</div>
		<!-- прочий монтаж -->
		<div class="container">
			<div class="row sm-margin-bottom">
				<div class="col-sm-4">
				</div>
				<div class="col-sm-4">
					<h3>Прочий монтаж</h3>
				</div>
				<div class="col-sm-4">
				</div>
			</div>
		</div>
		<!-- Шторный карниз -->
		<div class="container">
			<div class="row sm-margin-bottom">
				<div class="col-sm-4"></div>
				<div class="col-sm-4">
					<table class="table_calcform">
						<tr>
							<td class="td_calcform1">
								<button type="button" id="btn_cornice" class="btn add_fields">
									<label id="jform_n12-lbl" for="jform_n12" class="no_margin">Добавить шторный карниз</label>
								</button>
							</td>
							<td class="td_calcform2">
								<a class="help" >
									<img src="/images/hint.png" alt="подсказка" style="margin-bottom: 16px" />
									<span class="airhelp">
										Если его не будет или он будет крепиться к стене просто пропустите этот пункт.<br>
										Шторный карниз можно крепить на потолок двумя способами:<br> 
										<ul>
											<li>Видимый <br> <img src="/images/karniz.png" width="350" height="250"/></li>
											<li>Скрытый <br> <img src="/images/karniz2.png" width="350" height="250"/></li>
										</ul>
										<br>Во 2 случае надо указывать длину стены, на которой окно и ставить галочку напротив надписи скрытый шторный карниз
									</span>
								</a>							
							</td>
						</tr>
					</table>
				</div>
				<div class="col-sm-4"></div>
			</div>
		</div>
		<div class="container">
			<div class="row cornice" style="display: none; width: 100%;">
				<div class="col-sm-4">
					<div class="form-group">
						<div style="width: 100%; text-align: left;">
							<label id="jform_n27-lbl" for="jform_n27" class="" >Введите длину шторного карниза в МЕТРАХ</label>
						</div>
						<input name="jform[n27]" id="jform_n27" data-next="#jform_n12" value="<?php echo $this->item->n27; ?>" class="form-control" placeholder="м." type="tel">
					</div>
					<div class="form-group" style="text-align: left; margin-left: calc(50% - 70px);">
                        <div style="display: inline-block;">
                            <input name="jform[n16]" id="jform_n16" class="radio" value="0" type="radio" <?if(!$this->item->n16) echo "checked=\"checked\""?>>
                            <label for="jform_n16"> Обычный карниз</label>
                        </div>
                        <br>
                        <div style="display: inline-block;">
                            <input name="jform[n16]" id="jform_n16_1" class="radio" value="1" type="radio" <?if($this->item->n16) echo "checked=\"checked\""?>>
                            <label for="jform_n16_1"> Скрытый карниз</label>
                        </div>
					</div>
				</div>
				<div class="col-sm-4"></div>
			</div>
		</div>
		<!-- приобрести карнизы -->
		<div class="container">
			<div class="row sm-margin-bottom cornice" style="display: none; width: 100%;">
				<div class="col-sm-4"></div>
				<div class="col-sm-4">
					<h4>Можете приобрести карнизы у нас:</h4>
				</div>
				<div class="col-sm-4"></div>
			</div>
		</div>
		<div class="container">
			<div class="row sm-margin-bottom cornice" style="display: none; width: 100%;">
				<div class="col-sm-4"></div>
				<div class="col-sm-4">
					<div class="form-group" style="margin-bottom: 0em;">
						<div class="advanced_col1">
							<label>Кол-во,шт</label>
						</div>
						<div class="advanced_col2">
							<label>Тип</label>
						</div>
						<div class="advanced_col3">
							<label>Длина</label>
						</div>
						<div class="advanced_col4 center">
							<label><i class="fa fa-trash" aria-hidden="true"></i></label>
						</div>
						<div class="clr"></div>
					</div>
					<div id="jform_n15_block_html" class="hide_label">
						<?php $n15 = $this->item->n15; ?>
						<?php if (count($n15) > 0) { ?>
							<?php foreach ($n15 as $cornice) { ?>
								<div class="form-group">
									<div class="advanced_col1">
										<input id="n15_count" name="n15_count[]"  value="<?= $cornice->n15_count; ?>" class="form-control" placeholder="шт." type="tel">
									</div>
									<div class="advanced_col2">
										<select name="n15_type[]" id="n15" class="form-control n15_control" placeholder="Тип">
											<?foreach ($this->item->types[9]->id AS $type1):?>
												<option value="<?=$type1->id;?>" <?=($type1->id == $cornice->n15_type)?'selected':'';?>><?=$type1->title;?></option>
											<?endforeach;?>
										</select>
									</div>
									<div class="advanced_col3">
										<select name="n15_size[]" id="n15_1" class="form-control" placeholder="Диаметр">
											<?foreach ( $this->item->n15_all AS $cornice_item):?>
												<option value="<?=$cornice_item->id;?>" <?=($cornice_item->id == $cornice->n15_size)?'selected':'';?>><?=$cornice_item->title;?></option>
											<?endforeach;?>
										</select>
									</div>
									<div class="advanced_col4 center">
										<button class="clear_form_group btn btn-danger" type="button"><i class="fa fa-trash" aria-hidden="true"></i></button>
									</div>
									<div class="clr"></div>
								</div>
							<?php } ?>
						<?php } ?>
					</div>
					<button id="add_n15" class="btn btn-primary" type="button">Добавить</button>
				</div>
			</div>
		</div>
		<!-- трубы -->
		<div class="container">
			<div class="row sm-margin-bottom">
				<div class="col-sm-4"></div>
				<div class="col-sm-4">
					<table class="table_calcform">
						<tr>
							<td class="td_calcform1">
								<button type="button" id="btn_pipes" class="btn add_fields">
									<label id="jform_n12-lbl" for="jform_n12" class="no_margin">Добавить трубы, входящие в потолок</label>
								</button>
							</td>
							<td class="td_calcform2">
								<a class="help" ><img src="/images/hint.png" alt="подсказка" style="margin-bottom: 16px" />
									<span class="airhelp">
										<img src="/images/obvod.png" width="380" height="250" align="center"/>
										<br>На картинке изображены 3 трубы разного диаметра. Выбираем отдельно одну трубу 45-55, а две других трубы 32<br>
										<img src="/images/obvod2.png" width="380" height="250" align="center"/>
									</span>
								</a>						
							</td>
						</tr>
					</table>
				</div>
				<div class="col-sm-4"></div>
			</div>
		</div>
		<div class="container">
			<div class="row sm-margin-bottom" id="pipes" style="display: none; width: 100%;">
				<div class="col-sm-4"></div>
				<div class="col-sm-4">
					<div id="jform_n14_block" >
						<div class="form-group" style="margin-bottom: 0em;">
							<div class="advanced_col1">
								<label>Кол-во,шт</label>
							</div>
							<div class="advanced_col5">
								<label>Диаметр</label>
							</div>
							<div class="advanced_col4 center">
								<label><i class="fa fa-trash" aria-hidden="true"></i></label>
							</div>
							<div class="clr"></div>
						</div>
						<div id="jform_n14_block_html" class="hide_label">
							<?php $n14 = $this->item->n14; ?>
							<?php if (count($n14) > 0) { ?>
								<?php foreach ($n14 as $truba) { ?>
									<?php if ($truba->n14_count > 0) { ?>
										<div class="form-group">
											<div class="advanced_col1">
												<input id="n14_count" name="n14_count[]" class="form-control" value="<?php echo $truba->n14_count; ?>" placeholder="шт." type="tel">
											</div>
											<div class="advanced_col5">
												<select class="form-control" name="n14_type[]" placeholder="Платформа">
													<?foreach ($this->item->n14_all AS $truba_item):?>
														<option value="<?=$truba_item->id;?>" <?=($truba_item->id == $truba->n14_size)?'selected':'';?>><?=$truba_item->title;?></option>
													<?endforeach;?>
												</select>
											</div>
											<div class="advanced_col4 center">
												<button class="clear_form_group btn btn-danger" type="button"><i class="fa fa-trash" aria-hidden="true"></i></button>
											</div>
											<div class="clr"></div>
										</div>
									<?php } ?>
								<?php } ?>
							<?php } ?>
						</div>
						<button id="add_n14" class="btn btn-primary" type="button">Добавить</button>
					</div>
				</div>
				<div class="col-sm-4"></div>
			</div>
		</div>
		<div id="extra_section">
			<?php if ($type!='guest' && $user->dealer_type!=2) {?>
				<!-- плитка -->
				<div class="container">
					<div class="row sm-margin-bottom">
						<div class="col-sm-4"></div>
						<div class="col-sm-4">
							<div class="form-group">
								<table class="table_calcform">
									<tr>
										<td class="td_calcform3">
											<button type="button" id="btn_tile" class="btn add_fields">
												<label id="jform_n7-lbl" for="jform_n7" class="no_margin">Метраж стен с плиткой</label>
											</button>
										</td>
										<td class="td_calcform2">
											<a class="help" >
												<img src="/images/hint.png" alt="подсказка" style="margin-bottom: 16px" />
												<span class="airhelp">Считается дополнительная работа монтажникам по креплению багета в плитку</span>
											</a>						
										</td>
									</tr>
								</table>
								<input name="jform[n7]" id="jform_n7" data-next="#jform_n8" value="<?php echo $this->item->n7; ?>" class="form-control" placeholder="м." type="tel" style="display: none;">
							</div>
						</div>
						<div class="col-sm-4"></div>
					</div>
				</div>
				<!-- керамогранит -->
				<div class="container">
					<div class="row sm-margin-bottom">
						<div class="col-sm-4"></div>				
						<div class="col-sm-4">
							<div class="form-group">
								<table class="table_calcform">
									<tr>
										<td class="td_calcform3">
											<button type="button" id="btn_stoneware" class="btn add_fields">
												<label id="jform_n8-lbl" for="jform_n8" class="no_margin">Метраж стен с керамогранитом</label>
											</button>
										</td>
										<td class="td_calcform2">
											<a class="help" >
												<img src="/images/hint.png" alt="подсказка" style="margin-bottom: 16px" />
												<span class="airhelp">Считается дополнительная работа монтажникам по креплению багета в керамогранит</span>
											</a>
										</td>
									</tr>
								</table>
								<input name="jform[n8]" id="jform_n8" data-next="#jform_n19" value="<?php echo $this->item->n8; ?>" class="form-control" placeholder="м." type="tel" style="display: none;">
							</div>
						</div>
						<div class="col-sm-4"></div>					
					</div>
				</div>
				<!-- провод -->
				<div class = "container">
					<div class="row sm-margin-bottom">
						<div class="col-sm-4"></div>				
						<div class="col-sm-4">
							<div class="form-group">
								<table class="table_calcform">
									<tr>
										<td class="td_calcform3">
											<button type="button" id="btn_wire" class="btn add_fields">
												<label id="jform_n19-lbl" for="jform_n19" class="no_margin">Провод</label>
											</button>
										</td>
										<td class="td_calcform2">
											<a class="help" >
												<img src="/images/hint.png" alt="подсказка" style="margin-bottom: 16px" />
												<span class="airhelp">На 1м провода используется:  <br> Провод ПВС 2 х 0,75  + 2 * Дюбель полим. 6 * 51 + 2 * Саморез ГДК 3,5 * 51</span>
											</a>
										</td>
									</tr>
								</table>
								<input name="jform[n19]" id="jform_n19" data-next="#jform_n17" value="<?php echo $this->item->n19; ?>" class="form-control" placeholder="м." type="tel" style="display: none;">
							</div>
						</div>
						<div class="col-sm-4"></div>
					</div>					
				</div>
				<!-- закладная брусом -->
				<div class = "container">
					<div class="row sm-margin-bottom">
						<div class="col-sm-4"></div>				
						<div class="col-sm-4">
							<div class="form-group">
								<table class="table_calcform">
									<tr>
										<td class="td_calcform3">
											<button type="button" id="btn_bar" class="btn add_fields">
												<label id="jform_n17-lbl" for="jform_n17" class="no_margin">Закладная брусом</label>
											</button>
										</td>
										<td class="td_calcform2">
											<a class="help" >
												<img src="/images/hint.png" alt="подсказка" style="margin-bottom: 16px" />
												<span class="airhelp">На 1м  используется:  <br> Брус 40*50  + 3 * Подвес прямой П 60 (0,8) +  6 * Дюбель полим. 6 * 51 + 6 * Саморез ГДК 3,5 * 51 + 6 * Саморез ГДК 3,5 * 41</span>
											</a>
										</td>
									</tr>
								</table>
								<input name="jform[n17]" id="jform_n17"  value="<?php echo $this->item->n17; ?>" class="form-control" placeholder="м." type="tel" style="display: none;">
							</div>
						</div>
						<div class="col-sm-4"></div>					
					</div>
				</div>
				<!-- парящий потолок -->
				<div class = "container">
					<div class="row sm-margin-bottom" id="n30_block">
						<div class="col-sm-4"></div>
						<div class="col-sm-4">
							<div class="form-group">
								<table class="table_calcform">
									<tr>
										<td class="td_calcform3">
											<button type="button" id="btn_soaring" class="btn add_fields">
												<label id="jform_n30-lbl" for="jform_n30" class="no_margin">Парящий потолок</label>
											</button>
										</td>
										<td class="td_calcform2">
											<a class="help" >
												<img src="/images/hint.png" alt="подсказка" style="margin-bottom: 16px" />
												<span class="airhelp">На 1м парящего потолка используется:  <br> Багет для парящих пот аллюм + Вставка для парящих потолков </span>
										</a>
										</td>
									</tr>
								</table>
								<input name="jform[n30]" id="jform_n30" data-next="#jform_n17" value="<?php echo $this->item->n30; ?>" class="form-control" placeholder="м." type="tel" style="display: none;">
							</div>
						</div>
						<div class="col-sm-4"></div>
					</div>
				</div>
				<!-- переход уровня -->
				<div class="container">
					<div class="row sm-margin-bottom">
						<div class="col-sm-4"></div>
						<div class="col-sm-4" style="margin-bottom: 1em;">
							<table class="table_calcform">
								<tr>
									<td class="td_calcform3">
										<button type="button" id="btn_level" class="btn add_fields">
											Переход уровня
										</button>
									</td>
									<td class="td_calcform2">
									</td>
								</tr>
							</table>
						</div>
						<div class="col-sm-4"></div>
					</div>
				</div>
				<div class="container">
					<div class="row sm-margin-bottom" id="level" style="display: none; width: 100%;">
						<div class="col-sm-4"></div>
						<div class="col-sm-4">
							<div class="form-group" style="margin-bottom: 0em;">
								<div class="advanced_col1">
									<label>Кол-во, м</label>
								</div>
								<div class="advanced_col5">
									<label>Тип</label>
								</div>
								<div class="advanced_col4 center">
									<label><i class="fa fa-trash" aria-hidden="true"></i></label>
								</div>
								<div class="clr"></div>
							</div>
							<div id="level_block_html" class="hide_label">
								<?php $n29 = $this->item->n29; ?>
								<?php $component_model = Gm_ceilingHelpersGm_ceiling::getModel('components'); ?>
								<?php if(count($n29) > 0) { ?>
									<?php foreach($n29 as $level) {?>
										<div class="form-group">
											<div class="advanced_col1">
												<input id="n29_count" name="n29_count[]"  value="<?=/*$tmp[$item]*/ $level->n29_count; ?>" class="form-control" placeholder="м." type="tel">
											</div>
											<div class="advanced_col5">
												<select name="n29_type[]" id="n29" class="form-control n29_control" placeholder="Тип">
													<?php foreach ($this->item->types[11]->id AS $type1):
														if($this->item->n2 == 29 && ($type1->id == 12 || $type1->id == 15)) {?>
															<option value="<?=$type1->id;?>" <?=($type1->id == $level->n29_type)?'selected':'';?>><?=$type1->title;?></option>
														<?php } else if($this->item->n2 != 29) { ?>
															<option value="<?=$type1->id;?>" <?=($type1->id == $level->n29_type)?'selected':'';?>><?=$type1->title;?></option>
													<?php }; endforeach; ?>
												</select>
											</div>
											<div class="advanced_col4 center">
												<button class="clear_form_group btn btn-danger" type="button"><i class="fa fa-trash" aria-hidden="true"></i></button>
											</div>
											<div class="clr"></div>
										</div>
									<?php } ?>
								<?php } ?>
							</div>
							<button id="add_level" class="btn btn-primary" type="button">Добавить</button>
						</div>
					</div>
				</div>
			<?php } ?>
			<!-- пожарная сигнализация -->
			<div class = "container">
				<div class="row sm-margin-bottom">
					<div class="col-sm-4"></div>
					<div class="col-sm-4">
						<div class="form-group">
							<table>
								<tr>
									<td>Пожарная сигнализация</td>
									<td>
										<a class="help" >
											<img src="/images/hint.png" alt="подсказка" style="margin-bottom: 16px" />
											<span class="airhelp">Если на основном потолке установлена пожарная сигнализация или Вы планируете сделать ее установку на натяжной потолок,укажите количество пожарных сигнализаций в данной комнате для комплектации стоек под них</span>
										</a>
									</td>
									<td>
										<button type="button" id="btn_firealarm" class="btn btn-primary add_fields"><i class="fa fa-plus"></i></button>
									</td>
								</tr>
							</table>
							<input name="jform[n21]" id="jform_n21" data-next="#jform_n24" value="<?php echo $this->item->n21; ?>" class="form-control" placeholder="шт." type="tel" style="display: none;">
						</div>
					</div>
					<div class="col-sm-4"></div>
				</div>
			</div>
			<?php if($type!='guest' && $user->dealer_type!=2) {?>
				<!-- разделитель -->
				<div class = "container">
					<div class="row sm-margin-bottom" id="razdelitel">
						<div class="col-sm-4"></div>
						<div class="col-sm-4">
							<div class="form-group">
								<table>
									<tr>
										<td>
											<label id="jform_n20-lbl" for="jform_n20" class="">Разделитель</label>
										</td>
										<td>
											<a class="help" ><img src="/images/hint.png" alt="подсказка" style="margin-bottom: 16px" /><span class="airhelp">

											На 1м разделителя используется:  <br> Брус 40*50  + 3 * Саморез ГКД 4,2 * 102 +  3 * Дюбель полим. 6 * 51 + 20 * Саморез ГДК 3,5 * 51 +  Вставка в разд 303 гриб + Багет разделительный аллюм (2.5 м.) </span></a>
										</td>
									</tr>
								</table>
								<input name="jform[n20]" id="jform_n20" data-next="#jform_n21" value="<?php echo $this->item->n20; ?>" class="form-control" placeholder="м." type="tel">
							</div>
						</div>
						<div class="col-sm-4"></div>					
					</div>
				</div>
				<!-- сложность доступа -->
				<div class = "container">
					<div class="row sm-margin-bottom">
						<div class="col-sm-4"></div>
						<div class="col-sm-4">
							<div class="form-group">
								<table>
									<tr>
										<td>
											<label id="jform_n24-lbl" for="jform_n24" class="">Сложность доступа</label>
										</td>
										<td>
											<a class="help" ><img src="/images/hint.png" alt="подсказка" style="margin-bottom: 16px" /><span class="airhelp">

											Наценка на монтажные работы за труднодоступные места. Считается по метрам.  </span></a>
										</td>
									</tr>
								</table>

								<input name="jform[n24]" id="jform_n24" data-next="#jform_n10" value="<?php echo $this->item->n24; ?>" class="form-control" placeholder="м." type="tel">
							</div>
						</div>
						<div class="col-sm-4"></div>
					</div>
				</div>
				<!-- внутренний вырез на месте -->
				<div class = "container">
					<div class="row sm-margin-bottom">
						<div class="col-sm-4">
						</div>
						<div class="col-sm-4">
							<div class="form-group">
								<table>
									<tr>
										<td>
											<label id="jform_n11-lbl" for="jform_n11" class="">Внутренний вырез (на месте)</label>
										</td>
										<td>
											<a class="help" ><img src="/images/hint.png" alt="подсказка" style="margin-bottom: 16px" /><span class="airhelp">

											На 1м внутреннего выреза используется:  <br> Брус 40*50  + Багет (на выбор: ПВХ (2,5 м) , стеновой аллюм, потолочный аллюм) + 3 * Кронштейн 15 * 12,5 см. + + 22 * Саморез 3,5 * 41 +  16 * Дюбель полим. 6 * 51 + Гарпун </span></a>
										</td>
									</tr>
								</table>
								<input name="jform[n11]" id="jform_n11" value="<?php echo $this->item->n11; ?>" class="form-control" placeholder="м." type="tel">
							</div>
						</div>
						<div class="col-sm-4">
						</div>
					</div>
				</div>
				<!-- Внутренний вырез в цеху -->
				<div class = "container">
					<div class="row sm-margin-bottom">
						<div class="col-sm-4">
						</div>
						<div class="col-sm-4">
							<div class="form-group">
								<table>
									<tr>
										<td>
											<label id="jform_n31-lbl" for="jform_n31" class="">Внутренний вырез (в цеху)</label>
										</td>
										<td>
											<a class="help" ><img src="/images/hint.png" alt="подсказка" style="margin-bottom: 16px" /><span class="airhelp">

											На 1м внутреннего выреза используется:  <br>  Багет (на выбор: ПВХ (2,5 м) , стеновой аллюм, потолочный аллюм)  + 10 * Саморез 3,5 * 51 +  10 * Дюбель полим. 6 * 51 + Вставка</span></a>
										</td>
									</tr>
								</table>
								<input name="jform[n31]" id="jform_n31" value="<?php echo $this->item->n31; ?>" class="form-control" placeholder="м." type="tel">
							</div>
						</div>
						<div class="col-sm-4">
						</div>
					</div>
				</div>
				<!-- слив воды -->
				<div class = "container">
					<div class="row sm-margin-bottom">
						<div class="col-sm-4">
						</div>
						<div class="col-sm-4">
							<div class="form-group">
								<table>
									<tr>
										<td>
											<label id="jform_n32-lbl" for="jform_n32" class="">Слив воды</label>
										</td>
										<td>
											<a class="help" ><img src="/images/hint.png" alt="подсказка" style="margin-bottom: 16px" /><span class="airhelp">

										В работу входит слив воды. Укажите колиество комнат.</span></a>
										</td>
									</tr>
								</table>
								<input name="jform[n32]" id="jform_n32" value="<?php echo $this->item->n32; ?>" class="form-control" placeholder="Кол-во комнат, шт." type="tel">
							</div>
						</div>
						<div class="col-sm-4">
						</div>
					</div>
				</div>
				<!-- доп крепеж -->
				<div class = "container">
					<div class="row sm-margin-bottom">
						<div class="col-sm-4">
						</div>
						<div class="col-sm-4">
							<div class="form-group">
								<table>
									<tr>
										<td>
											<label id="jform_dop_krepezh-lbl" for="jform_dop_krepezh" class="">Дополнительный крепеж</label>
										</td>
										<td>
											<a class="help" ><img src="/images/hint.png" alt="подсказка" style="margin-bottom: 16px" /><span class="airhelp">

											На 1м дополнительного крепежа используется:  <br> 10 * Саморез 3,5 * 51  + Багет (на выбор: ПВХ (2,5 м) , стеновой аллюм, потолочный аллюм)  </span></a>
										</td>
									</tr>
								</table>

								<input name="jform[dop_krepezh]" id="jform_dop_krepezh" data-next="#jform_n18" value="<?php echo $this->item->dop_krepezh; ?>" class="form-control" placeholder="м." type="tel">
							</div>
						</div>
						<div class="col-sm-4">
						</div>
					</div>
				</div>
				<!-- Усилиние стен -->
				<div class = "container">
					<div class="row sm-margin-bottom">
						<div class="col-sm-4">
						</div>
						<div class="col-sm-4">
							<div class="form-group">
								<table>
									<tr>
										<td>
											<label id="jform_n18-lbl" for="jform_n18" class="">Усиление стен</label>
										</td>
										<td>
											<a class="help" ><img src="/images/hint.png" alt="подсказка" style="margin-bottom: 16px" /><span class="airhelp">

											На 1м усиления стен используется:  <br> Брус 40*50 + 3 * Саморез 3,5 * 51  + 3 * Дюбель полим. 6 * 51 + 3 * Кронштейн 15 * 12,5 см.</span></a>
										</td>
									</tr>
								</table>
								<input name="jform[n18]" id="jform_n18" data-next="#jform_n11" value="<?php echo $this->item->n18; ?>" class="form-control" placeholder="м." type="tel">
							</div>
						</div>
						<div class="col-sm-4">
						</div>
					</div>
				</div>
			<?php }?>
			<!-- вентиляция -->
			<div class="container">
				<div class="row">
					<div class="col-sm-4"></div>
					<div class="col-sm-4" style="margin-bottom: 1em;">
						<table>
							<tr>
								<td>Вентиляция</td>
							</tr>
						</table>
					</div>
					<div class="col-sm-4"></div>
				</div>
			</div>
			<div class="container">
				<div class="row sm-margin-bottom">
					<div class="col-sm-4"></div>
					<div class="col-sm-4">
						<div id="jform_n22_block"> 
							<div class="form-group" style="margin-bottom: 0em;">
								<div class="advanced_col1">
									<label>Кол-во,шт</label>
								</div>
								<div class="advanced_col2">
									<label>Тип</label>
								</div>
								<div class="advanced_col3">
									<label>Размер</label>
								</div>
								<div class="advanced_col4 center">
									<label><i class="fa fa-trash" aria-hidden="true"></i></label>
								</div>
								<div class="clr"></div>
							</div>
							<div id="jform_n22_block_html" class="hide_label">
								<?php $n22 = $this->item->n22;
									if (count($n22) > 0) {
										foreach($n22 as $ventilation) if ($ventilation->n22_count > 0) { ?>
											<div class="form-group">
												<div class="advanced_col1">
													<input name="n22_count[]" class="form-control" value="<?php echo $ventilation->n22_count; ?>" placeholder="м." type="tel">
												</div>
												<div class="advanced_col2">
													<select id="n22" class="form-control" name="n22_type[]" for="jform_n22_type">
													<?foreach ($this->item->types[4]->id AS $ring):?>
															<option value="<?=$ring->id;?>" <?=($ring->id == $ventilation->n22_type)?'selected':'';?>><?=$ring->title;?></option>
													<?endforeach;?>
													</select>
												</div>
												<div class="advanced_col3">
													<select id="n22_1" class="form-control" name="n22_diam[]" for="jform_n22_diam">
													<?foreach ($this->item->types[4]->id[$ventilation->n22_type]->options[0]->components_option AS $n22_item):?>
														<option value="<?=$n22_item->id;?>" <?=($n22_item->id == $ventilation->n22_size)?'selected':'';?>><?=$n22_item->title;?></option>
													<?endforeach;?>
													</select>
												</div>
												<div class="advanced_col4 center">
													<button class="clear_form_group btn btn-danger" type="button"><i class="fa fa-trash" aria-hidden="true"></i></button>
												</div>
												<div class="clr"></div>
											</div>
										<?php }?>
									<?php }?>
							</div>
							<button id="add_n22" class="btn btn-primary" type="button">Добавить</button>
						</div>
					</div>
				</div>
			</div>
			<?php if($type!='guest' && $user->dealer_type!=2) {?>
				<!-- диффузор -->
				<div class="container">
					<div class="row">
						<div class="col-sm-4"></div>
						<div class="col-sm-4" style="margin-bottom: 1em;">
							<table>
								<tr>
									<td>Диффузор</td>
									<td>
										<a class="help">
											<img src="/images/hint.png" alt="подсказка" style="margin-bottom: 16px" />
											<span class="airhelp">Вентиляционная решетка для отвода воздуха, который попадает за потолочное пространство <br></span>
										</a>
									</td>
								</tr>
							</table>
						</div>
						<div class="col-sm-4"></div>
					</div>
				</div>
				<div class="container">
					<div class="row sm-margin-bottom">
						<div class="col-sm-4"></div>
						<div class="col-sm-4">
							<div id="jform_n23_block">
								<div class="form-group" style="margin-bottom: 0em;">
									<div class="advanced_col1">
										<label>Кол-во,шт</label>
									</div>
									<div class="advanced_col5">
										<label>Размер</label>
									</div>
									<div class="advanced_col4 center">
										<label><i class="fa fa-trash" aria-hidden="true"></i></label>
									</div>
									<div class="clr"></div>
								</div>
								<div id="jform_n23_block_html" class="hide_label">
									<?php $n23 = $this->item->n23; ?>
									<?php if(count($n23) > 0) { ?>
										<?php foreach($n23 as $diffuzor) if ($diffuzor->n23_count > 0) { ?>
											<div class="form-group">
												<div class="advanced_col1">
													<input name="n23_count[]" class="form-control" value="<?php echo $diffuzor->n23_count; ?>" placeholder="шт." type="tel">
												</div>
												<div class="advanced_col5">
													<select class="form-control" name="n23_size[]" for="jform_n22_type" placeholder="Размер">
														<?foreach ($this->item->n23_all AS $diffuzor_item):?>
															<option value="<?=$diffuzor_item->id;?>" <?=($diffuzor_item->id == $diffuzor->n23_size)?'selected':'';?>><?=$diffuzor_item->title;?></option>
														<?endforeach;?>
													</select>
												</div>
												<div class="advanced_col4 center">
													<button class="clear_form_group btn btn-danger" type="button"><i class="fa fa-trash" aria-hidden="true"></i></button>
												</div>
												<div class="clr"></div>
											</div>
										<?php } ?>
									<?php } ?>
								</div>
								<button id="add_n23" class="btn btn-primary" type="button">Добавить</button>
							</div>
						</div>
						<div class="col-sm-4"></div>
					</div>
				</div>
				<!-- другие комплектующие -->
				<div class="container">
					<div class="row" style="margin-bottom: 1em;">
						<div class="col-sm-4"></div>
						<div class="col-sm-4">
							<table>
								<tr>
									<td>Другие комплектующие</td>
								</tr>
							</table>
						</div>
						<div class="col-sm-4"></div>
					</div>
				</div>
				<div class = "container">
					<div class="row">
						<div class="col-sm-4"></div>
						<div class="col-sm-4">
							<div class="advanced_col_half">
								<label>Название</label>
								<div id="extra_components_title_container">
									<?php foreach($extra_components_array as $item) { ?>
										<div class='form-group'>
											<input name='extra_components_title[]' value='<?php echo $item['title']; ?>' class='form-control' type='text'>
										</div>
									<?php } ?>
								</div>
							</div>
							<div class="advanced_col_half">
								<label>Стоимость</label>
								<div id="extra_components_value_container">
									<?php foreach($extra_components_array as $item) { ?>
										<div class='form-group'>
											<input name='extra_components_value[]' value='<?php echo $item['value']; ?>' class='form-control' type='text'>
										</div>
									<?php } ?>
								</div>
							</div>
						</div>
						<div class="col-sm-4"></div>
					</div>
					<div class="row sm-margin-bottom">
						<div class="col-sm-4"></div>
						<div class="col-sm-4">
							<div class="form-group">
								<button id="extra_components_button" class="btn btn-primary" type="button">Добавить</button>
							</div>
						</div>
						<div class="col-sm-4"></div>
					</div>
				</div>
				<!-- другие комплектующие из склада-->
				<div class="container">
					<div class="row" style="margin-bottom: 1em;">
						<div class="col-sm-4"></div>
						<div class="col-sm-4">
							<table>
								<tr>
									<td>Другие комплектующие со склада</td>
								</tr>
							</table>
						</div>
						<div class="col-sm-4"></div>
					</div>
				</div>
				<div class = "container">
					<div class="row">
						<div class="col-sm-4"></div>
						<div class="col-sm-4">
							<div class="advanced_col_half">
								<label>Название</label>
								<div id="components_title_stock_container">
									<?php foreach($components_stock_array as $item) { ?>
										<div class='form-group Area Type'>
											<input id="Type" value='<?php echo $item['title']; ?>' autocomplete="off" NameDB="CONCAT(components.title,' ',options.title)" onclick="GetList(this, ['Type'], ['Type']);" onkeyup="GetList(this, ['Type'], ['Type']);" onblur="ClearSelect(this)" class='form-control Input Type' type='text'>
											<input id="ID" value="<?php echo $item['id']; ?>" name="components_title_stock[]" hidden>
											<div class="Selects Type"></div>
										</div>
									<?php } ?>
								</div>
							</div>
							<div class="advanced_col_half">
								<label>Количество</label>
								<div id="components_value_stock_container">
									<?php foreach($components_stock_array as $item) { ?>
										<div class='form-group'>
											<input name='components_value_stock[]' value='<?php echo $item['value']; ?>' class='form-control' type='text'>
										</div>
									<?php } ?>
								</div>
							</div>
						</div>
						<div class="col-sm-4"></div>
					</div>
					<div class="row sm-margin-bottom">
						<div class="col-sm-4"></div>
						<div class="col-sm-4">
							<div class="form-group">
								<button id="components_button_stock" class="btn btn-primary" type="button">Добавить</button>
							</div>
						</div>
						<div class="col-sm-4"></div>
					</div>
				</div>
				<!-- другие работы по монтажу -->
				<div class = "container">
					<div class="row" style="margin-bottom: 1em;">
						<div class="col-sm-4"></div>
						<div class="col-sm-4">
							<table>
								<tr>
									<td>Другие работы по монтажу</td>
								</tr>
							</table>
						</div>
						<div class="col-sm-4"></div>
					</div>
				</div>
				<div class = "container">
					<div class="row">
						<div class="col-sm-4"></div>
						<div class="col-sm-4">
							<div class="advanced_col_half">
								<label>Название</label>
								<div id="extra_mounting_title_container">
									<?php foreach($extra_mounting_array as $item) { ?>
										<div class='form-group'><input name='extra_mounting_title[]' value='<?php echo $item['title']; ?>' class='form-control' type='text'></div>
									<?php } ?>
								</div>
							</div>
							<div class="advanced_col_half">
								<label>Стоимость</label>
								<div id="extra_mounting_value_container">
									<?php foreach($extra_mounting_array as $item) { ?>
										<div class='form-group'><input name='extra_mounting_value[]' value='<?php echo $item['value']; ?>' class='form-control' type='text'></div>
									<?php } ?>
								</div>
							</div>
						</div>
						<div class="col-sm-4"></div>
					</div>
					<div class="row sm-margin-bottom">
						<div class="col-sm-4"></div>
						<div class="col-sm-4">
							<div class="form-group">
								<button id="extra_mounting_button" class="btn btn-primary" type="button">Добавить</button>
							</div>
						</div>
						<div class="col-sm-4"></div>
					</div>
				</div>
				<!-- новый процент скидки -->
				<div class = "container">
					<div class="row sm-margin-bottom">
						<div class="col-sm-4"></div>
						<div class="col-sm-4 pull-center">
							<h3>Новый процент скидки</h3>
						</div>
						<div class="col-sm-4"></div>
					</div>
				</div>
				<div class = "container">
					<div class="row sm-margin-bottom">
						<div class="col-sm-4"></div>
						<div class="col-sm-4">
							<input name= "jform[discount]" id="new_discount" class="form-control" placeholder="Введите %" type="number" max="100" min="0" type="number" value="<?php echo $this->item->discount; ?>" >
						</div>
						<div class="col-sm-4"></div>
					</div>
				</div>
			<?php }?>
		</div>									
		<!-- Монтаж -->
		<div class="container">
			<div class="row sm-margin-bottom">
				<div class="col-sm-4"></div>
				<div class="col-sm-4">
					<h4>Монтаж</h4>
				</div>
				<div class="col-sm-4"></div>
			</div>
			<div class="row sm-margin-bottom">
				<div class="col-sm-4"></div>
				<div class="col-sm-4" id = "need_mount">
					<div class="form-group" style="text-align: left; margin-left: calc(50% - 47px);">
						<div style="display: inline-block;">
							<input type="radio" name = "need_mount" id = "with_mount" class = "radio" value = "1" checked>
							<label for="with_mount">Нужен</label>
						</div>
						<br>
						<div style="display: inline-block;">
							<input type="radio" name = "need_mount" id = "without" class = "radio" value = "0">
							<label for="without">Не нужен</label>
						</div>
					</div>
				</div>
				<div class="col-sm-4"></div>
			</div>
		</div>
		<!-- высота помещения -->
		<div class="container">
			<div class="row sm-margin-bottom">
				<div class="col-sm-4"></div>
				<div class="col-sm-4">
					<h4>Высота помещения</h4>
				</div>
				<div class="col-sm-4"></div>
			</div>
			<div class="row sm-margin-bottom">
				<div class="col-sm-4"></div>
				<div class="col-sm-4" id = "height">
					<div class="form-group" style="text-align: left; margin-left: calc(50% - 75px);">
                        <div style="display: inline-block;">
                            <input type="radio" name = "jform[height]" id = "max_height" class = "radio" value = "1" <?if($this->item->height != 0) echo "checked=\"checked\""?>>
                            <label for="max_height">больше 3х метров</label>
                        </div>
                        <br>
                        <div style="display: inline-block;">
                            <input type="radio" name = "jform[height]" id = "min_height" class = "radio" value = "0" <?if($this->item->height == 0) echo "checked=\"checked\""?>>
                            <label for="min_height">меньше 3х метров</label>
                        </div>
					</div>
				</div>
				<div class="col-sm-4"></div>
			</div>
		</div>
		<!-- расчитать -->
		<div class="container">
			<div class="row sm-margin-bottom">
				<div class="col-md-4 pull-center">
					<button id="calculate_button" class="btn btn-primary btn-big" type="button">
						<span class="loading" style="display: none;">
							Считаю...<i class="fa fa-refresh fa-spin fa-3x fa-fw"></i>
						</span>
						<span class="static">Рассчитать</span>
					</button>
				</div>
			</div>
		</div>
		<div id="result_block">
			<div class="container">
				<div class="row sm-margin-bottom">
					<div class="col-sm-4"></div>
					<?php if($this->type === "guest") { ?>
						<div class="col-sm-4 total_price center" style="display: none;" id="guest_price">
							<div class="price_value">
								<span id="final_price">0.00</span> руб. - <span style="color:red; " >30% </span>=
								<span id="discount_price">0.00</span> руб.<br>
							</div>
							<div class="price_title">
								Самая низкая цена в Воронеже!
							</div>                            
						</div>
					<?php } else {?>
						<div class="col-sm-4 total_price center">
							<div class="price_value">
								<span id="final_price">0.00</span> руб.
							</div>
							<div class="price_title">
								Самая низкая цена в Воронеже!
							</div>
						</div>
					<?php } ?>
				</div>
			</div>			
			<div class="container">
				<div class="row sm-margin-bottom">
					<div class="col-sm-4"></div>
					<div class="col-sm-4">
						<h4 center> Получить смету на почту </h4>
					</div>
					<div class="col-sm-4"></div>
				</div>
			</div>
			<div class="container">
				<div class="row sm-margin-bottom">
					<div class="col-sm-4"></div>
					<div class="col-sm-4">
						<div class="form-group">
							<input value="" id="send_email" name="jform[send_email]" class="form-control" placeholder="Введите ваш Email" type="email">
						</div>
					</div>
					<div class="col-sm-4"></div>
				</div>
			</div>
			<div class="container">
				<div class="row sm-margin-bottom">
					<div class="col-sm-4"></div>
					<div class="col-sm-4">
						<button class="btn btn-transparent" type="button" id="send_to_email">Получить подробную смету</button>
					</div>
					<div class="col-sm-4"></div>
				</div>
			</div>
			<div class="container">
				<div class="row sm-margin-bottom">
					<div class="col-sm-4"></div>
					<div class="col-sm-4">
						<div id="send_email_success" style="display: none; font-size: 26px;">
							Смета отправлена
						</div>
					</div>
					<div class="col-sm-4"></div>
				</div>
			</div>
		</div>
		<!-- сохранить -->
		<div class="container">
			<div class="row sm-margin-bottom">
				<div class="col-sm-4"></div>
				<?php if ($this->type === "gmcalculator") { ?>
					<?php if ($this->item->project_id) { ?>
						<div class="col-sm-4">
							<a id="save_button"  class="btn btn-primary btn-big "  href="index.php?option=com_gm_ceiling&view=project&type=gmcalculator&subtype=calendar&id=<?php echo $this->item->project_id; ?>">Сохранить</a></button>
						</div>
					<?php } elseif ($project_id) { ?>
						<div class="col-sm-4">
							<a id="save_button"  class="btn btn-primary btn-big "   href="index.php?option=com_gm_ceiling&view=project&type=gmcalculator&subtype=calendar&id=<?php echo $project_id; ?>">Сохранить</a></button>
						</div>
					<?php } else { ?>
						<div class="col-sm-4">
							<a class="btn btn-primary" href="index.php?option=com_gm_ceiling&view=projects&type=gmcalculator&subtype=calendar">Перейти к графику замеров</a>
						</div>
					<?php } ?>
				<?php } elseif ($this->type === "calculator") { ?>
					<?php if($this->item->project_id) { ?>
						<div class="col-sm-4">
							<a id="save_button"  class="btn btn-primary btn-big "  href="index.php?option=com_gm_ceiling&view=project&type=calculator&subtype=calendar&id=<?php echo $this->item->project_id; ?>">Сохранить</a>
						</div>
					<?php } elseif ($project_id) { ?>
						<div class="col-sm-4">
							<a id="save_button"  class="btn btn-primary btn-big "  href="index.php?option=com_gm_ceiling&view=project&type=calculator&subtype=calendar&id=<?php echo $project_id; ?>">Сохранить</a>
						</div>
					<?php } else { ?>
						<div class="col-sm-4">
							<a class="btn btn-primary" href="index.php?option=com_gm_ceiling&view=projects&type=calculator&subtype=calendar">Перейти к графику замеров</a>
						</div>
					<?php } ?>
				<?php } ?>
				<?php if ($this->type === "gmmanager") { ?>
					<div class="col-sm-4">
						<a id="save_button" class="btn btn-primary btn-big" href="<?php echo $_SESSION['url']; ?>">Сохранить</a>
					</div>
				<?php } ?>
				<div class="col-sm-4"></div>
			</div>
		</div>
		<!-- отменить -->
		<div class="container">
			<div class="row sm-margin-bottom">
				<div class="col-sm-4"></div>
				<?php if($this->type === "gmcalculator") { ?>
					<?php if ($this->item->project_id) { ?>
						<div class="col-sm-4">
							<a class="btn btn-primary" href="index.php?option=com_gm_ceiling&view=project&type=gmcalculator&subtype=calendar&id=<?php echo $this->item->project_id; ?>">Отменить</a>
						</div>
					<?php } elseif ($project_id) { ?>
						<div class="col-sm-4">
							<a class="btn btn-primary" href="index.php?option=com_gm_ceiling&view=project&type=gmcalculator&subtype=calendar&id=<?php echo $project_id; ?>">Отменить</a>
						</div>
					<?php } else { ?>
						<div class="col-sm-4">
							<a class="btn btn-primary" href="index.php?option=com_gm_ceiling&view=projects&type=gmcalculator&subtype=calendar">Перейти к графику замеров</a>
						</div>
					<?php } ?>
				<?php } elseif ($this->type === "calculator") { ?>
					<?php if ($this->item->project_id) { ?>
						<div class="col-sm-4">
							<a class="btn btn-primary" style="margin-top:15px;" href="index.php?option=com_gm_ceiling&view=project&type=calculator&subtype=calendar&id=<?php echo $this->item->project_id; ?>">Отменить</a>
						</div>
					<?php } elseif ($project_id) { ?>
						<div class="col-sm-4">
							<a class="btn btn-primary" style="margin-top:15px;" href="index.php?option=com_gm_ceiling&view=project&type=calculator&subtype=calendar&id=<?php echo $project_id; ?>">Отменить</a>
						</div>
					<?php } else { ?>
						<div class="col-sm-4">
							<a class="btn btn-primary" href="index.php?option=com_gm_ceiling&view=projects&type=calculator&subtype=calendar">Перейти к графику замеров</a>
						</div>
					<?php } ?>
					<?php } elseif($this->type === "manager") { ?>
						<div class="col-sm-4">
							<a class="btn btn-primary" href="/index.php?option=com_gm_ceiling&view=mainpage&type=managermainpage">Отменить</a>
						</div>
					<?php } ?>
				<div class="col-sm-4"></div>
			</div>
		</div>		
		<input type="hidden" id="activate" name="activate" value="0"/>
		<input type="hidden" name="option" value="com_gm_ceiling"/>
		<input type="hidden" name="task" value="calculationform.save" id="jform_task" />
		<?php echo JHtml::_('form.token'); ?>
		<!-- кнопка перезвона и два модальных окна Таранцева -->
		<!-- Закоменчена пока Костя не скажет откомментить -->
		<!-- <div id="popup__toggle">
			<div class="circlephone" style="transform-origin: center;"></div><div class="circle-fill" style="transform-origin: center;"></div><div class="img-circle" style="transform-origin: center;"><div class="img-circleblock" style="transform-origin: center;"></div></div>
			<button type="button" id="call-tar" value=""><i class="fa fa-phone fa-phone-tar" aria-hidden="true"></i></button>
		</div>
		<button type="button" id="enroll-tar"><i class="fa fa-pencil-square-o fa-pencil-square-o-tar" aria-hidden="true"></i></button> -->
		<div id="modal-window-container-tar">
			<button type="button" id="close-tar"><i class="fa fa-times fa-times-tar" aria-hidden="true"></i></button>
			<div id="modal-window-call-tar">
				<img src="/images/recall.png" id="image-call-tar" alt="Звонок">
				<p>Скоро мы вам перезвоним</p>
				<p><input type="text" id="name-call-tar" placeholder="Имя" required></p>
				<p><input type="text" id="phone-call-tar" placeholder="Телефон" required></p>
				<p><button type="button" id="re-call-call-tar" class="btn btn-primary">Заказать обратный звонок</button></p>
			</div>
		</div>
		<div id="modal-window-container2-tar">
			<button type="button" id="close2-tar"><i class="fa fa-times fa-times-tar" aria-hidden="true"></i></button>
			<div id="modal-window-enroll-tar">
				<img src="/images/enroll.png" id="image-enroll-tar" alt="Звонок">
				<p>Запишитесь на замер</p>
				<p><input type="text" id="name-enroll-tar" placeholder="Имя"></p>
				<p><input type="text" id="phone-enroll-tar" placeholder="Телефон"></p>
				<p><input type="text" id="adress-enroll-tar" placeholder="Адрес"></p>
				<p><input type="date" id="date-enroll-tar" placeholder="Дата замера"></p>
				<select id="time-tar">
					<option value="9:00">9:00-10:00</option>
					<option value="10:00">10:00-11:00</option>
					<option value="11:00">11:00-12:00</option>
					<option value="12:00">12:00-13:00</option>
					<option value="13:00">13:00-14:00</option>
					<option value="14:00">14:00-15:00</option>
					<option value="15:00">15:00-16:00</option>
					<option value="16:00">16:00-17:00</option>
					<option value="17:00">17:00-18:00</option>
					<option value="18:00">18:00-19:00</option>
					<option value="19:00">19:00-20:00</option>
				</select>
				<p><button type="button" id="re-call-enroll-tar" class="btn btn-primary">Записаться на замер</button></p>
			</div>
		</div>
		<!-- /////////////////////////////////////////////////////////////////// -->
		<div class="top_button_container">
			<a href="#" title="Вернуться к началу" class="topbutton"><i class="fa fa-arrow-up" aria-hidden="true"></i></a>
		</div>
	</form>
</div>
</div>

<script type="text/javascript">

    var $ = jQuery;
	//для подгрузки компонентов со склада
    function GetList(e, select, like) {
        var input = $(e),
            Selects = input.siblings(".Selects"),
            ID = input.attr("id"),
            parent = input.closest(".Form"),
            filter = {
                select: {},
                where: {like: {}},
                group: [],
                order: [],
                page: null
            },
            Select = $('<div/>').addClass("Select"),
            Item = $('<div/>').addClass("Item").attr("onclick", "SelectItem(this);");

        input.attr({"clear": "true", "add": "false"});
        Selects.empty();
        Selects.append(Select);
        var Select = Selects.find(".Select");

        filter.select["Type"] = input.attr("NameDB");
        filter.select["ID"] = "options.id";
        filter.where.like["components.title"] = "'%" + input.val() + "%' || true";
        filter.where.like["options.title"] = "'%" + input.val() + "%'";
        filter.page = "/index.php?option=com_gm_ceiling&task=componentform.getComponents";


        if (input.is(":focus")) {
            jQuery.ajax({
                type: 'POST',
                url: filter.page,
                data: {filter: filter},
                success: function (data) {
                    data = JSON.parse(data);

                    $.each(data, function (i, v) {
                        var I = Item.clone();
                        $.each(v, function (id, s) {
                            if (s === null) s = "Нет";
                            I.attr(id, s);
                            if (id == ID) I.html(s);
                        });
                        Select.append(I);
                    });
                },
                dataType: "text",
                timeout: 10000,
                error: function () {
                    noty({
                        theme: 'relax',
                        layout: 'center',
                        timeout: 1500,
                        type: "error",
                        text: "Сервер не отвечает!"
                    });
                }
            });
        }
    }

    function SelectItem(e) {
        e = $(e);
        var parent = e.closest(".Area"),
            elements = parent.find(".Input");

        if (typeof e.attr('error') !== 'undefined' && e.attr('error') !== false)
        {
            var error = JSON.parse(e.attr('error'));
            $.each(error, function (i, v) {
                noty({
                    theme: 'relax',
                    layout: 'center',
                    timeout: 1500,
                    type: "error",
                    text: v
                });
            });
        }
        else if (e.hasClass("Add")) e.closest(".Area").find(".Input").attr({"clear": "false", "add": "true"});
        else {
            elements.val(e.attr("Type"));
            elements.attr({"clear": "false", "add": "false"});
            parent.find("#ID").val(e.attr("ID"));
        }
    }

    function ClearSelect(e) {
        setTimeout(function () {
            e = $(e);
            if (e.attr("clear") != 'false') e.val("");
            e.siblings(".Selects").empty();
        }, 200);
    }

	function submit_form_sketch()
	{
		var regexp_d = /^\d+$/;
		if (!regexp_d.test(document.getElementById('jform_n2').value)
			|| !regexp_d.test(document.getElementById('jform_color').value)
			|| !regexp_d.test(document.getElementById('user_id').value))
		{
			alert("Неверный формат входных данных!");
			return;
		}
		document.getElementById('url').value = window.location.href.replace(/\#.*/, '');
		document.getElementById('texture').value=document.getElementById('jform_n2').value;
		document.getElementById('color').value=document.getElementById('jform_color').value;
		document.getElementById('manufacturer').value=document.getElementById('jform_proizv').value;
        document.getElementById('auto').value=document.getElementById('flag_auto').value;
        document.getElementById('n4').value=document.getElementById('jform_n4').value;
        document.getElementById('n5').value=document.getElementById('jform_n5').value;
        document.getElementById('n9').value=document.getElementById('jform_n9').value;
		<?php if(!$new || $type === "gmcalculator" || $type === "calculator"||$type === "gmmanager"  ) { ?>
			document.getElementById('calc_title').value=document.getElementById('jform_calculation_title').value;
		<?php } ?>

		document.getElementById('form_url').submit();
		
	}

	/* //////////////////////////////// Убрать банер, при тыке в другое место Таранцева //////////////////////////////// */

	jQuery(document).mouseup(function (e){ // событие клика по веб-документу
		var div = jQuery("#modal-window-call-tar"); // тут указываем ID элемента
		if (!div.is(e.target) // если клик был не по нашему блоку
		    && div.has(e.target).length === 0) { // и не по его дочерним элементам
			jQuery("#close-tar").hide();
			jQuery("#modal-window-container-tar").hide();
			jQuery("#modal-window-call-tar").hide();
		}
		var div1 = jQuery("#modal-window-enroll-tar"); // тут указываем ID элемента
		if (!div1.is(e.target) // если клик был не по нашему блоку
		    && div1.has(e.target).length === 0) { // и не по его дочерним элементам
			jQuery("#close2-tar").hide();
			jQuery("#modal-window-container2-tar").hide();
			jQuery("#modal-window-enroll-tar").hide();
		}
	});

	jQuery(document).ready(function() {

		// кнопки открытия скрытых полей
		jQuery("#btn_chandelier").click( function () {
			jQuery("#chandelier").toggle();
			if (jQuery("#btn_chandelier").css("background-color") == "rgb(65, 64, 153)") {
				jQuery("#btn_chandelier").css("background-color", "#010084");
			} else {
				jQuery("#btn_chandelier").css("background-color", "#414099");
			}
		});
		if (jQuery("#jform_n12").val() != null && jQuery("#jform_n12").val() != undefined && jQuery("#jform_n12").val() != "") {
			jQuery("#chandelier").show();
			jQuery("#btn_chandelier").css("background-color", "#010084");
		}
		jQuery("#btn_fixtures").click( function () {
			jQuery(".fixtures").toggle();
			if (jQuery("#btn_fixtures").css("background-color") == "rgb(65, 64, 153)") {
				jQuery("#btn_fixtures").css("background-color", "#010084");
			} else {
				jQuery("#btn_fixtures").css("background-color", "#414099");
			}
		});
		if ((jQuery("#n13_count").val() != null && jQuery("#n13_count").val() != undefined && jQuery("#n13_count").val() != "") || (jQuery("#ecola_count").val() != null && jQuery("#ecola_count").val() != undefined && jQuery("#ecola_count").val() != "")) {
			jQuery(".fixtures").show();
			jQuery("#btn_fixtures").css("background-color", "#010084");
		}
		jQuery("#btn_cornice").click( function () {
			jQuery(".cornice").toggle();
			if (jQuery("#btn_cornice").css("background-color") == "rgb(65, 64, 153)") {
				jQuery("#btn_cornice").css("background-color", "#010084");
			} else {
				jQuery("#btn_cornice").css("background-color", "#414099");
			}
		});
		if ((jQuery("#jform_n27").val() != null && jQuery("#jform_n27").val() != undefined && jQuery("#jform_n27").val() != "") || (jQuery("#n15_count").val() != null && jQuery("#n15_count").val() != undefined && jQuery("#n15_count").val() != "")) {
			jQuery(".cornice").show();
			jQuery("#btn_cornice").css("background-color", "#010084");
		}
		jQuery("#btn_pipes").click( function () {
			jQuery("#pipes").toggle();
			if (jQuery("#btn_pipes").css("background-color") == "rgb(65, 64, 153)") {
				jQuery("#btn_pipes").css("background-color", "#010084");
			} else {
				jQuery("#btn_pipes").css("background-color", "#414099");
			}
		});
		if (jQuery("#n14_count").val() != null && jQuery("#n14_count").val() != undefined && jQuery("#n14_count").val() != "") {
			jQuery("#pipes").show();
			jQuery("#btn_pipes").css("background-color", "#010084");
		}
		jQuery("#btn_tile").click( function () {
			jQuery("#jform_n7").toggle();
			if (jQuery("#btn_tile").css("background-color") == "rgb(65, 64, 153)") {
				jQuery("#btn_tile").css("background-color", "#010084");
			} else {
				jQuery("#btn_tile").css("background-color", "#414099");
			}
		});
		if (jQuery("#jform_n7").val() != null && jQuery("#jform_n7").val() != undefined && jQuery("#jform_n7").val() != "") {
			jQuery("#jform_n7").show();
			jQuery("#btn_tile").css("background-color", "#010084");
		}
		jQuery("#btn_stoneware").click( function () {
			jQuery("#jform_n8").toggle();
			if (jQuery("#btn_stoneware").css("background-color") == "rgb(65, 64, 153)") {
				jQuery("#btn_stoneware").css("background-color", "#010084");
			} else {
				jQuery("#btn_stoneware").css("background-color", "#414099");
			}
		});
		if (jQuery("#jform_n8").val() != null && jQuery("#jform_n8").val() != undefined && jQuery("#jform_n8").val() != "") {
			jQuery("#jform_n8").show();
			jQuery("#btn_stoneware").css("background-color", "#010084");
		}
		jQuery("#btn_wire").click( function () {
			jQuery("#jform_n19").toggle();
			if (jQuery("#btn_wire").css("background-color") == "rgb(65, 64, 153)") {
				jQuery("#btn_wire").css("background-color", "#010084");
			} else {
				jQuery("#btn_wire").css("background-color", "#414099");
			}
		});
		if (jQuery("#jform_n19").val() != null && jQuery("#jform_n19").val() != undefined && jQuery("#jform_n19").val() != "") {
			jQuery("#jform_n19").show();
			jQuery("#btn_wire").css("background-color", "#010084");
		}
		jQuery("#btn_bar").click( function () {
			jQuery("#jform_n17").toggle();
			if (jQuery("#btn_wire").css("background-color") == "rgb(65, 64, 153)") {
				jQuery("#btn_wire").css("background-color", "#010084");
			} else {
				jQuery("#btn_wire").css("background-color", "#414099");
			}
		});
		if (jQuery("#jform_n17").val() != null && jQuery("#jform_n17").val() != undefined && jQuery("#jform_n17").val() != "") {
			jQuery("#jform_n17").show();
			jQuery("#btn_wire").css("background-color", "#010084");
		}
		jQuery("#btn_soaring").click( function () {
			jQuery("#jform_n30").toggle();
			if (jQuery("#btn_soaring").css("background-color") == "rgb(65, 64, 153)") {
				jQuery("#btn_soaring").css("background-color", "#010084");
			} else {
				jQuery("#btn_soaring").css("background-color", "#414099");
			}
		});
		if (jQuery("#jform_n30").val() != null && jQuery("#jform_n30").val() != undefined && jQuery("#jform_n30").val() != "") {
			jQuery("#jform_n30").show();
			jQuery("#btn_soaring").css("background-color", "#010084");
		}
		jQuery("#btn_level").click( function () {
			jQuery("#level").toggle();
			if (jQuery("#btn_level").css("background-color") == "rgb(65, 64, 153)") {
				jQuery("#btn_level").css("background-color", "#010084");
			} else {
				jQuery("#btn_level").css("background-color", "#414099");
			}
		});
		if (jQuery("#n29_count").val() != null && jQuery("#n29_count").val() != undefined && jQuery("#n29_count").val() != "") {
			jQuery("#level").show();
			jQuery("#btn_level").css("background-color", "#010084");
		}
		jQuery("#btn_firealarm").click( function () {
			jQuery("#jform_n21").toggle();
			jQuery("#btn_firealarm").hide();
		});
		if (jQuery("#jform_n21").val() != null && jQuery("#jform_n21").val() != undefined && jQuery("#jform_n21").val() != "") {
			jQuery("#jform_n21").show();
			jQuery("#btn_firealarm").hide();
		}
		
		//------------------------------

		jQuery("body").addClass("yellow_home");

		if(jQuery("#jform_n4").val()==0 && jQuery("#jform_n5").val()==0 && jQuery("#jform_n9").val()==0)
		{
			jQuery("#sketch_image_block").css("display", "none");
			jQuery("#data-wrapper").css("display", "none");
		}

		/*///////////////////////////// Меняющиеся кнопки Таранцева ///////////////////////////////////////*/
		jQuery("#enroll-tar").mouseover(function() {
			timerId = setTimeout(function() {
				jQuery("#enroll-tar").text("Записаться на замер");
			}, 200);
		});
		
		jQuery("#enroll-tar").mouseout(function() {
			jQuery("#enroll-tar").html('<i class="fa fa-pencil-square-o fa-pencil-square-o-tar" aria-hidden="true"></i>');
			clearTimeout(timerId);
		});
		//---------------------------------------------------------------------------------------
		
		/*////////////////////////всплывающие окна для телефона Таранцева////////////////////////////// */
		jQuery("#phone-call-tar").mask('+7(999)999-99-99');
		jQuery("#phone-enroll-tar").mask('+7(999)999-99-99');

		jQuery("#call-tar").click(function() {
			jQuery("#modal-window-container-tar").show();
			jQuery("#modal-window-call-tar").show("slow");
			jQuery("#close-tar").show();
	    });

		jQuery("#enroll-tar").click(function() {
			jQuery("#modal-window-container2-tar").show();
			jQuery("#modal-window-enroll-tar").show("slow");
			jQuery("#close2-tar").show();
		});

		jQuery("#close-tar").click(function() {
			jQuery("#modal-window-container-tar").hide();
			jQuery("#modal-window-enroll-tar").hide();
			jQuery("#modal-window-call-tar").hide();
			jQuery("#close-tar").hide();
		});

		jQuery("#re-call-call-tar").click(function() {
			var regexp = /^[А-Яа-я\s]+$/;
			var regexp_p = /^[\(\)\+\-\s\d]+$/;
			if (!regexp.test(document.getElementById('name-call-tar').value))
			{
				alert('Имя содержит недопустимые символы!');
				return;
			}
			if (!regexp_p.test(document.getElementById('phone-call-tar').value))
			{
				alert('Телефон содержит недопустимые символы!');
				return;
			}

			jQuery.ajax({
				type: 'POST',
				url: "index.php?option=com_gm_ceiling&task=getDataFromPromo",
				data: {	
					name: document.getElementById('name-call-tar').value,
					phone: document.getElementById('phone-call-tar').value,
					action: "Обратный звонок",
					api_phone_id: <?php echo $rek; ?>
				},
				success: function(data){
					console.log(data);
					var n = noty({
						theme: 'relax',
						timeout: 2000,
						layout: 'center',
						maxVisible: 5,
						type: "success",
						text: "Звонок заказан."
					});
				},
				dataType: "text",
				timeout: 10000,
				error: function(data){
					console.log(data);
					var n = noty({
						theme: 'relax',
						timeout: 2000,
						layout: 'center',
						maxVisible: 5,
						type: "error",
						text: "Ошибка!"
					});
				}				
			});

			jQuery("#name-call-tar").hide("slow");
			jQuery("#phone-call-tar").hide("slow");
			jQuery("#re-call-call-tar").hide("slow");
	    });
		// -------------------------------------

		jQuery("#re-call-enroll-tar").click(function() {
			var regexp = /^[А-Яа-я\s]+$/;
			var regexp_p = /^[\(\)\+\-\s\d]+$/;
			if (!regexp.test(document.getElementById('name-enroll-tar').value))
			{
				alert('Имя содержит недопустимые символы!');
				return;
			}
			if (!regexp_p.test(document.getElementById('phone-enroll-tar').value))
			{
				alert('Телефон содержит недопустимые символы!');
				return;
			}

			jQuery.ajax({
				type: 'POST',
				url: "index.php?option=com_gm_ceiling&task=getDataFromPromo",
				data: {	
					name: document.getElementById('name-enroll-tar').value,
					phone: document.getElementById('phone-enroll-tar').value,
					adress: document.getElementById('adress-enroll-tar').value,
					date: document.getElementById('date-enroll-tar').value,
					time: document.getElementById('time-tar').value,
					action: "Запись на замер",
					api_phone_id: <?php echo $rek; ?>
				},
				success: function(data){
					console.log(data);
					var n = noty({
						theme: 'relax',
						timeout: 2000,
						layout: 'center',
						maxVisible: 5,
						type: "success",
						text: "Заявка принята."
					});
				},
				dataType: "text",
				timeout: 10000,
				error: function(data){
					console.log(data);
					var n = noty({
						theme: 'relax',
						timeout: 2000,
						layout: 'center',
						maxVisible: 5,
						type: "error",
						text: "Ошибка!"
					});
				}				
			});

			jQuery("#name-enroll-tar").hide("slow");
			jQuery("#phone-enroll-tar").hide("slow");
			jQuery("#adress-enroll-tar").hide("slow");
			jQuery("#date-enroll-tar").hide("slow");
			jQuery("#time-tar").hide("slow");
			jQuery("#re-call-enroll-tar").hide("slow");
	    });

        jQuery("#sketch_switch").click(function(){
            jQuery("#flag_auto").val(0);
            submit_form_sketch();
        });
	   
		jQuery("#user_phone").mask("+7 (999) 999-99-99");
		jQuery("#jform_client_contacts-top").mask("+7 (999) 999-99-99");
		jQuery("#jform_client_contacts").mask("+7 (999) 999-99-99");

		var handle = jQuery( "#custom-handle" );
		
		jQuery( "#scroll_down" ).click(function(){
			jQuery("html, body").scrollTo( jQuery( ".show_after_calculate" ), 1000);
		});
		
		var n2_start_option = "<option value='' selected>- Выберите фактуру -</option>",
		n3_start_option = "<option value='' selected>- Выберите производителя материала -</option>";
		
		jQuery( "#extra_components_button" ).click(function(){
			var extra_components_title_container = jQuery( "#extra_components_title_container" ),
			extra_components_value_container = jQuery( "#extra_components_value_container" );
			jQuery( "<div class='form-group'><input name='extra_components_title[]' value='' class='form-control' type='text'></div>" ).appendTo( extra_components_title_container );
			jQuery( "<div class='form-group'><input name='extra_components_value[]' value='' class='form-control' type='tel'></div>" ).appendTo( extra_components_value_container );
		});
		
		jQuery( "#extra_mounting_button" ).click(function(){
			var extra_mounting_title_container = jQuery( "#extra_mounting_title_container"),
			extra_mounting_value_container = jQuery( "#extra_mounting_value_container");
			jQuery( "<div class='form-group'><input name='extra_mounting_title[]' value='' class='form-control' type='text'></div>" ).appendTo( extra_mounting_title_container );
			jQuery( "<div class='form-group'><input name='extra_mounting_value[]' value='' class='form-control' type='tel'></div>" ).appendTo( extra_mounting_value_container );
		});

        jQuery( "#components_button_stock" ).click(function(){
            var components_title_stock_container = jQuery( "#components_title_stock_container" ),
                components_value_stock_container = jQuery( "#components_value_stock_container" );
            jQuery("<div class='form-group Area'><input value='' id='Type' autocomplete=\"off\"\n" +
                "        NameDB=\"CONCAT(components.title,' ',options.title)\"\n" +
                "        onclick=\"GetList(this, ['Type'], ['Type']);\"\n" +
                "        onkeyup=\"GetList(this, ['Type'], ['Type']);\"\n" +
                "        onblur=\"ClearSelect(this)\"\n" +
                "    class='form-control Input Type'\n" +
                "        type='text'><input id=\"ID\" name='components_title_stock[]'  hidden> <div class='Selects Type'></div></div>").appendTo(components_title_stock_container);
            jQuery( "<div class='form-group'><input name='components_value_stock[]' value='' class='form-control' type='tel'></div>" ).appendTo( components_value_stock_container );
        });

		
		//Автозамена запятой на точку
		jQuery( "input[type=tel]" ).on("keyup",function(){
			jQuery( this ).val( jQuery( this ).val().replace(',', '.') );
		});
		
		//Автозамена запятой на точку
		jQuery( "input" ).on("keyup",function(e){
			var code = (e.keyCode ? e.keyCode : e.which);
			if (code==13) {
				var next = jQuery( this ).data("next"),
				finish = jQuery( this ).data("finish");
				if( typeof(next) != "undefined" && next.length > 0 ) {
					jQuery( next ).focus();
				}
				if( typeof(finish) != "undefined" && finish.length > 0 ) {
					jQuery( finish ).click();
				}
			}
		});
		
        function change_flag_auto(){
			jQuery("#flag_auto").val(1);
        }
		jQuery( "#color_switch" ).click(function(){
			if(jQuery( "#jform_n2").val() != 0 ) { 
                change_flag_auto();
				jQuery ( "#jform_proizv" ).prop("disabled", false);			
				jQuery.getJSON( "index.php?option=com_gm_ceiling&task=getColorList&texture_id=" + jQuery( "#jform_n2").val() , 
				function( data ) { 
				
					var items = "<div class='center'>";
					jQuery.each( data, function( key, val ) {
						items += "<button class='click_color' type='button' data-color_id='"+ val[0] + "' data-color_img='" + val[2] + "'><img src='"+ val[2] + "' alt='' /><div class='color_title1'>" + val[1] + "</div><div class='color_title2'>" + val[1]+ "</div></button>";
					
					});
					items += "</div>";
					modal({
						type: 'info',
						title: 'Выберите цвет',
						text: items,
						size: 'large',
						onShow: function() {
							jQuery(".click_color").click(function(){
								jQuery("#jform_color").val( jQuery( this ).data("color_id") );
								jQuery("#color_img").prop( "src", jQuery( this ).data("color_img") );
							
							});
						},
						callback: function(result) {
							
						},
						autoclose: false,
						center: true,
						closeClick: true,
						closable: true,
						theme: 'xenon',
						animate: true,
						background: 'rgba(0,0,0,0.35)',
						zIndex: 1050,
						buttonText: {
							ok: 'Позвоните мне',
							cancel: 'Закрыть'
						},
						template: '<div class="modal-box"><div class="modal-inner"><div class="modal-title"><a class="modal-close-btn"></a></div><div class="modal-text"></div><div class="modal-buttons"></div></div></div>',
						_classes: {
							box: '.modal-box',
							boxInner: ".modal-inner",
							title: '.modal-title',
							content: '.modal-text',
							buttons: '.modal-buttons',
							closebtn: '.click_color'
						}
					});						
				});
			}
		});

		jQuery( "#color_switch_1" ).click(function(){		
				jQuery.getJSON( "index.php?option=com_gm_ceiling&task=getColor" , 
				function( data ) { 
				
					var items = "<div class='center'>";
					jQuery.each( data, function( key, val ) {
						items += "<button class='click_color_1' style='width: 70px; height: 80px; display: inline-block; float: left; margin:3px;' type='button' data-color_id_1='"+ val[0] + "' data-color_img_1='" + val[2] + "'><img style='width: 70px; height: 70px; display: inline-block; float: left; margin:3px;' src='"+ val[2] + "' alt='' /><div class='color_title1'>" + val[1] + "</div><div class='color_title2'>" + val[1]+ "</div></button>";
					
					});
					items += "</div>";
					modal({
						type: 'info',
						title: 'Выберите цвет',
						text: items,
						size: 'large',
						onShow: function() {
							jQuery(".click_color_1").click(function(){ 
							
								jQuery("#jform_color_1").val( jQuery( this ).data("color_id_1"));
								jQuery("#color_img_1").prop( "src", jQuery( this ).data("color_img_1") );
								
						
							});
						},
						callback: function(result) {
							

						},
						autoclose: false,
						center: true,
						closeClick: true,
						closable: true,
						theme: 'xenon',
						animate: true,
						background: 'rgba(0,0,0,0.35)',
						zIndex: 1050,
						buttonText: {
							ok: 'Позвоните мне',
							cancel: 'Закрыть'
						},
						template: '<div class="modal-box"><div class="modal-inner"><div class="modal-title"><a class="modal-close-btn"></a></div><div class="modal-text"></div><div class="modal-buttons"></div></div></div>',
						_classes: {
							box: '.modal-box',
							boxInner: ".modal-inner",
							title: '.modal-title',
							content: '.modal-text',
							buttons: '.modal-buttons',
							closebtn: '.click_color_1'
						}
					});						
				});
			
		});

		jQuery( "#reserve_button" ).click(function(){
			if(jQuery( "#jform_client_name").val().length > 0 && jQuery( "#jform_client_contacts").val().length > 0) {
				jQuery("#form-calculation").submit();
			}
		});
		jQuery( "#reserve_button-top" ).click(function(){
			if(jQuery( "#jform_client_name-top").val().length > 0 && jQuery( "#jform_client_contacts-top").val().length > 0) {
				jQuery("#form-calculation").submit();
			}
		});
		
		var n22_change = function () {
        var n22 =  document.getElementById('n22');
        var n22_1 =  document.getElementById('n22_1');
        if(n22.options[n22.selectedIndex].value == 1 || n22.options[n22.selectedIndex].value == 3){
            for(i=0;i<46;i++){
                n22_1.options[i].removeAttribute('disabled');
            }
            for(i=46;i<n22_1.options.length;i++){
               n22_1.options[i].setAttribute('disabled', true);
            }
        }
        else {
            if(n22.options[n22.selectedIndex].value == 2 || n22.options[n22.selectedIndex].value == 4){
                for(i=46;i<n22_1.options.length;i++){
                    n22_1.options[i].removeAttribute('disabled');
                }
                for(i=0;i<46;i++){
                    n22_1.options[i].setAttribute('disabled', true);
                }
            }
            }
        };
		jQuery("#n22").change(n22_change).change();
		
		jQuery( "#jform_n2" ).html( n2_start_option );
		
		jQuery( "#jform_n3" ).html( n3_start_option );

		jQuery.getJSON( "index.php?option=com_gm_ceiling&task=getTexturesList&jform_n1=28", function( data ) {
            console.log(data);
			var items = [];
			jQuery.each( data, function( key, val ) {
				items.push( "<option value='" + val[0] + "' data-texture_colored='" + val[2] + "'>" + val[1] + "</option>" );
			});

			jQuery( "#jform_n2" ).html( n2_start_option + items.join( "" ) );

			jQuery( "#jform_n3" ).html( n3_start_option );
			
			<?php if(!$new) { ?>
				
				jQuery("#jform_n2").val( jQuery("#jform_n2_hidden").val() );
				jQuery('#jform_n2 option:selected').each(function(){
					this.selected=false;
				});
				jQuery("#jform_n2 [value='" + jQuery("#jform_n2_hidden").val() +"']").attr("selected", "selected");
				jQuery("#jform_n2").change();
				
				<?php } ?>
				change_select_texture();
			});

		var ecolaFlagLoad = false;
		var ecola_items = [];
		jQuery.getJSON("index.php?option=com_gm_ceiling&task=getEcolaList",function(data){
			jQuery.each(data,function(key,val){		
				ecola_items.push( "<option value='" + val.id + "'>" + val.title + "</option>" );
			});
			if (ecolaFlagLoad) jQuery("#add_ecola").trigger('click');
			ecolaFlagLoad = true;

		});
		var ecola_lamps = [];
		jQuery.getJSON("index.php?option=com_gm_ceiling&task=getEcolaBulbs",function(data){
			jQuery.each(data,function(key,val){
				ecola_lamps.push( "<option value='" + val.id + "'>" + val.title + "</option>" );
			});	
			if (ecolaFlagLoad) jQuery("#add_ecola").trigger('click');
			ecolaFlagLoad = true;
		});

		var level = [];
    
        jQuery.getJSON("index.php?option=com_gm_ceiling&task=getListProfil", function (data) {
            jQuery.each(data, function (key, val) {
                var image = (val.image) ? "data:image/gif;base64," + val.image : "";
                var value = "value=\"" + val.id + "\"";
                var option = "<div class='OPTION_CUSTOM' onclick=\"OPTION_CUSTOM_CLICK(this);\" " + value + " > " + val.title + "<img src='" + image + "'alt='' width='40px' height='60px'    style = 'float: right;'  class=arrow /></div>";

                level.push(option);
            });
            jQuery("#add_level").trigger('click');
        });

		var rings = [];
		jQuery.getJSON("index.php?option=com_gm_ceiling&task=getListRings",function(data){
			var opt;
			jQuery.each(data,function(key,val){
				opt = document.createElement('option');
    			opt.value = val.id;
    			opt.id = key;
    			opt.text = val.title;
				rings.push(opt);
			});
			rings.sort(function compareNumeric(a, b) {
                var c = parseInt(a.text),
                    d = parseInt( b.text );
                if( c < d ){ return -1;
                }else if( c > d ){ return 1;  }
			});
			jQuery("#add_n13").trigger('click');
			jQuery("#add_n22").trigger('click');
		});

		var th_squares = [];
		jQuery.getJSON("index.php?option=com_gm_ceiling&task=getListThermalSquare",function(data){
			var opt;
			jQuery.each(data,function(key,val){
				opt = document.createElement('option');
    			opt.value = val.id;
    			opt.id = key;
    			opt.text = val.title;
				th_squares.push(opt);
			});
            th_squares.sort(function compareNumeric(a, b) {
                var c = parseInt(a.text),
                    d = parseInt( b.text );
                if( c < d ){ return -1;
                }else if( c > d ){ return 1;  }
            });
		});

		var cornice = [];
		jQuery.getJSON("index.php?option=com_gm_ceiling&task=getListCornice",function(data){
			var opt;
			jQuery.each(data,function(key,val){
				opt = document.createElement('option');
    			opt.value = val.id;
    			opt.id = key;
    			opt.text = val.title;
				cornice.push(opt);
			});
			jQuery("#add_n15").trigger('click');
		});

		var bypasses = [];
		jQuery.getJSON("index.php?option=com_gm_ceiling&task=getListBypass",function(data){
			jQuery.each(data,function(key,val){
			    if(val.title == "32 мм")
				bypasses.push( "<option value='" + val.id + "' selected >" + val.title + "</option>" );
			    else bypasses.push( "<option value='" + val.id + "' >" + val.title + "</option>" );
			});
			jQuery("#add_n14").trigger('click');
		});
		
		var difusors = [];
		jQuery.getJSON("index.php?option=com_gm_ceiling&task=getListDiffuzor",function(data){
			jQuery.each(data,function(key,val){
				difusors.push( "<option value='" + val.id + "'>" + val.title + "</option>" );
			});
			jQuery("#add_n23").trigger('click');		
		});

		jQuery("#extra_components_button").trigger('click');
		jQuery("#extra_mounting_button").trigger('click');
        jQuery("#components_button_stock").trigger('click');

		jQuery("#jform_n6_1").change(function(){
			jQuery("#jform_color_switch-lbl_1").fadeIn();
			jQuery("#color_switch_1").fadeIn();
			jQuery("#jform_n6_1").attr("checked");
		});
		jQuery("#jform_n6").change(function(){
			jQuery("#jform_color_switch-lbl_1").fadeOut();
			jQuery("#color_switch_1").fadeOut();
			jQuery("#color_img_1").prop("src","");
			jQuery("#jform_color_1").val("314");
			jQuery("#jform_n6").attr("checked");
			
		});
		
		jQuery("#jform_n6_2").change(function(){
			jQuery("#jform_color_switch-lbl_1").fadeOut();
			jQuery("#color_switch_1").fadeOut();
			jQuery("#color_img_1").prop("src","");
			jQuery("#jform_color_1").val("0");
			jQuery("#jform_n6_2").attr("checked");
		});

        var jform_proizv_flag = true;
		jQuery("#jform_n2" ).change(function(){
			if( jQuery ( this ).val() > 0 ) {
			    if(jQuery ( this ).val() == 29) { jQuery("#block_n6").hide(); jQuery("#block_n28").hide(); jQuery("#razdelitel").hide(); jQuery("#n30_block").hide(); }
			    else { jQuery("#block_n6").show(); jQuery("#block_n28").show(); jQuery("#razdelitel").show();  jQuery("#n30_block").show(); }
                change_flag_auto();
				if(jQuery( "#jform_n2 option:selected" ).data("texture_colored") == 1){
					jQuery("#jform_color_switch-lbl").fadeIn();
					jQuery("#color_switch").fadeIn();
				} else {
					jQuery("#jform_color_switch-lbl").fadeOut();
					jQuery("#color_switch").fadeOut();
					jQuery("#color_img").prop("src","");
					jQuery("#jform_color").val("0");
					jQuery ( "#jform_proizv" ).prop("disabled", false);	
				
				}
				var _this = this;
				var jform_proizvod_id = <?=(empty($this->item->n3))?0:$this->item->n3;?>;
				var jform_proizv_id  = (jform_proizv_flag)?<?=($this->item->n3)?("\"&jform_proizv=".$this->item->n3."\""):"\"\"";?>:"";
				var jform_proizv_start = (jform_proizv_flag)?<?=($this->item->n3)?"null":"n3_start_option";?>:n3_start_option;
				jQuery.getJSON( "index.php?option=com_gm_ceiling&task=getManufacturersList&jform_n2=" + jQuery( this ).val() + jform_proizv_id, function( data ) {

					var items = [];
					jQuery.each( data, function( key, val ) {
						items.push( "<option value='"+val[0]+"' "+((val[2]==jform_proizvod_id)?"selected":"")+">" + val[0] +" "+ val[1] + "</option>" );
						
					});
					jQuery( "#jform_proizv" ).html( jform_proizv_start + items.join( "" ) );
					change_select_manufacturer();
					
				});
				

			} else {
				jQuery ( "#jform_n3" ).prop("disabled", true);
				jQuery( "#jform_n3" ).html( n3_start_option );
			}
			jform_proizv_flag = false;
		});
		if(jQuery("#jform_n2" ).val() && jQuery("#jform_proizv").val() ){
			
			jQuery.getJSON( "index.php?option=com_gm_ceiling&task=getCanvasesList&jform_n2=" + jQuery("#jform_n2" ).val()+"&jform_proizv="+jQuery("#jform_proizv").val(), function( data ) {
					jQuery("#width").val(data);
			});
		}
		jQuery( "#jform_proizv" ).change(function(){
			jQuery("#calculate_button").prop("disabled",false);
			change_flag_auto();
			jQuery.getJSON( "index.php?option=com_gm_ceiling&task=getCanvasesList&jform_n2=" + jQuery("#jform_n2" ).val()+"&jform_proizv="+jQuery(this ).val(), function( data ) {
				var items = [];
				jQuery("#width").val(data);
				if(data!=""){
					jQuery("#sketch_switch").prop("disabled",false);
				}
			});
		});
		//Запрос к серверу на отправку сметы на почту
		jQuery( "#send_to_email" ).click(function(){
			console.log(jQuery("#jform_id").val());
			var reg = /^[-._a-z0-9]+@(?:[a-z0-9][-a-z0-9]+\.)+[a-z]{2,6}$/;
			if(reg.test(jQuery("#send_email").val())){
				if(jQuery("#form-calculation").validationEngine('validate')) {
					 jQuery.ajax({
						type: 'POST',
						url: "index.php?option=com_gm_ceiling&task=sendClientEstimate",
						data: {
							id : jQuery("#jform_id").val(),
							email : jQuery("#send_email").val()
						},
						success: function(data){
							jQuery('#send_email_success').slideDown();
						},
						dataType: "text",
						timeout: 10000,
						error: function(){
							var n = noty({
								theme: 'relax',
								timeout: 2000,
								layout: 'center',
								maxVisible: 5,
								type: "error",
								text: "Ошибка при попытке рассчитать. Сервер не отвечает"
							});
							calculate_button.removeClass("loading");
							calculate_button.find("span.loading").hide();
							calculate_button.find("span.static").show();
						}					
					}); 
				}
			}
			else{
				var n = noty({
					theme: 'relax',
					timeout: 2000,
					layout: 'center',
					maxVisible: 5,
					type: "error",
					text: "Некорректный e-mail"
				});
				jQuery("#send_email").focus();
			}
		});

		jQuery( "#calculate_button" ).click(function(){
            if(jQuery("#flag_auto").val()==1){
				var id = <?php echo $calc_id;?>;
				if((id == 0) || (id!=0 && jQuery("#jform_original_name").val()!="" )){
                    console.log('0');
					jQuery.ajax({
						type: 'POST',
						url: "index.php?option=com_gm_ceiling&task=send_sketch",
						data: {
							filename: jQuery("#jform_original_name").val(),
							from_db:0 
						},
						success: function (data) {
							jQuery("#walls").val(data);
							submit_form_sketch();

						},
						error: function (data) {
							console.log(data);
						}
					});
				}
                else{
                    if(id!=0){
                        console.log('!0');
                        jQuery.ajax({
                            type: 'POST',
                            url: "index.php?option=com_gm_ceiling&task=send_sketch",
                            data: {
                                id:id,
                                from_db:1 
                            },
                            success: function (data) {
                                jQuery("#walls").val(data);
                                submit_form_sketch();
                            },
                            error: function (data) {
                                console.log(data);
                            }
                        });
                    }
                }
            }
			jQuery('#send_email_success').slideUp();
			jQuery('#order_button').show();
			
			if(jQuery("#form-calculation").validationEngine('validate')) {
				var calculate_button = jQuery( this );
				if( !calculate_button.hasClass("loading") ) {
					calculate_button.addClass("loading");
					calculate_button.find("span.static").hide();
					calculate_button.find("span.loading").show();

					jQuery( "input[type=checkbox]").each(function(){
						if( jQuery( this ).is(':checked') ) {
							jQuery( this ).val( 1 );
						} else {
							jQuery( this ).val( 0 );
						}
					});
					
					var temp_task = jQuery("#jform_task").val();
					jQuery("#jform_task").val( "calculate" );
					data = jQuery( "#form-calculation").serialize();
					jQuery("#jform_task").val( temp_task );
					
					<?php if($type === "gmcalculator"||$type === "calculator" || ( $type === "gmmanager" && $subtype === "calendar")) { ?>
											
						var additional = "&save=1&pdf=1&del_flag=1&need_mount="+jQuery("input[name = 'need_mount']").val();
						 
						<?php } else if ($type === "gmmanager" || $type === "manager" || $type === "guest") {  ?>
					    var additional = "&save=0&pdf=0&del_flag=1&need_mount="+jQuery("input[name = 'need_mount']").val();
						<?php } else { ?>
							var additional = "";
							<?php } ?>
							jQuery.ajax({
								type: 'POST',
								url: "index.php?option=com_gm_ceiling&task=calculate"+additional,
								data: data,
								success: function(data){
									var html = "",
									total_sum = parseFloat(data.total_sum),
									project_discount = parseFloat(data.project_discount),
									dealer_final = parseFloat(total_sum) * ((100 - parseFloat(project_discount)) / 100);
                                    discount_price = parseFloat(total_sum) * (70 / 100);
                                    mount_price  = parseFloat(data.mounting_sum);
                                    discount_without  = parseFloat(total_sum - mount_price) * (70 / 100);
                                    jQuery("#result_block").show();
                                    jQuery("#jform_id").val(data.id);
                                    jQuery('#guest_price').show();
                                    jQuery("#total_price").text( total_sum.toFixed(0) );
                                    jQuery("#final_price").text( dealer_final.toFixed(0) );
                                    jQuery("#discount_price").text( discount_price.toFixed(0) );
                                    jQuery("#final_price_without").text( (dealer_final-mount_price).toFixed(0));
                                    jQuery("#discount_price_without").text( discount_without.toFixed(0) );
                                    calculate_button.removeClass("loading");
                                    calculate_button.find("span.loading").hide();
                                    calculate_button.find("span.static").show();
                                    jQuery("#info").show();
						},
						dataType: "json",
						timeout: 10000,
						error: function(data){
							var n = noty({
								theme: 'relax',
								timeout: 2000,
								layout: 'center',
								maxVisible: 5,
								type: "error",
								text: "Ошибка при попытке рассчитать. Сервер не отвечает"
							});
							calculate_button.removeClass("loading");
							calculate_button.find("span.loading").hide();
							calculate_button.find("span.static").show();
						}
					});
						}
					}
				});

			jQuery( "#save_button" ).click(function(){
				jQuery('#send_email_success').slideUp();
				jQuery('#order_button').show();
				if(jQuery("#form-calculation").validationEngine('validate')) {
					var save_button = jQuery( this );
					if( !save_button.hasClass("loading") ) {
						save_button.addClass("loading");
						save_button.find("span.static").hide();
						save_button.find("span.loading").show();

						jQuery( "input[type=checkbox]").each(function(){
							if( jQuery( this ).is(':checked') ) {
								jQuery( this ).val( 1 );
							} else {
								jQuery( this ).val( 0 );
							}
						});
						
						var temp_task = jQuery("#jform_task").val();
						jQuery("#jform_task").val( "calculate" );
						data = jQuery( "#form-calculation").serialize();
						jQuery("#jform_task").val( temp_task );
						<?php if($type === "gmcalculator"||$type === "calculator" || ( $type === "gmmanager" && $subtype === "calendar") ) { ?>
							var additional = "&save=1&pdf=1&del_flag=1&need_mount="+jQuery("input[name = 'need_mount']").val();
							
							<?php } else  if ($type === "gmmanager" || $type === "manager" || $type === "guest"){ ?>
							var additional = "&save=1&pdf=1&del_flag=1&need_mount="+jQuery("input[name = 'need_mount']").val();
							<?php } else { ?>
								var additional = "";
								<?php }
								?>
								jQuery.ajax({
									type: 'POST',
									url: "index.php?option=com_gm_ceiling&task=calculate&ajax=1&"+additional,
									data: data,
									success: function(data){
										
										var html = "",
										total_sum = parseFloat(data.total_sum),
										project_discount = parseFloat(data.project_discount),
										dealer_final = parseFloat(total_sum) * ((100 - parseFloat(project_discount)) / 100);
                                        discount_price = parseFloat(total_sum) * (70 / 100);
								jQuery("#result_block").show();
								jQuery("#jform_id").val(data.id);

								jQuery("#total_price").text( total_sum.toFixed(0) );
								jQuery("#final_price").text( dealer_final.toFixed(0) );
                                jQuery("#discount_price").text( discount_price.toFixed(0) );
						
								save_button.removeClass("loading");
								save_button.find("span.loading").hide();
								save_button.find("span.static").show();
								
							},
							dataType: "json",
							timeout: 10000,
							error: function(data){
								var n = noty({
									theme: 'relax',
									timeout: 2000,
									layout: 'center',
									maxVisible: 5,
									type: "error",
									text: "Ошибка при попытке рассчитать. Сервер не отвечает"
								});
								save_button.removeClass("loading");
								save_button.find("span.loading").hide();
								save_button.find("span.static").show();
							}
						});
							}
						}
			});

		
		jQuery( ".clear_form_group" ).click(function(){
			jQuery( this ).closest(".form-group").remove();
		});
			
		function change_n13(){
			change_options(this,"n13_type[]","n13_ring[]");
		}
		function change_n22(){
			change_options(this,"n22_type[]","n22_diam[]")
		}
		function change_n15(){
			change_options15(this,"n15_type[]","n15_size[]");
		}
        var change_options = function (obj,classname1,classname2) {
            var select = jQuery(obj);
            var value = select.val();
			var index = 0;
			var types = jQuery( "select[name=\""+classname1+"\"" );
			index = getSelectIndex(types,obj);
			var selects = jQuery( "select[name=\""+classname2+"\"" );
			if (value == 2 || value == 5 || value == 7){
				selects[index].empty();
				for(i=0;i<rings.length;i++)
					selects[index].appendChild(rings[i].clone());
			}
			else if(value == 3 || value == 6 || value == 8){
				selects[index].empty();
				for(i=0;i<th_squares.length;i++)
					selects[index].appendChild(th_squares[i].clone());
			}
        };
		var change_options15 = function (obj,classname1,classname2) {
        	var select = jQuery(obj);
            var value = select.val();
			var index = 0;
			var types = jQuery( "select[name=\""+classname1+"\"" );
			index = getSelectIndex(types,obj);
			var selects = jQuery( "select[name=\""+classname2+"\"" );
			if (value == 10){
				selects[index].empty();
				for(i=0;i<cornice.length;i++)
					selects[index].appendChild(cornice[i].clone());
			}
        };
		
		jQuery( "#add_n13" ).click(function(){
			var html = "<div class='form-group'>";
			html+= "<div class='advanced_col1'>";
			html+= "<input id = 'n13' name='n13_count[]' class='form-control' value='' placeholder='шт.' >";
			html+= "</div>";
			html+= "<div class='advanced_col2'>";
			html+= "<select class='form-control n13_control'  name='n13_type[]' placeholder='Платформа'>";
			html+= "<option value='2'>Круглые</option>";
			html+= "<option value='3'>Квадратные</option>";
			html+= "</select>";
			html+= "</div>";
			html+= "<div class='advanced_col3'>";
			html+= "<select name='n13_ring[]'  id='n13_1' class='form-control n13_module' placeholder='Размер'>"
			for(i=0;i<rings.length;i++){
			    if(rings[i].text == "90") html+= "<option value = '"+rings[i].value+"' selected>"+rings[i].text+"</option>";
				else html+= "<option value = '"+rings[i].value+"'>"+rings[i].text+"</option>";
			}
			html+= "</select>";
			html+= '</div>';
			html+= "<div class='advanced_col4 center'>";
			html+= "<button class='clear_form_group btn btn-danger' type='button'><i class='fa fa-trash' aria-hidden='true'></i></button>";
			html+= "</div>";
			html+= "<div class='clr'></div>";
			html+= "</div>";
			jQuery( html ).appendTo("#jform_n13_block_html");
			jQuery( ".clear_form_group" ).click(function(){
				jQuery( this ).closest(".form-group").remove();
			});
			var classname = jQuery(".n13_control");
			Array.from(classname).forEach(function(element) {
     		 	element.addEventListener('change',change_n13);
    		});
		});

		jQuery( "#add_n14" ).click(function(){
			var html = "<div class='form-group'>";
			html+= "<div class='advanced_col1'>";
			html+= "<input name='n14_count[]' class='form-control' value='' placeholder='шт.' type='tel'>";
			html+= "</div>";
			html+= "<div class='advanced_col5'>";
			html+= "<select class='form-control' name='n14_type[]' placeholder='Платформа'>";
			html+= bypasses ;
			html+= "</select>";
			html+= "</div>";
			html+= "<div class='advanced_col4 center'>";
			html+= "<button class='clear_form_group btn btn-danger' type='button'><i class='fa fa-trash' aria-hidden='true'></i></button>";
			html+= "</div>";
			html+= "<div class='clr'></div>";
			html+= "</div>";
			jQuery( html ).appendTo("#jform_n14_block_html");
			jQuery( ".clear_form_group" ).click(function(){
				jQuery( this ).closest(".form-group").remove();
			});
		});

		jQuery( "#add_n15" ).click(function(){
			var html = "<div class='form-group'>";
			html+= "<div class='advanced_col1'>";
			html+= "<input id = 'n15' name='n15_count[]' class='form-control' value='' placeholder='шт.' >";
			html+= "</div>";
			html+= "<div class='advanced_col2'>";
			html+= "<select class='form-control n15_control'  name='n15_type[]' placeholder='Тип'>";
			html+= "<option value='10'>Трехрядный</option>";
			html+= "</select>";
			html+= "</div>";
			html+= "<div class='advanced_col3'>";
			html+= "<select name='n15_size[]'  id='n15_1' class='form-control n15_module' placeholder='Длина'>"
			for(i=0;i<cornice.length;i++){
				html+= "<option value = '"+cornice[i].value+"'>"+cornice[i].text+"</option>";
			}
			html+= "</select>";
			html+= '</div>';
			html+= "<div class='advanced_col4 center'>";
			html+= "<button class='clear_form_group btn btn-danger' type='button'><i class='fa fa-trash' aria-hidden='true'></i></button>";
			html+= "</div>";
			html+= "<div class='clr'></div>";
			html+= "</div>";
			jQuery( html ).appendTo("#jform_n15_block_html");
			jQuery( ".clear_form_group" ).click(function(){
				jQuery( this ).closest(".form-group").remove();
			});
			var classname = jQuery(".n15_control");
			Array.from(classname).forEach(function(element) {
     		 	element.addEventListener('change',change_n15);
    		});
		});

		jQuery( "#add_ecola" ).click(function(){
			var html = "<div class='form-group'>";
			html+= "<div class='advanced_col1'>";
			html+= "<input name='ecola_count[]' class='form-control' value=''  placeholder='шт.' type='tel'>";
			html+= "</div>";
			html+= "<div class='advanced_col2'>";
			html+= "<select class='form-control' name='light_color[]' placeholder='Светильник'>";
			html+= ecola_items;
			html+= "</select>";
			html+= "</div>";
			html+= "<div class='advanced_col3 center'>";
			html+= "<select class='form-control' name='light_lamp_color[]' placeholder='Лампа'>";

			html+= ecola_lamps;

			html+= "</select>";
			html+= "</div>";
			html+= "<div class='advanced_col4 center'>";
			html+= "<button class='clear_form_group btn btn-danger' type='button'><i class='fa fa-trash' aria-hidden='true'></i></button>";
			html+= "</div>";
			html+= "<div class='clr'></div>";
			html+= "</div>";
			jQuery( html ).appendTo("#ecola_block_html");
			jQuery( ".clear_form_group" ).click(function(){
				jQuery( this ).closest(".form-group").remove();
			});
		});
        
        jQuery( "#add_level" ).click(function(){
			 var element = jQuery(this),
                block = element.siblings("#level_block_html");
            var html = "<div class='form-group'>";
            html+= "<div class='advanced_col1'>";
            html+= "<input name='n29_count[]' class='form-control' value=''  placeholder='м.' type='tel'>";
            html+= "</div>";
            html+= "<div class='advanced_col5'>";
            html+= "<select class='form-control' name='n29_type[]' placeholder=''>";
            html+= "<option value='12'>По прямой</option>";
			if(jQuery("#jform_n2" ).val() != 29) html+= "<option value='13'>По кривой</option>";
            html+= "<option value='15'>По прямой с нишей</option>";
            if(jQuery("#jform_n2" ).val() != 29) html+= "<option value='16'>По кривой с нишей</option>";
            html+= "</select>";
            html+= "</div>";
            html+= "<div class='advanced_col4 center'>";
            html+= "<button class='clear_form_group btn btn-danger' type='button'><i class='fa fa-trash' aria-hidden='true'></i></button>";
            html+= "</div>";
            html+= "<div class='clr'></div>";
            html+= "</div>";
            jQuery( html ).appendTo("#level_block_html");
            jQuery( ".clear_form_group" ).click(function(){
                jQuery( this ).closest(".form-group").remove();
            });
        });
		
		SELECT_CUSTOM_INIT();

		jQuery( "#add_n22" ).click(function(){
			var html = "<div class='form-group'>";
			html+= "<div class='advanced_col1'>";
			html+= "<input name='n22_count[]' class='form-control' value='' placeholder='шт.' type='tel'>";
			html+= "</div>";
			html+= "<div class='advanced_col2'>"; 
			html+= "<select id='n22' class='form-control n22_control' name='n22_type[]'>";
			html+= "<option value='5'>Круглая вентиляция</option>";
			html+= "<option value='6'>Квадратная вентиляция</option>";
			html+= "<option value='7'>Круглая электровытяжка</option>";
			html+= "<option value='8'>Квадратная электровытяжка</option>";
			html+= "</select>";
			html+= "</div>";
			html+= "<div class='advanced_col3'>";
			html+= "<select id='n22_1' class='form-control n22_module'  name='n22_diam[]'>" 
			for(i=0;i<rings.length;i++){
				html+= "<option value = '"+rings[i].value+"'>"+rings[i].text+"</option>";
			}
			html+= "</select>";
			html+= '</div>';
			html+= "<div class='advanced_col4 center'>";
			html+= "<button class='clear_form_group btn btn-danger' type='button'><i class='fa fa-trash' aria-hidden='true'></i></button>";
			html+= "</div>";
			html+= "<div class='clr'></div>";
			html+= "</div>";
			jQuery( html ).appendTo("#jform_n22_block_html");
			jQuery( ".clear_form_group" ).click(function(){
				jQuery( this ).closest(".form-group").remove();
			});
			var n22s = jQuery(".n22_control");
			Array.from(n22s).forEach(function(element) {
     		 	element.addEventListener('change',change_n22);
    		});
		});
		jQuery( "#add_n23" ).click(function(){
			var html = "<div class='form-group'>";
			html+= "<div class='advanced_col1'>";
			html+= "<input name='n23_count[]' class='form-control' value='' placeholder='шт.' type='tel'>";
			html+= "</div>";
			html+= "<div class='advanced_col5'>";
			html+= "<select class='form-control' name='n23_size[]' placeholder='Тип'>";
			html+= difusors;
			html+= "</select>";
			html+= "</div>";
			html+= "<div class='advanced_col4 center'>";
			html+= "<button class='clear_form_group btn btn-danger' type='button'><i class='fa fa-trash' aria-hidden='true'></i></button>";
			html+= "</div>";
			html+= "<div class='clr'></div>";
			html+= "</div>";
			jQuery( html ).appendTo("#jform_n23_block_html");
			jQuery( ".clear_form_group" ).click(function(){
				jQuery( this ).closest(".form-group").remove();
			});
		});
		
		jQuery("#form-calculation > input").keypress(function(e) {
			e.preventDefault();
			if(e.keyCode==13 && !$(this).is(":button")){
				jQuery(this).nextAll("input, textarea, select")[0].focus();
			}
		});
		
		jQuery( "#extra_header" ).click(function(){
			jQuery ( jQuery( this ).data("target") ).slideToggle();
		});
		
		n12_module();

		function n12_module() {
			jQuery(".n12_module").change(function(){
				var value = Number(jQuery (this).val().replace(/\D+/g,""));
				
				if(value < 55) {
					jQuery (this).val(55);
				} else if(value > 400) {
					jQuery (this).val(400);
				} else {
					var mod = Math.floor(value / 5);
					value = mod * 5;
					jQuery (this).val( value );
				}
			});
		}	
		function getSelectIndex(selects,obj){
			for (key in selects) {
				if(selects[key]==obj){
					
					index = key;
				}
				
			}
			console.log(index);
			return index;
		}

		<?php
			session_start();
			$texture = 0;
			$color = 0;
			$manufacturer = 0;
			$sess = $_SESSION['jform_n4'].' | '.$_SESSION['jform_n5'].' | '.$_SESSION['jform_n9'].' | '.$_SESSION['texture'].' | '.$_SESSION['color'].' | '.
					$_SESSION['manufacturer'].' | '.$_SESSION['calc_title'].' | '.$_SESSION['data'].' | '.$_SESSION['cut'].' | '.$_SESSION['offcut'].' | '.
					$_SESSION['width'].' | '.$_SESSION['original'].' | '.$_SESSION['cuts'];
			echo "console.log('$sess');";
			if (isset($_SESSION['jform_n4'],$_SESSION['jform_n5'],$_SESSION['jform_n9'],$_SESSION['data'],
				$_SESSION['cut'],$_SESSION['texture'],$_SESSION['color'],$_SESSION['manufacturer'],
				$_SESSION['width'],$_SESSION['offcut'],$_SESSION['calc_title']))
			{
				$n4 = $_SESSION['jform_n4'];
				$n5 = $_SESSION['jform_n5'];
				$n9 = $_SESSION['jform_n9'];
				$data = $_SESSION['data'];
				$cut = $_SESSION['cut'];
				$texture = $_SESSION['texture'];
				$manufacturer = $_SESSION['manufacturer'];
				$offcut = $_SESSION['offcut'];
				$color = $_SESSION['color'];
				$original = $_SESSION['original'];
				$calc_title = $_SESSION['calc_title'];
				$width = (string)$_SESSION['width']/100;
				if(empty(strpos($width,'.'))){
					$width.='.0';
				}
				$cuts = $_SESSION['cuts'];
				$model = Gm_ceilingHelpersGm_ceiling::getModel('colors');
				$imgurl = $model->getColorFile($color)->file;
				echo 'jQuery("#jform_n4").val("'.$n4.'");'
				.'jQuery("#jform_n5").val("'.$n5.'");'
				.'jQuery("#jform_n9").val("'.$n9.'");'
				.'jQuery("#jform_calculation_title").val("'.$calc_title.'");'
				.'jQuery("#jform_sketch_name").val("'.$data.'");'
				.'jQuery("#jform_cut_name").val("'.$cut.'");'
				.'jQuery("#jform_original_name").val("'.$original.'");'
				.'jQuery("#jform_cuts").val(\''.$cuts.'\');'
				.'jQuery("#sketch_image").prop("src", "/tmp/" + "'.$data.'" + ".svg");'
				.'jQuery("#sketch_image").removeAttr("hidden");'
				.'jQuery("#sketch_image_block").show();'
				.'jQuery("#data-wrapper").show();'
				.'jQuery("#jform_offcut_square").val("'.$offcut.'");';
				unset($_SESSION['jform_n4'],$_SESSION['jform_n5'],$_SESSION['jform_n9'],$_SESSION['data'],
				$_SESSION['cut'],$_SESSION['offcut'],$_SESSION['calc_title'],$_SESSION['original'],$_SESSION['color'],$_SESSION['cuts']);
			}
			echo 'function change_select_texture() {var lnk=document.getElementById(\'jform_n2\').options;'
				.'for (var i=0;i<lnk.length;i++) {'
				.'if (lnk[i].value=="'.$texture.'") {lnk[i].selected=true; jQuery( "#jform_n2" ).change();} }  } ';

			echo 'function change_select_manufacturer() {var lnk=document.getElementById(\'jform_proizv\').options;'
				.'for (var i=0;i<lnk.length;i++) {'
				.'if (lnk[i].value=="'.$manufacturer.'") {lnk[i].selected=true;} } '
				.'jQuery("#sketch_switch").prop("disabled",false);'
				.'jQuery("#jform_proizv").prop("disabled",false);'
				.'jQuery("#jform_color").val("'.$color.'");'
				.'jQuery("#color_img").attr("src","'.$imgurl.'");'
				.'jQuery("#calculate_button").prop("disabled",false);'
				.'jQuery("#flag_auto").val(0);'
				.'jQuery.getJSON( "index.php?option=com_gm_ceiling&task=getCanvasesList&jform_n2=" + jQuery("#jform_n2" ).val()+"&jform_proizv="+jQuery("#jform_proizv").val(), function( data ) {jQuery("#width").val(data);});change_select_n3();}';

			$str = 'function change_select_n3() {';
			if(!empty($width)){
				$str.='jQuery("#jform_n3_hidden").val("'.$width.'");';
			}
			$str.='jQuery("#jform_n3").html("<option value ='.$width.' selected>'.$width.'</option>")} ';
			echo $str;

			if(isset($_SESSION['need_calc'])){
				echo  'console.log("isset");';
				if($_SESSION['need_calc']==1){
					echo 'jQuery("#flag_auto").val(0);';
					echo 'timer = setInterval(calc_click,500);';
					unset($_SESSION['need_calc']);
				}
			}
		
		?>
	});

	function calc_click(){
         if(jQuery("#jform_n2").val()!=""&&jQuery("#jform_proizv").val()!=""){
			jQuery("#calculate_button").click();
            jQuery('html, body').animate({
                scrollTop: jQuery("#calculate_button").offset().top
            }, 1000);
            clearInterval(timer);
        }
	}

    function SELECT_CUSTOM_INIT() {
        var SELECT_CUSTOM = jQuery(".SELECT_CUSTOM");
        SELECT_CUSTOM.find("div").css({"line-height": SELECT_CUSTOM.height() + "px"});
        SELECT_CUSTOM.val(true);
    }

    function SELECT_CUSTOM_CLICK(e) {
        var element = jQuery(e);
        if (element.val()) element.find(".OPTIONS_CUSTOM").show();
        else element.find(".OPTIONS_CUSTOM").hide();
        element.val(!element.val())
    }

    function OPTION_CUSTOM_CLICK(e) {
        var element = jQuery(e);
        var root = element.closest(".SELECT_CUSTOM");
        root.find(".HIDDEN").val(element.attr("value"));
        root.find(".VALUE").html(element.html());
    }

    function SELECT_CUSTOM_BLUR(e) {
        var element = jQuery(e);
        element.find(".OPTIONS_CUSTOM").hide();
        element.val(true);
    }
    document.getElementById('new_discount').addEventListener('mousewheel', function(e){
        e.preventDefault();
    });
</script>