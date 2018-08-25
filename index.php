<?php

/**
 * Class DBJourneyXMLParser
 */
class DBJourneyXMLParser {
	const BAHN_ENDPOINT_URL = 'https://reiseauskunft.bahn.de//bin/stboard.exe/dn?rt=1&time=actual&start=yes&boardType=dep&L=vs_java3&input=';
	const MOCK = true;
	private $version = '1.0';
	public $data = '';
	public $journeys = '';
	public $station = '';
	public $destination = '';

	/**
	 * DBJourneyXMLParser
	 */
	public function __construct( $station, $destination ) {

		if ( $station == '' OR $destination == '' ) {
			throw new My_Exception( "station can't be empty" );

		} else {
			$this->station     = $station;
			$this->destination = $destination;
			//$url               = DBJourneyXMLParser::BAHN_ENDPOINT_URL . urlencode( $station );

			if ( DBJourneyXMLParser::MOCK ) {
				$url = 'mock.txt';
			}

			$this->data = file_get_contents( $url );

			if ( $this->data === false ) {
				throw new My_Exception( "data empty" );

			}

			$this->fixBAHNXML();
			//$this->getDirections()
			$this->getInfo();
		}
	}

	/**
	 * fix BAHN XML
	 */
	public function fixBAHNXML() {
		$xml            = '<?xml version="1.0" encoding="UTF-8" standalone="no" ?><Journeys>' . $this->data . '</Journeys>';
		$this->journeys = simplexml_load_string( $xml );
	}

	public function fixProduct( $product ) {
		$product       = trim( $product );
		$product       = substr( $product, 0, strpos( $product, "#" ) );
		$clean_product = preg_replace( '/\s+/', '', $product );

		return $clean_product;
	}

	public function getDirections() {
		$directions = array();

		foreach ( $this->journeys as $journey ) {
			$directions[] = $journey['targetLoc']->__toString();
		}

		print_r( array_unique( $directions ) );
	}

	public function getFullDate( $arrival_time ) {

		$timestamp_arrival = strtotime( $arrival_time );

		return date( 'l dS \o\f F Y H:i:s', $timestamp_arrival );
	}

	public function getRelativeTimeInMinutes( $arrival_time ) {
		$timestamp_arrival = strtotime( $arrival_time );
		$now               = strtotime( 'now' );

		if ( $timestamp_arrival > $now ) {
			$arrival_in_minutes = ( $timestamp_arrival - $now  ) / 60;

			return round( $arrival_in_minutes );
		} else {
			return false;
		}
	}

	public function isNotGone( $arrival_time ) {
		$timestamp_arrival = strtotime( $arrival_time );
		$now               = strtotime( 'now' );

		if ( $timestamp_arrival > $now ) {
			return true;
		} else {
			return false;
		}
	}

	public function getInfo() {
		foreach ( $this->journeys as $journey ) {
			if ( $journey['targetLoc'] == $this->destination ) {
				if ( $this->isNotGone( $journey['fpTime'] ) ) {

					echo  $this->fixProduct( $journey['prod'] ) .
					     ' RELATIVE: ' .
					     $this->getRelativeTimeInMinutes( $journey['fpTime'] ) .
					     ' FULLDATE: ' .
					     $this->getFullDate( $journey['fpTime'] ) .
					     ' DISPLAYTIME ' .
					     ( $journey['fpTime'] ) .
					     ' DELAY: ' .
					     $journey['delay'] .
					     '<br>';
				}
			}

		}
	}

}

$test = new DBJourneyXMLParser( "Hannover, Kafkastrasse", "Wettbergen, Hannover" );