<?php
namespace DB;

use Illuminate\Database\Capsule\Manager as Capsule;


class Schema
{
	private $_tables = [
		'countries',
		'designations',
		'designation_learning_modules', // if SMCR, designations/jobs have resources assigned to them, in the order, like a lesson, duplicated functionality, more or less. Also same functionality where you need to complete previous ones to continue
		'companies',
		'company_comments', // Store comments where company-user is linked.
		'company_resource_interested_in', // Store information what resources and how many learners company is interested in
		'departments',
		'locations',
		'cities',
		'roles',
		'roles_access',
		'role_credas_actors',
		'sessions',
		'groups',
		'group_users',
		'manager_users',
		'manager_departments',
		'manager_groups',
		'manager_learning_modules',
		'manager_learning_module_categories',
		'competencies',
		'users',
		'user_sub_departments',
		'learning_providers',
		'learning_module_categories',
		'learning_module_types',
		'learning_module_types_parameter',
		'learning_module_competencies',
		'learning_modules',
		'learning_module_prerequisites',
		'learning_module_feedback',
		'learning_sessions',
		'scorm_scorm_scoes',
		'scorm_scorm',
		'scorm_course',
		'scorm_course_modules',
		'scorm_scorm_scoes_track',
		'scorm_user',
		'scorm_role_assignments',
		'learning_course_modules',
		'user_learning_modules',
		'user_custom_reviews',
		'department_custom_reviews',
		'group_custom_reviews',
		'designation_custom_reviews',
		'department_learning_modules',
		'department_standards',
		'group_learning_modules',
		'group_standards',
		'learning_results',
		'learning_results_comments', // comments can be added to learning results where sign-off can be used.
		'email_templates',
		'email_queue',
		'email_history',
		'assessment_answers',
		'assessment_categories',
		'assessment_category_questions',
		'assessment_data',
		'assessment_questions',
		'assessment_task_comments',
		'assessment_tasks',
		'labels',
		'user_competencies',
		'error_log',
		'available_modules',
		'timings',
		'apprenticeship_standards',
		'apprenticeship_standards_users',
		'apprenticeship_issue_categories',
		'apprenticeship_issue_categories_users', // Naming has changed along the lines, those are outcomes now, but this table will hold criteria percentage, average. Abstract number.
		'apprenticeship_outcome_groups', // definition of groups that are used for outcomes
		'apprenticeship_optional_outcomes', // Link between groups and outcomes
		'apprenticeship_optional_outcome_users', // Where learners will keep data of selected groups
		'apprenticeship_issues',
		'apprenticeship_issues_user_disabled', // Can disable specific issues for specific users, will remove assigned learning from user
		'apprenticeship_issues_learning_modules', // Learning resources are linked with standard issues here.
		'apprenticeship_issues_user_learning_modules', // table where module/resource is assigned against issue, but only for specific user.
		'apprenticeship_issues_evidence', // Evidence type resouce are linked here with multiple standard issues(match to), evidence type is unique resource created by user and assigned to that user only.
		'apprenticeship_issues_departments', // links issue-department-lesson
		'apprenticeship_issues_department_schedules', // link date-time-duration with 'apprenticeship_issues_departments' for when lesson repeats.
		'apprenticeship_issues_groups',
		//'apprenticeship_sectors', // predefined standard sector, used to determine funding
		'apprenticeship_routes', // predefined standard route, used to determine funding
		'apprenticeship_routes_standards',
		'learning_module_evidences', // Evidence type resource can have files/comments attached, in this table
		'learning_module_evidence_meetings', // Evidence type resource can have meetings set up, this is where they are stored
		'forgotten_password_tokens',
		'configuration',
		'version',
		'ilr_templates',

		'manager_reviews', // Used for learners profile if apprentix is enabled, manager can leave review
		'manager_reviews_comments', // a review can contain multiple comments.

		'structure', // replacement of pages
		'role_structure',
		'custom_reviews',
		'custom_reviews_filters',

		// Some initial tables for ILR field values
		'ethnicities',
		'sexes',
		'lldd_health_problems',
		'lldd_health_problems_categories',
		'prior_attainments',
		'prior_attainment_legacy',
		'learning_module_containers', // Where strings/hashes will be stored
		'monthly_ilr_exports',
		'yearly_ilr_exports',
		'free_jackdaw_modules', // List of resources that can be installed for free as jackdaw users, identified by name, to install from reposotory

		'email_statistics', // will keep stats of how much e-mails are sent daily.
		'email_refresh_statistics', // when eash refresh e-mail is sent, save in this table, in case something fails, we have a trail and can re-send.

		// SMCR tables
		'smcr_staff_types',
		'smcr_functions_responsibilities',
		'smcr_staff_functions_responsibilities',  // Questionable naming
		'smcr_committees',
		'smcr_committee_roles',
		'smcr_committee_role_personnel',
		'smcr_reports',
		'smcr_report_types',
		'smcr_f_p_categories',

		// Evidence types
		'evidence_types', // or maybe it would be better as 'learning_module_evidence_types' ???


		// ILR tables, lots of them! Naming, in one hand, adding 's' at the end would keep the naming logic, but keeping same name as in ILR documentation, might be better.
		// Will shadow data in here, for a start, for reports, whenever user is added/updated, learning delivery is updated in this table! Eventually will use only this table.
		'ilr_learning_deliveries', // LearningDelivery
		'ilr_learning_delivery_aim_types', // AimType
		'ilr_learning_delivery_programme_types', // ProgType
		'ilr_learning_delivery_outcomes', // LearningDeliveryOutcome
		'ilr_learning_delivery_completion_status', // CompStatus
		'ilr_learning_delivery_funding_models', // FundModel
		'ilr_learning_delivery_financial_records', //AppFinRecord
		'ilr_learning_delivery_financial_record_types', //AFinType
		'ilr_learning_delivery_financial_record_codes', //AFinCode
		'ilr_learning_delivery_monitorings',
		'ilr_learner_employment_statuses', // LearnerEmploymentStatus
		'ilr_employment_statuses', // links to ilr_learner_employment_statuses "EmpStat" field
		'ilr_learner_destination_and_progressions',
		'ilr_progression_outcome_codes',
		'ilr_progression_outcome_types',
		/*
		'ilr_learning_delivery_fam', // LearningDeliveryFAM
		'ilr_learning_delivery_work_placement', // LearningDeliveryWorkPlacement
		'ilr_app_fin_record', // AppFinRecord
		'ilr_provider_spec_delivery_monitoring', // ProviderSpecDeliveryMonitoring
		'ilr_learning_delivery_he', // LearningDeliveryHE
		*/

		//dashboards tables
		'dashboards',
		'statistics',
		'dashboard_statistics',
		'statistics_history',
		'dataviews',
		'dashboard_dataviews',

		'favorites',
		'table_history', // Initial keep of history!
		'comments', // Univeral comments table, too much something_comments tables, should use only one. Will move all comments here eventually.
		'files', // Universal file table, same as comments table
		'meetings', // Same, universal

		'daily_statistics', // Every day cron task will update statistics for every site.
		'daily_statistics_resources',

		'skill_scans', // Tied to learning_results entry if module resource is "is_skillscan".
		'skill_scan_answers',
		'skill_scan_histories',


		'crons', // Cron tasks that need to be ran, frequency/run time/etc will be saved.

		'batch_reports', // Each time report is generated, its data is saved in database for historical use/audit. Can also add frequency when report is ran next time automatically and saved.
		'batch_report_data',
		'batch_report_managers'

		'gateway_readiness', // For marking standard/categories
		'user_gateway_readiness', // Linking user with gateway readiness and category/standard

		// Relationships between QA role and user-resource. To keep track if QA role had any interaction with users learning results.
		'qa_learning_results',

		// log which logs all access to each system (log in time, log out time and user name).
		// https://bitbucket.org/emilrw/scormdata/issues/1091/logging-access-to-system
		'log_authentications', // Logs log ins, log outs and automatic log outs.
		'log_export_imports',

		// Maybe, the right way!
		'schedules', // Entries in calendar, parent children structure, where parent can have multiple children, repeats.
		'schedule_links', // Holds links from entries in calendar to lessons, issues, resources, users, departments
		'schedule_permissions', // Links creator and anyone who is assigned to schedule.
		'schedule_visit_types',
		'event_types',

		'holidays', // start-end day for holidays, usable for school types,

		'picklists', // Multi functional table that holds simple name-slug-type data for drop-downs in various places.

		'table_extensions', // extension table that will link to any other table, will hold field name/type/value/order/status/table
		'table_extension_fields', // collection of fields for tables that can be extended!
		/*Discussion Forum Tables*/
		'forums', //forums manage the functionality control and is related to events one to one relation
		'topics', //Forum can have multiple topics one to many relation, The topic swill be posted by manager/learner and based on user_id the relations are managed
		'posts', //A Forum can have multiple posts related to it, that are posted by various user

		'country_groups',

		/*Quality controls */
		'quality_controls',

		/*Resource Query Builder*/
		'resource_query_variables',   //Stores variables either status
		'resource_query_variable_conditions',   //Stores conditions of each variable
		'resource_query_variable_parameters',   //Stores variable parameter of each variable
		'resource_queries',   //Stores condition's values of each variable

		'learning_module_versions',

		// Credas
		'credas_actors', // Holds list of actors/ids for credas course registrations
		'credas_processes',
		'credas_process_actors',
		'credas_courses', // Data of holy trinity will be here!
		'credas_reports', // Save generated report for display
		'credas_autofill_mappings',

		// functionality to overwrite default system labels from licensing file
		'default_labels',

		'quality_controls_report_view',
		'designation_competencies',
		'custom_programme_statuses',
		'user_custom_programme_statuses',
		'venues',
		'table_deleted', // Keeps deleted entries of specific tables, for historical reason

		//store outlook ids of events/tasks which have been added to a user
		'user_outlook_events',
        'user_payment_transactions'
	];

	private $_foreign_keys = [];

	private function addForeignKey($source_table, $dest_table, $source_field, $dest_field, $key_name='')
	{
		if (!isset($this->_foreign_keys[$source_table]))
		{
			$this->_foreign_keys[$source_table] = [];
		}
		$this->_foreign_keys[$source_table][] = [$dest_table, $source_field, $dest_field, $key_name];
	}

	public function createForeignKeys()
	{
		foreach($this->_foreign_keys as $source_table => $key_defs)
		{
			Capsule::schema()->table($source_table, function ($table) use ($key_defs) {
				foreach($key_defs as $key_def)
				{
					list($dest_table, $source_field, $dest_field, $key_name) = $key_def;
					// In case shorter foreign key needs to be passed, there is 64 character limit for keys and if table/field combination exceeds it, error is inevitable!.
					if ($key_name) {
						$table->foreign($source_field, $key_name)->references($dest_field)->on($dest_table);
					} else {
						$table->foreign($source_field)->references($dest_field)->on($dest_table);
					}
				}
			});
		}
	}

	private function createGroups() {
		Capsule::schema()->create('groups', function($table) {
			$table->increments('id');
			$table->string('name');
			$table->boolean('is_jackdaw_team');
			$table->boolean('add_remove_resources')->default(true);
			$table->boolean('status');
			$table->timestamps();
			$table->index('name');
		});
	}

	private function createLearningModuleVersions() {
		Capsule::schema()->create('learning_module_versions', function($table ) {
			$table->increments('id');
			$table->integer('version')->unsigned();
			$table->integer('learning_module_id')->unsigned();
			$table->text('material');
			//For type uploads
			$table->integer('evidence_type_id')->unsigned()->nullable();
			$table->boolean('require_management_signoff')->default(false);//upload,webpage,h5p
			$table->timestamps();
		});
		$this->addForeignKey('learning_module_versions', 'learning_modules', 'learning_module_id', 'id');
	}

	private function createGroupUsers()
	{
		Capsule::schema()->create('group_users', function($table) {
			$table->increments('id');
			$table->integer('user_id')->unsigned();
			$table->integer('group_id')->unsigned();
			$table->boolean('status');
			$table->timestamps();
		});

		$this->addForeignKey('group_users', 'users', 'user_id', 'id');
		$this->addForeignKey('group_users', 'groups', 'group_id', 'id');
	}

	private function createRoles () {
		Capsule::schema()->create('roles', function($table) {
			$table->increments('id');
			$table->string('name');
			$table->boolean('is_admin')->default(false); // Administrator
			$table->boolean('is_manager')->default(false); // Coach/Trainer
			$table->boolean('is_learner')->default(false); // Trainee
			$table->boolean('is_demo')->default(false); // Demo account, should have no access to saving anything.
			$table->boolean('is_qa')->default(false); // Quality Assurer
			$table->boolean('is_cd')->default(false); // Curriculum Developer
			$table->boolean('is_fa')->default(false); // Financial Auditor
			$table->boolean('access_all_companies')->default(false);
			$table->boolean('access_all_learners')->default(false);
			$table->boolean('sign_off_learner_status')->default(false);
			$table->boolean('hide_set_training_tab')->default(true);
			$table->boolean('hide_qa_tab')->default(true);
			$table->boolean('hide_set_pending_assessment_tab')->default(true);
			$table->boolean('hide_sign_off_tab')->default(true);
			$table->boolean('hide_approve_and_manage_booking')->default(true);
			$table->boolean('disable_edit_ilr_fields')->default(false);
			$table->boolean('admin_interface')->default(false); // Show administrator interface if user is "is_manager"
			$table->boolean('show_all_resources')->default(false); // Show all resources for admin in learning library, previously hidden, created by/for individual users.
			$table->boolean('email_disable_manager_notifications')->default(false);
			$table->boolean('exclude_manager_from_schedule')->default(false);
			$table->string('jackdaw_type')->nullable();
			$table->string('image');
			$table->text('description');
			$table->boolean('show_creator_menu')->default(false);
			$table->boolean('status');
			$table->timestamps();
			$table->index('name');
		});
	}

	private function createRolesAccess () {
		Capsule::schema()->create('roles_access', function($table) {
			$table->increments('id');
			$table->integer('role_id')->unsigned(); //
			$table->integer('access_id')->unsigned(); // other role's ID's.
			$table->boolean('status')->default(true);
			$table->timestamps();
		});
	}

	private function createRoleCredasActors () {
		Capsule::schema()->create('role_credas_actors', function($table) {
			$table->increments('id');
			$table->integer('role_id')->unsigned(); //
			$table->integer('credas_actor_id')->unsigned(); // other role's ID's.
			$table->boolean('status')->default(true);
			$table->timestamps();
		});

		$this->addForeignKey('role_credas_actors', 'roles', 'role_id', 'id');
		$this->addForeignKey('role_credas_actors', 'credas_actors', 'credas_actor_id', 'id');
	}

	private function createCities () {
		Capsule::schema()->create('cities', function($table) {
			$table->increments('id');
			$table->string('name');
			$table->integer('country_id')->unsigned();
			$table->boolean('status');
			$table->timestamps();
			$table->index('name');
		});

		$this->addForeignKey('cities', 'countries', 'country_id', 'id');
	}

	private function createLocations ()
	{
		Capsule::schema()->create('locations', function($table) {
			$table->increments('id');
			$table->string('name');
			$table->boolean('status');
			$table->timestamps();
			$table->index('name');
		});
	}

	private function createDepartments () {
		Capsule::schema()->create('departments', function($table) {
			$table->increments('id');
			$table->string('name');
			$table->integer('company_id')->unsigned();
			$table->string('email')->nullable();
			$table->text('address')->nullable();
			$table->string('phone')->nullable();
			$table->integer('parent_id')->unsigned();
			$table->boolean('status');
			$table->timestamps();
			$table->index('name');
		});

		$this->addForeignKey('departments', 'companies', 'company_id', 'id');
		$this->addForeignKey('departments', 'departments', 'parent_id', 'id');
	}


	private function createCompanies ()
	{
		Capsule::schema()->create('companies', function($table) {
			$table->increments('id');
			$table->string('name');
			$table->string('email')->nullable();
			$table->string('phone')->nullable();
			$table->string('reference_number')->default("");
			$table->text('address')->nullable();
			$table->text('message')->nullable();
			$table->string('urlextension')->nullable();
			$table->string('logo')->nullable()->default(NULL);
			$table->string('login_bg')->nullable()->default(NULL);
			$table->string('learner_bg')->nullable()->default(NULL);
			$table->string('e_learning_thumbnail')->nullable()->default(NULL);
			$table->string('e_learning_button_style')->nullable()->default(NULL);
			$table->integer('max_users')->unsigned()->nullable();
			$table->date("next_contact_date")->nullable();
			$table->boolean('status');
			$table->timestamps();
			$table->index('name');
		});
	}

	private function createCompanyComments () {
		Capsule::schema()->create('company_comments', function($table) {
			$table->increments('id');
			$table->text('comment');
			$table->integer('company_id')->unsigned();
			$table->integer('user_id')->unsigned()->nullable();
			$table->boolean('status');
			$table->timestamps();

			$this->addForeignKey('company_comments', 'companies', 'company_id', 'id');
			$this->addForeignKey('company_comments', 'users', 'user_id', 'id');
		});
	}

	private function createCompanyResourceInterestedIn () {
		Capsule::schema()->create('company_resource_interested_in', function($table) {
			$table->increments('id');
			$table->integer('company_id')->unsigned();
			$table->integer('people')->unsigned()->default(0);
			$table->integer('user_id')->unsigned()->nullable(); // Added by
			$table->integer('learning_module_id')->unsigned();
			$table->boolean('status');
			$table->timestamps();

			$this->addForeignKey('company_resource_interested_in', 'companies', 'company_id', 'id');
			$this->addForeignKey('company_resource_interested_in', 'users', 'user_id', 'id');
			$this->addForeignKey('company_resource_interested_in', 'learning_modules', 'learning_module_id', 'id');
		});
	}

	private function createDesignations()
	{
		Capsule::schema()->create('designations', function($table) {
			$table->increments('id');
			$table->string('name');
			$table->boolean('order_resources'); // For SMCR
			$table->boolean('status');
			$table->timestamps();
			$table->index('name');
		});
	}

	private function createDesignationLearningModules() {
		Capsule::schema()->create('designation_learning_modules', function($table) {
			$table->increments('id');
			$table->integer('designation_id')->unsigned();
			$table->integer('learning_module_id')->unsigned();
			$table->integer('order')->unsigned();
			$table->boolean('status');
			$table->timestamps();
		});
	}

	private function createDesignationCompetencies () {
		Capsule::schema()->create('designation_competencies', function($table) {
			$table->increments('id');
			$table->integer('designation_id')->unsigned();
			$table->integer('competency_id')->unsigned();
			$table->boolean('status')->default(true);
			$table->timestamps();
		});

		$this->addForeignKey('designation_competencies', 'designations', 'designation_id', 'id');
		$this->addForeignKey('designation_competencies', 'competencies', 'competency_id', 'id');
	}

	private function createCountries()
	{
		Capsule::schema()->create('countries', function($table) {
			$table->increments('id');
			$table->string('name');
			$table->integer('country_group_id')->unsigned()->nullable();
			$table->integer('display_order')->default(10);
			$table->boolean('status');
			$table->timestamps();
			$table->index('name');
		});
	}
	private function createCountryGroups()
	{
		Capsule::schema()->create('country_groups', function($table) {
			$table->increments('id');
			$table->string('name');
			$table->integer('position')->unsigned();
			$table->boolean('status');
			$table->timestamps();
			$table->index('name');
		});
	}

