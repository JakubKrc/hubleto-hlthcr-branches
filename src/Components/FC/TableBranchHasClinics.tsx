import Table from '@hubleto/react-ui/components/fc/Table'
import { type TableMeta, type TableProps } from '@hubleto/react-ui/components/fc/TableInterfaces'
import FormBranchHasClinic from './FormBranchHasClinic'

interface TableBranchHasClinicsProps extends TableProps {
  idBranch?: number,
}

const componentName = 'TableBranchHasClinics'
const parentApp = 'Hubleto/App/External/WaiBlue/HlthcrBranches'

const TableBranchHasClinics = (props: TableBranchHasClinicsProps) => {
  const idBranch = props.idBranch ?? 0;
  return <Table
    componentName={componentName}
    parentApp={parentApp}
    model={parentApp + '/Models/BranchHasClinic'}
    baseUrlSlug='hlthcr-branches/branch-has-clinics'
    endpointParams={{idBranch: idBranch}}
    formDefaultValues={idBranch > 0 ? {id_branch: idBranch} : {}}
    formModalProps={{type: 'right wide'}}
    renderForm={(table: TableMeta) => <FormBranchHasClinic {...table.getDefaultFormProps()} />}
    {...props}
  />;
}

export default TableBranchHasClinics;
