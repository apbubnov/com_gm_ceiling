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
?>

<form>
    <a class="btn btn-large btn-primary"
       href="/index.php?option=com_gm_ceiling&view=mainpage&type=gmmanagermainpage"
       id="back"><i class="fa fa-arrow-left" aria-hidden="true"></i> Назад</a>
    <button type = "button" id = "add" class = "btn btn-primary">Инкассация</button>
    <div id="modal_window_container" class = "modal_window_container">
		<button type="button" id="close" class = "close_btn"><i class="fa fa-times fa-times-tar" aria-hidden="true"></i></button>
		<div id="modal_window_sum" class = "modal_window">
			<h6 style = "margin-top:10px">Введите сумму</h6>
			<p><input type="text" id="sum" placeholder="Сумма" required></p>
			<p><button type="button" id="save" class="btn btn-primary">Сохранить</button>  <button type="button" id="cancel" class="btn btn-primary">Отмена</button></p>
	    </div>
    </div>
    <table class="table table-striped one-touch-view" id="callbacksList">
        <thead>
        <tr>
            <th>
              Дата
            </th>
            <th>
               № договора
            </th>
            <th>
               Бригада
            </th>
            <th>
                Сумма
            </th>
            <th>
                З\п монт-м
            </th>
            <th>
                Расходные мат-лы
            </th>
            <th>
                Остаток
            </th>
            <th>
                Касса
            </th>
            <th>
                Инкассация
            </th>
        </tr>
        </thead>

        <tbody id="table_body">
            <?php foreach ($this->item as $item) {
                $encash = 0;?>
                <tr>
                    <td>
                        <?php echo $item->closed;?>
                    
                    </td>
                    <td>
                        <?php echo $item->id;?>        
                    </td>
                    <td>
                        <?php echo $item->name;?>
                    
                    </td>
                    <td>
                        <?php echo $item->new_project_sum;?>        
                    </td>
                    <td>
                        <?php echo $item->new_mount_sum;?>        
                    </td>
                    <td>
                        <?php echo $item->new_material_sum;?>        
                    </td>
                    <td>
                        <?php
                            $residue = $item->new_project_sum - $item->new_mount_sum -$item->new_material_sum;
                            echo $residue;
                        ?>        
                    </td>
                    <td>
                        <?php
                            $cashbox += $residue - $encash;
                            echo $cashbox;
                        ?>
                    </td>
                    <td>
                        <?php 
                            if(isset($item->sum)) {
                                $encash = $item->sum;
                                echo $item->sum;
                                
                            }
                             ?>
                    </td>

                </tr>
            <?php }?>
        </tbody>
    </table>
</form>

<script>
    jQuery(document).mouseup(function (e){ // событие клика по веб-документу
        var div = jQuery("#modal_window_sum"); // тут указываем ID элемента
        if (!div.is(e.target) // если клик был не по нашему блоку
            && div.has(e.target).length === 0) { // и не по его дочерним элементам
            jQuery("#close").hide();
            jQuery("#modal_window_container").hide();
            jQuery("#modal_window_sum").hide();
        }
    });
    jQuery(document).ready(function (){
        jQuery("#cancel").click(function(){
            jQuery("#close").hide();
            jQuery("#modal_window_container").hide();
            jQuery("#modal_window_sum").hide();
        });
        jQuery("#add").click(function (){
            jQuery("#modal_window_container").show();
            jQuery("#modal_window_sum").show("slow");
            jQuery("#close").show();
        });
        jQuery("#save").click(function(){
            id = <?php echo $userId;?>;
            jQuery.ajax({
            type: 'POST',
            url: "index.php?option=com_gm_ceiling&task=saveEncashment",
            async: false,
            data: {
                sum: jQuery("#sum").val(),
                id: id
            },
            success: function (data) {
                console.log(data);
                jQuery("#close").hide(); 
                jQuery("#modal_window_container").hide();
                jQuery("#modal_window_sum").hide();
            },
            dataType: "text",
            timeout: 10000,
            error: function () {
                var n = noty({
                    timeout: 2000,
                    theme: 'relax',
                    layout: 'center',
                    maxVisible: 5,
                    type: "error",
                    text: "Ошибка cервер не отвечает"
                });
            }
        });
        })
    });
</script>