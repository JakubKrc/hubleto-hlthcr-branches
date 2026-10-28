<?php

namespace Hubleto\App\External\WaiBlue\HlthcrBranches\Models\Migrations;

use Hubleto\Framework\Migration;

class Job_0001 extends Migration
{
  public function upgradeSchema(): void
  {
    $this->db->execute("CREATE TABLE IF NOT EXISTS `hlthcr_jobs` (
      `id` int(8) primary key auto_increment,
      `name` varchar(255) NOT NULL,
      `id_branch` int(8) NULL DEFAULT NULL,
      `exam_interval_months` int(8) NULL DEFAULT NULL,
      INDEX `id_branch` (`id_branch`)
    ) ENGINE=InnoDB");
  }

  public function downgradeSchema(): void
  {
    $this->db->execute("DROP TABLE IF EXISTS `hlthcr_jobs`");
  }

  public function upgradeForeignKeys(): void
  {
    $this->db->execute("ALTER TABLE `hlthcr_jobs` ADD CONSTRAINT `fk__hlthcr_jobs__id_branch` FOREIGN KEY (`id_branch`) REFERENCES `hlthcr_branches` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT");
  }

  public function downgradeForeignKeys(): void
  {
    $this->db->execute("ALTER TABLE `hlthcr_jobs` DROP FOREIGN KEY `fk__hlthcr_jobs__id_branch`");
  }
}
