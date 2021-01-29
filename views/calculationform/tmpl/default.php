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
/*header('Access-Control-Allow-Origin: https://гмпотолки.рф');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Max-Age: 1000');
header('Access-Control-Allow-Headers: Content-Type, Content-Range, Content-Disposition, Content-Description');*/
echo parent::getPreloaderNotJS();

$user_id = $jinput->get('user_id', '', "STRING");
if (!empty($user_id)) {
    $user = JFactory::getUser($user_id);
} else {
    $user = JFactory::getUser();
}
$user_groups = $user->groups;
$triangulator_pro = 0;
if (in_array('16', $user_groups)) {
    $triangulator_pro = 1;
    $gmManager = true;
    $min_sum = 200;
}

$type = $jinput->get('type', '', 'STRING');
$subtype = $jinput->get('subtype', '', 'STRING');
$precalculation = $jinput->get('precalculation', '', 'STRING');
$addition = $jinput->get('addition',0,'INT');
$seam = $jinput->get('seam', 0, 'INT');
$api = $jinput->get('api', 0, 'INT');
$device = $jinput->get('device', '', "STRING");
$lattitude = $jinput->get('latitude', '', "STRING");
$longitude = $jinput->get('longitude', '', "STRING");
$advt = $jinput->get('advt', '', "STRING");
$type_url = '';

if (in_array('16', $user_groups) && $subtype == 'production') {
    $gm_mounters = "service";
}

if (!empty($gm_mounters)) {
    $gm_mounters_url = "&gm_mounters=$gm_mounters";
}
if (!empty($type)) {
    $type_url = "&type=$type";
}

$subtype_url = '';
if (!empty($subtype)) {
    $subtype_url = "&subtype=$subtype";
}

$precalculation_url = '';
if (!empty($precalculation)) {
    $precalculation_url = "&precalculation=$precalculation";
}
$addition_url = '';
if (!empty($addition)) {
    $addition_url = "&addition=$addition";
}
$device_url = '';
if (!empty($device)) {
    $device_url = "&device=$device";
}

$api_url = '';
if (!empty($api)) {
    $api_url = "&api=$api";
}

$lattitude_url = '';
if (!empty($lattitude)) {
    $lattitude_url = "&latitude=$lattitude";
}

$longitude_url = '';
if (!empty($longitude)) {
    $longitude_url = "&longitude=$longitude";
}

$advt_url = '';
if (!empty($advt)) {
    $advt_url = "&advt=$advt";
}
$user_url = '';
if (!empty($user_id)) {
    $user_url = "&user_id=$user_id";
}
if ($api == 1) {
    $ll = (!empty($lattitude) && !empty($longitude)) ? "$lattitude;$longitude" : "";
    $details = "device: $device;$ll";
}

if ($type == "gmchief") {
    $view = "projectform";
} else {
    $view = "project";
}

/*____________________Models_______________________  */
$canvases_model = Gm_ceilingHelpersGm_ceiling::getModel("canvases");
$calculation_model = Gm_ceilingHelpersGm_ceiling::getModel("calculation");
$calculationformModel = Gm_ceilingHelpersGm_ceiling::getModel("calculationform");
$projectModel = Gm_ceilingHelpersGm_ceiling::getModel('project');
$stockModel = Gm_ceilingHelpersGm_ceiling::getModel('stock');
$usersModel = Gm_ceilingHelpersGm_ceiling::getModel('users');
/*____________________end_______________________  */
$fields = $calculationformModel->getFields(1);
$goods_jobs_map = json_encode($fields['goods_jobs_map']);
unset($fields['goods_jobs_map']);
$data = quotemeta(json_encode($fields, JSON_HEX_QUOT));
$componentsInCategories = quotemeta(json_encode($calculationformModel->getcomponentsInCategories(), JSON_HEX_QUOT));
$harpoon = $stockModel->getGoodsByCategory(10);

$texturesData = json_encode($canvases_model->getCanvasesTextures());
$calculation_id = $jinput->get('calc_id', 0, 'INT');
if (!empty($calculation_id)) {
    $calculation = $calculation_model->new_getData($calculation_id);
    $dealerId = $calculation->dealer_id;
    $dealer = JFactory::getUser($dealerId);
    $stretchCount = ($dealer->dealer_type == 7) ? $calculation->n4 : $calculation->n5;
    if (empty($calculation)) {
        throw new Exception("Расчет не найден", 1);
    }
    $canvas = null;
    foreach($calculation->goods as $goods){
        if($goods->category_id == 1){
            $canvas = $goods;
            break;
        }
    }
    /*$canvas = array_filter(
        $calculation->goods,
        function ($e) {
            return $e->category_id == 1;
        }
    );*/
    if (!empty($canvas)) {
        $filter = "id = ".$canvas->id;
        $detailed_canvas = $canvases_model->getFilteredItemsCanvas($filter);
        $filter = "texture_id = ".$detailed_canvas[0]->texture_id." and manufacturer_id = ".$detailed_canvas[0]->manufacturer_id." and color = ".$detailed_canvas[0]->color."  and visibility = 1";
        $selected_canvases = $canvases_model->getFilteredItemsCanvas($filter);
        $arr_widths = [];
        $widths = [];
        foreach ($selected_canvases as $value) {
            if (!in_array($value->width, $arr_widths)) {
                array_push($arr_widths, $value->width);
                array_push($widths, (object)array("id"=>$value->id,"width" => $value->width, "price" => $value->price));
            }
        }
        usort($widths, function ($a, $b) {
            if ($a->width < $b->width) {
                return 1;
            }
            if ($a->width > $b->width) {
                return -1;
            }
            return 0;
        });
        $widths = json_encode($widths);
        $color = $detailed_canvas[0]->color;
        $hex = $detailed_canvas[0]->hex;
    }
    $calculation->extra_components = addslashes($calculation->extra_components);
    $calculation->photo_print = addslashes($calculation->photo_print);
    $calculation->extra_mounting = addslashes($calculation->extra_mounting);
    $calculation->components_stock = addslashes(Gm_ceilingHelpersGm_ceiling::decode_stock($calculation->components_stock));
    $calculation->fields_data = addslashes(gzuncompress(base64_decode($calculation->fields_data)));
    $calc_img_filename = md5('calculation_sketch' . $calculation_id) . '.svg';
    if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/calculation_images/' . $calc_img_filename)) {
        $calc_img = '/calculation_images/' . $calc_img_filename . '?t=' . time();
    } else {
        $calc_img = '';
    }

    $cut_img_filename = md5('cut_sketch' . $calculation_id) . '.svg';
    if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/cut_images/' . $cut_img_filename)) {
        $cut_img = '/cut_images/' . $cut_img_filename . '?t=' . time();
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
$harpoon_option = '';
foreach ($harpoon as $item){
    $harpoon_option .= "<option value='$item->id'>$item->name</option>";
}
$ceiling_html = '<div class="row" style="margin-bottom: 5px; margin-top: 5px;">
                    <div class="col-sm-11 col-xs-11" style="padding-right: 5px;">
                        <button type="button"  class="btn add_fields">
                            <div class="col-xs-2 col-sm-2">
                                <img src="/images/stretch.png " class="img_calcform"></div>
                                <div class="col-xs-10 col-sm-10" style="text-align: left;">Натяжка полотна</div>
                        </button>
                    </div>
                    <div class="col-sm-1 col-xs-1" style="padding-left: 0px;">
                        <div class="btn-primary help" style="padding: 5px 10px; border-radius: 5px; height: 42px; width: 42px;">
                            <div class="help_question center" style="padding-top:2px;">?</div>
                            <span class="airhelp" style="display: none;">Количество работ по натяжке полотна</span>
                        </div>
                    </div>
                    <div class="div-fields" style="display: none;">
                        <div>
                            <div class="row title" style="margin-left: 15px; color: rgb(65, 64, 153);">
                                <label style="margin-left: 15px; margin-bottom: 2px; color: rgb(65, 64, 153);">Введите кол-во натяжки полотна</label>
                            </div>
                            <div class="col-md-12 col-xs-12 row-fields" data-id="stretch"  data-jobs=\'["26"]\'>
                                <div class="countDiv col-md-12">
                                    <input class="form-control quantity_input" value="'.$stretchCount.'">
                                </div>
                            </div>
                        </div>
                    </div>
                 </div>';
$n5_shrink = $calculation->n5*$calculation->shrink_percent;
$harpoon_html = '<div class="row" style="margin-bottom: 5px; margin-top: 5px;">
                    <div class="col-sm-11 col-xs-11" style="padding-right: 5px;">
                        <button type="button"  class="btn add_fields">
                            <div class="col-xs-2 col-sm-2">
                                <img src="/images/garpun.png " class="img_calcform"></div>
                                <div class="col-xs-10 col-sm-10" style="text-align: left;">Тип гарпуна</div>
                        </button>
                    </div>
                    <div class="col-sm-1 col-xs-1" style="padding-left: 0px;">
                        <div class="btn-primary help" style="padding: 5px 10px; border-radius: 5px; height: 42px; width: 42px;">
                            <div class="help_question center" style="padding-top:2px;">?</div>
                            <span class="airhelp" style="display: none;">Выбор типа гарпуна, который будет использован на данном полотне</span>
                        </div>
                    </div>
                    <div class="div-fields" style="display: none;">
                        <div>
                            <div class="row title" style="margin-left: 15px; color: rgb(65, 64, 153);">
                                <label style="margin-left: 15px; margin-bottom: 2px; color: rgb(65, 64, 153);">Выберите тип гарпуна</label>
                            </div>
                            <div class="col-md-12 col-xs-12 row-fields" data-id="harpoon"  data-jobs=\'[]\'>
                                <div class="countDiv col-md-2" style="display:none">
                                    <input class="form-control quantity_input" value="'.$n5_shrink.'">
                                </div>
                                <div class="col-md-12 col-xs-12 selectDiv"><select class="form-control goods_select">'.$harpoon_option.'</select></div>
                            </div>
                        </div>
                    </div>
                 </div>';
/*получаем список МС*/
$listOfMS = $usersModel->getUserByGroup('26');
?>
<style>
    .container {
        font-family: "Cuprum";
    }

    .col-lg, .col-lg-1, .col-lg-10, .col-lg-11, .col-lg-12, .col-lg-2, .col-lg-3, .col-lg-4, .col-lg-5, .col-lg-6, .col-lg-7, .col-lg-8, .col-lg-9, .col-md, .col-md-1, .col-md-10, .col-md-11, .col-md-12, .col-md-2, .col-md-3, .col-md-4, .col-md-5, .col-md-6, .col-md-7, .col-md-8, .col-md-9, .col-sm, .col-sm-1, .col-sm-10, .col-sm-11, .col-sm-12, .col-sm-2, .col-sm-3, .col-sm-4, .col-sm-5, .col-sm-6, .col-sm-7, .col-sm-8, .col-sm-9, .col-xl, .col-xl-1, .col-xl-10, .col-xl-11, .col-xl-12, .col-xl-2, .col-xl-3, .col-xl-4, .col-xl-5, .col-xl-6, .col-xl-7, .col-xl-8, .col-xl-9, .col-xs, .col-xs-1, .col-xs-10, .col-xs-11, .col-xs-12, .col-xs-2, .col-xs-3, .col-xs-4, .col-xs-5, .col-xs-6, .col-xs-7, .col-xs-8, .col-xs-9 {
        padding: 2px !important;
    }
    .no-border{
        border:0px;
    }
    .total_price{
        font-family: "Cuprum";
        color: #414099;
    }
    .category_title{
        font-size: 14pt;
        text-align: center;
        font-weight: bold;
        color: #FFFFFF;
        border-radius: 5px;
        background-color:#414099;
        margin: 5px 0 5px 0;
        line-height: 2.5em;
    }
    .main_groups{
       display: none;
    }
