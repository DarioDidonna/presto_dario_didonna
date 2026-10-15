<?php

/*
| Messaggi di validazione in italiano per le regole usate nel progetto
| (login, registrazione, cambio password, foto profilo). Le regole non
| elencate qui ricadono sulla versione inglese (fallback_locale).
*/

return [
    'accepted' => 'Il campo :attribute deve essere accettato.',
    'array' => 'Il campo :attribute deve essere un elenco.',
    'confirmed' => 'La conferma del campo :attribute non corrisponde.',
    'current_password' => 'La password attuale non è corretta.',
    'dimensions' => 'Le dimensioni dell\'immagine :attribute non sono valide.',
    'email' => 'Il campo :attribute deve essere un indirizzo email valido.',
    'exists' => 'Il valore selezionato per :attribute non è valido.',
    'file' => 'Il campo :attribute deve essere un file.',
    'image' => 'Il campo :attribute deve essere un\'immagine.',
    'integer' => 'Il campo :attribute deve essere un numero intero.',
    'max' => [
        'array' => 'Il campo :attribute non può avere più di :max elementi.',
        'file' => 'Il file :attribute non può superare :max kilobyte.',
        'numeric' => 'Il campo :attribute non può essere maggiore di :max.',
        'string' => 'Il campo :attribute non può superare :max caratteri.',
    ],
    'mimes' => 'Il campo :attribute deve essere un file di tipo: :values.',
    'min' => [
        'array' => 'Il campo :attribute deve avere almeno :min elementi.',
        'file' => 'Il file :attribute deve essere di almeno :min kilobyte.',
        'numeric' => 'Il campo :attribute deve essere almeno :min.',
        'string' => 'Il campo :attribute deve contenere almeno :min caratteri.',
    ],
    'numeric' => 'Il campo :attribute deve essere un numero.',
    'password' => [
        'letters' => 'Il campo :attribute deve contenere almeno una lettera.',
        'mixed' => 'Il campo :attribute deve contenere almeno una lettera maiuscola e una minuscola.',
        'numbers' => 'Il campo :attribute deve contenere almeno un numero.',
        'symbols' => 'Il campo :attribute deve contenere almeno un simbolo.',
        'uncompromised' => 'La :attribute indicata è comparsa in una fuga di dati. Scegline una diversa.',
    ],
    'required' => 'Il campo :attribute è obbligatorio.',
    'string' => 'Il campo :attribute deve essere un testo.',
    'unique' => 'Il valore del campo :attribute è già in uso.',
    'uploaded' => 'Il caricamento del file :attribute non è riuscito.',

    'custom' => [],

    'attributes' => [
        'name' => 'nome utente',
        'email' => 'email',
        'password' => 'password',
        'password_confirmation' => 'conferma password',
        'current_password' => 'password attuale',
        'avatar' => 'foto profilo',
    ],
];
