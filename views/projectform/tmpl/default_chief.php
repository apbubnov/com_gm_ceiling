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

$user = JFactory::getUser();
$userId = $user->get('id');

$user_groups = $user->groups;
if(in_array('17',$user_groups)){
    $isNMS = true;
}
/*_____________блок для всех моделей/models block________________*/
$calculationsModel = Gm_ceilingHelpersGm_ceiling::getModel('calculations');
$mountModel = Gm_ceilingHelpersGm_ceiling::getModel('mount');
$projects_mounts_model = Gm_ceilingHelpersGm_ceiling::getModel('projects_mounts');
$apiPhoneModel = Gm_ceilingHelpersGm_ceiling::getModel('api_phones');

/*________________________________________________________________*/

$stages = [];
$json_mount = $this->item->mount_data;
if(!empty($this->item->mount_data)){
    $mount_types = $projects_mounts_model->get_mount_types();
    $this->item->mount_data = json_decode(htmlspecialchars_decode($this->item->mount_data));
    foreach ($this->item->mount_data as $value) {
        $value->stage_name = $mount_types[$value->stage];
        if(!array_key_exists($value->mounter,$stages)){
            $stages[$value->mounter] = array((object)array("stage"=>$value->stage,"time"=>$value->time));
        }
        else{
            array_push($stages[$value->mounter],(object)array("stage"=>$value->stage,"time"=>$value->time));
        }
    }
}


//address
$address = Gm_ceilingHelpersGm_ceiling::parseProjectInfo($this->item->project_info);


if(!empty($address->street)&&!empty($address->house)) {
    $addressForMap = "$address->street,$address->house";
    if (!empty($porch)) {
        $addressForMap .= ",$address->porch";
    }
}
$all_advt = $apiPhoneModel->getAdvt();
if ($this->item->api_phone_id == 10) {
    $repeatModel = Gm_ceilingHelpersGm_ceiling::getModel('repeatrequest');
    $repeat_advt = $repeatModel->getDataByProjectId($this->item->id);
    if (!empty($repeat_advt->advt_id)) {
        $advt = $apiPhoneModel->getDataById($repeat_advt->advt_id);
    } else {
        $advt = $apiPhoneModel->getDataById(10);
    }
} else {
    if (!empty($this->item->api_phone_id)) {
        $advt = $apiPhoneModel->getDataById($this->item->api_phone_id);
    }

}
if(!empty($advt)){
    $reklama = $advt->number . ' ' . $advt->name . ' ' . $advt->description;
}
else{
    $reklama = '-';
}

echo parent::getPreloader();
?>

<?=parent::getButtonBack();?>

<link rel="stylesheet" href="/components/com_gm_ceiling/views/projectform/tmpl/css/style.css" type="text/css" />
<style>
    .bottom_margin{
        margin-bottom: 1em !important;
    }
