<?php

namespace Hubleto\App\External\WaiBlue\HlthcrBranches\Models;

use Hubleto\App\External\WaiBlue\HlthcrCore\Models\Clinic;
use Hubleto\Framework\Db\Column\Lookup;

/**
 * Clinic that performs medical exams for employees of a branch.
 */
class BranchHasClinic extends \Hubleto\App\External\WaiBlue\HlthcrCore\Models\HlthcrModel
{
  public string $table = 'hlthcr_branch_has_clinics';
  public string $recordManagerClass = RecordManagers\BranchHasClinic::class;
  public ?string $lookupSqlValue = '(select name from hlthcr_clinics where hlthcr_clinics.id = {%TABLE%}.id_clinic)';
  public ?string $lookupUrlDetail = 'hlthcr-branches/branch-has-clinics/{%ID%}';

  public array $relations = [
    'BRANCH' => [ self::BELONGS_TO, Branch::class, 'id_branch', 'id' ],
    'CLINIC' => [ self::BELONGS_TO, Clinic::class, 'id_clinic', 'id' ],
  ];

  public function describeColumns(): array
  {
    return array_merge(parent::describeColumns(), [
      'id_branch' => (new Lookup($this, $this->translate('Branch'), Branch::class, 'CASCADE'))->setRequired()->setDefaultVisible(),
      'id_clinic' => (new Lookup($this, $this->translate('Clinic'), Clinic::class, 'CASCADE'))->setRequired()->setDefaultVisible(),
    ]);
  }

  public function describeTable(): \Hubleto\Framework\Description\Table
  {
    $description = parent::describeTable();
    $description->ui['addButtonText'] = $this->translate('Add clinic');
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
    return ['BRANCH', 'CLINIC'];
  }

  public function getMaxReadLevelForLoadTableData(): int
  {
    return 1;
  }
}
