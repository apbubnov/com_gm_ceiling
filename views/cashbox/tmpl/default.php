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
            <?php foreach ($this->item as $item) {?>
                <tr>
                    <td>
                        <?php
                            if (isset($item->closed))
                             echo $item->closed;
                             else echo $item->date_time;
                        ?>
                    
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
                            $cashbox += $residue;
                            echo $cashbox;
                        ?>
                    </td>
                    <td>
                        <?php 
                            if(isset($item->sum)) 
                             echo $item->sum ;?>
                    </td>

                </tr>
            <?php }?>
        </tbody>
    </table>
</form>

<script>
   
</script>