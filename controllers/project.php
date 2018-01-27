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
class Gm_ceilingControllerProject extends JControllerLegacy
{

	public function GetBusyMounters() {
		try
		{
	        $date = $_POST["date"];
	        
			$model = Gm_ceilingHelpersGm_ceiling::getModel('project');
			$mounters = $model->FindBusyMounters($date, $date);

			die(json_encode($mounters));
		}
		catch(Exception $e)
        {
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }
	}

	public function UpdateDateMountBrigade() {
		try
		{
			$id = $_POST["id_project"];
	        $datatime = $_POST["date"];
			$mounter = $_POST["mounter"];
			$oldmounter = $_POST["oldmounter"];
			$olddatetime = $_POST["olddatetime"];
			$id_client = $_POST["id_client"];;
			
			$model = Gm_ceilingHelpersGm_ceiling::getModel('project');
			$successupdate = $model->UpdateDateMountBrigade($id, $datatime, $mounter);
			
			// комменты
			if ($datatime != $olddatetime) {
				$data = ["datatime" => $datatime, "olddatetime" => $olddatetime];
				$model->AddCommentManager(1, $data, $id_client);
			}
			if ($mounter != $oldmounter) {
				$model->AddCommentManager(2, $mounter, $id_client);
			}

			die(json_encode($successupdate));
		}
		catch(Exception $e)
        {
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }
	}

	/**
	 * Method to check out an item for editing and redirect to the edit form.
	 *
	 * @return void
	 *
	 * @since    1.6
	 */
	public function edit()
	{
		try
		{
			$app = JFactory::getApplication();

			// Get the previous edit id (if any) and the current edit id.
			$previousId = (int) $app->getUserState('com_gm_ceiling.edit.project.id');
			$editId     = $app->input->getInt('id', 0);

			// Set the user id for the user to edit in the session.
			$app->setUserState('com_gm_ceiling.edit.project.id', $editId);

			// Get the model.
			$model = $this->getModel('Project', 'Gm_ceilingModel');

			/*// Check out the item
			if ($editId)
			{
				$model->checkout($editId);
			}

			// Check in the previous user.
			if ($previousId && $previousId !== $editId)
			{
				$model->checkin($previousId);
			}*/

			// Redirect to the edit screen.
			$this->setRedirect(JRoute::_('index.php?option=com_gm_ceiling&view=projectform&layout=edit', false));
		}
		catch(Exception $e)
        {
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }
	}

	public function recToMeasure()
    {
        try {
            $app = JFactory::getApplication();
            $user = JFactory::getUser();

            $model = $this->getModel('Project', 'Gm_ceilingModel');

            extract($_POST); // Элементы массива в переменные

            $data = $model->getData($project_id);

            if (empty($new_discount)) $new_discount = $data->project_discount;

            $emails = (empty($email_str))?[]:explode(";", $email_str);
            array_pop($emails);

            if (isset($data_delete) && isset($idCalcDelete)) {
                $model_calc = Gm_ceilingHelpersGm_ceiling::getModel('calculations');
                $resultDel = $model_calc->delete($idCalcDelete);
                if ($resultDel == 1) {
                    $this->setMessage("Потолок удален");
                    if(!empty($_SESSION['url'])) $this->setRedirect(JRoute::_($_SESSION['url'], false));
                    else $this->setRedirect(JRoute::_($_SERVER['HTTP_REFERER'], false));
                }

            } else {

            }
        } catch(Exception $e)
        {
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }
    }

