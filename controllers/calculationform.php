<?php

/**
 * @version    CVS: 0.1.7
 * @package    Com_Gm_ceiling
 * @author     SpectralEye <Xander@spectraleye.ru>
 * @copyright  2016 SpectralEye
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;

/**
 * Calculation controller class.
 *
 * @since  1.6
 */
class Gm_ceilingControllerCalculationForm extends JControllerForm
{
	/**
	 * Method to check out an item for editing and redirect to the edit form.
	 *
	 * @return void
	 *
	 * @since    1.6
	 */
	public function edit($key = NULL, $urlVar = NULL)
	{
		try
		{
			$app = JFactory::getApplication();

			// Get the previous edit id (if any) and the current edit id.
			$previousId = (int) $app->getUserState('com_gm_ceiling.edit.calculation.id');
			$editId     = $app->input->getInt('id', 0);

			// Set the user id for the user to edit in the session.
			$app->setUserState('com_gm_ceiling.edit.calculation.id', $editId);

			// Get the model.
			$model = $this->getModel('CalculationForm', 'Gm_ceilingModel');


			/*// Check out the item
			if ($editId)
			{
				$model->checkout($editId);
			}

			// Check in the previous user.
			if ($previousId)
			{
				$model->checkin($previousId);
			}*/

			// Redirect to the edit screen.
			$this->setRedirect(JRoute::_('index.php?option=com_gm_ceiling&view=calculationform&layout=edit', false));
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
			JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

			// Initialise variables.
			$app   = JFactory::getApplication();
			$model = $this->getModel('CalculationForm', 'Gm_ceilingModel');

			// Get the user data.
			$data = JFactory::getApplication()->input->get('jform', array(), 'array');
			
			
			// Validate the posted data.
			$form = $model->getForm();

			/*if (!$form)
			{
				throw new Exception($model->getError(), 500);
			}*/

			// Validate the posted data.
			$data = $model->validate($form, $data);
			
			// Check for errors.
			if ($data === false)
			{
				// Get the validation messages.
				$errors = $model->getErrors();

				// Push up to three validation messages out to the user.
				for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++)
				{
					if ($errors[$i] instanceof Exception)
					{
						$app->enqueueMessage($errors[$i]->getMessage(), 'warning');
					}
					else
					{
						$app->enqueueMessage($errors[$i], 'warning');
					}
				}

				$input = $app->input;
				$jform = $input->get('jform', array(), 'ARRAY');

				// Save the data in the session.
				$app->setUserState('com_gm_ceiling.edit.calculation.data', $jform);

				// Redirect back to the edit screen.
				$id = (int) $app->getUserState('com_gm_ceiling.edit.calculation.id');
				$this->setRedirect(JRoute::_('index.php?option=com_gm_ceiling&view=calculationform&layout=edit&id=' . $id, false));
			}
			
			//KM_CHANGED START
			
			$jinput = JFactory::getApplication()->input;
			
			//Забиваем массив дополнительных комплектующих в одну переменную
			$extra_components_title = $jinput->get('extra_components_title', '', 'ARRAY');
			$extra_components_value = $jinput->get('extra_components_value', '', 'ARRAY');
			$extra_components = array();
			foreach($extra_components_title as $key => $title){
				if(!empty($title) && $extra_components_value[$key]) {
					$extra_components[] = array(
						'title' => $title,
						'value' => $extra_components_value[$key]
					);
				}
			}
			$data['extra_components'] = json_encode($extra_components, JSON_FORCE_OBJECT);
			
			//Забиваем массив дополнительных монтажных работ в одну переменную
			$extra_mounting_title = $jinput->get('extra_mounting_title', '', 'ARRAY');
			$extra_mounting_value = $jinput->get('extra_mounting_value', '', 'ARRAY');
			$extra_mounting = array();
			foreach($extra_mounting_title as $key => $title){
				if(!empty($title) && $extra_mounting_value[$key]) {
					$extra_mounting[] = array(
						'title' => $title,
						'value' => $extra_mounting_value[$key]
					);
				}
			}
			$data['extra_mounting'] = json_encode($extra_mounting, JSON_FORCE_OBJECT);
			
			//Забиваем массив люстр в одну переменную
			$n12_array = array();
			$n12_num = $jinput->get('n12_num', '0', 'INT');
			$n12_array['n12_num'] = $n12_num;
			for($i = 1; $i <= $n12_num; $i++) {
				
				
				$n12_array['n12_type'.$i] = $jinput->get('n12_type' . $i, '0', 'INT');
				$n12_array['n12_count'.$i] = $jinput->get('n12_count' . $i, '0', 'INT');
			}
			$data['n12'] = json_encode($n12_array, JSON_FORCE_OBJECT);


	/*
			//Забиваем массив светильников в одну переменную
			if(isset($data['n13_advanced'])) {
				if($data['n13_advanced'] && $n13_array['n13_count'.$i]!="") {
					$n13_array = array();
					$n13_num = $jinput->get('n13_num', '0', 'INT');
					$n13_array['n13_num'] = $n13_num;
					for($i = 1; $i <= $n13_num; $i++) {
						$n13_array['n13_ring'.$i] = $jinput->get('n13_ring' . $i, '0', 'INT');
						//$n13_array['n13_platform'.$i] = $jinput->get('n13_platform' . $i, '0', 'INT');
						$n13_array['n13_type'.$i] = $jinput->get('n13_type' . $i, '0', 'INT');
						$n13_array['n13_count'.$i] = $jinput->get('n13_count' . $i, '0', 'INT');
					}
					$data['n13'] = json_encode($n13_array, JSON_FORCE_OBJECT);
				}
			}

			//Забиваем массив обвода труб в одну переменную
			if(isset($data['n14_advanced'])) {
				if($data['n14_advanced']) {
					$n14_array = array();
					$n14_num = $jinput->get('n14_num', '0', 'INT');
					$n14_array['n14_num'] = $n14_num;
					for($i = 1; $i <= $n14_num; $i++) {
						$n14_array['n14_type'.$i] = $jinput->get('n14_type' . $i, '0', 'INT');
						$n14_array['n14_count'.$i] = $jinput->get('n14_count' . $i, '0', 'INT');
					}
					$data['n14'] = json_encode($n14_array, JSON_FORCE_OBJECT);
				}
			}
				
			if(isset($data['n22_advanced'])) { 
			
				if($data['n22_advanced']) {
					$n22_array = array();
					$n22_num = $jinput->get('n22_num', '0', 'INT');
					$n22_array['n22_num'] = $n22_num;
					for($i = 1; $i <= $n22_num; $i++) {
						$n22_array['n22_diam'.$i] = $jinput->get('n22_diam' . $i, '0', 'INT');
						$n22_array['n22_type'.$i] = $jinput->get('n22_type' . $i, '0', 'INT');
						$n22_array['n22_count'.$i] = $jinput->get('n22_count' . $i, '0', 'INT');
					}
					$data['n22'] = json_encode($n22_array, JSON_FORCE_OBJECT);
				}
			}
			
			if(isset($data['n23_advanced'])) {
				if($data['n23_advanced']) {
					$n23_array = array();
					$n23_num = $jinput->get('n23_num', '0', 'INT');
					$n23_array['n23_num'] = $n23_num;
					for($i = 1; $i <= $n23_num; $i++) {
						$n23_array['n23_type'.$i] = $jinput->get('n23_type' . $i, '0', 'INT');
						$n23_array['n23_count'.$i] = $jinput->get('n23_count' . $i, '0', 'INT');
					}
					$data['n23'] = json_encode($n23_array, JSON_FORCE_OBJECT);
				}
			}
	*/
			if(empty($data['calculation_title'])) {
				$data['calculation_title'] = "Безымянный потолок";
			}
			
			$times = array(
				0 => "09:00:00",
				1 => "10:00:00",
				2 => "11:00:00",
				3 => "12:00:00",
				4 => "13:00:00",
				5 => "14:00:00",
				6 => "15:00:00",
				7 => "16:00:00",
				8 => "17:00:00",
				9 => "18:00:00",
				10 => "19:00:00",
				11 => "20:00:00",
				12 => "21:00:00"
			);
			
			$times2 = array(
				0 => "10:00:00",
				1 => "11:00:00",
				2 => "12:00:00",
				3 => "13:00:00",
				4 => "14:00:00",
				5 => "15:00:00",
				6 => "16:00:00",
				7 => "17:00:00",
				8 => "18:00:00",
				9 => "19:00:00",
				10 => "20:00:00",
				11 => "21:00:00",
				12 => "22:00:00"
			);
			
			$user = JFactory::getUser();
			
			//Получаем объект дилера
			if(empty($data['owner'])) {
				$dealer = JFactory::getUser(2);
			} else {
				$dealer = JFactory::getUser(2);
			}
			if(empty($data['owner']))
				$data['owner'] = 2;
			
			
			//Подготовка данных клиента
			$client_model = $this->getModel('ClientForm', 'Gm_ceilingModel');
			$client_data = array();
			$client_data['state'] = 1;
			$client_data['created'] = date("d.m.Y");
			if(!empty($data['client_name']))
				$client_data['client_name'] = $data['client_name'];
			elseif(!empty($data['client_name-top']))
				$client_data['client_name'] = $data['client_name-top'];
			
			if($data['client_contacts'])
				$client_data['client_contacts'] = $data['client_contacts'];
			elseif($data['client_contacts-top'])
				$client_data['client_contacts'] = $data['client_contacts-top'];

			//Подготовка данных проекта
			$project_model = $this->getModel('ProjectForm', 'Gm_ceilingModel');
			$project_data = array();
			$project_data['state'] = 1;
			$project_data['project_discount'] = $dealer->discount;
			if(!empty($data['project_info']))
				$project_data['project_info'] = $data['project_info'];
			elseif(!empty($data['project_info-top']))
				$project_data['project_info'] = $data['project_info-top'];
			$project_data['project_mounting_date'] = "00.00.0000";
			$project_data['project_mounting_daypart'] = "0";
			$project_data['created'] = date("d.m.Y");
			
			//Если расчет пришел со страницы клиентского гостевого расчета
			if($data['type'] === "guest") {
				$client_data['owner'] = $data['owner'];
				$client_id = $client_model->save($client_data);				
				$project_data['client_id'] = $client_id;
				$project_data['project_status'] = 1;
				if(!empty($data['project_calculation_date']))
					$jdate = new JDate($data['project_calculation_date']);
				elseif(!empty($data['project_calculation_date-top']))
					$jdate = new JDate($data['project_calculation_date-top']);
				$project_data['project_calculation_date'] = $jdate->format('d.m.Y H:i');
				if(!empty($data['project_calculation_daypart'])){	
					$project_data['project_calculation_daypart'] = $data['project_calculation_daypart'];
					/*$project_data['project_calculation_from'] = $jdate->format('d.m.Y') . " " . $times[$data['project_calculation_daypart']];
					$project_data['project_calculation_to'] = $jdate->format('d.m.Y') . " " . $times2[$data['project_calculation_daypart']];
				*/
				}
				elseif(!empty($data['project_calculation_daypart-top'])){
					$project_data['project_calculation_daypart'] = $data['project_calculation_daypart-top'];
					//$project_data['project_calculation_from'] = $jdate->format('d.m.Y') . " " . $times[$data['project_calculation_daypart-top']];
					//$project_data['project_calculation_to'] = $jdate->format('d.m.Y') . " " . $times2[$data['project_calculation_daypart-top']];
				}
				$project_data['who_calculate'] = 1;
				$project_data['who_mounting'] = 1;
				if(!empty($data['project_note']))
					$project_data['project_note'] = $data['project_note'];
				elseif(!empty($data['project_note-top']))
					$project_data['project_note'] = $data['project_note-top'];
				$project_data['owner'] = $data['owner'];
				$project_data['gm_canvases_margin']   = $dealer->gm_canvases_margin;
				$project_data['gm_components_margin'] = $dealer->gm_components_margin;
				$project_data['gm_mounting_margin']   = $dealer->gm_mounting_margin;
				
				$project_data['dealer_canvases_margin']   = $dealer->dealer_canvases_margin;
				$project_data['dealer_components_margin'] = $dealer->dealer_components_margin;
				$project_data['dealer_mounting_margin']   = $dealer->dealer_mounting_margin;
				
				$project_id = $project_model->save($project_data);
				
				$data['project_id'] = $project_id;
				Gm_ceilingHelpersGm_ceiling::notify($data, 0);

			}

	        $new_discount =  $jinput->get('new_discount',-1, 'RAW');
	        if((!empty($new_discount) && $new_discount >= 0) && $new_discount != $project_data['project_discount']){
	            $db = JFactory::getDbo();
	            $query = $db->getQuery(true);
	            $fields = array(
	                $db->quoteName('project_discount'). ' = '.$db->quote($new_discount)
	            );
	            $conditions = array(
	                $db->quoteName('id').' = '.$data['project_id']
	            );
	            $query->update($db->quoteName('#__gm_ceiling_projects'))->set($fields)->where($conditions);
	            $db->setQuery($query);

	            $result = $db->execute();
	        }

	        //Автоматически назначаем транспортные расходы, если не прикреплен договор или если калькуляция не первая
			if($data['project_id'] <= 0) {
				if($data['transport'] < 1) {
					$data['transport'] = 1;
				}
			} else {
				$db= JFactory::getDBO();
				$query = 'SELECT `id` FROM `#__gm_ceiling_calculations` WHERE `project_id` = ' . (int) $data['project_id'];
				$db->setQuery($query);
				$calculations = $db->loadObjectList();
				if(count($calculations) > 0) {
					if($calculations[0]->id == $data['id']){
						if($data['transport'] < 1) {
							$data['transport'] = 1;
						}					
					}
				} else {
					if($data['transport'] < 1) {
						$data['transport'] = 1;
					}
				}
			}
			
			//KM_CHANGED END

			// Attempt to save the data.
			$return = $model->save($data);

			// Check for errors.
			if ($return === false)
			{
				// Save the data in the session.
				$app->setUserState('com_gm_ceiling.edit.calculation.data', $data);

				// Redirect back to the edit screen.
				$id = (int) $app->getUserState('com_gm_ceiling.edit.calculation.id');
				$this->setMessage(JText::sprintf('Save failed', $model->getError()), 'warning');
				$this->setRedirect(JRoute::_('index.php?option=com_gm_ceiling&view=calculationform&layout=edit&id=' . $id, false));
			}
			
			//KM_CHANGED START
			

			//Gm_ceilingHelpersGm_ceiling::calculate(1,$return, 1, 0);
			
			$tmp_filename = $data['sketch_name'];
			
			$filename = md5("calculation_sketch".$return);
			
			if(is_file($_SERVER['DOCUMENT_ROOT'] . "/tmp/".$tmp_filename.".png")) {
				rename($_SERVER['DOCUMENT_ROOT'] . "/tmp/".$tmp_filename.".png", $_SERVER['DOCUMENT_ROOT'] . "/calculation_images/".$filename.".png");
			}
			
			//KM_CHANGED END
			
			// Clear the profile id from the session.
			$app->setUserState('com_gm_ceiling.edit.calculation.id', null);
			
			// Redirect to the list screen.
			$menu = JFactory::getApplication()->getMenu();
			$item = $menu->getActive();
			if($data['type'] === "guest") {
				$this->setMessage('Вы записались на замер! Наш менеджер скоро свяжется с Вами.');
				$url = '/';
			}
			else {
				if($user->dealer_type==2){
					Gm_ceilingController::back_status($data['project_id'],1);
				}
				$url = 'index.php?option=com_gm_ceiling&view=project&type=calculator&subtype=calendar&id='.$data['project_id'];
			}
			
			$this->setRedirect(JRoute::_($url, false));

			// Flush the data from the session.
			$app->setUserState('com_gm_ceiling.edit.calculation.data', null);
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
	}

