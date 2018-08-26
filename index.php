<?php

/**
 * Class DBJourneyXMLParser
 */

class Journey {
	public $product = '';
	public $destination = '';
	public $arrival_timestamp = '';
	public $delay = '';

	/**
	 * @param string $delay
	 */
	public function setDelay( $delay ) {
		$this->delay = $delay;
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

}

class DBJourneyXMLParser {
	private $version = '1.0';

	const BAHN_ENDPOINT_URL = 'https://reiseauskunft.bahn.de//bin/stboard.exe/dn?rt=1&time=actual&start=yes&boardType=dep&L=vs_java3&input=';
	const MOCK = true;
	const DEBUG = true;
	const LOCALCOPY = true;

	public $data = '';
	public $journeys = array();
	public $journeys_xml ='';
	public $origin = '';
	public $destination = '';

	/**
	 * DBJourneyXMLParser
	 */
	public function __construct( $origin, $destination ) {

			$this->setOrigin($origin);
			$this->setDestination($destination);


			$this->getXML();
			$this->fillJourneys();



			var_dump($this->journeys);

			//$this->getDirections()
			//echo $this->getInfo();

	}




	/**
	 * @param string $journeys_xml
	 */
	public function setJourneysXml( $journeys_xml ) {
		$this->journeys_xml = $journeys_xml;
	}

	/**
	 * @param bool|string $data
	 */
	public function setData( $data ) {
		$this->data = $data;
	}

	/**
	 * @param string $origin
	 */
	public function setOrigin( $origin ) {
		$this->origin = $origin;
	}

	/**
	 * @param string $destination
	 */
	public function setDestination( $destination ) {
		$this->destination = $destination;
	}


	public function getXML(){
		$url  = DBJourneyXMLParser::BAHN_ENDPOINT_URL . urlencode( $this->origin );

		if ( DBJourneyXMLParser::MOCK == true ) {
			$url = 'mock.txt';
		}

		if ( DBJourneyXMLParser::LOCALCOPY == true ) {
			$url = 'https://traintime.marc.tv/data.txt';
		}

		$data = file_get_contents( $url );

		if ($data === false ) {
			die("xml data is empty");

		}

		$this->setData($data);

		$this->convertBAHNXML();
	}


	/**
	 * fix BAHN XML
	 */
	public function convertBAHNXML() {
		$xml            = '<?xml version="1.0" encoding="UTF-8" standalone="no" ?><Journeys>' . $this->data . '</Journeys>';
		$this->journeys_xml = simplexml_load_string( $xml );
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
			$arrival_in_minutes = ( $timestamp_arrival - $now ) / 60;

			return round( $arrival_in_minutes ) . ' NOW: ' . date( 'l dS \o\f F Y H:i:s', $now ) . 'TSNOW: ' . $now;
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

	/**
	 * @param string $journeys
	 */
	public function setJourneys( $journeys ) {
		$this->journeys[] = $journeys;
	}


	public function fillJourneys() {

		foreach ( $this->journeys_xml as $journey_xml ) {

			$journey = new Journey();

			$arrvial_timestamp = strtotime( $journey_xml['fpTime'] );
			$journey->setArrivalTimestamp( $arrvial_timestamp );

			$destination = $journey_xml['targetLoc'];
			$journey->setDestination( $destination );

			$product = $journey_xml['prod'];
			$journey->setProduct( $product );

			$delay = $journey_xml['delay'];
			$journey->setDelay( $delay );

			$this->setJourneys($journey);

		}

	}

	public function getInfo() {
		$html = '';
		foreach ( $this->journeys as $journey ) {
			$html .= '<ul>';
			if ( $journey['targetLoc'] == $this->destination ) {
				if ( $this->isNotGone( $journey['fpTime'] ) ) {

					$html .= '<li>PROD: ' . $this->fixProduct( $journey['prod'] ) . '</li>';
					$html .= '<li>RELATIVE: in ' . $this->getRelativeTimeInMinutes( $journey['fpTime'] ) . '  </li>';
					$html .= '<li>FULLDATE: ' . $this->getFullDate( $journey['fpTime'] ) . '</li>';
					$html .= '<li>DISPLAYTIME ' . $journey['fpTime'] . '</li>';
					$html .= '<li>DELAY: ' . $journey['delay'] . '</li>';
				}
			}
			$html .= '</ul>';
		}

		return $html;
	}

}

$test = new DBJourneyXMLParser( "Hannover, Kafkastrasse", "Wettbergen, Hannover" );