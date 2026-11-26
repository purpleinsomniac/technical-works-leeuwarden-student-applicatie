<?php

return [
    // Accountwisseling is alleen beschikbaar in de lokale omgeving.
    'account_switcher' => env('DEMO_ACCOUNT_SWITCHER', true),
    'accounts' => [
        'student' => ['email' => 'student@example.test', 'label' => 'Student · Sam'],
        // Beide studenten hebben dezelfde rol, maar elk een eigen keuzenaam.
        'student2' => ['email' => 'student2@example.test', 'role' => 'student', 'label' => 'Student · Wesley'],
        'coach' => ['email' => 'coach@example.test', 'label' => 'Docent / coach'],
        'teamleider' => ['email' => 'manager@example.test', 'label' => 'Teamleider'],
        'beheerder' => ['email' => 'admin@example.test', 'label' => 'Applicatiebeheerder'],
    ],
];
