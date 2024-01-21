<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use App\Models\User;
use App\Notifications\NewNotification;
use App\Notifications\WasteCollectionSchedule as NotificationsWasteCollectionSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class WasteCollectionSchedule extends Controller
{
    public function sendNotification()
    {
        $users = User::where('status', 'active')->get();
        $notification = new NewNotification();

        foreach ($users as $user) {
        // Access the schedules relationship
        $schedules = $user->schedules;

        foreach ($schedules as $schedule) {
            // Access schedule properties, for example:
            $start = $schedule->start;
            $time = $schedule->time;
        }

            // Notify the user
            $user->notify($notification);
        }

        return view('schedule');
    }

    public function showCollectorSchedule()
    {
        return view('collector-schedule');
    }

    public function showAdminSchedule()
    {
        return view('collector');
    }

    public function showNotificationForm()
    {
        return view('schedule');
    }

    public function collector_showNotificationForm()
    {
        return redirect()->route('collector-schedule')->with('message', 'Email was sent successfully');
    }

    // without 1 day validation before sending to users
    // public function admin_sendNotification()
    // {
    //     $users = User::where('status', 'active')->get();
    //     // $notification = new NotificationsWasteCollectionSchedule();

    //     // Access all schedules
    //     $schedules = Schedule::all();

    //     foreach ($users as $user) {
    //         // Notify the user with schedule information
    //         $user->notify(new NotificationsWasteCollectionSchedule($schedules));
    //     }

    //     return redirect()->route('schedule')->with('message', 'Email was sent successfully');
    // }

    // with 1 day before the exact date before sending to email
    // public function admin_sendNotification()
    // {
    //     // Get the current date
    //     $currentDate = Carbon::now();

    //     // Get all active users
    //     $users = User::where('status', 'active')->get();

    //     // Access all schedules for which the scheduled date is one day before the current date
    //     $schedules = Schedule::whereDate('start', $currentDate->copy()->addDay()->toDateString())->get();

    //     foreach ($users as $user) {
    //         // Notify the user with schedule information
    //         $user->notify(new NotificationsWasteCollectionSchedule($schedules));
    //     }

    //     return redirect()->route('schedule')->with('message', 'Email was sent successfully');
    // }

    // with 1 day before the exact date before sending to email
    // residents matches the location to dropdown
    public function admin_sendNotification()
    {
        // Get the current date
        $currentDate = Carbon::now();

        // Get all active users
        $users = User::where('status', 'active')->get();

        foreach ($users as $user) {
            // Get the user's location (assuming the address contains location information)
            $userLocation = $user->location; // Update this line based on your actual structure

            // Access schedules with the same location and scheduled date one day before the current date
            $schedules = Schedule::whereDate('start', $currentDate->copy()->addDay()->toDateString())
                                ->where('location', $userLocation)
                                ->get();

            if ($schedules->isNotEmpty()) {
                // Notify the user with schedule information
                $user->notify(new NotificationsWasteCollectionSchedule($schedules));
            }
        }

        return redirect()->route('schedule')->with('message', 'Email was sent successfully');
    }

    // without 1 day validation before sending to users
    // public function collector_sendNotification()
    // {
    //     $users = User::where('status', 'active')->get();
    //     // $notification = new NotificationsWasteCollectionSchedule();

    //     // Access all schedules
    //     $schedules = Schedule::all();

    //     foreach ($users as $user) {
    //         // Notify the user with schedule information
    //         $user->notify(new NotificationsWasteCollectionSchedule($schedules));
    //     }

    //     return redirect()->route('collector-schedule')->with('message', 'Email was sent successfully');
    // }

    // with 1 day before the exact date before sending to email
    // public function collector_sendNotification()
    // {
    //      // Get the current date
    //      $currentDate = Carbon::now();

    //      // Get all active users
    //      $users = User::where('status', 'active')->get();

    //      // Access all schedules for which the scheduled date is one day before the current date
    //      $schedules = Schedule::whereDate('start', $currentDate->copy()->addDay()->toDateString())->get();

    //      foreach ($users as $user) {
    //          // Notify the user with schedule information
    //          $user->notify(new NotificationsWasteCollectionSchedule($schedules));
    //      }

    //      return redirect()->route('collector-schedule')->with('message', 'Email was sent successfully');
    // }

    // with 1 day before the exact date before sending to email
    // residents matches the location to dropdown
    public function collector_sendNotification()
    {
        // Get the current date
        $currentDate = Carbon::now();

        // Get all active users
        $users = User::where('status', 'active')->get();

        foreach ($users as $user) {
            // Get the user's location (assuming the address contains location information)
            $userLocation = $user->location; // Update this line based on your actual structure

            // Access schedules with the same location and scheduled date one day before the current date
            $schedules = Schedule::whereDate('start', $currentDate->copy()->addDay()->toDateString())
                                ->where('location', $userLocation)
                                ->get();

            if ($schedules->isNotEmpty()) {
                // Notify the user with schedule information
                $user->notify(new NotificationsWasteCollectionSchedule($schedules));
            }
        }

        return redirect()->route('collector-schedule')->with('message', 'Email was sent successfully');
    }

    public function schedule()
    {
        $events = array();
        $schedules = Schedule::all();
        foreach($schedules as $schedule) {
            $color = null;
            if ($schedule->title == 'Test') {
                $color = '#924ACE';
                // return $color;
            }

            if ($schedule->title == 'Test 1') {
                $color = '#68801A';
                // return $color;
            }

            $events[] = [
                'id' => $schedule->id,
                'title' => $schedule->title,
                'start' => $schedule->start_date,
                'time' => $schedule->time,
                'color' => $color
            ];
        }

        return view('calendar', ['events' => $events]);
    }

    public function store(Request $request)
    {
        $userId = Auth::user()->id;

        $schedule = new Schedule([
            'location' =>$request->location,
            'start_date' =>$request->start_date,
            'time' =>$request->time,
        ]);

        $user = User::find($userId);

        $user->schedules()->save($schedule);

        return response()->json($schedule);
    }

    public function update(Request $request, $id)
    {
        $schedules = Schedule::find($id);
            if(!$schedules) {
                return response()->json([
                    'error' => 'Unable to locate the ID'
                ], 404);
            }

        $schedules->update([
            'start_date' =>$request->start_date,
            'time' =>$request->time,
        ]);

        return response()->json('Event updated');
    }

    public function delete($id)
    {
        $schedules = Schedule::find($id);
            if(!$schedules) {
                return response()->json([
                    'error' => 'Unable to locate the ID'
                ], 404);
            }

        $schedules->delete();
        return $id;
        // return response()->json('Event deleted');
    }
}
