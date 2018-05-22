<?php

/**
 * @version    CVS: 1.0.0
 * @package    Com_Gm_ceiling
 * @author     Mikhail  <vms@itctl.ru>
 * @copyright  2016 Mikhail 
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;

/**
 * Team controller class.
 *
 * @since  1.6
 */
class Gm_ceilingControllerTeamForm extends JControllerForm
{

	public function RegisterBrigade() {
		try
		{
			$user     = JFactory::getUser();
			$userId   = $user->get('id');
			$dealerId = $user->dealer_id;
			
			$jinput = JFactory::getApplication()->input;
			$name = $jinput->get('name', '', 'STRING');
			$phone = $jinput->get('phone', '', 'STRING');
			$phone = preg_replace('/[\(\)\-\+\s]/', '', $phone);
			$email = $jinput->get('email', '', 'STRING');

			//генератор пароля
			$chars="qazxswedcvfrtgbnhyujmkiolp1234567890QAZXSWEDCVFRTGBNHYUJMKIOLP";  
			$max=6; 
			$size=StrLen($chars)-1; 
			$password=null; 
			while($max--) {
				$password.=$chars[rand(0,$size)];
			}
			//-----------------------------------
			jimport('joomla.user.helper');

			$data = array(
				"name" => $name,
				"username" => $phone,
				"password" => $password,
				"email" => $email,
				"groups" => array(2, 11),
				"dealer_id" => $dealerId
			);

			try {
			    $user = new JUser;
				if (!$user->bind($data)) {
					throw new Exception($user->getError());
				}
				if (!$user->save()) {
					throw new Exception($user->getError());
				}

				$id_brigade = $user->id;

				// письмо
				$mailer = JFactory::getMailer();
				$config = JFactory::getConfig();
				$sender = array(
					$config->get('mailfrom'),
					$config->get('fromname')
				);
				$mailer->setSender($sender);
				$mailer->addRecipient($email);
				$body = "Здравствуйте. Вас зарегистрировали на сайте Гильдии Мастеров как монтажную бригаду. Данные учетной записи для каждого участника бригады: \n Логин: ".$phone." \n Пароль: ".$password;
				$mailer->setSubject('Регистрация на сайте Гильдии Мастеров');
				$mailer->setBody($body);
				$send = $mailer->Send();

				// сохранение монтажников
				$model = $this->getModel('Teamform', 'Gm_ceilingModel');

				$name_mount = $jinput->get('name-mount', array(), 'ARRAY');
	            $phone_mount = $jinput->get('phone-mount', array(), 'ARRAY');
				$phone_mount = preg_replace('/[\(\)\-\+\s]/', '', $phone_mount);

				$pasport = [];
				for ($i=0; $i<count($_FILES['pasport']['name']); $i++) {
					if (!empty($_FILES['pasport']['name'][$i])) {
						// Проверяем, что при загрузке не произошло ошибок
						if ($_FILES['pasport']['error'][$i] == 0) {
							// Если файл загружен успешно, то проверяем - графический ли он
							if (substr($_FILES['pasport']['type'][$i], 0, 5) == 'image') {
								// Читаем содержимое файла
								$pasport[$i] = file_get_contents($_FILES['pasport']['tmp_name'][$i]);
							}
						}
					}
				}

				$ids = [];
				// вызов функции сохранения монтажников
				for ($i=0; $i<count($name_mount); $i++) {
					if ($name_mount[$i] != null && $phone_mount[$i] != null) {
						$id = $model->SaveMounters($name_mount[$i], $phone_mount[$i], $pasport[$i]);
						$ids[$i] = $id;
					}
				}

				for ($i=0; $i<count($ids); $i++) {
					$model->SaveMountersMap($id_brigade, $ids[$i]);
				}
				

				// редирект после сохранения
				if ($dealerId == 1) {
					header('Location: /index.php?option=com_gm_ceiling&view=teams&type=gmchief');
				} else {
					header('Location: /index.php?option=com_gm_ceiling&view=teams&type=chief');				
				}

			} catch (Exception $e) {
				$result = json_encode(array(
					'error' => array(
						'msg' => $e->getMessage(),
						'code' => $e->getCode(),
					),
				));
				die($result);
			}
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
	public function edit($key = NULL, $urlVar = NULL)
	{
		try
		{
			$app = JFactory::getApplication();

			// Get the previous edit id (if any) and the current edit id.
			$previousId = (int) $app->getUserState('com_gm_ceiling.edit.team.id');
			$editId     = $app->input->getInt('id', 0);

			// Set the user id for the user to edit in the session.
			$app->setUserState('com_gm_ceiling.edit.team.id', $editId);

			// Get the model.
			$model = $this->getModel('TeamForm', 'Gm_ceilingModel');

			// Check out the item
			if ($editId)
			{
				$model->checkout($editId);
			}

			// Check in the previous user.
			if ($previousId)
			{
				$model->checkin($previousId);
			}

			// Redirect to the edit screen.
			$this->setRedirect(JRoute::_('index.php?option=com_gm_ceiling&view=teamform&layout=edit', false));
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
	        $user = JFactory::getUser();

			//        $jinput = JFactory::getApplication()->input;
			//        $name = $jinput->get('name', '', 'STRING');

	        $data = JFactory::getApplication()->input->get('jform', array(), 'array');

	        if(!empty($data)){
	            $db = JFactory::getDbo();
	            $query = $db->getQuery(true);
	            $query
	                ->insert($db->quoteName('#__gm_ceiling_groups'))
	                ->columns($db->quoteName('name').','.$db->quoteName('dealer_id'))
	                ->values($db->quote($data['name']).','.$user->id);
	            
	            $db->setQuery($query);
	            $result = $db->execute();
	        }

	        if(false):
			// Check for request forgeries.
			//JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

			// Initialise variables.
			$app   = JFactory::getApplication();
			$model = $this->getModel('TeamForm', 'Gm_ceilingModel');

			// Get the user data.
			$data = JFactory::getApplication()->input->get('jform', array(), 'array');

			// Validate the posted data.
			$form = $model->getForm();

			if (!$form)
			{
				throw new Exception($model->getError(), 500);
			}

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
				$app->setUserState('com_gm_ceiling.edit.team.data', $jform);

				// Redirect back to the edit screen.
				$id = (int) $app->getUserState('com_gm_ceiling.edit.team.id');
				$this->setRedirect(JRoute::_('index.php?option=com_gm_ceiling&view=teamform&layout=edit&id=' . $id, false));
			}

			// Attempt to save the data.
			$return = $model->save($data);

			// Check for errors.
			if ($return === false)
			{
				// Save the data in the session.
				$app->setUserState('com_gm_ceiling.edit.team.data', $data);

				// Redirect back to the edit screen.
				$id = (int) $app->getUserState('com_gm_ceiling.edit.team.id');
				$this->setMessage(JText::sprintf('Save failed', $model->getError()), 'warning');
				$this->setRedirect(JRoute::_('index.php?option=com_gm_ceiling&view=teamform&layout=edit&id=' . $id, false));
			}

			// Check in the profile.
			if ($return)
			{
				$model->checkin($return);
			}

			// Clear the profile id from the session.
			$app->setUserState('com_gm_ceiling.edit.team.id', null);

			// Redirect to the list screen.
			$this->setMessage(JText::_('COM_GM_CEILING_ITEM_SAVED_SUCCESSFULLY'));
			$menu = JFactory::getApplication()->getMenu();
			$item = $menu->getActive();
			$url  = (empty($item->link) ? 'index.php?option=com_gm_ceiling&view=teams' : $item->link);
			$this->setRedirect(JRoute::_($url, false));

			// Flush the data from the session.
			$app->setUserState('com_gm_ceiling.edit.team.data', null);
	        endif;
	        $this->setMessage(JText::_('COM_GM_CEILING_ITEM_SAVED_SUCCESSFULLY'));
	        $url  = (empty($item->link) ? 'index.php?option=com_gm_ceiling&view=teams' : $item->link);
	        $this->setRedirect(JRoute::_($url, false));
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
			$editId = (int) $app->getUserState('com_gm_ceiling.edit.team.id');

			// Get the model.
			$model = $this->getModel('TeamForm', 'Gm_ceilingModel');

			// Check in the item
			if ($editId)
			{
				$model->checkin($editId);
			}

			$menu = JFactory::getApplication()->getMenu();
			$item = $menu->getActive();
			$url  = (empty($item->link) ? 'index.php?option=com_gm_ceiling&view=teams' : $item->link);
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
     *
     * @since 1.6
	 */
	public function remove()
    {
    	try
    	{
	        $app   = JFactory::getApplication();
	        $model = $this->getModel('TeamForm', 'Gm_ceilingModel');
	        $pk    = $app->input->getInt('id');

	        // Attempt to save the data
	        try
	        {
	            $return = $model->delete($pk);

	            // Check in the profile
	            $model->checkin($return);

	            // Clear the profile id from the session.
	            $app->setUserState('com_gm_ceiling.edit.team.id', null);

	            $menu = $app->getMenu();
	            $item = $menu->getActive();
	            $url = (empty($item->link) ? 'index.php?option=com_gm_ceiling&view=teams' : $item->link);

	            // Redirect to the list screen
	            $this->setMessage(JText::_('COM_EXAMPLE_ITEM_DELETED_SUCCESSFULLY'));
	            $this->setRedirect(JRoute::_($url, false));

	            // Flush the data from the session.
	            $app->setUserState('com_gm_ceiling.edit.team.data', null);
	        }
	        catch (Exception $e)
	        {
	            $errorType = ($e->getCode() == '404') ? 'error' : 'warning';
	            $this->setMessage($e->getMessage(), $errorType);
	            $this->setRedirect('index.php?option=com_gm_ceiling&view=teams');
	        }
	    }
	    catch(Exception $e)
        {
          Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
    }
}