	//запись на замер при входящем звонке
	public function recToMeasurement()
	{
		try {
            $app = JFactory::getApplication();
			$user = JFactory::getUser();
			$user_group = $user->groups;
			if (in_array("16", $user_group)) {
				$usertype = "gmmanagermainpage"; 
			} else {
				$usertype = "managermainpage";
			}
			$model = $this->getModel('Project', 'Gm_ceilingModel');
            $jinput = JFactory::getApplication()->input;
            $project_id = $jinput->get('project_id', '0', 'INT');
            $data = $model->getData($project_id);
            $type = $jinput->get('type', '', 'STRING');
            $subtype = $jinput->get('subtype', '', 'STRING');
            $new_discount = $jinput->get('new_discount', $data->project_discount, 'RAW');
            $call_id = $jinput->get('call_id', 0, 'INT');
            $isDiscountChange = $jinput->get('isDiscountChange', '0', 'INT');
            $isDataChange = $jinput->get('data_change', '0', 'INT');
            $client_id = $jinput->get('client_id', 1, 'INT');
            $api_phone_id = $jinput->get('advt_id', '0', 'INT');
            $selected_advt = $jinput->get('selected_advt', '0', 'INT');
            $call_type = $jinput->get('slider-radio', 'client', 'STRING');
            $recoil = $jinput->get('recoil', '', 'STRING');
            $sex = $jinput->get('slider-sex', "NULL", 'STRING');
            $email_str = $jinput->get('emails', "", "STRING");
			$without_advt = $jinput->get('without_advt', 0, 'INT');
			$client_form_model = $this->getModel('ClientForm', 'Gm_ceilingModel');
			$client_model = $this->getModel('client', 'Gm_ceilingModel');
            $user_model = $this->getModel('users', 'Gm_ceilingModel');
            $emails = [];
            if (!empty($email_str)) {
                $emails = explode(";", $email_str);
            }
            array_pop($emails);
            $isDataDelete = $jinput->get('data_delete', '0', 'INT');
            if ($isDataDelete) {
                $idCalc = $jinput->get('idCalcDelete', '0', 'INT');
                //print_r($idCalc); exit;
                $model_calc = Gm_ceilingHelpersGm_ceiling::getModel('calculations');
                $resultDel = $model_calc->delete($idCalc);
                //print_r($_SESSION['url']."test"); exit;
                if ($resultDel == 1) {
                    $this->setMessage("Потолок удален");
                    if(!empty($_SESSION['url'])) $this->setRedirect(JRoute::_($_SESSION['url'], false));
                    else $this->setRedirect(JRoute::_($_SERVER['HTTP_REFERER'], false));
                }

            } else {
                if ($api_phone_id == 0 && $without_advt == 0) {
                    if ($selected_advt != 0) {
                        $api_phone_id = $selected_advt;
                    } else {
                        $api_phone_id = NULL;
                        if ($client_id != 1) {
                            throw new Exception("Пожалуйста, укажите рекламу!");
                        }
                    }
                } elseif ($without_advt == 1) {
                    $api_phone_id = NULL;
                }

                $comments_string = $jinput->get('comments_id', '', 'STRING');
                $call_comment = $jinput->get('call_comment', "Отсутствует", 'STRING');
                $call_date = $jinput->get('call_date', "0", 'STRING');
                $comments_id = [];
                $client_history_model = $this->getModel('Client_history', 'Gm_ceilingModel');
                $status = $jinput->get('status', '0', 'INT');
                $gauger = null;
                $date = $jinput->get('project_new_calc_date', '', 'STRING');
                $time = $jinput->get('new_project_calculation_daypart', '0', 'STRING');
                $date_time = $date . " " . $time;
                switch ($status) {
                    case 1 :
                        $gauger = $jinput->get('project_gauger', '', 'STRING');
                        $result = "записан на замер";
                        $answer = "записан на замер на " . $date_time;
                        break;
                    case 2 :
                        $result = "переведен в статус отказа от замера";
                        break;
                    case 15:
                        $result = "переведен в статус отказа от сотрудничества";
                        break;
                    default:
                        $result = "переведен в статус просчета";
                        break;
                }
                if (!empty($comments_string))
                    $comments_id = explode(";", $comments_string);
                array_pop($comments_id);
                if ($isDiscountChange) {
                    if ($model->change_discount($project_id, $new_discount)) {

                        if (!empty($_SESSION['url'])) {
                            $this->setMessage("Процент скидки успешно изменен!");
                            //header('Location: '.$_SESSION['url'] ); exit();
                            $this->setRedirect(JRoute::_($_SESSION['url'], false));
                            //$this->setRedirect(JRoute::_('index.php?option=com_gm_ceiling&view=mainpage&type=gmmanagermainpage', false));
                        }
                    }
                }
                $name = $jinput->get('new_client_name', '', 'STRING');
                $phones = $jinput->get('new_client_contacts', array(), 'ARRAY');
                foreach ($phones as $key => $value) {
                    $phones[$key] = preg_replace('/[\(\)\-\+\s]/', '', $value);
                }

                $street = $jinput->get('new_address', '', 'STRING');
                $house = $jinput->get('new_house', '', 'STRING');
                $bdq = $jinput->get('new_bdq', '', 'STRING');
                $apartment = $jinput->get('new_apartment', '', 'STRING');
                $porch = $jinput->get('new_porch', '', 'STRING');
                $floor = $jinput->get('new_floor', '', 'STRING');
                $code = $jinput->get('new_code', '', 'STRING');
                if (!empty($house)) $address = $street . ", дом: " . $house;
                if (!empty($bdq)) $address .= ", корпус: " . $bdq;
                if (!empty($apartment)) $address .= ", квартира: " . $apartment;
                if (!empty($porch)) $address .= ", подъезд: " . $porch;
                if (!empty($floor)) $address .= ", этаж: " . $floor;
                if (!empty($code)) $address .= ", код: " . $code;

                $cl_phones_model = $this->getModel('Client_phones', 'Gm_ceilingModel');
                $manager_comment = $jinput->get('gmmanager_note', '', 'STRING');


                if ($client_id == 1 && $isDiscountChange == 0)
                {
                    $client_found_bool = false;
                    foreach($phones as $phone)
                    {   
                        $old_client = $cl_phones_model->getItemsByPhoneNumber($phone, $user->dealer_id);
                        if (!empty($old_client))
                        {
                            $client_found_bool = true;
                            break;
                        }
                    }
                    if ($client_found_bool)
                    {
                        $client_id = $old_client->id;
                        if ($old_client->client_name == 'Безымянный' || $old_client->client_name == '')
                        {
                            $client_model->updateClient($client_id, $name, $user->dealer_id);
                            $client_model->updateClientManager($client_id, $user->id);
                            $client_model->updateClientSex($client_id, $sex);
                        }
                    }
                    else
                    {
                        $client_data['client_name'] = $name;
                        $client_data['type_id'] = 1;
                        $client_data['manager_id'] = $user->id;
                        $client_data['dealer_id'] = $user->dealer_id;
                        $client_data['sex'] = $sex;
                        $client_id = $client_form_model->save($client_data);
                    }
                    
                    //обновление email
                    $dop_contacts = $this->getModel('clients_dop_contacts', 'Gm_ceilingModel');
                    $dop_contacts->update_client_id($emails, $client_id);
                    // добавление проекта к откатнику
                    if ($api_phone_id == 17) {
                        $rec_model = $this->getModel('recoil_map_project', 'Gm_ceilingModel');
                        $rec_model->save($recoil, $project_id, 0);
                    }
                    //добавление его номеров телефонов в бд
                    $cl_phones_model->save($client_id, $phones);
                    //обновление комментов к клиенту
                    if (count($comments_id) != 0) {
                        $client_history_model->updateClientId($client_id, $comments_id);
                    }
                    if ($call_type == "client") {
                        //обновление созданного проекта
                        $model->update_project_after_call($project_id, $client_id, $date_time, $address, $manager_comment, $status, $api_phone_id, $user->id, $gauger);
						if (!empty($answer))
						{
							$client_history_model->save($client_id, "Проект № " . $project_id . " " . $answer);
						}
						else
						{
							$client_history_model->save($client_id, "Проект № " . $project_id . " " . $result);
						}
                        //добавление звонка
                        if ($call_date != "") {
                            $callback_model = $this->getModel('callback', 'Gm_ceilingModel');
                            $callback_model->save($call_date, $call_comment, $client_id, $user->id);
                            //добавление в историю что добавлен звонок
                            $client_history_model->save($client_id, "Добавлен новый звонок. Примечание: $call_comment");
                        }
                    } elseif ($call_type == "promo") {
                        $client_history_model->save($client_id, "Клиент помечен как реклама.");
                        $model->update_project_after_call($project_id, $client_id, $date_time, $address, $manager_comment, 21, $api_phone_id, $user->id, $gauger);
                        $this->setMessage("Клиент помечен как реклама");
                        $status = 21;
                    } elseif ($call_type == "dealer") {
                        $dop_contacts = Gm_ceilingHelpersGm_ceiling::getModel('clients_dop_contacts');
                        $emails = $dop_contacts->getEmailByClientID($client_id);
                        if (count($emails) != 0) {
                            $email = $emails[0];
                        } else {
                            $email = "$client_id@$client_id";
                        }
                        //зарегать как user
                        $userID = Gm_ceilingHelpersGm_ceiling::registerUser($name, preg_replace('/[\(\)\-\s]/', '', array_shift($phones)), $email, $client_id);

                        $client_model->updateClient($client_id, null, $userID);

                        $info_model = Gm_ceilingHelpersGm_ceiling::getModel('dealer_info');
                        $dealer_canvases_margin = $info_model->getMargin('dealer_canvases_margin', $userID);
                        $dealer_components_margin = $info_model->getMargin('dealer_components_margin', $userID);
                        $dealer_mounting_margin = $info_model->getMargin('dealer_mounting_margin', $userID);
                        $gm_canvases_margin = $info_model->getMargin('gm_canvases_margin', $userID);
                        $gm_components_margin = $info_model->getMargin('gm_components_margin', $userID);
                        $gm_mounting_margin = $info_model->getMargin('gm_mounting_margin', $userID);

                        $client_history_model->save($client_id, "Клиент переведен в дилеры.");
                        $model->update_project_after_call($project_id, $client_id, $date_time, $address, $manager_comment, 20, $api_phone_id, $user->id, $gauger, $dealer_canvases_margin, $dealer_components_margin,
                            $dealer_mounting_margin, $gm_canvases_margin, $gm_components_margin, $gm_mounting_margin);
                        $status = 20;
                    }
                    if ($client_found_bool)
                    {
                        if ($status == 0)
                        {
                            $model->delete($project_id);
                        }
                        else
                        {
                            $client_projects = $model->getProjectsByClientID($client_id);
                            foreach ($client_projects as $key => $project)
                            {
                                if ($project->project_status == 0) {
                                    $model->delete($project->id);
                                }
                            }
                        }
                    }
                    if ($call_type == "client") {
                        $this->setMessage("Клиент создан и $result!");
                    }
                }
                elseif ($client_id != 1 && $isDiscountChange == 0)
                {
                    $new_phones = [];
                    $change_phones = [];
                    $newFIO = $jinput->get('new_client_name', '', 'STRING');
                    foreach ($phones as $key => $value) {
                        if (strlen($key) < 3) {
                            array_push($new_phones, $value);
                        } else {
                            if ($key != $value) {
                                $change_phones[$key] = $value;
                            }
                        }
                    }

                    if (!empty($newFIO)) {
                        if ($newFIO != $data->client_id) {
                            $client_model->updateClient($client_id, $newFIO);
                            $user_model->updateUserNameByAssociatedClient($client_id, $newFIO);
                            $client_history_model->save($client_id, "Изменено ФИО пользователя");
                        }
                    }
                    $client_model->updateClientSex($client_id, $sex);
                    if ($call_type == "client") {
                        if (count($new_phones) > 0) {
                            $cl_phones_model->save($client_id, $new_phones);
                        }

                        if (count($change_phones) > 0) {
                            $cl_phones_model->update($client_id, $change_phones);
                        }
                        if ($api_phone_id == 17) {
                            $rec_model = $this->getModel('recoil_map_project', 'Gm_ceilingModel');
                            $rec_model->save($recoil, $project_id, 0);
                        }
                        $rep_model = Gm_ceilingHelpersGm_ceiling::getModel('repeatrequest');
                        $rep_proj = $rep_model->getDataByProjectId($project_id);
                        if (empty($rep_proj) || $without_advt == 1) {
                            // условия на статус
                            $model->update_project_after_call($project_id, $client_id, $date_time, $address, $manager_comment, $status, $api_phone_id, $user->id, $gauger);
                        } else {
                            // условия на статус
                            $model->update_project_after_call($project_id, $client_id, $date_time, $address, $manager_comment, $status, 10, $user->id, $gauger);
                            $rep_upd = $rep_model->update($project_id, $api_phone_id);
                        }

                        $callback_model = $this->getModel('callback', 'Gm_ceilingModel');
                        if ($call_id != 0) {
                            if ($call_date != "") {
                                $callback_model->updateCall($call_id, $call_date, $call_comment);
                            } elseif ($call_date == "") {
                                $callback_model->deleteCall($call_id);
                            }
                        } else {
                            if ($call_date != "") {
                                $callback_model->save($call_date, $call_comment, $client_id, $user->id);
                                $client_history_model->save($client_id, "Добавлен новый звонок. Примечание: $call_comment");
                            }
                        }
                        if (!empty($answer)) $client_history_model->save($client_id, "Проект № " . $project_id . " " . $answer);
                        else $client_history_model->save($client_id, "Проект № " . $project_id . " " . $result);
                    } elseif ($call_type == "promo") {
                        $client_history_model->save($client_id, "Клиент помечен как реклама");

                        $rep_model = Gm_ceilingHelpersGm_ceiling::getModel('repeatrequest');
                        $rep_proj = $rep_model->getDataByProjectId($project_id);
                        if (empty($rep_proj) || $without_advt == 1) {
                            $model->update_project_after_call($project_id, $client_id, $date_time, $address, $manager_comment, 21, $api_phone_id, $user->id, $gauger);
                        } else {
                            $model->update_project_after_call($project_id, $client_id, $date_time, $address, $manager_comment, 21, 10, $user->id, $gauger);
                            $rep_upd = $rep_model->update($project_id, $api_phone_id);
                        }

                        $model->deleteAdvtProjectsByClientId($client_id);
                        $status = 21;
                        $this->setMessage("Клиент помечен как реклама");
                    } elseif ($call_type == "dealer") {
                        //зарегать как user,
                        $dop_contacts = Gm_ceilingHelpersGm_ceiling::getModel('clients_dop_contacts');
                        $emails = $dop_contacts->getEmailByClientID($client_id);
                        if (count($emails) != 0) {
                            $email = $emails[0]->contact;
                        } else {
                            $email = "$client_id@$client_id";
                        }
                        $client_history_model->save($client_id, "Клиент переведен в дилеры.");
                        $username = preg_replace('/[\(\)\-\s]/', '', array_shift($phones));
                        if ($client_model->checkIsDealer($username) == 0) {
                            $userID = Gm_ceilingHelpersGm_ceiling::registerUser($name, $username, $email, $client_id);
                            $client_model->updateClient($client_id, null, $userID);

                            $info_model = Gm_ceilingHelpersGm_ceiling::getModel('dealer_info');
                            $dealer_canvases_margin = $info_model->getMargin('dealer_canvases_margin', $userID);
                            $dealer_components_margin = $info_model->getMargin('dealer_components_margin', $userID);
                            $dealer_mounting_margin = $info_model->getMargin('dealer_mounting_margin', $userID);
                            $gm_canvases_margin = $info_model->getMargin('gm_canvases_margin', $userID);
                            $gm_components_margin = $info_model->getMargin('gm_components_margin', $userID);
                            $gm_mounting_margin = $info_model->getMargin('gm_mounting_margin', $userID);
                        }

                        $rep_model = Gm_ceilingHelpersGm_ceiling::getModel('repeatrequest');
                        $rep_proj = $rep_model->getDataByProjectId($project_id);
                        if (empty($rep_proj) || $without_advt == 1) {
                            $model->update_project_after_call($project_id, $client_id, $date_time, $address, $manager_comment, 20, $api_phone_id, $user->id, $gauger, $dealer_canvases_margin, $dealer_components_margin,
                                $dealer_mounting_margin, $gm_canvases_margin, $gm_components_margin, $gm_mounting_margin);
                        } else {
                            $model->update_project_after_call($project_id, $client_id, $date_time, $address, $manager_comment, 20, 10, $user->id, $gauger, $dealer_canvases_margin, $dealer_components_margin,
                                $dealer_mounting_margin, $gm_canvases_margin, $gm_components_margin, $gm_mounting_margin);
                            $rep_upd = $rep_model->update($project_id, $api_phone_id);
                        }
                        $status = 20;
                    }
                    if ($call_type == "client") {
                        $this->setMessage("Клиент $result!");
                    }
                }
                if ($status == 1) {

                    $data_notify['client_name'] = $name;
                    $data_notify['client_contacts'] = $phones;
                    $data_notify['project_info'] = $address;
                    $data_notify['project_calculation_date'] = $date;
                    $data_notify['project_calculation_daypart'] = $time;
                    $data_notify['project_note'] = $manager_comment;
                    Gm_ceilingHelpersGm_ceiling::notify($data_notify, 0);
                }
                if ($data->project_status != $status) {
                    $model_projectshistory = Gm_ceilingHelpersGm_ceiling::getModel('projectshistory');
                    $model_projectshistory->save($project_id, $status);
                }

                if (!$isDiscountChange)
                {
					if($usertype=="managermainpage"){
						$this->setRedirect(JRoute::_('/index.php?option=com_gm_ceiling&task=mainpage', false));
					}
					else{
						$this->setRedirect(JRoute::_('/index.php?option=com_gm_ceiling&view=mainpage&type='.$usertype, false));
					}
                }
            }
        }
		catch(Exception $e)
        {
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }
	}

