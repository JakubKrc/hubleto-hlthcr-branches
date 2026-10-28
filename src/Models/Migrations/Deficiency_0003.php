<?php

namespace Hubleto\App\External\WaiBlue\HlthcrBranches\Models\Migrations;

use Hubleto\Framework\Migration;

// Foreign keys of the workflow state (models and lookups review B6).
class Deficiency_0003 extends Migration
{
  public function upgradeSchema(): void
  {
  }

  public function downgradeSchema(): void
  {
  }

  public function upgradeForeignKeys(): void
  {
    $this->db->execute("ALTER TABLE `hlthcr_deficiencies` ADD CONSTRAINT `fk__hlthcr_deficiencies__id_workflow` FOREIGN KEY (`id_workflow`) REFERENCES `workflows` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT");
    $this->db->execute("ALTER TABLE `hlthcr_deficiencies` ADD CONSTRAINT `fk__hlthcr_deficiencies__id_workflow_step` FOREIGN KEY (`id_workflow_step`) REFERENCES `workflow_steps` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT");
  }

  public function downgradeForeignKeys(): void
  {
    $this->db->execute("ALTER TABLE `hlthcr_deficiencies` DROP FOREIGN KEY `fk__hlthcr_deficiencies__id_workflow`");
    $this->db->execute("ALTER TABLE `hlthcr_deficiencies` DROP FOREIGN KEY `fk__hlthcr_deficiencies__id_workflow_step`");
  }
}
