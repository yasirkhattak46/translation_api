<?php

namespace App\Services;

use App\Models\Locale;
use App\Models\Tag;
use App\Models\Translation;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class TranslationService
{
    public function __construct(protected Cache $cache) {}

    public function create(array $data): Translation
    {
        return DB::transaction(function () use ($data) {
            $locale = Locale::firstOrCreate(['code' => $data['locale'] ?? 'en'], ['name' => $data['locale'] ?? 'en']);

            $translation = Translation::create([
                'key' => $data['key'],
                'locale_id' => $locale->id,
                'content' => $data['content'],
                'meta' => $data['meta'] ?? null,
            ]);

            if (!empty($data['tags'])) {
                $tagIds = collect($data['tags'])->map(fn($slug) => Tag::firstOrCreate(['slug' => $slug])->id)->all();
                $translation->tags()->sync($tagIds);
            }

            $this->invalidateExportCache($locale->code);

            return $translation;
        });
    }

    public function update(Translation $translation, array $data): Translation
    {
        return DB::transaction(function () use ($translation, $data) {
            if (isset($data['content'])) {
                $translation->content = $data['content'];
            }
            if (isset($data['meta'])) {
                $translation->meta = $data['meta'];
            }
            $translation->save();

            if (isset($data['tags'])) {
                $tagIds = collect($data['tags'])->map(fn($slug) => Tag::firstOrCreate(['slug' => $slug])->id)->all();
                $translation->tags()->sync($tagIds);
            }

            $this->invalidateExportCache($translation->locale->code);

            return $translation->fresh();
        });
    }

    public function getById(int $id): ?Translation
    {
        return Translation::with(['locale','tags'])->find($id);
    }

    public function search(array $filters, int $perPage = 25): LengthAwarePaginator
    {
        $query = Translation::query()->with('tags','locale');

        if (!empty($filters['key'])) {
            $q = $filters['q'];
            $query->where(function ($sub) use ($q) {
                $sub->where('key', 'like', "%{$q}%")
                    ->orWhere('content', 'like', "%{$q}%");
            });
        }

        if (!empty($filters['locale'])) {
            $locale = Locale::where('code', $filters['locale'])->first();
            if ($locale) {
                $query->where('locale_id', $locale->id);
            }
        }

        if (!empty($filters['tag'])) {
            $query->whereHas('tags', function($q) use ($filters) {
                $q->where('slug', $filters['tag']);
            });
        }

        if (!empty($filters['q'])) {
            $q = $filters['q'];
            $query->where(function ($sub) use ($q) {
                $sub->where('key', 'like', "%{$q}%")
                    ->orWhere('content', 'like', "%{$q}%");
            });
        }

        return $query->orderBy('key')->paginate($perPage);
    }

    public function invalidateExportCache(string $localeCode): void
    {
        $this->cache->forget("translations_export:{$localeCode}");
    }

    public function streamTranslationsByLocale(string $localeCode)
    {
        $locale = Locale::where('code', $localeCode)->firstOrFail();

        return Translation::where('locale_id', $locale->id)
            ->select(['key', 'content'])
            ->orderBy('key')
            ->cursor();
    }
}
