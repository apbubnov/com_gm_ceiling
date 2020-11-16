<?php

/**
 * @version    CVS: 0.1.7
 * @package    Com_Gm_ceiling
 * @author     SpectralEye <Xander@spectraleye.ru>
 * @copyright  2016 SpectralEye
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.modelitem');
jimport('joomla.event.dispatcher');

/**
 * Methods supporting a list of Gm_ceiling records.
 *
 * @since  1.6
 */
class Gm_ceilingModelUsers_dealer_id_map extends JModelList{
    function getData($id){
        try{
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query
                ->select('*')
                ->from('`rgzbn_users_dealer_id_map`')
                ->where("id = $id");
            $db->setQuery($query);
            $data = $db->loadObject();
            return $data;
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function getDealerIdMap($id){
        try{
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query
                ->select('dm.id,dm.group_id,dm.dealer_type,dm.dealer_id,g.title,u.name')
                ->from('`rgzbn_users_dealer_id_map` AS dm')
                ->leftJoin('`rgzbn_users` AS u ON dm.dealer_id = u.id')
                ->leftJoin('`rgzbn_usergroups` AS g ON g.id = dm.group_id')
                ->where("dm.user_id = $id");
            $db->setQuery($query);
            $result = $db->loadObjectList();
            return $result;
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function getSavedGroups($id){
        try{
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query
                ->select('dm.group_id')
                ->from('`rgzbn_users_dealer_id_map` AS dm')
                ->where("dm.user_id = $id");
            $db->setQuery($query);
            $result = $db->loadColumn();
            return $result;
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
    function saveDealerIdMap($userId,$dealerId,$groupId,$dealerType){
        try{

            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query->insert('`rgzbn_users_dealer_id_map`');
            if(!empty($dealerType)){
                $query
                    ->columns('`user_id`,`dealer_id`,`group_id`,`dealer_type`')
                    ->values("$userId,$dealerId,$groupId,$dealerType");
            }
            else{
                $query
                    ->columns('`user_id`,`dealer_id`,`group_id`')
                    ->values("$userId,$dealerId,$groupId");
            }

            $db->setQuery($query);
            $db->execute();
            return true;
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
    function setGroupActive($id,$userId){
        try{
            if(!empty($userId) && !empty($id)) {
                $db = JFactory::getDbo();
                $query = $db->getQuery(true);
                $query
                    ->update('rgzbn_users_dealer_id_map')
                    ->set("is_active = 0")
                    ->where("user_id = $userId");
                $db->setQuery($query);
                $db->execute();

                $query = $db->getQuery(true);
                $query
                    ->update('rgzbn_users_dealer_id_map')
                    ->set("is_active = 1")
                    ->where("id = $id");
                $db->setQuery($query);
                $db->execute();
            }
            return true;
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
}