<?php
require_once "HumanDesignUltimate.php";

$resultado=null;
$svg=null;
$erro=null;

if($_SERVER['REQUEST_METHOD']=='POST')
{
    try {

        $hd=new HumanDesignUltimate();

        $resultado=$hd->calculate([
            'cidade'=>$_POST['cidade_label'],
            'year'=>$_POST['ano'],
            'month'=>$_POST['mes'],
            'day'=>$_POST['dia'],
            'hour'=>$_POST['hora'] ?: 12,
            'minute'=>$_POST['minuto'] ?: 0,
            'unknown_time'=>isset($_POST['unknown_time'])
        ]);

        $svg=$hd->renderMandala($resultado);

    } catch(Exception $e){
        $erro=$e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Human Design Calculator</title>

<link rel="stylesheet"
 href="https://unpkg.com/leaflet/dist/leaflet.css"/>

<style>

body{
    font-family:Arial;
    background:#f4f4f4;
    padding:40px;
}

.card{
    background:white;
    padding:20px;
    border-radius:12px;
    margin-bottom:20px;
}

#map{
    height:420px;
    border-radius:10px;
}

.hidden{ display:none; }

input{
    width:100%;
    padding:8px;
    margin:6px 0;
}

button{
    padding:12px;
    width:100%;
    background:black;
    color:white;
    border:0;
    cursor:pointer;
}

</style>
</head>

<body>

<h2>🧬 Human Design Calculator</h2>

<form method="POST">

<!-- STEP 1 -->

<div class="card">

<h3>1️⃣ Onde você nasceu?</h3>

<input id="searchCity"
       placeholder="Digite cidade ou hospital...">

<div id="map"></div>

<input type="hidden" name="cidade_label" id="cidade_label">

<p id="localStatus">Clique no mapa ou busque um local.</p>

</div>

<!-- STEP 2 -->

<div id="birthStep" class="card hidden">

<h3>2️⃣ Dados de nascimento</h3>

<label>Dia</label>
<input name="dia" required>

<label>Mês</label>
<input name="mes" required>

<label>Ano</label>
<input name="ano" required>

<label>Hora</label>
<input class="hora" name="hora">

<label>Minuto</label>
<input class="hora" name="minuto">

<label>
<input type="checkbox"
       name="unknown_time"
       onchange="toggleHora(this)">
Não sei meu horário exato
</label>

<br><br>

<button>Gerar Mandala</button>

</div>

</form>

<?php if($erro): ?>
<p style="color:red"><?=$erro?></p>
<?php endif; ?>

<?php if($svg): ?>
<div class="card">
<h3>Resultado</h3>
<?=$svg?>
</div>
<?php endif; ?>


<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

<script>

/* ================= MAP ================= */

var map=L.map('map').setView([-15,-55],4);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png')
.addTo(map);

var marker=null;

function unlockStep(place)
{
    document.getElementById("cidade_label").value=place;
    document.getElementById("birthStep").classList.remove("hidden");
    document.getElementById("localStatus").innerHTML=
        "✔ Local selecionado: "+place;
}

/* click manual */

map.on('click',function(e){

    if(marker) map.removeLayer(marker);

    marker=L.marker(e.latlng).addTo(map);

    unlockStep(
        e.latlng.lat.toFixed(4)+","+
        e.latlng.lng.toFixed(4)
    );
});


/* ================= SEARCH CITY ================= */

document.getElementById("searchCity")
.addEventListener("change",function(){

    let q=this.value;

    fetch(
      "https://nominatim.openstreetmap.org/search?format=json&q="+q
    )
    .then(r=>r.json())
    .then(data=>{

        if(!data.length) return;

        let lat=data[0].lat;
        let lon=data[0].lon;

        map.setView([lat,lon],12);

        if(marker) map.removeLayer(marker);

        marker=L.marker([lat,lon]).addTo(map);

        unlockStep(data[0].display_name);
    });
});


/* ================= UNKNOWN TIME ================= */

function toggleHora(cb)
{
    document.querySelectorAll('.hora')
    .forEach(e=>e.disabled=cb.checked);
}

</script>

</body>
</html>