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
$user = JFactory::getUser();
$user_group = $user->groups;
$userId = $user->get('id');
$userName = $user->get('username');
$canEdit = JFactory::getUser()->authorise('core.edit', 'com_gm_ceiling');
if (!$canEdit && JFactory::getUser()->authorise('core.edit.own', 'com_gm_ceiling')) {
    $canEdit = JFactory::getUser()->id == $this->item->created_by;
}

/*_____________блок для всех моделей/models block________________*/ 
$calculationsModel = Gm_ceilingHelpersGm_ceiling::getModel('calculations');
$clients_dop_contacts_model = Gm_ceilingHelpersGm_ceiling::getModel('clients_dop_contacts');
$model_api_phones = Gm_ceilingHelpersGm_ceiling::getModel('api_phones');
$repeat_model = Gm_ceilingHelpersGm_ceiling::getModel('repeatrequest');
$projects_mounts_model = Gm_ceilingHelpersGm_ceiling::getModel('projects_mounts');
/*________________________________________________________________*/

if (!empty($this->item->calcs_mounting_sum)) {
    $service_mount = get_object_vars(json_decode($this->item->calcs_mounting_sum));
}


$status = $this->item->project_status;
$status_attr = "data-status = \"$status\"";
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

$all_advt = $model_api_phones->getAdvt();
if ($this->item->api_phone_id == 10) {
    $repeat_advt = $repeat_model->getDataByProjectId($this->item->id);
    if (!empty($repeat_advt->advt_id)) {
        $reklama = $model_api_phones->getDataById($repeat_advt->advt_id);
    }
    else {
        $reklama = $model_api_phones->getDataById(10);
    }
} else {
    if(!empty($this->item->api_phone_id)){
         $reklama = $model_api_phones->getDataById($this->item->api_phone_id);
    }
   
}

$advt_str = $reklama->number.' '.$reklama->name.' '.$reklama->description; 

 if (!empty($calculation_total)) {
    $skidka = ($calculation_total - $project_total_1) / $calculation_total * 100;
} else {
    $skidka = 0;
}
 $contact_email = $clients_dop_contacts_model->getContact($this->item->id_client);

?>
<style>
    .act_btn{
        width:210px;
        margin-bottom: 10px;
    }
    .save_bnt{
        width:250px;
        height: 60px;
    }
    .btn_edit{
        position: absolute;
        right:0;
    }
    .manuf_div{
        height:80px;
        border: 2px solid grey;
        border-radius: 5px;
        margin-bottom: 5px;
    }
    .manuf_div.selected{
        border: 2px solid #414099;
        background-color: #d3d3f9;
    }
    .edit_div{
        position: absolute;
        right:0px;
    }
    .row{
        margin-bottom: 5px !important;
        margin-left: 2px !important;
        padding-right: 5px !important;
    }

    .border_container{
        border: 1px solid #414099;
        border-radius: 5px;
        margin-bottom: 15px;
        padding-left: 0;
        padding-right: 0;
    }
    .container{
        padding-left: 0;
        padding-right: 0;
    }

</style>
<link rel="stylesheet" href="/components/com_gm_ceiling/views/project/css/style.css" type="text/css" />

<?= parent::getButtonBack(); ?>
<form id="form-client" action="/index.php?option=com_gm_ceiling&task=project.activate&type=calculator&subtype=calendar" method="post" enctype="multipart/form-data">
<div class="project_activation" style="display: none;">
    <input name="project_id" id="project_id" value="<?php echo $this->item->id; ?>" type="hidden">
    <input name="client_id" id="client_id" value="<?php echo $this->item->id_client; ?>" type="hidden">
    <input name="type" value="calculator" type="hidden">
    <input name="subtype" value="calendar" type="hidden">
    <input id="project_verdict" name="project_verdict" value="0" type="hidden">
    <input id="project_status" name="project_status" value="<?php echo $this->item->project_status;?>" type="hidden">
    <input id="mounting_date" name="mounting_date" type='hidden'>
    <input id="mount" name="mount" type='hidden' value='<?php echo $json_mount ?>'>
    <input id="jform_project_mounting_date" name="jform_project_mounting_date" value="<?php echo $this->item->project_mounting_date; ?>" type='hidden'>
    <input id="project_mounter" name="project_mounter" value="<?php echo $this->item->project_mounter; ?>" type='hidden'>
    <input id="project_sum" name="project_sum" value="" type="hidden">
    <input id="project_sum_transport" name="project_sum_transport" value="<?php echo $project_total_discount_transport; ?>" type="hidden">
    <input name="comments_id" id="comments_id" value="<?php if (isset($_SESSION['comments'])) echo $_SESSION['comments']; ?>" type="hidden">
    <input name = "project_new_calc_date" id = "jform_project_new_calc_date"  value="" type='hidden'>
    <input id="jform_project_gauger" name="project_gauger" value="" type='hidden'>
</div>
    <h2 class="center" style="margin-top: 15px; margin-bottom: 15px;">Проект № <?php echo $this->item->id ?></h2>
    <div class="container">
        <div class="row">
            <div class="col-xs-12 col-md-6 item_fields">
                <?php include_once('components/com_gm_ceiling/views/project/info_block.php'); ?>
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
    </div>
    <!-- расчеты для проекта -->
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

        var $ = jQuery;
        var min_project_sum = '<?php echo  $min_project_sum;?>',
            min_components_sum = '<?php echo $min_components_sum;?>',
            self_data = JSON.parse('<?php echo $self_calc_data;?>'),
            project_id = "<?php echo $this->item->id; ?>",
            deleted_phones = [],
            deleted_emails = [],
            manufacturersData = [],
            project_status = '<?=$this->item->project_status;?>';



            

        jQuery(document).ready(function () {
            var client_id = "<?php echo $this->item->id_client;?>";
            var client_name = "<?php echo $this->item->client_id;?>";
            jQuery("[name = 'new_client_contacts[]']").mask('+7(999) 999-9999');
            fillProjectSum();


            if (document.getElementById('comments'))
            {
                show_comments();
            }

            // для истории и добавления комментария
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

            jQuery("#show_comments_btn").click(function(){
                jQuery("#comments_divs").toggle();

            });



            function add_history(id_client, comment) {
                jQuery.ajax({
                    url: "index.php?option=com_gm_ceiling&task=addComment",
                    data: {
                        comment: comment,
                        id_client: id_client
                    },
                    dataType: "json",
                    async: true,
                    success: function (data) {
                        var n = noty({
                            timeout: 2000,
                            theme: 'relax',
                            layout: 'topCenter',
                            maxVisible: 5,
                            type: "success",
                            text: "Добавленна запись в историю клиента"
                        });
                        if (jQuery("#client_id").val() == 1) {
                            
                            jQuery("#comments_id").val(jQuery("#comments_id").val() + data + ";");
                        }
                        show_comments();
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
                        //new_comments_id.push(data);
                        //document.getElementById("comments_id").value +=data+";";
                        jQuery("#comments_id").val(jQuery("#comments_id").val() + data + ";");
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
        }); //конец ready

    </script>