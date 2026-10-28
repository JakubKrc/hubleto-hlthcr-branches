<?php

namespace Hubleto\App\External\WaiBlue\HlthcrBranches\Models\Migrations;

use Hubleto\Framework\Migration;

// A branch with job positions cannot be deleted (both legacy apps); branches are deactivated instead.
class Job_0002 extends Migration
{
  public function upgradeSchema(): void
  {
  }

  public function downgradeSchema(): void
  {
  }

  public function upgradeForeignKeys(): void
  {
    $this->db->execute("ALTER TABLE `hlthcr_jobs` DROP FOREIGN KEY `fk__hlthcr_jobs__id_branch`");
    $this->db->execute("ALTER TABLE `hlthcr_jobs` ADD CONSTRAINT `fk__hlthcr_jobs__id_branch` FOREIGN KEY (`id_branch`) REFERENCES `hlthcr_branches` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT");
  }

  public function downgradeForeignKeys(): void
  {
    $this->db->execute("ALTER TABLE `hlthcr_jobs` DROP FOREIGN KEY `fk__hlthcr_jobs__id_branch`");
    $this->db->execute("ALTER TABLE `hlthcr_jobs` ADD CONSTRAINT `fk__hlthcr_jobs__id_branch` FOREIGN KEY (`id_branch`) REFERENCES `hlthcr_branches` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT");
  }
}
