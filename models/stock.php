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
class Gm_ceilingModelStock extends JModelList
{
    public function __construct($config = array())
    {
        try
        {
            if (empty($config['filter_fields'])) {
                $config['filter_fields'] = array(
                    'group_id', 'component.id',
                    'group_title', 'component.title',
                    'group_unit', 'component.unit'
                );
            }

            parent::__construct($config);
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function getAllListComponents($filter = array())
    {
        try
        {
            $query = $this->getListQuery();

            $db = JFactory::getDbo();
            $db->setQuery($query);

            return $db->loadObjectList();
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    protected function getListQuery()
    {
        try
        {
            // Создаем запрос
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);

            $query->from("`#__gm_ceiling_components` AS component");
            $query->select("component.id AS group_id");
            $query->select("component.title AS group_title");
            $query->select("component.unit AS group_unit");

            $query->join("RIGHT", "`#__gm_ceiling_components_option` AS com_option ON com_option.component_id = component.id");
            $query->select("com_option.id AS option_id");
            $query->select("com_option.title AS option_title");
            $query->select("com_option.price AS option_price");
            $query->select("com_option.count AS option_count");
            //$query->select("com_option.date AS option_date");
            //$query->select("com_option.user_accepted_id AS option_user");
            $query->select("CONCAT(component.title, ' ', com_option.title) AS full_title");

            $query->order("group_id");

            return $query;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function searchClients($search = null, $type = 1, $several = true)
    {
        try
        {
            $db = $this->getDbo();
            $query = $db->getQuery(true);

            $query
                ->select("c.id as client, c.client_name as client_name, c.dealer_id as dealer, u.name as dealer_name")
                ->from("`#__gm_ceiling_clients` as c")
                ->join("LEFT", "`#__gm_ceiling_clients_type` as ct ON ct.id = c.type_id")
                ->join("LEFT", "`#__users` as u ON c.dealer_id = u.id")
                ->where("c.client_name LIKE ('%" . $search . "%')")
                ->where("(c.type_id = " . $type . (($type == 1) ? " OR c.type_id IS NULL)" : ")"));

            $db->setQuery($query);
            $result = $db->loadObjectList();

            foreach ($result as $k => $v) {
                $query = $db->getQuery(true);

                $query
                    ->select("cc.phone as phone")
                    ->from("`#__gm_ceiling_clients_contacts` as cc")
                    ->where("cc.client_id = " . $v->client);

                $db->setQuery($query);
                $phones = $db->loadObjectList();

                $result[$k]->client_phone = array();
                foreach ($phones as $phone)
                    $result[$k]->client_phone[] = $phone->phone;

                $dealer = $this->searchDealers($v->client, false);
                $result[$k]->dealer_phone = $dealer->dealer_phone;
            }

            return ($several) ? $result : $result[0];
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function searchDealers($search = null, $several = true)
    {
        try
        {
            $db = $this->getDbo();
            $query = $db->getQuery(true);

            $query
                ->select("DISTINCT u.id as dealer, u.name as dealer_name")
                ->from("`#__users` as u")
                ->leftJoin('`rgzbn_users_dealer_id_map` as dm on dm.user_id = u.id')
                ->join("LEFT", "`#__user_usergroup_map` as ug ON u.id = ug.user_id")
                ->where("(ug.group_id = 14 OR dm.group_id = 14)")
                ->where("u.name LIKE ('%" . $search . "%')");

            $db->setQuery($query);
            $result = $db->loadObjectList();

            foreach ($result as $k => $v) {
                $query = $db->getQuery(true);

                $query
                    ->select("p.phone as phone")
                    ->from("`#__gm_ceiling_phone` as p")
                    ->where("p.users_id = " . $v->dealer);

                $db->setQuery($query);
                $phones = $db->loadObjectList();

                $result[$k]->dealer_phone = array();
                foreach ($phones as $phone)
                    $result[$k]->dealer_phone[] = $phone->phone;
            }

            return ($several) ? $result : $result[0];
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function getInformationStock($id)
    {
        try
        {
            if (intval($id) < 1) return null;

            $db = $this->getDbo();
            $query = $db->getQuery(true);

            $query->select('*')
                ->from("`#__gm_ceiling_stocks`")
                ->where("id = " . $db->quote($id));

            $db->setQuery($query);
            return $db->loadObject();
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function newDocument($stock, $type, $date)
    {
        try
        {
            $db = $this->getDbo();
            $query = $db->getQuery(true);

            $query->select('MAX(number) as number')
                ->from("`#__gm_ceiling_documents`")
                ->where("type = ".$db->quote($type)."AND stock = ".$db->quote($stock));

            $db->setQuery($query);
            $number = intval($db->loadObject()->number) + 1;

            $href = "http://" . $_SERVER['SERVER_NAME'] . '/files/stock/' . $type . '/' . $number . ".pdf";
            $path = $_SERVER['DOCUMENT_ROOT'] . '/files/stock/' . $type . '/' . $number . ".pdf";

            $db = $this->getDbo();
            $query = $db->getQuery(true);

            $query->insert($db->quoteName('#__gm_ceiling_documents'))
                ->columns('number, type, href, stock, date')
                ->values($db->quote($number) . "," . $db->quote($type) . "," .
                    $db->quote($href) . "," . $db->quote($stock) . "," . $db->quote($date));

            $db->setQuery($query);
            $res = $db->execute();
            return (empty($res)) ? null : $number;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function getStocks()
    {
        try
        {
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query->select('id, name')
                ->from("`#__gm_stock_stocks`")
                ->order('id DESC');
            $db->setQuery($query);
            return $db->loadObjectList();
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function getHistoryComponent($id, $date = null)
    {
        try
        {
            if (empty($id)) return (object) array();
            if (empty($date))
            {
                $end = date("Y.m.d H:i:s");
                $start = date("Y.m.d H:i:s", mktime(date("H"), date("i"), date("s"), date("m")-1, date("d"),   date("Y")));
                $date = (object) array("start" => $start, "end" => $end);
            }

            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query->select('analytic.*, status.title as status, stock.name as stock_name')
                ->from("`#__gm_ceiling_analytics_components` as analytic")
                ->join("LEFT", "`#__gm_ceiling_analytics_status` as status ON analytic.status = status.id")
                ->join("LEFT", "`#__gm_ceiling_stocks` as stock ON analytic.stock = stock.id")
                ->where("analytic.option_id = ".$db->quote($id))
                ->order("analytic.date_update DESC");

            if (!empty($date->start)) $query->where("analytic.date_update >= ".$db->quote($date->start));
            if (!empty($date->end)) $query->where("analytic.date_update <= ".$db->quote($date->end));
            $db->setQuery($query);
            $history = $db->loadObjectList();

            $client = Gm_ceilingHelpersGm_ceiling::getModel('clients');
            foreach ($history as $h) {
                $query = $db->getQuery(true);
                $query->select('SUM(count) as count')
                    ->from("`#__gm_ceiling_analytics_components`")
                    ->where("option_id = ".$db->quote($h->option_id))
                    ->where("barcode = ".$db->quote($h->barcode))
                    ->where("date_update <= ".$db->quote($h->date_update));
                $db->setQuery($query);
                $h->count_now = $db->loadObject()->count;
                $h->price = ($h->price > 0)?$h->price:0;

                $h->client = (!empty($h->client_id))?$client->getItem($h->client_id)->client_name:"-";
                $h->dealer = (!empty($h->dealer_id))?JFactory::getUser($h->dealer_id)->name:"-";
                $h->stock = JFactory::getUser($h->user_id)->name;
                $h->date_update = date("H:i d.m.Y", strtotime($h->date_update));
            }

            $query = $db->getQuery(true);
            $query
                ->select('CONCAT(components.title, \' \', options.title) as name, components.unit as unit')
                ->from('`#__gm_ceiling_components_option` AS options')
                ->join('LEFT', '`#__gm_ceiling_components` AS components  ON components.id = options.component_id ')
                ->where("options.id = ".$db->quote($id));
            $db->setQuery($query);
            $info = $db->loadObject();

            return (object) array('info' => $info, 'history' => $history);
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function getHistoryCanvas($id, $date = null)
    {
        try
        {
            if (empty($id)) return (object) array();
            if (empty($date))
            {
                $end = date("Y.m.d H:i:s");
                $start = date("Y.m.d H:i:s", strtotime($end." -1 month"));
                $date = (object) array("start" => $start, "end" => $end);
            }

            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query->select('canvas.*, status.title as status, stock.name as stock_name')
                ->from("`#__gm_ceiling_analytics_canvases` as canvas")
                ->join("LEFT","`#__gm_ceiling_analytics_status` as status ON status.id = canvas.status")
                ->join("LEFT", "`#__gm_ceiling_stocks` as stock ON canvas.stock = stock.id")
                ->where("canvas_id = ".$db->quote($id))
                ->order("date_update DESC");

            if (!empty($date->start)) $query->where("date_update >= ".$db->quote($date->start));
            if (!empty($date->end)) $query->where("date_update <= ".$db->quote($date->end));

            $db->setQuery($query);
            $history = $db->loadObjectList();

            $client = Gm_ceilingHelpersGm_ceiling::getModel('clients');
            foreach ($history as $h) {
                $query = $db->getQuery(true);
                $query->select('SUM(quad) as quad')
                    ->from("`#__gm_ceiling_analytics_canvases` as canvas")
                    ->where("canvas_id = ".$db->quote($h->canvas_id))
                    ->where("barcode = ".$db->quote($h->barcode))
                    ->where("date_update <= ".$db->quote($h->date_update));
                $db->setQuery($query);
                $h->quad_now = round(floatval($db->loadObject()->quad),2);
                $h->price = ($h->price > 0)?$h->price:0;

                $h->client = (!empty($h->client_id))?$client->getItem($h->client_id)->client_name:"-";
                $h->dealer = (!empty($h->dealer_id))?JFactory::getUser($h->dealer_id)->name:"-";
                $h->stock = JFactory::getUser($h->user_id)->name;
                $h->date_update = date("H:i d.m.Y", strtotime($h->date_update));
            }

            $query = $db->getQuery(true);
            $query
                ->select('CONCAT(c_mnfct.name, \' \', canvas.width, \' - \', c_mnfct.country) as name, \'м²\' as unit')
                ->from('`#__gm_ceiling_canvases` AS canvas')
                ->leftJoin('`#__gm_ceiling_canvases_manufacturers` as c_mnfct on canvas.manufacturer_id = c_mnfct.id' )
                ->where("canvas.id = ".$db->quote($id));
            $db->setQuery($query);
            $info = $db->loadObject();

            return (object) array('info' => $info, 'history' => $history);
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function getCustomer($filter = null,$dealer_type = '1'){
        try
        {
            $user = JFactory::getUser();
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query
                ->select('u.id,u.name,u.username,c.client_name,cc.phone,u.associated_client')
                ->from('`rgzbn_users` AS u')
                ->leftJoin('`rgzbn_gm_ceiling_clients` AS c ON c.id = u.associated_client')
                ->leftJoin('`rgzbn_gm_ceiling_clients_contacts` AS cc ON cc.client_id = c.id')
                ->where("(u.name LIKE '%$filter%' OR u.username LIKE '%$filter%' OR cc.phone LIKE '%$filter%' OR c.client_name LIKE '%$filter%')");
            if(!empty($dealer_type)){
                $query->where("u.dealer_type in($dealer_type)");
            }
            $db->setQuery($query);
            $items = $db->loadAssocList('id');
            return $items;

        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function setQuery($queryArray) {
        try
        {
            $queryText = "";
            foreach ($queryArray as $query)
            {
                $queryLine = (string) $query;
                $queryLine = str_replace(array("\r","\n"),"", $queryLine);
                $alphabet = array("SELECT","FROM","WHERE","GROUP BY","ORDER BY","UNION","UPDATE","SET","DELETE","ASK","DESC","NULL","IS","INSERT","VALUES","(",")");
                foreach ($alphabet as $a) $queryLine = str_replace($a, " ".$a." ", $queryLine);
                while(strpos($queryLine, "  ")) $queryLine = str_replace("  ", " ", $queryLine);
                $queryLine = substr($queryLine, 1);
                $queryText .= $queryLine.";\n";
            }
           
            $fileOld = file("components/com_gm_ceiling/views/stock/log/sql_log.txt");
            $fileNew = [];
            if (sizeof($fileOld) > 1000)
            {
                $fp=fopen("components/com_gm_ceiling/views/stock/log/sql_log.txt","w");
                for ($i = 500; $i < count($fileOld); $i++) $fileNew[] = $fileOld[$i];
                fputs($fp,implode("",$fileNew));
                fclose($fp);
            }

            $Text = "------------------------------------------------------------------------------------------------------\n";
            $date = date("d.m.Y H:i:s");
            $Text .= "Транзакция: ".$date."\n";
            $Text .= $queryText."\n";
            $db = $this->getDbo();
            $result = [];
            $queries = $db->splitSql($queryText);
            foreach( $queries AS $sql ) {
                $db->setQuery($sql);
                array_push($result,$db->execute());
            }
            //$db->setQuery($queryText);
            //$result = $db->execute();
            $Text .= (empty($result))?"Ответ: Неудачно!\n":"Ответ: Удачно!\n";
            $files = "components/com_gm_ceiling/views/stock/log/";
            file_put_contents($files.'sql_log.txt', $Text, FILE_APPEND);

            return (!empty($result));
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function updateCountGoods()
    {
        try {
            $db = $this->getDbo();

            $query = "UPDATE `#__gm_ceiling_canvases` AS C " .
                "SET `count`= (SELECT COUNT(R.id) FROM `#__gm_ceiling_canvases_all` AS R WHERE R.id_canvas = C.id);";

            $query .= "UPDATE `#__gm_ceiling_components_option` AS O " .
                "SET `count`= (SELECT SUM(G.count) FROM `#__gm_ceiling_components_goods` AS G WHERE G.option_id = O.id);";
            $result = [];
            $queries = $db->splitSql($query);
            foreach( $queries AS $sql ) {
                $db->setQuery($sql);
                array_push($result,$db->execute());
            }
            //$db->setQuery($query);
            //$result = $db->execute();
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function getAllProviders(){
        try
        {
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query->from("`#__gm_ceiling_counterparty` AS CP")
                ->select("*")
                ->where("CP.close_contract > ".$db->quote(date("Y.m.d")));
            $db->setQuery($query);
            return $db->loadObjectList();
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
    public function NextStatusProject($id)
    {
        try
        {
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query->from("`#__gm_ceiling_projects` AS P")
                ->select("P.project_status AS status")
                ->where("P.id = ".$db->quote($id));
            $db->setQuery($query);
            $status = floatval($db->loadObject()->status);

            if ($status == 5) $status = 7;
            else if ($status == 6) $status = 19;
            else if ($status == 19) $status = 8;

            $query = $db->getQuery(true);
            $query->update("`#__gm_ceiling_projects`")
                ->set("project_status = '$status'")
                ->where("id = ".$db->quote($id));
            $db->setQuery($query);
            $db->execute();
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function AddProject($client) {
        try
        {
            $db = $this->getDbo();
            $query = $db->getQuery(true);

            $query->insert('`#__gm_ceiling_projects`')
                ->columns('`client_id`, `project_status`, `created`')
                ->values("$client->id, 8, NOW()");

            $db->setQuery($query);
            $db->execute();
            $id = $db->insertid();
            return $id;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function getRealisedComponents($id,$filter = null){
        try{
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query
                ->select('g.id AS goods_id,g.category_id,g.unit_id,g.name,s.sale_price AS dealer_price,SUM(s.count) AS final_count,s.sale_price*s.count AS price_sum,1 as realised')
                ->select('CONCAT(\'[\',GROUP_CONCAT(CONCAT(\'{"inventory_id":"\',s.inventory_id,\'","i_count":"\',i.count,\'","r_count":"\',s.count,\'"}\') ORDER BY s.inventory_id DESC SEPARATOR \',\'),\']\') AS inventories')
                ->from('`rgzbn_gm_stock_sales` AS s ')
                ->leftJoin('`rgzbn_gm_stock_inventory` AS i ON i.id = s.inventory_id')
                ->leftJoin('`rgzbn_gm_stock_goods` AS g ON i.goods_id = g.id')
                ->where("s.project_id = $id")
                ->group('g.id');
            if(!empty($filter)){
                $query->where("$filter");
            }
            /*throw new Exception($query);*/
            $db->setQuery($query);
            $items = $db->loadObjectList('goods_id');
            return $items;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function getRealizedCanvases($id){
        try{
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query
                ->select("ac.canvas_id as id,abs(quad) as quad")
                ->from("`#__gm_ceiling_analytics_canvases` as ac")
                ->where("ac.project_id = $id");
            $db->setQuery($query);
            $items = $db->loadObjectList();
            return $items;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function getPropCanvasWidths() {
        try {
            $result = array();
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query
                ->select('*')
                ->from('`#__gm_stock_prop_canvas_widths`')
                ->order('`width`');
            $db->setQuery($query);
            $items = $db->loadObjectList();
            foreach ($items as $value) {
                $result[] = (object) array('id' => $value->width, 'value' => $value->width);
            }
            return $result;
        } catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function getPropColors() {
        try {
            $result = array();
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query
                ->select('*')
                ->from('`#__gm_stock_prop_colors`')
                ->order('`color`');
            $db->setQuery($query);
            $items = $db->loadObjectList();
            foreach ($items as $value) {
                $result[] = (object) array('id' => $value->color, 'value' => $value->color, 'hex' => $value->hex);
            }
            return $result;
        } catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function getPropManufacturers() {
        try {
            $result = array();
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query
                ->select('*')
                ->from('`#__gm_stock_prop_manufacturers`')
                ->order('`id`');
            $db->setQuery($query);
            $items = $db->loadObjectList();
            foreach ($items as $value) {
                $result[] = (object) array('id' => $value->id, 'value' => $value->manufacturer);
            }
            return $result;
        } catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function getPropTextures() {
        try {
            $result = array();
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query
                ->select('*')
                ->from('`#__gm_stock_prop_textures`')
                ->order('`id`');
            $db->setQuery($query);
            $items = $db->loadObjectList();
            foreach ($items as $value) {
                $result[] = (object) array('id' => $value->id, 'value' => $value->texture);
            }
            return $result;
        } catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function addPropColor($color_id, $hex) {
        try {
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query
                ->insert('`#__gm_stock_prop_colors`')
                ->columns('`color`, `hex`')
                ->values("$color_id, '$hex'");
            $db->setQuery($query);
            $db->execute();
            return $db->insertid();
        } catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function addPropManufacturer($manufacturer_title) {
        try {
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query
                ->insert('`#__gm_stock_prop_manufacturers`')
                ->columns('`manufacturer`')
                ->values("'$manufacturer_title'");
            $db->setQuery($query);
            $db->execute();
            return $db->insertid();
        } catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function addPropTexture($texture_title) {
        try {
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query
                ->insert('`#__gm_stock_prop_textures`')
                ->columns('`texture`')
                ->values("'$texture_title'");
            $db->setQuery($query);
            $db->execute();
            return $db->insertid();
        } catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function delPropColor($id) {
        try {
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query
                ->delete('`#__gm_stock_prop_colors`')
                ->where("`color` = $id");
            $db->setQuery($query);
            $db->execute();
            return true;
        } catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function delPropManufacturer($id) {
        try {
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query
                ->delete('`#__gm_stock_prop_manufacturers`')
                ->where("`id` = $id");
            $db->setQuery($query);
            $db->execute();
            return true;
        } catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function delPropTexture($id) {
        try {
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query
                ->delete('`#__gm_stock_prop_textures`')
                ->where("`id` = $id");
            $db->setQuery($query);
            $db->execute();
            return true;
        } catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function editPropColor($id, $value) {
        try {
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query
                ->update('`#__gm_stock_prop_colors`')
                ->set("`hex` = '$value'")
                ->where("`color` = $id");
            $db->setQuery($query);
            $db->execute();
            return true;
        } catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function editPropManufacturer($id, $value) {
        try {
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query
                ->update('`#__gm_stock_prop_manufacturers`')
                ->set("`manufacturer` = '$value'")
                ->where("`id` = $id");
            $db->setQuery($query);
            $db->execute();
            return true;
        } catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function editPropTexture($id, $value) {
        try {
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query
                ->update('`#__gm_stock_prop_textures`')
                ->set("`texture` = '$value'")
                ->where("`id` = $id");
            $db->setQuery($query);
            $db->execute();
            return true;
        } catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function getGoodsCategories() {
        try {
            $result = array();
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query
                ->select('*')
                ->from('`#__gm_stock_goods_categories`')
                ->order('`id`');
            $db->setQuery($query);
            $items = $db->loadObjectList();
            foreach ($items as $value) {
                $result[] = (object) array('id' => $value->id, 'value' => $value->category);
            }
            return $result;
        } catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function getCategoryAdditionalCount(){
        try{
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query
                ->select('id,additional_quantity')
                ->from('`#__gm_stock_goods_categories`')
                ->where('additional_quantity is not null')
                ->order('`id`');
            $db->setQuery($query);
            $items = $db->loadObjectList('id');
            return $items;
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
    public function getGoods($goods_id = null) {
        try {
            $result = [];
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query
                ->select('`go`.`id`, 
                    `go`.`category_id`, 
                    `go`.`name`, 
                    `go`.`unit_id`, 
                    `go`.`price`, 
                    `stoc`.`name` as `stock_name`, 
                    `stoc`.`id` as `stock_id`, 
                   SUM(`inv`.`count`) AS `count`')
                ->from('`#__gm_stock_goods` as `go`')
                ->leftJoin('`#__gm_stock_inventory` as `inv` on `go`.`id` = `inv`.`goods_id`')
                ->leftJoin('`#__gm_stock_stocks` as `stoc` on `inv`.`stock_id` = `stoc`.`id`')
                ->group('`go`.`id`,`stock_id`')
                ->order('`count` DESC');
                if (!empty($goods_id)) {
                    $query->where("`go`.`id`=$goods_id");
                }

            $db->setQuery($query);
            $items = $db->loadObjectList();
            $temp = [];
            foreach ($items as $value) {
                if(empty($temp[$value->id])){
                    $temp[$value->id] = (object) [
                        'id' => $value->id,
                        'name' => $value->name,
                        'category_id' => $value->category_id,
                        'unit_id' => $value->unit_id,
                        'price' => $value->price,
                        'stocks_count' => []
                    ];
                }
                if(!empty($value->stock_id)){
                    $temp[$value->id]->stocks_count[$value->stock_id] = (object)[
                        'id'=> $value->stock_id,
                        'name'=> $value->stock_name,
                        'count'=> $value->count
                    ];
                }

            }

            foreach ($temp as $t){
                array_push($result,$t);
            }
            return $result;
        } catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }


    public function getOperations() {
        try {
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query
                ->select('`op`.`id`,
                          `op`.`title`')
                ->from('`#__gm_ceiling_operations` as `op`')
                ->order('`op`.`id`');
            $db->setQuery($query);
            $items = $db->loadObjectList();

            return $items;     
        } catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }


    public function getGoodsUnits() {
        try{
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query
                ->select('`u`.`id`,
                          `u`.`unit`')
                ->from('`rgzbn_gm_stock_units` as `u`')
                ->order('`u`.`id`');
            $db->setQuery($query);
            $items = $db->loadObjectList();

            return $items;
        } catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
    public function getGoodsUnitsAssoc() {
        try{
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query
                ->select('`u`.`id`,
                          `u`.`unit`')
                ->from('`rgzbn_gm_stock_units` as `u`')
                ->order('`u`.`id`');
            $db->setQuery($query);
            $items = $db->loadObjectList('id');

            return $items;
        } catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
    public function getCounterparty($id = null){
        try
        {
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query->from("`#__gm_ceiling_counterparty` AS CP")
                ->select("*");

            if (!empty($id)) {
                $query->where("CP.id = ".$db->quote($id));
                $db->setQuery($query);
                $items = $db->loadObject();
            } else {
                $db->setQuery($query);
                $items = $db->loadObjectList();
            }
    
            return $items;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    //ПРИЕМКА
    public function saveDataInventory($array_reception, $stock_id, $id_counterparty) {
        try {
            $user = JFactory::getUser();
            $db = $this->getDbo();
            $reception_values = array();
            foreach ($array_reception as $value) {
                $query = $db->getQuery(true);
                $query
                    ->insert('`rgzbn_gm_stock_inventory`')
                    ->columns('`goods_id`, `count`, `stock_id`')
                    ->values($value['id'].','.$value['count'].','.$stock_id);
                $db->setQuery($query);
                $db->execute();
                $inventory_values[] = $db->insertid().','.$value['cost'].','.$value['count'].',NOW(),'.$user->id.','.$id_counterparty;
            }

            $query = $db->getQuery(true);
            $query
                ->insert('`rgzbn_gm_stock_reception`')
                ->columns('`inventory_id`, `cost_price`, `count`, `date_time`, `created_by`, `provider_id`')
                ->values($inventory_values);
            $db->setQuery($query);
            $db->execute();

            return true;
        } catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function addGoods($category,$goodsName,$goodsUnit,$goodsMultiplicity,$goodsPrice) {
        try{
            $user = JFactory::getUser();
            $dealerId = !empty($user->dealer_id) ? $user->dealer_id : 1;
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query
                ->insert('`rgzbn_gm_stock_goods`')
                ->columns('`name`,`category_id`,`unit_id`,`multiplicity`,`price`,`created_by`')
                ->values($db->quote($db->escape($goodsName,true)).",$category,$goodsUnit,$goodsMultiplicity,$goodsPrice,$dealerId");
            $db->setQuery($query);
            $db->execute();
            $result = $db->insertId();
            return $result;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
    function addProps($tableName,$columnName,$goodsId,$propValue){
        try{
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query
                ->insert("`$tableName`")
                ->columns("`good_id`,`$columnName`")
                ->values("$goodsId,$propValue");
            $db->setQuery($query);
            $db->execute();
            return true;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function getGoodsArrayByIds($ids){
        try{
            $dealer_id = JFactory::getUser()->dealer_id;
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query
                ->select('`g`.`id` as `goods_id`,`g`.`name`,`g`.`category_id`,`g`.`unit_id`,`g`.`price` as `original_price`,`g`.`multiplicity`')
                ->from('`#__gm_stock_goods` as `g`')
                ->where("`g`.`id` in ($ids)");
            if(!empty($dealer_id)){
                $query
                    ->select('ROUND(
                                    CASE
                                      WHEN `gdp`.`operation_id` = 1 THEN
                                        `gdp`.`value`
                                      WHEN `gdp`.`operation_id` = 2 THEN
                                        `g`.`price` + `gdp`.`value`
                                      WHEN `gdp`.`operation_id` = 3 THEN
                                        `g`.`price` - `gdp`.`value`
                                      WHEN `gdp`.`operation_id` = 4 THEN
                                        `g`.`price` + `gdp`.`value` / 100 * `g`.`price`
                                      WHEN `gdp`.`operation_id` = 5 THEN
                                        `g`.`price` - `gdp`.`value` / 100 * `g`.`price`
                                      ELSE
                                        `g`.`price`
                                    END, 2
                                  ) AS `dealer_price`')
                    ->leftJoin("`rgzbn_gm_ceiling_goods_dealer_price` AS `gdp` ON `gdp`.`goods_id` = `g`.`id` AND `gdp`.`dealer_id` = $dealer_id");
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

    public function getGoodsInCategories($dealer_id = null) {
        try {
            $temp_result = [];
            $result = [];
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query
                ->select('`g`.`id` as `goods_id`,`g`.`name`,`g`.`category_id`,`g`.`unit_id`,`g`.`price` as `original_price`,`g`.`multiplicity`,`gc`.`category`')
                ->from('`#__gm_stock_goods` as `g`')
                ->innerJoin('`#__gm_stock_goods_categories` as `gc` on `g`.`category_id` = `gc`.`id`')
                ->where('`visibility` <> 3')
                ->order('`g`.`name`');
            if(!empty($dealer_id)){
                $query
                    ->select('ROUND(
                                    CASE
                                      WHEN `gdp`.`operation_id` = 1 THEN
                                        `gdp`.`value`
                                      WHEN `gdp`.`operation_id` = 2 THEN
                                        `g`.`price` + `gdp`.`value`
                                      WHEN `gdp`.`operation_id` = 3 THEN
                                        `g`.`price` - `gdp`.`value`
                                      WHEN `gdp`.`operation_id` = 4 THEN
                                        `g`.`price` + `gdp`.`value` / 100 * `g`.`price`
                                      WHEN `gdp`.`operation_id` = 5 THEN
                                        `g`.`price` - `gdp`.`value` / 100 * `g`.`price`
                                      ELSE
                                        `g`.`price`
                                    END, 2
                                  ) AS `dealer_price`')
                    ->leftJoin("`rgzbn_gm_ceiling_goods_dealer_price` AS `gdp` ON `gdp`.`goods_id` = `g`.`id` AND `gdp`.`dealer_id` = $dealer_id");
            }
            $db->setQuery($query);
            //throw new Exception($query);
            $items = $db->loadObjectList();
            foreach ($items as $value) {
                if (empty($temp_result[$value->category_id])) {
                    $temp_result[$value->category_id] = (object) [
                        'category_id' => $value->category_id,
                        'category_name' => $value->category,
                        'goods' => array()
                    ];
                }
                $temp_result[$value->category_id]->goods[] = $value;
            }

            foreach ($temp_result as $value) {
                $result[] = $value;
            }

            return $result;
        } catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function getGoodsFromInvetory($ids){
        try{
            $db = $this->getDbo();

            $query = 'SET SESSION group_concat_max_len  = 16384';
            $db->setQuery($query);
            $db->execute();

            $query = $db->getQuery(true);
            $query
                ->select('`gi`.`goods_id`,SUM(`gi`.`count`) AS total_count,CONCAT(\'[\',GROUP_CONCAT(CONCAT(\'{"id":"\',`gi`.`id`,\'","count":"\',`gi`.`count`,\'","stock":"\',`gi`.`stock_id`,\'"}\') ORDER BY `gi`.`id` ASC SEPARATOR \',\'),\']\') AS detailed_count')
                ->from('`rgzbn_gm_stock_inventory` AS `gi`')
                ->where("`gi`.`goods_id` IN ($ids)")
                ->group('`gi`.`goods_id`');
            $db->setQuery($query);
            $items = $db->loadObjectList();
            return $items;
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function updateInventory($inventoryArr){
        try{
            $db = $this->getDbo();
            if(!empty($inventoryArr)) {
                foreach ($inventoryArr as $inventory) {
                    $query = $db->getQuery(true);
                    $query
                        ->update('`rgzbn_gm_stock_inventory`')
                        ->set("`count` = $inventory->count")
                        ->where("`id` = $inventory->id");
                    $db->setQuery($query);
                    $db->execute();
                }
            }
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function makeRealisation($realisationArr,$inventoryArr){
       try{
           if(!empty($realisationArr) && !empty($inventoryArr)) {

               $columns = implode(',', array_keys(get_object_vars($realisationArr[0])));
               $values_arr = [];
               foreach ($realisationArr as $item) {
                   $values_arr[] = implode(',', array_values(get_object_vars($item)));
               }

               $db = $this->getDbo();
               $query = $db->getQuery(true);
               $query
                   ->insert('`rgzbn_gm_stock_sales`')
                   ->columns("$columns")
                   ->values($values_arr);
               $db->setQuery($query);
               $db->execute();
               $this->updateInventory($inventoryArr);
           }
       }
       catch(Exception $e) {
           Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
       }
    }
     function makeReturn($returnArr,$realisattionUpdateArray,$inventoryUpdateArr,$projectId){
        try{
            $values = [];
            foreach ($returnArr as $returnItem){
                $values[]="$returnItem->inventory_id,$returnItem->count,$projectId";
            }
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query
                ->insert('`rgzbn_gm_stock_returns`')
                ->columns("inventory_id,count,project_id")
                ->values($values);
            $db->setQuery($query);
            $db->execute();

            foreach ($realisattionUpdateArray as $r_update) {
                $query = $db->getQuery(true);
                $query
                    ->update('`rgzbn_gm_stock_sales`')
                    ->set("`count` = $r_update->count")
                    ->where("`inventory_id` = $r_update->inventory_id and project_id = $projectId");
                $db->setQuery($query);
                $db->execute();
            }

            foreach ($inventoryUpdateArr as $inventory) {
                $query = $db->getQuery(true);
                $query
                    ->update('`rgzbn_gm_stock_inventory`')
                    ->set("`count` = $inventory->count")
                    ->where("`id` = $inventory->inventory_id");
                $db->setQuery($query);
                $db->execute();
            }

            $query = $db->getQuery(true);
            $query
                ->select('GROUP_CONCAT(`id` SEPARATOR \',\') as ids')
                ->from('`rgzbn_gm_stock_sales`')
                ->where("count = 0.00 and project_id = $projectId");
            $db->setQuery($query);
            $emptySalesIds = $db->loadObject();
            if(!empty($emptySalesIds->ids)){
                $query = $db->getQuery(true);
                $query
                    ->delete('`rgzbn_gm_stock_sales`')
                    ->where("id IN($emptySalesIds->ids)");
                $db->setQuery($query);
                $db->execute();
            }
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
     }

     function makeGoodsMovement($moveArray,$updateInventoryArr){
        try{
            $db = $this->getDbo();
            $moving_arr = [];
            $inserted=[];
            if(!empty($moveArray)){
                foreach ($moveArray as $item){
                    $queryInsert = $db->getQuery(true);
                    $queryInsert
                        ->insert('`rgzbn_gm_stock_inventory`')
                        ->columns('`goods_id`,`count`,`stock_id`')
                        ->values("$item->goods_id,$item->count,$item->new_stock");
                    $db->setQuery($queryInsert);
                    $db->execute();
                    $new_inventory_id = $db->insertId();
                    $inserted[]=$new_inventory_id;
                    $moving_arr[] = (object)array("from_inventory"=>$item->old_inventory_id,"to_inventory"=>$new_inventory_id,"count"=>$item->count);
                }
            }


            $inserted = implode(',',$inserted);
            $values = [];
            foreach ($moving_arr as $moving_object){
                $values[]= "$moving_object->from_inventory,$moving_object->to_inventory,$moving_object->count";
            }
            $queryMove = $db->getQuery(true);
            $queryMove
                ->insert('`rgzbn_gm_stock_moving`')
                ->columns('`from_inventory_id`,`to_inventory_id`,`count`')
                ->values($values);
            $db->setQuery($queryMove);
            $db->execute();
            $this->updateInventory($updateInventoryArr);


            $query = $db->getQuery(true);
            $query
                ->select('`id`,`count`,`stock_id`')
                ->from('`rgzbn_gm_stock_inventory`')
                ->where("id in($inserted)")
                ->order('id ASC');
            $db->setQuery($query);
            $result = $db->loadObjectList();
            return $result;
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
     }

     function addJobToGoods($goodsId,$jobId,$dealerId){
        try{
            $user = JFactory::getUser();
            if(!empty($user->id)) {
                $db = $this->getDbo();
                $query = $db->getQuery(true);
                $query
                    ->insert('`rgzbn_gm_ceiling_jobs_from_goods_map`')
                    ->columns('`parent_goods_id`,`child_job_id`,`count`,`dealer_id`')
                    ->values("$goodsId,$jobId,1,$user->dealer_id");
                $db->setQuery($query);
                $db->execute();

                return true;
            }
            else{
                throw new Exception("Empty user!");
            }
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
     }

     function getAllGoods(){
        try{
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query
                ->select('id,name,price')
                ->from('`rgzbn_gm_stock_goods`');
            $db->setQuery($query);
            $items = $db->LoadObjectList();
            return $items;
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
     }

     function updateGoods($id,$name,$price){
        try{
            if(!empty($id)&&(!empty($price) || !empty($name))) {
                $db = JFactory::getDbo();
                $query = $db->getQuery(true);
                $query
                    ->update('`rgzbn_gm_stock_goods`')
                    ->where("id = $id");
                if (!empty($name)) {
                    $query->set("name = '$name'");
                }
                if (!empty($price)) {
                    $query->set("price = $price");
                }
                $db->setQuery($query);
                $db->execute();
            }
        }

        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
     }

     function getGoodsByCategory($category){
        try{
            $user = JFactory::getUser();
            if($user->id == 0){
                $user = JFactory::getUser(1);
            }
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query
                ->select('*')
                ->from('`rgzbn_gm_stock_goods`')
                ->where("category_id = $category and (created_by=$user->id OR created_by=$user->dealer_id or created_by=1)");
            $db->setQuery($query);
            $goods = $db->loadObjectList();
            return $goods;
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
     }

     function getReceivedGoods($search = null ,$dateFrom = null,$dateTo = null){
        try{
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query
                ->select('r.id,r.count AS received_count,r.cost_price,r.inventory_id,g.id as goods_id,g.category_id,g.name,i.count,s.id as stock_id,s.name AS stock_name,r.date_time')
                ->from('`rgzbn_gm_stock_reception` AS r')
                ->innerJoin('`rgzbn_gm_stock_inventory` AS i ON i.id = r.inventory_id')
                ->innerJoin('`rgzbn_gm_stock_goods` AS g ON g.id = i.goods_id')
                ->innerJoin('`rgzbn_gm_stock_stocks` AS s ON s.id = i.stock_id')
                ->order('r.date_time DESC');
            if(!empty($search)){
                $query->where("g.id = '$search' OR g.name LIKE '%$search%'");
            }
            if(!empty($dateFrom)&&!empty($dateTo)){
                $query->where("r.date_time between '$dateFrom 00:00:00' and '$dateTo 23:59:59'");
            }
            if(!empty($dateFrom)&&empty($dateTo)){
                $query->where("r.date_time >= '$dateFrom 00:00:00' ");

            }
            if(empty($dateFrom)&&!empty($dateTo)){
                $query->where("r.date_time <= '$dateTo 23:59:59'");

            }
            $db->setQuery($query);
            $result = $db->loadObjectList('id');
            return $result;
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
     }

     function updateReceived($newGoods,$newCost,$newCountReceived,$newCountInventory,$newStock,$inventoryId,$receptionId){
        try{
            //throw new Exception("nG:$newGoods,nC:$newCost,cR:$newCountReceived,cI:$newCountInventory,nS:$newStock,ii:$inventoryId,ri:$receptionId");
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            if(!empty($newCountReceived) || !empty($newPrice)){
                $query
                    ->update('`rgzbn_gm_stock_reception`')
                    ->where("`id` = $receptionId");
                if(!empty($newCost)){
                    $query->set("`cost_price` = $newCost");
                }
                if(!empty($newCountReceived)){
                    $query->set("`count` = $newCountReceived");
                }
                //throw new Exception($query);
                $db->setQuery($query);
                $db->execute();
            }
            if(!empty($newCountInventory)||!empty($newGoods)||!empty($newStock)){
                $query = $db->getQuery(true);
                $query
                    ->update('`rgzbn_gm_stock_inventory`')
                    ->where("`id` = $inventoryId");
                if(!empty($newGoods)){
                    $query->set("`goods_id` = $newGoods");
                }
                if(!empty($newCountInventory)){
                    $query->set("`count` = $newCountInventory");
                }
                if(!empty($newStock)){
                    $query->set("`stock_id` = $newStock");
                }
                //throw new Exception($query);
                $db->setQuery($query);
                $db->execute();
            }
            return true;
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
     }

     function getReceivedInfo($id){
        try{
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query
                ->select('r.id,r.count AS received_count,r.cost_price,r.inventory_id,g.id as goods_id,g.category_id,g.name')
                ->select('i.count,s.id as stock_id,s.name AS stock_name,r.date_time')
                ->from('`rgzbn_gm_stock_reception` AS r')
                ->innerJoin('`rgzbn_gm_stock_inventory` AS i ON i.id = r.inventory_id')
                ->innerJoin('`rgzbn_gm_stock_goods` AS g ON g.id = i.goods_id')
                ->innerJoin('`rgzbn_gm_stock_stocks` AS s ON s.id = i.stock_id')
                ->where("r.id = $id");
            $db->setQuery($query);
            $result = $db->loadObject();
            return $result;
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
     }

     function getRests($date,$search){
        try{
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $saleQuery = $db->getQuery(true);
            $saleQuery
                ->select('inv.goods_id,SUM(s.count) AS sale_count')
                ->from('`rgzbn_gm_stock_sales` AS s')
                ->innerJoin('`rgzbn_gm_stock_inventory` AS inv ON s.inventory_id = inv.id')
                ->where("s.date_time <= '$date 23:59:59'")
                ->group('inv.goods_id')
                ->order('inv.goods_id');
            $query
                ->select('g.id,g.name,SUM(IFNULL(r.count,0)) AS received_count,IFNULL(sales.sale_count,0) AS sale_count,IFNULL(SUM(i.count),0) AS rest_count')
                ->from('`rgzbn_gm_stock_goods` AS g')
                ->leftJoin('`rgzbn_gm_stock_inventory` AS i ON i.goods_id = g.id')
                ->leftJoin('`rgzbn_gm_stock_reception` AS r ON r.inventory_id = i.id')
                ->leftJoin("($saleQuery) as sales ON sales.goods_id = g.id")
                ->where("(r.date_time <='$date 23:59:59' OR r.date_time IS  NULL)")
                ->group('g.id')
                ->order('rest_count DESC');
            if(!empty($search)){
                $query->where("g.name like '%$search%'");
            }
            $db->setQuery($query);
            $items = $db->loadObjectList();
            return $items;
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
     }

     function createCategory($name){
        try{
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query
                ->insert('`rgzbn_gm_stock_goods_categories`')
                ->columns('category')
                ->values("'$name'");
            $db->setQuery($query);
            $id = $db->insertId();
            return $id;
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
     }

     function getArrayOfGoods(){
         try{
             $db = JFactory::getDbo();
             $query = $db->getQuery(true);
             $query
                 ->select('id,name')
                 ->from('`rgzbn_gm_stock_goods`');
             $db->setQuery($query);
             $goods = $db->loadObjectList();
             return $goods;
         }
         catch(Exception $e) {
             Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
         }
     }
}