<?php

declare(strict_types=1);

namespace App\Services;

use App\Domain\BirthData;

final class HumanDesignCalculator
{
    private ActivationMapper $mapper;
    private readonly DesignDateCalculator $designDateCalculator;
    private readonly ChannelCalculator $channelCalculator;
    private readonly CenterCalculator $centerCalculator;
    private readonly DefinitionCalculator $definitionCalculator;
    private readonly TypeCalculator $typeCalculator;
    private readonly AuthorityCalculator $authorityCalculator;
    private readonly ProfileCalculator $profileCalculator;
    private readonly IncarnationCrossCalculator $incarnationCrossCalculator;

    private const EPHEMERIS_BODIES = [
        'SUN',
        'MOON',
        'NORTH_NODE',
        'MERCURY',
        'VENUS',
        'MARS',
        'JUPITER',
        'SATURN',
        'URANUS',
        'NEPTUNE',
        'PLUTO',
    ];

    private const BODY_ORDER = [
        'SUN',
        'EARTH',
        'MOON',
        'NORTH_NODE',
        'SOUTH_NODE',
        'MERCURY',
        'VENUS',
        'MARS',
        'JUPITER',
        'SATURN',
        'URANUS',
        'NEPTUNE',
        'PLUTO',
    ];

    public function __construct(
        private readonly EphemerisProviderInterface $ephemeris,
        ?DesignDateCalculator $designDateCalculator = null,
        ?ChannelCalculator $channelCalculator = null,
        ?CenterCalculator $centerCalculator = null,
        ?DefinitionCalculator $definitionCalculator = null,
        ?TypeCalculator $typeCalculator = null,
        ?AuthorityCalculator $authorityCalculator = null,
        ?ProfileCalculator $profileCalculator = null,
        ?IncarnationCrossCalculator $incarnationCrossCalculator = null
    ) {
        $this->mapper = new ActivationMapper();
        $this->designDateCalculator = $designDateCalculator
            ?? new DesignDateCalculator($ephemeris);
        $this->channelCalculator = $channelCalculator ?? new ChannelCalculator();
        $this->centerCalculator = $centerCalculator
            ?? new CenterCalculator($this->channelCalculator);
        $this->definitionCalculator = $definitionCalculator
            ?? new DefinitionCalculator($this->channelCalculator);
        $this->typeCalculator = $typeCalculator
            ?? new TypeCalculator($this->channelCalculator);
        $this->authorityCalculator = $authorityCalculator ?? new AuthorityCalculator();
        $this->profileCalculator = $profileCalculator ?? new ProfileCalculator();
        $this->incarnationCrossCalculator = $incarnationCrossCalculator
            ?? new IncarnationCrossCalculator();
    }

    public function calculate(BirthData $birth): array
    {
        $utc = $birth->utc();

        $personality = $this->calculateActivations(
            $utc,
            $birth->latitude,
            $birth->longitude
        );
        $designDate = $this->designDateCalculator->calculate($utc);
        $design = $this->calculateActivations(
            $designDate,
            $birth->latitude,
            $birth->longitude
        );

        $activeGates = [];

        foreach ([$personality, $design] as $side) {
            foreach ($side as $activation) {
                $activeGates[$activation->gate] = true;
            }
        }

        $activeGates = array_map('intval', array_keys($activeGates));
        $activeChannels = $this->channelCalculator->calculate($activeGates);
        $definedCenters = $this->centerCalculator->calculate($activeChannels);

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
            'design_date' => $designDate->format(DATE_ATOM),
            'personality' => array_map(
                static fn ($activation) => $activation->toArray(),
                $personality
            ),
            'design' => array_map(
                static fn ($activation) => $activation->toArray(),
                $design
            ),
            'active_gates' => $activeGates,
            'active_channels' => $activeChannels,
            'defined_centers' => $definedCenters,
            'type' => $this->typeCalculator->calculate($definedCenters, $activeChannels),
            'authority' => $this->authorityCalculator->calculate($definedCenters),
            'definition' => $this->definitionCalculator->calculate($definedCenters, $activeChannels),
            'profile' => $this->profileCalculator->calculate(
                $personality['SUN']->line,
                $design['SUN']->line
            ),
            'incarnation_cross' => $this->incarnationCrossCalculator->calculate(
                $personality['SUN']->gate,
                $personality['EARTH']->gate,
                $design['SUN']->gate,
                $design['EARTH']->gate
            ),
            'status' => 'foundation',
        ];
    }

    private function calculateActivations(
        \DateTimeImmutable $utc,
        ?float $latitude,
        ?float $longitude
    ): array {
        $longitudes = [];

        foreach (self::EPHEMERIS_BODIES as $body) {
            $longitudes[$body] = $this->ephemeris->longitude(
                $utc,
                $body,
                $latitude,
                $longitude
            );
        }

        $longitudes['EARTH'] = $this->oppositeLongitude($longitudes['SUN']);
        $longitudes['SOUTH_NODE'] = $this->oppositeLongitude(
            $longitudes['NORTH_NODE']
        );
        $activations = [];

        foreach (self::BODY_ORDER as $body) {
            $activations[$body] = $this->mapper->map($body, $longitudes[$body]);
        }

        return $activations;
    }

    private function oppositeLongitude(float $longitude): float
    {
        return fmod($longitude + 180.0, 360.0);
    }

}
