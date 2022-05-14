angular.module('lmsApp').controller('ModalScheduleEvent', function ($scope, $uibModalInstance, data, $http, $rootScope, $dateOperation, $scheduleService, $learningService, $uibModal, $zoomService, $teamsService, $fileService, $window, $interval, $timeout, $commentsService, $forumService, $confirm, $filter, $ajaxActions, $scormService, $instructorLeadService, $credasService, $textOperations, $outlookService, $jamboardService) {

	$scope.passData = data;
	$scope.selectedUsers = [];
	$scope.selectedQueueUsers = [];
	$scope.event_id = false;
	$scope.forum = null;
	$scope.forum_add_status = false;
	$scope.tableState = null;
	$scope.is_smartClass = $rootScope.config.isOpeneLMSClassroom;
	$scope.user_data = {};
	$scope.eventLocked = false;

	$scope.closeAlert = function () {
		$scope.alerts = [];
	};
	$scope.closeModal = function () {
		$uibModalInstance.close();
	};
	$scope.cancelModal = function () {
		$uibModalInstance.dismiss('cancel');
	};

	$scope.removeEntry = function (index, list) {
		list.splice(index, 1);
	};
	$scope.isArray = angular.isArray;

	$scope.resourceTypesWithoutOptions = ["zoom_meeting", "microsoft_teams", "jamboard"];

	$scope.dto = $dateOperation;
	$scope.scs = $scheduleService;
	$scope.ls = $learningService;
	$scope.zs = $zoomService;
	$scope.ts = $teamsService;
	$scope.fs = $fileService;
	$scope.cs = $commentsService;
	$scope.dfs = $forumService; //discussion forum service
	$scope.scos = $scormService;
	$scope.ils = $instructorLeadService;
	$scope.crs = $credasService;
	$scope.ols = $outlookService;
	$scope.jmb = $jamboardService;

	// Some defaults
	$scope.today = getJsDate();
	$scope.today.setHours(0, 0, 0, 0);
	$scope.userDisplayLimit = 100;

	if ($rootScope.overBookingReminder === undefined) {
		$rootScope.overBookingReminder = true;
	}


	$scope.checkAllNotification = function (event,users){
		angular.forEach(users, function(user) {
			if (!$scope.event.users.send_email) {
				$scope.selectedUsers.push(user.id);
				user.checked = true;
			} else {
				$scope.selectedUsers.remove(user.id);
				user.checked = false;
			}
		});
		if (!$scope.event.users.send_email) {
			$scope.event.users.send_email = true;
		} else {
			$scope.event.users.send_email = false;
		}
	};

	$scope.checkNotification = function(user,event,users) {
		if (event.target.checked) {
			$scope.selectedUsers.push(user.id);
		} else {
			$scope.selectedUsers.remove(user.id);
		}
		if ($scope.selectedUsers.length === users.length) {
			$scope.event.users.send_email = true;
		} else {
			$scope.event.users.send_email = false;
		}
	};

	$scope.checkAllQueueNotification = function (event,users){
		angular.forEach(users, function(user) {
			if (!$scope.event.waiting.send_email) {
				$scope.selectedQueueUsers.push(user.id);
				user.checked = true;
			} else {
				$scope.selectedQueueUsers.remove(user.id);
				user.checked = false;
			}
		});
		if (!$scope.event.waiting.send_email) {
			$scope.event.waiting.send_email = true;
		} else {
			$scope.event.waiting.send_email = false;
		}
	};

	$scope.checkQueueNotification = function(user,event,users) {
		if (event.target.checked) {
			$scope.selectedQueueUsers.push(user.id);
		} else {
			$scope.selectedQueueUsers.remove(user.id);
		}
		if ($scope.selectedQueueUsers.length === users.length) {
			$scope.event.waiting.send_email = true;
		} else {
			$scope.event.waiting.send_email = false;
		}
	};




	$scope.setSelfLearning = function (link,resource){
		if(resource.schedule_link.instructor_lead){
			link.instructorLead.set(resource);
		}
		if(resource.schedule_link.completion_date_custom){
			link.homework.set(resource);
		}
	};

	$scope.zoomAdded = [];
	$scope.teamsAdded = [];

	// Retrieve event!
	$scope.getEvent = function (id, delay) {
		var delay_timeout = 0;
		if (delay) {
			delay_timeout = 1000;
		}

		// If delay is true, retieve event in 1000ms, if someone clicks multiple times on add/remove actions, remove timeout and create new one
		$timeout.cancel($scope.getEventTimeout);

		$scope.getEventTimeout = $timeout(function () {

			// If link add/removal is in progress, exit! Not sure it works properly...
			if ($scope.eventLinkLocked) {
				return false;
			}

			$http({
				method: 'GET',
				url: '<?=$LMSUri?>schedule/' + (id || $scope.event.id || $scope.passData.event.id) + ($scope.passData.deleted ? '/deleted' : '')
			}).then(function successCallback(response) {
				console.log(response);
				$scope.$broadcast('schedule-event-retrieved', response.data);
			}, function errorCallback(response) {
				$scope.event.loadError = response.data;
			});
		}, delay_timeout);
	};

	$scope.event = {
		duration: $scope.dto.addMinutes($scope.today, $rootScope.config.lessonDuration),
		lesson: 'new',
		visible_learner: true,
		disable_lesson:false,
		lesson_id: 'new',
		managers: [],
		loaded: false,
		instructor_lead: false,
		extended: {}
	};

	if ($scope.passData.event) {
		$scope.getEvent();
	} else {
		$scope.event.loaded = true;
	}

	$scope.deleteEvent = function () {
		$scope.event.delete = true;
		$scope.updateEvent();
	};
   $scope.disableLesson=function(){
	   $scope.event.disable_lesson=!$scope.event.disable_lesson
   }
	$scope.enroleAnyLearner = function () {
		$scope.event.enrole_any_learner = !$scope.event.enrole_any_learner;
	};
	$scope.managementApproval = function () {
		$scope.event.approval = !$scope.event.approval;
	};

	// Send data to server to create new event entry
	$scope.createEvent = function () {
		// Prevent double submit
		if (
			$scope.eventLocked ||
			$scope.event_id
		) {
			return false;
		}
		$scope.alerts = [];
		$scope.$broadcast('show-errors-check-validity');
		if ($scope.dateForm.$valid) {
			$scope.eventLocked = true;
			$http({
				method: 'POST',
				url: '<?=$LMSUri?>schedule/new',
				data: {
					name: $scope.event.name,
					cost: $scope.event.cost,
					type: $scope.event.type,
					visit_type_id: $scope.event.visit_type_id,
					disable_lesson:$scope.event.disable_lesson,
					description: $scope.event.description,
					location: $scope.event.location,
					start_date: $scope.event.start_date,
					end_date: $scope.event.end_date,
					duration: $scope.dto.getTimeMinutes($scope.event.duration),
					lesson_id: $scope.event.lesson_id, // If type is lesson and existing lesson is chosen, send ID here
					category_id: $scope.event.category_id,
					created_for: ($scope.passData.user_id || null),
					visible_learner: $scope.event.visible_learner,
					visible_learner_task: $scope.event.visible_learner_task,
					visible_schedule: $scope.event.visible_schedule,
					// If CD then he will send managers/resouces along, this will create multiple events for each manager!
					managers: $rootScope.rs.check('is_cd') ? $scope.event.managers : false,
					resources: $rootScope.rs.check('is_cd') ? $scope.event.resources : false,
					extended: $scope.event.extended
				}
			}).then(function successCallback(response) {
				$scope.event_id = response.data;

				// retrieve list of lessons
				$ajaxActions.multiGetRequests([
					['<?=$LMSUri?>learning/course/all', 'learning_courses'],
				], $rootScope);

				// If CD, close window!
				if ($rootScope.rs.check('is_cd')) {
					$scope.closeModal();
				} else {
					$scope.getEvent(response.data);
					$rootScope.$broadcast('schedule-event-created', response.data);
				}
				$scope.eventLocked = false;
			});
		} else {
			$scope.alerts = [{type: 'danger', msg: 'Please correct the errors.'}];
			$scope.eventLocked = false;
		}
	};

	// Send data to server to create new event entry
	$scope.updateEvent = function (id) {
		$scope.alerts = [];
		$scope.$broadcast('show-errors-check-validity');
		if ($scope.dateForm.$valid) {
			$scope.eventLocked = true;
			$http({
				method: 'PUT',
				url: '<?=$LMSUri?>schedule/' + (id || $scope.event.id),
				data: {
					name: $scope.event.name,
					cost: $scope.event.cost,
					//type: $scope.event.type,
					visit_type_id: $scope.event.visit_type_id,
					category_id: $scope.event.category_id,
					description: $scope.event.description,
					location: $scope.event.location,
					start_date: $scope.event.start_date,
					end_date: $scope.event.end_date,
					duration: $scope.dto.getTimeMinutes($scope.event.duration),
					visible_learner: $scope.event.visible_learner,
					visible_learner_task: $scope.event.visible_learner_task,
					visible_schedule: $scope.event.visible_schedule,
					delete: $scope.event.delete,
					extended: $scope.event.extended,
					enrole_any_learner: $scope.event.enrole_any_learner,
					approval: $scope.event.approval,
					minclass: $scope.event.minclass,
					maxclass: $scope.event.maxclass
				}
			}).then(function successCallback() {
				if ($scope.event.delete) {
					$rootScope.$broadcast('schedule-event-created');
					$scope.closeModal();
				} else {
					$scope.alerts = [{type: 'success', msg: 'Event is updated!'}];
					$timeout(function () {
						$uibModalInstance.close();
					}, 750);
				}
			});
		} else {
			$scope.eventLocked = false;
			$scope.alerts = [{type: 'danger', msg: 'Please correct the errors.'}];
		}
	};

	// Will trigger when chosing lesson, when creating event. Will update empty category selector with lesson category, if exists.
	$scope.updateEventFromLesson = function () {
		var lesson;
		if ($scope.event.lesson_id) {
			lesson = $filter('filter')($scope.learning_courses, {id: $scope.event.lesson_id})[0];
			if (lesson) {
				// When lesson is chosen, assign its category to event
				if (!$scope.event.category_id) {
					$scope.event.category_id = $filter('filter')($scope.learning_courses, {id: $scope.event.lesson_id})[0].category_id;
				}
				// Also update description, taken from lesson
				if (!$scope.event.description) {
					$scope.event.description = $filter('filter')($scope.learning_courses, {id: $scope.event.lesson_id})[0].description;
				}
			}
		}
	};

	// List of possible event types that can be added to this schedule, mix and match.
	$scope.link = {
		refreshTable: 0,
		selected: {},
		types: {},
		setType: function (type) {
			if (
				$scope.link.selected &&
				$scope.link.selected.slug === type.slug
			) {
				delete $scope.link.selected;
			} else {
				$scope.resetTableState = true;
				type.all = [];
				$scope.link.selected = type;
				$scope.link.refreshTable++;
			}
		},
		emptyType: function () {
			delete $scope.link.selected;
		},
		add: function (entry, id, schedule_id, type, disable_callback) {
			var link_id = (id || entry.id);
			schedule_id = schedule_id || $scope.event.id;
			type = type || $scope.link.selected.slug;

			if (
				link_id &&
				schedule_id &&
				type
			) {
				entry.updating = true;
				$scope.eventLinkLocked = true;
				$http({
					method: 'POST',
					url: '<?=$LMSUri?>schedule/link/new',
					data: {
						link_id: link_id,
						schedule_id: schedule_id,
						type: type
					}
				}).then(function successCallback (response) {
					$scope.eventLinkLocked = false;
					if (!disable_callback) {
						$scope.linkAdded = response.data;
						// Need to restore this, for now untill better solution is in place
						$scope.getEvent(false, true);
					}
					entry.updating = false;
				});
			} else {
				// Someone tried to add link without created event, pretend everything is ok!
				$scope.event[type] = $scope.event[type] || [];
				$scope.event[type].push(entry);
			}
		},
		remove: function (entry, type) {
			var i,
				j,
				type_original = type
			;
			type = type || $scope.link.selected.slug;

			if (
				entry.id &&
				$scope.event.id &&
				type
			) {
				$scope.eventLinkLocked = true;
				entry.updating = true;
				$http({
					method: 'POST',
					url: '<?=$LMSUri?>schedule/link/delete',
					data: {
						link_id: entry.id,
						schedule_id: $scope.event.id,
						type: type
					}
				}).then(function successCallback () {
					// if custom slug is given, means that entry is being removed from assigned location, not from smart table, refresh smart table after removing!
					$scope.eventLinkLocked = false;
					if (
						type_original &&
						$scope.link.selected &&
						$scope.link.selected.request
					) {
						$scope.link.refreshTable++;
					}
					// Need to restore this untill better solution is in place
					$scope.getEvent(false, true);
					entry.updating = false;
				});
			} else {
				// someone wants to remove added item from collection without created event, ok!
				$scope.event[type] = $scope.event[type] || [];
				// Look into collection to remove entry by ID
				for (i = $scope.event[type].length - 1; i >= 0; i--) {
					if ($scope.event[type][i].id === entry.id) {
						$scope.event[type].splice(i, 1);
						// Look into $scope.selected.all and remove selected from there also, if exists.
						if (
							$scope.link.selected &&
							$scope.link.selected.all &&
							$scope.link.selected.slug === type
						) {
							for (j = $scope.link.selected.all.length - 1; j >= 0; j--) {
								if ($scope.link.selected.all[j].id === entry.id) {
									$scope.link.selected.all[j].isSelected = false;
									$scope.link.selected.all[j].processed = false;
								}
							}
						}
					}
				}
			}
		},
		// Map selected standard issues with this schedule!
		mapToIssue: function (standard_id) {
			$uibModal.open({
				animation: true,
				ariaLabelledBy: 'modal-title',
				ariaDescribedBy: 'modal-body',
				templateUrl: '<?=$LMSTplsUriHTML?>modal-map-schedule-to-standard.html',
				controller: 'modalMapScheduleToStandard',
				size: 'lg',
				backdrop: 'static',
				resolve: {
					data: function () {
						return {
							standard_id: standard_id,
							schedule_id: $scope.event.id
						};
					}
				}
			});
		},
		managers: {
			updateVisitor: function (manager) {
				manager.disabled = true;
				$http({
					method: 'put',
					url: "<?=$LMSUri?>schedule/update-visitor-status/" + manager.manager_schedule_link.id
				}).then(function successCallback() {
					manager.manager_schedule_link.manager_visitor = !manager.manager_schedule_link.manager_visitor;
					manager.disabled = false;
				}, function errorCallback() {
					$scope.alerts = [{type: 'danger', msg: 'Could not complete action!'}];
					manager.disabled = false;
				});

			}
		},
		// User list operation
		users: {
			// Manager could authorise the absence of the user
			authorise: function (user) {
				$uibModal.open({
					animation: true,
					ariaLabelledBy: 'modal-title',
					ariaDescribedBy: 'modal-body',
					templateUrl: '<?=$LMSTplsUriHTML?>modal-authorisation-details.html',
					controller: 'ModalScheduleEvent',
					size: 'xs',

					backdrop: 'static',
					resolve: {
						data: function () {
							$scope.user_data = user;
							return $scope.user_data;
						}
					}
				});
			},

			changeStatus: function (user, completion_status) {
				$http({
					method: 'put',
					url: "<?=$LMSUri?>schedule/update-completion-status/" + user.schedule_link.id,
					data: {
						type: 'user',
						completion_status: completion_status
					}
				}).then(function successCallback() {
					user.schedule_link.completion_status = completion_status;
					user.schedule_link.authorisation_notes = null;
				});
			},
			changequeueStatus: function (user, completion_status) {
				$http({
					method: 'put',
					url: "<?=$LMSUri?>schedule/update-completion-status/" + user.waiting_schedule_link.id,
					data: {
						type: 'user',
						completion_status: completion_status
					}
				}).then(function successCallback() {
					user.waiting_schedule_link.completion_status = completion_status;
					user.waiting_schedule_link.authorisation_notes = null;
				});
			},
			changeListStatus: function (completion_status,users) {
				angular.forEach(users, function(user) {
					if (user.checked) {
						$scope.link.users.changeStatus(user,completion_status);
					}
				});
			},
			changeQueueListStatus: function (completion_status,users) {
				angular.forEach(users, function(user) {
					if (user.checked) {
						$scope.link.users.changequeueStatus(user,completion_status);
					}
				});
			},
			remove: function (users,type='users') {
				angular.forEach(users, function(user) {
					if (user.checked) {
						$scope.link.remove(user,type);
						$scope.selectedUsers.remove(user.id);
					}
				});
			},
			sendemail: function (users,ignore_email) {
				angular.forEach(users, function(user) {
					if (user.checked) {
						$http({
							method: 'POST',
							url: '<?=$LMSUri?>schedule/link/update',
							data: {
								id: user.schedule_link_id,
								ignore_email: ignore_email,
							}
						}).then(function successCallback () {
							user.notification = !ignore_email;
						});
					}
				});
			}

		},
		homework: {
			set: function (resource) {
				if(resource.schedule_link.instructor_lead){
					$scope.link.instructorLead.set(resource);
				}
				//if there is completion_date_custom, remove it, else show input!
				if (resource.schedule_link.completion_date_custom) {
					resource.schedule_link.completion_date_custom = null;
					$scope.link.homework.saveDate(resource);
				} else {
					$scope.link.homework.showDateInput(resource);
				}
			},
			showDateInput: function (resource) {
				if (!(resource.schedule_link.completion_date_custom instanceof Date)) {
					resource.schedule_link.completion_date_custom = getJsDate(resource.schedule_link.completion_date_custom);
					resource.schedule_link.completion_date_custom.setHours(9, 0, 0, 0);
				}
				resource.showCompletionDateInput = true;
			},
			saveDate: function (resource) {
				$http({
					method: 'PUT',
					url: "<?=$LMSUri?>schedule/set-homework/" + resource.id,
					data: {
						schedule_id: $scope.event.id,
						completion_date_custom: resource.schedule_link.completion_date_custom
					}
				}).then(function successCallback() {
					resource.showCompletionDateInput = false;
				});
			}
		},
		instructorLead: {
			set: function (resource) {
				/*Unchecking Homework*/
				if(resource.schedule_link.completion_date_custom){
					$scope.link.homework.set(resource);
				}
				$http({
					method: 'PUT',
					url: "<?=$LMSUri?>schedule/set-instructor-lead/" + resource.id,
					data: {
						schedule_id: $scope.event.id,
						instructor_lead: resource.schedule_link.instructor_lead
					}
				}).then(function successCallback() {
					resource.schedule_link.instructor_lead = !resource.schedule_link.instructor_lead;
					$scope.ils.isEventInstrutorLead($scope.event);
				});
			},
			// Show checkbox only for certain type of resources
			isAllowed: function (resource) {
				var response = false,
					allowed = [
						'e_learning',
						'youtube',
						'h5p',
						'webpage',
						'jamboard'
					]
				;
				if (allowed.indexOf(resource.type.slug) !== -1) {
					response = true;
				}
				return response;
			}
		}
	};

	$scope.lesson = {
		updateResourceOrder: function () {
			var new_order = [];
			angular.forEach($scope.event.resources, function(entry) {
				new_order.push(entry.id);
			});
			$http({
				method: 'put',
				url: "<?=$LMSUri?>schedule/update-lesson-resource-order/" + $scope.event.id,
				data: {
					new_order: new_order
				}
			});
		}
	};

	$scope.sortable = {
		firstTime: true,
		handle: ' .lesson-module-drag',
		start: function (e) {
			if ($scope.sortable.firstTime) {
				$(e.target).sortable('refreshPositions');
				$scope.sortable.firstTime = false;
			}
		},
		stop: function () {
			$scope.lesson.updateResourceOrder();
		}
	};

	$scope.addRepeatTime = function (repeat_till_end_holiday) {
		var new_date,
			new_entry;
		// Add week to the top of last date
		if ($scope.event.children.length > 0) {
			new_date = new Date(+$scope.event.children[$scope.event.children.length - 1].start_date);
			new_date.setDate(new_date.getDate() + 7);
			// add week to top of lesson start time
		} else {
			new_date = new Date(+$scope.event.start_date);
			new_date.setDate(new_date.getDate() + 7);
		}

		if (
			repeat_till_end_holiday &&
			$scope.nextFirstHoliday &&
			new_date >= $scope.nextFirstHoliday
		) {
			return;
		}

		new_entry = {
			start_date: new_date,
			duration: new Date(+$scope.event.duration),
			edit: false
		};
		$scope.event.children.push(new_entry);
		$scope.saveRepeatTime(new_entry);

		if (repeat_till_end_holiday) {
			$scope.addRepeatTime(true);
		}
	};

	$scope.saveRepeatTime = function (entry) {
		// Send data to DB
		if (entry.id) {
			$http({
				method: 'put',
				url: "<?=$LMSUri?>schedule/" + entry.id,
				data: {
					parent_id: $scope.event.id,
					name: entry.name,
					description: entry.description,
					start_date: entry.start_date,
					duration: $scope.dto.getTimeMinutes(entry.duration)
				}
			}).then(function successCallback() {
				entry.edit = false;
			});
		} else {
			$http({
				method: 'post',
				url: "<?=$LMSUri?>schedule/new",
				data: {
					parent_id: $scope.event.id,
					name: entry.name,
					description: entry.description,
					start_date: entry.start_date,
					duration: $scope.dto.getTimeMinutes(entry.duration)
				}
			}).then(function successCallback(response) {
				entry.id = response.data;
				entry.edit = false;
			});
		}
	};

	$scope.deleteRepeatTime = function (index, entry) {
		if (entry.id) {
			$http({
				method: 'delete',
				url: "<?=$LMSUri?>schedule/" + entry.id
			}).then(function successCallback() {
				$scope.removeEntry(index, $scope.event.children);
			});
		} else {
			$scope.removeEntry(index, $scope.event.children);
		}
	};



	$scope.callServer = function (tableState) {
		var key;
		$scope.link.selected.all = [];
		if ($scope.resetTableState) {

			for (key in tableState.search.predicateObject) {
				if (tableState.search.predicateObject.hasOwnProperty(key)) {
					if (
						key !== 'refresh' &&
						key !== 'schedule_id' &&
						key !== 'relationship' &&
						key !== 'status'
					) {
						delete tableState.search.predicateObject[key];
					}

					if ($scope.link.selected.slug === 'resources') {
						tableState.search.predicateObject.is_course = "0";
					}
				}
			}
			$scope.resetTableState = false;
		}
		if ($scope.link.selected.request) {
			updateReport(tableState, "<?=$LMSUri?>" + $scope.link.selected.request, $scope, $http, function() {
				angular.forEach($scope.data, function(entry) {
					var i;
					entry.isSelected = entry[$scope.link.selected.count_prop || 'schedules_count'] > 0;
					// IF event is not created and there is collection of link types added, check against ID's and highlight them!
					if (
						!$scope.event.id &&
						$scope.event[$scope.link.selected.slug] &&
						$scope.event[$scope.link.selected.slug].length > 0
					) {
						for (i = $scope.event[$scope.link.selected.slug].length - 1; i >= 0; i--) {
							if ($scope.event[$scope.link.selected.slug][i].id === entry.id) {
								entry.isSelected = true;
							}
						}
					}
					if ($scope.link.selected.slug === 'users') {
						if (
							!$scope.event.id &&
							$scope.event.waiting &&
							$scope.event.waiting.length > 0
						) {
							for (i = $scope.event.waiting.length - 1; i >= 0; i--) {
								if ($scope.event.waiting[i].id === entry.id) {
									entry.isSelected = true;
								}
							}
						}
					}
					entry.processed = entry.isSelected;
					entry.updating = false;
					this.push(entry);
				}, $scope.link.selected.all);
			});
		}
	};

	$scope.$watch('link.selected.all', function (entry) {
		if (entry !== undefined) {
			entry.filter(function (e) {
				if (e.isSelected && !e.processed) {
					$scope.link.add(e);
					e.processed = true;
				}
				if (!e.isSelected && e.processed) {
					$scope.link.remove(e);
					e.processed = false;
				}
			});
		}
	}, true);


	// IF Zoom resource is created, man to this schedule as resource
	$scope.$on('zoom-resource-created', function (logistics, zoom) {
		$scope.logistics = logistics;
		if (
			zoom &&
			zoom.resource_id
		) {
			$scope.link.add(false, zoom.resource_id, false, 'resources');
		}
	});

	// Teams!
	$scope.$on('teams-resource-created', function (logistics, teams) {
		$scope.logistics = logistics;
		if (
			teams &&
			teams.resource_id
		) {
			$scope.link.add(false, teams.resource_id, false, 'resources');
		}
	});

	// Jackdaw resource created!
	$scope.$on('jackdaw-resource-created', function (logistics, resource) {
		$scope.logistics = logistics;
		if (
			resource &&
			resource.id
		) {
			$scope.link.add(false, resource.id, false, 'resources');
		}
	});


	// Check if holidays are in rootscope and at least one of them are in future.
	$scope.processRepeatTime = function () {
		$scope.showAddRepeatTimes = false;
		$scope.nextStartDate = new Date(+$scope.event.start_date);
		$scope.nextStartDate.setDate($scope.nextStartDate.getDate() + 7);
		$scope.nextFirstHoliday = false;
		if (angular.isArray($rootScope.holidays)) {
			angular.forEach($rootScope.holidays, function (entry) {
				entry.start_date = getJsDate(entry.start_date);
				entry.end_date = getJsDate(entry.end_date);
				if (entry.start_date > $scope.nextStartDate) {
					if (
						!$scope.nextFirstHoliday ||
						$scope.nextFirstHoliday > entry.start_date
					) {
						$scope.nextFirstHoliday = entry.start_date;
					}
					$scope.showAddRepeatTimes = true;
				}
			});
		}
	};

	// Do whatever when schedule event is retrieved.
	$scope.$on('schedule-event-retrieved', function (listenerEvent, data) {
		var i,
			res
		;
		$scope.forum = data.forum;
		$scope.forum_add_status = (($scope.forum) ? false : true);
		$scope.event_id = data.id;
		$scope.listenerEvent = listenerEvent;

		data.start_date = getJsDate(data.start_date);
		data.end_date = getJsDate(data.end_date);
		data.duration = $scope.dto.addMinutes($scope.today, data.duration);

		// loop children and change start date and duration to date format
		angular.forEach(data.children, function (child) {
			child.start_date = getJsDate(child.start_date);
			child.end_date = getJsDate(child.end_date);
			child.duration = $scope.dto.addMinutes($scope.today, child.duration);
		});

          $scope.event = data;
		if (
			$scope.event.type === 'lesson' &&
			data.lessons &&
			data.lessons[0] &&
			data.lessons[0].id
		) {
                  $scope.event.lesson_id = data.lessons[0].id.toString();
                        $scope.event.disable_lesson=!data.lessons[0].hide_lesson;
			if (
				!$scope.event.category_id &&
				data.lessons[0].category_id
			) {
				$scope.event.category_id = data.lessons[0].category_id;
			}

			// If lesson and event created_by is different, show "Update resources from lesson" button
			$scope.showUpdateLessonResourcesButton = false;
			if (data.lessons[0].created_by !== $scope.event.created_by) {
				$scope.showUpdateLessonResourcesButton = true;
			}
		}


		if ($scope.event.type === 'meeting') {
			$scope.sortable.disabled = true;
		}

		$scope.fs.setConfig(
			{
				table_name: 'schedules',
				table_row_id: $scope.event.id,
				list: $scope.event.files,
				hide_uploaded: true,
				showForm: true,
				showFormLogic: false
			}
		);

		// Check if zoom/tems are added
		$scope.zoomAdded = [];
		$scope.teamsAdded = [];
		$scope.isAddedToOutlookCalendar = false;
		$scope.isJamboardAdded = false;
		$scope.jamboardLink = null;

		$scope.isAddedToOutlookCalendar = $scope.event.outlook_integration;

		for (i = $scope.event.resources.length - 1; i >= 0; i--) {
			res = $scope.event.resources[i];
			if (
				res &&
				res.type &&
				res.type.slug
			) {
				if (res.type.slug === 'zoom_meeting') {
					$scope.zoomAdded.push(res.id);
				}
				if (res.type.slug === 'microsoft_teams') {
					$scope.teamsAdded.push(res.id);
				}
				if (res.type.slug === 'jamboard') {
					$scope.isJamboardAdded = true;
					$scope.jamboardLink = res.material.link;
				}
			}
		}

		// Set up comment service
		$scope.cs.setConfig(
			{
				table_name: 'schedules',
				table_row_id: $scope.event.id,
				added_for: $scope.event.created_for || false
			}
		);

		$scope.visitTypeChanged(false);

		$scope.menuLink();
		$scope.processRepeatTime();

		$scope.event.resources.sort(function (a, b) {
			return a.schedule_link.order > b.schedule_link.order;
		});

		// If curent event time overlap other event times for some of pople, show warning.
		if (
			$scope.event.over_lap_event &&
			$rootScope.overBookingReminder
		) {
			$confirm({
				text: 'Some of the assigned people in this event have overlapping events with this time, please look at assigned people for details!',
				title: 'Overbooking problem.',
				ok: 'OK',
				cancel: 'Do not warn me again in this session.'
			}).then(function() {
				$scope.overbookingConfirm = true;
			}, function () {
				$rootScope.overBookingReminder = false;
			});
		}

		// Check if any resource is instructor lead and there is video conferencing, then mark event as so, to show "lead lesson" button
		$scope.ils.isEventInstrutorLead($scope.event);

		$scope.event.loaded = true;

		$rootScope.$broadcast('schedule-event-retrieved-processed', $scope.event);
	});

	/*
		Menu Function, set menu items to be displayed.
	*/
	$scope.menuLink = function () {
		$scope.link.types = [
			{
				name: 'Additional times',
				slug: 'add_times',
				all: [],
				request: false,
				status: false,
				fontIcon: 'glyphicon glyphicon-time',
				type: 'Misc'
			},
			{
				name: 'Zoom',
				slug: 'zoom',
				all: [],
				request: false,
				click: function () {
					if ($scope.teamsAdded.length > 0) {
						$confirm({
							text: 'Adding this meeting will lose the existing one, are you sure you want to continue?',
							title: 'Add Zoom meeting',
							ok: 'OK',
							cancel: 'Cancel'
						}).then(function () {
							var i;
							for (i = $scope.teamsAdded.length - 1; i >= 0; i--) {
								$scope.link.remove({id: $scope.teamsAdded[i]}, 'resources');
							}
							$scope.zs.new($scope.event.start_date, $scope.event.duration, $scope.event.name);
						});
					} else {
						$scope.zs.new($scope.event.start_date, $scope.event.duration, $scope.event.name);
					}
				},
				status: true,
				fontIcon: 'glyphicon glyphicon-facetime-video',
				type: 'No video conferencing',
				disabled: $scope.zoomAdded.length > 0
			},
			{
				name: 'Teams',
				slug: 'teams',
				all: [],
				request: false,
				click: function () {
					if ($scope.zoomAdded.length > 0) {
						$confirm({
							text: 'Adding this meeting will lose the existing one, are you sure you want to continue?',
							title: 'Add Teams meeting',
							ok: 'OK',
							cancel: 'Cancel'
						}).then(function () {
							var i;
							for (i = $scope.zoomAdded.length - 1; i >= 0; i--) {
								$scope.link.remove({id: $scope.zoomAdded[i]}, 'resources');
							}
							$scope.ts.new($scope.event.start_date, $scope.event.duration, $scope.event.name);
						});
					} else {
						$scope.ts.new($scope.event.start_date, $scope.event.duration, $scope.event.name);
					}
				},
				status: true,
				fontIcon: 'glyphicon glyphicon-list-alt',
				type: 'No video conferencing',
				disabled: $scope.teamsAdded.length > 0
			},
			{
				name: '%%learning_resources%%',
				slug: 'resources',
				all: [],
				request: 'learning/list',
				status: true,
				fontIcon: 'glyphicon glyphicon-book',
				type: 'Learning'
			},
			{
				name: '%%programmes%%',
				slug: 'programmes',
				all: [],
				request: 'apprenticeshipstandards/list',
				status: (!$rootScope.rs.hideCurriculum() && $scope.event.id !== undefined),
				fontIcon: 'glyphicon glyphicon-blackboard',
				type: 'Learning'
			},
			{
				name: '%%users%%',
				slug: 'users',
				all: [],
				request: 'user/list',
				status: $scope.event.created_for ? false : true,
				fontIcon: 'glyphicon glyphicon-user',
				type: 'People'
			},
			{
				name: '%%managers%%',
				slug: 'managers',
				all: [],
				request: 'manager/list',
				status: true,
				relationship: 'ManagerSchedules', // Custom eloquent relationship, sometimes one module might have multiple
				count_prop: 'manager_schedules_count', // Custom relationship brings custom counting
				fontIcon: 'glyphicon glyphicon-knight',
				type: 'People'
			},
			{
				name: 'Departments',
				slug: 'departments',
				all: [],
				request: 'department/list',
				status: $scope.event.created_for ? false : true,
				fontIcon: 'glyphicon glyphicon-th-large',
				type: 'People'
			},
			{
				name: 'Groups',
				slug: 'groups',
				all: [],
				request: 'group/list',
				status: $scope.event.created_for ? false : true,
				fontIcon: 'glyphicon glyphicon-th-list',
				type: 'People'
			},
			{
				name: 'Quiz',
				slug: 'quiz',
				all: [],
				request: false,
				click: function () {
					$scope.ls.createJackdawResource('quiz');
				},
				status: $scope.event.type === 'lesson',
				fontIcon: 'glyphicon glyphicon-check',
				type: 'Learning'
			},
			{
				name: 'File Uploads',
				slug: 'files',
				all: [],
				request: false,
				status: true,
				fontIcon: 'glyphicon glyphicon-file',
				type: 'Misc'
			},
			{
				name: 'Add comment',
				slug: 'comments',
				all: [],
				request: false,
				click: function () {
					$scope.cs.add();
				},
				status: true,
				fontIcon: 'glyphicon glyphicon-comment',
				type: 'Misc'
			},
			{
				name: 'Add Chat',
				slug: 'chat',
				all: [],
				request: false,
				click: function () {
					$scope.dfs.add($scope.event_id);
				},
				status: $scope.forum_add_status,
				fontIcon: 'glyphicon glyphicon-th-list',
				type: 'Misc'
			},
			{
				name: 'Add ' + ($scope.credas_process ? $scope.credas_process.name : ''),
				slug: 'credas-progress-review',
				all: [],
				request: false,
				click: function () {
					$scope.crs.addProgressReview($scope.event, $scope.credas_process);
				},
				status: $scope.event.type === 'meeting' && $rootScope.config.IncludeCredasForms && angular.isDefined($scope.credas_process),
				fontIcon: 'glyphicon glyphicon-signal',
				type: 'Misc'
			},
		];
	};

	// When CD, run this on first run, not only when loading event
	if ($rootScope.rs.check('is_cd')) {
		$scope.menuLink();
	}

	// Check if lesson has different resources, than this event, if so, add them to this event.
	$scope.updateLessonResources = function () {
		$http({
			method: 'GET',
			url: "<?=$LMSUri?>schedule/update-from-lesson/" + $scope.event.id
		}).then(function successCallback() {
			$scope.getEvent();
		});
	};

	// When visit type is changed, perform some actions, credas reviews are the ones in need!
	$scope.visitTypeChanged = function (reload_menu) {
		if ($scope.event.visit_type_id) {
			var visit_type = $filter('filter')($rootScope.schedule_visit_types, {id: $scope.event.visit_type_id})[0];
			if (visit_type.credas_process) {
				$scope.credas_process = visit_type.credas_process;
			} else {
				delete $scope.credas_process;
			}
			if (!reload_menu) {
				$scope.menuLink();
			}
		}
	};

	// New topic is created using this schedule event as a base, pull it!
	$scope.$on('schedule-new-topic-created', function (logistics, new_id) {
		$scope.logistics = logistics;
		$scope.getEvent(new_id);
	});

	// When files are added or deleted, refresh event details to reflect changes.
	$scope.$on('files-refresh', function () {
		$scope.getEvent();
	});

	$scope.$on('comments-refresh', function () {
		$scope.getEventComments();
	});

	$scope.$on('topic-refresh', function () {
		$scope.refreshForum++;
	});

	$scope.refreshForum = 0;
	$scope.$on('forum-refresh', function (listenerEvent, data) {
		$scope.forumListenerEvent = listenerEvent;
		if (data) {
			$scope.forum = data.data;
		} else {
			$scope.forum = null;
		}
		$scope.forum_add_status = (($scope.forum) ? false : true);
		$scope.menuLink();
		$scope.refreshForum++;
	});

	/*
	$scope.$on("schedule-event-created", function() {

	});
	*/

	$scope.videoPopup = null;
	$scope.checkVideoPromise = null;

	$scope.checkVideo = function (resource_id, resource_type) {

		$scope.checkVideoPromise = null;

		if (resource_type === "zoom_meeting"){
			$scope.videoPopup = $window.open("<?=$LMSUri?>/zoom/video/" + resource_id, "_blank", "width=600,height=400,name=Zoom");
		}
		if (resource_type === "microsoft_teams"){
			$scope.videoPopup = $window.open("<?=$LMSUri?>/teams/onedrive/" + resource_id, "_blank", "width=600,height=400,name=Teams");
		}

		$timeout(function () {
			$scope.checkVideoPromise = $interval(function () {
				if (!$scope.videoPopup || $scope.videoPopup.closed || ($scope.videoPopup.name && $scope.videoPopup.name === "close")) {
					$interval.cancel($scope.checkVideoPromise);
					$scope.getEvent(0);
				}
			}, 500);
		}, 500);

	};

	$scope.refreshTable = 0;

	$scope.callForumTable = function (tableState) {
		if ($scope.forum) {
			$scope.dfs.callTable(tableState, $scope);
		}
	};

	/*Comments Call*/
	$scope.getEventComments = function () {
		$http({
			method: 'GET',
			url: "<?=$LMSUri?>schedule/" + $scope.event_id + "/comment"
		}).then(function successCallback(response) {
			$scope.event.comments = response.data;
		});
	};


	$scope.changeVisbility = function (type, event) {
		if (type === "visible_learner_task") {
			data = event.visible_learner_task;
		} else if (type === "visible_learner") {
			data = event.visible_learner;
		} else if (type === "visible_learner") {
			data = event.visible_learner;
		}
		$http({
			method: 'POST',
			url: "<?=$LMSUri?>schedule/change_visibility/" + event.id,
			data: {
				val: !data,
				type: type,
			}
		}).then(function successCallback() {
			if (type === "visible_learner_task") {
				$scope.event.visible_learner_task = !$scope.event.visible_learner_task;
			} else if (type === "visible_learner") {
				$scope.event.visible_learner = !$scope.event.visible_learner;
			} else if (type === "visible_schedule") {
				$scope.event.visible_schedule = !$scope.event.visible_schedule;
			}
		});
	};



	/*
		Manager lead lesson block!
	*/
	// Manager will launch modal interface where he will be able to play instructor led resources
	$scope.leadLesson = function () {
		$uibModal.open({
			animation: true,
			ariaLabelledBy: 'modal-title',
			ariaDescribedBy: 'modal-body',
			templateUrl: '<?=$LMSTplsUriHTML?>modal-instructor-lead-lesson.html',
			controller: 'modalInstructorLeadLesson',
			size: 'lg',
			windowClass: 'u-modal--full-width',
			backdrop: 'static',
			resolve: {
				data: function () {
					return $scope.event;
				}
			}
		});
	};
	$scope.updateResourceStatus = function (resource){
			$http({
				method: 'PUT',
				url: '<?=$LMSUri?>learningusers/update-resource-status/user/'+$rootScope.currentUser.id+'/'+resource.id,
			}).then(function() {
				$rootScope.$broadcast('refresh-learning-resources', resource.id);
				$window.open($textOperations.urlPrefix(resource.material.link), '_blank');

			}, function errorCallback() {
			});
	};

	// Manager launches resource in scorm player that will sync with users assigned to this event
	$scope.$on('launch-resource', function(event, resource) {
		// Assign resource to manager who launches this resource, then procees
		$http({
			method: 'PUT',
			url: "<?=$LMSUri?>learningusers/user/" + $rootScope.currentUser.id,
			data: {
				module_ids:[resource.id],
				no_email: true
			}
		}).then(function successCallback() {
			$scope.launchResourceEvent = event;
			if (resource.type.slug === 'webpage') {
				$scope.updateResourceStatus(resource);
			}
			$scope.scos.launch(resource);
		});
	});

	// Manager closed $scope.scos.launch(args);
	// sync scorm data/learning_results with other users.
	$scope.$on('refresh-learning-resources', function(event, resource_id) {
		$scope.refreshLearningResourcesEvent = event;
		$timeout(function() {
			$http({
				method: 'GET',
				url: "<?=$LMSUri?>schedule/sync-resource/" + $scope.event.id + "/" + resource_id,
			}).then(function successCallback() {
				$scope.getEvent();
			});
		}, 1000);
	});

	/*
		EOF Manager lead lesson block!
	*/




	// Add comment ar URL from credas report
	$scope.$on('schedule-event-credas-report-sso', function(event, data) {
		$scope.scheduleEventCredasReportSsoEvent = event;
		$scope.cs.addComment($scope.event.id, 'schedules', data.url, data.learner_id, false, true);
	});

	$scope.saveAuthorisation = function () {
		if (!$scope.passData.schedule_link.id) {
			$scope.passData.waiting_schedule_link.authorisation_notes = $scope.passData.schedule_link.authorisation_notes;
			$scope.passData.schedule_link = $scope.passData.waiting_schedule_link;
		}
		if ($scope.passData.schedule_link.is_authorised === null) {
			$scope.passData.schedule_link.is_authorised = 0;
		}
		if (!$scope.passData.schedule_link.is_authorised){
			$scope.passData.schedule_link.is_authorised = 0;
		} else {
			$scope.passData.schedule_link.is_authorised = 1;
		}
		$scope.$broadcast('show-errors-check-validity');
		if ($scope.authorisationForm.$valid) {
			$http({
				method: 'PUT',
				url: "<?=$LMSUri?>schedule/update-completion-status/" + $scope.passData.schedule_link.id,
				data: {
					type: 'user',
					completion_status: 'Not Attempted',
					is_authorised:$scope.passData.schedule_link.is_authorised,
					authorisation_notes:$scope.passData.schedule_link.authorisation_notes
				}
			}).then(function successCallback() {
				$scope.passData.schedule_link.completion_status = 'Not Attempted';
				$scope.alerts = [{type: 'success', msg: 'Saved successfully!'}];
				$scope.closeModal();
			});
		} else {
			$scope.alerts = [{type: 'danger', msg: 'Please correct the errors!'}];
			$scope.perror_class = "form-group has-error";
		}

	};

	$scope.changeAuthorisation = function (){
		$scope.passData.schedule_link.is_authorised = !$scope.passData.schedule_link.is_authorised;
	};

	$scope.isAddedToOutlookCalendar = false;
	$scope.addAllEventstoOutlook = $rootScope.config.addAllEventstoOutlook;

	$scope.updateCalendar = function() {
		if (!$scope.isAddedToOutlookCalendar){
			if ($rootScope.config.enableGlobalOutlookIntegration){
				$http({
					method: 'GET',
					url: "<?=$LMSUri?>/teams/outlook?event_id=" + $scope.event.id
				}).then(function successCallback() {
					$scope.isAddedToOutlookCalendar = true;
				});	
			} else {
				$scope.ols.new($scope.event.id);
			}			
		} else {
			$http({
				method: 'DELETE',
				url: "<?=$LMSUri?>/teams/outlook/" + $scope.event.id
			}).then(function successCallback() {
				$scope.isAddedToOutlookCalendar = false;
			});
		}
	};

	$scope.$on('event-added-to-outlook', function() {
		$scope.isAddedToOutlookCalendar = true;
	});

	$scope.updateJamboard = function() {
		if (!$scope.isJamboardAdded){
			$scope.jmb.new($scope.event.name);
		}
	};

	$scope.$on('event-jamboard-added', function(event, data) {
		$scope.isJamboardAdded = true;
		$scope.link.add(false, data.resource_id, false, 'resources');
		$scope.jamboardLink = data.join_url;
	});


});
