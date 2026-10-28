import Table from '@hubleto/react-ui/components/fc/Table'
import { type TableMeta, type TableProps } from '@hubleto/react-ui/components/fc/TableInterfaces'
import FormJobHasHazard from './FormJobHasHazard'

interface TableJobHasHazardsProps extends TableProps {
  idJob?: number,
}

const componentName = 'TableJobHasHazards'
const parentApp = 'Hubleto/App/External/WaiBlue/HlthcrBranches'

const TableJobHasHazards = (props: TableJobHasHazardsProps) => {
  const idJob = props.idJob ?? 0;
  return <Table
    componentName={componentName}
    parentApp={parentApp}
    model={parentApp + '/Models/JobHasHazard'}
    baseUrlSlug='hlthcr-branches/job-has-hazards'
    endpointParams={{idJob: idJob}}
    formDefaultValues={idJob > 0 ? {id_job: idJob} : {}}
    formModalProps={{type: 'right wide'}}
    renderForm={(table: TableMeta) => <FormJobHasHazard {...table.getDefaultFormProps()} />}
    {...props}
  />;
}

export default TableJobHasHazards;
