<?php

namespace local_ai\output;

use local_ai\api;

use local_ai\output\index_page;
class renderer extends \plugin_renderer_base {

    public function render_index_page(index_page $indexpage) {
        $data = $indexpage->export_for_template($this);
        return parent::render_from_template('local_ai/index_page', $data);
    }

}
