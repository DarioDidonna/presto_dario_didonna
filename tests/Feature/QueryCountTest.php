<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class QueryCountTest extends TestCase
{
    use RefreshDatabase;

    private const MAX_QUERIES = 12;

    protected function setUp(): void
    {
        parent::setUp();
        $categories = Category::factory()->count(3)->create();
        foreach (range(1, 10) as $n) {
            $article = Article::factory()
                ->for($categories[$n % 3])
                ->state(['title' => "Articolo numero $n", 'is_accepted' => $n === 10 ? null : true])
                ->create();
            $article->images()->create(['path' => "articles/{$article->id}/a.jpg"]);
        }
    }

    public static function pages(): array
    {
        return [
            'homepage' => ['/'],
            'indice' => ['/article/index'],
            'categoria' => ['/category/2'],
            'ricerca' => ['/article/index?query=Articolo&min_price=1&sort=price_desc'],
            'dettaglio' => ['/show/article/1'],
            'revisore' => ['/revisor/index'],
        ];
    }

    #[DataProvider('pages')]
    public function test_page_query_count_does_not_grow_with_articles(string $url): void
    {
        $revisor = User::factory()->revisor()->create();

        DB::enableQueryLog();
        $this->actingAs($revisor)->get($url)->assertOk();
        $queries = array_column(DB::getQueryLog(), 'query');
        // Il driver Scout "collection" (solo test) chiama toSearchableArray() su ogni articolo:
        // quelle query non esistono in produzione con TNTSearch, quindi non le contiamo
        if (str_contains($url, 'query=')) {
            $firstEngineQuery = array_search('select * from "articles" order by "articles"."id" desc', $queries);
            $queries = array_values(array_filter(
                $queries,
                fn ($q, $i) => ! ($i > $firstEngineQuery && str_contains($q, '"categories"."id" = ? limit 1')),
                ARRAY_FILTER_USE_BOTH
            ));
        }
        $count = count($queries);

        $this->assertLessThanOrEqual(self::MAX_QUERIES, $count);
    }
}
