<?php

/**
 * 2026 graduation list (source: "2026 FINAL LIST.pdf").
 * Consumed by `php artisan certificates:batch-2026`.
 *
 * Each row: [student_id, name, course, grade]
 *  - course  : matched against Course.title with a LIKE, created if missing
 *  - grade   : DISTINCTION | MERIT | CREDIT | PASS  -> certificate classification
 *
 * `default_durations` seeds duration_weeks ONLY when a course has to be created;
 * an existing course keeps its own duration.
 */

return [
    'default_durations' => [
        'General Basic ICT & Computing'   => 12,
        'Cybersecurity Fundamentals'      => 8,
        'Data Analysis'                   => 8,
        'Web Development'                 => 12,
        'Computer Studies'                => 24,
        'Secretarial & Office Management' => 12,
        'Microsoft Suite & Internet'      => 8,
        'Basics of Software Engineering'  => 12,
        'Basics of Information Technology' => 12,
    ],

    'graduates' => [
        // [student_id, name, course, grade]
        ['26EDU438751', 'LUYANDO MUCHIMBA',            'General Basic ICT & Computing', 'DISTINCTION'],
        ['26EDU243189', "WANE MARY NG'AMBI",           'General Basic ICT & Computing', 'DISTINCTION'],
        ['26EDU292186', 'MAUREEN CHIPAMPE MUPETA',     'General Basic ICT & Computing', 'DISTINCTION'],
        ['26EDU461507', 'MUZUNI NOOLE',                'General Basic ICT & Computing', 'DISTINCTION'],
        ['26EDU417966', 'TERRY MUNYATI',               'General Basic ICT & Computing', 'DISTINCTION'],
        ['26EDU431852', 'TAONGA TEMBO',                'General Basic ICT & Computing', 'DISTINCTION'],
        ['26EDU270253', 'ABIGAIL BWALYA',              'General Basic ICT & Computing', 'DISTINCTION'],
        ['26EDU438856', 'CHINTU CHIINDA',              'General Basic ICT & Computing', 'DISTINCTION'],
        ['26EDU387360', 'FRAGESTER MUDENDA',           'General Basic ICT & Computing', 'DISTINCTION'],
        ['26EDU349770', 'SINGALI PHIRI',               'General Basic ICT & Computing', 'DISTINCTION'],
        ['26EDU407479', 'JOYCE LISHEBELA',             'General Basic ICT & Computing', 'DISTINCTION'],
        ['EDU2026002',  'BETTY LUMBONGO',              'General Basic ICT & Computing', 'DISTINCTION'],
        ['26EDU346722', 'PATRICIA SIAMUKOPA',          'General Basic ICT & Computing', 'MERIT'],
        ['26EDU249580', 'CATHERINE NAMAKANDA',         'General Basic ICT & Computing', 'MERIT'],
        ['ED2026006',   'MIKE LEMBELEMBE',             'General Basic ICT & Computing', 'MERIT'],
        ['26EDU361975', 'IREEN NDEKE',                 'General Basic ICT & Computing', 'MERIT'],
        ['26EDU330116', 'DABALI LUYANDO',              'General Basic ICT & Computing', 'MERIT'],
        ['ED2026004',   'CHIPEGO TOMU',                'General Basic ICT & Computing', 'MERIT'],
        ['26EDU417901', 'TRUST WANKIE',                'General Basic ICT & Computing', 'MERIT'],
        ['26EDU300001', 'SYVIA SIACHIJA',              'General Basic ICT & Computing', 'MERIT'],
        ['26EDU387950', 'WATUPA EVANESS SIAKONDE',     'General Basic ICT & Computing', 'MERIT'],
        ['26EDU334347', 'EMMANUEL MUYAPEKWA',          'General Basic ICT & Computing', 'MERIT'],
        ['26EDU277449', 'LILADA CHIHOLYONGA NAYEE',    'General Basic ICT & Computing', 'MERIT'],
        ['26EDU136112', 'ESTON MUTANGA',               'General Basic ICT & Computing', 'MERIT'],
        ['26EDU302947', 'ABSON SIMUKABE',              'General Basic ICT & Computing', 'CREDIT'],
        ['EDU2026003',  'MOOYA SIMANYANGA',            'General Basic ICT & Computing', 'CREDIT'],
        ['26EDU234172', 'RACHEAL SIAMASAKA',           'General Basic ICT & Computing', 'CREDIT'],
        ['EDU2026001',  'SHARON LUMBONGO',             'General Basic ICT & Computing', 'CREDIT'],
        ['EDU2026005',  'JOYCE PHIRI',                 'General Basic ICT & Computing', 'CREDIT'],
        ['26EDU253566', 'STEPHEN DINGANI MBAZIMA',     'General Basic ICT & Computing', 'DISTINCTION'],
        ['26EDU543340', 'MILIMO NANJAME',              'General Basic ICT & Computing', 'MERIT'],
        ['26EDU579674', 'COSMAS MOONGA',               'General Basic ICT & Computing', 'MERIT'],
        ['26EDU324084', 'KALUWE MIKUNGA',              'General Basic ICT & Computing', 'MERIT'],
        ['26EDU609678', 'SAVIOUR MIKUNGA',             'General Basic ICT & Computing', 'MERIT'],
        ['26EDU462269', "NG'ONGA MWAPE",               'Cybersecurity Fundamentals',    'DISTINCTION'],
        ['26EDU462269', "NG'ONGA MWAPE",               'Data Analysis',                 'MERIT'],
        ['26EDU349447', 'PROTEST MUNSANJE',            'Web Development',               'DISTINCTION'],
        ['26EDU259288', 'EDWARD MUSOLE',               'Computer Studies',             'DISTINCTION'],
        ['26EDU273257', 'LWEENDO NAKEEMPA',            'Secretarial & Office Management', 'MERIT'],
        ['26EDU284855', 'MELUSI MUPWAYA',              'Microsoft Suite & Internet',    'MERIT'],
        ['26EDU319680', 'JOSHUA HAMWEENDA',            'Microsoft Suite & Internet',    'MERIT'],
        ['26EDU357873', 'BRIAN PHIRI',                 'Basics of Software Engineering', 'DISTINCTION'],
        ['26EDU391684', 'MARY MASELENI',               'Basics of Information Technology', 'DISTINCTION'],
    ],
];
