<?php

namespace Hubleto\App\External\WaiBlue\HlthcrBranches\Models;

use Hubleto\App\External\WaiBlue\HlthcrCore\Controllers\Api\UserLookup;
use Hubleto\App\External\WaiBlue\HlthcrCore\Models\Traits\EmptyDatesAsNull;
use Hubleto\App\External\WaiBlue\HlthcrCore\Models\Traits\UsesStoredValues;
use Hubleto\App\Community\Auth\Models\User;
use Hubleto\App\Community\Customers\Models\Customer;
use Hubleto\App\External\WaiBlue\HlthcrCore\Controllers\Api\CustomerLookup;
use Hubleto\App\External\WaiBlue\HlthcrCore\Services\Roles;
use Hubleto\Framework\Db\Column\Boolean;
use Hubleto\Framework\Db\Column\Date;
use Hubleto\Framework\Db\Column\Email;
use Hubleto\Framework\Db\Column\Lookup;
use Hubleto\Framework\Db\Column\Text;
use Hubleto\Framework\Db\Column\Varchar;

class Branch extends \Hubleto\App\External\WaiBlue\HlthcrCore\Models\HlthcrModel
{
  use EmptyDatesAsNull;
  use UsesStoredValues;
  public string $table = 'hlthcr_branches';
  public string $recordManagerClass = RecordManagers\Branch::class;
  public ?string $lookupSqlValue = 'concat({%TABLE%}.name, ifnull(concat(", ", {%TABLE%}.city), ""))';
  public ?string $lookupUrlDetail = 'hlthcr-branches/branches/{%ID%}';

  public array $relations = [
    'CUSTOMER' => [ self::BELONGS_TO, Customer::class, 'id_customer', 'id' ],
    'OWNER' => [ self::BELONGS_TO, User::class, 'id_owner', 'id' ],
    'BRANCH_TYPE' => [ self::BELONGS_TO, BranchType::class, 'id_branch_type', 'id' ],
    'JOBS' => [ self::HAS_MANY, Job::class, 'id_branch', 'id' ],
  ];

  public function describeColumns(): array
  {
    return array_merge(parent::describeColumns(), [
      'name' => (new Varchar($this, $this->translate('Branch')))->setRequired()->setDefaultVisible(),
      'id_customer' => (new Lookup($this, $this->translate('Client'), Customer::class))
        ->setRequired()
        ->setDefaultVisible()
        // Only clients visible to the user (the community lookup is not scoped).
        ->setEndpoint(CustomerLookup::ENDPOINT)
      ,
      'street' => (new Varchar($this, $this->translate('Street')))->setDefaultVisible(),
      'city' => (new Varchar($this, $this->translate('City')))->setDefaultVisible(),
      'postal_code' => (new Varchar($this, $this->translate('Postal code'))),
      // Both legacy apps have a branch type, with different values (categories A-D in one, industries in the other), so it is a code list.
      'id_branch_type' => (new Lookup($this, $this->translate('Branch type'), BranchType::class))->setDefaultVisible(),
      // The responsible hygienist. Named id_owner so Hubleto's "owned records" visibility applies.
      // Left empty, it is the client's hygienist.
      'id_owner' => (new Lookup($this, $this->translate('Hygienist'), User::class))
        ->setDefaultVisible()
        ->setEndpoint(UserLookup::endpointFor(Roles::HYGIENIST, 'id_owner'))
      ,
      'has_valid_first_aid_training' => (new Boolean($this, $this->translate('First aid training is valid')))
        ->setDefaultValue(false)
        ->setDefaultVisible()
      ,
      'first_aid_validity_changed_on' => (new Date($this, $this->translate('First aid validity last changed on'))),
      'first_aid_note' => (new Text($this, $this->translate('Note'))),
      'nurse_notes' => (new Text($this, $this->translate('Nurse notes'))),
      'contact_email' => (new Email($this, $this->translate('Contact e-mail'))),
      'is_active' => (new Boolean($this, $this->translate('Active')))->setDefaultValue(true)->setDefaultVisible(),
    ]);
  }

