# Foto per i dati demo (facoltative)

Il `DemoSeeder` usa le foto di questa cartella se esistono; altrimenti genera immagini astratte nei colori del sito.

Crea una sottocartella per categoria e mettici file `.jpg`, `.png` o `.webp` (meglio se orizzontali, almeno 1200×900):

- `abbigliamento/` → Abbigliamento
- `accessori/` → Accessori
- `animali-domestici/` → Animali domestici
- `casa-e-giardinaggio/` → Casa e Giardinaggio
- `elettronica/` → Elettronica
- `giocattoli/` → Giocattoli
- `libri-e-riviste/` → Libri e Riviste
- `motori/` → Motori
- `salute-e-bellezza/` → Salute e Bellezza
- `sport/` → Sport

Le foto vengono assegnate a rotazione agli annunci di quella categoria e passano dal normale ridimensionamento con watermark.
Usa solo immagini di cui hai i diritti (ad esempio scatti tuoi o foto con licenza libera come Unsplash/Pexels).

Poi rigenera i dati: `php artisan migrate:fresh --seed` (cancella il database!) oppure, su un database con le sole categorie, `php artisan db:seed --class=DemoSeeder`.
