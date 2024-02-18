<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
namespace local_ai\local;

use core\stored_file;
use local_ai\task\run_python_command;
require_once($CFG->libdir. '/filelib.php');

/**
 * AI Manager
 */
class manager {

    const DATA_DIR = '/ai';
    //const PY_ADD_DOCUMENT = 'add_to_vectorstore.py vectorstorelocation={$vectorstorelocation} documentlocation={$documentpath} metadata="{$metadata}"';
    private $id = null;
    private $data_dir = null;
    /**
     * @param $storeidentifier Uniquely identifies a vector store.
     */
    public function __construct($storeidentifier) {
        global $CFG;
        $this->id = $storeidentifier;
        $this->data_dir = $CFG->dataroot . self::DATA_DIR . '/' . $storeidentifier;
        $this->initialise_store();
    }
    public function initialise_store() {
        $exists = true;
        // Check if we have already created a data area for the vector store.
        debugging("Checking for Vector store at {$this->data_dir}");
        if (!check_dir_exists($this->data_dir, false)) {
            debugging("Vector store data directory does not exist, creating it now.");
            $exists = check_dir_exists($this->data_dir, true, true);
            $params = [
                'vectorstoreidentifier' => $this->id,
                'vectorstorelocation' => $this->data_dir
            ];
            $task = run_python_command::instance($this->id,"init_vectorstore.py", $params);
            $task->execute();
        } else {
            debugging("Vector store already exists {$this->data_dir}");
        }
    }

    /**
     * @param $file
     * @param $metadata
     * @return void
     */
    public function add_document(stored_file $file, array $metadata) {

        $params = [
            'vectorstorelocation' => $this->data_dir,
            'documentlocation' => $file->get_contenthash(),
            'metadata' => json_encode($metadata)
        ];
        $task = run_python_command::instance("add_to_vectorstore.py", $params);
        $task->execute();
    }

    public function generate_command($command, $parameters) {
        $cmd = escapeshellcmd($command);
        $cmd = $cmd ." ";
        foreach($parameters as $key => $value) {
            $cmd .= "{$key}=" . escapeshellarg($value) ." ";
        }
        return $cmd;
    }

    public function execute_command($cmd) {
        $execute = false;
        $result = 0;
        $tempdir = make_temp_directory('ai');
        $output = null;
        $returncode = null;
        chdir($tempdir);
        if ($execute) {
            $result = exec($cmd, $output, $returncode);
        } else {
            mtrace("Would have executed: ".$cmd);
        }
        return $result;
    }

    public function chat($message) {
        // We're going to use the OpenAI API format to get chat completions.

        $baseurl = "http://host.docker.internal:11434/v1/"; // Ollama running locally just now, but will become a config setting or delegated to an AI provider class.
        $apikey = "ollama"; // Temporary, needed for OpenAI but not ollama.
        
        $url = "{$baseurl}chat/completions" ;
        $headers = [
            'Content-Type' => "application/json"
        ];

        // TODO Perform RAG.
        $data = [
            'model' => "llama2",
            'messages' => [
                // These are arrays but since they're associative they'll be converted to JSON Objects.
                [
                    "role"=>"system",
                    "content"=>"You are a helpful assistant"
                ],
                [
                    "role" => "user",
                    "content" => $message
                ]
            ]
        ];
        
        $postdata = json_encode($data);
        if (debugging()) {
            echo "$url\n$postdata";
        }
        $result = \download_file_content(
            $url,
            $headers,
            $postdata
        );
        $result = json_decode($result);
        var_dump($result);
        $responses = $result->choices;
        foreach($responses as $response) {
            echo $response->message->content ."\n";
        }
    }
}
