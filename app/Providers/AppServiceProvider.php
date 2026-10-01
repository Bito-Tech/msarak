<?php

namespace App\Providers;

use App\Models\AssessmentSession;
use App\Models\AssessmentVersion;
use App\Models\Result;
use Database\Seeders\AssessmentQuestionBankV13Seeder;
use App\Policies\AssessmentSessionPolicy;
use App\Policies\ResultPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Throwable;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::policy(AssessmentSession::class, AssessmentSessionPolicy::class);
        Gate::policy(Result::class, ResultPolicy::class);

        if (! $this->app->runningInConsole() && $this->app->environment('production')) {
            try {
                $v13IsActive = AssessmentVersion::query()
                    ->where('version_number', 13)
                    ->where('status', 'active')
                    ->whereNotNull('published_at')
                    ->exists();

                if (! $v13IsActive) {
                    (new AssessmentQuestionBankV13Seeder)->run();
                }
            } catch (Throwable $exception) {
                report($exception);
            }
        }
    }
}
