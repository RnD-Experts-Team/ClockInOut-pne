<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Api\Admin\UserService;
use App\Http\Requests\Api\Admin\StoreUserRequest;
use App\Http\Requests\Api\Admin\UpdateUserRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{

    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }


    public function index()
    {
        try {

            $users = $this->userService->index();

            return response()->json([
                'success' => true,
                'data' => $users
            ]);

        } catch (\Exception $e) {

            Log::error($e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch users'
            ], 500);
        }
    }



    public function store(StoreUserRequest $request)
    {
        try {

            $user = $this->userService->store($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'User created successfully.',
                'data' => $user
            ], 201);

        } catch (\Exception $e) {

            Log::error($e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to create user'
            ], 500);
        }
    }



    public function show(User $user)
    {
        try {

            return response()->json([
                'success' => true,
                'data' => $user
            ]);

        } catch (\Exception $e) {

            Log::error($e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch user'
            ], 500);
        }
    }



    public function update(UpdateUserRequest $request, User $user)
    {
        try {

            $user = $this->userService->update(
                $request->validated(),
                $request,
                $user
            );

            return response()->json([
                'success' => true,
                'message' => 'User updated successfully.',
                'data' => $user
            ]);

        } catch (\Exception $e) {

            Log::error($e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to update user'
            ], 500);
        }
    }



    public function destroy(User $user)
    {
        try {

            $this->userService->destroy($user);

            return response()->json([
                'success' => true,
                'message' => 'User deleted successfully.'
            ]);

        } catch (\Exception $e) {

            Log::error($e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete user'
            ], 500);
        }
    }

}