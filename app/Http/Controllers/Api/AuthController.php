<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * تسجيل الدخول للطالب وإصدار Token
     */
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required', // يمكن أن يكون رقم الهاتف أو اسم المستخدم
            'password' => 'required',
        ]);

        // البحث عن المستخدم (سواء باسم المستخدم أو بالبريد أو برقم الهاتف)
        $user = User::where('username', $request->username)
                    ->orWhere('email', $request->username)
                    ->orWhere('phone', $request->username)
                    ->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'بيانات الدخول غير صحيحة',
            ], 401);
        }

        // إنشاء التوكن
        $token = $user->createToken('mobile-app')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'تم تسجيل الدخول بنجاح',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'role' => $user->getRoleNames()->first(),
            ]
        ]);
    }

    /**
     * تسجيل الخروج وإلغاء التوكن
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'تم تسجيل الخروج بنجاح'
        ]);
    }
}
