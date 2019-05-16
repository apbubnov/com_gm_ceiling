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
            $calcsMounts = $jinput->get('calcs',"","STRING");
            $projectId = $jinput->getInt('projectId');
            $stage = $jinput->get("stage","","STRING");
            $refreshFlag = $jinput->getInt('refresh');
            if($stage == 2){
                $stageName = "Обагечивание";
            }
            elseif($stage == 3){
                $stageName = "Натяжка";

            }
            elseif ($stage == 4){
                $stageName = "Вставка";
            }
            $floorName =  $jinput->get("floorName","","STRING");
            //модели
            $projectsMountsModel = Gm_ceilingHelpersGm_ceiling::getModel('projects_mounts');
            $model = Gm_ceilingHelpersGm_ceiling::getModel('mountersSalary');

            $calcsMounts = json_decode($calcsMounts);
            $stagesNames = $projectsMountsModel->get_mount_types();
            foreach ($calcsMounts as $calc) {
                $model->delete($calc->mounter, $projectId,$stageName);
            }
            foreach ($calcsMounts as $calc){
                $note = $floorName." ".$calc->title." ".$stagesNames[$stage];
                $model->save($calc->mounter,$projectId,$calc->sum,$note);
            }

            $data['id'] = $projectId;
            if(!$refreshFlag) {
                $data['project_status'] = $stage + 25;
            }
            $projectModel = Gm_ceilingHelpersGm_ceiling::getModel('project');
            $projectModel->save($data);
            die(json_encode(true));
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function savePay(){
        try{
            $jinput = JFactory::getApplication()->input;
            $mounterId = $jinput->getInt('mounter_id');
            $builderId = $jinput->getInt('builder_id');
            $sum = $jinput->get('paid_sum','','STRING');
            $model = Gm_ceilingHelpersGm_ceiling::getModel('mountersSalary');
            $model->savePay($mounterId,$builderId,$sum);
            die(json_encode(true));
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function getData(){
        try{
            $jinput = JFactory::getApplication()->input;
            $builder_id = $jinput->get('builder_id',null,"INT");
            $model = Gm_ceilingHelpersGm_ceiling::getModel('mountersSalary');
            $result = $model->getData($builder_id);
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
            $projectsId = $jinput->get('ids',array(),"ARRAY");
            $builderId = $jinput->getInt('builder_id');
            $projectFilter = (!empty($projectsId)) ? "AND (ms.project_id IN(".implode(",",$projectsId).") or builder_id = $builderId)" : " and builder_id = $builderId";
            $model = Gm_ceilingHelpersGm_ceiling::getModel('mountersSalary');
            $result = $model->getDataById($mounterId,$projectFilter);
            die(json_encode($result));
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function updateSumMounter(){
        try{
            $jinput = JFactory::getApplication()->input;
            $mounterId = $jinput->getInt('mounterId');
            $projectId = $jinput->getInt('project_id');
            $newMounterId = $jinput->getInt('new_mounter');
            $sum = $jinput->get('sum','','STRING');
            $calcTitle = $jinput->get('calcTitle','','STRING');
            $model = Gm_ceilingHelpersGm_ceiling::getModel('mountersSalary');
            $result = $model->updateSumMounter($mounterId,$newMounterId,$projectId,$calcTitle,$sum);
            die(json_encode(true));
        }
        catch(Exception $e){
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function getMounterSalaryByBuilder(){
        try{
            $jinput = JFactory::getApplication()->input;
            $mounterId = $jinput->getInt('mounterId');
            $builderId = $jinput->getInt('builder_id');
            $model = Gm_ceilingHelpersGm_ceiling::getModel('mountersSalary');
            $result = $model->getMounterSalaryByBuilder($mounterId,$builderId);

            die(json_encode($result));
        }
        catch(Exception $e){
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
}