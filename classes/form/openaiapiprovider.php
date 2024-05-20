<?php

namespace local_ai\form;
use local_ai\api;
class openaiapiprovider extends \core\form\persistent{
    /** @var string $persistentclass */
    protected static $persistentclass = 'local_ai\\aiprovider';

    protected static $fieldstoremove = [
        'type',
        'submitbutton',
        'action'
    ];

    public function __construct($action = null, $customdata = null, $method = 'post', $target = '', $attributes = null,
                                $editable = true, array $ajaxformdata = null){
        if (array_key_exists('type', $customdata)) {
            $this->type = $customdata['type'];
        }

        parent::__construct($action, $customdata, $method, $target, $attributes, $editable, $ajaxformdata);
    }
    public function definition() {
        global $PAGE, $OUTPUT;

        $mform = $this->_form;
        $provider = $this->get_persistent();
//        $mform->addElement('html','intro', 'hello');
        $mform->addElement('header', 'features', get_string('general', 'local_ai'));
        // Name.
        $mform->addElement('text', 'name', get_string('providername', 'local_ai'));
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->addHelpButton('name', 'providername', 'local_ai');
        $mform->addElement('advcheckbox', 'enabled', get_string('enabled', 'local_ai'));
        $mform->addHelpButton('enabled', 'enabled', 'local_ai');
        $mform->setDefault('enabled', true);
        // Client Secret.
        $mform->addElement('text','baseurl', get_string('baseurl', 'local_ai'));
        $mform->setType('baseurl', PARAM_URL);
        $mform->addElement('text', 'apikey', get_string('apikey', 'local_ai'));
        $mform->addRule('apikey', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->addHelpButton('apikey', 'apikey', 'local_ai');

        $mform->addElement('header', 'actions', get_string('features', 'local_ai'));
        $mform->addHelpButton('actions', 'actions', 'local_ai');

        $mform->addElement('advcheckbox', 'allowchat', get_string('allowchat', 'local_ai'));
        $mform->addHelpButton('allowchat', 'allowchat', 'local_ai');
        $mform->addElement('text','completionsurl', get_string('completionspath', 'local_ai'));
        $mform->addElement('text','completionmodel', get_string('completionmodel', 'local_ai'));
        $mform->disabledIf('completionsurl', 'allowchat', 'notchecked');
        $mform->disabledIf('completionmodel', 'allowchat', 'notchecked');

        $mform->addElement('advcheckbox', 'allowembeddings', get_string('allowembeddings', 'local_ai'));
        $mform->addHelpButton('allowembeddings', 'allowembeddings', 'local_ai');
        $mform->addElement('text','embeddingsurl', get_string('embeddingspath', 'local_ai'));
        $mform->addElement('text','embeddingmodel', get_string('embeddingmodel', 'local_ai'));
        $mform->disabledIf('embeddings', 'allowembeddings', 'notchecked');
        $mform->disabledIf('embeddingmodel', 'allowembeddings', 'notchecked');

        $mform->addElement('advcheckbox', 'allowsummarise', get_string('allowembeddings', 'local_ai'));
        $mform->addHelpButton('allowsummarise', 'allowsummarise', 'local_ai');

        $mform->addElement('advcheckbox', 'allowexplain', get_string('allowexplain', 'local_ai'));
        $mform->addHelpButton('allowexplain', 'allowexplain', 'local_ai');

        $mform->addElement('advcheckbox', 'allowask', get_string('allowask', 'local_ai'));
        $mform->addHelpButton('allowask', 'allowask', 'local_ai');

        $mform->addElement('advcheckbox', 'allowask', get_string('allowask', 'local_ai'));
        $mform->addHelpButton('allowask', 'allowask', 'local_ai');

        $mform->addElement('advcheckbox', 'allowtranslate', get_string('allowtranslate', 'local_ai'));
        $mform->addHelpButton('allowanswer', 'allowanswer', 'local_ai');

        $mform->addElement('advcheckbox', 'allowtranslate', get_string('allowtranslate', 'local_ai'));
        $mform->addHelpButton('allowanswer', 'allowtranslate', 'local_ai');

        $mform->addElement('header','contentconstraints', get_string('contentconstraints', 'local_ai'));

        $displaylist = [
            ""  => get_string('anywhere', 'local_ai'),
            "-1" => get_string('anyusercourse', 'local_ai')
        ];
        $displaylist =
            $displaylist +
            \core_course_category::make_categories_list('moodle/ai:selectcategory')
        ;
        
        $mform->addElement('autocomplete', 'categoryid', get_string('scopecoursecategory','local_ai'), $displaylist);
        $mform->addHelpButton('categoryid', 'scopecoursecategory', 'local_ai');
        $mform->setDefault('categoryid', null); // a null category is technical "whole" site

        $coursedisplaylist = \get_courses("all", "shortname");
        $coursedisplaylist = array_map(function($course) {
            return $course->shortname;
        }, $coursedisplaylist);
        $coursedisplaylist = ["" => "No Restriction"] + $coursedisplaylist;
        $mform->addElement('autocomplete', 'courseid', get_string('course'), $coursedisplaylist);
        $mform->addHelpButton('courseid', 'scopecourse', 'local_ai');
        $mform->setDefault('courseid', null); // a null category is technical "whole" site
//        $mform->disabledIf('courseid', 'categoryid', 'neq', "");

        $mform->addElement('hidden', 'contextid', );
        $mform->setType('contextid', PARAM_RAW);

        $mform->addElement('hidden', 'onlyenrolledcourses', );
        $mform->setType('onlyenrolledcourses', PARAM_RAW);

//        $mform->addElement('hidden', 'enabled', true);
//        $mform->setType('enabled', PARAM_ALPHA);
        
        $mform->addElement('hidden', 'type', $this->_customdata['type']);
        $mform->setType('type', PARAM_ALPHANUM);

        $mform->addElement('hidden', 'action', api::ACTION_EDIT_PROVIDER);
        $mform->setType('action', PARAM_ALPHA);

        $mform->addElement('hidden', 'id', $provider->get('id'));
        $mform->setType('id', PARAM_INT);
        $this->add_action_buttons(true, get_string('savechanges', 'local_ai'));
    }

    protected function filter_data_for_persistent($data) {
        if (!empty($data->categoryid)) {
            $data->onlyenrolledcourses = false;
            if ($data->categoryid >0) {
                $data->contextid = \core_course_category::get($data->categoryid)->get_context()->id;
            } else{
                $data->contextid = $data->categoryid;
                if ($data->contextid == -1) {
                    $data->onlyenrolledcourses = true;
                }
            }
        } else if (!empty($data->courseid)) {
            $data->contextid = \core\context\course::instance($data->courseid)->id;
        }
        return (object) array_diff_key((array) $data, array_flip((array) static::$foreignfields));
    }
    function extra_validation($data, $files, array &$errors) {
        parent::extra_validation($data, $files, $errors);
        if(!empty($data->categoryid) && !empty($data->courseid)) {
            // $data->category is not allowed to be set.
            $errors['courseid'] = "Course constraint cannot be set whilst a category one is set";
        }
        var_dump($errors);
        return $errors;
    }
}
