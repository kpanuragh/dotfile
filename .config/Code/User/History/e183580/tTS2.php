<?php
namespace DB;

class Configuration {
	public static function update ($settings = false) {
		$configuration_values = [
			'defaultUKPRN' => [
				'name' => 'Default UKPRN',
				'type' => 'string',
				'status' => 1,
				'value' => '',
				'description' => "UK provider reference number (UKPRN). \nMust contain a value in the range 10000000 to 99999999",
				'created_by' => 0
			],
			'defaultPrevUKPRN' => [
				'name' => 'Default UKPRN in previous year',
				'type' => 'string',
				'status' => 1,
				'value' => '',
				'description' => "UK provider reference number (UKPRN). \nMust contain a value in the range 10000000 to 99999999",
				'created_by' => 0
			],
			'defaultUsernameLabel' => [
				'name' => 'Default Username label on login screen.',
				'type' => 'string',
				'status' => 1,
				'value' => '',
				'description' => "Default Username label on login screen.",
				'created_by' => 0
			],
			'learnerReferenceNumberIteration' => [
				'name' => 'Learner Reference Number iteration.',
				'type' => 'integer',
				'status' => 1,
				'value' => 1,
				'description' => 'Iteration for Learner reference number. Final result(example): "domain000001".',
				'created_by' => 0
			],
			'learnerReferenceNumberId' => [
				'name' => 'Learner Reference number first part.',
				'type' => 'string',
				'status' => 1,
				'value' => '',
				'description' => 'Up to 6 character long string that goes in begginging for Learner Reference Number',
				'created_by' => 0
			],
			'showSocialLoginButtons' => [
				'name' => 'Show social log-in buttons on log-in page.',
				'type' => 'boolean',
				'status' => 1,
				'value' => 0,
				'description' => 'Show "Log in with Facebook" and "Sign in with Google" buttons on log-in page',
				'created_by' => 0
			],
			'allowRegistration' => [
				'name' => 'Allow Registration',
				'type' => 'boolean',
				'status' => 1,
				'value' => 0,
				'description' => 'Allow registration for new users',
				'created_by' => 0
			],
			'allowRemoteRegistration' => [
				'name' => 'Remote Registration in iframe',
				'type' => 'boolean',
				'status' => 1,
				'value' => 0,
				'description' => 'Allow Registration the "/register" extension to be used on remote sites. i.e. <iframe src="https://openelms.e-learningwmb.co.uk/SITEDOMAIN/register" width="100%" height="100%" frameborder="0" title="Registration form, embeded"></iframe>',
				'created_by' => 0,
				'update_name_desc' => true,
			],
			'defaultRegisterRole' => [
				'name' => 'Default role for registered user',
				'type' => 'integer',
				'status' => 1,
				'value' => 3,
				'description' => 'Default role for new registered user. Change this to your desired role ID.',
				'created_by' => 0
			],
			'defaultIlrRole' => [
				'name' => 'Default role for imported ILR users',
				'type' => 'integer',
				'status' => 1,
				'value' => 3,
				'description' => 'Default role for imported users using ILR import.',
				'created_by' => 0
			],
			'refreshCompletedAt' => [
				'name' => 'Refresh training when resource completed not first assigned.',
				'type' => 'boolean',
				'status' => 1,
				'value' => 1,
				'description' => 'Learning Results refresh time and "due_at" will be calculated by using "completed_at" as base.',
				'created_by' => 0
			],
			'sendRefreshEmail' => [
				'name' => 'Send refresh learning e-mails',
				'type' => 'boolean',
				'status' => 1,
				'value' => 1,
				'description' => 'When learning resource is refreshed, send e-mail to learner as reminder.',
				'created_by' => 0
			],
			'allowLearnerRefreshLearning' => [
				'name' => 'Allow Learner to refresh Learning',
				'type' => 'boolean',
				'status' => 1,
				'value' => 0,
				'description' => 'Allow Learner to refresh completed Learning Resource. When Learner will open completed Learning Resource, prompt will open with option to start new learning record.',
				'created_by' => 0
			],
			'allowAddBlogEntry' => [
				'name' => 'Allow Learner to add Blog Entry.',
				'type' => 'boolean',
				'status' => 1,
				'value' => $settings && isset($settings['licensing']) && isset($settings['licensing']['isApprentix']) && $settings['licensing']['isApprentix'] == 1 ? 1 : 0,
				'description' => 'Allow Learner to add Blog Entry as new learning type. That can be assigned to other learners by managers',
				'created_by' => 0
			],
			'allowApi' => [
				'name' => 'Enable API for site.',
				'type' => 'boolean',
				'status' => 1,
				'value' => 0,
				'description' => 'Allows API requests to be made to site.',
				'created_by' => 0,
			],
			'trainingWorkRatio' => [
				'name' => 'Training/work ratio',
				'type' => 'integer',
				'status' => 1,
				'value' => 0.2,
				'description' => 'This is the proportion of work time for every programme where training is carried out - this is 0.2 or 20% of UK apprenticeships.',
				'created_by' => 0
			],
			'defaultJackdawRegisterRole' => [
				'name' => 'Default role for registered Open eLMS Creator user',
				'type' => 'integer',
				'status' => 0,
				'value' => '',
				'description' => 'Default role for registered Open eLMS Creator users. Change this to your desired role ID from Orgainsation->Roles section',
				'created_by' => 0
			],
			'allowSCORMContainerPlayer' => [
				'name' => 'Allow SCORM Container to be played',
				'type' => 'boolean',
				'status' => 1,
				'value' => 0,
				'description' => 'Allow SCORM container downloaded from site to be enabled/functioning.',
				'created_by' => 0
			],
			'isDemoAccess' => [
				'name' => 'Demo User Interface',
				'type' => 'boolean',
				'status' => 1,
				'value' => 0,
				'description' => 'This will allow any user to access to a site without registering.',
				'created_by' => 0
			],
			'demoUserId' => [
				'name' => 'Demo User ID',
				'type' => 'integer',
				'status' => 1,
				'value' => '',
				'description' => 'If demo User Interface is enabled, valid user\'s ID must be specified. Demo user will be logged as that user.',
				'created_by' => 0
			],
			'mandatoryDuration' => [
				'name' => 'Mandatory duration',
				'type' => 'boolean',
				'status' => 1,
				'value' => 0,
				'description' => 'Mandatory duration for resources that learner wants to sign off.',
				'created_by' => 0
			],
			'schoolField' => [
				'name' => 'School Field',
				'type' => 'boolean',
				'status' => 1,
				'value' => 0,
				'description' => 'Free text field in user profile to specify School.',
				'created_by' => 0
			],
			'allowLearnerEditILR' => [
				'name' => 'Allow Learner Edit ILR',
				'type' => 'boolean',
				'status' => 1,
				'value' => 0,
				'description' => 'Allow Learner Edit ILR Data from my-profile window in learners interface.',
				'created_by' => 0
			],
			'signOffText' => [
				'name' => 'Sign off text',
				'type' => 'string',
				'status' => 1,
				'value' => 'I agree that the information provided here is an accurate account of what has taken place',
				'description' => 'Sign off text in learner\'s interface.',
				'created_by' => 0
			],
			'showEmergencyContactDetails' => [
				'name' => 'Emergency contact details',
				'type' => 'boolean',
				'status' => 1,
				'value' => 0,
				'description' => 'Show Emergency contact details in users profile/form.',
				'created_by' => 0
			],
			'showVisaEligibilityChecks' => [
				'name' => 'Visa Eligibility Checks',
				'type' => 'boolean',
				'status' => 1,
				'value' => 0,
				'description' => 'Show Visa Eligibility Checks in users profile/form.',
				'created_by' => 0
			],
			'apprentixEmailReminderFrequency' => [
				'name' => 'Frequency in days by which automated email alerts are sent out',
				'type' => 'integer',
				'status' => 1,
				'value' => 0,
				'description' => "Apprentix only. Resources assigned to standard only. Specified number will be frequency how often e-mails will be sent out as reminders for resources that are not completed. First e-mail will be sent out set days before expected completion date. Look in timings configuration for more information. \nSetting value to 0 will disable this functionality.",
				'created_by' => 0
			],

			'isMeetings' => [
				'name' => 'Make Meetings Optional',
				'type' => 'boolean',
				'status' => 1,
				'value' => 0,
				'description' => 'Having this as false will remove it from the toolbar of Coach/Trainer and Quality Assurer.',
				'created_by' => 0
			],

			'isManageBookings' => [
				'name' => 'Make Manage Bookings Optional',
				'type' => 'boolean',
				'status' => 1,
				'value' => 0,
				'description' => 'Having this as false will remove it from the toolbar of Coach/Trainer and Administrator.',
				'created_by' => 0
			],

			'isLearnerQAFilter' => [
				'name' => 'Show QA filter in learners interface',
				'type' => 'boolean',
				'status' => 1,
				'value' => 0,
				'description' => 'Show QA filter in learners interface. Leaner can filter Rejected and Accepted resources.',
				'created_by' => 0
			],

			'learnerAimReferenceURL' => [
				'name' => 'Learning Aim Reference Service URL',
				'type' => 'string',
				'status' => 1,
				'value' => 'https://des.fasst.org.uk/Learning%20Aims/Downloads/Documents/20190308_LARS_V006_CSV.zip',
				'description' => 'Direct link to Learning Aim Reference Service ZIP file.',
				'created_by' => 0
			],

			'version' => [
				'name' => 'Version',
				'type' => 'string',
				'status' => 1,
				'value' => '', // openelms, openelmstms, omniprez, smcrsolution, apprentix, openelmsschools, openelmscolleges, openelmsuniversities, openelmsbusiness, nras
				'description' => 'Version.',
				'created_by' => 0
			],

			//Power BI Configurations
			'powerbi_client_id' => [
				'name' => 'Power BI Client ID',
				'type' => 'string',
				'status' => 1,
				'value' => '',
				'description' => 'The Client ID of Azure Application used to control access to Power BI',
				'created_by' => 0
			],

			'powerbi_client_secret' => [
				'name' => 'Power BI Client Secret',
				'type' => 'string',
				'status' => 1,
				'value' => '',
				'description' => 'The Client Secret Key of Azure Application used to control access to Power BI',
				'secure' => true,
				'created_by' => 0
			],

			'powerbi_azure_username' => [
				'name' => 'Power BI Azure username',
				'type' => 'string',
				'status' => 1,
				'value' => '',
				'description' => 'The username of the Azure account used to export data to Power BI',
				'created_by' => 0
			],

			'powerbi_azure_password' => [
				'name' => 'Power BI Azure password',
				'type' => 'string',
				'status' => 1,
				'value' => '',
				'description' => 'The password of the Azure account used to export data to Power BI',
				'secure' => true,
				'created_by' => 0
			],

			'powerbi_dashboard_url' => [
				'name' => 'Default Power BI Dashboard',
				'type' => 'string',
				'status' => 1,
				'value' => '',
				'description' => 'Url to Power BI dashboard.',
				'created_by' => 0
			],

			'disableLazyLoading' => [
				'name' => 'Disable lazy load functionality',
				'type' => 'boolean',
				'status' => 1,
				'value' => 0,
				'description' => 'Learners interface has lazy load enabled by default, when image enters viewport, it loads image. Possibly some compatibility problems on Apple devices, will enable this switch to disable.',
				'created_by' => 0
			],
			'moodleLink' => [
				'name' => 'Moodle Link',
				'type' => 'string',
				'status' => 1,
				'value' => "",
				'description' => 'Url of Moodle installation',
				'created_by' => 0
			],
			'nextDueTrafficLight' => [
				'name' => 'Traffic light system for next Due Date',
				'type' => 'integer',
				'status' => 1,
				'value' => 4,
				'description' => 'Traffic light system to "Check Progress" table, so if the Next Due date is greater than 4 weeks in the future then the light is green, if less than 4 weeks away it is orange and if overdue (i.e. date is in the past) it is red',
				'created_by' => 0
			],
			'allowEmptyEmailImport' => [
				'name' => 'Allow importing users without email',
				'type' => 'boolean',
				'status' => 1,
				'value' => 0,
				'description' => 'When user is imported without email, a randomly dummy e-mail(non functioning) is generated and assigned to user, as email field is unique and required.',
				'created_by' => 0
			],
			'loggingDataRentention' => [
				'name' => 'How many years logging data is retained',
				'type' => 'integer',
				'status' => 1,
				'value' => 6,
				'description' => 'After how many years data is deleted. Login-in/out, data export.',
				'created_by' => 0
			],
			'enableEmailFunctionality' => [
				'name' => 'This will turn on all email sending features',
				'type' => 'boolean',
				'status' => 1,
				'value' => 1,
				'description' => 'If you want to enable e-mail on system, set this to true, to disable, set to false',
				'created_by' => 0
			],
			'enableClassRoomManagerDropDown' => [
				'name' => 'Will enable drop down functionality for classroom learning type.',
				'type' => 'boolean',
				'status' => 1,
				'value' => 0,
				'description' => 'Will replace free text field with drop-down of managers to choose from.',
				'created_by' => 0
			],
			'showResponsibilitiesAndCommittees' => [
				'name' => 'Manage Responsibilities and Committees for non-SMCR sites.',
				'type' => 'boolean',
				'status' => 1,
				'value' => 0,
				'description' => 'Will show "Responsibilities" and "Committee Memberships" in "Manage Learning" interface, visible to managers.',
				'created_by' => 0
			],
			'competenciesGamification' => [
				'name' => 'Enable Competencies/"Gamification" functionality.',
				'type' => 'boolean',
				'status' => 1,
				'value' => 0,
				'description' => '"Learner Programme", "Training Data" view will see Competencies table, Learner will see new head icon "Learning Profile" that will show summary of they profile with gamification table as well.',
				'created_by' => 0
			],
			'showLatestReleases' => [
				'name' => 'Show Latest Releases',
				'type' => 'boolean',
				'status' => 1,
				'value' => 0,
				'description' => 'This shows the latest 10 course releases added to the system.',
				'created_by' => 0
			],
			'companyCrm' => [
				'name' => 'Add CRM Fields to Company data',
				'type' => 'boolean',
				'status' => 1,
				'value' => 0,
				'description' => 'Ability to add products and contact details for sales activity',
				'created_by' => 0
			],
			'sessionTimeout' => [
				'name' => 'Session timeout for inactivity',
				'type' => 'integer',
				'status' => 1,
				'value' => 1,
				'description' => 'How many hours session will last untill user is logged out, due to inactivity.',
				'created_by' => 0
			],
			'linkIlrToUserProgramme' => [
				'name' => 'Uses dates in ILR record for standard.',
				'type' => 'boolean',
				'status' => 1,
				'value' => 0,
				'description' => 'When activated nightly cron task or when user is updated/imported, will check if user is assigned to programme and if that programme holds reference to users ILR record, if match is found ILR record dates will be used!',
				'created_by' => 0
			],
			'enableEmailAttachments' => [
				'name' => 'Allows adding files to email templates.',
				'type' => 'boolean',
				'status' => 1,
				'value' => 1,
				'description' => 'When activated, multiple files can be added to e-mail templates as attachments.',
				'created_by' => 0,
				//'force' => true,
			],
			'emailAttachmentsSize' => [
				'name' => 'Maximum size allowed to be added to templates (MB).',
				'type' => 'integer',
				'status' => 1,
				'value' => 5,
				'description' => 'The amount is the combined file size per e-mail template in MB. If file or files exceed specified limits, no new files can be added to template.',
				'created_by' => 0,
				//'force' => true,
			],
			'enableUploadType' => [
				'name' => 'Shows type dropdown for uploads.',
				'type' => 'boolean',
				'status' => 1, // Enable only for Apprentix and Colleges
				'value' => ($settings['licensing']['version'] == 'apprentix' || $settings['licensing']['version'] == 'openelmscolleges') ? 1 : 0,
				'description' => 'Shows type dropdown for uploads.',
				'created_by' => 0,
			],
			'enableJackdawHtml5' => [
				'name' => 'Switch Open eLMS Creator to HTML5.',
				'type' => 'boolean',
				'status' => 1,
				'value' => 1,
				'description' => 'Uses new HTML5 Open eLMS Creator version.',
				'created_by' => 0,
			],
			'disableResourceTypes' => [
				'name' => 'Disable specific resource types',
				'type' => 'string',
				'status' => 1,
				'value' => '',
				'description' => 'To disable specific resource types input slug, sperated by coma. Ex: "e_learning,youtube,webpage,classroom,book_cd_dvd,on_the_job,upload,blog_entry,reflective_log,vimeo,moodle_course,google_classroom"',
				'created_by' => 0,
			],
			'olarkCode' => [
				'name' => 'Script from Olark',
				'type' => 'text',
				'status' => 1,
				'value' => "",
				'description' => 'Add code from Olark in Value field.',
				'created_by' => 0,
			],
			'enableOlark' => [
				'name' => 'Enable Olark code.',
				'type' => 'boolean',
				'status' => 1,
				'value' => 0,
				'description' => 'Enable Olark code.',
				'created_by' => 0,
			],
			'enableSchedule' => [
				'name' => 'Enable Schedule.',
				'type' => 'boolean',
				'status' => 1,
				'value' => 0,
				'description' => 'Enable Schedule section in managers view.',
				'created_by' => 0,
			],
			'lessonDuration' => [
				'name' => 'Default Lesson duration when assigning lessons to class/departments',
				'type' => 'integer',
				'status' => 1,
				'value' => 60,
				'description' => 'Default Lesson duration, specified in minutes, used when assigning Lesson to Class for programme/standard.',
				'created_by' => 0,
				//'force' => true,
			],
			'learnerInterfaceV2' => [
				'name' => 'Enable changes to learner intrface.',
				'type' => 'boolean',
				'status' => 1,
				'value' => 0,
				'description' => 'Learner interface - remove some top icons and replaces them with text in header.',
				'created_by' => 0,
			],
			'enableFeedback' => [
				'name' => 'Enable feedback form and list for learner.',
				'type' => 'boolean',
				'status' => 1,
				'value' => 1,
				'description' => 'Feedback interface for learner, enable to allow learner add rating and comment to resource.',
				'created_by' => 0,
			],
			'enableFeedbackList' => [
				'name' => 'When this is enabled, learner will also see list of feedback by other users.',
				'type' => 'boolean',
				'status' => 1,
				'value' => 1,
				'description' => 'When this is enabled, learner will also see list of feedback by other users.',
				'created_by' => 0,
			],
			'offTheJobHoursForReviews' => [
				'name' => 'Off the job hours toggle for reviews.',
				'type' => 'boolean',
				'status' => 1,
				'value' => 0,
				'description' => 'Will show checkbox in reviews section, for managers to mark review as off the job, that will not be counted towards statistics..',
				'created_by' => 0,
			],
			'enableLeaderBoardImages' => [
				'name' => 'Show profile picture in leaderboards or random assigned icon.',
				'type' => 'boolean',
				'status' => 1,
				'value' => 1,
				'description' => 'When this is enabled, leaderboard list will show either profile picture or if that is not available, randomly assigned person icon.',
				'created_by' => 0,
			],
			'enableLeaderBoardList' => [
				'name' => 'Show leaderboard list/table.',
				'type' => 'boolean',
				'status' => 1,
				'value' => 1,
				'description' => 'When this is enabled, leaderboard list will be shown for learner and administration.',
				'created_by' => 0,
			],
			'scheduleReminderLead' => [
				'name' => 'How many minutes before to send e-mail reminder about starting scheduled event to users',
				'type' => 'integer',
				'status' => 1,
				'value' => 65,
				'description' => 'Send reminder to user about scheduled event that is going to start soon. Automated task is executed every 5 minutes, not suggestable to take this below 10 minutes.',
				'created_by' => 0,
				//'force' => true,
			],
			'eventReminderLead' => [
				'name' => 'How many minutes before to send e-mail reminder about starting scheduled event to users',
				'type' => 'integer',
				'status' => 1,
				'value' => 65,
				'description' => 'Send reminder to user about scheduled event that is going to start soon. Automated task is executed every 5 minutes, not suggestable to take this below 10 minutes.',
				'created_by' => 0,
				//'force' => true,
			],
			'virtualEventReminderLead' => [
				'name' => 'How many minutes before to send e-mail reminder about starting scheduled event to users',
				'type' => 'integer',
				'status' => 1,
				'value' => 65,
				'description' => 'Send reminder to user about scheduled event that is going to start soon. Automated task is executed every 5 minutes, not suggestable to take this below 10 minutes.',
				'created_by' => 0,
				//'force' => true,
			],
			'importYoutubePlaylist' => [
				'name' => 'Enable youtube playlist import into learning library',
				'type' => 'boolean',
				'status' => 1,
				'value' => 0,
				'description' => 'By enabling this option, you can specify playlist URL in learning library to impoert each individual videa as learning resource.',
				'created_by' => 0,
				//'force' => true,
			],
			'jackdawVersion' => [
				'name' => 'Jackdaw Billing Version',
				'type' => 'string',
				'status' => 1,
				'value' => 'JACKDAW', // JACKDAW, JACKDAWCMS, JACKDAWREAD
				'description' => 'Jackdaw Billing Version',
				'created_by' => 0
			],
			'forceYoutubeTitleOverThumbnail' => [
				'name' => 'Show video title over thumbnail for YouTube resources',
				'type' => 'boolean',
				'status' => 1,
				'value' => 1,
				'description' => 'When enabled, title will be shown over thumbnail in learners interface regardless of custom thumbnail existance, for YouTube items.',
				'created_by' => 0
			],
			'hideCurriculum' => [
				'name' => 'Hide Curriculum',
				'type' => 'boolean',
				'status' => 1,
				'value' => ($settings['licensing']['version'] == 'openelmsuniversities' || $settings['licensing']['version'] == 'openelmscolleges' || $settings['licensing']['version'] == 'openelmsschools') ? 1 : 0,
				'description' => 'This hides the curriculum schedule from all users.',
				'created_by' => 0
			],
			'hideCurriculumMatching' => [
				'name' => 'Hide Curriculum Matching',
				'type' => 'boolean',
				'status' => 1,
				'value' => ($settings['licensing']['version'] == 'openelmsuniversities' || $settings['licensing']['version'] == 'openelmscolleges' || $settings['licensing']['version'] == 'openelmsschools') ? 1 : 0,
				'description' => 'This hides the functionality to allow learners to match their submitted work to parts of the curriculum.',
				'created_by' => 0
			],
			'hideCurriculumLearner' => [
				'name' => 'Hide Curriculum for Learner',
				'type' => 'boolean',
				'status' => 1,
				'value' => ($settings['licensing']['version'] == 'openelmsuniversities' || $settings['licensing']['version'] == 'openelmscolleges' || $settings['licensing']['version'] == 'openelmsschools') ? 1 : 0,
				'description' => 'This hides the curriculum schedule from users in Learners interface.',
				'created_by' => 0,
				'update_name_desc' => true,
			],
			'ZoomClientId' => [
				'name' => 'Zoom Client ID',
				'type' => 'string',
				'status' => 1,
				'value' => '',
				'description' => 'SDK apps require an SDK Key and Secret for authentication. These credentials are account-level and are generated once per account. To generate SDK Keys and Secrets for your account, navigate to the Marketplace and https://marketplace.zoom.us/develop/create',
				'created_by' => 0
			],
			'ZoomClientSecret' => [
				'name' => 'Zoom Client Secret',
				'type' => 'string',
				'status' => 1,
				'value' => '',
				'description' => 'SDK apps require an SDK Key and Secret for authentication. These credentials are account-level and are generated once per account. To generate SDK Keys and Secrets for your account, navigate to the Marketplace and https://marketplace.zoom.us/develop/create',
				'secure' => true,
				'created_by' => 0
			],
			'isBlackColourScheme' => [
				'name' => 'Black Colour Scheme',
				'type' => 'boolean',
				'status' => 1,
				'value' => 1,
				'description' => 'This colour scheme shows the training with a dark background - making this false will show a light background and dark icons/writing.',
				'created_by' => 0
			],
			'AndersPinkApiKey' => [
				'name' => 'Private API key for Anders Pink',
				'type' => 'string',
				'status' => 1,
				'value' => '',
				'description' => 'Key can be obtained from https://anderspink.com/export-settings/ ',
				'secure' => true,
				'created_by' => 0
			],
			'isCategroyFilter' => [
				'name' => 'Category Filter in Learners interface',
				'type' => 'boolean',
				'status' => 1,
				'value' => 1,
				'description' => 'When true, a category filter is added to the learner’s interface allowing the learner to display one expanded category at a time.',
				'created_by' => 0
			],
			'isLearnerLandingPage' => [
				'name' => 'Add learner landing page',
				'type' => 'boolean',
				'status' => 1,
				'value' => 0,
				'description' => 'Setting this to true will set a landing page showing pictures of all learning categories which have an image assigned to them. Do ensure an image is defined for category if you are using this functionality.',
				'created_by' => 0
			],
			'learnerLandingPageDescription' => [
				'name' => 'Learner landing page description',
				'type' => 'text',
				'status' => 1,
				'value' => '',
				'description' => 'This text will show above landing page category images.',
				'created_by' => 0
			],
			'learnerLandingPageNameCentered' => [
				'name' => 'Centre category labels',
				'type' => 'boolean',
				'status' => 1,
				'value' => 0,
				'description' => 'Category landing page will have its name centred.',
				'created_by' => 0,
				'update_name_desc' => true,
			],
			'defaultTimezone' => [
				'name' => 'Default time zone',
				'type' => 'string',
				'status' => 1,
				'value' => 'Europe/London',
				'description' => 'Used for font and back end. Adjust if instance is used in different time zones.',
				'created_by' => 0
			],
			'thumbnailRedesign' => [
				'name' => 'Thumbnail redesign in Learners interface',
				'type' => 'boolean',
				'status' => 1,
				'value' => 0,
				'description' => 'On learners interface, thumbnails shown will be displayed with image on one side and name on another, all enclosed with white background.',
				'created_by' => 0
			],
			'thumbnailRedesignFont' => [
				'name' => 'Font used in thumbnail redesign, learners interface',
				'type' => 'string',
				'status' => 1,
				'value' => 'Roboto',
				'description' => 'Possible values can be: "Arial", "Roboto", "Times New Roman", "Times", "Courier New", "Courier", "Verdana", "Georgia", "Palatino", "Garamond", "Bookman", "Comic Sans MS", "Candara", "Arial Black", "Impact"',
				'created_by' => 0
			],
			'disableLookOvers' => [
				'name' => 'Disable Look Overs button/functionality in Learners profile for managers',
				'type' => 'boolean',
				'status' => 1,
				'value' => 0,
				'description' => 'With creation of events/schedule view this function will become obselete and won\'t be used for new clients. Reporting will go to custom reviews.',
				'created_by' => 0
			],
			'go1clientID' => [
				'name' => 'Go1 client ID',
				'type' => 'string',
				'status' => 1,
				'value' => '',
				'description' => 'Client ID which is a publicly exposed string used by the GO1 API to identify the OAuth client.',
				'created_by' => 0
			],
			'go1clientSecret' => [
				'name' => 'Go1 client secret',
				'type' => 'string',
				'status' => 1,
				'value' => '',
				'description' => 'Client secret which is used to authenticate the identity of the OAuth client and must be kept private between the partner application and the API.',
				'secure' => true,
				'created_by' => 0
			],
			'go1GrantCode' => [
				'name' => 'Go1 authorization code',
				'type' => 'string',
				'status' => 1,
				'value' => '',
				'description' => 'After access is provided to the portal, the user will be redirected back to the Partner application (redirect_uri specified in Step 1) with an authorization code as a GET-Parameter.',
				'secure' => true,
				'created_by' => 0
			],
			'go1AuthToken' => [
				'name' => 'Go1 Auth token',
				'type' => 'string',
				'status' => 1,
				'value' => '',
				'description' => 'The Partner server will exchange the authorization code for an access token by making a POST request to the authorization server\'s token endpoint.',
				'secure' => true,
				'created_by' => 0
			],
			'go1AccessToken' => [
				'name' => 'Go1 Access token',
				'type' => 'string',
				'status' => 1,
				'value' => '',
				'description' => 'The "access_token" will be valid for 12 hours. If it expires, it can be refreshed by the OAuth client using the "refresh_token", which is a one-time-use token and valid for 90 days.',
				'secure' => true,
				'created_by' => 0
			],
			'go1RefreshToken' => [
				'name' => 'Go1 Refresh token',
				'type' => 'string',
				'status' => 1,
				'value' => '',
				'description' => 'Each refresh will give you another "refresh_token", which is valid for another 90 days. In case the "access_token" and "refresh_token" are both invalid or not stored, the user will need to reauthorize starting at step 1.',
				'secure' => true,
				'created_by' => 0
			],
			'go1AccessTokenExpiresIn' => [
				'name' => 'Go1 Access token expires in',
				'type' => 'integer',
				'status' => 1,
				'value' => 0,
				'description' => '',
				'created_by' => 0
			],
			'go1AccessTokenType' => [
				'name' => 'Go1 Access token type',
				'type' => 'string',
				'status' => 1,
				'value' => '',
				'description' => '',
				'created_by' => 0
			],
			'go1InstallResourcesCron' => [
				'name' => 'Automatically install resources from authenticated GO1 library',
				'type' => 'boolean',
				'status' => 1,
				'value' => 0,
				'description' => 'If system holds authenticated Go1 credentials, cron task will check for resources on GO1 endpoint and install them automatically if new ones will become available.',
				'created_by' => 0
			],
			'makeResourcesLinktoHome' => [
				'name' => 'Make Resources link to Home',
				'type' => 'boolean',
				'status' => 1,
				'value' => 0,
				'description' => 'Setting this variable to true will allow the learner to navigate to the Home screen (where categories are displayed if activated) when pressing on the resources link in the header bar.',
				'created_by' => 0
			],
			'googleAnalyticsCode' => [
				'name' => 'Google Analytics',
				'type' => 'text',
				'status' => 1,
				'value' => "",
				'description' => 'Add code for Google Analytics in Value field.',
				'created_by' => 0,
			],
			'enableGoogleAnalytics' => [
				'name' => 'Enable Google Analytics code.',
				'type' => 'boolean',
				'status' => 1,
				'value' => 0,
				'description' => 'Enable Google Analytics code.',
				'created_by' => 0,
			],
			'learnerCategoryLandingRows' => [
				'name' => 'Learner Category landing page maximum shown rows.',
				'type' => 'integer',
				'status' => 1,
				'value' => 3,
				'description' => 'Choose between number 2 to 6 to show that many rows of categories in learner landing page, if enabled.',
				'created_by' => 0,
				'delete' => true
			],
			'learnerCategoryLandingMaxColumns' => [
				'name' => 'Maximum items per row in Learner category landing page.',
				'type' => 'integer',
				'status' => 1,
				'value' => 3,
				'description' => 'Choose between number 2 to 4 to show that many category items per row.',
				'created_by' => 0,
			],
			'enableUserFieldAlertSystem' => [
				'name' => 'Enable %%user%% field alert system.',
				'type' => 'boolean',
				'status' => 1,
				'value' => 0,
				'description' => 'List of field names which - when empty - will create a monthly alert asking the user to complete them.',
				'created_by' => 0,
			],
			'userFieldAlertSystemInterval' => [
				'name' => '%%user%% field alert system alert interval/check.',
				'type' => 'integer',
				'status' => 1,
				'value' => 30,
				'description' => '',
				'created_by' => 0,
			],
			'userFieldAlertSystemIntervalRunTime' => [
				'name' => '%%user%% field alert system alert sent time/date',
				'type' => 'string',
				'status' => 1,
				'value' => '',
				'description' => 'Holds stringified date when alert was sent last time.',
				'created_by' => 0,
			],
			'userFieldAlertSystemMonitoredFields' => [
				'name' => 'List of fields in user table, monitored for completion',
				'type' => 'text',
				'status' => 1,
				'value' => "",
				'description' => 'If user field alert system is enabled and fields are added here, they will be checked for completion, if not completed, reminder will be sent to users!',
				'created_by' => 0,
			],
			'hideResourcesInLesson' => [
				'name' => 'Hide learning resources in lessons',
				'type' => 'boolean',
				'status' => 1,
				'value' => 1,
				'description' => 'Hide resources in Learner interface if they are in Lesson',
				'created_by' => 0,
				'update_name_desc' => true,
			],
			'startupCourseID' => [
				'name' => 'Startup Course ID',
				'type' => 'integer',
				'status' => 1,
				'value' => '',
				'description' => 'If this has been defined then this course will play on start up whenever a new user accesses the system i.e. This will no longer show IF the course status = Completed. When this is the case the user will go into the home page as normal.',
				'created_by' => 0,
			],
			'showCriteriaCompletion' => [
				'name' => 'Show Criteria completion percentages',
				'description' => 'Show Criteria completion percentages in learner and administrator interface, usable for Open eLMS for Apprenticeships',
				'type' => 'boolean',
				'status' => 1,
				'value' => 0,
				'created_by' => 0,
				'update_name_desc' => true,
			],
			'salesforceSoapCredentials' => [
				'name' => 'Salesforce SOAP credentials',
				'type' => 'string',
				'status' => 1,
				'value' => '',
				'description' => 'When entering credentials in API section, make sure your user can login only from this server IP (ask support for it) and have only restricted access you require.',
				'created_by' => 0
			],
			'launchResourceText' => [
				'name' => 'Launch button text in Learners interface',
				'type' => 'string',
				'status' => 1,
				'value' => 'Launch resource',
				'description' => 'Custom launch text in Learners interface. Empty value to have default "Launch resource".',
				'created_by' => 0,
				'update_name_desc' => true,
			],
			'learnerSkipUploadPrompt' => [
				'name' => 'Skip "existing learning" prompt in Learners interface.',
				'type' => 'boolean',
				'status' => 1,
				'value' => 1,
				'description' => 'When enabled will not show "Select an existing learning resource or add new" dialogue when uploading in Learners interface, will go stright to uploading.',
				'created_by' => 0,
			],
			'mobileAPI' => [
				'name' => 'Enable mobile API to work with this instance.',
				'type' => 'boolean',
				'status' => 1,
				'value' => 0,
				'description' => 'By enabling, mobile Application will be able to used with this instance for users to log in and do training.',
				'created_by' => 0,
				'delete' => true,
			],
			'learnerProgressGauge' => [
				'name' => 'Show gauge in Learners interface',
				'type' => 'boolean',
				'status' => 1,
				'value' => 1,
				'description' => 'This shows the overall progress gauge for learners in a the header of the learner`s interface.',
				'created_by' => 0,
			],
			'buttonStyle' => [
				'name' => 'eLearning button style',
				'type' => 'string',
				'status' => 1,
				'value' => 'traditional',
				'description' => 'Selected button style for SCORM player.',
				'created_by' => 0,
			],
			'promoGradient' => [
				'name' => 'Promo Image gradient',
				'type' => 'boolean',
				'status' => 1,
				'value' => 1,
				'description' => 'Show gradient on promo image',
				'created_by' => 0,
			],
			'enableMFA' => [
				'name' => 'Multifactor Authentication',
				'type' => 'boolean',
				'status' => 1,
				'value' => 0,
				'description' => 'Enable Multi Factor Authentication for all role types, if you want to disable this feature for particular roles, then edit the role and select to "Disable MFA for this role"',
				'created_by' => 0,
			],
			'IncludeCredasForms' => [
				'name' => 'Include Credas Forms',
				'type' => 'boolean',
				'status' => 1,
				'value' => 0,
				'description' => 'Setting this to true allows you to use the Credas Forms for onboarding students and manage the data process flow between the learner and other parties such as employer and training provider (common with apprenticeships).  The system is also used to collect data on progress reviews.',
				'created_by' => 0,
			],
			'CredasApiKey' => [
				'name' => 'Credas API Key',
				'type' => 'string',
				'status' => 1,
				'value' => '',
				'description' => '',
				'secure' => true,
				'created_by' => 0,
			],
			'CredasAccessUrl' => [
				'name' => 'Credas Access URL',
				'type' => 'string',
				'status' => 1,
				'value' => 'https://connect.credasdemo.com',
				'description' => '',
				'created_by' => 0,
			],
			'CredasCourseTitleSuffix' => [
				'name' => 'Title of Credas Course registration suffix',
				'type' => 'string',
				'status' => 1,
				'value' => 'Apprenticeship registration',
				'description' => 'This value will be appended to title when registering course: "John Johnson Apprenticeship registration"',
				'created_by' => 0,
			],
			'CredasSuperUserEmail' => [
				'name' => 'Credas Super user email specified in call',
				'type' => 'string',
				'status' => 1,
				'value' => 'TEST-superuser@knrservices.co.uk',
				'description' => 'Update with email that was given for this instance.',
				'created_by' => 0,
			],
			'CredasDisableCourseRegistration' => [
				'name' => 'Disable course registration functionality for Credas.',
				'type' => 'boolean',
				'status' => 1,
				'value' => false,
				'description' => "When enabled together with IncludeCredasForms, Course registration is not available, but Credas reports can be used.",
				'created_by' => 0,
			],
			'isOpeneLMSClassroom' => [
				'name' => 'Open eLMS Classroom',
				'type' => 'boolean',
				'status' => 1,
				'value' => 1,
				'description' => 'If this value is true then the system is able to schedule Smart Classroom events within the calendar scheduling system.',
				'created_by' => 0,
				'update_name_desc' => true,
			],
			'LMSTitle' => [
				'name' => 'Title used in HTML tag <TITLE>',
				'type' => 'string',
				'status' => 1,
				'value' => '',
				'description' => '',
				'created_by' => 0,
			],
			'showCalendarTasksOnly' => [
				'name' => 'Show calendar tasks',
				'type' => 'boolean',
				'status' => 1,
				'value' => 1,
				'description' => 'Set "Show calendar tasks only" on learner interface to checked/unchecked.',
				'created_by' => 0,
				'update_name_desc' => true,
			],
			'submitAssessmentText' => [
				'name' => 'Submit Assessment Text',
				'type' => 'string',
				'status' => 1,
				'value' => 'Thank you for filling in these end of module questions.',
				'description' => 'Change this text to alter the feedback given to the learner once the assessment has been submitted.',
				'created_by' => 0,
			],
			'isGroupCountries' => [
				'name' => 'Group Countries',
				'type' => 'boolean',
				'status' => 1,
				'value' => 0,
				'description' => 'Setting this allows you to create Country groups (by region etc.) and group them together when selecting countries from a list.',
				'created_by' => 0,
			],
			'HelpVideoURL' => [
				'name' => 'Help Video',
				'type' => 'string',
				'status' => 1,
				'value' => '',
				'description' => "Setting this value changes the default learner's help video to one of your choice",
				'created_by' => 0,
			],
			'isPeertoPeerVideo' => [
				'name' => 'Peer to Peer Video',
				'type' => 'boolean',
				'status' => 1,
				'value' => 0,
				'description' => "Setting this value to true, will enable learners to communicate with each other via Skype etc. from within the Open eLMS learner interface.",
				'created_by' => 0,
			],
			'DefaultWelcomeText' => [
				'name' => 'Welcome text in log-in screen',
				'type' => 'string',
				'status' => 1,
				'value' => '',
				'description' => 'If empty, will use default.',
				'created_by' => 0,
			],
			'TeamsRedirectUrl' => [
				'name' => 'Custom Microsoft Teams redirect URL',
				'type' => 'string',
				'status' => 1,
				'value' => '',
				'description' => 'If empty, will use default.',
				'created_by' => 0,
			],
			'enableMaytasImport' => [
				'name' => 'Show old MAYTAS import option',
				'type' => 'boolean',
				'status' => 1,
				'value' => 0,
				'description' => 'Will allow import maytas export for importing ILR users, might now rok out of the box as maytas filescan be custom made, contact administration first.',
				'created_by' => 0,
			],
			'enableContactYourColleagues' => [
				'name' => 'Show “contact your colleagues“ section in Learner interface When Launch Conversation is clicked.',
				'type' => 'boolean',
				'status' => 1,
				'value' => 0,
				'description' => '',
				'created_by' => 0,
			],
			'TasksShowByDefaultCalendarEntriesOnly' => [
				'name' => 'Task list shows calendar entries only',
				'type' => 'boolean',
				'status' => 1,
				'value' => 1,
				'description' => 'Setting this value to False will show all tasks (including those with no expected deadline or appointment associated with it that does not appear on the users or managers calendars.)',
				'created_by' => 0,
			],
			'TasksSortedbySoonestExpectedCompletionDate' => [
				'name' => 'Task list sorted by expected completion date',
				'type' => 'boolean',
				'status' => 1,
				'value' => 1,
				'description' => 'Setting this value to true will sort these tasks by the latest task first, if false then the earliest task will be the first listed (i.e. the one that the user is most behind with.)',
				'created_by' => 0,
			],
			'TasksDefaultSelectedTaskType' => [
				'name' => 'Task list filtered by a selected type when loads',
				'type' => 'string',
				'status' => 1,
				'value' => 'learning',
				'description' => 'If a task type is entered then the list is filtered by this task when it is first loaded.',
				'created_by' => 0,
			],
			'BadgrApiKey' => [
				'name' => 'Data for Badgr API',
				'value' => ',',
				'description' => '',
				'type' => 'string',
				'status' => 1,
				'secure' => true,
				'created_by' => 0,
			],
			'badgesEnabled' => [
				'name' => 'Status of Badges API',
				'type' => 'boolean',
				'value' => false,
				'status' => 1,
				'description' => '',
				'created_by' => 0,
			],
			'badgrRegion' => [
				'name' => 'Badgr API Region',
				'type' => 'string',
				'value' => 'eu.',
				'status' => 1,
				'description' => '',
				'created_by' => 0,
			],
			'allowJamboards' => [
				'name' => 'Use Jamboards',
				'type' => 'boolean',
				'value' => 0,
				'status' => 1,
				'description' => 'Set this value to true if you wish to use Jamboard smart boards whenever creating a live lesson.',
				'created_by' => 0,
			],
			'GoogleJamboardClientId' => [
				'name' => 'Google Jamboard Client Id',
				'type' => 'string',
				'value' => '',
				'status' => 1,
				'description' => '',
				'created_by' => 0,
			],
			'GoogleJamboardClientSecret' => [
				'name' => 'Google Jamboard Client Secret',
				'type' => 'string',
				'value' => '',
				'status' => 1,
				'description' => '',
				'secure' => true,
				'created_by' => 0,
			],
			'JamboardTemplateFileId' => [
				'name' => 'Jamboard Template FileId',
				'type' => 'string',
				'value' => '',
				'status' => 1,
				'description' => '',
				'created_by' => 0,
			],
			'JamboardRedirectUrl' => [
				'name' => 'Jamboard Redirect Url',
				'type' => 'string',
				'value' => '',
				'status' => 1,
				'description' => '',
				'created_by' => 0,
			],
			'addCustomProgrammeStatus' => [
				'name' => 'Custom Programme Status',
				'type' => 'boolean',
				'status' => 1,
				'value' => 0,
				'description' => 'Setting this value to true allows the user to be defined by additional customisable statuses.  This allows more granularity to the standard Not Started, In Progress and Completed statuses - this does not replace these core statuses though.',
				'created_by' => 0,
			],
			'hideProgrammeStatusFromLearnerProgressView' => [
				'name' => 'Hide Programme status from learner progress view',
				'type' => 'boolean',
				'status' => 1,
				'value' => 1,
				'description' => 'Setting this value to true will hide Programme status from learner progress view.',
				'created_by' => 0,
			],
			'showOnlyAssignedCategories' => [
				'name' => 'Show only assigned categories for Learner.',
				'type' => 'boolean',
				'value' => 0,
				'status' => 1,
				'description' => 'Show only categories that are linked with resources assigned to Learner, learner interface. Upload/Evidence/Blog entry, etc...',
				'created_by' => 0,
			],
			'isApproveLearners' => [
				'name' => 'Approve learners before use.',
				'type' => 'boolean',
				'value' => 0,
				'status' => 1,
				'description' => 'If this is set to true, then any user who self-register on the system will need to be approved before using the system.',
				'created_by' => 0,
			],
			'PasswordExpiryDays' => [
				'name' => 'Password Expiry in Days',
				'type' => 'integer',
				'status' => 1,
				'value' => '',
				'description' => 'Entering a number here will set a number of days after which a password will need to be refreshed.  The user will get warned of this on logon.',
				'created_by' => 0,
			],
			'PasswordMaxAttempts' => [
				'name' => 'Password Maximum Attempts',
				'type' => 'integer',
				'status' => 1,
				'value' => '',
				'description' => 'Entering a number here will limit the number of access attempts allowed.',
				'created_by' => 0,
			],
			'H5P_CLIENT_ID' => [
				'name' => 'H5P Client Id',
				'type' => 'string',
				'value' => 'emilelchananreisserweston-a8432',
				'status' => 1,
				'description' => 'H5P LTI 1.1 Client Id - this is licenced for demo purposes only (DO NOT USE ON A LIVE INSTALL AS THESE RESOURCES ARE LIKELY TO GET DELETED PERIODICALLY).  Change the LTI configuration if H5P is purchased by your organisation to your own secret key.  See https://www.e-learningwmb.com/page/h5p for details on how this is done.',
				'created_by' => 0,
			],
			'H5P_CLIENT_SECRET' => [
				'name' => 'H5P Client Secret',
				'type' => 'string',
				'value' => 'tsqvZC2P9sZx0eOTjIE99AoyYWbjweA0',
				'status' => 1,
				'description' => 'H5P LTI 1.1 Secret  - this is licenced for demo purposes only (DO NOT USE ON A LIVE INSTALL AS THESE RESOURCES ARE LIKELY TO GET DELETED PERIODICALLY).  Change the LTI configuration if H5P is purchased by your organisation to your own secret key.  See https://www.e-learningwmb.com/page/h5p for details on how this is done.',
				'secure' => true,
				'created_by' => 0,
			],
			'H5P_LTI_URL' => [
				'name' => 'H5P LTI 1.1 Url',
				'type' => 'string',
				'value' => 'https://elearningwmb.h5p.com/lti',
				'status' => 1,
				'description' => 'H5P LTI 1.1 Url  - this is licenced for demo purposes only (DO NOT USE ON A LIVE INSTALL AS THESE RESOURCES ARE LIKELY TO GET DELETED PERIODICALLY).  Change the LTI configuration if H5P is purchased by your organisation to your own secret key.  See https://www.e-learningwmb.com/page/h5p for details on how this is done.',
				'created_by' => 0,
			],
			'allowIframeEmbedFromDomains' => [
				'name' => 'Specify domains that can embed site.',
				'description' => "Example: 'self' example.com *.example.net",
				'type' => 'string',
				'value' => '',
				'status' => 1,
				'created_by' => 0,
			],
			'reEnableUsersOnImport' => [
				'name' => 'Enable disabled users on Import.',
				'type' => 'boolean',
				'value' => 1,
				'status' => 1,
				'description' => 'If manual or automatic user import is done on user who was disabled on system, user will be enabled back to active status.',
				'created_by' => 0,
			],
			'PaymentsEngine' => [
				'name' => 'Payments Engine.',
				'type' => 'boolean',
				'status' => 1,
				'value' => 0,
				'description' => 'If set to true then cost settings are enabled to interact with the system’s configured payments engine.',
				'created_by' => 0,
			],
			'redirectBackToLesson' => [
				'name' => 'Redirect learner back to lesson upon closing learning resource that is part of lesson.',
				'type' => 'boolean',
				'status' => 1,
				'value' => 1,
				'description' => 'If learning resource is part of lesson, after closing player for that resource, learner is taken back to lesson.',
				'created_by' => 0,
			],
			'CertificateMessageTop' => [
				'name' => 'Certificate top message.',
				'type' => 'string',
				'status' => 1,
				'value' => $settings['DefaultCertificateMessageTop'],
				'description' => 'First message for certificate below logo.',
				'created_by' => 0,
			],
			'CertificateMessageBottom1' => [
				'name' => 'Certificate bottom first message.',
				'type' => 'string',
				'status' => 1,
				'value' => $settings['DefaultCertificateMessageBottom1'],
				'description' => 'First bottom message, footer.',
				'created_by' => 0,
			],
			'CertificateMessageBottom2' => [
				'name' => 'Certificate bottom second message.',
				'type' => 'string',
				'status' => 1,
				'value' => $settings['DefaultCertificateMessageBottom2'],
				'description' => 'Second bottom message, footer.',
				'created_by' => 0,
			],
			'enableGlobalOutlookIntegration' => [
				'name' => 'Enable Global Outlook integration.',
				'type' => 'boolean',
				'status' => 1,
				'value' => 0,
				'description' => "Add all events to a single (global) Outlook calendar. The GlobalOutlookIntegrationSecurityToken options must also be set.",
				'created_by' => 0,
			],
			'GlobalOutlookIntegrationSecurityToken' => [
				'name' => 'Global Outlook integration security token.',
				'type' => 'string',
				'status' => 1,
				'value' => $settings['GlobalOutlookIntegrationSecurityToken'] ?? "",
				'description' => "Security token used to add all events to users' calendars. Use this link to generate a token " . $settings["LMSUrl"] . "./teams/globaloutlooktoken",
				'created_by' => 0,
			],
			'addAllEventsToOutlook' => [
				'name' => 'Add all events to Outlook.',
				'type' => 'boolean',
				'status' => 1,
				'value' => false,
				'description' => "Automatically add all events to Outlook. This will only work if enableGlobalOutlookIntegration is true and GlobalOutlookIntegrationSecurityToken contains a valid security token. ",
				'created_by' => 0,
			],
			'DefaultLearnerScreen' => [
				'name' => 'Default Learner Screen.',
				'type' => 'select-list',
				'status' => 1,
				'value' => 'resources',
				'select_values' => [
					[
						'name' => 'Calendar',
						'value' => 'tasks/calendar'
					],
					[
						'name' => 'Tasks',
						'value' => 'tasks/list'
					],
					[
						'name' => 'Learning',
						'value' => 'resources'
					]
				],
				'description' => "The default learner screen is \"Learning\".  Set to \"Calendar\", \"Tasks\" or \"Learning\" to change the screen the learner loads in once logged in or presses the home link.\n\nConsider following other configuration options:\n1) startupCourseID - by specifying resource ID, user will be redirected to it and it will be auto-played.\n2) isLearnerLandingPage - if this is set to true, learner will be redirected to category landing page.",
				'created_by' => 0,
			],
			'hideReviewButton' => [
				'name' => 'Hide review button',
				'type' => 'boolean',
				'status' => 1,
				'value' => 1,
				'description' => 'This hides the old style review functionality from the Manager’s view of the learner record which has been superseded',
				'created_by' => 0
			],
			'DisableUserDepartmentOnImport' => [
				'name' => 'When user is imported with specified department, they are automatically disabled.',
				'type' => 'string',
				'status' => 1,
				'value' => '',
				'description' => 'Specify exact Department name in value field if you wish to automatically disable users, with that department, during import.',
				'created_by' => 0,
			],
			'EnableUserDepartmentOnImport' => [
				'name' => 'When user is imported with specified department, they are automatically enabled.',
				'type' => 'string',
				'status' => 1,
				'value' => '',
				'description' => 'Specify exact Department name in value field if you wish to automatically enable users, with that department, during import.',
				'created_by' => 0,
			],
			'allowLearnerUploads' => [
				'name' => 'Allow Learner Uploads.',
				'type' => 'boolean',
				'status' => 1,
				'value' => 1,
				'description' => 'Making this value false will prevent any learners from uploading files to the system.',
				'created_by' => 0,
			],
			'MicrosoftAppClientId' => [
				'name' => 'Microsoft Application Client Id.',
				'type' => 'string',
				'status' => 1,
				'value' => '68a2742e-92f8-471a-b1c4-7251ca5b2fa9',
				'description' => trim("
				You need to define the MicrosoftAppClientId and MicrosoftAppClientSecret to integrate MS Services. " .
				"Currently these values are set using eLearning WMB’s MS Azure values for demonstration purposes only.\n\n" .
				"To create the app, log in to https://portal.azure.com and create an app.  Then set the value in Open eLMS " .
				"Configuration settings for the MicrosoftAppClientId to its client id and set MicrosoftAppClientSecret. " .
				"Give this app the correct permissions (i.e. accessing Teams, One Drive, PowerBi, Outlook calendar, etc.) " .
				"and set up the following redirect urls: \n\n" .
					str_replace($settings["LMSUri"], "", $settings["LMSUrl"]) . "/msintegration/teams (to enable teams integraton)\n" .
					str_replace($settings["LMSUri"], "", $settings["LMSUrl"]) . "/msintegration/powerbi (powerbi)\n" .
					str_replace($settings["LMSUri"], "", $settings["LMSUrl"]) . "/msintegration/teams/outlook (outlook)\n" .
					str_replace($settings["LMSUri"], "", $settings["LMSUrl"]) . "/msintegration/teams/onedrive (one drive, needed to fetch teams recordings)\n"),
				'created_by' => 0,
			],
			'MicrosoftAppClientSecret' => [
				'name' => 'Microsoft Application Client Secret.',
				'type' => 'string',
				'status' => 1,
				'secure' => 1,
				'value' => '3yrR[RZ_2nuF82gwtKTUBMJjYCstxw]:',
				'description' => trim("
				You need to define the MicrosoftAppClientId and MicrosoftAppClientSecret to integrate MS Services. " .
				"Currently these values are set using eLearning WMB’s MS Azure values for demonstration purposes only.\n\n" .
				"To create the app, log in to https://portal.azure.com and create an app.  Then set the value in Open eLMS " .
				"Configuration settings for the MicrosoftAppClientId to its client id and set MicrosoftAppClientSecret. " .
				"Give this app the correct permissions (i.e. accessing Teams, One Drive, PowerBi, Outlook calendar, etc.) " .
				"and set up the following redirect urls: \n\n" .
					str_replace($settings["LMSUri"], "", $settings["LMSUrl"]) . "/msintegration/teams (to enable teams integraton)\n" .
					str_replace($settings["LMSUri"], "", $settings["LMSUrl"]) . "/msintegration/powerbi (powerbi)\n" .
					str_replace($settings["LMSUri"], "", $settings["LMSUrl"]) . "/msintegration/teams/outlook (outlook)\n" .
					str_replace($settings["LMSUri"], "", $settings["LMSUrl"]) . "/msintegration/teams/onedrive (one drive, needed to fetch teams recordings)\n"),
				'created_by' => 0,
			],
			'UseHTMLLineBreaksInOutlook' => [
				'name' => 'Replace line breaks in Outlook calendar emails with HTML tags.',
				'type' => 'boolean',
				'status' => 1,
				'value' => 0,
				'description' => 'Replace new lines with <BR/> tags.',
				'created_by' => 0,
			],
			'isTrackLearningResourceTime' => [
				'name' => 'Track Time for Learning Resources.',
				'type' => 'boolean',
				'status' => 1,
				'value' => 1,
				'description' => 'Set this value to true to track time on each learning resource.',
				'created_by' => 0,
			],
			'enableImpersonate' => [
				'name' => 'Enable Impersonate Learners by Admin functionality.',
				'type' => 'boolean',
				'status' => 1,
				'value' => 1,
				'description' => 'Set this value to true to allow Administrators impersonate Learners.',
				'created_by' => 0,
			],
			'isCivicaPaymentsEngine' => [
				'name' => 'Enabled the Civica Payments Engine',
				'type' => 'boolean',
				'status' => 1,
				'value' => 0,
				'description' => 'Enabling this option will allow learning to be purchased from the system.',
				'created_by' => 0,
			],
			'civicaDefaultGeneralLedgerCode' => [
				'name' => 'Civica Default General Ledger Code',
				'type' => 'string',
				'status' => 1,
				'value' => '',
				'description' => 'This is the default general ledger code used for Civica payments.  The system otherwise records the ‘Learning Resource Code’ if it is defined.',
				'created_by' => 0,
			],
			'civicaPaymentsEngineRequestURL' => [
				'name' => 'Civica Payments Engine Request URL',
				'type' => 'string',
				'status' => 1,
				'value' => 'https://www.civicaepay.co.uk/SuffolkPartnershipXMLTest/Paylinkxmlui/Default.aspx',
				'description' => 'Request URL for Civica Payments Engine. Note: remove the word Test for the Live configuration',
				'created_by' => 0,
			],
			'civicaPaymentsEngineVatCode' => [
				'name' => 'Civica Vat code relating to product',
				'type' => 'string',
				'status' => 1,
				'value' => '05',
				'description' => 'Vat code relating to product, default to use is 05 zero rated [01|Standard|20.00] [03|Fuel rate|5.00] [04|Out of scope|0.00] [05|Zero rated|0.00] [06|Exempt:0.00]',
				'created_by' => 0,
			],
			'showSevenDepartmentSubLevels' => [
				'name' => 'Show 6 sublevels of department',
				'type' => 'boolean',
				'status' => 1,
				'value' => false,
				'description' => "Setting this value to true will create 7 levels of location each connected to each other e.g. London, Southwark, Tooley Street, Building A, Zone B, Room 2, Desk Y)",
				'created_by' => 0,
			],
			"showResetCustomDatesButton" => [
				'name' => 'Show reset custom completion dates button for learning programmes',
				'type' => 'select-list',
				'status' => 1,
				'value' => 'none',
				'select_values' => [
					[
						'name' => 'Administrators',
						'value' => 'admin'
					],
					[
						'name' => 'Managers',
						'value' => 'manager'
					],
					[
						'name' => 'No one',
						'value' => 'none'
					]
				],
				'description' => "Level of access to the option to reset custom completion dates for ALL users in a learning programme",
				'created_by' => 0,
			],
			"showUserResetCustomDatesButton" => [
				'name' => 'Show reset custom completion dates button for users',
				'type' => 'boolean',
				'status' => 1,
				'value' => false,
				'description' => "Show to the button for reseting custom completion dates for a particular user in a given learning programme",
				'created_by' => 0,
			],
			"optionalResourceProgrammeLink" => [
				'name' => 'Optional %%programme%% selection',
				'type' => 'boolean',
				'status' => 1,
				'value' => false,
				'description' => "Make %%programme%% selection optional",
				'created_by' => 0,
			],
			"listAllEventsToManagers" => [
				'name' => 'Show all events to all Managers',
				'type' => 'boolean',
				'status' => 1,
				'value' => false,
				'description' => "All managers will be able to see all events, with certain restrictions.",
				'created_by' => 0,
			],
		];
		foreach ($configuration_values as $key => $configuration_value) {

			// If disableResourceTypes, and schools, disable some types
			if (
				$key == 'disableResourceTypes' &&
				$settings['licensing']['version'] == 'openelmsschools'
			) {
				$configuration_value['value'] = 'classroom, on_the_job, reflective_log, vimeo, google_classroom';
			}

			if (empty($configuration_value['delete'])) {
				$entry = \Models\Configuration
					::firstOrCreate(
						['key' => $key],
						[
							'name' => $configuration_value['name'],
							'type' => $configuration_value['type'],
							'status' => $configuration_value['status'],
							'value' => $configuration_value['value'],
							'select_values' => isset($configuration_value['select_values']) ? $configuration_value['select_values'] : null,
							'description' => $configuration_value['description'],
							'created_by' => isset($configuration_value['created_by']) ? $configuration_value['created_by'] : 0,
							'secure' => (isset($configuration_value['secure']) ? $configuration_value['secure'] : false),
						]
					)
				;

				if (isset($configuration_value['secure']) && ($configuration_value['secure'] == 1)){
					$entry->secure = 1;
					$entry->save();
				}
				// For certain configuration option, force it for certain site types
				// enableSchedule for schools/colleges
				if (
					$key == 'enableSchedule' &&
					(
						$settings['licensing']['version'] == 'openelmsschools' ||
						$settings['licensing']['version'] == 'openelmscolleges'
					)
				) {
					$entry->value = 1;
					$entry->save();
				}

				if (
					isset($configuration_value['force']) &&
					$configuration_value['force']
				) {
					$entry->name = $configuration_value['name'];
					$entry->type = $configuration_value['type'];
					$entry->status = $configuration_value['status'];
					$entry->value = $configuration_value['value'];
					$entry->select_values = isset($configuration_value['select_values']) ? $configuration_value['select_values'] : null;
					$entry->description = $configuration_value['description'];
					$entry->created_by = isset($configuration_value['created_by']) ? $configuration_value['created_by'] : 0;
					$entry->secure = isset($configuration_value['secure']) ? $configuration_value['secure'] : false;
					$entry->save();
				} else if (
					isset($configuration_value['update_name_desc']) &&
					$configuration_value['update_name_desc']
				) {
					$entry->name = $configuration_value['name'];
					$entry->description = $configuration_value['description'];
					$entry->secure = isset($configuration_value['secure']) ? $configuration_value['secure'] : false;
					$entry->save();
				} else if (isset($configuration_value['secure'])) {
					$entry->secure = $configuration_value['secure'];
					$entry->save();
				}
			} else {
				if ($configuration_value['delete']) {
					// delete entry, if marked for deletion
					\Models\Configuration::where('key', $key)->delete();
				}
			}
		}
	}
}
