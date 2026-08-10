<?php

namespace App\Providers;

use App\Models\SchoolSetting;
use App\Services\AssetNotificationService;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(SchoolSetting::class, fn () => Schema::hasTable('school_settings') ? (SchoolSetting::first() ?? SchoolSetting::fallback()) : SchoolSetting::fallback());
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();
        if ($this->app->bound('request')) {
            config(['filesystems.disks.public.url' => request()->getBaseUrl().'/media']);
        }
        View::composer('*', fn ($view) => $view->with('schoolSetting', app(SchoolSetting::class)));
        View::composer('partials.topbar', function ($view) {
            if (!$this->app->runningUnitTests()) {
                Cache::remember('notifications:overdue-synced', now()->addMinute(), function () {
                    app(AssetNotificationService::class)->syncOverdue();
                    return true;
                });
            }
            $user = auth()->user();
            $view->with('topbarNotifications', $user->notifications()->latest()->limit(7)->get())
                ->with('topbarUnreadCount', $user->unreadNotifications()->count());
        });
    }
}
