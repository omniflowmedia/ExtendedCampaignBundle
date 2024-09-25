Mautic.updateTriggerIntervalUnitOptions = function () {
  // Get the selected value of triggerIntervalType
  const intervalType = document.getElementById('campaignevent_triggerIntervalType').value;

  const intervalUnits = {
    'na': { 'i': 'minute(s)', 'h': 'hour(s)', 'd': 'day(s)', 'm': 'month(s)', 'y': 'year(s)' },
    'i': { 'h': 'hour(s)', 'd': 'day(s)', 'm': 'month(s)', 'y': 'year(s)' },
    'h': { 'd': 'day(s)', 'm': 'month(s)', 'y': 'year(s)' },
    'd': { 'm': 'month(s)', 'y': 'year(s)' },
    'w': { 'm': 'month(s)', 'y': 'year(s)' },
    'm': { 'y': 'year(s)' }
  };
  const intervalUnitSelect = document.getElementById('campaignevent_triggerIntervalUnit');
  intervalUnitSelect.innerHTML = '';
  const units = intervalUnits[intervalType] || {};
  for (const unitValue in units) {
    const option = document.createElement('option');
    option.value = unitValue;
    option.textContent = units[unitValue];
    intervalUnitSelect.appendChild(option);
  }
  Mautic.destroyChosen(mQuery("#campaignevent_triggerIntervalUnit"));
  Mautic.activateChosenSelect(mQuery("#campaignevent_triggerIntervalUnit"));
  Mautic.campaignEventShowHideIntervalSettings();

}
Mautic.updateTriggerIntervalOptions = function () {
  const status = document.getElementById('campaignevent_triggerIntervalStatus').value;
  console.log("clicked",status)
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
  // Mautic.destroyChosen(mQuery("#campaignevent_triggerIntervalType"));
  // Mautic.destroyChosen(mQuery("#campaignevent_triggerIntervalUnit"));
  // Mautic.activateChosenSelect(mQuery("#campaignevent_triggerIntervalUnit"));
  // Mautic.campaignEventShowHideIntervalSettings();
} 
