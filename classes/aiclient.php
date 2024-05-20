<?php
namespace local_ai;
require_once($CFG->libdir.'/filelib.php');
use local_ai\AiException;
/**
 * Base client for AI providers that uses simple http request.
 */
class AIClient extends \curl implements LoggerAwareInterface {
    use LoggerAwareTrait;
    /**
     * @var AIProvider
     */
    private $provider;
    public function __construct(
        AIProvider $provider
    ) {
        $this->provider = $provider;
        $this->setLogger($provider->get_logger());
        $settings = [];
        parent::__construct($settings);
        $this->setHeader('Authorization: Bearer ' . $this->provider->get('apikey'));
        $this->setHeader('Content-Type: application/json');
    }



    public function get_embeddings_url(): string {
        return $this->provider->get('baseurl') . $this->provider->get('embeddingsurl');
    }

    public function get_chat_completions_url(): string {
        return $this->provider->get('baseurl') . $this->provider->get('completionsurl');
    }

    /**
     * @param $messages
     * @return array String array of each line of the AI's Response.
     * @throws \coding_exception
     */
    public function chat($messages) {
        $params = [
            "model" => $this->provider->get('completionmodel'),
            "messages" => $messages
        ];
        $params = json_encode($params);
        $rawresult = $this->post($this->get_chat_completions_url(), $params);
        $this->logger->info("Response rescieved from AI provider: {name}", ['name' => $this->provider->get('name')]);
        $jsonresult = json_decode($rawresult);
        if (isset($jsonresult->error)) {
            $this->logger->error("Error: " . $jsonresult->error->message . ":". print_r($messages, true));
            throw new AiException("Error: " . $jsonresult->error->message . ":". print_r($messages, true));
            //return "Error: " . $jsonresult->error->message . ":". print_r($messages, true);
        }
        $result = [];
        if (isset($jsonresult->choices)) {
            $this->logger->info("Starting Processing completions");
            $result = $this->convert_chat_completion($jsonresult->choices);
            $this->logger->info("Finished completions");
        }
        if (isset($jsonresult->usage)) {
            $this->logger->info("Updating token usage");
            $usage = $jsonresult->usage;
            $updated = [
                $this->provider->increment_prompt_usage($usage->prompt_tokens),
                $this->provider->increment_completion_tokens($usage->completion_tokens),
                $this->provider->increment_total_tokens($usage->total_tokens)
            ];
            $this->logger->info("Request Tokens-{prompt_tokens}. Total tokens: {total_tokens}", (array)$usage);
            $this->logger->info("Tokens-Prompt:{$updated[0]}, Completion:{$updated[1]}, Total:{$updated[2]}");
        }
        //$this->logger->info($result);
        return $result;
    }

    /**
     * Converts an OpenAI Type of response to an array of sentences
     * @param $completion
     * @return array
     */
    protected function convert_chat_completion($choices) {
        $responses = [];
        foreach($choices as $choice) {
            array_push($responses, $choice->message);
        }
        return $responses;
    }
    /**
     * @param $document
     * @return array
     */
    public function embed_query($content): array {
        // Send document to back end and return the vector
        $usedptokens = $this->provider->get_usage('prompt_tokens');
        $totaltokens = $this->provider->get_usage('total_tokens');
        // mtrace("Prompt tokens: $usedptokens. Total tokens: $totaltokens");
        $content = $content ?? "";   // Fix "null" content to be "empty" string.
        $params = [
            "input" => htmlentities($content), // TODO need to do some length checking here!
            "model" => $this->provider->get('embeddingmodel'),
        ];
        $params = json_encode($params);
        $embeddingsurl = $this->get_embeddings_url();
        $this->logger->info("Embeddings URL: " . $embeddingsurl);
        $urlisblocked = $this->check_securityhelper_blocklist($embeddingsurl);
        if (!is_null($urlisblocked)) {
            $this->logger->warning($urlisblocked);
            throw new \moodle_exception("{$embeddingsurl} is blocked by policy");
        }
        $rawresult = $this->post($embeddingsurl, $params);

        $result = json_decode($rawresult, true);
        if (is_null($result)) {
            throw new \moodle_exception('Failed to decode response from AI provider: {$a}', "", "", $rawresult);
        } else if (isset($result['error'])) {
            throw new \moodle_exception($result['error']['message']);
        }
        if (isset($result['usage'])) {
            $usage = $result['usage'];
            $updated = [
                $this->provider->increment_prompt_usage($usage['prompt_tokens']),
                $this->provider->increment_total_tokens($usage['total_tokens'])
            ];
            $this->logger->info("Used Prompt tokens: {prompt_tokens}. Total tokens: {total_tokens}", $usage);
            $this->logger->info("Tokens-Prompt:{$updated[0]}, Total:{$updated[1]}");
        }
        if (isset($result['data'])) {
            $data = $result['data'];
            foreach ($data as $d) {
                if ($d['object'] == "embedding") {
                    return $d['embedding'];
                }
            }
        }
        $this->logger->warning('Somehow about to return with no vector data!');

        return [];
    }
    public function embed_documents(array $documents) {
        // Go send the documents off to a back end and then return array of each document's vectors.
        // But for the minute generate an array of fake vectors of a specific length.
        $embeddings = [];
        foreach($documents as $doc) {
            $embeddings[] = $this->embed_query($doc);
        }
        return $embeddings;
    }
    public function fake_embed(array $documents) {
        $vectors = [];
        foreach ($documents as $document) {
            $vectors[] = $this->fake_vector(1356);
        }
        return $vectors;
    }
    public function complete($query) {


    }
    private function fake_vector($length) {
        $vector = [];
        for ($i = 0; $i < $length; $i++) {
            $vector[] = rand(0, 1);
        }
        return $vector;
    }



}
