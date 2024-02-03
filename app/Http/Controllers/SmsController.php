<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Vonage\Client\Credentials\Basic;
use Vonage\Client;

class SmsController extends Controller
{
    // admin button
    public function sms()
    {
        // vonage sms api credentials
        $basic  = new \Vonage\Client\Credentials\Basic("59e7dbad", "2kKXDNaClVCz33u2");
        $client = new \Vonage\Client($basic);

        // Retrieve schedules from the database
        $schedules = Schedule::all();

        // Build the SMS content with schedule information
        $smsContent = 'Waste Collection Schedules:' . PHP_EOL;

        foreach ($schedules as $schedule) {
            $smsContent .= "Admin ID: {$schedule->users_id},\n Location: {$schedule->location},\n Date: {$schedule->start},\n Time: {$schedule->time}" . PHP_EOL;
        }

        // Send SMS with the built content
        $response = $client->sms()->send(
            new \Vonage\SMS\Message\SMS("639307296980", 'WDTS', $smsContent)
        );

        $message = $response->current();

        if ($message->getStatus() == 0) {
            return redirect()->route('schedule')->with('message', 'The message was sent successfully');
        } else {
            echo "The message failed with status: " . $message->getStatus() . "\n";
        }
    }

    // phone number dynamic based on the valid phone number of residents
    // admin button
    // public function sms()
    // {
    //     // Vonage SMS API credentials
    //     $basic  = new \Vonage\Client\Credentials\Basic("59e7dbad", "2kKXDNaClVCz33u2");
    //     $client = new \Vonage\Client($basic);

    //     // Retrieve schedules from the database
    //     $schedules = Schedule::all();

    //     // Iterate through schedules and send SMS to each user
    //     foreach ($schedules as $schedule) {
    //         // Get the user associated with the schedule
    //         $user = User::find($schedule->user_id);

    //         // Check if the user exists and has a phone number
    //         if ($user && $user->phone_number) {
    //             // Build the SMS content with schedule information
    //             $smsContent = "Waste Collection Schedule:\n";
    //             $smsContent .= "Location: {$schedule->location}\n";
    //             $smsContent .= "Date: {$schedule->start}\n";
    //             $smsContent .= "Time: {$schedule->time}\n";

    //             // Send SMS with the built content
    //             $response = $client->sms()->send(
    //                 new \Vonage\SMS\Message\SMS($user->phone_number, 'WDTS', $smsContent)
    //             );

    //             $message = $response->current();

    //             if ($message->getStatus() == 0) {
    //                 echo "Message sent successfully to user ID: " . $user->id . "\n";
    //             } else {
    //                 echo "Message failed for user ID: " . $user->id . " with status: " . $message->getStatus() . "\n";
    //             }
    //         } else {
    //             echo "User with ID: " . $schedule->user_id . " does not have a valid phone number.\n";
    //         }
    //     }

    //     return redirect()->route('schedule')->with('message', 'SMS sent to all users.');
    // }

    // admin button
    // recipient number dynamic based on the number of residents
    // public function sms()
    // {
    //     // vonage sms api credentials
    //     $basic  = new \Vonage\Client\Credentials\Basic("59e7dbad", "2kKXDNaClVCz33u2");
    //     $client = new \Vonage\Client($basic);

    //     // Retrieve schedules from the database
    //     $schedules = Schedule::all();

    //     // Retrieve users from the database to get their phone numbers
    //     $users = User::all(); // Assuming you have a User model

    //     // Build the SMS content with schedule information
    //     $smsContent = 'Waste Collection Schedules:' . PHP_EOL;

    //     foreach ($schedules as $schedule) {
    //         $smsContent .= "Admin ID: {$schedule->users_id},\n Location: {$schedule->location},\n Date: {$schedule->start},\n Time: {$schedule->time}" . PHP_EOL;
    //     }

