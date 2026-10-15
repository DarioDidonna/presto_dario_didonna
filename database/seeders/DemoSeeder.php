<?php

namespace Database\Seeders;

use App\Jobs\ResizeImage;
use App\Models\Article;
use App\Models\Category;
use App\Models\Image;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Dati dimostrativi: account con credenziali note e annunci con foto in tutti gli stati.
 *
 *   php artisan db:seed --class=DemoSeeder
 *
 * Foto: se esiste database/seeders/demo-images/{slug-categoria}/ con file jpg/png/webp vengono usati quelli,
 * altrimenti si generano immagini astratte nei colori del sito (nessuna connessione richiesta).
 */
class DemoSeeder extends Seeder
{
    public const PASSWORD = 'password';

    // Colore d'accento delle immagini generate, per categoria
    private const COLORS = [
        'Abbigliamento' => [236, 72, 153],
        'Accessori' => [168, 85, 247],
        'Animali domestici' => [34, 197, 94],
        'Casa e Giardinaggio' => [132, 204, 22],
        'Elettronica' => [6, 182, 212],
        'Giocattoli' => [249, 115, 22],
        'Libri e Riviste' => [234, 179, 8],
        'Motori' => [239, 68, 68],
        'Salute e Bellezza' => [244, 114, 182],
        'Sport' => [59, 130, 246],
    ];

    // [titolo, categoria, prezzo, descrizione, stato (true = pubblicato, null = in revisione, false = rifiutato), foto]
    private const ARTICLES = [
        ['Giacca in pelle da uomo taglia L', 'Abbigliamento', 120, "Giacca in vera pelle, colore marrone scuro.\nIndossata poche volte, nessun segno di usura. Fodera interna in perfette condizioni.", true, 2],
        ['Sneakers bianche numero 38', 'Abbigliamento', 45, "Sneakers in tela bianca, numero 38.\nUsate una sola stagione, lavate e igienizzate.", true, 1],
        ['Orologio automatico in acciaio', 'Accessori', 260, "Orologio automatico con cassa in acciaio da 40 mm e vetro zaffiro.\nCompleto di scatola, garanzia e cinturino di ricambio.", true, 3],
        ['Zaino da città impermeabile', 'Accessori', 35, "Zaino da 22 litri con tasca imbottita per PC fino a 15 pollici.\nTessuto impermeabile, ottimo per bici e treno.", true, 1],
        ['Cuccia imbottita per cani di taglia media', 'Animali domestici', 30, "Cuccia sfoderabile e lavabile in lavatrice, 80x60 cm.\nUsata pochissimo: il nostro cane preferisce il divano.", true, 1],
        ['Acquario 60 litri completo di filtro', 'Animali domestici', 70, "Acquario da 60 litri con coperchio, luce LED, filtro e riscaldatore.\nPerfettamente funzionante, svuotato e pulito.", null, 2],
        ['Set di 6 sedie in legno massello', 'Casa e Giardinaggio', 180, "Sei sedie in legno di faggio massello, seduta impagliata.\nStruttura solida, qualche piccolo segno del tempo.", true, 2],
        ['Tosaerba elettrico 1600W', 'Casa e Giardinaggio', 95, "Tosaerba elettrico con cesto di raccolta da 40 litri e altezza di taglio regolabile.\nIdeale per giardini fino a 400 mq.", true, 1],
        ['Smartphone 128 GB come nuovo', 'Elettronica', 340, "Smartphone da 128 GB, batteria al 94%.\nSempre usato con cover e pellicola: nessun graffio. Scatola e cavo originali.", true, 3],
        ['Cuffie wireless con cancellazione del rumore', 'Elettronica', 150, "Cuffie over-ear bluetooth con cancellazione attiva del rumore e 30 ore di autonomia.\nCustodia rigida inclusa.", true, 2],
        ['Monitor 27 pollici QHD', 'Elettronica', 210, "Monitor da 27\" con risoluzione 2560x1440 e frequenza a 144 Hz.\nNessun pixel bruciato, completo di cavi.", null, 1],
        ['Pista per macchinine con 4 auto', 'Giocattoli', 25, "Pista componibile con loop e 4 macchinine incluse.\nCompleta di tutti i pezzi, scatola originale.", true, 1],
        ['Costruzioni da 1000 pezzi', 'Giocattoli', 40, "Scatola di mattoncini da costruzione da 1000 pezzi assortiti.\nOttimo stato, pezzi lavati.", true, 2],
        ['Collezione di romanzi gialli (12 volumi)', 'Libri e Riviste', 36, "Dodici romanzi gialli in edizione tascabile.\nLetti una volta, conservati con cura.", true, 1],
        ['Enciclopedia illustrata della natura', 'Libri e Riviste', 60, "Enciclopedia in 8 volumi con illustrazioni a colori.\nPerfetta per ragazzi e appassionati.", false, 1],
        ['Casco integrale taglia M', 'Motori', 85, "Casco integrale omologato, taglia M, visiera fumé inclusa.\nMai caduto, imbottiture lavate.", true, 2],
        ['Portapacchi universale per auto', 'Motori', 55, "Barre portatutto in alluminio con chiave antifurto.\nAdatte alla maggior parte delle auto con mancorrenti.", null, 1],
        ['Asciugacapelli professionale 2200W', 'Salute e Bellezza', 40, "Asciugacapelli con motore AC, 3 temperature e 2 velocità.\nDiffusore e beccuccio inclusi.", true, 1],
        ['Spazzolino elettrico ricaricabile', 'Salute e Bellezza', 28, "Spazzolino elettrico con 3 modalità e timer.\nNuovo, mai usato: regalo doppio.", true, 1],
        ['Bicicletta da corsa in alluminio', 'Sport', 480, "Telaio in alluminio taglia 54, gruppo 2x11 e freni a disco.\nTagliandata da poco, gomme nuove.", true, 3],
        ['Tapis roulant pieghevole', 'Sport', 220, "Tapis roulant pieghevole, velocità fino a 14 km/h e inclinazione regolabile.\nPoco usato, perfettamente funzionante.", true, 2],
        ['Racchette da padel (coppia)', 'Sport', 70, "Coppia di racchette da padel in fibra di carbonio con borsa.\nUsate per una sola stagione.", false, 1],
        ['Pallone da calcio ufficiale', 'Sport', 15, "Pallone da calcio misura 5.\nNuovo, ancora con l'etichetta.", null, 1],
        ['Lampada da terra in stile industriale', 'Casa e Giardinaggio', 65, "Lampada da terra in metallo nero con paralume orientabile, altezza 160 cm.\nAttacco E27, lampadina inclusa.", true, 1],
    ];

