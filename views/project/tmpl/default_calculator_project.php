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


    $user = JFactory::getUser();
    $dealer = JFactory::getUser($user->dealer_id);
    $project_id = $this->item->id;


    $model_api_phones = Gm_ceilingHelpersGm_ceiling::getModel('api_phones');

    if(!empty($this->item->api_phone_id)){
        $reklama = $model_api_phones->getDataById($this->item->api_phone_id)->name;
    }
    else{
        $reklama = "";
    }
    $all_advt = $model_api_phones->getAdvt();
    $this->item->mount_data = json_decode(htmlspecialchars_decode($this->item->mount_data));
    foreach ($this->item->mount_data as $key=>$value) {
        if(empty($value->mounter)){
            $wasDelete = true;
            unset($this->item->mount_data[$key]);
        }
    }
    if($wasDelete){
        if(!empty($this->item->mount_data)) {
            $json_mount = json_encode(htmlspecialchars($this->item->mount_data));
        }
        else{
            $json_mount = [];
        }
    }

    /**КОНЕЦ КОСТЫЛЯ
     ***/
    $stages = [];
    if(!empty($this->item->mount_data)){
        $mount_types = $projects_mounts_model->get_mount_types();
        foreach ($this->item->mount_data as $value) {
            $value->stage_name = $mount_types[$value->stage];
            if(!array_key_exists($value->mounter,$stages)){
                $stages[$value->mounter] = array((object)array("stage"=>$value->stage,"time"=>$value->time));
            }
            else{
                array_push($stages[$value->mounter],(object)array("stage"=>$value->stage,"time"=>$value->time));
            }
        }
        foreach ($calculations as $calc) {
            foreach ($stages as $key => $value) {
                foreach ($value as $val) {
                    Gm_ceilingHelpersGm_ceiling::create_mount_estimate_by_stage($calc->id,$key,$val->stage,$val->time);
                }

            }
        }

}
?>
<style>
   .row{
       margin-bottom: 15px;
   }
</style>
<?= parent::getButtonBack(); ?>
<input name="url" value="" type="hidden">
<h2 class="center" style="margin-top: 15px; margin-bottom: 15px;">Проект № <?php echo $this->item->id ?></h2>
<div class="row">
    <div class="col-xs-12 col-md-6 no_padding">
            <h4>Информация о клиенте</h4>
            <div class="container"  style="margin-bottom: 25px;">
                <div class="row">
                    <div class="col-md-6">
                        <b>
                            <?php echo JText::_('COM_GM_CEILING_FORM_LBL_PROJECT_CLIENT_ID'); ?>
                        </b>
                    </div>
                    <div class="col-md-6">
                        <?php echo $this->item->client_id; ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <b>
                            <?php echo JText::_('COM_GM_CEILING_CLIENTS_CLIENT_CONTACTS'); ?>
                        </b>
                    </div>
                    <div class="col-md-6">
                        <?php
                            foreach ($phones AS $contact) {
                                echo $contact->phone;
                                echo "<br>";
                            } 
                        ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <b>
                            <?php echo JText::_('COM_GM_CEILING_FORM_LBL_PROJECT_PROJECT_INFO'); ?>
                        </b>
                    </div>
                    <div class="col-md-6">
                        <?php echo $this->item->project_info; ?>
                    </div>
                </div>
                <?php if(!empty($this->item->mount_data)):?>
                    <div class="row center" style="margin-bottom: 5px">
                        <div class="col-md-12">
                            <b>Монтаж</b>
                        </div>
                    </div>
                    <?php foreach ($this->item->mount_data as $value) { ?>
                        <div class="row" style="margin-bottom: 5px">
                            <div class="col-md-4">
                                <b>
                                    <?php echo $value->time;?>
                                </b>
                            </div>
                            <div class="col-md-4">
                                <?php echo $value->stage_name;?>
                            </div>
                            <div class="col-md-4">
                                <?php echo JFactory::getUser($value->mounter)->name;?>
                            </div>
                        </div>
                    <?php }?>
                <?php endif;?>
                <div class="row">
                    <div class="col-md-4">
                        <b>
                            Реклама
                        </b>
                    </div>
                    <div class="col-md-6">
                        <?php echo $reklama;?>
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-primary" type="button" id="change_rek"> <i class="fas fa-edit"></i> </button>
                    </div>
                </div>
                <?php if(!empty($this->item->project_calculator)):?>
                    <div class="row">
                        <div class="col-md-6">
                            <b>Замерщик</b>
                        </div>
                        <div class="col-md-6">
                            <?php echo JFactory::getUser($this->item->project_calculator)->name;?>
                        </div>
                    </div>
                <?php endif;?>
                <div class="row" style="margin-bottom: 5px">
                    <div class="col-md-4">
                        <b>
                            <?php echo JText::_('COM_GM_CEILING_PROJECTS_PROJECT_CALCULATION_DATE'); ?>
                        </b>
                    </div>
                    <div class="col-md-8">
                        <?php if ($this->item->project_calculation_date == "0000-00-00 00:00:00") { ?>
                            -
                        <?php } else { ?>
                            <?php $jdate = new JDate(JFactory::getDate($this->item->project_calculation_date)); ?>
                            <?php echo $jdate->format('d.m.Y H:i'); ?>
                        <?php } ?>
                    </div>

                </div>
            </div>
        <?php include_once('components/com_gm_ceiling/views/project/project_notes.php'); ?>
    </div>
    <div class="col-xs-12 col-md-6 comment">
        <label> История клиента: </label>
        <textarea id="comments" class="input-comment" rows=11 readonly> </textarea>
        <table>
            <tr>
                <td><label> Добавить комментарий: </label></td>
            </tr>
            <tr>
                <td width = 100%><textarea  class = "inputactive" id="new_comment"></textarea></td>
                <td><button class="btn btn-primary" type="button" id="add_comment"><i class="fa fa-paper-plane" aria-hidden="true"></i>
                    </button></td>
            </tr>
        </table>
    </div>
