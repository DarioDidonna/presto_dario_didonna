<?php

namespace App\Models;

use App\Jobs\GoogleVisionLabelImage;
use App\Jobs\GoogleVisionSafeSearch;
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

    // I job Google Vision salvano i rating SafeSearch e le etichette: se mancano entrambi l'analisi non è avvenuta
    public function hasVisionAnalysis(): bool
    {
        return $this->adult !== null || ! empty($this->labels);
    }

    /**
     * Catena di elaborazione: oscuramento volti, miniature 300/800 con watermark, analisi Google Vision.
     */
    public function process(): void
    {
        RemoveFaces::withChain([
            new ResizeImage($this->path, 300, 300),
            new ResizeImage($this->path, self::DISPLAY_SIZE, self::DISPLAY_SIZE),
            new GoogleVisionSafeSearch($this->id),
            new GoogleVisionLabelImage($this->id),
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

    // Livello leggibile da una macchina (per l'assistente del revisore) a partire dalla classe CSS salvata
    public static function likelihoodLevel(?string $cssClass): string
    {
        return match (true) {
            $cssClass === null => 'unknown',
            str_contains($cssClass, 'text-success') => 'unlikely',
            str_contains($cssClass, 'text-warning') => 'possible',
            str_contains($cssClass, 'text-danger') => 'likely',
            default => 'unknown',
        };
    }

    // Il DB salva la classe CSS dell'icona SafeSearch: qui la si traduce in testo leggibile (title e screen reader)
    public static function likelihoodLabel(?string $cssClass): string
    {
        return match (true) {
            $cssClass === null => __('ui.likelihood_unknown'),
            str_contains($cssClass, 'text-success') => __('ui.likelihood_unlikely'),
            str_contains($cssClass, 'text-warning') => __('ui.likelihood_possible'),
            str_contains($cssClass, 'text-danger') => __('ui.likelihood_likely'),
            default => __('ui.likelihood_unknown'),
        };
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

    public function getUrl($w = null, $h = null)
    {
        return self::getUrlByFilePath($this->path, $w, $h);
    }

    protected function casts(): array
    {
        return [
            'labels' => 'array',
        ];
    }
}
