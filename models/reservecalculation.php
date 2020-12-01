<?php

/**
 * @version    CVS: 0.1.7
 * @package    Com_Gm_ceiling
 * @author     SpectralEye <Xander@spectraleye.ru>
 * @copyright  2016 SpectralEye
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');

/**
 * Methods supporting a list of Gm_ceiling records.
 *
 * @since  1.6
 */
class Gm_ceilingModelReservecalculation extends JModelList {

    public function FindAllGauger($dealer_id) {
        try
        {
            $db = $this->getDbo();
            $query = $db->getQuery(true);

            if ($dealer_id == 1) {
                $group = "22";
            } else {
                $group = "21";
            }

            $query
                ->select('DISTINCT users.id, users.name')
                ->from('#__users as users')
                ->leftJoin('rgzbn_users_dealer_id_map as dm on dm.user_id = users.id')
                ->innerJoin('#__user_usergroup_map as usergroup_map ON users.id = usergroup_map.user_id')
                ->where("(users.dealer_id = '$dealer_id' AND usergroup_map.group_id = '$group') OR (dm.dealer_id = $dealer_id AND dm.group_id = $group)");
            $db->setQuery($query);

            $items = $db->loadObjectList();
    		return $items;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

}
