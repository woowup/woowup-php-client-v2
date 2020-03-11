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
    ];

    public function __construct($host, $apikey)
    {
        parent::__construct($host, $apikey);
    }

    public function update(\WoowUpV2\Models\UserModel $user)
    {
        if (!$user->validate()) {
            throw new \Exception("User is not valid", 1);
        }

        $response = $this->put($this->host . '/multiusers', $user);

        return $response->getStatusCode() == Endpoint::HTTP_OK || $response->getStatusCode() == Endpoint::HTTP_CREATED;
    }

    public function updateAsync(\WoowUpV2\Models\UserModel $user)
    {
        if (!$user->validate()) {
            throw new \Exception("User is not valid", 1);
        }

        return $this->putAsync($this->host . '/multiusers', $user);
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

    public function search($page = 0, $limit = 25, $search = '')
    {
        $response = $this->get($this->host . '/users/', [
            'page'   => $page,
            'limit'  => $limit,
            'search' => $search,
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

    public function getUserTransactions($identity, $concept = '')
    {
        $identity = array_merge(self::$DEFAULT_IDENTITY, $identity);
        $params   = array_merge($identity, [
            'concept' => $concept,
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
}
