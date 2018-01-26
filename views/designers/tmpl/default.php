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
$result_users = $users_model->getDesigners();
?>
    <a class="btn btn-large btn-primary"
       href="/index.php?option=com_gm_ceiling&view=mainpage&type=gmmanagermainpage"
       id="back"><i class="fa fa-arrow-left" aria-hidden="true"></i> Назад</a>
    <h2 class="center">Отделочники/Дизайнеры</h2>
    <div style="width: 48%; text-align: left;">
        <button type="button" id="new_designer" class="btn btn-primary">Создать Отделочника/дизайнера</button>
    </div>
    <br>
    <table class="table table-striped one-touch-view" id="callbacksList">
        <thead>
        <tr>
            <th>
               Имя
            </th>
            <th>
               Дата регистрации
            </th>
        </tr>
        </thead>
        <tbody>
        	<?php
        		foreach ($result_users as $key => $value)
        		{
        	?>
                <tr class="row<?php echo $i % 2; ?>" data-href="<?php echo JRoute::_('index.php?option=com_gm_ceiling&view=clientcard&type=designer&id='.(int) $value->associated_client); ?>">
		            <td>
		               <?php echo $value->name; ?>
		            </td>
		            <td>
		               <?php echo $value->registerDate; ?>
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
                <p><strong>Создание нового отделочника/дизайнера</strong></p>
                <p>ФИО:</p>
                <p><input type="text" id="fio_designer"></p>
                <p>Номер телефона:</p>
                <p><input type="text" id="designer_contacts"></p>
                <p><button type="submit" id="save_designer" class="btn btn-primary">ОК</button></p>
        </div>
    </div>

<script>
    jQuery(document).ready(function()
    {
        jQuery('#designer_contacts').mask('+7(999) 999-9999');
        jQuery('body').on('click', 'tr', function(e)
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

        jQuery("#new_designer").click(function(){
            jQuery("#close4-tar").show();
            jQuery("#modal-window-container").show();
            jQuery("#modal-window-1-tar").show("slow");
        });

        jQuery("#save_designer").click(function(){
            jQuery.ajax({
                type: 'POST',
                url: "index.php?option=com_gm_ceiling&task=dealer.create_designer",
                data: {
                    fio: document.getElementById('fio_designer').value,
                    phone: document.getElementById('designer_contacts').value
                },
                success: function(data){
                    location.reload();
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

        jQuery.ajax({
            type: 'POST',
            url: "index.php?option=com_gm_ceiling&task=RepeatSendCommercialOffer",
            success: function(data){
                console.log(data);
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
</script>