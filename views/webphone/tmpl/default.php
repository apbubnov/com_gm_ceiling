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

$dop_num_model = Gm_ceilingHelpersGm_ceiling::getModel('dop_numbers_of_users');
$dop_num = $dop_num_model->getData($userId)->dop_number;
?>

<iframe src="//ip.veryline.ru/clients/mp-vrn.ru/index.php" width="960" height="672" frameborder="0" scrolling="no" allowtransparency="" id='viget'></iframe>
<script src="https://api.yandex.mightycall.ru/api/v2/sdk/mightycall.webphone.sdk.js"></script>
<script src="/components/com_gm_ceiling/phone.js"></script>

<script>
    jQuery(document).ready(function()
    {
        <?php
            $user       = JFactory::getUser();
            $userId     = $user->get('id');
            $dop_num_model = Gm_ceilingHelpersGm_ceiling::getModel('dop_numbers_of_users');
            $dop_num = $dop_num_model->getData($userId)->dop_number;
        ?>
        jQuery('#rt-bottom').css('display','none');
        jQuery('header').css('display','none');
        phone("05e1b3d1-bad1-4966-919d-dc76f71e4c5a", "<?php echo $dop_num; ?>");
        nearest_callback();
    });
</script>