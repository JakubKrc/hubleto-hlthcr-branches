<?php

namespace Hubleto\App\External\WaiBlue\HlthcrBranches\Controllers;

class BranchHasClinics extends \Hubleto\Erp\Controller
{
  public function prepareView(): void
  {
    parent::prepareView();
    $this->setView('@Hubleto:App:External:WaiBlue:HlthcrBranches/BranchHasClinics.twig');
  }
}
