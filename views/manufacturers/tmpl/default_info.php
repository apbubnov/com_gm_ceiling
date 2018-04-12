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

$jinput = JFactory::getApplication()->input;
$id = $jinput->get('id','','INT');
$user = JFactory::getUser($id);
$model = Gm_ceilingHelpersGm_ceiling::getModel("dealer_info");
$city = $model->getDataById($user->id)->city;
?>

<form>
    <table class="table table-striped one-touch-view" id="manufacturers">
        <tr>
            <th>Название</th>
            <td><input class="input-gm" id="name" value="<?php echo $user->name; ?>"/>
                <button class = "btn btn-primary" type ="button" name="save">Ок</button></td>
        </tr>
         <tr>
            <th>Телефон</th>
            <td><input class="input-gm" id="phone" value="<?php echo $user->username; ?>"/>
                <button class = "btn btn-primary" type ="button" name="save">Ок</button></td>
        </tr>
         <tr>
            <th>email</th>
            <td><input id="email" class="input-gm" value="<?php echo $user->email; ?>"/>
                <button class = "btn btn-primary" type ="button" name="save">Ок</button></td>
        </tr>
        <tr>
            <th>Город</th>
            <td><input class="input-gm" id="city" value="<?php echo $city; ?>"/>
                <button class = "btn btn-primary" type ="button" name="save">Ок</button></td>
        </tr>
    </table>
</form>

<script type="text/javascript">
    jQuery("[name = save]").click(function(){
         let el = jQuery(this).closest('td').find('.input-gm');

        jQuery.ajax({
            type: 'POST',
            url: "index.php?option=com_gm_ceiling&task=update_mnfctr",
            data:{
                field: el[0].id,
                value: el[0].value,
                id: "<?php echo $id;?>"
            },
            async: true,
            success: function(data){
               location.reload();
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

    })
</script>