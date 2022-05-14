<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \GuzzleHttp\Psr7\LazyOpenStream as OpenStream;
use Illuminate\Database\Capsule\Manager as DB;
use Models\ResourceQuery;

$app->group("/schedule", function () {

	// List all events and if schedule_id is provided, check if event is linked with provided schedule_id
	$this->post('/list[/{event_id:\d+}]', function (Request $request, Response $response, array $args) {
		$params = $request->getParsedBody();
		$query = \Models\Schedule
			::select(
				'schedules.id',
				'schedules.name',
				'schedules.start_date'
			)
		;
		$query = \Models\Schedule::countAndConditions($query, $params);

		/*For excluding current event, for https://emil-reisser-weston.atlassian.net/browse/SCOR-2331*/
		if (isset($args["event_id"]) && !empty($args["event_id"])) {
			$query =  $query->where('schedules.id','!=',$args["event_id"]);
		}

		if (isset($params["search"]["refresh"])) {
			unset($params["search"]["refresh"]);
		}


		$p = \APP\SmartTable::searchPaginate($params, $query);

		return
			$response
				->withHeader('Content-Type', 'application/json')
				->write($p->toJson())
		;
	})->add(\APP\Auth::getStructureAccessCheck(['lessons-and-learning-resources'], 'select'));

	$this->get('/all', function (Request $request, Response $response, array $args) {

		$events = \Models\Schedule
			::where('schedules.status', true)
			->where('parent_id', null)
			->with('Children')
			->with(['Category' => function ($query) {
				$query
					->select(
						'learning_module_categories.id',
						'learning_module_categories.name'
					)
					->where('learning_module_categories.status', 1)
				;
			}])
			//->with('Owner.User')
			->with(['Lessons' => function($query) {
				$query = $query
					->select(
						'learning_modules.id',
						'learning_modules.name'
					)
					->where('learning_modules.status', true)
				;
			}])
			->selectRaw("DATE_ADD(start_date, INTERVAL duration minute) AS end_date")
		;
		if (\APP\Auth::isCd()) {
			$events = $events
				->select(
					'schedules.id',
					'schedules.name',
					'schedules.type',
					'schedules.category_id',
					'schedules.visit_type_id',
					'schedules.start_date',
					'schedules.duration',
					'schedules.parent_id',
                    'schedules.created_by',
                    'schedules.minclass',
                    'schedules.maxclass'
				)
			;
		} else {
			$events = $events
				->select('schedules.*')
			;
			// Show events to manager list only if this is true!
			if (!\APP\Auth::isLearner()) {
				$events = $events
					->where('visible_schedule', true)//New condition added for visible schedule
				;
			}

		}


		// If learner, show only those schedules this learner is linked with!
        if (\APP\Auth::isLearner()) {
            $resources = ResourceQuery::where('type', 'events')->get();
            $resources = $resources->map(function ($value) {
                return str_replace("SELECT users.id FROM", "SELECT users.id, $value->type_id as event_id FROM", $value->raw_query);
            });
            $resources = $resources->implode(' UNION ');

        /*Getting events query*/
			 if($resources){
                $events = $events
                    ->leftJoin(DB::raw("($resources)resource_query"),function($join){
                        $join->on('schedules.id', '=', 'resource_query.event_id');
                    });

            }
				$events=$events->where(function($query)use ($resources){
                    $query->where('enrole_any_learner', true);
                    if($resources){
                        $query->where(function($query){
                            $query->where('resource_query.id',\APP\Auth::getUserId());
                            $query->orWhere('resource_query.id',null);
                        });
                    }
                })
				->orWhereIn('schedules.id',
					\Models\ScheduleLink
						::select('schedule_id')
						->where('type', 'users')
						->where('link_id', \APP\Auth::getUserId())

						->get()
				)
				->with(['Files' => function ($query) {
					$query
						->where('status', true)
						->with(['AddedBy' => function ($query) {
							$query
								->select(
									'id',
									'fname',
									'lname',
									'role_id'
								)
								->with(['role' => function ($query) {
									$query
										->select(
											'id',
											'name'
										)
									;
								}])
							;
						}])
					;
				}])
				->with(['Resources' => function ($query) {
					$query = $query
						->select(
							'learning_modules.id',
							'learning_modules.name',
							'learning_modules.description',
							'learning_modules.type_id',
							'learning_modules.is_course',
							'learning_modules.material'
						)
						->where('learning_modules.status', true)
						->with('Type');
				}])
				->with(['Visitors' => function ($query) {
					$query
						->select(
							'users.id',
							'fname',
							'lname',
							'role_id'
						)
						->where('users.status', true)
					;

				}])
				->with(['Users' => function ($query) {
					$query
						->select(
							'users.id',
							'fname',
							'lname',
							'role_id',
							'schedule_links.approved'
						)
						->where('users.status', true)
					;
				}])
				->with(['Waiting' => function ($query) {
					$query
						->select(
							'users.id',
							'fname',
							'lname',
							'role_id',
							'schedule_links.approved'
						)
						->where('users.status', true)
					;
				}])
				->with(['Comments' => function ($query) {
					$query
						->where('status', true)
						->where('visible_learner', true)
						->with(['AddedBy' => function ($query) {
							$query
								->select(
									'id',
									'fname',
									'lname',
									'role_id',
									'image'
								)
								->with(['role' => function ($query) {
									$query
										->select(
											'id',
											'name'
										);
								}]);
						}]);
				}]);

            $events = $events->leftJoin("table_extensions", function($join) {
                $join
                    ->on("table_extensions.table_id", "=", "schedules.id")
                    ->where("table_extensions.table", "=", "schedules");

            })
                ->leftJoin("venues", function ($join1) {
                    $join1->on("venues.id", "=", "table_extensions.value")
                        ->select('venues.name as venue');
                })
                ->select('schedules.*','venues.name as venue');
		} else {
			// For manager, show schedules that were added by manager

			if (!\APP\Auth::isCd()) {
				$events = $events
					->where(function($query) {
						if (!\APP\Tools::getConfig('listAllEventsToManagers')) {
							$query
								->whereHas('Permissions', function ($query) {
									$query
										->where('user_id', \APP\Auth::getUserId())
									;
								})
								->orWhereHas('Managers', function ($query) {
									$query
										->where('users.id', \APP\Auth::getUserId())
									;
								})
							;
						}
					})
				;
			}

			$events = $events
				->with(['Departments' => function ($query) {
					$query
						->select(
							'departments.id',
							'departments.name'
						)
						->where('departments.status', 1)
					;
				}])
				->with(['Groups' => function ($query) {
					$query
						->select(
							'groups.id',
							'groups.name'
						)
						->where('groups.status', 1)
					;
				}])
				->with(['Managers' => function($query) use ($args) {
					$query = $query
						->select(
							'users.id',
							'users.fname',
							'users.lname',
							'users.email',
							'users.department_id'
						)
						->selectRaw("CONCAT(users.fname, ' ', users.lname) as full_name")
						->where('users.status', true)
						->with(['Groups' => function ($query) {
							$query
								->select(
									'groups.id',
									'groups.name'
								)
								->where('groups.status', 1)
							;
						}])
					;
				}])
			;
			if (!\APP\Auth::isCd()) {
				$events = $events
					->with(['Users' => function ($query) use ($args) {
						$query = $query
							->select(
								'users.id',
								'users.fname',
								'users.lname',
								'users.email'
							)
							->selectRaw("CONCAT(users.fname, ' ', users.lname) as full_name")
							->where('users.status', true)
						;
					}])
				;
			}
		}

		$events = $events
			->get();

		return
			$response
				->withHeader('Content-Type', 'application/json')
				->write(json_encode($events));
	})->add(\APP\Auth::getStructureAccessCheck(['lessons-and-learning-resources', 'trainee-standards'], 'select'));

	$this->get('/enrollable/all', function (Request $request, Response $response, array $args) {
		$resources = ResourceQuery::where('type', 'events')->get();
		$resources = $resources->map(function ($value) {
			return str_replace("SELECT users.id FROM", "SELECT users.id, $value->type_id as event_id FROM", $value->raw_query);
		});
		$resources = $resources->implode(' UNION ');
		$events = \Models\Schedule
			::select(
				'schedules.*',
				DB::raw("CONCAT(DATE_FORMAT(start_date,'%d/%m/%Y %H:%i'), '-', DATE_FORMAT(DATE_ADD(start_date, INTERVAL duration MINUTE),'%H:%i')) AS event_date_range")
			)
			->where('schedules.status', true)
			->where('visible_schedule', true)
			->where('parent_id', null)
			->with('Children')
			->with(['Users' => function ($query) {
				$query
					->select(
						'users.id',
						'role_id',
						'schedule_links.approved'
					)
					->where('users.status', true)
				;
			}])
			->with(['Waiting' => function ($query) {
				$query
					->select(
						'users.id',
						'role_id',
						'schedule_links.approved'
					)
					->where('users.status', true)
					->where('users.id', \APP\Auth::getUserId())
				;
			}])
			->with(['Category' => function ($query) {
				$query
					->select(
						'learning_module_categories.id',
						'learning_module_categories.name'
					)
					->where('learning_module_categories.status', 1)
				;
			}])
			->with(['Lessons' => function($query) {
				$query = $query
					->select(
						'learning_modules.id',
						'learning_modules.name',
						'learning_modules.keywords',
						'learning_modules.language'
					)
					->where('learning_modules.status', true)
				;
			}])
			->with(['Waiting' => function ($query) {
				$query
					->select(
						'users.id',
						'fname',
						'lname',
						'role_id',
						'schedule_links.approved'
					)
					->where('users.status', true)
					->where('users.id', \APP\Auth::getUserId())
				;
			}])
			->selectRaw("DATE_ADD(start_date, INTERVAL duration minute) AS end_date")
			->whereNotIn('schedules.id',
				\Models\ScheduleLink
					::select('schedule_id')
					->where('type', 'users')
					->where('link_id', \APP\Auth::getUserId())
					->whereNull('deleted_at')
					->whereNull('deleted_by')
					->get()
			)
		;
		if($resources){
			$events = $events
				->leftJoin(DB::raw("($resources)resource_query"),function($join){
					$join->on('schedules.id', '=', 'resource_query.event_id');
				});

		}
		$events=$events->where(function($query)use ($resources){
			$query->where('enrole_any_learner', true);
			if($resources){
				$query->where(function($query){
					$query->where('resource_query.id',\APP\Auth::getUserId());
					$query->orWhere('resource_query.id',null);
				});
			}
		});
		$events = $events
			->get();

		foreach($events as $event) {
			$event->lessons[0]->setAppends(['safe_thumbnail', 'highlight', 'safe_promo']);
		}

		return
			$response
				->withHeader('Content-Type', 'application/json')
				->write(json_encode($events));
	})->add(\APP\Auth::getStructureAccessCheck(['lessons-and-learning-resources', 'trainee-standards'], 'select'));

	$this->put('/update-lesson-name/{lesson_id:[0-9]+}', function (Request $request, Response $response, array $args) {
		$data = $request->getParsedBody();

		$lesson = \Models\LearningModule::find($args['lesson_id']);

		if (
			$lesson->is_course &&
			isset($data['name']) &&
			$data['name']
		) {
			$lesson->name = $data['name'];
			$lesson->save();
		}


		return
			$response
			//->withHeader('Content-Type', 'application/json')
			//->write(json_encode($department_issues))
			;
	})->add(\APP\Auth::getStructureAccessCheck('lessons-and-learning-resources', 'update'));


	$this->put('/toggle-lesson-order_modules/{lesson_id:[0-9]+}', function (Request $request, Response $response, array $args) {

		$lesson = \Models\LearningModule::find($args['lesson_id']);
		$lesson->order_modules = !$lesson->order_modules;
		$lesson->save();


		return
			$response;
	})->add(\APP\Auth::getStructureAccessCheck('lessons-and-learning-resources', 'update'));

	$this->put('/update-lesson-resource-order/{schedule_id:[0-9]+}', function (Request $request, Response $response, array $args) {
		$data = $request->getParsedBody();
		$event = \Models\Schedule::find($args['schedule_id']);

		if (
			$event &&
			$data['new_order'] &&
			is_array($data['new_order'])
		) {
			// This will trigger for managers who created lesson(created_by) and event(created_by)
			$lessons = $event->Lessons;
			if (
				$lessons &&
				$lessons[0]
			) {
				$lesson = $lessons[0];
				\Models\ScheduleLink::processAction(
					'create',
					$lesson->id,
					'lesson',
					[
						'link' => 'resources',
						'entries' => $data['new_order']
					]
				);
			}
		}


		return
			$response;
	})->add(\APP\Auth::getStructureAccessCheck('lessons-and-learning-resources', 'update'));


	// Get resources assigned to lesson
	$this->get('/lesson-resources/{lesson_id:[0-9]+}', function (Request $request, Response $response, array $args) {
		$data = $request->getParsedBody();

		$resources = \Models\LearningModule
			::select('learning_modules.*')
			->where('status', true)
			->whereHas('Course', function ($query) use ($args) {
				$query
					->where('learning_course_id', $args['lesson_id']);
			})
			->join("learning_course_modules", function ($join) use ($args) {
				$join
					->on("learning_course_modules.learning_module_id", "learning_modules.id")
					->where("learning_course_modules.learning_course_id", $args['lesson_id']);
			})
			->orderBy('learning_course_modules.id', 'ASC')
			->get();

		foreach($resources as $resource) {
			$resource->setAppends(['safe_thumbnail', 'safe_promo']);
		}

		return
			$response
				->withHeader('Content-Type', 'application/json')
				->write(json_encode($resources));
	})->add(\APP\Auth::getStructureAccessCheck('lessons-and-learning-resources', 'update'));

	$this->get('/update-from-lesson/{event_id:[0-9]+}', function (Request $request, Response $response, array $args) {

			$lesson = \Models\LearningModule
				::where('is_course', 1)
				->whereIn('learning_modules.id',
					\Models\ScheduleLink
						::select('link_id')
						->where('schedule_id', $args['event_id'])
						->where('type', 'lesson')
						->get()
				)
				->first()
			;
			if ($lesson) {
				$data = [
					'schedule_id' => $args['event_id'],
					'type' => 'resources'
				];
				foreach ($lesson->Resources as $key => $resource) {
					$data["link_id"] = $resource->id;
					\Models\ScheduleLink::addNewLink($data);
				}
			}


		return
			$response
		;
	})->add(\APP\Auth::getStructureAccessCheck('lessons-and-learning-resources', 'update'));


	// Restore deleted event!
	$this->get('/restore/{event_id:[0-9]+}', function (Request $request, Response $response, array $args) {
		$event = \Models\Schedule
			::where('id', $args['event_id'])
			->onlyTrashed()
			->first()
		;

		if ($event) {
			// Find all schedule links, restore them!
			$links = \Models\ScheduleLink
				::where('schedule_id', $event->id)
				->onlyTrashed()
				->get()
			;

			foreach ($links as $key => $link) {
				$link->status = true;
				$link->cron_task = true;
				$link->ignore_email = true; // Mark link to not send email about event creation.
				$link->save();
				$link->restore();
			}
			//restore children
			$children = \Models\Schedule
				::where('parent_id', $event->id)
				->onlyTrashed()
				->get()
			;
			foreach ($children as $key => $child) {
				$child->status = true;
				$child->cron_task = false;
				$child->save();
				$child->restore();
			}

			$event->status = true;
			$event->cron_task = true;
			$event->save();
			$event->restore();
		} else {
			return
				$response
					->withStatus(404)
					->withHeader('Content-Type', 'text/html')
					->write('404 Not Found')
			;
		}



		return
			$response
		;
	})->add(\APP\Auth::getStructureAccessCheck('system-setup-deleted-events', 'disable'));

	// Return comments against schedule
	$this->get('/{schedule_id:[0-9]+}/comment', function (Request $request, Response $response, $args) {

		if (!\APP\Auth::isAdminInterface()) {
			return false;
		}

		$comments = \Models\Comment::where([['table_row_id', '=', $args['schedule_id']],
			['table_name', '=', "schedules"]])
			->with(['AddedBy' => function ($query) {
				$query
					->select(
						'id',
						'fname',
						'lname',
						'role_id',
						'image'
					)
					->with('role');
			}])->orderBy('id', "DESC")->get();

		return
			$response
				->withHeader('Content-Type', 'application/json')
				->write(json_encode($comments));
	})->add(\APP\Auth::getStructureAccessCheck('lessons-and-learning-resources', 'select'));

	/*
		V2, proper schedule events, not tied to specific relationship
	*/

	// Get scheduled event
	$this->get('/{event_id:[0-9]+}{type:[\/a-z]*}', function (Request $request, Response $response, array $args) {
		$data = $request->getParsedBody();
		if (!\APP\Auth::isAdminInterface()) {

			$event = \Models\Schedule
			::where('id', $args['event_id'])
			->where('parent_id', null)
			->with(['Children' => function ($query) use ($args) {
				if ($args['type'] == '/deleted') {
					$query = $query
						->withTrashed()
					;
				}
			}])->first();
			$ids=\Models\Schedule::getChild($event->id);
			$schedules=\Models\Schedule::whereIn("id",$ids)
			->where('enrole_any_learner',1)
			->with(['Users' => function ($query) {
				$query = $query
					->select(
						'users.id'
					)
					->where('users.status', true)
				;
			}])->get();
			$event->schedules=$schedules;
			return
				$response
					->withHeader('Content-Type', 'application/json')
					->write(json_encode($event));
		}

		$event = \Models\Schedule
			::where('id', $args['event_id'])
			->where('parent_id', null)
			->with(['Children' => function ($query) use ($args) {
				if ($args['type'] == '/deleted') {
					$query = $query
						->withTrashed()
					;
				}
			}])
			->with('Category')
			->with('Departments')
			->with('Groups')
			->with('VisitType')
			->with(['Lessons' => function($query) use ($args) {
				$query = $query
					->select(
						'learning_modules.id',
						'learning_modules.name',
						'learning_modules.description',
						'learning_modules.type_id',
						'learning_modules.is_course',
						'learning_modules.category_id',
                        'learning_modules.created_by',
                        'learning_modules.hide_lesson'
					)
					->where('learning_modules.status', true)
					->with('Type')
					->with(['Files' => function($query) {
						$query
							->where('status', true)
							->with(['AddedBy' => function ($query) {
								$query
									->select(
										'id',
										'fname',
										'lname',
										'role_id'
									)
									->with('role');
							}])
						;
					}])
				;
				if ($args['type'] == '/deleted') {
					$query = $query
						->withTrashed('schedule_links.deleted_at')
					;
				}
			}])
			->with(['Files' => function ($query) {
				$query
					->where('status', true)
					->with(['AddedBy' => function ($query) {
						$query
							->select(
								'id',
								'fname',
								'lname',
								'role_id'
							)
							->with('role')
						;
					}])
				;
			}])
			->with(['Comments' => function ($query) {
				$query
					->where('status', true)
					->with(['AddedBy' => function ($query) {
						$query
							->select(
								'id',
								'fname',
								'lname',
								'role_id',
								'image'
							)
							->with('role');
					}]);
			}])
			/*Forum Added by managers*/
			->with(['forum' => function ($query) {
				$query
					->select('id', 'schedule_id', 'added_by', 'visible_learner', 'created_at', 'updated_at')
					->with(['AddedBy' => function ($query) {
						$query
							->select(
								'id',
								'fname',
								'lname',
								'role_id',
								'image'
							)
							->with('role');
					}])
					->with(['topics' => function ($query) {
						$query
							->select('id', 'forum_id', 'added_by', 'created_at', 'updated_at', 'name', 'content')
							->with(['AddedBy' => function ($query) {
								$query
									->select(
										'id',
										'fname',
										'lname',
										'role_id',
										'image'
									)
									->with('role');
							}])
							->first();
					}])
				;
			}])
			/*Forum ends here*/
			->with(['Programmes' => function($query) use ($args) {
				$query = $query
					->select(
						'apprenticeship_standards.id',
						'apprenticeship_standards.name'
					)
					->where('apprenticeship_standards.status', true)
				;
				if ($args['type'] == '/deleted') {
					$query = $query
						->withTrashed('schedule_links.deleted_at')
					;
				}
			}])
			->with(['resources' => function($query) use ($args) {
				$query = $query
					->select(
						'learning_modules.id',
						'learning_modules.name',
						'learning_modules.description',
						'learning_modules.type_id',
						'learning_modules.is_course',
						'learning_modules.material',
						'learning_modules.thumbnail',
						'learning_modules.promo_image'
					)
					->where('learning_modules.status', true)
					->with('type')
					// This is only for instructor lead lesson
					->with(['LearningResult' => function ($query) {
						$query
							->where('user_id', \APP\Auth::getUserId())
							->where('refreshed', 0)
							->select(
								'id',
								'completion_status',
								'learning_module_id'
							)
						;
					}])
					->with(['ScheduleLink' => function ($query) use ($args) {
						$query = $query
							->where('schedule_id', $args['event_id'])
						;
						if ($args['type'] == '/deleted') {
							$query = $query
								->withTrashed()
							;
						}
					}])
				;
				if ($args['type'] == '/deleted') {
					$query = $query
						->withTrashed('schedule_links.deleted_at')
					;
				}
			}])
			->with(['Users' => function ($query) use ($args) {
				$query = $query
					->select(
						'users.id',
						'users.fname',
						'users.lname',
						'users.email',
						'users.company_id',
						'users.department_id'
					)
					->where('users.status', true)
					->with(['ScheduleLink' => function ($query) use ($args) {
						$query = $query
							->where('schedule_id', $args['event_id'])
						;
					}])
					->with('company')
					->with('department')
					->withCount('CredasCourse')
				;
				if(\APP\Auth::isManager() && !\APP\Auth::accessAllLearners())
				{
					$query->with(['Managers'=>function($query){
						$query->where('manager_id',\APP\Auth::getUserId());
					}]);
				}
				if ($args['type'] == '/deleted') {
					$query = $query
						->withTrashed('schedule_links.deleted_at')
					;
				}

			}])
			->with(['Waiting' => function ($query) use ($args) {
				$query = $query
					->select(
						'users.id',
						'users.fname',
						'users.lname',
						'users.email'
					)
					->where('users.status', true)
					->with(['WaitingScheduleLink' => function ($query) use ($args) {
						$query = $query
							->where('schedule_id', $args['event_id'])
						;
					}])
				;
				if ($args['type'] == '/deleted') {
					$query = $query
						->withTrashed('schedule_links.deleted_at')
					;
				}

			}])
			->with(['Managers' => function ($query) use ($args) {
				$query = $query
					->select(
						'users.id',
						'users.fname',
						'users.lname',
						'users.email'
					)
					->where('users.status', true)
					->with(['ManagerScheduleLink' => function ($query) use ($args) {
						$query = $query
							->where('schedule_id', $args['event_id']);
					}])
				;
				if ($args['type'] == '/deleted') {
					$query = $query
						->withTrashed('schedule_links.deleted_at')
					;
				}
			}])
			->select('schedules.*')
			->selectRaw("DATE_ADD(start_date, INTERVAL duration minute) AS end_date")
		;

		if ($args['type'] == '/deleted') {
			$event = $event
				->onlyTrashed()
			;
		}

		$event = $event
			->first()
		;


		\Models\TableExtension::returnAllFields('schedules', $event->id, $event);
		if (!$event) {
			return
				$response
					->withStatus(404)
					->withHeader('Content-Type', 'text/html')
					->write('404 Not Found')
			;
		}

		foreach($event->resources as $resource) {
			$resource->setAppends(['safe_thumbnail', 'safe_promo']);
		}

		// I need to find events that overlap with this event, then find out if any user/manager from this event overlaps as well!
		// Also all repeat lessons!!! <- NOT I MPLEMENTED

		// Generate end date!
		//$event->end_date = \Carbon\Carbon::parse($event->start_date)->addMinutes($event->duration)->format('Y-m-d H:i:s');

		// Pluck current event manager and user id's
		$user_id_list = $event->Users->pluck('id')->toArray();
		$manager_id_list = $event->Managers->pluck('id')->toArray();

		// Find events that overlap current event!
		$over_lap_events = \Models\Schedule
			::where('status', true)
			->select('schedules.*')
			->selectRaw("DATE_ADD(start_date, INTERVAL duration minute) AS end_date")
			->whereNotIn('schedules.id', [$event->id])
			->where(function ($query) use ($event) {
				$query
					->whereBetween("start_date", [$event->start_date, \Carbon\Carbon::parse($event->end_date)->subMinute()])
					->orWhereRaw('(DATE_ADD(start_date, INTERVAL (duration-1) minute) BETWEEN ? AND ?)', [$event->start_date,\Carbon\Carbon::parse($event->end_date)->subMinute()])
					->orWhereRaw('( ? BETWEEN start_date AND DATE_ADD(start_date, INTERVAL (duration-1) minute))', [$event->start_date])
					->orWhereRaw('( ? BETWEEN start_date AND DATE_ADD(start_date, INTERVAL (duration-1) minute))', [\Carbon\Carbon::parse($event->end_date)->subMinute()])
				;
			})
			->with(['Users' => function($query) use ($user_id_list) {
				$query = $query
					->select(
						'users.id',
						'users.fname',
						'users.lname',
						'users.email'
					)
					->where('users.status', true)
					->whereIn('users.id', $user_id_list)
				;
			}])
			->with(['Managers' => function($query) use ($manager_id_list) {
				$query = $query
					->select(
						'users.id',
						'users.fname',
						'users.lname',
						'users.email'
					)
					->where('users.status', true)
					->whereIn('users.id', $manager_id_list)
				;
			}])
			->get()
		;

		$overlapping_cnt = 0;

		// Loop current users and compare with overlap users, if found add  overlapping event to entry against current user
		foreach ($event->Users as $key => $event_user) {
			$event_user->overlap_events = [];
			$event_user->notification = true;
			$event_user->checked = false;
			$event_userArray = $event_user->toArray();
			if ($event_userArray['schedule_link']['ignore_email']) {
				$event_user->notification = false;
			}
			$event_user->schedule_link_id = $event_userArray['schedule_link']['id'];
			foreach ($over_lap_events as $key => $over_lap_event) {
				foreach ($over_lap_event->Users as $key => $overlap_user) {
					if ($overlap_user->id == $event_user->id) {
						$overlapping_cnt++;
						$temp_event = new \stdClass();
						$temp_event->name = $over_lap_event->name;
						$temp_event->start_date = \Carbon\Carbon::parse($over_lap_event->start_date)->format("d/m/Y H:i");
						$event_user->overlap_events = array_merge($event_user->overlap_events, [$temp_event]);
					}
				}
			}
		}

		// Same for managers are users
		foreach ($event->Managers as $key => $event_manager) {
			$event_manager->overlap_events = [];
			foreach ($over_lap_events as $key => $over_lap_event) {
				foreach ($over_lap_event->Managers as $key => $overlap_manager) {
					if ($overlap_manager->id == $event_manager->id) {
						$overlapping_cnt++;
						$temp_event = new \stdClass();
						$temp_event->name = $over_lap_event->name;
						$temp_event->start_date = \Carbon\Carbon::parse($over_lap_event->start_date)->format("d/m/Y H:i");
						$event_manager->overlap_events = array_merge($event_manager->overlap_events, [$temp_event]);
					}
				}
			}
		}

		// Set boolean if there is at least one overlap event.

		$event->over_lap_event = $overlapping_cnt > 0 ? true : false;
		$event->hasPermission=\Models\SchedulePermission::hasOwnerPermission($event->id);
		$event->userPermission=\APP\Auth::accessAllLearners();
		$ids=\Models\Schedule::getChild($event->id);
		$schedules=\Models\Schedule::whereIn("id",$ids)->get();
		$event->schedules=$schedules;

		return
			$response
				->withHeader('Content-Type', 'application/json')
				->write(json_encode($event))
		;
	})->add(\APP\Auth::getStructureAccessCheck('lessons-and-learning-resources', 'select'));

	// Create new schedule event!
	$this->post('/new', function (Request $request, Response $response, array $args) {


		$data = $request->getParsedBody();
		/*Logic for limiting the functionality to owner manager, CD and Admin*/
		if (
			array_key_exists('parent_id',$data) &&
			!\Models\SchedulePermission::hasOwnerPermission($data['parent_id'])
		) {
			return false;
		}
		$create_response = '';

		// If person who creates event is manager, link manager with that event!
		if (
			\APP\Auth::isCd() &&
			isset($data["managers"])
		) {
			// if $data["lesson_id"] == 'new', crete new lesson with assigned resources and pass created lesson id
			if (
				$data["type"] == 'lesson' &&
				isset($data["lesson_id"]) &&
				$data["lesson_id"] == 'new'
			) {
				$new_lesson = new \Models\LearningModule();
				$new_lesson->name = isset($data["name"]) ? $data["name"] : null;
				$new_lesson->is_course = 1;
				$new_lesson->status = 1;
				$new_lesson->hide_lesson=$data['disable_lesson'];
				
				$new_lesson->created_by = \APP\Auth::getUserId();
				if (isset($data["category_id"])) {
					$new_lesson->category_id = $data["category_id"];
				}
				$new_lesson->description = isset($data["description"]) ? $data["description"] : '';
				$new_lesson->save();

				// Attach resources if any
				if (isset($data["resources"])) {
					foreach ($data["resources"] as $key => $resource) {
						\Models\LearningCourseModule::firstOrCreate(
							[
								'learning_course_id' => $new_lesson->id,
								'learning_module_id' => $resource['id']
							]
						);
					}
				}

				$data["lesson_id"] = $new_lesson->id;
			}
			foreach ($data["managers"] as $key => $manager) {
				\Models\Schedule::createEvent($data, $manager);
			}
			$create_response = $data["lesson_id"];
		} else {
			$create_response = \Models\Schedule::createEvent($data);
		}


		return
			$response
				->write($create_response)
		;
	})->add(\APP\Auth::getStructureAccessCheck('lessons-and-learning-resources', 'insert'));


	// Update event!
	$this->put('/{event_id:[0-9]+}', function (Request $request, Response $response, array $args) {
		$data = $request->getParsedBody();

				/*Logic for limiting the functionality to owner manager, CD and Admin*/
				if(! \Models\SchedulePermission::hasOwnerPermission($args['event_id'])){
					return false;
				}

			if (isset($data["start_date"]) && isset($data["duration"])){
				$data["end_date"] = \Carbon\Carbon::parse($data["start_date"])->addMinutes($data["duration"]);
			}

			$event = \Models\Schedule::find($args['event_id']);
			$event->category_id = isset($data["category_id"]) ? $data["category_id"] : null;
			$event->name = isset($data["name"]) ? $data["name"] : null;
			$event->cost = isset($data["cost"]) ? $data["cost"] : null;
			$event->description = isset($data["description"]) ? $data["description"] : '';
			$event->location = isset($data["location"]) ? $data["location"] : '';
			$event->visit_type_id = isset($data["visit_type_id"]) ? $data["visit_type_id"] : null;
			$event->start_date = isset($data["start_date"]) ? \Carbon\Carbon::parse($data["start_date"]) : null;
			$event->duration = isset($data["duration"]) ? $data["duration"] : null;
			$event->end_date = isset($data["end_date"]) ? \Carbon\Carbon::parse($data["end_date"]) : null;
			$event->parent_id = isset($data["parent_id"]) ? $data["parent_id"] : null;
			$event->visible_learner = isset($data["visible_learner"]) ? $data["visible_learner"] : false;
			$event->visible_learner_task = isset($data["visible_learner_task"]) ? $data["visible_learner_task"] : false;
			$event->visible_schedule = isset($data["visible_schedule"]) ? $data["visible_schedule"] : true;
			$event->enrole_any_learner = isset($data["enrole_any_learner"]) ? $data["enrole_any_learner"] : false;
			$event->approval = isset($data["approval"]) ? $data["approval"] : false;
			$event->minclass = isset($data["minclass"]) ? $data["minclass"] : 0;
			$event->maxclass = isset($data["maxclass"]) ? $data["maxclass"] : 0;
			$event->lesson->hide_lesson=!$data['disable_lesson'];
			$event->lesson->save();

			if (!$event->parent_id) {
				$event->cron_task = true;
			}

		if ($event->outlook_integration && \APP\Tools::getConfig("enableGlobalOutlookIntegration")) {
			$event->outlook_refresh_token = "use_global";
		}

		$event->save();

			// IF extension fields are present loop them and update data accordingly.
			if (isset($data["extended"])) {
				foreach ($data["extended"] as $field_name => $value) {
					\Models\TableExtension::updateField('schedules', $event->id, $field_name, $value);
				}
			}


			// Update relevant lesson name also!
			if ($event->type == 'lesson') {
				$lesson = \Models\LearningModule
					::where('is_course', 1)
					->whereIn('learning_modules.id',
						\Models\ScheduleLink
							::select('link_id')
							->where('schedule_id', $event->id)
							->where('type', 'lesson')
							->get()
					)
					->first();

				if (
					$lesson &&
					$lesson->is_course
				) {
					//$lesson->name = $event->name;
					if ($event->category_id) {
						$lesson->category_id = $event->category_id;
					}
					$lesson->save();
				}
    			\Models\ScheduleLink::resetUsers($args['event_id']);
			}



			// Check and delete event if marked for deletion.
			if (
				isset($data["delete"]) &&
				$data["delete"]
			) {
				\Models\Schedule::deleteEvent($event->id);
			}

	})->add(\APP\Auth::getStructureAccessCheck('lessons-and-learning-resources', 'update'));

	// delete event child
	$this->delete('/{event_child_id:[0-9]+}', function (Request $request, Response $response, array $args) {
		/*Logic for limiting the functionality to owner manager, CD and Admin*/

		$event = \Models\Schedule::find($args['event_child_id']);

		if (
			$event &&
			$event->parent_id
		) {
			if(! \Models\SchedulePermission::hasOwnerPermission($event->parent_id)){
				return false;
			}
			\Models\Schedule::deleteEvent($event->id);
		}
	})->add(\APP\Auth::getStructureAccessCheck('lessons-and-learning-resources', 'update'));


	// Change link completion status
	$this->put('/update-completion-status/{id:[0-9]+}', function (Request $request, Response $response, array $args) {
		$data = $request->getParsedBody();

		if (
			isset($data['type'])
		) {
			switch ($data['type']) {
				case 'user':
					$link = \Models\ScheduleLink
						::where('id', $args['id'])
						->with('Schedule')
						->first();
					if (
						$link &&
						isset($data['completion_status'])
					) {
						if (
							$link->completion_status != 'Completed' &&
							$data['completion_status'] == 'Completed' &&
							$link->Schedule->type == 'meeting'
						) {
							\Models\User::updateLastContactDate($link->link_id, $link->Schedule->start_date);
						}
						$link->completion_status = $data['completion_status'];
						$link->authorisation_notes = isset($data['authorisation_notes']) ? $data['authorisation_notes'] : null;
						if ($data['completion_status'] == 'Not Attempted') {
							$link->is_authorised = $data['is_authorised'];
						} else {
							$link->is_authorised = null;
						}



						$link->save();
					}
					break;

				case 'users':
					if (isset($data['completion_status'])) {
						$links = \Models\ScheduleLink
							::where('schedule_id', $args['id'])
							->where('type', 'users')
							->with('Schedule')
							->where('status', true)
							->get();
						foreach ($links as $key => $link) {
							if (
								$link->completion_status != 'Completed' &&
								$data['completion_status'] == 'Completed' &&
								$link->Schedule->type == 'meeting'
							) {
								\Models\User::updateLastContactDate($link->link_id, $link->Schedule->start_date);
							}
							$link->completion_status = $data['completion_status'];
							if(isset($data['completed_at'])){
							  $link->completion_status = $data['completed_at'];
						    }
							$link->save();
						}
					}
				break;
			}
		}


	})->add(\APP\Auth::getStructureAccessCheck('lessons-and-learning-resources', 'update'));


	$this->post('/update-completion-date', function (Request $request, Response $response, array $args) {
		$data = $request->getParsedBody();
		//if (isset($data['completion_status']=="Completed")) {
			$link = \Models\ScheduleLink
				::where('id', $data['schedule_link_id'])
				->where('link_id', $data['schedule_link']['link_id'])
				->first();
			$link->completed_at=$data['completed_at'];
			$link->save();
				return
			$response
				->write($link)
				;
		//}


	})->add(\APP\Auth::getStructureAccessCheck('lessons-and-learning-resources', 'update'));


	// Change manager visitor status
	$this->put('/update-visitor-status/{id:[0-9]+}', function (Request $request, Response $response, array $args) {
		$data = $request->getParsedBody();

		$link = \Models\ScheduleLink
			::where('id', $args['id'])
			->where('type', 'managers')
			->with('Schedule')
			->first();

		if (
		$link
		) {
			$link->manager_visitor = !$link->manager_visitor;
			$link->save();
		}


	})->add(\APP\Auth::getStructureAccessCheck('lessons-and-learning-resources', 'update'));

	$this->post('/link/update', function (Request $request, Response $response, array $args) {
		$data = $request->getParsedBody();

		if (
			isset($data["id"])
		) {
			\Models\ScheduleLink::updateLink($data);
		}

		return
			$response//->write($event->id)
			;
	})->add(\APP\Auth::getStructureAccessCheck('lessons-and-learning-resources', 'update'));


	// Link schedule with whatever entries
	$this->post('/link/new', function (Request $request, Response $response, array $args) {
		$data = $request->getParsedBody();
		$user = \APP\Auth::getUser();

		if (
			empty($data["schedule_id"]) ||
			empty($data['type']) ||
			empty($data['link_id'])
		) {
			return \APP\Tools::returnCode($response, '404');
		}

		$event = \Models\Schedule::find($data["schedule_id"]);

		if (!$event) {
			return \APP\Tools::returnCode($response, '404');
		}

		$created_by = \Models\User::find($event->created_by);

		/*Logic for limiting the functionality to owner manager, CD and Admin*/
		$permission = \Models\SchedulePermission::hasOwnerPermission($data["schedule_id"]);
		$user_permisssion = \Models\SchedulePermission::hasUserPermission($data['link_id']);
		$user_type = in_array($data['type'], ['users_queue','users']);
		if (
			(
				!$permission &&
				!$user_type
			) ||
			(
				$user_type &&
				!$user_permisssion &&
				!\APP\Auth::isLearner()
			) ||
			(
				\APP\Auth::isLearner() &&
				$event->enrole_any_learner != 1 &&
				\APP\Auth::getUserId() != $data["link_id"] &&
				!$user_type
			)
		) {
			return \APP\Tools::returnCode($response, '403');
		}

		if (
			isset($data["schedule_id"]) &&
			$event &&
			isset($data["type"]) &&
			(
				(\APP\Auth::isAdminInterface() && isset($data["link_id"])) ||
				// Or learner who is enrolling
				(
					\APP\Auth::isLearner() &&
					$event->enrole_any_learner == 1 &&
					(
						$data["type"] == 'users' ||
						$data["type"] == 'users_queue'
					) &&
					$data["link_id"] == \APP\Auth::getUserId()
				)
			)
		) {
			if (
				$data["type"] == 'users' ||
				$data["type"] == 'users_queue'
			) {
				$schedule = \Models\Schedule
					::where('id', $data["schedule_id"])
					->with(['Schedules' => function ($query) {
						$query = $query
							->with(['Users' => function ($query) {
								$query = $query
									->select(
										'users.id'
									)
									->where('users.status', true)
								;
							}])
						;
					}])
					->with(['Users' => function ($query) {
						$query = $query
							->select(
								'users.id'
							)
							->where('users.status', true)
						;
					}])
					->first()
				;

				if (!\APP\Auth::isAdminInterface()) {
					$ids = \Models\Schedule::getChild($data["schedule_id"]);
					$schedules = \Models\Schedule
						::whereIn("id", $ids)->where(function($query){
							$query
								->whereNull('cost')
								->orWhere('cost','=',0)
							;
						})
						->where('enrole_any_learner',1)
						->get()
					;
				} else {
					$ids = \Models\Schedule::getChild($data["schedule_id"]);
					$schedules = \Models\Schedule
						::whereIn("id", $ids)
						->with(['Users' => function ($query) {
							$query = $query
								->select(
									'users.id'
								)
								->where('users.status', true)
							;
						}])
						->get()
					;
				}

				$waitingList = 0;

				foreach ($schedules as $key => $linked_schedule) {
					if ($linked_schedule->id != $schedule->id) {
						$insert = $data;
						$insert["schedule_id"] = $linked_schedule->id;

						if (isset($linked_schedule->users)) {
							if ($linked_schedule->maxclass > 0) {
								if ($linked_schedule->maxclass <= count($linked_schedule->users)) {
									$insert["type"] = 'users_queue';
									$waitingList = 1;
								}
							}
						}
						$insert["approval"] = $linked_schedule->approval;
						\Models\ScheduleLink::addNewLink($insert);
					}
				}

				if (isset($schedule->users)) {
					if ($schedule->maxclass > 0) {
						if ($schedule->maxclass <= count($schedule->users)) {
							$data["type"] = 'users_queue';
						}
					}
				}
			}

			// if events are linked, reverse link as well
			if ($data["type"] == 'schedules') {
				\Models\ScheduleLink::addNewLink([
					'schedule_id' => $data["link_id"],
					'link_id' => $data["schedule_id"],
					'type' => $data["type"]
				]);
			}

			if (\APP\Auth::isLearner()) {
				$data['approval'] = $schedule->approval;
			}

			\Models\ScheduleLink::addNewLink($data);

			// If learner enrolls, process his event assignment!
			if (
					\APP\Auth:: isLearner() &&
					$event->enrole_any_learner == 1 &&
					(
						$data["type"] == 'users' ||
						$data["type"] == 'users_queue'
					) &&
					$data["link_id"] == \APP\Auth::getUserId()
			) {
				// TODO, processEvents for just this event, for just this user!
				\Models\Schedule::processEvents(false, $data['schedule_id'], $data["link_id"]);
			}

			if (
				$event->approval == "1" &&
				$data["type"] == 'users' &&
				\APP\Auth:: isLearner()
			) {
				// Send e-mail to Manager that status changes to approval
				$template = \Models\EmailTemplate::getTemplate('Event Approval Request');

				$managerList=\Models\ManagerUser::where('user_id', '=', \APP\Auth::getUserId())
				->select('manager_id','users.fname','users.lname')
				->join("users", function($join) {
					$join
						->on("manager_id", "users.id")
					;
				})
				->get();
				if ($template) {
				  foreach($managerList AS $managerListVal){
					$email_queue = new \Models\EmailQueue;
					$email_queue->email_template_id = $template->id;
					$email_queue->recipients = [$managerListVal->manager_id];
					$email_queue->from = \APP\Auth::getUserId();
					$email_queue->custom_variables = json_encode([
						'USER_FNAME' => $managerListVal->fname,
						'USER_LNAME' => $managerListVal->lname,
						'TRAINEE_FNAME' => $user->fname,
						'TRAINEE_LNAME'=> $user->lname,
						'EVENT_NAME' => $event->name,
						'EVENT_LOCATION' => $event->location,
						'EVENT_DATE' => $event->start_date,
						'CONFIG_LMSUrl' => $GLOBALS["CONFIG"]->LMSUrl,
						'MANAGER_APPROVAL_LINK' => 'app/dashboard/manage-learning/manage-learning-resources/lessons-and-learning-resources',
						'REGARDS' => $GLOBALS["CONFIG"]->Regards,
					]);
					$email_queue->save();
					}
				}
			}
		}

		if (!\APP\Auth::isAdminInterface()) {
			if ($data['approval'] == 1) {
				return $response->write('not_approved');
			} else if ($waitingList == 1) {
				return $response->write($waitingList);
			} else {
				return $response->write($data["type"]);
			}
		} else {
			if ($waitingList) {
				return $response
					->write($waitingList)
				;
			} else {
				return $response->write($data["type"]);
			}
		}
	})->add(\APP\Auth::getSessionCheck());

	$this->post('/link/getlink', function (Request $request, Response $response, array $args) {
		$data = $request->getParsedBody();

		if (
			isset($data["schedule_id"]) &&
			isset($data["type"]) &&
			isset($data["link_id"])
		) {
			$link = \Models\ScheduleLink
				::where('schedule_id', $data["schedule_id"])
				->where('type', $data["type"])
				->where('link_id', $data["link_id"])
				->first();

				return
			$response
				->write($link);

		}

		return
			$response
		;
	})->add(\APP\Auth::getStructureAccessCheck('lessons-and-learning-resources', 'select'));


	$this->post('/link/delete', function (Request $request, Response $response, array $args) {
		$data = $request->getParsedBody();
		if (
			empty($data["schedule_id"]) ||
			empty($data["type"]) ||
			empty($data["link_id"])
		) {
			return \APP\Tools::returnCode($response, '404');
		}
		$event = \Models\Schedule::find($data["schedule_id"]);
		if (!$event) {
			return \APP\Tools::returnCode($response, '404');
		}

		// If learner and he is not the one canceling his assignment, fail
		$permission = \Models\SchedulePermission::hasOwnerPermission($data["schedule_id"]);
		$user_permisssion = \Models\SchedulePermission::hasUserPermission($data['link_id']);
		$user_type = in_array($data['type'],['users_queue','users']);
		if (
			(
				!$permission &&
				!$user_type
			) ||
			(
				$user_type &&
				!$user_permisssion &&
				!\APP\Auth::isLearner()
			) ||
			(
				\APP\Auth::isLearner() &&
				$event->enrole_any_learner != 1 &&
				\APP\Auth::getUserId() != $data["link_id"] &&
				!$user_type
			)
		) {
			return \APP\Tools::returnCode($response, '403');
		}

		if ($data["type"] == 'linkevents') {
			$link_event = \Models\LinkedEvent
				::where('event_id', $data["schedule_id"])
				->where('link_id', $data["link_id"])
				->forceDelete()
			;
			$link_event = \Models\LinkedEvent
				::where('event_id',$data["link_id"])
				->where('link_id',$data["schedule_id"])
				->forceDelete()
			;
		} else {

			$link = \Models\ScheduleLink
				::where('schedule_id', $data["schedule_id"])
				->where(function ($query) use ($data) {
					$query
						->where('type', $data["type"])
					;
					// Workaround for issue where user wants to cancel event and he is as users_queue, permanent temporaty solution
					if ($data["type"] == 'users') {
						$query = $query
							->orWhere('type', 'users_queue')
						;
					}
				})
				->where('link_id', $data["link_id"])
				->first()
			;
			// Fail all ordeal if link is not found.
			if (!$link) {
				return \APP\Tools::returnCode($response, '404');
			}

			if (
				in_array($data['type'],['users','users_queue']) &&
				array_key_exists("remove_child", $data) &&
				$data['remove_child']
			) {
				$schedules = \Models\Schedule
					::getChild($data['schedule_id'])
				;
				\Models\ScheduleLink
					::whereIn('schedule_id', $schedules)
					->whereIn('type', ['users','users_queue'])
					->where('link_id', $data["link_id"])
					->update(
						[
							'status' => false,
							'deleted_by' => \APP\Auth::getUserId(),
							'cron_task' => true,
						]
					)
				;
				foreach($schedules as $id) {
					\Models\ScheduleLink::resetUsers($id);
					\Models\Schedule::setForCron($id);
				}

				return
					$response
				;
			}

			$link->status = false;
			$link->deleted_by = \APP\Auth::getUserId();
			$link->cron_task = true;
			$link->cancellation_reason=isset($data["cancellation_reason"])?$data["cancellation_reason"]:'';
			$link->save();

			if($data['type']=='users') {
				//Fetch managers id in schedule event
				$managers=\Models\ScheduleLink
				::where('schedule_id', $data["schedule_id"])
				->where('type','managers')->get();
				
				if(!empty($managers)){
					$userDetails=\Models\User::find(\APP\Auth::getUserId());
					foreach($managers AS $manager){
						$managerDetails=\Models\User::find($manager["link_id"]);
						$template = \Models\EmailTemplate
						::where('name', 'Event Cancellation')
						->where('status', true)
						->first()
						;
						if ($template && $template->id) {
							$email_queue = new \Models\EmailQueue;
							$email_queue->email_template_id = $template->id;
							$email_queue->recipients = [$manager["link_id"]];
							$email_queue->from = \APP\Auth::getUserId();
							$email_queue->custom_variables = json_encode([
								'USER' => $managerDetails->fname,
								'LEARNERNAME'=>$userDetails->fname,
								'EVENT_NAME' => $event->name,
								'EVENT_DATE' => $event->start_date,
								'CANCELLATION_REASON'=>$data["cancellation_reason"],
								'REGARDS' => $GLOBALS["CONFIG"]->Regards,
							]);
							$email_queue->save();
						}	
					}
				}
			}

		

			$created_by = \Models\User::find($link->link_id);

			\Models\ScheduleLink::resetUsers($data["schedule_id"]);
			\Models\Schedule::setForCron($data["schedule_id"]);

			// Detach resources from event lesson
			if ($data["type"] == 'resources') {
				$lesson = \Models\LearningModule
					::where('is_course', 1)
					->whereIn('learning_modules.id',
						\Models\ScheduleLink
							::select('link_id')
							->where('schedule_id', $data["schedule_id"])
							->where('type', 'lesson')
							->get()
					)
					->first()
				;

				// if lesson has been created by same person who created event/schedule!
				// And user is not CD
				if (
					!\APP\Auth::isCd() &&
					$lesson &&
					$lesson->created_by == $event->created_by
				) {
					$lesson->modules()->detach([$data["link_id"]]);
				}
			}

			// If departments or groups are detached, detach users in those collections
			if (
				$data["type"] == 'departments' ||
				$data["type"] == 'groups'
			) {
				if ($data["type"] == 'departments') {
					$user_collection = \Models\Department
						::where('id', $data["link_id"]);
				}
				if ($data["type"] == 'groups') {
					$user_collection = \Models\Group
						::where('id', $data["link_id"]);
				}
				$user_collection = $user_collection
					->with(['Users' => function ($query) {
						$query = $query
							->where('users.status', true);
					}])
					->first()
				;
				if (
					$user_collection &&
					$user_collection->users
				) {
					foreach ($user_collection->users as $key => $user) {
						$user_link = \Models\ScheduleLink::firstOrNew(
							[
								"schedule_id" => $data["schedule_id"],
								"type" => 'users',
								"link_id" => $user->id,
							]
						);
						$user_link->status = false;
						$user_link->cron_task = true;
						$user_link->save();
					}
				}
			}

			if (
				(
					$data["type"] == 'users' ||
					$data["type"] == 'users_queue'
				) &&
				$link->approved == 0
			) {
				// Send e-mail to Manager that status changes to approval
				$template = \Models\EmailTemplate
					::where('name', 'Event Not Approved')
					->where('status', true)
					->first()
				;
				if ($template && $template->id) {
					$email_queue = new \Models\EmailQueue;
					$email_queue->email_template_id = $template->id;
					$email_queue->recipients = [$created_by->id];
					$email_queue->from = \APP\Auth::getUserId();
					$email_queue->custom_variables = json_encode([
						'USER_FNAME' => $created_by->fname,
						'USER_LNAME' => $created_by->lname,
						'EVENT_NAME' => $event->name,
						'EVENT_LOCATION' => $event->location,
						'EVENT_DATE' => $event->start_date,
						'REGARDS' => $GLOBALS["CONFIG"]->Regards,
					]);
					$email_queue->save();
				}
			}
		}

		return
			$response
		;
	})->add(\APP\Auth::getSessionCheck());

	// Remove all non zoom/teams resources and reset learner status.
	$this->put('/new-topic/{schedule_id:[0-9]+}', function (Request $request, Response $response, array $args) {
		$data = $request->getParsedBody();
		$schedule = \Models\Schedule::find($args['schedule_id']);

		if (isset($data['name'])) {
			$schedule->name = $data['name'];
		}
		$schedule->cron_task = true;
		$schedule->save();

		/*
		$old_lesson = \Models\LearningModule
			::where('is_course', 1)
			->whereIn('id',
				\Models\ScheduleLink
					::select('link_id')
					->where('type', 'lesson')
					->where('schedule_id', $schedule->id)
					->where('status', true)
					->get()
			)
			->first()
		;
		*/

		// Create new lesson!
		$new_lesson = new \Models\LearningModule;
		$new_lesson->is_course = 1;
		$new_lesson->name = $schedule->name;
		$new_lesson->status = true;
		$new_lesson->save();

		// Replace old lesson in link with new one.
		\Models\ScheduleLink
			::where('schedule_id', $schedule->id)
			->where('status', true)
			->where('type', 'lesson')
			->update(['link_id' => $new_lesson->id]);

		$schedule_links = \Models\ScheduleLink
			::where('schedule_id', $schedule->id)
			->where('status', true)
			->get();

		foreach ($schedule_links as $key => $schedule_link) {
			if ($schedule_link->type == 'resources') {
				// Check if resource is zoom or teams, if not, remove!
				$resource = \Models\LearningModule::find($schedule_link->link_id);

				if (
					!$resource->type->slug == 'zoom_meeting' &&
					!$resource->type->slug == 'microsoft_teams'
				) {
					$schedule_link->status = false;
					$schedule_link->cron_task = true;
				} else {
					// attach these resources to new lesson!
					\Models\LearningCourseModule::firstOrCreate(
						[
							'learning_course_id' => $new_lesson->id,
							'learning_module_id' => $schedule_link->link_id
						]
					);
				}
			} else {
				$schedule_link->completion_status = 'Not Attempted';
			}
			$schedule_link->save();
		}


		return
			$response
				->write($schedule->id);
	})->add(\APP\Auth::getStructureAccessCheck('lessons-and-learning-resources', 'insert'));


	$this->put('/set-homework/{resource_id:[0-9]+}', function (Request $request, Response $response, array $args) {
		$data = $request->getParsedBody();

		$link = \Models\ScheduleLink
			::where('link_id', $args['resource_id'])
			->where('schedule_id', $data['schedule_id'])
			->where('status', true)
			->where('type', 'resources')
			->first();
		if (isset($data['completion_date_custom'])) {
			$link->completion_date_custom = \Carbon\Carbon::parse($data['completion_date_custom']);
		} else {
			$link->completion_date_custom = null;
		}

		$link->cron_task = true;
		$link->save();

		\Models\Schedule::setForCron($data['schedule_id']);

		return
			$response
			//->withHeader('Content-Type', 'application/json')
			//->write($result->homework)
			;
	})->add(\APP\Auth::getStructureAccessCheck(['lessons-and-learning-resources'], 'update'));


	// Resource can not be played by learner the normal way, it will sync with managers progress.
	$this->put('/set-instructor-lead/{resource_id:[0-9]+}', function (Request $request, Response $response, array $args) {
		$data = $request->getParsedBody();

		$link = \Models\ScheduleLink
			::where('link_id', $args['resource_id'])
			->where('schedule_id', $data['schedule_id'])
			->where('status', true)
			->where('type', 'resources')
			->first()
		;
		$link->instructor_lead = !$link->instructor_lead;
		$link->cron_task = true;
		$link->save();

		\Models\Schedule::setForCron($data['schedule_id']);

		return
			$response
				//->withHeader('Content-Type', 'application/json')
				//->write($result->homework)
		;
	})->add(\APP\Auth::getStructureAccessCheck(['lessons-and-learning-resources'], 'update'));

	$this->get('/visit-types/all', function (Request $request, Response $response) {
		$query = \Models\ScheduleVisitType
			::where("status", true)
		;

		if (\APP\Tools::getConfig("IncludeCredasForms")) {
			$query = $query
				->with('CredasProcess.Actors')
			;
		}

		$query = $query
			->get()
		;

		return
			$response
				->withHeader('Content-Type', 'application/json')
				->write(json_encode($query));
	})->add(\APP\Auth::getSessionCheck());


	// Update issue "VisibilityStatus from.
	$this->POST('/change_visibility/{id:[0-9]+}', function (Request $request, Response $response, array $args) {
		$data = $request->getParsedBody();
		$event = \Models\Schedule
			::findOrFail($args['id']);
		if ($data['type'] == "visible_learner_task") {
			$event->visible_learner_task = $data['val'];
		} else if ($data['type'] == "visible_learner") {
			$event->visible_learner = $data['val'];
		} else if ($data['type'] == "visible_schedule") {
			$event->visible_schedule = $data['val'];
		}
		$event->save();

		return
			$response;
	})->add(\APP\Auth::getStructureAccessCheck('standards-and-other-programmes', 'update'));

	// This will get data from learning_results, assessment tables and scorm_data table for logged in user and update all users assigned to this schedule/event/resource with same progress state.
	$this->get('/sync-resource/{schedule_id:[0-9]+}/{resource_id:[0-9]+}', function (Request $request, Response $response, $args) {
		$event = \Models\Schedule
			::with('users')
			->find($args['schedule_id'])
		;

		// Loop all users in schedule/event, create array
		$user_ids = [];
		foreach ($event->users as $key => $user) {
			$user_ids[] = $user->id;
		}


		// Get related assessment Data
		$assesment_data = \Models\Assessment\Data
			::where('user_id', \APP\Auth::getUserId())
			->where('course_id', $args['resource_id'])
			->get()
		;

		if ($assesment_data) {
			foreach ($assesment_data as $key => $assesment) {
				// Look into assessment_tasks, if there is anything that needs to be synced.
				$assessment_tasks = \Models\Assessment\Task
					::where('user_id', \APP\Auth::getUserId())
					->where('course_id', $args['resource_id'])
					->where('assessment_data_id', $assesment->id)
					->get()
				;

				// Create new assessment data for users
				foreach ($user_ids as $key => $user_id) {

					// Delete assessment_tasks for user, if unfinished!
					\Models\Assessment\Task
						::where('user_id', $user_id)
						->where('course_id', $args['resource_id'])
						->whereIn('assessment_data_id',
							\Models\Assessment\Data
								::select('id')
								->where('user_id', $user_id)
								->where('course_id', $args['resource_id'])
								->where('status', '!=', 4)
								->get()
						)
						->delete()
					;
					// Delete assessment_data for all users
					\Models\Assessment\Data
						::where('user_id', $user_id)
						->where('course_id', $args['resource_id'])
						->where('status', '!=', 4)
						->delete()
					;

					// how to work out duplicates?
					$new_assesment = new \Models\Assessment\Data;
					$new_assesment->user_id = $user_id;
					$new_assesment->course_id = $args['resource_id'];
					$new_assesment->status = true;
					$new_assesment->save();

					foreach ($assessment_tasks as $key => $assessment_task) {
						$new_assesment_task = new \Models\Assessment\Task;
						$new_assesment_task->assessment_data_id = $new_assesment->id;
						$new_assesment_task->user_id = $user_id;
						$new_assesment_task->course_id = $args['resource_id'];
						$new_assesment_task->reporter_id = $assessment_task->reporter_id;
						$new_assesment_task->question_id = $assessment_task->question_id;
						$new_assesment_task->answer_id = $assessment_task->answer_id;
						$new_assesment_task->user_comment = $assessment_task->user_comment;
						$new_assesment_task->weighting = $assessment_task->weighting;
						$new_assesment_task->status = true;
						$new_assesment_task->submitted_at = $assessment_task->submitted_at;
						$new_assesment_task->ignore = true;
						$new_assesment_task->save();
					}
				}
			}
		}
		// EOF assessment Data sync


		// Get managers learning_result
		$learning_result = \Models\LearningResult
			::where('learning_module_id', $args['resource_id'])
			->where('user_id', \APP\Auth::getUserId())
			->where('refreshed', 0)
			->with('Module')
			->first()
		;
		// Mass update learning result for all users assigned to this event/resource
		\Models\LearningResult
			::where('learning_module_id', $args['resource_id'])
			//->where('user_id', $user->id)
			->whereIn('user_id', $user_ids)
			->where('refreshed', 0)
			->where('completion_status', '!=', 'completed')
			->with('Module')
			->update([
				'completion_status' => $learning_result->completion_status,
				'passing_status' => $learning_result->passing_status,
				'grade' => $learning_result->grade,
				'score' => $learning_result->score,
				'duration_hours' => $learning_result->duration_hours,
				'duration_minutes' => $learning_result->duration_minutes,
				'duration_scorm' => $learning_result->duration_scorm,
			])
		;
		// EOF Mass update learning result

		// Mass update SCORM \Models\Scorm\Track
		/// Get managers latest attempt data from scorm_scorm_scoes_track table
		$max_attempt_no = \Models\Scorm\Track
			::where("userid", \APP\Auth::getUserId())
			->where("scormid", $args['resource_id'])
			->max("attempt")
		;
		$max_attempt_no = $max_attempt_no > 0 ? $max_attempt_no : 1;
		// Get all data in scorm tracking table to sync it with all users!
		$scorm_scorm_scoes_track = \Models\Scorm\Track
			::where("userid", \APP\Auth::getUserId())
			->where("scormid", $args['resource_id'])
			->where("attempt", $max_attempt_no)
			->get()
		;

		// Replace SCROM TRACK data with manager data
		foreach ($user_ids as $key => $user_id) {
			// Find max attempt for user
			$user_max_attempt_no = \Models\Scorm\Track
				::selectRaw("IFNULL(MAX(attempt), 0) + 1 AS maxAttempt")
				->where("userid", \APP\Auth::getUserId())
				->where("scormid", $args['resource_id'])
				->where("element", "LIKE", "%-r")
				->first()
			;
			// Delete data from \Models\Scorm\Track
			\Models\Scorm\Track
				::where("userid", $user_id)
				->where("scormid", $args['resource_id'])
				->where("attempt", $user_max_attempt_no->maxAttempt)
				->delete()
			;
			// Add manager data!
			foreach ($scorm_scorm_scoes_track as $key => $scoes_track) {
				$new_scorm_track = new \Models\Scorm\Track;
				$new_scorm_track->userid = $user_id;
				$new_scorm_track->scormid = $args['resource_id'];
				$new_scorm_track->attempt = $user_max_attempt_no->maxAttempt;
				$new_scorm_track->scoid = $scoes_track->scoid;
				$new_scorm_track->element = $scoes_track->element;
				$new_scorm_track->value = $scoes_track->value;
				$new_scorm_track->timemodified = $scoes_track->timemodified;
				$new_scorm_track->save();
			}
		}
		// EOF Mass update SCORM


		return
			$response
				//->withHeader('Content-Type', 'application/json')
				//->write(json_encode($query))
		;
	})->add(\APP\Auth::getStructureAccessCheck('lessons-and-learning-resources', 'update'));

	$this->post('/deleted/list{download:[\/a-z]*}', function (Request $request, Response $response, array $args) {

		$params = $request->getParsedBody();
		$query = \Models\Schedule
			::onlyTrashed()
			->select(
				'schedules.*',
				DB::raw("DATE_FORMAT(schedules.created_at,'%d/%m/%Y') AS created_at_uk"),
				DB::raw("DATE_FORMAT(schedules.start_date,'%d/%m/%Y') AS start_date_uk"),
				DB::raw("DATE_FORMAT(schedules.deleted_at,'%d/%m/%Y') AS deleted_at_uk")
			)
			->whereNull('parent_id')
			->leftJoin("learning_module_categories", function ($join) {
				$join
					->on("learning_module_categories.id", "schedules.category_id")
				;
			})

			->leftJoin("users as deleted_by_user", function ($join) {
				$join
					->on("deleted_by_user.id", "schedules.deleted_by")
				;
			})

			->leftJoin("users as created_by_user", function ($join) {
				$join
					->on("created_by_user.id", "schedules.created_by")
				;
			})

			->with(['DeletedBy' => function ($query) {
				$query
					->select(
						'id',
						'fname',
						'lname',
						'role_id'
					)
					->with('role')
				;
			}])
			->with(['CreatedBy' => function ($query) {
				$query
					->select(
						'id',
						'fname',
						'lname',
						'role_id'
					)
					->with('role')
				;
			}])
			->with('Category')
		;


		if (isset($params["search"]["refresh"])) {
			unset($params["search"]["refresh"]);
		}


		$p = \APP\SmartTable::searchPaginate($params, $query);

		return
			$response
				->withHeader('Content-Type', 'application/json')
				->write($p->toJson())
		;
	})->add(\APP\Auth::getStructureAccessCheck('system-setup-deleted-events', 'select'));

	// Add event to user's Outlook
	$this->post('/{user_id: [0-9]+}/outlook', function (Request $request, Response $response, array $args) {
		$user_id = $args["user_id"];
		$data = $request->getParsedBody();


		if (
			isset($data["event_id"]) &&
			isset($data["event_type"]) &&
			isset($data["start_date"]) &&
			isset($data["duration"]) &&
			\APP\Tools::getConfig("enableGlobalOutlookIntegration")
		) {
			$user = \Models\User::find($user_id);

			$teams = new \APP\Teams(false);
			$title = isset($data["title"]) && !empty($data["title"]) ? $this->settings["LMSName"] . ": ". $data["title"] : $this->settings["LMSName"] . " event";
			$teams->refresh_global_calendar_oauth_token(\APP\Tools::getConfig("GlobalOutlookIntegrationSecurityToken"));

			$link = isset($data["link"]) && !empty($data["link"]) ? $this->settings["LMSUrl"] . $this->settings["LMSAppUri"] . $data["link"] : false;
			$description = $data["description"] ?? "";

			$teams->createUserEvent($user, $title, $description, $data["start_date"], $data["duration"], $link, $data["event_id"], $data["event_type"]);
		};

		return
			$response
			;
	})->add(\APP\Auth::getStructureAccessCheck(['lessons-and-learning-resources', 'trainee-learning-results'], 'select'));


	/*Import Legacy Course Data*/
	$this->post('/import-event', function (Request $request, Response $response) {
		$params = $request->getParsedBody();
		if (isset($_FILES['importFile'])) {

			//upload file
			$storage = new \Upload\Storage\FileSystem($this->settings["LMSTempPath"]);
			$import_file = new \Upload\File('importFile', $storage);
			$import_file_name = $import_file->getNameWithExtension();
			$import_file_id = uniqid();
			$import_file->setName($import_file_id);
			$notify_roles = [];
			try {
				$import_file->upload();
			} catch (\Exception $e) {
				$errors = $import_file->getErrors();
				return
					$response
						->withStatus(500)
						->withHeader('Content-Type', 'text/html')
						->write(implode("\n", $errors))
					;
			}

			// Log import files!
			\Models\LogExportImport::insertRecord(file_get_contents($this->settings["LMSTempPath"] . $import_file->getNameWithExtension()), '.' . $import_file->getExtension(), $import_file_name, false, 'imports');
			/*
			if (
				isset($params['only_completed']) &&
				$params['only_completed']
			) {
			*/
				// Will import only completed, will not calculate anything, just iomport missing completed entries!
				$n_records = \APP\Import::userEventData($this->settings["LMSTempPath"] . $import_file->getNameWithExtension());
			/*
			} else {
				$n_records = \DB\ImportExcel::importCourse($this->settings, $this->settings["LMSTempPath"] . $import_file->getNameWithExtension(), $params, $response, $notify_roles);
			}
			*/

			return $response
				->write(
					json_encode(
						[
							'updated' => $n_records['n_record_updated'],
							'inserted' => $n_records['n_record_inserted'],
							'disabled' => $n_records['n_record_disabled'],
							'rejected' => $n_records['n_record_rejected'],
							'deleted' => $n_records['n_record_deleted'],
							'message' => $n_records['message'],
							'log' => $n_records['log']
						]
					)
				)
			;
		};

        })->add(\APP\Auth::getStructureAccessCheck(['system-setup-organisation-users'], 'insert'));
    $this->get('/import-template', function (Request $request, Response $response) {
            $excel = new \PHPExcel();
            $excel->getProperties()->setCreator("");
            $excel->getProperties()->setLastModifiedBy("");
            $excel->getProperties()->setTitle("");
            $excel->getProperties()->setSubject("");
            $excel->getProperties()->setDescription("");
            $excel->setActiveSheetIndex(0);
            $template_sheet = $excel->getActiveSheet();
            $template_sheet->setTitle("Template");
            $template_sheet->SetCellValue('A1', 'Username');
            $template_sheet->SetCellValue('B1', 'MappedEventID');
            $template_sheet->SetCellValue('C1', 'Status (Not Started, In Progress, Completed)');
            $template_sheet->SetCellValue('D1', 'isWaiting List (Yes or No)');
            $template_sheet->SetCellValue('E1', 'isReceive Email Notfiicatoins (Yes or No)');
            $template_sheet->SetCellValue('F1', 'isAuthorised Absence');
            $template_sheet->SetCellValue('G1', 'Absence Notes');
            $excel->createSheet(1);
            $excel->setActiveSheetIndex(1);
            $mapData=$excel->getActiveSheet();
            $mapData->setTitle('LookupData');
            $events = \Models\Schedule::select('id','name')->orderBy('created_at')->get();
            $events->prepend(['ID','Name']);
            $mapData->fromArray($events->toArray());
            $excel->setActiveSheetIndex(0);
            $response = $response->withHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            $response = $response->withHeader('Content-Disposition', 'attachment; filename="user_event_template.xlsx"');
            $objWriter = \PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
            $file_donwload=$this->settings['LMSTempPath'].'user_event_template.xlsx';
            $objWriter->save($file_donwload);
            readfile($file_donwload);
            return $response;


    })->add(\App\Auth::getStructureAccessCheck(['system-setup-organisation-users'], 'insert'));
});
