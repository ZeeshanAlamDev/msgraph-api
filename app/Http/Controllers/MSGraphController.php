<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Options;
use App\Models\Tokens;
use DateTime;
use DateTimeZone;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MSGraphController extends Controller
{
    public function api_response(Request $request)
    {
        if (isset($request->code)) {
            $this->set_access_token($request->code);
        }

        return redirect('/')->with('message', 'Unable to get access token.');
    }

    public function set_access_token($code)
    {
        $message = '';
        $tenant_id = config('msgraph.tenant_id');
        $redirect = url('msgraph-api');
        $endpoint = 'https://login.microsoftonline.com/' . $tenant_id . '/oauth2/v2.0/token';
        $param = [
            'grant_type' => 'authorization_code',
            'code' => $code,
            'client_id' => config('msgraph.app_id'),
            'client_secret' => config('msgraph.app_secret'),
            'scope' => 'Bookings.Read.All BookingsAppointment.ReadWrite.All ChannelMessage.Send',
            'redirect_uri' => $redirect,
        ];

        try {
            $response = Http::asForm()->post($endpoint, $param);
            $token_data = json_decode($response->getBody()->getContents());

            if (!empty($token_data->access_token) && !empty($token_data->refresh_token)) {
                $data = [
                    'token_type' => $token_data->token_type,
                    'expires_in' => time() + $token_data->expires_in,
                    'access_token' => $token_data->access_token,
                    'refresh_token' => $token_data->refresh_token,
                ];
                $token = Tokens::updateOrCreate(['token_type' => 'Bearer'], $data);
                $message = !empty($token->access_token) ? 'Successfully authorized!' : 'Unable to save authorization data in database.';
            } else {
                $message = 'Error getting access token. ' . $token_data->error . ' ' . $token_data->error_description;
            }
        } catch (\Throwable$th) {
            $message = 'Error getting access token. ' . $th->getMessage();
        }

        return redirect('/')->with('admin_message', $message);
    }

    public function refresh_token($token_data)
    {
        $error = '';
        $tenant_id = config('msgraph.tenant_id');
        $endpoint = 'https://login.microsoftonline.com/' . $tenant_id . '/oauth2/v2.0/token';
        $param = [
            'grant_type' => 'refresh_token',
            'client_id' => config('msgraph.app_id'),
            'client_secret' => config('msgraph.app_secret'),
            'refresh_token' => $token_data->refresh_token,
            'scope' => 'Bookings.Read.All BookingsAppointment.ReadWrite.All ChannelMessage.Send',
        ];

        try {
            $response = Http::asForm()->post($endpoint, $param);
            $token_data = json_decode($response->getBody()->getContents());

            if (!empty($token_data->access_token) && !empty($token_data->refresh_token)) {
                $data = [
                    'token_type' => $token_data->token_type,
                    'expires_in' => time() + $token_data->expires_in,
                    'access_token' => $token_data->access_token,
                    'refresh_token' => $token_data->refresh_token,
                ];
                $token = Tokens::updateOrCreate(['token_type' => 'Bearer'], $data);

                return $token_data->access_token;
            } else {
                $error = 'Error getting access token. ' . $token_data->error . ' ' . $token_data->error_description;
            }
        } catch (Exception $e) {
            $error = 'Error getting access token. ' . $e->getMessage();
        }
        $error = 'Refresh token error. ' . $error;
        Log::error($error);

        die();
    }

    public function get_access_token($check = false)
    {
        $access_token = '';
        $token_data = Tokens::where('token_type', 'Bearer')->first();

        if (!empty($token_data->access_token) && !empty($token_data->refresh_token)) {
            if (intval($token_data->expires_in - time()) > 950) {
                $access_token = $token_data->access_token;
            } else {
                $access_token = $this->refresh_token($token_data);
            }
        } else {
            $error = 'Access token not found. Please re-authorize app from admin page.';
            Log::error($error);

            die($error);
        }

        return $access_token;
    }

    public function get_booking_businesses()
    {
        $endpoint = 'https://graph.microsoft.com/v1.0/solutions/bookingBusinesses';
        $access_token = $this->get_access_token();
        $response = Http::withToken($access_token)->get($endpoint);
        $body = json_decode($response->getBody()->getContents());

        if (empty($body->value) || !is_array($body->value)) {
            $error = 'Unable to get booking businesses';
            Log::error($error);

            die($error);
        }

        $booking_businesses = $body->value;

        return $booking_businesses;
    }

    public function get_appointment_bookings($businesse_id)
    {
        $timezone = config('app.timezone');
        $start_date = new DateTime('today', new DateTimeZone($timezone));
        $start_date = $start_date->format(DateTime::ATOM);
        $end_date = new DateTime('tomorrow', new DateTimeZone($timezone));
        $end_date = $end_date->format(DateTime::ATOM);
        $endpoint = 'https://graph.microsoft.com/v1.0/solutions/bookingBusinesses/' . $businesse_id . '/calendarView?start=' . $start_date . '&end=' . $end_date;
        $access_token = $this->get_access_token();
        $response = Http::withToken($access_token)->get($endpoint);
        $body = json_decode($response->getBody()->getContents());
        $appointments = !empty($body->value) ? $body->value : [];
        $appointments = json_decode(json_encode($appointments), false);
        $appointment_bookings = [];

        foreach ($appointments as $appointment) {
            $customers = !empty($appointment->customers) ? $appointment->customers : $this->get_appointment_customers($businesse_id, $appointment->id);
            if (empty($customers)) {
                continue;
            }

            $startDateTime = date_format(date_create($appointment->startDateTime->dateTime), 'Y-m-d H:i:s');
            $endDateTime = date_format(date_create($appointment->endDateTime->dateTime), 'Y-m-d H:i:s');

            foreach ($customers as $customer) {
                $customer_exists = Options::where('key', $appointment->id)->where('value', $customer->emailAddress)->first();
                if (!empty($customer_exists->value)) {
                    continue;
                }

                $lastname = strstr($customer->name, ' ');
                $firstname = str_replace($lastname, '', $customer->name);
                $appointment_bookings[] = [
                    'firstname' => $firstname,
                    'lastname' => $lastname,
                    'email' => $customer->emailAddress,
                    'appointment_id' => $appointment->id,
                    'appointment_start' => $startDateTime,
                    'appointment_end' => $endDateTime,
                ];
            }
        }

        if (empty($appointment_bookings)) {
            $error = 'No appointment bookings found for calendar aka booking business ' . $businesse_id;
            Log::error($error);
        }

        return $appointment_bookings;
    }

    public function get_appointment_customers($businesse_id, $appointment_id)
    {
        $endpoint = 'https://graph.microsoft.com/v1.0/solutions/bookingBusinesses/' . $businesse_id . '/appointments/' . $appointment_id;
        $access_token = $this->get_access_token();
        $response = Http::withToken($access_token)->get($endpoint);
        $body = json_decode($response->getBody()->getContents());
        $customers = !empty($body->customers) ? $body->customers : [];

        return $customers;
    }

    public function teams_send_message($appointment_booking)
    {
        $team_id = config('msgraph.team_id');
        $channel_id = config('msgraph.channel_id');
        $get_channel_message = config('msgraph.channel_message');
        $shortcodes = [
            'customer_name',
            'customer_email',
            'appointment_id',
            'appointment_start',
            'appointment_end',
        ];
        $shortcode_values = [
            $appointment_booking['firstname'] . ' ' . $appointment_booking['lastname'],
            $appointment_booking['email'],
            $appointment_booking['appointment_id'],
            $appointment_booking['appointment_start'],
            $appointment_booking['appointment_end'],
        ];
        $channel_message = str_replace($shortcodes, $shortcode_values, $get_channel_message);
        $endpoint = 'https://graph.microsoft.com/v1.0/teams/' . $team_id . '/channels/' . $channel_id . '/messages';
        $access_token = $this->get_access_token();
        $content = json_encode([
            'body' => [
                'content' => $channel_message,
            ],
        ]);

        $response = Http::withToken($access_token)->withBody(
            $content,
            'application/json'
        )->post($endpoint);

        $body = json_decode($response->getBody()->getContents());
        $is_send = empty($body->error) && !empty($body->from) ? true : false;

        return $is_send;
    }
}
