<?php
defined('IN_YZMPHP') or exit('Access Denied'); 

class {JOB}{

    private $params;

    public function __construct($params = []) {
        $this->params = $params;
    }

	/**
	 * Execute the job.
	 *
	 * @return void
	 */
	public function handle() {
		
	}
}