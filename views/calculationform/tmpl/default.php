<?php
    if ($_SERVER['SERVER_NAME'] == 'calc.gm-vrn.ru') {
        require_once('metrika.php');
    }
    defined('_JEXEC') or die;
    JHtml::_('behavior.keepalive');
    //JHtml::_('behavior.tooltip');
    JHtml::_('behavior.formvalidation');
    $jinput = JFactory::getApplication()->input;
    //$lang = JFactory::getLanguage();
    //$lang->load('com_gm_ceiling', JPATH_SITE);
    //$doc = JFactory::getDocument();
    //$doc->addScript(JUri::base() . '/media/com_gm_ceiling/js/form.js');
    header('Access-Control-Allow-Origin: https://гмпотолки.рф');
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    header('Access-Control-Max-Age: 1000');
    header('Access-Control-Allow-Headers: Content-Type, Content-Range, Content-Disposition, Content-Description');

    echo parent::getPreloaderNotJS();

    $user_id = $jinput->get('user_id','',"STRING");
    if(!empty($user_id)){
        $user = JFactory::getUser($user_id);
    }
    else{
        $user = JFactory::getUser();
    }
    $user_groups = $user->groups;
    $triangulator_pro = 0;
    if(in_array('16',$user_groups)){
        $triangulator_pro = 1;
        $min_sum = 100;
    }

    $type = $jinput->get('type', '', 'STRING');
    $subtype = $jinput->get('subtype', '', 'STRING');
    $precalculation = $jinput->get('precalculation', '', 'STRING');
    $seam = $jinput->get('seam', 0, 'INT');
    $api = $jinput->get('api', 0, 'INT');
    $device = $jinput->get('device','',"STRING");
    $lattitude = $jinput->get('latitude','',"STRING");
    $longitude = $jinput->get('longitude','',"STRING");
    $advt = $jinput->get('advt','',"STRING");
    $type_url = '';

    if(in_array('16', $user_groups) && $subtype == 'production'){
        $gm_mounters = "service";
    }

    if(!empty($gm_mounters)){
        $gm_mounters_url = "&gm_mounters=$gm_mounters";
    }
    if (!empty($type))
    {
        $type_url = "&type=$type";
    }

    $subtype_url = '';
    if (!empty($subtype))
    {
        $subtype_url = "&subtype=$subtype";
    }

    $precalculation_url = '';
    if (!empty($precalculation))
    {
        $precalculation_url = "&precalculation=$precalculation";
    }

    $device_url = '';
    if (!empty($device))
    {
        $device_url = "&device=$device";
    }

    $api_url = '';
    if (!empty($api))
    {
        $api_url = "&api=$api";
    }

    $lattitude_url = '';
    if (!empty($lattitude))
    {
        $lattitude_url = "&latitude=$lattitude";
    }

    $longitude_url = '';
    if (!empty($longitude))
    {
        $longitude_url = "&longitude=$longitude";
    }

    $advt_url = '';
    if (!empty($advt))
    {
        $advt_url = "&advt=$advt";
    }
    $user_url = '';
    if (!empty($user_id))
    {
        $user_url = "&user_id=$user_id";
    }
    if($api ==1){
        $ll = (!empty($lattitude) && !empty($longitude)) ? "$lattitude;$longitude" :"";
        $details = "device: $device;$ll";
    }

    if($type=="gmchief"){
        $view = "projectform";
    }
    else{
        $view = "project";
    }

    /*____________________Models_______________________  */
    $canvases_model = Gm_ceilingHelpersGm_ceiling::getModel("canvases");
    $calculation_model = Gm_ceilingHelpersGm_ceiling::getModel("calculation");
    $components_model = Gm_ceilingHelpersGm_ceiling::getModel("components");
    $calculationformModel = Gm_ceilingHelpersGm_ceiling::getModel("calculationform");
    $data = quotemeta(json_encode($calculationformModel->getFields(1),JSON_HEX_QUOT));

    /*____________________end_______________________  */
    $color_data = json_encode($components_model->getColor());

    $texturesData = json_encode($canvases_model->getCanvasesTextures());

    $calculation_id = $jinput->get('calc_id',0,'INT');
    if(!empty($calculation_id)){
        $calculation =  $calculation_model->new_getData($calculation_id);
        if (empty($calculation)) {
            throw new Exception("Расчет не найден", 1);
        }
        if(!empty($calculation->n3)){
            $canvas = $canvases_model->getFilteredItemsCanvas("a.id = $calculation->n3")[0];
        }

        if(!empty($canvas)){
            $filter = "texture_id = $canvas->texture_id and manufacturer_id = $canvas->manufacturer_id and count>0";
            if(!empty($canvas->color_id)){
                $filter .= " and color_id = $canvas->color_id";
            }
            $widths_data = $canvases_model->getFilteredItemsCanvas($filter);
            $arr_widths = [];$widths = [];
            foreach($widths_data as $value){
                $width = (float)$value->width*100;
                if(!in_array($width,$arr_widths)){
                    array_push($arr_widths,$width);
                    array_push($widths,(object)array("width"=>$width,"price"=>$value->price));
                }
            }
            usort($widths,function($a,$b){
                if($a->width < $b->width){
                    return 1;
                }
                if($a->width > $b->width){
                    return -1;
                }
                return 0;
            });
            $widths = json_encode($widths);
            $texture_title = $canvas->texture_title;
            $manufacturer_title = $canvas->name." ".$canvas->width;
            $color_file = $canvas->color_file;
        }
        $calculation->n37 = addslashes($calculation->n37);
        $calculation->extra_components = addslashes($calculation->extra_components);
        $calculation->extra_mounting = addslashes($calculation->extra_mounting);
        $calculation->components_stock = addslashes(Gm_ceilingHelpersGm_ceiling::decode_stock($calculation->components_stock));


        $calc_img_filename = md5('calculation_sketch'.$calculation_id).'.svg';
        if (file_exists($_SERVER['DOCUMENT_ROOT'].'/calculation_images/'.$calc_img_filename)) {
            $calc_img = '/calculation_images/'.$calc_img_filename.'?t='.time();
        } else {
            $calc_img = '';
        }

        $cut_img_filename = md5('cut_sketch'.$calculation_id).'.svg';
        if (file_exists($_SERVER['DOCUMENT_ROOT'].'/cut_images/'.$cut_img_filename)) {
            $cut_img = '/cut_images/'.$cut_img_filename.'?t='.time();
        } else {
            $cut_img = '';
        }

        $project_id = $calculation->project_id;
        if (empty($project_id)) {
            throw new Exception("Пустой id проекта", 1);
        }

        $save_button_url = "index.php?option=com_gm_ceiling&view=$view$type_url$subtype_url&id=$project_id";
    } else {
        /* сгенерировать ошибку или создать калькуляцию? */
        throw new Exception("Пустой id калькуляции", 1);
    }

