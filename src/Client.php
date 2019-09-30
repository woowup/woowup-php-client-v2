<?php
namespace WoowUpV2;

use WoowUpV2\Endpoints\AbandonedCarts;
use WoowUpV2\Endpoints\Blacklist;
use WoowUpV2\Endpoints\Branches;
use WoowUpV2\Endpoints\Events;
use WoowUpV2\Endpoints\Products;
use WoowUpV2\Endpoints\Purchases;
use WoowUpV2\Endpoints\UserEvents;
use WoowUpV2\Endpoints\Users;
use WoowUpV2\Endpoints\Account;
use WoowUpV2\Endpoints\Multiusers;
use WoowUpV2\Endpoints\Stats;

class Client
{
    protected $http;

    /**
     * Purchases endpoint wrapper
     * @var WoowUpV2\Endpoints\Purchases
     */
    public $purchases;

    /**
     * Users endpoint wrapper
     * @var WoowUpV2\Endpoints\Users
     */
    public $users;

    /**
     * Products endpoint wrapper
     * @var WoowUpV2\Endpoints\Products
     */
    public $products;

    /**
     * Abandoned Cart endpoint wrapper
     * @var WoowUpV2\Endpoints\AbandonedCarts
     */
    public $abadonedCarts;

    /**
     * Events endpoint wrapper
     * @var WoowUpV2\Endpoints\Events
     */
    public $events;

    /**
     * UserEvents endpoint wrapper
     * @var WoowUpV2\Endpoints\UserEvents
     */
    public $userEvents;

    /**
     * Branches endpoint wrapper
     * @var WoowUpV2\Endpoints\Branches
     */
    public $branches;
  
    /**
     * Account endpoint wrapper
     * @var WoowUpV2\Endpoints\Account
     */
    public $account;

    /**
     * Blacklist endpoint wrapper
     * @var WoowUpV2\Endpoints\Blacklist
     */
    public $blacklist;

    /**
     * Integration stats endpoint wrapper
     * @var  WoowUpV2\Endpoints\Stats
     */
    public $stats;

    /**
     * Client constructor
     * @param string $apikey Account's apikey
     * @param string $host   WoowUp API host
     * @param string $version   WoowUp API version
     */
    public function __construct($apikey, $host = 'https://api.woowup.com', $version = 'apiv3')
    {
        $url = $host . '/' . $version;

        $this->purchases      = new Purchases($url, $apikey);
        $this->users          = new Users($url, $apikey);
        $this->products       = new Products($url, $apikey);
        $this->abandonedCarts = new AbandonedCarts($url, $apikey);
        $this->events         = new Events($url, $apikey);
        $this->userEvents     = new UserEvents($url, $apikey);
        $this->branches       = new Branches($url, $apikey);
        $this->account        = new Account($url, $apikey);
        $this->blacklist      = new Blacklist($url, $apikey);
        $this->stats          = new Stats($url, $apikey);
    }
}
