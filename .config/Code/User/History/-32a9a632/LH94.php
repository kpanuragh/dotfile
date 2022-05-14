<?php
namespace APP;
class Import {

	public static function learningResults($import_file) {

		$n_records = [
			'n_record_updated' => 0,
			'n_record_inserted' => 0,
			'n_record_rejected' => 0,
			'n_record_existing' => 0,
			'n_record_disabled' => 0,
			'n_record_deleted' => 0,
			'message' => null
		];

		$php_excel = \PHPExcel_IOFactory::load($import_file);
		$sheet = $php_excel->getActiveSheet();
		$rows = $sheet->getRowIterator(1);
		$fields = [
			"A" => "username",
			"B" => "name",
			"C" => "learning_module_id",
			"D" => "created_at",
			"E" => "completed_at",
			"F" => "completion_status",
			"G" => "score",
		];

		$data = [];
		$users = [];
		foreach ($rows as $row) {
			$row_i = $row->getRowIndex();
			$cells = $row->getCellIterator();
			$cells->setIterateOnlyExistingCells($row_i == 1);
			if ($row_i > 1) {
				$data[$row_i] = [];
			}
			foreach ($cells as $cell_i => $cell) {
				if ($row_i != 1) {
					if (
						empty($fields[$cell_i])
					) {
						continue;
					}
					$data[$row_i][$fields[$cell_i]] = $cell->getFormattedValue();
				}
			}
		}

		$log = [];
		foreach ($data as $key => $entry) {
			$entry["completion_status"] = strtolower($entry["completion_status"]);
			$user = \Models\User
				::where('username', $entry['username'])
				->where('status', true)
				->first()
			;
			$module = \Models\LearningModule
				::where('id', $entry['learning_module_id'])
				->where('status', true)
				->first()
			;

			if (
				$module &&
				$user
			) {
				$result = \Models\LearningResult
					::where('learning_module_id', $entry['learning_module_id'])
					->where('user_id', $user->id)
					->where('completion_status', $entry['completion_status'])
				;

				if (
					$entry['completion_status'] == 'completed' &&
					isset($entry['completed_at']) &&
					$entry['completed_at'] &&
					$entry['completed_at'] > ''
				) {
					try {
						$entry["completed_at"] = \Carbon\Carbon::parse($entry["completed_at"]);
					} catch (\Exception $e) {
						$entry["completed_at"] = \Carbon\Carbon::createFromFormat('d/m/Y', $entry["completed_at"]);
					}

					$result = $result
						->whereDate('completed_at', $entry["completed_at"])
					;
				} else {
					$entry["completed_at"] = null;
				}

				if (
					isset($entry['created_at']) &&
					$entry['created_at']
				) {
					try {
						$entry["created_at"] = \Carbon\Carbon::parse($entry["completed_at"]);
					} catch (\Exception $e) {
						$entry["created_at"] = \Carbon\Carbon::createFromFormat('d/m/Y', $entry["completed_at"]);
					}
				} else {
					$entry["created_at"] = null;
				}



				$result = $result
					->first()
				;


				if (
					!$result &&
					$entry['completion_status'] == 'completed'
				) {
					//echo "\n";
					//echo $entry['username'] . " - " . $user->id . " - " . $entry['learning_module_id'] . " - " . $entry["completed_at"] . " - " . $entry['completion_status'] . " - result does not exist.";
					$entry['refreshed'] = 1;
					$entry['user_id'] = $user->id;

					$learning_result = new \Models\LearningResult;
					$learning_result->user_id = $entry['user_id'];
					$learning_result->learning_module_id = $entry['learning_module_id'];
					$learning_result->completed_at = $entry['completed_at'];
					$learning_result->completion_status = $entry['completion_status'];
					$learning_result->created_at = $entry['created_at'];
					$learning_result->updated_at = \Carbon\Carbon::now();
					$learning_result->score = $entry['score'];
					$learning_result->refreshed = 1;
					$learning_result->save();

					$n_records['n_record_inserted']++;

					$log[] = [
						'record' => [
							'username' => $entry['username'],
							'name' => $module->name,
							'learning_module_id' => $entry['learning_module_id'],
							'created_at' => $entry['created_at'],
							'completed_at' => $entry['completed_at'],
							'completion_status' => $entry['completion_status'],
							'score' => $entry['score'],
						]
					];
				}
			} else {
				//echo "\n";
				//echo $entry['username'] . " - user does not exists";

			}

		}

		/*
		Futile attempt to fix existing data, park it or cancel it for now.
		$users = array_unique($users);

		// Need to find all users who have learning result entry
		$results = \Models\LearningResult
			::whereIn('user_id', $users)
			->groupBy('user_id', 'learning_module_id')
			->get()
		;

		//print_r(json_encode($results));
		foreach ($results as $key => $result) {
			//echo "\n";
			//echo $result->user_id . " - " . $result->learning_module_id;
			$lresults = \Models\LearningResult
				::where('user_id', $result->user_id)
				->where('learning_module_id', $result->learning_module_id)
				->get()
			;
			if (
				count($lresults) > 1 ||
				(
					count($lresults) == 1 &&
					$lresults[0]->refreshed === true
				)
			)  {
				if (count($lresults) == 1) {
					// need to set this one as refreshed = false;
				} else {
					$resource = \Models\LearningModule::find($result->learning_module_id);
					$refreshed_cnt = 0;
					foreach ($lresults as $key => $lresult) {
						if ($lresult->refreshed) {
							$refreshed_cnt++;
						}
					}

					echo "\n";
					echo count($lresults);
					echo " - ";
					echo $refreshed_cnt;
					echo " - ";
					echo $resource->refresh;
					if ($refreshed_cnt == 0) {
						// if resource does not need refresh, delete newest record!
					}
					if (
						!$resource->refresh
					) {
						echo " - delete - ";
						echo $result->user_id . " - " . $result->learning_module_id;
					}


				}
			}
		}
		die();
		*/

		$n_records['log'] = $log;
		return $n_records;
	}

