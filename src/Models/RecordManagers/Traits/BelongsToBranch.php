<?php

namespace Hubleto\App\External\WaiBlue\HlthcrBranches\Models\RecordManagers\Traits;

use Hubleto\App\External\WaiBlue\HlthcrBranches\Services\BranchScope;

/**
 * For RecordManagers of records that belong to a branch (table column id_branch).
 */
trait BelongsToBranch
{
  // Only records of branches the user may see; required because Hubleto's generic API accepts any model.
  protected function restrictToVisibleBranches(mixed $query): mixed
  {
    $hubleto = \Hubleto\Erp\Loader::getGlobalApp();
    /** @var BranchScope */
    $branchScope = $hubleto->getService(BranchScope::class);
    return $branchScope->restrictToVisibleBranches($query, $this->table . '.id_branch');
  }

  // Used by branch form tabs, which send ?idBranch=<id>.
  protected function filterByBranchFromUrl(mixed $query): mixed
  {
    $hubleto = \Hubleto\Erp\Loader::getGlobalApp();
    $idBranch = $hubleto->router()->urlParamAsInteger('idBranch');
    if ($idBranch <= 0) return $query;

    return $query->where($this->table . '.id_branch', $idBranch);
  }
}
