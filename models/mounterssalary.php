<?php
/**
 * Created by PhpStorm.
 * User: bubnov
 * Date: 05.12.2018
 * Time: 9:41
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.modelitem');
jimport('joomla.event.dispatcher');

class Gm_ceilingModelMountersSalary extends JModelItem {
    function getData(){
        try{
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);

            $query->select("ms.mounter_id,u.name,SUM(ms.sum) AS total")
                ->from('`rgzbn_gm_ceiling_mounters_salary` AS ms')
                ->innerJoin('`rgzbn_users` AS u ON u.id = ms.mounter_id')
                ->group('ms.mounter_id');
            $db->setQuery($query);
            $items = $db->loadObjectList();
            return $items;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function getDataById($id){
        try{
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            if(!empty($id)){
                $query->select("u.id,u.name,ms.sum,concat(p.project_info,' ',ms.note) as note")
                    ->from('`rgzbn_gm_ceiling_mounters_salary` AS ms')
                    ->innerJoin('`rgzbn_users` as u on u.id = ms.mounter_id')
                    ->innerJoin('`rgzbn_gm_ceiling_projects` as p on p.id = ms.project_id')
                    ->where("ms.mounter_id = $id");
                $db->setQuery($query);
                $items = $db->loadObjectList();
            }
            else{
                $items = [];
            }

            return $items;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function save($mounterId,$projectId,$sum,$note){
        try{
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);

            $query->insert('`#__gm_ceiling_mounters_salary`')
                ->columns('`mounter_id`,`project_id`,`sum`,`note`')
                ->values("$mounterId,$projectId,$sum,'$note'");
            $db->setQuery($query);
            $db->execute();
            return true;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
}