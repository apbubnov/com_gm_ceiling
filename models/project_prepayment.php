<?php
/**
 * Created by PhpStorm.
 * User: bubnov
 * Date: 17.05.2019
 * Time: 15:11
 */
defined('_JEXEC') or die;

class Gm_ceilingModelProject_Prepayment extends JModelItem
{
    function save($projectId,$sum){
        try{
            if(!empty($projectId)) {
                $sum = floatval($sum);
                if(!empty($sum)){
                    $db = JFactory::getDbo();
                    $query = $db->getQuery(true);
                    $query
                        ->insert('`rgzbn_gm_ceiling_projects_prepayment`')
                        ->columns('`project_id`, `prepayment_sum`')
                        ->values("$projectId,$sum");
                    $db->setQuery($query);
                    $db->execute();
                    return true;
                }
                else{
                    return null;
                }
            }
            else{
                return null;
            }
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function getData($projectId){
        try{
            $db    = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query
                ->select("id,prepayment_sum,DATE_FORMAT(datetime,'%d.%m.%Y %H:%i:%s') as datetime")
                ->from('`rgzbn_gm_ceiling_projects_prepayment`')
                ->where("`project_id` = $projectId");
            $db->setQuery($query);
            $items = $db->loadObjectList();
            return $items;
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function delete($id){
        try{
            $db    = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query
                ->delete('`rgzbn_gm_ceiling_projects_prepayment`')
                ->where("`id` = $id");
            $db->setQuery($query);
            $db->execute();
            return true;
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
}
?>