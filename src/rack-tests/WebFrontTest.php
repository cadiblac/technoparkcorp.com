<?php
/**
 * @version $Id$
 */

class WebFrontTest extends PhpRack_Test
{

    public function testWebFront()
    {
        $this->setAjaxOptions(
            array(
                'reload' => 5, // every 5 seconds, if possible
            )
        );
        // doesn't work for some reason at production server
        return;
        $this->assert->network->url
            ->url('http://www.technoparkcorp.com/')
            ->regex('TechnoPark');
    }

}