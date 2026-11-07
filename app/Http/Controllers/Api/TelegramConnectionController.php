<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TelegramConnectionController extends Controller
{
    /**
     * Generate a connection token for the currently authenticated user.
     * This will be used in the web frontend to generate a deep link.
     */
    public function generateToken(Request $request)
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        // Generate a random 32-character token
        $token = Str::random(32);

        // Store in cache for 15 minutes, mapping token -> user ID
        Cache::put('telegram_connect_token_'.$token, $user->id, now()->addMinutes(15));

        $botUsername = config('services.telegram.bot_username', env('TELEGRAM_BOT_USERNAME', 'cevltBot'));

        return response()->json([
            'token' => $token,
            'bot_username' => $botUsername,
            'deep_link' => 'https://t.me/'.$botUsername.'?start='.$token,
        ]);
    }

    /**
     * Connect the Telegram ID to the User account using the provided token.
     * This endpoint is called by the Telegram Bot.
     */
    public function connect(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'telegram_id' => 'required|string',
        ]);

        $token = $request->input('token');
        $telegramId = $request->input('telegram_id');

        $userId = Cache::get('telegram_connect_token_'.$token);

        if (! $userId) {
            return response()->json(['error' => 'Invalid or expired token.'], 400);
        }

        $user = User::find($userId);

        if (! $user) {
            return response()->json(['error' => 'User not found.'], 404);
        }

        $user->telegram_id = $telegramId;
        $user->save();

        // Optional: Remove token from cache after successful use
        Cache::forget('telegram_connect_token_'.$token);

        $user->load('resident');

        return response()->json([
            'message' => 'Successfully connected Telegram account.',
            'user' => [
                'id' => $user->id,
                'name' => $user->resident?->full_name ?? $user->username,
                'email' => $user->email,
            ],
        ]);
    }

    /**
     * Login directly from Telegram Bot and link the account
     */
    public function login(Request $request)
    {
        $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
            'telegram_id' => 'required|string',
        ]);

        $loginInput = $request->input('login');
        $password = $request->input('password');
        $telegramId = $request->input('telegram_id');

        // Search user by NIK, email, or phone
        $user = User::where('username', $loginInput)
            ->orWhere('email', $loginInput)
            ->orWhere('phone_number', $loginInput)
            ->first();

        if ($user && Hash::check($password, $user->password)) {
            if ($user->status === 'pending') {
                return response()->json(['error' => 'Akun Anda masih menunggu verifikasi oleh RT/Admin.'], 403);
            }

            if ($user->status === 'rejected' || $user->status === 'inactive') {
                return response()->json(['error' => 'Akun Anda tidak aktif atau telah ditolak.'], 403);
            }

            // Link telegram ID
            $user->telegram_id = $telegramId;
            $user->save();

            $user->load('resident');

            return response()->json([
                'message' => 'Berhasil login dan menghubungkan akun Telegram.',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->resident?->full_name ?? $user->username,
                    'role' => $user->role,
                ],
            ]);
        }

        return response()->json(['error' => 'Data login tidak ditemukan atau password salah.'], 401);
    }

    /**
     * Get user info by telegram ID
     */
    public function getUser($telegramId)
    {
        $user = User::with('resident')->where('telegram_id', $telegramId)->first();

        if (! $user) {
            return response()->json(['error' => 'User not found.'], 404);
        }

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->resident?->full_name ?? $user->username,
                'email' => $user->email,
                'role' => $user->role,
            ],
        ]);
    }

    /**
     * Disconnect / Logout Telegram account from User.
     */
    public function logout(Request $request)
    {
        $request->validate([
            'telegram_id' => 'required|string',
        ]);

        $telegramId = $request->input('telegram_id');
        $user = User::where('telegram_id', $telegramId)->first();

        if (! $user) {
            return response()->json(['error' => 'Akun Telegram Anda belum terhubung dengan akun SIWIL manapun.'], 404);
        }

        $userName = $user->resident?->full_name ?? $user->name ?? $user->username;
        $user->telegram_id = null;
        $user->save();

        return response()->json([
            'message' => "Berhasil logout. Akun {$userName} telah diputuskan dari bot Telegram ini.",
        ]);
    }
}
