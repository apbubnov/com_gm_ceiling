<?php

/**
 * @version    CVS: 0.1.7
 * @package    Com_Gm_ceiling
 * @author     SpectralEye <Xander@spectraleye.ru>
 * @copyright  2016 SpectralEye
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
 // No direct access.
 defined('_JEXEC') or die;
 
 jimport('joomla.application.component.modelitem');
 jimport('joomla.event.dispatcher');

/**
 * Methods supporting a list of Gm_ceiling records.
 *
 * @since  1.6
 */
class Gm_ceilingModelrecoil_map_project extends JModelList
{
	/*protected function populateState()
	{
		try
		{
			$app = JFactory::getApplication('com_gm_ceiling');

			// Load state from the request userState on edit or from the passed variable on default
			if (JFactory::getApplication()->input->get('layout') == 'edit')
            {
				$id = JFactory::getApplication()->getUserState('com_gm_ceiling.edit.recoil_map_project.id');
            }
			else
			{
				$id = JFactory::getApplication()->input->get('id');
				JFactory::getApplication()->setUserState('com_gm_ceiling.edit.recoil_map_project.id', $id);
			}

			$this->setState('recoil_map_project.id', $id);
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }*/
    
	function getData($id=null)
	{
		try
		{
            if (empty($id))
            {
                $id = $this->getState('recoil_map_project.id');
            }
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

            $query
                ->select('recoil_id')
                ->select('ifnull(project_id,\'-\') as `project_id`')
                ->select('date_time')
                ->select('sum')
                ->select('comment')
                ->from('#__gm_ceiling_recoil_map_project')
                ->where("recoil_id = $id")
                ->order('date_time desc');
			$db->setQuery($query);
			

			$items = $db->loadObjectList();
			return $items;
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

	function getDataForProject($project_id = null) {
        try
        {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);

            $query
                ->select('recoil_id')
                ->select('date_time')
                ->select('sum')
                ->from('#__gm_ceiling_recoil_map_project');

            if (empty($project_id))
                $query->where("project_id IS NOT NULL");
            else
                $query->where("project_id = '$project_id'");

            $db->setQuery($query);

            $items = null;
            if (empty($project_id))
                $items = $db->loadObjectList();
            else
                $items = $db->loadObject();

            return $items;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
	

	function save($id_recoil,$id_project,$sum)
	{
		try
		{
            $columns = '`recoil_id`, `project_id`,`sum`,`date_time`';
            $values = array($id_recoil,$id_project,$sum,'NOW()');
            if(empty($id_project)){
                $columns = '`recoil_id`,`sum`,`date_time`';
                $values = array($id_recoil,$sum,'NOW()');
            }

			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->insert('`#__gm_ceiling_recoil_map_project`');
			$query->columns($columns);
            $query->values(implode(',',$values));
			$db->setQuery($query);
            $db->execute();
            if(empty($id_project)){
               return $this->getData($id_recoil);
            }
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function insert($recoil_id, $project_id, $sum, $comment)
    {
        try
        {
            if (empty($recoil_id) || empty($sum) || empty($comment))
                throw new Exception("Переданы неверные данные!");

            $db = JFactory::getDbo();

            $query = $db->getQuery(true);

            $project_id = (empty($project_id))?"NULL":"'$project_id'";
            $date = date("Y-m-d H:i:s");

            $query->insert('`#__gm_ceiling_recoil_map_project`')
                ->columns("recoil_id, project_id, sum, date_time, comment")
                ->values("'$recoil_id', $project_id, '$sum', '$date', '$comment'");

            $db->setQuery($query);
            $db->execute();
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function getRecoilId($project_id)
    {
    	try
    	{
	        $result = false;
	        $db = JFactory::getDbo();
			$query = $db->getQuery(true);

	        $query
	            ->select('*')
	            ->from('`#__gm_ceiling_recoil_map_project`')
	            ->where("project_id = $project_id");
			$db->setQuery($query);
			

	        $items = $db->loadObject();
			return $items;
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
    function exist($project_id)
    {
    	try
    	{
	        $result = false;
	        $db = JFactory::getDbo();
			$query = $db->getQuery(true);

	        $query
	            ->select('*')
	            ->from('`#__gm_ceiling_recoil_map_project`')
	            ->where("project_id = $project_id");
			$db->setQuery($query);
			

	        $items = $db->loadObjectList();
	        if(count($items)>0){
	            $result = true;
	        }
			return $result;
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
    function updateSum($project_id,$sum)
    {
    	try
    	{
	        $db = JFactory::getDbo();
			$query = $db->getQuery(true);

	        $query
	            ->update('`#__gm_ceiling_recoil_map_project`')
                ->set("sum = $sum")
                ->set("date_time = NOW()")
	            ->where("project_id = $project_id");
	        $db->setQuery($query);
	        $db->execute();
	    }
	    catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}
	
	function getSum($id){
		try{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query
				->select("sum(coalesce(sum,0))")
				->from('`#__gm_ceiling_recoil_map_project`')
				->where("recoil_id = $id");
			$db->setQuery($query);
			return($db->loadResult());
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

    function filterDateScore($date1, $date2){
        try{
            $user = JFactory::getUser();
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query
                ->select("*")
                ->from('`#__gm_ceiling_recoil_map_project`')
                ->where("recoil_id = $user->id   AND date_time BETWEEN '$date1 00:00:00' AND '$date2 23:59:59'");

            $db->setQuery($query);
            $result = $db->loadObjectList();
            return $result;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function deleteByProjId($project_id){
        try{
            $user = JFactory::getUser();
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query
                ->delete("*")
                ->from('`#__gm_ceiling_recoil_map_project`')
                ->where("project_id = $project_id");
            $db->setQuery($query);
            $db->execute();
            return true;
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
}
?>