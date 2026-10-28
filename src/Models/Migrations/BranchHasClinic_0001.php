<?php

namespace Hubleto\App\External\WaiBlue\HlthcrBranches\Models\Migrations;

use Hubleto\Framework\Migration;

class BranchHasClinic_0001 extends Migration
{
  public function upgradeSchema(): void
  {
    $this->db->execute("CREATE TABLE IF NOT EXISTS `hlthcr_branch_has_clinics` (
      `id` int(8) primary key auto_increment,
      `id_branch` int(8) NULL DEFAULT NULL,
      `id_clinic` int(8) NULL DEFAULT NULL,
      UNIQUE INDEX `branch_has_clinic` (`id_branch`, `id_clinic`),
      INDEX `id_clinic` (`id_clinic`)
    ) ENGINE=InnoDB");
  }

  public function downgradeSchema(): void
  {
    $this->db->execute("DROP TABLE IF EXISTS `hlthcr_branch_has_clinics`");
  }

  public function upgradeForeignKeys(): void
  {
    $this->db->execute("ALTER TABLE `hlthcr_branch_has_clinics` ADD CONSTRAINT `fk__hlthcr_branch_has_clinics__id_branch` FOREIGN KEY (`id_branch`) REFERENCES `hlthcr_branches` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT");
    $this->db->execute("ALTER TABLE `hlthcr_branch_has_clinics` ADD CONSTRAINT `fk__hlthcr_branch_has_clinics__id_clinic` FOREIGN KEY (`id_clinic`) REFERENCES `hlthcr_clinics` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT");
  }

  public function downgradeForeignKeys(): void
  {
    $this->db->execute("ALTER TABLE `hlthcr_branch_has_clinics` DROP FOREIGN KEY `fk__hlthcr_branch_has_clinics__id_branch`");
    $this->db->execute("ALTER TABLE `hlthcr_branch_has_clinics` DROP FOREIGN KEY `fk__hlthcr_branch_has_clinics__id_clinic`");
  }
}
