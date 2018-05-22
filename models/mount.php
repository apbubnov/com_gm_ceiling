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
class Gm_ceilingModelMount extends JModelList
{
	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return   JDatabaseQuery
	 *
	 * @since    1.6
	 */
	protected function  getListQuery()
	{
		try
		{
			$app = JFactory::getApplication();
			$user = JFactory::getUser();
			$dealerId = $user->dealer_id;
			// Create a new query object.
			$db    = $this->getDbo();
			$query = $db->getQuery(true);
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('*');
			$query->from('#__gm_ceiling_mount');
			$query->where(" `user_id` = $dealerId");
			return $query;
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}
	function getData()
	{
		try
		{
			$app = JFactory::getApplication();
			$user = JFactory::getUser();
			$dealerId = $user->dealer_id;
			$db    = $this->getDbo();
			$query = $db->getQuery(true);
			$query->select('mp1,mp2,mp3,mp4,mp5,mp6,mp7,mp8,mp9,mp10,mp11,mp12,mp13,mp14,mp15,mp16,mp17,mp18,mp19,transport,user_id, distance');
			$query->from('#__gm_ceiling_mount');
			$query->where("`user_id` = $dealerId");
			$db->setQuery($query);
			$item = $db->loadObject();
			return $item;
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}
    function getDataAll($dealerId=null)
    {
    	try
    	{
			if(is_null($dealerId)){
				$app = JFactory::getApplication();
				$user = JFactory::getUser();
				$dealerId = $user->dealer_id;
			}
	        $db    = $this->getDbo();
	        $query = $db->getQuery(true);
	        $query->select('*');
	        $query->from('`#__gm_ceiling_mount`');
	        $query->where("`user_id` = '$dealerId'");
	        $db->setQuery($query);
	        $item = $db->loadObject();
	        return $item;
	    }
	    catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

	function insert($data)
    {
    	try
    	{
	        $db    = $this->getDbo();

	        unset($data->id);

	       	$col = '';
			$val = '';
			foreach ($data as $key => $value)
			{
				$col .= "`$key`,";
				$val .= "'$value',";
			}
			$col = substr($col, 0, -1);
			$val = substr($val, 0, -1);

			$query = $db->getQuery(true);
	        $query->delete('`#__gm_ceiling_mount`');
	        $query->where("`user_id` = $data->user_id");
	        $db->setQuery($query);
	        $db->execute();

	        $query = $db->getQuery(true);
	        $query->insert('`#__gm_ceiling_mount`');
	        $query->columns($col);
	        $query->values($val);
	        $db->setQuery($query);
	        $db->execute();
	        return true;
	    }
	    catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
	/**
	 * Method to get an array of data items
	 *
	 * @return  mixed An array of data on success, false on failure.
	 */
	public function getItems()
	{
		try
		{
			$items = parent::getItems();
			return $items;
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

}