	private function createUsers()
	{
		Capsule::schema()->create('users', function($table) {
			$table->increments('id');
			$table->string('username');
			$table->string('usercode')->default("");
			//$table->string('fname')->default(""); // migrate to "GivenNames" eventually
			//$table->string('lname')->default(""); // migrate to "FamilyName" eventually

			$table->string('fname', 100)->default("")->comment = "Family name";
			$table->string('lname', 100)->default("")->comment = "Given names";
			$table->string('image', 255)->nullable();
			$table->string('e_signature', 255)->nullable();
			$table->string('password')->nullable();
			$table->string('email', 100)->default("")->comment = "Email address"; // migrate to Email later
			$table->string('phone', 18)->default("")->comment = "Telephone number"; //migrate to TelNo later!
			$table->string('school', 255)->nullable();
			$table->integer('designation_id')->unsigned()->nullable();
			$table->date("designation_assigned")->nullable(); // Date when designation was assigned to user
			$table->integer('country_id')->unsigned()->nullable();
			$table->integer('company_id')->unsigned()->nullable();
			$table->integer('department_id')->unsigned()->nullable();
			$table->integer('location_id')->unsigned()->nullable();
			$table->integer('city_id')->unsigned()->nullable();
			$table->integer('role_id')->unsigned()->nullable();
			$table->integer('shadow_role_id')->unsigned()->nullable();
			$table->text('description')->nullable();
			$table->dateTime("last_login_dt")->nullable();
			$table->dateTime("previous_last_login_dt")->nullable();
			$table->dateTime("expiration_dt")->nullable();
			$table->dateTime("registration_dt")->nullable();
			$table->boolean('status')->default(0);
			$table->boolean('self_attested')->default(false);
			$table->boolean('self_attested_reminder_sent')->default(false);
			$table->boolean('certified')->default(false);
			$table->integer('updated_by')->unsigned()->nullable();

			$table->boolean('exclude_from_reports')->default(false);
			$table->boolean('exclude_from_ilr_export')->default(false);
			$table->boolean('exclude_from_emails')->default(false); // Do not send email to users with this checked!

			// Those two will be visible only if isSMCR is set to true
			$table->integer('staff_type_id')->unsigned()->nullable(); // table 'smcr_staff_types', can be null too
			$table->date("staff_type_assigned")->nullable(); // Date when Staff type is assigned
			$table->integer('report_to')->unsigned()->nullable(); // Any user, can be null too.
			$table->date("staff_type_sign_off_approval")->nullable(); // Date when sign off approval was sent to manager/s.


			//Will add two fields, for managers, used to store employee overall progress and fall behind for standards.
			$table->decimal('manager_percentage', 5, 1)->default(0);
			$table->decimal('manager_percentage_behind', 5, 1)->default(0);

			// Emergency contact details
			/*
				Name
				Relationship
				Contact Number/s
			*/
			$table->string('emergency_name', 255)->nullable();
			$table->string('emergency_relationship', 255)->nullable();
			$table->string('emergency_contact_numbers', 1000)->nullable();

			//  Visa Eligibility Checks
			/*
				If you are on a visa give the length of time living in the UK? ☐ Under 3 years ☐ Over 3 years
				Please provide passport / visa number
				Date of issue
			*/
			$table->enum('visa_length', ['Under 3 years', 'Over 3 years'])->nullable();
			$table->string('visa_number', 255)->nullable();
			$table->date('visa_date')->nullable();



			/*
				Learner overview from issue:
				https://bitbucket.org/emilrw/scormdata/issues/574/2-view-training-data-by-employee
				It will be updated in User::calculateGlobalProgress Module, either individually or globally
			*/
			// Total learning resources
			$table->smallInteger('total_resources')->unsigned()->nullable();
			$table->smallInteger('total_resources_smcr')->unsigned()->nullable();
			// Not Started
			$table->smallInteger('not_started_resources')->unsigned()->nullable();
			$table->smallInteger('not_started_resources_smcr')->unsigned()->nullable();
			// In Progress
			$table->smallInteger('in_progress_resources')->unsigned()->nullable();
			$table->smallInteger('in_progress_resources_smcr')->unsigned()->nullable();
			// Completed
			$table->smallInteger('completed_resources')->unsigned()->nullable();
			$table->smallInteger('completed_resources_smcr')->unsigned()->nullable();
			// Total Time spent
			$table->decimal('time_spent', 7, 2)->default(0);
			$table->decimal('time_spent_smcr', 7, 2)->default(0);
			$table->decimal('time_spent_off_the_job_training', 7, 2)->default(0);
			// % Completed
			$table->decimal('completed', 5, 2)->default(0);
			$table->decimal('completed_smcr', 5, 2)->default(0); // Completed resoures assigned to F&P category.

			// Add user's weeks working hours
			// https://bitbucket.org/emilrw/scormdata/issues/628/working-week-hours
			$table->decimal('week_hours', 5, 2)->nullable()->default(null);

			// https://bitbucket.org/emilrw/scormdata/issues/578/6-people-have-statuses
			$table->enum('learning_status', ['Completed', 'In Progress', 'Not Started'])->default('Not Started');

			// https://bitbucket.org/emilrw/scormdata/issues/579/7-add-last-completion-date-and-next
			$table->date('last_completion_date')->nullable();
			$table->date('next_completion_date')->nullable();

			// https://bitbucket.org/emilrw/scormdata/issues/720/9-adding-last-contact-date
			$table->date('last_contact_date')->nullable();

			/*
				tiny - 127 (99) - 2
				small - 32767 (9999) - 4
				medium - 8388607 (999999) - 6
				int - 2147483647 (999999999) - 9
				big - 9223372036854775807 (999999999999999999) - 18

			*/


			// ILR Specification for 2016 to 2017 – Version 3, new fields
				//Learning Provider
			$table->integer('UKPRN')->unsigned()->nullable()->comment = "UK provider reference number";
			$table->string('LearnRefNumber', 12)->default("")->comment = "Learner reference number";
			$table->string('PrevLearnRefNumber', 12)->default("")->comment = "Learner reference number in previous year";
			$table->integer('PrevUKPRN')->unsigned()->nullable()->comment = "UKPRN in previous year";
			$table->integer('PMUKPRN')->unsigned()->nullable()->comment = "The UKPRN of the provider prior to the merger.";
			$table->bigInteger('ULN')->unsigned()->nullable()->comment = "Unique learner number";
			$table->string('CampId', 8)->default("")->comment = "Campus Identifier";
			$table->integer('OTJHours')->unsigned()->nullable()->comment = "Off-the-job training hours";
			//$table->string('FamilyName', 100)->default("")->comment = "Family name";
			//$table->string('GivenNames', 100)->default("")->comment = "Given names";
			$table->date('DateOfBirth')->nullable()->comment = "Date of birth";
			$table->tinyInteger('Ethnicity')->unsigned()->nullable()->comment = "Ethnicity";
			$table->string('Sex', 1)->default("")->comment = "Sex";
			$table->tinyInteger('LLDDHealthProb')->unsigned()->nullable()->comment = "LLDD and health problem";
			$table->string('NINumber', 9)->default("")->comment = "National Insurance number";
			$table->tinyInteger('PriorAttainLegacy')->unsigned()->nullable()->comment = "Prior attainment";
			$table->text('PriorAttain')->nullable()->comment = "The learner's prior attainment when a new learning agreement has been agreed between the learner and the provider.";
			$table->tinyInteger('Accom')->unsigned()->nullable()->comment = "Accommodation";
			$table->mediumInteger('ALSCost')->unsigned()->nullable()->comment = "Learning support cost";
			$table->smallInteger('PlanLearnHours')->unsigned()->nullable()->comment = "Planned learning hours";
			$table->smallInteger('PlanEEPHours')->unsigned()->nullable()->comment = "Planned employability, enrichment and pastoral hours";
			$table->string('MathGrade', 4)->default("")->comment = "GCSE maths qualification grade";
			$table->string('EngGrade', 4)->default("")->comment = "GCSE English qualification grade";
			$table->string('PostcodePrior', 8)->default("")->comment = "Postcode prior to enrolment";
			$table->string('Postcode', 8)->default("")->comment = "Postcode";
			$table->string('AddLine1', 50)->default("")->comment = "Address line 1";
			$table->string('AddLine2', 50)->default("")->comment = "Address line 2";
			$table->string('AddLine3', 50)->default("")->comment = "Address line 3";
			$table->string('AddLine4', 50)->default("")->comment = "Address line 4";
			//$table->string('TelNo', 18)->default("")->comment = "Telephone number"; //uses "phone" field
			//$table->string('Email', 100)->default("")->comment = "Email address"; // uses "email" field

			// will contain JSON string
			$table->text('ContactPreference')->nullable()->comment = "Learner Contact Preference Entity Definition";
			$table->text('LLDDandHealthProblem')->nullable()->comment = "LLDD and Health Problem Entity Definition";
			$table->text('LearnerFAM')->nullable()->comment = "Learner Funding and Monitoring Entity Definition";
			$table->text('ProviderSpecLearnerMonitoring')->nullable()->comment = "Learner Provider Specified Monitoring Entity Definition";
			$table->text('LearnerEmploymentStatus')->nullable()->comment = "Learner Employment Status Entity Definition";
			$table->text('LearnerHE')->nullable()->comment = "Learner HE Entity Definition";
			$table->text('LearningDelivery')->nullable()->comment = "Learning Delivery Entity Definition";
			$table->text('LearnerDestinationandProgression')->nullable()->comment = "Learner Destination and Progression Entity Definition";
			// EOF json string fields

			// Fields only for MAYTAS
			$table->text('MAYTAS')->nullable()->comment = "All extra fields from MAYTAS format";

			//Skype Id
			$table->string('skype_id', 100)->nullable()->comment = "User skype id";

			//Zoom Id
			$table->string('zoom_id', 100)->nullable()->comment = "User Zoom id";

			//Teams Id
			$table->string('teams_id', 400)->nullable()->comment = "User Teams id";

			$table->timestamps();
			$table->index('username');
			$table->index('usercode');
			$table->index('fname');
			$table->index('lname');
			$table->index('email');
			$table->unique('username');
			$table->unique('email');
		});

		$this->addForeignKey('users', 'designations', 'designation_id', 'id');
		$this->addForeignKey('users', 'countries', 'country_id', 'id');
		$this->addForeignKey('users', 'companies', 'company_id', 'id');
		$this->addForeignKey('users', 'departments', 'department_id', 'id');
		$this->addForeignKey('users', 'locations', 'location_id', 'id');
		$this->addForeignKey('users', 'cities', 'city_id', 'id');
		$this->addForeignKey('users', 'roles', 'role_id', 'id');
		$this->addForeignKey('users', 'roles', 'shadow_role_id', 'id');
	}

	private function createTimings () {
		Capsule::schema()->create('timings', function($table) {
			$table->increments('id');
			$table->string('label');
			$table->string('key');
			$table->integer('timing')->unsigned();
			$table->date('start_date')->nullable(); // some timings have a start date.
			$table->timestamps();
		});
	}

	private function createSessions()
	{
		Capsule::schema()->create('sessions', function($table) {
			$table->increments('id');
			$table->integer('user_id')->unsigned();
			$table->string('hash');
			$table->boolean('api')->default(false); // set to true if logging in by API call
			$table->integer('api_access_count')->unsigned()->default(0);
			$table->timestamps();
			$table->index('hash');
		});

		$this->addForeignKey('sessions', 'users', 'user_id', 'id');
	}

	private function createStructure() {
		Capsule::schema()->create('structure', function($table) {
			$table->increments('id');
			$table->string('name');
			$table->string('name_template')->nullable(); // Custom html after name!
			$table->string('key');
			$table->string('description')->nullable();
			$table->string('description_template')->nullable();
			$table->string('icon')->nullable();
			$table->string('color')->nullable();
			$table->integer('parent_id')->unsigned()->nullable();
			$table->integer('order')->unsigned()->default(0);
			$table->boolean('status')->default(0);
			$table->boolean('show_help')->default(0);
			$table->boolean('hidden')->default(0); // Do not list when retrieving from menu.php, used only for permissions

			$table->timestamps();

			$table->index('name');
			$table->unique('key');
		});
	}

	private function createRoleStructure() {
		Capsule::schema()->create('role_structure', function($table) {
			$table->increments('id');
			$table->integer('role_id')->unsigned();
			$table->integer('structure_id')->unsigned();
			$table->boolean('view')->default(0); // show page in menu
			$table->boolean('select')->default(0); // allow to retrieve data
			$table->boolean('insert')->default(0); // allow to insert data
			$table->boolean('update')->default(0); // allow to update data
			$table->boolean('disable')->default(0); // allow to update data
			// can be added with more types if/when needed
			$table->timestamps();
		});

		$this->addForeignKey('role_structure', 'structure', 'structure_id', 'id');
		$this->addForeignKey('role_structure', 'roles', 'role_id', 'id');
	}

	private function createManagerUsers()
	{
		Capsule::schema()->create('manager_users', function($table) {
			$table->increments('id');
			$table->integer('manager_id')->unsigned();
			$table->integer('user_id')->unsigned();
			$table->timestamps();
		});

		$this->addForeignKey('manager_users', 'users', 'manager_id', 'id');
		$this->addForeignKey('manager_users', 'users', 'user_id', 'id');
	}

	private function createManagerDepartments()
	{
		Capsule::schema()->create('manager_departments', function($table) {
			$table->increments('id');
			$table->integer('manager_id')->unsigned();
			$table->integer('department_id')->unsigned();
			$table->timestamps();
		});

		$this->addForeignKey('manager_departments', 'users', 'manager_id', 'id');
		$this->addForeignKey('manager_departments', 'departments', 'department_id', 'id');
	}
	private function createManagerGroups()
	{
		Capsule::schema()->create('manager_groups', function($table) {
			$table->increments('id');
			$table->integer('manager_id')->unsigned();
			$table->integer('group_id')->unsigned();
			$table->timestamps();
		});

		$this->addForeignKey('manager_groups', 'users', 'manager_id', 'id');
		$this->addForeignKey('manager_groups', 'groups', 'group_id', 'id');
	}

	private function createManagerLearningModules()
	{
		Capsule::schema()->create('manager_learning_modules', function($table) {
			$table->increments('id');
			$table->integer('manager_id')->unsigned();
			$table->integer('learning_module_id')->unsigned();
			$table->timestamps();
		});

		$this->addForeignKey('manager_learning_modules', 'users', 'manager_id', 'id');
		$this->addForeignKey('manager_learning_modules', 'learning_modules', 'learning_module_id', 'id');
	}

	private function CreateManagerLearningModuleCategories() {
		Capsule::schema()->create('manager_learning_module_categories', function($table) {
			$table->increments('id');
			$table->integer('manager_id')->unsigned();
			$table->integer('learning_module_category_id')->unsigned();
			$table->timestamps();
		});

		$this->addForeignKey('manager_learning_module_categories', 'users', 'manager_id', 'id');
		$this->addForeignKey('manager_learning_module_categories', 'learning_module_categories', 'learning_module_category_id', 'id', 'manager_category_key');
	}

	private function createCompetencies()
	{
		Capsule::schema()->create('competencies', function($table) {
			$table->increments('id');
			$table->string('name');
			$table->integer('required_points')->unsigned();
			$table->text('description')->nullable();

			// added
			$table->text('badge')->nullable();

			$table->boolean('status')->default(0);
			$table->timestamps();
			$table->index('name');
		});
	}

	private function createlearningProviders()
	{
		Capsule::schema()->create('learning_providers', function($table) {
			$table->increments('id');
			$table->string('company')->nullable();
			$table->string('contactname')->nullable();
			$table->string('email')->nullable();
			$table->string('phone')->nullable();
			$table->string('address')->nullable();
			$table->boolean('status')->default(0);
			$table->timestamps();
			$table->index('company');
			$table->index('contactname');
			$table->index('email');
			$table->index('phone');
			$table->index('address');

		});
	}

	private function createLearningModuleCategories()
	{
		Capsule::schema()->create('learning_module_categories', function($table) {
			$table->increments('id');
			$table->string('name');
			$table->boolean('is_mandatory')->default(0);
			$table->string('landing_image')->nullable()->default(NULL);
			$table->boolean('status')->default(0);
			$table->timestamps();
			$table->index('name');
		});


	}

	private function createLearningModuleTypes()
	{
		Capsule::schema()->create('learning_module_types', function($table) {
			$table->increments('id');
			$table->string('name');
			$table->string('slug');
			$table->boolean('status')->default(0);
			$table->text('field')->default(NULL)->after('status');
			$table->boolean('custom')->default(0)->after('status');
			$table->text('field')->default(NULL);
			$table->timestamps();
			$table->index('name');
		});

	}

	private function createLearningModuleTypesParameter()
	{
		Capsule::schema()->create('learning_module_types_parameter', function($table) {
			$table->increments('id');
			$table->integer('learning_module_types_id')->unsigned();
			$table->string('parametername');
			$table->string('parameterslug');
			$table->string('parametertype');
			$table->string('mandatorycompletion');
			$table->boolean('status')->default(1);
			$table->timestamps();
		});

	}


	private function createLearningModuleCompetencies()
	{
		Capsule::schema()->create('learning_module_competencies', function($table) {
			$table->increments('id');
			$table->integer('learning_module_id')->unsigned();
			$table->integer('competency_id')->unsigned();
			$table->integer('points')->unsigned();
			$table->index('learning_module_id');
			$table->index('competency_id');
		});

		$this->addForeignKey('learning_module_competencies', 'competencies', 'competency_id', 'id');
		$this->addForeignKey('learning_module_competencies', 'learning_modules', 'learning_module_id', 'id');
	}

	private function createLearningModulePrerequisites()
	{
		Capsule::schema()->create('learning_module_prerequisites', function($table) {
			$table->increments('id');
			$table->integer('learning_module_id')->unsigned();
			$table->integer('prerequisite_id')->unsigned();
			$table->index('learning_module_id');
			$table->index('prerequisite_id');
		});

		$this->addForeignKey('learning_module_prerequisites', 'learning_modules', 'learning_module_id', 'id');
		$this->addForeignKey('learning_module_prerequisites', 'learning_modules', 'prerequisite_id', 'id');
	}

	private function createLearningModuleFeedback() {
		Capsule::schema()->create('learning_module_feedback', function($table) {
			$table->increments('id');
			$table->integer('module_id')->unsigned();
			$table->integer('user_id')->unsigned();
			$table->string('feedback', 2000);
			$table->integer('rating')->unsigned();
			$table->boolean('anonymous')->default(false);
			$table->boolean('status')->default(1);
			$table->timestamps();

#			$table->index('feedback');
		});

		$this->addForeignKey('learning_module_feedback', 'users', 'user_id', 'id');
		$this->addForeignKey('learning_module_feedback', 'learning_modules', 'module_id', 'id');
	}

	private function createLearningSessions()
	{
		Capsule::schema()->create('learning_sessions', function($table) {
			$table->increments('id');
			$table->integer('learning_module_id')->unsigned();
			$table->integer('user_id')->unsigned();
			$table->boolean('approved')->default(false);
			$table->boolean('completed')->default(false);
			$table->dateTime('session_date');
			$table->string('trainer')->default('N/A');
			$table->string('location');
			$table->string('duration');
			$table->string('session_uid');

			$table->timestamps();
			$table->index('learning_module_id');
			$table->index('user_id');
			$table->index('session_date');
			$table->index('location');
			$table->index('duration');
			$table->index('session_uid');
		});

		$this->addForeignKey('learning_sessions', 'users', 'user_id', 'id');
		$this->addForeignKey('learning_sessions', 'learning_modules', 'learning_module_id', 'id');
	}

	private function createLearningCourseModules()
	{
		Capsule::schema()->create('learning_course_modules', function($table) {
			$table->increments('id');
			$table->integer('learning_course_id')->unsigned();
			$table->integer('learning_module_id')->unsigned();
			$table->index('learning_course_id');
			$table->index('learning_module_id');

			$table->timestamps();

		});

		$this->addForeignKey('learning_course_modules', 'learning_modules', 'learning_course_id', 'id');
		$this->addForeignKey('learning_course_modules', 'learning_modules', 'learning_module_id', 'id');
	}

