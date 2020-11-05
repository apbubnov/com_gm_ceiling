<?php

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.modelitem');
jimport('joomla.event.dispatcher');

/**
 * Methods supporting a list of Gm_ceiling records.
 *
 * @since  1.6
 */
class Gm_ceilingModelClient_State_Of_Account extends JModelList
{
    function getData($clientId,$startDate = null,$endDate = null){
        try{
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query
                ->select('`soa`.`id`,`c`.`client_name`,`soa`.`project_id`,`soa`.`comment`,`soa`.`sum`,`soa`.`operation_type`,`dt`.`title` as `operation`,DATE_FORMAT(`soa`.`date_time`,"%d.%m.%Y %H:%i:%s") as `date`')
                ->from('`rgzbn_gm_ceiling_client_state_of_account` as soa')
                ->innerJoin('`rgzbn_gm_ceiling_debt_type` as `dt` on `dt`.`id` = `soa`.`operation_type`')
                ->innerJoin('`rgzbn_gm_ceiling_clients` as `c` on `c`.`id` = `soa`.client_id')
                ->where("`soa`.`client_id` = $clientId")
                ->order('`soa`.`date_time` desc');
            if(!empty($startDate)&&!empty($endDate)){
                $query->where("`soa`.`date_time` between '$startDate 00:00:00' and '$endDate 23:59:59'");
            }
            if(!empty($startDate)&&empty($endDate)){
                $query->where("`soa`.`date_time` >= '$startDate 00:00:00'");
            }
            if(empty($startDate)&&!empty($endDate)){
                $query->where("`soa`.`date_time` <= '$endDate 23:59:59'");
            }

            $db->setQuery($query);
            $data = $db->loadObjectList();
            return $data;
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function save($client_id,$operation,$sum,$comment,$project_id){
        try{
            $user = JFactory::getUser();
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            if(!empty($client_id) && !empty($operation) && !empty($sum)) {
                $columns = '`client_id`,`operation_type`,`sum`,`created_by`';
                $values = "$client_id,$operation,$sum,$user->id";

                if (!empty($project_id)) {
                    $columns .= ',`project_id`';
                    $values .= ",$project_id";
                }
                if (!empty($comment)) {
                    $columns .= ',`comment`';
                    $values .= ",'$comment'";
                }

                $query
                    ->insert('`rgzbn_gm_ceiling_client_state_of_account`')
                    ->columns($columns)
                    ->values($values);
                $db->setQuery($query);
                $db->execute();
            }
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function getStateOfAccount($clientId){
        try{
            if(!empty($clientId)){
                $db = JFactory::getDbo();
                $query = $db->getQuery(true);
                $query
                    ->select('SUM(IF(`soa`.`operation_type` = 1,`soa`.`sum`,0)) - SUM(IF(`soa`.`operation_type` = 2,`soa`.`sum`,0)) as sum')
                    ->from('`rgzbn_gm_ceiling_client_state_of_account` as soa')
                    ->where("client_id = $clientId");
                $db->setQuery($query);
                $data = $db->loadObject();
                return $data;
            }
            else{
                return (object)['sum'=>0];
            }
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function getStateOfAccountBeforeDate($clientId,$dateFrom){
        try{
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query
                ->select('SUM(IF(`soa`.`operation_type` = 1,`soa`.`sum`,0)) - SUM(IF(`soa`.`operation_type` = 2,`soa`.`sum`,0)) as sum')
                ->from('`rgzbn_gm_ceiling_client_state_of_account` as soa')
                ->where("client_id = $clientId and date_time<'$dateFrom 00:00:00'");
            $db->setQuery($query);
            $data = $db->loadObject();
            return $data;
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }


}
