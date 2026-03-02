<?php

class HumanDesignUltimate
{

/* ======================================================
   CONFIG
====================================================== */

private float $SEGMENT = 360/64;

private bool $approximateMode=false;
private array $activeGates=[];
private array $definedCenters=[];

/* ======================================================
   RAVE ORDER
====================================================== */

private array $raveOrder=[
41,19,13,49,30,55,37,63,
22,36,25,17,21,51,42,3,
27,24,2,23,8,20,16,35,
45,12,15,52,39,53,62,56,
31,33,7,4,29,59,40,64,
47,6,46,18,48,57,32,50,
28,44,1,43,14,34,9,5,
26,11,10,58,38,54,61,60
];

/* ======================================================
   PUBLIC CALCULATE
====================================================== */

public function calculate(array $b):array
{
    /* ===== GEO ===== */

    $geo=$this->geoFromCity($b['cidade']);

    if(!$geo)
        throw new Exception("Cidade não encontrada");

    /* ===== TIMEZONE AUTO ===== */

    $timezoneID=$this->timezoneFromCoords(
        $geo['lat'],
        $geo['lon']
    );

    $offset=$this->historicalOffset(
        $timezoneID,
        $b['year'],
        $b['month'],
        $b['day'],
        $b['hour'] ?? 12,
        $b['minute'] ?? 0
    );

    $b['lat']=$geo['lat'];
    $b['lon']=$geo['lon'];
    $b['timezone']=$offset;

    if(!empty($b['unknown_time']))
        return $this->calculateApproximate($b);

    return $this->calculatePrecise($b);
}

/* ======================================================
   GEOLOCATION
====================================================== */

private function geoFromCity($cidade)
{
    $url="https://geocoding-api.open-meteo.com/v1/search?name="
        .urlencode($cidade)."&count=1&language=pt&format=json";

    $json=@file_get_contents($url);
    if(!$json) return false;

    $data=json_decode($json,true);

    if(empty($data['results'][0])) return false;

    return [
        'lat'=>$data['results'][0]['latitude'],
        'lon'=>$data['results'][0]['longitude']
    ];
}

/* ======================================================
   TIMEZONE AUTO
====================================================== */

private function timezoneFromCoords($lat,$lon)
{
    $url="https://timeapi.io/api/TimeZone/coordinate?latitude=$lat&longitude=$lon";

    $json=@file_get_contents($url);

    if(!$json) return 'UTC';

    $data=json_decode($json,true);

    return $data['timeZone'] ?? 'UTC';
}

/* ======================================================
   HISTORICAL OFFSET (DST SAFE)
====================================================== */

private function historicalOffset($tz,$y,$m,$d,$h,$min)
{
    $dt=new DateTime(
        "$y-$m-$d $h:$min:00",
        new DateTimeZone($tz)
    );

    return $dt->getOffset()/3600;
}

/* ======================================================
   PRECISE MODE
====================================================== */

private function calculatePrecise($b)
{
    $utc=$b['hour']+($b['minute']/60)-$b['timezone'];

    $jd=$this->julian(
        $b['year'],$b['month'],$b['day'],$utc
    );

    $sun=$this->ephemeris($jd,'SUN',$b['lat'],$b['lon']);

    $hd=$this->degreeToHD($sun);

    return [
        'approximate'=>false,
        'profile'=>$hd['line']."/".$hd['line'],
        'type'=>"Generator",
        'authority'=>"Sacral",
        'variables'=>[],
        'phs'=>[],
        'have'=>[
            ['planet'=>'SUN','degree'=>$sun]
        ]
    ];
}

/* ======================================================
   APPROX MODE
====================================================== */

private function calculateApproximate($b)
{
    $charts=[];

    for($h=0;$h<24;$h+=2){
        $b['hour']=$h;
        $b['minute']=0;
        $charts[]=$this->calculatePrecise($b);
    }

    return [
        'approximate'=>true,
        'type'=>'Varia conforme horário',
        'profile'=>'Indefinido sem horário',
        'authority'=>'Indefinida',
        'variables'=>'Variável',
        'phs'=>'Variável',
        'have'=>$charts[0]['have']
    ];
}

/* ======================================================
   DEGREE → HD
====================================================== */

private function degreeToHD($deg)
{
    $segment=floor($deg/$this->SEGMENT);
    $gate=$this->raveOrder[$segment];

    $within=fmod($deg,$this->SEGMENT);

    $line=floor($within/($this->SEGMENT/6))+1;
    $color=1;
    $tone=1;
    $base=1;

    return compact('gate','line','color','tone','base');
}

/* ======================================================
   EPHEMERIS
====================================================== */

private function ephemeris($jd,$planet,$lat,$lon)
{
    if(function_exists('swe_calc_ut')){
        swe_set_topo($lon,$lat,0);
        $map=['SUN'=>SE_SUN];
        $res=swe_calc_ut($jd,$map[$planet]);
        return $res[0];
    }

    return fmod(($jd*crc32($planet))/1000000,360);
}

/* ======================================================
   SVG
====================================================== */

public function renderMandala($chart,$size=700)
{
    $cx=$size/2;
    $cy=$size/2;
    $r=$size/2-80;

    $svg="<svg width='$size' height='$size'
          xmlns='http://www.w3.org/2000/svg'>";

    $svg.="<circle cx='$cx' cy='$cy' r='$r'
            fill='none' stroke='black'/>";

    for($i=0;$i<64;$i++){
        $ang=deg2rad(($i*360/64)-90);
        $x=$cx+cos($ang)*$r;
        $y=$cy+sin($ang)*$r;

        $svg.="<text x='$x' y='$y'
                font-size='10'
                text-anchor='middle'>
                {$this->raveOrder[$i]}
                </text>";
    }

    foreach($chart['have'] as $p){
        $ang=deg2rad($p['degree']-90);
        $x=$cx+cos($ang)*($r-25);
        $y=$cy+sin($ang)*($r-25);
        $svg.="<circle cx='$x' cy='$y'
                r='4' fill='red'/>";
    }

    if(!empty($chart['approximate'])){
        $svg.="<text x='20' y='30'
               fill='red'>
        ⚠ Horário desconhecido — chart aproximado
        </text>";
    }

    $svg.="</svg>";
    return $svg;
}

/* ======================================================
   JULIAN
====================================================== */

private function julian($y,$m,$d,$h)
{
    if($m<=2){$y--; $m+=12;}
    $A=floor($y/100);
    $B=2-$A+floor($A/4);

    return floor(365.25*($y+4716))
        + floor(30.6001*($m+1))
        + $d+$B-1524.5+$h/24;
}

}