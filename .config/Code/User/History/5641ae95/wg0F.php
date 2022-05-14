<?php

namespace Models;



class Schedule extends \Illuminate\Database\Eloquent\Model {

	use \Staudenmeir\EloquentHasManyDeep\HasRelationships;
	use \Illuminate\Database\Eloquent\SoftDeletes;

	protected $casts = [
		'cron_task' => 'boolean',
		'status' => 'boolean',
		'reminder_sent' => 'boolean',
		'visible_learner' => 'boolean',
		'visible_learner_task' => 'boolean',
		'visible_schedule' => 'boolean',
		'outlook_event_response' => 'array',
	];

	public function DeletedBy() {
		return $this->belongsTo('Models\User', 'deleted_by', 'id');
	}

	public function CreatedBy() {
		return $this->belongsTo('Models\User', 'created_by', 'id');
	}

	public function Permissions() {
		return $this->hasMany('Models\SchedulePermission', 'schedule_id', 'id');
	}
	public function Children() {
		return $this->hasMany('Models\Schedule', 'parent_id', 'id');
	}

	public function VisitType() {
		return $this->belongsTo('Models\ScheduleVisitType');
	}

	public function Category() {
		return $this->belongsTo('Models\LearningModuleCategory', 'category_id', 'id');
	}

	public function Owner() {
		return $this
			->belongsTo('Models\SchedulePermission', 'id', 'schedule_id')
			->where('type', 'owner')
		;
	}

	public function Files() {
		return $this
			->hasMany('Models\File', 'table_row_id', 'id')
			->where('table_name', 'schedules')
		;
	}

	public function Comments() {
		return $this
			->hasMany('Models\Comment', 'table_row_id', 'id')
			->where('table_name', 'schedules')
		;
	}

	public function ScheduleLink() {
		return $this
			->hasOne('Models\ScheduleLink', 'link_id', 'id')
			->where('type', 'schedules')
		;
	}

	/*
	public function Resources() {
		return $this
			->hasManyThrough(
				'Models\LearningModule',
				'Models\ScheduleLink',
				'schedule_id',
				'id',
				'id',
				'link_id'
			)
			->where('schedule_links.type', 'resources')
			->where('schedule_links.status', true)
		;
	}
	*/
	// Test bed for https://github.com/staudenmeir/eloquent-has-many-deep
	public function Resources() {
		return $this
			->hasManyDeep(
				'Models\LearningModule',
				['Models\ScheduleLink'],
				[
					'schedule_id',
					'id',
				],
				[
					'id',
					'link_id',
				]
			)
			->where('schedule_links.type', 'resources')
			->where('schedule_links.status', true)
			->withIntermediate(
				'Models\ScheduleLink'
			)
		;
	}

	// Linked events!
	public function Schedules() {
		return $this
			->hasManyDeep(
				'Models\Schedule',
				['Models\ScheduleLink'],
				[
					'schedule_id',
					'id',
				],
				[
					'id',
					'link_id',
				]
			)
			->where('schedule_links.type', 'schedules')
			->where('schedule_links.status', true)
			->withIntermediate(
				'Models\ScheduleLink'
			)
		;
	}
	public function sheduleChild() {
		return $this
			->hasManyDeep(
				'Models\Schedule',
				['Models\ScheduleLink'],
				[
					'link_id',
					'id',
				],
				[
					'id',
					'schedule_id',
				]
			)
			->where('schedule_links.type', 'schedules')
			->where('schedule_links.status', true)
			->withIntermediate(
				'Models\ScheduleLink'
			)
		;
	}
  public static function getChild($schedule_id,$schedules=[]){
	$schedule = \Models\Schedule
	::where('id', $schedule_id)
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
	->first();
	if( $schedule &&!empty($schedule->schedules->toArray()))
	{
		foreach($schedule->schedules as $child_schedule)
		{
			if(!in_array($child_schedule->id,$schedules))
			{
				$schedules[]=$child_schedule->id;
				$schedules=self::getChild($child_schedule->id,$schedules);
			}
		}
	}
	return $schedules;
  }

	// BROKEN, NEED LARAVEL 5.7! For that need PHP 7.1+
	public function Lesson() {
		return $this
			->hasOneThrough(
				'Models\LearningModule',
				'Models\ScheduleLink',
				'schedule_id',
				'id',
				'id',
				'link_id'
			)
			->where('schedule_links.type', 'lesson')
			->where('schedule_links.status', true)
		;
	}

	public function Lessons() {
		return $this
			->hasManyDeep(
				'Models\LearningModule',
				['Models\ScheduleLink'],
				[
					'schedule_id',
					'id',
				],
				[
					'id',
					'link_id',
				]
			)
			->where('schedule_links.type', 'lesson')
			->where('schedule_links.status', true)
		;
	}

	public function Standards() {
		return $this
			->hasManyDeep(
				'Models\ApprenticeshipStandard',
				['Models\ScheduleLink'],
				[
					'schedule_id',
					'id',
				],
				[
					'id',
					'link_id',
				]
			)
			->where('schedule_links.type', 'programmes')
			->where('schedule_links.status', true)
		;
	}




