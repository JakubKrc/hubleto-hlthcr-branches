<?php

namespace Hubleto\App\External\WaiBlue\HlthcrBranches\Services;

use Hubleto\App\External\WaiBlue\HlthcrBranches\Models\Branch;
use Hubleto\App\External\WaiBlue\HlthcrBranches\Models\Job;
use Hubleto\App\External\WaiBlue\HlthcrBranches\Models\JobHasHazard;

/**
 * Builds the "categorisation of work" of a branch: job positions x the hazards they have, with levels.
 * The same matrix was printed by the legacy app (Client/BranchRiskOverview).
 */
class CategorisationMatrix extends \Hubleto\Erp\Core
{
  /**
   * Returns null when the branch does not exist or is not visible to the user.
   */
  public function build(int $idBranch): ?array
  {
    $branch = $this->getModel(Branch::class)->record->prepareReadQuery()->where('hlthcr_branches.id', $idBranch)->first();
    if (!$branch) return null;

    /** @var HazardsByType */
    $hazardsByType = $this->getService(HazardsByType::class);
    $hazardColumnsByType = $hazardsByType->loadUsedByJobsOfBranch($idBranch);
    $levels = $this->loadLevelsByJobAndHazard($idBranch);

    $jobs = [];
    $branchJobs = $this->getModel(Job::class)->record->where('id_branch', $idBranch)->orderBy('name')->get();
    foreach ($branchJobs as $job) {
      $cells = [];
      $workCategory = 0;
      foreach ($hazardColumnsByType as $hazardType) {
        foreach ($hazardType['hazards'] as $hazardColumn) {
          $level = $levels[$job->id][$hazardColumn['id_hazard']] ?? JobHasHazard::LEVEL_NOT_SET;
          $cells[] = $this->formatCell($level, $hazardType['uses_categories']);

          // The work category of the job is its highest work-factor category.
          if ($hazardType['uses_categories'] && $level > $workCategory) {
            $workCategory = $level;
          }
        }
      }

      $jobs[] = [
        'name' => $job->name,
        'cells' => $cells,
        'exam_interval_months' => $job->exam_interval_months,
        'work_category' => $workCategory > 0 ? $workCategory : '',
      ];
    }

    return [
      'branch' => ['id' => $branch->id, 'name' => $branch->name, 'city' => $branch->city],
      'hazardTypes' => $hazardColumnsByType,
      'jobs' => $jobs,
    ];
  }

  /**
   * Returns [idJob => [idHazard => level]].
   */
  private function loadLevelsByJobAndHazard(int $idBranch): array
  {
    $jobHazards = $this->getModel(JobHasHazard::class)->record
      ->whereIn('id_job', function ($jobsOfBranch) use ($idBranch) {
        $jobsOfBranch->select('id')->from('hlthcr_jobs')->where('id_branch', $idBranch);
      })
      ->get()
    ;

    $levels = [];
    foreach ($jobHazards as $jobHazard) {
      $levels[(int) $jobHazard->id_job][(int) $jobHazard->id_hazard] = (int) $jobHazard->level;
    }
    return $levels;
  }

  // Category hazards show the number (3 and 4 are highlighted), yes/no hazards show "x".
  private function formatCell(int $level, bool $usesCategories): array
  {
    $isSet = $level > JobHasHazard::LEVEL_NOT_SET;

    if (!$usesCategories) {
      return ['text' => $isSet ? 'x' : '-', 'highlight' => ''];
    }

    // Categories 3 and 4 are the risky works (legacy matrix highlights them too).
    $highlight = '';
    if ($level == JobHasHazard::CATEGORY_RISKY) $highlight = 'level-3';
    if ($level == JobHasHazard::CATEGORY_HIGHEST_RISK) $highlight = 'level-4';

    return ['text' => $isSet ? (string) $level : '-', 'highlight' => $highlight];
  }
}
