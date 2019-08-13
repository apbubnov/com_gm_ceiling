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

JHtml::_('behavior.keepalive');
//JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');

// Load admin language file
$lang = JFactory::getLanguage();
$lang->load('com_gm_ceiling', JPATH_SITE);
$doc = JFactory::getDocument();
$doc->addScript(JUri::base() . '/media/com_gm_ceiling/js/form.js');

$project_total = 0;
$project_total_discount = 0;

$user = JFactory::getUser();
$userId = $user->get('id');

$model = Gm_ceilingHelpersGm_ceiling::getModel('calculations');
$calculations = $model->getProjectItems($this->item->id);


//$project_mounter = "<select id='jform_project_mounter' name='jform[project_mounter]' class='inputbox'>";
//// Iterate through all the results
//foreach ($results as $result)
//{
//	$project_mounter .= "<option value='$result->id'>$result->name</option>";
//}
//$project_mounter .= "</select>";

foreach ($calculations as $calculation) {

    $calculation->dealer_canvases_sum = double_margin($calculation->canvases_sum, $this->item->gm_canvases_margin, $this->item->dealer_canvases_margin);
    $calculation->dealer_components_sum = double_margin($calculation->components_sum, $this->item->gm_components_margin, $this->item->dealer_components_margin);
    $calculation->dealer_gm_mounting_sum = double_margin($calculation->mounting_sum, $this->item->gm_mounting_margin, $this->item->dealer_mounting_margin);

    $calculation->calculation_total = $calculation->dealer_canvases_sum + $calculation->dealer_components_sum + $calculation->dealer_gm_mounting_sum;
    $calculation->calculation_total_discount = $calculation->calculation_total * ((100 - $this->item->project_discount) / 100);
    $project_total += $calculation->calculation_total;
    $project_total_discount += $calculation->calculation_total_discount;

}


$project_total = round($project_total, 2);
$project_total_discount = round($project_total_discount, 2);

$extra_spend_array = Gm_ceilingHelpersGm_ceiling::decode_extra($this->item->extra_spend);
$penalty_array = Gm_ceilingHelpersGm_ceiling::decode_extra($this->item->penalty);
$bonus_array = Gm_ceilingHelpersGm_ceiling::decode_extra($this->item->bonus);
/* $month1 = date("n");
$year1 = date("Y");
if ($month1 == 12) {
    $month2 = 1;
    $year2 = $year1 + 1;
} else {
    $month2 = $month1 + 1;
    $year2 = $year1;
} */

/* $jdate = new JDate($this->item->project_mounting_from);
$current_from = $jdate->format('Y-m-d H:i:s');

$jdate = new JDate($this->item->project_mounting_to);
$current_to = $jdate->format('Y-m-d H:i:s'); */

echo parent::getPreloader();

?>
<?=parent::getButtonBack();?>
    <style>
        #jform_project_mounter-lbl {
            display: none;
        }
    </style>
    <h2 class = "center" >Просмотр проекта</h2>
