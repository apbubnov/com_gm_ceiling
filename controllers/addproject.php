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
			$data = JFactory::getApplication()->input->get('jform', [], 'array');
			// Checking if the user can remove objec
			$user = JFactory::getUser();
			$userId = $user->get('id');

            $dealer_info = Gm_ceilingHelpersGm_ceiling::getDealerInfo($user->dealer_id);
            if (empty($dealer_info)) {
                $gm_canvases_margin = 0;
                $gm_components_margin = 0;
                $gm_mounting_margin = 0;
                $dealer_canvases_margin = 0;
                $dealer_components_margin = 0;
                $dealer_mounting_margin = 0;
            } else {
                $gm_canvases_margin = $dealer_info->gm_canvases_margin;
                $gm_components_margin = $dealer_info->gm_components_margin;
                $gm_mounting_margin = $dealer_info->gm_mounting_margin;
                $dealer_canvases_margin = $dealer_info->dealer_canvases_margin;
                $dealer_components_margin =$dealer_info->dealer_components_margin;
                $dealer_mounting_margin = $dealer_info->dealer_mounting_margin;
            }
			$project_data = [];
			$client_data = [];


			if(empty($data['client_id']))// если новый клиент создаем нового клиента
			{
				$client_model = $this->getModel('ClientForm', 'Gm_ceilingModel');
				$client_data['created'] = date("Y-m-d");
				$client_data['client_name'] = $data['client_name'];
                $client_data['client_contacts'] = mb_ereg_replace('[^\d]', '', $data['client_contacts']);
                $client_data['dealer_id'] = $user->dealer_id;
				$client_id = $client_model->save($client_data);
                if ($client_id == 'client_found')
                {
                    $clientsphonesModel = Gm_ceilingHelpersGm_ceiling::getModel('client_phones');
                    $client = $clientsphonesModel->getItemsByPhoneNumber($client_data['client_contacts'], $user->dealer_id);
                    if(!empty($client)) {
                        $client_id = $client->id;
                    }
                    else{
                        throw new Exception("Существующий клиент не найден по номеру телефона");
                    }
                }
                if($client_id == 0){
                    throw new Exception("Запись на Гильдию мастеров!!!");
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
			

            $project_model = $this->getModel('ProjectForm', 'Gm_ceilingModel');

            $project_data['state'] = 1;
            $project_data['checked_out'] = null;
            $adress = $data['new_address'].", дом: ".$data['new_house'];
            if(!empty($data['new_bdq'])) $adress .= ", корпус: ".$data['new_bdq'];
            if(!empty($data['new_apartment'])) $adress .= ", квартира: ".$data['new_apartment'];
            if(!empty($data['new_porch'])) $adress .= ", подъезд: ".$data['new_porch'];
            if(!empty($data['new_floor'])) $adress .= ", этаж: ".$data['new_floor'];
            if(!empty($data['new_code'])) $adress .= ", код: ".$data['new_code'];

            $project_data['project_info'] = $adress;
            $project_data['project_status'] = 1;
            $project_data['project_calculation_date'] = $data['project_calculation_date'];
            $project_data['project_note'] = $data['project_note'];

            $project_data['project_calculator'] = $data['project_calculator'];

            $project_data['created'] = date("Y-m-d");
            $project_data['read_by_manager'] = $user->id;

            $dealer = JFactory::getUser($user->dealer_id);

            $project_data['project_discount'] = $dealer->discount;
            $project_data['gm_canvases_margin']   = $gm_canvases_margin;
            $project_data['gm_components_margin'] = $gm_components_margin;
            $project_data['gm_mounting_margin']   = $gm_mounting_margin;

            $project_data['dealer_canvases_margin']   = $dealer_canvases_margin;
            $project_data['dealer_components_margin'] = $dealer_components_margin;
            $project_data['dealer_mounting_margin'] = $dealer_mounting_margin;
            if(!empty($data['advt'])){
                $project_data['api_phone_id'] = $data['advt'];
            }
            Gm_ceilingHelpersGm_ceiling::notify($data, 0);
            $project_id = $project_model->save($project_data);
            if(!empty($data['measure_note'])){
               $projectModel  = Gm_ceilingHelpersGm_ceiling::getModel('project');
               $projectModel->saveNote($project_id,$data['measure_note'],2);
            }
            /*проверка если замер записан более чем  через 2 дня, поставить звонок уточнить актуальность замера */

            $today = new DateTime(date('Y-m-d'));
            $measureDate = new DateTime($data['project_calculation_date']);
            $day_diff = date_diff($measureDate,$today);
            //throw new Exception(print_r($day_diff,true));
            if($day_diff->days >=2){
                $callbackDate = ($measureDate->sub(date_interval_create_from_date_string('1 days')))->format('Y-m-d');
                if(date('N',strtotime($callbackDate)) == 7){
                    $callbackDate = ($measureDate->sub(date_interval_create_from_date_string('2 days')))->format('Y-m-d');
                }
                if(date('N',strtotime($callbackDate)) == 6){
                    $callbackDate = ($measureDate->sub(date_interval_create_from_date_string('1 days')))->format('Y-m-d');
                }
                $callbackDate = $callbackDate.' 16:30:00';
                $callback_model = $this->getModel('callback', 'Gm_ceilingModel');
                $callback_model->save($callbackDate, "Уточнить актуальность замера", $project_data['client_id'], $user->id);
            }
            /*Запись в историю проектов*/
            $model_projectshistory = Gm_ceilingHelpersGm_ceiling::getModel('projectshistory');
            $model_projectshistory->save($project_id, 1);


            $this->setMessage(JText::_('COM_GM_CEILING_CALCULATION_RESERVED_SUCCESSFULLY'));
			$this->setRedirect(JRoute::_('/index.php?option=com_gm_ceiling&task=mainpage', false));
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
	}
}
