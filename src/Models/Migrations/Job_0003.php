<?php

namespace Hubleto\App\External\WaiBlue\HlthcrBranches\Models\Migrations;

use Hubleto\Framework\Migration;

// Job names are unique per branch (models and lookups review C5). Job::refuseInvalidJob() gives the message.
class Job_0003 extends Migration
{
  public function upgradeSchema(): void
  {
    $this->db->execute("ALTER TABLE `hlthcr_jobs` ADD UNIQUE INDEX `branch_job_name` (`id_branch`, `name`)");
  }

  public function downgradeSchema(): void
  {
    $this->db->execute("ALTER TABLE `hlthcr_jobs` DROP INDEX `branch_job_name`");
  }

  public function upgradeForeignKeys(): void
  {
  }

  public function downgradeForeignKeys(): void
  {
  }
}
