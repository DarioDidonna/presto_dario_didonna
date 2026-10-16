<?php

namespace App\Services;

use RuntimeException;

/**
 * La richiesta è già stata accettata, rifiutata o annullata (anche nello stesso istante, dall'altra parte).
 */
class PurchaseRequestNotPendingException extends RuntimeException {}
