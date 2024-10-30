jQuery(function ($) {

	// lucid_single_lesson_params is required to continue, ensure the object exists
	if (typeof lucid_single_lesson_params === 'undefined')
		return false;

	// Declaring variables
	var t,
		htmlTimer = $("#lesson-timer"),
		lessonDurationInSeconds = parseInt($("#lesson-duration").val()),
		saveTimeSpent,
		processingAjax = false;

	/**
	 * Show 'complete' button when timer stopped
	 */
	var activateCompleteButton = function () {
		$('#course-element-complete').slideDown();
	};

	// Check if timer exists
	if (htmlTimer.length > 0) {

		/**
		 * Send AJAX request to save time_spent into score card
		 */
		var saveTimeSpentFunc = function () {

			// Get time to go
			var time = htmlTimer.html();
			var ss = time.split(":");
			var dt = new Date();
			dt.setHours(ss[0]);
			dt.setMinutes(ss[1]);
			dt.setSeconds(ss[2]);

			// Get overall time needed to pass
			var midnight = new Date();
			midnight.setHours(0);
			midnight.setMinutes(0);
			midnight.setSeconds(0);

			// Find out current time_spent value and check if we're ready to complete
			var lessonDurationDate = new Date(midnight.valueOf() + (lessonDurationInSeconds * 1000)),
				timeSpent = Math.floor((lessonDurationDate - dt) / 1000);

			// Prepare data for AJAX request
			var data = {
				action      : 'save_time_spent_lesson',
				security    : lucid_single_lesson_params.save_time_spent_lesson_nonce,
				lessonID    : lucid_single_lesson_params.post_id,
				timeSpent   : timeSpent
			};

			if (!processingAjax) {
				processingAjax = true;
				$.ajax({
					type   : 'POST',
					url    : lucid_single_lesson_params.ajax_url,
					data   : data,
					success: function (success) {
						processingAjax = false;

						// TODO: handle errors
						if (success) {

						}
					}
				});
			}

		};

		/**
		 * Initiate saveTimeSpentFunc() every 15 seconds until timer stops
		 */
		var initSaveTimeSpentFunc = function () {
			if (t.timerState !== 'standby') {
				saveTimeSpent = setTimeout(function () {
					saveTimeSpentFunc();
					initSaveTimeSpentFunc();
				}, 15000);
			}
		};

		/**
		 * Initiate and start timer
		 *
		 * @type {Timer}
		 */
		t = new Timer(htmlTimer, false, activateCompleteButton, 1000);
		t.start();

		// Save time_spent into scorecard via ajax
		initSaveTimeSpentFunc();
	}

});