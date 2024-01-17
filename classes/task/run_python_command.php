<?php

namespace local_ai\task;

class run_python_command extends \core\task\adhoc_task {

    public static function instance(
        $vectorstoreidentifier,
        $command,
        $parameters
    ) {
        $task = new self();
        $task->set_custom_data((object)[
            'command' => $command,
            'parameters' => $parameters,
            'vectorstoreidentifier' => $vectorstoreidentifier
        ]);
        return $task;
    }
    public function execute() {
        $data = $this->get_custom_data();
        $vid = $data->vectorstoreidentifier;
        $manager = new \local_ai\local\manager($vid);
        $cmd = $manager->generate_command($data->command, $data->parameters);
        $manager->execute_command($cmd);
    }
}
