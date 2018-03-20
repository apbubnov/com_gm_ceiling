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
                <tr connected = <?php echo $item->connect ?>>
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
</form>

<script>
    jQuery(document).ready(function(){
        jQuery("#manufacturers > tbody > tr").click(function(){
            console.log(this.connected);
        });
    });
</script>