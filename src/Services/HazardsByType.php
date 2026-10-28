<?php

namespace Hubleto\App\External\WaiBlue\HlthcrBranches\Services;

use Hubleto\App\External\WaiBlue\HlthcrBranches\Models\JobHasHazard;
use Hubleto\App\External\WaiBlue\HlthcrCore\Models\Hazard;

/**
 * Hazards grouped by hazard type: the whole catalogue for the job levels editor,
 * or only the hazards the jobs of a branch have (columns of the categorisation of work).
 */
class HazardsByType extends \Hubleto\Erp\Core
{
  /**
   * Returns [['id', 'name', 'uses_categories', 'hazards' => [['id_hazard', 'name'], ...]], ...] ordered by type.
   */
  public function loadCatalogue(): array
  {
    /** @var Hazard */
    $mHazard = $this->getModel(Hazard::class);
    $hazards = $mHazard->record->orderBy('type')->orderBy('name')->get();
    return $this->groupByType($hazards);
  }

  // The caller must check that the branch is visible to the user.
  public function loadUsedByJobsOfBranch(int $idBranch): array
  {
    /** @var JobHasHazard */
    $mJobHazard = $this->getModel(JobHasHazard::class);
    $usedHazardIds = $mJobHazard->record
      ->whereIn('id_job', function ($jobsOfBranch) use ($idBranch) {
        $jobsOfBranch->select('id')->from('hlthcr_jobs')->where('id_branch', $idBranch);
      })
      ->pluck('id_hazard')
      ->toArray()
    ;

    /** @var Hazard */
    $mHazard = $this->getModel(Hazard::class);
    $hazards = $mHazard->record->whereIn('id', $usedHazardIds)->orderBy('type')->orderBy('name')->get();
    return $this->groupByType($hazards);
  }

  private function groupByType(mixed $hazards): array
  {
    /** @var Hazard */
    $mHazard = $this->getModel(Hazard::class);
    $typeNames = $mHazard->getTypeNames();

    $hazardTypes = [];
    foreach ($hazards as $hazard) {
      $idHazardType = (int) $hazard->type;

      if (!isset($hazardTypes[$idHazardType])) {
        $hazardTypes[$idHazardType] = [
          'id' => $idHazardType,
          'name' => $typeNames[$idHazardType] ?? '',
          'uses_categories' => Hazard::typeUsesCategories($idHazardType),
          'hazards' => [],
        ];
      }

      $hazardTypes[$idHazardType]['hazards'][] = ['id_hazard' => (int) $hazard->id, 'name' => $hazard->name];
    }

    ksort($hazardTypes);
    return array_values($hazardTypes);
  }
}
