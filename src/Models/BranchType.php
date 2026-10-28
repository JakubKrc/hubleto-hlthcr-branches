<?php

namespace Hubleto\App\External\WaiBlue\HlthcrBranches\Models;

use Hubleto\Framework\Db\Column\Varchar;

/**
 * Type of a branch. Both legacy apps have it with different values (categories A-D in one,
 * 38 industries in the other), so it is a code list the administrator edits, empty after install.
 */
class BranchType extends \Hubleto\App\External\WaiBlue\HlthcrCore\Models\HlthcrModel
{
  public string $table = 'hlthcr_branch_types';
  public string $recordManagerClass = RecordManagers\BranchType::class;
  public ?string $lookupSqlValue = '{%TABLE%}.name';
  public ?string $lookupUrlDetail = 'hlthcr-branches/branch-types/{%ID%}';

  public function describeColumns(): array
  {
    return array_merge(parent::describeColumns(), [
      'name' => (new Varchar($this, $this->translate('Name')))->setRequired()->setDefaultVisible(),
    ]);
  }

  public function describeTable(): \Hubleto\Framework\Description\Table
  {
    $description = parent::describeTable();
    $description->ui['addButtonText'] = $this->translate('Add branch type');
    $description->show(['header', 'fulltextSearch', 'columnSearch', 'moreActionsButton']);
    $description->hide(['footer']);
    return $description;
  }

  public function describeForm(): \Hubleto\Framework\Description\Form
  {
    return parent::describeForm();
  }
}
