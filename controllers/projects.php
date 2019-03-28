<?php
/**
 * @version    CVS: 0.1.7
 * @package    Com_Gm_ceiling
 * @author     SpectralEye <Xander@spectraleye.ru>
 * @copyright  2016 SpectralEye
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

/**
 * Projects list controller class.
 *
 * @since  1.6
 */
class Gm_ceilingControllerProjects extends Gm_ceilingController
{
	/**
	 * Proxy for getModel.
	 *
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional
	 * @param   array   $config  Configuration array for model. Optional
	 *
	 * @return object	The model
	 *
	 * @since	1.6
	 */
	public function &getModel($name = 'Projects', $prefix = 'Gm_ceilingModel', $config = array())
	{
		try {
			$model = parent::getModel($name, $prefix, array('ignore_request' => true));
			
			return $model;
		}
		catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
	}

    public function deleteEmptyProject()
    {
        try {
            $jinput = JFactory::getApplication()->input;
            $client_id = $jinput->get('client_id', 1, 'INT');
            $user = JFactory::getUser();
            $model = $this->getModel('Projects', 'Gm_ceilingModel');
            if($user->dealer_type == 1) {
                $model->deleteEmptyProject($client_id);
            }
            die(true);
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
    }

    function getProjectsInfo(){
    	try {
            $jinput = JFactory::getApplication()->input;
            $projects_str = $jinput->get('projects', '', 'STRING');
            $model = $this->getModel('Projects', 'Gm_ceilingModel');
            $result = $model->getInfoDealersAnalytic($projects_str);
            die(json_encode($result));
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
    }

    function getUnpaidProjects(){
	    try{
	        $jinput = JFactory::getApplication()->input;
	        $dealer_id = $jinput->get("dealer_id",null,"INT");
            $model = $this->getModel('Projects', 'Gm_ceilingModel');
            $result = $model->getUnpaidProjects($dealer_id);
            die(json_encode($result));
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function duplicate(){
	    try{
            $jinput = JFactory::getApplication()->input;
            $projectsModel = Gm_ceilingHelpersGm_ceiling::getModel('projects');
            $calcModel = Gm_ceilingHelpersGm_ceiling::getModel('calculation');
            $calculationsModel = Gm_ceilingHelpersGm_ceiling::getModel('calculations');
            $calcsMountModel = Gm_ceilingHelpersGm_ceiling::getModel('calcs_mount');
            $ids = $jinput->get('clients',array(),'ARRAY');
            $fromId = $jinput->get('idFrom',null,'INT');
            $fromProjects = $projectsModel->getClientsProjects($fromId);
            $toDupProjects = [];
            foreach ($ids as $id){
                foreach ($projectsModel->getClientsProjects($id) as $value)
                    array_push($toDupProjects,$value);
            }
            foreach ($toDupProjects as $dupProj) {
                $calculationsModel->deleteAllByProjectId($dupProj->id);
            }
            foreach ($fromProjects as $proj){
                $calcsId = $calculationsModel->getIdsByProjectId($proj->id);
                foreach ($calcsId as $calcId){
                    $calcData = $calcModel->new_getData($calcId->id);
                    unset($calcData->id);
                    $calcData = get_object_vars($calcData);
                    $mountData = $calcsMountModel->getData($calcId->id);
                    $calcData['mountData'] = $mountData;
                    foreach ($toDupProjects as $dupProj){
                        if($dupProj->project_info == $proj->project_info){
                            $calcData['project_id'] = $dupProj->id;
                            $newCalcId = $calcModel->duplicate($calcData);
                            $oldFileName = md5('calculation_sketch'.$calcId->id);
                            $oldImage = $_SERVER['DOCUMENT_ROOT']."/calculation_images/$oldFileName.svg";
                            $newFileName = md5('calculation_sketch'.$newCalcId);
                            $newImage =  $_SERVER['DOCUMENT_ROOT']."/calculation_images/$newFileName.svg";
                            copy($oldImage, $newImage);
                            //раскрой
                            $oldCutFileName = md5('cut_sketch'.$calcId->id);
                            $oldCutImage = $_SERVER['DOCUMENT_ROOT']."/cut_images/$oldCutFileName.svg";
                            $newCutFileName = md5('cut_sketch'.$newCalcId);
                            $newCutImage =  $_SERVER['DOCUMENT_ROOT']."/cut_images/$newCutFileName.svg";
                            copy($oldCutImage, $newCutImage);
                        }
                    }
                }
            }

            die(json_encode(true));
        } catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function getProjectsForBuh($dateFrom, $dateTo) {
        try {
            if (empty($dateFrom) && empty($dateTo)) {
                $jinput = JFactory::getApplication()->input;
                $dateFrom = $jinput->get('dateFrom', '0000-00-00', 'STRING');
                $dateTo = $jinput->get('dateTo', '0000-00-00', 'STRING');
            }
            $projectsModel = Gm_ceilingHelpersGm_ceiling::getModel('projects');
            $result = $projectsModel->getProjectsForBuh($dateFrom, $dateTo);
            die(json_encode($result));
        } catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function getMeasures(){
	    try{
            $jinput = JFactory::getApplication()->input;
            $dateFrom = $jinput->get('dateFrom', date('Y-m-d'), 'STRING');
            $dateTo = $jinput->get('dateTo', date('Y-m-d'), 'STRING');
            $type = $jinput->get('type', '', 'STRING');
            $subtype = $jinput->get('subtype','', 'STRING');
            $projectsModel = Gm_ceilingHelpersGm_ceiling::getModel('projects');
            $result = $projectsModel->getProjectsData($type,$subtype,$dateFrom,$dateTo);
            foreach ($result as $key=>$value){
                $result[$key]->note = Gm_ceilingHelpersGm_ceiling::getProjectNotes($value->id,2);
            }
            die(json_encode($result));
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
}
