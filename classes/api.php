<?php
// We're mocking a core Moodle "AI" Subsystem a la Oauth 2

namespace local_ai;
use local_ai\aiprovider;

/**
 * AI Help API.
 */
class api {
    const ACTION_ADD_PROVIDER = "add";
    const ACTION_REMOVE_PROVIDER = "remove";
    const ACTION_EDIT_PROVIDER = "edit";
    const ACTION_MANAGE_PROVIDERS = "manage";

    const ACTION_SAVE_PROVIDER = "save";
    /**
     * Return a list of AIProviders that are available for specified context.
     * @param $context
     * @return array
     */
    public static function get_all_providers($context = null) {
        return array_values(aiprovider::get_records());
    }
    public static function get_provider(int $id): AIProvider|false  {
        // Handle weird case where settings are trying access this before the table exists.
        try {
            $provider = aiprovider::get_record(['id' => $id]);
        } catch (\Exception $e) {
            $provider = false;
        }
        return $provider;
    }

    /**
     * @param $contextid
     * @param $allowchat
     * @param $allowembeddings
     * @return array
     */
    public static function get_providers($contextid = null, $allowchat = null, $allowembeddings = null) {
        $requirements  = ['contextid', 'allowchat', 'allowembeddings'];
        // Filtering AI providers that are available to $contextid, walking up the
        // tree when we only have the contextid the AIProvider is set *on* is going to take
        // more work.
        $filters = [];
        foreach($requirements as $req) {

            $reqparam = ${$req};
            // Null means we don't consider it.
            if (!is_null($reqparam)) {
                // True means it must be offered
                // false means it must *not* be offered by the provider
                $filters[$req] = $reqparam;
            }
        }
        debugging(print_r($filters,1), DEBUG_DEVELOPER);
        $providers = aiprovider::get_records($filters);
        return array_values($providers);
    }
    public static function create_provider($data) {
        return self::create_or_update_provider($data, true);
    }
    public static function update_provider($data) {
        return self::create_or_update_provider($data, false);
    }
    protected static function create_or_update_provider($data, bool $create) {
        //TODO Capability check.
        $provider = new aiprovider($data->id ?? 0, $data);

        if ($create) {
            $provider->create();
        } else {
            $provider->update();
        }
        return $provider;
    }
}
