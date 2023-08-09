jQuery(document).ready(function ($) {
  var textField = $("#custom_message");
  var sizeLimits = {
    '6"': 20,
    '8"': 30,
    '10"': 40,
    '12"': 50,
    '14"': 50,
    '16"': 50,
    '18x30"': 50
  };
  var defaultLimit = 30;

  // Function to show/hide the text field based on the selected variation
  function toggleCustomMessageField() {
    var selectedOption = $("#pa_size option:selected");
    var variation = selectedOption.text().trim();

    if (variation && sizeLimits.hasOwnProperty(variation)) {
      var limit = sizeLimits[variation];
      textField.attr("maxlength", limit);
    } else {
      textField.attr("maxlength", defaultLimit);
    }
  }

  // Show/hide the text field on page load
  toggleCustomMessageField();

  // Listen for changes in the variation select
  $("#pa_size").on("change", function () {
    toggleCustomMessageField();
  });

  $(".variations_form").on("woocommerce_variation_select_change", function () {
    toggleCustomMessageField();
  });

  // Prevent typing if no option is selected
  textField.on("keypress", function (e) {
    var selectedOption = $("#pa_size option:selected");
    var variation = selectedOption.text().trim();
    var inputText = textField.val();
    var inputLength = inputText.length;

    if (inputLength >= (sizeLimits[variation] || defaultLimit)) {
      e.preventDefault();
      return false;
    }
  });

  // Display notice when maximum limit is reached
  textField.on("input", function () {
    var selectedOption = $("#pa_size option:selected");
    var variation = selectedOption.text().trim();
    var inputText = textField.val();
    var inputLength = inputText.length;

    if (variation && inputLength >= sizeLimits[variation]) {
      $(".notice").text("Maximum character limit reached").show();
    } else {
      $(".notice").hide();
    }
  });

  // Set the default character limit
  textField.attr("maxlength", defaultLimit);
});