    public function run(): void
    {
        $this->call(CategoriesSeeder::class);

        $demo = $this->user('Utente Demo', 'demo@presto.it');
        $revisor = $this->user('Revisore Demo', 'revisore@presto.it', revisor: true);
        $sellers = collect([
            $demo,
            $this->user('Giulia Romano', 'giulia.romano@presto.it'),
            $this->user('Marco Esposito', 'marco.esposito@presto.it'),
            $this->user('Sara Colombo', 'sara.colombo@presto.it'),
        ]);

        if (Article::whereIn('user_id', $sellers->pluck('id'))->exists()) {
            $this->command?->warn('Annunci demo già presenti: salto la creazione (usa migrate:fresh --seed per ripartire da zero).');
            $this->printCredentials();
            return;
        }

        $categories = Category::pluck('id', 'name');

        foreach (self::ARTICLES as $index => [$title, $category, $price, $description, $status, $photos]) {
            $createdAt = now()->subDays(count(self::ARTICLES) - $index)->subHours($index * 3 % 24);

            $article = Article::create([
                'title' => $title,
                'description' => $description,
                'price' => $price,
                'category_id' => $categories[$category],
                'user_id' => $sellers[$index % $sellers->count()]->id,
            ]);
            $article->is_accepted = $status;
            $article->created_at = $createdAt;
            $article->updated_at = $createdAt;
            $article->save();

            foreach (range(1, $photos) as $n) {
                $this->addImage($article, $category, $n);
            }
        }

        $this->command?->info(count(self::ARTICLES) . ' annunci demo creati.');
        $this->printCredentials();
    }

    private function user(string $name, string $email, bool $revisor = false): User
    {
        $user = User::firstOrNew(['email' => $email]);
        $user->forceFill([
            'name' => $name,
            'password' => Hash::make(self::PASSWORD),
            'email_verified_at' => now(),
            'is_revisor' => $revisor,
        ])->save();

        return $user;
    }

