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
 * Component controller class.
 *
 * @since  1.6
 */
class Gm_ceilingControllerComponentForm extends JControllerForm
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
	        $data = $app->input->get('component', array(), 'array');
	        $data['date'] = $date;

	        $model = $this->getModel('ComponentForm', 'Gm_ceilingModel');

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
			// Initialise variables.
			$app   = JFactory::getApplication();
	        $date = date("Y-m-d H:i:s");

	        // Get the user data.
	        $data = $app->input->get('component', array(), 'array');
	        $data['date'] = $date;

	        $model = $this->getModel('ComponentForm', 'Gm_ceilingModel');

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
			$editId = (int) $app->getUserState('com_gm_ceiling.edit.component.id');

			// Get the model.
			$model = $this->getModel('ComponentForm', 'Gm_ceilingModel');

			// Check in the item
			if ($editId)
			{
				$model->checkin($editId);
			}

			$menu = JFactory::getApplication()->getMenu();
			$item = $menu->getActive();
			$url  = (empty($item->link) ? 'index.php?option=com_gm_ceiling&view=components' : $item->link);
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
	        $model = $this->getModel('ComponentForm', 'Gm_ceilingModel');
	        $pk    = $app->input->getInt('id');

	        $errorMessage = $model->delete($pk);
	        if (empty($errorMessage)) $this->setMessage('Успешно удалено!', 'success');
	        else $this->setMessage($errorMessage, 'error');

	        $httpref = getenv("HTTP_REFERER");
	        $url  = (empty($httpref) ? 'index.php?option=com_gm_ceiling&view=components' : $httpref);
	        $this->setRedirect(JRoute::_($url, false));
	    }
	    catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
    }

    public function getComponents()
    {
    	try
    	{
	        $filter = JFactory::getApplication()->input->get('filter', array(), 'array');
	        if ($filter != null)
	        {
	            $model = $this->getModel('Components', 'Gm_ceilingModel');
	            $result = $model->getComponents($filter);
                $user = JFactory::getUser($filter['user']['dealer']['id']);
                $user->getComponentsPrice();
                foreach ($result as $key => $item) {
                    $result[$key]->Price =
                        Gm_ceilingHelpersGm_ceiling::dealer_margin($result[$key]->Price, 0, $user->CComponentsPrice[$result[$key]->id]);
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
