<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use App\Http\Requests\StoreScheduleRequest;
use App\Http\Requests\UpdateScheduleRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ScheduleController extends Controller
{
    public function index()
    {
        return view('schedule');
    }

    public function index_collector()
    {
        return view('collector-schedule');
    }

    public function index_schedule(Request $request)
    {
        $dataQuery = Schedule::query();
        $users = User::whereNotNull('plate_no')->get();
        $locations = User::where('role', 'residents')->pluck('location', 'id')->unique();

        if ($request->has('start_date') && $request->has('end_date')) {
            $start_date = $request->start_date;
            $end_date = $request->end_date;

            $dataQuery->whereDate('start', '>=', $start_date)
                      ->whereDate('start', '<=', $end_date);
        }

        $data = $dataQuery->get();

        return view('schedule-list', compact('data', 'users', 'locations'));
    }

    public function index_collector_schedule(Request $request)
    {
        $dataQuery = Schedule::query();
        $users = User::whereNotNull('plate_no')->get();
        $locations = User::where('role', 'residents')->pluck('location', 'id')->unique();

        if ($request->has('start_date') && $request->has('end_date')) {
            $start_date = $request->start_date;
            $end_date = $request->end_date;

            $dataQuery->whereDate('start', '>=', $start_date)
                      ->whereDate('start', '<=', $end_date);
        }

        $data = $dataQuery->get();

        return view('collector-schedule-list', compact('data', 'users', 'locations'));
    }

    public function add_schedule()
    {
        $data = Schedule::all();

        $users = User::whereNotNull('plate_no')->get();

        // retrieve unique addresses for residents and populate to dropdown
        $locations = User::where('role', 'residents')->pluck('location', 'id')->unique();

        return view('add', compact('data', 'users', 'locations'));
    }

    public function add_schedule_collector()
    {
        $data = Schedule::all();

        $users = User::whereNotNull('plate_no')->get();

        // retrieve unique addresses for residents and populate to dropdown
        $locations = User::where('role', 'residents')->pluck('location', 'id')->unique();

        return view('add-collector', compact('data', 'users', 'locations'));
    }

    public function create(Request $request)
    {
        $userId = Auth::user()->id;

        // Parse the input time using Carbon
        $time = Carbon::parse($request->time)->format('h:i A');

        $schedule = new Schedule([
            'plate_no' =>$request->plate_no,
            'location' =>$request->location,
            'start' =>$request->start,
            'time' =>$time,
        ]);

        $user = User::find($userId);

        $user->schedules()->save($schedule);

        return redirect()->route('schedule')->with('message', 'Schedule added successfully');
    }

    public function update_schedule(Request $request, $id)
    {
        $data = Schedule::find($id);

        if (!$data) {
            return redirect()->route('schedule-list')->with('error', 'Schedule not found');
        }

        // Validate the request
        $request->validate([
            'plate_no' => 'nullable|string',
            'location' => 'nullable|string',
            'start' => 'nullable|string',
            'time' => 'nullable|date_format:H:i',
        ]);

        // Parse the input time using Carbon
        $time = Carbon::parse($request->time)->format('h:i A');

        // Update schedule information
        $data->update([
            'plate_no' => $request->input('plate_no'),
            'location' => $request->input('location'),
            'start' => $request->input('start'),
            'time' => $time, // Use the parsed time value
        ]);

        return redirect()->route('schedule-list')->with('message', 'Schedule updated successfully');
    }

    public function collector_update_schedule(Request $request, $id)
    {
        $data = Schedule::find($id);

        if (!$data) {
            return redirect()->route('collector-schedule-list')->with('error', 'Schedule not found');
        }

        // Validate the request
        $request->validate([
            'plate_no' => 'nullable|string',
            'location' => 'nullable|string',
            'start' => 'nullable|string',
            'time' => 'nullable|date_format:H:i',
        ]);

        // Parse the input time using Carbon
        $time = Carbon::parse($request->time)->format('h:i A');

        // Update schedule information
        $data->update([
            'plate_no' => $request->input('plate_no'),
            'location' => $request->input('location'),
            'start' => $request->input('start'),
            'time' => $time, // Use the parsed time value
        ]);

        return redirect()->route('collector-schedule-list')->with('message', 'Schedule updated successfully');
    }

    public function schedule_destroy($id)
    {
        $schedule_list = Schedule::findOrFail($id);
        $schedule_list->delete();

        return redirect()->route('schedule-list')->with('message', 'Schedule deleted successfully');
    }

    public function collector_schedule_destroy($id)
    {
        $schedule_list = Schedule::findOrFail($id);
        $schedule_list->delete();

        return redirect()->route('collector-schedule-list')->with('message', 'Schedule deleted successfully');
    }

    public function create_collector(Request $request)
    {
        // Get the authenticated user
        $user = Auth::user();

        $userId = Auth::user()->id;

        // Parse the input time using Carbon
        $time = Carbon::parse($request->time)->format('h:i A');

        $schedule = new Schedule([
            // 'plate_no' => $user->plate_no, save the schedule based on the plate no of collector auth
            'plate_no' => $request->plate_no,
            'location' =>$request->location,
            'start' =>$request->start,
            'time' =>$time,
        ]);

        $user = User::find($userId);

        $user->schedules()->save($schedule);

        return redirect()->route('collector-schedule')->with('message', 'Schedule added successfully');
    }


    public function getEvents()
    {
        $schedules = Schedule::all();
        return response()->json($schedules);
    }

    public function deleteEvent($id)
    {
        $schedule = Schedule::findOrFail($id);
        $schedule->delete();

        return response()->json(['message' => 'Event deleted successfully']);
    }

    public function update(Request $request, $id)
    {
        $schedule = Schedule::findOrFail($id);

        $schedule->update([
            'start' => Carbon::parse($request->input('start_date'))->setTimezone('UTC'),
            // 'end' => Carbon::parse($request->input('end_date'))->setTimezone('UTC'),
        ]);

        return response()->json(['message' => 'Event moved successfully']);
    }

    public function resize(Request $request, $id)
    {
        $schedule = Schedule::findOrFail($id);

        $newEndDate = Carbon::parse($request->input('end_date'))->setTimezone('UTC');
        $schedule->update(['end' => $newEndDate]);

        return response()->json(['message' => 'Event resized successfully.']);
    }

    public function search(Request $request)
    {
        $searchKeywords = $request->input('location');

        $matchingEvents = Schedule::where('location', 'like', '%' . $searchKeywords . '%')->get();

        return response()->json($matchingEvents);
    }

    public function restore($id)
    {
        $records = Schedule::withTrashed()->findOrFail($id);

        $records->restore();

        return redirect()->back()->with('success', 'Record restored successfully.');
    }

    public function collector_restore($id)
    {
        $records = Schedule::withTrashed()->findOrFail($id);

        $records->restore();

        return redirect()->back()->with('success', 'Record restored successfully.');
    }

    public function schedule_restore(Request $request)
    {
        // Retrieve soft deleted records
        $deletedRecords = Schedule::onlyTrashed()->get();
        $dataQuery = Schedule::query();
        $locations = User::where('role', 'residents')->pluck('location', 'id')->unique();

        if ($request->has('start_date') && $request->has('end_date')) {
            $start_date = $request->start_date;
            $end_date = $request->end_date;

            $dataQuery->whereDate('start', '>=', $start_date)
                      ->whereDate('start', '<=', $end_date);
        }

        $data = $dataQuery->get();

        return view('schedule-list-restore', compact('deletedRecords', 'locations', 'data'));
    }

    // restore waste collection info
    public function collector_schedule_restore(Request $request)
    {
        // Retrieve soft deleted records
        $deletedRecords = Schedule::onlyTrashed()->get();
        $dataQuery = Schedule::query();
        $locations = User::where('role', 'residents')->pluck('location', 'id')->unique();

        if ($request->has('start_date') && $request->has('end_date')) {
            $start_date = $request->start_date;
            $end_date = $request->end_date;

            $dataQuery->whereDate('start', '>=', $start_date)
                      ->whereDate('start', '<=', $end_date);
        }

        $data = $dataQuery->get();

        return view('collector-schedule-list-restore', compact('deletedRecords', 'locations', 'data'));
    }

}
