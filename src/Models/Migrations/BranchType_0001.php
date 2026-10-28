<?php

namespace Hubleto\App\External\WaiBlue\HlthcrBranches\Models\Migrations;

use Hubleto\Framework\Migration;

class BranchType_0001 extends Migration
{
  public function upgradeSchema(): void
  {
    $this->db->execute("CREATE TABLE IF NOT EXISTS `hlthcr_branch_types` (
      `id` int(8) primary key auto_increment,
      `name` varchar(255) NOT NULL,
      UNIQUE INDEX `name` (`name`)
    ) ENGINE=InnoDB");
  }

  public function downgradeSchema(): void
  {
    $this->db->execute("DROP TABLE IF EXISTS `hlthcr_branch_types`");
  }

  public function upgradeForeignKeys(): void
  {
  }

  public function downgradeForeignKeys(): void
  {
  }
}
