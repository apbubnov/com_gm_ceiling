<?php
/**
 * Created by PhpStorm.
 * User: bubnov
 * Date: 17.12.2018
 * Time: 9:15
 */
// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.modelitem');
jimport('joomla.event.dispatcher');

class Gm_ceilingModelCalcs_mount extends JModelItem{
    function getData($calcId){
        try{
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query
                ->select("stage_id,sum")
                ->from('`rgzbn_gm_ceiling_calcs_mount`')
                ->where("`calculation_id` = $calcId");
            $db->setQuery($query);
            $result = $db->loadObjectList();
            return $result;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
    function save($data){
        try{
            $calcId = $data['id'];
            $stages = $data['stages'];
            $stagesMounters = $this->selectMounter($calcId);
            $this->delete($calcId);
            foreach ($stages as $stage=>$sum){
                $insertData = [];
                $insertData['calculation_id'] = $calcId;
                $insertData['stage_id'] = $stage;
                $insertData['sum'] = $sum;
                $this->insert($insertData);
            }
            foreach($stagesMounters as $value){
                if(!empty($value->mounter_id)) {
                    $this->updateMounter($calcId, $value->stage_id, $value->mounter_id,null,true);
                }
            }

        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function selectMounter($calcId){
        try{
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query
                ->select("stage_id,mounter_id")
                ->from('`rgzbn_gm_ceiling_calcs_mount`')
                ->where("`calculation_id` = $calcId");
            $db->setQuery($query);
            $result = $db->loadObjectList();
            return $result;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function insert($data){
        try{
            $columns = array_keys($data);
            $values = implode(',',array_values($data));
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query
                ->insert('`rgzbn_gm_ceiling_calcs_mount`')
                ->columns($columns)
                ->values($values);

            $db->setQuery($query);
            $db->execute();
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function delete($calcId){
        try{
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query
                ->delete('`rgzbn_gm_ceiling_calcs_mount`')
                ->where("calculation_id = $calcId");
            $db->setQuery($query);
            $db->execute();
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
    function updateMounter($calcId,$stageId,$mounterId,$calcsId = null,$recalc = null){
        try{
            $user = JFactory::getUser();
            $groups = $user->groups;
            $status = '(NULL)';
            if(in_array(33,$groups)){
                $status = 4;
            }
            if(in_array(34,$groups)){
                $status = 3;
            }
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query
                ->update('`rgzbn_gm_ceiling_calcs_mount`')
                ->set("mounter_id = $mounterId")
                ->set("status_id = $status");
            if(empty($calcsId)) {
                $query->where("calculation_id = $calcId and stage_id = $stageId");
            }
            else{
                $ids = "(".implode(',',$calcsId).")";
                $query->where("calculation_id in $ids and stage_id = $stageId");
            }
            $db->setQuery($query);
            $db->execute();
            if($status == 4 && empty($recalc)){
                $calculationModel = Gm_ceilingHelpersGm_ceiling::getModel('calculation');
                $projectModel = Gm_ceilingHelpersGm_ceiling::getModel('project');
                if(!empty($calcId)){
                    $calculation = $calculationModel->getBaseCalculationDataById($calcId);
                    $projectId = $calculation->project_id;
                }
                else{
                    if(!empty($calcsId)){
                        $calculation = $calculationModel->getBaseCalculationDataById($calcsId[0]);
                        $projectId = $calculation->project_id;
                    }
                }
                if(!empty($projectId)){
                    $projectModel->newStatus($projectId,$stageId+25);
                }
            }
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function getMounterSum($mounter_id,$builder_id){
        try{
            /*SELECT mt.title,SUM(cm.sum) AS stage_sum
                FROM `rgzbn_gm_ceiling_calcs_mount` AS cm
                LEFT JOIN `rgzbn_gm_ceiling_calculations` AS c ON c.id = cm.calculation_id
                LEFT JOIN `rgzbn_gm_ceiling_projects` AS p ON c.project_id = p.id
                LEFT JOIN `rgzbn_gm_ceiling_clients` AS cl ON p.client_id = cl.id
                LEFT JOIN `rgzbn_gm_ceiling_mounts_types` AS mt ON mt.id = cm.stage_id
                WHERE mounter_id = 39 AND cl.dealer_id = 721
                GROUP BY cm.stage_id*/
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query
                ->select("mt.title,SUM(cm.sum) AS stage_sum")
                ->from("`rgzbn_gm_ceiling_calcs_mount` AS cm")
                ->leftJoin("`rgzbn_gm_ceiling_calculations` AS c ON c.id = cm.calculation_id")
                ->leftJoin("`rgzbn_gm_ceiling_projects` AS p ON c.project_id = p.id")
                ->leftJoin("`rgzbn_gm_ceiling_clients` AS cl ON p.client_id = cl.id")
                ->leftJoin("`rgzbn_gm_ceiling_mounts_types` AS mt ON mt.id = cm.stage_id")
                ->where("mounter_id = $mounter_id and cl.dealer_id = $builder_id")
                ->group("cm.stage_id");
            $db->setQuery($query);
            $result = $db->loadObjectList();
            return $result;
        }

        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function approveTaking($calcs_id,$stage){
        try{
            $ids = implode(',',$calcs_id);
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query
                ->update('`rgzbn_gm_ceiling_calcs_mount`')
                ->set('status_id = 4')
                ->where("calculation_id in($ids) and stage_id = $stage and status_id = 3");
            $db->setQuery($query);
            $db->execute();
            $calculationModel = Gm_ceilingHelpersGm_ceiling::getModel('calculation');
            $projectModel = Gm_ceilingHelpersGm_ceiling::getModel('project');
            if(!empty($calcs_id)){
                $calculation = $calculationModel->getBaseCalculationDataById($calcs_id[0]);
                $projectId = $calculation->project_id;
            }

            if(!empty($projectId)){
                $projectModel->newStatus($projectId,$stage+25);
            }
            return true;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
}