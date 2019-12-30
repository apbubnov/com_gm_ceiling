<?php
/**
 * Created by PhpStorm.
 * User: bubnov
 * Date: 17.12.2019
 * Time: 15:35
 */
class Gm_ceilingControllerCity extends JControllerLegacy{
    function getData(){
        try{
            $cityModel = Gm_ceilingHelpersGm_ceiling::getModel('city');
            $result = $cityModel->getData();
            die(json_encode($result));
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
}
?>