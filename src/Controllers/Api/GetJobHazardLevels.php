<?php

namespace Hubleto\App\External\WaiBlue\HlthcrBranches\Controllers\Api;

use Hubleto\App\External\WaiBlue\HlthcrBranches\Services\JobHazardLevels;

class GetJobHazardLevels extends \Hubleto\Erp\Controllers\ApiController
{
  public function renderJson(): array
  {
    $idJob = $this->router()->urlParamAsInteger('idJob');

    /** @var JobHazardLevels */
    $jobHazardLevels = $this->getService(JobHazardLevels::class);

    return [
      'status' => 'success',
      'hazardTypes' => $jobHazardLevels->getLevelsGroupedByHazardType($idJob),
      'mayChange' => $jobHazardLevels->mayActiveUserChangeLevels(),
    ];
  }
}
