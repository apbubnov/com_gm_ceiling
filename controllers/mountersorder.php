<?php

/**
 * @version    CVS: 0.1.7
 * @package    Com_Gm_ceiling
 * @author     SpectralEye <Xander@spectraleye.ru>
 * @copyright  2016 SpectralEye
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;

/**
 * Project controller class.
 *
 * @since  1.6
 */
class Gm_ceilingControllerMountersorder extends JControllerLegacy {

	public function GetData() {}

	public function GetDates() {
		try
		{
			$id = $_POST['url_proj'];
			$stage = $_POST['stage'];
			$model_request = null;
			$model = $this->getModel('Mountersorder', 'Gm_ceilingModel');
			if(!empty($id)){
				$model_request = $model->GetDates($id, $stage);
			}

			die(json_encode($model_request));
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
	}

	public function MountingStart() {
		try
		{
			$jinput = JFactory::getApplication()->input;
			$id = $jinput->get("url_proj","","STRING");
			$date = $jinput->get("date","","STRING");
			$stage = $jinput->get('stage', null, 'INT');
			$model = $this->getModel('Mountersorder', 'Gm_ceilingModel');
			$model_request = $model->MountingStart($id, $date, $stage);
			$server_name = $_SERVER['SERVER_NAME'];
			// письмо
			$emails = $model->AllNMSEmails();
			$DataOrder = $model->DataOrder($id, $stage);
			$NamesMounters = $model->NamesMounters($DataOrder[0]->project_mounter);
			$mailer = JFactory::getMailer();
			$config = JFactory::getConfig();
			$sender = array(
				$config->get('mailfrom'),
			 	$config->get('fromname')
			);
			$mailer->setSender($sender);
			foreach ($emails as $value) {
				$mailer->addRecipient($value->email);
			}
			$body = "Здравствуйте. \n";
			$body .= "Проект №$id перешел в статус \"Монтаж\".\n";
			$body .= "\n";
			$body .= "Монтажная Бригада: ".$DataOrder[0]->project_mounter_name." (";
			foreach ($NamesMounters as $value) {
				$names .= "$value->name, ";
			}
			$body .= substr($names, 0, -2);
			$body .= ").\n";
			$body .= "Адрес: ".$DataOrder[0]->project_info."\n";
			$body .= "Дата и время монтажа: ".substr($DataOrder[0]->project_mounting_date,8, 2).".".substr($DataOrder[0]->project_mounting_date,5, 2).".".substr($DataOrder[0]->project_mounting_date,0, 4)." ".substr($DataOrder->project_mounting_date,11, 5)." \n";
			$body .= "Чтобы перейти на сайт, щелкните здесь: <a href=\"http://$server_name\">http://$server_name</a>";
			$mailer->setSubject('Новый статус монтажа');
			$mailer->setBody($body);
			$send = $mailer->Send();

			echo json_encode($model_request);

			exit;
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
	}

	public function MountingComplited() {
		try
		{
			$jinput = JFactory::getApplication()->input;
			$id = $jinput->get("url_proj","","STRING");
			$date = $jinput->get("date","","STRING");
			$note = $jinput->get("note","","STRING");
			$stage = $jinput->get('stage', null, 'INT');

			if (!empty($note)) {
				$note2 = "Монтаж по проекту №$id выполнен. Примечание от монтажной бригады: ".$note;			
			} else {
				$note2 = "Монтаж выполнен. Примечание от монтажной бригады: отсутствует";
			}

			switch ($stage) {
				case 1:
					$status = 11;
					break;
				case 2:
					$status = 24;
					break;
				case 3:
					$status = 25;
					break;
				case 4:
					$status = 26;
					break;
				default:
					$status = 17;
					break;
			}

			$model = $this->getModel('Mountersorder', 'Gm_ceilingModel');
			$model_request = $model->MountingComplited($id, $date, $note2, $note, $status, $stage);

			// письмо
			$emails = $model->AllNMSEmails();
			$DataOrder = $model->DataOrder($id, $stage);
			$NamesMounters = $model->NamesMounters($DataOrder[0]->project_mounter);
			$mailer = JFactory::getMailer();
			$config = JFactory::getConfig();
			$sender = array(
				$config->get('mailfrom'),
				$config->get('fromname')
			);

			$mailer->setSender($sender);
			foreach ($emails as $value) {
				$mailer->addRecipient($value->email);
			}
			$body = "Здравствуйте.\n";
			$body .= "Проект №$id перешел в статус \"Монтаж закончен\".\n";
			$body .= "\n";
			$body .= "Монтажная Бригада: ".$DataOrder[0]->project_mounter_name." (";
			foreach ($NamesMounters as $value) {
				$names .= "$value->name, ";
			}
			$body .= substr($names, 0, -2);
			$body .= ").\n";
			$body .= "Адреc: ".$DataOrder[0]->project_info."\n";
			$body .= "Дата и время: ".substr($DataOrder[0]->project_mounting_date,8, 2).".".substr($DataOrder[0]->project_mounting_date,5, 2).".".substr($DataOrder[0]->project_mounting_date,0, 4)." ".substr($DataOrder->project_mounting_date,11, 5)." \n";
			if (strlen($note) != 0) {
				$body .= "Примечание монтажника: ".$note."\n";			
			}
			$body .= "Чтобы перейти на сайт, щелкните здесь: <a href=\"http://$server_name/\">http://$server_name</a>";		
			$mailer->setSubject('Новый статус монтажа');
			$mailer->setBody($body);
			$send = $mailer->Send();

			echo json_encode($model_request);

			exit;
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
	}

	public function MountingUnderfulfilled() {
		try
		{
			$id = $_POST["url_proj"];
			$note = $_POST["note"];
			$date = $_POST["date"];

			$note = "Монтаж по проекту №$id недовыполнен. Примечание от монтажной бригады: ".$note;

			$model = $this->getModel('Mountersorder', 'Gm_ceilingModel');
			$model_request = $model->MountingUnderfulfilled($id, $date, $note);

			// письмо
			$emails = $model->AllNMSEmails();
			$DataOrder = $model->DataOrder($id);
			$NamesMounters = $model->NamesMounters($DataOrder[0]->project_mounter);		
			$mailer = JFactory::getMailer();
			$config = JFactory::getConfig();
			$sender = array(
				$config->get('mailfrom'),
				$config->get('fromname')
			);

			$mailer->setSender($sender);
			foreach ($emails as $value) {
				$mailer->addRecipient($value->email);
			}
			$body = "Здравствуйте. \n";
			$body .= "Проект №$id перешел в статус \"Монтаж незавершен\".\n";
			$body .= "\n";
			$body .= "Монтажная Бригада: ".$DataOrder[0]->project_mounter_name." (";
			foreach ($NamesMounters as $value) {
				$names .= "$value->name, ";
			}
			$body .= substr($names, 0, -2);
			$body .= ").\n";
			$body .= "Адрес: ".$DataOrder[0]->project_info."\n";
			$body .= "Дата и время: ".substr($DataOrder[0]->project_mounting_date,8, 2).".".substr($DataOrder[0]->project_mounting_date,5, 2).".".substr($DataOrder[0]->project_mounting_date,0, 4)." ".substr($DataOrder->project_mounting_date,11, 5)." \n";
			$body .= "Примечание монтажника: ".$note."\n";
			$body .= "Чтобы перейти на сайт, щелкните здесь: <a href=\"http://$server_name/\">http://$server_name</a>";				
			$mailer->setSubject('Новый статус монтажа');
			$mailer->setBody($body);
			$send = $mailer->Send();

			echo json_encode($model_request);

			exit;
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
	}

}

