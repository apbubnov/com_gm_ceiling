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
        try{
            $jinput = JFactory::getApplication()->input;
            $id = $jinput->getInt('userId');
            $date = $jinput->get('date','','STRING');
            $dealerId = JFactory::getUser($id)->dealer_id;
            $filter = (!empty($date)) ? "DATE_FORMAT(a.date_time,'%Y-%m-%d') <= '$date' and" : "";
            $filter .= "(a.manager_id = $id or a.manager_id = $dealerId)";
            $model = Gm_ceilingHelpersGm_ceiling::getModel('callback');
            $result = $model->gettingData($filter);
            die(json_encode($result));
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
    }
}