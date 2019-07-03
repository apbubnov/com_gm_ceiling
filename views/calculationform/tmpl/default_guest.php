<?php
/**
 * @version    CVS: 0.1.2
 * @package    Com_Gm_ceiling
 * @author     SpectralEye <Xander@spectraleye.ru>
 * @copyright  2016 SpectralEye
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
/* defined('_JEXEC') or die;

JHtml::_('behavior.keepalive');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');

// Load admin language file
$lang = JFactory::getLanguage();
$lang->load('com_gm_ceiling', JPATH_SITE);
$doc = JFactory::getDocument();
$doc->addScript(JUri::base() . '/media/com_gm_ceiling/js/form.js'); */


?>
<!-- 
<script type="text/javascript" src="/task/task/scriptForSketch/paper-full.js"></script>
<script type="text/javascript" src="/task/task/scriptForSketch/paper-core.js"></script>
<script type="text/paperscript" src="/task/task/scriptForSketch/SketchDraw.js" canvas="myCanvas"></script>

<div class="calculation-edit front-end-edit">
	<div>
		<a href="<?php echo JRoute::_("index.php?option=com_users&view=login", false); ?>" class="btn btn-secondary" style="float: right;"><i class="fa fa-lock" aria-hidden="true"></i></a><h2>Расчет стоимости потолка</h2>
	</div>
	<form id="form-calculation"
		  action="<?php echo JRoute::_('index.php?option=com_gm_ceiling&task=calculation.save'); ?>"
		  method="post" class="form-validate form-horizontal" enctype="multipart/form-data">
		<!-- Верхняя запись на замер -->
		<h3 class="center">Записаться на замер бесплатно</h3>
				<div class="container">
					<div class="row sm-margin-bottom">
						<div class="col-sm-4">
							<!-- Регистрация клиента -->
							<div class="form-group">
								<label id="jform_client_name-lbl-top" for="jform_client_name-top" class="required">Ваше ФИО<span class="star">&nbsp;*</span></label>
								<input name="jform[client_name-top]" id="jform_client_name-top" data-next="#jform_client_contacts-top" value="" class="form-control required" placeholder="ФИО клиента" required="required" aria-required="true" type="text">
							</div>
						</div>
						<div class="col-sm-4">
							<div class="form-group">
								<label id="jform_client_contacts-lbl-top" for="jform_client_contacts-top" class="required">Ваш телефон<span class="star">&nbsp;*</span></label>
								<input name="jform[client_contacts-top]" id="jform_client_contacts-top" data-next="#jform_project_info-top" value="" class="form-control required" placeholder="Телефоны клиента" required="required" aria-required="true" type="text">
							</div>
						</div>
						<div class="col-sm-4">
							<!-- Создание проекта -->
							<div class="form-group">			
								<label id="jform_project_info-lbl-top" for="jform_project_info-top">Ваш адрес</label>
								<input name="jform[project_info-top]" id="jform_project_info-top" data-next="#jform_project_calculation_date-top" value="" class="form-control" placeholder="Адрес клиента" type="text">
							</div>
						</div>
					</div>
					<div class="row sm-margin-bottom">
						<div class="col-sm-4">
							<!-- Для начальника монтажной службы -->			
							<div class="form-group">
								<label id="jform_project_calculation_date-lbl-top" for="jform_project_calculation_date-top">Удобная дата замера</label>
								<?php //<input name="jform[project_calculation_date-top]" id="jform_project_calculation_date-top" value="" class="form-control" placeholder="Дата замера" type="text"> ?>
								<?php $attribs = array(); ?>
								<?php $attribs['class'] = "form-control calendar-input"; ?>
								<?php $attribs['placeholder'] = "Удобная дата замера"; ?>
								<?php echo JHtml::calendar( '', 'jform[project_calculation_date-top]', 'jform_project_calculation_date-top', '%d.%m.%Y', $attribs ); ?>
							</div>
						</div>
						<div class="col-sm-4">
							<div class="form-group">
								<label id="jform_project_calculation_daypart-lbl-top" for="jform_project_calculation_daypart-top">Удобное время замера</label>
								<select id="jform_project_calculation_daypart-top" name="jform[project_calculation_daypart-top]" class="form-control inputbox" disabled="true">
									<option value="0" selected="">- Выберите время замера -</option>
									<option value="1">9:00-10:00</option>
									<option value="2">10:00-11:00</option>
									<option value="3">11:00-12:00</option>
									<option value="4">12:00-13:00</option>
									<option value="5">13:00-14:00</option>
									<option value="6">14:00-15:00</option>
									<option value="7">15:00-16:00</option>
									<option value="8">16:00-17:00</option>
									<option value="9">17:00-18:00</option>
									<option value="10">18:00-19:00</option>
									<option value="11">19:00-20:00</option>
									<option value="12">20:00-21:00</option>
								</select>
							</div>
						</div>
						<div class="col-sm-4">
							<div class="form-group">
								<label id="jform_project_note-lbl-top" for="jform_project_note-top">Примечание</label>
								<input name="jform[project_note-top]" id="jform_project_note-top" class="form-control" value="" placeholder="Примечание" type="text">
							</div>
						</div>
					</div>
				</div>
				<div class="container">
					<div class="row sm-margin-bottom">
						<div class="col-md-4 pull-center">
							<button id="reserve_button-top" type="button" class="validate btn btn-primary btn-big">Записаться на замер</button>
						</div>
					</div>
				</div>	
				<div class="container">
					<div class="row sm-margin-bottom">
					   <span class="star">&nbsp;*</span> Поле для обязательного заполнения <div class="required_info"></div>
					</div>
				</div>
	<!-- конец верхней записи на замер -->
		<input id="jform_id" type="hidden" name="jform[id]" value="<?php echo $this->item->id; ?>" />
		<input type="hidden" name="jform[public]" value="1" />
		<input type="hidden" name="jform[ordering]" value="<?php echo $this->item->ordering; ?>" />
		<input type="hidden" name="jform[state]" value="<?php echo $this->item->state; ?>" />
		<input type="hidden" name="jform[checked_out]" value="<?php echo $this->item->checked_out; ?>" />
		<input type="hidden" name="jform[checked_out_time]" value="<?php echo $this->item->checked_out_time; ?>" />
		<input type="hidden" name="jform[dealer_id]" value="2" />
		<input type="hidden" name="jform[type]" value="guest" />
		<input id="jform_project_id" type="hidden" name="jform[project_id]" value="<?php echo $this->item->project_id; ?>" />
		<input id="jform_sketch_name" type="hidden" name="sketch_name" value="" />
		<input id="jform_components_sum" type="hidden" name="jform[components_sum]" value="" />
		<input id="jform_mounting_sum" type="hidden" name="jform[mounting_sum]" value="" />
		<input id="jform_gm_mounting_sum" type="hidden" name="jform[gm_mounting_sum]" value="" />
		<input id="jform_dealer_mounting_sum" type="hidden" name="jform[dealer_mounting_sum]" value="" />
		<input name="jform[created_by]" value="0" type="hidden">
		<input name="jform[modified_by]" value="0" type="hidden">
		<input name="jform[transport]" value="1" type="hidden">
		<input id="jform_n1" class="n1" name="jform[n1]" value="1" type="hidden">
		<input id="jform_n12_num" name="n12_num" value="1" type="hidden">
							
		<h3>Характеристики полотна</h3>		
		<div class="form-group">
			<label id="jform_n2-lbl" for="jform_n2">
				Выберите фактуру полотна
			</label>
			<select id="jform_n2" name="jform[n2]" class="form-control inputbox"><option value="0" selected="">- Выберите фактуру -</option></select>
		</div>

		<div class="form-group">
			<label id="jform_n3-lbl" for="jform_n3">
				Выберите ширину полотна
			</label>
			<select id="jform_n3" name="jform[n3]" class="form-control inputbox" disabled=""><option value="0" selected="">- Выберите полотно и ширину -</option></select>
		</div>

		<div id="sketch_image_block" style="display: none;">
			<h3 class="section_header">
				Показать чертеж <i class="fa fa-sort-desc" aria-hidden="true"></i>
			</h3>
			<div class="section_content">
				<img id="sketch_image" src="/" alt="">
			</div>
		</div>

		<h3>Размеры помещения</h3>
		<div class="container">
		  <div class="row">
			<div class="col-sm-4">
				<div class="form-group">
					<label id="jform_n4-lbl" for="jform_n4">
						Введите площадь комнаты
					</label>
					<input name="jform[n4]" class="form-control" id="jform_n4" value="" placeholder="Введите площадь комнаты" type="tel">
				</div>
				
				<div class="form-group">
					<label id="jform_n5-lbl" for="jform_n5">
						Введите периметр комнаты
					</label>
					<input name="jform[n5]" class="form-control" id="jform_n5" value="" placeholder="Введите периметр комнаты" type="tel">
				</div>
				
				<div class="form-group">
					<label id="jform_n9-lbl" for="jform_n9" class="">
						Введите количество углов в комнате
					</label>
					<input name="jform[n9]" id="jform_n9" value="" class="form-control" placeholder="Введите количество углов в комнате" type="tel">
				</div>
			</div>
			<div class="col-sm-1">
			  или
			</div>
			<div class="col-sm-4">
			  <a id="sketch_switch" class="btn btn-success">Нарисуйте план комнаты</a>
			</div>
		  </div>
		</div>
		
		<h3>Вставка</h3>
		<div class="form-group">
			<label id="jform_n6-lbl" for="jform_n6" class="">
				Нужна ли декоративная вставка
			</label>
			<input name="jform[n6]" id="jform_n6" class="form-control" value="1" type="checkbox">
		</div>
		
		<h3>Шторный карниз</h3>
		<div class="form-group">
			<label id="jform_n15-lbl" for="jform_n15" class="">
				Введите длину стороны со шторным карнизом
			</label>
			<input name="jform[n15]" id="jform_n15" class="form-control" value="" placeholder="Введите длину стороны со шторным карнизом" type="tel">
		</div>
		
		<h3>Установка люстр</h3>
		<div class="form-group">
			<label id="jform_n15-lbl" for="jform_n15" class="">Введите кол-во люстр</label>
			<input id="jform_n12_count1" name="n12_count1" class="form-control" type="tel">
		</div>
				
		<h3>Установка светильников</h3>
		<div class="form-group">
			<label id="jform_n13_easycount-lbl" for="jform_n13_easycount" class="">
				Введите кол-во светильников
			</label>
			<input name="jform[n13_easycount]" id="jform_n13_easycount" class="form-control" value="" placeholder="Введите кол-во светильников" type="tel">
		</div>
		
		<h3>Обвод труб</h3>
		<div class="form-group">
			<label id="jform_n14_easycount-lbl" for="jform_n14_easycount" class="">
				Введите кол-во труб
			</label>
			<input name="jform[n14_easycount]" id="jform_n14_easycount" class="form-control" value="" placeholder="Введите кол-во труб" type="tel">
		</div>
		
		<h3 class="section_header">Прочее <i class="fa fa-sort-desc" aria-hidden="true"></i></h3>
		<div class="section_content" style="display: none;">
			<div class="form-group">
				<label id="jform_n25-lbl" for="jform_n25" class="">Эcola GX53+лампы</label>
				<input name="jform[n25]" id="jform_n25" value="" class="form-control" placeholder="Эcola GX53+лампы" type="tel">
			</div>
			
			<div class="form-group">
				<label id="jform_n7-lbl" for="jform_n7" class="">Метраж стен с плиткой</label>
				<input name="jform[n7]" id="jform_n7" value="" class="form-control" placeholder="Метраж стен с плиткой" type="tel">
			</div>
			
			<div class="form-group">
				<label id="jform_n8-lbl" for="jform_n8" class="">Метраж стен с керамогранитом</label>
				<input name="jform[n8]" id="jform_n8" value="" class="form-control" placeholder="Метраж стен с керамогранитом" type="tel">
			</div>
			
			<div class="form-group">
				<label id="jform_n19-lbl" for="jform_n19" class="">Провод</label>
				<input name="jform[n19]" id="jform_n19" value="" class="form-control" placeholder="Провод" type="tel">
			</div>
			
			<div class="form-group">				
				<label id="jform_n17-lbl" for="jform_n17" class="">Закладная брусом</label>
				<input name="jform[n17]" id="jform_n17" value="" class="form-control" placeholder="Закладная брусом" type="tel">
			</div>
			
			<div class="form-group">
				<label id="jform_n18-lbl" for="jform_n18" class="">Укрепление стены</label>
				<input name="jform[n18]" id="jform_n18" value="" class="form-control" placeholder="Укрепление стены" type="tel">
			</div>
			
			<div class="form-group">
				<label id="jform_n20-lbl" for="jform_n20" class="">Разделитель</label>
				<input name="jform[n20]" id="jform_n20" value="" class="form-control" placeholder="Разделитель" type="tel">
			</div>
			
			<div class="form-group">
				<label id="jform_n21-lbl" for="jform_n21" class="">Пожарная сигнализация</label>
				<input name="jform[n21]" id="jform_n21" value="" class="form-control" placeholder="Пожарная сигнализация" type="tel">
			</div>
			
			<div class="form-group">
				<label id="jform_n24-lbl" for="jform_n24" class="">Сложность доступа</label>
				<input name="jform[n24]" id="jform_n24" value="" class="form-control" placeholder="Сложность доступа" type="tel">
			</div>
			
			<div class="form-group">
				<label id="jform_n10-lbl" for="jform_n10" class="">Криволинейный участок</label>
				<input name="jform[n10]" id="jform_n10" value="" class="form-control" placeholder="Криволинейный участок" type="tel">
			</div>
			
			<div class="form-group">
				<label id="jform_n11-lbl" for="jform_n11" class="">Внутренний вырез</label>
				<input name="jform[n11]" id="jform_n11" value="" class="form-control" placeholder="Внутренний вырез" type="tel">
			</div>
			
			<h4>Установка вентиляции</h4>
			<div class="form-group">
				<label id="jform_n22-lbl" for="jform_n22" class="">Установка вентиляции</label>
				<input name="jform[n22]" id="jform_n22" value="1" class="form-control" type="checkbox">
			</div>
			
			<div class="form-group">
				<label id="jform_n22_advanced-lbl" for="jform_n22_advanced" class="">Расширенный режим</label>
				<input name="jform[n22_advanced]" id="jform_n22_advanced" class="form-control" value="1" type="checkbox">
			</div>
			<div id="jform_n22_advanced_block" style="display: none;">
				<div class="form-group">
					<label id="jform_n22_type-lbl" for="jform_n22_type" class="">Тип</label>
					<select id="jform_n22_type" class="form-control" name="jform[n22_type]">
						<option value="1">кольцо 20-90 мм</option>
						<option value="2">кольцо 100-112 мм</option>
						<option value="3">кольцо 115-175 мм</option>
						<option value="4">кольцо 195-225 мм</option>
						<option value="5">кольцо 250-300 мм</option>
						<option value="6">кольцо 325-375 мм</option>
						<option value="7">кольцо 400-425 мм</option>
						<option value="8">кольцо 455-485 мм</option>
						<option value="9">кольцо 520-550 мм</option>
						<option value="10">кольцо 580-610 мм</option>
					</select>
				</div>
				
				<div class="form-group">
					<label id="jform_n22_ring-lbl" for="jform_n22_ring" class="">Кольцо</label>
					<select id="jform_n22_ring" class="form-control" name="jform[n22_ring]">
						<option value="1">Платформа 165-225 мм</option>
						<option value="2">Платформа 125-155 мм</option>
						<option value="3">Платформа 55-105 мм</option>
						<option value="4">Платформа 55-125 мм</option>
					</select>
				</div>
			</div>
				
			<h4>Установка электровытяжки</h4>
			<div class="form-group">
				<label id="jform_n23-lbl" for="jform_n23" class="">Установка электровытяжки</label>
				<input name="jform[n23]" id="jform_n23" class="form-control" value="1" type="checkbox">
			</div>
			
			<div class="form-group">				
				<label id="jform_n23_advanced-lbl" for="jform_n23_advanced" class="">Расширенный режим</label>
				<input name="jform[n23_advanced]" id="jform_n23_advanced" class="form-control" value="1" type="checkbox">
			</div>
			
			<div id="jform_n23_advanced_block" style="display: none;">
				<div class="form-group">
					<label id="jform_n23_type-lbl" for="jform_n23_type" class="">Тип</label>
					<select id="jform_n23_type" name="jform[n23_type]" class="form-control">
						<option value="1">кольцо 20-90 мм</option>
						<option value="2">кольцо 100-112 мм</option>
						<option value="3">кольцо 115-175 мм</option>
						<option value="4">кольцо 195-225 мм</option>
						<option value="5">кольцо 250-300 мм</option>
						<option value="6">кольцо 325-375 мм</option>
						<option value="7">кольцо 400-425 мм</option>
						<option value="8">кольцо 455-485 мм</option>
						<option value="9">кольцо 520-550 мм</option>
						<option value="10">кольцо 580-610 мм</option>
					</select>
				</div>
				
				<div class="form-group">
					<label id="jform_n23_ring-lbl" for="jform_n23_ring" class="">Кольцо</label>
					<select id="jform_n23_ring" name="jform[n23_ring]" class="form-control">
						<option value="1">Платформа 165-225 мм</option>
						<option value="2">Платформа 125-155 мм</option>
						<option value="3">Платформа 55-105 мм</option>
						<option value="4">Платформа 55-125 мм</option>
					</select>
				</div>
			</div>
		</div>
		
		<a id="calculate_button" class="btn btn-primary">
			<span class="loading" style="display: none;">
				Считаю...<i class="fas fa-sync fa-spin fa-3x fa-fw"></i>
			</span>
			<span class="static">Рассчитать</span>
		</a>
		
		<div id="result_block">
			<table class="client_public_price">
				<tbody><tr>
					<th class="total_price">
						<span id="total_price">0.00</span> руб.
					</th>
					<th class="discount">
						-15%
					</th>
					<th class="final_price">
						<span id="final_price">0.00</span> руб.					
					</th>
				</tr>
				<tr>
					<td>
						Стоимость Вашего потолка
					</td>
					<td class="discount">
						Ваша скидка
					</td>
					<td class="final_price">
						Цена со скидкой
					</td>
				</tr>
				<tr>
					<td></td>
					<td></td>
					<td class="final_price">
						<input value="" id="send_email" name="send_email" class="form-control" placeholder="Введите ваш Email" style="margin-bottom: 5px;" type="email">
						<br>
						<button class="btn btn-primary" type="button" id="send_to_email">Получить подробную смету</button>
						<div id="send_email_success" style="display: none;">
							Смета отправлена
						</div>
					</td>
				</tr>
			</tbody></table>
		</div>
		
		<div class="show_after_calculate">
			<h2>Записаться на замер</h2>
			
			<!-- Регистрация клиента -->
			<div class="form-group">
				<label id="jform_client_name-lbl" for="jform_client_name" class="required">Ваше ФИО<span class="star">&nbsp;*</span></label>
				<input name="jform[client_name]" id="jform_client_name" value="" class="form-control required" placeholder="ФИО клиента" required="required" aria-required="true" type="text">
			</div>
			<div class="form-group">
				<label id="jform_client_contacts-lbl" for="jform_client_contacts" class="required">Ваш телефон<span class="star">&nbsp;*</span></label>
				<input name="jform[client_contacts]" id="jform_client_contacts" value="" class="form-control required" placeholder="Телефоны клиента" required="required" aria-required="true" type="text">
			</div>
			
			<!-- Создание проекта -->
			<div class="form-group">			
				<label id="jform_project_info-lbl" for="jform_project_info">Ваш адрес</label>
				<input name="jform[project_info]" id="jform_project_info" value="" class="form-control" placeholder="Адрес клиента" type="text">
			</div>
			
			<!-- Для начальника монтажной службы -->			
			<div class="form-group">
				<label id="jform_project_calculation_date-lbl" for="jform_project_calculation_date">Удобная дата замера</label>
				<input name="jform[project_calculation_date]" id="jform_project_calculation_date" value="" class="form-control" placeholder="Дата замера" type="text">
			</div>
			
			<div class="form-group">
				<label id="jform_project_calculation_daypart-lbl" for="jform_project_calculation_daypart">Удобное время замера</label>
				<select id="jform_project_calculation_daypart" name="jform[project_calculation_daypart]" class="form-control inputbox">
					<option value="0" selected="">- Выберите время замера -</option>
					<option value="1">9:00-10:00</option>
					<option value="2">10:00-11:00</option>
					<option value="3">11:00-12:00</option>
					<option value="4">12:00-13:00</option>
					<option value="5">13:00-14:00</option>
					<option value="6">14:00-15:00</option>
					<option value="7">15:00-16:00</option>
					<option value="8">16:00-17:00</option>
					<option value="9">17:00-18:00</option>
					<option value="10">18:00-19:00</option>
					<option value="11">19:00-20:00</option>
					<option value="12">20:00-21:00</option>
				</select>
			</div>
			
			<div class="form-group">
				<label id="jform_project_note-lbl" for="jform_project_note">Примечание</label>
				<input name="jform[project_note]" id="jform_project_note" class="form-control" value="" placeholder="Примечание" type="text">
			</div>
			
			<div class="form-group">
				<button type="submit" class="validate btn btn-primary">Записаться на замер</button>
			</div>
		</div>
		<input type="hidden" id="activate" name="activate" value="0"/>
		<input type="hidden" name="option" value="com_gm_ceiling"/>
		<input type="hidden" name="task" value="calculationform.save"/>
		<?php echo JHtml::_('form.token'); ?>
	</form>