?>
<style>
    .container
    {
        font-family: "Cuprum";
    }

</style>
<?php if ($api == 1): ?>
    <style type="text/css">
        header
        {
            display: none;
        }
        footer
        {
            display: none;
        }
    </style>
<?php endif ?>
<div class="modal_window_container" id="mv_container">
    <button type="button" class="close_btn" id="close"><i class="fa fa-times fa-times-tar" aria-hidden="true"></i></button>
    <div class="modal_window" id="modal_window_seam">
        <p>Потолок со швом. Изменить раскрой вручную?</p>
        <p><button type="button" id="hide_redactor" class="btn btn-primary">Нет</button>
            <button type="button" id="show_redactor" class="btn btn-primary to_redactor">Да</button></p>
    </div>

</div>
<!-- форма для чертилки-->
<form method="POST" action="/sketch/index.php" style="display: none" id="form_url">
    <input name = "texturesData" id = "texturesData" value ="" type="hidden">
    <input name = "texture" id = "texture" value = "<?php echo $canvas->texture_id?>" type = "hidden">
    <input name = "color" id = "color" value = "<?php echo $canvas->color_id?>" type = "hidden">
    <input name = "manufacturer" id = "manufacturer" value = "<?php echo $canvas->manufacturer_id?>" type = "hidden">
    <input name = "walls" id = "walls" value="" type= "hidden">
    <input name = "width" id = "width" value ='<?php echo $widths?>' type="hidden">
    <input name = "calc_id" id = "calc_id" value="<?php echo $calculation_id;?>" type = "hidden">
    <input name = "n4" id="n4" value="" type ="hidden">
    <input name = "n5" id="n5" value="" type ="hidden">
    <input name = "n9" id="n9" value="" type ="hidden">
    <input name = "triangulator_pro" id = "triangulator_pro" value = "<?php echo $triangulator_pro?>" type = "hidden">
    <input name="type_url" id="type_url" value="<?php echo $type_url; ?>" type="hidden">
    <input name="subtype_url" id="subtype_url" value="<?php echo $subtype_url; ?>" type="hidden">
    <input name="precalculation" id="precalculation" value="<?php echo $precalculation_url; ?>" type="hidden">
    <input name="device" id="device" value="<?php echo $device_url; ?>"  type="hidden">
    <input name="api" id="api" value="<?php echo $api_url; ?>"  type="hidden">
    <input name="latitude" id="latitude" value="<?php echo $lattitude_url; ?>" type="hidden">
    <input name="longitude" id="longitude" value="<?php echo $longitude_url; ?>" type="hidden">
    <input name = "advt" id="advt" value="<?php echo $advt_url;?>" type = "hidden">
    <input name = "user_url" id="user_url" value="<?php echo $user_url;?>" type = "hidden">
