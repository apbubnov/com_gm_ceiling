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

$users_model = Gm_ceilingHelpersGm_ceiling::getModel('users');
$result_users = $users_model->getDealers();
$sum = 0;
$recoil_map_model = Gm_ceilingHelpersGm_ceiling::getModel('recoil_map_project');

?>
    <a class="btn btn-large btn-primary"
       href="/index.php?option=com_gm_ceiling&view=mainpage&type=gmmanagermainpage"
       id="back"><i class="fa fa-arrow-left" aria-hidden="true"></i> Назад</a>
    <h2 class="center">Дилеры</h2>
    <div style="width: 100%; text-align: left;">
        <button type="button" id="new_dealer" class="btn btn-primary">Создать дилера</button>
    </div>
    <br>
    <table class="table table-striped one-touch-view" id="callbacksList">
        <thead>
        <tr>
            <th>
               Имя
            </th>
            <th>
               Телефоны
            </th>
            <th>
               Дата регистрации
            </th>
            <th>
                Взнос
            </th>
        </tr>
        </thead>
        <tbody>
        	<?php
        		foreach ($result_users as $key => $value)
        		{
                    $data = $recoil_map_model->getData($value->id);
                    foreach ($data as $item)
                    {
                        $sum +=  $item->sum;
                    }
        	?>
                <tr class="row<?php echo $i % 2; ?>" >
		            <td data-href="<?php echo JRoute::_('index.php?option=com_gm_ceiling&view=clientcard&type=dealer&id='.(int) $value->associated_client); ?>">
		               <?php echo $value->name; ?>
		            </td>
                    <td data-href="<?php echo JRoute::_('index.php?option=com_gm_ceiling&view=clientcard&type=dealer&id='.(int) $value->associated_client); ?>">
                       <?php echo $value->client_contacts; ?>
                    </td>
		            <td data-href="<?php echo JRoute::_('index.php?option=com_gm_ceiling&view=clientcard&type=dealer&id='.(int) $value->associated_client); ?>">
		               <?php
                            if($value->created == "0000-00-00 00:00:00") {
                                echo "-";
                            } else {
                                $jdate = new JDate($value->created);
                                $created = $jdate->format("d.m.Y H:i");
                                echo $created;
                            }
                       ?>
		            </td>
                    <td data-href="">
                        <button class="btn btn-primary btn-done" user_id="<?= $value->id; ?>" type="button" > Внести сумму </button>
                        <div id="modal_window_container<?= $value->id; ?>" class="modal_window_container">
                            <button type="button" id="close" class="close_btn"><i class="fa fa-times fa-times-tar" aria-hidden="true"></i>
                            </button>
                            <div id="modal_window_acct<?= $value->id; ?>" class="modal_window">
                                <p><strong>Взнос задолжности. Дилер: <?php echo $value->name; ?> </strong></p>
                                <p>На счете : <?=$sum;?> руб.</p>
                                <p>Сумма взноса:</p>
                                <p><input type="text" id="pay_sum<?= $value->id; ?>" value=" <?=($sum<0)?abs($sum):0;?>"> </p>
                                <p><button type="submit" id="save_pay" class="btn btn-primary save_pay" user_id="<?= $value->id; ?>">ОК</button></p>
                            </div>
                        </div>
                    </td>
		        </tr>

        	<?php
        		}
        	?>
        </tbody>
    </table>
    <div id="modal-window-container">
        <button type="button" id="close4-tar"><i class="fa fa-times fa-times-tar" aria-hidden="true"></i></button>
        <div id="modal-window-1-tar">
                <p><strong>Создание нового дилера</strong></p>
                <p>ФИО:</p>
                <p><input type="text" id="fio_dealer"></p>
                <p>Номер телефона:</p>
                <p><input type="text" id="dealer_contacts"></p>
                <p><button type="submit" id="save_dealer" class="btn btn-primary">ОК</button></p>
        </div>
    </div>

<!--    <div id="modal-window-container">-->
<!--        <button type="button" id="close4-tar"><i class="fa fa-times fa-times-tar" aria-hidden="true"></i></button>-->
<!--        <div id="modal-window-1-tar" >-->
<!--            <p><strong>Взнос задолжности</strong></p>-->
<!--            <p>Сумма взноса:</p>-->
<!--            <p><input type="text" id="pay_sum"></p>-->
<!--            <p><button type="submit" id="save_pay" class="btn btn-primary">ОК</button></p>-->
<!--        </div>-->
<!--    </div>-->


<script>
    jQuery(document).ready(function()
    {
        jQuery('#dealer_contacts').mask('+7(999) 999-9999');
        jQuery('body').on('click', 'td', function(e)
        {
            if(jQuery(this).data('href')!=""){
                document.location.href = jQuery(this).data('href');
            }
        });

        jQuery(document).mouseup(function (e){ // событие клика по веб-документу
            var div3 = jQuery("#modal-window-1-tar"); // тут указываем ID элемента
            if (!div3.is(e.target) // если клик был не по нашему блоку
                && div3.has(e.target).length === 0) { // и не по его дочерним элементам
                jQuery("#close4-tar").hide();
                jQuery("#modal-window-container").hide();
                jQuery("#modal-window-1-tar").hide();
            }
        });

        jQuery("#new_dealer").click(function(){
            jQuery("#close4-tar").show();
            jQuery("#modal-window-container").show();
            jQuery("#modal-window-1-tar").show("slow");
        });

        jQuery("#save_dealer").click(function(){
             jQuery.ajax({
                type: 'POST',
                url: "index.php?option=com_gm_ceiling&task=dealer.create_dealer",
                data: {
                    fio: document.getElementById('fio_dealer').value,
                    phone: document.getElementById('dealer_contacts').value
                },
                success: function(data){
                    location.href = location.href;
                },
                dataType: "text",
                async: false,
                timeout: 10000,
                error: function(data){
                    var n = noty({
                        timeout: 2000,
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "error",
                        text: "Ошибка. Сервер не отвечает"
                    });
                }                   
            });
        });

        //для внесения денег
        jQuery(document).mouseup(function (e){ // событие клика по веб-документу
            var user_id = jQuery(this).attr("user_id");
            var div = jQuery(".modal_window"); // тут указываем ID элемента
            if (!div.is(e.target) // если клик был не по нашему блоку
                && div.has(e.target).length === 0) { // и не по его дочерним элементам
                jQuery(".close_btn").hide();
                jQuery(".modal_window_container").hide();
                jQuery(".modal_window").hide();
            }
        });

        jQuery(".btn-done").click(function(){
            var user_id = jQuery(this).attr("user_id");
            jQuery(".close_btn").show();
            jQuery("#modal_window_container" + user_id).show();
            jQuery("#modal_window_acct" + user_id).show("slow");
        });

        jQuery(".save_pay").click(function(){
            var user_id = jQuery(this).attr("user_id");
            jQuery.ajax({
                type: 'POST',
                url: "index.php?option=com_gm_ceiling&task=dealer.add_in_table_recoil_map_project",
                data: {
                    id: user_id,
                    sum: document.getElementById('pay_sum'+user_id).value
                },
                success: function(data){
                    var n = noty({
                        timeout: 5000,
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "success",
                        text: "Сумма успешно добавлена"
                    });
                    setInterval(function() { location.reload();}, 1500);
                },
                dataType: "text",
                async: false,
                timeout: 10000,
                error: function(data){
                    var n = noty({
                        timeout: 2000,
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "error",
                        text: "Ошибка. Сервер не отвечает"
                    });
                }
            });
        });


    });
</script>