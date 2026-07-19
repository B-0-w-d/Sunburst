<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\ActivationKey;
use App\Models\Member;

class RegisterController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:members,email',
            'password' => 'required|min:8|confirmed',
            'activation_key' => 'required|string',
            'birthday' => 'nullable|date',
            'instrument' => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()->first()], 422);
        }

        $key = ActivationKey::where('key_value', $request->activation_key)
            ->where('starts_at', '<=', now())
            ->where('expires_at', '>=', now())
            ->first();

        if (!$key) {
            return response()->json(['status' => 'error', 'message' => 'Key invalid or expired'], 400);
        }

        // Đảm bảo dữ liệu được truyền từ request vào Model
        $member = Member::create([
            'name'       => $request->name,
            'email'      => $request->email,
            'password'   => $request->password,
            'birthday'   => $request->birthday,
            'instrument' => $request->instrument, // Đã thêm
            'role'       => 'member',
            'status'     => 'active'
        ]);

        Auth::login($member);

        return response()->json(['status' => 'success', 'message' => 'Account created successfully!']);
    }
}