</form>

<form id="form-calculation" action="<?php echo JRoute::_('index.php?option=com_gm_ceiling&task=calculation.save'); ?>" method="post" class="form-validate form-horizontal" enctype="multipart/form-data">
    <input id="jform_id" type="hidden" name="jform[id]" value="<?php echo $calculation_id;?>"/>
    <div class="container">
        <div class="col-sm-3"></div>
        <div class="row sm-margin-bottom">
            <div class="col-sm-6">
                <h3>Рассчетная страница</h3>
            </div>
        </div>
        <div class="col-sm-6"></div>
    </div>

    <div class="container">
        <div class="row sm-margin-bottom">
            <div class="col-sm-3"></div>
            <div class="col-sm-6 ">
                <button id="sketch_switch" class="btn btn-primary btn-big" type="button">Начертить потолок</button>
                <div id="sketch_image_block" style="padding: 25px; display:none;">
                    <img id="sketch_image" style="width: 100%;">
                </div>
            </div>
            <div class="col-sm-3"></div>
        </div>
    </div>
    <!-- S,P,углы -->
    <div class="container">
        <div id="data-wrapper" style = "display:none;">
            <div class="row sm-margin-bottom">
                <div class="col-sm-3"></div>
                <div class="col-sm-6 xs-center">
                    <table style="width: 100%;">
                        <tr>
                            <td width=35%>
                                <label id="jform_texture-lbl" for="jform_n4"> Текстура: </label>
                            </td>
                            <td width=65%>
                                <input name="jform[texture]" class="form-control-input" id="jform_texture" value="<?php echo $texture_title?>" data-next="#jform_proizv" readonly>
                            </td>
                        </tr>
                        <tr>
                            <td width=35%>
                                <label id="jform_proizv-lbl" for="jform_proizv"> Производитель: </label>
                            </td>
                            <td width=65%>
                                <input name="jform[proizv]" class="form-control-input" id="jform_proizv" value="<?php echo $manufacturer_title?>" data-next="#jform_color" readonly>
                            </td>
                        </tr>
                        <?php if(!empty($color_file)){?>
                            <tr>
                                <td width=35%>
                                    <label id="jform_color-lbl" for="jform_color"> Цвет: </label>
                                </td>
                                <td width=65%>
                                    <img src="<?php echo $color_file?>" style="height:55px">
                                </td>
                            </tr>
                        <?php }?>
                        <tr>
                            <td width=35%>
                                <label id="jform_color-lbl" for="jform_n4"> Площадь: </label>
                            </td>
                            <td width=55%>
                                <input name="jform[n4]" class="form-control-input" id="jform_n4" data-next="#jform_n5" readonly>
                            </td>
                            <td width=10%>
                                <label for="jform_n4" class="control-label"> м<sup>2 </sup></label>
                            </td>
                        </tr>
                        <tr>
                            <td width=35%>
                                <label id="jform_n5-lbl" for="jform_n5"> Периметр: </label>
                            </td>
                            <td width=55%>
                                <input name="jform[n5]" class="form-control-input" id="jform_n5" data-next="#jform_n9" readonly>
                            </td>
                            <td width=10%>
                                <label for="jform_n5" class="control-label"> м </label>
                            </td>
                        </tr>
                        <tr>
                            <td width=35%>
                                <label id="jform_n9-lbl" for="jform_n9"> Кол-во углов: </label>
                            </td>
                            <td width=55%>
                                <input name="jform[n9]" id="jform_n9" data-next="#jform_n27" class="form-control-input" readonly>
                            </td>
                            <td width=10%>
                                <label for="jform_n9" class="control-label">шт.</label>
                            </td>
                        </tr>
                        <tr>
                            <td width=35%>
                                <label id="jform_shrink_per-lbl" for="jform_shrink_per"> % усадки: </label>
                            </td>
                            <td width=55%>
                                <input name="jform[shrink_per]" id="jform_shrink_per" data-next="#jform_n27" class="form-control-input" readonly>
                            </td>
                            <td width=10%>
                                <label for="jform_n9" class="control-label">%</label>
                            </td>
                        </tr>
                        <tr>
                            <td width=35%>
                                <label id="jform_n10-lbl" for="jform_n10"> Криволинейный участок: </label>
                            </td>
                            <td width=55%>
                                <input name="jform[n10]" id="jform_n10" class="form-control-input" readonly>
                            </td>
                            <td width=10%>
                                <label for="jform_n10" class="control-label">м.</label>
                            </td>
                        </tr>
                        <tr>
                            <td width=35%>
                                <label id="jform_n11-lbl" for="jform_n31"> Внутренний вырез: </label>
                            </td>
                            <td width=55%>
                                <input name="jform[n31]" id="jform_n31" class="form-control-input" readonly>
                            </td>
                            <td width=10%>
                                <label for="jform_n31" class="control-label">м.</label>
                            </td>
                        </tr>
                    </table>
                    <div id="div_for_test" style="display: none;">
                        <label>Усаженный периметр:</label> <input id="input_n5_shrink" type="text" readonly><br>
                        <label>Площадь обрезков:</label> <input name="jform[offcut_square]" id="jform_offcut_square" type="text" readonly><br>
                        <label>Процент усадки:</label> <input id="input_shrink_percent" type="text" readonly><br>
                        <label>Координаты:</label> <textarea id="input_cut_data" style="width: 600px; height: 200px;" readonly resize></textarea><br>
                        <img id="cut_image" style="width: 100%;">
                    </div>
                </div>
                <div class="col-sm-3"></div>
            </div>
        </div>
    </div>
    <div>
        <?php if ($triangulator_pro) { ?>
            <div class="container">
                <div class="row">
                    <div class="col-sm-3"></div>
                    <div class="col-sm-6">
                        <button class="btn btn-primary to_redactor" type="button" style="width: 100%; margin-bottom: 25px;"><i class="fas fa-edit" aria-hidden="true"></i> Изменить раскрой</button>
                    </div>
                    <div class="col-sm-3"></div>
                </div>
            </div>
        <?php } ?>

        <div id="add_mount_and_components" class="container">
            <div class="row">
                <div class="col-sm-3"></div>
                <div class="col-sm-6">
                    <button type="button" id="btn_add_components" class="btn btn-primary" style="width: 100%; margin-bottom: 25px;"><img src="../../../../../images/screwdriver.png" class="img_calcform"> <b>Добавить монтаж и комплектующие</b></button>
                </div>
                <div class="col-sm-3"></div>
            </div>
        </div>

        <div id="params_block">

        </div>
        <!-- Рассчитать -->
        <div class = "container">
            <div class="row sm-margin-bottom">
                <div class="col-sm-3"></div>
                <div class="col-sm-6 pull-center">
                    <h3>Процент скидки</h3>
                    <input name= "jform[discount]" id="new_discount" class="form-control" placeholder="Введите %" type="number" max="100" min="0" type="number" value="<?php echo $calculation->discount; ?>" >
                </div>
                <div class="col-sm-3"></div>
            </div>
        </div>
        <div class="container">
            <div class="row sm-margin-bottom" style="margin-top: 25px">
                <div class="col-sm-3"></div>
                <div class="col-sm-6">
                    <button id="calculate_button" class="btn btn-success btn-big" type="button">
                    <span class="loading" style="display: none;">
                        Считаю...<i class="fas fa-sync fa-spin fa-3x fa-fw"></i>
                    </span>
                        <span class="static">Рассчитать</span>
                    </button>
                </div>
                <div class="col-sm-3"></div>
            </div>
        </div>
        <div class="container" id = "sum_info" style="display:none">
            <div class="row sm-margin-bottom" style="margin-top: 25px">
                <div class="col-sm-3"></div>
                <div class="col-sm-6">
                    <p>
                        В стоимость входят материалы и работы по установке.
                    </p>
                </div>
                <div class="col-sm-3"></div>
            </div>
        </div>
        <div id="under_calculate" style="display: none;">
            <div id="result_block">
                <div class="container">
                    <div class="row sm-margin-bottom">
                        <div class="col-sm-3"></div>
                        <div class="col-sm-6 total_price center">
                            <div class="price_value">
                                <span id="price_api" style="display: none;"></span>
                                <span id="final_price">0.00</span> руб.
                            </div>
                        </div>
                        <div class="col-sm-3"></div>
                    </div>
                </div>
                <?php if($triangulator_pro){?>
                    <div class="container" id = "new_sum_container">
                        <div class="row">
                            <div class="col-sm-3"></div>
                            <div class="col-sm-6 center">
                                <label for="new_sum">
                                    Введите новую сумму
                                </label><br>
                                <input type="tel" id = new_sum class="input-gm">
                                <button class="btn btn-primary btn-sm" id ="save_new_sum" type="button">
                                    <i class="fas fa-save" aria-hidden="true"></i>
                                </button>

                            </div>
                            <div class="col-sm-3"></div>
                        </div>
                    </div>
                <?php }?>
            </div>
            <!-- название расчета -->
            <div class="form-group under_calculate">
                <div class="container">
                    <div class="row">
                        <div class="col-sm-3"></div>
                        <div class="col-sm-6">
                            <table class="table_calcform">
                                <tr>
                                    <td class="td_calcform1">
                                        <label id="jform_calculation_title-lbl" for="jform_calculation_title" class="">Название расчета:</label>
                                    </td>
                                    <td class="td_calcform2">
                                        <div class="btn-primary help" style="padding: 5px 10px; border-radius: 5px; height: 38px; width: 38px; margin-left: 5px;">
                                            <div class="help_question">?</div>
                                            <span class="airhelp">
													Назовите чертеж, по названию комнаты, в которой производится замер, что бы легче было потом ориентироваться. Например: "Спальня".
												</span>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                            <input id="jform_calculation_title" name="jform[calculation_title]"  class="form-control" type="text">
                        </div>
                        <div class="col-sm-3"></div>
                    </div>
                </div>
            </div>
            <?php if ($type === "gmcalculator" || $type === "calculator" || $api == 1)  { ?>
                <div class="container" id ="block_details">
                    <div class="row"  style="margin-bottom: 15px;">
                        <div class="col-sm-3"></div>
                        <div class="col-sm-6">
                            <table class="table_calcform">
                                <tr>
                                    <td class="td_calcform3">
                                        <button type="button" id="btn_details" data-cont_id="block_details" class="btn btn-primary" style="width: 100%;">Комментарий</button>
                                    </td>
                                </tr>
                            </table>
                            <input type="text" id="jform_details" name="jform[details]" value = "<?php echo $details;?>" class="form-control"  placeholder="Комментарий" style="display: none; margin-top: 20px; margin-bottom: 5px;">
                        </div>
                        <div class="col-sm-3"></div>
                    </div>
                </div>
            <?php } ?>
            <?php if($triangulator_pro == 1){?>
                <div class="container">
                    <div class="row" style="margin-bottom: 15px;">
                        <div class="col-sm-3"></div>
                        <div class="col-sm-6">
                            <label>Примечание менеджера</label>
                            <input type="text" id="jform_manager_note" name="jform[manager_note]" value = "" class="form-control"  placeholder="Комментарий" style="margin-top: 20px; margin-bottom: 5px;">
                        </div>
                        <div class="col-sm-3"></div>
                    </div>
                </div>
            <?php }?>
            <!-- кнопки -->
            <div class="container btn_tar">
                <div class="row sm-margin-bottom">
                    <div class="col-sm-3"></div>
                    <div class="col-sm-6">
                        <table style="width:100%; text-align: center;">
                            <tr>
                                <td style="text-align: center;">
                                    <button id="save_button" type = "button" class="btn btn-success">Сохранить</button>
                                </td>
                                <td style="text-align: center;">
                                    <!-- отменить -->
                                    <button type="button" id="cancel_button" class="btn btn-danger">Назад</button>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-sm-3"></div>
                </div>
            </div>
        </div>

