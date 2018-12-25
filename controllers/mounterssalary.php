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
            //данные
            $calcsMounts = $jinput->get('calcs',array(),"ARRAY");
            $projectId = $jinput->getInt('projectId');
            $stage = $jinput->get("stage","","STRING");
            //модели
            $projectsMountsModel = Gm_ceilingHelpersGm_ceiling::getModel('projects_mounts');
            $model = Gm_ceilingHelpersGm_ceiling::getModel('mountersSalary');

            $stagesNames = $projectsMountsModel->get_mount_types();
            foreach ($calcsMounts as $calc){
                $note = $calc['title']." ".$stagesNames[$stage];
                $model->save($calc['mounters'][0]['id'],$projectId,$calc['sum'],$note);
            }

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

    function getDataById(){
        try{
            $jinput = JFactory::getApplication()->input;
            $mounterId = $jinput->getInt('mounterId');
            $model = Gm_ceilingHelpersGm_ceiling::getModel('mountersSalary');
            $result = $model->getDataById($mounterId);
            die(json_encode($result));
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
}