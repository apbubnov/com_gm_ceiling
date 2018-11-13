<?php

/**
 * @version    CVS: 1.0.0
 * @package    Com_Gm_ceiling
 * @author     aleksander <nigga@hotmail.ru>
 * @copyright  2017 aleksander
 * @license    GNU General Public License версии 2 или более поздней; Смотрите LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;

/**
 * Canvas controller class.
 *
 * @since  1.6
 */
class Gm_ceilingControllerCanvasForm extends JControllerForm
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
	        // Initialise variables.
	        $app   = JFactory::getApplication();
	        $date = date("Y-m-d H:i:s");

	        // Get the user data.
	        $data = $app->input->get('canvas', array(), 'array');
	        $data['date'] = $date;

	        $model = $this->getModel('CanvasForm', 'Gm_ceilingModel');

	        $errorMessage = $model->edit($data);
	        if (empty($errorMessage)) $this->setMessage('Успешно изменено', 'success');
	        else $this->setMessage($errorMessage, 'error');

	        $link = $app->input->get('link', null, 'string');
	        $url  = (empty($link) ? 'index.php?option=com_gm_ceiling&view=components' : $link);
	        $this->setRedirect(JRoute::_($url, false));
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
	        echo "SAVE"; exit;
			// Check for request forgeries.
			JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

			// Initialise variables.
			$app   = JFactory::getApplication();
			$model = $this->getModel('CanvasForm', 'Gm_ceilingModel');

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
				$app->setUserState('com_gm_ceiling.edit.canvas.data', $jform);

				// Redirect back to the edit screen.
				$id = (int) $app->getUserState('com_gm_ceiling.edit.canvas.id');
				$this->setRedirect(JRoute::_('index.php?option=com_gm_ceiling&view=canvasform&layout=edit&id=' . $id, false));

				$this->redirect();
			}

			// Attempt to save the data.
			$return = $model->save($data);

			// Check for errors.
			if ($return === false)
			{
				// Save the data in the session.
				$app->setUserState('com_gm_ceiling.edit.canvas.data', $data);

				// Redirect back to the edit screen.
				$id = (int) $app->getUserState('com_gm_ceiling.edit.canvas.id');
				$this->setMessage(JText::sprintf('Save failed', $model->getError()), 'warning');
				$this->setRedirect(JRoute::_('index.php?option=com_gm_ceiling&view=canvasform&layout=edit&id=' . $id, false));
			}

			// Check in the profile.
			if ($return)
			{
				$model->checkin($return);
			}

			// Clear the profile id from the session.
			$app->setUserState('com_gm_ceiling.edit.canvas.id', null);

			// Redirect to the list screen.
			$this->setMessage(JText::_('COM_GM_CEILING_ITEM_SAVED_SUCCESSFULLY'));
			$menu = JFactory::getApplication()->getMenu();
			$item = $menu->getActive();
			$url  = (empty($item->link) ? 'index.php?option=com_gm_ceiling&view=canvases' : $item->link);
			$this->setRedirect(JRoute::_($url, false));

			// Flush the data from the session.
			$app->setUserState('com_gm_ceiling.edit.canvas.data', null);
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
			$editId = (int) $app->getUserState('com_gm_ceiling.edit.canvas.id');

			// Get the model.
			$model = $this->getModel('CanvasForm', 'Gm_ceilingModel');

			// Check in the item
			if ($editId)
			{
				$model->checkin($editId);
			}

			$menu = JFactory::getApplication()->getMenu();
			$item = $menu->getActive();
			$url  = (empty($item->link) ? 'index.php?option=com_gm_ceiling&view=canvases' : $item->link);
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
	        $model = $this->getModel('Canvases', 'Gm_ceilingModel');
	        $pk    = $app->input->getInt('id');

	        $errorMessage = $model->delete($pk);
	        if (empty($errorMessage)) die(json_encode(true));
	        else $this->setMessage($errorMessage, 'error');

	    }
	    catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
    }

    public function getCanvases()
    {
    	try
    	{
	        $filter = JFactory::getApplication()->input->get('filter', array(), 'array');
	        if ($filter != null)
	        {
	            $model = $this->getModel('Canvases', 'Gm_ceilingModel');
	            $result = $model->getCanvases($filter);
	            $user = JFactory::getUser($filter['user']['dealer']['id']);
	            $user->getCanvasesPrice();
                foreach ($result as $key => $item) {
                    $result[$key]->Price =
                        Gm_ceilingHelpersGm_ceiling::dealer_margin($result[$key]->Price, 0, $user->CanvasesPrice[$result[$key]->id]);
	            }
	            echo json_encode($result);
	        }
	        exit;
	    }
	    catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
    }
}