</style>
<h2 class="center" style="margin-bottom: 1em;">Просмотр проекта № <?php echo $this->item->id; ?></h2>
<form id="form-project" action="<?php echo JRoute::_('index.php?option=com_gm_ceiling&task=project.approve'); ?>" method="post" class="form-validate form-horizontal" enctype="multipart/form-data">
    <?php if ($this->item) {
        $item = $this->item;
        //print_r($this->item);?>
        <?php include_once('components/com_gm_ceiling/views/project/common_table.php'); ?>
        <div class="row">
            <div class="col-xs-12 col-md-6 no_padding">
                <h4>Информация по проекту № <?= $item->id;?></h4>
                <input type="hidden" name="jform[id]" value="<?php echo $this->item->id; ?>"/>
                <input type="hidden" name="jform[ordering]" value="<?php echo $this->item->ordering; ?>"/>
                <input type="hidden" name="jform[state]" value="<?php echo $this->item->state; ?>"/>
                <input type="hidden" name="jform[checked_out]" value="<?php echo $this->item->checked_out; ?>"/>
                <input type="hidden" name="jform[checked_out_time]" value="<?php echo $this->item->checked_out_time; ?>"/>
                <input name="project_id" id="project_id" value="<?php echo $this->item->id; ?>" type="hidden">
                <input type="hidden" id = "jform_project_status" name="jform[project_status]" value="<?=$item->project_status?>"/>
                <?php if (empty($this->item->created_by)): ?>
                    <input type="hidden" name="jform[created_by]" value="<?php echo JFactory::getUser()->id; ?>"/>
                <?php else: ?>
                    <input type="hidden" name="jform[created_by]" value="<?php echo $this->item->created_by; ?>"/>
                <?php endif; ?>
                <?php if (empty($this->item->modified_by)): ?>
                    <input type="hidden" name="jform[modified_by]" value="<?php echo JFactory::getUser()->id; ?>"/>
                <?php else: ?>
                    <input type="hidden" name="jform[modified_by]" value="<?php echo $this->item->modified_by; ?>"/>
                <?php endif; ?>
                <?php $jdate = new JDate(JFactory::getDate($this->item->project_mounting_date)); ?>
                <input name = "jform[project_new_calc_date]" id = "jform_project_new_calc_date" value="<?php if (isset($this->item->project_calculation_date)) { echo $this->item->project_calculation_date; } ?>" type="hidden">
                <input name = "jform[project_gauger]" id = "jform_project_gauger" value="<?php if ($this->item->project_calculator != null) { echo $this->item->project_calculator; } ?>" type="hidden">
                <input id="jform_project_gauger_old" type="hidden" name="jform_project_gauger_old" value="<?php if ($this->item->project_calculator != null) { echo $this->item->project_calculator; } else { echo "0"; } ?>"/>
                <input id="jform_project_calculation_date_old" type="hidden" name="jform_project_calculation_date_old" value="<?php if (isset($this->item->project_calculation_date)) { echo $this->item->project_calculation_date;} ?>"/>
                <input id="mount" type="hidden" name="mount" value="<?php echo $json_mount; ?>"/>
                <input type="hidden" name="option" value="com_gm_ceiling"/>
                <input type="hidden" name="task" value="project.approve"/>

                <div class="row bottom_margin">
                    <div class="col-md-6 col-xs-6">
                        <b>Номер договора</b>
                    </div>
                    <div class="col-md-6 col-xs-6">
                        <?php echo $this->item->id; ?>
                    </div>
                </div>
                <div class="row bottom_margin">
                    <div class="col-md-6 col-xs-6">
                        <b>Статус</b>
                    </div>
                    <div class="col-md-6 col-xs-6">
                        <?php
                        if ($this->item->project_status == 1) {
                            $status = "Ждет замера";
                        } else if ($this->item->project_status == 5) {
                            $status = "В производстве";
                        } else if ($this->item->project_status == 6) {
                            $status = "На раскрое";
                        } else if ($this->item->project_status == 7) {
                            $status = "Укомплектован";
                        } else if ($this->item->project_status == 8) {
                            $status = "Выдан";
                        } else if ($this->item->project_status == 9) {
                            $status = "Деактевирован";
                        } else if ($this->item->project_status == 10) {
                            $status = "Ожидает монтаж";
                        } else if ($this->item->project_status == 11) {
                            $status = "Монтаж выполнен";
                        } else if ($this->item->project_status == 12) {
                            $status = "Закрыт";
                        } else if ($this->item->project_status == 13) {
                            $status = "Ожидает оплаты";
                        } else if ($this->item->project_status == 14) {
                            $status = "Оплачен";
                        } else if ($this->item->project_status == 15) {
                            $status = "Отказ от сотруднечества";
                        } else if ($this->item->project_status == 16) {
                            $status = "Монтаж";
                        } else if ($this->item->project_status == 17) {
                            $status = "Монтаж недовыполнен";
                        } else if ($this->item->project_status == 19) {
                            $status = "Собран";
                        } else if ($this->item->project_status == 22) {
                            $status = "Отказ от производства";
                        } else if ($this->item->project_status == 4) {
                            $status = "Не назначен на монтаж";
                        }
                        echo $status;
                        ?>
                    </div>
                </div>
                <div class="row bottom_margin">
                    <div class="col-md-6 col-xs-6">
                        <b>Клиент</b>
                    </div>
                    <div class="col-md-6 col-xs-6">
                        <a href="/index.php?option=com_gm_ceiling&view=clientcard&id=<?php echo $item->_client_id?>"><?php echo $item->client_id; ?></a>
                    </div>
                </div>
                <div class="row bottom_margin">
                    <div class="col-md-6 col-xs-6">
                        <b>Телефон</b>
                    </div>
                    <div class="col-md-6 col-xs-6">
                        <?php echo "<a href='tel:+$item->client_contacts'>$item->client_contacts</a>" ?>
                    </div>
                </div>
                <div class="row bottom_margin">
                    <div class="col-md-6 col-xs-6">
                        <b>Адрес</b>
                    </div>
                    <div class="col-md-6 col-xs-6">
                        <a target="_blank" href="https://yandex.ru/maps/?mode=search&text=<?=$addressForMap;?>">
                            <?=$item->project_info;?>
                        </a>
                    </div>
                </div>
                <div class="row bottom_margin">
                    <div class="col-md-6 col-xs-6">
                        <b>Дата замера</b>
                    </div>
                    <div class="col-md-6 col-xs-6">
                        <?php if ($this->item->project_calculation_date == "0000-00-00 00:00:00") { ?>
                            -
                        <?php } else { ?>
                            <?php $jdate = new JDate(JFactory::getDate($this->item->project_calculation_date)); ?>
                            <?php echo $jdate->format('d.m.Y H:i'); ?>
                        <?php } ?>
                    </div>
                </div>
                <div class="row bottom_margin">
                    <div class="col-md-6 col-xs-6">
                        <b>Замерщик</b>
                    </div>
                    <div class="col-md-6 col-xs-6">
                        <?= !empty($item->project_calculator) ? JFactory::getUser($item->project_calculator)->name : '-';?>
                    </div>
                </div>
                <div class="row bottom_margin">
                    <div class="col-md-4 col-xs-4">
                        <b>Реклама</b>
                    </div>
                    <div class="col-md-4 col-xs-4">
                        <?= $reklama; ?>
                    </div>
                    <div class="col-md-4 col-xs-4">
                        <button class="btn btn-sm btn-primary" type="button" id="change_rek">
                            <i class="fas fa-edit"></i>
                        </button>
                    </div>
                </div>
                <div class="row bottom_margin">
                    <div class="col-md-6 col-xs-6">
                        <b>Примечание к монтажу</b>
                    </div>
                    <div class="col-md-6 col-xs-6">
                        <textarea name="jform[mount_note]" id="jform_mount_note" placeholder="Примечание начальника МС" aria-invalid="false" class="form-control"><?php //вывести по-другому ?></textarea>
                    </div>
                </div>
                <div class="row bottom_margin center">
                    <div class="col-md-12">
                        <b>Монтаж</b>
                    </div>
                </div>
                <div class="row bottom_margin">
                    <?php foreach ($this->item->mount_data as $value){ ?>
                        <div class="row">
                            <div class="col-md-4 col-xs-4">
                                <?php echo $value->time;?>
                            </div>
                            <div class="col-md-4 col-xs-4">
                                <?php echo $value->stage_name;?>
                            </div>
                            <div class="col-md-4 col-xs-4">
                                <?php echo JFactory::getUser($value->mounter)->name;?>
                            </div>
                        </div>
                    <?php }?>
                </div>
                <?php if ($userId == $user->dealer_id) { ?>
                    <input name="type" value="chief" type="hidden">
                <?php } else { ?>
                    <input name="type" value="gmchief" type="hidden">
                <?php } ?>
            </div>
            <div class="col-xs-12 col-md-6">
                <h4 class="center"> Примечания</h4>
                <?php include_once('components/com_gm_ceiling/views/project/project_notes.php'); ?>
            </div>
        </div>

        <div class="row bottom_margin">
            <div class="col-md-12">
                <?php
                    if ($this->item->project_status != 11 && $this->item->project_status != 12) {
                        echo "<h4 class='center'>Назначить/изменить монтажную бригаду, время и дату</h4>";
                        echo '<div id="calendar_mount" align="center"></div>';
                    }
                ?>
            </div>
        </div>
        <div class="row bottom_margin center">
            <div class="col-md-12">
                <?php if($this->item->project_status <=4) { ?>
                    <button type="button" data-status="5" class="btn_submit btn btn-primary">Запустить в производство</button>
                <?php } ?>
            </div>
        </div>
        <div class="row bottom_margin center">
            <div class="col-md-12">
                <button data-status="<?=$this->item->project_status;?>" type="button" class="btn_submit btn btn-primary">Сохранить</button>
            </div>
        </div>
    <?php } ?>
