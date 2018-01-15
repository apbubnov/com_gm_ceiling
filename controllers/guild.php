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
        die(json_encode((object) ["status" => "success", "calendar" => Gm_ceilingHelpersGm_ceiling::LiteCalendar($month)]));
    }
}