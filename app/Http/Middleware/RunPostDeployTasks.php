<?php

namespace App\Http\Middleware;

use App\Support\Deployment;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** See App\Support\Deployment: runs new migrations once after a deploy, without SSH. */
class RunPostDeployTasks
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Deployment::isPending() && ! Deployment::run()) {
            return response('Kustore is updating. Please try again in a minute.', 503, ['Retry-After' => '60', 'Content-Type' => 'text/plain; charset=UTF-8']);
        }

        return $next($request);
    }
}
