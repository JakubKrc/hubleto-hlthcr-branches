<?php

namespace Hubleto\App\External\WaiBlue\HlthcrBranches;

use Hubleto\App\Community\Customers\Models\Customer;
use Hubleto\App\Community\Workflow\Models\Workflow as WorkflowModel;
use Hubleto\App\Community\Workflow\Models\WorkflowStep;
use Hubleto\App\External\WaiBlue\HlthcrCore\Models\Clinic;
use Hubleto\App\External\WaiBlue\HlthcrCore\Models\Hazard;
use Hubleto\App\External\WaiBlue\HlthcrCore\Services\DemoData;

class Loader extends \Hubleto\App\External\WaiBlue\HlthcrCore\HlthcrApp
{
  // Tags of the deficiency workflow steps; a deficiency's status is the tag of its step.
  const DEFICIENCY_STEP_NEW = 'hlthcr-deficiency-new';
  const DEFICIENCY_STEP_IN_PROGRESS = 'hlthcr-deficiency-in-progress';
  const DEFICIENCY_STEP_RESOLVED = 'hlthcr-deficiency-resolved';

  public function init(): void
  {
    parent::init();

    $this->router()->get([
      '/^hlthcr-branches\/?$/' => Controllers\Home::class,
      '/^hlthcr-branches\/settings\/?$/' => Controllers\Settings::class,
      '/^hlthcr-branches\/branches\/(?<idBranch>\d+)\/categorisation\/?$/' => Controllers\Categorisation::class,
      '/^hlthcr-branches\/api\/get-job-hazard-levels\/?$/' => Controllers\Api\GetJobHazardLevels::class,
      '/^hlthcr-branches\/api\/save-job-hazard-levels\/?$/' => Controllers\Api\SaveJobHazardLevels::class,
    ]);
    $this->router()->crud('hlthcr-branches/branches', Controllers\Branches::class);
    $this->router()->crud('hlthcr-branches/branch-types', Controllers\BranchTypes::class);
    $this->router()->crud('hlthcr-branches/jobs', Controllers\Jobs::class);
    $this->router()->crud('hlthcr-branches/job-has-hazards', Controllers\JobHasHazards::class);
    $this->router()->crud('hlthcr-branches/branch-has-clinics', Controllers\BranchHasClinics::class);
    $this->router()->crud('hlthcr-branches/deficiencies', Controllers\Deficiencies::class);

    $this->addSearchSwitch('p', 'branches');

    /** @var EventListeners\CustomerChanges */
    $customerChanges = $this->getService(EventListeners\CustomerChanges::class);
    $this->eventManager()->addEventListener('onModelAfterCreate', $customerChanges);
    $this->eventManager()->addEventListener('onModelAfterUpdate', $customerChanges);

    /** @var \Hubleto\App\Community\Workflow\Manager $workflowManager */
    $workflowManager = $this->getService(\Hubleto\App\Community\Workflow\Manager::class);
    $workflowManager->addWorkflowGroup($this, Models\Deficiency::WORKFLOW_GROUP, Workflow::class);

    /** @var \Hubleto\App\Community\Settings\Loader $settingsApp */
    $settingsApp = $this->appManager()->getApp(\Hubleto\App\Community\Settings\Loader::class);
    $settingsApp->addSetting($this, [
      'title' => $this->translate('Branch types'),
      'icon' => 'fas fa-tags',
      'url' => 'hlthcr-branches/branch-types',
    ]);
  }

  public function installApp(int $round): void
  {
    if ($round == 1) {
      $this->getModel(Models\BranchType::class)->upgradeSchema();
      $this->getModel(Models\Branch::class)->upgradeSchema();
      $this->getModel(Models\Job::class)->upgradeSchema();
      $this->getModel(Models\JobHasHazard::class)->upgradeSchema();
      $this->getModel(Models\BranchHasClinic::class)->upgradeSchema();
      $this->getModel(Models\Deficiency::class)->upgradeSchema();
      // "php hubleto app install" runs only round 1, so foreign keys are created here too (Hubleto skips them later).
      $this->installForeignKeys();

      $this->installDeficiencyWorkflow();
    }
  }

