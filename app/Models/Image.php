<?php

namespace App\Models;

use App\Jobs\RemoveFaces;
use App\Jobs\ResizeImage;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Image extends Model
{
    use HasFactory;

    // Lato della versione grande (dettaglio annuncio e dashboard revisore); 300 resta per le card
    public const DISPLAY_SIZE = 800;

    protected $fillable = [
        'path',
    ];

    // Volti controllati dal rilevatore locale (anche se non ne ha trovati)
    public function facesChecked(): bool
    {
        return $this->faces_count !== null;
    }

    /** @return BelongsTo<Article, $this> */
    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }

    public static function getUrlByFilePath($filePath, $w = null, $h = null)
    {
        if (! $w && ! $h) {
            return Storage::url($filePath);
        }
        $path = dirname($filePath);
        $filename = basename($filePath);
        $file = "{$path}/crop_{$w}x{$h}_{$filename}";

        return Storage::url($file);
    }

    /**
     * Catena di elaborazione: oscuramento dei volti (rilevatore locale), poi miniature 300/800 con watermark.
     */
    public function process(): void
    {
        RemoveFaces::withChain([
            new ResizeImage($this->path, 300, 300),
            new ResizeImage($this->path, self::DISPLAY_SIZE, self::DISPLAY_SIZE),
        ])->dispatch($this->id);
    }

    /**
     * Elimina il record e i file su disco (originale e versioni ritagliate).
     */
    public function deleteWithFiles(): void
    {
        $dir = dirname($this->path);
        $name = basename($this->path);

        Storage::disk('public')->delete([
            $this->path,
            "{$dir}/crop_300x300_{$name}",
            "{$dir}/crop_".self::DISPLAY_SIZE.'x'.self::DISPLAY_SIZE."_{$name}",
        ]);

        $this->delete();
    }

    /**
     * Versione grande con watermark; ripiega sulla miniatura o sull'originale se non è ancora stata generata.
     */
    public function getDisplayUrl(): string
    {
        $disk = Storage::disk('public');
        foreach ([self::DISPLAY_SIZE, 300] as $size) {
            $crop = dirname($this->path)."/crop_{$size}x{$size}_".basename($this->path);
            if ($disk->exists($crop)) {
                return Storage::url($crop);
            }
        }

        // File spariti dal disco (dati vecchi o cancellati a mano): segnaposto invece di un riquadro vuoto
        return $disk->exists($this->path) ? Storage::url($this->path) : asset('media/placeholder.svg');
    }

    /**
     * Miniatura 300x300 con watermark; se non esiste ancora (la coda non l'ha generata) l'originale,
     * se manca anche quello il segnaposto: mai un'immagine rotta.
     */
    public function thumbnailUrl(): string
    {
        $disk = Storage::disk('public');
        $crop = dirname($this->path).'/crop_300x300_'.basename($this->path);

        return match (true) {
            $disk->exists($crop) => Storage::url($crop),
            $disk->exists($this->path) => Storage::url($this->path),
            default => asset('media/placeholder.svg'),
        };
    }

    public function getUrl($w = null, $h = null)
    {
        return self::getUrlByFilePath($this->path, $w, $h);
    }

    protected function casts(): array
    {
        return [
            'faces_count' => 'integer',
        ];
    }
}
