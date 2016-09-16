<?php
/**
 * Interfax
 *
 * (C) InterFAX, 2016
 *
 * @package interfax/interfax
 * @author Interfax <dev@interfax.net>
 * @author Mike Smith <mike.smith@camc-ltd.co.uk>
 * @copyright Copyright (c) 2016, InterFAX
 * @license MIT
 */
namespace Interfax;

class OutboundTest extends BaseTest
{
    public function test_completed()
    {
        $client = $this->getMockBuilder('Interfax\Client')
            ->disableOriginalConstructor()
            ->setMethods(['get'])
            ->getMock();

        $response = [['id' =>  12, 'senderCSID' => 'Interfax'],['id' => 14, 'senderCSID' => 'Interfax']];

        $client->expects($this->once())
            ->method('get')
            ->with('/outbound/faxes/completed')
            ->will($this->returnValue($response));

        $fax1 = $this->getMockBuilder('Interfax\Outbound\Fax')->disableOriginalConstructor()->getMock();
        $fax2 = $this->getMockBuilder('Interfax\Outbound\Fax')->disableOriginalConstructor()->getMock();
        $factory = $this->getFactory([
            [$fax1, [$client, 12, $response[0]]],
            [$fax2, [$client, 14, $response[1]]]
        ]);

        $outbound = new Outbound($client, $factory);

        $res = $outbound->completed(['12', '14']);

        $this->assertEquals([$fax1, $fax2], $res);
    }

    public function test_recent()
    {
        $client = $this->getMockBuilder('Interfax\Client')
            ->disableOriginalConstructor()
            ->setMethods(['get'])
            ->getMock();

        $response = [['id' =>  21]];

        $client->expects($this->once())
            ->method('get')
            ->with('/outbound/faxes', ['limit' => 5])
            ->will($this->returnValue($response));

        $fax = $this->getMockBuilder('Interfax\Outbound\Fax')->disableOriginalConstructor()->getMock();

        $factory = $this->getFactory([[$fax, [$client, 21, $response[0]]]]);

        $outbound = new Outbound($client, $factory);

        $res = $outbound->recent(['limit' => 5]);

        $this->assertEquals([$fax], $res);
    }

    public function test_resend()
    {
        $fax = $this->getMockBuilder('Interfax\Outbound\Fax')
            ->disableOriginalConstructor()
            ->setMethods(['resend'])
            ->getMock();

        $resent_fax = $this->getMockBuilder('Interfax\Outbound\Fax')->disableOriginalConstructor()->getMock();

        $fax->expects($this->once())
            ->method('resend')
            ->will($this->returnValue($resent_fax));

        $factory = $this->getFactory([$fax]);

        $client = $this->getMockBuilder('Interfax\Client')->disableOriginalConstructor()->getMock();

        $outbound = new Outbound($client, $factory);

        $this->assertEquals($resent_fax, $outbound->resend(34552));
    }

    public function test_resend_with_new_number()
    {
        $fax = $this->getMockBuilder('Interfax\Outbound\Fax')
            ->disableOriginalConstructor()
            ->setMethods(['resend'])
            ->getMock();

        $fax_number = '+112122323';

        $resent_fax = $this->getMockBuilder('Interfax\Outbound\Fax')->disableOriginalConstructor()->getMock();

        $fax->expects($this->once())
            ->method('resend')
            ->with($fax_number)
            ->will($this->returnValue($resent_fax));

        $factory = $this->getFactory([$fax]);

        $client = $this->getMockBuilder('Interfax\Client')->disableOriginalConstructor()->getMock();

        $outbound = new Outbound($client, $factory);

        $this->assertEquals($resent_fax, $outbound->resend(34552, $fax_number));
    }
}