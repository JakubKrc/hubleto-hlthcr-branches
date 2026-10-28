<?php

namespace Hubleto\App\External\WaiBlue\HlthcrBranches\Controllers\Api;

use Hubleto\App\External\WaiBlue\HlthcrBranches\Services\JobHazardLevels;

class GetJobHazardLevels extends \Hubleto\Erp\Controllers\ApiController
{
  public function renderJson(): array
  {
    $idJob = $this->router()->urlParamAsInteger('idJob');

    return [
      'status' => 'success',
      'hazardTypes' => $this->getService(JobHazardLevels::class)->getLevelsGroupedByHazardType($idJob),
    ];
  }
}
