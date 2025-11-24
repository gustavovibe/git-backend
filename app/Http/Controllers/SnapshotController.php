<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\TourSnapshot;
use Illuminate\Support\Facades\DB;

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
        $userId = $request->query('userId');
        $perPage = (int) $request->query('per_page', 50);
        $page = (int) $request->query('page', 1);
    
        $query = TourSnapshot::query();
    
        // If categories provided, parse them into ints and filter by the integer `type` column
        if ($categoriesParam) {
            $raw = array_filter(array_map('trim', explode(',', $categoriesParam)), fn($c) => $c !== '');
            // cast to ints and remove non-numeric values
            $categories = array_values(array_filter(array_map(function ($v) {
                return is_numeric($v) ? (int)$v : null;
            }, $raw)));
    
            if (!empty($categories)) {
                // Exact match against the integer `type` column
                $query->whereIn('type', $categories);
            }
        }
        if(!empty($userId)){
            $query->leftJoin('wishlists', function ($join) use ($userId) {
                    $join->on('wishlists.tour_id', '=', 'tour_snapshots.tour_id')
                        ->where('wishlists.user_id', '=', $userId);
                })
                ->addSelect(DB::raw('CASE WHEN wishlists.id IS NULL THEN 0 ELSE 1 END AS is_favorite, tour_snapshots.*'));
        } else {
            $query->addSelect(DB::raw('0 AS is_favorite, tour_snapshots.*'));
        }
    
        $paginator = $query->orderBy('tour_snapshots.id', 'asc')->paginate($perPage, ['*'], 'page', $page);
    
        // OPTIONAL: if you want a fallback to JSON payload search when `type` column is NULL,
        // uncomment the block below. Note: this is less efficient and may return duplicates.
        /*
        if (!empty($categories) && $paginator->isEmpty()) {
            // do a DB-level JSON search as fallback (broad)
            $query = TourSnapshot::query();
            $query->where(function ($q) use ($categories) {
                foreach ($categories as $cat) {
                    $q->orWhereRaw("JSON_SEARCH(payload, 'one', ?) IS NOT NULL", [(string)$cat]);
                }
            });
            $paginator = $query->orderBy('id', 'asc')->paginate($perPage, ['*'], 'page', $page);
        }
        */
    
        $response = [
            'data' => $paginator->items(),
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
