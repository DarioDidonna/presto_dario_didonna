<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateAvatarRequest;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Image\Enums\Fit;
use Spatie\Image\Enums\ImageDriver;
use Spatie\Image\Image;

class ProfileController extends Controller implements HasMiddleware
{
    public const AVATAR_SIZE = 256;

    public static function middleware(): array
    {
        return [
            new Middleware('auth'),
        ];
    }

    public function show(Request $request)
    {
        $user = $request->user();

        $articles = $user->articles()->with(['images', 'category'])->latest()->get();

        // whereStrict: con where() il confronto è "==" e null (in revisione) verrebbe contato come false (rifiutato)
        $counts = [
            'published' => $articles->whereStrict('is_accepted', true)->count(),
            'pending' => $articles->whereNull('is_accepted')->count(),
            'rejected' => $articles->whereStrict('is_accepted', false)->count(),
        ];

        return view('profile.show', compact('user', 'articles', 'counts'));
    }

    // Validazione in UpdateAvatarRequest
    public function updateAvatar(UpdateAvatarRequest $request)
    {
        $user = $request->user();
        $disk = Storage::disk('public');
        $path = "avatars/{$user->id}/".Str::random(40).'.jpg';

        // Ritaglio quadrato e ricodifica in JPEG: immagine leggera e senza metadati EXIF (es. posizione GPS)
        $disk->makeDirectory(dirname($path));
        Image::useImageDriver(ImageDriver::Gd)
            ->load($request->file('avatar')->getRealPath())
            ->fit(Fit::Crop, self::AVATAR_SIZE, self::AVATAR_SIZE)
            ->background('#0f172a') // i PNG trasparenti non diventano neri nel JPEG
            ->quality(85)
            ->save($disk->path($path));

        $this->deleteAvatarFile($user->avatar);
        $user->avatar = $path;
        $user->save();

        return redirect()->route('profile.show')->with('message', __('ui.avatar_updated'));
    }

    public function destroyAvatar(Request $request)
    {
        $user = $request->user();

        $this->deleteAvatarFile($user->avatar);
        $user->avatar = null;
        $user->save();

        return redirect()->route('profile.show')->with('message', __('ui.avatar_removed'));
    }

    private function deleteAvatarFile(?string $path): void
    {
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }
}
