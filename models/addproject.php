<?php

/**
 * @version    CVS: 0.1.7
 * @package    Com_Gm_ceiling
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
class Gm_ceilingModelAddproject extends JModelList {

    public function FindAllGauger($id) {
        try
        {
            $db = $this->getDbo();
            $query = $db->getQuery(true);

            $query
                ->select('DISTINCT u.id')
                ->from('#__users as u')
                ->leftJoin('`rgzbn_users_dealer_id_map` as `dm` on `dm`.`user_id` = `u`.`id`')
                ->innerJoin('#__user_usergroup_map as um ON u.id = um.user_id')
                ->where("(u.dealer_id = '$id' AND um.group_id = '22') OR (dm.dealer_id = $id AND dm.group_id = 22)");
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
