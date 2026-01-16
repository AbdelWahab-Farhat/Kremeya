<?php
namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Resources\CustomerResource;
use App\Http\Resources\EmployeeResource;
use App\Http\Resources\UserResource;
use App\Http\Traits\ApiResponse;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    use ApiResponse;

    public function __construct(private AuthService $authService)
    {}

    public function login(LoginRequest $request)
    {
        $data = $this->authService->login($request->validated());

        /** @var User $user */
        $user = $data['user'];

        return $this->success(
            $this->buildAuthPayload($user, $data['token'])
        );
    }

    public function me(Request $request)
    {
        /** @var User $user */
        $user = $request->user();
        return $this->success(
            $this->buildAuthPayload($user) // no token here
        );
    }

    private function buildAuthPayload(User $user, ?string $token = null): array
    {
        $payload = [
            'role' => $user->getRoleNames()->first(),
            'user' => new UserResource($user),
        ];

        if ($token) {
            $payload['token']      = $token;
            $payload['token_type'] = 'Bearer';
        }

        if ($user->hasRole('customer')) {
            $payload['profile'] = new CustomerResource(
                $user->customer()->with(['city', 'region', 'user'])->first()
            );
        } elseif ($user->hasRole('employee')) {
            $payload['profile'] = new EmployeeResource(
                $user->employee()->with(['user'])->first()
            );
        }

        return $payload;
    }

    public function logout(Request $request)
    {
        /** @var User $user */
        $user = $request->user();

        // Logout current token only
        $user->currentAccessToken()?->delete();

        return $this->success([
            'message' => 'Logged out successfully',
        ]);
    }

    public function webLogin(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (\Illuminate\Support\Facades\Auth::attempt($credentials)) {
            $request->session()->regenerate();

            return redirect()->intended(route('admin.dashboard'));
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function webLogout(Request $request)
    {
        \Illuminate\Support\Facades\Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
