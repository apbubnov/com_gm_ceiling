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
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

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
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

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
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

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
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
    }

    public function changeDiscount()
    {
        try
        {
            $jinput = JFactory::getApplication()->input;
            $model = $this->getModel('Project', 'Gm_ceilingModel');
            $project_id = $jinput->get('project_id', '0', 'INT');
            $data = $model->getData($project_id);
            $new_discount = $jinput->get('new_discount', $data->project_discount, 'INT');
            $project_total = $jinput->get('project_total',$data->project_sum,'string');
            $transport = Gm_ceilingHelpersGm_ceiling::calculate_transport($data->id);
            $project_total-=$transport['client_sum'];
            $new_sum = $project_total-($project_total*$new_discount/100)+$transport['client_sum'];
            $save_data = array("id"=>$data->id,"project_sum"=>$new_sum);
            $model->save($save_data);
            $result = $model->change_discount($project_id, $new_discount);

            die(json_encode($result));
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
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
            $jinput = $app->input;
            $project_id = $jinput->get('project_id', '0', 'INT');
            $data = $model->getData($project_id);
            $type = $jinput->get('type', '', 'STRING');
            $subtype = $jinput->get('subtype', '', 'STRING');
            $call_id = $jinput->get('call_id', 0, 'INT');
            $client_id = $jinput->get('client_id', 1, 'INT');
            $api_phone_id = $jinput->get('advt_id', '0', 'INT');
            $selected_advt = $jinput->get('selected_advt', '0', 'INT');
            $call_type = $jinput->get('slider-radio', 'client', 'STRING');
            $recoil = $jinput->get('recoil', '', 'STRING');
            $sex = $jinput->get('slider-sex', "NULL", 'STRING');
            $emails = $jinput->get('email',array(),'ARRAY');
			$without_advt = $jinput->get('without_advt', 0, 'INT');
			$client_form_model = $this->getModel('ClientForm', 'Gm_ceilingModel');
			$client_model = $this->getModel('client', 'Gm_ceilingModel');
            $user_model = $this->getModel('users', 'Gm_ceilingModel');
            $call_comment = $jinput->get('call_comment', '', 'STRING');
            $call_date = $jinput->get('call_date', "0", 'STRING');
            $isDataDelete = $jinput->get('data_delete', '0', 'INT');
            $measure_note = $jinput->get('measure_note','','STRING');
            if(!empty($measure_note)){
                $this->addNote($project_id, $measure_note,2);
            }
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
                $name = $jinput->get('new_client_name', '', 'STRING');
                $phones = $jinput->get('new_client_contacts', array(), 'ARRAY');
                foreach ($phones as $key => $value) {
                    $phones[$key] = preg_replace('/[\(\)\-\+\s]/', '', $value);
                }

                if(!empty($emails)){
                    $dop_contacts = $this->getModel('clients_dop_contacts', 'Gm_ceilingModel');
                    foreach ($emails as $key => $value) {
                        $dop_contacts->updateEmail($client_id,$value,$key);
                    }
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
                        $model->update_project_after_call($project_id, $client_id, $date_time, $address, $status, $api_phone_id, $user->id, $gauger);
                    } else {
                        // условия на статус
                        $model->update_project_after_call($project_id, $client_id, $date_time, $address, $status, 10, $user->id, $gauger);
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
                        $model->update_project_after_call($project_id, $client_id, $date_time, $address, 21, $api_phone_id, $user->id, $gauger);
                    } else {
                        $model->update_project_after_call($project_id, $client_id, $date_time, $address, 21, 10, $user->id, $gauger);
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
                        $model->update_project_after_call($project_id, $client_id, $date_time, $address, 20, $api_phone_id, $user->id, $gauger, $dealer_canvases_margin, $dealer_components_margin,
                            $dealer_mounting_margin, $gm_canvases_margin, $gm_components_margin, $gm_mounting_margin);
                    } else {
                        $model->update_project_after_call($project_id, $client_id, $date_time, $address, 20, 10, $user->id, $gauger, $dealer_canvases_margin, $dealer_components_margin,
                            $dealer_mounting_margin, $gm_canvases_margin, $gm_components_margin, $gm_mounting_margin);
                        $rep_upd = $rep_model->update($project_id, $api_phone_id);
                    }
                    $status = 20;
                }
                if ($call_type == "client") {
                    $this->setMessage("Клиент $result!");
                }
                //}
                if ($status == 1) {

                    $data_notify['client_name'] = $name;
                    $data_notify['client_contacts'] = $phones;
                    $data_notify['project_info'] = $address;
                    $data_notify['project_calculation_date'] = $date;
                    $data_notify['project_calculation_daypart'] = $time;
                    $data_notify['project_note'] = $gmmanager_comment;
                    $data_notify['project_calculator'] = $gauger;
                    Gm_ceilingHelpersGm_ceiling::notify($data_notify, 0);

                    /*проверка если замер записан более чем  через 2 дня, поставить звонок уточнить актуальность замера */
                    $today = new DateTime(date('Y-m-d'));
                    $measureDate = new DateTime($date);
                    $day_diff = date_diff($measureDate,$today);
                    //throw new Exception(print_r($day_diff,true));
                    if($day_diff->days >=2){
                        $callbackDate = ($measureDate->sub(date_interval_create_from_date_string('1 days')))->format('Y-m-d');
                        if(date('N',strtotime($callbackDate)) == 7){
                            $callbackDate = ($measureDate->sub(date_interval_create_from_date_string('2 days')))->format('Y-m-d');
                        }
                        if(date('N',strtotime($callbackDate)) == 6){
                            $callbackDate = ($measureDate->sub(date_interval_create_from_date_string('1 days')))->format('Y-m-d');
                        }
                        $callbackDate = $callbackDate.' 16:30:00';
                        $callback_model = $this->getModel('callback', 'Gm_ceilingModel');
                        $callback_model->save($callbackDate, "Уточнить актуальность замера", $client_id, $user->id);
                    }
                }
                if ($data->project_status != $status) {
                    $model_projectshistory = Gm_ceilingHelpersGm_ceiling::getModel('projectshistory');
                    $model_projectshistory->save($project_id, $status);
                }

				if($usertype=="managermainpage"){
					$this->setRedirect(JRoute::_('/index.php?option=com_gm_ceiling&task=mainpage', false));
				}
				else{
					$this->setRedirect(JRoute::_('/index.php?option=com_gm_ceiling&view=mainpage&type='.$usertype, false));
				}
            }
            unset($_SESSION['FIO'],$_SESSION['address'],$_SESSION['house'],$_SESSION['bdq'],$_SESSION['apartment'],$_SESSION['porch'],$_SESSION['floor'],$_SESSION['code'],$_SESSION['date'],$_SESSION['time'],$_SESSION['phones'],$_SESSION['manager_comment'],$_SESSION['comments'],$_SESSION['url'],$_SESSION['gauger']);
        }
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
	}

	public function run_in_production(){
		try{
			$app = JFactory::getApplication();
			$user = JFactory::getUser();
            $groups = $user->get('groups');
			//получение нужных моделей
			$model = $this->getModel('Project', 'Gm_ceilingModel');
			$client_history_model = $this->getModel('Client_history', 'Gm_ceilingModel');
			$client_model =  $this->getModel('client', 'Gm_ceilingModel');
			$calculationsModel = Gm_ceilingHelpersGm_ceiling::getModel('calculations');
            $projects_mounts_model = Gm_ceilingHelpersGm_ceiling::getModel('projects_mounts');
            $model_calcform = Gm_ceilingHelpersGm_ceiling::getModel('calculationForm');
			//--------
			$jinput = JFactory::getApplication()->input;
			$project_id = $jinput->get('project_id', '0', 'INT');
			$data = $model->getData($project_id);
			$isDataChange = $jinput->get('data_change', '0', 'INT');
			$client_id = $jinput->get('client_id', 1, 'INT');
			$include_calculation = $jinput->get('include_calculation', '', 'ARRAY');
			$name = $jinput->get('new_client_name', '', 'STRING');
			$phones = $jinput->get('new_client_contacts', array(), 'ARRAY');
            $sum = $jinput->get('project_sum', '', 'STRING');
			foreach ($phones as $key => $value) {
				$phones[$key] = preg_replace('/[\(\)\-\s]/', '', $value);
			}
            $mount_data = json_decode($jinput->get('mount','',"STRING"));
            if(!empty($mount_data)){
                $mount_types = $projects_mounts_model->get_mount_types();
                foreach ($mount_data as $value) {
                    $value->stage_name = $mount_types[$value->stage];
                }
            }
			$street = $jinput->get('new_address', '', 'STRING');
			$house = $jinput->get('new_house', '', 'STRING');
			$bdq = $jinput->get('new_bdq', '', 'STRING');
			$apartment = $jinput->get('new_apartment', '', 'STRING');
			$porch = $jinput->get('new_porch', '', 'STRING');
			$floor = $jinput->get('new_floor', '', 'STRING');
			$code = $jinput->get('new_code', '', 'STRING');
            $address = $street;
			if(!empty($house)) $address .= ", дом: ".$house;
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

            }
            $data->project_sum = $sum;
			$calculations = $calculationsModel->new_getProjectItems($data->id);
			$all_calculations = array();
			foreach($calculations as $calculation){
				$all_calculations[] = $calculation->id;
			}
			$ignored_calculations = array_diff($all_calculations, $include_calculation);
			$return = $model->activate($data, 5);
            if(!empty($mount_data)){
                $projects_mounts_model->save($project_id,$mount_data);
                $service_data = $this->check_mount_for_service($mount_data);
                $send_data['project_id'] = $project_id;
                $send_data['client_id'] = $data->id_client;
                $send_data['mount'] = $service_data;
                $pr_data['id'] = $project_id;
                if(!empty($service_data)){
                    $model_calculation = Gm_ceilingHelpersGm_ceiling::getModel('Calculation');
                    foreach ($include_calculation as $calc) {
                        $calculation =  $model_calculation->getBaseCalculationDataById($calc);
                        $extra_mounting = json_decode($calculation->extra_mounting);
                        $extra_mounting_sum = 0;
                        if(!empty($extra_mounting)){
                            foreach ($extra_mounting as $item){
                                if(isset($item->service_price)){
                                    $extra_mounting_sum += $item->service_price;
                                }
                                else{
                                    $extra_mounting_sum += $item->price + $item->price*0.2;
                                }
                            }
                        }
                        $total_mount_sum = $extra_mounting_sum;
                        $all_jobs = $model_calcform->getMountingServicePricesInCalculation($calc, $data->dealer_id);
                        $all_gm_jobs = $model_calcform->getJobsPricesInCalculation($calc, 1);
                        foreach ($all_jobs as $job){
                            $total_mount_sum += $job->price_sum;
                        }
                        $mount_sum[$calc] = $total_mount_sum;

                        $calc_data['calculation'] = $calculation;
                        $calc_data['jobs'] = $all_jobs;
                        $calc_data['gm_jobs'] = $all_gm_jobs;
                        foreach ($service_data as $key => $value) {
                            Gm_ceilingHelpersGm_ceiling::create_mount_estimate_by_stage($calc_data,$value->mounter,$value->stage);
                        }

                    }
                    Gm_ceilingHelpersGm_ceiling::create_common_estimate_mounters($project_id);
                    $pr_data['mounting_check'] = json_encode($mount_sum);
                    $this->change_project_data($pr_data);
                    Gm_ceilingHelpersGm_ceiling::notify((object)$send_data, 14);
                }
                else{
                      $pr_data['mounting_check'] = "";
                }
            }
            $recoil_map_model =Gm_ceilingHelpersGm_ceiling::getModel('recoil_map_project');
            $user_model = Gm_ceilingHelpersGm_ceiling::getModel('users');
            $dealer = $user_model->getUserByAssociatedClient($client_id);
            if(!empty($dealer)){
                if(!empty($sum)){
                    $recoil_map_model->save($dealer->id, $project_id, $sum);
                }
            }
            
			if ($return === false)
				{
					$this->setMessage(JText::sprintf('Save failed: %s', $model->getError()), 'warning');
				}
				else {

					if(count($ignored_calculations) > 0) {

						$client_id = $data->id_client;
						$project_data = clone $data;
						$project_data->project_status = 3;
						$this->addNote($project_id, "Не вошедшие в договор №".$data->id,3);
						//$project_data->project_verdict = 0;
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
				
                if (in_array('16', $groups))
                {
                    $this->setRedirect("/index.php?option=com_gm_ceiling&view=project&type=gmmanager&id=$project_id", false);
                }
                else
                {
                    $this->setRedirect(JRoute::_('index.php?option=com_gm_ceiling&task=mainpage', false));
                }
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
	}
	public function activate() {
		try
		{
            /*models*/
			$model = $this->getModel('Project', 'Gm_ceilingModel');
			$client_history_model = $this->getModel('Client_history', 'Gm_ceilingModel');
			$callback_model = $this->getModel('callback', 'Gm_ceilingModel');
            $projects_mounts_model = $this->getModel('projects_mounts','Gm_ceilingModel');
            $clientModel = Gm_ceilingHelpersGm_ceiling::getModel('client');
            $calculationsModel = Gm_ceilingHelpersGm_ceiling::getModel('calculations');
            /*get data*/
            $jinput = JFactory::getApplication()->input;
            $project_id = $jinput->get('project_id', 0, 'INT');
            $data = $model->getData($project_id);
            $include_calculation = $jinput->get('include_calculation', '', 'ARRAY');
            $type = $jinput->get('type', '', 'STRING');
            $subtype = $jinput->get('subtype', '', 'STRING');
            $mount_data = json_decode($jinput->get('mount','',"STRING"));
            if(!empty($mount_data)){
                $mount_str = "";
                $mount_types = $projects_mounts_model->get_mount_types();
                foreach ($mount_data as $value) {
                    $value->stage_name = $mount_types[$value->stage];
                    $date = new DateTime($value->time);
                    $mount_str .= $date->format('d.m.Y H:i:s')." - ".$mount_types[$value->stage]."; ";
                }
            }
			$data->project_sum =  $jinput->get('project_sum',1000, 'INT');
            //получение примечаний
			$mount_note = $jinput->get('mount_note','','STRING');
            $production_note = $jinput->get('production_note', '', 'STRING');
            $refuse_note = $jinput->get('ref_note','','STRING');
            $calcs_refuse_note = $jinput->get('refuse_note','','STRING');
            /*Запись примечаний*/
            if (!empty($mount_note)) {
                $this->addNote($project_id, $mount_note,5);
            }
            if (!empty($production_note)) {
                $this->addNote($project_id, $production_note,4);
            }
            if (!empty($refuse_note)) {
                $this->addNote($project_id, $refuse_note,3);
            }
            /*-------*/
            $smeta = $jinput->get('smeta', '0', 'INT');
            $project_verdict = $jinput->get('project_verdict', '0', 'INT');
            $project_status = $jinput->get('project_status', '0', 'INT');
            $prepayment = $jinput->get('prepayment','','STRING');
            // перимерт и зп бригаде
			$project_info_for_mail = $calculationsModel->InfoForMail($project_id);
			$perimeter = 0;
			$salary = 0;
			foreach ($project_info_for_mail as $value) {
				$perimeter += $value->n5;
				$salary += $value->mounting_sum;
			}
			$data->perimeter = $perimeter;
			$data->salary = $salary;
            $data->project_status = $project_status;


            $calculations = $calculationsModel->new_getProjectItems($data->id);
            $all_calculations = array();
            foreach($calculations as $calculation){
                $all_calculations[] = $calculation->id;
            }
            $ignored_calculations = array_diff($all_calculations, $include_calculation);
            $client = $clientModel->getClientById($data->id_client);
            $manager_id =   $client->dealer_id;
            if(!empty($client->manager_id)){
                $manager_id = $client->manager_id;
            }
            else{
                if(!empty($data->read_by_manager)){
                    $manager_id = $data->read_by_manager;
                }
            }
            if($project_verdict == 1){
                if(!empty(floatval($prepayment))){
                    $prepaymentModel = Gm_ceilingHelpersGm_ceiling::getModel('project_prepayment');
                    $prepaymentModel->save($project_id,$prepayment);
                }
                if(empty($mount_data)){
                    $client_history_model->save($data->id_client,"По проекту №".$project_id." заключен договор без даты монтажа");
                    $call_datetime = date('Y-m-d hh:ii:ss');
                    $callback_model->save($call_datetime,"Заключен договор без даты монтажа",$data->id_client,$manager_id);
                    $client_history_model->save($data->id_client,"Добавлен новый звонок");
                }
                else{
                    if($project_status == 4){
                        $client_history_model->save($data->id_client,"По проекту №".$project_id." заключен договор, но не запущен");
                        $client_history_model->save($data->id_client,"Проект №".$project_id." назначен на монтаж.".$mount_str );
                    } else {
                        $client_history_model->save($data->id_client,"По проекту №".$project_id." заключен договор");
                        $client_history_model->save($data->id_client,"Проект №".$project_id." назначен на монтаж. ".$mount_str);
                    }

                    foreach ($mount_data as $value) {
                        $c_date = date_create($value->time);
                        date_sub($c_date, date_interval_create_from_date_string('1 day'));

                        $checkDate = date_format($c_date,'Y-m-d');

                        if(date('N',strtotime($checkDate)) == 7){
                            date_sub($c_date, date_interval_create_from_date_string('2 days'));
                        }
                        if(date('N',strtotime($checkDate)) == 6){
                            date_sub($c_date, date_interval_create_from_date_string('2 days'));
                        }

                        $callback_model->save(date_format($c_date, 'Y-m-d H:i'),"Уточнить готов ли клиент к этапу монтажа $value->stage_name",$data->id_client,$manager_id);
                        $client_history_model->save($data->id_client,"Добавлен новый звонок по причине: Уточнить готов ли клиент к этапу монтажа $value->stage_name");
                    }

                    $projects_mounts_model->save($project_id,$mount_data);

                    $service_data = $this->check_mount_for_service($mount_data);
                    $send_data['project_id'] = $project_id;
                    $send_data['client_id'] = $data->id_client;
                    $send_data['mount'] = $service_data;
                    $pr_data['id'] = $project_id;
                    if(!empty($service_data)){
                        $model_calcform = Gm_ceilingHelpersGm_ceiling::getModel('CalculationForm');
                        $model_calculation = Gm_ceilingHelpersGm_ceiling::getModel('Calculation');
                        foreach ($include_calculation as $calc) {
                            $calculation = $model_calculation->getBaseCalculationDataById($calc);
                            $extra_mounting = json_decode($calculation->extra_mounting);
                            $extra_mounting_sum = 0;
                            if(!empty($extra_mounting)){
                                foreach ($extra_mounting as $item){
                                    if(!empty($item->service_price)){
                                        $extra_mounting_sum += $item->service_price;
                                    }
                                    else{
                                        $extra_mounting_sum += $item->price + $item->price*0.2;
                                    }
                                }
                            }
                            $total_mount_sum = $extra_mounting_sum;
                            $all_jobs = $model_calcform->getMountingServicePricesInCalculation($calc, $data->dealer_id);
                            $all_gm_jobs = $model_calcform->getJobsPricesInCalculation($calc, 1);
                            foreach ($all_jobs as $job){
                                $total_mount_sum += $job->price_sum;
                            }
                            $mount_sum[$calc] = $total_mount_sum;

                            $calc_data['calculation'] = $calculation;
                            $calc_data['jobs'] = $all_jobs;
                            $calc_data['gm_jobs'] = $all_gm_jobs;
                            foreach ($service_data as $key => $value) {
                                Gm_ceilingHelpersGm_ceiling::create_mount_estimate_by_stage($calc_data,$value->mounter,$value->stage);
                            }
                        }
                        $pr_data['calcs_mounting_sum'] = json_encode($mount_sum);
                        $this->change_project_data($pr_data);
                        Gm_ceilingHelpersGm_ceiling::notify((object)$send_data, 14);
                        Gm_ceilingHelpersGm_ceiling::create_common_estimate_mounters($project_id,$include_calculation);
                    }
                    else{
                          $pr_data['calcs_mounting_sum'] = "";
                    }
                }

            }
            else if ($project_verdict == 0 && $project_status == 3)
            {
                $ref_note = $jinput->get("ref_note","Отсутсвует","STRING");
                $client_history_model->save($data->id_client,"Отказ от договора по проекту №".$project_id."Примечание : ".$ref_note);
                $callback_model->save(date("Y-m-d H:i",strtotime("+30 minutes")),"Отказ от договора",$data->id_client,$manager_id);
                $client_history_model->save($data->id_client,"Добавлен новый звонок по причине: отказ от договора. Примечание  :".$ref_note);
            }

            // Check for errors.
            /*if ($return === false)
            {
                $this->setMessage(JText::sprintf('Save failed: %s', $model->getError()), 'warning');
            }
            else {*/
            if($project_verdict == 1 && count($ignored_calculations) > 0) {
                $project_data = clone $data;
                $project_data->project_sum = 0;
                foreach ($calculations as $calculation) {
                    if (in_array($calculation->id, $ignored_calculations)) {
                        $project_data->project_sum += $calculation->components_sum_with_margin;
                        $project_data->project_sum += $calculation->canvases_sum_with_margin;
                        $project_data->project_sum += $calculation->mounting_sum_with_margin;
                    } else {
                        $data->project_sum += $calculation->components_sum_with_margin;
                        $data->project_sum += $calculation->canvases_sum_with_margin;
                        $data->project_sum += $calculation->mounting_sum_with_margin;
                    }
                }

                $client_id = $data->id_client_num;
                $project_data->project_status = 3;

                //$project_data->project_verdict = 0;
                $old_advt = $project_data->api_phone_id;
                $project_data->api_phone_id = 10;
                $project_data->client_id = $client_id;

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
                if(!empty($calcs_refuse_note)) {
                    $this->addNote($refuse_id, $calcs_refuse_note, 3);
                }
            }
            $return = $model->activate($data, $project_status);
            //}
            /*$calculationsModel = Gm_ceilingHelpersGm_ceiling::getModel('calculations');
            $project_sum = 0;
            foreach($include_calculation as $calculation){
                if($smeta == 1) $tmp = $calculationsModel->updateComponents_sum($calculation);
                $calculations = $calculationsModel->new_getProjectItems($calculation);
                foreach($calculations as $calc) {
                    if($smeta == 0) $project_sum += margin($calc->components_sum, $dealer_components_margin);
                    $project_sum += margin($calc->canvases_sum,  $dealer_canvases_margin);
                    $project_sum += margin($calc->mounting_sum, $dealer_mounting_margin);
                }
            if($smeta == 1) $tmp = $calculationsModel->updateComponents_sum($calculation);
            }*/

            /*// Clear the profile id from the session.
            $app->setUserState('com_gm_ceiling.edit.project.id', null);

            // Flush the data from the session.
            $app->setUserState('com_gm_ceiling.edit.project.data', null);*/
            if ($return === false)
            {
                $this->setMessage(JText::sprintf('Save failed: %s', $model->getError()), 'warning');
            }
            // Redirect to the list screen.
            if($project_verdict == 1) {
                if( count($ignored_calculations) > 0 ) {
                    //$data = $model->getNewData($project_id);
                    $data->refuse_id = $refuse_id;
                    Gm_ceilingHelpersGm_ceiling::notify($data, 6);
                    $this->setMessage("Проект сформирован! <br>  Неотмеченные потолки перемещены в копию проекта с отказом");
                } else {
                    if($project_status == 4){
                        $this->setMessage("Проект сохранен");
                    }
                    else {
                        Gm_ceilingHelpersGm_ceiling::notify($data, 2);
                        $this->setMessage("Проект сформирован");
                        Gm_ceilingHelpersGm_ceiling::notify($data, 7);
                    }
                }
            } elseif($project_verdict == 0) {
                if(in_array($project_status, [2,3,15])){
                    Gm_ceilingHelpersGm_ceiling::notify($data, 4);
                    $this->setMessage("Проект отправлен в список отказов",'error');
                }
                else{
                    if($project_status == 1){
                        $this->setMessage("Проект записан на замер",'success');
                    }
                    else{
                        $this->setMessage("Сохранено",'success');
                    }
                }
            }
            /*
            $menu = JFactory::getApplication()->getMenu();
            $item = $menu->getActive();
            */

            if($type === "gmcalculator" && $subtype === "calendar") {
                $this->setRedirect(JRoute::_('index.php?option=com_gm_ceiling&view=projects&type=gmcalculator&subtype=calendar&id='.$project_id, false));
            }
            elseif ($type === "calculator" && $subtype === "calendar") {
                if($project_status == 4 ) $this->setRedirect(JRoute::_('index.php?option=com_gm_ceiling&view=mainpage&type=dealermainpage', false));
                else $this->setRedirect(JRoute::_('index.php?option=com_gm_ceiling&view=project&type=calculator&subtype=calendar&id='.$project_id, false));
            }
            else {
                $this->setRedirect(JRoute::_('index.php?option=com_gm_ceiling&task=mainpage', false));
            }
            if(!$project_verdict) $this->setRedirect(JRoute::_('index.php?option=com_gm_ceiling&task=mainpage', false));
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
	}
	public function activateByEmail(){
		try{
		    $user = JFactory::getUser();
			$jinput = JFactory::getApplication()->input;
			$projectId = $jinput->getInt('project_id');
			$email = $jinput->getString('email');
			$includeCalculations = $jinput->get('include_calcs',[],'ARRAY');
			$projectMount = $jinput->getString('mount_data');
			$projectMount = json_decode($projectMount);
			$productionNote = $jinput->getString('production_note');
            $mountNote = $jinput->getString('mount_note');
            $refNote = $jinput->getString('ref_note');

            $calculationsModel = Gm_ceilingHelpersGm_ceiling::getModel('calculations');
            $projectModel = $this->getModel('Project', 'Gm_ceilingModel');
            $clientHistoryModel = $this->getModel('Client_history', 'Gm_ceilingModel');
            $projectsMountsModel = $this->getModel('projects_mounts','Gm_ceilingModel');
            $calculationFormModel = $this->getModel('CalculationForm', 'Gm_ceilingModel');

            $project = $projectModel->getData($projectId);
            $calculations = $calculationsModel->new_getProjectItems($projectId);
            $all_calculations = [];
            foreach($calculations as $calculation){
                $all_calculations[] = $calculation->id;
                /*перегенерировать pdf-раскроя*/
                $data_for_manager_estimate = [];
                $all_goods = $calculationFormModel->getGoodsPricesInCalculation($calculation->id, $user->dealer_id);
                if (!empty($calculation->cancel_metiz)) {
                    $all_goods = Gm_ceilingHelpersGm_ceiling::deleteMetizFromGoods($all_goods);
                }
                $factory_jobs = $calculationFormModel->getFactoryWorksPricesInCalculation($calculation->id);
                foreach ($all_goods as $value) {
                    if ($value->category_id == 1) { // если полотно
                        $data_for_manager_estimate['canvas'] = $value;
                        $canvas_price = $value->dealer_price;
                        break;
                    }
                }
                if (empty($calculation->cancel_cuts) && !empty($calculation->offcuts) ) {
                    $data_for_manager_estimate['offcuts'] = (object)array("name"=>"Обрезки","count"=>$calculation->offcuts,"price"=>$canvas_price * 0.5);
                }
                $data_for_manager_estimate['photoprint'] = json_decode($calculation->photo_print);
                $data_for_manager_estimate['factory_jobs'] = $factory_jobs;
                $data_for_manager_estimate['calculation'] = $calculation;
                Gm_ceilingHelpersGm_ceiling::create_cut_pdf($data_for_manager_estimate);
            }
            $ignored_calculations = array_diff($all_calculations, $includeCalculations);

            if(count($ignored_calculations) > 0) {
                $project_data = clone $project;
                unset($project_data->id);
                $project_data->project_sum = 0;
                $project->project_sum = Gm_ceilingHelpersGm_ceiling::calculate_transport($projectId)['client_sum'];
                foreach ($calculations as $calculation) {
                    if (in_array($calculation->id, $ignored_calculations)) {
                        $project_data->project_sum += $calculation->canvases_sum_with_margin;
                        $project_data->project_sum += $calculation->components_sum_with_margin;
                        $project_data->project_sum += $calculation->mounting_sum_with_margin;
                    } else {
                        $project->project_sum += $calculation->canvases_sum_with_margin;
                        $project->project_sum += $calculation->components_sum_with_margin;
                        $project->project_sum += $calculation->mounting_sum_with_margin;
                    }
                }
                $client_id = $project->id_client_num;
                $project_data->project_status = 3;

                $old_advt = $project_data->api_phone_id;
                if(!empty($old_advt)){
                    $project_data->api_phone_id = 10;
                }

                $project_data->client_id = $client_id;

                $projectFormModel = Gm_ceilingHelpersGm_ceiling::getModel('projectform');
                $refuse_id = $projectFormModel->save(get_object_vars($project_data));

                if(!empty($old_advt)) {
                    $repeatRequestModel = Gm_ceilingHelpersGm_ceiling::getModel('RepeatRequest');
                    $repeatRequestModel->save($refuse_id, $old_advt);
                }

                $clientHistoryModel->save($client_id, "Не вошедшие в договор № ".$projectId." потолки перемещены в проект №".$refuse_id);
                $calculationModel = Gm_ceilingHelpersGm_ceiling::getModel('calculation');
                foreach($ignored_calculations as $ignored_calculation){
                    $calculationModel->changeProjectId($ignored_calculation, $refuse_id);
                }
                if(!empty($refNote)){
                    $this->addNote($refuse_id,$refNote,3);
                }
            }
            $projectModel->activate($project, 23);

            if(!empty($projectMount)){
                $projectsMountsModel->save($projectId,$projectMount);
            }

            if(!empty($productionNote)){
                $this->addNote($projectId,$productionNote,4);
            }
            if(!empty($mountNote)){
                $this->addNote($projectId,$mountNote,5);
            }
            Gm_ceilingHelpersGm_ceiling::create_common_cut_pdf($projectId);
            Gm_ceilingHelpersGm_ceiling::create_estimate_of_consumables($projectId,0);
            $mailer = JFactory::getMailer();
            $config = JFactory::getConfig();
            $sender = array(
                $config->get('mailfrom'),
                $config->get('fromname')
            );

            $mailer->setSender($sender);
            $mailer->addRecipient($email);
            $body = "Запуск в производство.\nПримечание: $productionNote.\nИнформация о потолках и расходных материалах во вложении.";
            $mailer->setSubject('Запуск в производство');
            $mailer->setBody($body);
            $mailer->addAttachment($_SERVER['DOCUMENT_ROOT'] . '/costsheets/' . md5($projectId . "common_cutpdf") . ".pdf");
            $mailer->addAttachment($_SERVER['DOCUMENT_ROOT'] . '/costsheets/'.md5($projectId . "consumablesnone") . ".pdf");
            $send = $mailer->Send();
            $files = "components/com_gm_ceiling/";
            file_put_contents($files.'runByEmail.txt', "$user->id, $user->name,$email", FILE_APPEND);

            die($send);
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
	}
	public function approve()
	{
		try
		{
			$app = JFactory::getApplication();
			$user = JFactory::getUser();
			$model = $this->getModel('Project', 'Gm_ceilingModel');
			$jinput = JFactory::getApplication()->input;
			$project_id = $jinput->get('jform[id]', '0', 'INT');
			$json_mount_data = $jinput->get('mount','','STRING');
            $mount_data = json_decode($json_mount_data);
			$get_data = JFactory::getApplication()->input->get('jform', [], 'array');
            $history_model = Gm_ceilingHelpersGm_ceiling::getModel('Client_history');
            $projects_mounts_model = Gm_ceilingHelpersGm_ceiling::getModel('Projects_mounts');
			$data = $model->getData($get_data['id']);
            $callback_model = Gm_ceilingHelpersGm_ceiling::getModel('Callback');
			$type = $jinput->get('type', '', 'STRING');
			$subtype = $jinput->get('subtype', '', 'STRING');
            if (empty($project_id)) {
                $project_id = $data->id;
            }

            if (!empty($get_data['mount_note'])) {
                $this->addNote($project_id, $get_data['mount_note'],5);
            }
            //$mount_data = json_decode($get_data['mount']);

			// замеры
			$data->project_calculation_date = $get_data['project_new_calc_date'];
			$old_date_gauger = $jinput->get('jform_project_calculation_date_old', '0000-00-00 00:00:00', 'DATE');
			$data->old_date_gauger = $old_date_gauger;
			$old_gauger = $jinput->get('jform_project_gauger_old','0','INT');
			$data->old_gauger = $old_gauger;
			if (!empty($get_data['project_gauger'])) {
				$data->project_calculator = $get_data['project_gauger'];
			}
			if ($data->project_calculation_date != $old_date_gauger) { // если изменилась только дата
				Gm_ceilingHelpersGm_ceiling::notify($data, 10);
			}
			if ($data->project_calculator != $old_gauger) { // если изменился замерщик
				Gm_ceilingHelpersGm_ceiling::notify($data, 0);
				Gm_ceilingHelpersGm_ceiling::notify($data, 11);
			}
			if ($data->project_calculation_date != $old_date_gauger) {
                $text = "У проекта №$data->id дата замера перенесена на $data->project_calculation_date ";
				$history_model->save($data->id_client,$text);
			}
			if ($data->project_calculator != $old_gauger) {
                $gauger_name = JFactory::getUser($data->project_calculator)->name;
                $text = "У проекта №$data->id был изменен замерщик на ".$gauger_name;
				$history_model->save($data->id_client,$text);
			}

            $old_mount_data = $projects_mounts_model->getData($data->id);
            //throw new Exception(print_r($old_mount_data,true));
            $common_mount = array_uintersect($old_mount_data, $mount_data, function ($e1, $e2) {
                return $e1 == $e2;
            });
            $mount_diff = array_udiff($mount_data,$common_mount,
                  function ($obj_a, $obj_b) {
                    if($obj_a != $obj_b){
                        return 1;
                    }
                    else{
                         return 0;
                    }
                  }
                );
			// монтажи
            $change_data  = [];// Массив для уведомления ГММенеджера
            $change_data['id'] = $data->id;
            $calcServiceMount['new_data'] = $mount_diff;
            $change_data['old_data'] = $old_mount_data;
            $change_data['new_data'] = $mount_data;
            $mount_types = $projects_mounts_model->get_mount_types();
            foreach($mount_diff as $value){
                $value->name = $mount_types[$value->stage];
            }
            foreach($old_mount_data as $value){
                $value->name = $mount_types[$value->stage];
            }
			if (!empty($mount_diff)) {
                foreach ($mount_diff as $value) {
                    foreach ($old_mount_data as $old_value) {
                       if($old_value->stage == $value->stage){
                            if($old_value->mounter == $value->mounter && $old_value->time != $value->time){
                               /* $change_stage = (object)[];
                                $change_stage->name = $mount_types[$value->stage];
                                $change_stage->old_date = $old_value->time;
                                $change_stage->new_date = $value->time;
                                $change_data['stages'][] = $change_stage;*/
                                Gm_ceilingHelpersGm_ceiling::notify($data, 8);
                                $text = "У проекта №$data->id дата этапа монтажа '".$mount_types[$value->stage]."' перенесена на $value->time";
                                $history_model->save($data->id_client,$text);
                                $callback_model->updateCallbackDate($value->time,$data->id_client);
                            }
                            if($old_value->mounter != $value->mounter){
                                Gm_ceilingHelpersGm_ceiling::notify($data, 7);
                                Gm_ceilingHelpersGm_ceiling::notify($data, 9);
                                $mounter_name = JFactory::getUser($value->mounter)->name;
                                $text = "У проекта №$data->id монтажная бригада этапа '".$mount_types[$value->stage]."' заменена на $mounter_name";
                                $history_model->save($data->id_client,$text);
                            }
                       }
                    }
                }
			}
            if($data->project_status == 5 || $data->project_status == 10){
                Gm_ceilingHelpersGm_ceiling::notify($change_data, 16);
            }
			// оповещение менеджерам
			if ($user->dealer_type == 1 && $data->project_mounting_date != $data->old_date) {
				Gm_ceilingHelpersGm_ceiling::notify($data, 12);
			}
            $projects_mounts_model->save($data->id,$mount_data);
            $return = $model->approve($data);
            
			if ($return === false) {
				$this->setMessage(JText::sprintf('Save failed: %s', $model->getError()), 'warning');
			} else {
				$this->setMessage("Данные успешно изменены!");
			}

			// редирект
			if ($data->project_status == 1 ) {
				if($type === "gmchief") {
					$this->setRedirect(JRoute::_('index.php?option=com_gm_ceiling&view=projects&type=gmchief&subtype=gaugings', false));
				} elseif ($type === "chief") {
					$this->setRedirect(JRoute::_('index.php?option=com_gm_ceiling&view=projects&type=chief&subtype=gaugings', false));
				}
			} else {
				if($type === "gmchief") {
					$this->setRedirect(JRoute::_('index.php?option=com_gm_ceiling&view=projects&type=gmchief', false));
				} else {
					$this->setRedirect(JRoute::_('index.php?option=com_gm_ceiling&view=mainpage&type=chiefmainpage', false));
				}
			}
			
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }

	}

    public function approvemanager()
    {
		try
        {
			$jinput = JFactory::getApplication()->input;
            $id = $jinput->get('id', '0', 'INT');
            if(!empty($id)){
                /*models*/
                $calculationModel = Gm_ceilingHelpersGm_ceiling::getModel('calculation');
                $model = Gm_ceilingHelpersGm_ceiling::getModel('Project');
                $projects_mounts_model = $this->getModel('projects_mounts','Gm_ceilingModel');
                $calc_model = Gm_ceilingHelpersGm_ceiling::getModel('calculations');
                $client_model = Gm_ceilingHelpersGm_ceiling::getModel('client');
                /*------*/
                $stockId = $jinput->get('stock_id','1','INT');
                $ready_date = json_decode($jinput->get('ready_dates','','STRING'));
                if(!empty($ready_date)){
                    foreach($ready_date as $value){
                        $calculationModel->save_ready_time($value->calc_id,$value->ready_time);
                    }
                }
                $data = $model->approvemanager($id);
                $res = $model->getNewData($id);
                $mount_data = json_decode($jinput->get('mount','',"STRING"));
                $calculations = $calc_model->new_getProjectItems($id);
                $material_sum = 0;
                $client  = $client_model->getClientById($res->client_id);
                $dealer_id = $client->dealer_id;
                $data->dealer_id = $dealer_id;
                foreach ($calculations as $calculation) {
                    $material_sum += $calculation->components_sum + $calculation->canvases_sum;
                }
                if ($data === false){
                    $this->setMessage(JText::sprintf('Save failed: %s', $model->getError()), 'warning');
                } else {
                    $this->setMessage("Проект ожидает монтажа");
                    if(!empty($mount_data)){
                        $projects_mounts_model->save($id,$mount_data);
                    }
                    Gm_ceilingHelpersGm_ceiling::notify($data, 1);
                    Gm_ceilingHelpersGm_ceiling::notify($data, 13);
                }
                /*списание денег с дилера*/
                if(!empty($material_sum)){
                    $dealer = JFactory::getUser($dealer_id);
                    $stateOfAccountModel =  Gm_ceilingHelpersGm_ceiling::getModel('client_state_of_account');
                    $stateOfAccountModel->save($dealer->associated_client,2,$material_sum,null,$id);
                }
                /*списание полотна и гарпуна*/
                $projectForStock = $model->getProjectForStock($id);
                $canvasGoods = (object)[
                    'ids'=> '',
                    'goods'=> [],
                    'goods_count' => 0];
                $goodsCount = 0;
                $ids = [];
                foreach ($projectForStock->goods as $goods){
                    if($goods->category_id == 1 || $goods->category_id == 10 ){
                        $goods->count = $goods->final_count;
                        array_push($canvasGoods->goods,$goods);
                        $goodsCount++;
                        array_push($ids,$goods->goods_id);
                    }
                }
                $canvasGoods->goods_count = $goodsCount;
                $canvasGoods->ids = implode(',',$ids);
                //throw new Exception(print_r($canvasGoods,true));
                $projectForStock->goods = $canvasGoods;
                $controllerStock = Gm_ceilingHelpersGm_ceiling::getController('stock');
                $realisationResult = $controllerStock->makeRealisation($id,$projectForStock,$stockId,1);
                if($realisationResult->type == 'error'){

                    $text = $realisationResult->text;
                    if(!empty($realisationResult->goods)){
                        foreach ($realisationResult->goods as $goods){
                            $text .= "\n $goods->name;";
                        }
                    }
                    $this->setRedirect(JRoute::_('index.php?option=com_gm_ceiling&view=project&type=gmmanager&id='.$id, false),$text,'error');
                }
                else{
                    $this->setRedirect($realisationResult->href['MergeFiles']);
                    //throw new Exception(print_r($realisationResult,true));
                }
                /**/
                $this->setRedirect(JRoute::_('index.php?option=com_gm_ceiling&view=projects&type=gmmanager', false));
            }
            else{
                $this->setRedirect(JRoute::_('index.php?option=com_gm_ceiling&view=projects&type=gmmanager', false),'Пустой номер проекта!','error');
            }

		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
	}
	
	public function GetNameGauger()
	{
		try
		{
			$id = $_POST["id"];
			$model = $this->getModel('Project', 'Gm_ceilingModel');
			$return = $model->GetNameGauger($id);

			die(json_encode($return));
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

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
            $model_projectshistory = Gm_ceilingHelpersGm_ceiling::getModel('projectshistory');
            /*удаление из истории статусов проекта еслион был в статусе 5,10,19*/
            $model_projectshistory->delete($id);
            $model_projectshistory->save($id,1);
            $model_recoil_map_project = Gm_ceilingHelpersGm_ceiling::getModel('recoil_map_project');
            $model_recoil_map_project->deleteByProjId($id);
			if ($data === false)
			{
				$this->setMessage(JText::sprintf('Save failed: %s', $model->getError()), 'warning');
			} else {
				$this->setMessage("Проект вернулся на стадию замера!");
				//Gm_ceilingHelpersGm_ceiling::notify($data, 1);
			}
			$this->setRedirect(JRoute::_('index.php?option=com_gm_ceiling&view=mainpage&type=gmmanagermainpage', false));
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
	}


	public function update_transport(){
		try{
			$jinput = JFactory::getApplication()->input;
			$project_model = self::getModel('Project');
			$project_id = $jinput->get('id', '', 'INT');
			$transport_type = $jinput->get('transport', '', 'STRING'); 
			$distance = $jinput->get('distance', '', 'STRING');
			$distance_col = $jinput->get('distance_col', '', 'STRING');
            $all = $jinput->get('all', '', 'INT');
			if(!empty($project_id)){
				$project_model->update_transport($project_id,$transport_type,$distance,$distance_col);
				if (empty($all)) $sum = Gm_ceilingHelpersGm_ceiling::calculate_transport($project_id);
				else $sum = Gm_ceilingHelpersGm_ceiling::calculate_transport($project_id);

                Gm_ceilingHelpersGm_ceiling::create_client_common_estimate($project_id);
                Gm_ceilingHelpersGm_ceiling::create_common_estimate_mounters($project_id);

				die(json_encode($sum));
			}
			else{
				throw new Exception("Empty project_id!");
			}
		}
		catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

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
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

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
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

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
            if($user->dealer_type != 1) {
                if($map_model->exist($project_id)==1){
                    $map_model->updateSum($project_id,$sum);
                    $recoil_id = $map_model->getRecoilId($project_id)->recoil_id;
                    $model_recoil =  $this->getModel('recoil', 'Gm_ceilingModel');
                    $recoil = $model_recoil->getRecoilInfo($recoil_id);
                    $result = "Заказ от откатника: ".$recoil->name.";Телефон: ".$recoil->username;

                }
            }

            // если проект был недовыолнен, а сейчас выполнен, то плюсовать сумму ранее записанную в переменнные  new
			$model = $this->getModel('Project', 'Gm_ceilingModel');
			$table = $model->getTable();
			$table->load($project_id);
			$data = $table;
			$data->new_project_sum = $new_value;
			$data->dealer_id = $user->dealer_id;
			$check_done = $model->new_getProjectItems($project_id);
			/*if($check_done->check_mount_done == 0 && $check == 1) {
			    throw new Exception('check');
				$new_value = $check_done->new_project_sum + $new_value;
				//$mouting_sum_itog = $mouting_sum + $check_done->new_project_mounting;
				$mouting_sum = $mouting_sum + $check_done->new_project_mounting;
				$material_sum = $material_sum + $check_done->new_material_sum;
			}*/
			// Attempt to save the data.
            $return = $model->done($project_id, $new_value, $mouting_sum, $material_sum, $check, $mouting_sum_itog );
			//Gm_ceilingHelpersGm_ceiling::notify($data, 2);
			Gm_ceilingHelpersGm_ceiling::notify($data, 3);
			$modelCalback = Gm_ceilingHelpersGm_ceiling::getModel('callback');
            $modelCalback->save(date('Y-m-d H:i:s'), "Договор №$project_id закрыт!", $check_done->client_id ,$check_done->read_by_mamager);

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
			Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

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
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

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
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

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
			Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
		}
	}

    public function updateProjectStatus()
    {
        try
        {   
            $jinput = JFactory::getApplication()->input;
            $project_id = $jinput->get('project_id', 0, 'int');
            //$in_history = $jinput->get('in_history',0,'int');
            $status = $jinput->get('status', 0, 'int');

            $model = $this->getModel('Project', 'Gm_ceilingModel');
            $result = $model->newStatus($project_id, $status);
            /*Запись в историю проектов*/
            $model_projectshistory = Gm_ceilingHelpersGm_ceiling::getModel('projectshistory');
            $model_projectshistory->save($project_id, $status);


            die($result);
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }



    public function delete_by_user(){
        try{
            $jinput = JFactory::getApplication()->input;
            $data['id'] = $jinput->get('project_id', null, 'int');
            $data['deleted_by_user'] = 1;

            $model = $this->getModel('Project', 'Gm_ceilingModel');
            $result = $model->save($data);
            die($result);
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
    }

    public function save_advt(){
        try {
            $jinput = JFactory::getApplication()->input;
            $data['id'] = $jinput->get('project_id', null, 'int');
            $api_phone_id = $jinput->get('api_phone_id', null, 'int');
            $client_id = $jinput->get('client_id', null, 'int');

            $model_project = Gm_ceilingHelpersGm_ceiling::getModel('Project', 'Gm_ceilingModel');
            $model_repeat = Gm_ceilingHelpersGm_ceiling::getModel('repeatrequest', 'Gm_ceilingModel');
            $model_api_phones = Gm_ceilingHelpersGm_ceiling::getModel('api_phones');

            $user = JFactory::getUser();
            $reklama = $model_api_phones->getDataById($api_phone_id);

            if ($user->dealer_id != $reklama->dealer_id && $user->id != $reklama->dealer_id) {
                throw new Exception('403 forbidden');
            }
            $project = $model_project->getData($data['id']);
            if($project->api_phone_id != 10){
                $data['api_phone_id'] = $api_phone_id;
                 $result = $model_project->save($data);
            }
            else{                
                  $model_repeat->update($data['id'],$api_phone_id);
            }           
            die(json_encode($result));
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
    }

    function save_mount_data($project_id,$data = null){
        try{
            $jinput = JFactory::getApplication()->input;
            if(empty($project_id)){
                $project_id = $jinput->getInt('id');
            }
            if(!empty($project_id)){
                if(empty($data)){
                    $data = $jinput->get('mount_data','','STRING');
                }
                if(!empty($data)){
                    $data = json_decode($data);
                    $mounts_model = Gm_ceilingHelpersGm_ceiling::getModel('Projects_mounts');
                    $result = $mounts_model->save($project_id,$data);
                    die(json_encode($result));
                }
                else{
                    throw new Exception("Empty mounting data!");     
                }
            }
            else {
                throw new Exception("Empty project_id!");
            }

        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
    }

    function change_address(){
        try{
            $jinput = JFactory::getApplication()->input;
            $address = $jinput->get('address','',"STRING");
            $id = $jinput->get('id',null,"INT");
            if(!empty($id)){
                $model = Gm_ceilingHelpersGm_ceiling::getModel('Project');
                $result = $model->update_address($id,$address);
                die(json_encode($result));
            }
            else {
                throw new Exception("Empty project id");
                
            }
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
    }

    function change_project_data($data = null){
        try{
            $jinput = JFactory::getApplication()->input;
            if(empty($data)){
                $ajax = 1;
                $data = json_decode($jinput->get('new_data', '', 'STRING'));
                $data = get_object_vars($data);
                foreach ($data as $key => $value) {
                    if(empty($value)){
                        unset($data[$key]);
                    }
                }
            }
            $model = $this->getModel('Project', 'Gm_ceilingModel');
            $result = $model->save($data);
            $project_data = $model->getData($data['id']);
            if($data['project_status'] == 1){
                Gm_ceilingHelpersGm_ceiling::notify(get_object_vars($project_data),0);
            }
            if($ajax){
                die(json_encode($result));
            }
            else{
                return true;
            }
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());

        }
    }

    function check_mount_for_service($mount_data){
        try{
            $mount = [];
            if(!empty($mount_data)){
                foreach ($mount_data as $value) {
                    $groups = JFactory::getUser($value->mounter)->groups;
                    if(in_array(26, $groups) && !in_array($value,$mount)){
                        array_push($mount, $value);
                    }
                }
            }
            return $mount;
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function calcServiceMount(){
        try{ 
            $jinput = JFactory::getApplication()->input;
            $project_id = $jinput->get("project_id",null,"INT");
            $project_model = Gm_ceilingHelpersGm_ceiling::getModel('project');
            $model_calcform = Gm_ceilingHelpersGm_ceiling::getModel('CalculationForm');
            $calculationModel = Gm_ceilingHelpersGm_ceiling::getModel('calculation');
            $project = $project_model->getData($project_id);
            $include_calculations = $jinput->get("calcs",array(),"ARRAY");
            $mount_data = json_decode($jinput->get("mount","","STRING"));
            $dealer_id = $project->dealer_id;
            $service_mount = $this->check_mount_for_service($mount_data);
            if(empty($service_mount)){
                foreach ($include_calculations as $calc) {
                    $calculation = $calculationModel->getBaseCalculationDataById($calc);
                    $extra_mounting = json_decode($calculation->extra_mounting);
                    $extra_mounting_sum = 0;
                    if(!empty($extra_mounting)){
                        foreach ($extra_mounting as $item){
                            $extra_mounting_sum += $item->price;
                        }
                    }

                    $total_mount_sum = $extra_mounting_sum;
                    $all_jobs = $model_calcform->getJobsPricesInCalculation($calc, $dealer_id); // Получение работ по прайсу дилера
                    foreach ($all_jobs as $job){
                        $total_mount_sum += $job->price_sum;
                    }
                    $result[$calc] = $total_mount_sum;
                }
            }
            else{
                foreach ($include_calculations as $calc) {
                    $calculation = $calculationModel->getBaseCalculationDataById($calc);
                    $extra_mounting = json_decode($calculation->extra_mounting);
                    $extra_mounting_sum = 0;
                    if(!empty($extra_mounting)){
                        foreach ($extra_mounting as $item){
                            if(!empty($item->service_price)){
                                $extra_mounting_sum += $item->service_price;
                            }
                            else{
                                $extra_mounting_sum += $item->price + $item->price*0.2;
                            }
                        }
                    }
                    $total_mount_sum = $extra_mounting_sum;
                    $all_jobs = $model_calcform->getMountingServicePricesInCalculation($calc, $dealer_id);
                    foreach ($all_jobs as $job){
                        $total_mount_sum += $job->price_sum;
                    }
                    $result[$calc] = $total_mount_sum;
                    if(!empty($project_id)){
                        $transport = Gm_ceilingHelpersGm_ceiling::calculate_transport($project_id,"service")['mounter_sum'];
                    }
                    $result['transport'] = $transport;        
                }
            }
            die(json_encode($result));
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }   
    }

    function saveService(){
        try{
            $jinput = JFactory::getApplication()->input;
            $project_id = $jinput->get('project_id',null,'INT');
            $dealer_id = $jinput->get('dealer_id',null,'INT');
            $dealer = JFactory::getUser($dealer_id);
            $email = $dealer->email;
            $projects_mounts_model = $this->getModel('projects_mounts','Gm_ceilingModel');
            $model = $this->getModel('Project', 'Gm_ceilingModel');
            $mount_data = json_decode($jinput->get('mount','',"STRING"));
            if(!empty($mount_data) && !empty($project_id)){
                $mount_types = $projects_mounts_model->get_mount_types();
                foreach ($mount_data as $value) {
                    $value->stage_name = $mount_types[$value->stage];
                }
                $return = $projects_mounts_model->save($project_id,$mount_data);
            }
            if($return){
                $data = $model->getData($project_id);
                $model_for_mail = Gm_ceilingHelpersGm_ceiling::getModel('calculations');       
                // перимерт и зп бригаде
                $project_info_for_mail = $model_for_mail->InfoForMail($project_id);
                $perimeter = 0;
                $salary = 0;
                foreach ($project_info_for_mail as $value) {
                    $perimeter += $value->n5;
                    $salary += $value->mounting_sum;
                }
                $data->perimeter = $perimeter;
                $data->salary = $salary;
                Gm_ceilingHelpersGm_ceiling::notify($data,7);
                $appr_data['project_id'] = $project_id;
                $appr_data['dealer_id'] = $dealer_id;
                $appr_data['mount'] = $mount_data;
                Gm_ceilingHelpersGm_ceiling::notify((object)$appr_data,15);
                $this->setMessage("Монтаж назначен!");  
            }
            else{
                $this->setMessage("Произошла ошибка");
            }
             $this->setRedirect(JRoute::_('index.php?option=com_gm_ceiling&view=project&type=gmchief&subtype=service&id='.$project_id, false));  
            
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function removeService(){
        try{
            $jinput = JFactory::getApplication()->input;
            $project_id = $jinput->get('project_id',null,'INT');
            $project_model = Gm_ceilingHelpersGm_ceiling::getModel('project');
            $projects_mounts_model = $this->getModel('projects_mounts','Gm_ceilingModel');
            $data['id'] = $project_id;
            $data['calcs_mounting_sum'] = '';
            $project_model->save($data);
            $stages_data = $projects_mounts_model->getData($project_id);
            $stages = [];
            foreach ($stages_data as $stage){
                $groups = JFactory::getUser($stage->mounter)->groups;
                if(in_array(26,$groups)){
                    $stages[] = $stage->stage;
                }
            }
            $stages = implode(',',$stages);
            $projects_mounts_model->deleteByStage($project_id,$stages);
            die(json_encode(true));
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function saveProjectMaterials(){
	    try{
            $jinput = JFactory::getApplication()->input;
            $project_id = $jinput->get('project_id',null,'INT');
            $components = $jinput->get('components',array(),'ARRAY');
            $canvases = $jinput->get('canvases',array(),'ARRAY');
            $components_sum = $jinput->get('components_sum',null,'INT');
            /*$data['project_id'] = $project_id;*/
            $data['canvases'] = $canvases;
            $data['components'] = $components;
            $data['components_sum'] = $components_sum;
            $materials_model = Gm_ceilingHelpersGm_ceiling::getModel('projects_materials');
            $result = $materials_model->save($project_id,json_encode($data,JSON_UNESCAPED_UNICODE));
            die(json_encode($result));

        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function getProjectMaterials(){
	    try{
            $jinput = JFactory::getApplication()->input;
            $project_id = $jinput->get('project_id',null,'INT');
            $project_model = Gm_ceilingHelpersGm_ceiling::getModel('project');
            $data = json_encode($project_model->getMaterialsForEstimate($project_id));
            die(json_encode($data));
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function saveStageMount(){
	    try{
            $jinput = JFactory::getApplication()->input;
            $project_id = $jinput->getInt('id');
            if(!empty($project_id)){
                if(empty($data)){
                    $data = $jinput->get('mount_data','','STRING');
                }
                if(!empty($data)){
                    $data = json_decode($data);
                    $mounts_model = Gm_ceilingHelpersGm_ceiling::getModel('Projects_mounts');
                    $result = $mounts_model->saveOrUpdateStage($project_id,$data);
                    die(json_encode($result));
                }
                else{
                    throw new Exception("Empty mounting data!");
                }
            }
            else {
                throw new Exception("Empty project_id!");
            }
        }
        catch(Exception $e)
        {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function addNote($project_id, $note,$type = 1) {
        try {
            $die = false;
            if (empty($project_id) && empty($note)) {
                $jinput = JFactory::getApplication()->input;
                $project_id = $jinput->getInt('project_id');
                $note = $jinput->get('note', '', 'STRING');
                $type = $jinput->get('type','','STRING');
                $die = true;
            }
            $project_model = Gm_ceilingHelpersGm_ceiling::getModel('project');
            $result = $project_model->saveNote($project_id, $note,$type);
            if ($die) {
                die(json_encode($result));
            } else {
                return $result;
            }
        } catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function getProjectNotes($project_id) {
        try {
            if (empty($project_id)) {
                $jinput = JFactory::getApplication()->input;
                $project_id = $jinput->getInt('project_id');
            }
            $result = Gm_ceilingHelpersGm_ceiling::getProjectNotes($project_id);
            die(json_encode($result));
        } catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function savePrepayment(){
	    try{
            $jinput = JFactory::getApplication()->input;
            $projectId = $jinput->getInt('project_id');
            $prepaymentSum = $jinput->get('sum','','STRING');
            $clientId = $jinput->getInt('client_id');
            $prepaymentModel = Gm_ceilingHelpersGm_ceiling::getModel('project_prepayment');
            $prepaymentModel->save($projectId,$prepaymentSum);
            $historyModel = Gm_ceilingHelpersGm_ceiling::getModel('client_history');
            $historyModel->save($clientId,"По проекту №$projectId получена предоплата в размере $prepaymentSum руб.");
            die(json_encode(true));
        } catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
    function getPrepayment(){
        try{
            $jinput = JFactory::getApplication()->input;
            $projectId = $jinput->getInt('project_id');
            $prepaymentModel = Gm_ceilingHelpersGm_ceiling::getModel('project_prepayment');
            $result = $prepaymentModel->getData($projectId);
            die(json_encode($result));
        } catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function updateMountDate(){
	    try{
            $jinput = JFactory::getApplication()->input;
            $projectId = $jinput->getInt('project_id');
            if(!empty($projectId)){
                if(empty($data)){
                    $data = $jinput->get('mount_data','','STRING');
                }
                if(!empty($data)){
                    $data = json_decode($data);
                    $mounts_model = Gm_ceilingHelpersGm_ceiling::getModel('Projects_mounts');
                    $result = $mounts_model->save($projectId,$data);
                    die(json_encode($result));
                }
                else{
                    throw new Exception("Empty mounting data!");
                }
            }
            else {
                throw new Exception("Empty project_id!");
            }
        } catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
	    }
    }

    function sendFiles(){
	    try{
            $jinput = JFactory::getApplication()->input;
            $files = $jinput->get('files',array(),'ARRAY');
            $email = $jinput->get('email','','STRING');
            $project_id = $jinput->get('project_id',null,'INT');
            $projectModel = Gm_ceilingHelpersGm_ceiling::getModel('project');
            $project = $projectModel->getData($project_id);
            if(!empty($files)&& !empty($email)){
                $mailer = JFactory::getMailer();
                $config = JFactory::getConfig();
                $sender = array(
                    $config->get('mailfrom'),
                    $config->get('fromname')
                );
                $mailer->setSender($sender);
                $mailer->addRecipient($email);
                $body = "Здравствуйте. Файлы по $project->project_info";
                $mailer->setSubject('Сметы');
                $mailer->setBody($body);

               foreach ($files as $file){
                    $mailer->addAttachment($_SERVER['DOCUMENT_ROOT'] . $file);
                }
                $send = $mailer->Send();
                die(json_encode(true));
            }
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }

    }

    function saveSum(){
	    try{
            $jinput = JFactory::getApplication()->input;
            $projectId = $jinput->getInt('project_id');
            $final_sum = $jinput->get('final_sum','','STRING');
            $projectModel = Gm_ceilingHelpersGm_ceiling::getModel('project');
            $projectModel->saveFinalSum($projectId,$final_sum);
            die(json_encode(true));
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    function runByClient(){
	    try{
            $jinput = JFactory::getApplication()->input;
            $projectId = $jinput->getInt('project_id');
            $mountData = $jinput->get('mount_data','','STRING');
            $mountNote =  $jinput->get('mount_note','','STRING');
            $productionNote =  $jinput->get('production_note','','STRING');
            $status = $jinput->getInt('status');
            if(!empty($mountData)){
                $mountData = json_decode($mountData);
                $projectMountModel = Gm_ceilingHelpersGm_ceiling::getModel('Projects_mounts');
                $projectMountModel->save($projectId,$mountData);
            }

            if(!empty($productionNote)){
                $this->addNote($projectId,$productionNote,4);
            }
            if(!empty($mountNote)){
                $this->addNote($projectId,$mountNote,5);
            }
            $model = $this->getModel('Project', 'Gm_ceilingModel');
            $result = $model->newStatus($projectId, $status);
            die(json_encode($result));
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }

    public function updateProjectData(){
	    try{
	        $jinput = JFactory::getApplication()->input;
	        $projectId = $jinput->getInt('project_id');
	        $data = $jinput->get('project_data',array(),'ARRAY');
	        if(!empty($data)&&$projectId) {
	            $data['id'] = $projectId;
                $projectModel = $this->getModel('Project', 'Gm_ceilingModel');
                $projectModel->save($data);
            }
            die(json_encode(true));
        }
        catch(Exception $e) {
            Gm_ceilingHelpersGm_ceiling::add_error_in_log($e->getMessage(), __FILE__, __FUNCTION__, func_get_args());
        }
    }
}
?>
