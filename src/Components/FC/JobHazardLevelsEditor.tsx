import React from 'react';
import Translator from '@hubleto/react-ui/core/Translator';
import request from '@hubleto/react-ui/core/Request';

interface JobHazardLevelsEditorProps {
  idJob: number,
}

interface HazardRow {
  id_hazard: number,
  name: string,
  level: number,
}

interface HazardTypeGroup {
  id: number,
  name: string,
  uses_categories: boolean,
  hazards: Array<HazardRow>,
}

const parentApp = 'Hubleto/App/External/WaiBlue/HlthcrBranches';
const T = new Translator(parentApp + '/Loader', 'Components/JobHazardLevelsEditor');

// Same values as JobHasHazard::LEVEL_* in PHP.
const LEVEL_NOT_SET = 0;
const LEVEL_YES = 1;
const CATEGORY_LEVELS = [LEVEL_NOT_SET, 1, 2, 3, 4];

/**
 * Edits the level of every branch hazard for one job position, like the legacy job form did.
 * Category hazard types use a 0-4 select, yes/no types use a checkbox (level 1 = yes).
 */
const JobHazardLevelsEditor = (props: JobHazardLevelsEditorProps) => {
  const [hazardTypes, setHazardTypes] = React.useState<Array<HazardTypeGroup>>([]);
  const [levels, setLevels] = React.useState<Record<number, number>>({});
  const [isLoaded, setIsLoaded] = React.useState(false);
  const [mayChange, setMayChange] = React.useState(false);
  const [saveMessage, setSaveMessage] = React.useState('');

  function applyLoadedLevels(response: any) {
    const loadedHazardTypes: Array<HazardTypeGroup> = response.hazardTypes ?? [];
    const loadedLevels: Record<number, number> = {};
    for (const hazardType of loadedHazardTypes) {
      for (const hazard of hazardType.hazards) {
        loadedLevels[hazard.id_hazard] = hazard.level;
      }
    }
    setHazardTypes(loadedHazardTypes);
    setLevels(loadedLevels);
    setMayChange(response.mayChange === true);
    setIsLoaded(true);
  }

  function loadLevels() {
    request.get('hlthcr-branches/api/get-job-hazard-levels', {idJob: props.idJob}, applyLoadedLevels);
  }

  React.useEffect(loadLevels, [props.idJob]);

  function changeLevel(idHazard: number, level: number) {
    setLevels({...levels, [idHazard]: level});
    setSaveMessage('');
  }

  function showSaveResult(response: any) {
    if (response.status == 'success') {
      setSaveMessage(T.translate('Hazard levels saved.'));
    } else {
      setSaveMessage(T.translate('Saving failed.') + ' ' + (response.message ?? ''));
    }
  }

  function saveLevels() {
    const requestParams = {idJob: props.idJob, levels: levels};
    request.post('hlthcr-branches/api/save-job-hazard-levels', requestParams, {}, showSaveResult);
  }

  function renderLevelInput(hazardType: HazardTypeGroup, hazard: HazardRow) {
    const currentLevel = levels[hazard.id_hazard] ?? LEVEL_NOT_SET;

    if (!hazardType.uses_categories) {
      return <input
        type='checkbox'
        checked={currentLevel !== LEVEL_NOT_SET}
        disabled={!mayChange}
        onChange={(event) => changeLevel(hazard.id_hazard, event.target.checked ? LEVEL_YES : LEVEL_NOT_SET)}
      />;
    }

    return <select
      value={currentLevel}
      disabled={!mayChange}
      onChange={(event) => changeLevel(hazard.id_hazard, parseInt(event.target.value))}
    >
      {CATEGORY_LEVELS.map((level) => <option key={level} value={level}>
        {level == LEVEL_NOT_SET ? T.translate('Category not set') : T.translate('Category') + ' ' + level}
      </option>)}
    </select>;
  }

  if (!isLoaded) return <div>{T.translate('Loading...')}</div>;

  return <div className='flex flex-col gap-4'>
    {hazardTypes.map((hazardType) => <div key={hazardType.id} className='card'>
      <div className='card-header'>{hazardType.name}</div>
      <div className='card-body'>
        <table className='table-default w-full'>
          <tbody>
            {hazardType.hazards.map((hazard) => <tr key={hazard.id_hazard}>
              <td className='py-1'>{hazard.name}</td>
              <td className='py-1 w-64'>{renderLevelInput(hazardType, hazard)}</td>
            </tr>)}
          </tbody>
        </table>
      </div>
    </div>)}
    {mayChange ? <div className='flex gap-2 items-center'>
      <button className='btn btn-primary' onClick={saveLevels}>
        <span className='icon'><i className='fas fa-save'></i></span>
        <span className='text'>{T.translate('Save hazard levels')}</span>
      </button>
      {saveMessage !== '' ? <span>{saveMessage}</span> : null}
    </div> : null}
  </div>;
}

export default JobHazardLevelsEditor;
