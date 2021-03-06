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
$jinput = JFactory::getApplication()->input;
$user = JFactory::getUser();
$userId = $user->get('id');

/*MODELS BLOCK*/
$model_calculations = Gm_ceilingHelpersGm_ceiling::getModel('calculations');
$model = Gm_ceilingHelpersGm_ceiling::getModel('project');
$canvases_model = Gm_ceilingHelpersGm_ceiling::getModel('canvases');
$client_model = Gm_ceilingHelpersGm_ceiling::getModel('client');
$mount_model = Gm_ceilingHelpersGm_ceiling::getModel('mount');
$projects_mounts_model = Gm_ceilingHelpersGm_ceiling::getModel('projects_mounts');
$calculationModel = Gm_ceilingHelpersGm_ceiling::getModel('calculation');
$stockModel = Gm_ceilingHelpersGm_ceiling::getModel('stock');
/*______________*/
$stocks = $stockModel->getStocks();
$project_id = $this->item->id;
$calculations = $model_calculations->new_getProjectItems($this->item->id);
foreach ($calculations as $calculation) {
    $calculation->goods = $calculationModel->getGoodsFromCalculation($calculation->id);
    $calculation->jobs = $calculationModel->getJobsFromCalculation($calculation->id);
}
$type = $jinput->get('type', '', 'STRING');
$subtype = $jinput->get('subtype', '', 'STRING');
$client = $client_model->getClientById($this->item->id_client);
$dealer = JFactory::getUser($client->dealer_id);
$dealer_cl = $client_model->getClientById($dealer->associated_client);
$type_url = '';
if (!empty($type)) {
    $type_url = "&type=$type";
}

$subtype_url = '';
if (!empty($subtype)) {
    $subtype_url = "&subtype=$subtype";
}
$service_mount = [];
if (!empty($this->item->calcs_mounting_sum)) {
    $service_mount = get_object_vars(json_decode($this->item->calcs_mounting_sum));
}

$need_service = (!empty($service_mount)) ? true : false;


$json_mount = $this->item->mount_data;
if (!empty($this->item->mount_data)) {
    $mount_types = $projects_mounts_model->get_mount_types();
    $this->item->mount_data = json_decode(htmlspecialchars_decode($this->item->mount_data));
    foreach ($this->item->mount_data as $value) {
        $value->stage_name = $mount_types[$value->stage];
    }

}
$transport_service = Gm_ceilingHelpersGm_ceiling::calculate_transport($this->item->id, "service")['mounter_sum'];
$transport = Gm_ceilingHelpersGm_ceiling::calculate_transport($this->item->id, "mount")['mounter_sum'];
$mounter_approve = true;

/*ГЕНЕРАЦИЯ ПДФ*/
$canvases_sum = [];
$model_calcform = Gm_ceilingHelpersGm_ceiling::getModel('CalculationForm');
foreach ($calculations as $calc) {
    $all_goods = $model_calcform->getGoodsPricesInCalculation($calc->id, $this->item->dealer_id);
    foreach ($all_goods as $goods) {
        if ($goods->category_id == 1) {
            $canvases_sum[$calc->id] = $goods->price_sum;
            break;
        }
    }
}


Gm_ceilingHelpersGm_ceiling::create_estimate_of_consumables($this->item->id);
Gm_ceilingHelpersGm_ceiling::create_common_cut_pdf($this->item->id);
Gm_ceilingHelpersGm_ceiling::create_common_estimate_mounters($this->item->id);


//статус проекта
$status = $model->WhatStatusProject($_GET['id']);
if (((int)$status[0]->project_status == 16) || ((int)$status[0]->project_status == 11) || ((int)$status[0]->project_status == 22) || $this->item->dealer_id != $user->dealer_id) {
    $display = 'style="display:none;"';
}
$realisedData = $model->getCalculationsRealisedCanvases($this->item->id);
$disabled = '';
foreach($realisedData as $rData){
    foreach ($rData->calculations as $calcData){
        if(!empty(floatval($calcData->realised_count))){
            $disabled = 'disabled';
            break 2;
        }
    }

}
?>
<style>
    .preview_img {
        max-width: 250px;
        cursor: pointer;
    }

    .original_img {
        width: 100%;
        cursor: pointer;

    }

    .row {
        margin-bottom: 15px;
    }
    .width_btn{
        width:250px;
    }
</style>
    <button class="btn btn-primary" id="btn_back"><i class="fa fa-arrow-left" aria-hidden="true"></i>Назад</button>

    <link rel="stylesheet" href="/components/com_gm_ceiling/views/project/css/style.css" type="text/css"/>

    <h2 class="center">Просмотр проекта</h2>
    <div id="preloader" class="PRELOADER_GM PRELOADER_GM_OPACITY" style="display: none;">
        <div class="PRELOADER_BLOCK"></div>
        <img src="/images/GM_R_HD.png"  alt = 'preloader' class="PRELOADER_IMG">
    </div>
