<?php
/**
 * @version    CVS: 1.0.0
 * @package    Com_Gm_ceiling
 * @author     Mikhail "Spectral Eye" Vinyukov <vms@itctl.ru>
 * @copyright  2016 Mikhail "Spectral Eye" Vinyukov
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

/**
 * Parts list controller class.
 *
 * @since  1.6
 */
class Gm_ceilingControllerAddProject extends Gm_ceilingController
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
	
	/**
	 * Method to save a user's profile data.
	 *
	 * @return void
	 *
	 * @throws Exception
	 * @since  1.6
	 */
	public function save($key = NULL, $urlVar = NULL)
	{
		try
		{
			// Check for request forgeries.
			////JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
			// Initialise variables.
			$app   = JFactory::getApplication();

			// Get the user data.
			$data = JFactory::getApplication()->input->get('jform', array(), 'array');

			if(empty($data['calculation_title'])) {
				$data['calculation_title'] = "Безымянный потолок";
			}	

			// Checking if the user can remove object
			
			$user = JFactory::getUser();
			$userId = $user->get('id');

			// Checking if the user can remove object
			//$user = JFactory::getUser();
			$dealer = JFactory::getUser($user->dealer_id);

			$dealer_info_model = $this->getModel('Dealer_info', 'Gm_ceilingModel');
			$gm_canvases_margin = $dealer_info_model->getMargin('gm_canvases_margin',$user->dealer_id);
			$gm_components_margin = $dealer_info_model->getMargin('gm_components_margin',$user->dealer_id);
			$gm_mounting_margin = $dealer_info_model->getMargin('gm_mounting_margin',$user->dealer_id);
			$dealer_canvases_margin = $dealer_info_model->getMargin('dealer_canvases_margin',$user->dealer_id);
			$dealer_components_margin = $dealer_info_model->getMargin('dealer_components_margin',$user->dealer_id);
			$dealer_mounting_margin = $dealer_info_model->getMargin('dealer_mounting_margin',$user->dealer_id);
			$project_model = $this->getModel('ProjectForm', 'Gm_ceilingModel');
			$project_data = array();
			$client_data = array();
			
			$client_bool = true;
			if($data['client_id']==0)// если новый клиент создаем нового клиента
			{
				$client_model = $this->getModel('ClientForm', 'Gm_ceilingModel');
				//$client_data['state'] = 1;
				$client_data['created'] = date("Y-m-d");
				$client_data['client_name'] = $data['client_name'];
				$client_data['client_contacts'] = preg_replace('/[\(\)\-\s]/', '', $data['client_contacts']);
				
				$groups = $user->get('groups');
				if (in_array("22", $groups) || in_array("21", $groups) || in_array("17", $groups) || in_array("12", $groups)) $client_data['dealer_id'] = $user->dealer_id;
				else if(in_array("14", $groups)) $client_data['dealer_id'] = $userId;
				//$client_data['manager_id'] = $user->id;
				
				$client_id = $client_model->save($client_data);
				if ($client_id == 'client_found')
				{
					$client_bool = false;
				}
				$project_data['client_id'] = $client_id;
			}
			else {
				//если существующий клиент помещаем в проект id этого клиента
				$project_data['client_id'] = $data['client_id'];

				$client = Gm_ceilingHelpersGm_ceiling::getModel('clientform');
				$dopinfo = $client->getInfo($data['client_id']);
				$data['client_name'] = $dopinfo->client_name;
				$data['client_contacts'] = $dopinfo->phone;

			}
			
			if ($client_bool)
			{
				$project_model = $this->getModel('ProjectForm', 'Gm_ceilingModel');
				
				$project_data['state'] = 1;
				$project_data['checked_out'] = null;
				$adress = $data['project_info'].", дом: ".$data['project_info_house'];
				if(!empty($data['project_info_bdq'])) $adress .= ", корпус: ".$data['project_info_bdq'];
				if(!empty($data['project_apartment'])) $adress .= ", квартира: ".$data['project_apartment'];
				if(!empty($data['project_info_porch'])) $adress .= ", подъезд: ".$data['project_info_porch'];
				if(!empty($data['project_info_floor'])) $adress .= ", этаж: ".$data['project_info_floor'];
				if(!empty($data['project_info_code'])) $adress .= ", код: ".$data['project_info_code'];
				//$adress = $data['project_info'].", дом: ".$data['project_info_house'].", корпус: ".$data['project_info_bdq'].", квартира: ".$data['project_apartment'].
				//", подъезд: ".$data['project_info_porch'].", этаж: ".$data['project_info_floor'].", код: ".$data['project_info_code'];
				$project_data['project_info'] = $adress;
				$project_data['project_status'] = 1;
				$project_data['project_calculation_date'] = $data['project_calculation_date'].' '.$data['project_calculation_daypart'];
				$project_data['project_note'] = $data['project_note'];
				$project_data['dealer_manager_note'] = $data['dealer_manager_note'];
				$project_data['dealer_id'] = $user->dealer_id;
				$project_data['project_calculator'] = $data['project_calculator'];
				if ($user->dealer_id == 1) {
					$project_data['who_calculate'] = 1;
				} else {
					$project_data['who_calculate'] = 0;
				}
				$project_data['created'] = date("Y-m-d");
				$project_data['read_by_manager'] = $user->dealer_id;

				$dealer = JFactory::getUser($user->dealer_id);
				
				$project_data['project_discount'] = $dealer->discount;
				$project_data['gm_canvases_margin']   = $gm_canvases_margin;
				$project_data['gm_components_margin'] = $gm_components_margin;
				$project_data['gm_mounting_margin']   = $gm_mounting_margin;

				$project_data['dealer_canvases_margin']   = $dealer_canvases_margin;//$dealer->dealer_canvases_margin;
				$project_data['dealer_components_margin'] = $dealer_components_margin;
				$project_data['dealer_mounting_margin']   = $dealer_mounting_margin;

				Gm_ceilingHelpersGm_ceiling::notify($data, 0);

				$project_id = $project_model->save($project_data);
				
				// Redirect to the list screen.
				//KM_CHANGED START
				$this->setMessage(JText::_('COM_GM_CEILING_CALCULATION_RESERVED_SUCCESSFULLY'));
				$menu = JFactory::getApplication()->getMenu();
				$item = $menu->getActive();
			}
			else
			{
				$this->setMessage('Клиент с таким номером существует!');
			}
			$url  = 'index.php?option=com_gm_ceiling&task=mainpage';
			$this->setRedirect(JRoute::_($url, false));
			//KM_CHANGED END
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
	}
}
