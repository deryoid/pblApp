<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\Admin;
use App\Http\Middleware\Evaluator;
use App\Http\Middleware\Mahasiswa;  

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function ($middleware) {
        // daftar global kalau perlu
        // $middleware->append(SomeGlobalMiddleware::class);

        // daftar alias (kayak $routeMiddleware lama)
        $middleware->alias([
            'admin' => Admin::class,
            'evaluator' => Evaluator::class,
            'mahasiswa' => Mahasiswa::class,
        ]);
    })
    ->withExceptions(function ($exceptions) {
        //
    })
    ->create();