	public function run_in_production(){
		try{
			$app = JFactory::getApplication();
			$user = JFactory::getUser();
			//получение нужных моделей
			$model = $this->getModel('Project', 'Gm_ceilingModel');
			$client_history_model = $this->getModel('Client_history', 'Gm_ceilingModel');
			$cl_phones_model = $this->getModel('Client_phones', 'Gm_ceilingModel');
			$client_model =  $this->getModel('client', 'Gm_ceilingModel');
			$calculationsModel = Gm_ceilingHelpersGm_ceiling::getModel('calculations');
			//--------
			$jinput = JFactory::getApplication()->input;
			$project_id = $jinput->get('project_id', '0', 'INT');
			$data = $model->getData($project_id);
			$type = $jinput->get('type', '', 'STRING');
			$subtype = $jinput->get('subtype', '', 'STRING');
			$new_discount =  $jinput->get('new_discount',$data->project_discount, 'RAW');
			$call_id =  $jinput->get('call_id',0, 'INT');
			$isDiscountChange = $jinput->get('isDiscountChange', '0', 'INT');
			$isDataChange = $jinput->get('data_change', '0', 'INT');
			$client_id = $jinput->get('client_id', 1, 'INT');
            $sex = $jinput->get('slider-sex',"NULL",'STRING');
			$include_calculation = $jinput->get('include_calculation', '', 'ARRAY');
			$call_comment = $jinput->get('call_comment', "Отсутствует", 'STRING');
			$call_date = $jinput->get('call_date', "0", 'STRING');
			$status = $jinput->get('status','','INT');
			if($isDiscountChange){
				if($model->change_discount($project_id,$new_discount))
				{
					
					if(!empty($_SESSION['url'])){
						$this->setMessage("Процент скидки успешно изменен!");
						$this->setRedirect(JRoute::_($_SESSION['url'], false));
					}
				}
			}
			$name = $jinput->get('new_client_name', '', 'STRING');
			$phones = $jinput->get('new_client_contacts', array(), 'ARRAY');
			foreach ($phones as $key => $value) {
				$phones[$key] = preg_replace('/[\(\)\-\s]/', '', $value);
			}
			$street = $jinput->get('new_address', '', 'STRING');
			$house = $jinput->get('new_house', '', 'STRING');
			$bdq = $jinput->get('new_bdq', '', 'STRING');
			$apartment = $jinput->get('new_apartment', '', 'STRING');
			$porch = $jinput->get('new_porch', '', 'STRING');
			$floor = $jinput->get('new_floor', '', 'STRING');
			$code = $jinput->get('new_code', '', 'STRING');
			if(!empty($house)) $address = $street.", дом: ".$house;
			if(!empty($bdq)) $address .= ", корпус: ".$bdq;
			if(!empty($apartment)) $address .= ", квартира: ".$apartment;
			if(!empty($porch)) $address .= ", подъезд: ".$porch;
			if(!empty($floor)) $address .= ", этаж: ".$floor;
			if(!empty($code)) $address .= ", код: ".$code;
            $isDataDelete = $jinput->get('data_delete', '0', 'INT');
            if($isDataDelete) {
                $idCalc = $jinput->get('idCalcDelete','0', 'INT');
				$resultDel = $calculationsModel->delete($idCalc);
                if($resultDel == 1) {
					$this->setMessage("Потолок удален");
                    $this->setRedirect(JRoute::_($_SESSION['url'], false));
                }
			}
			if($isDataChange){
				if(!empty($name)){
					if($name!=$data->client_id){
						$client_model->updateClient($data->id_client,$name);
						$client_history_model->save($data->id_client,"Изменено ФИО");	
					}				
				}
				if(!empty($address)){
					if($address!=$data->project_info){
						$model->update_address($data->id,$address);
						$client_history_model->save($data->id_client,"Адрес замера изменен с ".$data->project_info." на ".$address);
					}							
				}
				$new_phones = [];
				$change_phones = [];
				foreach ($phones as $key => $value) {
					if(strlen($key)<3){
						array_push($new_phones,$value);
					}
					else{
						if($key!=$value){
							$change_phones[$key] = $value;
						}
					}
				}
				$client_model->updateClientSex($client_id,$sex);
				if(count($new_phones)>0){
					$cl_phones_model->save($client_id,$new_phones);
				}
				if(count($change_phones)>0){
					$cl_phones_model->update($client_id,$change_phones);
				}
				$data->project_verdict = 1;
				$calculations = $calculationsModel->getProjectItems($data->id);
				$all_calculations = array();
				foreach($calculations as $calculation){
					$all_calculations[] = $calculation->id;
				}
				$ignored_calculations = array_diff($all_calculations, $include_calculation);
				$return = $model->activate($data, 5);
				if ($return === false)
					{
						$this->setMessage(JText::sprintf('Save failed: %s', $model->getError()), 'warning');
					}
					else {

						if(count($ignored_calculations) > 0) {

							$client_id = $data->id_client;
							$project_data = $model->getData($project_id);
							$project_data->project_status = 3;
							$project_data->gm_calculator_note = "Не вошедшие в договор №" . $data->id;
							$project_data->project_verdict = 0;
							$project_data->client_id = 	$client_id;
							$project_data->api_phone_id = 10;

							unset($project_data->id);
							$project_model = Gm_ceilingHelpersGm_ceiling::getModel('projectform');
							$refuse_id = $project_model->save(get_object_vars($project_data));
							$client_history_model->save($client_id, "Не вошедшие в договор № ".$project_id." потолки перемещены в проект №".$refuse_id);
							$calculationModel = Gm_ceilingHelpersGm_ceiling::getModel('calculation');
							foreach($ignored_calculations as $ignored_calculation){
								$calculationModel->changeProjectId($ignored_calculation, $refuse_id);
							}
						}
					}

					$calculationsModel = Gm_ceilingHelpersGm_ceiling::getModel('calculations');
					$calculations = $calculationsModel->getProjectItems($data->id);
					$components_data = array();
					$project_sum = 0;
					foreach($include_calculation as $calculation){
						if($smeta == 1) $tmp = $calculationsModel->updateComponents_sum($calculation);
						$calculations = $calculationsModel->getProjectItems($calculation);
						$from_db = 1;
						$save = 0;
						$ajax = 0;
						$pdf = 0;
						$print_components = 1;
						$del_flag = 0;

						$components_data[] = Gm_ceilingHelpersGm_ceiling::calculate($from_db,$calculation, $save, $ajax, $pdf, $print_components,$del_flag);
					} 
					Gm_ceilingHelpersGm_ceiling::print_components($project_id, $components_data);
					if(count($ignored_calculations) > 0 ) {
						$data = $model->getNewData($project_id);
						$data->refuse_id = $refuse_id;
						Gm_ceilingHelpersGm_ceiling::notify($data, 6);
						$this->setMessage("Проект сформирован! <br>  Неотмеченные потолки перемещены в копию проекта с отказом");
					} else {
						Gm_ceilingHelpersGm_ceiling::notify($data, 2);
						$this->setMessage("Проект сформирован");
						Gm_ceilingHelpersGm_ceiling::notify($data, 7);
					}
					$this->setRedirect(JRoute::_('index.php?option=com_gm_ceiling&task=mainpage', false));
			}
		}
		catch(Exception $e)
        {
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }
	}
	public function activate() {
		try
		{
			$app = JFactory::getApplication();
			$user = JFactory::getUser();
			$model = $this->getModel('Project', 'Gm_ceilingModel');
			$jinput = JFactory::getApplication()->input;
			$project_id = $jinput->get('project_id', '0', 'INT');
			$data = $model->getData($project_id);
			$model_projectshistory = Gm_ceilingHelpersGm_ceiling::getModel('projectshistory');
			$include_calculation = $jinput->get('include_calculation', '', 'ARRAY');
			$type = $jinput->get('type', '', 'STRING');
			$subtype = $jinput->get('subtype', '', 'STRING');
			$client_history_model = $this->getModel('Client_history', 'Gm_ceilingModel');
			$mounting_date = $jinput->get('jform_project_mounting_date', '0000-00-00 00:00:00', 'DATE');
			$data->project_mounting_date = $mounting_date;
			$project_mounter = $jinput->get('project_mounter',0,'INT');
			if ($project_mounter!=0) {
				$data->project_mounter = $project_mounter;
			}
			$callback_model = $this->getModel('callback', 'Gm_ceilingModel');
			$data->project_sum =  $jinput->get('project_sum',0, 'INT');

			$chief_note = $jinput->get('chief_note',"","STRING");
			if($type === "gmcalculator") {
				$data->gm_calculator_note = $jinput->get('gm_calculator_note', '', 'STRING');
				$data->gm_chief_note = $chief_note;
			} else {
				$data->dealer_calculator_note = $jinput->get('gm_calculator_note', '', 'STRING');
				$data->dealer_chief_note = $chief_note;
			}
			$street = $jinput->get('new_address', '', 'STRING');
			$house = $jinput->get('new_house', '', 'STRING');
			$bdq = $jinput->get('new_bdq', '', 'STRING');
			$apartment = $jinput->get('new_apartment', '', 'STRING');
			$porch = $jinput->get('new_porch', '', 'STRING');
			$floor = $jinput->get('new_floor', '', 'STRING');
			$code = $jinput->get('new_code', '', 'STRING');
			if(!empty($house)) $address = $street.", дом: ".$house;
			if(!empty($bdq)) $address .= ", корпус: ".$bdq;
			if(!empty($apartment)) $address .= ", квартира: ".$apartment;
			if(!empty($porch)) $address .= ", подъезд: ".$porch;
			if(!empty($floor)) $address .= ", этаж: ".$floor;
			if(!empty($code)) $address .= ", код: ".$code;
			$new_address = $address;
			$new_discount =  $jinput->get('new_discount',$data->project_discount, 'RAW');
			$isDataChange = $jinput->get('data_change', '0', 'INT');
			$isDiscountChange = $jinput->get('isDiscountChange', '0', 'INT');
			$isDataDelete = $jinput->get('data_delete', '0', 'INT');

			$smeta = $jinput->get('smeta', '0', 'INT');
			//print_r($smeta); exit;

			// перимерт и зп бригаде
			$model_for_mail = Gm_ceilingHelpersGm_ceiling::getModel('calculations');		
			$project_info_for_mail = $model_for_mail->InfoForMail($project_id);
			$perimeter = 0;
			$salary = 0;
			foreach ($project_info_for_mail as $value) {
				$perimeter += $value->n5;
				$salary += $value->mounting_sum;
			}
			$data->perimeter = $perimeter;
			$data->salary = $salary;
			// ------------------------------------------
			if($isDataDelete) {
				$idCalc = $jinput->get('idCalcDelete','0', 'INT');
				//print_r($idCalc); exit;
				$result = $model_for_mail->delete($idCalc);
				if($result == 1) {
					$this->setMessage("Потолок удален");
				if($type === "gmcalculator" && $subtype === "calendar") {
					$this->setRedirect(JRoute::_('index.php?option=com_gm_ceiling&view=project&type=gmcalculator&subtype=calendar&id='.$project_id, false));
				}
				elseif ($type === "calculator" && $subtype === "calendar") {
					$this->setRedirect(JRoute::_('index.php?option=com_gm_ceiling&view=project&type=calculator&subtype=calendar&id='.$project_id, false));
				}
				else {
					$this->setRedirect(JRoute::_('index.php?option=com_gm_ceiling&task=mainpage', false));
				}
				}
				
			}
			elseif ($isDataChange||$isDiscountChange) {
				if($isDataChange){
					$newFIO = $jinput->get('new_client_name','', 'STRING');
					$newDate = $jinput->get('project_new_calc_date','','STRING');
					$newDayPart = $jinput->get('new_project_calculation_daypart','','STRING');
					$client_model =  $this->getModel('client', 'Gm_ceilingModel');
					
					if($data->client_id!=1){
						if(!empty($newFIO)){
							if($newFIO!=$data->client_id){
								$client_model->updateClient($data->id_client,$newFIO);
								$client_history_model->save($data->id_client,"Изменено ФИО пользователя");	
							}				
						}
						if(!empty($new_address)){
							if($new_address!=$data->project_info){
								$model->update_address($data->id,$new_address);
								$client_history_model->save($data->id_client,"Адрес замера изменен с ".$data->project_info." на ".$new_address);
							}							
						}
						$date_time = $data->project_calculation_date;
						$date_arr = date_parse($date_time);
						$date = $date_arr['year'].'-'.$date_arr['month'].'-'.$date_arr['day'];
						$time = $date_arr['hour'].':00';
						if(!empty($newDate) && !empty($newDayPart))
						{
							if($date!=$newDate && $time!=$newDayPart){
								$model->update_date_time($data->id,$newDate." ".$newDayPart);
								$client_history_model->save($data->id_client,"Замер пернесен с ".$date." в ".$time." на ".$newDate." в ".$newDayPart);
							}
							elseif ($date!=$newDate) {
								$model->update_date_time($data->id,$newDate." ".$time);
								$client_history_model->save($data->id_client,"Замер пернесен с ".$date." в ".$time." на ".$newDate." в ".$time);
							}
							elseif ($newDayPart!=$time) {
								$model->update_date_time($data->id,$date." ".$newDayPart);
								$client_history_model->save($data->id_client,"Замер пернесен с ".$date." в ".$time." на ".$date." в ".$newDayPart);
							}
							
						}
					}
					else{
						$client_model = $this->getModel('ClientForm', 'Gm_ceilingModel');
						$client_data['created'] = date("d.m.Y");
						$client_data['client_name'] = $newFIO;
						$client_data['client_contacts'] = $newContacts;
						$client_data['dealer_id'] = $user->dealer_id;
						$client_data['manager_id'] = $user->id;
						$client_id = $client_model->save($client_data);
						$db = JFactory::getDbo();
						$query = $db->getQuery(true);
						$fields = array(
							$db->quoteName('client_id'). ' = '.$db->quote($client_id)
						);
						$conditions = array(
							$db->quoteName('id').' = '.$db->quote($project_id)
						);
						$query->update($db->quoteName('#__gm_ceiling_projects'))->set($fields)->where($conditions);
						$db->setQuery($query);
						$result = $db->execute();
					}
				}	
				if($isDiscountChange&&(!empty($new_discount)||$new_discount==0)){
					$model->change_discount($project_id,$new_discount);
				}
				$this->setMessage("Данные успешно изменены");
				if($type === "gmcalculator" && $subtype === "calendar") {
					$this->setRedirect(JRoute::_('index.php?option=com_gm_ceiling&view=project&type=gmcalculator&subtype=calendar&id='.$project_id, false));
				}
				elseif ($type === "calculator" && $subtype === "calendar") {
					$this->setRedirect(JRoute::_('index.php?option=com_gm_ceiling&view=project&type=calculator&subtype=calendar&id='.$project_id, false));
				}
				else {
					$this->setRedirect(JRoute::_('index.php?option=com_gm_ceiling&task=mainpage', false));
				}
			} else {
				if($subtype === "refused") {
					$model->return_project($project_id);
				// Clear the profile id from the session.
					$app->setUserState('com_gm_ceiling.edit.project.id', null);

				// Flush the data from the session.
					$app->setUserState('com_gm_ceiling.edit.project.data', null);

					$this->setMessage("Проект вернулся в Замеры");

				} else {
					$project_verdict = $jinput->get('project_verdict', '0', 'INT');	
					if($project_verdict == 1) {
						$data->project_verdict = 1;
					}
					$calculationsModel = Gm_ceilingHelpersGm_ceiling::getModel('calculations');
					$calculations = $calculationsModel->getProjectItems($data->id);
					$all_calculations = array();
					foreach($calculations as $calculation){
						$all_calculations[] = $calculation->id;
					}
					//$checked_calculations = array_intersect($data['include_calculation'], $all_calculations);
					$ignored_calculations = array_diff($all_calculations, $include_calculation);
					// Attempt to save the data.
					$gm_calculator_note = $jinput->get('gm_calculator_note','Отсутсвует','STRING');
					if($user->dealer_type!=2 && $project_verdict == 1) 
					{
						
						$c_date = date_create($data->project_mounting_date);
						date_sub($c_date, date_interval_create_from_date_string('1 day'));
						
						if(empty($data->project_mounting_date)){
							
							$data->project_status = 4;
							$client_history_model->save($data->id_client,"По проекту №".$project_id." заключен договор без даты монтажа");
							$call_mount_date = $jinput->get('calldate_without_mounter','','STRING');
							$call_mount_time = $jinput->get('calltime_without_mounter','','STRING'); 
							$callback_model->save($call_mount_date.' '.$call_mount_time,"Примечание от замерщика : ".$gm_calculator_note,$data->id_client,$data->read_by_manager);
							$client_history_model->save($data->id_client,"Добавлен новый звонок. Примечание от замерщика: ".$gm_calculator_note);
							$return = $model->activate($data,4/*3*/);
							
						}
						else{
							
							$client_history_model->save($data->id_client,"По проекту №".$project_id." заключен договор");
							$client_history_model->save($data->id_client,"Проект №".$project_id." назначен на монтаж на ".$data->project_mounting_date);
							$callback_model->save(date_format($c_date, 'Y-m-d H:i'),"Уточнить готов ли клиент к монтажу",$data->id_client,$data->read_by_manager);
							$client_history_model->save($data->id_client,"Добавлен новый звонок по причине: Уточнить готов ли клиент к монтажу");
							$return = $model->activate($data, 5/*3*/);
							
						}
						
					}
					else if ($user->dealer_type!=2 && $project_verdict == 0)
					{
						$return = $model->activate($data, 3/*7*/);
						$client_history_model->save($data->id_client,"Отказ от договора по проекту №".$project_id."Примечание замерщика : ".$gm_calculator_note);
						$callback_model->save(date("Y-m-d H:i",strtotime("+30 minutes")),"Отказ от договора",$data->id_client,$data->read_by_manager);
						$client_history_model->save($data->id_client,"Добавлен новый звонок по причине: отказ от договора. Примечание замерщика :".$gm_calculator_note);
					}
					// Check for errors.
					if ($return === false)
					{
						$this->setMessage(JText::sprintf('Save failed: %s', $model->getError()), 'warning');
					}
					else {

						if($project_verdict == 1 && count($ignored_calculations) > 0) {

							$client_id = $data->id_client;
							$project_data = $model->getData($project_id);
							$project_data->project_status = 3;
							$project_data->gm_calculator_note = "Не вошедшие в договор №" . $data->id;
							$project_data->project_verdict = 0;
							$project_data->client_id = 	$client_id;
							$old_advt = $project_data->api_phone_id; 
							$project_data->api_phone_id = 10;

							unset($project_data->id);
							$project_model = Gm_ceilingHelpersGm_ceiling::getModel('projectform');
							$refuse_id = $project_model->save(get_object_vars($project_data));
							
							
							$repeat_request = Gm_ceilingHelpersGm_ceiling::getModel('RepeatRequest');
							$repeat_request->save($refuse_id,$old_advt);
							$client_history_model->save($client_id, "Не вошедшие в договор № ".$project_id." потолки перемещены в проект №".$refuse_id);
							$calculationModel = Gm_ceilingHelpersGm_ceiling::getModel('calculation');
							foreach($ignored_calculations as $ignored_calculation){
								$calculationModel->changeProjectId($ignored_calculation, $refuse_id);
							}
						}
					}

					$calculationsModel = Gm_ceilingHelpersGm_ceiling::getModel('calculations');
					$calculations = $calculationsModel->getProjectItems($data->id);
					$components_data = array();
					$project_sum = 0;
					foreach($include_calculation as $calculation){
						if($smeta == 1) $tmp = $calculationsModel->updateComponents_sum($calculation);
						$calculations = $calculationsModel->getProjectItems($calculation);
						$from_db = 1;
						$save = 0;
						$ajax = 0;
						$pdf = 0;
						$print_components = 1;
						$del_flag = 0;

						$components_data[] = Gm_ceilingHelpersGm_ceiling::calculate($from_db,$calculation, $save, $ajax, $pdf, $print_components,$del_flag);
						$dealer_info_model = $this->getModel('Dealer_info', 'Gm_ceilingModel');
						$gm_canvases_margin = $dealer_info_model->getMargin('gm_canvases_margin',$user->dealer_id);
						if($smeta == 0) $gm_components_margin = $dealer_info_model->getMargin('gm_components_margin',$user->dealer_id);
						$gm_mounting_margin = $dealer_info_model->getMargin('gm_mounting_margin',$user->dealer_id);
						$dealer_canvases_margin = $dealer_info_model->getMargin('dealer_canvases_margin',$user->dealer_id);
						if($smeta == 0) $dealer_components_margin = $dealer_info_model->getMargin('dealer_components_margin',$user->dealer_id);
						$dealer_mounting_margin = $dealer_info_model->getMargin('dealer_mounting_margin',$user->dealer_id);
						foreach($calculations as $calc) {
							if($smeta == 0) $project_sum += margin($calc->components_sum, $dealer_components_margin);
							$project_sum += margin($calc->canvases_sum,  $dealer_canvases_margin);
							$project_sum += margin($calc->mounting_sum, $dealer_mounting_margin);
						}
					if($smeta == 1) $tmp = $calculationsModel->updateComponents_sum($calculation);
					} 
					if($smeta == 0) Gm_ceilingHelpersGm_ceiling::print_components($project_id, $components_data);

					// Clear the profile id from the session.
					$app->setUserState('com_gm_ceiling.edit.project.id', null);

					// Flush the data from the session.
					$app->setUserState('com_gm_ceiling.edit.project.data', null);

					// Redirect to the list screen.
					if($project_verdict == 1) {
						if( count($ignored_calculations) > 0 ) {
							$data = $model->getNewData($project_id);
							$data->refuse_id = $refuse_id;
							Gm_ceilingHelpersGm_ceiling::notify($data, 6);
							$this->setMessage("Проект сформирован! <br>  Неотмеченные потолки перемещены в копию проекта с отказом");
						} else {
							Gm_ceilingHelpersGm_ceiling::notify($data, 2);
							$this->setMessage("Проект сформирован");
							Gm_ceilingHelpersGm_ceiling::notify($data, 7);
						}
					} elseif($project_verdict == 0) {
						Gm_ceilingHelpersGm_ceiling::notify($data, 4);
						$this->setMessage("Проект отправлен в список отказов",'error');

					}

					$menu = JFactory::getApplication()->getMenu();

					$item = $menu->getActive();

					}

					if($type === "gmcalculator" && $subtype === "calendar") {
						$this->setRedirect(JRoute::_('index.php?option=com_gm_ceiling&view=projects&type=gmcalculator&subtype=calendar&id='.$project_id, false));
					}
					elseif ($type === "calculator" && $subtype === "calendar") {
						if($user->dealer_type!=2)
							$this->setRedirect(JRoute::_('index.php?option=com_gm_ceiling&view=project&type=calculator&subtype=calendar&id='.$project_id, false));
						else $this->setRedirect(JRoute::_('index.php?option=com_gm_ceiling&view=project&type=calculator&subtype=project&id='.$project_id, false));
					}
					else {
						$this->setRedirect(JRoute::_('index.php?option=com_gm_ceiling&task=mainpage', false));
					}
					if(!$project_verdict) $this->setRedirect(JRoute::_('index.php?option=com_gm_ceiling&task=mainpage', false));
			}

			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$fields = array(
				$db->quoteName('project_mounting_date'). ' = '.$db->quote($mounting_date)
			);
			$conditions = array(
				$db->quoteName('id').' = '.$db->quote($project_id)
			);
			$query->update($db->quoteName('#__gm_ceiling_projects'))->set($fields)->where($conditions);
			$db->setQuery($query);
			$result = $db->execute();
		}
		catch(Exception $e)
        {
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }
	}
	public function approve()
	{
		try
		// Initialise variables.
		{
			$app = JFactory::getApplication();
			// Checking if the user can remove object
			$user = JFactory::getUser();
			$model = $this->getModel('Project', 'Gm_ceilingModel');
			$jinput = JFactory::getApplication()->input;
			$project_id = $jinput->get('jform[id]', '0', 'INT');
			$get_data = JFactory::getApplication()->input->get('jform', array(), 'array');

			$data = $model->getData($get_data['id']);

			$type = $jinput->get('type', '', 'STRING');
			$subtype = $jinput->get('subtype', '', 'STRING');
			$data->gm_chief_note = $get_data['gm_chief_note'];
			$data->project_mounting_date = $get_data['project_mounting_date'];
			
			$old_date = $jinput->get('jform_project_mounting_date_old', '0000-00-00 00:00:00', 'DATE');
			$data->old_date = $old_date;
			$old_mounter = $jinput->get('jform_project_mounting_old','0','INT');
			$data->old_mounter = $old_mounter;
			
			if (!empty($get_data['project_mounting'])) {
				$data->project_mounter = $get_data['project_mounting'];
			}

			if ($data->project_mounting_date != $data->old_date && $data->project_mounter == $old_mounter) { // если изменилась только дата
				Gm_ceilingHelpersGm_ceiling::notify($data, 8);
			}
			if ($data->project_mounter != $old_mounter) { // если изменились монтажники		
				Gm_ceilingHelpersGm_ceiling::notify($data, 7);
				Gm_ceilingHelpersGm_ceiling::notify($data, 9);
			}
			
			// условия для комментов
			if ($data->project_mounting_date != $data->old_date) {
				$model->AddComment(1, $data);
			}
			if ($data->project_mounter != $old_mounter) {
				$model->AddComment(2, $data);
			}

            $return = $model->approve($data);
            
			if ($return === false)
			{
				$this->setMessage(JText::sprintf('Save failed: %s', $model->getError()), 'warning');
			} else {
				$this->setMessage("Данные успешно изменены!");
			}
			if($type === "gmchief") {
				$this->setRedirect(JRoute::_('index.php?option=com_gm_ceiling&view=projects&type=gmchief', false));
			} elseif($type === "chief" && $user->dealer_type == 1 && $old_date) {
				$this->setRedirect(JRoute::_('index.php?option=com_gm_ceiling&view=projects&type=chiefprojects', false));
			} else {
				$this->setRedirect(JRoute::_('index.php?option=com_gm_ceiling&view=projects&type=chief', false));
			}
		}
		catch(Exception $e)
        {
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }

	}

