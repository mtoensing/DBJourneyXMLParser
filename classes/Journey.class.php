<?php

/**
 *
 * Marc Tönsing 2018
 *
 * Class Journey
 */

class Journey {

	public $product = '';
	public $destination = '';
	public $arrival_timestamp = '';
	public $delay = '';


	public function getArrivalFullDate() {
		$arrival_date = date( 'l dS \o\f F Y H:i:s', $this->arrival_timestamp );

		return $arrival_date;
	}

	public function getRelativeMinutes() {
		$timestampt_diff = $this->arrival_timestamp - time();
		$minutes         = floor( $timestampt_diff / 60 );

		return $minutes;
	}

	/**
	 * @param string $delay
	 */
	public function setDelay( $delay ) {
		if ( is_numeric( $delay ) ) {
			$this->delay = intval( $delay );
		} else {
			$this->delay = 0;
		}
	}

	/**
	 * @param string $product
	 */
	public function setProduct( $product ) {
		$this->product = $product;
	}

	/**
	 * @param string $destination
	 */
	public function setDestination( $destination ) {
		$this->destination = $destination;
	}

	/**
	 * @param string $arrival_timestamp
	 */
	public function setArrivalTimestamp( $arrival_timestamp ) {
		$this->arrival_timestamp = $arrival_timestamp;
	}

	public function fixProduct() {
		$product       = trim( $this->product );
		$product       = substr( $product, 0, strpos( $product, "#" ) );
		$this->product = preg_replace( '/\s+/', '', $product );

	}

}

?>