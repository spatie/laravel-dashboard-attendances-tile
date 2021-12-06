<?php

namespace Spatie\AttendancesTile;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class AttendancesTileServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                FetchAttendancesCommand::class,
            ]);
        }

        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/dashboard-attendances-tile'),
        ], 'dashboard-attendances-tile-views');

        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'dashboard-attendances-tile');

        Livewire::component('attendances-tile', AttendancesTileComponent::class);
    }
}
