<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\UpdateUserRequest;
use Illuminate\Support\Facades\Response;
use App\Http\Requests\ChangePasswordRequest;
use Illuminate\Http\Response as HttpResponse;

class UserController extends Controller
{
    public function getAuthUser()
    {
        return UserResource::make(auth()->user());
    }

    public function updateAuthUser(UpdateUserRequest $request)
    {
        $data = $request->validated();
        User::where('id', auth()->id())->update($data);
        return UserResource::make(request()->user()->refresh());
    }

    public function changePassword(ChangePasswordRequest $request)
    {
        $data = $request->validated();
        User::where('id', auth()->id())->update(["password" => Hash::make($data['password'])]);
        return response()->json(['success' => "Your password has been successfully changed"], HttpResponse::HTTP_OK);
    }
}
