<?php
    defined('_JEXEC') or die;
    JHtml::_('behavior.keepalive');
    //JHtml::_('behavior.tooltip');
    JHtml::_('behavior.formvalidation');
    $jinput = JFactory::getApplication()->input;
    $lang = JFactory::getLanguage();
    $lang->load('com_gm_ceiling', JPATH_SITE);
    $doc = JFactory::getDocument();
    $doc->addScript(JUri::base() . '/media/com_gm_ceiling/js/form.js');

    $user = JFactory::getUser();
    $user_groups = $user->groups;
    if(in_array('16',$user_group)){
		$triangulator_pro = 1;
	}
	else{
		$triangulator_pro = 0;
	}
    /*____________________Models_______________________  */
    $canvases_model = Gm_ceilingHelpersGm_ceiling::getModel("canvases");
    $calculation_model = Gm_ceilingHelpersGm_ceiling::getModel("calculation");
    /*____________________end_______________________  */
    $canvases_data = json_encode($canvases_model->getFilteredItemsCanvas("count>0"));
    $calculation_id = $jinput->get('calc_id',0,'INT');
    if($calculation_id){
        $calculation =  $calculation_model->new_getData($calculation_id);
        if (!empty($calculation->n3)) {
            $canvas = json_encode($canvases_model->getFilteredItemsCanvas("`a`.`id` = $calculation->n3")[0]);
        }
        else $canvas = json_encode(null);
        $calc_img_filename = md5("calculation_sketch" . $calculation_id) . ".svg";
        if(file_exists($_SERVER['DOCUMENT_ROOT'].'/calculation_images/' . $calc_img_filename)){
            $calc_img = '/calculation_images/' . $calc_img_filename.'?t='.time();
        }
        else {
            $calc_img = "";
        }
        $project_id = $calculation->project_id;
        if(empty($project_id)){
            throw new Exception("Пустой id проекта");
        }
        $recalc = $jinput->get('recalc',0,'INT');
    }
    else{
        /* сгенерировать ошибку или создать калькуляцию? */
        throw new Exception("Пустой id калькуляции");
    }
    
    
   
?>
<!-- форма для чертилки-->
<form method="POST" action="/sketch/index.php" style="display: none" id="form_url">
	<input name="user_id" id="user_id" value="<?php echo $user->id ;?>" type="hidden">
	<input name = "width" id = "width" value = "" type = "hidden">
	<input name = "texture" id = "texture" value = "" type = "hidden">
	<input name = "color" id = "color" value = "" type = "hidden">
	<input name = "manufacturer" id = "manufacturer" value = "" type = "hidden">
    <input name = "auto" id = "auto" value="" type = "hidden">
    <input name = "walls" id = "walls" value="" type= "hidden">
    <input name = "calc_id" id = "calc_id" value="<?php echo $calculation_id;?>" type = "hidden">
    <input name = "n4" id = "n4" value ="" type ="hidden">
    <input name = "n5" id = "n5" value ="" type ="hidden">
    <input name = "n9" id = "n9" value ="" type ="hidden">
	<input name = "triangulator_pro" id = "triangulator_pro" value = "<?php echo $triangulator_pro?>" type = "hidden">
	<input name="proj_id" id="proj_id" value = "" type="hidden">
