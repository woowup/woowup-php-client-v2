<?php
namespace WoowUpV2\Endpoints;

/**
 *
 */
class Users extends Endpoint
{
    protected static $DEFAULT_IDENTITY = [
        'document'    => '',
        'email'       => '',
        'service_uid' => '',
        'telephone'   => ''
    ];

    /**
     * Identifiers enabled in the account's multi-id, read once per instance. false means not read
     * yet; null means the read failed, and that is memoized too (see multiIdentifiers()).
     *
     * @var array|null|false
     */
    private $multiIdentifiers = false;

    public function __construct($host, $apikey, \GuzzleHttp\ClientInterface $http = null)
    {
        parent::__construct($host, $apikey, $http);
    }

    public function update(\WoowUpV2\Models\UserModel $user)
    {
        if (!$user->validate()) {
            throw new \Exception("User is not valid", 1);
        }

        $response = $this->put($this->host . '/multiusers', $this->multiusersBody($user));

        return $response->getStatusCode() == Endpoint::HTTP_OK || $response->getStatusCode() == Endpoint::HTTP_CREATED;
    }

    public function updateAsync(\WoowUpV2\Models\UserModel $user)
    {
        if (!$user->validate()) {
            throw new \Exception("User is not valid", 1);
        }

        return $this->putAsync($this->host . '/multiusers', $this->multiusersBody($user));
    }

    /**
     * Body of PUT /multiusers. The API answers 400 "No identificator received, please check
     * multi-id settings" when the body carries service_uid and the account does not have it in its
     * multi-id, so in that case the key is dropped from the serialized payload. The model itself is
     * not touched: callers keep using it after the update (cache hashes, identity, logs).
     *
     * Only service_uid is dropped. When the multi-id cannot be read, the body goes as before.
     *
     * @return \WoowUpV2\Models\UserModel|array
     */
    protected function multiusersBody(\WoowUpV2\Models\UserModel $user)
    {
        $identifiers = $this->multiIdentifiers();

        if ($identifiers === null || in_array('service_uid', $identifiers, true)) {
            return $user;
        }

        $body = $user->jsonSerialize();
        unset($body['service_uid']);

        return $body;
    }

    /**
     * Keys of the account's multidentificator_settings, read with GET /account the first time it
     * is needed. A failed read (exception, non-200, payload without the settings or with them
     * empty) is memoized as null for the life of the instance: the request already goes through
     * Endpoint's retries for 429/502/503, and retrying on every update would add a GET to each PUT
     * of a whole run while /account is failing. Without the settings nothing is dropped, so the PUT behaves as before.
     *
     * @return array|null
     */
    protected function multiIdentifiers()
    {
        if ($this->multiIdentifiers !== false) {
            return $this->multiIdentifiers;
        }

        try {
            $settings = (new Account($this->host, $this->apikey, $this->http))->multidentificatorSettings();
        } catch (\Throwable $e) {
            error_log('[WARNING] [WoowupClient] Could not read the multi-id settings from GET /account: ' . $e->getMessage());
            $settings = null;
        }

        $this->multiIdentifiers = is_array($settings) ? array_map('strval', array_keys($settings)) : null;

        return $this->multiIdentifiers;
    }

    public function create(\WoowUpV2\Models\UserModel $user)
    {
        if (!$user->validate()) {
            throw new \Exception("User is not valid", 1);
        }

        $response = $this->post($this->host . '/users', $user);

        return $response->getStatusCode() == Endpoint::HTTP_OK || $response->getStatusCode() == Endpoint::HTTP_CREATED;
    }

    public function createAsync(\WoowUpV2\Models\UserModel $user)
    {
        if (!$user->validate()) {
            throw new \Exception("User is not valid", 1);
        }

        return $this->postAsync($this->host . '/users', $user);
    }

    public function exist($identity)
    {
        $identity = array_merge(self::$DEFAULT_IDENTITY, $identity);

        $response = $this->get($this->host . '/multiusers/exist', $identity);

        if ($response->getStatusCode() == Endpoint::HTTP_OK) {
            $data = json_decode($response->getBody());

            return isset($data->payload) && isset($data->payload->exist) && $data->payload->exist;
        }

        return false;
    }

    public function existAsync($identity)
    {
        $identity = array_merge(self::$DEFAULT_IDENTITY, $identity);

        return $this->getAsync($this->host . '/multiusers/exist', $identity);
    }

    protected function encode($uid)
    {
        return urlencode(base64_encode($uid));
    }

