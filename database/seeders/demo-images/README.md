# Foto per i dati demo

Il `DemoSeeder` sceglie la foto di ogni annuncio in quest'ordine:

1. **`{slug-del-titolo}.jpg`** (più `-2.jpg`, `-3.jpg` per altre foto dello stesso annuncio): per esempio
   `bicicletta-da-corsa-in-alluminio.jpg`. Le 24 foto presenti vengono da Wikimedia Commons con licenze libere:
   autori e licenze in [`CREDITS.md`](CREDITS.md).
2. **Una sottocartella per categoria** (`elettronica/`, `sport/`, …) con file `.jpg`, `.png` o `.webp`: le foto
   vengono assegnate a rotazione agli annunci di quella categoria.
3. Se non trova nulla, **genera un'immagine astratta** nei colori del sito.

Tutte passano dal normale ridimensionamento con watermark. Usa solo immagini di cui hai i diritti.

Per rigenerare i dati demo: `composer run demo` (cancella il database e le foto caricate).