	private function createLearningModules()
	{
		Capsule::schema()->create('learning_modules', function($table) {
			$table->increments('id');
			$table->string('code')->default('');
			$table->string('name');
			$table->integer('category_id')->unsigned()->nullable();
			$table->integer('f_p_category_id')->unsigned()->nullable();
			$table->string('keywords')->default('');
			$table->boolean('self_enroll')->default(false);
			$table->boolean('print_lesson')->default(true);
			$table->boolean('approval')->default(false);
			$table->integer('company_id')->unsigned()->nullable();
			$table->boolean('refresh')->default(false);
			$table->integer('refresh_period')->unsigned()->default(0);
			$table->integer('refresh_repeat')->unsigned()->nullable()->default(null);
			$table->boolean('refresh_custom_email')->default(false);
			$table->string('refresh_custom_email_subject')->default("Course Refresh Notification");
			$table->mediumText('refresh_custom_email_body')->nullable()->default(null);
			$table->integer('due_after_period')->unsigned()->default(7);
			$table->date('expiration_date')->nullable()->default(null);
			$table->mediumText('description')->nullable()->default(null);
			$table->integer('type_id')->unsigned()->nullable();
			$table->text('material');
			$table->boolean('is_course')->unsigned()->default(0);
			$table->boolean('order_modules')->unsigned()->default(0);
			$table->string('language')->default('');
			$table->integer('cost')->nullable();
			$table->smallInteger('duration_hours')->unsigned()->default(0);
			$table->tinyInteger('duration_minutes')->unsigned()->default(0);
			$table->boolean('duration_change')->default(true);
			$table->integer('provider_id')->unsigned()->nullable();
			$table->integer('responsible_user')->unsigned()->nullable()->default(null); // Coach/Trainer responsible for asset
			$table->tinyInteger('level')->unsigned()->nullable();
			$table->boolean('do_prerequisite')->default(0);
			$table->boolean('status')->default(0);
			$table->string('thumbnail')->default('');
			$table->string('promo_image')->default('');
			$table->string('highlight_image')->default('');
			$table->string('accreditation_main_logo')->default('');
			$table->string('accreditation_logo')->default('');
			$table->text('accreditation_description')->nullable();
			$table->boolean('jackdaw')->default(false); // This specifies that resource is editable by jackdaw editor.
			$table->boolean('jackdaw_resource')->default(false); // Whenever resource is created using Jackdaw editor, this sets to true, false for all other cases even if installed by repository.
			$table->string('jackdaw_access_token')->default(''); // single use token to load resource using jackdaw editor without user authentication.
			$table->boolean('project')->default(false); // for evidence/upload(7) type, project work that indicates this was created by manager
			$table->integer('created_by')->unsigned()->nullable();
			$table->integer('created_by_group')->unsigned()->nullable();
			$table->integer('updated_by')->unsigned()->nullable();
			$table->string('api_match')->default(''); // adminstrator will be able to match any string against specific module
			$table->tinyInteger('rating')->unsigned()->nullable();

			// If resource is evidence, that specific evidence can have different types
			$table->integer('evidence_type_id')->unsigned()->nullable();

			$table->integer('event_type_id')->unsigned()->nullable();

			// If resource is created as guideline evidence, use this boolean as true and then filter out resource from library or other places.
			$table->boolean('guideline')->default(false);

			$table->boolean('is_skillscan')->default(false);

			// Track learning resource for completion percentage in profile/programme, true by default.
			$table->boolean('track_progress')->default(true);

			// Print certificate boolean, default on
			$table->boolean('print_certificate')->default(true);

			// Whenever learning resource is created in learner's interface, this is set to true;
			$table->boolean('created_in_learner_interface')->default(false);

			// Some 3rd party scorm courses have different dimensions, specify custom player size in pixels
			$table->integer('player_width')->unsigned()->nullable()->default(null);
			$table->integer('player_height')->unsigned()->nullable()->default(null);

			// GO1 api integration, this will hold value if resource was installed by GO1
			$table->integer('go1_id')->unsigned()->nullable()->default(null);

			// Visible to learner, defaults to true, option for managers to hide and exclude from any calculations
			$table->boolean('visible_learner')->default(true);

			// Does not require management sign-off?
			$table->boolean('require_management_signoff')->default(false);

			$table->integer('deleted_by')->unsigned()->nullable();
			$table->timestamps();
			$table->softDeletes();

			$table->index('code');
			$table->index('name');
			$table->index('keywords');
		});

		$this->addForeignKey('learning_modules', 'learning_module_categories', 'category_id', 'id');
		$this->addForeignKey('learning_modules', 'smcr_f_p_categories', 'f_p_category_id', 'id');
		$this->addForeignKey('learning_modules', 'learning_module_types', 'type_id', 'id');
		$this->addForeignKey('learning_modules', 'companies', 'company_id', 'id');
		$this->addForeignKey('learning_modules', 'learning_providers', 'provider_id', 'id');
		$this->addForeignKey('learning_modules', 'users', 'created_by', 'id');
		$this->addForeignKey('learning_modules', 'groups', 'created_by_group', 'id');
	}

	private function createScormScormScoes()
	{
		Capsule::schema()->create('scorm_scorm_scoes', function($table) {
			$table->bigIncrements('id');
			$table->bigInteger('scorm')->default(0);
			$table->string('manifest')->default('');
			$table->string('organization')->default('');
			$table->string('parent')->default('');
			$table->string('identifier')->default('');
			$table->string('launch')->default('');
			$table->string('scormtype', 5)->default('sco');
			$table->string('title')->default('');
			$table->index('scorm');
		});
	}

	private function createScormScorm()
	{
		Capsule::schema()->create('scorm_scorm', function($table) {
			$table->bigIncrements('id');
			$table->bigInteger('course')->default(0);
			$table->string('name')->default('');
			$table->string('reference')->default('');
			$table->text('summary')->nullable();
			$table->string('version', 9)->default('');
			$table->double("maxgrade", 8, 4)->default(100);
			$table->tinyInteger("grademethod")->default(1);
			$table->bigInteger("whatgrade")->default(0);
			$table->bigInteger("maxattempt")->default(0);
			$table->tinyInteger("updatefreq")->default(0);
			$table->string('md5hash', 32)->default("c182abd7edd2bc32d17f0283e83911bf");
			$table->bigInteger("launch")->default(1);
			$table->bigInteger("skipview")->default(0);
			$table->bigInteger("hidebrowse")->default(0);
			$table->bigInteger("hidetoc")->default(0);
			$table->bigInteger("hidenav")->default(0);
			$table->bigInteger("auto")->default(0);
			$table->bigInteger("popup")->default(0);
			$table->string('options')->default('');
			$table->bigInteger("width")->default(100);
			$table->bigInteger("height")->default(500);
			$table->bigInteger("timemodified")->default(1360175270);
			$table->index('course');
		});
	}

	private function createScormCourse()
	{
		Capsule::schema()->create('scorm_course', function($table) {
			$table->bigIncrements('id');
			$table->bigInteger('category')->default(1);
			$table->bigInteger('sortorder')->default(100);
			$table->string("password", 50)->default('');
			$table->string("fullname", 254)->default('');
			$table->string("shortname", 100)->default('');
			$table->string("idnumber", 100)->default('');
			$table->text("summary")->nullable();
			$table->string("format", 10)->default('scorm');
			$table->tinyInteger("showgrades")->default(1);
			$table->longText('modinfo')->nullable();
			$table->mediumInteger("newsitems")->default(5);
			$table->string("teacher", 100)->default("Teacher");
			$table->string("teachers", 100)->default("Teachers");
			$table->string("student", 100)->default("Student");
			$table->string("students", 100)->default("Students");
			$table->tinyInteger("guest")->default(0);
			$table->bigInteger('startdate')->default(1360195200);
			$table->bigInteger('enrolperiod')->default(0);
			$table->mediumInteger("numsections")->default(10);
			$table->bigInteger("marker")->default(0);
			$table->bigInteger("maxbytes")->default(67108864);
			$table->smallInteger("showreports")->default(0);
			$table->tinyInteger("visible")->default(1);
			$table->tinyInteger("hiddensections")->default(0);
			$table->smallInteger("groupmode")->default(0);
			$table->smallInteger("groupmodeforce")->default(0);
			$table->bigInteger('defaultgroupingid')->default(0);
			$table->string("lang", 30)->default("");
			$table->string("theme", 50)->default("");
			$table->string("cost", 10)->default("");
			$table->string("currency", 3)->default("USD");
			$table->bigInteger('timecreated')->default(1360174508);
			$table->bigInteger('timemodified')->default(1360174508);
			$table->tinyInteger("metacourse")->default(0);
			$table->tinyInteger("requested")->default(0);
			$table->tinyInteger("restrictmodules")->default(0);
			$table->tinyInteger("expirynotify")->default(0);
			$table->bigInteger('expirythreshold')->default(864000);
			$table->tinyInteger("notifystudents")->default(0);
			$table->tinyInteger("enrollable")->default(1);
			$table->bigInteger('enrolstartdate')->default(0);
			$table->bigInteger('enrolenddate')->default(0);
			$table->string("enrol", 20)->default('');
			$table->bigInteger("defaultrole")->default(0);


			$table->index('category');
			$table->index('idnumber');
			$table->index('shortname');
		});
	}

	private function createScormCourseModules()
	{
		Capsule::schema()->create('scorm_course_modules', function($table) {
			$table->bigIncrements('id');
			$table->bigInteger('course')->default(0);
			$table->bigInteger('module')->default(14);
			$table->bigInteger('instance')->default(0);
			$table->bigInteger('section')->default(1);
			$table->string("idnumber", 100)->nullable()->default("");
			$table->bigInteger('added')->default(1360175271);
			$table->smallInteger("score")->default(0);
			$table->mediumInteger("indent")->default(0);
			$table->tinyInteger("visible")->default(1);
			$table->tinyInteger("visibleold")->default(1);
			$table->tinyInteger("groupmode")->default(0);
			$table->bigInteger("groupingid")->default(0);
			$table->smallInteger("groupmembersonly")->default(0);

			$table->index('course');
			$table->index('module');
			$table->index('visible');
			$table->index('instance');
			$table->index('groupingid');
			$table->index(['idnumber', 'course']);
		});

	}

	private function createScormScormScoesTrack()
	{
		Capsule::schema()->create('scorm_scorm_scoes_track', function($table) {
			$table->bigIncrements('id');
			$table->bigInteger('userid')->unsigned();
			$table->bigInteger('scormid')->unsigned();
			$table->bigInteger('scoid')->unsigned();
			$table->bigInteger('attempt')->unsigned();
			$table->string('element', 255);
			$table->longtext('value');
			$table->bigInteger('timemodified')->defalt(1360174508);
			$table->index('userid');
			$table->index('scormid');
			$table->index('scoid');
			$table->index('element');
			$table->index(['scormid', 'userid', 'element']);
			$table->unique(['scormid', 'userid', 'element', 'scoid','attempt'], "unique_key_sdklj3");
		});

	}

	private function createScormUser()
	{
		Capsule::schema()->create('scorm_user', function($table) {
			$table->bigIncrements('id');
			$table->string('auth', 20)->default('manual');
			$table->tinyInteger('confirmed')->default(1);
			$table->tinyInteger('policyagreed')->default(0);
			$table->tinyInteger('deleted')->default(0);
			$table->bigInteger('mnethostid')->default(1);
			$table->string('username', 100)->default('');
			$table->string('password', 32)->default('4f107c53663892d543811c703f5a5f82');
			$table->string('idnumber')->default('');
			$table->string('firstname', 100)->default('');
			$table->string('lastname', 100)->default('');
			$table->string('email', 100)->default('');
			$table->tinyInteger('emailstop')->default(0);
			$table->string('icq', 15)->default('');
			$table->string('skype', 50)->default('');
			$table->string('yahoo', 50)->default('');
			$table->string('aim', 50)->default('');
			$table->string('msn', 50)->default('');
			$table->string('phone1', 20)->default('12345');
			$table->string('phone2', 20)->default('');
			$table->string('institution', 40)->default('');
			$table->string('department', 30)->default('');
			$table->string('address', 70)->default('');
			$table->string('city', 20)->default('Lonodon');
			$table->string('country', 2)->default('UK');
			$table->string('lang', 30)->default('en_utf8');
			$table->string('theme', 50)->default('');
			$table->string('timezone', 100)->default('99');
			$table->bigInteger('firstaccess')->default(1360050016);
			$table->bigInteger('lastaccess')->default(1370679544);
			$table->bigInteger('lastlogin')->default(1369373573);
			$table->bigInteger('currentlogin')->default(1369374226);
			$table->string('lastip', 15)->default('127.0.0.1');
			$table->string('secret', 15)->default('');
			$table->tinyInteger('picture')->default(0);
			$table->string('url')->default('');
			$table->text('description')->nullable();
			$table->tinyInteger('mailformat')->default(1);
			$table->tinyInteger('maildigest')->default(0);
			$table->tinyInteger('maildisplay')->default(1);
			$table->tinyInteger('htmleditor')->default(1);
			$table->tinyInteger('ajax')->default(1);
			$table->tinyInteger('autosubscribe')->default(1);
			$table->tinyInteger('trackforums')->default(0);
			$table->bigInteger('timemodified')->default(1369373573);
			$table->bigInteger('trustbitmask')->default(0);
			$table->string('imagealt')->default('');
			$table->tinyInteger('screenreader')->default(0);

			$table->unique(['mnethostid', 'username'], "unique_key_sdklj3sask2");
			$table->index('deleted');
			$table->index('confirmed');
			$table->index('firstname');
			$table->index('lastname');
			$table->index('city');
			$table->index('country');
			$table->index('lastaccess');
			$table->index('email');
			$table->index('auth');
			$table->index('idnumber');
		});
	}

	private function createScormRoleAssignments()
	{
		Capsule::schema()->create('scorm_role_assignments', function($table) {
			$table->bigIncrements('id');
			$table->bigInteger('roleid')->default(1);
			$table->bigInteger('contextid')->default(1);
			$table->bigInteger('userid');
			$table->tinyInteger('hidden')->default(0);
			$table->bigInteger('timestart')->default(0);
			$table->bigInteger('timeend')->default(0);
			$table->bigInteger('timemodified')->default(1360049717);
			$table->bigInteger('modifierid')->default(0);
			$table->string('enrol', 20)->default('manual');
			$table->bigInteger('sortorder')->default(0);

			$table->unique(['contextid', 'roleid', 'userid'], "unique_key_458934");
			$table->index('sortorder');
			$table->index('roleid');
			$table->index('contextid');
			$table->index('userid');
		});
	}

	private function createUserLearningModules()
	{
		Capsule::schema()->create('user_learning_modules', function($table) {
			$table->increments('id');
			$table->integer('user_id')->unsigned();
			$table->integer('learning_module_id')->unsigned();
			$table->integer('created_by')->unsigned()->nullable()->default(null);
			$table->integer('deleted_by')->unsigned()->nullable()->default(null);
			$table->string('comment_link')->default('');
			$table->string('comment_unlink')->default('');

			$table->timestamps();
			$table->softDeletes();

			$table->index('user_id');
			$table->index('learning_module_id');
		});
		$this->addForeignKey('user_learning_modules', 'users', 'user_id', 'id');
		$this->addForeignKey('user_learning_modules', 'learning_modules', 'learning_module_id', 'id');
	}
	private function createUserCustomReviews()
	{
		Capsule::schema()->create('user_custom_reviews', function($table) {
			$table->increments('id');
			$table->integer('user_id')->unsigned();
			$table->integer('custom_review_id')->unsigned();

			$table->timestamps();

			$table->index('user_id');
			$table->index('custom_review_id');
		});
		$this->addForeignKey('user_custom_reviews', 'users', 'user_id', 'id');
		$this->addForeignKey('user_custom_reviews', 'custom_reviews', 'custom_review_id', 'id');
	}
	private function createDepartmentCustomReviews() {
		Capsule::schema()->create('department_custom_reviews', function($table) {
			$table->increments('id');
			$table->integer('department_id')->unsigned();
			$table->integer('custom_review_id')->unsigned();

			// Used for lessons, when department is assigned a lesson, date can be specified when lesson should be finished.
			$table->dateTime('due_at')->nullable()->default(null);
			$table->dateTime('start_at')->nullable()->default(null);

			$table->timestamps();

			$table->index('department_id');
			$table->index('custom_review_id');
		});
		$this->addForeignKey('department_custom_reviews', 'departments', 'department_id', 'id');
		$this->addForeignKey('department_custom_reviews', 'custom_reviews', 'custom_review_id', 'id');
	}
	private function createGroupCustomReviews()
	{
		Capsule::schema()->create('group_custom_reviews', function($table) {
			$table->increments('id');
			$table->integer('group_id')->unsigned();
			$table->integer('custom_review_id')->unsigned();
			$table->index('group_id');
			$table->index('custom_review_id');
			$table->timestamps();
		});
		$this->addForeignKey('group_custom_reviews', 'groups', 'group_id', 'id');
		$this->addForeignKey('group_custom_reviews', 'custom_reviews', 'custom_review_id', 'id');
	}
	private function createDesignationCustomReviews()
	{
		Capsule::schema()->create('designation_custom_reviews', function($table) {
			$table->increments('id');
			$table->integer('designation_id')->unsigned();
			$table->integer('custom_review_id')->unsigned();
			$table->index('designation_id');
			$table->index('custom_review_id');
			$table->timestamps();
		});
		$this->addForeignKey('designation_custom_reviews', 'designations', 'designation_id', 'id');
		$this->addForeignKey('designation_custom_reviews', 'custom_reviews', 'custom_review_id', 'id');
	}

	private function createLearningResults()
	{
		Capsule::schema()->create('learning_results', function($table) {
			$table->increments('id');
			$table->integer('user_id')->unsigned();
			$table->integer('learning_module_id')->unsigned();

			$table->enum('completion_status', ['not attempted', 'in progress', 'completed'])->default('not attempted');
			$table->enum('passing_status', ['not attempted', 'passed', 'failed'])->default('not attempted');
			$table->enum('grade', ['Pass', 'Fail', 'Merit', 'Distinction'])->nullable()->default(null);
			$table->integer('score')->unsigned()->nullable();
			$table->smallInteger('duration_hours')->unsigned()->default(0);
			$table->tinyInteger('duration_minutes')->unsigned()->default(0);
			$table->smallInteger('duration_scorm')->unsigned()->default(0); // Record minutes spent in SCORM player if e-learning!
			$table->boolean('off_the_job_hours')->default(false); //"Do not include the time taken on this learning resource towards total learning hours

			$table->text('log_learned')->nullable();
			$table->text('log_to_learn')->nullable();
			$table->text('log_used')->nullable();

			$table->boolean('approved')->default(true);
			$table->boolean('sign_off_trainee')->default(false);
			$table->date('sign_off_trainee_at')->nullable();
			$table->boolean('sign_off_manager')->default(false);
			$table->date('sign_off_manager_at')->nullable();
			$table->integer('sign_off_manager_by')->unsigned()->nullable();
			$table->text('manager_refused_comment')->nullable();
			$table->datetime('manager_refused_time')->nullable();
			$table->integer('manager_refused_by')->unsigned()->nullable();
			$table->boolean('favorite')->default(false);
			$table->datetime('grace_at')->nullable();
			$table->boolean('refreshed')->default(false);
			$table->datetime('due_at')->nullable(); // When learning is assigned
			$table->dateTime('completion_date_custom')->nullable(); // Used for apprentix, will appear in calendar by this date if this field contains date.
			$table->integer('completion_date_custom_days')->nullable()->default(null);
			$table->datetime('completed_at')->nullable();

			$table->enum('qa', ['Accepted', 'Rejected'])->nullable()->default(null);

			$table->boolean('learner_action')->default(false);
			$table->datetime('learner_action_date')->nullable();
			$table->boolean('manager_action')->default(false);
			$table->datetime('manager_action_date')->nullable();

			$table->integer('created_by')->unsigned()->nullable()->default(null); // Whenever learning result is created, auto fill this with active logged user ID!

			$table->boolean('off_the_job_training')->default(false);

			$table->dateTime('start_date')->nullable()->default(null); // If specified show to learner only if this specified date is in past

			$table->boolean('homework')->default(false); // Learner will be able to specify "Expected completion time"
			$table->integer('deleted_by')->unsigned()->nullable();
			$table->smallInteger('credits')->unsigned()->nullable()->default(null);

			$table->timestamps();

			$table->softDeletes();

			$table->index('user_id');
			$table->index('learning_module_id');
			$table->index('completion_status');
			$table->index('passing_status');
			$table->index('refreshed');
			$table->index('score');
		});
		$this->addForeignKey('learning_results', 'users', 'user_id', 'id');
		$this->addForeignKey('learning_results', 'learning_modules', 'learning_module_id', 'id');
		$this->addForeignKey('learning_results', 'users', 'sign_off_manager_by', 'id');
	}

	private function createLearningResultsComments () {
		Capsule::schema()->create('learning_results_comments', function($table) {
			$table->increments('id');
			$table->integer('comment_by_user_id')->unsigned();
			$table->integer('created_for_user_id')->unsigned();
			$table->integer('learning_module_id')->unsigned();
			$table->integer('learning_results_id')->unsigned();
			$table->text('comment');
			$table->boolean('is_read')->default(false);
			$table->integer('is_read_by_user_id')->unsigned();
			$table->boolean('qa')->default(false);
			$table->boolean('visible_learner')->default(true);

			$table->integer('status')->default(1);
			$table->integer('deleted_by')->unsigned()->nullable();
			$table->softDeletes();
			$table->timestamps();

			$table->index('comment_by_user_id');
			$table->index('learning_module_id');
			$table->index('learning_results_id');
		});
		$this->addForeignKey('learning_results_comments', 'users', 'comment_by_user_id', 'id');
		$this->addForeignKey('learning_results_comments', 'users', 'created_for_user_id', 'id');
		$this->addForeignKey('learning_results_comments', 'learning_modules', 'learning_module_id', 'id');
		$this->addForeignKey('learning_results_comments', 'learning_results', 'learning_results_id', 'id');

	}

