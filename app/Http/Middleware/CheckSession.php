<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class CheckSession
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!session()->has('user')) {
            return redirect('/login');
        }

        $user = session('user');

        if (!data_get($user, 'role') && data_get($user, 'username')) {
            $dbUser = DB::table('login')
                ->where('username', data_get($user, 'username'))
                ->first();

            if ($dbUser) {
                $role = $this->resolveRole($dbUser->username, $dbUser->role ?? null);

                session([
                    'user' => [
                        'id' => $dbUser->id,
                        'username' => $dbUser->username,
                        'role' => $role,
                        'name' => $this->resolveDisplayName($dbUser->username, $role),
                    ],
                ]);
            }
        }

        return $next($request);
    }

    private function resolveDisplayName(string $username, string $role): string
    {
        return match ($role) {
            'kepala_sekolah' => 'Kepala Sekolah',
            'admin' => $username === 'admin' ? 'Administrator' : ucfirst($username),
            default => ucfirst($username),
        };
    }

    private function resolveRole(string $username, ?string $role): string
    {
        if (in_array(strtolower($username), ['kepsek', 'kepala_sekolah'], true)) {
            return 'kepala_sekolah';
        }

        return $role === 'kepala_sekolah' ? 'kepala_sekolah' : 'admin';
    }
}
