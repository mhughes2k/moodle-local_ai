<?php
// We're mocking a core Moodle "AI" Subsystem a la Oauth 2
namespace local_ai;

use core\persistent;
use core_course_category;

class AIProvider extends persistent implements LoggerAwareInterface  {
    use LoggerAwareTrait;

    const CONTEXT_128K = 120000; // in K.
// Ultimately this would extend a persistent.

    const CONTEXT_ALL_MY_COURSES = -1;
    const TABLE = "local_ai_aiprovider";

    protected static function define_properties()
    {
        return [
            'name' => [
                'type' => PARAM_TEXT
            ],
            'enabled' => [
                'type' => PARAM_BOOL
            ],
            'apikey' =>[
                'type' => PARAM_ALPHANUMEXT
            ],
            'allowembeddings' => [
                'type' => PARAM_BOOL
            ],
            'allowchat' => [
                'type' => PARAM_BOOL
            ],
            'baseurl' => [
                'type' => PARAM_URL
            ],
            'embeddingsurl' => [
                'type' => PARAM_URL
            ],
            'embeddingmodel' => [
                'type' => PARAM_ALPHANUMEXT
            ],
            'embeddingdimensions' => [
                'type' => PARAM_INT,
                'default' => 1536
            ],
            'completionsurl' => [
                'type' => PARAM_URL
            ],
            'completionmodel' => [
                'type' => PARAM_ALPHANUMEXT
            ],
            // What context is this provider attached to. 
            // If null, it's a global provider.
            // If -1 its limited to user's own courses.
            'contextid' => [
                'type' => PARAM_INT
            ],
            // If true, only courses that the user is enrolled in will be searched.
            'onlyenrolledcourses' => [
                'type' => PARAM_BOOL
            ],
            'aimaxcontext' => [
                'type' => PARAM_INT,
                'default' => self::CONTEXT_128K
            ]
        ];
    }

    /**
     * @param string $prefix
     * @return LoggerInterface
     * @throws \coding_exception
     */
    public function get_logger(string $prefix = "") {
        if (is_null($this->logger)) {

            $id = $this->get('id');
            $name = "aiprovider-{$id}";
            if (!empty($prefix)) {
                $name = $prefix . "-{$name}";
            }
            $name .= ".log";
            $this->setLogger(new logger($name));
        }
        return $this->logger;
    }
    /**
     * Work out the context path from the site to this AI Provider's context
     * @return void
     */
    public function get_context_path() {
        $context = \context::instance_by_id($this->get('contextid'));
    }
    public function use_for_embeddings(): bool {
        return $this->get('allowembeddings');
    }

    public function use_for_query():bool {
        return $this->get('allowquery');
    }
    public function get_usage($type) {

        $key = [
            '$type',
            $this->get('id'),
            $this->get('apikey'),
        ];
        $key = implode("_", $key);
        $current = get_config('ai', $key);
        return $current;
    }
    public function increment_prompt_usage($change) {

        $key = [
            'prompttokens',
            $this->get('id'),
            $this->get('apikey'),
        ];
        $key = implode("_", $key);
        $current = get_config('ai', $key);
        $new = $current + $change;
        $this->logger->info("Incrementing prompt token usage from {$current} to {$new}");
        // set_config($key, $new, 'ai');
        return $new;
    }
    public function increment_completion_tokens($change) {

        $key = [
            'completiontokens',
            $this->get('id'),
            $this->get('apikey'),
        ];
        $key = implode("_", $key);
        $current = get_config('ai', $key);
        $new = $current + $change;
        $this->logger->info("Incrementing completion token usage from {$current} to {$new}");
//        set_config($key, $new, 'ai');
        return $new;
    }
    public function increment_total_tokens($change) {

        $key = [
            'totaltokens',
            $this->get('id'),
            $this->get('apikey'),
        ];
        $key = implode("_", $key);
        $current = get_config('ai', $key);
        $new = $current + $change;
        $this->logger->info("Incrementing total token usage from {$current} to {$new}");
//        set_config($key, $new, 'ai');
        return $new;
    }

    /**
     * Returns appropriate search settings based on 
     * provider configuration.
     */
    public function get_settings() {
        // `userquery` and `vector` will be filled at run time.
        $settings = [
            'userquery'=> null,
            'vector' => null,
            // `similarity` is a boolean that determines if the query should use vector similarity search.
            'similarity' => true,            
            'areaids' => [],
            // `excludeareaids` is an array of areaids that should be excluded from the search.
            'excludeareaids'=> ["core_user-user"],  // <-- This may be should be in control of the AI Provider.
            'courseids' => [],   // This of course IDs that result should be limited to.
        ];
        return $settings;
    }

