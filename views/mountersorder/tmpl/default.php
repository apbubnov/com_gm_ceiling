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

$jinput = JFactory::getApplication()->input;
$project = $jinput->get('project', null, 'INT');
$stages = json_decode($jinput->get('stage', null, 'STRING'));
$calc_sum_stage = 0;
$project_model = Gm_ceilingHelpersGm_ceiling::getModel('Project');
$project_data = $project_model->getData($project);
$service = !empty(json_decode($project_data->calcs_mounting_sum)) ? true : false;
$model = Gm_ceilingHelpersGm_ceiling::getModel('mountersorder');
$calculationModel = Gm_ceilingHelpersGm_ceiling::getModel('calculation');
$full = in_array('1',$stages);
$calculation_ids = $model->GetCalculation($project);

if (!empty($calculation_ids)) {
    if(!$service){
        $DataOfTransport = Gm_ceilingHelpersGm_ceiling::calculate_transport($project);
    }
    else{
        $DataOfTransport = Gm_ceilingHelpersGm_ceiling::calculate_transport($project);
    }
}

if (!empty($calculation_ids)) {
    $AllCalc = [];
    $modelCalcform = Gm_ceilingHelpersGm_ceiling::getModel('calculationform');
    $modelCalculation = Gm_ceilingHelpersGm_ceiling::getModel('calculation');
    $all_extra_mounting = [];
    $extra_mount_sum = 0;
    foreach ($calculation_ids as $value) {
        $calculation = $modelCalculation->getBaseCalculationDataById($value->id);
        $extra_mounting = json_decode($calculation->extra_mounting);
        if(!empty($extra_mounting)){
            $all_extra_mounting = array_merge($all_extra_mounting,$extra_mounting);
            foreach ($extra_mounting as $mount) {
                $extra_mount_sum += $mount->price;
            }
        }
        $all_jobs = $modelCalcform->getJobsPricesInCalculation($value->id, $user->dealer_id); // Получение работ по прайсу дилера
        if(!empty($all_jobs)){
            $DataOfProject[$value->id] = $all_jobs;
        }
        else{
            if(!$service){
                $DataOfProject[$value->id] = Gm_ceilingHelpersGm_ceiling::calculate_mount(0, $value->id, null);
            }
            else{
                $DataOfProject[$value->id] = Gm_ceilingHelpersGm_ceiling::calculate_mount(0, $value->id, null,null,"mount");
            }
        }
        foreach ($stages as $stage) {
            if(isset($DataOfProject[$value->id]["mounting_data"])) {
                foreach ($DataOfProject[$value->id]["mounting_data"] as $val) {
                    if ($val['stage'] == $stage || $stage == 1) {
                        if (!array_key_exists($val["title"], $AllCalc)) {
                            $AllCalc[$val["title"]] = ["title" => $val["title"], "gm_salary" => $val["gm_salary"], "dealer_salary" => $val["dealer_salary"], "quantity" => $val["quantity"], "gm_salary_total" => $val["gm_salary_total"], "dealer_salary_total" => $val["dealer_salary_total"]];
                        } else {
                            $AllCalc[$val["title"]]["quantity"] += $val["quantity"];
                            $AllCalc[$val["title"]]["gm_salary_total"] += $val["gm_salary_total"];
                            $AllCalc[$val["title"]]["dealer_salary_total"] += $val["dealer_salary_total"];
                        }
                    }
                }
            }
            else{
                foreach ($DataOfProject[$value->id] as $val) {
                    if ($val->mount_type_id == $stage || $stage == 1) {
                        if (!array_key_exists($val->name, $AllCalc)) {
                            $AllCalc[$val->name] = ["title" => $val->name, "gm_salary" => $val->price, "dealer_salary" => $val->price, "quantity" => $val->final_count, "gm_salary_total" => $val->price_sum, "dealer_salary_total" => $val->price_sum];
                        } else {
                            $AllCalc[$val->name]["quantity"] += $val->final_count;
                            $AllCalc[$val->name]["gm_salary_total"] += $val->price_sum;
                            $AllCalc[$val->name]["dealer_salary_total"] += $val->price_sum;
                        }
                    }
                }
            }
        }
    }
}
$canvas_model = Gm_ceilingHelpersGm_ceiling::getModel("canvases");
$components_model = Gm_ceilingHelpersGm_ceiling::getModel("components");
?>
<style>

    .div_imgs {
        overflow-x: auto;
        white-space: nowrap;
    }
    .uploaded_calc_img {
        display: inline-block;
        max-width: 200px;
        padding: 2px 10px;
        cursor: pointer;
    }
    .uploaded_calc_img:hover {
        background: gray;
    }
    .big_uploaded_img {
        width: 100%;
        height: 100%;
        object-fit: contain;
    }
    #modal_window_img {
        width: 800px !important;
        height: 600px !important;
        margin: auto !important;
    }
    #modal_window_img {
        width: 360px !important;
        height: 400px !important;
        margin: auto !important;
    }
    #btn_del_img {
        display: none;
        position: fixed;
        top: 20px;
        left: 30px;
        cursor: pointer;
    }
    #btn_close_img {
        right: 0px;
    }

    @media screen and (min-width: 768px) {
        #btn_close_img {
            right: 30px;
        }
        #modal_window_img {
            width: 800px !important;
            height: 600px !important;
            margin: auto !important;
        }

    }
</style>
<?=parent::getButtonBack();?>

<link rel="stylesheet" href="components/com_gm_ceiling/views/mountersorder/tmpl/CSS/style.css" type="text/css" />
<div id="preloader" style="display: none;" class="PRELOADER_GM PRELOADER_GM_OPACITY">
    <div class="PRELOADER_BLOCK"></div>
    <img src="/images/GM_R_HD.png"  alt = 'preloader' class="PRELOADER_IMG">
