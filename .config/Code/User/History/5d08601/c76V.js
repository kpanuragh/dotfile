angular.module('lmsApp').service('$listAction', function ($http, $uibModal, $log, $rootScope, $tinyMce, $fileService, $window, $timeout, $interval) {
	var $listAction = this;
	this.sendEmails = function ($scope) {
		if ($scope.tableState) {

				// Save e-mail into batch report table and send away!
				$http({
					method: 'POST',
					url: '<?=$LMSUri?>batch-report/new',
					data: {
						title: $scope.listAction.email.title,
						description: $scope.listAction.email.description,
						slug: $scope.listAction.baseUrl,
						table_state: generateParams($scope.tableState),
						table_state_original: JSON.stringify($scope.tableState),
						args: $scope.listAction.email.args,
						type: 'email',
						custom_review_id: ($scope.review && $scope.review.selected && $scope.review.selected.id) ? $scope.review.selected.id : null,
						frequency_pattern: JSON.stringify($scope.listAction.email.frequency.selected.pattern),
						copy_manager: $scope.listAction.email.copy_manager,
					}
				}).then(function successCallback(response) {
					var msg = ($scope.review && $scope.review.selected && $scope.review.selected.id) ? "This email will be sent in line with the schedule set. This  email report can be accessed again via the Batch Report link and will be repeated at the defined interval." : "This email will be sent in line with the schedule set.";
					$scope.batchReportResponse = response.data;
					$scope.alerts = [{ type: 'success', msg: msg }];
					$scope.listAction.showForm = false;

					// Attach files to report
					$scope.fs.conf.table_row_id = response.data;
					$scope.fs.upload();
				});

/*
			var confirmationF = function(response) {
					$scope.alerts = [{ type: 'success', msg: "This email will be sent in line with the schedule set. To " + response.data + " users." }];
				},
				failF =  function (response) {
					$scope.alerts = [{ type: 'danger', msg: "Emails failed to be sent: '" + response.data }];
				};
			$scope.listAction.showEmailForm = false;
			$scope.alerts = [{ type: 'info', msg: "Sending emails..." }];
			updateReport(
				$scope.tableState,
				$scope.listAction.emailUrl || ("<?=$LMSUri?>" + $scope.listAction.baseUrl +"/list/email"),
				$scope,
				$http,
				confirmationF,
				false,
				true,
				$scope.listAction.email,
				failF
			);
*/
		}
	};
	this.downloadReport = function ($scope) {
		updateReport(
			$scope.tableState,
			$scope.listAction.downloadUrl || ("<?=$LMSUri?>" + $scope.listAction.baseUrl +"/list/download"),
			$scope,
			$http,
			false,
			true
		);
	};
	this.setUpTableState = function ($scope, tableState) {
		$scope.tableState = tableState;
		$scope.tableStateEncoded = JSON.stringify($scope.tableState);
		$scope.lastTableState = tableState;
	};
	this.exportUsers = function ($scope) {
		// The filename format is as follows and is not case sensitive: ILR-LLLLLLLL-YYYY-yyyymmdd-hhmmss-NN.XML where: LLLLLLLL is the UK provider reference number (UKPRN) and NN is the serial number of the file. }
		var modalInstance = $uibModal.open({
				animation: true,
				ariaLabelledBy: 'modal-title',
				ariaDescribedBy: 'modal-body',
				templateUrl: '<?=$LMSTplsUriHTML?>ilr-export-confirm.html',
				controller: 'ModalInstanceCtrl', // modal window controller
				controllerAs: '$ctrl',
				size: 'lg',
				backdrop: 'static',
				resolve: {
					data: function () {
						return {
							type: 'ilr',
							mandatory_ni: true
							// test/dummy data
							/*type: 'ilr',
							ukprn: 11111111,
							ukprn_cp: 22222222,
							collection_year: 3333,
							nn: 44
							*/
						};
					}
				}
			})
		;

		modalInstance.result.then(function (data) {
			// submit export request

			var confirmationF = function(response) {
					$scope.alerts = [{ type: 'success', msg: "Export completed, " + response.data.users.success.length + " users exported." }];
					if (response.data.users.error.length > 0) {
						$scope.listAction.failedExportList = response.data.users.error;
						$scope.alerts.push({ type: 'danger', msg: 'Export failed for ' + response.data.users.error.length + ' users. Click <a href="" ng-click="listAction.showFailedExportReport()">here</a> to view report.' });
					}
					if (response.data.file_name) {
						window.location.href = '<?=$LMSUri?>download/' + data.type + '/' + response.data.file_name;
					} else {
						$scope.alerts.push({ type: 'danger', msg: 'Export failed, no users available or no users passed validation.' });
					}

				},
				failF =  function () {
					$scope.alerts = [{ type: 'danger', msg: "Export failed" }];
				};

			updateReport(
				$scope.tableState,
				'<?=$LMSUri?>export/' + data.type,
				$scope,
				$http,
				confirmationF,
				false,
				false,
				false,
				failF,
				true,
				{
					data: data,
					query: $scope.listAction.baseUrl,
				}
			);
		}, function () {
			$log.info('Export ILR dismissed at: ' + new Date());
		});
	};

	this.showFailedExportReport = function ($scope) {
		var modalInstance = $uibModal.open({
				animation: true,
				ariaLabelledBy: 'modal-title',
				ariaDescribedBy: 'modal-body',
				templateUrl: '<?=$LMSTplsUriHTML?>ilr-export-error-user-list.html',
				controller: 'ModalInstanceCtrl', // modal window controller
				controllerAs: '$ctrl',
				size: 'lg',
				backdrop: 'static',
				resolve: {
					data: function () {
						return {
							failedExportList: $scope.listAction.failedExportList
						};
					}
				}
			});
		modalInstance.result.then(function () {
			$log.info('Failed export user list window closed at: ' + new Date());
		}, function () {
			$log.info('Failed export user list window dismissed at: ' + new Date());
		});


	};


	this.defaults = function ($scope, baseUrl) {
		$scope.fs = $fileService;
		$scope.now = new Date();
		$scope.days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
		$scope.months = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
		$scope.listAction = {
			baseUrl: baseUrl,
			showForm: false,
                        send_to_manager:false,
                        sendToManager:function() {
                          console.log($scope.listAction.send_to_manager);
                          $scope.listAction.send_to_manager=!$scope.listAction.send_to_manager;
                        },
			email: {
				frequency : {
					list: [
						{
							name: 'Doesn\'t repeat',
							pattern: false
						},
						{
							name: 'Daily',
							pattern: {
								frequency: 1,
								period: {
									name: 'Day',
									id: 'every_day'
								}
							}
						},
						{
							name: 'Weekly on ' + $scope.days[$scope.now.getDay()],
							pattern: {
								frequency: 1,
								period: {
									name: 'Week',
									id: 'every_week'
								},
								day: [$scope.now.getDay()]
							}
						},
						{
							name: 'Monthly on the first ' + $scope.days[$scope.now.getDay()],
							pattern: {
								frequency: 1,
								period: {
									name: 'Month',
									id: 'every_month'
								},
								day: [$scope.now.getDay()]
							}
						},
						{
							name: 'Annually on ' + $scope.months[$scope.now.getMonth()] + ' ' + $scope.now.getDate(),
							pattern: {
								frequency: 1,
								period: {
									name: 'Year',
									id: 'every_year'
								},
								date: [$scope.now.getDate()],
								month: [$scope.now.getMonth()]
							}
						},
						{
							name: 'Every weekday (Monday to Friday)',
							pattern: {
								frequency: 1,
								period: {
									name: 'Week',
									id: 'every_week'
								},
								day: [1, 2, 3, 4, 5]
							}
						},
						{
							name: 'Custom...',
						}
					],
					set: function (frequency, pattern) {
						$scope.listAction.email.frequency.selected = frequency;
						if (pattern) {
							$scope.listAction.email.frequency.selected.pattern = pattern;
						}
						if (frequency.name === 'Custom...') {
							$scope.listAction.email.frequency.customPattern(pattern);
						}
					},
					customPattern: function (pattern) {
						var modalInstance = $uibModal.open({
							animation: true,
							ariaLabelledBy: 'modal-title',
							ariaDescribedBy: 'modal-body',
							templateUrl: '<?=$LMSTplsUriHTML?>modal-email-frequency.html',
							controller: 'ModalEmailFrequency',
							size: 'lg',
							backdrop: 'static',
							resolve: {
								data: function () {
									return {
										pattern: pattern || ($scope.listAction.email.frequency.selected.pattern || false)
									};
								}
							}
						});
						modalInstance.result.then(function (data) {
							$scope.listAction.email.frequency.selected.pattern = data;
						});
					}
				},
				show: function () {
					$scope.listAction.showForm = $scope.listAction.showForm === 'email' ? false : 'email';
					if ($scope.listAction.showForm === 'email') {
						//reload fileservice
						$scope.fs.setConfig(
							{
								table_name: 'batch_reports',
								list: []
							}
						);
					}
				}
			},
			fck: {
				options: {
					language: 'en',
					allowedContent: true,
					entities: false
				},
				//onReady: function () {}
			},
			sendEmails: function () {
				if (baseUrl === 'taskassessmentreports') {
					$scope.listAction.emailUrl = '<?=$LMSUri?>taskassessmentreports/' + $scope.additionalSearchParams.report + '/list/' + $scope.assessment_id + '/email';
				}
				$listAction.sendEmails($scope);
			},
			downloadReport: function () {
				if (baseUrl === 'taskassessmentreports') {
					$scope.listAction.downloadUrl = '<?=$LMSUri?>taskassessmentreports/' + $scope.additionalSearchParams.report + '/list/' + $scope.assessment_id + '/download';
				}
				$listAction.downloadReport($scope);
			},
			invoice: {

			},
			powerbi: {
				exporting: false,
				exported: false,
				report_type: 0,
				datasetsLoaded: false,
				datasets: [],
				show: function () {
					$scope.listAction.showForm = $scope.listAction.showForm === 'powerbi' ? false : 'powerbi';
					if ($scope.listAction.powerbi.exporting === 2) {
						$scope.listAction.powerbi.exporting = false;
					}
				},
				powerBiPopup: null,
				checkPowerBiPopupPromise: null,
				loggingIn: false,
				loginToPowerBi: function(callbackOnlogin) {

					$scope.listAction.powerbi.loggingIn = true;					
					$http.get('<?=$LMSUrl?>powerbi/hastoken')
						.then(
							function successCallback(response) {
								$scope.listAction.powerbi.loggingIn = false;
								callbackOnlogin();
							},
							function notLoggedInCallback(response){
								if ($scope.listAction.powerbi.powerBiPopup){
									$scope.listAction.powerbi.powerBiPopup.focus();
								} else {
									$scope.listAction.powerbi.powerBiPopup = $window.open(
										"<?=$LMSUrl?>/powerbi?",
										"_blank",
										"width=600,height=400,name=PowerBi"
									);	
								}
								$timeout(function() {
									$scope.listAction.checkPowerBiPopupPromise = $interval( function(){ 
			
										var popup = $scope.listAction.powerbi.powerBiPopup;
										if (!popup) {
											$scope.listAction.powerbi.loggingIn = false;
											$interval.cancel($scope.listAction.checkPowerBiPopupPromise);
											$scope.listAction.checkPowerBiPopupPromise = null;
											$scope.listAction.powerbi.powerBiPopup = null;

										}
										if (popup.name == "close") {
			
											$interval.cancel($scope.listAction.checkPowerBiPopupPromise);
											$scope.listAction.checkPowerBiPopupPromise = null;			
											$scope.listAction.powerbi.loggingIn = false;
											$scope.listAction.powerbi.powerBiPopup = null;

											callbackOnlogin();
										}							
									}, 500);
								}, 500);											
							}
						);
				},
				loadDatasets: function(){

					$scope.listAction.powerbi.loginToPowerBi(function () {
						$http({
							method: 'GET',
							url: '<?=$LMSUrl?>powerbi/datasets/' + $scope.listAction.baseUrl,
						}).then(function successCallback(response) {
							$scope.listAction.powerbi.datasets = response.data;
							$scope.listAction.powerbi.datasetsLoaded = true;							
						});							
					});
				},
				export: function() {

					$scope.listAction.powerbi.exporting = 1;
					$scope.listAction.powerbi.loginToPowerBi(function () {
						updateReport(
							$scope.tableState,
							//$scope.listAction.downloadUrl || ("<?=$LMSUri?>" + $scope.listAction.baseUrl +"/list/powerbi"),
							"<?=$LMSUrl?>/powerbi/exportall",
							$scope,
							$http,
							function () {
								$scope.listAction.powerbi.exporting = 2;
							},
							false,
							false,
							false,
							false, //callbackError,
							true, //exporting,
							$scope.listAction.powerbi.dataset//exportConfig
						);
	
						return false;												
					});


					//alert($scope.listAction.powerbi.dataset);
					return false;
				},
				args: false
			},
			print: {
				show: function () {
					$scope.listAction.showForm = $scope.listAction.showForm === 'print' ? false : 'print';
				},
				args: false,
				saveReport: function (do_not_save_report) {
					do_not_save_report = do_not_save_report || false; // In case you want only to print it out!
					var slug = $scope.listAction.baseUrl;
					if (
						!do_not_save_report
					) {

						$http({
							method: 'POST',
							url: '<?=$LMSUri?>batch-report/new',
							data: {
								title: $scope.listAction.print.title,
								description: $scope.listAction.print.description,
								slug: slug,
								table_state: generateParams($scope.tableState),
								table_state_original: JSON.stringify($scope.tableState),
								args: $scope.listAction.print.args,
								type: 'report',
								custom_review_id: ($scope.review && $scope.review.selected && $scope.review.selected.id) ? $scope.review.selected.id : null,
								frequency_pattern: JSON.stringify($scope.listAction.email.frequency.selected.pattern)
							}
						}).then(function successCallback(response) {
							$scope.batchReportResponse = response.data;
							if ($scope.review && $scope.review.selected && $scope.review.selected.id) {
								$scope.alerts = [{ type: 'success', msg: "This report can be accessed again via the Batch Report link and will be repeated at the defined interval." }];
							}
							$scope.listAction.showForm = false;
						});

					}
				}
			},
			reports: {
				showStatus: true,
				show: function () {
					$scope.listAction.showForm = $scope.listAction.showForm === 'reports' ? false : 'reports';
					if (
						$scope.listAction.showForm === 'reports'
					) {
						$scope.listAction.reports.getList();
					}
				},
				getList: function () {
					$scope.listAction.reports.list = [];
					if (
						$scope.review &&
						$scope.review.selected
					) {
						$http({
							method: 'POST',
							url: '<?=$LMSUri?>batch-report/list',
							data: {
								slug: $scope.listAction.baseUrl, // show only reports from this review!
								custom_review_id: ($scope.review && $scope.review.selected && $scope.review.selected.id) ? $scope.review.selected.id : null,
								page: 1,
								nPage: 1000,
								search: {},
								sort: {},
								export_config: ""
							}
						}).then(function successCallback(response) {
							$scope.listAction.reports.list = response.data.data;
						});
					}
				},
				setStatus: function (report, state) {
					$http({
						method: 'PUT',
						url: '<?=$LMSUri?>batch-report/' + state + '/' + report.id,
					}).then(function successCallback() {
						report.status = state === 'enable' ? true :  false;
					});
				},
				view: function (report) {
					$uibModal.open({
						animation: true,
						ariaLabelledBy: 'modal-title',
						ariaDescribedBy: 'modal-body',
						templateUrl: '<?=$LMSTplsUriHTML?>modal-view-report.html',
						controller: 'ModalViewReport',
						size: 'lg',
						backdrop: 'static',
						resolve: {
							data: function () {
								return {
									report: report
								};
							}
						}
					});
				},
				edit: function (report) {
					$uibModal.open({
						animation: true,
						ariaLabelledBy: 'modal-title',
						ariaDescribedBy: 'modal-body',
						templateUrl: '<?=$LMSTplsUriHTML?>modal-edit-report.html',
						controller: 'ModalEditReport',
						size: 'lg',
						backdrop: 'static',
						resolve: {
							data: function () {
								return {
									report_id: report.id
								};
							}
						}
					});
				}
			},
			emailFormInit: function () {
				$scope.listAction.emailformInit = true;
			},
			// Export ILR XML or some other format, lets start with ILR
			exportUsers: function () {
				$listAction.exportUsers($scope);
			},
			showFailedExportReport: function () {
				$listAction.showFailedExportReport($scope);
			},
			canExport: function () {
				var i,
					response = false;
				for (i = $rootScope.currentUser.role.pages.length - 1; i >= 0; i--) {
					if ($rootScope.currentUser.role.pages[i].link === 'export') {
						response = true;
					}
				}
				return response;
			},
			hidePrint: false,
			hideDownload: false,
			hideEmail: false,
			hideExport: true,
			hidePowerBi: true
		};
		$scope.listAction.email.frequency.selected = $scope.listAction.email.frequency.list[0];
		// run this only on listing pages.
		if (baseUrl) {
			$tinyMce.init($scope, true);
		}

		$scope.$on("batch-report-updated",function() {
			$scope.listAction.reports.getList();
		});
		$scope.$on('refresh-unread-batch-reports', function(){
			$scope.listAction.reports.getList();
		});

	};
});
