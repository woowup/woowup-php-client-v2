<?php
namespace WoowUpV2\Endpoints;

/**
 *
 */
class Branches extends Endpoint
{
    public function __construct($host, $apikey)
    {
        parent::__construct($host, $apikey);
    }

    public function update($branchName, \WoowUpV2\Models\BranchModel $branch)
    {
        if (!$branch->validate()) {
            throw new \Exception("Branch is not valid", 1);
        }

        $response = $this->put($this->host . '/branches/' . base64_encode($branchName), $branch);

        return $response->getStatusCode() == Endpoint::HTTP_OK || $response->getStatusCode() == Endpoint::HTTP_CREATED;
    }

    public function create(\WoowUpV2\Models\BranchModel $branch)
    {
        if (!$branch->validate()) {
            throw new \Exception("Branch is not valid", 1);
        }

        $response = $this->post($this->host . '/branches', $branch);

        return $response->getStatusCode() == Endpoint::HTTP_OK || $response->getStatusCode() == Endpoint::HTTP_CREATED;
    }

    public function find($branchId)
    {
        $response = $this->get($this->host . '/branches/' . $branchId, []);

        if ($response->getStatusCode() == Endpoint::HTTP_OK) {
            $data = json_decode($response->getBody());

            if (isset($data->payload)) {
                return \WoowUpV2\Models\BranchModel::createFromJson(json_encode($data->payload));
            }
        }

        return false;
    }

    public function search($page = 0, $limit = 10)
    {
        $response = $this->get($this->host . '/branches/', [
            'page'   => $page,
            'limit'  => $limit,
        ]);

        if ($response->getStatusCode() == Endpoint::HTTP_OK) {
            $data = json_decode($response->getBody());

            if (isset($data->payload)) {
                $result = [];
                foreach ($data->payload as $value) {
                    $result[] = \WoowUpV2\Models\BranchModel::createFromJson(json_encode($value));
                }

                return $result;
            }
        }

        return false;
    }
}