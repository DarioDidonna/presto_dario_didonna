<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Laravel\Fortify\Contracts\FailedPasswordResetLinkRequestResponse;

/**
 * Stessa risposta che se il link fosse partito: un errore rivelerebbe quali email sono registrate sul sito.
 */
class GenericPasswordResetLinkResponse implements FailedPasswordResetLinkRequestResponse
{
    /**
     * @param  Request  $request
     */
    public function toResponse($request): JsonResponse|RedirectResponse
    {
        return $request->wantsJson()
            ? new JsonResponse(['message' => __('passwords.sent')], 200)
            : back()->with('status', __('passwords.sent'));
    }
}
