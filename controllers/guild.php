<?php
/**
 * @version    CVS: 0.1.7
 * @package    Com_Gm_ceiling
 * @author     CEH4TOP <CEH4TOP@gmail.com>
 * @copyright  2017 CEH4TOP
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

/**
 * Stock list controller class.
 *
 * @since  1.6
 */
class Gm_ceilingControllerGuild extends JControllerLegacy
{
    public function &getModel($name = 'Guild', $prefix = 'Gm_ceilingModel', $config = array())
    {
        try {
            $model = parent::getModel($name, $prefix, array('ignore_request' => true));

            return $model;
        } catch (Exception $e) {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
    }

    public function testCuts()
    {
        try {
            $app = JFactory::getApplication();
            $cuts = $app->input->get('cuts', array(), 'array');

            $cursTemp = array();
            foreach ($cuts as $k => $v) if ($v != "") $cursTemp[] = $v;

            $model = $this->getModel();
            $cuts = ($model->getCuts((object) ["type" => "test", "data" => $cursTemp]));

            die(json_encode($cuts));
        } catch (Exception $e) {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
    }

    public function getCut()
    {
        try {
            $app = JFactory::getApplication();
            $id = $app->input->get('id', null, 'int');

            $model = $this->getModel();
            $cut = ($model->getCuts((object) ["type" => "get", "name" => "c.id", "value" => $id]));

            die(json_encode($cut));
        } catch (Exception $e) {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
    }

    public function sendWork()
    {
        try {
            $date = date("Y-m-d H:i:s");
            $app = JFactory::getApplication();
            $data = $_POST;
            unserialize($data);
            $data['Data'] = json_decode($data['Data']);
            $data = json_decode(json_encode($data));

            if (empty($data))
                die(json_encode((object) ["status" => "error", "message" => "Переданы неверные данные!"]));
            else {
                $model = $this->getModel();
                $cut = ($model->getCuts((object) ["type" => "get", "name" => "c.id", "value" => $data->Data->id]));

                foreach ($cut as $v) $cut = $v;
                foreach ($cut->I as $v) $cut = $v;
                foreach ($cut->I as $v) $cut = $v;

                if (empty($cut))
                    die(json_encode((object) ["status" => "error", "message" => "Переданы неверные данные!"]));

                try {
                    $model->sendWork((object)["employees" => $data->employees, "calculations" => $cut, "date" => $date]);
                    die(json_encode((object) ["status" => "success", "message" => "Ваша работа успешно учтена!"]));
                } catch (Exception $ex) {
                    die(json_encode((object) ["status" => "error", "message" => $ex->getMessage()]));
                }
            }
        } catch (Exception $e) {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
    }

    public function getCalendar()
    {
        try
        {
            $app = JFactory::getApplication();
            $month = $app->input->get('month', 0, 'int');
            $year = $app->input->get('year', 0, 'int');
            die(json_encode((object) ["status" => "success", "calendar" => Gm_ceilingHelpersGm_ceiling::LiteCalendar($month, $year)]));
        }
        catch(Exception $e)
        {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function getData()
    {
        try
        {
            $app = JFactory::getApplication();

            $Type = $app->input->get('Type', null, 'string');

            if (gettype($Type) != "array")
                $Type = [$Type];


            $DateStart = $app->input->get('DateStart', null, 'string');
            $DateEnd = $app->input->get('DateEnd', null, 'string');
            $Date = $app->input->get('Date', null, 'string');

            $Day = $app->input->get('Day', null, 'int');
            $Month = $app->input->get('Month', null, 'int');
            $Year = $app->input->get('Year', null, 'int');

            $User = $app->input->get('User', null, 'int');

            $data = (object) [];

            if (!empty($DateStart))
                $data->DateStart = $DateStart;

            if (!empty($DateEnd))
                $data->DateEnd = $DateEnd;

            if (!empty($Date))
            {
                $Date = DateTime::createFromFormat("Y-m-d H:i:s", $Date);

                $day = $Date->format("d");
                $month = $Date->format("m");
                $year = $Date->format("Y");

                $day = intval($day);
                $month = intval($month);
                $year = intval($year);

                $data->DateStart = date("Y-m-d H:i:s",  mktime(0, 0, 0, $month, $day, $year));
                $data->DateEnd = date("Y-m-d H:i:s",  mktime(0, 0, -1, $month, $day + 1, $year));
            }
            if (!empty($Day) || !empty($Month) || !empty($Year))
            {
                $Day = intval($Day);
                $Month = intval($Month);
                $Year = intval($Year);

                $data->DateStart = date("Y-m-d H:i:s",  mktime(0, 0, 0, $Month, $Day, $Year));
                $data->DateEnd = date("Y-m-d H:i:s",  mktime(0, 0, -1, $Month, $Day + 1, $Year));
            }
            if (!empty($User))
                $data->user_id = $User;

            $model = $this->getModel();

            $answer = [];

            if (in_array("Working", $Type)) $answer["Working"] = $model->getWorking($data);
            if (in_array("Employee", $Type)) $answer["Employee"] = $model->getBigDataEmployees($data);
            if (in_array("EmployeeWorking", $Type)) $answer["EmployeeWorking"] = $model->getWorkingEmployees($data);

            if (count($answer) == 1)
                foreach ($answer as $a) $answer = $a;
            else
            {
                $answer = json_decode(json_encode($answer));
                $answer->status = "success";
                $answer->message = "Данные успешно получены";
            }

            die(json_encode($answer));
        }
        catch(Exception $e)
        {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function setWorking()
    {
        try
        {
            $app = JFactory::getApplication();

            $user_id = $app->input->get('user_id', null, 'string');
            $date = $app->input->get('date', null, 'string');
            $action = $app->input->get('action', 0, 'int');

            $model = $this->getModel();

            try {
                $model->setWorking((object)["user_id" => $user_id, "date" => $date, "action" => $action]);
            }
            catch (Exception $ex) { die(json_encode((object)["status"=>"error", "message"=>$ex->getMessage()])); }

            die(json_encode((object)["status"=>"success", "message"=>"Успешно выполнено!"]));
        }
        catch(Exception $e)
        {
            add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
}