    //     // Send SMS to each user's phone number
    //     foreach ($users as $user) {
    //         // Check if the user has a phone number
    //         if ($user->phone_number) {
    //             // Send SMS with the built content to the user's phone number
    //             $response = $client->sms()->send(
    //                 new \Vonage\SMS\Message\SMS($user->phone_number, 'WDTS', $smsContent)
    //             );

    //             $message = $response->current();

    //             if ($message->getStatus() == 0) {
    //                 // If the message was sent successfully, redirect with success message
    //                 return redirect()->route('schedule')->with('message', 'The message was sent successfully');
    //             } else {
    //                 // If the message failed, display the status
    //                 echo "The message failed with status: " . $message->getStatus() . "\n";
    //             }
    //         }
    //     }
    // }

    // // with 1 day before the exact date before sending to sms
    // // residents matches the location to dropdown
    // public function sms()
    // {
    //     // Get the current date
    //     $currentDate = Carbon::now();

    //     // Get all active users
    //     $users = User::where('status', 'active')->get();

    //     // Initialize Vonage SMS API credentials
    //     $basic  = new \Vonage\Client\Credentials\Basic("80cd2c81", "OdKVwJeopoTsQM4V");
    //     $client = new \Vonage\Client($basic);

    //     foreach ($users as $user) {
    //         // Get the user's location (assuming the address contains location information)
    //         $userLocation = $user->location; // Update this line based on your actual structure

    //         // Access schedules with the same location and scheduled date one day before the current date
    //         $schedules = Schedule::whereDate('start', $currentDate->copy()->addDay()->toDateString())
    //                             ->where('location', $userLocation)
    //                             ->get();

    //         if ($schedules->isNotEmpty()) {
    //             // Build the SMS content with schedule information
    //             $smsContent = 'Waste Collection Schedule for ' . $userLocation . ' on ' . $currentDate->copy()->addDay()->toDateString() . ':' . PHP_EOL;

    //             foreach ($schedules as $schedule) {
    //                 $smsContent .= "Location: {$schedule->location},\n Date: {$schedule->start},\n Time: {$schedule->time}" . PHP_EOL;
    //             }

    //             // Send SMS with the built content to the user's phone number (assuming it's stored in the user model)
    //             $response = $client->sms()->send(
    //                 new \Vonage\SMS\Message\SMS($user->phone_number, 'WDTS', $smsContent)
    //             );

    //             $message = $response->current();

    //             if ($message->getStatus() == 0) {
    //                 // SMS sent successfully
    //                 // You may want to log or store the success information
    //             } else {
    //                 // SMS sending failed
    //                 echo "The message failed with status: " . $message->getStatus() . "\n";
    //                 // You may want to handle the failure accordingly
    //             }
    //         }
    //     }

    //     return redirect()->route('schedule')->with('message', 'SMS notifications were sent successfully');
    // }

    // collector button
    public function sms_controller()
    {
        // vonage sms api credentials
        $basic  = new \Vonage\Client\Credentials\Basic("59e7dbad", "2kKXDNaClVCz33u2");
        $client = new \Vonage\Client($basic);

        // Retrieve schedules from the database
        $schedules = Schedule::all();

        // Build the SMS content with schedule information
        $smsContent = 'Waste Collection Schedules:' . PHP_EOL;

        foreach ($schedules as $schedule) {
            $smsContent .= "Collector ID: {$schedule->users_id},\n Plate No.: {$schedule->plate_no},\n Location: {$schedule->location},\n Date: {$schedule->start},\n Time: {$schedule->time}" . PHP_EOL;
        }

        // Send SMS with the built content
        $response = $client->sms()->send(
            new \Vonage\SMS\Message\SMS("639307296980", 'WDTS', $smsContent)
        );

        $message = $response->current();

        if ($message->getStatus() == 0) {
            return redirect()->route('collector-schedule')->with('message', 'The message was sent successfully');
        } else {
            echo "The message failed with status: " . $message->getStatus() . "\n";
        }
    }

