import Translator from '@hubleto/react-ui/core/Translator';
import Form from '@hubleto/react-ui/components/fc/Form';
import { type FormProps } from '@hubleto/react-ui/components/fc/FormInterfaces';
import Input from '@hubleto/react-ui/components/fc/FormComponents/Input';

const componentName = 'FormBranchType';
const parentApp = 'Hubleto/App/External/WaiBlue/HlthcrBranches';
const T = new Translator(parentApp + '/Loader', 'Components/' + componentName);

const FormBranchType = (props: FormProps) => <Form
  componentName={componentName}
  parentApp={parentApp}
  model={parentApp + '/Models/BranchType'}
  urlSlug='hlthcr-branches/branch-types'
  title={{field: 'name', sub: T.translate('Branch type')}}
  tabs={{default: {content: () => <div className='grid grid-cols-1 gap-2'>
    <Input field='name' />
  </div>}}}
  {...props}
/>

export default FormBranchType;
