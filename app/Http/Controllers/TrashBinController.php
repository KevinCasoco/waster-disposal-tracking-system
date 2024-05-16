<?php

namespace App\Http\Controllers;

use App\Models\TrashBin;
use Illuminate\Http\Request;

class TrashBinController extends Controller
{
    public function index()
    {
        $trashBins = TrashBin::all();
        return view('admin-trash-bin', compact('trashBins'));
    }

    // dynamically add the trash bin location for admin account
    public function create(Request $request)
    {
        // Validate request
        $request->validate([
            'trash_bin_location' => 'required',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        // Create new trash bin
        $trashBin = new TrashBin();
        $trashBin->trash_bin_location = $request->trash_bin_location;
        $trashBin->latitude = $request->latitude;
        $trashBin->longitude = $request->longitude;
        $trashBin->save();

        return redirect()->back()->with('success', 'Trash bin added successfully.');
    }
}
