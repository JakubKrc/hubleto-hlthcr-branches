import Translator from '@hubleto/react-ui/core/Translator';
import Form from '@hubleto/react-ui/components/fc/Form';
import { type FormProps } from '@hubleto/react-ui/components/fc/FormInterfaces';
import Input from '@hubleto/react-ui/components/fc/FormComponents/Input';

const componentName = 'FormBranchHasClinic';
const parentApp = 'Hubleto/App/External/WaiBlue/HlthcrBranches';
const T = new Translator(parentApp + '/Loader', 'Components/' + componentName);

const FormBranchHasClinic = (props: FormProps) => <Form
  componentName={componentName}
  parentApp={parentApp}
  model={parentApp + '/Models/BranchHasClinic'}
  urlSlug='hlthcr-branches/branch-has-clinics'
  title={{ sub: T.translate('Branch clinic')}}
  tabs={{default: {content: () => <div className='grid grid-cols-1 md:grid-cols-2 gap-2'>
    <Input field='id_branch' />
    <Input field='id_clinic' />
  </div>}}}
  {...props}
/>

export default FormBranchHasClinic;
