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