</form>

<script type="text/javascript">
    jQuery(document).ready(function () {

        var data = JSON.parse('<?php echo $data?>');
        console.log(data);
        document.body.onload = function(){
            jQuery('.PRELOADER_GM').hide();
        };


        createBlocks(data);

        jQuery('.col-sm-6').on('mouseenter', '.help', function () {
            jQuery(this.lastElementChild).show();
        });

        jQuery('.col-sm-6').on('mouseleave', '.help', function () {
            jQuery(this.lastElementChild).hide();
        });

        jQuery('body').on('click','.btn_calc',function () {
            jQuery(this).closest('.col-sm-6').find('.inner_container').toggle();
        });

        jQuery('body').on('click','.add',function () {
           var rowFields = jQuery(jQuery(this).closest('.div-fields').find('.row-fields')[0]).clone();
           jQuery(this).closest('.row').before(rowFields);
        });

        jQuery('body').on('click','.delete_goods',function () {
            jQuery(this).closest('.row-fields').remove();
        });

    });

    function createBlocks(data){
        var div,containerDiv = jQuery("#params_block");
        jQuery.each(data,function (index,elem) {
            var buttonTitle = '<div class="col-xs-11"><b>'+elem.title+'</b></div><div class="col-xs-1"><i class="fa fa-angle-down" style="color: #414099;"></i></div>';
            div = jQuery(document.createElement('div'));
            div.addClass('row');
            div.append('<div class="col-sm-3"></div>');
            var btnDiv = jQuery(document.createElement('div')),
                button = jQuery(document.createElement('button'));
            button.addClass('btn btn_calc');
            button.prop('type','button');
            button.html(buttonTitle);
            btnDiv.addClass('col-sm-6');
            btnDiv.append(button);
            btnDiv.append(createWorkButton(elem.groups));
            div.append(btnDiv);
            div.append('<div class="col-sm-3"></div>');
            containerDiv.append(div);
        });

        console.log(div);
    }

    function createWorkButton(buttonsArray){
        var resultDiv = jQuery(document.createElement('div'));
        resultDiv.addClass('inner_container');
        resultDiv.css({'display': 'none'});
        jQuery.each(buttonsArray,function (index,elem) {
            var rowDiv = jQuery(document.createElement('div')),
                button = jQuery(document.createElement('button')),
                buttonHelp = jQuery(document.createElement('div')),
                buttonDivCol = jQuery(document.createElement('div')),
                helpDivCol = jQuery(document.createElement('div')),
                fieldsDiv = jQuery(document.createElement('div'));
            buttonDivCol.addClass('col-sm-11 col-xs-11');
            buttonDivCol.css({"padding-right":"5px"});
            helpDivCol.addClass('col-sm-1 col-xs-1');
            helpDivCol.css({"padding-left":"0px"});
            fieldsDiv.addClass('div-fields');
            /*кнопка подсказки*/
            buttonHelp.addClass('btn-primary help');
            buttonHelp.css({'padding': '5px 10px', 'border-radius': '5px', 'height': '38px', 'width': '38px','margin-left': '5px;'});
            buttonHelp.append('<div class="help_question center">?</div>');
            buttonHelp.append('<span class="airhelp" style="display: none;">'+elem.description+'</span>');
            helpDivCol.append(buttonHelp);
            /*кнопка раскрытия работы*/
            button.prop('type','button');
            button.addClass('btn add_fields');
            //button.css({'background-color': 'rgb(1, 0, 132)'});
            button.html('<div class="col-xs-2 col-sm-2"><i class="fa fa-angle-down" style="color: #414099;"></i></div><div class="col-xs-10 col-sm-10">'+elem.title+'</div>');
            buttonDivCol.append(button);
            //поля под кнопкой
            fieldsDiv.append(createFields(elem.fields));
            rowDiv.append(buttonDivCol);
            rowDiv.append(helpDivCol);
            rowDiv.append(fieldsDiv);
            rowDiv.addClass('row');
            rowDiv.css({'margin-bottom':'5px','margin-top':'5px'});
            resultDiv.append(rowDiv);
            console.log();
        });
        return resultDiv;
    }
    
    function createFields(fieldsData) {
        var resultDiv = jQuery(document.createElement('div'));
        resultDiv.addClass('row');
        jQuery.each(fieldsData,function (index,elem) {
            var divRow = jQuery(document.createElement('div')),
                countDiv = jQuery(document.createElement('div'));
            divRow.addClass('col-sm-12 row-fields');
            divRow.css({"margin-top":"10px","margin-bottom":"5px"})
            if(empty(elem.goods_category_id)){
                countDiv.append(createInput());
                divRow.append(countDiv);
            }else{
                var selectDiv = jQuery(document.createElement('div')),
                    deleteDiv = jQuery(document.createElement('div'));
                countDiv.addClass('col-sm-2 col-xs-2');
                selectDiv.addClass('col-sm-8 col-xs-8');
                deleteDiv.addClass('col-sm-2 col-xs-2');
                countDiv.append(createInput());
                selectDiv.append(createSelect(elem.goods));
                deleteDiv.append(createDeleteBtn());
                divRow.append(countDiv);
                divRow.append(selectDiv);
                divRow.append(deleteDiv);
            }
            resultDiv.append(divRow);
            if(elem.duplicate) {
                resultDiv.append(createAddBtn());
            }
        });
        return resultDiv;
    }
    
    function createSelect(selectData){
        var select = jQuery(document.createElement('select'));
        select.addClass('form-control goods_select');
        jQuery.each(selectData,function (index,elem) {
            select.append(jQuery('<option>', {
                value: elem.id,
                text: elem.name
            }));
        });
        return select;
    }
    
    function createInput(){
        var input = jQuery(document.createElement('input'));
        input.addClass('form-control');
        return input;
    }
    
    function createRadioBtns() {
        
    }
    function createDeleteBtn(){
        var deleteBtn = jQuery(document.createElement('button'));
        deleteBtn.addClass('clear_form_group btn btn-danger delete_goods');
        deleteBtn.prop('type','button');
        deleteBtn.html('<i class="fa fa-trash" aria-hidden="true"></i>');
        return deleteBtn;
    }
    
    function createAddBtn() {
        var div = jQuery(document.createElement('div')),
            addButton = jQuery(document.createElement('button'));
        div.addClass('row center');
        /*<button id="add_jform_n13" class="btn btn-primary add" style="margin-bottom:15px" type="button">Добавить</button>*/
        addButton.addClass('btn btn-primary add');
        addButton.css({'margin-bottom':'15px'});
        addButton.prop('type','button');
        addButton.html('<i class="fa fa-plus" aria-hidden="true"></i> Добавить');
        div.append(addButton);
        return div;
    }
</script>