<?php
namespace DB;

class EmailTemplates {
	public static function update ($settings = false) {

		// Handle renaming email templates
		$rename_templates = [
			'Jackdaw Free Licence Activated' => [
				'to' => 'Jackdaw Licence Activated',
				'subject' => 'Jackdaw Licence Activated'
			],
			'Learning Resource Signed Off by Coach' => [
				'to' => 'Learning Resource Signed Off by %%manager%%',
				'subject' => 'Learning Resource Signed Off by %%manager%%'
			],
			'Learning Resource Signed Off by Learner' => [
				'to' => 'Learning Resource Signed Off by %%user%%',
				'subject' => 'Learning Resource Signed Off by %%user%%'
			],
			'Assessment Notification' => [
				'to' => '%%assessment_notification%%',
				'subject' => '%%assessment_notification%%'
			],
			'Learning Resource Signed Off by %%user%%' => [
				'to' => '%%learning_resource%% Signed Off by %%user%%',
				'šubject' => '%%learning_resource%% Signed Off by %%user%%'
			],
			'Learning Resource Signed Off by %%manager%%' => [
				'to' => '%%learning_resource%% Signed Off by %%manager%%',
				'subject' => '%%learning_resource%% Signed Off by %%manager%%'
			],
			'Learning Resource Comment for Learner' => [
				'to' => '%%learning_resource%% Comment for %%user%%',
			],
			'Open Elms registration' => [
				'to' => '%%registration_email%%',
				'subject' => '%%registration_email%%',
			],
		];


		foreach ($rename_templates as $key => $rename_template) {
			$update_template = \Models\EmailTemplate
				::where('name', $key)
				->first()
			;
			if ($update_template) {
				$update_template->name = $rename_template['to'];
				if (isset($rename_template['subject'])) {
					$update_template->subject = $rename_template['subject'];
				}
				$update_template->save();
			}
		}


		$templates = [];
		$templates[] = [
			'name' => "Forgotten Password Link",
			'subject' => "Forgotten Password Link",
			'body' => '
				<p>Dear %%USER_FNAME%% %%USER_LNAME%%</p>,
					Click on this <a href=\"%%CONFIG_LMSUrl%%forgottenpassword/%%FORGOTTENPASSWORDID%%\">link</a> to recover your password.
					<br/>
					%%CONFIG_LMSUrl%%forgottenpassword/%%FORGOTTENPASSWORDID%%
					<br/>
					<br/><br/><p>Best Regards</p><p>Apprenticeship Administrator<br/>
				</p>
			',
			'site_versions' => '',
			'force_update' => false
		];


		$templates[] = [
			'name' => "Booking Cancellation",
			'subject' => "Cancellation of %%BOOKING_SESSION%%",
			'body' => '
				<p>Dear %%USER_FNAME%% %%USER_LNAME%%</p><br/><p>Unfortunately we have had to cancel the %%BOOKING_SESSION%% that you are booked on, sorry for the inconvenience.</p><p>Please review your schedule and rebook.</p><br/><br/><p>Best Regards</p><p>Apprenticeship Administrator<br/></p>
			',
			'site_versions' => '"apprentix" "openelmsschools" "openelmscolleges" "openelmsuniversities" "openelmsbusiness"',
			'force_update' => false
		];


		$templates[] = [
			'name' => "Booking Approval Request",
			'subject' => "Important training booking request needs approving",
			'body' => '
				<p>
				Dear %%USER_FNAME%%,<BR/>
				%%TRAINEE_FNAME%% %%TRAINEE_LNAME%% (%%TRAINEE_EMAIL%%) has requested to go on the following training course: %%LEARNING_NAME%%.<BR>
				You will need to approve this action before they can be accepted on the course...<BR>
				Click <a href=\"%%CONFIG_LMSUrl%%app/bookmodule/%%LEARNING_ID%%/%%TRAINEE_ID%%\">here</a> to approve/disapprove.
				<BR/><BR/>
								%%CONFIG_LMSUrl%%app/bookmodule/%%LEARNING_ID%%/%%TRAINEE_ID%%
				<BR/><BR/>
				Details of the course and trainee are listed below<BR>
				<BR>
				Many thanks<BR>
				Learning & Development
				<BR><BR><BR>
				Training Details:<BR>
				%%TRAINEE_FNAME%% %%TRAINEE_LNAME%% (%%TRAINEE_EMAIL%%) %%TRAINEE_USERNAME%%
				<BR>
				%%LEARNING_NAME%% at %%SESSION_LOCATION%% On %%SESSION_DATE%% with %%SESSION_TRAINER%%
				</p>
			',
			'site_versions' => '"apprentix" "openelmsschools" "openelmscolleges" "openelmsuniversities" "openelmsbusiness"',
			'force_update' => false
		];


		$templates[] = [
			'name' => "Booking Disapproval",
			'subject' => "Important your training booking has NOT been approved",
			'body' => '
				<p>
				Dear %%USER_FNAME%%,<BR>
				Your booking for the %%LEARNING_NAME%% at %%SESSION_LOCATION%% on %%SESSION_DATE%% with %%SESSION_TRAINER%% has NOT been approved.<BR>
				Please make sure the booking is listed in your diary for the %%SESSION_DATE%%.<BR>
				Should you need to amend any details, log into the Training Management System at %%CONFIG_LMSUrl%% with your user ID of %%LEARNING_ID%% and search for %%LEARNING_NAME%%.<BR>
								<BR>
				Many thanks<BR>
				Learning & Development
				</p>
			',
			'site_versions' => '"apprentix" "openelmsschools" "openelmscolleges" "openelmsuniversities" "openelmsbusiness"',
			'force_update' => false
		];


		$templates[] = [
			'name' => "Booking Approval",
			'subject' => "Important information about your training booking approval",
			'body' => '
				<p>
				Dear %%USER_FNAME%%,<BR/>
				Your booking for the %%LEARNING_NAME%% at %%SESSION_LOCATION%% on %%SESSION_DATE%% with %%SESSION_TRAINER%% has been approved.<BR/>
				Please make sure the booking is listed in your diary for the %%SESSION_DATE%%.<BR/>
				Should you need to amend any details, log into the Training Management System at %%CONFIG_LMSUrl%% with your user ID of %%LEARNING_ID%% and search for %%LEARNING_NAME%%.<BR/>
					<BR/>
				Many thanks<BR/>
				Learning & Development
				</p>
			',
			'site_versions' => '"apprentix" "openelmsschools" "openelmscolleges" "openelmsuniversities" "openelmsbusiness"',
			'force_update' => false
		];

		$templates[] = [
			'name' => "%%assessment_notification%%",
			'subject' => "%%assessment_notification%%",
			'body' => '
				Dear %%USER_FNAME%% %%USER_LNAME%%,
				We are glad to have you here.

				%%EMPLOYEE_USERNAME%%
				%%EMPLOYEE_FNAME%%
				%%EMPLOYEE_LNAME%%
				%%EMPLOYEE_EMAIL%%
				%%COURSE_NAME%%
				%%ASSESSMENT_DATE%%
				%%LMS_NAME%%
				%%LMS_LINK%%
				%%REGARDS%%
			',
			'site_versions' => '"apprentix" "openelmsschools" "openelmscolleges" "openelmsuniversities" "openelmsbusiness" "smcrsolution"',
			'force_update' => false
		];

		$templates[] = [
			'name' => "Registration Email",
			'subject' => "Welcome to the Training %%CONFIG_LMSTitle%%",
			'body' => '
				Dear %%USER_FNAME%% %%USER_LNAME%%,
				We are glad to have you here.
				<p>
				Dear %%USER_FNAME%%,<BR>
				You have now been signed up for the %%CONFIG_LMSTitle%%.<BR>
				You can log in any time at %%CONFIG_LMSUrl%% using your user ID of %%TRAINEE_USERNAME%% and password (if you have forgotten the password, click <a href=\"%%CONFIG_LMSUrl%%forgottenpassword\">here</a>).<BR>
				Many thanks<BR><BR>
				Learning & Development<BR>
				<p>
			',
			'site_versions' => '',
			'force_update' => false
		];

		$templates[] = [
			'name' => "Jackdaw Licence Activated",
			'subject' => "Jackdaw Licence Activated",
			'body' => '
				<p>Hi</p>
				<p>You\'ve registered on Jackdaw and are accessing the FREE account - congratulations you are on the way to producing the most creative and engaging e-learning possible using system that represents a step-change in usability. Your licence allows you to create and download a single e-learning course, You can either create your own from scratch or use one of our 9 free courses to customise and use yourself.</p>
				<p>Hopefully you are now logged in and enjoying the experience. Should you need to reset your password at any time please <a href="%%CONFIG_LMSUrl%%forgottenpassword/%%FORGOTTENPASSWORDID%%">press on this link.</a><br>
					%%CONFIG_LMSUrl%%forgottenpassword/%%FORGOTTENPASSWORDID%%
				</p>
				<p>Should you need any help using the system please email or call us at any time.</p>
				<p>
					The Jackdaw Support Team e-Learning WMB
				</p>
			',
			'site_versions' => '',
			'force_update' => false
		];

		$templates[] = [
			'name' => "%%registration_email%%",
			'subject' => "%%registration_email%%",
			'body' => '
				<p>Hi</p>
				<p>You\'ve registered on Open Elms and are accessing the FREE account - Open Elms is the most usable and flexible learning system around. Your licence allows you to fully explore the trainee interface and use any of the 9 free courses that have been created using Open Elms\' integrated authoring system - Jackdaw Cloud.</p>
				<p>Hopefully you are now logged in and enjoying the experience. Should you need to reset your password at any time please  <a href="%%CONFIG_LMSUrl%%forgottenpassword/%%FORGOTTENPASSWORDID%%">press on this link. </a><br>
					%%CONFIG_LMSUrl%%forgottenpassword/%%FORGOTTENPASSWORDID%%
				</p>
				<p>Should you need to access the administration system, an online tour or fancy a go at creating and downloading your own e-learning then do let us know by replying to this email or calling the office.</p>
				<p>
					The Open Elms Support Team e-Learning WMB
				</p>
			',
			'site_versions' => '',
			'force_update' => false
		];

		$templates[] = [
			'name' => "Course Refresh Notification",
			'subject' => "Course Refresh Notification",
			'body' => '
				<p>Dear %%USER_FNAME%% %%USER_LNAME%%,</p>
				<p>It is time to take %%COURSE_NAME%% again.</p>
				<p>Click <a href="%%CONFIG_LMSUrl%%app/learner/resources/%%COURSE_ID%%">here</a> to proceed.</p>
			',
			'site_versions' => '',
		];

		$templates[] = [
			'name' => "Learning Resource Needs Attention",
			'subject' => "Learning Resource Needs Attention",
			'body' => '
				<p>Dear %%USER_FNAME%% %%USER_LNAME%%,</p>
				<br>
				<p>The following learning resource has been altered and will require your attention - <a href="%%CONFIG_LMSUrl%%app/learner/resources/%%COURSE_ID%%">%%LEARNING_RESOURCE_NAME%%</a>.</p>
				<br>
				<p>Please visit the link and respond accordingly ASAP.</p>
				<br>
				<p>%%REGARDS%%</p>
			',
			'site_versions' => '"apprentix" "openelmsschools" "openelmscolleges" "openelmsuniversities" "openelmsbusiness" "smcrsolution"',
		];

		$templates[] = [
			'name' => "%%learning_resource%% Signed Off by %%user%%",
			'subject' => "%%learning_resource%% Signed Off by %%user%%",
			'body' => '
				<p>Dear %%USER_FNAME%% %%USER_LNAME%%,</p>
				<br>
				<p>The following learning resource has been signed off - "%%LEARNING_RESOURCE_NAME%%" by user "%%LEARNER_FNAME%% %%LEARNER_LNAME%%" (%%LEARNER_ID%%).<br>This now requires Coach/Trainer approval.</p>
				<br>
				<p>Please visit <a href="%%CONFIG_LMSUrl%%">learning site</a> and respond accordingly ASAP.</p>
				<br>
				<p>%%REGARDS%%</p>
			',
			'site_versions' => '"apprentix" "openelmsschools" "openelmscolleges" "openelmsuniversities" "openelmsbusiness" "smcrsolution"',
		];

		$templates[] = [
			'name' => "%%learning_resource%% Signed Off by %%manager%%",
			'subject' => "%%learning_resource%% Signed Off by %%manager%%",
			'body' => '
				<p>Dear %%USER_FNAME%% %%USER_LNAME%%,</p>
				<br>
				<p>The following learning resource has been successfully signed off by your Coach/Trainer - <a href="%%CONFIG_LMSUrl%%app/learner/resources/%%COURSE_ID%%">%%LEARNING_RESOURCE_NAME%%</a>.</p>
				<br>
				<p>No further action is required.</p>
				<br>
				<p>%%REGARDS%%</p>
			',
			'site_versions' => '"apprentix" "openelmsschools" "openelmscolleges" "openelmsuniversities" "openelmsbusiness" "smcrsolution"',
		];

		$templates[] = [
			'name' => "%%learning_resource%% Comment for %%user%%",
			'subject' => "Comment has been made concerning a learning resource you are currently working on",
			'body' => '
				<p>Dear %%USER_FNAME%% %%USER_LNAME%%,</p>
				<br>
				<p>The following comment has been made concerning a learning resource you are currently working on:</p>
				<p>
					<strong>"%%LEARNING_RESOURCE_NAME%%"</strong> - %%COMMENT%%
				</p>

				<p><a href="%%CONFIG_LMSUrl%%app/learner/resources/%%COURSE_ID%%/signoff">%%LEARNING_RESOURCE_NAME%%</a>.</p>
				<br>
				<br>
				<p>%%REGARDS%%</p>
			',
			'site_versions' => '"apprentix" "openelmsschools" "openelmscolleges" "openelmsuniversities" "openelmsbusiness" "smcrsolution"',
		];

		$templates[] = [
			'name' => "Learning Resource Comment for Manager",
			'subject' => "Comment has been made concerning a learning resource you are currently working on",
			'body' => '
				<p>Dear %%USER_FNAME%% %%USER_LNAME%%,</p>
				<br>
				<p>The following comment has been made concerning a learning resource you are currently working on:</p>
				<p>
					<strong>"%%LEARNING_RESOURCE_NAME%%"</strong> - %%COMMENT%%
				</p>
				<br>
				<br>
				<p>%%REGARDS%%</p>
			',
			'site_versions' => '"apprentix" "openelmsschools" "openelmscolleges" "openelmsuniversities" "openelmsbusiness" "smcrsolution"',
		];

		$templates[] = [
			'name' => "%%programme%% resources reminder",
			'subject' => "Upcoming Learning",
			'body' => '
				<p>Dear %%USER_FNAME%% %%USER_LNAME%%,</p>
				<br>
				<p>
					Please look at the following learning. Your next reminder will be sent in %%FREQUENCY_DAYS%% days.
				</p>
				%%REMINDER_RESOURCES%%
				<p>
					If you are going to be unable to meet any deadlines then please contact your tutor as soon as possible.
				</p>
				<br>
				<br>
				<p>%%REGARDS%%</p>
			',
			'site_versions' => '"apprentix" "openelmsschools" "openelmscolleges" "openelmsuniversities" "openelmsbusiness"',
		];

		$templates[] = [
			'name' => "Learner needs to Accept Function/Responsibility",
			'subject' => "Change of Function/Responsibility",
			'body' => '
				<p>Dear %%USER_FNAME%% %%USER_LNAME%%,</p>
				<p>
					You have been allocated this "%%FUNCTION_RESPONSIBILITY%%" under the Senior Managers and Certification Regime (SMCR). <br>
					You can log in to <a href="%%CONFIG_LMSUrl%%">%%CONFIG_LMSUrl%%</a> to check the full list of assigned to you.
				</p>

				<br>
				<br>
				<p>%%REGARDS%%</p>
			',
			'site_versions' => '"smcrsolution"',
		];

		$templates[] = [
			'name' => "Staff Approval Notification (learner been signed off)",
			'subject' => "Staff Approval Notification (learner been signed off)",
			'body' => '
				<p>Dear %%USER_FNAME%% %%USER_LNAME%%,</p>
				<p>Congratulations, you have been accepted as a %%SMCR_TYPE%% under the SMCR.</p>
				<br>
				<br>
				<p>%%REGARDS%%</p>
			',
			'site_versions' => '"smcrsolution"',
		];

		$templates[] = [
			'name' => "Committee position vacancy assigned - action needed",
			'subject' => "Committee position vacancy assigned - action needed",
			'body' => '
				<p>
					You have been assigned to a committee. <br>
					Please log in and either accept or reject the assigned position.
				</p>
				<br>
				<br>
				<p>%%REGARDS%%</p>
			',
			'site_versions' => '"smcrsolution"',
		];

		$templates[] = [
			'name' => "New work assigned",
			'subject' => "New work assigned",
			'body' => '
				<p>
					You have been assigned the following task. Please log in to the %%CONFIG_LMSName%% at <a href="%%CONFIG_LMSUrl%%">%%CONFIG_LMSUrl%%</a> to complete this task.
				</p>
				<br>
				<p>
					%%ASSIGNED_TASKS%%
				</p>
			',
			'site_versions' => '',
		];

		$templates[] = [
			'name' => "QA task completed and re-submitted for Quality Approval",
			'subject' => "QA task completed and re-submitted for Quality Approval",
			'body' => '
				<p>
					Hello, this email is to inform you that "%%LEARNING_RESOURCE_NAME%%" by "%%QA_LEARNER_NAME%%" which had Quality Assurance actions has now been completed by "%%COACH_NAME%%" and re-submitted for Quality Approval
				</p>
				<br>
				<p>
					<a href="%%CONFIG_LMSUrl%%">Follow the link should you wish to review it.</a>
				</p>
				<br>
				<p>%%REGARDS%%</p>
			',
			'site_versions' => '"apprentix" "openelmsschools" "openelmscolleges" "openelmsuniversities" "openelmsbusiness"',
		];


		// SMCR specific Templates.

		$templates[] = [
			'name' => "Senior Manager requires sign-off",
			'subject' => "Senior Manager requires sign-off",
			'body' => '
				<p>
					"%%SMCR_STAFF%%" has completed all the necessary documentation and training is ready to be signed off. Please visit their account and select the sign-off button if satisfied.
				</p>
				<br>
				<p>
					<a href="%%CONFIG_LMSUrl%%">Follow the link should you wish to review it.</a>
				</p>
			',
			'site_versions' => '"smcrsolution"',
		];
		$templates[] = [
			'name' => "Certification Staff Member requires sign-off",
			'subject' => "Certification Staff Member requires sign-off",
			'body' => '
				<p>
					"%%SMCR_STAFF%%" has completed all the necessary documentation and training is ready to be signed off. Please visit their account and select the sign-off button if satisfied.
				</p>
				<br>
				<p>
					<a href="%%CONFIG_LMSUrl%%">Follow the link should you wish to review it.</a>
				</p>
			',
			'site_versions' => '"smcrsolution"',
		];


		// Sent out to Compliance Manager - anyone with is_manager will get email.
		// Whenever full committee loses at least one member.
		$templates[] = [
			'name' => "Committee needs new member(s)",
			'subject' => "Committee needs new member(s)",
			'body' => '
				<p>
					"%%COMMITTEE%%" committee has become understaffed and needs new member(s).
				</p>
				<br>
				<p>
					<a href="%%CONFIG_LMSUrl%%">Follow the link should you wish to review it.</a>
				</p>
			',
			'site_versions' => '"smcrsolution"',
		];


		$templates[] = [
			'name' => "Senior Manager Approval",
			'subject' => "Senior Manager Approval",
			'body' => '
				<p>
					Congratulations - you have been granted approval in line with the SMCR Regulation.<br>
					You can log into your account and print off this year’s Statement of Responsibilities.
				</p>
				<br>
				<p>
					<a href="%%CONFIG_LMSUrl%%">Follow the link should you wish to review it.</a>
				</p>
			',
			'site_versions' => '"smcrsolution"',
		];

		$templates[] = [
			'name' => "Certification Staff Approval",
			'subject' => "Certification Staff Approval",
			'body' => '
				<p>
					Congratulations - you have been granted approval in line with the SMCR Regulation.<br>
					You can log into your account and print off this year’s Certificate of Completion.
				</p>
				<br>
				<p>
					<a href="%%CONFIG_LMSUrl%%">Follow the link should you wish to review it.</a>
				</p>
			',
			'site_versions' => '"smcrsolution"',
		];

		$templates[] = [
			'name' => "Senior Manager role needs to be (re)assessed",
			'subject' => "Senior Manager role needs to be (re)assessed",
			'body' => '
				<p>
					Time for reassessment<br>
					Please visit the %%CONFIG_LMSName%% at <a href="%%CONFIG_LMSUrl%%">%%CONFIG_LMSUrl%%</a> as you are due for reassessment in line with the SMCR.
				</p>
			',
			'site_versions' => '"smcrsolution"',
		];

		$templates[] = [
			'name' => "Certification Staff member needs to be (re)assessed",
			'subject' => "Certification Staff member needs to be (re)assessed",
			'body' => '
				<p>
					Time for reassessment<br>
					Please visit the %%CONFIG_LMSName%% at <a href="%%CONFIG_LMSUrl%%">%%CONFIG_LMSUrl%%</a> as your periodic training is due again in line with the SMCR.
				</p>
			',
			'site_versions' => '"smcrsolution"',
		];

		$templates[] = [
			'name' => "Conduct Rules Staff member needs to be (re)assessed",
			'subject' => "Conduct Rules Staff member needs to be (re)assessed",
			'body' => '
				<p>
					Time for reassessment<br>
					Please visit the %%CONFIG_LMSName%% at <a href="%%CONFIG_LMSUrl%%">%%CONFIG_LMSUrl%%</a> as you are due for reassessment in line with the SMCR.
				</p>
			',
			'site_versions' => '"smcrsolution"',
		];
		$templates[] = [
			'name' => "Conduct Rules Staff member needs to be (re)trained",
			'subject' => "Conduct Rules Staff member needs to be (re)trained",
			'body' => '
				<p>
					Time for refresher training<br>
					Please visit the %%CONFIG_LMSName%% at <a href="%%CONFIG_LMSUrl%%">%%CONFIG_LMSUrl%%</a> as you are due for refresher training in line with the requirements of the SMCR.
				</p>
			',
			'site_versions' => '"smcrsolution"',
		];

		// Send out after TIMING settings if position is not accepted or rejected!
		$templates[] = [
			'name' => "Committee Position Due",
			'subject' => "Committee Position Due",
			'body' => '
				<p>
					You have been assigned a committee position "%%COMMITTEE%%" which you are required to take action on as soon as possible. Please log in and accept or reject that offer.
				</p>
				<p>
					<a href="%%CONFIG_LMSUrl%%">Follow the link should you wish to review it.</a>
				</p>
			',
			'site_versions' => '"smcrsolution"',
		];

		$templates[] = [
			'name' => "Actions required following Quality Review",
			'subject' => "Actions required following Quality Review",
			'body' => '
				<p>
					The following work has been rejected by the quality assurance process. Please have a look and make amendments as necessary.
				</p>
				<p>
					<strong>%%REJECTED_WORK%%</strong>(%%REJECTED_WORK_ID%%) by "%%REJECTED_LEARNER%%".
				</p>
				<p></p>
				<p>
					<a href="%%CONFIG_LMSUrl%%">Follow the link should you wish to review it.</a>
				</p>
			',
			'site_versions' => '"apprentix" "openelmsschools" "openelmscolleges" "openelmsuniversities" "openelmsbusiness"',
		];


		$templates[] = [
			'name' => "QA Report ready for Quality Approval",
			'subject' => "QA Report ready for Quality Approval",
			'body' => '
				<p>
					Hello, this email is to inform you that the %%QUALITY_REPORT%% for %%LEARNER_NAME%% has been actioned by %%COACH_NAME%% and submitted for Quality Approval
				</p>
				<p></p>
				<p>
					<a href="%%CONFIG_LMSUrl%%">Follow the link should you wish to review it.</a>
				</p>
			',
			'site_versions' => '"apprentix" "openelmsschools" "openelmscolleges" "openelmsuniversities" "openelmsbusiness"',
		];


		$templates[] = [
			'name' => "Welcome message Admin",
			'subject' => "Welcome to the system",
			'body' => '
				<p>Hello %%USER_FNAME%%</p>

				<p>Welcome to the %%CONFIG_LMSName%% - you can access it at <a href="%%CONFIG_LMSUrl%%">%%CONFIG_LMSUrl%%</a> - you can use %%USER_USERNAME%% to log in. If you do not already have a password then click on the forgotten password at the bottom of the screen to reset your password and gain entry.</p>

				<p>You will need to follow the onboarding process to set up the system for your employees/students etc, you will find this in a flow chart at the very back of the system manual at <a href="%%CONFIG_LMSUrl%%help/%%manual_file_name%%">%%CONFIG_LMSUrl%%help/%%manual_file_name%%</a>. Feel free to get started with this straight away - it has links to the relevant sections of the manual for each task and indicates where support can help you out.</p>

				<p>The first step you will probably need to do is to complete the spreadsheet at <a href="%%CONFIG_LMSUrl%%tpl/excel/import_new_users.xls">%%CONFIG_LMSUrl%%tpl/excel/import_new_users.xls</a> - we can help you with this during onboarding but please ensure the details are there. Take care of the field entitled "Job" since this one is the one to which %%learning_resources%% are linked (i.e. all people with the same "Designation" or job field can be assigned the same default %%learning_resources%% on start up).</p>

				<p>Many thanks</p>

				<p>e-Learning WMB</p>
			',
			'site_versions' => '',
		];

		$templates[] = [
			'name' => "Welcome Message Manager",
			'subject' => "Welcome to the system",
			'body' => '
				<p>Hello %%USER_FNAME%%</p>

				<p>Welcome to the %%CONFIG_LMSName%% - you can access it at <a href="%%CONFIG_LMSUrl%%">%%CONFIG_LMSUrl%%</a> - you can use %%USER_USERNAME%% to log in. If you do not already have a password then click on the forgotten password at the bottom of the screen to reset your password and gain entry.</p>

				<p>When logging in you should be a dashboard giving you an overview of performance in the organisation. Select the dark orange button in the top right of the screen. This will show you the management dashboard. It should be clear from the design what you will need to do - just follow the instructions on the screen.</p>

				<p>There is no need for a formal manual - should you need any assistance you can press the blue help button (usually towards the top right of the screen) and it will give you a guided tour of functionality there.</p>

				<p>We hope you enjoy using the software.</p>

				<p>Many thanks</p>

				<p>e-Learning WMB</p>
			',
			'site_versions' => '',
		];

		$templates[] = [
			'name' => "Welcome Message Trainee",
			'subject' => "Welcome to the system",
			'body' => '
				<p>Hello %%USER_FNAME%%</p>

				<p>Welcome to the %%CONFIG_LMSName%% - you can access it at <a href="%%CONFIG_LMSUrl%%">%%CONFIG_LMSUrl%%</a> - you can use %%USER_USERNAME%% to log in. If you do not already have a password then click on the forgotten password at the bottom of the screen to reset your password and gain entry.</p>

				<p>There is no need for a formal manual - should you need any assistance you can press the white help button on the top right of the screen and it will give you a guided tour of functionality there.</p>

				<p>Should you have any issues please contact your local system administrator.</p>

				<p>We hope you enjoy using the software.</p>

				<p>Many thanks</p>

				<p>e-Learning WMB</p>
			',
			'site_versions' => '',
		];

		$templates[] = [
			'name' => "Visit set up",
			'subject' => "Visit set up",
			'body' => '
				<p>
					You have a new visit scheduled. Please log in to the %%CONFIG_LMSName%% at <a href="%%CONFIG_LMSUrl%%">%%CONFIG_LMSUrl%%</a> to review details and add it to your diary.
				</p>
				<p>
					%%ASSIGNED_TASKS%%
				</p>
			',
			'site_versions' => '"apprentix" "openelmsschools" "openelmscolleges" "openelmsuniversities" "openelmsbusiness" "smcrsolution"',
		];


		$templates[] = [
			'name' => "Visit Completed",
			'subject' => "Visit Completed",
			'body' => '
				<p>
					You have a completed your scheduled visit. Feel free to log in to the %%CONFIG_LMSName%% at <a href="%%CONFIG_LMSUrl%%">%%CONFIG_LMSUrl%%</a> to review details of the visit. It will be added to your training records.
				</p>
				<p>
					%%ASSIGNED_TASKS%%
				</p>
			',
			'site_versions' => '"apprentix" "openelmsschools" "openelmscolleges" "openelmsuniversities" "openelmsbusiness" "smcrsolution"',
		];

		$templates[] = [
			'name' => "Virtual meeting booked",
			'subject' => "Virtual meeting booked",
			'slug' => 'schedule_created',
			'body' => '
				<p>
					Dear %%USER_FNAME%%
				</p>
				<p>
					You have been invited to the following learning session. Please make add this to any external calendar you need to.
				</p>
				<p>
					<a href="%%CONFIG_LMSUrl%%app/learner/resources/%%LESSON_ID%%">
						%%EVENT_NAME%% at %%EVENT_TIME%% with %%MANAGER_FNAME%% %%MANAGER_LNAME%%
					</a>
				</p>
				<p>
					Regards<br>
					%%MANAGER_FNAME%% %%MANAGER_LNAME%%
				</p>
			',
			'site_versions' => '"apprentix" "openelmsschools" "openelmscolleges" "openelmsuniversities" "openelmsbusiness" "smcrsolution"',
			'force_update' => false,
		];

		$templates[] = [
			'name' => "Virtual meeting about to start",
			'subject' => "Virtual meeting about to start",
			'slug' => 'schedule_reminder',
			'body' => '
				<p>
					Dear %%USER_FNAME%%
				</p>
				<p>
					Please be aware that the following learning session will start shortly at %%EVENT_TIME%%. Please access this using the link below
				</p>
				<p>
					<a href="%%CONFIG_LMSUrl%%app/learner/resources/%%LESSON_ID%%">
						<strong>
							%%EVENT_NAME%%
						</strong>
					</a>
				</p>

				<p>
					Regards<br>
					%%MANAGER_FNAME%% %%MANAGER_LNAME%%
				</p>
			',
			'site_versions' => '"apprentix" "openelmsschools" "openelmscolleges" "openelmsuniversities" "openelmsbusiness" "smcrsolution"',
		];

		$templates[] = [
			'name' => "New meeting created",
			'subject' => "New meeting created",
			'slug' => 'schedule_meeting_created',
			'body' => '
				<p>
					Dear %%USER_FNAME%%
				</p>
				<p>
					You have been invited to the following meeting. Please add this to any external calendar (this has also been entered on your calendar in the system).
				</p>
				<p>
					<strong>%%EVENT_NAME%%<strong> at %%EVENT_TIME%% at %%EVENT_LOCATION%% with %%MANAGER_FNAME%% %%MANAGER_LNAME%%
				</p>
				<p>
					%%EVENT_DESCRIPTION%%
				</p>
				<p>
					Regards<br>
					%%MANAGER_FNAME%% %%MANAGER_LNAME%%
				</p>
			',
			'site_versions' => '"apprentix" "openelmsschools" "openelmscolleges" "openelmsuniversities" "openelmsbusiness" "smcrsolution"',
			'force_update' => false,
		];

		$templates[] = [
			'name' => "Meeting about to start",
			'subject' => "Meeting about to start",
			'slug' => 'schedule_meeting_reminder',
			'body' => '
				<p>
					Dear %%USER_FNAME%%
				</p>
				<p>
					Please be aware that the following meeting will start shortly at %%EVENT_TIME%% at %%EVENT_LOCATION%% with %%MANAGER_FNAME%% %%MANAGER_LNAME%%.
				</p>
				<p>
					%%EVENT_DESCRIPTION%%
				</p>
				<p>
					Regards<br>
					%%MANAGER_FNAME%% %%MANAGER_LNAME%%
				</p>
			',
			'site_versions' => '"apprentix" "openelmsschools" "openelmscolleges" "openelmsuniversities" "openelmsbusiness" "smcrsolution"',
		];


		$templates[] = [
			'name' => "%%version_name%%: Please complete your user details",
			'subject' => "%%version_name%%: Please complete your user details",
			'slug' => 'user_field_alert_reminder',
			'body' => '
				<p>
					Dear %%USER_FNAME%%
				</p>
				<p>
					Please complete your user details. The following fields are currently empty:
				</p>
				<ul>
					%%INCOMPLETE_FIELDS%%
				</ul>

				<p>Yours sincerely</p>
			',
			'site_versions' => '',
		];

		$templates[] = [
			'name' => "Senior Manager Self-Attestation Alert",
			'subject' => "Senior Manager Self-Attestation Alert",
			'slug' => 'senior_manager_self_attestation_alert',
			'body' => '
				<p>
					Dear %%USER_FNAME%%
				</p>
				<p>
					Your SMCR records are now ready for you to sign-off.<br>
					Please visit the %%CONFIG_LMSName%% at %%CONFIG_LMSUrl%% and press the `Self-Attested` [Edit] button on your record to complete this process.<br>
					It is vital that you do this so the SMCR process can continue.
				</p>
			',
			'site_versions' => '"smcrsolution"',
		];

		$templates[] = [
			'name' => "Certification Staff Member Self-Attestation Alert",
			'subject' => "Certification Staff Member Self-Attestation Alert",
			'slug' => 'certification_staff_member_self_attestation_alert',
			'body' => '
				<p>
					Dear %%USER_FNAME%%
				</p>
				<p>
					Your SMCR records are now ready for you to sign-off.<br>
					Please visit the %%CONFIG_LMSName%% at %%CONFIG_LMSUrl%% and press the `Self-Attested` [Edit] button on your record to complete this process.<br>
					It is vital that you do this so the SMCR process can continue.
				</p>
			',
			'site_versions' => '"smcrsolution"',
		];
		$templates[] = [
			'name' => "Account Approval",
			'subject' => "Your account is approved",
			'slug' => 'account_approval',
			'body' => '
				<p>
					Dear %%USER_FNAME%%
				</p>
				<br>
				<p>Your account has been approved.</p>
					<br>
				<p>%%REGARDS%%</p>

			',
			'site_versions' => '',
		];

		$templates[] = [
			'name' => "Account Denied",
			'subject' => "Your account is denied",
			'slug' => 'account_denied',
			'body' => '
				<p>
				Dear %%USER_FNAME%%
				</p>
				<br>
				<p>Apologies your account has not been approved.  Please feel free to contact the administrator of this system to find out why.</p>
				 <br>
				<p>%%REGARDS%%</p>

			',
			'site_versions' => '',
		];

		$templates[] = [
			'name' => "Competency Awarded",
			'subject' => "Competency Awarded",
			'slug' => 'competency_awarded',
			'body' => '
				<p>Dear %%USER_FNAME%% %%USER_LNAME%%,</p>
				<br>
				<p>Congratulations you have just achieved the %%COMPETENCY%% competency. You achieve these when you have successfully completed necessary learning. Do check on your personal progress for details.</p>
				<br>
				<p>%%REGARDS%%</p>
			',
			'site_versions' => '"apprentix" "openelmsschools" "openelmscolleges" "openelmsuniversities" "openelmsbusiness" "smcrsolution"',
		];

		$templates[] = [
			'name' => "Event Cancellation",
			'subject' => "Cancellation of %%EVENT_NAME%%",
			'slug' => 'event_cancellation',
			'body' => '
				<p>Dear %%USER_FNAME%% %%USER_LNAME%%</p><br/><p>Unfortunately we have had to cancel the %%EVENT_NAME%% that you are booked on, sorry for the inconvenience.</p><p>Please review your schedule and rebook.</p>
			',
			'site_versions' => '',
		];

		$templates[] = [
			'name' => "Event Approval Request",
			'subject' => "Event Request",
			'body' => '
				<p>Dear %%USER_FNAME%% %%USER_LNAME%%,</p>
				<p>The follower learner requires approval to attend a training event.</p><br/>

				<p>Learner: %%TRAINEE_FNAME%% %%TRAINEE_LNAME%%</p>
				<p>Event: %%EVENT_NAME%%</p>
				<p>Location: %%EVENT_LOCATION%%</p>
				<p>Date: %%EVENT_DATE%%</p>
				<p>click <a href="%%CONFIG_LMSUrl%%%%MANAGER_APPROVAL_LINK%%">here</a> to approve</p>
				<p>%%REGARDS%%</p>
			',
			'site_versions' => '"apprentix" "openelmsschools" "openelmscolleges" "openelmsuniversities" "openelmsbusiness"',
			'force_update' => false
		];

		$templates[] = [
			'name' => "Event Approval",
			'subject' => "Event Approval Confirmation",
			'body' => '
				<p>Dear %%USER_FNAME%%,</p>
				<p>Your event request has been approved by your manager for the %%EVENT_NAME%% on the %%EVENT_DATE%% at %%EVENT_LOCATION%%</p>
				<p>Please click <a href="%%CONFIG_LMSUrl%%%%EVENT_APPROVAL_LINK%%">here</a> to follow next steps (if this event requires payment then you can use the link and follow the process for payment).</p><br/>

			   <p>%%REGARDS%%</p>
			',
			'site_versions' => '"apprentix" "openelmsschools" "openelmscolleges" "openelmsuniversities" "openelmsbusiness"',
			'force_update' => false
		];

		$templates[] = [
			'name' => "Event Not Approved",
			'subject' => "Your event request has not been approved",
			'body' => '
				<p>Dear %%USER_FNAME%% %%USER_LNAME%%,</p>
				<p>Unfortunately you have not been granted approval to access the following learning event at this time:</p><br/>

				<p>Learning: %%EVENT_NAME%%</p>
				<p>Location: %%EVENT_LOCATION%%</p>
				<p>Date: %%EVENT_DATE%%</p>
				<p>Please contact your line manager for further information.</p>
				<p>%%REGARDS%%</p>
			',
			'site_versions' => '"apprentix" "openelmsschools" "openelmscolleges" "openelmsuniversities" "openelmsbusiness"',
			'force_update' => false
		];

		$templates[] = [
			'name' => "Resource Approval Request",
			'subject' => "Resource Request",
			'body' => '
				<p>Dear %%USER_FNAME%% %%USER_LNAME%%,</p>
				<p>The follower learner requires approval to access learning resource.</p><br/>

				<p>Learner:%%TRAINEE_FNAME%% %%TRAINEE_LNAME%%</p>
				<p>Event: %%RESOURCE_NAME%%</p>
				<p>click <a href="%%CONFIG_LMSUrl%%%%MANAGER_APPROVAL_LINK%%">here</a> to approve</p>
				<p>%%REGARDS%%</p>
			',
			'site_versions' => '"apprentix" "openelmsschools" "openelmscolleges" "openelmsuniversities" "openelmsbusiness"',
			'force_update' => false
		];

		$templates[] = [
			'name' => "Resource Approval",
			'subject' => "Resource Approval Confirmation",
			'body' => '
				<p>Dear %%USER_FNAME%%,</p>
				<p>Your resource request has been approved by your manager for the %%RESOURCE_NAME%%</p>
				<p>Please click <a href="%%CONFIG_LMSUrl%%%%RESOURCE_APPROVAL_LINK%%">here</a> to follow next steps (if this resource requires payment then you can use the link and follow the process for payment).</p><br/>

			   <p>%%REGARDS%%</p>
			',
			'site_versions' => '"apprentix" "openelmsschools" "openelmscolleges" "openelmsuniversities" "openelmsbusiness"',
			'force_update' => false
		];

		$templates[] = [
			'name' => "Resource Not Approved",
			'subject' => "Your resource request has not been approved",
			'body' => '
				<p>Dear %%USER_FNAME%% %%USER_LNAME%%,</p>
				<p>Unfortunately you have not been granted approval to access the following learning event at this time:</p><br/>

				<p>Learning: %%RESOURCE_NAME%%</p>
				<p>Please contact your line manager for further information.</p>
				<p>%%REGARDS%%</p>
			',
			'site_versions' => '"apprentix" "openelmsschools" "openelmscolleges" "openelmsuniversities" "openelmsbusiness"',
			'force_update' => false
		];

		$templates[] = [
			'name' => "Confirmation of purchase",
			'subject' => "Confirmation of purchase",
			'slug' => 'confirmation_of_purchase',
			'body' => '
				<p>Dear %%USER_FNAME%%,</p>
				<p>The following is a receipt for the %%LEARNING_NAME%% purchased on %%TODAYSDATE%%.</p>
				<p>The receipt number is %%RECEIPT_NUMBER%%.</p>
				<p>%%REGARDS%%</p>
			',
			'site_versions' => '',
		];

		foreach ($templates as $key => $template) {
			$query = \Models\EmailTemplate
				::firstOrCreate( // updateOrCreate, firstOrCreate
					['name' => $template['name']],
					[
						'subject' => $template['subject'],
						'body' => $template['body'],
						'site_versions' => $template['site_versions'] ? $template['site_versions'] : '',
						'slug' => ((isset($template['slug']) ? $template['slug'] : \APP\Tools::safeName($template['subject']))),
					]
				);
			;
			// Push update if specified in templace configuration, overwrite.
			if (
				isset($template['force_update']) &&
				$template['force_update']
			) {
				$query->subject = $template['subject'];
				$query->body = $template['body'];
				$query->site_versions = $template['site_versions'] ? $template['site_versions'] : '';
				$query->slug = ((isset($template['slug']) ? $template['slug'] : \APP\Tools::safeName($template['subject'])));
				$query->save();
			}

			// If slug does not exists, generate that one!
			if (!$query->slug) {
				$query->slug = ((isset($template['slug']) ? $template['slug'] : \APP\Tools::safeName($template['subject'])));
				$query->save();
			}

			// Set conditions for e-mail templates to show/hide on specific site versions.
			\Models\EmailTemplate
				::where('name', $template['name'])
				->where('site_versions', '!=', $template['site_versions'])
				->update(['site_versions' => $template['site_versions']])
			;
		}
	}
}