</style>
<?php if ($api == 1): ?>
    <style type="text/css">
        header {
            display: none;
        }

        footer {
            display: none;
        }
    </style>
<?php endif ?>
<div class="modal_window_container" id="mv_container">
    <button type="button" class="close_btn" id="close"><i class="fa fa-times fa-times-tar" aria-hidden="true"></i>
    </button>
    <div class="modal_window" id="modal_window_seam">
        <p>Потолок со швом. Изменить раскрой вручную?</p>
        <p>
            <button type="button" id="hide_redactor" class="btn btn-primary">Нет</button>
            <button type="button" id="show_redactor" class="btn btn-primary to_redactor">Да</button>
        </p>
    </div>

</div>
<!-- форма для чертилки-->
<form method="POST" action="/sketch/index.php" style="display: none" id="form_url">
    <input name="texturesData" id="texturesData" value="" type="hidden">
    <input name="texture" id="texture" value="<?php echo $canvas->texture_id ?>" type="hidden">
    <input name="color" id="color" value="<?php echo $canvas->color_id ?>" type="hidden">
    <input name="manufacturer" id="manufacturer" value="<?php echo $canvas->manufacturer_id ?>" type="hidden">
    <input name="walls" id="walls" value="" type="hidden">
    <input name="width" id="width" value='<?php echo $widths ?>' type="hidden">
    <input name="calc_id" id="calc_id" value="<?php echo $calculation_id; ?>" type="hidden">
    <input name="n4" id="n4" value="" type="hidden">
    <input name="n5" id="n5" value="" type="hidden">
    <input name="n9" id="n9" value="" type="hidden">
    <input name="triangulator_pro" id="triangulator_pro" value="<?php echo $triangulator_pro ?>" type="hidden">
    <input name="type_url" id="type_url" value="<?php echo $type_url; ?>" type="hidden">
    <input name="subtype_url" id="subtype_url" value="<?php echo $subtype_url; ?>" type="hidden">
    <input name="precalculation" id="precalculation" value="<?php echo $precalculation_url; ?>" type="hidden">
    <input name="addition" id="addition" value="<?php echo $addition_url; ?>" type="hidden">
    <input name="device" id="device" value="<?php echo $device_url; ?>" type="hidden">
    <input name="api" id="api" value="<?php echo $api_url; ?>" type="hidden">
    <input name="latitude" id="latitude" value="<?php echo $lattitude_url; ?>" type="hidden">
    <input name="longitude" id="longitude" value="<?php echo $longitude_url; ?>" type="hidden">
    <input name="advt" id="advt" value="<?php echo $advt_url; ?>" type="hidden">
    <input name="user_url" id="user_url" value="<?php echo $user_url; ?>" type="hidden">
</form>

<form id="form-calculation" action="<?php echo JRoute::_('index.php?option=com_gm_ceiling&task=calculation.save'); ?>"
      method="post" class="form-validate form-horizontal" enctype="multipart/form-data">
    <input id="jform_id" type="hidden" name="jform[id]" value="<?php echo $calculation_id; ?>"/>
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
        <div id="data-wrapper" style="display:none;">
            <div class="row sm-margin-bottom">
                <div class="col-sm-3"></div>
                <div class="col-sm-6 xs-center">
                    <table id = "common_info_table" style="width: 100%;">
                        <tr>
                            <td width=35%>
                                <label id="jform_texture-lbl" for="jform_n4"> Полотно: </label>
                            </td>
                            <td width=65%>
                                <input name="jform[canvas]" class="form-control-input no-border" style="width:100%" id="jform_canvas"
                                       value="<?php echo !empty($detailed_canvas[0])?$detailed_canvas[0]->name  :"";?>" data-next="#jform_proizv" readonly>
                            </td>
                        </tr>
                        <?php if (!empty($color)) { ?>
                            <tr>
                                <td width=35%>
                                    <label id="jform_color-lbl" for="jform_color"> Цвет: </label>
                                </td>
                                <td width=65%>
                                    <div class="col-md-3"><?=$color;?></div>
                                    <div class="col-md-9" style="background-color:<?="#".$hex;?>;color:<?="#".$hex;?>"><?=$color;?></div>
                                </td>
                            </tr>
                        <?php } ?>
                        <tr>
                            <td width=35%>
                                <label id="jform_color-lbl" for="jform_n4"> Площадь: </label>
                            </td>
                            <td width=55%>
                                <input name="jform[n4]" class="form-control-input no-border" id="jform_n4" data-next="#jform_n5"
                                       readonly>
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
                                <input name="jform[n5]" class="form-control-input no-border" id="jform_n5" data-next="#jform_n9"
                                       readonly>
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
                                <input name="jform[n9]" id="jform_n9" data-next="#jform_n27" class="form-control-input no-border"
                                       readonly>
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
                                <input name="jform[shrink_per]" id="jform_shrink_per" data-next="#jform_n27"
                                       class="form-control-input no-border" readonly>
                            </td>
                            <td width=10%>
                                <label for="jform_n9" class="control-label">%</label>
                            </td>
                        </tr>
                    </table>
                    <div id="div_for_test" style="display: none;">
                        <label>Площадь полотна:</label> <input id="input_camvas_area" type="text" readonly><br>
                        <label>Площадь обрезков:</label> <input name="jform[offcut_square]" id="jform_offcut_square" type="text" readonly><br>
                        <label>Координаты:</label> <textarea id="input_cut_data" style="width: 600px; height: 200px;" readonly resize></textarea><br>
                        <img id="cut_image" style="width: 100%;">
                        <label>Усаженный периметр:</label> <input id="input_n5_shrink" type="text" readonly><br>
                        <label>Площадь обрезков:</label> <input name="jform[offcut_square]" id="jform_offcut_square"
                                                                type="text" readonly><br>
                        <label>Процент усадки:</label> <input id="input_shrink_percent" type="text" readonly><br>
                        <label>Координаты:</label> <textarea id="input_cut_data" style="width: 600px; height: 200px;"readonly resize></textarea><br>
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
                        <button class="btn btn-primary to_redactor" type="button"
                                style="width: 100%; margin-bottom: 25px;"><i class="fas fa-edit" aria-hidden="true"></i>
                            Изменить раскрой
                        </button>
                    </div>
                    <div class="col-sm-3"></div>
                </div>
            </div>
        <?php } ?>
        <div class="container">
            <div class="row">
                <div class="col-md-3"></div>
                <div class="col-md-6">
                    <div class="row title">
                        <label style="margin-left: 15px; margin-bottom: 2px; color: rgb(65, 64, 153);">Введите высоту стен, м</label>
                    </div>
                    <div class="countDiv">
                        <input class="form-control" id="walls_height" value="<?=$calculation->walls_height;?>">
                    </div>
                </div>
                <div class="col-md-3"></div>
            </div>
        </div>
        <div class="container">
            <div class="row">
                <div class="col-md-3"></div>
                <div class="col-md-6">
                    <div class="div-fields">
                        <div class="row title" style="margin-left: 5px; color: rgb(65, 64, 153);">
                            <label style="margin-bottom: 2px; color: rgb(65, 64, 153);">Добавьте размеры проемов</label>
                        </div>
                        <div class="row center title" style="margin-left: 15px; color: rgb(65, 64, 153);">
                            <div class="countDiv col-md-3 col-xs-4">
                                <label style="margin-bottom: 2px; color: #414099;">Высота,см</label>
                            </div>
                            <div class="countDiv col-md-3 col-xs-4">
                                <label style="margin-bottom: 2px; color: #414099;">Ширина,см</label>
                            </div>
                            <div class="col-md-4 col-xs-4">
                                <label style="margin-bottom: 2px; color: #414099;">Тип</label>
                            </div>
                            <div class="col-md-2 col-xs-2">
                                <i class="fa fa-trash-alt" aria-hidden="true"></i>
                            </div>
                        </div>
                        <div class="col-md-12 col-xs-12 row-fields openings" data-id="opening_fields" style="margin-bottom: 5px;">
                            <div class="countDiv col-md-3 col-xs-4" style="padding-right: 0px;">
                                <input class="form-control quantity_input height" placeholder="Высота">
                            </div>
                            <div class="countDiv col-md-3 col-xs-4" style="padding-right: 0px;">
                                <input class="form-control quantity_input width" placeholder="Ширина">
                            </div>
                            <div class="col-md-4 col-xs-4">
                                <select class="form-control opening_select">
                                    <option value="1">Оконный</option>
                                    <option value="2">Дверной</option>
                                </select>
                            </div>
                            <div class="col-md-2 col-xs-2">
                                <button class="clear_form_group btn btn-danger delete_goods" type="button">
                                    <i class="fa fa-trash" aria-hidden="true"></i>
                                </button>
                            </div>
                        </div>
                        <div class="row center">
                            <button class="btn btn-primary add" type="button" data-field="opening_fields" style="margin-bottom: 15px;">
                                <i class="fa fa-plus" aria-hidden="true"></i> Добавить</button>
                        </div>
                    </div>
                </div>
                <div class="col-md-3"></div>
            </div>
        </div>
        <div id="params_block">
           <!-- <div class="row" id="cancel_maingroup">
                <div class="col-sm-3"></div>
                <div class="col-sm-6">
                    <button class="btn btn_calc" type="button" data-maingroup_id="cancel">
                        <div class="col-xs-11">
                            <b>Отменить</b>
                        </div>
                        <div class="col-xs-1">
                            <i class="fa fa-angle-down" style="color: #414099;"></i>
                        </div>
                    </button>
                    <div class="inner_container" style="display: none;">
                        <div class="row" style="margin-bottom: 5px; margin-top: 5px;">
                            <div class="col-sm-11 col-xs-11" style="padding-right: 5px;">
                                <button type="button" data-group_id="cancel_metiz" data-maingroup_id="cancel"
                                        class="btn add_fields">
                                    <div class="col-xs-2 col-sm-2">
                                        <img src="/images/cancel_metiz.png " class="img_calcform">
                                    </div>
                                    <div class="col-xs-10 col-sm-10" style="text-align: left;">Отменить метизы</div>
                                </button>
                            </div>
                            <div class="col-sm-1 col-xs-1" style="padding-left: 0px;">
                                <div class="btn-primary help"
                                     style="padding: 5px 10px; border-radius: 5px; height: 42px; width: 42px;">
                                    <div class="help_question center" style="padding-top:2px;">?</div>
                                    <span class="airhelp" style="display: none;">При выборе данной опции отменяются все метизы</span>
                                </div>
                            </div>
                            <div class="div-fields" style="display: none;">
                                <div>
                                    <div class="col-sm-12 row-fields center" data-id="is_cancel_metiz"
                                         data-group_id="cancel_metiz"
                                         style="margin-bottom: 5px;">
                                        <div class="countDiv">
                                            <input type="checkbox" id="fieldis_cancel_metiz" class="inp-cbx" style="display: none;">
                                            <label for="fieldis_cancel_metiz" class="cbx">
                                                <span>
                                                    <svg width="12px" height="10px" viewBox="0 0 12 10">
                                                        <polyline points="1.5 6 4.5 9 10.5 1"></polyline>
                                                    </svg>
                                                </span>
                                                <span>Отменить метизы</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row" style="margin-bottom: 5px; margin-top: 5px;">
                            <div class="col-sm-11 col-xs-11" style="padding-right: 5px;">
                                <button type="button" data-group_id="cancel_mount" data-maingroup_id="cancel" class="btn add_fields">
                                    <div class="col-xs-2 col-sm-2">
                                        <img src="/images/cancel_mount.png " class="img_calcform">
                                    </div>
                                    <div class="col-xs-10 col-sm-10" style="text-align: left;">Отменить монтаж</div>
                                </button>
                            </div>
                            <div class="col-sm-1 col-xs-1" style="padding-left: 0px;">
                                <div class="btn-primary help"style="padding: 5px 10px; border-radius: 5px; height: 42px; width: 42px;">
                                    <div class="help_question center" style="padding-top:2px;">?</div>
                                    <span class="airhelp" style="display: none;">
                                        При выборе опции "Свой прайс" монтажные работы считаются по Вашему прайсу монтажа, при выборе опции "Монтадная служба" работы считаются по прайсу монтажной службы ГМ, при выборе опции "Без монтажа" монтажные работы не будут посчитаны
                                    </span>
                                </div>
                            </div>
                            <div class="div-fields" style="display: none;">
                                <div>
                                    <div class="col-sm-12 row-fields" data-id="without_mount" data-group_id="cancel_mount" data-jobs="[]" style="margin-bottom: 5px;">
                                        <div class="countDiv">
                                            <input type="radio" data-id="without_mount" id="without_mount" name="cancel_mount" class="radio" value="0" <?/*= $gmManager ? 'checked' : '' */?>>
                                            <label for="without_mount">Без монтажа</label>
                                        </div>
                                    </div>
                                    <div class="col-sm-12 row-fields" data-id="mount_service"
                                         data-group_id="cancel_mount" data-jobs="['need_mount']"
                                         style="margin-bottom: 5px;">
                                        <div class="countDiv"><input type="radio" data-id="mount_service"
                                                                     id="mount_service" name="cancel_mount"
                                                                     class="radio" data-count="2" <?/*= !$gmManager ? 'checked' : '' */?>
                                                                     value="2">
                                            <label for="mount_service">Монтажная служба</label></div>
                                    </div>
                                    <div class="col-sm-12 row-fields" data-id="self_mount" data-group_id="cancel_mount"
                                         data-jobs="['need_mount']" style="margin-bottom: 5px;" checked>
                                        <div class="countDiv">
                                            <input type="radio" data-id="self_mount" id="self_mount"
                                                   name="cancel_mount" class="radio" data-count="1"
                                                   value="1" <?/*=$dealer->dealer_type == 7 ? 'checked':''*/?>>
                                            <label for="self_mount">Свой прайс</label>
                                        </div>
                                    </div>
                                </div>
                            </div>


                        </div>
                        <?php /*if($gmManager){*/?>
                            <div class="row" style="margin-bottom: 5px; margin-top: 5px;">
                                <div class="col-sm-11 col-xs-11" style="padding-right: 5px;">
                                    <button type="button" data-group_id="cancel_offcut" data-maingroup_id="cancel"
                                            class="btn add_fields">
                                        <div class="col-xs-2 col-sm-2"><img src="/images/offcut.png " class="img_calcform">
                                        </div>
                                        <div class="col-xs-10 col-sm-10" style="text-align: left;">Отменить обрезки</div>
                                    </button>
                                </div>
                                <div class="col-sm-1 col-xs-1" style="padding-left: 0px;">
                                    <div class="btn-primary help"
                                         style="padding: 5px 10px; border-radius: 5px; height: 42px; width: 42px;">
                                        <div class="help_question center" style="padding-top:2px;">?</div>
                                        <span class="airhelp" style="display: none;">При выборе данной опции отменяются обрезки</span>
                                    </div>
                                </div>
                                <div class="div-fields" style="display: none;">
                                    <div>
                                        <div class="col-sm-12 row-fields center" data-id="is_cancel_offcut"
                                             data-group_id="cancel_offcut" style="margin-bottom: 5px;">
                                            <div class="countDiv">
                                                <input type="checkbox" id="fieldis_cancel_offcut" class="inp-cbx" style="display: none;">
                                                <label for="fieldis_cancel_offcut" class="cbx">
                                                    <span>
                                                        <svg width="12px" height="10px" viewBox="0 0 12 10">
                                                            <polyline points="1.5 6 4.5 9 10.5 1"></polyline>
                                                        </svg>
                                                    </span>
                                                    <span> Отменить обрезки</span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php /*}*/?>
                    </div>
                </div>
                <div class="col-sm-3"></div>
            </div>-->
        </div>
        <!-- Рассчитать -->
        <div class="container">
            <div class="row sm-margin-bottom">
                <div class="col-sm-3"></div>
                <div class="col-sm-6 pull-center">
                    <h3>Процент скидки</h3>
                    <input name="jform[discount]" id="new_discount" class="form-control" placeholder="Введите %"
                           type="number" max="100" min="0" value="<?php echo $calculation->discount; ?>">
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
        <div class="container" id="sum_info" style="display:none">
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
                <?php if ($triangulator_pro) { ?>
                    <div class="container" id="new_sum_container">
                        <div class="row">
                            <div class="col-sm-3"></div>
                            <div class="col-sm-6 center">
                                <label for="new_sum">
                                    Введите новую сумму
                                </label><br>
                                <input type="tel" id=new_sum class="input-gm">
                                <button class="btn btn-primary btn-sm" id="save_new_sum" type="button">
                                    <i class="fas fa-save" aria-hidden="true"></i>
                                </button>

                            </div>
                            <div class="col-sm-3"></div>
                        </div>
                    </div>
                <?php } ?>
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
                                        <label id="jform_calculation_title-lbl" for="jform_calculation_title" class="">Название
                                            расчета:</label>
                                    </td>
                                    <td class="td_calcform2">
                                        <div class="btn-primary help"
                                             style="padding: 5px 10px; border-radius: 5px; height: 42px; width: 42px; margin-left: 5px;">
                                            <div class="help_question center" style="padding-top:2px;">?</div>
                                            <span class="airhelp">
													Назовите чертеж, по названию комнаты, в которой производится замер, что бы легче было потом ориентироваться. Например: "Спальня".
												</span>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                            <input id="jform_calculation_title" name="jform[calculation_title]" class="form-control"
                                   type="text">
                        </div>
                        <div class="col-sm-3"></div>
                    </div>
                </div>
            </div>
            <?php if ($type === "gmcalculator" || $type === "calculator" || $api == 1) { ?>
                <div class="container" id="block_details">
                    <div class="row" style="margin-bottom: 15px;">
                        <div class="col-sm-3"></div>
                        <div class="col-sm-6">
                            <table class="table_calcform">
                                <tr>
                                    <td class="td_calcform3">
                                        <button type="button" id="btn_details" data-cont_id="block_details"
                                                class="btn btn-primary" style="width: 100%;">Комментарий
                                        </button>
                                    </td>
                                </tr>
                            </table>
                            <input type="text" id="jform_details" name="jform[details]" value="<?php echo $details; ?>"
                                   class="form-control" placeholder="Комментарий"
                                   style="display: none; margin-top: 20px; margin-bottom: 5px;">
                        </div>
                        <div class="col-sm-3"></div>
                    </div>
                </div>
            <?php } ?>
            <?php if ($triangulator_pro == 1) { ?>
                <div class="container">
                    <div class="row" style="margin-bottom: 15px;">
                        <div class="col-sm-3"></div>
                        <div class="col-sm-6">
                            <label>Примечание менеджера</label>
                            <input type="text" id="jform_manager_note" name="jform[manager_note]" value=""
                                   class="form-control" placeholder="Комментарий"
                                   style="margin-top: 20px; margin-bottom: 5px;">
                        </div>
                        <div class="col-sm-3"></div>
                    </div>
                </div>
            <?php } ?>
            <!-- кнопки -->
            <div class="container btn_tar">
                <div class="row sm-margin-bottom">
                    <div class="col-sm-3"></div>
                    <div class="col-sm-6">
                        <table style="width:100%; text-align: center;">
                            <tr>
                                <td style="text-align: center;">
                                    <button id="save_button" type="button" class="btn btn-success">Сохранить</button>
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
        <div class="modal_window_container" id="mv_container">
            <button type="button" class="close_btn" id="close"><i class="fa fa-times fa-times-tar" aria-hidden="true"></i></button>
            <div class="modal_window" id="modal_window_seam">
                <p>Потолок со швом. Изменить раскрой вручную?</p>
                <p><button type="button" id="hide_redactor" class="btn btn-primary">Нет</button>
                    <button type="button" id="show_redactor" class="btn btn-primary to_redactor">Да</button></p>
            </div>
        </div>