</div> -->

<script>
	/* function get_form(){
		jQuery( "input[type=checkbox]").each(function(){
			if( jQuery( this ).is(':checked') ) {
				jQuery( this ).val( 1 );
			} else {
				jQuery( this ).val( 0 );
			}
		});
		
		var project_id = 0;
		if( jQuery( "#jform_project_id" ).val() > 0 ) {
			project_id = jQuery( "#jform_project_id" ).val();
		};
		
		var id = 0;
		if( jQuery( "#jform_id" ).val() > 0 ) {
			id = jQuery( "#jform_id" ).val();
		};
		
		var form_data = "";
		form_data += "id="+id;
		form_data += "&project_id="+project_id;
		form_data += "&n1="+jQuery( "#jform_n1" ).val();
		form_data += "&n2="+jQuery( "#jform_n2" ).val();
		form_data += "&n3="+jQuery( "#jform_n3" ).val();
		form_data += "&n4="+jQuery( "#jform_n4" ).val();
		form_data += "&n5="+jQuery( "#jform_n5" ).val();
		form_data += "&n6="+jQuery( "#jform_n6" ).val();
		form_data += "&n7="+jQuery( "#jform_n7" ).val();
		form_data += "&n8="+jQuery( "#jform_n8" ).val();
		form_data += "&n9="+jQuery( "#jform_n9" ).val();
		form_data += "&n10="+jQuery( "#jform_n10" ).val();
		form_data += "&n11="+jQuery( "#jform_n11" ).val();
		
		var n12_num = jQuery( "#jform_n12_num" ).val();
		form_data += "&n12_num="+n12_num;
		for( i = 1; i <= n12_num; i++) {
			form_data += "&n12_type"+i+"="+jQuery( "#jform_n12_type"+i ).val();
			form_data += "&n12_count"+i+"="+jQuery( "#jform_n12_count"+i ).val();
		}
		
		var n13_level = jQuery( "#jform_n13_advanced" ).val(),
			n13_num = jQuery( "#jform_n13_num" ).val(),
			n13_easycount = jQuery( "#jform_n13_easycount" ).val();
		
		form_data += "&n13_level="+n13_level;
		form_data += "&n13_num="+n13_num;
		form_data += "&n13_easycount="+n13_easycount;
		for( i = 1; i <= n13_num; i++) {
			form_data += "&n13_ring"+i+"="+jQuery( "#jform_n13_ring"+i ).val();
			form_data += "&n13_platform"+i+"="+jQuery( "#jform_n13_platform"+i ).val();
			form_data += "&n13_type"+i+"="+jQuery( "#jform_n13_type"+i ).val();
			form_data += "&n13_count"+i+"="+jQuery( "#jform_n13_count"+i ).val();
		}
		
		var n14_level = jQuery( "#jform_n14_advanced" ).val(),
			n14_num = jQuery( "#jform_n14_num" ).val(),
			n14_easycount = jQuery( "#jform_n14_easycount" ).val();
		
		form_data += "&n14_level="+n14_level;
		form_data += "&n14_num="+n14_num;
		form_data += "&n14_easycount="+n14_easycount;
		for( i = 1; i <= n14_num; i++) {
			form_data += "&n14_type"+i+"="+jQuery( "#jform_n14_type"+i ).val();
			form_data += "&n14_count"+i+"="+jQuery( "#jform_n14_count"+i ).val();
		}

		form_data += "&n15="+jQuery( "#jform_n15" ).val();
		form_data += "&n16="+jQuery( "#jform_n16" ).val();
		form_data += "&n17="+jQuery( "#jform_n17" ).val();
		form_data += "&n18="+jQuery( "#jform_n18" ).val();
		form_data += "&n19="+jQuery( "#jform_n19" ).val();
		form_data += "&n20="+jQuery( "#jform_n20" ).val();
		form_data += "&n21="+jQuery( "#jform_n21" ).val();
		form_data += "&n22="+jQuery( "#jform_n22" ).val();
		form_data += "&n22_level="+jQuery( "#jform_n22_advanced" ).val();
		form_data += "&n22_type="+jQuery( "#jform_n22_type" ).val();
		form_data += "&n22_ring="+jQuery( "#jform_n22_ring" ).val();
		form_data += "&n23="+jQuery( "#jform_n23" ).val();
		form_data += "&n23_level="+jQuery( "#jform_n23_advanced" ).val();
		form_data += "&n23_type="+jQuery( "#jform_n23_type" ).val();
		form_data += "&n23_ring="+jQuery( "#jform_n23_ring" ).val();
		form_data += "&n24="+jQuery( "#jform_n24" ).val();
		form_data += "&n25="+jQuery( "#jform_n25" ).val();
		form_data += "&title="+encodeURIComponent( jQuery( "#jform_calculation_title" ).val() );
		
		return form_data;
	}
	jQuery( document ).ready(function(){
			jQuery("#jform_client_contacts-top").mask("+7 (999) 999-99-99");
	jQuery("#jform_client_contacts").mask("+7 (999) 999-99-99");
		var n2_start_option = "<option value='0' selected>- Выберите фактуру -</option>",
			n3_start_option = "<option value='0' selected>- Выберите полотно и ширину -</option>";
			
		jQuery("#jform_project_calculation_date").mask("99.99.9999");
		jQuery("#jform_client_contacts").mask("+7 (999) 999-99-99");
			
		var project_id = getUrlParameter("project_id");
		if(project_id > 0) {
			jQuery("#jform_project_id").val(project_id);
			jQuery('#jform_project_id option[value="' + project_id + '"]').attr('selected',true);
		}
		
		//Автозамена запятой на точку
		jQuery( "input[type=tel]" ).on("keyup",function(){
			jQuery( this ).val( jQuery( this ).val().replace(',', '.') );
		});
				
		jQuery( "#jform_n13_advanced" ).change(function(){
			if( jQuery( this ).is(':checked') ) {
				jQuery( "#jform_n13_advanced_block" ).show();		
				jQuery( "#jform_n13_easy_block" ).hide();
				if( parseInt(jQuery("#jform_n13_num").val()) == 0) {
					add_selects("n13");
				}
			} else {
				jQuery( "#jform_n13_easy_block" ).show();
				jQuery( "#jform_n13_advanced_block" ).hide();
			}
		});
		
		jQuery( "#jform_n14_advanced" ).change(function(){
			if( jQuery( this ).is(':checked') ) {
				jQuery( "#jform_n14_advanced_block" ).show();
				jQuery( "#jform_n14_easy_block" ).hide();
				if( parseInt(jQuery("#jform_n14_num").val()) == 0) {
					add_selects("n14");
				}
			} else {
				jQuery( "#jform_n14_easy_block" ).show();
				jQuery( "#jform_n14_advanced_block" ).hide();
			}
		});
		
		jQuery( "#jform_n22_advanced" ).change(function(){
			if( jQuery( this ).is(':checked') ) {
				jQuery( "#jform_n22_advanced_block" ).show();
				jQuery( "#jform_n22" ).prop("checked", true);
			} else {
				jQuery( "#jform_n22_advanced_block" ).hide();
			}
		});
		
		jQuery( "#jform_n22" ).change(function(){
			if( !jQuery( this ).is(':checked') ) {
				jQuery( "#jform_n22_advanced_block" ).hide();
				jQuery( "#jform_n22_advanced" ).prop("checked", false);
			}
		});
		
		jQuery( "#jform_n23_advanced" ).change(function(){
			if( jQuery( this ).is(':checked') ) {
				jQuery( "#jform_n23_advanced_block" ).show();
				jQuery( "#jform_n23" ).prop("checked", true);
			} else {
				jQuery( "#jform_n23_advanced_block" ).hide();
			}
		});
		
		jQuery( "#jform_n23" ).change(function(){
			if( !jQuery( this ).is(':checked') ) {
				jQuery( "#jform_n23_advanced_block" ).hide();
				jQuery( "#jform_n23_advanced" ).prop("checked", false);
			}
		});
		
		jQuery( "#jform_n2" ).html( n2_start_option );
		
		jQuery( "#jform_n3" ).html( n3_start_option );
		
		jQuery.getJSON( "index.php?option=com_gm_ceiling&task=getTypesList", function( data ) {
			var items = [];
			jQuery.each( data, function( key, val ) {
				items.push( "<option value='" + val[0] + "'>" + val[1] + "</option>" );
			});
		});
		
		jQuery.getJSON( "index.php?option=com_gm_ceiling&task=getTexturesList&jform_n1=1", function( data ) {
			var items = [];
			jQuery.each( data, function( key, val ) {
				items.push( "<option value='" + val[0] + "'>" + val[1] + "</option>" );
			});

			jQuery( "#jform_n2" ).html( n2_start_option + items.join( "" ) );

			jQuery( "#jform_n3" ).html( n3_start_option );
		});
		
		jQuery( "#jform_n2" ).change(function(){
			if( jQuery ( this ).val() > 0 ) {
				jQuery ( "#jform_n3" ).prop("disabled", true);
				jQuery.getJSON( "index.php?option=com_gm_ceiling&task=getCanvasesList&jform_n2=" + jQuery( this ).val(), function( data ) {
					var items = [];
					jQuery.each( data, function( key, val ) {
						items.push( "<option value='" + val[0] + "'>" + val[1] + "</option>" );
					});

					jQuery( "#jform_n3" ).html( n3_start_option + items.join( "" ) );
					jQuery ( "#jform_n3" ).prop("disabled", false);
				});
			} else {
				jQuery ( "#jform_n3" ).prop("disabled", true);
				jQuery( "#jform_n3" ).html( n2_start_option );
			}
		});
		
		//Запрос к серверу на расчет потолка
		jQuery( "#send_to_email" ).click(function(){
			var form_data = get_form(),
				send_email = jQuery("#send_email").val();
			jQuery.getJSON( "index.php?option=com_gm_ceiling&task=calculate&send_client_cost=1&send_email="+send_email+"&"+form_data, function( data ) {
			
				jQuery('#send_email_success').slideDown();
			});
		});
		jQuery( "#calculate_button" ).click(function(){
			jQuery('#send_email_success').slideUp();
			var calculate_button = jQuery( this );
			if( !calculate_button.hasClass("loading") ) {
				calculate_button.addClass("loading");
				calculate_button.find("span.static").hide();
				calculate_button.find("span.loading").show();
				var form_data = get_form();
				jQuery.getJSON( "index.php?option=com_gm_ceiling&task=calculate&"+ form_data, function( data ) {
					console.log("index.php?option=com_gm_ceiling&task=calculate&"+ form_data);
					var html = "",
						gm_total = parseFloat(data.components_sum) + parseFloat(data.gm_mounting_sum),
						dealer_total = parseFloat(data.components_sum) + parseFloat(data.dealer_mounting_sum),
						dealer_final = parseFloat(dealer_total) * 0.8;
					html += "Стоимость компонентов: " + data.components_sum.toFixed(2) + " руб.<br>";
					html += "Монтаж службой ГМ: " + data.gm_mounting_sum.toFixed(2) + " руб. (Итого: " + gm_total.toFixed(2) + " руб.) <a class='print_gm_mounting_button btn btn-success btn-mini'>Смета для клиента</a> <a class='print_for_gm_button btn btn-warning btn-mini'>Наряд для монтажников</a><br>";
					html += "Монтаж службой дилера: " + data.dealer_mounting_sum.toFixed(2) + " руб. (Итого: " + dealer_total.toFixed(2) + "руб.) <a class='print_dealer_mounting_button btn btn-success btn-mini'>Смета для клиента</a> <a class='print_for_dealer_button btn btn-warning btn-mini'>Наряд для монтажников</a><br>";
					
					jQuery("#result_block").show();
					jQuery("#jform_id").val(data.id);
					jQuery("#jform_components_sum").val(data.components_sum.toFixed(2));
					jQuery("#jform_gm_mounting_sum").val( data.gm_mounting_sum.toFixed(2));
					jQuery("#jform_dealer_mounting_sum").val( data.dealer_mounting_sum.toFixed(2));
					
					jQuery("#total_price").text( dealer_total.toFixed(2) );
					jQuery("#final_price").text( dealer_final.toFixed(2) );
					
					jQuery( ".print_gm_mounting_button" ).click(function(){
						window.open( "/costsheets/" + data.gm_client_print);
					});
					
					jQuery( ".print_for_gm_button" ).click(function(){
						window.open( "/costsheets/" + data.gm_team_print);
					});	
					
					jQuery( ".print_dealer_mounting_button" ).click(function(){
						window.open( "/costsheets/" + data.dealer_client_print );
					});
					
					jQuery( ".print_for_dealer_button" ).click(function(){
						window.open( "/costsheets/" + data.dealer_team_print );
					});
					jQuery( ".show_after_calculate" ).show();
					
					calculate_button.removeClass("loading");
					calculate_button.find("span.loading").hide();
					calculate_button.find("span.static").show();
					
					jQuery('html, body').animate({
						scrollTop: 10000
					}, 2000);
				});
			}
		});
		
		jQuery( "#print_components_button" ).click(function(){
			var form_data = get_form();
			window.open( "index.php?option=com_gm_ceiling&task=calculate&print=1&" + form_data);
		});
		
		jQuery( "#activate_calculation" ).click(function(){
			if(!jQuery("#additional_info").hasClass("visible")) {
				jQuery("#additional_info").addClass("visible");
				jQuery("#additional_info").show();
				jQuery("#activate").val(1);
			} else {
				jQuery("#form-calculation").submit();
			}
		});	
				
		jQuery( "#sketch_switch" ).click(function(){
			jQuery("body").addClass("no-scroll");
			jQuery( ".main_wrapper" ).hide();
			jQuery( "#sketch_editor" ).show();
		});
		jQuery( "#close_sketch" ).click(function(){
			jQuery("body").removeClass("no-scroll");
			jQuery( "#sketch_editor" ).hide();
			jQuery( ".main_wrapper" ).show();

		});
				
		resize_canvas();

		jQuery( window ).resize(function(){
			resize_canvas();
		});
	});
	function resize_canvas() {
		jQuery( "#myCanvas" ).prop("width", jQuery(window).width() );
		jQuery( "#myCanvas" ).prop("height", jQuery(window).height() );
	} */
</script>