	/**
	 * Method to abort current operation
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	public function cancel($key = NULL)
	{
		try
		{
			$app = JFactory::getApplication();

			// Get the current edit id.
			$editId = (int) $app->getUserState('com_gm_ceiling.edit.calculation.id');

			// Get the model.
			$model = $this->getModel('CalculationForm', 'Gm_ceilingModel');

			/*// Check in the item
			if ($editId)
			{
				$model->checkin($editId);
			}*/

			$menu = JFactory::getApplication()->getMenu();
			$item = $menu->getActive();
			$url  = (empty($item->link) ? 'index.php?option=com_gm_ceiling&view=calculations' : $item->link);
			$this->setRedirect(JRoute::_($url, false));
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
	}

	/**
	 * Method to remove data
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	public function remove()
	{
		try
		{
			// Initialise variables.
			$app   = JFactory::getApplication();
			$model = $this->getModel('CalculationForm', 'Gm_ceilingModel');

			// Get the user data.
			$data       = array();
			$data['id'] = $app->input->getInt('id');

			// Check for errors.
			if (empty($data['id']))
			{
				// Get the validation messages.
				$errors = $model->getErrors();

				// Push up to three validation messages out to the user.
				for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++)
				{
					if ($errors[$i] instanceof Exception)
					{
						$app->enqueueMessage($errors[$i]->getMessage(), 'warning');
					}
					else
					{
						$app->enqueueMessage($errors[$i], 'warning');
					}
				}

				// Save the data in the session.
				$app->setUserState('com_gm_ceiling.edit.calculation.data', $data);

				// Redirect back to the edit screen.
				$id = (int) $app->getUserState('com_gm_ceiling.edit.calculation.id');
				$this->setRedirect(JRoute::_('index.php?option=com_gm_ceiling&view=calculation&layout=edit&id=' . $id, false));
			}

			// Attempt to save the data.
			$return = $model->delete($data);

			// Check for errors.
			if ($return === false)
			{
				// Save the data in the session.
				$app->setUserState('com_gm_ceiling.edit.calculation.data', $data);

				// Redirect back to the edit screen.
				$id = (int) $app->getUserState('com_gm_ceiling.edit.calculation.id');
				$this->setMessage(JText::sprintf('Delete failed', $model->getError()), 'warning');
				$this->setRedirect(JRoute::_('index.php?option=com_gm_ceiling&view=calculation&layout=edit&id=' . $id, false));
			}

			/*// Check in the profile.
			if ($return)
			{
				$model->checkin($return);
			}*/

			// Clear the profile id from the session.
			$app->setUserState('com_gm_ceiling.edit.calculation.id', null);

			// Redirect to the list screen.
			$this->setMessage(JText::_('COM_GM_CEILING_ITEM_DELETED_SUCCESSFULLY'));
			$menu = JFactory::getApplication()->getMenu();
			$item = $menu->getActive();
			$url  = (empty($item->link) ? 'index.php?option=com_gm_ceiling&view=calculations' : $item->link);
			$this->setRedirect(JRoute::_($url, false));

			// Flush the data from the session.
			$app->setUserState('com_gm_ceiling.edit.calculation.data', null);
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
	}

	public function removeClientByProjectId($proj_id = null)
	{
		try
		{
			$app   = JFactory::getApplication();

			if (empty($proj_id))
			{
				$jinput = $app->input;
				$proj_id = $jinput->get('proj_id', 0, 'INT');
			}

			$model_project = $this->getModel('Project', 'Gm_ceilingModel');
			$model_client = $this->getModel('Client', 'Gm_ceilingModel');

			$project = $model_project->getData($proj_id);
			$result = $model_client->delete($project->id_client);

			die(json_encode($result.' '.$project->id_client));
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
	}
}
