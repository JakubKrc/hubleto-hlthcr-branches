import Translator from '@hubleto/react-ui/core/Translator';

interface CategorisationTabProps {
  idBranch: number,
}

const parentApp = 'Hubleto/App/External/WaiBlue/HlthcrBranches';
const T = new Translator(parentApp + '/Loader', 'Components/CategorisationTab');

/**
 * Shows the printable categorisation of work (job positions x hazards) of a branch.
 */
const CategorisationTab = (props: CategorisationTabProps) => {
  const categorisationUrl = globalThis.hubleto.config.projectUrl + '/hlthcr-branches/branches/' + props.idBranch + '/categorisation';

  return <div className='flex flex-col gap-2 h-full'>
    <div>
      <a href={categorisationUrl} target='_blank' className='btn btn-transparent'>
        <span className='icon'><i className='fas fa-print'></i></span>
        <span className='text'>{T.translate('Open for printing')}</span>
      </a>
    </div>
    <iframe src={categorisationUrl} className='w-full border rounded' style={{minHeight: '70vh'}}></iframe>
  </div>;
}

export default CategorisationTab;
