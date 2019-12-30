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

$projects_mounts_model = Gm_ceilingHelpersGm_ceiling::getModel('projects_mounts');
$client_model = Gm_ceilingHelpersGm_ceiling::getModel('client_phones');


$json_mount = $this->item->mount_data;
$stages = [];
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
//----------------------------------------------------------------------------------
$server_name = $_SERVER['SERVER_NAME'];
$project_notes = Gm_ceilingHelpersGm_ceiling::getProjectNotes($this->item->id);

$phones = $client_model->getItemsByClientId($this->item->id_client);

?>
<style>
    .row{
        margin-bottom: 15px;
    }
</style>
<?=parent::getButtonBack();?>
<?php if ($this->item) : ?>
    <div class="row center">
        <h4>Проект №<?= $this->item->id?></h4>
    </div>
    <div class="container">
        <div class="row">
            <div class="col-md-6 item_fields">
                <input type="hidden" name="project_id" value="<?php echo $this->item->id; ?>">
                <div class="container">
                    <div class="row">
                        <div class="col-md-6">
                            <b>
                                <?php echo JText::_('COM_GM_CEILING_FORM_LBL_PROJECT_CLIENT_ID'); ?>
                            </b>
                        </div>
                        <div class="col-md-6">
                            <a href="http://<?php echo $server_name;?>/index.php?option=com_gm_ceiling&view=clientcard&id=<?=$this->item->id_client;?>">
                                <?php echo $this->item->client_id; ?>
                            </a>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <b>
                                <?php echo JText::_('COM_GM_CEILING_CLIENTS_CLIENT_CONTACTS'); ?>
                            </b>
                        </div>
                        <div class="col-md-6">
                            <?php foreach ($phones as $contact):?>
                                <div class="row">
                                    <div class="col-md-12">
                                        <a href="tel:+<?=$contact->phone;?>"><?=$contact->phone;?></a>
                                    </div>
                                </div>
                            <?endforeach;?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <b>
                                <?php echo JText::_('COM_GM_CEILING_FORM_LBL_PROJECT_PROJECT_INFO'); ?>
                            </b>
                        </div>
                        <div class="col-md-6">
                            <a target="_blank" href="https://yandex.ru/maps/?mode=search&text=<?=$this->item->project_info;?>">
                                <?=$this->item->project_info;?>
                            </a>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <b>
                                <?php echo JText::_('COM_GM_CEILING_PROJECTS_PROJECT_CALCULATION_DATE'); ?>
                            </b>
                        </div>
                        <div class="col-md-6">
                            <?php if($this->item->project_calculation_date == "0000-00-00 00:00:00") { ?>
                                -
                            <?php } else { ?>
                                <?php $jdate = new JDate($this->item->project_calculation_date); ?>
                                <?php echo $jdate->format('d.m.Y H:i'); ?>
                            <?php } ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <button type="button" id="return_project" class="btn btn btn-primary">
                                <i class="fas fa-undo"></i> Вернуть в работу
                            </button>
                        </div>

                </div>

                <?php include_once('components/com_gm_ceiling/views/project/project_notes.php'); ?>

                </div>

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

    <?php include_once('components/com_gm_ceiling/views/project/common_table.php'); ?>

    <script type="text/javascript" src="/components/com_gm_ceiling/create_calculation.js"></script>
    <script type="text/javascript" src="/components/com_gm_ceiling/views/project/common_table.js"></script>
    <script type="text/javascript">
        var project_id = "<?php echo $this->item->id; ?>",
            client_id = "<?php echo $this->item->id_client;?>";

        jQuery(document).ready(function(){
            jQuery('#return_project').click(function(){
                var project_data = {project_calculation_date:getFormattedDatetime(),project_status:1}
                jQuery.ajax({
                    url: "index.php?option=com_gm_ceiling&task=project.updateProjectData",
                    data: {
                        project_id: project_id,
                        project_data: project_data
                    },
                    dataType: "json",
                    async: true,
                    success: function (data) {
                        location.href = '/index.php?option=com_gm_ceiling&task=mainpage';
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

        });
        function getFormattedDatetime () {
            var now = new Date(),
                year = "" + now.getFullYear(),
                month = "" + (now.getMonth() + 1),
                day = "" + now.getDate(),
                hour = "" + now.getHours(),
                minute = "" + now.getMinutes(),
                second = "" + now.getSeconds();
            if (month.length == 1) { month = "0" + month; }
            if (day.length == 1) { day = "0" + day; }
            if (hour.length == 1) { hour = "0" + hour; }
            if (minute.length == 1) { minute = "0" + minute; }
            if (second.length == 1) { second = "0" + second; }
            return year + "-" + month + "-" + day + " " + hour + ":" + minute + ":" + second;
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
