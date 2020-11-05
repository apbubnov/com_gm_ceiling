<?php
class Gm_ceilingControllerJobs extends JControllerLegacy{
    public function getData(){
        try{
            $jinput = JFactory::getApplication()->input;
            $id = $jinput->getInt('id');
            $jobsModel = Gm_ceilingHelpersGm_ceiling::getModel('jobs');
            die(json_encode($jobsModel->get($id)));
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
    }
}