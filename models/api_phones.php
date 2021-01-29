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
class Gm_ceilingModelApi_phones extends JModelList
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see        JController
	 * @since      1.6
	 */
	public function __construct($config = array())
	{
		try
		{
			if (empty($config['filter_fields']))
			{
				$config['filter_fields'] = array(
				//	'id', 'a.id',
	              //  'texture_id', 'a.texture_id',
	                //'color_id', 'a.color_id',
	                //'name', 'a.name',
	                //'country', 'a.country',
	                //'width', 'a.width',
	                //'price', 'a.price',
	               // 'count', 'a.count',
	               // 'full_name', 'CONCAT( a.name , \' \', a.country , \' \', a.width )'
				);
			}

			parent::__construct($config);
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
			// Create a new query object.
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('*');
			$query->from('#__gm_ceiling_api_phones');
			$db->setQuery($query);
			$item = $db->loadObjectList();
			return $item;
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}
	
	function getAdvt(){
		try
		{
			$app = JFactory::getApplication();
			$user = JFactory::getUser();
			if($user->dealer_type == 0 || $user->dealer_type == 1){
                $dealerId = $user->dealer_id;
            }
			else{
                $dealerId = $user->id;
            }
			// Create a new query object.
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('*');
			$query->from('#__gm_ceiling_api_phones');
			$query->where("id <> 10 and dealer_id = $dealerId");
			$db->setQuery($query);
			$item = $db->loadObjectList();
			return $item;
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}
	function getDealersAdvt($dealer_id){
		try
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('id,name as advt_title');
			$query->from('#__gm_ceiling_api_phones');
			$query->where("dealer_id = $dealer_id OR dealer_id IS NULL");
			$db->setQuery($query);
			$item = $db->loadAssocList('id');
			return $item;
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}
	function getNumberInfo($number){
		try
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('*');
			$query->from('#__gm_ceiling_api_phones');
			$query->where("`number` = $number");
			$db->setQuery($query);
			$item = $db->loadObject();
			return $item;
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
    function getIdByName($name){
        try{
            $db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('*');
			$query->from('#__gm_ceiling_api_phones');
			$query->where("`name` = '$name'");
			$db->setQuery($query);
			$item = $db->loadResult();
			return $item;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
	function getDataById($id)
	{
		try
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('*');
			$query->from('#__gm_ceiling_api_phones');
			$query->where("`id` = $id");
			$db->setQuery($query);
			$item = $db->loadObject();
			return $item;
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
    function getArrayNumbers(){
        try{
            
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
            $query->select('number');
            $query->select('`name`');
			$query->from('#__gm_ceiling_api_phones');
			$query->where("number IS not null");
			$db->setQuery($query);
            $items = $db->loadObjectList();
            foreach($items as $item){
                $result[$item->number] = $item->name;
            }
			return $result;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
    function save($name){
        try
        {
        	$user = JFactory::getUser();
        	if($user->dealer_type == 0 || $user->dealer_type == 1){
        	    $dealer_id = $user->dealer_id;
            }
            else{
                $dealer_id = $user->id;
            }
        	if ($user->guest) {
        		throw new Exception('403 forbidden');
        	}
	        $db = JFactory::getDbo();
	        $query = $db->getQuery(true);
	        $query->insert('#__gm_ceiling_api_phones');
	        $query->columns('`name`, `dealer_id`');
			$query->values("'$name', $dealer_id");
			$db->setQuery($query);
	        $db->execute();
	        $last_id = $db->insertid();
	        $result  = $this->getDataById($last_id);
	        return $result;
	    }
	    catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function update_description($id,$description){
        try
        {
	        $db = JFactory::getDbo();
	        $description = $db->escape($description, false);
	        $query = $db->getQuery(true);
	        $query->update('`#__gm_ceiling_api_phones`');
	        $query->set("`description`='$description'");
			$query->where("`id`=$id");
			$db->setQuery($query);
	        $db->execute();
	        $last_id = $db->insertid();
	        return $last_id;
	    }
	    catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function updateName($id,$name){
    	try{
	        $db = JFactory::getDbo();
	        $query = $db->getQuery(true);
	        $query
	        	->update('`rgzbn_gm_ceiling_api_phones`')
	        	->set("`name` = '$name'")
	        	->where("`id` = $id");
	        $db->setQuery($query);
	        $db->execute();
	        return $db->getAffectedRows();
    	}
    	catch(Exception $e){
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
    	}
    }

    function saveExpense($apiPhoneId,$newExpense){
	    try{
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query->update('`#__gm_ceiling_api_phones`');
            $query->set("`expenses`= $newExpense");
            $query->where("`id` = $apiPhoneId");
            $db->setQuery($query);
            $db->execute();
            return true;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function delete($id){
    	try{
    		$db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query
            	->delete('`rgzbn_gm_ceiling_api_phones`')
        		->where("`id` = $id");
            $db->setQuery($query);
            $db->execute();
            return true;	
    	}
        catch(Exception $e){
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function saveAdvt($name,$expenses){
        try
        {
        	$user = JFactory::getUser();
        	$dealer_id = $user->dealer_id;
	        $db = JFactory::getDbo();
	        $query = $db->getQuery(true);
	        $query->insert('#__gm_ceiling_api_phones');
	        $query->columns('`name`,`expenses`, `dealer_id`');
			$query->values("'$name', '$expenses' ,$dealer_id");
			$db->setQuery($query);
	        $db->execute();
	        $last_id = $db->insertid();
	        return $last_id;
	    }
	    catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
}
