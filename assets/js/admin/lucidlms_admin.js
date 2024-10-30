/**
 * LucidLMS Admin JS
 *
 * @since 1.0.0
 */
jQuery(document).ready(function ($) {

  // Global variables
  var post_type = $('#post_type').val(),
      is_dashboard = $('#is_dashboard').length ? true : false,
      is_question_pool = $('#is_question_pool').length ? true : false,
      modalQuestionType,
      answers_list,
      post_type_taxonomy;

  $.fn._GET = function (param) {
    var vars = {};
    window.location.href.replace( location.hash, '' ).replace(/[?&]+([^=&]+)=?([^&]*)?/gi,
      function( m, key, value ) {
        vars[key] = value !== undefined ? value : '';
      }
    );

    if ( param ) {
      return vars[param] ? vars[param] : false;
    }
    return vars;
  };

  $.fn.uniqid = function (prefix, more_entropy) {

    if (typeof prefix === 'undefined') {
      prefix = '';
    }

    var retId;
    var formatSeed = function (seed, reqWidth) {
      seed = parseInt(seed, 10)
          .toString(16); // to hex str
      if (reqWidth < seed.length) { // so long we split
        return seed.slice(seed.length - reqWidth);
      }
      if (reqWidth > seed.length) { // so short we pad
        return Array(1 + (reqWidth - seed.length))
                .join('0') + seed;
      }
      return seed;
    };

    // BEGIN REDUNDANT
    if (!this.php_js) {
      this.php_js = {};
    }
    // END REDUNDANT
    if (!this.php_js.uniqidSeed) { // init seed with big random int
      this.php_js.uniqidSeed = Math.floor(Math.random() * 0x75bcd15);
    }
    this.php_js.uniqidSeed++;

    retId = prefix; // start with prefix, add current milliseconds hex string
    retId += formatSeed(parseInt(new Date()
            .getTime() / 1000, 10), 8);
    retId += formatSeed(this.php_js.uniqidSeed, 5); // add seed hex string
    if (more_entropy) {
      // for more entropy we add a float lower to 10
      retId += (Math.random() * 10)
          .toFixed(8)
          .toString();
    }

    return retId;
  };


  /**
   * Initialize chosen
   */
  $.fn.initChosen = function () {

    if ($('.post-type-score_card').length > 0) {

      var dateFilterDropdown = $('#filter-by-date');
      var courseTypeDropdown = $('#dropdown_course_type');
      var courseDropdown = $('#dropdown_course_id');
      var studentDropdown = $('#dropdown_student_id');
      var statusDropdown = $('#dropdown_sc_status');

      if (dateFilterDropdown.length > 0) {
        dateFilterDropdown.chosen({
          allow_single_deselect: true
        });
      }

      if (courseTypeDropdown.length > 0) {
        courseTypeDropdown.chosen({
          allow_single_deselect: true
        });
      }

      if (courseDropdown.length > 0) {
        courseDropdown.chosen({
          allow_single_deselect: true
        });
      }

      if (studentDropdown.length > 0) {
        studentDropdown.chosen({
          allow_single_deselect: true
        });
      }

      if (statusDropdown.length > 0) {
        statusDropdown.chosen({
          allow_single_deselect: true
        });
      }
    }
  };

  /**
   * Check and trim if id attr had prefix.
   *
   * @param id
   * @returns {*}
   */
  $.fn.checkIdPrefix = function (id) {

    if (id.substring(0, 8) == "element-") {
      id = id.substring(8);
    } else if (id.substring(0, 9) == "question-") {
      id = id.substring(9);
    }

    return id;
  };

  /**
   * Init function
   */
  $.fn.initLucidLMSAdmin = function () {
    /**
     * Init bootstrap tooltips
     */
    $(".tips, .help_tip").tooltip();

    /**
     * Bootstrap datetimepicker
     * Check if value is timestamp, if true - convert to formatted date and pass to datetimepicker.
     */
    var dt = $(".input-group.date").datetimepicker();
    dt.each(function (index) {
      var value = $(this).find('input').val();
      var valid = (moment(value * 1000)).isValid();
      if (valid) {
        $(this).data("DateTimePicker").setDate(moment(value * 1000).zone(lucidlms_admin.wp_timezone));
      }
    });

    /**
     * Check, what is the current course type
     *
     * @type {*|jQuery}
     */
    $('input[name="course_type"]').change(function () {
      $.fn.changeCourseType(this.value, $(this));
    });

    /**
     * Check, what is the current course element type
     *
     * @type {*|jQuery}
     */
    $('input[name="course_element_type"]').change(function () {
      $.fn.changeCourseElementType(this.value);
    });

    var elements = $('.course-elements');
    if (elements.length) {
      /**
       * Initiate jQuery UI sortable on course elements in the backend
       */
      elements.sortable({
        placeholder: "sortable-placeholder",
        update     : function (event, ui) {
          var sorted = $(this).sortable("serialize");

          $.fn.reorderElements(sorted, $(this));
        }
      });
    }

    var questions = $('.questions');
    if (questions.length && !questions.hasClass('no-sortable-ui')) {
      /**
       * Initiate jQuery UI sortable on questions in the backend
       */
      questions.sortable({
        placeholder: "sortable-placeholder",
        update     : function (event, ui) {
          var sorted = $(this).sortable("serialize");

          $.fn.reorderQuestions(sorted);
        }
      });
    }

    /**
     * Score card: get available (for score card) courses via ajax on first load
     */
    var student_field = $('body.post-new-php.post-type-score_card select[name="_student_id"]');
    if (student_field.length) {
      $.fn.replaceCoursesField(student_field.val());
    }

    /**
     * Initialize expand.js on dashboard
     */
    $('.collapsible-course .expand:not(:has(a))').toggler();

  };

    $.fn.getSelectedFilterData = function() {
        var $filters = $('.filters');
        var $filterByCategory = $filters.find('.categories-filter ul.categories-list li.selected');
        var $filterByCourse = $filters.find('.courses-filter ul.courses-list li.selected');


        var selectedCategories = [];
        $filterByCategory.each(function(){
            var id = $(this).data('id')
            if( id ) selectedCategories.push( id )
        });

        var selectedCourses = [];
        $filterByCourse.each(function(){
            var id = $(this).data('id')
            if( id ) selectedCourses.push( id )
        });

        return {
            categories: selectedCategories,
            courses: selectedCourses
        }
    }

    $.fn.loadFilteredQuestions = function() {
        var $questionsWrapper = $('.questions');

        var selected = $.fn.getSelectedFilterData();

        $.fn.showOverlay($questionsWrapper);
        $.post(
            lucidlms_admin.ajax_url,
            {
                action:   'filter_questions',
                filter_questions_nonce:   lucidlms_admin.filter_questions_nonce,
                selected_categories:        selected.categories,
                selected_courses:           selected.courses,
                is_question_pool:           is_question_pool
            },
            function (response) {
                if (response) {

                    $questionsWrapper.html(response);
                    $.fn.hideOverlay($questionsWrapper);

                }
            }
        );
    }

    $(document).on('click', '.lucidlms_page_lucid-question-pool .filters .filter .label', function(){

        var $this = $(this);
        var $filter = $this.closest('.filter');

        // change class of current element
        $this.toggleClass('selected');

        if( $this.hasClass('all') ){
            // selected "All" toggle => deselect all other
            $filter.find('ul > li').each(function(){
                if( !$(this).hasClass('all') ){
                    $(this).removeClass('selected');
                }
            });

        } else {
            // selected any course => deselect "all"
            $filter.find('ul > li.all').removeClass('selected');
        }

        if( $filter.find('ul > li.selected').length == 0 ){
            $filter.find('ul > li.all').addClass('selected');
        }

        $.fn.loadFilteredQuestions();
    });

        /**
   * Init modal helper functions
   */
  $.fn.initModalHelpers = function () {

    /**
     * Initiate bootstrap switch on is_correct checkboxes
     */
    $('.input-switch').bootstrapSwitch();

    $('#_question_text_extended').summernote({
      toolbar: [
        ['progress', ['undo', 'redo']],
        ['style', ['style', 'bold', 'italic', 'underline', 'strikethrough', 'clear']],
        ['para', ['ul', 'ol', 'paragraph']],
        ['insert', ['link', 'picture', 'video']],
        ['misc', ['codeview']]
      ]
    });
  };

  /**
   * Show overlay on an element
   *
   * @param element
   */
  $.fn.showOverlay = function (element) {
    element.addClass('loading');
  };

  /**
   * Remove overlay from an element
   *
   * @param element
   */
  $.fn.hideOverlay = function (element) {
    element.removeClass('loading');
  };

  /**
   *
   * @param title
   * @param content
   * @returns {boolean}
   */
  $.fn.initQuestionModal = function (title, content) {
    title = typeof title !== 'undefined' ? title : '';
    content = typeof content !== 'undefined' ? content : '';

    var tmpl = '<div class="question-modal modal fade" tabindex="-1" role="dialog" aria-labelledby="' + lucidlms_admin.i18n_question + '" aria-hidden="true">' +
        '<div class="modal-dialog">' +
        '<div class="modal-content">' +
        '	<div class="modal-header">' +
        '		<h4 class="modal-title">' + title + '</h4>' +
        '	</div>' +
        '	<div class="modal-body">' + content + '</div>' +
        '	<div class="modal-footer">' +
        '		<button type="button" class="btn btn-default" data-dismiss="modal">' + lucidlms_admin.i18n_close + '</button>' +
        '		<button type="submit" class="btn btn-primary save-question-meta">' + lucidlms_admin.i18n_save + '</button>' +
        '	</div>' +
        '</div><!-- /.modal-content -->' +
        '</div><!-- /.modal-dialog -->' +
        '</div><!-- /.modal -->';

    var modal = $('.question-modal');
    if (modal.length > 0) {
      modal.find('.modal-title').html(title);
      modal.find('.modal-body').html(content);
      modal.modal({
        keyboard: false,
        backdrop: 'static'
      });

      modalQuestionType = modal.find('.lucidlms-options').attr('data-question-type');
      answers_list = modal.find('ul.answers');
      return false;
    }

    $('body').append(tmpl);
    $.fn.initQuestionModal(title, content);
  };

    /**
     *
     * @param title
     * @param content
     * @returns {boolean}
     */
    $.fn.initQuestionPoolModal = function (title, content) {
        title = typeof title !== 'undefined' ? title : '';
        content = typeof content !== 'undefined' ? content : '';

        var tmpl = '<div class="insert-questions-modal modal fade" tabindex="-1" role="dialog" aria-labelledby="' + lucidlms_admin.i18n_question_pool + '" aria-hidden="true">' +
            '<div class="modal-dialog">' +
            '<div class="modal-content">' +
            '	<div class="modal-header">' +
            '		<h4 class="modal-title">' + title + '</h4>' +
            '	</div>' +
            '	<div class="modal-body">' + content + '</div>' +
            '	<div class="modal-footer">' +
            '		<button type="button" class="btn btn-default" data-dismiss="modal">' + lucidlms_admin.i18n_close + '</button>' +
            '		<button type="submit" class="btn btn-primary insert-questions">' + lucidlms_admin.i18n_insert + '</button>' +
            '	</div>' +
            '</div><!-- /.modal-content -->' +
            '</div><!-- /.modal-dialog -->' +
            '</div><!-- /.modal -->';

        var modal = $('.insert-questions-modal');
        if (modal.length > 0) {
            modal.find('.modal-title').html(title);
            modal.find('.modal-body').html(content);
            modal.modal({
                keyboard: false,
                backdrop: 'static'
            });

            return false;
        }

        $('body').append(tmpl);
        $.fn.initQuestionPoolModal(title, content);
    };


    /**
     *
     * @param title
     * @param content
     * @returns {boolean}
     */
    $.fn.initQuestionCategoriesModal = function (title, content) {
        title = typeof title !== 'undefined' ? title : '';
        content = typeof content !== 'undefined' ? content : '';

        var tmpl = '<div class="question-categories-modal modal fade" tabindex="-1" role="dialog" aria-labelledby="' + lucidlms_admin.i18n_manage_question_categories_title + '" aria-hidden="true">' +
            '<div class="modal-dialog">' +
            '<div class="modal-content">' +
            '	<div class="modal-header">' +
            '		<h4 class="modal-title">' + title + '</h4>' +
            '	</div>' +
            '	<div class="modal-body">' + content + '</div>' +
            '	<div class="modal-footer">' +
            '		<button type="button" class="btn btn-default" data-dismiss="modal">' + lucidlms_admin.i18n_close + '</button>' +
            '	</div>' +
            '</div><!-- /.modal-content -->' +
            '</div><!-- /.modal-dialog -->' +
            '</div><!-- /.modal -->';

        var modal = $('.question-categories-modal');
        if (modal.length > 0) {
            modal.find('.modal-title').html(title);
            modal.find('.modal-body').html(content);
            modal.modal({
                keyboard: false,
                backdrop: 'static'
            });

            return false;
        }

        $('body').append(tmpl);
        $.fn.initQuestionCategoriesModal(title, content);
    };

  /**
   * Check which course type radio button is checked and show needed content
   *
   * @param value
   * @param me
   * @since 1.0.0
   * access public
   */
  $.fn.changeCourseType = function (value, me) {

    var course_meta_box = $('#lucidlms-course-meta');
    var wrapper = course_meta_box.length ? course_meta_box : me.closest('.dashboard-course');
    if (!wrapper.length) {
      return false;
    }

    var post_id = lucidlms_admin.post_id !== '' ? lucidlms_admin.post_id : wrapper.find('#course_id').val();

    $.fn.showOverlay(wrapper);

    $.post(
        lucidlms_admin.ajax_url,
        {
          action                  : 'change_course_type',
          change_course_type_nonce: lucidlms_admin.change_course_type_nonce,
          post_id                 : post_id,
          type                    : value,
          is_dashboard            : is_dashboard
        },
        function (response) {
          if (response) {

            wrapper.find('.inside').html(response);

            $.fn.hideOverlay(wrapper);

            $.fn.initLucidLMSAdmin();
          }
        }
    );
    return false;
  };

  /**
   * Check which course element type radio button is checked and show needed content
   *
   * @param value
   * @since 1.0.0
   * access public
   */
  $.fn.changeCourseElementType = function (value) {

    var wrapper = $('#lucidlms-course-element-meta');

    $.fn.showOverlay(wrapper);
      var course_id = $.fn._GET('course_id');

      $.post(
        lucidlms_admin.ajax_url,
        {
          action                          : 'change_course_element_type',
          change_course_element_type_nonce: lucidlms_admin.change_course_element_type_nonce,
          post_id                         : lucidlms_admin.post_id,
          course_id                       : course_id,
          type                            : value
        },
        function (response) {
          if (response) {

            wrapper.find('.inside').html(response);

            // change body class to a correct one
            $('body').removeClass('lesson quiz').addClass( value );

            $.fn.hideOverlay(wrapper);

            $.fn.initLucidLMSAdmin();
          }
        }
    );
    return false;
  };

  /**
   * Create course via ajax (Dashboard only)
   *
   * @param e
   * @returns {boolean}
   * @since 1.0.0
   * access public
   */
  $.fn.createCourse = function(e) {
    e.preventDefault();

    var type = 'course',
        name = $('.new_course_name'),
        nameValue = name.val();

    var wrapper = $('.lucidlms-dashboard');
    if (!wrapper.length) {
      return false;
    }

    if (nameValue === '') {
      alert(lucidlms_admin.i18n_create_course_alert);
      return false;
    }

    $.fn.showOverlay(wrapper);

    name.val('').animate({width: 'toggle'}, 350);

    $('.add-course').toggle();
    $('.confirm-course-title').toggle();

    $.post(
        lucidlms_admin.ajax_url,
        {
          action             : 'create_course',
          create_course_nonce: lucidlms_admin.create_course_nonce,
          type               : type,
          name               : nameValue
        },
        function (response) {
          if (response) {

            wrapper.find('.dashboard-courses').html(response);

            $.fn.hideOverlay(wrapper);

            $.fn.initLucidLMSAdmin();
          }
        }
    );
    return false;
  };

  /**
   * Create element via ajax
   *
   * @param type
   * @param name
   * @param me
   * @returns {boolean}
   * @since 1.0.0
   * access public
   */
  $.fn.createCourseElement = function (type, name, me) {

    var course_meta_box = $('#lucidlms-course-meta');
    var wrapper = course_meta_box.length ? course_meta_box : me.closest('.dashboard-course');
    if (!wrapper.length) {
      return false;
    }

    var post_id = lucidlms_admin.post_id !== '' ? lucidlms_admin.post_id : wrapper.find('#course_id').val();

    $.fn.showOverlay(wrapper);

    $.post(
        lucidlms_admin.ajax_url,
        {
          action                     : 'create_course_element',
          create_course_element_nonce: lucidlms_admin.create_course_element_nonce,
          post_id                    : post_id,
          type                       : type,
          name                       : name
        },
        function (response) {
          if (response) {

            wrapper.find('.course-elements').html(response);

            $.fn.hideOverlay(wrapper);

          }
        }
    );
    return false;
  };

  /**
   * Create question via ajax
   *
   * @param type
   * @param name
   * @returns {boolean}
   * @since 1.0.0
   * access public
   */
  $.fn.createQuestion = function (type, name) {

    var wrapper = $('#lucidlms-course-element-meta');
    var args = {
        action               : 'create_question',
        create_question_nonce: lucidlms_admin.create_question_nonce,
        post_id              : lucidlms_admin.post_id,
        type                 : type,
        name                 : name,
        is_question_pool     : is_question_pool
    };

    if( is_question_pool ){
        var selected = $.fn.getSelectedFilterData();
        args.selected_categories = selected.categories;
        args.selected_courses = selected.courses;
        wrapper = $('.questions-area-wrapper');
    }

    $.fn.showOverlay(wrapper);

    $.post(
        lucidlms_admin.ajax_url,
        args,
        function (response) {
          if( response ){
              wrapper.find('.questions').html(response);
              $.fn.hideOverlay(wrapper);
          }
        }
    );
    return false;
  };

  /**
   * Remove course element via ajax
   *
   * @param id
   * @param me
   * @returns {boolean}
   * @since 1.0.0
   * access public
   */
  $.fn.removeCourseElement = function (id, me) {

    var course_meta_box = $('#lucidlms-course-meta');
    var wrapper = course_meta_box.length ? course_meta_box : me.closest('.dashboard-course');
    if (!wrapper.length) {
      return false;
    }

    var post_id = lucidlms_admin.post_id !== '' ? lucidlms_admin.post_id : wrapper.find('#course_id').val();

    $.fn.showOverlay(wrapper);

    $.post(
        lucidlms_admin.ajax_url,
        {
          action                     : 'remove_course_element',
          remove_course_element_nonce: lucidlms_admin.remove_course_element_nonce,
          post_id                    : post_id,
          course_element_id          : id
        },
        function (response) {

          wrapper.find('.course-elements').html(response);

          $.fn.hideOverlay(wrapper);

        }
    );
    return false;
  };

  /**
   * Remove question via ajax
   *
   * @param id
   * @returns {boolean}
   * @since 1.0.0
   * access public
   */
  $.fn.removeQuestion = function (id) {

    var wrapper = $('#lucidlms-course-element-meta');

    $.fn.showOverlay(wrapper);

    $.post(
        lucidlms_admin.ajax_url,
        {
          action               : 'remove_question',
          remove_question_nonce: lucidlms_admin.remove_question_nonce,
          post_id              : lucidlms_admin.post_id,
          question_id          : id
        },
        function (response) {

          if( !is_question_pool ){
              wrapper.find('.questions').html(response);
              $.fn.hideOverlay(wrapper);
          } else {
              $.fn.loadFilteredQuestions();
          }


        }
    );
    return false;
  };

  /**
   * Open modal with all needed fields for editing question
   *
   * @since 1.0.0
   * access public
   */
  $.fn.editQuestion = function (id, title) {

    var wrapper = $('#lucidlms-course-element-meta');

    $.fn.showOverlay(wrapper);

    $.post(
        lucidlms_admin.ajax_url,
        {
          action             : 'edit_question',
          edit_question_nonce: lucidlms_admin.edit_question_nonce,
          question_id        : id
        },
        function (response) {
          if (response) {

            $.fn.initQuestionModal(title, response);

            $.fn.initModalHelpers();

            $.fn.hideOverlay(wrapper);

          }
        }
    );
    return false;
  };

  /**
   * Replace the old list with the new one from ajax
   *
   * @since 1.0.0
   * access public
   */
  $.fn.recheckCourseElements = function () {

    var course_meta_box = $('#lucidlms-course-meta');
    var wrapper = course_meta_box.length ? course_meta_box : me.closest('.dashboard-course');
    if (!wrapper.length) {
      return false;
    }

    var post_id = lucidlms_admin.post_id !== '' ? lucidlms_admin.post_id : wrapper.find('#course_id').val();

    $.fn.showOverlay(wrapper);

    $.post(
        lucidlms_admin.ajax_url,
        {
          action                   : 'get_course_elements',
          get_course_elements_nonce: lucidlms_admin.get_course_elements_nonce,
          post_id                  : post_id
        },
        function (response) {
          if (response) {

            wrapper.find('.course-elements').html(response);

            $.fn.hideOverlay(wrapper);
          }
        }
    );
    return false;
  };

  /**
   * Replace the old list with the new one from ajax
   *
   * @since 1.0.0
   * access public
   */
  $.fn.recheckQuestions = function () {

    var wrapper = $('#lucidlms-course-element-meta');

    $.fn.showOverlay(wrapper);

    $.post(
        lucidlms_admin.ajax_url,
        {
          action             : 'get_questions',
          get_questions_nonce: lucidlms_admin.get_questions_nonce,
          post_id            : lucidlms_admin.post_id
        },
        function (response) {
          if (response) {

            wrapper.find('.questions').html(response);

            $.fn.hideOverlay(wrapper);
          }
        }
    );
    return false;
  };

  /**
   * Reorder elements via ajax
   *
   * @param sorted_data
   * @param me
   * @returns {boolean}
   */
  $.fn.reorderElements = function (sorted_data, me) {

    var course_meta_box = $('#lucidlms-course-meta');
    var wrapper = course_meta_box.length ? course_meta_box : me.closest('.dashboard-course');
    if (!wrapper.length) {
      return false;
    }

    var post_id = lucidlms_admin.post_id !== '' ? lucidlms_admin.post_id : wrapper.find('#course_id').val();

    $.fn.showOverlay(wrapper);

    $.post(
        lucidlms_admin.ajax_url,
        {
          action                       : 'reorder_course_elements',
          reorder_course_elements_nonce: lucidlms_admin.reorder_course_elements_nonce,
          post_id                      : post_id,
          sorted_data                  : sorted_data
        },
        function (response) {
          $.fn.hideOverlay(wrapper);
        }
    );
    return false;
  };

  /**
   * Reorder questions via ajax
   *
   * @param sorted_data
   * @returns {boolean}
   */
  $.fn.reorderQuestions = function (sorted_data) {
    var wrapper = $('#lucidlms-course-element-meta');

    $.fn.showOverlay(wrapper);

    $.post(
        lucidlms_admin.ajax_url,
        {
          action                 : 'reorder_questions',
          reorder_questions_nonce: lucidlms_admin.reorder_questions_nonce,
          post_id                : lucidlms_admin.post_id,
          sorted_data            : sorted_data
        },
        function (response) {
          $.fn.hideOverlay(wrapper);
        }
    );
    return false;
  };

  /**
   * Replace courses available to choose from via ajax when creating a score card
   *
   * @param student_id
   * @returns {boolean}
   */
  $.fn.replaceCoursesField = function (student_id) {
    var wrapper = $('#lucidlms-score-card-meta');

    $.fn.showOverlay(wrapper);

    $.post(
        lucidlms_admin.ajax_url,
        {
          action                     : 'get_available_courses',
          get_available_courses_nonce: lucidlms_admin.get_available_courses_nonce,
          student_id                 : student_id
        },
        function (response) {

          if (response) {
            $('body.post-new-php.post-type-score_card select[name="_course_id"]').html(response);
          }
          $.fn.hideOverlay(wrapper);
        }
    );
    return false;
  };

  /**
   * Create course element or create question for course or course element edit page
   */
  $.fn.create_element = function (e, me) {
    if (typeof me === 'undefined') me = $(this);

    var inputsParent = me.closest('.input-group');

    var type = inputsParent.prev('.new_element_field').find('#new_element'),
        name = inputsParent.find('.new_element_name');

    var currentCourseWrapper = me.closest('.dashboard-course');
    post_type_taxonomy = currentCourseWrapper.length ? currentCourseWrapper.find('#course_type').attr('name') : $('input[type="hidden"].taxonomy').attr('name');
    post_type = currentCourseWrapper.length ? currentCourseWrapper.find('#course_type').val() : post_type;

    if (type[0].selectedIndex > 0 && name.val() !== '') {
      if (post_type == 'course' && post_type_taxonomy == 'course_type') {
        $.fn.createCourseElement(type.val(), name.val(), me);
      } else if ( (post_type == 'course_element' && post_type_taxonomy == 'course_element_type') || is_question_pool) {
        $.fn.createQuestion(type.val(), name.val());
      }
      // reset inputs
      type.val(0);
      name.val('');
    } else {
      alert(lucidlms_admin.i18n_create_element_alert)
    }
  };

  /**
   * Listen for 'create course element' or 'create question' button
   */
  $(document).on('click', '.create-element', $.fn.create_element);
  $(document).on('keypress', '.new_element_name', function (e) {
    if (e.which == 13) {
      $.fn.create_element(e, $(this));
      return false;    //<---- Add this line
    }
  });

  /**
   * Remove course element or question
   */
  $(document).on('click', '.remove-element', function (e) {
    var panel = $(this).closest('.panel'),
        id = $.fn.checkIdPrefix(panel.attr('id')),
        currentCourseWrapper = $(this).closest('.dashboard-course');

    post_type_taxonomy = currentCourseWrapper.length ? currentCourseWrapper.find('#course_type').attr('name') : $('input[type="hidden"].taxonomy').attr('name');
    post_type = currentCourseWrapper.length ? currentCourseWrapper.find('#course_type').val() : post_type;

    if (post_type == 'course' && post_type_taxonomy == 'course_type') {
      $.fn.removeCourseElement(id, $(this));
    } else if ( (post_type == 'course_element' && post_type_taxonomy == 'course_element_type') || is_question_pool ) {
      $.fn.removeQuestion(id);
    }

  });

  /**
   * Open editing in a new tab on clicking 'edit course element' or 'edit question' button
   */
  $(document).on('click', '.edit-element', function (e) {
    var panel = $(this).closest('.panel'),
        id = $.fn.checkIdPrefix(panel.attr('id')),
        currentCourseWrapper = $(this).closest('.dashboard-course');

    post_type_taxonomy = currentCourseWrapper.length ? currentCourseWrapper.find('#course_type').attr('name') : $('input[type="hidden"].taxonomy').attr('name');
    post_type = currentCourseWrapper.length ? currentCourseWrapper.find('#course_type').val() : post_type;

    if (post_type == 'course' && post_type_taxonomy == 'course_type') {

      var edit_url = lucidlms_admin.post_url + '?post=' + id + '&action=edit';
      window.open(edit_url);

    } else if ((post_type == 'course_element' && post_type_taxonomy == 'course_element_type') || is_question_pool) {

      var title = panel.find('.type').text() + ' - ' + panel.find('.title').text();

      $.fn.editQuestion(id, title);

    }

  });

  /**
   * Handle save button in question modal
   */
  $(document).on('click', '.question-modal .save-question-meta', function (e) {

    $('form#question-form').submit();

  });

    /**
     * Insert selected questions from question pool to the quiz
     */
    $(document).on('click', '.insert-questions-modal button.insert-questions', function(){

        var wrapper = $('#lucidlms-course-element-meta');
        var post_id = lucidlms_admin.post_id !== '' ? lucidlms_admin.post_id : wrapper.find('#course_id').val();
        var $modalWrapper = $('.insert-questions-modal');
        var $questions = $modalWrapper.find('.question-group input.question:checked');
        var questions = [];

        $questions.each(function(){
            questions.push( $(this).val() );
        });

        if( questions.length > 0 ){
            $.fn.showOverlay(wrapper);

            $.post(
                lucidlms_admin.ajax_url,
                {
                    action:                   'insert_questions',
                    insert_questions_nonce:   lucidlms_admin.insert_questions_nonce,
                    post_id:                  post_id,
                    questions:                questions

                },
                function (response) {
                    if (response) {

                        wrapper.find('.questions').html(response);

                        $.fn.hideOverlay(wrapper);

                    }
                }
            );
        }

        $modalWrapper.modal('hide');

    });

    /**
     * Toggle related questions checkboxes by categories
     */
    $(document).on('click', '.insert-questions-modal .question-group input[type=checkbox].category', function(){
        var checked = $(this).is(':checked');

        $(this).closest('.question-group').find('input[type=checkbox].question').each(function(){
            $(this).prop('checked', checked);
        });

    });
        /**
   * Handle submit of question form via ajax
   */
  $(document).on('submit', 'form#question-form', function (e) {
    e.preventDefault();

    var questionType = $(this).find('.lucidlms-options').data('question-type');

    var validationPassed = true;
    var errorMessage = '';

    if ('open' !== questionType) {

      var hasCorrectAnswer = false;
      var hasEmptyFields = false;


      // validate answers here
      $(this).find('ul.answers > li').each(function () {

        var $answer = $(this);
        var answerText = $answer.find('input[type="text"]').val();
        var answerCorrect = $answer.find('input[type=checkbox]').is(':checked');

        if (!answerText) {
          hasEmptyFields = true;
          // validation failed
          $answer.css('border-color', 'red');
        } else {
          // it's ok
          $answer.css('border-color', '#DDD');
        }

        if (answerCorrect) {
          hasCorrectAnswer = true;
        }
      });

      if (hasEmptyFields) {
        errorMessage += lucidlms_admin.i18n_create_question_empty_answer + '\n';
        validationPassed = false;
      }

      if (!hasCorrectAnswer) {
        errorMessage += lucidlms_admin.i18n_create_question_no_correct_answer + '\n';
        validationPassed = false;
      }
    }
    if (validationPassed) {
      var wrapper = $('.question-modal');

      $.fn.showOverlay(wrapper);

      $.post(
          lucidlms_admin.ajax_url,
          {
            action                  : 'save_edit_question',
            save_edit_question_nonce: lucidlms_admin.save_edit_question_nonce,
            question                : $(this).serialize()
          },
          function (response) {

            $.fn.hideOverlay(wrapper);

            wrapper.modal('hide');

            $.fn.recheckQuestions();
          }
      );
    } else {
      if (errorMessage.length > 0) {
        alert(errorMessage)
      }
    }
    return false;
  });

  /**
   * Handle add answer inside question form
   */
  $(document).on('click', '.add-answer', function (e) {
    e.preventDefault();

    var uniqid = $.fn.uniqid('q_');

    var tmpl = '<li class="panel panel-default"><div class="panel-body">' +
        '<input type="text" name="_answers[' + uniqid + '][answer]" ' +
        'placeholder="' + lucidlms_admin.i18n_type_an_answer + '" value="" />' +
        '<input type="checkbox" class="input-switch" name="_answers[' + uniqid + '][is_correct]" ' +
        'data-on-color="success" data-off-color="danger" value="true" ' +
        'data-on-text="' + lucidlms_admin.i18n_correct_answer + '" ' +
        'data-off-text="' + lucidlms_admin.i18n_incorrect_answer + '">' +
        '<a href="#" class="btn btn-default remove-answer"><span class="glyphicon glyphicon-remove"></span></a>' +
        '</div></li>';

    answers_list.append(tmpl);

    $.fn.initModalHelpers();
  });

  /**
   * Handle remove answer inside question form
   */
  $(document).on('click', '.remove-answer', function (e) {
    e.preventDefault();

    $(this).closest('.panel').remove();
  });

  /**
   * Handle switch isCorrect for single_choice question type
   */
  $(document).on('switchChange.bootstrapSwitch', '.input-switch', function (event, state) {

    if (state && modalQuestionType === 'single_choice') {
      $('.input-switch:checked').not(this).bootstrapSwitch('state', false);
    }

  });

  /**
   * Score card: listen for student field to give admin ability choose only not started courses
   */
  $(document).on('change', 'body.post-new-php.post-type-score_card select[name="_student_id"]', function () {

    $.fn.replaceCoursesField($(this).val());
  });

  /**
   * Score card: hide/show results in score card backend
   */
  $(document).on('click', '.score-card-progress a.show-results', function (e) {
    e.preventDefault();

    $(this).next('.quiz-results').slideToggle();
  });

  /**
   * Dashboard: publish/unpublish course via ajax
   */
  $(document).on('click', '.dashboard-course .change-course-status a', function (e) {
    e.preventDefault();

    var wrapper = $(this).closest('.collapsible-course');
    if (!wrapper.length) {
      return false;
    }

    var post_id = wrapper.find('#course_id').val();

    $.fn.showOverlay(wrapper);

    $.post(
        lucidlms_admin.ajax_url,
        {
          action                    : 'change_course_status',
          change_course_status_nonce: lucidlms_admin.change_course_status_nonce,
          post_id                   : post_id,
          new_status                : $(this).data('status')
        },
        function (response) {
          if (response) {

            wrapper.html(response);

            $.fn.hideOverlay(wrapper);

            $.fn.initLucidLMSAdmin();
          }
        }
    );
    return false;
  });

  /**
   * Dashboard: add new course
   */
  $(document).on('click', '.lucidlms-dashboard a.add-course', function (e) {
    e.preventDefault();

    $('.new_course_name').animate({width: 'toggle'}, 350);

    $('.add-course').toggle();
    $('.confirm-course-title').toggle();
  });

  /**
   * Dashboard: create new course
   */
  $(document).on('click', '.lucidlms-dashboard a.confirm-course-title', $.fn.createCourse);
  $(document).on('keypress', '.new_course_name', function (e) {
    if (e.which == 13) {
      $.fn.createCourse(e);
      return false;    //<---- Add this line
    }
  });

  /**
   * Edit Quiz: insert questions from question pool
   */
  $(document).on('click', '.insert-from-question-pool', function (e){
      e.preventDefault();

      var wrapper = $('#lucidlms-course-element-meta');
      var $questions = $('.questions .question');
      var exclude_questions = [];

      // get ids of already present questions to exclude from insert modal
      $questions.each(function(){
          exclude_questions.push( $(this).prop('id').split('-')[1] );
      });

      $.fn.showOverlay(wrapper);

      $.post(
          lucidlms_admin.ajax_url,
          {
              action:                       'insert_questions_modal',
              insert_questions_modal_nonce: lucidlms_admin.insert_questions_modal_nonce,
              exclude_questions:            exclude_questions
          },
          function (response) {
              if (response) {

                  $.fn.initQuestionPoolModal(lucidlms_admin.i18n_insert_questions_modal_title, response);

                  $.fn.initModalHelpers();

                  $.fn.hideOverlay(wrapper);

              }
          }
      );
      return false;
  });

    /**
    * Question Pool: manage question's categories modal
    */
    $(document).on('click', '.manage-categories', function (e){
      e.preventDefault();

      $.fn.initQuestionCategoriesModal(lucidlms_admin.i18n_manage_question_categories_title);

      var wrapper = $('.question-categories-modal .modal-body');
      $.fn.showOverlay(wrapper);

      $.post(
          lucidlms_admin.ajax_url,
          {
              action:                                   'manage_questions_categories_modal',
              manage_questions_categories_modal_nonce:  lucidlms_admin.manage_questions_categories_modal_nonce
          },
          function (response) {
              if (response) {

                  wrapper.html(response);
                  $.fn.initModalHelpers();

                  $.fn.hideOverlay(wrapper);

              }
          }
      );
      return false;
    });

    /**
     * Question Pool: remove question from a category
     */
    $(document).on('click', '.lucidlms-question-pool .questions .categories .delete', function(){
        var $this = $(this);
        var $categoryTag = $this.parent();
        var $question = $this.closest('.question');
        var questionId = $question.data('id');
        var $categoriesWrapper = $question.find('.categories');
        var categoryId = $categoryTag.data('id');
        var categoryName = $categoryTag.text();



        $.fn.showOverlay($question);

        $.post(
            lucidlms_admin.ajax_url,
            {
                action:                         'question_remove_category',
                question_remove_category_nonce:   lucidlms_admin.question_remove_category_nonce,
                question_id:                    questionId,
                category_id:                    categoryId
            },
            function (response) {
                if (response.success && !response.error) {
                    //remove label with the category
                    $categoryTag.remove();
                    // if no categories left, add 'no categories' label
                    if( $categoriesWrapper.find('li:not(.add-category)').length == 0 ){
                        $categoriesWrapper.prepend('<li class="label label-success empty">' + lucidlms_admin.i18n_no_categories + '</li>').animate('fast');
                    }

                    // add category to 'Add category' select
                    $question.find('select.categories-select').append('<option value="' + categoryId + '">' + categoryName + '</option>');
                    // enable ability to add category (display '+')
                    $question.find('li.add-category.no-categories').removeClass('no-categories');

                } else {
                    alert(response.error);
                }
                $.fn.hideOverlay($question);
            }
        );

    });

    /**
     * Question Pool: Show categories list to add on question
     */
    $(document).on('click', '.categories a.add', function(e){
        e.preventDefault();

        $(this).hide();
        $(this).parent().find('.categories-select-wrapper').show();
    });

    /**
     * Question Pool
     * Manage Categories modal
     * Hide/show create new category form
     */
    $(document).on('click', '.question-categories-modal .create-category a.create', function(e){
       e.preventDefault();
       $(this).parent().find('form.create-category-form').toggleClass('visible', 4000);
    });

    /**
     * Question Pool
     * Manage Categories modal
     * Create new category
     */
    $(document).on('submit', '.question-categories-modal form.create-category-form', function(e){
        e.preventDefault();

        var $categoryNameInput = $(this).find('.new-category-name');
        var categoryName = $categoryNameInput.val();
        var $modal = $('.question-categories-modal');

        if( categoryName.length > 0 ){

            $.fn.showOverlay($modal);

            $.post(
                lucidlms_admin.ajax_url,
                {
                    action:                             'create_new_question_category',
                    create_new_question_category_nonce: lucidlms_admin.create_new_question_category_nonce,
                    category_name:                      categoryName
                },
                function (response) {
                    // response should be json with created category ID or error
                    var categoryId = parseInt( response.category_id );
                    if( categoryId && ! response.error ){
                        // add category to filter
                        $('.filters .categories-filter ul.categories-list').append('<li class="label" data-id="' + categoryId + '">' + categoryName +'</li>')

                        // add new category to selectboxes
                        $('.add-category .categories-select').each(function(){
                            $(this).append('<option value="' + categoryId + '">' + categoryName + '</option>');
                            if( $(this).closest('.add-category').hasClass('no-categories') ){
                                $(this).closest('.add-category').removeClass('no-categories');
                            }
                        });

                        // Add category to the modal body
                        $modal.find('.categories-list').prepend(
                            '<li class="list-group-item category-row" data-category-id="' + categoryId +'">'
                                + '<input type="text" class="category category-' + categoryId +'" name="category-' + categoryId +'" id="category-' + categoryId +'" value="' + categoryName +'" placeholder="Category name" disabled="disabled">'
                                + '<div class="btn-group category-controls" role="group">'
                                    + '<button type="button" class="btn btn-primary edit-category">Edit</button>'
                                    + '<button type="button" class="btn btn-default delete-category">Delete</button>'
                                    + '<button type="button" class="btn btn-success save-category">Save</button>'
                                + '</div>'
                                + '<div class="cf"></div>'
                            + '</li>'
                        );

                        // clear input with category name and return focus there
                        $categoryNameInput.val('');
                        $categoryNameInput.focus();
                    } else {
                        alert( response.error );
                    }

                    $.fn.hideOverlay($modal);
                }
            );
        } else {
            alert( lucidlms_admin.i18n_error_category_cannot_be_empty );
        }


    });

    /**
     * Question Pool
     * Manage Categories modal
     * Enable edit category view
     */
    $(document).on('click', '.question-categories-modal .categories-list .edit-category', function(){
        var $category = $(this).closest('.category-row');
        var $categoryName = $category.find('input.category');

        // enable input and set cursor to the end
        $categoryName.prop('disabled', false);
        // position where to put cursor
        // x2 for Opera browser
        var strLength= $categoryName.val().length * 2;
        $categoryName.focus();
        $categoryName[0].setSelectionRange(strLength, strLength);
        // hide edit and delete buttons
        $category.find('.edit-category').hide();
        $category.find('.delete-category').hide();
        // show save button
        $category.find('.save-category').show();
        // save value before changing
        $categoryName.data('prev-value', $categoryName.val());
    });

    /**
     * Question Pool
     * Manage Categories modal
     * Save category on "Save" button click or 'enter' button keypress
     */
    $(document).on('click', '.question-categories-modal .categories-list .save-category', saveCategory);
    $(document).on('keypress', '.question-categories-modal .categories-list input.category', saveCategory);

    function saveCategory(e){

        // fire save process for category on "Save" button click or 'enter' button keypress
        if( (e.type == 'click') || ( (e.type == 'keypress') && (e.which == 13) ) ){

            var $category = $(this).closest('.category-row');
            var $categoryName = $category.find('input.category');
            var categoryId = $category.data('category-id');
            var categoryName = $categoryName.val();

            if( categoryName.length > 0 ){

                $.fn.showOverlay($category);

                $.post(
                    lucidlms_admin.ajax_url,
                    {
                        action:                             'edit_question_category',
                        edit_question_category_nonce:       lucidlms_admin.edit_question_category_nonce,
                        new_category_name:                  categoryName,
                        category_id:                        categoryId
                    },
                    function (response) {
                        if( response.success && !response.error ){

                            // disable input
                            $categoryName.prop('disabled', true);
                            // hide edit and delete buttons
                            $category.find('.edit-category').show();
                            $category.find('.delete-category').show();
                            // show save button
                            $category.find('.save-category').hide();

                            //rename all related labels from filter
                            $('.filters .categories-filter .categories-list li[data-id=' + categoryId + ']').each(function(){
                                $(this).text(categoryName);
                            });

                            // rename options at "add question to label" options
                            $('.questions .question .add-category select.categories-select option[value=' + categoryId + ']').each(function(){
                                $(this).text(categoryName);
                            });

                            // rename question labels
                            $('.questions .question .taxonomies .categories > li[data-id=' + categoryId + ']').each(function(){
                                $(this).html(categoryName + '<i class="fa fa-times delete"></i>');
                            });


                        } else {
                            alert( response.error );
                        }

                        $.fn.hideOverlay($category);
                    }
                );
            } else {
                alert( lucidlms_admin.i18n_error_category_cannot_be_empty );
            }
        }

    }

    /**
     * Close edit category view by click on esc button
     */
    $(document).on('keyup', '.question-categories-modal .categories-list input.category', function(e){
        if( e.keyCode == 27 ){
            var $category = $(this).closest('.category-row');
            var $categoryName = $category.find('input.category');

            // disable input
            $categoryName.prop('disabled', true);
            // hide edit and delete buttons
            $category.find('.edit-category').show();
            $category.find('.delete-category').show();
            // show save button
            $category.find('.save-category').hide();
            // revert previous value
            $categoryName.val( $categoryName.data('prev-value') );
        }
    });

    /**
     * Question Pool
     * Manage Categories modal
     * Remove category on "Delete" button click
     */
    $(document).on('click', '.question-categories-modal .categories-list .delete-category', function(){
        if( confirm( lucidlms_admin.i18n_confirm_delete_category ) ) {

            var $categoryRow = $(this).closest('.category-row');
            var categoryId = $categoryRow.data('category-id');

            $.fn.showOverlay($categoryRow);

            $.post(
                lucidlms_admin.ajax_url,
                {
                    action:                             'remove_question_category',
                    remove_question_category_nonce:     lucidlms_admin.remove_question_category_nonce,
                    category_id:                        categoryId
                },
                function (response) {
                    if( response.success && !response.error  ){
                        // remove row from modal
                        $categoryRow.remove();
                        //remove all related labels from filter
                        $('.filters .categories-filter .categories-list li[data-id=' + categoryId + ']').each(function(){
                            $(this).remove();
                        });

                        // remove options from "add question to label" option
                        $('.questions .question .add-category select.categories-select option[value=' + categoryId + ']').each(function(){
                            var $addCategoryWrapper = $(this).closest('.add-category');
                            // if list of categories will be empty after we remove the last category, assign class 'no-categories'
                            if( $addCategoryWrapper.find('.categories-select option').length == 2 ){
                                $addCategoryWrapper.addClass('no-categories');
                            }
                            // delere option
                            $(this).remove();
                        });

                        // remove question labels
                        $('.questions .question .taxonomies .categories > li[data-id=' + categoryId + ']').each(function(){
                            var $categoriesLabelWrapper = $(this).parent();
                            // if no categories will remain after we delete the category, add 'no categories' label
                            if( $categoriesLabelWrapper.find('li:not(.add-category):not(.empty)').length == 1 ){
                                // if there is 'no categories' element, display it
                                if( $categoriesLabelWrapper.find('.empty').length > 0 ){
                                    $categoriesLabelWrapper.find('.empty').fadeIn();
                                } else {
                                    // otherwise, add this element and show
                                    $categoriesLabelWrapper.prepend('<li class="label label-success empty">' + lucidlms_admin.i18n_no_categories + '</li>').fadeIn();
                                }
                            }
                            // remove label for question
                            $(this).remove();
                        });

                    } else {
                        alert( response.error );
                    }


                    $.fn.hideOverlay($categoryRow);
                }
            );
        }
    });

    /**
     * Question Pool: hide 'Add category' selectbox on press 'Cancel'
     */
    $(document).on('click', '.lucidlms-question-pool .question .categories-select-wrapper a.cancel', function(e){
        e.preventDefault();
        e.stopPropagation();

        var wrapper = $(this).closest('.add-category');

        wrapper.find('.categories-select-wrapper').hide();
        wrapper.find('a.add').show();
    });

    /**
     * Question Pool: add category to a question
     */
    $(document).on('change', '.lucidlms-question-pool .questions .categories-select-wrapper select', function(){
        var $this = $(this);

        var $category = $this.find(':selected');
        var categoryId = $category.val();
        var categoryName = $category.text();

        var $question = $this.closest('.question');
        var questionId = $question.data('id');
        var $categoriesWrapper = $question.find('.categories');

        var $addCategoryWrapper = $this.closest('.add-category');


        $.fn.showOverlay($question);
        console.log($addCategoryWrapper.find('a.add'));

        $addCategoryWrapper.find('.categories-select-wrapper').hide('slow');
        $addCategoryWrapper.find('a.add').show();

        $.post(
            lucidlms_admin.ajax_url,
            {
                action:                         'question_add_category',
                question_add_category_nonce:   lucidlms_admin.question_add_category_nonce,
                question_id:                    questionId,
                category_id:                    categoryId
            },
            function (response) {
                if (response.success && !response.error) {

                    // hide empty label
                    $categoriesWrapper.find('.empty').hide('slow');

                    // create categorie's label
                    $categoriesWrapper.prepend('<li class="label label-success" data-id="' + categoryId + '">' + categoryName + '<i class="fa fa-times delete"></i></li>').animate('fast');

                    // remove category from list to add
                    $addCategoryWrapper.find('.categories-select option:selected').remove();

                    // if list of categories to add is empty, disable ability to add them at all (hide '+ Add category')
                    if( $addCategoryWrapper.find('.categories-select option').length <= 1 ){
                        $addCategoryWrapper.addClass('no-categories');
                    }
                } else {
                    alert(response.error);
                }

                $.fn.hideOverlay($question);
            }

        );

    });

  /**
   * Don't forget initialize for the first time
   */
  $.fn.initLucidLMSAdmin();

  /**
   * And don't forget to initialize chosen
   */
  $.fn.initChosen();
});