	private function createDepartmentLearningModules() {
		Capsule::schema()->create('department_learning_modules', function($table) {
			$table->increments('id');
			$table->integer('department_id')->unsigned();
			$table->integer('learning_module_id')->unsigned();

			// Used for lessons, when department is assigned a lesson, date can be specified when lesson should be finished.
			$table->dateTime('due_at')->nullable()->default(null);
			$table->dateTime('start_at')->nullable()->default(null);

			$table->timestamps();

			$table->index('department_id');
			$table->index('learning_module_id');
		});
		$this->addForeignKey('department_learning_modules', 'departments', 'department_id', 'id');
		$this->addForeignKey('department_learning_modules', 'learning_modules', 'learning_module_id', 'id');
	}


	private function createDepartmentStandards() {
		Capsule::schema()->create('department_standards', function($table) {
			$table->increments('id');
			$table->integer('department_id')->unsigned();
			$table->integer('standard_id')->unsigned();

			// Used for lessons, when department is assigned a lesson, date can be specified when lesson should be started/finished.
			$table->dateTime('start_at')->nullable()->default(null);
			$table->dateTime('due_at')->nullable()->default(null);

			$table->timestamps();

			$table->index('department_id');
			$table->index('standard_id');
		});
		$this->addForeignKey('department_standards', 'departments', 'department_id', 'id');
		$this->addForeignKey('department_standards', 'apprenticeship_standards', 'standard_id', 'id');
	}


	private function createGroupLearningModules()
	{
		Capsule::schema()->create('group_learning_modules', function($table) {
			$table->increments('id');
			$table->integer('group_id')->unsigned();
			$table->integer('learning_module_id')->unsigned();
			$table->index('group_id');
			$table->index('learning_module_id');
		});
		$this->addForeignKey('group_learning_modules', 'groups', 'group_id', 'id');
		$this->addForeignKey('group_learning_modules', 'learning_modules', 'learning_module_id', 'id');
	}

	private function createGroupStandards() {
		Capsule::schema()->create('group_standards', function($table) {
			$table->increments('id');
			$table->integer('group_id')->unsigned();
			$table->integer('standard_id')->unsigned();

			$table->dateTime('start_at')->nullable()->default(null);
			$table->dateTime('due_at')->nullable()->default(null);

			$table->timestamps();

			$table->index('group_id');
			$table->index('standard_id');
		});
		$this->addForeignKey('group_standards', 'groups', 'group_id', 'id');
		$this->addForeignKey('group_standards', 'apprenticeship_standards', 'standard_id', 'id');
	}


	private function createEmailTemplates() {
		Capsule::schema()->create('email_templates', function($table) {
			$table->increments('id');
			$table->string('slug'); // String, not exposed to front end as name also now is dynamic...
			$table->string('name');
			$table->string('subject');
			$table->text('body');
			$table->boolean('is_temporary')->default(false);
			$table->boolean('copy_email_to_managers')->default(false);
			$table->integer('batch_report_id')->unsigned()->nullable()->default(null); // Holds link to batch report, will pull data from batch report when sending emails
			$table->string('site_versions'); // List of site versions that are allowed to see template, if it is empty, show to all. Ex:'"openelms" "openelmstms" "omniprez" "smcrsolution" "apprentix" "openelmsschools" "openelmscolleges" "openelmsuniversities" "openelmsbusiness", "nras"'
			$table->boolean('status')->default(true);
			$table->timestamps();
			$table->index('name');
		});
	}

	private function createEmailQueue()
	{
		Capsule::schema()->create('email_queue', function($table) {
			$table->increments('id');
			$table->integer('email_template_id')->unsigned();
			$table->text('recipients');
			$table->integer('from')->unsigned()->nullable();
			$table->integer('frequency')->unsigned()->nullable();
			$table->text('frequency_pattern')->nullable()->default(null);
			$table->dateTime('send_date')->nullable();
			$table->integer('times_sent')->unsigned()->default(0);
			$table->text('get_users_parameters')->nullable();
			$table->text('get_users_modified_parameters')->nullable();
			$table->text('get_users_arguments')->nullable();
			$table->text('get_users_url')->nullable();
			$table->text('comment')->nullable(); // stores comment that needs to be sent out, comments are from learning resources, uploads/blogs.
			$table->text('custom_data')->nullable()->default(null); // Store something in JSON that can be used when e-mails are sent out.
			$table->string('get_users_method', 10)->nullable()->default('');
			$table->string('user_id_key', 255)->nullable()->default('');
			$table->integer('learning_module_id')->unsigned()->nullable()->default(null);
			$table->text('custom_variables')->nullable()->default(null);
			$table->boolean('processed')->default(false);
			$table->timestamps();
		});
		$this->addForeignKey('email_queue', 'email_templates', 'email_template_id', 'id');
		$this->addForeignKey('email_queue', 'users', 'from', 'id');
	}

	/*
		'',
		'',
		'assessment_tasks',
	 */
	private function createAssessmentAnswers()
	{
		Capsule::schema()->create('assessment_answers', function($table) {
			$table->increments('id');
			$table->integer('course_id')->unsigned();
			$table->integer('answer_id')->unsigned();
			$table->string('question_id', 50);
			$table->text('text')->nullable();
			$table->tinyInteger('correct')->unsigned();
			$table->text('feedback')->nullable();
			$table->integer('score')->unsigned()->nullable();
			$table->integer('order')->unsigned()->nullable();
			$table->text('task_name')->nullable();
			$table->text('task_description')->nullable();
			$table->integer('task_severity')->nullable();
			$table->integer('actionByID')->nullable();
			$table->timestamps();
			$table->unique(['course_id', 'question_id', 'correct', 'answer_id'], 'answer_index_dfsk3h');
			$table->index(['course_id', 'question_id']);
		});
		$this->addForeignKey('assessment_answers', 'learning_modules', 'course_id', 'id');
	}

	private function createAssessmentCategories()
	{
		Capsule::schema()->create('assessment_categories', function($table) {
			$table->increments('id');
			$table->integer('course_id')->unsigned();
			$table->string('title', 20);
			$table->timestamps();
		});
		$this->addForeignKey('assessment_categories', 'learning_modules', 'course_id', 'id');
	}

	private function createAssessmentCategoryQuestions()
	{
		Capsule::schema()->create('assessment_category_questions', function($table) {
			$table->increments('id');
			$table->string('question_id', 50);
			$table->integer('category_id')->unsigned();
			$table->timestamps();
		});
		$this->addForeignKey('assessment_category_questions', 'assessment_categories', 'category_id', 'id');
	}

	private function createAssessmentData()
	{
		Capsule::schema()->create('assessment_data', function($table) {
			$table->increments('id');
			$table->integer('user_id')->unsigned();
			$table->integer('course_id')->unsigned();
			$table->integer('status');
			$table->dateTime('started_at');
			$table->dateTime('submitted_at')->nullable();
			$table->dateTime('target_due_at')->nullable();
			$table->dateTime('closed_at')->nullable();
			$table->index(['user_id']);
			$table->index(['course_id']);
			$table->index(['status']);
			$table->index(['user_id', 'course_id']);
			$table->index(['user_id', 'course_id', 'status'], 'asm_data_index_dffdsgdsf23');
		});
		$this->addForeignKey('assessment_data', 'users', 'user_id', 'id');
		$this->addForeignKey('assessment_data', 'learning_modules', 'course_id', 'id');
	}

	private function createAssessmentQuestions()
	{
		Capsule::schema()->create('assessment_questions', function($table) {
			$table->increments('id');
			$table->integer('course_id')->unsigned();
			$table->string('question_id', 50);
			$table->string('question_type', 50)->nullable();
			$table->text('title')->nullable();
			$table->text('text')->nullable();
			$table->integer('image')->nullable();
			$table->integer('flash')->nullable();
			$table->tinyInteger('comment_box')->default(0);
			$table->integer('group_id')->nullable();
			$table->timestamps();

			$table->unique(['course_id', 'question_id']);
			$table->index('course_id');
			$table->index('question_id');
		});
		$this->addForeignKey('assessment_questions', 'learning_modules', 'course_id', 'id');
	}

	private function createAssessmentTaskComments()
	{
		Capsule::schema()->create('assessment_task_comments', function($table) {
			$table->increments('id');
			$table->integer('assessment_task_id')->unsigned();
			$table->integer('user_id')->unsigned();
			$table->text('comment')->nullable();
			$table->timestamps();

			$table->index('assessment_task_id');
			$table->index('user_id');
			$table->index(['assessment_task_id', 'user_id']);
		});
		$this->addForeignKey('assessment_task_comments', 'assessment_tasks', 'assessment_task_id', 'id');
	}

	private function createAssessmentTasks()
	{
		Capsule::schema()->create('assessment_tasks', function($table) {
			$table->increments('id');
			$table->integer('assessment_data_id')->unsigned();
			$table->integer('user_id')->unsigned();
			$table->integer('course_id')->unsigned();
			$table->integer('reporter_id')->unsigned();
			$table->string('question_id', 50);
			$table->integer('answer_id')->unsigned();
			$table->text('user_comment')->nullable();
			$table->integer('weighting')->unsigned();
			$table->integer('status')->unsigned();
			$table->dateTime('submitted_at')->nullable();
			$table->timestamps();

			$table->index('assessment_data_id');
			$table->index('user_id');
			$table->index('course_id');
			$table->index('question_id');
			$table->index('answer_id');
			$table->index(['user_id', 'course_id']);
		});
		$this->addForeignKey('assessment_tasks', 'assessment_data', 'assessment_data_id', 'id');
		$this->addForeignKey('assessment_tasks', 'users', 'user_id', 'id');
		$this->addForeignKey('assessment_tasks', 'learning_modules', 'course_id', 'id');
		$this->addForeignKey('assessment_tasks', 'users', 'reporter_id', 'id');
	}

	private function createLabels()
	{
		Capsule::schema()->create('labels', function($table) {
			$table->increments('id');
			$table->string('from_text');
			$table->string('to_text');

			$table->boolean('status')->default(true);

			$table->timestamps();

			$table->index('from_text');
		});
	}

	private function createUserCompetencies()
	{
		Capsule::schema()->create('user_competencies', function($table) {
			$table->increments('id');
			$table->integer('user_id')->unsigned();
			$table->integer('competency_id')->unsigned();
			$table->integer('points')->unsigned()->default(0);
			$table->dateTime('acquired_at')->nullable()->default(null);
			$table->string('badge', 256)->nullable();
			$table->timestamps();

			$table->index('user_id');
			$table->index('competency_id');
			$table->index(['user_id', 'competency_id']);
		});
		$this->addForeignKey('user_competencies', 'users', 'user_id', 'id');
		$this->addForeignKey('user_competencies', 'competencies', 'competency_id', 'id');
	}

	private function createErrorLog()
	{
		Capsule::schema()->create('error_log', function($table) {
			$table->increments('id');
			$table->integer('user_id')->unsigned()->nullable();
			$table->string('uri');
			$table->text('request');
			$table->text('log');
			$table->timestamps();

			$table->index('user_id');
			$table->index('uri');
		});
	}

	private function createAvailableModules()
	{
		Capsule::schema()->create('available_modules', function($table) {
			$table->increments('id');
			$table->string('name');
			$table->timestamps();

			$table->index('name');
			$table->unique('name');
		});
	}

	private function createApprenticeshipStandards() {
		Capsule::schema()->create('apprenticeship_standards', function($table) {
			$table->increments('id');
			$table->string('route');
			$table->string('name');
			$table->integer('completion_months')->unsigned()->nullable();
			$table->boolean('periodic_repeat')->default(false); // Periodically repeat entire programme
			$table->integer('periodic_repeat_months')->unsigned()->nullable();
			$table->integer('working_hours')->unsigned()->nullable();
			$table->integer('category_id')->unsigned()->nullable();
			$table->integer('level')->unsigned()->nullable();
			$table->integer('funding')->unsigned()->nullable();
			$table->string('reference_code');
			$table->boolean('periodic_review')->default(false);
			$table->smallInteger('review_interval')->unsigned()->nullable()->default(null); // In months
			$table->boolean('ilr_learning_delivery')->default(false);
			$table->string('learning_delivery_type');
			$table->string('delivery');
			$table->boolean('status');
			$table->integer('sort')->unsigned()->nullable();
			$table->enum('type', ['Standards', 'Frameworks', 'Qualifications'])->default('Standards');
			$table->boolean('course_credits')->default(false);
			$table->timestamps();

			$table->index('name');
		});
	}

	private function createApprenticeshipStandardsUsers() {
		Capsule::schema()->create('apprenticeship_standards_users', function($table) {
			$table->increments('id');
			$table->integer('standard_id')->unsigned()->nullable();
			$table->integer('user_id')->unsigned()->nullable();
			$table->tinyInteger('status')->default(1);
			$table->enum('completion_status', ['not attempted', 'in progress', 'completed'])->default('not attempted');
			$table->decimal('percentage', 5, 2)->default(0);
			$table->decimal('percentage_time', 5, 2)->default(0);
			$table->decimal('percentage_behind', 5, 2)->default(0);
			$table->decimal('time_spent', 7, 2)->default(0);
			$table->decimal('time_spent_off_the_job_training', 7, 2)->default(0);
			$table->decimal('time_behind', 7, 2)->default(0);
			$table->decimal('on_schedule', 6, 2)->default(0);
			$table->decimal('criteria_completion', 6, 2)->default(0);
			$table->dateTime('start_at')->nullable();
			$table->date('ilr_start_at')->nullable();
			$table->date('ilr_end_at')->nullable();
			$table->integer('ilr_completion_months')->nullable();
			$table->dateTime('due_at')->nullable();
			$table->dateTime('completed_at')->nullable();
			$table->date('end_at_calculated')->nullable(); // Date that will get re-calculated each night untill completed. Will use ILR date if present, or NOW() if less than due_at or due_at if NOW is bigger.
			$table->date('last_update')->nullable(); // Each time resource in this standard is updated, this date is updated also.
			$table->integer('actual_cost')->nullable(); // Sum of learning delivery "Apprenticeship Financial Records", recalculated on each "syncIlrData" in "User.php"
			$table->integer('working_hours')->unsigned()->nullable(); // This is final calculation using multiple factors. Either Learning delivery hours, custom user week hours or logic to calculate programe week hours.
			$table->boolean('working_hours_custom')->default(false);
			$table->boolean('working_hours_ilr')->default(false); // This will indicate that working hours are coming from ILR link, therefore not to be recalculated otherwise.
			$table->boolean('ilr_link')->default(false);
			$table->string('optional_outcome')->nullable()->default(null);

			$table->integer('created_by')->unsigned()->nullable()->default(null);
			$table->integer('deleted_by')->unsigned()->nullable()->default(null);
			$table->softDeletes();

			$table->boolean('paused')->default(false);
			$table->date('paused_start')->nullable()->default(null);
			$table->date('paused_end')->nullable()->default(null);

			$table->timestamps();

			$table->index('start_at');
			$table->index('completion_status');

		});
		$this->addForeignKey('apprenticeship_standards_users', 'apprenticeship_standards', 'standard_id', 'id');
		$this->addForeignKey('apprenticeship_standards_users', 'users', 'user_id', 'id');
	}

	private function createApprenticeshipIssueCategories() {
		Capsule::schema()->create('apprenticeship_issue_categories', function($table) {
			$table->increments('id');
			$table->string('name', 800);
			$table->integer('standard_id')->unsigned()->nullable();
			$table->boolean('status');
			$table->integer('sort')->unsigned()->nullable();
			$table->boolean('exclude_outcome')->default(false);
			$table->smallInteger('minimum_required_credits')->unsigned()->nullable()->default(null);
			$table->timestamps();

			$table->index('name');
		});
		$this->addForeignKey('apprenticeship_issue_categories', 'apprenticeship_standards', 'standard_id', 'id');
	}

	private function createApprenticeshipIssueCategoriesUsers() {
		Capsule::schema()->create('apprenticeship_issue_categories_users', function($table) {
			$table->increments('id');
			$table->integer('issue_category_id')->unsigned()->nullable();
			$table->integer('user_id')->unsigned()->nullable();
			$table->decimal('percentage', 5, 2)->default(0);
			$table->decimal('percentage_issues', 5, 2)->default(0);
			$table->boolean('status');
			$table->timestamps();

		});
	}


	private function createApprenticeshipIssues() {
		Capsule::schema()->create('apprenticeship_issues', function($table) {
			$table->increments('id');
			$table->string('name');
			$table->integer('issue_category_id')->unsigned()->nullable();
			$table->boolean('status');
			$table->boolean('hide_learner');
			$table->boolean('visible_resource')->default(1);
			$table->integer('sort')->unsigned()->nullable();
			$table->integer('end_day')->unsigned()->nullable();
			$table->integer('start_day')->unsigned()->nullable();
			$table->integer('parent_id')->unsigned()->default(0); // For use in case issues have sub-issues
			$table->string('guidelines', 2000); // for subcriteria
			$table->timestamps();
			$table->index('name');
		});
		$this->addForeignKey('apprenticeship_issues', 'apprenticeship_issue_categories', 'issue_category_id', 'id');
	}

	private function createApprenticeshipIssuesUserDisabled() {
		Capsule::schema()->create('apprenticeship_issues_user_disabled', function($table) {
			$table->increments('id');
			$table->integer('apprenticeship_issues_id')->unsigned()->nullable();
			$table->integer('user_id')->unsigned()->nullable();
			$table->timestamps();
		});
		$this->addForeignKey('apprenticeship_issues_user_disabled', 'apprenticeship_issues', 'apprenticeship_issues_id', 'id', 'key_issue_disabled');
		$this->addForeignKey('apprenticeship_issues_user_disabled', 'users', 'user_id', 'id', 'key_issue_disabled_user');
	}

	private function createApprenticeshipIssuesLearningModules() {
		Capsule::schema()->create('apprenticeship_issues_learning_modules', function($table) {
			$table->increments('id');
			$table->integer('apprenticeship_issues_id')->unsigned()->nullable();
			$table->integer('learning_modules_id')->unsigned()->nullable();
			$table->integer('sort')->unsigned()->nullable();
			$table->boolean('custom_work_window')->default(0);
			$table->integer('start_day')->unsigned()->nullable();
			$table->integer('end_day')->unsigned()->nullable();
			$table->timestamps();
		});
		$this->addForeignKey('apprenticeship_issues_learning_modules', 'apprenticeship_issues', 'apprenticeship_issues_id', 'id', 'issues_modules');
		$this->addForeignKey('apprenticeship_issues_learning_modules', 'learning_modules', 'learning_modules_id', 'id', 'issues_issues');
	}

	// This will link issue with department and module, so anyone assigned to department and standard will be assigned module on certain issue also.
	private function createApprenticeshipIssuesDepartments() {
		Capsule::schema()->create('apprenticeship_issues_departments', function($table) {
			$table->increments('id');
			$table->integer('apprenticeship_issue_id')->unsigned()->nullable();
			$table->integer('department_id')->unsigned()->nullable();
			$table->integer('learning_module_id')->unsigned()->nullable();
			$table->integer('sort')->unsigned()->nullable();
			$table->boolean('custom_work_window')->default(0);
			$table->integer('start_day')->unsigned()->nullable();
			$table->integer('end_day')->unsigned()->nullable();
			$table->dateTime('start_date')->nullable()->default(null);
			$table->dateTime('expected_completion_date')->nullable()->default(null);
			$table->integer('lesson_duration')->unsigned()->nullable()->default(null); // This will hold minutes, less hassle than using time field
			$table->boolean('signoff')->default(false);
			$table->timestamps();
		});
		$this->addForeignKey('apprenticeship_issues_departments', 'apprenticeship_issues', 'apprenticeship_issue_id', 'id', 'department_issue');
		$this->addForeignKey('apprenticeship_issues_departments', 'departments', 'department_id', 'id');
		$this->addForeignKey('apprenticeship_issues_departments', 'learning_modules', 'learning_module_id', 'id');
	}