	// Identical to Standards, just need that consistent naming in events!
	public function Programmes() {
		return $this
			->Standards()
		;
	}

	public function Users() {
		return $this
			->hasManyDeep(
				'Models\User',
				['Models\ScheduleLink'],
				[
					'schedule_id',
					'id',
				],
				[
					'id',
					'link_id',
				]
			)
			->where('schedule_links.type', 'users')
			->where('schedule_links.status', true)
		;
	}

	public function Waiting() {
		return $this
			->hasManyDeep(
				'Models\User',
				['Models\ScheduleLink'],
				[
					'schedule_id',
					'id',
				],
				[
					'id',
					'link_id',
				]
			)
			->where('schedule_links.type', 'users_queue')
			->where('schedule_links.status', true)
			->orderBy('schedule_links.updated_at', 'ASC')
		;
	}

	public function WaitingForApproval() {
		return $this
			->hasManyDeep(
				'Models\User',
				['Models\ScheduleLink'],
				[
					'schedule_id',
					'id',
				],
				[
					'id',
					'link_id',
				]
			)
			->where('schedule_links.approved', 0)
			->where(function ($query) {
				$query = $query
					->where('schedule_links.type', 'users_queue')
					->orWhere('schedule_links.type', 'users')
				;
			})
			->where('schedule_links.status', true)
			->orderBy('schedule_links.updated_at', 'ASC')
		;
	}


	public function Managers() {
		return $this
			->hasManyDeep(
				'Models\User',
				['Models\ScheduleLink'],
				[
					'schedule_id',
					'id',
				],
				[
					'id',
					'link_id',
				]
			)
			->where('schedule_links.type', 'managers')
			->where('schedule_links.status', true)
		;
	}

	public function Visitors() {
		return $this
			->hasManyThrough(
				'Models\User',
				'Models\ScheduleLink',
				'schedule_id',
				'id',
				'id',
				'link_id'
			)
			->where('schedule_links.type', 'managers')
			->where('schedule_links.status', true)
			//->where('schedule_links.manager_visitor', true)
		;
	}

	public function Departments() {
		return $this
			->hasManyThrough(
				'Models\Department',
				'Models\ScheduleLink',
				'schedule_id',
				'id',
				'id',
				'link_id'
			)
			->where('schedule_links.type', 'departments')
			->where('schedule_links.status', true)
		;
	}

	public function Groups() {
		return $this
			->hasManyThrough(
				'Models\Group',
				'Models\ScheduleLink',
				'schedule_id',
				'id',
				'id',
				'link_id'
			)
			->where('schedule_links.type', 'groups')
			->where('schedule_links.status', true)
		;
	}

	public function Links() {
		return $this->hasMany('Models\ScheduleLink', 'schedule_id', 'id');
	}
	public function ResourceLinks() {
		return
			$this
				->Links()
				->where('status', true)
				->where('type', 'resources')
		;
	}

	public function UserLink() {
		return
			$this
				->hasOne('Models\ScheduleLink', 'schedule_id', 'id')
				->where('status', true)
				->where('type', 'users')
		;
	}

	public function Venue() {
		return
			$this
				->hasOne('Models\TableExtension','table_id','id')
				->where('table', 'schedules')
		;
	}

	// Checks if event and assigned lesson to it was created by same person, if that is the case allow updating lesson resources or vice verse.
	public static function sameCreator($learning_course_module = false) {
		// In this case will find out event from learning_course_modules using learning_course_id
		$lesson = \Models\LearningModule::find($learning_course_module->learning_course_id);
		if (!$lesson->created_by) {
			return false;
		}
		$event = \Models\Schedule
			::whereHas("Links", function($query) use ($lesson) {
				$query = $query
					->where('schedule_links.type', 'lesson')
					->where('schedule_links.link_id', $lesson->id)
					->where('schedule_links.status', true)
				;
			})
			->where('created_by', $lesson->created_by)
			->first()
		;
		if (!$event) {
			return false;
		}

		return true;
	}

