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
 /*для переноса старого прайса в новую структуру*/
 const MOUNT_MAP = [
 	"mp1" => 1, // монтаж багета (стеновой)
 	"mp2" => 2, // закладная пож люстру планочная
 	"mp3" => 3, // закладная под люстру большая
 	"mp4" => 4, // закладная под кругл свтелиник
 	"mp5" => 5, // закладная под квадратн светильник
 	"mp6" => 6, // пожарная сигнализация
 	"mp7" => 7, // обвод трубы D>100
 	"mp8" => 8, // обвод трубы D<100
 	"mp9" => 9, // монтаж разделителя
 	"mp10" => 10, // вставка
 	"mp11" => 11, // закладная брусом
 	//"mp12" => 12, // электровытяжка
 	"mp13" => 13, // крепление в плитку
 	"mp14" => 14, // крепление в керамогранит
 	"mp15" => 15, // усиление стен
 	"mp16" => 12, // электровытяжка
 	"mp17" => 17, // сложность доступа
 	"mp18" => 18, // доп монтаж
 	"mp19" => 19, // установка диффузора
 	"mp20" => 55, // обработка 1 угла
 	"mp21" => 62, // криволинейный участок ПВХ
 	"mp22" => 63, // внутренний вырез в цеху
 	"mp30" => 21, // парящий потолок
 	"mp31" => 22, // монтаж багета потолочный
 	"mp32" => 23, // монтаж алюминиевый багет
 	"mp33" => 24, //?? монтаж ткань
 	"mp35" => 25, // перегарпунка 
 	"mp47" => 26, // натяжка 
 	"mp2_2" => 27, //установка люстры
 	"mp4_2" => 28, // установка светильника
 	"mp11_2" => 29, // установка шторного карниза
 	"mp48" => 30, // внутренний вырез на месте 
 	"mp48_2" => 31, // установка внутр выреза 
 	"mp49" => 32, // монтаж лючка 
 	"mp50" => 33, // обход лючка
 	"mp51" => 34, // установка светодиодной ленты 
 	"mp52" => 35, // монтаж и подключение БП
 	"mp53" => 36, // монтаж открытой ниши под штору 
 	"mp54" => 37, // монтаж закрытой ниши
 	"mp55" => 38, // монтаж ниши с пластиком 
 	"mp56" => 41, // натяжка закрытой ниши
 	"mp11_3" => 42, // монтаж карниза в бетон 
 	"mp57" => 39, // монтаж пластика под плитнтус 
 	"mp9_1" => 43, //отбойнник 
 	"mp58" => 44, //2й уровень безщелевой с натяжкой LED
 	"mp59" => 45, //2й уровень безщелевой с натяжкой
 	"mp60" => 46, //закладная под шторный карниз 
 	"mp61" => 47, //монтаж контурного профиля 
 	"mp62" => 48, //монтаж пластикового короба вытяжка 
 	"mp63" => 49, //демонтаж профиля 
 	"mp64" => 77, //???ремонт потолка 
 	"mp65" => 50, //высота от 4 до 5
 	"mp66" => 51, //высота более 5
 	"mp67" => 52, //сборка разборка лесов
 	"mp68" => 53, //монтаж стенового багета ткань
 	//"mp69" => 0, //??монтаж потолочного ткань
 	"mp70" => 58, //натяжка ткань
 	"mp71" => 16, //установка вентиляции(вклейка кольца\термоквадрата) 
 	"mp72" => 76, //???обработка угла
 	"mp73" => 56, //Пылесос 
 	"mp74" => 57, //демонтаж более 5кв.м.
 	"mp75" => 68, //монтаж KRAAB
 	"mp76" => 69, //Натяжка KRAAB
 	"mp77" => 72, //Натяжка (световые линии)
 	"mp78" => 73, //Обагечивание световые линии
 	"mp79" => 74, //обработка острого угла
 	"mp80" => 66, //высота 3-3,5
 	"mp81" => 67, // 3,5-4
 	"mp82" => 75, //Второй уровень ниши с подсветкой 
 	"mp83" => 70, //Вклейка кольца внутрь 
 	"mp84" => 71 //Двойное подключение светильников 
 ];
