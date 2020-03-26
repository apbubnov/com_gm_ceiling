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
/*____________________end_______________________  */
$color_data = json_encode($components_model->getColor());

$texturesData = json_encode($canvases_model->getCanvasesTextures('old'));

$calculation_id = $jinput->get('calc_id',0,'INT');
if(!empty($calculation_id)){
    $calculation =  $calculation_model->new_getData($calculation_id);
    if (empty($calculation)) {
        throw new Exception("Расчет не найден", 1);
    }
    if(!empty($calculation->n3)){
        $canvas = $canvases_model->getFilteredItemsCanvas("a.id = $calculation->n3",'old')[0];
    }

    if(!empty($canvas)){
        $filter = "texture_id = $canvas->texture_id and manufacturer_id = $canvas->manufacturer_id and count>0";
        if(!empty($canvas->color_id)){
            $filter .= " and color_id = $canvas->color_id";
        }
        $widths_data = $canvases_model->getFilteredItemsCanvas($filter,'old');
        $arr_widths = [];$widths = [];
        foreach($widths_data as $value){
            $width = (float)$value->width*100;
            if(!in_array($width,$arr_widths)){
                array_push($arr_widths,$width);
                array_push($widths,(object)["width"=>$width,"price"=>$value->price]);
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
    <div id="modal_window_rec_to_mesure" class="modal_window" style="float: center">
        <p><strong id="rec_header">Записаться на замер</strong></p>
        <p id="fio_cont">
            <label>ФИО:</label>
            <input type="text" id="fio" class = "input-gm" style = "float:right;">
        </p>
        <p id="phone_cont">
            <label>Телефон:</label>
            <input type="text" id="phone" class = "input-gm" style = "float:right;">
        </p>
        <p>
            <label>Адрес:</label>
            <input type="text" id="address" class = "input-gm" style = "float:right;">
        </p>
        <p>
            <label>Дом:</label>
            <input type="text" id="home" class = "input-gm" style = "float:right;">
        </p>
        <p>
            <label>Квартира:</label>
            <input type="text" id="appartment" class = "input-gm" style = "float:right;">
        </p>
        <p>
            <label>Дата:</label>
            <input type="date" id="rec_date" class = "input-gm" style = "float:right;">
        </p>
        <div id="rec_time_container" style="display:none;">
            <p>
                <label>Время:</label>
                <select  id="rec_time" class = "input-gm" style = "float:right;"></select>
            </p>
        </div>
        <p><button type="button" id="rec_to_measure" class="btn btn-primary">Записаться</button></p>
    </div>
    <div id="modal_window_authorisation" class="modal_window" >
        <p><strong id="auth_head">Похоже, у Вас уже есть аккаунт, пожалуйста авторизуйтесь</strong></p>
        <p>Логин:</p>
        <p><input type="text" id="login"></p>
        <p>Пароль:</p>
        <p><input type="text" id="pass"></p>
        <p>Введеные ранее данные об адресе и дате замера сохранились</p>
        <p><button type="button" id="rec_auth" class="btn btn-primary">Записаться на замер</button></p>
    </div>
</div>
<!-- форма для чертилки-->
<form method="POST" action="/sketch_old/index.php" style="display: none" id="form_url">
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
        <div class="col-sm-4"></div>
        <div class="row sm-margin-bottom">
            <div class="col-sm-4">
                <h3>Рассчетная страница</h3>
            </div>
        </div>
        <div class="col-sm-4"></div>
    </div>
    <!-- начертить -->
    <div class="container for_api">
        <div class="row">
            <div class="col-sm-4"></div>
            <div class="col-sm-4">
                <p>
                    <span class="caption_step">Шаг 1:</span> <strong>Начертите потолок</strong>
                    </br>Начертите контур помещения, вид сверху.
                    <span class="help" style="text-decoration: underline; color: #0275d8; padding: 0 0 0 5px;">
                        Пример
                        <span class="airhelp">
                            <button type="button" id="close_example" style="background-color: transparent; border: 0; color: #414099; top: -50px; right: 25px; position: absolute;"><i class="fa fa-times fa-times-tar" aria-hidden="true"></i></button>
                            <img src="../../../../../images/ceiling.png" alt="Потолок" style="height: 320px;">
                        </span>
                    </span>
                </p>
            </div>
            <div class="col-sm-4"></div>
        </div>
    </div>
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
    <!-- S,P,углы -->
    <div class="container">
        <div id="data-wrapper" style = "display:none;">
            <div class="row sm-margin-bottom">
                <div class="col-sm-4"></div>
                <div class="col-sm-4 xs-center">
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
                <div class="col-sm-4"></div>
            </div>
        </div>
    </div>
    <div>
        <?php if ($triangulator_pro) { ?>
            <div class="container">
                <div class="row">
                    <div class="col-sm-4"></div>
                    <div class="col-sm-4">
                        <button class="btn btn-primary to_redactor" type="button" style="width: 100%; margin-bottom: 25px;"><i class="fa fa-pencil-square-o" aria-hidden="true"></i>Изменить раскрой</button>
                    </div>
                    <div class="col-sm-4"></div>
                </div>
            </div>
        <?php } ?>
        <div class="container for_api">
            <div class="row">
                <div class="col-sm-4"></div>
                <div class="col-sm-4">
                    <p>
                        <span class="caption_step">Шаг 2:</span> <strong>Добавьте дополнительные работы</strong>
                        </br>Добавьте дополнительные работы, которые необходимы в Вашем потолке, например, люстры, трубы и т.д.
                    </p>
                </div>
                <div class="col-sm-4"></div>
            </div>
        </div>
        <div id="add_mount_and_components" class="container">
            <div class="row">
                <div class="col-sm-4"></div>
                <div class="col-sm-4">
                    <button type="button" id="btn_add_components" class="btn btn-primary" style="width: 100%; margin-bottom: 25px;"><img src="../../../../../images/screwdriver.png" class="img_calcform"> Добавить монтаж и комплектующие</button>
                </div>
                <div class="col-sm-4"></div>
            </div>
        </div>
        <!-- Рассчитать -->
        <div class="container for_api">
            <div class="row">
                <div class="col-sm-4"></div>
                <div class="col-sm-4">
                    <p style="margin-bottom: 0;">
                        <span class="caption_step">Шаг 3:</span> <strong>Расчитайте стоимость своего потолка</strong>
                    </p>
                </div>
                <div class="col-sm-4"></div>
            </div>
        </div>
        <?php if ($api != 1) { ?>
            <div class = "container">
                <div class="row sm-margin-bottom">
                    <div class="col-sm-4"></div>
                    <div class="col-sm-4 pull-center">
                        <h3>Процент скидки</h3>
                        <input name= "jform[discount]" id="new_discount" class="form-control" placeholder="Введите %" type="number" max="100" min="0" type="number" value="<?php echo $calculation->discount; ?>" >
                    </div>
                    <div class="col-sm-4"></div>
                </div>
            </div>
        <?php } ?>
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
        <div class="container" id = "sum_info" style="display:none">
            <div class="row sm-margin-bottom" style="margin-top: 25px">
                <div class="col-sm-4"></div>
                <div class="col-sm-4">
                    <p>
                        В стоимость входят материалы и работы по установке.
                    </p>
                </div>
                <div class="col-sm-4"></div>
            </div>
        </div>
        <div id="under_calculate" style="display: none;">
            <div id="result_block">
                <div class="container">
                    <div class="row sm-margin-bottom">
                        <div class="col-sm-4"></div>
                        <div class="col-sm-4 total_price center">
                            <div class="price_value">
                                <span id="price_api" style="display: none;"></span>
                                <span id="final_price">0.00</span> руб.
                            </div>
                            <div class="price_title">
                                Самая низкая цена в Воронеже!
                            </div>
                        </div>
                        <div class="col-sm-4"></div>
                    </div>
                </div>
                <?php if($triangulator_pro){?>
                    <div class="container" id = "new_sum_container">
                        <div class="row">
                            <div class="col-sm-4"></div>
                            <div class="col-sm-4 center">
                                <label for="new_sum">
                                    Введите новую сумму
                                </label><br>
                                <input type="tel" id = new_sum class="input-gm">
                                <button class="btn btn-primary btn-sm" id ="save_new_sum" type="button">
                                    <i class="fa fa-floppy-o" aria-hidden="true"></i>
                                </button>

                            </div>
                            <div class="col-sm-4"></div>
                        </div>
                    </div>
                <?php }?>
                <div class="container smeta_hide">
                    <div class="row" style="margin-bottom: 5px;">
                        <div class="col-sm-4"></div>
                        <div class="col-sm-4">
                            <h4 center> Получить смету на почту </h4>
                        </div>
                        <div class="col-sm-4"></div>
                    </div>
                </div>
                <div class="container smeta_hide">
                    <div class="row">
                        <div class="col-sm-4"></div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <input value="" id="send_email" name="jform[send_email]" class="form-control" placeholder="Введите ваш Email" type="email">
                            </div>
                        </div>
                        <div class="col-sm-4"></div>
                    </div>
                </div>
                <div class="container smeta_hide">
                    <div class="row sm-margin-bottom">
                        <div class="col-sm-4"></div>
                        <div class="col-sm-4">
                            <button class="btn btn-transparent" type="button" id="send_to_email">Получить подробную смету</button>
                        </div>
                        <div class="col-sm-4"></div>
                    </div>
                </div>
                <div class="container smeta_hide">
                    <div class="row">
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
            <!-- название расчета -->
            <div class="form-group under_calculate">
                <div class="container">
                    <div class="row">
                        <div class="col-sm-4"></div>
                        <div class="col-sm-4">
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
                        <div class="col-sm-4"></div>
                    </div>
                </div>
            </div>
            <?php if ($type === "gmcalculator" || $type === "calculator" || $api == 1)  { ?>
                <div class="container" id ="block_details">
                    <div class="row"  style="margin-bottom: 15px;">
                        <div class="col-sm-4"></div>
                        <div class="col-sm-4">
                            <table class="table_calcform">
                                <tr>
                                    <td class="td_calcform3">
                                        <button type="button" id="btn_details" data-cont_id="block_details" class="btn btn-primary" style="width: 100%;">Комментарий</button>
                                    </td>
                                </tr>
                            </table>
                            <input type="text" id="jform_details" name="jform[details]" value = "<?php echo $details;?>" class="form-control"  placeholder="Комментарий" style="display: none; margin-top: 20px; margin-bottom: 5px;">
                        </div>
                        <div class="col-sm-4"></div>
                    </div>
                </div>
            <?php } ?>
            <?php if($triangulator_pro == 1){?>
                <div class="container">
                    <div class="row" style="margin-bottom: 15px;">
                        <div class="col-sm-4"></div>
                        <div class="col-sm-4">
                            <label>Примечание менеджера</label>
                            <input type="text" id="jform_manager_note" name="jform[manager_note]" value = "" class="form-control"  placeholder="Комментарий" style="margin-top: 20px; margin-bottom: 5px;">
                        </div>
                        <div class="col-sm-4"></div>
                    </div>
                </div>
            <?php }?>
            <!-- кнопки -->
            <div class="container btn_tar">
                <div class="row sm-margin-bottom">
                    <div class="col-sm-4"></div>
                    <div class="col-sm-4">
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
                    <div class="col-sm-4"></div>
                </div>
            </div>
        </div>
        <div class="btn_api" style="width:100%; text-align:center;">
            <button class="btn btn-primary" type="button" id = "clear" style="display: none;">Очистить</button>
            <button class="btn btn-primary" type="button" id = "back_to_gm" style="display: none;">Вернуться</button>
            <button class="btn btn-primary" type="button" id = "show_rec" style="display: none;">Записаться на замер</button>
            <button class="btn btn-primary" type="button" id = "show_run" style="display: none;">Запустить в производство</button>
        </div>
</form>
<script>
    var user_id = "<?php echo $user_id;?>";
    var advt = "<?php echo $advt;?>"
    var texturesData = '<?php echo $texturesData?>';
    var isGmManager  = '<?php echo $triangulator_pro;?>';
    Function.prototype.process= function(state){
        var process= function(){
            var args= arguments;
            var self= arguments.callee;
            setTimeout(function(){
                self.handler.apply(self, args);
            }, 0);
        };
        for(var i in state)
        {
            process[i]= state[i];
        }
        process.handler= this;
        return process;
    };
    var calculation = JSON.parse('<?php echo json_encode($calculation);?>');
    var dealer_id = "<?php echo $user->dealer_id?>";
    var data;
    var n6_colors = JSON.parse('<?php echo $color_data;?>');
    var event_help = function(){
        let  help_buttons = document.getElementsByClassName('help');
        for(let i= help_buttons.length;i--;){
            help_buttons[i].onmouseenter = function(){
                jQuery(this.lastElementChild).show();
            };
            help_buttons[i].onmouseleave = function(){
                jQuery(this.lastElementChild).hide();
            };
        }
    };

    jQuery(document).mouseup(function (e){ // событие клика по веб-документу
        var div = jQuery("#modal_window_authorisation"); // тут указываем ID элемента
        var div1 = jQuery("#modal_window_rec_to_mesure");
        if (!div.is(e.target) && !div1.is(e.target) // если клик был не по нашему блоку
            && div.has(e.target).length === 0 && div1.has(e.target).length === 0) { // и не по его дочерним элементам
            jQuery("#mv_container").hide();
            jQuery("#modal_window_authorisation").hide();
            jQuery("#modal_window_rec_to_mesure").hide();
            jQuery("#close").hide();
        }
    });
    jQuery('document').ready(function()
    {
        jQuery("#jform_calculation_title").focusin(function(){
            jQuery("#jform_calculation_title").val("");
        });
        jQuery("#phone").mask("+7 (999) 999-99-99")
        var time_end,time_start = performance.now();
        if(user_id){
            jQuery("#fio_cont").hide();
            jQuery("#phone_cont").hide();
        }
        let api = "<?php echo $api;?>";
        let device = "<?php echo $device ?>";
        if (api == 1) {
            jQuery(".smeta_hide").hide();
            jQuery(".under_calculate").hide();
            jQuery(".btn_tar").hide();
            jQuery("#block_details").hide();
            jQuery(".btn_api").show();
            jQuery(".for_api").show();
            jQuery("#clear").show();
            jQuery("#show_rec").show();
            jQuery("#show_run").show();
            jQuery(".for_dealer").hide();
            jQuery("#jform_proizv-lbl").html('Производитель<br><a href="../../../../../files/Conclusion.pdf">Заключения</a>');
            jQuery("#btn_add_components").html('<img src="../../../../../images/screwdriver.png" class="img_calcform"> Дополнительные работы');

        } else {
            jQuery(".for_api").hide();
            jQuery(".for_dealer").show();

            jQuery("#btn_add_components").html('<img src="../../../../../images/screwdriver.png" class="img_calcform"> Добавить монтаж и комплектующие');
        }
        if (device == "web") {
            jQuery("#back_to_gm").show();
        }

        jQuery("#back_to_gm").click(function () {
            window.location.href = "http://гмпотолки.рф";
        });
        var seam = '<?php echo $seam; ?>';

        if (seam == '1')
        {
            jQuery("#close").show();
            jQuery("#mv_container").show();
            jQuery("#modal_window_seam").show("slow");
        }

        document.getElementById('hide_redactor').onclick = function()
        {
            jQuery("#close").hide();
            jQuery("#mv_container").hide();
            jQuery("#modal_window_kp").hide();
        };

        jQuery("#close_example").click(function () {
            jQuery(this).closest("span").hide();

        });

        if(document.getElementById('clear')){

            document.getElementById('clear').onclick = function(){
                jQuery.ajax({
                    type: 'POST',
                    url: '/index.php?option=com_gm_ceiling&task=calculation.clearCalculation',
                    dataType: "json",
                    timeout: 20000,
                    data: {
                        calc_id: calculation.id,
                        project_id: <?php echo $project_id; ?>
                    },
                    success: function(data){
                        location.reload();
                    },
                    error: function(data){
                        var n = noty({
                            theme: 'relax',
                            timeout: 2000,
                            layout: 'center',
                            maxVisible: 5,
                            type: "error",
                            text: "Ошибка сервера"
                        });
                    }
                });
            }
        }

        if(document.getElementById('save_new_sum')){
            document.getElementById('save_new_sum').onclick = function(){
                console.log(calculation.id,jQuery("#new_sum").val());
                jQuery.ajax({
                    type: 'POST',
                    url: '/index.php?option=com_gm_ceiling&task=calculation.updateSum',
                    dataType: "json",
                    timeout: 20000,
                    data: {
                        calcId: calculation.id,
                        sum: jQuery("#new_sum").val()
                    },
                    success: function(data){
                        jQuery("#final_price").text(jQuery("#new_sum").val());
                    },
                    error: function(data){
                        var n = noty({
                            theme: 'relax',
                            timeout: 2000,
                            layout: 'center',
                            maxVisible: 5,
                            type: "error",
                            text: "Ошибка сервера"
                        });
                    }
                });
            }
        }
        /*Rec to measure*/
        jQuery("#show_rec").click(function(){
            jQuery("#close").show();
            jQuery("#mv_container").show();
            jQuery("#rec_header").text("Заявка на запись на замер");
            jQuery("#modal_window_rec_to_mesure").show("slow");
            jQuery("#rec_date").show();
            jQuery("#rec_to_measure").attr("status",'1');
        });

        jQuery("#show_run").click(function(){
            jQuery("#close").show();
            jQuery("#mv_container").show();
            jQuery("#modal_window_rec_to_mesure").show("slow");
            jQuery("#rec_header").text("Заявка на запуск в производство");
            jQuery("#rec_date").hide();
            jQuery("#rec_to_measure").attr("status",'5');
        });

        jQuery("#rec_date").change(function(){
            jQuery.ajax({
                type: 'POST',
                url: '/index.php?option=com_gm_ceiling&task=api.getMeasureTimes',
                dataType: "json",
                timeout: 20000,
                data: {
                    date: {"date":this.value}
                },
                success: function(data){
                    data.forEach(function(item){
                        let option = jQuery("<option></option>")
                            .attr("value", item)
                            .text(item);
                        jQuery("#rec_time").append(option);
                    });
                    jQuery("#rec_time_container").show();
                },
                error: function(data){
                    var n = noty({
                        theme: 'relax',
                        timeout: 2000,
                        layout: 'center',
                        maxVisible: 5,
                        type: "error",
                        text: "Ошибка сервера"
                    });
                }
            });
        });
        jQuery("#rec_to_measure").click(function(){
            let address = `${jQuery("#address").val()} , дом: ${jQuery("#home").val()} , квартира:${jQuery("#appartment").val()}`;
            let fio = jQuery("#fio").val();
            let date_time = `${jQuery("#rec_date").val()} ${jQuery("#rec_time").val()}`;
            let phone = jQuery("#phone").val();
            let status  = jQuery(this).attr("status");
            dataForMeasure = {"user_id":user_id,"name":fio,"phone":phone,"address":address,"date_time":date_time,"advt":advt,"calc_id":calculation.id,"status":status};
            if(user_id){
                record_to_mesure(dataForMeasure);
            }
            else{
                check_user(phone);
            }

        });

        function record_to_mesure(data){
            let text = "Вы успешно записались на замер. В рабочее время с Вами свяжется менеджер для уточнения инормации!";
            if(data['status'] == 5){
                text = "Вы отправили заявку на запуск в производство. В рабочее время с Вами свяжется менеджер для уточнения инормации!";
            }
            jQuery.ajax({
                type: 'POST',
                url: '/index.php?option=com_gm_ceiling&task=api.recToMeasure',
                dataType: "json",
                timeout: 20000,
                data: {
                    rec_data: JSON.stringify(data)
                },
                success: function(data){
                    var n = noty({
                        theme: 'relax',
                        timeout: 2000,
                        layout: 'center',
                        maxVisible: 5,
                        type: "success",
                        text: text
                    });
                },
                error: function(data){
                    console.log(data);
                    var n = noty({
                        theme: 'relax',
                        timeout: 2000,
                        layout: 'center',
                        maxVisible: 5,
                        type: "error",
                        text: "Ошибка сервера"
                    });
                }
            });
        }

        function verify_password(id,pass){
            jQuery.ajax({
                type: 'POST',
                url: '/index.php?option=com_gm_ceiling&task=big_smeta.verify',
                dataType: "json",
                timeout: 20000,
                data: {
                    id: id,
                    pass:pass
                },
                success: function(result){
                    if(result.verification == true){
                        data["user_id"] = result.user_id;
                        record_to_mesure(data);
                    }
                    else{
                        var n = noty({
                            theme: 'relax',
                            timeout: 2000,
                            layout: 'center',
                            maxVisible: 5,
                            type: "error",
                            text: "Неверный пароль!"
                        });
                    }
                },
                error: function(data){
                    var n = noty({
                        theme: 'relax',
                        timeout: 2000,
                        layout: 'center',
                        maxVisible: 5,
                        type: "error",
                        text: "Ошибка сервера"
                    });
                }
            });
        }

        function check_user(phone){
            jQuery.ajax({
                type: 'POST',
                url: '/index.php?option=com_gm_ceiling&task=client.checkingUser',
                dataType: "json",
                timeout: 20000,
                data: {
                    phone: phone
                },
                async: false,
                success: function(data){
                    if(!data){
                        record_to_mesure(dataForMeasure);
                    }
                    else{
                        jQuery("#modal_window_rec_to_mesure").hide();
                        jQuery("#modal_window_authorisation").show();
                        jQuery("#rec_auth").attr("auth_data",data);
                    }
                },
                error: function(data){
                    console.log(data);
                    var n = noty({
                        theme: 'relax',
                        timeout: 2000,
                        layout: 'center',
                        maxVisible: 5,
                        type: "error",
                        text: "Ошибка сервера"
                    });
                }
            });
        }

        jQuery("#rec_auth").click(function(){
            verify_password(jQuery(this).attr('auth_data'),jQuery("#pass").val());
        });
        /*_________________*/
        var precalculation = '<?php echo $precalculation; ?>';
        jQuery("body").addClass("yellow_home");


        fill_calc_data();
        //var event_help_proccess = event_help.process();
        event_help();

        /* jQuery.each(canvases_data, function(key,value){
             //для отправки на чертилку
             let texture2 = {id:value.texture_id, title: value.texture_title};
             let manufacturer2 = {id:value.manufacturer_id,name:value.name};

             var textureExist = jQuery.map(texturesData,function(el,index){
                 if(el.texture.id == texture2.id)
                     return index;
             });
             if(!textureExist.length){
                 texturesData.push({texture:texture2,manufacturers:[manufacturer2]});
             }
             else{
                 var manufacturers = texturesData[textureExist[0]].manufacturers;
                 if(!obj_in_array(manufacturers,manufacturer2)){
                     manufacturers.push(manufacturer2);
                 }
             }


         });*/

        document.getElementById('cancel_button').onclick = function()
        {
            if (precalculation == '1')
            {
                jQuery.ajax({
                    type: 'POST',
                    url: '/index.php?option=com_gm_ceiling&task=calculationform.removeClientByProjectId',
                    dataType: "json",
                    timeout: 20000,
                    data: {
                        proj_id: <?php echo $project_id; ?>
                    },
                    success: function(data){
                        location.href = '/index.php?option=com_gm_ceiling&task=mainpage';
                    },
                    error: function(data){
                        var n = noty({
                            theme: 'relax',
                            timeout: 2000,
                            layout: 'center',
                            maxVisible: 5,
                            type: "error",
                            text: "Ошибка сервера"
                        });
                    }
                });
            }
            else
            {
                let url = '<?php echo $save_button_url;?>';
                location.href = url;
            }
        };
        //начертить
        jQuery("#sketch_switch").click(function(){
            jQuery("#walls").val("");
            jQuery("#auto").val("");
            submit_form_sketch();
        });
        //рассчитать
        jQuery("#calculate_button").click(function(){
            data = jQuery( "#form-calculation").serialize();
            console.log(data);
            jQuery("#under_calculate").show();
            var calculate_button = jQuery( this );
            let id = jQuery('#jform_id').val();
            let need_mount = jQuery("input[name = 'need_mount']").val();
            if(need_mount == undefined && api==1){
                need_mount = 1;
            }
            gm_mounters = "<?php echo $gm_mounters_url;?>"
            if (!calculate_button.hasClass("loading")) {
                calculate_button.addClass("loading");
                calculate_button.find("span.static").hide();
                calculate_button.find("span.loading").show();
                jQuery.ajax({
                    type: 'POST',
                    url: `index.php?option=com_gm_ceiling&task=calculate&save=1&pdf=1&del_flag=1&id=${id}&need_mount=${need_mount}${gm_mounters}`,
                    data: data,
                    success: function(data){
                        console.log(data);
                        if(api == 1){
                            jQuery("#sum_info").show();
                            jQuery('html, body').animate({
                                scrollTop: jQuery("#clear").offset().top
                            }, 2000);
                        }
                        var html = "",
                            total_sum = parseFloat(data.total_sum),
                            project_discount = parseFloat(data.project_discount);
                        if(project_discount == 0 && api==1){
                            project_discount = 50;
                        }
                        dealer_final = parseFloat(total_sum) * ((100 - parseFloat(project_discount)) / 100);
                        discount_price = parseFloat(total_sum) * (70 / 100);
                        mount_price  = parseFloat(data.mounting_sum);
                        discount_without  = parseFloat(total_sum - mount_price) * (70 / 100);
                        jQuery("#result_block").show();
                        jQuery("#total_price").text( total_sum.toFixed(0) );
                        if (api == 1) {
                            jQuery("#price_api").text( total_sum +" - 50% = ");
                            jQuery("#price_api").show();
                        }
                        jQuery("#final_price").text( dealer_final.toFixed(0) );
                        jQuery("#discount_price").text( discount_price.toFixed(0) );
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
        });

        //Запрос к серверу на отправку сметы на почту
        jQuery( "#send_to_email" ).click(function(){
            var reg = /^[-._a-z0-9]+@(?:[a-z0-9][-a-z0-9]+\.)+[a-z]{2,6}$/;
            if(reg.test(jQuery("#send_email").val())){
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


        jQuery(".to_redactor").click(function(){
            jQuery("#calc_id").val(calculation.id);
            jQuery("#proj_id").val(calculation.project_id);
            jQuery("#form_url").attr('action','sketch/cut_redactor_2/index.php');
            submit_form_sketch();
        });

        jQuery("#btn_add_components").click(function() {
            if (api == 1) {
                include('/components/com_gm_ceiling/views/calculationform2/JS/buttons_components_client.js?t=<?php echo time(); ?>');
            } else {
                if (jQuery('[data-parent = "btn_add_components"]').length < 1) {
                    include('/components/com_gm_ceiling/views/calculationform2/JS/buttons_components.js?t=<?php echo time(); ?>');
                } else {
                    jQuery('[data-parent = "btn_add_components"]').toggle();
                    /*var elems = jQuery('[data-parent]');
                    for (var i = elems.length, el; i--;) {
                        if (elems[i].getAttribute('data-parent') !== 'btn_add_components') {
                            jQuery(elems[i]).css('display','none');
                        }

                    }*/
                    jQuery('[data-parent = "basic_work"]').toggle();
                    jQuery('[data-parent = "light_cptn"]').toggle();
                    jQuery('[data-parent = "oter_mount_cptn"]').toggle();
                    jQuery('[data-parent = "need_mount"]').toggle();

                }
            }
            //setTimeout(event_help_proccess, 2000);
        });

        jQuery("#btn_details").click(function(){
            jQuery("#jform_details").toggle();
        });

        jQuery("#save_button").click(function(){
            let url = '<?php echo $save_button_url;?>';
            jQuery.ajax({
                type: 'POST',
                url: 'index.php?option=com_gm_ceiling&task=calculation.save_details',
                data: {
                    title: jQuery("#jform_calculation_title").val() ,
                    details: jQuery("#jform_details").val(),
                    manager_note: jQuery("#jform_manager_note").val(),
                    calc_id: calculation.id
                },
                success: function(data){
                    location.href = url;
                },
                error: function(data){
                    var n = noty({
                        theme: 'relax',
                        timeout: 2000,
                        layout: 'center',
                        maxVisible: 5,
                        type: "error",
                        text: "Ошибка при сохранении данных. Попробуйте позже"
                    });
                }
            });
        });
        function include(url) {
            let scripts = document.getElementsByTagName('script');
            let reg_exp = new RegExp(url);
            for(let i = scripts.length;i--;){
                if(reg_exp.test(scripts[i].src)){
                    return;
                }
            }
            var script = document.createElement('script');
            script.src = url;
            document.getElementsByTagName('head')[0].appendChild(script);
        }

        //если есть комплектующие раскрыть
        if(calculation.components_sum > 0 || calculation.need_mount > 0 || calculation.need_cuts > 0 || calculation.need_metiz > 0){
            jQuery("#btn_add_components").trigger("click");
        }

        function submit_form_sketch()
        {
            document.getElementById('n4').value = document.getElementById('jform_n4').value;
            document.getElementById('n5').value = document.getElementById('jform_n5').value;
            document.getElementById('n9').value = document.getElementById('jform_n9').value;
            if(calculation && calculation.original_sketch){
                document.getElementById('walls').value = calculation.original_sketch;
            }
            document.getElementById('texturesData').value = texturesData;
            console.log(jQuery("#texturesData").val());
            document.getElementById('form_url').submit();

        }

        function fill_calc_data(){
            if(calculation.n4 && calculation.n5 && calculation.n9){
                jQuery("#jform_n4").val(calculation.n4);
                jQuery("#jform_n5").val(calculation.n5);
                jQuery("#jform_n9").val(calculation.n9);
                jQuery("#jform_n10").val(calculation.n10);
                jQuery("#jform_n31").val(calculation.n31);
                jQuery("#jform_shrink_per").val(((1-calculation.shrink_percent).toFixed(2)*100).toFixed(2));
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

        document.body.onload = function(){
            jQuery('.PRELOADER_GM').hide();
        };

    });

    function puf() {
        let filename = '<?php echo $cut_img;?>';
        if (filename) {
            jQuery('#cut_image').attr('src', filename);
            jQuery('#input_cut_data').val(calculation.cut_data);
            jQuery('#input_shrink_percent').val(calculation.shrink_percent);
            jQuery('#jform_offcut_square').val(calculation.offcut_square);
            jQuery('#input_n5_shrink').val(calculation.n5_shrink);
            jQuery('#div_for_test').show();
        }
    }

</script>