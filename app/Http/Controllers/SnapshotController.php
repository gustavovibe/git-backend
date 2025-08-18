<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\TourSnapshot;

class SnapshotController extends Controller
{
    /**
     * GET /api/snapshots
     * Optional query parameters:
     *   - categories (comma separated list of tour_type_ids e.g. 4,189)
     *   - page (for pagination)
     *   - per_page (number of items per page)
     *
     * Returns paginated snapshots with all DB columns.
     */
    public function index(Request $request)
    {
        $categoriesParam = $request->query('categories');
        $perPage = (int) $request->query('per_page', 50);
        $page = (int) $request->query('page', 1);

        // Base query
        $query = TourSnapshot::query();

        // If categories provided, try to filter
        if ($categoriesParam) {
            $rawCats = array_filter(array_map('trim', explode(',', $categoriesParam)), fn($c) => $c !== '');
            $categories = array_values($rawCats);

            if (!empty($categories)) {
                // Attempt a DB-level JSON search: JSON_SEARCH(payload, 'one', '4') IS NOT NULL
                // This is a broad search (may match numbers elsewhere in JSON), but is fast.
                $query->where(function ($q) use ($categories) {
                    foreach ($categories as $cat) {
                        // use parameter binding to avoid injection
                        $q->orWhereRaw("JSON_SEARCH(payload, 'one', ?) IS NOT NULL", [$cat]);
                    }
                });

                // NOTE: If you need exact matching against $.type[*].tour_type_id or $.type[*].type.tour_type_id,
                // we could perform more precise JSON_EXTRACT/JSON_CONTAINS checks, but the shape may vary
                // so the above is intentionally tolerant.
            }
        }

        // Apply simple pagination at DB level
        $paginator = $query->orderBy('id', 'asc')->paginate($perPage, ['*'], 'page', $page);

        // If you provided categories and want a stricter (PHP) filter to avoid accidental matches,
        // uncomment the block below. It will re-filter the paged results in PHP (less efficient).
        /*
        if ($categoriesParam) {
            $cats = $categories;
            $items = collect($paginator->items())->filter(function ($row) use ($cats) {
                $payload = is_array($row['payload']) ? $row['payload'] : (array) $row['payload'];
                return $this->payloadMatchesCategories($payload, $cats);
            })->values()->all();

            // Replace paginator items with the filtered items (note: total won't reflect the stricter filter)
            $paginator->setCollection(collect($items));
        }
        */

        // Build response
        $response = [
            'data' => $paginator->items(),          // contains rows with all DB columns
            'total' => $paginator->total(),
            'per_page' => $paginator->perPage(),
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
        ];

        return response()->json($response);
    }

    /**
     * Strict payload check: inspects $payload['type'] to see if any tour_type_id / type.tour_type_id matches
     * (used only if you prefer PHP-level exact filtering).
     */
    protected function payloadMatchesCategories(array $payload, array $categories): bool
    {
        if (empty($payload) || empty($categories)) {
            return false;
        }

        $types = $payload['type'] ?? [];
        foreach ($types as $t) {
            // top-level tour_type_id
            if (isset($t['tour_type_id']) && in_array((string)$t['tour_type_id'], $categories, true)) {
                return true;
            }

            // nested path: type.tour_type_id
            $nested = $t['type'] ?? null;
            if (is_array($nested) && isset($nested['tour_type_id']) && in_array((string)$nested['tour_type_id'], $categories, true)) {
                return true;
            }
        }

        return false;
    }
}
