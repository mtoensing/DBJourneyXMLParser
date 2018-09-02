<?php

spl_autoload_register(function ($class_name) {
	include 'classes/' . $class_name . '.class.php';
});


$AlexaAbfahrtenSkill = new AlexaAbfahrtenSkill();

$AlexaAbfahrtenSkill->setup = array(
	'ApplicationID'       => 'amzn1.ask.skill.6f5d7f58-b0c7-4ef4-96c8-dd28418c96ba',
	// From your ALEXA developer console like: 'amzn1.ask.skill.45c11234-123a-1234-ffaa-1234567890a'
	'CheckSignatureChain' => true,
	// make sure the request is a true amazonaws api call
	'ReqValidTime'        => 60,
	// Time in Seconds a request is valid
	'AWSaccount'          => '',
	//If this is != empty the specified session->user->userId is required. This is usefull for account bound private only skills
	'validIP'             => false,
	// Limit allowed requests to specified IPv4, set to FALSE to disable the check.
	'LC_TIME'             => "de_DE"
	// We use german Echo so we want our date output to be german
);

$AlexaAbfahrtenSkill->getRequest();
$AlexaAbfahrtenSkill->remove_from_output = array( ', Hannover', 'Hannover,' );
$AlexaAbfahrtenSkill->replace_in_output  = array( 'STB', 'Stadtbahn ' );
$AlexaAbfahrtenSkill->setOrigin( "Hannover, Kafkastrasse" );
$AlexaAbfahrtenSkill->setDestination( "Wettbergen, Hannover" );
$AlexaAbfahrtenSkill->setShowDestinationOnly( true );
$AlexaAbfahrtenSkill->getXML();
$AlexaAbfahrtenSkill->fillJourneys();

echo $AlexaAbfahrtenSkill->getAlexaJSONResponse();





