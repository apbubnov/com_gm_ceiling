<?php
/**
 * Created by PhpStorm.
 * User: bubnov
 * Date: 12.12.2018
 * Time: 11:31
 */
// No direct access.
defined('_JEXEC') or die;

/**
 * Calculations list controller class.
 *
 * @since  1.6
 */
class Gm_ceilingControllerCallback extends Gm_ceilingController
{
    function getData(){
        try {
            $user = JFactory::getUser();
            $dealerId = $user->dealer_id;
            if($user->dealer_type == 8){
                $dealerId = $user->id;
            }

            $groups = $user->get('groups');
            $userId = $user->id;
            $filter = '';

            $jinput = JFactory::getApplication()->input;
            $date = $jinput->get('date', '', 'STRING');
            $label_id = $jinput->get('label_id', 0, 'INT');

            $type = $jinput->get('type', 0, 'INT');
            if ($type == 1) {
                $filter = '(`p`.`project_status` IS NULL) AND ';
            } elseif ($type == 2) {
                $filter = '`p`.`project_status` = 3 AND ';
            }

            $filter .= (!empty($date)) ? "DATE_FORMAT(`a`.`date_time`,'%Y-%m-%d') <= '$date' AND " : "";
            $filter .= (!empty($label_id)) ? "`c`.`label_id` = $label_id AND " : "";
            if (in_array('35', $groups)) {
                $filter .= "(`c`.`dealer_id` = $dealerId or `us`.`dealer_id` = $dealerId)";
            } else {
                $filter .= "(`a`.`manager_id` = $userId or `a`.`manager_id` = $dealerId or `us`.`dealer_id` = $userId)";
            }
            
            $model = Gm_ceilingHelpersGm_ceiling::getModel('callback');
            $result = $model->gettingData($filter);
            die(json_encode($result));
        } catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
    }

    function getCallsAnalytic(){
        try{
            $jinput = JFactory::getApplication()->input;
            $dealerId = $jinput->get('dealerId',null,'INT');
            $dateFrom = $jinput->get('dateFrom',null,'STRING');
            $dateTo = $jinput->get('dateTo',null,'STRING');
            $callbackModel = Gm_ceilingHelpersGm_ceiling::getModel('callback');
            $data = $callbackModel->selectCallAnalytic($dealerId,$dateFrom,$dateTo);
            die (json_encode($data));
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
    }

    function getCallsHistory(){
        try{
            $jinput = JFactory::getApplication()->input;
            $dealerId = $jinput->get('dealerId',null,'INT');
            $status = $jinput->get('statusId',null,'INT');
            $dateFrom = $jinput->get('dateFrom',null,'STRING');
            $dateTo = $jinput->get('dateTo',null,'STRING');
            $callbackModel = Gm_ceilingHelpersGm_ceiling::getModel('callback');
            $data = $callbackModel->selectCallHistoryByStatus($status,$dealerId,$dateFrom,$dateTo);
            die (json_encode($data));
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
    }
}