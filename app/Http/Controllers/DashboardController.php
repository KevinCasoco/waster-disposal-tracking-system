<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    // public function countUsersByRole() {
    //     $countAdmins = User::where('role', 'admin')->count();
    //     $countCollector = User::where('role', 'collector')->count();
    //     $countResidents = User::where('role', 'residents')->count();
    //     $totalUser = User::count();

    //     return view('dashboard',
    //         ['countAdmins' => $countAdmins,
    //         'countCollector' => $countCollector,
    //         'countResidents' => $countResidents,
    //         'totalUser' => $totalUser,
    //     ]);
    // }
    public function countUsersByRole()
{
    $chart = User::all();

    $countAdmins = User::where('role', 'admin')->count();
    $countCollector = User::where('role', 'collector')->count();
    $countResidents = User::where('role', 'residents')->count();
    $totalUser = User::count();

    return view('dashboard', compact('chart', 'countAdmins', 'countCollector', 'countResidents', 'totalUser'));
}
}
