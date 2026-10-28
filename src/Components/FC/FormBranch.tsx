import React from 'react';
import Translator from '@hubleto/react-ui/core/Translator';
import Form, { FormMetaContext } from '@hubleto/react-ui/components/fc/Form';
import { type FormProps } from '@hubleto/react-ui/components/fc/FormInterfaces';
import Input from '@hubleto/react-ui/components/fc/FormComponents/Input';
import TableJobs from './TableJobs';
import TableBranchHasClinics from './TableBranchHasClinics';
import TableDeficiencies from './TableDeficiencies';
import CategorisationTab from './CategorisationTab';

const componentName = 'FormBranch';
const parentApp = 'Hubleto/App/External/WaiBlue/HlthcrBranches';
const T = new Translator(parentApp + '/Loader', 'Components/' + componentName);

// Both legacy apps show the nurse notes to nurses only (the server sends them only to nurses).
const TabDefault = () => {
  const form = React.useContext(FormMetaContext);
  const showNurseNotes = Boolean(form.description?.ui?.showNurseNotes);
  return <div className='grid grid-cols-1 md:grid-cols-2 gap-2'>
  <div>
    <Input field='name' />
    <Input field='id_customer' />
    <Input field='street' />
    <Input field='city' />
    <Input field='postal_code' />
    <Input field='id_branch_type' />
    <Input field='id_owner' />
    <Input field='is_active' />
  </div>
  <div>
    <Input field='has_valid_first_aid_training' />
    <Input field='first_aid_validity_changed_on' />
    <Input field='first_aid_note' />
    {showNurseNotes ? <Input field='nurse_notes' /> : null}
    <Input field='contact_email' />
  </div>
</div>;
}

// The other tabs list records of this branch; they exist only after the branch is saved.
const SaveBranchFirst = () => <div className='alert alert-info'>{T.translate('Save the branch first.')}</div>;

const TabJobs = () => {
  const form = React.useContext(FormMetaContext);
  if (form.id <= 0) return <SaveBranchFirst />;
  return <TableJobs uid={form.uid + '_table_jobs'} parentForm={form} idBranch={form.id} />;
}

const TabClinics = () => {
  const form = React.useContext(FormMetaContext);
  if (form.id <= 0) return <SaveBranchFirst />;
  return <TableBranchHasClinics uid={form.uid + '_table_clinics'} parentForm={form} idBranch={form.id} />;
}

const TabDeficiencies = () => {
  const form = React.useContext(FormMetaContext);
  if (form.id <= 0) return <SaveBranchFirst />;
  return <TableDeficiencies uid={form.uid + '_table_deficiencies'} parentForm={form} idBranch={form.id} />;
}

const TabCategorisation = () => {
  const form = React.useContext(FormMetaContext);
  if (form.id <= 0) return <SaveBranchFirst />;
  return <CategorisationTab idBranch={form.id} />;
}

const FormBranch = (props: FormProps) => <Form
  componentName={componentName}
  parentApp={parentApp}
  model={parentApp + '/Models/Branch'}
  urlSlug='hlthcr-branches/branches'
  title={{field: 'name', sub: T.translate('Branch')}}
  tabs={{
    default: {title: <b>{T.translate('Branch')}</b>, content: () => <TabDefault />},
    jobs: {title: T.translate('Job positions'), content: () => <TabJobs />},
    clinics: {title: T.translate('Clinics'), content: () => <TabClinics />},
    deficiencies: {title: T.translate('Deficiencies'), content: () => <TabDeficiencies />},
    categorisation: {title: T.translate('Categorisation of work'), content: () => <TabCategorisation />},
  }}
  {...props}
/>;

export default FormBranch;
