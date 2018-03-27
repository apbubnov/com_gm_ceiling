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
class Gm_ceilingModelDealer_info extends JModelList
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
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return   JDatabaseQuery
	 *
	 * @since    1.6
	 */
	protected function getListQuery()
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
			$query->from('#__gm_ceiling_dealer_info');
			$query->where("`user_id` = $dealerId");
			return $query;
		}
		catch(Exception $e)
        {
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }
	}
	function getData()
	{
		try
		{
			$app = JFactory::getApplication();
			$user = JFactory::getUser();
			
			if(empty($user->dealer_id)) $dealerId = 1;
			else $dealerId = $user->dealer_id;
			// Create a new query object.
			$db    = $this->getDbo();
			$query = $db->getQuery(true);
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('*');
			$query->from('#__gm_ceiling_dealer_info');
			$query->where("`dealer_id` = $dealerId");
			$db->setQuery($query);
			$item = $db->loadObject();
			return $item;
		}
		catch(Exception $e)
        {
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }
	}
	function getDataById($dealerId)
	{
		try
		{
			$app = JFactory::getApplication();
			$user = JFactory::getUser();
			
			$db    = $this->getDbo();
			$query = $db->getQuery(true);
			$query->select('*');
			$query->from('#__gm_ceiling_dealer_info');
			$query->where("`dealer_id` = $dealerId");
			$db->setQuery($query);
			$item = $db->loadObject();
			return $item;
		}
		catch(Exception $e)
        {
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }
	}
	/**
	 * Method to get an array of data items
	 *
	 * @return  mixed An array of data on success, false on failure.
	 */
	public function getMargin($margin_name,$dealer_id)
	{
		try
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select($margin_name);
			$query->from('#__gm_ceiling_dealer_info');
			$query->where("`dealer_id` = $dealer_id");
			$db->setQuery($query);
			$result = $db->loadResult();
			return $result;
		}
		catch(Exception $e)
        {
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }
    }
    
    public function save ($d_margin_canv,$d_margin_comp,$d_margin_mount,$gm_margin_canv,$gm_margin_comp,$gm_margin_mount,$dealer_id,$discount)
	{
		try
		{
	        $db = JFactory::getDbo();
	        $query = $db->getQuery(true);
	        $query
	            ->insert('#__gm_ceiling_dealer_info')
	            ->columns('`dealer_canvases_margin`, `dealer_components_margin`, `dealer_mounting_margin`,`gm_canvases_margin`, `gm_components_margin`, `gm_mounting_margin`,`dealer_id`,`discount`')
	            ->values("$d_margin_canv,$d_margin_comp,$d_margin_mount,$gm_margin_canv,$gm_margin_comp,$gm_margin_mount,$dealer_id,$discount");
	        $db->setQuery($query);
	        $db->execute();
	    }
	    catch(Exception $e)
        {
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }
	}

	public function updateMarginAndMount($id, $data)
	{
		try
		{
			
			$db = JFactory::getDbo();
	        $query = $db->getQuery(true);
            $query->update('`#__gm_ceiling_dealer_info`')
				->set('`dealer_canvases_margin` = ' . $db->quote($data['dealer_canvases_margin']))
				->set('`dealer_components_margin` = ' . $db->quote($data['dealer_components_margin']))
				->set('`dealer_mounting_margin` = ' . $db->quote($data['dealer_mounting_margin']))
				->where('dealer_id = ' . $id);
            $db->setQuery($query);
			$db->execute();
			
			unset($data['dealer_canvases_margin'],$data['dealer_components_margin'],$data['dealer_mounting_margin']);

			$query = $db->getQuery(true);
			$query->select('`id`');
			$query->from('`#__gm_ceiling_mount`');
			$query->where("`user_id` = $id");
			$db->setQuery($query);
			$result = $db->loadResult();
			if(!empty($result)) {
				$query = $db->getQuery(true);
				$query->update('`#__gm_ceiling_mount`')
					->where('user_id = ' . $id);

				foreach ($data as $key => $value)
				{
					$query->set("$key = '$value'");
				}

				$db->setQuery($query);
				$db->execute();	
			}
			else {
				$query = $db->getQuery(true);
				$query->select('mp20')
				->from('#__gm_ceiling_mount')
				->where('user_id = 2');
				$db->setQuery($query);
				$mp20 = $db->loadObject()->mp20;

				$col = '';
				$val = '';
				foreach ($data as $key => $value)
				{
					$col .= "`$key`,";
					$val .= "'$value',";
				}
				$col .= '`user_id`,`mp20`';
				$val .= "$id, '$mp20'";

				$query = $db->getQuery(true);
	        	$query->insert('`#__gm_ceiling_mount`')
	            	->columns($col)
					->values($val);
	        $db->setQuery($query);
	        $db->execute();
			}
			return 1;
		}
	    catch(Exception $e)
        {
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }
	}
	function update_city($id,$city){
		try{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query
				->select("*")
				->from('`#__gm_ceiling_dealer_info`')
				->where('dealer_id = ' . $id);
			$db->setQuery($query);
			$result = $db->loadObjectList();
			if(count($result) == 0){
				$query->insert('`#__gm_ceiling_dealer_info`')
					->columns('`dealer_id`,`city`')
					->values("$id,'$city'");
				$db->setQuery($query);
				$db->execute();
			}
			if(count($result) == 1){
				$query->update('`#__gm_ceiling_dealer_info`')
					->set('`city` = ' . $db->quote($city))
					->where('dealer_id = ' . $id);
				$db->setQuery($query);
				$db->execute();
			}
			
            
		}
		catch(Exception $e)
        {
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }
	}
}
