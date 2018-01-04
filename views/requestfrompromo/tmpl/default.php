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
$dop_num_model = Gm_ceilingHelpersGm_ceiling::getModel('dop_numbers_of_users');
$dop_num = $dop_num_model->getData($userId)->dop_number;
$_SESSION['user_group'] = $user_group;
$_SESSION['dop_num'] = $dop_num;

?>

<form>
    <a class="btn btn-large btn-primary"
       href="/index.php?option=com_gm_ceiling&view=mainpage&type=gmmanagermainpage"
       id="back"><i class="fa fa-arrow-left" aria-hidden="true"></i> Назад</a>

    <table class="table table-striped one-touch-view" id="requests">
        <thead>
        <tr>
            <th >
               ФИО клиента
            </th>
            <th>
               Действие 
            </th>
            <th >
               Время
            </th>
        </tr>
        </thead>

        <tbody>
        <?php
            foreach($this->item as $item) :
        ?>
           
           <tr data-href="<?php echo JRoute::_('index.php?option=com_gm_ceiling&view=clientcard&id='.$item->client_id,false )?>">
                <td>
                    <?php echo $item->client_name;?>
                </td>
                <td>
                    <?php echo $item->action; ?>
                </td>
                <td>
                    <?php 
                        $date = new DateTime($item->date_time);
                        echo $date->Format('d.m.Y H:i');
                    ?>
                </td>
            </tr>
        <?php endforeach;?>
        </tbody>
    </table>
</form>

<script>
    jQuery(document).ready(function()
    {
        jQuery('body').on('click', 'tr', function(e)
        {
            if (jQuery(this).data('href') !== undefined)
            {
                document.location.href = jQuery(this).data('href');
            }
        });

    });

</script>