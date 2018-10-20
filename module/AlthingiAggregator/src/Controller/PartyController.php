<?php
namespace AlthingiAggregator\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use AlthingiAggregator\Lib\Consumer\ConsumerAwareInterface;
use AlthingiAggregator\Lib\Provider\ProviderAwareInterface;
use AlthingiAggregator\Extractor\Party;

class PartyController extends AbstractActionController implements ConsumerAwareInterface, ProviderAwareInterface
{
    use ConsoleHelper;

    public function findPartyAction()
    {
        $this->queryAndSave(
            'http://www.althingi.is/altext/xml/thingflokkar',
            'thingflokkar',
            '//þingflokkar/þingflokkur',
            new Party()
        );
    }
}
