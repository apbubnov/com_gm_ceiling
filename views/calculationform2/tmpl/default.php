<?php
    defined('_JEXEC') or die;
    JHtml::_('behavior.keepalive');
    //JHtml::_('behavior.tooltip');
    JHtml::_('behavior.formvalidation');
    $lang = JFactory::getLanguage();
    $lang->load('com_gm_ceiling', JPATH_SITE);
    $doc = JFactory::getDocument();
    $doc->addScript(JUri::base() . '/media/com_gm_ceiling/js/form.js');

    $canvases_model = Gm_ceilingHelpersGm_ceiling::getModel("canvases");
    $canvases_data = json_encode($canvases_model->getFilteredItemsCanvas("count>0"));
?>
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
        <button id="color_switch" class="btn btn-primary btn-width" type="button" style="display: none; margin-bottom: 1.5em;">Цвет <img id="color_img" class="calculation_color_img" style='width: 50px; height: 30px;' /></button>
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
            <div id="sketch_image_block" style="padding: 25px;">
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
<script>
    jQuery('document').ready(function()
    {
        let canvases_data = JSON.parse('<?php echo $canvases_data;?>');
        let textures = [];
        let canvases_data_of_selected_texture = [];
        console.log(canvases_data);

        jQuery.each(canvases_data, function(key,value){
            let texture = {id:value.texture_id, name: value.texture_title};
            if(!obj_in_array(textures,texture)){
                textures.push(texture);
                jQuery("#jform_n2")
                    .append(jQuery("<option></option>")
                                .attr("value", texture.id)
                                .text(texture.name));
            }
        });

        select_colors();

        document.getElementById('jform_n2').onchange = select_colors;
        jQuery('.click_color').change(select_manufacturers);
        document.getElementById('jform_proizv').onchange = select_widths;
        function select_colors(){
            let colors = [];
            
            jQuery.each(canvases_data, function(key,value){
                let select_texture = document.getElementById('jform_n2').value;
                if (value.texture_id === select_texture)
                {
                    let color = value.color_id;
                    if(!in_array(colors, color)){
                        colors.push(color);
                    }
                }
            });
            if(colors.length>0){
                //отрисовать кнопку с выбором цвета + окно
            }
           
            
            select_manufacturers();
        }
        function select_manufacturers()
        {
            let manufacturers = [];
            canvases_data_of_selected_texture = [];
            let select_texture = document.getElementById('jform_n2').value;
            let select_color = (document.getElementById('jform_color').value) ? document.getElementById('jform_color').value : null;
            jQuery("#jform_proizv").empty();

            jQuery.each(canvases_data, function(key,value){
                if (value.texture_id === select_texture && value.color_id === select_color)
                {
                    canvases_data_of_selected_texture.push(value);
                    let proizv = value.name + " " + value.country;

                    if(!in_array(manufacturers, proizv)){
                        manufacturers.push(proizv);
                        jQuery("#jform_proizv")
                            .append(jQuery("<option></option>")
                                        .attr("value", value.name)
                                        .text(proizv));
                    }
                }
            });
            console.log(canvases_data_of_selected_texture);
            select_widths();
        }

        function select_widths()
        {
            let arr_widths = [];
            let select_proizv = document.getElementById('jform_proizv').value;

            jQuery.each(canvases_data_of_selected_texture, function(key,value){
                if (value.name === select_proizv)
                {
                    let width = Math.round(value.width * 100);

                    if(!in_array(arr_widths, width)){
                        arr_widths.push(width);
                    }
                }
            });

            console.log(arr_widths);
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
    });
</script>