<?php

namespace Hubleto\App\External\WaiBlue\HlthcrBranches\Models\RecordManagers;

use Hubleto\App\External\WaiBlue\HlthcrBranches\Services\BranchScope;
use Hubleto\App\External\WaiBlue\HlthcrCore\Models\RecordManagers\Hazard;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobHasHazard extends \Hubleto\Erp\RecordManager
{
  public $table = 'hlthcr_job_has_hazards';

  public function JOB(): BelongsTo
  {
    return $this->belongsTo(Job::class, 'id_job', 'id');
  }

  public function HAZARD(): BelongsTo
  {
    return $this->belongsTo(Hazard::class, 'id_hazard', 'id');
  }

  public function prepareReadQuery(mixed $query = null, int $level = 0, array|null $includeRelations = null): mixed
  {
    $query = parent::prepareReadQuery($query, $level, $includeRelations);
    return $this->restrictToVisibleJobs($query);
  }

  // Dropdowns too: Hubleto's lookup query does not go through prepareReadQuery.
  public function prepareLookupQuery(string $search): mixed
  {
    $query = parent::prepareLookupQuery($search);
    return $this->restrictToVisibleJobs($query);
  }

  // A job hazard is visible when its job's branch is visible.
  private function restrictToVisibleJobs(mixed $query): mixed
  {
    $hubleto = \Hubleto\Erp\Loader::getGlobalApp();
    /** @var BranchScope */
    $branchScope = $hubleto->getService(BranchScope::class);

    return $query->whereIn($this->table . '.id_job', function ($visibleJobs) use ($branchScope) {
      $visibleJobs->select('hlthcr_jobs.id')->from('hlthcr_jobs');
      // The subquery builder is changed in place, so the returned value is not needed.
      $branchScope->restrictToVisibleBranches($visibleJobs, 'hlthcr_jobs.id_branch');
    });
  }

  public function addUrlFiltersToQuery(mixed $query): mixed
  {
    $query = parent::addUrlFiltersToQuery($query);

    $hubleto = \Hubleto\Erp\Loader::getGlobalApp();
    $idJob = $hubleto->router()->urlParamAsInteger('idJob');
    if ($idJob > 0) {
      $query = $query->where($this->table . '.id_job', $idJob);
    }

    return $query;
  }

  public function prepareLookupData(array $dataRaw): array
  {
    return parent::prepareLookupData($dataRaw);
  }
}
