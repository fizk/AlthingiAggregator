<?php

/**
 * Created by PhpStorm.
 * User: einarvalur
 * Date: 30/03/2016
 * Time: 8:08 PM
 */

namespace AlthingiAggregatorTest\Controller;

use AlthingiAggregator\Lib\Consumer\TestConsumer;
use AlthingiAggregator\Lib\Provider\TestProvider;
use Zend\Test\PHPUnit\Controller\AbstractConsoleControllerTestCase;

class PartyControllerTest extends AbstractConsoleControllerTestCase
{
    /** @var  TestConsumer */
    private $consumer;

    /** @var  TestProvider */
    private $provider;

    public function setUp()
    {
        $this->setApplicationConfig(
            include __DIR__ . '/../../../../config/application.config.php'
        );
        parent::setUp();

        $this->provider = new TestProvider();
        $this->consumer = new TestConsumer();

        $serviceManager = $this->getApplicationServiceLocator();
        $serviceManager->setAllowOverride(true);
        $serviceManager->setService('Provider', $this->provider);
        $serviceManager->setService('Consumer', $this->consumer);
    }

    public function testPartyRouter()
    {
        $this->provider->addDocument(
            'http://www.althingi.is/altext/xml/thingflokkar',
            $this->getDomDocument()
        );

        $this->dispatch('load:party');

        $this->assertControllerClass('PartyController');
        $this->assertActionName('find-party');
    }

    public function testPartyData()
    {
        $this->provider->addDocument(
            'http://www.althingi.is/altext/xml/thingflokkar',
            $this->getDomDocument()
        );

        $this->dispatch('load:party');

        $consumerStoredData = $this->consumer->getObjects();

        $this->assertCount(1, $consumerStoredData);
        $this->assertArrayHasKey('thingflokkar/27', $consumerStoredData);

    }

    public function getDomDocument()
    {
        $source = '<?xml version="1.0" encoding="UTF-8"?>
            <þingflokkar>
              <þingflokkur id="27">
                <heiti>Alþýðuflokkur</heiti>
                <skammstafanir>
                  <stuttskammstöfun>A</stuttskammstöfun>
                  <löngskammstöfun>Alþfl.</löngskammstöfun>
                </skammstafanir>
                <tímabil>
                  <fyrstaþing>27</fyrstaþing>
                  <síðastaþing>120</síðastaþing>
                </tímabil>
              </þingflokkur>
            </þingflokkar>';
        $dom = new \DOMDocument();
        $dom->loadXML($source);
        return $dom;
    }
}