<?php

namespace Hubleto\App\External\WaiBlue\HlthcrBranches\EventListeners;

use Hubleto\App\Community\Customers\Models\Customer;
use Hubleto\App\External\WaiBlue\HlthcrBranches\Models\Branch;
use Hubleto\Framework\Interfaces\ModelInterface;

/**
 * Keeps branches in sync with their client (community Customer), like the legacy app did.
 */
class CustomerChanges extends \Hubleto\Framework\EventListener implements \Hubleto\Framework\Interfaces\EventListenerInterface
{
  // A new client gets its first branch with the same name and address.
  public function onModelAfterCreate(ModelInterface $model, array $savedRecord): void
  {
    if (!($model instanceof Customer)) return;

    $idCustomer = (int) ($savedRecord['id'] ?? 0);
    if ($idCustomer <= 0) return;

    /** @var Branch */
    $mBranch = $this->getModel(Branch::class);
    $mBranch->record->recordCreate([
      'name' => $savedRecord['name'] ?? '',
      'id_customer' => $idCustomer,
      'street' => $savedRecord['street_line_1'] ?? null,
      'city' => $savedRecord['city'] ?? null,
      'postal_code' => $savedRecord['postal_code'] ?? null,
      'id_owner' => $savedRecord['id_owner'] ?? null,
      'is_active' => true,
    ]);
  }

  // Deactivating a client deactivates all its branches; a new hygienist of the client takes over its branches (as in one legacy app).
  public function onModelAfterUpdate(ModelInterface $model, array $originalRecord, array $savedRecord): void
  {
    if (!($model instanceof Customer)) return;

    /** @var Branch */
    $mBranch = $this->getModel(Branch::class);
    $idCustomer = (int) $savedRecord['id'];

    $wasActive = (bool) ($originalRecord['is_active'] ?? true);
    $isActive = (bool) ($savedRecord['is_active'] ?? $wasActive);
    $customerWasDeactivated = $wasActive && !$isActive;
    if ($customerWasDeactivated) {
      $mBranch->record->where('id_customer', $idCustomer)->update(['is_active' => 0]);
    }

    $idPreviousOwner = (int) ($originalRecord['id_owner'] ?? 0);
    $idOwner = (int) ($savedRecord['id_owner'] ?? 0);
    $hygienistChanged = $idOwner > 0 && $idOwner != $idPreviousOwner;
    if ($hygienistChanged) {
      $mBranch->record->where('id_customer', $idCustomer)->update(['id_owner' => $idOwner]);
    }
  }
}
