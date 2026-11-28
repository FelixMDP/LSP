<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property Carbon $published_on
 * @property string $category
 * @property string $title
 * @property string $summary
 * @property string $context
 * @property string $source_name
 * @property string $source_url
 * @property string|null $search_suggestions
 * @property Carbon|null $source_published_at
 */
class BeritaKesehatan extends Model
{
    protected $table = 'berita_kesehatan';

    protected $fillable = [
        'published_on',
        'category',
        'title',
        'summary',
        'context',
        'source_name',
        'source_url',
        'search_suggestions',
        'source_published_at',
    ];

    protected function casts(): array
    {
        return [
            'published_on' => 'date',
            'source_published_at' => 'date',
        ];
    }

    /**
     * @return array{
     *     kategori: string,
     *     tanggal: string,
     *     judul: string,
     *     ringkasan: string,
     *     konteks: string,
     *     sumber: string,
     *     url: string,
     *     searchSuggestions: string|null
     * }
     */
    public function toCardData(): array
    {
        return [
            'kategori' => $this->category,
            'tanggal' => ($this->source_published_at ?? $this->published_on)->toDateString(),
            'judul' => $this->title,
            'ringkasan' => $this->summary,
            'konteks' => $this->context,
            'sumber' => $this->source_name,
            'url' => $this->source_url,
            'searchSuggestions' => $this->search_suggestions,
        ];
    }
}
