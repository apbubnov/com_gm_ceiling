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
            $projects_arr = $jinput->get('projects', array(), 'ARRAY');
            $model = $this->getModel('Projects', 'Gm_ceilingModel');
            $projects_str = implode(',',$projects_arr);
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
}
