<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Controllers\HubspotController;
use App\Http\Controllers\MSGraphController;
use App\Models\Options;
use App\Models\Tokens;
use DateTime;
use DateTimeZone;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class APIController extends Controller
{
    /**
     * Function to run API calls in sequence through url from admin only page or cron job
     *
     * @return void
     */
    private function run()
    {
        ini_set('max_execution_time', '0');
        $msgraph = new MSGraphController();

        // Get booking businesses to retrieve booking appointments by booking business id
        $booking_businesses = $msgraph->get_booking_businesses();

        foreach ($booking_businesses as $booking_business) {
            //Get today's appointment bookings
            $appointment_bookings = $msgraph->get_appointment_bookings($booking_business->id);

            foreach ($appointment_bookings as $appointment_booking) {
                // Create a contact in hubspot for this appointment booking
                $contact = (new HubspotController())->create_contact($appointment_booking);

                if (!empty($contact->email)) {
                    // Send message to ms teams channel
                    $send_message = $msgraph->teams_send_message($appointment_booking);
                    if (!$send_message) {
                        Log::error('Unable to send message for appointment ' . $appointment_booking['appointment_id']);
                    } else {
                        $appointment_info = Options::Create([
                            'type' => 'appointment',
                            'key' => $appointment_booking['appointment_id'],
                            'value' => $appointment_booking['email'],
                        ]);
                    }

                } else {
                    Log::error('Unable to create or update contact for appointment ' . $appointment_booking['appointment_id']);
                }
            }
        }
    }

    public function admin_trigger_manually()
    {
        if (Auth::check()) {
            $this->run();
            return redirect('/')->with('admin_message', 'Triggered API call!');
        }

        return redirect()->to('/');
    }

    public function trigger_cron()
    {
        $this->run();

        $timezone = config('app.timezone');
        $datetime = new DateTime('now', new DateTimeZone($timezone));
        $datetime = $datetime->format('d-m-Y H:i:s');

        return $datetime;
    }

    public function delete()
    {
        if (Auth::check()) {
            Tokens::where('token_type', 'Bearer')->delete();
        }

        return redirect()->to('/');
    }
}
