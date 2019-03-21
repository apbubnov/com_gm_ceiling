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
    function getData($filter){
        try{
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);

            $query->select("ms.mounter_id,u.name,SUM(ms.sum) AS total")
                ->from('`rgzbn_gm_ceiling_mounters_salary` AS ms')
                ->innerJoin('`rgzbn_users` AS u ON u.id = ms.mounter_id')
                ->group('ms.mounter_id');
            if(!empty($filter)){
                $query->where($filter);
            }
            $db->setQuery($query);
            $items = $db->loadObjectList();
            return $items;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function getDataById($id,$projects){
        try{
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            if(!empty($id)){
                $query->select("u.id,u.name,ms.sum,concat(p.project_info,' ',ms.note) as note")
                    ->from('`rgzbn_gm_ceiling_mounters_salary` AS ms')
                    ->innerJoin('`rgzbn_users` as u on u.id = ms.mounter_id')
                    ->innerJoin('`rgzbn_gm_ceiling_projects` as p on p.id = ms.project_id')
                    ->where("ms.mounter_id = $id $projects");
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
            if(!empty($mounterId)){
                $query->insert('`#__gm_ceiling_mounters_salary`')
                    ->columns('`mounter_id`,`project_id`,`sum`,`note`')
                    ->values("$mounterId,$projectId,$sum,'$note'");
                $db->setQuery($query);
                $db->execute();
                return true;
            }
            else{
                throw new Exception("empty_mounter");
            }
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function delete($mounterId,$projectId){
        try{
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            if(!empty($mounterId) && !empty($projectId)){
                $query
                    ->delete('`#__gm_ceiling_mounters_salary`')
                    ->where("mounter_id = $mounterId and project_id = $projectId");
                $db->setQuery($query);
                $db->execute();
                return true;
            }
            else{
                throw new Exception("empty_mounter");
            }
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }

    }

    function getClosedSumByMounter($mounter_id,$builder_id){
        try{
        /*
         * SELECT SUM(ms.sum) AS `sum`
            FROM `rgzbn_gm_ceiling_mounters_salary` AS ms
            LEFT JOIN `rgzbn_gm_ceiling_projects` AS p ON ms.project_id = p.id
            LEFT JOIN `rgzbn_gm_ceiling_clients` AS cl ON p.client_id = cl.id
            WHERE mounter_id = 33 AND cl.dealer_id = 721*/
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query
                ->select("SUM(ms.sum) AS `sum`")
                ->from("`rgzbn_gm_ceiling_mounters_salary` AS ms")
                ->leftJoin("`rgzbn_gm_ceiling_projects` AS p ON ms.project_id = p.id")
                ->leftJoin("`rgzbn_gm_ceiling_clients` AS cl ON p.client_id = cl.id")
                ->where("mounter_id = $mounter_id AND cl.dealer_id = $builder_id");
            $db->setQuery($query);
            $result = $db->loadObject();
            return $result;
        }

        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
}