<?php

declare(strict_types=1);

namespace App\Services;

use App\Domain\BirthData;

final class HumanDesignCalculator
{
    private ActivationMapper $mapper;

    private const CHANNELS = [
        [1,8],[2,14],[3,60],[4,63],[5,15],[6,59],
        [7,31],[9,52],[10,20],[10,34],[10,57],
        [11,56],[12,22],[13,33],[16,48],[17,62],
        [18,58],[19,49],[20,34],[20,57],[21,45],
        [23,43],[24,61],[25,51],[26,44],[27,50],
        [28,38],[29,46],[30,41],[32,54],[34,57],
        [35,36],[37,40],[39,55],[42,53],[47,64],
    ];

    private const GATE_CENTER = [
        1=>'G',2=>'G',3=>'Sacral',4=>'Ajna',5=>'Sacral',6=>'Solar Plexus',
        7=>'G',8=>'Throat',9=>'Sacral',10=>'G',11=>'Ajna',12=>'Throat',
        13=>'G',14=>'Sacral',15=>'G',16=>'Throat',17=>'Ajna',18=>'Spleen',
        19=>'Root',20=>'Throat',21=>'Ego',22=>'Solar Plexus',23=>'Throat',
        24=>'Ajna',25=>'G',26=>'Ego',27=>'Sacral',28=>'Spleen',29=>'Sacral',
        30=>'Solar Plexus',31=>'Throat',32=>'Spleen',33=>'Throat',34=>'Sacral',
        35=>'Throat',36=>'Solar Plexus',37=>'Solar Plexus',38=>'Root',
        39=>'Root',40=>'Ego',41=>'Root',42=>'Sacral',43=>'Ajna',44=>'Spleen',
        45=>'Throat',46=>'G',47=>'Ajna',48=>'Spleen',49=>'Solar Plexus',
        50=>'Spleen',51=>'Ego',52=>'Root',53=>'Root',54=>'Root',
        55=>'Solar Plexus',56=>'Throat',57=>'Spleen',58=>'Root',
        59=>'Sacral',60=>'Root',61=>'Head',62=>'Throat',63=>'Head',64=>'Head',
    ];

    public function __construct(
        private readonly EphemerisProviderInterface $ephemeris
    ) {
        $this->mapper = new ActivationMapper();
    }

    public function calculate(BirthData $birth): array
    {
        $utc = $birth->utc();

        // Núcleo mínimo. Os demais corpos entram no próximo passo.
        $bodies = ['SUN', 'NORTH_NODE'];
        $personality = [];

        foreach ($bodies as $body) {
            $longitude = $this->ephemeris->longitude(
                $utc,
                $body,
                $birth->latitude,
                $birth->longitude
            );

            $personality[$body] = $this->mapper->map($body, $longitude);
        }

        $activeGates = [];

        foreach ($personality as $activation) {
            $activeGates[$activation->gate] = true;
        }

        [$activeChannels, $definedCenters] = $this->detectDefinition($activeGates);

        return [
            'metadata' => [
                'ephemeris' => $this->ephemeris->name(),
                'reliable' => $this->ephemeris->isReliable(),
                'warning' => $this->ephemeris->isReliable()
                    ? null
                    : 'Resultado demonstrativo. Não usar como mapa astronômico real.',
            ],
            'birth' => [
                'local' => $birth->localDateTime->format(DATE_ATOM),
                'utc' => $utc->format(DATE_ATOM),
                'timezone' => $birth->timezone,
            ],
            'personality' => array_map(
                static fn ($activation) => $activation->toArray(),
                $personality
            ),
            'active_gates' => array_map('intval', array_keys($activeGates)),
            'active_channels' => $activeChannels,
            'defined_centers' => array_values(array_keys($definedCenters)),
            'status' => 'foundation',
        ];
    }

    private function detectDefinition(array $activeGates): array
    {
        $channels = [];
        $centers = [];

        foreach (self::CHANNELS as [$gateA, $gateB]) {
            if (!isset($activeGates[$gateA], $activeGates[$gateB])) {
                continue;
            }

            $channels[] = "{$gateA}-{$gateB}";
            $centers[self::GATE_CENTER[$gateA]] = true;
            $centers[self::GATE_CENTER[$gateB]] = true;
        }

        return [$channels, $centers];
    }
}
