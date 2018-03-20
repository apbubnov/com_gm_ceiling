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


?>

<form>
    <h2>Выберите производителя</h2>
    <table class="table table-striped one-touch-view" id="manufacturers">
        <tbody>
            <?php foreach($this->item as $item){?>
                <tr data-connected = <?php echo $item->connect ?>>
                    <td>
                        <?php echo $item->name;?>
                    </td>
                    <td>
                        <?php echo $item->text;?>
                    </td>
                    <td>
                        <!-- Здесь будет выводится счет по этому производителю -->
                    </td>
                </tr>
            <?php }?>
        </tbody>
    </table>
    <div id="mv_container" class="modal_window_container">
        <button type="button" id="close" class="close_btn"><i class="fa fa-times fa-times-tar" aria-hidden="true"></i></button>
        <div id="modal_window_not_connected" class="modal_window">
            <p><strong>Производитель еще не подключен к системе.<br>
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
            }
        });
    });
</script>