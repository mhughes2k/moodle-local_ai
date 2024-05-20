<?php
namespace local_ai;
/**
 * Course module configuration object for AI.
 */
class cmconfig extends \core\persistent {

    const TABLE = "local_ai_cmconfig";

    protected static function define_properties() {
        return [
            'id' => [
                'type' => PARAM_INT
            ],
            'cmid' => [
                'type' => PARAM_INT
            ],
            'aiproviderid' => [
                'type' => PARAM_INT,
                'default' => -1,
                'null' => NULL_ALLOWED,
            ],
            'aicontext' => [
                'type' => PARAM_TEXT,
                'default' => ''
            ],
            'allowsummarise' => [
                'type' => PARAM_BOOL,
                'default' => false
            ],
            'allowexplain' => [
                'type' => PARAM_BOOL,
                'default' => false
            ],
            'allowask' => [
                'type' => PARAM_BOOL,
                'default' => false
            ],
            'allowanswer' => [
                'type' => PARAM_BOOL,
                'default' => false
            ],
            'allowtranslate' => [
                'type' => PARAM_BOOL,
                'default' => false

            ],
            'allowchat' => [
                'type' => PARAM_BOOL,
                'default' => true
            ],
        ];
    }

    public static function get_by_cmid(int $cmid) {
        if ($cmid < 0) {
            $return  = new static(-1, (object)['cmid' => $cmid]);
            $now = time();
            $return->set('timecreated', $now);
            $return->set('timemodified', $now);
            $return->set('usercreated', $now);
            $return->set('usercreated', $now);
            return $return;
        }
        $existing =  self::get_record(['cmid' => $cmid]);
        if ($existing) {
            return $existing;
        } else {
            return new static(-1, (object)['cmid' => $cmid]);
        }
    }

}
