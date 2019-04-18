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
    function getData($builder_id){
        try{
            /*
             * SELECT ms.mounter_id,SUM(GREATEST(0.00,ms.sum)) AS  closed, SUM(LEAST(0.00,ms.sum)) AS payed,t.sum AS taken
                FROM `rgzbn_gm_ceiling_mounters_salary` AS ms
                LEFT JOIN `rgzbn_gm_ceiling_projects` AS p ON p.id = ms.project_id
                LEFT JOIN `rgzbn_gm_ceiling_clients` AS c ON c.id = p.client_id
                LEFT JOIN
                (SELECT cm.mounter_id AS mounter_id,SUM(cm.sum) AS `sum`
                FROM `rgzbn_gm_ceiling_calcs_mount` AS cm
                LEFT JOIN `rgzbn_gm_ceiling_calculations` AS cl ON cl.id = cm.calculation_id
                LEFT JOIN `rgzbn_gm_ceiling_projects` AS pr ON cl.project_id = pr.id
                LEFT JOIN `rgzbn_gm_ceiling_clients` AS cli ON pr.client_id = cli.id
                WHERE cli.dealer_id = 721
                 GROUP BY cm.mounter_id) AS t ON t.mounter_id = ms.mounter_id
                WHERE c.dealer_id = 721 || ms.builder_id =721
                GROUP BY ms.mounter_id*/
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $subquery = $db->getQuery(true);
            $subquery
                ->select("cm.mounter_id AS mounter_id,SUM(cm.sum) AS `sum`")
                ->from("`rgzbn_gm_ceiling_calcs_mount` AS cm")
                ->leftJoin("`rgzbn_gm_ceiling_calculations` AS cl ON cl.id = cm.calculation_id")
                ->leftJoin("`rgzbn_gm_ceiling_projects` AS pr ON cl.project_id = pr.id")
                ->leftJoin("`rgzbn_gm_ceiling_clients` AS cli ON pr.client_id = cli.id")
                ->where("cli.dealer_id = $builder_id")
                ->group("cm.mounter_id");
            $query->select("ms.mounter_id,u.name,SUM(GREATEST(0.00,ms.sum)) AS  closed, SUM(LEAST(0.00,ms.sum)) AS payed,t.sum AS taken")
                ->from('`rgzbn_gm_ceiling_mounters_salary` AS ms')
                ->leftJoin("`rgzbn_gm_ceiling_projects` AS p ON p.id = ms.project_id")
                ->leftJoin("`rgzbn_gm_ceiling_clients` AS c ON c.id = p.client_id")
                ->leftJoin("($subquery) as t ON t.mounter_id = ms.mounter_id")
                ->innerJoin('`rgzbn_users` AS u ON u.id = ms.mounter_id')
                ->where(" c.dealer_id = $builder_id OR ms.builder_id = $builder_id")
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

    function getDataById($id,$projects){
        try{
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            if(!empty($id)){
                $query->select("u.id,u.name,ms.sum,concat(p.project_info,' ',ms.note) as note,DATE_FORMAT(`datetime`,'%d.%m.%Y %H:%i:%s') AS `datetime`")
                    ->from('`rgzbn_gm_ceiling_mounters_salary` AS ms')
                    ->innerJoin('`rgzbn_users` as u on u.id = ms.mounter_id')
                    ->leftJoin('`rgzbn_gm_ceiling_projects` as p on p.id = ms.project_id')
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

    function savePay($mounterId,$builderId,$sum){
        try{
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            if(!empty($mounterId)&&!empty($builderId)&&!empty($sum)) {
                $query->insert('`#__gm_ceiling_mounters_salary`')
                    ->columns('`mounter_id`,`builder_id`,`sum`')
                    ->values("$mounterId,$builderId,$sum");
                $db->setQuery($query);
                $db->execute();
                return true;
            }
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
    function delete($mounterId,$projectId,$stage){
        try{
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            if(!empty($mounterId) && !empty($projectId)){
                $query
                    ->delete('`#__gm_ceiling_mounters_salary`')
                    ->where("mounter_id = $mounterId and project_id = $projectId and note like '%$stage%'");
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

    function updateSumMounter($mounterId,$newMounterId,$projectId,$calcTitle,$sum){
        try{
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query
                ->update("`rgzbn_gm_ceiling_mounters_salary` AS ms")
                ->set("mounter_id = $newMounterId")
                ->where("mounter_id = $mounterId and project_id = $projectId and sum = $sum and note like '%$calcTitle%'");
            $db->setQuery($query);
            $db->execute();
            return true;
        }
        catch(Exception $e){
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
}