<?php

use OlivierBarbier\Zorm\Zobject\Account;

class VCRTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @vcr example
     */
    public function test_find()
    {
        $id = '2c92c0f851ce2d790151cf55799a79eb';

        $account = Account::find($id);

        $this->assertEquals($id, $account->Id);
        $this->assertEquals('Ceci est un compte de test', $account->Name);
    }

    /**
     * @vcr example
     */
    public function test_where()
    {
        $accounts = Account::where('Status', '=', 'Active')->get();

        $this->assertEquals(16, $accounts->count());
    }

    /**
     * @vcr example
     */
    public function test_create()
    {
        $account = Account::create([
            'Name'         => 'John', 'Currency' => 'EUR',
            'BillCycleDay' => 1, 'Status' => 'Draft',
        ]);

        $this->assertEquals('2c92c0f851e800b80151e815710a1192', $account->Id);
        $this->assertEquals('John', $account->Name);
        $this->assertEquals('EUR', $account->Currency);
        $this->assertEquals('1', $account->BillCycleDay);
        $this->assertEquals('Draft', $account->Status);
    }

    /**
     * @vcr example
     */
    public function test_save()
    {
        $account = Account::find('2c92c0f851e800b80151e815710a1192');

        $account->status = 'Active';

        $save = $account->save(['Status']);

        $this->assertTrue((bool) $save->result->Success);
    }
}
