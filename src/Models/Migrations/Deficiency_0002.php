<?php

namespace Hubleto\App\External\WaiBlue\HlthcrBranches\Models\Migrations;

use Hubleto\Framework\Migration;

// A branch with deficiencies cannot be deleted (both legacy apps); branches are deactivated instead.
class Deficiency_0002 extends Migration
{
  public function upgradeSchema(): void
  {
  }

  public function downgradeSchema(): void
  {
  }

  public function upgradeForeignKeys(): void
  {
    $this->db->execute("ALTER TABLE `hlthcr_deficiencies` DROP FOREIGN KEY `fk__hlthcr_deficiencies__id_branch`");
    $this->db->execute("ALTER TABLE `hlthcr_deficiencies` ADD CONSTRAINT `fk__hlthcr_deficiencies__id_branch` FOREIGN KEY (`id_branch`) REFERENCES `hlthcr_branches` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT");
  }

  public function downgradeForeignKeys(): void
  {
    $this->db->execute("ALTER TABLE `hlthcr_deficiencies` DROP FOREIGN KEY `fk__hlthcr_deficiencies__id_branch`");
    $this->db->execute("ALTER TABLE `hlthcr_deficiencies` ADD CONSTRAINT `fk__hlthcr_deficiencies__id_branch` FOREIGN KEY (`id_branch`) REFERENCES `hlthcr_branches` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT");
  }
}
