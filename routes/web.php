<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\UserController;




// Rutas públicas de autenticación
require __DIR__.'/auth.php';

// Grupo de rutas protegidas (requiere login)
Route::middleware('auth')->group(function () {

Route::get('/responses', [MessageController::class, 'showResponses'])->name('responses');
Route::post('/responses/reply', [MessageController::class, 'reply'])->name('responses.reply');


//Route::post('/api/waapi-webhook', [WebhookController::class, 'handle']);

//Route::post('/webhooks/waapi/{token}', [WebhookController::class, 'handle']);




// Mostrar la vista de respuestas
Route::get('/responses', [MessageController::class, 'showResponses'])
    ->name('responses');

// Enviar respuesta a un mensaje
Route::post('/responses/reply', [MessageController::class, 'reply'])
    ->name('responses.reply')
    ->middleware('auth');


    // Dashboard con permiso 'send-message'
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Plantillas
    Route::get('/plantillas', [MessageController::class, 'verPlantillas'])->name('templates.index');
    Route::post('/plantillas', [MessageController::class, 'guardarPlantilla'])->name('templates.store');

    // Subir Excel
    Route::get('/subir-excel', [MessageController::class, 'formUploadExcel'])->name('excel.upload');
    Route::post('/subir-excel', [MessageController::class, 'subirExcel'])->name('subir.excel');

    Route::get('/messages-preview', [MessageController::class, 'previewMessages'])
    ->name('messages-preview');

     //Enviar mensajes
    // Asegúrate que esta línea esté dentro del grupo Route::middleware('auth')->group(...)
Route::get('/enviar-mensajes', [MessageController::class, 'formSendMessages'])->name('messages.send.form');
//
    Route::post('/send-messages', [MessageController::class, 'sendMessage'])->name('messages.send');


    // Ver respuestas
   // Route::get('/respuestas', [MessageController::class, 'showResponses'])->name('messages.responses');
   // Route::post('/messages/reply', [MessageController::class, 'reply'])->name('messages.reply');

   // Perfil de usuario
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Rutas CRUD usuarios — solo para usuarios con permiso 'manage-users'
    Route::resource('users', UserController::class)->middleware('can:manage-users');

    Route::get('/roles/index', [\App\Http\Controllers\RolController::class, 'index'] )->name('roles.index');//->middleware('can:rol-write');//ruta de lectur
    Route::post('/roles/store',  [\App\Http\Controllers\RolController::class, 'store'] )->name('roles.store');//->middleware('can:rol-write');//escritura
    Route::post('/roles/destroy', [\App\Http\Controllers\RolController::class, 'destroy'] )->name('roles.destroy');//->middleware('can:rol-write');
    Route::post('/roles/addPermissions', [\App\Http\Controllers\RolController::class, 'addPermissions'] )->name('roles.addPermissions');//->middleware('can:rol-write');
    Route::get('/roles/getPermissions', [\App\Http\Controllers\RolController::class, 'getPermissions'] )->name('roles.getPermissions');//->middleware('can:rol-write');

    Route::get('/permission/index', [\App\Http\Controllers\PermissionController::class, 'index'])->name('permission.index'); // lectura
    Route::post('/permission/store', [\App\Http\Controllers\PermissionController::class, 'store'])->name('permission.store'); // creación/actualización
    Route::post('/permission/destroy', [\App\Http\Controllers\PermissionController::class, 'destroy'])->name('permission.destroy'); // eliminación
    Route::post('/permission/addRoles', [\App\Http\Controllers\PermissionController::class, 'addRoles'])->name('permission.addRoles'); // asignar roles a permiso
    Route::get('/permission/getRoles', [\App\Http\Controllers\PermissionController::class, 'getRoles'])->name('permission.getRoles'); // obtener roles del permiso

});



// Redirigir raíz a login
Route::get('/', function () {
    return redirect()->route('login');
});