    public function find($identity)
    {
        $identity = array_merge(self::$DEFAULT_IDENTITY, $identity);
        $response = $this->get($this->host . '/multiusers/find', $identity);

        if ($response->getStatusCode() == Endpoint::HTTP_OK) {
            $data = json_decode($response->getBody());

            if (isset($data->payload)) {
                return \WoowUpV2\Models\UserModel::createFromJson(json_encode($data->payload));
            }
        }

        return false;
    }

    public function search($page = 0, $limit = 25, $search = '', $include = '{}', $exclude = '{}', $segmentId = '')
    {
        $response = $this->get($this->host . '/users/', [
            'page'   => $page,
            'limit'  => $limit,
            'search' => $search,
            'include' => $include,
            'exclude' => $exclude,
            'segment_id' => $segmentId,
        ]);

        if ($response->getStatusCode() == Endpoint::HTTP_OK) {
            $data = json_decode($response->getBody());

            if (isset($data->payload)) {
                $result = [];
                foreach ($data->payload as $value) {
                    $result[] = \WoowUpV2\Models\UserModel::createFromJson(json_encode($value));
                }

                return $result;
            }
        }

        return false;
    }

    /**
     * Merges two customers of the same account: `from` is soft deleted and everything it owns moves
     * to `to`. There is no way back: the purchases are reassigned without a pointer to where they
     * came from, so a merge cannot be undone even from the database.
     *
     * The identity is NOT completed with $DEFAULT_IDENTITY, unlike find() and exist(). Sending an
     * empty document next to an email leaves the resolution of `from` to the backend, and whoever
     * asks for a merge has to be the one deciding which identifier the pair is matched by.
     *
     * Returns the decoded body instead of a boolean because the status code cannot tell a merge from
     * a no-op: when either side does not resolve, the API answers 200 with an empty body and merges
     * nothing. Callers have to check `code` for 'ok'.
     *
     * @param array $from identity of the customer that is deleted
     * @param array $to   identity of the customer that survives
     *
     * @return array the decoded response body, empty when there was none
     */
    public function merge(array $from, array $to)
    {
        $response = $this->post($this->host . '/multiusers/merge', [
            'from' => $from,
            'to'   => $to,
        ]);

        if ($response->getStatusCode() != Endpoint::HTTP_OK) {
            return [];
        }

        $body = json_decode((string) $response->getBody(), true);

        return is_array($body) ? $body : [];
    }

    public function getUserTransactions($identity, $concept = '', $from = '', $to = '')
    {
        $identity = array_merge(self::$DEFAULT_IDENTITY, $identity);
        $params   = array_merge($identity, [
            'concept' => $concept,
            'from'    => $from,
            'to'      => $to,
        ]);

        $response = $this->get($this->host . '/multiusers/transactions', $params);

        if ($response->getStatusCode() == Endpoint::HTTP_OK) {
            $data = json_decode($response->getBody());

            if (isset($data->payload)) {
                return $data->payload;
            }
        }

        return false;
    }

    public function addPoints($identity, $concept, $points, $description)
    {
        $identity = array_merge(self::$DEFAULT_IDENTITY, $identity);
        $params   = array_merge($identity, [
            'concept'     => $concept,
            'points'      => $points,
            'description' => $description,
        ]);

        $response = $this->post($this->host . '/multiusers/points', $params);

        return $response->getStatusCode() == Endpoint::HTTP_OK || $response->getStatusCode() == Endpoint::HTTP_CREATED;
    }

    public function createFamilyMember(\WoowUpV2\Models\FamilyMemberModel $member, $identity)
    {
        $identity = array_merge(self::$DEFAULT_IDENTITY, $identity);

        if ($member->validate()) {
            $response = $this->post($this->host.'/multiusers/members', $member, $identity);

            return $response->getStatusCode() == Endpoint::HTTP_OK || $response->getStatusCode() == Endpoint::HTTP_CREATED;
        }

        throw new \Exception("Family member is not valid", 1);
    }

    public function updateFamilyMember(\WoowUpV2\Models\FamilyMemberModel $member, $identity)
    {
        $identity = array_merge(self::$DEFAULT_IDENTITY, $identity);

        if ($member->validate()) {
            $response = $this->put($this->host.'/multiusers/members', $member, $identity);

            return $response->getStatusCode() == Endpoint::HTTP_OK || $response->getStatusCode() == Endpoint::HTTP_CREATED;
        }

        throw new \Exception("Family member is not valid", 1);
    }

    public function removeTags($userAppId, $tags)
    {
        if (is_array($tags)) {
            $tags = implode(',', $tags);
        }

        $params = [
            'remove_tags' => $tags
        ];

        $response = $this->put($this->host . '/users/' . $userAppId, $params);

        return $response->getStatusCode() == Endpoint::HTTP_OK;
    }
}
