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
                    $this->updateMounter($calcId, $value->stage_id, $value->mounter_id);
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
    function updateMounter($calcId,$stageId,$mounterId,$calcsId = null){
        try{
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query
                ->update('`rgzbn_gm_ceiling_calcs_mount`')
                ->set("mounter_id = $mounterId");
            if(empty($calcsId)) {
                $query->where("calculation_id = $calcId and stage_id = $stageId");
            }
            else{
                $ids = "(".implode(',',$calcsId).")";
                $query->where("calculation_id in $ids and stage_id = $stageId");
            }
            $db->setQuery($query);
            $db->execute();
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
}