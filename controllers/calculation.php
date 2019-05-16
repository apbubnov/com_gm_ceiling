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
class Gm_ceilingControllerCalculation extends JControllerLegacy
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
			$previousId = (int) $app->getUserState('com_gm_ceiling.edit.calculation.id');
			$editId     = $app->input->getInt('id', 0);

			// Set the user id for the user to edit in the session.
			$app->setUserState('com_gm_ceiling.edit.calculation.id', $editId);

			// Get the model.
			$model = $this->getModel('Calculation', 'Gm_ceilingModel');

			// Check out the item
			/*if ($editId)
			{
				$model->checkout($editId);
			}

			// Check in the previous user.
			if ($previousId && $previousId !== $editId)
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
				$model = $this->getModel('Calculation', 'Gm_ceilingModel');

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
				$app->setUserState('com_gm_ceiling.edit.calculation.id', null);

				// Flush the data from the session.
				$app->setUserState('com_gm_ceiling.edit.calculation.data', null);

				// Redirect to the list screen.
				$this->setMessage(JText::_('COM_GM_CEILING_ITEM_SAVED_SUCCESSFULLY'));
				$menu = JFactory::getApplication()->getMenu();
				$item = $menu->getActive();

				if (!$item)
				{
					// If there isn't any menu item active, redirect to list view
					$this->setRedirect(JRoute::_('index.php?option=com_gm_ceiling&view=calculations', false));
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
				$model = $this->getModel('Calculation', 'Gm_ceilingModel');

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
					$app->setUserState('com_gm_ceiling.edit.calculation.id', null);

					// Flush the data from the session.
					$app->setUserState('com_gm_ceiling.edit.calculation.data', null);

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

	public function create_calculation()
	{
		try
		{
			$jinput = JFactory::getApplication()->input;
            $proj_id = $jinput->get('proj_id', null, 'INT');

            $calc_model = Gm_ceilingHelpersGm_ceiling::getModel('calculation');
            $result  = $calc_model->create_calculation($proj_id);
            die(json_encode($result));
		}
		catch(Exception $e)
        {
           Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
	}

	public function save_details(){
		try{
			$jinput = JFactory::getApplication()->input;
			$title = $jinput->get('title', "", 'STRING');
			$comment = $jinput->get('details', "", 'STRING');
			$manager_note = $jinput->get('manager_note',"","STRING");

			$calc_id  = $jinput->get('calc_id', "", 'INT');
			if((!empty($title) || !empty($comment) || !empty($manager_note)) && !empty($calc_id)){
				$db = JFactory::getDbo();
				$query = $db->getQuery(true);
				$query->update('`#__gm_ceiling_calculations`');
				if(!empty($title)){
					$query->set("`calculation_title`='$title'");
				}
				if(!empty($comment)){
					$query->set("`details`='$comment'");
				}
				if(!empty($manager_note)){
					$query->set("`manager_note` = '$manager_note'");
				}
				$query->where("`id`=$calc_id");
				$db->setQuery($query);
		        $db->execute();
	        }
		}
		catch(Exception $e)
        {
           Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
	}

	public function clearCalculation(){
		try{
			$jinput = JFactory::getApplication()->input;
			$id = $jinput->get('calc_id',0,'INT');
			$project_id = $jinput->get('project_id',1,'INT');
			$calc_model = Gm_ceilingHelpersGm_ceiling::getModel('calculation');
			$calc_model->delete($id);
			$calc_model->save($id,$project_id);
			die(json_encode(true));
		}
		catch(Exception $e)
        {
           Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
	}

	function delete(){
		try{
			$jinput = JFactory::getApplication()->input;
			$idCalc = $jinput->get('calc_id',null, 'INT');
			if(!empty($idCalc)){
				$model = Gm_ceilingHelpersGm_ceiling::getModel('calculations');	
				$result = $model->delete($idCalc);
				die(json_encode($result));
			}
			else{
				throw new Exception("Empty calculation id!");
			}
			
		}
		catch(Exception $e)
        {
           Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

	function recalcMount(){
	    try{
            $jinput = JFactory::getApplication()->input;
            $calcsId = $jinput->get('calcs',"", 'STRING');
            $calcsId = json_decode($calcsId);
            foreach ($calcsId as $id){
                Gm_ceilingHelpersGm_ceiling::calculate_mount(0,$id,null,null);
            }
            die(json_encode(true));
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function updateSum(){
	    try{
            $jinput = JFactory::getApplication()->input;
            $calcId = $jinput->get('calcId',"", 'STRING');
            $sum = $jinput->get('sum',"", 'STRING');
            $model = Gm_ceilingHelpersGm_ceiling::getModel('calculation');
            $result = $model->updateSum($calcId,$sum);
            die(json_encode($result));
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function set_ready_time(){
	    try{
	        $jinput = JFactory::getApplication()->input;
	        $data = json_decode($jinput->get("data","","STRING"));
	        if(!empty($data)){
                $model = Gm_ceilingHelpersGm_ceiling::getModel('calculation');
	            foreach ($data as $value){
                    $model->save_ready_time($value->calc_id,$value->ready_time);
                }
            }
            die(json_encode(true));
        }

        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function upload_img() {
	    try {
	        $jinput = JFactory::getApplication()->input;
	        $calc_id = $jinput->get('calc_id', 0, 'int');
	        if (empty($calc_id)) {
	        	throw new Exception('Empty calc_id!');
	        }
	        if (!is_dir('uploaded_calc_images/'.$calc_id)) {
	        	mkdir('uploaded_calc_images/'.$calc_id);
	        }

	        $dir = 'uploaded_calc_images/'.$calc_id.'/';
	        $urls = array();

	        foreach ($_FILES as $file) {
				$md5 = md5($calc_id.microtime().$file['name']);
		        if (move_uploaded_file($file['tmp_name'], $dir.$md5)) {
		            $urls[] = $dir.$md5;
		        } else {
		            throw new Exception('File not upload', 500);
		        }
		    }
	        
            die(json_encode($urls));
        } catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function delete_img() {
	    try {
	        $jinput = JFactory::getApplication()->input;
	        $path = $jinput->get('path', '0', 'string');
	  		if (preg_match("/^[^a-z0-9\/]+$/i", $path)) {
	  			throw new Exception('Invalid path!');
	  		}

	        $file = 'uploaded_calc_images/'.$path;
	        $result = unlink($file);
	        
            die($result);
        } catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function saveComment(){
	    try{
            $jinput = JFactory::getApplication()->input;
            $comment = $jinput->get('comment', '', 'string');
            $calcId = $jinput->get('calc_id', null, 'INT');
            $model = Gm_ceilingHelpersGm_ceiling::getModel('calculation');
            if(!empty($comment)&&!empty($calcId)){
                $model->saveComment($calcId,$comment);
            }
            die(json_encode(true));
        } catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

}
