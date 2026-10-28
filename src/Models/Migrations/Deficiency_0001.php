<?php

namespace Hubleto\App\External\WaiBlue\HlthcrBranches\Models\Migrations;

use Hubleto\Framework\Migration;

class Deficiency_0001 extends Migration
{
  public function upgradeSchema(): void
  {
    $this->db->execute("CREATE TABLE IF NOT EXISTS `hlthcr_deficiencies` (
      `id` int(8) primary key auto_increment,
      `id_branch` int(8) NULL DEFAULT NULL,
      `description` text NOT NULL,
      `note` text NULL DEFAULT NULL,
      `id_workflow` int(8) NULL DEFAULT NULL,
      `id_workflow_step` int(8) NULL DEFAULT NULL,
      INDEX `id_branch` (`id_branch`),
      INDEX `id_workflow_step` (`id_workflow_step`)
    ) ENGINE=InnoDB");
  }

  public function downgradeSchema(): void
  {
    $this->db->execute("DROP TABLE IF EXISTS `hlthcr_deficiencies`");
  }

  public function upgradeForeignKeys(): void
  {
    $this->db->execute("ALTER TABLE `hlthcr_deficiencies` ADD CONSTRAINT `fk__hlthcr_deficiencies__id_branch` FOREIGN KEY (`id_branch`) REFERENCES `hlthcr_branches` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT");
  }

  public function downgradeForeignKeys(): void
  {
    $this->db->execute("ALTER TABLE `hlthcr_deficiencies` DROP FOREIGN KEY `fk__hlthcr_deficiencies__id_branch`");
  }
}
