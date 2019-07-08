<?php
/**
 * Created by PhpStorm.
 * User: bubnov
 * Date: 03.04.2019
 * Time: 10:19
 */

defined('_JEXEC') or die;


class Gm_ceilingControllerAnalytic extends Gm_ceilingController
{
    function getData(){
        try {
            $jinput = JFactory::getApplication()->input;
            $user = JFactory::getUser();
            if($user->dealer_type != 8){
                $dealer_id = $user->dealer_id;
            }
            else{
                $dealer_id = $user->id;

            }
            $date1 = $jinput->get('c_date_from', '', 'STRING');
            $date2 = $jinput->get('c_date_to', '', 'STRING');
            $date1_d = $jinput->get('d_date_from', date(), 'STRING');
            $date2_d = $jinput->get('d_date_to', date(), 'STRING');
            $commonAnalyticModel = Gm_ceilingHelpersGm_ceiling::getModel('analytic_new');
            $detailedAnalyticModel = Gm_ceilingHelpersGm_ceiling::getModel('Analytic_detailed_new');
            $commonData = $commonAnalyticModel->getData($dealer_id,$date1,$date2);
            $detailedData = $detailedAnalyticModel->getData($dealer_id,$date1_d,$date2_d);
            $result = (object)array("commonData"=>$commonData,"detailedData"=>$detailedData);
            die(json_encode($result));
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
    }
}