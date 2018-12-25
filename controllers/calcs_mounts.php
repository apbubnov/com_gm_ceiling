<?php
/**
 * Created by PhpStorm.
 * User: bubnov
 * Date: 20.12.2018
 * Time: 14:09
 */
defined('_JEXEC') or die;

class Gm_ceilingControllerCalcs_mounts extends JControllerLegacy{
    function updateMounter(){
        try {
            $jinput = JFactory::getApplication()->input;
            $calcId = $jinput->getInt('calcId');
            $calcsId = $jinput->get('calcsId',array(),'ARRAY');
            $mounterId = $jinput->getInt('mounterId');
            $stage = $jinput->getInt('stage');
            $model = Gm_ceilingHelpersGm_ceiling::getModel('Calcs_mount');
            $model->updateMounter($calcId,$stage,$mounterId,$calcsId);
            die(json_encode(JFactory::getUser($mounterId)));
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
}