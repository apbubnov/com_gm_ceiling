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
 * Project controller class.
 *
 * @since  1.6
 */
class Gm_ceilingControllerMounterscalendar extends JControllerLegacy {

	// смена статуса на прочитанный, отправка письма НМС
	public function ChangeStatus() {
		try {
		    $user = JFactory::getUser();
			$jinput = JFactory::getApplication()->input;
			$id = $jinput->get("id_calculation",null,'INT');
			
 			$model = $this->getModel('Mounterscalendar', 'Gm_ceilingModel');
			$model_request = $model->ChangeStatusOfRead($id);
			// письмо
			$emails = $model->AllNMSEmails();
			$DataOrder = $model->DataOrder($id);
			$mailer = JFactory::getMailer();
			$config = JFactory::getConfig();
			$sender = array(
				$config->get('mailfrom'),
				$config->get('fromname')
			);

			$mailer->setSender($sender);
			foreach ($emails as $value) {
				$mailer->addRecipient($value->email);
			}

			$body = "Здравствуйте.\n";
			$body .= "Проект №$id был прочитан Монтажной Бригадой $user->name.\n";
			$body .= "\n";
			if(!empty($DataOrder)){
			    $project_info = $DataOrder[0]->project_info;
                $body .= "Адреc: $project_info \n";
			    foreach($DataOrder as $mountData){
			        $mountDateTime = date('d.m.Y H:i',strtotime($mountData->project_mounting_date));
                    $body .= "Этап: $mountData->stage_name Бригада: $mountData->project_mounter_name Дата/Время: $mountDateTime \n";
                }
            }
			$mailer->setSubject('Новый статус монтажа');
			$mailer->setBody($body);
			$send = $mailer->Send();

			die(json_encode($model_request));
		} catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
		}
	}

	// получить все монтажи и выходные дни
	public function GetDataOfMounting() {
		try
		{
			$date = $_POST["date"];
			$id = $_POST["id"];
			
			$model = $this->getModel('Mounterscalendar', 'Gm_ceilingModel');
			$model_request = $model->GetDayMountingOfBrigade($id, $date);

			die(json_encode($model_request));
		}
		catch(Exception $e)
		{
			Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

		}
	}

	/**
	 * Method to check out an item for editing and redirect to the edit form.
	 *
	 * @return void
	 *
	 * @since    1.6
	 */
	 public function edit()
	 {
		 $app = JFactory::getApplication();
 
		 // Get the previous edit id (if any) and the current edit id.
		 $previousId = (int) $app->getUserState('com_gm_ceiling.edit.project.id');
		 $editId     = $app->input->getInt('id', 0);
 
		 // Set the user id for the user to edit in the session.
		 $app->setUserState('com_gm_ceiling.edit.project.id', $editId);
 
		 // Get the model.
		 $model = $this->getModel('Project', 'Gm_ceilingModel');
 
		 /*// Check out the item
		 if ($editId)
		 {
			 $model->checkout($editId);
		 }
 
		 // Check in the previous user.
		 if ($previousId && $previousId !== $editId)
		 {
			 $model->checkin($previousId);
		 }*/
 
		 // Redirect to the edit screen.
		 $this->setRedirect(JRoute::_('index.php?option=com_gm_ceiling&view=projectform&layout=edit', false));
	 }

	/**
	 * Method to save a user's profile data.
	 *
	 * @return    void
	 *
	 * @throws Exception
	 * @since    1.6
	 */
	public function publish()
	{
		// Initialise variables.
		$app = JFactory::getApplication();

		// Checking if the user can remove object
		$user = JFactory::getUser();

		if ($user->authorise('core.edit', 'com_gm_ceiling') || $user->authorise('core.edit.state', 'com_gm_ceiling'))
		{
			$model = $this->getModel('Project', 'Gm_ceilingModel');

			// Get the user data.
			$id    = $app->input->getInt('id');
			$state = $app->input->getInt('state');

			// Attempt to save the data.
			$return = $model->publish($id, $state);

			// Check for errors.
			if ($return === false)
			{
				$this->setMessage(JText::sprintf('Save failed: %s', $model->getError()), 'warning');
			}

			// Clear the profile id from the session.
			$app->setUserState('com_gm_ceiling.edit.project.id', null);

			// Flush the data from the session.
			$app->setUserState('com_gm_ceiling.edit.project.data', null);

			// Redirect to the list screen.
			$this->setMessage(JText::_('COM_GM_CEILING_ITEM_SAVED_SUCCESSFULLY'));
			$menu = JFactory::getApplication()->getMenu();
			$item = $menu->getActive();

			if (!$item)
			{
				// If there isn't any menu item active, redirect to list view
				$this->setRedirect(JRoute::_('index.php?option=com_gm_ceiling&view=projects', false));
			}
			else
			{
				$this->setRedirect(JRoute::_($item->link . $menuitemid, false));
			}
		}
		else
		{
			throw new Exception(500);
		}
	}

	/**
	 * Remove data
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	public function remove()
	{
		// Initialise variables.
		$app = JFactory::getApplication();

		// Checking if the user can remove object
		$user = JFactory::getUser();

		if ($user->authorise('core.delete', 'com_gm_ceiling'))
		{
			$model = $this->getModel('Project', 'Gm_ceilingModel');

			// Get the user data.
			$id = $app->input->getInt('id', 0);

			// Attempt to save the data.
			$return = $model->delete($id);

			// Check for errors.
			if ($return === false)
			{
				$this->setMessage(JText::sprintf('Delete failed', $model->getError()), 'warning');
			}
			else
			{
				/*// Check in the profile.
				if ($return)
				{
					$model->checkin($return);
				}*/

				// Clear the profile id from the session.
				$app->setUserState('com_gm_ceiling.edit.project.id', null);

				// Flush the data from the session.
				$app->setUserState('com_gm_ceiling.edit.project.data', null);

				$this->setMessage(JText::_('COM_GM_CEILING_ITEM_DELETED_SUCCESSFULLY'));
			}

			// Redirect to the list screen.
			$menu = JFactory::getApplication()->getMenu();
			$item = $menu->getActive();
			$this->setRedirect(JRoute::_($item->link, false));
		}
		else
		{
			throw new Exception(500);
		}
	}

}

