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

            $query->select('users.id, users.name')
                ->from('#__users as users')
                ->innerJoin('#__user_usergroup_map as usergroup_map ON users.id = usergroup_map.user_id')
                ->where("users.dealer_id = '$dealer_id' AND usergroup_map.group_id = '$group'");
            $db->setQuery($query);

            $items = $db->loadObjectList();
    		return $items;
        }
        catch(Exception $e)
        {
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }
    }

}
