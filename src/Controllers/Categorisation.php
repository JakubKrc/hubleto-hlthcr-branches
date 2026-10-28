<?php

namespace Hubleto\App\External\WaiBlue\HlthcrBranches\Controllers;

use Hubleto\App\External\WaiBlue\HlthcrBranches\Services\CategorisationMatrix;

/**
 * Printable categorisation of work of one branch (job positions x hazards).
 */
class Categorisation extends \Hubleto\Erp\Controller
{
  public bool $hideDefaultDesktop = true;

  public function prepareView(): void
  {
    parent::prepareView();

    $idBranch = $this->router()->urlParamAsInteger('idBranch');
    $this->viewParams['matrix'] = $this->getService(CategorisationMatrix::class)->build($idBranch);
    $this->viewParams['printedOn'] = date('d.m.Y');

    $this->setView('@Hubleto:App:External:WaiBlue:HlthcrBranches/Categorisation.twig');
  }
}