	private function createApprenticeshipIssuesDepartmentSchedules() {
		Capsule::schema()->create('apprenticeship_issues_department_schedules', function($table) {
			$table->increments('id');
			$table->integer('apprenticeship_issues_department_id')->unsigned()->nullable();
			$table->dateTime('start_date')->nullable()->default(null);
			$table->integer('lesson_duration')->unsigned()->nullable()->default(null);
			$table->timestamps();
		});
		$this->addForeignKey('apprenticeship_issues_department_schedules', 'apprenticeship_issues_departments', 'apprenticeship_issues_department_id', 'id', 'department_issue_schedule');
	}


	private function createApprenticeshipIssuesGroups() {
		Capsule::schema()->create('apprenticeship_issues_groups', function($table) {
			$table->increments('id');
			$table->integer('apprenticeship_issue_id')->unsigned()->nullable();
			$table->integer('group_id')->unsigned()->nullable();
			$table->integer('learning_module_id')->unsigned()->nullable();
			$table->integer('sort')->unsigned()->nullable();
			$table->boolean('custom_work_window')->default(0);
			$table->integer('start_day')->unsigned()->nullable();
			$table->integer('end_day')->unsigned()->nullable();
			$table->dateTime('start_date')->nullable()->default(null);
			$table->dateTime('expected_completion_date')->nullable()->default(null);
			$table->integer('lesson_duration')->unsigned()->nullable()->default(null);
			$table->boolean('signoff')->default(false);
			$table->timestamps();
		});
		$this->addForeignKey('apprenticeship_issues_groups', 'apprenticeship_issues', 'apprenticeship_issue_id', 'id', 'group_issue');
		$this->addForeignKey('apprenticeship_issues_groups', 'groups', 'group_id', 'id');
		$this->addForeignKey('apprenticeship_issues_groups', 'learning_modules', 'learning_module_id', 'id');
	}

	private function createApprenticeshipIssuesUserLearningModules() {
		Capsule::schema()->create('apprenticeship_issues_user_learning_modules', function($table) {
			$table->increments('id');
			$table->integer('apprenticeship_issues_id')->unsigned()->nullable();
			$table->integer('learning_modules_id')->unsigned()->nullable();
			$table->integer('user_id')->unsigned()->nullable();
			$table->integer('sort')->unsigned()->nullable();
			$table->timestamps();
		});
		$this->addForeignKey('apprenticeship_issues_user_learning_modules', 'apprenticeship_issues', 'apprenticeship_issues_id', 'id', 'key_modules');
		$this->addForeignKey('apprenticeship_issues_user_learning_modules', 'learning_modules', 'learning_modules_id', 'id', 'key_issues');
		$this->addForeignKey('apprenticeship_issues_user_learning_modules', 'users', 'user_id', 'id', 'key_user');
	}


	private function createLearningModuleEvidences() {
		Capsule::schema()->create('learning_module_evidences', function($table) {
			$table->increments('id');
			$table->integer('learning_modules_id')->unsigned()->nullable();
			$table->integer('user_id')->unsigned()->nullable();
			$table->integer('added_by')->unsigned()->nullable(); // In case manager/admin is adding file in user's learning result, so display will show who added correctly.
			$table->boolean('manager')->default(0);
			$table->string('evidence', 2000);
			$table->string('hash');
			$table->string('extension');
			$table->enum('evidence_type', ['file', 'video', 'image', 'comment'])->default('file');
			$table->boolean('status');
			$table->timestamps();

			$table->unique('hash');
#			$table->index('evidence');
		});
		$this->addForeignKey('learning_module_evidences', 'learning_modules', 'learning_modules_id', 'id');
		$this->addForeignKey('learning_module_evidences', 'users', 'user_id', 'id');
	}

	private function createLearningModuleEvidenceMeetings() {
		Capsule::schema()->create('learning_module_evidence_meetings', function($table) {
			$table->increments('id');
			$table->integer('learning_modules_id')->unsigned()->nullable();
			$table->integer('user_id')->unsigned()->nullable();
			$table->string('name', 300);
			$table->string('description', 2000);
			$table->dateTime('preferred_time')->nullable();
			$table->boolean('manager_accepted');
			$table->boolean('trainee_accepted');
			$table->boolean('status');
			$table->integer('created_by')->unsigned()->nullable();
			$table->integer('approved_by')->unsigned()->nullable();
			$table->timestamps();

#			$table->index('name');
		});
		$this->addForeignKey('learning_module_evidence_meetings', 'learning_modules', 'learning_modules_id', 'id');
		$this->addForeignKey('learning_module_evidence_meetings', 'users', 'user_id', 'id');
		$this->addForeignKey('learning_module_evidence_meetings', 'users', 'created_by', 'id');
		$this->addForeignKey('learning_module_evidence_meetings', 'users', 'approved_by', 'id');
	}

	// Links individual issues to trainee created evidence module
	private function createApprenticeshipIssuesEvidence() {
		Capsule::schema()->create('apprenticeship_issues_evidence', function($table) {
			$table->increments('id');
			$table->integer('learning_modules_id')->unsigned()->nullable();
			$table->integer('apprenticeship_issues_id')->unsigned()->nullable();
			$table->integer('user_id')->unsigned()->nullable();
			$table->timestamps();
		});
		$this->addForeignKey('apprenticeship_issues_evidence', 'learning_modules', 'learning_modules_id', 'id');
		$this->addForeignKey('apprenticeship_issues_evidence', 'apprenticeship_issues', 'apprenticeship_issues_id', 'id');
	}

	// Create predefined standard routes
	private function createApprenticeshipRoutes() {
		Capsule::schema()->create('apprenticeship_routes', function($table) {
			$table->increments('id');
			$table->string('name', 100);
			$table->boolean('status')->default(false);
			$table->timestamps();

			$table->index('name');

		});
	}

	// Create predefined standards
	private function createApprenticeshipRoutesStandards() {
		Capsule::schema()->create('apprenticeship_routes_standards', function($table) {
			$table->increments('id');
			$table->string('name', 255)->default('');
			$table->string('reference', 255)->default('');
			$table->integer('route_id')->unsigned();
			$table->tinyInteger('level')->unsigned()->default(0);
			$table->date('published')->default(null);
			$table->integer('lars_code')->unsigned()->default(0);
			$table->integer('funding_band')->unsigned()->default(0);
			$table->integer('funding_band_maximum')->unsigned()->default(0);
			$table->string('link', 255)->default('');
			$table->boolean('status')->default(false);
			$table->timestamps();

			$table->index('name');

		});
		$this->addForeignKey('apprenticeship_routes_standards', 'apprenticeship_routes', 'route_id', 'id');
	}


	private function createStaticScormTables()
	{
		$scorm_tables_file = "static_scorm_tables.sql";
		Capsule::connection()->unprepared(file_get_contents($scorm_tables_file));
	}

	private function createForgottenPasswordTokens()
	{
		Capsule::schema()->create('forgotten_password_tokens', function($table) {
			$table->increments('id');
			$table->integer('user_id')->unsigned();
			$table->string('token', 100);
			$table->boolean('status')->default(true);
			$table->timestamps();

			$table->index('token');
		});
		$this->addForeignKey('forgotten_password_tokens', 'users', 'user_id', 'id');
	}

	private function createConfiguration() {
		Capsule::schema()->create('configuration', function($table) {
			$table->increments('id');
			$table->string('name', 100);
			$table->string('key', 100);
			$table->string('value', 2000);
			$table->string('previousValue', 2000)->nullable();
			$table->string('description', 2000)->nullable();
			$table->enum('type', ['integer', 'string', 'boolean', 'text', 'list', 'select-list'])->default('integer');
			$table->json('select_values')->nullable();
			$table->integer('created_by')->unsigned()->nullable();
			$table->integer('updated_by')->unsigned()->nullable();
			$table->boolean('status')->default(1);
			$table->boolean('secure')->default(false);
			$table->timestamps();

			$table->index('name');
			$table->index('key');
		});
	}

	private function createManagerReviews() {
		Capsule::schema()->create('manager_reviews', function($table) {
			$table->increments('id');
			$table->enum('visit_type', ['Initial Assessment', 'Development', 'Training', 'General Admin', 'Progress Review', 'QA Report'])->nullable()->default(null);
			$table->integer('user_id')->unsigned()->nullable();
			$table->integer('visitor_id')->unsigned()->nullable(); // manager can be deleted, not foreign therefore, or should be?
			$table->integer('created_by')->unsigned()->nullable();
			$table->integer('updated_by')->unsigned()->nullable();
			$table->date('date');
			$table->time("time_start")->nullable();
			$table->time("time_end")->nullable();
			$table->integer('travel_time')->unsigned()->nullable();
			$table->decimal('expenses', 10, 2)->default(0);
			$table->string('report_file', 255);
			$table->integer('standard_id')->unsigned()->nullable();
			$table->boolean('checked_by_coach_trainer')->default(0);
			$table->integer('checked_by')->unsigned()->nullable();
			$table->enum('completion_status', ['Not Started', 'In Progress', 'Completed'])->nullable()->default('Not Started');
			$table->boolean('off_the_job_training')->default(false);
			$table->boolean('status')->default(1);
			$table->timestamps();
		});

		$this->addForeignKey('manager_reviews', 'users', 'user_id', 'id');
	}

	private function createManagerReviewsComments() {
		Capsule::schema()->create('manager_reviews_comments', function($table) {
			$table->increments('id');
			$table->integer('review_id')->unsigned();
			$table->text('comment')->nullable();
			$table->integer('commented_by')->unsigned()->nullable();
			$table->boolean('status')->default(1);
			$table->timestamps();
		});
		$this->addForeignKey('manager_reviews_comments', 'manager_reviews', 'review_id', 'id');
	}


	// Each upgrade.php will insert record into this table, id will be used as version number, will be used eventually for cashing, proof of concept as for now
	private function createVersion() {
		Capsule::schema()->create('version', function($table) {
			$table->increments('id');
			$table->timestamps();
		});
	}

	private function createIlrTemplates() {
		Capsule::schema()->create('ilr_templates', function($table) {
			$table->increments('id');
			$table->string('name', 100);
			$table->longText('template')->nullable();
			$table->integer('created_by')->unsigned();
			$table->timestamps();

			$table->index('name');
		});

		$this->addForeignKey('ilr_templates', 'users', 'created_by', 'id');
	}


	// Custom reviews in reviews section where fields and export fields can be slected, plenty of work left.
	private function createCustomReviews() {
		Capsule::schema()->create('custom_reviews', function($table) {
			$table->increments('id');
			$table->string('name');
			$table->enum('group', ['programmes', 'resources'])->nullable()->default(null);
			$table->string('key', 100);
			$table->longText('filter_list')->nullable();
			$table->longText('display_list')->nullable();
			$table->boolean('coach_trainers');
			$table->longText('table_state')->comment = "Store table state that will be loaded up when custom-review is opened again.";
			$table->string('option_group', 100)->default('Custom'); // Option group!
			$table->boolean('status');
			$table->integer('created_by')->unsigned(); // Don't want to foreign user that might be deleted or created automatically by system, there must be better way.

			// For file download functionality
			$table->string('slug');
			$table->text('export_fields');
			$table->text('export_fields_types');
			$table->text('relationships');
			$table->string('download_file_name');
			// EOF For file download functionality

			$table->timestamps();

			$table->index('name');
		});
	}

	// Filter list table for custom reviews,
	private function createCustomReviewsFilters() {
		Capsule::schema()->create('custom_reviews_filters', function($table) {
			$table->increments('id');
			$table->string('name');
			$table->string('description', 500);
			$table->enum('group', ['programmes', 'resources'])->nullable()->default(null); // In case some fields are exclusive to specific group, else null and show to both
			$table->string('key', 100); // Used to display entry property recieved from server in cell entry[key]
			$table->string('sort_key', 100);
			$table->string('type', 255); // date or anything else to convert output into specific format in cell.
			$table->boolean('decode')->default(0); //  If data is encoded json, this will tell output to decode it.
			$table->string('search', 100); // table's display input field model
			$table->string('search_options', 300); // if this is specified, render selectbox, contains "val1,val2,etc"
			$table->text('complex_options'); // array or object that will be encoded as string and decoded to be served in view.
			$table->text('left_join_extended'); // Will contain multiple left joins, the new left join!
			$table->string('left_join', 1000); // table,table.what,=,othertable,othertable.what
			$table->string('left_join_on', 1000); // contains array of possibly multiple strings, each of them can be one ON inside left join.
			$table->string('inner_join', 1000); //
			$table->text('join');
			$table->string('select', 1000); // table.something as something,etc.uh as uh
			$table->text('select_raw');
			$table->string('group_by', 1000);
			$table->string('filter_model', 100); // filter module that is being sent in post request, for database query
			$table->text('filter_mysql'); // Operations in customreviewlist to filter out entries
			$table->string('filter_options', 100); // global object that contains entries for select box.
			$table->string('filter_options_id', 100)->nullable()->default(null); // What field from selected filter_options to send in request
			$table->string('filter_options_sort', 100); // Default sort order for selectboxes
			$table->string('with', 100); // not sure if used.
			$table->string('where', 1000); // custom where queries json_encode "['learning_modules.status', '=', true],[etc]"
			$table->string('where_or', 1000); // Where query with multiple OR's inside
			$table->string('where_in', 1000); // Multiple where_ins
			$table->string('option_group', 100); // Option group!
			$table->integer('option_group_order')->unsigned()->default(0); // Will be used as default sort group
			$table->string('convert_to', 100); // When query is processed, before feeding fields/values to smart table, convert value to specific type, looks like excess workaround

			$table->integer('order')->unsigned()->nullable();
			$table->boolean('sort')->default(true);
			$table->boolean('status')->default(1);
			$table->integer('created_by')->unsigned()->nullable()->default(null);
			$table->boolean('is_apprentix')->default(0); // Will show these filters only if apprentix is enabled!
			$table->boolean('is_smcr')->default(0); // Will show these filters only if SMCR is enabled!
			$table->boolean('date_selector')->default(false);
			$table->boolean('range_selector')->default(false);
			$table->boolean('hide_print')->default(false); // Hide label in printing and downloading
			$table->boolean('disable_review')->default(false);
			$table->timestamps();

			$table->index('name');
		});
	}

	// Ethnicities
	private function createEthnicities () {
		Capsule::schema()->create('ethnicities', function($table) {
			$table->increments('id');
			$table->string('name');
			$table->boolean('status')->default(1);
			$table->timestamps();
			$table->index('name');
		});
	}

	// Sexes
	private function createSexes () {
		Capsule::schema()->create('sexes', function($table) {
			$table->increments('id');
			$table->string('name');
			$table->string('code');
			$table->boolean('status')->default(1);
			$table->timestamps();
			$table->index('name');
		});
	}

	// LLDD and health problem
	private function createLlddHealthProblems () {
		Capsule::schema()->create('lldd_health_problems', function($table) {
			$table->increments('id');
			$table->string('name');
			$table->boolean('status')->default(1);
			$table->timestamps();
			$table->index('name');
		});
	}

	// LLDD and health problem categories
	private function createLlddHealthProblemsCategories () {
		Capsule::schema()->create('lldd_health_problems_categories', function($table) {
			$table->increments('id');
			$table->string('name');
			$table->boolean('status')->default(1);
			$table->timestamps();
			$table->index('name');
		});
	}

	// Prior Attainment 2122+
	private function createPriorAttainments () {
		Capsule::schema()->create('prior_attainments', function($table) {
			$table->increments('id');
			$table->string('name');
			$table->boolean('status')->default(1);
			$table->timestamps();
			$table->index('name');
		});
	}

	// Prior Attainment < 2122
	private function createPriorAttainmentLegacy () {
		Capsule::schema()->create('prior_attainment_legacy', function($table) {
			$table->increments('id');
			$table->string('name');
			$table->boolean('status')->default(1);
			$table->timestamps();
			$table->index('name');
		});
	}

	// SCORM container table where container hash/string will be created against specific module
	private function createLearningModuleContainers () {
		Capsule::schema()->create('learning_module_containers', function($table) {
			$table->increments('id');
			$table->string('name');
			$table->integer('learning_module_id')->unsigned();
			$table->string('token');
			$table->integer('access_count')->unsigned()->default(0);
			$table->boolean('status')->default(1);
			$table->integer('created_by')->unsigned()->nullable();

			$table->timestamps();

			$table->index('name');
			$table->index('token');
			$table->unique('token');
		});


		$this->addForeignKey('learning_module_containers', 'learning_modules', 'learning_module_id', 'id');
		$this->addForeignKey('learning_module_containers', 'users', 'created_by', 'id');
	}

	private function createMonthlyIlrExports () {
		Capsule::schema()->create('monthly_ilr_exports', function($table) {
			$table->increments('id');
			$table->date('date');
			$table->text('users');
			$table->text('programmes');
			$table->integer('user_count')->unsigned();
			$table->timestamps();
		});
	}

	private function createYearlyIlrExports () {
		Capsule::schema()->create('yearly_ilr_exports', function($table) {
			$table->increments('id');
			$table->date('funding_start');
			$table->date('funding_end');
			$table->text('users');
			$table->integer('user_count')->unsigned();
			$table->timestamps();
		});
	}

	//free_jackdaw_modules
	private function createFreeJackdawModules () {
		Capsule::schema()->create('free_jackdaw_modules', function($table) {
			$table->increments('id');
			$table->string('name');
			$table->boolean('status')->default(1);
			$table->integer('created_by')->unsigned()->nullable();
			$table->timestamps();

			$table->index('name');
		});
	}

	private function createEmailStatistics () {
		Capsule::schema()->create('email_statistics', function($table) {
			$table->increments('id');
			$table->date('date');
			$table->integer('count')->unsigned()->default(0);
			$table->timestamps();
		});
	}

	// Wont join this to anything as users/modules can be deleted, but I need to keep this statistics.
	private function createEmailRefreshStatistics () {
		Capsule::schema()->create('email_refresh_statistics', function($table) {
			$table->increments('id');
			$table->dateTime('datetime');
			$table->integer('user_id')->unsigned()->nullable();
			$table->integer('learning_module_id')->unsigned()->nullable();
			$table->timestamps();
		});
	}

	private function createSmcrStaffTypes () {
		Capsule::schema()->create('smcr_staff_types', function($table) {
			$table->increments('id');
			$table->string('name');
			$table->smallInteger('refresh_assesment')->unsigned()->nullable()->default(365);
			$table->boolean('status')->default(1);
			$table->timestamps();

			$table->index('name');
		});
	}

	private function createSmcrFunctionsResponsibilities () {
		Capsule::schema()->create('smcr_functions_responsibilities', function($table) {
			$table->increments('id');
			$table->string('name');
			$table->text('description')->nullable();
			$table->integer('smcr_staff_type_id')->unsigned()->nullable();
			$table->boolean('is_mandatory')->default(0);
			$table->enum('type', ['function', 'responsibility'])->default('function');
			$table->boolean('status')->default(1);
			$table->timestamps();

			$table->index('name');
		});

		$this->addForeignKey('smcr_functions_responsibilities', 'smcr_staff_types', 'smcr_staff_type_id', 'id');
	}

	private function createSmcrCommittees () {
		Capsule::schema()->create('smcr_committees', function($table) {
			$table->increments('id');
			$table->string('name');
			$table->text('description')->nullable();
			$table->boolean('live')->default(0);

			$table->smallInteger('total_count')->unsigned();
			$table->smallInteger('filled_count')->unsigned();
			$table->smallInteger('missing_count')->unsigned();
			$table->smallInteger('percentage_filled')->unsigned();
			$table->smallInteger('accepted_count')->unsigned();
			$table->smallInteger('percentage_accepted')->unsigned();

			$table->boolean('status')->default(1);
			$table->timestamps();

			$table->index('name');
		});
	}

	private function createSmcrCommitteeRoles () {
		Capsule::schema()->create('smcr_committee_roles', function($table) {
			$table->increments('id');
			$table->string('name');
			$table->text('description')->nullable();
			$table->integer('smcr_committee_id')->unsigned()->nullable();
			$table->boolean('status')->default(1);
			$table->timestamps();

			$table->index('name');
		});
		$this->addForeignKey('smcr_committee_roles', 'smcr_committees', 'smcr_committee_id', 'id');
	}