  private function installDeficiencyWorkflow(): void
  {
    /** @var WorkflowModel */
    $mWorkflow = $this->getModel(WorkflowModel::class);
    /** @var WorkflowStep */
    $mWorkflowStep = $this->getModel(WorkflowStep::class);

    $workflowExists = $mWorkflow->record->where('group', Models\Deficiency::WORKFLOW_GROUP)->exists();
    if ($workflowExists) return;

    $idWorkflow = $mWorkflow->record->recordCreate([
      'name' => $this->translate('Deficiencies'),
      'show_in_kanban' => 1,
      'order' => 50,
      'group' => Models\Deficiency::WORKFLOW_GROUP,
    ])['id'];

    // The first step is the default for new deficiencies.
    $steps = [
      ['name' => $this->translate('Newly found'), 'color' => '#d33115', 'tag' => self::DEFICIENCY_STEP_NEW],
      ['name' => $this->translate('Being resolved'), 'color' => '#e27300', 'tag' => self::DEFICIENCY_STEP_IN_PROGRESS],
      ['name' => $this->translate('Resolved'), 'color' => '#4caf50', 'tag' => self::DEFICIENCY_STEP_RESOLVED],
    ];
    $order = 1;
    foreach ($steps as $step) {
      $mWorkflowStep->record->recordCreate([
        'id_workflow' => $idWorkflow,
        'name' => $step['name'],
        'color' => $step['color'],
        'tag' => $step['tag'],
        'order' => $order,
      ]);
      $order++;
    }
  }

  public function search(array $expressions): array
  {
    $branchesQuery = $this->getModel(Models\Branch::class)->record->prepareReadQuery();

    foreach ($expressions as $expression) {
      $branchesQuery = $branchesQuery->where(function ($matchingBranch) use ($expression) {
        $matchingBranch->orWhere('hlthcr_branches.name', 'like', '%' . $expression . '%');
        $matchingBranch->orWhere('hlthcr_branches.city', 'like', '%' . $expression . '%');
        $matchingBranch->orWhere('hlthcr_branches.street', 'like', '%' . $expression . '%');
      });
    }

    $results = [];
    foreach ($branchesQuery->limit(20)->get()->toArray() as $branch) {
      $results[] = [
        'id' => $branch['id'],
        'label' => $branch['name'],
        'url' => 'hlthcr-branches/branches/' . $branch['id'],
        'description' => trim(($branch['street'] ?? '') . ' ' . ($branch['city'] ?? '')),
      ];
    }

    $jobsQuery = $this->getModel(Models\Job::class)->record->prepareReadQuery()->with('BRANCH');
    foreach ($expressions as $expression) {
      $jobsQuery = $jobsQuery->where('hlthcr_jobs.name', 'like', '%' . $expression . '%');
    }
    foreach ($jobsQuery->limit(20)->get()->toArray() as $job) {
      $results[] = [
        'id' => $job['id'],
        'label' => $job['name'],
        'url' => 'hlthcr-branches/jobs/' . $job['id'],
        'description' => $this->translate('Job position') . ', ' . ($job['BRANCH']['name'] ?? ''),
      ];
    }

    return $results;
  }

  public function renderSecondSidebar(): string
  {
    return '
      ' . $this->secondSidebarTitle() . '
      <div class="app-sidebar-buttons">
        ' . $this->secondSidebarButton('hlthcr-branches/branches', 'fas fa-industry', $this->translate('Branches')) . '
        ' . $this->secondSidebarButton('hlthcr-branches/jobs', 'fas fa-helmet-safety', $this->translate('Job positions')) . '
        ' . $this->secondSidebarButton('hlthcr-branches/deficiencies', 'fas fa-circle-exclamation', $this->translate('Deficiencies')) . '
        ' . $this->secondSidebarButton('hlthcr-branches/branch-types', 'fas fa-tags', $this->translate('Branch types')) . '
        ' . $this->secondSidebarButton('hlthcr-branches/settings', 'fas fa-cog', $this->translate('Settings')) . '
      </div>
    ';
  }

  public function renderAlerts(): string
  {
    return '';
  }

  // No number in the menu: neither legacy app shows one.
  public function getSidebarBadgeNumber(): int
  {
    return 0;
  }

  public function generateHlthcrDemoData(): void
  {
    /** @var Customer */
    $mCustomer = $this->getModel(Customer::class);
    /** @var Models\Branch */
    $mBranch = $this->getModel(Models\Branch::class);

    $demoCompanyIds = $this->getService(DemoData::class)->getDemoCompanyIds();
    $demoCustomers = $mCustomer->record->whereIn('company_id', $demoCompanyIds)->get();

    foreach ($demoCustomers as $demoCustomer) {
      $branch = $mBranch->record->where('id_customer', $demoCustomer->id)->first();
      if (!$branch) {
        $createdBranch = $mBranch->record->recordCreate([
          'name' => $demoCustomer->name,
          'id_customer' => $demoCustomer->id,
          'street' => $demoCustomer->street_line_1,
          'city' => $demoCustomer->city,
          'postal_code' => $demoCustomer->postal_code,
          'id_owner' => $demoCustomer->id_owner,
          'is_active' => true,
        ]);
        $branch = $mBranch->record->find($createdBranch['id']);
      }

      $this->generateDemoBranchContent($branch);
    }
  }

