<?php
use OlivierBarbier\Zorm\Zobject\Account;

class VCRTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @vcr example
     */
    public function test()
    {
        $account = Account::find(1);

        $this->assertEquals(1, $account->Id);
    }
}
