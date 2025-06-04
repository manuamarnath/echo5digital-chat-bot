/**
 * Admin JavaScript for Echo5 AI Chatbot.
 *
 * Initializes the WordPress color picker for designated input fields on the
 * plugin's appearance settings page.
 *
 * @package Echo5_AI_Chatbot
 * @since   0.1.0
 */
(function( $ ) {
	'use strict'; // Enforce stricter parsing and error handling.

	$(function() {
		// Add Color Picker to all inputs that have the 'echo5-color-picker' class.
		// This class is added to the color input fields in settings-page.php.
		$( '.echo5-color-picker' ).wpColorPicker();
	});

})( jQuery );
