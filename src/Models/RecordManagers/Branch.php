<?php

namespace Hubleto\App\External\WaiBlue\HlthcrBranches\Models\RecordManagers;

use Hubleto\App\Community\Auth\Models\RecordManagers\User;
use Hubleto\App\Community\Customers\Models\RecordManagers\Customer;
use Hubleto\App\External\WaiBlue\HlthcrBranches\Services\BranchScope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Branch extends \Hubleto\Erp\RecordManager
{
  public $table = 'hlthcr_branches';

  // Options of the status filter in the table (URL filters[STATUS_FILTER]=<option>).
  const STATUS_FILTER = 'fBranchStatus';
  const STATUS_FILTER_ACTIVE = 0;
  const STATUS_FILTER_INACTIVE = 1;

  public function CUSTOMER(): BelongsTo
  {
    return $this->belongsTo(Customer::class, 'id_customer', 'id');
  }

  public function OWNER(): BelongsTo
  {
    return $this->belongsTo(User::class, 'id_owner', 'id');
  }

  public function BRANCH_TYPE(): BelongsTo
  {
    return $this->belongsTo(BranchType::class, 'id_branch_type', 'id');
  }

  public function JOBS(): HasMany
  {
    return $this->hasMany(Job::class, 'id_branch', 'id');
  }

  // Owner-based visibility (hygienist sees own branches) is applied by the parent, because the table has id_owner.
  public function prepareReadQuery(mixed $query = null, int $level = 0, array|null $includeRelations = null): mixed
  {
    return parent::prepareReadQuery($query, $level, $includeRelations);
  }

  public function prepareLookupQuery(string $search): mixed
  {
    $query = parent::prepareLookupQuery($search);

    // Dropdowns offer active branches only, as in both legacy apps (a chosen one still shows in its form).
    $query = $query->where($this->table . '.is_active', 1);

    $hubleto = \Hubleto\Erp\Loader::getGlobalApp();
    $query = $hubleto->getService(BranchScope::class)->restrictToVisibleBranches($query, $this->table . '.id');

    $idCustomer = $hubleto->router()->urlParamAsInteger('idCustomer');
    if ($idCustomer > 0) {
      $query = $query->where($this->table . '.id_customer', $idCustomer);
    }

    return $query;
  }

  public function addUrlFiltersToQuery(mixed $query): mixed
  {
    $query = parent::addUrlFiltersToQuery($query);

    $hubleto = \Hubleto\Erp\Loader::getGlobalApp();
    $router = $hubleto->router();

    $idCustomer = $router->urlParamAsInteger('idCustomer');
    if ($idCustomer > 0) {
      $query = $query->where($this->table . '.id_customer', $idCustomer);
    }

    $filters = $router->urlParamAsArray('filters');
    if (isset($filters[self::STATUS_FILTER])) {
      $showInactive = (int) $filters[self::STATUS_FILTER] == self::STATUS_FILTER_INACTIVE;
      $query = $query->where($this->table . '.is_active', $showInactive ? 0 : 1);
    }

    return $query;
  }

  public function prepareLookupData(array $dataRaw): array
  {
    return parent::prepareLookupData($dataRaw);
  }
}
