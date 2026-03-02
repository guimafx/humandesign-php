<?php


/***
 * 
 

$hd = new HumanDesign();

$chart = $hd->calculate([
    'year'=>1987,
    'month'=>5,
    'day'=>14,
    'hour'=>13.5
]);

$have = $hd->generateHAVE($chart);




print_r($have);



 * **/

class HumanDesign
{
    private array $planets = [
        'SUN','EARTH','MOON','MERCURY','VENUS','MARS',
        'JUPITER','SATURN','URANUS','NEPTUNE','PLUTO',
        'NORTH_NODE','SOUTH_NODE'
    ];

    private float $GATE_SIZE = 5.625;     // 360 / 64
    private float $LINE_SIZE = 0.9375;    // gate / 6

    private array $activeGates = [];
    private array $definedCenters = [];

    /* =====================================================
       PUBLIC API
    ===================================================== */

    public function calculate(array $birth): array
    {
        $jd = $this->toJulian(
            $birth['year'],
            $birth['month'],
            $birth['day'],
            $birth['hour']
        );

        // PERSONALITY
        $personality = $this->calculatePlanets($jd);

        // DESIGN (88° solares antes)
        $designJD = $this->findDesignDate($jd, $personality['SUN']['degree']);
        $design = $this->calculatePlanets($designJD);

        // ativa gates
        $this->activateGates($personality, 'personality');
        $this->activateGates($design, 'design');

        $this->detectCenters();

        return [
            'personality' => $personality,
            'design' => $design,
            'type' => $this->detectType(),
            'centers' => $this->definedCenters
        ];
    }

    /* =====================================================
       HAVE / RAVE MANDALA
    ===================================================== */

    public function generateHAVE(array $chart): array
    {
        $mandala = [];

        foreach ($chart['personality'] as $planet => $data) {
            $mandala[] = [
                'planet' => $planet,
                'gate' => $data['gate'],
                'line' => $data['line'],
                'angle' => $data['degree']
            ];
        }

        foreach ($chart['design'] as $planet => $data) {
            $mandala[] = [
                'planet' => 'D-'.$planet,
                'gate' => $data['gate'],
                'line' => $data['line'],
                'angle' => $data['degree']
            ];
        }

        return $mandala;
    }

    /* =====================================================
       PLANET CALCULATION
    ===================================================== */

    private function calculatePlanets(float $jd): array
    {
        $result = [];

        foreach ($this->planets as $planet) {

            // 🔥 ADAPTER — substituir por Swiss Ephemeris
            $degree = $this->ephemeris($jd, $planet);

            $mapping = $this->degreeToHD($degree);

            $result[$planet] = array_merge([
                'degree' => $degree
            ], $mapping);
        }

        return $result;
    }

    /* =====================================================
       DEGREE → HD STRUCTURE
    ===================================================== */

    private function degreeToHD(float $degree): array
    {
        $gate = floor($degree / $this->GATE_SIZE) + 1;

        $withinGate = fmod($degree, $this->GATE_SIZE);
        $line = floor($withinGate / $this->LINE_SIZE) + 1;

        $colorSize = $this->LINE_SIZE / 6;
        $toneSize  = $colorSize / 6;
        $baseSize  = $toneSize / 5;

        $withinLine = fmod($withinGate, $this->LINE_SIZE);
        $color = floor($withinLine / $colorSize) + 1;

        $withinColor = fmod($withinLine, $colorSize);
        $tone = floor($withinColor / $toneSize) + 1;

        $withinTone = fmod($withinColor, $toneSize);
        $base = floor($withinTone / $baseSize) + 1;

        return compact('gate','line','color','tone','base');
    }

    /* =====================================================
       DESIGN DATE (88° SOLAR OFFSET)
    ===================================================== */

    private function findDesignDate(float $jd, float $sunDegree): float
    {
        $target = $sunDegree - 88;
        if ($target < 0) $target += 360;

        $testJD = $jd;

        for ($i=0;$i<500;$i++) {

            $sun = $this->ephemeris($testJD,'SUN');

            if (abs($sun - $target) < 0.01)
                break;

            $testJD -= 0.1;
        }

        return $testJD;
    }

    /* =====================================================
       GATE ACTIVATION
    ===================================================== */

    private function activateGates(array $data, string $type)
    {
        foreach ($data as $planet=>$info) {
            $this->activeGates[$info['gate']] = true;
        }
    }

    /* =====================================================
       CENTERS (simplificado)
    ===================================================== */

    private function detectCenters()
    {
        $gateCenters = [
            34 => 'Sacral',
            20 => 'Throat',
            10 => 'G'
        ];

        foreach ($this->activeGates as $gate=>$v) {
            if(isset($gateCenters[$gate]))
                $this->definedCenters[$gateCenters[$gate]] = true;
        }
    }

    /* =====================================================
       TYPE DETECTION
    ===================================================== */

    private function detectType(): string
    {
        if(isset($this->definedCenters['Sacral']))
            return "Generator";

        if(empty($this->definedCenters))
            return "Reflector";

        return "Projector";
    }

    /* =====================================================
       JULIAN DATE
    ===================================================== */

    private function toJulian($y,$m,$d,$hour): float
    {
        if ($m <= 2) {
            $y--;
            $m += 12;
        }

        $A = floor($y/100);
        $B = 2 - $A + floor($A/4);

        return floor(365.25*($y+4716))
            + floor(30.6001*($m+1))
            + $d + $B - 1524.5 + $hour/24;
    }

    /* =====================================================
       EPHEMERIS ADAPTER (SUBSTITUIR)
    ===================================================== */

    private function ephemeris(float $jd, string $planet): float
    {
        /*
         Aqui você conecta Swiss Ephemeris:

         swe_calc_ut($jd, SE_SUN, ...)

         POR ENQUANTO:
         mock determinístico para testes
        */

        return fmod(($jd * crc32($planet)) / 1000000, 360);
    }
}