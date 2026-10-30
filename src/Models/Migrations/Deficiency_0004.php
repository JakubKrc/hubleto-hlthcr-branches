<?php

namespace Hubleto\App\External\WaiBlue\HlthcrBranches\Models\Migrations;

use Hubleto\App\External\WaiBlue\HlthcrBranches\Loader;
use Hubleto\Framework\Migration;

// Existing installs: the fourth deficiency state "Nesplnený" ("php hubleto migrate" does not run installApp()).
class Deficiency_0004 extends Migration
{
  public function upgradeSchema(): void
  {
    /** @var Loader */
    $branchesApp = $this->model->getService(Loader::class);
    $branchesApp->installDeficiencyWorkflow();
  }

  public function downgradeSchema(): void
  {
  }

  public function upgradeForeignKeys(): void
  {
  }

  public function downgradeForeignKeys(): void
  {
  }
}
