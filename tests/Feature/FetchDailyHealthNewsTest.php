<?php

use App\Models\BeritaKesehatan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    config()->set('services.gemini.api_key', 'test-gemini-key');
    config()->set('services.gemini.model', 'gemini-3.8-flash');
});

test('it saves one current grounded vaccination article per Jakarta day', function () {
    $today = now('Asia/Jakarta')->toDateString();
    $sourceUrl = 'https://www.kemkes.go.id/artikel-imunisasi';
    $searchSuggestions = '<div class="search-suggestions"><a href="https://www.google.com/search?q=imunisasi">Google Search</a></div>';
    $article = [
        'found' => true,
        'title' => 'Kemenkes memperkuat imunisasi anak',
        'category' => 'Imunisasi',
        'summary' => 'Kementerian Kesehatan memperluas program imunisasi di wilayah berisiko.',
        'context' => 'Orang tua dapat berkonsultasi dengan puskesmas mengenai jadwal imunisasi.',
        'sourcePublishedDate' => $today,
        'sourceUrl' => $sourceUrl,
    ];

    Http::fake([
        'generativelanguage.googleapis.com/*' => Http::response([
            'candidates' => [[
                'content' => [
                    'parts' => [['text' => json_encode($article, JSON_THROW_ON_ERROR)]],
                ],
                'groundingMetadata' => [
                    'searchEntryPoint' => ['renderedContent' => $searchSuggestions],
                    'groundingChunks' => [[
                        'web' => [
                            'uri' => $sourceUrl,
                            'title' => 'Kementerian Kesehatan RI',
                        ],
                    ]],
                ],
            ]],
        ]),
    ]);

    $this->artisan('berita:ambil-harian')->assertExitCode(0);
    $this->artisan('berita:ambil-harian')->assertExitCode(0);

    $this->assertDatabaseHas('berita_kesehatan', [
        'title' => $article['title'],
        'source_url' => $sourceUrl,
        'source_name' => 'Kementerian Kesehatan RI',
        'search_suggestions' => $searchSuggestions,
    ]);
    expect(BeritaKesehatan::query()->count())->toBe(1)
        ->and(BeritaKesehatan::query()->firstOrFail()->published_on->toDateString())->toBe($today);

    Http::assertSentCount(1);
    Http::assertSent(fn (Request $request): bool => $request->hasHeader('x-goog-api-key', 'test-gemini-key')
        && str_contains($request->url(), '/models/gemini-3.8-flash:generateContent')
        && data_get($request->data(), 'tools.0.google_search') !== null);
});

test('it does not save a result when no new relevant article is found', function () {
    Http::fake([
        'generativelanguage.googleapis.com/*' => Http::response([
            'candidates' => [[
                'content' => [
                    'parts' => [[
                        'text' => json_encode([
                            'found' => false,
                            'title' => '',
                            'category' => '',
                            'summary' => '',
                            'context' => '',
                            'sourcePublishedDate' => '',
                            'sourceUrl' => '',
                        ], JSON_THROW_ON_ERROR),
                    ]],
                ],
            ]],
        ]),
    ]);

    $this->artisan('berita:ambil-harian')
        ->expectsOutput('No new vaccination-related news was found today.')
        ->assertExitCode(0);

    expect(BeritaKesehatan::query()->count())->toBe(0);
});

test('it does not call Gemini when the API key is missing', function () {
    config()->set('services.gemini.api_key', null);

    $this->artisan('berita:ambil-harian')
        ->expectsOutput('GEMINI_API_KEY is not configured.')
        ->assertExitCode(1);

    Http::assertNothingSent();
});

test('it refuses to save results without vaccination-related keywords', function () {
    $today = now('Asia/Jakarta')->toDateString();
    $sourceUrl = 'https://www.kemkes.go.id/artikel-kesehatan';

    Http::fake([
        'generativelanguage.googleapis.com/*' => Http::response([
            'candidates' => [[
                'content' => [
                    'parts' => [[
                        'text' => json_encode([
                            'found' => true,
                            'title' => 'Informasi kesehatan anak',
                            'category' => 'Kesehatan',
                            'summary' => 'Kemenkes membagikan panduan menjaga kesehatan keluarga.',
                            'context' => 'Ikuti saran dari tenaga kesehatan.',
                            'sourcePublishedDate' => $today,
                            'sourceUrl' => $sourceUrl,
                        ], JSON_THROW_ON_ERROR),
                    ]],
                ],
                'groundingMetadata' => [
                    'searchEntryPoint' => [
                        'renderedContent' => '<div><a href="https://www.google.com/search?q=kesehatan">Google Search</a></div>',
                    ],
                    'groundingChunks' => [[
                        'web' => [
                            'uri' => $sourceUrl,
                            'title' => 'Kementerian Kesehatan RI',
                        ],
                    ]],
                ],
            ]],
        ]),
    ]);

    $this->artisan('berita:ambil-harian')
        ->expectsOutput('Gemini returned an article without a vaccination-related keyword; it was not saved.')
        ->assertExitCode(0);

    expect(BeritaKesehatan::query()->count())->toBe(0);
});
