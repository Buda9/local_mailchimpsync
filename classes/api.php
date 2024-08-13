<?php
namespace local_mailchimpsync;

defined('MOODLE_INTERNAL') || die();

class api {
    private $api_key;
    private $api_endpoint = 'https://<dc>.api.mailchimp.com/3.0';

    public function __construct() {
        $this->api_key = get_config('local_mailchimpsync', 'apikey');
        error_log("MailChimp API Key: " . substr($this->api_key, 0, 5) . '...');  // Logira prvih 5 znakova API ključa
        $this->api_endpoint = str_replace('<dc>', substr($this->api_key, strpos($this->api_key, '-')+1), $this->api_endpoint);
        error_log("MailChimp API Endpoint: " . $this->api_endpoint);
    }

    public function get_lists() {
        if (empty($this->api_key)) {
            throw new \moodle_exception('apierror', 'local_mailchimpsync', '', 'API key not set.');
        }
        $response = $this->request('GET', 'lists?fields=lists.id,lists.name,lists.web_id&count=1000');
        
        if (isset($response['lists']) && is_array($response['lists'])) {
            $lists = array();
            foreach ($response['lists'] as $list) {
                $lists[$list['id']] = [
                    'name' => $list['name'],
                    'web_id' => $list['web_id']
                ];
            }
            mtrace("API returned lists: " . print_r($lists, true));
            return $lists;
        } else {
            mtrace("Unexpected response format from MailChimp API: " . print_r($response, true));
            return array();
        }
    }

    public function add_list_member($list_id, $email, $merge_fields = null, $status = 'subscribed') {
        $data = [
            'email_address' => $email,
            'status' => $status,
        ];
        if ($merge_fields) {
            $data['merge_fields'] = $merge_fields;
        }
        return $this->request('POST', "lists/$list_id/members", $data);
    }
    
    public function update_list_member($list_id, $subscriber_hash, $merge_fields = null, $status = 'subscribed') {
        $data = [
            'status' => $status,
        ];
        if ($merge_fields) {
            $data['merge_fields'] = $merge_fields;
        }
        return $this->request('PATCH', "lists/$list_id/members/$subscriber_hash", $data);
    }

    public function delete_list_member($list_id, $subscriber_hash) {
        return $this->request('DELETE', "lists/$list_id/members/$subscriber_hash");
    }

    private function request($method, $endpoint, $data = null) {
        $url = $this->api_endpoint . '/' . $endpoint;

        mtrace("API Request: $method $url");
        if ($data !== null) {
            mtrace("Request Data: " . print_r($data, true));
        }

        $options = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: apikey ' . $this->api_key
            ],
            CURLOPT_VERBOSE => true
        ];
    
        if ($data !== null) {
            $options[CURLOPT_POSTFIELDS] = json_encode($data);
        }
    
        $ch = curl_init();
        curl_setopt_array($ch, $options);
    
        // Dodajte ovo za logiranje
        error_log("MailChimp API Request: $method $url");
        if ($data !== null) {
            error_log("MailChimp API Request Data: " . json_encode($data));
        }
    
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);

        mtrace("API Response Code: $http_code");
        mtrace("API Response: $response");
        if ($error) {
            mtrace("API Error: $error");
        }
    
        // Dodajte ovo za logiranje
        error_log("MailChimp API Response Code: $http_code");
        error_log("MailChimp API Response: $response");
        if ($error) {
            error_log("MailChimp API Error: $error");
        }
    
        curl_close($ch);
    
        if ($http_code >= 200 && $http_code < 300) {
            return json_decode($response, true);
        } else {
            throw new \moodle_exception('apierror', 'local_mailchimpsync', '', 'HTTP Code: ' . $http_code . ' Response: ' . $response);
        }
    }

    public function add_or_update_list_member($list_id, $email, $merge_fields = null, $status = 'subscribed') {
        $subscriber_hash = md5(strtolower($email));
        $data = [
            'email_address' => $email,
            'status_if_new' => $status,
        ];
        if (!empty($merge_fields)) {
            $data['merge_fields'] = $merge_fields;
        }
        return $this->request('PUT', "lists/{$list_id}/members/{$subscriber_hash}", $data);
    }

    public function get_merge_fields($list_id) {
        try {
            $result = $this->request('GET', "lists/{$list_id}/merge-fields");
            return isset($result['merge_fields']) ? $result['merge_fields'] : [];
        } catch (\moodle_exception $e) {
            debugging('Error fetching merge fields: ' . $e->getMessage(), DEBUG_DEVELOPER);
            return [];
        }
    }

    public function validate_list($list_id) {
        try {
            $result = $this->request('GET', "lists/{$list_id}");
            return isset($result['id']);
        } catch (\moodle_exception $e) {
            mtrace("Error validating list: " . $e->getMessage());
            return false;
        }
    }
}