<?php if ($this->item) { ?>
    <?php if (sizeof($calculations) > 0) { ?>
        <?php echo "<h3>Расчеты для проекта № ". $this->item->id ."</h3>"; ?>
        <!-- Nav tabs -->
        <ul class="nav nav-tabs" role="tablist">
            <?php foreach ($calculations as $k => $calculation) { ?>
                <li class="nav-item">
                    <a class="nav-link<?php if ($k == 0) {
                        echo " active";
                    } ?>" data-toggle="tab" href="#calculation<?php echo $calculation->id; ?>"
                       role="tab"><?php echo $calculation->calculation_title; ?></a>
                </li>
            <?php } ?>
        </ul>
    <?php } ?>

    <!-- Tab panes -->
    <div class="tab-content">
        <?php foreach ($calculations as $k => $calculation) { ?>
            <?php $mounters = json_decode($calculation->mounting_sum); ?>
            <?php $filename = "/calculation_images/" . md5("calculation_sketch" . $calculation->id) . ".svg"; ?>
            <div class="tab-pane<?php if ($k == 0) {
                echo " active";
            } ?>" id="calculation<?php echo $calculation->id; ?>" role="tabpanel">
                <h3><?php echo $calculation->calculation_title; ?></h3>
    <? if (!empty($filename)):?>
        <div class="sketch_image_block">
            <h3 class="section_header">
                Чертеж <i class="fa fa-sort-desc" aria-hidden="true"></i>
            </h3>
            <div class="section_content">
                <img class="sketch_image" src="<?php echo $filename.'?t='.time(); ?>" style="width:80vw;"/>
            </div>
        </div>
    <? endif; ?>
    <div class="row-fluid">
        <div class="span6">
            <?if($calculation->n1 && $calculation->n2 && $calculation->n3):?>
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
                <?php if ($calculation->n6 > 0) {?>
                    <div>
                        <h4> Вставка</h4>
                    </div>
                    <? if ($calculation->n6 == 314) {?>
                        <div> Белая </div>
                    <?php } else  {?>
                        <?php $color_model_1 = Gm_ceilingHelpersGm_ceiling::getModel('components'); ?>
                        <?php $color_1 = $color_model_1->getColorId($calculation->n6); ?>
                        <div>
                            Цветная : <?php echo $color_1[0]->title; ?> <img style='width: 50px; height: 30px;' src="/<?php echo $color_1[0]->file; ?>"
                                                                             alt=""/>
                        </div>
                    <?php }?>
                <?} endif; ?>
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
            <?php if ($calculation->n27> 0) { ?>
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

            <?php if ($calculation->n29) { ?>
                <h4>Переход уровня</h4>
                <?php foreach ($calculation->n29 as $key => $n29_item) {
                    echo "<b>Количество:</b> " . $n29_item->n29_count . " м - <b>Тип:</b>  " . $n29_item->type_title . " <br>";
                    ?>
                <?php }
            } ?>
            <h4>Прочее</h4>
            <?php if ($calculation->n9> 0) { ?>
                <div>
                    Углы, шт.: <?php echo $calculation->n9; ?>
                </div>
            <?php } ?>
            <?php if ($calculation->n10> 0) { ?>
                <div>
                    Криволинейный вырез, м: <?php echo $calculation->n10; ?>
                </div>
            <?php } ?>
            <?php if ($calculation->n11> 0) { ?>
                <div>
                    Внутренний вырез, м: <?php echo $calculation->n11; ?>
                </div>
            <?php } ?>
            <?php if ($calculation->n7> 0) { ?>
                <div>
                    Крепление в плитку, м: <?php echo $calculation->n7; ?>
                </div>
            <?php } ?>
            <?php if ($calculation->n8> 0) { ?>
                <div>
                    Крепление в керамогранит, м: <?php echo $calculation->n8; ?>
                </div>
            <?php } ?>
            <?php if ($calculation->n17> 0) { ?>
                <div>
                    Закладная брусом, м: <?php echo $calculation->n17; ?>
                </div>
            <?php } ?>
            <?php if ($calculation->n19> 0) { ?>
                <div>
                    Провод, м: <?php echo $calculation->n19; ?>
                </div>
            <?php } ?>
            <?php if ($calculation->n20> 0) { ?>
                <div>
                    Разделитель, м: <?php echo $calculation->n20; ?>
                </div>
            <?php } ?>
            <?php if ($calculation->n21> 0) { ?>
                <div>
                    Пожарная сигнализация, м: <?php echo $calculation->n21; ?>
                </div>
            <?php } ?>

            <?php if ($calculation->dop_krepezh> 0) { ?>
                <div>
                    Дополнительный крепеж: <?php echo $calculation->dop_krepezh; ?>
                </div>
            <?php } ?>

            <?php if ($calculation->n24> 0) { ?>
                <div>
                    Сложность доступа к месту монтажа, м: <?php echo $calculation->n24; ?>
                </div>
            <?php } ?>

            <?php if ($calculation->n30> 0) { ?>
                <div>
                    Парящий потолок, м: <?php echo $calculation->n30; ?>
                </div>
            <?php } ?>
            <?php if ($calculation->n32> 0) { ?>
                <div>
                    Слив воды, кол-во комнат: <?php echo $calculation->n32; ?>
                </div>
            <?php } ?>
            <? $extra_mounting = (array) json_decode($calculation->extra_mounting);?>
            <?php if (!empty($extra_mounting) ) { ?>
                <div>
                    <h4>Дополнительные работы</h4>
                    <? foreach($extra_mounting as $dop) {
                        echo "<b>Название:</b> " . $dop->title .  "<br>";
                    }?>
                </div>
            <?php } ?>
        </div>
            </div>
        <?php } ?>
    </div>
    <div class="container">
        <div class="row">
            <div class="col-xl-6 item_fields project-edit front-end-edit">
                <h4>Информация по проекту № <?php echo $this->item->id ?></h4>
                <?php $jdate = new JDate(JFactory::getDate($this->item->project_mounting_date)); ?>

                <br>
                <input type="hidden" name="option" value="com_gm_ceiling"/>
                <input type="hidden" name="task"
                       value="project.approve"/>
                <?php echo JHtml::_('form.token'); ?>

                <table class="table">
                    <tr>
                        <th>Номер договора</th>
                        <td><?php echo $this->item->id; ?></td>
                    </tr>
                    <tr>
                        <th>Дата и время монтажа</th>

                        <td>
                            <?php if ($this->item->project_mounting_date == "0000-00-00 00:00:00") { ?>
                                -
                            <?php } else { ?>
                                <?php $jdate = new JDate(JFactory::getDate($this->item->project_mounting_date)); ?>
                                <?php echo $jdate->format('d.m.Y H:i'); ?>
                            <?php } ?>
                        </td>

                    </tr>
                    <tr>
                        <th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_PROJECT_CLIENT_ID'); ?></th>
                        <td><?php echo $this->item->client_id; ?></td>
                    </tr>
                    <tr>
                        <th><?php echo JText::_('COM_GM_CEILING_CLIENTS_CLIENT_CONTACTS'); ?></th>
                        <? $mod = Gm_ceilingHelpersGm_ceiling::getModel('projectform');
                        $contact = $mod->getData($this->item->id); ?>
                        <td><?php echo $contact->client_contacts; ?></td>
                    </tr>
                    <tr>
                        <th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_PROJECT_PROJECT_INFO'); ?></th>
                        <td><?php echo $this->item->project_info; ?></td>
                    </tr>
                    <tr>
                        <th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_PROJECT_GM_CALCULATOR_NOTE'); ?></th>
                        <td><?php echo $this->item->gm_calculator_note; ?></td>
                    </tr>
                    <tr>
                        <th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_PROJECT_GM_CHIEF_NOTE'); ?></th>
                        <td><?php echo $this->item->gm_chief_note; ?></td>
                    </tr>
                    <tr>
                        <th>Замерщик</th>
                        <td><?php echo JFactory::getUser($this->item->project_calculator)->name; ?></td>
                    </tr>
                    <tr>
                        <th>Монтажная бригада</th>
                        <?php $mount_model = Gm_ceilingHelpersGm_ceiling::getModel('project'); ?>
                        <?php $mount = $mount_model->getMount($this->item->id); ?>
                        <td><?php echo $mount->name; ?></td>
                    </tr>
                </table>
                <table class="table calculation_sum">
                    <tr>
                        <th class="center">Название расчета</th>
                        <th class="center">Без скидки</th>
                        <th class="center">Со скидкой</th>
                    </tr>

                    <?php foreach ($calculations as $calculation) { ?>
                        <tr>
                            <td><?php echo $calculation->calculation_title; ?></td>
                            <td class="center"><?php echo round($calculation->calculation_total, 2); ?></td>
                            <td class="center"><?php echo round($calculation->calculation_total_discount, 2); ?></td>
                        </tr>
                    <?php } ?>
                    <tr>
                        <th class="right">Итого:</th>
                        <th class="center" id="project_total"><?php echo $project_total; ?></th>
                        <th class="center" id="project_total_discount"><?php echo $project_total_discount; ?></th>
                    </tr>
                </table>

                <div class="control-group">
                    <div class="controls">
                        <a class="btn btn-success"
                           href="<?php if ($userId == $user->dealer_id) echo JRoute::_('index.php?option=com_gm_ceiling&view=projects&type=chief&subtype=run');
                           else echo JRoute::_('index.php?option=com_gm_ceiling&view=projects&type=chief&subtype=run'); ?>"
                           title="">
                            Вернуться к монтажам
                        </a>
                    </div>
                </div>
                </form>
            </div>
            <div class="col-xl-6">
                <h4>Наряды на монтаж</h4>
                <table class="table">
                    <?php foreach ($calculations as $calculation) { ?>
                        <tr>
                            <th><?php echo $calculation->calculation_title; ?></th>
                            <td>
                                <?php echo $calculation->mounting_sum; ?> руб.
                            </td>
                            <td>
                                <?php $path = "/costsheets/" . md5($calculation->id . "-2") . ".pdf"; ?>
                                <?php if (file_exists($_SERVER['DOCUMENT_ROOT'] . $path)) { ?>
                                    <a href="<?php echo $path; ?>" class="btn btn-secondary"
                                       target="_blank">Посмотреть</a>
                                <?php } else { ?>
                                    Сохранится после утверждения
                                <?php } ?>
                            </td>
                        </tr>
                    <?php } ?>
                </table>
                <h4>Доп. затраты</h4>
                <div class="container">
                    <div class="row sm-margin-bottom">
                        <div class="col-sm-4">
                            <h5>Название</h5>
                            <div id="extra_spend_title_container">
                                <?php foreach ($extra_spend_array as $item) { ?>
                                    <div class='form-group'><input name='extra_spend_title[]'
                                                                   value='<?php echo $item['title']; ?>'
                                                                   class='form-control' type='text'></div>
                                <?php } ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <h5>Стоимость</h5>
                            <div id="extra_spend_value_container">
                                <?php foreach ($extra_spend_array as $item) { ?>
                                    <div class='form-group'><input name='extra_spend_value[]'
                                                                   value='<?php echo $item['value']; ?>'
                                                                   class='form-control' type='text'></div>
                                <?php } ?>
                            </div>
                        </div>
                    </div>

                </div>

                <h4>Штрафы</h4>
                <div class="container">
                    <div class="row sm-margin-bottom">
                        <div class="col-sm-4">
                            <h5>Название</h5>
                            <div id="penalty_title_container">
                                <?php foreach ($penalty_array as $item) { ?>
                                    <div class='form-group'><input name='penalty_title[]'
                                                                   value='<?php echo $item['title']; ?>'
                                                                   class='form-control' type='text'></div>
                                <?php } ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <h5>Стоимость</h5>
                            <div id="penalty_value_container">
                                <?php foreach ($penalty_array as $item) { ?>
                                    <div class='form-group'><input name='penalty_value[]'
                                                                   value='<?php echo $item['value']; ?>'
                                                                   class='form-control' type='text'></div>
                                <?php } ?>
                            </div>
                        </div>
                    </div>

                </div>
                <h4>Премии</h4>

                <div class="container">
                    <div class="row sm-margin-bottom">
                        <div class="col-sm-4">
                            <h5>Название</h5>
                            <div id="bonus_title_container">
                                <?php foreach ($bonus_array as $item) { ?>
                                    <div class='form-group'><input name='bonus_title[]'
                                                                   value='<?php echo $item['title']; ?>'
                                                                   class='form-control' type='text'></div>
                                <?php } ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <h5>Стоимость</h5>
                            <div id="bonus_value_container">
                                <?php foreach ($bonus_array as $item) { ?>
                                    <div class='form-group'><input name='bonus_value[]'
                                                                   value='<?php echo $item['value']; ?>'
                                                                   class='form-control' type='text'></div>
                                <?php } ?>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>


<?php } ?>