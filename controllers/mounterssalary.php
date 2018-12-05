<?php
/**
 * Created by PhpStorm.
 * User: bubnov
 * Date: 05.12.2018
 * Time: 9:40
 */
// No direct access
defined('_JEXEC') or die;
class Gm_ceilingControllerMountersSalary extends JControllerLegacy {
    function save(){
        try{
            $jinput = JFactory::getApplication()->input;
            $mounterId = $jinput->getInt('mounterId');
            $projectId = $jinput->getInt('projectId');
            $sum = $jinput->get("sum","","STRING");
            $stage = $jinput->get("stage","","STRING");
            $projectsMountsModel = Gm_ceilingHelpersGm_ceiling::getModel('projects_mounts');
            $stagesNames = $projectsMountsModel->get_mount_types();
            $note = $stagesNames[$stage];
            $model = Gm_ceilingHelpersGm_ceiling::getModel('mountersSalary');
            $model->save($mounterId,$projectId,$sum,$note);

            $data['id'] = $projectId;
            $data['project_status'] =  $stage + 25;
            $projectModel = Gm_ceilingHelpersGm_ceiling::getModel('project');
            $projectModel->save($data);
            die(json_encode(true));
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function getData(){
        try{
            $model = Gm_ceilingHelpersGm_ceiling::getModel('mountersSalary');
            $result = $model->getData();
            die(json_encode($result));
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
}