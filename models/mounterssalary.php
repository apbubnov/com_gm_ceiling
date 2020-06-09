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
            $taken_query = $db->getQuery(true);
            $taken_query
                ->select("cm.mounter_id AS mounter_id,u.name,SUM(cm.sum) AS `taken`,SUM(IF(stage_id=2,cm.sum,0)) AS obag,SUM(IF(stage_id=3,cm.sum,0)) AS natyazh,SUM(IF(stage_id=4,cm.sum,0)) AS vstav")
                ->from("`rgzbn_gm_ceiling_calcs_mount` AS cm")
                ->leftJoin("`rgzbn_gm_ceiling_calculations` AS cl ON cl.id = cm.calculation_id")
                ->leftJoin("`rgzbn_gm_ceiling_projects` AS pr ON cl.project_id = pr.id")
                ->leftJoin("`rgzbn_gm_ceiling_clients` AS cli ON pr.client_id = cli.id")
                ->leftJoin("`rgzbn_users` AS u ON u.id = cm.mounter_id")
                ->where("cli.dealer_id = $builder_id AND cm.mounter_id IS NOT NULL and pr.deleted_by_user = 0")
                ->group("cm.mounter_id");
            $db->setQuery($taken_query);

            $taken_items = $db->loadObjectList();

            $query->select("ms.mounter_id,u.name,SUM(IF(ms.sum > 0 AND ms.builder_id IS NULL,ms.sum, 0 ))AS  closed, SUM(LEAST(0.00,ms.sum)) AS payed")
                ->from('`rgzbn_gm_ceiling_mounters_salary` AS ms')
                ->leftJoin("`rgzbn_gm_ceiling_projects` AS p ON p.id = ms.project_id")
                ->leftJoin("`rgzbn_gm_ceiling_clients` AS c ON c.id = p.client_id")
                ->innerJoin('`rgzbn_users` AS u ON u.id = ms.mounter_id')
                ->where(" c.dealer_id = $builder_id OR ms.builder_id = $builder_id")
                ->group('ms.mounter_id');
            $db->setQuery($query);
            $items = $db->loadObjectList();
            $result = [];
            foreach ($taken_items as $taken_item){
                $object = (object)array("mounter_id"=>$taken_item->mounter_id,"name"=>$taken_item->name,"taken"=>$taken_item->taken,"obag"=>$taken_item->obag,"natyazh"=>$taken_item->natyazh,"vstav"=>$taken_item->vstav,"closed"=>0,"payed"=>0);
                $result[$taken_item->mounter_id] = $object;
            }
            foreach ($items as $item){

                if(isset($result[$item->mounter_id])){
                    $result[$item->mounter_id]->closed = $item->closed;
                    $result[$item->mounter_id]->payed = $item->payed;
                }
                else{
                    $object = (object)array("mounter_id"=>$item->mounter_id,"name"=>$item->name,"taken"=>0,"obag"=>0,"natyazh"=>0,"vstav"=>0,"closed"=> $item->closed,"payed"=> $item->payed);
                    $result[$item->mounter_id] = $object;
                }
            }
            return $result;
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
                $query->select("ms.id as sId,u.id,u.name,ms.sum,IFNULL(CONCAT(p.project_info,' ',ms.note),ms.note) AS note,DATE_FORMAT(`datetime`,'%d.%m.%Y %H:%i:%s') AS `datetime`")
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

    function getMounterSalaryByBuilder($mounterId,$builder_id){
        try{
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $subquery = $db->getQuery(true);
            $subquery
                ->select('id')
                ->from('`rgzbn_gm_ceiling_clients`')
                ->where("dealer_id = $builder_id");
            $query
                ->select('id')
                ->from('`rgzbn_gm_ceiling_projects`')
                ->where("client_id in ($subquery)");
            $db->setQuery($query);
            $items = $db->loadObjectList();
            $projectsId = [];
            if(!empty($items)){
                foreach ($items as $item){
                    array_push($projectsId,$item->id);
                }
            }
            $projectFilter = (!empty($projectsId)) ? "AND (ms.project_id IN(".implode(",",$projectsId).") or builder_id = $builder_id)" : " and builder_id = $builder_id";
            $result = $this->getDataById($mounterId,$projectFilter);
            return $result;
        }
        catch(Exception $e){
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function transferRest($mounterId,$oldBuilderId,$newBuilderId,$rest){
        try{
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $oldBuilder = JFactory::getUser($oldBuilderId);
            $newBuilder = Jfactory::getUser($newBuilderId);
            $values = [];
            $values[] = "$mounterId,$oldBuilderId,0-$rest,'Перенос остатка в $newBuilder->name'";
            $values[] = "$mounterId,$newBuilderId,$rest,'Перенос остатка из $oldBuilder->name'";
            if(!empty($mounterId)&&!empty($oldBuilderId)&&!empty($newBuilderId)&&!empty($rest)) {
                $query->insert('`#__gm_ceiling_mounters_salary`')
                    ->columns('`mounter_id`,`builder_id`,`sum`,`note`')
                    ->values($values);
                $db->setQuery($query);
                $db->execute();
                return true;
            }
        }
        catch(Exception $e){
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function recalcClosedSum($builder_id){
        try{
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $deleteQuery = $db->getQuery(true);
            $deleteSubQuery = $db->getQuery(true);
            $deleteSubQuery
                ->select('p.id')
                ->from('`rgzbn_gm_ceiling_projects` as p ')
                ->innerJoin('`rgzbn_gm_ceiling_clients` as c on c.id = p.client_id')
                ->where("c.dealer_id = $builder_id");
            $deleteQuery
                ->delete('`rgzbn_gm_ceiling_mounters_salary`')
                ->where("project_id in ($deleteSubQuery)");
            $db->setQuery($deleteQuery);
            $db->execute();
            $query
                ->select('p.id as project_id,p.project_status,mt.title,c.calculation_title,cl.client_name,cm.*')
                ->from('`rgzbn_gm_ceiling_calcs_mount` AS cm')
                ->innerJoin('`rgzbn_gm_ceiling_calculations` AS c ON c.id = cm.calculation_id')
                ->innerJoin('`rgzbn_gm_ceiling_projects` AS p ON p.id = c.project_id')
                ->innerJoin('`rgzbn_gm_ceiling_mounts_types` AS mt ON cm.stage_id = mt.id')
                ->innerJoin('`rgzbn_gm_ceiling_clients` AS cl ON cl.id = p.client_id')
                ->where(" cl.dealer_id = $builder_id AND mounter_id IS NOT NULL");
            $db->setQuery($query);
            $items = $db->loadObjectlist();
            //throw new Exception(print_r($items,true));
            $insertArr = [];
            foreach ($items as $item){
                if($item->project_status >= $item->stage+29){
                    $insertArr[] = "$item->mounter_id,$item->project_id,$item->sum,'$item->client_name $item->calculation_title $item->title'";
                }
            }
            $query = $db->getQuery(true);
            $query
                ->insert('`rgzbn_gm_ceiling_mounters_salary`')
                ->columns('`mounter_id`,`project_id`,`sum`,`note`')
                ->values($insertArr);
            $db->setQuery($query);
            $db->execute();
        }
        catch(Exception $e){
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function deletePay($id,$builder,$mounter,$sum){
        try{
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query
                ->delete('`rgzbn_gm_ceiling_mounters_salary`')
                ->where("`id`=$id and `mounter_id` = $mounter and `builder_id` = $builder and `sum` = $sum ");
            $db->setQuery($query);
            $db->execute();
            return true;
        }
        catch(Exception $e){
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
}