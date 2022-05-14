angular.module('lmsApp').service('$learningService', function($uibModal, $rootScope, $http) {
	// Leave Feedback form popup
	this.editResource = function(resource) {
		$uibModal.open({
			animation: true,
			ariaLabelledBy: 'modal-title',
			ariaDescribedBy: 'modal-body',
			templateUrl: '<?=$LMSTplsUriHTML?>modal-add-edit-resource.html',
			controller: 'ModalEditResource',
			size: 'lg',
			windowClass: 'u-modal--full-width',
			backdrop: 'static',
			resolve: {
				data: function () {
					return {
						resource: resource
					};
				}
			}
		});
	};
	// Block where companies are added and removed from user
	this.company = {
		remove: function (company_id, resource) {
			var i;
			if (resource.id) {
				$http({
					method: 'DELETE',
					url: '<?=$LMSUri?>company/resource/' + company_id + '/' + resource.id
				}).then(function successCallback() {
					for (i = resource.companies.length - 1; i >= 0; i--) {
						if (resource.companies[i].id === company_id) {
							resource.companies.splice(i, 1);
						}
					}
				});
			} else {
				for (i = resource.companies.length - 1; i >= 0; i--) {
					if (resource.companies[i].id === company_id) {
						resource.companies.splice(i, 1);
					}
				}
			}
		},
		add: function (company, resource) {
			// If resource is being edited, add directly to database, else add in resource.companies array that will be processed when submitting resource in Database
			if (resource.id) {
				$http({
					method: 'PUT',
					url: '<?=$LMSUri?>company/' + company.id + '/resource/' + resource.id
				}).then(function successCallback() {
					resource.companies.push(company);
				});
			} else {
				resource.companies = resource.companies || [];
				resource.companies.push(company);
			}
		},
		exists: function (company, resource) {
			if (company && resource && resource.companies) {
				var i;
				for (i = resource.companies.length - 1; i >= 0; i--) {
					if (resource.companies[i].id === company.id) {
						return true;
					}
				}
			}
		}
	};
	this.selectVersionPopup = function(resource){
		$uibModal.open({
			animation: true,
			ariaLabelledBy: 'modal-title',
			ariaDescribedBy: 'modal-body',
			templateUrl: '<?=$LMSTplsUriHTML?>modal-select-version.html',
			controller: 'ModalEditResource',
			size: 'lg',
			// windowClass: 'u-modal--full-window',
			backdrop: 'static',
			resolve: {
				data: function () {
					return {
						resource: resource
					};
				}
			}
		});
	};

	this.duplicateResource = function(resource) {
		$uibModal.open({
			animation: true,
			ariaLabelledBy: 'modal-title',
			ariaDescribedBy: 'modal-body',
			templateUrl: '<?=$LMSTplsUriHTML?>modal-duplicate-resource.html',
			controller: 'ModalDuplicateResource',
			size: 'md',
			// windowClass: 'u-modal--full-width',
			backdrop: 'static',
			resolve: {
				data: function () {
					return {
						resource: resource
					};
				}
			}
		});
	};

	this.addResource = function (type, module_id) {
		$uibModal.open({
			animation: true,
			ariaLabelledBy: 'modal-title',
			ariaDescribedBy: 'modal-body',
			templateUrl: '<?=$LMSTplsUriHTML?>modal-add-edit-resource.html',
			controller: 'ModalAddResource',
			size: 'lg',
			windowClass: 'u-modal--full-width',
			backdrop: 'static',
			resolve: {
				data: function () {
					return {
						type: type,
						module_id: module_id
					};
				}
			}
		});
	};

	this.addResourceFromLibrary = function() {
		$uibModal.open({
			animation: true,
			ariaLabelledBy: 'modal-title',
			ariaDescribedBy: 'modal-body',
			templateUrl: '<?=$LMSTplsUriHTML?>modal-add-resource-from-library.html',
			controller: 'ModalAddResourceFromLibrary',
			size: 'lg',
			windowClass: 'u-modal--full-width',
			backdrop: 'static',
			resolve: {
				data: function () {
					return {};
				}
			}
		});
	};

	// Add GO1 resources into our library.
	this.addResourceFromGo1Library = function () {
		$uibModal.open({
			animation: true,
			ariaLabelledBy: 'modal-title',
			ariaDescribedBy: 'modal-body',
			templateUrl: '<?=$LMSTplsUriHTML?>modal-add-resource-from-go1-library.html',
			controller: 'ModalAddResourceFromGo1Library',
			size: 'lg',
			windowClass: 'u-modal--full-width',
			backdrop: 'static',
			resolve: {
				data: function () {
					return {};
				}
			}
		});
	};

	this.jackdaw = function (learning, learning_id) {
		var modalInstance = $uibModal.open({
			animation: true,
			ariaLabelledBy: 'modal-title',
			ariaDescribedBy: 'modal-body',
			templateUrl: '<?=$LMSTplsUriHTML?>modal-jackdaw.html',
			controller: 'ModalJackdawController',
			size: 'lg',
			keyboard: false,
			backdrop: 'static',
			windowClass: 'u-modal--full-width',
			resolve: {
				data: function () {
					return {
						learning: learning,
						learning_id: learning_id
					};
				}
			}
		});

		modalInstance.result.then(function () {
			$rootScope.$broadcast('modal-jackdaw-closed');
		}, function () {
			$rootScope.$broadcast('modal-jackdaw-closed');
		});
	};

	this.createH5P = function () {
		$uibModal.open({
			animation: true,
			ariaLabelledBy: 'modal-title',
			ariaDescribedBy: 'modal-body',
			templateUrl: '<?=$LMSTplsUriHTML?>create-hp5-iframe.html',
			controller: 'ModalH5P',
			size: 'lg',
			windowClass: 'u-modal--full-width',
			backdrop: 'static',
			resolve: {
				data: function () {
					return {};
				}
			}
		});
	};

	this.viewH5P = function (learning_id) {
		$uibModal.open({
			animation: true,
			ariaLabelledBy: 'modal-title',
			ariaDescribedBy: 'modal-body',
			templateUrl: '<?=$LMSTplsUriHTML?>view-hp5-iframe.html',
			controller: 'ModalH5P',
			size: 'lg',
			windowClass: 'u-modal--full-width',
			backdrop: 'static',
			resolve: {
				data: function () {
					return {
						learning_id: learning_id
					};
				}
			}
		});
	};


	this.jackdawHtml5 = function (learning, learning_id) {
		var openJackdawModal = function (learning) {
			var modalInstance = $uibModal.open({
					animation: true,
					ariaLabelledBy: 'modal-title',
					ariaDescribedBy: 'modal-body',
					templateUrl: '<?=$LMSTplsUriHTML?>jackdawHtml5-iframe.html',
					controller: 'ModalJackdawHtml5', // modal window controller
					size: 'lg',
					windowClass: 'u-modal--full-window',
					backdrop: 'static',
					resolve: {
						data: function () {
							return {
								learning: learning,
								learning_id: learning_id
							};
						}
					}
				});
			modalInstance.result.then(function () {
				$rootScope.$broadcast("resource-list-needs-refresh");
			}, function () {
				$rootScope.$broadcast("resource-list-needs-refresh");
			});
		};
		// Tell server to check if resource needs updating from sample folder, after that open jackdaw!
		if (learning) {
			$http({
				method: 'GET',
				url: '<?=$LMSUri?>jackdaw/update-room-files/' + learning.id
			}).then(function successCallback() {
				openJackdawModal(learning);
			});
		} else {
			openJackdawModal(learning);
		}
	};



	// Create jackdaw resource with specific type and givin name, broadcast it, then open in editor!
	this.createJackdawResource = function (type) {
		$uibModal.open({
			animation: true,
			ariaLabelledBy: 'modal-title',
			ariaDescribedBy: 'modal-body',
			templateUrl: '<?=$LMSTplsUriHTML?>modal-create-jackdaw-resource-name-type.html',
			controller: 'ModalCreateJackdawResourceNameType', // modal window controller
			size: 'md',
			backdrop: 'static',
			resolve: {
				data: function () {
					return {
						type: type
					};
				}
			}
		});
	};


	this.viewResource = function (id) {
		$uibModal.open({
			animation: true,
			ariaLabelledBy: 'modal-title',
			ariaDescribedBy: 'modal-body',
			templateUrl: '<?=$LMSTplsUriHTML?>modal-view-learning-resource.html',
			controller: 'ModalViewLearningResourceController',
			size: 'lg',
			windowClass: 'u-modal--full-width',
			backdrop: 'static',
			resolve: {
				data: function () {
					return {
						id: id
					};
				}
			}
		});
	};

	this.assignLearning = function () {
		$uibModal.open({
			animation: true,
			ariaLabelledBy: 'modal-title',
			ariaDescribedBy: 'modal-body',
			templateUrl: '<?=$LMSTplsUriHTML?>modal-assign-learning.html',
			controller: 'ModalAssignLearning',
			size: 'lg',
			windowClass: 'u-modal--full-width',
			backdrop: 'static',
			resolve: {
				data: function () {
					return {};
				}
			}
		});
	};

	this.updateStatus = function(learningId, status) {
		$http({
			method: 'PUT',
			url: "<?=$LMSUri?>learning/" + status + "/" + learningId
		}).then(function successCallback() {
			$rootScope.$broadcast("resource-list-needs-refresh");
		});
	};

	// Create upload/evidence resource, assign to user and add it to library
	this.setWork = function (user_id) {
		$uibModal.open({
			animation: true,
			ariaLabelledBy: 'modal-title',
			ariaDescribedBy: 'modal-body',
			templateUrl: '<?=$LMSTplsUriHTML?>modal-set-work.html',
			controller: 'ModalSetWork',
			size: 'lg',
			windowClass: 'u-modal--full-width',
			backdrop: 'static',
			resolve: {
				data: function () {
					return {
						user_id: user_id
					};
				}
			}
		});
	};

	this.importYoutubePlaylist = function() {
		$uibModal.open({
			animation: true,
			ariaLabelledBy: 'modal-title',
			ariaDescribedBy: 'modal-body',
			templateUrl: '<?=$LMSTplsUriHTML?>modal-import-youtube-playlist.html',
			controller: 'ModalImportYoutubePlaylist',
			size: 'lg',
			backdrop: 'static',
			resolve: {
				data: function () {
					return {};
				}
			}
		});
	};

	this.mapResourceToStandard = function(resources) {
		if (!angular.isArray(resources)) {
			resources = [resources];
		}
		$uibModal.open({
			animation: true,
			ariaLabelledBy: 'modal-title',
			ariaDescribedBy: 'modal-body',
			templateUrl: '<?=$LMSTplsUriHTML?>modal-map-resources-to-standard.html',
			controller: 'ModalMapResourcesToStandard',
			size: 'lg',
			backdrop: 'static',
			resolve: {
				data: function () {
					return {
						resources: resources
					};
				}
			}
		});
	};

	// Create/edit lesson
	this.openLesson = function (id) {
		$uibModal.open({
			animation: true,
			ariaLabelledBy: 'modal-title',
			ariaDescribedBy: 'modal-body',
			templateUrl: '<?=$LMSTplsUriHTML?>modal-add-edit-entry.html',
			controller: 'ModalAddEditEntry',
			size: 'lg',
			windowClass: 'u-modal--full-width',
			backdrop: 'static',
			resolve: {
				data: function () {
					return {
						name: '%%lessons%%',
						slug: 'learning/course',
						id: id,
						update_method: 'POST',
						file_service: true,
						file_service_table_name: 'learning_modules',
						fields: [

							{
								name: 'Learning %%lesson%% Name',
								key: 'name',
								required: true,
								type: 'text',
								enabled: true
							},
							{
								name: 'Category',
								key: 'category_id',
								required: true,
								type: 'selectbox',
								list: $rootScope.categories,
								enabled: true
							},
							{
								name: 'F&P Activity Category',
								key: 'f_p_category_id',
								required: false,
								type: 'selectbox',
								list: $rootScope.fp_categories,
								enabled: $rootScope.isSMCR
							},
							{
								name: 'Learning %%lesson%% Code',
								key: 'code',
								required: false,
								type: 'text',
								enabled: true
							},
							{
								name: 'Keywords',
								key: 'keywords',
								required: false,
								type: 'text',
								enabled: true
							},
							{
								name: 'Available for Self Enrolment',
								key: 'self_enroll',
								required: false,
								type: 'checkbox',
								enabled: true
							},
							{
								name: 'Require Management approval',
								key: 'approval',
								required: false,
								type: 'checkbox',
								enabled: true
							},
							{
								name: 'Print certificate once lesson completed',
								key: 'print_lesson',
								default: true,
								required: false,
								type: 'checkbox',
								enabled: true
							},
							{
								name: 'Company Access',
								small: 'select to restrict',
								key: 'company_id',
								required: false,
								type: 'selectbox',
								list: $rootScope.companies,
								enabled: true
							},
							{
								name: 'Days till %%lesson%% is due',
								key: 'due_after_period',
								required: false,
								type: 'number',
								default: 7,
								enabled: true
							},
							{
								name: 'Description',
								key: 'description',
								required: false,
								type: 'textarea',
								enabled: true
							},
							{
								name: 'Thumbnail image',
								small: '150px height recommended, maximum size: 200KB',
								path: '<?=$LMSUrl;?>/images/thumbnails/',
								key: 'thumbnail',
								required: false,
								type: 'file_image',
								enabled: true
							},
							{
								name: 'Promo image',
								small: 'large background image, maximum size: 500KB',
								path: '<?=$LMSUrl;?>/images/promo/',
								key: 'promo_image',
								required: false,
								type: 'file_image',
								enabled: true
							},
							{
								name: 'Highlight image',
								small: '(maximum size: 600KB)',
								path: '<?=$LMSUrl;?>/images/highlight/',
								key: 'highlight_image',
								required: false,
								type: 'file_image',
								enabled: true
							},
							{
								name: 'Uploads',
								required: false,
								type: 'include_html',
								include: '<?=$LMSTplsUriHTML;?>entry-attachments.html',
								custom_block: true,
								enabled: true
							},
							{
								name: 'Custom Certificate Resources',
								type: 'header',
								custom_block: true,
								enabled: true
							},
							{
								name: 'Main Logo',
								small: 'leave blank to use company default',
								path: '<?=$LMSUrl;?>/images/accreditation/',
								key: 'accreditation_main_logo',
								required: false,
								type: 'file_image',
								enabled: true
							},
							{
								name: 'Accreditation Logo',
								small: 'Max 300 x 300',
								path: '<?=$LMSUrl;?>/images/accreditation/',
								key: 'accreditation_logo',
								required: false,
								type: 'file_image',
								enabled: true
							},
							{
								name: 'Text below Accreditation Logo',
								small: 'Max 100 words',
								key: 'accreditation_description',
								required: false,
								type: 'textarea',
								enabled: true
							},
							{
								name: 'Assign %%learning_resources%%',
								type: 'header',
								custom_block: true,
								enabled: true
							},
							{
								name: 'Assigned %%learning_resources%%',
								required: false,
								key: 'modules',
								type: 'include_html',
								include: '<?=$LMSTplsUriHTML?>assigned-learning-resources.html',
								custom_block: true,
								encode: true, // this will be json object, encode it!,
								cast: 'array',
								enabled: true
							},
							{
								name: '%%learning_resources%% to be Completed in Fixed Order',
								key: 'order_modules',
								required: false,
								type: 'checkbox',
								enabled: true
							},
							{
								name: 'Resource Query Builder',
								required: false,
								type: 'include_html',
								include: '<?=$LMSTplsUriHTML;?>resource-query-builder--lesson.html',
								custom_block: true,
								enabled: true
							},
						]
					};
				}
			}
		});
	};

	/*Import Legacy Course Data Modal*/
	this.importCourseModal = function () {
		$uibModal.open({
			animation: true,
			ariaLabelledBy: 'modal-title',
			ariaDescribedBy: 'modal-body',
			templateUrl: '<?=$LMSTplsUriHTML?>modal-import-course-data.html',
			controller: 'ModalImportCourseData',
			size: 'lg',
			windowClass: 'u-modal--full-width',
			backdrop: 'static',
			resolve: {
				data: function () {
					return {};
				}
			}
		});
	};

	this.formatTime = function (minutes) {
		var h = Math.floor(minutes / 60);
		var m = minutes % 60;
		h = h < 10 ? '0' + h : h; 
		m = m < 10 ? '0' + m : m; 
		return h + ' hours ' + m+ ' minutes';
	  }
});
