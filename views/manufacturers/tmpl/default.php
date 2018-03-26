<?php
/**
 * @version    CVS: 0.1.7
 * @package    Com_Gm_ceiling
 * @author     bubnov <2017 al.p.bubnov@gmail.com>
 * @copyright  2017 al.p.bubnov@gmail.com
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;

$user       = JFactory::getUser();
$userId     = $user->get('id');
$user_group = $user->groups;

$show_request_count = false;
if($user->dealer_id == 1){
    $show_request_count = true;
}

?>

<form>
    <h2>Выберите производителя</h2>
    <table class="table table-striped one-touch-view" id="manufacturers">
        <tbody>
            <?php foreach($this->item as $item){?>
                <tr data-connected = <?php echo (isset($item->connect) ? $item->connect : 0) ?> data-id = <?php echo $item->id;?> >
                    <td>
                        <?php echo $item->name;?>
                    </td>
                    <td>
                        <?php echo $item->text;?>
                    </td>
                    <td>
                        <!-- Здесь будет выводится счет по этому производителю -->
                    </td>
                    <?php if($show_request_count){?>
                    <td>
                        <?php echo $item->request_count;?>
                    </td>
                    <?php }?>
                </tr>
            <?php }?>
        </tbody>
    </table>
    <div id="mv_container" class="modal_window_container">
        <button type="button" id="close" class="close_btn"><i class="fa fa-times fa-times-tar" aria-hidden="true"></i></button>
        <div id="modal_window_not_connected" class="modal_window">
            <p><strong>Производитель еще не подключен к системе.
            Отправьте ему запрос на подключение</strong></p>
            <p><button type="button" id="send" class="btn btn-primary">Отправить</button></p>
        </div>
    </div>
</form>

<script>
    jQuery(document).mouseup(function (e){ // событие клика по веб-документу
        var div = jQuery("#modal_window_fio"); // тут указываем ID элемента
        if (!div.is(e.target) && div.has(e.target).length === 0) {
            jQuery("#close").hide();
            jQuery("#mv_container").hide();
            jQuery("#modal_window_not_connected").hide();            
        }
    });
    jQuery(document).ready(function(){
        jQuery("#manufacturers > tbody > tr").click(function(){
            if(jQuery(this).data('connected')==0 || jQuery(this).data('connected') == ''){
                jQuery("#close").show();
                jQuery("#mv_container").show();
                jQuery("#modal_window_not_connected").show();
                jQuery("#send").attr("data-manufacturer",jQuery(this).data('id'));
            }
        });
    });
    jQuery("#send").click(function(){
        jQuery.ajax({
            type: 'POST',
            url: "index.php?option=com_gm_ceiling&task=big_smeta.add_request",
            data:{
                id: jQuery(this).data('manufacturer'),
                dealer_id: <?php echo $userId;?>
            },
            async: true,
            success: function(data){
                send_email();
            },
            dataType: "json",
            timeout: 30000,
            error: function(data){
                var n = noty({
                    timeout: 2000,
                    theme: 'relax',
                    layout: 'center',
                    maxVisible: 5,
                    type: "error",
                    text: "Ошибка, пожалуйста попробуйте позже"
                });
            }                   
        });
    });
    function send_email(){
        var email ="<?php echo $user->email;?>";
        var subj = "Заявка от дилера";
        var text = "<?php echo $user->name?> хочет работать с Вами.";
        jQuery.ajax({
            url: "index.php?option=com_gm_ceiling&task=dealer.sendEmail",
            data: {
                email: email,
                subj: subj,
                text: text,
                ajax : 1
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
                    text: "Заявка отправлена!"
                });

            },
            error: function (data) {
                var n = noty({
                    timeout: 2000,
                    theme: 'relax',
                    layout: 'center',
                    maxVisible: 5,
                    type: "error",
                    text: "Ошибка сервера"
                });
            }
        });
    }
</script>