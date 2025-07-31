<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginDashboardRequest;
use App\Http\Resources\Templates\WithDataResource;
use App\Http\Resources\Templates\WithoutDataResource;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class LoginController extends Controller
{
    public function login(LoginDashboardRequest $request)
    {
        $credential = $request->validated();

        // check auth
        $loginSuccess = Auth::attempt([
            'email' => $credential['email'],
            'password' => $credential['password']
        ]) || Auth::attempt([
            'username' => $credential['email'],
            'password' => $credential['password']
        ]);
        if (!$loginSuccess) {
            Log::info("| Auth Berkas | - Login failed for email/username: {$credential['email']}, Invalid credentials");
            return response()->json(
                new WithoutDataResource(
                    Response::HTTP_UNAUTHORIZED,
                    'Login Gagal',
                    'Password atau username/email yang anda masukkan tidak valid, silahkan periksa kembali dan pastikan akun anda sudah terdaftar.'
                ),
                Response::HTTP_UNAUTHORIZED
            );
        }

        $user = Auth::user();

        $user->update(['last_login' => now()]);

        // login success
        Log::info("| Auth Berkas | - Login success for email: {$credential['email']}, at {$user->last_login}");

        $token = $user->createToken('create_token_' . Str::uuid())->plainTextToken;

        return response()->json(
            new WithDataResource(
                Response::HTTP_OK,
                'Login Berhasil',
                'Selamat datang, ' . $user->name . '!',
                [
                    'user' => $user,
                    'token' => $token
                ]
            ),
            Response::HTTP_OK
        );
    }

    public function logout()
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(
                new WithoutDataResource(
                    Response::HTTP_UNAUTHORIZED,
                    'Logout Gagal',
                    'Anda tidak memiliki sesi login yang aktif.'
                ),
                Response::HTTP_UNAUTHORIZED
            );
        }

        // Hapus token akses saat ini jika ada
        if (method_exists($user->currentAccessToken(), 'delete')) {
            $user->currentAccessToken()->delete();
        }

        Auth::guard('web')->logout();

        Log::info("| Auth Berkas | - Logout success for email: " . $user->email);

        return response()->json(
            new WithoutDataResource(
                Response::HTTP_OK,
                'Logout Berhasil',
                'Anda berhasil melakukan logout.'
            ),
            Response::HTTP_OK
        );
    }
}
