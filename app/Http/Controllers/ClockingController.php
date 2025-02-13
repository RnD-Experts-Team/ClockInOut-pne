<?php

namespace App\Http\Controllers;

use App\Models\Clocking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ClockingController extends Controller
{


    public function index()
    {
        // Retrieve the latest clocking record for the authenticated user
        $clocking = Clocking::where('user_id', Auth::id())
            ->whereNull('clock_out')
            ->where('is_clocked_in', true)
            ->first();

        // Pass the clocking status to the view
        return view('clocking', compact('clocking'));
    }



    public function clockIn(Request $request)
    {
        $request->validate([
            'miles_in' => 'required|integer',
            'image_in' => 'required|image|mimes:jpg,png,jpeg|max:2048',
        ]);

        // Store the clock-in image
        $imagePath = $request->file('image_in')->store('clocking_images', 'public');

        // Create a new clocking record
        Clocking::create([
            'user_id' => Auth::id(),
            'clock_in' => now(),
            'miles_in' => $request->miles_in,
            'image_in' => $imagePath,
            'is_clocked_in' => true,
        ]);

        return back()->with('success', 'Clock-in successful.');
    }

    public function clockOut(Request $request)
    {
        $request->validate([
            'miles_out' => 'required|integer',
            'image_out' => 'required|image|mimes:jpg,png,jpeg|max:2048',
        ]);

        // Store the clock-out image
        $imagePath = $request->file('image_out')->store('clocking_images', 'public');

        // Update the existing clocking record
        $clocking = Clocking::where('user_id', Auth::id())
            ->whereNull('clock_out')
            ->where('is_clocked_in', true)
            ->firstOrFail();

        $clocking->update([
            'clock_out' => now(),
            'miles_out' => $request->miles_out,
            'image_out' => $imagePath,
            'is_clocked_in' => false,
        ]);

        return back()->with('success', 'Clock-out successful.');
    }
}
