<?php

namespace Hubleto\App\External\WaiBlue\HlthcrBranches\Models;

use Hubleto\App\External\WaiBlue\HlthcrCore\Models\Hazard;
use Hubleto\App\External\WaiBlue\HlthcrCore\Models\Traits\HasHazardLevel;
use Hubleto\Framework\Db\Column\Integer;
use Hubleto\Framework\Db\Column\Lookup;
use Hubleto\Framework\Db\Column\Virtual;

/**
 * Level of one catalogue hazard for one job position (one legacy app links them so; the other went through branch hazards).
 * A row means the hazard is present: category 1-4 for work factors, 1 ("yes") for the other types.
 */
class JobHasHazard extends \Hubleto\App\External\WaiBlue\HlthcrCore\Models\HlthcrModel
{
  use HasHazardLevel;

  // Used by the hazard levels editor for a hazard without a row.
  const LEVEL_NOT_SET = 0;
  const LEVEL_YES = 1;
  const CATEGORY_RISKY = 3;
  const CATEGORY_HIGHEST_RISK = 4;

  public string $table = 'hlthcr_job_has_hazards';
  public string $recordManagerClass = RecordManagers\JobHasHazard::class;
  public ?string $lookupSqlValue = '(select name from hlthcr_hazards where hlthcr_hazards.id = {%TABLE%}.id_hazard)';
  public ?string $lookupUrlDetail = 'hlthcr-branches/job-has-hazards/{%ID%}';

  public array $relations = [
    'JOB' => [ self::BELONGS_TO, Job::class, 'id_job', 'id' ],
    'HAZARD' => [ self::BELONGS_TO, Hazard::class, 'id_hazard', 'id' ],
  ];

  public function describeColumns(): array
  {
    return array_merge(parent::describeColumns(), [
      'id_job' => (new Lookup($this, $this->translate('Job position'), Job::class, 'CASCADE'))
        ->setRequired()
        ->setDefaultVisible()
      ,
      'id_hazard' => (new Lookup($this, $this->translate('Hazard'), Hazard::class))
        ->setRequired()
        ->setDefaultVisible()
      ,
      'level' => (new Integer($this, $this->translate('Category')))
        ->setRequired()
        ->setDefaultValue(Hazard::LEVEL_YES)
        ->setEnumValues($this->getLevelTexts())
      ,
      'virt_level' => (new Virtual($this, $this->translate('Category')))
        ->setDefaultVisible()
        ->setProperty('sql', $this->getLevelTextSql())
      ,
    ]);
  }

  public function onBeforeCreate(array $record): array
  {
    $record = parent::onBeforeCreate($record);
    $this->refuseInvalidHazardLevel($record);
    return $record;
  }

  public function onBeforeUpdate(array $record): array
  {
    $record = parent::onBeforeUpdate($record);
    $this->refuseInvalidHazardLevel($record);
    return $record;
  }

  public function describeTable(): \Hubleto\Framework\Description\Table
  {
    $description = parent::describeTable();
    $description->ui['addButtonText'] = $this->translate('Add hazard level');
    $description->show(['header', 'fulltextSearch', 'columnSearch', 'moreActionsButton']);
    $description->hide(['footer']);
    return $description;
  }

  public function describeForm(): \Hubleto\Framework\Description\Form
  {
    return parent::describeForm();
  }
}
