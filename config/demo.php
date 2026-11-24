<?php

return [
    // Tijdelijk voor demonstraties. Buiten APP_ENV=local is wisselen altijd uitgeschakeld.
    'account_switcher' => env('DEMO_ACCOUNT_SWITCHER', true),
    'accounts' => [
        'student' => ['email'=>'student@example.test', 'label'=>'Student · Sam'],
        'student2' => ['email'=>'student2@example.test', 'role'=>'student', 'label'=>'Student · Wesley'],
        'coach' => ['email'=>'coach@example.test', 'label'=>'Docent / coach'],
        'teamleider' => ['email'=>'manager@example.test', 'label'=>'Teamleider'],
        'beheerder' => ['email'=>'admin@ITIG.edu', 'label'=>'Applicatiebeheerder'],
    ],
];