    public function approvemanager()
    {
		try
        {
			$jinput = JFactory::getApplication()->input;
            $id = $jinput->get('id', '0', 'INT');
            $ready_date = $jinput->get('ready_date','','STRING');
            $time = $jinput->get('time','','STRING');
            $ready_date_time = $ready_date.' '.$time;
            $quickly = $jinput->get('quick',0,'INT');
			$model = $this->getModel('Project', 'Gm_ceilingModel');
            $data = $model->approvemanager($id,$ready_date_time,$quickly);
            $res = $model->getData($id);
            $calc_model = Gm_ceilingHelpersGm_ceiling::getModel('calculations');
            $calculations = $calc_model->new_getProjectItems($id);
            $material_sum = 0;

            foreach ($calculations as $calculation) {
                $material_sum += $calculation->components_sum + $calculation->canvases_sum;
            }
            if(empty($material_sum)) $material_sum = 0;
            else $material_sum = -($material_sum);
            $recoil_map_model =Gm_ceilingHelpersGm_ceiling::getModel('recoil_map_project');
            if($res->dealer_id != 1 || $res->dealer_id != 2 )
                $recoil_map_model->save($res->dealer_id, $id, $material_sum);
			if ($data === false)
			{
				$this->setMessage(JText::sprintf('Save failed: %s', $model->getError()), 'warning');
			} else {
				$this->setMessage("Проект ожидает монтажа");
				Gm_ceilingHelpersGm_ceiling::notify($data, 1);
			}
			$this->setRedirect(JRoute::_('index.php?option=com_gm_ceiling&view=projects&type=gmmanager', false));
		}
		catch(Exception $e)
        {
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }
    }

