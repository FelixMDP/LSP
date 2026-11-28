<?php

namespace App\Console\Commands;

use App\Models\BeritaKesehatan;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use UnexpectedValueException;

class FetchDailyHealthNews extends Command
{
    protected $signature = 'berita:ambil-harian';

    protected $description = 'Fetch and save one grounded vaccination news article per Jakarta day.';

    private const TIMEZONE = 'Asia/Jakarta';

    private const RELEVANT_KEYWORDS = [
        'vaksin',
        'imunisasi',
        'vaccine',
        'vaccination',
    ];

    public function handle(): int
    {
        $today = CarbonImmutable::now(self::TIMEZONE)->startOfDay();

        if (BeritaKesehatan::query()->whereDate('published_on', $today)->exists()) {
            $this->info("A health news article is already saved for {$today->toDateString()}.");

            return self::SUCCESS;
        }

        $apiKey = config('services.gemini.api_key');
        $model = config('services.gemini.model');

        if (! \is_string($apiKey) || trim($apiKey) === '') {
            $this->error('GEMINI_API_KEY is not configured.');

            return self::FAILURE;
        }

        if (! \is_string($model) || preg_match('/^[a-zA-Z0-9._-]+$/', $model) !== 1) {
            $this->error('GEMINI_MODEL is missing or invalid.');

            return self::FAILURE;
        }

        $response = Http::acceptJson()
            ->withHeaders(['x-goog-api-key' => $apiKey])
            ->timeout(60)
            ->post(
                "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent",
                $this->requestPayload($today)
            )
            ->throw();

        $candidate = data_get($response->json(), 'candidates.0');

        if (! \is_array($candidate)) {
            throw new UnexpectedValueException('Gemini did not return a response candidate.');
        }

        $text = collect(data_get($candidate, 'content.parts', []))
            ->pluck('text')
            ->filter(fn (mixed $part): bool => \is_string($part))
            ->implode("\n");

        if ($text === '') {
            throw new UnexpectedValueException('Gemini returned no structured news content.');
        }

        $article = json_decode($text, true, 512, JSON_THROW_ON_ERROR);

        if (! \is_array($article) || ! \is_bool($article['found'] ?? null)) {
            throw new UnexpectedValueException('Gemini returned an invalid news response.');
        }

        if (! $article['found']) {
            $this->info('No new vaccination-related news was found today.');

            return self::SUCCESS;
        }

        $article = Validator::make($article, [
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:100'],
            'summary' => ['required', 'string', 'max:3000'],
            'context' => ['required', 'string', 'max:2000'],
            'sourcePublishedDate' => ['required', 'date_format:Y-m-d'],
            'sourceUrl' => ['required', 'url'],
        ])->validate();

        $searchableText = Str::lower(implode(' ', [
            $article['title'],
            $article['category'],
            $article['summary'],
        ]));

        if (! Str::contains($searchableText, self::RELEVANT_KEYWORDS)) {
            $this->warn('Gemini returned an article without a vaccination-related keyword; it was not saved.');

            return self::SUCCESS;
        }

        $sourcePublishedAt = CarbonImmutable::createFromFormat(
            '!Y-m-d',
            $article['sourcePublishedDate'],
            self::TIMEZONE
        );

        if (
            $sourcePublishedAt->gt($today)
            || $sourcePublishedAt->lt($today->subDay())
        ) {
            $this->warn('The article is outside the accepted publication-date window; it was not saved.');

            return self::SUCCESS;
        }

        $groundedSource = collect(data_get($candidate, 'groundingMetadata.groundingChunks', []))
            ->map(fn (array $chunk): ?array => $chunk['web'] ?? null)
            ->first(fn (?array $source): bool => ($source['uri'] ?? null) === $article['sourceUrl']);

        if (
            $groundedSource === null
            || parse_url($article['sourceUrl'], PHP_URL_SCHEME) !== 'https'
            || ! \is_string($groundedSource['title'] ?? null)
            || trim($groundedSource['title']) === ''
        ) {
            throw new UnexpectedValueException('The article URL could not be verified against Gemini Search grounding.');
        }

        $searchSuggestions = data_get($candidate, 'groundingMetadata.searchEntryPoint.renderedContent');

        if (! $this->isSafeSearchSuggestions($searchSuggestions)) {
            throw new UnexpectedValueException('Gemini Search Suggestions are missing or contain unsafe markup.');
        }

        if (BeritaKesehatan::query()->where('source_url', $article['sourceUrl'])->exists()) {
            $this->warn('Gemini found an article that is already in the news archive; it was not duplicated.');

            return self::SUCCESS;
        }

        $savedArticle = BeritaKesehatan::query()->firstOrCreate(
            ['published_on' => $today->toDateString()],
            [
                'category' => $article['category'],
                'title' => $article['title'],
                'summary' => $article['summary'],
                'context' => $article['context'],
                'source_name' => Str::limit(trim($groundedSource['title']), 255, ''),
                'source_url' => $article['sourceUrl'],
                'search_suggestions' => $searchSuggestions,
                'source_published_at' => $sourcePublishedAt->toDateString(),
            ]
        );

        if (! $savedArticle->wasRecentlyCreated) {
            $this->info("A health news article is already saved for {$today->toDateString()}.");

            return self::SUCCESS;
        }

        $this->info("Saved vaccination-related news for {$today->toDateString()}.");

        return self::SUCCESS;
    }

