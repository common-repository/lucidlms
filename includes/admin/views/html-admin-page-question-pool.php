<div class="wrap lucidlms lucidlms-question-pool">

    <h2>
        <?php _e( 'Question Pool', 'lucidlms' ); ?>
    </h2>

    <div class="description">
        <?php _e('This is your question repository: stored and managed in one place. Create questions and assign categories to them, then pull questions into your quizzes.', 'lucidlms') ?>
    </div>

    <div class="filters">
        <div class="filter categories-filter">
            <div class="caption">
                <h3><?php _e('By category: ', 'lucidlms') ?></h3>
                <a class="manage-categories" href="#">
                    <i class="fa fa-cog"></i> <?php _e('Manage categories', 'lucidlms') ?>
                </a>
            </div>
            <ul class="categories-list">
                <?php include 'question-pool/html-admin-question-pool-categories.php' ?>
            </ul>
            <div class="cf"></div>
        </div>

        <div class="filter courses-filter">
            <div class="caption">
                <h3><?php _e('By course: ', 'lucidlms') ?></h3>
                <a class="browse-courses" href="<?php echo admin_url( 'admin.php?page=lucid-dashboard' ); ?>">
                    <i class="fa fa-angle-double-right"></i>
                    <?php _e('Browse courses', 'lucidlms') ?>
                </a>
            </div>
            <ul class="courses-list">
                <?php include 'question-pool/html-admin-question-pool-courses.php' ?>
            </ul>
            <div class="cf"></div>

        </div>

    </div>
    <div class="questions-area-wrapper">
        <div class="new-question-wrapper">
            <?php lucidlms_wp_select( array(
                'id'      => 'new_element',
                'options' => $available_question_types,
            ) ); ?>

            <p class="input-group">
                <input type="text" class="form-control new_element_name" placeholder="<?php _e( 'new question text...', 'lucidlms' ) ?>">
                <span class="input-group-btn">
                    <button class="btn btn-primary create-element" type="button">
                        <?php _e( 'Create', 'lucidlms' ) ?>
                    </button>
                </span>
            </p>
        </div>

        <ul class="list-group questions no-sortable-ui">
            <?php include 'question-pool/html-admin-question-pool-questions.php' ?>
        </ul>

    </div>
    <?php //@TODO: pagination ?>


    <?php lucidlms_wp_hidden_input( array(
        'id'    => 'is_question_pool',
        'value' => TRUE
    ) ); ?>

</div>