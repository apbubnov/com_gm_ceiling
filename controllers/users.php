<?php
// No direct access
defined('_JEXEC') or die;

class Gm_ceilingControllerUsers extends JControllerForm
{

	public function deleteUser() {
		try
		{
			$dealer = JFactory::getUser();
			$jinput = JFactory::getApplication()->input;
            $user_id = $jinput->get('user_id', null, 'INT');

			$model = Gm_ceilingHelpersGm_ceiling::getModel('users');
			$result = $model->delete($user_id, $dealer->id);
			
			die(json_encode($result));
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
	}

}
?>