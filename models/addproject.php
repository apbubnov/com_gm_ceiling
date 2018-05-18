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
class Gm_ceilingModelAddproject extends JModelList {

    public function FindAllGauger($id) {
        try
        {
            $db = $this->getDbo();
            $query = $db->getQuery(true);

            $query->select('users.id')
                ->from('#__users as users')
                ->innerJoin('#__user_usergroup_map as usergroup_map ON users.id = usergroup_map.user_id')
                ->where("users.dealer_id = '$id' AND usergroup_map.group_id = '22'");
            $db->setQuery($query);

            $items = $db->loadObjectList();
    		return $items;
        }
        catch(Exception $e)
        {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

}
