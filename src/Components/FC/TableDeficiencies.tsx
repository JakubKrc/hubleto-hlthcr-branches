import Table from '@hubleto/react-ui/components/fc/Table'
import { type TableMeta, type TableProps } from '@hubleto/react-ui/components/fc/TableInterfaces'
import FormDeficiency from './FormDeficiency'

interface TableDeficienciesProps extends TableProps {
  idBranch?: number,
}

const componentName = 'TableDeficiencies'
const parentApp = 'Hubleto/App/External/WaiBlue/HlthcrBranches'

const TableDeficiencies = (props: TableDeficienciesProps) => {
  const idBranch = props.idBranch ?? 0;
  return <Table
    componentName={componentName}
    parentApp={parentApp}
    model={parentApp + '/Models/Deficiency'}
    baseUrlSlug='hlthcr-branches/deficiencies'
    endpointParams={{idBranch: idBranch}}
    formDefaultValues={idBranch > 0 ? {id_branch: idBranch} : {}}
    formModalProps={{type: 'right wide'}}
    renderForm={(table: TableMeta) => <FormDeficiency {...table.getDefaultFormProps()} />}
    {...props}
  />;
}

export default TableDeficiencies;
