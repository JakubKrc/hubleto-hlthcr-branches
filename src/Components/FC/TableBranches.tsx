import Table from '@hubleto/react-ui/components/fc/Table'
import { type TableMeta, type TableProps } from '@hubleto/react-ui/components/fc/TableInterfaces'
import FormBranch from './FormBranch'

interface TableBranchesProps extends TableProps {
  idCustomer?: number,
}

const componentName = 'TableBranches'
const parentApp = 'Hubleto/App/External/WaiBlue/HlthcrBranches'

const TableBranches = (props: TableBranchesProps) => {
  const idCustomer = props.idCustomer ?? 0;
  return <Table
    componentName={componentName}
    parentApp={parentApp}
    model={parentApp + '/Models/Branch'}
    baseUrlSlug='hlthcr-branches/branches'
    endpointParams={{idCustomer: idCustomer}}
    formDefaultValues={idCustomer > 0 ? {id_customer: idCustomer} : {}}
    formModalProps={{type: 'right wide'}}
    renderForm={(table: TableMeta) => <FormBranch {...table.getDefaultFormProps()} />}
    {...props}
  />;
}

export default TableBranches;
