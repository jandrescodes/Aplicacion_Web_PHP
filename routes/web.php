<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeesController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PositionsController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UsersController;
use Core\Container;
use Core\Router;

return static function (Router $router, Container $container): void {
    $home      = $container->resolve(HomeController::class);
    $auth      = $container->resolve(AuthController::class);
    $dashboard = $container->resolve(DashboardController::class);
    $employees = $container->resolve(EmployeesController::class);
    $positions = $container->resolve(PositionsController::class);
    $users     = $container->resolve(UsersController::class);
    $profile   = $container->resolve(ProfileController::class);

    $router->get('/',          [$dashboard, 'index']);
    $router->get('/index',     [$home, 'alias']);
    $router->get('/home',      [$home, 'alias']);
    $router->get('/dashboard', [$home, 'alias']);

    $router->get('/login',   [$auth, 'showLogin']);
    $router->post('/login',  [$auth, 'login']);
    $router->post('/cerrar', [$auth, 'logout']);

    $router->get('/empleados',                    [$employees, 'index']);
    $router->get('/empleados-crear',              [$employees, 'createForm']);
    $router->post('/empleados-crear',             [$employees, 'create']);
    $router->get('/empleados-editar',             [$employees, 'editForm']);
    $router->post('/empleados-editar',            [$employees, 'edit']);
    $router->post('/empleados-eliminar',          [$employees, 'delete']);
    $router->get('/empleados-carta-recomendacion', [$employees, 'recommendation']);

    $router->get('/puestos',         [$positions, 'index']);
    $router->get('/puestos-crear',   [$positions, 'createForm']);
    $router->post('/puestos-crear',  [$positions, 'create']);
    $router->get('/puestos-editar',  [$positions, 'editForm']);
    $router->post('/puestos-editar', [$positions, 'edit']);
    $router->post('/puestos-eliminar', [$positions, 'delete']);

    $router->get('/perfil',               [$profile, 'show']);
    $router->post('/perfil-datos',        [$profile, 'updateData']);
    $router->post('/perfil-contrasena',   [$profile, 'changePassword']);

    $router->get('/usuarios',         [$users, 'index']);
    $router->get('/usuarios-crear',   [$users, 'createForm']);
    $router->post('/usuarios-crear',  [$users, 'create']);
    $router->get('/usuarios-editar',  [$users, 'editForm']);
    $router->post('/usuarios-editar', [$users, 'edit']);
    $router->post('/usuarios-eliminar', [$users, 'delete']);
};