    /**
     * Gets user specific settings.
     * 
     * This takes on some of the function that the manager code did.
     * @param stdClass $cm
     * @param stdClass $user
     * @returns stdClass User specific config.
     */
    public function get_settings_for_user($cm, $user) {
        global $DB;
        $globalconfig = get_config('local_ai');
        $usersettings =  $this->get_settings();
        $usersettings['systemprompt'] = $globalconfig->systemprompt;

        // This is basically manager::build_limitcourseids().
        $mycourseids = enrol_get_my_courses(array('id', 'cacherev'), 'id', 0, [], false);
        $onlyenrolledcourses = $this->get('onlyenrolledcourses');
        $courseids = [];
        if ($this->get('contextid') == self::CONTEXT_ALL_MY_COURSES) {
            $courseids  = array_keys($mycourseids);
        } else if ($this->get('contextid') == 0) {
            $context = \context_system::instance();
        } else {

            $context = \context::instance_by_id($this->get('contextid'));
            if ($context->contextlevel == CONTEXT_COURSE) {
                // Check that the specific course is also in the user's list of courses.
                $courseids = array_intersect([$context->instanceid], $mycourseids);
            } else if ($context->contextlevel == CONTEXT_COURSECAT) {
                // CourseIDs will be all courses in the category, 
                // optionally that the user is enrolled in
                $category = core_course_category::get($context->instanceid);
                $categorycourseids = $category->get_courses([
                    'recursive'=>true,
                    'idonly' => true
                ]);
            } else if ($context->contextlevel == CONTEXT_SYSTEM) {
                // No restrictions anywhere.
            }
        }
        $usersettings['courseids'] = $courseids;

        // Now apply any coursemodule settings.

        if(!is_null($cm) && $cmconfig = $DB->get_record('local_ai_cmconfig', ['cmid' => $cm->id])) {
            $skip = ['id', 'cmid'];
            foreach ($cmconfig as $setting => $value) {
                if (in_array($value, $skip)) {
                    continue;
                }
                $usersettings[$setting] = $value;
            }
        }
        return $usersettings;
    }

    //public function
    // TODO token counting.
    /**
     * We're overriding this whilst we don't have a real DB table.
     * @param $filters This can have the contexts, but also can have 'excludesystem' set to not include system AI Providers.
     * @param $sort
     * @param $order
     * @param $skip
     * @param $limit
     * @return array
     */
    public static function get_records($filters = [], $sort = '', $order = 'ASC', $skip = 0, $limit = 0) {
        global $_ENV;
/*        $records = [];
        $fake = new static(0, (object) [
            'id' => 1,
            'name' => "Open AI Provider (hardcoded)",
            'enabled' => true,
            'allowembeddings' => true,
            'allowchat' => true,
            'baseurl' => 'https://api.openai.com/v1/',
            'embeddings' => 'embeddings',
            'embeddingmodel' => 'text-embedding-3-small',
            'completions' => 'chat/completions',
            'completionmodel' => 'gpt-4-turbo-preview',
            'apikey'=> $_ENV['OPENAIKEY'],
            'contextid' => \context_system::instance()->id,
            //null,  // Global AI Provider
            'onlyenrolledcourses' => true
        ]);
        array_push($records, $fake);
        $fake = new static(0, (object) [
            'id' => 2,
            'name' => "Ollama AI Provider (hard coded)",
            'enabled' => true,
            'allowembeddings' => true,
            'allowchat' => true,
            'baseurl' => 'http://127.0.0.1:11434/api/',
            'embeddings' => 'embeddings',
            'embeddingmodel' => '',
            'completions' => 'chat',
            'completionmodel' => 'llama2',
            'contextid' => null,  // Global AI Provider
            'onlyenrolledcourses' => true
        ]);
        array_push($records, $fake);*/
/*
        $fake = new static(0, (object) [
            'id' => 3,
            'name' => "Ollama AI Provider (hard coded) Misc Category only",
            'enabled' => true,
            'allowembeddings' => true,
            'allowchat' => true,
            'baseurl' => 'http://127.0.0.1:11434/api/',
            'embeddings' => 'embeddings',
            'embeddingmodel' => '',
            'completions' => 'chat',
            'completionmodel' => 'llama2',
            'contextid' => \context_system::instance()->id,
            // 111,  // Global AI Provider
            'onlyenrolledcourses' => true,
        ]);
        array_push($records, $fake);
*/
        $targetcontextid = $filters['contextid'] ?? null;
        $targetcontext = null;
        if (is_null($targetcontextid)) {
            unset($filters['contextid']); // Because we need special handling.
        } else {
            $targetcontext = \context::instance_by_id($targetcontextid);
        }
        $systemproviders = [];
        if (!isset($filters['excludesystem'])) {
            $systemfilter = $filters;
            $systemfilter['contextid'] = 0;
            $systemproviders = parent::get_records($systemfilter, $sort, $order, $skip, $limit);
        }
        $records = parent::get_records($filters, $sort, $order, $skip, $limit);
        $records = array_filter($records, function($record) use ($filters, $targetcontext) {
            $result = true;
            $providercontextid = $record->get('contextid');
            // System provider is already listed.
            if ($providercontextid == 0) {
                return false;
            }
            foreach($filters as $key => $value) {
                if ($key == "contextid") {
                    if ($providercontextid == self::CONTEXT_ALL_MY_COURSES) {
                        // More problematic.
                        $result = $result & true;
                    } else if ($providercontextid == 0) {
                        return false;
                    } else {
                        $providercontext = \context::instance_by_id(
                            $providercontextid
                        );
                        $ischild = $targetcontext->is_child_of($providercontext, true);
                        $result = $result & $ischild;
                    }
                }else {
                    if ($record->get($key) != $value) {
                        return false;
                    }
                }
            }
            return $result;
        });
        $records = array_merge($systemproviders, $records);
        return $records;
    }
    public function generate_system_prompts($cm, $USER) {

        $cmconfig = $this->get_settings_for_user($cm, $USER);
        $messages = [];
//        $messages
//        echo "<h1>Initial context</h1><pre>";
//        var_dump($cmconfig);
//        echo "</pre>";
        $sysprompt = $cmconfig['systemprompt'];
        $sysprompt .= !empty($cmconfig['aicontext']) ? "\n{$cmconfig['aicontext']}": "";
        $messages[] = $this->create_message(
            "system",
            $sysprompt
        );
        return $messages;
    }

    public function create_message($role, $content) {
        return (object)[
            'role' => $role,
            'content' => $content
        ];
    }
}
