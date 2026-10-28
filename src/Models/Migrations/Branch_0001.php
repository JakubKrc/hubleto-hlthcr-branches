<?php

namespace Hubleto\App\External\WaiBlue\HlthcrBranches\Models\Migrations;

use Hubleto\Framework\Migration;

class Branch_0001 extends Migration
{
  public function upgradeSchema(): void
  {
    $this->db->execute("CREATE TABLE IF NOT EXISTS `hlthcr_branches` (
      `id` int(8) primary key auto_increment,
      `name` varchar(255) NOT NULL,
      `id_customer` int(8) NULL DEFAULT NULL,
      `street` varchar(255) NULL DEFAULT NULL,
      `city` varchar(255) NULL DEFAULT NULL,
      `postal_code` varchar(255) NULL DEFAULT NULL,
      `id_branch_type` int(8) NULL DEFAULT NULL,
      `id_owner` int(8) NULL DEFAULT NULL,
      `has_valid_first_aid_training` int(1) NOT NULL DEFAULT 0,
      `first_aid_validity_changed_on` date NULL DEFAULT NULL,
      `first_aid_note` text NULL DEFAULT NULL,
      `nurse_notes` text NULL DEFAULT NULL,
      `contact_email` varchar(255) NULL DEFAULT NULL,
      `is_active` int(1) NOT NULL DEFAULT 1,
      UNIQUE INDEX `customer_branch_name` (`id_customer`, `name`),
      INDEX `id_owner` (`id_owner`),
      INDEX `id_branch_type` (`id_branch_type`),
      INDEX `is_active` (`is_active`)
    ) ENGINE=InnoDB");
  }

  public function downgradeSchema(): void
  {
    $this->db->execute("DROP TABLE IF EXISTS `hlthcr_branches`");
  }

  public function upgradeForeignKeys(): void
  {
    $this->db->execute("ALTER TABLE `hlthcr_branches` ADD CONSTRAINT `fk__hlthcr_branches__id_customer` FOREIGN KEY (`id_customer`) REFERENCES `customers` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT");
    $this->db->execute("ALTER TABLE `hlthcr_branches` ADD CONSTRAINT `fk__hlthcr_branches__id_owner` FOREIGN KEY (`id_owner`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT");
    $this->db->execute("ALTER TABLE `hlthcr_branches` ADD CONSTRAINT `fk__hlthcr_branches__id_branch_type` FOREIGN KEY (`id_branch_type`) REFERENCES `hlthcr_branch_types` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT");
  }

  public function downgradeForeignKeys(): void
  {
    $this->db->execute("ALTER TABLE `hlthcr_branches` DROP FOREIGN KEY `fk__hlthcr_branches__id_customer`");
    $this->db->execute("ALTER TABLE `hlthcr_branches` DROP FOREIGN KEY `fk__hlthcr_branches__id_owner`");
    $this->db->execute("ALTER TABLE `hlthcr_branches` DROP FOREIGN KEY `fk__hlthcr_branches__id_branch_type`");
  }
}
