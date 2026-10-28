<?php

namespace Hubleto\App\External\WaiBlue\HlthcrBranches\Services;

use Hubleto\App\External\WaiBlue\HlthcrCore\Services\AccessScope;

/**
 * Visibility of branches and of records that belong to a branch (jobs, hazards, deficiencies, employees, ...).
 * A branch is owned by its hygienist (hlthcr_branches.id_owner); which user types are restricted comes from AccessScope.
 */
class BranchScope extends \Hubleto\Erp\Core
{
  public function restrictToVisibleBranches(mixed $query, string $branchIdColumn): mixed
  {
    /** @var AccessScope $accessScope */
    $accessScope = $this->getService(AccessScope::class);
    $visibility = $accessScope->getVisibilityOfActiveUser();
    if ($visibility == AccessScope::SEES_ALL) return $query;
    if ($visibility == AccessScope::SEES_NOTHING) return $query->whereRaw('1 = 0');

    // Hygienists and other users who see only their own: the branches they own.
    $idUser = $this->authProvider()->getUserId();

    return $query->whereIn($branchIdColumn, function ($visibleBranches) use ($idUser) {
      $visibleBranches->select('id')->from('hlthcr_branches')->where('id_owner', $idUser);
    });
  }
}