	public function return()
    {
		try
        {
			$jinput = JFactory::getApplication()->input;
			$id = $jinput->get('id', '0', 'INT');
			$model = $this->getModel('Project', 'Gm_ceilingModel');
            $data = $model->return($id);
			if ($data === false)
			{
				$this->setMessage(JText::sprintf('Save failed: %s', $model->getError()), 'warning');
			} else {
				$this->setMessage("Проект вернулся на стадию замера!");
				//Gm_ceilingHelpersGm_ceiling::notify($data, 1);
			}
			$this->setRedirect(JRoute::_('index.php?option=com_gm_ceiling&view=projects&type=gmmanager&subtype=refused', false));
		}
		catch(Exception $e)
        {
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }
    }
	public function transport()
	{
		try
		{
			$jinput = JFactory::getApplication()->input;
			$data->id = $jinput->get('id', '0', 'INT');
			
			$data->transport = $jinput->get('transport', '0', 'INT');
			$data->distance = $jinput->get('distance', '', 'FLOAT');
			$distance_col = $jinput->get('distance_col', '', 'INT');
			$distance_col_1 = $jinput->get('distance_col_1', '', 'INT');


			if($data->transport == '1' ) $data->distance_col = $distance_col_1;
				elseif($data->transport == '2') $data->distance_col = $distance_col;
					else  $data->distance_col = 0;

			$model = $this->getModel('Project', 'Gm_ceilingModel');
			$res = $model->transport($data);
			$dealer_info_model = $this->getModel('Dealer_info', 'Gm_ceilingModel');
			$margin = $dealer_info_model->getMargin('dealer_mounting_margin',$res->user_id);

			if($res) {
				if($data->transport == 1) $transport_sum = $this->margin1($res->transport * $data->distance_col, $margin);
				elseif($data->transport == 2) {
					$transport_sum = $this->margin1($res->distance * $data->distance_col * $data->distance, $margin);
					if($transport_sum < $this->margin1($res->transport, $margin))
					$transport_sum = $this->margin1($res->transport, $margin);
				}
				else $transport_sum = 0;
			}
			$discount = $model->getDiscount($data->id);
			$min = 100;
			foreach($discount as $d) {
				if($d->discount < $min) $min = $d->discount;
			}
			$transport_sum = $transport_sum * ((100 - $min)/100);
			//throw new Exception($transport_sum, 1);
			$transport_sum = json_encode($transport_sum);
			die($transport_sum);
		}
		catch(Exception $e)
        {
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }
	}
	function margin1($value, $margin)
	{
		try{
			$return = ($value * 100 / (100 - $margin));
			return $return;
		}
		catch(Exception $e)
        {
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }
	}