</form>

<div class="modal_window_container" id="mw_container">
    <button type="button" class="close_btn" id="close_mw"><i class="fa fa-times fa-times-tar" aria-hidden="true"></i></button>
    <div class="modal_window" id="mw_measures_calendar"></div>
    <div class="modal_window" id="mw_mounts_calendar"></div>
    <div id="mw_advt" class="modal_window">
                <h4>Изменение/добавление рекламы</h4>
                <label>Выберите или добавьте новую рекламу</label>
                <div class="row">
                    <div class="col-md-2"></div>
                    <div class="col-xs-12 col-md-4">
                        <p>
                            <label><strong>Выбрать:</strong></label>
                        </p>
                        <select id="advt_choose" class="form-control">
                            <option value="0">Выберите рекламу</option>
                            <?php if (!empty($all_advt)) foreach ($all_advt as $item) { ?>
                                <option value="<?php echo $item->id ?>"><?php echo $item->name ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-xs-12 col-md-4">
                         <p>
                            <label><strong>Добавить:</strong></label>
                        </p>
                         <div id="new_advt_div">
                            <p><input id="new_advt_name" placeholder="Название рекламы" class="form-control"></p>
                            <button type="button" class="btn btn-primary" id="add_new_advt">Добавить</button>
                        </div>
                    </div>
                    <div class="col-md-2"></div>
                </div>
                <br>
                <button class="btn btn-primary" id="save_advt" type="button">Сохранить </button>
            </div>
</div>

<script type="text/javascript" src="/components/com_gm_ceiling/views/project/common_table.js"></script>
<script type="text/javascript" src="/components/com_gm_ceiling/date_picker/mounts_calendar.js"></script>
<script type="text/javascript">
    init_mount_calendar('calendar_mount','mount','mw_mounts_calendar',['close_mw','mw_container']);
    var min_project_sum = <?php echo  $min_project_sum;?>,
        min_components_sum = <?php echo $min_components_sum;?>,
        self_data = JSON.parse('<?php echo $self_calc_data;?>'),
        project_id = "<?php echo $this->item->id; ?>",
        preloader = '<?=parent::getPreloaderNotJS();?>',
        client_id = "<?php echo $this->item->id_client;?>";
    jQuery('#mw_container').click(function(e) { // событие клика по веб-документу
        var div = jQuery("#mw_measures_calendar"); // тут указываем ID элемента
        var div1 = jQuery("#mw_mounts_calendar");
        var div2 = jQuery("#mw_advt");
        if (!div.is(e.target) // если клик был не по нашему блоку
            && div.has(e.target).length === 0
            && !div1.is(e.target)
            && div1.has(e.target).length === 0
            && !div2.is(e.target)
            && div2.has(e.target).length === 0) { // и не по его дочерним элементам
            jQuery("#close_mw").hide();
            jQuery("#mw_container").hide();
            jQuery(".modal_window").hide();
        }
    });

    jQuery('.btn_submit').click(function(){
        var project_status = jQuery(this).data('status');
        jQuery('#jform_project_status').val(project_status);
        jQuery('#form-project').submit();
    });

    jQuery(document).ready(function () {

        jQuery("#change_rek").click(function(){
            jQuery("#close_mw").show();
            jQuery("#mw_container").show();
            jQuery("#mw_advt").show('slow');
        });


        jQuery("#save_advt").click(function() {
            if (jQuery("#advt_choose").val() == '0' || jQuery("#advt_choose").val() == '') {
                noty({
                    timeout: 2000,
                    theme: 'relax',
                    layout: 'center',
                    maxVisible: 5,
                    type: "warning",
                    text: "Укажите рекламу"
                });
                jQuery("#advt_choose").focus();
                return;
            }
            jQuery.ajax({
                url: "index.php?option=com_gm_ceiling&task=project.save_advt",
                data: {
                    project_id: project_id,
                    api_phone_id: jQuery("#advt_choose").val(),
                    client_id: client_id
                },
                dataType: "json",
                async: true,
                success: function(data) {
                    document.getElementById('save_advt').style.display = 'none';
                    document.getElementById('advt_choose').disabled = 'disabled';
                    noty({
                        timeout: 2000,
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "success",
                        text: "Реклама сохранена"
                    });
                    location.reload();
                },
                error: function(data) {
                    console.log(data);
                    noty({
                        timeout: 2000,
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "error",
                        text: "Ошибка"
                    });
                }
            });
        });

        jQuery("#add_new_advt").click(function() {
            jQuery.ajax({
                url: "index.php?option=com_gm_ceiling&task=addNewAdvt",
                data: {
                    name: jQuery("#new_advt_name").val()
                },
                dataType: "json",
                async: true,
                success: function (data) {
                    select = document.getElementById('advt_choose');
                    var opt = document.createElement('option');
                    opt.selected = true;
                    opt.value = data.id;
                    opt.innerHTML = data.name;
                    select.appendChild(opt);
                    jQuery("#new_advt_name").val('');
                },
                error: function (data) {
                    console.log(data);
                    var n = noty({
                        timeout: 2000,
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "error",
                        text: "ошибка"
                    });
                }
            });
        });
    });

</script>