	// Personnel are created when roles are created, user_id is empty on creation, assigned is filled when user ID is added, vacated is filled when user_id is removed(rejected/unassigned).
	private function createSmcrCommitteeRolePersonnel () {
		Capsule::schema()->create('smcr_committee_role_personnel', function($table) {
			$table->increments('id');
			$table->integer('smcr_committee_role_id')->unsigned()->nullable();
			$table->integer('user_id')->unsigned()->nullable();
			$table->date('vacated')->nullable();
			$table->date('assigned')->nullable();
			$table->date('reminder_sent')->nullable();
			$table->enum('completion_status', ['Assigned', 'Rejected', 'Accepted'])->default('Assigned');
			$table->boolean('status')->default(1);
			$table->timestamps();

		});
		$this->addForeignKey('smcr_committee_role_personnel', 'smcr_committee_roles', 'smcr_committee_role_id', 'id');
		$this->addForeignKey('smcr_committee_role_personnel', 'users', 'user_id', 'id');
	}

	// SMCR reports, table containing certificates when user learning_status gets 'Completed' state.
	private function createSmcrReports () {
		Capsule::schema()->create('smcr_reports', function($table) {
			$table->increments('id');
			$table->integer('user_id')->unsigned()->nullable();
			$table->integer('certified_by_id')->unsigned()->nullable();
			$table->date('start_at');
			$table->date('expire_at');
			$table->date('sign_off_date')->nullable()->default(null);
			$table->date('self_attested_date')->nullable()->default(null);
			$table->text('approved_authority')->nullabe();
			$table->text('comments')->nullabe();
			$table->enum('completion_status', ['Not Started', 'In Progress', 'Completed', 'Archived'])->default('Not Started');
			$table->integer('type_id')->unsigned()->nullable()->default(null);
			$table->longText('snapshot')->nullable(); // Snapshot for Statement of Reponsibilities, snapshot is json encoded data of assigned functions/responsibilities.
			$table->boolean('status')->default(true);
			$table->timestamps();

		});
		$this->addForeignKey('smcr_reports', 'users', 'user_id', 'id');
		$this->addForeignKey('smcr_reports', 'users', 'certified_by_id', 'id');
		$this->addForeignKey('smcr_reports', 'smcr_report_types', 'type_id', 'id');
	}

	private function createSmcrReportTypes() {
		Capsule::schema()->create('smcr_report_types', function($table) {
			$table->increments('id');
			$table->string('name');
			$table->boolean('status')->default(0);
			$table->timestamps();
			$table->index('name');
		});
	}

	private function createSmcrFPCategories () {
		Capsule::schema()->create('smcr_f_p_categories', function($table) {
			$table->increments('id');
			$table->string('name');
			$table->string('key'); // What will be used to create new default categories, not editable.
			$table->text('description')->nullabe();
			$table->boolean('status')->default(true);
			$table->boolean('status_learner')->default(true); // Hide from learner!
			$table->integer('order')->unsigned();
			$table->timestamps();

			$table->index('name');
		});
	}


	private function createDashboards () {
		Capsule::schema()->create('dashboards', function($table) {
			$table->increments('id');
			$table->string('name');
			$table->integer('role_id')->unsigned()->nullable();
			$table->integer('designation_id')->unsigned()->nullable();
			$table->integer('staff_type_id')->unsigned()->nullable();
			$table->enum('visibility', ['hidden', 'minimized', 'maximized'])->default('hidden');
			$table->boolean('status')->default(1);
			$table->timestamps();

			$table->index('name');
		});

		$this->addForeignKey('dashboards', 'roles', 'role_id', 'id');
		$this->addForeignKey('dashboards', 'designations', 'designation_id', 'id');
		$this->addForeignKey('dashboards', 'smcr_staff_types', 'staff_type_id', 'id');
	}

	private function createStatistics () {
		Capsule::schema()->create('statistics', function($table) {
			$table->increments('id');
			$table->string('name');
			$table->text('data')->nullable();
			$table->boolean('is_online')->default(0);
			$table->timestamps();
			$table->index('name');
		});
	}

	private function createDataviews () {
		Capsule::schema()->create('dataviews', function($table) {
			$table->increments('id');
			$table->string('name');
			$table->enum('name_id', [
				"learning_resources",
				"precentage_completed_by_department",
				"precentage_completed_by_company",
				"precentage_completed_by_job_title",
				"precentage_completed_by_location",
				"annual_performance",
				"training_due",
				"total_annual_training_impact_cpd_points",
				"total_annual_training_impact_competency_passes",
				"apprentice_progress",
				"coach_assessor_progress",
				"coach_assessor_apprentices",
				"monthly_revenue_per_coach",
				"senior_manager_functions",
				"senior_manager_responsibilities",
				"certification_staff_functions",
				"overall_compliance_functions",
				"progress_by_smcr_role",
				"committees",
				"responsibilities_map",
				'training',
				'uploads',
				'overall_complience'
			])->nullable();
			$table->boolean('is_online')->default(0);
			$table->text('data')->nullable();
			$table->timestamps();

			$table->index('name');
			$table->index('name_id');

			$table->unique('name_id');
		});

	}

	private function createStatisticsHistory () {
		Capsule::schema()->create('statistics_history', function($table) {
			$table->increments('id');
			$table->integer('statistic_id')->unsigned();
			$table->float('value')->default(0)->unsigned();
			$table->timestamps();
		});

		$this->addForeignKey('statistics_history', 'statistics', 'statistic_id', 'id');
	}

	private function createDashboardStatistics () {
		Capsule::schema()->create('dashboard_statistics', function($table) {
			$table->increments('id');
			$table->integer('dashboard_id')->unsigned()->nullable();
			$table->integer('statistic_id')->unsigned()->nullable();
		});

		$this->addForeignKey('dashboard_statistics', 'dashboards', 'dashboard_id', 'id');
		$this->addForeignKey('dashboard_statistics', 'statistics', 'statistic_id', 'id');
	}

	private function createDashboardDataviews () {
		Capsule::schema()->create('dashboard_dataviews', function($table) {
			$table->increments('id');
			$table->integer('dashboard_id')->unsigned()->nullable();
			$table->integer('dataview_id')->unsigned()->nullable();
			$table->integer('display_size')->unsigned()->default(11);
			$table->string('chart_type')->default("pie");
		});

		$this->addForeignKey('dashboard_dataviews', 'dashboards', 'dashboard_id', 'id');
		$this->addForeignKey('dashboard_dataviews', 'dataviews', 'dataview_id', 'id');
	}

	private function createSmcrStaffFunctionsResponsibilities() {
		Capsule::schema()->create('smcr_staff_functions_responsibilities', function($table) {
			$table->increments('id');
			$table->integer('user_id')->unsigned();
			$table->integer('function_responsibility_id')->unsigned();
			$table->string('completion_status')->default('Not Accepted');
			$table->boolean('status')->default(1);
			$table->date('completion_date')->nullable(); // When manager sign's off
			$table->date('learner_sign_off')->nullable(); // When Learner sign's off
			$table->date('rejected')->nullable(); // Learner or manager can reject and date appears here
			$table->date('reminder_sent')->nullable();
			$table->integer('rejected_by')->unsigned(); // When rejected, add user ID here
			$table->integer('accepted_by')->unsigned(); // Manager's ID who accepted/signed off

			// Accepted, Rejected

			$table->timestamps();

			$table->index(['user_id', 'function_responsibility_id'], 'index_dhsk');
		});
		$this->addForeignKey('smcr_staff_functions_responsibilities', 'users', 'user_id', 'id', 'key_staff_funct_resp_user');
		$this->addForeignKey('smcr_staff_functions_responsibilities', 'smcr_functions_responsibilities', 'function_responsibility_id', 'id', 'key_staff_funct_resp');
	}

	private function createEvidenceTypes() {
		Capsule::schema()->create('evidence_types', function($table) {
			$table->increments('id');
			$table->string('name');
			$table->boolean('perishable')->default(false);
			$table->integer('life_of_data')->unsigned()->nullable()->default(NULL);
			$table->text('life_after')->nullable()->default(NULL);
			$table->boolean('status')->default(true);
			$table->timestamps();
			$table->index('name');
		});
	}


	// LearningDelivery
	private function createIlrLearningDeliveries() {
		Capsule::schema()->create('ilr_learning_deliveries', function($table) {
			$table->increments('id');
			$table->integer('user_id')->unsigned();

			$table->string('LearnAimRefTitle')->nullable();
			$table->string('LearnAimRef', 8)->nullable(); //LearnAimRef
			$table->unsignedTinyInteger('AimType')->nullable(); //AimType
			$table->unsignedTinyInteger('AimSeqNumber')->nullable(); //AimSeqNumber
			$table->date('LearnStartDate')->nullable(); //LearnStartDate
			$table->date('OrigLearnStartDate')->nullable(); //OrigLearnStartDate
			$table->date('LearnPlanEndDate')->nullable(); //LearnPlanEndDate
			$table->unsignedTinyInteger('FundModel')->nullable(); //FundModel
			$table->unsignedSmallInteger('PHours')->nullable(); //PHours
			$table->unsignedSmallInteger('OTJActHours')->nullable();
			$table->unsignedTinyInteger('ProgType')->nullable(); //ProgType
			$table->unsignedSmallInteger('FworkCode')->nullable(); //FworkCode
			$table->unsignedSmallInteger('PwayCode')->nullable(); //PwayCode - 3
			$table->unsignedMediumInteger('StdCode')->nullable(); //StdCode - 5
			$table->unsignedInteger('PartnerUKPRN')->nullable(); //PartnerUKPRN - 8
			$table->string('DelLocPostCode', 8)->nullable(); //DelLocPostCode
			$table->string('LSDPostcode', 8)->nullable(); //LSDPostcode
			$table->unsignedMediumInteger('AddHours')->nullable(); //AddHours
			$table->unsignedSmallInteger('PriorLearnFundAdj')->nullable(); //PriorLearnFundAdj
			$table->unsignedSmallInteger('OtherFundAdj')->nullable(); //OtherFundAdj
			$table->string('ConRefNumber', 20)->nullable(); //ConRefNumber
			$table->string('EPAOrgID', 7)->nullable(); //EPAOrgID
			$table->unsignedTinyInteger('EmpOutcome')->nullable(); //EmpOutcome - 1
			$table->unsignedTinyInteger('CompStatus')->nullable(); //CompStatus
			$table->date('LearnActEndDate')->nullable(); //LearnActEndDate
			$table->unsignedSmallInteger('WithdrawReason')->nullable(); //WithdrawReason - 3
			$table->date('WithdrawDate')->nullable();
			$table->unsignedTinyInteger('Outcome')->nullable(); //Outcome
			$table->date('AchDate')->nullable(); //AchDate
			$table->string('OutGrade', 6)->nullable(); //OutGrade
			$table->string('SWSupAimId', 36)->nullable(); //SWSupAimId

			/* shildren - to be json object or table? */
			$table->text('LearningDeliveryFAM')->nullable(); //LearningDeliveryFAM
			$table->text('LearningDeliveryWorkPlacement')->nullable(); //LearningDeliveryWorkPlacement
			$table->text('AppFinRecord')->nullable(); //AppFinRecord
			$table->text('ProviderSpecDeliveryMonitoring')->nullable(); //ProviderSpecDeliveryMonitoring
			$table->text('LearningDeliveryHE')->nullable(); //LearningDeliveryHE
			$table->text('DPOutcome')->nullable(); //DPOutcome

			$table->boolean('status')->default(1);
			$table->timestamps();
		});

		$this->addForeignKey('ilr_learning_deliveries', 'users', 'user_id', 'id');
	}

	private function createIlrLearningDeliveryAimTypes() {
		Capsule::schema()->create('ilr_learning_delivery_aim_types', function($table) {
			$table->increments('id');
			$table->string('name');
			$table->boolean('status')->default(1);
			$table->timestamps();
			$table->index('name');
		});
	}

	private function createIlrLearningDeliveryProgrammeTypes() {
		Capsule::schema()->create('ilr_learning_delivery_programme_types', function($table) {
			$table->increments('id');
			$table->string('name');
			$table->boolean('status')->default(1);
			$table->timestamps();
			$table->index('name');
		});
	}

	// LearningDeliveryOutcome
	private function createIlrLearningDeliveryOutcomes() {
		Capsule::schema()->create('ilr_learning_delivery_outcomes', function($table) {
			$table->increments('id');
			$table->string('name');
			$table->boolean('status')->default(1);
			$table->timestamps();
			$table->index('name');
		});
	}


	private function createIlrLearningDeliveryCompletionStatus() {
		Capsule::schema()->create('ilr_learning_delivery_completion_status', function($table) {
			$table->increments('id');
			$table->string('name');
			$table->boolean('status')->default(1);
			$table->timestamps();
			$table->index('name');
		});
	}

	private function createIlrLearningDeliveryFundingModels() {
		Capsule::schema()->create('ilr_learning_delivery_funding_models', function($table) {
			$table->increments('id');
			$table->string('name');
			$table->boolean('status')->default(1);
			$table->timestamps();

			$table->index('name');
		});
	}


	//LearnerEmploymentStatus
	private function createIlrLearningDeliveryFinancialRecords() {
		Capsule::schema()->create('ilr_learning_delivery_financial_records', function($table) {
			$table->increments('id');
			$table->integer('ilr_learning_delivery_id')->unsigned();
			$table->integer('user_id')->unsigned(); // Make life easier for me! I will need to delete records based on passed user_id argument.
			$table->string('AFinType', 3)->nullable();
			$table->tinyInteger('AFinCode')->unsigned()->nullable();
			$table->date('AFinDate')->nullable();
			$table->mediumInteger('AFinAmount')->unsigned()->nullable();

			$table->boolean('status')->default(1);
			$table->timestamps();
		});

		$this->addForeignKey('ilr_learning_delivery_financial_records', 'ilr_learning_deliveries', 'ilr_learning_delivery_id', 'id', 'key_delivery_financial_record');
		$this->addForeignKey('ilr_learning_delivery_financial_records', 'users', 'user_id', 'id');
	}

	//AFinType
	private function createIlrLearningDeliveryFinancialRecordTypes() {
		Capsule::schema()->create('ilr_learning_delivery_financial_record_types', function($table) {
			$table->increments('id');
			$table->string('code');
			$table->string('name');
			$table->boolean('status')->default(1);
			$table->timestamps();

			$table->index('name');
		});
	}


	private function createIlrLearningDeliveryFinancialRecordCodes() {
		Capsule::schema()->create('ilr_learning_delivery_financial_record_codes', function($table) {
			$table->increments('id');
			$table->tinyInteger('code')->unsigned()->nullable(); //EmpStat
			$table->string('type');
			$table->string('name');
			$table->boolean('status')->default(1);
			$table->timestamps();

			$table->index('name');
		});
	}


	private function createIlrLearningDeliveryMonitorings() {
		Capsule::schema()->create('ilr_learning_delivery_monitorings', function($table) {
			$table->increments('id');
			$table->integer('ilr_learning_delivery_id')->unsigned();
			$table->integer('user_id')->unsigned();
			$table->string('ProvSpecDelMonOccur', 1)->nullable();
			$table->string('ProvSpecDelMon', 20)->nullable();
			$table->boolean('status')->default(1);
			$table->timestamps();
		});

		$this->addForeignKey('ilr_learning_delivery_monitorings', 'ilr_learning_deliveries', 'ilr_learning_delivery_id', 'id', 'key_delivery_monitorings');
		$this->addForeignKey('ilr_learning_delivery_monitorings', 'users', 'user_id', 'id');
	}


	//LearnerEmploymentStatus
	private function createIlrLearnerEmploymentStatuses() {
		Capsule::schema()->create('ilr_learner_employment_statuses', function($table) {
			$table->increments('id');
			$table->integer('user_id')->unsigned();
			$table->tinyInteger('EmpStat')->unsigned()->nullable(); //EmpStat
			$table->date('DateEmpStatApp')->nullable(); //DateEmpStatApp
			$table->integer('EmpId')->unsigned(); // EmpId
			$table->string('AgreeId', 6)->nullable(); //AgreeId
			$table->text('EmploymentStatusMonitoring')->nullable(); //EmploymentStatusMonitoring
			$table->boolean('status')->default(1);
			$table->timestamps();
		});

		$this->addForeignKey('ilr_learner_employment_statuses', 'users', 'user_id', 'id');
	}

	// EmploymentStatus
	private function createIlrEmploymentStatuses() {
		Capsule::schema()->create('ilr_employment_statuses', function($table) {
			$table->increments('id');
			$table->string('name');
			$table->boolean('status')->default(1);
			$table->timestamps();
			$table->index('name');
		});
	}

	private function createIlrLearnerDestinationAndProgressions() {
		Capsule::schema()->create('ilr_learner_destination_and_progressions', function($table) {
			$table->increments('id');
			$table->integer('user_id')->unsigned();
			$table->string('OutType', 3);
			$table->tinyInteger('OutCode')->unsigned()->nullable();
			$table->date('OutStartDate')->nullable();
			$table->date('OutEndDate')->nullable();
			$table->date('OutCollDate')->nullable();
			$table->boolean('status')->default(1);
			$table->timestamps();
		});
	}

	private function createIlrProgressionOutcomeTypes() {
		Capsule::schema()->create('ilr_progression_outcome_types', function($table) {
			$table->increments('id');
			$table->string('name');
			$table->string('value');
			$table->boolean('status')->default(1);
			$table->timestamps();
			$table->index('name');
		});
	}

	private function createIlrProgressionOutcomeCodes() {
		Capsule::schema()->create('ilr_progression_outcome_codes', function($table) {
			$table->increments('id');
			$table->string('name');
			$table->tinyInteger('value')->unsigned()->nullable();
			$table->string('condition_key');
			$table->string('condition_value');
			$table->boolean('status')->default(1);
			$table->timestamps();
			$table->index('name');
		});
	}

	// Favorite table that can hold relations to multiple tables
	// Will see how this will work out.
	private function createFavorites() {
		Capsule::schema()->create('favorites', function($table) {
			$table->increments('id');
			$table->string('type'); // Free string to filter out favorite type.
			$table->integer('user_id')->unsigned();
			$table->integer('relation_id')->unsigned(); // reviews/learning results/learning modules, anything
			$table->boolean('status')->default(1);
			$table->timestamps();
		});

		$this->addForeignKey('favorites', 'users', 'user_id', 'id');
	}

	// Table for keeping history of data before updating row.
	private function createTableHistory () {
		Capsule::schema()->create('table_history', function($table) {
			$table->increments('id');
			$table->string('table_name');
			$table->text('before');
			$table->text('after');
			$table->integer('user_id')->unsigned();
			$table->timestamps();
		});
	}

	private function createComments() {
		Capsule::schema()->create('comments', function($table) {
			$table->increments('id');
			$table->integer('table_row_id')->unsigned()->nullable()->default(null); // Relevant tables ID
			$table->string('table_name')->nullable()->default(null); // Linked tables name
			$table->string('group')->nullable()->default(null); // branch out added files for same table/table_row_id
			$table->text('comment')->nullable()->default(null);
			$table->integer('added_by')->unsigned()->nullable(); // Will be always by person filling comment
			$table->integer('added_for')->unsigned()->nullable(); // Might be needed in specific scenarios
			$table->boolean('visible_learner')->default(1);
			$table->boolean('url')->default(false); // Comment that will be converted to URL, clickable, opens in new window
			$table->boolean('status')->default(1);
			$table->timestamps();
			$table->softDeletes();
		});
		$this->addForeignKey('comments', 'users', 'added_by', 'id');
	}

	private function createFiles() {
		Capsule::schema()->create('files', function($table) {
			$table->increments('id');
			$table->integer('table_row_id')->unsigned()->nullable()->default(null); // Relevant tables ID
			$table->string('table_name')->nullable()->default(null); // Linked tables name
			$table->string('group')->nullable()->default(null); // branch out added files for same table/table_row_id
			$table->string('file');
			$table->string('hash');
			$table->string('extension');
			$table->integer('added_by')->unsigned()->nullable(); // Will be always by person filling comment
			$table->integer('added_for')->unsigned()->nullable(); // Might be needed in specific scenarios
			$table->boolean('status')->default(1);
			$table->timestamps();

			$table->unique('hash');
		});
		$this->addForeignKey('files', 'users', 'added_by', 'id');
	}

	private function createMeetings() {
		Capsule::schema()->create('meetings', function($table) {
			$table->increments('id');
			$table->integer('table_row_id')->unsigned()->nullable()->default(null); // Relevant tables ID
			$table->string('table_name')->nullable()->default(null); // Linked tables name

			$table->string('name', 300);
			$table->string('description', 2000);
			$table->dateTime('preferred_time')->nullable();
			$table->boolean('manager_accepted');
			$table->boolean('learner_accepted');
			$table->integer('approved_by')->unsigned()->nullable();

			$table->integer('added_by')->unsigned()->nullable(); // Will be always by person filling comment
			$table->integer('added_for')->unsigned()->nullable(); // Might be needed in specific scenarios
			$table->boolean('status')->default(1);
			$table->timestamps();

		});
		$this->addForeignKey('meetings', 'users', 'added_by', 'id');
	}

