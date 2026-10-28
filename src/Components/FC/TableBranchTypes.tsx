import Table from '@hubleto/react-ui/components/fc/Table'
import { type TableMeta, type TableProps } from '@hubleto/react-ui/components/fc/TableInterfaces'
import FormBranchType from './FormBranchType'

const componentName = 'TableBranchTypes'
const parentApp = 'Hubleto/App/External/WaiBlue/HlthcrBranches'

const TableBranchTypes = (props: TableProps) => <Table
  componentName={componentName}
  parentApp={parentApp}
  model={parentApp + '/Models/BranchType'}
  baseUrlSlug='hlthcr-branches/branch-types'
  renderForm={(table: TableMeta) => <FormBranchType {...table.getDefaultFormProps()} />}
  {...props}
/>;

export default TableBranchTypes;