</form>

<script type="text/javascript">
    var calculation = JSON.parse('<?php echo quotemeta(json_encode($calculation));?>'),
        dealerId = '<?php echo $dealerId;?>',
        texturesData = '<?php echo $texturesData?>',
        precalculation = '<?php echo $precalculation; ?>',
        gmManager = '<?= $gmManager?>',
        seam = '<?php echo $seam; ?>',
        goodsJobsMap = JSON.parse('<?=$goods_jobs_map?>'),
        dealerType = '<?= $dealer->dealer_type;?>',
        listOfNMS = JSON.parse('<?= json_encode($listOfMS)?>'),
        openingData,
        mountServiceOptions = [
            {
                id: "without_mount",
                group_id: "cancel_mount",
                goods_category_id: null,
                parent:null,
                goods: [],
                value: 0,
                duplicate: "0",
                input_type : "2",
                title: "Без монтажа"
            },
            {
                id: "self_mount",
                group_id: "cancel_mount",
                goods_category_id: null,
                parent:null,
                goods: [],
                value: 1,
                duplicate: "0",
                input_type : "2",
                title: "Свой прайс"
            }
        ];
    if(listOfNMS.length){
        for(var i = 0; i<listOfNMS.length;i++){
            mountServiceOptions.push(
                {
                    id: "mount_service_"+listOfNMS[i].id,
                    group_id: "cancel_mount",
                    goods_category_id: null,
                    parent:null,
                    goods: [],
                    value: listOfNMS[i].id,
                    duplicate: "0",
                    input_type : "2",
                    title: listOfNMS[i].name
                }
            );
        }
    }
    console.log('g-j MAP', goodsJobsMap);
    console.log("dealer",dealerId);
    var DEFAULT_MAINGROUPS = [
        {
            id: "guild_works",
            title: "Работы в цеху",
            groups: [
                {
                    title: "Фотопечать",
                    description: "В расчет включается стоимость фотопечати",
                    id: "photo_print",
                    main_group_id: "guild_works",
                    icon: "/images/photoprint.png",
                    fields: [
                        {
                            id: "photoprint",
                            group_id: "photo_print",
                            goods_category_id: null,
                            parent: null,
                            goods: [],
                            jobs: [],
                            duplicate: "0",
                            input_type: "4",
                            title: "Фотопечать",
                            subfields: [
                                {
                                    id: "print_square",
                                    title: "Площадь"
                                },
                                {
                                    id: "print_cost",
                                    title: "Стоимость"
                                }
                            ],
                        }
                    ]
                },
                {
                    title: "Обработка углов",
                    description: "В расчет включается стоимость обработки углов",
                    id: "angle_processing",
                    main_group_id: "guild_works",
                    icon: "/images/angle.png",
                    fields: [
                        {
                            id: "angle_count",
                            group_id: "angle_processing",
                            goods_category_id: null,
                            parent: null,
                            goods: [],
                            jobs: [],
                            duplicate: "0",
                            input_type: "0",
                            title: "Обработка углов"
                        }
                    ]
                },
                {
                    title: "Перегарпунка",
                    description: "В расчет включается стоимость перегарпунки",
                    id: "reharp",
                    main_group_id: "guild_works",
                    icon: "/images/garpun.png",
                    fields: [
                        {
                            id: "reharp_count",
                            group_id: "reharp",
                            goods_category_id: null,
                            parent: null,
                            goods: [],
                            jobs: [],
                            duplicate: "0",
                            input_type: "0",
                            title: "Перегарпунка"
                        }
                    ]
                }
            ]
        },
        {
            id: "cancel",
            title: "Отменить",
            groups:[
                {
                    title: "Отменить метизы",
                    description: "При выборе данной опции отменяются все метизы",
                    id: "cancel_metiz",
                    main_group_id: "cancel",
                    icon: "/images/cancel_metiz.png",
                    fields:[
                        {
                            id: "is_cancel_metiz",
                            group_id: "cancel_metiz",
                            goods_category_id: null,
                            parent:null,
                            goods: [],
                            jobs: [
                                {
                                    id:"cancel_metiz"
                                }
                            ],
                            duplicate: "0",
                            input_type : "1",
                            title: "Отментить метизы"

                        }
                    ]
                },
                {
                    title: "Отменить монтаж",
                    description: 'При выборе опции "Свой прайс" монтажные работы считаются по Вашему прайсу монтажа, ' +
                        'при выборе опции "Монтадная служба" работы считаются по прайсу монтажной службы , при выборе опции "Без монтажа" монтажные работы не будут посчитаны',
                    id: "cancel_mount",
                    main_group_id: "cancel",
                    icon: "/images/cancel_mount.png",
                    fields: mountServiceOptions
                },
                {
                    title: "Отменить обрезки",
                    description: "При выборе данной опции отменяются обрезки",
                    id: "cancel_offcut",
                    main_group_id: "cancel",
                    icon: "/images/offcut.png",
                    fields:[
                        {
                            id: "is_cancel_offcut",
                            group_id: "cancel_offcut",
                            goods_category_id: null,
                            parent:null,
                            goods: [],
                            jobs: [
                                {
                                    id:"cancel_offcuts"
                                }
                            ],
                            duplicate: "0",
                            input_type : "1",
                            title: "Отментить обрезки"
                        }
                    ]
                },
            ]
        }

        ],
        DEFAULT_FIELDS = [
            {
                title: "Другие работы по монтажу",
                description: "В расчет включается допалнительные работы",
                id: "dop_mount",
                main_group_id: "4",
                icon: "/images/hammer.png",
                fields: [
                    {
                        id: "dop_works",
                        input_type: "4",
                        title: "Дополнительные монтажные работы",
                        goods_category_id: null,
                        parent: null,
                        group_id: "dop_mount",
                        jobs: [],
                        duplicate: "1",
                        subfields: [
                            {
                                id: "work_title",
                                title: "Название"
                            },
                            {
                                id: "work_cost",
                                title: "Стоимость"
                            }
                        ],



                    }
                ]
            },
            {
                title: "Другие комплектующие",
                description: "В расчет включается стоимость дополнительных компонентов",
                id: "dop_components",
                main_group_id: "4",
                icon: "/images/drcomplect.png",
                fields: [
                    {
                        id:"dop_components",
                        input_type: "4",
                        title: "Дополнительные комплектующие",
                        goods_category_id: null,
                        parent: null,
                        group_id: "dop_components",
                        jobs: [],
                        duplicate: "1",
                        subfields: [
                            {
                                id: "component_title",
                                title: "Название"
                            },
                            {
                                id: "component_cost",
                                title: "Стоимость"
                            }
                        ]
                    }
                ]
            },
            {
                title: "Доп.комплектующие со склада",
                description: "В расчет включается стоимость дополнительных компонентов со склада",
                id: "dop_goods",
                main_group_id: "4",
                icon: "/images/drcomplect.png",
                fields: [
                    {
                        id: "dopgoods",
                        group_id: "dop_goods",
                        goods_category_id: null,
                        parent: null,
                        goods: [],
                        jobs: [],
                        duplicate: "0",
                        input_type: "3",
                        title: "Выберите категорию"
                    }
                ]
            }
            ]
    ,
    harpoon_field = `<?= $harpoon_html;?>`,
    ceiling_field = `<?= $ceiling_html;?>`;
    var componentsInCategories;

    function createAdditionalWorkFields(specJobs,select) {
        if (!empty(specJobs)&&!empty(select)) {
            jQuery.each(specJobs, function (n, specJob) {
                var divRow = jQuery(document.createElement('div')),
                    titleDiv = jQuery(document.createElement('div')),
                    countDiv = jQuery(document.createElement('div')),
                    label = jQuery(document.createElement('label')),
                    input = createInput();
                input.addClass('quantity_input');

                titleDiv.addClass('row title');
                titleDiv.css({"margin-left": "15px", "color": "#414099"})
                label.css({"margin-left": "15px", "margin-bottom": "2px", "color": "#414099"})
                label.html(specJob.name);
                titleDiv.append(label);
                titleDiv.attr('data-for_value', select.value);

                divRow.addClass('col-xs-12 col-md-12 row-fields');
                divRow.attr('data-jobs', "[" + specJob.id + "]");
                divRow.attr('data-for_value', select.value);
                divRow.attr('data-id','specJob'+specJob.id);
                countDiv.addClass('col-sm-12 col-xs-12 countDiv');
                countDiv.append(input);
                divRow.append(titleDiv);
                divRow.append(countDiv);
                jQuery(select).closest('.row-fields').after(divRow);
            });
        }
    }

    jQuery(document).ready(function () {

        if (seam == '1')
        {
            jQuery("#close").show();
            jQuery("#mv_container").show();
            jQuery("#modal_window_seam").show("slow");
        }

        jQuery('#hide_redactor').click(function(){
            jQuery("#close").hide();
            jQuery("#mv_container").hide();
            jQuery("#modal_window_kp").hide();
        });

        var data = JSON.parse('<?php echo $data?>');
        componentsInCategories = JSON.parse('<?php echo $componentsInCategories?>');
        if(!gmManager){
            DEFAULT_MAINGROUPS.shift();
        }
        data[0].main_groups = data[0].main_groups.concat(DEFAULT_MAINGROUPS);
        jQuery.each(DEFAULT_FIELDS,function(index,field){
            var insert_index = data[0].main_groups.findIndex(function (elem) {
                return elem.id == field.main_group_id;
            });
            data[0].main_groups[insert_index].groups.push(field);
        });
        console.log("data", data);
        createCategories(data);
        createBlocks(data);
        document.body.onload = function () {
            jQuery('.PRELOADER_GM').hide();
        };
        jQuery('.btn_calc[data-maingroup_id="4"]').parent().find('.inner_container').prepend(ceiling_field);
        jQuery('.btn_calc[data-maingroup_id="4"]').parent().find('.inner_container').prepend(harpoon_field);

        jQuery('.col-sm-6').on('mouseenter', '.help', function () {
            jQuery(this.lastElementChild).show();
        });

        jQuery('.col-sm-6').on('mouseleave', '.help', function () {
            jQuery(this.lastElementChild).hide();
        });

        jQuery('body').on('click', '.btn_calc', function () {
            var container = jQuery(this).closest('.col-sm-6').find('.inner_container'),
                angle = jQuery(this).find('i');
            container.toggle();
            if(container.is(':visible')){
                angle.removeClass('fa-angle-down');
                angle.addClass('fa-angle-up');
            }
            else{
                angle.removeClass('fa-angle-up');
                angle.addClass('fa-angle-down');
            }
        });

        jQuery('body').on('click', '.add', function () {
            var parent = jQuery(this).parent(),
                rowFields = jQuery(this).closest('.div-fields').find('.row-fields[data-id="'+jQuery(this).data('field')+'"]').first().clone(),
                prev = parent.prev(),
                count, radioName = '',
                lastRadioName = jQuery(prev.find('input[type=radio]')[0]).prop('name');
            if (!empty(lastRadioName)) {
                var splittedName = lastRadioName.split('_');
                radioName = splittedName[0];
                count = splittedName[1];
            }
            jQuery.each(rowFields, function (index, elem) {
                var radios = jQuery(elem).find('input[type=radio]'),
                    labels = jQuery(elem).find('label'),
                    countDivs = jQuery(elem).find('.countDiv');
                jQuery.each(countDivs,function(n,el){
                    jQuery(el).children()[0].value = "";
                });
                //jQuery(elem).find('.countDiv').children()[0].value = "";
                jQuery(elem).find('.div-goods_select').hide();
                jQuery.each(radios, function (ind, radioBtn) {
                    var id = jQuery(radioBtn).prop('id') + "_" + count;
                    jQuery(radioBtn).prop('id', id);
                    jQuery(radioBtn).prop('name', radioName + "_" + (+count + 1));
                    jQuery(radioBtn).prop('checked',false);
                });
                jQuery.each(labels, function (ind, label) {
                    var propFor = jQuery(label).prop('for') + "_" + count;
                    jQuery(label).prop('for', propFor);
                });
            });
            parent.before(rowFields);
        });

        jQuery('#params_block').on('click','.category_title',function () {
            var categoryRow = jQuery(this).closest('.category'),
                categoryId = categoryRow.data('id'),
                mainGroupsContainer = jQuery('.main_groups[data-category="'+categoryId+'"]'),
                angle = categoryRow.find('i');
            mainGroupsContainer.toggle();
            if(mainGroupsContainer.is(':visible')){
                angle.removeClass('fa-angle-down');
                angle.addClass('fa-angle-up');
            }
            else{
                angle.removeClass('fa-angle-up');
                angle.addClass('fa-angle-down');
            }
        });

        jQuery('body').on('input','.quantity_input',function () {
            jQuery(this).val(jQuery(this).val().replace(/\,/g, '.'));
           })
        jQuery('body').on('blur','.quantity_input',function () {
            var value = jQuery(this).val(),
                regExp = /^[0-9]*[.]?[0-9]+([-][0-9]*[.]?[0-9]+)*$/g,
                newValue;
            if(!empty(value)){
                if(regExp.test(value)){
                    jQuery(this).css('border-color','');
                    newValue = eval(value).toFixed(2);
                    jQuery(this).val(newValue);
                }
                else{
                    noty({
                        timeout: 2000,
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "error",
                        text: "Проверьте введенные данные! Допустимый формат для ввода X.XX - X.XX - X.XX "
                    });
                    jQuery(this).css('border-color','red');
                }
            }
            else{
                jQuery(this).css('border-color','');
            }
        });

        jQuery('body').on('click', '.delete_goods', function () {
            var parent = jQuery(this).closest('.row-fields'),
                prevRow = parent.prev(),
                nextRow = parent.next();
            var row = jQuery(this).closest('.row-fields');
            if (prevRow.hasClass('row-fields') || nextRow.hasClass('row-fields')) {
                var select = jQuery('.goods_select',row);
                console.log(select);
                if(!empty(select)){
                    jQuery('.row-fields[data-for_value="'+select.val()+'"]').remove();
                }
                row.remove();
            }
        });

        jQuery('body').on('click', '.add_fields', function () {
            var div_fields = jQuery(this).closest('.row').find('.div-fields');
            div_fields.toggle();
            if(jQuery(this).data('group_id') == 1 ){
                jQuery(div_fields.find('.countDiv')[0]).children().val(calculation.n5);
            }
            if(jQuery(this).data('group_id') == 2){
                if(dealerType != 7){
                    jQuery(div_fields.find('.countDiv')[0]).children().val(+calculation.n5+0.5);
                }
                else{
                    jQuery(div_fields.find('.countDiv')[0]).children().val(calculation.n5)
                }
            }
            /*подставляем площадь проемов*/
            if(jQuery(this).data('group_id') == 35){
                openingData = getOpeningsCommonValues();
                let height = empty(jQuery('#walls_height').val()) ? 0 : jQuery('#walls_height').val(),
                    square = height*calculation.n5 - openingData.square;

                if(square<0){
                    jQuery(div_fields.find('.countDiv')[0]).children().val(0);
                }
                else{
                    jQuery(div_fields.find('.countDiv')[0]).children().val(square);
                }
            }
        });

        jQuery('body').on('click', 'input[type="radio"]', function () {
            var selectDiv = jQuery(this).closest('.row-fields').find('.div-goods_select'),
                relatedInput = jQuery(this).closest('.row-fields').find('.div-jobs');
            if (this.checked) {
                var goodsSelects = jQuery(this).closest('.div-fields').find('.div-goods_select'),
                    jobsInput = jQuery(this).closest('.div-fields').find('.div-jobs');
                jQuery.each(goodsSelects, function (index, elem) {
                    var parent = jQuery(elem).parent(),
                        relatedRadio = jQuery(parent).find('input[type=radio]');
                    if (!relatedRadio.prop('checked')) {
                        jQuery(elem).hide();
                    }
                });
                jQuery.each(jobsInput, function (index, elem) {
                    var parent = jQuery(elem).parent(),
                        relatedRadio = jQuery(parent).find('input[type=radio]');
                    if (!relatedRadio.prop('checked')) {
                        jQuery(elem).hide();
                    }
                });
                if (!empty(selectDiv)) {
                    selectDiv.show();
                }
                if(!empty(relatedInput)){
                    relatedInput.toggle();
                }
            }
        });

        jQuery('body').on('click', '.duplicate_extra_goods', function () {
            var rowToClone = jQuery(this).closest('.row-fields'),
                clonedRow = rowToClone.clone();
            rowToClone.after(clonedRow);
        });

        jQuery('body').on('change', '[name="choose_category"]', function () {
            var goods = getGoodsByCategory(this.value),
                divRow = jQuery(document.createElement('div')),
                countDiv = jQuery(document.createElement('div')),
                selectDiv = jQuery(document.createElement('div')),
                duplicateDiv = jQuery(document.createElement('div')),
                deleteDiv = jQuery(document.createElement('div'));

            divRow.addClass('col-xs-12 col-md-12 row-fields');
            divRow.attr('data-group_id','dop_goods');
            divRow.attr('data-field_id','dopgoods');
            divRow.attr('data-category',this.value);
            divRow.css({"margin-bottom": "5px"});
            countDiv.addClass('col-sm-2 col-xs-2');
            countDiv.addClass('countDiv');
            countDiv.css({"padding-right": "0"});
            selectDiv.addClass('col-md-6 col-xs-6 selectDiv');
            duplicateDiv.addClass('col-md-2 col-xs-2');
            duplicateDiv.css({'text-align': 'right'}, {'padding': 0});
            deleteDiv.addClass('col-sm-2 col-xs-2');
            duplicateDiv.css({'padding': 0});
            var input = createInput();
            input.addClass('quantity_input');
            countDiv.append(input);
            selectDiv.append(createSelect(goods));
            duplicateDiv.append('<button class="btn btn-primary duplicate_extra_goods" type="button"><i class="far fa-clone"></i></button>')
            deleteDiv.append(createDeleteBtn());
            divRow.attr('data-jobs', "");
            divRow.append(countDiv);
            divRow.append(selectDiv);
            divRow.append(duplicateDiv);
            divRow.append(deleteDiv);

            jQuery(this).parent().append(divRow);

        });

        jQuery('body').on('click','.goods_select',function () {
            jQuery(this).data('old_val',this.value);
        }).on('change','.goods_select',function(){
            var select = this,
                oldVal = jQuery(this).data('old_val'),
                oldOption = jQuery('option[value="'+oldVal+'"]',this),
                specJobs = jQuery('option:selected',this).data('spec_jobs');
            /*удаляем старые поля, если они были*/
            jQuery('.title[data-for_value="'+oldVal+'"]').remove();
            jQuery('.row-fields[data-for_value="'+oldVal+'"]').remove();
            /*если есть доп работы добавляем поля для ввода количетсва*/
            createAdditionalWorkFields(specJobs,select);
        });

        jQuery("#sketch_switch").click(function(){
            jQuery("#walls").val("");
            jQuery("#auto").val("");
            submit_form_sketch();
        });

        jQuery(".to_redactor").click(function(){
            jQuery("#calc_id").val(calculation.id);
            jQuery("#proj_id").val(calculation.project_id);
            jQuery("#form_url").attr('action','sketch/cut_redactor_2/index.php');
            submit_form_sketch();
        });

        jQuery("#btn_details").click(function(){
            jQuery("#jform_details").toggle();
        });

        fill_calc_data();

        jQuery('#calculate_button').click(function () {
            var calculate_button = jQuery(this);
            if (!calculate_button.hasClass("loading")) {
                calculate_button.addClass("loading");
                calculate_button.find("span.static").hide();
                calculate_button.find("span.loading").show();
            }
            var collected_data = collectData(),
                dataToSave = collectFieldsDataToSave(),
                need_mount = jQuery('[name="cancel_mount_1"]:checked').val(),
                cancel_metiz = jQuery("#fieldis_cancel_metiz").is(':checked') ? 1 : 0,
                cancel_offcuts = jQuery("#fieldis_cancel_offcut").is(':checked') ? 1 : 0;

            console.log("collected_data",collected_data);

            jQuery.ajax({
                url: "index.php?option=com_gm_ceiling&task=calculationForm.calculate",
                type: "post",
                data: {
                    calc_id: calculation.id,
                    goods: collected_data.goods,
                    jobs: collected_data.jobs,
                    extra_components: JSON.stringify(collected_data.extra_components),
                    extra_mounting: JSON.stringify(collected_data.extra_mounting),
                    fields_data: dataToSave,
                    photo_print: JSON.stringify(collected_data.photo_print),
                    dealer_id: dealerId,
                    need_mount: need_mount,
                    cancel_metiz: cancel_metiz,
                    cancel_offcuts: cancel_offcuts,
                    discount: jQuery("#new_discount").val(),
                    walls_height: jQuery('#walls_height').val(),
                    openings: JSON.stringify(collected_data.openings)
                },
                dataType: "json",
                async: true,
                success: function (data) {
                    console.log(data);
                    var sum = 0;
                    for(var c = 0;c<data.all_goods.length;c++){
                        if(data.all_goods[c].category_id != 1) {
                            sum += data.all_goods[c].price_sum - 0;
                        }
                    }
                    //console.log("SUM",sum+data.extra_components_sum);
                    jQuery("#under_calculate").show();
                    jQuery("#final_price").text( data.final_sum.toFixed(0) );
                    calculate_button.removeClass("loading");
                    calculate_button.find("span.loading").hide();
                    calculate_button.find("span.static").show();
                },
                error: function (data) {
                    var n = noty({
                        timeout: 2000,
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "error",
                        text: "Ошибка сервера"
                    });
                    calculate_button.removeClass("loading");
                    calculate_button.find("span.loading").hide();
                    calculate_button.find("span.static").show();
                }
            });
        });

        jQuery('body').on('click','#save_new_sum',function(){
            // throw new Exception(print_r($goods_jobs_map,true));console.log(calculation.id,jQuery("#new_sum").val());
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
        });

        jQuery("#save_button").click(function(){
            let url = '<?php echo $save_button_url;?>',
                addition = '<?=$addition;?>';
            jQuery.ajax({
                type: 'POST',
                url: 'index.php?option=com_gm_ceiling&task=calculation.save_details',
                data: {
                    title: jQuery("#jform_calculation_title").val() ,
                    details: jQuery("#jform_details").val(),
                    manager_note: jQuery("#jform_manager_note").val(),
                    calc_id: calculation.id,
                    addition: addition
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

        jQuery('#cancel_button').click(function(){
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
        });
    });

    function submit_form_sketch()
    {
        document.getElementById('n4').value = document.getElementById('jform_n4').value;
        document.getElementById('n5').value = document.getElementById('jform_n5').value;
        document.getElementById('n9').value = document.getElementById('jform_n9').value;
        if(calculation && calculation.original_sketch){
            document.getElementById('walls').value = calculation.original_sketch;
        }
        document.getElementById('texturesData').value = texturesData;
/*        console.log(jQuery("#texturesData").val());
        console.log("W",jQuery("#width").val());*/
        document.getElementById('form_url').submit();

    }

    function createCategories(data){
        var categoryDivRow,
            categoryTitleDiv;
        jQuery.each(data,function(i,category){
            categoryDivRow = jQuery('<div/>')
            categoryDivRow.addClass('row');
            categoryDivRow.addClass('category');
            categoryDivRow.append(jQuery('<div/>').addClass('col-xs-3'));
            categoryDivRow.attr('data-id',category.id);

            categoryTitleDiv = jQuery('<div/>').addClass('col-xs-6');
            categoryTitleDiv.addClass('category_title');
            categoryTitleDiv.append(jQuery('<div/>').addClass('col-xs-10').append(category.title));
            categoryTitleDiv.append(jQuery('<div/>').addClass('col-xs-2').append('<i class="fa fa-angle-down"></i>'));

            categoryDivRow.append(categoryTitleDiv);
            categoryDivRow.append(jQuery('<div/>').addClass('col-xs-3'));

            jQuery('#params_block').append(categoryDivRow);
        });
    }
    function createBlocks(data) {
        var div,
            containerDiv;

        for(let i=0;i<data.length;i++){
            containerDiv = jQuery('<div/>').addClass('container');
            containerDiv.addClass('main_groups');
            jQuery.each(data[i].main_groups, function (index, elem) {
                console.log(elem);
                if(empty(containerDiv.attr('data-category'))){
                    containerDiv.attr('data-category',elem.category_id);
                }
                var buttonTitle = '<div class="col-xs-11"><b>' + elem.title + '</b></div><div class="col-xs-1"><i class="fa fa-angle-down" style="color: #414099;"></i></div>';
                div = jQuery(document.createElement('div'));
                div.addClass('row');
                div.append('<div class="col-sm-3"></div>');
                var btnDiv = jQuery(document.createElement('div')),
                    button = jQuery(document.createElement('button'));
                button.addClass('btn btn_calc');
                button.prop('type', 'button');
                button.html(buttonTitle);
                button.attr("data-maingroup_id", elem.id);
                btnDiv.addClass('col-sm-6');
                btnDiv.append(button);
                btnDiv.append(createWorkButton(elem.groups));
                div.append(btnDiv);
                div.append('<div class="col-sm-3"></div>');
                containerDiv.append(div);
            });
            console.log(containerDiv.attr('data-category'));
            jQuery('.category[data-id="'+containerDiv.attr('data-category')+'"]').after(containerDiv);
        }

    }

    function createWorkButton(buttonsArray) {
        var resultDiv = jQuery(document.createElement('div'));
        resultDiv.addClass('inner_container');
        resultDiv.css({'display': 'none'});
        jQuery.each(buttonsArray, function (index, elem) {
            var rowDiv = jQuery(document.createElement('div')),
                button = jQuery(document.createElement('button')),
                buttonHelp = jQuery(document.createElement('div')),
                buttonDivCol = jQuery(document.createElement('div')),
                helpDivCol = jQuery(document.createElement('div')),
                fieldsDiv = jQuery(document.createElement('div'));
            buttonDivCol.addClass('col-sm-11 col-xs-11');
            buttonDivCol.css({"padding-right": "5px"});
            helpDivCol.addClass('col-sm-1 col-xs-1');
            helpDivCol.css({"padding-left": "0px"});
            fieldsDiv.addClass('div-fields');
            fieldsDiv.css({"display": "none"});
            /*кнопка подсказки*/
            buttonHelp.addClass('btn-primary help');
            buttonHelp.css({
                'padding': '5px 10px',
                'border-radius': '5px',
                'height': '42px',
                'width': '42px',
                'margin-left': '5px;'
            });
            buttonHelp.append('<div class="help_question center" style="padding-top:2px;">?</div>');
            buttonHelp.append('<span class="airhelp" style="display: none;">' + elem.description + '</span>');
            helpDivCol.append(buttonHelp);
            /*кнопка раскрытия работы*/
            button.prop('type', 'button');
            button.attr('data-group_id', elem.id);
            button.attr('data-maingroup_id', elem.main_group_id);
            button.addClass('btn add_fields');
            //button.css({'background-color': 'rgb(1, 0, 132)'});
            button.html('<div class="col-xs-2 col-sm-2"><img src="' + elem.icon + ' " class="img_calcform"></div><div class="col-xs-10 col-sm-10" style="text-align: left;">' + elem.title + '</div>');
            buttonDivCol.append(button);
            //поля под кнопкой
            fieldsDiv.append(createFields(elem.fields));
            rowDiv.append(buttonDivCol);
            rowDiv.append(helpDivCol);
            rowDiv.append(fieldsDiv);
            rowDiv.addClass('row');
            rowDiv.css({'margin-bottom': '5px', 'margin-top': '5px'});
            resultDiv.append(rowDiv);
        });
        return resultDiv;
    }

    function createFields(fieldsData) {
        var resultDiv = jQuery(document.createElement('div'));
        jQuery.each(fieldsData, function (index, elem) {

            var divRow = jQuery(document.createElement('div')),
                countDiv = jQuery(document.createElement('div')),
                titleDiv = jQuery(document.createElement('div')),
                label = jQuery(document.createElement('label')),
                jobsIds = getIds(elem.jobs),
                goodsIds = (!empty(elem.default_goods)) ? getIds(elem.default_goods) : [];
            countDiv.addClass('countDiv');
            titleDiv.addClass('row title');
            titleDiv.css({"margin-left": "15px", "color": "#414099"})
            label.css({"margin-left": "15px", "margin-bottom": "2px", "color": "#414099"})
            divRow.addClass('col-md-12 col-xs-12 row-fields');
            divRow.css({"margin-bottom": "5px"});
            divRow.attr('data-id', elem.id);
            divRow.attr('data-group_id', elem.group_id);
            label.html(elem.title);
            titleDiv.append(label);
            divRow.attr('data-jobs', jobsIds);
            if(!empty(goodsIds)) {
                divRow.attr('data-goods', goodsIds);
            }
            if (empty(elem.goods_category_id)) {
                if (elem.input_type == 0) {
                    resultDiv.append(titleDiv);
                    var input = createInput();
                    input.addClass('quantity_input');
                    countDiv.append(input);
                    countDiv.addClass('col-md-12');
                    divRow.append(countDiv);
                    if(elem.duplicate == 1){
                        countDiv.removeClass('col-md-12');
                        countDiv.addClass('col-md-10 col-xs-10');
                        var deleteDiv = jQuery(document.createElement('div'));
                        deleteDiv.addClass('col-md-2 col-xs-2');
                        deleteDiv.append(createDeleteBtn());
                        divRow.append(deleteDiv);
                    }
                }
                if (elem.input_type == 1) {
                    var checkBox = createCheckBox(elem);
                  /*  console.log("el",elem);
                    console.log(!empty(elem.parent));*/
                    if(!empty(elem.parent)){
                        var checkboxDiv = jQuery(document.createElement('div'));
                        checkBox.input.attr('checked',true);
                        checkboxDiv.append(checkBox.input);
                        checkboxDiv .append(checkBox.label);
                        divRow.append(checkboxDiv);
                        divRow.attr('data-parent',elem.parent);
                    }
                    else{
                        countDiv.append(checkBox.input);
                        countDiv.append(checkBox.label);
                        divRow.append(countDiv);
                    }

                    divRow.append(countDiv);
                    divRow.addClass('center');
                }
                if (elem.input_type == 2) {
                    var radioDiv = jQuery(document.createElement('div')),
                        inputDiv = jQuery(document.createElement('div')),
                        radioBtn = createRadioBtns(elem),
                        input;
                    radioDiv.addClass('col-xs-6 div-radio countDiv');
                    inputDiv.addClass('col-xs-6 div-jobs');
                    if(elem.manual_jobs){
                        jQuery.each(elem.manual_jobs,function(i,j){
                            let div = jQuery(document.createElement('div'));
                            div.addClass('countDiv');
                            div.addClass('col-md-12');
                            div.attr('data-job_id',j.id);
                            div.append('<label>'+j.name+'</label>');
                            input = createInput();
                            input.attr('id','rd_man'+j.id);
                            div.append(input);
                            inputDiv.append(div);
                        });

                    }
                    inputDiv.css({"display": "none"});
                    radioDiv.append(radioBtn.radioBtn);
                    radioDiv.append(radioBtn.label);
                    divRow.append(radioDiv);
                    divRow.append(inputDiv);



                }
                if (elem.input_type == 3) {
                    var categoryDiv = jQuery(document.createElement('div')),
                        deleteDiv = jQuery(document.createElement('div')),
                        categories = getCategories(componentsInCategories)
                    select = createSelect(categories);
                    categoryDiv.addClass('category col-xs-12 col-md-12 countDiv');
                    //deleteDiv.addClass('col-sm-2 col-xs-2');
                    select.prop('name', 'choose_category');
                    categoryDiv.append(select);
                    //deleteDiv.append(createDeleteBtn());
                    resultDiv.append(titleDiv);
                    divRow.append(categoryDiv);
                    //divRow.append(deleteDiv);
                }
                if (elem.input_type == 4) {
                    resultDiv.append(titleDiv);
                    var titlesDiv = jQuery(document.createElement('div')),
                        fieldsDiv = jQuery(document.createElement('div')),
                        deleteDiv = jQuery(document.createElement('div'));
                    titlesDiv.addClass('row title');
                    fieldsDiv.addClass('row field');
                    titlesDiv.css({'margin-left': '0px','margin-right': '0px'});
                    fieldsDiv.css({'margin-left': '0px','margin-right': '0px'});


                    fieldsDiv.attr("data-field_id",elem.id);
                    for (var i = 0; i < elem.subfields.length; i++) {
                        var div = jQuery(document.createElement('div')),
                            title = jQuery(document.createElement('div'));
                        if(elem.duplicate == 1){
                            div.addClass('col-md-5 col-xs-5 countDiv');
                        }
                        else{
                            div.addClass('col-md-6 col-xs-6 countDiv');
                        }
                        title.addClass('col-md-6 col-xs-6');
                        title.append('<label>' + elem.subfields[i].title + '</label>');
                        titlesDiv.append(title);
                        var input = createInput();
                        input.attr('name', elem.subfields[i].id);
                        input.addClass('additional');
                        div.append(input);
                        fieldsDiv.append(div);
                    }
                    if(elem.duplicate == 1){
                        deleteDiv.addClass('col-xs-2 col-md-2');
                        deleteDiv.append(createDeleteBtn());
                        fieldsDiv.append(deleteDiv);
                    }
                    divRow.append(titlesDiv);
                    divRow.append(fieldsDiv);
                }
            }
            else if (!empty(elem.goods_category_id)) {
                if (elem.input_type == 0) {
                    resultDiv.append(titleDiv);
                    var selectDiv = jQuery(document.createElement('div')),
                        deleteDiv = jQuery(document.createElement('div'));
                    countDiv.addClass('col-md-2 col-xs-2');
                    countDiv.css({"padding-right": "0"});
                    if (elem.duplicate == 1) {
                        selectDiv.addClass('col-md-8 col-xs-8 selectDiv');
                        deleteDiv.addClass('col-md-2 col-xs-2');
                    }
                    else {
                        selectDiv.addClass('col-md-10 col-xs-10 selectDiv');

                    }
                    var input = createInput();
                    input.addClass('quantity_input');
                    countDiv.append(input);
                    selectDiv.append(createSelect(elem.goods));
                    if (elem.duplicate == 1) {
                        deleteDiv.append(createDeleteBtn());
                    }
                    divRow.append(countDiv);
                    divRow.append(selectDiv);
                    if (elem.duplicate == 1) {
                        divRow.append(deleteDiv);
                    }
                }
                if (elem.input_type == 1) {

                }
                if (elem.input_type == 2) {

                    var radioDiv = jQuery(document.createElement('div')),
                        selectDiv = jQuery(document.createElement('div')),
                        radioBtn = createRadioBtns(elem),
                        select = createSelect(elem.goods);
                    radioDiv.addClass('col-xs-6 div-radio');
                    selectDiv.addClass('col-xs-6 div-goods_select');
                    selectDiv.css({"display": "none"});
                    radioDiv.append(radioBtn.radioBtn);
                    radioDiv.append(radioBtn.label);
                    selectDiv.append(select);
                    divRow.append(radioDiv);
                    divRow.append(selectDiv);

                }
            }
            if (!empty(elem.parent)) {
                addToParentDiv(resultDiv, elem.parent, divRow);
            }
            else {
                resultDiv.append(divRow);
            }
            if (elem.duplicate == 1) {
                resultDiv.append(createAddBtn(elem.id));
            }
        });
        return resultDiv;
    }

    function createSelect(selectData) {
        var select = jQuery(document.createElement('select'));
        select.addClass('form-control goods_select ');
        jQuery.each(selectData, function (index, elem) {
            var option = jQuery(document.createElement('option'));
            option.prop('value', elem.id);
            option.prop('text', elem.name);
            if(!empty(elem.spec_jobs)){
                option.attr('data-spec_jobs',JSON.stringify(elem.spec_jobs));
            }
            if (!empty(elem.child_goods)) {
                option.attr('data-child_goods', getIds(elem.child_goods));
            }
            select.append(option);
        });
        return select;
    }

    function createInput() {
        var input = jQuery(document.createElement('input'));
        input.addClass('form-control');
        return input;
    }

    function createRadioBtns(field) {
        var result,
            radioBtn = jQuery(document.createElement('input')),
            label = jQuery(document.createElement('label'));
        radioBtn.prop('type', 'radio');
        radioBtn.attr('data-id', field.id);
        radioBtn.attr('data-parent', field.parent);
        radioBtn.prop('id', field.id);
        radioBtn.prop('name', field.group_id + '_1');
        radioBtn.addClass('radio');
        radioBtn.prop('value', "");
        if(!empty(field.jobs)){
            radioBtn.prop('value', getIds(field.jobs));
        }
        if(field.value !== undefined){
            radioBtn.prop('value', field.value);
        }
        label.prop('for', field.id);
        label.html(field.title);
        result = {radioBtn: radioBtn, label: label};
        return result;
    }

    function createCheckBox(field) {
        var input = jQuery(document.createElement('input')),
            label = jQuery(document.createElement('label'));
        input.prop("type", "checkbox");
        input.prop("id", "field" + field.id);
        input.addClass("inp-cbx");
        input.css({"display": "none"});
        label.prop("for", "field" + field.id);
        label.addClass("cbx");
        label.html("<span><svg width=\"12px\" height=\"10px\" viewBox=\"0 0 12 10\"><polyline points=\"1.5 6 4.5 9 10.5 1\"></polyline></svg></span><span> " + field.title + "</span>");
        return {input: input, label: label};
    }

    function createDeleteBtn() {
        var deleteBtn = jQuery(document.createElement('button'));
        deleteBtn.addClass('clear_form_group btn btn-danger delete_goods');
        deleteBtn.prop('type', 'button');
        deleteBtn.html('<i class="fa fa-trash" aria-hidden="true"></i>');
        return deleteBtn;
    }

    function createAddBtn(field_id) {
        var div = jQuery(document.createElement('div')),
            addButton = jQuery(document.createElement('button'));
        div.addClass('row center');
        addButton.addClass('btn btn-primary add');
        addButton.css({'margin-bottom': '15px'});
        addButton.prop('type', 'button');
        addButton.attr('data-field', field_id);
        addButton.html('<i class="fa fa-plus" aria-hidden="true"></i> Добавить');
        div.append(addButton);
        return div;
    }

    function getIds(objArr) {
        var result = [];
        if(!empty(objArr)) {
            for (var i = objArr.length; i--;) {
                result.push(objArr[i].id);
            }
        }
        return JSON.stringify(result);
    }

    function addToParentDiv(div, parentId, newElement) {
        jQuery.each(div.children(), function (index, elem) {
            if (jQuery(elem).data('id') == parentId) {
                jQuery(elem).append(newElement);
            }
        });
    }

    function fill_calc_data() {
        var factory_works = calculation.jobs.filter(function (job) {
                return job.is_factory_work == 1 && job.guild_only == 0;
            });
        if(factory_works.length){
            for(var i = 0;i<factory_works.length;i++){
                var tr = jQuery(document.createElement('tr'));
                tr.append('<td width=35%><label>'+factory_works[i].name+'</label></td>')
                tr.append('<td width=35%><input class="form-control-input no-border" value="'+factory_works[i].count+'" readonly></td>');
                jQuery("#common_info_table").append(tr);
            }
        }
        /*заполнение проемов*/
        var openings = (!empty(calculation.openings)) ? JSON.parse(atob(calculation.openings)) : null,
            openignsArr = !empty(openings) ? openings.openings : null,
            openingFields;
        if(!empty(openignsArr)){
            var addBtn = jQuery('.add[data-field = opening_fields]');
            for(var i=0;i<openignsArr.length;i++){
                openingFields = jQuery('.openings').last();
                openingFields.find('.width').val(openignsArr[i].width);
                openingFields.find('.height').val(openignsArr[i].height);
                openingFields.find('.opening_select').val(openignsArr[i].type);
                if(i<openignsArr.length-1) {
                    addBtn.trigger('click');
                }
            }
        }

        if (calculation.n4 && calculation.n5 && calculation.n9) {
            jQuery("#jform_n4").val(calculation.n4);
            jQuery("#jform_n5").val(calculation.n5);
            jQuery("#jform_n9").val(calculation.n9);
            jQuery("#jform_n10").val(calculation.n10);
            jQuery("#jform_n31").val(calculation.n31);
            jQuery("#jform_shrink_per").val(((1 - calculation.shrink_percent).toFixed(2) * 100).toFixed(2));
            jQuery("#data-wrapper").show();

        }
        let filename = '<?php echo $calc_img;?>';
        if (filename) {
            jQuery("#sketch_image").attr('src', filename);
            jQuery("#sketch_image_block").show();
        }
        var json_savedData = '<?php echo $calculation->fields_data;?>';
        var savedData = !empty(json_savedData) ? JSON.parse(json_savedData) : '';
        console.log('retrievedObject: ', savedData);
        if (!empty(savedData)) {
            jQuery.each(savedData, function (index, elem) {
                var mainGroupBtn = jQuery('#params_block').find('.btn_calc[data-maingroup_id="' + elem.maingroup_id + '"]'),
                    mainGroupsContainer = mainGroupBtn.closest('.main_groups'),
                    categoryId = mainGroupsContainer.attr('data-category'),
                    categoryBtn = jQuery('.category[data-id="'+categoryId+'"]').find('.category_title');
                if(!mainGroupsContainer.is(':visible')){
                    categoryBtn.trigger('click');
                }
                mainGroupBtn.trigger('click');
                for (var i = elem.groups.length; i--;) {
                    jQuery('#params_block').find('.add_fields[data-group_id="' + elem.groups[i].group_id + '"]').trigger('click');

                    var countDiv, input;
                    for (var j = 0; j < elem.groups[i].fields.length; j++) {
                        if (elem.groups[i].fields[j].field_data.length > 1) {
                            var addBtn = jQuery('#params_block').find('.add[data-field="' + elem.groups[i].fields[j].field_id + '"]');
                            for (var z = 1; z < elem.groups[i].fields[j].field_data.length; z++) {
                                addBtn.trigger('click');
                            }
                        }
                        for (var f = 0; f < elem.groups[i].fields[j].field_data.length; f++) {
                            var savedInput = elem.groups[i].fields[j].field_data[f],
                                rowFields = jQuery('#params_block').find('.row-fields[data-group_id="' + elem.groups[i].group_id + '"][data-id="' + elem.groups[i].fields[j].field_id + '"]');
                            if (savedInput.type == "checkbox") {
                                countDiv = jQuery(rowFields[f]).find('.countDiv');
                                input = jQuery(countDiv).children();
                                input.attr('checked', true);
                            }
                            if (savedInput.type == "text") {
                                countDiv = jQuery(rowFields[f]).find('.countDiv');
                                input = jQuery(countDiv).find('.quantity_input');
                                input.val(savedInput.value);
                                if (savedInput.related.length) {
                                    for (var k = 0; k < savedInput.related.length; k++) {
                                        if (savedInput.related[k].type == 'select-one') {
                                            var select = jQuery(rowFields[f]).find('.selectDiv').children();
                                            select.val(savedInput.related[k].value);
                                            var specJobs = jQuery('option:selected',select).data('spec_jobs');
                                                createAdditionalWorkFields(specJobs,select[0]);
                                            if(!empty(savedInput.related[k].additional)){
                                                jQuery.each(savedInput.related[k].additional,function(n,field){
                                                    jQuery('[data-id="'+field.id+'"]').find('.quantity_input').val(field.value);
                                                });
                                            }
                                        }
                                        if (savedInput.related[k].type == 'radio') {
                                            var radioBtn = jQuery('#' + savedInput.related[k].id + '[data-parent = "' + elem.groups[i].fields[j].field_id + '"]');
                                            radioBtn.attr('checked', true);

                                            if (savedInput.related[k].assoc) {
                                                radioBtn.trigger('click');
                                                radioBtn.closest('.row-fields').find('.div-goods_select').children().val(savedInput.related[k].assoc.value);
                                            }
                                            if (savedInput.related[k].assocInputs) {
                                                radioBtn.trigger('click');
                                                jQuery.each(savedInput.related[k].assocInputs,function(index,el){
                                                   radioBtn.closest('.row-fields').find('.div-jobs').children().find('#'+el.id).val(el.value);
                                                })
                                            }
                                        }
                                        if (savedInput.related[k].type == 'checkbox') {
                                            var checkbox = jQuery('#' + savedInput.related[k].id);
                                            checkbox.attr('checked', true);
                                        }
                                    }
                                }
                            }
                            if (savedInput.type == "radio") {
                                jQuery('.radio[data-id="' + savedInput.id + '"]').attr('checked', true);
                            }
                            if (savedInput.type == "additional") {
                                for (var ai = 0; ai < savedInput.data.length; ai++) {
                                    jQuery(rowFields[f]).find('[name="' + savedInput.data[ai].name + '"]').val(savedInput.data[ai].value)
                                }
                            }
                            if (savedInput.type == "select-one") {
                                for (var k = 0; k < savedInput.fields_data.length; k++) {
                                    jQuery('[name="choose_category"]').val(savedInput.fields_data[k].category).trigger('change');
                                    var goods_rows = jQuery("#params_block").find('.row-fields[data-category="' + savedInput.fields_data[k].category + '"]');
                                    jQuery.each(goods_rows, function (q, g_row) {
                                        var count_input = jQuery(g_row).find('.countDiv').children();
                                        if (empty(count_input.val())) {
                                            count_input.val(savedInput.fields_data[k].count);
                                            jQuery(g_row).find('.goods_select').val(savedInput.fields_data[k].goods);
                                        }
                                    });
                                }

                            }
                        }
                    }
                }
            });
        }
    }

    function getCategories(componentsArray) {
        var categories = [];
        jQuery.each(componentsArray, function (index, elem) {
            categories.push({id: elem.category_id, name: elem.category_name});
        })
        return categories;
    }

    function getGoodsByCategory(categoryId) {
        var category = componentsInCategories.find(function (elem, index) {
            if (elem.category_id == categoryId) {
                return elem;
            }
        })
        return category.goods;
    }

    function collectData() {
        var jobs = [],
            components = [],
            stock_goods = [];
        var fieldsDiv = jQuery('.row-fields');

        jQuery.each(fieldsDiv, function (index, div) {
            if(jQuery(div).data('field_id') != 'dopgoods'){
                var currentJobs = jQuery(div).data('jobs'),
                    currentGoods = jQuery(div).data('goods'),
                    countDiv, input, goodSelect, radio,checkbox,inputs;
                if (empty(currentJobs)) {
                    currentJobs = [];
                }
                if(empty(currentGoods)){
                    currentGoods = [];
                }
                countDiv = jQuery(div).find('.countDiv');
                input = jQuery(countDiv).children();
                if (input.prop('type') == "checkbox") {
                    if (input.is(':checked')) {
                        for (var i = currentJobs.length; i--;) {
                            jobs.push({id: currentJobs[i], count: 1});
                        }
                    }
                }
                if (input.prop('type') == "text") {
                    //поиск связанных radio
                    var id = countDiv.parent().data('id'),
                        radio = countDiv.parent().find('input[type=radio][data-parent="' + id + '"]:checked'),
                        radioGoodSelect = radio.closest('.row-fields').find('.div-goods_select').find('.goods_select'),
                        radioInputs = radio.closest('.row-fields').find('.div-jobs').find('.countDiv');
                    if (!empty(radio.val())) {
                        if (!empty(input.val())) {
                            if (currentJobs.length == 0) {
                                currentJobs = JSON.parse(radio.val());
                            }
                            else {
                                currentJobs = currentJobs.concat(JSON.parse(radio.val()));
                            }
                            if (radioGoodSelect.length != 0) {
                                var childGoods = radioGoodSelect.children("option:selected").data('child_goods');
                                if (!empty(childGoods)) {
                                    if (childGoods.length) {
                                        for (var i = 0; i < childGoods.length; i++) {
                                            components.push({id: childGoods[i], count: input.val()});
                                        }
                                    }
                                }
                                components.push({id: radioGoodSelect.val(), count: input.val()});
                            }
                            if(radioInputs.length != 0){
                                jQuery.each(radioInputs,function(i,j){
                                    var div = jQuery(j),
                                        input = div.find('input');
                                    if(!empty(input.val())){
                                        jobs.push({id: div.data('job_id'), count: input.val()})
                                    }
                                });
                            }
                        }
                    }
                    //поиск связанных селектов
                    goodSelect = countDiv.parent().find('.selectDiv').children();
                    //если есть селект и введеное количество не пустое добавляем компоненты
                    if (goodSelect.length != 0 && !empty(input.val())) {
                        var childGoods = goodSelect.children("option:selected").data('child_goods');
                        if (!empty(childGoods)) {
                            if (childGoods.length) {
                                for (var i = 0; i < childGoods.length; i++) {
                                    components.push({id: childGoods[i], count: input.val()});
                                }
                            }
                        }
                        components.push({id: goodSelect.val(), count: input.val()});
                    }

                    //поиск связанных checkbox
                    var checkbox = jQuery('.row-fields [data-parent="'+id+'"]').find('.inp-cbx:checked');
                    if(checkbox.length > 0){
                        var jbs = checkbox.closest('.row-fields').data('jobs');
                        currentJobs = currentJobs.concat(jbs);
                    }

                    //добавляем работы и компоненты если количество не пустое
                    if (!empty(input.val())) {
                        for (var i = currentJobs.length; i--;) {
                            jobs.push({id: currentJobs[i], count: input.val()});
                        }
                        for (var i = currentGoods.length; i--;) {
                            components.push({id: currentGoods[i], count: input.val()});
                        }
                    }
                }
                if (input.prop('type') == "radio" && empty(input.data('parent'))) {
                    if (input.is(':checked')) {
                        if(!empty(input.val())) {
                            console.log(input);
                            currentJobs = JSON.parse(input.val());
                            for (var i = currentJobs.length; i--;) {
                                jobs.push({id: currentJobs[i], count: 1});
                            }
                        }
                    }
                }
            }
            else{
                countDiv = jQuery(div).find('.countDiv');
                input = jQuery(countDiv).children();
                if (input.prop('type') == "text") {
                    //поиск связанных селектов
                    goodSelect = countDiv.parent().find('.selectDiv').children();
                    //если есть селект и введеное количество не пустое добавляем компоненты
                    if (goodSelect.length != 0 && !empty(input.val())) {
                        var childGoods = goodSelect.children("option:selected").data('child_goods');
                        if (!empty(childGoods)) {
                            if (childGoods.length) {
                                for (var i = 0; i < childGoods.length; i++) {
                                    components.push({id: childGoods[i], count: input.val()});
                                }
                            }
                        }
                        stock_goods.push({id: goodSelect.val(), count: input.val()});
                    }
                }
            }
        });


        //получение площади истоимости фотопечати

        var photoprint = "",
            additional_works = [],
            additional_components = [];
        if (!empty(jQuery('[name = "print_square"]').val()) && !empty(jQuery('[name = "print_cost"]').val())) {
            photoprint = {
                square: jQuery('[name = "print_square"]').val(),
                price: jQuery('[name = "print_cost"]').val()
            }
        }

        /*проемы*/
        var openingsEl = jQuery('.openings'),
            openingsData = {
                openings : [],
                square: 0,
                widths: 0
            },
            width,height,type;
        jQuery.each(openingsEl,function(n,o){
            width = jQuery(o).find('.width').val();
            height = jQuery(o).find('.height').val();
            type = jQuery(o).find('.opening_select').val();
            openingsData.square += width*height*0.0001;
            if(type == 2){
                openingsData.widths += width;
            }
            openingsData.openings.push({
               width: width,
               height: height,
               type: type
           });
        });

        jQuery.each(jQuery('[name = "work_title"]'), function (index, elem) {
            var cost = jQuery(elem).closest('.field').find('[name="work_cost"]').val(),
                isService = jQuery('#mount_service').is(':checked');
            if (!empty(cost)) {
                if(isService){
                    additional_works.push({title: elem.value, price: cost,service_price:+cost+ (cost*0.2)});

                }
                else{
                    additional_works.push({title: elem.value, price: cost});
                }
            }
        });
        jQuery.each(jQuery('[name = "component_title"]'), function (index, elem) {
            var cost = jQuery(elem).closest('.field').find('[name="component_cost"]').val();
            if (!empty(cost)) {
                additional_components.push({title: elem.value, price: cost});
            }
        });

        components = sumSameValues(components);
        jQuery.each(components,function (index,component) {
            if(goodsJobsMap[component.id]){
               var goodsJobsMapArr = goodsJobsMap[component.id];
                for(let i = 0;i<goodsJobsMapArr.length;i++){
                    jobs.push({id:goodsJobsMapArr[i].job_id,count:component.count*goodsJobsMapArr[i].count});
                }
            }
        });
        jobs = sumSameValues(jobs);
        components = components.concat(stock_goods);
        components = sumSameValues(components);

        return {
            jobs: jobs,
            goods: components,
            extra_components: additional_components,
            extra_mounting: additional_works,
            photo_print: photoprint,
            openings: openingsData
        };
    }

    function sumSameValues(arrData) {
        var result = [];
        arrData.reduce(function (res, value) {
            if (!res[value.id]) {
                res[value.id] = {
                    count: 0,
                    id: value.id
                };
                result.push(res[value.id])
            }
            res[value.id].count += +value.count;
            return res;
        }, {});
        return result;
    }

    function collectFieldsDataToSave() {
        var dataToSave = [],
            groups_data = jQuery('.add_fields'),
            result = [];

        jQuery.each(groups_data, function (index, elem) {
            var mainGroupId = jQuery(elem).data('maingroup_id'),
                groupId = jQuery(elem).data('group_id'),
                fieldsRow = jQuery('.row-fields[data-group_id="' + groupId + '"]'),
                fields = [];

            jQuery.each(fieldsRow, function (n, row) {
                var countDiv = jQuery(row).find('.countDiv'),
                    input = jQuery(countDiv).children(),
                    fieldObj = {},
                    related = [],
                    goodSelect, radio,checkbox;
                if (input.prop('type') == "checkbox") {
                    if (input.is(':checked')) {
                        fieldObj = {id: input.prop('id'), type: input.prop('type'), value: 1, related: []};
                    }
                }
                if (input.prop('type') == "text") {
                    if(input.hasClass('additional')){
                        var parent = input.closest('.field');
                        fieldObj = {
                            field_id : parent.data('field_id'),
                            type: "additional",
                            data : []
                        };

                        var inputs = parent.find('.additional');
                        jQuery.each(inputs,function (index,elem) {
                            if(!empty(elem.value)) {
                                fieldObj.data.push({name: jQuery(elem).prop('name'), value: elem.value});
                            }
                        });
                    }
                    else{
                        if (input.val() > 0) {
                            var id = countDiv.parent().data('id'),
                                radio = countDiv.parent().find('input[type=radio][data-parent="' + id + '"]:checked'),
                                radioGoodSelect = radio.closest('.row-fields').find('.div-goods_select').find('.goods_select'),
                                realatedInputs = radio.closest('.row-fields').find('.div-jobs').find('.countDiv');
                            if (!empty(radio.val())) {
                                var assocSelect = "",
                                    assocInputs = [];
                                if (radioGoodSelect.length != 0) {
                                    assocSelect = {
                                        id: radioGoodSelect.attr('id'),
                                        type: radioGoodSelect.prop('type'),
                                        value: radioGoodSelect.val()
                                    };
                                }
                                if (realatedInputs.length != 0) {
                                    jQuery.each(realatedInputs,function(i,j){
                                        var input = jQuery(j).find('input');
                                        if(!empty(input.val())){
                                            assocInputs.push(
                                                {
                                                    id: input.attr('id'),
                                                    type: input.prop('type'),
                                                    value: input.val()
                                                }
                                            );
                                        }
                                    })
                                }
                                related.push({
                                    id: radio.attr('id'),
                                    type: radio.prop('type'),
                                    value: 1,
                                    assoc: assocSelect,
                                    assocInputs: assocInputs
                                });

                            }
                            //поиск связанных селектов
                            goodSelect = countDiv.parent().find('.selectDiv').children();
                            //если есть селект и введеное количество не пустое добавляем компоненты
                            if (goodSelect.length != 0 && !empty(input.val())) {
                                var additionalFields = jQuery('.row-fields[data-for_value="'+goodSelect.val()+'"]'),
                                    additional = [];
                                if(!empty(additionalFields)){
                                    jQuery.each(additionalFields,function(n,field){
                                        var count = jQuery(field).find('.quantity_input').val();
                                        if(!empty(count)) {
                                            additional.push({
                                                id: jQuery(field).data('id'),
                                                value: count
                                            });
                                        }
                                    });
                                }
                                related.push({
                                    id: goodSelect.prop('id'),
                                    type: goodSelect.prop('type'),
                                    value: goodSelect.val(),
                                    additional: additional
                                });
                            }
                            checkbox = jQuery('.row-fields [data-parent="'+id+'"]').find('.inp-cbx:checked');
                            if(checkbox.length > 0){
                                related.push({
                                    id: checkbox.prop('id'),
                                    type: checkbox.prop('type'),
                                    value: checkbox.val()
                                })
                            }
                            fieldObj = {
                                id: input.prop('id'),
                                type: input.prop('type'),
                                value: input.val(),
                                related: related
                            };
                        }
                    }

                }
                if (input.prop('type') == "radio" && empty(input.data('parent'))) {
                    if (input.is(':checked')) {
                        fieldObj = {id: input.prop('id'), type: input.prop('type'), value: 1, related: []};
                    }
                }
                if(input.prop('type') == "select-one"){
                    fieldObj = {
                        type: input.prop('type'),
                        fields_data: []
                    }
                    var relatedRows = input.siblings('.row-fields[data-category]');
                    jQuery.each(relatedRows,function (index,relRow) {
                        var count = jQuery(relRow).find('.countDiv').children().val(),
                            goods_id = jQuery(relRow).find('.goods_select').val();
                        fieldObj.fields_data.push({count:count,category:jQuery(relRow).data('category'),goods:goods_id});
                    });
                }
                if(!jQuery.isEmptyObject(fieldObj)){
                    var fieldIndex = checkExistFieldId(fields, jQuery(row).data('id'));
                    if (fieldIndex == -1) {
                        fields.push({field_id: jQuery(row).data('id'), field_data: []});
                    }
                    fieldIndex = checkExistFieldId(fields, jQuery(row).data('id'));
                    fields[fieldIndex].field_data.push(fieldObj);
                }

            });

            var index = checkExistMaingroup(dataToSave, mainGroupId);
            if (index == -1) {
                dataToSave.push({maingroup_id: mainGroupId, groups: []});
            }
            index = checkExistMaingroup(dataToSave, mainGroupId);
            if (fields.length) {
                dataToSave[index].groups.push({maingroup: mainGroupId, group_id: groupId, fields: fields});
            }
        });
        jQuery.each(dataToSave, function (index, elem) {
            for (var i = elem.groups.length; i--;) {
                for (var j = 0; j < elem.groups[i].fields.length; j++) {
                    if(elem.groups[i].fields[j].field_data.length > 0){
                        for(var k=0;k < elem.groups[i].fields[j].field_data.length;k++){
                            if(elem.groups[i].fields[j].field_data[k].hasOwnProperty('data')) {
                                if (empty(elem.groups[i].fields[j].field_data[k].data)) {
                                    elem.groups[i].fields[j].field_data.splice(k, 1);
                                    continue;
                                }
                            }
                            if(elem.groups[i].fields[j].field_data[k].hasOwnProperty('fields_data')) {
                                if (empty(elem.groups[i].fields[j].field_data[k].fields_data)) {
                                    elem.groups[i].fields[j].field_data.splice(k, 1);
                                }
                            }
                        }
                    }
                    if (elem.groups[i].fields[j].field_data.length == 0) {
                        elem.groups[i].fields.splice(j, 1);
                    }
                }
                if (elem.groups[i].fields.length == 0) {
                    elem.groups.splice(i, 1);
                }
            }
        });
        for (var i = dataToSave.length; i--;) {
            if (dataToSave[i].groups.length == 0) {
                dataToSave.splice(i, 1);
            }
        }
        console.log("dataToSave",dataToSave);
        return JSON.stringify(dataToSave);
    }

    function checkExistMaingroup(array, maingroup) {
        return array.findIndex(function (element, index) {
            if (element.maingroup_id == maingroup) {
                return true;
            }
            else {
                return false;
            }
        });
    }

    function checkExistFieldId(array, field_id) {
        return array.findIndex(function (element, index) {
            if (element.field_id == field_id) {
                return true;
            }
            else {
                return false;
            }
        });
    }

    function puf() {
        let filename = '<?php echo $cut_img;?>';
        if (filename) {
            jQuery('#cut_image').attr('src', filename);
            jQuery('#input_cut_data').val(calculation.cut_data);
            jQuery('#input_shrink_percent').val(calculation.shrink_percent);
            jQuery('#jform_offcut_square').val(calculation.offcut_square);
            jQuery('#input_camvas_area').val(calculation.canvas_area);
            jQuery('#div_for_test').show();
        }
    }

    function getOpeningsCommonValues() {
        var result = {
            "square": 0,
            "length": 0
        }
        jQuery.each(jQuery('.openings'),function(i,e){
           result.square += jQuery(e).find('.width').val()*jQuery(e).find('.height').val()*0.0001;
           if(jQuery(e).find('opening_select').val() == 2){
               result.length += jQuery(e).find('.width').val();
           }
        });
        return result;
    }
</script>