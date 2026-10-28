<?php

namespace Hubleto\App\External\WaiBlue\HlthcrBranches\Models;

use Hubleto\App\Community\Workflow\Models\Workflow;
use Hubleto\App\Community\Workflow\Models\WorkflowStep;
use Hubleto\App\External\WaiBlue\HlthcrCore\Models\Traits\StaysInOwnWorkflow;
use Hubleto\App\External\WaiBlue\HlthcrCore\Models\Traits\UsesStoredValues;
use Hubleto\Framework\Db\Column\Lookup;
use Hubleto\Framework\Db\Column\Text;

/**
 * Deficiency found at a branch. Both legacy apps keep the text, a note and whether it is resolved
 * (one a state, the other a due date); here the state is a Workflow step, so Hubleto keeps its history.
 */
class Deficiency extends \Hubleto\App\External\WaiBlue\HlthcrCore\Models\HlthcrModel
{
  use StaysInOwnWorkflow;
  use UsesStoredValues;

  const WORKFLOW_GROUP = 'hlthcr_deficiencies';

  public string $table = 'hlthcr_deficiencies';
  public string $recordManagerClass = RecordManagers\Deficiency::class;
  public ?string $lookupSqlValue = 'left({%TABLE%}.description, 80)';
  public ?string $lookupUrlDetail = 'hlthcr-branches/deficiencies/{%ID%}';

  public array $relations = [
    'BRANCH' => [ self::BELONGS_TO, Branch::class, 'id_branch', 'id' ],
    'WORKFLOW' => [ self::BELONGS_TO, Workflow::class, 'id_workflow', 'id' ],
    'WORKFLOW_STEP' => [ self::BELONGS_TO, WorkflowStep::class, 'id_workflow_step', 'id' ],
  ];

  public function describeColumns(): array
  {
    return array_merge(parent::describeColumns(), [
      'id_branch' => (new Lookup($this, $this->translate('Branch'), Branch::class, 'RESTRICT'))->setRequired()->setDefaultVisible(),
      'description' => (new Text($this, $this->translate('Deficiency')))->setRequired()->setDefaultVisible(),
      'note' => (new Text($this, $this->translate('Note')))->setDefaultVisible(),
      'id_workflow' => (new Lookup($this, $this->translate('Workflow'), Workflow::class))->setReadonly(),
      'id_workflow_step' => (new Lookup($this, $this->translate('Status'), WorkflowStep::class))->setDefaultVisible()->setReadonly(),
    ]);
  }

  public function describeTable(): \Hubleto\Framework\Description\Table
  {
    $description = parent::describeTable();
    $description->ui['addButtonText'] = $this->translate('Add deficiency');
    $description->addFilter('fDeficiencyStatus', Workflow::buildTableFilterForWorkflowSteps($this, $this->translate('Status')));
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
    return ['BRANCH', 'WORKFLOW_STEP'];
  }

  public function getMaxReadLevelForLoadTableData(): int
  {
    return 1;
  }

  public function onBeforeUpdate(array $record): array
  {
    $record = parent::onBeforeUpdate($record);
    // The branch stays as created (both legacy apps).
    $record = $this->keepStoredValues($record, ['id_branch']);
    $this->refuseWorkflowOfOtherGroup($record, self::WORKFLOW_GROUP);
    return $record;
  }

  public function onAfterCreate(array $savedRecord): array
  {
    $savedRecord = parent::onAfterCreate($savedRecord);

    // New deficiencies start in the first step of the deficiency workflow ("newly found").
    /** @var Workflow */
    $mWorkflow = $this->getModel(Workflow::class);
    $savedRecord = $mWorkflow->applyDefaultWorkflow($savedRecord, self::WORKFLOW_GROUP);
    $this->record->recordUpdate($savedRecord);

    return $savedRecord;
  }
}
