<?php

namespace MultidevCreationAlert;

class MultidevCreationAlert
{
    public $dashboard_link;
    public $environment_link;
    public $secrets;
    public $site_env;
    public $site_id;
    public $site_name;
    public $user_email;
    public $workflow_id;

    public function __construct()
    {
        if ($this->isPantheon() && $this->isQuicksilver()) {
            $this->setQuicksilverVariables();
            $this->getSecrets();
        }
    }
    
    /**
    * Set Quicksilver variables from ENV data.
    *
    * @return void
    */
    private function setQuicksilverVariables()
    {
        $this->site_env  = $this->getPantheonEnvironment();
        $this->site_id   = $this->getPantheonSiteId();
        $this->site_name = $this->getPantheonSiteName();
        $this->user_email = $_POST['user_email'];
        $this->workflow_id = $_POST['trace_id'];

        $this->dashboard_link = "https://dashboard.pantheon.io/sites/$this->site_id#$this->site_env";
        $this->environment_link = "https://$this->site_env-$this->site_name.pantheonsite.io";
    }

    /**
    * Get the Pantheon site name.
    *
    * @return string|null
    */
    private function getPantheonSiteName(): ?string
    {
        return ! empty($_ENV['PANTHEON_SITE_NAME']) ? $_ENV['PANTHEON_SITE_NAME'] : null;
    }

    /**
    * Get the Pantheon siteId.
    *
    * @return string|null
    */
    private function getPantheonSiteId(): ?string
    {
        return ! empty($_ENV['PANTHEON_SITE']) ? $_ENV['PANTHEON_SITE'] : null;
    }
    /**
    * Get the Pantheon environment.
    *
    * @return string|null
    */
    private function getPantheonEnvironment(): ?string
    {
        return ! empty($_ENV['PANTHEON_ENVIRONMENT']) ? $_ENV['PANTHEON_ENVIRONMENT'] : null;
    }

    private function isPantheon()
    {
        if ($this->getPantheonSiteName() !== null && $this->getPantheonEnvironment() !== null) {
            return true;
        }
        die('No Pantheon environment detected.');
    }

    /**
    * Check if in the Quicksilver context.
    *
    * @return bool|void
    */
    private function isQuicksilver()
    {
        if ($this->isPantheon() && ! empty($_POST['wf_type'])) {
            return true;
        }
        die('No Pantheon Quicksilver environment detected.');
    }

    /**
     * Load secrets from secrets file.
     */
    public function getSecrets()
    {
        if (empty($this->secrets)) {
            $secretsFile = $_ENV['HOME'] . 'files/private/secrets.json';
            if (!file_exists($secretsFile)) {
                die('No secrets file found. Aborting!');
            }
            $secretsContents = file_get_contents($secretsFile);
            $secrets = json_decode($secretsContents, true);
            if (!$secrets) {
                die('Could not parse json in secrets file. Aborting!');
            }
            $this->secrets = $secrets;
        }
        return $this->secrets;
    }

    /**
     * @param string $key Key in secrets that must exist.
     * @return mixed|void
     */
    public function getSecret(string $key)
    {
        $secrets = $this->getSecrets();
        $missing = array_diff([$key], array_keys($secrets));
        if (!empty($missing)) {
            die('Missing required keys in json secrets file: ' . implode(',', $missing) . '. Aborting!');
        }
        return $secrets[$key];
    }
}
