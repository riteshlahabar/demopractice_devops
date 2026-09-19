<?php

namespace App\Services\Admin\People;

use App\Contracts\Admin\People\PersonSummaryBuilderContract;
use App\Contracts\Admin\People\PersonSummaryContract;
use App\Data\Admin\People\PersonSummary;
use App\Models\User;

final class PersonSummaryService implements PersonSummaryContract
{
    /** @var array<int, PersonSummaryBuilderContract> */
    private array $builders;

    public function __construct(DealerSummaryBuilder $dealer, CustomerSummaryBuilder $customer, SalesmanSummaryBuilder $salesman)
    {
        $this->builders = [$dealer, $customer, $salesman];
    }

    public function for(User $person): PersonSummary
    {
        foreach ($this->builders as $builder) {
            if ($builder->role() === $person->role) {
                return $builder->build($person);
            }
        }

        return PersonSummary::empty();
    }
}
