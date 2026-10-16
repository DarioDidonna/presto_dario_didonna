<?php

namespace App\Services;

use RuntimeException;

/**
 * Il carrello non può essere inviato (vuoto, o annunci non più disponibili): il messaggio è per l'utente.
 */
class CheckoutException extends RuntimeException {}
