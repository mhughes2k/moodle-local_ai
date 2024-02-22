<?
defined('MOODLE_INTERNAL') || die();

use core\analytics\indicator\any_write_action_in_course;
use core\event\base;
use local_ai\task\run_python_command;

class local_ai_observer {

    /**
     * List of modules that are supported.
     */
    static $supported = [
        'resource'
    ];

   
    /**
     * Handles course module updates
     */
    public static function course_module_updated(base $event) {
        $type = $event->other['modulename'];

        if (in_array(strtolower($type), self::$supported)) {
            $observer = new local_ai_observer($event);
            $observer->handle();
        }
    }

    private $event = null;
    private $mod = null;
    function __construct(base $event) {
        $this->event = $event;
        $this->mod = $event->other['modulename'];
    }

    const ACTION_INSERT = 1;
    const ACTION_UPDATE = 2;
    function handle(): void {
        
        $context = $this->event->get_context();
        $data = $this->event->get_data();
        $cmid = $data['objectid'];
        list($course,$cm) = get_course_and_cm_from_cmid($cmid);
        $class = get_class($this->event);
        $action = 0;
        switch(strtolower($class)) {
            case "core\event\course_module_created":
                $action = self::ACTION_INSERT;
                break;
            case "core\event\course_module_updated":
                $action = self::ACTION_UPDATE;
                break;
        }
        debugging("handle");
        $methodname = "handle_{$this->mod}";
        if (method_exists($this, $methodname)) {
            debugging("Running $methodname");
            $this->$methodname($cm, $context);
        } else {
            debugging("No {$methodname} found");
        }
        
    }

    function handle_resource($cm, $context) {
        debugging("Handling resource");
        // For the moment we're running 1 global store in /global
        $fs = get_file_storage();
        $files = $fs->get_area_files(
            $context->id,
            'mod_' . $this->mod,
            'content'
        );
        $ragmanager = new local_ai\local\manager("global");
        $cmmeta = [
            'coursemoduleid' => $cm->id,
            'context' => $context->id
        ];
        foreach($files as $file) {
            if ($file->get_filename() == ".") {
                continue;
            }
            $fmeta = $cmmeta;
            $fmeta['filename'] = $file->get_filename();
            $fmeta['author'] = $file->get_author();
            $fmata['license'] = $file->get_license();
            $ragmanager->add_document($file, $fmeta);
        }
    }
}