    // phone number dynamic based on the valid phone number of residents
    // collector button
    // public function sms_controller()
    // {
    //     // Vonage SMS API credentials
    //     $basic  = new \Vonage\Client\Credentials\Basic("59e7dbad", "2kKXDNaClVCz33u2");
    //     $client = new \Vonage\Client($basic);

    //     // Retrieve schedules from the database
    //     $schedules = Schedule::all();

    //     // Iterate through schedules and send SMS to each user
    //     foreach ($schedules as $schedule) {
    //         // Get the user associated with the schedule
    //         $user = User::find($schedule->user_id);

    //         // Check if the user exists and has a phone number
    //         if ($user && $user->phone_number) {
    //             // Build the SMS content with schedule information
    //             $smsContent = "Waste Collection Schedule:\n";
    //             $smsContent .= "Location: {$schedule->location}\n";
    //             $smsContent .= "Date: {$schedule->start}\n";
    //             $smsContent .= "Time: {$schedule->time}\n";

    //             // Send SMS with the built content
    //             $response = $client->sms()->send(
    //                 new \Vonage\SMS\Message\SMS($user->phone_number, 'WDTS', $smsContent)
    //             );

    //             $message = $response->current();

    //             if ($message->getStatus() == 0) {
    //                 echo "Message sent successfully to user ID: " . $user->id . "\n";
    //             } else {
    //                 echo "Message failed for user ID: " . $user->id . " with status: " . $message->getStatus() . "\n";
    //             }
    //         } else {
    //             echo "User with ID: " . $schedule->user_id . " does not have a valid phone number.\n";
    //         }
    //     }

    //     return redirect()->route('collector-schedule')->with('message', 'SMS sent to all users.');
    // }

    // // with 1 day before the exact date before sending to sms
    // // residents matches the location to dropdown
    // public function sms_controller()
    // {
    //     // Get the current date
    //     $currentDate = Carbon::now();

    //     // Get all active users
    //     $users = User::where('status', 'active')->get();

    //     // Initialize Vonage SMS API credentials
    //     $basic  = new \Vonage\Client\Credentials\Basic("80cd2c81", "OdKVwJeopoTsQM4V");
    //     $client = new \Vonage\Client($basic);

    //     foreach ($users as $user) {
    //         // Get the user's location (assuming the address contains location information)
    //         $userLocation = $user->location; // Update this line based on your actual structure

    //         // Access schedules with the same location and scheduled date one day before the current date
    //         $schedules = Schedule::whereDate('start', $currentDate->copy()->addDay()->toDateString())
    //                             ->where('location', $userLocation)
    //                             ->get();

    //         if ($schedules->isNotEmpty()) {
    //             // Build the SMS content with schedule information
    //             $smsContent = 'Waste Collection Schedule for ' . $userLocation . ' on ' . $currentDate->copy()->addDay()->toDateString() . ':' . PHP_EOL;

    //             foreach ($schedules as $schedule) {
    //                 $smsContent .= "Location: {$schedule->location},\n Date: {$schedule->start},\n Time: {$schedule->time}" . PHP_EOL;
    //             }

    //             // Send SMS with the built content to the user's phone number (assuming it's stored in the user model)
    //             $response = $client->sms()->send(
    //                 new \Vonage\SMS\Message\SMS($user->phone_number, 'WDTS', $smsContent)
    //             );

    //             $message = $response->current();

    //             if ($message->getStatus() == 0) {
    //                 // SMS sent successfully
    //                 // You may want to log or store the success information
    //             } else {
    //                 // SMS sending failed
    //                 echo "The message failed with status: " . $message->getStatus() . "\n";
    //                 // You may want to handle the failure accordingly
    //             }
    //         }
    //     }

    //     return redirect()->route('collector-schedule')->with('message', 'SMS notifications were sent successfully');
    // }

}
