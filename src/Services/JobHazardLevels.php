<?php

namespace Hubleto\App\External\WaiBlue\HlthcrBranches\Services;

use Hubleto\App\External\WaiBlue\HlthcrBranches\Models\Job;
use Hubleto\App\External\WaiBlue\HlthcrBranches\Models\JobHasHazard;
use Hubleto\App\External\WaiBlue\HlthcrCore\Models\Hazard;
use Hubleto\App\External\WaiBlue\HlthcrCore\Services\RecordWriteGuard;

/**
 * Reads and saves the hazard levels of one job position: one level per hazard of the catalogue (as in one legacy app).
 */
class JobHazardLevels extends \Hubleto\Erp\Core
{
  /**
   * Returns the hazard catalogue grouped by hazard type, each hazard with the current level of the job.
   * Returns an empty array when the job does not exist or is not visible to the user.
   */
  public function getLevelsGroupedByHazardType(int $idJob): array
  {
    $job = $this->getModel(Job::class)->record->prepareReadQuery()->where('hlthcr_jobs.id', $idJob)->first();
    if (!$job) return [];

    $currentLevels = [];
    $jobHazards = $this->getModel(JobHasHazard::class)->record->where('id_job', $idJob)->get();
    foreach ($jobHazards as $jobHazard) {
      $currentLevels[(int) $jobHazard->id_hazard] = (int) $jobHazard->level;
    }

    /** @var HazardsByType */
    $hazardsByType = $this->getService(HazardsByType::class);
    $hazardTypes = $hazardsByType->loadCatalogue();

    foreach ($hazardTypes as $typeIndex => $hazardType) {
      foreach ($hazardType['hazards'] as $hazardIndex => $hazard) {
        $level = $currentLevels[$hazard['id_hazard']] ?? JobHasHazard::LEVEL_NOT_SET;
        $hazardTypes[$typeIndex]['hazards'][$hazardIndex]['level'] = $level;
      }
    }

    return $hazardTypes;
  }

  // The same rule as for Hubleto's generic save (for example medical staff see the levels read-only).
  public function mayActiveUserChangeLevels(): bool
  {
    /** @var RecordWriteGuard */
    $recordWriteGuard = $this->getService(RecordWriteGuard::class);
    return $recordWriteGuard->mayActiveUserChangeModel(JobHasHazard::class);
  }

  /**
   * Saves [idHazard => level]. Level 0 removes the row. Unknown hazards are ignored.
   */
  public function saveLevels(int $idJob, array $levelsByHazard): void
  {
    if (!$this->mayActiveUserChangeLevels()) {
      throw new \Exception('You may not change the hazard levels.');
    }

    $job = $this->getModel(Job::class)->record->prepareReadQuery()->where('hlthcr_jobs.id', $idJob)->first();
    if (!$job) {
      throw new \Exception('Job position not found.');
    }

    /** @var JobHasHazard */
    $mJobHazard = $this->getModel(JobHasHazard::class);
    /** @var Hazard */
    $mHazard = $this->getModel(Hazard::class);
    $hazardTypesById = $mHazard->record->pluck('type', 'id')->toArray();

    foreach ($levelsByHazard as $idHazard => $submittedLevel) {
      $idHazard = (int) $idHazard;
      $isCatalogueHazard = isset($hazardTypesById[$idHazard]);
      if (!$isCatalogueHazard) continue;

      $level = Hazard::levelToStore((int) $hazardTypesById[$idHazard], (int) $submittedLevel);

      $existingJobHazard = $mJobHazard->record
        ->where('id_job', $idJob)
        ->where('id_hazard', $idHazard)
        ->first()
      ;

      if ($level == JobHasHazard::LEVEL_NOT_SET) {
        if ($existingJobHazard) $existingJobHazard->delete();
        continue;
      }

      if ($existingJobHazard) {
        $existingJobHazard->update(['level' => $level]);
      } else {
        $mJobHazard->record->recordCreate([
          'id_job' => $idJob,
          'id_hazard' => $idHazard,
          'level' => $level,
        ]);
      }
    }
  }
}
