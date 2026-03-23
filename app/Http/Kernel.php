<?php

namespace App\Http;

use App\Http\Middleware\CheckOnboarding;
use App\Http\Middleware\CheckPermission;
use App\Http\Middleware\CheckPlanFeature;
use App\Http\Middleware\CheckPlanLimit;
use App\Http\Middleware\CheckRole;
use App\Http\Middleware\DetectSuspiciousActivity;
use App\Http\Middleware\EncryptCookies;
use App\Http\Middleware\EnsureChurchContext;
use App\Http\Middleware\HandleTabRequest;
use App\Http\Middleware\LogPageVisit;
use App\Http\Middleware\NoCacheForAuth;
use App\Http\Middleware\RedirectIfAuthenticated;
use App\Http\Middleware\SanitizeInput;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\SetLocale;
use App\Http\Middleware\SuperAdmin;
use App\Http\Middleware\ThrottleLogin;
use App\Http\Middleware\TrustProxies;
use App\Http\Middleware\ValidateTelegramMiniApp;
use App\Http\Middleware\ValidateTelegramWebhook;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Auth\Middleware\AuthenticateWithBasicAuth;
use Illuminate\Auth\Middleware\Authorize;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;
use Illuminate\Auth\Middleware\RequirePassword;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Foundation\Http\Kernel as HttpKernel;
use Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull;
use Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests;
use Illuminate\Foundation\Http\Middleware\ValidatePostSize;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Http\Middleware\HandleCors;
use Illuminate\Http\Middleware\SetCacheHeaders;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Routing\Middleware\ValidateSignature;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class Kernel extends HttpKernel
{
    protected $middleware = [
        TrustProxies::class,
        HandleCors::class,
        SecurityHeaders::class,
        DetectSuspiciousActivity::class,
        ValidatePostSize::class,
        ConvertEmptyStringsToNull::class,
    ];

    protected $middlewareGroups = [
        'web' => [
            EncryptCookies::class,
            AddQueuedCookiesToResponse::class,
            StartSession::class,
            ShareErrorsFromSession::class,
            VerifyCsrfToken::class,
            SetLocale::class,
            SubstituteBindings::class,
            SanitizeInput::class,
            HandleTabRequest::class,
            NoCacheForAuth::class,
            LogPageVisit::class,
        ],

        'api' => [
            ThrottleRequests::class.':api',
            SubstituteBindings::class,
        ],
    ];

    protected $middlewareAliases = [
        'auth' => Authenticate::class,
        'auth.basic' => AuthenticateWithBasicAuth::class,
        'auth.session' => AuthenticateSession::class,
        'cache.headers' => SetCacheHeaders::class,
        'can' => Authorize::class,
        'guest' => RedirectIfAuthenticated::class,
        'password.confirm' => RequirePassword::class,
        'precognitive' => HandlePrecognitiveRequests::class,
        'signed' => ValidateSignature::class,
        'throttle' => ThrottleRequests::class,
        'verified' => EnsureEmailIsVerified::class,
        'church' => EnsureChurchContext::class,
        'role' => CheckRole::class,
        'throttle.login' => ThrottleLogin::class,
        'super_admin' => SuperAdmin::class,
        'onboarding' => CheckOnboarding::class,
        'permission' => CheckPermission::class,
        'telegram.webhook' => ValidateTelegramWebhook::class,
        'tma.validate' => ValidateTelegramMiniApp::class,
        'plan.limit' => CheckPlanLimit::class,
        'plan.feature' => CheckPlanFeature::class,
    ];
}
