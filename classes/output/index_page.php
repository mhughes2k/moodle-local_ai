<?php
namespace local_ai\output;
use local_ai\api;

class index_page implements \renderable, \templatable {

    private $providers = null;
    function __construct($providers) {
        $this->providers = $providers;
    }

    public function providers_table($providers) {
        global $CFG;
        $table = new \html_table();
        $table->head = [
            'name',
            'context',
            'completion',
            'embeddings',
            'status',
            'edit'
        ];

        $table->attributes['class'] = 'admintable generaltable';
        $data = [];
        $contextcache = [];
        $index = 0;
        foreach ($providers as $provider) {
            $first = false;
            if ($index == 0) {
                $first = true;
            }
            $last = false;
            if ($index == count($providers) - 1) {
                $last = true;
            }

            $name = $provider->get('name');
            $contextid = $provider->get('contextid');
            $context = "";
            if($contextid >0) {
                if (
                    !isset($contextcache[$contextid])
                ) {
                    $contextcache[$contextid] = \context::instance_by_id($contextid);
                }
                $contextinstance = $contextcache[$contextid];
                $context = $contextinstance->get_context_name();
            } else if ($contextid < 0){
                $context = "User's own courses";
            } else {
                $context = "System";
            }
            $completion = $provider->get('allowchat');
            $embeddings = $provider->get('allowembeddings');
            $status = $provider->get('enabled');

            // Set up cells.
            $namecell = new \html_table_cell($name);
            $namecell->header = true;

            $contextcell = new \html_table_cell($context);

            $completioncell = new \html_table_cell(
                $completion
                    ?"yes"//$this->pix_icon('yes', get_string('enabled','ai'))
                    :"no"//$this->pix_icon('no', get_string('disabled','ai'))
            );

            $embeddingscell = new \html_table_cell(
                $embeddings
                    ?"yes"//$this->pix_icon('yes', get_string('enabled','ai'), 'ai')
                    :"no"//$this->pix_icon('no', get_string('disabled','ai'), 'ai')
            );

            $statuscell = new \html_table_cell($status);
            $statuscell->header = true;

            $links = "";
            // Action links.
            $editurl = new \moodle_url($CFG->wwwroot . '/ai/index.php',
                [
                    'action' => api::ACTION_EDIT_PROVIDER,
                    'pid' => $provider->get('id')
                ]);
            $links .= \html_writer::link($editurl, 'Edit');
            $editcell = new \html_table_cell($links);
            $row = new \html_table_row([
                $namecell,
                $contextcell,
                $completioncell,
                $embeddingscell,
                $statuscell,
                $editcell
            ]);
            $data[] = $row;
        }
        $table->data = $data;
        return \html_writer::table($table);
    }

    private $providertypes = [
        'openaiapi' => 'OpenAI API'
    ];
    protected function template_buttons() {
        global $CFG;
        $buttons = [];
        foreach($this->providertypes as $type => $name) {
            $addurl = new \moodle_url($CFG->wwwroot . '/local/ai/index.php',
                [
                    'action' => api::ACTION_EDIT_PROVIDER,
                    'type' => $type,
                    'pid' => null
                ]
            );
            $buttontext = get_string('newprovider', 'ai', $name);
            $buttons[] = [$addurl, $buttontext];
        }
        return $buttons;
    }
    public function export_for_template(\renderer_base $output) : object{
        $buttondefs = $this->template_buttons();
        $buttons = array_map(function($button) use ($output) {
            return $output->single_button($button[0], $button[1]);
        }, $buttondefs);
        $data = [
            'providers_table' => $this->providers_table($this->providers),
            'templates' => $buttons
        ];
        return (object)$data;
    }
}
