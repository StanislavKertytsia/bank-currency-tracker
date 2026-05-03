<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function show(Request $request): UserResource
    {
        return new UserResource($request->user());
    }

    public function update(Request $request): UserResource
    {
        $user = $request->user();

        $data = $request->validate([
            'name'     => ['sometimes', 'string', 'max:255'],
            'email'    => ['sometimes', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['sometimes', 'confirmed', Password::min(8)],
        ]);

        $user->update($data);

        return new UserResource($user->fresh());
    }

    public function updateNotifications(Request $request): UserResource
    {
        $data = $request->validate([
            'notification_enabled' => ['required', 'boolean'],
        ]);

        $request->user()->update($data);

        return new UserResource($request->user()->fresh());
    }
}
