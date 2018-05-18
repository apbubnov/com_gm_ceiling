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
class Gm_ceilingControllerClientForm extends JControllerForm
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
			$previousId = (int) $app->getUserState('com_gm_ceiling.edit.client.id');
			$editId     = $app->input->getInt('id', 0);

			// Set the user id for the user to edit in the session.
			$app->setUserState('com_gm_ceiling.edit.client.id', $editId);

			// Get the model.
			$model = $this->getModel('ClientForm', 'Gm_ceilingModel');

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
			$this->setRedirect(JRoute::_('index.php?option=com_gm_ceiling&view=clientform&layout=edit', false));
		}
		catch(Exception $e)
        {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

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
			$model = $this->getModel('ClientForm', 'Gm_ceilingModel');

			// Get the user data.
			$data = JFactory::getApplication()->input->get('jform', array(), 'array');
			
			$data['client_contacts'] = preg_replace('/[^\d]/', '', $data['client_contacts']);

			$data['client_name'] = mb_ereg_replace('[^А-ЯЁа-яёA-Za-z0-9\-\@\.\s]', '', $data['client_name']);
		    $data['client_name'] = str_replace(array("\r","\n"), '', $data['client_name']);
		    $data['client_name'] = mb_ereg_replace('[\s]+', ' ', $data['client_name']);
		    $data['client_name'] = trim($data['client_name']);

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
				$app->setUserState('com_gm_ceiling.edit.client.data', $jform);

				// Redirect back to the edit screen.
				$id = (int) $app->getUserState('com_gm_ceiling.edit.client.id');
				$this->setRedirect(JRoute::_('index.php?option=com_gm_ceiling&view=clientform&layout=edit&id=' . $id, false));
			}

			// Attempt to save the data.
			$return = $model->save($data);

			// Check for errors.
			if ($return === false)
			{
				// Save the data in the session.
				$app->setUserState('com_gm_ceiling.edit.client.data', $data);

				// Redirect back to the edit screen.
				$id = (int) $app->getUserState('com_gm_ceiling.edit.client.id');
				$this->setMessage(JText::sprintf('Save failed', $model->getError()), 'warning');
				$this->setRedirect(JRoute::_('index.php?option=com_gm_ceiling&view=clientform&layout=edit&id=' . $id, false));

			}
			elseif ($return == 'client_found')
			{	
				$this->setMessage('Клиент с таким номером существует!');
				$this->setRedirect(JRoute::_('index.php?option=com_gm_ceiling&view=clientform&layout=edit&id=' . $id, false));
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

				$jinput = JFactory::getApplication()->input;
				$type = $jinput->getString('type', NULL);
				
				//KM_CHANGED START
					$this->setMessage(JText::_('COM_GM_CEILING_ITEM_SAVED_SUCCESSFULLY'));
					$menu = JFactory::getApplication()->getMenu();
					$item = $menu->getActive();

					$url  = 'index.php?option=com_gm_ceiling&view=clientcard&id='.(int) $return;

					$this->setRedirect(JRoute::_($url, false));
				//KM_CHANGED END

				// Flush the data from the session.
				$app->setUserState('com_gm_ceiling.edit.client.data', null);
			}
		}
		catch(Exception $e)
        {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

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
			$editId = (int) $app->getUserState('com_gm_ceiling.edit.client.id');

			// Get the model.
			$model = $this->getModel('ClientForm', 'Gm_ceilingModel');

			/*// Check in the item
			if ($editId)
			{
				$model->checkin($editId);
			}*/

			$menu = JFactory::getApplication()->getMenu();
			$item = $menu->getActive();

			$jinput = JFactory::getApplication()->input;
			$type = $jinput->getString('type', NULL);
			$id = $jinput->getString('id', NULL);
			
			if($type === "manager") {
				$url  = 'index.php?option=com_gm_ceiling&view=clients&type='.$type;
			} else {
				$url  = 'index.php?option=com_gm_ceiling&task=mainpage';
			}
			$this->setRedirect(JRoute::_($url, false));
		}
		catch(Exception $e)
        {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

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
			$model = $this->getModel('ClientForm', 'Gm_ceilingModel');

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
				$app->setUserState('com_gm_ceiling.edit.client.data', $data);

				// Redirect back to the edit screen.
				$id = (int) $app->getUserState('com_gm_ceiling.edit.client.id');
				$this->setRedirect(JRoute::_('index.php?option=com_gm_ceiling&view=client&layout=edit&id=' . $id, false));
			}

			// Attempt to save the data.
			$return = $model->delete($data);

			// Check for errors.
			if ($return === false)
			{
				// Save the data in the session.
				$app->setUserState('com_gm_ceiling.edit.client.data', $data);

				// Redirect back to the edit screen.
				$id = (int) $app->getUserState('com_gm_ceiling.edit.client.id');
				$this->setMessage(JText::sprintf('Delete failed', $model->getError()), 'warning');
				$this->setRedirect(JRoute::_('index.php?option=com_gm_ceiling&view=client&layout=edit&id=' . $id, false));
			}

			/*// Check in the profile.
			if ($return)
			{
				$model->checkin($return);
			}*/

			// Clear the profile id from the session.
			$app->setUserState('com_gm_ceiling.edit.client.id', null);

			// Redirect to the list screen.
			$this->setMessage(JText::_('COM_GM_CEILING_ITEM_DELETED_SUCCESSFULLY'));
			$menu = JFactory::getApplication()->getMenu();
			$item = $menu->getActive();
			$url  = (empty($item->link) ? 'index.php?option=com_gm_ceiling&view=clients' : $item->link);
			$this->setRedirect(JRoute::_($url, false));

			// Flush the data from the session.
			$app->setUserState('com_gm_ceiling.edit.client.data', null);
		}
		catch(Exception $e)
        {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
	}

	public function pay() {
	    try {
            $JInput   = JFactory::getApplication()->input;

            $recoil_id = $JInput->getInt('dealer_id');
            $sum = $JInput->getFloat('pay_sum');
            $comment = $JInput->getString('pay_comment');

            $recoil_map_project = $this->getModel('recoil_map_project', 'Gm_ceilingModel');
            $recoil_map_project->insert($recoil_id, null, $sum, $comment);

            $this->setMessage("Средства успешно внесены!");
            $this->setRedirect(JRoute::_($_SERVER["HTTP_REFERER"], false));
        }
        catch(Exception $e)
        {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
    }
}
