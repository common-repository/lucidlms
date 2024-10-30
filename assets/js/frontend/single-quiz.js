jQuery(function ($) {

	// lucid_single_lesson_params is required to continue, ensure the object exists
	if (typeof lucid_single_quiz_params === 'undefined')
		return false;

	/*************************************************
	 * Variables
	 ************************************************/
	var timer;
	var htmlTimer = $('#quiz-timer');

	var questionsInAjax = {};

	var questionsLoadingScreen = $('.questions-loading-screen');
	var quizStartScreen = $('.start-quiz-screen');
	var quizCompletedView = $('.quiz-completed');

	var questionsContainer = $('.questions-container');
	var questionsWrap = $('.questions');
	var questions = questionsContainer.find('.questions > li');
	var questionsReviewScreen = $('.review-screen');

	var questionsPagination = $('.questions-pagination');
	var questionsPaginationItems = questionsPagination.find('li');

	var questionsNav = $('.questions-nav');

	var currentQuestion;
	var newQuestionID;

	var answers;

	/*************************************************
	 * Helper functions
	 ************************************************/

	/**
	 * Init timer if it's enabled
	 */
	$.fn.initTimer = function () {

		// Check if timer exists
		if (htmlTimer.length > 0) {

			/**
			 * Initiate and start timer
			 *
			 * @type {Timer}
			 */
			timer = new Timer(htmlTimer, false, function () {

				$.fn.validateAnswer($.fn.completeQuiz);

				// Animate questions view
				$('.questions-loading-screen i').show();
				questionsContainer.fadeOut(600, function () {
					questionsLoadingScreen.fadeIn(600);
				});

			}, 1000);
			timer.start();

			// TODO: add timeSpent saving to DB
		}
	};

	/**
	 * Show question upon request
	 */
	$.fn.showQuestion = function () {

		if (typeof currentQuestion !== 'undefined' && currentQuestion.attr('data-id') === newQuestionID) {
			return;
		}

		var pattern = '[data-id="' + newQuestionID + '"]';
		questions.hide();
		questionsPaginationItems.removeClass('current');

		if (newQuestionID === 'review') {
			questionsNav.hide();

			/**
			 * Get review screen via ajax when finishing quiz
			 * Do not get review screen on first load (because it's already up to date via php)
			 */
			if (typeof currentQuestion !== 'undefined') {
				$.fn.updateReviewScreen();
			}
		} else {
			questionsNav.show();
		}

		currentQuestion = questionsWrap.find(pattern);

		currentQuestion.show();
		questionsPagination.find(pattern).addClass('current');

	};

	/**
	 * Animate questions view and run callback
	 * Used for changing question
	 *
	 * @param callback
	 */
	$.fn.animateQuestionsView = function (callback) {
		questionsContainer.fadeOut(600, function () {
			callback();
			questionsContainer.fadeIn(600);
		});
	};

	/**
	 * Validate answer and init save answer func
	 */
	$.fn.validateAnswer = function (callbackFunc) {

		// Check if answer exist
		answers = $.fn.checkAnswer();

		// Try to save answer
		$.fn.saveAnswer(callbackFunc);
	};
	/**
	 * Check for valid answers and return them
	 *
	 * @returns {*}
	 */
	$.fn.checkAnswer = function () {

		// Check current question type and build a pattern to match answer
		var questionType = currentQuestion.attr('data-type');
		var pattern;
		var key;
		var value;
		switch (questionType) {
			case 'multiple_choice':
				pattern = 'input[type="checkbox"]:checked';
				key = 'value';
				break;
			case 'single_choice':
				pattern = 'input[type="radio"]:checked';
				key = 'value';
				break;
			case 'open':
				pattern = 'textarea';
				key = 'open';
				break;
			default:
				pattern = 'input';
				key = 'open';
				break;
		}

		// Check if any answers exists
		var found = currentQuestion.find(pattern);

        var questionWarningOption = $('<p class="lucid-warning"><i class="fa fa-exclamation"></i>Please, select an option before moving on.</p>');
        var questionWarningOpen = $('<p class="lucid-warning"><i class="fa fa-exclamation"></i>Please leave an answer before moving on.</p>');

        if (!found.length) {
            if ( $('.lucid-warning').length == 0) {
                currentQuestion.parent('.questions').before($(questionWarningOption).fadeIn('slow'));
                return false;
            } else {
                $('.lucid-warning').fadeOut().fadeIn();
                return false;
            }
        }

		// Check if any values exists
		var values = {};
		found.each(function () {
			if ($(this).val() !== "") {
				if (key === 'value') {
					value = $(this).next('.answer').text();
					values[$(this).val()] = value;
				} else if (key === 'open') {
					values[key] = $(this).val();
				}
			} else {
                if ( $('.lucid-warning').length == 0) {
                    currentQuestion.parent('.questions').before($(questionWarningOpen).fadeIn('slow'));
                    return false;
                } else {
                    $('.lucid-warning').fadeOut().fadeIn();
                    return false;
                }
            }
		});

		return Object.keys(values).length ? values : false;
	};

	/**
	 * Update review screen helper function
	 */
	$.fn.updateReviewScreen = function () {

		if (!questionsReviewScreen.find('i').length) {
			questionsReviewScreen.html('<div class="questions-loading-screen"><i class="fa fa-cog fa-spin fa-2x"></i></div>');
		}

		// Hold on for 2 seconds if we're still saving questions via ajax
		if ($.isEmptyObject(questionsInAjax)) {
			$.fn.updateReviewScreenAjax();
		} else {
			setTimeout(function () {
				$.fn.updateReviewScreen()
			}, 2000);
		}

	};

	/*************************************************
	 * Ajax calls
	 ************************************************/

	/**
	 * Save answer to score card via ajax
	 *
	 * @returns {boolean}
	 * @since 1.0.0
	 * access public
	 */
	$.fn.saveAnswer = function (callbackFunc) {

		// Do not save anything on review stage
		if (!questionsPagination.find('.current').next().length) {
			return false;
		}

		var currentQuestionID = currentQuestion.attr('data-id');
		questionsInAjax[currentQuestionID] = 'processing';

		$.post(
			lucid_single_quiz_params.ajax_url,
			{
				action    : 'save_answer',
				security  : lucid_single_quiz_params.save_answer_nonce,
				quizId    : lucid_single_quiz_params.post_id,
				questionId: currentQuestion.attr('data-id'),
				answers   : JSON.stringify(answers)
			},
			function (response) {
				delete questionsInAjax[currentQuestionID];

				// TODO: handle errors
				if (response) {

				}

				if (callbackFunc && (typeof callbackFunc == "function")) {
					callbackFunc();
				}
			}
		);
		return false;
	};

	/**
	 * Update review screen via ajax
	 *
	 * @returns {boolean}
	 * @since 1.0.0
	 * access public
	 */
	$.fn.updateReviewScreenAjax = function () {

		$.post(
			lucid_single_quiz_params.ajax_url,
			{
				action  : 'update_review_screen',
				security: lucid_single_quiz_params.update_review_screen_nonce,
				quizId  : lucid_single_quiz_params.post_id
			},
			function (response) {

				// TODO: handle errors
				if (response) {
					questionsReviewScreen.fadeOut(600, function () {
						questionsReviewScreen.html(response).fadeIn(600);
					});
				}
			}
		);
		return false;
	};

	/**
	 * Complete quiz via ajax
	 *
	 * @returns {boolean}
	 * @since 1.0.0
	 * access public
	 */
	$.fn.completeQuiz = function () {

		if (typeof timer !== 'undefined') {
			timer.stop();
		}

		$.post(
			lucid_single_quiz_params.ajax_url,
			{
				action  : 'complete_quiz',
				security: lucid_single_quiz_params.complete_quiz_nonce,
				quizId  : lucid_single_quiz_params.post_id
			},
			function (response) {

				// TODO: handle errors
				if (response) {
					questionsLoadingScreen.fadeOut(600, function () {
						quizCompletedView.html(response);
						quizCompletedView.show().animate({opacity: 1}, 600);
					});
				}
			}
		);
		return false;
	};

	/*************************************************
	 * DOM listeners
	 ************************************************/

	/**
	 * Listen to show quiz results button
	 */
	$(document).on('click', '.show-quiz-results', function () {
		$(this).fadeOut(600, function () {
			quizCompletedView.show().animate({opacity: 1}, 600);
		});
	});

	/**
	 * Listen to window load to start the quiz
	 */
	$(window).load(function () {

		if (questionsLoadingScreen.length) {
			// Show the active question (the last one that has anchor)
			newQuestionID = $('.questions-pagination li a').last().parent().attr('data-id');
			$.fn.showQuestion();

			// Animate start quiz screen
			$('.questions-loading-screen i').fadeOut(600, function () {
				quizStartScreen.addClass('show').animate({opacity: 1}, 600);
			});
		}

	});

	/**
	 * Listen to start/continue quiz button click event
	 */
	$(document).on('click', '.start-quiz-screen button', function (e) {
		e.preventDefault();

		// Animate questions view
		questionsLoadingScreen.fadeOut(600, function () {
			quizStartScreen.removeClass('show');
			questionsContainer.fadeIn(600, function () {
				$.fn.initTimer();
			});
		});
	});

	/**
	 * Listen to questions pagination
	 */
	$(document).on('click', '.questions-pagination li a', function (e) {
		e.preventDefault();

        // Remove error-message in next step
        if ( $('.lucid-warning').length != 0 ) {
            $('.lucid-warning').fadeOut(500, function () { $('.lucid-warning').remove() } );
        }

		// Assign a new question id value
		newQuestionID = $(this).parent().attr('data-id');

		// Animate questions view
		$.fn.animateQuestionsView(function () {
			$.fn.showQuestion();
		});
	});

	/**
	 * Listen to questions navigation (prev, next)
	 * This should not be available on review stage
	 */
	$(document).on('click', '.questions-nav li button, .questions-nav li a', function (e) {
		e.preventDefault();

		// Fallback if someone is cheating
		if (currentQuestion.attr('data-id') === 'review') {
			return;
		}

		// Check button direction
		var direction = $(this).parent().attr('class');
		var element;
		if (direction == 'next') {
			element = questionsPagination.find('.current').next();
            $.fn.validateAnswer();

			// Create anchor inside pagination for newly accessed questions
			if (!element.find('a').length && answers) {
				var index = element.html();
				element.html('<a href="#">' + index + '</a>');
			}

		} else if (direction == 'prev') {
			element = questionsPagination.find('.current').prev();
		}

		// Check if we're able to change view
		if (element.length && ( answers || direction == 'prev' )) {
			newQuestionID = element.attr('data-id');

            // Remove error-message in next step
            if ( $('.lucid-warning').length != 0 ) {
                $('.lucid-warning').fadeOut(500, function () { $('.lucid-warning').remove() } );
            }

			// Animate questions view
			$.fn.animateQuestionsView(function () {
				$.fn.showQuestion();
			});

		}
	});

	/**
	 * Listen to complete quiz button click event
	 */
	$(document).on('click', '.complete-quiz', function () {

		// Animate questions view
		$('.questions-loading-screen i').show();
		questionsContainer.fadeOut(600, function () {
			$.fn.completeQuiz();
			questionsLoadingScreen.fadeIn(600);
		});

	});

});

