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
 * Calculations list controller class.
 *
 * @since  1.6
 */
class Gm_ceilingControllerCalculations extends Gm_ceilingController
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
	public function &getModel($name = 'Calculations', $prefix = 'Gm_ceilingModel', $config = array())
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

	public function GetBusyMounters() {
		try
		{
			$date = $_POST["date"];
			$dealer = $_POST["dealer"];
	        
			$model = Gm_ceilingHelpersGm_ceiling::getModel('calculations');
			$mounters = $model->FindBusyMounters($date, $date, $dealer);

			die(json_encode($mounters));
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
	}

	public function GetBusyGauger() {
		try
		{
			$date = $_POST["date"];
			$dealer = $_POST["dealer"];
	        
			$model = Gm_ceilingHelpersGm_ceiling::getModel('calculations');
			$gauger = $model->FindBusyGauger($date, $date, $dealer);

			die(json_encode($gauger));
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}
	function getQuadrature(){
		try{
			$jinput = JFactory::getApplication()->input;
			$date1 = $jinput->get("date1","","STRING");
			$date2 = $jinput->get("date2","","STRING");
			$select_type = $jinput->get("type",null,"INT");
			$model = Gm_ceilingHelpersGm_ceiling::getModel('Analytic_Dealers');
			$result = $model->calculateQuadratureByPeriod($date1,$date2,$select_type);
			die(json_encode($result));
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

	function duplicate(){
	    try{
            $jinput = JFactory::getApplication()->input;
            $projectId = $jinput->get('project_id',null,'INT');
            $calcs = $jinput->get('calcs',array(),'ARRAY');
            $need_new = $jinput->get('need_new',0,'INT');
            if(!empty($calcs)){
                $projectModel = Gm_ceilingHelpersGm_ceiling::getModel('project');
                $calculationModel = Gm_ceilingHelpersGm_ceiling::getModel('calculation');
                $calcsMountModel = Gm_ceilingHelpersGm_ceiling::getModel('calcs_mount');
                if($need_new){
                    $project_data = get_object_vars($projectModel->new_getProjectItems($projectId));
                    unset($project_data['id']);
                    $projectFormModel = Gm_ceilingHelpersGm_ceiling::getModel('projectform');
                    $projectId = $projectFormModel->save($project_data);
                }

                foreach ($calcs as $calcId){
                    $calcData = $calculationModel->new_getData($calcId);
                    unset($calcData->id);
                    $calcData = get_object_vars($calcData);
                    $mountData = $calcsMountModel->getData($calcId);
                    $calcData['mountData'] = $mountData;
                    $calcData['project_id'] = $projectId;
                    $newCalcId = $calculationModel->duplicate($calcData);
                    $oldFileName = md5('calculation_sketch'.$calcId);
                    $oldImage = $_SERVER['DOCUMENT_ROOT']."/calculation_images/$oldFileName.svg";
                    $newFileName = md5('calculation_sketch'.$newCalcId);
                    $newImage =  $_SERVER['DOCUMENT_ROOT']."/calculation_images/$newFileName.svg";
                    copy($oldImage, $newImage);
                    //раскрой
                    $oldCutFileName = md5('cut_sketch'.$calcId);
                    $oldCutImage = $_SERVER['DOCUMENT_ROOT']."/cut_images/$oldCutFileName.svg";
                    $newCutFileName = md5('cut_sketch'.$newCalcId);
                    $newCutImage =  $_SERVER['DOCUMENT_ROOT']."/cut_images/$newCutFileName.svg";
                    copy($oldCutImage, $newCutImage);
                }

            }
            die(json_encode($projectId));
        }
        catch(Exception $e){
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
}
