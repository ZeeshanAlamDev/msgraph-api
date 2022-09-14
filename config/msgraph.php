<?php

return [
  'app_id'          => env('MSGRAPH_APP_ID', ''),
  'app_secret'      => env('MSGRAPH_APP_SECRET', ''),
  'tenant_id'       => env('MSGRAPH_TENANT_ID', ''),
  'team_id'         => env('MSTEAM_ID', ''),
  'channel_id'      => env('MSTEAM_CHANNEL_ID', ''),
  'channel_message' => env('MSTEAM_MESSAGE', ''),
  'hubspot_token'   => env('HS_ACCESS_TOKEN', ''),
  'interval'   => env('CRON_INTERVAL', ''),
];
