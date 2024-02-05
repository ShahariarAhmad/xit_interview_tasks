<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\User;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/


Route::get('role', function () {
    return $nonAdminUsers = User::whereDoesntHave(
        'roles',
        function ($query) {
            $query->where('name', 'admin');
        }
    )->get();

    $role = Role::create(['name' => 'admin']);
    $role = Role::create(['name' => 'user']);

    $permission = Permission::create(['name' => 'approveUser']);
    $role = Role::find(1);
    $permission = Permission::where('name', 'approveUser')->get();

    $role->givePermissionTo($permission);
    // $permission->assignRole($role);
});
Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    $data = User::where('isVerified',0)->where('isCanceled',0)->whereDoesntHave(
        'roles',
        function ($query) {
            $query->where('name', 'admin');
        }
    )->get();
    return view('dashboard', compact('data'));
})->middleware(['auth', 'verified'])->name('dashboard');


Route::group(['prefix' => 'profile', 'as' => 'profile.'], function () {

    Route::get('/', [ProfileController::class, 'edit'])->name('edit');
    Route::patch('/', [ProfileController::class, 'update'])->name('update');
    Route::delete('/', [ProfileController::class, 'destroy'])->name('destroy');

    Route::get('/{id}/approve', [ProfileController::class, 'approve'])->name('approve');
    Route::get('/{id}/cancel', [ProfileController::class, 'cancel'])->name('cancel');
})->middleware('auth');



require __DIR__ . '/auth.php';
