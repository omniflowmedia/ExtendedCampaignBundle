Mautic.updateTriggerIntervalUnitOptions = function () {
  const intervalType = document.getElementById('campaignevent_triggerIntervalType').value;
  const triggerStatus = mQuery('#campaignevent_triggerIntervalStatus').val();
  let intervalValue = parseInt(document.getElementById('campaignevent_triggerInterval').value, 10);
  if(isNaN(intervalValue))intervalValue = 0;
  const intervalUnits = {
    i: {
      limits: {
        h: { min: 0, max: 59 },
        d: { min: 0, max: 24*60 },
        m: { min: 0, max: 31*24*60 },
        y: { min: 0, max: 366*24*60 },
      },
      labels: { h: 'hour(s)', d: 'day(s)', m: 'month(s)', y: 'year(s)' },
    },
    h: {
      limits: {
        d: { min: 0, max: 23 },
        m: { min: 0, max: 31*24 },
        y: { min: 0, max: 366*24 },
      },
      labels: { d: 'day(s)', m: 'month(s)', y: 'year(s)' },
    },
    d: {
      limits: {
        m: { min: 0, max: 31 },
        y: { min: 0, max: 366 },
      },
      labels: { m: 'month(s)', y: 'year(s)' },
    },
    w: {
      limits: {
        m: { min: 0, max: 5 },
        y: { min: 0, max: 53 },
      },
      labels: { m: 'month(s)', y: 'year(s)' },
    },
    m: {
      limits: {
        y: { min: 1, max: 12 },
      },
      labels: { y: 'year(s)' },
    },
  };

  
  const intervalUnitSelect = document.getElementById('campaignevent_triggerIntervalUnit');
  intervalUnitSelect.innerHTML = '';
  
  let availableLabels;
  if (triggerStatus === 'wait_until') {
    units = intervalUnits[intervalType] || {};
    availableLabels = getAvailableLabels(intervalValue, intervalType);
  } else {
    availableLabels = {
      'i': 'minute(s)',
      'h': 'hour(s)',
      'd': 'day(s)',
      'm': 'month(s)',
      'y': 'year(s)'
    };
  }
  function getAvailableLabels(intervalValue, intervalType) {
    const currentUnit = intervalUnits[intervalType];
  
    if (!currentUnit) {
      return [];
    }
  
    const availableLabels = {};
  
    for (const [unit, limit] of Object.entries(currentUnit.limits)) {
      if (intervalValue >= limit.min && intervalValue <= limit.max) {
        availableLabels[unit] = currentUnit.labels[unit];
      }
    }
    return availableLabels;
  }

  for(const units in availableLabels){
    const option = document.createElement('option');
    option.value = units;
    option.textContent = availableLabels[units];
    intervalUnitSelect.appendChild(option);
  };
  
  Mautic.destroyChosen(mQuery("#campaignevent_triggerIntervalUnit"));
  Mautic.activateChosenSelect(mQuery("#campaignevent_triggerIntervalUnit"));
  Mautic.campaignEventShowHideIntervalSettings();
}



Mautic.updateTriggerIntervalOptions = function () {
  const status = document.getElementById('campaignevent_triggerIntervalStatus').value;
  if(status == 'wait'){
    mQuery('#label-container').addClass('hide');
    mQuery('#triggerIntervalType-container').addClass('hide');
    mQuery('#triggerInterval-container').removeClass('col-sm-3').addClass('col-sm-4'); // Change triggerInterval to col-sm-6
    mQuery('#triggerIntervalUnit-container').removeClass('col-sm-4').addClass('col-sm-8'); // C
  }else{
      mQuery('#label-container').removeClass('hide');
      mQuery('#triggerIntervalType-container').removeClass('hide');
      mQuery('#triggerInterval-container').removeClass('col-sm-4').addClass('col-sm-3'); // Change triggerInterval to col-sm-6
      mQuery('#triggerIntervalUnit-container').removeClass('col-sm-8').addClass('col-sm-4'); // C
  }

  Mautic.updateTriggerIntervalUnitOptions();
} 
