<?php

namespace Hubleto\App\External\WaiBlue\HlthcrBranches;

/**
 * Deficiencies on the Workflow kanban board, grouped by status.
 */
class Workflow extends \Hubleto\App\Community\Workflow\Workflow
{
  public function loadItems(int $idWorkflow, array $filters): array
  {
    /** @var Models\Deficiency */
    $mDeficiency = $this->getModel(Models\Deficiency::class);
    $query = $mDeficiency->record->prepareReadQuery()
      ->where('hlthcr_deficiencies.id_workflow', $idWorkflow)
      ->with(['BRANCH'])
    ;

    // The board's user buttons: deficiencies of the branches of that hygienist.
    $idOwner = (int) ($filters['fOwner'] ?? 0);
    if ($idOwner > 0) {
      $branchIdsOfOwner = $this->getModel(Models\Branch::class)->record->where('id_owner', $idOwner)->pluck('id');
      $query = $query->whereIn('hlthcr_deficiencies.id_branch', $branchIdsOfOwner);
    }

    $deficiencies = $query->get()->toArray();

    $items = [];
    foreach ($deficiencies as $deficiency) {
      $deficiency['_WORKFLOW_ITEM_TITLE'] = mb_substr((string) $deficiency['description'], 0, 80);
      $deficiency['_WORKFLOW_ITEM_SUBTITLE'] = $deficiency['BRANCH']['name'] ?? '';
      $deficiency['_DETAIL_URL'] = 'hlthcr-branches/deficiencies/' . $deficiency['id'];
      $deficiency['_DETAIL_VIEW'] = '@Hubleto:App:Community:Workflow/WorkflowItemDetail.twig';
      $items[] = $deficiency;
    }

    return $items;
  }
}
