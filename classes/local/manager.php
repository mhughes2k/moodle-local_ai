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

use core\task\adhoc_task;
use \stored_file;
use local_ai\task\run_python_command;
require_once($CFG->libdir. '/filelib.php');

/**
 * AI Manager
 */
class manager {

    const DATA_DIR = '/local_ai';
    //const PY_ADD_DOCUMENT = 'add_to_vectorstore.py vectorstorelocation={$vectorstorelocation} documentlocation={$documentpath} metadata="{$metadata}"';
    private $id = null;
    private $data_dir = null;

    /**
     * Should we output verification content?
     */
    private $verification = true;
    /**
     * Directory in moodle data/local_ai/ for verification output to be placed.
     */
    private $verificationdir = '/var/www/html/local/ai/verify';
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
            debugging("Vector store data directory does not exist {$this->data_dir}, creating it now.");
            // $exists = check_dir_exists($this->data_dir, true, true);
            // $params = [
            //     'vectorstoreidentifier' => $this->id,
            //     'vectorstorelocation' => $this->data_dir
            // ];
            // $task = run_python_command::instance($this->id,"init_vectorstore.py", $params);
            // $task->execute();
        } else {
            debugging("Vector store already exists {$this->data_dir}");
        }
    }

    /**
     * @param $file
     * @param $metadata
     * @return void
     */
    public function add_document(stored_file $file, array $metadata, bool $rightnow = false) {
        $filepath = $file->copy_content_to_temp('local_ai_ingest','local_ai');
        if ($filepath === false) {
            return;
        }
        $params = [
            'vectorstorelocation' => $this->data_dir,
            'documentpath' => $filepath,
            'documentmetadata' => json_encode($metadata)
        ];
        $task = run_python_command::instance($this->id, "add_to_vectorstore.py", $params);
        if ($rightnow) {
            $task->execute(); 
        } else {
            debugging("Queuing add_document task");
            \core\task\manager::queue_adhoc_task($task);
        }
    }

    public function generate_command($command, $parameters) {
        global $CFG;
        //$cmd = "source {$CFG->dirroot}/local/ai/cli/python/bin/activate; python3 " . $CFG->dirroot . "/local/ai/cli/python/" . $command;
        $cmd = "{$CFG->dirroot}/local/ai/cli/python/bin/python3 " . $CFG->dirroot . "/local/ai/cli/python/" . $command;
        $cmd = escapeshellcmd($cmd);
        $cmd = $cmd ." ";
        foreach($parameters as $key => $value) {
            $cmd .= "--{$key}=" . escapeshellarg($value) ." ";
        }
        return $cmd;
    }
    private function get_verify_dir() {
        global $CFG;
        if (is_null($this->verifyid)) {
            $this->verifyid = uniqid("local_ai_verify");
        }

        $fullpath = $CFG->dataroot ."/local_ai/";
        if ($this->verification) {
            // if verificationdir starts with / 
            if ($this->verificationdir[0] != '/') {
                $fullpath .= $this->verificationdir . "/{$this->verifyid}";
            } else {
                $fullpath = $this->verificationdir. "/{$this->verifyid}";
            }
        }

        if (file_exists($fullpath)) {
            return $fullpath;
        } else {
            return make_writable_directory($fullpath);
        }

        return false;
    }
    private $verifyid = null;
    protected function write_verify($name, $content) {
        if ($this->verification) {
            $dir = $this->get_verify_dir();
            file_put_contents($dir . "/". $name, $content);
        }
    } 

    public function execute_command($cmd, $execute = false) {
        $execute = true;
        $result = 0;
        $tempdir = make_temp_directory('ai');
        $output = null;
        $returncode = null;
        chdir($tempdir);
        if ($execute) {
            mtrace($cmd);
            $result = exec($cmd, $output, $returncode);
        } else {
            mtrace("Would have executed: ".$cmd);
        }
        if (is_array($output)) {
            return implode(" ", $output);
        }
        return $output;
    }

    public function chat($message) {
        // We're going to use the OpenAI API format to get chat completions.
        if ($this->verification) {
            debugging("Verification files output to ". $this->get_verify_dir());
        }
        $baseurl = "http://host.docker.internal:11434/v1/"; // Ollama running locally just now, but will become a config setting or delegated to an AI provider class.
        $apikey = "ollama"; // Temporary, needed for OpenAI but not ollama.
        
        $url = "{$baseurl}chat/completions" ;
        $headers = [
            'Content-Type' => "application/json"
        ];
        $this->write_verify('prompt.txt', $message);
        $context = $this->get_context($message);
        echo("context size: " . strlen($context));
        $globalcontext = get_config('local_ai','globalcontext');
        $messages = [
            [
                "role"=>"system",
                "content"=>"{$globalcontext}"
            ],
            [
                "role"=>"system",
                "content"=>"You may be provided with additional information after this. You should use this information first."
            ]
        ];
        if (!empty($context)) {
            $this->write_verify("context.txt", $context);
            $messages[] = [
                "role"=>"system",
                "content" => $context
            ];
        };
        $messages[] = [
                "role" => "user",
                "content" => $message
        ];
        $data = [
            'model' => "llama2",
            'messages' => $messages
        ];
        
        $postdata = json_encode($data);
        echo("Query size: " . strlen($postdata)."\n");
        $this->write_verify('prompt.json', $postdata);
        $result = \download_file_content(
            $url,
            $headers,
            $postdata
        );
        $this->write_verify("response.json", $result);
        if (is_null($result)) {
            // Something went wrong with completion / chat
            return "Sorry something went wrong";
        }
        $result = json_decode($result);
        if (!isset($result->choices)) {
            echo "no answer";
            return;
        }
        $responses = $result->choices;
        $answer = "";
        foreach($responses as $response) {
            $answer .= $response->message->content ."\n";
        }
        $this->write_verify("answer.txt", $message. "\n\n" .$answer);
        echo $answer;
    }
    /**
     * Fetch the context for the LLM. 
     */
    protected function get_context(string $usermessage): string {
        $context = "";
        $params = [
            'vectorstorelocation' => $this->data_dir,
            'query' => $usermessage
        ];
        $command = self::generate_command('query_vectorstore.py', $params);
        if (debugging()) {
            echo "Executing \"{$command}\"\n";
        }
        $context = $this->execute_command($command, true);

        return $context;
    }
}
