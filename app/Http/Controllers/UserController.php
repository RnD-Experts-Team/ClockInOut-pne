<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of the users.
     */
    public function index()
    {
        // Retrieve all users from the database.
        $users = User::all();

        // Return the view with users.
        return view('admin.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        return view('admin.users.create');
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request)
{
    // Validate the form data, including hourly_pay.
    $request->validate([
        'name'       => 'required|string|max:255',
        'email'      => 'required|email|unique:users,email',
        'password'   => 'required|string|min:8|confirmed',
        'role'       => 'required|string|in:admin,user',
        'hourly_pay' => 'required|numeric|min:0', // Validate hourly pay as a positive number.
    ]);

    // Create the user.
    User::create([
        'name'       => $request->name,
        'email'      => $request->email,
        'password'   => Hash::make($request->password),
        'role'       => $request->role,
        'hourly_pay' => $request->hourly_pay,
    ]);

    return redirect()->route('users.index')->with('success', 'User created successfully.');
}


    /**
     * Show the form for editing the specified user.
     */
    public function edit($id)
    {
        $user = User::findOrFail($id);

        return view('admin.users.edit', compact('user'));
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
    
        // Validate the form data, including hourly_pay.
        $request->validate([
            'name'       => 'required|string|max:255',
            'email'      => "required|email|unique:users,email,{$id}",
            'password'   => 'nullable|string|min:8|confirmed',
            'role'       => 'required|string|in:admin,user',
            'hourly_pay' => 'required|numeric|min:0',
        ]);
    
        $user->name       = $request->name;
        $user->email      = $request->email;
        $user->role       = $request->role;
        $user->hourly_pay = $request->hourly_pay;
    
        // Update password only if provided.
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }
    
        $user->save();
    
        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }
    



   public function destroy($id)
{
    $user = User::findOrFail($id);
    $user->delete();

    return redirect()->route('users.index')->with('success', 'User deleted successfully.');
}

}
