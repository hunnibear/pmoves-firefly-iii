<?php

namespace FireflyIII\Repositories\PiggyBank;

use FireflyIII\Models\PiggyBank;
use Illuminate\Support\Collection;

/**
 * Interface PiggyBankRepositoryInterface
 *
 * @package FireflyIII\Repositories\PiggyBank
 */
interface PiggyBankRepositoryInterface
{

    /**
     * @return Collection
     */
    public function getPiggyBanks();

    /**
     * @param PiggyBank $piggyBank
     *
     * @return Collection
     */
    public function getEvents(PiggyBank $piggyBank);

    /**
     * @param PiggyBank $piggyBank
     * @param           $amount
     *
     * @return bool
     */
    public function createEvent(PiggyBank $piggyBank, $amount);

    /**
     * @param PiggyBank $piggyBank
     *
     * @return Collection
     */
    public function getEventSummarySet(PiggyBank $piggyBank);

    /**
     * @param PiggyBank $piggyBank
     *
     * @return bool
     */
    public function destroy(PiggyBank $piggyBank);

    /**
     * Set all piggy banks to order 0.
     *
     * @return void
     */
    public function reset();

    /**
     *
     * set id of piggy bank.
     *
     * @param int $id
     * @param int $order
     *
     * @return void
     */
    public function setOrder($id, $order);


    /**
     * @param array $data
     *
     * @return PiggyBank
     */
    public function store(array $data);

    /**
     * @param PiggyBank $piggyBank
     * @param array     $data
     *
     * @return PiggyBank
     */
    public function update(PiggyBank $piggyBank, array $data);
}
