import Table from '@hubleto/react-ui/components/fc/Table'
import { type TableMeta, type TableProps } from '@hubleto/react-ui/components/fc/TableInterfaces'
import FormJob from './FormJob'

interface TableJobsProps extends TableProps {
  idBranch?: number,
}

const componentName = 'TableJobs'
const parentApp = 'Hubleto/App/External/WaiBlue/HlthcrBranches'

// A job without its own exam interval is stored as 0; the list shows an empty cell instead.
function renderJobCell(table: TableMeta, columnName: string, column: any, data: any, options: any) {
  const hasNoOwnInterval = columnName == 'exam_interval_months' && Number(data[columnName] ?? 0) == 0;
  if (hasNoOwnInterval && !options.renderEditor) return <></>;
  return table.renderDefaultCell(columnName, column, data, options);
}

const TableJobs = (props: TableJobsProps) => {
  const idBranch = props.idBranch ?? 0;
  return <Table
    componentName={componentName}
    parentApp={parentApp}
    model={parentApp + '/Models/Job'}
    baseUrlSlug='hlthcr-branches/jobs'
    endpointParams={{idBranch: idBranch}}
    formDefaultValues={idBranch > 0 ? {id_branch: idBranch} : {}}
    formModalProps={{type: 'right wide'}}
    renderForm={(table: TableMeta) => <FormJob {...table.getDefaultFormProps()} />}
    renderCell={renderJobCell}
    {...props}
  />;
}

export default TableJobs;
