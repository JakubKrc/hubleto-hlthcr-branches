<?php

namespace Hubleto\App\External\WaiBlue\HlthcrBranches\Models;

use Hubleto\Framework\Db\Column\Boolean;
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
      // A type in use cannot be deleted; an inactive one is no longer offered for new branches.
      'is_active' => (new Boolean($this, $this->translate('Active')))->setDefaultValue(true)->setDefaultVisible(),
    ]);
  }

  // Hubleto stores a boolean that a save does not send as 0, so a type added through the record API would start inactive.
  public function onBeforeCreate(array $record): array
  {
    $record = parent::onBeforeCreate($record);
    if (!array_key_exists('is_active', $record)) $record['is_active'] = true;
    return $record;
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