	private function createDailyStatistics() {
		Capsule::schema()->create('daily_statistics', function($table) {
			$table->increments('id');


			$table->integer('resources_not_attempted')->unsigned()->default(0);
			$table->integer('resources_in_progress')->unsigned()->default(0);

			// Learning Resources (percentage of all Learning Resources with a "Completed" status)
			$table->integer('resources_completed')->unsigned()->default(0);
			$table->double('resources_completed_percentage', 8, 2)->default(0);

			// Also record separately percentages for each type of Learning Resource e.g. Blog Entries, e-Learning etc - this is less urgent but it is likely this will be needed later.
			// Use linked table: "daily_statistics_resources"

			// Committee Positions (percentage of Committee positions with a "Accepted" status
			$table->integer('smcr_committee_accepted')->unsigned()->default(0);
			$table->double('smcr_committee_accepted_percentage', 8, 2)->default(0);

			// Staff Status (percentage of employees with a "Completed" status
			$table->integer('learners_completed')->unsigned()->default(0);
			$table->double('learners_completed_percentage', 8, 2)->default(0);

			// SM Functions (percentage of those with a status of "Accepted")
			$table->integer('smcr_sm_functions_accepted')->unsigned()->default(0);
			$table->double('smcr_sm_functions_accepted_percentage', 8, 2)->default(0);

			// SM Responsibilities (percentage of those with a status of "Accepted")
			$table->integer('smcr_sm_responsibilities_accepted')->unsigned()->default(0);
			$table->double('smcr_sm_responsibilities_accepted_percentage', 8, 2)->default(0);

			// Certification Staff Functions (percentage of those with a status of "Accepted")
			$table->integer('smcr_cs_functions_accepted')->unsigned()->default(0);
			$table->double('smcr_cs_functions_accepted_percentage', 8, 2)->default(0);

			// Senior Managers Certified
			$table->integer('smcr_senior_manager_certified')->unsigned()->default(0);
			// Senior Managers Not Certified
			$table->integer('smcr_senior_manager_not_certified')->unsigned()->default(0);
			// Certificaiton Staff Certified
			$table->integer('smcr_certification_staff_certified')->unsigned()->default(0);
			// Certificaiton Staff Not Certified
			$table->integer('smcr_certification_staff_not_certified')->unsigned()->default(0);

			$table->double("microtime", 8, 4)->default(0);

			$table->boolean('status')->default(1);
			$table->timestamps();

		});
	}

	private function createDailyStatisticsResources() {
		Capsule::schema()->create('daily_statistics_resources', function($table) {
			$table->increments('id');

			$table->integer('type')->unsigned();
			$table->integer('daily_statistics_id')->unsigned();
			$table->integer('not_attempted')->unsigned()->default(0);
			$table->integer('in_progress')->unsigned()->default(0);
			$table->integer('completed')->unsigned()->default(0);
			$table->double('completed_percentage', 8, 2)->default(0);

			$table->boolean('status')->default(1);
			$table->timestamps();
		});

		$this->addForeignKey('daily_statistics_resources', 'daily_statistics', 'daily_statistics_id', 'id');
	}

	private function createSkillScans() {
		Capsule::schema()->create('skill_scans', function($table) {
			$table->increments('id');

			$table->integer('user_id')->unsigned();
			$table->integer('learning_results_id')->unsigned();
			$table->integer('learning_module_id')->unsigned();
			$table->integer('first_score')->unsigned()->default(0);
			$table->integer('last_score')->unsigned()->default(0);
			$table->integer('improvements')->unsigned()->default(0);
			$table->integer('attempt')->unsigned();
			$table->integer('track_id')->unsigned(); // Used to cut off answer repetition, do not use any answer after this ID for statistics as answers can be repeated. I sense that this is less than ideal solution.
			$table->date('next_due_date')->nullable();

			$table->boolean('status')->default(1);
			$table->timestamps();
		});

		$this->addForeignKey('skill_scans', 'users', 'user_id', 'id');
		$this->addForeignKey('skill_scans', 'learning_modules', 'learning_module_id', 'id');
		$this->addForeignKey('skill_scans', 'learning_results', 'learning_results_id', 'id');
	}


	private function createSkillScanAnswers() {
		Capsule::schema()->create('skill_scan_answers', function($table) {
			$table->increments('id');

			$table->integer('skill_scan_id')->unsigned();
			$table->string('question', 20);
			$table->text('comment')->nullable()->default(null);
			$table->integer('score')->unsigned();

			$table->boolean('status')->default(1);
			$table->timestamps();
		});

		$this->addForeignKey('skill_scan_answers', 'skill_scans', 'skill_scan_id', 'id');
	}


	private function createCrons() {
		Capsule::schema()->create('crons', function($table) {
			$table->increments('id');
			$table->string('name');
			$table->text('description');
			$table->string('function');
			$table->integer('frequency')->unsigned()->default(1440); // How often, minutes
			$table->integer('preferred_time')->unsigned()->nullable()->default(null); // 0 - 23, hours
			$table->text('conditions')->nullable()->default(null);
			$table->integer('times_run')->unsigned();
			$table->double("last_run_time", 12, 4);
			$table->double("average_run_time", 12, 4);
			$table->text('output');
			$table->text('last_output');
			$table->boolean('locked')->default(false); // When starting this, lock function so it is not run twice on long executions.
			$table->boolean('status')->default(true);
			$table->timestamps();
		});
	}

	private function createEmailHistory() {
		Capsule::schema()->create('email_history', function($table) {
			$table->increments('id');

			$table->string('email_to')->nullable();
			$table->string('email_from')->nullable();

			$table->string('name_to')->nullable();
			$table->string('name_from')->nullable();

			$table->string('subject')->nullable();
			$table->text('body')->nullable();

			$table->integer('user_id')->unsigned()->nullable();
			$table->integer('user_id_from')->unsigned()->nullable();

			$table->integer('email_template_id')->nullable()->unsigned();
			$table->string('email_template_name')->nullable();

			$table->text('debug')->nullable();
			$table->text('error')->nullable();
			$table->boolean('sent')->default(true);
			$table->timestamps();
		});
	}


	private function createBatchReports() {
		Capsule::schema()->create('batch_reports', function($table) {
			$table->increments('id');

			$table->string('title');
			$table->text('description')->nullable();
			$table->string('slug')->nullable(); // To identify what report I am on!
			$table->integer('custom_review_id')->unsigned()->nullable(); // And/IF custom review
			$table->text('table_state')->nullable();
			$table->text('table_state_original')->nullable();
			$table->text('args')->nullable(); // Some reports need to have some args in em!
			$table->text('display_list')->nullable();
			$table->text('filter_list')->nullable();
			$table->text('frequency_pattern')->nullable()->default(null);
			$table->date('run_date')->nullable()->default(null); // IF pattern specified, calculate run date here.
			$table->enum('type', ['report', 'email'])->nullable()->default(null);

			// Copy from email_queue
			$table->text('get_users_parameters')->nullable();
			$table->text('get_users_modified_parameters')->nullable();
			$table->text('get_users_arguments')->nullable();
			$table->text('get_users_url')->nullable();
			$table->string('get_users_method', 10)->nullable()->default('');
			$table->string('user_id_key', 255)->nullable()->default('');
			// EOF Copy from email_queue

			$table->boolean('copy_manager')->default(false);
			$table->integer('user_id')->unsigned(); // witch user saved this report
			$table->integer('times_run')->unsigned()->default(0);
			$table->text('debug')->nullable();
			$table->text('error')->nullable();
			$table->boolean('status')->default(true);
			$table->timestamps();

			$table->index('title');
		});

		$this->addForeignKey('batch_reports', 'custom_reviews', 'custom_review_id', 'id');
	}


	private function createBatchReportData() {
		Capsule::schema()->create('batch_report_data', function($table) {
			$table->increments('id');
			$table->integer('batch_report_id')->unsigned();
			$table->integer('rows')->unsigned();
			$table->integer('run_time')->unsigned(); // seconds
			$table->binary('data')->nullable();
			$table->boolean('unread')->default(true); // when record is created, it is set as unread, when printed or downloaded, it is set to false.
			$table->boolean('status')->default(true);
			$table->timestamps();
		});

		$this->addForeignKey('batch_report_data', 'batch_reports', 'batch_report_id', 'id');
	}


	private function createGatewayReadiness() {
		Capsule::schema()->create('gateway_readiness', function($table) {
			$table->increments('id');
			$table->string('name');
			$table->boolean('status')->default(true);
			$table->timestamps();
		});
	}

	private function createUserGatewayReadiness() {
		Capsule::schema()->create('user_gateway_readiness', function($table) {
			$table->increments('id');
			$table->integer('user_id')->unsigned();
			$table->integer('gateway_readiness_id')->unsigned();
			$table->integer('link_id')->unsigned(); // Either category or standa rdd id
			$table->enum('type', ['standard', 'category']);
			$table->boolean('status')->default(true);
			$table->timestamps();
		});

		$this->addForeignKey('user_gateway_readiness', 'gateway_readiness', 'gateway_readiness_id', 'id');
		$this->addForeignKey('user_gateway_readiness', 'users', 'user_id', 'id');
	}


	// This will hold record of QA role interacting with specific learning result
	private function createQaLearningResults() {
		Capsule::schema()->create('qa_learning_results', function($table) {
			$table->increments('id');
			$table->integer('qa_id')->unsigned();
			$table->integer('learning_result_id')->unsigned();
			$table->timestamps();
		});
		$this->addForeignKey('qa_learning_results', 'users', 'qa_id', 'id');
		$this->addForeignKey('qa_learning_results', 'learning_results', 'learning_result_id', 'id');
	}

	private function createLogAuthentications() {
		Capsule::schema()->create('log_authentications', function($table) {
			$table->increments('id');
			$table->integer('user_id')->unsigned();
			$table->string('name');
			$table->string('username');
			$table->string('email');
			$table->string('ip');
			$table->text('role');
			$table->enum('type', ['login', 'logout', 'timeout']);
			$table->timestamps();
			$table->index('name');
		});
	}

	private function createLogExportImports() {
		Capsule::schema()->create('log_export_imports', function($table) {
			$table->increments('id');
			$table->integer('user_id')->unsigned();
			$table->string('name');
			$table->string('username');
			$table->string('email');
			$table->string('ip');
			$table->text('role');
			$table->text('file_original'); // Original file name
			$table->text('file_name'); // Exported data is saved on disk!
			$table->text('file_extension');
			$table->text('file_password'); // Exported data is encrypted with this password
			$table->enum('type', ['exports', 'imports']);
			$table->text('parameters'); // Keep export parameters!
			$table->timestamps();
			$table->index('name');
		});
	}

	private function createSchedules() {
		Capsule::schema()->create('schedules', function($table) {
			$table->increments('id');
			$table->string('name')->nullable()->default(NULL); // Custom name that appears on Calendar, not requirement, or uses resources name
			$table->string('type')->nullable()->default(NULL); // Holds event type, like lesson, meeting, something else
			$table->integer('category_id')->unsigned()->nullable()->default(NULL); // Will use same learning_modules categories
			$table->integer('visit_type_id')->unsigned()->nullable()->default(NULL); // Usable if event is set up as meeting/visit.

			$table->text('description');
			$table->text('location'); // Usable for meetings at start, might be usefull later also.
			$table->datetime('start_date');
			$table->integer('duration')->unsigned()->nullable()->default(NULL); // In minutes
			$table->datetime('end_date')->nullable()->default(NULL);
			$table->integer('parent_id')->unsigned()->nullable()->default(NULL);
			$table->integer('created_by')->unsigned()->nullable();
			$table->integer('created_for')->unsigned()->nullable(); // In case this is one to one
			$table->boolean('cron_task')->default(false); // cron task will check if this is false, then will run delete/add actions
			$table->boolean('reminder_sent')->default(false);

			$table->boolean('status')->default(true);
			$table->integer('deleted_by')->unsigned()->nullable(); // Will hold ID of user who changed status!

			$table->boolean('visible_learner')->default(true);
			$table->boolean('visible_schedule')->default(true);
			$table->boolean('visible_learner_task')->default(true);
			$table->boolean('outlook_integration')->default(false);
			$table->text('outlook_refresh_token')->nullable();
			$table->text('outlook_event_id')->nullable();
			$table->json('outlook_event_response')->nullable();

			$table->timestamps();
			$table->softDeletes();

			$table->index('name');
		});
		$this->addForeignKey('schedules', 'schedules', 'parent_id', 'id');
		$this->addForeignKey('schedules', 'schedule_visit_types', 'visit_type_id', 'id');
	}
	private function createScheduleLinks() {
		Capsule::schema()->create('schedule_links', function($table) {
			$table->increments('id');
			$table->integer('schedule_id')->unsigned();
			$table->string('type')->nullable()->default(NULL);
			$table->enum('completion_status', ['Not Attempted', 'In Progress', 'Completed'])->default('Not Attempted'); // Usable for user links
			$table->boolean('is_authorised')->nullable()->default(null);
			$table->dateTime('completed_at')->nullable()->default(NULL);
			$table->integer('completed_by')->unsigned()->nullable()->default(NULL); // ID of person when link was completed!
			$table->integer('duration')->unsigned()->default(0); // This will hold minutes, usable to track how much time users spent in this event/zoom/teams/lesson/etc
			$table->integer('link_id')->unsigned()->nullable()->default(NULL);
			$table->dateTime('completion_date_custom')->nullable()->default(NULL); // used for learning resources!
			$table->boolean('cron_task')->default(false); // When link is added or removed it changes this boolean to false and cron task will action on it, if status is false, remove it, if status is true, assing resources, etc
			$table->boolean('manager_visitor')->default(false); // For managers, learner will see this manager/s in meeting popup as visiting manager.
			$table->integer('order')->unsigned()->default(0);
			$table->boolean('instructor_lead')->default(false);
			$table->boolean('ignore_email')->default(false);
			$table->boolean('status')->default(true);
			$table->integer('updated_by')->unsigned()->nullable()->default(NULL);
			$table->integer('deleted_by')->unsigned()->nullable()->default(NULL); // Will hold ID of user who changed status!

			$table->timestamps();
			$table->softDeletes();

			$table->index('type');
			$table->index('completion_status');

		});
		$this->addForeignKey('schedule_links', 'schedules', 'schedule_id', 'id');
		$this->dropForeignKey("schedule_links_schedule_id_foreign");
	}
	private function createScheduleVisitTypes() {
		Capsule::schema()->create('schedule_visit_types', function($table) {
			$table->increments('id');
			$table->string('name');
			$table->string('slug');
			$table->boolean('off_the_job_training')->default(false); // If checked, will count duration and add to learners "off the job training"
			$table->boolean('status')->default(true);
			$table->timestamps();

			$table->index('name');
			$table->string('slug');
		});
	}

	private function createSchedulePermissions() {
		Capsule::schema()->create('schedule_permissions', function($table) {
			$table->increments('id');
			$table->integer('schedule_id')->unsigned();
			$table->enum('type', ['owner', 'remove', 'view', 'edit']);
			$table->integer('user_id')->unsigned()->nullable();
			$table->timestamps();
		});
		$this->addForeignKey('schedule_permissions', 'schedules', 'schedule_id', 'id');
		$this->addForeignKey('schedule_permissions', 'users', 'user_id', 'id');
	}

	private function createHolidays() {
		Capsule::schema()->create('holidays', function($table) {
			$table->increments('id');
			$table->string('name')->nullable()->default(NULL);
			$table->date('start_date')->nullable();
			$table->date('end_date')->nullable();
			$table->integer('added_by')->unsigned()->nullable();
			$table->boolean('status')->default(true);
			$table->timestamps();

			$table->index('name');

		});
		$this->addForeignKey('holidays', 'users', 'added_by', 'id');
	}

	// Picklists are available for all when logged in, some of them must be public to register
	private function createPicklists() {
		Capsule::schema()->create('picklists', function($table) {
			$table->increments('id');
			$table->string('value');
			$table->string('slug');
			$table->string('type');
			$table->integer('order')->unsigned()->default(0);
			$table->boolean('public')->default(false);
			$table->boolean('status')->default(true);
			$table->timestamps();

			$table->index('value');
		});
	}

	//extension table that will link to any other table, will hold field name/type/value/order/status/table
	private function createTableExtensions() {
		Capsule::schema()->create('table_extensions', function($table) {
			$table->increments('id');
			$table->string('table'); // will hold table name that this entry is linked to
			$table->integer('table_id')->unsigned();
			$table->string('name'); // Field name
			$table->string('type'); // Field type, int/string/date
			$table->text('value'); // All data stored as string, might be tricky with dates, possibly, possibly not.
			$table->integer('order')->unsigned()->default(0);
			$table->boolean('status')->default(true);
			$table->timestamps();
		});
	}

	// List of all allowed fields used to extend tables!
	private function createTableExtensionFields() {
		Capsule::schema()->create('table_extension_fields', function($table) {
			$table->increments('id');
			$table->string('table');
			$table->string('field_name');
			$table->string('field_key');
			$table->string('field_type');
			$table->text('description')->default('');
			$table->string('versions')->default('');
			$table->text('conditions')->nullable()->default(null);
			$table->boolean('show_administration')->default(true);
			$table->boolean('show_learner')->default(false);
			$table->string('options')->nullable()->default(null); // If field is linked with collection that can be accessed in angular
			$table->string('default')->nullable()->default(null); // Default value

			$table->integer('order')->unsigned()->default(0);


			$table->boolean('status')->default(true);
			$table->timestamps();
		});
	}

	private function createEventTypes() {
		Capsule::schema()->create('event_types', function($table) {
			$table->increments('id');
			$table->string('name');
			$table->string('slug');
			$table->integer('event_category_id')->unsigned();
			$table->integer('order')->unsigned()->default(0);
			$table->boolean('status')->default(true);
			$table->boolean('system')->default(false);
			$table->timestamps();

			$table->index('name');
			$table->unique('slug');
		});
	}

	/*Discussion Forum*/
	private function createForums() {
		Capsule::schema()->create('forums', function($table) {
			$table->increments('id');
			$table->integer('schedule_id')->unsigned()->nullable()->default(null); // Relevant schedule
			$table->boolean('visible_learner')->default(1);
			$table->boolean('allow_learner_topic')->default(1);
			$table->boolean('allow_learner_post')->default(1);
			$table->integer('added_by')->unsigned()->nullable(); // Will be always by allowing forum
			$table->boolean('status')->default(true);

			$table->timestamps();
			$table->softDeletes();

		});
		$this->addForeignKey('forums', 'schedules', 'schedule_id', 'id');
	}

	/*Discussion Forum Topics*/
	private function createTopics() {  //more apt name could be forum_topics but table could be used as part of future association with another tables like comments
		Capsule::schema()->create('topics', function($table) {
			$table->increments('id');
			$table->integer('forum_id')->unsigned()->nullable()->default(null); // Relevant Forum
			$table->string('name');
			$table->integer('added_by')->unsigned()->nullable(); // Will be always by person filling topic
			$table->integer('added_for')->unsigned()->nullable(); // Might be needed in specific scenarios
			$table->text('content')->nullable()->default(null);
			$table->boolean('status')->default(true);

			$table->timestamps();
			$table->softDeletes();

			$table->index('name');
		});
		$this->addForeignKey('topics', 'forums', 'forum_id', 'id');
		$this->addForeignKey('topics', 'users', 'added_by', 'id');

	}

	/*Discussion Forum Topic Posts*/
	private function createPosts() {
		Capsule::schema()->create('posts', function($table) {
			$table->increments('id');
			$table->integer('topic_id')->unsigned()->nullable()->default(null); // Relevant Forum
			$table->text('post')->nullable()->default(null);
			$table->integer('added_by')->unsigned()->nullable(); // Will be always by person filling post
			$table->integer('added_for')->unsigned()->nullable(); // Might be needed in specific scenarios
			$table->boolean('status')->default(true);

			$table->timestamps();
			$table->softDeletes();
		});
		$this->addForeignKey('posts', 'topics', 'topic_id', 'id');
		$this->addForeignKey('posts', 'users', 'added_by', 'id');

	}

