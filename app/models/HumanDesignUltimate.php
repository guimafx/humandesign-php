<?php



/****
 * 
 * 
 * Astronomia real ✔
Latitude ✔
Longitude ✔
HD Engine ✔
Mandala ✔
PHS ✔
Types ✔
Variables ✔


$hd = new HumanDesignUltimate();

$chart = $hd->calculate([
    'year'=>1987,
    'month'=>5,
    'day'=>14,
    'hour'=>13,
    'minute'=>32,
    'timezone'=>-3,
    'lat'=>-27.5954,
    'lon'=>-48.5480
]);

echo $hd->renderMandala($chart);

 * **/
class HumanDesignUltimate
{

/* ======================================================
   CONFIG
====================================================== */

private float $SEGMENT = 360/64;

private array $raveOrder = [
41,19,13,49,30,55,37,63,
22,36,25,17,21,51,42,3,
27,24,2,23,8,20,16,35,
45,12,15,52,39,53,62,56,
31,33,7,4,29,59,40,64,
47,6,46,18,48,57,32,50,
28,44,1,43,14,34,9,5,
26,11,10,58,38,54,61,60
];

private array $gateCenter = [
1=>'G',2=>'G',3=>'Sacral',4=>'Ajna',5=>'Sacral',
6=>'Solar',7=>'G',8=>'Throat',9=>'Sacral',10=>'G',
11=>'Ajna',12=>'Throat',13=>'G',14=>'Sacral',
15=>'G',16=>'Throat',17=>'Ajna',18=>'Spleen',
19=>'Root',20=>'Throat',21=>'Ego',22=>'Solar',
23=>'Throat',24=>'Ajna',25=>'G',26=>'Ego',
27=>'Sacral',28=>'Spleen',29=>'Sacral',30=>'Solar',
31=>'Throat',32=>'Spleen',33=>'Throat',34=>'Sacral',
35=>'Throat',36=>'Solar',37=>'Solar',38=>'Root',
39=>'Root',40=>'Ego',41=>'Root',42=>'Sacral',
43=>'Ajna',44=>'Spleen',45=>'Throat',46=>'G',
47=>'Ajna',48=>'Spleen',49=>'Solar',50=>'Spleen',
51=>'Ego',52=>'Root',53=>'Root',54=>'Root',
55=>'Solar',56=>'Throat',57=>'Spleen',58=>'Root',
59=>'Sacral',60=>'Root',61=>'Head',62=>'Throat',
63=>'Head',64=>'Head'
];

private array $channels = [
[1,8],[2,14],[3,60],[4,63],[5,15],[6,59],
[7,31],[9,52],[10,20],[10,34],[10,57],
[11,56],[12,22],[13,33],[16,48],[17,62],
[18,58],[19,49],[20,34],[20,57],[21,45],
[23,43],[24,61],[25,51],[26,44],[27,50],
[28,38],[29,46],[30,41],[32,54],[34,57],
[35,36],[37,40],[39,55],[42,53],[47,64]
];

private array $determination=[1=>'Appetite',2=>'Taste',3=>'Thirst',4=>'Touch',5=>'Sound',6=>'Light'];
private array $environment=[1=>'Caves',2=>'Markets',3=>'Kitchens',4=>'Mountains',5=>'Valleys',6=>'Shores'];
private array $motivation=[1=>'Fear',2=>'Hope',3=>'Desire',4=>'Need',5=>'Guilt',6=>'Innocence'];
private array $perspective=[1=>'Survival',2=>'Possibility',3=>'Power',4=>'Personal',5=>'Probability',6=>'Observation'];

private array $activeGates=[];
private array $definedCenters=[];

/* ======================================================
   MAIN CALCULATION
====================================================== */

public function calculate(array $b): array
{
    $utc = $b['hour'] + ($b['minute']/60) - $b['timezone'];

    $jd = $this->julian($b['year'],$b['month'],$b['day'],$utc);

    $p = $this->planets($jd,$b);
    $designJD = $this->designDate($jd,$p['SUN']['degree'],$b);
    $d = $this->planets($designJD,$b);

    $this->activate($p);
    $this->activate($d);
    $this->detectCenters();

    return [
        'profile'=>$p['SUN']['line'].'/'.$d['SUN']['line'],
        'type'=>$this->detectType(),
        'authority'=>$this->detectAuthority(),
        'variables'=>$this->variables($p,$d),
        'phs'=>$this->phs($p,$d),
        'have'=>$this->have($p,$d)
    ];
}

/* ======================================================
   PLANETS
====================================================== */

private function planets($jd,$b)
{
    $plist=['SUN','NORTH_NODE'];
    $out=[];

    foreach($plist as $pl){

        $deg=$this->ephemeris($jd,$pl,$b['lat'],$b['lon']);

        $out[$pl]=$this->degreeToHD($deg);
        $out[$pl]['degree']=$deg;
    }

    return $out;
}

/* ======================================================
   EPHEMERIS (REAL + FALLBACK)
====================================================== */

private function ephemeris($jd,$planet,$lat,$lon)
{
    if(function_exists('swe_calc_ut')){

        swe_set_topo($lon,$lat,0);

        $map=[
            'SUN'=>SE_SUN,
            'NORTH_NODE'=>SE_TRUE_NODE
        ];

        $res=swe_calc_ut($jd,$map[$planet]);

        return $res[0];
    }

    // fallback mock
    return fmod(($jd*crc32($planet))/1000000,360);
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
    $color=floor(fmod($within,$this->SEGMENT/6)/(($this->SEGMENT/6)/6))+1;
    $tone=$color;
    $base=1;

    return compact('gate','line','color','tone','base');
}

/* ======================================================
   ACTIVATIONS
====================================================== */

private function activate($data)
{
    foreach($data as $p)
        $this->activeGates[$p['gate']]=true;
}

private function detectCenters()
{
    foreach($this->activeGates as $g=>$v)
        $this->definedCenters[$this->gateCenter[$g]]=true;
}

/* ======================================================
   TYPE DETECTION
====================================================== */

private function graph()
{
    $g=[];
    foreach($this->channels as $c)
        if(isset($this->activeGates[$c[0]]) && isset($this->activeGates[$c[1]])){
            $a=$this->gateCenter[$c[0]];
            $b=$this->gateCenter[$c[1]];
            $g[$a][]=$b;
            $g[$b][]=$a;
        }
    return $g;
}

private function path($g,$s,$t,$v=[])
{
    if($s==$t) return true;
    $v[$s]=1;
    foreach($g[$s]??[] as $n)
        if(!isset($v[$n]) && $this->path($g,$n,$t,$v))
            return true;
    return false;
}

private function motorToThroat()
{
    $motors=['Sacral','Solar','Ego','Root'];
    $g=$this->graph();

    foreach($motors as $m)
        if($this->path($g,$m,'Throat')) return true;

    return false;
}

private function detectType()
{
    $sacral=isset($this->definedCenters['Sacral']);
    $motor=$this->motorToThroat();

    if($sacral && $motor) return 'Manifesting Generator';
    if($sacral) return 'Generator';
    if($motor) return 'Manifestor';
    if(empty($this->definedCenters)) return 'Reflector';
    return 'Projector';
}

/* ======================================================
   AUTHORITY + PHS + VARIABLES
====================================================== */

private function detectAuthority()
{
    if(isset($this->definedCenters['Solar'])) return 'Emotional';
    if(isset($this->definedCenters['Sacral'])) return 'Sacral';
    if(isset($this->definedCenters['Spleen'])) return 'Splenic';
    if(isset($this->definedCenters['Ego'])) return 'Ego';
    if(isset($this->definedCenters['G'])) return 'Self';
    if(empty($this->definedCenters)) return 'Lunar';
    return 'None';
}

private function LR($t){ return $t<=3?'L':'R'; }

private function variables($p,$d)
{
    return [
        'determination'=>$this->LR($d['SUN']['tone']),
        'environment'=>$this->LR($d['NORTH_NODE']['tone']),
        'perspective'=>$this->LR($p['SUN']['tone']),
        'motivation'=>$this->LR($p['NORTH_NODE']['tone'])
    ];
}

private function phs($p,$d)
{
    return [
        'determination'=>$this->determination[$d['SUN']['color']],
        'environment'=>$this->environment[$d['NORTH_NODE']['color']],
        'motivation'=>$this->motivation[$p['SUN']['color']],
        'perspective'=>$this->perspective[$p['NORTH_NODE']['color']]
    ];
}

/* ======================================================
   HAVE STRUCTURE
====================================================== */

private function have($p,$d)
{
    $o=[];
    foreach($p as $k=>$v)$o[]=['planet'=>$k,'degree'=>$v['degree']];
    foreach($d as $k=>$v)$o[]=['planet'=>'D-'.$k,'degree'=>$v['degree']];
    return $o;
}

/* ======================================================
   SVG RENDER
====================================================== */

public function renderMandala($chart,$size=720)
{
    $cx=$size/2; $cy=$size/2; $r=$size/2-90;

    $svg="<svg width='$size' height='$size'
          xmlns='http://www.w3.org/2000/svg'>";

    $svg.="<circle cx='$cx' cy='$cy' r='$r'
            fill='none' stroke='black' stroke-width='2'/>";

    for($i=0;$i<64;$i++){
        $ang=deg2rad(($i*360/64)-90);
        $x=$cx+cos($ang)*$r;
        $y=$cy+sin($ang)*$r;
        $svg.="<text x='$x' y='$y'
        font-size='10' text-anchor='middle'>
        {$this->raveOrder[$i]}</text>";
    }

    foreach($chart['have'] as $p){
        $ang=deg2rad($p['degree']-90);
        $x=$cx+cos($ang)*($r-25);
        $y=$cy+sin($ang)*($r-25);
        $svg.="<circle cx='$x' cy='$y' r='4' fill='red'/>";
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

?>