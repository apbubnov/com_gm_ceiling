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
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files . 'error_log.txt', (string)$date . ' | ' . __FILE__ . ' | ' . __FUNCTION__ . ' | ' . $e->getMessage() . "\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
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
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files . 'error_log.txt', (string)$date . ' | ' . __FILE__ . ' | ' . __FUNCTION__ . ' | ' . $e->getMessage() . "\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }
    }

    public function getCuts($data = null)
    {
        $db = $this->getDbo();
        $query = $db->getQuery(true);
        $query->from("`#__gm_ceiling_calculations` as c")
            ->join("Left", "`#__gm_ceiling_projects` as p ON p.id = c.project_id")
            ->join("Left", "`#__gm_ceiling_canvases` as s ON s.id = c.n3")
            ->join("Left", "`#__gm_ceiling_textures` as t ON t.id = s.texture_id")
            ->join("Left", "`#__gm_ceiling_colors` as r ON r.id = s.color_id")
            ->join("Left", "`#__gm_ceiling_cuttings` as cut ON cut.id = c.id")
            ->where("p.project_status IN (5, 7)")
            ->where("cut.ready = 0 OR cut.ready IS NULL")
            ->order("p.quickly, p.ready_time, p.project_calculation_date, s.name, s.texture_id, s.width")
            ->select("p.quickly, p.ready_time, p.project_status, p.id as project")
            ->select("c.id as id, c.calculation_title as title, c.n3 as canvas_id, c.n4 as perimeter, c.n5 as quad, c.n9 as angles, c.calc_data, c.cut_data, c.offcut_square as square")
            ->select("s.name as name, s.country as country, s.width as width, t.texture_title as texture, r.title as color");

        if (!empty($data) && $data->type == "get") $query->where("$data->name = '$data->value'");
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
            $calc->canvas_name = $calc->name . " " .$calc->country . " " .$calc->width . " " .$calc->texture . ((empty($calc->color))?"":" ".$calc->color);
            $calc->cut_pdf = '/costsheets/' . md5($calc->id . 'cutpdf' . -2) . '.pdf';
            $calc->cut_image = "/cut_images/".md5("cut_sketch" . $calc->id).".png";
            $calc->quad = floatval($calc->quad);
            $calc->perimeter = floatval($calc->perimeter);
            $calc->square = floatval($calc->square);
            $calc->percent = round((($calc->quad + $calc->square)*$calc->square)/100.0, 2);
            $calc->angles = intval($calc->angles);
            $splitCalcData = preg_split("/;/", $calc->calc_data);
            $split = preg_split("/Полотно\d{1,5}:/", $calc->cut_data);
            unset($split[0]);
            preg_match_all("/Полотно\d{1,5}:/", $calc->cut_data, $preg);
            $cut_data = [];
            foreach ($split as $key => $value)
                $cut_data[] = (object) ["title" => $preg[0][$key - 1], "data" => $value];
            $calc->cut_data = $cut_data;
            $calc->calc_data = implode("; ", $splitCalcData);

            if ($calc->project_status == 7) $calc->status = "Собран";
            else $calc->status = "Раскроен";

            $calc->works = [];
            $calc->sumWork = 0.0;
            foreach ($works as $work) {
                $workTemp = (object) [];
                $workTemp->name = $work->name;
                $workTemp->unit = $work->unit;
                $flag = false;
                switch (intval($work->id))
                {
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

                if (!$flag)
                {
                    $calc->works[] = $workTemp;
                    $calc->sumWork += $workTemp->sum;
                }
            }


            $calc->quickly = (empty($calc->quickly) || $calc->quickly == 0)?"B":"A";
            $calc->ready_time = (empty($calc->ready_time))?date("d.m.Y H:i", strtotime(' +1 hour +'.(string)$minute.' minute')):date("d.m.Y H:i", strtotime($calc->ready_time));
            $name = ((empty($calc->ready_time))?date("Ymd", strtotime(' +1 hour +'.(string)$minute.' minute')):date("Ymd", strtotime($calc->ready_time)))
                .$calc->quickly
                .((empty($calc->ready_time))?date("Hi", strtotime(' +1 hour +'.(string)$minute.' minute')):date("Hi", strtotime($calc->ready_time)));

            if (empty($calculationsTemp[$name]))
            {
                $calculationsTemp[$name] = (object)[];
                $calculationsTemp[$name]->date = $calc->ready_time;
                $calculationsTemp[$name]->quickly = $calc->quickly;
                $calculationsTemp[$name]->id = $name;
                $calculationsTemp[$name]->name = ((string)$calc->ready_time).(($calc->quickly == "A") ? " - Срочно" : "");
                $calculationsTemp[$name]->I = [];
            }
            if (empty($calculationsTemp[$name]->I[$calc->canvas_id]))
            {
                $calculationsTemp[$name]->I[$calc->canvas_id] = (object)[];
                $calculationsTemp[$name]->I[$calc->canvas_id]->canvases = $calc->canvas_name;
                $calculationsTemp[$name]->I[$calc->canvas_id]->I = [];

            }

            $calculationsTemp[$name]->I[$calc->canvas_id]->I[] = $calc;
        }
        ksort($calculationsTemp);

        return $calculationsTemp;
    }

    public function getEmployees($id = null)
    {
        $db = $this->getDbo();
        $query = $db->getQuery(true);
        $query->select('user.id, user.name')
            ->from("`#__users` as user")
            ->join("LEFT", "`#__user_usergroup_map` as map ON map.user_id = user.id")
            ->where("map.group_id = '18'");
        if (!empty($id)) $query->where("user.id = '$id'");
        $db->setQuery($query);
        return (empty($id))?$db->loadObjectList():$db->loadObject();
    }

    public function sendWork($data)
    {
        return;
    }
}