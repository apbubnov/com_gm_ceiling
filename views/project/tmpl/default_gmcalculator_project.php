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

$canEdit = JFactory::getUser()->authorise('core.edit', 'com_gm_ceiling');
if (!$canEdit && JFactory::getUser()->authorise('core.edit.own', 'com_gm_ceiling')) {
    $canEdit = JFactory::getUser()->id == $this->item->created_by;
}

Gm_ceilingHelpersGm_ceiling::create_client_common_estimate($this->item->id);
Gm_ceilingHelpersGm_ceiling::create_common_estimate_mounters($this->item->id);
Gm_ceilingHelpersGm_ceiling::create_estimate_of_consumables($this->item->id);

$model = Gm_ceilingHelpersGm_ceiling::getModel('calculations');
$calculations = $model->getProjectItems($this->item->id);

foreach ($calculations as $calculation) {
    $calculation->dealer_canvases_sum = double_margin($calculation->canvases_sum, $this->item->gm_canvases_margin, $this->item->dealer_canvases_margin);
    $calculation->dealer_components_sum = double_margin($calculation->components_sum, $this->item->gm_components_margin, $this->item->dealer_components_margin);
    $calculation->dealer_gm_mounting_sum = double_margin($calculation->gm_mounting_sum, $this->item->gm_mounting_margin, $this->item->dealer_mounting_margin);

    $calculation->calculation_total = round($calculation->dealer_canvases_sum + $calculation->dealer_components_sum + $calculation->dealer_gm_mounting_sum, 2);
    $calculation->calculation_total_discount = round($calculation->calculation_total * ((100 - $this->item->project_discount) / 100), 2);
    $project_total += $calculation->calculation_total;
    $project_total_discount += $calculation->calculation_total_discount;

}

$project_total = round($project_total, 2);
$project_total_discount = round($project_total_discount, 2);
$client_model = Gm_ceilingHelpersGm_ceiling::getModel('client_phones');
$phones = $client_model->getItemsByClientId($this->item->id_client);
?>
<?= parent::getButtonBack(); ?>

