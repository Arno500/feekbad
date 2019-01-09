<?php

namespace App\Http\Middleware;

use Auth0\Login\Contract\Auth0UserRepository;
use Auth0\SDK\Exception\CoreException;
use Auth0\SDK\Exception\InvalidTokenException;
use Closure;
use Debugbar;
use \App\Repositories\CustomUserRepository;

class CheckJWT
{
    protected $userRepository;

    /**
     * CheckJWT constructor.
     *
     * @param Auth0UserRepository $userRepository
     */
    public function __construct(CustomUserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $auth0 = \App::make('auth0');

        $accessToken = $request->bearerToken();
        try {
            Debugbar::startMeasure('decodeJWT','Decoding JWT');
            $tokenInfo = $auth0->decodeJWT($accessToken);
            Debugbar::stopMeasure('decodeJWT');
            Debugbar::startMeasure('getJWTData','Retrieving user data');
            $user = $this->userRepository->getUserByDecodedJWT($tokenInfo);
            if (!$user) {
                return response()->json(["message" => "Unauthorized user"], 401);
            }
            \Auth::login($user);
            Debugbar::stopMeasure('getJWTData');

        } catch (CoreException $e) {
            return response()->json(["message" => $e->getMessage()], 401);
        } catch (InvalidTokenException $e) {
            return response()->json(["message" => $e->getMessage()], 401);
        }

        return $next($request);
    }
}
