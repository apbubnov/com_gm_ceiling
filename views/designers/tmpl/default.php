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
<form>
    <a class="btn btn-large btn-primary"
       href="/index.php?option=com_gm_ceiling&view=mainpage&type=gmmanagermainpage"
       id="back"><i class="fa fa-arrow-left" aria-hidden="true"></i> Назад</a>
    <h2 class="center">Отделочники/Дизайнеры</h2>
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
                <tr class="row<?php echo $i % 2; ?>" data-href="<?php echo JRoute::_('index.php?option=com_gm_ceiling&view=clientcard&type=dealer&id='.(int) $value->associated_client); ?>">
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
</form>

<script>
    jQuery(document).ready(function()
    {
        jQuery('body').on('click', 'tr', function(e)
        {

            if(jQuery(this).data('href')!=""){
                document.location.href = jQuery(this).data('href');
            } 
        });
    });
</script>