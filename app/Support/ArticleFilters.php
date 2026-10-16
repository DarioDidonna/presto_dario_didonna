<?php

namespace App\Support;

use App\Models\Article;
use App\Models\Category;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * Filtri dell'elenco annunci (testo, categoria, prezzo, ordinamento) letti dalla query string.
 * I valori non validi vengono ignorati invece di generare errori: è un form di ricerca in GET.
 */
class ArticleFilters
{
    public const SORTS = ['relevance', 'newest', 'price_asc', 'price_desc'];

    private const MAX_PRICE = 999999.99;

    public function __construct(
        public readonly ?string $query = null,
        public readonly ?int $category = null,
        public readonly ?float $minPrice = null,
        public readonly ?float $maxPrice = null,
        public readonly string $sort = 'newest',
    ) {}

    public static function fromRequest(Request $request): self
    {
        $query = $request->input('query');
        // Serve almeno una lettera o una cifra: una ricerca di soli simboli ("%", "_") non ha senso
        $query = is_string($query) && preg_match('/[\p{L}\p{N}]/u', $query) ? mb_substr(trim($query), 0, 100) : null;

        $category = filter_var($request->input('category'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $category = $category !== false && Category::whereKey($category)->exists() ? $category : null;

        $minPrice = self::price($request->input('min_price'));
        $maxPrice = self::price($request->input('max_price'));
        if ($minPrice !== null && $maxPrice !== null && $minPrice > $maxPrice) {
            [$minPrice, $maxPrice] = [$maxPrice, $minPrice];
        }

        // Senza testo da cercare la "pertinenza" non ha senso: si ordina per data
        $sort = in_array($request->input('sort'), self::SORTS, true) ? $request->input('sort') : null;
        $sort ??= $query !== null ? 'relevance' : 'newest';
        if ($sort === 'relevance' && $query === null) {
            $sort = 'newest';
        }

        return new self($query, $category, $minPrice, $maxPrice, $sort);
    }

    private static function price(mixed $value): ?float
    {
        if (! is_scalar($value) || $value === '') {
            return null;
        }
        // Accetta anche la virgola decimale (es. "12,50")
        $price = filter_var(str_replace(',', '.', (string) $value), FILTER_VALIDATE_FLOAT);

        return $price !== false && $price >= 0 ? min($price, self::MAX_PRICE) : null;
    }

    public function isFiltered(): bool
    {
        return $this->query !== null || $this->category !== null || $this->minPrice !== null || $this->maxPrice !== null;
    }

    /**
     * Annunci pubblicati che rispettano i filtri. Il testo è cercato con Scout, che restituisce solo gli ID:
     * filtri, ordinamento e paginazione restano al database, così conteggi e pagine sono sempre corretti.
     */
    public function query(): Builder
    {
        $builder = Article::accepted()->withCardRelations();

        $ids = [];
        if ($this->query !== null) {
            $ids = $this->matchingIds();
            $builder->whereKey($ids);
        }

        $builder
            ->when($this->category, fn ($q) => $q->where('category_id', $this->category))
            ->when($this->minPrice !== null, fn ($q) => $q->where('price', '>=', $this->minPrice))
            ->when($this->maxPrice !== null, fn ($q) => $q->where('price', '<=', $this->maxPrice));

        return match ($this->sort) {
            'price_asc' => $builder->orderBy('price')->latest(),
            'price_desc' => $builder->orderByDesc('price')->latest(),
            'relevance' => $this->orderByRelevance($builder, $ids),
            default => $builder->latest(),
        };
    }

    // Parole che compaiono quasi ovunque: cercandole ("bici DA corsa") si otteneva mezzo catalogo
    private const STOPWORDS = [
        'il', 'lo', 'la', 'le', 'gli', 'un', 'uno', 'una', 'di', 'da', 'in', 'con', 'su', 'per', 'tra', 'fra', 'ed',
        'del', 'della', 'dei', 'degli', 'delle', 'al', 'alla', 'ai', 'agli', 'alle', 'dal', 'dalla', 'nel', 'nella',
        'sul', 'sulla', 'che', 'non', 'come', 'cerco', 'vorrei', 'voglio',
        'the', 'an', 'of', 'for', 'to', 'with', 'and', 'or', 'on', 'my', 'some',
        'el', 'los', 'las', 'de', 'para', 'mi', 'busco',
    ];

    /**
     * Parole significative del testo cercato (minuscole, senza parole vuote, al massimo 6).
     *
     * @return list<string>
     */
    private function keywords(): array
    {
        $words = preg_split('/[^\p{L}\p{N}]+/u', mb_strtolower((string) $this->query), -1, PREG_SPLIT_NO_EMPTY);

        return array_slice(array_values(array_unique(array_filter(
            $words,
            fn (string $word) => mb_strlen($word) >= 2 && ! in_array($word, self::STOPWORDS, true),
        ))), 0, 6);
    }

    /**
     * ID degli annunci che corrispondono a TUTTE le parole significative, in ordine di pertinenza.
     * Per ogni parola: indice TNTSearch (testo, descrizione, categoria, con tolleranza ai refusi) oppure titolo
     * che la contiene (TNTSearch confronta parole intere: "bici" non trova "bicicletta").
     */
    private function matchingIds(): array
    {
        $keywords = $this->keywords();
        if ($keywords === []) {
            return [];
        }

        $perWord = array_map(function (string $word) {
            $fromIndex = Article::search($word)->keys()->map(fn ($id) => (int) $id);
            $fromTitle = Article::accepted()
                ->where('title', 'like', '%'.addcslashes($word, '\\%_').'%')
                ->limit(500)
                ->pluck('id');

            return $fromIndex->merge($fromTitle)->unique()->all();
        }, $keywords);

        $matching = array_values(array_intersect(...$perWord));

        // Nessun annuncio con tutte le parole ("oggetti per cani"): quelli con più parole in comune, prima i migliori
        if ($matching === [] && count($perWord) > 1) {
            $hits = array_count_values(array_merge(...$perWord));
            arsort($hits);
            $matching = array_keys($hits);
        }

        // Ordine: prima quello che il motore di ricerca considera più pertinente per l'intera frase
        $ranking = Article::search(implode(' ', $keywords))->keys()->map(fn ($id) => (int) $id)->all();

        return array_values(array_unique([...array_intersect($ranking, $matching), ...$matching]));
    }

    // Stesso ordine restituito dal motore di ricerca (CASE portabile tra MySQL e SQLite)
    private function orderByRelevance(Builder $builder, array $ids): Builder
    {
        if ($ids === []) {
            return $builder->latest();
        }

        $cases = implode(' ', array_map(fn ($position) => "WHEN ? THEN {$position}", array_keys($ids)));

        return $builder->orderByRaw("CASE id {$cases} END", $ids);
    }

    // Parametri da riportare nei link (paginazione, "azzera"), senza i valori vuoti
    public function toArray(): array
    {
        return array_filter([
            'query' => $this->query,
            'category' => $this->category,
            'min_price' => $this->minPrice,
            'max_price' => $this->maxPrice,
            'sort' => $this->sort,
        ], fn ($value) => $value !== null);
    }
}