    private function isSafeSearchSuggestions(mixed $searchSuggestions): bool
    {
        if (! \is_string($searchSuggestions) || trim($searchSuggestions) === '' || \strlen($searchSuggestions) > 20000) {
            return false;
        }

        $decodedMarkup = html_entity_decode($searchSuggestions, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return preg_match(
            '/<\s*(script|iframe|object|embed)\b|[\s\/]on[a-z0-9_-]+\s*=|(?:javascript|vbscript)\s*:|(?:href|src)\s*=\s*["\']?\s*data:/i',
            $decodedMarkup
        ) !== 1;
    }

    /**
     * @return array<string, mixed>
     */
    private function requestPayload(CarbonImmutable $today): array
    {
        $prompt = implode("\n", [
            "Tanggal Indonesia (Asia/Jakarta): {$today->toDateString()}.",
            'Cari dengan Google Search satu berita baru yang terbit hari ini atau kemarin dan secara jelas membahas vaksin, vaksinasi, atau imunisasi.',
            'Utamakan sumber resmi kesehatan (Kementerian Kesehatan, WHO, UNICEF, BPOM) atau media tepercaya di Indonesia.',
            'Ringkas dengan bahasa Indonesia dalam 2-3 kalimat, jangan menyalin artikel penuh, jangan membuat klaim diagnosis atau sebab-akibat pada individu, dan sertakan konteks edukatif yang aman.',
            'sourceUrl harus sama persis dengan URL dari salah satu sumber hasil Google Search. Jangan membuat atau menebak URL.',
            'Jika tidak menemukan berita yang relevan dan baru, isi found=false dan string lainnya dengan nilai kosong.',
            'Kembalikan hanya objek JSON sesuai skema.',
        ]);

        return [
            'contents' => [
                ['parts' => [['text' => $prompt]]],
            ],
            'tools' => [
                ['google_search' => new \stdClass],
            ],
            'generationConfig' => [
                'responseMimeType' => 'application/json',
                'responseJsonSchema' => [
                    'type' => 'OBJECT',
                    'properties' => [
                        'found' => ['type' => 'BOOLEAN'],
                        'title' => ['type' => 'STRING'],
                        'category' => ['type' => 'STRING'],
                        'summary' => ['type' => 'STRING'],
                        'context' => ['type' => 'STRING'],
                        'sourcePublishedDate' => ['type' => 'STRING'],
                        'sourceUrl' => ['type' => 'STRING'],
                    ],
                    'required' => [
                        'found',
                        'title',
                        'category',
                        'summary',
                        'context',
                        'sourcePublishedDate',
                        'sourceUrl',
                    ],
                ],
            ],
        ];
    }
}
