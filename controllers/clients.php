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
 * Clients list controller class.
 *
 * @since  1.6
 */
class Gm_ceilingControllerClients extends Gm_ceilingController
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
	public function &getModel($name = 'Clients', $prefix = 'Gm_ceilingModel', $config = array())
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

	public function searchClients($search = '')
	{
		try
		{
            $jinput = JFactory::getApplication()->input;
            $search = $jinput->get('search_text', '', 'STRING');
            $model_clients = $this->getModel('clients', 'Gm_ceilingModel');
            $result = $model_clients->searchClients($search);
			die(json_encode($result));
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

	function createBuilderFloors(){
	    try{
	        $jinput = JFactory::getApplication()->input;
	        $floorCount = $jinput->get('floors',null,'INT');
	        $apartmentCount = $jinput->get('apartment',null,'INT');
	        $builderId = $jinput->get('builderId',null,'INT');
	        $model = Gm_ceilingHelpersGm_ceiling::getModel('clientForm');
	        $project_model = Gm_ceilingHelpersGm_ceiling::getModel('project');
	        $data = [];$project_data = [];
	        $data['dealer_id'] = $builderId;
	        for($i=0;$i<$floorCount;$i++){
	            $data['client_name'] = "Этаж ".($i+1);
	            $floorId = $model->save($data);
	            $project_data['client_id'] = $floorId;
                $project_data['project_status'] = 0;
	            for($j=0;$j<$apartmentCount;$j++){
                    $project_data['project_info'] = "Квартира ".($j+1);
                    $project_data['project_note'] = "Квартира ".($j+1);
                    $project_model->save($project_data);
                }

            }
            die(json_encode(true));
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function getInfoByFloors(){
	    try{
            $jinput = JFactory::getApplication()->input;
            $dealer_id = $jinput->getInt('dealerId');
            $stage = $jinput->getInt('stage');
            $model = Gm_ceilingHelpersGm_ceiling::getModel('clients');
            $result = $model->getClientsAndprojectsData($dealer_id,$stage);

            die(json_encode($result));
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function getCommonInfo(){
	    try{
            $jinput = JFactory::getApplication()->input;
            $dealer_id = $jinput->getInt('dealerId');
            $model = Gm_ceilingHelpersGm_ceiling::getModel('clients');
            $result = $model->getCommonData($dealer_id);
            die(json_encode($result));
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function saveNewLabel() {
        try {
            $jinput = JFactory::getApplication()->input;
            $label_id = $jinput->get('label_id', null, 'int');
            $color_code = $jinput->get('color_code', null, 'string');
            $title = $jinput->get('title', null, 'string');
            $dealer_id = JFactory::getUser()->dealer_id;
            $model = Gm_ceilingHelpersGm_ceiling::getModel('clients');
            $result = $model->saveNewLabel($label_id, $title, $color_code, $dealer_id);
            die(json_encode($result));
        } catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function saveClientLabel() {
        try {
            $jinput = JFactory::getApplication()->input;
            $label_id = $jinput->get('label_id', null, 'int');
            $client_id = $jinput->get('client_id', null, 'int');
            $dealer_id = JFactory::getUser()->dealer_id;
            if (empty($label_id) || empty($client_id) || empty($dealer_id)) {
                throw new Exception('Empty input data');
            }
            $model = Gm_ceilingHelpersGm_ceiling::getModel('clients');
            $result = $model->saveClientLabel($client_id, $label_id, $dealer_id);
            die(json_encode($result));
        } catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function deleteLabel() {
        try {
            $jinput = JFactory::getApplication()->input;
            $label_id = $jinput->get('label_id', null, 'int');
            $dealer_id = JFactory::getUser()->dealer_id;
            $model = Gm_ceilingHelpersGm_ceiling::getModel('clients');
            $result = $model->deleteLabel($label_id, $dealer_id);
            die(json_encode($result));
        } catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

}
