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
class Gm_ceilingControllerTeam extends JControllerLegacy {
	
	function GetProjectsFilter() {
		try
		{
			$datetime1 = $_POST['datetime1'];
			$datetime2 = $_POST['datetime2'];
			$id = $_POST['$id'];
			$model = Gm_ceilingHelpersGm_ceiling::getModel('Team');
			$projects = $model->GetProjects($id, $datetime1, $datetime2);
			die(json_encode($projects));
		}
		catch(Exception $e)
        {
          Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
	}

	function MoveBrigade() {
		try
		{
			$id_mounter = $_POST['id_mounter'];
			$id_brigade = $_POST['brigade'];
			$model = Gm_ceilingHelpersGm_ceiling::getModel('Team');
			$model->MoveBrigade($id_mounter, $id_brigade);
			$current_brigade = $_POST['current_brigade'];
			$mounters = $model->GetMounters($current_brigade);
			
			$json = [];
			$i = 0;
			if (!empty($mounters)) {
				$str = "[";
				foreach ($mounters as $value) {
					$json[$i] = ["id" => $value->id, "name" => $value->name, "phone" => $value->phone];
					$str .= substr(json_encode($json[$i]), 0, -1).",\"passport\":\"data:image/png;base64,".base64_encode($value->pasport)."\"},";
					$i++;
				}
				$str = substr($str, 0, -1)."]";
				//throw new Exception($str);
				die($str);
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
	/*public function edit()
	{
		$app = JFactory::getApplication();

		// Get the previous edit id (if any) and the current edit id.
		$previousId = (int) $app->getUserState('com_gm_ceiling.edit.team.id');
		$editId     = $app->input->getInt('id', 0);

		// Set the user id for the user to edit in the session.
		$app->setUserState('com_gm_ceiling.edit.team.id', $editId);

		// Get the model.
		$model = $this->getModel('Team', 'Gm_ceilingModel');

		// Check out the item
		if ($editId)
		{
			$model->checkout($editId);
		}

		// Check in the previous user.
		if ($previousId && $previousId !== $editId)
		{
			$model->checkin($previousId);
		}

		// Redirect to the edit screen.
		$this->setRedirect(JRoute::_('index.php?option=com_gm_ceiling&view=teamform&layout=edit', false));
	}

	/**
	 * Method to save a user's profile data.
	 *
	 * @return    void
	 *
	 * @throws Exception
	 * @since    1.6
	 */
	/*public function publish()
	{
		// Initialise variables.
		$app = JFactory::getApplication();

		// Checking if the user can remove object
		$user = JFactory::getUser();

		if ($user->authorise('core.edit', 'com_gm_ceiling') || $user->authorise('core.edit.state', 'com_gm_ceiling'))
		{
			$model = $this->getModel('Team', 'Gm_ceilingModel');

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
			$app->setUserState('com_gm_ceiling.edit.team.id', null);

			// Flush the data from the session.
			$app->setUserState('com_gm_ceiling.edit.team.data', null);

			// Redirect to the list screen.
			$this->setMessage(JText::_('COM_GM_CEILING_ITEM_SAVED_SUCCESSFULLY'));
			$menu = JFactory::getApplication()->getMenu();
			$item = $menu->getActive();

			if (!$item)
			{
				// If there isn't any menu item active, redirect to list view
				$this->setRedirect(JRoute::_('index.php?option=com_gm_ceiling&view=teams', false));
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
	/*public function remove()
	{
		// Initialise variables.
		$app = JFactory::getApplication();

		// Checking if the user can remove object
		$user = JFactory::getUser();

		if ($user->authorise('core.delete', 'com_gm_ceiling'))
		{
			$model = $this->getModel('Team', 'Gm_ceilingModel');

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
				// Check in the profile.
				if ($return)
				{
					$model->checkin($return);
				}

				// Clear the profile id from the session.
				$app->setUserState('com_gm_ceiling.edit.team.id', null);

				// Flush the data from the session.
				$app->setUserState('com_gm_ceiling.edit.team.data', null);

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
	}*/

	

}
