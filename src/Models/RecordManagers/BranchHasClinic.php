<?php

namespace Hubleto\App\External\WaiBlue\HlthcrBranches\Models\RecordManagers;

use Hubleto\App\External\WaiBlue\HlthcrCore\Models\RecordManagers\Clinic;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BranchHasClinic extends \Hubleto\Erp\RecordManager
{
  use Traits\BelongsToBranch;

  public $table = 'hlthcr_branch_has_clinics';

  public function BRANCH(): BelongsTo
  {
    return $this->belongsTo(Branch::class, 'id_branch', 'id');
  }

  public function CLINIC(): BelongsTo
  {
    return $this->belongsTo(Clinic::class, 'id_clinic', 'id');
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
