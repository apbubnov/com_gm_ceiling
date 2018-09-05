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
    <table class="table table-striped one-touch-view">
        <thead>
        <tr>
            <th class='center'>
               ФИО
            </th>
            <th class='center'>
               Номер телефона
            </th>
            <th class='center'>
               Сумма
            </th>
        </tr>
        </thead>

        <tbody>
            <?php foreach($this->item as $item){?>
            <tr data-href ="<?php echo JRoute::_('index.php?option=com_gm_ceiling&view=recoil_map_project&id='.(int)$item->id);?>">
                <td class="center one-touch"><?php echo $item->name;?></td>
                <td class="center one-touch"><?php echo $item->username; ?></td>
                <td class="center one-touch"><?php echo $item->sum;?></td>
            </tr>
            <?php }?>
        </tbody>
    </table>
</form>
<script>

</script>