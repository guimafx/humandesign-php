<?php
require_once "HumanDesignUltimate.php";

/* =====================================================
   GEOLOCALIZAÇÃO (cidade → lat/lon)
===================================================== */

function buscarLatLon($cidade)
{
    $cidade = urlencode($cidade);

    $url = "https://geocoding-api.open-meteo.com/v1/search?name={$cidade}&count=1&language=pt&format=json";

    $json = @file_get_contents($url);
    if(!$json) return false;

    $data = json_decode($json,true);

    if(empty($data['results'][0])) return false;

    return [
        'lat'=>$data['results'][0]['latitude'],
        'lon'=>$data['results'][0]['longitude'],
        'cidade'=>$data['results'][0]['name'],
        'pais'=>$data['results'][0]['country']
    ];
}

/* =====================================================
   PROCESSAMENTO
===================================================== */

$resultado=null;
$svg=null;
$erro=null;

if($_SERVER['REQUEST_METHOD']=='POST')
{
    $geo = buscarLatLon($_POST['cidade']);

    if(!$geo){
        $erro="Cidade não encontrada.";
    } else {

        $hd = new HumanDesignUltimate();

        $resultado = $hd->calculate([
            'year'=>$_POST['ano'],
            'month'=>$_POST['mes'],
            'day'=>$_POST['dia'],
            'hour'=>$_POST['hora'] ?: 12,
            'minute'=>$_POST['minuto'] ?: 0,
            'timezone'=>$_POST['timezone'],
            'lat'=>$geo['lat'],
            'lon'=>$geo['lon'],
            'unknown_time'=>isset($_POST['unknown_time'])
        ]);

        $svg = $hd->renderMandala($resultado);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Calculadora Human Design</title>

<style>
body{
    font-family:Arial;
    background:#f2f2f2;
    padding:40px;
}

.container{
    display:flex;
    gap:40px;
}

form{
    background:white;
    padding:20px;
    border-radius:10px;
    width:360px;
}

input{
    width:100%;
    padding:8px;
    margin:6px 0;
}

button{
    width:100%;
    padding:12px;
    background:#111;
    color:white;
    border:0;
    cursor:pointer;
}

.result{
    background:white;
    padding:20px;
    border-radius:10px;
}

.erro{
    color:red;
}
</style>

<script>
function toggleHora(cb){
    document.querySelectorAll('.hora').forEach(e=>{
        e.disabled = cb.checked;
    });
}
</script>

</head>

<body>

<h2>🧬 Calculadora Human Design</h2>

<div class="container">

<form method="POST">

<label>Nome</label>
<input name="nome" required>

<label>Cidade de nascimento</label>
<input name="cidade" placeholder="Ex: Florianópolis" required>

<label>Dia</label>
<input type="number" name="dia" required>

<label>Mês</label>
<input type="number" name="mes" required>

<label>Ano</label>
<input type="number" name="ano" required>

<label>Hora</label>
<input class="hora" type="number" name="hora" min="0" max="23">

<label>Minuto</label>
<input class="hora" type="number" name="minuto" min="0" max="59">

<label>Timezone (GMT)</label>
<input name="timezone" value="-3">

<label>
<input type="checkbox" name="unknown_time"
       onchange="toggleHora(this)">
Não sei meu horário exato
</label>

<br><br>

<button type="submit">Gerar Mandala</button>

<?php if($erro): ?>
<p class="erro"><?=$erro?></p>
<?php endif; ?>

</form>

<?php if($resultado): ?>

<div class="result">

<h3>Resultado</h3>

<p>
Tipo: <b><?=$resultado['type']?></b><br>
Perfil: <b><?=$resultado['profile']?></b><br>
Autoridade: <b><?=$resultado['authority']?></b>
</p>

<h4>PHS</h4>

<?php if(is_array($resultado['phs'])): ?>
<ul>
<li>Determination: <?=$resultado['phs']['determination']?></li>
<li>Environment: <?=$resultado['phs']['environment']?></li>
<li>Motivation: <?=$resultado['phs']['motivation']?></li>
<li>Perspective: <?=$resultado['phs']['perspective']?></li>
</ul>
<?php else: ?>
<p><?=$resultado['phs']?></p>
<?php endif; ?>

<div>
<?=$svg?>
</div>

</div>

<?php endif; ?>

</div>

</body>
</html>