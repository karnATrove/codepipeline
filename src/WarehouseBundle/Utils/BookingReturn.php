<?php
namespace WarehouseBundle\Utils;

class BookingReturn
{
	/**
	 * Container aware.
	 */
	private $container;

	/**
	 * Make this utility container-aware (adding availaiblity of doctrine for example)
	 *
	 * @param      <type>  $container  The container
	 */
	public function __construct($container) {
		$this->container = $container;
	}

    /**
     * Listing of available return statuses.
     *
     * @return     array  Available return product statuses.
     */
    public function returnStatusList() {
        return array(
            0 => 'Deleted',
            1 => 'Pending',
            2 => 'Accepted',
        );
    }

    /**
     * Return a human readable format of the return status.
     *
     * @param      integer  $status  The return status identifier
     *
     * @return     string  Human readable text.
     */
    public function returnStatusName($status) {
        return isset($this->returnStatusList()[$status]) ? $this->returnStatusList()[$status] : 'Unknown';
    }

    /**
     * Listing of available return product statuses.
     *
     * @return     array  Available return product statuses.
     */
    public function returnProductStatusList() {
        return array(
            1 => 'Mint',
            2 => 'Good',
            3 => 'Fair',
            4 => 'Broken',
        );
    }

    /**
     * Return a human readable format of the return product status.
     *
     * @param      integer  $status  The return product status identifier
     *
     * @return     string  Human readable text.
     */
    public function returnProductStatusName($status) {
        return isset($this->returnProductStatusList()[$status]) ? $this->returnProductStatusList()[$status] : 'Unknown';
    }

}