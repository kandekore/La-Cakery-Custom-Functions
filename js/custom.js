jQuery(document).ready(function($) {
  var textField = $('#custom_message');
  var sizeLimits = {
    '6"': 20,
    '8"': 30,
    '10"': 40,
    '12"': 50,
    '14"': 50,
    '16"': 50,
    '18x30"': 50
  };
  var defaultLimit = 50;

  // Function to show/hide the text field based on the selected variation
  function toggleCustomMessageField() {
    var selectedOption = $('#size option:selected');
    var variation = selectedOption.text().trim();

    if (variation && sizeLimits.hasOwnProperty(variation)) {
      var limit = sizeLimits[variation];
      textField.parent().show();
      textField.attr('maxlength', limit);
    } else {
      textField.parent().hide();
    }
  }

  // Show/hide the text field on page load
  toggleCustomMessageField();

  // Listen for changes in the variation select
  $('#size').on('change', function() {
    toggleCustomMessageField();
  });

  // Prevent typing if no option is selected
 textField.on('keypress', function(e) {
    var selectedOption = $('#size option:selected');
    var variation = selectedOption.text().trim();
    var inputText = textField.val();
    var inputLength = inputText.length;
    
    if (!variation || inputLength >= sizeLimits[variation]) {
      e.preventDefault();
      return false;
    }
  });

  // Display notice when maximum limit is reached
  textField.on('input', function() {
    var selectedOption = $('#size option:selected');
    var variation = selectedOption.text().trim();
    var inputText = textField.val();
    var inputLength = inputText.length;
    
    if (variation && inputLength >= sizeLimits[variation]) {
      $('.notice').text('Maximum character limit reached').show();
    } else {
      $('.notice').hide();
    }
  });

  // Set the default character limit
  textField.attr('maxlength', defaultLimit);
});