	/*Resource custom Query Builder Tables*/
	// Resource Query Variable,
	private function createResourceQueryVariables() {
		Capsule::schema()->create('resource_query_variables', function($table) {
			$table->increments('id');
			$table->string('name')->nullable()->default(null);;
			$table->string('description', 500)->nullable()->default(null);
			$table->enum('group', ['programmes', 'resources', 'lessons', 'competencies'])->nullable()->default(null); // In case some fields are exclusive to specific group, else null and show to both
			$table->string('variable_key', 100)->nullable()->default(null);; // Variable Key
			$table->string('master_table', 100)->nullable()->default(null);; // Master table
			$table->string('key', 100)->nullable()->default(null);
			$table->string('left_join', 1000)->nullable()->default(null); // table,table.what,=,othertable,othertable.what
			$table->string('left_join_on', 1000)->nullable()->default(null); // contains array of possibly multiple strings, each of them can be one ON inside left join.
			$table->string('inner_join', 1000)->nullable()->default(null); //
			$table->string('search', 100)->nullable()->default(null);; // table's display input field model
			$table->text('join')->nullable()->default(null);
			$table->string('select', 1000)->nullable()->default(null); // table.something as something,etc.uh as uh
			$table->text('select_raw')->nullable()->default(null);
			$table->string('group_by', 1000)->nullable()->default(null);
			$table->string('with', 100)->nullable()->default(null); // not sure if used.
			$table->string('where', 1000)->nullable()->default(null); // custom where queries json_encode "['learning_modules.status', '=', true],[etc]"
			$table->string('where_or', 1000)->nullable()->default(null); // Where query with multiple OR's inside
			$table->string('where_in', 1000)->nullable()->default(null); // Multiple where_ins
			$table->string('option_group', 100)->nullable()->default(null); // Option group!
			$table->integer('option_group_order')->unsigned()->default(0); // Will be used as default sort group
			$table->integer('order')->unsigned()->nullable();
			$table->boolean('sort')->default(true);
			$table->boolean('status')->default(1);
			$table->integer('created_by')->unsigned()->nullable()->default(null);
			$table->boolean('has_optional_values')->default(true);//if not then text box
			$table->timestamps();
			$table->index('name');
		});
	}

	// Resource Query Variable Conditions,
	private function createResourceQueryVariableConditions() {
		Capsule::schema()->create('resource_query_variable_conditions', function($table) {
			$table->increments('id');
			$table->integer('resource_query_variable_id'); // Relevant variable
			$table->string('key', 100); //option text
			$table->string('variable_key', 100)->nullable()->default(null);
			$table->string('value', 100)->nullable()->default(null);
			$table->integer('order')->unsigned()->default(0);
			$table->timestamps();
			$table->boolean('multiple')->default(false);//if not then text box
			$table->index('key');
		});
	}

	// Resource Query Variable Value,
	private function createResourceQueryVariableParameters() {
		Capsule::schema()->create('resource_query_variable_parameters', function($table) {
			$table->increments('id');
			$table->string('key', 100); //option text
			$table->integer('resource_query_variable_id'); // Relevant variable
			$table->string('variable_key', 100)->nullable()->default(null);
			$table->string('value', 100)->nullable()->default(null);
			$table->integer('order')->unsigned()->default(0);
			$table->timestamps();
			$table->index('key');
		});
	}

	// Resource Query Master,
	private function createResourceQueries() {
		Capsule::schema()->create('resource_queries', function($table) {
			$table->increments('id');
			$table->enum('type', ['programmes', 'resources', 'lessons'])->nullable()->default(null);
			$table->integer('type_id')->unsigned()->nullable()->default(null);
			$table->longText('query_variable')->nullable()->default(null);
			$table->longText('raw_query')->nullable()->default(null);
			$table->integer('created_by')->unsigned()->nullable()->default(null);
			$table->integer('deleted_by')->unsigned()->nullable();
			$table->softDeletes();
			$table->timestamps();
		});
	}


	// quality_controls,
	private function createQualityControls() {
		Capsule::schema()->create('quality_controls', function($table) {
			$table->increments('id');
			$table->enum('type', ['apprenticeship_issue_categories', 'apprenticeship_issues', 'apprenticeship_sub_issues'])->nullable()->default(null);
			$table->integer('type_id')->unsigned()->nullable()->default(null);
			$table->integer('user_id')->unsigned()->nullable()->default(null);
			$table->integer('qa_user_id')->unsigned()->nullable()->default(null);
			$table->enum('qa', ['Accepted', 'Rejected'])->nullable()->default(null);
			$table->boolean('qa_favorite')->default(false);
			$table->text('judgement_reason')->nullable();
			$table->timestamps();
		});
	}

	private function createCredasActors() {
		Capsule::schema()->create('credas_actors', function($table) {
			$table->increments('id');
			$table->string('name');
			$table->string('slug');
			$table->boolean('status')->default(true);
			$table->timestamps();

			$table->index('name');
			$table->unique('slug');
		});
	}

	private function createCredasProcesses() {
		Capsule::schema()->create('credas_processes', function($table) {
			$table->increments('id');
			$table->string('name');
			$table->string('slug');
			$table->string('process_id');
			$table->integer('visit_type_id')->unsigned()->nullable()->default(NULL);
			$table->boolean('status')->default(true);
			$table->timestamps();

			$table->index('name');
			$table->unique('slug');
		});
		$this->addForeignKey('credas_processes', 'schedule_visit_types', 'visit_type_id', 'id');
	}

	private function createCredasProcessActors() {
		Capsule::schema()->create('credas_process_actors', function($table) {
			$table->increments('id');
			$table->integer('credas_actor_id')->unsigned();
			$table->integer('credas_process_id')->unsigned();
			$table->integer('actor_id')->unsigned(); // confusing with credas_actor_id, this is value used by credas system, credas_actor_id is link to credas_actors table
			$table->boolean('status')->default(true);
			$table->timestamps();
		});
		$this->addForeignKey('credas_process_actors', 'credas_actors', 'credas_actor_id', 'id');
		$this->addForeignKey('credas_process_actors', 'credas_processes', 'credas_process_id', 'id');
	}

	private function createCredasCourses() {
		Capsule::schema()->create('credas_courses', function($table) {
			$table->increments('id');
			$table->string('title');
			$table->string('course_id');
			$table->string('journeyId');
			$table->dateTime('dateCreated');
			$table->double('percentageComplete', 8, 2)->default(0);
			$table->boolean('isComplete')->default(false);
			$table->boolean('status')->default(true);

			//$table->longText('processTasks')->nullable();
			$table->json('processTasks')->nullable();

			//$table->text('processActors')->nullable();
			$table->json('processActors')->nullable();

			$table->integer('learner_id')->unsigned();
			$table->integer('employer_id')->unsigned();
			$table->integer('provider_id')->unsigned();



			$table->timestamps();
			$table->index('title');
		});
		$this->addForeignKey('credas_courses', 'users', 'learner_id', 'id');
		Capsule::statement("ALTER TABLE credas_courses ROW_FORMAT=COMPRESSED;");
	}

	private function createCredasReports() {
		Capsule::schema()->create('credas_reports', function($table) {
			$table->increments('id');
			$table->string('title');
			$table->string('report_id');
			$table->string('journeyId');
			$table->dateTime('dateCreated');
			$table->double('percentageComplete', 8, 2)->default(0);
			$table->boolean('isComplete')->default(false);
			$table->boolean('status')->default(true);
			$table->json('processTasks')->nullable();
			$table->json('processActors')->nullable();
			$table->json('entities')->nullable();
			$table->json('esignDocuments')->nullable();
			$table->integer('learner_id')->unsigned();
			$table->integer('credas_process_id')->unsigned();

			$table->timestamps();
			$table->index('title');
		});
		$this->addForeignKey('credas_reports', 'users', 'learner_id', 'id');
		$this->addForeignKey('credas_reports', 'credas_processes', 'credas_process_id', 'id');
		Capsule::statement("ALTER TABLE credas_reports ROW_FORMAT=COMPRESSED;");
	}

	private function createCredasAutofillMappings() {
		Capsule::schema()->create('credas_autofill_mappings', function($table) {
			$table->increments('id');
			$table->integer('target_process_id')->unsigned();
			$table->string('target_item_name');
			$table->string('target_owner_id');
			$table->integer('target_actor_id')->unsigned();
			$table->string('source_notes')->nullable()->default(null);
			$table->string('source_table')->nullable()->default(null);
			$table->string('source_field')->nullable()->default(null);
			$table->string('default_value')->nullable()->default(null);
			$table->json('concats')->nullable()->default(null);
			$table->json('joins')->nullable()->default(null);
			$table->json('wheres')->nullable()->default(null);
			$table->json('source_journey_type')->nullable()->default(null);
			$table->json('source_item_owner_id')->nullable()->default(null);
			$table->integer('source_actor_id')->unsigned()->nullable()->default(null);
			$table->boolean('status')->default(true);

			$table->timestamps();
			$table->softDeletes();

			$table->index('target_item_name');
			$table->index('target_owner_id');
		});
		$this->addForeignKey('credas_autofill_mappings', 'credas_processes', 'target_process_id', 'id');
		$this->addForeignKey('credas_autofill_mappings', 'credas_actors', 'target_actor_id', 'id');
		$this->addForeignKey('credas_autofill_mappings', 'credas_actors', 'source_actor_id', 'id');
	}

	// Will use Licensing labels as base, can be overwritten
	//
	private function createDefaultLabels() {
		Capsule::schema()->create('default_labels', function($table) {
			$table->increments('id');
			$table->string('slug');
			$table->string('overwrite');
			$table->boolean('status')->default(true);
			$table->timestamps();

			$table->index('slug');
		});
	}

	//Custom Programme Status
	private function createCustomProgrammeStatuses() {
		Capsule::schema()->create('custom_programme_statuses', function($table) {
			$table->increments('id');
			$table->string('name');
			$table->string('slug')->nullable();
			$table->text('description')->nullable();
			$table->boolean('status')->default(true);
			$table->integer('order')->unsigned()->nullable()->default(0);
			$table->integer('created_by')->unsigned()->nullable()->default(NULL);
			$table->integer('updated_by')->unsigned()->nullable()->default(NULL);
			$table->timestamps();
			$table->index('slug');
		});
	}

	//User's Custom Programme Status
	private function createUserCustomProgrammeStatuses() {
		Capsule::schema()->create('user_custom_programme_statuses', function($table) {
			$table->increments('id');
			$table->integer('custom_programme_status_id')->unsigned();
			$table->text('notes')->nullable();
			$table->boolean('status')->default(true);
			$table->boolean('is_active')->default(false);
			$table->integer('user_id')->unsigned();
			$table->integer('created_by')->unsigned()->nullable()->default(NULL);
			$table->integer('updated_by')->unsigned()->nullable()->default(NULL);
			$table->timestamps();
		});
	}

	// quality_controls VIEW,
	private function createQualityControlsReportView() {
//		Capsule::statement("create or replace view quality_controls_report_view AS SELECT
//		DISTINCT qc.id,
//		qc.user_id,
//		qc.type,
//		qc.type_id,
//		qc.qa,
//		qc.judgement_reason,
//		qc.created_at,
//		qc.updated_at,
//		qc.qa_user_id,
//		type_name,
//		standard_name,
//		standard_id,
//		apprenticeship_issue_categories_id,
//		CASE WHEN qc.type = 'apprenticeship_issue_categories' then 'Outcome' WHEN qc.type = 'apprenticeship_issues' then 'Criteria' else 'Subcriteria' end AS qctype,
//		CASE WHEN qc.qa_favorite = 1 then 'yes' else 'no' end AS qa_favorite,
//		CONCAT(
//		  (
//			SELECT
//			  qctype
//		  ),
//		  ' ',
//		  UPPER(qc.qa),
//		  ' because ',
//		  LOWER(qc.judgement_reason)
//		) AS qa_details,
//		CONCAT(ul.fname, ' ', ul.lname) AS learner_name,
//		CONCAT(uq.fname, ' ', uq.lname) AS qa_user_name
//	  FROM
//		users,
//		quality_controls qc
//		JOIN users ul on ul.id = qc.user_id
//		JOIN users uq on uq.id = qc.qa_user_id
//		JOIN (
//		  (
//			SELECT
//			  outcome.id as apprenticeship_issue_categories_id,
//			  standard.id as standard_id,
//			  standard.name as standard_name,
//			  outcome.name AS type_name,
//			  outcome.id,
//			  'apprenticeship_issue_categories' AS type
//			FROM
//			  apprenticeship_issue_categories outcome
//			  JOIN apprenticeship_standards standard ON outcome.standard_id = standard.id
//			WHERE
//			  outcome.status = 1
//		  )
//		  UNION
//			(
//			  SELECT
//				aic.id as apprenticeship_issue_categories_id,
//				standard.id as standard_id,
//				standard.name as standard_name,
//				issue.name AS type_name,
//				issue.id,
//				'apprenticeship_issues' AS type
//			  FROM
//				apprenticeship_issues issue
//				JOIN apprenticeship_issue_categories aic ON issue.issue_category_id = aic.id
//				JOIN apprenticeship_standards standard ON aic.standard_id = standard.id
//			  WHERE
//				issue.status = 1
//			)
//		  UNION
//			(
//			  SELECT
//				aic.id as apprenticeship_issue_categories_id,
//				standard.id as standard_id,
//				standard.name as standard_name,
//				sub_issue.name AS type_name,
//				sub_issue.id,
//				'apprenticeship_sub_issues' AS type
//			  FROM
//				apprenticeship_issues sub_issue
//				JOIN apprenticeship_issue_categories aic ON sub_issue.issue_category_id = aic.id
//				JOIN apprenticeship_standards standard ON aic.standard_id = standard.id
//			  WHERE
//				sub_issue.status = 1
//			)
//		) ids ON qc.type_id = ids.id
//		AND qc.type = ids.type
//		AND qc.is_new = 1
//		");
	}

	// Table for keeping data of deleted rofs from select tables
	private function createTableDeleted () {
		Capsule::schema()->create('table_deleted', function($table) {
			$table->increments('id');
			$table->string('name');
			$table->text('data');
			$table->integer('user_id')->unsigned();
			$table->timestamps();
		});
		Capsule::statement("ALTER TABLE table_deleted ROW_FORMAT=COMPRESSED;");
	}

	private function createVenues () {
		Capsule::schema()->create('venues', function($table) {
			$table->increments('id');
			$table->string('name');
			$table->text('address')->nullable();
			$table->string('postcode')->nullable();
			$table->string('contact_number')->nullable();
			$table->string('contact_name')->nullable();
			$table->tinyInteger('rating')->unsigned()->nullable();
			$table->integer('capacity')->unsigned()->nullable();
			$table->boolean('status');
			$table->timestamps();
			$table->softDeletes();

			$table->index('name');
		});
	}


	private function createUserOutlookEvents () {
		Capsule::schema()->create('user_outlook_events', function($table) {
			$table->increments('id');
			$table->integer('user_id')->unsigned();
			$table->integer('event_id')->unsigned();
			$table->string('event_type')->default("");
			$table->text('outlook_event_id');
			$table->timestamps();

			$this->addForeignKey('user_outlook_events', 'users', 'user_id', 'id');
		});
	}
	private function createUserPaymentTransactions () {
		Capsule::schema()->create('user_payment_transactions', function($table) {
			$table->increments('id');
			$table->integer('user_id')->unsigned();
			$table->string('type')->default("");/*Learning, Schedules*/
			$table->string('type_reference_table')->default("");/*Learning, Schedules*/
			$table->integer('type_id')->unsigned();
            /*Return response*/
            $table->string('payment_authorisation_code')->default("");
            $table->string('income_management_receipt_number')->default("");
            $table->string('originators_reference')->default("");
            $table->string('card_scheme')->default("");
            $table->string('card_type')->default("");
            $table->float('payment_amount')->nullable();
            $table->string('response_code')->default("");
            $table->string('response_description')->default("");
            $table->string('gws_rd')->default("");
            /*Request*/
            $table->string('calling_application_id')->default("");
            $table->float('payment_total')->unsigned();
            $table->string('network_user_id')->default("");
            $table->text('return_url')->default("");
            $table->text('redirect_url')->default("");
            $table->text('fund_code')->default("");
            $table->text('Payment_1')->default("");
            /*Item Info*/
            $table->float('item_cost')->nullable();
            $table->float('item_discount')->nullable();
            $table->boolean('status')->default(false);
            $table->string('payment_gateway')->default("civica");
            $table->string('system_generated_transaction_id')->default("");
			$table->timestamps();

//			$this->addForeignKey('user_payment_transactions', 'users', 'user_id', 'id');
		});
	}

	//apprenticeship_outcome_groups
	private function createApprenticeshipOutcomeGroups() {
		Capsule::schema()->create('apprenticeship_outcome_groups', function($table) {
			$table->increments('id');
			$table->integer('standard_id')->unsigned();
			$table->string('name');
			$table->integer('created_by')->unsigned()->nullable();
			$table->integer('deleted_by')->unsigned()->nullable();
			$table->timestamps();
			$table->softDeletes();
		});

		$this->addForeignKey('apprenticeship_outcome_groups', 'apprenticeship_standards', 'standard_id', 'id');
	}

	private function createApprenticeshipOptionalOutcomes() {
		Capsule::schema()->create('apprenticeship_optional_outcomes', function($table) {
			$table->increments('id');
			$table->integer('outcome_id')->unsigned();
			$table->integer('group_id')->unsigned();
			$table->integer('created_by')->unsigned()->nullable();
			$table->integer('deleted_by')->unsigned()->nullable();
			$table->timestamps();
			$table->softDeletes();
		});

		$this->addForeignKey('apprenticeship_optional_outcomes', 'apprenticeship_issue_categories', 'outcome_id', 'id');
		$this->addForeignKey('apprenticeship_optional_outcomes', 'apprenticeship_outcome_groups', 'group_id', 'id');
	}


	private function createApprenticeshipOptionalOutcomeUsers() {
		Capsule::schema()->create('apprenticeship_optional_outcome_users', function($table) {
			$table->increments('id');
			$table->integer('group_id')->unsigned();
			$table->integer('user_id')->unsigned();
			$table->integer('standard_id')->unsigned();
			$table->integer('created_by')->unsigned()->nullable();
			$table->integer('deleted_by')->unsigned()->nullable();
			$table->timestamps();
			$table->softDeletes();
		});

		$this->addForeignKey('apprenticeship_optional_outcome_users', 'apprenticeship_outcome_groups', 'group_id', 'id');
		$this->addForeignKey('apprenticeship_optional_outcome_users', 'users', 'user_id', 'id');
		$this->addForeignKey('apprenticeship_optional_outcome_users', 'apprenticeship_standards', 'standard_id', 'id');
	}

	private function createUserSubDepartments() {
		Capsule::schema()->create('user_sub_departments', function($table) {
			$table->increments('id');
			$table->integer('user_id')->unsigned();
			$table->integer('department_id')->unsigned();
			$table->timestamps();
			$table->softDeletes();
		});
		$this->addForeignKey('user_sub_departments', 'users', 'user_id', 'id');
		$this->addForeignKey('user_sub_departments', 'departments', 'department_id', 'id');
	}

	private function createSkillScanHistories() {
		Capsule::schema()->create('skill_scan_histories', function($table) {
			$table->increments('id');
			$table->integer('user_id')->unsigned();
			$table->integer('skill_scan_id')->unsigned();
			$table->date('next_due_date')->nullable();
			$table->integer('learning_results_id')->unsigned();
			$table->integer('learning_module_id')->unsigned();
			$table->integer('first_score')->unsigned()->default(0);
			$table->integer('last_score')->unsigned()->default(0);
			$table->integer('improvements')->signed()->default(0);
			$table->integer('attempt')->unsigned();
			$table->boolean('status')->default(1);
			$table->timestamps();
		});

		$this->addForeignKey('skill_scan_histories', 'skill_scans', 'skill_scan_id', 'id');
	}

	public function createTable($table) {
		if (!Capsule::schema()->hasTable($table)) {
			$table_method = "create" . str_replace("_", "", ucwords($table, "_"));
			$this->$table_method();
		}
	}

	public function createDB() {
#		Capsule::schema()->defaultStringLength(192);

		foreach($this->_tables as $table) {
			$this->createTable($table);
		}

		//$this->createDummyScormTables();

		$this->createForeignKeys();
	}

	public function dropDB() {
		Capsule::connection()->statement("SET foreign_key_checks = 0");
		foreach($this->_tables as $table) {
			Capsule::schema()->dropIfExists(strtolower($table));
		}
		Capsule::connection()->statement("SET foreign_key_checks = 1");
	}
}
