<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
*/


// Web Site
use Illuminate\Support\Facades\Auth;
use Illuminate\Routing\Console\MakeControllerCommand;
use Illuminate\Routing\Controller;

Route::get('/api/csrf', function() {
    return Session::token();
});

Route::get('auth', 'Tappleby\AuthToken\AuthTokenController@index');
Route::post('auth', 'Tappleby\AuthToken\AuthTokenController@store');
Route::delete('auth', 'Tappleby\AuthToken\AuthTokenController@destroy');

Route::group(array('before'=>'api.csrf'),function(){
	Route::get('/','LoginController@index');
	Route::get('/upgrade','upgradeController@index');
	Route::post('/upgrade','upgradeController@proceed');

	Route::get('/install','InstallController@index');
	Route::post('/install','InstallController@proceed');

	Route::get('/login','LoginController@index');
	Route::post('/login','LoginController@attemp');
	Route::get('/logout','LoginController@logout');

	Route::get('/forgetpwd','LoginController@forgetpwd');
	Route::post('/forgetpwd','LoginController@forgetpwdStepOne');
	Route::get('/forgetpwd/{uniqid}','LoginController@forgetpwdStepTwo');
	Route::post('/forgetpwd/{uniqid}','LoginController@forgetpwdStepTwo');

	Route::get('/register/classes','LoginController@registerClasses');
	Route::get('/register/searchStudents/{student}','LoginController@searchStudents');

    Route::get('/register/searchUsers/{usersType}/{student}','LoginController@searchUsers');

    Route::get('/register','LoginController@register');
	Route::post('/register','LoginController@registerPost');

	Route::get('/terms','LoginController@terms');
});
// api

	header("Access-Control-Allow-Origin: *");
	header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
	
    Route::get('/api/teachers','ApiController@teachers');
    Route::get('/api/students','ApiController@students');
    Route::get('/api/parents','ApiController@parents');
    Route::get('/api/subjects','ApiController@subjects');
    Route::get('/api/classes','ApiController@classes');
    Route::get('/api/exams','ApiController@exams');
    Route::get('/api/messages','ApiController@messages');


