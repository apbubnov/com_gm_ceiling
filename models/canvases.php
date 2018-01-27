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
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'id', 'a.id',
                'full_name', 'a.full_name',
                'texture_title', 'a.texture_title',
                'color_title', 'a.color_title',
                'lenght', 'a.lenght',
                'count', 'a.count',
                'purchasing_price', 'a.purchasing_price',
                'price', 'a.price'
            );
        }

        parent::__construct($config);
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

    /**
     * Build an SQL query to load the list data.
     *
     * @return   JDatabaseQuery
     *
     * @since    1.6
     */
    protected function getListQuery()
    {
        // Create a new query object.
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $query->from('`#__gm_ceiling_canvases_all` AS roller')
            ->join('LEFT', '`#__gm_ceiling_canvases` AS canvas ON canvas.id = roller.id_canvas')
            ->join('LEFT', '`#__gm_ceiling_textures` AS texture ON texture.id = canvas.texture_id')
            ->join('LEFT', '`#__gm_ceiling_colors` AS color ON color.id = canvas.color_id');

        $query->select('roller.id as roller_id, roller.barcode, roller.article, roller.stock, roller.type, roller.quad, roller.points')
            ->select('canvas.id as canvas_id, canvas.name, canvas.country, canvas.width, canvas.price, canvas.count')
            ->select('color.id as color_id, color.title as color_title, color.file as color_file, color.hex as color_hex')
            ->select('texture.id as texture_id, texture.texture_title, texture.texture_colored');

        $query->group('canvas.country, canvas.name, canvas.width, roller.id');

        $search = $this->getState('filter.search');
        if (!empty($search)) {
            if (stripos($search, 'id:') === 0) {
                $query->where('a.id = ' . (int)substr($search, 3));
            } else {
                $search = $db->Quote('%' . $db->escape($search, true) . '%');
                $query->where('( a.title LIKE ' . $search . '  OR  component.unit LIKE ' . $search . '  OR a.price LIKE ' . $search . ' )');
            }
        }

        // Add the list ordering clause.
        $orderCol = $this->state->get('list.ordering');
        $orderDirn = $this->state->get('list.direction');

        if ($orderCol && $orderDirn) {
            $query->order($db->escape($orderCol . ' ' . $orderDirn));
        }

        $this->setState('list.limit', null);
        return $query;
    }

    /**
     * Method to get an array of data items
     *
     * @return  mixed An array of data on success, false on failure.
     */
    public function getItems()
    {
        $items = parent::getItems();
        $user = JFactory::getUser();
        $dealer = JFactory::getUser($user->dealer_id);
        $dealerInfo  = $dealer->getDealerInfo();
        $dealer_canvases_margin = $dealerInfo->dealer_canvases_margin;
        $gm_canvases_margin = $dealerInfo->gm_canvases_margin;

        $result = array();
        foreach ($items as $item)
        {
            $item->color_title = (!empty($item->color_title))?$item->color_title:'303';
            $item->color_hex = (!empty($item->color_hex))?"#".$item->color_hex:"#FFFFFF";
            $item->color_file = (!empty($item->color_file))?'/'.$item->color_file:null;
            $item->quad = (!empty($item->quad))?$item->quad:0;
            $item->client_price = round((100 * $item->price)/(100 - $gm_canvases_margin - $dealer_canvases_margin - $gm_canvases_margin * $dealer_canvases_margin),2);

            $idFTC = $item->name.$item->country.$item->texture_title.$item->color_title;
            if (empty($result[$idFTC]))
            {
                $result[$idFTC] = array();
                $result[$idFTC]['full_name'] = $item->name." ".$item->country;
                $result[$idFTC]['texture_title'] = $item->texture_title;
                $result[$idFTC]['color_title'] = $item->color_title;
                $result[$idFTC]['color_hex'] = $item->color_hex;
                $result[$idFTC]['color_file'] = $item->color_file;
                $result[$idFTC]['lenght'] = 0;
                $result[$idFTC]['count'] = 0;
                $result[$idFTC]['sum_pp'] = 0;
                $result[$idFTC]['purchasing_price'] = 0;
                $result[$idFTC]['price'] = 0;
                $result[$idFTC]['client_price'] = 0;

                $result[$idFTC]['child'] = array();
            }
            if (empty($result[$idFTC]['child'][$item->width]))
            {
                $result[$idFTC]['child'][$item->width] = array();
                $result[$idFTC]['child'][$item->width]['id'] = $item->canvas_id;
                $result[$idFTC]['child'][$item->width]['stock'] = $item->stock;
                $result[$idFTC]['child'][$item->width]['width'] = $item->width;
                $result[$idFTC]['child'][$item->width]['lenght'] = $item->quad;
                $result[$idFTC]['lenght'] += $item->quad;
                $result[$idFTC]['child'][$item->width]['count'] = $item->count;
                $result[$idFTC]['count'] += $item->count;
                $result[$idFTC]['child'][$item->width]['purchasing_price'] = ($item->purchasing_price != 'Неизвестно')?$item->purchasing_price*$item->count:$item->purchasing_price;
                $result[$idFTC]['child'][$item->width]['one_purchasing_price'] = $item->purchasing_price;
                $result[$idFTC]['sum_pp'] += ($item->purchasing_price != 'Неизвестно')?$item->purchasing_price*$item->count:0;
                $result[$idFTC]['purchasing_price'] = ($result[$idFTC]['sum_pp'] <= 0)?'Неизвестно':$result[$idFTC]['sum_pp'];
                $result[$idFTC]['child'][$item->width]['price'] = $item->price*$item->count;
                $result[$idFTC]['child'][$item->width]['client_price'] = $item->client_price*$item->count;
                $result[$idFTC]['price'] += ($item->count > 0)?$item->price*$item->count:0;
                $result[$idFTC]['client_price'] += ($item->count > 0)?$item->client_price*$item->count:0;
                $result[$idFTC]['child'][$item->width]['one_price'] = $item->price;
                $result[$idFTC]['child'][$item->width]['one_client_price'] = $item->client_price;


                if (!empty($item->roller_id))
                    $result[$idFTC]['child'][$item->width]['child'] = array();
            }
            if (!empty($item->roller_id) && empty($result[$idFTC]['child'][$item->width]['child'][$item->roller_id]))
            {
                $result[$idFTC]['child'][$item->width]['child'][$item->roller_id] = array();
                $result[$idFTC]['child'][$item->width]['child'][$item->roller_id]['id'] = $item->roller_id;
                $result[$idFTC]['child'][$item->width]['child'][$item->roller_id]['lenght'] = $item->quad;
                $result[$idFTC]['child'][$item->width]['child'][$item->roller_id]['price'] = $item->price;
                $result[$idFTC]['child'][$item->width]['child'][$item->roller_id]['client_price'] = $item->client_price;
                $result[$idFTC]['child'][$item->width]['child'][$item->roller_id]['stock'] = $item->stock;

            }
        }
        return $result;
    }

    //KM_CHANGED START

    public function getCanvases($filter = null)
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
            ->from('`#__gm_ceiling_canvases` AS canvases')
            ->join('LEFT','`#__gm_ceiling_canvases_all` AS rollers ON canvases.id = rollers.id_canvas ')
            ->join('LEFT','`#__gm_ceiling_textures` AS textures ON textures.id = canvases.texture_id ')
            ->join('LEFT','`#__gm_ceiling_colors` AS colors ON colors.id = canvases.color_id ');

        if ($filter['select'])
            foreach ($filter['select'] as $key => $value)
                $query->select($value." AS ".$key);
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

    public function getCanvasesForInventory($start, $count)
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
            $canvas->Name = $item->country." ".$item->name." ".$item->width." :: Т: ".$item->texture_title." Ц: "
                .((empty($item->color_title))?"Нет":$item->color_title);
            $canvas->Barcode = $item->barcode;
            $canvas->Article = $item->article;
            $canvas->Quad = $item->quad;
            $canvases[] = $canvas;
        }

        $start = $end;
        $page = ceil($end/$count);
        $pages = ceil($ecount/$count);
        $info = (object) array('start' => $start, 'page'=>$page, 'pages'=>$pages);

        return (object) array('info' => $info, 'canvases' => $canvases);

        /*
        $countElements = empty($countElements)?100:$countElements;

        $session = JFactory::getSession();
        if (empty($session->get('inventory', null)))
        {
            $inventory = array('canvases' => 0, 'components' => 0);
            $session->set( 'inventory', json_encode($inventory));
        }
        $inventory = json_decode($session->get('inventory', null));
        $page = $inventory->canvases;

        $items = parent::getItems();

        $result = array();
        foreach ($items as $value)
        {
            $key = $value->id;
            if (!$result[$key])
            {
                $result[$key] = (object)array();

                $result[$key]->id = $value->id;
                $result[$key]->country = $value->country;
                $result[$key]->name = $value->name;
                $result[$key]->texture = $value->texture_title;
                $result[$key]->width = $value->width;
                $result[$key]->color = (empty($value->color_title))?'Нет':$value->color_title;
                $result[$key]->count = 0;
                $result[$key]->rollers = array();
            }

            $k = $value->ca_lenght;

            if (!$result[$key]->rollers[$k])
            {
                $result[$key]->rollers[$k] = (object)array();
                $result[$key]->rollers[$k]->id = $value->ca_id;
                $result[$key]->rollers[$k]->count = 0;
                $result[$key]->count += 1;
            }
            $result[$key]->rollers[$k]->quad = $value->ca_lenght;
            $result[$key]->rollers[$k]->purchasingPrice = $value->ca_purchasing_price;
            $result[$key]->rollers[$k]->count += (empty($value->ca_purchasing_price))?0:1;
        }

        $results = array();
        $index = 0;
        foreach ($result as $item)
        {
            if ($index >= $page && $index < ($page + $countElements))
            {
                $results[] = $item;
            }
            else if ($index >= ($page + $countElements)) break;

            $index += 1;
        }

        return array("result" => $results, "count" => count($result), "start" => $page, "end" => $page + $countElements);
        */
    }


    public function getNameCountryFilteredItems($filter)
    {
        // Создаем новый query объект.
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('distinct a.name');
        $query->select('a.country, a.id');
        $query->from('#__gm_ceiling_canvases AS a');
        if ($filter) {
            $query->where($filter);
        }

        $db->setQuery($query);
        $items = $db->loadObjectList();
        return $items;

    }

    public function getIdFilteredItems($filter)
    {
        // Создаем новый query объект.
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('a.id');
        $query->from('#__gm_ceiling_canvases AS a');
        if ($filter) {
            $query->where($filter);
        }
        $db->setQuery($query);
        $items = $db->loadObjectList();
        return $items;

    }

    public function getFilteredItemsCanvas($filter)
    {
        // Создаем новый query объект.
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('a.*');
        $query->from('#__gm_ceiling_canvases AS a');
        $query->select('textures.texture_title');
        $query->join('LEFT', '#__gm_ceiling_textures AS textures ON textures.id = a.texture_id ');
        $query->select('color.title');
        $query->join('LEFT', '#__gm_ceiling_colors AS color ON color.id = a.color_id ');
        if ($filter) {
            $query->where($filter);
        }
        $db->setQuery($query);
        $items = $db->loadObjectList();
        return $items;

    }

    public function getFilteredItems($filter)
    {
        // Создаем новый query объект.
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        // Выбераем поля.

        $query->select('distinct a.width');
        $query->select('a.price');

        $query->from('#__gm_ceiling_canvases AS a');
        if ($filter) {
            $query->where($filter);
        }
        $query->order("a.width desc");
        $db->setQuery($query);
        $items = $db->loadObjectList();

        /* foreach ($items as $item)
         {
             if (isset($item->canvas_texture) && $item->canvas_texture != '')
             {
                 if (is_object($item->canvas_texture))
                 {
                     $item->canvas_texture = \Joomla\Utilities\ArrayHelper::fromObject($item->canvas_texture);
                 }

                 $values = (is_array($item->canvas_texture)) ? $item->canvas_texture : explode(',', $item->canvas_texture);
                 $textValue = array();

                 foreach ($values as $value)
                 {
                     $db = JFactory::getDbo();
                     $query = $db->getQuery(true);
                     $query
                         ->select('`#__gm_ceiling_textures_2460714`.`texture_title`')
                         ->from($db->quoteName('#__gm_ceiling_textures', '#__gm_ceiling_textures_2460714'))
                         ->where($db->quoteName('id') . ' = ' . $db->quote($db->escape($value)));
                     $db->setQuery($query);
                     $results = $db->loadObject();

                     if ($results)
                     {
                         $textValue[] = $results->texture_title;
                     }
                 }

                 $item->canvas_texture = !empty($textValue) ? implode(', ', $textValue) : $item->canvas_texture;
             }

         }*/

        return $items;
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

    /**
     * Checks if a given date is valid and in a specified format (YYYY-MM-DD)
     *
     * @param   string $date Date to be checked
     *
     * @return bool
     */
    private function isValidDate($date)
    {
        $date = str_replace('/', '-', $date);
        return (date_create($date)) ? JFactory::getDate($date)->format("Y-m-d") : null;
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
                    $query->from("`#__gm_ceiling_canvases` AS C")
                        ->join("LEFT", "`#__gm_ceiling_textures` AS T ON C.texture_id = T.id")
                        ->join("LEFT", "`#__gm_ceiling_colors` AS L ON C.color_id = L.id")
                        ->where(($v->Texture == "Нет")?"C.texture_id IS NULL":"T.texture_title = '$v->Texture'")
                        ->where(($v->Color == "Нет")?"C.color_id IS NULL":"L.title = '$v->Color'")
                        ->where("C.name = '$v->Name' and C.width = '$v->Width' and C.country = '$v->Country'")
                        ->select("C.id, C.name, C.width, C.country, T.texture_title as texture, L.title as color, C.price");
                    $db->setQuery($query);
                    $good = $db->loadObject();

                    if ($page == "Receipt") $good->price = $v->Price;

                    if (empty($good)) throw new Exception("Полотно: $v->Country $v->Name $v->Width :: Т: $v->Texture Ц: $v->Color - НЕ НАЙДЕНО!");

                    $good->quad = floatval($v->Quad);
                    $good->unit = "м²";
                    $good->code = "055";

                    if (empty($new_data[$good->id]))
                    {
                        $good->quad = array($good->quad);
                        $new_data[$good->id] = $good;
                    }
                    else $new_data[$good->id]->quad[] = $good->quad;
                }
            }
            return $new_data;
    }

    public function setPrice($data) {
        try
        {
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query->update("`#__gm_ceiling_canvases`")
                ->set("price = $data->price")
                ->where("id = $data->id");
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

    public function saveCuts($id,$data) {
        try
        {
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query->delete("`#__gm_ceiling_cuttings`")
                ->where("`id` = $id");
            $db->setQuery($query);
            $db->execute();

            $query = $db->getQuery(true);
            $query->insert("`#__gm_ceiling_cuttings`")
                ->columns("`id`, `data`")
                ->values("$id,'$data'");
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
}
