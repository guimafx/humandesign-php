<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Domain\BirthData;
use App\Services\HumanDesignCalculator;

final class ChartController extends BaseController
{
    public function calculate(): string
    {
        $birth = BirthData::fromArray($this->request->all());

        /** @var HumanDesignCalculator $calculator */
        $calculator = $this->service(HumanDesignCalculator::class);

        $chart = $calculator->calculate($birth);

        return $this->view->render('chart', [
            'title' => 'Resultado',
            'birth' => $birth,
            'chart' => $chart,
        ]);
    }
}
