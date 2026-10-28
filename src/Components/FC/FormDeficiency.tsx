import React from 'react';
import Translator from '@hubleto/react-ui/core/Translator';
import Form, { FormMetaContext } from '@hubleto/react-ui/components/fc/Form';
import { type FormProps } from '@hubleto/react-ui/components/fc/FormInterfaces';
import Input from '@hubleto/react-ui/components/fc/FormComponents/Input';

const componentName = 'FormDeficiency';
const parentApp = 'Hubleto/App/External/WaiBlue/HlthcrBranches';
const T = new Translator(parentApp + '/Loader', 'Components/' + componentName);

// The branch stays as created (the server keeps it), so it is read-only after saving.
const TabDefault = () => {
  const form = React.useContext(FormMetaContext);
  const isSaved = form.id > 0;
  return <div className='grid grid-cols-1 md:grid-cols-2 gap-2'>
    <Input field='id_branch' readonly={isSaved} />
    <Input field='description' />
    <Input field='note' />
  </div>;
}

const FormDeficiency = (props: FormProps) => <Form
  componentName={componentName}
  parentApp={parentApp}
  model={parentApp + '/Models/Deficiency'}
  urlSlug='hlthcr-branches/deficiencies'
  title={{field: 'description', sub: T.translate('Deficiency')}}
  tabs={{default: {content: () => <TabDefault />}}}
  {...props}
/>

export default FormDeficiency;