<?php if ($this->item) : ?>
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <div class="item_fields">
                    <h4>Информация по проекту № <?= $this->item->id; ?></h4>
                    <table class="table">
                        <tr>
                            <th>Дилер</th>
                            <td><?php echo $dealer_cl->client_name; ?></td>
                        </tr>
                        <tr>
                            <th><?php echo JText::_('COM_GM_CEILING_PROJECTS_PROJECT_CALCULATION_DATE'); ?></th>
                            <td>
                                <?php
                                if ($this->item->project_calculation_date == "0000-00-00 00:00:00") { ?> -
                                <?php } else { ?>
                                    <?php $jdate = new JDate($this->item->project_calculation_date); ?>
                                    <?php echo $jdate->format('d.m.Y H:i');
                                } ?>
                            </td>
                        </tr>
                        <?php if (!empty($this->item->mount_data)): ?>
                            <tr>
                                <th colspan="3" style="text-align: center;">Монтаж</th>
                            </tr>
                            <?php foreach ($this->item->mount_data as $value) { ?>
                                <tr>
                                    <th><?php echo $value->time; ?></th>
                                    <td><?php echo $value->stage_name; ?></td>
                                    <td><?php echo JFactory::getUser($value->mounter)->name; ?></td>
                                </tr>
                            <?php } ?>
                        <?php endif; ?>
                        <tr>
                            <th style="text-align: center;" colspan=3><?php echo "Дата готовности полотен"; ?></th>
                        </tr>
                        <?php foreach ($calculations as $calculation) { ?>
                            <tr>
                                <td><?php echo $calculation->calculation_title; ?></td>
                                <td><?php echo (!empty($calculation->run_date) || !empty($calculation->run_by_call)) ? ($calculation->run_by_call) ? "По звонку" : $calculation->run_date : "Отсутствует" ?></td>
                            </tr>
                        <?php } ?>
                        <tr>
                            <th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_PROJECT_PROJECT_INFO'); ?></th>
                            <td><?php echo $this->item->project_info; ?></td>
                        </tr>
                        <tr>
                            <th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_PROJECT_CLIENT_ID'); ?></th>
                            <td><?php echo $this->item->client_id; ?></td>
                        </tr>
                        <tr>
                            <th><?php echo JText::_('COM_GM_CEILING_CLIENTS_CLIENT_CONTACTS'); ?></th>
                            <?php $contacts = $model->getClientPhones($this->item->id_client); ?>
                            <td><?php foreach ($contacts as $phone) {
                                    echo $phone->client_contacts;
                                    echo "<br>";
                                } ?></td>
                        </tr>
                        <tr>
                            <th>Доставка</th>
                            <td colspan="2">
                                <div class="col-md-10 col-xs-10">
                                    <input class="form-control" id="delivery_sum" value="<?=!empty($this->item->delivery_sum) ? $this->item->delivery_sum : '';?>">
                                </div>
                                <div class="col-md-2 col-xs-2">
                                    <button class="btn btn-primary" id="save_delivery" type="button">
                                        <i class="far fa-save"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </table>

                </div>
                <input name="project_id" value="<?php echo $this->item->id; ?>" type="hidden">
                <input name="client" id="client_id" value="<?php echo $this->item->client_id; ?>" type="hidden">
                <button class="btn btn-primary" id="create_pdfs">
                    <i class="fas fa-sync-alt"></i> Перегенерировать сметы
                </button>
            </div>
            <div class="col-md-6">
                <h4 class="center">Примечания</h4>
                <?php include_once('components/com_gm_ceiling/views/project/project_notes.php'); ?>

            </div>
        </div>
        <h4>Информация для менеджера</h4>
        <table class="table table_cashbox">
            <?php
            $mount = $mount_model->getDataAll();
            $common_canvases_sum = 0;
            $total_components_sum = 0;
            ?>
            <thead>
            <tr>
                <th>
                    Название
                </th>
                <th>
                    Стоимость
                </th>
                <th>
                    Фото
                </th>
                <th>
                    Раскрой
                </th>
                <th>
                    Примечания
                </th>
                <th>
                    Изменить раскрой
                </th>
                <th>
                    Изменить расчет
                </th>
            </tr>
            </thead>
            <tbody>
            <?php
            $calc_data = [];
            $calcImages = [];
            $total_square = 0;
            $total_perimeter = 0;
            foreach ($calculations as $calculation) {
                $common_canvases_sum += $calculation->canvases_sum;
                $total_components_sum += $calculation->components_sum;
                $total_square += $calculation->n4;
                $total_perimeter += $calculation->n5;
                if (!empty($calculation->n3)) {
                    $color_filter = "";
                    $canvas = $canvases_model->getFilteredItemsCanvas("a.id = $calculation->n3", 'old')[0];
                    if (!empty($canvas->color_id)) {
                        $color_filter = "and color_id = $canvas->color_id";
                    }
                    $canvases = $canvases_model->getFilteredItemsCanvas("texture_id = $canvas->texture_id AND manufacturer_id = $canvas->manufacturer_id and count>0 $color_filter", 'old');
                    $widths = [];
                    foreach ($canvases as $item) {
                        $widths[] = (object)array("width" => $item->width * 100, "price" => $item->price);
                    }

                    usort($widths, function ($obj_a, $obj_b) {
                        return ($obj_a > $obj_b) ? -1 : 1;

                    });
                    $calc_data[$calculation->id] = array(
                        "n3" => $calculation->n3,
                        "n4" => $calculation->n4,
                        "n5" => $calculation->n5,
                        "n9" => $calculation->n9,
                        "widths" => $widths,
                        "texture" => $canvas->texture_id,
                        "manufacturer" => $canvas->manufacturer_id,
                        "color" => $canvas->color_id,
                        "walls" => $calculation->original_sketch
                    );

                } else {
                    $canvas = null;
                    foreach ($calculation->goods as $goods) {
                        if ($goods->category_id == 1) {
                            $canvas = $goods;
                            break;
                        }
                    }
                    if (!empty($canvas)) {
                        $filter = "id = " . $canvas->id;
                        $detailed_canvas = $canvases_model->getFilteredItemsCanvas($filter);
                        $filter = "texture_id = " . $detailed_canvas[0]->texture_id . " and manufacturer_id = " . $detailed_canvas[0]->manufacturer_id . " and color = " . $detailed_canvas[0]->color . "  and visibility = 1";
                        $selected_canvases = $canvases_model->getFilteredItemsCanvas($filter);
                        $arr_widths = [];
                        $widths = [];
                        foreach ($selected_canvases as $value) {
                            if (!in_array($value->width, $arr_widths)) {
                                array_push($arr_widths, $value->width);
                                array_push($widths, (object)array("id" => $value->id, "width" => $value->width, "price" => $value->price));
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
                        $calc_data[$calculation->id] = array(
                            "n4" => $calculation->n4,
                            "n5" => $calculation->n5,
                            "n9" => $calculation->n9,
                            "widths" => $widths,
                            "texture" => $detailed_canvas[0]->texture_id,
                            "manufacturer" => $detailed_canvas[0]->manufacturer_id,
                            "color" => $detailed_canvas[0]->color,
                            "walls" => $calculation->original_sketch
                        );
                        $widths = json_encode($widths);
                        $color = $detailed_canvas[0]->color;
                        $hex = $detailed_canvas[0]->hex;
                    }
                }
                /*Изображения*/

                $dir_before = 'uploaded_calc_images/' . $calculation->id . '/before';
                $dir_after = 'uploaded_calc_images/' . $calculation->id . '/after';
                $dir_defect = 'uploaded_calc_images/' . $calculation->id . '/defect';

                $files = [];
                $temp = [];
                if (is_dir($dir_before)) {
                    $temp = scandir($dir_before);
                    foreach ($temp as $key => $value) {
                        if (strlen($value) === 32) {
                            $temp[$key] = $dir_before . '/' . $value;
                        } else {
                            unset($temp[$key]);
                        }
                    }
                    $files = array_merge($files, $temp);
                }
                if (is_dir($dir_after)) {
                    $temp = scandir($dir_after);
                    foreach ($temp as $key => $value) {
                        if (strlen($value) === 32) {
                            $temp[$key] = $dir_after . '/' . $value;
                        } else {
                            unset($temp[$key]);
                        }
                    }
                    $files = array_merge($files, $temp);
                }
                if (is_dir($dir_defect)) {
                    $temp = scandir($dir_defect);
                    foreach ($temp as $key => $value) {
                        if (strlen($value) === 32) {
                            $temp[$key] = $dir_defect . '/' . $value;
                        } else {
                            unset($temp[$key]);
                        }
                    }
                    $files = array_merge($files, $temp);
                }
                $calcImages[$calculation->id] = $files;
                /************/
                ?>
                <tr>
                    <td><?php echo $calculation->calculation_title; ?></td>
                    <td>
                        <?php /*echo $canvases_sum[$calculation->id]*/
                        echo $calculation->canvases_sum ?> руб.
                    </td>
                    <td>
                        <button class="btn btn-primary show_img" data-id="<?= $calculation->id ?>"><i
                                    class="fas fa-image"></i></button>
                    </td>

                    <td>
                        <?php $path = "/costsheets/" . md5($calculation->id . "cutpdf") . ".pdf"; ?>
                        <?php if (file_exists($_SERVER['DOCUMENT_ROOT'] . $path)) { ?>
                            <a href="<?php echo $path; ?>" class="btn btn-secondary"
                               target="_blank">Посмотреть</a>
                        <?php } else { ?>
                            -
                        <?php } ?>
                    </td>
                    <td>
                        <?php
                        echo !empty($calculation->comment) ? $calculation->comment : '';
                        ?>
                    </td>
                    <td>
                        <?php if ($this->item->project_status != 12) { ?>
                            <button data-calc_id="<?php echo $calculation->id; ?>" name="change_cut"
                                    class="btn btn-primary">Изменить
                            </button>
                        <?php } ?>
                    </td>
                    <td>
                        <?php if ($this->item->project_status != 12) { ?>
                            <button data-calc_id="<?php echo $calculation->id; ?>" name="change_calc"
                                    class="btn btn-primary">Изменить
                            </button>
                        <?php } ?>
                    </td>
                </tr>
            <?php } ?>
            <tr>
                <td>
                    <div class="col-md-12"><b>Общая информация</b></div>
                    <div class="col-md-12">Всего потолков: <?= count($calculations); ?></div>
                </td>
                <td><?= $common_canvases_sum; ?> руб.</td>
                <td></td>
                <td>
                    <?php $path = "/costsheets/" . md5($this->item->id . "common_cutpdf") . ".pdf"; ?>
                    <?php if (file_exists($_SERVER['DOCUMENT_ROOT'] . $path)) { ?>
                        <a href="<?php echo $path; ?>" class="btn btn-secondary"
                           target="_blank">Посмотреть</a>
                    <?php } else { ?>
                        -
                    <?php } ?>
                </td>
                <td colspan="3">
                    <div class="col-md-12">
                        <b>Площадь:</b> <?= $total_square; ?> м.<sup>2</sup>
                    </div>
                    <div class="col-md-12">
                        <b>Периметр:</b> <?= $total_perimeter; ?> м.
                    </div>
                </td>
            </tr>
            </tbody>
        </table>
        <table class="table">
            <tr>
                <th>Общая себестоимость расходников</th>
                <td>
                    <?php $path = "/costsheets/" . md5($this->item->id . "consumables") . ".pdf"; ?>
                    <?php if (file_exists($_SERVER['DOCUMENT_ROOT'] . $path)) { ?>
                        <?php echo $total_components_sum; ?>
                        руб.
                    <?php } else { ?>
                        0
                    <?php } ?>
                </td>
                <td>
                    <?php $path = "/costsheets/" . md5($this->item->id . "consumables") . ".pdf"; ?>
                    <?php if ($total_components_sum > 0 && file_exists($_SERVER['DOCUMENT_ROOT'] . $path)) { ?>
                        <a href="<?php echo $path; ?>" class="btn btn-secondary" target="_blank">Посмотреть</a>
                    <?php } else { ?>
                        -
                    <?php } ?>
                </td>
            </tr>

        </table>
        <?php
        if (is_array($this->item->mount_data)) {
            $mount_data = $this->item->mount_data;
        } else {
            $mount_data = json_decode(htmlspecialchars_decode($this->item->mount_data));
        }
        ?>
        <div class="row send_files">
            <div class="col-md-3">
                <h4>Отправить на почту</h4>
            </div>
            <div class="col-md-1"><i class="fas fa-angle-down"></i></div>
        </div>
        <?php
        $filestoSend = [];
        $path = "/costsheets/" . md5($this->item->id . "mount_common_gm") . ".pdf";
        if (file_exists($_SERVER['DOCUMENT_ROOT'] . $path)) {
            $filestoSend[] = $path;
        }
        $path = "/costsheets/" . md5($this->item->id . "consumables") . ".pdf";
        if (file_exists($_SERVER['DOCUMENT_ROOT'] . $path)) {
            $filestoSend[] = $path;
        }
        $path = "/costsheets/" . md5($this->item->id . "common_cutpdf") . ".pdf";
        if (file_exists($_SERVER['DOCUMENT_ROOT'] . $path)) {
            $filestoSend[] = $path;
        }
        $filestoSend = json_encode($filestoSend);
        ?>
        <div class="row" id="send_files" style="display: none;">
            <div class="col-md-12">
                <h6>Отправить общий раскрой, смету по расходным материалам и общий наряд на монтаж на почту</h6>
            </div>
            <div class="col-md-6">
                <div class="col-md-10">
                    <input type="text" id="emailToSend" class="form-control" placeholder="Введите e-mail">
                </div>
                <div class="col-md-2">
                    <button type="button" id="sendFiles" class="btn btn-primary">Отправить</button>
                </div>
            </div>
        </div>
        <div class="row mount_info">
            <div class="col-md-3">
                <h4>Информация по монтажу</h4>
            </div>
            <div class="col-md-1"><i class="fas fa-angle-down"></i></div>
        </div>
        <div id="mount_info" style="display:none;">
            <?php if(!empty($this->item->mount_data)){?>
                <div id="mount_orders" style="display: none;">
                    <div class="row">
                        <div class="col-md-12">
                            <b>
                                <span style="font-size: 14pt;">Наряды на монтаж</span>
                            </b>
                        </div>
                    </div>
                    <table class="table table_cashbox" id="table_mount_orders">
                        <thead>
                        <tr>
                            <th>
                                Название
                            </th>
                            <th>Сумма</th>
                            <th>
                                Наряды
                            </th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php $total_mount = 0;
                        foreach ($calculations as $calculation) { ?>
                            <tr>
                                <td><?php echo $calculation->calculation_title; ?></td>
                                <td>
                                    <?php
                                    $total_mount += $calculation->mounting_sum;
                                    echo $calculation->mounting_sum;
                                    ?> руб.
                                </td>
                                <td>
                                    <?php
                                    if (count($mount_data) === 0 || (count($mount_data) === 1 && $mount_data[0]->stage == 1)) {
                                        $path = "/costsheets/" . md5($calculation->id . 'mount_single_dealer') . '.pdf';
                                        $path_gm = "/costsheets/" . md5($calculation->id . 'mount_single_gm') . '.pdf';
                                        echo '<a href="' . $path . '" class="btn btn-secondary" target="_blank">Наряд МС</a>';
                                        if (file_exists($_SERVER['DOCUMENT_ROOT'] . $path_gm)) {
                                            echo '<a href="' . $path_gm . '" class="btn btn-secondary" target="_blank">Наряд бригаде</a>';
                                        }
                                    } else {
                                        foreach ($mount_data as $value) {
                                            $path_gm = "/costsheets/" . md5($calculation->id . 'mount_stage_gm' . $value->stage) . '.pdf';
                                            $path = "/costsheets/" . md5($calculation->id . 'mount_stage_dealer' . $value->stage) . '.pdf';
                                            if (file_exists($_SERVER['DOCUMENT_ROOT'] . $path)) {
                                                switch ($value->stage) {
                                                    case 2:
                                                        echo '<a href="' . $path . '" class="btn btn-secondary" target="_blank">Обагечивание МС</a>';
                                                        echo '<a href="' . $path_gm . '" class="btn btn-secondary" target="_blank">Обагечивание ГМ</a>';
                                                        break;
                                                    case 3:
                                                        echo '<a href="' . $path . '" class="btn btn-secondary" target="_blank">Натяжка МС</a>';
                                                        echo '<a href="' . $path_gm . '" class="btn btn-secondary" target="_blank">Натяжка ГМ</a>';
                                                        break;
                                                    case 4:
                                                        echo '<a href="' . $path . '" class="btn btn-secondary" target="_blank">Вставка МС</a>';
                                                        echo '<a href="' . $path_gm . '" class="btn btn-secondary" target="_blank">Вставка ГМ</a>';
                                                        break;
                                                    default:
                                                        break;
                                                }
                                            }
                                        }
                                    }
                                    ?>
                                </td>
                            </tr>
                        <?php } ?>
                        <tr>
                            <td>
                                <b>Итого</b>
                            </td>
                            <td>
                                <?php echo $total_mount + $transport; ?> руб.
                            </td>
                            <td>
                                <?php
                                $path = "/costsheets/" . md5($this->item->id . "mount_common_gm") . ".pdf";
                                $path_gm = "/costsheets/" . md5($this->item->id . "mount_common_dealer") . ".pdf";
                                ?>
                                <a href="<?php echo $path; ?>" class="btn btn-secondary" target="_blank">Наряд МС</a>
                                <a href="<?php echo $path_gm; ?>" class="btn btn-secondary" target="_blank">Наряд ГМ</a>
                            </td>
                        </tbody>
                    </table>
                </div>
                <div style="border-top: 1px solid #eceeef;" >
                    <span style="font-size: 14pt;">
                        <b>Текущие данные</b>
                    </span>
                    <table class="table">
                        <tr>
                            <th>
                                Дата этапа
                            </th>
                            <th>
                                Название
                            </th>
                            <th>
                                Бригада
                            </th>
                        </tr>
                        <?php foreach ($this->item->mount_data as $value) { ?>
                            <tr>
                                <td>
                                    <?php echo $value->time; ?>
                                </td>
                                <td>
                                    <?php echo $value->stage_name; ?>
                                </td>
                                <td>
                                    <?php echo JFactory::getUser($value->mounter)->name; ?>
                                </td>
                            </tr>
                        <?php } ?>
                    </table>
                </div>
            <?php }
                else{
                    echo "<div class='row center'><b><span style='font-size: 14pt'>Данные по монтажу отсутствуют</span></b></div>";
                }?>
            <div id="table-container">
                <div id="calendar_mount" align="center" <?php echo $display; ?>></div>
            </div>
        </div>
    </div>
    <form id="form-project1"
          action="/index.php?option=com_gm_ceiling&task=project.return&id=<?= $this->item->id ?>"
          method="post" class="form-validate form-horizontal" enctype="multipart/form-data">
        <input type="hidden" name="need_return" id="need_return" value = "1">
        <button type="button" id="return_project" class="btn btn-primary width_btn">Вернуть на стадию замера</button>
    </form><br>
    <?php if ($subtype != "run") {
        $action = "/index.php?option=com_gm_ceiling&task=project.approvemanager&id=".$this->item->id;
    }
    else {
        $action = "/index.php?option=com_gm_ceiling&task=project.refusing&id=".$this->item->id;
    }
    ?>
    <form id="form-project"
          action=<?= $action;?>
          method="post" class="form-validate form-horizontal" enctype="multipart/form-data">

        <input type="hidden" name="mount" id=mount value="<?php echo $json_mount; ?>">
        <input type="hidden" name="ready_dates" id="ready_dates" value="">
        <input type="hidden" name="include_calcs" id="include_calcs" value="">
        <input type="hidden" name="realise_calcs" id="realise_calcs" value="">
        <input type="hidden" name="realised" id="realised" value="">
        <input type="hidden" name="offcuts" id="offcuts" value="">
        <?php if($subtype !='run'){?>
        <button type="button" id="run" class="btn btn-primary width_btn">
            Запустить
        </button>
        <?php }?>
        <div id="mw_container" class="modal_window_container">
            <button type="button" id="close_mw" class="close_btn"><i class="fa fa-times fa-times-tar"
                                                                     aria-hidden="true"></i></button>
            <div id="mw_date" class="modal_window">
                <h6 style="margin-top:10px;margin-bottom: 1em;">Запуск в производство</h6>
                <div class="row center" style="padding-bottom: 5px;margin-left: 15%;margin-right: 15%;">
                    <div class="col-md-1">
                        <b><span>Запускать</span></b>
                    </div>
                    <div class="col-md-3">
                        <b><span>Название</span></b>
                    </div>
                    <div class="col-md-6">
                        <b><span>Готовность</span></b>
                    </div>
                    <div class="col-md-1">
                        <b><span>Списать</span></b>
                    </div>
                    <div class="col-md-1">
                        <b><span>Из обрезков</span></b>
                    </div>
                </div>
                <?php if (count($calculations) > 1) { ?>
                    <div class="row center" style="padding-bottom: 5px;margin-left: 15%;margin-right: 15%;">
                        <div class="col-md-1">
                            <input name='run_all'  id="run_all"  style="display: none" type='checkbox' class="inp-cbx" checked style="cursor: pointer;" <?=$disabled?>>
                            <label for="run_all" class="cbx">
                                <span>
                                    <svg width="12px" height="10px" viewBox="0 0 12 10">
                                        <polyline points="1.5 6 4.5 9 10.5 1"></polyline>
                                    </svg>
                                </span>
                            </label>
                            <span>Все</span>
                        </div>
                        <div class="col-md-3">
                            На все полотна
                        </div>
                        <div class="col-md-3">
                            <input type="checkbox" id="all_by_call" class="inp-cbx" style="display: none">
                            <label for="all_by_call" class="cbx">
                                    <span>
                                        <svg width="12px" height="10px" viewBox="0 0 12 10">
                                            <polyline points="1.5 6 4.5 9 10.5 1"></polyline>
                                        </svg>
                                    </span>
                                <span>По звонку</span>
                            </label>
                        </div>
                        <div class="col-md-3 left">
                            <input type="datetime-local"
                                   value="<?php echo str_replace(' ', 'T', date("Y-m-d H:i")); ?>"
                                   pattern="[0-9]{4}-[0-9]{2}-[0-9]{2}T[0-9]{2}:[0-9]{2}" name="all_canvas_ready"
                                   class="form-control">
                        </div>
                        <div class="col-md-1">
                            <input name='realise_all'  id="realise_all"  style="display: none" type='checkbox' class="inp-cbx" checked style="cursor: pointer;" <?=$disabled?>>
                            <label for="realise_all" class="cbx">
                                <span>
                                    <svg width="12px" height="10px" viewBox="0 0 12 10">
                                        <polyline points="1.5 6 4.5 9 10.5 1"></polyline>
                                    </svg>
                                </span>
                            </label>
                            <span>Все</span>
                        </div>
                    </div>
                <?php } ?>
                <?php foreach ($calculations as $calculation) { ?>
                    <div class="row center" style="padding-bottom: 5px;margin-left: 15%;margin-right: 15%;">
                        <div class="col-md-1">
                            <input name='include_calculation' value='<?php echo "$calculation->id"; ?>' id="<?php echo "incl_$calculation->id"?>"  style="display: none" type='checkbox' class="inp-cbx" checked style="cursor: pointer;" <?=$disabled?>>
                            <label for="<?php echo "incl_$calculation->id"?>" class="cbx" <?=$displayNone?>>
                                <span>
                                    <svg width="12px" height="10px" viewBox="0 0 12 10">
                                        <polyline points="1.5 6 4.5 9 10.5 1"></polyline>
                                    </svg>
                                </span>
                            </label>
                            <span></span>

                        </div>
                        <div class="col-md-3 ">
                            <?php echo $calculation->calculation_title; ?>
                        </div>
                        <div class="col-md-3">
                            <input type="checkbox" <?php echo ($calculation->run_by_call) ? "checked" : "" ?>
                                   data-calc_id="<?php echo $calculation->id ?>" id="<?php echo $calculation->id ?>"
                                   name="runByCall" class="inp-cbx" style="display: none">
                            <label for="<?php echo $calculation->id ?>" class="cbx">
                                    <span>
                                        <svg width="12px" height="10px" viewBox="0 0 12 10">
                                            <polyline points="1.5 6 4.5 9 10.5 1"></polyline>
                                        </svg>
                                    </span>
                                <span>По звонку</span>
                            </label>
                        </div>
                        <div class="col-md-3 left">
                            <input type="datetime-local"
                                   value="<?php echo str_replace(' ', 'T', $calculation->run_date); ?>"
                                   pattern="[0-9]{4}-[0-9]{2}-[0-9]{2}T[0-9]{2}:[0-9]{2}" required
                                   data-calc_id="<?php echo $calculation->id ?>" name="date_canvas_ready"
                                   class="form-control">
                        </div>
                        <div class="col-md-1">
                            <input name='realise' value='<?php echo "$calculation->id"; ?>' id="<?php echo "r_$calculation->id"?>"  style="display: none" type='checkbox' class="inp-cbx" checked style="cursor: pointer;" <?=$disabled?>>
                            <label for="<?php echo "r_$calculation->id"?>" class="cbx">
                                <span>
                                    <svg width="12px" height="10px" viewBox="0 0 12 10">
                                        <polyline points="1.5 6 4.5 9 10.5 1"></polyline>
                                    </svg>
                                </span>
                            </label>
                            <span></span>
                        </div>
                        <div class="col-md-1">
                            <input name='from_offcuts' value='<?php echo "$calculation->id"; ?>' id="<?php echo "fo_$calculation->id"?>"  style="display: none" type='checkbox' class="inp-cbx" style="cursor: pointer;" <?=$disabled?>>
                            <label for="<?php echo "fo_$calculation->id"?>" class="cbx">
                                <span>
                                    <svg width="12px" height="10px" viewBox="0 0 12 10">
                                        <polyline points="1.5 6 4.5 9 10.5 1"></polyline>
                                    </svg>
                                </span>
                            </label>
                            <span></span>
                        </div>
                    </div>
                <?php } ?>
                <div class="row center">
                    <div class="col-md-12">Выберите склад, для списания полотна и гарпуна</div>
                </div>
                <div class="row center">
                    <div class="col-md-4"></div>
                    <div class="col-md-4">
                        <select id="stock_id" name="stock_id" class="form-control">
                            <?php foreach ($stocks as $stock) { ?>
                                <option value="<?= $stock->id; ?>"><?= $stock->name ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-md-4"></div>
                </div>
                <div class="row">
                    <button type="button" id="save" class="btn btn-primary">Сохранить</button>
                </div>
            </div>
            <div id="mw_mounts_calendar" class="modal_window"></div>
            <div id="mw_images" class="modal_window">
                <div id="calculation_images">
                    <div class="container" id="images_container">
                        <h4> Фотографии отсутствуют</h4>
                    </div>
                </div>
            </div>
            <div class="modal_window" id="mw_return">
                <h6>Вернуть проект</h6>
                <?php foreach ($realisedData as $rGoods){?>
                    <div class="row" style="margin-left: 15%;margin-right: 15%;">
                        <div class="col-md-4">
                            <?=$rGoods->name;?>
                        </div>
                        <div class="col-md-3">
                            <b>Всего списано: <?=$rGoods->realised_count;?></b>
                        </div>
                        <div class="col-md-5">
                            <?php foreach ($calculations as $calculation){ ?>
                                <div class="row">
                                    <div class="col-md-8">
                                        <?=$calculation->calculation_title?>
                                    </div>
                                    <div class="col-md-4">
                                        <?=$rGoods->calculations[$calculation->id]->count;?>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                <?php }?>
                <div class="row center">
                    <div class="col-md-12">
                        <input type="checkbox" id="return_realised" name="return_realised" class="inp-cbx" style="display: none" checked>
                        <label for="return_realised" class="cbx">
                                    <span>
                                        <svg width="12px" height="10px" viewBox="0 0 12 10">
                                            <polyline points="1.5 6 4.5 9 10.5 1"></polyline>
                                        </svg>
                                    </span>
                            <span>Вернуть списанные полотна</span>
                        </label>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <button class="btn btn-primary" id="return_project_btn" type="button">Вернуть на стадию замера</button>
                    </div>
                </div>
            </div>
            <div class="modal_window" id="mw_error">
                <div class="row">
                    <div class="col-md-3"></div>
                    <div class="col-md-6">
                        <table id="goods_diff_table">
                            <thead>
                                <th>Наименование</th>
                                <th>Списано</th>
                                <th>Планируется списать</th>
                            </thead>
                            <tbody>

                            </tbody>
                        </table>
                    </div>
                    <div class="col-md-3"></div>
                </div>
            </div>
        </div>
        <?php if($subtype =='run'){?>
            <table class="table ">
                <tr>
                    <?php if ((int)$status[0]->project_status != 11 && (int)$status[0]->project_status != 16 && (int)$status[0]->project_status != 17) { ?>
                        <td style=" padding-left:0;"><a class="btn btn-primary" id="refuse">Отказ от производства</a>
                        </td>
                    <?php } ?>
                </tr>
                <tbody class="refuse" style="display: none">
                <tr>
                    <td>
                        <label>Данные для перезвона:<span class="star">&nbsp;</span></label>

                        <p><input type="date" id="date" name="date" placeholder="Дата замера" class="inputactive"
                                  style="width:70%;" required></p>
                        <select id="time" name="time" class="inputactive" style="width:70%;">
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

                    </td>
                    <td>
                        <p><label>Добавить примечание:<span class="star">&nbsp;</span></label></p>
                        <p><textarea class="inputactive" id="comment" name="comment"
                                     placeholder="Примечание"></textarea></p>
                    </td>
                </tr>
                <tr>
                    <td>
                        <button id="refus_project" class="btn btn btn-danger" type="submit">
                            Отправить в отказы
                        </button>
                    </td>
                </tr>
                </tbody>
            </table>
        <?php }?>
    </form>
    <form method="POST" action="/sketch/cut_redactor_2/index.php" style="display: none" id="form_url">
        <input name="user_id" id="user_id" value="<?php echo $user->id; ?>" type="hidden">
        <input name="width" id="width" value="" type="hidden">
        <input name="texture" id="texture" value="" type="hidden">
        <input name="color" id="color" value="" type="hidden">
        <input name="manufacturer" id="manufacturer" value="" type="hidden">
        <input name="auto" id="auto" value="" type="hidden">
        <input name="walls" id="walls" value="" type="hidden">
        <input name="calc_id" id="calc_id" value="" type="hidden">
        <input name="n4" id="n4" value="" type="hidden">
        <input name="n5" id="n5" value="" type="hidden">
        <input name="n9" id="n9" value="" type="hidden">
        <input name="proj_id" id="proj_id" value="<?php echo $project_id; ?>" type="hidden">
        <input name="type_url" id="type_url" value="<?php echo $type_url; ?>" type="hidden">
        <input name="subtype_url" id="subtype_url" value="<?php echo $subtype_url; ?>" type="hidden">
        <input name="page" id="page" value="gmmanager" type="hidden">
    </form>

    <script type="text/javascript" src="/components/com_gm_ceiling/create_calculation.js"></script>
    <script type="text/javascript"
            src="/components/com_gm_ceiling/views/project/common_table.js?t=<?php echo time(); ?>"></script>
    <script type="text/javascript" src="/components/com_gm_ceiling/date_picker/mounts_calendar.js"></script>
    <script type="text/javascript">

        init_mount_calendar('calendar_mount', 'mount', 'mw_mounts_calendar', ['close_mw', 'mw_container']);
        var project_id = "<?php echo $this->item->id; ?>",
            calcImages = JSON.parse('<?=json_encode($calcImages);?>');
        jQuery(document).mouseup(function (e) { // событие клика по веб-документу
            var div1 = jQuery("#mw_date"), // тут указываем ID элемента
                div2 = jQuery("#mw_mounts_calendar"),
                div3 = jQuery("#mw_images"),
                div4 = jQuery('#mw_return');
            if (!div1.is(e.target) // если клик был не по нашему блоку
                && div1.has(e.target).length === 0
                && !div2.is(e.target)
                && div2.has(e.target).length === 0
                && !div3.is(e.target)
                && div3.has(e.target).length === 0
                && !div4.is(e.target)
                && div4.has(e.target).length === 0){ // и не по его дочерним элементам
                jQuery("#close_mw").hide();
                jQuery("#mw_container").hide();
                div1.hide();
                div2.hide();
                div3.hide();
                div4.hide();
            }
        });
        jQuery(document).ready(function () {

            let calc_data = JSON.parse('<?php echo json_encode($calc_data);?>');
            let sendFiles = JSON.parse('<?php echo $filestoSend;?>');
            console.log('sF', sendFiles);

            jQuery("#sendFiles").click(function () {
                var email = jQuery('#emailToSend').val();
                if (sendFiles.length < 3) {
                    var n = noty({
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "error",
                        text: "Какая-то из смет отсутствует! Пожалуйста, перегенерируйте сметы!"
                    });
                } else {

                    jQuery.ajax({
                        type: 'POST',
                        url: "index.php?option=com_gm_ceiling&task=project.sendFiles",
                        data: {
                            project_id: <?php echo $this->item->id; ?>,
                            files: sendFiles,
                            email: email
                        },
                        success: function (data) {
                            console.log(data);
                        },
                        dataType: "text",
                        timeout: 10000,
                        error: function (data) {
                            console.log(data);
                            var n = noty({
                                theme: 'relax',
                                layout: 'center',
                                maxVisible: 5,
                                type: "error",
                                text: "Ошибка сервера"
                            });
                        }
                    });
                }
            });

            jQuery("[name = 'change_cut']").click(function () {

                let id = jQuery(this).data('calc_id'),
                    data = calc_data[id];
                console.log(calc_data);
                if (!empty(data.n3)) {
                    jQuery("#form_url").attr('action', '/sketch_old/cut_redactor_2/index.php')
                }
                jQuery("#calc_id").val(id);
                jQuery('#texture').val(data.texture);
                jQuery("#color").val(data.color);
                jQuery("#manufacturer").val(data.manufacturer);
                jQuery("#n4").val(data.n4);
                jQuery("#n5").val(data.n5);
                jQuery("#n9").val(data.n9);
                jQuery("#walls").val(data.walls);
                jQuery("#width").val(JSON.stringify(data.widths));
                jQuery("#form_url").submit();
            });

            jQuery("[name = 'change_calc']").click(function () {
                let id = jQuery(this).data('calc_id'),
                    data = calc_data[id];
                if (!empty(data.n3)) {
                    location.href = '/index.php?option=com_gm_ceiling&view=calculationform2&type=gmmanager&calc_id=' + id;
                } else {
                    location.href = '/index.php?option=com_gm_ceiling&view=calculationform&type=gmmanager&calc_id=' + id;
                }
            });

            jQuery('#btn_back').click(function () {
                var l = location.href.replace('project', 'projects');
                l = l.replace('run', 'runprojects');
                l = l.replace(/&id=\d+/, '');
                location.href = l;
            });

            jQuery('#create_pdfs').click(function () {
                jQuery('#preloader').show();
                jQuery.ajax({
                    type: 'POST',
                    url: "/index.php?option=com_gm_ceiling&task=createPdfs",
                    data: {
                        id:<?php echo $this->item->id;?>
                    },
                    success: function (data) {
                        location.reload();
                    },
                    error: function (data) {
                        jQuery('#preloader').hide();
                        console.log(data);
                        noty({
                            theme: 'relax',
                            layout: 'center',
                            maxVisible: 5,
                            type: "error",
                            text: "Ошибка при генерации!"
                        });
                    }
                });
            });

            jQuery("#run").click(function () {
                jQuery("#mw_container").show();
                jQuery("#mw_date").show("slow");
                jQuery("#close_mw").show();
            });

            jQuery('[name = "runByCall"]').change(function () {
                var checkBox = this;
                if (checkBox.checked) {
                    jQuery('[name = "date_canvas_ready"]').filter(function () {
                        if (jQuery(this).data("calc_id") == jQuery(checkBox).data("calc_id")) {
                            this.value = "";
                        }
                        ;
                    });
                }
            });

            jQuery('[name = "date_canvas_ready"]').focus(function () {
                var date = new Date,
                    month = (date.getMonth() < 10) ? "0" + (date.getMonth() + 1) : (date.getMonth() + 1),
                    day = (date.getDate() < 10) ? "0" + date.getDate() : date.getDate();
                this.value = date.getFullYear() + "-" + month + "-" + day + "T09:00";
            });

            jQuery('[name = "date_canvas_ready"]').change(function () {
                var date_time = this;
                jQuery("#all_by_call").attr("checked", false);
                jQuery('[name = "runByCall"]').filter(function () {
                    if (jQuery(this).data("calc_id") == jQuery(date_time).data("calc_id")) {
                        jQuery(this).attr("checked", false);
                    }
                    ;
                });
            });

            jQuery("#save").click(function () {
                checkRealisation();
                if(jQuery('#realised').val()) {
                    var readyDates = jQuery('[name = "date_canvas_ready"]').filter(function () {
                            if (this.value) {
                                return this;
                            };
                        }),
                        byCall = jQuery('[name = "runByCall"]:checked'),
                        result = [],
                        runCalcs = [],
                        realiseCalcs = [],
                        fromOffcuts = [];

                    jQuery.each(jQuery('input[name="include_calculation"]'), function (n, el) {
                        if (jQuery(el).prop('checked')) {
                            runCalcs.push(jQuery(el).val());
                        }
                    });

                    jQuery.each(jQuery('input[name="realise"]'), function (n, el) {
                        if (jQuery(el).prop('checked')) {
                            realiseCalcs.push(jQuery(el).val());
                        }
                    });

                    jQuery.each(jQuery('input[name="from_offcuts"]'), function (n, el) {
                        if (jQuery(el).prop('checked')) {
                            fromOffcuts.push(jQuery(el).val());
                        }
                    });

                    jQuery.each(readyDates, function (index, elem) {
                        result.push({calc_id: jQuery(elem).data("calc_id"), ready_time: jQuery(elem).val()});
                    });

                    jQuery.each(byCall, function (index, elem) {
                        result.push({calc_id: jQuery(elem).data("calc_id"), ready_time: "by_call"});
                    });

                    jQuery("#ready_dates").val(JSON.stringify(result));
                    jQuery("#include_calcs").val(JSON.stringify(runCalcs));
                    jQuery("#realise_calcs").val(JSON.stringify(realiseCalcs));
                    jQuery("#offcuts").val(JSON.stringify(fromOffcuts));
                    jQuery("#form-project").submit();
                }
            });

            //готовность на все потолки
            jQuery("#all_by_call").change(function () {
                var checkBox = this, attr;
                if (checkBox.checked) {
                    jQuery('[name = "all_canvas_ready"]')[0].value = '';
                    jQuery('[name = "date_canvas_ready"]').each(function (index, elem) {
                        elem.value = '';
                    });
                    attr = true;
                } else {
                    attr = false;
                }
                jQuery('[name = "runByCall"]').each(function (index, elem) {
                    jQuery(elem).attr("checked", attr);
                });
            });

            jQuery('[name = "all_canvas_ready"]').focus(function () {
                var date = new Date,
                    month = (date.getMonth() < 10) ? "0" + (date.getMonth() + 1) : (date.getMonth() + 1),
                    day = (date.getDate() < 10) ? "0" + date.getDate() : date.getDate();
                this.value = date.getFullYear() + "-" + month + "-" + day + "T09:00";
                jQuery('[name = "runByCall"]').each(function (index, elem) {
                    jQuery(elem).attr("checked", false);
                });
                jQuery("#all_by_call").attr("checked", false);
            });

            jQuery('[name = "all_canvas_ready"]').change(function () {
                var date_time = this;
                jQuery('[name = "date_canvas_ready"]').each(function (index, elem) {
                    elem.value = date_time.value;
                });
            });

            jQuery('#run_all').change(function () {
                var checked = jQuery(this).prop('checked');
                jQuery('[name = "include_calculation"]').each(function (index, elem) {
                   jQuery(elem).attr('checked',checked);
                });
            });

            jQuery('input[name="realise"]').change(function(){
                var allChecked = true;
                jQuery('[name = "realise"]').each(function (index, elem) {
                    if(!jQuery(elem).prop('checked')){
                        allChecked = false;
                        return;
                    }
                });
                jQuery('#realise_all').prop('checked',allChecked);
            });

            jQuery('input[name="include_calculation"]').change(function(){
                var allChecked = true;
                jQuery('[name = "include_calculation"]').each(function (index, elem) {
                    if(!jQuery(elem).prop('checked')){
                        allChecked = false;
                        return;
                    }
                });
                jQuery('#run_all').prop('checked',allChecked);
            });

            jQuery('#realise_all').change(function () {
                var checked = jQuery(this).prop('checked');
                jQuery('[name = "realise"]').each(function (index, elem) {
                    jQuery(elem).attr('checked',checked);
                });
            });



            jQuery("#accept_project").click(function () {
                jQuery("input[name='project_verdict']").val(1);
                jQuery(".project_activation").show();
                jQuery("#mounting_date_control").show();
            });

            jQuery("#refuse_project").click(function () {
                jQuery("input[name='project_verdict']").val(0);
                jQuery(".project_activation").show();
                jQuery("#mounting_date_control").hide();
            });

            jQuery('.show_img').click(function () {
                var calc_id = jQuery(this).data('id'),
                    images = calcImages[calc_id],
                    html = '';
                if (images.length == 0) {
                    html = '<h4>Изображения отсутствуют</h4>';
                }
                jQuery('#images_container').empty();
                for (var i = 0; i < images.length; i++) {
                    if (i % 3 == 0) {
                        html += '<div class="row">';
                    }
                    html += '<div class="col-md-4">';
                    html += '<div class=row><img class="preview_img" src="' + images[i] + '"></div>';
                    html += '</div>';
                    if ((i + 1) % 3 == 0 || i + 1 == images.length) {
                        html += '</div>';
                    }
                }
                jQuery('#images_container').append(html);

                jQuery('#mw_container').show();
                jQuery('#close_mw').show();
                jQuery('#mw_images').show('slow');
            });

            jQuery(document).on("click", ".preview_img", function () {
                jQuery(this).addClass('original_img');
                jQuery(this).removeClass('preview_img');

            });

            jQuery(document).on("click", ".original_img", function () {
                jQuery(this).addClass('preview_img');
                jQuery(this).removeClass('original_img ');
            });

            jQuery('.mount_info').click(function () {
                var i = jQuery(this).find('i');
                jQuery('#mount_info').toggle();
                jQuery('#mount_orders').toggle();
                if(jQuery('#mount_info').is(':visible')){
                    i.addClass('fa-angle-up');
                    i.removeClass('fa-angle-down');
                }
                else{
                    i.addClass('fa-angle-down');
                    i.removeClass('fa-angle-up');
                }
            });
            send_files
            jQuery('.send_files').click(function () {
                var i = jQuery(this).find('i');
                jQuery('#send_files').toggle();
                if(jQuery('#send_files').is(':visible')){
                    i.addClass('fa-angle-up');
                    i.removeClass('fa-angle-down');
                }
                else{
                    i.addClass('fa-angle-down');
                    i.removeClass('fa-angle-up');
                }
            });

            jQuery('#save_delivery').click(function () {
                var sum = jQuery('#delivery_sum').val();
                if(!empty(sum)){
                    var data = {delivery_sum: sum},
                        id = project_id;
                    if (empty(id)) {
                        id = project_id.value;
                    }
                    if (id) {
                        jQuery.ajax({
                            type: 'POST',
                            url: "index.php?option=com_gm_ceiling&task=project.updateProjectData",
                            data: {
                                project_id: id,
                                project_data: data
                            },
                            success: function (data) {
                                noty({
                                    theme: 'relax',
                                    timeout: 2000,
                                    layout: 'center',
                                    maxVisible: 5,
                                    type: "success",
                                    text: "Сохранено!"
                                });
                                setTimeout(function () {location.reload()},3000);
                            },
                            dataType: "json",
                            timeout: 20000,
                            error: function (data) {
                                console.log('error');
                            }
                        });
                    }
                }
                else{
                    noty({
                        theme: 'relax',
                        timeout: 2000,
                        layout: 'center',
                        maxVisible: 5,
                        type: "error",
                        text: "Сохранение невозможно, пустая сумма!"
                    });
                }
            });
            /*Возврат проекта*/
            jQuery('#return_project').click(function(){
                jQuery('#mw_container').show();
                jQuery('#close_mw').show();
                jQuery('#mw_return').show();
            });

            jQuery('#return_project_btn').click(function(){
                jQuery('#form-project1').submit();
            });

            jQuery('#return_realised').change(function () {
                if(jQuery(this).prop('checked')){
                    jQuery('#need_return').val(1);
                }
                else{
                    jQuery('#need_return').val(0);
                }
            });
            /*----*/

            function checkRealisation(){
                jQuery.ajax({
                    type: 'POST',
                    url: "index.php?option=com_gm_ceiling&task=project.checkExistRealiseCanvases",
                    data: {
                        project_id: <?php echo $this->item->id; ?>,
                    },
                    success: function (data) {
                        if(data.type=='error'){
                            jQuery('#realised').val(0);
                            noty({
                                theme: 'relax',
                                layout: 'center',
                                timeout: 5000,
                                type: "error",
                                text: data.error_text,
                                buttons: [
                                    {
                                        addClass: 'btn btn-primary',
                                        text: 'Посмотреть детали',
                                        onClick: function ($noty) {
                                            jQuery("#goods_diff_table > tbody").empty();
                                            jQuery.each(data.diff, function (index, goods) {
                                                jQuery("#goods_diff_table > tbody").append('<tr>' +
                                                    '<td>' + goods.name + '</td>' +
                                                    '<td>' + goods.realised + '</td>' +
                                                    '<td>' + goods.to_realise + '</td>' +
                                                    '</tr>')
                                            });
                                            jQuery("#mw_container").show();
                                            jQuery("#mw_error").show('slow');
                                            jQuery("#close_mw").show();
                                            $noty.close();
                                        }
                                    },
                                    {
                                        addClass: 'btn btn-primary', text: 'Отмена', onClick: function ($noty) {
                                            $noty.close();
                                        }
                                    }
                                ]
                            });
                        }
                        if(data.type=='realised'){
                            jQuery('#realised').val(1);
                        }
                        if(data.type=='success'){
                            jQuery('#realised').val(2);
                        }
                    },
                    async: false,
                    dataType: "json",
                    timeout: 10000,
                    error: function () {
                        noty({
                            theme: 'relax',
                            layout: 'center',
                            maxVisible: 5,
                            type: "error",
                            text: "Ошибка сервера"
                        });
                    }
                });
            }
        });

        <?php if (($dealer->dealer_type == 0 || $dealer->dealer_type == 1) && $user->dealer_id != $dealer->dealer_id)
        { ?>
        jQuery("#refuse").click(function () {
            jQuery.ajax({
                type: 'POST',
                url: "index.php?option=com_gm_ceiling&task=project.updateProjectStatus",
                data: {
                    project_id: <?php echo $this->item->id; ?>,
                    status: 4
                },
                success: function (data) {
                    console.log(data);
                    location.href = location.href.replace('&subtype=run', '');
                },
                dataType: "text",
                timeout: 10000,
                error: function () {
                    console.log(data);
                    noty({
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "error",
                        text: "Ошибка сервера"
                    });
                }
            });
        });
        <?php } else {?>
        var temp = 0;
        jQuery("#refuse").click(function () {
            if (!temp) {
                jQuery(".refuse").show();
                temp = 1;
            } else {
                jQuery(".refuse").hide();
                temp = 0;
            }
        });
        <?php } ?>

        function send_ajax(id) {
            jQuery.ajax({
                type: 'POST',
                url: "index.php?option=com_gm_ceiling&task=send_sketch",
                data: {
                    id: id,
                    from_db: 1
                },
                success: function (data) {
                    jQuery("#input_walls").val(data);
                    jQuery("#calc_id").val(id);
                    jQuery("#proj_id").val(<?php echo $this->item->id; ?>);
                    jQuery("#data_form").submit();

                },
                error: function (data) {
                    console.log(data);
                }

            });
        }

        function calculate_total() {
            var components_total = 0;
            gm_total = 0;
            dealer_total = 0;
            jQuery("input[name^='include_calculation']:checked").each(function () {
                var parent = jQuery(this).closest(".include_calculation"),
                    components_sum = parent.find("input[name^='components_sum']").val(),
                    gm_mounting_sum = parent.find("input[name^='gm_mounting_sum']").val(),
                    dealer_mounting_sum = parent.find("input[name^='dealer_mounting_sum']").val();
                components_total += parseFloat(components_sum);
                gm_total += parseFloat(gm_mounting_sum);
                dealer_total += parseFloat(dealer_mounting_sum);
            });
            jQuery("#components_total").text(components_total.toFixed(2));
            jQuery("#gm_total").text(gm_total.toFixed(2));
            jQuery("#dealer_total").text(dealer_total.toFixed(2));
        }

    </script>

<?php
else:
    echo JText::_('COM_GM_CEILING_ITEM_NOT_LOADED');
endif;
?>