// Dashboard
Route::group(array('prefix'=>'/','before'=>'auth.Ui|auth.token|api.csrf'),function(){
	Route::get('/','DashboardController@index');

	Route::get('/dashboard','DashboardController@dashboardData');
	Route::get('/dashboard/baseUser','DashboardController@baseUser');
	Route::get('/calender','DashboardController@calender');
	Route::post('/dashboard/polls','DashboardController@savePolls');
	Route::get('/uploads/{section}/{image}','DashboardController@image');
    Route::post('/dashboard/changeAcYear','DashboardController@changeAcYear');
    Route::post('/dashboard/classesList','DashboardController@classesList');
    Route::post('/dashboard/subjectList','DashboardController@subjectList');
    Route::get('/dashboard/profileImage/{id}','DashboardController@profileImage');
    
   	//Languages & phrases
	Route::get('/languages','DashboardController@index');
	Route::get('/languages/listAll','LanguagesController@listAll');
	Route::post('/languages','LanguagesController@create');
	Route::get('/languages/{id}','LanguagesController@fetch');
    Route::post('/languages/delete/{id}','LanguagesController@delete');
    Route::post('/languages/{id}','LanguagesController@edit');

	//Dormitories
	Route::get('/dormitories','DashboardController@index');
	Route::get('/dormitories/listAll','DormitoriesController@listAll');
	Route::post('/dormitories','DormitoriesController@create');
	Route::get('/dormitories/{id}','DormitoriesController@fetch');
    Route::post('/dormitories/delete/{id}','DormitoriesController@delete');
    Route::post('/dormitories/{id}','DormitoriesController@edit');

	//Admins
	Route::get('/admins','DashboardController@index');
	Route::get('/admins/listAll','AdminsController@listAll');
	Route::post('/admins','AdminsController@create');
	Route::get('/admins/{id}','AdminsController@fetch');
    Route::post('/admins/delete/{id}','AdminsController@delete');
    Route::post('/admins/{id}','AdminsController@edit');

	//Teachers
	Route::get('/teachers','DashboardController@index');
	Route::post('/teachers/import/{type}','TeachersController@import');
    Route::post('/teachers/reviewImport','TeachersController@reviewImport');
	Route::get('/teachers/export','TeachersController@export');
	Route::get('/teachers/exportpdf','TeachersController@exportpdf');
	Route::post('/teachers/upload','TeachersController@uploadFile');
	Route::get('/teachers/waitingApproval','TeachersController@waitingApproval');
	Route::post('/teachers/leaderBoard/{id}','TeachersController@leaderboard');
    Route::post('/teachers/approveOne/{id}','TeachersController@approveOne');
	Route::get('/teachers/profile/{id}','TeachersController@profile');
	Route::get('/teachers/listAll','TeachersController@listAll');
	Route::get('/teachers/listAll/{page}','TeachersController@listAll');
    Route::get('/teachers/search/{keyword}/{page}','TeachersController@search');
	Route::post('/teachers','TeachersController@create');
	Route::get('/teachers/{id}','TeachersController@fetch');
    Route::post('/teachers/leaderBoard/delete/{id}','TeachersController@leaderboardRemove');
    Route::post('/teachers/delete/{id}','TeachersController@delete');
    Route::post('/teachers/{id}','TeachersController@edit');

	//Students
	Route::get('/students','DashboardController@index');
	Route::post('/students/import/{type}','StudentsController@import');
    Route::post('/students/reviewImport','StudentsController@reviewImport');
	Route::get('/students/export','StudentsController@export');
	Route::get('/students/exportpdf','StudentsController@exportpdf');
	Route::post('/students/upload','StudentsController@uploadFile');
	Route::get('/students/waitingApproval','StudentsController@waitingApproval');
	Route::post('/students/approveOne/{id}','StudentsController@approveOne');
    Route::get('/students/print/marksheet/{student}/{exam}','StudentsController@marksheetPDF');
	Route::get('/students/marksheet/{id}','StudentsController@marksheet');
	Route::get('/students/attendance/{id}','StudentsController@attendance');
	Route::get('/students/profile/{id}','StudentsController@profile');
	Route::post('/students/leaderBoard/{id}','StudentsController@leaderboard');
    Route::get('/students/listAll','StudentsController@listAll');
	Route::get('/students/listAll/{page}','StudentsController@listAll');
    Route::get('/students/search/{keyword}/{page}','StudentsController@search');
	Route::post('/students','StudentsController@create');
	Route::get('/students/{id}','StudentsController@fetch');
    Route::post('/students/printbulk/marksheet','StudentsController@marksheetBulkPDF');
    Route::post('/students/leaderBoard/delete/{id}','StudentsController@leaderboardRemove');
    Route::post('/students/acYear/delete/{student}/{id}','StudentsController@acYearRemove');
    Route::post('/students/delete/{id}','StudentsController@delete');
	Route::post('/students/{id}','StudentsController@edit');

	//Parents
	Route::get('/parents/search/{student}','ParentsController@searchStudents');
    Route::post('/parents/import/{type}','ParentsController@import');
    Route::post('/parents/reviewImport','ParentsController@reviewImport');
	Route::get('/parents/export','ParentsController@export');
	Route::get('/parents/exportpdf','ParentsController@exportpdf');
	Route::get('/parents','DashboardController@index');
	Route::post('/parents/upload','ParentsController@uploadFile');
	Route::get('/parents/waitingApproval','ParentsController@waitingApproval');
	Route::get('/parents/profile/{id}','ParentsController@profile');
	Route::post('/parents/approveOne/{id}','ParentsController@approveOne');
	Route::get('/parents/listAll','ParentsController@listAll');
	Route::get('/parents/listAll/{page}','ParentsController@listAll');
    Route::get('/parents/search/{keyword}/{page}','ParentsController@search');
	Route::post('/parents','ParentsController@create');
	Route::get('/parents/{id}','ParentsController@fetch');
    Route::post('/parents/delete/{id}','ParentsController@delete');
	Route::post('/parents/{id}','ParentsController@edit');

	//Classes
	Route::get('/classes','DashboardController@index');
	Route::get('/classes/listAll','ClassesController@listAll');
	Route::post('/classes','ClassesController@create');
	Route::get('/classes/{id}','ClassesController@fetch');
    Route::post('/classes/delete/{id}','ClassesController@delete');
	Route::post('/classes/{id}','ClassesController@edit');

    //Sections
	Route::get('/sections','DashboardController@index');
	Route::get('/sections/listAll','sectionsController@listAll');
	Route::post('/sections','sectionsController@create');
	Route::get('/sections/{id}','sectionsController@fetch');
    Route::post('/sections/delete/{id}','sectionsController@delete');
	Route::post('/sections/{id}','sectionsController@edit');

	//subjects
	Route::get('/subjects','DashboardController@index');
	Route::get('/subjects/listAll','SubjectsController@listAll');
	Route::post('/subjects','SubjectsController@create');
	Route::get('/subjects/{id}','SubjectsController@fetch');
    Route::post('/subjects/delete/{id}','SubjectsController@delete');
	Route::post('/subjects/{id}','SubjectsController@edit');

	//NewsBoardass
	Route::get('/newsboard','DashboardController@index');
	Route::get('/newsboard/listAll/{page}','NewsBoardController@listAll');
    Route::get('/newsboard/search/{keyword}/{page}','NewsBoardController@search');
	Route::post('/newsboard','NewsBoardController@create');
	Route::get('/newsboard/{id}','NewsBoardController@fetch');
    Route::post('/newsboard/delete/{id}','NewsBoardController@delete');
	Route::post('/newsboard/{id}','NewsBoardController@edit');

	//Library
	Route::get('/library','DashboardController@index');
	Route::get('/library/listAll','LibraryController@listAll');
	Route::get('/library/listAll/{page}','LibraryController@listAll');
    Route::get('/library/download/{id}','LibraryController@download');
    Route::get('/library/search/{keyword}/{page}','LibraryController@search');
	Route::post('/library','LibraryController@create');
	Route::get('/library/{id}','LibraryController@fetch');
    Route::post('/library/delete/{id}','LibraryController@delete');
	Route::post('/library/{id}','LibraryController@edit');

	//Account Settings
	Route::get('/accountSettings','DashboardController@index');
	Route::get('/accountSettings/langs','AccountSettingsController@langs');
	Route::get('/accountSettings/data','AccountSettingsController@listAll');
	Route::post('/accountSettings/profile','AccountSettingsController@saveProfile');
	Route::post('/accountSettings/email','AccountSettingsController@saveEmail');
	Route::post('/accountSettings/password','AccountSettingsController@savePassword');

	//Class Schedule
	Route::get('/classschedule','DashboardController@index');
	Route::get('/classschedule/listAll','ClassScheduleController@listAll');
	Route::get('/classschedule/{id}','ClassScheduleController@fetch');
	Route::get('/classschedule/sub/{id}','ClassScheduleController@fetchSub');
	Route::post('/classschedule/sub/{id}','ClassScheduleController@editSub');
    Route::post('/classschedule/delete/{class}/{id}','ClassScheduleController@delete');
	Route::post('/classschedule/{id}','ClassScheduleController@addSub');

	//Site Settings
    Route::get('/settings','DashboardController@index');
	Route::get('/siteSettings/langs','SiteSettingsController@langs');
	Route::get('/siteSettings/{title}','SiteSettingsController@listAll');
	// Route::get('/siteSettings/{title}','SiteSettingsController@save');
	Route::post('/siteSettings/{title}','SiteSettingsController@save');
	Route::post('/siteSettings/fileUpload','SiteSettingsController@fileUpload');

	//Attendance
    Route::get('/attendance','DashboardController@index');
	Route::get('/attendance/data','AttendanceController@listAll');
	Route::post('/attendance/list','AttendanceController@listAttendance');
	Route::post('/attendance','AttendanceController@saveAttendance');
	Route::get('/attendance/stats','AttendanceController@getStats');
	Route::get('/attendance/stats/{date}','AttendanceController@getStats');
	Route::post('/attendance/stats','AttendanceController@search');

	//Grade Levels
	Route::get('/gradeLevels','DashboardController@index');
	Route::get('/gradeLevels/listAll','GradeLevelsController@listAll');
	Route::post('/gradeLevels','GradeLevelsController@create');
	Route::get('/gradeLevels/{id}','GradeLevelsController@fetch');
    Route::post('/gradeLevels/delete/{id}','GradeLevelsController@delete');
	Route::post('/gradeLevels/{id}','GradeLevelsController@edit');

	//Exams List
	Route::get('/examsList','DashboardController@index');
	Route::get('/examsList/listAll','ExamsListController@listAll');
	Route::post('/examsList/notify/{id}','ExamsListController@notifications');
	Route::post('/examsList','ExamsListController@create');
	Route::get('/examsList/{id}','ExamsListController@fetch');
	Route::post('/examsList/{id}','ExamsListController@edit');
	Route::get('/examsList/getMarks/{exam}/{class}/{subject}','ExamsListController@fetchMarks');
    Route::post('/examsList/delete/{id}','ExamsListController@delete');
	Route::post('/examsList/saveMarks/{exam}/{class}/{subject}','ExamsListController@saveMarks');

	//Events
	Route::get('/events','DashboardController@index');
	Route::get('/events/listAll','EventsController@listAll');
	Route::post('/events','EventsController@create');
	Route::get('/events/{id}','EventsController@fetch');
    Route::post('/events/delete/{id}','EventsController@delete');
	Route::post('/events/{id}','EventsController@edit');

	//Assignments
	Route::get('/assignments','DashboardController@index');
	Route::get('/assignments/listAll','AssignmentsController@listAll');
    Route::get('/assignments/listAnswers/{id}','AssignmentsController@listAnswers');
	Route::post('/assignments','AssignmentsController@create');
    Route::get('/assignments/download/{id}','AssignmentsController@download');
    Route::get('/assignments/downloadAnswer/{id}','AssignmentsController@downloadAnswer');
    Route::get('/assignments/{id}','AssignmentsController@fetch');
    Route::post('/assignments/checkUpload','AssignmentsController@checkUpload');
    Route::post('/assignments/delete/{id}','AssignmentsController@delete');
	Route::post('/assignments/upload/{id}','AssignmentsController@upload');
	Route::post('/assignments/{id}','AssignmentsController@edit');

    //Study Material
	Route::get('/materials','DashboardController@index');
	Route::get('/materials/listAll','StudyMaterialController@listAll');
	Route::post('/materials','StudyMaterialController@create');
    Route::get('/materials/download/{id}','StudyMaterialController@download');
    Route::get('/materials/{id}','StudyMaterialController@fetch');
    Route::post('/materials/delete/{id}','StudyMaterialController@delete');
	Route::post('/materials/{id}','StudyMaterialController@edit');

	//Mail / SMS
	Route::get('/mailsms','DashboardController@index');
	Route::get('/mailsms/listAll','MailSmsController@listAll');
	Route::post('/mailsms','MailSmsController@create');
	Route::get('/mailsms/settings','MailSmsController@settings');
	Route::post('/mailsms/settings','MailSmsController@settingsSave');

	//Messages
	Route::get('/messages','DashboardController@index');
	Route::post('/messages','MessagesController@create');
	Route::get('/messages/listAll/{page}','MessagesController@listMessages');
    Route::get('/messages/searchUser/{user}','MessagesController@searchUser');
	Route::post('/messages/read','MessagesController@read');
	Route::post('/messages/unread','MessagesController@unread');
	Route::post('/messages/delete','MessagesController@delete');
	Route::get('/messages/{id}','MessagesController@fetch');
	Route::post('/messages/{id}','MessagesController@reply');
	Route::get('/messages/ajax/{from}/{to}/{before}','MessagesController@ajax');
	Route::get('/messages/before/{from}/{to}/{before}','MessagesController@before');

	//Online Exams
	Route::get('/onlineExams','DashboardController@index');
	Route::get('/onlineExams/listAll','OnlineExamsController@listAll');
    Route::post('/onlineExams/take/{id}','OnlineExamsController@take');
	Route::post('/onlineExams/took/{id}','OnlineExamsController@took');
	Route::get('/onlineExams/marks/{id}','OnlineExamsController@marks');
	Route::get('/onlineExams/export/{id}/{type}','OnlineExamsController@export');
	Route::post('/onlineExams','OnlineExamsController@create');
	Route::get('/onlineExams/{id}','OnlineExamsController@fetch');
    Route::post('/onlineExams/delete/{id}','OnlineExamsController@delete');
	Route::post('/onlineExams/{id}','OnlineExamsController@edit');

	//Transportation
	Route::get('/transports','DashboardController@index');
	Route::get('/transports/listAll','TransportsController@listAll');
	Route::get('/transports/list/{id}','TransportsController@fetchSubs');
	Route::post('/transports','TransportsController@create');
	Route::get('/transports/{id}','TransportsController@fetch');
    Route::post('/transports/delete/{id}','TransportsController@delete');
	Route::post('/transports/{id}','TransportsController@edit');

	//Media
	Route::get('/media','DashboardController@index');
	Route::get('/media/listAll','MediaController@listAlbum');
	Route::get('/media/listAll/{id}','MediaController@listAlbumById');
	Route::get('/media/resize/{file}/{width}/{height}','MediaController@resize');
    Route::get('/media/image/{id}','MediaController@image');
	Route::post('/media/newAlbum','MediaController@newAlbum');
	Route::get('/media/editAlbum/{id}','MediaController@fetchAlbum');
	Route::post('/media/editAlbum/{id}','MediaController@editAlbum');
	Route::post('/media','MediaController@create');
	Route::get('/media/{id}','MediaController@fetch');
    Route::post('/media/album/delete/{id}','MediaController@deleteAlbum');
    Route::post('/media/delete/{id}','MediaController@delete');
    Route::post('/media/{id}','MediaController@edit');

	//Static pages
	Route::get('/static','DashboardController@index');
	Route::get('/static/listAll','StaticPagesController@listAll');
	Route::get('/static/active/{id}','StaticPagesController@active');
	Route::post('/static','StaticPagesController@create');
	Route::get('/static/{id}','StaticPagesController@fetch');
    Route::post('/static/delete/{id}','StaticPagesController@delete');
	Route::post('/static/{id}','StaticPagesController@edit');

	//Polls
	Route::get('/polls','DashboardController@index');
	Route::get('/polls/listAll','PollsController@listAll');
	Route::post('/polls/active/{id}','PollsController@makeActive');
	Route::post('/polls','PollsController@create');
	Route::get('/polls/{id}','PollsController@fetch');
    Route::post('/polls/delete/{id}','PollsController@delete');
	Route::post('/polls/{id}','PollsController@edit');

	//Mail / SMS Templates
	Route::get('/mailsmsTemplates','DashboardController@index');
	Route::get('/MailSMSTemplates/listAll','MailSMSTemplatesController@listAll');
	Route::get('/MailSMSTemplates/{id}','MailSMSTemplatesController@fetch');
	Route::post('/MailSMSTemplates/{id}','MailSMSTemplatesController@edit');

    //Fee Types
	Route::get('/feeTypes','DashboardController@index');
	Route::get('/feeTypes/listAll','feeTypesController@listAll');
	Route::post('/feeTypes','feeTypesController@create');
	Route::get('/feeTypes/{id}','feeTypesController@fetch');
    Route::post('/feeTypes/delete/{id}','feeTypesController@delete');
	Route::post('/feeTypes/{id}','feeTypesController@edit');

    //Fee Allocation
	Route::get('/feeAllocation','DashboardController@index');
	Route::get('/feeAllocation/listAll','feeAllocationController@listAll');
	Route::post('/feeAllocation','feeAllocationController@create');
	Route::get('/feeAllocation/{id}','feeAllocationController@fetch');
    Route::post('/feeAllocation/delete/{id}','feeAllocationController@delete');
	Route::post('/feeAllocation/{id}','feeAllocationController@edit');

	//Payments
	Route::get('/payments','DashboardController@index');
	Route::get('/payments/listAll','PaymentsController@listAll');
    Route::get('/payments/listAll/{page}','PaymentsController@listAll');
    Route::get('/payments/searchUsers/{student}','PaymentsController@searchStudents');
    Route::get('/payments/search/{keyword}/{page}','PaymentsController@search');
	Route::get('/payments/failed','PaymentsController@paymentFailed');
	Route::get('/payments/invoice/{id}','PaymentsController@invoice');
	Route::get('/payments/export/{id}','PaymentsController@export');
	Route::get('/payments/details/{id}','PaymentsController@PaymentData');
	Route::post('/payments','PaymentsController@create');
	Route::get('/payments/{id}','PaymentsController@fetch');
    Route::post('/payments/delete/{id}','PaymentsController@delete');
	Route::post('/payments/{id}','PaymentsController@edit');

    //Expenses
	Route::get('/expenses','expensesController@index');
	Route::get('/expenses/listAll','expensesController@listAll');
	Route::post('/expenses','expensesController@create');
	Route::get('/expenses/{id}','expensesController@fetch');
    Route::post('/expenses/delete/{id}','expensesController@delete');
	Route::post('/expenses/{id}','expensesController@edit');

	//Promotion
    Route::get('/promotion','DashboardController@index');
    Route::get('/promotion/search/{student}','promotionController@searchStudents');
	Route::get('/promotion/listData','promotionController@listAll');
	Route::post('/promotion/listStudents','promotionController@listStudents');
	Route::post('/promotion','promotionController@promoteNow');

    //Academic Year
    Route::get('/academicYear','DashboardController@index');
	Route::get('/academic/listAll','academicYearController@listAll');
	Route::post('/academic/active/{id}','academicYearController@active');
	Route::post('/academic','academicYearController@create');
	Route::get('/academic/{id}','academicYearController@fetch');
    Route::post('/academic/delete/{id}','academicYearController@delete');
	Route::post('/academic/{id}','academicYearController@edit');

    //Staff Attendance
	Route::get('/staffAttendance','DashboardController@index');
	Route::post('/sattendance/list','SAttendanceController@listAttendance');
	Route::post('/sattendance','SAttendanceController@saveAttendance');
	Route::get('/sattendance/stats','SAttendanceController@getStats');
	Route::get('/sattendance/stats/{date}','SAttendanceController@getStats');
	Route::post('/sattendance/stats','SAttendanceController@search');

    //Reports
    Route::get('/reports','DashboardController@index');
    Route::post('/reports','reportsController@report');
    Route::get('/reports/preAttendace','reportsController@preAttendaceStats');

    //vacation
    Route::get('/vacation','DashboardController@index');
    Route::post('/vacation','vacationController@getVacation');
    Route::post('/vacation','vacationController@getVacation');
    Route::post('/vacation/confirm','vacationController@saveVacation');
    Route::post('/vacation/delete/{id}','vacationController@delete');

    //Hostel
	Route::get('/hostel','DashboardController@index');
	Route::get('/hostel/listAll','hostelController@listAll');
    Route::get('/hostel/listSubs/{id}','hostelController@listSubs');
	Route::post('/hostel','hostelController@create');
	Route::get('/hostel/{id}','hostelController@fetch');
    Route::post('/hostel/delete/{id}','hostelController@delete');
	Route::post('/hostel/{id}','hostelController@edit');

    //HostelCat
	Route::get('/hostelCat','DashboardController@index');
	Route::get('/hostelCat/listAll','hostelCatController@listAll');
	Route::post('/hostelCat','hostelCatController@create');
	Route::get('/hostelCat/{id}','hostelCatController@fetch');
    Route::post('/hostelCat/delete/{id}','hostelCatController@delete');
	Route::post('/hostelCat/{id}','hostelCatController@edit');
});
Route::post('/payments/success/{id}','PaymentsController@paymentSuccess');

// Templates
Route::group(array('prefix'=>'/templates/'),function(){
    Route::get('{template}', array( function($template)
    {
        $template = str_replace(".html","",$template);
        View::addExtension('html','php');
        return View::make('templates.'.$template);
    }));
});

Route::filter('api.csrf', function($route, $request)
{
    if ( Request::isMethod('post') )
	{
		if( !((Input::has('_token') AND Session::token() == Input::get('_token')) || ($request->header('X-Csrf-Token') != "" AND Session::token() == $request->header('X-Csrf-Token')) ) ){
		//	return Response::json('CSRF does not match', 400);
		}
	}
});

Event::listen('auth.token.valid', function($user)
{
  //Token is valid, set the user on auth system.
  Auth::setUser($user);
});

Route::filter('auth.Ui', function()
{
    if(Request::header('x-auth-token') != ""){
        return;
    }
    if (Auth::guest()) return Redirect::guest('/login');

    if (Auth::check())
    {
        if (!Auth::User())
            return Redirect::to('/dashboard');
    }
    else
        return Redirect::guest('/login');
});