</form>
<form>
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
                <table class="table_calcform" style="margin-bottom: 5px;">
                    <tr>
                        <td class="td_calcform1" style="text-align: left;">
                            <label id="jform_n2-lbl" for="jform_n2">Выберите фактуру полотна</label>
                        </td>
                        <td class="td_calcform2">
                            <div class="btn-primary help" style="padding: 5px 10px; border-radius: 5px; height: 38px; width: 38px; margin-left: 5px;">
                                <div class="help_question">?</div>
                                <span class="airhelp">
                                    <strong>Выберите фактуру для Вашего будущего потолка</strong>
                                    <ul style="text-align: left;">
                                        <li>Матовый больше похож на побелку</li>
                                        <li>Сатин – на крашенный потолок</li>
                                        <li>Глянец – имеет легкий отблеск</li>
                                    </ul>									
                                </span>
                            </div>
                        </td>
                    </tr>
                </table>
                <select id="jform_n2" name="jform[n2]" class="form-control inputbox">
                </select>
            </div>
        </div>
        <div class="col-sm-4"></div>
    </div>
    <!-- Ширина -->
    <input type = "hidden" id = "width" name = 'jform[width]'>
    <!-- Цвет -->
    <div class="container">
        <div class="col-sm-4"></div>
        <div class="col-sm-4">
            <div style="width: 100%; text-align: left;">
                <label id="jform_color_switch-lbl" for="color_switch" style="display: none; text-align: left !important;">Выберите цвет:</label>
            </div>
            <button id="color_switch" class="btn btn-primary btn-width" type="button" style="display: none; margin-bottom: 1.5em;">Цвет <img id="color_img" class="calculation_color_img" style='width: 50px; height: 30px; display:none;' /></button>
            <input id="jform_color" name="jform[color]"  type="hidden">
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
                    <table class="table_calcform" style="margin-bottom: 5px;">
                        <tr>
                            <td class="td_calcform1" style="text-align: left;">
                                <label id="jform_proizv-lbl" for="jform_proizv">Выберите производителя</label>
                            </td>
                            <td class="td_calcform2">
                                <div class="btn-primary help" style="padding: 5px 10px; border-radius: 5px; height: 38px; width: 38px; margin-left: 5px;">
                                    <div class="help_question">?</div>
                                    <span class="airhelp">
                                        От производителя материала зависит качество потолка и его цена!									
                                    </span>
                                </div>
                            </td>
                        </tr>
                    </table>
                    <select id="jform_proizv" name="jform[proizv]" class="form-control inputbox">
                    </select>
                </div>
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
                <div id="sketch_image_block" style="padding: 25px; display:none;">
                        <img id="sketch_image" style="width: 100%;">
                </div>
            </div>
            <div class="col-sm-4"></div>
        </div>
    </div>
    <div class="container">
			<div class="row sm-margin-bottom">
				<div class="col-sm-4"></div>
				<div class="col-sm-4 ">
					<button id="redactor" class="btn btn-primary" type="button">Редактор</button>
				</div>
				<div class="col-sm-4"></div>
			</div>
	</div>
    <!-- S,P,углы -->
    <div class="container">
        <div id="data-wrapper" style = "display:none;">
            <div class="row sm-margin-bottom">
                <div class="col-sm-4"></div>
                <div class="col-sm-4 xs-center">
                    <table style="width: 100%;">
                        <tr>
                            <td width=35%>
                                <label id="jform_n4-lbl" for="jform_n4" class="center" > S = </label>
                            </td>
                            <td width=55%>
                                <input name="jform[n4]" class="form-control-input" id="jform_n4" data-next="#jform_n5" placeholder="Площадь комнаты"  readonly  type="tel"> 
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
                                <input name="jform[n5]" class="form-control-input" id="jform_n5" data-next="#jform_n9"  placeholder="Периметр комнаты" readonly  type="tel"> 
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
                                <input name="jform[n9]" id="jform_n9" data-next="#jform_n27" class="form-control-input" placeholder="Кол-во углов"  readonly  type="tel"> 
                            </td>
                            <td width=10%>
                                <label for="jform_n9" class="control-label">шт.</label>
                            </td>
                        </tr>
                    </table>
                    <input name = "jform[offcut_square]" id = "jform_offcut_square" type="hidden">
                </div>
                <div class="col-sm-4"></div>
            </div>
        </div>
    </div>
    <div id="add_mount_and_components" class="container">
        <div class="row">
            <div class="col-sm-4"></div>
            <div class="col-sm-4">
                <button type="button" id="btn_add_components" class="btn btn-primary" style="width: 100%; margin-bottom: 25px;">Добавить монтаж и комплектующие</button>
            </div>
            <div class="col-sm-4"></div>
        </div>
    </div>
    <!-- Рассчитать -->
    <div class="container">
        <div class="row sm-margin-bottom" style="margin-top: 25px">
            <div class="col-sm-4"></div>
            <div class="col-sm-4">
                <button id="calculate_button" class="btn btn-success btn-big" type="button">
                    <span class="loading" style="display: none;">
                        Считаю...<i class="fa fa-refresh fa-spin fa-3x fa-fw"></i>
                    </span>
                    <span class="static">Рассчитать</span>
                </button>
            </div>
            <div class="col-sm-4"></div>
        </div>
    </div>