</div>
<div id="content-tar">
    <h2 class="center tar-color-414099">Просмотр проекта №<?php echo $project; ?></h2>
    <ul class="nav nav-tabs" role="tablist" id="tabs">
        <li class="nav-item">
            <a class="nav-link active" data-toggle="tab" href="#summary" role="tab">Общее</a>
        </li>
        <?php if (!empty($calculation_ids)) {
            ?>
            <?php foreach ($calculation_ids as $value) { ?>
                <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#ceiling<?php echo $value->id; ?>" role="tab"><?php echo $value->calculation_title; ?></a>
                </li>
            <?php } ?>
        <?php } ?>
    </ul>
    <div class="tab-content" id="content-tabs">
        <div id="summary" class="content-tab tab-pane active" role="tabpanel">
            <div class = "overflow">
                <table id="table-all-orders" cols=4>
                    <tr class="caption">
                        <td>Наименование</td>
                        <td>Цена, ₽</td>
                        <td>Количество</td>
                        <td>Стоимость, ₽</td>
                    </tr>
                    <?php foreach ($AllCalc as $val) { ?>
                        <tr>
                            <td class="left"><?php echo $val["title"]; ?></td>
                            <?php if ($user->dealer_id == 1) { ?>
                                <td><?php echo $val["gm_salary"]; ?></td>
                            <?php } else { ?>
                                <td><?php echo $val["dealer_salary"]; ?></td>
                            <?php } ?>
                            <td><?php echo $val["quantity"]; ?></td>
                            <?php if ($user->dealer_id == 1) { ?>
                                <td><?php echo $val["gm_salary_total"]; ?></td>
                                <?php $AllSum += $val["gm_salary_total"]; ?>
                            <?php } else { ?>
                                <td><?php echo $val["dealer_salary_total"]; ?></td>
                                <?php $AllSum += $val["gm_salary_total"]; ?>
                            <?php } ?>
                        </tr>
                    <?php } ?>
                    <tr class="caption">
                        <td colspan=3 style="text-align: right;">Итого, ₽:</td>
                        <td id="sum-all"><?php echo $AllSum; ?></td>
                    </tr>
                    <?php if(!empty($all_extra_mounting)){?>
                        <tr class="caption">
                            <td colspan="4" style="text-align: center; background-color: #ffffff;">
                                Дополнительные работы
                            </td>
                        </tr>
                        <tr class="caption">
                            <td colspan="2">Название</td>
                            <td colspan="2">Стоимость, ₽</td>
                        </tr>
                        <?php foreach ($all_extra_mounting as $mount){?>
                            <tr>
                                <td colspan="2">
                                    <?=$mount->title;?>
                                </td>
                                <td colspan="2">
                                    <?=$mount->price;?>
                                </td>
                            </tr>
                        <?php } ?>
                        <tr class="caption">
                            <td colspan=3 style="text-align: right;">Итого, ₽:</td>
                            <td id="sum-all"><?=$extra_mount_sum;?></td>
                        </tr>
                    <?php }
                    if (!empty($DataOfTransport)) { ?>
                        <tr class="caption">
                            <td colspan="4" style="text-align: center; background-color: #ffffff;">
                                Транспортные расходы
                            </td>
                        </tr>
                        <tr class="caption">
                            <td>Вид транспорта</td>
                            <td>Кол-во км</td>
                            <td>Кол-во выездов</td>
                            <td>Стоимость, ₽</td>
                        </tr>
                        <tr>
                            <td>
                                <?php echo $DataOfTransport["transport"]; ?>
                            </td>
                            <td>
                                <?php echo $DataOfTransport["distance"]; ?>
                            </td>
                            <td>
                                <?php echo $DataOfTransport["distance_col"]; ?>
                            </td>
                            <td>
                                <?php echo $DataOfTransport["mounter_sum"]; ?>
                            </td>
                        </tr>
                    <?php } ?>
                </table>
            </div>
        </div>
        <?php foreach ($calculation_ids as $value) { ?>
            <div id="ceiling<?php echo $value->id; ?>" class="content-tab tab-pane" role="tabpanel">
                <?php
                $calculation = $calculationModel->getDataById($value->id);
                $calculation->n13 = $modelCalcform->n13_load($calculation->id);
                $calculation->n14 = $modelCalcform->n14_load($calculation->id);
                $calculation->n15 = $modelCalcform->n15_load($calculation->id);
                $calculation->n22 = $modelCalcform->n22_load($calculation->id);
                $calculation->n23 = $modelCalcform->n23_load($calculation->id);
                $calculation->n26 = $modelCalcform->n26_load($calculation->id);
                $calculation->n29 = $modelCalcform->n29_load($calculation->id);
                $calculation->n19 = $modelCalcform->n19_load($calculation->id);
                $calculation->n45 = $modelCalcform->n45_load($calculation->id);
                /*----new-----*/
                $allGoods = $modelCalcform->getGoodsPricesInCalculation($calculation->id,$user->dealer_id);
                if(!empty($calculation->cancel_metiz)){
                    $calculation->goods = Gm_ceilingHelpersGm_ceiling::deleteMetizFromGoods($allGoods);
                }
                else{
                    $calculation->goods = $allGoods;
                }
                $calculation->jobs = $modelCalcform->getJobsPricesInCalculation($calculation->id, 1);

                /*------------*/
                $dir_before = 'uploaded_calc_images/'.$value->id.'/before';
                $dir_after = 'uploaded_calc_images/'.$value->id.'/after';
                $dir_defect = 'uploaded_calc_images/'.$value->id.'/defect';
                $files = [];
                $temp = [];
                if (is_dir($dir_before)) {
                    $temp = scandir($dir_before);
                    foreach ($temp as $key => $value1) {
                        if (strlen($value1) === 32) {
                            $temp[$key] = $dir_before.'/'.$value1;
                        } else {
                            unset($temp[$key]);
                        }
                    }
                    $files = array_merge($files, $temp);
                }
                if (is_dir($dir_after)) {
                    $temp = scandir($dir_after);
                    foreach ($temp as $key => $value1) {
                        if (strlen($value1) === 32) {
                            $temp[$key] = $dir_after.'/'.$value1;
                        } else {
                            unset($temp[$key]);
                        }
                    }
                    $files = array_merge($files, $temp);
                }
                if (is_dir($dir_defect)) {
                    $temp = scandir($dir_defect);
                    foreach ($temp as $key => $value1) {
                        if (strlen($value1) === 32) {
                            $temp[$key] = $dir_defect.'/'.$value1;
                        } else {
                            unset($temp[$key]);
                        }
                    }
                    $files = array_merge($files, $temp);
                }

                if (empty($files)) {
                    $col1 = 0;
                    $col2 = 5;
                } else {
                    $col1 = 8;
                    $col2 = 4;
                }
                ?>

                <div class="row">
                    <div class="col-md-<?=$col1?>">
                        <div class="row div_imgs">
                            <?php
                            foreach ($files as $value1) {
                                echo '<img src="'.$value1.'" data-path="'.str_replace('uploaded_calc_images/', '', $value1).'" class="uploaded_calc_img">';
                            }
                            ?>
                        </div>
                    </div>
                    <div class="col-md-<?=$col2;?>">
                        <textarea class="inputactive" readonly name="calc_comment" rows="5" ><?=$calculation->comment?></textarea>
                    </div>
                </div>
                <?php if (!empty($value->details)) { ?>
                    <div>
                        Примечание к потолку: <?php echo $value->details; ?>
                    </div>
                <?php } ?>
                <div class="col-md-8">
                    <div class="ceiling">
                        <img src="/calculation_images/<?php echo md5("calculation_sketch".$value->id); ?>.svg" class="image-ceiling">
                    </div>
                </div>
                <div class="col-md-4">
                    <?php if (!empty($calculation->n3)){
                        $canvas = $canvas_model->getFilteredItemsCanvas("`a`.`id` = $calculation->n3");?>
                        <h4>Материал</h4>
                        <table class="table_info2">
                            <tr>
                                <td>
                                    <?php echo $canvas[0]->texture_title.' '.$canvas[0]->name.' '.$canvas[0]->width;?>
                                </td>
                            </tr>
                            <?php
                            if (!empty($canvas[0]->color_id)) {
                                ?>
                                <tr>
                                    <td>Цвет:</td>
                                    <td>
                                        <?php echo $canvas[0]->color_title; ?>
                                        <img src="/<?php echo $canvas[0]->color_file; ?>" alt=""/>
                                    </td>
                                </tr>
                            <?php } ?>
                        </table>
                    <?php }
                    else{
                        $canvas = null;
                        foreach($calculation->goods as $goods){
                            if($goods->category_id == 1){
                                $canvas = $goods;
                                break;
                            }
                        }
                        $detailed_canvas = '';
                        if (!empty($canvas)) {
                            $filter = "id = " . $canvas->goods_id;
                            $detailed_canvas = $canvas_model->getFilteredItemsCanvas($filter);
                        }
                        $color = $detailed_canvas[0]->color;
                        $hex = $detailed_canvas[0]->hex;
                        if (!empty($detailed_canvas)){?>
                            <h4>Материал</h4>
                            <table class="table_info2">
                                <tr>
                                    <td colspan="2">
                                        <?php echo $detailed_canvas[0]->name?>
                                    </td>
                                </tr>
                                <tr>
                                    <td style='width:20%'>Цвет:</td>
                                    <td style='width:80%'>
                                        <div class="col-md-3"><?=$color;?></div>
                                        <div class="col-md-9" style="background-color:<?="#".$hex;?>;color:<?="#".$hex;?>"><?=$color;?></div>
                                    </td>
                                </tr>

                            </table>
                        <?php }
                    }?>
                    <h4 style="margin: 10px 0;">Размеры помещения</h4>
                    <table class="table_info2">
                        <tr>
                            <td>Площадь, м<sup>2</sup>:</td>
                            <td><?php echo $calculation->n4; ?></td>
                        </tr>
                        <tr>
                            <td>Периметр, м:</td>
                            <td><?php echo $calculation->n5; ?></td>
                        </tr>
                    </table>
                    <?php if(empty($allGoods)) { ?>
                        <h4 style="margin: 10px 0;">Профиль</h4>
                        <?php switch ($calculation->n28) {
                            case 0:
                                $profil = "Отсутствует";
                                break;
                            case 1:
                                $profil = "Потолочный Al";
                                break;
                            case 2:
                                $profil = "Стеновой Al";
                                break;
                            case 3:
                                $profil = "Стеновой ПВХ";
                                break;
                            case 4:
                                $profil = "KRAAB";
                                break;
                        } ?>
                        <table class="table_info2">
                            <tr>
                                <td><?php echo $profil; ?></td>
                            </tr>
                        </table>
                        <?php if (!empty(floatval($calculation->remove_n28)) || !empty(floatval($calculation->n41))) { ?>
                            <h4 style="margin: 10px 0;">Демонтаж</h4>
                            <table class="table_info2">
                                <?php if (!empty(floatval($calculation->remove_n28))) { ?>
                                    <tr>
                                        <th>Демонтаж профиля, м:</th>
                                        <td><?php echo $calculation->remove_n28; ?></td>
                                    </tr>
                                <?php } ?>
                                <?php if (!empty(floatval($calculation->n41))) { ?>
                                    <tr>
                                        <th>Демонтаж потолка:</th>
                                        <td>нужен</td>
                                    </tr>
                                <?php } ?>
                            </table>
                        <?php } ?>
                        <?php if ($calculation->n6 > 0) { ?>
                            <h4 style="margin: 10px 0;">Вставка</h4>
                            <table class="table_info2">
                                <tr>
                                    <?php if ($calculation->n6 == 314) { ?>
                                        <td>Белая</td>
                                        <td></td>
                                        <?php
                                    } else {
                                        $color = $components_model->getColorId($calculation->n6);
                                        ?>
                                        <td>Цветная:</td>
                                        <td>
                                            <?php echo $color->title; ?> <img style='width: 50px; height: 30px;'
                                                                              src="/<?php echo $color->file; ?>"/>
                                        </td>
                                    <?php } ?>
                                </tr>
                            </table>
                        <?php } ?>
                        <?php if ($calculation->n12) { ?>
                            <h4 style="margin: 10px 0;">Установка люстры</h4>
                            <table class="table_info2">
                                <tr>
                                    <td><?php echo $calculation->n12; ?> шт.</td>
                                    <td></td>
                                </tr>
                            </table>
                        <?php }
                        ?>
                        <?php if ($calculation->n13) { ?>
                            <h4 style="margin: 10px 0;">Установка светильников</h4>
                            <table class="table_info2">
                                <?php
                                foreach ($calculation->n13 as $key => $n13_item) {
                                    echo "<tr><td><b>Количество:</b> " . $n13_item->n13_count . " шт - <b>Тип:</b>  " . $n13_item->type_title . " - <b>Размер:</b> " . $n13_item->component_title . "</td></tr>";
                                }
                                ?>
                            </table>
                        <?php } ?>
                        <?php if ($calculation->n26) { ?>
                            <h4 style="margin: 10px 0;">Светильники Гильдии Мастеров</h4>
                            <table class="table_info2">
                                <?php
                                foreach ($calculation->n26 as $key => $n26_item) {
                                    echo "<tr><td><b>Количество:</b> " . $n26_item->n26_count . " шт - <b>Тип:</b>  " . $n26_item->component_title_illum . " -  <b>Лампа:</b> " . $n26_item->component_title . "</td></tr>";
                                }
                                ?>
                            </table>
                        <?php } ?>
                        <?php if ($calculation->n14) { ?>
                            <h4 style="margin: 10px 0;">Обвод трубы</h4>
                            <table class="table_info2">
                                <?php
                                foreach ($calculation->n14 as $key => $n14_item) {
                                    echo "<tr><td><b>Количество:</b> " . $n14_item->n14_count . " шт  -  <b>Диаметр:</b>  " . $n14_item->component_title . "</td></tr>";
                                }
                                ?>
                            </table>
                        <?php } ?>
                        <?php if ($calculation->n27 > 0) { ?>
                            <h4 style="margin: 10px 0;">Шторный карниз</h4>
                            <?php if ($calculation->n16) {
                                switch ($calculation->niche) {
                                    case 1:
                                        $niche_title = "Открытая ниша";
                                        break;
                                    case 2:
                                        $niche_title = "Закрытая ниша";
                                        break;
                                    case 3:
                                        $niche_title = "Ниша с пластиком 100мм";
                                        break;
                                    case 4:
                                        $niche_title = "Ниша с пластиком 150мм";
                                        break;
                                    case 5:
                                        $niche_title = "Ниша с пластиком 200мм";
                                        break;
                                }
                                ?>
                                <table class="table_info2">
                                    <tr>
                                        <td><?php echo $niche_title ?></td>
                                        <td><?php echo $calculation->n27; ?> м.</td>
                                    </tr>
                                </table>
                            <?php } else { ?>
                                <table class="table_info2">
                                    <tr>
                                        <td><?php echo "Обычный шторный карниз" ?></td>
                                        <td><?php echo $calculation->n27; ?> м.</td>
                                    </tr>
                                </table>
                                <?php
                            }
                        } ?>

                        <?php if ($calculation->n15) { ?>
                            <h4 style="margin: 10px 0;">Шторный карниз Гильдии мастеров</h4>
                            <table class="table_info2">
                                <?php
                                foreach ($calculation->n15 as $key => $n15_item) {
                                    echo "<tr><td><b>Количество:</b> " . $n15_item->n15_count . " шт - <b>Тип:</b>   " . $n15_item->type_title . " <b>Длина:</b> " . $n15_item->component_title . "</td></tr>";
                                }
                                ?>
                            </table>
                        <?php } ?>

                        <?php if ($calculation->n22 || $calculation->n22 || $calculation->n42) { ?>
                            <h4 style="margin: 10px 0;">Вентиляция</h4>
                            <?php if ($calculation->n22) { ?>
                                <table class="table_info2">
                                    <?php
                                    foreach ($calculation->n22 as $key => $n22_item) {
                                        echo "<tr><td><b>Количество:</b> " . $n22_item->n22_count . " шт - <b>Тип:</b>   " . $n22_item->type_title . " - <b>Размер:</b> " . $n22_item->component_title . "</td></tr>";
                                    }
                                    ?>
                                </table>
                            <?php } ?>
                            <?php if ($calculation->n22_1) { ?>
                                <table class="table_info2">
                                    <tr>
                                        <th>Пластиковый короб, м</th>
                                        <td><?php echo $calculation->n22_1; ?></td>
                                    </tr>
                                </table>
                            <?php } ?>
                            <?php if ($calculation->n42) { ?>
                                <table class="table_info2">
                                    <tr>
                                        <th>Вытяжка(наклейка кольца), шт</th>
                                        <td><?php echo $calculation->n42; ?></td>
                                    </tr>
                                </table>
                            <?php } ?>
                        <?php } ?>
                        <?php if ($calculation->n23) { ?>
                            <h4 style="margin: 10px 0;">Диффузор</h4>
                            <table class="table_info2">
                                <?php
                                foreach ($calculation->n23 as $key => $n23_item) {
                                    echo "<tr><td><b>Количество:</b> " . $n23_item->n23_count . " шт - <b>Размер:</b>  " . $n23_item->component_title . "</td></tr>";
                                }
                                ?>
                            </table>
                        <?php } ?>
                        <?php if ($calculation->n29) { ?>
                            <h4 style="margin: 10px 0;">Переход уровня</h4>
                            <table class="table_info2">
                                <?php
                                foreach ($calculation->n29 as $key => $n29_item) {
                                    echo "<tr><td><b>Количество:</b> " . $n29_item->n29_count . " м - <b>Тип:</b>  " . $n29_item->type_title . "</td></tr>";
                                }
                                ?>
                            </table>
                        <?php } ?>
                        <?php if (!empty($calculation->n45)) { ?>
                            <h4 style="margin: 10px 0;">Световые линии</h4>
                            <table class="table_info2">
                                <?php
                                foreach ($calculation->n45 as $key => $n45_item) {
                                    echo "<tr><td><b>Количество:</b> " . $n45_item->n45_count . " м - <b>Тип:</b>  " . $n45_item->component_title . "</td></tr>";
                                }
                                ?>
                            </table>
                        <?php } ?>
                        <?php if ($calculation->n19) { ?>
                            <h4 style="margin: 10px 0;">Провода</h4>
                            <table class="table_info2">
                                <?php
                                foreach ($calculation->n19 as $key => $n19_item) {
                                    echo "<tr><td><b>Количество:</b> " . $n19_item->count . " м - <b>Тип:</b>   " . $n19_item->wire_title . "</td></tr>";
                                }
                                ?>
                            </table>
                        <?php } ?>
                        <h4 style="margin: 10px 0;">Прочее</h4>
                        <table class="table_info2">
                            <?php if ($calculation->n9 > 0) { ?>
                                <tr>
                                    <td>Углы, шт.:</td>
                                    <td><?php echo $calculation->n9; ?></td>
                                </tr>
                            <?php } ?>
                            <?php if ($calculation->n10 > 0) { ?>
                                <tr>
                                    <td> Криволинейный участок, м:</td>
                                    <td><?php echo $calculation->n10; ?></td>
                                </tr>
                            <?php } ?>
                            <?php if ($calculation->n11 > 0) { ?>
                                <tr>
                                    <td>Внутренний вырез, м:</td>
                                    <td><?php echo $calculation->n11; ?></td>
                                </tr>
                            <?php } ?>
                            <?php if ($calculation->n7 > 0) { ?>
                                <tr>
                                    <td>Крепление в плитку, м:</td>
                                    <td><?php echo $calculation->n7; ?></td>
                                </tr>
                            <?php } ?>
                            <?php if ($calculation->n8 > 0) { ?>
                                <tr>
                                    <td>Крепление в керамогранит, м:</td>
                                    <td><?php echo $calculation->n8; ?></td>
                                </tr>
                            <?php } ?>
                            <?php if ($calculation->n17 > 0) { ?>
                                <tr>
                                    <td>Закладная брусом, м:</td>
                                    <td><?php echo $calculation->n17; ?></td>
                                </tr>
                            <?php } ?>
                            <?php if ($calculation->n18 > 0) { ?>
                                <tr>
                                    <td> Усиление стен, м:</td>
                                    <td><?php echo $calculation->n18; ?></td>
                                </tr>
                            <?php } ?>
                            <?php if ($calculation->n20 > 0) { ?>
                                <tr>
                                    <td>Разделитель, м:</td>
                                    <td><?php echo $calculation->n20; ?></td>
                                </tr>
                            <?php } ?>
                            <?php if ($calculation->n20_1 > 0) { ?>
                                <tr>
                                    <td>Отбойник, м:</td>
                                    <td><?php echo $calculation->n20_1; ?></td>
                                </tr>
                            <?php } ?>
                            <?php if ($calculation->n21 > 0) { ?>
                                <tr>
                                    <td>Пожарная сигнализация, шт:</td>
                                    <td><?php echo $calculation->n21; ?></td>
                                </tr>
                            <?php } ?>
                            <?php if ($calculation->dop_krepezh > 0) { ?>
                                <tr>
                                    <td>Дополнительный крепеж:</td>
                                    <td><?php echo $calculation->dop_krepezh; ?></td>
                                </tr>
                            <?php } ?>
                            <?php if ($calculation->n24 > 0) { ?>
                                <tr>
                                    <td>Сложность доступа к месту монтажа, м:</td>
                                    <td><?php echo $calculation->n24; ?></td>
                                </tr>
                            <?php } ?>
                            <?php if ($calculation->n30 > 0) { ?>
                                <tr>
                                    <td>Парящий потолок, м:</td>
                                    <td><?php echo $calculation->n30; ?></td>
                                </tr>
                            <?php } ?>
                            <?php if ($calculation->n32 > 0) { ?>
                                <tr>
                                    <td>Слив воды, кол-во комнат:</td>
                                    <td><?php echo $calculation->n32; ?></td>
                                </tr>
                            <?php } ?>
                            <?php if ($calculation->n22_1 > 0) { ?>
                                <tr>
                                    <td>Пластиковый короб:</td>
                                    <td><?php echo $calculation->n22_1; ?></td>
                                </tr>
                            <?php } ?>
                            <?php if ($calculation->n33 > 0) { ?>
                                <tr>
                                    <td>Лючок:</td>
                                    <td><?php echo $calculation->n33; ?></td>
                                </tr>
                            <?php } ?>
                            <?php if ($calculation->n33_2 > 0) { ?>
                                <tr>
                                    <td>Большой люк:</td>
                                    <td><?php echo $calculation->n33_2; ?></td>
                                </tr>
                            <?php } ?>
                            <?php if ($calculation->n34 > 0) { ?>
                                <tr>
                                    <td>Диодная лента:</td>
                                    <td><?php echo $calculation->n34; ?></td>
                                </tr>
                            <?php } ?>
                            <?php if ($calculation->n34_2 > 0) { ?>
                                <tr>
                                    <td>Блок питания диод.ленты:</td>
                                    <td><?php echo $calculation->n34_2; ?></td>
                                </tr>
                            <?php } ?>
                            <?php if ($calculation->n35 > 0) { ?>
                                <tr>
                                    <td>Контурный профиль:</td>
                                    <td><?php echo $calculation->n35; ?></td>
                                </tr>
                            <?php } ?>
                            <?php if ($calculation->n36 > 0) { ?>
                                <tr>
                                    <td>Перегарпунка, м:</td>
                                    <td><?php echo $calculation->n36; ?></td>
                                </tr>
                            <?php } ?>
                            <?php if ($calculation->n37) { ?>
                                <tr>
                                    <td>Фотопечать, м<sup>2</sup>:</td>
                                    <td><?php echo json_decode($calculation->n37)->square; ?></td>
                                </tr>
                            <?php } ?>
                            <?php if ($calculation->n38) { ?>
                                <tr>
                                    <td>Ремонт потолка, шт:</td>
                                    <td><?php echo $calculation->n38; ?></td>
                                </tr>
                            <?php } ?>
                            <?php if (!empty(floatval($calculation->n39))) { ?>
                                <tr>
                                    <td>Лента на шторный карниз, м:</td>
                                    <td><?php echo $calculation->n39; ?></td>
                                </tr>
                            <?php } ?>
                            <?php if ($calculation->n40) { ?>
                                <tr>
                                    <td>Закругления на шторный карниз, шт:</td>
                                    <td><?php echo $calculation->n40; ?></td>
                                </tr>
                            <?php } ?>
                        </table>
                        <?php $extra_mounting = (array)json_decode($calculation->extra_mounting); ?>
                        <?php if (!empty($extra_mounting)) { ?>
                            <h4 style="margin: 10px 0;">Дополнительные работы</h4>
                            <table class="table_info2">
                                <?php
                                foreach ($extra_mounting as $dop) {
                                    echo "<tr><td><b>Название:</b></td><td>" . $dop->title . "</td></tr>";
                                }
                                ?>
                            </table>
                        <?php }
                    }?>
                    <?php if(!empty($calculation->goods)){?>
                        <h4 style="margin: 10px 0;cursor: pointer;" class="calc_goods"><i class="fas fa-angle-down"></i> Комплектующие</h4>
                        <table class="table_info2 table_goods" style="display:none;">
                            <thead>
                            <th>Название</th>
                            <th>Количество</th>
                            </thead>
                            <tbody>
                            <?php
                            foreach ($calculation->goods as $goods){
                                if($goods->category_id != 1){
                                    echo "<tr><td>$goods->name</td><td>$goods->final_count</td></tr>";
                                }
                            }
                            ?>
                            </tbody>
                        </table>
                    <?php }?>
                    <?php if(!empty($calculation->jobs)){?>
                        <h4 style="margin: 10px 0;cursor: pointer;" class="mount_jobs"><i class="fas fa-angle-down"></i> Монтажные работы</h4>
                        <table class="table_info2 table_jobs" style="display:none;">
                            <thead>
                            <th>Название</th>
                            <th>Количество</th>
                            </thead>
                            <tbody>
                            <?php foreach ($calculation->jobs as $job){
                                if($full){
                                    if(!$job->guild_only && !$job->is_factory_work){
                                        echo "<tr><td>$job->name</td><td>".round($job->final_count,2)."</td></tr>";
                                    }
                                }
                                else{
                                    if(!$job->guild_only && !$job->is_factory_work && in_array($job->mount_type_id,$stages)){
                                        echo "<tr><td>$job->name</td><td>".round($job->final_count,2)."</td></tr>";
                                    }
                                }
                            }
                            ?>
                            </tbody>
                        </table>
                    <?php }?>
                </div>

                <div class = "overflow">
                    <table id="table-order-<?php echo $value->id; ?>" cols=4 class="table-order">
                        <tr class="caption">
                            <td>Наименование</td>
                            <td>Цена, ₽</td>
                            <td>Количество</td>
                            <td>Стоимость, ₽</td>
                        </tr>
                        <?php
                        $calc_sum_stage = 0;
                        if (!empty($DataOfProject[$value->id])) {
                            foreach ($stages as $stage) {
                                if (isset($DataOfProject[$value->id]["mounting_data"])){
                                    foreach ($DataOfProject[$value->id]["mounting_data"] as $val) {
                                        if ($val['stage'] == $stage || $stage == 1) { ?>
                                            <tr>
                                                <td class="left">
                                                    <?php echo $val["title"]; ?>
                                                </td>
                                                <?php if ($user->dealer_id == 1) { ?>
                                                    <td>
                                                        <?php echo $val["gm_salary"]; ?>
                                                    </td>
                                                <?php } else { ?>
                                                    <td>
                                                        <?php echo $val["dealer_salary"]; ?>
                                                    </td>
                                                <?php } ?>
                                                <td>
                                                    <?php echo $val["quantity"]; ?>
                                                </td>
                                                <?php if ($user->dealer_id == 1) { ?>
                                                    <td>

                                                        <?php
                                                        $calc_sum_stage += $val["gm_salary_total"];
                                                        echo $val["gm_salary_total"];
                                                        ?>
                                                    </td>
                                                <?php } else { ?>
                                                    <td>
                                                        <?php
                                                        $calc_sum_stage += $val["dealer_salary_total"];
                                                        echo $val["dealer_salary_total"];
                                                        ?>
                                                    </td>
                                                <?php } ?>
                                            </tr>
                                            <?php
                                        }
                                    }
                                }
                                else{
                                    foreach ($DataOfProject[$value->id] as $val) {
                                        if ($val->moount_type_id == $stage || $stage == 1) { ?>
                                            <tr>
                                                <td class="left">
                                                    <?php echo $val->name; ?>
                                                </td>
                                                <td>
                                                    <?php echo $val->price; ?>
                                                </td>
                                                <td>
                                                    <?php echo $val->final_count; ?>
                                                </td>
                                                <td>
                                                    <?php
                                                    $calc_sum_stage += $val->price_sum;
                                                    echo $val->price_sum;
                                                    ?>
                                                </td>

                                            </tr>
                                            <?php
                                        }
                                    }
                                }
                            }
                            ?>
                            <tr class="caption">
                                <td colspan=3 style="text-align: right;">Итого, ₽:</td>
                                <td>
                                    <?php echo $calc_sum_stage;?>
                                </td>
                            </tr>
                        <?php } ?>
                    </table>
                </div>
            </div>
        <?php } ?>
    </div>
    <div id="buttons-cantainer">
        <button id="begin" class="btn" disabled><i class="fa fa-play fa-tar" aria-hidden="true" ></i> Монтаж начат</button>
        <button id="complited" class="btn modal" disabled><i class="fa fa-check" aria-hidden="true" ></i> Монтаж выполнен</button>
        <button id="underfulfilled" class="btn modal" disabled><i class="fa fa-pause fa-tar" aria-hidden="true" ></i> Монтаж недовыполнен</button>
    </div>
    <div id="modal-window-container-tar">
        <button id="close-tar" type="button"><i class="fa fa-times fa-times-tar" aria-hidden="true"></i></button>
        <div id="modal-window-1-tar" style="width: 86%; margin: auto;">

            <?php foreach ($calculation_ids as $value) { ?>
                <div id="div-images-block">
                    <div class="row">
                        <div class="col-md-1 col-sm-0"></div>
                        <div class="col-md-5 col-sm-12">Изображение для "<?= $value->calculation_title; ?>"</div>
                        <div class="col-md-5 col-sm-12"><input type="file" class="img_file" data-calc-id="<?= $value->id; ?>" data-img-type="after" multiple accept="image/*"></div>
                        <div class="col-md-1 col-sm-0"></div>
                    </div>
                    <hr>
                </div>
            <?php } ?>
            <p>Введите примечание:</p>
            <p>
                <textarea id="note" style="min-width: 50px; width: 90%"></textarea>
            </p>
            <div id="warning">
                <p>Введите примечание</p>
            </div>

            <p><button type="button" id="save" class="btn btn-primary">Ок</button></p>
        </div>
    </div>
    <div class="modal_window_container" id="img_modal_container">
        <button type="button" class="close_btn" id="btn_close_img"><i class="fa fa-times fa-times-tar" aria-hidden="true"></i></button>
        <button type="button" class="close_btn" id="btn_del_img"><i class="fa fa-trash" aria-hidden="true"></i> Удалить изображение</button>
        <div class="modal_window" id="modal_window_img" style="border: 2px solid black; border-radius: 4px;"></div>
    </div>

