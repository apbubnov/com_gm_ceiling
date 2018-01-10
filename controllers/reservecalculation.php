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
class Gm_ceilingControllerReserveCalculation extends Gm_ceilingController
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
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
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
			//JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

			// Initialise variables.
			$app   = JFactory::getApplication();

			// Get the user data.
			$jinput = JFactory::getApplication()->input;
			$data = $jinput->get('jform', array(), 'array');
			$type = $jinput->get('type', '', 'string');
			// Checking if the user can remove object
			$user = JFactory::getUser();
			$dealer = JFactory::getUser($user->dealer_id);
			$project_model = $this->getModel('ProjectForm', 'Gm_ceilingModel');
			$project_data = array();
			$adress = $data['project_info'].", дом: ".$data['project_info_house'];
			if(!empty($data['project_info_bdq'])) $adress .= ", корпус: ".$data['project_info_bdq'];
			if(!empty($data['project_apartment'])) $adress .= ", квартира: ".$data['project_apartment'];
			if(!empty($data['project_info_porch'])) $adress .= ", подъезд: ".$data['project_info_porch'];
			if(!empty($data['project_info_floor'])) $adress .= ", этаж: ".$data['project_info_floor'];
			if(!empty($data['project_info_code'])) $adress .= ", код: ".$data['project_info_code'];
			if($data['client_id']==0)// если новый клиент создаем нового клиента
			{
				$client_model = $this->getModel('ClientForm', 'Gm_ceilingModel');
				$client_data['state'] = 1;
				$client_data['created'] = date("Y-m-d");
				$client_data['client_name'] = $data['client_name'];
				$client_data['client_contacts'] = preg_replace('/[\(\)\-\s]/', '', $data['client_contacts']);
				$client_data['owner'] = $user->dealer_id;
				$client_data['manager_id'] = $user->id;
				$client_id = $client_model->save($client_data);
				$project_data['client_id'] = $client_id;
			}
			else {
				//если существующий клиент помещаем в проект id этого клиента
				$project_data['client_id'] = $data['client_id'];
			}
			
			$project_data['state'] = 1;
			
			$project_data['project_info'] = $adress;
			$project_data['project_status'] = 1;
			$jdate = $data['project_calculation_date'];
			$project_data['project_calculation_date'] = $jdate." ".$data['project_calculation_daypart'];
			$project_data['project_mounting_date'] = "0000-00-00";
			$project_data['project_mounting_daypart'] = "00:00:00";
			$project_data['project_note'] = $data['project_note'];
			$project_data['dealer_id'] = $user->dealer_id;
			$project_data['project_calculator'] = $data['project_calculator'];
			$gauger_dealer_id = $project_model->WhatDealerGauger($project_data['project_calculator']);
			if ($gauger_dealer_id[0]->dealer_id == 1) {
				$project_data['who_calculate'] = 1;
			}
			else  {
				$project_data['who_calculate'] = 0;
			}
			$project_data['created'] = date("Y-m-d");
			$project_data['project_discount'] = $dealer->discount;
			$project_data['gm_canvases_margin']   = $dealer->gm_canvases_margin;
			$project_data['gm_components_margin'] = $dealer->gm_components_margin;
			$project_data['gm_mounting_margin']   = $dealer->gm_mounting_margin;
			
			$project_data['dealer_canvases_margin']   = $dealer->dealer_canvases_margin;
			$project_data['dealer_components_margin'] = $dealer->dealer_components_margin;
			$project_data['dealer_mounting_margin']   = $dealer->dealer_mounting_margin;
			
			$project_id = $project_model->save($project_data);
			
			// Redirect to the list screen.
			//KM_CHANGED START
			$this->setMessage(JText::_('COM_GM_CEILING_CALCULATION_RESERVED_SUCCESSFULLY'));
			$menu = JFactory::getApplication()->getMenu();
			$item = $menu->getActive();
			if($user->dealer_type==0){
				if($type === "manager") {
					$url  = 'index.php?option=com_gm_ceiling&view=mainpage&type=managermainpage';
				} elseif($type === "gmmanager") {
					$url  = 'index.php?option=com_gm_ceiling&view=mainpage&type=gmmanagermainpage';
				} else {
					$url  = 'index.php?option=com_gm_ceiling&task=mainpage';
				}
			}
			else $url  = 'index.php?option=com_gm_ceiling&task=mainpage';
			$this->setRedirect(JRoute::_($url, false));
			//KM_CHANGED END
		}
		catch(Exception $e)
        {
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }
	}
}