</form>
<script>
    jQuery('document').ready(function()
    {
        let canvases_data = JSON.parse('<?php echo $canvases_data;?>');
        let textures = [];
        let canvases_data_of_selected_texture = [];
        let calculation = JSON.parse('<?php echo json_encode($calculation);?>');
        let canvas = JSON.parse('<?php echo $canvas;?>');
        let need_click = <?php echo $recalc;?>; 
        console.log(canvases_data);
        fill_calc_data();

        jQuery.each(canvases_data, function(key,value){
            let texture = {id:value.texture_id, name: value.texture_title};
            if(!obj_in_array(textures,texture)){
                textures.push(texture);
                let option = jQuery("<option></option>")
                                .attr("value", +texture.id)
                                .text(texture.name);
                jQuery("#jform_n2").append(option);
            }
        });
        
        select_colors();
        initial_fill();

        document.getElementById('jform_n2').onchange = select_colors;
        jQuery('.click_color').change(select_manufacturers);
        document.getElementById('jform_proizv').onchange = select_widths;
        
        function select_colors(){
            let colors = [];
            canvases_data_of_selected_texture = [];

            jQuery.each(canvases_data, function(key,value){
                let select_texture = document.getElementById('jform_n2').value;
                if (value.texture_id === select_texture)
                {
                    canvases_data_of_selected_texture.push(value);
                    let color = value.color_id;
                    if(!in_array(colors, color) && color !== null){
                        colors.push(color);
                    }
                }
            });
            if(canvas && canvas.filled){
                jQuery("#auto").val(1);
            }
            jQuery("#color_img").prop( "src", "");
            jQuery("#color_img").hide();
            jQuery("#jform_color").val("");
            if(colors.length>0){
                jQuery("#jform_color_switch-lbl").show();
                jQuery("#color_switch").show();
            }
            else{
                jQuery("#jform_color_switch-lbl").hide();
                jQuery("#color_switch").hide();
            }
            if(canvas && !canvas.filled && canvas.color_id){
                fill_selected_color(canvas.color_file,canvas.color_id);
            }
            select_manufacturers();
        }

        function select_manufacturers()
        {
            let manufacturers = [];
            let select_texture = document.getElementById('jform_n2').value;
            let select_color = (document.getElementById('jform_color').value) ? document.getElementById('jform_color').value : null;
            console.log("select_color",select_color);
            jQuery("#jform_proizv").empty();
            jQuery.each(canvases_data_of_selected_texture, function(key,value){
                if (value.texture_id === select_texture && value.color_id === select_color)
                {
                    let proizv = value.name + " " + value.country;
                    if(!in_array(manufacturers, proizv)){
                        manufacturers.push(proizv);
                        let option = jQuery("<option></option>")
                                        .attr("value", value.manufacturer_id)
                                        .text(proizv);
                        jQuery("#jform_proizv").append(option);
                    }
                }
            });
            if(canvas && canvas.filled){
                jQuery("#auto").val(1);
            }
            select_widths();
        }

        function select_widths()
        {
            let arr_widths = [];
            let select_proizv = document.getElementById('jform_proizv').value;
            let width_polotna = [];
            jQuery.each(canvases_data_of_selected_texture, function(key,value){
                if (value.manufacturer_id === select_proizv)
                {
                    let width = Math.round(value.width * 100);
    
                    if(!in_array(arr_widths, width)){
                        arr_widths.push(width);
                        width_polotna.push({width:width,price:+value.price});
                    }
                }
            });
            width_polotna.sort(function (a, b) {
                if (a.width < b.width) {
                    return 1;
                }
                if (a.width > b.width) {
                    return -1;
                }
                return 0;
            });
            jQuery("#width").val(JSON.stringify(width_polotna));
            if(canvas && canvas.filled){
                jQuery("#auto").val(1);
            }
        }
        //выбор цвета
        jQuery( "#color_switch" ).click(function(){
            var items = "<div class='center'>";
            jQuery.each(canvases_data_of_selected_texture, function( key, val ) {
                items += `<button class='click_color' type='button' data-color_id='${+val.color_id}' data-color_img='${val.color_file}'><img src='${val.color_file}'/><div class='color_title1'>${val.color_title}</div><div class='color_title2'>${val.color_title}</div></button>`;
    
            });
            items += "</div>";
            modal({
                type: 'info',
                title: 'Выберите цвет',
                text: items,
                size: 'large',
                onShow: function() {
                    jQuery(".click_color").click(function(){
                        fill_selected_color(jQuery(this).data("color_img"),jQuery( this ).data("color_id"));
                        select_manufacturers();
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
                template: '<div class="modal-box"><div class="modal-inner"><div class="modal-title"><a class="modal-close-btn" id = "modal_close_color"></a></div><div class="modal-text"></div><div class="modal-buttons"></div></div></div>',
                _classes: {
                    box: '.modal-box',
                    boxInner: ".modal-inner",
                    title: '.modal-title',
                    content: '.modal-text',
                    buttons: '.modal-buttons',
                    closebtn: '.click_color'
                }
            });
            document.getElementById('modal_close_color').onclick = function(){
                jQuery("#modal-window").hide();
            };						
        });
        //начертить
        jQuery("#sketch_switch").click(function(){
            submit_form_sketch();
        });
        //рассчитать
        jQuery("#calculate_button").click(function(){
            let recalc = jQuery("#auto").val();
            if(recalc){
                submit_form_sketch();
            }
        });

        jQuery("#redactor").click(function(){
            jQuery("#calc_id").val(calculation.id);
            jQuery("#proj_id").val(calculation.project_id);
            jQuery("#form_url").attr('action','sketch/cut_redactor_2/index.php');
            submit_form_sketch();
		});
        function generate_block(object){
            let cnt = create_container(object.block_id,object.btn_cont_id);
            jQuery(`#${object.prev_id}`).after(cnt);
            if(object.btn_id){
                let block =  create_block_btn('table_calcform',"margin-bottom: 15px;",object.btn_id,object.btn_text,`help_${object.block_id}`);
                jQuery(`#${object.btn_cont_id}`).append(block);
            }
            else{
                jQuery(`#${object.btn_cont_id}`).append(object.btn_text);
            }
        }
        //добавить комплектующие && монтаж
        jQuery("#btn_add_components").click(function(){
            let arr_blocks = [
                {block_id:"block_n28",btn_cont_id:"btn_cont_n28",prev_id:"add_mount_and_components",btn_id:"btn_baguette",btn_text:(calculation.n_28) ? "Изменить багет" : "Добавить багет"},
                {block_id:"block_n6",btn_cont_id:"btn_cont_n6",prev_id:"block_n28",btn_id:"btn_insert",btn_text:"Декоративная вставка"},
                {block_id:"block_light_cptn",btn_cont_id:"head_lighting",prev_id:"block_n6",btn_id:"",btn_text:"<h3>Освещение</h3>"},
                {block_id:"block_n12",btn_cont_id:"btn_cont_n1",prev_id:"block_light_cptn",btn_id:"btn_chandelier",btn_text:"Добавить люстры"},
                {block_id:"block_n13",btn_cont_id:"btn_cont_n13",prev_id:"block_n12",btn_id:"btn_fixtures",btn_text:"Добавить светильники"},
                {block_id:"block_n19",btn_cont_id:"btn_cont_n19",prev_id:"block_n13",btn_id:"btn_wire",btn_text:"Провод"},
                {block_id:"block_oter_mount_cptn",btn_cont_id:"head_other_mount",prev_id:"block_n19",btn_id:"",btn_text:"<h4>Прочий монтаж</h4>"},
                {block_id:"block_n14",btn_cont_id:"btn_cont_n14",prev_id:"block_oter_mount_cptn",btn_id:"btn_pipes",btn_text:"Добавить трубы входящие в потолок"},
                {block_id:"block_n16",btn_cont_id:"btn_cont_n16",prev_id:"block_n14",btn_id:"btn_cornice",btn_text:"Добавить шторный карниз"},
                {block_id:"block_n17",btn_cont_id:"btn_cont_n17",prev_id:"block_n16",btn_id:"btn_bar",btn_text:"Закладная брусом"},
                {block_id:"block_n20",btn_cont_id:"btn_cont_n20",prev_id:"block_n17",btn_id:"btn_delimeter",btn_text:"Разделитель"},
                {block_id:"block_n7",btn_cont_id:"btn_cont_n7",prev_id:"block_n20",btn_id:"btn_bar",btn_text:"Метраж стен с плиткой"},
                {block_id:"block_n8",btn_cont_id:"btn_cont_n8",prev_id:"block_n7",btn_id:"btn_stoneware",btn_text:"Метраж стен с керамогранитом"},
                {block_id:"block_n18",btn_cont_id:"btn_cont_n18",prev_id:"block_n8",btn_id:"btn_stoneware",btn_text:"Усиление стен"},
                {block_id:"block_dop_krepezh",btn_cont_id:"btn_dop_krepezh",prev_id:"block_n18",btn_id:"btn_fixture2",btn_text:"Дополнительный крепеж"},
                {block_id:"block_n21",btn_cont_id:"btn_cont_n21",prev_id:"block_dop_krepezh",btn_id:"btn_firealarm",btn_text:"Пожарная сигнализация"},
                {block_id:"block_n22",btn_cont_id:"btn_cont_n22",prev_id:"block_n21",btn_id:"btn_hoods",btn_text:"Вентиляция"},
                {block_id:"block_n23",btn_cont_id:"btn_cont_n23",prev_id:"block_n22",btn_id:"btn_diffuser",btn_text:"Диффузор"},
                {block_id:"block_n30",btn_cont_id:"btn_cont_n30",prev_id:"block_n23",btn_id:"btn_soaring",btn_text:"Парящий потолок"},
                {block_id:"block_n29",btn_cont_id:"btn_cont_n29",prev_id:"block_n30",btn_id:"btn_level",btn_text:"Переход уровня"},
                {block_id:"block_n31",btn_cont_id:"btn_cont_n31",prev_id:"block_n29",btn_id:"btn_notch2",btn_text:"Внутренний вырез (в цеху)"},
                {block_id:"block_n11",btn_cont_id:"btn_cont_n11",prev_id:"block_n31",btn_id:"btn_notch1",btn_text:"Внутренний вырез (на месте)"},
                {block_id:"block_n32",btn_cont_id:"btn_cont_n32",prev_id:"block_n11",btn_id:"btn_draining",btn_text:"Слив воды"},
                {block_id:"block_height",btn_cont_id:"btn_cont_height",prev_id:"block_n32",btn_id:"btn_height",btn_text:"Высота помещения"},
                {block_id:"block_n24",btn_cont_id:"btn_cont_n24",prev_id:"block_height",btn_id:"btn_access",btn_text:"Сложность доступа"},
                {block_id:"block_extra",btn_cont_id:"btn_cont_extra",prev_id:"block_n24",btn_id:"btn_accessories",btn_text:"Другие комплектующие"},
                {block_id:"block_extra2",btn_cont_id:"btn_cont_extra2",prev_id:"block_extra",btn_id:"btn_accessories2",btn_text:"Другие комплектующие со склада"},
                {block_id:"block_extra_mount",btn_cont_id:"btn_cont_extra_mount",prev_id:"block_extra2",btn_id:"btn_mount",btn_text:"Другие работы по монтажу"},
                {block_id:"block_need_mount",btn_cont_id:"btn_cont_need_mount",prev_id:"block_extra_mount",btn_id:"btn_mount2",btn_text:"Отменить монтаж"},
                {block_id:"block_discount",btn_cont_id:"btn_cont_discount",prev_id:"block_need_mount",btn_id:"",btn_text:"Скидка"}
            ];
            arr_blocks.forEach(function(item){
                generate_block(item);
            });

        });
        function create_block_btn(class_name,style,btn_id,btn_text,help){
            return `<table class="${class_name}" style="${style}">
								<tr>
									<td class="td_calcform1">
										<button type="button" id="${btn_id}" class="btn add_fields">
                                        <label class="no_margin">${btn_text}</label>
										</button>
									</td>
									<td class="td_calcform2">
										<div class="btn-primary help" style="padding: 5px 10px; border-radius: 5px; height: 38px; width: 38px; margin-left: 5px;">
											<div class="help_question">?</div>
											    ${help}
										</div>
									</td>
								</tr>
							</table>`;
        }
        function submit_form_sketch()
	    {
            var regexp_d = /^\d+$/;
            if (!regexp_d.test(document.getElementById('jform_n2').value)
                || !regexp_d.test(document.getElementById('user_id').value))
            {
                alert("Неверный формат входных данных!");
                return;
            }
            document.getElementById('texture').value = document.getElementById('jform_n2').value;
            document.getElementById('color').value = document.getElementById('jform_color').value;
            document.getElementById('manufacturer').value=document.getElementById('jform_proizv').value;
            document.getElementById('n4').value = document.getElementById('jform_n4').value;
            document.getElementById('n5').value = document.getElementById('jform_n5').value;
            document.getElementById('n9').value = document.getElementById('jform_n9').value;
            if(calculation && calculation.original_sketch){
                document.getElementById('walls').value = calculation.original_sketch;
            }
            document.getElementById('form_url').submit();
            
        }
        function initial_fill(){
            let n2_options = jQuery("#jform_n2 option");
            let proizv_options = jQuery("#jform_proizv option");
            if(canvas){
                add_select_attr_to_option(n2_options,canvas.texture_id);
                select_colors();
                if(canvas.color_id){
                    jQuery("#jform_color_switch-lbl").show();
                    jQuery("#color_switch").show();
                }
                add_select_attr_to_option(proizv_options,canvas.manufacturer_id);
                canvas.filled = true;
            } 
        }
        function create_container(cnt_id,col_id){
            return `<div class = "container" id = "${cnt_id}">
                                <div class = "row">
                                    <div class = "col-sm-4"></div>
                                        <div class = "col-sm-4" id = "${col_id}"></div>
                                    <div class = "col-sm-4"></div>
                                </div>
                            </div>`;
            
        }
        function fill_selected_color(src,color_id){
            jQuery("#color_img").prop( "src", src);
            jQuery("#color_img").show();
            jQuery("#jform_color").val(color_id);
        }

        function add_select_attr_to_option(options,value){
            options.each(function(){
                if(jQuery(this).attr('value') === value){
                    jQuery(this).attr('selected','selected');
                }
            });
        }

        function fill_calc_data(){
            if(calculation.n4 && calculation.n5 && calculation.n9){
                jQuery("#jform_n4").val(calculation.n4);
                jQuery("#jform_n5").val(calculation.n5);
                jQuery("#jform_n9").val(calculation.n9);
                jQuery("#data-wrapper").show();
            }
            let filename = '<?php echo $calc_img;?>';
            if(filename){
                jQuery("#sketch_image").attr('src',filename);
                jQuery("#sketch_image_block").show();

            }
        } 
        
        function obj_in_array(array,obj){
            let result = false;
            for(let i = array.length; i--;){
                let value1 = JSON.stringify(array[i]),value2 = JSON.stringify(obj);
                if(value1 === value2){
                    result = true;
                    break;
                }
            }
            return result;
        }

        function in_array(array,value){
            let result = false;
            for(let i = array.length; i--;){
                if(array[i] === value){
                    result = true;
                    break;
                }
            }
            return result;
        }
        function click_after_recalc(){
            if(need_click){
                jQuery("#calculate_button").click();
                jQuery('html, body').animate({
                    scrollTop: jQuery("#calculate_button").offset().top
                }, 2000);
            }
        }
        setTimeout(click_after_recalc,500);
    });
</script>