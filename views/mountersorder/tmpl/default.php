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
    foreach ($calculation_ids as $value) {
        if(!$service){
            $DataOfProject[$value->id] = Gm_ceilingHelpersGm_ceiling::calculate_mount(0, $value->id, null);
        }
        else{
            $DataOfProject[$value->id] = Gm_ceilingHelpersGm_ceiling::calculate_mount(0, $value->id, null,null,"mount");
        }
        foreach ($stages as $stage) {
            foreach ($DataOfProject[$value->id]["mounting_data"] as $val) {
                if($val['stage']==$stage || $stage == 1){
                    if (!array_key_exists($val["title"], $AllCalc)) {
                        $AllCalc[$val["title"]] = ["title"=>$val["title"], "gm_salary"=>$val["gm_salary"], "dealer_salary"=>$val["dealer_salary"], "quantity"=>$val["quantity"], "gm_salary_total"=>$val["gm_salary_total"], "dealer_salary_total"=>$val["dealer_salary_total"]];
                    } else {
                        $AllCalc[$val["title"]]["quantity"] += $val["quantity"];
                        $AllCalc[$val["title"]]["gm_salary_total"] += $val["gm_salary_total"];
                        $AllCalc[$val["title"]]["dealer_salary_total"] += $val["dealer_salary_total"];
                    }
                }
            }
        }
    }
}
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
                    <?php if (!empty($DataOfTransport)) { ?>
                        <tr class="caption">
                            <td colspan="4" style="text-align: center; background-color: #ffffff;">Транспортные расходы</td>
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
                <div class="ceiling">
                    <img src="/calculation_images/<?php echo md5("calculation_sketch".$value->id); ?>.svg" class="image-ceiling">
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
                                foreach ($DataOfProject[$value->id]["mounting_data"] as $val) {
                                    if($val['stage'] == $stage || $stage == 1){ ?>
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
                                                    $calc_sum_stage +=  $val["gm_salary_total"];
                                                    echo $val["gm_salary_total"];
                                                    ?>
                                                </td>
                                            <?php } else { ?>
                                                <td>
                                                    <?php
                                                    $calc_sum_stage +=  $val["dealer_salary_total"];
                                                    echo $val["dealer_salary_total"];
                                                    ?>
                                                </td>
                                            <?php } ?>
                                        </tr>
                                        <?php
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
                        if(stage == 1 || stage == 2){
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
                for (var i = elemsFiles.length; i--;) {
                    if (elemsFiles[i].files.length < 1) {
                        continue;
                    }
                    arrayCalcImages.push({
                        calc_id: elemsFiles[i].getAttribute('data-calc-id'),
                        type: elemsFiles[i].getAttribute('data-img-type'),
                        images: []
                    });
                    jQuery.each(elemsFiles[i].files, function(key, value) {
                        arrayCalcImages[i].images.push(value.name);
                        formData.append(key, value);
                    });
                }
                console.log(arrayCalcImages);
                formData.append('date', date);
                formData.append('url_proj', url_proj);
                formData.append('note', note);
                formData.append('stage', stage);
                formData.append('arrayCalcImages', JSON.stringify(arrayCalcImages));

                jQuery.ajax({
                    type: "POST",
                    url: "index.php?option=com_gm_ceiling&task=mountersorder.MountingComplited",
                    dataType: 'json',
                    cache: false,
                    processData: false, // Не обрабатываем файлы (Don't process the files)
                    contentType: false, // Так jQuery скажет серверу что это строковой запрос
                    data: formData,
                    success: function(msg) {
                        console.log(msg);
                        if (msg[0].project_status == 11 || msg[0].project_status == 24 || msg[0].project_status == 25 || msg[0].project_status == 26) {
                            window.location.href = "/index.php?option=com_gm_ceiling&&view=mounterscalendar"
                        }
                    },
                    error: function(data) {
                        console.log(data);
                    }
                });
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
