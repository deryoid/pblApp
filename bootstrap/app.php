<?php

use App\Console\Commands\AutoTandaiTidakAdaTanggapan;
use App\Http\Middleware\Admin;
use App\Http\Middleware\Evaluator;
use App\Http\Middleware\Mahasiswa;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withCommands([
        AutoTandaiTidakAdaTanggapan::class,
    ])
    ->withMiddleware(function ($middleware) {
        $middleware->trustProxies(at: '*');

        // daftar global kalau perlu
        // $middleware->append(SomeGlobalMiddleware::class);

        // Configure web middleware group
        $middleware->web(append: [
            // Add any web-specific middleware here if needed
        ]);

        // daftar alias (kayak $routeMiddleware lama)
        $middleware->alias([
            'admin' => Admin::class,
            'evaluator' => Evaluator::class,
            'mahasiswa' => Mahasiswa::class,
            'profile.complete' => \App\Http\Middleware\EnsureProfileIsCompleted::class,
        ]);
    })
    ->withExceptions(function ($exceptions) {
        //
    })
    ->create();