    public function save_mount()
    {
		try{
			$jinput = JFactory::getApplication()->input;
			$id = $jinput->get('id', '0', 'INT');
			$type = $jinput->get('type', '', 'STRING');
			$jform = $jinput->get('jform',array(), 'ARRAY');
			//print_r($id."                ".$type); exit;
			$model = $this->getModel('Project', 'Gm_ceilingModel');
			$return = $model->activate_mount($id, $jform);
			//throw new Exception($id);

			if ($return === false)
			{
				$this->setMessage(JText::sprintf('Save failed: %s', $model->getError()), 'warning');
			} else {
				$this->setMessage("Данные успешно изменены");
			}
			if($type=="gmchief")
			{
				$this->setRedirect(JRoute::_('index.php?option=com_gm_ceiling&view=project&type=gmchief&id='.$id, false));
			}
			else if ($type=="chief")
			{
				$this->setRedirect(JRoute::_('index.php?option=com_gm_ceiling&view=project&type=chief&id='.$id, false));
			}
			}
			catch(Exception $e)
			{
				$date = date("d.m.Y H:i:s");
				$files = "components/com_gm_ceiling/";
				file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
				throw new Exception('Ошибка!', 500);
			}
		}
	public function done()
	{
		try
		{		
			// Initialise variables.
			$app = JFactory::getApplication();

			// Checking if the user can remove object
			$user = JFactory::getUser();

			$jinput = JFactory::getApplication()->input;
			$project_id = $jinput->get('project_id', '0', 'INT');
            $check = $jinput->get('check', '0', 'INT'); // 1 - монтаж выполнен, 0 - недовыполнен
			$new_value = $jinput->get('new_value', '0', 'FLOAT');
			$mouting_sum = $jinput->get('mouting_sum', '0', 'FLOAT');
            $mouting_sum_itog = $jinput->get('mouting_sum_itog', '0', 'FLOAT'); // сумма, которую получат монтажники сначала без выполненной работы
			$material_sum = $jinput->get('material_sum', '0', 'FLOAT');
			//print_r("project_id - $project_id ||| check - $check ||| new_value - $new_value ||| mouting_sum - $mouting_sum ||| mouting_sum_itog - $mouting_sum_itog ||| material_sum - $material_sum"); exit;
			$map_model = $this->getModel('recoil_map_project', 'Gm_ceilingModel');
			$sum = $new_value*0.1;
			if($check == 1) $result = "Договор закрыт!";
			if($check == 0) $result = "Договор пока не закрыт из-за недовыполненного монтажа!";
            //throw new Exception("1");
			if($map_model->exist($project_id)==1){
                $map_model->updateSum($project_id,$sum);
                $recoil_id = $map_model->getRecoilId($project_id)->recoil_id;
                $model_recoil =  $this->getModel('recoil', 'Gm_ceilingModel');
                $recoil = $model_recoil->getRecoilInfo($recoil_id);
                $result = "Заказ от откатника: ".$recoil->name.";Телефон: ".$recoil->username;
               
            }
            // если проект был недовыолнен, а сейчас выполнен, то плюсовать сумму ранее записанную в переменнные  new
			$model = $this->getModel('Project', 'Gm_ceilingModel');
			$table = $model->getTable();
			$table->load($project_id);
			$data = $table;
			$data->new_project_sum = $new_value;
			$check_done = $model->new_getProjectItems($project_id);
			if($check_done->check_mount_done == 0 && $check == 1) {
				$new_value = $check_done->new_project_sum + $new_value;
				//$mouting_sum_itog = $mouting_sum + $check_done->new_project_mounting;
				$mouting_sum = $mouting_sum + $check_done->new_project_mounting;
				$material_sum = $material_sum + $check_done->new_material_sum;
			}
			// Attempt to save the data.
            $return = $model->done($project_id, $new_value, $mouting_sum, $material_sum, $check, $mouting_sum_itog );
			//Gm_ceilingHelpersGm_ceiling::notify($data, 2);
			Gm_ceilingHelpersGm_ceiling::notify($data, 3);
			// Check for errors.


			if ($return === false)
			{
                throw new Exception('Save failed:'. $model->getError());
                //$result = 'Save failed:'. $model->getError();
				//echo JText::sprintf('Save failed: %s', $model->getError());
			}
			 

			die($result);
		}
		catch(Exception $e)
		{
			$date = date("d.m.Y H:i:s");
			$files = "components/com_gm_ceiling/";
			file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
			throw new Exception('Ошибка!', 500);
		}
	}

