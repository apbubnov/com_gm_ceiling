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
class Gm_ceilingModelCanvases extends JModelList
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
                    'canvas_id', 'canvas_name', 'canvas_count', 'canvas_price', 'canvas_country', 'canvas_width',
                    'texture_title', 'color_title'
                );
            }

            parent::__construct($config);
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
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

            if (empty($list['ordering']))
            {
                $list['ordering'] = 'ordering';
            }

            if (empty($list['direction']))
            {
                $list['direction'] = 'asc';
            }

            if (isset($list['ordering']))
            {
                $this->setState('list.ordering', $list['ordering']);
            }

            if (isset($list['direction']))
            {
                $this->setState('list.direction', $list['direction']);
            }

            // List state information.
            parent::populateState($ordering, $direction);
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
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
            // Create a new query object.
            $db = $this->getDbo();
            $query = $db->getQuery(true);

            $query->from('`#__canvases` AS canvas')
                ->join('LEFT', '`#__gm_ceiling_canvases_all` AS roller ON roller.id_canvas = canvas.id')
                ->join('LEFT', '`#__gm_ceiling_textures` AS texture ON texture.id = canvas.texture_id')
                ->join('LEFT', '`#__gm_ceiling_colors` AS color ON color.id = canvas.color_id');

            $query->select('canvas.id as canvas_id, canvas.name as canvas_name, canvas.country as canvas_country, '
                . 'canvas.width as canvas_width, canvas.price as canvas_price, canvas.count as canvas_count')
                ->select('roller.id as roller_id, roller.barcode as roller_barcode, roller.article as roller_article, '
                . 'roller.stock as roller_stock, roller.type as roller_type, roller.quad as roller_quad')
                ->select('color.id as color_id, color.title as color_title, color.file as color_file, color.hex as color_hex')
                ->select('texture.id as texture_id, texture.texture_title as texture_title, '
                    . ' texture.texture_colored as texture_colored');

            // Add the list ordering clause.
            $orderCol = $this->state->get('list.ordering');
            $orderDirn = $this->state->get('list.direction');

            if ($orderCol && $orderDirn) {
                $query->order($db->escape($orderCol . ' ' . $orderDirn));
            } else {
                $query->order("canvas_id asc, roller_id asc");
            }

            $this->setState('list.limit', null);

            return $query;
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
        try {
            $user = JFactory::getUser();
            $user->groups = $user->get('groups');
            $stock = in_array(19, $user->groups);

            $db = $this->getDbo();

            $items = parent::getItems();

            $result = [];

            $OLD_TC_ID = null;
            $OLD_N_ID = null;
            $OLD_CANVAS_ID = null;
            $OLD_ROLLER_ID = null;

            foreach ($items as $key => $item) {
                if (!$stock && intval($item->canvas_count) <= 0) continue;

                $item->pprice = self::MinPriceRoller($item->roller_id);
                $item->pprice = (empty($item->pprice) ? "?" : $item->pprice);

                $query = $db->getQuery(true);
                $query->from("`#__gm_ceiling_stocks`")
                    ->select("min_name as name")
                    ->where("id = '$item->roller_stock'");
                $db->setQuery($query);
                $query_rslt = $db->loadObject();
                if(!empty($query_rslt)){
                    $item->stock_name = $query_rslt->name;    
                }
                else{
                    $item->stock_name = "";   
                }

                $TC_ID = $item->texture_id;
                if (empty($OLD_TC_ID)) $OLD_TC_ID = $TC_ID;
                if (empty($result[$TC_ID])) {
                    $canvas = (object)[];
                    $canvas->texture_title = $item->texture_title;
                    $canvas->texture_colored = $item->texture_colored;
                    $canvas->count = 0;
                    $canvas->ocount = "";
                    $canvas->canvases = [];

                    $result[$TC_ID] = $canvas;
                }

                $N_ID = $item->canvas_country . "/" . $item->canvas_name;
                if (empty($OLD_N_ID)) $OLD_N_ID = $N_ID;
                if (empty($result[$TC_ID]->canvases[$N_ID])) {
                    $canvas = (object)[];
                    $canvas->country = $item->canvas_country;
                    $canvas->name = $item->canvas_name;
                    $canvas->count = 0;
                    $canvas->ocount = "";
                    $canvas->canvases = [];

                    $result[$TC_ID]->count += 1;

                    $result[$TC_ID]->canvases[$N_ID] = $canvas;
                }

                $CANVAS_ID = $item->canvas_id;
                if (empty($result[$TC_ID]->canvases[$N_ID]->canvases[$CANVAS_ID])) {
                    $canvas = (object)[];
                    $canvas->id = $item->canvas_id;
                    $canvas->color_title = (strpos($item->texture_title, "бел"))?303:$item->color_title;
                    $canvas->color_file = $item->color_file;
                    $canvas->color_hex = ($canvas->color_title == 303)?"FFFFFF":$item->color_hex;
                    $canvas->width = $item->canvas_width;
                    $canvas->price = $item->canvas_price;
                    $canvas->count = $item->canvas_count;
                    $canvas->ocount = self::getOCount($item->canvas_id);
                    $canvas->rollers = [];

                    if ($canvas->ocount > 0)
                        $result[$TC_ID]->ocount =
                            $result[$TC_ID]->canvases[$N_ID]->ocount = "<i class=\"fa fa-check-circle\"></i>";

                    $result[$TC_ID]->canvases[$N_ID]->count += 1;
                    $result[$TC_ID]->canvases[$N_ID]->canvases[$CANVAS_ID] = $canvas;
                }

                $ROLLER_ID = $item->roller_id;
                $roller = (object)[];
                $roller->id = $item->roller_id;
                $roller->barcode = $item->roller_barcode;
                $roller->article = $item->roller_article;
                $roller->stock = $item->roller_stock;
                $roller->stock_name = $item->stock_name;
                $roller->type = $item->roller_type;
                $roller->quad = $item->roller_quad;
                $roller->pprice = $item->pprice;

                $result[$TC_ID]->canvases[$N_ID]->canvases[$CANVAS_ID]->rollers[$ROLLER_ID] = $roller;

                $tempCanvas = $result[$TC_ID]->canvases[$N_ID]->canvases[$CANVAS_ID];
                if (empty($tempCanvas->pprice) || floatval($tempCanvas->pprice) < floatval($roller->pprice))
                    $tempCanvas->pprice = $roller->pprice;
                $result[$TC_ID]->canvases[$N_ID]->canvases[$CANVAS_ID] = $tempCanvas;
            }

            return $result;
        } catch (Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    private function getOCount($canvas_id) {
        try
        {
            $db = $this->getDbo();

            $TempDate = (object) [];
            $TempDate->YDateStart = date("Y-m-d H:i:s",  mktime(0, 0, 0, date("m"), 1, date("Y") - 1));
            $TempDate->YDateEnd = date("Y-m-d H:i:s",  mktime(0, 0, -1, date("m") + 1, 1, date("Y") - 1));
            $TempDate->MDateStart = date("Y-m-d H:i:s",  mktime(0, 0, 0, date("m") - 1, 1, date("Y")));
            $TempDate->MDateEnd = date("Y-m-d H:i:s",  mktime(0, 0, -1, date("m"), 1, date("Y")));
            $TempDate->DateStart = date("Y-m-d H:i:s",  mktime(0, 0, 0, date("m"), 1, date("Y")));
            $TempDate->DateEnd = date("Y-m-d H:i:s",  mktime(0, 0, -1, date("m") + 1, 1, date("Y")));

            $query = $db->getQuery(true);
            $query->from("`#__gm_ceiling_analytics_canvases`")
                ->select("SUM(quad) as quad")
                ->where("(date_update > '$TempDate->YDateStart' AND date_update < '$TempDate->YDateEnd')")
                ->where("status = '1'")
                ->where("canvas_id = '$canvas_id'");
            $db->setQuery($query);
            $query_rslt = $db->loadObject();
            if(!empty($query_rslt)){
                $YCount = $query_rslt->quad;
            } 
            else {
                $YCount = 0;  
            }

            $query = $db->getQuery(true);
            $query->from("`#__gm_ceiling_analytics_canvases`")
                ->select("SUM(quad) as quad")
                ->where("(date_update > '$TempDate->MDateStart' AND date_update < '$TempDate->MDateEnd')")
                ->where("status = '1'")
                ->where("canvas_id = '$canvas_id'");
            $db->setQuery($query);
            $query_rslt = $db->loadObject();
            if(!empty($query_rslt)){
                $MCount = $query_rslt->quad;
            } 
            else {
                $MCount = 0;  
            }

            $query = $db->getQuery(true);
            $query->from("`#__gm_ceiling_analytics_canvases`")
                ->select("SUM(quad) as quad")
                ->where("(date_update > '$TempDate->DateStart' AND date_update < '$TempDate->DateEnd')")
                ->where("status = '1'")
                ->where("canvas_id = '$canvas_id'");
            $db->setQuery($query);
            $query_rslt = $db->loadObject();
            if(!empty($query_rslt)){
                $Count = $query_rslt->quad;
            } 
            else {
                $Count = 0;  
            }

            $result = ceil((floatval($YCount) + floatval($MCount)) / 2);
            $result -= ($result < $Count)?0:$Count;
            return $result;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function MinPriceCanvas($canvas_id) {
        try
        {
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query->from("`#__gm_ceiling_analytics_canvases`")
                ->select("MAX(price) as price")
                ->where("canvas_id = '$canvas_id'")
                ->where("roller_id IS NOT NULL")
                ->where("status = 1");
            $db->setQuery($query);
            $pprice = $db->loadObject()->price;
            return $pprice;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function MinPriceRoller($roller_id) {
        try
        {
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query->from("`#__gm_ceiling_analytics_canvases`")
                ->select("MAX(price) as price")
                ->where("roller_id = '$roller_id'")
                ->where("status = 1");
            $db->setQuery($query);
            $pprice = $db->loadObject()->price;
            return $pprice;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    //KM_CHANGED START

    public function getCanvases($filter = null)
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
                ->from('`#__canvases` AS canvases')
                ->join('LEFT','`#__gm_ceiling_canvases_all` AS rollers ON canvases.id = rollers.id_canvas ')
                ->join('LEFT','`#__gm_ceiling_textures` AS textures ON textures.id = canvases.texture_id ')
                ->join('LEFT','`#__gm_ceiling_colors` AS colors ON colors.id = canvases.color_id ');

            if ($filter['select']) {
                $filter['select']['id'] = 'canvases.id';
                foreach ($filter['select'] as $key => $value)
                    $query->select($value." AS ".$key);
            }
            else if ($filter['id_canvas'])
                $query->select('DISTINCT canvases.id AS id, canvases.name AS name, canvases.country AS country, canvases.width AS width, canvases.price AS price')
                    ->select('canvases.id AS canvases_id, canvases.texture_id AS texture_id, textures.texture_title AS texture, canvases.color_id AS color_id, colors.title AS color')
                    ->where('canvases.id = '.$filter['id_canvas']);
            else if ($filter['id_canvas_all'])
                $query->select('DISTINCT canvases.id AS id, canvases.name AS name, canvases.country AS country, canvases.width AS width, canvases.price AS price')
                    ->select('canvases.id AS canvases_id, canvases.texture_id AS texture_id, textures.texture_title AS texture, canvases.color_id AS color_id, colors.title AS color')
                    ->select('rollers.id AS idAll, rollers.lenght AS lenghtAll, rollers.purchasing_price AS purchasing_price')
                    ->where('rollers.id = '.$filter['id_canvas_all']);
            else $query->select('*');

            if($filter['where'])
                foreach ($filter['where'] as $key => $value)
                    foreach ($value as $title => $item)
                        $query->where($title.' '.$key.' '.$item.' ');

            if ($filter['group'])
                foreach ($filter['group'] as $value)
                    $query->group($value);

            if ($filter['order'])
                foreach ($filter['order'] as $value)
                    $query->order($value);

            $db->setQuery($query);
            $result = $db->loadObjectList();

            return $result;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function getCanvasesForInventory($start, $count)
    {
        try
        {
        $items = parent::getItems();
        $canvases = array();
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
            $canvas = (object) array();
            $canvas->Id = $item->roller_id;
            $canvas->Name = $item->canvas_country." ".$item->canvas_name." ".$item->canvas_width." :: Т: ".$item->texture_title." Ц: "
                .((empty($item->color_title))?"Нет":$item->color_title);
            $canvas->Barcode = $item->roller_barcode;
            $canvas->Article = $item->roller_article;
            $canvas->Quad = $item->roller_quad;
            $canvases[] = $canvas;
        }

        $start = $end;
        $page = ceil($end/$count);
        $pages = ceil($ecount/$count);
        $info = (object) array('start' => $start, 'page'=>$page, 'pages'=>$pages);

        return (object) array('info' => $info, 'canvases' => $canvases);


        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }


    public function getNameCountryFilteredItems($filter)
    {
        try
        {
            // Создаем новый query объект.
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query->select('distinct a.name');
            $query->select('a.country, a.id');
            $query->from('#__canvases AS a');
            if ($filter) {
                $query->where($filter);
            }

            $db->setQuery($query);
            $items = $db->loadObjectList();
            return $items;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function getIdFilteredItems($filter)
    {
        try
        {
            // Создаем новый query объект.
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query->select('a.id');
            $query->from('#__canvases AS a');
            if ($filter) {
                $query->where($filter);
            }
            $db->setQuery($query);
            $items = $db->loadObjectList();
            return $items;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function getFilteredItemsCanvas($filter = null,$type = null)
    {
        try
        {
            // Создаем новый query объект.
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query->select('`a`.*');
            if(!empty($type)){
                $query->from('`#__gm_ceiling_canvases` AS `a`');
                $query->select('`textures`.`texture_title` AS `texture_title`');
                $query->join('LEFT', '`#__gm_ceiling_textures` AS `textures` ON `textures`.`id` = `a`.`texture_id`');
                $query->select('`color`.`title` AS `color_title`, `color`.`file` AS `color_file`');
                $query->join('LEFT', '`#__gm_ceiling_colors` AS `color` ON `color`.`id` = `a`.`color_id`');
                $query->select('`manufacturer`.`name` AS `name`, `manufacturer`.`country` AS `country`');
                $query->join('LEFT', '`#__gm_ceiling_canvases_manufacturers` AS `manufacturer` ON `manufacturer`.`id` = `a`.`manufacturer_id`');
            }
            else{
                $query->from('`rgzbn_goods_canvases` AS `a`');
                $query->order('`a`.`color`');
            }
            //throw new Exception($query);

            if (!empty($filter)) {
                $query->where($filter);
            }

            $db->setQuery($query);

            $items = $db->loadObjectList();
            return $items;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function getFilteredItems($filter)
    {
        try
        {
            // Создаем новый query объект.
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);

            // Выбераем поля.

            $query->select('distinct a.width');
            $query->select('a.price');

            $query->from('#__canvases AS a');
            if ($filter) {
                $query->where($filter);
            }
            $query->order("a.width desc");
            $db->setQuery($query);
            $items = $db->loadObjectList();



            return $items;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }


    //KM_CHANGED END

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
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
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
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function Format($data, $page = "Realization")
    {
        try
        {
            $new_data = array();
            if ($page == "Realization" || $page == "Receipt")
            {
                foreach ($data as $v)
                {
                    $db = $this->getDbo();
                    $query = $db->getQuery(true);
                    $query->from("`#__canvases` AS C")
                        ->join("LEFT", "`#__gm_ceiling_textures` AS T ON C.texture_id = T.id")
                        ->join("LEFT", "`#__gm_ceiling_colors` AS L ON C.color_id = L.id")
                        ->where(($v->Texture == "Нет" || $v->Texture == "")?"C.texture_id IS NULL":"T.texture_title = '$v->Texture'")
                        ->where(($v->Color == "Нет")?"C.color_id IS NULL":"L.title = '$v->Color'")
                        ->where("C.name = '$v->Name' AND C.width = '$v->Width' AND C.country = '$v->Country'")
                        ->select("C.id, C.name, C.width, C.country, T.texture_title as texture, L.title as color, C.price");
                    $db->setQuery($query);
                    $good = $db->loadObject();

                    if ($page == "Receipt") $good->price = $v->Price;

                    if (empty($good)) throw new Exception("Полотно: $v->Country $v->Name $v->Width :: Т: $v->Texture Ц: $v->Color - НЕ НАЙДЕНО!");


                    $good->quad = floatval($v->Quad);
                    $good->unit = "м²";
                    $good->code = "055";

                    if (!empty($new_data[$good->id])) {
                        $new_data[$good->id]->quad[] = $good->quad;

                        if (empty($new_data[$good->id]->discount[$v->discount]))
                            $new_data[$good->id]->discount[$v->discount] = $good->quad;
                        else
                            $new_data[$good->id]->discount[$v->discount] += $good->quad;
                    }
                    else {
                        $good->discount = [$v->discount => $good->quad];
                        $good->quad = [$good->quad];
                        $new_data[$good->id] = $good;
                    }
                }
            }
            return $new_data;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function setPrice($data) {
        try
        {
            if (gettype($data) == "object")
                $data = [$data];

            $db = $this->getDbo();
            //$querySTR = "";
            foreach ($data as $v) {
                $query = $db->getQuery(true);
                $query->update("`#__gm_ceiling_canvases`")
                    ->set("price = '$v->price'")
                    ->where("id = '$v->id'");
                $db->setQuery($query);
                $db->execute();
                //$querySTR .= ((string) $query) . "; ";
            }
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function getPrice($data) {
        try
        {
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query->from("`#__canvases` as canvas")
                ->join('LEFT', '`#__gm_ceiling_textures` AS texture ON texture.id = canvas.texture_id')
                ->join('LEFT', '`#__gm_ceiling_colors` AS color ON color.id = canvas.color_id')
                ->select("distinct canvas.id as id, canvas.price as price");

            if (gettype($data) == "array")
                $query->where("canvas.id in (" . implode(", ", $data) . ")");
            else if (gettype($data) == "string" || gettype($data) == "integer")
                $query->where("canvas.id = '$data'");
            else if (gettype($data) == "object")
                foreach ($data as $key => $item)
                    foreach ($item as $value)
                        $query->$key($value);

            $db->setQuery($query);
            return $db->loadObjectList();
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function saveCuts($id, $data, $canvas_area) {
        try {
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query->delete("`#__gm_ceiling_cuttings`")
                ->where("`id` = $id");
            $db->setQuery($query);
            $db->execute();

            if (!empty($data)) {
                $data = $db->escape($data, true);
                $query = $db->getQuery(true);
                $query->insert("`#__gm_ceiling_cuttings`")
                    ->columns("`id`, `data`, `canvas_area`")
                    ->values("$id, '$data', $canvas_area");
                $db->setQuery($query);
                $db->execute();
            }
        } catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function getCutArea($id) {
        try {
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query
                ->select('canvas_area')
                ->from("`#__gm_ceiling_cuttings`")
                ->where("`id` = $id");
            $db->setQuery($query);
            $result = $db->loadObject();
            return $result;

        } catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function getCutsData($id) {
        try {
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query
                ->select('*')
                ->from("`#__gm_ceiling_cuttings`")
                ->where("`id` = $id");
            $db->setQuery($query);
            $result = $db->loadObject();
            return $result;

        } catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function save($texture, $manufacturer, $price, $width, $color) {
        try {
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query
                ->insert("`#__gm_ceiling_canvases`")
                ->columns("`texture_id`, `color_id`, `manufacturer_id`, `width`, `price`, `count`")
                ->values("$texture, $color, $manufacturer, '$width', $price, 1");
            $db->setQuery($query);

            $db->execute();
        } catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
    function delete($id){
        try {
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query->delete("`#__gm_ceiling_canvases`")
                ->where("`id` = $id");
            $db->setQuery($query);
            $db->execute();
        } catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

        /*SELECT
        FROM	`rgzbn_gm_ceiling_canvases` AS `c`
        INNER JOIN	`rgzbn_gm_ceiling_textures` AS `t` ON
        `c`.`texture_id` = `t`.`id`
        INNER JOIN	`rgzbn_gm_ceiling_canvases_manufacturers` AS `m` ON
        `c`.`manufacturer_id` = `m`.`id`
        GROUP BY	`c`.`texture_id`
        ORDER BY	`c`.`texture_id`
        ;*/
    function getCanvasesTextures($flag = null){
        try{
          $db= $this->getDbo();
          $query = $db->getQuery(true);
          if(!empty($flag)){
              $query
                  ->select("CONCAT( '{\"texture\": {\"id\": \"',
                                `c`.`texture_id`, '\", \"title\": \"',
                                `t`.`texture_title`,
                                '\"}, \"manufacturers\": [',
                                GROUP_CONCAT(
                                    DISTINCT CONCAT('{\"id\": \"', `c`.`manufacturer_id`, '\", \"name\": \"', `m`.`name`, '\"}') ORDER BY `c`.`manufacturer_id`
                                    SEPARATOR ', '
                                ),
                                ']}'
                        ) AS `textures_data`")
                  ->from("`rgzbn_gm_ceiling_canvases` AS `c`")
                  ->innerJoin("`rgzbn_gm_ceiling_textures` AS `t` ON `c`.`texture_id` = `t`.`id`")
                  ->innerJoin("`rgzbn_gm_ceiling_canvases_manufacturers` AS `m` ON `c`.`manufacturer_id` = `m`.`id`")
                  ->where('`c`.`count` > 0')
                  ->group("`c`.`texture_id`")
                  ->order("`c`.`texture_id`");
          }
          else{
              $query
                  ->select("CONCAT( '{\"texture\": {\"id\": \"',
                                `c`.`texture_id`, '\", \"title\": \"',
                                `c`.`texture`,
                                '\"}, \"manufacturers\": [',
                                GROUP_CONCAT(
                                    DISTINCT CONCAT('{\"id\": \"', `c`.`manufacturer_id`, '\", \"name\": \"', `c`.`manufacturer`, '\"}') ORDER BY ISNULL(`c`.`order`),`c`.`order` 
                                    SEPARATOR ', '
                                ),
                                ']}'
                        ) AS `textures_data`")
                  ->from("`rgzbn_goods_canvases` AS `c`")
                  ->where('`c`.`visibility` = 1')
                  ->group("`c`.`texture_id`")
                  ->order("`c`.`texture_id`");
              //throw new Exception($query);
          }

          $db->setQuery($query);
          $items = $db->loadObjectList();
          $result = [];
          foreach($items as $item){
              array_push($result,json_decode($item->textures_data));
          }
          return $result;

        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
    public function updateCount($id,$count){
        try{
            $db= $this->getDbo();
            $query = $db->getQuery(true);
            $query
                ->update('`rgzbn_gm_ceiling_canvases`')
                ->set("count = $count")
                ->where("`id` = $id");
            $db->setQuery($query);
            $db->execute();
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function getCanvasesWidths($filter){
        try{
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query->select('`a`.`id`,`a`.`width`,`a`.`price`');
            $query->from('`rgzbn_goods_canvases` AS `a`');
            if (!empty($filter)) {
                $query->where($filter);
            }
            $db->setQuery($query);
            //throw new Exception($query);
            $items = $db->loadObjectList();
            return $items;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
}
