<?php
/**
 * Created by PhpStorm.
 * User: bubnov
 * Date: 20.11.2019
 * Time: 11:27
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.modelitem');
jimport('joomla.event.dispatcher');

class Gm_ceilingModelMountersDebt extends JModelItem
{
    function getData($mounterId){
        try{
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query
                ->select('md.sum,DATE_FORMAT(md.date_time, \'%d.%m.%Y\') as date_time,u.name,dt.title')
                ->from('`rgzbn_gm_ceiling_mounters_debt` as md')
                ->innerJoin('`rgzbn_users` as u on u.id = md.created_by')
                ->innerJoin('`rgzbn_gm_ceiling_debt_type` as dt on dt.id = md.type')
                ->where("md.mounter_id = $mounterId");
            $db->setQuery($query);
            $items = $db->loadObjectList();
            return $items;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function save($mounter_id,$sum,$type){
        try {
            $user = JFactory::getUser();
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query
                ->insert('`rgzbn_gm_ceiling_mounters_debt`')
                ->columns('`mounter_id`,`sum`,`type`,`created_by`')
                ->values("$mounter_id,$sum,$type,$user->id");
            $db->setQuery($query);
            $db->execute();
            return true;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function getTypes(){
        try{
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query
                ->select('*')
                ->from('`rgzbn_gm_ceiling_debt_type`');
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
?>