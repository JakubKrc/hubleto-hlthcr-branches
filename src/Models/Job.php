<?php

namespace Hubleto\App\External\WaiBlue\HlthcrBranches\Models;

use Hubleto\App\External\WaiBlue\HlthcrCore\Models\Traits\UsesStoredValues;
use Hubleto\App\External\WaiBlue\HlthcrCore\Models\Hazard;
use Hubleto\Framework\Db\Column\Integer;
use Hubleto\Framework\Db\Column\Lookup;
use Hubleto\Framework\Db\Column\Varchar;
use Hubleto\Framework\Db\Column\Virtual;

/**
 * Job position at a branch. Its hazard levels decide the work category and the medical exam obligations.
 */
class Job extends \Hubleto\App\External\WaiBlue\HlthcrCore\Models\HlthcrModel
{
  use UsesStoredValues;

  public string $table = 'hlthcr_jobs';
  public string $recordManagerClass = RecordManagers\Job::class;
  public ?string $lookupSqlValue = '{%TABLE%}.name';
  public ?string $lookupUrlDetail = 'hlthcr-branches/jobs/{%ID%}';

  public array $relations = [
    'BRANCH' => [ self::BELONGS_TO, Branch::class, 'id_branch', 'id' ],
    'HAZARD_LEVELS' => [ self::HAS_MANY, JobHasHazard::class, 'id_job', 'id' ],
  ];

  // The work category of a job: the highest category of its work factors. Empty when it has none.
  public static function getWorkCategorySql(string $jobIdColumn): string
  {
    return 'select max(jhh.level) from hlthcr_job_has_hazards jhh
      join hlthcr_hazards h on h.id = jhh.id_hazard
      where jhh.id_job = ' . $jobIdColumn . ' and h.type = ' . Hazard::TYPE_FACTOR;
  }

  public function describeColumns(): array
  {
    return array_merge(parent::describeColumns(), [
      'name' => (new Varchar($this, $this->translate('Job position')))->setRequired()->setDefaultVisible(),
      'id_branch' => (new Lookup($this, $this->translate('Branch'), Branch::class, 'RESTRICT'))->setRequired()->setDefaultVisible(),
      // A job may set its own exam interval; it wins over the hazard intervals (one legacy app keeps the interval on the job).
      'exam_interval_months' => (new Integer($this, $this->translate('Own exam interval')))
        ->setUnit($this->translate('months'))
        ->setHint($this->translate('Leave empty to use the shortest interval of the job hazards.'))
        ->setDefaultVisible()
      ,
      // The highest category of a work factor on the job (one legacy app computes it the same way).
      'virt_work_category' => (new Virtual($this, $this->translate('Work category')))
        ->setDefaultVisible()
        ->setProperty('sql', self::getWorkCategorySql('hlthcr_jobs.id'))
      ,
    ]);
  }

  // The branch stays as created (one legacy app), so the job's employees and hazards stay at their branch.
  public function onBeforeUpdate(array $record): array
  {
    $record = parent::onBeforeUpdate($record);
    return $this->keepStoredValues($record, ['id_branch']);
  }

  public function describeTable(): \Hubleto\Framework\Description\Table
  {
    $description = parent::describeTable();
    $description->ui['addButtonText'] = $this->translate('Add job position');
    $description->show(['header', 'fulltextSearch', 'columnSearch', 'moreActionsButton']);
    $description->hide(['footer']);
    return $description;
  }

  public function describeForm(): \Hubleto\Framework\Description\Form
  {
    return parent::describeForm();
  }

  public function getRelationsIncludedInLoadTableData(): array|null
  {
    return ['BRANCH'];
  }

  public function getMaxReadLevelForLoadTableData(): int
  {
    return 1;
  }

  public function getRelationsIncludedInLoadFormData(): array|null
  {
    return ['BRANCH'];
  }
}