    private function addImage(Article $article, string $category, int $n): void
    {
        $disk = Storage::disk('public');
        $path = "articles/{$article->id}/demo-{$n}-" . Str::random(8) . '.jpg';
        $disk->makeDirectory(dirname($path));

        $photo = $this->realPhoto($category, $article->id + $n);
        $photo
            ? File::copy($photo, $disk->path($path))
            : $this->generateImage($disk->path($path), self::COLORS[$category] ?? [6, 182, 212], $article->id * 10 + $n);

        $image = $article->images()->create(['path' => $path]);

        // Solo le miniature con watermark: l'analisi Google Vision si lancia con "php artisan app:analyze-images"
        ResizeImage::dispatchSync($image->path, 300, 300);
        ResizeImage::dispatchSync($image->path, Image::DISPLAY_SIZE, Image::DISPLAY_SIZE);
    }

    // Foto vere opzionali in database/seeders/demo-images/{slug-categoria}/
    private function realPhoto(string $category, int $seed): ?string
    {
        $dir = database_path('seeders/demo-images/' . Str::slug($category));
        if (!is_dir($dir)) {
            return null;
        }

        $files = collect(File::files($dir))
            ->filter(fn ($file) => in_array(strtolower($file->getExtension()), ['jpg', 'jpeg', 'png', 'webp'], true))
            ->values();

        return $files->isEmpty() ? null : $files[$seed % $files->count()]->getPathname();
    }

    // Composizione astratta 1200x900 nei colori del sito: sfondo scuro, alone e anelli nel colore della categoria
    private function generateImage(string $target, array $rgb, int $seed): void
    {
        mt_srand($seed);
        [$w, $h] = [1200, 900];
        $img = imagecreatetruecolor($w, $h);

        for ($y = 0; $y < $h; $y++) {
            $t = $y / $h;
            imageline($img, 0, $y, $w, $y, imagecolorallocate($img, (int) (10 + 6 * $t), (int) (15 + 8 * $t), (int) (29 + 14 * $t)));
        }

        [$cx, $cy] = [mt_rand(420, 780), mt_rand(330, 570)];
        $this->glow($img, $cx, $cy, 460, $rgb, 0.55);
        // Secondo alone ambra, più piccolo, dalla parte opposta (stessa coppia di colori del sito)
        $this->glow($img, $w - $cx + mt_rand(-150, 150), $h - $cy + mt_rand(-100, 100), 240, [245, 158, 11], 0.35);

        imagesetthickness($img, 4);
        foreach (range(1, 4) as $i) {
            $ring = imagecolorallocatealpha($img, $rgb[0], $rgb[1], $rgb[2], 10 + $i * 18);
            imagearc($img, $cx, $cy, 150 * $i, 150 * $i, mt_rand(0, 120), mt_rand(220, 350), $ring);
        }

        foreach (range(1, 40) as $i) {
            $dot = imagecolorallocatealpha($img, 255, 255, 255, mt_rand(60, 110));
            imagefilledellipse($img, mt_rand(0, $w), mt_rand(0, $h), 4, 4, $dot);
        }

        $grid = imagecolorallocatealpha($img, 255, 255, 255, 120);
        imagesetthickness($img, 1);
        for ($x = 0; $x < $w; $x += 60) {
            imageline($img, $x, 0, $x, $h, $grid);
        }
        for ($y = 0; $y < $h; $y += 60) {
            imageline($img, 0, $y, $w, $y, $grid);
        }

        imagejpeg($img, $target, 88);
    }

    // Alone radiale: strati concentrici quasi trasparenti; la sovrapposizione li rende più intensi verso il centro
    private function glow($img, int $cx, int $cy, int $radius, array $rgb, float $centerOpacity): void
    {
        $layers = 24;
        // Opacità del singolo strato tale che al centro (tutti gli strati sovrapposti) si ottenga $centerOpacity
        $alpha = (int) round(127 * (1 - $centerOpacity) ** (1 / $layers));
        for ($i = 0; $i < $layers; $i++) {
            $r = (int) ($radius * (1 - $i / $layers));
            imagefilledellipse($img, $cx, $cy, $r * 2, $r * 2, imagecolorallocatealpha($img, $rgb[0], $rgb[1], $rgb[2], $alpha));
        }
    }

    private function printCredentials(): void
    {
        $this->command?->table(['Ruolo', 'Email', 'Password'], [
            ['Utente', 'demo@presto.it', self::PASSWORD],
            ['Revisore', 'revisore@presto.it', self::PASSWORD],
        ]);
    }
}
