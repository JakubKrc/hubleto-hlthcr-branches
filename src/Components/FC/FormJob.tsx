import React from 'react';
import Translator from '@hubleto/react-ui/core/Translator';
import Form, { FormMetaContext } from '@hubleto/react-ui/components/fc/Form';
import { type FormProps } from '@hubleto/react-ui/components/fc/FormInterfaces';
import Input from '@hubleto/react-ui/components/fc/FormComponents/Input';
import JobHazardLevelsEditor from './JobHazardLevelsEditor';

const componentName = 'FormJob';
const parentApp = 'Hubleto/App/External/WaiBlue/HlthcrBranches';
const T = new Translator(parentApp + '/Loader', 'Components/' + componentName);

const TabHazardLevels = () => {
  const form = React.useContext(FormMetaContext);
  if (form.id <= 0) return <div className='alert alert-info'>{T.translate('Save the job position first.')}</div>;
  return <JobHazardLevelsEditor idJob={form.id} />;
}

const FormJob = (props: FormProps) => <Form
  componentName={componentName}
  parentApp={parentApp}
  model={parentApp + '/Models/Job'}
  urlSlug='hlthcr-branches/jobs'
  title={{field: 'name', sub: T.translate('Job position')}}
  tabs={{
    default: {title: <b>{T.translate('Job position')}</b>, content: () => <div className='grid grid-cols-1 md:grid-cols-2 gap-2'>
      <Input field='name' />
      <Input field='id_branch' />
      <Input field='exam_interval_months' />
    </div>},
    hazardLevels: {title: T.translate('Hazard levels'), content: () => <TabHazardLevels />},
  }}
  {...props}
/>;

export default FormJob;
