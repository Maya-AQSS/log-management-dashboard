<?php

namespace App\Repositories\Eloquent;

use App\Models\ErrorCode;
use App\Repositories\Contracts\ErrorCodeRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Support\LikeEscaper;
use Illuminate\Support\Facades\DB;

class ErrorCodeRepository implements ErrorCodeRepositoryInterface
{
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return ErrorCode::query()
            ->with('application')
            ->withCount(['logs', 'archivedLogs', 'comments'])
            ->orderBy('code')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function searchAndFilter(
        ?string $search,
        ?int $filterApp,
        ?string $severity,
        int $perPage = 15
    ): LengthAwarePaginator {
        $driver = DB::connection()->getDriverName();
        $escapedSearch = $search !== null && trim($search) !== ''
            ? LikeEscaper::escapeLikePattern(trim($search))
            : null;

        return ErrorCode::query()
            ->with('application')
            ->withCount(['logs', 'archivedLogs', 'comments'])
            ->when($escapedSearch !== null, function ($query) use ($driver, $escapedSearch) {
                $pattern = "%{$escapedSearch}%";
                if ($driver === 'pgsql') {
                    $query->where(function ($query) use ($pattern) {
                        $query->whereRaw("code ILIKE ? ESCAPE '" . LikeEscaper::LIKE_ESCAPE_CHARACTER . "'", [$pattern])
                              ->orWhereRaw("name ILIKE ? ESCAPE '" . LikeEscaper::LIKE_ESCAPE_CHARACTER . "'", [$pattern]);
                    });
                } else {
                    $query->where(function ($query) use ($search) {
                        $query->where('code', 'LIKE', "%{$search}%")
                              ->orWhere('name', 'LIKE', "%{$search}%");
                    });
                }
            })
            ->when($filterApp, fn ($query, $filterApp) => $query->where('application_id', $filterApp))
            ->when($severity, fn ($q) => $q->where('severity', $severity))
            ->orderBy('code')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function findOrFail(int $id): ErrorCode
    {
        return ErrorCode::query()
            ->with('application')
            ->findOrFail($id);
    }

    public function create(array $data): ErrorCode
    {
        return ErrorCode::query()->create($data);
    }

    public function update(ErrorCode $errorCode, array $data): ErrorCode
    {
        $errorCode->fill($data);
        $errorCode->save();

        return $errorCode;
    }

    public function delete(ErrorCode $errorCode): void
    {
        $errorCode->delete();
    }
}
