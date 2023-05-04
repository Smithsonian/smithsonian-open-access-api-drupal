(function ($, Drupal) {
  Drupal.behaviors.clearSearchResults = {
    attach: function (context, settings) {
      // Disable the submit button initially
      $('input[name="op"]').prop('disabled', true);

      // Clear search results when user input values change.
      $('select[name="endpoint"], select[name="category"], input[name="term"], input[name="query"]', context).once('clearSearchResults').on('input', function () {
        var submitButton = $('input[name="op"]');
        var searchResults = $('#search-results');

        // Disable the submit button
        submitButton.prop('disabled', true);

        // Clear the search results
        searchResults.empty();

        // Enable the submit button once clearing is complete
        searchResults.promise().done(function () {
          submitButton.prop('disabled', false);
        });
      });
    }
  };
})(jQuery, Drupal);
