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

if ($month1 == 12) {
    $month2 = 1;
    $year2 = $year1 + 1;
} else {
    $month2 = $month1 + 1;
    $year2 = $year1;
}

$jdate = new JDate($this->item->project_mounting_from);
$current_from = $jdate->format('Y-m-d H:i:s');

$jdate = new JDate($this->item->project_mounting_to);
$current_to = $jdate->format('Y-m-d H:i:s');

/* $calendar = Gm_ceilingHelpersGm_ceiling::draw_calendar($this->item->id, $this->item->project_mounter, $month1, $year1, $current_from, $current_to);
$calendar .= Gm_ceilingHelpersGm_ceiling::draw_calendar($this->item->id, $this->item->project_mounter, $month2, $year2, $current_from, $current_to);
 */

?>

<?= parent::getButtonBack(); ?>
<h2 class="center">Просмотр проекта</h2>
<?php if ($this->item) : ?>
    <?php $model = Gm_ceilingHelpersGm_ceiling::getModel('calculations'); ?>
    <?php $calculations = $model->getProjectItems($this->item->id); ?>

    <div class="container">
        <div class="row">
            <div class="col-xl item_fields">
                <h4>Информация по проекту № <?php echo $this->item->id ?></h4>
                <form id="form-client"
                      action="/index.php?option=com_gm_ceiling&task=project.save_mount"
                      method="post" class="form-validate form-horizontal" enctype="multipart/form-data">
                    <table class="table">
                        <tr>
                            <th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_PROJECT_CLIENT_ID'); ?></th>
                            <td><?php echo $this->item->client_id; ?></td>
                        </tr>
                        <tr>
                            <th><?php echo JText::_('COM_GM_CEILING_CLIENTS_CLIENT_CONTACTS'); ?></th>
                            <? $contacts = $model->getClientPhone($this->item->client_id); ?>
                            <td><?php foreach ($contacts as $phone) echo $phone->client_contacts; ?></td>
                        </tr>
                        <tr>
                            <th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_PROJECT_PROJECT_INFO'); ?></th>
                            <td><?php echo $this->item->project_info; ?></td>
                        </tr>

                        <tr>
                            <th><?php echo JText::_('COM_GM_CEILING_PROJECTS_PROJECT_CALCULATION_DATE'); ?></th>
                            <td> <?php $jdate = new JDate(JFactory::getDate($this->item->project_calculation_date)); ?>
                                <?php if ($jdate->format('d.m.Y') == "00.00.0000" || $jdate->format('d.m.Y') == '30.11.-0001') { ?>
                                    -
                                <?php } else { ?>
                                    <?php echo $jdate->format('d.m.Y'); ?>
                                <?php } ?></td>
                        </tr>
                        <tr>
                            <th><?php echo JText::_('COM_GM_CEILING_PROJECTS_PROJECT_CALCULATION_DAYPART'); ?></th>
                            <td><?php $jdate = new JDate(JFactory::getDate($this->item->project_calculation_date)); ?>
                                <?php if ($jdate->format('H:i') == "00:00") { ?>
                                    -
                                <?php } else { ?>
                                    <?php echo $jdate->format('H:i'); ?>
                                <?php } ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Дата и время монтажа</th>
                            <td><?php $jdate = new JDate(JFactory::getDate($this->item->project_mounting_date)); ?>
                                <?php if ($this->item->project_mounting_date == "0000-00-00 00:00:00") { ?>
                                    -
                                <?php } else { ?>
                                    <?php echo $jdate->format('d.m.Y H:i'); ?>
                                <?php } ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Монтажная бригада</th>
                            <?php $mount_model = Gm_ceilingHelpersGm_ceiling::getModel('project'); ?>
                            <?php $mount = $mount_model->getMount($this->item->id); ?>
                            <td><?php echo $mount->name; ?></td>
                        </tr>
                    </table>
                    <?php $jdate = new JDate(JFactory::getDate($this->item->project_mounting_date)); ?>
                    <input name="id" value="<?php echo $this->item->id; ?>" type="hidden">
                    <input name="type" value="chief" type="hidden">
                    <input id="jform_project_mounting_from" type="hidden" name="jform[project_mounting_from]"
                           value="<?php echo $jdate->format('H:i'); ?>"/>
                    <input id="jform_project_mounting_date" type="hidden" name="jform[project_mounting_date]"
                           value="<?php echo $jdate->format('d.m.Y H:i'); ?>"/>
                    <input id="jform_project_mounter" type="hidden" name="jform[project_mounting]"
                           value="<?php echo ($mount->project_mounter) ? $mount->project_mounter : '1'; ?>"/>
                    <?php if ($this->item->project_status == 10) { ?>
                        <a class="btn btn btn-primary"
                           id="change_data">Изменить дату и время монтажа
                        </a>
                        <?php
                    } ?>
                    <div class="calendar_wrapper" style="display: none;">
                        <table>
                            <tr>
                                <td>
                                    <button id="calendar_prev" type="button" class="btn btn-secondary"> <<</button>
                                </td>
                                <td>
                                    <div id="calendar">
                                        <?php echo $calendar; ?>
                                    </div>
                                </td>
                                <td>
                                    <button id="calendar_next" type="button" class="btn btn-secondary"> >></button>
                                </td>
                            </tr>

                        </table>
                        <div class="control-group" id="save">
                            <div class="controls">
                                <button type="submit" class="validate btn btn-primary">
                                    Сохранить
                                </button>
                            </div>
                        </div>
                    </div>


                    <?php echo "<h3>Расчеты для проекта</h3>"; ?>
                    <!-- Nav tabs -->
                    <ul class="nav nav-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-toggle="tab" href="#summary" role="tab">Общее</a>
                        </li>
                        <?php foreach ($calculations as $k => $calculation) { ?>
                            <li class="nav-item">
                                <a class="nav-link" data-toggle="tab" href="#calculation<?php echo $calculation->id; ?>"
                                   role="tab"><?php echo $calculation->calculation_title; ?></a>
                            </li>
                        <?php } ?>
                        <li class="nav-item">
                            <button class="nav-link" id="add_calc">
                                <i class="fa fa-plus-square-o" aria-hidden="true"></i>
                            </button>
                        </li>
                    </ul>

                    <!-- Tab panes -->
                    <div class="tab-content">
                        <div class="tab-pane active" id="summary" role="tabpanel">
                            <table>
                                <tr>
                                    <th class="section_header" id="sh_ceilings">Потолки <i class="fa fa-sort-desc"
                                                                                           aria-hidden="true"></i></th>
                                    <th></th>
                                </tr>
                                <?php $project_total = 0; ?>
                                <?php $project_total_discount = 0;
                                foreach ($calculations as $calculation) { ?>
                                    <?php $dealer_canvases_sum = double_margin($calculation->canvases_sum, 0 /*$this->item->gm_canvases_margin*/, $this->item->dealer_canvases_margin); ?>
                                    <?php $dealer_components_sum = double_margin($calculation->components_sum, 0/* $this->item->gm_components_margin*/, $this->item->dealer_components_margin); ?>
                                    <?php $dealer_gm_mounting_sum = double_margin($calculation->mounting_sum, 0/*$this->item->gm_mounting_margin*/, $this->item->dealer_mounting_margin); ?>
                                    <?php $calculation_total = $dealer_canvases_sum + $dealer_components_sum + $dealer_gm_mounting_sum; ?>
                                    <?php $calculation_total_discount = $calculation_total * ((100 - $this->item->project_discount) / 100); ?>
                                    <?php $project_total += $calculation_total; ?>
                                    <?php $project_total_discount += $calculation_total_discount; ?>
                                    <?php
                                    $calculation->calculation_title; ?>
                                    <?php $total_square += $calculation->n4; ?>
                                    <?php $total_perimeter += $calculation->n5; ?>

                                    <tr class="section_ceilings">
                                        <td class="include_calculation">

                                            <input name='calculation_total[<?php echo $calculation->id; ?>]'
                                                   value='<?php echo $calculation_total; ?>' type='hidden'>
                                            <input name='calculation_total_discount[<?php echo $calculation->id; ?>]'
                                                   value='<?php echo $calculation_total_discount; ?>' type='hidden'>
                                            <input name='total_square[<?php echo $calculation->id; ?>]'
                                                   value='<?php echo $calculation->n4; ?>' type='hidden'>
                                            <input name='total_perimeter[<?php echo $calculation->id; ?>]'
                                                   value='<?php echo $calculation->n5; ?>' type='hidden'>
                                            <input name='calculation_total1[<?php echo $calculation->id; ?>]'
                                                   value='<?php echo $calculation_total_1; ?>' type='hidden'>
                                            <input name='calculation_total2[<?php echo $calculation->id; ?>]'
                                                   value='<?php echo $dealer_gm_mounting_sum_1; ?>' type='hidden'>
                                            <input name='calculation_total3[<?php echo $calculation->id; ?>]'
                                                   value='<?php echo $project_total_1; ?>' type='hidden'>
                                            <?php echo $calculation->calculation_title; ?>
                                        </td>
                                    </tr>
                                    <tr class="section_ceilings" id="">
                                        <td>Площадь/Периметр :</td>
                                        <td>
                                            <?php echo $calculation->n4; ?> м<sup>2</sup>
                                            / <?php echo $calculation->n5; ?> м
                                        </td>
                                    </tr>
                                    <tr class="section_ceilings">
                                        <?php if ($this->item->project_discount != 0) { ?>
                                            <td>Без скидки/Со скидкой :</td>
                                            <td id="calculation_total"> <?php echo round($calculation_total, 0); ?> руб.
                                                /
                                            </td>
                                            <td id="calculation_total_discount"> <?php echo round($calculation_total_discount, 0); ?>
                                                руб.
                                            </td>
                                        <?php } else { ?>
                                            <td>Итого</td>
                                            <td id="calculation_total"> <?php echo round($calculation_total, 0); ?>
                                                руб.
                                            </td>
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
                                    <?php if ($this->item->project_discount != 0) { ?>
                                        <th>Сумма без скидки/со скидкой :</th>
                                        <th id="project_total"> <?php echo round($project_total, 0); ?> руб. /</th>
                                        <th id="project_total_discount"> <?php echo round($project_total_discount, 0); ?>
                                            руб.
                                        </th>
                                    <?php }
                                    else { ?>
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
                                <tr class="section_estimate" id="section_estimate_<?= $calculation->id; ?>"
                                    style="display:none;">
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
                                    <?php }
                                    $json = json_encode($pdf_names); ?>
                                </tr>


                                <?php if ($this->item->project_mounter != 'Монтажная бригада ГМ') { ?>
                                    <tr>
                                        <th id="sh_mount"> Наряд на монтаж <i class="fa fa-sort-desc"
                                                                              aria-hidden="true"></i></th>
                                    </tr>
                                    <?php foreach ($calculations as $calculation) { ?>
                                        <tr class="section_mount" id="section_mount_<?= $calculation->id; ?>" style="display:none;">
                                        <td><?php echo $calculation->calculation_title; ?></td>
                                        <td>
                                            <?php $path = "/costsheets/" . md5($calculation->id . "mount_single") . ".pdf"; ?>
                                            <?php $pdf_names_mount[] = array("name" => $calculation->calculation_title, "filename" => md5($calculation->id . "mount_single") . ".pdf", "id" => $calculation->id); ?>
                                            <?php if (file_exists($_SERVER['DOCUMENT_ROOT'] . $path)) { ?>
                                                <a href="<?php echo $path; ?>" class="btn btn-secondary"
                                                   target="_blank">Посмотреть</a>
                                            <?php } else { ?>
                                                После договора
                                            <?php } ?>
                                        </td>

                                    <?php } ?>
                                    </tr>
                                <?php } ?>
                            </table>
                        </div>
                        <?php foreach ($calculations as $k => $calculation) { ?>
                            <?php $mounters = json_decode($calculation->mounting_sum); ?>
                            <?php $filename = "/calculation_images/" . md5("calculation_sketch" . $calculation->id) . ".svg"; ?>
                            <div class="tab-pane" id="calculation<?php echo $calculation->id; ?>" role="tabpanel">
                                <h3><?php echo $calculation->calculation_title; ?></h3>

                                <a class="btn btn-primary"
                                   href="index.php?option=com_gm_ceiling&view=calculationform&type=calculator&subtype=calendar&calc_id=<?php echo $calculation->id; ?>">Изменить
                                    расчет</a>
                                <div class="sketch_image_block">
                                    <h3 class="section_header">
                                        Чертеж <i class="fa fa-sort-desc" aria-hidden="true"></i>
                                    </h3>
                                    <div class="section_content">
                                        <img class="sketch_image" src="<?php echo $filename.'?t='.time(); ?>" style="width:80vw;"/>
                                    </div>
                                </div>
                                <div class="row-fluid">
                                    <div class="span6">
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
                                                Цвет: <?php echo $color->title; ?> <img
                                                        src="/<?php echo $color->file; ?>"
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
                                            <? if ($calculation->n6 == 303) { ?>
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
                                        <? } ?>


                                        <?php if ($calculation->transport) { ?>
                                            <h4>Транспортные расходы</h4>
                                            <div>
                                                Транспортные расходы, шт.: <?php echo $calculation->transport; ?>
                                            </div>
                                        <?php } ?>

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
                                        <?php if ($calculation->n27) { ?>
                                            <h4>Шторный карниз</h4>
                                            <? if ($calculation->n16) echo "Скрытый карниз"; ?>
                                            <? if (!$calculation->n16) echo "Обычный карниз"; ?>
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


                                        <?php if ($calculation->n9) { ?>
                                            <h4>Прочее</h4>
                                            <div>
                                                Углы, шт.: <?php echo $calculation->n9; ?>
                                            </div>
                                        <?php } ?>
                                        <?php if ($calculation->n10) { ?>
                                            <div>
                                                Криволинейный вырез, м: <?php echo $calculation->n10; ?>
                                            </div>
                                        <?php } ?>
                                        <?php if ($calculation->n11) { ?>
                                            <div>
                                                Внутренний вырез, м: <?php echo $calculation->n11; ?>
                                            </div>
                                        <?php } ?>
                                        <?php if ($calculation->n7) { ?>
                                            <div>
                                                Крепление в плитку, м: <?php echo $calculation->n7; ?>
                                            </div>
                                        <?php } ?>
                                        <?php if ($calculation->n8) { ?>
                                            <div>
                                                Крепление в керамогранит, м: <?php echo $calculation->n8; ?>
                                            </div>
                                        <?php } ?>
                                        <?php if ($calculation->n17) { ?>
                                            <div>
                                                Закладная брусом, м: <?php echo $calculation->n17; ?>
                                            </div>
                                        <?php } ?>
                                        <?php if ($calculation->n19) { ?>
                                            <div>
                                                Провод, м: <?php echo $calculation->n19; ?>
                                            </div>
                                        <?php } ?>
                                        <?php if ($calculation->n20) { ?>
                                            <div>
                                                Разделитель, м: <?php echo $calculation->n20; ?>
                                            </div>
                                        <?php } ?>
                                        <?php if ($calculation->n21) { ?>
                                            <div>
                                                Пожарная сигнализация, м: <?php echo $calculation->n21; ?>
                                            </div>
                                        <?php } ?>

                                        <?php if ($calculation->dop_krepezh) { ?>
                                            <div>
                                                Дополнительный крепеж: <?php echo $calculation->dop_krepezh; ?>
                                            </div>
                                        <?php } ?>

                                        <?php if ($calculation->n24) { ?>
                                            <div>
                                                Сложность доступа к месту монтажа, м: <?php echo $calculation->n24; ?>
                                            </div>
                                        <?php } ?>
                                    </div>
                                    <div class="span6">

                                    </div>
                                </div>
                            </div>
                        <?php } ?>

                    </div>
            </div>
            </form>
        </div>
    </div>
    </div>

    <script type="text/javascript" src="/components/com_gm_ceiling/create_calculation.js"></script>
    <script type="text/javascript" src="/components/com_gm_ceiling/views/project/common_table.js"></script>
    <script type="text/javascript">
        var project_id = "<?php echo $this->item->id; ?>";
        jQuery(document).ready(function () {
            jQuery("input[name^='include_calculation']").click(function () {
                if (jQuery(this).prop("checked")) {
                    jQuery(this).closest("tr").removeClass("not-checked");
                } else {
                    jQuery(this).closest("tr").addClass("not-checked");
                }
                calculate_total();
            });
            jQuery("#change_data").click(function () {
                jQuery("#mounter_wraper").toggle();
                jQuery("#title").toggle();
                jQuery(".calendar_wrapper").toggle();
                jQuery(".buttons_wrapper").toggle();
                jQuery("#mounting_date_control").show();
                jQuery("#calendar_wrapper").show();
            });

            document.getElementById('add_calc').onclick = function()
            {
                create_calculation(<?php echo $this->item->id; ?>);
            };

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


            var preloader = '<?=parent::getPreloaderNotJS();?>';
            var calendar_toggle = 0,
                month = <?php echo date("n"); ?>,
                year = <?php echo date("Y"); ?>;
            jQuery('body').append(preloader);
            //jQuery("#jform_project_mounting_daypart").val(jQuery('#hours_list').val());
            jQuery("#jform_project_mounting_date").mask("99.99.9999");

            jQuery("#jform_project_mounter").change(function () {
                update_calendar();
            });



            var hours_list = "<select id='hours_list'>";
            hours_list += "<option value='09:00:00'>09:00</option>";
            hours_list += "<option value='10:00:00'>10:00</option>";
            hours_list += "<option value='11:00:00'>11:00</option>";
            hours_list += "<option value='12:00:00'>12:00</option>";
            hours_list += "<option value='13:00:00'>13:00</option>";
            hours_list += "<option value='14:00:00'>14:00</option>";
            hours_list += "<option value='15:00:00'>15:00</option>";
            hours_list += "<option value='16:00:00'>16:00</option>";
            hours_list += "<option value='17:00:00'>17:00</option>";
            hours_list += "<option value='18:00:00'>18:00</option>";
            hours_list += "<option value='19:00:00'>19:00</option>";
            hours_list += "<option value='20:00:00'>20:00</option>";
            hours_list += "<option value='21:00:00'>21:00</option>";
            hours_list += "</select>";

            listening();



            jQuery("#mounter_prev").click(function () {
                jQuery("#jform_project_mounter option:selected").prop("selected", false).prev("option").prop("selected", true);
                jQuery("#jform_project_mounter").change();
            });
            jQuery("#mounter_next").click(function () {
                jQuery("#jform_project_mounter option:selected").prop("selected", false).next("option").prop("selected", true);
                jQuery("#jform_project_mounter").change();
            });

            jQuery("#calendar_prev").click(function () {
                if (month == 1) {
                    month = 12;
                    year = year - 1;
                } else {
                    month = month - 1;
                }
                update_calendar();
            });
            jQuery("#calendar_next").click(function () {
                if (month == 12) {
                    month = 1;
                    year = year + 1;
                } else {
                    month = month + 1;
                }
                update_calendar();
            });
            update_calendar();


        });

        function update_calendar() {
            jQuery(".PRELOADER_GM").addClass('PRELOADER_GM_OPACITY');
            jQuery.ajax({
                type: 'POST',
                url: "index.php?option=com_gm_ceiling&task=update_calendar",
                data: {
                    project_id: <?php echo $this->item->id; ?>,
                    project_mounter: jQuery("#jform_project_mounter").val(),
                    current_from: jQuery("#jform_project_mounting_date").val()

                    //current_to: jQuery("#jform_project_mounting_to").val()
                },
                success: function (data) {
                    jQuery("#calendar").html(data);
                    jQuery(".PRELOADER_GM").remove();
                    listening();
                },
                dataType: "text",
                timeout: 10000,
                error: function () {
                    var n = noty({
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "error",
                        text: "Ошибка при попытке обновить календарь. Сервер не отвечает"
                    });
                }
            });
        }

        var mountArray = {};

        function selectTimeF(obj) {
            obj = jQuery(obj);
            var sel = obj.val();
            var mountObj = jQuery('#selectMount').html('');
            jQuery.each(mountArray[sel], function (key, val) {
                var option = jQuery('<option>').html(val.mount).val(val.id);
                if (val.id == jQuery('#jform_project_mounting').val()) option.attr('selected', '');
                mountObj.append(option);
            });
        }

        function selectMountF(obj) {
            obj = jQuery(obj);
            var input = jQuery('input[name="project_mounter"]');
            input.val(obj.val());
        }

        function listening() {
            jQuery(".b-calendar__day").click(function () {
                var this_td = jQuery(this),
                    date = this_td.data('date'),
                    project_mounter = jQuery("#selectMount").val();
                // console.log(project_mounter);
                //Задаем дату начала
                //if(calendar_toggle == 0) {

                jQuery.ajax({
                    type: 'POST',
                    url: "index.php?option=com_gm_ceiling&task=get_calendar",
                    data: {
                        date: date
                        //project_mounter: project_mounter
                    },
                    success: function (data) {

                        data = JSON.parse(data);
                        var result = null;
                        if (data.message == 0) {
                            var selectTime = jQuery('<select class="form-control" >').attr({
                                'id': 'selectTime',
                                'onchange': 'selectTimeF(this);'
                            });
                            var selectMount = jQuery('<select class="form-control">').attr({
                                'id': 'selectMount',
                                'onchange': 'selectMountF(this);'
                            });
                            jQuery.each(data.info, function (key, val) {
                                mountArray[key] = val;
                                var option = jQuery('<option>').html(key).val(key);
                                var currentDate = (jQuery("#jform_project_mounting_date").val()).replace(/(\d+)\.(\d+)\.(\d+)/, '$3-$2-$1');
                                if (key == jQuery('#jform_project_mounting_from').val() && date == currentDate) option.attr('selected', '');
                                selectTime.append(option);
                            });
                            var select = jQuery('<div>').append(selectTime);
                            var mount = jQuery('<div>').append(selectMount);

                            result = select.html() + mount.html();
                        }
                        else if (data.message == 1) result = data.info;

                        noty({
                            layout: 'center',
                            modal: true,
                            text: '<br>Выберите время <strong>начала</strong> монтажа:<br>' + result,
                            buttons: [
                                {
                                    addClass: 'btn btn-danger', text: 'Отмена', onClick: function ($noty) {
                                    $noty.close();
                                }
                                },
                                {
                                    addClass: 'btn btn-primary', text: 'ОК', onClick: function ($noty) {
                                    jQuery(".b-calendar__day").removeClass("current_project");
                                    jQuery("input[name='jform[project_mounting_date]']").val(date);
                                    jQuery("input[name='jform[project_mounting_from]']").val(date + " " + jQuery('#selectTime').val());
                                    jQuery("input[name='jform[project_mounting]']").val(jQuery('#selectMount').val());
                                    //jQuery("input[name ='jform_project_mounting_to']").val(date + " " + jQuery('#hours_list').val());
                                    calendar_toggle = 1;
                                    this_td.addClass("current_project");
                                    $noty.close();
                                }
                                }
                            ]
                        });
                        jQuery('#selectTime').change();
                    },
                    dataType: "text",
                    timeout: 10000,
                    error: function () {
                        var n = noty({
                            theme: 'relax',
                            layout: 'center',
                            maxVisible: 5,
                            type: "error",
                            text: "Ошибка при попытке получить список занятых в этот день монтажников. Сервер не отвечает"
                        });
                    }
                });

            })
        };
    </script>

    <?php
else:
    echo JText::_('COM_GM_CEILING_ITEM_NOT_LOADED');
endif;
?>
<script language="JavaScript">
    function PressEnter(your_text, your_event) {
        if (your_text != "" && your_event.keyCode == 13)
            jQuery("#update_discount").click();
    }


</script>
