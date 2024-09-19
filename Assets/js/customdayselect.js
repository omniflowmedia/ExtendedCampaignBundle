Mautic.customDaySelect = function() {
  if (mQuery('#campaignevent_triggerMode_2').length) {
    var immediateChecked = mQuery('#campaignevent_triggerMode_0').prop('checked');
    var intervalChecked = mQuery('#campaignevent_triggerMode_1').prop('checked');
    var dateChecked = mQuery('#campaignevent_triggerMode_2').prop('checked');
  } else {
    var immediateChecked = false;
    var intervalChecked = mQuery('#campaignevent_triggerMode_0').prop('checked');
    var dateChecked = mQuery('#campaignevent_triggerMode_1').prop('checked');
  }

  if (mQuery('#campaignevent_triggerInterval').length) {
    if (immediateChecked) {
      mQuery('#triggerInterval').addClass('hide');
      mQuery('#triggerDate').addClass('hide');
    } else if (intervalChecked) {
      mQuery('#triggerInterval').removeClass('hide');
      mQuery('#triggerDate').addClass('hide');
    } else if (dateChecked) {
      mQuery('#triggerInterval').addClass('hide');
      mQuery('#triggerDate').removeClass('hide');
    }
  }
};




Mautic.updateTriggerIntervalUnitOptions = function() {
      // Get the selected value of triggerIntervalType
      const intervalType = document.getElementById('campaignevent_triggerIntervalType').value;
      console.log(intervalType);
      const intervalUnits = {
        'n': {'i': 'minutes', 'h': 'hours', 'd': 'days', 'w': 'weeks', 'm': 'months'}, 
        'i': {'h': 'hours', 'd': 'days', 'w': 'weeks', 'm': 'months'},     
        'h': {'d': 'days', 'w': 'weeks', 'm': 'months'},     
        'd': {'m': 'months', 'y': 'years'},    
        'w': {'m': 'months', 'y': 'years'},
        'm': {'y': 'years'}      
    };
  
      // Get the triggerIntervalUnit select element
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
}
