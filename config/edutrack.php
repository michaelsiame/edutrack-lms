<?php

/*
|--------------------------------------------------------------------------
| Edutrack institution / contact details
|--------------------------------------------------------------------------
| Single source of truth for the college's public contact info used in
| emails (and anywhere else). Override per-environment via .env without
| editing templates.
*/

return [
    'name' => env('EDUTRACK_NAME', 'Edutrack Computer Training College'),
    'short_name' => env('EDUTRACK_SHORT_NAME', 'Edutrack LMS'),
    'tagline' => env('EDUTRACK_TAGLINE', 'Computer Training College'),
    'email' => env('EDUTRACK_EMAIL', env('MAIL_FROM_ADDRESS', 'edutrackzambia@gmail.com')),
    'phone' => env('EDUTRACK_PHONE', '+260 770 666 937'),
    'location' => env('EDUTRACK_LOCATION', 'Kalomo, Zambia'),
];
