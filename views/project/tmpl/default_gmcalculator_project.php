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


$client_model = Gm_ceilingHelpersGm_ceiling::getModel('client_phones');
$phones = $client_model->getItemsByClientId($this->item->id_client);
$server_name = $_SERVER['SERVER_NAME'];
?>
<style>
    .row{
        margin-bottom: 15px;
    }
</style>
<?= parent::getButtonBack(); ?>
<h4 class="center" style="margin-top: 15px; margin-bottom: 15px;">Проект № <?php echo $this->item->id ?></h4>
<?php if ($this->item) : ?>
    <div class="container">
        <div class="row">
            <div class="col-xs-12 col-md-6 item_fields">
                <div class = "container">
                    <div class="row">
                        <div class="col-md-6">
                            <b>
                                <?php echo JText::_('COM_GM_CEILING_FORM_LBL_PROJECT_CLIENT_ID'); ?>
                            </b>
                        </div>
                        <div class="col-md-6">
                            <a href="/index.php?option=com_gm_ceiling&view=clientcard&id=<?=$this->item->id_client;?>"><?php echo $this->item->client_id; ?></a>
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
                                foreach ($phones as $contact):?>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <a href="tel:+<?=$contact->phone;?>"><?=$contact->phone;?></a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <b>
                                <?php echo JText::_('COM_GM_CEILING_FORM_LBL_PROJECT_PROJECT_INFO'); ?>
                            </b>
                        </div>
                        <div class="col-md-6">
                            <a target="_blank" href="https://yandex.ru/maps/?mode=search&text=<?=$this->item->project_info;?>"><?=$this->item->project_info;?></a>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <b>
                                <?php echo JText::_('COM_GM_CEILING_PROJECTS_PROJECT_CALCULATION_DATE'); ?>
                            </b>
                        </div>
                        <div class="row">
                            <?php if ($this->item->project_calculation_date == "0000-00-00 00:00:00") { ?>
                                -
                            <?php } else { ?>
                                <?php $jdate = new JDate(JFactory::getDate($this->item->project_calculation_date)); ?>
                                <?php echo $jdate->format('d.m.Y H:i'); ?>
                            <?php } ?>
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


    </div>

    <?php include_once('components/com_gm_ceiling/views/project/common_table.php'); ?>


    <script type="text/javascript" src="/components/com_gm_ceiling/create_calculation.js"></script>
    <script type="text/javascript" src="/components/com_gm_ceiling/views/project/common_table.js?t=<?php echo time(); ?>"></script>
    <script type="text/javascript">
        var project_id = "<?php echo $this->item->id; ?>",
            client_id = '<?php echo $this->item->id_client;?>';
        jQuery(document).ready(function(){
            show_comments();

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
                            text: "Комментарий добавлен."
                        });
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

        });
    </script>

<?php
    else:
        echo JText::_('COM_GM_CEILING_ITEM_NOT_LOADED');
    endif;
?>