<h2 class="center">Просмотр проекта</h2>
<?php if ($this->item) : ?>

    <form id="form-client" action="/index.php?option=com_gm_ceiling&task=project.activate&type=gmcalculator&subtype=calendar" method="post" class="form-validate form-horizontal" enctype="multipart/form-data">
        <div class="container">
            <div class="row">
                <div class="col-xl-6 item_fields">
                    <h4>Информация по проекту № <?= $this->item->id;?></h4>
                    <table class="table">
                        <tr>
                            <th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_PROJECT_CLIENT_ID'); ?></th>
                            <td><a href="http://test1.gm-vrn.ru/index.php?option=com_gm_ceiling&view=clientcard&id=<?=$this->item->id_client;?>"><?php echo $this->item->client_id; ?></a></td>
                        </tr>
                        <tr>
                            <th><?php echo JText::_('COM_GM_CEILING_CLIENTS_CLIENT_CONTACTS'); ?></th>
                            <td><?foreach ($phones as $contact):?><a href="tel:+<?=$contact->phone;?>"><?=$contact->phone;?></a><?endforeach;?></td>
                        </tr>
                        <tr>
                            <th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_PROJECT_PROJECT_INFO'); ?></th>
                            <td><a target="_blank" href="https://yandex.ru/maps/?mode=search&text=<?=$this->item->project_info;?>"><?=$this->item->project_info;?></a></td>
                        </tr>
                        <tr>
                            <th><?php echo JText::_('COM_GM_CEILING_PROJECTS_PROJECT_CALCULATION_DATE'); ?></th>
                            <td><?php if ($this->item->project_mounting_date == '0000-00-00 00:00:00') echo "-"; else echo $this->item->project_mounting_date; ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        <?php if (sizeof($calculations) > 0) { ?>
        <?php echo "<h3>Расчеты для проекта</h3>"; ?>
        <!-- Nav tabs -->
        <ul class="nav nav-tabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active " data-toggle="tab" href="#summary" role="tab">Общее</a>
            </li>
            <?php foreach ($calculations as $k => $calculation) { ?>
                <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#calculation<?php echo $calculation->id; ?>"
                    role="tab"><?php echo $calculation->calculation_title; ?></a>
                </li>
            <?php } ?>

        </ul>
        <!-- Tab panes -->
        <div class="tab-content">
            <div class="tab-pane active" id="summary" role="tabpanel">
                <table class="table calculation_sum">
                    <tr>
                        <th class="section_header" id="sh_ceilings">Потолки <i class="fa fa-sort-desc"aria-hidden="true"></i></th>
                        <th></th>
                    </tr>
                    <?php $project_total = 0; ?>
                    <?php $project_total_discount = 0;
                    $dealer_gm_mounting_sum_1 = 0;
                    $calculation_total_1 = 0;
                    $project_total_1 = 0;
                    $dealer_gm_mounting_sum_11 = 0;
                    $calculation_total_11 = 0;
                    $project_total_11 = 0;
                    $tmp = 0;
                    $sum_transport_discount_total = 0;
                    $sum_transport_total = 0;
                    foreach ($calculations as $calculation) { ?>
                        <?php $dealer_canvases_sum = double_margin($calculation->canvases_sum, 0 /*$this->item->gm_canvases_margin*/, $this->item->dealer_canvases_margin); ?>
                        <?php $dealer_components_sum = double_margin($calculation->components_sum, 0/* $this->item->gm_components_margin*/, $this->item->dealer_components_margin); ?>
                        <?php $dealer_gm_mounting_sum = double_margin($calculation->mounting_sum, 0/*$this->item->gm_mounting_margin*/, $this->item->dealer_mounting_margin); ?>
                        <?php $calculation_total = $dealer_canvases_sum + $dealer_components_sum + $dealer_gm_mounting_sum; ?>
                        <?php $calculation_total_discount = $calculation_total * ((100 - $this->item->project_discount) / 100); ?>
                        <?php $project_total += $calculation_total; ?>
                        <?php $project_total_discount += $calculation_total_discount; ?>
                        <?php if ($user->dealer_type != 2) {
                            $dealer_canvases_sum_1 = margin($calculation->canvases_sum, 0/*$this->item->gm_canvases_margin*/);
                            $dealer_components_sum_1 = margin($calculation->components_sum, 0/*$this->item->gm_components_margin*/);
                            $dealer_gm_mounting_sum_1 = margin($calculation->mounting_sum, 0/*$this->item->gm_mounting_margin*/);
                            $dealer_gm_mounting_sum_11 += $dealer_gm_mounting_sum_1;
                            $calculation_total_1 = $dealer_canvases_sum_1 + $dealer_components_sum_1;
                            $calculation_total_11 += $calculation_total_1;
                            $project_total_1 = $calculation_total_1 + $dealer_gm_mounting_sum_1;
                            $project_total_11 += $project_total_1;
                        }
                        $calculation->calculation_title; ?>
                        <?php $total_square += $calculation->n4; ?>
                        <?php $total_perimeter += $calculation->n5;

                        // --------------------------Высчитываем транспорт в отдельную строчку -----------------------------------------------------
                        $sum_transport = 0;  $sum_transport_discount = 0;
                        $mountModel = Gm_ceilingHelpersGm_ceiling::getModel('mount');
                        $mount_transport = $mountModel->getDataAll();
                        if($calculation->transport == 1 ) {
                            $tmp = 1;
                            $sum_transport = double_margin($mount_transport->transport, $this->item->gm_mounting_margin, $this->item->dealer_mounting_margin);
                            $sum_transport_total = $sum_transport;
                        }
                        if($calculation->distance > 0  && $calculation->distance_col > 0 ) {
                            $tmp = 2;
                            $sum_transport = double_margin($mount_transport->distance * $calculation->distance * $calculation->distance_col, $this->item->gm_mounting_margin, $this->item->dealer_mounting_margin);
                            $sum_transport_total = $sum_transport;
                        }
                        if($calculation->discount > 0 && $sum_transport > 0) {
                            $sum_transport_discount = $sum_transport * ((100 - $calculation->discount) / 100);
                            $sum_transport_discount_total = $sum_transport_discount;
                        }?>
                        <tr class="section_ceilings">
                            <td class="include_calculation">
                                <?php echo $calculation->calculation_title; ?>
                            </td>
                        </tr>
                        <tr class="section_ceilings" id="">
                            <td>Площадь/Периметр :</td>
                            <td>
                                <?php echo $calculation->n4; ?> м<sup>2</sup> / <?php echo $calculation->n5; ?> м
                            </td>
                        </tr>
                        <tr class="section_ceilings">
                            <?php if ($calculation->discount != 0) { ?>
                                <td>Цена / -<?php echo $calculation->discount ?>% :</td>
                                <td id="calculation_total"> <?php echo round($calculation_total - $sum_transport, 0); ?> руб. /</td>
                                <td id="calculation_total_discount"> <?php echo round($calculation_total_discount - $sum_transport_discount, 0); ?>
                                    руб.
                                </td>
                            <?php } else { ?>
                                <td>Итого</td>
                                <td id="calculation_total"> <?php echo round($calculation_total - $sum_transport, 0); ?> руб.</td>
                                <td></td>
                            <?php } ?>

                        </tr>

                    <?php } ?>
                    <tr>
                        <th>Общая площадь/общий периметр :</th>
                        <th id="total_square">
                            <?php echo $total_square; ?>м<sup>2</sup> /
                        </th>
                        <th id="total_perimeter">
                            <?php echo $total_perimeter; ?> м
                        </th>
                    </tr>
                    <tr>
                        <?php if ($tmp == 1 && $sum_transport_discount_total != 0 ) { ?>
                            <th> Транспорт / - % </th>
                            <td> <?= $sum_transport_total;?> руб. / <?= $sum_transport_discount_total;?> руб. </td>
                            <td></td>
                        <?php } elseif ($tmp == 1 && $sum_transport_discount_total == 0 ) { ?>
                            <th> Транспорт </th>
                            <td> <?= $sum_transport_total;?> руб. </td>
                            <td></td>
                        <?php } elseif ($tmp == 2 && $sum_transport_discount_total != 0 ) { ?>
                            <th> Выезд за город / - % </th>
                            <td> <?= $sum_transport_total;?> руб. / <?= $sum_transport_discount_total;?> руб. </td>
                            <td></td>
                        <?php } elseif ($tmp == 2 && $sum_transport_discount_total == 0 ) { ?>
                            <th> Выезд за город</th>
                            <td> <?= $sum_transport_total;?> руб. </td>
                            <td></td>
                        <?php } ?>
                    </tr>
                    <tr>
                        <?php if ($this->item->project_discount != 0) { ?>
                            <th>Сумма без скидки/со скидкой :</th>
                            <th id="project_total"> <?php echo round($project_total, 0); ?> руб. /</th>
                            <th id="project_total_discount"> <?php echo round($project_total_discount, 0); ?> руб.</th>
                        <?php }  else { ?>
                        <th>Итого</th>
                        <th id="project_total">
                            <?php
                            if ($this->item->new_project_sum == 0) {
                                echo round($project_total, 2);
                            } else {
                                echo round($this->item->new_project_sum, 2);
                            }
                            } ?>

                    </tr>
                    <tr>
                        <th class="section_header" id="sh_estimate"> Сметы <i class="fa fa-sort-desc"
                                                                            aria-hidden="true"></i></th>
                    </tr>
                    <?php foreach ($calculations

                                as $calculation) { ?>
                    <tr class="section_estimate" id="section_estimate_<?= $calculation->id; ?>" style="display:none;">
                        <td><?php echo $calculation->calculation_title; ?></td>
                        <td>
                            <?php
                            $path = "/costsheets/" . md5($calculation->id . "client_single") . ".pdf";

                            $pdf_names[] = array("name" => $calculation->calculation_title, "filename" => md5($calculation->id . "client_single") . ".pdf", "id" => $calculation->id);
                            ?>
                            <?php if (file_exists($_SERVER['DOCUMENT_ROOT'] . $path)) { ?>
                                <a href="<?php echo $path; ?>" class="btn btn-secondary" target="_blank">Посмотреть</a>
                            <?php } else { ?>
                                -
                            <?php } ?>
                        </td>
                        <?php } ?>
                    </tr>
                    <?php if ($this->item->project_mounter != 'Монтажная бригада ГМ') { ?>
                    <tr>
                        <th id="sh_mount"> Наряд на монтаж <i class="fa fa-sort-desc" aria-hidden="true"></i></th>
                    </tr>

                    <?php foreach ($calculations

                    as $calculation) { ?>
                    <tr class="section_mount" id="section_mount_<?= $calculation->id; ?>" style="display:none;">
                        <td><?php echo $calculation->calculation_title; ?></td>
                        <td>
                            <?php $path = "/costsheets/" . md5($calculation->id . "mount_single") . ".pdf"; ?>
                            <?php $pdf_names_mount[] = array("name" => $calculation->calculation_title, "filename" => md5($calculation->id . "mount_single") . ".pdf", "id" => $calculation->id); ?>
                            <?php if (file_exists($_SERVER['DOCUMENT_ROOT'] . $path)) { ?>
                                <a href="<?php echo $path; ?>" class="btn btn-secondary" target="_blank">Посмотреть</a>
                            <?php } else { ?>
                                После договора
                            <?php } ?>
                        </td>
                        <?php }
                        $json = json_encode($pdf_names_mount); ?>
                        <?php } ?>
                    </tr>

                </table>
            </div>
            <?php foreach ($calculations as $k => $calculation) { ?>
                <?php $mounters = json_decode($calculation->mounting_sum); ?>
                <?php $filename = "/calculation_images/" . md5("calculation_sketch" . $calculation->id) . ".svg"; ?>
                <div class="tab-pane" id="calculation<?php echo $calculation->id; ?>" role="tabpanel">
                    <h3><?php echo $calculation->calculation_title; ?></h3>
                    <?php if (!empty($filename)): ?>
                        <div class="sketch_image_block">
                            <h3 class="section_header">
                                Чертеж <i class="fa fa-sort-desc" aria-hidden="true"></i>
                            </h3>
                            <div class="section_content">
                                <img class="sketch_image" src="<?php echo $filename . '?t=' . time(); ?>"
                                    style="width:80vw;"/>
                            </div>
                        </div>
                    <?php endif; ?>
                    <div class="row-fluid">
                        <div class="span6">
                            <?php if ($calculation->n1 && $calculation->n2 && $calculation->n3): ?>
                                <h4>Материал</h4>
                                <div>
                                    Тип потолка: <?php echo $calculation->n1; ?>
                                </div>
                                <div>
                                    Тип фактуры: <?php echo $calculation->n2; ?>
                                </div>
                                <div>
                                    Производитель, ширина: <?php echo $calculation->n3; ?>
                                </div>

                                <?php if ($calculation->color > 0) { ?>
                                    <?php $color_model = Gm_ceilingHelpersGm_ceiling::getModel('color'); ?>
                                    <?php $color = $color_model->getData($calculation->color); ?>
                                    <div>
                                        Цвет: <?php echo $color->colors_title; ?> <img src="/<?php echo $color->file; ?>"
                                                                                    alt=""/>
                                    </div>
                                <?php } ?>
                                <h4>Размеры помещения</h4>
                                <div>
                                    Площадь, м<sup>2</sup>: <?php echo $calculation->n4; ?>
                                </div>
                                <div>
                                    Периметр, м: <?php echo $calculation->n5; ?>
                                </div>
                                <?php if ($calculation->n6 > 0) { ?>
                                    <div>
                                        <h4> Вставка</h4>
                                    </div>
                                    <?php if ($calculation->n6 == 314) { ?>
                                        <div> Белая</div>
                                    <?php } else { ?>
                                        <?php $color_model_1 = Gm_ceilingHelpersGm_ceiling::getModel('components'); ?>
                                        <?php $color_1 = $color_model_1->getColorId($calculation->n6); ?>
                                        <div>
                                            Цветная : <?php echo $color_1[0]->title; ?> <img
                                                    style='width: 50px; height: 30px;'
                                                    src="/<?php echo $color_1[0]->file; ?>"
                                                    alt=""/>
                                        </div>
                                    <?php } ?>
                                <?php } endif; ?>
                            <?php if ($calculation->n16) { ?>
                                <div>
                                    Скрытый карниз: <?php echo $calculation->n16; ?>
                                </div>
                            <?php } ?>

                            <?php if ($calculation->n12) { ?>
                                <h4>Установка люстры</h4>
                                <?php echo $calculation->n12; ?> шт.
                            <?php } ?>

                            <?php if ($calculation->n13) { ?>
                                <h4>Установка светильников</h4>
                                <?php foreach ($calculation->n13 as $key => $n13_item) {
                                    echo "<b>Количество:</b> " . $n13_item->n13_count . " шт - <b>Тип:</b>  " . $n13_item->type_title . " - <b>Размер:</b> " . $n13_item->component_title . "<br>";
                                    ?>
                                <?php }
                            } ?>

                            <?php if ($calculation->n14) { ?>
                                <h4>Обвод трубы</h4>
                                <?php foreach ($calculation->n14 as $key => $n14_item) {
                                    echo "<b>Количество:</b> " . $n14_item->n14_count . " шт  -  <b>Диаметр:</b>  " . $n14_item->component_title . "<br>";
                                    ?>
                                <?php }
                            } ?>

                            <?php if ($calculation->n15) { ?>
                                <h4>Шторный карниз Гильдии мастеров</h4>
                                <?php foreach ($calculation->n15 as $key => $n15_item) {
                                    echo "<b>Количество:</b> " . $n15_item->n15_count . " шт - <b>Тип:</b>   " . $n15_item->type_title . " <b>Длина:</b> " . $n15_item->component_title . "<br>";
                                    ?>
                                <?php }
                            } ?>
                            <?php if ($calculation->n27 > 0) { ?>
                                <h4>Шторный карниз</h4>
                                <?php if ($calculation->n16) echo "Скрытый карниз"; ?>
                                <?php if (!$calculation->n16) echo "Обычный карниз"; ?>
                                <?php echo $calculation->n27; ?> м.
                            <?php } ?>

                            <?php if ($calculation->n26) { ?>
                                <h4>Светильники Эcola</h4>
                                <?php foreach ($calculation->n26 as $key => $n26_item) {
                                    echo "<b>Количество:</b> " . $n26_item->n26_count . " шт - <b>Тип:</b>  " . $n26_item->component_title_illuminator . " -  <b>Лампа:</b> " . $n26_item->component_title_lamp . "<br>";
                                    ?>
                                <?php }
                            } ?>

                            <?php if ($calculation->n22) { ?>
                                <h4>Вентиляция</h4>
                                <?php foreach ($calculation->n22 as $key => $n22_item) {
                                    echo "<b>Количество:</b> " . $n22_item->n22_count . " шт - <b>Тип:</b>   " . $n22_item->type_title . " - <b>Размер:</b> " . $n22_item->component_title . "<br>";
                                    ?>
                                <?php }
                            } ?>

                            <?php if ($calculation->n23) { ?>
                                <h4>Диффузор</h4>
                                <?php foreach ($calculation->n23 as $key => $n23_item) {
                                    echo "<b>Количество:</b> " . $n23_item->n23_count . " шт - <b>Размер:</b>  " . $n23_item->component_title . "<br>";
                                    ?>
                                <?php }
                            } ?>

                            <?php if ($calculation->n29) { ?>
                                <h4>Переход уровня</h4>
                                <?php foreach ($calculation->n29 as $key => $n29_item) {
                                    echo "<b>Количество:</b> " . $n29_item->n29_count . " м - <b>Тип:</b>  " . $n29_item->type_title . " <br>";
                                    ?>
                                <?php }
                            } ?>
                            <h4>Прочее</h4>
                            <?php if ($calculation->n9 > 0) { ?>
                                <div>
                                    Углы, шт.: <?php echo $calculation->n9; ?>
                                </div>
                            <?php } ?>
                            <?php if ($calculation->n10 > 0) { ?>
                                <div>
                                    Криволинейный вырез, м: <?php echo $calculation->n10; ?>
                                </div>
                            <?php } ?>
                            <?php if ($calculation->n11 > 0) { ?>
                                <div>
                                    Внутренний вырез, м: <?php echo $calculation->n11; ?>
                                </div>
                            <?php } ?>
                            <?php if ($calculation->n7 > 0) { ?>
                                <div>
                                    Крепление в плитку, м: <?php echo $calculation->n7; ?>
                                </div>
                            <?php } ?>
                            <?php if ($calculation->n8 > 0) { ?>
                                <div>
                                    Крепление в керамогранит, м: <?php echo $calculation->n8; ?>
                                </div>
                            <?php } ?>
                            <?php if ($calculation->n17 > 0) { ?>
                                <div>
                                    Закладная брусом, м: <?php echo $calculation->n17; ?>
                                </div>
                            <?php } ?>
                            <?php if ($calculation->n19 > 0) { ?>
                                <div>
                                    Провод, м: <?php echo $calculation->n19; ?>
                                </div>
                            <?php } ?>
                            <?php if ($calculation->n20 > 0) { ?>
                                <div>
                                    Разделитель, м: <?php echo $calculation->n20; ?>
                                </div>
                            <?php } ?>
                            <?php if ($calculation->n21 > 0) { ?>
                                <div>
                                    Пожарная сигнализация, м: <?php echo $calculation->n21; ?>
                                </div>
                            <?php } ?>

                            <?php if ($calculation->dop_krepezh > 0) { ?>
                                <div>
                                    Дополнительный крепеж: <?php echo $calculation->dop_krepezh; ?>
                                </div>
                            <?php } ?>

                            <?php if ($calculation->n24 > 0) { ?>
                                <div>
                                    Сложность доступа к месту монтажа, м: <?php echo $calculation->n24; ?>
                                </div>
                            <?php } ?>

                            <?php if ($calculation->n30 > 0) { ?>
                                <div>
                                    Парящий потолок, м: <?php echo $calculation->n30; ?>
                                </div>
                            <?php } ?>
                            <?php if ($calculation->n32 > 0) { ?>
                                <div>
                                    Слив воды, кол-во комнат: <?php echo $calculation->n32; ?>
                                </div>
                            <?php } ?>
                            <?php $extra_mounting = (array)json_decode($calculation->extra_mounting); ?>
                            <?php if (!empty($extra_mounting)) { ?>
                                <div>
                                    <h4>Дополнительные работы</h4>
                                    <?php foreach ($extra_mounting as $dop) {
                                        echo "<b>Название:</b> " . $dop->title . "<br>";
                                    } ?>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            <?php } ?>
            <?php } ?>
        </div>
    </form>

    <script>
        jQuery(document).ready(function () {
            jQuery("input[name^='include_calculation']").click(function () {
                if (jQuery(this).prop("checked")) {
                    jQuery(this).closest("tr").removeClass("not-checked");
                } else {
                    jQuery(this).closest("tr").addClass("not-checked");
                }
                calculate_total();
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
            var flag = 0;
            jQuery("#sh_ceilings").click(function () {
                if (flag) {
                    jQuery(".section_ceilings").hide();
                    flag = 0;
                }
                else {
                    jQuery(".section_ceilings").show();
                    flag = 1;
                }
            });
            var flag1 = 0;
            jQuery("#sh_estimate").click(function () {
                if (flag1) {
                    jQuery(".section_estimate").hide();
                    flag1 = 0;
                }
                else {
                    jQuery(".section_estimate").show();
                    flag1 = 1;
                }
                jQuery(".section_estimate").each(function () {
                    var el = jQuery(this);
                    if (el.attr("vis") == "hide") el.hide();
                })
            });


            var flag2 = 0;
            jQuery("#sh_mount").click(function () {
                if (flag2) {
                    jQuery(".section_mount").hide();
                    flag2 = 0;
                }
                else {
                    jQuery(".section_mount").show();
                    flag2 = 1;
                }
                jQuery(".section_mount").each(function () {
                    var el = jQuery(this);
                    if (el.attr("vis") == "hide") el.hide();
                })
            });

        });

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