</div>
<div class="modal_window_container" id="mw_container">
    <button type="button" class="close_btn" id="close_mw"><i class="fa fa-times fa-times-tar" aria-hidden="true"></i></button>
    <div id="mw_advt" class="modal_window">
        <h4>Изменение/добавление рекламы</h4>
        <label>Выберите или добавьте новую рекламу</label>
        <div class="row">  
            <div class="col-xs-6 col-md-6">
                <p>
                    <label><strong>Выбрать:</strong></label>
                </p>
                <select id="advt_choose">
                    <option value="0">Выберите рекламу</option>
                    <?php if (!empty($all_advt)) foreach ($all_advt as $item) { ?>
                        <option value="<?php echo $item->id ?>"><?php echo $item->name ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="col-xs-6 col-md-6">
                 <p>
                    <label><strong>Добавить:</strong></label>
                </p>
                 <div id="new_advt_div">
                    <p><input id="new_advt_name" placeholder="Название рекламы"></p>
                    <button type="button" class="btn btn-primary" id="add_new_advt">Добавить</button>
                </div>
            </div>
        </div>
        <br>
        <button class="btn btn-primary" id="save_advt" type="button">Сохранить </button>
    </div>
</div>
<?php if ($this->item) : ?>
    <?php include_once('components/com_gm_ceiling/views/project/common_table.php'); ?>
    <? if ($this->item->project_status >= 5 && $this->item->project_status != 12): ?>
        <button class="btn btn-primary btn-done" data-project_id="<?= $this->item->id; ?>" type="button">Выполнено</button>
    <? endif; ?>

    <script type="text/javascript" src="/components/com_gm_ceiling/create_calculation.js"></script>
    <script type="text/javascript" src="/components/com_gm_ceiling/views/project/common_table.js"></script>
    <script type="text/javascript">
        var $ = jQuery;
        var project_id = "<?php echo $this->item->id; ?>";
        var client_id = "<?php echo $this->item->id_client;?>";
        jQuery('#mw_container').click(function(e) { // событие клика по веб-документу
            var div = jQuery("#mw_advt");
            if (!div.is(e.target) // если клик был не по нашему блоку
                && div.has(e.target).length === 0) { // и не по его дочерним элементам
                jQuery("#close_mw").hide();
                jQuery("#mw_container").hide();
                jQuery(".modal_window").hide();
            }
        });

        jQuery(document).ready(function () {
            $(".head_comsumables").click(function () {
                e = $(this);
                if (e.val() === "") e.val(true);
                if (e.val() === false) {
                    e.find("i").removeClass("fa-sort-desc").addClass("fa-sort-asc");
                    $(".section_comsumables").show();
                } else {
                    e.find("i").removeClass("fa-sort-asc").addClass("fa-sort-desc");
                    $(".section_comsumables").hide();
                }
                e.val(!e.val());
            });

            if (document.getElementById('comments'))
            {
                show_comments();
            }

            jQuery("#add_comment").click(function () {
                var comment = jQuery("#new_comment").val();
                var reg_comment = /[\\\<\>\/\'\"\#]/;
                if (reg_comment.test(comment) || comment === "") {
                    alert('Неверный формат примечания!');
                    return;
                }
                jQuery.ajax({
                    url: "index.php?option=com_gm_ceiling&task=addComment",
                    data: {
                        comment: comment,
                        id_client: client_id
                    },
                    dataType: "json",
                    async: true,
                    success: function (data) {
                        var n = noty({
                            timeout: 2000,
                            theme: 'relax',
                            layout: 'center',
                            maxVisible: 5,
                            type: "success",
                            text: "Комментарий добавлен"
                        });
                        show_comments();

                        jQuery("#new_comment").val("");
                    },
                    error: function (data) {
                        var n = noty({
                            timeout: 2000,
                            theme: 'relax',
                            layout: 'center',
                            maxVisible: 5,
                            type: "error",
                            text: "Ошибка отправки"
                        });
                    }
                });
            });

            var id = "<?php echo $sb_project_id; ?>";
            orderId = id != 0 ? id : "";
            jQuery.ajax({
                type: 'POST',
                url: "index.php?option=com_gm_ceiling&task=get_paymanet_status&",
                data: {
                    orderId: orderId
                },
                dataType: "json",
                success: function (data) {
                    if (data.OrderStatus == 2 && data.ErrorMessage == "Успешно") {
                        change_project_status(<?php echo $project_id;?>, 14);
                    }
                },
                timeout: 10000,
                error: function (data) {
                    console.log("error", data);
                }
            });

            jQuery(".btn-done").click(function () {
                var button = jQuery(this);
                noty({
                    layout: 'center',
                    type: 'warning',
                    modal: true,
                    text: 'Вы уверены, что хотите отметить договор выполненным?',
                    killer: true,
                    buttons: [
                        {
                            addClass: 'btn btn-success', text: 'Выполнен', onClick: function ($noty) {
                                jQuery.get(
                                    "/index.php?option=com_gm_ceiling&task=project.done",
                                    {
                                        project_id: button.data("project_id"),
                                        check: 1
                                    },
                                    function(data) {
                                        location.reload();
                                    }
                                );
                                $noty.close();
                            }
                        },
                        {
                            addClass: 'btn', text: 'Отмена', onClick: function ($noty) {
                                $noty.close();
                            }
                        }
                    ]
                });

            });
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

        function change_project_status(project_id, project_status) {
            jQuery.ajax({
                type: 'POST',
                url: "index.php?option=com_gm_ceiling&task=change_status&",
                data: {
                    id: project_id,
                    project_status: project_status
                },
                dataType: "json",
                success: function (data) {
                },
                timeout: 10000,
                error: function (data) {
                    var n = noty({
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "error",
                        text: "Ошибка!"
                    });
                }
            });
        }

        function show_comments() {
            var id_client = <?php echo $this->item->id_client;?>;
            jQuery.ajax({
                url: "index.php?option=com_gm_ceiling&task=selectComments",
                data: {
                    id_client: id_client
                },
                dataType: "json",
                async: true,
                success: function (data) {
                    var comments_area = document.getElementById('comments');
                    comments_area.innerHTML = "";
                    var date_t;
                    for (var i = 0; i < data.length; i++) {
                        date_t = new Date(data[i].date_time);
                        comments_area.innerHTML += formatDate(date_t) + "\n" + data[i].text + "\n----------\n";
                    }
                    comments_area.scrollTop = comments_area.scrollHeight;
                },
                error: function (data) {
                    var n = noty({
                        timeout: 2000,
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "error",
                        text: "Ошибка вывода примечаний"
                    });
                }
            });
        }

        function formatDate(date) {

            var dd = date.getDate();
            if (dd < 10) dd = '0' + dd;

            var mm = date.getMonth() + 1;
            if (mm < 10) mm = '0' + mm;

            var yy = date.getFullYear();
            if (yy < 10) yy = '0' + yy;

            var hh = date.getHours();
            if (hh < 10) hh = '0' + hh;

            var ii = date.getMinutes();
            if (ii < 10) ii = '0' + ii;

            var ss = date.getSeconds();
            if (ss < 10) ss = '0' + ss;

            return dd + '.' + mm + '.' + yy + ' ' + hh + ':' + ii + ':' + ss;
        }
    </script>

<?php
    else:
        echo JText::_('COM_GM_CEILING_ITEM_NOT_LOADED');
    endif;
?>