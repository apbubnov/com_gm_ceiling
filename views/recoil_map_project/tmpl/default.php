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
    <a class="btn btn-large btn-primary"
       href="/index.php?option=com_gm_ceiling&view=mainpage&type=gmmanagermainpage"
       id="back"><i class="fa fa-arrow-left" aria-hidden="true"></i> Назад</a>
    <button type = "button" id = "add" class = "btn btn-primary">Добавить</button>
    <div id="modal_window_container" class = "modal_window_container">
		<button type="button" id="close" class = "close_btn"><i class="fa fa-times fa-times-tar" aria-hidden="true"></i></button>
		<div id="modal_window_sum" class = "modal_window">
			<h6 style = "margin-top:10px">Введите сумму</h6>
			<p><input type="text" id="sum" placeholder="Сумма" required></p>
			<p><button type="button" id="save" class="btn btn-primary">Сохранить</button>  <button type="button" id="cancel" class="btn btn-primary">Отмена</button></p>
	    </div>
    </div>
    <table class="table table-striped one-touch-view" id="info">
        <thead>
        <tr>
            <th>
                № договора
            </th>
            <th>
                Дата
            </th>
            <th>
                Сумма
            </th>
        </tr>
        </thead>

        <tbody>
            <?php foreach($this->item as $item){?>
                <tr>
                    <td>
                        <?php echo $item->project_id;?>
                    </td>
                    <td>
                        <?php echo $item->date_time; ?>
                    </td>
                    <td>
                        <?php
                            $all_sum+=$item->sum;
                            echo $item->sum;
                        ?>
                    </td>
                </tr>
            <?php }?>
            <tr >
                <td colspan = "3" style="text-align:right">
                    <b>Итого:</b> <?php echo $all_sum;?>
                </td>
            </tr>
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
        var available_sum = 0;
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
            var entered_sum = jQuery("#sum").val()-0;
            var id = <?php echo $this->item[0]->recoil_id;?>;
            available_sum = getAvailableSum(id);
            console.log(available_sum);
            if(entered_sum<=available_sum){
                save_sum(0-entered_sum,id);
            }
            else{
                var n = noty({
                        timeout: 2000,
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "error",
                        text: "Ошибка введенная сумма больше начисленной суммы"
                    });
            }
        });
    });

    function getAvailableSum(id){
        var result;
        jQuery.ajax({
            type: 'POST',
            url: "index.php?option=com_gm_ceiling&task=getAvailableSum",
            async: false,
            data: {
                id:id
            },
            success: function (data) {
                console.log(data);
               result = data-0;
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
        return result;
    }

    function save_sum(sum,id){
        jQuery.ajax({
            type: 'POST',
            url: "index.php?option=com_gm_ceiling&task=saveGivenOutSum",
            data: {
                sum:sum,
                id:id
            },
            success: function (data) {
                data = JSON.parse(data);
                all_sum = 0;
                jQuery("#info tbody").empty();
                for(var i=0;i<data.length;i++) {
                    jQuery("#info").append('<tr></tr>');
                    for(var j=1;j<Object.keys(data[i]).length;j++){
                        if(Object.keys(data[i])[j]=='sum'){
                            all_sum+=data[i][Object.keys(data[i])[j]]-0;
                        }
                        jQuery('#info > tbody > tr:last').append('<td>'+data[i][Object.keys(data[i])[j]] +'</td>');
                    }
                }
                jQuery("#info").append('<tr><td colspan=3 style="text-align:right"><b>Итого: '+all_sum+'</b></td></tr>');
                jQuery("#close").hide();
                jQuery("#modal_window_container").hide();
                jQuery("#modal_window_sum").hide();
                var n = noty({
                    timeout: 2000,
                    theme: 'relax',
                    layout: 'center',
                    maxVisible: 5,
                    type: "success",
                    text: "Успешно!"
                });

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

    }
</script>
