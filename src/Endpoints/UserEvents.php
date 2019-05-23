<?php
namespace WoowUpV2\Endpoints;

/**
 *
 */
class UserEvents extends Endpoint
{
    public function __construct($host, $apikey)
    {
        parent::__construct($host, $apikey);
    }

    public function create(\WoowUpV2\Models\UserEventModel $event)
    {
		if (!$event->validate()) {
			throw new \Exception("User Event is not valid", 1);
		}

        $response = $this->post($this->host . '/user-events', $event);

        return $response->getStatusCode() == Endpoint::HTTP_OK || $response->getStatusCode() == Endpoint::HTTP_CREATED;
    }
}
