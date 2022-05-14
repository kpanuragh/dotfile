<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Illuminate\Database\Capsule\Manager as DB;

$app->group("/learning", function () {

	$this->put('/disable/{id:[0-9]+}', function (Request $request, Response $response, $args) {
		$learning = \Models\LearningModule::find($args["id"]);
		$learning->status = 0;
		$learning->save();

		return $response;
	})->add(\APP\Auth::getStructureAccessCheck('library-learning-resources-and-lessons', 'disable'));

	$this->put('/enable/{id:[0-9]+}', function (Request $request, Response $response, $args) {
		$learning = \Models\LearningModule::find($args["id"]);
		$learning->status = 1;
		$learning->save();

		return $response;
	})->add(\APP\Auth::getStructureAccessCheck('library-learning-resources-and-lessons', 'disable'));

	// Disable/Enable multiple resources
	$this->put('/{state}-multiple', function (Request $request, Response $response, $args) {
		$data = $request->getParsedBody();

		if (
			isset($data['ids']) &&
			is_array($data['ids'])
		) {
			$query = \Models\LearningModule
				::whereIn('id', $data['ids']);
			if ($args['state'] == 'disable') {
				$query = $query
					->update(['status' => false]);
			}
			if ($args['state'] == 'enable') {
				$query = $query
					->update(['status' => true]);
			}
		}
		return $response;
	})->add(\APP\Auth::getStructureAccessCheck('library-learning-resources-and-lessons', 'disable'));


	// Move/change category for given resources
	$this->put('/move-category', function (Request $request, Response $response, $args) {
		$data = $request->getParsedBody();

		if (
			isset($data['ids']) &&
			is_array($data['ids']) &&
			isset($data['category_id'])
		) {
			$query = \Models\LearningModule
				::whereIn('id', $data['ids'])
				->update(['category_id' => $data['category_id']]);
		}
		return $response;
	})->add(\APP\Auth::getStructureAccessCheck('library-learning-resources-and-lessons', 'update'));

	// Get lesson
	$this->get('/course/{id:[0-9]+}', function (Request $request, Response $response, $args) {
		$learning = \Models\LearningModule
			::with(["modules" => function ($query) {
				$query
					->select("learning_modules.id", "name")
					->orderBy('learning_course_modules.id', 'asc')
				;
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
							->with('role');
					}]);
			}])
			->find($args["id"]);

		return
			$response
				->withHeader('Content-Type', 'application/json')
				->write(json_encode($learning));
	})->add(\APP\Auth::getStructureAccessCheck('library-learning-resources-and-lessons', 'select'));

	// get learning resource
	$this->get('/module/{id:[0-9]+}[/{schedule_id:[0-9]+}]', function (Request $request, Response $response, $args) {
		$user = \Models\User::findOrFail(\APP\Auth::getUserId());

		// If User is learner, check if resource is assigned to user or enrollable, if neither are, deny access!
		if (
			\APP\Auth::isLearningInterface()
		) {
			$assigned = \Models\UserLearningModule
				::where('learning_module_id', $args["id"])
				->where('user_id', \APP\Auth::getUserId())
				->first()
			;
			$enrollable = $learning = \Models\LearningModule
				::where('id', $args["id"])
				->where('self_enroll', true)
				->first()
			;
			$enrollable_event = false;
			if (isset($args['schedule_id'])) {
				$enrollable_event = \Models\Schedule
					::where('id', $args['schedule_id'])
					->where('schedules.status', true)
					->where('visible_schedule', true)
					->where('enrole_any_learner', 1)
					->where('parent_id', null)
					->whereNotIn('schedules.id',
						\Models\ScheduleLink
							::select('schedule_id')
							->where('type', 'users')
							->where('link_id', \APP\Auth::getUserId())
							->get()
					)
					->whereIn('schedules.id',
						\Models\ScheduleLink
							::select('schedule_id')
							->where('type', 'lesson')
							->where('link_id', $args["id"])
							->get()
					)
					->first()
				;
			}

			if (
				!$assigned &&
				!$enrollable &&
				!$enrollable_event
			) {
				return $response
					->withStatus(403)
					->withHeader('Content-Type', 'text/html')
					->write('403 Forbidden')
				;
			}
		}

		$learning = \Models\LearningModule
			::where('learning_modules.id', $args["id"])
			->with(["type" => function ($query) {
				$query
					->with("LearningModuleTypeParameter");
			}])
			->with("competencies")
            ->with("companies")
			->with("competencies")
			->with(["AssessmentCategories" => function ($query) {
				$query
					->with("questions");
			}])
			->with("AssessmentQuestions")
			->with(["prerequisites" => function ($query) {
				$query->select("learning_modules.id", "name");
			}])
			->with(["LearningModuleEvidences" => function ($query) use ($user, $args) {
				$query
				->where(function($query) use ($user, $args) {
					$query
						->whereRaw('user_id = ? ', [$user->id]) //see your own added evidence
						->orWhereRaw('manager = ?', [1]) // manager added this, so everyone must see this.
					;
				});
			}])

			->select('*',DB::raw('DATE_FORMAT(learning_modules.created_at, "%d/%m/%Y %H:%i") as version_created_at'),DB::raw('DATE_FORMAT(learning_modules.updated_at, "%d/%m/%Y %H:%i") as version_updated_at'))
		;

		if (
			isset($enrollable_event) &&
			$enrollable_event
		) {
			$learning = $learning
				->with(['ScheduleLessonLinks' => function ($query) use ($enrollable_event) {
					$query
						->select([
							'id',
							'schedule_id',
							'link_id'
						])
						->where('schedule_id', $enrollable_event->id)
						->with(['Schedule' => function ($query) {
							$query
								->select([
									'id',
									'name',
									'cost',
									'description',
									'category_id',
									'start_date',
									'duration',
									'enrole_any_learner',
									DB::raw("CONCAT(DATE_FORMAT(start_date,'%d/%m/%Y %H:%i'), '-', DATE_FORMAT(DATE_ADD(start_date, INTERVAL duration MINUTE),'%H:%i')) AS event_date_range")
								])
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
								->with(['WaitingForApproval' => function ($query) {
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
							;
						}])
					;
				}])
			;
		}

		$learning = $learning
			->first()
		;

		$learning->setAppends(['safe_thumbnail', 'safe_promo', 'highlight']);

		return
			$response
				->withHeader('Content-Type', 'application/json')
				->write(json_encode($learning))
		;
	})->add(\APP\Auth::getStructureAccessCheck(['library-learning-resources-and-lessons', 'trainee-modules'], 'select'));

	// Approve learning result
	$this->put('/{module_id:[0-9]+}/{user_id:[0-9]+}/{id:[0-9]+}/approve', function (Request $request, Response $response, $args) {
		$data = $request->getParsedBody();
		$learning_result = \Models\LearningResult
			::where("id", '=', $args["id"])
			->where("learning_module_id", "=", $args["module_id"])
			//->where("user_id", "=", $args["user_id"])
			->firstOrFail()
		;
		$learning_result->approved = true;
		$learning_result->is_paid = $data["is_paid"];
		$learning_result->save();

		$learner_details = \Models\User::find($args["user_id"]);

		$learning_module = \Models\LearningModule
			::where("id", "=", $args["module_id"])
			->firstOrFail()
		;

		if ($learning_module->approval == "1") {
			// Send e-mail to Manager that status changes to approval
			$template = \Models\EmailTemplate
				::where('name', 'Resource Approval')
				->where('status', true)
				->first()
			;

			if ($template && $template->id) {
				$email_queue = new \Models\EmailQueue;
				$email_queue->email_template_id = $template->id;
				$email_queue->recipients = [$learner_details->id];
				$email_queue->from = \APP\Auth::getUserId();
				$email_queue->custom_variables = json_encode([
					'USER_FNAME' => $learner_details->fname,
					'RESOURCE_NAME'=>$learning_module->name,
					'CONFIG_LMSUrl' => $GLOBALS["CONFIG"]->LMSUrl,
					'RESOURCE_APPROVAL_LINK'=>'app/learner/resources/'.$args["module_id"],
					'REGARDS'=>$GLOBALS["CONFIG"]->Regards,
				]);
				$email_queue->save();
			}
	    }

		return $response;

	})->add(\APP\Auth::getStructureAccessCheck('lessons-and-learning-resources', 'update'));

	$this->post('/addqa/{module_id:[0-9]+}/{user_id:[0-9]+}/{id:[0-9]+}', function (Request $request, Response $response, $args) {
		$data = $request->getParsedBody();
		$learning_result = \Models\LearningResult
			::where("id", '=', $args["id"])
			->where("learning_module_id", "=", $args["module_id"])
			->where("user_id", "=", $args["user_id"])
			->with('User')
			->firstOrFail();

		if($learning_result->qa!=null && $learning_result->judgement_reason!=null){
			$qc = new \Models\QualityControl;
			$qc->type = 'learning_resource';
			$qc->type_id = $learning_result->id;
			$qc->user_id = $args["user_id"];
			$qc->qa_user_id = \APP\Auth::getUserId();
			$qc->qa = $learning_result->qa;
			$qc->qa_favorite = 0;
			$qc->judgement_reason = $learning_result->judgement_reason;
			$qc->created_at = $learning_result->qa_date ? $learning_result->qa_date : $learning_result->created_at;
			$qc->save();
			$learning_result->qa = $data["qa"];
			$learning_result->judgement_reason = $data['judgement_reason'];
			$learning_result->qa_date = \Carbon\Carbon::now();
			$learning_result->qa_created_by = \APP\Auth::getUserId();

			$learning_result->save();
			return $response;
		}
		return $response;
	})->add(\APP\Auth::getStructureAccessCheck('lessons-and-learning-resources', 'update'));

	// Update learning result
	// TODO: need a rework of this, in eventual future! Too much if/elses and cascading logic
	$this->put('/{module_id:[0-9]+}/{user_id:[0-9]+}/{id:[0-9]+}', function (Request $request, Response $response, $args) {
		$data = $request->getParsedBody();

		$learning_result = \Models\LearningResult
			::where("id", $args["id"])
			->where("learning_module_id", $args["module_id"])
			->where("user_id", $args["user_id"])
			->with('User')
			->first()
		;

		if (!$learning_result) {
			return
				$response
					->withStatus(404)
					->withHeader('Content-Type', 'text/html')
					->write('404 Not Found')
			;
		}


		if (
			$learning_result->module->expiration_date &&
			$learning_result->module->expiration_date < \Carbon\Carbon::now()
		) {
			return
				$response
					->withStatus(500)
					->withHeader('Content-Type', 'text/html')
					->write('The learning resource has expired.  Contact your administrator to get this resource extended in order to change its status.');
		}

		if (
			$this->settings['licensing']['isApprentix'] &&
			isset($data['qa_favorite']) &&
			\APP\Auth::isManager()
		) {
			$favorite = \Models\Favorite
				::where('user_id', \APP\Auth::getUserId())
				->where('relation_id', $learning_result->id)
				->where('type', 'learning-qa')
				->first();
			if ($data['qa_favorite']) {
				if (count($favorite) == 0) {
					$favorite = new \Models\Favorite;
					$favorite->user_id = \APP\Auth::getUserId();
					$favorite->relation_id = $learning_result->id;
					$favorite->type = 'learning-qa';
					$favorite->save();
				}
			} else {
				if ($favorite) {
					$favorite->delete();
				}
			}
		}

		$learning_result->module->prerequisites = \APP\Learning::augmentPrerequisitesLearningResult(
			$args["user_id"],
			$learning_result->module->prerequisites
		);

		$hassAccessToOfflineTaskStatus =
			$args["user_id"] == \APP\Auth::getUserId() &&
			in_array($learning_result->module->type_id, [5, 6]) &&
			\APP\Auth::checkStructureAccess(['misc-permissions-change-offline-task-status'], 'update');

		if (isset($data["completed_at"])) {
			// if completed_at is passed, but is empty, nullify completed date, in case status is changed from complete to something else.
			if ($data["completed_at"] != null) {
				$data["completed_at"] = \Carbon\Carbon::parse($data["completed_at"]);
			} else {
				$data["completed_at"] = null;
			}
		} else {
			$data["completed_at"] = \Carbon\Carbon::now();
		}
		// If grade is passed, update it!
		if (
			isset($data["grade"]) &&
			$data["grade"] &&
			\APP\Auth::isManagerOf($args["user_id"]) ||
			\APP\Auth::isAdmin() ||
			\APP\Auth::accessAllLearners()
		) {
			$learning_result->grade = $data["grade"];
		}


		// Toggle off_the_job_training
		if (
			$learning_result->user_id == \APP\Auth::getUserId() ||
			\APP\Auth::isManagerOf($learning_result->user_id) ||
			\APP\Auth::isAdmin() ||
			\APP\Auth::accessAllLearners()
		) {
			$off_the_job_training = false;
			if (
				isset($data['off_the_job_training']) &&
				$data['off_the_job_training']
			) {
				$off_the_job_training = true;
			}
			$learning_result->off_the_job_training = $off_the_job_training;
		}

		// If learning result is updated not by learner and status is changed, set it as action for learner

		if (
			!(
				$data["completion_status"] == 'completed' &&
				$learning_result->completion_status == 'completed'
			) &&
			!\APP\Auth::isLearner()
		) {
			$learning_result->learner_action = true;
			$learning_result->learner_action_date = \Carbon\Carbon::now();
		}


		if (
			(
				$args["user_id"] == \APP\Auth::getUserId()
				&& \APP\Auth::isManager()
			) ||
			\APP\Auth::isManagerOf($args["user_id"]) ||
			\APP\Auth::isAdmin() ||
			\APP\Auth::accessAllLearners() ||
			$hassAccessToOfflineTaskStatus
		) {
			if ($data["module"]["type_id"] == 1) {
				if (!isset($data["score"])) {
					if (!empty($learning_result->module->material->min_passing_percentage)) {
						$data["score"] = $learning_result->module->material->min_passing_percentage;
					} else {
						$data["score"] = 100;
					}
				}

				$course = \APP\Course::get($args["module_id"]);
				$course->updateUserScormRecord(
					$args["user_id"],
					$data["completion_status"],
					$learning_result->completion_status,
					$data["score"],
					$data["completed_at"],

					// Change completed_at date if you are admin and agree to change completed at date.
					(\APP\Auth::isAdmin() || \APP\Auth::accessAllLearners()) && isset($data['updateComplete']) && $data['updateComplete'] && $data["completed_at"] != null
				);
				$course->updateUserResult($args["user_id"], $this->settings['licensing']['isApprentix']);
			} else {
				if (isset($data["completion_status"])) {
					$learning_result->completion_status = $data["completion_status"];
				}
				if ($learning_result->completion_status == "completed") {
					$learning_result->completed_at = $data["completed_at"];
				}

				// Any other resource can have score also
				if (isset($data["score"])) {
					$learning_result->score = $data["score"];
				}

				if (
					\APP\Auth::isManager() ||
					\APP\Auth::isAdmin() ||
					\APP\Auth::accessAllLearners()
				) {
					if (
						(
							!$learning_result->sign_off_trainee ||
							!$learning_result->sign_off_manager
						) && (
							isset($data["sign_off_trainee"]) &&
							isset($data["sign_off_manager"])
						)
					) {
						$learning_result->sign_off_manager_by = \APP\Auth::getUserId();
						// Update date when manager signed off
						$learning_result->sign_off_manager_at = \Carbon\Carbon::now();
					}

					if (isset($data["sign_off_trainee"])) {
						$learning_result->sign_off_trainee = $data["sign_off_trainee"];
					}
					if (isset($data["sign_off_manager"])) {
						$learning_result->sign_off_manager = $data["sign_off_manager"];
					}

					// If siggned off by manager/admin, send email to trainee


					if (
						$learning_result->sign_off_trainee == 1 &&
						$learning_result->sign_off_manager == 1 &&
						isset($data["sign_off_refused"]) &&
						$data["sign_off_refused"] == false
					) {
						// Perform check if SMCR and resource in F&P category that is hidden to user and resource is upload, then do not send e-mail to user!
						$send_email = \APP\Smcr::sendLearnerEmail($learning_result);

						// Send out "Learning Resource Signed Off by Coach" to learner
						$template = \Models\EmailTemplate
							::where('name', '%%learning_resource%% Signed Off by %%manager%%')
							->where('status', true)
							->first();
						if (
							$template &&
							$template->id &&
							$send_email &&
							$learning_result->module->track_progress
						) {
							$email_queue = new \Models\EmailQueue;
							$email_queue->email_template_id = $template->id;
							$email_queue->learning_module_id = $args["module_id"];
							$email_queue->recipients = [intval($args["user_id"])];
							$email_queue->from = \APP\Auth::getUserId();
							$email_queue->save();
						}

						//Send e-mail to to QA if there are link between QA and this learning result
						$qa_learning_resource = \Models\QaLearningResult
							::where('learning_result_id', $learning_result->id)
							->first();
						if ($qa_learning_resource) {
							$qa_template = \Models\EmailTemplate
								::where('name', 'QA task completed and re-submitted for Quality Approval')
								->where('status', true)
								->first();

							$coach = \APP\Auth::getUser();
							$qa_user = \Models\User::find($qa_learning_resource->qa_id);

							if (
								$qa_template &&
								$qa_user &&
								$qa_user->status &&
								$learning_result->module->track_progress
							) {
								$email_queue = new \Models\EmailQueue;
								$email_queue->email_template_id = $qa_template->id;
								$email_queue->learning_module_id = $args["module_id"];
								$email_queue->recipients = [$qa_learning_resource->qa_id];
								$email_queue->from = $coach->id;
								$email_queue->custom_variables = json_encode([
									'COACH_NAME' => $coach->fname . ' ' . $coach->lname,
									'QA_LEARNER_NAME' => $learning_result->user->fname . ' ' . $learning_result->user->lname,
								]);
								$email_queue->save();
							}
						}
					}
				}


				if (
					isset($data["sign_off_refused"]) &&
					$data["sign_off_refused"]
				) {
					$learning_result->manager_refused_time = \Carbon\Carbon::now();
					$learning_result->manager_refused_by = \APP\Auth::getUserId();
					$learning_result->manager_refused_comment = null;
					$learning_result->completed_at = null;
					$learning_result->completion_status = 'in progress';
					$learning_result->sign_off_manager_by = null;
					$learning_result->sign_off_manager_at = null;

					$send_email = \APP\Smcr::sendLearnerEmail($learning_result);

					// Send e-mail to learner that resource is refused.
					$template = \Models\EmailTemplate::where('name', 'Learning Resource Needs Attention')
						->where('status', true)
						->first();
					if (
						$template &&
						$template->id &&
						$send_email &&
						$learning_result->module->track_progress
					) {
						$email_queue = new \Models\EmailQueue;
						$email_queue->email_template_id = $template->id;
						$email_queue->learning_module_id = $args["module_id"];
						$email_queue->recipients = [intval($args["user_id"])];
						$email_queue->from = \APP\Auth::getUserId();
						$email_queue->save();
					}

				}

				// Update all user's timing if standards are enabled
				if ($this->settings['licensing']['isApprentix']) {
					\Models\ApprenticeshipStandardUser::updateUserProgress($args["user_id"]);
				}

			}

			// Adding back this one, if completed_at is forced, do it to learning result too
			if (
				\APP\Auth::isAdmin() &&
				isset($data['updateComplete']) &&
				$data['updateComplete'] &&
				$data["completed_at"] != null
			) {
				$learning_result->completed_at = $data["completed_at"];
				$learning_result->completion_status = $data["completion_status"];
				$learning_result->score = $data["score"];
			}

			// If QA update just qa status
			if (
				\APP\Auth::isQa() &&
				isset($data["qa"])
			) {
				$learning_result->qa = $data["qa"];
				// if rejected, set status to "In progress"
				if ($learning_result->qa == 'Rejected') {
					$learning_result->completed_at = null;
					$learning_result->completion_status = 'in progress';
					$learning_result->sign_off_trainee = false;
					$learning_result->sign_off_manager = false;


					// Send e-mail to learners managers that this was rejected!
					// "Work rejected by QA"
					$template = \Models\EmailTemplate
						::where('name', 'Actions required following Quality Review')
						->where('status', true)
						->first();
					if (
						$template &&
						$template->id
					) {

						// Get all managers that needs notification
						$manager_ids = [];
						foreach ($learning_result->user->managers as $key => $manager) {
							if (
								!$manager->role->email_disable_manager_notifications &&
								$manager->status
							) {
								$manager_ids[] = $manager->id;
							}
						}

						if (
							count($manager_ids) > 0 &&
							$learning_result->module->track_progress
						) {
							$email_queue = new \Models\EmailQueue;
							$email_queue->email_template_id = $template->id;
							$email_queue->recipients = $manager_ids;
							$email_queue->from = \APP\Auth::getUserId();
							$email_queue->custom_variables = json_encode([
								'REJECTED_WORK' => $learning_result->module->name,
								'REJECTED_LEARNER' => $learning_result->user->fname . ' ' . $learning_result->user->lname,
								'REJECTED_WORK_ID' => $learning_result->module->id,
							]);
							$email_queue->save();
						}
					}
				}
				if (
					$learning_result->qa != $data['qa'] ||
					$learning_result->judgement_reason != $data['judgement_reason']
				) {
					if (
						$learning_result->qa != null &&
						$learning_result->judgement_reason != null
					) {

						$learning_result->qa_date = \Carbon\Carbon::now();
						$learning_result->qa_created_by = \APP\Auth::getUserId();
					}
				}

				// Add this user to qa-learning_results table
				\Models\QaLearningResult::firstOrCreate(
					[
						'qa_id' => \APP\Auth::getUserId(),
						'learning_result_id' => $learning_result->id
					]
				);
			}


			$learning_result->judgement_reason = $data['judgement_reason'];
			$learning_result->save();
		}

		if (isset($data["extended"])) {
			foreach ($data["extended"] as $field_name => $value) {
				\Models\TableExtension::updateField('learning_results', $learning_result->id, $field_name, $value);
			}
		}

		return $response;
	})->add(\APP\Auth::getStructureAccessCheck('lessons-and-learning-resources', 'update'));


	// Update/add custom completion date for specific resource assigned to user
	// Used for Apprentix where resource is assigned to issue
	$this->put('/update-due', function (Request $request, Response $response, $args) {
		$data = $request->getParsedBody();


		if (
			\APP\Auth::isManagerOf($data["user_id"]) ||
			\APP\Auth::isAdmin() ||
			\APP\Auth::accessAllLearners()
		) {

			$learning_result = \Models\LearningResult
				::where("id", $data["id"])
				->where("user_id", $data["user_id"])
				->where("refreshed", false)
				->with(["module" => function ($query) {
					$query
						->with(["modules" => function ($query) {
							$query
								->where('status', true)
							;
						}])
					;
				}])
				->first()
			;

			if (!$learning_result) {
				return $response
					->withStatus(404)
					->withHeader('Content-Type', 'text/html')
					->write('404 Not Found')
				;
			}

			$learning_result->completion_date_custom = \Carbon\Carbon::parse($data["due_at"]);

			if (isset($data['standard_start_at'])) {
				$learning_result->completion_date_custom_days = \Carbon\Carbon::parse($data['standard_start_at'])->diffInDays($learning_result->completion_date_custom, false);
			}

			$learning_result->saveWithoutEvents();

			// If resource is course, then update completion date to all relevant learning results
			if ($learning_result->module->is_course == 1) {
				foreach ($learning_result->module->modules as $key => $module) {

					$learning_result = \Models\LearningResult
						::where("user_id", $data["user_id"])
						->where('learning_module_id', $module->id)
						->where("refreshed", false)
						->first()
					;
					if ($learning_result) {
						$learning_result->completion_date_custom = \Carbon\Carbon::parse($data["due_at"]);

						if (isset($data['standard_start_at'])) {
							$learning_result->completion_date_custom_days = \Carbon\Carbon::parse($data['standard_start_at'])->diffInDays($learning_result->completion_date_custom, false);
						}

						$learning_result->saveWithoutEvents();
					}
				}
			}

			exit(0);

		} else {
			return $response
				->withStatus(403)
				->withHeader('Content-Type', 'text/html')
				->write('403 Forbidden')
			;
		}

		return
			$response;
	})->add(\APP\Auth::getStructureAccessCheck('lessons-and-learning-resources', 'update'));

	// remove custom completion date
	// Used in Apprentix
	$this->put('/remove-due', function (Request $request, Response $response, $args) {
		$data = $request->getParsedBody();

		if (
			\APP\Auth::isManagerOf($data["user_id"])
			|| \APP\Auth::isAdmin()
			|| \APP\Auth::accessAllLearners()
		) {

			$learning_result = \Models\LearningResult
				::where("id", '=', $data["id"])
				->where("user_id", "=", $data["user_id"])
				->first();
			if ($learning_result) {
				$learning_result->completion_date_custom = null;
				$learning_result->save();
			}

		} else {
			return
				$response
					->withStatus(500)
					->withHeader('Content-Type', 'text/html')
					->write('No access!');
		}

		return
			$response;
	})->add(\APP\Auth::getStructureAccessCheck('lessons-and-learning-resources', 'update'));


	// Get learning result
	$this->get('/{module_id:[0-9]+}/{user_id:[0-9]+}/{id:[0-9]+}', function (Request $request, Response $response, $args) {

		$hassAccessToOfflineTaskStatus = false;

		if (
			$args["user_id"] == \APP\Auth::getUserId() ||
			\APP\Auth::isManager() ||
			\APP\Auth::isAdmin() ||
			(
				\APP\Auth::accessAllLearners() &&
				\APP\Auth::isAdminInterface()
			)
		) {
			$learning_result = \Models\LearningResult
				::where("id", $args["id"])
				->with(["module" => function ($query) use ($args) {
					$query
						->where('status', true)
						->with(["type" => function ($query) {
							$query
								->with("LearningModuleTypeParameter");
						}])
						->with("competencies", "category", "FPCategory", "provider", "EvidenceType", "EventType")
						->with("files") // for use mostly by lessons
						->with(['modules' => function ($query) use ($args) {
							$query = \APP\Learning::simpleLearningResult($query, $args);
							$query = $query
								->orderBy('learning_course_modules.id', 'asc')
							;
						}])
						// Attach ID's of lessons this resource is part of and user is assigned to.
						->with(['Courses' => function ($query) use ($args) {
							$query = $query
								->select('learning_modules.id')
								->where('status', 1)
								->whereIn('learning_modules.id',
									\Models\UserLearningModule
										::select('learning_module_id')
										->where('user_learning_modules.user_id', $args["user_id"])
										->whereNull('user_learning_modules.deleted_at')
										->get()
								)
							;
						}])
						->with(['ScheduleLessonLinks' => function ($query) use ($args) {
							$query
								->select([
									'id',
									'schedule_id',
									'link_id'
								])
								->with(['Schedule' => function ($query) use ($args) {
									$query
										->select([
											'id',
											'name',
											'description',
											'category_id',
											'start_date',
											'approval',
											'cost',
											'enrole_any_learner'
										])
										->with(['ResourceLinks' => function ($query) use ($args) {
											$query
												->with(['resource' => function ($query) use ($args) {
													$query = \APP\Learning::simpleLearningResult($query, $args);
												}])
												// check if resource is self enrollable or is assigned to user!
												->whereHas('resource', function ($query) use ($args) {
													$query = $query
														->where('status', true)
														->where('visible_learner', true)
														->where(function ($query) use ($args) {
															$query = $query
																->where('self_enroll', true)
																->orWhere(function ($query) use ($args) {
																	$query = $query
																		->whereHas('Users', function ($query) use ($args) {
																			$query = $query
																				->where('users.id', $args["user_id"])
																			;
																		})
																	;
																})
															;
														})
													;
												})
											;
										}])
										->with(['UserLink' => function ($query) use ($args) {
											$query
												->where('link_id', $args["user_id"]);
										}])
										->with(['Files' => function($query) {
											$query
												->where('status', true)
												->with(['AddedBy' => function($query) {
													$query
														->select(
															'id',
															'fname',
															'lname',
															'role_id'
														)
														->with(['role' => function($query) {
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
														->with('role')
														->with(['role' => function ($query) {
															$query
																->select(
																	'id',
																	'name'
																);
														}]);
												}]);
										}])
										/*Forum Added by managers*/
										->with(['forum' => function ($query) {
											$query
												->where('visible_learner', true)
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
												}]);
										}])/*Forum ends here*/
									;
								}])
								->whereIn('schedule_id',
									\Models\ScheduleLink
										::select('schedule_id')
										->where('type', 'users')
										->where('link_id', $args["user_id"])
										->where('status', true)
										->get()
								)
								->where('status', true);
						}])
						->with(['prerequisites' => function ($query) use ($args) {
							$query = \APP\Learning::simpleLearningResult($query, $args);
						}])
						->with(['LearningModuleEvidences' => function ($query) use ($args) {
							$query
								->where('status', true)
								->where(function ($query) use ($args) {
									$query = $query
										->where('user_id', $args["user_id"])
										->orWhere('manager', 1)
									;
								})
								->orderBy('created_at', 'desc')
								->with(['user' => function ($query) {
									$query
										->select('id', 'fname', 'lname');
								}])
								->with(['AddedBy' => function ($query) {
									$query
										->select('id', 'fname', 'lname');
								}]);
						}])
						->with(['meetings' => function ($query) use ($args) {
							$query
								->where('user_id', $args["user_id"])
								->where('status', 1)
								->with(['createdby' => function ($query) {
									$query
										->select('id', 'fname', 'lname');
								}])
								->with(['approvedby' => function ($query) {
									$query
										->select('id', 'fname', 'lname');
								}]);
						}])
						->select(
							'learning_modules.*',
							DB::raw('
								CASE
									WHEN learning_modules.expiration_date < NOW()
									THEN 1
									ELSE 0
								END
								as expired
							')
						);
					if (
						\APP\Tools::getConfig('enableFeedback') &&
						\APP\Tools::getConfig('enableFeedbackList')
					) {
						$query
							->with(["feedback" => function ($query) {
								$query
									->where('status', true)
									->where('rating', '>', 0)
									->orderBy('created_at', 'desc')
									->whereHas('module', function ($query) {
										$query
											->where('learning_modules.type_id', '!=', 7);
									});
								if (!\APP\Auth::isLearner()) {
									$query
										->with(['user' => function ($query) {
											$query
												->select('id', 'fname', 'lname');
										}]);
								}
							}]);
					}
				}])
				->with(["user" => function ($query) {
					$query->select('id', 'fname', 'lname', 'e_signature');
				}])
				->with(['qalogs' =>function($query) {
					$query
						->select('*',DB::raw("DATE_FORMAT(created_at,'%d/%m/%Y') AS created_at_uk"))
					->with(["qaUser" => function ($query) {
						$query->select('id', 'fname', 'lname');
					}])->orderBy('quality_controls.created_at','desc')->get();

				}])
				->with(["refusedby" => function ($query) {
					$query->select('id', 'fname', 'lname');
				}])
				->with(["SignOffManagerBy" => function ($query) {
					$query->select('id', 'fname', 'lname', 'e_signature');
				}])
				->with(["comments" => function ($query) use ($args) {
					$query
						->where('created_for_user_id', $args["user_id"])
						->where('status', true)
						->with(["createdby" => function ($query) {
							$query
								->select("id", "fname", "lname", "role_id")
								->with('role');
						}]);
					if (\APP\Auth::isLearner()) {
						$query = $query
							->where('visible_learner', true);
					}
				}])
				->with(["QaCreatedBy" => function ($query) {
					$query->select('id', 'fname', 'lname');
				}])
				->where("learning_module_id", "=", $args["module_id"])
				->where("user_id", "=", $args["user_id"])
				//->where("refreshed", "=", 0)
					->select('*', DB::raw("DATE_FORMAT(learning_results.qa_date,'%d/%m/%Y') AS qa_created_at_uk"))
				->first();
dd($learning_result);
			if (
				$learning_result &&
				(
					\APP\Auth::isManagerOf($args["user_id"]) ||
					\APP\Auth::accessAllLearners()
				)
			) {
				$learning_result->managed_by = true;
			}

			/*
			if (isset($learning_result->module->prerequisites)) {
				$learning_result->module->prerequisites = \APP\Learning::augmentPrerequisitesLearningResult(
					$args["user_id"],
					$learning_result->module->prerequisites
				);
			}
			*/

			// In case of "Learning Lesson" where "modules" are populated, also want to know learning_result
			// TODO: assign "RequiringCourseName" property to modules, I don't know how, yet!
			/*
			if (isset($learning_result->module->modules)) {
				$learning_result->module->modules = \APP\Learning::augmentPrerequisitesLearningResult(
					$args["user_id"],
					$learning_result->module->modules
				);
			}
			*/

			if (isset($learning_result->module)) {
				$learning_result->module->required_modules = \APP\Learning::getCourseRequiredModules(
					$args["user_id"],
					$learning_result->module,
					true // course status must be enabled to get list.
				);
				foreach ($learning_result->module->required_modules as $result) {
					$result->setAppends(['safe_thumbnail']);
					// Also, check if this module can be launched, expensive
					$result = \APP\Learning::canResourceBeLaunched($result, $args);
				}

				foreach ($learning_result->module->prerequisites as $prerequisite) {
					$prerequisite->setAppends(['safe_thumbnail']);
					// Also, check if this module can be launched, expensive
					$prerequisite = \APP\Learning::canResourceBeLaunched($prerequisite, $args);
				}

				if (isset($learning_result->module->ScheduleLessonLinks)) {
					foreach ($learning_result->module->ScheduleLessonLinks as $key => $schedule_lesson_link) {
						foreach ($schedule_lesson_link->Schedule->ResourceLinks as $key => $resource_link) {
							if ($resource_link->resource) {
								$resource_link->resource->setAppends(['safe_thumbnail', 'safe_promo']);
								$resource_link->resource = \APP\Learning::canResourceBeLaunched($resource_link->resource, $args);
							}
						}
					}
				}
			}

			if (isset($learning_result->module->modules)) {
				foreach ($learning_result->module->modules as $module) {
					$module->setAppends(['safe_thumbnail']);

					$module->required_modules = \APP\Learning::getCourseRequiredModules(
						$args["user_id"],
						$module,
						true // course status must be enabled to get list.
					);

					// Also, check if this module can be launched, expensive
					$module = \APP\Learning::canResourceBeLaunched($module, $args);
				}
			}


			$hassAccessToOfflineTaskStatus = false;
			if (
				$learning_result &&
				isset($learning_result->module) &&
				isset($learning_result->module->type_id)
			) {
				$hassAccessToOfflineTaskStatus =
					$args["user_id"] == \APP\Auth::getUserId() &&
					in_array($learning_result->module->type_id, [5, 6]) &&
					\APP\Auth::checkStructureAccess(['misc-permissions-change-offline-task-status'], 'update');
			}

			if (
				$this->settings['licensing']['isApprentix'] &&
				\APP\Auth::isManager() &&
				$learning_result
			) {
				$favorite = \Models\Favorite
					::where('user_id', \APP\Auth::getUserId())
					->where('relation_id', $learning_result->id)
					->where('type', 'learning-qa')
					->count();
				$learning_result->qa_favorite = false;
				if ($favorite > 0) {
					$learning_result->qa_favorite = true;
				}
			}

			// Append safe thumbnail!
			if (isset($learning_result->module)) {
				$learning_result->module->setAppends(['safe_thumbnail', 'safe_promo']);
			}
		} else {
			$learning_result = null;
		}

		if ($learning_result) {
			\Models\TableExtension::returnAllFields('learning_results', $learning_result->id, $learning_result);
		}

		return $response
			->withHeader('Content-Type', 'application/json')
			->write(json_encode([
				"learning_result" => $learning_result,
				"hassAccessToOfflineTaskStatus" => $hassAccessToOfflineTaskStatus,
				"accessAllLearners" => \APP\Auth::accessAllLearners()
			]));


	})->add(\APP\Auth::getStructureAccessCheck(['trainee-learning-results', 'lessons-and-learning-resources'], 'select'));

	// Add scorm file to be used for learning resource
	$this->post('/module/uploadscormfile', function (Request $request, Response $response) {
		$data = $request->getParsedBody();

		if (isset($_FILES['zipfile'])) {
			$storage = new \Upload\Storage\FileSystem($this->settings["LMSTempPath"], true);
			$scormZipFile = new \Upload\File('zipfile', $storage);
			$scormZipFileName = preg_replace('/[^a-zA-Z0-9]/', '_', $data["name"]);
			$scormZipFile->setName($scormZipFileName);
			$scormZipFileName = $scormZipFile->getNameWithExtension();
			$fileTypeValidation = new \Upload\Validation\Mimetype(['application/zip', 'application/octet-stream']);
			$fileTypeValidation->setMessage("Invalid file type. You must upload a zip archive.");
			$scormZipFile->addValidations([
				$fileTypeValidation,
				new \Upload\Validation\Size('1024M')
			]);

			try {
				$scormZipFile->upload();

				// If module ID is not present, this is new resource installation, do not version it!
				if (!isset($data['module_id'])) {
					$response = $response
						->getBody()
						->write($scormZipFileName)
					;
				} else {
					$temp_file_zip_name = $scormZipFileName;
					$zipfile = $this->settings["LMSTempPath"] . $temp_file_zip_name;
					$zip = new \ZipArchive();

					if ($zip->open($zipfile) === TRUE ) {
						if ($zip->locateName('imsmanifest.xml') === false) {
							$error = "Invalid SCORM zip file. Can't find imsmanifest.xml";
							unlink($zipfile);

							return
								$response
									->withStatus(500)
									->withHeader('Content-Type', 'text/html')
									->write($error)
							;
						} else {
							//-------------IF all Validation Passed------------
							//---------Copy File to version directory and remove temp file-------------

							if (!isset($data['version'])) {
								return
									$response
										->withStatus(500)
										->withHeader('Content-Type', 'text/html')
										->write('Versioning failed!')
								;
							}

							$scorm_version_folder = $this->settings["LMSScormDataPath"] . $data['module_id'] . '/' . $data['version'];

							if (!is_dir($scorm_version_folder)) {
								$old = umask(0);
								if (!is_dir($this->settings["LMSScormDataPath"])) {
									mkdir($this->settings["LMSScormDataPath"], 0775);
								}
								if (!is_dir($this->settings["LMSScormDataPath"] . $data['module_id'])) {
									mkdir($this->settings["LMSScormDataPath"] . $data['module_id'], 0775);
								}
								mkdir($scorm_version_folder, 0775);
								umask($old);

							} else {
								\APP\Course::recursiveRemoveDirectory($scorm_version_folder);
							}

							$zip->extractTo($scorm_version_folder);
						}
						$response =
							$response
								->getBody()
								->write($scormZipFileName)
						;
					} else {
						return
							$response
								->withStatus(500)
								->withHeader('Content-Type', 'text/html')
								->write('Missing SCORM zip file')
							;
					}
				}
			} catch (\Upload\Exception\UploadException $e) {
				$errors = $scormZipFile->getErrors();
				return
					$response
						->withStatus(500)
						->withHeader('Content-Type', 'text/html')
						->write(implode("\n", $errors))
				;
			}
		} else {
			return
				$response
					->withStatus(500)
					->withHeader('Content-Type', 'text/html')
					->write('Missing SCORM zip file')
			;
		}

		return
			$response
		;
	})->add(\APP\Auth::getStructureAccessCheck('library-learning-resources-and-lessons', 'update'));

	//-----------IMPORTANT------------>>This functions is similar to above code optimisation needs clubing both the route actions
	// Add scorm file to be used for learning resource for resource version

	$this->post('/module/version/uploadscormfile', function (Request $request, Response $response) {
		$data = $request->getParsedBody();

		if (isset($_FILES['zipfile'])) {
			$storage = new \Upload\Storage\FileSystem($this->settings["LMSTempPath"], true);
			$scormZipFile = new \Upload\File('zipfile', $storage);
			$scormZipFileName = preg_replace('/[^a-zA-Z0-9]/', '_', $data["name"]);
			$scormZipFile->setName($scormZipFileName);
			$scormZipFileName = $scormZipFile->getNameWithExtension();
			$fileTypeValidation = new \Upload\Validation\Mimetype(['application/zip', 'application/octet-stream']);
			$fileTypeValidation->setMessage("Invalid file type. You must upload a zip archive.");
			$scormZipFile->addValidations([
				$fileTypeValidation,
				new \Upload\Validation\Size('1024M')
			]);
			try {

				$scormZipFile->upload();
				$temp_file_zip_name = $scormZipFileName;
				$zipfile = $this->settings["LMSTempPath"].$temp_file_zip_name;
				$zip = new \ZipArchive();
				if ($zip->open($zipfile) === TRUE )
				{
					if ($zip->locateName('imsmanifest.xml') === false){
						$error = "Invalid SCORM zip file. Can't find imsmanifest.xml";
						unlink($zipfile);
						return
						$response->withStatus(500)
							->withHeader('Content-Type', 'text/html')
							->write($error);
						}
						//-------------IF all Validation Passed------------
						else{
						//---------Copy File to version directory and remove temp file-------------
						$scorm_version_folder = $this->settings["LMSScormDataPath"].$data['module_id'].'/'.$data['version'];
						if(!is_dir($scorm_version_folder)) {
							$old = umask(0);
							mkdir($scorm_version_folder, 0775,true);
							umask($old);
						} else {
							array_map('unlink', glob("$scorm_version_folder/*.*"));
						}
						$zip->extractTo($scorm_version_folder);
						}
						$response = $response->getBody()->write(json_encode(['zip_file'=>$scormZipFileName, 'upload_file'=>true]));
				}
				else {
					//throw Exception("Missing SCORM zip file"); - does not work " Call to undefined function Exception()"
					return
						$response->withStatus(500)
							->withHeader('Content-Type', 'text/html')
							->write('Missing SCORM zip file');

				}



				//--------------------------------------------------

			} catch (\Upload\Exception\UploadException $e) {
				$errors = $scormZipFile->getErrors();
				return
					$response->withStatus(500)
						->withHeader('Content-Type', 'text/html')
						->write(implode("\n", $errors));
			}
		}
		else if (
			$data['cloned_version_zip_file'] ||
			file_exists($this->settings["LMSScormDataPath"].$data['module_id'] . "/moddata/scorm/1/imsmanifest.xml")
		) {
			$active_version_folder = $this->settings["LMSScormDataPath"].$data['module_id'] . '/' . $data['active_version'];

			// Real time updation ie When active data is updated and cloned to another vervsion
			if (
				file_exists($this->settings["LMSScormDataPath"].$data['module_id'] . "/moddata/scorm/1/imsmanifest.xml") &&
				$data['cloned_version'] == $data['active_version']
			) {
				if (file_exists($active_version_folder.'/'."imsmanifest.xml")) {
					\APP\Course::recursiveRemoveDirectory($active_version_folder);
					mkdir($active_version_folder, 0775,true);
					\APP\Tools::recurseCopy($this->settings["LMSScormDataPath"].$data['module_id'] . "/moddata/scorm/1/", $active_version_folder);
				}

			}
			if(!$data['cloned_version_zip_file']){
				$data['cloned_version_zip_file'] = preg_replace('/[^a-zA-Z0-9]/', '_', $data["name"]);
			}
			$scorm_version_cloned_folder = $this->settings["LMSScormDataPath"] . $data['module_id'] . '/' . $data['cloned_version'];
			$scorm_version_folder = $this->settings["LMSScormDataPath"].$data['module_id'].'/'.$data['version'];

			if(!is_dir($scorm_version_cloned_folder)) {
				$old = umask(0);
				mkdir($scorm_version_cloned_folder, 0775, true);
				umask($old);
			}

			if (!is_dir($scorm_version_folder)) {
				$old = umask(0);
				mkdir($scorm_version_folder, 0775);
				umask($old);
			}

			if (
				file_exists($scorm_version_cloned_folder.'/'.'imsmanifest.xml') &&
				$data['cloned_version'] != $data['version']
			) {
				\APP\Course::recursiveRemoveDirectory($scorm_version_folder);
				\APP\Tools::recurseCopy($scorm_version_cloned_folder.'/', $scorm_version_folder.'/');
				$response = $response->getBody()->write(json_encode(['zip_file'=>$data['cloned_version_zip_file'],'upload_file'=>false]));
			} else {
					if (
						file_exists($this->settings["LMSScormDataPath"] . $data['module_id'] . "/moddata/scorm/1/imsmanifest.xml")
					) {
						\APP\Course::recursiveRemoveDirectory($scorm_version_cloned_folder);
						mkdir($scorm_version_cloned_folder, 0775);
						\APP\Tools::recurseCopy($this->settings["LMSScormDataPath"].$data['module_id']. "/moddata/scorm/1/", $scorm_version_cloned_folder.'/');
						\APP\Course::recursiveRemoveDirectory($scorm_version_folder);
						mkdir($scorm_version_folder, 0775);
						\APP\Tools::recurseCopy($scorm_version_cloned_folder.'/', $scorm_version_folder.'/');
						$response = $response->getBody()->write(json_encode(['zip_file'=>$data['cloned_version_zip_file'],'upload_file'=>false]));

					} else {
						\APP\Course::recursiveRemoveDirectory($scorm_version_folder);
						\APP\Course::recursiveRemoveDirectory($scorm_version_cloned_folder);
						return
							$response
								->withStatus(500)
								->withHeader('Content-Type', 'text/html')
								->write("Version ".$data['cloned_version']." has no scorm data, please try uploading new lesson zip file.")
						;
					}
				}
			} else {
				return
					$response->withStatus(500)
						->withHeader('Content-Type', 'text/html')
						->write("Missing SCORM zip file or clone's master version has no scorm data");
		}

		return $response;
	})->add(\APP\Auth::getStructureAccessCheck('library-learning-resources-and-lessons', 'update'));

	$this->post('/module/uploadimage', function (Request $request, Response $response) {
		$data = $request->getParsedBody();
		if (isset($data["id"])) {
			$learningModule = \Models\LearningModule::find($data["id"]);
			if ($learningModule) {
				$response = \Models\LearningModule::uploadImages($response, $data, $_FILES, $this->settings, $learningModule);
			}
		}

		return $response;
	})->add(\APP\Auth::getStructureAccessCheck('library-learning-resources-and-lessons', 'update'));


	// just to delete promo or thumbnail image from module
	$this->put('/deleteimage/module/{id:[0-9]+}', function (Request $request, Response $response, array $args) {
		$data = $request->getParsedBody();
		$learning = \Models\LearningModule::find($args["id"]);
		\Models\LearningModule::deleteImage($learning, $data['field'], $this->settings);

		return $response;
	})->add(\APP\Auth::getStructureAccessCheck('library-learning-resources-and-lessons', 'update'));


	// Universal version of deleting images
	$this->delete('/course/{field: .+}/{course_id:[0-9]+}', function (Request $request, Response $response, array $args) {
		$learning = \Models\LearningModule::find($args["course_id"]);
		\Models\LearningModule::deleteImage($learning, $args['field'], $this->settings);
		\Models\LearningCourseModule::updateTracking($learning);
		return $response;
	})->add(\APP\Auth::getStructureAccessCheck('library-learning-resources-and-lessons', 'update'));


	// Will check all uploaded modules in scormdata directory, check for "xx/moddata/scorm/1/xml/information.xml" file and set flag("jackdaw") against resource, used in learning list to show/hide "jackdaw" button.
	$this->get('/module/compatibilitycheck', function (Request $request, Response $response, $args) {
		$report = [];
		//if (file_exists($this->settings["LMSScormDataPath"] . $learning->id . "/moddata/scorm/1/xml/information.xml")) {

		$directory = $this->settings["LMSScormDataPath"];
		$scanned_directory = array_diff(scandir($directory), array('..', '.'));

		foreach ($scanned_directory as $module_id) {

			// loop scromdata directory subdirectories
			if (is_dir($this->settings["LMSScormDataPath"] . $module_id)) {

				// if any of directories are numbers bigger than 0, must be modules
				if (intval($module_id) > 0) {
					// look into DB for existing module
					$learning = \Models\LearningModule::find(intval($module_id));
					if ($learning) {
						// check for XML file, if exists, update learning_modules table entry with jackdaw = 1, else jackadaw = 0
						if (file_exists($this->settings["LMSScormDataPath"] . $module_id . "/moddata/scorm/1/xml/information.xml")) {
							$report[] = [
								'id' => intval($module_id),
								'message' => 'Found module with ID: ' . $module_id . ' (' . $learning->name . '), compatible with Jackdaw, updating database.',
								'status' => 'found'
							];
							$learning->jackdaw = 1;
						} else {
							$report[] = [
								'id' => intval($module_id),
								'message' => 'Found module with ID: ' . $module_id . ' (' . $learning->name . '), not compatible with Jackdaw, updating database.',
								'status' => 'not found'
							];
							$learning->jackdaw = 0;
						}
						$learning->save();
					} else {
						// Directory is found, but no entry in database
						$report[] = [
							'id' => intval($module_id),
							'message' => 'Found module with ID: ' . $module_id . ', module is not found in database.',
							'status' => 'not found in db'
						];
					}
				}
			}
		}

		return
			$response
				->withHeader('Content-Type', 'application/json')
				->write(json_encode($report));

	})->add(\APP\Auth::getStructureAccessCheck('library-learning-resources-and-lessons', 'update'));

	//Adds latest e-learning updates to e-learning supplied by e-Learning WMB.
	// This updates the course folders from e-Learning WMB with all files minus the quizxml.xml imsmanifest.xml, intro.swf, /docs, /images, /swf, /video and /xml to <strong>src/public/api/data/Sample course</strong> and <strong>src/public/scormdata</strong>
	$this->post('/module/updateengine', function (Request $request, Response $response, $args) {
		$data = $request->getParsedBody();

		if (isset($_FILES['enginefile'])) {
			$storage = new \Upload\Storage\FileSystem($this->settings["LMSTempPath"], true);
			$engineZipFile = new \Upload\File('enginefile', $storage);
			$engineZipFile->setName('Publish');

			$engineZipFileName = $engineZipFile->getNameWithExtension();
			$fileTypeValidation = new \Upload\Validation\Mimetype(['application/zip', 'application/octet-stream']);
			$fileTypeValidation->setMessage("Invalid file type. You must upload a zip archive.");
			$engineZipFile->addValidations([
				$fileTypeValidation,
				new \Upload\Validation\Size('1024M')
			]);
			try {
				$engineZipFile->upload();
				// Great success, now unzip and copy over all installed resources and sample
				$zip = new ZipArchive;
				if ($zip->open($this->settings["LMSTempPath"] . 'Publish.zip') === TRUE) {
					$zip->extractTo($this->settings["LMSTempPath"] . 'engineUpdate');
					$zip->close();
					$engineDir = $this->settings["LMSTempPath"] . 'engineUpdate/';

					// delete zip file
					unlink($this->settings["LMSTempPath"] . 'Publish.zip');

					// delete files/filders that should not be overwritten from extracted archive, if they exist
					// quizxml.xml imsmanifest.xml, intro.swf, /docs, /images, /swf, /video and /xml
					$delete_files = ['quizxml.xml', 'quizXML.xml', 'imsmanifest.xml', 'intro.swf'];
					$delete_directories = ['docs', 'images', 'swf', 'video', 'xml'];

					foreach ($delete_files as $key => $delete_file) {
						if (is_file($engineDir . $delete_file)) {
							unlink($engineDir . $delete_file);
						}
					}

					foreach ($delete_directories as $key => $delete_directory) {
						if (is_dir($engineDir . $delete_directory)) {
							\APP\Tools::delDirTree($engineDir . $delete_directory);
						}
					}

					//replace sample course and scormdata resources with extracrted archive
					\APP\Tools::recurseCopy($engineDir, $this->settings["LMSPublicPath"] . 'api/data/Sample course');

					$learning_resources = array_diff(
						scandir($this->settings["LMSPublicPath"] . 'scormdata'),
						array('..', '.')
					);
					foreach ($learning_resources as $key => $learning_resources) {
						if (file_exists($this->settings["LMSPublicPath"] . 'scormdata/' . $learning_resources . "/moddata/scorm/1/xml/information.xml")) {
							\APP\Tools::recurseCopy($engineDir, $this->settings["LMSPublicPath"] . 'scormdata/' . $learning_resources . "/moddata/scorm/1");
						}
					}

					// delete extracted archive
					if (is_dir($engineDir)) {
						\APP\Tools::delDirTree($engineDir);
					}

					$response = $response->getBody()->write($engineZipFileName);
				} else {
					return
						$response->withStatus(500)
							->withHeader('Content-Type', 'text/html')
							->write('Can\'t open ZIP archieve!');
				}
			} catch (\Upload\Exception\UploadException $e) {
				$errors = $engineZipFile->getErrors();
				return
					$response->withStatus(500)
						->withHeader('Content-Type', 'text/html')
						->write(implode("\n", $errors));
			}
		} else {
			return
				$response->withStatus(500)
					->withHeader('Content-Type', 'text/html')
					->write('Missing SCORM zip file');
		}

		return $response;
	})->add(\APP\Auth::getStructureAccessCheck('library-learning-resources-and-lessons', 'update'));

	//Update room files to all e-learning types
	$this->post('/module/updateallengine', function (Request $request, Response $response, $args) {
		$data = $request->getParsedBody();
      $installedResources = \Models\LearningModule
			::where(function ($query) {
				$query
					->where('jackdaw', 1)
					->orWhere('jackdaw_resource', true)
				;
			})
			->whereIn( 'type_id',
				\Models\LearningModuleType
					::select('id')
					->where('slug', 'e_learning')
					->get()
			)
			->get()
		;
		$updated_resources = [];
		$failed_resources = [];
		foreach ($installedResources as $key => $installedResource) {
			// ignoring errors for update all
			try {
				// Update room files, force overwrite, if update succeed, add name to updated list.
				if (\APP\Jackdaw::updateRoomFiles($this->settings["LMSPublicPath"], $installedResource->id, true, true)) {
					$updated_resources[] = $installedResource->name;
				}
			} catch (Exception $e) {
				$failed_resources[] = $installedResource->name;
			}
		}
		return
			$response
				->withHeader('Content-Type', 'application/json')
				->write(
					json_encode(
						[
							"updated_resources" => $updated_resources,
							"failed_resources" => $failed_resources
						]
					)
				);
	})->add(\APP\Auth::getStructureAccessCheck('library-learning-resources-and-lessons', 'update'));


	// Application will loop all e-learning courses supplied by e-Learning WMB and copy over client branding. Note branded files need to be first copied to the installation's /src/public/api/data/Sample course folder.
	// Files to copy are intro.swf and /images/thumbs/1.jpg
	$this->post('/updatebranding', function (Request $request, Response $response, $args) {
		$data = $request->getParsedBody();
		//Check button style
		if (isset($data['buttonStyle'])) {
			\APP\Tools::recurseCopy($this->settings["LMSPublicPath"] . 'images/default_buttons/' . $data['buttonStyle'], $this->settings["LMSPublicPath"] . 'api/data/Sample course/assets/button');
			\APP\Tools::updateConfig('buttonStyle', $data['buttonStyle']);
		}

		// Upload intro.swf if provided
		if (isset($_FILES['branding-intro'])) {
			$storage = new \Upload\Storage\FileSystem($this->settings["LMSPublicPath"] . 'api/data/Sample course/', true);
			$bandingIntro = new \Upload\File('branding-intro', $storage);
			$bandingIntro->setName('intro');

			$bandingIntroValidation = new \Upload\Validation\Mimetype(['application/x-shockwave-flash', 'application/octet-stream']);
			$bandingIntroValidation->setMessage("Invalid file type: '" . $bandingIntro->getMimetype() . "'!");
			$bandingIntro->addValidations([
				$bandingIntroValidation,
				new \Upload\Validation\Size('14M')
			]);
			try {
				$bandingIntro->upload();
			} catch (\Upload\Exception\UploadException $e) {
				$errors = $bandingIntro->getErrors();
				return
					$response->withStatus(500)
						->withHeader('Content-Type', 'text/html')
						->write(implode("\n", $errors));
			}
		}

		// Upload /images/thumbs/1.jpg if provided
		if (isset($_FILES['branding-thumb'])) {
			$storage = new \Upload\Storage\FileSystem($this->settings["LMSPublicPath"] . 'api/data/Sample course/images/thumbs/', true);
			$bandingThumb = new \Upload\File('branding-thumb', $storage);
			$bandingThumb->setName('1');

			$bandingThumbValidation = new \Upload\Validation\Mimetype(['image/jpeg', 'image/jpg']);
			$bandingThumbValidation->setMessage("Invalid file type. You must upload a jpg file.");
			$bandingThumb->addValidations([
				$bandingThumbValidation,
				new \Upload\Validation\Size('4M')
			]);
			try {
				$bandingThumb->upload();
			} catch (\Upload\Exception\UploadException $e) {
				$errors = $bandingThumb->getErrors();
				return
					$response->withStatus(500)
						->withHeader('Content-Type', 'text/html')
						->write(implode("\n", $errors));
			}
		}

		// Upload site logo if provided
		if (isset($_FILES['branding-logo'])) {
			$storage = new \Upload\Storage\FileSystem($this->settings["LMSPublicPath"] . 'images/licensing/', true);
			$bandingLogo = new \Upload\File('branding-logo', $storage);

			$bandingLogo->setName($this->settings["licensing"]['version'] . 'logo');

			echo $this->settings["licensing"]['version'] . 'logo';

			$bandingLogoValidation = new \Upload\Validation\Mimetype(['image/png']);
			$bandingLogoValidation->setMessage("Invalid file type. You must upload a png file.");
			$bandingLogo->addValidations([
				$bandingLogoValidation,
				new \Upload\Validation\Size('2M')
			]);
			try {
				$bandingLogo->upload();
			} catch (\Upload\Exception\UploadException $e) {
				$errors = $bandingLogo->getErrors();
				return
					$response->withStatus(500)
						->withHeader('Content-Type', 'text/html')
						->write(implode("\n", $errors));
			}
		}

		// Upload site log-in background if provided
		if (isset($_FILES['branding-bg'])) {
			$storage = new \Upload\Storage\FileSystem($this->settings["LMSPublicPath"] . 'images/licensing/', true);
			$bandingBg = new \Upload\File('branding-bg', $storage);
			$bandingBg->setName($this->settings["licensing"]['version'] . 'bg');

			$bandingBgValidation = new \Upload\Validation\Mimetype(['image/jpeg', 'image/jpg']);
			$bandingBgValidation->setMessage("Invalid file type. You must upload a jpg file.");
			$bandingBg->addValidations([
				$bandingBgValidation,
				new \Upload\Validation\Size('2M')
			]);
			try {
				$bandingBg->upload();
				// Make sure file ends with .jpg
				rename($this->settings["LMSPublicPath"] . 'images/licensing/' . $bandingBg->getNameWithExtension(), $this->settings["LMSPublicPath"] . 'images/licensing/' . $bandingBg->getName() . '.jpg');

			} catch (\Upload\Exception\UploadException $e) {
				$errors = $bandingBg->getErrors();
				return
					$response->withStatus(500)
						->withHeader('Content-Type', 'text/html')
						->write(implode("\n", $errors));
			}
		}

		if (isset($data['updateType']) && $data['updateType'] == 'branding') {
			// pull all directories in scormdata
			$scanned_directory = array_diff(
				scandir($this->settings["LMSPublicPath"] . 'scormdata'),
				array('..', '.')
			);
			foreach ($scanned_directory as $key => $resource) {
				// if intro.swf exists then it is correct resource to be used for branding
				if (is_file($this->settings["LMSPublicPath"] . 'scormdata/' . $resource . '/moddata/scorm/1/intro.swf')) {
					copy($this->settings["LMSPublicPath"] . 'api/data/Sample course/intro.swf', $this->settings["LMSPublicPath"] . 'scormdata/' . $resource . '/moddata/scorm/1/intro.swf');
				}
				if (is_file($this->settings["LMSPublicPath"] . 'scormdata/' . $resource . '/moddata/scorm/1/images/thumbs/1.jpg')) {
					copy($this->settings["LMSPublicPath"] . 'api/data/Sample course/images/thumbs/1.jpg', $this->settings["LMSPublicPath"] . 'scormdata/' . $resource . '/moddata/scorm/1/images/thumbs/1.jpg');
				}
			}
		}


	})->add(\APP\Auth::getStructureAccessCheck('library-learning-resources-and-lessons', 'update'));

	// Updates all installed module images from stock, if stock exists.
	$this->get('/module/updateimages', function (Request $request, Response $response, $args) {
		$learning = \Models\LearningModule::
		select('id', 'name', 'thumbnail', 'promo_image', 'type_id')
			->get();;

		$report = [];
		$types = [
			'e-learning.jpg',
			'youtube.jpg',
			'webpage.jpg',
			'classroom.jpg',
			'bookcddvd.jpg',
			'on-the-job.jpg',
			'upload.jpg',
			'blog-entry.jpg',
			'reflective-log.jpg',
			'zoom.jpg',
		];
		$directory = $this->settings["AvailableModulesLocation"];
		if (is_dir($directory)) {

			//scan module stock directory
			$scanned_directory = array_diff(scandir($directory), array('..', '.'));

			foreach ($learning as $key => $value) {

				// If installed learning_module name matches directory name in stock
				if (in_array($value->name, $scanned_directory)) {

					// delete promo image, if exists
					if (is_file($this->settings["LMSScormDataPath"] . $value->id . '/moddata/scorm/1/images/promo.jpg')) {
						unlink($this->settings["LMSScormDataPath"] . $value->id . '/moddata/scorm/1/images/promo.jpg');
					}

					// delete thumbnail image if exists
					if (is_file($this->settings["LMSScormDataPath"] . $value->id . '/moddata/scorm/1/images/thumb.jpg')) {
						unlink($this->settings["LMSScormDataPath"] . $value->id . '/moddata/scorm/1/images/thumb.jpg');
					}

					// update promo/thumb image, if source promo does not exists, copy default
					if (is_dir($this->settings["LMSScormDataPath"] . $value->id . '/moddata/scorm/1/images')) {
						if (!is_file($this->settings["AvailableModulesLocation"] . $value->name . "/images/promo.jpg") || !copy($this->settings["AvailableModulesLocation"] . $value->name . "/images/promo.jpg", $this->settings["LMSScormDataPath"] . $value->id . '/moddata/scorm/1/images/promo.jpg')) {
							copy($this->settings["LMSDefaultTypePath"] . $types[$value->type_id - 1], $this->settings["LMSScormDataPath"] . $value->id . '/moddata/scorm/1/images/promo.jpg');
						}

						if (!is_file($this->settings["AvailableModulesLocation"] . $value->name . "/images/thumb.jpg") || !copy($this->settings["AvailableModulesLocation"] . $value->name . "/images/thumb.jpg", $this->settings["LMSScormDataPath"] . $value->id . '/moddata/scorm/1/images/thumb.jpg')) {
							copy($this->settings["LMSDefaultTypePath"] . $types[$value->type_id - 1], $this->settings["LMSScormDataPath"] . $value->id . '/moddata/scorm/1/images/thumb.jpg');
						}

						$report[] = [
							'id' => $value->id,
							'name' => $value->name
						];
					}

					////echo $value->id . ' : ' . (is_dir($this->settings["LMSScormDataPath"] . $value->id . '/moddata/scorm/1/images') ? 'true' : 'false') . ' - ' . $this->settings["LMSScormDataPath"] . $value->id . '/moddata/scorm/1/images' . "\n";
				} else {
					// module exists, but image does not, copy default type to module.
					////echo $value->id . ' $value->type_id > 0 : ' . ($value->type_id > 0 ? 'true' : 'false') . "\n";
					if (
						$value->type_id > 0 &&
						isset($types[$value->type_id - 1]) &&
						is_file($this->settings["LMSDefaultTypePath"] . $types[$value->type_id - 1]) &&
						is_dir($this->settings["LMSScormDataPath"] . $value->id . '/moddata/scorm/1/images')
					) {
						copy($this->settings["LMSDefaultTypePath"] . $types[$value->type_id - 1], $this->settings["LMSScormDataPath"] . $value->id . '/moddata/scorm/1/images/thumb.jpg');
						copy($this->settings["LMSDefaultTypePath"] . $types[$value->type_id - 1], $this->settings["LMSScormDataPath"] . $value->id . '/moddata/scorm/1/images/promo.jpg');
						// send back updated modules
						$report[] = [
							'id' => $value->id,
							'name' => $value->name
						];
					}
				}
			}
		}


		return
			$response
				->withHeader('Content-Type', 'application/json')
				->write(json_encode($report));

	})->add(\APP\Auth::getStructureAccessCheck('library-learning-resources-and-lessons', 'update'));

	// updates specified learning resource!!
	$this->put('/module/{id:[0-9]+}', function (Request $request, Response $response, array $args) {

		$data = $request->getParsedBody();
		if(array_key_exists("review_status",$data))
		{
			$data['review_date']=  \Carbon\Carbon::now()->addDays($data['review_interval']);
			unset($data['review_status']);
		}
		if($data['review'])
		{
			$data['review_date']= \Carbon\Carbon::now()->addDays($data['review_interval']);
		}else
		{
			$data['review_date']='';
		}
		$learning = \Models\LearningModule::find($args["id"]);


		/*
			Need to create logic where material field is checked for sessions and compared with ones being submitted.
			Ones that are being deleted, need to be removed from session table too
			Logic would loop material saved in DB and submitted one, compare and ones in DB and not submitted, will be deleted.
		*/
		if (
			$learning->material &&
			isset($learning->material->sessions)
		) {
			foreach ($learning->material->sessions as $dbKey => $dbValue) {
				$delete_key = $dbValue->session_uid;
				foreach ($data["material"]["sessions"] as $postKey => $postValue) {
					if ($delete_key == $postValue['session_uid']) {
						$delete_key = '';
					}
				}
				if ($delete_key) {
					//get all entries from sessions that have key of deleted session
					$l_session = \Models\LearningSession::where("session_uid", "=", $delete_key)->get();

					$e = \APP\Email::createMailer("Booking Cancellation");
					if ($e) {
						$vars = [
							"BOOKING_SESSION" => $data["name"]
						];

						// for each to be deleted session get the user and send email to him.
						foreach ($l_session as $session) {
							$l_session_user = Models\User::find($session->user_id);
							$e->sendToUser($l_session_user, $vars);
						}
					}

					// Why not just delete?
					$l_session = \Models\LearningSession::where("session_uid", "=", $delete_key)->delete();
				}
			}
		}
		// EOF session check

		$data["refresh_period"] = isset($data["refresh_period"]) ? intval($data["refresh_period"]) : 0;
		$data["refresh_repeat"] = isset($data["refresh_repeat"]) ? intval($data["refresh_repeat"]) : null;
		$data["due_after_period"] = isset($data["due_after_period"]) ? intval($data["due_after_period"]) : 0;

		if (!isset($data['level'])) {
			$data['level'] = 'NULL';
		}

		$fields = [
			"code", "name", "category_id", "keywords", "self_enroll", "approval", "require_management_signoff",
			"company_id", "refresh", "refresh_period", "refresh_repeat", "refresh_custom_email", "refresh_custom_email_subject", "refresh_custom_email_body", "due_after_period", "description", "type_id",
			"is_course", "language", "cost", "duration_hours", "duration_minutes", "duration_change", "provider_id", "level", "do_prerequisite", "is_skillscan", "track_progress", "print_certificate", "material", "description", "accreditation_description", "evidence_type_id", "f_p_category_id", "responsible_user", "expiration_date", "player_width", "player_height", "event_type_id", "visible_learner",
			"review", "review_interval", "review_date", "badge"
		];

		// if "min_passing_percentage" is provided, update the value in the courses respective information.xml file (<screens><screen><Quizes><settings><PassMark>)
		if (
			isset($data['material']) &&
			$data['material'] &&
			isset($data['material']['min_passing_percentage'])
		) {
			if (file_exists($this->settings["LMSScormDataPath"] . $learning->id . "/moddata/scorm/1/xml/information.xml")) {
				$xml = simplexml_load_file($this->settings["LMSScormDataPath"] . $learning->id . "/moddata/scorm/1/xml/information.xml");
				foreach ($xml as $key => $screen) {
					if (
						isset($screen->Quizes)
						&& isset($screen->Quizes->settings)
						&& isset($screen->Quizes->settings->PassMark)
					) {
						$screen->Quizes->settings->PassMark = ($data['material']['min_passing_percentage'] - 1);
						$xml->asXml($this->settings["LMSScormDataPath"] . $learning->id . "/moddata/scorm/1/xml/information.xml");
					}

				}
			}
		}

		\APP\Tools::setObjectFields($learning, $fields, $data);

		if (empty($data['f_p_category_id'])) {
			$learning->f_p_category_id = null;
		}
		if (empty($data['responsible_user'])) {
			$learning->responsible_user = null;
		}
		if (empty($data['expiration_date'])) {
			$learning->expiration_date = null;
		}
		if (empty($data['player_width'])) {
			$learning->player_width = null;
		}
		if (empty($data['player_height'])) {
			$learning->player_height = null;
		}

		//version Check and update
		// version update
		if(!empty($data['id'])){
			$versionUpdate = \Models\LearningModuleVersion::versionSync("update",$data,$learning);
			$learning->version = $data['version'];
		}


		$learning->save();

		$competencies = [];
		if (isset($data["competencies"])) {
			foreach ($data["competencies"] as $c) {
				$competencies[$c["id"]] = [
					"points" => $c["points"],
				];
			}
		}
		$learning->competencies()->sync($competencies);
        if($learning->self_enroll)
        {
            $companyModuleEnrollments=[];
            if(isset($data['companies']))
            {
                foreach ($data['companies'] as $c)
                {
                    $companyModuleEnrollments[] = $c['id'];
                }
            }
            $learning->companies()->sync($companyModuleEnrollments);
        }else
        {
            $learning->companies()->sync([]);
        }
		$prerequisites = isset($data["prerequisites"]) ? \APP\Tools::getObjectIds($data["prerequisites"]) : [];
		$learning->prerequisites()->sync($prerequisites);

		// Refresh period has changed and person updating resource opted in to update all due_at dates in learning_results that are completed and contain due_at date.
		if (
			isset($data["update_due_at"]) &&
			$data["update_due_at"]
		) {
			// get configuration for how to calculate due_at
			$refreshCompletedAt = \Models\Configuration
				::where('key', 'refreshCompletedAt')
				->first();
			if ($refreshCompletedAt->value == '1') {
				$refresh_base = 'completed_at';
			} else {
				$refresh_base = 'created_at';
			}

			// get all afflicted learning results
			$learning_results = \Models\LearningResult
				::where('learning_module_id', $learning->id)
				->where('completion_status', 'completed')
				->where('refreshed', 0)
				->get()
			;

			// loop learning results and update them accordingly
			foreach ($learning_results as $key => $learning_result) {
				$refresh_base_date = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $learning_result->$refresh_base);
				$learning_result->due_at = $refresh_base_date->copy()->addDays(
					$learning->refresh_period + $learning->due_after_period
				);
				$learning_result->save();
			}


			// Also update due at for active learning results, that are not assigned to standard for user!
			$learning_results_progress = \Models\LearningResult
				::where('learning_module_id', $learning->id)
				->where('completion_status', '!=', 'completed')
				->where('refreshed', 0)
			;

			if ($this->settings['licensing']['isApprentix']) {
				$learning_results_progress = $learning_results_progress
					->whereNotIn('learning_results.user_id',
						\Models\ApprenticeshipStandardUser
							::select('apprenticeship_standards_users.user_id')
							->whereIn('standard_id',
								\Models\ApprenticeshipStandard
									::select('id')
									->where('status', true)
									->whereIn('id',
										\Models\ApprenticeshipIssueCategories
											::select('standard_id')
											->where('status', true)
											->whereIn('id',
												\Models\ApprenticeshipIssues
													::select('issue_category_id')
													->where('status', true)
													->where(function ($query) use ($learning) {
														$query
															->whereIn('id',
																\Models\ApprenticeshipIssuesLearningModules
																	::select('apprenticeship_issues_id')
																	->where('learning_modules_id', $learning->id)
																	->get()
															)
															->orWhereIn('id',
																\Models\ApprenticeshipIssuesUserLearningModules
																	::select('apprenticeship_issues_id')
																	->where('learning_modules_id', $learning->id)
																	->get()
															)
															->orWhereIn('id',
																\Models\ApprenticeshipIssuesEvidence
																	::select('apprenticeship_issues_id')
																	->where('learning_modules_id', $learning->id)
																	->get()
															);
													})
													->get()
											)
											->get()
									)
									->get()
							)
							->get()
					);
			}

			$learning_results_progress = $learning_results_progress
				->get()
			;

			// loop learning results and update them accordingly
			foreach ($learning_results_progress as $key => $learning_result) {
				$created_at = \Carbon\Carbon::parse($learning_result->created_at);
				$learning_result->due_at = $created_at->copy()->addDays($learning->due_after_period);
				$learning_result->save();
			}
		}


		// Resource zip file is provided
		if (
			$learning->type_id == 1 &&
			isset($data["material"]["zip_file"]) &&
			(
				!empty($data["update_scorm_file"]) ||
				$data["active_version"] != $data["version"]
			)
		) {
			$course = \APP\Course::get($learning);
			if (
				$data["active_version"] != $data["version"] &&
				isset($data["material"]["zip_file"])
			) {
				$scorm_file =  $this->settings["LMSTempPath"] . $data["material"]["zip_file"];
			}
			if ($data["update_scorm_file"]) {
				$scorm_file =  $this->settings["LMSTempPath"] . $data["material"]["zip_file"];
			} else {
				// Why? But I guess there is reason.
				$scorm_file = $this->settings["LMSTempPath"] . "dummy1233212.zip";
			}

			try {
				$course->deleteScormSetup();
				$course->setupScorm(
					$scorm_file,
					$this->settings["LMSScormDataPath"],
					$data
				);

				// Great success with setting up new resource file.

				// If "Fix SCORM track data" is checked, replace scoid with new one, check passmark and update status.
				if (
					isset($data['updateScormTrackWithScoid']) &&
					$data['updateScormTrackWithScoid']
				) {
					// Replace scoid
					$max_scoid = \Models\Scorm\Sco::where("scorm", "=", $learning->id)->max("id");
					$scoesTrack = \Models\Scorm\Track
						::where('scormid', '=', $learning->id)
						->update(
							[
								'scoid' => $max_scoid
							]
						)
					;


					//-----------------------
					// Look into learning results, if passing percentage is bigger than set in course, set to completed, add scorm track records to preserve completin state.
					$incomplete_learning_results = \Models\LearningResult
						::where('learning_module_id', $learning->id)
						->where('completion_status', '!=', 'completed')
						->get()
					;

					foreach ($incomplete_learning_results as $key => $incomplete_learning_result) {
						if ($incomplete_learning_result->score >= $data['material']['min_passing_percentage']) {

							$update_learning_result = \Models\LearningResult::find($incomplete_learning_result->id);
							$update_learning_result->completion_status = 'completed';
							$update_learning_result->completed_at = $update_learning_result->updated_at;
							$update_learning_result->save();

							// Update \Models\Scorm\Track "cmi.core.lesson_status" as "passed"

							$updateScormTrackUser = \Models\Scorm\Track::where('scormid', '=', $learning->id)
								->where('userid', '=', $incomplete_learning_result->user_id)
								->where('element', '=', 'cmi.core.lesson_status')
								->update(
									[
										'value' => 'passed'
									]
								)
							;
						}
					}
				}


				// Check if this module can be edited by Jackdaw, if yes, set flag in DB and that will be used in listings pages to show/hide jackdaw button
				if (file_exists($this->settings["LMSScormDataPath"] . $learning->id . "/moddata/scorm/1/xml/information.xml")) {
					$learning->jackdaw = 1;
				} else {
					$learning->jackdaw = 0;
				}
				$learning->save();

			} catch (\APP\ScormException $e) {
				return
					$response
						->withStatus(500)
						->withHeader('Content-Type', 'text/html')
						->write(implode("\n", ["Scorm error." . $e->getMessage()]))
				;
			}
		}

		$asm = new \APP\Assessment();

		//check if the module is an e-learning one and whether it has assessments
		if ($learning->type_id == 1 && $asm->hasAssessment($learning->id)) {
			//check if assessment categories data are submited. If not, clear all categories
			$asm_cats = isset($data["assessment_categories"]) ? $data["assessment_categories"] : [];
			$asm->updateAssessmentCategories($learning->id, $asm_cats);
		}

		return $response;

	})->add(\APP\Auth::getStructureAccessCheck('library-learning-resources-and-lessons', 'update'));

	$this->get('/assessment_questions/{id:[0-9]+}', function (Request $request, Response $response, array $args) {

		$result = [];

		$asm_questions = \Models\Assessment\Question
			::where("course_id", "=", $args["id"])
			->get();

		foreach ($asm_questions as $asm_question) {
			$result[] = $asm_question;
		}

		return
			$response
				->withHeader('Content-Type', 'application/json')
				->write(json_encode($result));

	})->add(\APP\Auth::getStructureAccessCheck('library-learning-resources-and-lessons', 'select'));

	// Adds new learning resource
	$this->post('/module/new', function (Request $request, Response $response) {

		$data = $request->getParsedBody();
		$availableModuleId = isset($data["availableModuleId"]) ? $data["availableModuleId"] : 0;
		$data["refresh_period"] = isset($data["refresh_period"]) ? $data["refresh_period"] : 0;
		$data["refresh_repeat"] = isset($data["refresh_repeat"]) ? $data["refresh_repeat"] : null;

		$learning = new \Models\LearningModule;

		if (!isset($data['level'])) {
			$data['level'] = 'NULL';
		}
		if($data['review'])
		{
		$data['review_date']=  \Carbon\Carbon::now()->addDays($data['review_interval']);
		}
		else
		{
			$data['review_date']=null;
		}
		$fields = [
			"code", "name", "category_id", "keywords", "self_enroll", "approval", "require_management_signoff",
			"company_id", "refresh", "refresh_period", "refresh_repeat", "refresh_custom_email", "refresh_custom_email_subject", "refresh_custom_email_body", "description", "type_id", "is_course", "language", "cost", "duration_hours", "duration_minutes", "duration_change", "provider_id", "level", "do_prerequisite", "material", "accreditation_description", "evidence_type_id", "is_skillscan", "track_progress", "print_certificate", "f_p_category_id", "responsible_user", "expiration_date", "player_width", "player_height", "event_type_id", "visible_learner",
			"review","review_interval","review_date","badge"
		];

		\APP\Tools::setObjectFields($learning, $fields, $data);

		$learning->id = \APP\Course::getNewCourseId(
			$learning->name,
			$this->settings["FixedCourseIds"],
			$this->settings["CourseIdStart"]
		);
		$learning->status = 1;
		$learning->created_by = \APP\Auth::getUserId();
		$learning->save();

		$competencies = [];
		if (isset($data["competencies"])) {
			foreach ($data["competencies"] as $c) {
				$competencies[$c["id"]] = [
					"points" => $c["points"],
				];
			}
		}

		$learning->competencies()->sync($competencies);
        if($learning->self_enroll)
        {
            $companyModuleEnrollments=[];
            if(isset($data['companies']))
            {
                foreach ($data['companies'] as $c)
                {
                    $companyModuleEnrollments[] = $c['id'];
                }
            }
            $learning->companies()->sync($companyModuleEnrollments);
        }else
        {
            $learning->companies()->sync([]);
        }
		$prerequisites = isset($data["prerequisites"]) ? $data["prerequisites"] : [];
		$learning->prerequisites()->sync(\APP\Tools::getObjectIds($prerequisites));

		if (
			$learning->type_id == 1 &&
			isset($data["material"]["zip_file"])
		) {
			$course = \APP\Course::get($learning);
			$course->deleteScormSetup();

			try {

				$course->setupScorm(
					$this->settings["LMSTempPath"] . $data["material"]["zip_file"],
					$this->settings["LMSScormDataPath"]
				);

				// Check if this module can be edited by Jackdaw, if yes, set flag in DB and that will be used in listings pages to show/hide jackdaw button
				if (file_exists($this->settings["LMSScormDataPath"] . $learning->id . "/moddata/scorm/1/xml/information.xml")) {
					$learning->jackdaw = 1;
					$learning->save();
				}

			} catch (\APP\ScormException $e) {
				$learning->delete();
				return
					$response
						->withStatus(500)
						->withHeader('Content-Type', 'text/html')
						->write(implode("\n", ["Scorm error." . $e->getMessage()]))
				;
			}
		}

		if (
			$learning->type_id == 1 &&
			$availableModuleId > 0
		) {
			$course = \APP\Course::get($learning);
			$available_module = \Models\AvailableModule::find($availableModuleId);

			if ($available_module) {
				try {
					$source_location = $this->settings["AvailableModulesLocation"] . "/" . $available_module->name;
					$course->copyAvailableModule(
						$source_location,
						$this->settings["LMSScormDataPath"]
					);

				} catch (\APP\ScormException $e) {
					$course->deleteScormSetup();
					$learning->delete();
					return
						$response
							->withStatus(500)
							->withHeader('Content-Type', 'text/html')
							->write(implode("\n", ["Scorm error." . $e->getMessage()]))
					;
				}
			}
		}

		// check if installed resource is jackdaw compatible, might be better solutioin, someday
		if (
			$learning->type_id == 1 &&
			file_exists($this->settings["LMSScormDataPath"] . $learning->id . "/moddata/scorm/1/xml/information.xml")
		) {
			$learning->jackdaw = 1;
			$learning->save();

			// Overwrite room filews with newest version
			\APP\Jackdaw::updateRoomFiles($this->settings["LMSPublicPath"], $learning->id);
		}


		if (
			isset($data['material']) &&
			$data['material'] &&
			isset($data['material']['min_passing_percentage'])
		) {
			if (file_exists($this->settings["LMSScormDataPath"] . $learning->id . "/moddata/scorm/1/xml/information.xml")) {
				$xml = simplexml_load_file($this->settings["LMSScormDataPath"] . $learning->id . "/moddata/scorm/1/xml/information.xml");
				foreach ($xml as $key => $screen) {
					if (
						isset($screen->Quizes)
						&& isset($screen->Quizes->settings)
						&& isset($screen->Quizes->settings->PassMark)
					) {
						$screen->Quizes->settings->PassMark = ($data['material']['min_passing_percentage'] - 1);
						$xml->asXml($this->settings["LMSScormDataPath"] . $learning->id . "/moddata/scorm/1/xml/information.xml");
					}
				}
			}
		}

		// Add comments/urls if present
		if (
			isset($data['learning_module_evidences']) &&
			is_array($data['learning_module_evidences'])
		) {
			foreach ($data['learning_module_evidences'] as $key => $learning_module_evidence) {
				$evidence = new \Models\LearningModuleEvidence;
				$evidence->learning_modules_id = $learning->id;
				$evidence->user_id = \APP\Auth::getUserId();
				$evidence->added_by = \APP\Auth::getUserId();
				$evidence->manager = \APP\Auth::isAdminInterface();
				$evidence->evidence = $learning_module_evidence['evidence'];
				$evidence->evidence_type = 'comment';
				$evidence->status = 1;
				$evidence->save();
			}
		}

		return
			$response
				->write($learning->id)
		;

	})->add(\APP\Auth::getStructureAccessCheck('library-learning-resources-and-lessons', 'insert'));

	// Add evidence as comment!
	$this->post('/module/{module_id:[0-9]+}/comment', function (Request $request, Response $response, array $args) {
		$data = $request->getParsedBody();

		if (isset($data['comment_url'])) {
			$evidence = new \Models\LearningModuleEvidence;
			$evidence->learning_modules_id = $args['module_id'];
			$evidence->user_id = \APP\Auth::getUserId();
			$evidence->manager = \APP\Auth::isAdminInterface();
			$evidence->evidence = $data['comment_url'];
			$evidence->evidence_type = 'comment';
			$evidence->status = 1;
			$evidence->save();
		} else {
			return $response
				->withStatus(401)
				->withHeader('Content-Type', 'text/html')
				->write('Missing comment!')
			;
		}


		return
			$response
				->withHeader('Content-Type', 'application/json')
				->write(json_encode($evidence))
		;
	})->add(\APP\Auth::getStructureAccessCheck('library-learning-resources-and-lessons', 'insert'));


	$this->post('/youtube-playlist/new', function (Request $request, Response $response) {
		$data = $request->getParsedBody();
		if (
			isset($data['category']) &&
			isset($data['videos']) &&
			is_array($data['videos'])
		) {
			$type = \Models\LearningModuleType::where('slug', 'youtube')->first();

			foreach ($data['videos'] as $key => $video) {

				$learning = \Models\LearningModule
					::where('type_id', $type->id)
					->where('material', 'LIKE', '%' . $video['videoID'] . '%')
					->first();
				if (!$learning) {
					$learning = new \Models\LearningModule;
					$learning->type_id = $type->id;
					$learning->material = ["sessions" => [], "link" => 'https://www.youtube.com/watch?v=' . $video['videoID']];
				}

				$learning->name = $video['title'];
				$learning->category_id = $data['category'];
				$learning->description = $video['description'];
				$learning->created_by = \APP\Auth::getUserId();
				$learning->status = true;
				if (isset($data['playListName'])) {
					$provider = \Models\LearningProvider::firstOrCreate(
						['company' => $data['playListName']],
						['status' => true]
					);
					$learning->provider_id = $provider->id;
				}

				// Retrieve images!
				if (
				isset($video['thumbnails'])
				) {
					if (isset($video['thumbnails']['medium']['url'])) {
						$thumb_parts = pathinfo($video['thumbnails']['medium']['url']);
						if (
							$thumb_parts['extension'] == 'jpg' ||
							$thumb_parts['extension'] == 'png' ||
							$thumb_parts['extension'] == 'jpeg'
						) {
							$thumb_path = $this->settings["LMSThumbPath"];
							$thumbnail = $video['videoID'] . '_thumb.' . $thumb_parts['extension'];
							file_put_contents($thumb_path . $thumbnail, fopen($video['thumbnails']['medium']['url'], 'r'));
							$learning->thumbnail = $thumbnail;
						}
					}
					if (isset($video['thumbnails']['maxres']['url'])) {
						$promo_parts = pathinfo($video['thumbnails']['maxres']['url']);
						if (
							$promo_parts['extension'] == 'jpg' ||
							$promo_parts['extension'] == 'png' ||
							$promo_parts['extension'] == 'jpeg'
						) {
							$promo_path = $this->settings["LMSPromoPath"];
							$promo = $video['videoID'] . '_promo.' . $promo_parts['extension'];
							file_put_contents($promo_path . $promo, fopen($video['thumbnails']['maxres']['url'], 'r'));
							$learning->promo_image = $promo;
						}
					}
				}

				$learning->save();

			}
		}

	})->add(\APP\Auth::getStructureAccessCheck('library-learning-resources-and-lessons', 'insert'));

	// Adds new lesson
	$this->post('/course/new', function (Request $request, Response $response) {

		$data = $request->getParsedBody();

		$lesson = new \Models\LearningModule;
		$lesson->self_enroll = (bool)$lesson->self_enroll;
		$lesson->print_lesson = (bool)$lesson->print_lesson;
		$lesson->approval = (bool)$lesson->approval;
		$lesson->order_modules = (bool)$lesson->order_modules;

		$fields = [
			"code", "name", "category_id", "f_p_category_id", "keywords", "self_enroll", "approval", "print_lesson",
			"company_id", "description", "order_modules", "accreditation_description", "due_after_period"
		];

		\APP\Tools::setObjectFields($lesson, $fields, $data);

		$lesson->id = \APP\Course::getNewCourseId(
			$lesson->name,
			$this->settings["FixedCourseIds"],
			$this->settings["CourseIdStart"]
		);
		$lesson->material = false;
		$lesson->is_course = 1;
		$lesson->status = 1;
		$lesson->created_by = \APP\Auth::getUserId();
		$lesson->save();

		$course_modules = isset($data["modules"]) ? \APP\Tools::getObjectIds(json_decode($data["modules"], true)) : [];
		$lesson->modules()->sync($course_modules);
		\Models\LearningModule::uploadImages($response, $data, $_FILES, $this->settings, $lesson);

		/*Update Lesson progress*/
		\Models\LearningCourseModule::updateTracking($lesson);
		return
			$response
				->write($lesson->id);

	})->add(\APP\Auth::getStructureAccessCheck('library-learning-resources-and-lessons', 'insert'));

	// Update lesson
	$this->post('/course/{id:[0-9]+}', function (Request $request, Response $response, array $args) {

		$data = $request->getParsedBody();

		// get all existing 	modules and detach them from users.
		$lesson = \Models\LearningModule
			::with(["modules" => function ($query) {
				$query->select("learning_modules.id", "name");
			}])
			->find($args["id"]);


		$module_ids_remove = [];
		foreach ($lesson->modules as $key => $value) {
			$module_ids_remove[] = $value->id;
		}
		// get all related modules to ones needed to be removed

		$fields = [
			"code", "name", "category_id", "f_p_category_id", "keywords", "self_enroll", "approval",
			"company_id", "description", "order_modules", "accreditation_description", "due_after_period", "print_lesson"
		];

		$data["due_after_period"] = isset($data["due_after_period"]) ? intval($data["due_after_period"]) : 0;

		\APP\Tools::setObjectFields($lesson, $fields, $data, true);
		$lesson->save();

		$lesson_changes = $lesson->getChanges();

		// IF due_after_period has changed, update lesson due at times!
		if (isset($lesson_changes['due_after_period'])) {
			$lesson_results = \Models\LearningResult
				::where('learning_module_id', $lesson->id)
				->get();;

			// loop learning results and update them accordingly
			foreach ($lesson_results as $key => $lesson_result) {
				$created_at = \Carbon\Carbon::parse($lesson_result->created_at);
				$lesson_result->due_at = $created_at->copy()->addDays($lesson->due_after_period);
				$lesson_result->save();
			}
		}


		$course_modules = isset($data["modules"]) ? \APP\Tools::getObjectIds(json_decode($data["modules"], true)) : [];

		// need to remove all entries from learning_course_modules then sync them
		\Models\LearningCourseModule::where('learning_course_id', $args["id"])->get()->each->delete();
		$lesson->modules()->sync($course_modules);
		// Sync does not trigger created
		\Models\ScheduleLink::processAction(
			'create',
			$lesson->id,
			'lesson',
			[
				'link' => 'resources',
				'entries' => $course_modules
			]
		);


		// get all results from learning results that have this course, so I can get all assigned users
		$learning_results = \Models\LearningResult
			::where('learning_module_id', $args["id"])
			->where('refreshed', false)
			->groupBy('user_id')
			->get()
		;
		// Possibly wrong way of getting active assigned users to this course, need to research more.

		$removed_modules = array_diff($module_ids_remove, $course_modules);
		$added_modules = array_diff($course_modules, $module_ids_remove);

		if (
			!empty($removed_modules) ||
			!empty($added_modules)
		) {
			foreach ($learning_results as $key => $value) { // loop all responses from learning_results table(users)
				$user = \Models\User::find($value->user_id); // get user(that is assigned to course) object.

				// detach modules assigned to course (before this course was saved) from user, clean up
				if (!empty($removed_modules)) {
					\Models\UserLearningModule::unlinkResources($user->id, $removed_modules, 'lesson updated, remove removed resources');
				}

				// detach/attach modules from user using module ssubmitted in this request
				if (!empty($added_modules)) {
					\Models\UserLearningModule::linkResources($user->id, $added_modules, 'lesson updated, assign added resources');
				}

				// do the syncing for user
				\APP\Learning::syncUserResults($user->id);
			}
		}

		$response = \Models\LearningModule::uploadImages($response, $data, $_FILES, $this->settings, $lesson);
		\Models\LearningCourseModule::updateTracking($lesson);

		return $response;

	})->add(\APP\Auth::getStructureAccessCheck('lessons-and-learning-resources', 'update'));

	// Gets a list of all modules(unpaginated), except lessons and evidence created for sub-criteria.
	$this->get('/module/all', function (Request $request, Response $response) {
		$query = [];
		if (
			\APP\Auth::isAdminInterface() ||
			\APP\Auth::isDistributor()
		) {
			$query = \Models\LearningModule
				::select(
					"id",
					"name",
					"type_id",
					"category_id",
					"created_by",
					"f_p_category_id",
					"is_course"
				)
				->where("status", true)
				->where("is_course", false)
				->where('guideline', false)
				->with(["Type" => function ($query) {
					$query->select("id", "name", "slug");
				}])
				->with(["Category" => function ($query) {
					$query->select("id", "name");
				}])
				->with(["CreatedBy" => function ($query) {
					$query->select("id", "fname", "lname");
				}]);

			if ($this->settings['licensing']['isSMCR']) {
				$query = $query
					->with('FPCategory');
			}

			$query = \Models\LearningModule::filterEvidenceforRoles($query);
			$query = $query->get();
		}

		return
			$response
				->withHeader('Content-Type', 'application/json')
				->write(json_encode($query))
		;
	})->add(\APP\Auth::getStructureAccessCheck(['library-learning-resources-and-lessons', 'trainee-modules'], 'select'));

	$this->get('/course/all', function (Request $request, Response $response) {
		$query = [];
		if (\APP\Auth::isAdminInterface()) {
			$query = \Models\LearningModule
				::where("status", ">", 0)
				->where("is_course", "=", 1)
				->get();
		}
		return
			$response
				->withHeader('Content-Type', 'application/json')
				->write(json_encode($query));
	})->add(\APP\Auth::getStructureAccessCheck(['library-learning-resources-and-lessons', 'trainee-modules'], 'select'));

	$this->get('/types/all', function (Request $request, Response $response) {
		$query = \Models\LearningModuleType
			::with(["LearningModuleTypeParameter" => function ($query) {
				$query
					->orderBy('position', 'asc')
					->where("status", 1)
				;
			}])
			->where("status", 1)
			->whereNotIn("id", $this->settings['licensing']['hiddenResourceTypes'])
		;

		$query = $query
			->get()
		;

		foreach ($query as $key => $item) {
			foreach ($item->LearningModuleTypeParameter as $parameter_key => $parameter) {
				$parameter->ngm = 'learning.material.' . $parameter->parameterslug;
			}
		}

		return
			$response
				->withHeader('Content-Type', 'application/json')
				->write(json_encode($query));
	})->add(\APP\Auth::getStructureAccessCheck(['library-learning-resources-and-lessons', 'trainee-modules'], 'select'));

	// Validates??!!!
	$this->group("/validate", function () {
		$this->get('/code/{code: .+}', function (Request $request, Response $response, $args) {

			$code = $args["code"];
			$params = $request->getQueryParams();

			if (isset($params["exclude_id"])) {
				if (
				\Models\LearningModule::where("code", "=", $code)
					->where("id", "<>", $params["exclude_id"])->first()
				) {
					return $response->withStatus(409)->write('Already exists');
				}
			}

			return
				$response->write("ok");
		});
	})->add(\APP\Auth::getSessionCheck());

	// list all users related to "learning_modules" table by field "created_by"
	$this->get('/createdbyusers/list', function (Request $request, Response $response) {
		$query = [];

		if (\APP\Auth::checkStructureAccess(['library-learning-resources-and-lessons'], 'select')) {
			$query = \Models\User
				::join("learning_modules", function ($join) {
					$join
						->on("learning_modules.created_by", "=", "users.id");
				})
				->select("users.id", "users.fname", "users.lname")
				->groupBy("users.id")
				->get();
		}
		return
			$response
				->withHeader('Content-Type', 'application/json')
				->write(json_encode($query));

	})->add(\APP\Auth::getSessionCheck());


	// list all resources that are active, without relations
	// TODO: filter out evidence that is created by/for learners not assigned to you
	$this->get('/list{type:[\/a-z\-]*}{user_id:[\/0-9]*}', function (Request $request, Response $response, $args) {
		$query = \Models\LearningModule
			::where('status', true)
			->where('guideline', false);

		if (
			isset($args['type']) &&
			(
				$args['type'] == '/full' ||
				$args['type'] == '/full/'
			)
		) {
			$query = $query
				->select(
					'id',
					'name',
					'category_id',
					'f_p_category_id',
					'type_id',
					'is_course',
					'company_id'
				)
				->with('category')
				->with('FPCategory')
				->with('type')
				->with("competencies")
				->with("company");

		} else {
			$query = $query
				->select('id', 'name');
		}

		// If user_id is passed,return flag with resource if user is assigned said resource
		if (
			isset($args['user_id']) &&
			$args['user_id']
		) {
			$query = $query
				->addSelect(
					DB::raw(
						"
							(
								case when (
									select count(*) from user_learning_modules where user_id = " . $args['user_id'] . " and learning_module_id = learning_modules.id and deleted_at is null
								) > 0
									then true
									else false
								end
							) as assigned
						"
					)
				);
		}

		// You don't need to see evidence resource added by learners not assigned to you
		$query = \Models\LearningModule::filterEvidenceforRoles($query);
		$query = $query
			->get();

		return
			$response
				->withHeader('Content-Type', 'application/json')
				->write(json_encode($query));

	})->add(\APP\Auth::getStructureAccessCheck('library-learning-resources-and-lessons', 'select'));
	// learning page, paginated
	// filter out evidence that is created by/for learners not assigned to you
	$this->post('/list{download:[\/a-z]*}', function (Request $request, Response $response,array $args) {
		$params = $request->getParsedBody();
		$user = \APP\Auth::getUser();

		if (isset($params["search"]["additionalSearchParams"])) {
			$additional_search_params = $params["search"]["additionalSearchParams"];
			unset($params["search"]["additionalSearchParams"]);
		}

		$query = \Models\LearningModule::select("learning_modules.*")
			->with("category")
			->with('FPCategory')
			->with("type")
			->where('hide_lesson',0)
			->with("competencies")
			->with("company")
			->with("versions")
			->with(["createdby" => function ($query) {
				$query->select("id", "fname", "lname");
			}])
			->where('guideline', false);

		// If manager, check if there is need to filter out categories
		$query = \Models\ManagerLearningModuleCategory::checkManagerAccessToCategories($query);


		// If you are manager/admin, show evidence created by you for your trainees or by you for re-use, or by other managers for reuse
		// If you are trainee, show evidence created for you or managers/admin

		if (
			!\APP\Auth::isAdmin() ||
			(
				\APP\Auth::isAdmin() &&
				!\APP\Auth::showAllResources()
			)
		) {
			$query = \Models\LearningModule::filterEvidenceforRoles($query);

			// Also hide any zoom/teams resources created in events
			$query = $query
				->whereNotIn('id',
					\Models\LearningModule
						::select('id')
						->whereIn('type_id',
							\Models\LearningModuleType
								::select('id')
								->where('slug', 'microsoft_teams')
								->orWhere('slug', 'zoom_meeting')
								->get()
						)
						->whereIn(
							'id',
							\Models\ScheduleLink
								::select('link_id')
								->where('type', 'resources')
								->get()
						)
						->get()
				)
			;
		}

		if (isset($params["search"])) {
			if (isset($params["search"]["competency_id"])) {
				$query->join("learning_module_competencies", function ($join) use ($params) {
					$join->on("learning_module_competencies.learning_module_id", "=", "learning_modules.id")
						->where("learning_module_competencies.competency_id", "=", $params["search"]["competency_id"]);
				});

				unset($params["search"]["competency_id"]);
			}
			if (isset($params["search"]["refresh"])) {
				unset($params["search"]["refresh"]);
			}

			if (isset($params["search"]["company_id"])) {
				$query
					->where("company_id", "=", $params["search"]["company_id"])//->orWhereNull("company_id")
				;
				unset($params["search"]["company_id"]);
			}

			//If lesson_id is passed, look if resource is assigned to that lesson and return indicator that so
			if (isset($params["search"]["lesson_id"])) {
				$lesson_id = $params["search"]["lesson_id"];
				unset($params["search"]["lesson_id"]);
				$query = $query
					->withCount(['Course' => function ($query) use ($lesson_id) {
						$query
							->where('learning_course_id', $lesson_id);
					}]);

				if (isset($params["search"]["added"])) {
					$added = $params["search"]["added"];
					unset($params["search"]["added"]);
					$query = $query
						->whereHas('Course', function ($query) use ($added, $lesson_id) {
							if ($added == 1) {
								$query
									->where('learning_course_id', $lesson_id);
							} else {
								$query
									->where('learning_course_id', '!=', $lesson_id);
							}
						});
				}
			}

			// If schedule ID is passed, return count of schedules resource is assigned to (target should be 1)
			$query = \Models\Schedule::countAndConditions($query, $params);

		}
		if (isset($args["download"]) && $args["download"] == "/download") {
			$data = \APP\SmartTable::searchPaginate($params, $query, false, false);
			$export_fields = [
				"ID" => "id",
				"Name" => "name",
				"Category" => "category.name",
				"Type"=>"type.name",
				"Company"=>"company.name",
				"createdby"=>"createdby",
				"Average time spent"=>'total_duration',
				"Number of times completed"=>"users_count"
			];


			$download_file_name = uniqid("resource.list.") . ".xlsx";

			\APP\Tools::generateExcelDownload(
					$data,
					$export_fields,
					$this->settings["LMSTempPath"] . $download_file_name
				)
			;

			return
			$response
			->withHeader('Content-Type', 'application/json')
			->write(json_encode($download_file_name))
			;
		} elseif(isset($args["download"]) && $args["download"] == "/print") {
			$data = \APP\SmartTable::searchPaginate($params, $query, false, false);
			return $response
			->withHeader('Content-Type', 'application/json')
			->write(json_encode($data));
		}else{
			$p = \APP\SmartTable::searchPaginate($params, $query);
		}

		foreach ($p as $learning) {
			$learning->setAppends(['safe_thumbnail']);
		}

		// Will loop all responses and if isJackdawCloud is enabled, will return parameter with learning that will show jackdaw button
		if ($this->settings['licensing']['isJackdawCloud']) {
			foreach ($p as $key => $learning) {
				if (
					$user->role->jackdaw_type &&
					$learning->type_id == 1 &&
					$learning->jackdaw == 1
				) {
					$learning->jackdaw = 0;

					// if jackdaw_type is CMS, allow to edit all e-learning resources
					if (
						$user->role->jackdaw_type == 'CMS' ||
						$user->role->jackdaw_type == 'Unlimited'
					) {
						$learning->jackdaw = 1;

						// Else check if team, then show jackdaw button from everyone in team.
					} elseif ($user->role->jackdaw_type == 'Team') {
						foreach ($user->groups as $key => $group) {
							if ($learning->created_by_group == $group->id && $group->is_jackdaw_team) {
								$learning->jackdaw = 1;
							}
						}
						// Last one, check if learning was created by user.
					} elseif (
						$learning->created_by == $user->id &&
						$learning->jackdaw_resource == true // means it was created using jackdaw editor
					) {
						$learning->jackdaw = 1;
					}
				} else {
					$learning->jackdaw = 0;
				}
			}
		}

		$json = $p->toJson();

		return
			$response
				->withHeader('Content-Type', 'application/json')
				->write($json);
	})->add(\APP\Auth::getStructureAccessCheck('library-learning-resources-and-lessons', 'select'));

	// Update all installed learning resources if found in installation folder, preserve quizxml and information.xml
	$this->put('/available/update/all', function (Request $request, Response $response, array $args) {
		$params = $request->getParsedBody();

		$installedResources = \Models\LearningModule
			::where('status', 1)
			->where('type_id', 1)
			->get();
		$updated_resources = [];
		$failed_resources = [];

		foreach ($installedResources as $key => $installedResource) {

			// ignoring errors for update all
			try {
				$source_location = $this->settings["AvailableModulesLocation"] . "/" . $installedResource->name;
				if (is_dir($source_location) && $installedResource->name) {
					$course = \APP\Course::get($installedResource);
					$course->copyAvailableModule(
						$source_location,
						$this->settings["LMSScormDataPath"],
						true,
						isset($params['force']) && $params['force'] ? false : true // true will preserve quizxml and information.xml, force - false will overwrite everything.
					);

					// Update room files
					\APP\Jackdaw::updateRoomFiles($this->settings["LMSPublicPath"], $installedResource->id);

					$updated_resources[] = $installedResource->name;
				}
			} catch (Exception $e) {
				$failed_resources[] = $installedResource->name;
			}
		}
		return
			$response
				->withHeader('Content-Type', 'application/json')
				->write(
					json_encode(
						[
							"updated_resources" => $updated_resources,
							"failed_resources" => $failed_resources
						]
					)
				);
	})->add(\APP\Auth::getStructureAccessCheck('library-learning-resources-and-lessons', 'update'));

	$this->put('/available/update/{id:[0-9]+}', function (Request $request, Response $response, array $args) {
		$available_module_name = "";
		$available_module = \Models\AvailableModule::find($args["id"]);

		if ($available_module) {
			$available_module_name = $available_module->name;
			try {
				$learning_module = \Models\LearningModule
					::where("name", $available_module_name)
					->where('status', 1)
					->where('type_id', 1)
					->first()
				;
				if (!$learning_module) {
					throw new Exception("Not e-learning resource!");
				}
				$source_location = $this->settings["AvailableModulesLocation"] . "/" . $available_module_name;

				if (
					is_dir($source_location) &&
					$available_module_name
				) {
					$course = \APP\Course::get($learning_module);
					$course->copyAvailableModule(
						$source_location,
						$this->settings["LMSScormDataPath"],
						true
					);
					// Update room files
					\APP\Jackdaw::updateRoomFiles($this->settings["LMSPublicPath"], $learning_module->id);
				} else {
					return
						$response->withStatus(500)
							->withHeader('Content-Type', 'text/html')
							->write("Source learning resource does not exist!")
					;
				}
			} catch (Exception $e) {
				return
					$response->withStatus(500)
						->withHeader('Content-Type', 'text/html')
						->write(implode("\n", ["An error occured while updating the course." . $e->getMessage()]));
			}
		}
		return $response;
	})->add(\APP\Auth::getStructureAccessCheck('library-learning-resources-and-lessons', 'update'));

	$this->get('/available/{id:[0-9]+}', function (Request $request, Response $response, array $args) {
		$available_module_name = "";
		$available_module = \Models\AvailableModule::find($args["id"]);
		if ($available_module) {
			if (isset($this->settings["DefaultCourseDescriptions"][$available_module->name])) {
				$available_module->description = $this->settings["DefaultCourseDescriptions"][$available_module->name];
			}

			if (isset($this->settings["DefaultCourseKeywords"][$available_module->name])) {
				$available_module->keywords = $this->settings["DefaultCourseKeywords"][$available_module->name];
			}

			return
				$response
					->withHeader('Content-Type', 'application/json')
					->write(json_encode($available_module));
		} else {
			return $response;
		}

	})->add(\APP\Auth::getStructureAccessCheck('library-learning-resources-and-lessons', 'insert'));

	// List all available learning resources for installation/update
	$this->post('/available/list', function (Request $request, Response $response) {

		$params = $request->getParsedBody();
		$available_modules = [];
		if ($this->settings["AvailableModulesLocation"]) {
			\APP\Course::UpdateAvailableModules($this->settings["AvailableModulesLocation"]);
		}
		$query = \Models\AvailableModule
			::select(['id', 'name'])
			->selectRaw("
				`name` IN (
					SELECT
						name
					FROM
						learning_modules
					WHERE
						type_id = 1
				) AS status
			")
			->selectRaw("
				`name` IN (
					SELECT
						name
					FROM
						learning_modules
					WHERE
						type_id = 1
					AND
						status = 0
				) AS disabled
			")
			->selectRaw("
				`name` IN (
					SELECT
						name
					FROM
						learning_modules
					WHERE
						type_id = 1
					AND
						status = 1
				) AS enabled
			");
		$p = \APP\SmartTable::searchPaginate($params, $query);

		return
			$response
				->withHeader('Content-Type', 'application/json')
				->write($p->toJson());

	})->add(\APP\Auth::getStructureAccessCheck('library-learning-resources-and-lessons', 'select'));

	// Add comment to learning result
	$this->post('/addlearningresultscomment', function (Request $request, Response $response) {
		$data = $request->getParsedBody();
		if (!isset($data["user_id"])) {
			$data["user_id"] = \APP\Auth::getUserId();
		}

		// Manager is submitting comment.
		//\APP\Auth::isManager()
		if (
			\APP\Auth::isAdmin() ||
			\APP\Auth::isManagerOf($data["user_id"]) ||
			$data["user_id"] == \APP\Auth::getUserId() ||
			\APP\Auth::accessAllLearners()
		) {

			$learningResult = \Models\LearningResult
				::find($data["learning_results_id"]);

			$query = new \Models\LearningResultsComment;
			$query->comment_by_user_id = \APP\Auth::getUserId();
			$query->created_for_user_id = $data["user_id"];
			$query->learning_module_id = $data["learning_module_id"];
			$query->learning_results_id = $data["learning_results_id"];
			if (!\APP\Auth::isLearner()) {
				$query->visible_learner = isset($data["visible_learner"]) ? $data["visible_learner"] : false;
			}
			$query->comment = $data["comment"];
			if (\APP\Auth::isQa()) {
				$query->qa = true;
			}
			$query->save();

			// If manager refused with this comment update learning results table's manager_refused fields.
			if (
				isset($data['manager_refused']) &&
				$data['manager_refused']
			) {

				$learningResult->manager_refused_comment = $data["comment"];
				$learningResult->manager_refused_time = \Carbon\Carbon::now();
				$learningResult->manager_refused_by = \APP\Auth::getUserId();
			}

			// If learner, update last active date in user's table
			if (\APP\Auth::isLearner()) {
				$user = \APP\Auth::getUser();
				$user->last_contact_date = \Carbon\Carbon::now();
				$user->save();


				// Send e-mail to learner's managers
				// Send out "Learning Resource Comment for Manager" to learner
				$template = \Models\EmailTemplate
					::where('name', 'Learning Resource Comment for Manager')
					->where('status', true)
					->first();

				$manager_ids = [];
				foreach ($user->managers as $key => $manager) {
					if (
						!$manager->role->email_disable_manager_notifications &&
						$manager->status
					) {
						$manager_ids[] = $manager->id;
					}
				}

				if (
					$template &&
					$template->id &&
					count($manager_ids) > 0 &&
					$learningResult->module->track_progress
				) {
					$email_queue = new \Models\EmailQueue;
					$email_queue->email_template_id = $template->id;
					$email_queue->learning_module_id = $data["learning_module_id"];
					$email_queue->recipients = $manager_ids;
					$email_queue->comment = $data["comment"];
					$email_queue->from = \APP\Auth::getUserId();
					$email_queue->save();
				}
				$learningResult->learner_action = false;
				$learningResult->save();
			} else {
				$learningResult->learner_action = true;
				$learningResult->learner_action_date = \Carbon\Carbon::now();
				$learningResult->save();
			}

			// Update last contact date against users standard pivot if this resource is assigned to any standard.
			\Models\ApprenticeshipStandardUser::lastUpdate($data["learning_module_id"], $data["user_id"]);

			// Send e-mail to learner and update last contact's date for learner
			if (
				\APP\Auth::isManager() ||
				\APP\Auth::isAdmin() ||
				\APP\Auth::accessAllLearners()
			) {
				$user_update_date = \Models\User::find($data["user_id"]);
				$user_update_date->last_contact_date = \Carbon\Carbon::now();
				$user_update_date->save();

				// Update managers activity date also
				$manager_update_date = \Models\User::find(\APP\Auth::getUserId());
				$manager_update_date->last_contact_date = \Carbon\Carbon::now();
				$manager_update_date->save();

				// Do not send if you are QA
				if (
					!\APP\Auth::isQa() &&
					$query->visible_learner
				) {

					$send_email = \APP\Smcr::sendLearnerEmail($learningResult);

					$template = \Models\EmailTemplate
						::where('name', '%%learning_resource%% Comment for %%user%%')
						->where('status', true)
						->first();
					if (
						$template &&
						$template->id &&
						$send_email &&
						$learningResult->module->track_progress
					) {
						$email_queue = new \Models\EmailQueue;
						$email_queue->email_template_id = $template->id;
						$email_queue->learning_module_id = $data["learning_module_id"];
						$email_queue->recipients = [intval($data["user_id"])];
						$email_queue->comment = $data["comment"];
						$email_queue->from = \APP\Auth::getUserId();
						$email_queue->save();
					}
				}
			}
		} else {
			$response = $response->withStatus(500);
		}

		return
			$response;
	})->add(\APP\Auth::getStructureAccessCheck(['lessons-and-learning-resources', 'trainee-learning-results', 'misc-permissions-learning-results-comments'], 'insert'));

	$this->put("/comment-visibility/{id:[0-9]+}", function (Request $request, Response $response, array $args) {
		$data = $request->getParsedBody();

		// Fail is user is DEMO
		if (
			\APP\Auth::isDemoUser() ||
			\APP\Auth::isLearner()
		) {
			return $response
				->withStatus(403)
				->withHeader('Content-Type', 'text/html')
				->write('403 Forbidden');
		}

		$comment = \Models\LearningResultsComment::find($args['id']);
		$comment->visible_learner = !$comment->visible_learner;
		$comment->save();

	})->add(\APP\Auth::getSessionCheck());

	//Comment list for learner interface.
	$this->get('/results/{learning_results_id:[0-9]+}/commentslist', function (Request $request, Response $response, $args) {
		$query = \Models\LearningResultsComment
			::where('learning_results_id', $args['learning_results_id'])
			->where('created_for_user_id', \APP\Auth::getUserId())
			->where('status', true)
			->where('visible_learner', true)
			->with(["createdby" => function ($query) {
				$query->select("id", "fname", "lname");
			}])
			->where('qa', false) // Hide QA comments for learner
			->get();

		return
			$response
				->withHeader('Content-Type', 'application/json')
				->write(json_encode($query));
	})->add(\APP\Auth::getStructureAccessCheck(['lessons-and-learning-resources', 'trainee-learning-results'], 'select'));

	// Update duration for learning results by trainee
	$this->put('/update-duration/{learning_results_id:[0-9]+}', function (Request $request, Response $response, $args) {
		$data = $request->getParsedBody();

		if (
			isset($data['user_id']) &&
			(
				\APP\Auth::isAdmin() ||
				\APP\Auth::isManagerOf($data["user_id"])
			)
		) {
			$user = \Models\User::findOrFail($data['user_id']);
		} else {
			$user = \Models\User::findOrFail(\APP\Auth::getUserId());
		}

		$learning_result = \Models\LearningResult
			::where("id", '=', $args["learning_results_id"])
			->where("user_id", "=", $user->id)
			->with('module')
			->firstOrFail();

		if (\APP\Auth::isLearner()) {
			$learning_result->learner_action = false;
		}

		if (
			(
				isset($data['duration_hours']) ||
				isset($data['duration_minutes'])
			) &&
			(
				$learning_result->module->duration_change ||
				\APP\Auth::isAdminInterface()
			)
		) {
			if (isset($data['duration_hours'])) {
				$learning_result->duration_hours = $data['duration_hours'];
			}
			if (isset($data['duration_minutes'])) {
				$learning_result->duration_minutes = $data['duration_minutes'];
			}
			$learning_result->save();
		} else {
			return $response
				->withStatus(403)
				->withHeader('Content-Type', 'text/html')
				->write('403 Forbidden');
		}

		return
			$response;
	})->add(\APP\Auth::getStructureAccessCheck(['lessons-and-learning-resources', 'trainee-learning-results'], 'update'));


	// If configuration option "allowLearnerRefreshLearning" is true, allow for Learner to refresh learning_result.
	$this->get('/record-new-learning/{module_id:[0-9]+}', function (Request $request, Response $response, array $args) {
		if (\APP\Tools::getConfig('allowLearnerRefreshLearning')) {
			\APP\Refresh::refreshResults(true, [\APP\Auth::getUserId()], [$args['module_id']]);
		} else {
			return
				$response
					->withStatus(500);
		}
		return
			$response;
	})->add(\APP\Auth::getStructureAccessCheck('trainee-learning-results', 'update'));

	// Favorite learning_result, for filtering
	$this->get('/favorite/{id:[0-9]+}', function (Request $request, Response $response, array $args) {
		$result = \Models\LearningResult
			::where('id', $args['id'])
			->with('userlearningmodules')
			->first();

		if (count($result->userlearningmodules) > 0) {
			$result->favorite = !$result->favorite;
			$result->save();
		} else {
			return
				$response
					->withStatus(404);
		}

		return
			$response;
	})->add(\APP\Auth::getStructureAccessCheck('trainee-learning-results', 'update'));

	// List all actions needed for current logged in user.
	$this->get('/actions', function (Request $request, Response $response, array $args) {

		$query = \Models\LearningResult
			::where('user_id', \APP\Auth::getUserId())
			->select(
				'id',
				'user_id',
				'learning_module_id',
				'learner_action_date',
				'completion_status',
				'refreshed',
				'learner_action',
				DB::raw("DATE_FORMAT(learner_action_date,'%d/%m/%Y') AS learner_action_date_uk")
			)
			->where('refreshed', false)
			//->where('completion_status', '!=', 'completed')
			->where('learner_action', true)
			->whereHas('module', function ($query) {
				$query
					->where('status', true)
					->whereHas('Type', function ($query) {
						$query
					->where('status', true)
							->whereIn('slug', ['webpage', 'classroom', 'book_cd_dvd', 'on_the_job', 'upload', 'blog_entry', 'reflective_log'])
						;
					})
				;
			})
			->whereIn('learning_module_id',
				\Models\UserLearningModule::select('learning_module_id')
					->where('user_id', \APP\Auth::getUserId())
					->get()
			)
			->with(["module" => function ($query) {
				$query
					->select(
						'id',
						'name',
						'type_id'
					)
					->with("type");
			}])
			->withCount(["Comments" => function ($query) {
				$query
					->where("status", true);
			}])
			->get();

		// Here will also check if enableUserFieldAlertSystem is enabled and user is missing any fields, if so, notify em!
		$userFieldAlertSystemInterval = \APP\Tools::getConfig('userFieldAlertSystemInterval');
		if ($userFieldAlertSystemInterval) {
			$userFieldAlertSystemMonitoredFields = \APP\Tools::getConfig('userFieldAlertSystemMonitoredFields');
			$userFieldAlertSystemMonitoredFields = explode(',', $userFieldAlertSystemMonitoredFields);
			if (count($userFieldAlertSystemMonitoredFields) > 0) {

				// get generic fields from picklist!
				$user_field_alert_list = \Models\Picklist::where('type', 'user_field_alert_list')->get();
				$field_names = [];
				foreach ($user_field_alert_list as $key => $user_field_alert_list_item) {
					$field_names[$user_field_alert_list_item->slug] = $user_field_alert_list_item->value;
				}

				// Get extended fields!
				$user_field_alert_list_extended = \Models\TableExtensionField
					::where("status", true)
					->where('versions', 'like', '%"' . $this->settings["licensing"]['version'] . '"%')
					->where('show_learner', true)
					->get();
				$field_names_extended = [];
				foreach ($user_field_alert_list_extended as $key => $user_field_alert_list_item_extended) {
					$field_names_extended[$user_field_alert_list_item_extended->field_key] = $user_field_alert_list_item_extended->field_name;
				}


				$user = \APP\Auth::getUser();
				\Models\TableExtension::returnAllFields('users', $user->id, $user);

				$field_status = new \stdClass();
				$field_status->completion_status = [];


				foreach ($userFieldAlertSystemMonitoredFields as $key => $field) {
					if (
						// Complicated
						strpos($field, 'extended_') === false &&
						array_key_exists($field, $user->toArray()) &&
						(
							$user->{$field} == '' ||
							$user->{$field} == null
						) &&
						isset($field_names[$field])
					) {
						$field_status->completion_status[] = $field_names[$field];
					}

					$extended_field = str_replace("extended_", "", $field);
					if (
						strpos($field, 'extended_') !== false &&
						$user->extended &&
						isset($field_names_extended[$extended_field]) &&
						(
							(
								property_exists($user->extended, $extended_field) &&
								(
									$user->extended->{$extended_field} == '' ||
									$user->extended->{$extended_field} == null
								)
							) ||
							!property_exists($user->extended, $extended_field)
						)

					) {
						$field_status->completion_status[] = $field_names_extended[$extended_field];
					}
				}

				$field_status->module = new \stdClass();
				$field_status->module->name = $GLOBALS["CONFIG"]->licensing['labels']['missing_fields_in_your_profile'];
				$field_status->module->type = new \stdClass();
				$field_status->module->type->name = 'user profile';
				if (count($field_status->completion_status) > 0) {
					$query[] = $field_status;
				}
			}


		}

		return
			$response
				->withHeader('Content-Type', 'application/json')
				->write(json_encode($query));
	})->add(\APP\Auth::getSessionCheck());

	// Remove completed action by learner
	$this->delete('/actions/{id:[0-9]+}', function (Request $request, Response $response, array $args) {

		$query = \Models\LearningResult
			::where('id', $args['id'])
			->where('user_id', \APP\Auth::getUserId())
			->where('completion_status', 'completed')
			->where('learner_action', true)
			->first();

		// Could be that there is no action
		if ($query) {
			$query->learner_action = false;
			$query->save();
		}

		return
			$response;
	})->add(\APP\Auth::getSessionCheck());

	// Toggle off_the_job_training
	$this->put('/off_the_job_training/{id:[0-9]+}', function (Request $request, Response $response, $args) {
		$data = $request->getParsedBody();
		$result = \Models\LearningResult::find($args["id"]);

		if (
			$result->user_id == \APP\Auth::getUserId() ||
			\APP\Auth::isManagerOf($result->user_id) ||
			\APP\Auth::isAdmin() ||
			\APP\Auth::accessAllLearners()
		) {
			$result->off_the_job_training = isset($data['off_the_job_training']) ? $data['off_the_job_training'] : false;
			$result->save();
		} else {
			return $response
				->withStatus(403)
				->withHeader('Content-Type', 'text/html')
				->write('403 Forbidden');
		}

		return $response;
	})->add(\APP\Auth::getSessionCheck());


	/*Import Legacy Course Data*/
	$this->post('/import-course', function (Request $request, Response $response) {
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
				$n_records = \APP\Import::learningResults($this->settings["LMSTempPath"] . $import_file->getNameWithExtension());
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

	/*Import Legacy Course Data code Ends Here*/


	/*VERSION ADDING*/
	$this->post('/add_version', function (Request $request, Response $response, array $args) {
		$data = $request->getParsedBody();
		if (!\Models\LearningModule::find($data['id'])) {
			return $response;
		}

		$data["LMSScormVersionPath"]=$this->settings["LMSScormVersionPath"];
		$data["LMSScormDataPath"]=$this->settings["LMSScormDataPath"];
		$version = \Models\LearningModuleVersion::versionSync("add",$data);
		return $response
		->withHeader('Content-Type', 'application/json')
		->write($version);


	})->add(\APP\Auth::getStructureAccessCheck('library-learning-resources-and-lessons', 'update'));

	$this->get('/get_versions/{module_id:[0-9]+}', function (Request $request, Response $response, $args) {
		$versions = \Models\LearningModuleVersion::
		where('learning_module_id', '=', $args['module_id'])
		->orderBy('learning_module_versions.version', 'asc')
			->with(['CreatedUser' => function ($query) {
				$query
				->select('fname','lname','id', DB::raw("CONCAT(users.fname, ' ', users.lname) as version_created_user"));
			}])
			->with(['UpdatedUser' => function ($query) {
				$query
				->select('fname','lname','id', DB::raw("CONCAT(users.fname, ' ', users.lname) as version_updated_user"));
			}])
			->select('*', DB::raw('DATE_FORMAT(created_at, "%d/%m/%Y %H:%i") as version_created_at'),DB::raw('DATE_FORMAT(updated_at, "%d/%m/%Y %H:%i") as version_updated_at'))
			->get();

			// Tried "with" but seems not working with condition. need optimisation
			foreach($versions as $key=>$version){
				$evidence = \Models\LearningModuleEvidence
					::where(
						[
							['learning_modules_id','=',$version->learning_module_id],
							['version','=',$version->version]
						]
					)
					->where(function ($query) {
						$query
							->where('status', true)
							->where('manager', 1)
							->orderBy('created_at', 'desc')
							->with(['user' => function ($query) {
								$query
									->select('id', 'fname', 'lname')
								;
							}])
							->with(['AddedBy' => function ($query) {
								$query
									->select('id', 'fname', 'lname')
								;
							}])
						;
					})
					->get()
				;
				$versions[$key]['learning_module_evidences'] = $evidence;
			}

		return
			$response
				->withHeader('Content-Type', 'application/json')
				->write(json_encode($versions));
	})->add(\APP\Auth::getStructureAccessCheck('library-learning-resources-and-lessons', 'update'));


	// Remove completed action by learner
	$this->post('/remove_version', function (Request $request, Response $response, array $args) {
		$data = $request->getParsedBody();
		$learning = \Models\LearningModule::find($data['id']);

		if($learning->type_id==1){

			$scorm_version_folder = $this->settings["LMSScormDataPath"].$data['id'].'/'.$data['version'];
			\APP\Course::recursiveRemoveDirectory($scorm_version_folder);
		}
		$query = \Models\LearningModuleVersion
			::where('learning_module_id', "=", $data['id'])
			->where('version', "=", $data['version'])
			->first();

		// Could be that there is no action
		if ($query) {
			$query->delete();
		}

		return
			$response;
	})->add(\APP\Auth::getSessionCheck());

	$this->put('/module-duplicate/{id:[0-9]+}', function (Request $request, Response $response, array $args) {
		/*Replicate Learning Resource*/


		$learning = \Models\LearningModule::find($args["id"]);

		$learning_clone = $learning->replicate()->fill(
			[
				'code' => null,
				'name' => "copy of ".$learning->name,
			]
		);
		$learning_clone->save();

		/*Replicate Learning Resource Competencies*/
		$learning_compentencies = $learning->competencies;
		if(count($learning_compentencies)>0){
			foreach ($learning_compentencies as $compentency){
				$learning_module_compentency =  \Models\LearningModuleCompetency::find($compentency->id);
				if($learning_module_compentency){
					$learning_module_compentency_clone = $learning_module_compentency->replicate()->fill(
						[
							'learning_module_id' => $learning_clone->id,
						]
					);
					$learning_module_compentency_clone->save();
				}

			}
		}

		/*Replicate Learning Resource Prere*/
		$learning_prerequisites = $learning->prerequisites;
		if(count($learning_prerequisites)>0){
			foreach ($learning_prerequisites as $prerequisite){
				$learning_module_prerequisite =  \Models\LearningModulePrerequisite::find($prerequisite->id);
			  if( $learning_module_prerequisite){
				  $learning_module_prerequisite_clone = $learning_module_prerequisite->replicate()->fill(
					  [
						  'learning_module_id' => $learning_clone->id,
					  ]
				  );
				  $learning_module_prerequisite_clone->save();
			  }


			}
		}

		/*Replicate Learning Resource Versions*/
		$learning_versions = $learning->versions;
		if(count($learning_versions)>0){
			foreach ($learning_versions as $version){
				$learning_module_version =  \Models\LearningModuleVersion::find($version->id);
				$learning_module_version_clone = $learning_module_version->replicate()->fill(
					[
						'learning_module_id' => $learning_clone->id,
					]
				);
				$learning_module_version_clone->save();


			}
		}
		/*Replicate Learning Resource Versions SCORM folder and files for type e-learning only*/
		if ($learning->type->slug == 'e_learning') {
			if (file_exists($this->settings["LMSPublicPath"] . 'scormdata/' . $learning->id . "/moddata/scorm/1/xml/information.xml")) {
				\APP\Tools::recurseCopy($this->settings["LMSPublicPath"] . 'scormdata/' . $learning->id , $this->settings["LMSPublicPath"] . 'scormdata/' . $learning_clone->id );
				$course = \APP\Course::get($learning_clone);
				$course->createScormData($this->settings["LMSScormDataPath"]);
			}
		}
		/*Replicate Learning Resource Versions SCORM folder and files*/

		/*Replicate Learning Resource Evidences*/
		$learning_evidences= $learning->LearningModuleEvidences;
		if(count($learning_evidences)>0){
			foreach ($learning_evidences as $evidence){
				$learning_module_evidence =  \Models\LearningModuleEvidence::find($evidence->id);
				if($learning_module_evidence){
					$learning_module_evidence_clone = $learning_module_evidence->replicate()->fill(
						[
							'learning_modules_id' => $learning_clone->id,
							'hash' => bin2hex(random_bytes(16)),
						]
					);
					$learning_module_evidence_clone->save();
				}

				/*Duplicate Learning Resource Evidence files*/

				if (is_file($this->settings["LMSEvidencePath"] .'/'.$evidence->hash.'.'.$evidence->extension)) {
					copy($this->settings["LMSEvidencePath"] .'/'.$evidence->hash.'.'.$evidence->extension, $this->settings["LMSEvidencePath"] .'/'.$learning_module_evidence_clone->hash.'.'.$learning_module_evidence_clone->extension);
				}


			}
		}

		/*Replicate Learning Resource Courses*/
		$learning_courses= $learning->Courses;
		if(count($learning_courses)>0){
			foreach ($learning_courses as $course){
				$learning_course =  \Models\LearningCourseModule::find($course->id);
				if($learning_course){
					$learning_module_course_clone = $learning_course->replicate()->fill(
						[
							'learning_module_id' => $learning_clone->id,
						]
					);
					$learning_module_course_clone->save();
				}
			}
		}

		/*Replicate Learning Resource Department*/
		$data= $learning->Departments;
		if(count($data)>0){
			foreach ($data as $result){
				$obj=  \Models\DepartmentLearningModule::find($result->id);
				if($obj){
					$obj_clone = $obj->replicate()->fill(
						[
							'learning_module_id' => $learning_clone->id,
						]
					);
					$obj_clone->save();
				}


			}
		}

		/*Replicate Learning Resource Groups*/
		$data= $learning->Groups;
		if(count($data)>0){
			$obj_clone= null;
			$obj= null;
			foreach ($data as $result){
				$obj=  \Models\GroupLearningModule::find($result->id);
				if($obj){
					$obj_clone = $obj->replicate()->fill(
						[
							'learning_module_id' => $learning_clone->id,
						]
					);
					$obj_clone->save();
				}

			}
		}
		return $response->withStatus(200)
			->withHeader('Content-Type', 'text/html')
			 ->write(
			json_encode(
				[
					'l_name' => $learning_clone->name,
					'l_id' => $learning_clone->id,
				]
			)
		);
	})->add(\APP\Auth::getStructureAccessCheck('library-learning-resources-and-lessons', 'update'));

    $this->get('/perform', function (Request $request, Response $response, $args) {
        \Models\LearningResult::awardBadgeOnLearningCompletion(102447);
        \Models\ApprenticeshipStandardUser::awardBadgeOnProgramCompletion(715);
         print_r(\Models\UserBadge::select(DB::raw("user_badges.badge,learning_modules.name as learning_name,learning_modules.description as learning_description, learning_modules.id as learning_id, apprenticeship_standards.name as standard_name,apprenticeship_standards.id as standard_id, competencies.name as competency_name, competencies.id as competency_id"))
             ->leftJoin('learning_modules',function($join){
             $join->on('learning_modules.id','user_badges.type_id')->where('user_badges.type','=','learning_modules');
         })->leftJoin('apprenticeship_standards',function($join){
             $join->on('apprenticeship_standards.id','user_badges.type_id')->where('user_badges.type','=','apprenticeship_standards');
         })->leftJoin('competencies',function($join){
             $join->on('competencies.id','user_badges.type_id')->where('user_badges.type','=','competencies');
         })->where('user_id',1)->get()->toArray()
         );
    });
});
