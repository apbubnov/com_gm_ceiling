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
 * Client controller class.
 *
 * @since  1.6
 */
class Gm_ceilingControllerClient extends JControllerLegacy
{
	/**
	 * Method to check out an item for editing and redirect to the edit form.
	 *
	 * @return void
	 *
	 * @since    1.6
	 */
	public function edit()
	{
		try
		{
			$app = JFactory::getApplication();

			// Get the previous edit id (if any) and the current edit id.
			$previousId = (int) $app->getUserState('com_gm_ceiling.edit.client.id');
			$editId     = $app->input->getInt('id', 0);

			// Set the user id for the user to edit in the session.
			$app->setUserState('com_gm_ceiling.edit.client.id', $editId);

			// Get the model.
			$model = $this->getModel('Client', 'Gm_ceilingModel');

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
			$this->setRedirect(JRoute::_('index.php?option=com_gm_ceiling&view=clientform&layout=edit', false));
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
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
		try
		{
			// Initialise variables.
			$app = JFactory::getApplication();

			// Checking if the user can remove object
			$user = JFactory::getUser();

			if ($user->authorise('core.edit', 'com_gm_ceiling') || $user->authorise('core.edit.state', 'com_gm_ceiling'))
			{
				$model = $this->getModel('Client', 'Gm_ceilingModel');

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
				$app->setUserState('com_gm_ceiling.edit.client.id', null);

				// Flush the data from the session.
				$app->setUserState('com_gm_ceiling.edit.client.data', null);

				// Redirect to the list screen.
				$this->setMessage(JText::_('COM_GM_CEILING_ITEM_SAVED_SUCCESSFULLY'));
				$menu = JFactory::getApplication()->getMenu();
				$item = $menu->getActive();

				if (!$item)
				{
					// If there isn't any menu item active, redirect to list view
					$this->setRedirect(JRoute::_('index.php?option=com_gm_ceiling&view=clients', false));
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
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

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
		try
		{
			// Initialise variables.
			$app = JFactory::getApplication();

			// Checking if the user can remove object
			$user = JFactory::getUser();

			if ($user->authorise('core.delete', 'com_gm_ceiling'))
			{
				$model = $this->getModel('Client', 'Gm_ceilingModel');

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
					$app->setUserState('com_gm_ceiling.edit.client.id', null);

					// Flush the data from the session.
					$app->setUserState('com_gm_ceiling.edit.client.data', null);

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
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
	}


	public function addBirthday()
	{
		try
		{
			$jinput = JFactory::getApplication()->input;
            $id_client = $jinput->get('id_client', '0', 'INT');
            $birthday = $jinput->get('birthday', '', 'STRING');

            $client_model = Gm_ceilingHelpersGm_ceiling::getModel('client');
            $result = $client_model->addBirthday($id_client, $birthday);

            die(json_encode($result));
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
	}

	public function addPhone()
	{
		try
		{
			$jinput = JFactory::getApplication()->input;
            $client_id = $jinput->get('client_id', null, 'INT');
            $phones[0] = $jinput->get('phone', null, 'STRING');

            $client_model = Gm_ceilingHelpersGm_ceiling::getModel('client_phones');
            $result = $client_model->save($client_id, $phones);

            die(json_encode($result));
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
	}
	public function removeEmail(){
		try
		{
			$jinput = JFactory::getApplication()->input;
            $client_id = $jinput->get('client_id', null, 'INT');
            $email = $jinput->get('email', null, 'STRING');

            $client_dop_model = Gm_ceilingHelpersGm_ceiling::getModel('clients_dop_contacts');
            $result = $client_dop_model->removeEmail($client_id, $email);
            die(json_encode($result));
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
	}
	public function create(){
		try
		{
			$jinput = JFactory::getApplication()->input;
            $user_id = $jinput->get('user_id', null, 'STRING');
            $phone = $jinput->get('phone','','STRING');
            if(empty($user_id)){
            	$user= JFactory::getUser();
            	$manager_id = $user->id;
            	$user_id = $user->dealer_id;
            }
            $client_model = Gm_ceilingHelpersGm_ceiling::getModel('client');
            $result = $client_model->create($user_id,$manager_id);
            if(!empty($phone)){
	            $client_phone_model = Gm_ceilingHelpersGm_ceiling::getModel('client_phones');
	            $client_phone_model->save($result,[$phone]);
	        }
            die(json_encode($result));
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }

	}

	public function checkingUser(){
		try
		{
			$jinput = JFactory::getApplication()->input;
            $phone = $jinput->get('phone', null, 'STRING');
            $phone = preg_replace('/[\(\)\-\+\s]/', '', $phone);
            $client_model = Gm_ceilingHelpersGm_ceiling::getModel('client_phones');
            $client = $client_model->getItemsByPhoneNumber($phone, 1);
            $user_model = Gm_ceilingHelpersGm_ceiling::getModel('users');
            if(!empty($client->id)){
				$user = $user_model->getUserByAssociatedClient($client->id);
				if(!empty($user->id)){
					$result = $user->id;
	            }
	            else{
	            	$result = 0;
	            }
            }
           	else{
           		$result = 0;
           	}
            
            die(json_encode($result));
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
	}

	public function delete_by_user(){
		try{
			$model = $this->getModel('ClientForm', 'Gm_ceilingModel');
			$jinput = JFactory::getApplication()->input;
            $client_id = $jinput->get('client_id', null, 'INT');
            $data['id'] = $client_id;
            $data['deleted_by_user'] = 1;
            $model->save($data);
            die(json_encode(true));
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
	}

	function update_info(){
		try{
			$jinput = JFactory::getApplication()->input;
            $client_id = $jinput->get('client_id', null, 'INT');
           	$phones = $jinput->get('phones', array(), 'ARRAY');
           	$emails = $jinput->get('emails', array(), 'ARRAY');
           	$deleted_phones = $jinput->get('deleted_phones', array(), 'ARRAY');
           	$deleted_emails = $jinput->get('deleted_emails', array(), 'ARRAY');
           	$new_name = $jinput->get('client_name','','STRING');
           	$client_dop_model = Gm_ceilingHelpersGm_ceiling::getModel('clients_dop_contacts');
           	$client_phones_model = Gm_ceilingHelpersGm_ceiling::getModel('client_phones');
           	$client_model = Gm_ceilingHelpersGm_ceiling::getModel('Client');
           	$new_phones =[];
           	$need_update_phones = [];
           	if(!empty($new_name)){
           		$client_model->updateClient($client_id,$new_name);
           	}
           	if(!empty($phones)){
           		foreach ($phones as $value) {
           			if(isset($value['old_phone'])){
           				$need_update_phones[$value['old_phone']] = preg_replace('/[\(\)\-\+\s]/', '', $value['phone']);
           			}
           			else{
           				array_push($new_phones, preg_replace('/[\(\)\-\+\s]/', '', $value['phone']));
           			}
           		}
           		if(!empty($new_phones)){
           			$client_phones_model->save($client_id,$new_phones);
           		}
           		if(!empty($need_update_phones)){
           			$client_phones_model->update($client_id,$need_update_phones);
           		}
           	}

           	if(!empty($emails)){
           		foreach ($emails as $value) {
           			if(isset($value['old_email'])){
           				$client_dop_model->updateEmail($client_id,$value['email'],$value['old_email']);
           			}
           			else{
           				$client_dop_model->save($client_id,1,$value['email']);
           			}
           		}
           	}

           	if(!empty($deleted_phones)){
           		foreach ($deleted_phones as $phone) {
           			$client_phones_model->deletePhone($client_id,preg_replace('/[\(\)\-\+\s]/', '', $phone));
           		}
           	}
           	
           	if(!empty($deleted_emails)){
           		foreach ($deleted_emails as $value) {
           			$client_dop_model->removeEmail($client_id, $value);
           		}
           	}
           	die(json_encode(true));
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
	}

	function updateManager(){
	    try{
	        $jinput = JFactory::getApplication()->input;
	        $clientId = $jinput->getInt('client_id');
	        $managerId = $jinput->getInt('manager_id');
            $client_model = Gm_ceilingHelpersGm_ceiling::getModel('Client');
            $client_model->updateClientManager($clientId,$managerId);
            die(json_encode(JFactory::getUser($managerId)->name, JSON_UNESCAPED_UNICODE));
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function saveSign(){
	    try{
	        $jinput = JFactory::getApplication()->input;
	        $clientId = $jinput->getInt('client_id');
	        $projectId = $jinput->getInt('project_id');
	        $birthday = $jinput->get('birthday', '', 'string');
	        $document = $jinput->get('document', '', 'string');
            $signImg = $jinput->get('sign_image', '', 'string');
            $mount = $jinput->get('mount', '', 'string');
            $filename = md5('client_'.$clientId.'_sign').'.svg';
            $mount_start = '';
            if(!empty($mount)){
                $mount = json_decode($mount);
                $mount_start = strtotime($mount[0]->time);
                if(count($mount) > 1){
                    foreach ($mount as $stage){
                        if(strtotime($stage->time) < $mount_start){
                            //throw new Exception("$stage->time < $mount_start");
                            $mount_start = strtotime($stage->time);
                        }
                    }
                }
                $mount_start = date('d.m.Y',$mount_start);
            }
            if(!empty($birthday)){
                $clientModel = Gm_ceilingHelpersGm_ceiling::getModel('client');
                $clientModel->addBirthday($clientId,$birthday);
            }
            if(!empty($document)){
                $clientDataModel = Gm_ceilingHelpersGm_ceiling::getModel('Client_data');
                $clientDataModel->saveDocument($clientId,$document);
            }
            file_put_contents($_SERVER['DOCUMENT_ROOT']."/images/client_signatures/$filename", base64_decode($signImg));
            Gm_ceilingHelpersPDF::createContract($projectId,$birthday,$document,$mount_start);
            die(json_encode(true));
	    }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
}