	public static function userEventData($import_file) {

		$n_records = [
			'n_record_updated' => 0,
			'n_record_inserted' => 0,
			'n_record_rejected' => 0,
			'n_record_existing' => 0,
			'n_record_disabled' => 0,
			'n_record_deleted' => 0,
			'message' => null
		];

		$php_excel = \PHPExcel_IOFactory::load($import_file);
                $php_excel->setActiveSheetIndex(0);
                $sheet = $php_excel->getActiveSheet();
		$rows = $sheet->getRowIterator(1);
		$fields = [
			"A" => "username",
			"B" => "event_id",
			"C" => "completion_status",
			"D" => "type",
			"E" => "is_recive_email",
			"F" => "is_authorized",
			"G" => "authorisation_note",
		];

		$data = [];
                $users = [];
		foreach ($rows as $row) {
			$row_i = $row->getRowIndex();
			$cells = $row->getCellIterator();
			$cells->setIterateOnlyExistingCells($row_i == 1);
			if ($row_i > 1) {
				$data[$row_i] = [];
                        }
                        foreach ($cells as $cell_i => $cell) {
				if ($row_i != 1) {
					if (
						empty($fields[$cell_i])
					) {
						continue;
                                        }
					$data[$row_i][$fields[$cell_i]] = $cell->getFormattedValue();
				}
			}
		}
		
		$log = [];
		foreach ($data as $key => $entry) {
			$entry["completion_status"] = ucwords($entry["completion_status"]);
			$user = \Models\User
				::where('username', $entry['username'])
				->where('status', true)
				->first()
			;
			 $schedule = \Models\Schedule
			 	::where('id', $entry['event_id'])
			 	->where('status', true)
			 	->first()
                         ;

			if (
			        $schedule &&
				$user
			) {
				
                        $link_data=[];
                        $link_data['schedule_id']=$schedule->id;
                        $link_data['link_id']=$user->id;
                        $link_data['approval']=true;
                        if(!empty($entry['type']))
                        {
                            $link_data['type']=trim(strtolower($entry['type']))=='yes'?'users_queue':'users';
                        }else {
                         $link_data['type']='users'; 
                        }
                        $link=\Models\ScheduleLink::addNewLink($link_data);
                        if($link)
                        {
                          if(!empty($entry['completion_status']) && in_array($entry['completion_status'],['Completed','Not Attempted','Not Attempted'])){
                          $link->completion_status=$entry['completion_status'];
                          }
                          if(!empty($entry['is_authorized']))
                            {
                          $link->is_authorised=strtolower($entry['is_authorized'])=='yes'?true:false;
                            }
                          if(!empty($entry['is_recive_email']))
                            {
                              $link->ignore_email=strtolower($entry['is_recive_email'])=='yes'?false:true;
                            }
                            if(!empty($entry['authorisation_note']))
                            {
                                $link->authorisation_notes=$entry['authorisation_note'];
                            }
                          $link->save();
                          $n_records['n_record_inserted']++;
                        }else {
                          $n_records['n_record_rejected']++; 
                        }

			$log[] = [
			    'record' => [
                            'username' => $entry['username'],
                            'name'=>$schedule->name,
			    'event_id' => $entry['event_id'],
			    'type' => $entry['type'],
                            'is_recive_email' => $entry['is_recive_email'],
                            'is_authorized'=> $entry['is_authorized'],
                            'authorisation_note'=> $entry['authorisation_note'],
			    'completion_status' => $entry['completion_status']
						]
			    ];
				//}
                        } else {
                       $n_records['n_record_rejected']++; 

			//	echo "\n";
			//	echo $entry['username'] . " - user does not exists";

			}

		}
		$n_records['log'] = $log;
		return $n_records;
	}