	/**
	 * Method to save a user's profile data.
	 *
	 * @return    void
	 *
	 * @throws Exception
	 * @since    1.6
	 */
	public function publish()
	{
		try
		{
			// Initialise variables.
			$app = JFactory::getApplication();

			// Checking if the user can remove object
			$user = JFactory::getUser();

			if ($user->authorise('core.edit', 'com_gm_ceiling') || $user->authorise('core.edit.state', 'com_gm_ceiling'))
			{
				$model = $this->getModel('Project', 'Gm_ceilingModel');

				// Get the user data.
				$id    = $app->input->getInt('id');
				$state = $app->input->getInt('state');

				// Attempt to save the data.
				$return = $model->publish($id, $state);

				// Check for errors.
				if ($return === false)
				{
					$this->setMessage(JText::sprintf('Save failed: %s', $model->getError()), 'warning');
				}

				// Clear the profile id from the session.
				$app->setUserState('com_gm_ceiling.edit.project.id', null);

				// Flush the data from the session.
				$app->setUserState('com_gm_ceiling.edit.project.data', null);

				// Redirect to the list screen.
				$this->setMessage(JText::_('COM_GM_CEILING_ITEM_SAVED_SUCCESSFULLY'));
				$menu = JFactory::getApplication()->getMenu();
				$item = $menu->getActive();

				if (!$item)
				{
					// If there isn't any menu item active, redirect to list view
					$this->setRedirect(JRoute::_('index.php?option=com_gm_ceiling&view=projects', false));
				}
				else
				{
					$this->setRedirect(JRoute::_($item->link . $menuitemid, false));
				}
			}
			else
			{
				throw new Exception(500);
			}
		}
		catch(Exception $e)
        {
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }
	}

