<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('mongopay');
});



Route::group(['middleware' => 'auth'], function () {
	Route::get('table-list', function () {
		return view('pages.table_list');
	})->name('table');

	Route::get('typography', function () {
		return view('pages.typography');
	})->name('typography');

	Route::get('icons', function () {
		return view('pages.icons');
	})->name('icons');

	Route::get('map', function () {
		return view('pages.map');
	})->name('map');

	Route::get('notifications', function () {
		return view('pages.notifications');
	})->name('notifications');

	Route::get('rtl-support', function () {
		return view('pages.language');
	})->name('language');

	Route::get('upgrade', function () {
		return view('pages.upgrade');
	})->name('upgrade');
});

 
//Project Routes
Route::get('/member', [ 'as' => 'member', 'uses' => 'SiteController@check_member_login']);
Route::post('do-member-login', [ 'as' => 'do-member-login', 'uses' => 'SiteController@member_login']);

Route::group(['middleware' => ['App\Http\Middleware\CheckMember']], function () {
	Route::get('/member-dash', 'MemberController@index');
	Route::get('/welcome-letter', 'MemberController@welcome_letter');
	Route::any('/my-family/{id?}', 'TreeController@user_tree');
	Route::get('/my-earnings', 'EarningController@member_earning');
	Route::get('/the-wallet', 'WalletController@subscriber_wallet');

 
 
	Route::get('/profile', 'MemberController@profile');
	Route::get('/password', 'MemberController@passwords');
  
	Route::post('/save-profile', 'MemberController@save_profile');
	Route::post('/update-profile-pic', 'MemberController@save_profile_pic');
	Route::get('/member-logout', 'SiteController@member_logout');
	Route::get('/new-member', function () { return view('admin.member.register', ['title' => 'Create a Member Account']); });
	Route::any('/earningsearch', 'EarningController@earningsearch');
	Route::post('/transfer', 'WalletController@transfer_fund');
	Route::post('/update-profile', 'MemberController@save_profile');
	Route::post('/update-password', 'MemberController@update_member_password');

	Route::get('/the-transfered', 'WalletController@transfer_fund_history');
	//Route::get('/transfer-fund', 'WalletController@transfer_fund_form');
	Route::get('/the-received', 'WalletController@fund_received_history');

	if (env('ALLOW_FUND_TRANSFER') === true):
        Route::post('/transfersearch', 'WalletController@fund_search');
    
		Route::get('/transfer-fund', 'WalletController@transfer_fund_form');
    endif;
 
    if (env('ALLOW_WITHDRAW') == true):
        Route::get('/withdraw-fund', 'Wallet@withdraw_fund');
    endif;


}); 
Route::get('/getuser/{id}', 'SiteController@getuser');

Route::get('/register/{id?}/{leg?}/{epin?}', function () { return view('site.register', ['title' => 'Create a Member Account']); });

 