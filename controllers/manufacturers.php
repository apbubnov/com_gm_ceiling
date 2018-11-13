<?php
/**
 * Created by PhpStorm.
 * User: bubnov
 * Date: 13.11.2018
 * Time: 10:27
 */
class Gm_ceilingControllerManufacturers extends JControllerLegacy
{
    public function &getModel($name = 'Canvases_manufacturers', $prefix = 'Gm_ceilingModel', $config = array())
    {
        try
        {
            $model = parent::getModel($name, $prefix, array('ignore_request' => true));

            return $model;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
    }

    function getData(){
        try{
            $model = $this->getModel();
            $result = $model->getData();
            die(json_encode($result));
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
    }
}