  /**
   * Adds demo job positions with hazard levels, a clinic and a deficiency to a branch (once).
   */
  private function generateDemoBranchContent(mixed $branch): void
  {
    /** @var Models\Job */
    $mJob = $this->getModel(Models\Job::class);
    /** @var Models\JobHasHazard */
    $mJobHazard = $this->getModel(Models\JobHasHazard::class);
    /** @var Models\BranchHasClinic */
    $mBranchClinic = $this->getModel(Models\BranchHasClinic::class);
    /** @var Models\Deficiency */
    $mDeficiency = $this->getModel(Models\Deficiency::class);

    $branchAlreadyHasContent = $mJob->record->where('id_branch', $branch->id)->exists();
    if ($branchAlreadyHasContent) return;

    $demoHazardNames = ['Hluk', 'Fyzická záťaž', 'Chemické faktory', 'Práca so zobrazovacími jednotkami', 'Audiometria', 'EKG'];
    $hazardIdsByName = [];
    foreach ($demoHazardNames as $hazardName) {
      $hazard = $this->getModel(Hazard::class)->record->where('name', $hazardName)->first();
      if ($hazard) $hazardIdsByName[$hazardName] = (int) $hazard->id;
    }

    // Levels per hazard name; 1 means "yes" for hazard types without categories.
    // Only the office job sets its own exam interval; the others use the hazard intervals.
    $demoJobs = [
      [
        'name' => 'Skladník',
        'exam_interval_months' => null,
        'levels' => ['Hluk' => 2, 'Fyzická záťaž' => 3, 'Audiometria' => 1],
      ],
      [
        'name' => 'Operátor výroby',
        'exam_interval_months' => null,
        'levels' => ['Hluk' => 4, 'Chemické faktory' => 3, 'Audiometria' => 1, 'EKG' => 1],
      ],
      [
        'name' => 'Administratívny pracovník',
        'exam_interval_months' => 48,
        'levels' => ['Práca so zobrazovacími jednotkami' => 1],
      ],
    ];
    foreach ($demoJobs as $demoJob) {
      $createdJob = $mJob->record->recordCreate([
        'name' => $demoJob['name'],
        'id_branch' => $branch->id,
        'exam_interval_months' => $demoJob['exam_interval_months'],
      ]);
      foreach ($demoJob['levels'] as $hazardName => $level) {
        if (!isset($hazardIdsByName[$hazardName])) continue;
        $mJobHazard->record->recordCreate([
          'id_job' => $createdJob['id'],
          'id_hazard' => $hazardIdsByName[$hazardName],
          'level' => $level,
        ]);
      }
    }

    $firstClinic = $this->getModel(Clinic::class)->record->orderBy('id')->first();
    if ($firstClinic) {
      $mBranchClinic->record->recordCreate(['id_branch' => $branch->id, 'id_clinic' => $firstClinic->id]);
    }

    // recordCreate() runs Deficiency::onAfterCreate(), which sets the default workflow step.
    $mDeficiency->record->recordCreate([
      'id_branch' => $branch->id,
      'description' => $this->translate('Missing first aid kit in the warehouse.'),
      'note' => $this->translate('Place a complete first aid kit next to the entrance.'),
    ]);
    $deficiencyBeingResolved = $mDeficiency->record->recordCreate([
      'id_branch' => $branch->id,
      'description' => $this->translate('Escape routes are not marked.'),
      'note' => $this->translate('Signs are ordered.'),
    ]);
    $this->moveDeficiencyToStep((int) $deficiencyBeingResolved['id'], self::DEFICIENCY_STEP_IN_PROGRESS);
  }

  private function moveDeficiencyToStep(int $idDeficiency, string $stepTag): void
  {
    $workflowStep = $this->getModel(WorkflowStep::class)->record->where('tag', $stepTag)->first();
    if (!$workflowStep) return;

    $this->getModel(Models\Deficiency::class)->record->where('id', $idDeficiency)->update(['id_workflow_step' => $workflowStep->id]);
  }
}
