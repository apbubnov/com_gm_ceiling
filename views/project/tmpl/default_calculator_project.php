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
$canEdit = JFactory::getUser()->authorise('core.edit', 'com_gm_ceiling');
if (!$canEdit && JFactory::getUser()->authorise('core.edit.own', 'com_gm_ceiling')) {
    $canEdit = JFactory::getUser()->id == $this->item->created_by;
}

$address = Gm_ceilingHelpersGm_ceiling::parseProjectInfo($this->item->project_info);
$json_mount = $this->item->mount_data;
$wasDelete = false;
$this->item->mount_data = json_decode(htmlspecialchars_decode($this->item->mount_data));
/***
 * КОООСТТТЫЫЛЬ
 */
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
<link rel="stylesheet" href="/components/com_gm_ceiling/views/project/css/style.css" type="text/css"/>
<style>
    .row {
        margin-bottom: 15px;
    }

    .action_btn {
        width: 300px;
    }
</style>

<?= parent::getButtonBack(); ?>
<form id="form-client"
      action="/index.php?option=com_gm_ceiling&task=project.activate&type=calculator&subtype=calendar" method="post"
      enctype="multipart/form-data">
    <div class="project_activation" style="display: none;">
        <input name="project_id" id="project_id" value="<?php echo $this->item->id; ?>" type="hidden">
        <input name="client_id" id="client_id" value="<?php echo $this->item->id_client; ?>" type="hidden">
        <input name="type" value="calculator" type="hidden">
        <input name="subtype" value="calendar" type="hidden">
        <input id="project_verdict" name="project_verdict" value="0" type="hidden">
        <input id="project_status" name="project_status" value="<?php echo $this->item->project_status; ?>"
               type="hidden">
        <input id="mounting_date" name="mounting_date" type='hidden'>
        <input id="mount" name="mount" type='hidden' value='<?php echo $json_mount ?>'>
        <input id="jform_project_mounting_date" name="jform_project_mounting_date"
               value="<?php echo $this->item->project_mounting_date; ?>" type='hidden'>
        <input id="project_mounter" name="project_mounter" value="<?php echo $this->item->project_mounter; ?>"
               type='hidden'>
        <input id="project_sum" name="project_sum" value="" type="hidden">
        <input id="project_sum_transport" name="project_sum_transport"
               value="<?php echo $project_total_discount_transport; ?>" type="hidden">
        <input name="comments_id" id="comments_id"
               value="<?php if (isset($_SESSION['comments'])) echo $_SESSION['comments']; ?>" type="hidden">
        <input name="project_new_calc_date" id="jform_project_new_calc_date" value="" type='hidden'>
        <input id="jform_project_gauger" name="project_gauger" value="<?=$this->item->project_calculator;?>" type='hidden'>

    </div>
    <h2 class="center">Проект №<?= $this->item->id ?></h2>
    <div class="container">
        <div class="row">
            <div class="col-xs-12 col-md-6 item_fields">
                <?php
                include_once('components/com_gm_ceiling/views/project/info_block.php');
                include_once('components/com_gm_ceiling/views/project/project_notes.php');
                ?>
            </div>
            <div class="col-xs-12 col-md-6 comment">
                <label> История клиента: </label>
                <textarea id="comments" class="input-comment" rows=11 readonly> </textarea>
                <table>
                    <tr>
                        <td><label> Добавить комментарий: </label></td>
                    </tr>
                    <tr>
                        <td width=100%><textarea class="inputactive" id="new_comment"></textarea></td>
                        <td>
                            <button class="btn btn-primary" type="button" id="add_comment"><i
                                        class="fa fa-paper-plane" aria-hidden="true"></i>
                            </button>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    <?php include_once('components/com_gm_ceiling/views/project/common_table.php'); ?>

    <?php
    if(!in_array($this->item->project_status,VERDICT_STATUSES)){
        include_once('components/com_gm_ceiling/views/project/project_actions.php');
    }
    ?>
</form>
<script type="text/javascript" src="/components/com_gm_ceiling/create_calculation.js"></script>
<script type="text/javascript" src="/components/com_gm_ceiling/views/project/common_table.js?t=<?php echo time(); ?>"></script>


<script type="text/javascript">
    var min_project_sum = '<?php echo $min_project_sum;?>',
        min_components_sum = '<?php echo $min_components_sum;?>',
        self_data = JSON.parse('<?php echo $self_calc_data;?>'),
        project_status = '<?=$this->item->project_status;?>',
        project_id = "<?php echo $this->item->id; ?>",
        deleted_phones = [],
        deleted_emails = [],
        manufacturersData = [];

    jQuery(document).ready(function () {

        if (document.getElementById('comments')) {
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

    function getFormattedDatetime() {
        var now = new Date(),
            year = "" + now.getFullYear(),
            month = "" + (now.getMonth() + 1),
            day = "" + now.getDate(),
            hour = "" + now.getHours(),
            minute = "" + now.getMinutes(),
            second = "" + now.getSeconds();
        if (month.length == 1) {
            month = "0" + month;
        }
        if (day.length == 1) {
            day = "0" + day;
        }
        if (hour.length == 1) {
            hour = "0" + hour;
        }
        if (minute.length == 1) {
            minute = "0" + minute;
        }
        if (second.length == 1) {
            second = "0" + second;
        }
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
