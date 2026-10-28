<?php

namespace Hubleto\App\External\WaiBlue\HlthcrBranches\Models\RecordManagers;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Job extends \Hubleto\Erp\RecordManager
{
  use Traits\BelongsToBranch;

  public $table = 'hlthcr_jobs';

  public function BRANCH(): BelongsTo
  {
    return $this->belongsTo(Branch::class, 'id_branch', 'id');
  }

  public function HAZARD_LEVELS(): HasMany
  {
    return $this->hasMany(JobHasHazard::class, 'id_job', 'id');
  }

  public function prepareReadQuery(mixed $query = null, int $level = 0, array|null $includeRelations = null): mixed
  {
    $query = parent::prepareReadQuery($query, $level, $includeRelations);

    return $this->restrictToVisibleBranches($query);
  }

  public function prepareLookupQuery(string $search): mixed
  {
    $query = parent::prepareLookupQuery($search);

    $query = $this->restrictToVisibleBranches($query);

    // The employee form sends idBranch, so it offers only jobs of the employee's branch.
    return $this->filterByBranchFromUrl($query);
  }

  public function addUrlFiltersToQuery(mixed $query): mixed
  {
    $query = parent::addUrlFiltersToQuery($query);

    return $this->filterByBranchFromUrl($query);
  }

  public function prepareLookupData(array $dataRaw): array
  {
    return parent::prepareLookupData($dataRaw);
  }
}