	public static function createEvent ($data, $manager = false) {
		// IF CD and managers are in data, create unique event for each manager

		if (isset($data["start_date"]) && isset($data["duration"])){
			$data["end_date"] = \Carbon\Carbon::parse($data["start_date"])->addMinutes($data["duration"]);
		}


		$event = new \Models\Schedule;
		$event->name = isset($data["name"]) ? $data["name"] : null;
		$event->cost = isset($data["cost"]) ? $data["cost"] : null;
		$event->description = isset($data["description"]) ? $data["description"] : '';
		$event->location = isset($data["location"]) ? $data["location"] : '';
		$event->type = isset($data["type"]) ? $data["type"] : null;
		$event->category_id = isset($data["category_id"]) ? $data["category_id"] : null;
		$event->visit_type_id = isset($data["visit_type_id"]) ? $data["visit_type_id"] : null;
		$event->start_date = isset($data["start_date"]) ? \Carbon\Carbon::parse($data["start_date"]) : null;
		$event->duration = isset($data["duration"]) ? $data["duration"] : null;
		$event->end_date = isset($data["end_date"]) ? \Carbon\Carbon::parse($data["end_date"]) : null;
		$event->parent_id = isset($data["parent_id"]) ? $data["parent_id"] : null;
		$event->created_by = $manager && isset($manager['id']) ? $manager['id'] : \APP\Auth::getUserId();
		$event->created_for = isset($data["created_for"]) ? $data["created_for"] : null;
		$event->visible_learner = isset($data["visible_learner"]) ? $data["visible_learner"] : false;
		$event->visible_learner_task = isset($data["visible_learner_task"]) ? $data["visible_learner_task"] : false;
		$event->visible_schedule = isset($data["visible_schedule"]) ? $data["visible_schedule"] : true;

		if (\APP\Tools::getConfig("enableGlobalOutlookIntegration") && \APP\Tools::getConfig("addAllEventstoOutlook")){
			$event->outlook_integration = 1;
			$event->outlook_refresh_token = "use_global";
		}

		$event->save();

		// IF extension fields are present loop them and update data accordingly.
		if (isset($data["extended"])) {
			foreach ($data["extended"] as $field_name => $value) {
				\Models\TableExtension::updateField('schedules', $event->id, $field_name, $value);
			}
		}

		// If "created_for", link event with that user!
		if ($event->created_for) {
			$link = \Models\ScheduleLink::firstOrNew(
				[
					"schedule_id" => $event->id,
					"type" => 'users',
					"link_id" => $event->created_for,
				]
			);
			$link->status = true;
			$link->cron_task = true;
			$link->save();
		}

		// If person who creates event is manager, link manager with that event!
		if (
			(
				\APP\Auth::isManager() ||
				\APP\Auth::isCD()
			) &&
			!$event->parent_id
		) {
			$link = \Models\ScheduleLink::firstOrNew(
				[
					"schedule_id" => $event->id,
					"type" => 'managers',
					"link_id" => ($manager && isset($manager['id']) ? $manager['id'] : \APP\Auth::getUserId()),
				]
			);
			$link->status = true;
			$link->cron_task = true;
			$link->save();
		}

		// If parent id is given, this is additional time for schedule, no need to add for permissions or cron task
		if (!$event->parent_id) {

			$event->cron_task = true;
			$event->save();

			// Create entry in schedule_permissions for user that he is owner
			$permission = new \Models\SchedulePermission;
			$permission->schedule_id = $event->id;
			$permission->type = 'owner';
			$permission->user_id = \APP\Auth::getUserId();
			$permission->save();


			// Currently event works with lessons, when creating event new lesson can be created or exisitng one can be chosen.
			if ($event->type) {
				$lesson_id = false;
				if (
					$data["type"] == 'lesson' &&
					isset($data["lesson_id"]) &&
					$data["lesson_id"] == 'new'
				) {
					$new_lesson = new \Models\LearningModule();
					$new_lesson->name = $event->name;
					$new_lesson->is_course = 1;
					$new_lesson->status = 1;
					$new_lesson->created_by = \APP\Auth::getUserId();
					if ($event->category_id) {
						$new_lesson->category_id = $event->category_id;
					}
					if ($event->description) {
						$new_lesson->description = $event->description;
					}
					$new_lesson->save();

					$lesson_id = $new_lesson->id;

					// Save type as lesson after setting up a new one.
					$event->type = $data["type"];
					$event->save();
				} else if (
					$data["type"] == 'lesson' &&
					isset($data["lesson_id"]) &&
					$data["lesson_id"] > 0
				) {
					$lesson_id = $data["lesson_id"];

					// Get all resources assigned to lesson and link them with event
					$resources = \Models\LearningModule
						::where('status', true)
						->whereIn('id',
							\Models\LearningCourseModule
								::select('learning_module_id')
								->where('learning_course_id', $lesson_id)
								->get()
						)
						->get()
					;
					foreach ($resources as $key => $resource) {
						$link = \Models\ScheduleLink::firstOrNew(
							[
								"schedule_id" => $event->id,
								"type" => 'resources',
								"link_id" => $resource->id,
							]
						);
						$link->status = true;
						$link->cron_task = true;
						$link->save();
					}
				}
				if ($lesson_id) {
					$link = \Models\ScheduleLink::firstOrNew(
						[
							"schedule_id" => $event->id,
							"type" => 'lesson',
							"link_id" => $lesson_id,
						]
					);
					$link->status = true;
					$link->cron_task = true;
					$link->save();
				}
			}
		}

		return $event->id;
	}

	public static function countAndConditions($query, &$params) {

		$schedule_id = false;

		if (isset($params["search"]["schedule_id"])) {
			$schedule_id = $params["search"]["schedule_id"];
			unset($params["search"]["schedule_id"]);
		}

		if (
			isset($params["link"]) &&
			$params["link"] == '/schedule/' &&
			isset($params["link_id"])
		) {
			$schedule_id = $params["link_id"];
		}

		$relationship = 'Schedules';
		if (isset($params["search"]["relationship"])) {
			$relationship = $params["search"]["relationship"];
			unset($params["search"]["relationship"]);
		}


		if ($schedule_id) {
			$query = $query
				->withCount([$relationship => function($query) use ($schedule_id) {
					$query
						->where('schedule_links.schedule_id', $schedule_id)
						->where('schedule_links.status', true)
					;
				}])
			;

			if (isset($params["search"]["added"])) {
				$added = $params["search"]["added"];
				unset($params["search"]["added"]);
				if ($added == 1) {
					$query = $query
						->whereHas($relationship, function ($query) use ($added, $schedule_id) {
							$query
								->where('schedule_links.schedule_id', $schedule_id)
								->where('schedule_links.status', true)
							;
						})
					;
				} else {
					$query = $query
						->whereDoesntHave($relationship, function ($query) use ($added, $schedule_id) {
							$query
								->where('schedule_links.schedule_id', $schedule_id)
							;
						})
					;
				}
			}
		}
		return $query;
	}

