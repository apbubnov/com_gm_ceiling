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
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files . 'error_log.txt', (string)$date . ' | ' . __FILE__ . ' | ' . __FUNCTION__ . ' | ' . $e->getMessage() . "\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
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
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files . 'error_log.txt', (string)$date . ' | ' . __FILE__ . ' | ' . __FUNCTION__ . ' | ' . $e->getMessage() . "\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
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
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files . 'error_log.txt', (string)$date . ' | ' . __FILE__ . ' | ' . __FUNCTION__ . ' | ' . $e->getMessage() . "\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
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
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files . 'error_log.txt', (string)$date . ' | ' . __FILE__ . ' | ' . __FUNCTION__ . ' | ' . $e->getMessage() . "\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }
    }

    public function getCalendar()
    {
        $app = JFactory::getApplication();
        $month = $app->input->get('month', 0, 'int');
        $year = $app->input->get('year', 0, 'int');
        die(json_encode((object) ["status" => "success", "calendar" => Gm_ceilingHelpersGm_ceiling::LiteCalendar($month, $year)]));
    }

    public function getWorking()
    {
        $app = JFactory::getApplication();

        $DateStart = $app->input->get('DateStart', null, 'string');
        $DateEnd = $app->input->get('DateEnd', null, 'string');
        $Date = $app->input->get('Date', null, 'string');

        $Day = $app->input->get('Day', null, 'int');
        $Month = $app->input->get('Month', null, 'int');
        $Year = $app->input->get('Year', null, 'int');

        $User = $app->input->get('User', null, 'int');

        $data = (object) [];

        if (!empty($DateStart))
            $data->DateStart = date("Y.m.d H:i:s", strtotime($DateStart));
        if (!empty($DateEnd))
            $data->DateEnd = date("Y.m.d H:i:s", strtotime($DateEnd));
        if (!empty($Date))
        {
            $day = date("d", strtotime($Date));
            $month = date("m", strtotime($Date));
            $year = date("Y", strtotime($Date));

            $data->DateStart = date("Y.m.d H:i:s",  mktime(0, 0, 0, $month, $day, $year));
            $data->DateEnd = date("Y.m.d H:i:s",  mktime(0, 0, -1, $month, $day + 1, $year));
        }
        if (!empty($Day) || !empty($Month) || !empty($Year))
        {
            $data->DateStart = date("Y.m.d H:i:s",  mktime(0, 0, 0, $Month, $Day, $Year));
            $data->DateEnd = date("Y.m.d H:i:s",  mktime(0, 0, -1, $Month, $Day + 1, $Year));
        }
        if (!empty($User))
            $data->user_id = $User;

        $model = $this->getModel();

        die(json_encode($model->getWorking($data)));
    }
}