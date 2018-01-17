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
    
    public function save ($d_margin_canv,$d_margin_comp,$d_margin_mount,$gm_margin_canv,$gm_margin_comp,$gm_margin_mount,$dealer_id,$dealer_type,$discount)
	{
		try
		{
	        $db = JFactory::getDbo();
	        $query = $db->getQuery(true);
	        $query
	            ->insert('#__gm_ceiling_dealer_info')
	            ->columns('`dealer_canvases_margin`, `dealer_components_margin`, `dealer_mounting_margin`,`gm_canvases_margin`, `gm_components_margin`, `gm_mounting_margin`,`dealer_id`,`dealer_type`,`discount`')
	            ->values("$d_margin_canv,$d_margin_comp,$d_margin_mount,$gm_margin_canv,$gm_margin_comp,$gm_margin_mount,$dealer_id,$dealer_type,$discount");
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
				->set('dealer_canvases_margin = ' . $db->quote($data['dealer_canvases_margin']))
				->set('dealer_components_margin = ' . $db->quote($data['dealer_components_margin']))
				->set('dealer_mounting_margin = ' . $db->quote($data['dealer_mounting_margin']))
				->where('dealer_id = ' . $id);
                $db->setQuery($query);
				$db->execute();
				
			$query = $db->getQuery(true);
			$query->update('`#__gm_ceiling_mount`')
				->set('mp1 = ' . $db->quote($data['mp1']))
				->set('mp2 = ' . $db->quote($data['mp2']))
				->set('mp3 = ' . $db->quote($data['mp3']))
				->set('mp4 = ' . $db->quote($data['mp4']))
				->set('mp5 = ' . $db->quote($data['mp5']))
				->set('mp6 = ' . $db->quote($data['mp6']))
				->set('mp7 = ' . $db->quote($data['mp7']))
				->set('mp8 = ' . $db->quote($data['mp8']))
				->set('mp9 = ' . $db->quote($data['mp9']))
				->set('mp10 = ' . $db->quote($data['mp10']))
				->set('mp11 = ' . $db->quote($data['mp11']))
				->set('mp12 = ' . $db->quote($data['mp12']))
				->set('mp13 = ' . $db->quote($data['mp13']))
				->set('mp14 = ' . $db->quote($data['mp14']))
				->set('mp15 = ' . $db->quote($data['mp15']))
				->set('mp16 = ' . $db->quote($data['mp16']))
				->set('mp17 = ' . $db->quote($data['mp17']))
				->set('mp18 = ' . $db->quote($data['mp18']))
				->set('mp19 = ' . $db->quote($data['mp19']))
				->set('mp22 = ' . $db->quote($data['mp22']))
				->set('mp23 = ' . $db->quote($data['mp23']))
				->set('mp24 = ' . $db->quote($data['mp24']))
				->set('mp25 = ' . $db->quote($data['mp25']))
				->set('mp26 = ' . $db->quote($data['mp26']))
				->set('mp27 = ' . $db->quote($data['mp27']))
				->set('mp30 = ' . $db->quote($data['mp30']))
				->set('mp31 = ' . $db->quote($data['mp31']))
				->set('mp32 = ' . $db->quote($data['mp32']))
				->set('mp33 = ' . $db->quote($data['mp33']))
				->set('mp34 = ' . $db->quote($data['mp34']))
				->set('mp36 = ' . $db->quote($data['mp36']))
				->set('mp37 = ' . $db->quote($data['mp37']))
				->set('mp38 = ' . $db->quote($data['mp38']))
				->set('mp40 = ' . $db->quote($data['mp40']))
				->set('mp41 = ' . $db->quote($data['mp41']))
				->set('mp42 = ' . $db->quote($data['mp42']))
				->set('mp43 = ' . $db->quote($data['mp43']))
				->set('transport = ' . $db->quote($data['transport']))
				->set('distance = ' . $db->quote($data['distance']))
				->where('user_id = ' . $id);
				$db->setQuery($query);
				$db->execute();	
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
}
