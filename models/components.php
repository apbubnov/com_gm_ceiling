<?php
/**
 * @version    CVS: 0.1.7
 * @package    Com_Gm_ceiling
 * @author     SpectralEye <Xander@spectraleye.ru>
 * @copyright  2016 SpectralEye
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');

/**
 * Methods supporting a list of Gm_ceiling records.
 *
 * @since  1.6
 */
class Gm_ceilingModelComponents extends JModelList
{
    /**
     * Constructor.
     *
     * @param   array $config An optional associative array of configuration settings.
     *
     * @see        JController
     * @since      1.6
     */
    public function __construct($config = array())
    {
        try
        {
            if (empty($config['filter_fields'])) {
                $config['filter_fields'] = array(
                    'component_id', 'component_title', 'option_count', "option_price"
                );
            }

            parent::__construct($config);
        }
        catch(Exception $e)
        {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @param   string $ordering Elements order
     * @param   string $direction Order direction
     *
     * @return void
     *
     * @throws Exception
     *
     * @since    1.6
     */
    protected function populateState($ordering = null, $direction = null)
    {
        try
        {
            // Initialise variables.
            $app = JFactory::getApplication();

            $list = $app->getUserState($this->context . '.list');

            if (empty($list['ordering'])) $list['ordering'] = 'id';
            if (empty($list['direction'])) $list['direction'] = 'desc';

            if (isset($list['ordering'])) $this->setState('list.ordering', $list['ordering']);
            if (isset($list['direction'])) $this->setState('list.direction', $list['direction']);

            // List state information.
            parent::populateState($ordering, $direction);
        }
        catch(Exception $e)
        {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    /**
     * Build an SQL query to load the list data.
     *
     * @return   JDatabaseQuery
     *
     * @since    1.6
     */

    public function getAllList_Price()
    {
        try
        {
            // Создаем новый запрс
            $db = $this->getDbo();
            $query = $db->getQuery(true);

            $query->select('a.*');
            $query->from('`#__gm_ceiling_components_option` AS a');
            $query->select('CONCAT( component.title , \' \', a.title ) AS full_name');
            $query->select('component.id AS component_id');
            $query->select('component.title AS component_title');
            $query->select('component.unit AS component_unit');
            $query->join('RIGHT', '`#__gm_ceiling_components` AS component ON a.component_id = component.id');

            $db->setQuery($query);
            $return = $db->loadObjectList();
            return $return;
        }
        catch(Exception $e)
        {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function getFilteredItems($filter)
    {
        try
        {
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query->select('`co`.`id` AS `id`,
                            `c`.`id` AS `component_id`,
                            CONCAT(`c`.`title`, \' \', `co`.`title`) AS `full_name`,
                            `c`.`title` AS `component_title`,
                            `co`.`title` AS `title`,
                            `c`.`unit` AS `component_unit`,
                            SUM(`co`.`count`) AS `count`,
                            `co`.`price` AS `price`,
                            `co`.`count_sale` AS `count_sale`');
            $query->from('`#__gm_ceiling_components_option` AS `co`');

            $query->join('LEFT', '`#__gm_ceiling_components` AS `c` ON `co`.`component_id` = `c`.`id`');
            $query->group('`c`.`title`, `co`.`title`');
            if ($filter) $query->where($filter);
            //throw new Exception($query, 1);
            
            $db->setQuery($query);
            $return = $db->loadObjectList();
            return $return;
        }
        catch(Exception $e)
        {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function getListProfil($id = null)
    {
        try
        {
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query->select('a.*');
            $query->from('`#__gm_ceiling_components_option` AS a');
            $query->select('CONCAT( component.title , \' \', a.title ) AS full_name');
            $query->select('component.id AS component_id');
            $query->select('component.title AS component_title');
            $query->select('component.unit AS component_unit');
            $query->join('LEFT', '`#__gm_ceiling_components` AS component ON a.component_id = component.id');
            $query->select('profil_images.images AS image');
            $query->join('LEFT', '`#__gm_ceiling_profil_images` AS profil_images ON a.id = profil_images.profil_id');
            $query->where('component.title = "Профиль"');
            $query->group('component.title, a.title');
            if ($id) $query->where('a.id = '. $id);
            $db->setQuery($query);

            if($id) $return = $db->loadObject();
            else $return = $db->loadObjectList();
            //print_r(empty($return[0]->image)); exit;
            return $return;
        }
        catch(Exception $e)
        {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
    protected function getListQuery()
    {
        try
        {
            // Create a new query object.
            $db = $this->getDbo();
            $query = $db->getQuery(true);

            $user = JFactory::getUser();
            $user->groups = $user->get('groups');
            $stock = in_array(19, $user->groups);

            $query->from("`#__gm_ceiling_components_option` AS options")
                ->join("LEFT", "`#__gm_ceiling_components` AS components ON components.id = options.component_id")
                ->join("LEFT", "`#__gm_ceiling_components_goods` AS goods ON goods.option_id = options.id")
                ->select('options.id as option_id, options.title as option_title, options.price as option_price, options.count as option_count, options.count_sale as option_count_sale')
                ->select('components.id as component_id, components.title as component_title, components.unit, components.code')
                ->select("goods.id as good_id, goods.stock as good_stock, goods.barcode as good_barcode, goods.article as good_article, goods.count as good_count");

            $query->group('components.title, options.title'.(($stock)?", goods.id":""));

            // Add the list ordering clause.
            $orderCol = $this->state->get('list.ordering');
            $orderDirn = $this->state->get('list.direction');

            if ($orderCol && $orderDirn)
                $query->order($db->escape($orderCol . ' ' . $orderDirn));

            $this->setState('list.limit', null);

            return $query;
        }
        catch(Exception $e)
        {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
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
            $user = JFactory::getUser();
            $user->groups = $user->get('groups');
            $stock = in_array(19, $user->groups);

            $db = $this->getDbo();

            $items = parent::getItems();

            $result = [];

            $component_id = null;
            $option_id = null;
            $good_id = null;

            foreach ($items as $item) {
                $item->pprice = $this->MinPriceGood($item->good_id);
                $item->pprice = (empty($item->pprice)?"Нет":$item->pprice);

                $query = $db->getQuery(true);
                $query->from("`#__gm_ceiling_stocks`")
                    ->select("min_name as name")
                    ->where("id = '$item->good_stock'");
                $db->setQuery($query);
                $item->stock_name = $db->loadObject()->name;

                if (empty($result[$item->component_id]))
                {
                    $component = (object) [];
                    $component->title = $item->component_title;
                    $component->unit = $item->component_unit;
                    $component->code = $item->component_code;
                    $component->options = [];

                    $result[$item->component_id] = $component;
                }

                if (empty($result[$item->component_id]->options[$item->option_id]))
                {
                    $option = (object) [];
                    $option->title = $item->option_title;
                    $option->price = $item->option_price;
                    $option->count = $item->option_count;
                    $option->count_sale = $item->option_count_sale;
                    $option->pprice = null;
                    $option->goods = [];
                    $option->ocount = $this->getOCount($item->option_id);

                    $result[$item->component_id]->options[$item->option_id] = $option;
                }

                $good = (object) [];
                $good->stock = $item->good_stock;
                $good->barcode = $item->good_barcode;
                $good->article = $item->good_article;
                $good->count = $item->good_count;
                $good->pprice = $item->pprice;
                $good->stock_name = $item->stock_name;

                $result[$item->component_id]->options[$item->option_id]->goods[$item->good_id] = $good;

                if ($good_id != $item->good_id) {
                    $good_id = $item->good_id;
                }

                if ($option_id != $item->option_id) {
                    if (isset($option_id)) {
                        $tempOption = $result[$component_id]->options[$option_id];
                        foreach ($tempOption->goods as $v) {
                            if (empty($tempOption->pprice) || $v->pprice > $tempOption->pprice) {
                                $tempOption->pprice = $v->pprice;
                            }
                        }
                        $result[$component_id]->options[$option_id] = $tempOption;
                    }
                    $option_id = $item->option_id;
                }

                if ($component_id != $item->component_id) {
                    $component_id = $item->component_id;
                }
            }

            return $result;
        }
        catch(Exception $e)
        {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    private function getOCount($option_id) {
        $db = $this->getDbo();

        $TempDate = (object) [];
        $TempDate->YDateStart = date("Y-m-d H:i:s",  mktime(0, 0, 0, date("m"), 1, date("Y") - 1));
        $TempDate->YDateEnd = date("Y-m-d H:i:s",  mktime(0, 0, -1, date("m") + 1, 1, date("Y") - 1));
        $TempDate->MDateStart = date("Y-m-d H:i:s",  mktime(0, 0, 0, date("m") - 1, 1, date("Y")));
        $TempDate->MDateEnd = date("Y-m-d H:i:s",  mktime(0, 0, -1, date("m"), 1, date("Y")));
        $TempDate->DateStart = date("Y-m-d H:i:s",  mktime(0, 0, 0, date("m"), 1, date("Y")));
        $TempDate->DateEnd = date("Y-m-d H:i:s",  mktime(0, 0, -1, date("m") + 1, 1, date("Y")));

        $query = $db->getQuery(true);
        $query->from("`#__gm_ceiling_analytics_components`")
            ->select("SUM(count) as count")
            ->where("(date_update > '$TempDate->YDateStart' AND date_update < '$TempDate->YDateEnd')")
            ->where("status = '1'")
            ->where("option_id = '$option_id'");
        $db->setQuery($query);
        $YCount = $db->loadObject()->count;

        $query = $db->getQuery(true);
        $query->from("`#__gm_ceiling_analytics_components`")
            ->select("SUM(count) as count")
            ->where("(date_update > '$TempDate->MDateStart' AND date_update < '$TempDate->MDateEnd')")
            ->where("status = '1'")
            ->where("option_id = '$option_id'");
        $db->setQuery($query);
        $MCount = $db->loadObject()->count;

        $query = $db->getQuery(true);
        $query->from("`#__gm_ceiling_analytics_components`")
            ->select("SUM(count) as count")
            ->where("(date_update > '$TempDate->DateStart' AND date_update < '$TempDate->DateEnd')")
            ->where("status = '1'")
            ->where("option_id = '$option_id'");
        $db->setQuery($query);
        $Count = $db->loadObject()->count;

        $result = ceil((floatval($YCount) + floatval($MCount)) / 2);
        $result -= ($result < $Count)?0:$Count;
        return $result;
    }

    public function MinPriceOption($option_id) {
        $db = $this->getDbo();
        $query = $db->getQuery(true);
        $query->from("`#__gm_ceiling_analytics_components`")
            ->select("MAX(price) as price")
            ->where("option_id = '$option_id'")
            ->where("good_id IS NOT NULL")
            ->where("status = 1");
        $db->setQuery($query);
        $pprice = $db->loadObject()->price;
        return $pprice;
    }

    public function MinPriceGood($good_id) {
        $db = $this->getDbo();
        $query = $db->getQuery(true);
        $query->from("`#__gm_ceiling_analytics_components`")
            ->select("MAX(price) as price")
            ->where("good_id = '$good_id'")
            ->where("status = 1");
        $db->setQuery($query);
        $pprice = $db->loadObject()->price;
        return $pprice;
    }

    public function getInfoAnalytics($data) {
        try {
            $db = $this->getDbo();
            $query = $db->getQuery(true);

            $query->from("`#__gm_ceiling_analytics_components` AS analytic")
                ->select("analytic.*")
                ->where("$data->type = $data->id");

            $db->setQuery($query);
            $return = $db->loadObject();

            return $return;
        } catch (Exception $ex) {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function getComponents($filter = null)
    {
        try
        {
            $db = $this->getDbo();
            $query = $db->getQuery(true);

            $filter['where']['IS'] = array();
            foreach ($filter['where']['like'] as $k => $v)
            {
                if ($v == "'%%'") unset($filter['where']['like'][$k]);
                else if ($v == "'%Нет%'")
                {
                    unset($filter['where']['like'][$k]);
                    $filter['where']['IS'][$k] = "NULL OR ".$k." = ''";
                }
            }

            $query
                ->from('`#__gm_ceiling_components` AS components')
                ->join('LEFT', '`#__gm_ceiling_components_option` AS options  ON components.id = options.component_id')
                ->join('LEFT', '`#__gm_ceiling_components_goods` AS goods ON options.id = goods.option_id');

            if ($filter['select'])
                foreach ($filter['select'] as $key => $value)
                    $query->select($value . " AS " . $key);
            else if ($filter['id_component'])
                $query->select('components.id AS component_id, components.title AS component_title, components.unit AS component_unit')
                    ->where('components.id = ' . $filter['id_component']);
            else if ($filter['id_option'])
                $query->select('options.*, options.title AS titleOption, options.id AS idOption, components.*')
                    ->where('options.id = ' . $filter['id_option']);
            else $query->select('*');

            if ($filter['where'])
                foreach ($filter['where'] as $key => $value)
                    foreach ($value as $title => $item)
                        $query->where($title . ' ' . $key . ' ' . $item . ' ');

            if ($filter['group'])
                foreach ($filter['group'] as $value)
                    $query->group($value);

            if ($filter['order'])
                foreach ($filter['order'] as $value)
                    $query->order($value);

            $db->setQuery($query);
            $result = $db->loadObjectList();

            if ($filter['id_option']) $result[0]->purchasing_price = $this->getAnalyticInfoInEnd($filter['id_option'])->purchasing_price;

            return $result;
        }
        catch(Exception $e)
        {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function getComponentsForInventory($start, $count)
    {
        try
        {
            $items = parent::getItems();
            $components = array();
            $ecount = count($items);

            $start = intval($start);
            $count = intval($count);
            $end = $start + $count;
            if ($start >= $ecount)
            {
                $start = 0;
                $end = $start + $count;
            }

            for ($i = $start; $i < $ecount && $i < $end; $i++)
            {
                $item = $items[$i];
                $component = (object) array();
                $component->Id = $item->good_id;
                $component->Name = $item->component_title." ".$item->option_title;
                $component->Barcode = $item->good_barcode;
                $component->Article = $item->good_article;
                $component->Count = $item->good_count;
                $component->Unit = $item->unit;
                $components[] = $component;
            }

            $start = $end;
            $page = ceil($end/$count);
            $pages = ceil($ecount/$count);
            $info = (object) array('start' => $start, 'page'=>$page, 'pages'=>$pages);

            return (object) array('info' => $info, 'components' => $components);
        }
        catch(Exception $e)
        {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function getAnalyticInfoInEnd($id)
    {
        try
        {
            $db = $this->getDbo();
            $query = $db->getQuery(true);

            $query->select('analytic.price as purchasing_price, analytic.*')
                ->from('`#__gm_ceiling_analytics_components` AS analytic')
                ->where('analytic.option_id = ' . $id . ' AND analytic.count >= 0')
                ->order('analytic.date_update DESC');

            $query->select('client_type.title AS client_type')
                ->join('LEFT', '`#__gm_ceiling_clients_type` AS client_type ON analytic.client_id = client_type.id');

            $query->select('storekeeper.id AS storekeeper_id, storekeeper.name AS storekeeper_name')
                ->join('LEFT', '`#__users` AS storekeeper ON analytic.user_id = storekeeper.id');

            $query->select('dealer.id AS dealer_id, dealer.name AS dealer_name')
                ->join('LEFT', '`#__users` AS dealer ON analytic.dealer_id = dealer.id');

            $db->setQuery($query);
            $return = $db->loadObject();
            return $return;
        }
        catch(Exception $e)
        {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function getDealerInfo($id = NULL)
    {
        try
        {
            $db = $this->getDbo();
            $query = $db->getQuery(true);

            $user = JFactory::getUser();
            if (empty($id)) $id = $user->id;

                $query->select('a.*')
                ->from('`#__gm_ceiling_dealer_info` AS a')
                ->where('a.dealer_id = ' . $id);

            $db->setQuery($query);
            $return = $db->loadObject();
            return $return;
        }
        catch(Exception $e)
        {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function getColor()
    {
        try
        {
            $db = $this->getDbo();
            $query = $db->getQuery(true);

            $query->select('options.*')
                ->from('`#__gm_ceiling_components_option` AS options')
                ->join('LEFT', '`#__gm_ceiling_components` AS components  ON components.id = options.component_id ')
                ->select('color.title, color.file')
                ->join('LEFT', '`#__gm_ceiling_colors` AS color  ON color.title = options.title ')
                ->where('components.title LIKE \'%Вставка%\' AND (color.file LIKE \'%mat%\' OR color.file LIKE \'%sat%\')')
                ->group('options.title');

            $db->setQuery($query);
            $return = $db->loadObjectList();

            return $return;
        }
        catch(Exception $e)
        {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function getColorId($id)
    {
        try
        {
            $db = $this->getDbo();
            $query = $db->getQuery(true);

            $query->select('options.*')
                ->from('`#__gm_ceiling_components_option` AS options')
                ->join('LEFT', '`#__gm_ceiling_components` AS components  ON components.id = options.component_id ')
                ->select('color.title, color.file')
                ->join('LEFT', '`#__gm_ceiling_colors` AS color  ON color.title = options.title ')
                ->where(' options.id = ' . $id . ' AND  (color.file LIKE \'%mat%\' OR color.file LIKE \'%sat%\') ')
                ->group('options.title');
            $db->setQuery($query);
            $return = $db->loadObject();

            return $return;
        }
        catch(Exception $e)
        {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    /**
     * Overrides the default function to check Date fields format, identified by
     * "_dateformat" suffix, and erases the field if it's not correct.
     *
     * @return void
     */
    protected function loadFormData()
    {
        try
        {
            $app = JFactory::getApplication();
            $filters = $app->getUserState($this->context . '.filter', array());
            $error_dateformat = false;

            foreach ($filters as $key => $value) {
                if (strpos($key, '_dateformat') && !empty($value) && $this->isValidDate($value) == null) {
                    $filters[$key] = '';
                    $error_dateformat = true;
                }
            }

            if ($error_dateformat) {
                $app->enqueueMessage(JText::_("COM_GM_CEILING_SEARCH_FILTER_DATE_FORMAT"), "warning");
                $app->setUserState($this->context . '.filter', $filters);
            }

            return parent::loadFormData();
        }
        catch(Exception $e)
        {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    /**
     * Checks if a given date is valid and in a specified format (YYYY-MM-DD)
     *
     * @param   string $date Date to be checked
     *
     * @return bool
     */
    private function isValidDate($date)
    {
        try
        {
            $date = str_replace('/', '-', $date);
            return (date_create($date)) ? JFactory::getDate($date)->format("Y-m-d") : null;
        }
        catch(Exception $e)
        {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function Format($data, $page = "Realization")
    {
            $new_data = array();
            if ($page == "Realization" || $page == "Receipt")
            {
                foreach ($data as $v)
                {
                    $db = $this->getDbo();
                    $query = $db->getQuery(true);
                    $query->from("`#__gm_ceiling_components_option` AS O")
                        ->join("LEFT","`#__gm_ceiling_components` AS C ON C.id = O.component_id")
                        ->select("C.id as component_id, O.id AS id, O.price AS price, O.count_sale AS csale, C.title AS type, O.title AS name, C.unit AS unit, C.code AS code")
                        ->where("O.title = ".$db->quote($v->Name)." and C.title = ".$db->quote($v->Type)." and C.unit = ".$db->quote($v->Unit));
                    $db->setQuery($query);
                    $good = $db->loadObject();
                    if ($page == "Receipt") $good->price = $v->Price;

                    if (empty($good)) throw new Exception("Компонент: $v->Type $v->Name - НЕ НАЙДЕН!");
                    $good->count = floatval($v->Count);

                    if (empty($new_data[$good->id])) $new_data[$good->id] = $good;
                    else $new_data[$good->id]->count += $good->count;
                }
            }
            return $new_data;
    }

    public function setPrice($data) {
        try
        {
            if (gettype($data) == "object")
                $data = [$data];

            $db = $this->getDbo();
            $querySTR = "";
            foreach ($data as $v) {
                $query = $db->getQuery(true);
                $query->update("`#__gm_ceiling_components_option`")
                    ->set("price = '$v->price'")
                    ->where("id = '$v->id'");
                $querySTR .= ((string) $query) . "; ";
            }
            $db->setQuery($querySTR);
            $db->execute();
        }
        catch(Exception $e)
        {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function getPrice($data) {
        try
        {
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query->from("`#__gm_ceiling_components_option`")
                ->select("id, price");

            if (gettype($data) == "array")
                $query->where("id in (" . implode(", ", $data) . ")");
            else if (gettype($data) == "string" || gettype($data) == "integer")
                $query->where("id = '$data'");

            $db->setQuery($query);
            return $db->loadObjectList();
        }
        catch(Exception $e)
        {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
}
