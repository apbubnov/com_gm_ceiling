<?php
/**
 * @version    CVS: 0.1.7
 * @package    Com_Gm_ceiling
 * @author     CEH4TOP <CEH4TOP@gmail.com>
 * @copyright  2017 CEH4TOP
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');

/**
 * Methods supporting a list of Gm_ceiling records.
 *
 * @since  1.6
 */
class Gm_ceilingModelGuild extends JModelList
{
    public function __construct($config = array())
    {
        try {
            if (empty($config['filter_fields'])) {
                $config['filter_fields'] = array();
            }

            parent::__construct($config);
        } catch (Exception $e) {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    protected function getListQuery()
    {
        try {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query->from("`#__gm_ceiling_guild` as guild")
                ->select("guild.*");

            return $query;
        } catch (Exception $e) {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function getCuts($data = null)
    {
        try
        {
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query->from("`#__gm_ceiling_calculations` as c")
                ->join("Left", "`#__gm_ceiling_projects` as p ON p.id = c.project_id")
                ->join("Left", "`#__canvases` as s ON s.id = c.n3")
                ->join("Left", "`#__gm_ceiling_textures` as t ON t.id = s.texture_id")
                ->join("Left", "`#__gm_ceiling_colors` as r ON r.id = s.color_id")
                ->join("Left", "`#__gm_ceiling_cuttings` as cut ON cut.id = c.id")
                ->where("p.project_status IN (5, 7)")
                ->where("(cut.ready = 0 OR cut.ready IS NULL)")
                ->order("p.quickly, p.ready_time, p.project_calculation_date, s.name, s.texture_id, s.width")
                ->select("p.quickly, p.ready_time, p.project_status, p.id as project")
                ->select("c.id as id, c.calculation_title as title, c.n3 as canvas_id, c.n4 as perimeter, c.n5 as quad, c.n9 as angles, c.calc_data, c.cut_data, c.offcut_square as square")
                ->select("s.name as name, s.country as country, s.width as width, t.texture_title as texture, r.title as color");

            if (!empty($data) && $data->type == "get") $query->where($data->name . " = '" . $data->value . "'");
            else if (!empty($data) && $data->type == "test" && count($data->data) > 0) $query->where("c.id NOT IN (" . implode(",", $data->data) . ")");
            $db->setQuery($query);
            $calculations = $db->loadObjectList();

            $query = $db->getQuery(true);
            $query->select("*")
                ->from("`#__gm_ceiling_guild_works`");
            $db->setQuery($query);
            $works = $db->loadObjectList();

            $minute = ceil(date("i") / 5) * 5 - date("i");

            $calculationsTemp = [];
            foreach ($calculations as $calc) {
                $calc->canvas_name = $calc->name . " " . $calc->country . " " . $calc->width . " " . $calc->texture . ((empty($calc->color)) ? "" : " " . $calc->color);
                $calc->cut_pdf = '/costsheets/' . md5($calc->id . 'cutpdf') . '.pdf';
                $calc->cut_image = "/cut_images/" . md5("cut_sketch" . $calc->id) . ".svg";
                $calc->cut_image_dop = "?date=" . ((string)date("dmYHis"));
                $calc->quad = floatval($calc->quad);
                $calc->perimeter = floatval($calc->perimeter);
                $calc->square = floatval($calc->square);
                $calc->percent = round((($calc->quad + $calc->square) * $calc->square) / 100.0, 2);
                $calc->angles = intval($calc->angles);
                $splitCalcData = preg_split("/;/", $calc->calc_data);
                $split = preg_split("/Полотно\d{1,5}:/", $calc->cut_data);
                unset($split[0]);
                preg_match_all("/Полотно\d{1,5}:/", $calc->cut_data, $preg);
                $cut_data = [];
                foreach ($split as $key => $value)
                    $cut_data[] = (object)["title" => $preg[0][$key - 1], "data" => $value];
                $calc->cut_data = $cut_data;
                $calc->calc_data = implode("; ", $splitCalcData);

                if ($calc->project_status == 7) $calc->status = "Собран";
                else $calc->status = "Раскроен";

                $calc->works = [];
                $calc->sumWork = 0.0;
                foreach ($works as $work) {
                    $workTemp = (object)[];
                    $workTemp->name = $work->name;
                    $workTemp->unit = $work->unit;
                    $flag = false;
                    switch (intval($work->id)) {
                        case 1:
                            $workTemp->count = floatval($calc->quad);
                            $workTemp->sum = (floatval($calc->quad) - floatval($work->free)) * $work->price;
                            break;
                        case 2:
                            $workTemp->count = floatval($calc->angles);
                            $workTemp->sum = (floatval($calc->angles) - floatval($work->free)) * $work->price;
                            break;
                        case 5:
                            $workTemp->count = floatval($calc->perimeter);
                            $workTemp->sum = (floatval($calc->perimeter) - floatval($work->free)) * $work->price;
                            break;
                        default:
                            $flag = true;
                            break;
                    }
                    if ($workTemp->sum < 0) $workTemp->sum = 0;

                    $workTemp->id = $work->id;

                    if (!$flag) {
                        $calc->works[] = $workTemp;
                        $calc->sumWork += $workTemp->sum;
                    }
                }

                $calc->quickly = (empty($calc->quickly) || $calc->quickly == 0) ? "B" : "A";
                $calc->ready_time = (empty($calc->ready_time)) ? date("d.m.Y H:i", strtotime(' +1 hour +' . (string)$minute . ' minute')) : date("d.m.Y H:i", strtotime($calc->ready_time));
                $name = ((empty($calc->ready_time)) ? date("Ymd", strtotime(' +1 hour +' . (string)$minute . ' minute')) : date("Ymd", strtotime($calc->ready_time)))
                    . $calc->quickly
                    . ((empty($calc->ready_time)) ? date("Hi", strtotime(' +1 hour +' . (string)$minute . ' minute')) : date("Hi", strtotime($calc->ready_time)));

                if (empty($calculationsTemp[$name])) {
                    $calculationsTemp[$name] = (object)[];
                    $calculationsTemp[$name]->date = $calc->ready_time;
                    $calculationsTemp[$name]->quickly = $calc->quickly;
                    $calculationsTemp[$name]->id = $name;
                    $calculationsTemp[$name]->name = ((string)$calc->ready_time) . (($calc->quickly == "A") ? " - Срочно" : "");
                    $calculationsTemp[$name]->I = [];
                }
                if (empty($calculationsTemp[$name]->I[$calc->canvas_id])) {
                    $calculationsTemp[$name]->I[$calc->canvas_id] = (object)[];
                    $calculationsTemp[$name]->I[$calc->canvas_id]->canvases = $calc->canvas_name;
                    $calculationsTemp[$name]->I[$calc->canvas_id]->I = [];

                }

                $calculationsTemp[$name]->I[$calc->canvas_id]->I[] = $calc;
            }
            ksort($calculationsTemp);

            return $calculationsTemp;
        }
        catch(Exception $e)
        {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function getEmployees($id = null)
    {
        try
        {
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query->select('user.id, user.name, user.username, user.email')
                ->from("`#__users` as user")
                ->join("LEFT", "`#__user_usergroup_map` as map ON map.user_id = user.id")
                ->where("map.group_id = '18'");
            if (!empty($id)) $query->where("user.id = '$id'");

            $db->setQuery($query);

            $employees = (empty($id)) ? $db->loadObjectList() : $db->loadObject();

            if (empty($id))
            {
                $employeesTemp = [];
                foreach ($employees as $employee)
                    $employeesTemp[$employee->id] = $employee;
                $employees = $employeesTemp;
            }

            return $employees;
        }
        catch(Exception $e)
        {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function getWorkingEmployees($data = null)
    {
        try
        {
            $id = null;
            if (gettype($id) == "object")
                $id = $data->id;

            $date = date("Y-m-d H:i:s");

            $employees = null;
            if (empty($id)) $employees = $this->getEmployees();
            else $employees = [$this->getEmployees($id)];

            foreach ($employees as $key => $employee)
            {
                $working = $this->getWorking((object) ["user_id" => $employee->id, "Date" => $date]);

                $START = null;
                $END = null;
                foreach ($working as $work)
                {
                    if ($work->action == 1 && strtotime($work->date) <= strtotime($date)) $START = strtotime($work->date);
                    if ($work->action == 0) $END = strtotime($work->date);
                }

                $WORKING = 0;
                if ($START != null && $START < strtotime($date) && ($END == null || $END < $START || $END > strtotime($date))) $WORKING = 1;
                $employees[$key]->Work = $WORKING;
            }

            if (isset($id)) $employees = $employees[0];
            else {
                $employeesTemp = [];
                foreach ($employees as $employee)
                    $employeesTemp[$employee->id] = $employee;
                $employees = $employeesTemp;
            }

            return $employees;
        }
        catch(Exception $e)
        {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function getBigDataEmployees($data = null)
    {
        try
        {
            $db = $this->getDbo();

            if (empty($data->DateStart))
                $data->DateStart = date("Y-m-d H:i:s",  mktime(0, 0, 0, date("m") - 1, date("d"), date("Y")));

            if (empty($data->DateEnd))
                $data->DateEnd = date("Y-m-d H:i:s",  mktime(0, 0, -1, date("m"), date("d") + 1, date("Y")));

            $employees = (empty($data->user_id))?$this->getWorkingEmployees():[$data->user_id => $this->getWorkingEmployees($data->user_id)];

            foreach ($employees as $key => $employee)
            {
                // Получение расписания для данного работника
                $query = $db->getQuery(true);
                $query->from("`#__gm_ceiling_guild_working` as w")
                    ->select("w.`id`, w.`user_id`, w.`date`, w.`action`")
                    ->where("w.date BETWEEN '$data->DateStart' AND '$data->DateEnd'")
                    ->where("w.user_id = '$employee->id'")
                    ->order("w.date");
                $db->setQuery($query);
                $working = $db->loadObjectList();

                $Start = null;
                $End = null;
                $WorkingTimes = [];
                $WorkingTime = 0.0;
                foreach ($working as $work)
                {
                    if ($work->action == 1 && $Start == null)
                        $Start = $work->date;

                    if ($work->action == 0)
                        $End = $work->date;

                    $TempWT = $WorkingTimes[count($WorkingTimes) - 1];
                    if (($TempWT->End != null || count($WorkingTimes) < 1) && $work->action == 1)
                        $WorkingTimes[] = (object) ["Start" => $work->date, "End" => null, "Time" => null, "TimeLine" => null];
                    else if ($work->action == 0)
                    {
                        $TempWT->End = $work->date;

                        $TStart = DateTime::createFromFormat("Y-m-d H:i:s", $TempWT->Start);
                        $TEnd = DateTime::createFromFormat("Y-m-d H:i:s", $TempWT->End);

                        $hours = $TEnd->format("H") - $TStart->format("H");
                        $minute = $TEnd->format("i") - $TStart->format("i");

                        $hours += ceil(floatval($minute) / 60.0 * 100) / 100;

                        $TempWT->TimeLine = $TStart->format("H:i") . " - " . $TEnd->format("H:i");

                        $TempWT->Time = $hours;
                        $WorkingTime += $hours;

                        $WorkingTimes[count($WorkingTimes) - 1] = $TempWT;
                    }
                }

                if (count($WorkingTimes) > 0 && empty($WorkingTimes[count($WorkingTimes) - 1]->End))
                {
                    $TempWT = $WorkingTimes[count($WorkingTimes) - 1];
                    $TempWT->End = $data->DateEnd;

                    $TStart = DateTime::createFromFormat("Y-m-d H:i:s", $TempWT->Start);
                    $TEnd = DateTime::createFromFormat("Y-m-d H:i:s", $TempWT->End)->modify("+1 second");
                    $TempWT->End = $TEnd->format("Y-m-d H:i:s");

                    $day = $TEnd->format("d") - $TStart->format("d");
                    $hours = $TEnd->format("H") - $TStart->format("H");
                    $minute = $TEnd->format("i") - $TStart->format("i");

                    $hours += (floatval($day)*24.0) + ceil(floatval($minute) / 60.0 * 100) / 100;

                    $TempWT->TimeLine = $TStart->format("H:i") . " - " . $TEnd->format("H:i");

                    $TempWT->Time = $hours;
                    $WorkingTime += $hours;

                    $WorkingTimes[count($WorkingTimes) - 1] = $TempWT;
                }

                $employee->Working = (object) ["Start" => $Start, "End" => $End, "Time" => $WorkingTime, "Times" => $WorkingTimes];

                $Salaries = $this->getSalaries((object) ["user_id" => $key, "DateStart" => $data->DateStart, "DateEnd" => $data->DateEnd]);
                $employee->Salaries = (object) [];
                $employee->Salaries->List = [];
                $employee->Salaries->Price = 0.0;
                foreach ($Salaries as $value)
                {
                    $date = DateTime::createFromFormat("Y-m-d H:i:s", $value->accrual_date);
                    $S = (object) [];

                    $S->Time = $date->format("H:i");
                    $S->Work = $value->work;
                    $S->Ceiling = "Проект №$value->project_id : $value->name : $value->canvas $value->country "
                    . "$value->width $value->texture $value->invoice $value->color";
                    $S->Ceiling = str_replace("  ", " ", $S->Ceiling);
                    $S->Price = $value->salaries;
                    $employee->Salaries->Price += $S->Price;

                    $employee->Salaries->List[] = $S;
                }

                $employees[$key] = $employee;
            }

            return $employees;
        }
        catch(Exception $e)
        {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function sendWork($data)
    {
        try
        {
            $employees = $data->employees;
            $calculations = $data->calculations;
            $calculation = $calculations->id;
            $db = $this->getDbo();

            $query = $db->getQuery(true);
            $query->update("`#__gm_ceiling_cuttings`")
                ->set("ready = '1'")
                ->where("id = '$calculation'");
            $db->setQuery($query);
            $result = $db->execute();

            if (empty($result)) {
                throw new Exception("Перевести полотно в раскроенное не удалось! Попробуйте еще раз!");
            }
            $query = $db->getQuery(true);
            $query->insert("`#__gm_ceiling_guild_salaries`")
                ->columns("`user_id`, `calc_id`, `salaries`, `work`, `note`, `accrual_date`");

            $i = 0;
            foreach ($employees as $key => $emp) {
                $work = $calculations->works[$i];
                if ($work->id != $key)
                    throw new Exception("Ошибка данных! Повторите позже!");

                $sum = ceil(floatval($work->sum) / floatval(count($emp)) * 100.0) / 100.0;

                foreach ($emp as $val)
                    $query->values("'$val', '$calculation', '$sum', '$work->id', '$work->name', '$data->date'");

                $i++;
            }

            $db->setQuery($query);
            $result = $db->execute();

            $query = $db->getQuery(true);
            $query->from("`#__gm_ceiling_calculations` as c")
                ->select("COUNT(c.id) as COUNT")
                ->where("c.project_id = '$calculations->project'");
            $db->setQuery($query);
            $all = $db->loadObject()->COUNT;

            $query = $db->getQuery(true);
            $query->from("`#__gm_ceiling_calculations` as c")
                ->join("LEFT", "`#__gm_ceiling_cuttings` as cut ON c.id = cut.id")
                ->select("COUNT(c.id) as COUNT")
                ->where("c.project_id = '$calculations->project' && cut.ready = '1'");
            $db->setQuery($query);
            $ready = $db->loadObject()->COUNT;

            $query = $db->getQuery(true);
            $query->from("`#__gm_ceiling_projects` as p")
                ->where("p.id = '$calculations->project'")
                ->select("p.project_status as status");
            $db->setQuery($query);
            $status = intval($db->loadObject()->status);

            if (intval($all) == intval($ready)) {
                if ($status == 5) $status = 6;
                else if ($status == 7) $status = 19;

                $query = $db->getQuery(true);
                $query->update("`#__gm_ceiling_projects`")
                    ->set("project_status = '$status'")
                    ->where("id = '$calculations->project'");
                $db->setQuery($query);
                $db->execute();
            }

            return;
        }
        catch(Exception $e)
        {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function getWorking($data = null)
    {
        try
        {
            if (empty($data)) $data = (object) [];

            if (isset($data->Date))
            {
                $Date = DateTime::createFromFormat("Y-m-d H:i:s", $data->Date);

                $year = $Date->format("Y");
                $month = $Date->format("m");
                $day = $Date->format("d");

                $data->DateStart = date("Y-m-d H:i:s",  mktime(0, 0, 0, $month, $day, $year));
                $data->DateEnd = date("Y-m-d H:i:s",  mktime(0, 0, -1, $month, $day + 1, $year));
            }
            if (empty($data->DateStart))
                $data->DateStart = date("Y-m-d H:i:s",  mktime(0, 0, 0, date("m") - 1, date("d"), date("Y")));

            if (empty($data->DateEnd))
                $data->DateEnd = date("Y-m-d H:i:s",  mktime(0, 0, -1, date("m"), date("d") + 1, date("Y")));

            $db = $this->getDbo();

            $users = $this->getEmployees();

            $query = $db->getQuery(true);
            $query->from("`#__gm_ceiling_guild_working` as w")
                ->select("w.`id`, w.`user_id`, w.`date`, w.`action`")
                ->where("w.date BETWEEN '$data->DateStart' AND '$data->DateEnd'")
                ->order("w.date");

            if (!empty($data->user_id))
                $query->where("w.user_id = '$data->user_id'");

            if (!empty($data->action))
                $query->where("w.action = '$data->action'");

            $db->setQuery($query);
            $working = $db->loadObjectList();

            foreach ($working as $key => $work)
            {
                $Date = DateTime::createFromFormat("Y-m-d H:i:s", $work->date);

                $time = $Date->format("H:i");

                $working[$key]->time = $time;
                $working[$key]->user = $users[$work->user_id];
            }

            return $working;
        }
        catch(Exception $e)
        {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function setWorking($data = null)
    {
        try
        {
            if (empty($data)) $data = (object) [];

            if (empty($data->user_id) || empty($data->date))
                throw new Exception("Не все данные переданы!");

            $getWorking = $this->getWorking((object) ["user_id" => $data->user_id, "Date" => $data->date]);
            $countWorking = count($getWorking);

            if (($countWorking < 1 && $data->action == 0) || ($getWorking[$countWorking - 1]->action == 0 && $data->action == 0))
                throw new Exception("Невозможно добавить выход работника, когда он еще не пришел!");
            else if ($countWorking > 0 && $getWorking[$countWorking - 1]->action == 1 && $data->action == 1)
                throw new Exception("Данный работник еще на месте!");
            else if ($getWorking[$countWorking - 1]->date > $data->date)
                throw new Exception("Нельзя добавить промежуточное значение!");
            else if ($getWorking[$countWorking - 1]->date == $data->date)
                throw new Exception("Данное время уже занято этим работником!");

            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query->insert("`#__gm_ceiling_guild_working`")
                ->columns("`user_id`, `date`, `action`")
                ->values("'$data->user_id', '$data->date', '$data->action'");
            $db->setQuery($query);
            $result = $db->execute();

            if (empty($result))
                throw new Exception("Не удалось добавить в расписание! Попробуйте снова!");
        }
        catch(Exception $e)
        {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function getSalaries($data = null)
    {
        try
        {
            if (empty($data)) $data = (object) [];

            if (empty($data->DateStart))
                $data->DateStart = date("Y-m-d H:i:s",  mktime(0, 0, 0, date("m") - 1, date("d"), date("Y")));

            if (empty($data->DateEnd))
                $data->DateEnd = date("Y-m-d H:i:s",  mktime(0, 0, -1, date("m"), date("d") + 1, date("Y")));

            $db = $this->getDbo();

            $users = $this->getEmployees();

            $query = $db->getQuery(true);
            $query->from("`#__gm_ceiling_guild_salaries` as s")
                ->join("LEFT", "`#__gm_ceiling_guild_works` as w ON w.id = s.work")
                ->join("LEFT", "`#__gm_ceiling_calculations` as c ON c.id = s.calc_id")
                ->join("LEFT", "`#__canvases` as canvas ON canvas.id = c.n3")
                ->join("LEFT", "`#__gm_ceiling_textures` as t1 ON t1.id = c.n1")
                ->join("LEFT", "`#__gm_ceiling_textures` as t2 ON t2.id = c.n2")
                ->join("LEFT", "`#__gm_ceiling_colors` as color ON color.id = canvas.color_id")
                ->select("s.user_id, s.salaries, s.accrual_date, w.name as work, c.project_id, c.calculation_title as name")
                ->select("canvas.name as canvas, canvas.country, canvas.width")
                ->select("t1.texture_title as texture, t2.texture_title as invoice, color.title as color")
                ->where("s.accrual_date BETWEEN '$data->DateStart' AND '$data->DateEnd'")
                ->order("s.accrual_date");

            if (!empty($data->user_id))
                $query->where("s.user_id = '$data->user_id'");

            $db->setQuery($query);
            $salaries = $db->loadObjectList();

            foreach ($salaries as $key => $salar)
            {
                $salaries[$key]->user = $users[$salar->user_id];
                if (empty($salar->invoice)) $salaries[$key]->invoice = "";
                if (empty($salar->color)) $salaries[$key]->color = "";
            }

            return $salaries;
        }
        catch(Exception $e)
        {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
}