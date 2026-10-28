<?php

namespace Hubleto\App\External\WaiBlue\HlthcrBranches\Models\Migrations;

use Hubleto\Framework\Migration;

// A branch type in use can be retired instead of deleted (models and lookups review C5).
class BranchType_0002 extends Migration
{
  public function upgradeSchema(): void
  {
    $this->db->execute("ALTER TABLE `hlthcr_branch_types` ADD COLUMN `is_active` tinyint(1) NOT NULL DEFAULT 1, ADD INDEX `is_active` (`is_active`)");
  }

  public function downgradeSchema(): void
  {
    $this->db->execute("ALTER TABLE `hlthcr_branch_types` DROP COLUMN `is_active`");
  }

  public function upgradeForeignKeys(): void
  {
  }

  public function downgradeForeignKeys(): void
  {
  }
}
