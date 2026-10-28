<?php

namespace Hubleto\App\External\WaiBlue\HlthcrBranches\Models\RecordManagers;

use Hubleto\App\External\WaiBlue\HlthcrCore\Models\RecordManagers\Traits\OffersActiveRecords;

class BranchType extends \Hubleto\Erp\RecordManager
{
  use OffersActiveRecords;

  public $table = 'hlthcr_branch_types';

  public function prepareReadQuery(mixed $query = null, int $level = 0, array|null $includeRelations = null): mixed
  {
    return parent::prepareReadQuery($query, $level, $includeRelations);
  }

  public function restrictToOfferedRecords(mixed $query): mixed
  {
    return $this->restrictToActiveOrChosen($query, 'id_branch_type');
  }

  public function addUrlFiltersToQuery(mixed $query): mixed
  {
    return parent::addUrlFiltersToQuery($query);
  }

  public function prepareLookupData(array $dataRaw): array
  {
    return parent::prepareLookupData($dataRaw);
  }
}