	/**
	 * Remove data
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	public function remove()
	{
		try
		{
			// Initialise variables.
			$app = JFactory::getApplication();

			// Checking if the user can remove object
			$user = JFactory::getUser();

			if ($user->authorise('core.delete', 'com_gm_ceiling'))
			{
				$model = $this->getModel('Project', 'Gm_ceilingModel');

				// Get the user data.
				$id = $app->input->getInt('id', 0);

				// Attempt to save the data.
				$return = $model->delete($id);

				// Check for errors.
				if ($return === false)
				{
					$this->setMessage(JText::sprintf('Delete failed', $model->getError()), 'warning');
				}
				else
				{
					/*// Check in the profile.
					if ($return)
					{
						$model->checkin($return);
					}*/

					// Clear the profile id from the session.
					$app->setUserState('com_gm_ceiling.edit.project.id', null);

					// Flush the data from the session.
					$app->setUserState('com_gm_ceiling.edit.project.data', null);

					$this->setMessage(JText::_('COM_GM_CEILING_ITEM_DELETED_SUCCESSFULLY'));
				}

				// Redirect to the list screen.
				$menu = JFactory::getApplication()->getMenu();
				$item = $menu->getActive();
				$this->setRedirect(JRoute::_($item->link, false));
			}
			else
			{
				throw new Exception(500);
			}
		}
		catch(Exception $e)
        {
            $date = date("d.m.Y H:i:s");
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'error_log.txt', (string)$date.' | '.__FILE__.' | '.__FUNCTION__.' | '.$e->getMessage()."\n----------\n", FILE_APPEND);
            throw new Exception('Ошибка!', 500);
        }
	}
    
    public function refusing()
	{
		try
		{	
			// Initialise variables.
			$app = JFactory::getApplication();

			// Checking if the user can remove object
			$user = JFactory::getUser();
			$model = $this->getModel('Project', 'Gm_ceilingModel');
			$callback_model = $this->getModel('callback', 'Gm_ceilingModel');
			$client_model = $this->getModel('client', 'Gm_ceilingModel');
			$jinput = JFactory::getApplication()->input;
			//$project_id = $jinput->get('jform[id]', '0', 'INT');
			$project_id = $jinput->get('id', '', 'int');
			$date = $jinput->get('date', '0000-00-00', 'DATE');
			$time = $jinput->get('time', '00:00', 'string');
			$comment = $jinput->get('comment', '', 'string');

			if($comment == "") $comment= "Отказ от производства";
			$data_time = $date." ".$time;
			$client_id = $model->refusing($project_id);
			$manager = $client_model->getClientById($client_id);
			$return = $callback_model->save($data_time, $comment, $client_id ,$manager->manager_id);
			
			if ($return)
			{
				$this->setMessage("Проект отправлен в отказы от производства!");
				$this->setRedirect(JRoute::_('index.php?option=com_gm_ceiling&view=mainpage&type=gmmanagermainpage', false));
			} else {
				$this->setMessage("Не удалось отправить проект в отказы от производства!");
			}
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

