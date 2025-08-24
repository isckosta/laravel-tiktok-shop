<?php

namespace TikTokShop\Auth;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use TikTokShop\Services\AuthorizationService;

class AuthorizationController extends Controller
{
    public function __construct(private AuthorizationService $authService) {}

    public function redirect()
    {
        $url = $this->authService->generateAuthorizationUrl('default');
        return redirect()->away($url);
    }

    public function callback(Request $request)
    {
        $authCode = (string) $request->query('code');
        $state    = (string) $request->query('state');

        abort_unless($authCode, 400, 'Missing auth_code.');

        $result = $this->authService->handleCallback($authCode, $state);

        return response()->json($result);
    }
}
