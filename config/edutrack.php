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

    // Video-conference domain for the built-in room. Point JITSI_DOMAIN at a
    // self-hosted Jitsi (e.g. meet.edutrackzambia.com) to escape the public
    // instance's limits; defaults to the free public server.
    'jitsi_domain' => env('JITSI_DOMAIN', 'meet.jit.si'),

    /*
    | Final-grade weighting (best attempt per item, combined by these weights).
    */
    'grade' => [
        'quiz_weight' => (float) env('GRADE_QUIZ_WEIGHT', 40),
        'assignment_weight' => (float) env('GRADE_ASSIGNMENT_WEIGHT', 60),
    ],
];