	public static function setForCron($id) {
		$entry = \Models\Schedule::find($id);
		$entry->cron_task = true;
		$entry->save();
	}


	// List all changed schedule events and update relevant assignment tables.
	public static function processEvents($settings = null, $event_ids = false, $user_ids = false) {

		if ($event_ids) {
			if (!is_array($event_ids)) {
				$event_ids = [$event_ids];
			}
		}


		if ($user_ids) {
			if (!is_array($user_ids)) {
				$user_ids = [$user_ids];
			}
		}

		$schedules = \Models\Schedule
			::where('id', '>', 0)
		;

		// Process only events that are given!
		if ($event_ids) {
			$schedules = $schedules
				->whereIn('id', $event_ids)
			;
		}
		$schedules->where('id',487); //TODO need to remove this
		$schedules = $schedules
			->get()
		;
dd($schedules);

		// Set up variables used in reminder
		$now = \Carbon\Carbon::now();
		$lead_minutes = \APP\Tools::getConfig('scheduleReminderLead');
                $lead_event_minutes = \APP\Tools::getConfig('eventReminderLead');
                $lead_virtual_event_minutes = \APP\Tools::getConfig('virtualEventReminderLead');
		foreach ($schedules as $key => $schedule) {
			// Process deleted events
			if (
				!$schedule->status &&
				$schedule->parent_id == NULL &&
				$schedule->cron_task == true
			) {
				$users = \Models\User
					::whereIn('users.id',
						\Models\ScheduleLink
							::select('link_id')
							->where('schedule_id', $schedule->id)
							->where('type', 'users')
							->get()
					)
				;

				if ($user_ids) {
					$users = $users
						->whereIn('id', $user_ids)
					;
				}

				$users = $users
					->get()
				;

				$resources = \Models\LearningModule
					::whereIn('learning_modules.id',
						\Models\ScheduleLink
							::select('link_id')
							->where('schedule_id', $schedule->id)
							->where('type', 'resources')
							->get()
					)
					->pluck('learning_modules.id')
					->toArray()
				;

				$lessons = \Models\LearningModule
					::whereIn('learning_modules.id',
						\Models\ScheduleLink
							::select('link_id')
							->where('schedule_id', $schedule->id)
							->where('type', 'lesson')
							->get()
					)
					->pluck('learning_modules.id')
					->toArray()
				;

				$issues = \Models\ApprenticeshipIssues
					::whereIn('apprenticeship_issues.id',
						\Models\ScheduleLink
							::select('link_id')
							->where('schedule_id', $schedule->id)
							->where('type', 'issues')
							->get()
					)
					->pluck('apprenticeship_issues.id')
					->toArray()
				;

				// Process assigned users
				$merged_resources = array_merge($resources, $lessons);
				$assigned_user_ids = [];
				foreach ($users as $key => $user) {
					$assigned_user_ids[] = $user->id;

					// Detach resources/lessons from user
					\Models\UserLearningModule::unlinkResources($user->id, $merged_resources, 'schedule - process events, remove resources from user, from deleted event');

					// Remove issue<->resource map from user in "apprenticeship_issues_user_learning_modules"
					\Models\ApprenticeshipIssuesUserLearningModules
						::whereIn('apprenticeship_issues_id', $issues)
						->whereIn('learning_modules_id', $merged_resources)
						->where('user_id', $user->id)
						->delete()
					;
				}

				// If $user_ids is provided delete links only related to that user and do not delete event
				if ($user_ids) {
					//Delete specific links!
					\Models\ScheduleLink
						::where('schedule_id', $schedule->id)
						->where('type', 'users')
						->whereIn('link_id', $user_ids)
						->update(['deleted_by' => $schedule->deleted_by])
					;
					\Models\ScheduleLink
						::where('schedule_id', $schedule->id)
						->where('type', 'users')
						->whereIn('link_id', $user_ids)
						->delete()
					;
				} else {
					//Delete all links!
					\Models\ScheduleLink
						::where('schedule_id', $schedule->id)
						->update(['deleted_by' => $schedule->deleted_by])
					;
					\Models\ScheduleLink
						::where('schedule_id', $schedule->id)
						->delete()
					;

					// Delete children schedule_links
					\Models\ScheduleLink
						::whereIn('schedule_id',
							\Models\Schedule
								::select('id')
								->where('parent_id', $schedule->id)
								->get()
						)
						->update(['deleted_by' => $schedule->deleted_by])
					;
					\Models\ScheduleLink
						::whereIn('schedule_id',
							\Models\Schedule
								::select('id')
								->where('parent_id', $schedule->id)
								->get()
						)
						->delete()
					;


					// Delete children
					\Models\Schedule
						::where('parent_id', $schedule->id)
						->update(['deleted_by' => $schedule->deleted_by])
					;
					\Models\Schedule
						::where('parent_id', $schedule->id)
						->delete()
					;

					// Delete permissions
					$schedule->permissions()->delete();

					// Delete Forums attached to Schedule!
					\Models\Post
						::whereIn('topic_id',
							\Models\Topic
								::select('id')
								->whereIn('forum_id',
									\Models\Forum
										::select('id')
										->where('schedule_id', $schedule->id)
										->get()
								)
								->get()
						)
						->delete()
					;
					\Models\Topic
						::whereIn('forum_id',
							\Models\Forum
								::select('id')
								->where('schedule_id', $schedule->id)
								->get()
						)
						->delete()
					;
					\Models\Forum::where('schedule_id', $schedule->id)->delete();


					// Send out emails to all attached users that this event is canelled.
					$template = \Models\EmailTemplate::getTemplate('event_cancellation');
					if (
						$template &&
						count($assigned_user_ids) > 0
					) {
						$email_queue = new \Models\EmailQueue;
						$email_queue->email_template_id = $template->id;
						$email_queue->recipients = $assigned_user_ids;
						$email_queue->from = $schedule->deleted_by;
						$email_queue->custom_variables = json_encode([
							'EVENT_NAME' => $schedule->name,
						]);
						$email_queue->save();
					}

					// Delete schedule
					$schedule->delete();
				}
			}

			// Action alive events
			if (
				$schedule->status &&
				$schedule->parent_id == NULL &&
				$schedule->cron_task == true
			) {
				/*
					UPDATE ALL USERS/RESOURCES/ISSUES relationships using active schedule links!
				*/
				$users = \Models\User
					::whereIn('users.id',
						\Models\ScheduleLink
							::select('link_id')
							->where('schedule_id', $schedule->id)
							->where('type', 'users')
							->get()
					)
					->with(['ScheduleLink' => function($query) use($schedule) {
						$query
							->where('schedule_links.schedule_id', $schedule->id)
						;
					}])
				;

				if ($user_ids) {
					$users = $users
						->whereIn('id', $user_ids)
					;
				}

				$users = $users
					->get()
				;

				$resources = \Models\LearningModule
					::whereIn('learning_modules.id',
						\Models\ScheduleLink
							::select('link_id')
							->where('schedule_id', $schedule->id)
							->where('type', 'resources')
							->where('status', true)
							->get()
					)
					->pluck('learning_modules.id')
					->toArray()
				;

				$lessons = \Models\LearningModule
					::whereIn('learning_modules.id',
						\Models\ScheduleLink
							::select('link_id')
							->where('schedule_id', $schedule->id)
							->where('type', 'lesson')
							->where('status', true)
							->get()
					)
					->pluck('learning_modules.id')
					->toArray()
				;

				$issues = \Models\ApprenticeshipIssues
					::whereIn('apprenticeship_issues.id',
						\Models\ScheduleLink
							::select('link_id')
							->where('schedule_id', $schedule->id)
							->where('type', 'issues')
							->where('status', true)
							->get()
					)
					->pluck('apprenticeship_issues.id')
					->toArray()
				;


				/*
					Take all DELETED schedule links and unasign users from resources, etc.
				*/
				$deleted_users = \Models\User
					::whereIn('users.id',
						\Models\ScheduleLink
							::select('link_id')
							->where('schedule_id', $schedule->id)
							->where('type', 'users')
							->where('status', false)
							->get()
					)
				;
				if ($user_ids) {
					$deleted_users = $deleted_users
						->whereIn('users.id', $user_ids)
					;
				}
				$deleted_users = $deleted_users
					->get()
				;

				$deleted_resources = \Models\LearningModule
					::whereIn('learning_modules.id',
						\Models\ScheduleLink
							::select('link_id')
							->where('schedule_id', $schedule->id)
							->where('type', 'resources')
							->where('status', false)
							->get()
					)
					->pluck('learning_modules.id')
					->toArray()
				;

				$deleted_lessons = \Models\LearningModule
					::whereIn('learning_modules.id',
						\Models\ScheduleLink
							::select('link_id')
							->where('schedule_id', $schedule->id)
							->where('type', 'lesson')
							->where('status', false)
							->get()
					)
					->pluck('learning_modules.id')
					->toArray()
				;

				$deleted_issues = \Models\ApprenticeshipIssues
					::whereIn('apprenticeship_issues.id',
						\Models\ScheduleLink
							::select('link_id')
							->where('schedule_id', $schedule->id)
							->where('type', 'issues')
							->where('status', false)
							->get()
					)
					->pluck('apprenticeship_issues.id')
					->toArray()
				;

				$merged_deleted_resources = array_merge($deleted_resources, $deleted_lessons);
				$merged_resources = array_merge($resources, $lessons);

				$send_email_to_users_ids = [];

				// Process ALL users
				foreach ($users as $key => $user) {

					/* what is happening here?
						// Detach resources/lessons from user
						$user->modules()->detach($merged_resources);
						// Attach them back!
						$user->modules()->attach($merged_resources);
					*/
					\Models\UserLearningModule::linkResources($user->id, $merged_resources, 'schedule - process events, link resources to user');
					\Models\UserLearningModule::unlinkResources($user->id, $merged_deleted_resources, 'schedule - process events, remove resources from user, from active event');

					\APP\Learning::syncUserResults($user->id);

					// Link issues with modules/users
					foreach ($issues as $key => $issue) {
						foreach ($merged_resources as $key => $merged_resource) {
							$issue_link = \Models\ApprenticeshipIssuesUserLearningModules::firstOrNew(
								[
									"apprenticeship_issues_id" => $issue,
									"learning_modules_id" => $merged_resource,
									"user_id" => $user->id,
								]
							);
							$issue_link->save();
						}
					}

					// Unlink user - standard issues - event lesson/resources
					\Models\ApprenticeshipIssuesUserLearningModules
						::whereIn('apprenticeship_issues_id', $deleted_issues)
						->whereIn('learning_modules_id', $merged_resources)
						->where('user_id', $user->id)
						->delete()
					;

					// If user link is marked for cron task and status is 1, then he is added and create new email for him to be sent out!
					if (
						$user->ScheduleLink->status == 1 &&
						$user->ScheduleLink->cron_task == 1 &&
						$schedule->visible_learner &&
						!$user->ScheduleLink->ignore_email
					) {
						$send_email_to_users_ids[] = $user->id;
					}
				}

				// Get all schedule links to resources, get field completion_date_custom, update all learning results with that!
				$completion_date_links = \Models\ScheduleLink
					::where('status', true)
					->where('cron_task', true)
					->where('type', 'resources')
					//->whereNotNull('completion_date_custom')
					->where('schedule_id', $schedule->id)
					->get()
				;

				$duration = !empty($schedule->duration) ? $schedule->duration : 0;
				foreach ($completion_date_links as $key => $completion_date_link) {
					// update all learning results 'completion_date_custom' field with users in this schedule
					$learning_results = \Models\LearningResult
						::where('learning_module_id', $completion_date_link->link_id)
						->where('refreshed', 0)
						->where('completion_status', '!=', 'completed')
						->whereIn('user_id',
							\Models\ScheduleLink
								::select('link_id')
								->where('schedule_id', $schedule->id)
								->where('type', 'users')
								->where('status', true)
								->get()
						)
					;

					if ($user_ids) {
						$learning_results = $learning_results
							->whereIn('user_id', $user_ids)
						;
					}

					$learning_results = $learning_results
						->get()
					;
					foreach ($learning_results as $key => $learning_result) {
						$learning_result->completion_date_custom = $completion_date_link->completion_date_custom;
						$learning_result->due_at = \Carbon\Carbon::parse($schedule->start_date)->addMinutes($duration);
						$learning_result->save();
					}
					//update resource link to make sure its completion status is synced with the corresponding user completion status
					$completion_date_link->save();
				}


				// Set up email queueue
				if (count($send_email_to_users_ids) > 0) {
					switch ($schedule->type) {
						case 'lesson':
							$template_slug = 'schedule_created';
						break;
						case 'meeting':
							$template_slug = 'schedule_meeting_created';
						break;

						default:
							$template_slug = 'schedule_created';
						break;
					}

					$template = \Models\EmailTemplate::getTemplate($template_slug);
					if ($template) {

						// Retrieve manager
						$manager = \Models\User
							::whereIn('users.id',
								\Models\SchedulePermission
									::select('user_id')
									->where('schedule_permissions.schedule_id', $schedule->id)
									->where('schedule_permissions.type', 'owner')
									->get()
							)
							->first()
						;

						$start_date_uk = \Carbon\Carbon::parse($schedule->start_date);
						$email_queue = new \Models\EmailQueue;
						$email_queue->email_template_id = $template->id;
						$email_queue->recipients = $send_email_to_users_ids;
						$email_queue->from = $manager->id;
						$email_queue->custom_variables = json_encode([
							'EVENT_NAME' => $schedule->name,
							'EVENT_LOCATION' => $schedule->location,
							'EVENT_DESCRIPTION' => $schedule->description,
							'LESSON_ID' => ($lessons && $lessons[0] ? $lessons[0] : ''),
							'EVENT_TIME' => $start_date_uk->format("d/m/Y H:i"),
							'MANAGER_FNAME' => $manager->fname,
							'MANAGER_LNAME' => $manager->lname,
						]);
						$email_queue->save();
					}
				}


				// process deleted users!
				foreach ($deleted_users as $key => $deleted_user) {
					// Detach all resources
					$deleted_user->modules()->detach($merged_resources);
					$deleted_user->modules()->detach($merged_deleted_resources);

					// Unlink deleted issues/resources link
					\Models\ApprenticeshipIssuesUserLearningModules
						::whereIn('apprenticeship_issues_id', $deleted_issues)
						->whereIn('learning_modules_id', $merged_deleted_resources)
						->where('user_id', $deleted_user->id)
						->delete()
					;

					// Unlink remaining issues/resources link
					\Models\ApprenticeshipIssuesUserLearningModules
						::whereIn('apprenticeship_issues_id', $issues)
						->whereIn('learning_modules_id', $merged_resources)
						->where('user_id', $deleted_user->id)
						->delete()
					;
				}


				// If $user_ids is provided delete links only related to that user and do not update event
				if ($user_ids) {
					//Delete specific links!
					\Models\ScheduleLink
						::where('schedule_id', $schedule->id)
						->where('type', 'users')
						->whereIn('link_id', $user_ids)
						->where('status', false)
						->update(['deleted_by' => $schedule->deleted_by])
					;
					\Models\ScheduleLink
						::where('schedule_id', $schedule->id)
						->where('type', 'users')
						->where('status', false)
						->whereIn('link_id', $user_ids)
						->delete()
					;
				} else {
					// Delete obselete links
					\Models\ScheduleLink
						::where('schedule_id', $schedule->id)
						->where('status', false)
						->delete()
					;

					// Update links as processed

					\Models\ScheduleLink
						::where('schedule_id', $schedule->id)
						->where('cron_task', true)
						->update(
							[
								'cron_task' => false
							]
						)
					;

					// Set schedule as processed
					$schedule->cron_task = false;
					$schedule->save();
				}



				//Add event to outlook
				if ($schedule->outlook_integration && $schedule->outlook_refresh_token){

					if (\Carbon\Carbon::parse($schedule->start_date)->gte(\Carbon\Carbon::now())){

						try {

							$teams = new \APP\Teams(false);

							if (\APP\Tools::getConfig("enableGlobalOutlookIntegration") && ($schedule->outlook_refresh_token == "use_global")){
								$teams->refresh_global_calendar_oauth_token(\APP\Tools::getConfig("GlobalOutlookIntegrationSecurityToken"));
							} else {
								$teams->refresh_calendar_oauth_token($schedule->outlook_refresh_token);
							}

							$meeting_url = false;

							if (isset($merged_resources)) {

								$meeting_type_ids = \Models\LearningModuleType::whereIn("slug", ["microsoft_teams", "zoom_meeting"])->pluck("id");

								//get teams meeting again as they could have been modified
								if ($meeting = \Models\LearningModule::whereIn("type_id", $meeting_type_ids)->whereIn("id", $merged_resources)->first()){
									if ($meeting->material && $meeting->material->link){
										$meeting_url = $meeting->material->link;
									}
								}
							}

							$user_emails = [];

							foreach($users as $user){
								if (!in_array($user->email, $user_emails)) {
									$user_emails[] = $user->email;
								}
							}

							$managers = \Models\User
								::whereIn('users.id',
									\Models\ScheduleLink
										::select('link_id')
										->where('schedule_id', $schedule->id)
										->where('type', 'managers')
										->get()
								)
								->with(['ScheduleLink' => function($query) use($schedule) {
									$query
										->where('schedule_links.schedule_id', $schedule->id)
									;
								}])
							;
							if ($user_ids) {
								$managers = $managers
									->whereIn('users.id', $user_ids)
								;
							}

							$managers = $managers
								->get()
							;

							foreach($managers as $user){
								if (!in_array($user->email, $user_emails)) {
									$user_emails[] = $user->email;
								}
							}

							$teams->createEventFromSchedule($schedule, $user_emails, $meeting_url);
						} catch(\Exception $e) {
							// Sometimes Access token denied is returned here with 500, should that kill all event process?
						}
					}
				}
			}


			// Loop all schedules, check if reminder needs to be sent out, only to those that are visible to learner
			if (
				$schedule->status &&
				!$schedule->reminder_sent &&
				$schedule->visible_learner &&
				!$user_ids // Do not check reminders if this eventprocessing is triggered by user.
			) {
				// check start time, even for children, send out reminders!
				$start_date = \Carbon\Carbon::parse($schedule->start_date);
                                $difference =  $start_date->diffInMinutes($now);
                              	$virtualMeetings = \Models\LearningModule
						::whereIn('type_id', [19, 22])
						->whereIn('learning_modules.id',
							\Models\ScheduleLink
								::select('link_id')
								->where('schedule_id', $schedule->id)
								->whereIn('type', ['resources','lesson'])
								->get()
						)
						->get()
					;


				if (
					$start_date > $now &&
					((count($virtualMeetings) > 0) && ($difference < $lead_virtual_event_minutes)) || ((count($virtualMeetings)==0) && ($difference < $lead_event_minutes))
				) {
					$schedule_name = $schedule->name;
					$schedule_id = $schedule->id;


					// If schedule is child, get parent!
					if (
						$schedule->parent_id
					) {
						// Get parent of schedule!
						$schedule_parent = \Models\Schedule::find($schedule->parent_id);
						if (!$schedule->name) {
							$schedule_name = $schedule_parent->name;
						}
						$schedule_id = $schedule_parent->id;
					}
					$reminder_users_ids = \Models\User
						::whereIn('users.id',
							\Models\ScheduleLink
								::select('link_id')
								->where('schedule_id', $schedule_id)
								->where('type', 'users')
								->get()
						)
					->pluck('users.id')
					->toArray()
					;

									switch ($schedule->type) {
						case 'lesson':
							if (count($virtualMeetings) > 0) {
								$template_slug = 'schedule_reminder';
							} else {
								$template_slug = 'event_reminder';
							}
						break;
						case 'meeting':
							$template_slug = 'schedule_meeting_reminder';
						break;

						default:
							$template_slug = 'schedule_reminder';
						break;
					}

					$template = \Models\EmailTemplate::getTemplate($template_slug);
					if (
						$template &&
						count($reminder_users_ids) > 0
					) {
						$reminder_lesson = \Models\LearningModule
							::whereIn('learning_modules.id',
								\Models\ScheduleLink
									::select('link_id')
									->where('schedule_id', $schedule_id)
									->where('type', 'lesson')
									->get()
							)
							->first()
						;

						// Retrieve manager
						$manager = \Models\User
							::whereIn('users.id',
								\Models\SchedulePermission
									::select('user_id')
									->where('schedule_permissions.schedule_id', $schedule_id)
									->where('schedule_permissions.type', 'owner')
									->get()
							)
							->first()
						;

						$start_date_uk = \Carbon\Carbon::parse($schedule->start_date);
						$email_queue = new \Models\EmailQueue;
						$email_queue->email_template_id = $template->id;
						$email_queue->recipients = $reminder_users_ids;
						$email_queue->from = $manager->id;
						$email_queue->custom_variables = json_encode([
							'EVENT_NAME' => $schedule_name,
							'EVENT_LOCATION' => $schedule->location,
							'EVENT_DESCRIPTION' => $schedule->description,
							'LESSON_ID' => ($reminder_lesson ? $reminder_lesson->id . '-' . $schedule->id : ''),
							'EVENT_TIME' => $start_date_uk->format("d/m/Y H:i"),
							'MANAGER_FNAME' => $manager->fname,
							'MANAGER_LNAME' => $manager->lname,
						]);
						$email_queue->save();

						$schedule->reminder_sent = true;
						$schedule->save();
					}
				}
			}

			// //Process requests to add teams meetings to outlook calendar
			// if (isset($merged_resources)) {

			// 	$teams_type_id = \Models\LearningModuleType::where("slug", "=", "microsoft_teams")->first()->id;

			// 	//get teams meeting again as they could have been modified
			// 	$teams_meetings = \Models\LearningModule::where("type_id", "=", $teams_type_id)->whereIn("id", $merged_resources)->get();


			// 	foreach($teams_meetings as $meeting) {

			// 		//check if outlook integration is requested and then the refresh_token is available
			// 		if (
			// 			isset($meeting->material->add_to_outlook)
			// 			&& $meeting->material->add_to_outlook
			// 			&& isset($meeting->material->outlook_refresh_token)
			// 			){

			// 			$teams = new \APP\Teams(false);
			// 			$teams->refresh_calendar_oauth_token($meeting->material->outlook_refresh_token);


			// 			try {
			// 				//create event in Outlook
			// 				$response = $teams->createEvent($meeting);
			// 				$meeting->material = [
			// 					"sessions" => $meeting->material->sessions,
			// 					"link" => $meeting->material->link,
			// 					"add_to_outlook" => 1,
			// 					"outlook_refresh_token" => $meeting->material->outlook_refresh_token,
			// 					"start_date" => $meeting->material->start_date,
			// 					"end_date" => $meeting->material->end_date,
			// 					"topic" => $meeting->material->topic,
			// 					"outlook_event_id" => isset($response->id) ? $response->id : false
			// 				];
			// 				$meeting->save();
			// 			} catch(\Exception $e) {
			// 				// we need to add some logging here
			// 			}
			// 		}
			// 	}
			// }
		}
	}


	/*Forum Relation a schedule event will be only having one forum currently*/
	public function forum() {
		return $this->hasOne('Models\Forum','schedule_id');
	}

	// Delete event!
	public static function deleteEvent ($schedule_id = false) {
		if ($schedule_id) {
			$event = \Models\Schedule::find($schedule_id);
			// If this is child, just delete it, else mark for cron action!
			/*Delete all related References*/
			if ($event->parent_id) {
				if ($event->comments()->exists()) {
					$event->comments()->delete();
				}

				$forum = $event->forum();
				if ($forum->exists()) {
					if ($forum->posts()->exists()) {
						$forum->posts()->delete();
					}

					if ($forum->topics()->exists()) {
						$forum->topics()->delete();
					}
					$forum->delete();
				}
				$event->deleted_by = \App\Auth::getUserId();
				$event->save();

				$event->delete();
			} else {
				$event->status = false;
				$event->deleted_by = \App\Auth::getUserId();
				$event->cron_task = true;
				$event->save();
			}
		}

	}


	protected static function boot() {
		parent::boot();
		static::saving(function($schedule) {
			// Allways set this to true for lesson types!
			if ($schedule->type == 'lesson') {
				$schedule->visible_learner = true;
			}
		});
	}

}
