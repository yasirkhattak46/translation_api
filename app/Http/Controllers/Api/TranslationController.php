<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Translation;
use App\Services\TranslationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TranslationController extends Controller
{
    public function __construct(protected TranslationService $service)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = (int)$request->get('per_page', 25);
        $result = $this->service->search($request->only(['locale', 'tag', 'q', 'key']), $perPage);
        return response()->json($result);
    }

    public function show(int $id): JsonResponse
    {
        $t = $this->service->getById($id);
        return response()->json($t);
    }

    public function store(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'key' => 'required|string|max:191',
            'locale' => 'required|string|max:10',
            'content' => 'required|string',
            'tags' => 'sometimes|array',
            'tags.*' => 'string',
            'meta' => 'sometimes|array',
        ]);

        $translation = $this->service->create($payload);
        return response()->json($translation, 201);
    }

    public function update(Request $request, Translation $translation): JsonResponse
    {
        $payload = $request->validate([
            'content' => 'sometimes|string',
            'tags' => 'sometimes|array',
            'tags.*' => 'string',
            'meta' => 'sometimes|array',
        ]);

        $updated = $this->service->update($translation, $payload);
        return response()->json($updated);
    }

    public function destroy(Translation $translation): JsonResponse
    {
        $translation->delete();
        $this->service->invalidateExportCache($translation->locale->code);
        return response()->json(null, 204);
    }

    public function search(Request $request): JsonResponse
    {
        $perPage = (int)$request->get('per_page', 25);
        $result = $this->service->search($request->only(['locale', 'tag', 'q', 'key']), $perPage);
        return response()->json($result);
    }

    public function export(Request $request, string $locale_id): JsonResponse
    {
        $cacheKey = "translations_export_{$locale_id}";

        // 1️⃣ Try to serve from cache
        if (Cache::has($cacheKey)) {
            return response()->json(Cache::get($cacheKey), 200, [
                'Cache-Control' => 'no-store',
            ]);
        }

        // 2️⃣ Get translations as a lazy collection
        $translations = DB::table('translations')
            ->where('locale_id', $locale_id)
            ->select('key', 'content')
            ->orderBy('id')
            ->lazy();

        // 3️⃣ Create the JSON array (using chunking for memory efficiency)
        $data = [];
        foreach ($translations as $row) {
            $data[] = [
                'key' => $row->key,
                'content' => $row->content,
            ];
        }

        // Optional: store in cache for future requests
        Cache::put($cacheKey, $data, now()->addMinutes(10));

        // 4️⃣ Return a clean JSON response
        return response()->json($data, 200, [
            'Cache-Control' => 'no-store',
        ]);

    }
}