/*для изменения старого прайса монтажа при изменении нового*/
const NEWMOUNT_MAP = [
 	"job_1" => "mp1",
    "job_2" => "mp2",
    "job_3" => "mp3",
    "job_4" => "mp4",
    "job_5" => "mp5",
    "job_6" => "mp6",
    "job_7" => "mp7",
    "job_8" => "mp8",
    "job_9" => "mp9",
    "job_10" => "mp10",
    "job_11" => "mp11",
    "job_13" => "mp13",
    "job_14" => "mp14",
    "job_15" => "mp15",
    "job_12" => "mp16",
    "job_17" => "mp17",
    "job_18" => "mp18",
    "job_19" => "mp19",
    "job_55" => "mp20",
    "job_62" => "mp21",
    "job_63" => "mp22",
    "job_21" => "mp30",
    "job_22" => "mp31",
    "job_23" => "mp32",
    "job_24" => "mp33",
    "job_25" => "mp35",
    "job_26" => "mp47",
    "job_27" => "mp2_2",
    "job_28" => "mp4_2",
    "job_29" => "mp11_2",
    "job_30" => "mp48",
    "job_31" => "mp48_2",
    "job_32" => "mp49",
    "job_33" => "mp50",
    "job_34" => "mp51",
    "job_35" => "mp52",
    "job_36" => "mp53",
    "job_37" => "mp54",
    "job_38" => "mp55",
    "job_41" => "mp56",
    "job_42" => "mp11_3",
    "job_39" => "mp57",
    "job_43" => "mp9_1",
    "job_44" => "mp58",
    "job_45" => "mp59",
    "job_46" => "mp60",
    "job_47" => "mp61",
    "job_48" => "mp62",
    "job_49" => "mp63",
    "job_77" => "mp64",
    "job_50" => "mp65",
    "job_51" => "mp66",
    "job_52" => "mp67",
    "job_53" => "mp68",
    "job_58" => "mp70",
    "job_16" => "mp71",
    "job_76" => "mp72",
    "job_56" => "mp73",
    "job_57" => "mp74",
    "job_68" => "mp75",
    "job_69" => "mp76",
    "job_72" => "mp77",
    "job_73" => "mp78",
    "job_74" => "mp79",
    "job_66" => "mp80",
    "job_67" => "mp81",
    "job_75" => "mp82",
    "job_70" => "mp83",
    "job_71" => "mp84"
 ];
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

	public function transferMountPrice($userId, $mountArr = null){
		try{
			$db = JFactory::getDbo();
			if(empty($mountArr)){
				$query = $db->getQuery(true);
				$query
					->select('*')
					->from('`rgzbn_gm_ceiling_mount`')
					->where("user_id = $userId");
				$db->setQuery($query);
				$mountArr = $db->loadAssoc();
			}

			$newPrice = [];
			foreach ($mountArr as $key => $value) {
				if(in_array($key,array_keys(MOUNT_MAP))){
				    $val = floatval($value);
				    $val = !empty($val) ? $val : 0;
					array_push($newPrice,MOUNT_MAP["$key"].",$val,$userId");
				}
			}
			$oldPrice = $this->getDealerPrice($userId);
			if(!empty($oldPrice)){
				$this->deletePrice($userId);
			}
			$this->savePrice($newPrice);
			return true;
		}
		catch(Exception $e){
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

	public function transferAllDealerprices(){
		try{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query
				->select('*')
				->from('`rgzbn_gm_ceiling_mount`')
				->where('user_id > 0');
			$db->setQuery($query);
			$dealersPrices = $db->loadObjectList();

			foreach($dealersPrices as $dealerPrice){
				$empty = true;
				foreach ($dealerPrice as $key => $value) {
					if(!empty($value)&&!empty(floatval($value))){
						$empty = false;
						break;
					}
				}
				$transport = !empty($dealerPrice->transport) ? $dealerPrice->transport : 0;
				$minSum = (!empty(floatval($dealerPrice->min_sum))) ? $dealerPrice->min_sum : 0;
				$distance = !empty($dealerPrice->distance) ? $dealerPrice->distance : 0;
                $query = $db->getQuery(true);
				$query
					->update('`rgzbn_gm_ceiling_dealer_info`')
					->set("`min_sum` =  $minSum")
					->set("`transport` =  $transport")
					->set("`distance` =  $distance")
					->where("`dealer_id` = $dealerPrice->user_id");
				$db->setQuery($query);
				$db->execute();
				if(!$empty){
					$this->transferMountPrice($dealerPrice->user_id);
				}
			}
		}
		catch(Exception $e){
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

	public function getDealerPrice($dealerId){
		try{
			$db= JFactory::getDbo();
			$query = $db->getQuery(true);

			$query
				->select("`id`,`job_id`, `price`")
				->from("`rgzbn_gm_ceiling_jobs_dealer_price`")
				->where("`dealer_id` = $dealerId");
			$db->setQuery($query);
			$items = $db->loadObjectList();
			return $items;

		}
		catch(Exception $e){
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

	private function deletePrice($dealerId){
		try{
			$db= JFactory::getDbo();
			$query = $db->getQuery(true);

			$query
				->delete("`rgzbn_gm_ceiling_jobs_dealer_price`")
				->where("`dealer_id` = $dealerId");
			$db->setQuery($query);
			$db->execute();
			return true;
		}
		catch(Exception $e){
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

	public function savePrice($mountArr){
		try{
			$db= JFactory::getDbo();
			$query = $db->getQuery(true);

			$query
				->insert("`rgzbn_gm_ceiling_jobs_dealer_price`")
				->columns('`job_id`,`price`,`dealer_id`')
				->values($mountArr);
			$db->setQuery($query);
			$db->execute();
			return true;
		}
		catch(Exception $e){
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

	public function updateOldStructurePrice($newPrice,$dealerId,$transportArr){
		try{
			$db = JFactory::getDbo();
			$oldPrice = $this->getDataAll($dealerId);
			$minSum = !empty($transportArr['min_sum']) ? $transportArr['min_sum'] : 0;
			$transport = !empty($transportArr['transport']) ? $transportArr['transport'] : 0;
			$distance =!empty( $transportArr['distance']) ? $transportArr['distance'] : 0;
			if(empty($oldPrice)){
				$query = $db->getQuery(true);
				$query
					->insert('`rgzbn_gm_ceiling_mount`')
					->columns('`user_id`, `min_sum`, `transport`,`distance`')
					->values("$dealerId,$minSum,$transport,$distance");
				$db->setQuery($query);
				$db->execute();
			}
			$updateStr = '';
			foreach ($newPrice as $key => $value) {
				$job_id = $value['job_id'];
				$price = $value['price'];
				$id = "job_$job_id";
				if(in_array($id,array_keys(NEWMOUNT_MAP) )){
					$updateStr .= NEWMOUNT_MAP[$id].'=\''.round($price,0).'\',';
				}
			}
			$updateStr .= "min_sum = '$minSum', transport = '$transport',distance = '$distance'";
			$query = $db->getQuery(true);
			$query
				->update('`rgzbn_gm_ceiling_mount`')
				->set($updateStr)
				->where("user_id = $dealerId");
			$db->setQuery($query);
		
			
			$db->execute();
			return true;

		}
		catch(Exception $e){
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
	}

	function getJobs(){
	    try{
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query
                ->select('id,name')
                ->from('`rgzbn_gm_ceiling_jobs`')
                ->where('guild_only <>1');
            $db->setQuery($query);
            $jobs = $db->loadObjectList();
            return $jobs;
        }
        catch(Exception $e){
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function getServicePrice(){
	    try{
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query
                ->select('`id`,`name`,`price`')
                ->from('`rgzbn_gm_ceiling_jobs`')
                ->where('is_factory_work = 0 and guild_only = 0');
            $db->setQuery($query);
            $price = $db->loadObjectList();
            return $price;
        }
        catch(Exception $e){
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function updateServicePrice($price){
	    try{
	        $update_case = '';
	        if(!empty($price)){
	            $update_case .=  'CASE ';
	            foreach ($price as $item){
	                $update_case .= "WHEN id = $item->job_id THEN $item->price ";
                }
	            $update_case .= 'END';
            }
	        if(!empty($update_case)){
	            $db = JFactory::getDbo();
	            $query = $db->getQuery(true);
	            $query
                    ->update('`rgzbn_gm_ceiling_jobs`')
                    ->set("price = $update_case;1");
	            $db->setQuery($query);
	            $db->execute();
            }
	        return true;
        }
        catch(Exception $e){
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
}
