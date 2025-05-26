<?php

use App\Livewire\Dashboard\DashboardComponent;
use App\Livewire\Employee;
use App\Livewire\LoginComponent;
use App\Livewire\Order\Create;
use App\Livewire\Order\Edit;
use App\Livewire\Order\OrderEditComponent;
use App\Livewire\Order\PendingOrderComponent;
use App\Livewire\Order\ReadyOrderComponent;
use App\Livewire\Order\RemovedOrderComponent;
use App\Models\Order;
use Illuminate\Support\Facades\Route;

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
Route::get('login', LoginComponent::class)->name('login');
Route::group(['middleware' => 'auth'], function () {
    Route::get('/', DashboardComponent::class)->name('home');
    Route::get('create/order', Create::class)->name('order.create');
    // Route::get('edit/order/{orderID}', Edit::class)->name('order.edit');
    Route::get('pending/order', PendingOrderComponent::class)->name('pending.orders');
    Route::get('ready/order', ReadyOrderComponent::class)->name('ready.orders');
    Route::get('removed/order', RemovedOrderComponent::class)->name('removed.orders');
    Route::get('edit/order', OrderEditComponent::class)->name('order.edit');
    Route::get('employee', Employee::class)->name('employee');
    Route::get('logout', function() {
        auth()->logout();
        return redirect()->route('login');
    })->name('logout');

});


Route::get('email', function() {
    $order = Order::first();
    return view('email.ready-email', [
        'order' =>$order
    ]);
});