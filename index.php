
<?php

$hd = new HumanDesignUnified();

$chart = $hd->calculate([
   'year'=>1982,
   'month'=>3,
   'day'=>27,
   'hour'=>11,
   'minute'=>05,
   'timezone'=>-3,
   'lat'=>-30.03,
   'lon'=>-51.23
]);

print_r($chart);

?>


