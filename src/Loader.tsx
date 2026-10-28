import React from 'react';
import App from '@hubleto/react-ui/core/App'
import Translator from '@hubleto/react-ui/core/Translator';
import FormCustomizer from '@hubleto/react-ui/core/FormCustomizer';
import { type FormMeta } from '@hubleto/react-ui/components/fc/FormInterfaces';
import TableBranches from './Components/FC/TableBranches'
import TableJobs from './Components/FC/TableJobs'
import TableJobHasHazards from './Components/FC/TableJobHasHazards'
import TableBranchHasClinics from './Components/FC/TableBranchHasClinics'
import TableDeficiencies from './Components/FC/TableDeficiencies'
import TableBranchTypes from './Components/FC/TableBranchTypes'

const parentApp = 'Hubleto/App/External/WaiBlue/HlthcrBranches';
const T = new Translator(parentApp + '/Loader', 'Loader');

// "Prevádzky" tab in the customer form (community Customers app).
function mountCustomerBranchesTab() {
  return {
    title: T.translate('Branches'),
    content: (form: FormMeta) => {
      if (form.id <= 0) return <div className='alert alert-info'>{T.translate('Save the client first.')}</div>;
      return <TableBranches uid={form.uid + '_table_branches'} parentForm={form} idCustomer={form.id} />;
    },
  };
}

class HlthcrBranchesApp extends App {
  init() {
    super.init();
    globalThis.hubleto.registerReactComponent('HlthcrBranchesTableBranches', TableBranches);
    globalThis.hubleto.registerReactComponent('HlthcrBranchesTableJobs', TableJobs);
    globalThis.hubleto.registerReactComponent('HlthcrBranchesTableJobHasHazards', TableJobHasHazards);
    globalThis.hubleto.registerReactComponent('HlthcrBranchesTableBranchHasClinics', TableBranchHasClinics);
    globalThis.hubleto.registerReactComponent('HlthcrBranchesTableDeficiencies', TableDeficiencies);
    globalThis.hubleto.registerReactComponent('HlthcrBranchesTableBranchTypes', TableBranchTypes);

    FormCustomizer.addTab('FormCustomer', 'hlthcr-branches', mountCustomerBranchesTab);
  }
}

globalThis.hubleto.registerApp(parentApp, new HlthcrBranchesApp());