	public static function userEventData($import_file) {

		$n_records = [
			'n_record_updated' => 0,
			'n_record_inserted' => 0,
			'n_record_rejected' => 0,
			'n_record_existing' => 0,
			'n_record_disabled' => 0,
			'n_record_deleted' => 0,
			'message' => null
		];

		$php_excel = \PHPExcel_IOFactory::load($import_file);
                $php_excel->setActiveSheetIndex(0);
                $sheet = $php_excel->getActiveSheet();
		$rows = $sheet->getRowIterator(1);
		$fields = [
			"A" => "username",
			"B" => "event_id",
			"C" => "completion_status",
			"D" => "type",
			"E" => "is_recive_email",
			"F" => "is_authorized",
			"G" => "authorisation_note",
		];

		$data = [];
                $users = [];
		foreach ($rows as $row) {
			$row_i = $row->getRowIndex();
			$cells = $row->getCellIterator();
			$cells->setIterateOnlyExistingCells($row_i == 1);
			if ($row_i > 1) {
				$data[$row_i] = [];
                        }
                        foreach ($cells as $cell_i => $cell) {
				if ($row_i != 1) {
					if (
						empty($fields[$cell_i])
					) {
						continue;
                                        }
					$data[$row_i][$fields[$cell_i]] = $cell->getFormattedValue();
				}
			}
		}
		
		$log = [];
		foreach ($data as $key => $entry) {
			$entry["completion_status"] = ucwords($entry["completion_status"]);
			$user = \Models\User
				::where('username', $entry['username'])
				->where('status', true)
				->first()
			;
			 $schedule = \Models\Schedule
			 	::where('id', $entry['event_id'])
			 	->where('status', true)
			 	->first()
                         ;

			if (
			        $schedule &&
				$user
			) {
				
                        $link_data=[];
                        $link_data['schedule_id']=$schedule->id;
                        $link_data['link_id']=$user->id;
                        $link_data['approval']=true;
                        if(!empty($entry['type']))
                        {
                            $link_data['type']=trim(strtolower($entry['type']))=='yes'?'users_queue':'users';
                        }else {
                         $link_data['type']='users'; 
                        }
                        $link=\Models\ScheduleLink::addNewLink($link_data);
                        if($link)
                        {
                          if(!empty($entry['completion_status']) && in_array($entry['completion_status'],['Completed','Not Attempted','Not Attempted'])){
                          $link->completion_status=$entry['completion_status'];
                          }
                          if(!empty($entry['is_authorized']))
                            {
                          $link->is_authorised=strtolower($entry['is_authorized'])=='yes'?true:false;
                            }
                          if(!empty($entry['is_recive_email']))
                            {
                              $link->ignore_email=strtolower($entry['is_recive_email'])=='yes'?false:true;
                            }
                            if(!empty($entry['authorisation_note']))
                            {
                                $link->authorisation_notes=$entry['authorisation_note'];
                            }
                          $link->save();
                          $n_records['n_record_inserted']++;
                        }else {
                          $n_records['n_record_rejected']++; 
                        }

			$log[] = [
			    'record' => [
                            'username' => $entry['username'],
                            'name'=>$schedule->name,
			    'event_id' => $entry['event_id'],
			    'type' => $entry['type'],
                            'is_recive_email' => $entry['is_recive_email'],
                            'is_authorized'=> $entry['is_authorized'],
                            'authorisation_note'=> $entry['authorisation_note'],
			    'completion_status' => $entry['completion_status']
						]
			    ];
				//}
                        } else {
                       $n_records['n_record_rejected']++; 

			//	echo "\n";
			//	echo $entry['username'] . " - user does not exists";

			}

		}
		$n_records['log'] = $log;
		return $n_records;
	}
}