  public function describeTable(): \Hubleto\Framework\Description\Table
  {
    $description = parent::describeTable();
    $description->ui['addButtonText'] = $this->translate('Add branch');
    $description->show(['header', 'fulltextSearch', 'columnSearch', 'moreActionsButton']);
    $description->hide(['footer']);

    $description->addFilter(RecordManagers\Branch::STATUS_FILTER, [
      'title' => $this->translate('Active'),
      'options' => [
        RecordManagers\Branch::STATUS_FILTER_ACTIVE => $this->translate('Active'),
        RecordManagers\Branch::STATUS_FILTER_INACTIVE => $this->translate('Inactive'),
      ],
    ]);

    return $description;
  }

  public function describeForm(): \Hubleto\Framework\Description\Form
  {
    $description = parent::describeForm();
    $description->defaultValues['is_active'] = true;
    $description->ui['showNurseNotes'] = $this->activeUserIsNurse();
    return $description;
  }

  public function getRelationsIncludedInLoadTableData(): array|null
  {
    return ['CUSTOMER', 'OWNER', 'BRANCH_TYPE'];
  }

  public function getMaxReadLevelForLoadTableData(): int
  {
    return 1;
  }

  // The form needs only these; the jobs are shown as a separate table in a tab.
  public function getRelationsIncludedInLoadFormData(): array|null
  {
    return ['CUSTOMER', 'OWNER', 'BRANCH_TYPE'];
  }

  public function onBeforeCreate(array $record): array
  {
    $record = parent::onBeforeCreate($record);
    $record = $this->emptyDatesAsNull($record);

    if (empty($record['id_owner'])) {
      $record['id_owner'] = $this->getHygienistOfClient((int) ($record['id_customer'] ?? 0));
    }

    $hasFirstAid = (bool) ($record['has_valid_first_aid_training'] ?? false);
    if ($hasFirstAid && empty($record['first_aid_validity_changed_on'])) {
      $record['first_aid_validity_changed_on'] = date('Y-m-d');
    }
    return $record;
  }

  public function onBeforeUpdate(array $record): array
  {
    $record = parent::onBeforeUpdate($record);
    $record = $this->emptyDatesAsNull($record);
    // The client stays as created (one legacy app), so the branch's documents and history stay with it.
    $record = $this->keepStoredValues($record, ['id_customer']);

    // Nurse notes are for nurses only (both legacy apps); others do not get them, so they must not overwrite them.
    if (!$this->activeUserIsNurse()) unset($record['nurse_notes']);

    // Both legacy apps note the day the first aid validity changed.
    $originalBranch = $this->record->where('id', (int) $record['id'])->first();
    $firstAidChanged = $originalBranch && array_key_exists('has_valid_first_aid_training', $record)
      && (bool) $record['has_valid_first_aid_training'] != (bool) $originalBranch->has_valid_first_aid_training;
    if ($firstAidChanged) {
      $record['first_aid_validity_changed_on'] = date('Y-m-d');
    }
    return $record;
  }

  // Every read (forms, lists, the record API) goes through here.
  public function onAfterLoadRecord(array $record): array
  {
    $record = parent::onAfterLoadRecord($record);
    if (!$this->activeUserIsNurse()) unset($record['nurse_notes']);
    return $record;
  }

  private function activeUserIsNurse(): bool
  {
    /** @var Roles */
    $roles = $this->getService(Roles::class);
    return $roles->activeUserHasRole(Roles::NURSE);
  }

  private function getHygienistOfClient(int $idCustomer): ?int
  {
    $idOwner = (int) $this->getModel(Customer::class)->record->where('id', $idCustomer)->value('id_owner');
    return $idOwner > 0 ? $idOwner : null;
  }
}