</div>

<script type="text/javascript">
    var stages = JSON.parse('<?php echo json_encode($stages);?>'),
        stage = Math.max.apply(Math,stages);
    var url_proj = '<?php echo $project; ?>';
    // функция получения текущего времени
    var date;
    function CurrentDateTime() {
        var now = new Date();
        var year = String(now.getFullYear());
        var month = String(now.getMonth());
        month++;
        if (month.length == 1) {
            month = "0"+month;
        }
        var day = String(now.getDate());
        if (day.length == 1) {
            day = "0"+day;
        }
        var hour = String(now.getHours());
        if (hour.length == 1) {
            hour = "0"+hour;
        }
        var minute = String(now.getMinutes());
        if (minute.length == 1) {
            minute = "0"+minute;
        }
        var second = String(now.getSeconds());
        if (second.length == 1) {
            second = "0"+second;
        }
        date = year+"-"+month+"-"+day+" "+hour+":"+minute+":"+second;
        return date;
    }

    jQuery(document).ready(function() {

        //отправка ajax для проверки дат начала конца монтажа и статуса
        jQuery.ajax({
            type: "POST",
            url: "index.php?option=com_gm_ceiling&task=mountersorder.GetDates",
            dataType: 'json',
            data: {
                url_proj: url_proj,
                stage: stage
            },
            success: function(msg) {
                console.log(msg);
                start = msg[0].project_mounting_start;
                end = msg[0].project_mounting_end;
                status_mount = msg[0].project_status;
                console.log(status_mount);
                switch(status_mount){
                    case '10':
                    case '19':
                        if(stage == 1 || stages.indexOf("2")>=0){
                            jQuery("#begin").attr("disabled", false);
                        }
                        break;
                    case '16':
                        if(stage == 1){
                            jQuery("#complited").attr("disabled", false);
                            jQuery("#underfulfilled").attr("disabled",false);
                        }
                        break;
                    case '24':
                        if(stage == 3){
                            jQuery("#begin").attr("disabled", false);
                        }
                        break;
                    case '25':
                        if(stage == 4){
                            jQuery("#begin").attr("disabled", false);
                        }
                        break;
                    case '26':
                        jQuery("#begin").attr("disabled", "disabled");
                        jQuery("#complited").attr("disabled", "disabled");
                        break;
                    case '27':
                        if(stage == 2){
                            jQuery("#begin").attr("disabled", "disabled");
                            jQuery("#complited").attr("disabled", false);
                        }
                        break;
                    case '28':
                        if(stage == 3){
                            jQuery("#begin").attr("disabled", "disabled");
                            jQuery("#complited").attr("disabled", false);
                        }
                        break;
                    case '29':
                        if(stage == 4){
                            jQuery("#begin").attr("disabled", "disabled");
                            jQuery("#complited").attr("disabled", false);
                        }
                        break;
                }
                /*if (status_mount == 27 || status_mount == 28 || status_mount == 29 ) {
                    jQuery("#begin").attr("disabled", "disabled");
                    jQuery("#complited").attr("disabled", false);
                    jQuery("#underfulfilled").attr("disabled", false);
                } else if (status_mount == 10 || status_mount == 19) {
                    jQuery("#begin").attr("disabled", false);
                    jQuery("#complited").attr("disabled", "disabled");
                    jQuery("#underfulfilled").attr("disabled", "disabled");
                } else if (status_mount == 16 || status_mount == 24 || status_mount == 25 || status_mount == 26 ) {
                    jQuery("#complited").attr("disabled", false);
                    jQuery("#underfulfilled").attr("disabled", false);
                }*/
            },
            error: function(data){
                console.log(data);
            }
        });

        //скрыть модальное окно
        jQuery("#close-tar").click(function(e) {
            jQuery("#close-tar").hide();
            jQuery("#modal-window-container-tar").hide();
            jQuery("#modal-window-1-tar").hide();
        });

        jQuery(document).on('click','.calc_goods',function(){
            jQuery(".table_goods").toggle();
            var i = jQuery(this).find('i');
            if(i.hasClass('fa-angle-down')){
                i.removeClass("fa-angle-down").addClass("fa-angle-up");
            }
            else if(i.hasClass('fa-angle-up')){
                i.removeClass("fa-angle-up").addClass("fa-angle-down");
            }
        });

        jQuery(document).on('click','.mount_jobs',function(){
            jQuery(".table_jobs").toggle();
            var i = jQuery(this).find('i');
            if(i.hasClass('fa-angle-down')){
                i.removeClass("fa-angle-down").addClass("fa-angle-up");
            }
            else if(i.hasClass('fa-angle-up')){
                i.removeClass("fa-angle-up").addClass("fa-angle-down");
            }

        });
        //  кнопка "монтаж начат"
        jQuery("#begin").click(function() {
            CurrentDateTime();
            jQuery.ajax({
                type: "POST",
                url: "index.php?option=com_gm_ceiling&task=mountersorder.MountingStart",
                dataType: 'json',
                data: {
                    date: date,
                    url_proj: url_proj,
                    stage: stage
                },
                success: function(msg) {
                    if (msg[0].project_status == 16) {
                        window.location.href = "/index.php?option=com_gm_ceiling&&view=mounterscalendar"
                    }
                }
            });
        });

        jQuery('.img_file').change(function() {
            var elem_file = jQuery(this)[0];
            var n = noty({
                theme: 'relax',
                type: 'alert',
                layout: 'topCenter',
                text: '<input type="radio" value="after" name="img_type" style="margin-top: 10px; cursor: pointer;" checked> После<br>'+
                    '<input type="radio" value="defect" name="img_type" style="margin-top: 10px; cursor: pointer;"> Дефект<br>',
                modal: true,
                buttons:[
                    {
                        addClass: 'btn btn-primary', text: 'Ок', onClick: function($noty) {
                            elem_file.setAttribute('data-img-type', jQuery('[name="img_type"]:checked').val());
                            $noty.close();
                        }
                    },
                    {
                        addClass: 'btn btn-primary', text: 'Отмена', onClick: function($noty) {
                            $noty.close();
                        }
                    }
                ]
            }).show();
            document.getElementsByClassName('noty_message')[0].style.textAlign = 'left';
            document.getElementsByClassName('noty_message')[0].style.paddingLeft = '30%';
            document.getElementsByClassName('noty_message')[0].style.fontSize = '14pt';
            document.getElementsByClassName('noty_buttons')[0].style.textAlign = 'center';
        });

        // узнаем какая кнопка, открываем модальное окно
        jQuery("#buttons-cantainer").on("click", ".modal", function() {
            whatBtn = this.id;
            jQuery("#close-tar").show();
            jQuery("#modal-window-container-tar").show();
            jQuery("#modal-window-1-tar").show("slow");
            if (whatBtn === "complited") {
                jQuery('div-images-block').show();
            } else {
                jQuery('div-images-block').hide();
            }
        });

        // получение значений из селектов
        jQuery("#modal-window-container-tar").on("click", "#save", function() {
            var note = jQuery("#note").val();

            if (whatBtn == "complited") {

                // кнопка "монтаж выполнен"
                CurrentDateTime();

                var formData = new FormData();
                var elemsFiles = document.getElementsByClassName('img_file');
                var arrayCalcImages = [];
                for (var obj,i = elemsFiles.length; i--;) {
                    if (elemsFiles[i].files.length < 1) {
                        continue;
                    }
                    obj = {
                        calc_id: elemsFiles[i].getAttribute('data-calc-id'),
                        type: elemsFiles[i].getAttribute('data-img-type'),
                        images: []
                    }

                    jQuery.each(elemsFiles[i].files, function(key, value) {
                        console.log(key);
                        obj.images.push(value.name);
                        formData.append(+elemsFiles[i].getAttribute('data-calc-id')+ +key, value);
                    });
                    arrayCalcImages.push(obj);
                }
                formData.append('date', date);
                formData.append('url_proj', url_proj);
                formData.append('note', note);
                formData.append('stage', stage);
                formData.append('arrayCalcImages', JSON.stringify(arrayCalcImages));
                if(!empty(arrayCalcImages)){
                    jQuery("#preloader").show();
                    jQuery.ajax({
                        type: "POST",
                        url: "index.php?option=com_gm_ceiling&task=mountersorder.MountingComplited",
                        dataType: 'json',
                        cache: false,
                        processData: false, // Не обрабатываем файлы (Don't process the files)
                        contentType: false, // Так jQuery скажет серверу что это строковой запрос
                        data: formData,
                        success: function(msg) {
                            jQuery("#preloader").hide();
                            console.log(msg);
                            if (msg[0].project_status == 11 || msg[0].project_status == 24 || msg[0].project_status == 25 || msg[0].project_status == 26) {
                                window.location.href = "/index.php?option=com_gm_ceiling&&view=mounterscalendar"
                            }
                        },
                        error: function(data) {
                            jQuery("#preloader").hide();
                            console.log(data);
                            noty({
                                timeout: 2000,
                                theme: 'relax',
                                layout: 'center',
                                maxVisible: 5,
                                type: "error",
                                text: "Ошибка при попытке сохранить!"
                            });
                        }
                    });
                }
                else{
                    noty({
                        timeout: 2000,
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "error",
                        text: "Пожалуйста, загрузите фотографии!"
                    });
                }
            } else if (whatBtn == "underfulfilled") {
                // кнопка "монтаж недовыполнен"
                if (note.length != 0) {
                    CurrentDateTime();
                    jQuery.ajax( {
                        type: "POST",
                        url: "index.php?option=com_gm_ceiling&task=mountersorder.MountingUnderfulfilled",
                        dataType: 'json',
                        data: {
                            date : date,
                            url_proj : url_proj,
                            note : note,
                        },
                        success: function(msg) {
                            if (msg[0].project_status == 17) {
                                window.location.href = "/index.php?option=com_gm_ceiling&&view=mounterscalendar"
                            }
                        }
                    });
                } else {
                    jQuery("#warning").show();
                }
            }
        });

        // проверка, если пустое то убирать подсказку
        jQuery("#modal-window-container-tar").on("keydown", "#note", function() {
            jQuery("#warning").hide();
        });

        function clickUploadedCalcImg() {
            jQuery("#modal_window_img")[0].innerHTML = '<img src="'+this.src+'" class="big_uploaded_img">';
            jQuery("#input_delete_uploaded_calc_img").val(this.getAttribute('data-path'));
            jQuery("#btn_close_img").show();
            jQuery("#btn_del_img").show();
            jQuery("#img_modal_container").show();
            jQuery("#modal_window_img").show();
        }

        jQuery('.uploaded_calc_img').click(clickUploadedCalcImg);

        jQuery("#btn_close_img").click(function(){
            jQuery("#btn_close_img").hide();
            jQuery("#btn_del_img").hide();
            jQuery("#img_modal_container").hide();
            jQuery("#modal_window_img").hide();
        });
    });

</script>
